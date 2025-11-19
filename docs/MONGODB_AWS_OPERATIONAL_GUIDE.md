# 🚀 MongoDB Atlas su AWS - Guida Operativa Completa

**Versione**: 1.0.0  
**Data**: 2025-01-28  
**Contesto**: FlorenceEGI - NATAN_LOC Production Setup  
**Autore**: Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici

---

## 📋 Informazioni AWS Verificate

### **Infrastructure AWS:**
- **EC2 Instance ID**: `i-0e50d9a88c7682f20` (florenceegi-staging)
- **Instance Type**: `t3.small`
- **Private IP**: `10.0.1.121`
- **Public IP**: `13.48.57.194`
- **VPC ID**: `vpc-019e351bf6db868ab`
- **Security Group ID**: `sg-0c960d72011237d05` (default)
- **Subnet ID**: `subnet-0ee5da08fd323ba60` (Public Subnet 2)
- **Region**: `eu-north-1` (Stockholm, Svezia) - GDPR compliant

### **Architettura:**
```
Laravel (Forge EC2: i-0e50d9a88c7682f20, IP: 10.0.1.121) 
    ↓ HTTP API (localhost o internal network)
Python FastAPI (su stesso EC2)
    ↓ MongoDB Connection (mongodb+srv://)
MongoDB Atlas (eu-north-1, cluster0.xxxxx.mongodb.net)
```

---

## 🎯 Step 1: Creare MongoDB Atlas Cluster

### **1.1 Registrazione/Creazione Account**

