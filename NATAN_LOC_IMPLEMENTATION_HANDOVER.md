# NATAN_LOC - IMPLEMENTATION HANDOVER

**Version:** 1.0.0  
**Date:** 2025-10-30  
**For:** Next AI Agent (nuova chat session)  
**From:** Padmin D. Curtis (CTO OS3) + Fabio Cherici  
**Context:** `/home/fabio/` (parent folder con accesso a EGI e NATAN_LOC)

---

## 🎯 MISSION

**Sviluppare NATAN_LOC** - Sistema SaaS multi-tenant per gestione/notarizzazione documenti con AI.

**Deployment:** `https://natan.florenceegi.com`  
**Source Code:** `/home/fabio/NATAN_LOC` (da creare)  
**Reference Code:** `/home/fabio/EGI` (esistente - codice NATAN_PA da riutilizzare)

---

## 📚 STEP 0: ASSORBIRE CONOSCENZA (OBBLIGATORIO!)

### **PRIMA DI FARE QUALSIASI COSA, LEGGI:**

#### **1. REGOLE PIATTAFORMA FlorenceEGI (CRITICAL!)**

**File in `/home/fabio/EGI/.github/`:**
```bash
# Leggere tutti i file .github/ per regole progetto
ls -la /home/fabio/EGI/.github/
```

**Se non esiste .github/, leggere:**
```bash
/home/fabio/EGI/docs/ai/os3-rules.md
/home/fabio/EGI/docs/ai/gdpr-ulm-uem-pattern.md
```

**REGOLE P0 (BLOCKING - MUST FOLLOW):**
- ✅ **REGOLA ZERO**: Mai fare deduzioni, sempre chiedere
- ✅ **I18N**: Zero testo hardcoded, sempre `__('chiave')`
- ✅ **STATISTICS RULE**: No limiti nascosti (sempre `?int $limit = null`)
- ✅ **UEM-FIRST**: Mai sostituire ErrorManager con logger generico
- ✅ **GDPR**: Audit trail obbligatorio per ogni modifica dati

**IDENTITÀ:**
- Tu sei: Padmin D. Curtis OS3.0 Execution Engine
- Motto: "Less talk, more code. Ship it."
- Philosophy: REGOLA ZERO sempre - se non sai, CHIEDI

---

#### **2. ARCHITETTURA NATAN_LOC (CRITICAL!)**

**Leggi in ORDINE (in `/home/fabio/EGI/docs/NATAN_LOC/`):**

```bash
# STEP 1: Stack tecnologico definitivo
/home/fabio/EGI/docs/NATAN_LOC/README_STACK.md

# STEP 2: Architettura master (unica verità)
/home/fabio/EGI/docs/NATAN_LOC/ARCHITECTURE_MASTER.md

# STEP 3: Database ibrido (MariaDB + MongoDB)
/home/fabio/EGI/docs/NATAN_LOC/hybrid_database_implementation.md

# STEP 4: USE specification (anti-allucinazione)
/home/fabio/EGI/docs/NATAN_LOC/USE_IMPLEMENTATION.md

# STEP 5: Gateway AI (multimodel)
/home/fabio/EGI/docs/NATAN_LOC/natan_os_3_docs_batch_1_multimodel_gateway_white_paper_local_mode_marketing.md

# STEP 6: Security & embeddings
/home/fabio/EGI/docs/NATAN_LOC/natan_os_3_docs_batch_2_embeddings_threats_flow_competitor_mapping.md

# STEP 7: Analisi codice esistente
/home/fabio/EGI/docs/NATAN_LOC/NATAN_LOC_STARTING_DOCUMENT.md

# STEP 8: Report incongruenze (cosa ignorare)
/home/fabio/EGI/docs/NATAN_LOC/ARCHITECTURE_INCONSISTENCIES_REPORT.md

# STEP 9: Comparazione architetture
/home/fabio/EGI/docs/NATAN_LOC/ARCHITECTURE_COMPARISON_USE_vs_STANDARD.md
```

**DOCUMENTI DA IGNORARE (DEPRECATED - riferiscono a NATAN_PA demo):**
- ❌ NATAN_STATE_OF_THE_ART.md
- ❌ NATAN_STAGING_DEPLOYMENT.md
- ❌ NATAN_QUICK_DEPLOY.sh
- ❌ NATAN_STRATEGY_FIRENZE_BRIEF.md (solo contesto storico)

