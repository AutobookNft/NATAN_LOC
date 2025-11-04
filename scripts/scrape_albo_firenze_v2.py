#!/usr/bin/env python3
"""
Scraper per Albo Pretorio del Comune di Firenze
Estrae tutti gli atti pubblicati dall'albo pretorio online
"""

import requests
from bs4 import BeautifulSoup
import json
import os
import re
from datetime import datetime
from time import sleep
from urllib.parse import urljoin, parse_qs, urlparse

class AlboPretorioFirenze:
    def __init__(self, output_dir='storage/testing/firenze_atti'):
        self.base_url = "https://accessoconcertificato.comune.fi.it"
        self.search_url = f"{self.base_url}/AOL/Affissione/ComuneFi/Page"
        self.output_dir = output_dir
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        })
        
        # Crea directory output
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
                'scraped_at': datetime.now().isoformat()
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
    
    def scrape_all(self, max_pages=None, download_pdfs=False):
        """Scrape tutte le pagine"""
        print("🚀 INIZIO SCRAPING ALBO PRETORIO FIRENZE")
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
            all_atti.extend(atti)
        
        print(f"\n✅ Totale atti estratti: {len(all_atti)}")
        
        # Salva JSON
        output_file = os.path.join(self.output_dir, 'json', f'atti_{datetime.now().strftime("%Y%m%d_%H%M%S")}.json')
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(all_atti, f, indent=2, ensure_ascii=False)
        print(f"💾 Salvato: {output_file}")
        
        # Download PDF se richiesto
        if download_pdfs:
            print(f"\n📥 Download PDF...")
            pdf_count = 0
            for atto in all_atti:
                for pdf in atto.get('pdf_links', []):
                    pdf_url = pdf['url']
                    filename = f"{atto['numero_registro'].replace('/', '_')}_{pdf_count}.pdf"
                    if self.download_pdf(pdf_url, filename):
                        pdf_count += 1
                    sleep(1)
            print(f"✅ PDF scaricati: {pdf_count}")
        
        return all_atti


def main():
    import argparse
    
    parser = argparse.ArgumentParser(description='Scraper Albo Pretorio Firenze')
    parser.add_argument('--max-pages', type=int, help='Numero massimo di pagine da scaricare')
    parser.add_argument('--download-pdfs', action='store_true', help='Scarica anche i PDF allegati')
    parser.add_argument('--output-dir', default='storage/testing/firenze_atti', help='Directory output')
    
    args = parser.parse_args()
    
    scraper = AlboPretorioFirenze(output_dir=args.output_dir)
    atti = scraper.scrape_all(max_pages=args.max_pages, download_pdfs=args.download_pdfs)
    
    print(f"\n🎉 COMPLETATO! {len(atti)} atti estratti")
    print(f"📂 Files salvati in: {args.output_dir}")


if __name__ == '__main__':
    main()

