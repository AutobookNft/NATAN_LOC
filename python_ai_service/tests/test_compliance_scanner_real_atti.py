#!/usr/bin/env python3
"""
Test Compliance Scanner REALE - Conta atti estratti
Questo è il vero test: quanti atti riesci a leggere?
"""

import sys
import asyncio
from pathlib import Path
sys.path.insert(0, str(Path(__file__).parent.parent))

from app.services.compliance_scanner import AlboPretorioComplianceScanner

# URL reali Albi Pretori comuni toscani
COMUNI_REALI = {
    "firenze": "https://www.comune.firenze.it/albo-pretorio",
    "pisa": "https://www.comune.pisa.it/albo-pretorio",
    "siena": "https://www.comune.siena.it/albo-pretorio",
    "arezzo": "https://www.comune.arezzo.it/albo-pretorio",
    "livorno": "https://www.comune.livorno.it/albo-pretorio",
    "prato": "https://www.comune.prato.it/albo-pretorio",
    "pistoia": "https://www.comune.pistoia.it/albo-pretorio",
    "lucca": "https://www.comune.lucca.it/albo-pretorio",
    "grosseto": "https://www.comune.grosseto.it/albo-pretorio",
    "massa": "https://www.comune.massa.it/albo-pretorio",
}

async def test_real_atti_extraction():
    """Test REALE: quanti atti riesci a estrarre?"""
    scanner = AlboPretorioComplianceScanner()
    
    print("🔍 TEST REALE COMPLIANCE SCANNER - ESTRAZIONE ATTI")
    print("=" * 70)
    print("Questo test verifica QUANTI ATTI riesci realmente a leggere\n")
    
    results = []
    
    for comune_slug, expected_url in COMUNI_REALI.items():
        print(f"📋 {comune_slug.upper()}")
        print(f"   URL: {expected_url}")
        
        try:
            report = await scanner.scan_comune(comune_slug)
            
            # Conta atti estratti (il vero test!)
            atti_count = report.metadata.get("atti_estratti", 0) if hasattr(report, 'metadata') and report.metadata else 0
            
            results.append({
                "comune": comune_slug,
                "url": report.albo_url,
                "score": report.compliance_score,
                "violations": len(report.violations),
                "atti_estratti": atti_count,
                "success": atti_count > 0 or report.compliance_score > 0
            })
            
            if atti_count > 0:
                print(f"   ✅ ATTI ESTRATTI: {atti_count} atti")
                # Mostra primi 3 atti
                atti_list = report.metadata.get("atti_list", [])[:3] if hasattr(report, 'metadata') and report.metadata else []
                for i, atto in enumerate(atti_list, 1):
                    print(f"      {i}. {atto.get('tipo', 'N/A').upper()} n. {atto.get('numero', 'N/A')} - {atto.get('oggetto', '')[:50]}...")
            else:
                print(f"   ❌ NESSUN ATTO ESTRATTO (0 atti)")
            
            print(f"   📊 Score: {report.compliance_score}/100")
            print(f"   ⚠️ Violazioni: {len(report.violations)}")
            print()
                
        except Exception as e:
            print(f"   ❌ Errore: {str(e)[:80]}")
            results.append({
                "comune": comune_slug,
                "success": False,
                "error": str(e),
                "atti_estratti": 0
            })
            print()
    
    # Riepilogo FINALE
    print(f"{'='*70}")
    print("📊 RIEPILOGO FINALE - ESTRAZIONE ATTI")
    print(f"{'='*70}\n")
    
    successful = [r for r in results if r.get("atti_estratti", 0) > 0]
    failed = [r for r in results if r.get("atti_estratti", 0) == 0]
    
    total_atti = sum(r.get("atti_estratti", 0) for r in results)
    
    print(f"✅ Comuni con atti estratti: {len(successful)}/{len(COMUNI_REALI)}")
    print(f"❌ Comuni senza atti: {len(failed)}/{len(COMUNI_REALI)}")
    print(f"📊 TOTALE ATTI ESTRATTI: {total_atti} atti\n")
    
    if successful:
        print(f"🏆 Comuni con atti estratti:")
        for r in sorted(successful, key=lambda x: x["atti_estratti"], reverse=True):
            print(f"   - {r['comune']}: {r['atti_estratti']} atti (Score: {r['score']}/100)")
    
    if failed:
        print(f"\n❌ Comuni senza atti estratti:")
        for r in failed:
            print(f"   - {r['comune']}: 0 atti (Score: {r.get('score', 0)}/100)")
    
    print(f"\n{'='*70}")
    print(f"🎯 RISULTATO FINALE: {len(successful)}/{len(COMUNI_REALI)} comuni con atti estratti")
    print(f"📊 Totale atti: {total_atti}")
    print(f"{'='*70}")
    
    return len(successful) > 0

if __name__ == "__main__":
    success = asyncio.run(test_real_atti_extraction())
    sys.exit(0 if success else 1)

