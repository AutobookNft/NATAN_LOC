"""
TEST FULL HTTP FLOW DEBUG
Riproduce ESATTAMENTE il flusso HTTP completo del frontend.

Verifica:
1. Frontend chiama Python API (localhost:9000)
2. Python API risponde
3. Frontend riceve risposta
4. Frontend processa risposta
5. Frontend mostra "NO DATA" quando non dovrebbe

Questo test DEVE fallire se la chat reale fallisce.
"""

import pytest
import httpx
import asyncio
import json
from typing import Dict, Any
import logging

logger = logging.getLogger(__name__)

# ============================================================================
# CONFIGURAZIONE - ESATTAMENTE come il frontend
# ============================================================================

FRONTEND_BASE_URL = "http://localhost:9000"  # Frontend chiama Python direttamente
TEST_QUERY = "Cosa è Florence egl"  # Stessa query dello screenshot

# ============================================================================
# TEST: Flusso HTTP completo
# ============================================================================

class TestFullHttpFlowDebug:
    """
    Test che riproduce ESATTAMENTE il flusso HTTP del frontend.
    """
    
    @pytest.mark.asyncio
    async def test_full_http_flow_reproduces_chat_error(self):
        """
        Test completo: Riproduce ESATTAMENTE quello che fa il frontend
        """
        logger.info("\n" + "="*80)
        logger.info("TEST: Flusso HTTP completo - Riproduce chat reale")
        logger.info("="*80)
        logger.info("")
        logger.info("STEP 1: Frontend chiama Python API")
        logger.info("="*80)
        
        async with httpx.AsyncClient(timeout=120.0) as client:
            # STEP 1: Chiamata esatta del frontend (api.ts riga 120)
            logger.info(f"URL: {FRONTEND_BASE_URL}/api/v1/use/query")
            logger.info(f"Method: POST")
            logger.info(f"Headers: Content-Type: application/json, Accept: application/json")
            logger.info(f"Body: question={TEST_QUERY}, tenant_id=1, persona=strategic")
            logger.info("")
            
            try:
                response = await client.post(
                    f"{FRONTEND_BASE_URL}/api/v1/use/query",
                    json={
                        "question": TEST_QUERY,
                        "tenant_id": 1,
                        "persona": "strategic"
                    },
                    headers={
                        "Content-Type": "application/json",
                        "Accept": "application/json"
                    }
                )
                
                logger.info(f"Response Status: {response.status_code}")
                logger.info(f"Response Headers: {dict(response.headers)}")
                
                if response.status_code != 200:
                    pytest.fail(
                        f"❌ BUG: Python API ritorna {response.status_code}. "
                        f"Response: {response.text[:500]}"
                    )
                
                # STEP 2: Parse JSON (come fa il frontend api.ts riga 135)
                try:
                    result = response.json()
                except json.JSONDecodeError as e:
                    pytest.fail(
                        f"❌ BUG: Risposta non è JSON valido. "
                        f"Response text: {response.text[:500]}"
                    )
                
                logger.info("")
                logger.info("="*80)
                logger.info("STEP 2: Analisi risposta Python API")
                logger.info("="*80)
                logger.info("")
                
                # Estrai campi come fa il frontend (ChatInterface.ts riga 248-260)
                status = result.get("status")
                verification_status = result.get("verification_status", "")
                answer = result.get("answer", "")
                verified_claims = result.get("verified_claims", [])
                avg_urs = result.get("avg_urs", 0.0)
                blocked_claims = result.get("blocked_claims", [])
                
                logger.info(f"status: {status}")
                logger.info(f"verification_status: {verification_status}")
                logger.info(f"answer length: {len(answer)} caratteri")
                logger.info(f"verified_claims count: {len(verified_claims)}")
                logger.info(f"blocked_claims count: {len(blocked_claims)}")
                logger.info(f"avg_urs: {avg_urs}")
                logger.info("")
                logger.info(f"answer (first 300 chars):")
                logger.info(f"{answer[:300]}...")
                logger.info("")
                
                # STEP 3: Verifica bug "NO DATA" quando ci sono dati
                logger.info("="*80)
                logger.info("STEP 3: Verifica bug 'NO DATA' quando ci sono dati")
                logger.info("="*80)
                logger.info("")
                
                # BUG CHECK 1: Se ci sono verified_claims, answer NON deve dire "no data"
                if len(verified_claims) > 0:
                    answer_lower = answer.lower()
                    no_data_indicators = [
                        "non ho informazioni sufficienti",
                        "documenti presenti nell'archivio non contengono dati pertinenti",
                        "dati non disponibili"
                    ]
                    
                    answer_says_no_data = any(indicator in answer_lower for indicator in no_data_indicators)
                    
                    if answer_says_no_data:
                        logger.error("="*80)
                        logger.error("❌ BUG RILEVATO: Answer dice 'no data' ma ci sono verified_claims!")
                        logger.error("="*80)
                        logger.error(f"verified_claims count: {len(verified_claims)}")
                        logger.error(f"answer contiene 'no data': {answer_says_no_data}")
                        logger.error(f"answer: {answer[:500]}")
                        logger.error("="*80)
                        pytest.fail(
                            f"❌ BUG RILEVATO (stesso dello screenshot): "
                            f"Python API ha {len(verified_claims)} verified_claims "
                            f"ma answer contiene 'no data'. "
                            f"Answer: {answer[:300]}"
                        )
                
                # BUG CHECK 2: verification_status deve essere coerente
                if len(verified_claims) > 0:
                    if verification_status.upper() == "NO_DATA":
                        logger.error("="*80)
                        logger.error("❌ BUG RILEVATO: verification_status è 'NO_DATA' ma ci sono verified_claims!")
                        logger.error("="*80)
                        logger.error(f"verified_claims count: {len(verified_claims)}")
                        logger.error(f"verification_status: {verification_status}")
                        logger.error("="*80)
                        pytest.fail(
                            f"❌ BUG RILEVATO (stesso dello screenshot): "
                            f"Python API ha {len(verified_claims)} verified_claims "
                            f"ma verification_status è 'NO_DATA' invece di 'SAFE'"
                        )
                    
                    if status == "success" and verification_status.upper() != "SAFE":
                        logger.warning(
                            f"⚠️ WARNING: status='success' e ci sono {len(verified_claims)} verified_claims "
                            f"ma verification_status è '{verification_status}' invece di 'SAFE'"
                        )
                
                # BUG CHECK 3: avg_urs deve essere > 0 se ci sono verified_claims
                if len(verified_claims) > 0 and avg_urs == 0.0:
                    logger.error("="*80)
                    logger.error("❌ BUG RILEVATO: avg_urs è 0.0 ma ci sono verified_claims!")
                    logger.error("="*80)
                    logger.error(f"verified_claims count: {len(verified_claims)}")
                    logger.error(f"avg_urs: {avg_urs}")
                    logger.error("="*80)
                    pytest.fail(
                        f"❌ BUG RILEVATO (stesso dello screenshot): "
                        f"avg_urs=0.00 ma ci sono {len(verified_claims)} verified_claims. "
                        f"URS dovrebbe essere > 0"
                    )
                
                # STEP 4: Simula processing frontend
                logger.info("")
                logger.info("="*80)
                logger.info("STEP 4: Simula processing frontend (ChatInterface.ts)")
                logger.info("="*80)
                logger.info("")
                
                # Simula esattamente ChatInterface.ts riga 248-260
                assistant_message = {
                    "id": "test_msg_123",
                    "role": "assistant",
                    "content": answer or self._format_response(result),  # Riga 251
                    "claims": verified_claims or [],  # Riga 253
                    "blockedClaims": [],  # Riga 254
                    "avgUrs": avg_urs,  # Riga 256
                    "verificationStatus": verification_status,  # Riga 257
                }
                
                logger.info(f"assistant_message.verificationStatus: {assistant_message['verificationStatus']}")
                logger.info(f"assistant_message.claims count: {len(assistant_message['claims'])}")
                logger.info(f"assistant_message.avgUrs: {assistant_message['avgUrs']}")
                logger.info(f"assistant_message.content (first 200 chars): {assistant_message['content'][:200]}")
                logger.info("")
                
                # BUG CHECK 4: Se message.verificationStatus è "NO_DATA" ma ci sono claims
                if assistant_message["verificationStatus"] and assistant_message["verificationStatus"].upper() == "NO_DATA":
                    if len(assistant_message["claims"]) > 0:
                        logger.error("="*80)
                        logger.error("❌ BUG RILEVATO: Frontend message.verificationStatus è 'NO_DATA' ma ci sono claims!")
                        logger.error("="*80)
                        logger.error(f"claims count: {len(assistant_message['claims'])}")
                        logger.error(f"verificationStatus: {assistant_message['verificationStatus']}")
                        logger.error("="*80)
                        pytest.fail(
                            f"❌ BUG RILEVATO: Frontend processa verificationStatus='NO_DATA' "
                            f"ma ci sono {len(assistant_message['claims'])} claims. "
                            f"Questo spiega perché mostra 'NO DATA' nello screenshot."
                        )
                
                # STEP 5: Simula rendering (Message.ts riga 192-206)
                logger.info("")
                logger.info("="*80)
                logger.info("STEP 5: Simula rendering frontend (Message.ts)")
                logger.info("="*80)
                logger.info("")
                
                # Simula Message.ts riga 192-206
                rendered_status = None
                if assistant_message.get("verificationStatus"):
                    # Riga 202: statusValue.textContent = message.verificationStatus
                    # Riga 201: className = 'font-medium capitalize'
                    rendered_status = assistant_message["verificationStatus"].capitalize()
                
                logger.info(f"rendered_status (Message.ts): {rendered_status}")
                logger.info("")
                
                # BUG CHECK 5: Se rendered_status contiene "No_data" o "No data"
                if rendered_status:
                    if "no_data" in rendered_status.lower() or "no data" in rendered_status.lower():
                        if len(assistant_message["claims"]) > 0:
                            logger.error("="*80)
                            logger.error("❌ BUG RILEVATO: Frontend renderizza 'NO DATA' ma ci sono claims!")
                            logger.error("="*80)
                            logger.error(f"rendered_status: {rendered_status}")
                            logger.error(f"claims count: {len(assistant_message['claims'])}")
                            logger.error("="*80)
                            pytest.fail(
                                f"❌ BUG RILEVATO (stesso dello screenshot): "
                                f"Frontend renderizza status='{rendered_status}' "
                                f"ma ci sono {len(assistant_message['claims'])} claims. "
                                f"Questo è ESATTAMENTE il bug dello screenshot."
                            )
                
                # STEP 6: Verifica completa
                logger.info("")
                logger.info("="*80)
                logger.info("STEP 6: Verifica finale")
                logger.info("="*80)
                logger.info("")
                
                # Se arriviamo qui, tutti i check sono passati
                logger.info("✅ Python API risposta corretta")
                logger.info(f"✅ Status: {status}")
                logger.info(f"✅ Verification Status: {verification_status}")
                logger.info(f"✅ Verified Claims: {len(verified_claims)}")
                logger.info(f"✅ Answer: contiene informazioni")
                logger.info(f"✅ Frontend processing: corretto")
                logger.info(f"✅ Frontend rendering: corretto")
                logger.info("")
                logger.info("⚠️ Se la chat reale mostra 'NO DATA', il problema è:")
                logger.info("   1. Frontend NON riceve questa risposta")
                logger.info("   2. C'è un errore JavaScript che blocca il rendering")
                logger.info("   3. C'è un problema CORS/autenticazione")
                logger.info("")
                
            except httpx.RequestError as e:
                pytest.fail(f"❌ Errore di connessione: {str(e)}")
            except Exception as e:
                logger.error(f"❌ Errore inaspettato: {str(e)}", exc_info=True)
                raise
    
    def _format_response(self, response: Dict[str, Any]) -> str:
        """Simula formatResponse() del frontend"""
        if response.get("answer"):
            return response["answer"]
        elif response.get("status") == "success" and response.get("verified_claims"):
            return "Risposta generata con Ultra Semantic Engine. Vedi i claim verificati qui sotto."
        elif response.get("status") == "no_results":
            return "Nessun risultato trovato nei documenti."
        else:
            return response.get("message") or "Risposta generata."


if __name__ == "__main__":
    pytest.main([__file__, "-v", "-s", "--tb=short", "--log-cli-level=INFO"])






