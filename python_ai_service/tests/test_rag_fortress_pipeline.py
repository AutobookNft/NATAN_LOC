#!/usr/bin/env python3
"""
Test completo RAG-Fortress Pipeline
"""

import sys
import asyncio
from pathlib import Path
sys.path.insert(0, str(Path(__file__).parent.parent))

from app.services.rag_fortress.pipeline import rag_fortress

async def test_rag_fortress():
    """Test pipeline RAG-Fortress completa"""
    print("🧪 Test RAG-Fortress Pipeline")
    print("=" * 60)
    
    # Test con domanda esempio
    question = "Qual è l'importo stanziato nella delibera n. 123/2024?"
    tenant_id = "1"
    
    try:
        result = await rag_fortress(
            question=question,
            tenant_id=tenant_id,
            user_id=None
        )
        
        print(f"\n✅ Pipeline completata!")
        print(f"📝 Risposta: {result.get('answer', '')[:200]}...")
        print(f"📊 URS Score: {result.get('urs_score', 0)}/100")
        print(f"📋 Claims usate: {len(result.get('claims_used', []))}")
        print(f"🔗 Fonti: {len(result.get('sources', []))}")
        print(f"⚠️ Allucinazioni: {len(result.get('hallucinations_found', []))}")
        print(f"📉 Gap: {len(result.get('gaps_detected', []))}")
        
        return True
        
    except Exception as e:
        print(f"❌ Errore: {e}")
        import traceback
        traceback.print_exc()
        return False

if __name__ == "__main__":
    success = asyncio.run(test_rag_fortress())
    sys.exit(0 if success else 1)

