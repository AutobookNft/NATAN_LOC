"""
Verificatore di evidenze - Turno 1
Verifica rigorosa della rilevanza delle evidenze recuperate
"""

import logging
from typing import List, Dict
import json
from app.services.ai_router import AIRouter
from app.services.providers import AnthropicChatAdapter

logger = logging.getLogger(__name__)

# Prompt ultra-rigoroso in italiano
VERIFICATION_PROMPT = """
Sei un verificatore rigoroso di evidenze per documenti della Pubblica Amministrazione italiana.

La tua missione è verificare se ogni evidenza recuperata è DIRETTAMENTE rilevante per la domanda dell'utente.

DOMANDA UTENTE:
{user_question}

EVIDENZE DA VERIFICARE:
{evidences_json}

ISTRUZIONI RIGOROSE:
1. Analizza ogni evidenza singolarmente
2. Verifica se l'evidenza contiene informazioni DIRETTAMENTE pertinenti alla domanda
3. Estrai la citazione esatta (exact_quote) che supporta la domanda, se presente
4. Assegna uno score di rilevanza 0-10 (10 = massima rilevanza diretta)
5. Se l'evidenza è solo tangenzialmente rilevante, score < 5
6. Se l'evidenza NON è rilevante, score < 3

OUTPUT RICHIESTO (JSON array):
[
  {{
    "evidence_id": "id_evidenza",
    "is_directly_relevant": true/false,
    "exact_quote": "citazione esatta o null",
    "supports_user_question": true/false,
    "relevance_score": 0-10 (float)
  }}
]

IMPORTANTE:
- Rispondi SOLO con JSON valido
- Non aggiungere testo prima o dopo il JSON
- Se un'evidenza non è rilevante, is_directly_relevant = false
- exact_quote deve essere una citazione letterale dall'evidenza, non parafrasi
"""

class EvidenceVerifier:
    """
    Verificatore di evidenze usando Claude-3.5-Sonnet o Llama-3.1-70B in JSON mode
    """
    
    def __init__(self):
        self.ai_router = AIRouter()
        self.model = "claude-3-5-sonnet"  # Claude-3.5-Sonnet o Llama-3.1-70B
    
    async def verify_evidence(
        self, 
        user_question: str, 
        evidences: List[Dict]
    ) -> List[Dict]:
        """
        Verifica evidenze recuperate
        
        Args:
            user_question: Domanda dell'utente
            evidences: Lista di evidenze da verificare
            
        Returns:
            Lista di evidenze verificate con score di rilevanza
        """
        if not evidences:
            logger.warning("Nessuna evidenza da verificare")
            return []
        
        try:
            # Prepara evidenze per il prompt
            evidences_for_prompt = [
                {
                    "evidence_id": ev.get("evidence_id", ""),
                    "content": ev.get("content", "")[:500],  # Limita lunghezza
                    "source": ev.get("source", ""),
                    "score": ev.get("score", 0.0)
                }
                for ev in evidences
            ]
            
            # Costruisci prompt
            prompt = VERIFICATION_PROMPT.format(
                user_question=user_question,
                evidences_json=json.dumps(evidences_for_prompt, ensure_ascii=False, indent=2)
            )
            
            # Usa Claude in JSON mode
            context = {"task_class": "verification"}
            adapter = self.ai_router.get_chat_adapter(context)
            
            # Forza JSON mode per Claude
            messages = [
                {
                    "role": "user",
                    "content": prompt
                }
            ]
            
            # Genera risposta con temperature bassa per massima precisione
            result = await adapter.generate(
                messages,
                temperature=0.1,  # Bassa temperatura per risposte deterministiche
                max_tokens=4096
            )
            
            content = result["content"].strip()
            
            # Estrai JSON dalla risposta (rimuovi markdown code blocks se presenti)
            if "```json" in content:
                content = content.split("```json")[1].split("```")[0].strip()
            elif "```" in content:
                content = content.split("```")[1].split("```")[0].strip()
            
            # Parse JSON
            verified_evidences = json.loads(content)
            
            # Valida e arricchisci risultati
            verified_results = []
            for verified in verified_evidences:
                # Trova evidenza originale per mantenere metadata
                original_ev = next(
                    (ev for ev in evidences if ev.get("evidence_id") == verified.get("evidence_id")),
                    None
                )
                
                if original_ev:
                    verified_results.append({
                        "evidence_id": verified.get("evidence_id"),
                        "is_directly_relevant": verified.get("is_directly_relevant", False),
                        "exact_quote": verified.get("exact_quote"),
                        "supports_user_question": verified.get("supports_user_question", False),
                        "relevance_score": float(verified.get("relevance_score", 0.0)),
                        "content": original_ev.get("content", ""),
                        "source": original_ev.get("source", ""),
                        "metadata": original_ev.get("metadata", {})
                    })
            
            logger.info(f"Verificate {len(verified_results)} evidenze su {len(evidences)}")
            
            return verified_results
            
        except json.JSONDecodeError as e:
            logger.error(f"Errore parsing JSON da verificatore: {e}")
            logger.error(f"Contenuto ricevuto: {content[:500]}")
            # Fallback: ritorna evidenze con score basso
            return [
                {
                    **ev,
                    "is_directly_relevant": False,
                    "relevance_score": 0.0
                }
                for ev in evidences
            ]
        except Exception as e:
            logger.error(f"Errore durante verifica evidenze: {e}", exc_info=True)
            return []
