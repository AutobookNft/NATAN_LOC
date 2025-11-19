"""
AlboPretorioComplianceScanner - Scanner completo conformità Albi Pretori
Per comuni toscani - L.69/2009 + CAD + AgID 2025
"""

import logging
import asyncio
from typing import Dict, List, Optional
import httpx
import json
from datetime import datetime
from .models import ComplianceViolation, ComplianceReport
from .atto_extractor import AttoExtractor

# Optional imports per strategie avanzate
try:
    from playwright.async_api import async_playwright, Browser, Page
    PLAYWRIGHT_AVAILABLE = True
except ImportError:
    PLAYWRIGHT_AVAILABLE = False

try:
    from selenium import webdriver
    from selenium.webdriver.chrome.options import Options
    from selenium.webdriver.common.by import By
    SELENIUM_AVAILABLE = True
except ImportError:
    SELENIUM_AVAILABLE = False

try:
    import feedparser
    FEEDPARSER_AVAILABLE = True
except ImportError:
    FEEDPARSER_AVAILABLE = False

logger = logging.getLogger(__name__)

# Normativa di riferimento
NORMATIVA = {
    "L.69/2009": {
        "name": "Legge 69/2009 - Trasparenza Amministrativa",
        "articles": {
            "art_5": "Pubblicazione obbligatoria atti",
            "art_6": "Accessibilità informazioni",
            "art_7": "Tempi pubblicazione"
        }
    },
    "CAD": {
        "name": "Codice dell'Amministrazione Digitale (D.Lgs 82/2005)",
        "articles": {
            "art_54": "Pubblicazione dati aperti",
            "art_55": "Interoperabilità",
            "art_56": "Accessibilità siti web"
        }
    },
    "AgID_2025": {
        "name": "Linee Guida AgID 2025 - Trasparenza Digitale",
        "articles": {
            "art_1": "Standard tecnici pubblicazione",
            "art_2": "Formato dati strutturati",
            "art_3": "API trasparenti"
        }
    }
}

# Email template
EMAIL_TEMPLATE = """
Oggetto: Violazioni Normative Albo Pretorio - {comune_name}

Gentile Segretario Comunale / Responsabile Trasparenza,

Il presente report evidenzia violazioni normative rilevate nell'Albo Pretorio del Comune di {comune_name}.

SCORE CONFORMITÀ: {compliance_score}/100

VIOLAZIONI CRITICHE:
{violations_critical}

VIOLAZIONI ALTE:
{violations_high}

Per maggiori dettagli, consultare il report PDF allegato o visitare:
{landing_page_url}

Cordiali saluti,
NATAN Compliance Scanner
"""

