"""
Retriever Service - Estrae chunks citabili con source_ref precisi
MongoDB vector similarity search
"""

from typing import List, Dict, Any, Optional
import numpy as np
from app.services.mongodb_service import MongoDBService


class RetrieverService:
    """
    Service per retrieval di chunks con source references
    Usa MongoDB vector search per similarity
    """
    
    def __init__(self):
        """Initialize retriever"""
        pass
    
    def retrieve(
        self,
        query_embedding: List[float],
        tenant_id: int,
        limit: int = 10,
        min_score: float = 0.3,
        filters: Optional[Dict[str, Any]] = None
    ) -> List[Dict[str, Any]]:
        """
        Retrieve chunks by vector similarity
        
        Args:
            query_embedding: Query embedding vector
            tenant_id: Tenant ID for isolation
            limit: Max results to return
            min_score: Minimum similarity score
            filters: Optional filters (date_range, document_type, etc.)
        
        Returns:
            List of chunks with source_ref, metadata, similarity score
            (Empty list if MongoDB unavailable)
        """
        from app.services.mongodb_service import MongoDBService
        
        # Check if MongoDB is available - force connection attempt
        if not MongoDBService.is_connected():
            # Try to force connection
            MongoDBService.get_client()
            if not MongoDBService.is_connected():
                # Return empty list - pipeline will work without retrieved chunks
                return []
        
        # Build query filter
        query_filter = {
            "tenant_id": tenant_id,
            "embedding": {"$exists": True}  # Only documents with embeddings
        }
        
        # Add optional filters
        if filters:
            if "document_type" in filters:
                query_filter["document_type"] = filters["document_type"]
            if "date_from" in filters or "date_to" in filters:
                query_filter["created_at"] = {}
                if "date_from" in filters:
                    query_filter["created_at"]["$gte"] = filters["date_from"]
                if "date_to" in filters:
                    query_filter["created_at"]["$lte"] = filters["date_to"]
        
        # Get candidates from MongoDB - ensure connection first
        import logging
        logger = logging.getLogger(__name__)
        try:
            candidates = MongoDBService.find_documents("documents", query_filter)
            if not candidates:
                # CRITICAL: Empty database = no documents to retrieve
                logger.warning(f"⚠️ CRITICAL: No documents found in MongoDB with filter: {query_filter}. Database may be empty.")
                return []  # Return empty - verifica postuma bloccherà risposta
            
            # Log retrieval info for debugging
            logger.info(f"🔍 Retriever: Found {len(candidates)} candidate documents for tenant {tenant_id}")
        except Exception as e:
            logger.error(f"❌ CRITICAL: Error retrieving documents from MongoDB: {e}")
            return []  # Return empty on error
        
        # Calculate cosine similarity with minimum threshold
        results = []
        query_vector = np.array(query_embedding)
        # CRITICAL: Lower threshold for documents with only "oggetto" (subject) - they have less semantic content
        # Once documents have full PDF text, we can raise this back to 0.3
        MIN_SIMILARITY_THRESHOLD = 0.2  # Lowered from 0.3 to allow retrieval of documents with only "oggetto"
        
        for doc in candidates:
            # Check if document has chunks with embeddings (preferred) or document-level embedding
            chunks = doc.get("content", {}).get("chunks", [])
            doc_embedding = doc.get("embedding")
            
            if chunks and len(chunks) > 0:
                # Process chunks (each chunk has its own embedding)
                for chunk in chunks:
                    chunk_embedding = chunk.get("embedding")
                    if not chunk_embedding:
                        continue
                    
                    chunk_vector = np.array(chunk_embedding)
                    similarity = float(np.dot(query_vector, chunk_vector) / (np.linalg.norm(query_vector) * np.linalg.norm(chunk_vector)))
                    
                    # CRITICAL: Use effective threshold (lowered to 0.2 for documents with only "oggetto")
                    effective_threshold = max(min_score, MIN_SIMILARITY_THRESHOLD)
                    if similarity < effective_threshold:
                        logger.debug(f"⚠️ Chunk similarity {similarity:.3f} below threshold {effective_threshold} for doc {doc.get('document_id', 'N/A')[:30]}")
                    
                    if similarity >= effective_threshold:
                        # Log successful retrieval
                        logger.debug(f"✅ Chunk retrieved: similarity {similarity:.3f} >= {effective_threshold} for doc {doc.get('document_id', 'N/A')[:30]}")
                        results.append({
                            "document_id": doc.get("document_id"),
                            "chunk_index": chunk.get("chunk_index"),
                            "chunk_text": chunk.get("chunk_text", ""),
                            "similarity": similarity,
                            "source_ref": {
                                "document_id": doc.get("document_id"),
                                "title": doc.get("title", doc.get("filename", "Documento")),
                                "filename": doc.get("filename"),
                                "relative_path": doc.get("relative_path"),
                                "url": f"#doc-{doc.get('document_id')}",  # Internal document reference
                                "chunk_index": chunk.get("chunk_index"),
                                "page_number": chunk.get("page_number"),  # If available
                            },
                            "metadata": {
                                "document_type": doc.get("document_type"),
                                "file_type": doc.get("file_type"),
                                **chunk.get("metadata", {})
                            }
                        })
            elif doc_embedding:
                # Fallback to document-level embedding (when no chunks available)
                doc_vector = np.array(doc_embedding)
                
                # Cosine similarity
                similarity = self._cosine_similarity(query_vector, doc_vector)
                
                # CRITICAL: Use higher threshold to avoid irrelevant chunks
                effective_threshold = max(min_score, MIN_SIMILARITY_THRESHOLD)
                if similarity >= effective_threshold:
                    # Get source reference
                    source_ref = self._build_source_ref(doc)
                    
                    # Try to get full_text from content dict, fallback to raw_text
                    content = doc.get("content", {})
                    chunk_text = ""
                    if isinstance(content, dict):
                        chunk_text = content.get("full_text", content.get("raw_text", ""))
                    elif isinstance(content, str):
                        chunk_text = content
                    
                    # Limit chunk_text to first 2000 chars for display
                    if len(chunk_text) > 2000:
                        chunk_text = chunk_text[:2000] + "..."
                    
                    results.append({
                        "chunk_id": str(doc.get("_id", "")),
                        "chunk_index": 0,  # Document-level, no chunk index
                        "chunk_text": chunk_text,
                        "document_id": doc.get("document_id"),
                        "similarity": float(similarity),
                        "source_ref": source_ref,
                        "metadata": {
                            "document_type": doc.get("document_type"),
                            "file_type": doc.get("file_type"),
                            **doc.get("metadata", {})
                        }
                    })
        
        # Sort by similarity (descending)
        results.sort(key=lambda x: x.get("similarity", x.get("similarity_score", 0)), reverse=True)
        
        # Log final results for debugging
        if results:
            logger.info(f"✅ Retriever: Returning {len(results[:limit])} chunks (top similarity: {results[0].get('similarity', 0):.3f})")
        else:
            logger.warning(f"⚠️ Retriever: No chunks found above threshold {MIN_SIMILARITY_THRESHOLD} for tenant {tenant_id}")
        
        # Limit results
        return results[:limit]
    
    @staticmethod
    def _cosine_similarity(vec1: np.ndarray, vec2: np.ndarray) -> float:
        """
        Calculate cosine similarity between two vectors
        
        Args:
            vec1: First vector
            vec2: Second vector
        
        Returns:
            Cosine similarity (0.0 - 1.0)
        """
        dot_product = np.dot(vec1, vec2)
        norm1 = np.linalg.norm(vec1)
        norm2 = np.linalg.norm(vec2)
        
        if norm1 == 0 or norm2 == 0:
            return 0.0
        
        return dot_product / (norm1 * norm2)
    
    def _build_source_ref(self, doc: Dict[str, Any]) -> Dict[str, Any]:
        """
        Build source reference with precise URL and page
        
        Args:
            doc: Document dict
        
        Returns:
            Source reference dict with url, page, title, etc.
        """
        source_id = doc.get("source_id")
        if source_id:
            sources = MongoDBService.find_documents("sources", {
                "_id": source_id,
                "tenant_id": doc.get("tenant_id")
            })
            source = sources[0] if sources else None
            if source:
                url = source.get("url", "")
                page = doc.get("content", {}).get("page_number")
                
                # Add page anchor if present
                if page and "#" not in url:
                    url = f"{url}#page={page}"
                
                return {
                    "source_id": str(source_id),
                    "url": url,
                    "title": source.get("title", ""),
                    "type": source.get("type", ""),
                    "page": page,
                    "hash": source.get("hash", "")
                }
        
        # Fallback if no source
        return {
            "source_id": None,
            "url": "",
            "title": doc.get("document_id", ""),
            "type": "unknown",
            "page": None,
            "hash": None
        }
    
    def get_source_by_id(self, source_id: str, tenant_id: int) -> Optional[Dict[str, Any]]:
        """
        Get source document by ID
        
        Args:
            source_id: Source ID
            tenant_id: Tenant ID
        
        Returns:
            Source dict or None
        """
        sources = MongoDBService.find_documents("sources", {
            "_id": source_id,
            "tenant_id": tenant_id
        })
        return sources[0] if sources else None

