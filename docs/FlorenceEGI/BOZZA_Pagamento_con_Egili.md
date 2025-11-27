# Sistema di Pagamento con Egili – Bozza Esplicativa

## Premessa

Questo documento descrive il **nuovo meccanismo di pagamento con Egili** per il minting di EGI, integrando il sistema esistente di 3 livelli (No Wallet FIAT, Wallet proprio FIAT, Crypto via PSP).

---

## Cos'è Egili?

**Egili** è l'**utility token interno** di FlorenceEGI con le seguenti caratteristiche:

### Caratteristiche Fondamentali

- ✅ **Non trasferibile**: Non può essere scambiato tra utenti
- ✅ **Non quotato**: Nessuna quotazione su exchange esterni
- ✅ **Non convertibile**: Non può essere convertito direttamente in denaro
- ✅ **Utility pura**: Utilizzabile SOLO all'interno della piattaforma FlorenceEGI
- ✅ **Merit-based**: Si guadagna attraverso azioni meritevoli, non si compra

### Finalità

Egili serve per:

1. **Ridurre le fee** della piattaforma (commissioni di servizio)
2. **Pagare parzialmente o totalmente** il minting di nuovi EGI
3. **Accedere a funzionalità premium** (future implementazioni)
4. **Ottenere early access** a drop esclusivi (future implementazioni)

---

## Come si Guadagnano gli Egili?

Gli Egili sono **assegnati automaticamente** dalla piattaforma secondo criteri meritocratici:

### 1. Volume di Vendite

**Formula base**:

```
Per ogni €1,000 di vendite generate → Guadagni X Egili
```

**Esempio pratico**:

- Creator vende opere per €5,000 in un mese
- Riceve: `5 × X Egili` (dove X è il parametro configurabile dalla piattaforma)

---

### 2. Referral Verificati

**Meccanica**:

```
Per ogni nuovo utente portato che completa la verifica KYC → Y Egili
```

**Condizioni**:

- Utente deve completare onboarding completo
- Utente deve effettuare almeno 1 transazione (acquisto o vendita)
- Bonus erogato dopo 30 giorni (anti-gaming)

---

### 3. Donazioni EPP Volontarie

**Bonus aggiuntivo**:

```
Donazione volontaria EPP oltre il 20% standard → Bonus Egili proporzionale
```

**Esempio**:

- Standard: 20% EPP obbligatorio
- Creatore dona volontariamente 5% extra → Riceve bonus Egili

---

### 4. Partecipazione Community

**Attività premiate**:

- Partecipazione eventi ufficiali FlorenceEGI
- Feedback costruttivo (bug report, suggerimenti accettati)
- Contributi alla community (tutorial, guide, supporto altri utenti)
- Milestone individuali (primo EGI venduto, 10 EGI venduti, etc.)

**Assegnazione**: Valutata discrezionalmente dal team FlorenceEGI.

---

### 5. Fondo di Distribuzione Egili

**Meccanismo di finanziamento**:

```
1% delle commissioni di servizio incassate da FlorenceEGI → Pool Egili
```

**Esempio**:

- FlorenceEGI incassa €100,000 di fee in un mese
- €1,000 destinati al Pool Egili
- Pool distribuito a Creator secondo criteri merito

**Trasparenza**: Dashboard pubblica con:

- Pool totale disponibile
- Egili distribuiti nel periodo
- Top earners (anonimizzati o pubblici, a scelta utente)

---

## Come si Utilizzano gli Egili?

### Meccanismo di "Burn" (Consumo)

**Principio fondamentale**:

```
Utilizzo Egili = Consumo IRREVERSIBILE
```

Quando un utente **spende Egili**, questi vengono:

1. **Rimossi definitivamente** dal suo saldo
2. **Bruciati** (burned) dal sistema
3. **NON tornano** nel pool di distribuzione (deflationary mechanism)

---

### Tasso di Conversione Egili → Sconto

**Formula generale**:

```
1 Egili = €0.01 di sconto sulle fee
```

**Esempio pratico**:

#### Scenario 1: Riduzione Fee su Transazione Esistente

