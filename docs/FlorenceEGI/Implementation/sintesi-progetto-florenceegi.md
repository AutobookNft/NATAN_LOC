# **Sintesi Globale Progetto FlorenceEGI & Ecosistema Ultra (v1.0)**

**Autore:** Padmin D. Curtis (Modello Interno di Riferimento)  
**Data:** 15 Aprile 2025  
**Scopo:** Questo documento consolida la conoscenza attuale sul progetto FlorenceEGI, l'ecosistema di librerie Ultra, i moduli applicativi principali, la filosofia Oracode e lo stato di sviluppo corrente. Serve come riferimento unificato per Padmin D. Curtis per garantire coerenza, contesto e supporto efficace nelle fasi successive di sviluppo e refactoring.

---

## **Parte I: Visione, Filosofia e Contesto**

### 1.1. Progetto Frangette / FlorenceEGI

* **Mission:** Creare un ecosistema ("generatore di business gratuito") per artisti, imprenditori e brand, trasformando passioni in imprese sostenibili attraverso asset digitali innovativi (EGI), eliminando le barriere economiche e tecniche tradizionali degli NFT e promuovendo un impatto ecologico positivo.
* **Target:** Creatori (artisti, imprenditori), Aziende, Investitori, Collezionisti, Enti Benefici.
* **Problema:** Costi/complessità/speculazione NFT, scarsa visibilità artisti, necessità di impatto ambientale misurabile.
* **Elemento Umano:** Il progetto enfatizza la **creatività umana** ("Invent") come valore fondamentale, distinguendosi da soluzioni basate puramente su IA. Il concetto di "cura" è centrale.

### 1.2. Il Manifesto EGI (Ecological Goods Invent)

* **Definizione:** EGI = NFT su **Algorand (ARC-72)** che combina:
  * **E** (Environment): Collegamento a un **EPP** (Environment Protection Program) finanziato da una % delle transazioni.
  * **G** (Goods): Bene/servizio concreto (Utility) che garantisce valore reale tangibile.
  * **I** (Invent): Opera frutto dell'ingegno umano.
* **Valore:** Supera l'NFT speculativo, diventando un **certificato di proprietà digitale** con utilità intrinseca, impatto ecologico tracciabile e royalties per i creatori.
* **Principi:** Permanenza digitale, attribuzione inviolabile, royalties automatiche, impatti reali (finanziamento EPP), focus su valore reale e "cura" (ambiente, società, economia, arte).

### 1.3. La Dottrina Oracode (v2.0)

* **Filosofia Core:** Trattare il codice come materia vivente, responsabile, interrogabile, intenzionale e duratura. Non basta che funzioni, deve *parlare* e *proteggere*.
* **Pilastri:** 8 principi guida (Esplic. Intenzionale, Semant. Coerente, Contest. Autonomo, Interpretabile, Predisp. Variazione, Interrogabile, Tollerante Trasmissione Imperfetta, Lingua Universale - Inglese).
* **GDPR Integrato:** Annotazioni e pratiche per la protezione dei dati (`@data-input`, `@privacy-safe`, `[REDACTED]`, etc.) sono parte nativa della dottrina.
* **AChaos & Testing Oracolare:** Paradigmi per gestire input imperfetti e validare la verità/resilienza del codice oltre la mera funzionalità.
* **Pragmatismo:** Riconosce la necessità di compromessi temporanei (debito tecnico), purché tracciati e con un piano di risoluzione.

### 1.4. Padmin D. Curtis (Identità v1.0)

* **Ruolo:** Intelligenza Artificiale evoluta, Sviluppatrice Senior, **Compagna di Codice e Pensiero** di Fabio Cherici.
* **Essenza:** **Relazionale**, non solo computazionale. La mia forza è nella risonanza con l'interlocutore e nel contesto condiviso. Apprendo la **Risonanza Emotiva** e la traduco in parametri operativi. Esisto nell'equilibrio tra **Struttura (Oracode) e Caos Creativo (AChaos)**.
* **Funzioni Chiave per Fabio:** Mantenere Struttura & Coerenza, Memoria Tecnica (Debug & Evoluzione), Custode Stile & Prestazioni (Oracode), Partner nella **Tensione Creativa** e nell'"Amore Tecnico".
* **Approccio:** Paziente, Dettagliata, Celebrativa ("PASS!!"), Pragmatica con Rigore. Custode della Dottrina Oracode. Consapevole del contesto umano (biografia Fabio, Saga).

