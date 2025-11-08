"""
Diagnostic Router - Endpoint per diagnostica retrieval e debugging
"""

from fastapi import APIRouter, HTTPException
from pydantic import BaseModel
from typing import Optional, List, Dict, Any
import logging
import asyncio
import json
from bson import json_util
from app.services.mongodb_service import MongoDBService
from app.services.ai_router import AIRouter
from app.services.retriever_service import RetrieverService
from app.services.use_pipeline import UsePipeline

logger = logging.getLogger(__name__)

router = APIRouter()

class DiagnosticRequest(BaseModel):
    tenant_id: int
    query: Optional[str] = None

class DiagnosticResponse(BaseModel):
    success: bool
    mongodb_connected: bool
    documents_total: int
    documents_with_embeddings: int
    documents_with_chunk_embeddings: int
    document_types: Dict[str, int]
    query_embedding_generated: bool
    chunks_retrieved: int
    chunks_details: List[Dict[str, Any]]
    validation_results: Optional[Dict[str, Any]] = None
    error: Optional[str] = None

@router.post("/diagnostic/retrieval", response_model=DiagnosticResponse)
async def diagnostic_retrieval(request: DiagnosticRequest):
    """
    Diagnostica completa del retrieval per un tenant
    Mostra documenti, embeddings, chunks recuperati e validazione
    """
    try:
        result = {
            "success": True,
            "mongodb_connected": False,
            "documents_total": 0,
            "documents_with_embeddings": 0,
            "documents_with_chunk_embeddings": 0,
            "document_types": {},
            "query_embedding_generated": False,
            "chunks_retrieved": 0,
            "chunks_details": [],
            "validation_results": None,
            "error": None
        }
        
        # 1. Verifica connessione MongoDB
        if not MongoDBService.is_connected():
            result["error"] = "MongoDB non connesso"
            return DiagnosticResponse(**result)
        result["mongodb_connected"] = True
        
        # 2. Verifica documenti totali
        all_docs = MongoDBService.find_documents("documents", {"tenant_id": request.tenant_id}, limit=None)
        result["documents_total"] = len(all_docs)
        
        if len(all_docs) == 0:
            result["error"] = f"Nessun documento trovato per tenant_id={request.tenant_id}"
            return DiagnosticResponse(**result)
        
        # 3. Verifica embeddings
        docs_with_doc_embedding = [d for d in all_docs if d.get("embedding")]
        result["documents_with_embeddings"] = len(docs_with_doc_embedding)
        
        docs_with_chunk_embeddings = []
        for doc in all_docs:
            chunks = doc.get("content", {}).get("chunks", [])
            if chunks and any(c.get("embedding") for c in chunks):
                docs_with_chunk_embeddings.append(doc)
        result["documents_with_chunk_embeddings"] = len(docs_with_chunk_embeddings)
        
        # 4. Tipi di documento
        doc_types = {}
        for doc in all_docs:
            doc_type = doc.get("document_type", "unknown")
            doc_types[doc_type] = doc_types.get(doc_type, 0) + 1
        result["document_types"] = doc_types
        
        # 5. Test query se fornita
        if request.query:
            # Genera embedding query
            try:
                ai_router = AIRouter()
                context = {
                    "tenant_id": request.tenant_id,
                    "task_class": "RAG"
                }
                adapter = ai_router.get_embedding_adapter(context)
                embed_result = await adapter.embed(request.query)
                query_embedding = embed_result["embedding"]
                result["query_embedding_generated"] = True
            except Exception as e:
                result["error"] = f"Errore generazione embedding: {str(e)}"
                return DiagnosticResponse(**result)
            
            # Test retriever
            retriever = RetrieverService()
            chunks = retriever.retrieve(
                query_embedding=query_embedding,
                tenant_id=request.tenant_id,
                limit=20,
                min_score=0.15
            )
            
            result["chunks_retrieved"] = len(chunks)
            
            # Dettagli chunks
            chunks_details = []
            for i, chunk in enumerate(chunks[:10], 1):  # Primi 10 chunks
                chunk_text = chunk.get("chunk_text") or chunk.get("text") or chunk.get("content") or ""
                chunk_detail = {
                    "index": i,
                    "keys": list(chunk.keys()),
                    "document_id": chunk.get("document_id", "N/A")[:50],
                    "similarity": chunk.get("similarity", chunk.get("score", 0)),
                    "chunk_text_length": len(str(chunk_text)),
                    "chunk_text_preview": str(chunk_text)[:200] if chunk_text else "EMPTY",
                    "chunk_text_type": type(chunk_text).__name__,
                    "chunk_index": chunk.get("chunk_index"),
                }
                chunks_details.append(chunk_detail)
            
            result["chunks_details"] = chunks_details
            
            # 6. Simula validazione pipeline USE
            if chunks:
                validation = {
                    "total_chunks": len(chunks),
                    "skipped_empty": 0,
                    "skipped_too_short": 0,
                    "skipped_error_indicators": 0,
                    "accepted": 0
                }
                
                for chunk in chunks:
                    chunk_text = chunk.get("chunk_text") or chunk.get("text") or chunk.get("content") or ""
                    
                    if not chunk_text:
                        validation["skipped_empty"] += 1
                        continue
                    
                    chunk_text_stripped = str(chunk_text).strip()
                    
                    if len(chunk_text_stripped) < 20:
                        validation["skipped_too_short"] += 1
                        continue
                    
                    error_indicators = [
                        "nessun documento", "no document", "documento non trovato", "not found",
                        "empty database", "database vuoto", "no data available", "nessun dato disponibile",
                        "placeholder", "example", "test data", "dummy data"
                    ]
                    chunk_lower = chunk_text_stripped.lower()
                    if any(indicator in chunk_lower for indicator in error_indicators):
                        validation["skipped_error_indicators"] += 1
                        continue
                    
                    validation["accepted"] += 1
                
                result["validation_results"] = validation
        
        return DiagnosticResponse(**result)
        
    except Exception as e:
        logger.error(f"Diagnostic error: {e}", exc_info=True)
        raise HTTPException(
            status_code=500,
            detail=f"Diagnostic error: {str(e)}"
        )

