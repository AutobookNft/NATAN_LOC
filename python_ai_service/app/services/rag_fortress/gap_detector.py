"""
Rilevatore di gap di copertura
Identifica parti della domanda non coperte dalle evidenze
"""

import logging
from typing import List
import json
from app.services.ai_router import AIRouter

logger = logging.getLogger(__name__)

GAP_DETECTION_PROMPT = """
Sei un analizzatore rigoroso per documenti della Pubblica Amministrazione italiana.

La tua missione è identificare parti della domanda dell'utente che NON sono coperte dalle claim estratte.

DOMANDA UTENTE:
{user_question}

CLAIM ESTRATTE:
{claims_json}

ISTRUZIONI:
1. Analizza la domanda dell'utente punto per punto
2. Verifica se ogni parte della domanda è coperta da almeno una claim
3. Se una parte NON è coperta → identifica il gap
4. Se TUTTO è coperto → ritorna esattamente ["FULL_COVERAGE"]

FORMATO OUTPUT:
- GAP_01: descrizione parte non coperta
- GAP_02: descrizione altra parte non coperta
- Oppure: ["FULL_COVERAGE"] se tutto coperto

ESEMPIO:
Domanda: "Qual è l'importo stanziato e quando è stata approvata la delibera?"
Claim: [CLAIM_001] L'importo stanziato è di 50.000 euro
Gap: GAP_01: Data di approvazione della delibera non specificata

Rispondi SOLO con lista di gap o ["FULL_COVERAGE"]
"""

class GapDetector:
    """
    Rilevatore di gap usando Claude-3.5-Sonnet per massimo rigore
    """
    
    def __init__(self):
        self.ai_router = AIRouter()
        self.model = "claude-3-5-sonnet"
    
    async def detect_gaps(
        self, 
        user_question: str, 
        claims: List[str]
    ) -> List[str]:
        """
        Rileva gap di copertura nella domanda
        
        Args:
            user_question: Domanda dell'utente
            claims: Lista di claim estratte
            
        Returns:
            Lista di gap nel formato "GAP_XX: descrizione"
            O ["FULL_COVERAGE"] se tutto coperto
        """
        if not claims or claims == ["[NO_CLAIMS]"]:
            return [f"GAP_01: Nessuna informazione disponibile per rispondere alla domanda"]
        
        try:
            # Costruisci prompt
            prompt = GAP_DETECTION_PROMPT.format(
                user_question=user_question,
                claims_json=json.dumps(claims, ensure_ascii=False, indent=2)
            )
            
            # Usa Claude
            context = {"task_class": "analysis"}
            adapter = self.ai_router.get_chat_adapter(context)
            
            messages = [
                {
                    "role": "user",
                    "content": prompt
                }
            ]
            
            result = await adapter.generate(
                messages,
                temperature=0.1,
                max_tokens=1024
            )
            
            content = result["content"].strip()
            
            # Estrai gap
            gaps = []
            for line in content.split("\n"):
                line = line.strip()
                if line.startswith("GAP_") and ":" in line:
                    gaps.append(line)
                elif line == "FULL_COVERAGE" or line == '["FULL_COVERAGE"]':
                    return ["FULL_COVERAGE"]
            
            if not gaps:
                # Se nessun gap esplicito, verifica se dice FULL_COVERAGE
                if "FULL_COVERAGE" in content.upper() or "tutto coperto" in content.lower():
                    return ["FULL_COVERAGE"]
                # Altrimenti assume che ci sia almeno un gap generico
                return [f"GAP_01: Alcune parti della domanda non sono completamente coperte"]
            
            logger.info(f"Rilevati {len(gaps)} gap di copertura")
            
            return gaps
            
        except Exception as e:
            logger.error(f"Errore durante rilevamento gap: {e}", exc_info=True)
            return [f"GAP_01: Errore nell'analisi della copertura"]
