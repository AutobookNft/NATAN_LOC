# Executive Summary – Gestione delle Transazioni FlorenceEGI

## 1. Principio Guido

FlorenceEGI opera **esclusivamente come intermediario tecnico** per la gestione delle transazioni tra venditori e acquirenti di EGI (digitali o con componente fisica). 

### 🔑 **Punti Chiave**
- La piattaforma **NON custodisce fondi**
- I pagamenti in denaro avvengono sempre **direttamente tra le parti** tramite provider di pagamento esterni (es. Stripe Connect, bonifico)
- L'on-chain serve solo a **trasferire il token**

## 2. Flusso Generale della Transazione

### 2.1 Ordine e Pagamento FIAT

**Processo:**
1. L'acquirente effettua il pagamento tramite carta, bonifico o altro metodo supportato
2. Il denaro viene trasferito automaticamente in split:
   - **Al venditore** (es. 80% dell'importo)
   - **All'EPP** (es. 20%) se previsto
3. La piattaforma può ricevere solo **commissioni di servizio** dal PSP (*application fee*)

### 2.2 Conferma Pagamento

**Trigger per l'avvio della transazione on-chain:**
- Ricezione conferma da provider di pagamento (webhook)
- Verifica manuale per bonifico
- Avvio del **trasferimento del token** su Algorand

### 2.3 Transazione On-Chain

**Operazioni blockchain:**
- Mint o trasferimento del token EGI all'acquirente
- Pagamento della sola fee minima di rete
- **⚠️ Nessun importo di vendita gestito su blockchain**

## 3. Gestione Beni Fisici

### 3.1 Processo per EGI con Componente Fisica

1. **Spedizione** avviata dal venditore
2. **Escrow logico** del pagamento:
   - Incassato ma non trasferito al venditore
   - Bloccato fino alla conferma di consegna
3. **Conferma di ricezione**:
   - L'acquirente scansiona il QR presente nel pacco, OPPURE
   - Il corriere conferma la consegna
4. **Sblocco finale**:
   - Trasferimento dei fondi al venditore (se escrow)
   - Trasferimento del token o conferma di possesso

## 4. Fatturazione

### 4.1 Principio di Non Emissione

**La piattaforma NON emette fatture per conto proprio**

### 4.2 Supporto Integrato

**Servizi di supporto:**
- Generazione automatica della fattura da parte del venditore
- Integrazione API con il sistema di fatturazione del venditore
- Eventuale ricevuta/fattura separata da parte dell'EPP per la sua quota

### 4.3 Ricezione Documenti

**L'acquirente riceve sempre i documenti fiscali direttamente dal soggetto che riceve il pagamento**

## 5. Conferma Finale e Registri

### 5.1 Tracciamento a Tre Livelli

Ogni transazione è tracciata in:

#### 📊 **1. Registro FIAT**
- Dati del pagamento dal PSP
- Split automatici
- Commissioni di servizio

#### ⛓️ **2. Registro On-Chain**
- ID della transazione di mint/transfer su Algorand
- Proof of ownership
- Cronologia trasferimenti

#### 📦 **3. Registro Logistico**
- Conferma spedizione/consegna per beni fisici
- Tracking del corriere
- Scansione QR di conferma

### 5.2 Collegamento dei Registri

**Tutti i registri sono collegati per:**
- Audit completo delle transazioni
- Supporto post-vendita efficace
- Compliance e tracciabilità

## 📋 Sintesi Operativa

| **Aspetto** | **Modalità** | **Responsabile** |
|-------------|--------------|------------------|
| **Pagamenti** | Solo off-chain, diretti a venditore ed EPP | PSP Esterno |
| **Blockchain** | Solo trasferimento token e tracciamento proprietà | FlorenceEGI |
| **Beni Fisici** | Escrow logico sbloccato a consegna confermata | Venditore + Corriere |
| **Fatturazione** | Emessa dai venditori/EPP con supporto integrato | Venditore/EPP |
| **Trasparenza** | Tracciamento unificato di pagamento, token e consegna | FlorenceEGI |

## 🎯 Vantaggi del Sistema

### ✅ **Per la Piattaforma**
- Zero rischio di custodia fondi
- Compliance semplificata
- Focus sulla tecnologia blockchain

### ✅ **Per i Venditori**
- Pagamenti diretti
- Controllo sulla fatturazione
- Gestione autonoma dei propri flussi

### ✅ **Per gli Acquirenti**
- Sicurezza dell'escrow logico
- Tracciabilità completa
- Documenti fiscali corretti

### ✅ **Per l'Ecosistema**
- Trasparenza totale
- Audit trail completo
- Scalabilità del sistema

---

*Documento preparato per il progetto FlorenceEGI - Seconda Fase*  
*Data: Agosto 2025*