**Operazione**: Creator vende opera a €1,000

- Fee piattaforma: 10% = €100
- Creator ha 500 Egili nel saldo
- Decide di utilizzare 200 Egili

**Calcolo**:

```
Sconto = 200 Egili × €0.01 = €2.00
Fee finale = €100 - €2 = €98
Egili rimanenti = 500 - 200 = 300
```

---

#### Scenario 2: Pagamento Parziale Minting

**Operazione**: Collector vuole mintare un EGI del valore di €500

- Fee minting: 10% = €50
- Collector ha 2,000 Egili
- Decide di pagare **interamente la fee** con Egili

**Calcolo**:

```
Fee minting = €50
Egili necessari = €50 / €0.01 = 5,000 Egili
Egili disponibili = 2,000 Egili (insufficienti)

→ Pagamento parziale:
   - 2,000 Egili = €20 sconto
   - Rimanente €30 da pagare in FIAT
```

**Flusso di checkout**:

1. Totale: €500 (opera) + €50 (fee) = €550
2. Applicazione Egili: -€20 (2,000 Egili bruciati)
3. **Da pagare in FIAT**: €530
4. Metodo pagamento: Carta/Bonifico/PSP

---

#### Scenario 3: Pagamento Totale Minting con Egili

**Operazione**: Creator esperto vuole mintare nuova collezione (10 opere × €200)

- Totale opere: €2,000
- Fee minting totale: 10% = €200
- Creator ha 25,000 Egili (accumulati in anni di attività)

**Calcolo**:

```
Fee minting = €200
Egili necessari = €200 / €0.01 = 20,000 Egili
Egili disponibili = 25,000 Egili (sufficienti)

→ Pagamento totale in Egili:
   - 20,000 Egili bruciati
   - €0 da pagare in FIAT (solo fee coperta)
   - Egili rimanenti = 5,000
```

**IMPORTANTE**:

- Gli Egili coprono SOLO le **fee di servizio** (commissioni piattaforma)
- Il **valore dell'opera** (€2,000) deve essere pagato in FIAT/Crypto secondo livelli esistenti

---

## Integrazione con Livelli di Pagamento Esistenti

### Livello 0 (NUOVO): Pagamento Totale Egili

**Condizioni**:

- Utente ha Egili sufficienti a coprire fee minting
- Valore opera = €0 (minting gratuito per Creator proprie opere)

**Flusso**:

```
1. Creator carica opera (metadata, immagine, CoA)
2. Sceglie "Paga con Egili"
3. Sistema calcola fee minting (es: €10)
4. Verifica saldo Egili (es: 5,000 disponibili, serve 1,000)
5. Conferma: Brucia 1,000 Egili
6. Minting eseguito SENZA pagamento FIAT
7. Wallet auto-generato (Livello 1) o wallet proprio (Livello 2)
```

**MiCA-safe**: ✅

- Egili = punti fedeltà (fuori perimetro MiCA)
- Nessun trasferimento fondi/crypto
- Solo consumo utility interna

---

### Livello 1: No Wallet FIAT + Egili

**Flusso combinato**:

```
1. Collector acquista EGI a €500
2. Fee piattaforma: €50 (10%)
3. Collector ha 2,000 Egili → Sconto €20
4. Totale FIAT da pagare: €500 + €30 = €530
5. Pagamento PSP (Stripe/Adyen)
6. Wallet auto-generato (custodia tecnica limitata NFT)
7. 2,000 Egili bruciati dal saldo
```

**Dashboard checkout**:

```
┌─────────────────────────────────────┐
│ Riepilogo Acquisto                  │
├─────────────────────────────────────┤
│ Opera: "Sunset #42"         €500.00 │
│ Fee piattaforma (10%)        €50.00 │
│ Sconto Egili (2,000)        -€20.00 │
├─────────────────────────────────────┤
│ TOTALE                      €530.00 │
└─────────────────────────────────────┘
│ Egili rimanenti dopo acquisto: 0    │
└─────────────────────────────────────┘
```

---

### Livello 2: Wallet Proprio FIAT + Egili

**Identico a Livello 1**, ma:

