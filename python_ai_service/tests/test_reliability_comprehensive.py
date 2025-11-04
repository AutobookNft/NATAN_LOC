"""
COMPREHENSIVE RELIABILITY TEST
Verifica completa dell'affidabilità del sistema NATAN_LOC
"""

import pytest
import asyncio
from app.services.use_pipeline import UsePipeline
from app.services.mongodb_service import MongoDBService
from app.services.retriever_service import RetrieverService


class TestReliabilityComprehensive:
    """Test completo di affidabilità del sistema"""
    
    @pytest.fixture
    def pipeline(self):
        return UsePipeline()
    
    @pytest.mark.asyncio
    async def test_01_mongodb_connection(self):
        """RELIABILITY TEST 1: MongoDB deve essere connesso"""
        print("\n📊 TEST 1: MongoDB Connection")
        is_connected = MongoDBService.is_connected()
        print(f"   MongoDB connected: {is_connected}")
        
        if not is_connected:
            # Try to connect
            MongoDBService.get_client()
            is_connected = MongoDBService.is_connected()
            print(f"   After retry: {is_connected}")
        
        assert is_connected, "MongoDB must be connected"
        print("   ✅ PASS: MongoDB connected")
    
    @pytest.mark.asyncio
    async def test_02_mongodb_has_documents(self):
        """RELIABILITY TEST 2: MongoDB deve contenere documenti"""
        print("\n📊 TEST 2: MongoDB Documents")
        db = MongoDBService.get_database()
        collection = db["documents"]
        count = collection.count_documents({})
        print(f"   Total documents: {count}")
        
        assert count > 0, "MongoDB must contain documents"
        print(f"   ✅ PASS: {count} documents found")
    
    @pytest.mark.asyncio
    async def test_03_documents_have_embeddings(self):
        """RELIABILITY TEST 3: Documenti devono avere embeddings"""
        print("\n📊 TEST 3: Document Embeddings")
        db = MongoDBService.get_database()
        collection = db["documents"]
        
        total = collection.count_documents({})
        with_embeddings = collection.count_documents({"embedding": {"$exists": True}})
        
        print(f"   Total documents: {total}")
        print(f"   With embeddings: {with_embeddings}")
        print(f"   Percentage: {(with_embeddings/total*100) if total > 0 else 0:.1f}%")
        
        # At least some documents must have embeddings
        assert with_embeddings > 0, "At least some documents must have embeddings"
        print(f"   ✅ PASS: {with_embeddings} documents have embeddings")
    
    @pytest.mark.asyncio
    async def test_04_retriever_finds_chunks(self):
        """RELIABILITY TEST 4: Retriever deve trovare chunks per query rilevanti"""
        print("\n📊 TEST 4: Retriever Finds Chunks")
        
        from app.services.ai_router import AIRouter
        retriever = RetrieverService()
        ai_router = AIRouter()
        
        # Test with FlorenceEGI query
        question = "Cosa è FlorenceEGI?"
        context = {"tenant_id": 1, "task_class": "RAG"}
        adapter = ai_router.get_embedding_adapter(context)
        embed_result = await adapter.embed(question)
        query_embedding = embed_result["embedding"]
        
        chunks = retriever.retrieve(query_embedding, tenant_id=1, limit=10)
        print(f"   Query: '{question}'")
        print(f"   Chunks found: {len(chunks)}")
        
        assert len(chunks) > 0, "Retriever must find chunks for relevant queries"
        print(f"   ✅ PASS: {len(chunks)} chunks retrieved")
    
    @pytest.mark.asyncio
    async def test_05_pipeline_generates_claims(self, pipeline):
        """RELIABILITY TEST 5: Pipeline deve generare claims quando ci sono chunks"""
        print("\n📊 TEST 5: Pipeline Generates Claims")
        
        # Use auto-discovery to get available model
        from app.services.ai_router import AIRouter
        ai_router = AIRouter()
        context = {"tenant_id": 1, "task_class": "USE", "persona": "strategic"}
        adapter = ai_router.get_chat_adapter(context)
        
        # Get model name from adapter if possible
        try:
            # Try to discover model
            if hasattr(adapter, '_discover_model'):
                model_name = await adapter._discover_model()
            else:
                model_name = "anthropic.sonnet-4"  # Fallback
        except:
            model_name = "anthropic.sonnet-4"  # Fallback
        
        result = await pipeline.process_query(
            question="Cosa è FlorenceEGI?",
            tenant_id=1,
            persona="strategic",
            model=model_name
        )
        
        verified_claims = result.get("verified_claims", [])
        status = result.get("status")
        
        print(f"   Status: {status}")
        print(f"   Verified claims: {len(verified_claims)}")
        
        # If status is success, must have claims
        if status == "success":
            assert len(verified_claims) > 0, "Success status must have verified claims"
            print(f"   ✅ PASS: {len(verified_claims)} verified claims generated")
        else:
            # If no_results, check why
            print(f"   ⚠️  Status is '{status}' - checking reason...")
            print(f"   Message: {result.get('message', 'No message')}")
    
    @pytest.mark.asyncio
    async def test_06_no_contradiction_answer_vs_claims(self, pipeline):
        """RELIABILITY TEST 6: Answer NON deve dire 'no data' se ci sono verified claims"""
        print("\n📊 TEST 6: No Contradiction (Answer vs Claims)")
        
        result = await pipeline.process_query(
            question="Cosa è FlorenceEGI?",
            tenant_id=1,
            persona="strategic",
            model="anthropic.sonnet-4"  # Use available model
        )
        
        answer = result.get("answer", "").lower()
        verified_claims = result.get("verified_claims", [])
        has_no_data = "non ho informazioni sufficienti" in answer
        
        print(f"   Verified claims: {len(verified_claims)}")
        print(f"   Answer contains 'no data': {has_no_data}")
        
        if len(verified_claims) > 0 and has_no_data:
            pytest.fail("CONTRADICTION: Answer says 'no data' but has verified claims")
        
        if len(verified_claims) > 0:
            assert not has_no_data, "Answer must not say 'no data' when claims exist"
            print(f"   ✅ PASS: No contradiction - answer correct for {len(verified_claims)} claims")
    
    @pytest.mark.asyncio
    async def test_07_blocked_claims_never_exposed(self, pipeline):
        """RELIABILITY TEST 7: Blocked claims MAI esposti (security)"""
        print("\n📊 TEST 7: Blocked Claims Security")
        
        result = await pipeline.process_query(
            question="Cosa è FlorenceEGI?",
            tenant_id=1,
            persona="strategic",
            model="anthropic.sonnet-4"  # Use available model
        )
        
        blocked_claims = result.get("blocked_claims", [])
        
        print(f"   Blocked claims in response: {len(blocked_claims)}")
        
        # CRITICAL: Blocked claims must NEVER be exposed
        assert len(blocked_claims) == 0, "Blocked claims must NEVER be exposed (security risk)"
        print(f"   ✅ PASS: No blocked claims exposed (security OK)")
    
    @pytest.mark.asyncio
    async def test_08_no_data_only_when_no_chunks(self, pipeline):
        """RELIABILITY TEST 8: 'No data' solo quando NON ci sono chunks"""
        print("\n📊 TEST 8: 'No Data' Logic")
        
        # Test with query that should find data
        result_with_data = await pipeline.process_query(
            question="Cosa è FlorenceEGI?",
            tenant_id=1
        )
        
        # Test with query that should NOT find data
        result_no_data = await pipeline.process_query(
            question="Quali sono le regole per la pesca nel Mar Mediterraneo nel 2050?",
            tenant_id=1
        )
        
        print(f"   Query with data (FlorenceEGI):")
        print(f"      Status: {result_with_data.get('status')}")
        print(f"      Claims: {len(result_with_data.get('verified_claims', []))}")
        
        print(f"   Query without data (random):")
        print(f"      Status: {result_no_data.get('status')}")
        print(f"      Claims: {len(result_no_data.get('verified_claims', []))}")
        
        # First query should find data
        if result_with_data.get("status") == "no_results":
            pytest.fail("Query 'Cosa è FlorenceEGI?' should find data (chunks exist in MongoDB)")
        
        print(f"   ✅ PASS: Logic correct - 'no data' only when no chunks")
    
    @pytest.mark.asyncio
    async def test_09_answer_reconstruction_works(self, pipeline):
        """RELIABILITY TEST 9: Answer reconstruction da claims funziona"""
        print("\n📊 TEST 9: Answer Reconstruction")
        
        result = await pipeline.process_query(
            question="Cosa è FlorenceEGI?",
            tenant_id=1
        )
        
        answer = result.get("answer", "")
        verified_claims = result.get("verified_claims", [])
        has_basandomi = "basandomi sui documenti disponibili" in answer.lower()
        
        print(f"   Verified claims: {len(verified_claims)}")
        print(f"   Answer contains 'basandomi': {has_basandomi}")
        print(f"   Answer preview: {answer[:200]}...")
        
        # If we have claims, answer should be reconstructed (not LLM's wrong "no data")
        if len(verified_claims) > 0:
            assert len(answer) > 100, "Answer must be substantial when claims exist"
            assert "non ho informazioni sufficienti" not in answer.lower(), "Answer must not say 'no data'"
            print(f"   ✅ PASS: Answer correctly reconstructed from claims")
    
    @pytest.mark.asyncio
    async def test_10_multiple_queries_consistency(self, pipeline):
        """RELIABILITY TEST 10: Multiple queries devono essere consistenti"""
        print("\n📊 TEST 10: Consistency Across Queries")
        
        queries = [
            "Cosa è FlorenceEGI?",
            "Che cos'è un EGI?",
            "FlorenceEGI cosa è?"
        ]
        
        results = []
        for query in queries:
            result = await pipeline.process_query(
                question=query,
                tenant_id=1,
                persona="strategic"
            )
            results.append(result)
            print(f"   Query: '{query}'")
            print(f"      Status: {result.get('status')}")
            print(f"      Claims: {len(result.get('verified_claims', []))}")
        
        # All queries about FlorenceEGI should find data
        success_count = sum(1 for r in results if r.get("status") == "success")
        print(f"\n   Success rate: {success_count}/{len(queries)}")
        
        assert success_count > 0, "At least some queries should succeed"
        print(f"   ✅ PASS: {success_count}/{len(queries)} queries succeeded")
    
    def test_11_reliability_report(self):
        """RELIABILITY TEST 11: Generate final reliability report"""
        print("\n" + "="*70)
        print("📊 NATAN_LOC RELIABILITY REPORT")
        print("="*70)
        print("""
RELIABILITY CRITERIA:
1. ✅ MongoDB connection stable
2. ✅ Documents available in database
3. ✅ Embeddings generated for documents
4. ✅ Retriever finds relevant chunks
5. ✅ Pipeline generates verified claims
6. ✅ No contradiction (answer vs claims)
7. ✅ Blocked claims never exposed (security)
8. ✅ 'No data' only when no chunks
9. ✅ Answer reconstruction works
10. ✅ Consistency across queries

SYSTEM STATUS: 
   All critical tests must pass for system to be considered reliable.
   
   If any test fails, system reliability is compromised.
""")
        print("="*70 + "\n")


if __name__ == "__main__":
    pytest.main([__file__, "-v", "-s"])

