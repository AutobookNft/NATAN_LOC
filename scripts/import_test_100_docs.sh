#!/bin/bash
# Script per import TEST di 100 documenti
# NATAN_LOC - Verifica import completo (PDF + testo + struttura + embeddings)

echo "📦 NATAN_LOC - Import TEST 100 Documenti"
echo "=========================================="
echo ""

# Configurazione
TENANT_ID=2
ANNO_TEST=2024
TIPI_ATTO="DG DD"  # Deliberazioni Giunta + Determinazioni Dirigenziali
MAX_DOCS=100

echo "📋 Configurazione Import Test:"
echo "   - Tenant ID: $TENANT_ID"
echo "   - Anno: $ANNO_TEST"
echo "   - Tipi Atto: $TIPI_ATTO"
echo "   - Max documenti target: ~$MAX_DOCS"
echo "   - PDF: ✅ Download e estrazione testo"
echo "   - Embeddings: ✅ Generazione automatica"
echo "   - Chunking: ✅ Intelligente"
echo "   - Struttura: ✅ Identificazione sezioni"
echo ""

read -p "Avviare import test? (yes/no): " confirm

if [ "$confirm" != "yes" ]; then
    echo "❌ Import annullato."
    exit 0
fi

echo ""
echo "🚀 Avvio import..."
echo ""

cd /home/fabio/NATAN_LOC

# Import con limite documenti per test
# Note: Lo scraper non ha un parametro --max-docs diretto,
# ma limitiamo a 1 anno + 2 tipi atto per avere ~100 docs
python3 scripts/scrape_firenze_deliberazioni.py \
  --mongodb \
  --tenant-id $TENANT_ID \
  --anno-inizio $ANNO_TEST \
  --anno-fine $ANNO_TEST \
  --tipi $TIPI_ATTO \
  --download-pdfs \
  --output-dir storage/testing/import_test_100

EXIT_CODE=$?

echo ""
if [ $EXIT_CODE -eq 0 ]; then
    echo "✅ Import completato!"
    echo ""
    echo "📊 Verifica documenti importati:"
    docker exec -it natan_mongo mongosh --quiet --eval "
    use natan_ai_core
    print('Total documents:', db.documents.countDocuments({}))
    print('Tenant $TENANT_ID documents:', db.documents.countDocuments({tenant_id: $TENANT_ID}))
    print('Documents with PDF:', db.documents.countDocuments({'metadata.pdf_url': {\$ne: null, \$ne: ''}}))
    print('Documents with structure:', db.documents.countDocuments({'content.structure': {\$exists: true, \$ne: null}}))
    print('Documents with chunks:', db.documents.countDocuments({'content.chunks.0': {\$exists: true}}))
    " 2>/dev/null
    
    echo ""
    echo "🔍 Sample document check:"
    docker exec -it natan_mongo mongosh --quiet --eval "
    use natan_ai_core
    var doc = db.documents.findOne({tenant_id: $TENANT_ID})
    if (doc) {
        print('Document ID:', doc.document_id)
        print('Title:', doc.title)
        print('Full text length:', doc.content.full_text ? doc.content.full_text.length : 0, 'chars')
        print('Chunks:', doc.content.chunks ? doc.content.chunks.length : 0)
        print('Has PDF URL:', doc.metadata && doc.metadata.pdf_url ? 'YES' : 'NO')
        print('Has structure:', doc.content.structure ? 'YES' : 'NO')
    } else {
        print('No documents found!')
    }
    " 2>/dev/null
    
    echo ""
    echo "✅ Import test completato con successo!"
    echo ""
    echo "🔧 Prossimi step:"
    echo "   1. Verifica che Vector Index su Atlas sia ACTIVE"
    echo "   2. Testa la chat con una query"
    echo "   3. Verifica vista documento (deve mostrare PDF + testo completo)"
    echo "   4. Se tutto OK → procedi con import completo"
    echo ""
else
    echo "❌ Errore durante import (exit code: $EXIT_CODE)"
    echo "   Controlla i log per dettagli."
    exit 1
fi

