# 🏛️ Multi-Tenant Albo Pretorio Scraper System

Sistema modulare e scalabile per lo scraping automatizzato degli Albi Pretori dei comuni italiani.

## 📋 Indice

- [Architettura](#architettura)
- [Componenti](#componenti)
- [Installazione](#installazione)
- [Usage](#usage)
- [Scrapers Implementati](#scrapers-implementati)
- [Aggiungere Nuovi Scrapers](#aggiungere-nuovi-scrapers)
- [Testing](#testing)

---

## 🏗️ Architettura

### **Design Pattern: Factory + Abstract Base Class**

```
BaseAlboScraper (abstract)
├── detect_platform() - abstract
├── scrape_page() - abstract
├── get_total_pages() - abstract
├── scrape_all() - comune a tutti
└── save_to_mongodb() - integrazione esistente

ScraperFactory
├── Registry dei scrapers
├── Auto-detection piattaforma
├── Creazione scraper appropriato
└── Interface high-level

Platform-Specific Scrapers
├── DrupalAlboScraper (TODO)
├── TrasparenzaVMScraper (TODO)
├── WordPressAlboScraper (TODO)
└── APIAlboScraper (TODO)
```

---

## 📦 Componenti

### **1. Base Components**

#### **`base_scraper.py`**
- `AttoPA`: Struttura dati standardizzata per atti PA
- `ScrapeResult`: Wrapper risultato con status, atti, errori, stats
- `BaseAlboScraper`: Classe astratta base per tutti gli scrapers
- `CircuitBreaker`: Pattern per gestire siti failing
- `StructuredLogger`: Logging JSON per analisi

#### **`factory.py`**
- `ScraperFactory`: Factory per creazione scrapers
  - `register_scraper()`: Registra nuovo scraper
  - `create_scraper()`: Auto-detection e creazione
  - `scrape_comune()`: Interface high-level
  - `scrape_multiple()`: Scraping sequenziale multiplo

### **2. Utilities** (`utils/`)

#### **`rate_limiter.py`**
- `AdaptiveRateLimiter`: Rate limiting intelligente
  - Presets: `pa_gentle`, `pa_moderate`, `pa_aggressive`, `api_endpoint`
  - Adaptive delays basati su response server
  - Burst limiting
  - Daily limits

#### **`smart_headers.py`**
- `SmartHeaders`: Generazione headers realistici
- `SessionManager`: Gestione sessioni persistenti con cookies

---

## 🚀 Installazione

### **Requirements**

```bash
cd /home/fabio/dev/NATAN_LOC

# Install dependencies
pip install httpx beautifulsoup4 lxml aiofiles

# Per scrapers JavaScript-heavy (quando implementati)
pip install playwright
playwright install chromium
```

### **Directory Structure**

```
python_ai_service/app/scrapers/
├── __init__.py                  # Package exports
├── base_scraper.py             # Base classes
├── factory.py                  # Factory pattern
├── utils/
│   ├── __init__.py
│   ├── rate_limiter.py
│   └── smart_headers.py
├── drupal_scraper.py           # TODO
├── trasparenza_vm_scraper.py   # TODO
└── wordpress_scraper.py        # TODO

logs/scrapers/                   # Structured logs (auto-created)
├── empoli.jsonl
├── firenze.jsonl
└── ...
```

---

## 💻 Usage

### **Esempio Base: Single Comune**

```python
import asyncio
from python_ai_service.app.scrapers import ScraperFactory

async def main():
    # Scrape singolo comune con auto-detection
    result = await ScraperFactory.scrape_comune(
        comune_code='empoli',
        url='https://www.empoli.gov.it/albo-pretorio',
        tenant_id='tenant_toscana',
        max_pages=2,
        save_to_mongodb=True  # Salva automaticamente in MongoDB
    )
    
    print(f"Status: {result.status}")
    print(f"Atti trovati: {len(result.atti)}")
    print(f"Errori: {len(result.errors)}")
    print(f"Stats: {result.stats}")
    
    # Salva risultato in JSON
    result.save_to_json('output/empoli_result.json')

asyncio.run(main())
```

### **Esempio: Multiple Comuni**

```python
async def scrape_toscana():
    comuni = [
        {'code': 'empoli', 'url': 'https://www.empoli.gov.it/albo-pretorio'},
        {'code': 'prato', 'url': 'https://www.comune.prato.it/albo'},
        {'code': 'scandicci', 'url': 'https://www.comune.scandicci.fi.it/albo-pretorio'},
    ]
    
    results = await ScraperFactory.scrape_multiple(
        comuni=comuni,
        tenant_id='tenant_toscana',
        max_pages=5,
        save_to_mongodb=True
    )
    
    # Results è dict: comune_code -> ScrapeResult
    for code, result in results.items():
        print(f"{code}: {result.status} - {len(result.atti)} atti")

asyncio.run(scrape_toscana())
```

### **Configuration**

```python
# Custom config per scraper
config = {
    'rate_limit_preset': 'pa_gentle',  # o 'pa_moderate', 'pa_aggressive'
    'failure_threshold': 5,             # Circuit breaker
    'recovery_timeout': 300,            # 5 min
    'page_delay': 2.0,                  # Delay tra pagine
    'log_dir': 'logs/custom',           # Directory logs
}

result = await ScraperFactory.scrape_comune(
    comune_code='empoli',
    url='https://...',
    tenant_id='tenant_toscana',
    config=config
)
```

---

## 🔧 Scrapers Implementati

### **Status Attuale**

| Scraper | Status | Comuni Coperti | Priority |
|---------|--------|----------------|----------|
| **BaseAlboScraper** | ✅ Implementato | - | - |
| **DrupalAlboScraper** | 🔨 TODO | 3 (23%) | Alta |
| **TrasparenzaVMScraper** | 🔨 TODO | 4 (31%) | ⭐ MASSIMA |
| **WordPressAlboScraper** | 🔨 TODO | 1 (8%) | Media |
| **URBIAlboScraper** | 🔨 TODO | 1 (8%) | Media |
| **APIAlboScraper** | 🔨 TODO | 1 (8%) | Media |

### **Coverage Atteso**

Dopo implementazione primi 3 scrapers:
- **8/13 comuni Toscana (62%)** ✅
- **~1000+ atti** dalla prima settimana

---

## 🆕 Aggiungere Nuovi Scrapers

### **Step 1: Implementa Scraper**

```python
# python_ai_service/app/scrapers/drupal_scraper.py

from .base_scraper import BaseAlboScraper, AttoPA
from typing import List
import httpx
from bs4 import BeautifulSoup

class DrupalAlboScraper(BaseAlboScraper):
    """Scraper per siti Drupal."""
    
    SIGNATURES = ['Drupal', '/sites/default/', 'drupal.js']
    
    async def detect_platform(self, url: str) -> bool:
        """Detect se è Drupal."""
        async with httpx.AsyncClient() as client:
            try:
                response = await client.get(url, timeout=10.0)
                html = response.text
                return any(sig in html for sig in self.SIGNATURES)
            except:
                return False
    
    async def scrape_page(self, url: str, page_num: int = 1) -> List[AttoPA]:
        """Scrape pagina Drupal."""
        page_url = f"{url}?page={page_num - 1}"
        
        async with httpx.AsyncClient() as client:
            response = await client.get(page_url, timeout=15.0)
            response.raise_for_status()
            
            soup = BeautifulSoup(response.content, 'lxml')
            atti_elements = soup.select('.views-row')
            
            atti = []
            for elem in atti_elements:
                # Parse logic...
                atto = self._parse_atto(elem, url)
                if atto:
                    atti.append(atto)
            
            return atti
    
    async def get_total_pages(self, url: str) -> int:
        """Get total pages."""
        # Implementation...
        return 10
    
    def _parse_atto(self, elem, base_url: str) -> AttoPA:
        """Parse singolo atto."""
        # Implementation...
        pass
```

### **Step 2: Registra Scraper**

```python
# In __init__.py o setup script
from .factory import ScraperFactory
from .drupal_scraper import DrupalAlboScraper

# Registra
ScraperFactory.register_scraper(DrupalAlboScraper)
```

### **Step 3: Test**

```python
# Test auto-detection
scraper = await ScraperFactory.create_scraper(
    comune_code='empoli',
    url='https://www.empoli.gov.it/albo-pretorio',
    tenant_id='test'
)

assert scraper is not None
assert isinstance(scraper, DrupalAlboScraper)

# Test scraping
result = await scraper.scrape_all(url, max_pages=1)
assert result.status == 'success'
assert len(result.atti) > 0
```

---

## 🧪 Testing

### **Unit Tests**

```bash
# Run tests (quando implementati)
pytest tests/scrapers/test_base_scraper.py -v
pytest tests/scrapers/test_factory.py -v
pytest tests/scrapers/test_drupal_scraper.py -v
```

### **Integration Tests**

```bash
# Test con siti reali (slow)
pytest tests/scrapers/test_integration.py -v --integration
```

### **Manual Testing**

```python
# Script di test rapido
from python_ai_service.app.scrapers import ScraperFactory
import asyncio

async def test():
    result = await ScraperFactory.scrape_comune(
        comune_code='empoli',
        url='https://www.empoli.gov.it/albo-pretorio',
        tenant_id='test',
        max_pages=1
    )
    print(f"Status: {result.status}")
    print(f"Atti: {len(result.atti)}")

asyncio.run(test())
```

---

## 📊 Monitoring

### **Structured Logs**

I log sono salvati in formato JSONL per facile parsing:

```bash
# Analizza logs
cat logs/scrapers/empoli.jsonl | jq '.event_type' | sort | uniq -c

# Filtra errori
cat logs/scrapers/empoli.jsonl | jq 'select(.event_type == "page_error")'

# Stats per comune
cat logs/scrapers/*.jsonl | jq -s 'group_by(.comune_code) | map({comune: .[0].comune_code, events: length})'
```

### **Stats da ScrapeResult**

```python
result = await ScraperFactory.scrape_comune(...)

print(result.stats)
# {
#   'comune_code': 'empoli',
#   'total_atti': 50,
#   'pages_scraped': 5,
#   'duration_seconds': 12.5,
#   'errors_count': 0,
#   'circuit_breaker_state': 'CLOSED'
# }
```

---

## 🔐 Best Practices

### **1. Rate Limiting**
- USA preset `pa_gentle` o `pa_moderate` per siti PA
- EVITA `pa_aggressive` se non necessario
- Scraping notturno preferito (2-6 AM)

### **2. Identificazione**
```python
# Opzione A: Headers realistici (default)
from .utils import SmartHeaders
headers = SmartHeaders.get_realistic_headers(url)

# Opzione B: Identificati come bot (più etico)
headers = SmartHeaders.get_natan_headers()
# User-Agent: NatanBot/1.0 (Scraper Albi Pretori PA; +https://natan.it/bot; contact@natan.it)
```

### **3. Error Handling**
- Circuit breaker gestisce automaticamente siti failing
- Partial success è OK (alcuni comuni possono fallire)
- Structured logs per debugging

### **4. MongoDB Integration**
- Usa `save_to_mongodb=True` per save automatico
- Multi-tenant isolation via `tenant_id`
- Integrazione con esistente `PAActMongoDBImporter`

---

## 🚀 Prossimi Passi

1. **Implementa DrupalAlboScraper**
   - 3 comuni (Empoli, Prato, Scandicci)
   - 23% coverage

2. **Implementa TrasparenzaVMScraper** ⭐ PRIORITY
   - 4 comuni (Livorno, Grosseto, Pistoia, Carrara)
   - 31% coverage
   - Richiede Playwright (JS-heavy)

3. **Setup Testing**
   - Unit tests
   - Integration tests
   - CI/CD

4. **Monitoring Dashboard**
   - FastAPI endpoint `/health`
   - Dashboard HTML
   - Alerting

5. **Production Deployment**
   - Cron scheduling
   - Queue system (parallel processing)
   - Documentation completa

---

## 📝 License

Internal project - NATAN_LOC

---

## 👥 Contributors

- Fabio (Implementation)
- GitHub Copilot (Architecture & Code Generation)

---

**Last Updated**: 13 novembre 2025  
**Version**: 1.0.0-alpha (Base architecture implemented)
