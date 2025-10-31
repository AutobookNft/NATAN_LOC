#!/bin/bash

echo "📥 GUIDA RAPIDA: Installazione MongoDB Compass"
echo "=============================================="
echo ""
echo "1️⃣  INSTALLAZIONE SU WINDOWS:"
echo "   - Vai su: https://www.mongodb.com/try/download/compass"
echo "   - Scarica la versione Windows 64-bit"
echo "   - Vai in Downloads → Trova MongoDBCompass*.exe o *.msi"
echo "   - DOPPIO CLIC sul file → Next → Install → Finish"
echo ""
echo "2️⃣  AVVIA MONGODB COMPASS:"
echo "   - Start Menu → Cerca 'MongoDB Compass' → Apri"
echo ""
echo "3️⃣  CONNESSIONE:"
echo "   - Nel campo 'New Connection', incolla questo URI:"
echo ""
echo "   mongodb://natan_user:secret_password@localhost:27017/natan_ai_core?authSource=admin"
echo ""
echo "   - Clicca 'Connect'"
echo ""
echo "4️⃣  VERIFICA DOCKER:"
docker ps | grep mongodb || echo "⚠️  MongoDB non è in esecuzione! Esegui: ./start_services.sh"
echo ""
echo "✅ Fatto! Ora puoi vedere le collections e i dati."