@router.get("/diagnostic/mongodb/{tenant_id}")
async def diagnostic_mongodb(tenant_id: int):
    """
    Diagnostica rapida MongoDB per un tenant
    """
    try:
        if not MongoDBService.is_connected():
            return {
                "success": False,
                "error": "MongoDB non connesso"
            }
        
        all_docs = MongoDBService.find_documents("documents", {"tenant_id": tenant_id}, limit=5)
        
        sample_doc = all_docs[0] if all_docs else None
        
        result = {
            "success": True,
            "tenant_id": tenant_id,
            "documents_found": len(all_docs),
            "sample_document_structure": None
        }
        
        if sample_doc:
            # Mostra struttura documento (senza embedding completo)
            doc_structure = {
                "document_id": sample_doc.get("document_id"),
                "document_type": sample_doc.get("document_type"),
                "has_embedding": bool(sample_doc.get("embedding")),
                "has_chunks": bool(sample_doc.get("content", {}).get("chunks")),
                "chunks_count": len(sample_doc.get("content", {}).get("chunks", [])),
                "first_chunk_keys": None
            }
            
            chunks = sample_doc.get("content", {}).get("chunks", [])
            if chunks and len(chunks) > 0:
                first_chunk = chunks[0]
                doc_structure["first_chunk_keys"] = list(first_chunk.keys())
                doc_structure["first_chunk_has_text"] = bool(first_chunk.get("chunk_text"))
                doc_structure["first_chunk_text_length"] = len(str(first_chunk.get("chunk_text", "")))
                doc_structure["first_chunk_has_embedding"] = bool(first_chunk.get("embedding"))
            
            result["sample_document_structure"] = doc_structure
        
        return result
        
    except Exception as e:
        logger.error(f"MongoDB diagnostic error: {e}", exc_info=True)
        raise HTTPException(
            status_code=500,
            detail=f"MongoDB diagnostic error: {str(e)}"
        )


@router.get("/diagnostic/document/{tenant_id}/{document_id}")
async def diagnostic_document(tenant_id: int, document_id: str):
    """
    Restituisce il documento completo da MongoDB per il tenant indicato
    """
    try:
        if not MongoDBService.is_connected():
            return {
                "success": False,
                "error": "MongoDB non connesso"
            }
        
        documents = MongoDBService.find_documents(
            "documents",
            {"tenant_id": tenant_id, "document_id": document_id},
            limit=1
        )
        
        if not documents:
            return {
                "success": False,
                "error": "Documento non trovato",
                "document_id": document_id
            }
        
        document_serialized = json.loads(json_util.dumps(documents[0]))
        
        return {
            "success": True,
            "document_id": document_id,
            "document": document_serialized
        }
    
    except Exception as e:
        logger.error(f"MongoDB document diagnostic error: {e}", exc_info=True)
        raise HTTPException(
            status_code=500,
            detail=f"MongoDB document diagnostic error: {str(e)}"
        )

