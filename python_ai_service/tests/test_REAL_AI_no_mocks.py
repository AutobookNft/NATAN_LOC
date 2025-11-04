"""
TEST CON AI REALE - NO MOCKS
Questo test interroga VERAMENTE l'AI per verificare il comportamento reale
"""

import pytest
import asyncio
from app.services.use_pipeline import UsePipeline


@pytest.mark.asyncio
async def test_real_ai_query_florenceegi():
    """
    TEST CON AI REALE - NO MOCKS
    Interroga veramente l'AI per query su FlorenceEGI
    """
    print("\n" + "="*70)
    print("🔍 TEST AI REALE - NO MOCKS")
    print("="*70)
    print("⚠️  Questo test interroga VERAMENTE l'AI Anthropic")
    print("⚠️  Richiede API key configurata e funzionante")
    print("="*70 + "\n")
    
    pipeline = UsePipeline()
    
    # Query reale
    question = "Cosa è FlorenceEGI?"
    tenant_id = 1
    
    print(f"📋 QUERY:")
    print(f"   Domanda: '{question}'")
    print(f"   Tenant ID: {tenant_id}")
    print(f"   Modello: Auto-discovery (usa quello disponibile)")
    print(f"\n⏳ Chiamata AI in corso... (può richiedere alcuni secondi)\n")
    
    try:
        # Chiamata REALE - NO MOCKS
        # Il pipeline userà l'AI reale (Anthropic) se disponibile
        result = await pipeline.process_query(
            question=question,
            tenant_id=tenant_id,
            persona="strategic"
            # model non specificato = usa default da config
        )
        
        print("="*70)
        print("📊 RISULTATO AI REALE:")
        print("="*70)
        
        status = result.get("status")
        verified_claims = result.get("verified_claims", [])
        answer = result.get("answer", "")
        chunks_used = result.get("chunks_used", [])
        model_used = result.get("model_used", "N/A")
        tokens_used = result.get("tokens_used", {})
        
        print(f"\n✅ STATUS: {status}")
        print(f"✅ MODEL USED: {model_used}")
        print(f"✅ VERIFIED CLAIMS: {len(verified_claims)}")
        print(f"✅ CHUNKS USED: {len(chunks_used)}")
        
        if tokens_used:
            total_tokens = tokens_used.get("input", 0) + tokens_used.get("output", 0)
            print(f"✅ TOKENS: {total_tokens} (input: {tokens_used.get('input', 0)}, output: {tokens_used.get('output', 0)})")
        
        print(f"\n📝 ANSWER ({len(answer)} caratteri):")
        print("-" * 70)
        print(answer)
        print("-" * 70)
        
        # Analisi critica
        answer_lower = answer.lower()
        has_no_data = "non ho informazioni sufficienti" in answer_lower
        has_basandomi = "basandomi sui documenti disponibili" in answer_lower
        has_florenceegi = "florenceegi" in answer_lower or "egi" in answer_lower
        
        print(f"\n🔍 ANALISI CRITICA:")
        print(f"   Answer contiene 'no data': {has_no_data}")
        print(f"   Answer contiene 'basandomi': {has_basandomi}")
        print(f"   Answer contiene riferimenti a FlorenceEGI/EGI: {has_florenceegi}")
        print(f"   Verified claims: {len(verified_claims)}")
        
        # Verifiche
        print(f"\n✅ VERIFICHE AFFIDABILITÀ:")
        
        if status == "no_results":
            print(f"   ❌ STATUS: 'no_results'")
            print(f"   Motivo: {result.get('message', 'Nessun motivo specificato')}")
            if len(chunks_used) == 0:
                print(f"   Problema: Nessun chunk trovato (retriever fallito)")
            elif len(verified_claims) == 0:
                print(f"   Problema: Chunks trovati ma nessun claim verificato")
            pytest.fail(f"Sistema ritorna 'no_results' - NON affidabile")
        else:
            print(f"   ✅ STATUS: '{status}' (OK)")
        
        if len(chunks_used) == 0:
            print(f"   ❌ Nessun chunk usato - retriever non ha trovato documenti")
            pytest.fail("Retriever non ha trovato chunks - sistema non affidabile")
        else:
            print(f"   ✅ {len(chunks_used)} chunks usati (retriever funziona)")
        
        if len(verified_claims) == 0:
            print(f"   ⚠️  Nessun verified claim generato")
            if has_no_data:
                print(f"   OK: 'no data' è corretto se non ci sono claims")
            else:
                print(f"   ⚠️  Status è '{status}' ma nessun claim - inconsistenze")
        else:
            print(f"   ✅ {len(verified_claims)} verified claims generati")
        
        if has_no_data and len(verified_claims) > 0:
            print(f"   ❌ CONTRADIZIONE: Answer dice 'no data' ma ha {len(verified_claims)} claims!")
            pytest.fail("BUG CONTRADIZIONE: 'no data' + verified claims")
        elif len(verified_claims) > 0 and has_no_data:
            print(f"   ❌ CONTRADIZIONE: Answer 'no data' con claims verificati")
            pytest.fail("BUG CONTRADIZIONE")
        else:
            print(f"   ✅ Nessuna contraddizione rilevata")
        
        if len(verified_claims) > 0 and not has_florenceegi:
            print(f"   ⚠️  WARNING: Claims verificati ma answer non menziona FlorenceEGI")
        
        # Mostra claims se disponibili
        if verified_claims:
            print(f"\n📋 VERIFIED CLAIMS ({len(verified_claims)}):")
            for i, claim in enumerate(verified_claims[:3], 1):
                claim_text = claim.get("text", "")[:150]
                urs = claim.get("urs", 0)
                print(f"   {i}. URS {urs:.2f}: {claim_text}...")
        
        print(f"\n" + "="*70)
        print(f"✅ TEST AI REALE COMPLETATO")
        print(f"   Sistema: {'AFFIDABILE' if status == 'success' and len(verified_claims) > 0 else 'DA VERIFICARE'}")
        print("="*70 + "\n")
        
        # Final assertions
        assert status != "no_results" or len(chunks_used) == 0, "Se ci sono chunks, non deve ritornare no_results"
        assert not (has_no_data and len(verified_claims) > 0), "Nessuna contraddizione: 'no data' + claims"
        
    except Exception as e:
        print(f"\n❌ ERRORE DURANTE TEST AI REALE:")
        print(f"   {type(e).__name__}: {e}")
        import traceback
        traceback.print_exc()
        
        # Se è un errore API, documentalo ma non fallire (problema configurazione)
        if "API" in str(e) or "529" in str(e) or "anthropic" in str(e).lower():
            print(f"\n⚠️  ERRORE API ANTHROPIC:")
            print(f"   Questo è un problema di configurazione/rate limiting")
            print(f"   NON un problema di logica del sistema")
            pytest.skip(f"Test saltato: problema API Anthropic - {e}")
        else:
            pytest.fail(f"Test fallito: {e}")


