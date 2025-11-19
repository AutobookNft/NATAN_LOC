"""
Scraper factory: auto-detection and creation of platform-specific scrapers.

This module implements the Factory pattern for creating appropriate scrapers
based on URL analysis and platform detection.
"""

from typing import Optional, List, Type
import logging

from .base_scraper import BaseAlboScraper, ScrapeResult

logger = logging.getLogger(__name__)


class ScraperFactory:
    """
    Factory for creating and managing platform-specific scrapers.
    
    Responsibilities:
    - Maintain registry of available scrapers
    - Auto-detect platform from URL
    - Create appropriate scraper instance
    - Provide high-level scraping interface
    """
    
    # Registry of available scraper classes
    # Will be populated as we implement platform-specific scrapers
    _SCRAPERS: List[Type[BaseAlboScraper]] = []
    
    @classmethod
    def register_scraper(cls, scraper_class: Type[BaseAlboScraper]):
        """
        Register a new scraper class.
        
        Args:
            scraper_class: Scraper class to register (must inherit from BaseAlboScraper)
        """
        if not issubclass(scraper_class, BaseAlboScraper):
            raise ValueError(f"{scraper_class} must inherit from BaseAlboScraper")
        
        if scraper_class not in cls._SCRAPERS:
            cls._SCRAPERS.append(scraper_class)
            logger.info(f"Registered scraper: {scraper_class.__name__}")
    
    @classmethod
    def get_registered_scrapers(cls) -> List[str]:
        """Get list of registered scraper names."""
        return [scraper.__name__ for scraper in cls._SCRAPERS]
    
    @classmethod
    async def create_scraper(
        cls,
        comune_code: str,
        url: str,
        tenant_id: str,
        config: Optional[dict] = None
    ) -> Optional[BaseAlboScraper]:
        """
        Auto-detect platform and create appropriate scraper.
        
        Tries each registered scraper's detect_platform() method until
        one returns True.
        
        Args:
            comune_code: Municipality code (e.g., 'firenze')
            url: URL to scrape
            tenant_id: Multi-tenant identifier
            config: Optional configuration dict
            
        Returns:
            Appropriate scraper instance, or None if no compatible scraper found
        """
        logger.info(f"Auto-detecting platform for {comune_code}: {url}")
        
        if not cls._SCRAPERS:
            logger.warning("No scrapers registered! Use ScraperFactory.register_scraper()")
            return None
        
        # Try each registered scraper
        for scraper_class in cls._SCRAPERS:
            try:
                # Create temporary instance for detection
                scraper = scraper_class(comune_code, tenant_id, config)
                
                # Check if compatible
                if await scraper.detect_platform(url):
                    logger.info(f"✅ Platform detected: {scraper_class.__name__}")
                    return scraper
            
            except Exception as e:
                logger.warning(f"Error during detection with {scraper_class.__name__}: {e}")
                continue
        
        logger.error(f"❌ No compatible scraper found for {url}")
        return None
    
    @classmethod
    async def scrape_comune(
        cls,
        comune_code: str,
        url: str,
        tenant_id: str,
        max_pages: Optional[int] = None,
        config: Optional[dict] = None,
        save_to_mongodb: bool = False
    ) -> ScrapeResult:
        """
        High-level interface: scrape a municipality with auto-detection.
        
        This is the main entry point for scraping operations.
        
        Args:
            comune_code: Municipality code (e.g., 'empoli')
            url: Albo Pretorio URL
            tenant_id: Multi-tenant identifier
            max_pages: Optional limit on pages to scrape
            config: Optional configuration dict
            save_to_mongodb: If True, automatically save to MongoDB after scraping
            
        Returns:
            ScrapeResult with status, acts, errors, and statistics
        """
        logger.info(f"Starting scrape for {comune_code}")
        logger.info(f"URL: {url}")
        logger.info(f"Tenant: {tenant_id}")
        
        # Create appropriate scraper
        scraper = await cls.create_scraper(comune_code, url, tenant_id, config)
        
        if not scraper:
            return ScrapeResult(
                status='error',
                atti=[],
                errors=[f'No compatible scraper found for {url}'],
                stats={'comune_code': comune_code}
            )
        
        # Scrape
        result = await scraper.scrape_all(url, max_pages=max_pages)
        
        # Optionally save to MongoDB
        if save_to_mongodb and result.atti:
            logger.info("Saving to MongoDB...")
            save_stats = await scraper.save_to_mongodb(result.atti)
            result.stats['mongodb_save'] = save_stats
        
        logger.info(
            f"Scraping completed for {comune_code}: "
            f"{result.status.upper()} - {len(result.atti)} atti"
        )
        
        return result
    
    @classmethod
    async def scrape_multiple(
        cls,
        comuni: List[dict],
        tenant_id: str,
        max_pages: Optional[int] = None,
        config: Optional[dict] = None,
        save_to_mongodb: bool = False
    ) -> dict:
        """
        Scrape multiple municipalities sequentially.
        
        For parallel processing, use DistributedScraperQueue (to be implemented).
        
        Args:
            comuni: List of dicts with 'code' and 'url' keys
            tenant_id: Multi-tenant identifier
            max_pages: Optional limit on pages per comune
            config: Optional configuration dict
            save_to_mongodb: If True, save all results to MongoDB
            
        Returns:
            Dictionary mapping comune_code -> ScrapeResult
        """
        logger.info(f"Scraping {len(comuni)} comuni sequentially")
        
        results = {}
        
        for comune in comuni:
            comune_code = comune['code']
            url = comune['url']
            
            try:
                result = await cls.scrape_comune(
                    comune_code=comune_code,
                    url=url,
                    tenant_id=tenant_id,
                    max_pages=max_pages,
                    config=config,
                    save_to_mongodb=save_to_mongodb
                )
                results[comune_code] = result
            
            except Exception as e:
                logger.exception(f"Fatal error scraping {comune_code}")
                results[comune_code] = ScrapeResult(
                    status='error',
                    atti=[],
                    errors=[f"Fatal error: {str(e)}"],
                    stats={'comune_code': comune_code}
                )
        
        # Summary
        success_count = sum(1 for r in results.values() if r.status == 'success')
        partial_count = sum(1 for r in results.values() if r.status == 'partial')
        error_count = sum(1 for r in results.values() if r.status == 'error')
        total_atti = sum(len(r.atti) for r in results.values())
        
        logger.info(
            f"\n{'='*70}\n"
            f"SCRAPING SUMMARY\n"
            f"{'='*70}\n"
            f"Total comuni: {len(comuni)}\n"
            f"Success: {success_count}\n"
            f"Partial: {partial_count}\n"
            f"Error: {error_count}\n"
            f"Total atti: {total_atti}\n"
            f"{'='*70}"
        )
        
        return results


