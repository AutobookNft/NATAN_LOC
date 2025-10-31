"""Embeddings router - Generate text embeddings"""

from fastapi import APIRouter, HTTPException
from pydantic import BaseModel
from app.services.ai_router import AIRouter
import time

router = APIRouter()
ai_router = AIRouter()

class EmbedRequest(BaseModel):
    text: str
    tenant_id: int
    model: str = None  # Optional, will use policy engine if None
    task_class: str = "embed"  # For policy selection

class EmbedResponse(BaseModel):
    embedding: list[float]
    model: str
    dimensions: int
    tokens: int

@router.post("/embed", response_model=EmbedResponse)
async def generate_embedding(request: EmbedRequest):
    """
    Generate text embedding using configured model (OpenAI/Ollama)
    
    Uses Policy Engine to select appropriate embedding model based on context.
    """
    try:
        # Build context for policy engine
        context = {
            "tenant_id": request.tenant_id,
            "task_class": request.task_class
        }
        
        # Get embedding adapter
        adapter = ai_router.get_embedding_adapter(context)
        
        # Generate embedding
        start_time = time.time()
        result = await adapter.embed(request.text)
        elapsed_ms = int((time.time() - start_time) * 1000)
        
        return EmbedResponse(
            embedding=result["embedding"],
            model=result["model"],
            dimensions=result["dimensions"],
            tokens=result.get("tokens", 0)
        )
    
    except ValueError as e:
        raise HTTPException(status_code=400, detail=str(e))
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Embedding generation failed: {str(e)}")

