# Menu
## Open Collection. 
Se c'è una sola collection si apre direttamente quella, se ce ne sono più di una su apre un carousel che permette la scelta. Si apre il form CRUD in modalità Edit.
Qui si possono modificare i dati della collection: nome, tipo, numero massimo degli EGI della collection, il floor price da assegnare ad ogni nuovo EGI, la posizione relativa alle altre collection, la sua descrizione, un sito, e il bottone di pubblicazione.
	poi si può accedere
	- EGIs
		- Si apre un carousel degli EGI di quella collection, cliccando su un EGI si apre la finestra CRUD degli EGI.
	- Head image
		- Da qui può modificare le immagini banner, card e avatar
	- Collection members
		- Si vedono tutti i membri collegati alla collection con i loro ruoli
		- Tutti gli wallet dei membri con le loro quote di default ci saranno sempre gli wallet di 
			- Creator 70% - 4,3%
			- EPP 20% - 1%
			- Natan (la piattaforma stessa) 10% - 0,7%
		- Da qui si può
			- invitare nuovi membri a partecipare alla collection
			- modificare wallet (previo invio di notifica che il ricevente può accettare o rifiutare)
			- donare parti della propria quota al wallet di EPP
## New collection. Apre lo stesso form CRUD ma in modalità Create.

## Dashboard (group)
Notification
Biografy
Stat
# **Elenco Funzionalità da Gestire nella Dashboard**

#### **1. Statistiche**

- Conteggio delle collection create.
- Numero totale di membri nelle collection.
- Performance economica:
    - Royalties generate (Mint/Rebind).
    - Quote donate all’EPP.
- Storico delle transazioni.

#### **2. Notifiche**

- Notifiche pendenti e storiche.
- Sistema di filtri per tipologia (wallet, inviti, altro).
- Azioni dirette sulle notifiche (accetta/rifiuta).

#### **3. Dati Anagrafici**

- Modifica dei dati personali.
- Upload di immagine del profilo/avatar.

#### **4. Privacy e GDPR**

- Gestione di consensi per cookies e privacy policy.
- Visualizzazione dei dati personali raccolti (funzione "scarica i miei dati").
- Pulsante per richiedere la cancellazione dell’account (diritto all’oblio).

#### **5. Gestione delle Icone**

- Scelta e anteprima degli stili disponibili (es. Elegant, Classic, ecc.).
- Sistema per caricare nuovi set di icone in futuro.

---

### **Altri Elementi Potenziali**

#### **6. Gestione delle Collection**

- Accesso rapido alle collection create.
- Stato di ogni collection (bozza, pubblicata, in attesa di approvazioni).
- Azioni rapide (modifica, elimina, duplica).

#### **7. Wallet Management**

- Panoramica dei wallet collegati.
- Stato delle modifiche proposte/approvate.
- Grafico delle quote assegnate (per tipologia: Mint/Rebind).

#### **8. Donazioni e Titoli Onorifici**

- Storico delle donazioni effettuate.
- Visualizzazione del livello onorifico raggiunto.
- Proposte per il livello successivo (es. "donando X arriverai a Silver Donor").

#### **9. Accesso alle Risorse**

- Manuale utente o guida alla piattaforma.
- FAQ integrate.
- Modulo di contatto o sistema di supporto rapido (es. chat o ticketing).

#### **10. Configurazione dell’Account**

- Gestione della password e autenticazione a due fattori.
- Configurazione delle preferenze personali (es. lingua, notifiche email).

#### **11. Integrazioni Future**

- Collegamenti a social media o strumenti di promozione.
- Statistiche sugli accessi o vendite attraverso canali esterni.

---

### **Considerazioni sulla Struttura**

1. **Modularità**:
        - Ogni sezione della Dashboard (statistiche, notifiche, dati anagrafici) dovrebbe essere un modulo indipendente, facilmente aggiornabile o sostituibile.
2. **Scalabilità**:      
    - Implementare un sistema di routing interno per gestire i moduli in modo dinamico.
    - Utilizzare componenti Livewire per aggiornamenti in tempo reale.
1. **Esperienza Utente**:
    - Layout responsive per garantire accesso fluido su desktop e mobile.
    - Navigazione chiara con una sidebar o un menu a schede.
2. **Architettura Tecnica**:    
    - Strutturare la Dashboard come un insieme di microservizi frontend, ognuno con il proprio controller e componente dedicato.
    - Centralizzare le dipendenze comuni (es. IconProvider, UserDataProvider).

---

#dashboard #struttura_generale #ambiente_di_sviluppo #collections_user #makecollection #decline_proposal_modal


Link
[Decline Proposal Modal](Decline%20Proposal%20Modal.md)
[Sviluppo gestione wallet](Sviluppo%20gestione%20wallet.md)
[Concetti base make collection](Natan/docs/Sviluppo/Gestione_Tecnica/Backend/Moduli_applicazione/make_collection/#%20Concetti%20base%20make%20collection.md)
[Wallets](Wallets.md)
[Gestione_wallet](gestione_wallet.md)
[Decline Proposal Modal](Decline%20Proposal%20Modal.md)





