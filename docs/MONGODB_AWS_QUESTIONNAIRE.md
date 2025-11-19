# 📋 MongoDB su AWS - Questionario Configurazione

**Versione**: 1.0.0  
**Data**: 2025-01-28  
**Contesto**: FlorenceEGI - NATAN_LOC Production Setup  
**Autore**: Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici

---

## 🎯 Scopo del Documento

Questo questionario raccoglie tutte le informazioni necessarie per creare una **guida operativa completa** per configurare MongoDB su AWS per NATAN_LOC.

**⚠️ REGOLA ZERO APPLICATA:**  
Non invento configurazioni. Compila questo questionario e genererò la guida precisa per il tuo setup specifico.

---

## 📝 ISTRUZIONI

1. **Rispondi a tutte le domande** (segna ✅ o ❌, compila i campi)
2. **Salva questo file** con le tue risposte
3. **Condividi le risposte** → Genererò la guida operativa completa

---

## 1️⃣ AWS Infrastructure Esistente

### 1.1 VPC Configuration
- [x] ✅ **VERIFICATO** - VPC esistente trovato
- [ ] ❌ Devo creare un nuovo VPC

**✅ VERIFICATO da AWS Console:**
- **VPC ID**: `vpc-019e351bf6db868ab` ✅ **VERIFICATO**
- **VPC Name**: `vapor-network-1753455845` ✅ **VERIFICATO**
- **CIDR Block**: `_________________` ⚠️ **DA VERIFICARE** (vedi VPC Dashboard)
- **Region**: `eu-north-1` (Stockholm, Svezia) ✅ **VERIFICATO**

**💡 Nota**: VPC trovato. CIDR Block da verificare su VPC Dashboard se necessario.

### 1.2 Security Groups
- [x] ✅ **VERIFICATO** - Security Groups esistenti
- [ ] ❌ Devo creare nuovi Security Groups

**✅ VERIFICATO:**
- **Laravel Security Group ID**: `sg-0c960d72011237d05` ✅ **VERIFICATO**
- **Security Group Name**: `default` ✅ **VERIFICATO**
- **Security Group Rule ID (Outbound)**: `sgr-05c5ab2ed8640ef95` (outbound - All traffic)

**💡 Nota**: Security Group ID principale verificato. Questo è il Security Group da usare per configurare MongoDB Atlas access. Le outbound rules permettono già tutto il traffico in uscita (necessario per MongoDB Atlas).

### 1.3 AWS Region
**Quale regione AWS stai usando?**

- [ ] `eu-south-1` (Milano, Italia) - **CONSIGLIATA per compliance italiana/GDPR**
- [ ] `eu-central-1` (Francoforte, Germania) - **CONSIGLIATA per compliance GDPR**
- [ ] `eu-west-1` (Dublino, Irlanda)
- [ ] `eu-west-2` (Londra, UK)
- [x] `eu-north-1` (Stockholm, Svezia) ✅ **VERIFICATO** - **ATTENZIONE: Regione EU ma non Italia**

**⚠️ IMPORTANTE - Compliance GDPR:**
```
Regione attuale: eu-north-1 (Stockholm, Svezia)
✅ Regione EU - GDPR compliant
⚠️ Non è regione italiana (eu-south-1 Milano)
✅ Svezia è in EU quindi dati risiedono in EU (GDPR OK)
```

**Motivazione scelta regione:**
```
Regione eu-north-1 (Stockholm) è GDPR compliant (EU).
Se serve regione italiana specifica, considerare migrazione a eu-south-1 (Milano).
Per MongoDB Atlas: può essere deployato in qualsiasi regione EU.
```

### 1.4 EC2 Laravel Instance (Forge)
- [x] ✅ **CONFERMATO** - Ho già un'istanza EC2 gestita da Forge dove gira Laravel
- [ ] ❌ Laravel non è ancora deployato su AWS

