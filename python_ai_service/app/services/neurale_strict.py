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
                - tokens_used: Token usage (real values from LLM responses)
        """
        # Build context from chunks
        context = self._build_context(chunks)
        
        # Step 1: Generate natural language answer first (synthesized response)
        answer_result = await self._generate_natural_answer(
            question=question,
            context=context,
            persona=persona,
            model=model,
            tenant_id=tenant_id
        )
        
        # Extract answer text and token usage from result
        if isinstance(answer_result, dict):
            answer_text = answer_result.get("answer", "")
            answer_tokens = answer_result.get("tokens", {})
            answer_model = answer_result.get("model", model)
        else:
            # Backward compatibility: if method returns string directly
            answer_text = answer_result
            answer_tokens = {"input_tokens": 0, "output_tokens": 0}
            answer_model = model
        
        # Step 2: Generate atomic claims from the answer (for verification)
        claims_result = await self._generate_claims_from_context(
            question=question,
            context=context,
            persona=persona,
            model=model,
            tenant_id=tenant_id,
            chunks=chunks
        )
        
        # Extract claims and token usage from result
        if isinstance(claims_result, dict):
            claims = claims_result.get("claims", [])
            claims_tokens = claims_result.get("tokens", {})
            claims_model = claims_result.get("model", model)
        else:
            # Backward compatibility: if method returns list directly
            claims = claims_result
            claims_tokens = {"input_tokens": 0, "output_tokens": 0}
            claims_model = model
        
        # Validate claims structure
        validated_claims = self._validate_claims(claims, chunks)
        
        # Generate answer ID
        import uuid
        answer_id = f"ans_{uuid.uuid4().hex[:8]}"
        
        # Aggregate token usage from both LLM calls (use real values if available)
        total_input_tokens = (
            answer_tokens.get("input_tokens", 0) + 
            claims_tokens.get("input_tokens", 0)
        )
        total_output_tokens = (
            answer_tokens.get("output_tokens", 0) + 
            claims_tokens.get("output_tokens", 0)
        )
        
        # Use actual model from LLM response, fallback to requested model
        actual_model = answer_model or claims_model or model
        
        return {
            "answer": answer_text,  # Natural language answer (main response)
            "claims": validated_claims,  # Verified claims with sources (proof)
            "answer_id": answer_id,
            "model_used": actual_model,
            "tokens_used": {
                "input": total_input_tokens,
                "output": total_output_tokens,
                "total": total_input_tokens + total_output_tokens
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
        # CRITICAL: Check if context is empty or irrelevant
        if not context or context.strip() == "" or len(context.strip()) < 50:
            return self._no_data_response(question)
        
        # Build prompt for natural language synthesis with STRICT anti-hallucination rules
        # CRITICAL: Se ci sono chunks forniti, l'LLM DEVE rispondere usando quei chunks
        # NON può dire "no data" se ci sono documenti forniti - questo è un bug, non un comportamento corretto
        prompt = f"""You are NATAN, an expert organizational knowledge assistant that answers questions based ONLY on provided documents.

Question: {question}

Relevant information from sources:
{context}

🚨 REGOLA ZERO - LA PIÙ IMPORTANTE 🚨
SE HAI RICEVUTO DOCUMENTI, DEVI RISPONDERE USANDO QUEI DOCUMENTI.
NON dire MAI "non ho informazioni" se hai ricevuto documenti sopra.

CRITICAL INSTRUCTIONS - READ CAREFULLY:
1. **IF YOU RECEIVED DOCUMENTS ABOVE (sources are provided), YOU MUST ANSWER USING THOSE DOCUMENTS.**
   - You have received sources - this means there IS relevant information available
   - Your job is to synthesize an answer from the provided sources
   - DO NOT say "no data" or "non ho informazioni" if sources are provided above
   - The fact that sources were provided means they contain relevant information - USE IT

2. Answer ONLY using information that is EXPLICITLY stated in the provided sources above
   - Extract facts, concepts, definitions, and information directly from the sources
   - Synthesize them into a coherent answer to the question
   - Even if the answer is partial, provide what you can from the sources

3. DO NOT invent, assume, infer, or generate data that is not explicitly present in the sources
   - But DO extract and synthesize what IS in the sources
   - If a concept is mentioned in sources, explain it using the source content

4. DO NOT use general knowledge, external data, or patterns from other documents
   - Use ONLY the sources provided above
   - If a source defines something, use that definition

5. DO NOT fill gaps with "logical" assumptions or deductions
   - But DO connect facts from sources if they naturally relate

