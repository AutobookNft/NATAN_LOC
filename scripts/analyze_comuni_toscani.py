#!/usr/bin/env python3
"""
Analisi automatica albi pretori comuni toscani
Identifica piattaforme, pattern DOM, metadata disponibili
"""

import sys
from pathlib import Path

# Add NATAN_LOC python_ai_service to path
NATAN_LOC_ROOT = Path(__file__).parent.parent
sys.path.insert(0, str(NATAN_LOC_ROOT / "python_ai_service"))

# Add venv site-packages to sys.path
venv_site_packages = NATAN_LOC_ROOT / "python_ai_service" / "venv" / "lib" / "python3.12" / "site-packages"
if venv_site_packages.exists() and str(venv_site_packages) not in sys.path:
    sys.path.insert(0, str(venv_site_packages))

# Try alternative paths for different Python versions
for python_version in ['3.12', '3.11', '3.10', '3.9']:
    alt_path = NATAN_LOC_ROOT / "python_ai_service" / "venv" / "lib" / f"python{python_version}" / "site-packages"
    if alt_path.exists() and str(alt_path) not in sys.path:
        sys.path.insert(0, str(alt_path))

import requests
from bs4 import BeautifulSoup
import json
from datetime import datetime
from urllib.parse import urlparse, urljoin
import time
import re

