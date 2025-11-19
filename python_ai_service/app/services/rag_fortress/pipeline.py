"""
Pipeline orchestrator RAG-Fortress
Coordina tutti i componenti per generare risposta zero-allucinazione
"""

import logging
from typing import Dict, Optional, List
from .retriever import HybridRetriever
from .evidence_verifier import EvidenceVerifier
from .claim_extractor import ClaimExtractor
from .gap_detector import GapDetector
from .constrained_synthesizer import ConstrainedSynthesizer
from .hostile_factchecker import HostileFactChecker
from .urs_calculator import URSCalculator

logger = logging.getLogger(__name__)

class RAGFortressPipeline:
    """
    Pipeline completa RAG-Fortress Zero-Hallucination
    """
    
    def __init__(self):
        self.retriever = HybridRetriever()
        self.evidence_verifier = EvidenceVerifier()
        self.claim_extractor = ClaimExtractor()
        self.gap_detector = GapDetector()
        self.synthesizer = ConstrainedSynthesizer()
        self.fact_checker = HostileFactChecker()
        self.urs_calculator = URSCalculator()
    
    def _extract_claim_numbers(self, text: str) -> List[str]:
        """Estrae numeri di claim citate nel testo"""
        import re
        claims = re.findall(r'\(CLAIM_\d+\)', text)
        return list(set(claims))  # Rimuovi duplicati
    
    def _has_citations(self, text: str) -> bool:
        """Verifica se il testo contiene citazioni claim"""
        return "(CLAIM_" in text
    
    async def rag_fortress(
        self,
        question: str,
        tenant_id: str,
        user_id: Optional[str] = None
    ) -> Dict:
        """
        Pipeline completa RAG-Fortress
        
        Args:
            question: Domanda dell'utente
            tenant_id: ID del tenant
            user_id: ID dell'utente (opzionale)
            
        Returns:
            Dict con:
            - answer: Risposta generata
            - urs_score: Score URS 0-100
            - urs_explanation: Spiegazione score
            - claims_used: Lista di claim utilizzate
            - sources: Lista di fonti
            - hallucinations_found: Lista di allucinazioni
            - gaps_detected: Lista di gap
        """
        try:
            logger.info(f"Avvio pipeline RAG-Fortress per tenant {tenant_id}")
            
            # STEP 1: Retrieve evidenze (over-retrieve 100)
            logger.info("STEP 1: Retrieval evidenze...")
            evidences = await self.retriever.retrieve_evidence(
                question=question,
                tenant_id=str(tenant_id),
                top_k=100
            )
            
            if not evidences:
                logger.warning("Nessuna evidenza recuperata")
                return {
                    "answer": "Non dispongo di informazioni verificate sufficienti per rispondere alla domanda posta.",
                    "urs_score": 0.0,
                    "urs_explanation": "Nessuna evidenza recuperata dal database.",
                    "claims_used": [],
                    "sources": [],
                    "hallucinations_found": [],
                    "gaps_detected": ["GAP_01: Nessuna informazione disponibile"]
                }
            
            # STEP 2: Verifica evidenze
            logger.info("STEP 2: Verifica evidenze...")
            verified_evidences = await self.evidence_verifier.verify_evidence(
                user_question=question,
                evidences=evidences
            )
            
            # Filtra solo evidenze direttamente rilevanti
            relevant_evidences = [
                ev for ev in verified_evidences
                if ev.get("is_directly_relevant", False) and ev.get("relevance_score", 0) >= 7.0
            ]
            
            if not relevant_evidences:
                logger.warning("Nessuna evidenza direttamente rilevante")
                return {
                    "answer": "Non dispongo di informazioni verificate sufficienti per rispondere alla domanda posta.",
                    "urs_score": 0.0,
                    "urs_explanation": "Nessuna evidenza direttamente rilevante trovata.",
                    "claims_used": [],
                    "sources": [],
                    "hallucinations_found": [],
                    "gaps_detected": ["GAP_01: Nessuna evidenza rilevante disponibile"]
                }
            
            # STEP 3: Estrai claim atomiche
            logger.info("STEP 3: Estrazione claim...")
            claims = await self.claim_extractor.extract_atomic_claims(relevant_evidences)
            
            if not claims or claims == ["[NO_CLAIMS]"]:
                logger.warning("Nessuna claim estratta")
                return {
                    "answer": "Non dispongo di informazioni verificate sufficienti per rispondere alla domanda posta.",
                    "urs_score": 0.0,
                    "urs_explanation": "Nessuna claim fattuale estratta dalle evidenze.",
                    "claims_used": [],
                    "sources": [],
                    "hallucinations_found": [],
                    "gaps_detected": ["GAP_01: Nessuna informazione fattuale verificabile"]
                }
            
            # STEP 4: Rileva gap
            logger.info("STEP 4: Rilevamento gap...")
            gaps = await self.gap_detector.detect_gaps(question, claims)
            
            # STEP 5: Sintetizza risposta
            logger.info("STEP 5: Sintesi risposta...")
            answer = await self.synthesizer.synthesize_response(
                user_question=question,
                claims=claims,
                gaps=gaps
            )
            
            # STEP 6: Fact-checking ostile
            logger.info("STEP 6: Fact-checking ostile...")
            hallucinations = await self.fact_checker.hostile_check(answer, claims)
            
            # Se ci sono allucinazioni gravi, rifiuta risposta
            if hallucinations and hallucinations != ["NESSUNA_ALLUCINAZIONE"]:
                logger.warning(f"Allucinazioni trovate: {hallucinations}")
                # Calcola URS comunque
                claim_numbers = self._extract_claim_numbers(answer)
                urs_result = self.urs_calculator.calculate_urs(
                    claims_used=len(claim_numbers),
                    total_claims=len(claims),
                    gaps=gaps,
                    hallucinations=hallucinations,
                    citations_present=self._has_citations(answer)
                )
                
                # Se URS < 90, rifiuta risposta
                if urs_result["score"] < 90:
                    return {
                        "answer": "Non posso fornire una risposta verificata sufficientemente affidabile. Si prega di riformulare la domanda o consultare direttamente i documenti ufficiali.",
                        "urs_score": urs_result["score"],
                        "urs_explanation": f"{urs_result['explanation']} Risposta rifiutata per affidabilità insufficiente.",
                        "claims_used": claim_numbers,
                        "sources": list(set(ev.get("source", "") for ev in relevant_evidences)),
                        "hallucinations_found": hallucinations,
                        "gaps_detected": gaps
                    }
            
            # STEP 7: Calcola URS finale
            logger.info("STEP 7: Calcolo URS...")
            claim_numbers = self._extract_claim_numbers(answer)
            urs_result = self.urs_calculator.calculate_urs(
                claims_used=len(claim_numbers),
                total_claims=len(claims),
                gaps=gaps,
                hallucinations=hallucinations if hallucinations != ["NESSUNA_ALLUCINAZIONE"] else [],
                citations_present=self._has_citations(answer)
            )
            
            # Estrai fonti uniche
            sources = list(set(ev.get("source", "") for ev in relevant_evidences if ev.get("source")))
            
            logger.info(f"Pipeline completata - URS: {urs_result['score']}/100")
            
            return {
                "answer": answer,
                "urs_score": urs_result["score"],
                "urs_explanation": urs_result["explanation"],
                "claims_used": claim_numbers,
                "sources": sources,
                "hallucinations_found": hallucinations if hallucinations != ["NESSUNA_ALLUCINAZIONE"] else [],
                "gaps_detected": gaps if gaps != ["FULL_COVERAGE"] else []
            }
            
        except Exception as e:
            logger.error(f"Errore nella pipeline RAG-Fortress: {e}", exc_info=True)
            return {
                "answer": "Si è verificato un errore durante l'elaborazione della domanda. Si prega di riprovare.",
                "urs_score": 0.0,
                "urs_explanation": f"Errore tecnico: {str(e)}",
                "claims_used": [],
                "sources": [],
                "hallucinations_found": [],
                "gaps_detected": []
            }

# Istanza globale della pipeline
_pipeline_instance = None

async def rag_fortress(
    question: str,
    tenant_id: str,
    user_id: Optional[str] = None
) -> Dict:
    """
    Funzione principale per chiamare la pipeline RAG-Fortress
    
    Args:
        question: Domanda dell'utente
        tenant_id: ID del tenant
        user_id: ID dell'utente (opzionale)
        
    Returns:
        Dict con risposta completa e metadata
    """
    global _pipeline_instance
    if _pipeline_instance is None:
        _pipeline_instance = RAGFortressPipeline()
    
    return await _pipeline_instance.rag_fortress(question, tenant_id, user_id)
