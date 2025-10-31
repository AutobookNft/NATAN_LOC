"""
Question Classifier - Classifica le domande per intent detection
Usa AI leggero per determinare il tipo di query
"""

from typing import Dict, Any, Optional
from enum import Enum


class QueryIntent(str, Enum):
    """Query intent types"""
    FACT_CHECK = "fact_check"           # Verifica fatti numerici/data
    NUMERICAL = "numerical"             # Query numerica specifica
    INTERPRETATION = "interpretation"   # Interpretazione/analisi
    COMPARISON = "comparison"           # Confronto tra entità
    DEFINITION = "definition"           # Definizione/concetto
    PROCEDURE = "procedure"             # Procedura/processo
    TEMPORAL = "temporal"               # Query temporale
    SPATIAL = "spatial"                 # Query geografica/spaziale
    BLOCKED = "blocked"                 # Query non verificabile/bloccata


class QuestionClassifier:
    """
    Classifica domande per determinare intent e confidenza
    TODO: Implementare con AI leggero (es. distilbert per classification)
    """
    
    # Keywords patterns per classification rapida (fallback)
    KEYWORD_PATTERNS = {
        QueryIntent.FACT_CHECK: [
            "quando", "dove", "chi", "cosa", "quale", "quanti", "quante",
            "importo", "costo", "prezzo", "valore", "numero"
        ],
        QueryIntent.NUMERICAL: [
            "quanto", "quantità", "somma", "totale", "percentuale",
            "€", "euro", "dollari"
        ],
        QueryIntent.COMPARISON: [
            "confronta", "differenza", "vs", "rispetto", "meglio", "peggio"
        ],
        QueryIntent.DEFINITION: [
            "cos'è", "cosa significa", "definizione", "significato"
        ],
        QueryIntent.PROCEDURE: [
            "come", "procedura", "processo", "step", "fasi"
        ],
        QueryIntent.TEMPORAL: [
            "quando", "data", "periodo", "dal", "al", "anno", "mese"
        ],
        QueryIntent.INTERPRETATION: [
            "perché", "motivo", "ragione", "opinione", "ritieni", "pensi"
        ]
    }
    
    @staticmethod
    def classify(question: str, tenant_id: int, model: str = "light") -> Dict[str, Any]:
        """
        Classifica una domanda
        
        Args:
            question: Testo della domanda
            tenant_id: ID tenant per context
            model: Model da usare ("light" o "llm")
        
        Returns:
            Dict con:
                - intent: QueryIntent
                - confidence: float (0.0 - 1.0)
                - constraints: Dict[str, Any] (filters, date ranges, etc.)
        """
        question_lower = question.lower().strip()
        
        # TODO: Implementare con AI leggero (es. fine-tuned model)
        # Per ora: keyword-based classification
        
        intent = QueryIntent.FACT_CHECK
        confidence = 0.6  # Default confidence per keyword-based
        constraints = {}
        
        # Keyword matching
        for intent_type, keywords in QuestionClassifier.KEYWORD_PATTERNS.items():
            if any(keyword in question_lower for keyword in keywords):
                intent = intent_type
                confidence = 0.75
                break
        
        # Block interpretation/intent troppo aperto
        if intent == QueryIntent.INTERPRETATION:
            # Alcune interpretation possono essere accettate se hanno constraint
            if any(word in question_lower for word in ["quale", "quali", "seleziona"]):
                # Convert to fact_check se ha constraint
                intent = QueryIntent.FACT_CHECK
                confidence = 0.7
            else:
                # Block open interpretation
                intent = QueryIntent.BLOCKED
                confidence = 0.9
        
        return {
            "intent": intent.value,
            "confidence": confidence,
            "constraints": constraints,
            "model": model,
            "question": question
        }
    
    @staticmethod
    def classify_with_llm(question: str, tenant_id: int) -> Dict[str, Any]:
        """
        Classifica con LLM (per queries complesse)
        TODO: Implementare chiamata a LLM leggero
        
        Args:
            question: Testo della domanda
            tenant_id: ID tenant
        
        Returns:
            Dict con intent, confidence, constraints
        """
        # Placeholder - implementare con LLM call
        return QuestionClassifier.classify(question, tenant_id, model="llm")

