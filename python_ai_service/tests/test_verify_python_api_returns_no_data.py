"""
TEST: Verifica se Python API ritorna "NO_DATA" in alcuni casi

Lo screenshot mostra "Status: NO DATA", quindi verificationStatus = "NO_DATA".
Se Python API ritorna "NO_DATA" in alcuni casi, questo spiegherebbe il bug.

Questo test DEVE fallire se Python API ritorna "NO_DATA" quando non dovrebbe.
"""

import pytest
import httpx
import asyncio
import json
import logging

logger = logging.getLogger(__name__)

PYTHON_API_URL = "http://localhost:9000"

class TestVerifyPythonApiReturnsNoData:
    """
    Verifica se Python API ritorna "NO_DATA" quando non dovrebbe
    """
    
    @pytest.mark.asyncio
    async def test_python_api_query_florenceegi_should_not_return_no_data(self):
        """
        Test: Verifica che Python API NON ritorni "NO_DATA" per query su FlorenceEGI
        
        Query: "Cosa è Florence egl" (stessa dello screenshot)
        Dovrebbe avere dati su FlorenceEGI, quindi NON dovrebbe ritornare "NO_DATA"
        """
        logger.info("\n" + "="*80)
        logger.info("TEST: Python API ritorna NO_DATA per query FlorenceEGI?")
        logger.info("="*80)
        logger.info("")
        
        query = "Cosa è Florence egl"  # Stessa query dello screenshot
        
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
                pytest.fail(f"❌ Python API ritorna {response.status_code}")
            
            result = response.json()
            
            status = result.get("status")
            verification_status = result.get("verification_status", "").upper()
            verified_claims = result.get("verified_claims", [])
            answer = result.get("answer", "")
            
            logger.info(f"status: {status}")
            logger.info(f"verification_status: {verification_status}")
            logger.info(f"verified_claims count: {len(verified_claims)}")
            logger.info(f"answer (first 200 chars): {answer[:200]}")
            logger.info("")
            
            # BUG CHECK: Se ritorna "NO_DATA" ma ci sono verified_claims
            if verification_status == "NO_DATA":
                if len(verified_claims) > 0:
                    logger.error("="*80)
                    logger.error("❌ BUG RILEVATO: Python API ritorna 'NO_DATA' ma ha verified_claims!")
                    logger.error("="*80)
                    logger.error(f"verification_status: {verification_status}")
                    logger.error(f"verified_claims count: {len(verified_claims)}")
                    logger.error(f"answer: {answer[:300]}")
                    logger.error("="*80)
                    pytest.fail(
                        f"❌ BUG RILEVATO (stesso dello screenshot): "
                        f"Python API ritorna verification_status='NO_DATA' "
                        f"ma ha {len(verified_claims)} verified_claims. "
                        f"Questo è ESATTAMENTE il bug dello screenshot!"
                    )
                
                # Se NON ci sono verified_claims, "NO_DATA" è corretto
                logger.info("✅ Python API ritorna 'NO_DATA' e NON ha verified_claims (corretto)")
            else:
                logger.info(f"✅ Python API ritorna verification_status='{verification_status}' (NON 'NO_DATA')")
    
    @pytest.mark.asyncio
    async def test_python_api_multiple_queries(self):
        """
        Test: Esegue la stessa query 5 volte per vedere se a volte ritorna "NO_DATA"
        
        Se Python API a volte ritorna "NO_DATA" e a volte "SAFE" per la stessa query,
        c'è un problema di inconsistenza.
        """
        logger.info("\n" + "="*80)
        logger.info("TEST: Esegue query 5 volte per verificare consistenza")
        logger.info("="*80)
        logger.info("")
        
        query = "Cosa è Florence egl"
        
        results = []
        async with httpx.AsyncClient(timeout=120.0) as client:
            for i in range(5):
                logger.info(f"Esecuzione {i+1}/5...")
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
                
                if response.status_code == 200:
                    result = response.json()
                    verification_status = result.get("verification_status", "").upper()
                    verified_claims_count = len(result.get("verified_claims", []))
                    results.append({
                        "execution": i+1,
                        "verification_status": verification_status,
                        "verified_claims": verified_claims_count
                    })
                    
                    logger.info(f"  Esecuzione {i+1}: verification_status={verification_status}, claims={verified_claims_count}")
        
        logger.info("")
        logger.info("="*80)
        logger.info("RISULTATI 5 ESECUZIONI:")
        logger.info("="*80)
        for r in results:
            logger.info(f"  Esecuzione {r['execution']}: {r['verification_status']} ({r['verified_claims']} claims)")
        logger.info("")
        
        # Verifica consistenza
        no_data_count = sum(1 for r in results if r["verification_status"] == "NO_DATA")
        safe_count = sum(1 for r in results if r["verification_status"] == "SAFE")
        
        logger.info(f"Verification Status 'NO_DATA': {no_data_count}/5")
        logger.info(f"Verification Status 'SAFE': {safe_count}/5")
        logger.info("")
        
        # BUG CHECK: Se alcune esecuzioni ritornano "NO_DATA" quando dovrebbero ritornare "SAFE"
        if no_data_count > 0:
            no_data_results = [r for r in results if r["verification_status"] == "NO_DATA" and r["verified_claims"] > 0]
            if len(no_data_results) > 0:
                pytest.fail(
                    f"❌ BUG RILEVATO: {len(no_data_results)} esecuzioni ritornano 'NO_DATA' "
                    f"ma hanno verified_claims. Questa inconsistenza spiegherebbe il bug!"
                )
            
            logger.warning(
                f"⚠️ WARNING: {no_data_count} esecuzioni ritornano 'NO_DATA'. "
                f"Verifica se hanno verified_claims o no."
            )






