# 🧪 Test Suite - Scraper Albo Pretorio Firenze

Suite completa di unit test per verificare il corretto funzionamento degli scraper modificati per MongoDB.

## 📋 Test Disponibili

### `test_scrape_albo_firenze_v2.py`

Test per lo scraper HTML dell'Albo Pretorio:

- ✅ **Inizializzazione**: JSON-only vs MongoDB
- ✅ **Parsing HTML**: Estrazione dati da pagine HTML
- ✅ **Parsing atti**: Validazione struttura dati estratti
- ✅ **Paginazione**: Estrazione numero totale pagine
- ✅ **MongoDB Integration**: Import atti in MongoDB
- ✅ **Tenant Isolation**: Verifica isolamento multi-tenant
- ✅ **Error Handling**: Gestione errori e fallback
- ✅ **Data Transformation**: Mapping corretto dati -> MongoDB

**Numero test**: ~20 test cases

---

### `test_scrape_firenze_deliberazioni.py`

Test per lo scraper API delle Deliberazioni:

- ✅ **Inizializzazione**: JSON-only vs MongoDB
- ✅ **API Calls**: Chiamate API corrette
- ✅ **Payload Structure**: Verifica struttura payload HTTP
- ✅ **Parsing JSON**: Estrazione dati da risposta API
- ✅ **MongoDB Integration**: Import atti in MongoDB
- ✅ **Tenant Isolation**: Verifica isolamento multi-tenant
- ✅ **PDF Extraction**: Estrazione URL PDF da allegati
- ✅ **Error Handling**: Gestione errori API e MongoDB
- ✅ **Data Transformation**: Mapping corretto API -> MongoDB

**Numero test**: ~20 test cases

---

## 🚀 Esecuzione

### Metodo 1: Script Bash (Raccomandato)

```bash
cd /home/fabio/NATAN_LOC
./scripts/tests/run_tests.sh
```

### Metodo 2: pytest Diretto

```bash
cd /home/fabio/NATAN_LOC
python3 -m pytest scripts/tests/ -v
```

### Metodo 3: Test Specifico

```bash
# Solo test scraper albo pretorio
python3 -m pytest scripts/tests/test_scrape_albo_firenze_v2.py -v

# Solo test scraper deliberazioni
python3 -m pytest scripts/tests/test_scrape_firenze_deliberazioni.py -v

# Test specifico
python3 -m pytest scripts/tests/test_scrape_albo_firenze_v2.py::TestAlboPretorioFirenze::test_parse_atto_valid -v
```

---

## 📦 Requisiti

I test usano:

- **pytest**: Framework testing
- **pytest-asyncio**: Supporto async/await
- **pytest-mock**: Mocking per isolare test
- **beautifulsoup4**: Parsing HTML (per test)

**Installazione automatica**: Lo script `run_tests.sh` installa automaticamente le dipendenze.

**Installazione manuale**:

```bash
pip3 install pytest pytest-asyncio pytest-mock beautifulsoup4
```

---

## ✅ Cosa Testano i Test

### 1. **Parsing Corretto**

I test verificano che:

- HTML/JSON vengano parsati correttamente
- Tutti i campi siano estratti (numero_atto, tipo_atto, oggetto, etc.)
- Campi mancanti siano gestiti (non crashano)

### 2. **MongoDB Integration**

I test verificano che:

- `PAActMongoDBImporter` venga chiamato correttamente
- Mapping dati sia corretto (campi HTML/API -> MongoDB)
- `tenant_id` sia passato correttamente
- Errori MongoDB siano gestiti

### 3. **Tenant Isolation**

I test verificano che:

- Ogni scraper usa il `tenant_id` corretto
- Documenti siano isolati per tenant

### 4. **Error Handling**

I test verificano che:

- Errori network siano gestiti
- Errori MongoDB siano gestiti
- Fallback a JSON-only quando MongoDB non disponibile

### 5. **Data Transformation**

