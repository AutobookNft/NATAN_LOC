#!/bin/bash
# Script per IMPORT COMPLETO PRODUZIONE
# NATAN_LOC - Import tutti gli atti 2018-2025

echo "🚀 NATAN_LOC - Import COMPLETO Produzione"
echo "==========================================="
echo ""

# Configurazione
TENANT_ID=2
ANNO_INIZIO=2018
ANNO_FINE=2025
TIPI_ATTO="DG DC DD DS OD"  # Tutti i tipi

echo "📋 Configurazione Import Produzione:"
echo "   - Tenant ID: $TENANT_ID"
echo "   - Periodo: $ANNO_INIZIO-$ANNO_FINE (8 anni)"
echo "   - Tipi Atto: $TIPI_ATTO"
echo "   - Documenti stimati: ~5000-10000"
echo "   - PDF: ✅ Download e estrazione testo"
echo "   - Embeddings: ✅ Generazione automatica"
echo "   - Tempo stimato: 2-6 ore"
echo ""

echo "⚠️  ATTENZIONE:"
echo "   - Questo import richiederà diverse ore"
echo "   - Consumo API OpenAI stimato: $20-50"
echo "   - Assicurati che il DB sia pulito"
echo "   - Assicurati che Vector Index sia creato su Atlas"
echo ""

read -p "Sei SICURO di voler procedere con import COMPLETO? (yes/no): " confirm

if [ "$confirm" != "yes" ]; then
    echo "❌ Import annullato."
    exit 0
fi

echo ""
echo "📊 Verifica stato DB attuale:"
docker exec -it natan_mongo mongosh --quiet --eval "
use natan_ai_core
db.documents.countDocuments({})
" 2>/dev/null | tail -1

echo ""
read -p "Confermare avvio import? (yes/no): " confirm2

if [ "$confirm2" != "yes" ]; then
    echo "❌ Import annullato."
    exit 0
fi

echo ""
echo "🚀 Avvio import COMPLETO..."
echo "   Inizio: $(date)"
echo ""

cd /home/fabio/NATAN_LOC

# Import completo con tutti gli anni e tipi
python3 scripts/scrape_firenze_deliberazioni.py \
  --mongodb \
  --tenant-id $TENANT_ID \
  --anno-inizio $ANNO_INIZIO \
  --anno-fine $ANNO_FINE \
  --tipi $TIPI_ATTO \
  --download-pdfs \
  --output-dir storage/production/firenze_atti_completi

EXIT_CODE=$?

echo ""
echo "   Fine: $(date)"
echo ""

if [ $EXIT_CODE -eq 0 ]; then
    echo "✅ Import COMPLETO completato!"
    echo ""
    echo "📊 Statistiche finali:"
    docker exec -it natan_mongo mongosh --quiet --eval "
    use natan_ai_core
    print('Total documents:', db.documents.countDocuments({}))
    print('Tenant $TENANT_ID documents:', db.documents.countDocuments({tenant_id: $TENANT_ID}))
    print('Documents with PDF:', db.documents.countDocuments({'metadata.pdf_url': {\$ne: null, \$ne: ''}}))
    print('Documents with structure:', db.documents.countDocuments({'content.structure': {\$exists: true, \$ne: null}}))
    print('Avg chars per document:', db.documents.aggregate([
        {$match: {tenant_id: $TENANT_ID}},
        {$project: {chars: {\$strLenCP: '\$content.full_text'}}},
        {$group: {_id: null, avg: {$avg: '\$chars'}}}
    ]).toArray()[0].avg)
    " 2>/dev/null
    
    echo ""
    echo "✅ IMPORT PRODUZIONE COMPLETATO!"
    echo ""
    echo "🔧 Verifica finale:"
    echo "   1. Vector Index su Atlas deve essere ACTIVE"
    echo "   2. Testa query nella chat"
    echo "   3. Verifica performance (deve essere ~20-30s)"
    echo "   4. Apri vista documento per verificare PDF + testo"
    echo ""
else
    echo "❌ Errore durante import completo (exit code: $EXIT_CODE)"
    echo "   Controlla i log per dettagli."
    exit 1
fi

