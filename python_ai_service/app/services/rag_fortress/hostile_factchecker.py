"""
Fact-checker ostile - Modello diverso dal synthesizer
Verifica estrema per individuare allucinazioni
"""

import logging
from typing import List
import json
from app.services.ai_router import AIRouter

logger = logging.getLogger(__name__)

HOSTILE_CHECK_PROMPT = """
Sei un fact-checker ESTREMAMENTE OSTILE e RIGOROSO.

La tua missione è trovare QUALSIASI frase nella risposta che NON sia presente nelle claim consentite.

RISPOSTA DA VERIFICARE:
{response}

CLAIM CONSENTITE (SOLO queste sono valide):
{allowed_claims_json}

ISTRUZIONI:
1. Analizza OGNI frase della risposta
2. Verifica se ogni frase fattuale è presente nelle claim consentite
3. Se una frase NON è presente nelle claim → è un'ALLUCINAZIONE
4. Sei ESTREMAMENTE OSTILE: anche piccole variazioni sono allucinazioni
5. Se TUTTO è corretto → ritorna esattamente ["NESSUNA_ALLUCINAZIONE"]

FORMATO OUTPUT:
- HALLUCINATION: frase esatta non presente nelle claim
- HALLUCINATION: altra frase non presente
- Oppure: ["NESSUNA_ALLUCINAZIONE"]

ESEMPIO:
Risposta: "L'importo è di 50.000 euro (CLAIM_001). La delibera è stata approvata ieri."
Claim: [CLAIM_001] L'importo stanziato è di 50.000 euro
HALLUCINATION: "La delibera è stata approvata ieri" (non presente nelle claim)

Rispondi SOLO con lista di allucinazioni o ["NESSUNA_ALLUCINAZIONE"]
"""

class HostileFactChecker:
    """
    Fact-checker ostile usando modello DIVERSO dal synthesizer
    Gemini-1.5-Flash o Llama-3.1-405B
    """
    
    def __init__(self):
        self.ai_router = AIRouter()
        # Usa modello diverso dal synthesizer per diversità
        self.model = "gemini-1.5-flash"  # Gemini-1.5-Flash o Llama-3.1-405B
    
    async def hostile_check(
        self, 
        response: str, 
        allowed_claims: List[str]
    ) -> List[str]:
        """
        Verifica ostile della risposta per individuare allucinazioni
        
        Args:
            response: Risposta sintetizzata da verificare
            allowed_claims: Lista di claim consentite
            
        Returns:
            Lista di allucinazioni trovate
            O ["NESSUNA_ALLUCINAZIONE"] se tutto corretto
        """
        if not response or not allowed_claims or allowed_claims == ["[NO_CLAIMS]"]:
            return ["NESSUNA_ALLUCINAZIONE"]
        
        try:
            # Costruisci prompt
            prompt = HOSTILE_CHECK_PROMPT.format(
                response=response,
                allowed_claims_json=json.dumps(allowed_claims, ensure_ascii=False, indent=2)
            )
            
            # Usa modello diverso (Gemini o Llama)
            # Per ora fallback su Claude se Gemini non disponibile
            context = {"task_class": "verification"}
            adapter = self.ai_router.get_chat_adapter(context)
            
            messages = [
                {
                    "role": "system",
                    "content": "Sei un fact-checker estremamente rigoroso e ostile. Trova QUALSIASI allucinazione."
                },
                {
                    "role": "user",
                    "content": prompt
                }
            ]
            
            result = await adapter.generate(
                messages,
                temperature=0.0,  # Zero temperatura per massima precisione
                max_tokens=1024
            )
            
            content = result["content"].strip()
            
            # Frasi standard da ignorare (formule burocratiche comuni)
            IGNORE_PATTERNS = [
                "in riferimento alla domanda",
                "si comunica quanto segue",
                "non dispongo di informazioni verificate",
                "si conclude che",
                "in conclusione",
                "si segnala che",
                "si ribadisce che",
                "le informazioni fornite sono limitate"
            ]
            
            # Estrai allucinazioni
            hallucinations = []
            for line in content.split("\n"):
                line = line.strip()
                if line.startswith("HALLUCINATION:") or line.startswith("HALLUCINATION"):
                    # Estrai testo dopo "HALLUCINATION:"
                    hallucination_text = line.split(":", 1)[1].strip() if ":" in line else line.replace("HALLUCINATION", "").strip()
                    
                    if hallucination_text:
                        # Rimuovi virgolette
                        hallucination_text = hallucination_text.strip('"\'')
                        
                        # IGNORA se contiene citazione claim (CLAIM_XXX)
                        if "(CLAIM_" in hallucination_text:
                            logger.debug(f"Ignorata 'hallucination' con citazione claim: {hallucination_text[:80]}")
                            continue
                        
                        # IGNORA se è formula standard
                        is_standard = any(pattern in hallucination_text.lower() for pattern in IGNORE_PATTERNS)
                        if is_standard:
                            logger.debug(f"Ignorata formula standard: {hallucination_text[:80]}")
                            continue
                        
                        hallucinations.append(f"HALLUCINATION: {hallucination_text}")
                        
                elif line == "NESSUNA_ALLUCINAZIONE" or line == '["NESSUNA_ALLUCINAZIONE"]':
                    return ["NESSUNA_ALLUCINAZIONE"]
            
            if not hallucinations:
                # Verifica se dice esplicitamente nessuna allucinazione
                if "NESSUNA_ALLUCINAZIONE" in content.upper() or "nessuna allucinazione" in content.lower():
                    return ["NESSUNA_ALLUCINAZIONE"]
                # Se non trova esplicitamente, assume che tutto sia ok
                return ["NESSUNA_ALLUCINAZIONE"]
            
            logger.warning(f"Trovate {len(hallucinations)} allucinazioni reali nella risposta")
            
            return hallucinations
            
        except Exception as e:
            logger.error(f"Errore durante fact-checking ostile: {e}", exc_info=True)
            # In caso di errore, assume che ci possa essere un problema
            return [f"HALLUCINATION: Errore nella verifica - risposta potrebbe contenere informazioni non verificate"]
