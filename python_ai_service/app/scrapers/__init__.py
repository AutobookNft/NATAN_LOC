"""
Multi-tenant scraper system for Italian PA Albo Pretorio.

This package provides a scalable, modular architecture for scraping
various platforms used by Italian municipalities for their public notices.
"""

from .base_scraper import BaseAlboScraper, AttoPA, ScrapeResult
from .factory import ScraperFactory
from .trasparenza_vm_scraper import TrasparenzaVMScraper
from .drupal_scraper import DrupalAlboScraper

# Auto-register available scrapers
ScraperFactory.register_scraper(TrasparenzaVMScraper)
ScraperFactory.register_scraper(DrupalAlboScraper)

__all__ = [
    'BaseAlboScraper',
    'AttoPA',
    'ScrapeResult',
    'ScraperFactory',
    'TrasparenzaVMScraper',
    'DrupalAlboScraper',
]

__version__ = '1.0.1'
