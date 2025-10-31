"""Configuration modules for NATAN AI Gateway"""
import os
import yaml
from pathlib import Path

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

MONGODB_URI = f"mongodb://{MONGODB_USERNAME}:{MONGODB_PASSWORD}@{MONGODB_HOST}:{MONGODB_PORT}/{MONGODB_DATABASE}?authSource=admin"

# Provider API keys
ANTHROPIC_API_KEY = os.getenv("ANTHROPIC_API_KEY", "")
OPENAI_API_KEY = os.getenv("OPENAI_API_KEY", "")
AISIRU_API_KEY = os.getenv("AISIRU_API_KEY", "")
