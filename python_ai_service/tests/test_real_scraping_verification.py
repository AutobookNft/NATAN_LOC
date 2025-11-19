#!/usr/bin/env python3
"""
Test REALE - Verifica cosa riesci realmente a leggere
Usa gli scraper esistenti se disponibili
"""

import sys
import asyncio
from pathlib import Path
sys.path.insert(0, str(Path(__file__).parent.parent))

import httpx
from bs4 import BeautifulSoup
import re

# URL reali Albi Pretori (alcuni potrebbero essere diversi)
ALBI_URLS = {
    "firenze": [
        "https://accessoconcertificato.comune.fi.it/AOL/Affissione/ComuneFi/Page",  # URL reale scraper
        "https://www.comune.firenze.it/albo-pretorio"
    ],
    "pisa": [
        "https://www.comune.pisa.it/albo-pretorio",
        "https://albopretorio.comune.pisa.it"
    ],
    "siena": [
        "https://www.comune.siena.it/albo-pretorio",
        "https://albopretorio.comune.siena.it"
    ],
    "arezzo": [
        "https://www.comune.arezzo.it/albo-pretorio"
    ],
    "livorno": [
        "https://www.comune.livorno.it/albo-pretorio"
    ],
}

async def test_real_scraping():
    """Test REALE: cosa riesci a leggere?"""
    print("🔍 TEST REALE SCRAPING - VERIFICA CONTENUTI")
    print("=" * 70)
    
    results = {}
    
    for comune, urls in ALBI_URLS.items():
        print(f"\n📋 {comune.upper()}")
        atti_found = 0
        
        for url in urls:
            print(f"   Testing: {url}")
            try:
                async with httpx.AsyncClient(timeout=15.0, follow_redirects=True) as client:
                    response = await client.get(url)
                    
                    if response.status_code == 200:
                        soup = BeautifulSoup(response.text, 'html.parser')
                        text = soup.get_text()
                        
                        # Cerca pattern atti nel testo
                        patterns = [
                            r'(delibera|determina|ordinanza)\s+n\.?\s*(\d+)[/\-](\d{4})',
                            r'n\.?\s*(\d+)[/\-](\d{4})',  # Solo numero/anno
                            r'(\d{1,2})[/\-](\d{1,2})[/\-](\d{4})',  # Date
                        ]
                        
                        for pattern in patterns:
                            matches = re.findall(pattern, text, re.IGNORECASE)
                            if matches:
                                atti_found += len(matches)
                                print(f"      ✅ Pattern trovati: {len(matches)}")
                                if matches:
                                    print(f"      Esempio: {str(matches[0])[:60]}")
                                break
                        
                        # Cerca tabelle
                        tables = soup.find_all('table')
                        if tables:
                            rows = sum(len(t.find_all('tr')) for t in tables)
                            print(f"      Tabelle: {len(tables)}, Righe totali: {rows}")
                        
                        # Cerca link PDF
                        pdf_links = soup.find_all('a', href=re.compile(r'\.pdf', re.I))
                        if pdf_links:
                            print(f"      Link PDF: {len(pdf_links)}")
                        
                    else:
                        print(f"      Status: {response.status_code}")
                        
            except Exception as e:
                print(f"      Errore: {str(e)[:60]}")
        
        results[comune] = atti_found
        print(f"   📊 Totale pattern trovati: {atti_found}")
    
    # Riepilogo
    print(f"\n{'='*70}")
    print("📊 RIEPILOGO FINALE")
    print(f"{'='*70}\n")
    
    for comune, count in sorted(results.items(), key=lambda x: x[1], reverse=True):
        status = "✅" if count > 0 else "❌"
        print(f"{status} {comune}: {count} pattern trovati")
    
    total = sum(results.values())
    print(f"\n🎯 TOTALE PATTERN TROVATI: {total}")
    print(f"📊 Comuni con contenuto: {sum(1 for v in results.values() if v > 0)}/{len(results)}")
    
    return total > 0

if __name__ == "__main__":
    success = asyncio.run(test_real_scraping())
    sys.exit(0 if success else 1)

