# 🚀 PROSSIMI PASSI - IMPLEMENTAZIONE SISTEMA MULTI-TENANT

**Status**: Ricerca completata ✅  
**Prossimo**: Implementazione pratica  
**Data**: 13 novembre 2025

---

## 📊 RECAP RICERCA COMPLETATA

### **Documento Creato**
✅ **`MASTER_CLASS_WEB_SCRAPING.md`** - 500+ righe, documentazione completa:
- Best Practices & Etica (legalità PA Italia, polite scraping)
- Librerie Python (requests, httpx, Scrapy, Playwright, Selenium)
- JavaScript Rendering (Playwright deep dive, quando necessario)
- Anti-Detection Strategies (headers, cookies, proxy, fingerprinting)
- Rate Limiting & Politeness (adaptive rate limiter, orari intelligenti)
- Error Handling (retry strategies, circuit breaker)
- Architettura Scalabile (base classes, factory pattern, queue system)
- Performance Optimization (connection pooling, caching, parallel processing)
- Testing & Monitoring (unit tests, integration tests, dashboard)

### **Conoscenze Acquisite**

✅ **Legale & Etico**
- Scraping PA Italia è legale (D.lgs 33/2013, CAD)
- Trasparenza amministrativa è un diritto
- Rispettare rate limiting, robots.txt, identificarsi con User-Agent

✅ **Stack Tecnologico Ottimale**
- **API endpoints**: `httpx` async (Firenze-style)
- **HTML statico**: `requests` + `BeautifulSoup` + `lxml`
- **JS-heavy (Trasparenza VM)**: `Playwright` con stealth mode
- **Progetti grandi (1000+ pagine)**: `Scrapy`

✅ **Architettura**
```
BaseAlboScraper (abstract)
├── DrupalAlboScraper (3 comuni, 23%)
├── TrasparenzaVMScraper (4 comuni, 31%) ⭐ CRITICAL
├── WordPressAlboScraper (1 comune, 8%)
├── URBIAlboScraper (1 comune, 8%)
└── APIAlboScraper (Firenze-style)

ScraperFactory
├── Auto-detection
├── Rate limiting adattivo
├── Circuit breaker
└── Monitoring integrato

DistributedScraperQueue
├── Multi-tenant support
├── Priority queue
├── Parallel processing
└── Health checks
```

---

## 🎯 ROADMAP IMPLEMENTAZIONE

### **Phase 1: Foundation (Settimana 1)**

#### **Task 1.1: Setup Base Architecture**
```bash
# Crea struttura
mkdir -p python_ai_service/app/scrapers
touch python_ai_service/app/scrapers/__init__.py
touch python_ai_service/app/scrapers/base_scraper.py
touch python_ai_service/app/scrapers/factory.py
```

**File**: `base_scraper.py`
- Classe `AttoPA` (dataclass standardizzata)
- Classe `ScrapeResult` 
- Classe astratta `BaseAlboScraper` con:
  - `detect_platform()` - abstract
  - `scrape_page()` - abstract
  - `get_total_pages()` - abstract
  - `scrape_all()` - comune a tutti
  - `save_to_mongodb()` - integrazione esistente

**File**: `factory.py`
- Classe `ScraperFactory`
- Registry scrapers disponibili
- Metodo `create_scraper()` con auto-detection
- Metodo `scrape_comune()` high-level

**Testing**: Unit tests basic structure

---

#### **Task 1.2: DrupalAlboScraper (3 comuni)**
```bash
touch python_ai_service/app/scrapers/drupal_scraper.py
```

**Comuni target**:
- ✅ Empoli: https://www.empoli.gov.it/albo-pretorio
- ✅ Prato: https://www.comune.prato.it/albo
- ✅ Scandicci: https://www.comune.scandicci.fi.it/albo-pretorio

**Features**:
- Detection: signatures Drupal (`/sites/default/`, `node/\d+`)
- Parsing: `.views-row`, `.field-name-*`
- Paginazione: `?page=0` based
- Date parsing italiane
- PDF links extraction

**Testing**: 
- Test detection su 3 comuni
- Test parsing singola pagina
- Mock HTML responses

---

#### **Task 1.3: TrasparenzaVMScraper (4 comuni) ⭐**
```bash
touch python_ai_service/app/scrapers/trasparenza_vm_scraper.py
```

**Comuni target** (31% coverage!):
- ✅ Livorno: https://livorno.trasparenza-valutazione-merito.it/...
- ✅ Grosseto: https://grosseto.trasparenza-valutazione-merito.it/...
- ✅ Pistoia: https://pistoia.trasparenza-valutazione-merito.it/...
- ✅ Carrara: https://carrara.trasparenza-valutazione-merito.it/...

**Features**:
- Detection: `trasparenza-valutazione-merito.it` in URL
- Rendering: Playwright (JS-heavy)
- Stealth mode: anti-detection headers/scripts
- Network interception: cercare API endpoints nascosti
- Resource blocking: no images/CSS per performance

