# 📊 ANALISI STATO ATTUALE - SCRAPER ALBO PRETORIO

**Data analisi**: 13 novembre 2025  
**Obiettivo**: Sistema multi-tenant per scraping albi pretori italiani

---

## 🔍 STATO ATTUALE - COSA ABBIAMO

### **Scraper Esistenti**

#### **1. `scrape_albo_firenze_v2.py`** - HTML Scraping
- ✅ **Funzionante**: Scraping pagine HTML albo pretorio Firenze
- ✅ **Target**: `https://accessoconcertificato.comune.fi.it/AOL/Affissione/ComuneFi/Page`
- ✅ **Estrazione**: 
  - Tipo atto, direzione
  - Numero registro, numero atto
  - Date inizio/fine pubblicazione
  - Oggetto atto
  - Link PDF allegati
- ✅ **Features**:
  - Paginazione automatica (estrae totale pagine)
  - Rate limiting (sleep tra richieste)
  - Download PDF opzionale
  - Salvataggio JSON
- ✅ **Librerie**: `requests`, `BeautifulSoup4`
- ⚠️ **Limitazioni**: 
  - Solo HTML parsing (no JS rendering)
  - Specifico per struttura DOM Firenze
  - No multi-tenant support
  - No MongoDB integration

#### **2. `scrape_firenze_deliberazioni.py`** - API Scraping
- ✅ **Funzionante**: Scraping via API REST Firenze
- ✅ **Target**: `https://accessoconcertificato.comune.fi.it/trasparenza-atti-cat/searchAtti`
- ✅ **Estrazione**:
  - Deliberazioni Giunta (DG)
  - Deliberazioni Consiglio (DC)
  - Determinazioni Dirigenziali (DD)
  - Decreti Sindacali (DS)
  - Ordinanze Dirigenziali (OD)
- ✅ **Features**:
  - **MongoDB integration** completa (tenant-aware)
  - Estrazione testo da PDF
  - Chunking intelligente
  - Generazione embeddings
  - Cost tracking (OpenAI tokens)
  - Download PDF parallelo
  - Salvataggio JSON backup
  - Statistiche dettagliate
- ✅ **Librerie**: `requests`, `asyncio`
- ✅ **Integration**: `PAActMongoDBImporter` per import MongoDB
- 🎯 **Ottimo esempio** per sistema target!

#### **3. `pa_act_mongodb_importer.py`** - MongoDB Importer
- ✅ **Componente riutilizzabile** per import atti PA
- ✅ **Features**:
  - Estrazione testo PDF (PyPDF2, pdfplumber)
  - Chunking intelligente (~2000 chars, 200 overlap)
  - Embeddings generation (OpenAI/Ollama)
  - Multi-tenant support (tenant_id)
  - Cost tracking dettagliato
  - Dry-run mode per test
  - Statistics tracking
- ✅ **Storage MongoDB**: Collection `documents` con:
  - `document_type: "pa_act"`
  - `tenant_id` per isolamento
  - Metadata completi (protocol, date, source)
  - Chunks con embeddings per semantic search

---

## 💎 PUNTI DI FORZA ATTUALI

### **Architettura MongoDB Multi-Tenant**
✅ Sistema già pensato per multi-tenancy  
✅ Collection `documents` con `tenant_id`  
✅ Importer generico e riutilizzabile  
✅ Supporto embeddings per semantic search  
✅ Cost tracking integrato

### **Pattern Code Quality**
✅ Codice pulito e ben strutturato  
✅ Error handling robusto  
✅ Logging dettagliato  
✅ Async support (asyncio)  
✅ CLI arguments ben definiti

### **Features Avanzate**
✅ Estrazione PDF multi-library (PyPDF2, pdfplumber)  
✅ Chunking intelligente per retrieval  
✅ Rate limiting per scraping etico  
✅ Progress tracking JSON per frontend  
✅ Backup JSON + MongoDB storage

---

## ⚠️ LIMITAZIONI ATTUALI

### **Specificità Firenze**
❌ Scraper hardcoded per Firenze (URL, DOM structure, API endpoints)  
❌ No generalizzazione per altri comuni  
❌ No auto-detection piattaforma  
❌ No configurazione per-tenant

### **Scalabilità**
❌ No queue system per scraping distribuito  
❌ No scheduling automatico  
❌ No retry logic robusto  
❌ No monitoring/dashboard

### **Pattern Recognition**
❌ No ML per identificare strutture simili  
❌ No fallback automatico se struttura cambia  
❌ No confidence scoring  
❌ Parsing regex fragile

