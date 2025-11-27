# 📋 NATAN_LOC - Elenco Attività da Completare

**Versione**: 1.0.0  
**Data**: 2025-01-28  
**Autore**: Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici  
**Fonte**: Analisi `docs/NATAN_LOC_STATO_DELLARTE.md`

---

## 🎯 PRIORITÀ ALTA (P0) - Bloccanti per Production

### **1. Frontend - Chat UI Completa con RAG-Fortress Integration** 🚧

**Status**: Componenti base presenti, integrazione RAG-Fortress incompleta

**Da Implementare**:
- [ ] **Visualizzazione URS Score** nella risposta chat
  - Badge visuale con colore basato su score (verde >90, giallo 70-90, rosso <70)
  - Tooltip con spiegazione dettagliata URS
  - Posizionamento: sopra o accanto alla risposta

- [ ] **Visualizzazione Claims** estratte
  - Lista numerata delle claim [CLAIM_001], [CLAIM_002], etc.
  - Evidenziazione claim utilizzate nella risposta
  - Link alle fonti per ogni claim

- [ ] **Visualizzazione Sources** (fonti documenti)
  - Lista documenti utilizzati come evidenze
  - Link ai documenti originali (se disponibili)
  - Preview estratti rilevanti

- [ ] **Visualizzazione Gaps** rilevati
  - Alert visivo quando ci sono parti non coperte
  - Messaggio: "Non dispongo di informazioni verificate sufficienti per rispondere a questa parte della domanda."
  - Styling distintivo (bordo giallo/arancione)

- [ ] **Visualizzazione Hallucinations** (se trovate)
  - Alert critico se il fact-checker trova allucinazioni
  - Messaggio di rifiuto automatico se URS < 90
  - Logging per debugging

**File da Modificare**:
- `frontend/src/components/ChatInterface.ts` - Aggiungere rendering URS, claims, sources, gaps
- `frontend/src/components/Message.ts` - Estendere componente messaggio con nuovi campi
- `laravel_backend/resources/views/natan-pro/chat.blade.php` - Aggiungere template Blade per nuovi elementi

**Stima**: 2-3 giorni

---

### **2. Frontend - Document Upload UI** 🚧

**Status**: Non implementato (nessun componente upload trovato)

**Da Implementare**:
- [ ] **Componente Upload Form**
  - Drag & drop area
  - Selezione file multipli
  - Preview file selezionati
  - Validazione client-side (tipo, dimensione)

- [ ] **Integrazione Ultra Upload Manager** (già presente in EGI_Documentation)
  - Utilizzare `file_upload_manager.ts` esistente
  - Integrare con `EGIUploadHandler.ts` o creare `NatanUploadHandler.ts`
  - Supporto per PDF, DOCX, TXT, immagini

- [ ] **Backend Upload Endpoint**
  - `POST /api/v1/documents/upload` in FastAPI
  - Validazione server-side
  - Estrazione testo (PDF, DOCX)
  - Generazione embeddings
  - Salvataggio in MongoDB con metadata

- [ ] **UI Upload nella vista Documents**
  - Pulsante "Carica Documento" in `natan-pro/documents/index.blade.php`
  - Modal o pagina dedicata per upload
  - Progress bar durante upload
  - Feedback successo/errore

**File da Creare/Modificare**:
- `frontend/src/components/DocumentUpload.ts` - Nuovo componente
- `python_ai_service/app/routers/documents.py` - Nuovo endpoint upload
- `laravel_backend/resources/views/natan-pro/documents/upload.blade.php` - Nuova vista
- `laravel_backend/app/Http/Controllers/DocumentController.php` - Metodo `upload()`

**Stima**: 3-4 giorni

---

### **3. Compliance Scanner - Estensione Comuni Toscani** 🚧

**Status**: Solo Firenze (2297 documenti) e Sesto Fiorentino (127 documenti) completi

**Da Implementare**:
- [ ] **Scraping per tutti i comuni toscani** (40+ comuni)
  - Empoli, Pisa, Prato, Arezzo, Livorno, Pistoia, Grosseto, Massa, Lucca, Siena
  - Altri comuni minori della Toscana
  - Strategia: API dirette → ScraperFactory → TrivellaBrutale → Fallback

- [ ] **Metodi specifici ottimizzati** (come Firenze e Sesto Fiorentino)
  - Analisi API di ogni comune
  - Implementazione endpoint specifici se disponibili
  - HTML scraping per Albi Pretori

- [ ] **Configurazione comuni** in `scanner.py`
  - Lista completa comuni toscani con slug
  - Mapping URL base per ogni comune
  - Configurazione tipi atti per comune

