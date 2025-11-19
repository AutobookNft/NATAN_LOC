"""
Base scraper module: abstract classes and data structures.

This module defines the core architecture for the multi-tenant scraping system:
- AttoPA: Standardized data structure for PA acts
- ScrapeResult: Result wrapper with status and errors
- BaseAlboScraper: Abstract base class for all platform-specific scrapers
"""

from abc import ABC, abstractmethod
from dataclasses import dataclass, field
from datetime import datetime
from typing import List, Dict, Any, Optional
import asyncio
import json
import logging
from pathlib import Path


# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


@dataclass
class AttoPA:
    """
    Standardized structure for a PA (Public Administration) act.
    
    This dataclass represents a single document from an Albo Pretorio,
    normalized across different platform implementations.
    """
    numero: str                              # Act number (e.g., "123/2024")
    data_pubblicazione: datetime             # Publication date
    oggetto: str                             # Subject/description
    tipo_atto: str                          # Act type (delibera, determinazione, etc.)
    url_dettaglio: str                      # Detail page URL
    comune_code: str                        # Municipality code (e.g., "firenze")
    tenant_id: str                          # Multi-tenant identifier
    
    data_scadenza: Optional[datetime] = None  # Expiration date (if applicable)
    url_pdf: Optional[str] = None            # Direct PDF URL (if available)
    metadata: Dict[str, Any] = field(default_factory=dict)  # Platform-specific data
    
    def to_dict(self) -> Dict[str, Any]:
        """Convert to dictionary for JSON serialization."""
        return {
            'numero': self.numero,
            'data_pubblicazione': self.data_pubblicazione.isoformat(),
            'data_scadenza': self.data_scadenza.isoformat() if self.data_scadenza else None,
            'oggetto': self.oggetto,
            'tipo_atto': self.tipo_atto,
            'url_dettaglio': self.url_dettaglio,
            'url_pdf': self.url_pdf,
            'comune_code': self.comune_code,
            'tenant_id': self.tenant_id,
            'metadata': self.metadata
        }


@dataclass
class ScrapeResult:
    """
    Result of a scraping operation.
    
    Contains the list of scraped acts, status, any errors encountered,
    and statistics about the operation.
    """
    status: str                              # 'success', 'partial', 'error'
    atti: List[AttoPA]                      # List of scraped acts
    errors: List[str]                       # List of error messages
    stats: Dict[str, Any]                   # Statistics (duration, counts, etc.)
    
    def to_dict(self) -> Dict[str, Any]:
        """Convert to dictionary for JSON serialization."""
        return {
            'status': self.status,
            'atti': [atto.to_dict() for atto in self.atti],
            'errors': self.errors,
            'stats': self.stats
        }
    
    def save_to_json(self, filepath: str):
        """Save result to JSON file."""
        with open(filepath, 'w', encoding='utf-8') as f:
            json.dump(self.to_dict(), f, indent=2, ensure_ascii=False)


class StructuredLogger:
    """JSON-structured logger for analysis and debugging."""
    
    def __init__(self, log_file: str):
        self.log_file = Path(log_file)
        self.log_file.parent.mkdir(parents=True, exist_ok=True)
    
    def log(self, event_type: str, **kwargs):
        """Log structured event."""
        log_entry = {
            'timestamp': datetime.now().isoformat(),
            'event_type': event_type,
            **kwargs
        }
        
        with open(self.log_file, 'a', encoding='utf-8') as f:
            f.write(json.dumps(log_entry, ensure_ascii=False) + '\n')


class CircuitBreaker:
    """
    Circuit breaker pattern implementation.
    
    Protects against repeatedly trying to scrape failing sites.
    States: CLOSED (normal) -> OPEN (too many failures) -> HALF_OPEN (testing recovery)
    """
    
    def __init__(
        self,
        failure_threshold: int = 5,
        recovery_timeout: int = 300,  # 5 minutes
        success_threshold: int = 2
    ):
        self.failure_threshold = failure_threshold
        self.recovery_timeout = recovery_timeout
        self.success_threshold = success_threshold
        
        self.state = 'CLOSED'
        self.failure_count = 0
        self.success_count = 0
        self.last_failure_time: Optional[datetime] = None
    
    def can_execute(self) -> bool:
        """Check if we can execute a request."""
        if self.state == 'CLOSED':
            return True
        
        if self.state == 'OPEN':
            # Check if recovery timeout has passed
            if self.last_failure_time:
                elapsed = (datetime.now() - self.last_failure_time).total_seconds()
                if elapsed >= self.recovery_timeout:
                    logger.info(f"Circuit breaker: Trying HALF_OPEN after {elapsed:.0f}s")
                    self.state = 'HALF_OPEN'
                    return True
            return False
        
        # HALF_OPEN: allow request to test recovery
        return True
    
    def record_success(self):
        """Record a successful operation."""
        if self.state == 'HALF_OPEN':
            self.success_count += 1
            if self.success_count >= self.success_threshold:
                logger.info("Circuit breaker: CLOSED (recovered)")
                self.state = 'CLOSED'
                self.failure_count = 0
                self.success_count = 0
        elif self.state == 'CLOSED':
            self.failure_count = 0  # Reset failures on success
    
    def record_failure(self):
        """Record a failed operation."""
        self.failure_count += 1
        self.last_failure_time = datetime.now()
        
        if self.state == 'HALF_OPEN':
            logger.warning("Circuit breaker: OPEN (still failing)")
            self.state = 'OPEN'
            self.success_count = 0
        
        elif self.failure_count >= self.failure_threshold:
            logger.error(f"Circuit breaker: OPEN ({self.failure_count} failures)")
            self.state = 'OPEN'