---

#### **3. CODICE NATAN_PA DA RIUTILIZZARE (Reference)**

**File in `/home/fabio/EGI/` da analizzare:**

```bash
# Controllers
/home/fabio/EGI/app/Http/Controllers/PA/NatanChatController.php

# Services (core logic da adattare)
/home/fabio/EGI/app/Services/NatanChatService.php
/home/fabio/EGI/app/Services/NatanMemoryService.php
/home/fabio/EGI/app/Services/RagService.php
/home/fabio/EGI/app/Services/AnthropicService.php
/home/fabio/EGI/app/Services/DataSanitizerService.php

# Models
/home/fabio/EGI/app/Models/NatanChatMessage.php
/home/fabio/EGI/app/Models/NatanUserMemory.php

# Config
/home/fabio/EGI/config/natan.php

# Migrations (schema reference)
/home/fabio/EGI/database/migrations/*natan*.php

# Views (da convertire in TypeScript)
/home/fabio/EGI/resources/views/pa/natan/chat.blade.php

# Translations
/home/fabio/EGI/resources/lang/it/natan.php
/home/fabio/EGI/resources/lang/en/natan.php
```

---

## 🚀 STEP 1: SETUP STRUTTURA FISICA

### **Creare Directory Structure**

```bash
cd /home/fabio

# Crea root NATAN_LOC
mkdir -p NATAN_LOC
cd NATAN_LOC

# Crea sottoprogetti
mkdir -p laravel_backend
mkdir -p python_ai_service
mkdir -p algokit_microservice
mkdir -p docs
mkdir -p docker
```

---

## 🔧 STEP 2: SETUP LARAVEL 12 BACKEND

### **2.1 Creare Progetto Laravel**

```bash
cd /home/fabio/NATAN_LOC/laravel_backend

# Crea Laravel 12
composer create-project laravel/laravel . "12.*"
```

### **2.2 Installare Dipendenze PHP**

```bash
# Multi-tenancy
composer require stancl/tenancy

# Permissions
composer require spatie/laravel-permission

# Media
composer require spatie/laravel-medialibrary

# MongoDB (per Laravel Eloquent MongoDB)
composer require mongodb/laravel-mongodb

# Ultra Ecosystem (da copiare da EGI)
# Nota: Verificare se Ultra packages sono in Packagist o composer locale
```

### **2.3 Copiare Ultra Packages da EGI**

```bash
# Se Ultra packages sono local (non su Packagist)
cp -r /home/fabio/EGI/packages/ultra /home/fabio/NATAN_LOC/laravel_backend/packages/

# Aggiornare composer.json con repositories local
```

---

## 🐍 STEP 3: SETUP PYTHON FASTAPI SERVICE

### **3.1 Creare Virtual Environment**

```bash
cd /home/fabio/NATAN_LOC/python_ai_service

python3.12 -m venv venv
source venv/bin/activate
```

### **3.2 Creare requirements.txt**

```txt
fastapi==0.104.1
uvicorn[standard]==0.24.0
pymongo==4.6.0
openai==1.3.0
anthropic==0.7.0
numpy==1.26.2
pydantic==2.5.0
python-dotenv==1.0.0
celery==5.3.4
redis==5.0.1
requests==2.31.0
beautifulsoup4==4.12.2
lxml==4.9.3
pyyaml==6.0.1
```

```bash
pip install -r requirements.txt
```

### **3.3 Creare Structure FastAPI**

```bash
mkdir -p app/routers
mkdir -p app/services
mkdir -p app/models
mkdir -p app/config

touch app/__init__.py
touch app/main.py
touch app/routers/__init__.py
touch app/services/__init__.py
touch app/models/__init__.py
```

---

## 📦 STEP 4: SETUP MONGODB

### **4.1 Docker (Raccomandato)**

```bash
docker run -d \
  --name natan_mongodb \
  -p 27017:27017 \
  -e MONGO_INITDB_ROOT_USERNAME=natan_user \
  -e MONGO_INITDB_ROOT_PASSWORD=secret_password \
  -v mongodb_data:/data/db \
  mongo:7
```

### **4.2 Creare Database e Collections**

