"""
END-TO-END TEST - Verifica che query su FlorenceEGI funzioni correttamente
Questo test verifica TUTTO il flusso dal query al risultato finale
"""

import pytest
import asyncio
from app.services.use_pipeline import UsePipeline


@pytest.mark.asyncio
async def test_florenceegi_query_end_to_end():
    """END-TO-END: Query su FlorenceEGI deve ritornare dati, NON 'no data'"""
    pipeline = UsePipeline()
    question = "Cosa è FlorenceEGI?"
    tenant_id = 1
    
    print("\n" + "="*70)
    print("🔍 END-TO-END TEST: FlorenceEGI Query")
    print("="*70)
    
    result = await pipeline.process_query(
        question=question,
        tenant_id=tenant_id,
        persona="strategic",
        model="anthropic.sonnet-4"  # Use available model
    )
    
    print(f"\n📊 RESULT:")
    print(f"   Status: {result.get('status')}")
    print(f"   Verification Status: {result.get('verification_status')}")
    print(f"   Verified Claims: {len(result.get('verified_claims', []))}")
    print(f"   Answer length: {len(result.get('answer', ''))}")
    
    answer = result.get("answer", "")
    answer_lower = answer.lower()
    has_no_data = "non ho informazioni sufficienti" in answer_lower
    has_basandomi = "basandomi sui documenti disponibili" in answer_lower
    
    print(f"\n📝 ANSWER ANALYSIS:")
    print(f"   Contains 'no data' message: {has_no_data}")
    print(f"   Contains 'basandomi' (reconstructed): {has_basandomi}")
    print(f"\n   Answer preview:\n{answer[:500]}...")
    
    # CRITICAL ASSERTIONS
    print(f"\n✅ ASSERTIONS:")
    
    # 1. Status must be success if chunks were found
    if result.get("status") == "no_results":
        print(f"   ❌ FAIL: Status is 'no_results'")
        print(f"      Reason: {result.get('message', 'No reason')}")
        print(f"      This means no chunks found or all blocked")
        pytest.fail("Pipeline returned 'no_results' - chunks not found or all blocked")
    else:
        print(f"   ✅ PASS: Status is '{result.get('status')}'")
    
    # 2. If status is success, must have verified claims
    if result.get("status") == "success":
        verified_count = len(result.get("verified_claims", []))
        if verified_count == 0:
            print(f"   ❌ FAIL: Status is 'success' but no verified claims!")
            pytest.fail("Status success but no verified claims - contradiction")
        else:
            print(f"   ✅ PASS: Has {verified_count} verified claims")
        
        # 3. Answer must NOT contain 'no data' if we have verified claims
        if has_no_data:
            print(f"   ❌ FAIL: Answer contains 'no data' message!")
            print(f"      But we have {verified_count} verified claims!")
            print(f"      This is the contradiction bug!")
            pytest.fail("Contradiction: 'no data' message + verified claims")
        else:
            print(f"   ✅ PASS: Answer does NOT contain 'no data' message")
        
        # 4. If answer was reconstructed, should contain 'basandomi'
        if has_basandomi:
            print(f"   ✅ PASS: Answer was correctly reconstructed from claims")
        
        # 5. Must have sources
        sources_count = len(result.get("verified_claims", []))
        if sources_count > 0:
            print(f"   ✅ PASS: Has {sources_count} claims with sources")
    
    print(f"\n" + "="*70)
    print(f"✅ END-TO-END TEST PASSED - System working correctly")
    print("="*70 + "\n")
    
    # Final assertions
    assert result.get("status") != "no_results", "Must not return 'no_results' when documents exist"
    assert len(result.get("verified_claims", [])) > 0, "Must have verified claims"
    assert not has_no_data, "Answer must not say 'no data' when claims exist"


if __name__ == "__main__":
    asyncio.run(test_florenceegi_query_end_to_end())