1. Vai su [MongoDB Atlas](https://www.mongodb.com/cloud/atlas)
2. Accedi o crea account
3. Crea nuova organizzazione (se prima volta): `FlorenceEGI` o `NATAN_LOC`

### **1.2 Creare Cluster**

1. **Dashboard Atlas** → **Build a Database**
2. **Deployment Type**: Seleziona **M10** (o M0 Free per test)
3. **Cloud Provider**: **AWS**
4. **Region**: **eu-north-1 (Stockholm)** ✅ **Stessa regione EC2 per bassa latenza**
5. **Cluster Name**: `natan-loc-production` (o `natan-loc-staging`)
6. **Cluster Tier**: 
   - **M0 Free** (512MB) - Solo per test
   - **M10** (2GB RAM, 10GB storage) - **RACCOMANDATO per produzione** (~$57/mese)
7. **Additional Settings**:
   - ✅ **Enable Backup** (incluso in M10+)
   - ✅ **Enable Monitoring** (incluso)
8. Clicca **Create Deployment**

**⏱️ Tempo stimato**: 5-10 minuti per creazione cluster

---

## 🔐 Step 2: Configurare Sicurezza

### **2.1 Creare Database User**

1. **Atlas Dashboard** → **Database Access** → **Add New Database User**
2. **Authentication Method**: `Password`
3. **Username**: `natan_user`
4. **Password**: Genera password sicura (salvala!)
5. **Database User Privileges**: 
   - **Built-in Role**: `Read and write to any database`
   - Oppure custom: `readWrite` su database `natan_ai_core`
6. Clicca **Add User**

**⚠️ IMPORTANTE**: Salva password in password manager sicuro!

### **2.2 Configurare Network Access (IP Whitelist)**

**Opzione A: IP Whitelist (Più Semplice)**

1. **Atlas Dashboard** → **Network Access** → **Add IP Address**
2. **Access List Entry**: 
   - **IP Address**: `13.48.57.194` (Public IP EC2 Laravel)
   - **Comment**: `Laravel Forge EC2 - florenceegi-staging`
3. Clicca **Add IP Address**

**Opzione B: VPC Peering (Più Sicuro - Consigliato per Produzione)**

1. **Atlas Dashboard** → **Network Access** → **Peering**
2. **Add Peering Connection**
3. **Cloud Provider**: AWS
4. **Region**: `eu-north-1`
5. **VPC ID**: `vpc-019e351bf6db868ab`
6. Segui wizard per completare peering
7. Configura route tables su AWS

**🎯 Raccomandazione**: 
- **Sviluppo/Test**: IP Whitelist (Opzione A)
- **Produzione**: VPC Peering (Opzione B)

### **2.3 Abilitare TLS/SSL**

✅ **TLS/SSL è abilitato di default** in MongoDB Atlas

Verifica:
- **Atlas Dashboard** → **Clusters** → Il tuo cluster
- **Connection** → Verifica che connection string usi `mongodb+srv://` (SSL incluso)

---

## 🔌 Step 3: Ottenere Connection String

### **3.1 Ottenere Connection String**

1. **Atlas Dashboard** → **Clusters** → Clicca **Connect** sul tuo cluster
2. **Connect your application**
3. **Driver**: `Python`
4. **Version**: `3.6 or later`
5. Copia connection string:

```
mongodb+srv://natan_user:<password>@cluster0.xxxxx.mongodb.net/?retryWrites=true&w=majority
```

### **3.2 Personalizzare Connection String**

Sostituisci:
- `<password>` con la password del database user
- Aggiungi database name: `natan_ai_core`

**Connection String Finale:**
```
mongodb+srv://natan_user:YOUR_PASSWORD@cluster0.xxxxx.mongodb.net/natan_ai_core?retryWrites=true&w=majority&tls=true
```

---

## ⚙️ Step 4: Configurare Python FastAPI

### **4.1 Aggiornare `python_ai_service/app/config/__init__.py`**

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

### **4.2 Aggiornare `python_ai_service/app/services/mongodb_service.py`**

Aggiungere supporto SSL per MongoDB Atlas:

```python
from pymongo import MongoClient
import certifi  # Per MongoDB Atlas SSL

# ... codice esistente ...

@classmethod
def get_client(cls) -> Optional[MongoClient]:
    """Get or create MongoDB client (singleton)"""
    if cls._client is None:
        try:
            # Support SSL per MongoDB Atlas
            if "mongodb.net" in MONGODB_URI or "mongodb+srv" in MONGODB_URI:
                cls._client = MongoClient(
                    MONGODB_URI,
                    tls=True,
                    tlsCAFile=certifi.where(),  # Usa certificati CA standard
                    serverSelectionTimeoutMS=5000
                )
            else:
                # Connection standard (locale o DocumentDB)
                cls._client = MongoClient(MONGODB_URI, serverSelectionTimeoutMS=5000)
            
            # Test connection
            cls._client.admin.command('ping')
            logger.info("MongoDB connection established")
            cls._connected = True
        except Exception as e:
            # ... gestione errori esistente ...
```

**⚠️ IMPORTANTE**: Installa `certifi` se non presente:
```bash
cd python_ai_service
pip install certifi
```

### **4.3 Aggiornare `.env` File**

**File**: `python_ai_service/.env`

```env
# MongoDB Atlas (AWS Production)
MONGODB_URI=mongodb+srv://natan_user:YOUR_PASSWORD@cluster0.xxxxx.mongodb.net/natan_ai_core?retryWrites=true&w=majority&tls=true

# Componenti (backup, se MONGODB_URI non disponibile)
MONGO_DB_HOST=cluster0.xxxxx.mongodb.net
MONGO_DB_PORT=27017
MONGO_DB_DATABASE=natan_ai_core
MONGO_DB_USERNAME=natan_user
MONGO_DB_PASSWORD=YOUR_PASSWORD_HERE
```

**⚠️ SICUREZZA**: 
- Non committare `.env` con password
- Usa variabili d'ambiente su Forge
- Considera AWS Secrets Manager per produzione

---

## 🔒 Step 5: Configurare Security Groups AWS

### **5.1 Se Usi IP Whitelist (Opzione A)**

**Nessuna modifica necessaria** - MongoDB Atlas gestisce whitelist internamente.

### **5.2 Se Usi VPC Peering (Opzione B)**

**Configurare Security Group per MongoDB Atlas:**

1. **AWS Console** → **EC2** → **Security Groups**
2. Trova Security Group: `sg-0c960d72011237d05` (default - Laravel EC2)
3. **Inbound Rules** → **Edit inbound rules**
4. **Add rule**:
   - **Type**: `Custom TCP`
   - **Port**: `27017-27019` (MongoDB ports)
   - **Source**: Security Group ID di MongoDB Atlas (fornito da Atlas dopo peering)
   - **Description**: `MongoDB Atlas VPC Peering`
5. Clicca **Save rules**

**💡 Nota**: Le **Outbound Rules** già permettono tutto il traffico in uscita (`sgr-05c5ab2ed8640ef95` - All traffic to 0.0.0.0/0), quindi le connessioni MongoDB Atlas in uscita sono già permesse.

**💡 Nota Importante**: 
- Le **Outbound Rules** del Security Group `sg-0c960d72011237d05` già permettono tutto il traffico in uscita
- Per MongoDB Atlas con IP whitelist: **Nessuna modifica necessaria** (traffico in uscita già permesso)
- Per MongoDB Atlas con VPC Peering: Aggiungere solo inbound rule come sopra

**Oppure** crea nuovo Security Group dedicato per MongoDB (opzionale):
- **Name**: `mongodb-atlas-access`
- **Description**: `Security Group for MongoDB Atlas access`
- **VPC**: `vpc-019e351bf6db868ab`
- **Inbound Rules**: Come sopra

---

## 🧪 Step 6: Test Connessione

### **6.1 Test Locale (da Lenovo i7)**

```bash
cd python_ai_service
source venv/bin/activate

# Test connessione
python3 -c "from app.services.mongodb_service import MongoDBService; print('Connected:', MongoDBService.is_connected())"
```

**Output atteso:**
```
MongoDB connection established
Connected: True
```

### **6.2 Test da EC2 Laravel (Forge)**

**SSH su EC2:**
```bash
ssh forge@13.48.57.194
# Oppure via Forge panel → SSH

cd /home/forge/default/python_ai_service
source venv/bin/activate

# Test connessione
python3 -c "from app.services.mongodb_service import MongoDBService; print('Connected:', MongoDBService.is_connected())"
```

### **6.3 Test via Python FastAPI**

```bash
# Avvia Python FastAPI
cd python_ai_service
uvicorn app.main:app --reload

# In altro terminale, test endpoint
curl http://localhost:8000/api/health
# Oppure endpoint MongoDB se disponibile
```

---

## 📊 Step 7: Configurare Backup

### **7.1 MongoDB Atlas Backup (M10+)**

✅ **Backup automatici sono inclusi** in M10+

**Configurare Backup Settings:**

1. **Atlas Dashboard** → **Clusters** → Il tuo cluster
2. **Backup** tab → **Edit Configuration**
3. **Backup Schedule**:
   - **Frequency**: `6 hours` (o `Daily`)
   - **Retention**: `30 days` ✅ **Raccomandato per GDPR**
4. **Point-in-Time Recovery**: ✅ **Enable** (se disponibile)
5. Clicca **Save**

### **7.2 Test Restore**

**Procedure di test restore mensile:**

1. **Atlas Dashboard** → **Backup** → **Restore**
2. Seleziona snapshot da testare
3. **Restore to**: Nuovo cluster temporaneo
4. Verifica dati restaurati
5. Elimina cluster temporaneo dopo verifica

---

## 📈 Step 8: Configurare Monitoring

### **8.1 MongoDB Atlas Monitoring**

✅ **Monitoring è incluso** in MongoDB Atlas

**Metriche disponibili:**
- CPU usage
- Memory usage
- Disk I/O
- Connection count
- Query performance
- Replication lag

**Accesso:**
- **Atlas Dashboard** → **Clusters** → Il tuo cluster → **Metrics** tab

### **8.2 AWS CloudWatch Integration**

**Opzione A: CloudWatch Metrics via API**

```python
# Esempio: Inviare metriche MongoDB a CloudWatch
import boto3

cloudwatch = boto3.client('cloudwatch', region_name='eu-north-1')

# Dopo operazione MongoDB
cloudwatch.put_metric_data(
    Namespace='NATAN_LOC/MongoDB',
    MetricData=[
        {
            'MetricName': 'ConnectionCount',
            'Value': connection_count,
            'Unit': 'Count'
        }
    ]
)
```

**Opzione B: CloudWatch Alarms**

1. **AWS Console** → **CloudWatch** → **Alarms**
2. **Create alarm**
3. **Metric**: Custom metric `NATAN_LOC/MongoDB/ConnectionCount`
4. **Threshold**: Es. `> 100 connections`
5. **Action**: Email o SNS notification

### **8.3 ULM/UEM Integration**

**Aggiornare `mongodb_service.py` per logging ULM:**

```python
# Aggiungere logging strutturato
import logging

logger = logging.getLogger(__name__)

@classmethod
def get_client(cls) -> Optional[MongoClient]:
    try:
        # ... connessione MongoDB ...
        logger.info('MongoDB connection established', {
            'host': MONGODB_HOST,
            'database': MONGODB_DATABASE,
            'log_category': 'MONGODB_CONNECTION_SUCCESS'
        })
        # ...
    except Exception as e:
        logger.error('MongoDB connection failed', {
            'host': MONGODB_HOST,
            'error': str(e),
            'log_category': 'MONGODB_CONNECTION_ERROR'
        })
        # ...
```

---

## 🚀 Step 9: Deploy su Forge EC2

### **9.1 Aggiornare `.env` su Forge**

**Via Forge Panel:**
1. **Laravel Forge** → Il tuo server → **Environment**
2. Aggiungi variabili:
   ```
   MONGODB_URI=mongodb+srv://natan_user:password@cluster0.xxxxx.mongodb.net/natan_ai_core?retryWrites=true&w=majority&tls=true
   MONGO_DB_HOST=cluster0.xxxxx.mongodb.net
   MONGO_DB_DATABASE=natan_ai_core
   MONGO_DB_USERNAME=natan_user
   MONGO_DB_PASSWORD=your_password
   ```

**Via SSH:**
```bash
ssh forge@13.48.57.194
cd /home/forge/default/python_ai_service
nano .env
# Aggiungi variabili MongoDB Atlas
```

### **9.2 Installare Dipendenze**

```bash
cd /home/forge/default/python_ai_service
source venv/bin/activate
pip install certifi pymongo[srv]
```

### **9.3 Riavviare Python FastAPI**

**Se Python FastAPI gira come servizio systemd:**
```bash
sudo systemctl restart python-fastapi
# Oppure
sudo systemctl restart natan-python-service
```

**Se Python FastAPI gira manualmente:**
```bash
# Kill processo esistente
pkill -f "uvicorn app.main:app"

# Riavvia
cd /home/forge/default/python_ai_service
source venv/bin/activate
nohup uvicorn app.main:app --host 0.0.0.0 --port 8000 > /var/log/python-fastapi.log 2>&1 &
```

### **9.4 Verificare Deploy**

```bash
# Test connessione
python3 -c "from app.services.mongodb_service import MongoDBService; print('Connected:', MongoDBService.is_connected())"

# Test endpoint
curl http://localhost:8000/api/health
```

---

## 🔍 Step 10: Troubleshooting

### **Problema 1: Connection Timeout**

**Sintomi:**
```
pymongo.errors.ServerSelectionTimeoutError: No servers found
```

**Soluzioni:**
1. Verifica IP whitelist in MongoDB Atlas
2. Verifica Security Group rules (se VPC peering)
3. Verifica connection string (password corretta?)
4. Verifica firewall EC2

### **Problema 2: Authentication Failed**

**Sintomi:**
```
pymongo.errors.OperationFailure: Authentication failed
```

**Soluzioni:**
1. Verifica username/password in `.env`
2. Verifica database user in Atlas
3. Verifica database name nella connection string

### **Problema 3: SSL/TLS Error**

**Sintomi:**
```
[SSL: CERTIFICATE_VERIFY_FAILED]
```

**Soluzioni:**
1. Installa `certifi`: `pip install certifi`
2. Verifica `tlsCAFile=certifi.where()` in connection
3. Verifica che connection string usi `mongodb+srv://`

### **Problema 4: Slow Queries**

**Soluzioni:**
1. Crea index su campi usati frequentemente:
   ```javascript
   db.documents.createIndex({ "tenant_id": 1, "created_at": -1 })
   db.documents.createIndex({ "tenant_id": 1, "scraper_id": 1 })
   ```
2. Monitora query performance in Atlas Dashboard
3. Considera upgrade cluster (M10 → M20)

---

## 📝 Checklist Finale

### **Pre-Deployment:**
- [ ] MongoDB Atlas cluster creato in `eu-north-1`
- [ ] Database user `natan_user` creato
- [ ] IP whitelist configurata (o VPC peering)
- [ ] Connection string ottenuta e testata
- [ ] `.env` aggiornato con credenziali
- [ ] `app/config/__init__.py` supporta MongoDB Atlas
- [ ] `mongodb_service.py` supporta SSL
- [ ] `certifi` installato

### **Deployment:**
- [ ] `.env` aggiornato su Forge EC2
- [ ] Dipendenze installate (`certifi`, `pymongo[srv]`)
- [ ] Python FastAPI riavviato
- [ ] Connessione testata da EC2
- [ ] Endpoint testati

### **Post-Deployment:**
- [ ] Backup configurato (30 giorni retention)
- [ ] Monitoring verificato
- [ ] Index creati su MongoDB
- [ ] Performance monitorate
- [ ] Logging ULM/UEM verificato
- [ ] Documentazione aggiornata

---

## 📚 Risorse Aggiuntive

- **MongoDB Atlas Documentation**: https://docs.atlas.mongodb.com/
- **PyMongo SSL Configuration**: https://pymongo.readthedocs.io/en/stable/examples/tls.html
- **GDPR Compliance MongoDB**: https://www.mongodb.com/compliance/gdpr
- **AWS VPC Peering**: https://docs.aws.amazon.com/vpc/latest/peering/

---

**Versione**: 1.0.0  
**Ultimo aggiornamento**: 2025-01-28  
**Status**: PRODUCTION READY

