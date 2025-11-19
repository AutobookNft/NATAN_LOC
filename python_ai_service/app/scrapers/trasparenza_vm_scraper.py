"""
Trasparenza VM Scraper - For Trasparenza Valutazione Merito vendor sites.

This scraper handles JavaScript-heavy sites from the "Trasparenza VM" vendor,
used by 4 municipalities in Tuscany (31% coverage):
- Livorno
- Grosseto  
- Pistoia
- Carrara

These sites require Playwright for JS rendering and may have anti-bot protection.
"""

import asyncio
import logging
from typing import List, Optional, Dict, Any
from datetime import datetime
from urllib.parse import urljoin, urlparse

from playwright.async_api import async_playwright, Page, Browser, BrowserContext
from bs4 import BeautifulSoup
from dateutil import parser as date_parser

from .base_scraper import BaseAlboScraper, AttoPA

logger = logging.getLogger(__name__)


class TrasparenzaVMScraper(BaseAlboScraper):
    """
    Scraper for Trasparenza Valutazione Merito vendor sites.
    
    Key Features:
    - JavaScript rendering with Playwright
    - Stealth mode to bypass anti-bot protection
    - Network interception for API discovery
    - Resource blocking for performance
    - Adaptive selectors (sites may vary slightly)
    
    Known Issues:
    - Sites may have Cloudflare or similar protection
    - May require non-headless mode in some cases
    - Timeout issues possible due to slow loading
    """
    
    # Detection signature
    VENDOR_DOMAIN = 'trasparenza-valutazione-merito.it'
    
    # Possible selectors (adaptive - will try multiple)
    ATTO_SELECTORS = [
        '.atto-item',
        '.documento-item',
        '.publication',
        '.albo-atto',
        'div[class*="atto"]',
        'div[class*="pubblicazione"]',
        'div[class*="document"]',
        'table.atti tbody tr',
        '.portlet-content table tbody tr',
    ]
    
    PAGINATION_SELECTORS = [
        '.pagination',
        '.pager',
        'nav[aria-label="pagination"]',
        'div[class*="paginat"]',
    ]
    
    def __init__(self, comune_code: str, tenant_id: str, config: Optional[Dict[str, Any]] = None):
        """
        Initialize Trasparenza VM scraper.
        
        Config options:
            - headless: bool (default True, set False if blocked)
            - block_resources: bool (default True for performance)
            - screenshot_on_error: bool (default False, useful for debugging)
            - stealth_mode: bool (default True)
            - timeout: int (default 30000ms)
        """
        super().__init__(comune_code, tenant_id, config)
        
        self.headless = self.config.get('headless', True)
        self.block_resources = self.config.get('block_resources', True)
        self.screenshot_on_error = self.config.get('screenshot_on_error', False)
        self.stealth_mode = self.config.get('stealth_mode', True)
        self.timeout = self.config.get('timeout', 30000)
        
        logger.info(
            f"TrasparenzaVMScraper initialized: "
            f"headless={self.headless}, stealth={self.stealth_mode}"
        )
    
    async def detect_platform(self, url: str) -> bool:
        """
        Detect if URL is from Trasparenza VM vendor.
        
        Simple check: domain contains 'trasparenza-valutazione-merito.it'
        """
        is_trasparenza_vm = self.VENDOR_DOMAIN in url.lower()
        
        if is_trasparenza_vm:
            logger.info(f"✅ Detected Trasparenza VM vendor in URL: {url}")
        
        return is_trasparenza_vm
    
    async def _create_browser_context(self) -> tuple[Browser, BrowserContext]:
        """
        Create Playwright browser with stealth configuration.
        
        Returns:
            Tuple of (browser, context)
        """
        p = await async_playwright().start()
        
        # Launch browser with anti-detection args
        browser = await p.chromium.launch(
            headless=self.headless,
            args=[
                '--disable-blink-features=AutomationControlled',
                '--disable-dev-shm-usage',
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-infobars',
                '--window-size=1920,1080',
                '--disable-extensions',
                '--disable-gpu',
            ] if self.stealth_mode else []
        )
        
        # Create context with realistic settings
        context = await browser.new_context(
            user_agent='Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            viewport={'width': 1920, 'height': 1080},
            locale='it-IT',
            timezone_id='Europe/Rome',
            geolocation={'latitude': 43.5508, 'longitude': 10.3086},  # Livorno coords
            permissions=['geolocation'],
        )
        
        # Block unnecessary resources for performance
        if self.block_resources:
            await context.route(
                "**/*.{png,jpg,jpeg,gif,svg,css,woff,woff2,ttf,eot}",
                lambda route: route.abort()
            )
        
        return browser, context
    
    async def _setup_stealth_page(self, page: Page):
        """
        Add anti-detection scripts to page.
        
        Args:
            page: Playwright Page object
        """
        if not self.stealth_mode:
            return
        
        await page.add_init_script("""
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
    
    async def scrape_page(self, url: str, page_num: int = 1) -> List[AttoPA]:
        """
        Scrape a single page using Playwright.
        
        Args:
            url: Base URL
            page_num: Page number (1-indexed)
            
        Returns:
            List of AttoPA objects
        """
        logger.info(f"Scraping page {page_num} with Playwright...")
        
        browser = None
        try:
            # Create browser and context
            browser, context = await self._create_browser_context()
            page = await context.new_page()
            
            # Setup stealth
            await self._setup_stealth_page(page)
            
            # Build page URL (adjust pagination pattern as needed)
            page_url = self._build_page_url(url, page_num)
            
            logger.info(f"Navigating to: {page_url}")
            
            # Navigate with timeout
            try:
                response = await page.goto(
                    page_url,
                    wait_until='networkidle',
                    timeout=self.timeout
                )
                
                if not response or response.status != 200:
                    logger.error(f"Bad response: {response.status if response else 'None'}")
                    if self.screenshot_on_error:
                        await page.screenshot(path=f'/tmp/error_page_{page_num}.png')
                    return []
                
            except Exception as e:
                logger.error(f"Navigation timeout/error: {e}")
                if self.screenshot_on_error:
                    try:
                        await page.screenshot(path=f'/tmp/timeout_page_{page_num}.png')
                    except:
                        pass
                return []
            
            # Wait for content to load (adjust selector as needed)
            try:
                # Try to wait for atti container (with shorter timeout)
                await page.wait_for_selector(
                    ', '.join(self.ATTO_SELECTORS),
                    timeout=10000
                )
            except Exception as e:
                logger.warning(f"Atti selector not found: {e}")
                # Continue anyway - might be empty page
            
            # Get page content
            html = await page.content()
            
            # Parse with BeautifulSoup
            soup = BeautifulSoup(html, 'lxml')
            
            # Try to find atti with adaptive selectors
            atti = await self._parse_atti_adaptive(soup, url)
            
            logger.info(f"Found {len(atti)} atti on page {page_num}")
            
            return atti
        
        except Exception as e:
            logger.exception(f"Error scraping page {page_num}: {e}")
            return []
        
        finally:
            if browser:
                try:
                    await browser.close()
                except:
                    pass
    
    def _build_page_url(self, base_url: str, page_num: int) -> str:
        """
        Build URL for specific page number.
        
        Common patterns:
        - ?page=X
        - ?p=X
        - &delta=X (offset-based)
        
        Args:
            base_url: Base URL
            page_num: Page number (1-indexed)
            
        Returns:
            URL for page
        """
        # Default: use ?page= parameter (0-indexed)
        separator = '&' if '?' in base_url else '?'
        return f"{base_url}{separator}page={page_num - 1}"
    
    async def _parse_atti_adaptive(self, soup: BeautifulSoup, base_url: str) -> List[AttoPA]:
        """
        Parse atti using adaptive selectors.
        
        Tries multiple selectors and parsing strategies.
        
        Args:
            soup: BeautifulSoup object
            base_url: Base URL for resolving relative links
            
        Returns:
            List of AttoPA objects
        """
        atti = []
        
        # Try each selector
        for selector in self.ATTO_SELECTORS:
            elements = soup.select(selector)
            
            if not elements:
                continue
            
            logger.info(f"Found {len(elements)} elements with selector: {selector}")
            
            # Try to parse each element
            for elem in elements:
                try:
                    atto = self._parse_single_atto(elem, base_url, selector)
                    if atto:
                        atti.append(atto)
                except Exception as e:
                    logger.warning(f"Error parsing atto: {e}")
                    continue
            
            # If we found atti, stop trying other selectors
            if atti:
                break
        
        if not atti:
            logger.warning("No atti found with any selector!")
            # Log HTML snippet for debugging
            logger.debug(f"Page HTML snippet: {str(soup)[:500]}")
        
        return atti
    
    def _parse_single_atto(self, elem, base_url: str, selector_used: str) -> Optional[AttoPA]:
        """
        Parse a single atto element.
        
        This method uses heuristics to extract data from different
        possible HTML structures.
        
        Args:
            elem: BeautifulSoup element
            base_url: Base URL
            selector_used: Selector that found this element
            
        Returns:
            AttoPA object or None
        """
        # Common field patterns to search for
        numero = self._extract_field(elem, ['numero', 'number', 'id', 'protocollo'])
        oggetto = self._extract_field(elem, ['oggetto', 'object', 'titolo', 'title', 'descrizione'])
        data_text = self._extract_field(elem, ['data', 'date', 'pubblicazione', 'publication'])
        tipo = self._extract_field(elem, ['tipo', 'type', 'tipologia'])
        
        # Extract link
        link_elem = elem.select_one('a[href]')
        url_dettaglio = urljoin(base_url, link_elem['href']) if link_elem else base_url
        
        # If we don't have minimum required fields, skip
        if not numero and not oggetto:
            return None
        
        # Parse date
        data_pubblicazione = datetime.now()
        if data_text:
            try:
                data_pubblicazione = date_parser.parse(data_text, dayfirst=True)
            except:
                logger.warning(f"Could not parse date: {data_text}")
        
        # Build AttoPA
        atto = AttoPA(
            numero=numero or 'N/A',
            data_pubblicazione=data_pubblicazione,
            oggetto=oggetto or 'N/A',
            tipo_atto=tipo or 'atto',
            url_dettaglio=url_dettaglio,
            comune_code=self.comune_code,
            tenant_id=self.tenant_id,
            metadata={
                'platform': 'trasparenza_vm',
                'selector_used': selector_used,
                'vendor': self.VENDOR_DOMAIN
            }
        )
        
        return atto
    
    def _extract_field(self, elem, possible_names: List[str]) -> Optional[str]:
        """
        Extract field value using multiple possible names/classes.
        
        Args:
            elem: BeautifulSoup element
            possible_names: List of possible class/attribute names
            
        Returns:
            Extracted text or None
        """
        for name in possible_names:
            # Check if elem itself has this class
            elem_classes = elem.get('class', [])
            if isinstance(elem_classes, str):
                elem_classes = [elem_classes]
            if any(name in cls for cls in elem_classes):
                text = elem.get_text(strip=True)
                if text:
                    return text
            
            # Try class selector in children
            found = elem.select_one(f'.{name}, [class*="{name}"]')
            if found:
                text = found.get_text(strip=True)
                if text:
                    return text
            
            # Try data attribute
            if elem.get(f'data-{name}'):
                return elem[f'data-{name}']
        
        return None
    
    async def get_total_pages(self, url: str) -> int:
        """
        Determine total number of pages.
        
        Args:
            url: Base URL
            
        Returns:
            Total pages (minimum 1)
        """
        logger.info("Determining total pages...")
        
        browser = None
        try:
            browser, context = await self._create_browser_context()
            page = await context.new_page()
            await self._setup_stealth_page(page)
            
            # Navigate to first page
            await page.goto(url, wait_until='networkidle', timeout=self.timeout)
            
            html = await page.content()
            soup = BeautifulSoup(html, 'lxml')
            
            # Try to find pagination
            for selector in self.PAGINATION_SELECTORS:
                pagination = soup.select_one(selector)
                if pagination:
                    # Try to find last page number
                    page_links = pagination.select('a, button')
                    page_numbers = []
                    
                    for link in page_links:
                        text = link.get_text(strip=True)
                        # Try to extract number
                        if text.isdigit():
                            page_numbers.append(int(text))
                    
                    if page_numbers:
                        total = max(page_numbers)
                        logger.info(f"Found {total} total pages")
                        return total
            
            # Default: assume at least 1 page
            logger.info("Could not determine pagination, assuming 1 page")
            return 1
        
        except Exception as e:
            logger.exception(f"Error determining total pages: {e}")
            return 1
        
        finally:
            if browser:
                try:
                    await browser.close()
                except:
                    pass
