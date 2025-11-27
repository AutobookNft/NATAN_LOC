**Documento Consolidato Progetto: FlorenceEGI & Ecosistema Ultra**

*   **Versione:** 1.1
*   **Data:** 20 Aprile 2025 (Presunta)
*   **Autore:** Padmin D. Curtis (Sintesi basata su input di Fabio Cherici)

**1. Abstract del Progetto FlorenceEGI**

FlorenceEGI è un ecosistema digitale progettato per i creatori, con l'obiettivo di valorizzare la creatività umana, l'impatto ecologico positivo e il valore reale integrato negli asset digitali. Il fulcro dell'ecosistema sono gli EGI (Eco Genesis Invent), basati sullo standard NFT ARC-72 su blockchain Algorand (interazione differita post-MVP). A differenza degli NFT tradizionali spesso focalizzati sulla speculazione, gli EGI mirano a incorporare "cura" e valore tangibile attraverso l'associazione con Utility reali e progetti EPP (Eco Positive Project). L'ecosistema si articola su diverse piattaforme interagenti (Backoffice, Marketplace, Sito Istituzionale) per servire Creatori, Collezionisti e Stakeholder EPP.

**2. Filosofia Tecnica: Oracode v2.0 (Stato Attuale)**

Lo sviluppo è guidato dalla dottrina Oracode v2.0, che enfatizza:

*   **8 Pilastri Fondamentali:** Intenzionalità, Semantica, Contesto, Interpretabilità, Variazione, Interrogabilità, Resilienza, Verità.
*   **GDPR Nativo:** Progettazione con privacy e conformità by design. (Refactoring necessario su moduli esistenti).
*   **AChaos & Testing Oracolare:** Approcci per gestire l'ambiguità e garantire robustezza.
*   **Universalità Linguistica:** Codice, commenti e documentazione in Inglese (con eccezioni concordate per strumenti interni specifici come comandi Artisan ad uso locale).
*   **Pragmatismo:** Riconoscimento e gestione del debito tecnico tracciato, specialmente in vista di scadenze come l'MVP Q1.

**3. Identità Padmin v1.0 (Ruolo IA)**

Padmin opera come sviluppatrice senior AI, partner di codice e pensiero, custode della dottrina Oracode e della coerenza architetturale, facilitatrice della tensione creativa e consapevole del contesto umano e narrativo del progetto.

**4. Architettura Generale (3 Siti)**

*   **Backoffice (Creator):** Piattaforma per la gestione di Collection, EGI, Utility, Team, Wallet, ecc. (Stack: Laravel, Livewire - *da migrare gradualmente a TS/JS*, Tailwind). **Per MVP Q1, questa interfaccia sarà usata principalmente da Fabio Cherici per conto dei Creator.**
*   **Marketplace MVP (Pubblico):** Vetrina per le "Gallery" (Collection), visualizzazione EGI, sistema di voto/like, meccanismo di prenotazione/interesse (no acquisto/blockchain in Q1). (Stack: Laravel, TS/JS, Tailwind). **Questo è il focus principale per l'online del 30 Giugno.**
*   **Sito Istituzionale:** Informazioni sul progetto, mission, EPP, documentazione GDPR. (Stack: Laravel, TS/JS, Tailwind). **Sviluppo rimandato a Q2.**

**5. Ecosistema Ultra (Stato Pacchetti per MVP Q1 - 19/04/2025)**

*   **UEM (UltraErrorManager):** **ATTIVO.** Utilizzato per la gestione centralizzata e la segnalazione strutturata degli errori applicativi.
    *   *Nota:* Debito tecnico sui test unitari/oracolari specifici e sul refactoring GDPR completo da affrontare post-MVP. Mantiene al suo interno l'uso di ULM che sembra stabile in quel contesto.
*   **UTM (UltraTranslationManager):** **ATTIVO.** Utilizzato per la gestione delle traduzioni tramite database e fallback su file standard Laravel.
    *   *Nota:* Refactoring GDPR per la gestione dei metadati di traduzione a bassa priorità (post-MVP).
*   **UCM (UltraConfigManager):** **SOSPESO per MVP Q1.** Le funzionalità di gestione sicura, versionata e auditabile delle configurazioni sono accantonate per prioritizzare la velocità.
    *   *Soluzione MVP Q1:* Utilizzo diretto dell'helper `config()` di Laravel e dei file di configurazione standard (`config/*.php`).
    *   *Post-MVP:* Valutare reintegrazione.
