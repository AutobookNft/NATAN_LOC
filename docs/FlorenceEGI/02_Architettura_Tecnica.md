# FlorenceEGI – Architettura Tecnica

## Stack Tecnologico

FlorenceEGI è un **SaaS multi-tenant** con architettura hub-and-spoke:

- **FlorenceEGI Core**: Hub centrale (governance, auth, billing, audit)
- **Tenant Specializzati**: NATAN (AI), FlorenceArtEGI (Arte & Marketplace)
- **Blockchain Layer**: Algorand (Protocol Layer)
- **Marketplace Pubblico**: Discovery, listing, transazioni P2P

---

## FlorenceEGI Core (Hub Centrale)

### Responsabilità

1. **Governance**: Policy centrali, controllo accessi globale
2. **Autenticazione e Onboarding**:
   - Weak Registration (email/password)
   - Strong Registration (SPID, CIE, OTP)
3. **Billing**: Gestione pagamenti, fatturazione, PSP integration
4. **ULM/UEM (UltraLogManager / UltraErrorManager)**: Logging centralizzato, error tracking
5. **Audit Trail**: Tracciabilità completa operazioni
6. **Compliance**: GDPR-by-design, ConsentService
7. **Registro Tenant**: Gestione tenant attivi e configurazioni

---

## Tenant Specializzati

### NATAN (Neural Assistant for Technical Art Navigation)

**Tenant funzionale** dedicato a:

- Assistenza documentale AI
- Notarizzazione documenti
- Servizi RAG (Retrieval-Augmented Generation)
- Automazioni intelligenti per enti pubblici e privati

**NATAN Market Engine** (cuore AMMk):

- **Valuation**: Valore, floor price, traiettoria asset
- **Activation**: Campagne, alert, suggerimenti attivati da trigger on/off-chain

### FlorenceArtEGI (Arte & Marketplace)

**Tenant verticale** per:

- Arte e marketplace NFT
- Collection workspace multi-utente
- Curatori digitali e mecenati

#### Collection Workspace

Ogni collection può gestire:

- **Max 8 wallet personali** (collaboratori)
- **Max 4 wallet di collection** (escrow, tesoreria, automazioni)
- **Totale: 12 slot** (4 riservati al core per rispettare limite Algorand di 16 account per gruppo atomico - MaxTxGroupSize)

---

## AMMk (Asset Market Maker)

**Paradigma unico**: FlorenceEGI è il primo AMMk al mondo che origina, certifica, valuta e rende liquidi gli EGI.

### I Cinque Engine

#### 1. NATAN Market Engine

Intelligenza del tenant NATAN che rende la piattaforma un vero market maker:

**Valuation**:

- Definisce valore, floor price e traiettoria
- Analizza qualità, storico e domanda
- Algoritmi predittivi

**Activation**:

- Orchestrazione campagne marketing
- Alert e notifiche intelligenti
- Suggerimenti attivati da trigger on/off-chain

#### 2. Asset Engine

Gestisce il ciclo di vita dell'asset:

- Listing opere
- Aste automatiche
- Vendite secondarie
- Liquidità marketplace

#### 3. Distribution Engine

Automazione finanziaria:

- Royalty automatiche (4.5% piattaforma)
- Fee piattaforma
- Quota EPP (donazioni ambientali)
- Tracciabilità fiscale end-to-end

#### 4. Co-Creation Engine

Orchestra il flusso Creator/Co-Creator/Collector:

- Minting EGI
- Notarizzazione opere
- Firme digitali
- Catena di custodia

#### 5. Compliance Engine

GDPR by design e MiCA-safe:

- Audit trail completo
- Policy condivise tenant
- ConsentService
- ULM integration

---

## Tenancy & RBAC (Role-Based Access Control)

### Multi-Tenancy FlorenceEGI

Il core SaaS governa identità e permessi, mentre i tenant verticali applicano policy specifiche mantenendo sicurezza condivisa.