### **Management**
❌ No admin panel per gestione tenant/comuni  
❌ No API REST per accesso dati  
❌ No sistema di notifiche (scraping fallito, etc.)  
❌ No health checks

---

## 🎯 LIBRERIE GIÀ IN USO

### **Core Scraping**
```python
requests       # HTTP client
BeautifulSoup4 # HTML parsing
asyncio        # Async operations
```

### **PDF Processing**
```python
PyPDF2         # PDF text extraction
pdfplumber     # Alternative PDF extraction
```

### **Database**
```python
pymongo        # MongoDB driver (via MongoDBService)
```

### **AI/Embeddings**
```python
openai         # Embeddings generation (via AIRouter)
# + support Ollama (locale)
```

---

## 📋 ANALISI STRUTTURA FIRENZE

### **Piattaforma Identificata**
- **Vendor**: Sistema custom Comune di Firenze
- **CMS**: Non standard (custom web app)
- **Tecnologie**:
  - Frontend: HTML + JavaScript (pagine dinamiche)
  - Backend: API REST JSON
  - PDF storage: File system web-exposed

### **URL Patterns**
```
Albo Pretorio HTML:
https://accessoconcertificato.comune.fi.it/AOL/Affissione/ComuneFi/Page?page=N

API Atti:
https://accessoconcertificato.comune.fi.it/trasparenza-atti-cat/searchAtti
(POST con JSON payload)
```

### **DOM Structure (HTML Scraping)**
```html
<!-- Ogni atto in card div -->
<div class="card concorso-card multi-line">
  <!-- Tipo atto, direzione -->
  <!-- N° registro: XX/YYYY -->
  <!-- N° atto: XX/YYYY -->
  <!-- Inizio pubblicazione: DD/MM/YYYY -->
  <!-- Fine pubblicazione: DD/MM/YYYY -->
  <!-- Oggetto: testo lungo... -->
  <!-- Link PDF: <a href="...pdf"> -->
</div>
```

### **API Structure**
```json
POST /trasparenza-atti-cat/searchAtti
{
  "oggetto": "",
  "numeroAdozione": "",
  "competenza": "DG",
  "annoAdozione": "2024",
  "tipiAtto": ["DG"]
}

Response: Array di atti con:
- numeroAdozione
- tipoAtto
- oggetto
- dataAdozione
- competenza
- annoAdozione
- allegati: [{id, link, contentType}]
```

---

## 🚀 PROSSIMI STEP

### **FASE 1: ANALISI COMUNI (IN CORSO)**
1. ✅ Firenze analizzato
2. 🔄 Analizzare 9+ comuni toscani:
   - Prato, Livorno, Arezzo, Siena
   - Pistoia, Lucca, Grosseto
   - Massa, Pisa
   - + Comuni piccoli/medi per varietà
3. Identificare pattern comuni e piattaforme ricorrenti
4. Documentare differenze e similarità

### **FASE 2: RICERCA DOCUMENTAZIONE**
- Best practices web scraping etico
- Librerie avanzate (Scrapy, Selenium, Playwright)
- ML per pattern recognition
- Anti-detection strategies
- Rate limiting best practices

### **FASE 3: DESIGN ARCHITETTURA**
- Base Scraper class (abstract)
- Platform-specific scrapers (Halley, Insiel, etc.)
- Auto-detection engine
- Configuration system per-tenant
- Queue system (Celery + RabbitMQ)
- Monitoring dashboard

---

## 💡 NOTE TECNICHE

### **Cosa Funziona Bene**
- MongoDB importer è production-ready
- Cost tracking accurato e utile
- Chunking strategy efficace (2000 chars, 200 overlap)
- Async architecture scalabile
- Error handling robusto

### **Cosa Migliorare**
- Generalizzare parsing (attualmente troppo specifico)
- Aggiungere retry logic con exponential backoff
- Implementare circuit breaker per API instabili
- Caching per evitare ri-scraping stesso atto
- Deduplication basata su hash contenuto

### **Cosa Aggiungere**
- ML classifier per auto-detect piattaforma
- Fallback scrapers per handling variazioni DOM
- Confidence scoring per validare dati estratti
- Admin dashboard (Streamlit/Gradio)
- API REST per accesso programmatico
- Notification system (email/Slack quando scraping fallisce)

---

**Status**: ✅ Analisi scraper esistente COMPLETATA  
**Next**: 🔄 Analisi manuale 10+ comuni toscani IN CORSO
