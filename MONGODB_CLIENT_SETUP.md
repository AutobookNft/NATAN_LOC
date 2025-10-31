# 🔧 Setup Client MongoDB per NATAN_LOC

## 🎯 Opzioni Disponibili

### 1️⃣ **MongoDB Compass (GUI Desktop) - CONSIGLIATO**

**Per Windows/WSL:**
1. Scarica MongoDB Compass: https://www.mongodb.com/try/download/compass
2. Installa su Windows
3. Connetti con:
   ```
   mongodb://natan_user:secret_password@localhost:27017/natan_ai_core?authSource=admin
   ```

**Vantaggi:**
- ✅ Interfaccia grafica intuitiva
- ✅ Visualizza collection e documenti
- ✅ Query builder visuale
- ✅ Analisi dati e aggregazioni
- ✅ Export/Import dati

---

### 2️⃣ **MongoDB Shell (mongosh) - CLI**

**Già disponibile tramite Docker:**

```bash
# Connetti al container MongoDB
docker exec -it natan_mongodb mongosh -u natan_user -p secret_password --authenticationDatabase admin

# Oppure direttamente
docker exec -it natan_mongodb mongosh "mongodb://natan_user:secret_password@localhost:27017/natan_ai_core?authSource=admin"
```

**Comandi utili:**
```javascript
// Mostra database
show dbs

// Usa database
use natan_ai_core

// Mostra collections
show collections

// Conta documenti
db.documents.countDocuments()
db.conversational_responses.countDocuments()

// Visualizza documenti
db.documents.find().limit(5).pretty()
db.conversational_responses.find().pretty()

// Query specifiche
db.documents.find({tenant_id: 1}).limit(10)
db.documents.find({document_type: "egi_documentation"}).count()

// Cerca per testo
db.documents.find({$text: {$search: "EGI"}})
```

---

### 3️⃣ **VS Code Extension (se usi VS Code)**

**Estensione:** "MongoDB for VS Code"

1. Apri VS Code
2. Installa estensione "MongoDB for VS Code"
3. Aggiungi connessione:
   - Connection String: `mongodb://natan_user:secret_password@localhost:27017`
   - Database: `natan_ai_core`
   - Authentication: `admin`

**Vantaggi:**
- ✅ Integrato nell'editor
- ✅ Visualizza documenti inline
- ✅ Query editor
- ✅ Supporto IntelliSense

---

### 4️⃣ **Client Web-based (NoSQLBooster for MongoDB)**

**Alternativa GUI:**
- Download: https://www.nosqlbooster.com/downloads
- Connection string: `mongodb://natan_user:secret_password@localhost:27017/natan_ai_core?authSource=admin`

---

## 📊 Quick Access Script

Ho creato uno script per accesso rapido:

```bash
cd /home/fabio/NATAN_LOC
./mongodb_connect.sh
```

---

## 🔍 Verifica Connessione

```bash
# Test connessione
docker exec natan_mongodb mongosh -u natan_user -p secret_password --authenticationDatabase admin --eval "db.adminCommand('ping')"
```

## 📝 Collections Principali

Nel database `natan_ai_core` troverai:

- **`documents`** - Documenti importati (EGI docs, PA acts, etc.)
- **`conversational_responses`** - Risposte conversazionali apprese con embeddings
- **`sources`** - USE pipeline atomic sources
- **`claims`** - USE pipeline verified claims
- **`query_audit`** - Audit trail USE pipeline
- **`natan_chat_messages`** - Storia chat
- **`ai_logs`** - Log operazioni AI
- **`analytics`** - Metriche aggregate



