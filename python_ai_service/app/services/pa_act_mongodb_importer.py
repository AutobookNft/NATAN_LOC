"""
PA Act MongoDB Importer - Import atti PA in MongoDB con tracking costi
Gestisce estrazione PDF, chunking, embeddings e salvataggio in MongoDB
Con supporto dry-run e report dettagliato
"""

import asyncio
import hashlib
import json
import logging
import os
import re
from datetime import datetime
from pathlib import Path
from typing import List, Dict, Any, Optional, Tuple
import sys

# Add parent directory to path
sys.path.insert(0, str(Path(__file__).parent.parent.parent))

from app.services.mongodb_service import MongoDBService
from app.services.ai_router import AIRouter
from app.services.document_structure_parser import DocumentStructureParser

logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)


class CostTracker:
    """Track embedding costs per model"""
    
    # Prezzi per 1M tokens (in dollari, convertiti in € a fine report)
    # Aggiornati al 2025-01
    PRICING = {
        "openai.text-embedding-3-small": 0.02,  # $ per 1M tokens
        "openai.text-embedding-3-large": 0.13,
        "openai.text-embedding-ada-002": 0.10,
        "ollama.nomic-embed-text": 0.0,  # Gratuito (locale)
    }
    
    # Tasso cambio USD/EUR (aggiornare se necessario)
    USD_EUR_RATE = 0.92
    
    def __init__(self):
        self.total_tokens = 0
        self.model_used = None
        self.cost_usd = 0.0
    
    def add_usage(self, tokens: int, model: str):
        """Add token usage"""
        self.total_tokens += tokens
        if not self.model_used:
            self.model_used = model
    
    def calculate_cost(self) -> Dict[str, Any]:
        """Calculate total cost"""
        model_key = self.model_used or "openai.text-embedding-3-small"
        
        # Normalize model key
        if model_key.startswith("openai."):
            model_key = f"openai.{model_key.replace('openai.', '')}"
        
        price_per_million = self.PRICING.get(model_key, self.PRICING["openai.text-embedding-3-small"])
        
        # Cost in USD
        cost_usd = (self.total_tokens / 1_000_000) * price_per_million
        
        # Cost in EUR
        cost_eur = cost_usd * self.USD_EUR_RATE
        
        return {
            "total_tokens": self.total_tokens,
            "model": self.model_used or "unknown",
            "cost_usd": round(cost_usd, 4),
            "cost_eur": round(cost_eur, 4),
            "price_per_million": price_per_million
        }


