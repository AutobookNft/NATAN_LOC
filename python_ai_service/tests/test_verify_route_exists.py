"""
TEST: Verifica se la route Laravel esiste
Se il frontend chiama Laravel invece di Python, la route deve esistere.
"""

import pytest
import httpx
import logging

logger = logging.getLogger(__name__)

LARAVEL_URL = "http://localhost:7000"  # Porta corretta del frontend Laravel

class TestLaravelRouteExists:
    """Verifica se le route Laravel esistono"""
    
    @pytest.mark.asyncio
    async def test_laravel_route_use_query_exists(self):
        """Verifica se /api/v1/use/query esiste in Laravel"""
        logger.info(f"\n{'='*80}")
        logger.info("TEST: Verifica route Laravel /api/v1/use/query")
        logger.info(f"{'='*80}\n")
        
        async with httpx.AsyncClient(timeout=10.0, follow_redirects=True) as client:
            # Prova chiamata GET (per vedere se la route esiste)
            try:
                response = await client.get(f"{LARAVEL_URL}/api/v1/use/query")
                logger.info(f"GET Status: {response.status_code}")
                logger.info(f"GET Response: {response.text[:200]}")
            except Exception as e:
                logger.info(f"GET Error: {str(e)}")
            
            # Prova chiamata POST (come fa il frontend)
            try:
                response = await client.post(
                    f"{LARAVEL_URL}/api/v1/use/query",
                    json={"question": "test", "tenant_id": 1},
                    headers={"Content-Type": "application/json"}
                )
                logger.info(f"POST Status: {response.status_code}")
                logger.info(f"POST Response: {response.text[:200]}")
                
                if response.status_code == 404:
                    logger.warning("⚠️ Route /api/v1/use/query NON esiste in Laravel")
                    logger.warning("⚠️ Frontend deve chiamare Python direttamente (porta 9000)")
                elif response.status_code == 405:
                    logger.warning("⚠️ Route esiste ma metodo non permesso")
                elif response.status_code == 401:
                    logger.warning("⚠️ Route esiste ma richiede autenticazione")
                else:
                    logger.info(f"✅ Route esiste e ritorna {response.status_code}")
                    
            except Exception as e:
                logger.error(f"POST Error: {str(e)}")
    
    @pytest.mark.asyncio
    async def test_laravel_route_conversations_save_exists(self):
        """Verifica se /natan/conversations/save esiste in Laravel"""
        logger.info(f"\n{'='*80}")
        logger.info("TEST: Verifica route Laravel /natan/conversations/save")
        logger.info(f"{'='*80}\n")
        
        async with httpx.AsyncClient(timeout=10.0, follow_redirects=True) as client:
            try:
                response = await client.post(
                    f"{LARAVEL_URL}/natan/conversations/save",
                    json={"conversation_id": "test"},
                    headers={"Content-Type": "application/json"}
                )
                logger.info(f"POST Status: {response.status_code}")
                logger.info(f"POST Response: {response.text[:300]}")
                
                if response.status_code == 404:
                    pytest.fail(
                        f"❌ BUG RILEVATO: Route /natan/conversations/save NON esiste in Laravel. Status: 404"
                    )
                elif response.status_code == 401:
                    logger.warning("⚠️ Route esiste ma richiede autenticazione (questo spiega 401)")
                    
            except Exception as e:
                logger.error(f"POST Error: {str(e)}")

