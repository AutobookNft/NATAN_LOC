# Documento Tecnico Completo: Certificati di Prenotazione EGI – FlorenceEGI

## ✨ Scopo del Documento

Questo documento definisce in modo completo la logica, la struttura tecnica e la funzione semantica dei **Certificati di Prenotazione EGI** utilizzati nella piattaforma FlorenceEGI.

Tali certificati sono la versione Web2.0 dell'impegno economico e relazionale dell'utente nei confronti della piattaforma e di un'opera specifica (EGI). Sono documenti **pubblici, firmati digitalmente e consultabili** che svolgono una funzione fondamentale per la trasparenza e la tracciabilità del processo di prenotazione.

---

## 🔍 Finalità dei Certificati

Ogni volta che un utente effettua una prenotazione per un EGI, viene generato un certificato. Questo certificato:

- **Attesta pubblicamente** l'interesse e l'impegno dell'utente ad acquistare un determinato EGI a un prezzo specifico;
    
- **Costituisce una garanzia** da parte della piattaforma FlorenceEGI che quell'opera sarà riservata all'utente secondo le regole di priorità;
    
- **Rappresenta un impegno digitale** che viene firmato dalla piattaforma tramite hash univoco, e reso accessibile tramite URL pubblico;
    
- **Rende trasparente** il processo di prenotazione, anche nella fase MVP in cui le transazioni non avvengono ancora on-chain.
    

---

## 🔹 Tipi di Prenotazione

Esistono due forme ufficiali di prenotazione nella piattaforma FlorenceEGI, ciascuna con una diversa valenza legale e prioritaria.

### ✅ Prenotazione Forte (`strong`)

- Effettuata da **utenti registrati** (con nome, cognome, email, eventualmente anche documento d'identità);
    
- Non ha scadenza temporale automatica;
    
- Ha **priorità assoluta** rispetto a tutte le prenotazioni deboli, anche se l'importo offerto è inferiore;
    
- Genera un certificato firmato e autenticato.
    

### ⚠ Prenotazione Debole (`weak`)

- Effettuata da **utenti non registrati**, che si connettono con un **wallet Algorand fake** (soluzione adottata solo durante l'MVP);
    
- Non ha scadenza automatica, ma può essere **scavalcata** da una prenotazione forte;
    
- Ha **priorità inferiore**, anche se l'importo offerto è maggiore;
    
- Genera un certificato pubblico, ma marcato come "Prenotazione Debole".
    

---

## 🔄 Rilanci e Cronologia

- Ogni **nuova offerta**, anche dallo stesso utente, **genera un nuovo certificato**;
    
- I certificati precedenti vengono marcati come `is_superseded = true` ma **non vengono cancellati**;
    
- Viene mantenuta una **cronologia completa** delle offerte e delle prenotazioni per ciascun EGI;
    
- I certificati possono essere visualizzati in ordine cronologico o per utente.
    

---

## 🏆 Regole di Priorità

1. Vince sempre la **prenotazione forte più alta**;
    
2. Se non esistono prenotazioni forti, vince la **prenotazione debole più alta**;
    
3. Se un EGI ha già una prenotazione forte, tutte le prenotazioni deboli, anche con offerta maggiore, **vengono escluse** fino a disdetta o mancato acquisto.
    

---

## 🔢 Struttura del Certificato

### Contenuto Base:

- Nome dell'utente (o pseudonimo);
    
- Wallet Algorand (fake o reale);
    
- Titolo e ID dell'EGI prenotato;
    
- Nome del Creator;
    
- Offerta in euro e in ALGO equivalente;
    
- Tipo di prenotazione (`strong` o `weak`);
    
- Data e ora della prenotazione;
    
- Firma SHA256 (hash dei dati);
    
- Link pubblico o QR code per la verifica.
    

### Testo Garanzia (dinamico):

- Per prenotazione forte:
    

> "FlorenceEGI certifica che l'utente ha diritto prioritario su questo EGI."

- Per prenotazione debole:
    

> "L'utente ha riservato l'EGI, ma il diritto di prelazione è subordinato a prenotazioni forti."

---

## 🔍 Verifica Pubblica

Tutti i certificati sono pubblicamente accessibili via URL:

```
https://florenceegi.com/egi-certificates/{uuid}
```

- Il certificato mostra un **badge visivo** chiaro per distinguerne il tipo;
    
- Ogni certificato ha il suo **hash di firma** per verificarne l'autenticità;
    
- In futuro, il sistema potrà prevedere **verifica via Merkle Tree** o **firma on-chain**.
    

---

## 🔨 Dati Tecnici del Sistema

### Input della funzione `generateEgiReservationCertificate`

|Campo|Tipo|Obbligatorio|Descrizione|
|---|---|---|---|
|`user_id`|integer|solo se `strong`|Utente registrato|
|`user_name`|string|sì|Nome visualizzato|
|`wallet_address`|string|sì|Wallet utente|
|`egi_id`|integer|sì|ID dell'EGI|
|`egi_title`|string|sì|Titolo opera|
|`creator_name`|string|sì|Nome dell'autore|
|`offer_amount_eur`|decimal|sì|Offerta in euro|
|`offer_amount_algo`|decimal|sì|Stima in ALGO|
|`reservation_type`|enum|sì|`strong` o `weak`|
|`reservation_date`|datetime|sì|Data prenotazione|
|`signature_hash`|string|sì|Hash SHA256|

### Tabella `egi_reservation_certificates`

|Campo|Tipo|
|---|---|
|`id`|bigint|
|`egi_id`|FK|
|`user_id`|FK nullable|
|`wallet_address`|string|
|`reservation_type`|enum|
|`offer_amount_eur`|decimal|
|`offer_amount_algo`|decimal|
|`signature_hash`|string|
|`is_superseded`|boolean|
|`is_current_highest`|boolean|
|`pdf_path`|string|
|`public_url`|string|
|`created_at`|timestamp|

---

## ❤️ Filosofia

Questo certificato non è solo un documento tecnico, ma un **simbolo di fiducia**.

Nel mondo FlorenceEGI, non è solo il token a dare valore, ma anche la parola firmata, l'impegno pubblico, la trasparenza accessibile. Questo documento è il "fratello Web2.0" dell'NFT, una **dichiarazione d'intenti che vive anche prima della blockchain**, un ponte tra l'umano e il digitale.

> Anche nel Web3, ci mettiamo la faccia. Anche nel Web2, lasciamo una firma.