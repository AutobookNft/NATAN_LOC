#!/usr/bin/env python3
"""
Scraper per Albo Pretorio del Comune di Firenze
Estrae tutti gli atti pubblicati dall'albo pretorio online
Supporta salvataggio in MongoDB per NATAN_LOC
"""

import requests
from bs4 import BeautifulSoup
import json
import os
import re
import sys
import asyncio
from datetime import datetime
from time import sleep
from pathlib import Path
from urllib.parse import urljoin, parse_qs, urlparse

# Add NATAN_LOC python_ai_service to path for MongoDB importer
NATAN_LOC_ROOT = Path(__file__).parent.parent
sys.path.insert(0, str(NATAN_LOC_ROOT / "python_ai_service"))

class AlboPretorioFirenze:
    def __init__(self, output_dir='storage/testing/firenze_atti', use_mongodb=False, tenant_id=1):
        self.base_url = "https://accessoconcertificato.comune.fi.it"
        self.search_url = f"{self.base_url}/AOL/Affissione/ComuneFi/Page"
        self.output_dir = output_dir
        self.use_mongodb = use_mongodb
        self.tenant_id = tenant_id
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        })
        
        # MongoDB importer (if enabled)
        self.mongodb_importer = None
        if self.use_mongodb:
            try:
                from app.services.pa_act_mongodb_importer import PAActMongoDBImporter
                self.mongodb_importer = PAActMongoDBImporter(tenant_id=self.tenant_id, dry_run=False)
                print(f"✅ MongoDB import enabled (tenant_id={tenant_id})")
            except Exception as e:
                print(f"⚠️  Errore inizializzazione MongoDB importer: {e}")
                print("   Continuo con salvataggio JSON solo")
                self.use_mongodb = False
        
        # Crea directory output (anche se usiamo MongoDB, per PDF)
        os.makedirs(output_dir, exist_ok=True)
        os.makedirs(f"{output_dir}/json", exist_ok=True)
        os.makedirs(f"{output_dir}/pdf", exist_ok=True)
    
    def get_page(self, page_num=1):
        """Scarica una pagina di risultati"""
        params = {}
        if page_num > 1:
            params['page'] = page_num
        
        try:
            print(f"📥 Scaricamento pagina {page_num}...")
            response = self.session.get(self.search_url, params=params, timeout=15)
            response.raise_for_status()
            return response.text
        except Exception as e:
            print(f"❌ Errore nel caricamento pagina {page_num}: {e}")
            return None
    
    def parse_atto(self, atto_div):
        """Estrae i dati da un singolo atto"""
        try:
            # Tipo atto e direzione (prima riga)
            header = atto_div.get_text(strip=True, separator=' ')
            lines = [line.strip() for line in atto_div.stripped_strings]
            
            atto_data = {
                'tipo_atto': lines[0] if lines else '',
                'direzione': lines[1] if len(lines) > 1 else '',
                'numero_registro': '',
                'numero_atto': '',
                'data_inizio': '',
                'data_fine': '',
                'oggetto': '',
                'pdf_links': [],
                'scraped_at': datetime.now().isoformat(),
                'scraper_type': 'albo_firenze_v2'  # Identificatore scraper
            }
            
            # Cerca i campi strutturati
            text = atto_div.get_text(separator='\n')
            
            # N° registro
            match = re.search(r'N°\s*registro\s*(\d+/\d+)', text, re.IGNORECASE)
            if match:
                atto_data['numero_registro'] = match.group(1)
            
            # N° atto
            match = re.search(r'N°\s*atto\s*(\d+/\d+)', text, re.IGNORECASE)
            if match:
                atto_data['numero_atto'] = match.group(1)
            
            # Data inizio
            match = re.search(r'Inizio\s+pubblicazione\s*(\d{2}/\d{2}/\d{4})', text, re.IGNORECASE)
            if match:
                atto_data['data_inizio'] = match.group(1)
            
            # Data fine
            match = re.search(r'Fine\s+pubblicazione\s*(\d{2}/\d{2}/\d{4})', text, re.IGNORECASE)
            if match:
                atto_data['data_fine'] = match.group(1)
            
            # Oggetto (l'ultimo testo lungo)
            for line in reversed(lines):
                if len(line) > 50:  # Probabilmente è l'oggetto
                    atto_data['oggetto'] = line
                    break
            
            # Link PDF
            pdf_links = atto_div.find_all('a', href=lambda x: x and '.pdf' in x.lower())
            for link in pdf_links:
                pdf_url = urljoin(self.base_url, link.get('href'))
                atto_data['pdf_links'].append({
                    'url': pdf_url,
                    'text': link.get_text(strip=True)
                })
            
            return atto_data
            
        except Exception as e:
            print(f"⚠️  Errore nel parsing atto: {e}")
            return None
    
    def get_total_pages(self, html):
        """Estrae il numero totale di pagine"""
        soup = BeautifulSoup(html, 'html.parser')
        
        # Cerca "Pagina X di Y"
        pagination_text = soup.find(string=re.compile(r'Pagina\s+\d+\s+di\s+\d+', re.IGNORECASE))
        if pagination_text:
            match = re.search(r'Pagina\s+\d+\s+di\s+(\d+)', pagination_text, re.IGNORECASE)
            if match:
                return int(match.group(1))
        
        return 1
    
    def scrape_page(self, html):
        """Estrae tutti gli atti da una pagina HTML"""
        soup = BeautifulSoup(html, 'html.parser')
        atti = []
        
        # Ogni atto è in un div con classe "card concorso-card multi-line"
        card_divs = soup.find_all('div', class_='card concorso-card multi-line')
        
        print(f"      Trovati {len(card_divs)} card divs")
        
        for card in card_divs:
            atto = self.parse_atto(card)
            if atto and atto.get('numero_registro'):
                atti.append(atto)
        
        return atti
    
    def download_pdf(self, pdf_url, filename):
        """Scarica un PDF"""
        try:
            response = self.session.get(pdf_url, timeout=30)
            response.raise_for_status()
            
            filepath = os.path.join(self.output_dir, 'pdf', filename)
            with open(filepath, 'wb') as f:
                f.write(response.content)
            
            return filepath
        except Exception as e:
            print(f"❌ Errore download PDF {filename}: {e}")
            return None

    async def import_atto_to_mongodb(self, atto, pdf_path=None):
        """Importa un atto in MongoDB usando PAActMongoDBImporter"""
        if not self.mongodb_importer:
            return False
        
        try:
            # Prepara dati per MongoDB importer
            anno = None
            if atto.get('data_inizio'):
                try:
                    anno = datetime.strptime(atto['data_inizio'], '%d/%m/%Y').year
                except (ValueError, TypeError):
                    pass  # Se il parsing fallisce, anno rimane None
            
            atto_data_for_mongodb = {
                'numero_atto': atto.get('numero_atto') or atto.get('numero_registro', ''),
                'tipo_atto': atto.get('tipo_atto', ''),
                'oggetto': atto.get('oggetto', ''),
                'data_atto': atto.get('data_inizio', ''),
                'data_fine': atto.get('data_fine', ''),
                'direzione': atto.get('direzione', ''),
                'anno': anno,
                'scraper_type': atto.get('scraper_type', 'albo_firenze_v2')
            }
            
            # URL PDF (primo link disponibile)
            pdf_url = None
            if atto.get('pdf_links'):
                pdf_url = atto['pdf_links'][0].get('url')
            
            # Import in MongoDB
            success = await self.mongodb_importer.import_atto(
                atto_data=atto_data_for_mongodb,
                pdf_path=pdf_path,
                pdf_url=pdf_url,
                ente="Comune di Firenze"
            )
            
            return success
        except Exception as e:
            print(f"⚠️  Errore import MongoDB atto {atto.get('numero_atto', 'N/A')}: {e}")
            return False

    async def scrape_all(self, max_pages=None, download_pdfs=False):
        """Scrape tutte le pagine"""
        print("🚀 INIZIO SCRAPING ALBO PRETORIO FIRENZE")
        print("=" * 70)
        if self.use_mongodb:
            print(f"📦 MongoDB import: ABILITATO (tenant_id={self.tenant_id})")
        else:
            print("💾 Salvataggio: JSON locale")
        print("=" * 70)
        
        # Prima pagina per ottenere totale
        html = self.get_page(1)
        if not html:
            print("❌ Impossibile caricare la prima pagina!")
            return []
        
        total_pages = self.get_total_pages(html)
        print(f"📊 Totale pagine: {total_pages}")
        
        if max_pages:
            total_pages = min(total_pages, max_pages)
            print(f"   (limitato a {max_pages} pagine)")
        
        all_atti = []
        
        # Scrape tutte le pagine
        for page in range(1, total_pages + 1):
            if page > 1:
                sleep(2)  # Pausa tra richieste
                html = self.get_page(page)
                if not html:
                    continue
            
            atti = self.scrape_page(html)
            print(f"   Pagina {page}/{total_pages}: {len(atti)} atti trovati")
            
            # Processa ogni atto
            for atto in atti:
                pdf_path = None
                
                # Download PDF se richiesto
                if download_pdfs and atto.get('pdf_links'):
                    pdf_url = atto['pdf_links'][0].get('url')
                    if pdf_url:
                        filename = f"{atto['numero_registro'].replace('/', '_')}.pdf"
                        pdf_path = self.download_pdf(pdf_url, filename)
                
                # Import in MongoDB se abilitato
                if self.use_mongodb:
                    await self.import_atto_to_mongodb(atto, pdf_path)
                
                all_atti.append(atto)
            
            # Pausa tra pagine
            sleep(1)
        
        print(f"\n✅ Totale atti estratti: {len(all_atti)}")
        
        # Salva JSON (anche se usiamo MongoDB, per backup)
        output_file = os.path.join(self.output_dir, 'json', f'atti_{datetime.now().strftime("%Y%m%d_%H%M%S")}.json')
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(all_atti, f, indent=2, ensure_ascii=False)
        print(f"💾 Backup JSON salvato: {output_file}")
        
        # Statistiche MongoDB se abilitato
        if self.use_mongodb and self.mongodb_importer:
            stats = self.mongodb_importer.stats
            print(f"\n📊 Statistiche MongoDB:")
            print(f"   ✅ Importati: {stats['processed']}")
            print(f"   ⚠️  Saltati: {stats['skipped']}")
            print(f"   ❌ Errori: {stats['errors']}")
            print(f"   📄 Totale chunks: {stats['total_chunks']}")
            
            # Costi embeddings
            cost_info = self.mongodb_importer.cost_tracker.calculate_cost()
            if cost_info['cost_eur'] > 0:
                print(f"\n💰 Costi embeddings:")
                print(f"   Modello: {cost_info['model']}")
                print(f"   Tokens: {cost_info['total_tokens']:,}")
                print(f"   Costo: €{cost_info['cost_eur']:.4f}")
        
        return all_atti