class PAActMongoDBImporter:
    """
    Import atti PA in MongoDB con chunking e embeddings
    Supporta dry-run e report costi dettagliato
    """
    
    # Chunk size for text splitting (tokens ~ 512)
    CHUNK_SIZE = 2000  # characters (approx 500 tokens)
    CHUNK_OVERLAP = 200  # characters overlap between chunks
    
    def __init__(self, tenant_id: int = 1, dry_run: bool = False):
        self.tenant_id = tenant_id
        self.dry_run = dry_run
        self.ai_router = AIRouter()
        self.structure_parser = DocumentStructureParser()
        
        # Statistics tracking
        self.stats = {
            "processed": 0,
            "skipped": 0,
            "errors": 0,
            "error_details": [],
            "total_chunks": 0,
            "total_documents": 0
        }
        
        # Cost tracking
        self.cost_tracker = CostTracker()
    
    def download_pdf_from_url(self, pdf_url: str, atto_data: Dict[str, Any]) -> Optional[str]:
        """Download PDF from URL to temporary file"""
        try:
            import requests
            import tempfile
            
            # Create temp directory if it doesn't exist
            temp_dir = Path(__file__).parent.parent.parent / "temp_pdfs"
            temp_dir.mkdir(exist_ok=True)
            
            # Generate filename from atto data
            numero_atto = atto_data.get('numero_atto', 'unknown')
            tipo_atto = atto_data.get('tipo_atto', 'atto').replace(' ', '_')
            filename = f"{tipo_atto}_{numero_atto}.pdf"
            temp_pdf_path = temp_dir / filename
            
            # Download PDF
            response = requests.get(pdf_url, timeout=30, stream=True)
            response.raise_for_status()
            
            with open(temp_pdf_path, 'wb') as f:
                for chunk in response.iter_content(chunk_size=8192):
                    f.write(chunk)
            
            logger.info(f"  ✅ PDF scaricato: {temp_pdf_path}")
            return str(temp_pdf_path)
        except Exception as e:
            logger.error(f"Errore download PDF da {pdf_url}: {e}")
            import traceback
            logger.error(traceback.format_exc())
            return None
    
    def extract_pdf_text(self, pdf_path: str) -> Optional[str]:
        """Extract text from PDF file"""
        try:
            # Try PyPDF2 first
            try:
                import PyPDF2
                text = ""
                with open(pdf_path, 'rb') as f:
                    pdf_reader = PyPDF2.PdfReader(f)
                    for page in pdf_reader.pages:
                        text += page.extract_text() + "\n"
                return text.strip() if text else None
            except ImportError:
                # Try pdfplumber as fallback
                try:
                    import pdfplumber
                    text = ""
                    with pdfplumber.open(pdf_path) as pdf:
                        for page in pdf.pages:
                            page_text = page.extract_text()
                            if page_text:
                                text += page_text + "\n"
                    return text.strip() if text else None
                except ImportError:
                    logger.warning("Né PyPDF2 né pdfplumber installati. Installa uno dei due per estrarre testo PDF.")
                    return None
        except Exception as e:
            logger.error(f"Errore estrazione PDF {pdf_path}: {e}")
            return None
    
    def split_text_into_chunks(self, text: str) -> List[Dict[str, Any]]:
        """Split text into chunks for better retrieval"""
        chunks = []
        
        # Clean text
        text = text.strip()
        if not text or len(text) < 50:
            return chunks
        
        # Split by paragraphs first (double newlines)
        paragraphs = re.split(r'\n\s*\n', text)
        
        current_chunk = ""
        chunk_index = 0
        
        for para in paragraphs:
            para = para.strip()
            if not para:
                continue
            
            # If adding this paragraph would exceed chunk size, save current chunk
            if current_chunk and len(current_chunk) + len(para) + 2 > self.CHUNK_SIZE:
                chunks.append({
                    'chunk_index': chunk_index,
                    'chunk_text': current_chunk.strip(),
                    'tokens': len(current_chunk.split())  # Approximate
                })
                chunk_index += 1
                
                # Start new chunk with overlap
                if self.CHUNK_OVERLAP > 0:
                    overlap_text = current_chunk[-self.CHUNK_OVERLAP:]
                    current_chunk = overlap_text + "\n\n" + para
                else:
                    current_chunk = para
            else:
                if current_chunk:
                    current_chunk += "\n\n" + para
                else:
                    current_chunk = para
        
        # Add last chunk
        if current_chunk.strip():
            chunks.append({
                'chunk_index': chunk_index,
                'chunk_text': current_chunk.strip(),
                'tokens': len(current_chunk.split())
            })
        
        return chunks
    
    def generate_document_id(self, atto_data: Dict[str, Any], ente: str = "Firenze") -> str:
        """
        Generate unique document_id from atto metadata
        Format: pa_act_{ente}_{tipo}_{numero}_{anno}
        """
        tipo = atto_data.get('tipo_atto', '').replace(' ', '_')
        numero = atto_data.get('numero_atto', '').replace('/', '_').replace(' ', '_')
        
        # Estrai anno da anno o data_atto (può essere int o string)
        anno = atto_data.get('anno', '')
        if not anno:
            data_atto = atto_data.get('data_atto', '')
            if data_atto:
                if isinstance(data_atto, int):
                    anno = str(data_atto)
                elif isinstance(data_atto, str) and len(data_atto) >= 4:
                    anno = data_atto[:4]
                else:
                    anno = str(data_atto)
        if not anno:
            anno = ''
        
        # Create base ID
        base_id = f"pa_act_{ente}_{tipo}_{numero}_{anno}".lower()
        
        # Clean special characters
        base_id = re.sub(r'[^a-z0-9_]', '_', base_id)
        base_id = re.sub(r'_+', '_', base_id)  # Remove multiple underscores
        
        # Add hash if needed for uniqueness
        if not base_id or len(base_id) < 10:
            unique_str = f"{ente}_{tipo}_{numero}_{anno}_{atto_data.get('oggetto', '')[:50]}"
            hash_obj = hashlib.md5(unique_str.encode())
            base_id = f"pa_act_{hash_obj.hexdigest()[:12]}"
        
        return base_id
    
    async def generate_embedding(self, text: str) -> Tuple[Optional[List[float]], Optional[int], Optional[str]]:
        """
        Generate embedding for text
        
        Returns:
            (embedding, tokens, model_used)
        """
        try:
            context = {
                "tenant_id": self.tenant_id,
                "task_class": "embed"
            }
            adapter = self.ai_router.get_embedding_adapter(context)
            result = await adapter.embed(text)
            
            embedding = result.get("embedding", [])
            tokens = result.get("tokens", 0)
            model = result.get("model", "unknown")
            
            # Track cost
            if tokens > 0:
                self.cost_tracker.add_usage(tokens, model)
            
            return embedding, tokens, model
        except Exception as e:
            logger.error(f"Errore generazione embedding: {e}")
            return None, 0, None
    
    async def import_atto(
        self,
        atto_data: Dict[str, Any],
        pdf_path: Optional[str] = None,
        pdf_url: Optional[str] = None,
        ente: str = "Firenze"
    ) -> bool:
        """
        Import single atto to MongoDB
        
        Args:
            atto_data: Dict with atto metadata (numero_atto, tipo_atto, oggetto, data_atto, etc.)
            pdf_path: Optional path to PDF file (local)
            pdf_url: Optional URL to PDF (remote)
            ente: Nome ente (default: Firenze)
        
        Returns:
            True if successful, False otherwise
        """
        try:
            # Extract text from PDF if available
            text_content = None
            temp_pdf_path = None
            using_fallback_oggetto = False  # Track if we're using oggetto as fallback
            
            # Try local PDF path first
            if pdf_path and os.path.exists(pdf_path):
                logger.info(f"  📄 Estraendo testo da PDF locale: {Path(pdf_path).name}")
                text_content = self.extract_pdf_text(pdf_path)
            # If no local PDF but URL available, download it temporarily
            elif pdf_url and not pdf_path:
                try:
                    logger.info(f"  📥 Downloading PDF from URL: {pdf_url}")
                    temp_pdf_path = self.download_pdf_from_url(pdf_url, atto_data)
                    if temp_pdf_path and os.path.exists(temp_pdf_path):
                        logger.info(f"  📄 Estraendo testo da PDF scaricato: {Path(temp_pdf_path).name}")
                        text_content = self.extract_pdf_text(temp_pdf_path)
                        if text_content:
                            extracted_chars = len(text_content.strip())
                            logger.info(f"  ✅ Testo estratto: {extracted_chars} caratteri")
                        else:
                            logger.warning(f"  ⚠️  Estrazione PDF fallita: nessun testo estratto da {Path(temp_pdf_path).name}")
                    else:
                        logger.warning(f"  ⚠️  Download PDF fallito: file non trovato dopo download")
                except Exception as e:
                    logger.error(f"  ❌ Errore download/estrazione PDF da URL: {e}")
                    import traceback
                    logger.error(f"  Traceback: {traceback.format_exc()}")
            
            # Fallback to oggetto if no PDF text extracted
            if not text_content or len(text_content.strip()) < 50:
                if atto_data.get('oggetto'):
                    logger.warning(f"  ⚠️  PDF non disponibile o estrazione fallita per atto {atto_data.get('numero_atto', 'N/A')}")
                    logger.warning(f"  ⚠️  Testo estratto: {len(text_content.strip()) if text_content else 0} caratteri (minimo richiesto: 50)")
                    logger.warning(f"  ⚠️  Uso oggetto come fallback: '{atto_data.get('oggetto', '')[:100]}...'")
                    text_content = atto_data.get('oggetto', '')
                    # Mark that we're using fallback oggetto (will be used in update logic)
                    using_fallback_oggetto = True
                else:
                    logger.warning(f"  ⚠️  Nessun contenuto testo valido per atto {atto_data.get('numero_atto', 'N/A')}")
                    self.stats["skipped"] += 1
                    # Clean up temp file if created
                    if temp_pdf_path and os.path.exists(temp_pdf_path):
                        try:
                            os.remove(temp_pdf_path)
                        except:
                            pass
                    return False
            
            # Clean up temp file after extraction
            if temp_pdf_path and os.path.exists(temp_pdf_path):
                try:
                    os.remove(temp_pdf_path)
                except:
                    pass
            
            # Parse document structure (identifica sezioni logiche)
            document_structure = None
            if text_content and len(text_content) > 500:
                try:
                    logger.info(f"  📋 Analizzando struttura documento...")
                    document_structure = self.structure_parser.parse_structure(text_content, use_llm=True)
                    if document_structure.get('section_count', 0) > 0:
                        logger.info(f"  ✅ Identificate {document_structure['section_count']} sezioni: {', '.join(document_structure.get('section_names', []))}")
                except Exception as e:
                    logger.warning(f"  ⚠️  Errore analisi struttura: {e}")
                    document_structure = None
            
            # Split into chunks
            chunks = self.split_text_into_chunks(text_content)
            if not chunks:
                logger.warning(f"  ⚠️  Nessun chunk estratto per atto {atto_data.get('numero_atto', 'N/A')}")
                self.stats["skipped"] += 1
                return False
            
            logger.info(f"  📝 Estratti {len(chunks)} chunks")
            self.stats["total_chunks"] += len(chunks)
            
            if self.dry_run:
                # In dry-run, just count chunks without generating embeddings
                logger.info(f"  🔍 DRY-RUN: Simulando {len(chunks)} embeddings...")
                # Estimate tokens (rough: 1 token ≈ 4 characters)
                estimated_tokens = sum(len(chunk['chunk_text']) // 4 for chunk in chunks)
                self.cost_tracker.add_usage(estimated_tokens, "openai.text-embedding-3-small")
                self.stats["processed"] += 1
                return True
            
            # Generate embeddings for each chunk (batch processing)
            chunks_with_embeddings = []
            batch_size = 5  # Process multiple chunks concurrently
            
            for i in range(0, len(chunks), batch_size):
                batch = chunks[i:i+batch_size]
                logger.info(f"  🤖 Generando embeddings chunks {i+1}-{min(i+batch_size, len(chunks))}/{len(chunks)}...")
                
                # Generate embeddings in parallel
                tasks = [self.generate_embedding(chunk['chunk_text']) for chunk in batch]
                results = await asyncio.gather(*tasks, return_exceptions=True)
                
                # Attach embeddings to chunks
                for chunk, result in zip(batch, results):
                    if isinstance(result, Exception):
                        logger.warning(f"  ⚠️  Errore embedding: {result}")
                    elif result[0]:  # embedding exists
                        chunk['embedding'] = result[0]
                        chunk['tokens_used'] = result[1]
                        chunk['model_used'] = result[2]
                        chunks_with_embeddings.append(chunk)
                
                # Small delay between batches
                if i + batch_size < len(chunks):
                    await asyncio.sleep(0.2)
            
            if not chunks_with_embeddings:
                logger.error(f"  ❌ Nessun embedding generato per atto {atto_data.get('numero_atto', 'N/A')}")
                self.stats["errors"] += 1
                self.stats["error_details"].append({
                    "atto": atto_data.get('numero_atto', 'N/A'),
                    "error": "Nessun embedding generato"
                })
                return False
            
            # Generate document-level embedding (from title + first chunk)
            doc_text_for_embedding = f"{atto_data.get('oggetto', '')}\n\n{chunks_with_embeddings[0]['chunk_text'][:500]}"
            logger.info(f"  🤖 Generando embedding document-level...")
            doc_embedding, doc_tokens, doc_model = await self.generate_embedding(doc_text_for_embedding)
            
            # Prepare document for MongoDB
            document_id = self.generate_document_id(atto_data, ente)
            
            # CRITICAL: Check if document already exists by protocol_number + tenant_id
            # This handles cases where document_id format changed (e.g., timestamp vs year, tipo_atto missing)
            # If found, use existing document_id to ensure update instead of duplicate creation
            protocol_number = atto_data.get('numero_atto', '')
            if protocol_number:
                existing_docs = MongoDBService.find_documents("documents", {
                    "protocol_number": protocol_number,
                    "tenant_id": self.tenant_id
                }, limit=1)
                
                if existing_docs:
                    existing_doc_id = existing_docs[0].get("document_id")
                    if existing_doc_id and existing_doc_id != document_id:
                        logger.info(f"  🔄 Documento esistente trovato per protocol_number {protocol_number}")
                        logger.info(f"  🔄 Document_id esistente: {existing_doc_id}")
                        logger.info(f"  🔄 Document_id generato: {document_id}")
                        logger.info(f"  🔄 Usando document_id esistente per garantire update invece di creare duplicate")
                        document_id = existing_doc_id
            
            document = {
                "document_id": document_id,
                "tenant_id": self.tenant_id,
                "title": atto_data.get('oggetto', f"Atto {atto_data.get('numero_atto', 'N/A')}"),
                "filename": pdf_path.split('/')[-1] if pdf_path else None,
                "document_type": "pa_act",
                "protocol_number": atto_data.get('numero_atto', ''),
                "protocol_date": atto_data.get('data_atto', ''),
                "embedding": doc_embedding,  # Document-level embedding
                "content": {
                    "raw_text": text_content[:5000],  # Preview
                    "full_text": text_content,
                    "chunks": chunks_with_embeddings,
                    "structure": document_structure  # Sezioni logiche identificate
                },
                "metadata": {
                    "source": "pa_scraper",
                    "ente": ente,
                    "tipo_atto": atto_data.get('tipo_atto', ''),
                    "numero_atto": atto_data.get('numero_atto', ''),
                    "data_atto": atto_data.get('data_atto', ''),
                    "anno": atto_data.get('anno', ''),
                    "pdf_url": pdf_url,
                    "pdf_path": pdf_path,
                    "imported_at": datetime.now().isoformat(),
                    "chunk_count": len(chunks_with_embeddings),
                    "total_chars": len(text_content),
                    "scraper_type": atto_data.get('scraper_type', 'unknown')
                },
                "status": "active",
                "created_at": datetime.now(),
                "updated_at": datetime.now()
            }
            
            # Save to MongoDB (update if exists, insert if new)
            result_id = MongoDBService.insert_document("documents", document)
            
            if result_id == 'duplicate':
                # Documento già presente - verifica se ha testo completo o solo oggetto
                existing_docs = MongoDBService.find_documents("documents", {
                    "document_id": document_id,
                    "tenant_id": self.tenant_id
                }, limit=1)
                
                if existing_docs:
                    existing_doc = existing_docs[0]
                    existing_full_text = existing_doc.get("content", {}).get("full_text", "")
                    existing_chars = len(existing_full_text)
                    new_chars = len(text_content)
                    
                    # Se il nuovo documento ha più testo O se manca la struttura, aggiorna
                    existing_structure = existing_doc.get("content", {}).get("structure")
                    existing_structure_count = existing_structure.get('section_count', 0) if existing_structure else 0
                    new_structure_count = document_structure.get('section_count', 0) if document_structure else 0
                    
                    # Aggiorna se:
                    # 1. Il nuovo testo è significativamente più lungo (almeno 100 caratteri in più)
                    # 2. Il documento esistente ha solo l'oggetto (meno di 1000 caratteri) e il nuovo ha testo completo
                    # 3. Manca la struttura e il nuovo documento la ha
                    # CRITICAL: NON aggiornare se il nuovo testo è solo l'oggetto (fallback) e il documento esistente ha già solo l'oggetto
                    # Questo previene loop infiniti di "aggiornamenti" inutili quando l'estrazione PDF fallisce
                    is_fallback_oggetto = (new_chars < 1000 and using_fallback_oggetto)
                    existing_is_oggetto = (existing_chars < 1000)
                    
                    needs_update = (
                        (new_chars > existing_chars + 100 or  # Almeno 100 caratteri in più
                        (existing_chars < 1000 and new_chars >= 1000) or  # Da oggetto a testo completo
                        (new_structure_count > 0 and existing_structure_count == 0))  # Aggiungi struttura se manca
                        and not (is_fallback_oggetto and existing_is_oggetto)  # NON aggiornare se entrambi sono solo oggetto
                    )
                    
                    if is_fallback_oggetto and existing_is_oggetto:
                        logger.info(f"  ⏭️  Salto aggiornamento: documento esistente e nuovo hanno entrambi solo l'oggetto (estrazione PDF fallita, nessun miglioramento)")
                    
                    if needs_update:
                        # Determina il motivo dell'aggiornamento per log più chiaro
                        if (existing_chars < 1000 and new_chars >= 1000):
                            logger.info(f"  🔄 Documento esistente con solo oggetto ({existing_chars} caratteri) → aggiornamento con testo completo PDF ({new_chars} caratteri)...")
                        elif new_chars > existing_chars + 100:
                            logger.info(f"  🔄 Documento esistente con testo incompleto ({existing_chars} → {new_chars} caratteri). Aggiornamento...")
                        elif new_structure_count > 0 and existing_structure_count == 0:
                            logger.info(f"  🔄 Aggiungendo struttura documento ({existing_structure_count} → {new_structure_count} sezioni)...")
                        
                        # Update document with new content
                        update_result = MongoDBService.update_document("documents", {
                            "document_id": document_id,
                            "tenant_id": self.tenant_id
                        }, {
                            "content.full_text": text_content,
                            "content.raw_text": text_content[:5000],
                            "content.chunks": chunks_with_embeddings,
                            "content.structure": document_structure,
                            "embedding": doc_embedding,
                            "metadata.chunk_count": len(chunks_with_embeddings),
                            "metadata.total_chars": len(text_content),
                            "metadata.pdf_path": pdf_path,
                            "metadata.pdf_url": pdf_url,  # Aggiorna anche PDF URL se disponibile
                            "updated_at": datetime.now()
                        })
                        
                        if update_result > 0:
                            logger.info(f"  ✅ Documento aggiornato in MongoDB: {document_id} ({existing_chars} → {new_chars} caratteri)")
                            self.stats["processed"] += 1
                            return True
                        else:
                            logger.warning(f"  ⚠️  Aggiornamento fallito per {document_id}")
                            self.stats["skipped"] += 1
                            return True  # Considerato successo (skip)
                    else:
                        # Documento già presente con testo completo o simile
                        logger.info(f"  ⏭️  Documento già presente in MongoDB: {document_id} (testo completo: {existing_chars} caratteri)")
                        self.stats["skipped"] += 1
                        return True  # Considerato successo (skip, non errore)
                else:
                    # Duplicate ma documento non trovato (strano, ma gestiamo)
                    logger.warning(f"  ⚠️  Documento marcato come duplicate ma non trovato: {document_id}")
                    self.stats["skipped"] += 1
                    return True
            elif result_id:
                logger.info(f"  ✅ Salvato in MongoDB: {document_id}")
                self.stats["processed"] += 1
                self.stats["total_documents"] += 1
                return True
            else:
                logger.error(f"  ❌ Errore salvataggio MongoDB per {document_id}")
                self.stats["errors"] += 1
                self.stats["error_details"].append({
                    "atto": atto_data.get('numero_atto', 'N/A'),
                    "error": "Errore salvataggio MongoDB"
                })
                return False
        
        except Exception as e:
            logger.error(f"  ❌ Errore import atto: {e}", exc_info=True)
            self.stats["errors"] += 1
            self.stats["error_details"].append({
                "atto": atto_data.get('numero_atto', 'N/A'),
                "error": str(e)
            })
            return False
    
    def get_report(self) -> Dict[str, Any]:
        """
        Generate final report with statistics and costs
        
        Returns:
            Dict with stats, costs, and error details
        """
        cost_info = self.cost_tracker.calculate_cost()
        
        return {
            "dry_run": self.dry_run,
            "stats": {
                "processed": self.stats["processed"],
                "skipped": self.stats["skipped"],
                "errors": self.stats["errors"],
                "total_chunks": self.stats["total_chunks"],
                "total_documents": self.stats["total_documents"]
            },
            "costs": cost_info,
            "errors": self.stats["error_details"] if self.stats["error_details"] else []
        }
    
    def print_report(self):
        """Print formatted report to console"""
        report = self.get_report()
        
        print("\n" + "="*70)
        print("📊 REPORT IMPORT ATTI PA → MONGODB")
        print("="*70)
        
        if report["dry_run"]:
            print("🔍 MODALITÀ: DRY-RUN (nessun dato salvato)")
        else:
            print("💾 MODALITÀ: IMPORT REALE")
        
        print(f"\n📈 STATISTICHE:")
        print(f"  ✅ Processati:     {report['stats']['processed']}")
        print(f"  ⏭️  Saltati:        {report['stats']['skipped']}")
        print(f"  ❌ Errori:         {report['stats']['errors']}")
        print(f"  📝 Chunks totali:  {report['stats']['total_chunks']}")
        print(f"  📄 Documenti:      {report['stats']['total_documents']}")
        
        print(f"\n💰 COSTI:")
        print(f"  🤖 Modello:        {report['costs']['model']}")
        print(f"  🎫 Token usati:    {report['costs']['total_tokens']:,}")
        print(f"  💵 Costo USD:      ${report['costs']['cost_usd']:.4f}")
        print(f"  💶 Costo EUR:      €{report['costs']['cost_eur']:.4f}")
        print(f"  📊 Prezzo/M tokens: ${report['costs']['price_per_million']:.2f}")
        
        if report["errors"]:
            print(f"\n❌ ERRORI DETTAGLIATI:")
            for i, error in enumerate(report["errors"][:10], 1):  # Mostra max 10 errori
                print(f"  {i}. Atto {error['atto']}: {error['error']}")
            if len(report["errors"]) > 10:
                print(f"  ... e altri {len(report['errors']) - 10} errori")
        
        print("="*70 + "\n")












