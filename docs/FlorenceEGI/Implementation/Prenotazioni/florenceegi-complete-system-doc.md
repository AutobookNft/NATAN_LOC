# 🎨 FlorenceEGI - Sistema Completo: Prenotazioni, Attivazione e Mercato Secondario

## 📋 Executive Summary

FlorenceEGI è una piattaforma blockchain-based (Algorand) che rivoluziona il mercato NFT attraverso un sistema di **attivazione sociale** dove gli acquirenti diventano parte permanente della storia dell'opera. Il sistema opera in tre fasi distinte: Prenotazioni, Attivazione/Mint, e Mercato Secondario.

---

## 🏗️ ARCHITETTURA DEL SISTEMA

### **Principi Fondamentali**

1. **No Custodia Fondi**: FlorenceEGI NON custodisce mai denaro
2. **Proof of Interest**: Le prenotazioni validano l'interesse prima del mint
3. **Immortalità Digitale**: Gli attivatori sono legati all'opera per sempre
4. **Impact Automatico**: Ogni transazione supporta progetti ambientali (EPP)

### **Tecnologie Core**

- **Blockchain**: Algorand (ASA per gli EGI)
- **Database**: MySQL con campi legacy e nuovi
- **Pagamenti**: PSP esterni (Stripe Connect, bonifici)
- **Framework**: Laravel 11 + TypeScript
- **Error Management**: UEM (Ultra Error Manager)
- **Logging**: ULM (Ultra Log Manager)

---

## 👥 ATTORI DEL SISTEMA

### **User Types (Definiti alla Registrazione)**

#### **Commissioner** 🎭
- **Visibilità totale**: nome e volto pubblici per sempre
- **Motivazione**: ricerca di riconoscimento sociale
- **Benefici**: immortalità digitale, possibili royalty
- **Privacy**: rinuncia consapevole all'anonimato

#### **Collector** 🔒
- **Completamente anonimo**: solo wallet address
- **Motivazione**: investimento discreto
- **Benefici**: stessi diritti economici del Commissioner
- **Privacy**: totalmente preservata

#### **Creator** 🎨
- **Ruolo doppio**: crea EGI e può attivare altri EGI
- **Controllo**: decide timing del mint
- **Revenue**: riceve pagamenti diretti (80% tipicamente)
- **Anonimo** quando attiva EGI di altri

#### **Patron** 💎
- **Mecenate moderno**: supporta l'ecosistema
- **Anonimo**: privacy preservata
- **Portfolio**: visibilità delle attivazioni

#### **Altri Ruoli**
- **EPP**: Environment Protection Projects (ricevono 20%)
- **Company**: aziende che partecipano
- **Trader Pro**: operatori professionali

---

## 📊 FASE 1: SISTEMA PRENOTAZIONI (ATTUALE)

### **Concetto**
Le prenotazioni determinano chi avrà il diritto di attivare (acquistare) l'EGI quando il creator deciderà di mintarlo.

### **Flusso Operativo**

#### **1. Pubblicazione EGI**
- Creator pubblica EGI **senza mintarlo**
- EGI diventa "prenotabile"
- Nessun costo di mint upfront

#### **2. Processo Prenotazione**
```
Utente vede EGI → Clicca "Prenota" → Inserisce offerta EUR → 
Sistema calcola ranking → Notifiche inviate → Feedback posizione
```

#### **3. Ranking System**
- **Ordinamento**: amount_eur DESC, created_at ASC (tie-breaker)
- **Visibilità**: pubblica con nomi per Commissioner, anonima per altri
- **Aggiornamento**: real-time ad ogni nuova offerta
- **Modifiche**: utente può solo aumentare la propria offerta

#### **4. Notifiche**
- **Nuovo primo**: "Congratulazioni! Sei il più alto offerente!"
- **Superato**: "La tua offerta è stata superata"
- **Cambio significativo**: "Sei salito/sceso di X posizioni"
- **Solo in-app**: niente email spam

### **Database Schema Prenotazioni**

```sql
reservations:
- id
- egi_id
- user_id
- amount_eur (importo offerto)
- rank_position (posizione attuale)
- is_highest (boolean)
- superseded_by_id (chi ti ha superato)
- status (active/withdrawn/completed)
- is_current (boolean)
- created_at
- updated_at
```

### **Regole Business**

1. **Una prenotazione per utente per EGI**
2. **Nessun limite** importo minimo/massimo
3. **Nessun limite** numero totale prenotazioni
4. **Modificabile** solo in aumento
5. **Ritirabile** fino al mint
6. **UserType determina** visibilità (Commissioner pubblico, altri anonimi)

---

## 💰 FASE 2: ATTIVAZIONE E MINT

### **Trigger del Mint**
Creator decide basandosi su:
- Numero prenotazioni ricevute
- Importo highest bid
- Momentum del mercato

### **Processo Countdown**

#### **Step 1: Avvio**
- Creator clicca "Mint Now"
- Sistema invia notifica al primo in classifica
- Timer 24-48h per conferma

#### **Step 2: Cascata**
```
Primo conferma? → Procedi con pagamento
Primo rifiuta/timeout? → Passa al secondo
Secondo rifiuta? → Passa al terzo
... fino a conferma o esaurimento lista
```

#### **Step 3: Pagamento**
- **Via PSP esterno** (Stripe, bonifico)
- **Split automatico**:
  - 80% al Creator
  - 20% all'EPP
- **FlorenceEGI riceve** solo commissione servizio

#### **Step 4: Mint On-Chain**
- Conferma pagamento trigger mint
- Token ASA creato su Algorand
- Transfer all'attivatore
- Metadata con ruolo (Commissioner/Collector)

