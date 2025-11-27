# 🏰 RAG-Fortress Zero-Hallucination - Implementazione Completata

**Versione**: 2.0.0  
**Data**: 2025-11-26  
**Progetto**: NATAN_LOC - python_ai_service  
**Status**: ✅ **VERSIONE 2.0 - QUALITÀ ISTITUZIONALE PA**

---

## 🆕 Novità Versione 2.0 (26 Nov 2025)

### **Sistema Semaforo Affidabilità Dati**
Ogni risposta include marcatura visiva per trasparenza totale:
- 🟢 **VERIFICATO** = Dato presente nei documenti ufficiali
- 🟠 **STIMATO** = Elaborazione/stima basata su dati parziali
- 🔴 **PROPOSTA AI** = Suggerimento del sistema, NON presente nei documenti

### **Header Metodologico Obbligatorio**
Ogni report inizia con nota metodologica:
```markdown
## ⚠️ NOTA METODOLOGICA - Limiti del Report

| Metrica | Valore |
|---------|--------|
| **Atti analizzati** | 50 su 1199 disponibili (4.2%) |
| **Periodo coperto** | 2024-04 - 2025-11 |
| **Tipologie incluse** | Manutenzioni, Progetti PNRR, Accordi quadro |
| **Categorie patrimonio coperte** | Viabilità e Ponti, Edilizia Scolastica, Impianti Sportivi |
| **⚠️ Categorie NON presenti** | Verde Pubblico, Illuminazione Pubblica, Cimiteri... |

> 🔴 **COPERTURA LIMITATA**: Analizzato solo il 4.2% degli atti. I dati potrebbero non rappresentare il quadro completo.

> ⚡ **AVVERTENZA IMPORTANTE**:  
> Questo report analizza un **campione** degli atti disponibili.  
> NON rappresenta necessariamente l'intero patrimonio comunale.  
> Categorie come "Verde Pubblico" potrebbero non essere coperte.  
> NON sostituisce la progettazione tecnica e la validazione degli uffici competenti.
```

### **Rilevamento Automatico Categorie Patrimonio**
Il sistema identifica automaticamente quali categorie del patrimonio comunale sono coperte dai documenti analizzati e quali **mancano**:

**Categorie tracciate:**
- Viabilità e Ponti
- Edilizia Scolastica
- Impianti Sportivi
- Verde Pubblico
- Illuminazione Pubblica
- Cimiteri
- Edifici Comunali
- Mercati
- Patrimonio Culturale

**Indicatori copertura:**
- 🔴 **COPERTURA LIMITATA** (< 10%): Warning rosso
- 🟠 **COPERTURA PARZIALE** (10-30%): Warning arancione
- Nessun warning se copertura > 30%

### **Separazione FATTI vs PROPOSTE**
Struttura obbligatoria in due parti:
- **PARTE A: STATO ATTUALE** (solo fatti 🟢/🟠)
- **PARTE B: PROPOSTE E RACCOMANDAZIONI** (solo 🔴)

### **Citazioni Precise**
Sostituzione riferimenti generici con citazioni PA:
- ❌ Prima: `[DOC 1-10]`
- ✅ Dopo: `[Prot. 00457 - Accordo Quadro Manutenzione Guard Rail]`

### **Numeri Rossi come Range**
Mai valori puntuali per proposte AI:
- ❌ Prima: `🔴 €200.000`
- ✅ Dopo: `🔴 €150.000-250.000 (stima indicativa, da validare)`

### **Tono Condizionale per Proposte**
Linguaggio prudente obbligatorio per proposte 🔴:
- ✅ "Si potrebbe valutare...", "Sarebbe opportuno considerare..."
- ✅ "Una possibile soluzione potrebbe essere..."
- ❌ MAI: "Bisogna fare...", "È necessario...", "Occorre implementare..."

### **Processing Notice con Stima Tempo**
Endpoint `/estimate` per pre-flight check:
- Mostra tempo stimato prima dell'elaborazione
- Esempio: "Dai 1199 documenti, estraggo i 100 più simili → elaboro i 50 più rilevanti"

### **Export Buttons**
Nuovi pulsanti per esportazione:
- 📋 Copia negli appunti
- 📄 Esporta HTML professionale
- 📊 Esporta Excel/CSV

---

## ✅ Tutti i 10 Passi Completati

### **PASSO 0**: Struttura Base ✅
- Cartella `rag_fortress` creata
- Tutti i file base creati con `__init__.py`
- Modelli Pydantic definiti

### **PASSO 1**: Retriever ✅
- Hybrid search MongoDB Atlas implementato
- Vector search + text search combinati
- Reranking con bge-reranker/Cohere
- Filtro relevance_score > 8.8
- Multi-tenant support

### **PASSO 2**: Evidence Verifier ✅
- Verifica rigorosa evidenze con Claude-3.5-Sonnet
- JSON mode per output strutturato
- Score di rilevanza 0-10
- Estrazione exact_quote

### **PASSO 3**: Claim Extractor ✅
- Estrazione claim atomiche
- Formato [CLAIM_XXX] rigoroso
- Supporto Llama-3.1-70B/Grok-4
- Anti-allucinazione core

### **PASSO 4**: Gap Detector ✅
- Rilevamento gap di copertura
- Claude-3.5-Sonnet per massimo rigore
- Output formato GAP_XX

### **PASSO 5**: Constrained Synthesizer ✅
- Sintesi vincolata alle claim
- Stile burocratico italiano perfetto
- Citazioni obbligatorie (CLAIM_XXX)
- Max 450 parole

### **PASSO 6**: Hostile Fact-Checker ✅
- Verifica ostile con modello diverso
- Gemini-1.5-Flash/Llama-3.1-405B
- Rilevamento allucinazioni estremo

### **PASSO 7**: URS Calculator ✅
- Calcolo Ultra Reliability Score 0-100
- Formula completa con penalità/bonus
- Spiegazione dettagliata

### **PASSO 8**: Pipeline Orchestrator ✅
- Coordinamento completo 6 step
- Gestione errori robusta
- Rifiuto risposta se URS < 90

### **PASSO 9**: Integrazione Chat Router ✅
- Integrato in `routers/chat.py`
- Response model esteso con metadata
- Fallback a metodo tradizionale

---

## 📁 Struttura File Creata

```
python_ai_service/app/services/rag_fortress/
├── __init__.py                    ✅
├── models.py                      ✅
├── retriever.py                   ✅ PASSO 1
├── evidence_verifier.py           ✅ PASSO 2
├── claim_extractor.py             ✅ PASSO 3
├── gap_detector.py                ✅ PASSO 4
├── constrained_synthesizer.py     ✅ PASSO 5
├── hostile_factchecker.py         ✅ PASSO 6
├── urs_calculator.py              ✅ PASSO 7
└── pipeline.py                    ✅ PASSO 8
```

---

## 🔧 Configurazione Necessaria

### **MongoDB Atlas**
- Index `vector_index` su campo `embedding`
- Collection `documents` con struttura:
  ```json
  {
    "_id": ObjectId,
    "tenant_id": "string",
    "content": "string",
    "source": "string",
    "metadata": {},
    "embedding": [float, ...]
  }
  ```

### **Environment Variables**
- `OPENAI_API_KEY` - Per embeddings
- `ANTHROPIC_API_KEY` - Per Claude
- `MONGODB_URI` - Connection string Atlas

---

## 🚀 Utilizzo

### **API Endpoint**

```bash
POST /api/v1/chat
```

**Request:**
```json
{
  "messages": [
    {"role": "user", "content": "Qual è l'importo della delibera n. 123/2024?"}
  ],
  "tenant_id": 1,
  "use_rag_fortress": true
}
```

**Response:**
```json
{
  "message": "Risposta formale...",
  "model": "rag-fortress-pipeline",
  "urs_score": 95.0,
  "urs_explanation": "...",
  "claims": ["(CLAIM_001)", "(CLAIM_002)"],
  "sources": ["delibera_123_2024.pdf"],
  "hallucinations_found": [],
  "gaps_detected": []
}
```

---

## 🧪 Test

### **PASSO 10: Test Finale**

```bash
cd python_ai_service
source venv/bin/activate
uvicorn app.main:app --reload
```

**Test con curl:**
```bash
curl -X POST http://localhost:8001/api/v1/chat \
  -H "Content-Type: application/json" \
  -d '{
    "messages": [{"role": "user", "content": "Test domanda"}],
    "tenant_id": 1,
    "use_rag_fortress": true
  }'
```

---

## 📊 Pipeline Flow

```
1. Question → Retriever (100 chunk)
   ↓
2. Evidences → Evidence Verifier (score rilevanza)
   ↓
3. Verified Evidences → Claim Extractor ([CLAIM_XXX])
   ↓
4. Claims + Question → Gap Detector (GAP_XX)
   ↓
5. Claims + Gaps → Constrained Synthesizer (risposta)
   ↓
6. Response + Claims → Hostile Fact-Checker (allucinazioni)
   ↓
7. All → URS Calculator (score 0-100)
   ↓
8. Se URS < 90 → Rifiuta risposta
   ↓
9. Return Response con metadata completo
```

---

## ✅ Checklist Implementazione

- [x] ✅ Struttura cartella creata
- [x] ✅ Retriever implementato
- [x] ✅ Evidence Verifier implementato
- [x] ✅ Claim Extractor implementato
- [x] ✅ Gap Detector implementato
- [x] ✅ Constrained Synthesizer implementato
- [x] ✅ Hostile Fact-Checker implementato
- [x] ✅ URS Calculator implementato
- [x] ✅ Pipeline orchestrator implementato
- [x] ✅ Integrazione chat router completata
- [x] ✅ Test finale (PASSO 10)

### 🆕 Miglioramenti Qualità PA v2.0
- [x] ✅ Sistema semaforo 🟢/🟠/🔴
- [x] ✅ Header metodologico obbligatorio
- [x] ✅ Metriche copertura esplicite
- [x] ✅ Citazioni precise con protocollo
- [x] ✅ Separazione FATTI vs PROPOSTE
- [x] ✅ Numeri rossi come RANGE
- [x] ✅ Tono condizionale per proposte
- [x] ✅ Processing notice con stima tempo
- [x] ✅ Export buttons (HTML/Clipboard)
- [x] ✅ **Rilevamento categorie patrimonio coperte/mancanti**
- [x] ✅ **Warning copertura limitata (< 10% / < 30%)**
- [x] ✅ **Template Gold Standard sezione Ponti**
- [x] ✅ **Gestione graceful errori API (fondi esauriti, rate limit, etc.)**

---

## 🔧 Gestione Errori API - Messaggi User-Friendly

Il sistema gestisce gracefully gli errori dei servizi AI esterni mostrando messaggi comprensibili agli utenti.

### Tipi di Errore Gestiti

| Tipo | Codice | Messaggio Utente |
|------|--------|------------------|
| **Fondi Esauriti** | `insufficient_funds` | "Il credito API è esaurito. Contatta l'amministratore." |
| **API Key Invalida** | `invalid_api_key` | "Chiave API non valida. Contatta l'amministratore." |
| **Rate Limit** | `rate_limited` | "Troppe richieste. Attendi qualche secondo." |
| **Quota Superata** | `quota_exceeded` | "Quota giornaliera superata. Riprova domani." |
| **Servizio Non Disponibile** | `service_unavailable` | "Servizio AI non raggiungibile." |
| **Timeout** | `timeout` | "Richiesta troppo lunga. Prova domanda più breve." |

### Esempio Messaggio (Fondi Esauriti)

```markdown
⚠️ **Servizio AI temporaneamente non disponibile**

Il credito API per il servizio di intelligenza artificiale è esaurito.

**Cosa significa?**
Il sistema NATAN utilizza servizi AI esterni (Claude, GPT) che richiedono credito prepagato.

**Cosa puoi fare?**
- Attendi che l'amministratore ricarichi il credito
- Contatta il supporto tecnico per informazioni
- Prova più tardi

*I tuoi dati sono al sicuro. Il problema è solo nel servizio di generazione testi.*
```

### File Coinvolti

- `app/services/providers/api_errors.py` - Eccezioni custom e parsing
- `app/services/providers/anthropic_adapter.py` - Gestione errori Anthropic
- `app/services/rag_fortress/pipeline.py` - Intercettazione e risposta user-friendly

---

## 🏆 TEMPLATE GOLD STANDARD - Sezione "Ponti e Viabilità"

Questo template rappresenta l'**esempio di riferimento** per la redazione di sezioni tematiche.
Ogni sezione del report dovrebbe seguire questo formato per garantire qualità istituzionale.

---

### 📄 ESEMPIO COMPLETO - Analisi Infrastrutture Ponti

```markdown
---
## ⚠️ NOTA METODOLOGICA

| Metrica | Valore |
|---------|--------|
| **Atti analizzati** | 47 su 1199 disponibili (3.9%) |
| **Periodo coperto** | 2023-06 - 2025-11 |
| **Tipologie incluse** | Manutenzione straordinaria, PNRR, Accordi Quadro, Delibere |
| **Tipologie escluse** | Ordinaria amministrazione, Permessi ZTL, Eventi temporanei |

> ⚡ **AVVERTENZA**: Questo report è una sintesi AI a supporto decisionale.
> NON sostituisce la progettazione tecnica professionale e la validazione degli uffici competenti.
> I dati provengono da atti amministrativi pubblici del periodo indicato.

---

## 📊 LEGENDA AFFIDABILITÀ DATI

| Simbolo | Significato | Utilizzo |
|---------|-------------|----------|
| 🟢 **VERIFICATO** | Dato presente testualmente nei documenti | Può essere citato in atti ufficiali |
| 🟠 **STIMATO** | Elaborazione basata su dati parziali | Richiede verifica prima dell'uso |
| 🔴 **PROPOSTA AI** | Suggerimento algoritmico | Richiede validazione tecnica completa |

---

# PARTE A: STATO ATTUALE DOCUMENTATO
> *Questa sezione contiene SOLO fatti verificabili dai documenti analizzati*

## 1. Quadro Interventi Infrastrutturali in Corso

### 1.1 Ponti e Cavalcavia

| Opera | Intervento | Importo | Stato | Riferimento |
|-------|-----------|---------|-------|-------------|
| Ponte al Pino | 🟢 Consolidamento strutturale | 🟢 €1.200.000 | 🟢 In corso | [Prot. 18910/2025 - Determina affidamento lavori consolidamento Ponte al Pino] |
| Cavalcavia Affrico | 🟢 Sostituzione giunti | 🟢 €200.000 | 🟢 Completato | [Prot. 00594/2025 - Collaudo giunti cavalcavia Affrico] |
| Ponte Vespucci | 🟠 Ispezione programmata | 🟠 €45.000 (stima) | 🟢 Pianificato Q1 2026 | [Prot. 12450/2025 - Piano ispezioni ponti 2025-2026] |

**Note:**
- 🟢 Il Ponte al Pino è stato classificato con indice di degrado 3 su scala 1-5 [Prot. 14200/2024]
- 🟢 Sono presenti 12 ponti carrabili e 8 passerelle pedonali nel territorio comunale [Prot. 09800/2024 - Censimento opere d'arte]
- 🟠 Di questi, 4 ponti risultano con priorità intervento "alta" (stima da incrocio dati censimento)

### 1.2 Accordi Quadro Attivi

| Accordo | Oggetto | Importo Quadriennale | Operatore | Scadenza |
|---------|---------|---------------------|-----------|----------|
| AQ-001/2024 | 🟢 Manutenzione guard rail | 🟢 €500.000 | 🟢 Infrastrutture SpA | 🟢 31/12/2027 |
| AQ-003/2024 | 🟢 Segnaletica orizzontale | 🟢 €320.000 | 🟢 Segna Strade Srl | 🟢 30/06/2028 |
| AQ-007/2025 | 🟢 Interventi rapidi viabilità | 🟢 €180.000 | 🟢 Pronto Strada Coop | 🟢 31/12/2026 |

[Fonte: Prot. 00457/2024 - Accordo Quadro Manutenzione Guard Rail; Prot. 03210/2024 - Accordo Segnaletica; Prot. 01890/2025 - Accordo Pronto Intervento]

### 1.3 Criticità Documentate

1. **Ponte al Pino** (priorità ALTA)
   - 🟢 Rilevate fessurazioni piloni P2 e P3 [Prot. 14200/2024 - Relazione statica]
   - 🟢 Intervento di consolidamento già appaltato [Prot. 18910/2025]
   - 🟢 Limitazione carico temporanea: 3,5 tonnellate [Ordinanza 456/2024]

2. **Cavalcavia SS67** (priorità MEDIA)
   - 🟢 Degrado giunti di dilatazione documentato [Prot. 11340/2024]
   - 🟠 Intervento stimato in €150.000-180.000 (da verifica computo metrico)
   - 🟢 Inserito nel piano triennale LLPP 2025-2027 [Delibera 234/2024]

3. **Sottopasso Via Roma** (priorità BASSA)
   - 🟢 Infiltrazioni d'acqua segnalate [Prot. 08900/2025]
   - 🟢 Sopralluogo tecnico effettuato il 15/10/2025 [Verbale 892/2025]
   - 🟠 Costo stimato impermeabilizzazione: €80.000-120.000

---

# PARTE B: PROPOSTE E RACCOMANDAZIONI AI
> *Questa sezione contiene SOLO proposte algoritmiche che richiedono validazione tecnica*

## 2. Piano di Manutenzione Predittiva (Proposta)

### 2.1 Sistema di Monitoraggio IoT

🔴 **Proposta**: Si suggerisce di valutare l'implementazione di un sistema di monitoraggio strutturale IoT sui ponti classificati a priorità alta.

| Voce | Stima Indicativa | Note |
|------|-----------------|------|
| Sensori accelerometrici (x4 ponti) | 🔴 €60.000-90.000 | Da validare con offerte mercato |
| Piattaforma dati centralizzata | 🔴 €30.000-50.000 | Possibile integrazione con esistenti |
| Manutenzione annuale | 🔴 €15.000-25.000/anno | Stima da benchmark altri enti |
| **TOTALE INDICATIVO** | 🔴 **€105.000-165.000** | *Range ampio per incertezza* |

**Motivazione**: L'analisi dei documenti mostra 4 interventi emergenziali su ponti negli ultimi 18 mesi [Prot. 05670/2024, 09120/2024, 14200/2024, 02340/2025]. Un sistema predittivo potrebbe intercettare criticità prima dell'emergenza.

> ⚠️ *Questa proposta richiede validazione con:*
> - *Ufficio Tecnico Lavori Pubblici*
> - *Servizio Manutenzione Patrimonio*
> - *Eventuale consulenza strutturista*

### 2.2 Prioritizzazione Interventi (Proposta)

🔴 **Proposta**: Si potrebbe valutare la seguente prioritizzazione basata sui dati disponibili:

| Priorità | Opera | Intervento Suggerito | Budget Indicativo | Tempistica Suggerita |
|----------|-------|---------------------|-------------------|---------------------|
| 🔴 1 | Cavalcavia SS67 | Sostituzione giunti | €150.000-200.000 | Entro Q2 2026 |
| 🔴 2 | Sottopasso Via Roma | Impermeabilizzazione | €80.000-120.000 | Entro Q4 2026 |
| 🔴 3 | Passerella Parco Nord | Verniciatura protettiva | €25.000-40.000 | Entro Q1 2027 |

> ⚠️ *Priorità suggerite dall'algoritmo. La definizione delle priorità reali compete agli uffici tecnici.*

### 2.3 Possibili Fonti di Finanziamento (Proposta)

🔴 **Proposta**: Si segnalano possibili canali di finanziamento da valutare:

1. **PNRR Missione 3** - Infrastrutture sostenibili
   - 🔴 *Ipotesi*: Potrebbero essere disponibili fondi per monitoraggio smart infrastrutture
   - 🔴 *Azione suggerita*: Verificare bandi aperti su Italia Domani

2. **Fondo Opere Indifferibili**
   - 🔴 *Ipotesi*: Possibile compensazione aumenti prezzi materiali
   - 🔴 *Azione suggerita*: Verificare requisiti con Ragioneria

3. **Contributi Regionali** 
   - 🔴 *Ipotesi*: La Regione Toscana potrebbe avere linee dedicate
   - 🔴 *Azione suggerita*: Contattare Settore Infrastrutture Regione

> ⚠️ *Queste sono ipotesi algoritmiche. La verifica dell'effettiva disponibilità e applicabilità dei finanziamenti richiede approfondimento con uffici competenti.*

---

## 📋 RIEPILOGO ESECUTIVO

### Fatti Verificati (PARTE A)
- 🟢 3 interventi principali su ponti documentati
- 🟢 3 accordi quadro attivi per manutenzione viabilità
- 🟢 Budget impegnato 2024-2025: €2.200.000
- 🟢 4 ponti a priorità alta su 20 censiti

### Proposte AI (PARTE B) - Da Validare
- 🔴 Sistema monitoraggio IoT: €105.000-165.000 (proposta)
- 🔴 3 interventi prioritari identificati per €255.000-360.000 (proposta)
- 🔴 3 possibili canali finanziamento da esplorare

---

## ⚠️ DISCLAIMER FINALE

Le informazioni contenute nella **PARTE A** sono estratte da documenti ufficiali dell'Ente.
Le proposte contenute nella **PARTE B** sono elaborazioni algoritmiche del sistema NATAN
e **NON costituiscono parere tecnico o amministrativo**.

Ogni decisione operativa deve essere:
1. Validata dagli uffici tecnici competenti
2. Verificata sotto il profilo amministrativo-contabile
3. Approvata secondo le procedure dell'Ente

*Report generato il 26/11/2025 - Sistema NATAN v2.0*
```

---

### 📐 Regole del Template

**Struttura Obbligatoria:**
1. ⚠️ Nota Metodologica (metriche copertura)
2. 📊 Legenda Affidabilità
3. **PARTE A**: Solo fatti (🟢/🟠)
4. **PARTE B**: Solo proposte (🔴)
5. 📋 Riepilogo Esecutivo
6. ⚠️ Disclaimer Finale

**Regole Citazioni:**
- Formato: `[Prot. XXXXX/YYYY - Titolo Atto]`
- Mai riferimenti generici come `[DOC 1-10]`
- Includere sempre data o anno

**Regole Numeri:**
- 🟢 = valore esatto da documento
- 🟠 = stima con fonte indicata
- 🔴 = SEMPRE range (es: €100.000-150.000)

**Regole Linguaggio PARTE B:**
- ✅ "Si suggerisce di valutare..."
- ✅ "Si potrebbe considerare..."
- ✅ "Una possibile opzione sarebbe..."
- ❌ MAI: "Bisogna...", "È necessario...", "Occorre..."

---

## 🎯 Roadmap Futura

### Prossimi Miglioramenti
1. ~~**Template sezione campione**~~ ✅ Completato (vedi sopra)
2. **Multi-lingua** - Supporto inglese per documenti UE
3. **Grafici automatici** - Generazione chart da dati estratti
4. **Diff report** - Confronto tra report successivi

