"""RAG router - Vector similarity search"""

from fastapi import APIRouter, HTTPException
from pydantic import BaseModel
from typing import List, Optional
from app.services.ai_router import AIRouter
from app.services.vector_search import VectorSearchService
import time

router = APIRouter()
ai_router = AIRouter()

class RagSearchRequest(BaseModel):
    query: str
    tenant_id: int
    limit: Optional[int] = 10
    min_score: Optional[float] = 0.0
    collection_name: Optional[str] = "documents"

class RagResult(BaseModel):
    document_id: str
    score: float
    metadata: dict

class RagSearchResponse(BaseModel):
    results: List[RagResult]
    total_found: int
    search_time_ms: int

@router.post("/rag/search", response_model=RagSearchResponse)
async def rag_search(request: RagSearchRequest):
    """
    Vector similarity search in MongoDB using cosine similarity
    
    1. Generate embedding for query using Policy Engine
    2. Search documents/chunks in MongoDB using cosine similarity
    3. Return top-K results sorted by similarity score
    """
    try:
        start_time = time.time()
        
        # Build context for policy engine
        context = {
            "tenant_id": request.tenant_id,
            "task_class": "RAG"
        }
        
        # Generate query embedding
        adapter = ai_router.get_embedding_adapter(context)
        embed_result = await adapter.embed(request.query)
        query_vector = embed_result["embedding"]
        
        # Vector similarity search
        search_results = VectorSearchService.knn_search(
            query_vector=query_vector,
            tenant_id=request.tenant_id,
            collection_name=request.collection_name,
            k=request.limit or 10,
            min_score=request.min_score or 0.0
        )
        
        elapsed_ms = int((time.time() - start_time) * 1000)
        
        # Format results
        formatted_results = [
            RagResult(
                document_id=result["document_id"],
                score=result["score"],
                metadata=result["metadata"]
            )
            for result in search_results
        ]
        
        return RagSearchResponse(
            results=formatted_results,
            total_found=len(formatted_results),
            search_time_ms=elapsed_ms
        )
    
    except ValueError as e:
        raise HTTPException(status_code=400, detail=str(e))
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"RAG search failed: {str(e)}")

