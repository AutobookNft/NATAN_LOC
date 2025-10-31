"""Health check router"""

from fastapi import APIRouter, HTTPException
from app.services.mongodb_service import MongoDBService
import logging

logger = logging.getLogger(__name__)
router = APIRouter()

@router.get("/healthz")
async def health_check():
    """
    Health check endpoint
    
    Checks:
    - Service status
    - MongoDB connectivity
    """
    health_status = {
        "status": "ok",
        "service": "natan-ai-gateway",
        "version": "1.0.0",
        "checks": {}
    }
    
    # Check MongoDB
    try:
        MongoDBService.get_client().admin.command('ping')
        health_status["checks"]["mongodb"] = "ok"
    except Exception as e:
        logger.error(f"MongoDB health check failed: {e}")
        health_status["checks"]["mongodb"] = "error"
        health_status["status"] = "degraded"
    
    # If MongoDB is down, return 503
    if health_status["checks"].get("mongodb") == "error":
        raise HTTPException(status_code=503, detail=health_status)
    
    return health_status

