# Executive Summary
Versione **v1.1** aggiornata alla luce dell’implementazione attuale (selector valuta, componenti Blade, schema `reservations`, codici UEM/ULM). La piattaforma è **FIAT‑first con EUR valuta canonica**: tutti i prezzi contrattuali sono in **EUR**. USD/GBP sono valute di **visualizzazione** e **pagamento** al checkout tramite **quote a tempo (TTL)**. L’on‑chain (Algorand) è un **mezzo di regolamento**: al pagamento si calcola/blocca l’importo in ALGO usando il tasso corrente e registrando in modo auditabile il **tasso usato**. Il documento specifica compatibilità con i campi legacy e lo standard interno per la direzione dei tassi.

---

# Scopo
- Definire una policy unica e coerente per prezzi, prenotazioni, pagamenti/rimborsi.
- Stabilire EUR come *valuta di verità* e la semantica **univoca** dei cambi.
- Specificare campi dati **additivi** e la migrazione *strangler* dal modello esistente.
- Allineare UI/UX con i componenti già in uso (selector, currency‑price) e con UEM/ULM reali.

## Fuori Scopo
- Ranking/ordering (documento separato).
- Logiche d’asta avanzate (qui solo aspetti valute/quote/pagamenti).

---

# Principi Operativi
1. **Valuta canonica = EUR.** Tutti i listini e gli importi contrattuali sono salvati in EUR (`amount_eur`).
2. **FIAT‑first.** L’importo *dovuto* per un EGI è sempre espresso in EUR; altre valute sono comfort di visualizzazione o mezzi di pagamento.
3. **Quote a tempo (TTL).** Per pagamenti non in EUR (USD/GBP) e per ALGO escrow, si genera una **quotazione** con scadenza (es. 15′) e la si blocca al pagamento.
4. **Standard tassi**: lo **standard interno** è `fx_rate_used = ALGO per 1 EUR` e, per FIAT, `usd_per_eur` / `gbp_per_eur`. I tassi legacy salvati come **FIAT per 1 ALGO** restano disponibili solo per audit/retro‑compatibilità.
5. **Auditabilità.** Ogni azione monetaria persiste `fx_rate_used`, `quoted_at`, `executed_at`, `provider`, importi FIAT e ALGO (in microALGO).
6. **Rounding deterministico.** FIAT a 2 decimali; ALGO in **microALGO (BIGINT)**; buffer anti‑slippage e rimborso automatico eccedenza.
7. **Spiegabilità (Oracode 2.0).** UI mostra: prezzo EUR, stima ≈ nella valuta utente, chip prenotazione con cambio bloccato, timer quota.

---

# Glossario
- **List Amount (EUR)**: importo contrattuale EGI, `amount_eur`.
- **Viewer Currency**: preferenza di visualizzazione dell’utente (EUR/USD/GBP) usata solo per mostrare stima (≈).
- **Buyer Currency**: valuta scelta al checkout per il pagamento (EUR/USD/GBP).
- **FX Quote**: quotazione a tempo del cambio (EUR→USD/GBP/ALGO) con `fx_rate_used` e TTL.
- **ALGO Escrow**: importo in microALGO bloccato al pagamento.

---

# Flussi Principali
## 1) Listing (creazione EGI)
- Input del creator in qualsiasi valuta → conversione **una tantum** in EUR e salvataggio come `amount_eur`.
- Audit: `input_currency`, `input_amount`, `creation_fx_rate` (se input ≠ EUR). Da lì in poi la **verità** è in EUR.

## 2) Visualizzazione (card/list/detail)
- Primario: **€ X.XXX,XX** (sempre).
- Secondario (opzionale): **≈ $ X,XXX.XX** / **≈ £ X,XXX.XX** (stima live dalla preferenza utente).
- Prenotato: chip “**Prenotato: $ 1,250 (bloccato 12/08 15:32)**”. Nessuna riconversione del primario.

## 3) Prenotazione / Pagamento
- L’importo *dovuto* resta `amount_eur`.
- Pagamento in USD/GBP: genera **FX Quote** con TTL; salva `buyer_currency`, `buyer_amount`, `fx_rate_used`, `fx_quoted_at`, `fx_quote_expires_at`, `executed_at`.
- Escrow ALGO: calcola `algo_amount_locked_micro` da `amount_eur × fx_rate_used` (ALGO per 1 EUR), applica buffer e salva `algo_tx_id`.