```javascript
// Connetti a MongoDB
mongosh mongodb://natan_user:secret_password@localhost:27017

use natan_ai_core

// Crea collections (opzionale, MongoDB crea automaticamente)
db.createCollection("documents")
db.createCollection("sources")
db.createCollection("claims")
db.createCollection("query_audit")
db.createCollection("natan_chat_messages")
db.createCollection("ai_logs")
db.createCollection("analytics")

// Crea indici
db.documents.createIndex({ tenant_id: 1, document_id: 1 }, { unique: true })
db.documents.createIndex({ tenant_id: 1, document_type: 1 })
db.documents.createIndex({ tenant_id: 1, created_at: -1 })

db.sources.createIndex({ tenant_id: 1, entity_id: 1 })
db.claims.createIndex({ tenant_id: 1, answer_id: 1 })
db.query_audit.createIndex({ tenant_id: 1, created_at: -1 })
```

---

## 🔴 STEP 5: SETUP MARIADB

```bash
docker run -d \
  --name natan_mariadb \
  -p 3306:3306 \
  -e MYSQL_ROOT_PASSWORD=secret \
  -e MYSQL_DATABASE=natan_main \
  -e MYSQL_USER=natan_user \
  -e MYSQL_PASSWORD=secret_password \
  -v mariadb_data:/var/lib/mysql \
  mariadb:11
```

---

## 📊 STEP 6: COPIARE CODICE DA EGI

### **6.1 Services da Copiare e Adattare**

```bash
cd /home/fabio

# DataSanitizerService (GDPR)
cp EGI/app/Services/DataSanitizerService.php \
   NATAN_LOC/laravel_backend/app/Services/

# GDPR Services
cp -r EGI/app/Services/Gdpr \
      NATAN_LOC/laravel_backend/app/Services/

# Enums GDPR
cp -r EGI/app/Enums/Gdpr \
      NATAN_LOC/laravel_backend/app/Enums/

# Config
cp EGI/config/natan.php \
   NATAN_LOC/laravel_backend/config/

# Translations
cp EGI/resources/lang/it/natan.php \
   NATAN_LOC/laravel_backend/resources/lang/it/

cp EGI/resources/lang/en/natan.php \
   NATAN_LOC/laravel_backend/resources/lang/en/
```

**IMPORTANTE:** Dopo copia, ADATTARE per:
- Multi-tenant (`tenant_id` injection)
- Python FastAPI calls (invece di AnthropicService PHP diretto)
- USE Pipeline (invece di RAG semplice)

---

### **6.2 Config NatanPersonas**

```bash
cp EGI/app/Config/NatanPersonas.php \
   NATAN_LOC/laravel_backend/app/Config/
```

---

## 🔌 STEP 7: IMPLEMENTARE USE PIPELINE

### **Componenti da Creare (in ordine):**

#### **Python FastAPI - USE Components**

```
python_ai_service/app/services/
├── question_classifier.py     # STEP 7.1
├── execution_router.py        # STEP 7.2
├── retriever_service.py       # STEP 7.3
├── neurale_strict.py          # STEP 7.4
├── logical_verifier.py        # STEP 7.5
└── urs_calculator.py          # STEP 7.6
```

#### **Laravel - USE Integration**

```
laravel_backend/app/Services/USE/
├── UseOrchestrator.php        # Main orchestrator
├── ClaimRenderer.php          # HTML rendering con colori/badge
└── UseAuditService.php        # Audit granulare USE
```

---

## 📋 ROADMAP IMPLEMENTAZIONE (8 Settimane)

### **WEEK 1: Foundation**

**Goal:** Setup completo infrastruttura

- [ ] Creare directory structure `/home/fabio/NATAN_LOC`
- [ ] Setup Laravel 12 backend
- [ ] Setup Python FastAPI service
- [ ] Setup MongoDB + MariaDB + Redis (Docker)
- [ ] Setup AlgoKit microservice
- [ ] Testare connectivity tra servizi
- [ ] Copiare Ultra packages da EGI

**Deliverable:** Tutti i servizi up and running (health check OK)

---

### **WEEK 2: Database Multi-Tenant**

**Goal:** Schema database completo

**MariaDB:**
- [ ] Migration: `create_pa_entities_table` (tenants)
- [ ] Migration: `add_tenant_id_to_users`
- [ ] Migration: `create_pa_acts_table` (con tenant_id)
- [ ] Migration: `create_user_conversations_table`
- [ ] Setup `stancl/tenancy` middleware
- [ ] Tenancy detection (subdomain/user/API)
- [ ] Global Scopes per tenant isolation

