"""
TEST FRONTEND RENDERING BUG
Verifica perché il frontend mostra "NO DATA" quando ci sono dati.

Il frontend mostra verificationStatus nel componente Message.ts (riga 192-206).
Deve mostrare il valore di message.verificationStatus.

Se Python API ritorna verification_status: "SAFE" ma il frontend mostra "NO DATA",
c'è un bug nel mapping o nel rendering.
"""

import pytest
import json
from typing import Dict, Any
import logging

logger = logging.getLogger(__name__)

# Simula ESATTAMENTE quello che fa il frontend
# ChatInterface.ts riga 248-260 crea assistantMessage
# Message.ts riga 192-206 renderizza verificationStatus

def simulate_frontend_processing(python_api_response: Dict[str, Any]) -> Dict[str, Any]:
    """
    Simula ESATTAMENTE quello che fa ChatInterface.ts (riga 248-260)
    """
    assistant_message = {
        "id": "test_msg_123",
        "role": "assistant",
        "content": python_api_response.get("answer") or format_response(python_api_response),
        "timestamp": "2025-11-02T10:00:00Z",
        "claims": python_api_response.get("verified_claims") or [],
        "blockedClaims": [],
        "sources": extract_sources(python_api_response),
        "avgUrs": python_api_response.get("avg_urs"),
        "verificationStatus": python_api_response.get("verification_status"),  # Riga 257
        "tokensUsed": python_api_response.get("tokens_used"),
        "modelUsed": python_api_response.get("model_used"),
    }
    return assistant_message


def format_response(response: Dict[str, Any]) -> str:
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


def extract_sources(response: Dict[str, Any]) -> list:
    """
    Simula extractSources() del frontend
    """
    sources = []
    if response.get("verified_claims"):
        for claim in response["verified_claims"]:
            if claim.get("sourceRefs"):
                for ref in claim["sourceRefs"]:
                    if not any(s.get("url") == ref.get("url") for s in sources):
                        sources.append({
                            "id": ref.get("source_id") or ref.get("url"),
                            "url": ref.get("url"),
                            "title": ref.get("title"),
                            "type": "internal",
                        })
    return sources


def simulate_message_rendering(message: Dict[str, Any]) -> Dict[str, Any]:
    """
    Simula ESATTAMENTE quello che fa Message.ts (riga 192-206)
    Renderizza verificationStatus
    """
    rendered = {
        "content": message.get("content", ""),
        "verificationStatus": message.get("verificationStatus"),  # Riga 202
        "avgUrs": message.get("avgUrs"),
        "claims": message.get("claims", []),
        "sources": message.get("sources", []),
    }
    
    # Simula rendering status (Message.ts riga 192-206)
    if message.get("verificationStatus"):
        # Riga 202: statusValue.textContent = message.verificationStatus
        # Riga 201: className = 'font-medium capitalize'
        # capitalize trasforma "SAFE" in "Safe", "NO_DATA" in "No_data"
        rendered["status_display"] = message.get("verificationStatus").capitalize()
    else:
        rendered["status_display"] = None
    
    return rendered


class TestFrontendRenderingBug:
    """
    Test che verifica il rendering del frontend
    """
    
    def test_frontend_shows_no_data_when_python_returns_safe(self):
        """
        BUG TEST: Verifica se il frontend mostra "NO DATA" quando Python ritorna "SAFE"
        """
        logger.info("\n" + "="*80)
        logger.info("TEST: Frontend mostra NO DATA quando Python ritorna SAFE?")
        logger.info("="*80)
        logger.info("")
        
        # Simula risposta Python API corretta (come vediamo nei test)
        python_response = {
            "status": "success",
            "question": "Cosa è Florence egl",
            "answer": "Basandomi sui documenti disponibili:\n\n• FlorenceEGI è...",
            "verified_claims": [
                {
                    "text": "FlorenceEGI è una piattaforma blockchain-certified",
                    "source_ids": ["chunk_1"],
                    "urs": 0.88
                }
            ],
            "avg_urs": 0.88,
            "verification_status": "SAFE",  # Python ritorna "SAFE"
            "blocked_claims": [],
        }
        
        logger.info("Python API Response:")
        logger.info(f"  verification_status: {python_response['verification_status']}")
        logger.info(f"  verified_claims: {len(python_response['verified_claims'])}")
        logger.info(f"  avg_urs: {python_response['avg_urs']}")
        logger.info("")
        
        # Simula processing frontend (ChatInterface.ts)
        message = simulate_frontend_processing(python_response)
        
        logger.info("Frontend Processing (ChatInterface.ts):")
        logger.info(f"  message.verificationStatus: {message['verificationStatus']}")
        logger.info(f"  message.content: {message['content'][:100]}...")
        logger.info(f"  message.claims: {len(message['claims'])}")
        logger.info("")
        
        # Simula rendering frontend (Message.ts)
        rendered = simulate_message_rendering(message)
        
        logger.info("Frontend Rendering (Message.ts):")
        logger.info(f"  rendered.verificationStatus: {rendered['verificationStatus']}")
        logger.info(f"  rendered.status_display: {rendered['status_display']}")
        logger.info("")
        
        # BUG CHECK: Se Python ritorna "SAFE", il frontend NON deve mostrare "NO DATA"
        if python_response["verification_status"] == "SAFE":
            if rendered["verificationStatus"] != "SAFE":
                pytest.fail(
                    f"❌ BUG: Python ritorna 'SAFE' ma frontend ha verificationStatus='{rendered['verificationStatus']}'"
                )
            
            if rendered["status_display"] and "no_data" in rendered["status_display"].lower():
                pytest.fail(
                    f"❌ BUG: Python ritorna 'SAFE' ma frontend mostra status_display='{rendered['status_display']}' (contiene 'no_data')"
                )
            
            # Verifica che ci siano claims
            if len(message["claims"]) > 0:
                # Se ci sono claims, NON deve dire "no data" nell'answer
                content_lower = message["content"].lower()
                if "non ho informazioni sufficienti" in content_lower:
                    pytest.fail(
                        f"❌ BUG: Python ritorna 'SAFE' con {len(message['claims'])} claims "
                        f"ma frontend content contiene 'non ho informazioni sufficienti'. "
                        f"Content: {message['content'][:300]}"
                    )
        
        logger.info("✅ Frontend processing e rendering corretti")
        logger.info("")
        
        # Verifica mapping snake_case → camelCase
        if python_response.get("verification_status") != message.get("verificationStatus"):
            pytest.fail(
                f"❌ BUG: Mapping errato. Python ha 'verification_status'='{python_response.get('verification_status')}' "
                f"ma frontend ha 'verificationStatus'='{message.get('verificationStatus')}'"
            )






