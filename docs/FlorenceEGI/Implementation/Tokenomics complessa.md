# Tokenomics della Piattaforma Florence EGI

**Descrizione Generale**: La tokenomics di Florence EGI è progettata per unire la praticità delle transazioni in moneta FIAT con la trasparenza e la sicurezza offerte dalla tecnologia blockchain. La piattaforma supporta i creator, gli Environmental Protection Programs (EPP) e altri attori chiave attraverso una struttura equa di distribuzione dei profitti e delle royalties. Questo sistema ibrido innovativo assicura una gestione efficiente delle transazioni, un impatto sociale positivo e un'esperienza utente semplificata.

---

## **1. Definizione di EGI e Struttura dell'Item**

### **1.1 [EGI](Definizione%20e%20spiegazioni%20di%20EGI.md) (Ecological Goods Invent)**

- **Descrizione**: Un EGI è un bene digitale che può essere un'immagine, un brano musicale inedito (non coperto da SIAE), un eBook o un video. Ogni EGI deve sempre avere una **consistenza fisica oltre che digitale**, distinguendosi dagli NFT tradizionali e "effimeri".

- **Componenti Essenziali di un EGI**:
  - **Elemento Digitale**: L'asset digitale principale, che può essere un'immagine, un brano musicale, un eBook o un video.
  - **Utility**: Se l'EGI non rappresenta un bene fisico, deve avere un'utility associata (es. servizi, abbonamenti, omaggi) per garantire una componente fisica o funzionale.
  - **EPP (Environmental Protection Program)**: Ogni EGI è associato a un EPP, contribuendo a una causa ambientale specifica.

- **Metadata dell'EGI**:
  - Nome del file, autore, nome attribuito all'EGI, data di creazione, descrizione, prezzo.
  - **Traits (Tratti Distintivi)**: Proprietà o tag che caratterizzano l'EGI. Questi possono includere categorie, attributi specifici e valori unici.

- **Cover e Rappresentazione Visiva**:
  - Se l'EGI è un libro, un brano musicale o un video, avrà una **cover** sotto forma di immagine che lo rappresenta visivamente.

### **1.2 Item**

- **Descrizione**: L'Item è l'entità tecnica all'interno della piattaforma che raggruppa tutti gli elementi dell'EGI. È l'involucro che contiene:
  - L'EGI stesso (elemento digitale principale).
  - La cover dell'EGI (se applicabile).
  - I metadata e i traits associati.
  - L'utility (se l'EGI non rappresenta un bene fisico).

- **Funzionalità dell'Item**:
  - Può essere coinvolto in una **Drop** (evento di lancio o promozione).
  - Può essere trasferito internamente tra le collection dell'utente o inviato ad altri utenti utilizzando l'email come riferimento.

### **1.3 Minting e Registrazione**

- **Processo di Minting**:
  - L'EGI viene mintato dalla piattaforma al momento del deploy dello smart contract della collection.
  - Solo la URL dell'elemento digitale principale dell'EGI viene registrata come token sulla blockchain.
  - La cover, l'utility e altri elementi vengono salvati come metadata su server IPFS.

