"""
NATAN_LOC - Python AI Gateway Service
FastAPI microservice for AI operations (embeddings, chat, RAG)
"""

import os
import logging
import sys
from pathlib import Path
from dotenv import load_dotenv

# Configure logging BEFORE importing other modules
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    handlers=[
        logging.StreamHandler(sys.stdout),
        logging.FileHandler('/tmp/natan_python_detailed.log', mode='a')
    ]
)

# Set specific loggers to DEBUG for detailed diagnostics
logging.getLogger('app.services.use_pipeline').setLevel(logging.INFO)
logging.getLogger('app.services.retriever_service').setLevel(logging.INFO)
logging.getLogger('app.services.mongodb_service').setLevel(logging.INFO)

# Load environment variables from .env file
env_path = Path(__file__).parent.parent / '.env'
load_dotenv(dotenv_path=env_path)

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from app.routers import (
    health,
    embeddings,
    chat,
    rag,
    use,
    audit,
    system,
    diagnostic,
    commands,
    admin,
    memories,
    infographics,
)

logger = logging.getLogger(__name__)
logger.info("🚀 NATAN AI Gateway starting...")

app = FastAPI(
    title="NATAN AI Gateway",
    description="AI Gateway microservice for NATAN_LOC",
    version="1.0.0"
)

# CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # TODO: Configure for production
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Include routers
app.include_router(health.router, tags=["health"])
app.include_router(system.router, prefix="/api/v1", tags=["system"])
app.include_router(embeddings.router, prefix="/api/v1", tags=["embeddings"])
app.include_router(chat.router, prefix="/api/v1", tags=["chat"])
app.include_router(rag.router, prefix="/api/v1", tags=["rag"])
app.include_router(use.router, prefix="/api/v1", tags=["use"])
app.include_router(audit.router, prefix="/api/v1", tags=["audit"])
app.include_router(diagnostic.router, prefix="/api/v1", tags=["diagnostic"])
app.include_router(commands.router, prefix="/api/v1", tags=["commands"])
app.include_router(admin.router, prefix="/api/v1", tags=["admin"])
app.include_router(memories.router, prefix="/api/v1", tags=["memories"])
app.include_router(infographics.router, prefix="/api/v1", tags=["infographics"])