- Mint diretto su wallet utente (sender=wallet_address_utente)
- Non-custodial
- Export chiavi non necessario (utente già controlla wallet)

---

### Livello 3: Crypto via PSP + Egili

**Scenario**:

```
1. Merchant abilita pagamento stablecoin (USDCa)
2. Collector paga €500 in USDCa (via PSP/CASP partner)
3. Fee piattaforma: €50
4. Collector ha 3,000 Egili → Sconto €30
5. PSP notifica FlorenceEGI: "Pagamento ricevuto €500 + €20 fee"
   (€30 sconto Egili applicato pre-checkout)
6. Minting eseguito
7. 3,000 Egili bruciati
```

**IMPORTANTE**:

- PSP/CASP gestisce pagamento crypto (KYC/AML)
- FlorenceEGI applica sconto Egili sulla propria fee
- MiCA-safe mantenuto (no intermediazione crypto)

---

## Limitazioni e Regole d'Uso

### 1. Egili NON Coprono Valore Opera

**Regola fondamentale**:

```
Egili applicabili SOLO su fee piattaforma (commissioni servizio)
```

**Esempio**:

- Opera €1,000 + Fee €100
- Egili coprono max €100 (se sufficienti)
- Opera €1,000 DEVE essere pagata in FIAT/Crypto

**Eccezione**: Minting proprio Creator (valore opera = €0)

---

### 2. Tasso di Conversione Variabile

**FlorenceEGI si riserva il diritto** di modificare il tasso:

- Preavviso: **30 giorni**
- Pubblicazione: Dashboard + email notifica
- **NON retroattivo**: Egili già accumulati mantengono valore al tasso corrente

**Esempio scenario futuro**:

```
Oggi: 1 Egili = €0.01
Nuovo tasso (tra 30gg): 1 Egili = €0.008

Utente con 10,000 Egili oggi:
- Valore attuale: €100
- Valore tra 30gg (se non spesi): €80
→ Incentivo a utilizzare Egili prima della modifica
```

---

### 3. Scadenza Temporale (Anti-Hoarding)

**Meccanismo futuro** (da implementare):

```
Egili non utilizzati entro 12 mesi → Scadenza 50%
Egili non utilizzati entro 24 mesi → Scadenza 100%
```

**Finalità**:

- Incentivare circolazione
- Evitare accumulo speculativo
- Premiare utilizzo attivo

**Status attuale**: **NON implementato** (da valutare)

---

### 4. Cumulo con Fee Dinamiche

**Regola di applicazione**:

```
Sconto Egili si applica DOPO riduzione fee dinamiche
```

**Esempio**:

```
Volume Creator: €50,000 cumulativo
Fee base: 10%
Fee dinamica: 7% (riduzione per volume)
Opera venduta: €1,000

Calcolo fee:
1. Fee dinamica: €1,000 × 7% = €70
2. Sconto Egili: 1,000 Egili = €10
3. Fee finale: €70 - €10 = €60

Risparmio totale rispetto base:
- Fee base (10%): €100
- Fee effettiva: €60
- Risparmio: €40 (40%)
```

**Vantaggio Creator**: Massimo risparmio cumulando volume + Egili.

---

## Aspetti Fiscali e Compliance

### Inquadramento Egili

**Posizione legale** (riferimento: `nota_tecnica_fiscale_crypto_florence_egi_italia_2025.md`):

Egili sono **punti fedeltà**, NON crypto-asset:

- ✅ **Fuori perimetro MiCA** (non utility token né payment token)
- ✅ **Fuori perimetro fiscale crypto** (no quadro RW, no monitoraggio)
- ✅ **Trattamento commerciale**: Sconto su servizio (come coupon/voucher)

---

### Fatturazione con Sconto Egili

**Principio**:

```
Fattura emessa per importo NETTO (dopo sconto Egili)
```

**Esempio**:

