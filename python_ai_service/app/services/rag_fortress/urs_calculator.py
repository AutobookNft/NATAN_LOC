"""
Calcolatore Ultra Reliability Score (URS)
Calcola score 0-100 basato su claim, gap, allucinazioni
"""

import logging
from typing import List, Dict

logger = logging.getLogger(__name__)

class URSCalculator:
    """
    Calcolatore Ultra Reliability Score
    """
    
    @staticmethod
    def calculate_urs(
        claims_used: int,
        total_claims: int,
        gaps: List[str],
        hallucinations: List[str],
        citations_present: bool
    ) -> Dict:
        """
        Calcola URS score 0-100
        
        Formula:
        - Base 100
        - -15 per ogni GAP
        - -30 per ogni hallucination
        - -20 se no citazioni
        - Bonus +5 se >8 claim usate
        
        Args:
            claims_used: Numero di claim utilizzate nella risposta
            total_claims: Numero totale di claim disponibili
            gaps: Lista di gap rilevati
            hallucinations: Lista di allucinazioni trovate
            citations_present: Se ci sono citazioni nella risposta
            
        Returns:
            Dict con 'score' (0-100) e 'explanation' (stringa)
        """
        # Base score
        score = 100.0
        
        # Penalità per gap
        gap_count = len([g for g in gaps if g != "FULL_COVERAGE"])
        score -= gap_count * 15
        
        # Penalità per allucinazioni
        hallucination_count = len([h for h in hallucinations if h != "NESSUNA_ALLUCINAZIONE"])
        score -= hallucination_count * 30
        
        # Penalità se mancano citazioni
        if not citations_present and claims_used > 0:
            score -= 20
        
        # Bonus se molte claim usate
        if claims_used > 8:
            score += 5
        
        # Assicura che score sia tra 0 e 100
        score = max(0.0, min(100.0, score))
        
        # Genera spiegazione
        explanation_parts = []
        
        if score == 100:
            explanation_parts.append("Score perfetto: tutte le informazioni sono verificate e citate correttamente.")
        else:
            if gap_count > 0:
                explanation_parts.append(f"{gap_count} gap di copertura rilevati (-{gap_count * 15} punti).")
            
            if hallucination_count > 0:
                explanation_parts.append(f"{hallucination_count} allucinazioni trovate (-{hallucination_count * 30} punti).")
            
            if not citations_present and claims_used > 0:
                explanation_parts.append("Citazioni mancanti (-20 punti).")
            
            if claims_used > 8:
                explanation_parts.append(f"Bonus per uso di {claims_used} claim (+5 punti).")
        
        explanation = " ".join(explanation_parts) if explanation_parts else "Score base senza penalità o bonus."
        
        logger.info(f"URS calcolato: {score}/100 - {explanation}")
        
        return {
            "score": round(score, 1),
            "explanation": explanation
        }
