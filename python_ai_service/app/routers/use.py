"""USE Pipeline router - Ultra Semantic Engine endpoint"""

from fastapi import APIRouter, HTTPException
from pydantic import BaseModel
from typing import List, Optional
from app.services.use_pipeline import UsePipeline

router = APIRouter()

class UseQueryRequest(BaseModel):
    question: str
    tenant_id: int
    persona: str = "strategic"
    model: str = "anthropic.sonnet-3.5"
    query_embedding: Optional[List[float]] = None
    debug: bool = False

class UseQueryResponse(BaseModel):
    status: str
    question: Optional[str] = None
    answer: Optional[str] = None  # Natural language answer (main semantic response)
    answer_id: Optional[str] = None
    verified_claims: Optional[List[dict]] = None
    blocked_claims: Optional[List[dict]] = None
    avg_urs: Optional[float] = None
    verification_status: Optional[str] = None
    chunks_used: Optional[List[dict]] = None
    model_used: Optional[str] = None
    tokens_used: Optional[dict] = None
    reason: Optional[str] = None
    message: Optional[str] = None
    debug: Optional[dict] = None

@router.post("/use/query", response_model=UseQueryResponse)
async def process_use_query(request: UseQueryRequest):
    """
    Process query through USE pipeline
    Complete flow: Classifier -> Router -> Retriever -> Neurale -> Verifier
    """
    try:
        pipeline = UsePipeline()
        result = await pipeline.process_query(
            question=request.question,
            tenant_id=request.tenant_id,
            persona=request.persona,
            model=request.model,
            query_embedding=request.query_embedding,
            debug=request.debug
        )
        
        return UseQueryResponse(**result)
    
    except Exception as e:
        raise HTTPException(
            status_code=500,
            detail=f"USE Pipeline error: {str(e)}"
        )







