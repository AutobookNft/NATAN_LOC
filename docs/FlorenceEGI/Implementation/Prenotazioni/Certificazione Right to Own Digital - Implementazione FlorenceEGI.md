# Certificazione Right to Own Digital - Implementazione FlorenceEGI

---

## 1. Bozza di testo legale da inserire nel certificato (esempio ITA/ENG)

### Italiano

> Il presente certificato attesta che il soggetto [NOME_ACQUIRENTE] ha ottenuto, ai sensi del regolamento FlorenceEGI, il diritto di proprietà digitale sull’opera denominata [NOME_OPERA] (ID: [ID_OPERA], HASH: [HASH_OPERA]) realizzata da [NOME_CREATOR]. Il diritto include la titolarità esclusiva della prenotazione/acquisto, secondo le condizioni riportate nella piattaforma FlorenceEGI. Eventuali limitazioni, modalità di trasferimento, uso o rivendita sono specificate nei Termini e Condizioni accettati dalle parti. Questo certificato è firmato elettronicamente/digitalmente e ne garantisce autenticità, integrità e opponibilità ai sensi della normativa vigente (eIDAS/Reg. UE 910/2014).

### English

> This certificate certifies that [BUYER_NAME] has acquired, pursuant to the FlorenceEGI regulations, the digital ownership right over the artwork entitled [ARTWORK_NAME] (ID: [ARTWORK_ID], HASH: [ARTWORK_HASH]) created by [CREATOR_NAME]. This right includes exclusive entitlement to reserve/purchase, according to the conditions stated on the FlorenceEGI platform. Any limitations, transfer modes, usage, or resale conditions are detailed in the Terms and Conditions accepted by the parties. This certificate is electronically/digitally signed and guarantees authenticity, integrity, and enforceability under applicable law (eIDAS/EU Reg. 910/2014).

---

## 2. Schema di flusso integrato firma elettronica (DocuSign/Yousign/AdobeSign)

1. **Prenotazione confermata** su FlorenceEGI
    
    - L’utente completa la procedura di prenotazione/acquisto EGI
        
2. **Generazione PDF** certificato con tutti i dati rilevanti (acquirente, creator, opera, hash, regole, timestamp)
    
3. **Invio richiesta firma elettronica avanzata**
    
    - API verso provider (DocuSign, Yousign, ecc.)
        
    - Destinatari: piattaforma FlorenceEGI (firma come ente) + creator (opzionale) + acquirente (opzionale)
        
    - Link per firma sicura inviato via email agli interessati
        
4. **Firma elettronica avanzata**
    
    - Gli utenti accedono, firmano il PDF (con log, timestamp, identificazione email/OTP)
        
    - Il provider sigilla il documento (audit log + hash)
        
5. **Restituzione PDF firmato a FlorenceEGI**
    
    - PDF archiviato (S3/DO Spaces), linkato alla prenotazione e visibile all’utente
        
    - Invio automatico PDF firmato anche agli interessati via email
        
6. **Registrazione hash del PDF**
    
    - Calcolo hash SHA256 del PDF firmato
        
    - Scrittura hash su Algorand/on-chain per immutabilità (opzionale in MVP, consigliato in V2)
        
7. **Verifica pubblica**
    
    - Dashboard “Verifica Certificato”: caricando/scansionando il PDF, verifica validità firma, hash e matching con la prenotazione originale
        

---

## 3. Checklist tecnica per integrazione firma PDF lato Laravel

**A. Integrazione PDF**

-  Utilizzo di library PDF (es. dompdf, barryvdh/laravel-dompdf, TCPDF, o simili)
    
-  Template PDF dinamico con merge di variabili (Blade o raw PHP)
    
-  Inserimento QR code, hash SHA256, metadata visibili
    

**B. Integrazione provider firma elettronica**

-  Selezione provider (DocuSign, Yousign, AdobeSign)
    
-  Registrazione API key/account, set up ambiente sandbox
    
-  Creazione flusso OAuth e autenticazione sicura
    
-  API: invio documento, destinatari, callback/notification
    
-  Webhook per ricevere PDF firmato + audit log
    
-  Parsing/validazione PDF firmato (verifica firma con tool provider o opensource)
    
-  Gestione errori (timeout, firma non completata, ecc.)
    

**C. Storage e sicurezza**

-  Storage PDF firmato su storage sicuro (S3, DigitalOcean Spaces, o analogo)
    
-  Hash del PDF firmato calcolato e salvato nel DB (reservation/certificate table)
    
-  Backup automatico su storage secondario
    

**D. Integrazione con frontend**

-  Visualizzazione stato firma su dashboard utente/creator
    
-  Download PDF firmato e accesso alla verifica firma
    
-  Notifiche via email/SMS per ogni stato della procedura
    

**E. On-chain (facoltativo subito, obbligatorio in V2)**

-  Calcolo hash PDF firmato e scrittura su Algorand (transazione con note, oppure smart contract)
    
-  Link hash on-chain visualizzabile sulla dashboard/certificato
    

**F. Verifica pubblica**

-  Endpoint/public tool per verifica PDF: parsing firma, controllo hash, confronto con DB FlorenceEGI
    

---

> Tutte le implementazioni dovranno essere documentate secondo lo standard Oracode, con checklist di QA e script di test unitari/integrati per ogni step chiave (PDF, firma, storage, verifica).