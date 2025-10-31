# 📥 Installazione MongoDB Compass su Windows

## Procedura di Installazione

### 1️⃣ Scarica MongoDB Compass
Se non l'hai già fatto, scarica MongoDB Compass dal sito ufficiale:
- **URL:** https://www.mongodb.com/try/download/compass
- **Versione:** Windows 64-bit (.exe o .msi)

### 2️⃣ Installa MongoDB Compass

**Opzione A - File .exe (Installer)**
1. Vai nella cartella `Downloads` di Windows
2. Cerca il file `MongoDBCompass*.exe` (es: `MongoDBCompass-1.x.x.exe`)
3. **Doppio clic** sul file .exe
4. Segui la procedura guidata di installazione:
   - Clicca "Next" → "Next" → "Install"
   - Attendi il completamento
   - Clicca "Finish"

**Opzione B - File .msi (Windows Installer)**
1. Vai nella cartella `Downloads` di Windows
2. Cerca il file `MongoDBCompass*.msi`
3. **Doppio clic** sul file .msi
4. Segui la procedura guidata (stessa procedura del .exe)

### 3️⃣ Avvia MongoDB Compass
- Vai su **Start Menu** → Cerca "MongoDB Compass"
- Clicca sull'icona per avviarlo

## 🔌 Configurazione Connessione

### Dati di connessione per NATAN_LOC:

**URI di connessione (copia e incolla questo):**
```
mongodb://natan_user:secret_password@localhost:27017/natan_ai_core?authSource=admin
```

**Oppure compila manualmente:**
- **Host:** `localhost`
- **Port:** `27017`
- **Authentication:** `Username / Password`
- **Username:** `natan_user`
- **Password:** `secret_password`
- **Authentication Database:** `admin`
- **Default Database:** `natan_ai_core`

### Procedura:
1. Apri MongoDB Compass
2. Nel campo "New Connection", incolla l'URI sopra
3. Clicca **"Connect"**
4. ✅ Dovresti vedere il database `natan_ai_core` con le sue collections

## 📊 Collections da Vedere

Dopo la connessione, dovresti vedere:
- `conversational_responses` - Risposte conversazionali apprese
- `documents` - Documentazione importata da EGI
- `sources` - Fonti utilizzate dalla USE pipeline
- `claims` - Claims verificati
- `query_audit` - Log delle query

## ❓ Problemi Comuni

**"Connection refused" o "Cannot connect"**
- Assicurati che Docker sia in esecuzione
- Verifica che il container MongoDB sia attivo: `docker ps | grep mongodb`
- Se non lo è, esegui: `cd /home/fabio/NATAN_LOC && ./start_services.sh`

**"Authentication failed"**
- Verifica username: `natan_user`
- Verifica password: `secret_password`
- Verifica Authentication Database: `admin`

**Porta bloccata**
- Verifica che nessun altro servizio usi la porta 27017
- Controlla il firewall Windows


