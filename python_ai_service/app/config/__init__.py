"""Configuration module for NATAN AI Gateway"""

import os
from dotenv import load_dotenv

load_dotenv()

# MongoDB Configuration
MONGO_URI = os.getenv("MONGO_URI", "mongodb://localhost:27017/natan_ai_core")
MONGO_DB_NAME = os.getenv("MONGO_DB_NAME", "natan_ai_core")

# OpenAI Configuration
OPENAI_API_KEY = os.getenv("OPENAI_API_KEY")
OPENAI_ORGANIZATION = os.getenv("OPENAI_ORGANIZATION")

# Anthropic Configuration
ANTHROPIC_API_KEY = os.getenv("ANTHROPIC_API_KEY")

# Ollama Configuration (Local Mode)
OLLAMA_HOST = os.getenv("OLLAMA_HOST", "http://localhost:11434")

# AI Policy Configuration
AI_POLICY_FILE = os.getenv("AI_POLICY_FILE", "config/ai_policies.yaml")

