import asyncio
import sys
sys.path.insert(0, '/app')
from app.services.rag_fortress.retriever import HybridRetriever

async def test():
    retriever = HybridRetriever()
    
    # Query specifica sui documenti che abbiamo importato
    question = 'Quali progetti per biciclette o mobilità sostenibile?'
    
    print(f'🔍 Query: {question}')
    print()
    
    evidences = await retriever.retrieve_evidence(
        question=question,
        tenant_id='2',
        top_k=5
    )
    
    print(f'📊 Risultati: {len(evidences)} documenti')
    print()
    
    for i, ev in enumerate(evidences[:5]):
        title = ev.get('title', 'N/A')[:70]
        score = ev.get('score', 0)
        
        # Get content preview
        content = ev.get('content', '')
        if isinstance(content, dict):
            text = content.get('text', '') or content.get('full_text', '')
        else:
            text = str(content)
        
        print(f'[{i+1}] Score: {score:.4f}')
        print(f'    Title: {title}')
        print(f'    Content length: {len(text)} chars')
        print(f'    Preview: {text[:150]}...')
        print()

asyncio.run(test())
