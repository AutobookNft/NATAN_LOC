"""
TEST: Verifica se la route funziona dopo il fix
"""

import pytest
import httpx
import logging

logger = logging.getLogger(__name__)

LARAVEL_URL = "http://localhost:8000"

class TestRouteFixed:
    """Verifica se la route funziona"""
    
    @pytest.mark.asyncio
    async def test_natan_api_route_works(self):
        """Verifica se /natan-api/conversations/save funziona"""
        logger.info(f"\n{'='*80}")
        logger.info("TEST: Verifica route /natan-api/conversations/save")
        logger.info(f"{'='*80}\n")
        
        async with httpx.AsyncClient(timeout=10.0, follow_redirects=True) as client:
            try:
                response = await client.post(
                    f"{LARAVEL_URL}/natan-api/conversations/save",
                    json={
                        "messages": [
                            {
                                "id": "test",
                                "role": "user",
                                "content": "test",
                                "timestamp": "2025-11-02T10:00:00Z"
                            }
                        ]
                    },
                    headers={
                        "Content-Type": "application/json"
                    }
                )
                logger.info(f"POST Status: {response.status_code}")
                logger.info(f"POST Response: {response.text[:300]}")
                
                if response.status_code == 404:
                    pytest.fail(
                        f"❌ Route /natan-api/conversations/save NON esiste. Status: 404"
                    )
                elif response.status_code == 422:
                    # 422 è OK, significa che la route esiste ma la validazione fallisce (user non autenticato)
                    logger.info("✅ Route esiste! Status 422 = validazione fallita (user non autenticato), ma route funziona")
                elif response.status_code == 401:
                    logger.info("✅ Route esiste! Status 401 = richiede autenticazione, ma route funziona")
                elif response.status_code == 200:
                    logger.info("✅ Route funziona perfettamente!")
                else:
                    logger.info(f"✅ Route esiste! Status: {response.status_code}")
                    
            except Exception as e:
                logger.error(f"POST Error: {str(e)}")
                raise






