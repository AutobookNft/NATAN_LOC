# 🕷️ Scraper Albo Pretorio Firenze - MongoDB Integration

Gli scraper per l'Albo Pretorio del Comune di Firenze sono stati modificati per supportare l'import diretto in **MongoDB** per NATAN_LOC.

## 📋 Scraper Disponibili

### 1. `scrape_albo_firenze_v2.py`

**Tipo:** HTML scraping  
**Sorgente:** Albo Pretorio online (pagina HTML)  
**Descrizione:** Estrae atti dall'albo pretorio navigando le pagine HTML

**Uso base:**

```bash
cd /home/fabio/NATAN_LOC
python3 scripts/scrape_albo_firenze_v2.py --mongodb --tenant-id 1 --max-pages 5
```

**Uso completo:**

```bash
python3 scripts/scrape_albo_firenze_v2.py \
  --mongodb \
  --tenant-id 1 \
  --max-pages 10 \
  --download-pdfs \
  --output-dir storage/testing/firenze_atti
```

---

### 2. `scrape_firenze_deliberazioni.py`

**Tipo:** API scraping  
**Sorgente:** API Trasparenza Amministrativa  
**Descrizione:** Estrae deliberazioni, determinazioni e altri atti tramite API JSON

**Uso base:**

```bash
cd /home/fabio/NATAN_LOC
python3 scripts/scrape_firenze_deliberazioni.py --mongodb --tenant-id 1 --anno-inizio 2024 --anno-fine 2024
```

**Uso completo:**

```bash
python3 scripts/scrape_firenze_deliberazioni.py \
  --mongodb \
  --tenant-id 1 \
  --anno-inizio 2024 \
  --anno-fine 2025 \
  --download-pdfs \
  --tipi DG DC DD \
  --output-dir storage/testing/firenze_atti_completi
```

---

## 🔧 Parametri MongoDB

### `--mongodb`

**Obbligatorio per import MongoDB**  
Abilita l'import diretto in MongoDB usando `PAActMongoDBImporter`.

### `--tenant-id <ID>`

**Default:** `1` (Florence EGI)  
Specifica il tenant ID per l'isolamento multi-tenant in MongoDB.

**Esempi:**

- `--tenant-id 1` → Florence EGI (default)
- `--tenant-id 2` → Altro tenant

---

## 📊 Cosa Fa l'Import MongoDB

Quando usi `--mongodb`, ogni atto viene:

1. ✅ **Estratto** dall'albo pretorio/API
2. ✅ **Processato** con `PAActMongoDBImporter`:
   - Estrazione testo da PDF (se disponibile)
   - Chunking intelligente del testo
   - Generazione embeddings per ricerca semantica
   - Salvataggio in MongoDB collection `documents`
3. ✅ **Tracciato** con statistiche e costi embeddings
4. ✅ **Isolato** per tenant (`tenant_id` nel documento)

**Metadata salvati:**

- `document_type`: `"pa_act"`
- `tenant_id`: ID tenant specificato
- `title`: Oggetto atto
- `protocol_number`: Numero protocollo
- `protocol_date`: Data atto
- `metadata.source`: `"pa_scraper"`
- `metadata.scraper_type`: Tipo scraper usato
- `content.chunks`: Array chunks con embeddings
- `embedding`: Embedding a livello documento

---

## 💾 Backup JSON

Anche con `--mongodb`, gli scraper salvano sempre un **backup JSON** in `output_dir/json/` per sicurezza.

---

## 📈 Statistiche e Costi

A fine esecuzione, se usi `--mongodb`, vedrai:

```
📊 Statistiche MongoDB:
   ✅ Importati: 42
   ⚠️  Saltati: 3
   ❌ Errori: 0
   📄 Totale chunks: 127

💰 Costi embeddings:
   Modello: openai.text-embedding-3-small
   Tokens: 45,230
   Costo: €0.0008
```

---

## ⚠️ Note Importanti

