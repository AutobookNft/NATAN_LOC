# Executive Summary

Questo documento definisce **come funzionano le prenotazioni** su FlorenceEGI in modo chiaro, scalabile e auditabile. La logica è **FIAT‑first con EUR come valuta canonica** (in parallelo al documento valutario) e introduce una **macchina a stati** per le prenotazioni, le regole di concorrenza (una prenotazione attiva per EGI), le **quotazioni a tempo (TTL)**, i casi “senza pagamento” vs “con deposito/escrow”, la gestione rimborsi e l’integrazione con ULM/UEM. È pensato per essere implementato a strati (strangler) senza big‑bang.

---

# Scopo

- Standardizzare il ciclo di vita di una prenotazione (creazione → pagamento → settlement → chiusura).
- Ridurre ambiguità tra **visualizzazione** e **valori contrattuali**.
- Impostare regole robuste per **concorrenza, TTL, idempotenza, audit**.
- Allineare **UI/UX** (card, chip, CTA, timer) con i reali stati della prenotazione.

## Fuori Scopo

- Motore di ranking e ordering (documento dedicato).
- Policy prezzi e valute (coperto nel documento “FIAT‑first, EUR”).

---

# Glossario

- **Reservation**: richiesta di blocco di un EGI da parte di un buyer.
- **Soft Hold**: prenotazione **senza pagamento**; scade automaticamente (TTL breve).
- **Hard Hold / Escrow**: prenotazione **con pagamento** (deposito o full) e blocco fondi (ALGO/USDC/carta).
- **Quote**: quotazione a tempo (EUR→USD/GBP/ALGO) usata al checkout.
- **Settlement**: conferma finale e consegna (rilascio certificati, email, DB).

---

# Macchina a Stati (concettuale)

Stato principale (`status`) + sotto‑stato operativo (`sub_status`) per leggibilità.

**Stati principali**

1. `active`  – la prenotazione è viva (soft o hard hold).
2. `completed` – pagamento/settlement eseguiti; EGI chiuso.
3. `expired` – scaduta per TTL o mancata azione.
4. `cancelled` – annullata dal buyer o dal sistema.

**Sotto‑stati (sub\_status)**

- `soft_hold` (creata senza pagamento; TTL breve, es. 15′)
- `awaiting_payment` (quote emessa; in attesa del pagamento; timer visibile)
- `escrow_locked` (pagamento eseguito; fondi bloccati; in attesa di settlement)
- `settled` (consegna eseguita; alias di completed per UI)
- `refunding` (rimborso in corso)
- `failed` (errore di pagamento/escrow)

**Transizioni (principali)**

- `soft_hold → awaiting_payment` (utente apre checkout, emessa quote)
- `awaiting_payment → escrow_locked` (pagamento riuscito)
- `escrow_locked → completed` (settlement riuscito)
- `soft_hold|awaiting_payment → expired` (TTL scaduto)
- `awaiting_payment|escrow_locked → cancelled` (annullo esplicito + regole)
- `escrow_locked → refunding → cancelled` (rimborso totale) / `completed` (parziale post‑settlement)
- Qualsiasi → `failed` (errore irreversibile, es. rete on‑chain)

> **Mappatura DB minimal**: manteniamo `status` come ENUM corto e aggiungiamo `sub_status` per granularità operativa e UI.

---

# Regole Fondamentali

1. **Una sola prenotazione attiva per EGI** (`status='active'`).
2. **EUR è la verità**: l’importo contrattuale è `amount_eur`. Quote e pagamenti salvano `fx_rate_used`, `buyer_currency`, `buyer_amount`.
3. **TTL**: `soft_hold` (es. 15′), `awaiting_payment` (durata quota, es. 15′). Scaduto → `expired`.
4. **Idempotency**: ogni tentativo di pagamento usa una chiave idempotente; replay sicuri.
5. **No price change dopo soft/hard hold**: il creator non può modificare `amount_eur` mentre esiste una prenotazione attiva.
6. **Rimborsi**: contano in EUR; conversione alla valuta di rimborso al tasso del momento del rimborso.

---

# Concorrenza & Locking