async def main_async():
    """Main async function"""
    import argparse
    
    parser = argparse.ArgumentParser(description='Scraper Albo Pretorio Firenze')
    parser.add_argument('--max-pages', type=int, help='Numero massimo di pagine da scaricare')
    parser.add_argument('--download-pdfs', action='store_true', help='Scarica anche i PDF allegati')
    parser.add_argument('--output-dir', default='storage/testing/firenze_atti', help='Directory output')
    parser.add_argument('--mongodb', action='store_true', help='Importa in MongoDB (NATAN_LOC)')
    parser.add_argument('--tenant-id', type=int, default=1, help='Tenant ID per MongoDB (default: 1)')
    
    args = parser.parse_args()
    
    scraper = AlboPretorioFirenze(
        output_dir=args.output_dir,
        use_mongodb=args.mongodb,
        tenant_id=args.tenant_id
    )
    atti = await scraper.scrape_all(max_pages=args.max_pages, download_pdfs=args.download_pdfs)
    
    print(f"\n🎉 COMPLETATO! {len(atti)} atti estratti")
    print(f"📂 Files salvati in: {args.output_dir}")
    if args.mongodb:
        print(f"📦 Importati in MongoDB (tenant_id={args.tenant_id})")


def main():
    """Main entry point"""
    asyncio.run(main_async())


if __name__ == '__main__':
    main()

