#!/usr/bin/env python3
"""
Unit tests per scrape_albo_firenze_v2.py
Testa parsing HTML, integrazione MongoDB, tenant isolation
"""

import pytest
import sys
import os
from pathlib import Path
from unittest.mock import Mock, patch, MagicMock, AsyncMock
from bs4 import BeautifulSoup
from datetime import datetime

# Add scripts directory to path
SCRIPTS_DIR = Path(__file__).parent.parent
sys.path.insert(0, str(SCRIPTS_DIR))

from scrape_albo_firenze_v2 import AlboPretorioFirenze


class TestAlboPretorioFirenze:
    """Test suite per AlboPretorioFirenze scraper"""
    
    @pytest.fixture
    def scraper_json_only(self):
        """Scraper senza MongoDB (solo JSON)"""
        return AlboPretorioFirenze(
            output_dir='/tmp/test_albo_output',
            use_mongodb=False,
            tenant_id=1
        )
    
    @pytest.fixture
    def scraper_with_mongodb(self):
        """Scraper con MongoDB abilitato"""
        with patch('scrape_albo_firenze_v2.PAActMongoDBImporter'):
            scraper = AlboPretorioFirenze(
                output_dir='/tmp/test_albo_output',
                use_mongodb=True,
                tenant_id=1
            )
            return scraper
    
    @pytest.fixture
    def sample_html_page(self):
        """HTML di esempio per test parsing"""
        return """
        <html>
        <body>
            <div class="card concorso-card multi-line">
                <div>
                    <strong>Delibera</strong>
                    <span>Direzione Servizi Tecnici</span>
                    <p>N° registro 123/2024</p>
                    <p>N° atto 456/2024</p>
                    <p>Inizio pubblicazione 01/01/2024</p>
                    <p>Fine pubblicazione 31/01/2024</p>
                    <p>Oggetto: Approvazione progetto urbanistico per il centro storico</p>
                    <a href="/documenti/atto_123.pdf">Scarica PDF</a>
                </div>
            </div>
            <div class="card concorso-card multi-line">
                <div>
                    <strong>Determinazione</strong>
                    <span>Direzione Servizi Sociali</span>
                    <p>N° registro 789/2024</p>
                    <p>N° atto 012/2024</p>
                    <p>Inizio pubblicazione 15/02/2024</p>
                    <p>Fine pubblicazione 28/02/2024</p>
                    <p>Oggetto: Assegnazione contributo per progetti sociali anno 2024</p>
                    <a href="/documenti/atto_789.pdf">Scarica PDF</a>
                </div>
            </div>
        </body>
        </html>
        """
    
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
    
    def test_parse_atto_valid(self, scraper_json_only, sample_html_page):
        """Test parsing atto valido"""
        soup = BeautifulSoup(sample_html_page, 'html.parser')
        atto_div = soup.find('div', class_='card concorso-card multi-line')
        
        atto_data = scraper_json_only.parse_atto(atto_div)
        
        assert atto_data is not None
        assert atto_data['tipo_atto'] == 'Delibera'
        assert atto_data['direzione'] == 'Direzione Servizi Tecnici'
        assert atto_data['numero_registro'] == '123/2024'
        assert atto_data['numero_atto'] == '456/2024'
        assert atto_data['data_inizio'] == '01/01/2024'
        assert atto_data['data_fine'] == '31/01/2024'
        assert 'Approval' in atto_data['oggetto'] or 'progetto urbanistico' in atto_data['oggetto']
        assert len(atto_data['pdf_links']) > 0
        assert atto_data['scraper_type'] == 'albo_firenze_v2'
    
    def test_parse_atto_multiple(self, scraper_json_only, sample_html_page):
        """Test parsing multipli atti"""
        soup = BeautifulSoup(sample_html_page, 'html.parser')
        atti_divs = soup.find_all('div', class_='card concorso-card multi-line')
        
        atti = [scraper_json_only.parse_atto(div) for div in atti_divs]
        
        assert len(atti) == 2
        assert atti[0]['tipo_atto'] == 'Delibera'
        assert atti[1]['tipo_atto'] == 'Determinazione'
    
    def test_parse_atto_missing_fields(self, scraper_json_only):
        """Test parsing atto con campi mancanti"""
        html_minimal = """
        <div class="card concorso-card multi-line">
            <p>Oggetto: Test minimale</p>
        </div>
        """
        soup = BeautifulSoup(html_minimal, 'html.parser')
        atto_div = soup.find('div', class_='card concorso-card multi-line')
        
        atto_data = scraper_json_only.parse_atto(atto_div)
        
        assert atto_data is not None
        assert atto_data['tipo_atto'] == '' or 'Test minimale' in atto_data['oggetto']
        assert atto_data['scraper_type'] == 'albo_firenze_v2'
    
    def test_get_total_pages(self, scraper_json_only):
        """Test estrazione numero totale pagine"""
        html_with_pagination = """
        <html>
        <body>
            <span>Pagina 1 di 5</span>
        </body>
        </html>
        """
        total = scraper_json_only.get_total_pages(html_with_pagination)
        assert total == 5
    
    def test_get_total_pages_no_pagination(self, scraper_json_only):
        """Test estrazione pagine quando non presente"""
        html_no_pagination = "<html><body>Nessuna paginazione</body></html>"
        total = scraper_json_only.get_total_pages(html_no_pagination)
        assert total == 1
    
    def test_scrape_page(self, scraper_json_only, sample_html_page):
        """Test estrazione atti da pagina HTML"""
        atti = scraper_json_only.scrape_page(sample_html_page)
        
        assert len(atti) == 2
        assert all(atto.get('numero_registro') for atto in atti)
    
    @pytest.mark.asyncio
    async def test_import_atto_to_mongodb_success(self, scraper_with_mongodb):
        """Test import atto in MongoDB con successo"""
        atto_data = {
            'tipo_atto': 'Delibera',
            'numero_registro': '123/2024',
            'numero_atto': '456/2024',
            'data_inizio': '01/01/2024',
            'oggetto': 'Test oggetto',
            'pdf_links': [{'url': 'http://test.com/doc.pdf'}],
            'scraper_type': 'albo_firenze_v2'
        }
        
        # Mock MongoDB importer
        scraper_with_mongodb.mongodb_importer.import_atto = AsyncMock(return_value=True)
        
        result = await scraper_with_mongodb.import_atto_to_mongodb(atto_data)
        
        assert result == True
        scraper_with_mongodb.mongodb_importer.import_atto.assert_called_once()
        
        # Verifica chiamata corretta
        call_args = scraper_with_mongodb.mongodb_importer.import_atto.call_args
        assert call_args[1]['ente'] == 'Comune di Firenze'
        assert call_args[1]['atto_data']['numero_atto'] == '456/2024'
        assert call_args[1]['atto_data']['tipo_atto'] == 'Delibera'
    
    @pytest.mark.asyncio
    async def test_import_atto_to_mongodb_no_importer(self, scraper_json_only):
        """Test import MongoDB quando importer non disponibile"""
        atto_data = {'numero_atto': '123/2024'}
        
        result = await scraper_json_only.import_atto_to_mongodb(atto_data)
        
        assert result == False
    
    @pytest.mark.asyncio
    async def test_import_atto_to_mongodb_error_handling(self, scraper_with_mongodb):
        """Test gestione errori durante import MongoDB"""
        atto_data = {
            'numero_atto': '123/2024',
            'tipo_atto': 'Delibera',
            'oggetto': 'Test',
            'scraper_type': 'albo_firenze_v2'
        }
        
        # Mock errore MongoDB
        scraper_with_mongodb.mongodb_importer.import_atto = AsyncMock(side_effect=Exception("MongoDB error"))
        
        result = await scraper_with_mongodb.import_atto_to_mongodb(atto_data)
        
        assert result == False
    
    @pytest.mark.asyncio
    async def test_import_atto_to_mongodb_anno_parsing(self, scraper_with_mongodb):
        """Test parsing anno da data_inizio"""
        atto_data = {
            'numero_atto': '123/2024',
            'data_inizio': '15/03/2024',
            'tipo_atto': 'Delibera',
            'oggetto': 'Test',
            'scraper_type': 'albo_firenze_v2'
        }
        
        scraper_with_mongodb.mongodb_importer.import_atto = AsyncMock(return_value=True)
        
        await scraper_with_mongodb.import_atto_to_mongodb(atto_data)
        
        call_args = scraper_with_mongodb.mongodb_importer.import_atto.call_args
        assert call_args[1]['atto_data']['anno'] == 2024
    
    @pytest.mark.asyncio
    async def test_import_atto_to_mongodb_anno_invalid(self, scraper_with_mongodb):
        """Test gestione data_inizio non valida"""
        atto_data = {
            'numero_atto': '123/2024',
            'data_inizio': 'data-non-valida',
            'tipo_atto': 'Delibera',
            'oggetto': 'Test',
            'scraper_type': 'albo_firenze_v2'
        }
        
        scraper_with_mongodb.mongodb_importer.import_atto = AsyncMock(return_value=True)
        
        result = await scraper_with_mongodb.import_atto_to_mongodb(atto_data)
        
        assert result == True
        call_args = scraper_with_mongodb.mongodb_importer.import_atto.call_args
        assert call_args[1]['atto_data']['anno'] is None
    
    def test_tenant_id_isolation(self):
        """Test che ogni scraper usa tenant_id corretto"""
        scraper_tenant_1 = AlboPretorioFirenze(tenant_id=1, use_mongodb=False)
        scraper_tenant_2 = AlboPretorioFirenze(tenant_id=2, use_mongodb=False)
        
        assert scraper_tenant_1.tenant_id == 1
        assert scraper_tenant_2.tenant_id == 2
    
    @patch('scrape_albo_firenze_v2.requests.Session')
    @pytest.mark.asyncio
    async def test_get_page_success(self, mock_session, scraper_json_only):
        """Test download pagina HTML"""
        mock_response = Mock()
        mock_response.text = "<html><body>Test</body></html>"
        mock_response.raise_for_status = Mock()
        mock_session.return_value.get.return_value = mock_response
        
        result = scraper_json_only.get_page(1)
        
        assert result == "<html><body>Test</body></html>"
    
    @patch('scrape_albo_firenze_v2.requests.Session')
    def test_get_page_error(self, mock_session, scraper_json_only):
        """Test gestione errori download pagina"""
        mock_session.return_value.get.side_effect = Exception("Network error")
        
        result = scraper_json_only.get_page(1)
        
        assert result is None