## 4) Rimborso
- Si ragiona in EUR; se rimborsi in altra valuta/ALGO, si usa **nuova FX Quote** al momento del rimborso e si registra il relativo `fx_rate_used`.

---

# Regole di Conversione e Rounding
- **FIAT**: 2 decimali, `DECIMAL(12,2)`; per aritmetica critica usa anche *minor units* (cent) in `INT`.
- **ALGO**: `microALGO = ceil(amount_eur × fx_rate_used × 1_000_000 × (1 + buffer))`; buffer tipico 1,0%; eccedenza rimborsata a settlement.
- **Direzione tassi**: interno = **per EUR** (`usd_per_eur`, `gbp_per_eur`, `algo_per_eur`). Legacy = **per ALGO** (`exchange_rate = fiat_per_algo`).
- **TTL Quota**: default 15′; timer UI; quote scadute vanno rigenerate.

---

# Struttura Dati
## Patch A – minimal (additiva su `reservations`)
**Nuovi campi** (dual‑write con i legacy finché necessario):
- `amount_eur DECIMAL(12,2)`
- `buyer_currency CHAR(3) NULL` (EUR/USD/GBP)
- `buyer_amount DECIMAL(12,2) NULL`
- `fx_provider VARCHAR(32) NULL`
- `fx_quote_id VARCHAR(64) NULL`
- `fx_rate_used DECIMAL(20,10) NULL`  // **ALGO per 1 EUR** o, per FIAT, *per EUR*
- `fx_quoted_at TIMESTAMP NULL`
- `fx_quote_expires_at TIMESTAMP NULL`
- `payment_executed_at TIMESTAMP NULL`
- `payment_method ENUM('algo','usdc','card','bank') DEFAULT 'algo'`
- `algo_amount_locked_micro BIGINT UNSIGNED NULL`
- `algo_tx_id VARCHAR(128) NULL`
- `refund_algo_tx_id VARCHAR(128) NULL`

**Compatibilità (campi legacy)** — *non si rimuovono fino a fine migrazione*:
- `fiat_currency` (default storico USD), `offer_amount_fiat`, `offer_amount_algo` (microALGO), `exchange_rate` (FIAT per 1 ALGO), `exchange_timestamp`, `original_currency`, `original_price`, `algo_price`, `rate_timestamp`.
- **Mappatura**: continuare a scrivere i legacy dove richiesto dai flussi storici; i **nuovi** diventano fonte per report e audit.

## Opzione Strutturale – Patch B (pulita)
- **`fx_quotes`**: `id, egi_id, amount_eur, usd_per_eur, gbp_per_eur, algo_per_eur, provider, quoted_at, expires_at`.
- **`payments`**: `id, reservation_id, amount_eur, buyer_currency, buyer_amount, fx_rate_used, provider, quoted_at, executed_at, tx_id, method, algo_amount_micro`.

---

# API & Integrazione UI
- **Endpoint cambio**: consolidare l’uso degli endpoint presenti (rate singolo / all rates / convert / preferenza utente). Segnare come **legacy** eventuali path obsoleti e mappare i nuovi (es. `/api/currency/algo-exchange-rate`).
- **Currency Selector** in header: già operativo con persistenza (auth) e session (guest). La card v2 **consuma** la preferenza e la stima senza duplicare logica.
- **Currency Price Blade component**: usato come fallback server‑side se JS assente; il **PricePresenter** fornisce stringhe pronte (`primary`, `secondary≈`).
- **Regola visuale**: primario **sempre EUR**; stima con prefisso **≈** nella valuta utente.

---

# Error Handling (UEM) & Logging (ULM)
**Codici UEM da usare** (copertura i18n IT/EN completa):
- `CURRENCY_EXCHANGE_SERVICE_UNAVAILABLE`
- `CURRENCY_INVALID_RATE_DATA`
- `CURRENCY_RATE_CACHE_ERROR`
- `CURRENCY_CONVERSION_VALIDATION_ERROR`
- `CURRENCY_CONVERSION_ERROR`
- `CURRENCY_UNSUPPORTED_CURRENCY`
- `USER_PREFERENCE_UPDATE_FAILED`
- `USER_PREFERENCE_FETCH_ERROR`

**Eventi ULM** (telemetria operativa):
- `fx.quote.issued` {egi_id|reservation_id, rate, provider, ttl}
- `fx.quote.expired` {quote_id}
- `payment.executed` {reservation_id, buyer_currency, buyer_amount, rate, tx_id}
- `escrow.locked` {reservation_id, microalgo, rate, tx_id}
- `refund.executed` {reservation_id, amount_eur, buyer_currency, rate, tx_id}

