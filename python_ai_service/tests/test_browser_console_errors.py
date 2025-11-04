"""
TEST BROWSER CONSOLE ERRORS
Simula errori console del browser che potrebbero bloccare il rendering.

Gli errori console mostrano:
- 401 (Unauthorized) quando salva conversazione
- "User not authenticated"

Questo test verifica se questi errori potrebbero causare il rendering "NO DATA".
"""

import pytest
import httpx
import logging

logger = logging.getLogger(__name__)

class TestBrowserConsoleErrors:
    """
    Verifica errori console che potrebbero bloccare il rendering
    """
    
    @pytest.mark.asyncio
    async def test_save_conversation_401_error(self):
        """
        Test: Verifica errore 401 quando salva conversazione
        
        Console mostra:
        - "Failed to load resource: the server responded with a status of 401 (Unauthorized)"
        - "Error: User not authenticated"
        
        Questo errore potrebbe bloccare il rendering e mostrare "NO DATA"
        """
        logger.info("\n" + "="*80)
        logger.info("TEST: Errore 401 quando salva conversazione")
        logger.info("="*80)
        logger.info("")
        logger.info("Simula ESATTAMENTE quello che fa il frontend quando salva")
        logger.info("")
        
        LARAVEL_URL = "http://localhost:7000"  # Porta corretta del frontend Laravel
        
        async with httpx.AsyncClient(timeout=30.0) as client:
            # Simula chiamata salvataggio conversazione (ChatInterface.ts riga 307-388)
            try:
                response = await client.post(
                    f"{LARAVEL_URL}/api/natan/conversations/save",
                    json={
                        "conversation_id": "test_session_123",
                        "title": "Test",
                        "persona": "strategic",
                        "messages": [
                            {
                                "id": "msg_1",
                                "role": "user",
                                "content": "Cosa è Florence egl",
                                "timestamp": "2025-11-02T10:00:00Z"
                            },
                            {
                                "id": "msg_2",
                                "role": "assistant",
                                "content": "Test answer",
                                "timestamp": "2025-11-02T10:00:01Z"
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
                logger.info(f"Response Headers: {dict(response.headers)}")
                logger.info(f"Response: {response.text[:500]}")
                logger.info("")
                
                # Verifica se ritorna 401 o 404
                if response.status_code == 401:
                    logger.error("="*80)
                    logger.error("❌ BUG RILEVATO: 401 Unauthorized quando salva conversazione")
                    logger.error("="*80)
                    logger.error("Questo è ESATTAMENTE l'errore console:")
                    logger.error("- 'Failed to load resource: the server responded with a status of 401'")
                    logger.error("- 'Error: User not authenticated'")
                    logger.error("="*80)
                    pytest.fail(
                        f"❌ BUG RILEVATO: Laravel API ritorna 401 quando salva conversazione. "
                        f"Questo è l'errore console. Response: {response.text[:300]}"
                    )
                
                elif response.status_code == 404:
                    logger.error("="*80)
                    logger.error("❌ BUG RILEVATO: 404 Not Found quando salva conversazione")
                    logger.error("="*80)
                    logger.error("Route /api/natan/conversations/save non esiste")
                    logger.error("="*80)
                    pytest.fail(
                        f"❌ BUG RILEVATO: Route non esiste. Status: 404. "
                        f"Response: {response.text[:300]}"
                    )
                
                elif response.status_code == 200:
                    logger.warning("⚠️ API accetta richieste senza autenticazione (potrebbe essere un problema di sicurezza)")
                    
            except httpx.RequestError as e:
                logger.error(f"❌ Errore di connessione: {str(e)}")
                pytest.fail(f"❌ Errore di connessione: {str(e)}")
    
    @pytest.mark.asyncio
    async def test_error_might_block_rendering(self):
        """
        Test: Verifica se l'errore 401/404 potrebbe bloccare il rendering
        
        Se il frontend:
        1. Riceve risposta corretta da Python API
        2. Prova a salvare conversazione
        3. Riceve errore 401/404
        4. Potrebbe non renderizzare correttamente la risposta
        
        Questo spiegherebbe perché mostra "NO DATA" anche se Python API funziona.
        """
        logger.info("\n" + "="*80)
        logger.info("TEST: Verifica se errore salvataggio blocca rendering")
        logger.info("="*80)
        logger.info("")
        
        # Simula scenario:
        # 1. Frontend chiama Python API → SUCCESS (dati corretti)
        # 2. Frontend processa risposta → OK
        # 3. Frontend prova a salvare → ERROR 401/404
        # 4. Frontend potrebbe non renderizzare → Mostra "NO DATA"
        
        logger.info("Scenario simulato:")
        logger.info("1. Python API ritorna dati corretti ✅")
        logger.info("2. Frontend processa correttamente ✅")
        logger.info("3. Frontend prova a salvare → ERROR 401/404 ❌")
        logger.info("4. Frontend potrebbe non renderizzare → Mostra 'NO DATA' ❌")
        logger.info("")
        
        # Questo è un bug di logica frontend se succede
        logger.warning("⚠️ Se questo scenario è vero, il bug è nel frontend:")
        logger.warning("   - Errore salvataggio non dovrebbe bloccare rendering risposta")
        logger.warning("   - Risposta dovrebbe essere mostrata anche se salvataggio fallisce")
        logger.warning("")

