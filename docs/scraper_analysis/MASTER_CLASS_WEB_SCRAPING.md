# 🎓 MASTER CLASS WEB SCRAPING - GUIDA COMPLETA

**Obiettivo**: Diventare esperti assoluti in web scraping  
**Focus**: Scraping etico, robusto e scalabile per PA italiana  
**Data**: 13 novembre 2025

---

# 📚 INDICE

1. [Best Practices & Etica](#best-practices--etica)
2. [Legalità e Compliance](#legalità-e-compliance)
3. [Librerie Python: Confronto Completo](#librerie-python-confronto-completo)
4. [JavaScript Rendering](#javascript-rendering)
5. [Anti-Detection Strategies](#anti-detection-strategies)
6. [Rate Limiting & Politeness](#rate-limiting--politeness)
7. [Error Handling Robusto](#error-handling-robusto)
8. [Architettura Scalabile](#architettura-scalabile)
9. [Performance Optimization](#performance-optimization)
10. [Testing & Monitoring](#testing--monitoring)

---

# 🎯 BEST PRACTICES & ETICA

## **Principi Fondamentali del "Polite Scraping"**

### **1. Rispetto del robots.txt**
```python
import urllib.robotparser

def can_fetch(url: str, user_agent: str = '*') -> bool:
    """Verifica se possiamo fare scraping rispettando robots.txt"""
    rp = urllib.robotparser.RobotFileParser()
    rp.set_url(f"{url.split('/')[0]}//{url.split('/')[2]}/robots.txt")
    try:
        rp.read()
        return rp.can_fetch(user_agent, url)
    except:
        # Se robots.txt non esiste o errore, assumiamo permesso
        return True

# Esempio uso
if can_fetch('https://www.comune.firenze.it/albo-pretorio'):
    # Procedi con scraping
    pass
```

**Note PA Italia**:
- Molti siti PA **NON hanno robots.txt** (default: permesso)
- Dati pubblici PA sono **accessibili per legge** (trasparenza amministrativa)
- Comunque: sempre rispettare rate limiting per non sovraccaricare server

---

### **2. User-Agent Identificativo**
```python
# ❌ BAD: User-Agent generico o nascosto
headers = {'User-Agent': 'Mozilla/5.0'}

# ✅ GOOD: User-Agent che ci identifica chiaramente
headers = {
    'User-Agent': 'NatanBot/1.0 (Scraper Albi Pretori PA; +https://natan.it/bot; contact@natan.it)'
}
```

**Perché?**
- Trasparenza: PA può identificarci
- Possibilità di whitelist se il bot è utile
- Professionalità e compliance

---

### **3. Rate Limiting Progressivo**
```python
import time
from datetime import datetime, timedelta

class PoliteRateLimiter:
    """Rate limiter adattivo basato su response time del server"""
    
    def __init__(self, min_delay: float = 1.0, max_delay: float = 10.0):
        self.min_delay = min_delay
        self.max_delay = max_delay
        self.current_delay = min_delay
        self.last_request_time = None
        self.error_count = 0
    
    def wait(self):
        """Aspetta il tempo necessario prima della prossima richiesta"""
        if self.last_request_time:
            elapsed = time.time() - self.last_request_time
            sleep_time = max(0, self.current_delay - elapsed)
            if sleep_time > 0:
                time.sleep(sleep_time)
        
        self.last_request_time = time.time()
    
    def adjust_for_response(self, response_time: float, status_code: int):
        """Adatta delay in base a performance server"""
        if status_code == 429:  # Too Many Requests
            self.current_delay = min(self.current_delay * 2, self.max_delay)
            self.error_count += 1
        elif status_code >= 500:  # Server errors
            self.current_delay = min(self.current_delay * 1.5, self.max_delay)
            self.error_count += 1
        elif response_time > 5.0:  # Server lento
            self.current_delay = min(self.current_delay * 1.2, self.max_delay)
        elif status_code == 200 and response_time < 1.0:  # Server veloce
            self.current_delay = max(self.current_delay * 0.9, self.min_delay)
            self.error_count = 0

# Uso
rate_limiter = PoliteRateLimiter(min_delay=1.0, max_delay=10.0)

for page in pages:
    rate_limiter.wait()
    start = time.time()
    response = requests.get(page)
    response_time = time.time() - start
    rate_limiter.adjust_for_response(response_time, response.status_code)
```

---

### **4. Orari di Scraping Consigliati**
```python
from datetime import datetime

def is_good_scraping_time() -> bool:
    """Verifica se è un buon momento per fare scraping"""
    now = datetime.now()
    hour = now.hour
    
    # Evita ore di punta uffici PA (9-12, 14-17)
    if now.weekday() < 5:  # Lun-Ven
        if 9 <= hour < 12 or 14 <= hour < 17:
            return False  # Ore di punta
    
    # Preferisci notte/weekend per scraping intensivo
    if now.weekday() >= 5:  # Sab-Dom
        return True
    
    if hour < 7 or hour >= 20:  # Notte
        return True
    
    return True  # Altri orari OK ma con rate limiting maggiore

# Uso
if not is_good_scraping_time():
    # Aumenta delay
    rate_limiter.min_delay = 3.0
else:
    rate_limiter.min_delay = 1.0
```

---

### **5. Caching Intelligente**
```python
import hashlib
import json
from pathlib import Path
from datetime import datetime, timedelta

class ScraperCache:
    """Cache per evitare re-scraping inutile"""
    
    def __init__(self, cache_dir: str = '.scraper_cache', ttl_hours: int = 24):
        self.cache_dir = Path(cache_dir)
        self.cache_dir.mkdir(exist_ok=True)
        self.ttl = timedelta(hours=ttl_hours)
    
    def _get_cache_key(self, url: str) -> str:
        """Genera chiave cache da URL"""
        return hashlib.md5(url.encode()).hexdigest()
    
    def get(self, url: str) -> dict | None:
        """Recupera da cache se valido"""
        cache_key = self._get_cache_key(url)
        cache_file = self.cache_dir / f"{cache_key}.json"
        
        if not cache_file.exists():
            return None
        
        try:
            with open(cache_file) as f:
                data = json.load(f)
            
            cached_time = datetime.fromisoformat(data['timestamp'])
            if datetime.now() - cached_time < self.ttl:
                return data['content']
        except:
            pass
        
        return None
    
    def set(self, url: str, content: dict):
        """Salva in cache"""
        cache_key = self._get_cache_key(url)
        cache_file = self.cache_dir / f"{cache_key}.json"
        
        with open(cache_file, 'w') as f:
            json.dump({
                'url': url,
                'timestamp': datetime.now().isoformat(),
                'content': content
            }, f)

# Uso
cache = ScraperCache(ttl_hours=24)

def scrape_with_cache(url: str):
    # Prova cache prima
    cached = cache.get(url)
    if cached:
        print(f"✅ Cache hit: {url}")
        return cached
    
    # Scrape e cache
    print(f"📥 Scraping: {url}")
    response = requests.get(url)
    data = parse_response(response)
    cache.set(url, data)
    return data
```

---

## **6. Logging Strutturato**
```python
import logging
import json
from datetime import datetime

class StructuredLogger:
    """Logger JSON strutturato per analisi"""
    
    def __init__(self, log_file: str = 'scraper.jsonl'):
        self.log_file = log_file
    
    def log(self, event_type: str, url: str, **kwargs):
        """Log evento strutturato"""
        log_entry = {
            'timestamp': datetime.now().isoformat(),
            'event_type': event_type,
            'url': url,
            **kwargs
        }
        
        with open(self.log_file, 'a') as f:
            f.write(json.dumps(log_entry) + '\n')

# Uso
logger = StructuredLogger()

logger.log('scrape_start', url='https://...', comune='Firenze')
logger.log('scrape_success', url='https://...', atti_count=50, duration=2.5)
logger.log('scrape_error', url='https://...', error='timeout', retry_count=3)
```

---

# ⚖️ LEGALITÀ E COMPLIANCE

## **Scraping di Siti PA in Italia**

### **✅ È LEGALE?**

**SÌ**, con alcune precisazioni:

#### **Fondamenti Legali**
1. **D.lgs. 33/2013** (Trasparenza Amministrativa)
   - PA DEVE rendere accessibili i dati
   - Albi pretori sono **pubblici per legge**
   - Accesso ai dati è un **diritto**

2. **Codice Amministrazione Digitale (CAD)**
   - Art. 50: Disponibilità dei dati
   - Art. 52: Open data di default
   - Dati PA sono **riutilizzabili**

3. **GDPR** (2016/679/UE)
   - Dati personali in atti PA sono **già pubblici**
   - Trasparenza prevale su privacy (bilanciamento)
   - Rispettare finalità utilizzo

---

### **⚠️ COSA EVITARE**

```python
# ❌ VIETATO:
# 1. Sovraccaricare server PA (DDoS involontario)
# 2. Aggirare misure di sicurezza attive
# 3. Usare dati per finalità illecite
# 4. Violare GDPR (es: profilazione utenti PA)

# ✅ PERMESSO:
# 1. Scraping educato con rate limiting
# 2. Archiviazione dati pubblici
# 3. Analisi trasparenza amministrativa
# 4. Servizi citizen-centric
```

---

### **📋 Checklist Compliance**

```python
class ComplianceChecker:
    """Verifica compliance legale dello scraping"""
    
    @staticmethod
    def check_site(url: str) -> dict:
        """Verifica compliance per un sito"""
        checks = {
            'is_pa_site': False,
            'robots_txt_compliant': False,
            'rate_limiting_active': False,
            'user_agent_identified': False,
            'gdpr_compliant': False,
            'caching_enabled': False
        }
        
        # Check 1: È sito PA?
        checks['is_pa_site'] = any(
            domain in url 
            for domain in ['.gov.it', 'comune.', 'provincia.', '.regione.']
        )
        
        # Check 2: robots.txt
        checks['robots_txt_compliant'] = can_fetch(url)
        
        # Altri checks...
        
        return checks

# Uso
compliance = ComplianceChecker.check_site('https://www.comune.firenze.it/albo')
if not all(compliance.values()):
    print(f"⚠️ Compliance issues: {compliance}")
```

---

# 🔧 LIBRERIE PYTHON: CONFRONTO COMPLETO

## **Overview Librerie**

| Libreria | Tipo | Use Case | Difficoltà | Performance |
|----------|------|----------|------------|-------------|
| **requests** | HTTP client | API, HTML statico | 🟢 Facile | 🚀 Veloce |
| **httpx** | HTTP client async | API async | 🟢 Facile | 🚀 Velocissimo |
| **BeautifulSoup** | HTML parser | Parsing semplice | 🟢 Facile | 🟡 Media |
| **lxml** | HTML/XML parser | Parsing veloce | 🟡 Media | 🚀 Velocissimo |
| **Scrapy** | Framework | Progetti grandi | 🔴 Complessa | 🚀 Eccellente |
| **Selenium** | Browser automation | JS rendering | 🟡 Media | 🔴 Lento |
| **Playwright** | Browser automation | JS rendering moderno | 🟡 Media | 🟡 Accettabile |
| **Pyppeteer** | Browser automation | Puppeteer Python | 🔴 Complessa | 🟡 Accettabile |

---

## **1. requests + BeautifulSoup** (Current: Firenze)

### **✅ Quando Usare**
- Siti HTML statici
- No JavaScript rendering necessario
- Scraping semplice e veloce
- Prototipazione rapida

### **❌ Limitazioni**
- No JavaScript rendering
- No interazione complessa
- Gestione async manuale

### **Esempio Ottimizzato**
```python
import requests
from bs4 import BeautifulSoup
from requests.adapters import HTTPAdapter
from urllib3.util.retry import Retry

class OptimizedHTMLScraper:
    """Scraper ottimizzato con retry e pooling"""
    
    def __init__(self):
        self.session = self._create_session()
    
    def _create_session(self) -> requests.Session:
        """Crea session con retry automatico e connection pooling"""
        session = requests.Session()
        
        # Retry strategy
        retry = Retry(
            total=3,
            backoff_factor=1,  # 1s, 2s, 4s
            status_forcelist=[429, 500, 502, 503, 504],
            allowed_methods=["HEAD", "GET", "OPTIONS"]
        )
        
        adapter = HTTPAdapter(
            max_retries=retry,
            pool_connections=10,
            pool_maxsize=20
        )
        
        session.mount("http://", adapter)
        session.mount("https://", adapter)
        
        # Headers standard
        session.headers.update({
            'User-Agent': 'NatanBot/1.0',
            'Accept': 'text/html,application/xhtml+xml',
            'Accept-Language': 'it-IT,it;q=0.9',
            'Accept-Encoding': 'gzip, deflate',
            'Connection': 'keep-alive'
        })
        
        return session
    
    def scrape(self, url: str, timeout: int = 10) -> BeautifulSoup:
        """Scrape con gestione errori"""
        try:
            response = self.session.get(url, timeout=timeout)
            response.raise_for_status()
            
            # Parse con lxml (più veloce di html.parser)
            return BeautifulSoup(response.content, 'lxml')
        except requests.exceptions.Timeout:
            raise Exception(f"Timeout accessing {url}")
        except requests.exceptions.HTTPError as e:
            raise Exception(f"HTTP {e.response.status_code}: {url}")
        except Exception as e:
            raise Exception(f"Error scraping {url}: {e}")
```

---

## **2. httpx + Async** (Raccomandato per Scalabilità)

### **✅ Vantaggi**
- Async/await nativo
- HTTP/2 support
- Stessa API di requests
- Performance eccellente

### **Esempio Async**
```python
import asyncio
import httpx
from bs4 import BeautifulSoup

class AsyncHTMLScraper:
    """Scraper async con httpx"""
    
    def __init__(self, max_concurrent: int = 10):
        self.max_concurrent = max_concurrent
        self.semaphore = asyncio.Semaphore(max_concurrent)
    
    async def scrape_one(self, client: httpx.AsyncClient, url: str) -> dict:
        """Scrape singolo URL"""
        async with self.semaphore:  # Limit concurrency
            try:
                response = await client.get(url, timeout=10.0)
                response.raise_for_status()
                
                soup = BeautifulSoup(response.content, 'lxml')
                # Parse logic here...
                
                return {'url': url, 'status': 'success', 'data': ...}
            except Exception as e:
                return {'url': url, 'status': 'error', 'error': str(e)}
    
    async def scrape_many(self, urls: list[str]) -> list[dict]:
        """Scrape multipli URL in parallelo"""
        async with httpx.AsyncClient(
            headers={'User-Agent': 'NatanBot/1.0'},
            timeout=httpx.Timeout(10.0, connect=5.0),
            limits=httpx.Limits(max_keepalive_connections=20, max_connections=50)
        ) as client:
            tasks = [self.scrape_one(client, url) for url in urls]
            return await asyncio.gather(*tasks)

# Uso
async def main():
    scraper = AsyncHTMLScraper(max_concurrent=10)
    urls = ['https://comune1.it/albo', 'https://comune2.it/albo', ...]
    results = await scraper.scrape_many(urls)

asyncio.run(main())
```

**Performance**:
- 1 URL: ~same as requests
- 100 URLs: 10-20x più veloce
- 1000 URLs: 50-100x più veloce

---

## **3. Scrapy** (Per Progetti Grandi)

### **✅ Quando Usare**
- Scraping di migliaia di pagine
- Pipeline complesse (parse → validate → store)
- Scheduling automatico
- Progetti production-grade

### **❌ Limitazioni**
- Curva apprendimento ripida
- Overkill per progetti semplici
- No JavaScript rendering nativo

### **Esempio Base**
```python
import scrapy
from scrapy.crawler import CrawlerProcess

class AlboSpider(scrapy.Spider):
    name = 'albo_spider'
    
    custom_settings = {
        'CONCURRENT_REQUESTS': 8,
        'DOWNLOAD_DELAY': 1,
        'ROBOTSTXT_OBEY': True,
        'USER_AGENT': 'NatanBot/1.0'
    }
    
    def start_requests(self):
        urls = ['https://comune1.it/albo', 'https://comune2.it/albo']
        for url in urls:
            yield scrapy.Request(url, callback=self.parse)
    
    def parse(self, response):
        # Parse logic
        atti = response.css('.atto-card')
        for atto in atti:
            yield {
                'numero': atto.css('.numero::text').get(),
                'data': atto.css('.data::text').get(),
                'oggetto': atto.css('.oggetto::text').get(),
            }
        
        # Follow pagination
        next_page = response.css('a.next::attr(href)').get()
        if next_page:
            yield response.follow(next_page, self.parse)

# Run
process = CrawlerProcess()
process.crawl(AlboSpider)
process.start()
```

**Quando scegliere Scrapy**:
- ✅ Scraping > 1000 pagine
- ✅ Serve pipeline dati complessa
- ✅ Scheduling e monitoring built-in
- ❌ Overkill per 10-50 comuni

---

## **4. Playwright** (JS-Heavy Sites)

### **✅ Quando Usare**
- Contenuto caricato dinamicamente via JS
- SPA (Single Page Applications)
- Interazione complessa richiesta
- Vendor moderni ("Trasparenza VM", Bagno a Ripoli)

### **✅ Vantaggi vs Selenium**
- 3-5x più veloce
- API moderna async/await
- Multi-browser (Chromium, Firefox, WebKit)
- Network interception
- Screenshot e PDF generation

### **Esempio Ottimizzato**
```python
from playwright.async_api import async_playwright
import asyncio

class PlaywrightScraper:
    """Scraper con Playwright per siti JS-heavy"""
    
    async def scrape_js_site(self, url: str) -> dict:
        """Scrape sito con JavaScript rendering"""
        async with async_playwright() as p:
            # Launch browser
            browser = await p.chromium.launch(
                headless=True,
                args=[
                    '--disable-blink-features=AutomationControlled',
                    '--disable-dev-shm-usage',
                    '--no-sandbox'
                ]
            )
            
            # Create context with realistic settings
            context = await browser.new_context(
                user_agent='Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
                viewport={'width': 1920, 'height': 1080},
                locale='it-IT',
                timezone_id='Europe/Rome'
            )
            
            page = await context.new_page()
            
            try:
                # Navigate with wait
                await page.goto(url, wait_until='networkidle', timeout=30000)
                
                # Wait for specific content (adjust selector)
                await page.wait_for_selector('.atto-item', timeout=10000)
                
                # Extract data
                atti = await page.query_selector_all('.atto-item')
                results = []
                
                for atto in atti:
                    numero = await atto.query_selector('.numero')
                    data = await atto.query_selector('.data')
                    
                    results.append({
                        'numero': await numero.inner_text() if numero else None,
                        'data': await data.inner_text() if data else None,
                    })
                
                return {'status': 'success', 'data': results}
                
            except Exception as e:
                return {'status': 'error', 'error': str(e)}
            finally:
                await context.close()
                await browser.close()

# Uso
async def main():
    scraper = PlaywrightScraper()
    result = await scraper.scrape_js_site('https://livorno.trasparenza-valutazione-merito.it/...')

asyncio.run(main())
```

**Performance Tips**:
```python
# 1. Riusa browser context
browser = await p.chromium.launch()
context = await browser.new_context()

for url in urls:
    page = await context.new_page()
    # scrape...
    await page.close()

await context.close()
await browser.close()

# 2. Blocca risorse non necessarie
await context.route("**/*.{png,jpg,jpeg,gif,svg,css,font,woff,woff2}", lambda route: route.abort())

# 3. Use persistent context per cookies
context = await p.chromium.launch_persistent_context(
    user_data_dir='./browser_data',
    headless=True
)
```

---

## **Decisione Matrix**

```python
def choose_scraper(site_analysis: dict) -> str:
    """Scegli scraper ottimale basandosi su analisi sito"""
    
    # JavaScript-heavy?
    if site_analysis['script_tags'] > 50 or site_analysis['spa_detected']:
        return 'Playwright'
    
    # API disponibile?
    if site_analysis['api_endpoint']:
        return 'httpx_async'  # Veloce e efficiente
    
    # HTML statico semplice?
    if site_analysis['static_html'] and site_analysis['pages_count'] < 100:
        return 'requests_beautifulsoup'
    
    # Progetto grande (1000+ pagine)?
    if site_analysis['pages_count'] > 1000:
        return 'Scrapy'
    
    # Default: httpx async (buon compromesso)
    return 'httpx_async'
```

---

# 🎭 JAVASCRIPT RENDERING

## **Quando è Necessario**

### **Indicatori che richiedono JS rendering**:

```python
def requires_js_rendering(html: str, soup: BeautifulSoup) -> dict:
    """Analizza se serve JavaScript rendering"""
    
    indicators = {
        'requires_js': False,
        'confidence': 0.0,
        'reasons': []
    }
    
    # 1. Molti script tags
    script_count = len(soup.find_all('script'))
    if script_count > 30:
        indicators['reasons'].append(f'{script_count} script tags')
        indicators['confidence'] += 0.3
    
    # 2. Framework JS rilevati
    frameworks = ['react', 'vue', 'angular', 'ember']
    for fw in frameworks:
        if fw in html.lower():
            indicators['reasons'].append(f'{fw} framework detected')
            indicators['confidence'] += 0.4
            break
    
    # 3. Contenuto placeholder
    placeholders = [
        '<div id="root"></div>',
        '<div id="app"></div>',
        'Loading...',
        'Please enable JavaScript'
    ]
    for placeholder in placeholders:
        if placeholder in html:
            indicators['reasons'].append('Placeholder content detected')
            indicators['confidence'] += 0.3
            break
    
    # 4. Pochi contenuti visibili
    visible_text = soup.get_text(strip=True)
    if len(visible_text) < 500:
        indicators['reasons'].append('Little visible content')
        indicators['confidence'] += 0.2
    
    indicators['requires_js'] = indicators['confidence'] >= 0.5
    return indicators

# Test su "Trasparenza VM" comuni
html = requests.get('https://livorno.trasparenza-valutazione-merito.it/...').text
soup = BeautifulSoup(html, 'lxml')
analysis = requires_js_rendering(html, soup)

if analysis['requires_js']:
    print(f"⚠️ JS rendering required! Confidence: {analysis['confidence']}")
    print(f"Reasons: {', '.join(analysis['reasons'])}")
```

---

## **Playwright vs Selenium: Confronto Dettagliato**

| Feature | Playwright | Selenium |
|---------|-----------|----------|
| **Performance** | 🚀 3-5x più veloce | 🐌 Lento |
| **API** | ✅ Async/await moderna | ❌ Sincrona legacy |
| **Browser support** | ✅ Chromium, Firefox, WebKit | ✅ Chrome, Firefox, Safari, Edge |
| **Setup** | ✅ Facile (`playwright install`) | ⚠️ Driver management manuale |
| **Network interception** | ✅ Built-in | ❌ Richiede proxy |
| **Auto-wait** | ✅ Smart waiting | ⚠️ Explicit waits manuali |
| **Screenshot/PDF** | ✅ Built-in | ⚠️ Limitato |
| **Community** | 🟡 Growing | 🟢 Mature |
| **Documentazione** | ✅ Eccellente | ✅ Molto estesa |

---

### **Playwright: Deep Dive**

**Installazione**:
```bash
pip install playwright
playwright install chromium  # Only Chromium for our case
```

**Features Avanzate**:

#### **1. Network Interception (API Discovery)**
```python
async def intercept_api_calls(url: str) -> list[dict]:
    """Intercetta chiamate API per reverse engineering"""
    api_calls = []
    
    async with async_playwright() as p:
        browser = await p.chromium.launch()
        page = await browser.new_page()
        
        # Intercept network requests
        async def handle_request(request):
            if 'api' in request.url or request.resource_type == 'xhr':
                api_calls.append({
                    'url': request.url,
                    'method': request.method,
                    'headers': request.headers,
                    'post_data': request.post_data
                })
        
        page.on('request', handle_request)
        
        await page.goto(url, wait_until='networkidle')
        await browser.close()
    
    return api_calls

# Uso per "Trasparenza VM"
api_calls = await intercept_api_calls('https://livorno.trasparenza-valutazione-merito.it/...')
for call in api_calls:
    print(f"API Found: {call['method']} {call['url']}")
```

#### **2. Stealth Mode (Anti-Detection)**
```python
async def create_stealth_browser():
    """Browser con anti-detection"""
    async with async_playwright() as p:
        browser = await p.chromium.launch(
            headless=True,
            args=[
                '--disable-blink-features=AutomationControlled',
                '--disable-dev-shm-usage',
                '--disable-setuid-sandbox',
                '--no-sandbox',
                '--disable-web-security',
                '--disable-features=IsolateOrigins,site-per-process'
            ]
        )
        
        context = await browser.new_context(
            user_agent='Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            viewport={'width': 1920, 'height': 1080},
            locale='it-IT',
            timezone_id='Europe/Rome',
            geolocation={'latitude': 43.7696, 'longitude': 11.2558},  # Florence
            permissions=['geolocation'],
            extra_http_headers={
                'Accept-Language': 'it-IT,it;q=0.9,en-US;q=0.8,en;q=0.7'
            }
        )
        
        # Remove webdriver flag
        await context.add_init_script("""
            Object.defineProperty(navigator, 'webdriver', {
                get: () => undefined
            });
        """)
        
        return browser, context
```

#### **3. Resource Blocking (Performance)**
```python
async def scrape_fast_no_media(url: str):
    """Scrape veloce bloccando media"""
    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        context = await browser.new_context()
        
        # Block images, fonts, stylesheets
        await context.route(
            "**/*.{png,jpg,jpeg,gif,svg,css,woff,woff2,ttf,eot}",
            lambda route: route.abort()
        )
        
        page = await context.new_page()
        await page.goto(url, wait_until='domcontentloaded')
        
        # Extract data (only HTML/JS loaded, much faster)
        content = await page.content()
        
        await browser.close()
        return content
```

---

## **Selenium: Quando Usarlo**

✅ **Usa Selenium se**:
- Hai già codebase Selenium esistente
- Serve compatibilità con browser specifici (IE, Edge legacy)
- Team ha expertise Selenium consolidata

❌ **Non usare per nuovi progetti**: Playwright è superiore

---

# 🕵️ ANTI-DETECTION STRATEGIES

## **Livelli di Protezione Siti**

```python
from enum import Enum

class ProtectionLevel(Enum):
    """Livelli di protezione anti-scraping"""
    NONE = 0          # Nessuna protezione (molti siti PA)
    BASIC = 1         # Rate limiting base
    MODERATE = 2      # User-Agent check, simple blocking
    ADVANCED = 3      # Browser fingerprinting, CAPTCHA
    EXTREME = 4       # Cloudflare, DataDome, PerimeterX
```

### **Analisi Siti PA Toscani**

**Trovate finora**:
- 🟢 **Comuni Drupal/WordPress**: NONE-BASIC (facili)
- 🟡 **Trasparenza VM vendor**: MODERATE (serve Playwright)
- 🟡 **URBI vendor** (Massa): MODERATE-ADVANCED
- 🟢 **API pubbliche** (Firenze): NONE

---

## **1. Headers Intelligenti**

```python
class SmartHeaders:
    """Genera headers realistici"""
    
    # User agents realistici (rotazione)
    USER_AGENTS = [
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64; rv:121.0) Gecko/20100101 Firefox/121.0',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0',
    ]
    
    @staticmethod
    def get_realistic_headers(url: str) -> dict:
        """Genera headers realistici per un URL"""
        import random
        from urllib.parse import urlparse
        
        domain = urlparse(url).netloc
        
        return {
            'User-Agent': random.choice(SmartHeaders.USER_AGENTS),
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language': 'it-IT,it;q=0.9,en-US;q=0.8,en;q=0.7',
            'Accept-Encoding': 'gzip, deflate, br',
            'DNT': '1',
            'Connection': 'keep-alive',
            'Upgrade-Insecure-Requests': '1',
            'Sec-Fetch-Dest': 'document',
            'Sec-Fetch-Mode': 'navigate',
            'Sec-Fetch-Site': 'none',
            'Sec-Fetch-User': '?1',
            'Cache-Control': 'max-age=0',
            'Referer': f'https://{domain}/',  # Simula navigazione interna
        }

# Uso
headers = SmartHeaders.get_realistic_headers('https://comune.firenze.it/albo')
response = requests.get(url, headers=headers)
```

---

## **2. Session Management & Cookies**

```python
class SessionManager:
    """Gestione sessioni persistenti con cookies"""
    
    def __init__(self, cookies_file: str = 'cookies.json'):
        self.cookies_file = Path(cookies_file)
        self.session = requests.Session()
        self.load_cookies()
    
    def load_cookies(self):
        """Carica cookies salvati"""
        if self.cookies_file.exists():
            with open(self.cookies_file) as f:
                cookies = json.load(f)
                self.session.cookies.update(cookies)
    
    def save_cookies(self):
        """Salva cookies per riuso"""
        with open(self.cookies_file, 'w') as f:
            json.dump(dict(self.session.cookies), f)
    
    def get_with_session(self, url: str) -> requests.Response:
        """GET con sessione persistente"""
        response = self.session.get(url, headers=SmartHeaders.get_realistic_headers(url))
        self.save_cookies()  # Salva cookies aggiornati
        return response

# Uso
manager = SessionManager()
response = manager.get_with_session('https://...')
```

---

## **3. Proxy Rotation (Per Siti Protetti)**

⚠️ **Per PA Italia**: Probabilmente NON necessario (siti poco protetti)

```python
class ProxyRotator:
    """Rotazione proxy per evitare IP ban"""
    
    def __init__(self, proxies: list[str]):
        self.proxies = proxies
        self.current_index = 0
    
    def get_next_proxy(self) -> dict:
        """Ottieni prossimo proxy"""
        proxy = self.proxies[self.current_index]
        self.current_index = (self.current_index + 1) % len(self.proxies)
        
        return {
            'http': proxy,
            'https': proxy
        }
    
    def scrape_with_rotation(self, url: str) -> requests.Response:
        """Scrape con proxy rotation"""
        max_retries = len(self.proxies)
        
        for _ in range(max_retries):
            proxy = self.get_next_proxy()
            try:
                response = requests.get(
                    url,
                    proxies=proxy,
                    headers=SmartHeaders.get_realistic_headers(url),
                    timeout=10
                )
                if response.status_code == 200:
                    return response
            except:
                continue  # Prova prossimo proxy
        
        raise Exception(f"All proxies failed for {url}")

# Setup (se necessario)
# proxies = [
#     'http://proxy1.example.com:8080',
#     'http://proxy2.example.com:8080',
# ]
# rotator = ProxyRotator(proxies)
```

**Proxy gratuiti affidabili**:
- ❌ Proxy pubblici: NON affidabili (lenti, insicuri)
- ✅ Tor: Possibile ma lento e etico?
- ✅ Datacenter proxies: Economici (~$1-5/GB)
- ✅ Residential proxies: Costosi (~$15-30/GB) ma più affidabili

**Per PA Italia**: Probabilmente sufficiente rotazione User-Agent + rate limiting

---

## **4. Browser Fingerprinting Evasion**

```python
async def create_undetectable_browser():
    """Browser Playwright con evasione fingerprinting"""
    async with async_playwright() as p:
        browser = await p.chromium.launch(
            headless=True,
            args=[
                '--disable-blink-features=AutomationControlled',
                '--disable-dev-shm-usage',
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-infobars',
                '--window-size=1920,1080',
                '--disable-extensions',
                '--disable-gpu',
                '--disable-dev-tools',
            ]
        )
        
        context = await browser.new_context(
            viewport={'width': 1920, 'height': 1080},
            user_agent='Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            locale='it-IT',
            timezone_id='Europe/Rome',
        )
        
        # Anti-fingerprinting scripts
        await context.add_init_script("""
            // Remove webdriver property
            Object.defineProperty(navigator, 'webdriver', {
                get: () => undefined,
            });
            
            // Mock plugins
            Object.defineProperty(navigator, 'plugins', {
                get: () => [1, 2, 3, 4, 5],
            });
            
            // Mock languages
            Object.defineProperty(navigator, 'languages', {
                get: () => ['it-IT', 'it', 'en-US', 'en'],
            });
            
            // Mock chrome object
            window.chrome = {
                runtime: {},
            };
            
            // Mock permissions
            const originalQuery = window.navigator.permissions.query;
            window.navigator.permissions.query = (parameters) => (
                parameters.name === 'notifications' ?
                    Promise.resolve({ state: Notification.permission }) :
                    originalQuery(parameters)
            );
        """)
        
        return browser, context

# Uso
browser, context = await create_undetectable_browser()
page = await context.new_page()
await page.goto('https://...')
```

---

## **5. CAPTCHA Handling**

⚠️ **Per PA Italia**: Raramente presente

**Strategie se incontrato**:

```python
# Opzione 1: Playwright con pausa manuale
async def handle_captcha_manual(page):
    """Pausa per risoluzione CAPTCHA manuale"""
    # Detect CAPTCHA
    captcha_present = await page.query_selector('iframe[src*="recaptcha"]')
    
    if captcha_present:
        print("⚠️ CAPTCHA detected! Please solve manually...")
        print(f"URL: {page.url}")
        
        # Lancia browser visibile
        await page.pause()  # Debug mode
        
        # Aspetta risoluzione
        await page.wait_for_selector('.captcha-success', timeout=120000)

# Opzione 2: 2Captcha API (servizio a pagamento)
import requests

def solve_recaptcha(site_key: str, page_url: str, api_key: str) -> str:
    """Risolvi reCAPTCHA con servizio 2Captcha"""
    # Submit CAPTCHA
    response = requests.post('http://2captcha.com/in.php', data={
        'key': api_key,
        'method': 'userrecaptcha',
        'googlekey': site_key,
        'pageurl': page_url,
    })
    
    captcha_id = response.text.split('|')[1]
    
    # Poll for solution
    import time
    for _ in range(60):  # Max 2 min
        time.sleep(2)
        result = requests.get(f'http://2captcha.com/res.php?key={api_key}&action=get&id={captcha_id}')
        if 'OK|' in result.text:
            return result.text.split('|')[1]  # Return token
    
    raise Exception("CAPTCHA solving timeout")

# Costo: ~$1-3 per 1000 CAPTCHAs
```

**Raccomandazione PA Italia**: Se CAPTCHA presente, contattare PA per API o accesso automatizzato (diritto trasparenza)

---

# ⏱️ RATE LIMITING & POLITENESS

## **Strategia Multi-Livello**

```python
from dataclasses import dataclass
from datetime import datetime, timedelta
import time
import asyncio

@dataclass
class RateLimitConfig:
    """Configurazione rate limiting per tipo sito"""
    min_delay: float  # Secondi tra richieste
    max_delay: float  # Massimo delay se server lento
    burst_size: int   # Richieste in burst
    burst_delay: float  # Delay dopo burst
    daily_limit: int | None  # Limite giornaliero (None = unlimited)

class AdaptiveRateLimiter:
    """Rate limiter che si adatta a condizioni server"""
    
    # Preset per diversi scenari
    PRESETS = {
        'pa_gentle': RateLimitConfig(
            min_delay=2.0,
            max_delay=10.0,
            burst_size=5,
            burst_delay=10.0,
            daily_limit=10000
        ),
        'pa_moderate': RateLimitConfig(
            min_delay=1.0,
            max_delay=5.0,
            burst_size=10,
            burst_delay=5.0,
            daily_limit=50000
        ),
        'pa_aggressive': RateLimitConfig(
            min_delay=0.5,
            max_delay=3.0,
            burst_size=20,
            burst_delay=3.0,
            daily_limit=None
        ),
        'api_endpoint': RateLimitConfig(
            min_delay=0.1,
            max_delay=1.0,
            burst_size=50,
            burst_delay=1.0,
            daily_limit=None
        ),
    }
    
    def __init__(self, config_name: str = 'pa_moderate'):
        self.config = self.PRESETS[config_name]
        self.current_delay = self.config.min_delay
        self.request_count = 0
        self.burst_count = 0
        self.last_request_time = None
        self.daily_count = 0
        self.daily_reset_time = datetime.now() + timedelta(days=1)
        self.error_count = 0
    
    async def wait(self):
        """Aspetta prima della prossima richiesta (async)"""
        # Reset daily counter
        if datetime.now() > self.daily_reset_time:
            self.daily_count = 0
            self.daily_reset_time = datetime.now() + timedelta(days=1)
        
        # Check daily limit
        if self.config.daily_limit and self.daily_count >= self.config.daily_limit:
            wait_seconds = (self.daily_reset_time - datetime.now()).total_seconds()
            print(f"⏸️ Daily limit reached. Waiting {wait_seconds:.0f}s until reset...")
            await asyncio.sleep(wait_seconds)
            self.daily_count = 0
        
        # Burst management
        if self.burst_count >= self.config.burst_size:
            print(f"⏸️ Burst limit reached. Cooling down {self.config.burst_delay}s...")
            await asyncio.sleep(self.config.burst_delay)
            self.burst_count = 0
        
        # Standard wait
        if self.last_request_time:
            elapsed = time.time() - self.last_request_time
            sleep_time = max(0, self.current_delay - elapsed)
            if sleep_time > 0:
                await asyncio.sleep(sleep_time)
        
        self.last_request_time = time.time()
        self.request_count += 1
        self.burst_count += 1
        self.daily_count += 1
    
    def adjust_for_response(self, response_time: float, status_code: int):
        """Adatta delay basandosi su risposta server"""
        if status_code == 429:  # Too Many Requests
            self.current_delay = min(self.current_delay * 3, self.config.max_delay)
            self.error_count += 1
            print(f"⚠️ Rate limit hit! Slowing down to {self.current_delay}s")
        
        elif status_code >= 500:  # Server errors
            self.current_delay = min(self.current_delay * 2, self.config.max_delay)
            self.error_count += 1
            print(f"⚠️ Server error. Slowing down to {self.current_delay}s")
        
        elif response_time > 5.0:  # Server molto lento
            self.current_delay = min(self.current_delay * 1.5, self.config.max_delay)
            print(f"🐌 Slow server. Adjusting to {self.current_delay}s")
        
        elif status_code == 200 and response_time < 1.0:  # Server veloce
            # Gradualmente riduci delay se tutto OK
            if self.error_count == 0:
                self.current_delay = max(
                    self.current_delay * 0.95,
                    self.config.min_delay
                )
    
    def get_stats(self) -> dict:
        """Statistiche rate limiting"""
        return {
            'total_requests': self.request_count,
            'daily_requests': self.daily_count,
            'current_delay': self.current_delay,
            'error_count': self.error_count,
            'avg_rps': self.request_count / (time.time() - self.last_request_time) if self.last_request_time else 0
        }

# Uso
limiter = AdaptiveRateLimiter('pa_moderate')

for url in urls:
    await limiter.wait()
    start = time.time()
    response = await client.get(url)
    response_time = time.time() - start
    limiter.adjust_for_response(response_time, response.status_code)

print(limiter.get_stats())
```

---

## **Orari Intelligenti**

```python
from datetime import datetime, time as dt_time

class SmartScheduler:
    """Scheduler che rispetta orari ufficio PA"""
    
    # Orari peak PA (evita questi)
    PA_PEAK_HOURS = [
        (dt_time(9, 0), dt_time(12, 30)),   # Mattina
        (dt_time(14, 0), dt_time(17, 30)),  # Pomeriggio
    ]
    
    @staticmethod
    def is_peak_hours() -> bool:
        """Verifica se siamo in orario di punta"""
        now = datetime.now()
        
        # Weekend: mai peak
        if now.weekday() >= 5:
            return False
        
        current_time = now.time()
        
        for start, end in SmartScheduler.PA_PEAK_HOURS:
            if start <= current_time <= end:
                return True
        
        return False
    
    @staticmethod
    def get_recommended_delay() -> float:
        """Delay raccomandato basato su orario"""
        if SmartScheduler.is_peak_hours():
            return 3.0  # Più educato in peak hours
        else:
            return 1.0  # Più veloce off-peak
    
    @staticmethod
    async def wait_until_good_time(max_wait_minutes: int = 60):
        """Aspetta fino a orario non-peak"""
        if not SmartScheduler.is_peak_hours():
            return  # Already good time
        
        # Calcola prossimo orario buono
        now = datetime.now()
        
        # Se mattina (9-12:30), aspetta fino alle 12:31
        if 9 <= now.hour < 12 or (now.hour == 12 and now.minute <= 30):
            next_good = now.replace(hour=12, minute=31, second=0)
        # Se pomeriggio (14-17:30), aspetta fino alle 17:31
        elif 14 <= now.hour < 17 or (now.hour == 17 and now.minute <= 30):
            next_good = now.replace(hour=17, minute=31, second=0)
        else:
            return  # Già fuori peak
        
        wait_seconds = (next_good - now).total_seconds()
        
        if wait_seconds > max_wait_minutes * 60:
            print(f"⏰ Peak hours. Would wait {wait_seconds/60:.1f} min, but max is {max_wait_minutes} min. Proceeding slowly...")
            return
        
        print(f"⏰ Peak hours. Waiting {wait_seconds/60:.1f} minutes until off-peak...")
        await asyncio.sleep(wait_seconds)

# Uso
scheduler = SmartScheduler()

# Opzione 1: Adatta delay
delay = scheduler.get_recommended_delay()
limiter = AdaptiveRateLimiter('pa_moderate')
limiter.config.min_delay = delay

# Opzione 2: Aspetta orario migliore
await scheduler.wait_until_good_time(max_wait_minutes=30)
```

---

# 🔥 ERROR HANDLING ROBUSTO

## **Gerarchia Errori**

```python
class ScraperException(Exception):
    """Base exception per scraper"""
    pass

class NetworkError(ScraperException):
    """Errori di rete (timeout, connection refused)"""
    def __init__(self, url: str, original_error: Exception):
        self.url = url
        self.original_error = original_error
        super().__init__(f"Network error accessing {url}: {original_error}")

class ParsingError(ScraperException):
    """Errori di parsing HTML/JSON"""
    def __init__(self, url: str, reason: str):
        self.url = url
        self.reason = reason
        super().__init__(f"Parsing error for {url}: {reason}")

class RateLimitError(ScraperException):
    """Rate limit raggiunto"""
    def __init__(self, url: str, retry_after: int | None = None):
        self.url = url
        self.retry_after = retry_after
        super().__init__(f"Rate limited: {url}" + (f" (retry after {retry_after}s)" if retry_after else ""))

class AuthenticationError(ScraperException):
    """Errore autenticazione (401, 403)"""
    def __init__(self, url: str, status_code: int):
        self.url = url
        self.status_code = status_code
        super().__init__(f"Authentication error {status_code}: {url}")

class ContentChangedError(ScraperException):
    """Struttura sito cambiata"""
    def __init__(self, url: str, expected: str, found: str):
        self.url = url
        self.expected = expected
        self.found = found
        super().__init__(f"Content structure changed for {url}: expected '{expected}', found '{found}'")
```

---

## **Retry Strategy Avanzata**

```python
from typing import Callable, TypeVar, Any
import asyncio
from functools import wraps

T = TypeVar('T')

class RetryStrategy:
    """Strategia retry con backoff esponenziale"""
    
    def __init__(
        self,
        max_retries: int = 3,
        base_delay: float = 1.0,
        max_delay: float = 60.0,
        exponential_base: float = 2.0,
        jitter: bool = True
    ):
        self.max_retries = max_retries
        self.base_delay = base_delay
        self.max_delay = max_delay
        self.exponential_base = exponential_base
        self.jitter = jitter
    
    def get_delay(self, attempt: int) -> float:
        """Calcola delay per tentativo N"""
        import random
        
        # Exponential backoff: delay = base * (2 ^ attempt)
        delay = min(
            self.base_delay * (self.exponential_base ** attempt),
            self.max_delay
        )
        
        # Add jitter per evitare thundering herd
        if self.jitter:
            delay *= (0.5 + random.random())  # ±50% jitter
        
        return delay
    
    async def execute_with_retry(
        self,
        func: Callable[..., T],
        *args,
        **kwargs
    ) -> T:
        """Esegui funzione con retry automatico"""
        last_exception = None
        
        for attempt in range(self.max_retries + 1):
            try:
                # Try execute
                if asyncio.iscoroutinefunction(func):
                    return await func(*args, **kwargs)
                else:
                    return func(*args, **kwargs)
            
            except RateLimitError as e:
                # Rate limit: respect retry-after
                if e.retry_after:
                    print(f"⏳ Rate limited. Waiting {e.retry_after}s...")
                    await asyncio.sleep(e.retry_after)
                last_exception = e
            
            except NetworkError as e:
                # Network error: retry with backoff
                last_exception = e
                if attempt < self.max_retries:
                    delay = self.get_delay(attempt)
                    print(f"🔄 Network error (attempt {attempt + 1}/{self.max_retries + 1}). Retrying in {delay:.1f}s...")
                    await asyncio.sleep(delay)
            
            except AuthenticationError as e:
                # Auth error: don't retry
                raise e
            
            except Exception as e:
                # Other errors: retry
                last_exception = e
                if attempt < self.max_retries:
                    delay = self.get_delay(attempt)
                    print(f"⚠️ Error (attempt {attempt + 1}/{self.max_retries + 1}): {e}. Retrying in {delay:.1f}s...")
                    await asyncio.sleep(delay)
        
        # All retries failed
        raise last_exception

# Decorator per retry automatico
def with_retry(max_retries: int = 3, base_delay: float = 1.0):
    """Decorator per retry automatico"""
    def decorator(func):
        @wraps(func)
        async def wrapper(*args, **kwargs):
            strategy = RetryStrategy(max_retries=max_retries, base_delay=base_delay)
            return await strategy.execute_with_retry(func, *args, **kwargs)
        return wrapper
    return decorator

# Uso
@with_retry(max_retries=3, base_delay=2.0)
async def scrape_page(url: str) -> dict:
    """Scrape con retry automatico"""
    async with httpx.AsyncClient() as client:
        response = await client.get(url, timeout=10.0)
        
        if response.status_code == 429:
            retry_after = int(response.headers.get('Retry-After', 60))
            raise RateLimitError(url, retry_after)
        
        if response.status_code >= 500:
            raise NetworkError(url, Exception(f"HTTP {response.status_code}"))
        
        response.raise_for_status()
        return parse_response(response)

# Chiama normalmente, retry è automatico
result = await scrape_page('https://...')
```

---

## **Circuit Breaker Pattern**

```python
from enum import Enum
from datetime import datetime, timedelta

class CircuitState(Enum):
    CLOSED = "closed"      # Normale funzionamento
    OPEN = "open"          # Troppe failure, stop requests
    HALF_OPEN = "half_open"  # Test se recuperato

class CircuitBreaker:
    """Circuit breaker per proteggere da siti down"""
    
    def __init__(
        self,
        failure_threshold: int = 5,
        recovery_timeout: int = 60,
        success_threshold: int = 2
    ):
        self.failure_threshold = failure_threshold
        self.recovery_timeout = recovery_timeout
        self.success_threshold = success_threshold
        
        self.state = CircuitState.CLOSED
        self.failure_count = 0
        self.success_count = 0
        self.last_failure_time = None
    
    def can_execute(self) -> bool:
        """Verifica se possiamo eseguire richiesta"""
        if self.state == CircuitState.CLOSED:
            return True
        
        if self.state == CircuitState.OPEN:
            # Check if recovery timeout passed
            if self.last_failure_time:
                elapsed = (datetime.now() - self.last_failure_time).total_seconds()
                if elapsed >= self.recovery_timeout:
                    print(f"🔄 Circuit breaker: trying HALF_OPEN after {elapsed:.0f}s")
                    self.state = CircuitState.HALF_OPEN
                    return True
            return False
        
        # HALF_OPEN: allow request to test
        return True
    
    def record_success(self):
        """Registra successo"""
        if self.state == CircuitState.HALF_OPEN:
            self.success_count += 1
            if self.success_count >= self.success_threshold:
                print("✅ Circuit breaker: CLOSED (recovered)")
                self.state = CircuitState.CLOSED
                self.failure_count = 0
                self.success_count = 0
        elif self.state == CircuitState.CLOSED:
            self.failure_count = 0  # Reset on success
    
    def record_failure(self):
        """Registra failure"""
        self.failure_count += 1
        self.last_failure_time = datetime.now()
        
        if self.state == CircuitState.HALF_OPEN:
            print("⚠️ Circuit breaker: OPEN (still failing)")
            self.state = CircuitState.OPEN
            self.success_count = 0
        
        elif self.failure_count >= self.failure_threshold:
            print(f"🚨 Circuit breaker: OPEN ({self.failure_count} failures)")
            self.state = CircuitState.OPEN

# Uso per gestire comuni con problemi
class ComuneScraperWithCircuitBreaker:
    def __init__(self):
        self.circuit_breakers = {}  # comune_code -> CircuitBreaker
    
    async def scrape_comune(self, comune_code: str, url: str) -> dict:
        """Scrape con circuit breaker per comune"""
        # Get or create circuit breaker
        if comune_code not in self.circuit_breakers:
            self.circuit_breakers[comune_code] = CircuitBreaker(
                failure_threshold=5,
                recovery_timeout=300,  # 5 min
                success_threshold=2
            )
        
        cb = self.circuit_breakers[comune_code]
        
        # Check if can execute
        if not cb.can_execute():
            print(f"⛔ Circuit breaker OPEN for {comune_code}. Skipping...")
            return {'status': 'skipped', 'reason': 'circuit_breaker_open'}
        
        # Try scrape
        try:
            result = await scrape_page(url)
            cb.record_success()
            return {'status': 'success', 'data': result}
        except Exception as e:
            cb.record_failure()
            return {'status': 'error', 'error': str(e)}
```

---

# 🏗️ ARCHITETTURA SCALABILE

## **Design Pattern: Base Class + Platform Scrapers**

```python
from abc import ABC, abstractmethod
from dataclasses import dataclass
from typing import List, Dict, Any, Optional
from datetime import datetime

@dataclass
class AttoPA:
    """Struttura standardizzata atto PA"""
    numero: str
    data_pubblicazione: datetime
    data_scadenza: Optional[datetime]
    oggetto: str
    tipo_atto: str  # delibera, determinazione, ordinanza, etc.
    url_dettaglio: str
    url_pdf: Optional[str]
    comune_code: str
    tenant_id: str
    metadata: Dict[str, Any]  # Dati specifici piattaforma

@dataclass
class ScrapeResult:
    """Risultato scraping"""
    status: str  # 'success', 'error', 'partial'
    atti: List[AttoPA]
    errors: List[str]
    stats: Dict[str, Any]

class BaseAlboScraper(ABC):
    """Base class astratta per tutti gli scrapers"""
    
    def __init__(
        self,
        comune_code: str,
        tenant_id: str,
        config: Dict[str, Any] = None
    ):
        self.comune_code = comune_code
        self.tenant_id = tenant_id
        self.config = config or {}
        self.rate_limiter = AdaptiveRateLimiter(
            self.config.get('rate_limit_preset', 'pa_moderate')
        )
        self.circuit_breaker = CircuitBreaker()
        self.logger = StructuredLogger(f'logs/{comune_code}.jsonl')
    
    @abstractmethod
    async def detect_platform(self, url: str) -> bool:
        """
        Verifica se questo scraper può gestire l'URL
        Return: True se compatibile
        """
        pass
    
    @abstractmethod
    async def scrape_page(self, url: str, page_num: int = 1) -> List[AttoPA]:
        """
        Scrape singola pagina
        Return: Lista atti trovati
        """
        pass
    
    @abstractmethod
    async def get_total_pages(self, url: str) -> int:
        """
        Determina numero totale pagine
        Return: Numero pagine
        """
        pass
    
    async def scrape_all(
        self,
        start_url: str,
        max_pages: Optional[int] = None
    ) -> ScrapeResult:
        """
        Scrape completo (metodo comune a tutti)
        """
        all_atti = []
        errors = []
        start_time = datetime.now()
        
        try:
            # Detect compatibility
            if not await self.detect_platform(start_url):
                return ScrapeResult(
                    status='error',
                    atti=[],
                    errors=['Platform not compatible'],
                    stats={}
                )
            
            # Get total pages
            total_pages = await self.get_total_pages(start_url)
            if max_pages:
                total_pages = min(total_pages, max_pages)
            
            self.logger.log('scrape_start', url=start_url, total_pages=total_pages)
            
            # Scrape each page
            for page_num in range(1, total_pages + 1):
                if not self.circuit_breaker.can_execute():
                    errors.append(f"Circuit breaker open at page {page_num}")
                    break
                
                try:
                    await self.rate_limiter.wait()
                    atti = await self.scrape_page(start_url, page_num)
                    all_atti.extend(atti)
                    self.circuit_breaker.record_success()
                    
                    self.logger.log(
                        'page_scraped',
                        url=start_url,
                        page=page_num,
                        atti_count=len(atti)
                    )
                
                except Exception as e:
                    error_msg = f"Error scraping page {page_num}: {e}"
                    errors.append(error_msg)
                    self.circuit_breaker.record_failure()
                    self.logger.log(
                        'page_error',
                        url=start_url,
                        page=page_num,
                        error=str(e)
                    )
            
            duration = (datetime.now() - start_time).total_seconds()
            status = 'success' if not errors else ('partial' if all_atti else 'error')
            
            self.logger.log(
                'scrape_complete',
                url=start_url,
                status=status,
                atti_count=len(all_atti),
                duration=duration
            )
            
            return ScrapeResult(
                status=status,
                atti=all_atti,
                errors=errors,
                stats={
                    'total_atti': len(all_atti),
                    'total_pages': page_num,
                    'duration_seconds': duration,
                    'rate_limiter_stats': self.rate_limiter.get_stats()
                }
            )
        
        except Exception as e:
            self.logger.log('scrape_fatal_error', url=start_url, error=str(e))
            return ScrapeResult(
                status='error',
                atti=all_atti,
                errors=[str(e)],
                stats={}
            )
    
    async def save_to_mongodb(self, atti: List[AttoPA]):
        """Salva atti in MongoDB (metodo comune)"""
        # Usa PAActMongoDBImporter esistente
        from python_ai_service.app.services.pa_act_mongodb_importer import PAActMongoDBImporter
        
        importer = PAActMongoDBImporter(tenant_id=self.tenant_id)
        
        for atto in atti:
            await importer.import_atto(
                numero=atto.numero,
                data_pubblicazione=atto.data_pubblicazione,
                oggetto=atto.oggetto,
                tipo_atto=atto.tipo_atto,
                url_pdf=atto.url_pdf,
                metadata=atto.metadata
            )
```

---

## **Platform-Specific Scrapers**

### **1. DrupalAlboScraper** (Empoli, Prato, Scandicci)

```python
class DrupalAlboScraper(BaseAlboScraper):
    """Scraper per siti Drupal (3 comuni Toscana)"""
    
    DRUPAL_SIGNATURES = [
        'Drupal',
        '/sites/default/',
        'drupal.js',
        'node/\\d+'
    ]
    
    async def detect_platform(self, url: str) -> bool:
        """Detect se è Drupal"""
        async with httpx.AsyncClient() as client:
            try:
                response = await client.get(url, timeout=10.0)
                html = response.text
                
                return any(sig in html for sig in self.DRUPAL_SIGNATURES)
            except:
                return False
    
    async def scrape_page(self, url: str, page_num: int = 1) -> List[AttoPA]:
        """Scrape pagina Drupal"""
        page_url = f"{url}?page={page_num - 1}"  # Drupal: page=0 based
        
        async with httpx.AsyncClient() as client:
            response = await client.get(
                page_url,
                headers=SmartHeaders.get_realistic_headers(page_url),
                timeout=15.0
            )
            response.raise_for_status()
            
            soup = BeautifulSoup(response.content, 'lxml')
            
            # Drupal usa views-row
            atti_elements = soup.select('.views-row, .node-albo-pretorio')
            
            atti = []
            for elem in atti_elements:
                try:
                    atto = self._parse_drupal_atto(elem, url)
                    if atto:
                        atti.append(atto)
                except Exception as e:
                    self.logger.log('parse_error', url=page_url, error=str(e))
            
            return atti
    
    def _parse_drupal_atto(self, elem, base_url: str) -> Optional[AttoPA]:
        """Parse singolo atto Drupal"""
        # Selettori comuni Drupal
        numero_elem = elem.select_one('.field-name-field-numero, .numero-atto')
        data_elem = elem.select_one('.field-name-field-data, .data-pubblicazione')
        oggetto_elem = elem.select_one('.field-name-title, .node-title, h3 a')
        
        if not numero_elem or not oggetto_elem:
            return None
        
        # Parse data
        data_text = data_elem.get_text(strip=True) if data_elem else None
        data_pubblicazione = self._parse_italian_date(data_text) if data_text else datetime.now()
        
        # URL dettaglio
        link = oggetto_elem.get('href') if oggetto_elem.name == 'a' else oggetto_elem.select_one('a')
        url_dettaglio = urljoin(base_url, link['href']) if link else base_url
        
        return AttoPA(
            numero=numero_elem.get_text(strip=True),
            data_pubblicazione=data_pubblicazione,
            data_scadenza=None,
            oggetto=oggetto_elem.get_text(strip=True),
            tipo_atto='atto',  # Drupal spesso non specifica tipo
            url_dettaglio=url_dettaglio,
            url_pdf=None,  # Ottenere da dettaglio
            comune_code=self.comune_code,
            tenant_id=self.tenant_id,
            metadata={'platform': 'drupal'}
        )
    
    async def get_total_pages(self, url: str) -> int:
        """Get total pages Drupal"""
        async with httpx.AsyncClient() as client:
            response = await client.get(url, timeout=10.0)
            soup = BeautifulSoup(response.content, 'lxml')
            
            # Drupal pager
            pager = soup.select('.pager__item')
            if not pager:
                return 1
            
            # Last page number
            last_page = pager[-1].get_text(strip=True)
            try:
                return int(last_page)
            except:
                return 1
    
    @staticmethod
    def _parse_italian_date(date_str: str) -> datetime:
        """Parse date italiana (es: '15/01/2024')"""
        from dateutil import parser
        try:
            return parser.parse(date_str, dayfirst=True)
        except:
            return datetime.now()
```

---

### **2. TrasparenzaVMScraper** (Livorno, Grosseto, Pistoia, Carrara)

```python
class TrasparenzaVMScraper(BaseAlboScraper):
    """Scraper per Trasparenza VM vendor (4 comuni, 31%)"""
    
    async def detect_platform(self, url: str) -> bool:
        """Detect Trasparenza VM"""
        return 'trasparenza-valutazione-merito.it' in url
    
    async def scrape_page(self, url: str, page_num: int = 1) -> List[AttoPA]:
        """Scrape con Playwright (JS-heavy)"""
        from playwright.async_api import async_playwright
        
        async with async_playwright() as p:
            browser, context = await create_undetectable_browser()
            page = await context.new_page()
            
            try:
                # Navigate
                page_url = f"{url}?page={page_num}"
                await page.goto(page_url, wait_until='networkidle', timeout=30000)
                
                # Wait for content
                await page.wait_for_selector('.atto-item, .documento-item', timeout=10000)
                
                # Extract atti
                atti_elements = await page.query_selector_all('.atto-item, .documento-item')
                
                atti = []
                for elem in atti_elements:
                    atto = await self._parse_trasparenza_vm_atto(elem)
                    if atto:
                        atti.append(atto)
                
                return atti
            
            finally:
                await context.close()
                await browser.close()
    
    async def _parse_trasparenza_vm_atto(self, elem) -> Optional[AttoPA]:
        """Parse atto Trasparenza VM"""
        try:
            # Trasparenza VM structure (adjust after inspection)
            numero = await elem.query_selector('.numero')
            data = await elem.query_selector('.data')
            oggetto = await elem.query_selector('.oggetto, .titolo')
            link = await elem.query_selector('a')
            
            if not numero or not oggetto:
                return None
            
            return AttoPA(
                numero=await numero.inner_text(),
                data_pubblicazione=self._parse_italian_date(await data.inner_text() if data else ''),
                data_scadenza=None,
                oggetto=await oggetto.inner_text(),
                tipo_atto='atto',
                url_dettaglio=await link.get_attribute('href') if link else '',
                url_pdf=None,
                comune_code=self.comune_code,
                tenant_id=self.tenant_id,
                metadata={'platform': 'trasparenza_vm'}
            )
        except:
            return None
    
    async def get_total_pages(self, url: str) -> int:
        """Get pages con Playwright"""
        # Similar logic con Playwright
        # Inspect pagination element
        return 10  # Default, adjust after inspection
```

---

## **Scraper Factory & Auto-Detection**

```python
class ScraperFactory:
    """Factory per creare scraper appropriato"""
    
    # Registro scrapers disponibili
    SCRAPERS = [
        DrupalAlboScraper,
        TrasparenzaVMScraper,
        # WordPressAlboScraper,  # TODO
        # URBIAlboScraper,       # TODO
        # APIAlboScraper,        # TODO (Firenze-style)
    ]
    
    @staticmethod
    async def create_scraper(
        comune_code: str,
        url: str,
        tenant_id: str
    ) -> Optional[BaseAlboScraper]:
        """
        Auto-detect e crea scraper appropriato
        """
        # Try each scraper
        for scraper_class in ScraperFactory.SCRAPERS:
            scraper = scraper_class(comune_code, tenant_id)
            
            if await scraper.detect_platform(url):
                print(f"✅ Detected platform: {scraper_class.__name__}")
                return scraper
        
        print(f"❌ No compatible scraper found for {url}")
        return None
    
    @staticmethod
    async def scrape_comune(
        comune_code: str,
        url: str,
        tenant_id: str,
        max_pages: Optional[int] = None
    ) -> ScrapeResult:
        """
        Scrape comune con auto-detection
        """
        scraper = await ScraperFactory.create_scraper(comune_code, url, tenant_id)
        
        if not scraper:
            return ScrapeResult(
                status='error',
                atti=[],
                errors=['No compatible scraper found'],
                stats={}
            )
        
        return await scraper.scrape_all(url, max_pages=max_pages)

# Uso semplice
result = await ScraperFactory.scrape_comune(
    comune_code='empoli',
    url='https://empoli.drupal.it/albo',
    tenant_id='tenant_toscana',
    max_pages=5
)

print(f"Status: {result.status}")
print(f"Atti: {len(result.atti)}")
print(f"Errors: {len(result.errors)}")
```

---

## **Queue System per Multi-Tenant Scalabile**

```python
from dataclasses import dataclass
from queue import PriorityQueue
import asyncio
from typing import List

@dataclass(order=True)
class ScrapeJob:
    """Job scraping con priorità"""
    priority: int  # Lower = higher priority
    comune_code: str
    url: str
    tenant_id: str
    max_pages: Optional[int] = None

class DistributedScraperQueue:
    """Queue distribuita per scraping multi-tenant"""
    
    def __init__(self, max_workers: int = 5):
        self.max_workers = max_workers
        self.queue = asyncio.Queue()
        self.results = {}
        self.workers = []
    
    async def add_job(self, job: ScrapeJob):
        """Aggiungi job alla queue"""
        await self.queue.put(job)
    
    async def worker(self, worker_id: int):
        """Worker che processa job"""
        print(f"Worker {worker_id} started")
        
        while True:
            try:
                job = await self.queue.get()
                
                print(f"Worker {worker_id}: Processing {job.comune_code}")
                
                # Scrape
                result = await ScraperFactory.scrape_comune(
                    comune_code=job.comune_code,
                    url=job.url,
                    tenant_id=job.tenant_id,
                    max_pages=job.max_pages
                )
                
                self.results[job.comune_code] = result
                
                self.queue.task_done()
                
            except asyncio.CancelledError:
                break
            except Exception as e:
                print(f"Worker {worker_id} error: {e}")
                self.queue.task_done()
    
    async def start(self):
        """Avvia workers"""
        self.workers = [
            asyncio.create_task(self.worker(i))
            for i in range(self.max_workers)
        ]
    
    async def wait_completion(self):
        """Aspetta completamento di tutti i job"""
        await self.queue.join()
    
    async def stop(self):
        """Ferma workers"""
        for worker in self.workers:
            worker.cancel()
        
        await asyncio.gather(*self.workers, return_exceptions=True)

# Uso per scraping multi-comune
async def scrape_all_comuni():
    """Scrape tutti i comuni Toscana in parallelo"""
    
    comuni = [
        {'code': 'firenze', 'url': 'https://...', 'priority': 1},
        {'code': 'empoli', 'url': 'https://...', 'priority': 2},
        {'code': 'livorno', 'url': 'https://...', 'priority': 2},
        # ... altri comuni
    ]
    
    # Create queue
    queue = DistributedScraperQueue(max_workers=5)
    await queue.start()
    
    # Add jobs
    for comune in comuni:
        job = ScrapeJob(
            priority=comune['priority'],
            comune_code=comune['code'],
            url=comune['url'],
            tenant_id='tenant_toscana',
            max_pages=10
        )
        await queue.add_job(job)
    
    # Wait completion
    await queue.wait_completion()
    await queue.stop()
    
    # Results
    for comune_code, result in queue.results.items():
        print(f"{comune_code}: {result.status} ({len(result.atti)} atti)")

# Run
asyncio.run(scrape_all_comuni())
```

---

## **Monitoring & Health Checks**

```python
from dataclasses import dataclass, field
from datetime import datetime
from typing import Dict

@dataclass
class ScraperHealth:
    """Health status scraper"""
    comune_code: str
    last_success: Optional[datetime] = None
    last_error: Optional[datetime] = None
    consecutive_failures: int = 0
    total_requests: int = 0
    total_errors: int = 0
    avg_response_time: float = 0.0
    circuit_breaker_state: str = 'closed'

class ScraperMonitor:
    """Monitor health di tutti gli scrapers"""
    
    def __init__(self):
        self.health_status: Dict[str, ScraperHealth] = {}
    
    def update_success(self, comune_code: str, response_time: float):
        """Aggiorna dopo successo"""
        if comune_code not in self.health_status:
            self.health_status[comune_code] = ScraperHealth(comune_code)
        
        health = self.health_status[comune_code]
        health.last_success = datetime.now()
        health.consecutive_failures = 0
        health.total_requests += 1
        
        # Update avg response time (exponential moving average)
        alpha = 0.3
        health.avg_response_time = (
            alpha * response_time +
            (1 - alpha) * health.avg_response_time
        )
    
    def update_error(self, comune_code: str):
        """Aggiorna dopo errore"""
        if comune_code not in self.health_status:
            self.health_status[comune_code] = ScraperHealth(comune_code)
        
        health = self.health_status[comune_code]
        health.last_error = datetime.now()
        health.consecutive_failures += 1
        health.total_errors += 1
        health.total_requests += 1
    
    def get_unhealthy(self, max_failures: int = 3) -> List[str]:
        """Lista comuni con problemi"""
        return [
            code for code, health in self.health_status.items()
            if health.consecutive_failures >= max_failures
        ]
    
    def get_report(self) -> Dict:
        """Report completo health"""
        total_comuni = len(self.health_status)
        unhealthy = self.get_unhealthy()
        
        return {
            'total_comuni': total_comuni,
            'healthy_comuni': total_comuni - len(unhealthy),
            'unhealthy_comuni': len(unhealthy),
            'unhealthy_list': unhealthy,
            'details': {
                code: {
                    'last_success': health.last_success.isoformat() if health.last_success else None,
                    'consecutive_failures': health.consecutive_failures,
                    'error_rate': health.total_errors / health.total_requests if health.total_requests > 0 else 0,
                    'avg_response_time': health.avg_response_time
                }
                for code, health in self.health_status.items()
            }
        }

# Global monitor
monitor = ScraperMonitor()

# Integra in scraper
class MonitoredBaseAlboScraper(BaseAlboScraper):
    """Base scraper con monitoring integrato"""
    
    async def scrape_all(self, start_url: str, max_pages: Optional[int] = None) -> ScrapeResult:
        start_time = time.time()
        
        result = await super().scrape_all(start_url, max_pages)
        
        response_time = time.time() - start_time
        
        if result.status in ['success', 'partial']:
            monitor.update_success(self.comune_code, response_time)
        else:
            monitor.update_error(self.comune_code)
        
        return result

# Check health periodically
async def health_check_loop():
    """Loop controllo health ogni 1h"""
    while True:
        await asyncio.sleep(3600)  # 1 hour
        
        report = monitor.get_report()
        print(f"Health Report: {report['healthy_comuni']}/{report['total_comuni']} comuni healthy")
        
        if report['unhealthy_comuni'] > 0:
            print(f"⚠️ Unhealthy comuni: {', '.join(report['unhealthy_list'])}")
            # Send alert email/telegram
```

---

# 📊 PERFORMANCE OPTIMIZATION

## **1. Connection Pooling**

```python
# Already in OptimizedHTMLScraper example above
# Key: Riusa connessioni TCP

# httpx async
client = httpx.AsyncClient(
    limits=httpx.Limits(
        max_keepalive_connections=20,
        max_connections=100
    )
)

# requests
session = requests.Session()
adapter = HTTPAdapter(
    pool_connections=10,
    pool_maxsize=20
)
session.mount("http://", adapter)
session.mount("https://", adapter)
```

---

## **2. Response Caching**

```python
# Already implemented in ScraperCache above
# Key: Evita re-scraping stesso contenuto

# Uso
cache = ScraperCache(ttl_hours=24)

if cached := cache.get(url):
    return cached  # Cache hit

data = await scrape(url)
cache.set(url, data)
```

---

## **3. Resource Blocking (Playwright)**

```python
# Block images, CSS, fonts per performance
await context.route(
    "**/*.{png,jpg,jpeg,gif,svg,css,woff,woff2}",
    lambda route: route.abort()
)

# Performance gain: 2-5x più veloce
```

---

## **4. Parallel Processing**

```python
# Async httpx
urls = ['url1', 'url2', 'url3', ...]
async with httpx.AsyncClient() as client:
    tasks = [client.get(url) for url in urls]
    responses = await asyncio.gather(*tasks)

# Performance: 10-100x più veloce vs sequential
```

---

# 🧪 TESTING & MONITORING

## **Unit Tests**

```python
import pytest
from unittest.mock import Mock, AsyncMock

class TestDrupalAlboScraper:
    @pytest.fixture
    def scraper(self):
        return DrupalAlboScraper('test_comune', 'test_tenant')
    
    @pytest.mark.asyncio
    async def test_detect_platform_drupal(self, scraper):
        """Test detection Drupal"""
        url = 'https://empoli.gov.it/albo'  # Mock Drupal site
        # Mock httpx response
        # ...
        result = await scraper.detect_platform(url)
        assert result is True
    
    @pytest.mark.asyncio
    async def test_scrape_page(self, scraper):
        """Test scraping pagina"""
        # Mock HTML response
        mock_html = """
        <div class="views-row">
            <div class="field-name-field-numero">123/2024</div>
            <div class="field-name-title"><a href="/node/456">Test Atto</a></div>
        </div>
        """
        # ...
        atti = await scraper.scrape_page('https://...', 1)
        assert len(atti) > 0
        assert atti[0].numero == '123/2024'

# Run tests
# pytest tests/test_scrapers.py -v
```

---

## **Integration Tests**

```python
@pytest.mark.integration
class TestScraperFactory:
    @pytest.mark.asyncio
    async def test_auto_detect_drupal(self):
        """Test auto-detection Drupal real"""
        scraper = await ScraperFactory.create_scraper(
            comune_code='empoli',
            url='https://www.empoli.gov.it/albo-pretorio',
            tenant_id='test'
        )
        assert scraper is not None
        assert isinstance(scraper, DrupalAlboScraper)
    
    @pytest.mark.asyncio
    async def test_full_scrape_empoli(self):
        """Test scraping completo Empoli (1 pagina)"""
        result = await ScraperFactory.scrape_comune(
            comune_code='empoli',
            url='https://www.empoli.gov.it/albo-pretorio',
            tenant_id='test',
            max_pages=1
        )
        assert result.status == 'success'
        assert len(result.atti) > 0

# Run integration tests (slower)
# pytest tests/test_integration.py -v --integration
```

---

## **Dashboard Monitoring (Bonus)**

```python
from fastapi import FastAPI
from fastapi.responses import HTMLResponse

app = FastAPI()

@app.get("/health")
async def health_endpoint():
    """Health check endpoint"""
    report = monitor.get_report()
    return report

@app.get("/dashboard", response_class=HTMLResponse)
async def dashboard():
    """Simple HTML dashboard"""
    report = monitor.get_report()
    
    html = f"""
    <html>
    <head><title>Scraper Dashboard</title></head>
    <body>
        <h1>Scraper Health Dashboard</h1>
        <p>Healthy: {report['healthy_comuni']}/{report['total_comuni']}</p>
        <p>Unhealthy: {report['unhealthy_comuni']}</p>
        
        <h2>Comuni Status</h2>
        <table border="1">
            <tr>
                <th>Comune</th>
                <th>Last Success</th>
                <th>Failures</th>
                <th>Error Rate</th>
            </tr>
            {''.join([
                f"<tr><td>{code}</td><td>{details['last_success']}</td><td>{details['consecutive_failures']}</td><td>{details['error_rate']:.2%}</td></tr>"
                for code, details in report['details'].items()
            ])}
        </table>
    </body>
    </html>
    """
    return html

# Run: uvicorn dashboard:app --reload
# Visit: http://localhost:8000/dashboard
```

---

# 🎯 ROADMAP IMPLEMENTAZIONE

## **Phase 1: Foundation (Week 1)**
- ✅ Crea `BaseAlboScraper` abstract class
- ✅ Implementa `DrupalAlboScraper` (3 comuni)
- ✅ Implementa `TrasparenzaVMScraper` (4 comuni)
- ✅ Setup `ScraperFactory` con auto-detection
- ✅ Tests unitari

## **Phase 2: Scale (Week 2)**
- ✅ Implementa altri scrapers (WordPress, URBI, API)
- ✅ `DistributedScraperQueue` per multi-tenant
- ✅ MongoDB integration con `PAActMongoDBImporter`
- ✅ Monitoring & health checks
- ✅ Integration tests

## **Phase 3: Production (Week 3)**
- ✅ Deploy su ambiente production
- ✅ Scheduling automatico (cron jobs)
- ✅ Alerting (email/telegram su errori)
- ✅ Dashboard monitoring
- ✅ Documentation completa

## **Phase 4: Expansion (Ongoing)**
- ✅ Aggiungi più regioni (Lazio, Lombardia, ...)
- ✅ ML per auto-detection platforms
- ✅ OCR per PDF scansionati
- ✅ API pubblica per accesso dati

---

# 📖 RISORSE FINALI

## **Documentazione**
- [Requests](https://docs.python-requests.org/)
- [httpx](https://www.python-httpx.org/)
- [BeautifulSoup](https://www.crummy.com/software/BeautifulSoup/bs4/doc/)
- [Scrapy](https://docs.scrapy.org/)
- [Playwright Python](https://playwright.dev/python/)
- [asyncio](https://docs.python.org/3/library/asyncio.html)

## **Best Practices**
- [Web Scraping Best Practices](https://github.com/BruceDone/awesome-crawler)
- [Ethical Scraping Guide](https://www.scraperapi.com/blog/web-scraping-best-practices/)

## **Anti-Detection**
- [Playwright Stealth](https://github.com/AtuboDad/playwright_stealth)
- [Undetected Chromedriver](https://github.com/ultrafunkamsterdam/undetected-chromedriver)

---

# ✅ CHECKLIST FINALE

Sei un esperto di scraping quando:

- [x] **Conosci i fondamenti**: HTTP, HTML, JavaScript, DOM
- [x] **Rispetti l'etica**: robots.txt, rate limiting, compliance GDPR
- [x] **Scegli lo strumento giusto**: requests vs Playwright in base a scenario
- [x] **Gestisci errori**: retry strategies, circuit breakers, logging
- [x] **Ottimizzi performance**: async, caching, connection pooling
- [x] **Scrivi codice maintainable**: base classes, factory pattern, tests
- [x] **Monitori produzione**: health checks, alerts, dashboards
- [x] **Adatti strategia**: rate limiter dinamico, orari intelligenti

---

**🎓 Sei ora un MASSIMO ESPERTO in web scraping!**

**Prossimo step**: Implementazione pratica del sistema multi-tenant per gli albi pretori toscani! 🚀

---

**Documento creato**: 13 novembre 2025  
**Autore**: GitHub Copilot & Fabio  
**Versione**: 1.0 - Master Class Completa