```
Operazione: Vendita EGI €1,000 + Fee €100
Sconto Egili: €20
Totale incassato: €1,080

Fattura Creator → Acquirente:
- Imponibile: €1,000
- IVA (se applicabile): €220 (22%)
- Totale: €1,220 (gestione IVA a carico Creator secondo regime)

Fattura FlorenceEGI → Creator (per servizio):
- Commissione servizio: €80 (€100 - €20 sconto Egili)
- IVA: €17.60
- Totale: €97.60
```

**Nota contabile**:

- Sconto Egili = **Sconto commerciale** (Art. 15 DPR 633/72)
- Riduci base imponibile fee
- Nessun obbligo dichiarativo specifico

---

### Rendicontazione Dashboard

**Report mensile Creator** include:

```
┌──────────────────────────────────────────┐
│ Report Mensile - Gennaio 2025            │
├──────────────────────────────────────────┤
│ Vendite totali:              €15,000.00  │
│ Fee piattaforma base:         €1,500.00  │
│ Fee dinamica applicata (7%):  €1,050.00  │
│ Sconto Egili (5,000 burned):    -€50.00  │
│ Fee effettiva pagata:         €1,000.00  │
├──────────────────────────────────────────┤
│ Egili guadagnati:                15,000  │
│ Egili spesi:                      5,000  │
│ Saldo Egili:                     10,000  │
└──────────────────────────────────────────┘
```

**Export CSV/XML** compatibile con software fiscali.

---

## Vantaggi per l'Ecosistema

### Per i Creator

✅ **Riduzione costi operativi**: Meno fee = più margine
✅ **Incentivo vendite**: Più vendi, più Egili guadagni, più risparmi
✅ **Fidelizzazione**: Vantaggio cumulativo nel tempo
✅ **Minting gratuito**: Opere proprie mintabili senza costi FIAT (se abbastanza Egili)

---

### Per i Collector

✅ **Sconti acquisti**: Meno fee su ogni transazione
✅ **Reward partecipazione**: Referral, community, contributi premiati
✅ **Esperienza gamificata**: Accumulo Egili come achievement

---

### Per la Piattaforma

✅ **Engagement**: Incentivo utilizzo continuativo
✅ **Anti-speculazione**: Egili non trasferibili/vendibili
✅ **Sostenibilità economica**: 1% fee → Pool Egili (autofinanziato)
✅ **Compliance**: Fuori MiCA (semplificazione normativa)
✅ **Circolarità**: Burn Egili riduce supply (deflationary)

---

## Implementazione Tecnica (Cenni)

### Database Schema

```sql
-- Tabella Egili Balance
CREATE TABLE egili_balances (
    user_id BIGINT PRIMARY KEY,
    balance INT DEFAULT 0,
    total_earned INT DEFAULT 0,
    total_burned INT DEFAULT 0,
    last_updated TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Tabella Transazioni Egili
CREATE TABLE egili_transactions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,
    type ENUM('earn', 'burn'),
    amount INT,
    reason VARCHAR(255), -- 'sales_volume', 'referral', 'fee_discount', etc.
    related_transaction_id BIGINT, -- Link a transazione EGI se applicabile
    created_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

---

### API Endpoints (Esempi)

```javascript
// Ottieni saldo Egili utente
GET /api/v1/egili/balance
Response: { "balance": 5000, "total_earned": 15000, "total_burned": 10000 }

// Calcola sconto applicabile
POST /api/v1/egili/calculate-discount
Body: { "fee_amount": 100, "egili_to_use": 2000 }
Response: { "discount": 20, "egili_required": 2000, "final_fee": 80 }