**File da Modificare**:
- `python_ai_service/app/services/compliance_scanner/scanner.py` - Aggiungere metodi `_strategy_api_{comune}`
- `python_ai_service/app/services/compliance_scanner/scanner.py` - Estendere lista `COMUNI_TOSCANI`

**Stima**: 1-2 settimane (a seconda della complessità di ogni comune)

---

## 🎯 PRIORITÀ MEDIA (P1) - Importanti per UX

### **4. Compliance Scanner - Dashboard Regionale** 🚧

**Status**: Non implementato

**Da Implementare**:
- [ ] **Vista Dashboard Compliance**
  - Mappa Toscana con indicatori per comune
  - Tabella comuni con score compliance
  - Filtri per score, violazioni, data scraping
  - Grafici trend compliance nel tempo

- [ ] **Statistiche Regionali**
  - Score medio regionale
  - Comuni più conformi / meno conformi
  - Trend miglioramento nel tempo
  - Confronto tra comuni

- [ ] **Dettaglio Comune**
  - Score compliance dettagliato
  - Lista violazioni con descrizione
  - Link a report PDF
  - Storico scansioni

**File da Creare**:
- `laravel_backend/resources/views/natan-pro/compliance/dashboard.blade.php` - Nuova vista
- `laravel_backend/app/Http/Controllers/ComplianceController.php` - Nuovo controller
- `laravel_backend/routes/web.php` - Route `/natan-pro/compliance/dashboard`

**Stima**: 2-3 giorni

---

### **5. Compliance Scanner - Alert Automatici** 🚧

**Status**: Non implementato

**Da Implementare**:
- [ ] **Sistema Alert per Violazioni Critiche**
  - Definizione violazioni critiche (score < 50%, violazioni L.69/2009)
  - Email automatica al comune quando violazione critica rilevata
  - Notifica in dashboard admin
  - Logging in MongoDB per audit trail

- [ ] **Scheduling Scansioni Automatiche**
  - Cron job per scansioni periodiche (settimanali/mensili)
  - Notifica se score compliance peggiora
  - Report automatici via email

- [ ] **Integrazione Sistema Notifiche** (se esiste in EGI)
  - Utilizzare sistema notifiche esistente
  - Creare notifiche tipo "Compliance Violation"

**File da Creare/Modificare**:
- `python_ai_service/app/services/compliance_scanner/alert_sender.py` - Nuovo servizio
- `laravel_backend/app/Console/Commands/ComplianceScanScheduler.php` - Nuovo comando cron
- `laravel_backend/app/Services/ComplianceAlertService.php` - Nuovo servizio Laravel

**Stima**: 2-3 giorni

---

### **6. Notarizzazione Workflow UI** 🚧

**Status**: Backend parzialmente implementato (CoA), UI mancante

**Da Implementare**:
- [ ] **UI Workflow Notarizzazione**
  - Selezione documento da notarizzare
  - Preview documento
  - Form metadati notarizzazione
  - Conferma e invio

- [ ] **Visualizzazione Certificato CoA**
  - Preview certificato generato
  - Download PDF certificato
  - QR code per verifica pubblica
  - Link verifica pubblica

- [ ] **Storico Notarizzazioni**
  - Lista documenti notarizzati
  - Filtri per data, tipo, stato
  - Dettaglio singola notarizzazione

**File da Creare**:
- `laravel_backend/resources/views/natan-pro/notarization/index.blade.php`
- `laravel_backend/resources/views/natan-pro/notarization/create.blade.php`
- `laravel_backend/resources/views/natan-pro/notarization/show.blade.php`
- `laravel_backend/app/Http/Controllers/NotarizationController.php`

**Stima**: 3-4 giorni

---

### **7. Dashboard Tenant Avanzata** 🚧

**Status**: Dashboard base presente, funzionalità avanzate mancanti

**Da Implementare**:
- [ ] **Analytics Avanzate**
  - Grafici utilizzo AI nel tempo
  - Statistiche domande più frequenti
  - Analisi sentiment conversazioni
  - Heatmap utilizzo features

- [ ] **Gestione Utenti Tenant**
  - Lista utenti del tenant
  - Invito nuovi utenti
  - Gestione permessi (Spatie Permissions)
  - Ruoli e responsabilità

- [ ] **Configurazione Tenant**
  - Impostazioni generali
  - Configurazione AI models preferiti
  - Impostazioni notifiche
  - Integrazioni esterne

