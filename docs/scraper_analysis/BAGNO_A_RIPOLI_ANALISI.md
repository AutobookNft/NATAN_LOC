# 🔬 ANALISI APPROFONDITA - BAGNO A RIPOLI

**Comune**: Bagno a Ripoli (FI)  
**Popolazione**: 26,035 abitanti  
**Data analisi**: 13 novembre 2025

---

## 📊 INFORMAZIONI GENERALI

### **URL Albo Pretorio**
```
https://trasparenza.comune.bagno-a-ripoli.fi.it/web/trasparenza/menu-albo-pretorio
```

### **Portale Principale**
```
https://www.comune.bagno-a-ripoli.fi.it/
```

### **Portale Trasparenza**
```
https://trasparenza.comune.bagno-a-ripoli.fi.it/
```

---

## 🏗️ PIATTAFORMA TECNOLOGICA

### **CMS Base**: Drupal
- Rilevato da meta tags e struttura base
- Versione: Da determinare
- Moduli custom installati

### **Overlay Custom**: JavaScript-Heavy Application
- **Script tags rilevati**: 149
- **Framework frontend**: Da identificare (possibile React/Vue/Angular)
- **Contenuto dinamico**: Caricato via JavaScript dopo page load
- **Rendering**: Richiede browser automation

### **Possibili Vendor**
Candidati da verificare:
- **Halley Informatica** (Halley Suite PA)
- **Civilia Suite** (Engineering)
- **Custom development** da fornitore locale

---

## 🎯 CARATTERISTICHE TECNICHE

### **Struttura DOM**
```
Analisi HTML statico:
- Card divs: 0 (contenuto caricato dopo)
- Tables: 0 (contenuto caricato dopo)
- Lists: 0 (contenuto caricato dopo)
- PDF links (static): 0 (caricati dinamicamente)
```

### **JavaScript Architecture**
```
- 149 script tags
- Contenuto principale in <div id="main"> probabilmente
- Data fetching via AJAX/Fetch API
- Possibili endpoint API da scoprire
```

### **Network Analysis Necessaria**
Per identificare API endpoints:
1. Aprire DevTools → Network tab
2. Navigare albo pretorio
3. Filtrare XHR/Fetch requests
4. Identificare endpoint JSON/API

---

## 🛠️ STRATEGIA SCRAPING

### **Approccio 1: Browser Automation (RACCOMANDATO)**
**Tool**: Selenium o Playwright

**Vantaggi**:
✅ Rendering completo JavaScript  
✅ Interazione con elementi dinamici  
✅ Screenshot per debug  
✅ Gestione paginazione automatica

**Svantaggi**:
❌ Più lento (richiede browser)  
❌ Più resource-intensive  
❌ Manutenzione maggiore

**Implementazione**:
```python
from playwright.async_api import async_playwright

async def scrape_bagno_ripoli_albo():
    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        page = await browser.new_page()
        
        await page.goto('https://trasparenza.comune.bagno-a-ripoli.fi.it/web/trasparenza/menu-albo-pretorio')
        
        # Wait for content to load
        await page.wait_for_selector('.atto-card', timeout=10000)  # Adjust selector
        
        # Extract atti
        atti = await page.query_selector_all('.atto-card')
        
        for atto in atti:
            # Extract data from each atto
            pass
        
        await browser.close()
```

---

### **Approccio 2: API Reverse Engineering**
**Tool**: Browser DevTools + requests

**Vantaggi**:
✅ Molto più veloce  
✅ Resource-efficient  
✅ Più affidabile  
✅ Facile parallelizzazione

**Svantaggi**:
❌ Richiede analisi manuale  
❌ API può cambiare  
❌ Possibili protezioni CSRF/auth

**Procedura**:
1. Aprire DevTools → Network
2. Navigare albo pretorio
3. Identificare chiamate API (XHR/Fetch)
4. Copiare request headers
5. Replicare con requests/httpx

**Esempio endpoint da cercare**:
```
GET /api/albo/atti?page=1&limit=20
POST /trasparenza/search
```

---

### **Approccio 3: Hybrid (API + Browser Fallback)**
**Strategia migliore per robustezza**:

```python
class BagnoARipoliScraper:
    def __init__(self):
        self.api_endpoint = None
        self.use_browser = False
    
    async def scrape(self):
        # Try API first
        if self.api_endpoint:
            try:
                return await self.scrape_via_api()
            except Exception as e:
                logger.warning(f"API failed: {e}, falling back to browser")
                self.use_browser = True
        
        # Fallback to browser
        return await self.scrape_via_browser()
```

---

## 🔍 ANALISI MANUALE RICHIESTA

