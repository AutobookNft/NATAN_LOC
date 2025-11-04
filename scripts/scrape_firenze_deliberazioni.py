#!/usr/bin/env python3
"""
Scraper completo per Deliberazioni e Determinazioni del Comune di Firenze
Estrae tutti gli atti dal 2018 al 2025 con allegati PDF
"""

import requests
import json
import os
from datetime import datetime
from time import sleep
from urllib.parse import urljoin

class FirenzeAttiScraper:
    def __init__(self, output_dir='storage/testing/firenze_atti_completi'):
        self.base_url = "https://accessoconcertificato.comune.fi.it"
        self.api_url = f"{self.base_url}/trasparenza-atti-cat/searchAtti"
        self.output_dir = output_dir
        
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'Accept': 'application/json, text/plain, */*',
            'Accept-Language': 'it-IT,it;q=0.9',
            'Content-Type': 'application/json',
            'Origin': self.base_url,
            'Referer': f'{self.base_url}/trasparenza-atti/',
        })
        
        # Crea directory
        os.makedirs(output_dir, exist_ok=True)
        os.makedirs(f"{output_dir}/json", exist_ok=True)
        os.makedirs(f"{output_dir}/pdf", exist_ok=True)
    
    def search_atti(self, anno, tipo_atto='DG'):
        """Cerca atti per anno e tipo"""
        payload = {
            "oggetto": "",
            "notLoadIniziale": "ok",
            "numeroAdozione": "",
            "competenza": tipo_atto,
            "annoAdozione": str(anno),
            "tipiAtto": [tipo_atto]  # Può includere più tipi
        }
        
        try:
            response = self.session.post(self.api_url, json=payload, timeout=30)
            response.raise_for_status()
            return response.json()
        except Exception as e:
            print(f"❌ Errore ricerca {tipo_atto} {anno}: {e}")
            return []
    
    def download_pdf(self, pdf_link, filename):
        """Scarica un PDF"""
        try:
            pdf_url = urljoin(self.base_url, pdf_link)
            response = self.session.get(pdf_url, timeout=30)
            response.raise_for_status()
            
            filepath = os.path.join(self.output_dir, 'pdf', filename)
            with open(filepath, 'wb') as f:
                f.write(response.content)
            
            return filepath
        except Exception as e:
            print(f"⚠️  Errore download PDF {filename}: {e}")
            return None
    
    def scrape_all(self, anni=None, tipi_atto=None, download_pdfs=False, max_pdf_per_type=None):
        """Scrape tutti gli atti"""
        
        # Default: dal 2018 al 2025
        if anni is None:
            anni = list(range(2018, 2026))
        
        # Tipi di atto disponibili
        if tipi_atto is None:
            tipi_atto = {
                'DG': 'Deliberazioni di Giunta',
                'DC': 'Deliberazioni di Consiglio',
                'DD': 'Determinazioni Dirigenziali',
                'DS': 'Decreti Sindacali',
                'OD': 'Ordinanze Dirigenziali',
            }
        
        print("🚀 INIZIO SCRAPING ATTI COMUNE DI FIRENZE")
        print("=" * 70)
        print(f"📅 Anni: {min(anni)} - {max(anni)}")
        print(f"📋 Tipi atto: {list(tipi_atto.keys())}")
        print(f"💾 Output: {self.output_dir}")
        
        all_atti = {}
        total_count = 0
        pdf_downloaded = 0
        
        for tipo_codice, tipo_nome in tipi_atto.items():
            print(f"\n{'='*70}")
            print(f"📂 {tipo_nome} ({tipo_codice})")
            print(f"{'='*70}")
            
            tipo_atti = []
            
            for anno in anni:
                print(f"\n   📅 Anno {anno}...", end=' ', flush=True)
                
                atti = self.search_atti(anno, tipo_codice)
                
                if atti:
                    print(f"✅ {len(atti)} atti trovati")
                    tipo_atti.extend(atti)
                    total_count += len(atti)
                    
                    # Download PDF se richiesto
                    if download_pdfs:
                        pdf_count_this_type = 0
                        for atto in atti:
                            if max_pdf_per_type and pdf_count_this_type >= max_pdf_per_type:
                                break
                            
                            for allegato in atto.get('allegati', []):
                                if max_pdf_per_type and pdf_count_this_type >= max_pdf_per_type:
                                    break
                                
                                if allegato.get('contentType') == 'application/pdf':
                                    pdf_link = allegato.get('link')
                                    if pdf_link:
                                        filename = f"{tipo_codice}_{anno}_{atto['numeroAdozione']}_{allegato['id']}.pdf"
                                        if self.download_pdf(pdf_link, filename):
                                            pdf_downloaded += 1
                                            pdf_count_this_type += 1
                                        sleep(0.5)  # Pausa tra download
                else:
                    print("⚪ 0 atti")
                
                sleep(1)  # Pausa tra richieste
            
            # Salva JSON per tipo
            if tipo_atti:
                all_atti[tipo_codice] = tipo_atti
                output_file = os.path.join(
                    self.output_dir, 
                    'json', 
                    f'{tipo_codice}_{min(anni)}_{max(anni)}.json'
                )
                with open(output_file, 'w', encoding='utf-8') as f:
                    json.dump(tipo_atti, f, indent=2, ensure_ascii=False)
                print(f"\n   💾 Salvato: {output_file}")
                print(f"   📊 Totale {tipo_nome}: {len(tipo_atti)} atti")
        
        # Salva JSON completo
        master_file = os.path.join(
            self.output_dir,
            'json',
            f'tutti_atti_{datetime.now().strftime("%Y%m%d_%H%M%S")}.json'
        )
        with open(master_file, 'w', encoding='utf-8') as f:
            json.dump(all_atti, f, indent=2, ensure_ascii=False)
        
        # Statistiche finali
        print(f"\n{'='*70}")
        print(f"🎉 COMPLETATO!")
        print(f"{'='*70}")
        print(f"📊 Totale atti estratti: {total_count}")
        print(f"📁 File JSON salvati per tipo: {len(all_atti)}")
        print(f"💾 Master file: {master_file}")
        
        if download_pdfs:
            print(f"📑 PDF scaricati: {pdf_downloaded}")
        
        return all_atti


