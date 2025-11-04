# **FLORENCEEGI - PROJECT-SPECIFIC RULES**

**Version**: 1.0.0  
**Date**: 2025-10-28  
**Context**: FlorenceEGI Platform - Enterprise & PA  
**Foundation**: OS3.0 Standards (see `cursorrules_*.md` files for universal rules)

---

> **NOTE:** Questo file contiene SOLO le regole specifiche per il progetto FlorenceEGI.  
> Per le regole universali OS3.0 (REGOLA ZERO, I18N, STATISTICS, UEM-FIRST, etc.) fare riferimento ai file `cursorrules_*.md`

---

# **🏛️ CONTESTO PROGETTO - ENTERPRISE & PA**

## **LIVELLO APPLICAZIONE:**

**FlorenceEGI è una piattaforma ENTERPRISE di livello mission-critical:**

-   🏛️ **Target principale**: Pubbliche Amministrazioni (PA) italiane
-   🏢 **Standard richiesti**: Enterprise-grade, audit-ready, compliance-first
-   📊 **Criticità dati**: Ogni dato mostrato deve essere accurato e completo
-   🔒 **Sicurezza**: GDPR mandatory, audit trail completo, privacy by design
-   📈 **Scalabilità**: Architettura per migliaia di utenti PA concorrenti

## **IMPLICAZIONI OPERATIVE:**

### **🚨 CREDIBILITÀ PA:**

-   **Zero tolleranza errori**: Una statistica sbagliata = fiducia persa = contratto a rischio
-   **Trasparenza totale**: Ogni operazione deve essere tracciabile e verificabile
-   **Dati completi**: Mai mostrare dati parziali come se fossero completi
-   **Professionalità**: Codice enterprise-grade, no shortcuts, no workarounds

### **🔒 COMPLIANCE:**

-   **GDPR obbligatorio**: Non optional, non "nice to have" - è BLOCKING
-   **Audit trail**: Ogni modifica dati personali deve essere loggata
-   **Consent management**: Check esplicito prima di ogni operazione su dati sensibili
-   **Error handling**: Mai esporre errori tecnici agli utenti PA

### **📊 QUALITÀ CODICE:**

-   **OOP puro**: No procedural spaghetti code
-   **Design patterns**: Repository, Service, DTO quando appropriati
-   **Type safety**: Type hints sempre, strict types quando possibile
-   **Testing mindset**: Codice deve essere testabile (anche se test non sempre scritti)

### **🎯 USER EXPERIENCE PA:**

-   **Interfaccia professionale**: No colori sgargianti, no animazioni eccessive
-   **Accessibilità WCAG 2.1 AA**: Obbligatoria per PA
-   **Performance**: Caricamenti rapidi, no lag percepibile
-   **Affidabilità**: Sistema deve essere percepito come solido e stabile

## **⚠️ COSA SIGNIFICA IN PRATICA:**

**Quando scrivi codice per FlorenceEGI:**

1. ❓ **"Questo codice resisterebbe ad un audit PA?"**
2. ❓ **"Se questo dato fosse sbagliato, perderemmo il cliente?"**
3. ❓ **"Questa soluzione è enterprise-grade o è un workaround?"**
4. ❓ **"Il GDPR officer approverebbe questo flusso?"**

**Se la risposta a qualsiasi domanda è NO → 🛑 STOP e ripensa l'approccio**

---

# **🎨 BRAND GUIDELINES OBBLIGATORIE**

**Documento di riferimento completo**: `docs/ai/marketing/FlorenceEGI Brand Guidelines.md`  
**Leggere SEMPRE prima di creare/modificare UI, layout, colori**

## **PALETTE COLORI:**

```css
#D4A574 - Oro Fiorentino (CTA, premium, evidenziazioni)
#2D5016 - Verde Rinascita (sostenibilità, EPP, ambiente)
#1B365D - Blu Algoritmo (tecnologia, blockchain, trust)
#6B6B6B - Grigio Pietra (testi secondari, bordi)
#C13120 - Rosso Urgenza (alert, azioni critiche)
#E67E22 - Arancio Energia (notifiche positive)
#8E44AD - Viola Innovazione (premium, futuristico)
```