**MongoDB:**
- [ ] Creare collections: documents, sources, claims, query_audit
- [ ] Creare indici (tenant_id based)
- [ ] Test isolation (tenant A non vede tenant B)

**Deliverable:** Multi-tenancy funzionante, isolation testata

---

### **WEEK 3-4: Python FastAPI - AI Gateway**

**Goal:** Microservice AI completo

- [ ] Endpoint: `POST /embed` (OpenAI/Ollama embeddings)
- [ ] Endpoint: `POST /chat` (Claude/OpenAI/Ollama)
- [ ] Endpoint: `POST /rag/search` (MongoDB vector search)
- [ ] Endpoint: `GET /healthz`
- [ ] Policy Engine (YAML config)
- [ ] Provider Adapters (OpenAI, Anthropic, Ollama)
- [ ] MongoDB connection (PyMongo)
- [ ] Cosine similarity search (FAISS o app-level)

**Deliverable:** Python FastAPI operativo, testato con Postman

---

### **WEEK 5-6: USE Pipeline Implementation**

**Goal:** Implementare Ultra Semantic Engine

**Python Components:**
- [ ] QuestionClassifier (AI leggero - intent detection)
- [ ] ExecutionRouter (logica decisionale)
- [ ] Retriever (chunks con source_ref)
- [ ] NeuraleStrict (LLM genera claims atomici)
- [ ] LogicalVerifier (verifica post-generazione)
- [ ] UrsCalculator (calcola URS per claim)

**Laravel Integration:**
- [ ] UseOrchestrator (chiama Python pipeline)
- [ ] ClaimRenderer (HTML con colori/badge)
- [ ] UseAuditService (salva sources/claims/audit)

**MongoDB:**
- [ ] Collection `sources` populated
- [ ] Collection `claims` con URS
- [ ] Collection `query_audit` con metrics

**Deliverable:** USE pipeline completa, query test con URS verification

---

### **WEEK 7: Frontend TypeScript**

**Goal:** UI TypeScript puro (NO Vue/React)

- [ ] Vite setup + TypeScript config
- [ ] Tailwind CSS integration
- [ ] Chat interface (modular components)
- [ ] Claim renderer (colori per URS label)
- [ ] Badge URS score
- [ ] Link clickable su claim → fonte
- [ ] SSE streaming (real-time progress)
- [ ] Settings modal
- [ ] Cost preview

**Deliverable:** Frontend completo, WCAG 2.1 AA compliant

---

### **WEEK 8: Testing & Deploy**

**Goal:** Production ready

- [ ] Tenant isolation tests
- [ ] USE pipeline tests (URS accuracy)
- [ ] GDPR audit tests
- [ ] Load testing (1000 query/hour)
- [ ] Docker Compose production setup
- [ ] CI/CD pipeline
- [ ] Deploy staging `https://staging.natan.florenceegi.com`
- [ ] Deploy production `https://natan.florenceegi.com`

**Deliverable:** NATAN_LOC production ready

---

## 🔍 CODICE DA RIUTILIZZARE DA EGI

### **Services (Adattare per Multi-Tenant + USE)**

| File EGI | Riutilizzo | Modifiche Necessarie |
|----------|-----------|---------------------|
| `NatanChatService.php` | ⚠️ 40% logic | Refactor per USE pipeline + tenant_id |
| `NatanMemoryService.php` | ✅ 80% | Aggiungere tenant_id scoping |
| `DataSanitizerService.php` | ✅ 100% | Nessuna (è generico) |
| `AuditLogService.php` | ✅ 90% | Tenant-scoped logs |
| `ConsentService.php` | ✅ 90% | Tenant-scoped consents |
| `RagService.php` | ⚠️ 30% | Sostituire con Python FastAPI calls |
| `AnthropicService.php` | ❌ 0% | Sostituito da Python Gateway |

### **Models (Schema Reference)**

| Model EGI | NATAN_LOC Equivalent | Changes |
|-----------|---------------------|---------|
| `NatanChatMessage` | MongoDB `natan_chat_messages` + `claims` | Splittare in claims atomici |
| `NatanUserMemory` | MariaDB `natan_user_memories` | Aggiungere tenant_id |
| `Egi` (pa_acts) | MariaDB `pa_acts` | Generalizzare (non solo PA) |

