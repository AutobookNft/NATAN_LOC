"""
AlboPretorioComplianceScanner - Scanner completo conformità Albi Pretori
Per comuni toscani - L.69/2009 + CAD + AgID 2025

Integrato con sistema di scraping esistente:
- ScraperFactory per auto-detection e scrapers specifici
- trivella_brutale per fallback aggressivo
"""

import logging
import asyncio
import sys
from pathlib import Path
from typing import Dict, List, Optional
import httpx
import json
from datetime import datetime
from .models import ComplianceViolation, ComplianceReport
from .atto_extractor import AttoExtractor
from .comuni_tracker import ComuniScrapingTracker

# Logger deve essere definito prima degli import che lo usano
logger = logging.getLogger(__name__)

# Import sistema scraping esistente
try:
    # Aggiungi path per importare scrapers
    scrapers_path = Path(__file__).parent.parent.parent.parent / "app" / "scrapers"
    if str(scrapers_path) not in sys.path:
        sys.path.insert(0, str(scrapers_path.parent.parent))
    
    from app.scrapers import ScraperFactory, ScrapeResult
    SCRAPER_FACTORY_AVAILABLE = True
except ImportError as e:
    logger.warning(f"ScraperFactory non disponibile: {e}")
    SCRAPER_FACTORY_AVAILABLE = False

# Import trivella brutale come fallback
try:
    from app.scrapers.trivella_brutale import TrivellaBrutale
    TRIVELLA_BRUTALE_AVAILABLE = True
