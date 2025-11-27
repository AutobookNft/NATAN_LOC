# 📦 PROCEDURA IMPORT DOCUMENTI - NATAN_LOC

Guida completa per pulizia DB e re-import documenti con PDF, testo completo, struttura e embeddings.

---

## 🎯 **OBIETTIVO**

Importare documenti PA con:
- ✅ PDF scaricati e URL salvati
- ✅ Testo completo estratto (>1000 caratteri medi)
- ✅ Struttura identificata (sezioni logiche)
- ✅ Chunking intelligente
- ✅ Embeddings generati
- ✅ Multi-tenant isolation

---

## 📋 **PROCEDURA COMPLETA**

### **STEP 1: TEST CHAT (ORA)** ✅

Prima di fare cleanup, testa la chat attuale:
- Verifica link blu e sottolineati
- Verifica tabelle formattate
- Verifica sezione FONTI spaziata
- Cronometra tempi di risposta

---

### **STEP 2: PULIZIA DB** 🧹

**Quando sei pronto per cleanup:**

```bash
cd /home/fabio/NATAN_LOC/scripts
./cleanup_mongodb_documents.sh
```

**Cosa fa:**
- ❓ Chiede doppia conferma
- 📊 Mostra conteggio documenti attuali
- 🗑️ Elimina tutti i documenti dalla collection `documents`
- ✅ Conferma eliminazione completata
- ⚠️ Avvisa di ricreare Vector Index se necessario

**IMPORTANTE:** Se fai cleanup con collection drop (invece di deleteMany), devi **ricreare il Vector Index** su Atlas!

---

### **STEP 3: IMPORT TEST (100 documenti)** 🧪

**Import di test per verificare qualità:**

```bash
cd /home/fabio/NATAN_LOC/scripts
./import_test_100_docs.sh
```

**Cosa fa:**
- 📥 Importa ~100 documenti (anno 2024, DG + DD)
- 📄 Scarica PDF
- 📝 Estrae testo completo
- 🧩 Fa chunking intelligente
- 🔢 Genera embeddings
- 🏗️ Identifica struttura
- 📊 Mostra statistiche import
- 🔍 Verifica sample documenti

**Tempo stimato:** 10-30 minuti  
**Costo OpenAI:** ~$1-3

---

### **STEP 4: VERIFICA QUALITÀ** ✅

**Dopo import test, verifica qualità:**

```bash
cd /home/fabio/NATAN_LOC/scripts
./verify_import_quality.sh 2  # 2 = tenant_id
```

**Cosa verifica:**
1. ✅ Conteggio documenti
2. ✅ % documenti con PDF URL (target: >90%)
3. ✅ Lunghezza media testo (target: >1000 caratteri)
4. ✅ % documenti con struttura
5. ✅ Chunks per documento (target: >1)
6. ✅ % documenti con embeddings (target: 100%)
7. ✅ Sample 3 documenti con dettagli

**Checklist qualità:**
- [ ] Tutti i documenti hanno PDF URL (o >90%)
- [ ] Lunghezza media > 1000 caratteri
- [ ] Documenti <500 caratteri < 10%
- [ ] Tutti hanno almeno 1 chunk
- [ ] Tutti hanno embedding
- [ ] Sample hanno dati completi

---

### **STEP 5: TEST CHAT CON NUOVI DATI** 🧪

**Testa la chat con i documenti importati:**

1. **Verifica Vector Index su Atlas:**
   - Vai su MongoDB Atlas → Search
   - Verifica che `vector_index` sia **ACTIVE** 🟢
   - Se non esiste o è stato rimosso, ricrealo (vedi documentazione)

2. **Fai una query di test:**
   ```
   "Trova tutti i progetti di riqualificazione stradale del 2024"
   ```

3. **Verifica performance:**
   - Tempo risposta: **20-30 secondi** (con vector index)
   - Se >60s, vector index non è attivo

4. **Apri vista documento:**
   - Click su un link documento nella risposta
   - Verifica:
     - [ ] Testo completo visibile (>1000 caratteri)
     - [ ] Sezioni strutturate presenti
     - [ ] PDF viewer funzionante
     - [ ] Nessun warning "PDF mancante"

---

### **STEP 6: IMPORT COMPLETO PRODUZIONE** 🚀

**SE tutto OK con import test → Import completo:**

