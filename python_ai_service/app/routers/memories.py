"""
Memory Service - Gestione memorie utente con embedding
"""

from fastapi import APIRouter, HTTPException
from pydantic import BaseModel
from typing import Optional
import logging
from app.services.mongodb_service import MongoDBService

router = APIRouter()
logger = logging.getLogger(__name__)


class GenerateEmbeddingRequest(BaseModel):
    memory_id: int
    content: str
    user_id: Optional[int] = None


class UpdateMemoryEmbeddingRequest(BaseModel):
    memory_id: int
    embedding: list


@router.post("/memories/generate-embedding")
async def generate_memory_embedding(request: GenerateEmbeddingRequest):
    """
    Genera embedding per una memoria utente
    """
    try:
        from app.services.ai_router import AIRouter
        
        logger.info(f"🧠 Generating embedding for memory {request.memory_id}")
        
        # Get embedding adapter
        ai_router = AIRouter()
        context = {
            "task": "embedding",
            "model": "openai.text-embedding-3-small"
        }
        adapter = ai_router.get_embedding_adapter(context)
        
        # Generate embedding
        result = await adapter.embed(request.content)
        embedding = result["embedding"]
        
        # Store embedding in MongoDB
        if MongoDBService.is_connected():
            from datetime import datetime
            # Update or insert memory document
            doc = {
                "memory_id": request.memory_id,
                "content": request.content,
                "embedding": embedding,
                "embedding_model": "openai.text-embedding-3-small",
                "updated_at": datetime.utcnow(),
                "is_active": True
            }
            if request.user_id:
                doc["user_id"] = request.user_id
                
            MongoDBService.get_collection("user_memories").update_one(
                {"memory_id": request.memory_id},
                {"$set": doc},
                upsert=True
            )
            
            logger.info(f"✅ Embedding generated and stored for memory {request.memory_id}")
            
            return {
                "success": True,
                "memory_id": request.memory_id,
                "embedding_dimensions": len(embedding)
            }
        else:
            logger.warning(f"⚠️ MongoDB not connected, embedding generated but not stored")
            return {
                "success": True,
                "memory_id": request.memory_id,
                "embedding": embedding,
                "warning": "MongoDB not connected, embedding not persisted"
            }
            
    except Exception as e:
        logger.error(f"❌ Error generating memory embedding: {e}")
        raise HTTPException(
            status_code=500,
            detail=f"Failed to generate embedding: {str(e)}"
        )


@router.post("/memories/update-embedding")
async def update_memory_embedding(request: UpdateMemoryEmbeddingRequest):
    """
    Aggiorna embedding esistente per una memoria
    """
    try:
        if not MongoDBService.is_connected():
            raise HTTPException(
                status_code=503,
                detail="MongoDB not connected"
            )
        
        # Update embedding in MongoDB
        result = MongoDBService.get_collection("user_memories").update_one(
            {"memory_id": request.memory_id},
            {
                "$set": {
                    "embedding": request.embedding,
                    "updated_at": MongoDBService._get_current_timestamp()
                }
            }
        )
        
        if result.modified_count > 0:
            logger.info(f"✅ Embedding updated for memory {request.memory_id}")
            return {
                "success": True,
                "memory_id": request.memory_id
            }
        else:
            logger.warning(f"⚠️ Memory {request.memory_id} not found in MongoDB")
            return {
                "success": False,
                "message": "Memory not found"
            }
            
    except Exception as e:
        logger.error(f"❌ Error updating memory embedding: {e}")
        raise HTTPException(
            status_code=500,
            detail=f"Failed to update embedding: {str(e)}"
        )


@router.get("/memories/test")
async def test_memory_system():
    """
    Test endpoint per verificare il sistema di memorie
    """
    try:
        if not MongoDBService.is_connected():
            return {
                "status": "warning",
                "message": "MongoDB not connected",
                "mongodb_connected": False
            }
        
        # Count memories in MongoDB
        count = MongoDBService.get_collection("user_memories").count_documents({})
        
        # Sample memory
        sample = MongoDBService.get_collection("user_memories").find_one({})
        
        return {
            "status": "ok",
            "mongodb_connected": True,
            "total_memories": count,
            "has_sample": sample is not None,
            "sample_has_embedding": sample.get("embedding") is not None if sample else False
        }
        
    except Exception as e:
        logger.error(f"❌ Error testing memory system: {e}")
        raise HTTPException(
            status_code=500,
            detail=f"Test failed: {str(e)}"
        )
