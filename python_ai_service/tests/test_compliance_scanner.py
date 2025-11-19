#!/usr/bin/env python3
"""
Test Compliance Scanner - Scraping comuni toscani
"""

import sys
import asyncio
from pathlib import Path
sys.path.insert(0, str(Path(__file__).parent.parent))

from app.services.compliance_scanner import AlboPretorioComplianceScanner

# Lista comuni toscani da testare
COMUNI_TOSCANI = [
    "firenze",
    "pisa",
    "siena",
    "arezzo",
    "livorno",
    "prato",
    "pistoia",
    "lucca",
    "grosseto",
    "massa"
]

async def test_compliance_scanner():
    """Test compliance scanner su comuni toscani"""
    scanner = AlboPretorioComplianceScanner()
    
    results = []
    
    for comune in COMUNI_TOSCANI:
        print(f"\n{'='*60}")
        print(f"🔍 Scansione: {comune.upper()}")
        print(f"{'='*60}")
        
        try:
            report = await scanner.scan_comune(comune)
            
            results.append({
                "comune": comune,
                "score": report.compliance_score,
                "violations": len(report.violations),
                "url": report.albo_url,
                "success": True
            })
            
            print(f"✅ Score: {report.compliance_score}/100")
            print(f"📋 Violazioni: {len(report.violations)}")
            print(f"🔗 URL: {report.albo_url}")
            
            if report.violations:
                print(f"\n⚠️ Violazioni rilevate:")
                for v in report.violations[:3]:  # Mostra prime 3
                    print(f"  - {v.norm} {v.article}: {v.description[:60]}...")
            
        except Exception as e:
            print(f"❌ Errore: {e}")
            results.append({
                "comune": comune,
                "success": False,
                "error": str(e)
            })
    
    # Riepilogo
    print(f"\n{'='*60}")
    print(f"📊 RIEPILOGO SCANSIONI")
    print(f"{'='*60}")
    
    successful = [r for r in results if r.get("success")]
    failed = [r for r in results if not r.get("success")]
    
    print(f"\n✅ Riuscite: {len(successful)}/{len(COMUNI_TOSCANI)}")
    print(f"❌ Fallite: {len(failed)}/{len(COMUNI_TOSCANI)}")
    
    if successful:
        avg_score = sum(r["score"] for r in successful) / len(successful)
        print(f"\n📊 Score medio: {avg_score:.1f}/100")
        
        print(f"\n🏆 Top 3 comuni (score più alto):")
        sorted_successful = sorted(successful, key=lambda x: x["score"], reverse=True)
        for i, r in enumerate(sorted_successful[:3], 1):
            print(f"  {i}. {r['comune']}: {r['score']}/100 ({r['violations']} violazioni)")
    
    if failed:
        print(f"\n❌ Comuni con errori:")
        for r in failed:
            print(f"  - {r['comune']}: {r.get('error', 'Unknown error')}")
    
    return len(successful) > 0

if __name__ == "__main__":
    success = asyncio.run(test_compliance_scanner())
    sys.exit(0 if success else 1)

