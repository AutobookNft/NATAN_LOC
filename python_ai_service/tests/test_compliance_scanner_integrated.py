#!/usr/bin/env python3
"""
Test Compliance Scanner integrato con sistema scraping esistente
Verifica che estragga atti reali usando ScraperFactory + TrivellaBrutale
"""

import asyncio
import sys
from pathlib import Path

# Aggiungi path per import
sys.path.insert(0, str(Path(__file__).parent.parent))

from app.services.compliance_scanner import AlboPretorioComplianceScanner
import logging

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


async def test_compliance_scanner_integrated():
    """Test Compliance Scanner con sistema scraping integrato"""
    
    scanner = AlboPretorioComplianceScanner()
    
    # Comuni da testare (mix di comuni grandi e piccoli)
    comuni_test = [
        "firenze",  # Dovrebbe funzionare con API
        "empoli",   # Drupal
        "pisa",     # TrasparenzaVM
        "prato",    # Drupal
    ]
    
    print("\n" + "="*80)
    print("🧪 TEST COMPLIANCE SCANNER INTEGRATO")
    print("="*80)
    print(f"Comuni da testare: {len(comuni_test)}")
    print(f"Comuni: {', '.join(comuni_test)}")
    print("="*80 + "\n")
    
    risultati = []
    
    for comune_slug in comuni_test:
        print(f"\n{'='*80}")
        print(f"🏛️  Test comune: {comune_slug.upper()}")
        print(f"{'='*80}")
        
        try:
            report = await scanner.scan_comune(comune_slug, tenant_id=1)
            
            atti_estratti = report.metadata.get('atti_estratti', 0) if report.metadata else 0
            metodo = report.metadata.get('metodo', 'Unknown') if report.metadata else 'Unknown'
            
            risultato = {
                'comune': comune_slug,
                'score': report.compliance_score,
                'atti_estratti': atti_estratti,
                'metodo': metodo,
                'violazioni': len(report.violations),
                'albo_url': report.albo_url,
                'status': 'SUCCESS' if atti_estratti > 0 else 'NO_ATTI'
            }
            
            risultati.append(risultato)
            
            print(f"\n✅ RISULTATO:")
            print(f"  Score compliance: {report.compliance_score}/100")
            print(f"  Atti estratti: {atti_estratti}")
            print(f"  Metodo usato: {metodo}")
            print(f"  Violazioni: {len(report.violations)}")
            print(f"  URL: {report.albo_url}")
            
            if atti_estratti > 0:
                print(f"  ✅ SUCCESSO: {atti_estratti} atti estratti con {metodo}")
            else:
                print(f"  ⚠️  NESSUN ATTO: Metodo {metodo} non ha estratto atti")
            
            # Mostra prime violazioni
            if report.violations:
                print(f"\n  Violazioni trovate:")
                for v in report.violations[:3]:
                    print(f"    - {v.norm} {v.article}: {v.description[:60]}...")
        
        except Exception as e:
            logger.exception(f"Errore testando {comune_slug}")
            risultati.append({
                'comune': comune_slug,
                'status': 'ERROR',
                'error': str(e)
            })
            print(f"  ❌ ERRORE: {e}")
    
    # Riepilogo finale
    print("\n" + "="*80)
    print("📊 RIEPILOGO FINALE")
    print("="*80)
    
    successi = sum(1 for r in risultati if r.get('atti_estratti', 0) > 0)
    totali = len(risultati)
    atti_totali = sum(r.get('atti_estratti', 0) for r in risultati)
    
    print(f"Comuni testati: {totali}")
    print(f"✅ Comuni con atti estratti: {successi}/{totali}")
    print(f"📄 Atti totali estratti: {atti_totali}")
    
    if totali > 0:
        coverage = (successi / totali) * 100
        print(f"📈 Coverage: {coverage:.1f}%")
    
    print("\nDettaglio per comune:")
    for r in risultati:
        status_icon = "✅" if r.get('atti_estratti', 0) > 0 else "❌"
        print(f"  {status_icon} {r['comune']:15} | Atti: {r.get('atti_estratti', 0):3} | Metodo: {r.get('metodo', 'N/A')[:30]}")
    
    print("\n" + "="*80)
    
    # Verifica che almeno un comune abbia estratto atti
    assert successi > 0, f"Nessun comune ha estratto atti! Test fallito."
    assert atti_totali > 0, f"Totale atti estratti è 0! Test fallito."
    
    print("✅ TEST COMPLETATO CON SUCCESSO")
    print("="*80)
    
    return risultati


if __name__ == "__main__":
    risultati = asyncio.run(test_compliance_scanner_integrated())
    print(f"\n🎯 Test completato: {len(risultati)} comuni testati")

