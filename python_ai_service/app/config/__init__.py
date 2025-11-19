"""Configuration modules for NATAN AI Gateway"""
import os
import yaml
from pathlib import Path
from dotenv import load_dotenv

# Load environment variables from .env file
# This ensures .env is loaded before any configuration is read
env_path = Path(__file__).parent.parent.parent / '.env'
load_dotenv(dotenv_path=env_path, override=True)

# Load AI policies
POLICIES_PATH = Path(__file__).parent / "ai_policies.yaml"

def load_policies():
    """Load AI policies from YAML file"""
    with open(POLICIES_PATH, 'r') as f:
        return yaml.safe_load(f)

# Load policies on import
POLICIES = load_policies()

# MongoDB configuration
MONGODB_HOST = os.getenv("MONGO_DB_HOST", "localhost")
MONGODB_PORT = int(os.getenv("MONGO_DB_PORT", "27017"))
MONGODB_DATABASE = os.getenv("MONGO_DB_DATABASE", "natan_ai_core")
MONGODB_USERNAME = os.getenv("MONGO_DB_USERNAME", "natan_user")
MONGODB_PASSWORD = os.getenv("MONGO_DB_PASSWORD", "")

# Prefer MONGODB_URI if set (for Atlas connection strings)
MONGODB_URI = os.getenv("MONGODB_URI", None)

if not MONGODB_URI:
    # Build MongoDB URI from components
    if MONGODB_PASSWORD:
        # For Atlas: use mongodb+srv with SSL
        if "mongodb.net" in MONGODB_HOST or "atlas" in MONGODB_HOST.lower():
            MONGODB_URI = f"mongodb+srv://{MONGODB_USERNAME}:{MONGODB_PASSWORD}@{MONGODB_HOST}/{MONGODB_DATABASE}?retryWrites=true&w=majority&tls=true"
        else:
            # For DocumentDB or self-hosted: use standard connection
            MONGODB_URI = f"mongodb://{MONGODB_USERNAME}:{MONGODB_PASSWORD}@{MONGODB_HOST}:{MONGODB_PORT}/{MONGODB_DATABASE}?authSource=admin"
    else:
        MONGODB_URI = f"mongodb://{MONGODB_HOST}:{MONGODB_PORT}/{MONGODB_DATABASE}"

# Provider API keys
ANTHROPIC_API_KEY = os.getenv("ANTHROPIC_API_KEY", "")
OPENAI_API_KEY = os.getenv("OPENAI_API_KEY", "")
AISIRU_API_KEY = os.getenv("AISIRU_API_KEY", "")
