"""
USE Pipeline - Orchestrator principale
Coordina tutti i componenti USE: Classifier -> Router -> Retriever -> Neurale -> Verifier
"""

import logging
from typing import Dict, Any, Optional

logger = logging.getLogger(__name__)
from app.services.question_classifier import QuestionClassifier, QueryIntent
from app.services.execution_router import ExecutionRouter, RouterAction
from app.services.retriever_service import RetrieverService
from app.services.neurale_strict import NeuraleStrict
from app.services.logical_verifier import LogicalVerifier
from app.services.urs_calculator import UrsCalculator
from app.services.conversational_responses import ConversationalResponseSystem
from app.services.conversational_learner import ConversationalLearner


class UsePipeline:
    """
    Ultra Semantic Engine Pipeline
    Orchestrates the complete USE flow
    """
    
    def __init__(self):
        """Initialize USE Pipeline with all components"""
        self.classifier = QuestionClassifier()
        self.router = ExecutionRouter()
        self.retriever = RetrieverService()
        self.neurale = NeuraleStrict(retriever=self.retriever)
        self.verifier = LogicalVerifier()
        self.conversational_learner = ConversationalLearner()
    
    async def process_query(
        self,
        question: str,
        tenant_id: int,
        persona: str = "strategic",
        model: str = "anthropic.sonnet-3.5",
        query_embedding: Optional[list] = None,
        debug: bool = False
    ) -> Dict[str, Any]:
        """
        Process a query through complete USE pipeline
        
        Args:
            question: User question
            tenant_id: Tenant ID
            persona: Persona name
            model: LLM model
            query_embedding: Optional pre-computed query embedding
        
        Returns:
            Complete USE pipeline result
        """
        debug_info: Dict[str, Any] = {}

        # Step 1: Classify question
        classification = self.classifier.classify(question, tenant_id)
        if debug:
            debug_info["classification"] = classification
        
        intent = classification["intent"]
        confidence = classification["confidence"]
        constraints = classification.get("constraints", {})
        
        # Step 2: Route query
        routing = self.router.route(
            intent=intent,
            confidence=confidence,
            question=question,
            tenant_id=tenant_id,
            constraints=constraints
        )
        if debug:
            debug_info["routing"] = routing
        
        action = routing["action"]
        
        # Handle different actions
        if action == RouterAction.BLOCK.value:
            return {
                "status": "blocked",
                "reason": routing["reason"],
                "intent": intent,
                "classification": classification,
                "routing": routing
            }
        
        elif action == RouterAction.DIRECT_QUERY.value:
            # Handle conversational queries or direct queries (no RAG needed)
            # For conversational queries, respond directly
            if intent == "conversational":
                return await self._handle_conversational_query(
                    question=question,
                    classification=classification,
                    routing=routing,
                    tenant_id=tenant_id,
                    persona=persona
                )
            
            # Simple numeric/count queries (e.g., "quanti documenti nel 2024?")
            return await self._handle_direct_simple_query(
                question=question,
                classification=classification,
                routing=routing,
                tenant_id=tenant_id
            )
        
        elif action == RouterAction.RAG_STRICT.value:
            # Step 3: Retrieve chunks
            # Generate embedding if not provided
            if not query_embedding:
                from app.services.ai_router import AIRouter
                ai_router = AIRouter()
                context = {
                    "tenant_id": tenant_id,
                    "task_class": "RAG"
                }
                adapter = ai_router.get_embedding_adapter(context)
                embed_result = await adapter.embed(question)
                query_embedding = embed_result["embedding"]
            
            # Enhanced retrieval with logging
            logger.info(f"🔍 USE Pipeline: Starting retrieval for tenant {tenant_id}")
            logger.info(f"🔍 USE Pipeline: Query: '{question[:100]}...' (length: {len(question)})")
            
            chunks = self.retriever.retrieve(
                query_embedding=query_embedding,
                tenant_id=tenant_id,
                limit=20,  # Increased limit for better coverage
                min_score=0.15,  # Lower minimum score for generic queries
                filters=constraints
            )
            if debug:
                debug_info["retrieved_chunks"] = {
                    "count": len(chunks),
                    "document_ids": [chunk.get("document_id") for chunk in chunks[:10]]
                }
            
            logger.info(f"🔍 USE Pipeline: Retrieved {len(chunks)} chunks from retriever")
            
            # CRITICAL: Check if chunks are actually relevant
            if not chunks:
                logger.warning(f"⚠️ USE Pipeline: No chunks retrieved for tenant {tenant_id}")
                logger.warning(f"⚠️ USE Pipeline: Query was: '{question}'")
                logger.warning(f"⚠️ USE Pipeline: This may indicate: 1) No documents in MongoDB for tenant {tenant_id}, 2) Embeddings missing, 3) Similarity threshold too high")
                no_data_message = (
                    "Non ho informazioni sufficienti nei documenti disponibili per rispondere a questa domanda. "
                    "I documenti presenti nell'archivio non contengono dati pertinenti per questa richiesta.\n\n"
                    "💡 **Suggerimento**: Puoi creare un progetto e inserire tutti i dati pertinenti alla risposta. "
                    "Una volta caricati i documenti necessari, potrò rispondere alla tua domanda in modo completo e accurato."
                )
                result_payload = {
                    "status": "no_results",
                    "message": no_data_message,
                    "answer": no_data_message,
                    "verified_claims": [],
                    "blocked_claims": [],
                    "avg_urs": 0.0,
                    "verification_status": "NO_DATA",
                    "classification": classification,
                    "routing": routing
                }
                if debug:
                    result_payload["debug"] = debug_info
                return result_payload
            
            # CRITICAL: Validate chunk relevance - check if any chunk has meaningful text
            relevant_chunks = []
            skipped_empty = 0
            skipped_too_short = 0
            skipped_error_indicators = 0
            
            logger.info(f"🔍 USE Pipeline: Validating {len(chunks)} chunks for relevance...")
            logger.info(f"🔍 USE Pipeline: First chunk structure: {list(chunks[0].keys()) if chunks else 'NO_CHUNKS'}")
            
            for i, chunk in enumerate(chunks):
                # Try multiple field names for chunk text
                chunk_text = chunk.get("chunk_text") or chunk.get("text") or chunk.get("content") or ""
                similarity = chunk.get("similarity") or chunk.get("score", 0)
                doc_id = chunk.get("document_id", "N/A")[:50]
                
                # Log first chunk details for debugging
                if i == 0:
                    logger.info(f"🔍 USE Pipeline: First chunk details:")
                    logger.info(f"   Keys: {list(chunk.keys())}")
                    logger.info(f"   chunk_text type: {type(chunk_text)}, length: {len(str(chunk_text)) if chunk_text else 0}")
                    logger.info(f"   chunk_text preview: {str(chunk_text)[:200] if chunk_text else 'NONE'}")
                    logger.info(f"   similarity: {similarity}")
                
                if not chunk_text:
                    skipped_empty += 1
                    logger.warning(f"⚠️ Chunk {i+1}/{len(chunks)} skipped: empty text (doc: {doc_id}, similarity: {similarity:.3f})")
                    logger.warning(f"   Chunk keys: {list(chunk.keys())}")
                    continue
                
                chunk_text_stripped = str(chunk_text).strip()
                text_length = len(chunk_text_stripped)
                
                # TEMPORARY: Remove minimum length check to see what happens
                # Accept ANY chunk with text, even if very short
                # Minimum meaningful content check - DISABLED TEMPORARILY FOR DEBUGGING
                # if text_length < 10:
                #     skipped_too_short += 1
                #     logger.info(f"⚠️ Chunk {i+1}/{len(chunks)} skipped: too short ({text_length} chars < 10) (doc: {doc_id}, similarity: {similarity:.3f})")
                #     logger.info(f"   Text preview: {chunk_text_stripped[:200]}")
                #     continue
                
                # CRITICAL: Check for placeholder/error indicators in chunk text
                # If chunk contains error messages, skip it
                error_indicators = [
                    "nessun documento", "no document", "documento non trovato", "not found",
                    "empty database", "database vuoto", "no data available", "nessun dato disponibile",
                    "placeholder", "example", "test data", "dummy data"
                ]
                chunk_lower = chunk_text_stripped.lower()
                if any(indicator in chunk_lower for indicator in error_indicators):
                    # Chunk is a placeholder/error message, skip it
                    skipped_error_indicators += 1
                    logger.warning(f"⚠️ Chunk {i+1}/{len(chunks)} skipped: contains error indicator (doc: {doc_id}, similarity: {similarity:.3f})")
                    continue
                
                # CRITICAL: NON filtrare per relevance_score - potrebbe escludere chunk validi
                # Il retriever ha già fatto una selezione, non scartiamo chunk con score basso
                # Solo verifichiamo che non siano placeholder/errori
                
                relevant_chunks.append(chunk)
                logger.info(f"✅ Chunk {i+1}/{len(chunks)} accepted: {text_length} chars, similarity: {similarity:.3f} (doc: {doc_id})")
            
            logger.info(f"🔍 USE Pipeline: Chunk validation results:")
            logger.info(f"   ✅ Accepted: {len(relevant_chunks)}/{len(chunks)}")
            logger.info(f"   ⚠️ Skipped - empty: {skipped_empty}")
            logger.info(f"   ⚠️ Skipped - too short: {skipped_too_short}")
            logger.info(f"   ⚠️ Skipped - error indicators: {skipped_error_indicators}")
            if debug:
                debug_info["relevant_chunks"] = {
                    "accepted": len(relevant_chunks),
                    "skipped_empty": skipped_empty,
                    "skipped_too_short": skipped_too_short,
                    "skipped_error_indicators": skipped_error_indicators
                }
            
            if not relevant_chunks:
                # CRITICAL: No relevant chunks found - return immediately without calling LLM
                # REGOLA ZERO: Se non abbiamo documenti pertinenti, NON chiamiamo l'LLM
                # L'LLM potrebbe inventare dati anche con prompt anti-hallucination
                no_data_message = (
                    "Non ho informazioni sufficienti nei documenti disponibili per rispondere a questa domanda. "
                    "I documenti presenti nell'archivio non contengono dati pertinenti per questa richiesta.\n\n"
                    "💡 **Suggerimento**: Puoi creare un progetto e inserire tutti i dati pertinenti alla risposta. "
                    "Una volta caricati i documenti necessari, potrò rispondere alla tua domanda in modo completo e accurato."
                )
                logger.error(f"❌ USE Pipeline: ALL chunks filtered out!")
                logger.error(f"   Total chunks retrieved: {len(chunks)}")
                logger.error(f"   Skipped - empty: {skipped_empty}")
                logger.error(f"   Skipped - too short: {skipped_too_short}")
                logger.error(f"   Skipped - error indicators: {skipped_error_indicators}")
                
                # CRITICAL DEBUG: Show first chunk structure if available
                if chunks:
                    logger.error(f"   🔍 DEBUG: First chunk structure:")
                    logger.error(f"      Keys: {list(chunks[0].keys())}")
                    for key in ['chunk_text', 'text', 'content', 'document_id', 'similarity', 'score']:
                        if key in chunks[0]:
                            value = chunks[0][key]
                            if isinstance(value, str):
                                logger.error(f"      {key}: {value[:200]}")
                            else:
                                logger.error(f"      {key}: {type(value).__name__} = {value}")
                
                logger.error(f"   💡 This may indicate chunks are empty or contain error indicators")
                
                result_payload = {
                    "status": "no_results",
                    "message": "I documenti recuperati non contengono informazioni sufficienti o rilevanti per rispondere a questa domanda.",
                    "answer": no_data_message,
                    "verified_claims": [],
                    "blocked_claims": [],
                    "avg_urs": 0.0,
                    "verification_status": "NO_DATA",
                    "classification": classification,
                    "routing": routing,
                    "_internal_note": f"Chunks filtered out - {len(chunks)} chunks retrieved but all filtered (empty: {skipped_empty}, too_short: {skipped_too_short}, error_indicators: {skipped_error_indicators}). LLM not called to prevent hallucination."
                }
                if debug:
                    result_payload["debug"] = debug_info
                return result_payload
            
            # Use only relevant chunks
            chunks = relevant_chunks
            
            # CRITICAL: Additional pre-generation check
            # If we have chunks but they seem too generic/not relevant to the question, 
            # we still pass them but the LLM will be instructed to return [] if not relevant
            
            # Step 4: Generate claims with Neurale Strict
            generation_result = await self.neurale.generate_claims(
                question=question,
                chunks=chunks,
                tenant_id=tenant_id,
                persona=persona,
                model=model
            )
            
            claims = generation_result["claims"]
            answer_text = generation_result.get("answer", "")  # Natural language answer
            if debug:
                debug_info["answer_text"] = answer_text
            
            # CRITICAL: Check if answer text indicates "no data" - BUT if we have verified claims, 
            # the answer is WRONG and we should use claims instead (claims take precedence)
            answer_lower = answer_text.lower() if answer_text else ""
            no_data_indicators = [
                "non ho informazioni sufficienti",
                "i documenti presenti nell'archivio non contengono dati pertinenti",
                "non ho informazioni nei documenti disponibili"
            ]
            
            answer_says_no_data = any(indicator in answer_lower for indicator in no_data_indicators)
            if debug:
                debug_info["answer_says_no_data"] = answer_says_no_data
            
            # CRITICAL: If no claims generated, return no_results
            if not claims or len(claims) == 0:
                no_data_message = (
                    "Non ho informazioni sufficienti nei documenti disponibili per rispondere a questa domanda. "
                    "I documenti presenti nell'archivio non contengono dati pertinenti per questa richiesta.\n\n"
                    "💡 **Suggerimento**: Puoi creare un progetto e inserire tutti i dati pertinenti alla risposta. "
                    "Una volta caricati i documenti necessari, potrò rispondere alla tua domanda in modo completo e accurato."
                )
                result_payload = {
                    "status": "no_results",
                    "message": "I documenti recuperati non contengono informazioni sufficienti per rispondere a questa domanda.",
                    "answer": no_data_message,
                    "verified_claims": [],
                    "blocked_claims": [],  # Mai esporre claim bloccati - rischio legale
                    "avg_urs": 0.0,
                    "verification_status": "NO_DATA",
                    "classification": classification,
                    "routing": routing
                }
                if debug:
                    result_payload["debug"] = debug_info
                return result_payload
            
            # Step 5: Verify claims
            verification = self.verifier.verify_claims(
                claims=claims,
                chunks=chunks,
                tenant_id=tenant_id
            )
            if debug:
                debug_info["claims_count"] = len(claims)
                debug_info["verified_claims"] = len(verification.get("verified_claims", []))
                debug_info["blocked_claims"] = len(verification.get("blocked_claims", []))
            
            # CRITICAL: If answer says "no data" BUT we have verified claims, this is a BUG
            # This should NOT happen anymore after the root cause fix in neurale_strict.py
            # But we keep this check as a safety fallback and log a warning
            if answer_says_no_data and len(verification["verified_claims"]) > 0:
                # This is unexpected - LLM should not say "no data" when chunks are provided
                # Reconstruct answer from verified claims as fallback
                verified_texts = [claim.get("text", "") for claim in verification["verified_claims"] if claim.get("text")]
                if verified_texts:
                    # Build answer from verified claims (fallback)
                    answer_text = "Basandomi sui documenti disponibili:\n\n" + "\n\n".join(f"• {text}" for text in verified_texts)
                    # Force verification_status to SAFE since we have verified claims
                    if verification["status"] != "SAFE":
                        verification["status"] = "SAFE"
                    logger.error(f"⚠️ BUG DETECTED: LLM said 'no data' but {len(verification['verified_claims'])} verified claims exist. This should not happen after root cause fix. Reconstructing answer from claims.")
                    logger.warning(f"This indicates the prompt fix may not be working correctly - verify neurale_strict.py prompt")
            
            # CRITICAL: If all claims were blocked (hallucinations), return no_results
            if len(verification["verified_claims"]) == 0 and len(verification["blocked_claims"]) > 0:
                # CRITICAL: NON esporre claim bloccati all'utente - contengono dati inventati che potrebbero essere interpretati come veri
                # Questo è un rischio legale/pericolo per la PA - non mostrare informazioni non verificate
                no_data_message = (
                    "Non ho informazioni sufficienti nei documenti disponibili per rispondere a questa domanda. "
                    "I documenti presenti nell'archivio non contengono dati pertinenti per questa richiesta.\n\n"
                    "💡 **Suggerimento**: Puoi creare un progetto e inserire tutti i dati pertinenti alla risposta. "
                    "Una volta caricati i documenti necessari, potrò rispondere alla tua domanda in modo completo e accurato."
                )
                result_payload = {
                    "status": "no_results",
                    "message": "I documenti recuperati non supportano alcuna affermazione verificabile per rispondere a questa domanda.",
                    "answer": no_data_message,
                    "verified_claims": [],
                    "blocked_claims": [],  # NON esporre - troppo pericoloso mostrare dati inventati all'utente
                    "avg_urs": 0.0,
                    "verification_status": "ALL_CLAIMS_BLOCKED",
                    "classification": classification,
                    "routing": routing,
                    "_internal_blocked_count": len(verification["blocked_claims"])  # Solo per log/debug interno
                }
                if debug:
                    result_payload["debug"] = debug_info
                return result_payload
            
            # STEP 6: VERIFICA POSTUMA OBBLIGATORIA - Garantisce che ogni affermazione sia tracciabile
            from app.services.post_verification_service import PostVerificationService
            post_verifier = PostVerificationService()
            should_block, block_reason = post_verifier.should_block_response(
                answer_text,
                verification["verified_claims"],
                chunks,
                question
            )
            if debug:
                debug_info["post_verification_block"] = {
                    "blocked": should_block,
                    "reason": block_reason
                }
            
            if should_block:
                # CRITICAL: NON esporre claim bloccati - contengono dati potenzialmente inventati
                # Mostrare solo messaggio generico per evitare che l'utente veda informazioni false
                no_data_message = (
                    "Non ho informazioni sufficienti nei documenti disponibili per rispondere a questa domanda. "
                    "I documenti presenti nell'archivio non contengono dati pertinenti per questa richiesta.\n\n"
                    "💡 **Suggerimento**: Puoi creare un progetto e inserire tutti i dati pertinenti alla risposta. "
                    "Una volta caricati i documenti necessari, potrò rispondere alla tua domanda in modo completo e accurato."
                )
                result_payload = {
                    "status": "no_results",
                    "message": block_reason,
                    "answer": no_data_message,
                    "verified_claims": [],  # Blocca tutto se verifica postuma fallisce
                    "blocked_claims": [],  # NON esporre claim bloccati - troppo pericoloso (contengono dati inventati)
                    "avg_urs": 0.0,
                    "verification_status": "POST_VERIFICATION_FAILED",
                    "verification_reason": block_reason,
                    "classification": classification,
                    "routing": routing,
                    "_internal_blocked_count": len(verification["blocked_claims"]) + len(verification["verified_claims"])  # Solo per log interno
                }
                if debug:
                    result_payload["debug"] = debug_info
                return result_payload
            
            # Build final result - solo se verifica postuma passa
            # CRITICAL: Non esporre blocked_claims anche in caso di successo
            # Solo claims verificati con sourceRefs vengono esposti all'utente
            # I blocked_claims contengono dati inventati - mai esporli per sicurezza legale
            if debug:
                debug_info["final_status"] = verification["status"]
                debug_info["verified_claims_list"] = [claim.get("text") for claim in verification.get("verified_claims", [])]
            result_payload = {
                "status": "success",
                "question": question,
                "answer": answer_text,  # Main natural language answer (verificata postuma)
                "answer_id": generation_result["answer_id"],
                "verified_claims": verification["verified_claims"],  # Claims with sources (proof) - ONLY these are shown
                "blocked_claims": [],  # NEVER expose blocked claims - security risk (invented data)
                "avg_urs": verification["avg_urs"],
                "verification_status": verification["status"],
                "post_verification": "PASSED",  # Indica che verifica postuma è passata
                "chunks_used": chunks,
                "classification": classification,
                "routing": routing,
                "model_used": generation_result["model_used"],
                "tokens_used": generation_result["tokens_used"],
                "persona": persona,
                "_internal_blocked_count": len(verification["blocked_claims"])  # Solo per log interno, mai esposto
            }
            if debug:
                result_payload["debug"] = debug_info
            return result_payload
        
        else:
            result_payload = {
                "status": "error",
                "message": f"Unknown action: {action}",
                "routing": routing
            }
            if debug:
                result_payload["debug"] = debug_info
            return result_payload
    
    async def _handle_conversational_query(
        self,
        question: str,
        classification: Dict[str, Any],
        routing: Dict[str, Any],
        tenant_id: int = 0,
        persona: str = "strategic"
    ) -> Dict[str, Any]:
        """
        Handle conversational queries using advanced pattern matching
        Auto-learns from AI if no match found
        """
        question_lower = question.lower().strip()
        
        # Step 1: Check learned responses first using semantic search
        learned_response = await self.conversational_learner.find_matching_learned_response(
            question=question,
            question_lower=question_lower
        )
        if learned_response:
            # Use learned response
            variants = learned_response.get("variants", [])
            if variants:
                import random
                response_text = random.choice(variants)
                logger.info(f"Using learned response for: {question[:50]}")
            else:
                response_text = None
        else:
            # Step 2: Try pattern matching system
            response_text = ConversationalResponseSystem.generate_response(question)
        
        # Step 3: If no match, learn from AI
        if response_text is None:
            logger.info(f"🔍 No match found for question, starting AI learning: {question[:50]}")
            
            try:
                # Ask AI to generate 5+ variants
                context = {
                    "tenant_id": tenant_id,
                    "persona": persona
                }
                logger.info(f"🤖 Asking AI to generate response variants for: {question[:50]}")
                variants = await self.conversational_learner.learn_from_ai(question, context)
                logger.info(f"📝 AI generated {len(variants) if variants else 0} variants")
            except Exception as e:
                logger.error(f"❌ Error during AI learning: {e}", exc_info=True)
                variants = []
            
            if variants and len(variants) >= 5:
                # Generate embedding for the question before saving
                logger.info(f"💾 Saving {len(variants)} learned response variants with embedding...")
                saved = await self.conversational_learner.save_learned_responses(
                    question=question,
                    question_lower=question_lower,
                    variants=variants
                )
                
                if saved:
                    logger.info(f"✅ Successfully saved learned responses")
                else:
                    logger.warning(f"⚠️ Failed to save learned responses")
                
                # Use random variant as response
                import random
                response_text = random.choice(variants)
                logger.info(f"✅ Using learned response: {response_text[:50]}...")
            elif variants and len(variants) > 0:
                # Use what we got even if less than 5
                logger.warning(f"⚠️ AI generated only {len(variants)} variants (expected 5+)")
                saved = await self.conversational_learner.save_learned_responses(
                    question=question,
                    question_lower=question_lower,
                    variants=variants
                )
                import random
                response_text = random.choice(variants)
            else:
                # Fallback to misc category
                logger.error(f"❌ AI learning returned no variants, using fallback")
                import random
                misc_template = random.choice(ConversationalResponseSystem.CATEGORIES["misc"]["templates"])
                response_text = ConversationalResponseSystem._replace_variables(misc_template)
        
        # Step 4: Return response
        
        return {
            "status": "success",
            "question": question,
            "answer_id": f"conv_{hash(question)}",
            "verified_claims": [{
                "text": response_text,
                "urs": 1.0,
                "ursLabel": "A",
                "sourceRefs": [],
                "isInference": False,
                "ursBreakdown": {
                    "conversational": 1.0,
                    "total": 1.0
                }
            }],
            "blocked_claims": [],
            "avg_urs": 1.0,
            "verification_status": "conversational",
            "chunks_used": [],
            "classification": classification,
            "routing": routing,
            "model_used": "conversational",
            "tokens_used": {"total": 0, "prompt": 0, "completion": 0},
            "persona": "strategic"
        }
    
    async def _handle_direct_simple_query(
        self,
        question: str,
        classification: Dict[str, Any],
        routing: Dict[str, Any],
        tenant_id: int
    ) -> Dict[str, Any]:
        """
        Handle simple numeric/count queries that can be answered directly from MongoDB
        (e.g., "quanti documenti nel 2024?", "quante delibere ci sono?")
        
        Args:
            question: User question
            classification: Classification result
            routing: Routing result
            tenant_id: Tenant ID
        
        Returns:
            Dict with answer and metadata
        """
        from app.services.mongodb_service import MongoDBService
        import re
        from datetime import datetime
        
        question_lower = question.lower().strip()
        
        # Build MongoDB filter
        filter_query = {
            "tenant_id": tenant_id
        }
        
        # Extract date filters from question
        # Patterns: "nel 2024", "dal 2023 al 2024", "nel mese di gennaio", etc.
        year_match = re.search(r'\b(?:nel|anno|del|dell\'|nell\')\s+(\d{4})\b', question_lower)
        if year_match:
            year = int(year_match.group(1))
            # IMPORTANT: created_at in MongoDB is datetime object, not ISO string
            filter_query["created_at"] = {
                "$gte": datetime(year, 1, 1),
                "$lt": datetime(year + 1, 1, 1)
            }
        
        # Extract document type filters
        # IMPORTANT: Map Italian keywords to actual document_type values in MongoDB
        doc_type_map = {
            "delibera": "pa_act",  # Deliberazioni sono pa_act
            "delibere": "pa_act",
            "atto": "pa_act",  # Atti sono pa_act, non "atto"
            "atti": "pa_act",
            "determinazione": "pa_act",
            "determinazioni": "pa_act",
            "protocollo": "pa_act",  # Protocolli sono pa_act
            "protocolli": "pa_act",
            "provvedimento": "pa_act",  # Provvedimenti sono pa_act
            "provvedimenti": "pa_act",
            "documento": None,  # Generic, no filter
            "documenti": None
        }
        
        for keyword, doc_type in doc_type_map.items():
            if keyword in question_lower and doc_type:
                filter_query["document_type"] = doc_type
                break
        
        # Count documents
        count = MongoDBService.count_documents("documents", filter_query)
        
        # Log for debugging
        logger.info(f"[USE Pipeline] Direct simple query: count={count}, filter={filter_query}, tenant_id={tenant_id}")
        
        # Build answer
        if count == 0:
            answer_text = f"Non ci sono documenti che corrispondono ai criteri richiesti."
        else:
            # Extract document type name for answer
            doc_type_name = filter_query.get("document_type", "documenti")
            if doc_type_name == "delibera":
                doc_type_name = "delibere"
            elif doc_type_name == "atto":
                doc_type_name = "atti"
            elif doc_type_name == "protocollo":
                doc_type_name = "protocolli"
            elif doc_type_name == "provvedimento":
                doc_type_name = "provvedimenti"
            
            year_info = ""
            if year_match:
                year_info = f" nel {year_match.group(1)}"
            
            answer_text = f"Ci sono {count} {doc_type_name}{year_info}."
        
        return {
            "status": "success",
            "question": question,
            "answer": answer_text,
            "answer_id": f"count_{hash(question)}",
            "verified_claims": [{
                "text": answer_text,
                "urs": 1.0,
                "ursLabel": "A",
                "sourceRefs": [],
                "isInference": False,
                "ursBreakdown": {
                    "direct_query": 1.0,
                    "total": 1.0
                }
            }],
            "blocked_claims": [],
            "avg_urs": 1.0,
            "verification_status": "direct_query",
            "chunks_used": [],
            "classification": classification,
            "routing": routing,
            "model_used": "mongodb_direct",
            "tokens_used": {"total": 0, "input": 0, "output": 0},
            "persona": "strategic"
        }

