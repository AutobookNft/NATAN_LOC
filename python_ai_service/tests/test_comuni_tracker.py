"""
Unit Tests for ComuniScrapingTracker
Test tracking system for scraped municipalities
"""

import pytest
from unittest.mock import Mock, patch, MagicMock
from datetime import datetime
from typing import Dict, List

from app.services.compliance_scanner.comuni_tracker import ComuniScrapingTracker


class TestComuniScrapingTracker:
    """Test suite for ComuniScrapingTracker"""
    
    @pytest.fixture
    def mock_mongodb_service(self):
        """Mock MongoDBService"""
        with patch('app.services.compliance_scanner.comuni_tracker.MongoDBService') as mock:
            mock.is_connected.return_value = True
            mock.get_collection.return_value = MagicMock()
            mock.find_documents.return_value = []
            mock.insert_document.return_value = "test_id"
            yield mock
    
    def test_mark_comune_scraped_new(self, mock_mongodb_service):
        """Test marking a new comune as scraped"""
        # Setup: no existing comune
        mock_collection = MagicMock()
        mock_collection.find_one.return_value = None  # Comune non esiste
        mock_collection.update_one = MagicMock()
        mock_mongodb_service.get_collection.return_value = mock_collection
        
        # Execute
        result = ComuniScrapingTracker.mark_comune_scraped(
            comune_slug="firenze",
            comune_nome="Firenze",
            atti_estratti=2275,
            compliance_score=0.85,
            violations_count=2,
            metodo_usato="API REST Firenze",
            tenant_id=1,
            status="completed"
        )
        
        # Verify
        assert result is True
        mock_collection.find_one.assert_called_once()
        # Verifica che insert_document sia stato chiamato (tramite MongoDBService)
        assert mock_mongodb_service.is_connected.called
    
    def test_mark_comune_scraped_update(self, mock_mongodb_service):
        """Test updating existing comune tracking"""
        # Setup: comune già esiste
        existing_doc = {
            "_id": "existing_id",
            "comune_slug": "firenze",
            "scraped_at": datetime(2025, 1, 1)
        }
        mock_collection = MagicMock()
        mock_collection.find_one.return_value = existing_doc
        mock_collection.update_one = MagicMock()
        mock_mongodb_service.get_collection.return_value = mock_collection
        
        # Execute
        result = ComuniScrapingTracker.mark_comune_scraped(
            comune_slug="firenze",
            atti_estratti=2400,  # Aggiornato
            tenant_id=1
        )
        
        # Verify
        assert result is True
        mock_collection.update_one.assert_called_once()
    
    def test_is_comune_scraped_true(self, mock_mongodb_service):
        """Test checking if comune is already scraped (returns True)"""
        # Setup: comune trovato
        mock_mongodb_service.find_documents.return_value = [
            {
                "comune_slug": "firenze",
                "status": "completed",
                "scraped_at": datetime.now()
            }
        ]
        
        # Execute
        result = ComuniScrapingTracker.is_comune_scraped("firenze", tenant_id=1)
        
        # Verify
        assert result is True
        mock_mongodb_service.find_documents.assert_called_once()
    
    def test_is_comune_scraped_false(self, mock_mongodb_service):
        """Test checking if comune is scraped (returns False)"""
        # Setup: comune non trovato
        mock_mongodb_service.find_documents.return_value = []
        
        # Execute
        result = ComuniScrapingTracker.is_comune_scraped("empoli", tenant_id=1)
        
        # Verify
        assert result is False
    
    def test_get_scraped_comuni(self, mock_mongodb_service):
        """Test getting list of scraped comuni"""
        # Setup: mock comuni scrapati
        mock_comuni = [
            {
                "comune_slug": "firenze",
                "comune_nome": "Firenze",
                "atti_estratti": 2275,
                "scraped_at": datetime.now()
            },
            {
                "comune_slug": "sesto_fiorentino",
                "comune_nome": "Sesto Fiorentino",
                "atti_estratti": 127,
                "scraped_at": datetime.now()
            }
        ]
        mock_mongodb_service.find_documents.return_value = mock_comuni
        
        # Execute
        result = ComuniScrapingTracker.get_scraped_comuni(tenant_id=1)
        
        # Verify
        assert len(result) == 2
        assert result[0]["comune_slug"] == "firenze"
        assert result[1]["comune_slug"] == "sesto_fiorentino"
    
    def test_get_unscraped_comuni(self, mock_mongodb_service):
        """Test filtering unscraped comuni"""
        # Setup: alcuni comuni già scrapati
        comuni_list = ["firenze", "empoli", "pisa", "prato"]
        mock_mongodb_service.find_documents.return_value = [
            {"comune_slug": "firenze", "status": "completed"},
            {"comune_slug": "pisa", "status": "completed"}
        ]
        
        # Execute
        result = ComuniScrapingTracker.get_unscraped_comuni(comuni_list, tenant_id=1)
        
        # Verify
        assert len(result) == 2
        assert "empoli" in result
        assert "prato" in result
        assert "firenze" not in result
        assert "pisa" not in result
    
    def test_get_comune_info(self, mock_mongodb_service):
        """Test getting detailed info for a comune"""
        # Setup: comune trovato
        mock_info = {
            "comune_slug": "firenze",
            "comune_nome": "Firenze",
            "atti_estratti": 2275,
            "compliance_score": 0.85,
            "scraped_at": datetime.now()
        }
        mock_mongodb_service.find_documents.return_value = [mock_info]
        
        # Execute
        result = ComuniScrapingTracker.get_comune_info("firenze", tenant_id=1)
        
        # Verify
        assert result is not None
        assert result["comune_slug"] == "firenze"
        assert result["atti_estratti"] == 2275
    
    def test_get_comune_info_not_found(self, mock_mongodb_service):
        """Test getting info for non-existent comune"""
        # Setup: comune non trovato
        mock_mongodb_service.find_documents.return_value = []
        
        # Execute
        result = ComuniScrapingTracker.get_comune_info("comune_inesistente", tenant_id=1)
        
        # Verify
        assert result is None
    
    def test_get_comuni_stats(self, mock_mongodb_service):
        """Test getting aggregate statistics"""
        # Setup: mock comuni scrapati
        mock_comuni = [
            {
                "comune_slug": "firenze",
                "atti_estratti": 2275,
                "violations_count": 2,
                "compliance_score": 0.85,
                "status": "completed"
            },
            {
                "comune_slug": "sesto_fiorentino",
                "atti_estratti": 127,
                "violations_count": 1,
                "compliance_score": 0.90,
                "status": "completed"
            }
        ]
        mock_mongodb_service.find_documents.return_value = mock_comuni
        
        # Execute
        stats = ComuniScrapingTracker.get_comuni_stats(tenant_id=1)
        
        # Verify
        assert stats["total_scraped"] == 2
        assert stats["total_atti"] == 2402  # 2275 + 127
        assert stats["total_violations"] == 3  # 2 + 1
        assert stats["comuni_completed"] == 2
        assert stats["avg_compliance_score"] == 0.875  # (0.85 + 0.90) / 2
    
    def test_mark_comune_scraped_mongodb_unavailable(self):
        """Test marking comune when MongoDB is unavailable"""
        # Setup: MongoDB non disponibile
        with patch('app.services.compliance_scanner.comuni_tracker.MongoDBService') as mock:
            mock.is_connected.return_value = False
            
            # Execute
            result = ComuniScrapingTracker.mark_comune_scraped(
                comune_slug="firenze",
                atti_estratti=100
            )
            
            # Verify: ritorna False ma non crasha
            assert result is False
    
    def test_get_unscraped_comuni_all_scraped(self, mock_mongodb_service):
        """Test when all comuni are already scraped"""
        # Setup: tutti i comuni già scrapati
        comuni_list = ["firenze", "empoli"]
        mock_mongodb_service.find_documents.return_value = [
            {"comune_slug": "firenze", "status": "completed"},
            {"comune_slug": "empoli", "status": "completed"}
        ]
        
        # Execute
        result = ComuniScrapingTracker.get_unscraped_comuni(comuni_list, tenant_id=1)
        
        # Verify
        assert len(result) == 0
    
    def test_get_unscraped_comuni_none_scraped(self, mock_mongodb_service):
        """Test when no comuni are scraped yet"""
        # Setup: nessun comune scrapato
        comuni_list = ["firenze", "empoli", "pisa"]
        mock_mongodb_service.find_documents.return_value = []
        
        # Execute
        result = ComuniScrapingTracker.get_unscraped_comuni(comuni_list, tenant_id=1)
        
        # Verify: restituisce tutti i comuni
        assert len(result) == 3
        assert set(result) == set(comuni_list)