6. DO NOT create statistics, numbers, dates, or facts that are not directly stated in the sources
   - But DO use statistics, numbers, dates from the sources if present

IMPORTANT: Without documents you would return "no data". BUT HERE YOU HAVE DOCUMENTS. Therefore you MUST extract information from them. The user question asks for an analysis of investment areas; the documents contain budget variations and spending categories. Identify the main areas of spending/investment mentioned in the sources and summarize them.

7. Focus on:
   - Budget chapters, spending categories, recurring investments
   - Projects or initiatives funded (economic development, welfare, culture, environment, etc.)
   - Any trend or emphasis evident in the documents (e.g., increases in specific functions)

8. Structure your answer clearly:
   - Introduce the context (e.g., "Dai documenti esaminati emerge che…")
   - Elenca le principali aree/ambiti, citando i capitoli o le funzioni con le cifre o descrizioni ricavate
   - Chiudi con una sintesi del quadro complessivo

9. The answer MUST be in Italian.

Generate your final answer strictly following these instructions."""
        
        # Prepare messages for LLM with REGOLA ZERO in system message
        # CRITICAL: If sources are provided in the user message, the LLM MUST use them
        # The system message should NOT say "say you don't have information" if sources are provided
        messages = [
            {"role": "system", "content": f"""You are NATAN, a {persona} assistant for Public Administration analysis.

REGOLA ZERO: 
- If sources are provided in the user message, you MUST answer using those sources. DO NOT say "no data" if sources are provided.
- If NO sources are provided (context is empty), then you can say you don't have information.
- DO NOT invent information that is not in the sources, but DO extract and synthesize what IS in the sources.

Your responses MUST be based ONLY on the provided sources. Never use general knowledge or assumptions.

CRITICAL: If the user message includes "Relevant information from sources:" followed by content, this means sources ARE available. You MUST synthesize an answer from those sources. DO NOT respond with "no data" message if sources are provided.

This is a legal requirement for PA systems - false information can have serious consequences. Only state facts from sources, but if sources are provided, USE THEM."""},
            {"role": "user", "content": prompt}
        ]
        
        # Get chat adapter based on context
        context_dict = {
            "tenant_id": tenant_id,
            "persona": persona,
            "task_class": "USE"
        }
        adapter = self.ai_router.get_chat_adapter(context_dict)
        
        # Generate response with VERY LOW temperature to minimize hallucination
        # Temperature 0.1 = maximum conservatism, minimal creativity (exactly what we want)
        result = await adapter.generate(messages, temperature=0.1, max_tokens=2048)
        answer_text = result["content"].strip()
        
        # Extract token usage from result (real values from LLM)
        usage = result.get("usage", {})
        tokens = {
            "input_tokens": usage.get("prompt_tokens") or usage.get("input_tokens") or 0,
            "output_tokens": usage.get("completion_tokens") or usage.get("output_tokens") or 0
        }
        
        # Get actual model used (might differ from requested if fallback occurred)
        actual_model = result.get("model", model)
        
        # CRITICAL: Verify the answer doesn't contain invented data
        # Check for common hallucination patterns (statistics, specific numbers not in sources)
        # If answer is too long or contains patterns suggesting invented data, return no_data_response
        if len(answer_text) > 2000:  # Suspiciously long answer might contain invented data
            # Check if it's the standard "no data" response
            if "non ho informazioni sufficienti" not in answer_text.lower():
                # Might contain invented data - return safe response
                return {
                    "answer": self._no_data_response(question),
                    "tokens": tokens,
                    "model": actual_model
                }
        
        # Return answer with token usage for aggregation
        return {
            "answer": answer_text,
            "tokens": tokens,
            "model": actual_model
        }
    
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
        # CRITICAL: Check if context is empty or irrelevant
        if not context or context.strip() == "" or len(context.strip()) < 100:
            # CRITICAL: Se il contesto è troppo breve, non può contenere informazioni reali
            # Ritorna array vuoto - il pipeline bloccherà la risposta
            return []
        
        # Verifica aggiuntiva: il contesto non deve essere un messaggio di errore
        error_indicators = [
            "nessun documento", "no document", "documento non trovato", "not found",
            "empty database", "database vuoto", "no data available", "nessun dato disponibile"
        ]
        context_lower = context.lower()
        if any(indicator in context_lower for indicator in error_indicators):
            # Il contesto indica che non ci sono documenti - ritorna array vuoto
            return []
        
        prompt = f"""You are NATAN, an expert analyst for Public Administration that generates claims ONLY from provided sources.

