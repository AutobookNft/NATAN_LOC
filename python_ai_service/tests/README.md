# NATAN_LOC Test Suite

## Overview

Suite di test completa per verificare la logica del USE Pipeline e garantire coerenza logica.

## Struttura Test

### `test_use_pipeline_logic.py`

Test unitari che verificano tutte le condizioni critiche:

1. **TEST 1**: No chunks → `no_results` con claims vuoti
2. **TEST 2**: Chunks vuoti/placeholder → `no_results`
3. **TEST 3**: Chunks validi + claims verificati → `success` con claims
4. **TEST 4**: Answer "no data" + claims verificati → ricostruisce answer dai claims
5. **TEST 5**: Nessun claim generato → `no_results`
6. **TEST 6**: Tutti i claims bloccati → `no_results`, claims bloccati mai esposti
7. **TEST 7**: Post-verification fallisce → `no_results`
8. **TEST 8**: Claims bloccati MAI esposti (requisito sicurezza)
9. **TEST 9**: Summary regole logiche

## Esecuzione Test

```bash
# Tutti i test
pytest tests/ -v

# Test specifico
pytest tests/test_use_pipeline_logic.py::TestUsePipelineLogic::test_answer_says_no_data_but_has_verified_claims_reconstructs_answer -v -s

# Con coverage
pytest tests/ --cov=app.services.use_pipeline --cov-report=html

# Test veloci (senza I/O)
pytest tests/ -v -m "not slow"
```

## Regole Logiche (Devono essere sempre vere)

1. **No chunks** → `no_results` con claims vuoti
2. **Chunks vuoti/placeholder** → `no_results`
3. **Chunks validi + verified claims** → `success` con claims
4. **Answer "no data" + verified claims** → ricostruisce answer dai claims
5. **No claims generati** → `no_results`
6. **Tutti claims bloccati** → `no_results`, claims bloccati mai esposti
7. **Post-verification fallisce** → `no_results`
8. **Blocked claims MAI esposti** (requisito sicurezza)
9. **verified_claims vuoto** → status deve essere `no_results`
10. **status success** → verified_claims non deve essere vuoto (tranne conversational)

## Requisiti

```bash
pip install pytest pytest-asyncio pytest-cov
```