# Convenience function for CLI usage
async def scrape_comune_cli(
    comune_code: str,
    url: str,
    tenant_id: str = 'tenant_toscana',
    max_pages: Optional[int] = None,
    save_json: Optional[str] = None,
    save_to_mongodb: bool = False
) -> ScrapeResult:
    """
    CLI-friendly scraping function.
    
    Args:
        comune_code: Municipality code
        url: Albo Pretorio URL
        tenant_id: Multi-tenant identifier (default: 'tenant_toscana')
        max_pages: Optional page limit
        save_json: Optional path to save JSON result
        save_to_mongodb: If True, save to MongoDB
        
    Returns:
        ScrapeResult
    """
    result = await ScraperFactory.scrape_comune(
        comune_code=comune_code,
        url=url,
        tenant_id=tenant_id,
        max_pages=max_pages,
        save_to_mongodb=save_to_mongodb
    )
    
    # Save to JSON if requested
    if save_json:
        result.save_to_json(save_json)
        logger.info(f"Result saved to {save_json}")
    
    return result


# Example usage (for documentation)
if __name__ == "__main__":
    import asyncio
    
    async def example():
        """Example usage of ScraperFactory."""
        
        # Single comune
        result = await ScraperFactory.scrape_comune(
            comune_code='empoli',
            url='https://www.empoli.gov.it/albo-pretorio',
            tenant_id='tenant_toscana',
            max_pages=2
        )
        
        print(f"Status: {result.status}")
        print(f"Atti: {len(result.atti)}")
        print(f"Errors: {result.errors}")
        
        # Multiple comuni
        comuni = [
            {'code': 'empoli', 'url': 'https://www.empoli.gov.it/albo-pretorio'},
            {'code': 'prato', 'url': 'https://www.comune.prato.it/albo'},
        ]
        
        results = await ScraperFactory.scrape_multiple(
            comuni=comuni,
            tenant_id='tenant_toscana',
            max_pages=1
        )
        
        for code, result in results.items():
            print(f"{code}: {result.status} - {len(result.atti)} atti")
    
    # asyncio.run(example())
    print("Import this module and use ScraperFactory.scrape_comune() or scrape_multiple()")
