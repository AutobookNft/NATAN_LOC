#!/usr/bin/env python3
"""
ANALISI PROFONDA COMUNI - STESSO APPROCCIO DI FIRENZE
======================================================
Non ci accontentiamo di cercare link - ANALIZZIAMO TUTTO come fatto per Firenze:
- Intercettiamo richieste di rete
- Testiamo form con Playwright
- Replicamo POST requests
- Cerchiamo API nascoste
"""
import json
import re
import time
from datetime import datetime
from typing import Dict, List, Optional
from urllib.parse import urljoin
import requests
from playwright.sync_api import sync_playwright

# Import lista comuni
from comuni_toscana_data import COMUNI_TOSCANA_COMPLETI


class AnalizzatoreProfondoComuni:
    """Analisi PROFONDA - come fatto per Firenze"""
    
    def __init__(self):
        self.risultati = []
        self.stats = {
            'totale': 0,
            'funzionanti': 0,
            'form_post': 0,
            'api_trovate': 0,
            'atti_totali': 0
        }
    
    def analizza_firenze(self) -> Dict:
        """Firenze - metodo VERIFICATO"""
        print("🔍 Firenze - metodo verificato API")
        
        url = "https://accessoconcertificato.comune.fi.it/trasparenza-atti-cat/searchAtti"
        payload = {
            'oggetto': '',
            'notLoadIniziale': 'ok',
            'numeroAdozione': '',
            'competenza': 'DG',
            'annoAdozione': '2025',
            'tipiAtto': ['DG']
        }
        
        try:
            response = requests.post(url, json=payload, timeout=15)
            if response.status_code == 200:
                data = response.json()
                return {
                    'comune': 'Firenze',
                    'abitanti': 367150,
                    'funzionante': True,
                    'metodo': 'API_REST',
                    'atti_recuperabili': len(data),
                    'url': url,
                    'payload': payload,
                    'note': f'{len(data)} delibere 2025 via API verificata'
                }
        except Exception as e:
            pass
        
        return {
            'comune': 'Firenze',
            'abitanti': 367150,
            'funzionante': False,
            'note': 'Errore API Firenze'
        }
    
    def analizza_con_playwright(self, nome: str, url_base: str, abitanti: int) -> Dict:
        """
        ANALISI PROFONDA con Playwright - STESSO METODO FIRENZE
        1. Intercetta TUTTE le richieste
        2. Naviga alla homepage
        3. Trova link albo
        4. Cerca form di ricerca
        5. Compila form con date 2025
        6. Cattura POST request
        7. Analizza risposta
        """
        print(f"  🔍 Playwright: intercetto richieste...")
        
        try:
            with sync_playwright() as p:
                browser = p.chromium.launch(headless=True)
                context = browser.new_context(
                    user_agent='Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                )
                page = context.new_page()
                
                # Log TUTTE le richieste
                requests_log = []
                
                def log_request(request):
                    requests_log.append({
                        'url': request.url,
                        'method': request.method,
                        'post_data': request.post_data,
                        'resource_type': request.resource_type
                    })
                
                page.on('request', log_request)
                
                # Homepage
                print(f"  📄 Carico homepage...")
                page.goto(url_base, timeout=20000, wait_until='networkidle')
                
                # Cerca link albo/trasparenza
                albo_links = []
                links = page.query_selector_all('a')
                
                for link in links:
                    href = link.get_attribute('href')
                    text = (link.text_content() or '').strip().lower()
                    
                    if href and any(k in text for k in ['albo', 'trasparenza', 'pubblicazioni', 'atti']):
                        if not href.startswith('#') and not href.startswith('javascript:'):
                            full_url = urljoin(url_base, href)
                            albo_links.append({'text': text[:50], 'url': full_url})
                
                print(f"  🔗 Link albo trovati: {len(albo_links)}")
                
                if not albo_links:
                    browser.close()
                    return {
                        'comune': nome,
                        'abitanti': abitanti,
                        'funzionante': False,
                        'tag': 'NO_ALBO_LINK',
                        'note': 'Nessun link albo/trasparenza trovato in homepage'
                    }
                
                # Segui primo link albo
                albo_url = albo_links[0]['url']
                print(f"  📑 Navigo a: {albo_url[:60]}...")
                
                requests_before = len(requests_log)
                page.goto(albo_url, timeout=20000, wait_until='networkidle')
                
                # Cerca form di ricerca
                forms = page.query_selector_all('form')
                print(f"  📝 Form trovati: {len(forms)}")
                
                if not forms:
                    # Nessun form - cerca API nelle richieste
                    api_reqs = [r for r in requests_log if 
                               'api' in r['url'].lower() or 
                               'search' in r['url'].lower() or
                               'atti' in r['url'].lower()]
                    
                    if api_reqs:
                        browser.close()
                        return {
                            'comune': nome,
                            'abitanti': abitanti,
                            'funzionante': False,
                            'tag': 'API_TROVATA',
                            'url_api': api_reqs[0]['url'],
                            'note': f'API trovata ma richiede payload - {len(api_reqs)} endpoint'
                        }
                    
                    browser.close()
                    return {
                        'comune': nome,
                        'abitanti': abitanti,
                        'funzionante': False,
                        'tag': 'NO_FORM',
                        'url_albo': albo_url,
                        'note': 'Pagina albo senza form di ricerca'
                    }
                
                # COMPILA FORM (come Firenze)
                print(f"  ✍️  Compilo form...")
                form = forms[0]
                
                # Trova campi
                inputs = form.query_selector_all('input, select, textarea')
                form_filled = False
                
                for inp in inputs:
                    name = inp.get_attribute('name')
                    input_type = inp.get_attribute('type')
                    tag = inp.evaluate('el => el.tagName').lower()
                    
                    if not name:
                        continue
                    
                    name_lower = name.lower()
                    
                    try:
                        # Date
                        if any(k in name_lower for k in ['data', 'date', 'from', 'to', 'inizio', 'fine']):
                            if any(k in name_lower for k in ['da', 'from', 'inizio', 'start']):
                                page.fill(f'[name="{name}"]', '01/01/2025')
                            else:
                                page.fill(f'[name="{name}"]', '13/11/2025')
                            form_filled = True
                        
                        # Anno
                        elif 'anno' in name_lower or 'year' in name_lower:
                            if tag == 'select':
                                # Prova a selezionare 2025
                                try:
                                    page.select_option(f'[name="{name}"]', '2025')
                                except:
                                    # Se non c'è 2025, prendi ultima opzione
                                    options = inp.query_selector_all('option')
                                    if options:
                                        last_val = options[-1].get_attribute('value')
                                        if last_val:
                                            page.select_option(f'[name="{name}"]', last_val)
                            else:
                                page.fill(f'[name="{name}"]', '2025')
                            form_filled = True
                        
                        # Tipo atto
                        elif 'tipo' in name_lower and tag == 'select':
                            options = inp.query_selector_all('option')
                            for opt in options:
                                opt_text = (opt.text_content() or '').lower()
                                opt_val = opt.get_attribute('value')
                                if opt_val and ('deliber' in opt_text or 'all' in opt_text or 'tutt' in opt_text):
                                    page.select_option(f'[name="{name}"]', opt_val)
                                    form_filled = True
                                    break
                        
                        # Risultati per pagina
                        elif any(k in name_lower for k in ['result', 'limit', 'pagina']):
                            if tag == 'select':
                                options = inp.query_selector_all('option')
                                # Prendi valore più alto
                                max_val = '50'
                                for opt in options:
                                    val = opt.get_attribute('value')
                                    if val and val.isdigit() and int(val) > int(max_val):
                                        max_val = val
                                try:
                                    page.select_option(f'[name="{name}"]', max_val)
                                except:
                                    pass
                    
                    except Exception as e:
                        print(f"    ⚠️ Campo {name}: {e}")
                        continue
                
                if not form_filled:
                    browser.close()
                    return {
                        'comune': nome,
                        'abitanti': abitanti,
                        'funzionante': False,
                        'tag': 'FORM_NO_DATE',
                        'url_albo': albo_url,
                        'note': 'Form trovato ma nessun campo data/anno riconoscibile'
                    }
                
                # SUBMIT
                print(f"  🚀 Submit form...")
                requests_before_submit = len(requests_log)
                
                submit_btn = form.query_selector('input[type="submit"], button[type="submit"], button:has-text("Cerca"), button:has-text("Ricerca")')
                
                if submit_btn:
                    submit_btn.click()
                    page.wait_for_timeout(4000)  # Aspetta risposta
                    
                    # Analizza richieste POST
                    new_requests = requests_log[requests_before_submit:]
                    post_requests = [r for r in new_requests if r['method'] == 'POST']
                    
                    print(f"  📨 POST requests: {len(post_requests)}")
                    
                    # Analizza contenuto pagina risultati
                    content = page.content()
                    
                    # Cerca atti nella risposta
                    delibere = re.findall(r'delibera[^\d]*(\d+)', content, re.I)
                    determine = re.findall(r'determinazione[^\d]*(\d+)', content, re.I)
                    
                    # Cerca anche tabelle/liste
                    rows = page.query_selector_all('tr, li.atto, div.atto, article')
                    
                    atti_count = max(len(set(delibere)), len(set(determine)), len(rows) - 5)  # -5 per header/footer
                    
                    print(f"  📊 Atti trovati: {atti_count} (delibere: {len(set(delibere))}, determine: {len(set(determine))}, rows: {len(rows)})")
                    
                    if atti_count > 0:
                        # SUCCESSO!
                        result = {
                            'comune': nome,
                            'abitanti': abitanti,
                            'funzionante': True,
                            'metodo': 'FORM_POST',
                            'atti_recuperabili': atti_count,
                            'url_albo': albo_url,
                            'note': f'{atti_count} atti tramite form'
                        }
                        
                        # Aggiungi POST data se disponibile
                        if post_requests:
                            result['url_post'] = post_requests[0]['url']
                            result['post_data'] = post_requests[0]['post_data']
                            result['note'] += ' - POST intercettata'
                        
                        browser.close()
                        return result
                    
                    # Nessun atto visibile
                    if post_requests:
                        browser.close()
                        return {
                            'comune': nome,
                            'abitanti': abitanti,
                            'funzionante': False,
                            'tag': 'FORM_POST_0_ATTI',
                            'url_post': post_requests[0]['url'],
                            'post_data': post_requests[0]['post_data'],
                            'note': 'Form funziona ma 0 atti nel periodo 2025 (provare altri anni?)'
                        }
                
                browser.close()
                return {
                    'comune': nome,
                    'abitanti': abitanti,
                    'funzionante': False,
                    'tag': 'FORM_NO_SUBMIT',
                    'url_albo': albo_url,
                    'note': 'Form trovato ma submit non disponibile/funzionante'
                }
                
        except Exception as e:
            return {
                'comune': nome,
                'abitanti': abitanti,
                'funzionante': False,
                'tag': 'ERRORE_PLAYWRIGHT',
                'note': f'Errore: {str(e)[:100]}'
            }
    
    def analizza_comune(self, comune: Dict) -> Dict:
        """Analisi COMPLETA di un comune"""
        nome = comune['nome']
        url = comune['url']
        abitanti = comune['abitanti']
        
        print(f"\n{'='*80}")
        print(f"[{self.stats['totale'] + 1}] {nome} ({abitanti:,} ab.)")
        print(f"{'='*80}")
        
        # Firenze ha metodo specifico
        if nome == 'Firenze':
            return self.analizza_firenze()
        
        # TUTTI gli altri: analisi PROFONDA Playwright
        return self.analizza_con_playwright(nome, url, abitanti)
    
    def analizza_tutti(self, comuni: List[Dict], salva_ogni: int = 5):
        """Analizza tutti i comuni"""
        print("="*80)
        print("ANALISI PROFONDA COMUNI TOSCANA")
        print("Metodo: STESSO APPROCCIO DI FIRENZE")
        print("="*80)
        print(f"Comuni da analizzare: {len(comuni)}")
        print(f"Inizio: {datetime.now().strftime('%H:%M:%S')}")
        print("="*80)
        
        for i, comune in enumerate(comuni):
            risultato = self.analizza_comune(comune)
            
            self.risultati.append(risultato)
            self.stats['totale'] += 1
            
            if risultato['funzionante']:
                self.stats['funzionanti'] += 1
                self.stats['atti_totali'] += risultato.get('atti_recuperabili', 0)
                
                if risultato['metodo'] == 'FORM_POST':
                    self.stats['form_post'] += 1
                
                print(f"✅ {risultato['metodo']} - {risultato['atti_recuperabili']} atti")
            else:
                tag = risultato.get('tag', 'ERRORE')
                print(f"❌ {tag} - {risultato['note'][:60]}")
            
            # Salva progressivo
            if (i + 1) % salva_ogni == 0:
                self.salva_risultati()
                print(f"\n💾 Salvati risultati dopo {i+1} comuni")
            
            # Rate limiting
            time.sleep(0.5)
        
        self.salva_risultati()
        self.stampa_riepilogo()
    
    def salva_risultati(self):
        """Salva risultati JSON"""
        output = {
            'timestamp': datetime.now().isoformat(),
            'stats': self.stats,
            'comuni': self.risultati
        }
        
        with open('comuni_analisi_profonda_results.json', 'w', encoding='utf-8') as f:
            json.dump(output, f, indent=2, ensure_ascii=False)
    
    def stampa_riepilogo(self):
        """Stampa riepilogo finale"""
        print("\n" + "="*80)
        print("RIEPILOGO ANALISI PROFONDA")
        print("="*80)
        print(f"Totale comuni: {self.stats['totale']}")
        print(f"✅ Funzionanti: {self.stats['funzionanti']}")
        print(f"   - API REST: {self.stats['funzionanti'] - self.stats['form_post']}")
        print(f"   - FORM POST: {self.stats['form_post']}")
        print(f"📄 Atti totali: {self.stats['atti_totali']}")
        
        if self.stats['totale'] > 0:
            coverage = (self.stats['funzionanti'] / self.stats['totale']) * 100
            print(f"📈 Coverage: {coverage:.1f}%")
        
        print(f"\n💾 Risultati: comuni_analisi_profonda_results.json")


if __name__ == '__main__':
    analizzatore = AnalizzatoreProfondoComuni()
    
    # Test preliminare Firenze
    print("TEST PRELIMINARE FIRENZE")
    print("="*80)
    test_firenze = analizzatore.analizza_firenze()
    print(json.dumps(test_firenze, indent=2, ensure_ascii=False))
    
    if not test_firenze['funzionante']:
        print("\n❌ FIRENZE FALLITO")
        exit(1)
    
    print(f"\n✅ FIRENZE OK - {test_firenze['atti_recuperabili']} atti")
    print("="*80)
    
    # Analizza primi 20 comuni per test
    print("\n🚀 Avvio analisi PROFONDA su primi 20 comuni...")
    print("(modificare script per analizzare tutti i 255)")
    time.sleep(2)
    
    analizzatore.analizza_tutti(COMUNI_TOSCANA_COMPLETI[:20], salva_ogni=5)
