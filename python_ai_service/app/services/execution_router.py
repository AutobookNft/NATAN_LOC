"""
Execution Router - Router logico deterministico
Decide come processare la query (direct_query, RAG_strict, block)
"""

from typing import Dict, Any, Optional
from enum import Enum
from app.services.question_classifier import QueryIntent


class RouterAction(str, Enum):
    """Router action types"""
    DIRECT_QUERY = "direct_query"       # Query diretta MongoDB (no AI)
    RAG_STRICT = "rag_strict"          # RAG con USE pipeline
    BLOCK = "block"                    # Blocca query (non verificabile)


class ExecutionRouter:
    """
    Router deterministico per decisione di processing
    Logica basata su intent classification
    """
    
    # Intent -> Action mapping
    INTENT_ACTION_MAP = {
        QueryIntent.FACT_CHECK: RouterAction.RAG_STRICT,
        QueryIntent.NUMERICAL: RouterAction.RAG_STRICT,
        QueryIntent.COMPARISON: RouterAction.RAG_STRICT,
        QueryIntent.DEFINITION: RouterAction.RAG_STRICT,
        QueryIntent.PROCEDURE: RouterAction.RAG_STRICT,
        QueryIntent.TEMPORAL: RouterAction.RAG_STRICT,
        QueryIntent.SPATIAL: RouterAction.RAG_STRICT,
        QueryIntent.INTERPRETATION: RouterAction.BLOCK,
        QueryIntent.BLOCKED: RouterAction.BLOCK,
    }
    
    @staticmethod
    def route(
        intent: str,
        confidence: float,
        question: str,
        tenant_id: int,
        constraints: Optional[Dict[str, Any]] = None
    ) -> Dict[str, Any]:
        """
        Route una query basata su classification
        
        Args:
            intent: Intent from QuestionClassifier
            confidence: Classification confidence
            question: Original question
            tenant_id: Tenant ID
            constraints: Optional constraints/filters
        
        Returns:
            Dict con:
                - action: RouterAction
                - reason: str (motivo decisione)
                - requires_ai: bool
                - can_respond_directly: bool
        """
        try:
            intent_enum = QueryIntent(intent)
        except ValueError:
            # Unknown intent -> block
            return {
                "action": RouterAction.BLOCK.value,
                "reason": f"Unknown intent: {intent}",
                "requires_ai": False,
                "can_respond_directly": False
            }
        
        # Get action from mapping
        action = ExecutionRouter.INTENT_ACTION_MAP.get(
            intent_enum,
            RouterAction.BLOCK
        )
        
        # Low confidence -> block
        if confidence < 0.5:
            action = RouterAction.BLOCK
            reason = f"Low classification confidence: {confidence}"
        else:
            # Standard routing
            reason_map = {
                RouterAction.DIRECT_QUERY: "Query semplice, risposta diretta possibile",
                RouterAction.RAG_STRICT: "Richiede RAG con verifica USE pipeline",
                RouterAction.BLOCK: f"Query {intent} non verificabile o troppo aperta"
            }
            reason = reason_map.get(action, "Unknown routing decision")
        
        # Determine requirements
        requires_ai = action == RouterAction.RAG_STRICT
        can_respond_directly = action == RouterAction.DIRECT_QUERY
        
        return {
            "action": action.value,
            "reason": reason,
            "requires_ai": requires_ai,
            "can_respond_directly": can_respond_directly,
            "intent": intent,
            "confidence": confidence
        }
    
    @staticmethod
    def can_respond_without_ai(
        intent: str,
        constraints: Optional[Dict[str, Any]] = None
    ) -> bool:
        """
        Determina se query può essere risposta senza AI
        
        Args:
            intent: Query intent
            constraints: Optional constraints
        
        Returns:
            True se può rispondere direttamente
        """
        # Per ora: tutte le query richiedono AI
        # TODO: Implementare logica per query semplici (es. "quante delibere nel 2024?")
        return False

