#!/usr/bin/env python3
"""
Aggressive Extraction Scraper
==============================
Per ogni comune:
1. Tenta TUTTI i metodi di estrazione possibili
2. Se riesce → estrae dati
3. Se fallisce → identifica problema + suggerisce soluzione

NON ci arrendiamo finché non abbiamo provato tutto.
"""

import asyncio
import json
import re
from datetime import datetime
from typing import Dict, List, Optional
import requests
from bs4 import BeautifulSoup
from playwright.async_api import async_playwright


class AggressiveExtractor:
    """Estrattore aggressivo che prova TUTTI i metodi"""
    
    def __init__(self):
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36'
        })
    
    async def extract_pisa(self) -> dict:
        """Pisa - albopretorio.comune.pisa.it"""
        print("\n" + "="*60)
        print("🏛️  PISA - Tentativo Estrazione")
        print("="*60)
        
        result = {
            'comune': 'Pisa',
            'url': 'https://albopretorio.comune.pisa.it/',
            'atti': [],
            'metodo_funzionante': None,
            'problema': None
        }
        
        # METODO 1: HTTP diretto con BeautifulSoup
        print("\n📍 Metodo 1: HTTP + BeautifulSoup")
        try:
            resp = self.session.get('https://albopretorio.comune.pisa.it/', timeout=10)
            soup = BeautifulSoup(resp.content, 'html.parser')
            
            # Cerca link atti
            links = soup.find_all('a')
            atti_trovati = []
            
            for link in links:
                text = link.get_text(strip=True)
                href = link.get('href', '')
                
                if any(k in text.lower() for k in ['deliber', 'determin', 'n.', 'atto']):
                    atti_trovati.append({
                        'testo': text[:100],
                        'link': href
                    })
            
            if atti_trovati:
                print(f"✅ Trovati {len(atti_trovati)} atti con HTTP")
                result['atti'] = atti_trovati
                result['metodo_funzionante'] = 'http_beautifulsoup'
                return result
            else:
                print("❌ Nessun atto trovato in HTML statico")
        
        except Exception as e:
            print(f"❌ Errore HTTP: {e}")
        
        # METODO 2: Playwright con JavaScript
        print("\n📍 Metodo 2: Playwright + JavaScript rendering")
        
        p = await async_playwright().start()
        browser = await p.chromium.launch(headless=True)
        page = await browser.new_page()
        
        try:
            await page.goto('https://albopretorio.comune.pisa.it/', wait_until='networkidle', timeout=15000)
            
            # Aspetta rendering JavaScript
            await page.wait_for_timeout(3000)
            
            # Scroll per lazy loading
            for _ in range(3):
                await page.evaluate('window.scrollBy(0, 1000)')
                await page.wait_for_timeout(500)
            
            # Cerca tabelle, liste, div con atti
            selettori = [
                'table tr',
                'ul li',
                'div[class*="atto"]',
                'div[class*="item"]',
                'div[class*="result"]',
                '.atto', '.item', '.result'
            ]
            
            for sel in selettori:
                elementi = await page.query_selector_all(sel)
                if len(elementi) > 5:  # Probabile lista atti
                    print(f"✓ Trovati {len(elementi)} elementi con selettore '{sel}'")
                    
                    atti_js = []
                    for elem in elementi[:50]:  # Prime 50
                        text = await elem.text_content()
                        if text and any(k in text.lower() for k in ['deliber', 'determin', 'atto', 'n.']):
                            # Cerca link dentro elemento
                            link_elem = await elem.query_selector('a')
                            href = await link_elem.get_attribute('href') if link_elem else ''
                            
                            atti_js.append({
                                'testo': text.strip()[:200],
                                'link': href
                            })
                    
                    if atti_js:
                        print(f"✅ Estratti {len(atti_js)} atti con Playwright")
                        result['atti'] = atti_js
                        result['metodo_funzionante'] = f'playwright_{sel}'
                        await browser.close()
                        await p.stop()
                        return result
            
            print("❌ Nessun atto trovato con Playwright")
        
        except Exception as e:
            print(f"❌ Errore Playwright: {e}")
        
        # METODO 3: Intercetta chiamate AJAX/API
        print("\n📍 Metodo 3: Intercettazione network requests")
        
        try:
            page2 = await browser.new_page()
            
            # Intercetta richieste
            api_calls = []
            
            async def intercept(request):
                if any(k in request.url for k in ['api', 'search', 'atti', 'albo', 'json']):
                    api_calls.append({
                        'url': request.url,
                        'method': request.method,
                        'post_data': request.post_data
                    })
            
            page2.on('request', intercept)
            
            await page2.goto('https://albopretorio.comune.pisa.it/', wait_until='networkidle')
            await page2.wait_for_timeout(5000)
            
            # Cerca form e compilalo
            forms = await page2.query_selector_all('form')
            for form in forms:
                try:
                    # Submit form
                    submit_btn = await form.query_selector('button[type="submit"], input[type="submit"]')
                    if submit_btn:
                        await submit_btn.click()
                        await page2.wait_for_timeout(2000)
                except:
                    pass
            
            if api_calls:
                print(f"✅ Intercettate {len(api_calls)} chiamate API")
                
                # Prova a replicare chiamate API
                for call in api_calls:
                    try:
                        if call['method'] == 'GET':
                            resp = self.session.get(call['url'], timeout=10)
                        else:
                            resp = self.session.post(call['url'], data=call['post_data'], timeout=10)
                        
                        # Tenta parsing JSON
                        try:
                            data = resp.json()
                            # Cerca array con atti
                            if isinstance(data, list) and len(data) > 0:
                                print(f"✅ API JSON trovata: {len(data)} elementi")
                                result['atti'] = data[:50]
                                result['metodo_funzionante'] = 'api_json'
                                await page2.close()
                                await browser.close()
                                await p.stop()
                                return result
                        except:
                            pass
                    except:
                        pass
            
            await page2.close()
        
        except Exception as e:
            print(f"❌ Errore intercettazione: {e}")
        
        # METODO 4: Cerca iframe esterni
        print("\n📍 Metodo 4: Ricerca iframe esterni")
        
        try:
            page3 = await browser.new_page()
            await page3.goto('https://albopretorio.comune.pisa.it/')
            
            iframes = await page3.query_selector_all('iframe')
            print(f"Iframe trovati: {len(iframes)}")
            
            for iframe in iframes:
                src = await iframe.get_attribute('src')
                if src:
                    print(f"  → {src}")
                    
                    # Prova a navigare nell'iframe
                    try:
                        frame = await iframe.content_frame()
                        if frame:
                            # Cerca atti nell'iframe
                            frame_links = await frame.query_selector_all('a')
                            atti_iframe = []
                            
                            for link in frame_links:
                                text = await link.text_content()
                                if text and any(k in text.lower() for k in ['deliber', 'determin']):
                                    href = await link.get_attribute('href')
                                    atti_iframe.append({
                                        'testo': text.strip()[:100],
                                        'link': href
                                    })
                            
                            if atti_iframe:
                                print(f"✅ Trovati {len(atti_iframe)} atti in iframe")
                                result['atti'] = atti_iframe
                                result['metodo_funzionante'] = f'iframe_{src}'
                                await page3.close()
                                await browser.close()
                                await p.stop()
                                return result
                    except:
                        pass
            
            await page3.close()
        
        except Exception as e:
            print(f"❌ Errore iframe: {e}")
        
        await browser.close()
        await p.stop()
        
        # FALLIMENTO: Nessun metodo ha funzionato
        print("\n❌ TUTTI I METODI FALLITI")
        result['problema'] = {
            'tipo': 'no_extraction_method',
            'dettaglio': 'Nessun metodo di estrazione ha trovato atti',
            'soluzioni': [
                'API REST pubblica in JSON',
                'Export CSV risultati ricerca',
                'Feed RSS atti pubblicati'
            ]
        }
        
        return result
    
    async def extract_arezzo(self) -> dict:
        """Arezzo - servizionline.comune.arezzo.it/jattipubblicazioni"""
        print("\n" + "="*60)
        print("🏛️  AREZZO - Tentativo Estrazione")
        print("="*60)
        
        result = {
            'comune': 'Arezzo',
            'url': 'https://servizionline.comune.arezzo.it/jattipubblicazioni/',
            'atti': [],
            'metodo_funzionante': None,
            'problema': None
        }
        
        # METODO 1: Form POST HTTP diretto
        print("\n📍 Metodo 1: Form POST HTTP")
        
        try:
            # Parametri form (sappiamo già dal debug precedente)
            data = {
                'organo': '',
                'annoAtto': '',
                'numeroAtto': '',
                'dadata': '01/01/2024',
                'adata': '31/12/2024',
                'oggetto': '',
                'settore': '',
                'ordinamento': '1',
                'sort': '-',
                'resultXPag': '100',  # Max risultati
                'servizio': 'Ricerca',
                'submit': 'Ricerca'
            }
            
            resp = self.session.post(
                'https://servizionline.comune.arezzo.it/jattipubblicazioni/AttiPubblicazioni',
                data=data,
                timeout=15
            )
            
            soup = BeautifulSoup(resp.content, 'html.parser')
            
            # Cerca tabelle risultati
            tabelle = soup.find_all('table')
            print(f"Tabelle trovate: {len(tabelle)}")
            
            for i, table in enumerate(tabelle):
                righe = table.find_all('tr')
                print(f"  Tabella {i+1}: {len(righe)} righe")
                
                # Skippa header
                for riga in righe[1:]:
                    celle = riga.find_all('td')
                    if len(celle) >= 3:  # Probabile riga atto
                        testo = ' '.join([c.get_text(strip=True) for c in celle])
                        
                        # Cerca link dettaglio
                        link = riga.find('a')
                        href = link.get('href') if link else ''
                        
                        if any(k in testo.lower() for k in ['deliber', 'determin', 'n.']):
                            result['atti'].append({
                                'testo': testo[:200],
                                'link': href
                            })
            
            if result['atti']:
                print(f"✅ Estratti {len(result['atti'])} atti con POST HTTP")
                result['metodo_funzionante'] = 'form_post_http'
                return result
            else:
                print("❌ Tabelle vuote o formato non riconosciuto")
        
        except Exception as e:
            print(f"❌ Errore POST HTTP: {e}")
        
        # METODO 2: Playwright con form compilation
        print("\n📍 Metodo 2: Playwright form automation")
        
        p = await async_playwright().start()
        browser = await p.chromium.launch(headless=True)
        page = await browser.new_page()
        
        try:
            # Intercetta response HTML
            responses = []
            
            async def handle_response(response):
                if 'AttiPubblicazioni' in response.url:
                    responses.append(response)
            
            page.on('response', handle_response)
            
            await page.goto('https://servizionline.comune.arezzo.it/jattipubblicazioni/')
            
            # Compila form
            await page.fill('input[name="dadata"]', '01/01/2024')
            await page.fill('input[name="adata"]', '31/12/2024')
            
            # Prova a selezionare 100, se non esiste usa 50
            try:
                await page.select_option('select[name="resultXPag"]', '100', timeout=5000)
            except:
                try:
                    await page.select_option('select[name="resultXPag"]', '50', timeout=5000)
                except:
                    print("⚠️  Select resultXPag fallito, uso default")
            
            # Submit
            await page.click('input[name="submit"]')
            await page.wait_for_timeout(5000)
            
            # Analizza HTML risultati
            content = await page.content()
            
            # Pattern regex per trovare atti
            patterns = [
                r'Delibera[^\d]*(\d+)[^\d]+(\d{4})',
                r'Determinazione[^\d]*(\d+)[^\d]+(\d{4})',
                r'<tr[^>]*>.*?</tr>'
            ]
            
            for pattern in patterns:
                matches = re.findall(pattern, content, re.DOTALL | re.IGNORECASE)
                if matches:
                    print(f"✅ Pattern '{pattern[:30]}...' trovato: {len(matches)} match")
                    
                    # Se sono tuple (numero, anno)
                    if isinstance(matches[0], tuple):
                        for numero, anno in matches[:50]:
                            result['atti'].append({
                                'numero': numero,
                                'anno': anno,
                                'tipo': 'Delibera' if 'Delibera' in pattern else 'Determinazione'
                            })
                    else:
                        # HTML righe
                        for match in matches[:50]:
                            soup_riga = BeautifulSoup(match, 'html.parser')
                            text = soup_riga.get_text(strip=True)
                            if any(k in text.lower() for k in ['deliber', 'determin']):
                                link = soup_riga.find('a')
                                result['atti'].append({
                                    'testo': text[:200],
                                    'link': link.get('href') if link else ''
                                })
                    
                    if result['atti']:
                        print(f"✅ Estratti {len(result['atti'])} atti con Playwright")
                        result['metodo_funzionante'] = 'playwright_form'
                        await browser.close()
                        await p.stop()
                        return result
        
        except Exception as e:
            print(f"❌ Errore Playwright: {e}")
        
        await browser.close()
        await p.stop()
        
        # FALLIMENTO
        print("\n❌ ESTRAZIONE FALLITA")
        result['problema'] = {
            'tipo': 'form_post_no_results',
            'dettaglio': 'Form POST funziona ma risposta non contiene atti parsabili',
            'soluzioni': [
                'API endpoint JSON: GET /api/atti?anno=2024',
                'Migliorare HTML risultati con classi semantic (class="atto-row")',
                'Export CSV risultati ricerca'
            ]
        }
        
        return result
    
    async def extract_all(self) -> List[dict]:
        """Estrai da tutti i comuni"""
        
        risultati = []
        
        # Arezzo (prima, è più stabile)
        risultati.append(await self.extract_arezzo())
        
        # Livorno
        risultati.append(await self.extract_livorno())
        
        # Siena
        risultati.append(await self.extract_siena())
        
        # Pisa (ultimo, ha timeout)
        # risultati.append(await self.extract_pisa())
        
        return risultati
    
    async def extract_livorno(self) -> dict:
        """Livorno - comune.livorno.it/trasparenza-vm/albo-pretorio"""
        print("\n" + "="*60)
        print("🏛️  LIVORNO - Tentativo Estrazione")
        print("="*60)
        
        result = {
            'comune': 'Livorno',
            'url': 'https://www.comune.livorno.it/trasparenza-vm/albo-pretorio',
            'atti': [],
            'metodo_funzionante': None,
            'problema': None
        }
        
        # TrasparenzaVM platform - Playwright obbligatorio
        p = await async_playwright().start()
        browser = await p.chromium.launch(headless=True)
        page = await browser.new_page()
        
        try:
            await page.goto('https://www.comune.livorno.it/trasparenza-vm/albo-pretorio', timeout=15000)
            
            content = await page.content()
            
            # Check Cloudflare
            if 'cloudflare' in content.lower() or 'checking your browser' in content.lower():
                print("🔒 Cloudflare protection attivo")
                result['problema'] = {
                    'tipo': 'cloudflare_protection',
                    'dettaglio': 'Cloudflare anti-bot blocca accesso automatizzato',
                    'soluzioni': [
                        'Disabilitare Cloudflare anti-bot per /albo-pretorio/* (€0, 30 min)',
                        'API JSON pubblica: GET /api/albo/atti (€500-1.000, 2-3 giorni)'
                    ]
                }
                await browser.close()
                await p.stop()
                return result
            
            # Cerca form e risultati
            await page.wait_for_timeout(3000)
            
            # Cerca link atti
            links = await page.query_selector_all('a')
            for link in links[:100]:
                text = await link.text_content()
                if text and any(k in text.lower() for k in ['deliber', 'determin']):
                    href = await link.get_attribute('href')
                    result['atti'].append({
                        'testo': text.strip()[:200],
                        'link': href
                    })
            
            if result['atti']:
                print(f"✅ Estratti {len(result['atti'])} atti")
                result['metodo_funzionante'] = 'playwright_links'
            else:
                print("❌ Nessun atto estratto")
                result['problema'] = {
                    'tipo': 'no_data_found',
                    'dettaglio': 'Sito accessibile ma nessun atto trovato'
                }
        
        except Exception as e:
            print(f"❌ Errore: {e}")
            result['problema'] = {
                'tipo': 'error',
                'dettaglio': str(e)
            }
        
        await browser.close()
        await p.stop()
        return result
    
    async def extract_siena(self) -> dict:
        """Siena - comune.siena.it/albo-pretorio"""
        print("\n" + "="*60)
        print("🏛️  SIENA - Tentativo Estrazione")
        print("="*60)
        
        result = {
            'comune': 'Siena',
            'url': 'https://www.comune.siena.it/albo-pretorio',
            'atti': [],
            'metodo_funzionante': None,
            'problema': None
        }
        
        # METODO 1: HTTP diretto
        print("\n📍 Metodo 1: HTTP + BeautifulSoup")
        try:
            resp = self.session.get('https://www.comune.siena.it/albo-pretorio', timeout=10)
            soup = BeautifulSoup(resp.content, 'html.parser')
            
            # Cerca iframe
            iframes = soup.find_all('iframe')
            print(f"Iframe trovati: {len(iframes)}")
            
            for iframe in iframes:
                src = iframe.get('src', '')
                if src and 'albo' in src.lower():
                    print(f"  → Iframe albo: {src}")
                    
                    # Prova ad accedere iframe
                    try:
                        iframe_url = src if src.startswith('http') else 'https://www.comune.siena.it' + src
                        iframe_resp = self.session.get(iframe_url, timeout=10)
                        iframe_soup = BeautifulSoup(iframe_resp.content, 'html.parser')
                        
                        links = iframe_soup.find_all('a')
                        for link in links:
                            text = link.get_text(strip=True)
                            if any(k in text.lower() for k in ['deliber', 'determin']):
                                result['atti'].append({
                                    'testo': text[:200],
                                    'link': link.get('href', '')
                                })
                        
                        if result['atti']:
                            print(f"✅ Estratti {len(result['atti'])} atti da iframe")
                            result['metodo_funzionante'] = 'http_iframe'
                            return result
                    except Exception as e:
                        print(f"  ❌ Errore iframe: {e}")
        
        except Exception as e:
            print(f"❌ Errore HTTP: {e}")
        
        # METODO 2: Playwright
        print("\n📍 Metodo 2: Playwright")
        
        p = await async_playwright().start()
        browser = await p.chromium.launch(headless=True)
        page = await browser.new_page()
        
        try:
            await page.goto('https://www.comune.siena.it/albo-pretorio', wait_until='networkidle')
            
            # Aspetta caricamento
            await page.wait_for_timeout(3000)
            
            # Scroll
            for _ in range(3):
                await page.evaluate('window.scrollBy(0, 1000)')
                await page.wait_for_timeout(500)
            
            # Cerca iframe
            iframes = await page.query_selector_all('iframe')
            print(f"Iframe (Playwright): {len(iframes)}")
            
            for iframe in iframes:
                try:
                    frame = await iframe.content_frame()
                    if frame:
                        await frame.wait_for_load_state('networkidle', timeout=5000)
                        
                        frame_links = await frame.query_selector_all('a')
                        for link in frame_links:
                            text = await link.text_content()
                            if text and any(k in text.lower() for k in ['deliber', 'determin']):
                                href = await link.get_attribute('href')
                                result['atti'].append({
                                    'testo': text.strip()[:200],
                                    'link': href
                                })
                        
                        if result['atti']:
                            print(f"✅ Estratti {len(result['atti'])} atti da iframe (Playwright)")
                            result['metodo_funzionante'] = 'playwright_iframe'
                            await browser.close()
                            await p.stop()
                            return result
                except Exception as e:
                    print(f"  ❌ Errore frame: {e}")
            
            # Cerca anche nella pagina principale
            main_links = await page.query_selector_all('a')
            for link in main_links:
                text = await link.text_content()
                if text and any(k in text.lower() for k in ['deliber', 'determin', 'pubblicaz']):
                    href = await link.get_attribute('href')
                    result['atti'].append({
                        'testo': text.strip()[:200],
                        'link': href
                    })
            
            if result['atti']:
                print(f"✅ Estratti {len(result['atti'])} atti da pagina principale")
                result['metodo_funzionante'] = 'playwright_main'
            else:
                print("❌ Nessun atto trovato")
                result['problema'] = {
                    'tipo': 'no_data_found',
                    'dettaglio': 'Pagina accessibile ma nessun atto individuato',
                    'soluzioni': [
                        'API JSON pubblica',
                        'Feed RSS atti',
                        'Export CSV'
                    ]
                }
        
        except Exception as e:
            print(f"❌ Errore Playwright: {e}")
            result['problema'] = {'tipo': 'error', 'dettaglio': str(e)}
        
        await browser.close()
        await p.stop()
        return result


async def main():
    extractor = AggressiveExtractor()
    
    print("🔥 AGGRESSIVE EXTRACTION - Tentativo TUTTI i metodi")
    print("="*60)
    
    risultati = await extractor.extract_all()
    
    # Summary
    print("\n" + "="*60)
    print("📊 RISULTATI FINALI")
    print("="*60)
    
    for r in risultati:
        print(f"\n🏛️  {r['comune']}")
        if r['atti']:
            print(f"   ✅ {len(r['atti'])} atti estratti")
            print(f"   Metodo: {r['metodo_funzionante']}")
        else:
            print(f"   ❌ Estrazione fallita")
            if r['problema']:
                print(f"   Problema: {r['problema']['tipo']}")
                print(f"   Soluzioni suggerite:")
                for sol in r['problema']['soluzioni']:
                    print(f"     - {sol}")
    
    # Salva risultati
    with open('aggressive_extraction_results.json', 'w', encoding='utf-8') as f:
        json.dump(risultati, f, indent=2, ensure_ascii=False)
    
    print("\n✅ Risultati salvati in aggressive_extraction_results.json")


if __name__ == '__main__':
    asyncio.run(main())
