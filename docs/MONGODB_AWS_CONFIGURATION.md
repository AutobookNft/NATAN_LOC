# 🚀 MongoDB su AWS - Guida Configurazione NATAN_LOC

**Versione**: 1.0.0  
**Data**: 2025-01-28  
**Contesto**: FlorenceEGI - NATAN_LOC Production Setup  
**Autore**: Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici

---

## 📋 Domande e Risposte per Configurazione MongoDB su AWS

### **Q1: Quale servizio MongoDB usare su AWS?**

**Risposta:** Due opzioni principali:

#### **Opzione A: Amazon DocumentDB (Consigliato per integrazione AWS nativa)**

✅ **Vantaggi:**
- Servizio AWS completamente gestito
- Compatibile con MongoDB 3.6, 4.0, 5.0
- Backup automatici, alta disponibilità
- Integrazione nativa con VPC, IAM, CloudWatch
- Encryption at rest e in transit
- Compliance GDPR-ready (regioni EU disponibili)

❌ **Limitazioni:**
- Non supporta tutte le feature MongoDB (es. alcune aggregation pipeline)
- Costi più elevati rispetto a self-hosted
- Lock-in AWS (non portabile)

#### **Opzione B: MongoDB Atlas su AWS Infrastructure**

✅ **Vantaggi:**
- MongoDB completo (tutte le feature)
- Gestito da MongoDB Inc. (esperti MongoDB)
- Deployabile su AWS infrastructure
- Supporto completo per vector search (importante per NATAN_LOC)
- Portabile (non lock-in AWS)
- Free tier disponibile per test

❌ **Limitazioni:**
- Gestione esterna (non completamente AWS-native)
- Costi variabili in base all'uso

**🎯 Raccomandazione per NATAN_LOC:**
- **Produzione**: **MongoDB Atlas su AWS** (supporto completo vector search)
- **Sviluppo/Test**: **DocumentDB** (se già in ecosistema AWS)

---

### **Q2: Quale regione AWS scegliere per compliance GDPR/MiCA?**

**Risposta:** Regioni EU per compliance GDPR:

**Regioni consigliate:**
- `eu-central-1` (Francoforte, Germania) - **CONSIGLIATA**
- `eu-west-1` (Dublino, Irlanda)
- `eu-west-2` (Londra, UK) - solo se necessario
- `eu-south-1` (Milano, Italia) - disponibile

**Configurazione per NATAN_LOC:**
```env
# .env - Python AI Service
MONGO_DB_HOST=cluster0.xxxxx.mongodb.net
MONGO_DB_PORT=27017
MONGO_DB_DATABASE=natan_ai_core
MONGO_DB_USERNAME=natan_user
MONGO_DB_PASSWORD=your_secure_password

# MongoDB Atlas Connection String (con SSL)
MONGODB_URI=mongodb+srv://natan_user:password@cluster0.xxxxx.mongodb.net/natan_ai_core?retryWrites=true&w=majority&tls=true
```

**⚠️ GDPR Compliance:**
- Dati personali devono risiedere in EU
- Encryption at rest obbligatoria
- Audit logging attivo (già implementato in NATAN_LOC)

---

### **Q3: Come configurare la connessione MongoDB da Python FastAPI?**

**Risposta:** Aggiornare `python_ai_service/app/config/__init__.py`:

**Configurazione attuale (locale):**
```python
# MongoDB configuration
MONGODB_HOST = os.getenv("MONGO_DB_HOST", "localhost")
MONGODB_PORT = int(os.getenv("MONGO_DB_PORT", "27017"))
MONGODB_DATABASE = os.getenv("MONGO_DB_DATABASE", "natan_ai_core")
MONGODB_USERNAME = os.getenv("MONGO_DB_USERNAME", "natan_user")
MONGODB_PASSWORD = os.getenv("MONGO_DB_PASSWORD", "")

# Build MongoDB URI
if MONGODB_PASSWORD:
    MONGODB_URI = f"mongodb://{MONGODB_USERNAME}:{MONGODB_PASSWORD}@{MONGODB_HOST}:{MONGODB_PORT}/{MONGODB_DATABASE}?authSource=admin"
else:
    MONGODB_URI = f"mongodb://{MONGODB_HOST}:{MONGODB_PORT}/{MONGODB_DATABASE}"
```