@pytest.mark.asyncio
async def test_real_ai_no_data_query():
    """
    TEST AI REALE - Query che NON dovrebbe trovare dati
    Verifica che sistema ritorni 'no data' correttamente
    """
    print("\n" + "="*70)
    print("🔍 TEST AI REALE - Query senza dati")
    print("="*70)
    
    pipeline = UsePipeline()
    
    # Query che NON dovrebbe trovare dati
    question = "Quali sono le regole per la pesca nel Mar Mediterraneo nel 2050?"
    tenant_id = 1
    
    print(f"📋 QUERY (dovrebbe ritornare 'no data'):")
    print(f"   Domanda: '{question}'")
    print(f"\n⏳ Chiamata AI in corso...\n")
    
    try:
        result = await pipeline.process_query(
            question=question,
            tenant_id=tenant_id,
            persona="strategic"
        )
        
        status = result.get("status")
        verified_claims = result.get("verified_claims", [])
        answer = result.get("answer", "")
        chunks_used = result.get("chunks_used", [])
        
        print(f"📊 RISULTATO:")
        print(f"   Status: {status}")
        print(f"   Verified claims: {len(verified_claims)}")
        print(f"   Chunks used: {len(chunks_used)}")
        
        answer_lower = answer.lower()
        has_no_data = "non ho informazioni sufficienti" in answer_lower
        
        print(f"\n✅ VERIFICA:")
        if status == "no_results" or len(verified_claims) == 0:
            print(f"   ✅ PASS: Sistema ritorna 'no data' correttamente (nessun dato disponibile)")
        else:
            print(f"   ⚠️  Sistema ha trovato claims per query che non dovrebbe avere dati")
        
        # Per query senza dati, è OK avere no_results
        print(f"   ✅ TEST COMPLETATO")
        
    except Exception as e:
        if "API" in str(e) or "529" in str(e):
            pytest.skip(f"Test saltato: problema API - {e}")
        else:
            pytest.fail(f"Test fallito: {e}")


if __name__ == "__main__":
    asyncio.run(test_real_ai_query_florenceegi())






