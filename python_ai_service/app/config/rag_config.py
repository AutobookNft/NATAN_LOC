"""
RAG Fortress Configuration Loader

Carica configurazione RAG da ai_policies.yaml basata sul profilo attivo.
Permette di switchare tra cloud/local/hybrid senza modificare codice.
"""

import os
import yaml
import logging
from pathlib import Path
from dataclasses import dataclass
from typing import Optional

logger = logging.getLogger(__name__)

@dataclass
class RAGConfig:
    """Configurazione RAG Fortress"""
    max_evidences: int = 50
    batch_size: int = 10
    text_preview_length: int = 2000
    timeout_seconds: int = 240
    extraction_provider: Optional[str] = None
    aggregation_provider: Optional[str] = None
    profile_name: str = "default"


def load_rag_config() -> RAGConfig:
    """
    Carica configurazione RAG dal file ai_policies.yaml
    
    Returns:
        RAGConfig con i parametri del profilo attivo
    """
    config_path = Path(__file__).parent / "ai_policies.yaml"
    
    try:
        with open(config_path, "r") as f:
            policies = yaml.safe_load(f)
        
        rag_config = policies.get("rag_fortress", {})
        active_profile = rag_config.get("active_profile", "cloud")
        profiles = rag_config.get("profiles", {})
        
        # Permetti override da environment variable
        active_profile = os.environ.get("RAG_PROFILE", active_profile)
        
        if active_profile not in profiles:
            logger.warning(f"Profilo RAG '{active_profile}' non trovato, uso 'cloud'")
            active_profile = "cloud"
        
        profile = profiles.get(active_profile, {})
        
        config = RAGConfig(
            max_evidences=profile.get("max_evidences", 50),
            batch_size=profile.get("batch_size", 10),
            text_preview_length=profile.get("text_preview_length", 2000),
            timeout_seconds=profile.get("timeout_seconds", 240),
            extraction_provider=profile.get("extraction_provider"),
            aggregation_provider=profile.get("aggregation_provider"),
            profile_name=active_profile
        )
        
        logger.info(f"📋 RAG Config caricata: profilo='{active_profile}', "
                   f"max_evidences={config.max_evidences}, "
                   f"batch_size={config.batch_size}")
        
        return config
        
    except Exception as e:
        logger.error(f"Errore caricamento config RAG: {e}, uso defaults")
        return RAGConfig(profile_name="default_fallback")


# Singleton - caricato una volta all'avvio
_rag_config: Optional[RAGConfig] = None

def get_rag_config() -> RAGConfig:
    """Ritorna configurazione RAG (singleton)"""
    global _rag_config
    if _rag_config is None:
        _rag_config = load_rag_config()
    return _rag_config

def reload_rag_config() -> RAGConfig:
    """Ricarica configurazione (utile per hot-reload)"""
    global _rag_config
    _rag_config = load_rag_config()
    return _rag_config
