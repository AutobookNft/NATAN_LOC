#!/bin/bash
# Script per pulizia collection documents MongoDB
# Per NATAN_LOC - Test Import

echo "🧹 NATAN_LOC - Pulizia MongoDB Documents"
echo "========================================"
echo ""

# Verifica che l'utente sia sicuro
read -p "⚠️  Questo eliminerà TUTTI i documenti dalla collection 'documents'. Continuare? (yes/no): " confirm

if [ "$confirm" != "yes" ]; then
    echo "❌ Operazione annullata."
    exit 0
fi

echo ""
echo "📊 Documenti attuali prima della pulizia:"
docker exec -it natan_mongo mongosh --quiet --eval "
use natan_ai_core
db.documents.countDocuments({})
" 2>/dev/null | tail -1

echo ""
read -p "Confermare eliminazione? (yes/no): " confirm2

if [ "$confirm2" != "yes" ]; then
    echo "❌ Operazione annullata."
    exit 0
fi

echo ""
echo "🗑️  Eliminazione documenti in corso..."

docker exec -it natan_mongo mongosh --quiet --eval "
use natan_ai_core
db.documents.deleteMany({})
" 2>/dev/null

echo ""
echo "✅ Documenti rimanenti:"
docker exec -it natan_mongo mongosh --quiet --eval "
use natan_ai_core
db.documents.countDocuments({})
" 2>/dev/null | tail -1

echo ""
echo "✅ Pulizia completata!"
echo ""
echo "⚠️  IMPORTANTE: Se hai fatto drop della collection, devi ricreare il Vector Index su MongoDB Atlas!"
echo "   1. Vai su Atlas → Search"
echo "   2. Ricrea l'indice 'vector_index' sulla collection 'documents'"
echo ""

