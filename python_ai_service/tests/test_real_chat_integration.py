"""
REAL CHAT INTEGRATION TEST
Riproduce ESATTAMENTE quello che succede nella chat reale.

Questo test DEVE fallire con gli stessi errori della chat:
- 401 Unauthorized quando salva conversazioni
- "NO DATA" quando ci sono dati disponibili
- Errori di autenticazione

NON CORREGGERE NULLA fino a quando questo test non rivela gli stessi errori della chat.
"""

import pytest
import httpx
import asyncio
from typing import Dict, Any
import logging

logger = logging.getLogger(__name__)

# ============================================================================
# CONFIGURAZIONE - Deve matchare la chat reale
# ============================================================================

PYTHON_API_URL = "http://localhost:9000"
LARAVEL_API_URL = "http://localhost:8000"
TEST_QUERY = "Cosa è Florence egl"  # Stessa query dello screenshot (con typo)

# ============================================================================
# TEST: Riproduce chiamata chat reale
# ============================================================================

class TestRealChatIntegration:
    """
    Test che riproduce ESATTAMENTE la chat reale.
    Deve fallire con gli stessi errori.
    """
    
    @pytest.mark.asyncio
    async def test_chat_query_without_auth(self):
        """
        Test 1: Query senza autenticazione (come nella chat reale)
        
        Questo DEVE fallire con 401 se la chat fallisce con 401.
        """
        logger.info("\n" + "="*80)
        logger.info("TEST 1: Query senza autenticazione (come nella chat reale)")
        logger.info("="*80)
        logger.info(f"Query: {TEST_QUERY}")
        logger.info(f"Python API URL: {PYTHON_API_URL}")
        logger.info("")
        
        # Step 1: Chiamata a Python API (come fa la chat reale)
        async with httpx.AsyncClient(timeout=120.0) as client:
            try:
                response = await client.post(
                    f"{PYTHON_API_URL}/api/v1/use/query",
                    json={
                        "question": TEST_QUERY,
                        "tenant_id": 1,
                        "persona": "strategic"
                    },
                    headers={
                        "Content-Type": "application/json"
                    }
                )
                
                logger.info(f"Python API Status: {response.status_code}")
                logger.info(f"Python API Response: {response.text[:500]}")
                
                if response.status_code != 200:
                    pytest.fail(f"❌ Python API returned {response.status_code}: {response.text}")
                
                result = response.json()
                
                # Step 2: Verifica che NON dica "NO DATA" se ci sono dati
                status = result.get("status")
                verification_status = result.get("verification_status", "").upper()
                answer = result.get("answer", "").lower()
                verified_claims = result.get("verified_claims", [])
                
                logger.info(f"Status: {status}")
                logger.info(f"Verification Status: {verification_status}")
                logger.info(f"Verified Claims: {len(verified_claims)}")
                logger.info(f"Answer preview: {answer[:200]}")
                
                # CRITICAL CHECK: Se ci sono verified_claims, NON deve dire "no data"
                if len(verified_claims) > 0:
                    no_data_indicators = [
                        "non ho informazioni sufficienti",
                        "dati non disponibili",
                        "documenti presenti nell'archivio non contengono"
                    ]
                    
                    answer_says_no_data = any(indicator in answer for indicator in no_data_indicators)
                    
                    if answer_says_no_data:
                        pytest.fail(
                            f"❌ BUG: Sistema ha {len(verified_claims)} verified_claims ma answer dice 'no data'. "
                            f"Answer: {answer[:300]}"
                        )
                    
                    if verification_status == "NO_DATA":
                        pytest.fail(
                            f"❌ BUG: Sistema ha {len(verified_claims)} verified_claims ma verification_status è 'NO_DATA'. "
                            f"Status dovrebbe essere 'SAFE'."
                        )
                
                # Step 3: Tentativo di salvare conversazione (come fa la chat reale)
                # Questo DEVE fallire con 401 se la chat fallisce con 401
                try:
                    save_response = await client.post(
                        f"{LARAVEL_API_URL}/api/natan/conversations/save",
                        json={
                            "conversation_id": "test_session_123",
                            "title": "Test conversation",
                            "persona": "strategic",
                            "messages": [
                                {
                                    "id": "msg_1",
                                    "role": "user",
                                    "content": TEST_QUERY,
                                    "timestamp": "2025-11-02T10:00:00Z"
                                },
                                {
                                    "id": "msg_2",
                                    "role": "assistant",
                                    "content": result.get("answer", ""),
                                    "timestamp": "2025-11-02T10:00:01Z"
                                }
                            ],
                            "tokens_used": result.get("tokens_used", {}),
                            "model_used": result.get("model_used", "")
                        },
                        headers={
                            "Content-Type": "application/json",
                            "Accept": "application/json"
                        }
                    )
                    
                    logger.info(f"Laravel Save Status: {save_response.status_code}")
                    logger.info(f"Laravel Save Response: {save_response.text[:500]}")
                    
                    if save_response.status_code == 401:
                        pytest.fail(
                            f"❌ BUG: Laravel API ritorna 401 Unauthorized quando salva conversazione. "
                            f"Response: {save_response.text}"
                        )
                    
                    if save_response.status_code != 200:
                        pytest.fail(
                            f"❌ BUG: Laravel API ritorna {save_response.status_code} quando salva conversazione. "
                            f"Response: {save_response.text}"
                        )
                
                except httpx.HTTPStatusError as e:
                    if e.response.status_code == 401:
                        pytest.fail(
                            f"❌ BUG: Errore 401 Unauthorized quando salva conversazione. "
                            f"Response: {e.response.text}"
                        )
                    raise
                
            except httpx.RequestError as e:
                pytest.fail(f"❌ Errore di connessione: {str(e)}")
    
    @pytest.mark.asyncio
    async def test_chat_query_reproduces_screenshot_error(self):
        """
        Test 2: Riproduce ESATTAMENTE l'errore dello screenshot
        
        Screenshot mostra:
        - Query: "Cosa è Florence egl"
        - Risposta: "NO DATA" con Status: NO_DATA
        - URS Medio: 0.00
        - Ma dovrebbe avere dati su FlorenceEGI
        
        Questo test DEVE fallire se la chat reale fallisce.
        """
        logger.info("\n" + "="*80)
        logger.info("TEST 2: Riproduce errore screenshot")
        logger.info("="*80)
        logger.info("Query screenshot: 'Cosa è Florence egl'")
        logger.info("Errore screenshot: Status NO_DATA quando dovrebbe avere dati")
        logger.info("")
        
        query = "Cosa è Florence egl"  # Stessa query dello screenshot (con typo)
        
        async with httpx.AsyncClient(timeout=120.0) as client:
            response = await client.post(
                f"{PYTHON_API_URL}/api/v1/use/query",
                json={
                    "question": query,
                    "tenant_id": 1,
                    "persona": "strategic"
                },
                headers={
                    "Content-Type": "application/json"
                }
            )
            
            if response.status_code != 200:
                pytest.fail(f"❌ Python API returned {response.status_code}")
            
            result = response.json()
            
            status = result.get("status")
            verification_status = result.get("verification_status", "").upper()
            answer = result.get("answer", "").lower()
            verified_claims = result.get("verified_claims", [])
            avg_urs = result.get("avg_urs", 0.0)
            
            logger.info(f"Status: {status}")
            logger.info(f"Verification Status: {verification_status}")
            logger.info(f"Verified Claims: {len(verified_claims)}")
            logger.info(f"Avg URS: {avg_urs}")
            logger.info(f"Answer: {answer[:300]}")
            
            # BUG CHECK: Se verification_status è NO_DATA, dovrebbe essere perché NON ci sono dati
            # Ma se ci sono verified_claims, allora c'è un BUG
            if verification_status == "NO_DATA" and len(verified_claims) > 0:
                pytest.fail(
                    f"❌ BUG RILEVATO (stesso dello screenshot): "
                    f"verification_status='NO_DATA' ma ci sono {len(verified_claims)} verified_claims. "
                    f"Status dovrebbe essere 'SAFE'. Answer: {answer[:300]}"
                )
            
            # BUG CHECK: Se answer dice "no data" ma ci sono verified_claims
            no_data_indicators = [
                "non ho informazioni sufficienti",
                "documenti presenti nell'archivio non contengono dati pertinenti",
                "dati non disponibili"
            ]
            
            answer_says_no_data = any(indicator in answer for indicator in no_data_indicators)
            
            if answer_says_no_data and len(verified_claims) > 0:
                pytest.fail(
                    f"❌ BUG RILEVATO (stesso dello screenshot): "
                    f"Answer dice 'no data' ma ci sono {len(verified_claims)} verified_claims. "
                    f"Answer: {answer[:300]}"
                )
            
            # BUG CHECK: Se avg_urs è 0.0 ma ci sono verified_claims
            if avg_urs == 0.0 and len(verified_claims) > 0:
                pytest.fail(
                    f"❌ BUG RILEVATO (stesso dello screenshot): "
                    f"avg_urs=0.00 ma ci sono {len(verified_claims)} verified_claims. "
                    f"URS dovrebbe essere > 0."
                )
            
            logger.info("✅ Test passato - nessun bug rilevato")
    
    @pytest.mark.asyncio
    async def test_save_conversation_requires_auth(self):
        """
        Test 3: Verifica che salvare conversazione richieda autenticazione
        
        Se la chat fallisce con 401, questo test DEVE fallire.
        """
        logger.info("\n" + "="*80)
        logger.info("TEST 3: Salvataggio conversazione senza autenticazione")
        logger.info("="*80)
        logger.info("Questo test verifica se l'API richiede autenticazione")
        logger.info("")
        
        async with httpx.AsyncClient(timeout=30.0) as client:
            try:
                response = await client.post(
                    f"{LARAVEL_API_URL}/api/natan/conversations/save",
                    json={
                        "conversation_id": "test_session_123",
                        "title": "Test",
                        "persona": "strategic",
                        "messages": [
                            {
                                "id": "msg_1",
                                "role": "user",
                                "content": "Test",
                                "timestamp": "2025-11-02T10:00:00Z"
                            }
                        ]
                    },
                    headers={
                        "Content-Type": "application/json",
                        "Accept": "application/json"
                        # NO AUTH HEADER - come nella chat reale quando fallisce
                    }
                )
                
                logger.info(f"Response Status: {response.status_code}")
                logger.info(f"Response: {response.text[:500]}")
                
                # Se ritorna 401, questo è il bug che vediamo nella chat
                if response.status_code == 401:
                    pytest.fail(
                        f"❌ BUG RILEVATO (stesso della chat): "
                        f"L'API ritorna 401 Unauthorized quando salva conversazione senza auth. "
                        f"Response: {response.text}"
                    )
                
                # Se ritorna 200, allora l'auth non è richiesta (potrebbe essere un altro problema)
                if response.status_code == 200:
                    logger.warning("⚠️ API accetta richieste senza autenticazione - potrebbe essere un problema di sicurezza")
                
            except httpx.RequestError as e:
                pytest.fail(f"❌ Errore di connessione: {str(e)}")


# ============================================================================
# MAIN TEST RUNNER - Esegue tutti i test
# ============================================================================

if __name__ == "__main__":
    import sys
    pytest.main([__file__, "-v", "-s", "--tb=short", "--log-cli-level=INFO"])