I test verificano che:

- Mapping HTML -> MongoDB sia corretto
- Mapping API -> MongoDB sia corretto
- Campi opzionali siano gestiti

---

## 📊 Esempio Output

```
==============================================================================
🧪 TEST SUITE - Scraper Albo Pretorio Firenze
==============================================================================

🚀 Esecuzione test...

tests/test_scrape_albo_firenze_v2.py::TestAlboPretorioFirenze::test_init_json_only PASSED
tests/test_scrape_albo_firenze_v2.py::TestAlboPretorioFirenze::test_init_with_mongodb PASSED
tests/test_scrape_albo_firenze_v2.py::TestAlboPretorioFirenze::test_parse_atto_valid PASSED
tests/test_scrape_albo_firenze_v2.py::TestAlboPretorioFirenze::test_parse_atto_multiple PASSED
...
tests/test_scrape_firenze_deliberazioni.py::TestFirenzeAttiScraper::test_init_json_only PASSED
tests/test_scrape_firenze_deliberazioni.py::TestFirenzeAttiScraper::test_search_atti_success PASSED
...

==============================================================================
✅ Test completati!
==============================================================================

========== 40 passed in 2.34s ==========
```

---

## 🔍 Debug Test Falliti

Se un test fallisce:

1. **Leggi il messaggio di errore**:

   ```bash
   python3 -m pytest scripts/tests/test_scrape_albo_firenze_v2.py::TestAlboPretorioFirenze::test_parse_atto_valid -v -s
   ```

2. **Verifica mock**: I test usano mock, verifica che siano configurati correttamente

3. **Esegui test isolato**: Esegui solo il test che fallisce per vedere l'errore completo

4. **Controlla fixtures**: Verifica che le fixtures (sample HTML, API responses) siano corrette

---

## ⚠️ Note Importanti

### Mock vs Real

I test usano **mock** per:

- ✅ Network calls (non fanno richieste HTTP reali)
- ✅ MongoDB calls (non modificano MongoDB reale)
- ✅ File system (usano `/tmp` temporaneo)

**Vantaggi**:

- ✅ Veloce (no I/O)
- ✅ Isolato (non dipende da servizi esterni)
- ✅ Riproducibile (stesso risultato sempre)

**Limiti**:

- ⚠️ Non testa connessione MongoDB reale
- ⚠️ Non testa chiamate HTTP reali
- ⚠️ Non testa parsing HTML reale del sito

### Test Integration

Per testare con MongoDB reale, crea test separati di integrazione:

```python
# test_integration_real_mongodb.py
@pytest.mark.integration
async def test_real_mongodb_import():
    # Test con MongoDB reale
    ...
```

Esegui con:

```bash
pytest scripts/tests/ -m integration
```

---

## 📝 Aggiungere Nuovi Test

Per aggiungere nuovi test:

1. **Identifica cosa testare**: Nuova funzionalità o edge case

2. **Aggiungi test nella classe appropriata**:

   ```python
   def test_my_new_feature(self, scraper_json_only):
       """Test descrizione"""
       # Arrange
       input_data = ...

       # Act
       result = scraper_json_only.my_new_method(input_data)

       # Assert
       assert result == expected
   ```

3. **Esegui il test**:

   ```bash
   python3 -m pytest scripts/tests/test_scrape_albo_firenze_v2.py::TestAlboPretorioFirenze::test_my_new_feature -v
   ```

4. **Verifica che passi**: ✅

---

## 🎯 Coverage Obiettivo

**Obiettivo**: >80% coverage del codice degli scraper

Verifica coverage:

```bash
python3 -m pytest scripts/tests/ --cov=scrape_albo_firenze_v2 --cov=scrape_firenze_deliberazioni --cov-report=html
```

Apri `htmlcov/index.html` per vedere coverage dettagliato.

---

**Versione**: 1.0.0  
**Data**: 2025-01-31  
**Autore**: Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
