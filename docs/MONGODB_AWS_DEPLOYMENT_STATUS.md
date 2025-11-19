# 🚀 MongoDB Atlas AWS - Status Deployment

**Versione**: 1.0.0  
**Data**: 2025-01-28  
**Contesto**: FlorenceEGI - NATAN_LOC Production Setup

---

## ✅ COMPLETATO (Automatizzato)

### **1. Configurazione Python FastAPI**

- [x] ✅ **Aggiornato `app/config/__init__.py`**
  - Supporto `MONGODB_URI` environment variable
  - Auto-detection MongoDB Atlas (mongodb.net)
  - Auto-generazione connection string con SSL

- [x] ✅ **Aggiornato `app/services/mongodb_service.py`**
  - Supporto SSL/TLS per MongoDB Atlas
  - Integrazione `certifi` per certificati CA
  - Logging strutturato (ULM-compatible)
  - Fallback graceful se MongoDB non disponibile

- [x] ✅ **Aggiunto `certifi` a `requirements.txt`**
  - Versione: `certifi==2024.2.2`
  - Necessario per SSL/TLS MongoDB Atlas

- [x] ✅ **Creato `.env.example`**
  - Template completo per MongoDB Atlas
  - Esempi per locale e produzione
  - Documentazione inline

- [x] ✅ **Creato script di test**
  - `scripts/test_mongodb_atlas_connection.py`
  - Test connessione e operazioni base
  - Troubleshooting automatico

---

## ⚠️ DA FARE MANUALMENTE (Richiede UI)

### **2. MongoDB Atlas Setup**

- [ ] ⚠️ **Creare cluster MongoDB Atlas**
  - URL: https://www.mongodb.com/cloud/atlas
  - Region: `eu-north-1` (Stockholm)
  - Tier: M10 (produzione) o M0 (test)
  - ⏱️ Tempo: 5-10 minuti

- [ ] ⚠️ **Creare database user**
  - Username: `natan_user`
  - Password: Genera password sicura
  - Role: `Read and write to any database`

- [ ] ⚠️ **Configurare Network Access**
  - **Opzione A (Semplice)**: IP Whitelist
    - Aggiungere IP: `13.48.57.194` (EC2 Public IP)
  - **Opzione B (Sicuro)**: VPC Peering
    - Configurare VPC peering con `vpc-019e351bf6db868ab`

- [ ] ⚠️ **Ottenere Connection String**
  - Atlas Dashboard → Clusters → Connect
  - Driver: Python
  - Copiare connection string

---

### **3. Configurare Environment Variables**

- [ ] ⚠️ **Aggiornare `.env` su Forge EC2**
  - Aggiungere `MONGODB_URI` con connection string
  - Oppure componenti separati (MONGO_DB_HOST, etc.)

**Template:**
```env
MONGODB_URI=mongodb+srv://natan_user:YOUR_PASSWORD@cluster0.xxxxx.mongodb.net/natan_ai_core?retryWrites=true&w=majority&tls=true
```

---

### **4. Installare Dipendenze**

- [ ] ⚠️ **SSH su EC2 Forge**
  ```bash
  ssh forge@13.48.57.194
  cd /home/forge/default/python_ai_service
  source venv/bin/activate
  pip install certifi
  ```

---

### **5. Test Connessione**

- [ ] ⚠️ **Test da EC2**
  ```bash
  cd /home/forge/default/python_ai_service
  source venv/bin/activate
  python3 scripts/test_mongodb_atlas_connection.py
  ```

---

## 📋 Checklist Completa

### **Pre-Deployment:**
- [x] ✅ Codice Python aggiornato per MongoDB Atlas
- [x] ✅ SSL/TLS support implementato
- [x] ✅ `.env.example` creato
- [x] ✅ Script di test creato
- [ ] ⚠️ MongoDB Atlas cluster creato
- [ ] ⚠️ Database user creato
- [ ] ⚠️ Network access configurato
- [ ] ⚠️ Connection string ottenuta

### **Deployment:**
- [ ] ⚠️ `.env` aggiornato su Forge EC2
- [ ] ⚠️ Dipendenze installate (`certifi`)
- [ ] ⚠️ Python FastAPI riavviato
- [ ] ⚠️ Connessione testata

### **Post-Deployment:**
- [ ] ⚠️ Backup configurato (30 giorni)
- [ ] ⚠️ Monitoring verificato
- [ ] ⚠️ Index creati su MongoDB
- [ ] ⚠️ Performance monitorate

---

## 🚀 Prossimi Passi

1. **Segui guida operativa**: `docs/MONGODB_AWS_OPERATIONAL_GUIDE.md`
2. **Crea cluster MongoDB Atlas** (Step 1-2 della guida)
3. **Configura `.env`** con connection string (Step 3)
4. **Deploy su Forge** (Step 9)
5. **Test connessione** (Step 6)

---

## 📚 File di Riferimento

- **Guida Operativa**: `docs/MONGODB_AWS_OPERATIONAL_GUIDE.md`
- **Questionario Completato**: `docs/MONGODB_AWS_QUESTIONNAIRE_COMPLETED.md`
- **Security Group Analysis**: `docs/AWS_SECURITY_GROUP_ANALYSIS.md`

---

**Versione**: 1.0.0  
**Status**: CODE READY - AWAITING MANUAL SETUP