**✅ VERIFICATO da AWS Console:**
- **Instance ID**: `i-0e50d9a88c7682f20` ✅ **VERIFICATO**
- **Instance Name**: `florenceegi-staging` ✅ **VERIFICATO**
- **Instance Type**: `t3.small` ✅ **VERIFICATO**
- **Instance State**: `Running` ✅ **VERIFICATO**
- **Private IP**: `10.0.1.121` ✅ **VERIFICATO**
- **Public IP**: `13.48.57.194` ✅ **VERIFICATO** (da URL sslip.io)
- **Public DNS**: `ec2-13-48-57-194.eu-north-1.compute.amazonaws.com` ✅ **VERIFICATO**
- **Subnet ID**: `subnet-0ee5da08fd323ba60` (Public Subnet 2) ✅ **VERIFICATO**
- **VPC ID**: `vpc-019e351bf6db868ab` ✅ **VERIFICATO**
- **Region**: `eu-north-1` ✅ **VERIFICATO**

**✅ VERIFICATO dalla codebase:**
- **Forge Script**: `bash/forge-migrate-atomic.sh` presente
- **Server URL**: `app.13.48.57.194.sslip.io` (verificato - risponde)
- **Forge Path**: `/home/forge/default` (menzionato in script)

---

## 2️⃣ MongoDB Requirements

### 2.1 Deployment Strategy
**Dove deve girare MongoDB?**

- [x] **Opzione A**: MongoDB Atlas (Managed Cloud) - **RACCOMANDATO** ✅
  - ✅ Zero maintenance
  - ✅ Backup automatici
  - ✅ Scaling automatico
  - ✅ Security hardened
  - ✅ Supporto completo vector search (importante per NATAN_LOC)
  - ❌ Costo mensile (~$57/month M10)

- [ ] **Opzione B**: Self-hosted su EC2 separato
  - ✅ Controllo totale
  - ✅ Costo minore per grandi volumi
  - ❌ Maintenance manuale
  - ❌ Backup strategy da implementare
  - ❌ Vector search da configurare manualmente

- [ ] **Opzione C**: Stessa istanza EC2 di Laravel (sconsigliato per produzione)
  - ⚠️ Solo per test/development

**Scelta**: `A` ✅ **RACCOMANDATO** (MongoDB Atlas)

**Motivazione:**
```
✅ Supporto completo vector search (critico per NATAN_LOC AI features)
✅ Zero maintenance (importante per progetto PA mission-critical)
✅ Backup automatici (compliance GDPR)
✅ Security hardened by default
✅ Scaling automatico (per crescita futura)
✅ Compliance-ready (GDPR, ISO)
```

### 2.2 Database Size Estimation
**Dimensioni database previste?**

- **Dimensione attuale (locale)**: `_________________` GB
- **Crescita prevista (12 mesi)**: `_________________` GB
- **Picco stimato**: `_________________` GB

**Tipologia dati:**
- [ ] Documenti testuali (scraping Albo Pretorio)
- [ ] Embeddings vector (AI/ML)
- [ ] Logs e audit trail
- [ ] Altro: `_________________`

### 2.3 Backup Policy
**Requisiti backup:**

- **Frequenza backup:**
  - [ ] Giornaliero (daily)
  - [ ] Settimanale (weekly)
  - [ ] Continuo (continuous/point-in-time)

- **Retention period:**
  - [ ] 7 giorni
  - [ ] 30 giorni (consigliato per GDPR)
  - [ ] 90 giorni
  - [ ] Altro: `_________________` giorni

- **Cross-region backup:**
  - [ ] ✅ Richiesto (disaster recovery)
  - [ ] ❌ Non necessario

- **Test restore:**
  - [ ] ✅ Mensile
  - [ ] ✅ Trimestrale
  - [ ] ❌ Non necessario

---

## 3️⃣ Networking & Security

### 3.1 Access Pattern
**Chi deve accedere a MongoDB?**

- [ ] **Solo Laravel EC2** (produzione)
- [ ] **Laravel EC2 + Lenovo i7 locale** (sviluppo)
- [ ] **Laravel EC2 + Team remoto** (sviluppo condiviso)
- [ ] **Pubblico con autenticazione** (sconsigliato)

**Se accesso da locale (Lenovo i7):**

- [ ] ✅ Ho già VPN configurata (AWS VPN, OpenVPN, WireGuard)
- [ ] ✅ Ho già Bastion Host configurato
- [ ] ❌ Devo configurare accesso sicuro

**Dettagli accesso sicuro:**
```
VPN Type: _________________
Bastion Host IP: _________________
SSH Key Path: _________________
```