**Priorità**: ALTA - singolo scraper gestisce 31% comuni

**Testing**:
- Test Playwright setup
- Test stealth mode funziona
- Test parsing (adjust selectors dopo inspection)

---

### **Phase 2: Integration & Scale (Settimana 2)**

#### **Task 2.1: Altri Platform Scrapers**
```bash
touch python_ai_service/app/scrapers/wordpress_scraper.py
touch python_ai_service/app/scrapers/urbi_scraper.py
touch python_ai_service/app/scrapers/api_scraper.py
```

**Comuni**:
- WordPress: Bagno a Ripoli (già analizzato)
- URBI: Massa (cloud.urbi.it)
- API: Firenze (adatta scraper esistente)

---

#### **Task 2.2: Queue System**
```bash
touch python_ai_service/app/scrapers/queue_manager.py
```

**Features**:
- `DistributedScraperQueue` con asyncio
- Priority queue (Firenze=1, altri=2)
- Worker pool (5 workers default)
- Result aggregation
- Rate limiting globale

**Testing**: Test con 5 comuni in parallelo

---

#### **Task 2.3: Monitoring**
```bash
touch python_ai_service/app/scrapers/monitor.py
touch python_ai_service/app/scrapers/dashboard.py
```

**Features**:
- `ScraperHealth` per ogni comune
- `ScraperMonitor` con stats
- Health check endpoint (`/health`)
- Dashboard HTML semplice (`/dashboard`)
- Alert su consecutive failures (>3)

---

### **Phase 3: Production (Settimana 3)**

#### **Task 3.1: MongoDB Integration**
- Integra `PAActMongoDBImporter` esistente
- Test multi-tenant isolation
- Bulk insert optimization
- Error handling DB

#### **Task 3.2: Scheduling**
```bash
# Cron job per scraping periodico
0 2 * * * /path/to/scrape_all_comuni.py  # Ogni notte alle 2 AM
```

**Script**: `scrape_all_comuni.py`
- Carica comuni da config
- Usa `DistributedScraperQueue`
- Logging strutturato
- Email report risultati

#### **Task 3.3: Documentation**
- README per setup environment
- API documentation (se esposta)
- Troubleshooting guide
- Deployment guide

---

## 📋 CHECKLIST PRE-IMPLEMENTATION

Prima di iniziare coding, verifica:

### **Environment Setup**
- [ ] Python 3.10+ installato
- [ ] `virtualenv` creato per progetto
- [ ] MongoDB running e accessible
- [ ] Playwright installato (`playwright install chromium`)

### **Dependencies**
```bash
pip install httpx beautifulsoup4 lxml playwright requests
pip install pytest pytest-asyncio  # Testing
```

### **Repository Structure**
```
python_ai_service/app/scrapers/
├── __init__.py
├── base_scraper.py          # BaseAlboScraper, AttoPA, ScrapeResult
├── factory.py               # ScraperFactory
├── drupal_scraper.py        # DrupalAlboScraper
├── trasparenza_vm_scraper.py  # TrasparenzaVMScraper ⭐
├── wordpress_scraper.py     # WordPressAlboScraper
├── urbi_scraper.py          # URBIAlboScraper
├── api_scraper.py           # APIAlboScraper (Firenze-style)
├── queue_manager.py         # DistributedScraperQueue
├── monitor.py               # ScraperMonitor, health checks
├── dashboard.py             # FastAPI dashboard
└── utils/
    ├── rate_limiter.py      # AdaptiveRateLimiter
    ├── retry_strategy.py    # RetryStrategy, circuit breaker
    ├── smart_headers.py     # SmartHeaders, SessionManager
    └── cache.py             # ScraperCache

tests/
├── test_base_scraper.py
├── test_drupal_scraper.py
├── test_trasparenza_vm_scraper.py
├── test_factory.py
└── test_integration.py

scripts/
└── scrape_all_comuni.py     # Main script for cron
```

---

## 🎬 COMANDI QUICK START

### **1. Setup Environment**
```bash
cd /home/fabio/dev/NATAN_LOC

# Create virtual environment (if not exists)
python3 -m venv venv
source venv/bin/activate

# Install dependencies
pip install -r requirements.txt
playwright install chromium
```

### **2. Create Base Files**
```bash
# Directory structure
mkdir -p python_ai_service/app/scrapers/utils
touch python_ai_service/app/scrapers/{__init__,base_scraper,factory}.py
touch python_ai_service/app/scrapers/{drupal_scraper,trasparenza_vm_scraper}.py
touch python_ai_service/app/scrapers/utils/{rate_limiter,retry_strategy,smart_headers,cache}.py
```

### **3. Run Tests**
```bash
# Unit tests
pytest tests/test_base_scraper.py -v

# Integration tests (slow)
pytest tests/test_integration.py -v --integration

# Single comune test
python -m python_ai_service.app.scrapers.factory --comune empoli --max-pages 1
```