- **Obiettivo**: evitare doppie prenotazioni sullo stesso EGI.
- **Transazione applicativa**:
  1. `SELECT ... FOR UPDATE` dell’EGI o riga “contatore” logica.
  2. Verifica assenza di `reservations.active` per quell’EGI.
  3. Crea reservation `active/soft_hold`.
- **Indice** consigliato: `(egi_id, status)` per lookup rapido.
- **Coda d’attesa (opzionale)**: se esiste già un `active`, consenti “Entra in coda” (notifiche se scade/cancella).

---

# Quote & Pagamenti (sintesi)

- **Quote** (EUR→buyer currency e EUR→ALGO) con **TTL** (es. 15′).
- Alla conferma pagamento: salva `fx_rate_used`, `buyer_currency`, `buyer_amount`, `algo_amount_locked_micro`, `executed_at`, `provider`, `tx_id`.
- **Buffer anti‑slippage** su ALGO (es. +1%); rimborsa eccedenza al settlement.
- Se la quota **scade**, l’utente deve rigenerarla (nuovo `fx_quote_id`).

---

# Tipi di Prenotazione

1. **Soft Hold** (senza pagamento)
   - Stato: `active/soft_hold` → scade a TTL.
   - Azioni: checkout; cancella.
2. **Hard Hold** (con deposito/escrow parziale)
   - Stato: `active/escrow_locked` fino a settlement o rimborso.
   - Regole: finestra massima per completare (es. 24–48h) prima di cancellazione e rimborso.
3. **Full Payment** (pagamento completo immediato)
   - Flusso rapido: `awaiting_payment → escrow_locked → completed`.

---

# UI/UX (card & checkout)

- **Card** usa lo stato principale e sub‑stato per badge/CTA:
  - `AVAILABLE` → “Prenota”
  - `RESERVED_BY_OTHER` (`active/*`) → badge “Prenotato”; CTA disabilitata/“Entra in coda”
  - `RESERVED_BY_ME` (`active/*` con mio user) → “Completa pagamento”
  - `SOLD/CLOSED` → badge “Chiuso”
- **Chip prenotazione**: “Prenotato: \$ 1,250 (bloccato 12/08 15:32)”.
- **Timer** su quote: countdown visibile (es. 14:59) e messaggio alla scadenza.
- **Microcopy** (coerente col doc valutario): “Prezzo ufficiale in euro. Se paghi in USD/GBP, il cambio viene bloccato per 15′ al checkout.”

**Integrazione multi‑currency già presente**

- **Currency Selector** in header con persistenza preferenza (auth) e session (guest): non replicare logica nella card; consumare la preferenza e la stima già fornite.
- **Currency Price Blade component** per mostrare prezzo/stima (fallback server‑side se JS assente).
- **Regola visuale**: primario sempre EUR; stima con prefisso **≈** nella valuta utente.

---

# Struttura Dati (Patch A – minimal)

**Aggiunte chiave a **``

- `amount_eur DECIMAL(12,2)`
- `status ENUM('active','expired','completed','cancelled')`
- `sub_status ENUM('soft_hold','awaiting_payment','escrow_locked','settled','refunding','failed') DEFAULT 'soft_hold'`
- `buyer_currency CHAR(3) NULL`
- `buyer_amount DECIMAL(12,2) NULL`
- `fx_provider VARCHAR(32) NULL`
- `fx_quote_id VARCHAR(64) NULL`
- `fx_rate_used DECIMAL(20,10) NULL`
- `fx_quoted_at TIMESTAMP NULL`
- `fx_quote_expires_at TIMESTAMP NULL`
- `payment_executed_at TIMESTAMP NULL`
- `payment_method ENUM('algo','usdc','card','bank') DEFAULT 'algo'`
- `algo_amount_locked_micro BIGINT UNSIGNED NULL`
- `algo_tx_id VARCHAR(128) NULL`
- `refund_algo_tx_id VARCHAR(128) NULL`

**Tabelle opzionali (Patch B – pulita)**

- `fx_quotes(id, egi_id, amount_eur, eur_usd, eur_gbp, eur_algo, provider, quoted_at, expires_at)`
- `payments(id, reservation_id, amount_eur, buyer_currency, buyer_amount, fx_rate_used, provider, quoted_at, executed_at, tx_id, method, algo_amount_micro)`
- `reservation_events(id, reservation_id, type, payload JSON, created_at)` per audit (state changes, errori, scadenze).