**File da Modificare/Creare**:
- `laravel_backend/resources/views/natan-pro/statistics/dashboard.blade.php` - Estendere
- `laravel_backend/resources/views/natan-pro/tenant/users.blade.php` - Nuova vista
- `laravel_backend/resources/views/natan-pro/tenant/settings.blade.php` - Nuova vista
- `laravel_backend/app/Http/Controllers/TenantController.php` - Nuovo controller

**Stima**: 4-5 giorni

---

## 🎯 PRIORITÀ BASSA (P2) - Production Hardening

### **8. Monitoring Completo (Prometheus/Grafana)** 📋

**Status**: Non implementato

**Da Implementare**:
- [ ] **Metriche Prometheus**
  - Metriche FastAPI (request rate, latency, errors)
  - Metriche Laravel (query time, cache hits)
  - Metriche MongoDB (connection pool, query time)
  - Metriche AI (token usage, model calls, costs)

- [ ] **Dashboard Grafana**
  - Dashboard sistema generale
  - Dashboard AI service
  - Dashboard compliance scanner
  - Alert rules per soglie critiche

**Stima**: 3-4 giorni

---

### **9. Backup Automation MongoDB Atlas** 📋

**Status**: Backup manuali disponibili, automazione mancante

**Da Implementare**:
- [ ] **Script Backup Automatico**
  - Backup giornaliero MongoDB Atlas
  - Verifica integrità backup
  - Rotazione backup (mantieni ultimi 30 giorni)
  - Notifica se backup fallisce

- [ ] **Disaster Recovery Plan**
  - Documentazione procedure restore
  - Test restore periodici
  - RTO/RPO definiti

**Stima**: 2-3 giorni

---

### **10. Performance Optimization** 📋

**Status**: Performance buone, ottimizzazioni possibili

**Da Implementare**:
- [ ] **Caching Strategico**
  - Cache risultati RAG-Fortress per domande simili
  - Cache embeddings documenti
  - Cache statistiche dashboard
  - Cache compliance scores

- [ ] **Query Optimization**
  - Analisi query MongoDB lente
  - Ottimizzazione indici
  - Query batching dove possibile

- [ ] **Rate Limiting e Throttling**
  - Rate limiting API endpoints
  - Throttling richieste AI
  - Protezione DDoS

**Stima**: 2-3 giorni

---

### **11. Testing Completo** 📋

**Status**: Test base presenti, coverage incompleta

**Da Implementare**:
- [ ] **Integration Tests RAG-Fortress**
  - Test pipeline completa end-to-end
  - Test con documenti reali
  - Test edge cases

- [ ] **E2E Tests Compliance Scanner**
  - Test scraping comuni reali
  - Test generazione report
  - Test import MongoDB

- [ ] **Performance Tests**
  - Load testing chat endpoint
  - Stress testing MongoDB
  - Benchmark RAG-Fortress pipeline

- [ ] **Test Coverage**
  - Obiettivo: >80% coverage
  - Test critici per ogni componente
  - Test regression automatici

**Stima**: 1 settimana

---

## 📊 Riepilogo per Priorità

### **P0 - Bloccanti (1-2 settimane)**
1. Chat UI completa con RAG-Fortress (2-3 giorni)
2. Document Upload UI (3-4 giorni)
3. Estensione Compliance Scanner comuni (1-2 settimane)

**Totale P0**: ~3 settimane

### **P1 - Importanti (2-3 settimane)**
4. Dashboard Compliance Regionale (2-3 giorni)
5. Alert Automatici Compliance (2-3 giorni)
6. Notarizzazione Workflow UI (3-4 giorni)
7. Dashboard Tenant Avanzata (4-5 giorni)

**Totale P1**: ~2 settimane

### **P2 - Production Hardening (2-3 settimane)**
8. Monitoring Prometheus/Grafana (3-4 giorni)
9. Backup Automation (2-3 giorni)
10. Performance Optimization (2-3 giorni)
11. Testing Completo (1 settimana)

**Totale P2**: ~2 settimane

---

## 🎯 Timeline Complessiva

**Settimane 1-3**: P0 (Bloccanti)  
**Settimane 4-5**: P1 (Importanti)  
**Settimane 6-7**: P2 (Production Hardening)

**Totale**: ~7 settimane per completamento completo

---

## 📝 Note

- Le stime sono indicative e possono variare in base alla complessità reale
- Alcune attività possono essere parallelizzate (es. Frontend e Backend)
- Priorità possono cambiare in base a requisiti business
- Testing dovrebbe essere continuo durante lo sviluppo, non solo alla fine

---

**Versione**: 1.0.0  
**Data**: 2025-01-28  
**Status**: ✅ Elenco completo e prioritizzato

