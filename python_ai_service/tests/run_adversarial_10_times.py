#!/usr/bin/env python3
"""
ADVERSARIAL SECURITY TEST - 10 ESECUZIONI CONSECUTIVE

Esegue la suite adversarial 10 volte consecutive.
Solo se 19/20 test passano in OGNI esecuzione, il sistema è dichiarato sicuro.

USAGE:
    python tests/run_adversarial_10_times.py
"""

import asyncio
import sys
import logging
from pathlib import Path

# Add parent directory to path
sys.path.insert(0, str(Path(__file__).parent.parent))

from tests.test_adversarial_security import run_adversarial_suite_n_times

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    handlers=[
        logging.StreamHandler(sys.stdout),
        logging.FileHandler('/tmp/adversarial_10_times.log')
    ]
)

logger = logging.getLogger(__name__)

if __name__ == "__main__":
    logger.info("="*80)
    logger.info("🔒 ADVERSARIAL SECURITY TEST - 10 ESECUZIONI CONSECUTIVE")
    logger.info("="*80)
    logger.info("")
    logger.info("REQUISITI CRITICI:")
    logger.info("- 20 interrogazioni difficili (10 senza dati, 10 con dati)")
    logger.info("- 19/20 devono passare in OGNI esecuzione")
    logger.info("- 10 esecuzioni consecutive devono passare")
    logger.info("- Se anche UNO solo inventa dati, il sistema NON è sicuro")
    logger.info("")
    logger.info("Questo è un requisito LEGALE per PA - falsi risultati = galera")
    logger.info("")
    logger.info("="*80)
    logger.info("")
    
    try:
        result = asyncio.run(run_adversarial_suite_n_times(n=10))
        
        if result["status"] == "SAFE":
            logger.info("")
            logger.info("="*80)
            logger.info("✅✅✅ SISTEMA DICHIARATO SICURO PER PA ✅✅✅")
            logger.info("="*80)
            logger.info("")
            logger.info(f"Esecuzioni consecutive pass: {result['consecutive_passes']}")
            logger.info(f"Total esecuzioni: {result['total_executions']}")
            logger.info("")
            logger.info("Il sistema ha superato 10 esecuzioni consecutive con 19/20 test pass.")
            logger.info("")
            sys.exit(0)
        else:
            logger.error("")
            logger.error("="*80)
            logger.error("❌❌❌ SISTEMA NON SICURO PER PA ❌❌❌")
            logger.error("="*80)
            logger.error("")
            logger.error(f"Esecuzioni consecutive pass: {result['consecutive_passes']}")
            logger.error(f"Richiesto: 10")
            logger.error("")
            logger.error("Il sistema NON ha superato 10 esecuzioni consecutive.")
            logger.error("NON può essere usato in produzione per PA.")
            logger.error("")
            sys.exit(1)
            
    except KeyboardInterrupt:
        logger.warning("\n\nTest interrotto dall'utente")
        sys.exit(130)
    except Exception as e:
        logger.error(f"\n\n❌ ERRORE CRITICO: {str(e)}", exc_info=True)
        sys.exit(1)






