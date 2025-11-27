#!/bin/bash

echo "🚀 RE-IMPORT COMPLETO DOCUMENTI FIRENZE CON PDF"
echo "=============================================="
echo ""
echo "Questo script re-importerà tutti i documenti 2024-2025"
echo "con estrazione PDF completa."
echo ""
echo "⏱️  Tempo stimato: ~2-3 ore"
echo "💰 Costo stimato embeddings: ~€0.50-1.00"
echo ""
read -p "Vuoi procedere? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]
then
    echo "❌ Annullato"
    exit 1
fi

cd /home/fabio/NATAN_LOC

echo ""
echo "📥 Importazione Delibere Giunta (DG) 2024-2025..."
python3 scripts/scrape_firenze_deliberazioni.py \
  --mongodb \
  --tenant-id 2 \
  --anno-inizio 2024 \
  --anno-fine 2025 \
  --tipi DG \
  --download-pdfs

echo ""
echo "📥 Importazione Delibere Consiglio (DD) 2024-2025..."
python3 scripts/scrape_firenze_deliberazioni.py \
  --mongodb \
  --tenant-id 2 \
  --anno-inizio 2024 \
  --anno-fine 2025 \
  --tipi DD \
  --download-pdfs

echo ""
echo "🎉 RE-IMPORT COMPLETATO!"
echo ""
echo "📊 Verifica documenti aggiornati:"
echo "   docker exec natan_python_fastapi python3 -c \"
from pymongo import MongoClient
import os
uri = os.getenv('MONGODB_URI')
client = MongoClient(uri)
db = client['natan_ai_core']

# Conta documenti con testo completo (>1000 chars)
docs = list(db['documents'].find({'tenant_id': 2}))
full_text_docs = 0
for doc in docs:
    content = doc.get('content', {})
    if isinstance(content, dict):
        full_text = content.get('full_text', '')
        if len(full_text) > 1000:
            full_text_docs += 1

print(f'Totale documenti: {len(docs)}')
print(f'Con testo completo (>1000 chars): {full_text_docs}')
print(f'Percentuale: {full_text_docs*100//len(docs)}%')
\""

