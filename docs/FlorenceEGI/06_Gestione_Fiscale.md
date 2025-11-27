# FlorenceEGI – Gestione Fiscale e Rendicontazione

## Principi Guida

### 1. Trasparenza Radicale

Ogni flusso economico è **tracciabile, ricostruibile e accessibile** per garantire massima chiarezza grazie alla blockchain.

### 2. Automazione Intelligente

Generazione automatica di **report, ricevute e fatture elettroniche** per ridurre il rischio di errore umano.

### 3. Responsabilità Chiara

Ogni attore conosce esattamente i propri obblighi. **La piattaforma non agisce mai da sostituto d'imposta.**

---

## Ruolo della Piattaforma

### Obblighi Fiscali di FlorenceEGI

#### Gestione Fee

- Incassa **esclusivamente la propria fee** di servizio (default 10%)
- I fondi **non vengono mai trattenuti** per conto di terzi (MiCA-safe)
- Le fee vengono accreditate **direttamente sul wallet** della piattaforma
- **Zero intermediazione finanziaria**

#### Fatturazione e IVA

- Emette **fattura elettronica** per ogni fee incassata tramite SDI (Sistema di Interscambio)
- Adotta **fatturazione cumulativa (batch)** per operazioni ad alto volume
- Gestisce IVA secondo normative nazionali e internazionali (OSS/MOSS)

### Flusso Finanziario della Piattaforma

```
Transazione Utente (Minting/Trading)
    ↓
Separazione Fee
    ↓
Wallet FlorenceEGI (Fee incassata)
    ↓
Fatturazione (emissione fattura all'utente)
```

---

## Gestione Fiscale per Creator e Mecenati

### Se Sei un Privato

#### Vendite Occasionali

Per vendite **non abituali**, devi emettere:

- **Ricevuta per prestazione occasionale**
- Dichiarare il reddito come **"reddito diverso"** nella dichiarazione annuale

**Soglie**:

- Limite prestazione occasionale: **€5,000/anno**
- Oltre questa soglia: **obbligo apertura Partita IVA**

#### Se l'Attività Diventa Abituale

**Obbligo Partita IVA** quando:

- Vendite regolari (continuità)
- Organizzazione mezzi (studio, galleria)
- Scopo di lucro prevalente

---

### Se Hai Partita IVA

#### Fatturazione Elettronica Obbligatoria

Per ogni incasso ricevuto:

- **Fattura XML** standard FatturaPA 1.6.1
- Invio tramite SDI (Sistema di Interscambio)
- Applicazione regime fiscale proprio (forfettario, ordinario, etc.)
- IVA se prevista (22% aliquota ordinaria, esenzioni per opere d'arte originali)

#### Esenzione IVA Opere d'Arte (Art. 10 DPR 633/72)

**Opere d'arte originali esenti IVA**:

- Quadri, collage, pitture
- Incisioni, stampe, litografie originali
- Sculture originali

**Condizioni**:

- Opera creata dall'artista
- Esemplari unici o tiratura limitata (max 8 copie + 4 prove d'artista)
- Artista titolare diritti d'autore

---

### Strumenti a Disposizione

#### Dashboard con Report Dettagliato

- Vendite totali (primarie + secondarie)
- Royalty ricevute (4.5% automatiche)
- Donazioni EPP (se applicabile)
- Split per tipologia reddito

#### Export Dati (CSV/XML)

- Download transazioni per contabilità
- Formato compatibile con software fiscali (Teamsystem, Zucchetti, etc.)
- Export mensile/trimestrale/annuale

#### Alert Automatici

Notifiche al raggiungimento soglie fiscali:

- **€5,000**: Limite prestazione occasionale (consiglia apertura P.IVA)
- **€10,000**: Soglia monitoraggio fiscale
- **€30,000**: Obbligo split payment (se B2B con PA)

#### Modelli Scaricabili

- Template ricevuta prestazione occasionale
- Bozza fattura elettronica XML
- Guida dichiarazione redditi diversi

---

### Flusso di Incasso per Creator

```
1. Vendita EGI
   ↓
2. Accredito Diretto (al netto fee piattaforma)
   ↓
3. Il Tuo Wallet (fondi ricevuti direttamente)
   ↓
4. Compliance Fiscale (emetti fattura/ricevuta + dichiari reddito)
```

**Ricorda**: Sei l'unico responsabile della tua dichiarazione fiscale. FlorenceEGI **non è sostituto d'imposta**.

---

## Gestione Fiscale per EPP (Enti Ambientali)

### Piccoli Enti No Profit (ETS/ONLUS)

#### Obblighi

- **NON devono** emettere fattura elettronica per donazioni
- **Devono** rilasciare **ricevuta di donazione** (solo su richiesta donatore)
- Ricevuta può essere **cumulativa** (annuale o mensile) per semplificazione

#### Gestione Fondi

- Fondi ricevuti **direttamente sul wallet** dell'ente
- Zero intermediazione FlorenceEGI
- Tracciabilità completa on-chain

#### Ricevuta Donazione

**Contenuto minimo**:

- Denominazione ente (ETS/ONLUS)
- Codice fiscale ente
- Importo donazione
- Nome/cognome donatore (o Anonimo)
- Data donazione
- Firma legale rappresentante

**Finalità**: Detrazioni/deduzioni fiscali per donatore (Art. 15 TUIR)

---

### Grandi Enti e Aziende

#### Gestione Interna

Compliance gestita tramite:

- **ERP** (Enterprise Resource Planning): SAP, Oracle, etc.
- **CRM** (Customer Relationship Management): Salesforce, HubSpot, etc.

#### Integrazione FlorenceEGI

- **Export dati** (CSV/XML) per import in ERP
- **API webhook** per notifiche real-time donazioni
- **Report personalizzati** (trimestrale/annuale)

**Responsabilità documentazione fiscale**: Ente (non piattaforma)

---

### Flusso di Donazione per EPP

```
Donazione Utente (tramite acquisto EGI)
    ↓
Accredito Diretto (quota EPP sul wallet ente)
    ↓
Wallet EPP (ente riceve fondi)
    ↓
Ricevuta (su richiesta donatore, opzionale)
```

---

## Gestione Fiscale per Trader e Alto Flusso

### Gestione Fee di Piattaforma

#### Fatturazione Batch (Cumulativa)

Per alto numero micro-transazioni:

- **Una sola fattura elettronica periodica** (mensile)
- Allegato **report dettagliato** con singole transazioni
- Formato XML compatibile SDI

**Esempio**:

- Trader esegue 500 trade/mese
- Fee totali: €250
- Riceve: 1 fattura mensile €250 + allegato CSV 500 righe

---

### Gestione Donazioni agli EPP

#### Ricevuta Cumulativa

Anche micro-donazioni derivanti da migliaia di trade:

- **Richiesta ricevuta cumulativa** all'EPP (annuale/mensile)
- Download automatico da dashboard
- Valida per detrazioni fiscali

---

### Responsabilità sulla Plusvalenza

⚠️ **ATTENZIONE**: FlorenceEGI **NON si occupa** della fiscalità transazioni tra utenti.

**Sei responsabile** di dichiarare:

- **Plusvalenze** (capital gains): Profitto da rivendita asset
- **Minusvalenze** (capital losses): Perdita da rivendita

#### Come Calcolare Plusvalenza

```
Plusvalenza = Prezzo Vendita - Prezzo Acquisto - Fee - Costi
```

**Esempio**:

- Acquisto EGI: €1,000
- Rivendita EGI: €1,500
- Fee piattaforma: €50
- Plusvalenza: €1,500 - €1,000 - €50 = **€450**

#### Regime Fiscale Plusvalenze

**Italia** (art. 67 TUIR):

- **Aliquota**: 26% (imposta sostitutiva)
- **Regime dichiarativo**: Quadro RT dichiarazione redditi
- **Regime amministrato**: Tramite intermediario (non applicabile FlorenceEGI)

**FlorenceEGI fornisce**: Report completo transazioni per facilitare calcoli.

---

## Gestione IVA e Fiscalità Internazionale

### Utenti Residenti in Italia

**IVA sulle fee piattaforma**:

- Aliquota: **22%** (ordinaria)
- Fattura elettronica via SDI

---

### Utenti Residenti in UE

#### Privati (B2C)

**Regime OSS** (One Stop Shop):

- IVA del **paese di residenza consumatore**
- FlorenceEGI dichiara e versa tramite portale OSS italiano
- Nessun obbligo per utente

#### Aziende con Partita IVA UE (B2B)

**Reverse Charge**:

- Fattura emessa **senza IVA**
- Cliente applica IVA del proprio paese
- Meccanismo inversione contabile

**Esempio**:

- Cliente tedesco con Partita IVA DE
- Fattura FlorenceEGI: €100 + €0 IVA (reverse charge)
- Cliente tedesco autoliquida IVA 19% in Germania

---

### Utenti Residenti Extra-UE

**Fatture senza IVA**:

- Servizi digitali a soggetti extra-UE generalmente esenti IVA
- Transazione tracciata e segnalata secondo normative

**Obbligo dichiarativo**: INTRASTAT (se soglie superate)

---

### Donazioni e Vendite tra Utenti

#### Donazioni agli EPP

**NON soggette a IVA** (atti di liberalità).

#### Vendite tra Utenti (Creator → Collector)

**IVA dipende da**:

- Regime fiscale venditore (merchant)
- Tipo opera (esenzione art. 10 se opera originale)
- Residenza acquirente

**Responsabilità**: Venditore (merchant)

---

## Merchant, Pagamenti e Fatturazione

### Registrazione e Autenticazione

Ogni **merchant** (chi vende EGI) deve:

- **Autenticazione forte**: SPID, CIE, OTP o equivalente
- **Dati fiscali validi**: Partita IVA o Codice Fiscale
- **Accettazione termini**: Utilizzo + informativa GDPR

---

### Metodi di Pagamento

Merchant può abilitare:

- **FIAT**: Tramite PSP convenzionati (Stripe, Adyen)
- **Stablecoin**: USDCa, EURC su Algorand
- **Criptovalute**: ALGO, BTC, ETH tramite PSP dedicati

**Responsabilità merchant**: Corretta ricezione pagamenti + gestione wallet.

**FlorenceEGI non custodisce chiavi private**, non riceve fondi, non partecipa ai flussi di pagamento.

---

### Uso di PSP Autorizzati

Merchant può usare **PSP esterni** per ricezione/conversione crypto:

- Rapporti contrattuali (KYC, AML) **direttamente** tra merchant e PSP
- FlorenceEGI riceve solo **notifica pagamento completato**
- Piattaforma procede con registrazione EGI su blockchain

---

### Fatturazione Elettronica

Per ogni transazione completata, FlorenceEGI fornisce al merchant:

- **Dati necessari** per fatturazione elettronica
- **Integrazione SDI**: Provider accreditato (standard FatturaPA 1.6.1)

#### Opzioni Merchant

1. **Sistema automatico** fornito da FlorenceEGI
2. **File XML** per invio tramite proprio software fatturazione

**Fatture emesse**: In nome e per conto del **merchant** (responsabile dati fiscali e adempimenti tributari).

---

### Trasparenza e Responsabilità

Tutti i merchant devono dichiarare:

- **Metodi pagamento accettati**
- **PSP utilizzato** (se applicabile)

FlorenceEGI mostra tali informazioni **prima della conferma d'acquisto**.

**Piattaforma NON interviene** nei flussi di denaro e non svolge funzioni di intermediazione finanziaria.

---

## Rendicontazione Fiscale e Conservazione Digitale

### Registro delle Transazioni

FlorenceEGI mantiene registro digitale completo:

- **Identificativo univoco** EGI
- **Data e ora** operazione
- **Importo lordo** + commissioni + quota EPP
- **Identificativi parti** (acquirente + merchant verificato)
- **Modalità pagamento** + PSP utilizzato
- **Riferimento fattura** o documento contabile

**Sicurezza**: Firmato digitalmente, archivi a prova di manomissione (audit trail).

---

### Rendicontazione Automatica

La piattaforma genera periodicamente:

#### Report Trimestrali

- Vendite per singolo merchant
- Formato CSV e XML
- Export automatico o download manuale

#### Report Annuali Riepilogativi

- Utili per dichiarazione redditi
- Include: vendite primarie, secondarie, royalty, donazioni EPP

#### Registro IVA

- Se applicabile (merchant con P.IVA)
- Per operazioni soggette a fatturazione elettronica

**Accesso**: Area riservata merchant, export/invio automatico a provider SDI.

---

### Conservazione Digitale (10 Anni)

FlorenceEGI garantisce **conservazione sostitutiva digitale** per:

- Fatture
- Ricevute
- Report fiscali

**Durata**: Minimo **10 anni** (normativa italiana - D.M. 17/06/2014 + Linee Guida AgID)

#### Provider Accreditato

- Conservazione presso provider SDI accreditato
- Alternativa: Infrastruttura cloud certificata

#### Sicurezza Garantita

- **Firma digitale** + **marca temporale**
- **Replica geografica** (backup multi-region)
- **Verifica integrità periodica** (hash SHA-256)

---

### Accesso e Trasparenza

Ogni merchant può accedere **real-time** alla propria situazione contabile tramite **area amministrativa personale**.

#### Accesso Autorità Competenti

Autorità (Agenzia delle Entrate, Guardia di Finanza) possono richiedere accesso dati fiscali tramite:

- **Procedura autenticata**
- Rispetto GDPR
- Norme riservatezza commerciale

---

## Ambito Normativo

Il sistema di rendicontazione e conservazione è conforme a:

- **Regolamento MiCA** (UE 2023/1114) - Esclusione attività CASP
- **Direttiva PSD2** (UE 2015/2366) - Nessuna intermediazione finanziaria
- **Regolamento GDPR** (UE 2016/679) - Protezione dati
- **Normativa italiana** fatturazione elettronica e conservazione digitale

---

## Responsabilità Utente vs Piattaforma

### Utente (Creator/Trader/EPP) - 85%

- Dichiarazione redditi
- Emissione fatture/ricevute
- Calcolo plusvalenze
- Versamento imposte
- Conservazione documentazione

### Piattaforma (FlorenceEGI) - 15%

- Fatturazione proprie fee
- Fornire report/export
- Conservazione digitale 10 anni
- Supporto compliance
- Tracciabilità transazioni

**FlorenceEGI NON è sostituto d'imposta**. Ogni utente è responsabile della propria compliance fiscale.