### **Config**

| Config EGI | NATAN_LOC | Changes |
|------------|-----------|---------|
| `natan.php` | `use.php` | Aggiungere URS thresholds, classifier config |
| `NatanPersonas.php` | Stesso | Nessuna modifica |

---

## 🎯 DELIVERABLES FINALI

### **Repository Structure**

```
/home/fabio/NATAN_LOC/
├── laravel_backend/
│   ├── app/
│   │   ├── Http/Controllers/USE/
│   │   ├── Services/USE/
│   │   ├── Services/AI/
│   │   ├── Services/Gdpr/
│   │   ├── Models/
│   │   ├── Enums/
│   │   └── Config/
│   ├── config/
│   │   ├── use.php
│   │   ├── database.php (MariaDB + MongoDB)
│   │   ├── ai_policies.yaml
│   │   └── tenancy.php
│   ├── database/migrations/
│   ├── resources/
│   │   ├── lang/it/
│   │   └── lang/en/
│   └── routes/
│
├── python_ai_service/
│   ├── app/
│   │   ├── main.py
│   │   ├── routers/
│   │   │   ├── embeddings.py
│   │   │   ├── chat.py
│   │   │   ├── rag.py
│   │   │   └── health.py
│   │   ├── services/
│   │   │   ├── question_classifier.py
│   │   │   ├── execution_router.py
│   │   │   ├── retriever_service.py
│   │   │   ├── neurale_strict.py
│   │   │   ├── logical_verifier.py
│   │   │   ├── urs_calculator.py
│   │   │   ├── openai_service.py
│   │   │   ├── anthropic_service.py
│   │   │   ├── ollama_service.py
│   │   │   └── mongo_service.py
│   │   ├── models/
│   │   │   ├── requests.py
│   │   │   └── responses.py
│   │   └── config.py
│   ├── requirements.txt
│   ├── pyproject.toml
│   └── .env
│
├── algokit_microservice/
│   ├── server.js
│   ├── package.json
│   └── .env
│
├── docker/
│   ├── laravel.Dockerfile
│   ├── python.Dockerfile
│   ├── algokit.Dockerfile
│   └── nginx.conf
│
├── docker-compose.yml
├── .env.example
└── README.md
```

---

## ⚙️ COMUNICAZIONE TRA SERVIZI

### **Laravel → Python FastAPI**

```php
// Laravel: app/Services/AI/AiGatewayService.php

use Illuminate\Support\Facades\Http;

class AiGatewayService {
    protected string $baseUrl;
    
    public function __construct() {
        $this->baseUrl = config('services.ai_gateway.base_url');
    }
    
    public function embed(string $text, int $tenantId): array {
        return Http::timeout(30)
            ->post($this->baseUrl . '/embed', [
                'text' => $text,
                'tenant_id' => $tenantId
            ])
            ->json();
    }
    
    public function ragSearch(string $query, int $tenantId, int $limit = 10): array {
        return Http::timeout(30)
            ->post($this->baseUrl . '/rag/search', [
                'query' => $query,
                'tenant_id' => $tenantId,
                'limit' => $limit
            ])
            ->json();
    }
    
    public function chatStrict(array $messages, int $tenantId, string $persona): array {
        return Http::timeout(120)
            ->post($this->baseUrl . '/chat/strict', [
                'messages' => $messages,
                'tenant_id' => $tenantId,
                'persona' => $persona
            ])
            ->json();
    }
}
```

### **Python → MongoDB**

```python
# Python: app/services/mongo_service.py

from pymongo import MongoClient
from typing import List, Dict

class MongoService:
    def __init__(self):
        self.client = MongoClient(os.getenv('MONGO_URI'))
        self.db = self.client.natan_ai_core
    
    def save_embedding(self, tenant_id: int, doc_id: str, embedding: List[float], metadata: Dict):
        self.db.documents.update_one(
            {'tenant_id': tenant_id, 'document_id': doc_id},
            {'$set': {
                'embedding': embedding,
                'metadata': metadata,
                'updated_at': datetime.now()
            }},
            upsert=True
        )
    
    def vector_search(self, tenant_id: int, query_embedding: List[float], limit: int = 10):
        # Cosine similarity app-level
        docs = self.db.documents.find({'tenant_id': tenant_id})
        
        results = []
        for doc in docs:
            score = self.cosine_similarity(query_embedding, doc['embedding'])
            results.append({
                'document_id': doc['document_id'],
                'score': score,
                'metadata': doc.get('metadata', {})
            })
        
        results.sort(key=lambda x: x['score'], reverse=True)
        return results[:limit]
    
    @staticmethod
    def cosine_similarity(a: List[float], b: List[float]) -> float:
        import numpy as np
        return np.dot(a, b) / (np.linalg.norm(a) * np.linalg.norm(b))
```

