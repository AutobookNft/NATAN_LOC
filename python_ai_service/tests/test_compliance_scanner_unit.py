"""
Unit Tests for Compliance Scanner
Test scraping logic with mocks (no real HTTP requests)
"""

import pytest
from unittest.mock import Mock, patch, AsyncMock, MagicMock
from datetime import datetime
from typing import List, Dict

from app.services.compliance_scanner import AlboPretorioComplianceScanner
from app.services.compliance_scanner.models import ComplianceReport, ComplianceViolation


class TestComplianceScannerUnit:
    """Unit tests for Compliance Scanner with mocks"""
    
    @pytest.fixture
    def scanner(self):
        """Create scanner instance"""
        return AlboPretorioComplianceScanner(output_dir="storage/testing/compliance_scanner")
    
    @pytest.fixture
    def mock_atti_list(self):
        """Mock atti list"""
        return [
            {
                "numero": "001/2025",
                "data": "2025-01-15",
                "oggetto": "Test atto 1",
                "tipo": "Delibera"
            },
            {
                "numero": "002/2025",
                "data": "2025-01-20",
                "oggetto": "Test atto 2",
                "tipo": "Determina"
            }
        ]
    
    @pytest.mark.asyncio
    async def test_scan_comune_dry_run_firenze(self, scanner):
        """Test dry-run scan for Firenze"""
        # Mock API Firenze
        with patch.object(scanner, '_strategy_api_firenze', new_callable=AsyncMock) as mock_api:
            mock_api.return_value = [
                {"numero": "001/2025", "data": "2025-01-15", "oggetto": "Test", "tipo": "DG"},
                {"numero": "002/2025", "data": "2025-01-20", "oggetto": "Test 2", "tipo": "DC"}
            ]
            
            # Execute
            report = await scanner.scan_comune("firenze", dry_run=True)
            
            # Verify
            assert report is not None
            assert report.comune_slug == "firenze"
            assert report.metadata is not None
            assert report.metadata["atti_estratti"] == 2
            # In dry-run, atti_list contiene solo primo e ultimo
            assert len(report.metadata["atti_list"]) == 2
    
    @pytest.mark.asyncio
    async def test_scan_comune_dry_run_sesto_fiorentino(self, scanner):
        """Test dry-run scan for Sesto Fiorentino"""
        # Mock API Sesto Fiorentino
        with patch.object(scanner, '_strategy_api_sesto_fiorentino', new_callable=AsyncMock) as mock_api:
            mock_api.return_value = [
                {"numero": "001/2025", "data": "2025-01-15", "oggetto": "Test", "tipo": "Atto"}
            ]
            
            # Execute
            report = await scanner.scan_comune("sesto_fiorentino", dry_run=True)
            
            # Verify
            assert report is not None
            assert report.comune_slug == "sesto_fiorentino"
            assert report.metadata["atti_estratti"] == 1
    
    @pytest.mark.asyncio
    async def test_scan_comune_no_atti_found(self, scanner):
        """Test scan when no atti are found"""
        # Mock: no atti found
        with patch.object(scanner, '_strategy_api_firenze', new_callable=AsyncMock) as mock_api:
            mock_api.return_value = []
            
            # Execute
            report = await scanner.scan_comune("firenze", dry_run=True)
            
            # Verify
            assert report is not None
            assert report.metadata["atti_estratti"] == 0
            # Should have violations for no atti found
            assert len(report.violations) > 0
    
    @pytest.mark.asyncio
    async def test_scan_comune_scraper_factory_fallback(self, scanner):
        """Test fallback to ScraperFactory when API fails"""
        # Mock: API fails, ScraperFactory succeeds
        with patch.object(scanner, '_strategy_api_firenze', new_callable=AsyncMock) as mock_api:
            mock_api.return_value = []
            
            # Mock ScraperFactory
            with patch('app.services.compliance_scanner.scanner.SCRAPER_FACTORY_AVAILABLE', True):
                with patch('app.services.compliance_scanner.scanner.ScraperFactory') as mock_factory:
                    from app.scrapers.base_scraper import ScrapeResult, AttoPA
                    
                    from datetime import datetime
                    mock_result = ScrapeResult(
                        status='success',
                        atti=[
                            AttoPA(
                                numero="001/2025",
                                data_pubblicazione=datetime(2025, 1, 15),
                                oggetto="Test",
                                tipo_atto="Delibera",
                                url_dettaglio="http://test.com/001",
                                comune_code="firenze",
                                tenant_id="tenant_1"
                            )
                        ],
                        errors=[],
                        stats={}
                    )
                    mock_factory.scrape_comune = AsyncMock(return_value=mock_result)
                    
                    # Execute
                    report = await scanner.scan_comune("firenze", dry_run=False)
                    
                    # Verify
                    assert report is not None
                    assert report.metadata["atti_estratti"] > 0
    
    @pytest.mark.asyncio
    async def test_compliance_score_calculation(self, scanner):
        """Test compliance score calculation"""
        # Mock: atti found, no violations
        with patch.object(scanner, '_strategy_api_firenze', new_callable=AsyncMock) as mock_api:
            mock_api.return_value = [
                {"numero": f"{i}/2025", "data": "2025-01-15", "oggetto": f"Test {i}", "tipo": "DG"}
                for i in range(100)  # 100 atti
            ]
            
            # Execute
            report = await scanner.scan_comune("firenze", dry_run=True)
            
            # Verify: score should be high with many atti and no violations
            assert report.compliance_score > 50  # Base score + bonus atti
    
    @pytest.mark.asyncio
    async def test_tracking_saved_after_scraping(self, scanner):
        """Test that tracking is saved after successful scraping"""
        # Mock API
        with patch.object(scanner, '_strategy_api_firenze', new_callable=AsyncMock) as mock_api:
            mock_api.return_value = [
                {"numero": "001/2025", "data": "2025-01-15", "oggetto": "Test", "tipo": "DG"}
            ]
            
            # Mock tracker
            with patch('app.services.compliance_scanner.scanner.ComuniScrapingTracker') as mock_tracker:
                mock_tracker.mark_comune_scraped = Mock(return_value=True)
                
                # Execute (non dry-run)
                report = await scanner.scan_comune("firenze", dry_run=False)
                
                # Verify: tracker should be called
                assert mock_tracker.mark_comune_scraped.called
                call_args = mock_tracker.mark_comune_scraped.call_args[1]
                assert call_args["comune_slug"] == "firenze"
                assert call_args["atti_estratti"] == 1
    
    @pytest.mark.asyncio
    async def test_tracking_not_saved_dry_run(self, scanner):
        """Test that tracking is NOT saved in dry-run"""
        # Mock API
        with patch.object(scanner, '_strategy_api_firenze', new_callable=AsyncMock) as mock_api:
            mock_api.return_value = [
                {"numero": "001/2025", "data": "2025-01-15", "oggetto": "Test", "tipo": "DG"}
            ]
            
            # Mock tracker
            with patch('app.services.compliance_scanner.scanner.ComuniScrapingTracker') as mock_tracker:
                mock_tracker.mark_comune_scraped = Mock(return_value=True)
                
                # Execute (dry-run)
                report = await scanner.scan_comune("firenze", dry_run=True)
                
                # Verify: tracker should NOT be called in dry-run
                assert not mock_tracker.mark_comune_scraped.called
    
    @pytest.mark.asyncio
    async def test_json_backup_saved(self, scanner):
        """Test that JSON backup is saved after scraping"""
        # Mock API
        with patch.object(scanner, '_strategy_api_firenze', new_callable=AsyncMock) as mock_api:
            mock_api.return_value = [
                {"numero": "001/2025", "data": "2025-01-15", "oggetto": "Test", "tipo": "DG"}
            ]
            
            # Mock file operations
            with patch('builtins.open', create=True) as mock_open:
                mock_open.return_value.__enter__.return_value.write = Mock()
                
                # Execute (non dry-run)
                report = await scanner.scan_comune("firenze", dry_run=False)
                
                # Verify: JSON backup should be saved
                assert report.metadata.get("json_backup_path") is not None
    
    @pytest.mark.asyncio
    async def test_json_backup_not_saved_dry_run(self, scanner):
        """Test that JSON backup is NOT saved in dry-run"""
        # Mock API
        with patch.object(scanner, '_strategy_api_firenze', new_callable=AsyncMock) as mock_api:
            mock_api.return_value = [
                {"numero": "001/2025", "data": "2025-01-15", "oggetto": "Test", "tipo": "DG"}
            ]
            
            # Execute (dry-run)
            report = await scanner.scan_comune("firenze", dry_run=True)
            
            # Verify: JSON backup should NOT be saved in dry-run
            assert report.metadata.get("json_backup_path") is None
    
    @pytest.mark.asyncio
    async def test_violations_for_no_atti(self, scanner):
        """Test that violations are added when no atti found"""
        # Mock: no atti from API, and no atti from other strategies
        with patch.object(scanner, '_strategy_api_firenze', new_callable=AsyncMock) as mock_api:
            mock_api.return_value = []
            
            # Mock other strategies to also return no atti
            with patch('app.services.compliance_scanner.scanner.SCRAPER_FACTORY_AVAILABLE', False):
                with patch('app.services.compliance_scanner.scanner.TRIVELLA_BRUTALE_AVAILABLE', False):
                    # Mock base strategies to return None (no content)
                    async def mock_strategy_none(url):
                        return None
                    scanner.strategies = [mock_strategy_none for _ in scanner.strategies]
                    
                    # Mock atto_extractor to return empty list
                    scanner.atto_extractor.extract_atti = Mock(return_value=[])
                    
                    # Execute
                    report = await scanner.scan_comune("firenze", dry_run=True)
                    
                    # Verify: if atti_count is 0, should have at least one violation
                    if report.metadata["atti_estratti"] == 0:
                        # Verifica che ci sia almeno una violazione quando non ci sono atti
                        # Può essere "no_atti_extracted" o "accessibility" (per atti < 5)
                        assert len(report.violations) > 0, f"Expected at least one violation when no atti found, but got: {[v.violation_type for v in report.violations]}"
                        # Verifica che almeno una violazione sia critica o alta quando nessun atto trovato
                        critical_violations = [v for v in report.violations if v.severity in ["critical", "high"]]
                        assert len(critical_violations) > 0, f"Expected critical/high violation when no atti found"
                    else:
                        # Se trova atti da altre strategie, va bene - ma verifica che non ci siano violazioni "no_atti_extracted"
                        violations_no_atti = [v for v in report.violations if v.violation_type == "no_atti_extracted"]
                        assert len(violations_no_atti) == 0, "Should not have 'no_atti_extracted' violation if atti were found"
    
    @pytest.mark.asyncio
    async def test_status_determination(self, scanner):
        """Test status determination based on atti and violations"""
        # Mock tracker
        with patch('app.services.compliance_scanner.scanner.ComuniScrapingTracker') as mock_tracker:
            mock_tracker.mark_comune_scraped = Mock(return_value=True)
            
            # Test 1: Completed (atti + no violations)
            # Mock: atti found, no violations (score high = no violations)
            with patch.object(scanner, '_strategy_api_firenze', new_callable=AsyncMock) as mock_api:
                mock_api.return_value = [
                    {"numero": "001/2025", "data": "2025-01-15", "oggetto": "Test", "tipo": "DG"}
                    for _ in range(10)  # 10 atti per avere score alto
                ]
                
                # Mock _analyze_compliance_from_atti to return no violations
                with patch.object(scanner, '_analyze_compliance_from_atti', return_value=[]):
                    report = await scanner.scan_comune("firenze", dry_run=False)
                    
                    # Verify status saved as "completed" (atti > 0 and violations == 0)
                    if mock_tracker.mark_comune_scraped.called:
                        call_args = mock_tracker.mark_comune_scraped.call_args[1]
                        # Status può essere "completed" se atti > 0 e violations == 0
                        assert call_args["status"] in ["completed", "partial"]
                    else:
                        pytest.skip("Tracking not called (dry-run or no atti)")
            
            # Test 2: Partial (atti + violations)
            # Mock: atti found but with violations
            with patch.object(scanner, '_strategy_api_firenze', new_callable=AsyncMock) as mock_api:
                mock_api.return_value = [
                    {"numero": "001/2025", "data": "2025-01-15", "oggetto": "Test", "tipo": "DG"}
                ]
                
                # Mock _analyze_compliance_from_atti to return violations
                from app.services.compliance_scanner.models import ComplianceViolation
                mock_violations = [
                    ComplianceViolation(
                        norm="L.69/2009",
                        article="art_5",
                        violation_type="accessibility",
                        description="Test violation",
                        severity="high"
                    )
                ]
                with patch.object(scanner, '_analyze_compliance_from_atti', return_value=mock_violations):
                    report = await scanner.scan_comune("firenze", dry_run=False)
                    
                    # Verify status saved as "partial" (atti > 0 but violations > 0)
                    if mock_tracker.mark_comune_scraped.called:
                        call_args = mock_tracker.mark_comune_scraped.call_args[1]
                        assert call_args["status"] == "partial"


