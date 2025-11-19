"""Chat router - LLM inference con RAG-Fortress Zero-Hallucination"""

from fastapi import APIRouter, HTTPException
from pydantic import BaseModel
from typing import List, Optional
from app.services.ai_router import AIRouter
from app.services.rag_fortress.pipeline import rag_fortress
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
    use_rag_fortress: bool = True  # Usa RAG-Fortress per default

class ChatResponse(BaseModel):
    message: str
    model: str
    usage: dict
    citations: List[str] = []
    urs_score: Optional[float] = None
    urs_explanation: Optional[str] = None
    claims: List[str] = []
    sources: List[str] = []
    hallucinations_found: List[str] = []
    gaps_detected: List[str] = []

@router.post("/chat", response_model=ChatResponse)
async def chat_inference(request: ChatRequest):
    """
    LLM inference con RAG-Fortress Zero-Hallucination Pipeline
    
    Se use_rag_fortress=True (default), usa la pipeline RAG-Fortress completa.
    Altrimenti usa il metodo tradizionale con Policy Engine.
    """
    try:
        # Se RAG-Fortress è abilitato, usa la pipeline completa
        if request.use_rag_fortress:
            # Estrai ultimo messaggio utente (domanda)
            user_messages = [msg for msg in request.messages if msg.role == "user"]
            if not user_messages:
                raise HTTPException(status_code=400, detail="Nessun messaggio utente trovato")
            
            question = user_messages[-1].content
            
            # Esegui pipeline RAG-Fortress
            start_time = time.time()
            result = await rag_fortress(
                question=question,
                tenant_id=str(request.tenant_id),
                user_id=None  # TODO: estrai da auth se disponibile
            )
            elapsed_ms = int((time.time() - start_time) * 1000)
            
            # Costruisci risposta con metadata RAG-Fortress
            return ChatResponse(
                message=result.get("answer", ""),
                model="rag-fortress-pipeline",
                usage={
                    "prompt_tokens": 0,  # TODO: calcola da pipeline
                    "completion_tokens": 0,
                    "total_tokens": 0,
                    "elapsed_ms": elapsed_ms
                },
                citations=result.get("claims_used", []),
                urs_score=result.get("urs_score", 0.0),
                urs_explanation=result.get("urs_explanation", ""),
                claims=result.get("claims_used", []),
                sources=result.get("sources", []),
                hallucinations_found=result.get("hallucinations_found", []),
                gaps_detected=result.get("gaps_detected", [])
            )
        
        # Metodo tradizionale (fallback)
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