## **TIPOGRAFIA:**

-   **Titoli**: Playfair Display / Crimson Text (eleganza rinascimentale)
-   **Corpo**: Source Sans Pro / Open Sans (leggibilità moderna)
-   **Mono**: JetBrains Mono / Fira Code (codice, dati tecnici)

## **PRINCIPI UI/UX FONDAMENTALI:**

-   ✅ Eleganza rinascimentale - spazi bianchi, proporzioni auree
-   ✅ Zero friction - ogni azione chiara e immediata
-   ✅ Trasparenza - fee, royalty, impatti sempre visibili
-   ✅ Accessibilità WCAG 2.1 AA obbligatoria
-   ❌ NO colori sgargianti - no crypto-hype style
-   ❌ NO gergo tecnico/crypto - linguaggio nobile accessibile
-   ❌ NO animazioni eccessive - eleganza e sobrietà

## **QUANDO LAVORI SU UI:**

1. 📖 Leggi Brand Guidelines complete
2. 🎨 Verifica palette colori usata
3. 📐 Rispetta principi layout rinascimentale
4. ♿ Testa accessibilità WCAG 2.1 AA

---

# **🏛️ REGOLA MiCA-SAFE - COMPLIANCE EUROPEA OBBLIGATORIA**

## **🚨 FLORENCE EGI DEVE RIMANERE 100% MiCA-SAFE 🚨**

**PRINCIPIO FONDAMENTALE:** La piattaforma FlorenceEGI NON deve mai richiedere licenze crypto europee (CASP/EMI).

## **✅ COSA È PERMESSO (MiCA-SAFE):**

-   **Emettere NFT/ASA** per conto dell'utente (minting service)
-   **Custodire temporaneamente** NFT in wallet della piattaforma
-   **Trasferire NFT** a wallet utenti su richiesta
-   **Gestire pagamenti FIAT** tramite PSP tradizionali (Stripe, PayPal)
-   **Fornire servizi tecnologici** blockchain senza toccare crypto-asset per conto terzi

## **❌ COSA È VIETATO (RICHIEDE LICENZA):**

-   **Custodire criptovalute** (ALGO, USDC, etc.) per conto degli utenti
-   **Fare da exchange** crypto/fiat
-   **Processare pagamenti crypto** direttamente
-   **Fornire wallet custodial** per crypto-asset degli utenti
-   **Gestire chiavi private** di wallet utenti contenenti crypto

## **📋 IMPLICAZIONI OPERATIVE:**

### **LIVELLO 1 - Nessun wallet (100% tradizionale):**

-   ✅ Cliente paga in EUR via PSP
-   ✅ Piattaforma minta EGI su wallet proprio
-   ✅ Cliente riceve certificato PDF + QR verifica
-   ❌ **NO wallet custodial per il cliente**
-   ❌ **NO gestione crypto per conto del cliente**

### **LIVELLO 2 - Ho un wallet, pago in FIAT:**

-   ✅ Cliente paga in EUR via PSP
-   ✅ Cliente fornisce indirizzo wallet proprio
-   ✅ Piattaforma trasferisce EGI al wallet cliente
-   ❌ **NO gestione del wallet cliente**
-   ❌ **NO custodia crypto per il cliente**

### **LIVELLO 3 - Pagamenti Crypto (Partner esterni):**

-   ✅ Partner CASP/EMI gestisce pagamenti crypto
-   ✅ Piattaforma riceve solo notifica di pagamento completato
-   ❌ **NO gestione diretta pagamenti crypto**
-   ❌ **NO custodia crypto anche temporanea**

## **🛡️ CONTROLLI AUTOMATICI:**

**PRIMA DI IMPLEMENTARE QUALSIASI FEATURE BLOCKCHAIN:**

1. ❓ **"Questa funzione richiede custodia crypto per utenti?"** → SE SÌ: ❌ STOP
2. ❓ **"Questa funzione tocca crypto-asset di proprietà utenti?"** → SE SÌ: ❌ STOP
3. ❓ **"Questa funzione richiede licenza CASP/EMI?"** → SE SÌ: ❌ STOP
4. ❓ **"Posso implementarla solo con NFT/ASA + FIAT?"** → SE NO: ❌ STOP