- **Storage su IPFS**:
  - I file digitali (elementi dell'EGI, cover, utility) e i metadata sono salvati su IPFS per garantire decentralizzazione e sicurezza.

---

## **2. Struttura della Piattaforma**

La piattaforma EGIFlorence è composta da tre siti principali:

1. **Office**: Area dedicata ai creator per la creazione e gestione delle collection e degli EGI. Include moduli come Make Collection, Make Item, Make Traits, Make Team, Make Wallet, Make EPP, Make Utilities e Make Drop.

2. **Marketplace**: Il luogo dove gli utenti possono esplorare le gallerie dei creator, acquistare EGI e interagire con la community. Le transazioni possono avvenire in Equilibrium (valuta interna) o in moneta FIAT.

3. **Istituzionale**: Contiene informazioni sul progetto, il white paper, la roadmap, le policy e le iniziative legate agli EPP.

---

## **3. Processo di Creazione di una [collection](Concetti%20di%20Base%20per%20la%20gestione%20delle%20collection.md)**

### **3.1 Creazione della Collection**

- **Passaggi**:
  1. **Creazione della Collection**: Il creator attribuisce un nome e inserisce le informazioni di base.
  2. **Assegnazione dell'EPP**: Seleziona un EPP da associare alla collection (obbligatorio).
  3. **Definizione dell'Utility**: Se gli EGI non rappresentano beni fisici, viene assegnata un'utility alla collection.
  4. **Impostazione del Prezzo Base**: Stabilisce il floor price per gli EGI della collection.

- **Ereditarietà**: Gli EGI (Items) creati all'interno della collection ereditano automaticamente l'EPP, l'utility e il prezzo base definiti.

### **3.2 Creazione degli Items (EGI)**

- **Upload degli Elementi Digitali**: Il creator carica gli elementi digitali che costituiscono gli EGI utilizzando l'Ultra Download Manager.

- **Assegnazione di Traits**:
  - Il creator può attribuire traits (tratti distintivi) agli EGI per caratterizzarli ulteriormente.
  - I traits possono essere selezionati da un archivio globale o creati ex novo.

- **Configurazione dell'Item**:
  - Definizione dei metadata specifici.
  - Associazione della cover (se l'EGI è un libro, brano musicale o video).
  - Impostazione di eventuali utility specifiche.

---

## **4. Distribuzione dei Profitti**

### **4.1 Prima Vendita (Mint)**

Quando un EGI viene venduto per la prima volta, la distribuzione dei profitti è la seguente:

- **Natan (Piattaforma)**: **15%**
- **Mediator**: **10%**
- **Creator**: **10%**
- **Owner**: **40%**
- **EPP**: **25%**

**Note**:

- **Owner**: Inizialmente coincide con il Creator fino a quando l'EGI Asset non viene venduto.
- **Mediator**: Se non specificato, la quota destinata al Mediator viene assegnata a Natan.
- **EPP**: Il 25% è destinato a programmi ambientali selezionati dal creator.

### **4.2 Vendite Secondarie (Rebind)**

Per le rivendite degli EGI sul mercato secondario, viene applicata una commissione fissa del **5%**, distribuita come segue:

- **Collection Owner**: **3.0%**
- **Creator**: **1.0%** (1.5% per Smart Contract Goodwill)
- **EPP**: **0.5%** (non applicabile per Smart Contract Goodwill)
- **Mediator**: **0.3%** (se non specificato, assegnato a Natan)
- **Natan**: **0.2%**

**Note**:

- **Collection Owner**: Riceve una royalty per ogni rivendita dell'EGI.
- **Le quote sono fisse** e non modificabili dai creator per garantire uniformità e trasparenza.

---

## **5. Ruoli Chiave e Wallet Associati**

### **5.1 Natan**

- **Ruolo**: Rappresenta la piattaforma EGIFlorence e riceve una percentuale fissa per sostenere i costi operativi e di sviluppo.
- **Quota**:
  - **15%** sulla prima vendita.
  - **0.2%** sulle vendite secondarie.

### **5.2 Mediator**

- **Ruolo**: Responsabile delle vendite e della promozione delle collection. Può essere un gallerista o un membro dell'associazione Frangette.
- **Quota**:
  - **10%** sulla prima vendita.
  - **0.3%** sulle vendite secondarie.
- **Gestione**:
  - Se non specificato, la quota del Mediator viene assegnata a Natan.
  - Il Mediator è gestito da Fabio Cherici (Superadmin) che decide quale wallet assegnare.

### **5.3 Creator**

- **Ruolo**: L'artista o il team che ha creato l'EGI o la collection.
- **Quota**:
  - **10%** sulla prima vendita.
  - **1.0%** sulle vendite secondarie.
- **Diritti**: Continua a ricevere royalties per ogni rivendita dell'EGI.

### **5.4 Owner**

- **Ruolo**: Proprietario attuale dell'EGI Asset o dell'EGI singolo.
- **Quota**:
  - **40%** sulla prima vendita.
  - **3.0%** sulle vendite secondarie.
- **Note**:
  - L'Owner iniziale è il Creator. Quando l'EGI Asset viene venduto, l'Owner cambia e inizia a percepire le quote spettanti.

### **5.5 EPP (Environmental Protection Program)**

- **Ruolo**: Programmi o enti benefici selezionati dal Creator che ricevono una percentuale delle vendite per sostenere cause ambientali.
- **Quota**:
  - **25%** sulla prima vendita.
  - **0.5%** sulle vendite secondarie.

---

## **6. Smart Contract e Tipologie**

### **6.1 Smart Contract Standard**

- **Applicazione**: Default per tutte le collection a meno che non sia specificato diversamente.
- **Distribuzione**:

  - **Natan**: **15%**
  - **Mediator**: **10%**
  - **Creator**: **50%**
  - **EPP**: **25%**

### **6.2 Smart Contract Goodwill**

- **Applicazione**: Per enti benefici o progetti sociali con user type "goodwill".
- **Distribuzione**:

  - **Natan**: **15%**
  - **Mediator**: **10%**
  - **Creator**: **75%**
  - **EPP**: **0%** (non applicabile)

### **6.3 Smart Contract Team**

- **Applicazione**: Quando la collection è creata da un team di creator.
- **Distribuzione**:

  - **Natan**: **15%**
  - **Mediator**: **10%**
  - **Creator**: **50%** (suddiviso tra i membri del team)
  - **EPP**: **25%**

---

## **7. Gestione delle Transazioni**

### **7.1 Transazioni in FIAT**

- **Processo di Pagamento**:

  1. **Acquisto**: L'utente acquista un EGI pagando in moneta FIAT tramite un gateway di pagamento integrato (es. Stripe, PayPal).
  2. **Ricezione dei Fondi**: Il pagamento viene ricevuto sul conto bancario centralizzato della piattaforma.
  3. **Sub-Payout Automatizzato**: Il sistema distribuisce automaticamente le quote ai wallet digitali interni dei membri del team secondo le percentuali stabilite.

- **Wallet Digitali Interni**:

  - Ogni membro del team ha un wallet digitale interno sulla piattaforma.
  - I fondi accumulati possono essere prelevati dai membri verso i propri conti bancari o metodi di pagamento preferiti.

### **7.2 Transazioni in Equilibrium**

- **Descrizione**: Equilibrium è la valuta interna della piattaforma utilizzata per transazioni interne e per rappresentare l'impegno sociale degli utenti.

- **Gestione**:

  - Le transazioni in Equilibrium sono tracciate nel database centralizzato e registrate su blockchain per garantire sicurezza e trasparenza.
  - Gli Equilibrium accumulati possono essere utilizzati per acquisti interni o, se previsto, convertiti in moneta FIAT.

### **7.3 Registrazione su Blockchain**

- **Blockchain Utilizzata**: Algorand.

- **Dati Registrati**:

  - Transazioni di vendita e acquisto.
  - Distribuzione delle royalties.
  - Cambi di proprietà degli EGI e degli EGI Asset.

- **Vantaggi**:

  - Garantisce trasparenza e tracciabilità.
  - Fornisce un backup decentralizzato delle informazioni chiave.

---

## **8. Gestione dei Wallet e Prelievi**

- **Wallet Digitali Interni**:

  - Ogni utente dispone di un wallet interno per accumulare le proprie quote.
  - I wallet mostrano saldi separati per FIAT ed Equilibrium.

- **Prelievo dei Fondi**:

  - Gli utenti possono richiedere il prelievo dei fondi accumulati.
  - I prelievi vengono processati dalla piattaforma verso il metodo di pagamento scelto dall'utente (conto bancario, PayPal, ecc.).

- **Sicurezza**:

  - La piattaforma utilizza sistemi di sicurezza avanzati per proteggere i fondi degli utenti.
  - Le transazioni sono monitorate e registrate per prevenire frodi e garantire conformità legale.

---

## **9. Ruolo dell'Associazione Frangette e Natan**

### **9.1 Associazione Frangette**

- **Descrizione**: Organizzazione no-profit che funge da ente certificatore e promotore di iniziative ambientali sulla piattaforma.

- **Obiettivi**:

  - Sostenere progetti ambientali attraverso gli EPP.
  - Promuovere la sostenibilità e l'impatto sociale positivo.

### **9.2 Natan**

- **Descrizione**: Personaggio simbolico e alter-ego dell'associazione Frangette, rappresenta l'impegno della piattaforma verso il miglioramento del pianeta.

- **Narrativa**:

  - Natan è un alieno il cui scopo è fungere da "aggiustapianeti".
  - Si ritrova sulla Terra e, per riparare la sua nave spaziale, deve accumulare Equilibrium, contribuendo così a salvare il pianeta.
  - La storia di Natan evolve con la piattaforma e viene narrata attraverso capitoli e EGI speciali.

---

## **10. Environmental Protection Programs (EPP)**

- **Selezione degli EPP**:

  - I creator devono selezionare un EPP al momento della creazione della collection.
  - Gli EPP sono organizzazioni o progetti che operano in uno dei tre settori:

    1. **Aquatic Plastic Removal**: Rimozione della plastica dai corpi idrici.
    2. **Appropriate Restoration Forestry**: Riforestazione sostenibile.
    3. **Bee Population Enhancement**: Aumento della popolazione delle api.

- **Contributo agli EPP**:

  - Ricevono il **25%** delle vendite iniziali e **0.5%** delle vendite secondarie.
  - Fondi destinati a sostenere progetti specifici e misurabili.

---

## **11. Governance e Community Engagement**

- **Votazioni e Decisioni Comunitarie**:

  - Gli utenti possono partecipare a votazioni riguardanti la selezione di nuovi EPP, modifiche alle policy e altre decisioni chiave.
  - Il sistema di governance è gestito tramite smart contract su blockchain per garantire trasparenza.

- **Incentivi per la Partecipazione**:

  - Gli utenti attivi nella community possono ricevere ricompense in Equilibrium.
  - Programmi di riconoscimento per i creator e gli utenti più impegnati.

---

## **12. Compliance Legale e Fiscale**

- **Gestione dei Fondi**:

  - La piattaforma non funge da intermediario finanziario per le transazioni principali.
  - L'utilizzo di sub-payout automatizzati garantisce conformità alle normative vigenti.

- **Privacy e Sicurezza**:

  - I dati degli utenti sono protetti in conformità alle leggi sulla privacy.
  - Misure di sicurezza avanzate per proteggere le informazioni e i fondi degli utenti.

---

## **13. Glossario dei Termini**

- **EGI (Ecological Goods Invent)**: Un bene digitale che unisce un elemento digitale, un EPP e un'utility, garantendo una componente fisica o funzionale oltre al digitale.

- **Item**: L'entità tecnica che raggruppa tutti gli elementi dell'EGI all'interno della piattaforma.

- **EGI Asset**: Token che rappresenta un'intera collection, consentendo la vendita o il trasferimento della collection come singola entità.

- **Equilibrium**: Valuta interna della piattaforma, rappresenta l'impegno sociale degli utenti e viene utilizzata per transazioni interne.

- **Rebind**: Rivendita di un EGI sul mercato secondario.

- **Mediator**: Figura che assiste il creator nella vendita e promozione delle collection.

- **Natan**: Simbolo della piattaforma e alter-ego dell'associazione Frangette.

- **EPP (Environmental Protection Program)**: Programmi ambientali sostenuti dalla piattaforma.

- **Traits (Tratti Distintivi)**: Proprietà o tag che caratterizzano gli EGI.

- **Sub-Payout Automatizzato**: Sistema automatizzato per la distribuzione delle quote ai membri del team.

- **Blockchain**: Tecnologia utilizzata per la registrazione trasparente delle transazioni (Algorand).

---

## **14. Conclusioni**

La tokenomics di EGIFlorence è stata progettata per creare un ecosistema sostenibile, trasparente e vantaggioso per tutti gli attori coinvolti. Integrando la corretta definizione di EGI come bene digitale con una componente fisica o funzionale, la piattaforma si distingue dagli NFT tradizionali, offrendo valore tangibile agli utenti.

**Vantaggi Chiave**:

- **Per i Creator**: Struttura di royalties chiara e garantita, strumenti per gestire e promuovere le proprie opere, e la possibilità di creare EGI con valore reale.

- **Per gli Utenti**: Facilità di acquisto, partecipazione a iniziative ambientali, coinvolgimento nella governance, e possesso di EGI con utilità tangibile.

- **Per gli EPP**: Supporto finanziario diretto, visibilità attraverso la piattaforma.

- **Per la Piattaforma**: Sostenibilità economica, conformità legale, posizionamento innovativo sul mercato, differenziandosi attraverso EGI che combinano digitale e fisico.

EGIFlorence si propone come una soluzione all'avanguardia nel mondo degli asset digitali, combinando arte, tecnologia e responsabilità sociale in un unico luogo, offrendo agli utenti non solo beni digitali, ma esperienze e valori tangibili.

---

**Nota**: Questa tokenomics è stata aggiornata per integrare la corretta definizione di EGI come fornita. Tutte le sezioni sono state adeguate per riflettere l'importanza dell'utility e della componente fisica negli EGI, garantendo coerenza e applicabilità pratica.