class TestComuniScrapingTrackerIntegration:
    """Integration tests for ComuniScrapingTracker (requires MongoDB)"""
    
    @pytest.mark.asyncio
    @pytest.mark.integration
    async def test_tracking_workflow(self):
        """Test complete tracking workflow"""
        # Skip if MongoDB not available
        from app.services.mongodb_service import MongoDBService
        if not MongoDBService.is_connected():
            pytest.skip("MongoDB not available")
        
        comune_slug = "test_comune_tracker"
        test_tenant = 999
        
        try:
            # Step 0: Cleanup any existing test data first
            try:
                collection = MongoDBService.get_collection("scraped_comuni")
                if collection is not None:
                    collection.delete_one({
                        "comune_slug": comune_slug,
                        "tenant_id": test_tenant
                    })
            except:
                pass
            
            # Step 1: Verify comune is not scraped
            assert not ComuniScrapingTracker.is_comune_scraped(comune_slug, tenant_id=test_tenant)
            
            # Step 2: Mark as scraped
            result = ComuniScrapingTracker.mark_comune_scraped(
                comune_slug=comune_slug,
                comune_nome="Test Comune",
                atti_estratti=100,
                compliance_score=0.80,
                violations_count=1,
                metodo_usato="Test Method",
                tenant_id=test_tenant
            )
            assert result is True
            
            # Step 3: Verify comune is now scraped
            assert ComuniScrapingTracker.is_comune_scraped(comune_slug, tenant_id=test_tenant)
            
            # Step 4: Get info
            info = ComuniScrapingTracker.get_comune_info(comune_slug, tenant_id=test_tenant)
            assert info is not None
            assert info["atti_estratti"] == 100
            assert info["compliance_score"] == 0.80
            
            # Step 5: Verify it's filtered from unscraped list
            comuni_list = [comune_slug, "altro_comune"]
            unscraped = ComuniScrapingTracker.get_unscraped_comuni(comuni_list, tenant_id=test_tenant)
            assert comune_slug not in unscraped
            assert "altro_comune" in unscraped
            
        finally:
            # Cleanup: remove test comune from MongoDB
            try:
                collection = MongoDBService.get_collection("scraped_comuni")
                if collection is not None:
                    collection.delete_one({
                        "comune_slug": comune_slug,
                        "tenant_id": test_tenant
                    })
            except:
                pass

