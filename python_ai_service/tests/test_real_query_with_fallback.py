"""
REAL QUERY TEST with Model Fallback
Test con query reale usando modello disponibile
"""

import pytest
import asyncio
from app.services.use_pipeline import UsePipeline
from app.services.ai_router import AIRouter


@pytest.mark.asyncio
async def test_real_florenceegi_query_with_available_model():
    """TEST REALE: Query su FlorenceEGI usando modello disponibile"""
    pipeline = UsePipeline()
    
    print("\n" + "="*70)
    print("🔍 REAL QUERY TEST - FlorenceEGI con Modello Disponibile")
    print("="*70)
    
    # Scopri modello disponibile
    ai_router = AIRouter()
    context = {"tenant_id": 1, "task_class": "USE", "persona": "strategic"}
    
    try:
        adapter = ai_router.get_chat_adapter(context)
        # Prova a scoprire modello
        if hasattr(adapter, '_discover_model'):
            try:
                available_model = await adapter._discover_model()
                print(f"\n📊 Modello disponibile: {available_model}")
            except Exception as e:
                print(f"\n⚠️  Errore discovery modello: {e}")
                # Usa fallback
                available_model = "anthropic.sonnet-4"
        else:
            available_model = "anthropic.sonnet-4"
    except Exception as e:
        print(f"\n⚠️  Errore creazione adapter: {e}")
        available_model = "anthropic.sonnet-4"
    
    print(f"   Usando modello: {available_model}")
    
    # Esegui query
    try:
        result = await pipeline.process_query(
            question="Cosa è FlorenceEGI?",
            tenant_id=1,
            persona="strategic",
            model=available_model
        )
        
        print(f"\n📊 RISULTATO:")
        print(f"   Status: {result.get('status')}")
        print(f"   Verification Status: {result.get('verification_status')}")
        print(f"   Verified Claims: {len(result.get('verified_claims', []))}")
        
        answer = result.get("answer", "")
        verified_claims = result.get("verified_claims", [])
        
        has_no_data = "non ho informazioni sufficienti" in answer.lower()
        has_basandomi = "basandomi sui documenti disponibili" in answer.lower()
        
        print(f"\n📝 ANALISI ANSWER:")
        print(f"   Answer length: {len(answer)}")
        print(f"   Contains 'no data': {has_no_data}")
        print(f"   Contains 'basandomi': {has_basandomi}")
        print(f"   Verified claims: {len(verified_claims)}")
        print(f"\n   Answer preview:\n{answer[:500]}...")
        
        # ASSERTIONS CRITICHE
        print(f"\n✅ VERIFICHE AFFIDABILITÀ:")
        
        if result.get("status") == "no_results":
            print(f"   ❌ FAIL: Status è 'no_results'")
            print(f"   Motivo: {result.get('message', 'Nessun motivo')}")
            pytest.fail(f"Query 'Cosa è FlorenceEGI?' ha ritornato no_results - sistema non affidabile")
        else:
            print(f"   ✅ PASS: Status è '{result.get('status')}'")
        
        if len(verified_claims) == 0:
            print(f"   ❌ FAIL: Nessun verified claim")
            pytest.fail("Nessun verified claim generato - sistema non affidabile")
        else:
            print(f"   ✅ PASS: {len(verified_claims)} verified claims")
        
        if has_no_data and len(verified_claims) > 0:
            print(f"   ❌ CONTRADICTION: Answer dice 'no data' ma ha {len(verified_claims)} claims!")
            pytest.fail("CONTRADIZIONE: 'no data' + verified claims - BUG!")
        else:
            print(f"   ✅ PASS: Nessuna contraddizione")
        
        if len(verified_claims) > 0 and not has_basandomi and has_no_data:
            print(f"   ⚠️  WARNING: Answer non è stata ricostruita dai claims")
            print(f"   Il fix potrebbe non essere applicato")
        
        print(f"\n" + "="*70)
        print(f"✅ TEST COMPLETATO - Sistema AFFIDABILE")
        print("="*70 + "\n")
        
    except Exception as e:
        print(f"\n❌ ERRORE: {e}")
        import traceback
        traceback.print_exc()
        pytest.fail(f"Test fallito con errore: {e}")


if __name__ == "__main__":
    asyncio.run(test_real_florenceegi_query_with_available_model())






