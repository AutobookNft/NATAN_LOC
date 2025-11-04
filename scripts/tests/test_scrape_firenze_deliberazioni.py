#!/usr/bin/env python3
"""
Unit tests per scrape_firenze_deliberazioni.py
Testa API calls, parsing JSON, integrazione MongoDB, tenant isolation
"""

import pytest
import sys
import json
from pathlib import Path
from unittest.mock import Mock, patch, MagicMock, AsyncMock
from datetime import datetime

# Add scripts directory to path
SCRIPTS_DIR = Path(__file__).parent.parent
sys.path.insert(0, str(SCRIPTS_DIR))

from scrape_firenze_deliberazioni import FirenzeAttiScraper


class TestFirenzeAttiScraper:
    """Test suite per FirenzeAttiScraper"""
    
    @pytest.fixture
    def scraper_json_only(self):
        """Scraper senza MongoDB (solo JSON)"""
        return FirenzeAttiScraper(
            output_dir='/tmp/test_deliberazioni_output',
            use_mongodb=False,
            tenant_id=1
        )
    
    @pytest.fixture
    def scraper_with_mongodb(self):
        """Scraper con MongoDB abilitato"""
        with patch('scrape_firenze_deliberazioni.PAActMongoDBImporter'):
            scraper = FirenzeAttiScraper(
                output_dir='/tmp/test_deliberazioni_output',
                use_mongodb=True,
                tenant_id=1
            )
            return scraper
    
    @pytest.fixture
    def sample_api_response(self):
        """Risposta API di esempio"""
        return [
            {
                'numeroAdozione': '123/2024',
                'tipoAtto': 'Deliberazione di Giunta',
                'oggetto': 'Approvazione piano urbanistico',
                'dataAdozione': '2024-01-15',
                'annoAdozione': 2024,
                'competenza': 'DG',
                'allegati': [
                    {
                        'id': '1',
                        'link': '/documenti/allegato_123.pdf',
                        'contentType': 'application/pdf'
                    }
                ]
            },
            {
                'numeroAdozione': '456/2024',
                'tipoAtto': 'Determinazione Dirigenziale',
                'oggetto': 'Assegnazione contributi sociali',
                'dataAdozione': '2024-02-20',
                'annoAdozione': 2024,
                'competenza': 'DD',
                'allegati': []
            }
        ]
    
    def test_init_json_only(self, scraper_json_only):
        """Test inizializzazione scraper senza MongoDB"""
        assert scraper_json_only.use_mongodb == False
        assert scraper_json_only.tenant_id == 1
        assert scraper_json_only.mongodb_importer is None
        assert scraper_json_only.base_url == "https://accessoconcertificato.comune.fi.it"
    
    def test_init_with_mongodb(self, scraper_with_mongodb):
        """Test inizializzazione scraper con MongoDB"""
        assert scraper_with_mongodb.use_mongodb == True
        assert scraper_with_mongodb.tenant_id == 1
        assert scraper_with_mongodb.mongodb_importer is not None
    
    @patch('scrape_firenze_deliberazioni.requests.Session')
    def test_search_atti_success(self, mock_session, scraper_json_only, sample_api_response):
        """Test ricerca atti via API"""
        mock_response = Mock()
        mock_response.json.return_value = sample_api_response
        mock_response.raise_for_status = Mock()
        mock_session.return_value.post.return_value = mock_response
        
        result = scraper_json_only.search_atti(2024, 'DG')
        
        assert len(result) == 2
        assert result[0]['numeroAdozione'] == '123/2024'
        assert result[1]['numeroAdozione'] == '456/2024'
        
        # Verifica chiamata API corretta
        call_args = mock_session.return_value.post.call_args
        assert call_args[0][0] == scraper_json_only.api_url
        payload = call_args[1]['json']
        assert payload['annoAdozione'] == '2024'
        assert payload['competenza'] == 'DG'
    
    @patch('scrape_firenze_deliberazioni.requests.Session')
    def test_search_atti_empty(self, mock_session, scraper_json_only):
        """Test ricerca atti senza risultati"""
        mock_response = Mock()
        mock_response.json.return_value = []
        mock_response.raise_for_status = Mock()
        mock_session.return_value.post.return_value = mock_response
        
        result = scraper_json_only.search_atti(2024, 'DG')
        
        assert result == []
    
    @patch('scrape_firenze_deliberazioni.requests.Session')
    def test_search_atti_error(self, mock_session, scraper_json_only):
        """Test gestione errori ricerca API"""
        mock_session.return_value.post.side_effect = Exception("API error")
        
        result = scraper_json_only.search_atti(2024, 'DG')
        
        assert result == []
    
    @pytest.mark.asyncio
    async def test_import_atto_to_mongodb_success(self, scraper_with_mongodb, sample_api_response):
        """Test import atto API in MongoDB con successo"""
        atto_api = sample_api_response[0]
        
        scraper_with_mongodb.mongodb_importer.import_atto = AsyncMock(return_value=True)
        
        result = await scraper_with_mongodb.import_atto_to_mongodb(atto_api)
        
        assert result == True
        scraper_with_mongodb.mongodb_importer.import_atto.assert_called_once()
        
        # Verifica mapping corretto
        call_args = scraper_with_mongodb.mongodb_importer.import_atto.call_args
        atto_mongodb = call_args[1]['atto_data']
        
        assert atto_mongodb['numero_atto'] == '123/2024'
        assert atto_mongodb['tipo_atto'] == 'Deliberazione di Giunta'
        assert atto_mongodb['oggetto'] == 'Approvazione piano urbanistico'
        assert atto_mongodb['data_atto'] == '2024-01-15'
        assert atto_mongodb['anno'] == 2024
        assert atto_mongodb['competenza'] == 'DG'
        assert atto_mongodb['scraper_type'] == 'firenze_deliberazioni'
        assert call_args[1]['ente'] == 'Comune di Firenze'
    
    @pytest.mark.asyncio
    async def test_import_atto_to_mongodb_pdf_url(self, scraper_with_mongodb, sample_api_response):
        """Test estrazione URL PDF da allegati"""
        atto_api = sample_api_response[0]
        
        scraper_with_mongodb.mongodb_importer.import_atto = AsyncMock(return_value=True)
        
        await scraper_with_mongodb.import_atto_to_mongodb(atto_api)
        
        call_args = scraper_with_mongodb.mongodb_importer.import_atto.call_args
        pdf_url = call_args[1]['pdf_url']
        
        assert pdf_url is not None
        assert 'allegato_123.pdf' in pdf_url or '/documenti/allegato_123.pdf' in pdf_url
    
    @pytest.mark.asyncio
    async def test_import_atto_to_mongodb_no_pdf(self, scraper_with_mongodb, sample_api_response):
        """Test atto senza PDF allegato"""
        atto_api = sample_api_response[1]  # Senza allegati
        
        scraper_with_mongodb.mongodb_importer.import_atto = AsyncMock(return_value=True)
        
        await scraper_with_mongodb.import_atto_to_mongodb(atto_api)
        
        call_args = scraper_with_mongodb.mongodb_importer.import_atto.call_args
        pdf_url = call_args[1]['pdf_url']
        
        assert pdf_url is None
    
    @pytest.mark.asyncio
    async def test_import_atto_to_mongodb_error_handling(self, scraper_with_mongodb, sample_api_response):
        """Test gestione errori durante import MongoDB"""
        atto_api = sample_api_response[0]
        
        scraper_with_mongodb.mongodb_importer.import_atto = AsyncMock(side_effect=Exception("MongoDB error"))
        
        result = await scraper_with_mongodb.import_atto_to_mongodb(atto_api)
        
        assert result == False
    
    @pytest.mark.asyncio
    async def test_import_atto_to_mongodb_no_importer(self, scraper_json_only, sample_api_response):
        """Test import MongoDB quando importer non disponibile"""
        atto_api = sample_api_response[0]
        
        result = await scraper_json_only.import_atto_to_mongodb(atto_api)
        
        assert result == False
    
    def test_tenant_id_isolation(self):
        """Test che ogni scraper usa tenant_id corretto"""
        scraper_tenant_1 = FirenzeAttiScraper(tenant_id=1, use_mongodb=False)
        scraper_tenant_2 = FirenzeAttiScraper(tenant_id=2, use_mongodb=False)
        
        assert scraper_tenant_1.tenant_id == 1
        assert scraper_tenant_2.tenant_id == 2


