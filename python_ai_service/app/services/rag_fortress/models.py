"""
Modelli Pydantic per RAG-Fortress Pipeline
"""

from typing import List, Dict, Optional
from pydantic import BaseModel, Field


class Evidence(BaseModel):
    """Modello per evidenza recuperata"""
    evidence_id: str
    content: str
    source: str
    metadata: Dict
    score: float = Field(ge=0, le=10)
    exact_quote: Optional[str] = None


class VerifiedEvidence(BaseModel):
    """Modello per evidenza verificata"""
    evidence_id: str
    is_directly_relevant: bool
    exact_quote: Optional[str]
    supports_user_question: bool
    relevance_score: float = Field(ge=0, le=10)


class Claim(BaseModel):
    """Modello per claim atomica"""
    claim_id: str
    text: str
    supporting_evidences: List[str]


class Gap(BaseModel):
    """Modello per gap di copertura"""
    gap_id: str
    description: str


class RAGFortressResponse(BaseModel):
    """Risposta completa RAG-Fortress"""
    answer: str
    urs_score: float = Field(ge=0, le=100)
    urs_explanation: str
    claims_used: List[str]
    sources: List[str]
    hallucinations_found: List[str]
    gaps_detected: List[str]