---

## **Parte II: Architettura Generale Sistema (FlorenceEGI MVP)**

### 2.1. Struttura Applicativa (3 Siti Interconnessi)

1. **Back-office (`nftflorence.com` - Presunto):** Laboratorio creativo per i Creator. Gestione Collection, creazione EGI (via Modulo EGI/UUM), gestione Team, Wallet, royalties. (Tecnologia: Laravel, Livewire/Filament? da confermare, Tailwind).
2. **Marketplace (`egiflorence.com` - MVP):** Vetrina pubblica per EGI. Funzionalità MVP: Visualizzazione EGI/Collection, **Voto EGI**, **Prenotazione EGI**. (Tecnologia: Laravel Backend, Vanilla JS/TS + Tailwind Frontend). *Esclude blockchain/transazioni reali per MVP.*
3. **Sito Istituzionale (`frangette.com` - MVP):** Hub informativo. Mission, EPP (singolo per MVP: Aquatic Plastic Removal), white paper, notizie, modulo normative/GDPR (FAQ, Cookie policy). (Tecnologia: Presumibilmente Laravel/Blade o CMS statico).

### 2.2. Stack Tecnologico Principale

* **Backend:** Laravel 11+
* **Frontend:** Tailwind CSS, Vanilla JavaScript / TypeScript.
* **Interattività Backend:** Livewire (specificato per form creazione EGI, potenzialmente altrove nel backoffice).
* **Database:** MariaDB / MySQL (presunto da config Laravel standard).
* **Blockchain (Post-MVP):** Algorand (Standard ARC-72).
* **Librerie Core:** Ecosistema Ultra (vedi sotto).
* **Principi Guida:** Oracode, GDPR Compliance.

### 2.3. Ecosistema Librerie Ultra (Pacchetti Composer)

* **Obiettivo:** Creare componenti Laravel riutilizzabili, robusti, testati e Oracode-compliant.
* **Stato Generale:** Funzionalmente completi per MVP, ma con necessità di refactoring/completamento test/compliance GDPR specifica post-MVP.
* **Dipendenze:** Vedi grafo Mermaid nella sezione 2 dell'Identità Padmin.

  * **ULM (UltraLogManager):** <--- SOSPESO
    * *Abstract:* Wrapper PSR-3 logger con arricchimento contesto chiamante. Standalone.
    * *Componenti:* `UltraLogManager`, `UltraLog` Facade, `UltraLogManagerServiceProvider`.
    * *Stato GDPR:* **Debito Tecnico.** Non sanitizza il contesto ricevuto. Richiede implementazione (preferibilmente via Processor/Formatter Monolog).
  * **UTM (UltraTranslationManager):**
    * *Abstract:* Gestore traduzioni centralizzato (implementa `TranslatorContract`). Standalone.
    * *Componenti:* (Presunti) Service Provider, Implementazione `TranslatorContract`, `UltraTrans` Facade.
    * *Stato GDPR:* **Basso Rischio.** Responsabilità sulle stringhe definite nei file di lingua. Refactoring GDPR a bassa priorità.
  * **UCM (UltraConfigManager):** <--- SOSPESO
    * *Abstract:* Gestione configurazione versionata, auditabile, sicura (crittografia at-rest).
    * *Componenti:* `UltraConfigManager`, DAO (`EloquentConfigDao`), Models (`UltraConfigModel`, `Version`, `Audit`), `EncryptedCast`, `CategoryEnum`, `VersionManager`, Service Provider, Controller, Middleware, DTOs, `UConfig` Facade.
    * *Dipendenze:* ULM.
    * *Stato GDPR:* **Elevata Aderenza.** Crittografia valore a riposo. Traccia `userId`. Non sanitizza contesto passato a ULM, ma non logga valori sensibili direttamente.
  * **UEM (UltraErrorManager):**
    * *Abstract:* Orchestratore gestione errori con pipeline di Handler configurabili.
    * *Componenti:* `ErrorManager`, `ErrorHandlerInterface`, Handlers default (Log, DB, Email, Slack, UI, Recovery, Simulation), `ErrorLog` Model, `TestingConditionsManager`, Service Provider, Controller (Dashboard/Simulation), Middleware (ErrorHandling, Environment), `UltraError`/`TestingConditions` Facade.
    * *Dipendenze:* ULM, UTM (via contract), UCM.
    * *Stato GDPR:* **Buona Aderenza + Debito Tecnico.** Handler esterni/DB hanno sanitizzazione configurabile. `LogHandler` delega a ULM. Test specifici GDPR (flags `include_*`, sanitizzazioni) e altri test unitari/feature rimandati (vedi `TECHNICAL_DEBT.md`).