class TestAPIIntegration:
    """Test integrazione API"""
    
    @patch('scrape_firenze_deliberazioni.requests.Session')
    def test_api_payload_structure(self, mock_session):
        """Test struttura payload API corretta"""
        scraper = FirenzeAttiScraper(use_mongodb=False, tenant_id=1)
        
        mock_response = Mock()
        mock_response.json.return_value = []
        mock_response.raise_for_status = Mock()
        mock_session.return_value.post.return_value = mock_response
        
        scraper.search_atti(2024, 'DG')
        
        call_args = mock_session.return_value.post.call_args
        payload = call_args[1]['json']
        
        # Verifica struttura payload
        assert 'oggetto' in payload
        assert 'notLoadIniziale' in payload
        assert 'numeroAdozione' in payload
        assert 'competenza' in payload
        assert 'annoAdozione' in payload
        assert 'tipiAtto' in payload
        
        assert payload['competenza'] == 'DG'
        assert payload['annoAdozione'] == '2024'
        assert payload['tipiAtto'] == ['DG']
    
    @patch('scrape_firenze_deliberazioni.requests.Session')
    def test_api_headers(self, mock_session):
        """Test headers HTTP corretti"""
        scraper = FirenzeAttiScraper(use_mongodb=False, tenant_id=1)
        
        mock_response = Mock()
        mock_response.json.return_value = []
        mock_response.raise_for_status = Mock()
        mock_session.return_value.post.return_value = mock_response
        
        scraper.search_atti(2024, 'DG')
        
        # Verifica session headers
        session_instance = mock_session.return_value
        assert 'User-Agent' in session_instance.headers
        assert 'Accept' in session_instance.headers
        assert 'Content-Type' in session_instance.headers


