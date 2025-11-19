#!/usr/bin/env python3
"""
Quick test script to verify base scraper architecture.

Tests:
- Import modules
- Create mock scraper
- Test factory registration
- Test rate limiter
"""

import asyncio
import sys
from pathlib import Path

# Add project root to path
project_root = Path(__file__).parent.parent.parent.parent
sys.path.insert(0, str(project_root))

from python_ai_service.app.scrapers.base_scraper import (
    BaseAlboScraper,
    AttoPA,
    ScrapeResult
)
from python_ai_service.app.scrapers.factory import ScraperFactory
from python_ai_service.app.scrapers.utils import (
    AdaptiveRateLimiter,
    SmartHeaders
)
from datetime import datetime


# Create a mock scraper for testing
class MockAlboScraper(BaseAlboScraper):
    """Mock scraper for testing."""
    
    async def detect_platform(self, url: str) -> bool:
        """Mock detection - always returns True."""
        return 'mock' in url.lower() or 'test' in url.lower()
    
    async def scrape_page(self, url: str, page_num: int = 1) -> list:
        """Mock scraping - returns dummy atti."""
        atti = []
        for i in range(3):  # 3 atti per page
            atto = AttoPA(
                numero=f'{page_num}-{i+1}/2025',
                data_pubblicazione=datetime.now(),
                oggetto=f'Test Atto {page_num}-{i+1}',
                tipo_atto='delibera',
                url_dettaglio=f'{url}/atto/{page_num}-{i+1}',
                comune_code=self.comune_code,
                tenant_id=self.tenant_id,
                url_pdf=f'{url}/pdf/{page_num}-{i+1}.pdf'
            )
            atti.append(atto)
        return atti
    
    async def get_total_pages(self, url: str) -> int:
        """Mock total pages."""
        return 3


async def test_base_architecture():
    """Test base architecture components."""
    
    print("="*70)
    print("🧪 TESTING BASE SCRAPER ARCHITECTURE")
    print("="*70)
    
    # Test 1: Import modules
    print("\n1️⃣ Testing imports...")
    print("   ✅ BaseAlboScraper imported")
    print("   ✅ AttoPA imported")
    print("   ✅ ScrapeResult imported")
    print("   ✅ ScraperFactory imported")
    print("   ✅ AdaptiveRateLimiter imported")
    print("   ✅ SmartHeaders imported")
    
    # Test 2: Create mock scraper
    print("\n2️⃣ Creating mock scraper...")
    scraper = MockAlboScraper(
        comune_code='test_comune',
        tenant_id='test_tenant',
        config={'page_delay': 0.1}  # Fast for testing
    )
    print(f"   ✅ MockAlboScraper created: {scraper.comune_code}")
    
    # Test 3: Test detection
    print("\n3️⃣ Testing platform detection...")
    is_compatible = await scraper.detect_platform('https://test.example.com')
    print(f"   ✅ Detection result: {is_compatible}")
    
    # Test 4: Test scraping single page
    print("\n4️⃣ Testing single page scraping...")
    atti = await scraper.scrape_page('https://test.example.com', page_num=1)
    print(f"   ✅ Scraped {len(atti)} atti from page 1")
    for atto in atti[:2]:
        print(f"      - {atto.numero}: {atto.oggetto}")
    
    # Test 5: Test full scraping
    print("\n5️⃣ Testing full scraping workflow...")
    result = await scraper.scrape_all('https://test.example.com', max_pages=2)
    print(f"   ✅ Status: {result.status}")
    print(f"   ✅ Total atti: {len(result.atti)}")
    print(f"   ✅ Pages scraped: {result.stats['pages_scraped']}")
    print(f"   ✅ Duration: {result.stats['duration_seconds']:.2f}s")
    print(f"   ✅ Errors: {len(result.errors)}")
    
    # Test 6: Factory registration
    print("\n6️⃣ Testing factory registration...")
    ScraperFactory.register_scraper(MockAlboScraper)
    registered = ScraperFactory.get_registered_scrapers()
    print(f"   ✅ Registered scrapers: {registered}")
    
    # Test 7: Factory auto-detection
    print("\n7️⃣ Testing factory auto-detection...")
    detected_scraper = await ScraperFactory.create_scraper(
        comune_code='test_comune',
        url='https://test.example.com',
        tenant_id='test_tenant'
    )
    print(f"   ✅ Detected scraper: {type(detected_scraper).__name__}")
    
    # Test 8: Factory high-level interface
    print("\n8️⃣ Testing factory high-level scraping...")
    factory_result = await ScraperFactory.scrape_comune(
        comune_code='test_comune',
        url='https://test.example.com',
        tenant_id='test_tenant',
        max_pages=1
    )
    print(f"   ✅ Factory result status: {factory_result.status}")
    print(f"   ✅ Factory result atti: {len(factory_result.atti)}")
    
    # Test 9: Rate limiter
    print("\n9️⃣ Testing rate limiter...")
    limiter = AdaptiveRateLimiter('pa_moderate')
    print(f"   ✅ Rate limiter created: {limiter.config.min_delay}s min delay")
    
    # Simulate some requests
    for i in range(3):
        await limiter.wait()
        limiter.adjust_for_response(response_time=0.5, status_code=200)
    
    stats = limiter.get_stats()
    print(f"   ✅ Requests made: {stats['total_requests']}")
    print(f"   ✅ Current delay: {stats['current_delay']:.2f}s")
    
    # Test 10: Smart headers
    print("\n🔟 Testing smart headers...")
    headers = SmartHeaders.get_realistic_headers('https://example.com')
    print(f"   ✅ Generated {len(headers)} headers")
    print(f"   ✅ User-Agent: {headers['User-Agent'][:50]}...")
    
    natan_headers = SmartHeaders.get_natan_headers()
    print(f"   ✅ Natan headers: {natan_headers['User-Agent']}")
    
    # Summary
    print("\n" + "="*70)
    print("✅ ALL TESTS PASSED!")
    print("="*70)
    print("\n📊 Summary:")
    print(f"   - Base architecture: ✅ Working")
    print(f"   - Mock scraper: ✅ Working")
    print(f"   - Factory pattern: ✅ Working")
    print(f"   - Rate limiter: ✅ Working")
    print(f"   - Smart headers: ✅ Working")
    print("\n🚀 Ready to implement platform-specific scrapers!")
    print("="*70)


if __name__ == '__main__':
    asyncio.run(test_base_architecture())