### **Gestione Beni Fisici**
Se EGI include componente fisica:
1. **Escrow logico** del pagamento
2. **Spedizione** dal creator
3. **QR conferma** ricezione
4. **Sblocco fondi** al creator

---

## 🔄 FASE 3: MERCATO SECONDARIO (FUTURO)

### **Trasformazione del Sistema**

Il sistema prenotazioni diventa **sistema aste** per EGI già mintati.

### **Tipologie di Vendita**

#### **Asta Tradizionale**
- Durata predefinita
- Rilanci incrementali
- Highest bidder vince

#### **Buy Now**
- Prezzo fisso
- Acquisto immediato
- No competizione

#### **Asta con Riserva**
- Prezzo minimo segreto
- Se non raggiunto, no vendita

### **Database Extension per Aste**

```sql
auctions:
- id
- egi_id
- seller_id
- type (traditional/buy_now/reserve)
- starting_price
- current_price
- reserve_price (nullable)
- starts_at
- ends_at
- winner_id (nullable)
- status

bids:
- id
- auction_id
- bidder_id
- amount
- is_winning
- created_at
```

### **Smart Contract Integration**
- Escrow automatico on-chain
- Settlement immediato
- Royalty distribution automatica

---

## 🏠 PORTFOLIO E VISIBILITÀ

### **Homepage Personale**

Ogni Commissioner/Creator/Patron ha portfolio con:

#### **EGI Posseduti**
- Badge "POSSEDUTO"
- Evidenza piena
- Storia dell'attivazione

#### **EGI Persi**
- Visualizzazione opaca
- Badge "NON POSSEDUTO"
- Storia partecipazione

### **Metriche Visibili**
- Numero attivazioni totali
- Valore portfolio
- Ranking nelle prenotazioni attive
- Impact EPP generato

---

## 🛡️ PRIVACY E GDPR

### **Principi**

1. **Consenso Esplicito**: Commissioner accetta visibilità permanente
2. **Privacy by Default**: Collector/Creator/Patron anonimi
3. **Immutabilità Blockchain**: comunicata chiaramente
4. **Diritto Modifica**: solo pre-mint via admin

### **Gestione UserType**

- **Scelto alla registrazione**
- **Modificabile su richiesta** (admin approval)
- **Effetto retroattivo** su prenotazioni non minate
- **Immutabile post-mint**

### **Dati Pubblici**

#### **Per Commissioner**
- Nome completo
- Avatar/foto (opzionale)
- Storia attivazioni
- Portfolio pubblico

#### **Per Altri**
- Solo wallet address
- Nessun dato personale
- Storia anonimizzata

---

## 💻 IMPLEMENTAZIONE TECNICA

### **Stack Tecnologico**

#### **Backend**
- Laravel 11
- MySQL con migration additive
- UEM per error handling
- ULM per logging strutturato
- Algorand SDK

#### **Frontend**
- TypeScript
- Blade Components
- Livewire per real-time
- TailwindCSS

#### **Infrastructure**
- Docker containers
- Redis per caching
- Queue workers per notifiche
- WebSocket per updates live

### **Pattern Architetturali**

1. **Service Layer**: ReservationService, NotificationService
2. **Repository Pattern**: per data access
3. **Observer Pattern**: per eventi e notifiche
4. **Factory Pattern**: per creazione oggetti complessi
5. **Strategy Pattern**: per diversi tipi di aste

### **Error Handling (UEM)**

```php
$this->errorManager->handle('ERROR_CODE', [
    'context' => $data
], $exception);
```

### **Logging (ULM)**

```php
$this->logger->info('[MODULE] Operation', [
    'user_id' => $userId,
    'data' => $relevantData
]);
```

---

## 📈 METRICHE E KPI

### **Fase Prenotazioni**
- Conversion rate visitor → prenotazione
- Average bid amount
- Prenotazioni per EGI
- Ranking competition index

### **Fase Attivazione**
- Prenotazioni → mint conversion
- Tempo medio conferma
- Cascata depth (quanti rifiuti)
- Revenue per creator

### **Mercato Secondario**
- Volume transazioni
- Price appreciation
- Velocity (frequenza trading)
- Liquidity metrics

---

## 🚀 ROADMAP EVOLUTIVA

### **Q3 2025 - Prenotazioni**
- ✅ Sistema ranking
- ✅ Notifiche
- ✅ Portfolio base
- ⏳ Testing pubblico

### **Q4 2025 - Attivazione**
- Countdown system
- Payment integration
- Mint automation
- Certificate generation

### **Q1 2026 - Mercato Secondario**
- Auction system
- Smart contracts
- Royalty distribution
- Advanced analytics

### **Q2 2026 - Ecosystem**
- Mobile app
- API pubbliche
- Partner integration
- DAO governance

---

## 🔒 SICUREZZA E COMPLIANCE

### **Security Measures**
- 2FA obbligatorio per Commissioner
- Rate limiting su offerte
- Captcha per anti-bot
- Audit trail completo

### **Compliance**
- GDPR compliant
- KYC per importi elevati
- Anti-money laundering
- Tax reporting ready

### **Business Continuity**
- Backup incrementali
- Disaster recovery plan
- Failover automatico
- Data redundancy

---

## 📝 GLOSSARIO

- **Attivatore**: Chi acquista e "attiva" un EGI non ancora mintato
- **Commissioner**: Attivatore con visibilità pubblica
- **Collector**: Attivatore anonimo
- **EPP**: Environment Protection Project
- **Ranking**: Classifica delle prenotazioni
- **Cascata**: Sistema di conferma a scorrimento
- **Mint**: Creazione del token su blockchain
- **ASA**: Algorand Standard Asset

---

*Documento Sistema Completo FlorenceEGI v2.0*
*Data: 15 Agosto 2025*
*Status: In Implementazione*