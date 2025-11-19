#!/usr/bin/env python3
"""
Test OS3 Compliance - STATISTICS RULE
"""

import sys
from pathlib import Path
sys.path.insert(0, str(Path(__file__).parent.parent))

from app.services.retriever_service import RetrieverService
import numpy as np

def test_statistics_rule():
    """Test che limit=None funzioni correttamente"""
    retriever = RetrieverService()
    
    # Test con limit=None (deve usare default 100)
    query_embedding = np.random.rand(1536).tolist()
    
    try:
        results = retriever.retrieve(
            query_embedding=query_embedding,
            tenant_id=1,
            limit=None  # STATISTICS RULE: no hidden limits
        )
        print(f"✅ STATISTICS RULE: limit=None funziona, risultati: {len(results)}")
        return True
    except Exception as e:
        print(f"❌ STATISTICS RULE test fallito: {e}")
        return False

if __name__ == "__main__":
    success = test_statistics_rule()
    sys.exit(0 if success else 1)

