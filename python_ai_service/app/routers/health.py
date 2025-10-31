"""Health check router"""

from fastapi import APIRouter

router = APIRouter()

@router.get("/healthz")
async def health_check():
    """Health check endpoint"""
    return {"status": "ok", "service": "natan-ai-gateway", "version": "1.0.0"}

