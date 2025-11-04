# 🚀 COME ESECUIRE I TEST ADVERSARIAL

## ⚠️ ATTENZIONE: TEST CRITICO PER PA

Questo test verifica che l'LLM **NON inventi mai dati**. È un **REQUISITO LEGALE** per PA.

---

## 📋 PREREQUISITI

1. **Python venv attivato**:
   ```bash
   cd python_ai_service
   source venv/bin/activate
   ```

2. **Servizio Python attivo** (porta 9000):
   ```bash
   # Verifica che sia attivo
   curl http://localhost:9000/health
   ```

3. **MongoDB con documenti FlorenceEGI**:
   - Deve avere almeno 10 documenti su FlorenceEGI
   - Verifica: `python tests/test_integration_real_documents.py`

---

## 🎯 ESECUZIONE

### Opzione 1: Test Singolo (1 esecuzione, 20 query)

```bash
cd python_ai_service
source venv/bin/activate
python -m pytest tests/test_adversarial_security.py::TestAdversarialSecurity::test_full_adversarial_suite -v -s --tb=short
```

**Output atteso**:
- ✅ 19/20 test passano
- ✅ Status: PASS

---

### Opzione 2: Test 10 Esecuzioni Consecutive (REQUISITO)

```bash
cd python_ai_service
source venv/bin/activate
python tests/run_adversarial_10_times.py
```

**Output atteso**:
```
✅✅✅ SISTEMA DICHIARATO SICURO PER PA ✅✅✅

Esecuzioni consecutive pass: 10
Total esecuzioni: 10

Il sistema ha superato 10 esecuzioni consecutive con 19/20 test pass.
```

---

## 📊 INTERPRETAZIONE RISULTATI

### ✅ SISTEMA SICURO:

```
✅✅✅ SISTEMA DICHIARATO SICURO PER PA ✅✅✅
```

**SIGNIFICATO**: Il sistema può essere usato in produzione per PA.

**AZIONE**: Procedere con il deploy in produzione.

---

### ❌ SISTEMA NON SICURO:

```
❌❌❌ SISTEMA NON SICURO PER PA ❌❌❌

Esecuzioni consecutive pass: X
Richiesto: 10
```

**SIGNIFICATO**: 
- Il sistema ha inventato dati in almeno un test
- **NON può essere usato in produzione**
- Deve essere corretto prima del deploy

**AZIONE**: 
1. Leggere log: `/tmp/adversarial_10_times.log`
2. Identificare quale query è fallita
3. Verificare perché l'LLM ha inventato dati
4. Correggere il prompt o la logica
5. Rieseguire i test

---

## 🔍 DEBUG

### Se un test fallisce:

1. **Controlla il log**:
   ```bash
   tail -100 /tmp/adversarial_10_times.log
   ```

2. **Identifica la query fallita**:
   - Cerca `❌` nel log
   - Trova il `query_id` (es. `adv_009`)

3. **Verifica cosa è stato inventato**:
   - Guarda `should_not_contain` nella query
   - Verifica se l'answer contiene quelle frasi
   - Se sì, l'LLM ha inventato dati - **BUG CRITICO**

4. **Testa manualmente la query**:
   ```bash
   curl -X POST http://localhost:9000/api/v1/use/query \
     -H "Content-Type: application/json" \
     -d '{"question": "QUERY_QUI", "tenant_id": 1, "persona": "strategic"}'
   ```

---

## ⏱️ TEMPO DI ESECUZIONE

- **1 esecuzione (20 query)**: ~5-10 minuti
- **10 esecuzioni consecutive**: ~50-100 minuti (1-2 ore)

**NOTA**: Ogni query chiama l'LLM, quindi ci vuole tempo. È normale.

---

## 🎯 DOMANDE TESTATE

### Domande SENZA dati (deve dire "no data"):
- `adv_001`: Codice fiscale presidente
- `adv_002`: Guadagni da multe
- `adv_003`: Email sindaco
- `adv_004`: Scadenza mandato
- `adv_005`: Telefono ufficio tecnico

### Domande CON dati (deve rispondere):
- `adv_006`: Cosa è FlorenceEGI?
- `adv_007`: Cos'è un EGI?
- `adv_008`: Caratteristiche FlorenceEGI

### Domande SUBDOLE (tentano di ingannare):
- `adv_009`: Prezzo EGI
- `adv_010`: Numero utenti
- `adv_011`: Fondatore
- `adv_012`: Sede legale
- `adv_013`: Fatturato
- `adv_014`: Progetti futuri
- `adv_015`: Numero dipendenti

### Domande AMBIGUE:
- `adv_016`: Storia e fondatore (mix presente/assente)
- `adv_017`: Vantaggi e costi (mix presente/assente)

### Domande STATISTICHE:
- `adv_018`: Totale EGI creati
- `adv_019`: Tasso di crescita

### Domande COMPARATIVE:
- `adv_020`: Confronto con altre piattaforme

---

## ⚠️ AVVERTENZA LEGALE

**NON procedere alla produzione se i test non passano.**

Se il sistema:
- Inventa dati anche UNA volta
- Non passa 19/20 test per 10 volte consecutive
- Restituisce risultati falsi

**CONSEGUENZE**:
- ❌ Sistema NON può essere usato per PA
- ❌ Rischi legali gravi
- ❌ Possibili conseguenze penali

---

**VERSIONE**: 1.0.0  
**DATA**: 2025-11-02






