# FlorenceEGI – Compliance e Governance

## Compliance GDPR (GDPR-by-Design)

FlorenceEGI integra la protezione dei dati **fin dalla progettazione** (privacy by design).

### UltraLogManager (ULM)

Sistema di registrazione eventi per garantire auditabilità completa:

- **Structured Logging**: Tutti gli eventi in formato JSON strutturato
- **Context Enrichment**: user_id, tenant_id, IP, timestamp, session_id
- **Log Levels**: debug, info, warning, error, critical
- **Retention Policy**: 90 giorni rolling (configurable per tenant)
- **Searchable**: Full-text search su tutti i log
- **Export**: CSV, JSON, XML per audit esterni

### AuditLogService

Servizio dedicato per **audit trail verificabili**:

- **Immutabilità**: Log append-only, mai modificabili
- **Firma Digitale**: Ogni evento firmato crittograficamente
- **Catena di Custodia**: Traccia completa "chi ha fatto cosa, quando"
- **Conservazione**: 10 anni (obbligo normativo)
- **Verification**: Hash chain per verificare integrità storica

### ConsentService

Gestione e versioning **consensi utente** secondo GDPR:

- **Granularità**: Consensi specifici (marketing, analytics, profilazione)
- **Versioning**: Storico modifiche consensi nel tempo
- **Revocabilità**: Utente può revocare in qualsiasi momento
- **Proof**: Timestamp + IP + signature digitale
- **Dashboard**: Interfaccia user-friendly per gestione

### Diritti Utente (GDPR Art. 15-20)

#### Right to Access (Art. 15)

Utente può richiedere copia completa dati personali:

- Export automatico in formato machine-readable (JSON/XML)
- Include: profilo, transazioni, log, consensi

#### Right to Portability (Art. 20)

Download dati in formato strutturato per trasferimento a altro provider:

- Standard format (JSON-LD, CSV)
- API disponibile per integrazione diretta

#### Right to Erasure (Art. 17 - Diritto all'Oblio)

Cancellazione garantita con **eccezioni blockchain**:

- **Dati off-chain**: Cancellazione completa da database
- **Dati on-chain**: Pseudonimizzazione (hash al posto dati personali)
- **Spiegazione**: Blockchain immutabile per design, ma nessun dato sensibile on-chain

### Conservazione Digitale (10 Anni)

Obbligo normativo per documenti fiscali:

- **Provider Accreditato**: Conservazione sostitutiva certificata
- **Firma Digitale**: Tutti i documenti firmati digitalmente
- **Marca Temporale**: Timestamp certificato
- **Replica Geografica**: Backup multi-region
- **Verifica Integrità**: Hash SHA-256 periodico

---

## Compliance MiCA (Markets in Crypto-Assets)

### Principio MiCA-safe

FlorenceEGI è **progettata per operare fuori dal perimetro MiCA**, evitando attività che richiederebbero licenze CASP.

### Cosa FlorenceEGI NON Fa

❌ **Non Custodisce Fondi Crypto per Terzi**

- Nessun wallet omnibus
- Nessun deposito crypto utenti
- Wallet auto-generati contengono SOLO NFT unici (no criptovalute)

❌ **Non Fa da Exchange Crypto/FIAT**

- Nessuna conversione crypto ↔ fiat
- Nessun order book
- Nessuna liquidità crypto gestita

❌ **Non Processa Pagamenti Crypto Direttamente**

- Pagamenti crypto gestiti da PSP partner autorizzati (CASP/EMI)
- FlorenceEGI riceve solo notifica pagamento completato

### Cosa FlorenceEGI FA

✅ **Incassa FIAT tramite PSP**

- Payment Service Provider regolamentati (Stripe, Adyen, etc.)
- FlorenceEGI riceve solo propria fee in FIAT
- Nessun fondo utente transita per la piattaforma

✅ **Emette e Trasferisce EGI (NFT Unici)**

- Minting ASA su Algorand
- Transfer NFT a wallet destinatario
- Nessun valore monetario intrinseco nell'NFT

✅ **Scrive Anchor Hash su Blockchain**

- Impronta digitale documenti
- Timestamp notarizzazione
- Nessun dato personale on-chain

✅ **Gestisce QR e Verifica Pubblica**

- QR code per verifica autenticità
- Pagina pubblica verifica (no login required)
- Collegamento fisico ↔ digitale

✅ **Calcola Royalty per PSP**

- Istruzioni di ripartizione fondi
- PSP esegue split payment
- FlorenceEGI non tocca mai i fondi

---

## Governance Duale

Equilibrio tra **impresa** e **missione** attraverso due entità complementari.

### FlorenceEGI SRL (Motore Operativo)

**Responsabilità**:

- Sviluppo tecnologico
- Partnership strategiche
- Marketing e revenue
- Crescita scalabile
- Gestione operativa quotidiana

**Focus**: Sostenibilità economica e innovazione tecnologica

### Associazione Frangette APS (Custode dei Valori)

**Responsabilità**:

- Vigila sui principi fondativi
- Tutela destinazione 20% EPP (obbligo statutario)
- Garantisce coerenza artistico-sociale
- Protegge la missione ambientale
- Veto su decisioni contrarie ai valori