**Configurazione per AWS (MongoDB Atlas):**
```python
# MongoDB configuration (AWS-compatible)
MONGODB_HOST = os.getenv("MONGO_DB_HOST", "localhost")
MONGODB_PORT = int(os.getenv("MONGO_DB_PORT", "27017"))
MONGODB_DATABASE = os.getenv("MONGO_DB_DATABASE", "natan_ai_core")
MONGODB_USERNAME = os.getenv("MONGO_DB_USERNAME", "natan_user")
MONGODB_PASSWORD = os.getenv("MONGO_DB_PASSWORD", "")

# Prefer MONGODB_URI if set (for Atlas connection strings)
MONGODB_URI = os.getenv("MONGODB_URI", None)

if not MONGODB_URI:
    # Build MongoDB URI from components
    if MONGODB_PASSWORD:
        # For Atlas: use mongodb+srv with SSL
        if "mongodb.net" in MONGODB_HOST or "atlas" in MONGODB_HOST.lower():
            MONGODB_URI = f"mongodb+srv://{MONGODB_USERNAME}:{MONGODB_PASSWORD}@{MONGODB_HOST}/{MONGODB_DATABASE}?retryWrites=true&w=majority&tls=true"
        else:
            # For DocumentDB or self-hosted: use standard connection
            MONGODB_URI = f"mongodb://{MONGODB_USERNAME}:{MONGODB_PASSWORD}@{MONGODB_HOST}:{MONGODB_PORT}/{MONGODB_DATABASE}?authSource=admin&tls=true"
    else:
        MONGODB_URI = f"mongodb://{MONGODB_HOST}:{MONGODB_PORT}/{MONGODB_DATABASE}"
```

**File `.env` per produzione AWS:**
```env
# MongoDB Atlas (AWS Infrastructure)
MONGODB_URI=mongodb+srv://natan_user:secure_password@cluster0.xxxxx.mongodb.net/natan_ai_core?retryWrites=true&w=majority&tls=true

# Oppure componenti separati (per compatibilità)
MONGO_DB_HOST=cluster0.xxxxx.mongodb.net
MONGO_DB_PORT=27017
MONGO_DB_DATABASE=natan_ai_core
MONGO_DB_USERNAME=natan_user
MONGO_DB_PASSWORD=secure_password
```

---

### **Q4: Come gestire la sicurezza e autenticazione?**

**Risposta:** Best practices per NATAN_LOC:

#### **A. Network Security (VPC, Security Groups)**

**Per MongoDB Atlas:**
1. **IP Whitelist**: Aggiungere solo IP delle applicazioni NATAN_LOC
2. **VPC Peering**: Configurare VPC peering tra AWS VPC e MongoDB Atlas
3. **Private Endpoint**: Usare MongoDB Atlas Private Endpoint (consigliato)

**Per DocumentDB:**
1. **Security Group**: Consentire traffico solo da EC2/ECS/Lambda NATAN_LOC
2. **VPC**: Deployare in subnet private (non pubblica)
3. **Encryption**: Abilitare encryption at rest e in transit

#### **B. Authentication**

**MongoDB Atlas:**
```env
# Username/Password (Database User)
MONGO_DB_USERNAME=natan_user
MONGO_DB_PASSWORD=secure_password_here

# Oppure IAM Role (per applicazioni AWS-native)
# Configurare IAM Role in MongoDB Atlas
```

**DocumentDB:**
```env
# Username/Password (Master User)
MONGO_DB_USERNAME=admin
MONGO_DB_PASSWORD=secure_password_here

# authSource=admin (obbligatorio per DocumentDB)
```

