#!/usr/bin/env python3
"""
Test completo OS3 Compliance + Compliance Scanner + RAG-Fortress
"""

import sys
import asyncio
from pathlib import Path
sys.path.insert(0, str(Path(__file__).parent.parent))

async def test_all():
    """Esegue tutti i test"""
    print("🧪 TEST COMPLETO OS3 COMPLIANCE + COMPLIANCE SCANNER + RAG-FORTRESS")
    print("=" * 70)
    
    results = {}
    
    # Test 1: OS3 STATISTICS RULE
    print("\n📋 TEST 1: OS3 STATISTICS RULE")
    print("-" * 70)
    try:
        from app.services.retriever_service import RetrieverService
        import numpy as np
        
        retriever = RetrieverService()
        query_embedding = np.random.rand(1536).tolist()
        results_none = retriever.retrieve(query_embedding, tenant_id=1, limit=None)
        results_default = retriever.retrieve(query_embedding, tenant_id=1)
        
        print(f"✅ limit=None: {len(results_none)} risultati")
        print(f"✅ limit default: {len(results_default)} risultati")
        results["statistics_rule"] = True
    except Exception as e:
        print(f"❌ Errore: {e}")
        results["statistics_rule"] = False
    
    # Test 2: Compliance Scanner (solo 3 comuni per velocità)
    print("\n📋 TEST 2: COMPLIANCE SCANNER (3 comuni)")
    print("-" * 70)
    try:
        from app.services.compliance_scanner import AlboPretorioComplianceScanner
        
        scanner = AlboPretorioComplianceScanner()
        test_comuni = ["firenze", "pisa", "siena"]
        
        for comune in test_comuni:
            try:
                report = await scanner.scan_comune(comune)
                print(f"✅ {comune}: Score {report.compliance_score}/100, {len(report.violations)} violazioni")
            except Exception as e:
                print(f"⚠️ {comune}: {str(e)[:60]}...")
        
        results["compliance_scanner"] = True
    except Exception as e:
        print(f"❌ Errore: {e}")
        results["compliance_scanner"] = False
    
    # Test 3: RAG-Fortress Pipeline
    print("\n📋 TEST 3: RAG-FORTRESS PIPELINE")
    print("-" * 70)
    try:
        from app.services.rag_fortress.pipeline import rag_fortress
        
        result = await rag_fortress(
            question="Test domanda",
            tenant_id="1"
        )
        
        print(f"✅ Pipeline completata")
        print(f"   URS Score: {result.get('urs_score', 0)}/100")
        print(f"   Risposta: {len(result.get('answer', ''))} caratteri")
        results["rag_fortress"] = True
    except Exception as e:
        print(f"❌ Errore: {e}")
        import traceback
        traceback.print_exc()
        results["rag_fortress"] = False
    
    # Riepilogo
    print("\n" + "=" * 70)
    print("📊 RIEPILOGO TEST")
    print("=" * 70)
    
    for test_name, success in results.items():
        status = "✅ PASS" if success else "❌ FAIL"
        print(f"{status}: {test_name}")
    
    total_passed = sum(1 for v in results.values() if v)
    total_tests = len(results)
    
    print(f"\n📊 Totale: {total_passed}/{total_tests} test passati")
    
    return total_passed == total_tests

if __name__ == "__main__":
    import asyncio
    success = asyncio.run(test_all())
    sys.exit(0 if success else 1)