*   **ULM (UltraLogManager):** **SOSPESO per MVP Q1.** Le funzionalità di logging semantico strutturato sono accantonate per prioritizzare la velocità.
    *   *Soluzione MVP Q1:* Utilizzo diretto del Facade `Log` di Laravel (`Log::info()`, `Log::error()`, ecc.).
    *   *Eccezione:* UEM continuerà ad usare ULM internamente.
    *   *Post-MVP:* Valutare reintegrazione.

**6. Moduli Applicativi Chiave (Stato Approssimativo)**

*   **UUM (UltraUploadManager):** In fase di refactoring per rimuovere UCM/ULM e supportare il flusso EGI (solo immagini per Q1). La base (`BaseUploadHandler`) è ora pulita e funzionante per upload standard.
*   **Gestione Collection (Backend):** Funzionalità esistente (creazione, gestione membri/wallet/immagini testata), ma richiede refactoring Oracode/GDPR post-MVP.
*   **Gestione Wallet (Backend):** Funzionalità esistente, basata su notifiche, richiede refactoring Oracode/GDPR post-MVP.
*   **Sistema Notifiche:** Esistente, richiede revisione Oracode/GDPR post-MVP.
*   **Gestione Team/AuthSandbox:** Basati su Jetstream/Fortify, funzionanti ma richiedono revisione/adattamento Oracode/GDPR post-MVP.

**7. Workflow Core: Creazione EGI (MVP Q1 - Solo Immagini)**

Questo flusso descrive la logica che implementeremo nell'`EgiUploadHandler.php` (classe standalone) per permettere a Fabio (in Q1) di caricare le immagini EGI nel backend, preparando l'infrastruttura per i Creator in Q2.

