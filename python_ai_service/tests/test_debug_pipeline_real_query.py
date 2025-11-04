"""
Debug Test - Find WHERE the pipeline breaks with real FlorenceEGI query
"""

import pytest
import asyncio
from app.services.use_pipeline import UsePipeline


@pytest.mark.asyncio
async def test_debug_florenceegi_query_step_by_step():
    """Debug test to find WHERE pipeline breaks with real query"""
    pipeline = UsePipeline()
    question = "Cosa è FlorenceEGI?"
    tenant_id = 1
    
    print("\n" + "="*70)
    print("🔍 DEBUG TEST - Step by step pipeline execution")
    print("="*70)
    
    # STEP 1: Classify
    print("\n📋 STEP 1: Classification")
    classification = pipeline.classifier.classify(question, tenant_id)
    print(f"   Intent: {classification.get('intent')}")
    print(f"   Confidence: {classification.get('confidence')}")
    
    # STEP 2: Route
    print("\n📋 STEP 2: Routing")
    from app.services.execution_router import ExecutionRouter
    router = ExecutionRouter()
    routing = router.route(
        intent=classification["intent"],
        confidence=classification["confidence"],
        question=question,
        tenant_id=tenant_id,
        constraints=classification.get("constraints", {})
    )
    print(f"   Action: {routing.get('action')}")
    
    if routing["action"] != "rag_strict":
        print(f"   ❌ STOPPED: Action is not rag_strict (got: {routing['action']})")
        return
    
    # STEP 3: Generate embedding
    print("\n📋 STEP 3: Generate query embedding")
    from app.services.ai_router import AIRouter
    ai_router = AIRouter()
    context = {"tenant_id": tenant_id, "task_class": "RAG"}
    adapter = ai_router.get_embedding_adapter(context)
    embed_result = await adapter.embed(question)
    query_embedding = embed_result["embedding"]
    print(f"   Embedding length: {len(query_embedding)}")
    
    # STEP 4: Retrieve chunks
    print("\n📋 STEP 4: Retrieve chunks")
    chunks = pipeline.retriever.retrieve(
        query_embedding=query_embedding,
        tenant_id=tenant_id,
        limit=10
    )
    print(f"   Chunks retrieved: {len(chunks)}")
    
    if len(chunks) == 0:
        print(f"   ❌ PROBLEM: No chunks retrieved!")
        print(f"   This explains 'no data' message")
        return
    
    # Show chunks
    print(f"\n   Chunks details:")
    for i, chunk in enumerate(chunks[:3], 1):
        chunk_text = chunk.get("chunk_text", "")[:150]
        similarity = chunk.get("similarity", 0)
        print(f"      Chunk {i}: similarity={similarity:.3f}")
        print(f"         Text: {chunk_text}...")
    
    # STEP 5: Filter chunks (relevance check)
    print("\n📋 STEP 5: Filter chunks for relevance")
    relevant_chunks = []
    for chunk in chunks:
        chunk_text = chunk.get("chunk_text") or chunk.get("text", "")
        if not chunk_text:
            continue
        
        chunk_text_stripped = chunk_text.strip()
        
        # Minimum meaningful content check
        if len(chunk_text_stripped) < 50:
            print(f"   ⚠️  Filtered: Chunk too short ({len(chunk_text_stripped)} chars)")
            continue
        
        # Check for placeholder/error indicators
        error_indicators = [
            "nessun documento", "no document", "documento non trovato", "not found",
            "empty database", "database vuoto", "no data available", "nessun dato disponibile",
            "placeholder", "example", "test data", "dummy data"
        ]
        chunk_lower = chunk_text_stripped.lower()
        if any(indicator in chunk_lower for indicator in error_indicators):
            print(f"   ⚠️  Filtered: Contains error indicator")
            continue
        
        relevant_chunks.append(chunk)
    
    print(f"   Relevant chunks after filtering: {len(relevant_chunks)}")
    
    if len(relevant_chunks) == 0:
        print(f"   ❌ PROBLEM: All chunks filtered out!")
        print(f"   This would cause 'no data' message")
        return
    
    # STEP 6: Generate claims
    print("\n📋 STEP 6: Generate claims with LLM")
    try:
        generation_result = await pipeline.neurale.generate_claims(
            question=question,
            chunks=relevant_chunks,
            tenant_id=tenant_id,
            persona="strategic",
            model="anthropic.sonnet-4"  # Use available model
        )
        
        claims = generation_result.get("claims", [])
        answer_text = generation_result.get("answer", "")
        
        print(f"   Claims generated: {len(claims)}")
        print(f"   Answer text length: {len(answer_text)}")
        print(f"   Answer preview: {answer_text[:200]}...")
        
        # Check if answer says "no data"
        answer_lower = answer_text.lower()
        if "non ho informazioni sufficienti" in answer_lower:
            print(f"   ⚠️  WARNING: LLM generated 'no data' message!")
            print(f"   This is the problem even though chunks exist!")
        
        if len(claims) == 0:
            print(f"   ❌ PROBLEM: No claims generated!")
            print(f"   This would cause 'no data' message")
            return
        
        # STEP 7: Verify claims
        print("\n📋 STEP 7: Verify claims")
        verification = pipeline.verifier.verify_claims(
            claims=claims,
            chunks=relevant_chunks,
            tenant_id=tenant_id
        )
        
        verified_claims = verification.get("verified_claims", [])
        blocked_claims = verification.get("blocked_claims", [])
        
        print(f"   Verified claims: {len(verified_claims)}")
        print(f"   Blocked claims: {len(blocked_claims)}")
        
        if len(verified_claims) == 0:
            print(f"   ❌ PROBLEM: All claims blocked!")
            print(f"   This would cause 'no data' message")
            return
        
        # STEP 8: Check contradiction
        print("\n📋 STEP 8: Check for contradiction")
        if "non ho informazioni sufficienti" in answer_lower and len(verified_claims) > 0:
            print(f"   ❌ CONTRADICTION DETECTED!")
            print(f"   Answer says 'no data' but has {len(verified_claims)} verified claims")
            print(f"   This is the bug we're fixing!")
        
        # STEP 9: Post-verification
        print("\n📋 STEP 9: Post-verification")
        from app.services.post_verification_service import PostVerificationService
        post_verifier = PostVerificationService()
        should_block, block_reason = post_verifier.should_block_response(
            answer_text,
            verified_claims,
            relevant_chunks,
            question
        )
        
        print(f"   Should block: {should_block}")
        if should_block:
            print(f"   Block reason: {block_reason}")
            print(f"   ❌ PROBLEM: Post-verification blocked response!")
        
        print("\n" + "="*70)
        print("✅ DEBUG COMPLETE - Check steps above for problems")
        print("="*70 + "\n")
        
    except Exception as e:
        print(f"\n❌ ERROR in claims generation: {e}")
        import traceback
        traceback.print_exc()


if __name__ == "__main__":
    asyncio.run(test_debug_florenceegi_query_step_by_step())

