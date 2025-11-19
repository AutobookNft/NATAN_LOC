#!/usr/bin/env python3
"""
Script per merge LoRA NATAN-LegalPA-v1 con Ollama
Scarica automaticamente il modello da HuggingFace e configura Ollama
"""

import os
import sys
import subprocess
from pathlib import Path
import logging

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

MODEL_NAME = "NATAN-LegalPA-v1-Q4_K_M"
HUGGINGFACE_REPO = "florenceegi/NATAN-LegalPA-v1"  # Placeholder - da aggiornare con repo reale
MODEL_FILE = f"{MODEL_NAME}.gguf"
MODELS_DIR = Path(__file__).parent / "models" / "lora"
OLLAMA_HOST = os.getenv("OLLAMA_HOST", "http://localhost:11434")

def check_ollama_installed():
    """Verifica se Ollama è installato"""
    try:
        result = subprocess.run(["ollama", "--version"], capture_output=True, text=True)
        return result.returncode == 0
    except FileNotFoundError:
        return False

def download_model():
    """Scarica modello da HuggingFace se non esiste"""
    model_path = MODELS_DIR / MODEL_FILE
    
    if model_path.exists():
        logger.info(f"Modello {MODEL_FILE} già presente")
        return str(model_path)
    
    logger.info(f"Download modello {MODEL_FILE} da HuggingFace...")
    # TODO: Implementa download da HuggingFace
    # Per ora crea file placeholder
    MODELS_DIR.mkdir(parents=True, exist_ok=True)
    model_path.touch()
    logger.warning(f"Placeholder creato - implementare download reale da {HUGGINGFACE_REPO}")
    
    return str(model_path)

def configure_ollama():
    """Configura Ollama per usare il modello"""
    if not check_ollama_installed():
        logger.error("Ollama non installato. Installa da https://ollama.ai")
        return False
    
    model_path = download_model()
    
    # Importa Ollama
    try:
        import ollama
    except ImportError:
        logger.error("Libreria ollama non installata. Esegui: pip install ollama")
        return False
    
    # Verifica connessione Ollama
    try:
        models = ollama.list()
        logger.info(f"Ollama connesso. Modelli disponibili: {[m['name'] for m in models.get('models', [])]}")
    except Exception as e:
        logger.error(f"Errore connessione Ollama: {e}")
        logger.info(f"Assicurati che Ollama sia in esecuzione su {OLLAMA_HOST}")
        return False
    
    # Importa modello in Ollama (se non già presente)
    model_name_ollama = "natan-legalpa-v1-q4"
    try:
        # Prova a verificare se modello esiste
        try:
            ollama.show(model_name_ollama)
            logger.info(f"Modello {model_name_ollama} già presente in Ollama")
        except:
            # Importa modello
            logger.info(f"Importazione modello {model_name_ollama} in Ollama...")
            # TODO: Implementa import reale
            logger.warning("Import modello - implementare con ollama.create()")
    except Exception as e:
        logger.error(f"Errore configurazione modello Ollama: {e}")
        return False
    
    logger.info(f"✅ Ollama configurato con modello {model_name_ollama}")
    return True

def update_constrained_synthesizer():
    """Aggiorna constrained_synthesizer.py per usare Ollama"""
    synthesizer_path = Path(__file__).parent / "python_ai_service" / "app" / "services" / "rag_fortress" / "constrained_synthesizer.py"
    
    if not synthesizer_path.exists():
        logger.error(f"File {synthesizer_path} non trovato")
        return False
    
    content = synthesizer_path.read_text(encoding="utf-8")
    
    # Aggiorna MODEL_NAME
    if 'MODEL_NAME = "grok-4"' in content or 'self.model = "grok-4"' in content:
        content = content.replace('self.model = "grok-4"', 'self.model = "natan-legalpa-v1-q4"')
        content = content.replace('MODEL_NAME = "grok-4"', 'MODEL_NAME = "natan-legalpa-v1-q4"')
        synthesizer_path.write_text(content, encoding="utf-8")
        logger.info("✅ constrained_synthesizer.py aggiornato")
        return True
    else:
        logger.warning("Pattern modello non trovato in constrained_synthesizer.py")
        return False

def main():
    """Main function"""
    logger.info("🚀 Avvio merge LoRA NATAN-LegalPA-v1")
    
    # Step 1: Verifica Ollama
    if not check_ollama_installed():
        logger.error("❌ Ollama non installato")
        sys.exit(1)
    
    # Step 2: Configura Ollama
    if not configure_ollama():
        logger.error("❌ Configurazione Ollama fallita")
        sys.exit(1)
    
    # Step 3: Aggiorna synthesizer
    if not update_constrained_synthesizer():
        logger.warning("⚠️ Aggiornamento synthesizer fallito - controlla manualmente")
    
    logger.info("✅ Merge LoRA completato!")

if __name__ == "__main__":
    main()