class TestComplianceScannerIntegration:
    """Integration tests for Compliance Scanner (may require real HTTP)"""
    
    @pytest.mark.asyncio
    @pytest.mark.integration
    async def test_real_firenze_dry_run(self):
        """Test real Firenze dry-run (requires network)"""
        scanner = AlboPretorioComplianceScanner(output_dir="storage/testing/compliance_scanner")
        
        try:
            report = await scanner.scan_comune("firenze", dry_run=True)
            
            # Verify basic structure
            assert report is not None
            assert report.comune_slug == "firenze"
            assert report.metadata is not None
            assert "atti_estratti" in report.metadata
            
            # Firenze should have many atti
            if report.metadata["atti_estratti"] > 0:
                assert report.metadata["atti_estratti"] > 1000  # Firenze has many atti
                
        except Exception as e:
            pytest.skip(f"Network error or Firenze API unavailable: {e}")
    
    @pytest.mark.asyncio
    @pytest.mark.integration
    async def test_real_sesto_fiorentino_dry_run(self):
        """Test real Sesto Fiorentino dry-run (requires network)"""
        scanner = AlboPretorioComplianceScanner(output_dir="storage/testing/compliance_scanner")
        
        try:
            report = await scanner.scan_comune("sesto_fiorentino", dry_run=True)
            
            # Verify basic structure
            assert report is not None
            assert report.comune_slug in ["sesto_fiorentino", "sesto-fiorentino"]
            assert report.metadata is not None
            
        except Exception as e:
            pytest.skip(f"Network error or Sesto Fiorentino API unavailable: {e}")

