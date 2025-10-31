"""
Neurale Strict - LLM genera claims atomici con source_ids
Ogni frase = 1 claim, ogni claim deve avere source_ids
"""

from typing import List, Dict, Any, Optional
import json
from app.services.retriever_service import RetrieverService


class NeuraleStrict:
    """
    LLM Strict Mode - Genera claims atomici con fonti
    Ogni affermazione è un claim separato con source references
    """
    
    def __init__(self, retriever: Optional[RetrieverService] = None):
        """
        Initialize Neurale Strict
        
        Args:
            retriever: Optional RetrieverService instance
        """
        self.retriever = retriever or RetrieverService()
    
    async def generate_claims(
        self,
        question: str,
        chunks: List[Dict[str, Any]],
        tenant_id: int,
        persona: str = "strategic",
        model: str = "anthropic.sonnet-3.5"
    ) -> Dict[str, Any]:
        """
        Generate atomic claims from question and chunks
        
        Args:
            question: User question
            chunks: Retrieved chunks with source_ref
            persona: Persona name (strategic, financial, legal, etc.)
            tenant_id: Tenant ID
            model: LLM model to use
        
        Returns:
            Dict with:
                - claims: List of claim dicts
                - answer_id: Unique answer ID
                - model_used: Model name
                - tokens_used: Token usage
        """
        # TODO: Implementare chiamata a LLM (OpenAI/Anthropic/Ollama)
        # Per ora: placeholder structure
        
        # Build context from chunks
        context = self._build_context(chunks)
        
        # Generate claims using LLM
        # Placeholder - implementare con chiamata reale
        claims = self._generate_claims_from_context(
            question=question,
            context=context,
            persona=persona,
            model=model
        )
        
        # Validate claims structure
        validated_claims = self._validate_claims(claims, chunks)
        
        # Generate answer ID
        import uuid
        answer_id = f"ans_{uuid.uuid4().hex[:8]}"
        
        return {
            "claims": validated_claims,
            "answer_id": answer_id,
            "model_used": model,
            "tokens_used": {
                "input": 0,  # TODO: Get from LLM response
                "output": 0
            },
            "persona": persona
        }
    
    def _build_context(self, chunks: List[Dict[str, Any]]) -> str:
        """
        Build context string from chunks
        
        Args:
            chunks: List of chunk dicts
        
        Returns:
            Context string for LLM
        """
        context_parts = []
        for i, chunk in enumerate(chunks, 1):
            source_ref = chunk.get("source_ref", {})
            url = source_ref.get("url", "")
            page = source_ref.get("page")
            
            context_part = f"[Source {i}]"
            if url:
                context_part += f" {url}"
            if page:
                context_part += f" (page {page})"
            context_part += f"\n{chunk.get('text', '')}\n"
            
            context_parts.append(context_part)
        
        return "\n---\n".join(context_parts)
    
    def _generate_claims_from_context(
        self,
        question: str,
        context: str,
        persona: str,
        model: str
    ) -> List[Dict[str, Any]]:
        """
        Generate claims using LLM
        
        Args:
            question: User question
            context: Context string from chunks
            persona: Persona name
            model: Model name
        
        Returns:
            List of claim dicts
        """
        # TODO: Implementare chiamata reale a LLM
        # Prompt structure:
        # - Question
        # - Context with sources
        # - Instruction: Generate atomic claims, each with source_ids
        # - Format: JSON array of claims
        
        # Placeholder: return empty claims
        # In production, questo chiamerà OpenAI/Anthropic/Ollama
        return []
    
    def _validate_claims(
        self,
        claims: List[Dict[str, Any]],
        chunks: List[Dict[str, Any]]
    ) -> List[Dict[str, Any]]:
        """
        Validate claims structure and source_ids
        
        Args:
            claims: Raw claims from LLM
            chunks: Source chunks
        
        Returns:
            Validated claims with proper structure
        """
        validated = []
        chunk_source_map = {chunk.get("source_id"): chunk for chunk in chunks}
        
        for claim in claims:
            # Ensure required fields
            if "text" not in claim:
                continue
            
            # Ensure source_ids exists and is list
            source_ids = claim.get("source_ids", [])
            if not isinstance(source_ids, list):
                source_ids = []
            
            # Filter source_ids to only include valid ones
            valid_source_ids = [
                sid for sid in source_ids
                if sid in chunk_source_map
            ]
            
            # Mark as inference if no sources
            is_inference = len(valid_source_ids) == 0
            
            validated_claim = {
                "text": claim["text"],
                "source_ids": valid_source_ids,
                "is_inference": is_inference,
                "basis_ids": claim.get("basis_ids", []),
                "created_at": claim.get("created_at") or self._now_iso()
            }
            
            validated.append(validated_claim)
        
        return validated
    
    @staticmethod
    def _now_iso() -> str:
        """Get current ISO datetime string"""
        from datetime import datetime
        return datetime.utcnow().isoformat() + "Z"

