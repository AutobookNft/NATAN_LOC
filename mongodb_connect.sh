#!/bin/bash

# Quick MongoDB Connection Script
# Connetti facilmente a MongoDB

echo "🔌 MongoDB Connection Helper"
echo ""
echo "Scegli un'opzione:"
echo "  1) MongoDB Shell (mongosh) - CLI interattivo"
echo "  2) MongoDB Compass (GUI) - Connection string"
echo "  3) VS Code Extension - Connection string"
echo "  4) Quick queries (script)"
echo ""
read -p "Scelta [1-4]: " choice

case $choice in
    1)
        echo ""
        echo "🔌 Connessione MongoDB Shell..."
        docker exec -it natan_mongodb mongosh -u natan_user -p secret_password --authenticationDatabase admin natan_ai_core
        ;;
    2)
        echo ""
        echo "📋 MongoDB Compass Connection String:"
        echo ""
        echo "mongodb://natan_user:secret_password@localhost:27017/natan_ai_core?authSource=admin"
        echo ""
        echo "Copia questa stringa in MongoDB Compass > New Connection"
        ;;
    3)
        echo ""
        echo "📋 VS Code MongoDB Extension Connection:"
        echo ""
        echo "Connection String: mongodb://natan_user:secret_password@localhost:27017"
        echo "Database: natan_ai_core"
        echo "Auth Database: admin"
        ;;
    4)
        echo ""
        echo "🔍 Quick Queries:"
        docker exec natan_mongodb mongosh -u natan_user -p secret_password --authenticationDatabase admin natan_ai_core --quiet << 'MONGOEOF'
print("\n📊 DATABASE INFO:")
print("="*60)
print("Database: natan_ai_core")
print("\n📚 Collections:")
collections = db.getCollectionNames()
for coll in collections:
    count = db[coll].countDocuments()
    print(f"  - {coll}: {count} documenti")

print("\n📄 DOCUMENTS:")
print(f"  Totali: {db.documents.countDocuments()}")
print(f"  EGI docs: {db.documents.countDocuments({document_type: 'egi_documentation'})}")
print(f"  Con embeddings: {db.documents.countDocuments({embedding: {$exists: true}})}")

print("\n💬 CONVERSATIONAL RESPONSES:")
print(f"  Totali: {db.conversational_responses.countDocuments()}")
print(f"  Con embeddings: {db.conversational_responses.countDocuments({embedding: {$exists: true}})}}")

print("\n✅ Status: MongoDB operativo!")
MONGOEOF
        ;;
    *)
        echo "Scelta non valida"
        ;;
esac
