class AlboPretorioAnalyzer:
    """Analizza struttura albi pretori per identificare pattern comuni"""
    
    def __init__(self):
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        })
        
        # Database comuni toscani con URL albo pretorio
        self.comuni = {
            'firenze': {
                'nome': 'Firenze',
                'provincia': 'FI',
                'popolazione': 382258,
                'urls': [
                    'https://accessoconcertificato.comune.fi.it/AOL/Affissione/ComuneFi/Page',
                    'https://accessoconcertificato.comune.fi.it/trasparenza-atti/'
                ]
            },
            'prato': {
                'nome': 'Prato',
                'provincia': 'PO',
                'popolazione': 195763,
                'urls': [
                    'https://www.comune.prato.it/albo/',
                    'https://trasparenza.comune.prato.it/'
                ]
            },
            'livorno': {
                'nome': 'Livorno',
                'provincia': 'LI',
                'popolazione': 157139,
                'urls': [
                    'https://livorno.trasparenza-valutazione-merito.it/web/trasparenza/albo-pretorio',
                    'https://www.comune.livorno.it/'
                ]
            },
            'arezzo': {
                'nome': 'Arezzo',
                'provincia': 'AR',
                'popolazione': 99543,
                'urls': [
                    'https://www.comune.arezzo.it/albo-pretorio',
                    'https://albo.comune.arezzo.it/'
                ]
            },
            'siena': {
                'nome': 'Siena',
                'provincia': 'SI',
                'popolazione': 53903,
                'urls': [
                    'https://www.comune.siena.it/albo-pretorio',
                    'https://albopretorio.comune.siena.it/'
                ]
            },
            'pistoia': {
                'nome': 'Pistoia',
                'provincia': 'PT',
                'popolazione': 90363,
                'urls': [
                    'https://pistoia.trasparenza-valutazione-merito.it/web/trasparenzaj/albo-pretorio',
                    'https://www.comune.pistoia.it/'
                ]
            },
            'lucca': {
                'nome': 'Lucca',
                'provincia': 'LU',
                'popolazione': 89046,
                'urls': [
                    'https://www.comune.lucca.it/albo-pretorio',
                    'https://albopretorio.comune.lucca.it/'
                ]
            },
            'grosseto': {
                'nome': 'Grosseto',
                'provincia': 'GR',
                'popolazione': 82284,
                'urls': [
                    'https://grosseto.trasparenza-valutazione-merito.it/',
                    'https://www.comune.grosseto.it/'
                ]
            },
            'massa': {
                'nome': 'Massa',
                'provincia': 'MS',
                'popolazione': 68511,
                'urls': [
                    'https://cloud.urbi.it/urbi/progs/urp/ur1ME001.sto?DB_NAME=n201312',
                    'https://www.comune.massa.ms.it/'
                ]
            },
            'pisa': {
                'nome': 'Pisa',
                'provincia': 'PI',
                'popolazione': 90745,
                'urls': [
                    'https://albopretorio.comune.pisa.it/',
                    'https://www.comune.pisa.it/'
                ]
            },
            'carrara': {
                'nome': 'Carrara',
                'provincia': 'MS',
                'popolazione': 62592,
                'urls': [
                    'https://carrara.trasparenza-valutazione-merito.it/web/trasparenza/albo-pretorio',
                    'https://www.comune.carrara.ms.it/'
                ]
            },
            'viareggio': {
                'nome': 'Viareggio',
                'provincia': 'LU',
                'popolazione': 62467,
                'urls': [
                    'https://www.comune.viareggio.lu.it/albo-pretorio',
                    'https://albopretorio.comune.viareggio.lu.it/'
                ]
            },
            'bagno_a_ripoli': {
                'nome': 'Bagno a Ripoli',
                'provincia': 'FI',
                'popolazione': 26035,
                'urls': [
                    'https://trasparenza.comune.bagno-a-ripoli.fi.it/web/trasparenza/menu-albo-pretorio',
                    'https://www.comune.bagno-a-ripoli.fi.it/'
                ]
            }
        }
    
    def analyze_url(self, url: str, timeout: int = 10) -> dict:
        """Analizza un singolo URL per identificare pattern"""
        try:
            print(f"  📡 Testando: {url}")
            response = self.session.get(url, timeout=timeout, allow_redirects=True)
            
            analysis = {
                'url': url,
                'final_url': response.url,
                'status_code': response.status_code,
                'accessible': response.status_code == 200,
                'redirect': url != response.url,
                'content_type': response.headers.get('Content-Type', ''),
                'server': response.headers.get('Server', ''),
                'timestamp': datetime.now().isoformat()
            }
            
            if analysis['accessible']:
                soup = BeautifulSoup(response.text, 'html.parser')
                
                # Detect platform/CMS
                analysis['platform'] = self.detect_platform(response.text, soup)
                
                # Detect patterns
                analysis['patterns'] = self.detect_patterns(soup)
                
                # Find API endpoints
                analysis['api_endpoints'] = self.find_api_endpoints(response.text, soup)
                
                # Check for common elements
                analysis['elements'] = self.analyze_elements(soup)
                
                print(f"     ✅ Accessibile - Platform: {analysis['platform']['name']}")
            else:
                print(f"     ❌ Status {response.status_code}")
            
            return analysis
            
        except requests.exceptions.Timeout:
            print(f"     ⏱️  Timeout")
            return {'url': url, 'accessible': False, 'error': 'timeout'}
        except requests.exceptions.ConnectionError:
            print(f"     ❌ Connection error")
            return {'url': url, 'accessible': False, 'error': 'connection_error'}
        except Exception as e:
            print(f"     ❌ Error: {e}")
            return {'url': url, 'accessible': False, 'error': str(e)}
    
    def detect_platform(self, html: str, soup: BeautifulSoup) -> dict:
        """Identifica la piattaforma/CMS utilizzato"""
        platform = {
            'name': 'Unknown',
            'vendor': None,
            'version': None,
            'indicators': []
        }
        
        # Check for common platforms
        platforms_signatures = {
            'Trasparenza VM': ['trasparenza-valutazione-merito.it'],
            'URBI': ['urbi.it', 'cloud.urbi'],
            'Halley': ['halley', 'publics.halleyweb'],
            'Insiel': ['insiel', 'insielweb'],
            'J-Iris': ['j-iris', 'jiris'],
            'WordPress': ['wp-content', 'wp-includes'],
            'Drupal': ['drupal', '/sites/all/'],
            'Joomla': ['joomla', '/components/'],
            'AmtAB': ['amtab', 'amt-ab'],
            'Civilia': ['civilia', 'civiliasuite'],
            'Istituzionale': ['istituzionale', 'sito-istituzionale']
        }
        
        html_lower = html.lower()
        
        for platform_name, signatures in platforms_signatures.items():
            for signature in signatures:
                if signature in html_lower:
                    platform['name'] = platform_name
                    platform['indicators'].append(signature)
                    break
            if platform['name'] != 'Unknown':
                break
        
        # Check meta tags for more info
        generator = soup.find('meta', attrs={'name': 'generator'})
        if generator:
            platform['vendor'] = generator.get('content', '')
        
        return platform
    
    def detect_patterns(self, soup: BeautifulSoup) -> dict:
        """Identifica pattern comuni nel DOM"""
        patterns = {
            'has_table_layout': bool(soup.find('table', class_=re.compile(r'(albo|atto|delibera|determina)', re.I))),
            'has_card_layout': bool(soup.find('div', class_=re.compile(r'card', re.I))),
            'has_list_layout': bool(soup.find('ul', class_=re.compile(r'(albo|atti|list)', re.I))),
            'has_pagination': bool(soup.find(['nav', 'div'], class_=re.compile(r'pag', re.I))),
            'has_search_form': bool(soup.find('form', id=re.compile(r'search', re.I))),
            'has_pdf_links': len(soup.find_all('a', href=re.compile(r'\.pdf$', re.I))) > 0,
            'pdf_count': len(soup.find_all('a', href=re.compile(r'\.pdf$', re.I)))
        }
        
        return patterns
    
    def find_api_endpoints(self, html: str, soup: BeautifulSoup) -> list:
        """Trova possibili endpoint API nel codice JavaScript"""
        endpoints = []
        
        # Search in script tags
        scripts = soup.find_all('script')
        for script in scripts:
            script_text = script.string or ''
            
            # Look for API URLs
            api_patterns = [
                r'["\']([^"\']*api[^"\']*)["\']',
                r'["\']([^"\']*search[^"\']*)["\']',
                r'fetch\s*\(\s*["\']([^"\']+)["\']',
                r'axios\.(get|post)\s*\(\s*["\']([^"\']+)["\']'
            ]
            
            for pattern in api_patterns:
                matches = re.findall(pattern, script_text, re.I)
                for match in matches:
                    if isinstance(match, tuple):
                        match = match[-1]  # Last group
                    if match and len(match) > 5:
                        endpoints.append(match)
        
        # Deduplicate and filter
        endpoints = list(set(endpoints))
        endpoints = [e for e in endpoints if any(keyword in e.lower() for keyword in ['api', 'search', 'atti', 'albo'])]
        
        return endpoints[:10]  # Top 10
    
    def analyze_elements(self, soup: BeautifulSoup) -> dict:
        """Analizza elementi comuni nel DOM"""
        return {
            'total_links': len(soup.find_all('a')),
            'total_images': len(soup.find_all('img')),
            'total_forms': len(soup.find_all('form')),
            'total_tables': len(soup.find_all('table')),
            'has_react': bool(soup.find(id='root')) or bool(soup.find(id='app')),
            'has_vue': 'vue' in str(soup).lower(),
            'has_angular': 'ng-app' in str(soup),
        }
    
    def analyze_comune(self, comune_key: str) -> dict:
        """Analizza tutti gli URL di un comune"""
        comune = self.comuni[comune_key]
        print(f"\n{'='*70}")
        print(f"🏛️  {comune['nome']} ({comune['provincia']}) - Pop: {comune['popolazione']:,}")
        print(f"{'='*70}")
        
        results = {
            'comune': comune['nome'],
            'provincia': comune['provincia'],
            'popolazione': comune['popolazione'],
            'urls_tested': [],
            'accessible_urls': [],
            'platforms_detected': [],
            'analysis_timestamp': datetime.now().isoformat()
        }
        
        for url in comune['urls']:
            analysis = self.analyze_url(url)
            results['urls_tested'].append(analysis)
            
            if analysis.get('accessible'):
                results['accessible_urls'].append(url)
                platform = analysis.get('platform', {}).get('name')
                if platform and platform != 'Unknown':
                    results['platforms_detected'].append(platform)
            
            time.sleep(1)  # Rate limiting
        
        results['platforms_detected'] = list(set(results['platforms_detected']))
        
        print(f"\n📊 Riepilogo {comune['nome']}:")
        print(f"   URL accessibili: {len(results['accessible_urls'])}/{len(comune['urls'])}")
        print(f"   Piattaforme rilevate: {', '.join(results['platforms_detected']) or 'Nessuna'}")
        
        return results
    
    def analyze_all(self, output_file: str = 'docs/scraper_analysis/analisi_comuni_toscani.json'):
        """Analizza tutti i comuni"""
        print("🚀 INIZIO ANALISI COMUNI TOSCANI")
        print("="*70)
        print(f"📋 Comuni da analizzare: {len(self.comuni)}")
        print("="*70)
        
        all_results = {
            'analysis_date': datetime.now().isoformat(),
            'total_comuni': len(self.comuni),
            'comuni': {}
        }
        
        for comune_key in sorted(self.comuni.keys()):
            results = self.analyze_comune(comune_key)
            all_results['comuni'][comune_key] = results
            time.sleep(2)  # Pausa tra comuni
        
        # Statistiche globali
        stats = {
            'total_urls_tested': sum(len(c['urls_tested']) for c in all_results['comuni'].values()),
            'total_accessible': sum(len(c['accessible_urls']) for c in all_results['comuni'].values()),
            'platforms_summary': {}
        }
        
        # Conta piattaforme
        for comune_data in all_results['comuni'].values():
            for platform in comune_data['platforms_detected']:
                stats['platforms_summary'][platform] = stats['platforms_summary'].get(platform, 0) + 1
        
        all_results['statistics'] = stats
        
        # Salva risultati
        import os
        os.makedirs(os.path.dirname(output_file), exist_ok=True)
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(all_results, f, indent=2, ensure_ascii=False)
        
        print(f"\n{'='*70}")
        print(f"✅ ANALISI COMPLETATA")
        print(f"{'='*70}")
        print(f"📊 Statistiche Globali:")
        print(f"   URL testati: {stats['total_urls_tested']}")
        print(f"   URL accessibili: {stats['total_accessible']}")
        print(f"\n🏗️  Piattaforme rilevate:")
        for platform, count in sorted(stats['platforms_summary'].items(), key=lambda x: x[1], reverse=True):
            print(f"   - {platform}: {count} comuni")
        print(f"\n💾 Risultati salvati: {output_file}")
        
        return all_results


def main():
    analyzer = AlboPretorioAnalyzer()
    results = analyzer.analyze_all()
    
    print("\n🎯 Prossimo step: Analisi manuale dettagliata delle piattaforme rilevate")


if __name__ == '__main__':
    main()