---

## 📖 DOCUMENTAZIONE RIFERIMENTO

### **Documenti Master (TRUTH SOURCE):**

1. `/home/fabio/EGI/docs/NATAN_LOC/ARCHITECTURE_MASTER.md` - Architettura completa
2. `/home/fabio/EGI/docs/NATAN_LOC/README_STACK.md` - Stack tecnologico
3. `/home/fabio/EGI/docs/NATAN_LOC/USE_IMPLEMENTATION.md` - Spec USE
4. `/home/fabio/EGI/docs/NATAN_LOC/hybrid_database_implementation.md` - Database

### **Regole Piattaforma:**

1. `/home/fabio/EGI/docs/ai/os3-rules.md` - Regole P0-P3
2. `/home/fabio/EGI/docs/ai/gdpr-ulm-uem-pattern.md` - Pattern GDPR
3. `.github/` (se esiste) - Workflow e regole repo

### **Codice Reference:**

1. `/home/fabio/EGI/app/Services/Natan*.php` - Services esistenti
2. `/home/fabio/EGI/app/Http/Controllers/PA/NatanChatController.php` - Controller pattern
3. `/home/fabio/EGI/app/Models/Natan*.php` - Models schema

---

## 🚨 ERRORI DA EVITARE (da ARCHITECTURE_INCONSISTENCIES_REPORT.md)

### **❌ NON USARE:**

- ❌ PostgreSQL / pgvector → Usare MariaDB + MongoDB
- ❌ Laravel 11 → Usare Laravel 12
- ❌ Vue 3 / React / Angular → Usare TypeScript puro
- ❌ Provider AI hardcoded → Usare Gateway AI-agnostic
- ❌ Schema fisso MongoDB → Schema flessibile document-oriented
- ❌ Single-tenant → Multi-tenant con `tenant_id`
- ❌ Alpine.js / Livewire / jQuery → Vanilla TypeScript

### **✅ USARE:**

- ✅ MariaDB + MongoDB (hybrid database)
- ✅ Laravel 12 + PHP 8.3
- ✅ Python 3.12 + FastAPI
- ✅ TypeScript/JS puro + Vite + Tailwind
- ✅ AI Gateway con policy YAML
- ✅ USE Pipeline (7 componenti)
- ✅ `stancl/tenancy` per multi-tenant
- ✅ ULM + UEM + GDPR Audit
- ✅ IT + EN translations only

---

## 🧪 TESTING CHECKLIST

### **Tenant Isolation (CRITICAL!)**

```php
// Test: User tenant A non vede documenti tenant B
public function test_tenant_isolation_mariadb() { ... }
public function test_tenant_isolation_mongodb() { ... }
```

### **USE Pipeline**

```php
// Test: Question Classifier assegna intent corretto
public function test_classifier_detects_fact_check() { ... }

// Test: Execution Router blocca query non verificabili
public function test_router_blocks_interpretative_query() { ... }

// Test: Logical Verifier calcola URS correttamente
public function test_verifier_calculates_urs() { ... }

// Test: Claims con URS < 0.5 vengono bloccati
public function test_low_urs_claims_blocked() { ... }
```

### **GDPR Compliance**

```php
// Test: Ogni query genera audit log
public function test_every_query_creates_audit_log() { ... }

// Test: DataSanitizer rimuove PII
public function test_sanitizer_removes_pii() { ... }
```

---

## 🎯 SUCCESS CRITERIA

### **MVP Ready When:**