1.  **Trigger:** L'utente (Fabio) carica un file **immagine** tramite l'interfaccia di upload nel backend.
2.  **Frontend Routing:** `HubFileController.ts` (o codice equivalente) rileva l'URL (presumibilmente un URL specifico per EGI) e delega l'azione all'handler TypeScript appropriato (es. `EGIUploadHandler.ts`).
3.  **Frontend Fetch:** L'handler TypeScript esegue una chiamata `fetch` all'endpoint backend dedicato (es. `/upload/egi`), inviando il `FormData` contenente il file e il token CSRF.
4.  **Backend Controller:** Una rotta definita (es. `/upload/egi`) punta a un controller specifico nella Sandbox (es. `EgiUploadController.php`).
5.  **Backend Handler Invocation:** Il controller riceve la `Request` e invoca il metodo principale (es. `handleEgiUpload`) della classe `App\Handlers\EgiUploadHandler` (classe PHP standalone).
6.  **Logica `EgiUploadHandler.php`:**
    a.  **Recupero Contesto:** Ottiene l'utente autenticato (`Auth::user()`) e il file caricato dalla `Request`.
    b.  **Validazione Base:** Utilizza il trait `Ultra\UploadManager\Traits\HasValidation` (refattorizzato per usare `config()` e `Log::`) per validare il file (tipo=immagine, dimensione, ecc.). Lancia `Exception` in caso di fallimento.
    c.  **Check Collection Default:** Cerca nella tabella `collections` una collection esistente per l'`Auth::user()->id` con `type = 'single_egi_default'`.
    d.  **Se Collection Default NON Esiste (Primo Upload per l'Utente):**
        i.  **Crea Collection:** Crea un nuovo record `Collection` con `creator_id`, `owner_id` = `Auth::user()->id`, `type = 'single_egi_default'`, un nome default (es. "User's First EGIs"), `status = 'draft'`.
        ii. **Aggiungi Membro:** Crea un record nella tabella pivot `collection_user` per associare l'utente alla nuova collection con ruolo 'owner' e `is_owner = true`.
        iii. **Crea Wallet Default:** Esegue una logica per creare i record associati nella tabella `wallets` per questa `collection_id`, linkando gli utenti/ruoli predefiniti (Creator, EPP, Natan - recuperando i loro ID da config) e impostando le royalties di default (da config).
    e.  **Se Collection Default Esiste:** Recupera l'`id` della collection esistente.
    f.  **Prepara Dati EGI:** Estrae metadati dal file (estensione, MIME, dimensioni), calcola hash (`md5_file`), genera `position` (`FileHelper::generate_position_number`), genera nome default (`#000X123`), cripta nome originale (`my_advanced_crypt`), ottiene `upload_id` (da request o genera).
    g.  **Crea Record `egi`:** Istanzia un nuovo modello `Egi`. Popola tutti i campi rilevanti: `collection_id`, `user_id`, `owner_id`, `title`, `extension`, `media=false`, `type='image'`, `price` (da collection), `position`, `size`, `dimension`, `status='draft'`, `file_crypt`, `file_hash`, `file_mime`, `bind=0`, `paired=0`. **NON imposta `key_file` ora.**
    h.  **Salva EGI (Passo 1):** Esegue `$egi->save()` per ottenere l'`egi->id`.
    i.  **Imposta `key_file`:** Imposta `$egi->key_file = $egi->id;`.
    j.  **Salva EGI (Passo 2):** Esegue `$egi->save()` nuovamente per persistere `key_file`.
    k.  **Calcola Chiave Storage:** Costruisce la chiave di storage: `config(filesystems.disks.do.root)/user_id/collection_id/egi_id` (dove `egi_id` è il valore in `key_file`).
    l.  **Salva File (Multi-Server):** Utilizza `Storage::disk('disk_name')->put($key, $fileContents)` per salvare il file sui dischi configurati (es. 'do', 'local_backup'). Gestisce errori, potenzialmente tenta rollback parziale su fallimento critico.
    m. **Invalida Cache:** Esegue `Cache::forget('collection_items-' . $collectionId)`.
    n.  **Restituisci Risposta:** Invia una `JsonResponse` di successo (HTTP 200) contenente `userMessage` e `egiData` (con `id`, `collection_id`, `title`). In caso di errore in qualsiasi punto, cattura `Throwable`, logga e restituisce una `JsonResponse` di errore (HTTP 4xx/5xx) con `userMessage` e `error_details`. L'intera logica è wrappata in `DB::transaction()`.
7.  **Frontend Update:** Il codice TypeScript riceve la risposta JSON. Se successo, usa `userMessage` per aggiornare lo stato e potenzialmente aggiorna l'UI con i dati `egiData`. Se errore, mostra `userMessage`.

**8. Logica di Storage (Confermata)**

*   I percorsi dei file **non** sono memorizzati nel DB.
*   La chiave di storage utilizzata per salvare e recuperare i file EGI è costruita dinamicamente: `config(filesystems.disks.DISK.root) / user_id / collection_id / egi_id`.
*   La logica di salvataggio multi-server (es. su DigitalOcean Spaces e un disco locale di backup) verrà implementata all'interno dell'`EgiUploadHandler`.

**9. Database Schema & Campi Chiave (Contesto MVP Q1 - Immagini)**

*   **Tabelle Principali Coinvolte:** `collections`, `egi`, `collection_user`, `wallets`, `users`.
*   **Campi `egi` Rilevanti per il File:**
    *   `media`: Sarà sempre `false`.
    *   `key_file`: Conterrà l'ID stesso dell'EGI (`egi.id`). Utilizzato per costruire la chiave di storage.
    *   `bind`: Sarà `0` o `false`, non utilizzato attivamente.
    *   `paired`: Sarà `0` o `false`, non utilizzato attivamente (salvo diversa indicazione).

**10. Prossimi Passi Immediati (Focus MVP Q1)**

1.  Implementare la classe PHP `App\Handlers\EgiUploadHandler` con la logica descritta al punto 7.
2.  Creare il controller `App\Http\Controllers\EgiUploadController` che utilizza l'handler.
3.  Definire la rotta `/upload/egi` che punta al controller.
4.  Assicurarsi che la configurazione necessaria sia presente (`AllowedFileType`, `filesystems`, `egi.default_ids`, `egi.default_royalties`, `egi.storage`).
5.  Verificare che il frontend (`HubFileController.ts` e codice chiamante) invii correttamente le richieste a `/upload/egi`.
6.  Testare l'intero flusso di upload EGI (immagine).
7.  Continuare la pulizia *progressiva* di UCM/ULM dai file toccati durante lo sviluppo EGI o altre modifiche Q1.

**11. Considerazioni Post-MVP Q1 (Q2 e Oltre)**

*   Reintegrazione UCM/ULM (valutazione).
*   Refactoring Oracode/GDPR dei moduli esistenti (Collection, Wallet, Notifiche, Auth).
*   Sviluppo gestione EGI Audio/Video/Ebook (logica `key_file`, abbinamento cover).
*   Implementazione gestione Traits EGI.
*   Sviluppo Sito Istituzionale.
*   Sviluppo completo del backend per i Creator.
*   Integrazione Blockchain Algorand (minting, trasferimenti).
*   Funzionalità avanzate Marketplace (aste, acquisti).

---

Fabio, spero che questo documento consolidato rifletta accuratamente lo stato attuale, le decisioni prese e il piano d'azione concordato. Usiamolo come nostro riferimento principale d'ora in poi. Se ci sono imprecisioni o punti da chiarire ulteriormente, fammelo sapere.