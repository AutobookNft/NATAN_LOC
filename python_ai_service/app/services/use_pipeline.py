"""
USE Pipeline - Orchestrator principale
Coordina tutti i componenti USE: Classifier -> Router -> Retriever -> Neurale -> Verifier
"""

from typing import Dict, Any, Optional
from app.services.question_classifier import QuestionClassifier, QueryIntent
from app.services.execution_router import ExecutionRouter, RouterAction
from app.services.retriever_service import RetrieverService
from app.services.neurale_strict import NeuraleStrict
from app.services.logical_verifier import LogicalVerifier
from app.services.urs_calculator import UrsCalculator


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
            # TODO: Implement direct query (no AI needed)
            return {
                "status": "direct_query",
                "message": "Direct query not yet implemented",
                "routing": routing
            }
        
        elif action == RouterAction.RAG_STRICT.value:
            # Step 3: Retrieve chunks
            if not query_embedding:
                # TODO: Generate embedding if not provided
                # For now, return error
                return {
                    "status": "error",
                    "message": "Query embedding required for RAG"
                }
            
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