#### **C. SSL/TLS**

**Sempre abilitato in produzione:**
- MongoDB Atlas: SSL abilitato di default
- DocumentDB: Richiede certificato CA (fornito da AWS)

**Configurazione PyMongo:**
```python
# app/services/mongodb_service.py
from pymongo import MongoClient
import certifi  # Per MongoDB Atlas SSL

# MongoDB Atlas (con SSL)
client = MongoClient(
    MONGODB_URI,
    tls=True,
    tlsCAFile=certifi.where(),  # Usa certificati CA standard
    serverSelectionTimeoutMS=5000
)

# DocumentDB (con SSL)
client = MongoClient(
    MONGODB_URI,
    tls=True,
    tlsCAFile='/path/to/rds-ca-2019-root.pem',  # Certificato AWS
    serverSelectionTimeoutMS=5000
)
```

---

### **Q5: Come configurare backup e disaster recovery?**

**Risposta:** Strategia per NATAN_LOC:

#### **MongoDB Atlas:**
- ✅ **Backup automatici**: Abilitati di default (snapshot continui)
- ✅ **Point-in-time recovery**: Disponibile
- ✅ **Export manuale**: Disponibile via UI o API

**Configurazione:**
1. Abilitare **Continuous Cloud Backups** in Atlas
2. Configurare **retention period** (es. 30 giorni)
3. Abilitare **Point-in-Time Recovery** per database critici

#### **DocumentDB:**
- ✅ **Automated backups**: Abilitati di default (7 giorni retention)
- ✅ **Manual snapshots**: Disponibili
- ✅ **Cross-region backup**: Configurabile

**Configurazione:**
```bash
# AWS CLI - Creare snapshot manuale
aws docdb create-db-cluster-snapshot \
    --db-cluster-snapshot-identifier natan-mongodb-snapshot-$(date +%Y%m%d) \
    --db-cluster-identifier natan-docdb-cluster
```

**🎯 Raccomandazione:**
- **Backup automatici**: Giornalieri
- **Retention**: 30 giorni (compliance GDPR)
- **Test restore**: Mensile
- **Cross-region backup**: Per disaster recovery

---

### **Q6: Come monitorare MongoDB su AWS?**

**Risposta:** Integrazione con sistema esistente NATAN_LOC:

#### **A. CloudWatch Metrics (AWS)**

**Per DocumentDB:**
- CPU, Memory, Disk I/O automatici
- Custom metrics via CloudWatch API

**Per MongoDB Atlas:**
- Metrics disponibili in Atlas UI
- Export a CloudWatch via API (se necessario)

#### **B. ULM Integration (NATAN_LOC esistente)**

**Logging MongoDB operations:**
```python
# app/services/mongodb_service.py
from app.config import ULTRA_LOG_MANAGER
import logging

logger = logging.getLogger(__name__)

class MongoDBService:
    @classmethod
    def get_client(cls) -> Optional[MongoClient]:
        try:
            cls._client = MongoClient(MONGODB_URI, serverSelectionTimeoutMS=5000)
            cls._client.admin.command('ping')
            
            # ULM: Log successful connection
            logger.info('MongoDB connection established', {
                'host': MONGODB_HOST,
                'database': MONGODB_DATABASE,
                'log_category': 'MONGODB_CONNECTION_SUCCESS'
            })
            
            cls._connected = True
        except Exception as e:
            # ULM: Log connection failure
            logger.error('MongoDB connection failed', {
                'host': MONGODB_HOST,
                'error': str(e),
                'log_category': 'MONGODB_CONNECTION_ERROR'
            })
            cls._connected = False
```

#### **C. Alerting**

**CloudWatch Alarms:**
- CPU > 80%
- Memory > 85%
- Connection count > threshold
- Replication lag > 5s

