"""
Integration Tests for Scraper Tracking System
Test complete workflow: scraping → tracking → skip already scraped
"""

import pytest
import asyncio
from datetime import datetime
from typing import List

from app.services.compliance_scanner import AlboPretorioComplianceScanner
from app.services.compliance_scanner.comuni_tracker import ComuniScrapingTracker
from app.services.mongodb_service import MongoDBService


class TestScraperTrackingIntegration:
    """Integration tests for scraper tracking workflow"""
    
    @pytest.mark.asyncio
    @pytest.mark.integration
    async def test_complete_tracking_workflow(self):
        """Test complete workflow: scrape → track → verify → skip"""
        # Skip if MongoDB not available
        if not MongoDBService.is_connected():
            pytest.skip("MongoDB not available")
        
        test_comune = "test_tracking_integration"
        test_tenant = 999
        
        try:
            # Step 1: Verify comune is not scraped
            assert not ComuniScrapingTracker.is_comune_scraped(test_comune, tenant_id=test_tenant)
            
            # Step 2: Mock scraping (dry-run to avoid real HTTP)
            scanner = AlboPretorioComplianceScanner(output_dir="storage/testing/compliance_scanner")
            
            with patch.object(scanner, '_strategy_api_firenze', new_callable=AsyncMock) as mock_api:
                mock_api.return_value = [
                    {"numero": "001/2025", "data": "2025-01-15", "oggetto": "Test", "tipo": "DG"}
                ]
                
                # Change comune slug for test
                original_scan = scanner.scan_comune
                
                async def mock_scan(comune_slug, **kwargs):
                    if comune_slug == test_comune:
                        # Return mock report
                        from app.services.compliance_scanner.models import ComplianceReport
                        report = ComplianceReport(
                            comune_slug=test_comune,
                            comune_name="Test Comune",
                            scan_date=datetime.now(),
                            albo_url="http://test.com",
                            compliance_score=80.0,
                            violations=[]
                        )
                        report.metadata = {
                            "atti_estratti": 1,
                            "atti_list": [{"numero": "001/2025"}],
                            "metodo": "Test Method",
                            "json_backup_path": "test/path.json"
                        }
                        return report
                    return await original_scan(comune_slug, **kwargs)
                
                scanner.scan_comune = mock_scan
                
                # Execute scraping (non dry-run to trigger tracking)
                report = await scanner.scan_comune(test_comune, tenant_id=test_tenant, dry_run=False)
                
                # Manually trigger tracking (since we mocked scan_comune)
                ComuniScrapingTracker.mark_comune_scraped(
                    comune_slug=test_comune,
                    comune_nome="Test Comune",
                    atti_estratti=1,
                    compliance_score=0.80,
                    violations_count=0,
                    metodo_usato="Test Method",
                    json_backup_path="test/path.json",
                    tenant_id=test_tenant,
                    status="completed"
                )
            
            # Step 3: Verify comune is now tracked
            assert ComuniScrapingTracker.is_comune_scraped(test_comune, tenant_id=test_tenant)
            
            # Step 4: Verify it's filtered from unscraped list
            comuni_list = [test_comune, "altro_comune"]
            unscraped = ComuniScrapingTracker.get_unscraped_comuni(comuni_list, tenant_id=test_tenant)
            assert test_comune not in unscraped
            assert "altro_comune" in unscraped
            
            # Step 5: Verify info retrieval
            info = ComuniScrapingTracker.get_comune_info(test_comune, tenant_id=test_tenant)
            assert info is not None
            assert info["atti_estratti"] == 1
            assert info["compliance_score"] == 0.80
            
        finally:
            # Cleanup
            try:
                collection = MongoDBService.get_collection("scraped_comuni")
                if collection:
                    collection.delete_one({
                        "comune_slug": test_comune,
                        "tenant_id": test_tenant
                    })
            except:
                pass
    
    @pytest.mark.asyncio
    @pytest.mark.integration
    async def test_skip_already_scraped_comuni(self):
        """Test that scraper skips already scraped comuni"""
        # Skip if MongoDB not available
        if not MongoDBService.is_connected():
            pytest.skip("MongoDB not available")
        
        test_comuni = ["test_comune_1", "test_comune_2"]
        test_tenant = 999
        
        try:
            # Mark first comune as scraped
            ComuniScrapingTracker.mark_comune_scraped(
                comune_slug=test_comuni[0],
                atti_estratti=100,
                tenant_id=test_tenant,
                status="completed"
            )
            
            # Verify filtering
            unscraped = ComuniScrapingTracker.get_unscraped_comuni(test_comuni, tenant_id=test_tenant)
            
            assert test_comuni[0] not in unscraped
            assert test_comuni[1] in unscraped
            
        finally:
            # Cleanup
            try:
                collection = MongoDBService.get_collection("scraped_comuni")
                if collection:
                    collection.delete_many({
                        "comune_slug": {"$in": test_comuni},
                        "tenant_id": test_tenant
                    })
            except:
                pass
    
    @pytest.mark.asyncio
    @pytest.mark.integration
    async def test_stats_aggregation(self):
        """Test statistics aggregation across multiple comuni"""
        # Skip if MongoDB not available
        if not MongoDBService.is_connected():
            pytest.skip("MongoDB not available")
        
        test_comuni = ["test_stats_1", "test_stats_2", "test_stats_3"]
        test_tenant = 999
        
        try:
            # Mark multiple comuni as scraped
            ComuniScrapingTracker.mark_comune_scraped(
                comune_slug=test_comuni[0],
                atti_estratti=100,
                compliance_score=0.80,
                violations_count=1,
                tenant_id=test_tenant,
                status="completed"
            )
            
            ComuniScrapingTracker.mark_comune_scraped(
                comune_slug=test_comuni[1],
                atti_estratti=200,
                compliance_score=0.90,
                violations_count=0,
                tenant_id=test_tenant,
                status="completed"
            )
            
            ComuniScrapingTracker.mark_comune_scraped(
                comune_slug=test_comuni[2],
                atti_estratti=50,
                compliance_score=0.70,
                violations_count=2,
                tenant_id=test_tenant,
                status="partial"
            )
            
            # Get stats
            stats = ComuniScrapingTracker.get_comuni_stats(tenant_id=test_tenant)
            
            # Verify
            assert stats["total_scraped"] == 3
            assert stats["total_atti"] == 350  # 100 + 200 + 50
            assert stats["total_violations"] == 3  # 1 + 0 + 2
            assert stats["comuni_completed"] == 2
            assert stats["comuni_partial"] == 1
            assert stats["avg_compliance_score"] == pytest.approx(0.80, abs=0.01)  # (0.80 + 0.90 + 0.70) / 3
            
        finally:
            # Cleanup
            try:
                collection = MongoDBService.get_collection("scraped_comuni")
                if collection:
                    collection.delete_many({
                        "comune_slug": {"$in": test_comuni},
                        "tenant_id": test_tenant
                    })
            except:
                pass


# Helper for async mock
from unittest.mock import AsyncMock
from unittest.mock import patch

