#!/usr/bin/env python3
"""
Verifica URL dei comuni - controlla se gli URL base sono corretti
e cerca subdomain alternativi per l'albo pretorio
"""
import requests
import re
from urllib.parse import urlparse, urljoin
from bs4 import BeautifulSoup
import time
import json

COMUNI_TEST = [
    {'nome': 'Firenze', 'url': 'https://www.comune.fi.it'},
    {'nome': 'Sesto Fiorentino', 'url': 'https://www.sestofiorentino.it'},
    {'nome': 'Scandicci', 'url': 'https://www.comune.scandicci.fi.it'},
    {'nome': 'Empoli', 'url': 'https://www.empoli.gov.it'},
    {'nome': 'Campi Bisenzio', 'url': 'https://www.comune.campi-bisenzio.fi.it'},
    {'nome': 'Bagno a Ripoli', 'url': 'https://www.comune.bagno-a-ripoli.fi.it'},
    {'nome': 'Fiesole', 'url': 'https://www.comune.fiesole.fi.it'},
    {'nome': 'Pontassieve', 'url': 'https://www.comune.pontassieve.fi.it'},
    {'nome': 'Borgo San Lorenzo', 'url': 'https://www.comune.borgo-san-lorenzo.fi.it'},
    {'nome': 'Calenzano', 'url': 'https://www.comune.calenzano.fi.it'}
]

def verifica_url(comune):
    """Verifica URL e cerca subdomain albo"""
    nome = comune['nome']
    url = comune['url']
    
    print(f"\n{'='*80}")
    print(f"🔍 {nome}")
    print(f"{'='*80}")
    print(f"URL dichiarato: {url}")
    
    risultato = {
        'comune': nome,
        'url_dichiarato': url,
        'url_raggiungibile': False,
        'status_code': None,
        'url_reale': None,
        'redirect': False,
        'subdomain_albo': [],
        'link_albo_trovati': []
    }
    
    # 1. Verifica URL base
    try:
        response = requests.get(url, timeout=10, allow_redirects=True)
        risultato['status_code'] = response.status_code
        risultato['url_reale'] = response.url
        risultato['url_raggiungibile'] = response.status_code == 200
        
        if response.url != url:
            risultato['redirect'] = True
            print(f"  ⚠️  REDIRECT: {url} → {response.url}")
        
        print(f"  ✅ URL base raggiungibile (HTTP {response.status_code})")
        
        # 2. Cerca link albo nella homepage
        soup = BeautifulSoup(response.text, 'lxml')
        
        # Pattern comuni per link albo
        albo_keywords = ['albo', 'trasparenza', 'pubblicazioni', 'atti', 'pretorio']
        
        for link in soup.find_all('a', href=True):
            href = link['href']
            text = link.get_text().strip().lower()
            
            # Se il testo contiene keyword albo
            if any(k in text for k in albo_keywords):
                full_url = urljoin(response.url, href)
                risultato['link_albo_trovati'].append({
                    'text': text[:100],
                    'url': full_url
                })
        
        if risultato['link_albo_trovati']:
            print(f"  📑 Link albo trovati: {len(risultato['link_albo_trovati'])}")
            for i, link in enumerate(risultato['link_albo_trovati'][:3], 1):
                print(f"    {i}. {link['text'][:50]} -> {link['url'][:70]}")
        
        # 3. Cerca subdomain comuni
        domain_parts = urlparse(response.url).netloc.split('.')
        if len(domain_parts) >= 2:
            base_domain = '.'.join(domain_parts[-2:])
            
            # Subdomain comuni da testare
            subdomain_patterns = [
                f"http://servizi.comune.{base_domain}",
                f"https://servizi.comune.{base_domain}",
                f"http://albo.comune.{base_domain}",
                f"https://albo.comune.{base_domain}",
                f"http://albopretorio.comune.{base_domain}",
                f"https://albopretorio.comune.{base_domain}",
                f"https://trasparenza.comune.{base_domain}",
                f"https://pubblicazioni.comune.{base_domain}",
            ]
            
            # Per Sesto: prova anche varianti nome
            if 'sesto' in nome.lower():
                nome_normalized = 'sesto-fiorentino'
                subdomain_patterns.extend([
                    f"http://servizi.comune.{nome_normalized}.fi.it",
                    f"http://albo.comune.{nome_normalized}.fi.it",
                ])
            
            print(f"  🔎 Testo {len(subdomain_patterns)} subdomain possibili...")
            for subdomain in subdomain_patterns:
                try:
                    test_resp = requests.head(subdomain, timeout=5, allow_redirects=True)
                    if test_resp.status_code == 200:
                        risultato['subdomain_albo'].append(subdomain)
                        print(f"    ✅ TROVATO: {subdomain}")
                except:
                    pass
        
        if not risultato['subdomain_albo']:
            print(f"  ⚠️  Nessun subdomain albo trovato")
    
    except requests.exceptions.Timeout:
        print(f"  ❌ TIMEOUT: {url} non risponde")
        risultato['status_code'] = 'TIMEOUT'
    
    except requests.exceptions.ConnectionError as e:
        print(f"  ❌ CONNECTION ERROR: {str(e)[:100]}")
        risultato['status_code'] = 'CONNECTION_ERROR'
    
    except Exception as e:
        print(f"  ❌ ERRORE: {str(e)[:100]}")
        risultato['status_code'] = f'ERROR: {type(e).__name__}'
    
    time.sleep(1)  # Rate limiting
    return risultato

