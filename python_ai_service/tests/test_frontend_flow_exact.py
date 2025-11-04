"""
TEST FRONTEND FLOW EXACT
Riproduce ESATTAMENTE il flusso del frontend:
1. Frontend chiama /api/v1/use/query
2. Verifica risposta ricevuta
3. Verifica come viene processata la risposta
4. Verifica rendering "NO DATA"

Questo test DEVE fallire con gli stessi errori della chat.
"""

import pytest
import httpx
import asyncio
import json
from typing import Dict, Any
import logging

logger = logging.getLogger(__name__)

# ============================================================================
# CONFIGURAZIONE - Deve matchare ESATTAMENTE il frontend
# ============================================================================

# Il frontend usa: import.meta.env.VITE_API_BASE_URL || 'http://localhost:9000'
# Quindi chiama Python direttamente su porta 9000
FRONTEND_BASE_URL = "http://localhost:9000"
TEST_QUERY = "Cosa è Florence egl"  # Stessa query dello screenshot

# ============================================================================
# TEST: Riproduce ESATTAMENTE il flusso frontend
# ============================================================================

class TestFrontendFlowExact:
    """
    Test che riproduce ESATTAMENTE il flusso del frontend.
    Simula quello che fa ChatInterface.ts
    """
    
    @pytest.mark.asyncio
    async def test_frontend_calls_python_directly(self):
        """
        Test 1: Frontend chiama Python direttamente (come fa realmente)
        
        Il frontend chiama: baseUrl + '/api/v1/use/query'
        Dove baseUrl = 'http://localhost:9000' (Python)
        """
        logger.info("\n" + "="*80)
        logger.info("TEST 1: Frontend chiama Python direttamente")
        logger.info("="*80)
        logger.info(f"Frontend baseUrl: {FRONTEND_BASE_URL}")
        logger.info(f"Endpoint chiamato: {FRONTEND_BASE_URL}/api/v1/use/query")
        logger.info(f"Query: {TEST_QUERY}")
        logger.info("")
        
        async with httpx.AsyncClient(timeout=120.0) as client:
            # Simula ESATTAMENTE quello che fa il frontend
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
            
            if response.status_code != 200:
                pytest.fail(f"❌ API ritorna {response.status_code}: {response.text}")
            
            result = response.json()
            
            # Log completo della risposta
            logger.info("="*80)
            logger.info("RISPOSTA COMPLETA DA PYTHON API:")
            logger.info("="*80)
            logger.info(json.dumps(result, indent=2, ensure_ascii=False))
            logger.info("="*80)
            logger.info("")
            
            # Estrai campi come fa il frontend
            status = result.get("status")
            verification_status = result.get("verification_status", "")
            answer = result.get("answer", "")
            verified_claims = result.get("verified_claims", [])
            avg_urs = result.get("avg_urs", 0.0)
            
            logger.info(f"status: {status}")
            logger.info(f"verification_status: {verification_status}")
            logger.info(f"answer length: {len(answer)}")
            logger.info(f"verified_claims count: {len(verified_claims)}")
            logger.info(f"avg_urs: {avg_urs}")
            logger.info("")
            
            # BUG CHECK 1: Se ci sono verified_claims, NON deve dire "no data"
            if len(verified_claims) > 0:
                answer_lower = answer.lower()
                no_data_indicators = [
                    "non ho informazioni sufficienti",
                    "documenti presenti nell'archivio non contengono dati pertinenti",
                    "dati non disponibili"
                ]
                
                answer_says_no_data = any(indicator in answer_lower for indicator in no_data_indicators)
                
                if answer_says_no_data:
                    pytest.fail(
                        f"❌ BUG RILEVATO: Python API ha {len(verified_claims)} verified_claims "
                        f"ma answer contiene 'no data'. Answer: {answer[:300]}"
                    )
                
                if verification_status.upper() == "NO_DATA":
                    pytest.fail(
                        f"❌ BUG RILEVATO: Python API ha {len(verified_claims)} verified_claims "
                        f"ma verification_status è 'NO_DATA' invece di 'SAFE'"
                    )
            
            # BUG CHECK 2: Se status è "success" e ci sono verified_claims, verification_status deve essere "SAFE"
            if status == "success" and len(verified_claims) > 0:
                if verification_status.upper() != "SAFE":
                    pytest.fail(
                        f"❌ BUG RILEVATO: status='success' e ci sono {len(verified_claims)} verified_claims "
                        f"ma verification_status è '{verification_status}' invece di 'SAFE'"
                    )
            
            # BUG CHECK 3: Se avg_urs è 0.0 ma ci sono verified_claims
            if avg_urs == 0.0 and len(verified_claims) > 0:
                pytest.fail(
                    f"❌ BUG RILEVATO: avg_urs=0.0 ma ci sono {len(verified_claims)} verified_claims. "
                    f"URS dovrebbe essere > 0"
                )
            
            logger.info("✅ Python API risposta corretta")
            return result
    
    @pytest.mark.asyncio
    async def test_frontend_processes_response(self):
        """
        Test 2: Simula come il frontend processa la risposta
        
        Il frontend fa:
        1. response.answer || this.formatResponse(response)
        2. response.verified_claims || []
        3. response.verification_status
        4. response.avg_urs
        
        Verifica che il mapping sia corretto.
        """
        logger.info("\n" + "="*80)
        logger.info("TEST 2: Frontend processa risposta (simula ChatInterface.ts)")
        logger.info("="*80)
        logger.info("")
        
        # Ottieni risposta da Python
        async with httpx.AsyncClient(timeout=120.0) as client:
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
            
            if response.status_code != 200:
                pytest.fail(f"❌ API ritorna {response.status_code}")
            
            result = response.json()
        
        # Simula ESATTAMENTE quello che fa ChatInterface.ts (riga 248-260)
        assistant_message = {
            "id": "test_msg_123",
            "role": "assistant",
            "content": result.get("answer") or self._format_response(result),  # Riga 251
            "claims": result.get("verified_claims") or [],  # Riga 253
            "avgUrs": result.get("avg_urs"),  # Riga 256
            "verificationStatus": result.get("verification_status"),  # Riga 257
        }
        
        logger.info("="*80)
        logger.info("MESSAGGIO PROCESSATO DAL FRONTEND (simulato):")
        logger.info("="*80)
        logger.info(json.dumps(assistant_message, indent=2, ensure_ascii=False))
        logger.info("="*80)
        logger.info("")
        
        # Verifica mapping
        content = assistant_message.get("content", "").lower()
        verification_status = assistant_message.get("verificationStatus", "").upper()
        claims_count = len(assistant_message.get("claims", []))
        avg_urs = assistant_message.get("avgUrs", 0.0)
        
        logger.info(f"content (first 200 chars): {content[:200]}")
        logger.info(f"verificationStatus: {verification_status}")
        logger.info(f"claims count: {claims_count}")
        logger.info(f"avgUrs: {avg_urs}")
        logger.info("")
        
        # BUG CHECK: Se il messaggio processato mostra "NO DATA" quando ci sono dati
        if claims_count > 0:
            no_data_indicators = [
                "non ho informazioni sufficienti",
                "documenti presenti nell'archivio non contengono dati pertinenti"
            ]
            
            content_says_no_data = any(indicator in content for indicator in no_data_indicators)
            
            if content_says_no_data:
                pytest.fail(
                    f"❌ BUG RILEVATO: Messaggio processato dice 'no data' ma ci sono {claims_count} claims. "
                    f"Content: {content[:300]}"
                )
            
            if verification_status == "NO_DATA":
                pytest.fail(
                    f"❌ BUG RILEVATO: verificationStatus è 'NO_DATA' ma ci sono {claims_count} claims. "
                    f"Dovrebbe essere 'SAFE'"
                )
            
            if avg_urs == 0.0:
                pytest.fail(
                    f"❌ BUG RILEVATO: avgUrs è 0.0 ma ci sono {claims_count} claims. "
                    f"URS dovrebbe essere > 0"
                )
        
        logger.info("✅ Frontend processa risposta correttamente")
    
    def _format_response(self, response: Dict[str, Any]) -> str:
        """
        Simula formatResponse() del frontend (ChatInterface.ts riga 393-406)
        """
        if response.get("answer"):
            return response["answer"]
        elif response.get("status") == "success" and response.get("verified_claims"):
            return "Risposta generata con Ultra Semantic Engine. Vedi i claim verificati qui sotto."
        elif response.get("status") == "no_results":
            return "Nessun risultato trovato nei documenti."
        else:
            return response.get("message") or "Risposta generata."