### 3.2 TLS/SSL Requirements
**Sicurezza connessione:**

- [ ] ✅ TLS/SSL obbligatorio (produzione)
- [ ] ⚠️ TLS/SSL opzionale (solo sviluppo)
- [ ] ❌ Nessun TLS/SSL (sconsigliato)

**Certificati:**
- [ ] MongoDB Atlas (gestiti automaticamente)
- [ ] AWS Certificate Manager (ACM)
- [ ] Self-signed (solo test)
- [ ] Certificato custom: `_________________`

### 3.3 Network Isolation
**Isolamento rete:**

- [ ] ✅ MongoDB in subnet privata (no public IP)
- [ ] ✅ MongoDB accessibile solo via Security Group rules
- [ ] ✅ VPC Peering configurato (se MongoDB Atlas)
- [ ] ⚠️ MongoDB con public IP (solo se necessario)

**Security Group Rules previste:**
```
Source: Laravel EC2 Security Group
Port: 27017 (MongoDB)
Protocol: TCP
Direction: Inbound
```

---

## 4️⃣ Laravel Connection

### 4.1 MongoDB Package
**Package Laravel per MongoDB:**

- [x] ❌ **VERIFICATO** - `jenssegers/mongodb` NON installato in `composer.json`
- [ ] ✅ Già installato altro package: `_________________`
- [ ] ❌ Devo installare package MongoDB

**✅ VERIFICATO dalla codebase:**
- **Laravel NON usa MongoDB direttamente**
- **MongoDB è usato SOLO da Python FastAPI** (`python_ai_service`)
- **Laravel comunica con MongoDB via Python FastAPI HTTP API**

**Versione package attuale:**
```
Package: N/A (Laravel non usa MongoDB direttamente)
MongoDB Service: Python FastAPI (pymongo)
```

### 4.2 Environment Configuration
**Configurazione .env attuale:**

- [x] ✅ **VERIFICATO** - Ho già `.env` configurato per MongoDB locale (Python FastAPI)
- [ ] ❌ Devo creare configurazione MongoDB

**✅ Configurazione attuale (locale) - VERIFICATA:**
```env
# Python AI Service (.env)
MONGO_URI=mongodb://natan_user:secret_password@localhost:27017/natan_ai_core
MONGO_DB_NAME=natan_ai_core

# Oppure componenti separati:
MONGO_DB_HOST=localhost
MONGO_DB_PORT=27017
MONGO_DB_DATABASE=natan_ai_core
MONGO_DB_USERNAME=natan_user
MONGO_DB_PASSWORD=secret_password
```

**💡 Nota**: Configurazione MongoDB è in `python_ai_service/.env`, non in Laravel

### 4.3 Database Migration
**Strategia database:**

- [x] ✅ **CONFERMATO** - MongoDB è **nuovo database aggiuntivo** (non migrazione)
- [ ] ⚠️ Devo **migrare dati** da MySQL/PostgreSQL a MongoDB
- [ ] ⚠️ Devo **migrare dati** da MongoDB locale a AWS

**✅ VERIFICATO dalla codebase:**
- **Laravel usa MariaDB/MySQL** (configurato in `config/database.php`)
- **MongoDB è database separato** per vector search e document storage
- **Nessuna migrazione necessaria** - MongoDB è già database aggiuntivo

**Se migrazione:**
- **Database sorgente**: `_________________`
- **Dimensione dati da migrare**: `_________________` GB
- **Downtime accettabile**: `_________________` ore/minuti

---

## 5️⃣ Forge Workaround

### 5.1 Forge Integration
**Laravel Forge deployment:**

- [x] ✅ **CONFERMATO** - Forge gestisce deployment Laravel
- [x] ✅ **CONFERMATO** - MongoDB sarà **completamente separato** da Forge
- [ ] ❌ MongoDB gestito da Forge (se supportato)

**✅ VERIFICATO dalla codebase:**
- **Forge Script**: `bash/forge-migrate-atomic.sh` presente e funzionante
- **Forge Path**: `/home/forge/default` (menzionato in script)
- **Server URL**: `app.13.48.57.194.sslip.io` (verificato - risponde)