def main():
    print("🔥 VERIFICA URL COMUNI")
    print("="*80)
    print("Controlla se gli URL dichiarati sono corretti")
    print("e cerca subdomain alternativi per albo pretorio")
    print("="*80)
    
    risultati = []
    
    for comune in COMUNI_TEST:
        risultato = verifica_url(comune)
        risultati.append(risultato)
    
    # Salva report
    with open('verifica_url_comuni_report.json', 'w', encoding='utf-8') as f:
        json.dump({
            'timestamp': time.strftime('%Y-%m-%d %H:%M:%S'),
            'totale_comuni': len(risultati),
            'raggiungibili': sum(1 for r in risultati if r['url_raggiungibile']),
            'con_redirect': sum(1 for r in risultati if r['redirect']),
            'con_subdomain_albo': sum(1 for r in risultati if r['subdomain_albo']),
            'risultati': risultati
        }, f, indent=2, ensure_ascii=False)
    
    # Riepilogo
    print("\n" + "="*80)
    print("📊 RIEPILOGO")
    print("="*80)
    
    raggiungibili = [r for r in risultati if r['url_raggiungibile']]
    non_raggiungibili = [r for r in risultati if not r['url_raggiungibile']]
    con_redirect = [r for r in risultati if r['redirect']]
    con_subdomain = [r for r in risultati if r['subdomain_albo']]
    
    print(f"Comuni testati: {len(risultati)}")
    print(f"✅ Raggiungibili: {len(raggiungibili)}")
    print(f"❌ Non raggiungibili: {len(non_raggiungibili)}")
    print(f"🔀 Con redirect: {len(con_redirect)}")
    print(f"🌐 Con subdomain albo: {len(con_subdomain)}")
    
    if non_raggiungibili:
        print(f"\n❌ COMUNI NON RAGGIUNGIBILI:")
        for r in non_raggiungibili:
            print(f"  - {r['comune']}: {r['url_dichiarato']} ({r['status_code']})")
    
    if con_redirect:
        print(f"\n🔀 COMUNI CON REDIRECT (URL da aggiornare):")
        for r in con_redirect:
            print(f"  - {r['comune']}")
            print(f"    Vecchio: {r['url_dichiarato']}")
            print(f"    Nuovo:   {r['url_reale']}")
    
    if con_subdomain:
        print(f"\n🌐 SUBDOMAIN ALBO TROVATI:")
        for r in con_subdomain:
            print(f"  - {r['comune']}:")
            for sub in r['subdomain_albo']:
                print(f"    ✅ {sub}")
    
    print(f"\n💾 Report salvato: verifica_url_comuni_report.json")

if __name__ == '__main__':
    main()