## Compatibilità con sistema attuale (schema, API, UI)

**Schema esistente (verificato):** `reservations` contiene già `fiat_currency (USD default)`, `offer_amount_fiat (DECIMAL)`, `offer_amount_algo (microALGO BIGINT)`, `exchange_rate (FIAT per 1 ALGO)`, `exchange_timestamp` e campi legacy `original_currency`, `original_price`, `algo_price`, `rate_timestamp`. Le nostre aggiunte (`amount_eur`, `buyer_*`, `fx_*`, `algo_*`) restano **additive** e coesistono in dual‑write. Standardizziamo il nuovo `fx_rate_used` come **ALGO per 1 EUR**; manteniamo `exchange_rate` (FIAT per 1 ALGO) come dato **legacy/raw** per audit e compatibilità.

**API/UI esistenti:** sono già presenti gli endpoint currency (rate singolo/tutti, convert) e il **Currency Selector** in header con salvataggio preferenza utente e aggiornamento live delle card; il **currency‑price component** lato Blade è operativo. La card v2 deve solo consumare il `PricePresenter` e non ricreare logica di conversione.

**Conseguenza pratica:**

- La **verità contrattuale** rimane `amount_eur` (EUR) nelle prenotazioni.
- Per **escrow ALGO** calcoliamo `algo_amount_locked_micro` usando `fx_rate_used` (ALGO per 1 EUR) e salviamo **anche** `exchange_rate` legacy (FIAT per 1 ALGO) se già presente nel flusso attuale.
- Durante la migrazione, i report possono leggere i campi nuovi; i vecchi restano per retro‑compatibilità e audit.

---

# Job Schedulati

- `reservation:expire-soft-hold`: scade `soft_hold` oltre TTL → `expired` + notifica.
- `quote:expire`: invalida quote scadute; aggiorna UI via websocket/notify.
- `reservation:cleanup`: chiude prenotazioni orfane; invia rimborsi pendenti.

---

# Error Handling (UEM) & Logging (ULM)

**Mappatura agli error code reali (UEM)** Usare i **codici descrittivi** già configurati. Per i casi d’uso delle prenotazioni:

- `CURRENCY_EXCHANGE_SERVICE_UNAVAILABLE` → provider FX non disponibile (retry+fallback, 503)
- `CURRENCY_INVALID_RATE_DATA` → dati tasso malformati (502)
- `CURRENCY_RATE_CACHE_ERROR` → errore cache tassi (log‑only)
- `CURRENCY_CONVERSION_VALIDATION_ERROR` → input conversione non valido (422)
- `CURRENCY_CONVERSION_ERROR` → errore di calcolo conversione (400)
- `CURRENCY_UNSUPPORTED_CURRENCY` → valuta non supportata (400)
- `USER_PREFERENCE_UPDATE_FAILED` / `USER_PREFERENCE_FETCH_ERROR` → problemi preferenze valuta (500/404)

**Pattern d’uso (esempio sintetico)**

```php
return $this->errorManager->handle('CURRENCY_EXCHANGE_SERVICE_UNAVAILABLE', [
  'operation' => 'fx.quote.issue',
  'reservation_id' => $reservationId,
  'currency' => $buyerCurrency,
]);
```

**ULM – eventi (coerenti con i flussi)**

- `reservation.created` {egi\_id, user\_id, status, sub\_status}
- `fx.quote.issued` {reservation\_id, rate, provider, ttl}
- `fx.quote.expired` {reservation\_id, quote\_id}
- `payment.executed` {reservation\_id, buyer\_currency, buyer\_amount, rate, tx\_id}
- `escrow.locked` {reservation\_id, microalgo, rate, tx\_id}
- `reservation.expired` {reservation\_id}
- `reservation.cancelled` {reservation\_id}
- `reservation.completed` {reservation\_id}
- `refund.executed` {reservation\_id, amount\_eur, buyer\_currency, rate, tx\_id}