def main():
    import argparse
    
    parser = argparse.ArgumentParser(description='Scraper Atti Comune Firenze')
    parser.add_argument('--anno-inizio', type=int, default=2018, help='Anno inizio (default: 2018)')
    parser.add_argument('--anno-fine', type=int, default=2025, help='Anno fine (default: 2025)')
    parser.add_argument('--download-pdfs', action='store_true', help='Scarica anche i PDF')
    parser.add_argument('--max-pdf-per-type', type=int, help='Max PDF per tipo (per test)')
    parser.add_argument('--output-dir', default='storage/testing/firenze_atti_completi', help='Directory output')
    parser.add_argument('--tipi', nargs='+', help='Tipi atto specifici (es: DG DC DD)')
    
    args = parser.parse_args()
    
    anni = list(range(args.anno_inizio, args.anno_fine + 1))
    
    tipi_atto = None
    if args.tipi:
        tipi_map = {
            'DG': 'Deliberazioni di Giunta',
            'DC': 'Deliberazioni di Consiglio',
            'DD': 'Determinazioni Dirigenziali',
            'DS': 'Decreti Sindacali',
            'OD': 'Ordinanze Dirigenziali',
        }
        tipi_atto = {k: v for k, v in tipi_map.items() if k in args.tipi}
    
    scraper = FirenzeAttiScraper(output_dir=args.output_dir)
    scraper.scrape_all(
        anni=anni,
        tipi_atto=tipi_atto,
        download_pdfs=args.download_pdfs,
        max_pdf_per_type=args.max_pdf_per_type
    )


if __name__ == '__main__':
    main()

