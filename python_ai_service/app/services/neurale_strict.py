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
        
        # Step 1: Generate natural language answer first (synthesized response)
        answer_text = await self._generate_natural_answer(
            question=question,
            context=context,
            persona=persona,
            model=model,
            tenant_id=tenant_id
        )
        
        # Step 2: Generate atomic claims from the answer (for verification)
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
            "answer": answer_text,  # Natural language answer (main response)
            "claims": validated_claims,  # Verified claims with sources (proof)
            "answer_id": answer_id,
            "model_used": model,
            "tokens_used": {
                "input": sum(claim.get("tokens", 0) for claim in validated_claims),  # Approximate
                "output": len(answer_text.split()) + len(validated_claims) * 50  # Answer + claims
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
            page = source_ref.get("page_number") or source_ref.get("page")
            title = source_ref.get("title", "")
            
            # Get chunk text - retriever uses 'chunk_text', but also check 'text' for compatibility
            chunk_text = chunk.get("chunk_text") or chunk.get("text", "")
            
            context_part = f"[Source {i}]"
            if title:
                context_part += f" {title}"
            if url:
                context_part += f" ({url})"
            if page:
                context_part += f" - page {page}"
            context_part += f"\n{chunk_text}\n"
            
            context_parts.append(context_part)
        
        return "\n---\n".join(context_parts)
    
    async def _generate_natural_answer(
        self,
        question: str,
        context: str,
        persona: str,
        model: str,
        tenant_id: int
    ) -> str:
        """
        Generate natural language answer from context (synthesized response)
        
        Args:
            question: User question
            context: Context string from chunks
            persona: Persona name
            model: Model name
            tenant_id: Tenant ID
        
        Returns:
            Natural language answer text
        """
        # Build prompt for natural language synthesis
        prompt = f"""You are an expert assistant answering questions for Public Administration.

Question: {question}

Relevant information from sources:
{context}

Instructions:
1. Provide a comprehensive, natural language answer to the question
2. Synthesize information from the sources into a coherent response
3. Use clear, professional language appropriate for PA context
4. Structure the answer logically (introduction, main points, conclusion if needed)
5. Do NOT cite sources in the answer text itself (sources will be shown separately)
6. Focus on providing a complete, human-like response

Provide your answer:"""
        
        # Prepare messages for LLM
        messages = [
            {"role": "system", "content": f"You are a {persona} assistant for Public Administration analysis. Provide clear, comprehensive answers based on the provided sources."},
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
        result = await adapter.generate(messages, temperature=0.5, max_tokens=2048)
        answer_text = result["content"].strip()
        
        return answer_text
    
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
        # Use index-based IDs matching the [Source N] notation in context
        chunk_source_map = {}
        for i, chunk in enumerate(chunks, 1):
            # Source ID matches [Source N] notation in context (1-based index)
            source_id = f"chunk_{i}"
            chunk_id = chunk.get("chunk_id") or source_id
            document_id = chunk.get("document_id")
            chunk_source_map[source_id] = {
                "source_id": source_id,
                "document_id": document_id,
                "chunk_index": chunk.get("chunk_index"),
                "chunk_id": chunk_id
            }
        
        # Build prompt for LLM
        prompt = f"""You are an expert analyst answering questions based on provided sources.

Question: {question}

Context from sources:
{context}

Instructions:
1. Answer the question using ONLY the provided sources
2. Break your answer into atomic claims (one fact per claim)
3. Each claim MUST reference source IDs from the context using chunk_N format (e.g., chunk_1, chunk_2) matching [Source N]
4. Return a JSON array of claims with this structure:
   [
     {{
       "text": "claim text here",
       "source_ids": ["chunk_1", "chunk_2"],
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
            
            # Map chunk_ids to source_ids (chunk_source_map already has correct mapping)
            for claim in parsed_claims:
                chunk_ids = claim.get("source_ids", [])
                # Keep source_ids as-is (chunk_1, chunk_2, etc.) - they match the context notation
                # The validation will check these against valid source IDs
                mapped_source_ids = [
                    chunk_id for chunk_id in chunk_ids
                    if chunk_id in chunk_source_map or chunk_id.startswith("chunk_")
                ]
                
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
        
        # Build mapping: chunk_N -> chunk index (0-based) for validation
        # Source IDs use format chunk_1, chunk_2, etc. (1-based index)
        valid_source_ids_set = {f"chunk_{i+1}" for i in range(len(chunks))}
        
        for claim in claims:
            # Ensure required fields
            if "text" not in claim or not claim.get("text"):
                continue
            
            # Ensure source_ids exists and is list
            source_ids = claim.get("source_ids", [])
            if not isinstance(source_ids, list):
                source_ids = []
            
            # Filter source_ids to only include valid ones (chunk_1, chunk_2, etc.)
            valid_source_ids = [
                sid for sid in source_ids
                if sid in valid_source_ids_set
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

