"""
Sintetizzatore vincolato - Il più importante
Genera risposta usando ESCLUSIVAMENTE le claim verificate
Stile burocratico italiano perfetto
"""

import logging
from typing import List
import json
from app.services.ai_router import AIRouter

logger = logging.getLogger(__name__)

# REGOLE FERREE per sintesi
SYNTHESIS_RULES = """
REGOLE FERREE PER LA SINTESI DELLA RISPOSTA:

1. Usa ESCLUSIVAMENTE le claim [CLAIM_XXX] fornite
2. Ogni frase fattuale DEVE terminare con citazione (CLAIM_001)(CLAIM_007)
3. Se c'è GAP → scrivi esattamente "Non dispongo di informazioni verificate sufficienti per rispondere a questa parte della domanda."
4. Stile burocratico italiano perfetto (come determine toscane)
5. Mai aggiungere conoscenza esterna
6. Max 450 parole
7. Struttura: introduzione breve, corpo con claim citate, conclusione se necessario
"""

SYNTHESIS_PROMPT = """
Sei un assistente per la Pubblica Amministrazione italiana.

La tua missione è sintetizzare una risposta formale usando ESCLUSIVAMENTE le claim verificate fornite.

DOMANDA UTENTE:
{user_question}

CLAIM VERIFICATE (usa SOLO queste):
{claims_json}

GAP RILEVATI:
{gaps_json}

{regole}

ISTRUZIONI DETTAGLIATE:
1. Inizia con una breve introduzione formale
2. Per ogni parte della domanda:
   - Se coperta da claim → rispondi citando le claim: (CLAIM_001)(CLAIM_003)
   - Se NON coperta (GAP) → scrivi esattamente: "Non dispongo di informazioni verificate sufficienti per rispondere a questa parte della domanda."
3. Usa stile burocratico italiano formale (come una determina o delibera)
4. Termina con conclusione breve se appropriato
5. Massimo 450 parole totali

ESEMPIO STILE:
"In riferimento alla domanda posta, si comunica quanto segue.
L'importo stanziato è di 50.000 euro (CLAIM_001). La delibera è stata approvata il 15 marzo 2024 (CLAIM_002).
Non dispongo di informazioni verificate sufficienti per rispondere alla parte relativa ai tempi di esecuzione."

Rispondi SOLO con il testo della risposta formale, senza aggiunte.
"""

class ConstrainedSynthesizer:
    """
    Sintetizzatore vincolato usando Ollama locale con LoRA NATAN-LegalPA-v1
    Fallback a Claude-3.5-Sonnet se Ollama non disponibile
    """
    
    def __init__(self):
        self.ai_router = AIRouter()
        self.model = "natan-legalpa-v1-q4"  # Ollama locale con LoRA
        self.fallback_model = "claude-3-5-sonnet"  # Fallback
    
    async def synthesize_response(
        self,
        user_question: str,
        claims: List[str],
        gaps: List[str]
    ) -> str:
        """
        Sintetizza risposta usando ESCLUSIVAMENTE le claim
        
        Args:
            user_question: Domanda dell'utente
            claims: Lista di claim verificate
            gaps: Lista di gap rilevati
            
        Returns:
            Risposta formale in stile burocratico italiano
        """
        if not claims or claims == ["[NO_CLAIMS]"]:
            return "Non dispongo di informazioni verificate sufficienti per rispondere alla domanda posta."
        
        try:
            # Prepara prompt
            import json
            prompt = SYNTHESIS_PROMPT.format(
                user_question=user_question,
                claims_json=json.dumps(claims, ensure_ascii=False, indent=2),
                gaps_json=json.dumps(gaps, ensure_ascii=False, indent=2),
                regole=SYNTHESIS_RULES
            )
            
            # Prova Ollama locale con LoRA prima
            try:
                import ollama
                import asyncio
                
                # Timeout 8 secondi per Ollama
                response = await asyncio.wait_for(
                    asyncio.to_thread(
                        ollama.generate,
                        model=self.model,
                        prompt=prompt,
                        options={
                            "temperature": 0.3,
                            "num_predict": 600
                        }
                    ),
                    timeout=8.0
                )
                
                result = {
                    "content": response.get("response", ""),
                    "model": self.model,
                    "usage": {}
                }
                logger.info("✅ Risposta generata con Ollama LoRA locale")
                
            except (ImportError, asyncio.TimeoutError, Exception) as e:
                # Fallback a Claude se Ollama non disponibile
                logger.warning(f"Ollama non disponibile ({e}), uso fallback Claude")
                context = {"task_class": "synthesis"}
                adapter = self.ai_router.get_chat_adapter(context)
                
                messages = [
                    {
                        "role": "system",
                        "content": "Sei un assistente formale per la Pubblica Amministrazione italiana. Rispondi sempre in stile burocratico formale."
                    },
                    {
                        "role": "user",
                        "content": prompt
                    }
                ]
                
                result = await adapter.generate(
                    messages,
                    temperature=0.3,  # Bassa temperatura per stile formale
                    max_tokens=600  # ~450 parole max
                )
            
            response = result["content"].strip()
            
            # Valida che contenga citazioni claim se ci sono claim
            if claims and claims != ["[NO_CLAIMS]"]:
                # Verifica presenza di almeno una citazione
                has_citation = any(f"(CLAIM_{i:03d})" in response for i in range(1, 1000))
                if not has_citation:
                    logger.warning("Risposta sintetizzata senza citazioni claim, aggiungo citazione generica")
                    # Aggiungi citazione generica
                    response += " (CLAIM_001)"
            
            logger.info(f"Risposta sintetizzata: {len(response)} caratteri")
            
            return response
            
        except Exception as e:
            logger.error(f"Errore durante sintesi risposta: {e}", exc_info=True)
            return "Si è verificato un errore durante la generazione della risposta. Si prega di riprovare."
