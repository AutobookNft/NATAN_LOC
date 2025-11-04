"""USE Audit router - Save sources, claims, query_audit to MongoDB"""

from fastapi import APIRouter, HTTPException
from pydantic import BaseModel
from typing import List, Dict, Any, Optional
from app.services.mongodb_service import MongoDBService
from datetime import datetime
import uuid

router = APIRouter()

class SourceSaveRequest(BaseModel):
    chunks: List[Dict[str, Any]]
    tenant_id: int

class ClaimSaveRequest(BaseModel):
    claims: List[Dict[str, Any]]
    answer_id: str
    tenant_id: int

class QueryAuditSaveRequest(BaseModel):
    tenant_id: int
    user_id: int
    question: str
    intent: str
    classifier_conf: float
    router_action: str
    status: str
    verification_status: str
    avg_urs: Optional[float] = None
    verified_claims_count: int = 0
    blocked_claims_count: int = 0
    answer_id: Optional[str] = None
    model_used: Optional[str] = None
    tokens_used: Optional[Dict[str, int]] = None

@router.post("/audit/sources")
async def save_sources(request: SourceSaveRequest):
    """Save sources (chunks) to MongoDB sources collection"""
    try:
        saved_ids = []
        
        for chunk in request.chunks:
            source_doc = {
                "tenant_id": request.tenant_id,
                "entity_id": chunk.get("document_id") or chunk.get("chunk_id"),
                "document_id": chunk.get("document_id"),
                "chunk_id": chunk.get("chunk_id"),
                "source_ref": chunk.get("source_ref", {}),
                "text": chunk.get("text", ""),
                "similarity_score": chunk.get("similarity_score"),
                "metadata": chunk.get("metadata", {}),
                "created_at": datetime.utcnow()
            }
            
            # Upsert by tenant_id + entity_id
            filter_query = {
                "tenant_id": request.tenant_id,
                "entity_id": source_doc["entity_id"]
            }
            
            MongoDBService.update_document(
                "sources",
                filter_query,
                source_doc
            )
            
            # Get saved document ID
            saved = MongoDBService.find_documents("sources", filter_query, limit=1)
            if saved:
                saved_ids.append(str(saved[0].get("_id", "")))
            else:
                # Insert if not exists
                doc_id = MongoDBService.insert_document("sources", source_doc)
                saved_ids.append(doc_id)
        
        return {
            "status": "success",
            "saved_count": len(saved_ids),
            "source_ids": saved_ids
        }
    
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Failed to save sources: {str(e)}")

@router.post("/audit/claims")
async def save_claims(request: ClaimSaveRequest):
    """Save claims to MongoDB claims collection"""
    try:
        saved_ids = []
        
        for claim in request.claims:
            claim_doc = {
                "tenant_id": request.tenant_id,
                "answer_id": request.answer_id,
                "text": claim.get("text", ""),
                "source_ids": claim.get("source_ids", []),
                "basis_ids": claim.get("basis_ids", []),
                "urs": claim.get("urs"),
                "urs_label": claim.get("urs_label"),
                "is_inference": claim.get("is_inference", False),
                "created_at": datetime.utcnow()
            }
            
            # Generate unique claim ID
            claim_id = str(uuid.uuid4())
            claim_doc["claim_id"] = claim_id
            
            # Insert claim
            doc_id = MongoDBService.insert_document("claims", claim_doc)
            saved_ids.append(doc_id)
        
        return {
            "status": "success",
            "saved_count": len(saved_ids),
            "claim_ids": saved_ids
        }
    
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Failed to save claims: {str(e)}")

@router.post("/audit/query")
async def save_query_audit(request: QueryAuditSaveRequest):
    """Save query audit to MongoDB query_audit collection"""
    try:
        audit_doc = {
            "tenant_id": request.tenant_id,
            "user_id": request.user_id,
            "question": request.question,
            "intent": request.intent,
            "classifier_conf": request.classifier_conf,
            "router_action": request.router_action,
            "status": request.status,
            "verification_status": request.verification_status,
            "avg_urs": request.avg_urs,
            "verified_claims_count": request.verified_claims_count,
            "blocked_claims_count": request.blocked_claims_count,
            "answer_id": request.answer_id,
            "model_used": request.model_used,
            "tokens_used": request.tokens_used or {},
            "created_at": datetime.utcnow()
        }
        
        doc_id = MongoDBService.insert_document("query_audit", audit_doc)
        
        return {
            "status": "success",
            "audit_id": doc_id
        }
    
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Failed to save query audit: {str(e)}")

@router.get("/audit/history")
async def get_audit_history(tenant_id: int, user_id: int, limit: Optional[int] = None):
    """Get query audit history for user
    
    STATISTICS RULE: limit=None means get all records (with safety max)
    """
    try:
        filter_query = {
            "tenant_id": tenant_id,
            "user_id": user_id
        }
        
        # STATISTICS RULE: If limit is None, use max safety limit (explicit, not hidden)
        query_limit = limit if limit is not None else 10000
        
        results = MongoDBService.find_documents(
            "query_audit",
            filter_query,
            limit=query_limit,
            sort=[("created_at", -1)]
        )
        
        # Convert ObjectId to string
        for doc in results:
            doc["_id"] = str(doc["_id"])
        
        return {
            "status": "success",
            "count": len(results),
            "results": results
        }
    
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Failed to get audit history: {str(e)}")

@router.get("/audit/claims/{answer_id}")
async def get_claims_for_answer(answer_id: str, tenant_id: int):
    """Get claims for a specific answer"""
    try:
        filter_query = {
            "tenant_id": tenant_id,
            "answer_id": answer_id
        }
        
        results = MongoDBService.find_documents("claims", filter_query)
        
        # Convert ObjectId to string
        for doc in results:
            doc["_id"] = str(doc["_id"])
        
        return {
            "status": "success",
            "count": len(results),
            "claims": results
        }
    
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Failed to get claims: {str(e)}")




















