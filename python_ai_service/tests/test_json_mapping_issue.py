"""
TEST JSON MAPPING ISSUE
Verifica se c'è un problema nel mapping JSON che causa "NO DATA".

Problema possibile:
- Python API ritorna verification_status (snake_case)
- Frontend si aspetta verificationStatus (camelCase)
- JSON.parse() NON converte automaticamente
- Frontend potrebbe avere verificationStatus = undefined

Questo spiegherebbe perché mostra "NO DATA".
"""

import pytest
import json
import logging

logger = logging.getLogger(__name__)

class TestJsonMappingIssue:
    """
    Verifica problemi di mapping JSON
    """
    
    def test_json_mapping_snake_to_camel(self):
        """
        Test: Verifica se JSON.parse() mappa correttamente snake_case → camelCase
        
        Python API ritorna:
        {
            "verification_status": "SAFE",
            "verified_claims": [...],
            "avg_urs": 0.88
        }
        
        Frontend si aspetta:
        {
            "verificationStatus": "SAFE",
            "verifiedClaims": [...],
            "avgUrs": 0.88
        }
        
        JSON.parse() NON converte automaticamente!
        Se il frontend cerca response.verificationStatus ma Python ritorna verification_status,
        il valore sarà undefined.
        """
        logger.info("\n" + "="*80)
        logger.info("TEST: Verifica mapping JSON snake_case → camelCase")
        logger.info("="*80)
        logger.info("")
        
        # Simula risposta Python API (snake_case)
        python_response_json = {
            "status": "success",
            "verification_status": "SAFE",  # snake_case
            "verified_claims": [{"text": "test"}],  # snake_case
            "avg_urs": 0.88,  # snake_case
            "answer": "Test answer"
        }
        
        # Converti in JSON string e ri-parse (come fa il frontend)
        json_string = json.dumps(python_response_json)
        parsed_response = json.loads(json_string)
        
        logger.info("Python API Response (snake_case):")
        logger.info(f"  verification_status: {parsed_response.get('verification_status')}")
        logger.info(f"  verified_claims: {len(parsed_response.get('verified_claims', []))}")
        logger.info(f"  avg_urs: {parsed_response.get('avg_urs')}")
        logger.info("")
        
        # Simula come il frontend accede ai dati (ChatInterface.ts riga 257)
        verification_status = parsed_response.get("verification_status")  # ✅ Funziona
        verification_status_camel = parsed_response.get("verificationStatus")  # ❌ undefined
        
        logger.info("Frontend access (ChatInterface.ts riga 257):")
        logger.info(f"  response.verification_status: {verification_status}")  # ✅ "SAFE"
        logger.info(f"  response.verificationStatus: {verification_status_camel}")  # ❌ undefined
        logger.info("")
        
        # BUG CHECK: Se il frontend usa response.verificationStatus invece di response.verification_status
        if verification_status and not verification_status_camel:
            logger.error("="*80)
            logger.error("❌ BUG RILEVATO: Mapping errato!")
            logger.error("="*80)
            logger.error("Python ritorna 'verification_status' (snake_case)")
            logger.error("Se frontend cerca 'verificationStatus' (camelCase), sarà undefined")
            logger.error("Questo potrebbe causare rendering 'NO DATA'")
            logger.error("="*80)
            
            # Verifica se il frontend usa il nome sbagliato
            # Se usa response.verificationStatus invece di response.verification_status,
            # il valore sarà undefined e potrebbe causare "NO DATA"
            
            # Ma il codice frontend usa response.verification_status (riga 257)
            # Quindi questo NON dovrebbe essere il problema
        
        logger.info("✅ Frontend usa response.verification_status (corretto)")
        logger.info("✅ Mapping dovrebbe funzionare correttamente")
        logger.info("")
    
    def test_undefined_verification_status_shows_no_data(self):
        """
        Test: Verifica se verificationStatus undefined causa rendering "NO DATA"
        
        Se message.verificationStatus è undefined:
        - Message.ts riga 166: Condizione check
        - Message.ts riga 192: if (message.verificationStatus)
        - Se undefined, il check è false, status non viene renderizzato
        
        MA... lo screenshot mostra "Status: NO DATA", quindi viene renderizzato!
        Quindi verificationStatus NON è undefined, ma ha valore "NO_DATA".
        """
        logger.info("\n" + "="*80)
        logger.info("TEST: Verifica se undefined causa 'NO DATA'")
        logger.info("="*80)
        logger.info("")
        
        # Simula message con verificationStatus undefined
        message_undefined = {
            "verificationStatus": None,  # undefined
            "claims": [],
            "avgUrs": 0.0
        }
        
        # Simula check Message.ts riga 166
        should_render_metadata = (
            message_undefined.get("avgUrs") is not None or
            message_undefined.get("verificationStatus")
        )
        
        logger.info("Message con verificationStatus = undefined:")
        logger.info(f"  should_render_metadata: {should_render_metadata}")
        logger.info(f"  Se avgUrs è 0.0 e verificationStatus è undefined, metadata NON viene renderizzato")
        logger.info("")
        
        # Simula message con verificationStatus = "NO_DATA"
        message_no_data = {
            "verificationStatus": "NO_DATA",  # Stringa, non undefined
            "claims": [],
            "avgUrs": 0.0
        }
        
        # Simula rendering Message.ts riga 192-206
        if message_no_data.get("verificationStatus"):
            rendered_status = message_no_data["verificationStatus"].capitalize()  # "No_data"
            logger.info("Message con verificationStatus = 'NO_DATA':")
            logger.info(f"  rendered_status: {rendered_status}")
            logger.info(f"  Questo verrebbe renderizzato come 'Status: No_data'")
            logger.info("")
        
        logger.info("="*80)
        logger.info("CONCLUSIONE:")
        logger.info("="*80)
        logger.info("")
        logger.info("Se lo screenshot mostra 'Status: NO DATA', significa:")
        logger.info("  - verificationStatus NON è undefined")
        logger.info("  - verificationStatus ha valore 'NO_DATA'")
        logger.info("  - Questo valore viene da Python API o frontend lo imposta")
        logger.info("")
        logger.info("QUINDI il problema è:")
        logger.info("  - Python API ritorna verification_status: 'NO_DATA' invece di 'SAFE'")
        logger.info("  - O frontend imposta verificationStatus = 'NO_DATA' in qualche punto")
        logger.info("")