### Ruoli Globali (Core)

- **User** / **Creator** / **Collector**: Identità principali
- **Tenant Admin**: Gestione verticale tenant
- **Platform Admin**: Governance core

### Ruoli Locali (Tenant)

**NATAN**:

- Operatori RAG
- Notarizzazione
- Auditor

**FlorenceArtEGI**:

- Curator (curatore opere)
- Inspector (verifica autenticità)
- Marketplace Manager

**Collection Workspace**:

- Owner (proprietario collection)
- Editor (modifica metadati)
- Viewer (sola lettura)

### Enforcement

- **TenantResolver**: Risolve tenant da request
- **Policy Laravel**: Autorizzazione scope tenant_id
- **Wallet Registry**: Gestione sicura wallet (AES-256)

---

## Blockchain Algorand e Smart Contract

### Perché Algorand?

- **Carbon-Negative**: Blockchain sostenibile
- **Proof-of-Stake Pura**: Nessun mining, basso consumo
- **Scalabilità**: 1000+ TPS
- **Finalità Immediata**: 4.5 secondi per blocco
- **Sicurezza**: Byzantine fault tolerance

### Operazioni On-Chain

#### Mint ASA (Algorand Standard Asset)

```
Creazione token EGI su Algorand
Supply = 1 (NFT unico)
Metadata: nome, descrizione, immagine IPFS
Manager: wallet creator
Freeze/Clawback: disabilitati (proprietà vera)
```

#### Smart Contract per CoA (Certificate of Authenticity)

```
Certificato immutabile collegato a EGI
Hash crittografico opera
Timestamp creazione
Firma digitale creator
```

#### Escrow

Gestione sicura fondi durante transazioni:

- Atomic transfers (tutto o niente)
- Multi-signature wallet
- Time-locked contracts

#### Attestazioni (Provenance, Ownership, EPP Allocation)

```
Provenance: storia proprietari
Ownership: proprietario attuale
EPP Allocation: quota donata a progetti ambientali
```

### Smart Contract Intelligenti

**Caratteristica unica**: Emettono **hook/trigger** verso Event Bus per attivare azioni NATAN

**Flusso**:

```
Smart Contract → Event Bus → NATAN Actions
(on-chain)        (bridge)    (off-chain automation)
```

**Esempi trigger**:

- Vendita completata → Notifica creator + Aggiorna floor price
- Floor price superato → Alert collector
- Milestone community → Attiva campagna marketing

### Collegamento Fisico-Digitale

#### CoA Verificato

Certificato di autenticità blockchain-based per opere fisiche

#### QR/NFC Unidirezionali

- QR code stampato/applicato su opera fisica
- Link a pagina verifica pubblica
- Mostra hash, metadata, storia proprietari
- Nessuna chiave privata esposta

#### Hash Crittografici

SHA-256 dell'immagine opera + metadata → immutabilità verificabile

---

## Frontend e UX

### App Web

- **Framework**: Laravel 11+ (backend)
- **UI**: TypeScript + Tailwind CSS
- **Componenti**: Livewire / Filament (admin)

### Homepage e Discovery

- Opere in evidenza
- Drops trimestrali
- Trending collections
- Filtri avanzati (prezzo, categoria, EPP)

### NATAN Assistant (Chatbot Integrato)

- RAG-powered (Retrieval-Augmented Generation)
- Assistenza artisti (descrizioni SEO, strategie marketing)
- Assistenza collector (suggerimenti opere, artisti emergenti)

### Dashboard Utente

#### Collection Management

- Upload opere
- Metadata editing
- Invito collaboratori (max 8)
- Gestione wallet collection (max 4)

#### Stats e Analytics

- Vendite totali
- Royalty ricevute
- Floor price collection
- Engagement community

#### Notifications

- Vendite completate
- Nuove offerte
- Alert NATAN
- Scadenze fiscali

#### Personal Data

