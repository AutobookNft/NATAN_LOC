# USE Pipeline - Regole Logiche e Condizioni

## Principio Fondamentale

**REGOLA ZERO**: Se non ci sono dati verificabili nei documenti, NON inventare. Ritorna "non ho informazioni".

**Priorità delle Fonti**: Claims verificati con fonti hanno sempre la precedenza sull'answer text dell'LLM.

---

## Decision Tree Completo

```
START: Query ricevuta
│
├─→ Classifier → Router
│
├─→ Action: BLOCK
│   └─→ Return: {"status": "blocked", "reason": "..."}
│
├─→ Action: DIRECT_QUERY (conversational)
│   └─→ Return: Conversational response
│
└─→ Action: RAG_STRICT
    │
    ├─→ Retrieve chunks
    │   │
    │   ├─→ NO CHUNKS retrieved
    │   │   └─→ Return: {"status": "no_results", "verified_claims": [], "answer": "Non ho informazioni..."}
    │   │
    │   ├─→ Chunks EMPTY/PLACEHOLDER (error indicators, < 50 chars)
    │   │   └─→ Return: {"status": "no_results", "verified_claims": [], "answer": "Non ho informazioni..."}
    │   │
    │   └─→ Valid chunks exist
    │       │
    │       ├─→ Generate claims + answer text
    │       │
    │       ├─→ NO CLAIMS generated
    │       │   └─→ Return: {"status": "no_results", "verified_claims": [], "answer": "Non ho informazioni..."}
    │       │
    │       ├─→ Claims generated
    │       │   │
    │       │   ├─→ Check: Answer text says "no data"?
    │       │   │   │
    │       │   │   ├─→ YES + Verified claims exist
    │       │   │   │   └─→ RECONSTRUCT answer from verified claims
    │       │   │   │       └─→ Answer = "Basandomi sui documenti disponibili:\n\n• [claim 1]\n\n• [claim 2]"
    │       │   │   │
    │       │   │   └─→ NO (answer is valid)
    │       │   │       └─→ Continue...
    │       │   │
    │       │   ├─→ Verify claims
    │       │   │
    │       │   ├─→ ALL claims BLOCKED (hallucinations)
    │       │   │   └─→ Return: {"status": "no_results", "verified_claims": [], "blocked_claims": [], "answer": "Non ho informazioni..."}
    │       │   │       NOTE: blocked_claims NEVER exposed (security)
    │       │   │
    │       │   ├─→ Some/All claims VERIFIED
    │       │   │   │
    │       │   │   ├─→ Post-verification
    │       │   │   │
    │       │   │   ├─→ Post-verification FAILS
    │       │   │   │   └─→ Return: {"status": "no_results", "verified_claims": [], "answer": "Non ho informazioni..."}
    │       │   │   │
    │       │   │   └─→ Post-verification PASSES
    │       │   │       └─→ Return: {"status": "success", "verified_claims": [...], "answer": "...", "blocked_claims": []}
    │       │   │           NOTE: blocked_claims ALWAYS [] in response (never exposed)
```

---

## Regole Critiche

### 1. No Chunks → No Data
**Condizione**: `len(chunks) == 0`  
**Azione**: Return `no_results` con `verified_claims: []`  
**Ragione**: Nessun documento trovato = nessun dato disponibile

### 2. Chunks Vuoti/Placeholder → No Data
**Condizione**: Chunks contengono error indicators o sono troppo brevi (< 50 chars)  
**Azione**: Return `no_results` con `verified_claims: []`  
**Ragione**: Chunks non contengono informazioni reali

### 3. No Claims → No Data
**Condizione**: `len(claims) == 0` dopo generazione  
**Azione**: Return `no_results` con `verified_claims: []`  
**Ragione**: LLM non ha potuto estrarre informazioni dai chunks

### 4. Answer "No Data" + Verified Claims → Ricostruisci Answer
**Condizione**: Answer text contiene "non ho informazioni sufficienti" MA `len(verified_claims) > 0`  
**Azione**: **Ricostruisci answer dai verified claims**  
**Ragione**: Se ci sono claims verificati con fonti, significa che CI SONO dati. L'answer è errato.  
**Logica**: Claims verificati hanno precedenza sull'answer text dell'LLM.

```python
if answer_says_no_data and len(verified_claims) > 0:
    # LLM answer is WRONG - we have verified data
    answer_text = "Basandomi sui documenti disponibili:\n\n" + "\n\n".join(f"• {claim['text']}" for claim in verified_claims)
```

### 5. All Claims Blocked → No Data
**Condizione**: `len(verified_claims) == 0` e `len(blocked_claims) > 0`  
**Azione**: Return `no_results` con `verified_claims: []` e `blocked_claims: []`  
**Ragione**: Tutti i claims sono allucinazioni. Mai esporre blocked_claims all'utente.

### 6. Post-Verification Fails → No Data
**Condizione**: Post-verification service ritorna `should_block=True`  
**Azione**: Return `no_results` con `verified_claims: []`  
**Ragione**: Answer contiene affermazioni non tracciabili alle fonti

### 7. Blocked Claims MAI Esposti
**Condizione**: Sempre  
**Azione**: `blocked_claims: []` in ogni risposta  
**Ragione**: Sicurezza legale - dati inventati non devono mai essere mostrati all'utente

---

## Invarianti (Devono essere sempre vere)

1. ✅ `status == "no_results"` → `verified_claims == []`
2. ✅ `status == "success"` → `len(verified_claims) > 0` (tranne conversational)
3. ✅ `blocked_claims == []` in ogni risposta (mai esposti)
4. ✅ `answer` contiene "non ho informazioni" → `verified_claims == []` (se no claims verificati)
5. ✅ `len(verified_claims) > 0` → `answer` NON contiene "non ho informazioni" (dopo ricostruzione)

---

## Test Coverage

Tutti questi scenari sono coperti dai test in `tests/test_use_pipeline_logic.py`:

- ✅ TEST 1: No chunks → no_results
- ✅ TEST 2: Empty chunks → no_results
- ✅ TEST 3: Valid chunks + verified claims → success
- ✅ TEST 4: Answer "no data" + verified claims → reconstruct answer
- ✅ TEST 5: No claims → no_results
- ✅ TEST 6: All claims blocked → no_results
- ✅ TEST 7: Post-verification fails → no_results
- ✅ TEST 8: Blocked claims never exposed
- ✅ TEST 9: Logical rules summary

---

## Esecuzione Test

```bash
# Tutti i test
cd python_ai_service
./run_tests.sh

# O manualmente
pytest tests/test_use_pipeline_logic.py -v -s

# Con coverage
pytest tests/test_use_pipeline_logic.py --cov=app.services.use_pipeline --cov-report=html
```

---

## Note per Sviluppatori

1. **Mai modificare la logica senza aggiornare i test**
2. **Ogni nuova condizione deve avere un test dedicato**
3. **I test sono la documentazione vivente della logica**
4. **Se un test fallisce, la logica è rotta**

---

**Data creazione**: 2025-11-02  
**Versione**: 1.0.0  
**Autore**: Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici






