# Testing Strategy - NATAN_LOC

## Test Types

### 1. Unit Tests (`test_use_pipeline_logic.py`)
**Purpose**: Verifica la logica del codice con mock
- ✅ Testa tutte le condizioni critiche
- ✅ Verifica invarianti logiche
- ✅ Veloce (no I/O)

**Limitation**: Non testa comportamento con documenti reali

### 2. Integration Tests (`test_integration_real_documents.py`)
**Purpose**: Verifica comportamento con MongoDB reale
- ✅ Verifica che MongoDB contenga documenti
- ✅ Verifica che retriever trovi chunks
- ✅ Documenta stato del sistema

**Use**: Diagnosi problemi di configurazione

### 3. Debug Tests (`test_debug_pipeline_real_query.py`)
**Purpose**: Debug step-by-step del pipeline
- ✅ Mostra ogni step del processo
- ✅ Identifica dove il pipeline si rompe
- ✅ Log dettagliato per troubleshooting

**Use**: Quando il sistema non funziona, esegui questo per capire dove si rompe

### 4. End-to-End Tests (`test_end_to_end_florenceegi.py`)
**Purpose**: Test completo con query reale
- ✅ Verifica tutto il flusso
- ✅ Query reale su FlorenceEGI
- ✅ Assertions critiche

**Use**: Verifica che tutto funzioni prima di deploy

## Esecuzione Test

```bash
# Tutti i test
pytest tests/ -v

# Solo unit test (veloci)
pytest tests/test_use_pipeline_logic.py -v

# Debug quando qualcosa non funziona
pytest tests/test_debug_pipeline_real_query.py -v -s

# End-to-end (verifica tutto funziona)
pytest tests/test_end_to_end_florenceegi.py -v -s
```

## Problemi Comuni

### "No chunks found"
- MongoDB vuoto → Eseguire scrapers
- Embeddings non generati → Rigenerare embeddings
- tenant_id mismatch → Verificare tenant_id

### "Answer says 'no data' but has verified claims"
- Bug fixed in code → Verificare che fix sia applicato
- Servizio Python non riavviato → Riavviare servizio

### "All claims blocked"
- Chunks non rilevanti → Verificare similarity threshold
- Claims senza sourceRefs → Verificare claim generation