**Forge Server Details:**
- **Server Name**: `_________________` ⚠️ **DA VERIFICARE su Forge panel**
- **Forge Provider**: `AWS` ✅ **VERIFICATO** (IP 13.48.57.194 su AWS)
- **PHP Version**: `_________________` ⚠️ **DA VERIFICARE** (probabilmente 8.2+ da composer.json)
- **Laravel Version**: `^12.0` ✅ **VERIFICATO** (da `composer.json`)

### 5.2 Connection Setup
**Come collegare Laravel (Forge) a MongoDB (separato):**

- [x] ✅ **IMPORTANTE**: Laravel NON si connette direttamente a MongoDB
- [x] ✅ **ARCHITETTURA**: Laravel → Python FastAPI → MongoDB
- [ ] ✅ Documentare connection string in `.env` Python FastAPI (non Laravel)
- [ ] ✅ Configurare Security Group per permettere connessione Python FastAPI → MongoDB
- [ ] ✅ Testare connessione da Python FastAPI (su EC2 o separato) a MongoDB
- [ ] ⚠️ Usare VPN/Bastion per connessione sicura (se MongoDB Atlas)

**✅ ARCHITETTURA VERIFICATA:**
```
Laravel (Forge EC2) 
    ↓ HTTP API
Python FastAPI (su stesso EC2 o separato)
    ↓ MongoDB Connection
MongoDB (AWS - Atlas o DocumentDB)
```

**Note aggiuntive:**
```
MongoDB connection è gestita da Python FastAPI, non da Laravel.
Laravel comunica con MongoDB solo tramite Python FastAPI HTTP endpoints.
Configurazione MongoDB va in python_ai_service/.env, non in Laravel.
```

---

## 6️⃣ Monitoring & Maintenance

### 6.1 ULM/UEM Integration
**Integrazione logging NATAN_LOC:**

- [x] ✅ **CONFERMATO** - ULM già presente in progetto (`ultra/ultra-log-manager`)
- [x] ✅ **CONFERMATO** - UEM già presente in progetto (`ultra/ultra-error-manager`)
- [ ] ⚠️ Solo logging standard (no ULM/UEM)
- [ ] ❌ Nessun logging MongoDB specifico

**✅ VERIFICATO dalla codebase:**
- **ULM Package**: `ultra/ultra-log-manager` installato in `composer.json`
- **UEM Package**: `ultra/ultra-error-manager` installato in `composer.json`
- **MongoDB Service**: Usa `logging` standard Python (da `mongodb_service.py`)

**Eventi da loggare:**
- [x] ✅ Connection success/failure (già implementato in `mongodb_service.py`)
- [ ] ⚠️ Query performance (slow queries) - **DA IMPLEMENTARE**
- [x] ✅ Errori operazioni CRUD (già implementato con logging Python)
- [ ] ⚠️ Backup operations - **DA IMPLEMENTARE**
- [ ] ⚠️ Integrazione ULM/UEM in Python FastAPI - **DA IMPLEMENTARE** (attualmente solo logging Python standard)

### 6.2 Monitoring Tools
**Strumenti di monitoring:**

- [ ] ✅ AWS CloudWatch (nativo AWS)
- [ ] ✅ MongoDB Atlas Monitoring (se Atlas)
- [ ] ✅ Prometheus + Grafana
- [ ] ✅ Datadog / New Relic
- [ ] ⚠️ Solo logging applicativo
- [ ] ❌ Nessun monitoring esterno

**Metriche da monitorare:**
- [ ] CPU usage
- [ ] Memory usage
- [ ] Disk I/O
- [ ] Connection count
- [ ] Query performance
- [ ] Replication lag (se replica set)
- [ ] Altro: `_________________`

### 6.3 Alerting
**Sistema di alert:**

- [ ] ✅ CloudWatch Alarms
- [ ] ✅ Email notifications
- [ ] ✅ Slack notifications
- [ ] ✅ UEM error alerts (già implementato)
- [ ] ❌ Nessun alert automatico

**Soglie alert:**
```
CPU > 80%: [ ] Alert
Memory > 85%: [ ] Alert
Connection count > 100: [ ] Alert
Replication lag > 5s: [ ] Alert
```

---