### Ottimizzazioni Performance
1. **Caching** - Cache per query frequenti
2. **Batch processing** - Elaborazione batch per report lunghi
3. **Streaming** - Risposta progressiva per UX migliore

---

## 📋 Esempio Output Completo v2.0

```markdown
---
## ⚠️ NOTA METODOLOGICA

| Metrica | Valore |
|---------|--------|
| **Atti analizzati** | 50 su 1199 disponibili (4.2%) |
| **Periodo coperto** | 2024-04 - 2025-11 |
| **Tipologie incluse** | Manutenzioni, Progetti PNRR |

> ⚡ AVVERTENZA: Questo report è una sintesi AI a supporto decisionale.
> NON sostituisce la progettazione tecnica e la validazione degli uffici competenti.

---

**📊 LEGENDA AFFIDABILITÀ DATI**

🟢 **VERIFICATO** = Dato presente nei documenti ufficiali
🟠 **STIMATO** = Elaborazione/stima basata su dati parziali  
🔴 **PROPOSTA AI** = Suggerimento del sistema, NON nei documenti

---

## PARTE A: STATO ATTUALE

**Infrastrutture Viarie:**
- 🟢 Accordo Quadro guard rail - €500.000 [Prot. 00457]
- 🟢 Manutenzione giunti cavalcavia Affrico - €200.000 [Prot. 00594]
- 🟢 Consolidamento Ponte al Pino [Prot. 18910/2025]

## PARTE B: PROPOSTE E RACCOMANDAZIONI

**Piano Manutenzione Predittiva:**
- 🔴 Si suggerisce di implementare sistema IoT per monitoraggio ponti
- 🔴 Budget indicativo: €200.000-350.000 (da validare con uffici tecnici)
- 🔴 Target indicativo: riduzione interventi emergenza -30% (da calibrare su dati storici)

> **DISCLAIMER**: Proposte 🔴 richiedono validazione tecnica e amministrativa.
```

---

**Versione**: 2.0.0  
**Status**: ✅ **QUALITÀ ISTITUZIONALE PA** - Pronto per produzione