**I18N**: i messaggi UEM sono **completi** in IT/EN per tutti i codici multi‑currency; la UI deve mostrare i messaggi *user* e loggare i *dev*.

---

# Policy Rimborsi

- **Totale** prima del settlement: `escrow_locked → refunding → cancelled`.
- **Parziale** dopo il settlement: record in `payments` con importo negativo; UI e email con dettaglio.
- **Cambio** del rimborso salvato in `fx_rate_used` del rimborso.

---

# Test di Accettazione

1. **Soft Hold TTL**: crea, attendi 15′ → `expired`, card aggiornata, notifica inviata.
2. **Quote → Pay**: emetti quote, paga entro TTL → `escrow_locked`, log completo (rate, tx\_id, microALGO).
3. **Quote Expired**: scadenza → impedisci pagamento, richiedi rigenerazione.
4. **Full Payment**: da listing a `completed` in un’unica sessione.
5. **Refund Total**: da `escrow_locked` a `cancelled` con rimborso registrato.
6. **Idempotenza**: doppio click su “Paga” → 1 solo `payment.executed`.
7. **Concorrenza**: due utenti tentano soft hold simultaneo → uno solo `active`, l’altro riceve messaggio coda.

---

# KPI & Monitoraggio

- Tasso conversione soft→hard hold.
- Percentuale quote scadute.
- Tempo medio tra `escrow_locked` e `completed`.
- Errori per categoria UEM (alert > soglia).

---

# Rollout (a strati)

1. **Aggiungi campi** `sub_status`, `buyer_*`, `fx_*`, `algo_*` a `reservations` e inizia dual‑write.
2. **UI**: card usa stati e chip; checkout mostra timer quote.
3. **Job TTL**: attiva scadenze e notifiche.
4. **Report**: basati su `amount_eur`, `buyer_currency/amount`, `fx_rate_used`.
5. (Opzionale) Split su `fx_quotes`/`payments` + `reservation_events`.

---

# Allegato – Microcopy pronta (ITA)

- "Prenotazione creata. Hai 15 minuti per completare il pagamento."
- "Il cambio è bloccato per 15 minuti: completa ora per confermare."
- "La tua quotazione è scaduta. Rigenera per procedere."
- "Pagamento duplicato rilevato: nessun addebito aggiuntivo."
- "Escrow bloccato. Stiamo finalizzando la consegna."
- "Prenotazione scaduta. L’EGI è tornato disponibile."
- "Rimborso effettuato: € 250,00 (tasso del 13/08 10:21)."

---

# Conclusione

La gestione delle prenotazioni ruota attorno a **stati chiari, TTL, idempotenza e audit**. Con EUR come verità e quote a tempo per pagamenti/ALGO, l’esperienza è semplice per l’utente e robusta per te. L’adozione a strati ti permette di integrare tutto senza interrompere i flussi esistenti, con metriche e logging che rendono l’operazione governabile nel tempo.



---

# Certificati (Emissione • Ancoraggio • Verifica)

Integrare i **certificati** nel ciclo di vita della prenotazione rende l’esperienza trasparente per l’utente e inattaccabile in sede di audit. Tutti i certificati sono file **PDF/A** con **hash, firma applicativa** e (quando previsto) **ancoraggio su Algorand** tramite **Merkle root**.

## Obiettivi

- Fornire al buyer e al creator evidenze **non ripudiabili** delle fasi economiche (escrow, settlement, rimborso).
- Collegare ogni certificato ad **univoci identificativi** (reservation, pagamento, EGI) e al relativo **fx\_rate\_used**.
- Offrire una **verifica pubblica** via endpoint e QR.

## Tipologie di certificato

1. **Escrow Receipt** (alla transizione `awaiting_payment → escrow_locked`)
   - Prova di fondi bloccati (ALGO/USDC/carta), con `algo_amount_locked_micro`, `fx_rate_used`, `executed_at`, `algo_tx_id`.
   - Uso: comunicazione al buyer; base per eventuale rimborso.
2. **Certificate of Ownership & Authenticity (COA)** (alla transizione `escrow_locked → completed/settled`)
   - Attesta il trasferimento/assegnazione dell’EGI all’acquirente.
   - Contiene: `amount_eur`, `buyer_currency/amount`, `fx_rate_used`, `executed_at`, identificativi EGI, eventuale `asa_id` (se coniato), `anchor_tx_id` e **Merkle proof**.