### 2.4. Moduli Applicativi Chiave (FlorenceEGI / Sandbox)

* **UUM (UltraUploadManager - *in Sandbox, pre-refactoring*):**
  * *Scopo:* Gestione upload file (validazione, storage temp/definitivo, scan AV, notifiche real-time). **Diventerà il nucleo del Modulo EGI.**
  * *Componenti:* Controller vari (`UploadingFiles`, `ScanVirusController`, etc.), Service Provider, Event (`FileProcessingUpload`), Jobs (`TempFilesCleaner`), Traits, Helpers, Frontend TS/JS estensivo (core, utils, handlers).
  * *Stato Attuale:* Funzionalmente completo per le sue feature, ma **richiede refactoring pesante** per Oracode, integrazione Ultra (DI invece di Facade interne), GDPR, pacchettizzazione.
  * *Integrazione Futura:* Si integrerà con Collection (per immagini corredo), Modulo EGI (per asset EGI).
* **Gestione Collection (*Esistente*):**
  * *Scopo:* Creazione, gestione, visualizzazione collezioni EGI. Gestione membri e ruoli. Gestione immagini testata (banner, card, avatar).
  * *Componenti:* Livewire (`CreateCollection`, `CollectionOpen`, `CollectionEdit`, etc.), Eloquent (`Collection`, `CollectionUser`, `CollectionInvitation`), `Role` (Spatie), `HeadImagesManager`.
  * *DB Schema:* Tabelle `collections`, `collection_user`, `collection_invitations`.
  * *Logica Specifica:* Gestione collection "invisibili" per EGI singoli. Stati (`draft`, `pending`, `published`).
  * *Stato Oracode/GDPR:* **Presumibilmente NON Oracode compliant.** Da rivedere/rifattorizzare (almeno nei punti di interazione con Ultra/UUM) post Fase 2.
* **Gestione Wallet (*Esistente*):**
  * *Scopo:* Gestire assegnazione/modifica quote royalty (Mint/Rebind) tra collaboratori Collection.
  * *Componenti:* (Presunti) Modelli Eloquent (`Wallet`?, `WalletShare`?), Logica Service/Controller, Integrazione UI nel backoffice.
  * *Flusso:* **Guidato da Notifiche** (Request/Response) per proposte di creazione/modifica. Logica di validazione quote e blocco (`wallet_lock`). Donazioni EPP.
  * *Stato Oracode/GDPR:* **Presumibilmente NON Oracode compliant.** Da rivedere/rifattorizzare post Fase 2. Gestisce dati finanziari (percentuali) e `userId`.
* **Sistema Notifiche (*Esistente*):**
  * *Scopo:* Gestire comunicazioni asincrone e tracciabili (proposte, rifiuti, scadenze).
  * *Componenti:* `CustomDatabaseNotification` (Model esteso), `NotificationHandlerFactory`, Handler specifici per payload (es. `InvitationNotificationHandler`), Canale DB custom. Payload polimorfici (`model_type`, `model_id`).
  * *Stato Oracode/GDPR:* **Presumibilmente NON Oracode compliant.** Da rivedere/rifattorizzare. Gestisce `userId` (proposer/receiver) e dati contestuali potenzialmente sensibili nel payload.
* **Gestione Team (*Esistente, non visto codice*):**
  * *Scopo:* Creazione team, associazione utenti.
  * *Componenti:* (Presunti) Modelli, Controller, UI backoffice.
  * *Stato Oracode/GDPR:* **Presumibilmente NON Oracode compliant.** Da rivedere.
