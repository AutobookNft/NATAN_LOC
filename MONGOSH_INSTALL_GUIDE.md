# 📦 Guida Installazione MongoDB Shell (mongosh)

## ⚠️ Attenzione: Versione Windows Rilevata

Hai scaricato la versione **Windows** (`mongosh-2.5.9-win32-x64.zip`), ma sei su **WSL/Linux**.

## 🎯 Cosa Devi Fare

### Opzione 1: Usa la Versione Già in Docker (PIÙ SEMPLICE) ✅

**Non serve installare nulla!** mongosh è già disponibile nel container:

```bash
docker exec -it natan_mongodb mongosh -u natan_user -p secret_password --authenticationDatabase admin natan_ai_core
```

**Vantaggi:**
- ✅ Già configurato
- ✅ Funziona subito
- ✅ Nessuna installazione necessaria

---

### Opzione 2: Installa MongoDB Compass (GUI) su Windows

Se vuoi una GUI, installa MongoDB Compass su Windows:

1. Scarica: https://www.mongodb.com/try/download/compass
2. Installa su Windows
3. Connection string:
   ```
   mongodb://natan_user:secret_password@localhost:27017/natan_ai_core?authSource=admin
   ```

---

### Opzione 3: Scarica Versione Linux (se vuoi mongosh locale)

**Se vuoi installare mongosh localmente in WSL:**

```bash
# Opzione A: Script automatico
cd /tmp
wget https://downloads.mongodb.com/compass/mongosh-2.5.9-linux-x64.tgz
tar -xzf mongosh-2.5.9-linux-x64.tgz
mv mongosh-2.5.9-linux-x64 ~/mongosh

# Aggiungi a PATH
echo 'export PATH="$HOME/mongosh/bin:$PATH"' >> ~/.bashrc
source ~/.bashrc

# Test
mongosh --version
```

**O usa lo script automatico:**
```bash
/tmp/install_mongosh_linux.sh
```

---

## 🚀 Raccomandazione

**Per ora, usa semplicemente:**

```bash
docker exec -it natan_mongodb mongosh -u natan_user -p secret_password --authenticationDatabase admin natan_ai_core
```

Funziona perfettamente e non richiede installazione!

---

## 📊 Comandi Utili una Volta Connesso

```javascript
// Mostra database
show dbs

// Usa database NATAN
use natan_ai_core

// Mostra collections
show collections

// Conta documenti
db.documents.countDocuments()
db.conversational_responses.countDocuments()

// Visualizza documenti (primi 5)
db.documents.find().limit(5).pretty()

// Query specifiche
db.documents.find({document_type: "egi_documentation"}).limit(10).pretty()

// Cerca documento specifico
db.documents.findOne({filename: "perplexity-setup.md"})

// Esporta query results
db.documents.find({tenant_id: 1}).limit(10).forEach(doc => printjson(doc))
```



