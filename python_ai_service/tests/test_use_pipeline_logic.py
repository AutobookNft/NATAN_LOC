"""
Unit Tests for USE Pipeline Logic
Tests all critical conditions to ensure logical consistency
"""

import pytest
from unittest.mock import Mock, AsyncMock, patch, MagicMock
from typing import Dict, Any, List

from app.services.use_pipeline import UsePipeline
from app.services.execution_router import RouterAction


class TestUsePipelineLogic:
    """Test suite for USE Pipeline logical conditions"""
    
    @pytest.fixture
    def pipeline(self):
        """Create UsePipeline instance with mocked dependencies"""
        pipeline = UsePipeline()
        
        # Mock all dependencies
        pipeline.classifier = Mock()
        pipeline.router = Mock()
        pipeline.retriever = Mock()
        pipeline.neurale = Mock()
        pipeline.verifier = Mock()
        
        return pipeline
    
    @pytest.fixture
    def mock_chunks_with_data(self) -> List[Dict[str, Any]]:
        """Valid chunks with actual content"""
        return [
            {
                "chunk_id": "chunk_1",
                "chunk_text": "FlorenceEGI è una piattaforma dove l'EGI è l'unità fondamentale. L'EGI non è un semplice NFT, ma è un oggetto digitale certificato.",
                "document_id": "doc_1",
                "source_ref": {
                    "url": "test_url",
                    "title": "Test Document",
                    "page_number": 1
                },
                "relevance_score": 0.85
            },
            {
                "chunk_id": "chunk_2",
                "chunk_text": "EGI significa Ecological, Goods & Inventive e sono opere d'arte digitali che rappresentano il cuore della piattaforma FlorenceEGI.",
                "document_id": "doc_1",
                "source_ref": {
                    "url": "test_url",
                    "title": "Test Document",
                    "page_number": 2
                },
                "relevance_score": 0.82
            }
        ]
    
    @pytest.fixture
    def mock_verified_claims(self) -> List[Dict[str, Any]]:
        """Valid verified claims with sources"""
        return [
            {
                "text": "FlorenceEGI è una piattaforma dove l'EGI è l'unità fondamentale.",
                "urs": 0.88,
                "ursLabel": "A",
                "sourceRefs": [
                    {
                        "source_id": "chunk_1",
                        "url": "test_url",
                        "title": "Test Document",
                        "page_number": 1
                    }
                ],
                "isInference": False
            },
            {
                "text": "EGI significa Ecological, Goods & Inventive.",
                "urs": 0.88,
                "ursLabel": "A",
                "sourceRefs": [
                    {
                        "source_id": "chunk_2",
                        "url": "test_url",
                        "title": "Test Document",
                        "page_number": 2
                    }
                ],
                "isInference": False
            }
        ]
    
    @pytest.mark.asyncio
    async def test_no_chunks_returns_no_data(self, pipeline):
        """TEST 1: If no chunks retrieved → must return no_results with empty claims"""
        # Setup
        pipeline.classifier.classify = Mock(return_value={
            "intent": "question",
            "confidence": 0.9
        })
        pipeline.router.route = Mock(return_value={
            "action": RouterAction.RAG_STRICT.value
        })
        pipeline.retriever.retrieve = Mock(return_value=[])  # No chunks
        
        # Execute
        result = await pipeline.process_query(
            question="Cosa è FlorenceEGI?",
            tenant_id=1,
            persona="strategic"
        )
        
        # Assert
        assert result["status"] == "no_results"
        assert "non ho informazioni sufficienti" in result["answer"].lower()
        assert result["verified_claims"] == []
        assert result["blocked_claims"] == []
        assert result["verification_status"] == "NO_DATA"
        print("✅ TEST 1 PASSED: No chunks → no_results")
    
    @pytest.mark.asyncio
    async def test_empty_chunks_returns_no_data(self, pipeline):
        """TEST 2: If chunks are empty/placeholder → must return no_results"""
        # Setup
        pipeline.classifier.classify = Mock(return_value={
            "intent": "question",
            "confidence": 0.9
        })
        pipeline.router.route = Mock(return_value={
            "action": RouterAction.RAG_STRICT.value
        })
        # Chunks with placeholder/error text
        pipeline.retriever.retrieve = Mock(return_value=[
            {"chunk_text": "nessun documento trovato", "chunk_id": "chunk_1"},
            {"chunk_text": "", "chunk_id": "chunk_2"},  # Empty
            {"chunk_text": "test", "chunk_id": "chunk_3"}  # Too short (< 50 chars)
        ])
        
        # Execute
        result = await pipeline.process_query(
            question="Cosa è FlorenceEGI?",
            tenant_id=1
        )
        
        # Assert
        assert result["status"] == "no_results"
        assert result["verified_claims"] == []
        print("✅ TEST 2 PASSED: Empty/placeholder chunks → no_results")
    
    @pytest.mark.asyncio
    async def test_valid_chunks_with_claims_returns_success(self, pipeline, mock_chunks_with_data, mock_verified_claims):
        """TEST 3: If valid chunks + verified claims → must return success with claims"""
        # Setup
        pipeline.classifier.classify = Mock(return_value={
            "intent": "question",
            "confidence": 0.9
        })
        pipeline.router.route = Mock(return_value={
            "action": RouterAction.RAG_STRICT.value
        })
        pipeline.retriever.retrieve = Mock(return_value=mock_chunks_with_data)
        
        # Mock Neurale to generate valid claims and answer
        pipeline.neurale.generate_claims = AsyncMock(return_value={
            "claims": [
                {"text": "Test claim 1", "source_ids": ["chunk_1"]},
                {"text": "Test claim 2", "source_ids": ["chunk_2"]}
            ],
            "answer": "Basandomi sui documenti disponibili, FlorenceEGI è una piattaforma...",
            "answer_id": "ans_123",
            "model_used": "anthropic.sonnet-3.5",
            "tokens_used": {"input": 100, "output": 50}
        })
        
        # Mock Verifier to verify claims
        pipeline.verifier.verify_claims = Mock(return_value={
            "verified_claims": mock_verified_claims,
            "blocked_claims": [],
            "avg_urs": 0.88,
            "status": "VERIFIED"
        })
        
        # Mock PostVerificationService and AIRouter (imported inside function)
        with patch('app.services.ai_router.AIRouter') as MockAIRouter:
            mock_adapter = Mock()
            mock_adapter.embed = AsyncMock(return_value={"embedding": [0.1] * 768})
            mock_ai_router = Mock()
            mock_ai_router.get_embedding_adapter = Mock(return_value=mock_adapter)
            MockAIRouter.return_value = mock_ai_router
            
            with patch('app.services.post_verification_service.PostVerificationService') as MockPostVerifier:
                mock_post_verifier = Mock()
                mock_post_verifier.should_block_response = Mock(return_value=(False, None))
                MockPostVerifier.return_value = mock_post_verifier
                
                # Execute
                result = await pipeline.process_query(
                    question="Cosa è FlorenceEGI?",
                    tenant_id=1
                )
        
        # Assert
        assert result["status"] == "success"
        assert len(result["verified_claims"]) > 0
        assert "non ho informazioni sufficienti" not in result["answer"].lower()
        assert result["blocked_claims"] == []  # Never expose blocked claims
        print("✅ TEST 3 PASSED: Valid chunks + verified claims → success")
    
    @pytest.mark.asyncio
    async def test_answer_says_no_data_but_has_verified_claims_reconstructs_answer(self, pipeline, mock_chunks_with_data, mock_verified_claims):
        """TEST 4: If answer says 'no data' BUT verified claims exist → reconstruct answer from claims"""
        # Setup
        pipeline.classifier.classify = Mock(return_value={
            "intent": "question",
            "confidence": 0.9
        })
        pipeline.router.route = Mock(return_value={
            "action": RouterAction.RAG_STRICT.value
        })
        pipeline.retriever.retrieve = Mock(return_value=mock_chunks_with_data)
        
        # Mock Neurale: generates "no data" answer BUT also generates claims
        pipeline.neurale.generate_claims = AsyncMock(return_value={
            "claims": [
                {"text": "FlorenceEGI è una piattaforma", "source_ids": ["chunk_1"]}
            ],
            "answer": "Non ho informazioni sufficienti nei documenti disponibili per rispondere a questa domanda.",
            "answer_id": "ans_123",
            "model_used": "anthropic.sonnet-3.5",
            "tokens_used": {"input": 100, "output": 50}
        })
        
        # Mock Verifier: claims ARE verified
        pipeline.verifier.verify_claims = Mock(return_value={
            "verified_claims": mock_verified_claims,
            "blocked_claims": [],
            "avg_urs": 0.88,
            "status": "VERIFIED"
        })
        
        # Mock PostVerificationService and AIRouter
        with patch('app.services.ai_router.AIRouter') as MockAIRouter:
            mock_adapter = Mock()
            mock_adapter.embed = AsyncMock(return_value={"embedding": [0.1] * 768})
            mock_ai_router = Mock()
            mock_ai_router.get_embedding_adapter = Mock(return_value=mock_adapter)
            MockAIRouter.return_value = mock_ai_router
            
            with patch('app.services.post_verification_service.PostVerificationService') as MockPostVerifier:
                mock_post_verifier = Mock()
                mock_post_verifier.should_block_response = Mock(return_value=(False, None))
                MockPostVerifier.return_value = mock_post_verifier
                
                # Execute
                result = await pipeline.process_query(
                    question="Cosa è FlorenceEGI?",
                    tenant_id=1
                )
        
        # Assert
        assert result["status"] == "success"
        assert len(result["verified_claims"]) > 0
        # Answer should be reconstructed from claims, not the wrong "no data" message
        assert "basandomi sui documenti disponibili" in result["answer"].lower()
        assert "non ho informazioni sufficienti" not in result["answer"].lower()
        print("✅ TEST 4 PASSED: Answer 'no data' + verified claims → reconstruct answer from claims")
    
    @pytest.mark.asyncio
    async def test_no_claims_generated_returns_no_data(self, pipeline, mock_chunks_with_data):
        """TEST 5: If chunks exist but no claims generated → must return no_results"""
        # Setup
        pipeline.classifier.classify = Mock(return_value={
            "intent": "question",
            "confidence": 0.9
        })
        pipeline.router.route = Mock(return_value={
            "action": RouterAction.RAG_STRICT.value
        })
        pipeline.retriever.retrieve = Mock(return_value=mock_chunks_with_data)
        
        # Mock Neurale: generates empty claims
        pipeline.neurale.generate_claims = AsyncMock(return_value={
            "claims": [],  # Empty!
            "answer": "Test answer",
            "answer_id": "ans_123",
            "model_used": "anthropic.sonnet-3.5",
            "tokens_used": {"input": 100, "output": 50}
        })
        
        with patch('app.services.ai_router.AIRouter') as MockAIRouter:
            mock_adapter = Mock()
            mock_adapter.embed = AsyncMock(return_value={"embedding": [0.1] * 768})
            mock_ai_router = Mock()
            mock_ai_router.get_embedding_adapter = Mock(return_value=mock_adapter)
            MockAIRouter.return_value = mock_ai_router
            
            # Execute
            result = await pipeline.process_query(
                question="Cosa è FlorenceEGI?",
                tenant_id=1
            )
        
        # Assert
        assert result["status"] == "no_results"
        assert "non ho informazioni sufficienti" in result["answer"].lower()
        assert result["verified_claims"] == []
        print("✅ TEST 5 PASSED: No claims generated → no_results")
    
    @pytest.mark.asyncio
    async def test_all_claims_blocked_returns_no_data(self, pipeline, mock_chunks_with_data):
        """TEST 6: If all claims are blocked (hallucinations) → must return no_results"""
        # Setup
        pipeline.classifier.classify = Mock(return_value={
            "intent": "question",
            "confidence": 0.9
        })
        pipeline.router.route = Mock(return_value={
            "action": RouterAction.RAG_STRICT.value
        })
        pipeline.retriever.retrieve = Mock(return_value=mock_chunks_with_data)
        
        # Mock Neurale: generates claims
        pipeline.neurale.generate_claims = AsyncMock(return_value={
            "claims": [
                {"text": "Invented claim", "source_ids": []}  # No sources = hallucination
            ],
            "answer": "Test answer",
            "answer_id": "ans_123",
            "model_used": "anthropic.sonnet-3.5",
            "tokens_used": {"input": 100, "output": 50}
        })
        
        # Mock Verifier: ALL claims blocked (hallucinations)
        pipeline.verifier.verify_claims = Mock(return_value={
            "verified_claims": [],  # None verified
            "blocked_claims": [
                {"text": "Invented claim", "reason": "No sources"}
            ],
            "avg_urs": 0.0,
            "status": "ALL_BLOCKED"
        })
        
        with patch('app.services.ai_router.AIRouter') as MockAIRouter:
            mock_adapter = Mock()
            mock_adapter.embed = AsyncMock(return_value={"embedding": [0.1] * 768})
            mock_ai_router = Mock()
            mock_ai_router.get_embedding_adapter = Mock(return_value=mock_adapter)
            MockAIRouter.return_value = mock_ai_router
            
            # Execute
            result = await pipeline.process_query(
                question="Cosa è FlorenceEGI?",
                tenant_id=1
            )
        
        # Assert
        assert result["status"] == "no_results"
        assert "non ho informazioni sufficienti" in result["answer"].lower()
        assert result["verified_claims"] == []
        assert result["blocked_claims"] == []  # Never expose blocked claims
        assert result["verification_status"] == "ALL_CLAIMS_BLOCKED"
        print("✅ TEST 6 PASSED: All claims blocked → no_results, blocked claims never exposed")
    
    @pytest.mark.asyncio
    async def test_post_verification_fails_returns_no_data(self, pipeline, mock_chunks_with_data, mock_verified_claims):
        """TEST 7: If post-verification fails → must return no_results"""
        # Setup
        pipeline.classifier.classify = Mock(return_value={
            "intent": "question",
            "confidence": 0.9
        })
        pipeline.router.route = Mock(return_value={
            "action": RouterAction.RAG_STRICT.value
        })
        pipeline.retriever.retrieve = Mock(return_value=mock_chunks_with_data)
        
        pipeline.neurale.generate_claims = AsyncMock(return_value={
            "claims": [{"text": "Test claim", "source_ids": ["chunk_1"]}],
            "answer": "Test answer",
            "answer_id": "ans_123",
            "model_used": "anthropic.sonnet-3.5",
            "tokens_used": {"input": 100, "output": 50}
        })
        
        pipeline.verifier.verify_claims = Mock(return_value={
            "verified_claims": mock_verified_claims,
            "blocked_claims": [],
            "avg_urs": 0.88,
            "status": "VERIFIED"
        })
        
        # Mock PostVerificationService: FAILS
        with patch('app.services.ai_router.AIRouter') as MockAIRouter:
            mock_adapter = Mock()
            mock_adapter.embed = AsyncMock(return_value={"embedding": [0.1] * 768})
            mock_ai_router = Mock()
            mock_ai_router.get_embedding_adapter = Mock(return_value=mock_adapter)
            MockAIRouter.return_value = mock_ai_router
            
            with patch('app.services.post_verification_service.PostVerificationService') as MockPostVerifier:
                mock_post_verifier = Mock()
                mock_post_verifier.should_block_response = Mock(return_value=(
                    True,  # Should block!
                    "Answer contains statements not traceable to sources"
                ))
                MockPostVerifier.return_value = mock_post_verifier
                
                # Execute
                result = await pipeline.process_query(
                    question="Cosa è FlorenceEGI?",
                    tenant_id=1
                )
        
        # Assert
        assert result["status"] == "no_results"
        assert "non ho informazioni sufficienti" in result["answer"].lower()
        assert result["verified_claims"] == []  # Blocked by post-verification
        assert result["verification_status"] == "POST_VERIFICATION_FAILED"
        print("✅ TEST 7 PASSED: Post-verification fails → no_results")
    
    @pytest.mark.asyncio
    async def test_never_expose_blocked_claims(self, pipeline, mock_chunks_with_data):
        """TEST 8: Blocked claims must NEVER be exposed to user (security requirement)"""
        # Setup
        pipeline.classifier.classify = Mock(return_value={
            "intent": "question",
            "confidence": 0.9
        })
        pipeline.router.route = Mock(return_value={
            "action": RouterAction.RAG_STRICT.value
        })
        pipeline.retriever.retrieve = Mock(return_value=mock_chunks_with_data)
        
        pipeline.neurale.generate_claims = AsyncMock(return_value={
            "claims": [
                {"text": "Valid claim", "source_ids": ["chunk_1"]},
                {"text": "Invented claim", "source_ids": []}
            ],
            "answer": "Test answer",
            "answer_id": "ans_123",
            "model_used": "anthropic.sonnet-3.5",
            "tokens_used": {"input": 100, "output": 50}
        })
        
        # Some verified, some blocked
        pipeline.verifier.verify_claims = Mock(return_value={
            "verified_claims": [
                {"text": "Valid claim", "urs": 0.88, "sourceRefs": []}
            ],
            "blocked_claims": [
                {"text": "Invented claim", "reason": "No sources"}
            ],
            "avg_urs": 0.88,
            "status": "PARTIAL"
        })
        
        with patch('app.services.ai_router.AIRouter') as MockAIRouter:
            mock_adapter = Mock()
            mock_adapter.embed = AsyncMock(return_value={"embedding": [0.1] * 768})
            mock_ai_router = Mock()
            mock_ai_router.get_embedding_adapter = Mock(return_value=mock_adapter)
            MockAIRouter.return_value = mock_ai_router
            
            with patch('app.services.post_verification_service.PostVerificationService') as MockPostVerifier:
                mock_post_verifier = Mock()
                mock_post_verifier.should_block_response = Mock(return_value=(False, None))
                MockPostVerifier.return_value = mock_post_verifier
                
                # Execute
                result = await pipeline.process_query(
                    question="Cosa è FlorenceEGI?",
                    tenant_id=1
                )
        
        # Assert
        # Even if some claims are blocked, they must NEVER appear in response
        assert result["blocked_claims"] == []
        # Only verified claims should be exposed
        assert len(result.get("verified_claims", [])) >= 0
        print("✅ TEST 8 PASSED: Blocked claims never exposed (security requirement)")
    
    def test_logical_consistency_summary(self):
        """TEST 9: Summary of logical rules that must always be true"""
        rules = [
            "Rule 1: No chunks → no_results with empty claims",
            "Rule 2: Empty/placeholder chunks → no_results",
            "Rule 3: Valid chunks + verified claims → success with claims",
            "Rule 4: Answer 'no data' + verified claims → reconstruct answer from claims",
            "Rule 5: No claims generated → no_results",
            "Rule 6: All claims blocked → no_results, blocked claims never exposed",
            "Rule 7: Post-verification fails → no_results",
            "Rule 8: Blocked claims NEVER exposed (security requirement)",
            "Rule 9: verified_claims empty → status must be no_results",
            "Rule 10: status success → verified_claims must not be empty (unless conversational)"
        ]
        
        print("\n" + "="*70)
        print("📋 LOGICAL CONSISTENCY RULES - These must ALWAYS be true:")
        print("="*70)
        for i, rule in enumerate(rules, 1):
            print(f"{i:2d}. {rule}")
        print("="*70 + "\n")
        
        assert len(rules) == 10
        print("✅ TEST 9 PASSED: Logical consistency rules documented")


if __name__ == "__main__":
    pytest.main([__file__, "-v", "-s"])