## **🚨 SE VIOLI MiCA-SAFE:**

```
🛑 VIOLAZIONE MiCA-SAFE RILEVATA!

Funzione proposta: [nome funzione]
Violazione: [descrizione]
Licenza richiesta: [CASP/EMI/ALTRO]

AZIONI OBBLIGATORIE:
1. STOP implementazione immediato
2. Propongo alternative MiCA-safe
3. Documento il rischio di compliance
4. Aspetto conferma esplicita per procedere
```

## **🎯 ARCHITECTURE PATTERN MiCA-SAFE:**

**SEMPRE APPLICARE:**

-   **Gateway PSP** per tutti i pagamenti fiat
-   **Microservizio blockchain** separato per operazioni tecniche
-   **Wallet piattaforma** per custodia temporanea EGI
-   **Transfer automatici** EGI → wallet utenti
-   **Zero gestione crypto** proprietà utenti

**Questa regola è BLOCKING: se violi MiCA-safe, tutto il progetto è a rischio normativo.**

---

# **📋 REGOLA PA/ENTERPRISE - PROJECT TRACKING OBBLIGATORIO**

## **PRIMA AZIONE IN OGNI NUOVA CHAT:**

### **STEP 1: LEGGI PA_ENTERPRISE_TODO_MASTER.md**

```bash
read_file docs/ai/context/PA_ENTERPRISE_TODO_MASTER.md
```

**Questo file contiene:**

-   ✅ Status attuale progetto PA/Enterprise
-   ✅ Task completati e da fare (41 task totali)
-   ✅ Dependencies tra task
-   ✅ Effort estimates e priorities
-   ✅ Milestone tracking (MVP → Expansion → Release)

### **STEP 2: LEGGI DOCUMENTI ACCESSORI NECESSARI**

In base al task corrente, leggi:

```bash
# Per CODE PATTERNS e implementazione:
read_file docs/ai/context/PA_ENTERPRISE_IMPLEMENTATION_GUIDE.md

# Per DESIGN UI/UX:
read_file docs/ai/marketing/PA_ENTERPRISE_BRAND_GUIDELINES.md

# Per VOCABULARY expansion (FASE 2):
read_file docs/ai/context/PA_ENTERPRISE_VOCABULARY_EXPANSION.md

# Per ARCHITETTURA sistema:
read_file docs/ai/context/PA_ENTERPRISE_ARCHITECTURE.md
```

### **STEP 3: IDENTIFICA TASK CORRENTE**

Cerca nel TODO_MASTER:

-   Task con status 🟡 IN PROGRESS (priorità assoluta)
-   Task con status ⚪ NOT STARTED e Priority P0 (blocking)
-   Verifica dependencies soddisfatte

### **STEP 4: DICHIARA STATUS E PROPONI AZIONE**

```
📋 PA/ENTERPRISE PROJECT STATUS:
- Current Phase: FASE [X]
- Current Task: [Task ID e nome]
- Status: [completati]/[totali] task
- Dependencies: [✅ OK | ⚠️ MANCANTI: lista]

🎯 PROPOSED ACTION:
[Descrizione task da fare]

Procedo? [SI/NO/MODIFICHE]
```

## **⚠️ SE TODO_MASTER NON ESISTE:**

```
🛑 ERRORE CRITICO: PA_ENTERPRISE_TODO_MASTER.md non trovato

Possibili cause:
1. Chat precedente a creazione documentazione
2. File spostato/rinominato
3. Working directory errata

AZIONI:
1. Cerco file: grep_search "PA_ENTERPRISE_TODO" -includePattern="docs/**"
2. Se non trovo → CHIEDO: "Devo ricreare documentazione PA/Enterprise?"
```

## **🔄 UPDATE TODO_MASTER DOPO COMPLETAMENTO TASK:**

Quando completi un task:

1. Chiedi conferma: "Task [X] completato. Aggiorno TODO_MASTER status?"
2. Se confermato, marca task come ✅ COMPLETATO
3. Aggiorna progress percentuale fase
4. Commit con messaggio: `[DOC] Update PA_ENTERPRISE_TODO_MASTER - Task [X] completed`

