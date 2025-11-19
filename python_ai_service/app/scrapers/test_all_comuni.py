"""
Test scraping su TUTTI i 13 comuni della ricerca.

Questo script:
1. Testa ogni comune con gli scraper disponibili
2. Conta quanti atti riesce a recuperare
3. Genera report JSON con risultati
4. Identifica quali comuni funzionano e quali no
"""

import asyncio
import json
from datetime import datetime
from typing import Dict, List, Any
import sys
import os

sys.path.append('/home/fabio/dev/NATAN_LOC/python_ai_service')
sys.path.append('/home/fabio/dev/NATAN_LOC')

from app.scrapers import DrupalAlboScraper, TrasparenzaVMScraper
from scripts.scrape_firenze_deliberazioni import FirenzeAttiScraper


# Configurazione comuni da testare
COMUNI_CONFIG = [
    {
        'code': 'firenze',
        'name': 'Firenze',
        'url': 'https://accessoconcertificato.comune.fi.it/AOL/Affissione/ComuneFi/Page',
        'platform': 'custom_api',
        'popolazione': 382258
    },
    {
        'code': 'arezzo',
        'name': 'Arezzo',
        'url': 'https://www.comune.arezzo.it/albo-pretorio',
        'platform': 'drupal',
        'popolazione': 99543
    },
    {
        'code': 'siena',
        'name': 'Siena',
        'url': 'https://www.comune.siena.it/albo-pretorio',
        'platform': 'drupal',
        'popolazione': 53903
    },
    {
        'code': 'lucca',
        'name': 'Lucca',
        'url': 'https://www.comune.lucca.it/albo-pretorio',
        'platform': 'wordpress',
        'popolazione': 89046
    },
    {
        'code': 'livorno',
        'name': 'Livorno',
        'url': 'https://livorno.trasparenza-valutazione-merito.it/web/trasparenza/albo-pretorio',
        'platform': 'trasparenza_vm',
        'popolazione': 157139
    },
    {
        'code': 'grosseto',
        'name': 'Grosseto',
        'url': 'https://grosseto.trasparenza-valutazione-merito.it/web/trasparenza/albo-pretorio',
        'platform': 'trasparenza_vm',
        'popolazione': 82284
    },
    {
        'code': 'pistoia',
        'name': 'Pistoia',
        'url': 'https://pistoia.trasparenza-valutazione-merito.it/web/trasparenza/albo-pretorio',
        'platform': 'trasparenza_vm',
        'popolazione': 90363
    },
    {
        'code': 'carrara',
        'name': 'Carrara',
        'url': 'https://carrara.trasparenza-valutazione-merito.it/web/trasparenza/albo-pretorio',
        'platform': 'trasparenza_vm',
        'popolazione': 62997
    },
    {
        'code': 'prato',
        'name': 'Prato',
        'url': 'https://trasparenza.comune.prato.it/',
        'platform': 'custom',
        'popolazione': 195763
    },
    {
        'code': 'pisa',
        'name': 'Pisa',
        'url': 'https://albopretorio.comune.pisa.it/',
        'platform': 'unknown',
        'popolazione': 90745
    },
    {
        'code': 'bagno_a_ripoli',
        'name': 'Bagno a Ripoli',
        'url': 'https://trasparenza.comune.bagno-a-ripoli.fi.it/web/trasparenza/menu-albo-pretorio',
        'platform': 'custom_js',
        'popolazione': 26035
    },
    {
        'code': 'massa',
        'name': 'Massa',
        'url': None,  # URL non trovato nella ricerca
        'platform': 'unknown',
        'popolazione': 68511
    },
]