**UEM Integration (per errori critici):**
```python
# In caso di errori MongoDB critici
from Ultra.ErrorManager.Interfaces.ErrorManagerInterface import ErrorManagerInterface

try:
    # MongoDB operation
except Exception as e:
    # UEM: Alert team per errori critici
    errorManager->handle('MONGODB_OPERATION_FAILED', [
        'operation' => 'document_insert',
        'collection' => 'documents',
        'error_message' => str(e)
    ], e)
```

---

### **Q7: Come gestire multi-tenancy con MongoDB su AWS?**

**Risposta:** NATAN_LOC usa `tenant_id` per isolamento:

**Configurazione attuale:**
```python
# app/services/mongodb_service.py
def find_documents(collection: str, query: dict, limit: int = None):
    # Query include sempre tenant_id
    query_with_tenant = {**query, 'tenant_id': tenant_id}
    return collection.find(query_with_tenant).limit(limit)
```

**Per AWS (nessuna modifica necessaria):**
- ✅ Stesso pattern `tenant_id` funziona
- ✅ Index su `tenant_id` per performance
- ✅ Sharding per tenant (se necessario in futuro)

**Index consigliato:**
```javascript
// MongoDB Index per multi-tenancy
db.documents.createIndex({ "tenant_id": 1, "created_at": -1 })
db.documents.createIndex({ "tenant_id": 1, "scraper_id": 1 })
```

---

### **Q8: Come migrare dati da locale a AWS?**

**Risposta:** Processo di migrazione:

#### **Step 1: Backup dati locali**
```bash
# Export da MongoDB locale
mongodump --uri="mongodb://natan_user:password@localhost:27017/natan_ai_core" \
    --out=/backup/mongodb-export-$(date +%Y%m%d)
```

#### **Step 2: Import su AWS**
```bash
# Import su MongoDB Atlas
mongorestore --uri="mongodb+srv://natan_user:password@cluster0.xxxxx.mongodb.net/natan_ai_core" \
    /backup/mongodb-export-YYYYMMDD/natan_ai_core

# Oppure DocumentDB
mongorestore --uri="mongodb://admin:password@docdb-cluster.xxxxx.docdb.amazonaws.com:27017/natan_ai_core?tls=true&tlsCAFile=/path/to/rds-ca-2019-root.pem" \
    /backup/mongodb-export-YYYYMMDD/natan_ai_core
```

#### **Step 3: Verifica**
```python
# Test connessione e dati
from app.services.mongodb_service import MongoDBService

if MongoDBService.is_connected():
    count = MongoDBService.get_collection("documents").count_documents({})
    print(f"Documenti migrati: {count}")
```

#### **Step 4: Switchover**
1. Aggiornare `.env` con nuove credenziali AWS
2. Riavviare Python FastAPI service
3. Verificare operazioni
4. Monitorare per 24h

---

### **Q9: Costi stimati per NATAN_LOC?**

**Risposta:** Stima basata su uso tipico:

#### **MongoDB Atlas (M10 - Production):**
- **Instance**: ~$57/mese (2GB RAM, 10GB storage)
- **Storage**: $0.25/GB/mese (oltre 10GB)
- **Backup**: Incluso
- **Totale stimato**: ~$70-100/mese (per uso medio)

#### **DocumentDB (db.t3.medium):**
- **Instance**: ~$150/mese (2 vCPU, 4GB RAM)
- **Storage**: $0.10/GB/mese
- **Backup**: $0.095/GB/mese
- **Totale stimato**: ~$200-250/mese

**🎯 Raccomandazione:**
- **Sviluppo/Test**: MongoDB Atlas Free Tier (512MB)
- **Produzione**: MongoDB Atlas M10 (2GB) o M20 (4GB) se necessario
- **Alta disponibilità**: MongoDB Atlas M10+ con replica set

---

### **Q10: Checklist configurazione completa**

