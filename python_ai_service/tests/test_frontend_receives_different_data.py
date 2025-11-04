"""
TEST: Verifica se il frontend riceve dati diversi da Python API

Problema possibile:
- Python API ritorna correttamente (SAFE, verified_claims)
- Ma il frontend riceve dati diversi (NO_DATA, no claims)
- Causa possibile: middleware, proxy, o trasformazione dati

Questo test verifica se c'è una differenza tra quello che Python ritorna
e quello che il frontend riceve.
"""

import pytest
import httpx
import asyncio
import json
import logging

logger = logging.getLogger(__name__)

PYTHON_API_URL = "http://localhost:9000"

class TestFrontendReceivesDifferentData:
    """
    Verifica se il frontend riceve dati diversi
    """
    
    @pytest.mark.asyncio
    async def test_simulate_frontend_exact_call(self):
        """
        Test: Simula ESATTAMENTE la chiamata del frontend con tutti i dettagli
        
        Frontend fa:
        - fetchWithAuth('/api/v1/use/query', {...})
        - Headers: Content-Type, Accept, CSRF token, Authorization (se presente)
        - Body: question, tenant_id, persona
        
        Verifica se la risposta è diversa quando chiamata "come il frontend"
        """
        logger.info("\n" + "="*80)
        logger.info("TEST: Simula chiamata frontend esatta")
        logger.info("="*80)
        logger.info("")
        
        query = "Cosa è Florence egl"
        
        async with httpx.AsyncClient(timeout=120.0) as client:
            # Chiamata ESATTAMENTE come il frontend (api.ts riga 120)
            url = f"{PYTHON_API_URL}/api/v1/use/query"
            
            # Headers esatti come fetchWithAuth (api.ts riga 48-63)
            headers = {
                "Content-Type": "application/json",
                "Accept": "application/json",
                # NO CSRF token (frontend potrebbe non averlo)
                # NO Authorization (frontend potrebbe non averlo configurato)
            }
            
            body = {
                "question": query,
                "tenant_id": 1,
                "persona": "strategic"
            }
            
            logger.info(f"URL: {url}")
            logger.info(f"Headers: {headers}")
            logger.info(f"Body: {json.dumps(body, indent=2)}")
            logger.info("")
            
            response = await client.post(
                url,
                json=body,
                headers=headers
            )
            
            logger.info(f"Response Status: {response.status_code}")
            logger.info(f"Response Headers: {dict(response.headers)}")
            
            if response.status_code != 200:
                pytest.fail(f"❌ API ritorna {response.status_code}: {response.text[:500]}")
            
            # Parse JSON
            try:
                result = response.json()
            except json.JSONDecodeError as e:
                pytest.fail(f"❌ Risposta non è JSON: {str(e)}")
            
            logger.info("")
            logger.info("="*80)
            logger.info("RISPOSTA RICEVUTA (come frontend):")
            logger.info("="*80)
            logger.info(json.dumps({
                "status": result.get("status"),
                "verification_status": result.get("verification_status"),
                "verified_claims_count": len(result.get("verified_claims", [])),
                "avg_urs": result.get("avg_urs"),
                "answer_preview": result.get("answer", "")[:200]
            }, indent=2))
            logger.info("")
            
            # Verifica
            verification_status = result.get("verification_status", "").upper()
            verified_claims = result.get("verified_claims", [])
            answer = result.get("answer", "").lower()
            
            # BUG CHECK: Se riceve "NO_DATA" ma dovrebbe avere dati
            if verification_status == "NO_DATA":
                if len(verified_claims) > 0:
                    pytest.fail(
                        f"❌ BUG RILEVATO: Frontend riceve verification_status='NO_DATA' "
                        f"ma ci sono {len(verified_claims)} verified_claims. "
                        f"Questo è ESATTAMENTE il bug dello screenshot!"
                    )
                
                # Verifica se answer dice "no data"
                no_data_in_answer = "non ho informazioni sufficienti" in answer
                if no_data_in_answer:
                    logger.warning(
                        f"⚠️ Frontend riceve 'NO_DATA' con answer che dice 'no data'. "
                        f"Questo è corretto se NON ci sono dati."
                    )
            
            logger.info(f"✅ Frontend riceve verification_status='{verification_status}'")
            logger.info(f"✅ Frontend riceve {len(verified_claims)} verified_claims")
            logger.info("")