3. **Refund Note** (quando `refunding → cancelled` o rimborso parziale post‑settlement)
   - Attesta importo rimborsato (in EUR), valuta di erogazione, `fx_rate_used` del rimborso, `refund_algo_tx_id`.

> Opzionali: *Cancellation Notice* per prenotazioni scadute/cancellate senza pagamento (utile per audit, non inviata all’utente se non richiesto).

## Dati minimi obbligatori nei certificati

- **Header**: `certificate_id (ULID)`, `certificate_type`, `version` (es. `v1.0`), `issued_at`.
- **Riferimenti**: `reservation_id`, `payment_id` (se presente), `egi_id`, `creator_id`, `buyer_id` (pseudonimo se privacy), `status` al momento dell’emissione.
- **Valori**: `amount_eur`, `buyer_currency`, `buyer_amount`, `fx_rate_used`, `fx_provider`, `quoted_at`, `executed_at`.
- **On‑chain** (se applicabile): `algo_amount_locked_micro`, `algo_tx_id`, `anchor_tx_id`, `anchor_round`, `merkle_root`, `merkle_proof[]`.
- **Integrità**: `pdf_sha256`, `payload_sha256`, `app_signature` (JWS o CMS), `signing_key_id`.
- **Verifica**: `verification_url` (deep‑link) + **QR** in prima pagina.

## Pipeline di generazione

1. **Trigger applicativo**
   - Escrow Receipt: al successo del pagamento/lock.
   - COA: al completamento del settlement.
   - Refund Note: alla conferma rimborso.
2. **Build payload JSON** (ordinamento deterministico delle chiavi) → `payload_sha256`.
3. **PDF/A** da template (Blade/Chromium) con watermark discreto e QR.
4. **Firma applicativa** del payload (`app_signature`) e **hash PDF** (`pdf_sha256`).
5. **Storage**: `storage/app/certificates/YYYY/MM/<certificate_id>.pdf` + object storage esterno (replica).
6. **Merkle batching** (job orario/giornaliero)
   - Accorpa `payload_sha256` dei certificati emessi nel periodo.
   - Calcola `merkle_root` e ancora su Algorand (txn con `note` o app dedicata).
   - Aggiorna ogni certificato con `merkle_proof[]`, `merkle_root`, `anchor_tx_id`, `anchor_round`.

## Verifica pubblica

- **Endpoint** `/certificates/verify/{certificate_id}` e `/certificates/verify?hash=<pdf_sha256>`.
- La pagina **ricalcola gli hash**, verifica firma `app_signature`, ricostruisce la **Merkle proof** fino a `merkle_root` e valida l’ancoraggio consultando la transazione su Algorand.
- Esito: `valid | tampered | revoked | pending_anchor` con motivazione.

## Modello dati

### Aggiunte a `reservations`

- `certificate_status ENUM('none','escrow_issued','coa_issued','revoked') DEFAULT 'none'`
- `escrow_certificate_id ULID NULL`, `coa_certificate_id ULID NULL`

### Nuove tabelle

``

- `id ULID PK`
- `reservation_id BIGINT NULL` | `payment_id BIGINT NULL`
- `egi_id BIGINT NOT NULL`
- `type ENUM('escrow_receipt','coa','refund_note')`
- `version VARCHAR(8) DEFAULT 'v1.0'`
- `payload_sha256 CHAR(64)`, `pdf_sha256 CHAR(64)`
- `app_signature TEXT`, `signing_key_id VARCHAR(64)`
- `merkle_root CHAR(64) NULL`, `merkle_proof JSON NULL`, `anchor_tx_id VARCHAR(128) NULL`, `anchor_round BIGINT NULL`
- `issued_at TIMESTAMP`, `revoked_at TIMESTAMP NULL`, `revocation_reason VARCHAR(255) NULL`
- `pdf_path VARCHAR(255)`, `verification_url VARCHAR(255)`
- Indici: `(egi_id)`, `(reservation_id)`, `(payment_id)`, `(type, issued_at)`

``

