from pymongo import MongoClient
import os

uri = os.getenv('MONGODB_URI')
client = MongoClient(uri)
db = client['natan_ai_core']
collection = db['documents']

# Get sample embedding
sample = collection.find_one({'tenant_id': 2, 'embedding': {'$exists': True}})
if not sample:
    print('No documents')
    exit(1)

test_embedding = sample['embedding']
print(f'✅ Test con embedding da: {sample.get("title", "N/A")[:50]}')
print(f'   Dimensione: {len(test_embedding)}')

# Test $vectorSearch
print()
print('🔍 Test $vectorSearch su Atlas:')
pipeline = [
    {
        '$vectorSearch': {
            'index': 'vector_index',
            'path': 'embedding',
            'queryVector': test_embedding,
            'numCandidates': 20,
            'limit': 5,
            'filter': {'tenant_id': 2}
        }
    },
    {'$project': {'title': 1, 'tenant_id': 1, 'score': {'$meta': 'vectorSearchScore'}}}
]

try:
    results = list(collection.aggregate(pipeline))
    print(f'   Risultati: {len(results)}')
    if results:
        for i, doc in enumerate(results[:3]):
            print(f'   [{i+1}] {doc.get("title", "N/A")[:60]} (score: {doc.get("score", 0):.4f})')
    else:
        print('   ⚠️  ZERO risultati - Vector Search NON funziona!')
except Exception as e:
    print(f'   ❌ Errore: {e}')