except ImportError as e:
    logger.warning(f"TrivellaBrutale non disponibile: {e}")
    TRIVELLA_BRUTALE_AVAILABLE = False

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
    
    def __init__(self, output_dir: Optional[str] = None):
        self.strategies = [
            self._strategy_requests,
            self._strategy_httpx,
            self._strategy_playwright_stealth,
            self._strategy_selenium,
            self._strategy_rss,
            self._strategy_api_trasparente
        ]
        self.atto_extractor = AttoExtractor()
        
        # Output directory per JSON backup (come gli altri scraper)
        if output_dir:
            self.output_dir = Path(output_dir)
        else:
            # Default: storage/testing/compliance_scanner (come pattern altri scraper)
            project_root = Path(__file__).parent.parent.parent.parent
            self.output_dir = project_root / "storage" / "testing" / "compliance_scanner"
        
        # Crea directory se non esiste
        self.output_dir.mkdir(parents=True, exist_ok=True)
        (self.output_dir / "json").mkdir(parents=True, exist_ok=True)
    
    async def scan_comune(self, comune_slug: str, tenant_id: Optional[int] = None, dry_run: bool = False, mongodb_import: bool = False) -> ComplianceReport:
        """
        Scansiona Albo Pretorio di un comune usando sistema scraping integrato
        
        Strategia:
        1. Prova ScraperFactory (auto-detection + scrapers specifici)
        2. Se fallisce, prova trivella_brutale (bruteforce completo)
        3. Se fallisce ancora, usa strategie base (fallback)
        
        Args:
            comune_slug: Slug del comune (es: "firenze", "pisa")
            tenant_id: ID tenant per multi-tenancy
            
        Returns:
            ComplianceReport completo con atti reali estratti
        """
        logger.info(f"🚀 Avvio scan conformità per comune: {comune_slug}")
        
        # Costruisci URL Albo Pretorio (pattern realistici per comuni toscani)
        albo_urls = self._get_albo_urls(comune_slug)
        
        atti_count = 0
        atti_list = []
        working_url = None
        metodo_usato = None
        
        # STRATEGIA 0: API dirette per comuni specifici (senza dipendenze)
        if comune_slug == "firenze":
            logger.info(f"  🔍 Tentativo API diretta Firenze...")
            try:
                if dry_run:
                    # DRY RUN: Conta TUTTI gli atti e estrai solo primo e ultimo per preview
                    atti_firenze_completi = await self._strategy_api_firenze()
                    if atti_firenze_completi and len(atti_firenze_completi) > 0:
                        atti_count = len(atti_firenze_completi)
                        # Per preview: solo primo e ultimo atto (non tutti)
                        atti_list = []
                        if atti_count > 0:
                            atti_list.append(atti_firenze_completi[0])  # Primo
                            if atti_count > 1:
                                atti_list.append(atti_firenze_completi[-1])  # Ultimo
                    else:
                        atti_count = 0
                        atti_list = []
                    working_url = "https://accessoconcertificato.comune.fi.it/trasparenza-atti-cat/searchAtti"
                    metodo_usato = "API REST Firenze (dry-run count)"
                    logger.info(f"  ✅ API Firenze DRY RUN: {atti_count} atti trovati (primo e ultimo estratti per preview)")
                else:
                    # Esecuzione normale: estrai tutti gli atti
                    atti_firenze = await self._strategy_api_firenze()
                    if atti_firenze and len(atti_firenze) > 0:
                        atti_count = len(atti_firenze)
                        atti_list = atti_firenze  # TUTTI gli atti
                        working_url = "https://accessoconcertificato.comune.fi.it/trasparenza-atti-cat/searchAtti"
                        metodo_usato = "API REST Firenze (diretta)"
                        logger.info(f"  ✅ API Firenze OK: {atti_count} atti estratti")
            except Exception as e:
                logger.debug(f"  API Firenze fallita: {e}")
        
        elif comune_slug == "sesto_fiorentino" or comune_slug == "sesto-fiorentino":
            logger.info(f"  🔍 Tentativo API diretta Sesto Fiorentino...")
            try:
                if dry_run:
                    # DRY RUN: Conta TUTTI gli atti e estrai solo primo e ultimo per preview
                    atti_sesto_completi = await self._strategy_api_sesto_fiorentino()
                    if atti_sesto_completi and len(atti_sesto_completi) > 0:
                        atti_count = len(atti_sesto_completi)
                        # Per preview: solo primo e ultimo atto (non tutti)
                        atti_list = []
                        if atti_count > 0:
                            atti_list.append(atti_sesto_completi[0])  # Primo
                            if atti_count > 1:
                                atti_list.append(atti_sesto_completi[-1])  # Ultimo
                    else:
                        atti_count = 0
                        atti_list = []
                    working_url = "http://servizi.comune.sesto-fiorentino.fi.it/albo/search.php"
                    metodo_usato = "API DataTables Sesto Fiorentino (dry-run count)"
                    logger.info(f"  ✅ API Sesto Fiorentino DRY RUN: {atti_count} atti trovati (primo e ultimo estratti per preview)")
                else:
                    # Esecuzione normale: estrai tutti gli atti
                    atti_sesto = await self._strategy_api_sesto_fiorentino()
                    if atti_sesto and len(atti_sesto) > 0:
                        atti_count = len(atti_sesto)
                        atti_list = atti_sesto  # TUTTI gli atti
                        working_url = "http://servizi.comune.sesto-fiorentino.fi.it/albo/search.php"
                        metodo_usato = "API DataTables Sesto Fiorentino (diretta)"
                        logger.info(f"  ✅ API Sesto Fiorentino OK: {atti_count} atti estratti")
            except Exception as e:
                logger.debug(f"  API Sesto Fiorentino fallita: {e}")
        
        # STRATEGIA 1: ScraperFactory (auto-detection + scrapers specifici)
        if atti_count == 0 and SCRAPER_FACTORY_AVAILABLE:
            logger.info(f"  🔍 Tentativo ScraperFactory per {comune_slug}...")
            for url in albo_urls[:3]:  # Prova primi 3 URL
                try:
                    result: ScrapeResult = await ScraperFactory.scrape_comune(
                        comune_code=comune_slug,
                        url=url,
                        tenant_id=f"tenant_{tenant_id or 'toscana'}",
                        max_pages=2,  # Limita a 2 pagine per compliance scan
                        save_to_mongodb=False  # Non salvare, solo estrarre
                    )
                    
                    if result.status in ['success', 'partial'] and len(result.atti) > 0:
                        atti_count = len(result.atti)
                        atti_list = [atto.to_dict() for atto in result.atti]  # TUTTI gli atti estratti
                        working_url = url
                        metodo_usato = f"ScraperFactory ({result.stats.get('scraper_type', 'auto')})"
                        logger.info(f"  ✅ ScraperFactory OK: {atti_count} atti estratti")
                        break
                except Exception as e:
                    logger.debug(f"  ScraperFactory fallito per {url}: {e}")
                    continue
        
        # STRATEGIA 2: Trivella Brutale (bruteforce completo)
        if atti_count == 0 and TRIVELLA_BRUTALE_AVAILABLE:
            logger.info(f"  🔨 Tentativo Trivella Brutale per {comune_slug}...")
            try:
                trivella = TrivellaBrutale()
                # Prova con primo URL - massacra_comune richiede dict con nome, url, abitanti
                comune_dict = {
                    'nome': comune_slug.title(),
                    'url': albo_urls[0],
                    'abitanti': 0  # Non necessario per scraping
                }
                # IMPORTANTE: TrivellaBrutale.massacra_comune() è SYNC ma siamo in contesto ASYNC
                # Wrappiamo in asyncio.to_thread() per non bloccare l'event loop
                result_brutale = await asyncio.to_thread(trivella.massacra_comune, comune_dict)
                
                if result_brutale and result_brutale.get('funzionante', False):
                    # TrivellaBrutale restituisce dict con info, non lista atti
                    # Usa il metodo trovato per estrarre atti
                    metodo_trovato = result_brutale.get('metodo', 'unknown')
                    if metodo_trovato and metodo_trovato != 'unknown':
                        # Se ha trovato un metodo, prova a estrarre atti usando quello
                        # Per ora, considera funzionante se ha trovato metodo
                        atti_count = result_brutale.get('atti', 1)  # Assume almeno 1 atto se funzionante
                        working_url = result_brutale.get('url', albo_urls[0])
                        metodo_usato = f"TrivellaBrutale ({metodo_trovato})"
                        logger.info(f"  ✅ Trivella Brutale OK: metodo {metodo_trovato} trovato")
            except Exception as e:
                logger.debug(f"  Trivella Brutale fallita: {e}")
        
        # STRATEGIA 3: Strategie base (fallback)
        if atti_count == 0:
            logger.info(f"  🔄 Fallback a strategie base per {comune_slug}...")
            content = None
            
            for strategy in self.strategies:
                for url in albo_urls:
                    try:
                        content = await strategy(url)
                        if content:
                            working_url = url
                            logger.info(f"  Strategia {strategy.__name__} riuscita per {url}")
                            break
                    except Exception as e:
                        logger.debug(f"  Strategia {strategy.__name__} fallita per {url}: {e}")
                        continue
                
                if content:
                    break
            
            # Estrai atti da HTML se abbiamo contenuto
            if content:
                atti_estratti = self.atto_extractor.extract_atti(content, working_url)
                atti_count = len(atti_estratti)
                atti_list = atti_estratti  # TUTTI gli atti estratti
                metodo_usato = "HTML Parsing (fallback)"
                logger.info(f"  ✅ HTML Parsing: {atti_count} atti estratti")
        
        # Se ancora nessun atto trovato
        if atti_count == 0:
            violations = [ComplianceViolation(
                norm="CAD",
                article="art_56",
                violation_type="accessibility",
                description="Albo Pretorio non accessibile tramite scraping (ScraperFactory + TrivellaBrutale + fallback)",
                severity="critical"
            )]
            
            return ComplianceReport(
                comune_slug=comune_slug,
                comune_name=comune_slug.title(),
                scan_date=datetime.now(),
                albo_url=albo_urls[0] if albo_urls else None,
                compliance_score=0.0,
                violations=violations,
                metadata={"atti_estratti": 0, "metodo": "Nessuno"}
            )
        
        # Analizza conformità (usa content se disponibile, altrimenti solo URL)
        violations = self._analyze_compliance_from_atti(atti_count, working_url)
        
        # Se pochi atti estratti, è una violazione
        if atti_count < 5:
            violations.append(ComplianceViolation(
                norm="L.69/2009",
                article="art_5",
                violation_type="insufficient_atti",
                description=f"Solo {atti_count} atti estratti (soglia minima: 5)",
                severity="high"
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
        
        # Salva JSON backup SOLO se non è dry-run (dry-run non estrae atti)
        json_output_path = None
        if not dry_run and atti_list:
            json_output_path = self._save_json_backup(comune_slug, atti_list, tenant_id)
            if json_output_path:
                print(f"\n   💾 Backup JSON salvato: {json_output_path}", flush=True)
                logger.info(f"JSON backup salvato: {json_output_path}")
        
        # Aggiungi metadata atti estratti (include path JSON per compatibilità)
        report.metadata = {
            "atti_estratti": atti_count,  # Conteggio totale
            "atti_list": atti_list,  # In dry-run contiene primo e ultimo atto per preview
            "metodo": metodo_usato or "Unknown",
            "json_backup_path": json_output_path  # Path del JSON salvato (per Laravel)
        }
        
        # Salva tracking in MongoDB se scraping completato (non dry-run)
        if not dry_run and atti_count > 0:
            # Determina status in base al risultato
            status = "completed" if atti_count > 0 and len(violations) == 0 else "partial"
            if atti_count == 0:
                status = "failed"
            
            # Salva nel tracker
            ComuniScrapingTracker.mark_comune_scraped(
                comune_slug=comune_slug,
                comune_nome=comune_slug.title(),
                atti_estratti=atti_count,
                compliance_score=score / 100.0,  # Normalizza a 0-1
                violations_count=len(violations),
                metodo_usato=metodo_usato,
                json_backup_path=json_output_path,
                pdf_report_path=None,  # Verrà aggiunto dopo generazione PDF
                landing_page_url=None,  # Verrà aggiunto dopo generazione landing page
                email_sent=False,  # Verrà aggiornato dopo invio email
                tenant_id=tenant_id,
                status=status
            )
            logger.info(f"  📊 Tracking salvato per comune: {comune_slug}")
        
        logger.info(f"✅ Scan completato: score {score}/100, {len(violations)} violazioni, {atti_count} atti estratti ({metodo_usato})")
        
        # Import in MongoDB con embeddings se richiesto (non dry-run)
        if mongodb_import and not dry_run and atti_list and len(atti_list) > 0:
            logger.info(f"  📊 Avvio import MongoDB con embeddings per {len(atti_list)} atti...")
            try:
                from app.services.pa_act_mongodb_importer import PAActMongoDBImporter
                
                importer = PAActMongoDBImporter(tenant_id=tenant_id or 1, dry_run=False)
                
                imported_count = 0
                errors_count = 0
                
                for atto_dict in atti_list:
                    try:
                        # Converti formato atto da Compliance Scanner a formato PAActMongoDBImporter
                        atto_data = {
                            'numero_atto': atto_dict.get('numero', atto_dict.get('numero_atto', '')),
                            'tipo_atto': atto_dict.get('tipo', atto_dict.get('tipo_atto', '')),
                            'oggetto': atto_dict.get('oggetto', atto_dict.get('descrizione', '')),
                            'data_atto': atto_dict.get('data', atto_dict.get('data_pubblicazione', atto_dict.get('data_adozione', ''))),
                            'anno': atto_dict.get('anno', ''),
                            'scraper_type': 'compliance_scanner',
                            'comune_slug': comune_slug
                        }
                        
                        # URL PDF se disponibile
                        pdf_url = atto_dict.get('url_pdf') or atto_dict.get('pdf_url') or atto_dict.get('link')
                        
                        # Import atto
                        success = await importer.import_atto(
                            atto_data=atto_data,
                            pdf_url=pdf_url,
                            ente=comune_slug.title()
                        )
                        
                        if success:
                            imported_count += 1
                        else:
                            errors_count += 1
                            
                    except Exception as e:
                        logger.error(f"  ❌ Errore import atto {atto_dict.get('numero', 'N/A')}: {e}")
                        errors_count += 1
                
                logger.info(f"  ✅ Import MongoDB completato: {imported_count}/{len(atti_list)} atti importati, {errors_count} errori")
                
                # Aggiungi statistiche import al metadata
                if report.metadata:
                    report.metadata['mongodb_import'] = {
                        'imported': imported_count,
                        'total': len(atti_list),
                        'errors': errors_count
                    }
                
            except Exception as e:
                logger.error(f"  ❌ Errore import MongoDB: {e}")
                # Non bloccare il report se l'import fallisce
        
        return report
    
    def _get_albo_urls(self, comune_slug: str) -> List[str]:
        """Costruisci lista URL Albo Pretorio per comune"""
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
                "https://accessoconcertificato.comune.fi.it/trasparenza-atti-cat/searchAtti",
            ],
            "sesto_fiorentino": [
                "http://servizi.comune.sesto-fiorentino.fi.it/albo/search.php",
                "http://servizi.comune.sesto-fiorentino.fi.it/albo/",
            ],
            "sesto-fiorentino": [
                "http://servizi.comune.sesto-fiorentino.fi.it/albo/search.php",
                "http://servizi.comune.sesto-fiorentino.fi.it/albo/",
            ],
            "pisa": [
                "https://www.comune.pisa.it/albo-pretorio",
                "https://albopretorio.comune.pisa.it/",
            ],
            "siena": [
                "https://www.comune.siena.it/albo-pretorio",
            ],
        }
        
        if comune_slug in specific_urls:
            albo_urls = specific_urls[comune_slug] + albo_urls
        
        return albo_urls
    
    def _analyze_compliance_from_atti(self, atti_count: int, url: Optional[str]) -> List[ComplianceViolation]:
        """Analizza conformità basandosi su atti estratti"""
        violations = []
        
        # Verifica L.69/2009 - Art. 5: Pubblicazione obbligatoria
        if atti_count == 0:
            violations.append(ComplianceViolation(
                norm="L.69/2009",
                article="art_5",
                violation_type="no_atti_extracted",
                description="Impossibile estrarre atti dall'Albo Pretorio (0 atti trovati)",
                severity="critical"
            ))
        
        # Verifica CAD - Art. 56: Accessibilità
        if atti_count < 5:
            violations.append(ComplianceViolation(
                norm="CAD",
                article="art_56",
                violation_type="accessibility",
                description=f"Albo Pretorio difficilmente accessibile (solo {atti_count} atti estratti)",
                severity="high"
            ))
        
        return violations
    
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
                    return json.dumps([entry for entry in feed.entries])  # TUTTI gli entry RSS
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
        """Analizza contenuto per violazioni normative (metodo legacy, mantenuto per compatibilità)"""
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
    
    async def _strategy_api_firenze(self) -> Optional[List[Dict]]:
        """Strategia completa Firenze - Estrae TUTTI i documenti pubblici (API + Albo Pretorio HTML)"""
        try:
            import requests
            from bs4 import BeautifulSoup
            import re
            from urllib.parse import urljoin
            
            base_url = "https://accessoconcertificato.comune.fi.it"
            tutti_documenti = []
            
            # ============================================
            # PARTE 1: API Atti (Deliberazioni, Determinazioni, etc.)
            # ============================================
            logger.info(f"  📡 FASE 1: Estrazione da API Atti...")
            
            api_url = f"{base_url}/trasparenza-atti-cat/searchAtti"
            
            # TUTTI i tipi di atti disponibili
            tipi_atto = [
                ("DG", "Deliberazioni di Giunta"),
                ("DC", "Deliberazioni di Consiglio"),
                ("DD", "Determinazioni Dirigenziali"),
                ("DS", "Decreti Sindacali"),
                ("OD", "Ordinanze Dirigenziali")
            ]
            
            # TUTTI gli anni disponibili (2018-2025 tipicamente)
            anni = list(range(2018, 2026))  # 2018-2025
            
            headers = {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept': 'application/json, text/plain, */*',
                'Accept-Language': 'it-IT,it;q=0.9',
                'Content-Type': 'application/json',
                'Origin': base_url,
                'Referer': f'{base_url}/trasparenza-atti/',
            }
            
            logger.info(f"    Scansione API: {len(tipi_atto)} tipi × {len(anni)} anni = {len(tipi_atto) * len(anni)} chiamate")
            
            for tipo_codice, tipo_nome in tipi_atto:
                for anno in anni:
                    payload = {
                        "annoAdozione": str(anno),
                        "tipiAtto": [tipo_codice],
                        "competenza": tipo_codice,
                        "notLoadIniziale": "ok"
                    }
                    
                    try:
                        response = requests.post(api_url, json=payload, headers=headers, timeout=15)
                        
                        if response.status_code == 200:
                            data = response.json()
                            
                            # Estrai documenti dalla risposta
                            documenti = []
                            if isinstance(data, list):
                                documenti = data
                            elif isinstance(data, dict):
                                documenti = data.get('data', data.get('results', data.get('items', data.get('records', []))))
                            
                            if isinstance(documenti, list) and len(documenti) > 0:
                                tutti_documenti.extend(documenti)
                                logger.debug(f"    API {tipo_codice} {anno}: {len(documenti)} documenti")
                    
                    except Exception as e:
                        logger.debug(f"    Errore API {tipo_codice} {anno}: {e}")
                        continue
            
            logger.info(f"  ✅ API: {len(tutti_documenti)} documenti estratti")
            
            # ============================================
            # PARTE 2: Albo Pretorio HTML (documenti pubblicati)
            # ============================================
            logger.info(f"  📄 FASE 2: Estrazione da Albo Pretorio HTML...")
            
            albo_url = f"{base_url}/AOL/Affissione/ComuneFi/Page"
            session = requests.Session()
            session.headers.update({
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            })
            
            # Ottieni prima pagina per determinare totale pagine
            try:
                response = session.get(albo_url, timeout=15)
                if response.status_code == 200:
                    soup = BeautifulSoup(response.text, 'html.parser')
                    
                    # Estrai totale pagine
                    pagination_text = soup.find(string=re.compile(r'Pagina\s+\d+\s+di\s+\d+', re.IGNORECASE))
                    total_pages = 1
                    if pagination_text:
                        match = re.search(r'Pagina\s+\d+\s+di\s+(\d+)', pagination_text, re.IGNORECASE)
                        if match:
                            total_pages = int(match.group(1))
                    
                    logger.info(f"    Albo Pretorio: {total_pages} pagine trovate")
                    
                    # Scrapa tutte le pagine
                    documenti_albo = []
                    for page_num in range(1, total_pages + 1):
                        if page_num > 1:
                            import asyncio
                            await asyncio.sleep(1)  # Rate limiting
                            response = session.get(albo_url, params={'page': page_num}, timeout=15)
                            if response.status_code != 200:
                                continue
                            soup = BeautifulSoup(response.text, 'html.parser')
                        
                        # Cerca card divs con atti
                        card_divs = soup.find_all('div', class_='card concorso-card multi-line')
                        
                        for card in card_divs:
                            try:
                                text = card.get_text(separator='\n')
                                lines = [line.strip() for line in card.stripped_strings]
                                
                                # Estrai dati
                                numero_registro = ''
                                numero_atto = ''
                                data_inizio = ''
                                data_fine = ''
                                oggetto = ''
                                
                                # N° registro
                                match = re.search(r'N°\s*registro\s*(\d+/\d+)', text, re.IGNORECASE)
                                if match:
                                    numero_registro = match.group(1)
                                
                                # N° atto
                                match = re.search(r'N°\s*atto\s*(\d+/\d+)', text, re.IGNORECASE)
                                if match:
                                    numero_atto = match.group(1)
                                
                                # Date
                                match = re.search(r'Inizio\s+pubblicazione\s*(\d{2}/\d{2}/\d{4})', text, re.IGNORECASE)
                                if match:
                                    data_inizio = match.group(1)
                                
                                match = re.search(r'Fine\s+pubblicazione\s*(\d{2}/\d{2}/\d{4})', text, re.IGNORECASE)
                                if match:
                                    data_fine = match.group(1)
                                
                                # Oggetto (ultimo testo lungo)
                                for line in reversed(lines):
                                    if len(line) > 50:
                                        oggetto = line
                                        break
                                
                                # Tipo atto (prima riga)
                                tipo_atto = lines[0] if lines else 'Documento Albo'
                                
                                # Link PDF
                                pdf_links = card.find_all('a', href=lambda x: x and '.pdf' in x.lower())
                                pdf_url = ''
                                if pdf_links:
                                    pdf_url = urljoin(base_url, pdf_links[0].get('href', ''))
                                
                                # Crea documento solo se ha numero valido
                                if numero_registro or numero_atto:
                                    doc_albo = {
                                        'numero': numero_atto or numero_registro,
                                        'numero_registro': numero_registro,
                                        'data': data_inizio or data_fine,
                                        'data_inizio': data_inizio,
                                        'data_fine': data_fine,
                                        'oggetto': oggetto,
                                        'tipo': tipo_atto,
                                        'fonte': 'Albo Pretorio HTML',
                                        'link': pdf_url
                                    }
                                    documenti_albo.append(doc_albo)
                            
                            except Exception as e:
                                logger.debug(f"    Errore parsing card: {e}")
                                continue
                        
                        logger.debug(f"    Pagina {page_num}/{total_pages}: {len(card_divs)} card, {len(documenti_albo)} documenti estratti")
                    
                    # Aggiungi documenti Albo Pretorio
                    tutti_documenti.extend(documenti_albo)
                    logger.info(f"  ✅ Albo Pretorio HTML: {len(documenti_albo)} documenti estratti")
            
            except Exception as e:
                logger.debug(f"  Errore scraping Albo Pretorio HTML: {e}")
            
            # ============================================
            # CONVERSIONE FORMATO STANDARD
            # ============================================
            if len(tutti_documenti) > 0:
                documenti_formattati = []
                for doc in tutti_documenti:
                    # Normalizza formato
                    documenti_formattati.append({
                        'numero': doc.get('numero', doc.get('numeroAdozione', doc.get('numeroAtto', doc.get('numero_registro', 'N/A')))),
                        'data': doc.get('dataAdozione', doc.get('data', doc.get('data_inizio', 'N/A'))),
                        'oggetto': doc.get('oggetto', doc.get('descrizione', 'N/A')),
                        'tipo': doc.get('tipoAtto', doc.get('tipo', 'Documento')),
                        'anno': doc.get('annoAdozione', doc.get('anno', 'N/A')),
                        'competenza': doc.get('competenza', 'N/A'),
                        'fonte': doc.get('fonte', 'API Atti'),
                        'link': doc.get('url', doc.get('link', ''))
                    })
                
                logger.info(f"  ✅ TOTALE Firenze: {len(documenti_formattati)} documenti pubblici estratti (API + Albo Pretorio)")
                return documenti_formattati
        
        except Exception as e:
            logger.debug(f"  Strategia Firenze fallita: {e}")
        
        return None
    
    async def _strategy_api_sesto_fiorentino(self) -> Optional[List[Dict]]:
        """Strategia completa Sesto Fiorentino - Estrae TUTTI i documenti pubblici (API DataTables + Albo Pretorio HTML)"""
        try:
            import requests
            from bs4 import BeautifulSoup
            import re
            from urllib.parse import urljoin
            
            base_url = "http://servizi.comune.sesto-fiorentino.fi.it"
            tutti_documenti = []
            
            # ============================================
            # PARTE 1: API DataTables (tutti gli anni disponibili)
            # ============================================
            logger.info(f"  📡 FASE 1: Estrazione da API DataTables Sesto Fiorentino...")
            
            api_url = f"{base_url}/albo/search.php"
            
            headers = {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept': 'application/json, text/plain, */*',
                'Accept-Language': 'it-IT,it;q=0.9',
            }
            
            # TUTTI gli anni disponibili (2018-2025 tipicamente)
            anni = list(range(2018, 2026))  # 2018-2025
            
            logger.info(f"    Scansione API: {len(anni)} anni da verificare")
            
            # Per ogni anno, estrai TUTTI i documenti
            for anno in anni:
                try:
                    import asyncio
                    await asyncio.sleep(0.3)  # Rate limiting tra anni
                    
                    # Chiamata iniziale per vedere quanti documenti ci sono per questo anno
                    params_anno = {
                        'draw': '1',
                        'start': '0',
                        'length': '1000',  # Provo a prendere tutti
                        'search[value]': str(anno),
                        'search[regex]': 'false',
                        # Filtro colonna anno (colonna 1)
                        'columns[1][data]': '1',
                        'columns[1][searchable]': 'true',
                        'columns[1][search][value]': str(anno),
                        'columns[1][search][regex]': 'false'
                    }
                    
                    response = requests.get(api_url, params=params_anno, headers=headers, timeout=15)
                    
                    if response.status_code == 200:
                        data = response.json()
                        
                        if isinstance(data, dict):
                            # Estrai documenti filtrati per anno
                            filtered_docs = data.get('data', [])
                            
                            if isinstance(filtered_docs, list) and len(filtered_docs) > 0:
                                # Verifica che siano effettivamente dell'anno richiesto
                                docs_anno = []
                                for doc in filtered_docs:
                                    if isinstance(doc, list) and len(doc) > 1:
                                        anno_doc = str(doc[1])
                                        if anno_doc == str(anno):
                                            docs_anno.append(doc)
                                
                                if len(docs_anno) > 0:
                                    tutti_documenti.extend(docs_anno)
                                    logger.debug(f"    Anno {anno}: {len(docs_anno)} documenti estratti")
                                    
                                    # Se ci sono più documenti, fai paginazione
                                    records_filtered = data.get('recordsFiltered', len(docs_anno))
                                    if records_filtered > len(docs_anno):
                                        start = len(docs_anno)
                                        draw = 2
                                        batch_size = 1000
                                        
                                        while start < records_filtered:
                                            params_batch = {
                                                'draw': str(draw),
                                                'start': str(start),
                                                'length': str(batch_size),
                                                'search[value]': str(anno),
                                                'search[regex]': 'false',
                                                'columns[1][data]': '1',
                                                'columns[1][searchable]': 'true',
                                                'columns[1][search][value]': str(anno),
                                                'columns[1][search][regex]': 'false'
                                            }
                                            
                                            try:
                                                await asyncio.sleep(0.3)
                                                response_batch = requests.get(api_url, params=params_batch, headers=headers, timeout=15)
                                                
                                                if response_batch.status_code == 200:
                                                    batch_data = response_batch.json()
                                                    if isinstance(batch_data, dict) and 'data' in batch_data:
                                                        batch_docs = batch_data['data']
                                                        if isinstance(batch_docs, list) and len(batch_docs) > 0:
                                                            # Filtra solo documenti dell'anno corretto
                                                            batch_anno = [d for d in batch_docs if isinstance(d, list) and len(d) > 1 and str(d[1]) == str(anno)]
                                                            if len(batch_anno) > 0:
                                                                tutti_documenti.extend(batch_anno)
                                                                logger.debug(f"    Anno {anno} batch {draw}: {len(batch_anno)} documenti (totale anno: {len([d for d in tutti_documenti if isinstance(d, list) and len(d) > 1 and str(d[1]) == str(anno)])})")
                                                                start += len(batch_anno)
                                                                draw += 1
                                                                
                                                                if len(batch_anno) < batch_size:
                                                                    break
                                                            else:
                                                                break
                                                        else:
                                                            break
                                                    else:
                                                        break
                                                else:
                                                    break
                                            
                                            except Exception as e:
                                                logger.debug(f"    Errore batch anno {anno}: {e}")
                                                break
                
                except Exception as e:
                    logger.debug(f"    Errore anno {anno}: {e}")
                    continue
            
            logger.info(f"  ✅ API DataTables: {len(tutti_documenti)} documenti estratti da {len(anni)} anni")
            
            # ============================================
            # PARTE 2: Albo Pretorio HTML (se disponibile)
            # ============================================
            logger.info(f"  📄 FASE 2: Estrazione da Albo Pretorio HTML Sesto Fiorentino...")
            
            albo_urls = [
                f"{base_url}/albo/",
                f"{base_url}/albo/index.php",
                f"{base_url}/albo/albo.php"
            ]
            
            session = requests.Session()
            session.headers.update({
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            })
            
            documenti_albo = []
            
            for albo_url in albo_urls:
                try:
                    response = session.get(albo_url, timeout=15)
                    if response.status_code == 200:
                        soup = BeautifulSoup(response.text, 'html.parser')
                        
                        # Cerca tabelle con atti
                        tables = soup.find_all('table')
                        for table in tables:
                            rows = table.find_all('tr')
                            for row in rows[1:]:  # Skip header
                                cells = row.find_all(['td', 'th'])
                                if len(cells) >= 3:
                                    try:
                                        # Estrai dati da riga tabella
                                        numero = cells[0].get_text(strip=True) if len(cells) > 0 else ''
                                        data = cells[1].get_text(strip=True) if len(cells) > 1 else ''
                                        oggetto = cells[2].get_text(strip=True) if len(cells) > 2 else ''
                                        
                                        # Cerca link PDF
                                        pdf_links = row.find_all('a', href=lambda x: x and '.pdf' in x.lower())
                                        pdf_url = ''
                                        if pdf_links:
                                            pdf_url = urljoin(base_url, pdf_links[0].get('href', ''))
                                        
                                        if numero and oggetto:
                                            doc_albo = {
                                                'numero': numero,
                                                'data': data,
                                                'oggetto': oggetto,
                                                'tipo': 'Documento Albo',
                                                'fonte': 'Albo Pretorio HTML Sesto Fiorentino',
                                                'link': pdf_url
                                            }
                                            documenti_albo.append(doc_albo)
                                    except:
                                        continue
                        
                        # Cerca anche div con atti
                        divs_atti = soup.find_all('div', class_=lambda x: x and ('atto' in x.lower() or 'card' in x.lower() or 'pubblicazione' in x.lower()))
                        for div in divs_atti:
                            try:
                                testo = div.get_text(separator='\n')
                                lines = [line.strip() for line in div.stripped_strings]
                                
                                if len(lines) >= 2:
                                    numero = lines[0] if lines else ''
                                    oggetto = lines[1] if len(lines) > 1 else ''
                                    
                                    # Cerca date nel testo
                                    match_data = re.search(r'(\d{2}/\d{2}/\d{4})', testo)
                                    data = match_data.group(1) if match_data else ''
                                    
                                    # Link PDF
                                    pdf_links = div.find_all('a', href=lambda x: x and '.pdf' in x.lower())
                                    pdf_url = ''
                                    if pdf_links:
                                        pdf_url = urljoin(base_url, pdf_links[0].get('href', ''))
                                    
                                    if numero and oggetto:
                                        doc_albo = {
                                            'numero': numero,
                                            'data': data,
                                            'oggetto': oggetto,
                                            'tipo': 'Documento Albo',
                                            'fonte': 'Albo Pretorio HTML Sesto Fiorentino',
                                            'link': pdf_url
                                        }
                                        documenti_albo.append(doc_albo)
                            except:
                                continue
                        
                        if len(documenti_albo) > 0:
                            logger.info(f"  ✅ Albo Pretorio HTML: {len(documenti_albo)} documenti estratti da {albo_url}")
                            break  # Se trovato, non provare altri URL
                
                except Exception as e:
                    logger.debug(f"  Errore scraping HTML {albo_url}: {e}")
                    continue
            
            # Aggiungi documenti HTML a quelli API
            tutti_documenti.extend(documenti_albo)
            
            # ============================================
            # CONVERSIONE FORMATO STANDARD
            # ============================================
            if len(tutti_documenti) > 0:
                documenti_formattati = []
                for doc in tutti_documenti:
                    # DataTables restituisce array con 9 elementi
                    if isinstance(doc, list) and len(doc) >= 9:
                        # [0]=numero_registro, [1]=anno, [2]=oggetto, [3]=pdf_links, 
                        # [4]=categoria, [5]=tipo, [6]=direzione, [7]=data_inizio, [8]=data_fine
                        pdf_links_str = doc[3] if len(doc) > 3 else ''
                        pdf_url = ''
                        if pdf_links_str and '|' in pdf_links_str:
                            first_pdf = pdf_links_str.split('|')[0].strip()
                            pdf_url = f"{base_url}/albo/{first_pdf}" if first_pdf else ''
                        elif pdf_links_str:
                            pdf_url = f"{base_url}/albo/{pdf_links_str.strip()}"
                        
                        documenti_formattati.append({
                            'numero': f"{doc[0]}/{doc[1]}" if len(doc) > 1 else str(doc[0]) if len(doc) > 0 else 'N/A',
                            'numero_registro': str(doc[0]) if len(doc) > 0 else 'N/A',
                            'anno': str(doc[1]) if len(doc) > 1 else 'N/A',
                            'data': doc[7] if len(doc) > 7 else doc[8] if len(doc) > 8 else 'N/A',
                            'data_inizio': doc[7] if len(doc) > 7 else 'N/A',
                            'data_fine': doc[8] if len(doc) > 8 else 'N/A',
                            'oggetto': doc[2] if len(doc) > 2 else 'N/A',
                            'tipo': doc[5] if len(doc) > 5 else doc[4] if len(doc) > 4 else 'Documento',
                            'categoria': doc[4] if len(doc) > 4 else 'N/A',
                            'direzione': doc[6] if len(doc) > 6 else 'N/A',
                            'link': pdf_url,
                            'fonte': 'API DataTables Sesto Fiorentino'
                        })
                    elif isinstance(doc, list) and len(doc) > 0:
                        # Array con meno elementi
                        documenti_formattati.append({
                            'numero': str(doc[0]) if len(doc) > 0 else 'N/A',
                            'data': doc[1] if len(doc) > 1 else 'N/A',
                            'oggetto': doc[2] if len(doc) > 2 else 'N/A',
                            'tipo': doc[3] if len(doc) > 3 else 'Documento',
                            'link': doc[4] if len(doc) > 4 else '',
                            'fonte': 'API DataTables Sesto Fiorentino'
                        })
                    elif isinstance(doc, dict):
                        # Dict (da HTML o altro)
                        documenti_formattati.append({
                            'numero': doc.get('numero', doc.get('numero_atto', doc.get('numero_registro', 'N/A'))),
                            'data': doc.get('data', doc.get('data_pubblicazione', doc.get('data_adozione', doc.get('data_inizio', 'N/A')))),
                            'oggetto': doc.get('oggetto', doc.get('descrizione', 'N/A')),
                            'tipo': doc.get('tipo', doc.get('tipo_atto', 'Documento')),
                            'link': doc.get('link', doc.get('url', doc.get('pdf_url', ''))),
                            'fonte': doc.get('fonte', 'API DataTables Sesto Fiorentino')
                        })
                
                logger.info(f"  ✅ TOTALE Sesto Fiorentino: {len(documenti_formattati)} documenti pubblici estratti (API + Albo Pretorio)")
                return documenti_formattati
        
        except Exception as e:
            logger.debug(f"  Strategia Sesto Fiorentino fallita: {e}")
        
        return None
    
    async def _count_atti_firenze(self) -> int:
        """Conta velocemente gli atti di Firenze senza estrarli tutti (per dry-run)"""
        try:
            import requests
            
            base_url = "https://accessoconcertificato.comune.fi.it"
            api_url = f"{base_url}/trasparenza-atti-cat/searchAtti"
            
            tipi_atto = [("DG", "Deliberazioni di Giunta"), ("DC", "Deliberazioni di Consiglio"), 
                        ("DD", "Determinazioni Dirigenziali"), ("DS", "Decreti Sindacali"), 
                        ("OD", "Ordinanze Dirigenziali")]
            anni = list(range(2018, 2026))  # 2018-2025
            
            headers = {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept': 'application/json, text/plain, */*',
                'Content-Type': 'application/json',
                'Origin': base_url,
                'Referer': f'{base_url}/trasparenza-atti/',
            }
            
            total_count = 0
            
            # Conta per tipo e anno (la API restituisce TUTTI gli atti in una chiamata, non paginati)
            for tipo_codice, tipo_nome in tipi_atto:
                for anno in anni:
                    payload = {
                        "annoAdozione": str(anno),
                        "tipiAtto": [tipo_codice],
                        "competenza": tipo_codice,
                        "notLoadIniziale": "ok"
                    }
                    
                    try:
                        response = requests.post(api_url, json=payload, headers=headers, timeout=10)
                        if response.status_code == 200:
                            data = response.json()
                            if isinstance(data, list):
                                total_count += len(data)
                    except:
                        continue
            
            return total_count
            
        except Exception as e:
            logger.debug(f"Errore conteggio Firenze: {e}")
            return 0
    
    async def _count_atti_sesto_fiorentino(self) -> int:
        """Conta velocemente gli atti di Sesto Fiorentino senza estrarli tutti (per dry-run)"""
        try:
            import requests
            
            base_url = "http://servizi.comune.sesto-fiorentino.fi.it"
            api_url = f"{base_url}/albo/search.php"
            
            headers = {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept': 'application/json, text/plain, */*',
            }
            
            # Una chiamata per vedere il totale senza paginazione completa
            params = {
                'draw': '1',
                'start': '0',
                'length': '1',  # Solo per vedere recordsTotal
            }
            
            try:
                response = requests.get(api_url, params=params, headers=headers, timeout=10)
                if response.status_code == 200:
                    data = response.json()
                    if isinstance(data, dict):
                        return data.get('recordsTotal', 0)
            except:
                pass
            
            return 0
            
        except Exception as e:
            logger.debug(f"Errore conteggio Sesto Fiorentino: {e}")
            return 0
    
    def _save_json_backup(self, comune_slug: str, atti_list: List[Dict], tenant_id: Optional[int] = None) -> Optional[str]:
        """
        Salva backup JSON degli atti estratti (come gli altri scraper)
        
        Args:
            comune_slug: Slug del comune
            atti_list: Lista completa di tutti gli atti estratti
            tenant_id: ID tenant (opzionale)
            
        Returns:
            Path del file JSON salvato (relativo, come pattern altri scraper)
        """
        try:
            if not atti_list:
                return None
            
            # Formato file: atti_{comune_slug}_{timestamp}.json (come altri scraper)
            timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
            json_filename = f"atti_{comune_slug}_{timestamp}.json"
            json_path = self.output_dir / "json" / json_filename
            
            # Salva JSON con tutti gli atti (non solo primi 10)
            with open(json_path, 'w', encoding='utf-8') as f:
                json.dump(atti_list, f, indent=2, ensure_ascii=False)
            
            # Restituisci path relativo (come gli altri scraper)
            # Pattern: storage/testing/compliance_scanner/json/atti_{comune}_{timestamp}.json
            relative_path = f"storage/testing/compliance_scanner/json/{json_filename}"
            
            return relative_path
            
        except Exception as e:
            logger.error(f"Errore salvataggio JSON backup: {e}")
            return None

