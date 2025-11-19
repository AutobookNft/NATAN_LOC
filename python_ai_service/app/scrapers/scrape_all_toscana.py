#!/usr/bin/env python3
"""
Script autonomo per testare TUTTI i 273 comuni della Toscana
Strategia: prova TUTTO per ogni comune, se funziona conta atti, se no TAG
NON SI FERMA fino alla fine
"""

import asyncio
import httpx
from bs4 import BeautifulSoup
from playwright.async_api import async_playwright
import json
from datetime import datetime
from typing import Dict, List, Optional
import re

# Import lista completa comuni
from comuni_toscana_data import COMUNI_TOSCANA_COMPLETI as COMUNI_TOSCANA

class ToscanaAlboTester:
    def __init__(self):
        self.risultati = []
        self.stats = {
            "totale": 0,
            "funzionanti": 0,
            "cloudflare": 0,
            "albo_non_trovato": 0,
            "js_required": 0,
            "atti_totali": 0
        }
        
    async def test_http_static(self, url: str, comune: str) -> Optional[Dict]:
        """Tenta scraping HTTP statico"""
        try:
            async with httpx.AsyncClient(timeout=10.0) as client:
                # 1. Cerca link albo
                response = await client.get(url)
                soup = BeautifulSoup(response.text, 'lxml')
                
                # Pattern albo
                albo_links = soup.find_all('a', href=re.compile(r'albo|pubblicazioni|trasparenza', re.I))
                
                if not albo_links:
                    return None
                
                # Testa primo link albo
                albo_url = albo_links[0].get('href')
                if not albo_url.startswith('http'):
                    albo_url = url.rstrip('/') + '/' + albo_url.lstrip('/')
                
                albo_response = await client.get(albo_url)
                albo_soup = BeautifulSoup(albo_response.text, 'lxml')
                
                # Conta atti
                atti_keywords = albo_soup.find_all(string=re.compile(r'delibera|determinazione|atto', re.I))
                atti_count = len(atti_keywords)
                
                if atti_count > 5:  # Soglia minima
                    return {
                        "metodo": "HTTP_STATIC",
                        "albo_url": albo_url,
                        "atti_stimati": atti_count,
                        "funzionante": True
                    }
        except:
            pass
        return None
    
    async def test_api_rest(self, url: str) -> Optional[Dict]:
        """Tenta API REST comuni"""
        endpoints = [
            "/api/albo",
            "/rest/albo",
            "/api/atti",
            "/trasparenza-atti-cat/searchAtti",
            "/api/pubblicazioni"
        ]
        
        try:
            async with httpx.AsyncClient(timeout=10.0) as client:
                for endpoint in endpoints:
                    try:
                        api_url = url.rstrip('/') + endpoint
                        response = await client.get(api_url)
                        
                        if response.status_code == 200:
                            # Verifica se JSON
                            try:
                                data = response.json()
                                if isinstance(data, (list, dict)):
                                    return {
                                        "metodo": "API_REST",
                                        "endpoint": endpoint,
                                        "funzionante": True,
                                        "api_url": api_url
                                    }
                            except:
                                pass
                    except:
                        continue
        except:
            pass
        return None
    
    async def test_playwright(self, url: str, comune: str) -> Optional[Dict]:
        """Tenta con Playwright per JS rendering"""
        try:
            p = await async_playwright().start()
            browser = await p.chromium.launch(headless=True)
            page = await browser.new_page()
            
            # Vai alla home
            await page.goto(url, wait_until='networkidle', timeout=15000)
            
            # Cerca link albo
            albo_link = await page.query_selector('a[href*="albo"], a[href*="trasparenza"]')
            
            if albo_link:
                albo_href = await albo_link.get_attribute('href')
                if not albo_href.startswith('http'):
                    albo_href = url.rstrip('/') + '/' + albo_href.lstrip('/')
                
                await page.goto(albo_href, wait_until='networkidle', timeout=15000)
                
                # Conta atti
                content = await page.content()
                
                # Cloudflare?
                if 'cloudflare' in content.lower() or 'checking your browser' in content.lower():
                    await browser.close()
                    await p.stop()
                    return {"metodo": "PLAYWRIGHT", "funzionante": False, "tag": "CLOUDFLARE"}
                
                # Conta delibere/determine
                atti_count = content.lower().count('delibera') + content.lower().count('determinazione')
                
                if atti_count > 5:
                    await browser.close()
                    await p.stop()
                    return {
                        "metodo": "PLAYWRIGHT",
                        "albo_url": albo_href,
                        "atti_stimati": atti_count,
                        "funzionante": True
                    }
            
            await browser.close()
            await p.stop()
        except:
            pass
        return None
    
    async def test_comune(self, comune: Dict) -> Dict:
        """Testa UN comune con TUTTE le strategie"""
        nome = comune['nome']
        url = comune['url']
        abitanti = comune['abitanti']
        
        print(f"\n{'='*80}")
        print(f"🏛️  {nome} ({abitanti:,} abitanti)")
        print(f"{'='*80}")
        
        risultato = {
            "comune": nome,
            "url": url,
            "abitanti": abitanti,
            "timestamp": datetime.now().isoformat(),
            "metodo": None,
            "funzionante": False,
            "atti_recuperabili": 0,
            "tag": None,
            "note": ""
        }
        
        # Strategia 1: HTTP Statico
        print("  🔹 Test HTTP statico...")
        http_result = await self.test_http_static(url, nome)
        if http_result and http_result.get('funzionante'):
            print(f"    ✅ HTTP STATICO OK - {http_result['atti_stimati']} atti")
            risultato.update(http_result)
            risultato['atti_recuperabili'] = http_result['atti_stimati']
            return risultato
        else:
            print("    ❌ HTTP statico non funzionante")
        
        # Strategia 2: API REST
        print("  🔹 Test API REST...")
        api_result = await self.test_api_rest(url)
        if api_result and api_result.get('funzionante'):
            print(f"    ✅ API REST OK - {api_result['endpoint']}")
            risultato.update(api_result)
            risultato['atti_recuperabili'] = 999  # Da verificare con chiamata specifica
            return risultato
        else:
            print("    ❌ API REST non trovata")
        
        # Strategia 3: Playwright
        print("  🔹 Test Playwright (JS rendering)...")
        pw_result = await self.test_playwright(url, nome)
        if pw_result:
            if pw_result.get('funzionante'):
                print(f"    ✅ PLAYWRIGHT OK - {pw_result['atti_stimati']} atti")
                risultato.update(pw_result)
                risultato['atti_recuperabili'] = pw_result['atti_stimati']
                return risultato
            elif pw_result.get('tag') == 'CLOUDFLARE':
                print("    🔒 CLOUDFLARE PROTECTION")
                risultato['tag'] = 'CLOUDFLARE'
                risultato['note'] = 'Sito protetto da Cloudflare'
                return risultato
        else:
            print("    ❌ Playwright non ha trovato atti")
        
        # Nessuna strategia funziona
        print("  ⚠️  TAG: DA_CONTATTARE")
        risultato['tag'] = 'DA_CONTATTARE'
        risultato['note'] = 'Nessun metodo automatico funzionante - richede indagine manuale'
        
        return risultato
    
    async def run(self, comuni: List[Dict]):
        """Esegue test su TUTTI i comuni"""
        print("\n" + "="*80)
        print("🚀 INIZIO TEST AUTOMATICO TUTTI I COMUNI TOSCANA")
        print("="*80)
        print(f"Comuni da testare: {len(comuni)}")
        print(f"Inizio: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
        print("="*80)
        
        for i, comune in enumerate(comuni, 1):
            print(f"\n[{i}/{len(comuni)}]")
            
            risultato = await self.test_comune(comune)
            self.risultati.append(risultato)
            
            # Aggiorna stats
            self.stats['totale'] += 1
            if risultato['funzionante']:
                self.stats['funzionanti'] += 1
                self.stats['atti_totali'] += risultato['atti_recuperabili']
            elif risultato['tag'] == 'CLOUDFLARE':
                self.stats['cloudflare'] += 1
            elif risultato['tag'] == 'DA_CONTATTARE':
                self.stats['albo_non_trovato'] += 1
            
            # Salva progressivo ogni 10 comuni
            if i % 10 == 0:
                self.save_results()
                self.print_stats()
        
        # Salvataggio finale
        self.save_results()
        
        print("\n" + "="*80)
        print("✅ TEST COMPLETATO")
        print("="*80)
        self.print_stats()
    
    def save_results(self):
        """Salva risultati JSON"""
        output = {
            "timestamp": datetime.now().isoformat(),
            "stats": self.stats,
            "comuni": self.risultati
        }
        
        with open('toscana_scraping_results.json', 'w', encoding='utf-8') as f:
            json.dump(output, f, indent=2, ensure_ascii=False)
        
        print(f"\n💾 Risultati salvati in: toscana_scraping_results.json")
    
    def print_stats(self):
        """Stampa statistiche"""
        print(f"\n📊 STATISTICHE PROGRESSIVE")
        print(f"  Totale testati: {self.stats['totale']}")
        print(f"  ✅ Funzionanti: {self.stats['funzionanti']}")
        print(f"  🔒 Cloudflare: {self.stats['cloudflare']}")
        print(f"  ⚠️  Da contattare: {self.stats['albo_non_trovato']}")
        print(f"  📄 Atti totali recuperabili: {self.stats['atti_totali']:,}")
        
        if self.stats['totale'] > 0:
            coverage = (self.stats['funzionanti'] / self.stats['totale']) * 100
            print(f"  📈 Coverage: {coverage:.1f}%")


async def main():
    print("🚀 Inizializzazione ToscanaAlboTester...")
    print(f"📊 Comuni da testare: {len(COMUNI_TOSCANA)}")
    tester = ToscanaAlboTester()
    print("✅ Tester creato, avvio run()...")
    await tester.run(COMUNI_TOSCANA)
    print("🏁 Run completato!")


if __name__ == "__main__":
    print("=" * 80)
    print("AVVIO SCRAPING TUTTI I COMUNI TOSCANA")
    print("=" * 80)
    try:
        asyncio.run(main())
    except Exception as e:
        print(f"❌ ERRORE FATALE: {e}")
        import traceback
        traceback.print_exc()