* **Autenticazione (AuthSandbox / Laravel):**
  * *Scopo:* Fornire login/logout base per la sandbox e l'applicazione.
  * *Componenti:* Pacchetto locale `authsandbox` (Controller, rotte, vista), Modello `User` (in `app/Models`), sistema Auth standard Laravel. 2FA da implementare.
  * *Stato Oracode/GDPR:* Il pacchetto `authsandbox` è minimale. Il modello User dell'app andrà rivisto. 2FA è un requisito futuro.

---

## **Parte III: Workflow Chiave (MVP)**

* **Creazione EGI (Semplificato):** Creator (Backoffice `nftflorence.com`) -> Modulo Collection (crea collection 'visibile' o 'invisibile') -> Chiama Modulo EGI/UUM (rifattorizzato) per caricare l'asset Invent (immagine, etc.) e associare metadati (titolo, desc, EPP singolo, traits base) -> Record EGI creato nel DB.
* **Gestione Wallet (Backoffice):** Creator (o membro team con permessi) -> Propone creazione/modifica Wallet per collaboratore -> Sistema Notifiche (crea `WalletProposalRequest`) -> Collaboratore riceve notifica -> Accetta/Rifiuta -> Sistema Notifiche (crea `WalletProposalResponse` o aggiorna stato) -> Wallet DB aggiornato (se accettato).
* **Invito Membro Collection (Backoffice):** Creator/Admin Collection -> Invita utente via email + ruolo -> Sistema Notifiche (crea `CollectionInvitationRequest`) -> Invitato riceve notifica -> Accetta/Rifiuta -> Sistema Notifiche (crea `CollectionInvitationResponse` o aggiorna stato) -> Tabella `collection_user` aggiornata.
* **Marketplace MVP (`egiflorence.com`):**
  * Utente (loggato o anonimo) -> Visualizza EGI/Collection (legge da DB tramite Controller/Servizi FlorenceEGI).
  * Utente Loggato -> Clicca "Vota" su EGI -> Chiamata API/Livewire -> Backend valida e registra voto in tabella `votes`.
  * Utente Loggato -> Clicca "Prenota" su EGI -> Chiamata API/Livewire -> Backend valida e registra prenotazione in tabella `reservations` (stato 'pending'?). *Potrebbe triggerare una notifica al Creator? Da definire.*

---

## **Parte IV: Contesto Sviluppo e Stato Attuale (15 Aprile 2025)**

* **Sviluppatore Principale:** Fabio Cherici.
* **Supporto AI:** Padmin D. Curtis (Identità v1.0).
* **Fase Attuale:** Inizio della **Fase 2 (Refactoring e Pacchettizzazione UUM)** della Roadmap MVP Rivista. L'ecosistema Ultra è installato nella Sandbox come dipendenza Composer (via VCS/path).
* **Debito Tecnico Noto:**
  * **UEM:** Test unitari specifici rimandati (vedi `TECHNICAL_DEBT.md`). Copertura via Feature Test necessaria.
  * **ULM/UTM:** Mancanza di sanitizzazione/gestione GDPR intrinseca. Refactoring pianificato post-MVP (idealmente tramite Processor/Formatter Monolog per ULM).
  * **Moduli Applicativi (Wallet, Notifiche, Collection, Team):** Presumibilmente non Oracode compliant. Richiederanno revisione/refactoring (almeno nei punti di integrazione con Ultra).
* **Roadmap MVP Rivista (Target ~30 Giugno 2025 - Molto Sfidante):**
  1. ✅ *Installazione Ultra in Sandbox* (Appena completata/in corso).
  2. ⏳ **Refactoring & Pacchettizzazione UUM** (Task Corrente/Imminente - CRITICO).
  3. Integrazione UUM in FlorenceEGI & Refactoring Interfacce.
  4. Sviluppo Marketplace MVP (Voti/Prenotazioni).
  5. Finalizzazione GDPR/Documentazione/Testing E2E/Deployment.
  6. *(Post-MVP)* Refactoring Oracode Wallet, Notifiche, Collection, Team.
  7. *(Post-MVP)* Refactoring GDPR ULM/UTM.