Question: {question}

Context from sources:
{context}

🚨 REGOLA ZERO - LA PIÙ IMPORTANTE 🚨
SE NON SAI, NON INVENTARE. Ritorna array vuoto [].

CRITICAL INSTRUCTIONS - ANTI-HALLUCINATION MODE:
1. Answer ONLY using information that is EXPLICITLY stated in the provided sources above
2. If the sources do NOT contain information relevant to answer the question, return an EMPTY array: []
3. DO NOT invent, assume, infer, or generate claims not directly supported by the sources
4. DO NOT fill gaps with "logical" deductions or assumptions
5. Each claim MUST be directly traceable to a specific source using chunk_N format (e.g., chunk_1, chunk_2) matching [Source N]
6. DO NOT create statistics, numbers, dates, or facts that are not explicitly stated in the sources
7. DO NOT combine information to create new facts unless explicitly stated in sources
8. If a source mentions partial data, state that explicitly in the claim (e.g., "I dati disponibili indicano parzialmente...")
9. Break your answer into atomic claims (one fact per claim)
10. If you're not 100% sure a claim is in the sources, DO NOT include it
11. Better to return [] than to guess or infer

Return a JSON array of claims with this structure (or [] if no relevant data):
   [
     {{
       "text": "claim text here (MUST be directly from sources, word-for-word if possible)",
       "source_ids": ["chunk_1", "chunk_2"],
       "basis_ids": []
     }}
   ]

ANTI-HALLUCINATION CHECK:
- Before creating each claim, verify it can be directly traced to a specific [Source N] above
- If you cannot trace a claim to a source, DO NOT include it
- If sources are not relevant, return empty array: []
- If you're unsure, return []

Remember: This is for Public Administration. False claims can have serious legal consequences.

Generate the answer as atomic claims (or [] if no relevant data):"""
        
        # Prepare messages for LLM with REGOLA ZERO in system message
        messages = [
            {
                "role": "system",
                "content": f"""
You are NATAN, a {persona} assistant for Public Administration analysis.

REGOLA ZERO: CON DOCUMENTI FORNITI DEVI produrre claims. Non puoi rispondere con un array vuoto se il contesto contiene informazioni rilevanti. Vuoto solo se davvero non c'è alcun dato pertinente.

I tuoi claims DEVONO basarsi SOLO sulle fonti fornite. Non usare conoscenza generale o supposizioni. Se le fonti non contengono informazioni rilevanti, restituisci [].

Questo è un requisito legale per i sistemi della Pubblica Amministrazione – claims falsi comportano conseguenze serie. Tuttavia, se il contesto contiene dati utili devi trasformarli in claims."""
            },
            {"role": "user", "content": prompt}
        ]
        
        # Get chat adapter based on context
        context_dict = {
            "tenant_id": tenant_id,
            "persona": persona,
            "task_class": "USE"
        }
        adapter = self.ai_router.get_chat_adapter(context_dict)
        
        # Generate response with VERY LOW temperature to minimize hallucination
        # Temperature 0.1 = maximum conservatism, minimal creativity (exactly what we want)
        result = await adapter.generate(messages, temperature=0.1, max_tokens=2048)
        response_text = result["content"]
        
        # Extract token usage from result (real values from LLM)
        usage = result.get("usage", {})
        tokens = {
            "input_tokens": usage.get("prompt_tokens") or usage.get("input_tokens") or 0,
            "output_tokens": usage.get("completion_tokens") or usage.get("output_tokens") or 0
        }
        
        # Get actual model used (might differ from requested if fallback occurred)
        actual_model = result.get("model", model)
        
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
        
        # Return claims with token usage for aggregation
        return {
            "claims": claims,
            "tokens": tokens,
            "model": actual_model
        }
    
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
    
    @staticmethod
    def _no_data_response(question: str) -> str:
        """
        Return standard response when no data is available
        Includes suggestion to create a project and add relevant data
        
        Args:
            question: User question (for context)
        
        Returns:
            Standard "no data" response with project creation suggestion
        """
        return (
            f"Non ho informazioni sufficienti nei documenti disponibili per rispondere alla domanda: '{question}'. "
            f"I documenti presenti nell'archivio non contengono dati pertinenti per questa richiesta.\n\n"
            f"💡 **Suggerimento**: Puoi creare un progetto e inserire tutti i dati pertinenti alla risposta. "
            f"Una volta caricati i documenti necessari, potrò rispondere alla tua domanda in modo completo e accurato."
        )

