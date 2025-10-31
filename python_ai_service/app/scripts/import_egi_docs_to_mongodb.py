"""
Script per importare documentazione EGI in MongoDB
Legge tutti i file da /home/fabio/EGI/docs e li carica in MongoDB con embeddings
"""

import asyncio
import json
import logging
import os
import re
from pathlib import Path
import sys
from typing import List, Dict, Any, Optional

# Add parent directory to path
sys.path.insert(0, str(Path(__file__).parent.parent.parent))

from app.services.mongodb_service import MongoDBService
from app.services.ai_router import AIRouter
from dotenv import load_dotenv

# Load environment variables
env_path = Path(__file__).parent.parent.parent / '.env'
load_dotenv(dotenv_path=env_path)

logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)


class DocumentImporter:
    """Import documents from EGI/docs directory to MongoDB"""
    
    # Chunk size for text splitting (tokens ~ 512)
    CHUNK_SIZE = 2000  # characters (approx 500 tokens)
    CHUNK_OVERLAP = 200  # characters overlap between chunks
    
    def __init__(self, tenant_id: int = 1):
        self.tenant_id = tenant_id
        self.ai_router = AIRouter()
        self.processed = 0
        self.skipped = 0
        self.errors = 0
    
    def read_text_file(self, file_path: Path) -> Optional[str]:
        """Read text content from file"""
        try:
            if file_path.suffix.lower() == '.md':
                # Markdown file
                with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                    return f.read()
            elif file_path.suffix.lower() == '.txt':
                # Plain text file
                with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                    return f.read()
            elif file_path.suffix.lower() == '.pdf':
                # PDF file - requires PDF parser
                try:
                    import PyPDF2
                    text = ""
                    with open(file_path, 'rb') as f:
                        pdf_reader = PyPDF2.PdfReader(f)
                        for page in pdf_reader.pages:
                            text += page.extract_text() + "\n"
                    return text
                except ImportError:
                    logger.warning(f"PyPDF2 not installed, skipping PDF: {file_path.name}")
                    return None
                except Exception as e:
                    logger.error(f"Error reading PDF {file_path.name}: {e}")
                    return None
            else:
                logger.warning(f"Unsupported file type: {file_path.suffix}")
                return None
        except Exception as e:
            logger.error(f"Error reading file {file_path}: {e}")
            return None
    
    def split_text_into_chunks(self, text: str, filename: str) -> List[Dict[str, Any]]:
        """
        Split text into chunks for better retrieval
        Returns list of chunk dictionaries
        """
        chunks = []
        
        # Clean text
        text = text.strip()
        if not text or len(text) < 50:  # Skip very short texts
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
    
    async def generate_embedding(self, text: str) -> Optional[List[float]]:
        """Generate embedding for text"""
        try:
            context = {
                "tenant_id": self.tenant_id,
                "task_class": "embed"
            }
            adapter = self.ai_router.get_embedding_adapter(context)
            result = await adapter.embed(text)
            return result.get("embedding", [])
        except Exception as e:
            logger.error(f"Error generating embedding: {e}")
            return None
    
    async def process_file(self, file_path: Path) -> bool:
        """Process a single file and import to MongoDB"""
        try:
            logger.info(f"📄 Processing: {file_path.name}")
            
            # Read file content
            content = self.read_text_file(file_path)
            if not content:
                logger.warning(f"⚠️  Could not read content from {file_path.name}")
                self.skipped += 1
                return False
            
            # Split into chunks
            chunks = self.split_text_into_chunks(content, file_path.name)
            if not chunks:
                logger.warning(f"⚠️  No chunks extracted from {file_path.name}")
                self.skipped += 1
                return False
            
            logger.info(f"  📝 Extracted {len(chunks)} chunks")
            
            # Generate embeddings for each chunk (batch processing for better performance)
            chunks_with_embeddings = []
            batch_size = 5  # Process multiple chunks concurrently
            
            for i in range(0, len(chunks), batch_size):
                batch = chunks[i:i+batch_size]
                logger.info(f"  🤖 Generating embeddings for chunks {i+1}-{min(i+batch_size, len(chunks))}/{len(chunks)}...")
                
                # Generate embeddings in parallel
                tasks = [self.generate_embedding(chunk['chunk_text']) for chunk in batch]
                embeddings = await asyncio.gather(*tasks, return_exceptions=True)
                
                # Attach embeddings to chunks
                for chunk, embedding in zip(batch, embeddings):
                    if isinstance(embedding, Exception):
                        logger.warning(f"  ⚠️  Failed to generate embedding: {embedding}")
                    elif embedding:
                        chunk['embedding'] = embedding
                        chunks_with_embeddings.append(chunk)
                
                # Small delay between batches to avoid rate limiting
                if i + batch_size < len(chunks):
                    await asyncio.sleep(0.2)
            
            if not chunks_with_embeddings:
                logger.error(f"  ❌ No embeddings generated for {file_path.name}")
                self.errors += 1
                return False
            
            # Prepare document for MongoDB
            # Use relative path from EGI/docs as document_id for uniqueness
            rel_path = file_path.relative_to(Path("/home/fabio/EGI/docs"))
            document_id = f"egi_doc_{rel_path.as_posix().replace('/', '_').replace(' ', '_')}"
            
            # Generate document-level embedding (from title + first chunk)
            doc_text_for_embedding = f"{file_path.stem}\n\n{chunks_with_embeddings[0]['chunk_text'][:500]}"
            logger.info(f"  🤖 Generating document-level embedding...")
            doc_embedding = await self.generate_embedding(doc_text_for_embedding)
            
            document = {
                "document_id": document_id,
                "tenant_id": self.tenant_id,
                "title": file_path.stem,
                "filename": file_path.name,
                "file_path": str(file_path),
                "relative_path": str(rel_path),
                "file_type": file_path.suffix.lower(),
                "document_type": "egi_documentation",
                "protocol_number": None,
                "protocol_date": None,
                "embedding": doc_embedding,  # Document-level embedding for search
                "content": {
                    "raw_text": content[:5000],  # Store first 5000 chars as preview
                    "full_text": content,
                    "chunks": chunks_with_embeddings  # Chunks with their own embeddings
                },
                "metadata": {
                    "source": "EGI/docs",
                    "imported_at": str(Path(__file__).parent.parent.parent),
                    "chunk_count": len(chunks_with_embeddings),
                    "total_chars": len(content),
                    "file_size": file_path.stat().st_size if file_path.exists() else 0
                },
                "status": "active",
                "created_at": None,  # Will be set by MongoDB
                "updated_at": None
            }
            
            # Save to MongoDB
            result_id = MongoDBService.insert_document("documents", document)
            
            if result_id:
                logger.info(f"  ✅ Saved to MongoDB: {len(chunks_with_embeddings)} chunks with embeddings")
                self.processed += 1
                return True
            else:
                logger.error(f"  ❌ Failed to save to MongoDB")
                self.errors += 1
                return False
            
        except Exception as e:
            logger.error(f"  ❌ Error processing {file_path.name}: {e}", exc_info=True)
            self.errors += 1
            return False
    
    async def import_directory(self, docs_dir: Path, tenant_id: int = 1):
        """Import all documents from directory"""
        logger.info("="*70)
        logger.info("🚀 IMPORT DOCUMENTAZIONE EGI → MONGODB")
        logger.info("="*70)
        logger.info(f"📁 Directory: {docs_dir}")
        logger.info(f"🏢 Tenant ID: {tenant_id}")
        logger.info("")
        
        # Reset MongoDB client to ensure fresh connection
        MongoDBService._client = None
        MongoDBService._connected = False
        
        # Force connection attempt
        logger.info("🔌 Tentativo connessione MongoDB...")
        client = MongoDBService.get_client()
        
        # Check MongoDB connection
        if not client or not MongoDBService.is_connected():
            logger.error("❌ MongoDB non connesso! Impossibile importare.")
            logger.info("💡 Avvia MongoDB: cd /home/fabio/NATAN_LOC/docker && docker compose up -d mongodb")
            return False
        
        logger.info("✅ MongoDB connesso con successo!")
        logger.info("")
        
        # Find all files
        supported_extensions = ['.md', '.txt', '.pdf']
        all_files = []
        
        for ext in supported_extensions:
            all_files.extend(docs_dir.rglob(f"*{ext}"))
        
        logger.info(f"📊 Trovati {len(all_files)} file da processare")
        logger.info("")
        
        # Process each file
        for idx, file_path in enumerate(all_files, 1):
            logger.info(f"[{idx}/{len(all_files)}] {file_path.relative_to(docs_dir)}")
            await self.process_file(file_path)
            logger.info("")  # Empty line between files
        
        # Summary
        logger.info("="*70)
        logger.info("📊 RIEPILOGO IMPORT")
        logger.info("="*70)
        logger.info(f"✅ Processati:     {self.processed}")
        logger.info(f"⏭️  Saltati:        {self.skipped}")
        logger.info(f"❌ Errori:         {self.errors}")
        logger.info(f"📝 Totale file:    {len(all_files)}")
        logger.info("="*70)
        
        if self.processed > 0:
            logger.info(f"\n🎉 Import completato! {self.processed} documenti ora disponibili per RAG search!")
        
        return True


async def main():
    """Main import function"""
    egi_docs_dir = Path("/home/fabio/EGI/docs")
    tenant_id = 1  # Default tenant
    
    if not egi_docs_dir.exists():
        logger.error(f"❌ Directory non trovata: {egi_docs_dir}")
        return
    
    importer = DocumentImporter(tenant_id=tenant_id)
    await importer.import_directory(egi_docs_dir, tenant_id)


if __name__ == "__main__":
    asyncio.run(main())

