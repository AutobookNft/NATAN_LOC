import asyncio
import sys
sys.path.insert(0, '/app')
from app.services.rag_fortress.retriever import HybridRetriever

async def test():
    retriever = HybridRetriever()
    
    # Query che dovrebbe trovare il doc BICI