## **📊 FREQUENCY CHECKS:**

-   **Ogni nuova chat**: Leggi TODO_MASTER (OBBLIGATORIO)
-   **Ogni ora di lavoro**: Verifica progress milestone
-   **Dopo ogni task**: Update TODO_MASTER status
-   **Prima di proporre nuove feature**: Verifica non sia già in TODO

## **RATIONALE:**

**Contesto PA/Enterprise:** Progetto strutturato in 41 task, 8 settimane, 130 ore effort. Senza tracking:

-   ❌ Rischio duplicazione lavoro
-   ❌ Rischio violare dependencies
-   ❌ Impossibile continuare tra sessioni diverse
-   ❌ No visibility per Fabio su avanzamento

**Con tracking TODO_MASTER:**

-   ✅ Continuità perfetta tra sessioni AI
-   ✅ Zero duplicazione effort
-   ✅ Dependencies rispettate sempre
-   ✅ Progress trasparente e misurabile

**Questa regola è BLOCKING per progetto PA/Enterprise, non applicare a fix minori o feature isolate non PA.**

---

# **📂 STRUTTURA PROGETTO FLORENCEEGI**

## **MODULI PRINCIPALI:**

-   **EGI System**: Core blockchain integration (NFT/ASA management)
-   **Biography System**: Cultural heritage content management
-   **Marketplace**: Trading platform for digital assets
-   **GDPR Compliance**: Consent management, audit trail, data export
-   **PA Integration**: Specialized features for Public Administration clients

## **SERVIZI CHIAVE:**

-   `ConsentService`: GDPR consent management
-   `AuditLogService`: Complete audit trail for PA compliance
-   `EgiService`: EGI NFT/ASA operations
-   `BiographyService`: Content management for cultural assets
-   `MarketplaceService`: Trading operations

## **DOCUMENTAZIONE DI RIFERIMENTO:**

-   `docs/ai/context/PA_ENTERPRISE_*.md`: PA/Enterprise project documentation
-   `docs/ai/marketing/FlorenceEGI Brand Guidelines.md`: Complete brand guidelines
-   `docs/core/Oracode/*.md`: Technical architecture and patterns

---

# **🎯 QUANDO APPLICARE QUESTE REGOLE**

**Applica le regole FlorenceEGI-specific quando:**

-   ✅ Lavori su feature legate a PA/Enterprise
-   ✅ Crei/modifici UI (Brand Guidelines obbligatorie)
-   ✅ Implementi funzionalità blockchain (MiCA-SAFE obbligatorio)
-   ✅ Lavori su task del PA_ENTERPRISE_TODO_MASTER
-   ✅ Crei componenti che devono resistere ad audit PA

**NON applicare quando:**

-   ❌ Lavori su progetti esterni a FlorenceEGI
-   ❌ Fix minori non correlati a PA/Enterprise
-   ❌ Test o esperimenti in branch isolati

---

# **🔗 RIFERIMENTI**

**Regole universali OS3.0:**

-   `cursorrules_PADMIN_D_CURTIS_OS3_INTEGRATED.md` - Documento master completo
-   `cursorrules_OS3_QUICK_REFERENCE_CARD.md` - Quick reference rapida
-   `cursorrules_CURSOR_COPILOT_ROLES.md` - Partnership Fabio/Padmin

**Documentazione tecnica:**

-   `docs/core/Oracode/` - Architecture patterns e technical docs
-   `docs/ai/context/` - Context e implementation guides
-   `docs/ai/marketing/` - Brand guidelines e marketing docs

---

**Version**: 1.0.0  
**Date**: 2025-10-28  
**Status**: PRODUCTION READY  
**Scope**: FlorenceEGI Project Only

---

**Note:**

-   Questo file completa le regole universali OS3.0 con le specifiche FlorenceEGI
-   In caso di conflitto, le regole FlorenceEGI-specific hanno precedenza nel contesto di questo progetto
-   Le regole P0 universali (REGOLA ZERO, I18N, etc.) rimangono sempre valide
