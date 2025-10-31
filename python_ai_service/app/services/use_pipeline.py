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
        query_embedding: Optional[list] = None
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
        # Step 1: Classify question
        classification = self.classifier.classify(question, tenant_id)
        
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
            
            # Other direct queries (TODO: implement for simple factual queries)
            return {
                "status": "direct_query",
                "message": "Direct query not yet implemented",
                "routing": routing
            }
        
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
            
            chunks = self.retriever.retrieve(
                query_embedding=query_embedding,
                tenant_id=tenant_id,
                limit=10,
                filters=constraints
            )
            
            if not chunks:
                return {
                    "status": "no_results",
                    "message": "No relevant chunks found",
                    "classification": classification,
                    "routing": routing
                }
            
            # Step 4: Generate claims with Neurale Strict
            generation_result = await self.neurale.generate_claims(
                question=question,
                chunks=chunks,
                tenant_id=tenant_id,
                persona=persona,
                model=model
            )
            
            claims = generation_result["claims"]
            
            # Step 5: Verify claims
            verification = self.verifier.verify_claims(
                claims=claims,
                chunks=chunks,
                tenant_id=tenant_id
            )
            
            # Build final result
            return {
                "status": "success",
                "question": question,
                "answer_id": generation_result["answer_id"],
                "verified_claims": verification["verified_claims"],
                "blocked_claims": verification["blocked_claims"],
                "avg_urs": verification["avg_urs"],
                "verification_status": verification["status"],
                "chunks_used": chunks,
                "classification": classification,
                "routing": routing,
                "model_used": generation_result["model_used"],
                "tokens_used": generation_result["tokens_used"],
                "persona": persona
            }
        
        else:
            return {
                "status": "error",
                "message": f"Unknown action: {action}",
                "routing": routing
            }
    
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

