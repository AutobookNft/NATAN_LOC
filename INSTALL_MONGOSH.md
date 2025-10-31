# 📦 Installazione MongoDB Shell (mongosh) Locale

## 🎯 Cosa Fare

Il README dice di **estrarre il pacchetto in una posizione adatta**. Ecco come procedere:

### Step 1: Identifica il File

Il file che hai scaricato probabilmente si chiama:
- `mongosh-<versione>-linux-x64.tgz` (per Linux)
- `mongosh-<versione>-windows-x64.zip` (per Windows)
- Oppure simile

### Step 2: Estrai il File

**Per Linux/WSL:**
```bash
# Trova il file (solitamente in Downloads)
cd ~/Downloads
# O dove hai scaricato il file

# Estrai
tar -xzf mongosh-*-linux-x64.tgz
# Questo creerà una directory come: mongosh-2.5.8-linux-x64/

# Sposta in una posizione permanente (opzionale ma consigliato)
sudo mv mongosh-*-linux-x64 /opt/mongosh
# Oppure nella tua home
mv mongosh-*-linux-x64 ~/mongosh
```

**Per Windows:**
```bash
# Se sei su WSL e hai il file in Windows Downloads
cd /mnt/c/Users/$USER/Downloads
unzip mongosh-*-windows-x64.zip -d ~/mongosh
```

### Step 3: Aggiungi al PATH

**Opzione A: Symlink in /usr/local/bin (consigliato)**
```bash
# Se installato in /opt/mongosh
sudo ln -s /opt/mongosh/bin/mongosh /usr/local/bin/mongosh

# Se installato in ~/mongosh
sudo ln -s ~/mongosh/bin/mongosh /usr/local/bin/mongosh
```

**Opzione B: Aggiungi alla PATH in .bashrc**
```bash
echo 'export PATH="$HOME/mongosh/bin:$PATH"' >> ~/.bashrc
source ~/.bashrc
```

### Step 4: Verifica Installazione

```bash
mongosh --version
```

### Step 5: Connetti a MongoDB NATAN_LOC

```bash
mongosh "mongodb://natan_user:secret_password@localhost:27017/natan_ai_core?authSource=admin"
```

O con parametri separati:
```bash
mongosh \
  --host localhost:27017 \
  --username natan_user \
  --password secret_password \
  --authenticationDatabase admin \
  natan_ai_core
```

---

## 🚀 Installazione Automatica (Script)

Ho creato uno script che fa tutto automaticamente:

```bash
cd /home/fabio/NATAN_LOC
./install_mongosh.sh
```

---

## 📝 Alternativa: Usa quello già in Docker

**Se non vuoi installare localmente**, puoi sempre usare mongosh dal container:

```bash
docker exec -it natan_mongodb mongosh -u natan_user -p secret_password --authenticationDatabase admin natan_ai_core
```

---

## ⚡ Quick Start

Dopo l'installazione, connettiti subito:

```bash
mongosh "mongodb://natan_user:secret_password@localhost:27017/natan_ai_core?authSource=admin"
```

E poi prova questi comandi:
```javascript
show dbs
use natan_ai_core
show collections
db.documents.countDocuments()
db.documents.find().limit(3).pretty()
```



