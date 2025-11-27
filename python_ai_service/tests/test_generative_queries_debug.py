"""
Test diagnostico per query generative - Debug BLOCKED issue

Testa step by step il flusso completo per identificare dove si blocca.
"""

import sys
import os
sys.path.insert(0, os.path.abspath(os.path.join(os.path.dirname(__file__), '..')))

from app.services.question_classifier import QuestionClassifier, QueryIntent
from app.services.execution_router import ExecutionRouter, RouterAction


def test_question_classifier():
    """Test 1: Question Classifier classifica correttamente query generative?"""
    print("\n" + "="*80)
    print("TEST 1: Question Classifier")
    print("="*80)
    
    classifier = QuestionClassifier()
    
    # Query generative da testare
    generative_queries = [
        "Crea una matrice decisionale per prioritizzare i progetti in base a impatto, urgenza, costo e fattibilità tecnica",
        "Genera un framework per organizzare i bandi pubblici",
        "Sviluppa un template per valutare le proposte",
        "Suggerisci un piano di lavoro per il progetto X",
        "Elabora una strategia per migliorare l'efficienza",
        "Progetta un sistema di monitoraggio per i progetti",
        "Scrivi un documento di analisi per le priorità"
    ]
    
    print("\nTestando classificazione di query generative:")
    all_correct = True
    
    for query in generative_queries:
        result = classifier.classify(question=query, tenant_id=2, model="light")
        intent = result["intent"]
        confidence = result["confidence"]
        
        is_correct = intent == QueryIntent.GENERATIVE.value
        status = "✅" if is_correct else "❌"
        
        print(f"\n{status} Query: {query[:60]}...")
        print(f"   Intent: {intent} (expected: {QueryIntent.GENERATIVE.value})")
        print(f"   Confidence: {confidence}")
        
        if not is_correct:
            all_correct = False
    
    print("\n" + "-"*80)
    if all_correct:
        print("✅ TEST 1 PASSED: Question Classifier classifica correttamente")
    else:
        print("❌ TEST 1 FAILED: Question Classifier NON classifica correttamente")
        print("\n🔧 DIAGNOSI: Il problema è nel Question Classifier")
        print("   Possibili cause:")
        print("   - Pattern keywords non matchano")
        print("   - Logica classificazione ha priorità errata")
        print("   - has_document_request override la classificazione")
    
    return all_correct


def test_execution_router():
    """Test 2: Execution Router mappa correttamente GENERATIVE → RAG_GENERATIVE?"""
    print("\n" + "="*80)
    print("TEST 2: Execution Router")
    print("="*80)
    
    router = ExecutionRouter()
    
    # Test mapping GENERATIVE → RAG_GENERATIVE
    routing = router.route(
        intent=QueryIntent.GENERATIVE.value,
        confidence=0.85,
        question="Crea una matrice decisionale",
        tenant_id=2,
        constraints={}
    )
    
    action = routing["action"]
    reason = routing["reason"]
    
    is_correct = action == RouterAction.RAG_GENERATIVE.value
    
    print(f"\nIntent: {QueryIntent.GENERATIVE.value}")
    print(f"Action: {action} (expected: {RouterAction.RAG_GENERATIVE.value})")
    print(f"Reason: {reason}")
    print(f"Requires AI: {routing['requires_ai']}")
    
    if is_correct:
        print("\n✅ TEST 2 PASSED: Execution Router mappa correttamente")
    else:
        print("\n❌ TEST 2 FAILED: Execution Router NON mappa correttamente")
        print("\n🔧 DIAGNOSI: Il problema è nell'Execution Router")
        print("   Possibili cause:")
        print("   - INTENT_ACTION_MAP non aggiornato")
        print("   - Override logic blocca il mapping")
    
    return is_correct


