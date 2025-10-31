"""Vector similarity search (cosine similarity)"""
import numpy as np
from typing import List, Dict, Any, Tuple, Optional
from app.services.mongodb_service import MongoDBService

def cosine_similarity(vec1: List[float], vec2: List[float]) -> float:
    """
    Calculate cosine similarity between two vectors
    
    Args:
        vec1: First vector
        vec2: Second vector
    
    Returns:
        Cosine similarity score (0-1)
    """
    vec1_array = np.array(vec1)
    vec2_array = np.array(vec2)
    
    dot_product = np.dot(vec1_array, vec2_array)
    norm1 = np.linalg.norm(vec1_array)
    norm2 = np.linalg.norm(vec2_array)
    
    if norm1 == 0 or norm2 == 0:
        return 0.0
    
    return float(dot_product / (norm1 * norm2))

class VectorSearchService:
    """Vector similarity search service using cosine similarity"""
    
    @staticmethod
    def knn_search(
        query_vector: List[float],
        tenant_id: int,
        collection_name: str = "documents",
        k: int = 10,
        min_score: float = 0.0
    ) -> List[Dict[str, Any]]:
        """
        K-nearest neighbors search using cosine similarity
        
        Args:
            query_vector: Query embedding vector
            tenant_id: Tenant ID for filtering
            collection_name: MongoDB collection name
            k: Number of results to return
            min_score: Minimum similarity score threshold
        
        Returns:
            List of documents with similarity scores, sorted by score (desc)
        """
        # Get all documents with embeddings for this tenant
        filter_query = {
            "tenant_id": tenant_id,
            "embedding": {"$exists": True, "$ne": None}
        }
        
        documents = MongoDBService.find_documents(
            collection_name,
            filter_query,
            limit=None  # Need all for comparison
        )
        
        # Calculate similarities
        results = []
        for doc in documents:
            if "embedding" not in doc or not doc["embedding"]:
                continue
            
            embedding = doc.get("embedding", [])
            if not isinstance(embedding, list) or len(embedding) == 0:
                continue
            
            score = cosine_similarity(query_vector, embedding)
            
            if score >= min_score:
                results.append({
                    "document_id": doc.get("document_id"),
                    "score": score,
                    "metadata": {
                        "title": doc.get("title"),
                        "document_type": doc.get("document_type"),
                        "protocol_number": doc.get("protocol_number"),
                        "protocol_date": str(doc.get("protocol_date", "")),
                        "content_preview": doc.get("content", {}).get("raw_text", "")[:200] if isinstance(doc.get("content"), dict) else ""
                    },
                    "document": doc  # Full document for reference
                })
        
        # Sort by score descending
        results.sort(key=lambda x: x["score"], reverse=True)
        
        # Return top K
        return results[:k]
    
    @staticmethod
    def search_chunks(
        query_vector: List[float],
        tenant_id: int,
        document_id: Optional[str] = None,
        k: int = 5,
        min_score: float = 0.0
    ) -> List[Dict[str, Any]]:
        """
        Search within document chunks
        
        Args:
            query_vector: Query embedding vector
            tenant_id: Tenant ID
            document_id: Optional specific document ID
            k: Number of chunks to return
            min_score: Minimum similarity score
        
        Returns:
            List of chunks with similarity scores
        """
        filter_query = {
            "tenant_id": tenant_id,
            "chunks": {"$exists": True}
        }
        
        if document_id:
            filter_query["document_id"] = document_id
        
        documents = MongoDBService.find_documents("documents", filter_query)
        
        results = []
        for doc in documents:
            chunks = doc.get("chunks", [])
            if not isinstance(chunks, list):
                continue
            
            for chunk in chunks:
                if "embedding" not in chunk or not chunk.get("embedding"):
                    continue
                
                score = cosine_similarity(query_vector, chunk["embedding"])
                
                if score >= min_score:
                    results.append({
                        "document_id": doc.get("document_id"),
                        "chunk_index": chunk.get("chunk_index"),
                        "chunk_text": chunk.get("chunk_text", "")[:500],
                        "score": score,
                        "metadata": {
                            "page_number": chunk.get("page_number"),
                            "tokens": chunk.get("tokens")
                        }
                    })
        
        # Sort by score descending
        results.sort(key=lambda x: x["score"], reverse=True)
        
        return results[:k]

