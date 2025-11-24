import unittest
import sys
import os

# Add project root to path
sys.path.append(os.path.abspath(os.path.join(os.path.dirname(__file__), '..')))

# Import only ToonConverter to test the integration
from toon_utils import ToonConverter


class TestNeuraleToonIntegration(unittest.TestCase):
    
    def test_build_context_toon_format_simulation(self):
        """Test that chunks convert correctly to TOON format (simulating NeuraleStrict._build_context)"""
        
        # Sample chunks simulating RAG retrieval (same structure as in NeuraleStrict)
        chunks = [
            {
                "chunk_text": "Il bilancio 2024 prevede investimenti per 1M euro.",
                "source_ref": {
                    "title": "Bilancio Previsione",
                    "url": "http://example.com/doc1",
                    "page_number": 5
                }
            },
            {
                "chunk_text": "La spesa corrente è aumentata del 2%.",
                "source_ref": {
                    "title": "Report Spese",
                    "url": "http://example.com/doc2",
                    "page": 12
                }
            },
            {
                "text": "Nuovo progetto infrastrutture stradali.",
                "source_ref": {
                    "title": "Progetti Opere Pubbliche",
                    "url": "http://example.com/doc3",
                    "page_number": 3
                }
            }
        ]
        
        # Simulate _build_context logic using ToonConverter
        toon_data = []
        for i, chunk in enumerate(chunks, 1):
            source_ref = chunk.get("source_ref", {})
            
            item = {
                "id": f"chunk_{i}",
                "title": source_ref.get("title", ""),
                "url": source_ref.get("url", ""),
                "page": str(source_ref.get("page_number") or source_ref.get("page") or ""),
                "text": chunk.get("chunk_text") or chunk.get("text", "")
            }
            toon_data.append(item)
            
        context = ToonConverter.to_toon(toon_data, "sources")
        
        print(f"\n[DEBUG] Generated TOON Context:\n{context}\n")
        
        # Assertions
        expected_header = "sources[3]{id,title,url,page,text}:"
        self.assertTrue(context.startswith(expected_header), 
                       f"Header mismatch. Expected: {expected_header}, Got: {context.splitlines()[0]}")
        
        # Check content presence
        self.assertIn("chunk_1", context)
        self.assertIn("Bilancio Previsione", context)
        self.assertIn("http://example.com/doc1", context)
        self.assertIn("5", context)
        self.assertIn("Il bilancio 2024 prevede investimenti per 1M euro.", context)
        
        self.assertIn("chunk_2", context)
        self.assertIn("Report Spese", context)
        self.assertIn("12", context)
        self.assertIn("La spesa corrente è aumentata del 2%.", context)
        
        self.assertIn("chunk_3", context)
        self.assertIn("Progetti Opere Pubbliche", context)
        self.assertIn("Nuovo progetto infrastrutture stradali.", context)
        
        # Verify we can convert back without data loss
        restored = ToonConverter.from_toon(context)
        self.assertEqual(len(restored), 3)
        self.assertEqual(restored[0]["id"], "chunk_1")
        self.assertEqual(restored[0]["title"], "Bilancio Previsione")
        self.assertEqual(restored[0]["text"], "Il bilancio 2024 prevede investimenti per 1M euro.")
        
        print("[SUCCESS] TOON format integration test passed. Context is correctly formatted and reversible.")

    def test_token_reduction_estimate(self):
        """Estimate token reduction with TOON vs JSON format"""
        
        # Sample data
        chunks = [
            {
                "chunk_text": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
                "source_ref": {"title": "Doc1", "url": "http://ex.com/1", "page_number": 1}
            },
            {
                "chunk_text": "Sed do eiusmod tempor incididunt ut labore et dolore magna.",
                "source_ref": {"title": "Doc2", "url": "http://ex.com/2", "page_number": 2}
            }
        ]
        
        # Build TOON format
        toon_data = []
        for i, chunk in enumerate(chunks, 1):
            source_ref = chunk.get("source_ref", {})
            item = {
                "id": f"chunk_{i}",
                "title": source_ref.get("title", ""),
                "url": source_ref.get("url", ""),
                "page": str(source_ref.get("page_number") or ""),
                "text": chunk.get("chunk_text", "")
            }
            toon_data.append(item)
        
        toon_format = ToonConverter.to_toon(toon_data, "sources")
        
        # Build equivalent JSON format (as string)
        import json
        json_format = json.dumps(toon_data, ensure_ascii=False)
        
        # Rough token estimate (1 token ≈ 4 chars in English)
        toon_tokens = len(toon_format) / 4
        json_tokens = len(json_format) / 4
        
        reduction = ((json_tokens - toon_tokens) / json_tokens) * 100
        
        print(f"\n[TOKEN REDUCTION ANALYSIS]")
        print(f"TOON format: {len(toon_format)} chars (~{toon_tokens:.0f} tokens)")
        print(f"JSON format: {len(json_format)} chars (~{json_tokens:.0f} tokens)")
        print(f"Reduction: {reduction:.1f}%\n")
        
        # Assert reduction is positive (TOON should be smaller)
        self.assertGreater(reduction, 0, "TOON should reduce token count compared to JSON")
        self.assertGreater(reduction, 20, "TOON should reduce tokens by at least 20%")


if __name__ == '__main__':
    unittest.main()
