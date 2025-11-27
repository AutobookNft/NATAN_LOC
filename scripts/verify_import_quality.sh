#!/bin/bash
# Script per verificare qualità import documenti
# NATAN_LOC - Quality Check

echo "🔍 NATAN_LOC - Verifica Qualità Import"
echo "======================================="
echo ""

TENANT_ID=${1:-2}

echo "📊 Verifica documenti Tenant ID: $TENANT_ID"
echo ""

echo "1️⃣  Conteggio documenti:"
docker exec -it natan_mongo mongosh --quiet --eval "
use natan_ai_core
print('Total documents:', db.documents.countDocuments({tenant_id: $TENANT_ID}))
" 2>/dev/null

echo ""
echo "2️⃣  Verifica PDF:"
docker exec -it natan_mongo mongosh --quiet --eval "
use natan_ai_core
var total = db.documents.countDocuments({tenant_id: $TENANT_ID})
var withPdf = db.documents.countDocuments({tenant_id: $TENANT_ID, 'metadata.pdf_url': {\$ne: null, \$ne: ''}})
var percentage = total > 0 ? ((withPdf / total) * 100).toFixed(1) : 0
print('Documents with PDF URL:', withPdf, '/', total, '(' + percentage + '%)')
" 2>/dev/null

echo ""
echo "3️⃣  Verifica testo completo:"
docker exec -it natan_mongo mongosh --quiet --eval "
use natan_ai_core
var result = db.documents.aggregate([
    {$match: {tenant_id: $TENANT_ID}},
    {$project: {textLength: {\$strLenCP: '\$content.full_text'}}},
    {$group: {
        _id: null,
        count: {\$sum: 1},
        avgLength: {\$avg: '\$textLength'},
        minLength: {\$min: '\$textLength'},
        maxLength: {\$max: '\$textLength'},
        tooShort: {\$sum: {\$cond: [{$lt: ['\$textLength', 500]}, 1, 0]}}
    }}
]).toArray()[0]
if (result) {
    print('Avg text length:', Math.round(result.avgLength), 'chars')
    print('Min text length:', result.minLength, 'chars')
    print('Max text length:', result.maxLength, 'chars')
    print('Documents < 500 chars:', result.tooShort, '(potrebbero avere problemi)')
}
" 2>/dev/null

echo ""
echo "4️⃣  Verifica struttura:"
docker exec -it natan_mongo mongosh --quiet --eval "
use natan_ai_core
var total = db.documents.countDocuments({tenant_id: $TENANT_ID})
var withStructure = db.documents.countDocuments({tenant_id: $TENANT_ID, 'content.structure': {\$exists: true, \$ne: null}})
var percentage = total > 0 ? ((withStructure / total) * 100).toFixed(1) : 0
print('Documents with structure:', withStructure, '/', total, '(' + percentage + '%)')
" 2>/dev/null

echo ""
echo "5️⃣  Verifica chunks:"
docker exec -it natan_mongo mongosh --quiet --eval "
use natan_ai_core
var result = db.documents.aggregate([
    {$match: {tenant_id: $TENANT_ID}},
    {$project: {chunkCount: {\$size: {\$ifNull: ['\$content.chunks', []]}}}},
    {$group: {
        _id: null,
        avgChunks: {\$avg: '\$chunkCount'},
        minChunks: {\$min: '\$chunkCount'},
        maxChunks: {\$max: '\$chunkCount'},
        noChunks: {\$sum: {\$cond: [{\$eq: ['\$chunkCount', 0]}, 1, 0]}}
    }}
]).toArray()[0]
if (result) {
    print('Avg chunks per doc:', Math.round(result.avgChunks))
    print('Min chunks:', result.minChunks)
    print('Max chunks:', result.maxChunks)
    print('Documents with 0 chunks:', result.noChunks, '(PROBLEMA se > 0)')
}
" 2>/dev/null

echo ""
echo "6️⃣  Verifica embeddings:"
docker exec -it natan_mongo mongosh --quiet --eval "
use natan_ai_core
var total = db.documents.countDocuments({tenant_id: $TENANT_ID})
var withEmbedding = db.documents.countDocuments({tenant_id: $TENANT_ID, embedding: {\$exists: true, \$ne: null}})
var percentage = total > 0 ? ((withEmbedding / total) * 100).toFixed(1) : 0
print('Documents with embedding:', withEmbedding, '/', total, '(' + percentage + '%)')
" 2>/dev/null

echo ""
echo "7️⃣  Sample documenti (primi 3):"
docker exec -it natan_mongo mongosh --quiet --eval "
use natan_ai_core
db.documents.find({tenant_id: $TENANT_ID}).limit(3).forEach(function(doc) {
    print('---')
    print('ID:', doc.document_id)
    print('Title:', doc.title.substring(0, 60) + '...')
    print('Text length:', doc.content.full_text ? doc.content.full_text.length : 0, 'chars')
    print('Chunks:', doc.content.chunks ? doc.content.chunks.length : 0)
    print('PDF:', doc.metadata && doc.metadata.pdf_url ? 'YES' : 'NO')
    print('Structure:', doc.content.structure ? 'YES' : 'NO')
})
" 2>/dev/null

echo ""
echo "✅ Verifica completata!"
echo ""
echo "📋 Checklist qualità:"
echo "   [ ] Tutti i documenti hanno PDF URL (o almeno >90%)"
echo "   [ ] Lunghezza media testo > 1000 caratteri"
echo "   [ ] Documenti con <500 caratteri < 10%"
echo "   [ ] Tutti hanno almeno 1 chunk"
echo "   [ ] Tutti hanno embedding"
echo "   [ ] Sample documenti hanno dati completi"
echo ""