- Profilo pubblico verificato
- Storico co-creazioni
- Portfolio opere

#### Portfolio

- EGI posseduti
- EGI creati
- Valore totale

#### GDPR Controls

- Consensi gestiti
- Portabilità dati
- Diritto all'oblio

#### Admin Tools (per admin/curator)

- Moderazione contenuti
- Gestione utenti
- Report fiscali

#### Wallet Management

- Connessione wallet esterni (Pera, Defly)
- Wallet auto-generati (custodia tecnica limitata)
- Export chiavi private

### Marketplace Pubblico

- Discovery opere
- Listing vendita
- Transazioni P2P dirette
- Aste temporizzate

### Responsive Design

Mobile-first, ottimizzato per tutti i dispositivi

---

## Event Bus

Sistema che riceve **trigger on-chain e off-chain** e attiva azioni NATAN:

**Input**:

- Smart contract hooks
- User actions (like, follow)
- Time-based events (milestone, anniversario)

**Output**:

- Campagne marketing
- Notifiche push
- Suggerimenti prezzo
- Alert curatori

**Tecnologia**: Queue system (Laravel Horizon + Redis)

---

## Observability (ULM, UEM, Audit Trail, GDPR)

### UltraLogManager (ULM)

Logging centralizzato per tutti i tenant:

- Structured logging (JSON)
- Log levels (debug, info, warning, error, critical)
- Context enrichment (user_id, tenant_id, IP)
- Retention policy (90 giorni rolling)

### UltraErrorManager (UEM)

Error tracking e alerting:

- Cattura eccezioni
- Stack trace completo
- Error grouping
- Alert Slack/Email per errori critici

### Audit Trail

Tracciabilità completa operazioni sensibili:

- Chi ha fatto cosa, quando
- Immutabilità (append-only log)
- Firma digitale eventi
- Conservazione 10 anni

### GDPR Compliance

- **ConsentService**: Gestione consensi utente (versioning)
- **Right to Access**: Export dati personali (JSON/XML)
- **Right to Portability**: Download completo
- **Right to Erasure**: Cancellazione garantita (pseudonimizzazione blockchain)

---

## Diagramma Architetturale

```
┌─────────────────────────────────────────────────────────────────┐
│                    Users / Companies / PA                        │
└────────────────────────┬────────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────────┐
│              FlorenceEGI Core (SaaS Hub)                         │
│  Governance · Auth · Billing · ULM/UEM · Audit · Tenant Ops     │
└───────┬───────────────────────┬──────────────────────────────────┘
        │                       │
        ▼                       ▼
┌──────────────────┐   ┌────────────────────────────────────┐
│ Tenant: NATAN    │   │ Tenant: FlorenceArtEGI             │
│ (AI, RAG, Notary)│   │ (Arte & Marketplace)               │
│ → Market Engine  │   │ → Collection Workspace             │
└──────────────────┘   └────────────────────────────────────┘
        │                       │
        └───────────┬───────────┘
                    ▼
┌────────────────────────────────────────────────────────────────┐
│ AMMk Engines (coordinati dal Core)                             │
│ NATAN Market | Asset | Distribution | Co-Creation | Compliance │
└───────┬──────────────┬──────────────┬────────────────────────┘
        │              │              │
        ▼              ▼              ▼
┌────────────┐   ┌────────────┐   ┌──────────────────────────┐
│Marketplace │   │ Event Bus  │   │ Algorand Blockchain      │
│  Pubblico  │   │(Trigger IA)│   │ ASA · Smart Contracts    │
└────────────┘   └────────────┘   └──────────────────────────┘
                                            │
                                            ▼
                                   Observability & Compliance
                                   (ULM, UEM, Audit, GDPR)
```

---

## Principio Fondamentale

**FlorenceEGI certifica, non custodisce.**

Il merchant/creator resta proprietario del bene; la piattaforma garantisce solo la verità della proprietà e dell'autenticità attraverso la blockchain.
