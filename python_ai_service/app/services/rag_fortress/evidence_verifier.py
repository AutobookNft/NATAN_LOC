"""
Verificatore di evidenze - Turno 1
Verifica rigorosa della rilevanza delle evidenze recuperate
Usa formato TOON per ottimizzazione token (OS3/OS4 compliant)
"""

import logging
from typing import List, Dict
from app.services.ai_router import AIRouter
from toon_utils import ToonConverter

logger = logging.getLogger(__name__)


class EvidenceVerifier:
    """
    Verificatore di evidenze usando retrieval score + soglia similarity.
    Ottimizzato per Groq free tier - evita chiamate AI extra quando possibile.
    """
    
    def __init__(self):
        self.ai_router = AIRouter()
        # Soglia minima di similarity per considerare evidenza rilevante
        self.MIN_SIMILARITY = 0.45
    
    async def verify_evidence(
        self, 
        user_question: str, 
        evidences: List[Dict]
    ) -> List[Dict]:
        """
        Verifica evidenze usando similarity score dal retrieval.
        Evita chiamate AI extra per rispettare rate limits Groq free tier.
        
        Args:
            user_question: Domanda dell'utente
            evidences: Lista di evidenze da verificare
            
        Returns:
            Lista di evidenze verificate con score di rilevanza
        """
        if not evidences:
            logger.warning("Nessuna evidenza da verificare")
            return []
        
        MAX_EVIDENCES = 20
        evidences_to_verify = evidences[:MAX_EVIDENCES]
        
        logger.info(f"Verificando {len(evidences_to_verify)}/{len(evidences)} evidenze (similarity-based)")
        
        verified_results = []
        relevant_count = 0
        
        for ev in evidences_to_verify:
            # Usa similarity score dal retrieval come proxy per rilevanza
            similarity = ev.get("score", 0.0)
            
            # Normalizza similarity a 0-10 scale
            relevance_score = min(10.0, similarity * 20)  # 0.5 sim = 10 score
            
            is_relevant = similarity >= self.MIN_SIMILARITY
            if is_relevant:
                relevant_count += 1
            
            verified_results.append({
                "evidence_id": ev.get("evidence_id"),
                "is_directly_relevant": is_relevant,
                "exact_quote": None,  # Non disponibile senza chiamata AI
                "supports_user_question": is_relevant,
                "relevance_score": relevance_score,
                "content": ev.get("content", ""),
                "source": ev.get("source", ""),
                "metadata": ev.get("metadata", {})
            })
        
        # Log stats
        score_stats = [v.get("relevance_score", 0) for v in verified_results]
        if score_stats:
            logger.info(f"Verificate {len(verified_results)} evidenze: {relevant_count} rilevanti (sim>={self.MIN_SIMILARITY}), score range: {min(score_stats):.1f}-{max(score_stats):.1f}")
        
        return verified_results