class AlboPretorioComplianceScanner:
    """
    Scanner completo conformità Albi Pretori comuni toscani
    """
    
    def __init__(self):
        self.strategies = [
            self._strategy_requests,
            self._strategy_httpx,
            self._strategy_playwright_stealth,
            self._strategy_selenium,
            self._strategy_rss,
            self._strategy_api_trasparente
        ]
        self.atto_extractor = AttoExtractor()
    
    async def scan_comune(self, comune_slug: str, tenant_id: Optional[int] = None) -> ComplianceReport:
        """
        Scansiona Albo Pretorio di un comune
        
        Args:
            comune_slug: Slug del comune (es: "firenze", "pisa")
            tenant_id: ID tenant per multi-tenancy
            
        Returns:
            ComplianceReport completo
        """
        logger.info(f"Avvio scan conformità per comune: {comune_slug}")
        
        # Costruisci URL Albo Pretorio (pattern realistici per comuni toscani)
        albo_urls = [
            f"https://www.comune.{comune_slug}.it/albo-pretorio",
            f"https://www.comune.{comune_slug}.it/trasparenza/albo-pretorio",
            f"https://www.comune.{comune_slug}.it/amministrazione-trasparente/albo-pretorio",
            f"https://{comune_slug}.comune.it/albo-pretorio",
            f"https://www.{comune_slug}.comune.it/albo-pretorio",
            f"https://{comune_slug}.it/albo-pretorio",
            f"https://www.{comune_slug}.it/albo-pretorio",
            f"https://albo.{comune_slug}.it",
            f"https://albopretorio.{comune_slug}.it",
        ]
        
        # URL specifici per comuni grandi
        specific_urls = {
            "firenze": [
                "https://www.comune.firenze.it/albo-pretorio",
                "https://www.comune.firenze.it/trasparenza/albo-pretorio",
            ],
            "pisa": [
                "https://www.comune.pisa.it/albo-pretorio",
            ],
            "siena": [
                "https://www.comune.siena.it/albo-pretorio",
            ],
        }
        
        if comune_slug in specific_urls:
            albo_urls = specific_urls[comune_slug] + albo_urls
        
        # Prova 6 strategie di scraping
        content = None
        working_url = None
        
        for strategy in self.strategies:
            for url in albo_urls:
                try:
                    content = await strategy(url)
                    if content:
                        working_url = url
                        logger.info(f"Strategia {strategy.__name__} riuscita per {url}")
                        break
                except Exception as e:
                    logger.debug(f"Strategia {strategy.__name__} fallita per {url}: {e}")
                    continue
            
            if content:
                break
        
        if not content:
            # Nessuna strategia riuscita
            violations = [ComplianceViolation(
                norm="CAD",
                article="art_56",
                violation_type="accessibility",
                description="Albo Pretorio non accessibile tramite scraping standard",
                severity="critical"
            )]
            
            return ComplianceReport(
                comune_slug=comune_slug,
                comune_name=comune_slug.title(),
                scan_date=datetime.now(),
                albo_url=albo_urls[0],
                compliance_score=0.0,
                violations=violations
            )
        
        # PROVA REALE: Estrai atti dall'HTML
        atti_estratti = self.atto_extractor.extract_atti(content, working_url)
        atti_count = len(atti_estratti)
        
        logger.info(f"🎯 ATTI ESTRATTI: {atti_count} atti trovati per {comune_slug}")
        
        # Analizza conformità
        violations = self._analyze_compliance(content, working_url)
        
        # Se non riesci a estrarre atti, è una violazione critica
        if atti_count == 0:
            violations.append(ComplianceViolation(
                norm="L.69/2009",
                article="art_5",
                violation_type="no_atti_extracted",
                description=f"Impossibile estrarre atti dall'Albo Pretorio (0 atti trovati)",
                severity="critical"
            ))
        
        # Calcola score basato su atti estratti + violazioni
        score = 100.0
        
        # Penalità per violazioni
        for violation in violations:
            if violation.severity == "critical":
                score -= 20
            elif violation.severity == "high":
                score -= 10
            elif violation.severity == "medium":
                score -= 5
            else:
                score -= 2
        
        # Bonus se riesci a estrarre atti
        if atti_count > 0:
            score += min(30, atti_count * 2)  # Max +30 punti per atti estratti
        
        score = max(0.0, min(100.0, score))
        
        report = ComplianceReport(
            comune_slug=comune_slug,
            comune_name=comune_slug.title(),
            scan_date=datetime.now(),
            albo_url=working_url,
            compliance_score=score,
            violations=violations
        )
        
        # Aggiungi metadata atti estratti
        report.metadata = {
            "atti_estratti": atti_count,
            "atti_list": atti_estratti[:10]  # Primi 10 per debug
        }
        
        logger.info(f"Scan completato: score {score}/100, {len(violations)} violazioni, {atti_count} atti estratti")
        
        return report
    
    async def _strategy_requests(self, url: str) -> Optional[str]:
        """Strategia 1: requests standard"""
        try:
            import requests
            response = requests.get(url, timeout=10)
            if response.status_code == 200:
                return response.text
        except:
            pass
        return None
    
    async def _strategy_httpx(self, url: str) -> Optional[str]:
        """Strategia 2: httpx async"""
        try:
            async with httpx.AsyncClient(timeout=10.0) as client:
                response = await client.get(url)
                if response.status_code == 200:
                    return response.text
        except:
            pass
        return None
    
    async def _strategy_playwright_stealth(self, url: str) -> Optional[str]:
        """Strategia 3: Playwright stealth"""
        if not PLAYWRIGHT_AVAILABLE:
            return None
        try:
            async with async_playwright() as p:
                browser = await p.chromium.launch(headless=True)
                page = await browser.new_page()
                await page.goto(url, wait_until="networkidle")
                content = await page.content()
                await browser.close()
                return content
        except:
            pass
        return None
    
    async def _strategy_selenium(self, url: str) -> Optional[str]:
        """Strategia 4: Selenium"""
        if not SELENIUM_AVAILABLE:
            return None
        try:
            options = Options()
            options.add_argument("--headless")
            options.add_argument("--no-sandbox")
            driver = webdriver.Chrome(options=options)
            driver.get(url)
            content = driver.page_source
            driver.quit()
            return content
        except:
            pass
        return None
    
    async def _strategy_rss(self, url: str) -> Optional[str]:
        """Strategia 5: RSS Feed"""
        if not FEEDPARSER_AVAILABLE:
            return None
        try:
            rss_urls = [
                f"{url}/rss",
                f"{url}/feed",
                f"{url}/albo-pretorio.rss"
            ]
            for rss_url in rss_urls:
                feed = feedparser.parse(rss_url)
                if feed.entries:
                    return json.dumps([entry for entry in feed.entries[:10]])
        except:
            pass
        return None
    
    async def _strategy_api_trasparente(self, url: str) -> Optional[str]:
        """Strategia 6: API Trasparente"""
        try:
            api_urls = [
                f"{url}/api/v1/atti",
                f"{url}/api/trasparente/atti",
                f"{url}/api/albo-pretorio"
            ]
            async with httpx.AsyncClient(timeout=10.0) as client:
                for api_url in api_urls:
                    response = await client.get(api_url)
                    if response.status_code == 200:
                        return response.text
        except:
            pass
        return None
    
    def _analyze_compliance(self, content: str, url: str) -> List[ComplianceViolation]:
        """Analizza contenuto per violazioni normative"""
        violations = []
        
        # Verifica L.69/2009 - Art. 5: Pubblicazione obbligatoria
        if "albo pretorio" not in content.lower() and "albo-pretorio" not in content.lower():
            violations.append(ComplianceViolation(
                norm="L.69/2009",
                article="art_5",
                violation_type="missing_section",
                description="Sezione Albo Pretorio non trovata o non identificabile",
                severity="critical"
            ))
        
        # Verifica CAD - Art. 56: Accessibilità
        if not self._check_accessibility(content):
            violations.append(ComplianceViolation(
                norm="CAD",
                article="art_56",
                violation_type="accessibility",
                description="Mancanza indicatori di accessibilità WCAG",
                severity="high"
            ))
        
        # Verifica AgID 2025 - Art. 2: Formato dati strutturati
        if not self._check_structured_data(content):
            violations.append(ComplianceViolation(
                norm="AgID_2025",
                article="art_2",
                violation_type="data_format",
                description="Dati non disponibili in formato strutturato (JSON/XML)",
                severity="medium"
            ))
        
        return violations
    
    def _check_accessibility(self, content: str) -> bool:
        """Verifica indicatori accessibilità WCAG"""
        indicators = [
            "aria-label",
            "alt=",
            "lang=",
            "role="
        ]
        return any(indicator in content for indicator in indicators)
    
    def _check_structured_data(self, content: str) -> bool:
        """Verifica presenza dati strutturati"""
        return "application/json" in content or "application/xml" in content or "<rss" in content