class TestDataTransformation:
    """Test trasformazione dati API -> MongoDB"""
    
    @pytest.mark.asyncio
    async def test_complete_atto_mapping(self):
        """Test mappatura completa atto API -> MongoDB"""
        with patch('scrape_firenze_deliberazioni.PAActMongoDBImporter'):
            scraper = FirenzeAttiScraper(use_mongodb=True, tenant_id=1)
            
            atto_api_complete = {
                'numeroAdozione': '789/2024',
                'tipoAtto': 'Deliberazione di Consiglio',
                'oggetto': 'Approvazione bilancio 2024',
                'dataAdozione': '2024-03-10',
                'annoAdozione': 2024,
                'competenza': 'DC',
                'allegati': [
                    {
                        'id': '1',
                        'link': '/documenti/bilancio_2024.pdf',
                        'contentType': 'application/pdf'
                    },
                    {
                        'id': '2',
                        'link': '/documenti/allegato.xlsx',
                        'contentType': 'application/vnd.ms-excel'
                    }
                ]
            }
            
            scraper.mongodb_importer.import_atto = AsyncMock(return_value=True)
            
            await scraper.import_atto_to_mongodb(atto_api_complete)
            
            call_args = scraper.mongodb_importer.import_atto.call_args
            atto_mongodb = call_args[1]['atto_data']
            
            # Verifica mapping completo
            assert atto_mongodb['numero_atto'] == '789/2024'
            assert atto_mongodb['tipo_atto'] == 'Deliberazione di Consiglio'
            assert atto_mongodb['oggetto'] == 'Approvazione bilancio 2024'
            assert atto_mongodb['data_atto'] == '2024-03-10'
            assert atto_mongodb['anno'] == 2024
            assert atto_mongodb['competenza'] == 'DC'
            assert atto_mongodb['scraper_type'] == 'firenze_deliberazioni'
            
            # Verifica PDF URL (primo PDF trovato)
            pdf_url = call_args[1]['pdf_url']
            assert pdf_url is not None
            assert 'bilancio_2024.pdf' in pdf_url
    
    @pytest.mark.asyncio
    async def test_missing_fields_handling(self):
        """Test gestione campi mancanti in risposta API"""
        with patch('scrape_firenze_deliberazioni.PAActMongoDBImporter'):
            scraper = FirenzeAttiScraper(use_mongodb=True, tenant_id=1)
            
            atto_api_minimal = {
                'numeroAdozione': '999/2024',
                # Altri campi mancanti
            }
            
            scraper.mongodb_importer.import_atto = AsyncMock(return_value=True)
            
            result = await scraper.import_atto_to_mongodb(atto_api_minimal)
            
            assert result == True
            call_args = scraper.mongodb_importer.import_atto.call_args
            atto_mongodb = call_args[1]['atto_data']
            
            # Campi mancanti dovrebbero essere stringhe vuote
            assert atto_mongodb['numero_atto'] == '999/2024'
            assert atto_mongodb['tipo_atto'] == ''
            assert atto_mongodb['oggetto'] == ''
            assert atto_mongodb['scraper_type'] == 'firenze_deliberazioni'


class TestMongoDBIntegration:
    """Test integrazione MongoDB"""
    
    @pytest.mark.asyncio
    async def test_mongodb_importer_initialization(self):
        """Test inizializzazione MongoDB importer"""
        with patch('scrape_firenze_deliberazioni.PAActMongoDBImporter') as mock_importer_class:
            mock_importer = Mock()
            mock_importer_class.return_value = mock_importer
            
            scraper = FirenzeAttiScraper(use_mongodb=True, tenant_id=1)
            
            assert scraper.use_mongodb == True
            assert scraper.mongodb_importer is not None
            mock_importer_class.assert_called_once_with(tenant_id=1, dry_run=False)
    
    @pytest.mark.asyncio
    async def test_mongodb_importer_init_error(self):
        """Test fallback quando MongoDB importer fallisce"""
        with patch('scrape_firenze_deliberazioni.PAActMongoDBImporter', side_effect=ImportError("Module not found")):
            scraper = FirenzeAttiScraper(use_mongodb=True, tenant_id=1)
            
            # Dovrebbe fallback a JSON-only
            assert scraper.use_mongodb == False
            assert scraper.mongodb_importer is None


if __name__ == '__main__':
    pytest.main([__file__, '-v'])