class BaseAlboScraper(ABC):
    """
    Abstract base class for all Albo Pretorio scrapers.
    
    Provides common functionality:
    - Rate limiting
    - Circuit breaker
    - Structured logging
    - Common scraping workflow
    - MongoDB integration (via existing PAActMongoDBImporter)
    
    Platform-specific scrapers must implement:
    - detect_platform(): Check if this scraper can handle a URL
    - scrape_page(): Scrape a single page
    - get_total_pages(): Determine total number of pages
    """
    
    def __init__(
        self,
        comune_code: str,
        tenant_id: str,
        config: Optional[Dict[str, Any]] = None
    ):
        """
        Initialize scraper.
        
        Args:
            comune_code: Municipality code (e.g., 'firenze', 'empoli')
            tenant_id: Multi-tenant identifier (e.g., 'tenant_toscana')
            config: Optional configuration dict with:
                - rate_limit_preset: 'pa_gentle', 'pa_moderate', 'pa_aggressive'
                - max_retries: int
                - timeout: int
                - etc.
        """
        self.comune_code = comune_code
        self.tenant_id = tenant_id
        self.config = config or {}
        
        # Initialize components
        self.circuit_breaker = CircuitBreaker(
            failure_threshold=self.config.get('failure_threshold', 5),
            recovery_timeout=self.config.get('recovery_timeout', 300),
            success_threshold=self.config.get('success_threshold', 2)
        )
        
        log_dir = self.config.get('log_dir', 'logs/scrapers')
        self.logger = StructuredLogger(f'{log_dir}/{comune_code}.jsonl')
        
        logger.info(f"Initialized {self.__class__.__name__} for {comune_code}")
    
    @abstractmethod
    async def detect_platform(self, url: str) -> bool:
        """
        Detect if this scraper can handle the given URL.
        
        Args:
            url: URL to check
            
        Returns:
            True if this scraper is compatible with the URL/platform
        """
        pass
    
    @abstractmethod
    async def scrape_page(self, url: str, page_num: int = 1) -> List[AttoPA]:
        """
        Scrape a single page and return list of acts.
        
        Args:
            url: Base URL to scrape
            page_num: Page number (1-indexed)
            
        Returns:
            List of AttoPA objects found on the page
        """
        pass
    
    @abstractmethod
    async def get_total_pages(self, url: str) -> int:
        """
        Determine total number of pages to scrape.
        
        Args:
            url: Base URL
            
        Returns:
            Total number of pages (minimum 1)
        """
        pass
    
    async def scrape_all(
        self,
        start_url: str,
        max_pages: Optional[int] = None
    ) -> ScrapeResult:
        """
        Scrape all pages (common method for all scrapers).
        
        This method implements the complete scraping workflow:
        1. Platform detection
        2. Get total pages
        3. Scrape each page with rate limiting and error handling
        4. Collect statistics
        
        Args:
            start_url: Starting URL for scraping
            max_pages: Optional limit on number of pages to scrape
            
        Returns:
            ScrapeResult with status, acts, errors, and statistics
        """
        all_atti: List[AttoPA] = []
        errors: List[str] = []
        start_time = datetime.now()
        pages_scraped = 0
        
        try:
            # Step 1: Check platform compatibility
            logger.info(f"Checking platform compatibility for {start_url}")
            if not await self.detect_platform(start_url):
                error_msg = f"Platform not compatible with {self.__class__.__name__}"
                logger.error(error_msg)
                return ScrapeResult(
                    status='error',
                    atti=[],
                    errors=[error_msg],
                    stats={'comune_code': self.comune_code}
                )
            
            logger.info(f"✅ Platform detected: {self.__class__.__name__}")
            
            # Step 2: Get total pages
            total_pages = await self.get_total_pages(start_url)
            if max_pages:
                total_pages = min(total_pages, max_pages)
            
            logger.info(f"Total pages to scrape: {total_pages}")
            self.logger.log(
                'scrape_start',
                url=start_url,
                total_pages=total_pages,
                comune_code=self.comune_code
            )
            
            # Step 3: Scrape each page
            for page_num in range(1, total_pages + 1):
                # Check circuit breaker
                if not self.circuit_breaker.can_execute():
                    error_msg = f"Circuit breaker OPEN at page {page_num}"
                    logger.warning(error_msg)
                    errors.append(error_msg)
                    break
                
                try:
                    logger.info(f"Scraping page {page_num}/{total_pages}...")
                    
                    # Rate limiting (basic sleep for now, can be enhanced)
                    if page_num > 1:
                        await asyncio.sleep(self.config.get('page_delay', 2.0))
                    
                    # Scrape page
                    page_start = datetime.now()
                    atti = await self.scrape_page(start_url, page_num)
                    page_duration = (datetime.now() - page_start).total_seconds()
                    
                    all_atti.extend(atti)
                    pages_scraped = page_num
                    
                    # Record success
                    self.circuit_breaker.record_success()
                    
                    logger.info(f"✅ Page {page_num}: {len(atti)} atti (in {page_duration:.2f}s)")
                    self.logger.log(
                        'page_scraped',
                        url=start_url,
                        page=page_num,
                        atti_count=len(atti),
                        duration=page_duration
                    )
                
                except Exception as e:
                    error_msg = f"Error scraping page {page_num}: {str(e)}"
                    logger.error(error_msg)
                    errors.append(error_msg)
                    
                    # Record failure
                    self.circuit_breaker.record_failure()
                    
                    self.logger.log(
                        'page_error',
                        url=start_url,
                        page=page_num,
                        error=str(e)
                    )
                    
                    # Continue to next page (partial success is OK)
                    continue
            
            # Step 4: Determine final status
            duration = (datetime.now() - start_time).total_seconds()
            
            if not errors:
                status = 'success'
            elif all_atti:
                status = 'partial'  # Some pages worked
            else:
                status = 'error'    # Complete failure
            
            logger.info(
                f"Scraping completed: {status.upper()} - "
                f"{len(all_atti)} atti from {pages_scraped} pages in {duration:.2f}s"
            )
            
            self.logger.log(
                'scrape_complete',
                url=start_url,
                status=status,
                atti_count=len(all_atti),
                pages_scraped=pages_scraped,
                duration=duration,
                errors_count=len(errors)
            )
            
            return ScrapeResult(
                status=status,
                atti=all_atti,
                errors=errors,
                stats={
                    'comune_code': self.comune_code,
                    'tenant_id': self.tenant_id,
                    'total_atti': len(all_atti),
                    'pages_scraped': pages_scraped,
                    'pages_requested': total_pages,
                    'duration_seconds': duration,
                    'errors_count': len(errors),
                    'circuit_breaker_state': self.circuit_breaker.state
                }
            )
        
        except Exception as e:
            error_msg = f"Fatal error: {str(e)}"
            logger.exception(error_msg)
            
            self.logger.log(
                'scrape_fatal_error',
                url=start_url,
                error=str(e),
                comune_code=self.comune_code
            )
            
            return ScrapeResult(
                status='error',
                atti=all_atti,
                errors=[error_msg],
                stats={
                    'comune_code': self.comune_code,
                    'total_atti': len(all_atti),
                    'pages_scraped': pages_scraped
                }
            )
    
    async def save_to_mongodb(self, atti: List[AttoPA]) -> Dict[str, Any]:
        """
        Save acts to MongoDB using existing PAActMongoDBImporter.
        
        Args:
            atti: List of AttoPA objects to save
            
        Returns:
            Dictionary with save statistics
        """
        logger.info(f"Saving {len(atti)} atti to MongoDB...")
        
        try:
            # Import here to avoid circular dependency
            from python_ai_service.app.services.pa_act_mongodb_importer import PAActMongoDBImporter
            
            importer = PAActMongoDBImporter(tenant_id=self.tenant_id)
            
            saved_count = 0
            errors = []
            
            for atto in atti:
                try:
                    # Call existing importer (adjust parameters based on actual method signature)
                    await importer.import_atto(
                        numero=atto.numero,
                        data_pubblicazione=atto.data_pubblicazione,
                        oggetto=atto.oggetto,
                        tipo_atto=atto.tipo_atto,
                        url_dettaglio=atto.url_dettaglio,
                        url_pdf=atto.url_pdf,
                        metadata={
                            **atto.metadata,
                            'comune_code': atto.comune_code,
                            'data_scadenza': atto.data_scadenza.isoformat() if atto.data_scadenza else None
                        }
                    )
                    saved_count += 1
                except Exception as e:
                    error_msg = f"Error saving atto {atto.numero}: {str(e)}"
                    logger.error(error_msg)
                    errors.append(error_msg)
            
            logger.info(f"✅ Saved {saved_count}/{len(atti)} atti to MongoDB")
            
            return {
                'saved_count': saved_count,
                'total_atti': len(atti),
                'errors': errors
            }
        
        except Exception as e:
            error_msg = f"MongoDB save failed: {str(e)}"
            logger.exception(error_msg)
            return {
                'saved_count': 0,
                'total_atti': len(atti),
                'errors': [error_msg]
            }
