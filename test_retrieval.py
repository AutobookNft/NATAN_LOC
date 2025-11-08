#!/usr/bin/env python3
import sys
import asyncio
from pathlib import Path

NATAN_LOC_ROOT = Path(__file__).parent
sys.path.insert(0, str(NATAN_LOC_ROOT / "python_ai_service"))

from dotenv import load_dotenv
load_dotenv(NATAN_LOC_ROOT / "python_ai_service" / ".env", override=True)

from app.services.mongodb_service import MongoDBService
from app.services.ai_router import AIRouter
from app.services.retriever_service import RetrieverService

async def test():
    output_lines = []
    tenant_id = 2
    query = "Analizza le principali aree di investimento del Comune negli ultimi 12 mesi"
    
    output_lines.append(f"\n{'='*80}")
    output_lines.append(f"🔍 TEST RETRIEVAL - Tenant ID: {tenant_id}")
    output_lines.append(f"{'='*80}\n")
    
    # 1. Verifica connessione
    if not MongoDBService.is_connected():
        output_lines.append("❌ MongoDB NON connesso!")
        return "\n".join(output_lines)
    output_lines.append("✅ MongoDB connesso\n")
    
    # 2. Verifica documenti
    all_docs = MongoDBService.find_documents("documents", {"tenant_id": tenant_id}, limit=None)
    output_lines.append(f"📄 Documenti totali: {len(all_docs)}")
    
    docs_with_embeddings = [d for d in all_docs if d.get("embedding")]
    output_lines.append(f"📄 Con embeddings: {len(docs_with_embeddings)}\n")
    
    # 3. Genera embedding query
    ai_router = AIRouter()
    context = {"tenant_id": tenant_id, "task_class": "RAG"}
    adapter = ai_router.get_embedding_adapter(context)
    embed_result = await adapter.embed(query)
    query_embedding = embed_result["embedding"]
    output_lines.append(f"✅ Embedding query generato (dim: {len(query_embedding)})\n")
    
    # 4. Test retriever
    retriever = RetrieverService()
    chunks = retriever.retrieve(
        query_embedding=query_embedding,
        tenant_id=tenant_id,
        limit=20,
        min_score=0.15
    )
    
    output_lines.append(f"📊 Chunks recuperati: {len(chunks)}\n")
    
    if chunks:
        output_lines.append("✅ PRIMI 3 CHUNKS:")
        for i, chunk in enumerate(chunks[:3], 1):
            output_lines.append(f"\n{i}. Chunk:")
            output_lines.append(f"   Keys: {list(chunk.keys())}")
            chunk_text = chunk.get("chunk_text") or chunk.get("text") or chunk.get("content") or ""
            output_lines.append(f"   chunk_text type: {type(chunk_text)}")
            output_lines.append(f"   chunk_text length: {len(str(chunk_text))}")
            output_lines.append(f"   chunk_text preview: {str(chunk_text)[:200]}")
            output_lines.append(f"   similarity: {chunk.get('similarity', 'N/A')}")
            output_lines.append(f"   document_id: {chunk.get('document_id', 'N/A')[:50]}")
    else:
        output_lines.append("❌ NESSUN CHUNK RECUPERATO!")
    
    output_lines.append(f"\n{'='*80}\n")
    
    result = "\n".join(output_lines)
    # Write to file
    with open("/tmp/test_retrieval_output.txt", "w") as f:
        f.write(result)
    
    return result

if __name__ == '__main__':
    result = asyncio.run(test())
    print(result)

