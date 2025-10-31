"""
Neurale Strict - LLM genera claims atomici con source_ids
Ogni frase = 1 claim, ogni claim deve avere source_ids
"""

from typing import List, Dict, Any, Optional
import json
from app.services.retriever_service import RetrieverService
from app.services.ai_router import AIRouter


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
        self.ai_router = AIRouter()
    
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
        claims = await self._generate_claims_from_context(
            question=question,
            context=context,
            persona=persona,
            model=model,
            tenant_id=tenant_id,
            chunks=chunks
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
                "input": sum(claim.get("tokens", 0) for claim in validated_claims),  # Approximate
                "output": len(validated_claims) * 50  # Approximate
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
    
    async def _generate_claims_from_context(
        self,
        question: str,
        context: str,
        persona: str,
        model: str,
        tenant_id: int,
        chunks: List[Dict[str, Any]]
    ) -> List[Dict[str, Any]]:
        """
        Generate claims using LLM
        
        Args:
            question: User question
            context: Context string from chunks
            persona: Persona name
            model: Model name
            tenant_id: Tenant ID
            chunks: Chunks with source_ids
        
        Returns:
            List of claim dicts
        """
        # Build source_id mapping for chunks
        chunk_source_map = {}
        for i, chunk in enumerate(chunks):
            chunk_id = chunk.get("chunk_id", f"chunk_{i}")
            source_id = chunk.get("source_id")
            document_id = chunk.get("document_id")
            chunk_source_map[chunk_id] = {
                "source_id": source_id,
                "document_id": document_id,
                "chunk_index": chunk.get("chunk_index")
            }
        
        # Build prompt for LLM
        prompt = f"""You are an expert analyst answering questions based on provided sources.

Question: {question}

Context from sources:
{context}

Instructions:
1. Answer the question using ONLY the provided sources
2. Break your answer into atomic claims (one fact per claim)
3. Each claim MUST reference source IDs from the context using [Source N] notation
4. Return a JSON array of claims with this structure:
   [
     {{
       "text": "claim text here",
       "source_ids": ["chunk_id1", "chunk_id2"],
       "basis_ids": []
     }}
   ]

Generate the answer as atomic claims:"""
        
        # Prepare messages for LLM
        messages = [
            {"role": "system", "content": f"You are a {persona} assistant for Public Administration analysis."},
            {"role": "user", "content": prompt}
        ]
        
        # Get chat adapter based on context
        context_dict = {
            "tenant_id": tenant_id,
            "persona": persona,
            "task_class": "USE"
        }
        adapter = self.ai_router.get_chat_adapter(context_dict)
        
        # Generate response
        result = await adapter.generate(messages, temperature=0.3, max_tokens=2048)
        response_text = result["content"]
        
        # Parse JSON from response (handle markdown code blocks)
        claims = []
        try:
            # Try to extract JSON from markdown code block if present
            if "```json" in response_text:
                json_start = response_text.find("```json") + 7
                json_end = response_text.find("```", json_start)
                json_text = response_text[json_start:json_end].strip()
            elif "```" in response_text:
                json_start = response_text.find("```") + 3
                json_end = response_text.find("```", json_start)
                json_text = response_text[json_start:json_end].strip()
            else:
                json_text = response_text.strip()
            
            # Try to find JSON array in text
            array_start = json_text.find("[")
            array_end = json_text.rfind("]") + 1
            if array_start >= 0 and array_end > array_start:
                json_text = json_text[array_start:array_end]
            
            parsed_claims = json.loads(json_text)
            
            # Map chunk_ids to source_ids
            for claim in parsed_claims:
                chunk_ids = claim.get("source_ids", [])
                mapped_source_ids = []
                for chunk_id in chunk_ids:
                    if chunk_id in chunk_source_map:
                        source_info = chunk_source_map[chunk_id]
                        if source_info["source_id"]:
                            mapped_source_ids.append(source_info["source_id"])
                        elif source_info["document_id"]:
                            mapped_source_ids.append(source_info["document_id"])
                
                claims.append({
                    "text": claim.get("text", ""),
                    "source_ids": mapped_source_ids,
                    "basis_ids": claim.get("basis_ids", []),
                    "tokens": len(claim.get("text", "").split())
                })
        
        except (json.JSONDecodeError, KeyError) as e:
            # If JSON parsing fails, create single claim from response
            claims = [{
                "text": response_text[:500],  # Limit length
                "source_ids": [chunk.get("source_id") for chunk in chunks[:3] if chunk.get("source_id")],
                "basis_ids": [],
                "tokens": len(response_text.split())
            }]
        
        return claims
    
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

