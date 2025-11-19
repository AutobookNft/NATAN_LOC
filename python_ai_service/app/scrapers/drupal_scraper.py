"""
DrupalAlboScraper - Scraper for Drupal-based Albo Pretorio sites.

Target comuni (from research):
- Empoli, Prato, Scandicci (23% coverage)

Drupal detection signatures:
- Meta tag: <meta name="Generator" content="Drupal X">
- Path patterns: /sites/default/files/, /node/123
- JavaScript: drupal.js, jquery.js
- CSS: .views-row, .field-content, .pager

Strategy:
- HTML static (no JS rendering needed)
- Use httpx + BeautifulSoup
- Standard Drupal Views selectors
- Pagination: ?page=0 (0-indexed)
"""

import re
from typing import Optional, List, Dict, Any
from datetime import datetime
from urllib.parse import urljoin, urlparse
import logging

import httpx
from bs4 import BeautifulSoup
from dateutil import parser as date_parser

from .base_scraper import BaseAlboScraper, AttoPA, logger
from .utils.smart_headers import SmartHeaders


class DrupalAlboScraper(BaseAlboScraper):
    """
    Scraper for Drupal-based Albo Pretorio sites.
    
    Supports comuni: Empoli, Prato, Scandicci
    Coverage: 23% (3/13 comuni analizzati)
    
    Features:
    - Static HTML parsing (fast, no browser needed)
    - Standard Drupal Views detection
    - Multiple selector fallbacks
    - Smart date parsing (Italian formats)
    - Pagination support (?page=0 pattern)
    """
    
    # Drupal detection signatures
    DRUPAL_SIGNATURES = [
        'Drupal',
        '/sites/default/',
        'drupal.js',
        'node/',
        'views-row',
        'field-content',
        'Powered by Drupal'
    ]
    
    # Common Drupal selectors for atti
    ATTO_SELECTORS = [
        '.views-row',           # Standard Drupal Views
        '.view-content .item',  # Alternative Views
        'article.node',         # Node-based listing
        '.views-table tbody tr' # Table Views
    ]
    
    # Field selectors (flexible for different Drupal configs)
    FIELD_SELECTORS = {
        'numero': ['.field-numero', '.field-name-field-numero', 'td.numero', '[class*="numero"]'],
        'oggetto': ['.field-oggetto', '.field-name-field-oggetto', 'h2 a', '.node-title a', 'td.oggetto'],
        'data': ['.field-data', '.field-name-field-data', 'time', '.date', 'td.data', '[class*="data-pub"]'],
        'tipo': ['.field-tipo', '.field-name-field-tipo', '.tipo-atto', 'td.tipo']
    }
    
    def __init__(self, comune_code: str, tenant_id: str, config: Optional[dict] = None):
        super().__init__(comune_code, tenant_id, config)
        
        # HTTP client setup
        self.timeout = self.config.get('timeout', 30)
        self.session = httpx.AsyncClient(timeout=self.timeout)
        self.headers = {
            'User-Agent': 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language': 'it-IT,it;q=0.9,en;q=0.8',
        }
        
        logger.info(f"DrupalAlboScraper initialized for {comune_code}")
    
    async def detect_platform(self, url: str) -> bool:
        """
        Detect if site is Drupal-based.
        
        Args:
            url: Site URL
            
        Returns:
            True if Drupal detected
        """
        try:
            resp = await self.session.get(url, headers=self.headers, timeout=10)
            html_content = resp.text  # text is a property, not a method
            
            # Check multiple signatures (need at least 2 for confidence)
            matches = sum(1 for sig in self.DRUPAL_SIGNATURES if sig in html_content)
            
            if matches >= 2:
                logger.info(f"✅ Detected Drupal site: {url} ({matches} signatures)")
                return True
            
            logger.debug(f"Not Drupal: {url} ({matches}/2 signatures)")
            return False
        except Exception as e:
            logger.error(f"Error detecting platform for {url}: {e}")
            return False
    
    async def scrape_page(self, url: str, page_num: int) -> List[AttoPA]:
        """
        Scrape single page of Drupal albo.
        
        Args:
            url: Base URL
            page_num: Page number (1-indexed)
            
        Returns:
            List of AttoPA objects
        """
        # Drupal pagination is 0-indexed: ?page=0, ?page=1, ...
        page_url = self._build_page_url(url, page_num)
        
        logger.info(f"Scraping Drupal page {page_num}: {page_url}")
        
        response = await self.session.get(
            page_url,
            headers=self.headers,
            timeout=self.timeout
        )
        response.raise_for_status()
        html = response.text  # property, not method
        
        soup = BeautifulSoup(html, 'lxml')
        atti = await self._parse_atti(soup, url)
        
        logger.info(f"Found {len(atti)} atti on page {page_num}")
        return atti
    
    async def _parse_atti(self, soup: BeautifulSoup, base_url: str) -> List[AttoPA]:
        """
        Parse atti from Drupal page using multiple selector strategies.
        
        Args:
            soup: BeautifulSoup object
            base_url: Base URL for relative links
            
        Returns:
            List of AttoPA objects
        """
        atti = []
        
        # Try each selector until we find atti
        for selector in self.ATTO_SELECTORS:
            elements = soup.select(selector)
            if elements:
                logger.info(f"Found {len(elements)} elements with selector: {selector}")
                
                for elem in elements:
                    try:
                        atto = self._parse_single_atto(elem, base_url, selector)
                        if atto:
                            atti.append(atto)
                    except Exception as e:
                        logger.warning(f"Error parsing atto element: {e}")
                        continue
                
                break  # Stop after first successful selector
        
        if not atti:
            logger.warning("No atti found with any selector!")
            logger.debug(f"Page HTML snippet: {str(soup)[:500]}")
        
        return atti
    
    def _parse_single_atto(self, elem, base_url: str, selector_used: str) -> Optional[AttoPA]:
        """
        Parse single atto element from Drupal Views.
        
        Args:
            elem: BeautifulSoup element
            base_url: Base URL
            selector_used: Selector that found this element
            
        Returns:
            AttoPA object or None
        """
        # Extract fields using flexible selectors
        numero = self._extract_field(elem, self.FIELD_SELECTORS['numero'])
        oggetto = self._extract_field(elem, self.FIELD_SELECTORS['oggetto'])
        data_text = self._extract_field(elem, self.FIELD_SELECTORS['data'])
        tipo = self._extract_field(elem, self.FIELD_SELECTORS['tipo'])
        
        # Extract link (check <a> tags)
        link_elem = elem.select_one('a[href]')
        if link_elem:
            url_dettaglio = urljoin(base_url, link_elem['href'])
        else:
            url_dettaglio = base_url
        
        # Validate minimum required fields
        if not oggetto:
            # Try extracting from link text
            if link_elem:
                oggetto = link_elem.get_text(strip=True)
        
        if not oggetto:
            logger.debug(f"Skipping atto: missing oggetto")
            return None
        
        # Parse date
        data_pubblicazione = self._parse_date(data_text)
        
        # Generate numero if missing (use timestamp)
        if not numero:
            numero = f"atto_{int(data_pubblicazione.timestamp())}"
        
        # Default tipo
        if not tipo:
            tipo = 'atto'
        
        return AttoPA(
            numero=numero,
            oggetto=oggetto[:500],  # Limit length
            data_pubblicazione=data_pubblicazione,
            url_dettaglio=url_dettaglio,
            tipo_atto=tipo,
            comune_code=self.comune_code,
            tenant_id=self.tenant_id,
            metadata={
                'platform': 'drupal',
                'selector_used': selector_used,
                'scrape_date': datetime.now().isoformat()
            }
        )
    
    def _extract_field(self, elem, selectors: List[str]) -> Optional[str]:
        """
        Extract field value trying multiple selectors.
        
        Args:
            elem: BeautifulSoup element
            selectors: List of CSS selectors to try
            
        Returns:
            Extracted text or None
        """
        for selector in selectors:
            found = elem.select_one(selector)
            if found:
                text = found.get_text(strip=True)
                if text:
                    return text
        return None
    
    def _parse_date(self, date_text: Optional[str]) -> datetime:
        """
        Parse Italian date formats.
        
        Common formats:
        - 15/01/2025
        - 15-01-2025
        - 15 gennaio 2025
        - 2025-01-15
        
        Args:
            date_text: Date string
            
        Returns:
            Parsed datetime or now() if parsing fails
        """
        if not date_text:
            return datetime.now()
        
        # Clean text
        date_text = date_text.strip()
        
        try:
            # Try dateutil parser (handles many formats)
            return date_parser.parse(date_text, dayfirst=True)
        except:
            # Fallback patterns
            patterns = [
                r'(\d{1,2})[/-](\d{1,2})[/-](\d{4})',  # DD/MM/YYYY
                r'(\d{4})[/-](\d{1,2})[/-](\d{1,2})',  # YYYY-MM-DD
            ]
            
            for pattern in patterns:
                match = re.search(pattern, date_text)
                if match:
                    try:
                        if pattern.startswith(r'(\d{4})'):  # YYYY-MM-DD
                            year, month, day = match.groups()
                        else:  # DD/MM/YYYY
                            day, month, year = match.groups()
                        
                        return datetime(int(year), int(month), int(day))
                    except:
                        continue
            
            logger.warning(f"Failed to parse date: {date_text}, using now()")
            return datetime.now()
    
    def _build_page_url(self, base_url: str, page_num: int) -> str:
        """
        Build Drupal pagination URL.
        
        Drupal uses 0-indexed pagination: ?page=0, ?page=1, ...
        
        Args:
            base_url: Base URL
            page_num: Page number (1-indexed from scraper)
            
        Returns:
            Page URL with ?page=X parameter
        """
        # Convert to 0-indexed
        page_index = page_num - 1
        
        # Add or append page parameter
        separator = '&' if '?' in base_url else '?'
        return f"{base_url}{separator}page={page_index}"
    
    async def get_total_pages(self, url: str) -> int:
        """
        Determine total pages from Drupal pager.
        
        Drupal pager structure:
        <ul class="pager">
          <li class="pager-item"><a href="?page=0">1</a></li>
          <li class="pager-item"><a href="?page=1">2</a></li>
          ...
        </ul>
        
        Args:
            url: Base URL
            
        Returns:
            Total pages (minimum 1)
        """
        logger.info("Determining total pages from Drupal pager...")
        
        try:
            response = await self.session.get(
                url,
                headers=self.headers,
                timeout=self.timeout
            )
            response.raise_for_status()
            html = response.text
            
            soup = BeautifulSoup(html, 'lxml')
            
            # Strategy 1: Find highest page number in pager
            pager_links = soup.select('.pager a[href*="page="], .pagination a[href*="page="]')
            
            if pager_links:
                max_page = 0
                for link in pager_links:
                    href = link.get('href', '')
                    match = re.search(r'page=(\d+)', href)
                    if match:
                        page_num = int(match.group(1))
                        max_page = max(max_page, page_num)
                
                # Convert from 0-indexed to 1-indexed
                total = max_page + 1
                logger.info(f"Found {total} pages from pager")
                return max(1, total)
            
            # Strategy 2: Check for "last" page link
            last_link = soup.select_one('.pager-last a, .pagination-last a')
            if last_link:
                href = last_link.get('href', '')
                match = re.search(r'page=(\d+)', href)
                if match:
                    total = int(match.group(1)) + 1  # 0-indexed to 1-indexed
                    logger.info(f"Found {total} pages from 'last' link")
                    return total
            
            # Strategy 3: Look for text like "Pagina 1 di 5"
            page_info = soup.select_one('.pager-info, .pagination-info')
            if page_info:
                text = page_info.get_text()
                match = re.search(r'di\s+(\d+)', text, re.IGNORECASE)
                if match:
                    total = int(match.group(1))
                    logger.info(f"Found {total} pages from info text")
                    return total
            
            logger.info("No pagination found, assuming 1 page")
            return 1
            
        except Exception as e:
            logger.error(f"Error determining total pages: {e}")
            return 1
