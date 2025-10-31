"""
NATAN_LOC - Python AI Gateway Service
FastAPI microservice for AI operations (embeddings, chat, RAG)
"""

import os
from pathlib import Path
from dotenv import load_dotenv

# Load environment variables from .env file
env_path = Path(__file__).parent.parent / '.env'
load_dotenv(dotenv_path=env_path)

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from app.routers import health, embeddings, chat, rag, use, audit, system

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