// Applica sconto Egili (burn)
POST /api/v1/egili/apply-discount
Body: { "transaction_id": 12345, "egili_amount": 2000 }
Response: { "success": true, "new_balance": 3000, "discount_applied": 20 }
```

---

### Smart Contract Integration

**Nessuna necessità** di smart contract on-chain per Egili:

- Egili = database off-chain (gestione centralizzata piattaforma)
- **NON sono crypto-asset** (no blockchain)
- Tracciabilità: AuditLog + ULM (sufficient per compliance)

**Vantaggio**:

- Zero costi gas
- Modificabilità parametri (tasso, scadenza, etc.)
- Performance elevate (no latenza blockchain)

---

## Roadmap Implementazione

### Fase 1: MVP (Q1 2025)

- ✅ Guadagno Egili da volume vendite
- ✅ Sconto su fee transazioni esistenti
- ✅ Dashboard visualizzazione saldo
- ✅ Report mensile con Egili earned/burned

---

### Fase 2: Estensione (Q2 2025)

- 🔄 Pagamento fee minting con Egili
- 🔄 Referral system con bonus Egili
- 🔄 Gamification (milestone, achievement)
- 🔄 Export dettagliato transazioni Egili

---

### Fase 3: Advanced Features (Q3 2025)

- 📋 Tipping artisti con Egili
- 📋 Early access drop (spendi Egili per prenotazione)
- 📋 VIP tiers (accumulo Egili → status premium)
- 📋 Marketplace Egili interno (scambio Egili per servizi premium, NO cash-out)

---

## Domande Frequenti (FAQ)

### 1. Posso vendere i miei Egili?

**NO**. Egili sono **non trasferibili** e **non scambiabili** esternamente. Possono essere utilizzati SOLO all'interno della piattaforma FlorenceEGI.

---

### 2. Posso convertire Egili in euro?

**NO**. Egili **non sono convertibili** in denaro. Possono solo **ridurre le fee** o essere usati per servizi interni.

---

### 3. Egili scadono?

**Attualmente NO**. In futuro potrebbe essere introdotta una scadenza (12-24 mesi) con preavviso di 30 giorni.

---

### 4. Quanti Egili ricevo per ogni vendita?

Il tasso esatto è configurabile dalla piattaforma e pubblicato nella dashboard. **Esempio indicativo**: 1 Egili ogni €100 di vendite.

---

### 5. Posso usare Egili per pagare l'opera (non solo la fee)?

**NO**. Egili coprono SOLO le **fee di servizio** (commissioni piattaforma). Il valore dell'opera deve essere pagato in FIAT o crypto.

**Eccezione**: Minting proprio Creator (valore opera = €0).

---

### 6. Cosa succede agli Egili bruciati?

Gli Egili bruciati vengono **rimossi definitivamente** dalla circolazione (deflationary mechanism). **NON tornano** nel pool di distribuzione.

---

### 7. Egili sono tassati?

**NO**. Egili sono **punti fedeltà** (sconto commerciale), fuori perimetro fiscale crypto. Nessun obbligo dichiarativo.

---

### 8. Posso regalare Egili ad altri utenti?

**Attualmente NO** (non trasferibili). In futuro potrebbe essere implementato un sistema di **tipping** (donazione diretta artista, con limite massimo).

---

## Conclusioni

Il sistema di pagamento con **Egili** rappresenta un'evoluzione naturale dell'ecosistema FlorenceEGI, offrendo:

1. **Incentivi concreti** a Creator e Collector per partecipazione attiva
2. **Riduzione costi** operativi (fee più basse)
3. **Gamification** e fidelizzazione utenti
4. **Compliance totale** (fuori MiCA, punti fedeltà)
5. **Sostenibilità economica** (autofinanziato da 1% fee)

**Prossimo step**: Integrare questa logica nel documento **04_Gestione_Pagamenti.md** come **Livello 0** o sezione dedicata.

---

## Note per Integrazione in 04_Gestione_Pagamenti.md

**Sezioni da aggiungere**:

1. **Introduzione Egili** (dopo premessa)

   - Cos'è, come si guadagna, caratteristiche fondamentali

2. **Livello 0: Pagamento con Egili** (prima di Livello 1)

   - Flusso minting totale in Egili
   - Limitazioni (solo fee, non valore opera)

3. **Integrazione Egili in Livelli 1-3**

   - Esempi checkout combinato (FIAT + Egili, Crypto + Egili)
   - Dashboard riepilogo sconto

4. **Sezione Fiscalità Egili** (dopo compliance MiCA-safe)

   - Inquadramento punti fedeltà
   - Fatturazione con sconto

5. **FAQ Egili** (sezione finale)

**Tono**: Mantenere coerenza con stile attuale (tecnico, esempi pratici, MiCA-safe highlights).
