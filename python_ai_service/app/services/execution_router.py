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
        QueryIntent.CONVERSATIONAL: RouterAction.DIRECT_QUERY,  # Conversational queries don't need RAG
        QueryIntent.FACT_CHECK: RouterAction.RAG_STRICT,
        QueryIntent.NUMERICAL: RouterAction.RAG_STRICT,
        QueryIntent.COMPARISON: RouterAction.RAG_STRICT,
        QueryIntent.DEFINITION: RouterAction.RAG_STRICT,
        QueryIntent.PROCEDURE: RouterAction.RAG_STRICT,
        QueryIntent.TEMPORAL: RouterAction.RAG_STRICT,
        QueryIntent.SPATIAL: RouterAction.RAG_STRICT,
        QueryIntent.INTERPRETATION: RouterAction.RAG_STRICT,  # Changed: interpretation with document request → RAG
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
        
        # Check if this is a document request (override for INTERPRETATION)
        question_lower = question.lower().strip()
        document_request_keywords = [
            "tokenomics", "documento", "documenti", "dati", "dato", "informazioni",
            "estrai", "estrae", "estraggo", "estragga",
            "cerca", "cercare", "trova", "trovare", "mostra", "mostrami", "mostrare",
            "forniscimi", "fornisci", "fornire", "analizza", "analizzare", "analisi"
        ]
        has_document_request = any(doc_kw in question_lower for doc_kw in document_request_keywords)
        
        # Get action from mapping
        action = ExecutionRouter.INTENT_ACTION_MAP.get(
            intent_enum,
            RouterAction.BLOCK
        )
        
        # Override: INTERPRETATION with document request → RAG_STRICT (not block)
        if intent_enum == QueryIntent.INTERPRETATION and has_document_request:
            action = RouterAction.RAG_STRICT
        
        # Override: Simple count queries can be answered directly (no AI needed)
        if action == RouterAction.RAG_STRICT and ExecutionRouter.can_respond_without_ai(intent, question, constraints):
            action = RouterAction.DIRECT_QUERY
        
        # Low confidence -> block (but allow if document request)
        if confidence < 0.5 and not has_document_request:
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
        question: str,
        constraints: Optional[Dict[str, Any]] = None
    ) -> bool:
        """
        Determina se query può essere risposta senza AI
        
        Args:
            intent: Query intent
            question: Original question text
            constraints: Optional constraints
        
        Returns:
            True se può rispondere direttamente
        """
        question_lower = question.lower().strip()
        
        # Query semplici numeriche che possono essere risposte direttamente con MongoDB
        # Pattern: "quanti/quante [documenti/delibere/atti] [filters]?"
        simple_count_patterns = [
            r'\bquant[iae]\s+(document[io]|deliber[ae]|att[io]|protocoll[io]|provvediment[io])',
            r'\bnumero\s+(totale|di|di\s+document[io]|di\s+deliber[ae])',
            r'\bconta\s+(document[io]|deliber[ae]|att[io])',
            r'\b(quant[iae]|numero)\s+(ce ne sono|ci sono|sono presenti)',
        ]
        
        # Verifica se è una query di conteggio semplice
        import re
        is_simple_count = any(re.search(pattern, question_lower) for pattern in simple_count_patterns)
        
        # Query semplici fattuali (chi/cosa/quando) per singolo documento
        # NOTA: Queste richiedono comunque RAG perché servono i contenuti del documento
        # Quindi non le trattiamo come DIRECT_QUERY
        
        # Solo query numeriche di conteggio possono essere risposte direttamente
        if intent in ["numerical", "fact_check"] and is_simple_count:
            return True
        
        return False