### **Step 1: Identificare API Endpoints**
```bash
# Aprire DevTools nel browser
# Navigare: https://trasparenza.comune.bagno-a-ripoli.fi.it/web/trasparenza/menu-albo-pretorio
# Network tab → Filter: XHR
# Cercare richieste a:
# - /api/...
# - /trasparenza/...
# - /albo/...
# - Qualsiasi JSON response
```

### **Step 2: Analizzare Response Structure**
```json
// Esempio possibile response
{
  "atti": [
    {
      "id": 12345,
      "numero": "123/2025",
      "tipo": "Determinazione",
      "oggetto": "...",
      "data_pubblicazione": "2025-11-13",
      "data_scadenza": "2025-11-28",
      "allegati": [
        {"url": "/uploads/...", "nome": "documento.pdf"}
      ]
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 50,
    "total_items": 1000
  }
}
```

### **Step 3: Identificare Selettori DOM**
Se API non disponibile, identificare:
```css
/* Selettori per atti */
.atto-item
.documento-albo
.albo-row

/* Campi dati */
.numero-atto
.data-pubblicazione
.oggetto-atto
.link-pdf
```

---

## 📋 METADATA ATTESI

Basandosi su standard albi pretori PA:

```python
atto_structure = {
    'numero_atto': str,          # "123/2025"
    'numero_registro': str,       # "REG/2025/456"
    'tipo_atto': str,            # "Determinazione", "Delibera", etc.
    'oggetto': str,              # Descrizione atto
    'data_pubblicazione': date,  # Inizio pubblicazione
    'data_scadenza': date,       # Fine pubblicazione (solitamente +15gg)
    'ufficio': str,              # Ufficio responsabile
    'allegati': [
        {
            'nome': str,
            'url': str,
            'formato': str,      # "pdf", "doc", etc.
            'dimensione': int    # bytes
        }
    ],
    'note': str,                 # Campo note opzionale
}
```

---

## ⚠️ SFIDE IDENTIFICATE

### **1. Rendering JavaScript Obbligatorio**
- Contenuto non presente in HTML statico
- Richiede browser automation o API discovery

### **2. Possibili Protezioni**
- CSRF tokens
- Session-based authentication
- Rate limiting
- Bot detection (Cloudflare, etc.)

### **3. Struttura Custom**
- Non segue pattern Drupal standard
- Selettori DOM custom
- Possibile vendor proprietario

---

## 🎯 PROSSIMI STEP

### **Priorità Alta**
1. ✅ **Analisi manuale Network tab** per identificare API
2. ⏳ **Test Playwright/Selenium** per rendering JS
3. ⏳ **Identificare vendor software** (Halley? Civilia?)
4. ⏳ **Documentare selettori DOM** se API non disponibile

### **Priorità Media**
5. ⏳ **Verificare rate limiting** e protezioni anti-bot
6. ⏳ **Testare paginazione** e navigation
7. ⏳ **Validare metadata** estratti vs standard PA

### **Priorità Bassa**
8. ⏳ Contattare comune per documentazione tecnica (opzionale)
9. ⏳ Verificare se esiste API pubblica documentata

---

## 💡 RACCOMANDAZIONI

### **Per Sistema Multi-Tenant**
```python
# Configurazione Bagno a Ripoli
{
    'comune': 'bagno_a_ripoli',
    'nome': 'Bagno a Ripoli',
    'provincia': 'FI',
    'url': 'https://trasparenza.comune.bagno-a-ripoli.fi.it/web/trasparenza/menu-albo-pretorio',
    'scraper_class': 'BagnoARipoliScraper',
    'requires_browser': True,  # 🔴 IMPORTANTE
    'browser_type': 'playwright',  # o 'selenium'
    'api_endpoint': None,  # Da scoprire
    'rate_limit': 2.0,  # secondi tra richieste
    'headers': {
        'User-Agent': 'Mozilla/5.0 ...'
    }
}
```

### **Librerie Necessarie**
```bash
# Per browser automation
pip install playwright
playwright install chromium

# Alternative
pip install selenium
pip install undetected-chromedriver  # Anti-detection
```

### **Fallback Strategy**
```python
# Se Bagno a Ripoli fallisce:
1. Log error con dettagli
2. Retry con exponential backoff (3 tentativi)
3. Alert admin se fallimento persistente
4. Continue con altri comuni (don't block batch)
```

---

## 📊 STIMA EFFORT

**Complessità**: 🔴 ALTA

**Tempo stimato**:
- Analisi manuale API: 1-2 ore
- Sviluppo scraper Playwright: 3-4 ore
- Testing e debugging: 2-3 ore
- **Totale**: ~6-9 ore

**Vs comuni Drupal standard**: +400% effort

**Raccomandazione**: Implementare DOPO aver completato scrapers per piattaforme più comuni (Drupal, WordPress, Halley).

---

**Status**: ⏳ Analisi preliminare completata, analisi manuale API necessaria  
**Next**: Aprire DevTools e identificare endpoint API o confermare necessità Playwright