class TestMongoDBIntegration:
    """Test integrazione MongoDB"""
    
    @pytest.mark.asyncio
    async def test_mongodb_importer_initialization(self):
        """Test inizializzazione MongoDB importer"""
        with patch('scrape_albo_firenze_v2.PAActMongoDBImporter') as mock_importer_class:
            mock_importer = Mock()
            mock_importer_class.return_value = mock_importer
            
            scraper = AlboPretorioFirenze(use_mongodb=True, tenant_id=1)
            
            assert scraper.use_mongodb == True
            assert scraper.mongodb_importer is not None
            mock_importer_class.assert_called_once_with(tenant_id=1, dry_run=False)
    
    @pytest.mark.asyncio
    async def test_mongodb_importer_init_error(self):
        """Test fallback quando MongoDB importer fallisce"""
        with patch('scrape_albo_firenze_v2.PAActMongoDBImporter', side_effect=ImportError("Module not found")):
            scraper = AlboPretorioFirenze(use_mongodb=True, tenant_id=1)
            
            # Dovrebbe fallback a JSON-only
            assert scraper.use_mongodb == False
            assert scraper.mongodb_importer is None


class TestDataTransformation:
    """Test trasformazione dati per MongoDB"""
    
    @pytest.mark.asyncio
    async def test_atto_data_mapping(self):
        """Test mappatura corretta dati atto per MongoDB"""
        with patch('scrape_albo_firenze_v2.PAActMongoDBImporter'):
            scraper = AlboPretorioFirenze(use_mongodb=True, tenant_id=1)
            
            atto_original = {
                'tipo_atto': 'Delibera',
                'numero_registro': '123/2024',
                'numero_atto': '456/2024',
                'data_inizio': '01/01/2024',
                'data_fine': '31/01/2024',
                'oggetto': 'Test oggetto completo',
                'direzione': 'Direzione Test',
                'pdf_links': [{'url': 'http://test.com/doc.pdf'}],
                'scraper_type': 'albo_firenze_v2'
            }
            
            scraper.mongodb_importer.import_atto = AsyncMock(return_value=True)
            
            await scraper.import_atto_to_mongodb(atto_original)
            
            # Verifica mapping corretto
            call_args = scraper.mongodb_importer.import_atto.call_args
            atto_mongodb = call_args[1]['atto_data']
            
            assert atto_mongodb['numero_atto'] == '456/2024'  # Preferisce numero_atto
            assert atto_mongodb['tipo_atto'] == 'Delibera'
            assert atto_mongodb['oggetto'] == 'Test oggetto completo'
            assert atto_mongodb['data_atto'] == '01/01/2024'
            assert atto_mongodb['scraper_type'] == 'albo_firenze_v2'
            assert call_args[1]['ente'] == 'Comune di Firenze'


if __name__ == '__main__':
    pytest.main([__file__, '-v'])