### **4. Monitor Dashboard**
```bash
# Start dashboard
uvicorn python_ai_service.app.scrapers.dashboard:app --reload

# Visit http://localhost:8000/dashboard
```

---

## 🚨 CRITICAL PRIORITIES

### **Must-Have (Week 1)**
1. ⭐ **TrasparenzaVMScraper** - 31% coverage con singolo scraper!
2. **DrupalAlboScraper** - 23% coverage (3 comuni facili)
3. **BaseAlboScraper + Factory** - fondamenta architettura

### **Should-Have (Week 2)**
4. **Queue system** - parallel processing 5 comuni
5. **MongoDB integration** - storage multi-tenant
6. **Basic monitoring** - health checks + logs

### **Nice-to-Have (Week 3)**
7. Altri scrapers (WordPress, URBI, API)
8. Dashboard HTML
9. Cron scheduling
10. Documentation completa

---

## 💡 TIPS IMPLEMENTAZIONE

### **Inizia Semplice**
```python
# Prima versione: solo detection + 1 pagina
class DrupalAlboScraper(BaseAlboScraper):
    async def detect_platform(self, url: str) -> bool:
        # Implementation
        pass
    
    async def scrape_page(self, url: str, page_num: int = 1) -> List[AttoPA]:
        # Solo prima pagina
        pass
    
    async def get_total_pages(self, url: str) -> int:
        return 1  # Hardcoded per testing

# Test
scraper = DrupalAlboScraper('empoli', 'test_tenant')
if await scraper.detect_platform('https://www.empoli.gov.it/albo'):
    atti = await scraper.scrape_page('https://www.empoli.gov.it/albo')
    print(f"Found {len(atti)} atti")
```

### **Itera Velocemente**
1. Detection → Test → OK
2. Parse 1 pagina → Test → OK
3. Parse N pagine → Test → OK
4. MongoDB save → Test → OK
5. Rate limiting → Test → OK

### **Debug con Playwright**
```python
# Launch headed (non-headless) per vedere cosa succede
browser = await p.chromium.launch(headless=False, slow_mo=1000)

# Screenshot per debugging
await page.screenshot(path='debug.png')

# Pause per inspection manuale
await page.pause()
```

---

## 📞 DOMANDE & DECISIONI

Prima di iniziare, rispondi:

### **Domanda 1: Quanti comuni scraping parallel?**
- Opzione A: 5 workers (raccomandato per PA educato)
- Opzione B: 10 workers (più veloce, potenzialmente meno educato)
- Opzione C: 1 worker (lentissimo ma sicuro)

**Raccomandazione**: **5 workers** con rate limiting moderato

---

### **Domanda 2: Frequenza scraping?**
- Opzione A: Ogni notte (1x/giorno) - sufficiente per albi pretori
- Opzione B: Ogni 6 ore (4x/giorno) - più aggiornato
- Opzione C: Real-time (ogni ora) - overkill per PA

**Raccomandazione**: **1x/giorno alle 2 AM** (off-peak PA)

---

### **Domanda 3: Storage MongoDB**
- Tenant singolo: `tenant_toscana` per tutti i comuni Toscana
- Tenant per comune: `tenant_firenze`, `tenant_empoli`, etc.
- Tenant per progetto: `tenant_natan_albi_pretori`

**Raccomandazione**: **Tenant per regione** (`tenant_toscana`) poi espandere

---

### **Domanda 4: Priorità comuni?**
Ordine implementazione scrapers:
1. ⭐ **TrasparenzaVM** (4 comuni, 31%)
2. **Drupal** (3 comuni, 23%)
3. **Firenze API** (1 comune, già working)
4. **WordPress** (1 comune, 8%)
5. **URBI** (1 comune, 8%)

**Coverage dopo step 1-3**: **62%** (8/13 comuni) ✅

---

## 🎉 SUCCESSO QUANDO...

Il sistema sarà considerato successo quando:

✅ **8/13 comuni Toscana** scraped automaticamente (62% coverage)  
✅ **Nessun errore critico** per 7 giorni consecutivi  
✅ **MongoDB** contiene 1000+ atti multi-tenant  
✅ **Dashboard** mostra health status real-time  
✅ **Response time** < 2s per comune (avg)  
✅ **Zero complain** da PA per overload  

---

## 🚀 READY TO CODE?

**Prossima azione**:
```bash
# Crea branch
git checkout -b feature/multi-tenant-scrapers

# Setup structure
mkdir -p python_ai_service/app/scrapers/utils
touch python_ai_service/app/scrapers/__init__.py

# Start coding!
code python_ai_service/app/scrapers/base_scraper.py
```

**Comando per iniziare implementazione**:
```
"Iniziamo! Crea BaseAlboScraper in base_scraper.py con AttoPA, ScrapeResult, e classe astratta"
```

---

**Buon coding! 💪**