```bash
cd /home/fabio/NATAN_LOC/scripts
./import_full_production.sh
```

**Cosa fa:**
- 📥 Importa TUTTI gli atti 2018-2025
- 📄 Tutti i tipi: DG, DC, DD, DS, OD
- 📊 Documenti stimati: 5000-10000
- 📄 Scarica tutti i PDF
- 📝 Estrae tutto il testo
- 🧩 Chunking completo
- 🔢 Embeddings per tutti
- 🏗️ Struttura per tutti

**Tempo stimato:** 2-6 ore  
**Costo OpenAI:** $20-50  
**Spazio disco:** ~2-5 GB PDF

**IMPORTANTE:**
- ⚠️ Non interrompere lo script durante l'esecuzione
- ⚠️ Assicurati di avere credito API OpenAI sufficiente
- ⚠️ Verifica spazio disco disponibile
- ⚠️ Script può essere ripreso se interrotto (deduplica automatica)

---

### **STEP 7: VERIFICA FINALE** ✅

**Dopo import completo:**

```bash
./verify_import_quality.sh 2
```

**Test finale chat:**
- Query complesse con più progetti
- Verifica tempi <30 secondi
- Verifica fonti complete e cliccabili
- Verifica vista documenti completa

---

## 🔧 **TROUBLESHOOTING**

### **Import fallisce con "OpenAI API error"**

**Soluzione:**
```bash
# Verifica API key
docker exec -it natan_python_fastapi env | grep OPENAI_API_KEY

# Riavvia container se necessario
docker restart natan_python_fastapi
```

### **PDF non vengono scaricati**

**Soluzione:**
- Verifica connessione internet
- Alcuni PDF potrebbero richiedere autenticazione
- Script continua anche se alcuni PDF falliscono

### **Vector Index non funziona**

**Soluzione:**
1. Vai su MongoDB Atlas → Search
2. Verifica che `vector_index` sia **ACTIVE**
3. Se manca, ricrealo:
   - Index name: `vector_index`
   - Collection: `documents`
   - Database: `natan_ai_core`
   - Configuration: (vedi documentazione separata)

### **Documenti duplicati**

**Soluzione:**
```bash
# Lo scraper ha deduplica automatica
# Ma se vuoi forzare cleanup duplicati:
docker exec -it natan_mongo mongosh --eval "
use natan_ai_core
db.documents.aggregate([
  {\$group: {_id: '\$document_id', count: {\$sum: 1}, ids: {\$push: '\$_id'}}},
  {\$match: {count: {\$gt: 1}}}
]).forEach(function(doc) {
  doc.ids.shift(); // Keep first
  db.documents.deleteMany({_id: {\$in: doc.ids}});
})
"
```

---

## 📊 **METRICHE TARGET**

| **Metrica** | **Target** | **Come Verificare** |
|-------------|-----------|-------------------|
| Documenti totali | 5000-10000 | `verify_import_quality.sh` |
| PDF URL presente | >90% | Script verifica automatica |
| Lunghezza media | >1000 caratteri | Script verifica automatica |
| Chunks per doc | >1 | Script verifica automatica |
| Embeddings | 100% | Script verifica automatica |
| Tempo risposta chat | <30s | Test manuale query |
| Vista documento OK | 100% | Test manuale sample |

---

## 🎯 **CHECKLIST FINALE**

Prima di considerare import completo:

- [ ] Import test 100 docs completato con successo
- [ ] Verifica qualità: tutti i check verdi
- [ ] Vector Index su Atlas: ACTIVE
- [ ] Test chat: tempi <30s
- [ ] Vista documento: PDF + testo + struttura visibili
- [ ] Import completo eseguito
- [ ] Verifica finale: metriche target raggiunte
- [ ] Performance stabile per 24h

---

## 📞 **SUPPORTO**

In caso di problemi:
1. Controlla log: `docker logs natan_python_fastapi --tail 100`
2. Verifica MongoDB: `docker exec -it natan_mongo mongosh`
3. Consulta documentazione RAG-Fortress
4. Verifica connessioni API esterne

---

**Creato il:** 2025-11-21  
**Autore:** Padmin D. Curtis (AI Partner OS3.0)  
**Progetto:** NATAN_LOC - RAG-Fortress Zero-Hallucination Pipeline

