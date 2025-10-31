"""Chat router - LLM inference"""

from fastapi import APIRouter
from pydantic import BaseModel
from typing import List

router = APIRouter()

class Message(BaseModel):
    role: str
    content: str

class ChatRequest(BaseModel):
    messages: List[Message]
    tenant_id: int
    persona: str
    model: str = "anthropic.sonnet-3.5"

class ChatResponse(BaseModel):
    message: str
    model: str
    usage: dict
    citations: List[str] = []

@router.post("/chat", response_model=ChatResponse)
async def chat_inference(request: ChatRequest):
    """
    LLM inference with persona
    TODO: Implement chat with USE pipeline
    """
    # Placeholder - will be implemented with USE pipeline
    return ChatResponse(
        message="",
        model=request.model,
        usage={"input_tokens": 0, "output_tokens": 0},
        citations=[]
    )

