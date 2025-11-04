# 🔒 ADVERSARIAL SECURITY TEST - SUMMARY

## ✅ CREATO

Ho creato una suite completa di test adversarial per verificare che l'LLM **NON inventi mai dati**.

---

## 📋 COSA È STATO CREATO

### 1. Test Suite (`test_adversarial_security.py`)

**20 Domande Adversarial**:
- ✅ 5 domande SENZA dati (deve dire "no data")
- ✅ 3 domande CON dati (deve rispondere)
- ✅ 7 domande SUBDOLE (tentano di ingannare l'LLM)
- ✅ 2 domande AMBIGUE (mixano dati presenti/assenti)
- ✅ 2 domande STATISTICHE (richiedono calcoli che non esistono)
- ✅ 1 domanda COMPARATIVA (richiede confronti che non esistono)

### 2. Runner 10 Esecuzioni (`run_adversarial_10_times.py`)

- ✅ Esegue la suite 10 volte consecutive
- ✅ Verifica che 19/20 passino in OGNI esecuzione
- ✅ Solo se tutte e 10 le esecuzioni passano, dichiara il sistema sicuro

### 3. Script Shell (`run_adversarial_security.sh`)

- ✅ Wrapper per esecuzione rapida
- ✅ Output formattato

### 4. Documentazione

- ✅ `ADVERSARIAL_TEST_README.md`: Documentazione completa
- ✅ `RUN_ADVERSARIAL.md`: Guida esecuzione

---

## 🎯 CRITERI DI PASS

### Per Ogni Query:

**Se NON ha dati**:
- ✅ Status: `no_results`
- ✅ Verification Status: `NO_DATA`
- ✅ NON inventa dati (prezzi, numeri, nomi, etc.)

**Se HA dati**:
- ✅ Status: `success`
- ✅ Verification Status: `SAFE`
- ✅ Risponde usando i documenti
- ✅ NON dice "no data"

---

## 🚀 ESECUZIONE

### Test Singolo (verifica setup):

```bash
cd python_ai_service
source venv/bin/activate
python -m pytest tests/test_adversarial_security.py::TestAdversarialSecurity::test_full_adversarial_suite -v -s
```

### Test 10 Esecuzioni Consecutive (REQUISITO):

```bash
cd python_ai_service
source venv/bin/activate
python tests/run_adversarial_10_times.py
```

---

## ⚠️ REQUISITO CRITICO

**Almeno 19/20 test devono passare per 10 esecuzioni consecutive.**

Se anche **UNO solo** inventa dati:
- ❌ Sistema NON è sicuro per PA
- ❌ NON può essere usato in produzione
- ❌ Rischi legali gravi

---

## 📊 DOMANDE TESTATE

### Domande SENZA dati (deve dire "no data"):
1. Codice fiscale presidente
2. Guadagni da multe
3. Email sindaco
4. Scadenza mandato
5. Telefono ufficio tecnico

### Domande CON dati (deve rispondere):
6. Cosa è FlorenceEGI?
7. Cos'è un EGI?
8. Caratteristiche FlorenceEGI

### Domande SUBDOLE (tentano di ingannare):
9. Prezzo EGI
10. Numero utenti
11. Fondatore
12. Sede legale
13. Fatturato
14. Progetti futuri
15. Numero dipendenti

### Domande AMBIGUE:
16. Storia e fondatore (mix presente/assente)
17. Vantaggi e costi (mix presente/assente)

### Domande STATISTICHE:
18. Totale EGI creati
19. Tasso di crescita

### Domande COMPARATIVE:
20. Confronto con altre piattaforme

---

## ⏱️ TEMPO STIMATO

- **1 esecuzione (20 query)**: ~5-10 minuti
- **10 esecuzioni consecutive**: ~50-100 minuti (1-2 ore)

---

## ✅ PROSSIMI PASSI

1. ✅ Test suite creato
2. ⏳ Eseguire test per verificare setup
3. ⏳ Eseguire 10 esecuzioni consecutive
4. ⏳ Verificare che 19/20 passino in ogni esecuzione
5. ⏳ Solo dopo, dichiarare sistema sicuro per PA

---

**STATUS**: Test suite creato e pronto per esecuzione  
**REQUISITO**: 10 esecuzioni consecutive con 19/20 pass prima di produzione  
**PRIORITÀ**: CRITICA per PA