### 1. **Tenant Isolation**

**CRITICO:** I documenti in MongoDB DEVONO avere `tenant_id` corretto.  
Gli scraper lo impostano automaticamente dal parametro `--tenant-id`.

### 2. **PDF Download**

Usa `--download-pdfs` solo se necessario:

- ✅ Consente estrazione testo completo
- ⚠️ Richiede spazio disco
- ⏱️ Aumenta tempo esecuzione

### 3. **Rate Limiting**

Gli scraper rispettano pause tra richieste:

- Albo Pretorio: 2 secondi tra pagine
- API Deliberazioni: 1 secondo tra richieste

**Non modificare** questi tempi per evitare blocchi IP.

---

## 🚀 Esempi Pratici

### Import rapido (test)

```bash
# Solo ultime 2 pagine albo pretorio
python3 scripts/scrape_albo_firenze_v2.py \
  --mongodb \
  --tenant-id 1 \
  --max-pages 2
```

### Import completo 2024

```bash
# Tutti gli atti 2024, con PDF
python3 scripts/scrape_firenze_deliberazioni.py \
  --mongodb \
  --tenant-id 1 \
  --anno-inizio 2024 \
  --anno-fine 2024 \
  --download-pdfs
```

### Import multi-tenant

```bash
# Tenant 1 (Florence EGI)
python3 scripts/scrape_firenze_deliberazioni.py --mongodb --tenant-id 1 --anno-inizio 2024 --anno-fine 2024

# Tenant 2 (altro tenant)
python3 scripts/scrape_firenze_deliberazioni.py --mongodb --tenant-id 2 --anno-inizio 2024 --anno-fine 2024
```

---

## 🔍 Verifica Import

Dopo l'import, verifica i documenti in MongoDB:

```bash
cd /home/fabio/NATAN_LOC
docker exec natan_mongodb mongosh -u natan_user -p secret_password --authenticationDatabase admin natan_ai_core --eval "
  db.documents.countDocuments({tenant_id: 1, document_type: 'pa_act'})
"
```

---

## 📚 Integrazione con USE Pipeline

I documenti importati sono immediatamente disponibili per:

- ✅ **USE Pipeline** (`UsePipeline.process_query()`)
- ✅ **RAG Search** (ricerca semantica con embeddings)
- ✅ **Question Classification** (analisi query utente)
- ✅ **Claim Verification** (verifica claims con source references)

**Query esempio:**

```
"Quante deliberazioni di giunta ci sono nel 2024?"
```

Il USE Pipeline troverà automaticamente i documenti importati tramite:

- Similarity search su embeddings
- Filtering per `tenant_id`
- Filtering per `document_type: "pa_act"`

---

## 🛠️ Troubleshooting

### Errore: "MongoDB importer initialization failed"

**Causa:** `PAActMongoDBImporter` non trovato o errore connessione MongoDB

**Soluzione:**

1. Verifica MongoDB attivo: `docker ps | grep mongodb`
2. Verifica path Python: `python3 -c "import sys; print(sys.path)"`
3. Verifica `.env` file in `python_ai_service/`

### Errore: "No active transaction"

**Causa:** Nessun problema, è normale (MongoDB non usa transazioni)

### PDF non estratti

**Causa:** PyPDF2 o pdfplumber non installati

**Soluzione:**

```bash
cd python_ai_service
source venv/bin/activate  # Se usi venv
pip install PyPDF2
```

---

## 📝 Note Legali

⚠️ **IMPORTANTE:** Gli albi pretori sono pubblici per legge (D.Lgs. 33/2013 - Trasparenza Amministrativa).  
Lo scraping è legale se:

- ✅ Rispetti rate limiting (pause tra richieste)
- ✅ Non sovraccarichi il server
- ✅ Usi i dati solo per scopi legittimi
- ✅ Rispetti robots.txt (se presente)

---

**Versione:** 1.0.0  
**Data:** 2025-01-31  
**Autore:** Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