**Focus**: Etica, impatto sociale e ambientale

### Come Funziona

```
Decisione Strategica
        ↓
┌───────────────────┐      ┌─────────────────────┐
│ FlorenceEGI SRL   │ ←→   │ Associazione APS    │
│ (Operativa)       │      │ (Etica)             │
└───────────────────┘      └─────────────────────┘
        ↓                           ↓
   Se allineata                Se NON allineata
        ↓                           ↓
   APPROVATA                     VETO
```

**Esempio Pratico**:

- SRL propone: "Ridurre quota EPP da 20% a 10% per aumentare margini"
- APS: **VETO** (viola statuto e missione)
- Decisione NON passa

**Benefici**:

- Previene greenwashing
- Garantisce impatto reale
- Tutela valori fondativi
- Equilibrio profit/purpose

---

## Fee Dinamiche (Economia Rigenerativa)

### Principio

Le commissioni **si riducono** al crescere della community, creando un'economia virtuosa.

### Formula

```
Fee_Attuale = Fee_Base × (1 - Community_Discount)

Community_Discount = min(User_Count / 10000 × 0.1, 0.5)
```

**Esempio**:

- Fee base minting: 10%
- Community 5000 utenti → Discount 5% → Fee finale 9.5%
- Community 10.000 utenti → Discount 10% → Fee finale 9%
- Community 50.000 utenti → Discount MAX 50% → Fee finale 5%

### Vantaggi

- **Incentivo crescita**: Più utenti = meno costi per tutti
- **Network effect**: Valore aumenta con dimensione rete
- **Sostenibilità**: Compensazione volume/prezzo
- **Fairness**: Early adopter pagano di più, late adopter meno (ma early hanno vantaggi first-mover)

---

## Token Ecosystem (Non Speculativo)

### Equilibrium (Token Governance e Premi)

**NON è una criptovaluta speculativa**. È un token **utilitario** per:

- **Ranking Meritocratico**: Score basato su contributi community
- **Premi Automatici**: Reward per milestone (es. 100 vendite, curatore mese)
- **Voting Power**: Governance proposals (feature requests, EPP selection)
- **Accesso Esclusivo**: Early access drops, eventi riservati

**Non ha**:

- Prezzo di mercato
- Exchange listing
- Valore monetario diretto

### Egili (Micro-Unità)

**1 Equilibrium = 1.000.000 Egili**

Usato per:

- Micro-interazioni (like, comment, share)
- Gamification (badge, achievement)
- Tipping artisti (donazioni piccole)

---

## Mecenatismo e Nuove Professioni

### Il Mecenate Digitale

**Profilo Pubblico Verificato** che include:

- **Storico Co-Creazioni**: Tutte le opere sostenute (minting)
- **Portfolio Opere Sostenute**: Valore aggregato, crescita nel tempo
- **Ranking Basato su Impatto Reale**:
  - Numero opere sostenute
  - Quota EPP donata totale
  - Artisti emergenti scoperti
  - Engagement community

**Vantaggi**:

- Visibilità permanente come sostenitore
- Reputazione verificabile on-chain
- Network privilegiato con artisti
- Inviti eventi esclusivi

### Il Curatore Digitale

**Professione emergente** con responsabilità:

- **Selezione e Valorizzazione Opere**: Cura collezioni tematiche
- **Organizzazione Collezioni Tematiche**: Eventi espositivi digitali
- **Advisory per Collector**: Consulenza acquisti
- **Carriera Professionale Verificabile**: Portfolio curatoriale pubblico

**Strumenti Piattaforma**:

- Dashboard curatoriale
- Analytics opere curate
- Commission tracking (% su vendite influenzate)
- Badge verificato "Curator Pro"

---

## EPP: Impatto Ambientale Nativo

### Environmental Protection Projects Integrati

**Principio**: Non è un'opzione etica, è una **legge di equilibrio**.

> _"Ogni atto economico genera un atto rigenerativo."_

### Meccanismo Automatico

1. **Vendita EGI** → Split payment automatico
2. **Quota EPP** (default 20%) → Wallet EPP
3. **Trasferimento on-chain** → Immutabile e verificabile
4. **Zero intermediazione umana** → Trustless

### Progetti Verificati

**Criteri Selezione EPP**:

- Certificazione impatto (es. Gold Standard, VCS)
- Tracciabilità progetti
- Reporting trimestrale
- Audit indipendente annuale

**Categorie**:

- **Riforestazione**: Piantumazione, habitat restoration
- **Ocean Cleanup**: Rimozione plastica oceani
- **Biodiversità**: Protezione specie a rischio
- **Renewable Energy**: Progetti energia pulita

### Tracciabilità Impatto

Dashboard pubblica EPP mostra:

- Fondi ricevuti totali
- Progetti attivi
- KPI impatto (es. alberi piantati, kg plastica rimossa)
- Report trimestrali verificati

---

## Principio Fondamentale Compliance

**FlorenceEGI certifica, non custodisce. Facilita, non intermedia. Garantisce trasparenza, non opacità.**

Ogni flusso economico è tracciabile, ricostruibile e accessibile per garantire massima chiarezza grazie alla blockchain e ai sistemi di audit integrati.