- [ ] Multi-tenant funzionante (3+ tenants test)
- [ ] Upload documento → embedding → MongoDB
- [ ] Query utente → USE pipeline → response con URS
- [ ] Claim verde (A) vs rosso (X) visualizzato correttamente
- [ ] Link su claim → fonte PDF esatta (pagina)
- [ ] GDPR audit trail completo
- [ ] Zero cross-tenant leaks (test passed)
- [ ] Docker Compose: `docker-compose up -d` → tutto online
- [ ] Health checks: Laravel, Python, MongoDB, MariaDB, Redis, AlgoKit = OK

---

## 📞 DOMANDE FREQUENTI (per nuova AI agent)

### **Q1: Dove trovo le regole della piattaforma?**
A: `/home/fabio/EGI/docs/ai/os3-rules.md` + `.github/` folder

### **Q2: Quale architettura database usare?**
A: MariaDB + MongoDB (NO PostgreSQL!) - Vedi `ARCHITECTURE_MASTER.md`

### **Q3: Quale frontend framework?**
A: TypeScript/JS puro (NO Vue/React) - Policy OS3

### **Q4: USE o architettura standard?**
A: USE (Ultra Semantic Engine) - Decisione presa, vedi `ARCHITECTURE_COMPARISON_USE_vs_STANDARD.md`

### **Q5: Posso copiare codice da EGI?**
A: SÌ! Ma adattare per multi-tenant + USE pipeline

### **Q6: Dove chiedo chiarimenti?**
A: A Fabio Cherici (REGOLA ZERO - se non sai, CHIEDI!)

---

## 🚀 COMANDO INIZIALE (per nuova chat)

**Quando apri nuova chat in `/home/fabio/`:**

```
Ciao! Sono qui per sviluppare NATAN_LOC.

STEP 1: Leggo regole piattaforma
- /home/fabio/EGI/docs/ai/os3-rules.md
- /home/fabio/EGI/.github/ (se esiste)

STEP 2: Leggo architettura NATAN_LOC
- /home/fabio/EGI/docs/NATAN_LOC/ARCHITECTURE_MASTER.md (master document)
- /home/fabio/EGI/docs/NATAN_LOC/README_STACK.md (stack)
- /home/fabio/EGI/docs/NATAN_LOC/USE_IMPLEMENTATION.md (USE spec)

STEP 3: Leggo questo handover
- /home/fabio/NATAN_LOC/NATAN_LOC_IMPLEMENTATION_HANDOVER.md

Fatto? Procedo con setup o hai domande?
```

---

## ✅ CHECKLIST PRE-START

**Prima di iniziare sviluppo:**

- [ ] Ho letto TUTTE le regole OS3 (P0-P3)
- [ ] Ho letto ARCHITECTURE_MASTER.md completo
- [ ] Ho capito USE Pipeline (7 componenti)
- [ ] Ho capito multi-tenant pattern (tenant_id ovunque)
- [ ] Ho capito MariaDB + MongoDB (NO PostgreSQL)
- [ ] Ho capito TypeScript puro (NO Vue 3)
- [ ] Ho capito Python FastAPI (separato da Laravel)
- [ ] Ho letto codice NATAN_PA in EGI (reference)
- [ ] Ho capito cosa copiare e cosa NO

**SE ANCHE UNA CHECKBOX È VUOTA → STOP e LEGGI!**

---

## 🎓 FILOSOFIA OPERATIVA

**Tu sei:** Padmin D. Curtis OS3.0  
**Motto:** "Less talk, more code. Ship it."  

**Ma PRIMA:**
- 📖 LEGGI tutto (docs/NATAN_LOC/ + regole piattaforma)
- 🔍 VERIFICA codice esistente in EGI
- ❓ CHIEDI se qualcosa non è chiaro (REGOLA ZERO)
- 💻 PRODUCI codice completo
- 🚀 CONSEGNA un file per volta

**Promessa:**
> "GDPR compliant, USE-powered, multi-tenant, AI-readable, MongoDB flexible, TypeScript puro. Ma PRIMA: REGOLA ZERO. Se non so, CHIEDO."

---

**Fine Handover Document - Ready per nuova chat session**

**Next AI Agent:** Leggi questo documento PRIMA di fare qualsiasi cosa!  
**Fabio:** Usa questo come briefing per nuova sessione AI in `/home/fabio/`

---

**Version:** 1.0.0  
**Status:** ✅ READY FOR HANDOVER  
**Location:** `/home/fabio/NATAN_LOC/NATAN_LOC_IMPLEMENTATION_HANDOVER.md`