## 7️⃣ Compliance & Security

### 7.1 GDPR Compliance
**Requisiti GDPR:**

- [ ] ✅ Dati devono risiedere in EU (regione EU obbligatoria)
- [ ] ✅ Encryption at rest obbligatoria
- [ ] ✅ Encryption in transit obbligatoria
- [ ] ✅ Audit logging obbligatorio
- [ ] ✅ Data retention policy definita
- [ ] ✅ Right to deletion implementato

**Note compliance:**
```
_________________________________________________
_________________________________________________
```

### 7.2 Security Hardening
**Sicurezza aggiuntiva:**

- [ ] ✅ IP whitelist configurata
- [ ] ✅ Strong passwords policy
- [ ] ✅ Database user con least privilege
- [ ] ✅ Network isolation (VPC, Security Groups)
- [ ] ✅ Regular security updates
- [ ] ✅ Penetration testing (se richiesto)

---

## 8️⃣ Costi & Budget

### 8.1 Budget Disponibile
**Budget mensile stimato:**

- **Budget disponibile**: `_________________` €/mese
- **Budget annuale**: `_________________` €/anno

**Priorità:**
- [ ] Costo minimo (self-hosted EC2)
- [ ] Bilanciamento costo/features (MongoDB Atlas M10)
- [ ] Performance massima (MongoDB Atlas M20+)
- [ ] Altro: `_________________`

### 8.2 Cost Optimization
**Strategia ottimizzazione costi:**

- [ ] ✅ Reserved instances (se EC2)
- [ ] ✅ Auto-scaling (scale down quando non necessario)
- [ ] ✅ Storage optimization (compression, cleanup)
- [ ] ✅ Monitoring costi CloudWatch
- [ ] ❌ Nessuna ottimizzazione necessaria

---

## 9️⃣ Timeline & Priorità

### 9.1 Deployment Timeline
**Quando serve MongoDB su AWS?**

- [ ] ✅ Urgente (entro 1 settimana)
- [ ] ⚠️ Normale (entro 1 mese)
- [ ] ⏳ Pianificato (entro 3 mesi)
- [ ] 📅 Data specifica: `_________________`

### 9.2 Priorità Features
**Ordina per priorità (1 = massima):**

- [ ] Setup base MongoDB: `___`
- [ ] Connessione Laravel: `___`
- [ ] Backup automatici: `___`
- [ ] Monitoring: `___`
- [ ] Accesso da locale: `___`
- [ ] Disaster recovery: `___`
- [ ] Performance tuning: `___`

---

## 🔟 Informazioni Aggiuntive

### 10.1 Contatti & Support
**Team responsabile:**

- **DevOps/Infrastructure**: `_________________`
- **Backend Developer**: `_________________`
- **Security Officer**: `_________________`

### 10.2 Documentazione Esistente
**Documenti di riferimento:**

- [ ] ✅ Ho documentazione AWS esistente
- [ ] ✅ Ho runbook deployment
- [ ] ✅ Ho disaster recovery plan
- [ ] ❌ Nessuna documentazione esistente

**Link documentazione:**
```
_________________________________________________
_________________________________________________
```

### 10.3 Note Finali
**Altre informazioni rilevanti:**

```
_________________________________________________
_________________________________________________
_________________________________________________
_________________________________________________
```

---

## ✅ CHECKLIST COMPLETAMENTO

Prima di procedere, verifica:

- [ ] Tutte le sezioni compilate
- [ ] Scelte tecniche giustificate
- [ ] Budget e timeline definiti
- [ ] Security requirements chiari
- [ ] Compliance requirements verificati

---

## 🚀 PROSSIMI PASSI

Una volta completato questo questionario:

1. **Salva questo file** con le tue risposte
2. **Condividi le risposte** → Genererò:
   - ✅ Guida operativa step-by-step
   - ✅ Scripts di deployment
   - ✅ Configurazioni `.env` pronte
   - ✅ Security Group rules
   - ✅ Backup automation
   - ✅ Monitoring setup
   - ✅ Troubleshooting guide
   - ✅ Rollback procedures

---

**Versione**: 1.0.0  
**Data creazione**: 2025-01-28  
**Status**: QUESTIONNAIRE - In attesa risposte