- `id ULID PK`, `batch_from TIMESTAMP`, `batch_to TIMESTAMP`, `merkle_root CHAR(64)`, `anchor_tx_id VARCHAR(128)`, `anchor_round BIGINT`, `count INT`

`` (audit append‑only)

- `id ULID PK`, `certificate_id ULID`, `type`, `payload JSON`, `created_at`

- `id ULID PK`, `certificate_id ULID`, `type`, `payload JSON`, `created_at`

## Regole e politiche

- **Immutabilità**: i certificati non si modificano; si **revocano** con motivazione e, se necessario, si **riemettono** con nuovo `id` e collegamento a `previous_certificate_id` (campo aggiuntivo su `certificates`).
- **Privacy**: evitare PII nel payload pubblico; usare `buyer_alias`/`buyer_hash` (HMAC con chiave server) per riferimenti.
- **Retenzione**: PDF e metadati tenuti a tempo indeterminato; i batch Merkle sono permanenti.
- **Localizzazione**: PDF in lingua viewer; numeri formattati per locale (EUR primario).

## UI/UX

- In **card dettaglio prenotazione**: se `escrow_locked` → pulsante “Scarica ricevuta escrow”. Se `completed` → “Scarica certificato di proprietà”. Se `refunding/refunded` → “Scarica nota di rimborso”.
- In **email**: link diretto al PDF + link “Verifica autenticità”.
- In **pagina verify** (già esistente): stato con badge (Verde "Valido", Giallo "In attesa ancoraggio", Rosso "Revocato/Alterato"), dettagli tecnici comprimibili.

## ULM / UEM (estensioni)

- **ULM**

  - `certificate.issued` {id, type, reservation\_id, egi\_id, hash}
  - `certificate.anchored` {id, merkle\_root, anchor\_tx\_id}
  - `certificate.revoked` {id, reason}

- **UEM (codici conformi allo stile del sistema)**

  - `CERTIFICATE_GENERATION_FAILED` — errore generazione PDF/A o firma applicativa → retry (bounded) + notifica supporto; log completo payload.
  - `CERTIFICATE_ANCHOR_FAILED` — ancoraggio Merkle su Algorand fallito (provider down/txn rejected) → retry con backoff; marca certificato `pending_anchor`.
  - `CERTIFICATE_PAYLOAD_VALIDATION_ERROR` — payload incompleto/non valido (hash mancanti, `fx_rate_used` assente, id incoerenti) → 422 + messaggio dev.
  - `CERTIFICATE_VERIFICATION_TAMPERED` — hash PDF ≠ payload firmato → segna come `tampered`, blocca download pubblico, alert.
  - `CERTIFICATE_ANCHOR_TX_NOT_FOUND` — transazione di ancoraggio non reperibile (round/tx id) → stato `pending_anchor`, re‑sync indexer.
  - `CERTIFICATE_REVOKE_FAILED` — revoca non riuscita (stato non coerente o write DB fallita) → rollback transazione e alert.

  *Nota*: se nel `config/error-manager.php` esistono già codici equivalenti, mappare 1:1; altrimenti aggiungerli con `type`, `blocking`, `dev_message_key`, `user_message_key`, `http_status_code` coerenti ai codici `CURRENCY_*`.

## Test di accettazione (aggiuntivi)

1. **Escrow Receipt**: dopo pagamento, PDF generato con `fx_rate_used`, `algo_amount_locked_micro`, `algo_tx_id`; hash coerente; verifica OK.
2. **COA**: al settlement, PDF con dati finali; dopo job Merkle, pagina verify mostra ancoraggio valido.
3. **Revoca & Riemissione**: revoca giustificata di un COA (errore dati), nuova emissione collegata; verify segnala lo stato corretto.
4. **Tampering**: upload di PDF alterato → verify risponde `tampered`.
5. **Rimborso**: emissione Refund Note con cambio del momento; verify OK.

## Rollout

- Fase 1: Escrow Receipt senza ancoraggio (solo firma e hash) + pagina verify.
- Fase 2: Merkle batching e ancoraggio Algorand; backfill `merkle_proof` per certificati dell’ultima settimana.
- Fase 3: Revoca/riemissione e UI completa nei dettagli prenotazione.

