#!/usr/bin/env python3
"""
Script diagnostico per verificare perché il retriever non trova documenti
"""

import sys
import json
import argparse
from pathlib import Path
import numpy as np

# Aggiungi path per importare moduli
NATAN_LOC_ROOT = Path(__file__).parent.parent.parent.parent
sys.path.insert(0, str(NATAN_LOC_ROOT / "python_ai_service"))

from dotenv import load_dotenv
load_dotenv(NATAN_LOC_ROOT / "python_ai_service" / ".env", override=True)

from app.services.mongodb_service import MongoDBService
from app.services.ai_router import AIRouter
from app.services.retriever_service import RetrieverService


def cosine_similarity(vec1, vec2):
    """Calculate cosine similarity"""
    vec1 = np.array(vec1)
    vec2 = np.array(vec2)
    dot_product = np.dot(vec1, vec2)
    norm1 = np.linalg.norm(vec1)
    norm2 = np.linalg.norm(vec2)
    if norm1 == 0 or norm2 == 0:
        return 0.0
    return float(dot_product / (norm1 * norm2))


async def diagnose_retrieval(tenant_id: int, query: str = None):
    """
    Diagnostica completa del retrieval
    """
    print(f"\n{'='*80}")
    print(f"🔍 DIAGNOSTICA RETRIEVAL - Tenant ID: {tenant_id}")
    print(f"{'='*80}\n")
    
    # 1. Verifica connessione MongoDB
    print("1️⃣ Verifica connessione MongoDB...")
    if not MongoDBService.is_connected():
        print("❌ MongoDB NON connesso!")
        return
    print("✅ MongoDB connesso\n")
    
    # 2. Verifica documenti totali per tenant
    print(f"2️⃣ Verifica documenti per tenant {tenant_id}...")
    all_docs = MongoDBService.find_documents("documents", {"tenant_id": tenant_id}, limit=None)
    print(f"   📄 Documenti totali: {len(all_docs)}")
    
    if len(all_docs) == 0:
        print("   ❌ PROBLEMA: Nessun documento trovato per questo tenant!")
        print(f"   💡 Verifica che gli atti siano stati importati con tenant_id={tenant_id}")
        return
    
    # 3. Verifica documenti con embeddings
    print(f"\n3️⃣ Verifica embeddings...")
    docs_with_doc_embedding = [d for d in all_docs if d.get("embedding")]
    docs_with_chunk_embeddings = []
    for doc in all_docs:
        chunks = doc.get("content", {}).get("chunks", [])
        if chunks and any(c.get("embedding") for c in chunks):
            docs_with_chunk_embeddings.append(doc)
    
    print(f"   📄 Documenti con embedding a livello documento: {len(docs_with_doc_embedding)}")
    print(f"   📄 Documenti con embeddings a livello chunk: {len(docs_with_chunk_embeddings)}")
    
    if len(docs_with_doc_embedding) == 0 and len(docs_with_chunk_embeddings) == 0:
        print("   ❌ PROBLEMA: Nessun documento ha embeddings!")
        print("   💡 Gli atti devono essere importati con --mongodb per generare embeddings")
        return
    
    # 4. Verifica tipi di documento
    print(f"\n4️⃣ Tipi di documento trovati...")
    doc_types = {}
    for doc in all_docs:
        doc_type = doc.get("document_type", "unknown")
        doc_types[doc_type] = doc_types.get(doc_type, 0) + 1
    for doc_type, count in doc_types.items():
        print(f"   📋 {doc_type}: {count}")
    
    # 5. Test query se fornita
    if query:
        print(f"\n5️⃣ Test query: '{query}'...")
        
        # Genera embedding query
        ai_router = AIRouter()
        context = {
            "tenant_id": tenant_id,
            "task_class": "RAG"
        }
        adapter = ai_router.get_embedding_adapter(context)
        embed_result = await adapter.embed(query)
        query_embedding = embed_result["embedding"]
        
        print(f"   ✅ Embedding query generato (dimensioni: {len(query_embedding)})")
        
        # Test retriever
        retriever = RetrieverService()
        chunks = retriever.retrieve(
            query_embedding=query_embedding,
            tenant_id=tenant_id,
            limit=20,
            min_score=0.15
        )
        
        print(f"   📊 Chunks recuperati: {len(chunks)}")
        
        if len(chunks) == 0:
            print("   ❌ PROBLEMA: Retriever non ha trovato chunks!")
            print("   🔍 Analisi similarità...")
            
            # Analizza similarità manualmente
            similarities = []
            for doc in docs_with_doc_embedding[:10]:  # Campiona primi 10
                doc_embedding = doc.get("embedding")
                if doc_embedding:
                    sim = cosine_similarity(query_embedding, doc_embedding)
                    similarities.append({
                        "document_id": doc.get("document_id", "N/A")[:50],
                        "similarity": sim,
                        "title": doc.get("title", "N/A")[:50]
                    })
            
            # Controlla chunks
            for doc in docs_with_chunk_embeddings[:5]:  # Campiona primi 5
                chunks_in_doc = doc.get("content", {}).get("chunks", [])
                for chunk in chunks_in_doc[:3]:  # Primi 3 chunks
                    chunk_embedding = chunk.get("embedding")
                    if chunk_embedding:
                        sim = cosine_similarity(query_embedding, chunk_embedding)
                        similarities.append({
                            "document_id": doc.get("document_id", "N/A")[:50],
                            "similarity": sim,
                            "chunk_index": chunk.get("chunk_index"),
                            "text_preview": chunk.get("chunk_text", "")[:100]
                        })
            
            # Ordina per similarità
            similarities.sort(key=lambda x: x["similarity"], reverse=True)
            
            print(f"\n   📊 Top 10 similarità trovate:")
            for i, sim_data in enumerate(similarities[:10], 1):
                print(f"   {i}. Similarità: {sim_data['similarity']:.4f}")
                print(f"      Document: {sim_data.get('document_id', 'N/A')}")
                if 'title' in sim_data:
                    print(f"      Title: {sim_data['title']}")
                if 'chunk_index' in sim_data:
                    print(f"      Chunk: {sim_data['chunk_index']}")
                    print(f"      Text: {sim_data.get('text_preview', 'N/A')}")
                print()
            
            if similarities:
                max_sim = similarities[0]["similarity"]
                print(f"   💡 Similarità massima trovata: {max_sim:.4f}")
                print(f"   💡 Threshold attuale: 0.15")
                if max_sim < 0.15:
                    print(f"   ⚠️ Similarità massima ({max_sim:.4f}) < threshold (0.15)")
                    print(f"   💡 Soluzione: Abbassare threshold o migliorare embeddings")
        else:
            print(f"   ✅ Retriever funziona! Chunks trovati:")
            for i, chunk in enumerate(chunks[:5], 1):
                print(f"   {i}. Similarità: {chunk.get('similarity', 0):.4f}")
                print(f"      Document: {chunk.get('document_id', 'N/A')[:50]}")
                print(f"      Text: {chunk.get('chunk_text', '')[:100]}...")
                print()
    
    print(f"\n{'='*80}\n")


async def main():
    parser = argparse.ArgumentParser(description='Diagnostica retrieval MongoDB')
    parser.add_argument('--tenant-id', type=int, required=True, help='Tenant ID')
    parser.add_argument('--query', type=str, help='Query di test (opzionale)')
    
    args = parser.parse_args()
    
    await diagnose_retrieval(args.tenant_id, args.query)


if __name__ == '__main__':
    import asyncio
    asyncio.run(main())

