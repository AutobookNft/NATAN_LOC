# 🌍 Piano Implementazione Sistema Multi-Currency 

## 📊 Analisi Situazione Attuale

### ✅ Componenti Già Implementati
- **Sistema multi-currency base** funzionante (USD, EUR, GBP)
- **Database schema** con campi legacy per retrocompatibilità
- **CurrencyService** per conversioni e tassi di cambio
- **API endpoints** per operazioni currency
- **UEM/ULM** integrazione per error handling e logging
- **Frontend currency selector** nell'header

### 🚨 Componenti da Implementare (CRITICAL)
- **Policy FIAT-first** con EUR come valuta canonica
- **Sistema di quotazioni a tempo (TTL)** per pagamenti
- **Gestione stati prenotazioni** completa con sub_status
- **Nuovo schema DB** con campi `amount_eur`, `buyer_*`, `fx_*`
- **Certificati PDF/A** con ancoraggio blockchain
- **Job schedulati** per scadenze e cleanup

---

## 🎯 Piano di Lavoro Strutturato

### FASE 1: Preparazione Database (Non Distruttiva)

#### Sprint 1.1: Schema Evolution
```sql
-- Migration additiva per nuovi campi
-- Dual-write strategy (manteniamo legacy)
-- Indici per performance
-- Testing su DB di sviluppo
```

**Deliverables:**
- [ ] Migration file per nuovi campi
- [ ] Script di rollback
- [ ] Test su ambiente di sviluppo
- [ ] Documentazione schema changes

#### Sprint 1.2: Model Updates
```php
// Reservation model con nuovi attributi
// Metodi per gestione stati
// Accessor/Mutator per retrocompatibilità
// Unit tests per model
```

**Deliverables:**
- [ ] Model Reservation aggiornato
- [ ] Unit tests per nuovi metodi
- [ ] Backward compatibility verificata
- [ ] Documentation updates

---

### FASE 2: Core Services Update

#### Sprint 2.1: ReservationService Evolution
```php
// Implementazione macchina a stati
// Quote generation con TTL
// FX rate handling (ALGO per 1 EUR)
// Idempotency keys
// Comprehensive testing
```

**Deliverables:**
- [ ] State machine implementation
- [ ] Quote system con TTL
- [ ] FX rate calculation logic
- [ ] Error handling robusto
- [ ] Integration tests

#### Sprint 2.2: CurrencyService Enhancement
```php
// EUR as canonical currency
// Quote caching strategy
// Buffer calculations
// Slippage management
```

**Deliverables:**
- [ ] EUR-first policy implementation
- [ ] Cache strategy per quotes
- [ ] Buffer e slippage calculations
- [ ] Performance optimization

---

### FASE 3: Payment Flow

#### Sprint 3.1: Quotation System
```javascript
// FX Quote generation
// TTL management
// Quote expiration handling
// UI timer components
```

**Deliverables:**
- [ ] Quote generation API
- [ ] TTL management system
- [ ] Frontend countdown timers
- [ ] Expiration handling logic

#### Sprint 3.2: Payment Processing
```php
// Multi-currency payment handling
// ALGO escrow calculation
// Transaction logging
// Refund mechanisms
```

**Deliverables:**
- [ ] Payment processing pipeline
- [ ] ALGO escrow integration
- [ ] Comprehensive logging
- [ ] Refund automation

---

### FASE 4: Certificati e Compliance

#### Sprint 4.1: Certificate System
```php
// PDF/A generation
// Merkle tree batching
// Algorand anchoring
// Verification endpoints
```

**Deliverables:**
- [ ] PDF/A certificate generator
- [ ] Blockchain anchoring system
- [ ] Verification API endpoints
- [ ] Certificate template engine

#### Sprint 4.2: Audit & Compliance
```php
// Complete audit trail
// GDPR compliance checks
// Financial reporting
// Error recovery procedures
```

**Deliverables:**
- [ ] Audit logging system
- [ ] GDPR compliance tools
- [ ] Financial reports
- [ ] Recovery procedures

---

## 🔄 Strategia Cambio Chat

Quando raggiungi i limiti della chat, usa questo **handover protocol**:

### Template di Handover
```markdown
## 🔄 HANDOVER TO NEW CHAT

### Current Status:
- **Phase**: [FASE X.Y]
- **Sprint**: [Nome Sprint]
- **Last Completed**: [Ultimo file/componente completato]
- **Next Task**: [Prossima attività]

### Critical Context:
- Working on: [Descrizione del modulo]
- Key Decisions: [Decisioni prese]
- Blockers: [Eventuali problemi]

### Files Modified:
1. [path/to/file1.php] - [cosa è stato fatto]
2. [path/to/file2.php] - [cosa è stato fatto]

### Testing Status:
- [ ] Unit tests written
- [ ] Manual testing done
- [ ] Integration verified

### Next Steps:
1. [Prossimo step immediato]
2. [Step successivo]
3. [Step finale del sprint]

### Critical Warnings:
- [Eventuali warning finanziari]
- [Problemi di compatibilità]
```

---

## 🚀 Primo Sprint: Database Schema Evolution

### Obiettivi Sprint 1.1
Creare una migration **NON DISTRUTTIVA** che:

1. **Aggiunge nuovi campi** alla tabella `reservations`
2. **NON rimuove campi legacy** (backward compatibility)
3. **Implementa dual-write strategy**
4. **Aggiunge indici ottimizzati**

### ❗ Domande Critiche Pre-Implementazione

#### Ambiente e Sicurezza
- [ ] **Database di test**: Disponibile ambiente di sviluppo separato?
- [ ] **Backup**: Eseguito backup completo del database production?
- [ ] **Downtime**: Accettabili 2-3 minuti di downtime o necessario zero-downtime?
- [ ] **Staging**: Disponibile ambiente di staging per testing?

#### Configurazione Tecnica
- [ ] **Laravel version**: Quale versione in uso?
- [ ] **Database engine**: MySQL/PostgreSQL/altro?
- [ ] **Caching**: Redis/Memcached disponibile?
- [ ] **Queue system**: Configurato per job scheduling?

---

## 📋 Checklist Generale

### Pre-Implementazione
- [ ] Analisi requisiti completata
- [ ] Backup database eseguito
- [ ] Ambiente di test configurato
- [ ] Team alignment su architettura

### Durante Sviluppo
- [ ] Code review per ogni componente
- [ ] Unit tests per nuove funzionalità
- [ ] Integration tests per flussi completi
- [ ] Performance testing su ambiente staging

### Post-Implementazione
- [ ] Monitoring attivo per errori
- [ ] Rollback plan testato
- [ ] Documentation aggiornata
- [ ] Training team completato

---

## 🎖️ Criteri di Successo

### Tecnici
- ✅ Zero data loss durante migration
- ✅ Backward compatibility mantenuta
- ✅ Performance non degradata
- ✅ Test coverage > 90%

### Business
- ✅ Flusso pagamenti multi-currency funzionante
- ✅ Certificati blockchain integrati
- ✅ Compliance finanziaria rispettata
- ✅ UX migliorata per utenti internazionali

---

*Documento generato il: 2025-08-15*  
*Versione: 1.0*  
*Status: Ready for Implementation*