**Pre-deployment:**
- [ ] Account AWS/MongoDB Atlas creato
- [ ] Regione EU selezionata (GDPR compliance)
- [ ] Cluster MongoDB creato
- [ ] Database user creato con password sicura
- [ ] IP whitelist configurata (o VPC peering)
- [ ] SSL/TLS abilitato
- [ ] Backup automatici configurati

**Configurazione applicazione:**
- [ ] `.env` aggiornato con credenziali AWS
- [ ] `app/config/__init__.py` supporta connection string AWS
- [ ] `mongodb_service.py` testato con connessione AWS
- [ ] Index creati (tenant_id, created_at, ecc.)
- [ ] ULM logging verificato
- [ ] UEM error handling testato

**Post-deployment:**
- [ ] Connessione testata
- [ ] Query di test eseguite
- [ ] Performance monitorate
- [ ] Backup verificato
- [ ] Disaster recovery testato
- [ ] Documentazione aggiornata

---

## 📝 File di Configurazione da Aggiornare

### **1. `python_ai_service/.env`**
```env
# MongoDB AWS Configuration
MONGODB_URI=mongodb+srv://natan_user:password@cluster0.xxxxx.mongodb.net/natan_ai_core?retryWrites=true&w=majority&tls=true

# Componenti (backup, se MONGODB_URI non disponibile)
MONGO_DB_HOST=cluster0.xxxxx.mongodb.net
MONGO_DB_PORT=27017
MONGO_DB_DATABASE=natan_ai_core
MONGO_DB_USERNAME=natan_user
MONGO_DB_PASSWORD=secure_password_here
```

### **2. `python_ai_service/app/config/__init__.py`**
```python
# Supporto per MongoDB Atlas connection string
MONGODB_URI = os.getenv("MONGODB_URI", None)

if not MONGODB_URI:
    # Build from components (come già implementato)
    # ... codice esistente ...
```

### **3. `python_ai_service/app/services/mongodb_service.py`**
```python
# Aggiungere supporto SSL per AWS
import certifi

client = MongoClient(
    MONGODB_URI,
    tls=True,
    tlsCAFile=certifi.where() if "mongodb.net" in MONGODB_URI else None,
    serverSelectionTimeoutMS=5000
)
```

---

## 🚀 Quick Start: Deploy MongoDB Atlas su AWS

1. **Crea cluster MongoDB Atlas:**
   - Vai su [MongoDB Atlas](https://www.mongodb.com/cloud/atlas)
   - Crea cluster M10 (o Free per test)
   - Seleziona regione: **EU (Frankfurt)** o **EU (Ireland)**

2. **Configura sicurezza:**
   - Crea database user: `natan_user`
   - Aggiungi IP whitelist (o configura VPC peering)
   - Abilita SSL/TLS

3. **Ottieni connection string:**
   ```
   mongodb+srv://natan_user:<password>@cluster0.xxxxx.mongodb.net/?retryWrites=true&w=majority
   ```

4. **Aggiorna `.env`:**
   ```env
   MONGODB_URI=mongodb+srv://natan_user:password@cluster0.xxxxx.mongodb.net/natan_ai_core?retryWrites=true&w=majority&tls=true
   ```

5. **Test connessione:**
   ```bash
   cd python_ai_service
   python3 -c "from app.services.mongodb_service import MongoDBService; print('Connected:', MongoDBService.is_connected())"
   ```

---

## 📚 Risorse Aggiuntive

- **MongoDB Atlas Documentation**: https://docs.atlas.mongodb.com/
- **Amazon DocumentDB Guide**: https://docs.aws.amazon.com/documentdb/
- **PyMongo SSL Configuration**: https://pymongo.readthedocs.io/en/stable/examples/tls.html
- **GDPR Compliance MongoDB**: https://www.mongodb.com/compliance/gdpr

---

**Versione**: 1.0.0  
**Ultimo aggiornamento**: 2025-01-28  
**Status**: PRODUCTION READY