---

# UI/UX – Card & Checkout
- **Card**: prezzo primario EUR; stima ≈ nella valuta utente; chip prenotazione con valuta e timestamp bloccati; nessuna riconversione del primario.
- **Checkout**: mostra quota con **timer** (TTL); testo di fiducia “Prezzo ufficiale in euro. Se paghi in USD/GBP, il cambio viene bloccato per 15 minuti al checkout”.
- **Escrow ALGO**: mostra importo ≈ ALGO e countdown di scadenza.

---

# Performance
- Cache consultazione tassi (UI) per 60s.
- Quote pagamento sempre *fresh* (no cache) con TTL e anti‑replay.
- Indici: `fx_quote_expires_at`, `payment_executed_at`, `(egi_id, status)` su reservations.

---

# Test di Accettazione
1) **Pay Now EUR**: prezzo 1.250 €, pagamento EUR; escrow ALGO calcolato da `algo_per_eur`; log completo.
2) **Pay Now USD**: quota 15′, pagamento USD; `buyer_amount`, `fx_rate_used`, `algo_amount_locked_micro` salvati; chip corretta.
3) **Quote Expired**: scadenza → impedisci pagamento, richiedi rigenerazione.
4) **Reserve‑NoPay → Pay Later**: nessun ALGO fino al checkout; al pagamento usa il tasso corrente.
5) **Refund Partial**: rimborso 250 €; pagamento originale USD; rimborso in USD al tasso del rimborso; log salvato.
6) **Idempotenza**: doppio click su “Paga” → 1 solo `payment.executed`.
7) **Rounding/Buffer**: differenze cent/microALGO; eccedenza rimborsata.

---

# Rollout
- **Fase 0 – Add only**: aggiungi i nuovi campi e avvia **dual‑write**.
- **Fase 1 – Switch UI**: card/checkout leggono i campi nuovi; microcopy aggiornata.
- **Fase 2 – Job TTL**: scadenza quote e soft‑hold; notifiche.
- **Fase 3 – Pulizia**: depreca i campi legacy; (opz.) split verso `fx_quotes`/`payments`.

---

# Allegato A — Data Dictionary (campi nuovi)
| Campo | Tipo | Semantica |
|---|---|---|
| amount_eur | DECIMAL(12,2) | Importo contrattuale EGI in EUR |
| buyer_currency | CHAR(3) | Valuta pagamento scelta dall’utente |
| buyer_amount | DECIMAL(12,2) | Importo effettivamente pagato nella buyer currency |
| fx_provider | VARCHAR(32) | Sorgente del tasso (es. Coinbase/Kraken/Oracle) |
| fx_quote_id | VARCHAR(64) | Id quotazione mostrata al buyer |
| fx_rate_used | DECIMAL(20,10) | **ALGO per 1 EUR** (o FIAT per 1 EUR) usato al pagamento |
| fx_quoted_at | TIMESTAMP | Emissione quotazione |
| fx_quote_expires_at | TIMESTAMP | Scadenza quotazione (TTL) |
| payment_executed_at | TIMESTAMP | Momento effettivo del pagamento |
| payment_method | ENUM | 'algo' | 'usdc' | 'card' | 'bank' |
| algo_amount_locked_micro | BIGINT UNSIGNED | Importo ALGO bloccato (µALGO) |
| algo_tx_id | VARCHAR(128) | Txid Algorand dell’escrow |
| refund_algo_tx_id | VARCHAR(128) | Txid eventuale rimborso |

---

# Allegato B — Microcopy pronta (ITA)
- "Prezzo ufficiale in euro. Se paghi in USD/GBP, il cambio è bloccato per 15 minuti al checkout."
- "Stima nella tua valuta (tasso live, può variare)."
- "Prenotato: $ 1,250 (bloccato 12/08 15:32)"
- "Quota scaduta: rigenera per procedere"
- "Pagamento duplicato rilevato: nessun addebito ulteriore"
- "Eccedenza rimossa: rimborsati 0.024300 ALGO"

---

# Conclusione
Con EUR come valuta canonica e un layer di quotazione a tempo per USD/GBP/ALGO, l’esperienza è chiara per l’utente europeo, la contabilità è semplice e l’audit è chiuso. La v1.1 allinea standard dei tassi, dual‑write con i campi legacy, componenti UI già esistenti e codici UEM/ULM effettivi, facilitando adozione e manutenzione.

