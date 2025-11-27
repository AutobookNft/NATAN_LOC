"""
Estrattore di claim atomiche - Core anti-allucinazione
Estrae solo affermazioni fattuali 100% supportate dalle evidenze
"""

import logging
from typing import List, Dict
import json
from app.services.ai_router import AIRouter

logger = logging.getLogger(__name__)

# Prompt ultra-strict in italiano
CLAIM_EXTRACTION_PROMPT = """
Sei un estrattore rigoroso di claim atomiche per documenti della Pubblica Amministrazione italiana.

La tua missione è estrarre SOLO affermazioni fattuali che sono 100% supportate dalle evidenze verificate.

EVIDENZE VERIFICATE:
{verified_evidences_json}

REGOLE FERREE:
1. Estrai SOLO affermazioni fattuali che sono esplicitamente presenti nelle evidenze
2. Ogni claim deve essere atomica (una singola affermazione verificabile)
3. Formato: [CLAIM_001] Testo esatto della claim
4. Numera sequenzialmente: CLAIM_001, CLAIM_002, CLAIM_003...
5. NON inventare informazioni non presenti nelle evidenze
6. NON fare inferenze o deduzioni
7. NON aggiungere conoscenza esterna
8. Se NON puoi estrarre claim certe → ritorna esattamente ["[NO_CLAIMS]"]

ESEMPI CORRETTI:
- [CLAIM_001] La delibera n. 123/2024 è stata approvata il 15 marzo 2024
- [CLAIM_002] L'importo stanziato è di 50.000 euro
- [CLAIM_003] Il responsabile del procedimento è il dott. Mario Rossi

ESEMPI SBAGLIATI (NON fare così):
- [CLAIM_001] Probabilmente la delibera è stata approvata (NON certo)
- [CLAIM_002] L'importo potrebbe essere significativo (NON fattuale)
- [CLAIM_003] Secondo le prassi comuni... (NON presente nelle evidenze)

OUTPUT RICHIESTO:
Lista di claim numerate nel formato [CLAIM_XXX] Testo claim
Una claim per riga.
Se nessuna claim può essere estratta con certezza, ritorna esattamente: ["[NO_CLAIMS]"]
"""

class ClaimExtractor:
    """
    Estrattore di claim atomiche usando Llama-3.1-70B o Grok-4
    """
    
    def __init__(self):
        self.ai_router = AIRouter()
        self.model = "llama-3-1-70b"  # Llama-3.1-70B o Grok-4
    
    async def extract_atomic_claims(
        self, 
        verified_evidences: List[Dict]
    ) -> List[str]:
        """
        Estrae claim atomiche dalle evidenze verificate
        
        Args:
            verified_evidences: Lista di evidenze verificate
            
        Returns:
            Lista di claim nel formato [CLAIM_XXX] Testo claim
            O ["[NO_CLAIMS]"] se nessuna claim può essere estratta
        """
        if not verified_evidences:
            logger.warning("Nessuna evidenza verificata, ritorno [NO_CLAIMS]")
            return ["[NO_CLAIMS]"]
        
        # Filtra solo evidenze direttamente rilevanti
        # Soglia 5.0 (da 7.0) per essere meno restrittivi con modelli free-tier
        relevant_evidences = [
            ev for ev in verified_evidences
            if ev.get("is_directly_relevant", False) and ev.get("relevance_score", 0) >= 5.0
        ]
        
        if not relevant_evidences:
            logger.info("Nessuna evidenza direttamente rilevante, ritorno [NO_CLAIMS]")
            return ["[NO_CLAIMS]"]
        
        # Limita evidenze per rispettare rate limits
        MAX_EVIDENCES = 10
        MAX_CONTENT_LENGTH = 400
        relevant_evidences = relevant_evidences[:MAX_EVIDENCES]
        
        try:
            # Prepara evidenze per il prompt
            evidences_for_prompt = []
            for ev in relevant_evidences:
                content = ev.get("content", "")
                # Assicurati che content sia una stringa
                if not isinstance(content, str):
                    content = str(content) if content else ""
                evidences_for_prompt.append({
                    "evidence_id": ev.get("evidence_id", ""),
                    "content": content[:MAX_CONTENT_LENGTH],  # Limita lunghezza
                    "exact_quote": ev.get("exact_quote", "") or "",
                    "relevance_score": ev.get("relevance_score", 0.0)
                })
            
            # Costruisci prompt
            prompt = CLAIM_EXTRACTION_PROMPT.format(
                verified_evidences_json=json.dumps(evidences_for_prompt, ensure_ascii=False, indent=2)
            )
            
            # Usa Llama o Grok
            context = {"task_class": "extraction"}
            adapter = self.ai_router.get_chat_adapter(context)
            
            messages = [
                {
                    "role": "user",
                    "content": prompt
                }
            ]
            
            # Genera risposta con temperature molto bassa
            result = await adapter.generate(
                messages,
                temperature=0.0,  # Zero temperatura per massima precisione
                max_tokens=2048
            )
            
            content = result["content"].strip()
            
            # Estrai claim (una per riga)
            claims = []
            for line in content.split("\n"):
                line = line.strip()
                if line.startswith("[CLAIM_") and "]" in line:
                    # Estrai claim completa
                    claim_text = line
                    claims.append(claim_text)
                elif line == "[NO_CLAIMS]":
                    return ["[NO_CLAIMS]"]
            
            # Valida formato claim
            validated_claims = []
            for claim in claims:
                if claim.startswith("[CLAIM_") and "]" in claim:
                    validated_claims.append(claim)
            
            if not validated_claims:
                logger.warning("Nessuna claim valida estratta, ritorno [NO_CLAIMS]")
                return ["[NO_CLAIMS]"]
            
            logger.info(f"Estratte {len(validated_claims)} claim atomiche")
            
            return validated_claims
            
        except Exception as e:
            logger.error(f"Errore durante estrazione claim: {e}", exc_info=True)
            return ["[NO_CLAIMS]"]
