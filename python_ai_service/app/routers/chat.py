"""Chat router - LLM inference"""

from fastapi import APIRouter, HTTPException
from pydantic import BaseModel
from typing import List
from app.services.ai_router import AIRouter
import time

router = APIRouter()
ai_router = AIRouter()

class Message(BaseModel):
    role: str
    content: str

class ChatRequest(BaseModel):
    messages: List[Message]
    tenant_id: int
    persona: str = "strategic"
    model: str = None  # Optional, will use policy engine if None
    task_class: str = "chat"  # For policy selection
    temperature: float = None  # Optional override
    max_tokens: int = None  # Optional override

class ChatResponse(BaseModel):
    message: str
    model: str
    usage: dict
    citations: List[str] = []

@router.post("/chat", response_model=ChatResponse)
async def chat_inference(request: ChatRequest):
    """
    LLM inference with persona (Claude/OpenAI/Ollama)
    
    Uses Policy Engine to select appropriate chat model based on context.
    """
    try:
        # Build context for policy engine
        context = {
            "tenant_id": request.tenant_id,
            "persona": request.persona,
            "task_class": request.task_class
        }
        
        # Get chat adapter
        adapter = ai_router.get_chat_adapter(context)
        
        # Prepare messages
        messages = [{"role": msg.role, "content": msg.content} for msg in request.messages]
        
        # Prepare options
        options = {}
        if request.temperature is not None:
            options["temperature"] = request.temperature
        if request.max_tokens is not None:
            options["max_tokens"] = request.max_tokens
        
        # Generate response
        start_time = time.time()
        result = await adapter.generate(messages, **options)
        elapsed_ms = int((time.time() - start_time) * 1000)
        
        return ChatResponse(
            message=result["content"],
            model=result["model"],
            usage=result["usage"],
            citations=[]  # Citations added by USE pipeline
        )
    
    except ValueError as e:
        raise HTTPException(status_code=400, detail=str(e))
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Chat inference failed: {str(e)}")