async def test_comune(comune: Dict[str, Any]) -> Dict[str, Any]:
    """
    Test scraping per un singolo comune.
    
    Returns:
        Dict con risultati test
    """
    result = {
        'comune': comune['name'],
        'code': comune['code'],
        'url': comune['url'],
        'platform': comune['platform'],
        'popolazione': comune['popolazione'],
        'timestamp': datetime.now().isoformat(),
        'scraper_used': None,
        'detection_ok': False,
        'scraping_ok': False,
        'atti_count': 0,
        'error': None,
        'sample_atti': []
    }
    
    if not comune['url']:
        result['error'] = 'URL not available'
        return result
    
    try:
        # Test Firenze API (caso speciale)
        if comune['code'] == 'firenze':
            result['scraper_used'] = 'FirenzeAPIScraper'
            try:
                firenze_scraper = FirenzeAttiScraper(use_mongodb=False)
                atti = firenze_scraper.search_atti(anno=2025, tipo_atto='DG')
                result['detection_ok'] = True
                result['scraping_ok'] = True
                result['atti_count'] = len(atti)
                
                # Sample primi 3 atti
                for atto in atti[:3]:
                    result['sample_atti'].append({
                        'numero': f"{atto.get('numeroAdozione')}/{atto.get('annoAdozione', 2025)}",
                        'oggetto': atto.get('oggetto', '')[:100],
                        'data': atto.get('dataAdozione', ''),
                        'tipo': atto.get('tipoAtto', 'DG')
                    })
                return result
            except Exception as e:
                result['error'] = f'Firenze API error: {str(e)}'
                return result
        
        # Prova DrupalAlboScraper
        if comune['platform'] in ['drupal', 'custom', 'custom_api', 'unknown']:
            scraper = DrupalAlboScraper(comune['code'], 'test_tenant')
            result['scraper_used'] = 'DrupalAlboScraper'
            
            try:
                is_platform = await scraper.detect_platform(comune['url'])
                result['detection_ok'] = is_platform
                
                if is_platform:
                    atti = await scraper.scrape_page(comune['url'], 1)
                    result['scraping_ok'] = True
                    result['atti_count'] = len(atti)
                    
                    # Sample primi 3 atti
                    for atto in atti[:3]:
                        result['sample_atti'].append({
                            'numero': atto.numero,
                            'oggetto': atto.oggetto[:100],
                            'data': atto.data_pubblicazione.isoformat(),
                            'tipo': atto.tipo_atto
                        })
            finally:
                await scraper.session.aclose()
        
        # Se Drupal non funziona, prova TrasparenzaVMScraper
        if not result['detection_ok'] and comune['platform'] == 'trasparenza_vm':
            scraper = TrasparenzaVMScraper(comune['code'], 'test_tenant', {'headless': True, 'timeout': 15000})
            result['scraper_used'] = 'TrasparenzaVMScraper'
            
            try:
                is_platform = await scraper.detect_platform(comune['url'])
                result['detection_ok'] = is_platform
                
                if is_platform:
                    # Note: TrasparenzaVM sites are protected, likely 0 atti
                    # But we test detection
                    pass
            except Exception as e:
                result['error'] = f'TrasparenzaVM error: {str(e)}'
    
    except Exception as e:
        result['error'] = str(e)
    
    return result


async def test_all_comuni():
    """Test tutti i comuni e genera report."""
    
    print("=" * 80)
    print("🧪 TEST SCRAPING - TUTTI I COMUNI TOSCANI")
    print("=" * 80)
    print(f"\nTestando {len(COMUNI_CONFIG)} comuni...\n")
    
    results = []
    
    for i, comune in enumerate(COMUNI_CONFIG, 1):
        print(f"[{i}/{len(COMUNI_CONFIG)}] Testing {comune['name']}... ", end='', flush=True)
        
        result = await test_comune(comune)
        results.append(result)
        
        # Status icon
        if result['scraping_ok'] and result['atti_count'] > 0:
            status = f"✅ {result['atti_count']} atti"
        elif result['detection_ok']:
            status = f"⚠️ Detected but 0 atti"
        elif result['error']:
            status = f"❌ {result['error'][:30]}"
        else:
            status = "❌ Not compatible"
        
        print(status)
    
    # Summary
    print("\n" + "=" * 80)
    print("📊 SUMMARY")
    print("=" * 80)
    
    successful = [r for r in results if r['scraping_ok'] and r['atti_count'] > 0]
    detected = [r for r in results if r['detection_ok']]
    failed = [r for r in results if not r['detection_ok']]
    
    print(f"\n✅ Scraping funzionante: {len(successful)}/{len(COMUNI_CONFIG)} comuni")
    for r in successful:
        print(f"   - {r['comune']}: {r['atti_count']} atti ({r['scraper_used']})")
    
    print(f"\n⚠️ Detection OK ma 0 atti: {len(detected) - len(successful)} comuni")
    for r in detected:
        if r not in successful:
            print(f"   - {r['comune']}: {r['platform']} ({r.get('error', 'likely protected')})")
    
    print(f"\n❌ Non compatibili: {len(failed)} comuni")
    for r in failed:
        print(f"   - {r['comune']}: {r['platform']}")
    
    # Calculate coverage
    total_popolazione = sum(c['popolazione'] for c in COMUNI_CONFIG)
    successful_popolazione = sum(r['popolazione'] for r in successful)
    coverage_pct = (successful_popolazione / total_popolazione) * 100
    
    print(f"\n📈 Coverage popolazione: {coverage_pct:.1f}%")
    print(f"   ({successful_popolazione:,} / {total_popolazione:,} abitanti)")
    
    # Save report
    report_file = '/home/fabio/dev/NATAN_LOC/python_ai_service/test_results.json'
    with open(report_file, 'w', encoding='utf-8') as f:
        json.dump({
            'timestamp': datetime.now().isoformat(),
            'total_comuni': len(COMUNI_CONFIG),
            'successful': len(successful),
            'detected': len(detected),
            'failed': len(failed),
            'coverage_pct': coverage_pct,
            'results': results
        }, f, indent=2, ensure_ascii=False)
    
    print(f"\n💾 Report salvato: {report_file}")
    print("=" * 80)
    
    return results


if __name__ == '__main__':
    asyncio.run(test_all_comuni())
