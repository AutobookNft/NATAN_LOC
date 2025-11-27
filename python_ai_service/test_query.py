import asyncio
import sys
sys.path.insert(0, '/app')
from app.services.rag_fortress.retriever import HybridRetriever

async def test():
    retriever = HybridRetriever()
    
    # Test con la query dell'utente
    question = 'Crea una matrice decisionale per prioritizzare i progetti in base a impatto, urgenza, costo e fattibilità tecnica'
    
    print(f'🔍 Query: {question}')
    print()
    
    evidences = await retriever.retrieve_evidence(
        question=question,
        tenant_id='2',
        top_k=10
    )
    
    print(f'📊 Risultati: {len(evidences)} documenti')
    print()
    
    for i, ev in enumerate(evidences[:10]):
        title = ev.get('title', 'N/A')
        score = ev.get('score', 0)
        content_preview = str(ev.get('content', ''))[:100]
        
        print(f'[{i+1}] Score: {score:.4f}')
        print(f'    Title: {title[:80]}')
        print(f'    Preview: {content_preview}...')
        print()

asyncio.run(test())