def test_full_flow():
    """Test 3: Flusso completo Classifier → Router"""
    print("\n" + "="*80)
    print("TEST 3: Flusso Completo (Classifier → Router)")
    print("="*80)
    
    classifier = QuestionClassifier()
    router = ExecutionRouter()
    
    query = "Crea una matrice decisionale per prioritizzare i progetti in base a impatto, urgenza, costo e fattibilità tecnica"
    
    print(f"\nQuery: {query}")
    
    # Step 1: Classify
    print("\n--- STEP 1: Classification ---")
    classification = classifier.classify(question=query, tenant_id=2, model="light")
    intent = classification["intent"]
    confidence = classification["confidence"]
    
    print(f"Intent: {intent}")
    print(f"Confidence: {confidence}")
    print(f"Constraints: {classification.get('constraints', {})}")
    
    # Step 2: Route
    print("\n--- STEP 2: Routing ---")
    routing = router.route(
        intent=intent,
        confidence=confidence,
        question=query,
        tenant_id=2,
        constraints=classification.get("constraints", {})
    )
    
    action = routing["action"]
    reason = routing["reason"]
    
    print(f"Action: {action}")
    print(f"Reason: {reason}")
    print(f"Requires AI: {routing['requires_ai']}")
    print(f"Can Respond Directly: {routing['can_respond_directly']}")
    
    # Verifica risultato atteso
    expected_flow = (
        intent == QueryIntent.GENERATIVE.value and
        action == RouterAction.RAG_GENERATIVE.value
    )
    
    print("\n" + "-"*80)
    if expected_flow:
        print("✅ TEST 3 PASSED: Flusso completo corretto")
        print(f"   {intent} → {action}")
    else:
        print("❌ TEST 3 FAILED: Flusso bloccato!")
        print(f"   Expected: {QueryIntent.GENERATIVE.value} → {RouterAction.RAG_GENERATIVE.value}")
        print(f"   Got: {intent} → {action}")
        
        # Diagnosi dettagliata
        print("\n🔧 DIAGNOSI DETTAGLIATA:")
        if intent != QueryIntent.GENERATIVE.value:
            print(f"   ❌ Classifier non riconosce come GENERATIVE (intent={intent})")
            print("   → Fix: Aggiornare pattern keywords nel QuestionClassifier")
        elif action != RouterAction.RAG_GENERATIVE.value:
            print(f"   ❌ Router non mappa a RAG_GENERATIVE (action={action})")
            print("   → Fix: Verificare INTENT_ACTION_MAP in ExecutionRouter")
        else:
            print("   ❌ Problema sconosciuto nel flusso")
    
    return expected_flow


def test_keyword_patterns():
    """Test 4: Verifica pattern keywords GENERATIVE"""
    print("\n" + "="*80)
    print("TEST 4: Keyword Patterns GENERATIVE")
    print("="*80)
    
    # Verifica che i pattern siano caricati correttamente
    classifier = QuestionClassifier()
    
    generative_keywords = classifier.KEYWORD_PATTERNS.get(QueryIntent.GENERATIVE, [])
    
    print(f"\nPattern keywords GENERATIVE caricati: {len(generative_keywords)} keywords")
    print("\nPrimi 20 keywords:")
    for kw in generative_keywords[:20]:
        print(f"  - {kw}")
    
    # Test matching sulla query problematica
    query = "Crea una matrice decisionale per prioritizzare i progetti"
    query_lower = query.lower()
    
    print(f"\nQuery test: {query}")
    print("\nKeywords che matchano:")
    matches = [kw for kw in generative_keywords if kw in query_lower]
    
    if matches:
        print("✅ Keywords trovate:")
        for match in matches:
            print(f"  - '{match}'")
    else:
        print("❌ NESSUNA keyword trovata!")
        print("\n🔧 DIAGNOSI: Pattern keywords non matchano la query")
        print("   Possibili cause:")
        print("   - Keyword 'crea' non in lista")
        print("   - Keyword 'matrice' non in lista")
        print("   - Pattern non case-insensitive")
    
    return len(matches) > 0


def run_all_tests():
    """Esegui tutti i test diagnostici"""
    print("\n")
    print("🔬 NATAN_LOC - Test Diagnostico Query Generative")
    print("=" * 80)
    print("Obiettivo: Identificare perché le query generative vengono BLOCKED")
    print("=" * 80)
    
    results = {
        "test_1_classifier": test_question_classifier(),
        "test_2_router": test_execution_router(),
        "test_3_full_flow": test_full_flow(),
        "test_4_keywords": test_keyword_patterns()
    }
    
    # Summary
    print("\n" + "="*80)
    print("SUMMARY")
    print("="*80)
    
    for test_name, passed in results.items():
        status = "✅ PASSED" if passed else "❌ FAILED"
        print(f"{status}: {test_name}")
    
    all_passed = all(results.values())
    
    print("\n" + "="*80)
    if all_passed:
        print("🎉 TUTTI I TEST PASSATI! Il sistema dovrebbe funzionare correttamente.")
    else:
        print("❌ ALCUNI TEST FALLITI. Vedere diagnosi sopra per fix necessari.")
        
        # Raccomandazioni prioritarie
        print("\n📋 RACCOMANDAZIONI PRIORITARIE:")
        if not results["test_4_keywords"]:
            print("   1. Fix keywords pattern nel QuestionClassifier")
        if not results["test_1_classifier"]:
            print("   2. Fix logica classificazione nel QuestionClassifier")
        if not results["test_2_router"]:
            print("   3. Fix mapping nell'ExecutionRouter")
        if not results["test_3_full_flow"]:
            print("   4. Verificare integrazione completa")
    
    print("="*80)
    
    return all_passed


if __name__ == "__main__":
    run_all_tests()

