#!/usr/bin/env python3
"""
Test Compliance Scanner su comuni reali con URL corretti
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

async def test_real_comuni():
    """Test su comuni reali"""
    scanner = AlboPretorioComplianceScanner()
    
    print("🔍 TEST COMPLIANCE SCANNER - COMUNI TOSCANI REALI")
    print("=" * 70)
    
    results = []
    
    for comune_slug, expected_url in COMUNI_REALI.items():
        print(f"\n📋 {comune_slug.upper()}")
        print(f"   URL atteso: {expected_url}")
        
        try:
            report = await scanner.scan_comune(comune_slug)
            
            success = report.compliance_score > 0 or len(report.violations) > 0
            
            results.append({
                "comune": comune_slug,
                "url_found": report.albo_url,
                "score": report.compliance_score,
                "violations": len(report.violations),
                "success": success
            })
            
            if success:
                print(f"   ✅ URL trovato: {report.albo_url}")
                print(f"   📊 Score: {report.compliance_score}/100")
                print(f"   ⚠️ Violazioni: {len(report.violations)}")
            else:
                print(f"   ⚠️ Nessun contenuto recuperato")
                
        except Exception as e:
            print(f"   ❌ Errore: {str(e)[:80]}")
            results.append({
                "comune": comune_slug,
                "success": False,
                "error": str(e)
            })
    
    # Riepilogo
    print(f"\n{'='*70}")
    print("📊 RIEPILOGO")
    print(f"{'='*70}")
    
    successful = [r for r in results if r.get("success")]
    failed = [r for r in results if not r.get("success")]
    
    print(f"\n✅ Riuscite: {len(successful)}/{len(COMUNI_REALI)}")
    print(f"❌ Fallite: {len(failed)}/{len(COMUNI_REALI)}")
    
    if successful:
        print(f"\n📊 Score medio: {sum(r['score'] for r in successful) / len(successful):.1f}/100")
        print(f"\n🏆 Comuni con contenuto recuperato:")
        for r in successful:
            print(f"   - {r['comune']}: {r['url_found']}")
    
    return len(successful) > 0

if __name__ == "__main__":
    success = asyncio.run(test_real_comuni())
    sys.exit(0 if success else 1)

