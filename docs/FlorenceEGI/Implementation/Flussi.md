
==L'utente arriva su florenceegi, atterra sulla home page, qui c'è una navbar, un carousel a scheda singola che mostra EGI random di ogni collection, e sotto di esso un carousel a schede multiple di tutte le collection.==
### L'utente può cliccare su uno dei menu della navbar.

La pagina si basa sul layout guest.blade.php con vista home.blade.php
==Quando un utente si connette o logga rimane in questa stessa pagina, ma con menu e flussi che variano. Vedi sotto.==

Nel footer della pagina di layout ci deve essere un link per accedere al pannello di controllo dei cookie e del pannello privacy dove si possono trovare tutti i documenti e gli strumenti necessari quali cancellazione dell'account (non lo si può fare in modo diretto, occorre inviare una comunicazione, alla cancellazione dell'account sono associate molte cose ed è una cosa da ponderare bene, comunque è sempre un soft delete e c'è una procedura di recupero.) cancellazione di specifici dati, possibiltà di aver e un report di tutti i dati personali che la piattaforma tratta e ogni altra cosa richiesta dal GDPR
## Menu della navbar

### ==Logo (/home==
### ==Home (/Home)==	
## ==Modale di connessione del wallet==
==Non è lo strumento nativo di Algorad, ma un semplice form che consente di incollare una stringa (che viene controllata e validata come compatibile con gli address Algorand)==
==Si apre solo se: l'utente non è connesso e clicca su Create EGI o Create collection o Connect Wallet.==
## ==Create EGI==
==Apre la modale di upload delle immagini. la quale consente di fare upload e scrivere metadata. Nel caso faccia upload di più immagini, i metadata verranno scritti nel db associati a ogni immagine. Se non mette il nome dell'EGI, il sistema ne creai in automatico.==
==Gli EGI sono collegati alla current_collection.==
## ==Create collection==
==Apre la vista CRUD in modalità C per le collection.==
==(Nota e ricorda che quando lo user si connette per la prima volta o si registra come user effettivo, viene creata una collection che diventa la sua collection di default e in quel momento viene anche segnata come current_collection.)==
## Collections
==Apre la vista (/home/collections)==
Se *l'utente non è connesso ne loggato* vedrà tutte le collection della piattaforma, se è connesso e/o loggato, vedrà solo le sue collection.  
Le collection sono disposte in una griglia con un formato simile a questo
![](Pasted%20image%2020250508111137.png)

Qui ci sono anche gli strumenti per filtrare le collection. Ovviamente noi avremo i nostri dati statistici.

## ==Collection list (Questo menu è visibile solo per gli utenti loggati.)==
==menu con un submenu con l'elenco di tutte le sue collection, e quelle in cui collabora. La collection su cui fa click oltre ad aprirsi: (home/collection/{collection_id}) diventa anche la current_collection.)==
==Se l'utente si logga, il menu Collection diventa invisibile e viene sostituito da questo menu.==
- ==Collection_1==
 	==- Collection_2==
 	==- ...==
==Lo user può essere un collaboratore in una collection NON creata da lui stesso, il suo ruolo corrisponderà a quello che il creator di quella collection gli ha assegnato==
==Lo user vede anche le collection in cui egli è collaboratore,== 
 	==- External collections==
	 	==- Creator Giulio  - Collection_96==
	 	==- Creator Marco - Collection_31==
## Wallet connect (button)
==Cosa succede quando lo user connette il proprio wallet. Il sistema cerca un valore valido su user->wallet, se esiste effettua la registrazione della sessione, scrive il cookie in chiaro del wallet; a quel punto lo user è connesso ma non loggato. Potrà fare prenotazioni deboli.==
==Se l'address non viene trovato il sistema crea un nuovo record nella tabella users, viene generata e assegnata una password e un indirizzo email random.== 
Viene creata la collection di default per l'utente i tre wallet: creator, epp, natan, che vengono collegati alla collection, la quale diventa anche la current_collection inserendo in user->current_collection_id l'id della collection appena creato. 
==Sul menu compare lo short address come caption del button su cui prima c'era scritto Wallet Connect, ad esempio: 0xE24378.==
==Per connettere il wallet deve solo incollare l'address Algorand nella modale che si apre quando si clicca su: Connect Wallet, Create EGI, Create Collection (a patto che non sia già connesso).== 
*Non c'è neanche una verifica sulla esistenza del vero wallet su Algornad, sarà preoccupazione dello user assicurarsi che sia corretto quando vorrà fare delle transazioni reali. Comunque quando faremo le transazioni reali sarà richiesta una connessione con lo strumento wallet di Algorand, quindi sicuramente a quel punto l'address sarà corretto. La sola connessione consente di fare prenotazioni deboli.*
==Il bottone cambia la sua caption quanto l'utete si connette. La caption cambia da Wallet connect a i primi otto caratteri del wallet address.==
==Il bottone ha un submenu a discesa,  in esso si trovano: copy address, dashboard, disconnected==
## Login
==Quando un utente registrato fa il login, oltre a loggarsi connette anche il wallet. Lo fa il sistema in automatico, recuperando il wallet dell'utente da user->wallet.==
## Log-out
==Il logout avviene quando l'utente clicca su disconnect nel sub menu di Wallet connect.== 
### Register
==Per registrarsi occorre compilare il normale form di registrazione con i dati base dello user,== che comprende anche l'indirizzo del wallet. Una volta registrato lo user potrà effettuare il login, una volta loggato potrà fare prenotazioni forti.
## Collection badge (visibile solo per utenti loggati)
==Sulla navbar compare un evidente badge del nome della collection. Da quel momento in poi qualsiasi cosa farà interagirà attraverso quella collection. Ad esempio se crea un nuovo EGI, si connetterà a quella collection.== 
==Se clicca sul badge entra nella vista CRUD della collection. (collections/{id}/edit)==
## Notifiche
Se lo user è loggato può ricevere notifiche.
Quando c'è una o più notifiche che lo user deve leggere compare un badge sulla navbar: Read You Notification. Il badge rimane fino a quando tutte le notifiche no saranno archiviate. Sul badge viene scritto anche il numero delle notifiche che rimangono da gestire.
Cliccare sul badge permette di entrare nella gestione delle notifiche dentro la dashboard (/dashboard)
## ==Ruoli e permessi (Spatie. Contesto collection)==

==Lo user_id = 1 è superadmin==
==Lo user_id = 2 è EPP (Solo per MVP)==

==Da user_id = 3 in poi sono tutti gli altri utenti con id generati in automatico.==
==Gli utenti quando si connettono o registrano vengono registrati con il ruolo di creator ma solo nel contesto collection, non nel contesto user.==
==Anche il creator della collection viene aggiunto nella tabella collection_users, con i collection_users->role='creator' ed è questo che determina i suoi permessi in quella collection.==
==Nella piattaforma non c'è un contesto user. quando un utente è registrato se si connette o si logga, ha sempre una current_collection e i suoi permessi sono quelli associati al role in quella collection.==
==Quando si logga è sempre la collection di default (la prima che è stata creata per lui), ad essere la currente_collection, se l'utente rende corrente una collection diversa, saranno i permessi legati al ruolo che ha in quella collection, a determinare cosa potrà fare in quella collection.== 
==Questo succede perché qualunque utente registrato alla piattaforma può essere invitato a collaborare a un collection non sua, in quel caso viene aggiunto nella tabella collection_user, con il role che il creator che lo ha invitato ha determinato per lui.==
## Carousel
EGI Carousel a scheda unica.
	==In automatico scorrono uno per volta EGI presi a caso da ogni collection.== Su ogni EGI c'è la descrizione completa. Cliccandoci si arriva alla vista del EGI (egis/{id})

Collection carousel
	==Mostra in un unico carousel alcune collection a caso==. (in futuro ci saranno dei criteri di scelta in base alla quantità di [[equilibrium]] prodotta). Cliccando su una di queste collection si apre quella collection
### sub menu Dashboard (/dashboard) (del bottone Wallet connect dopo la connessone o il login) 
Vedi [[dashboard]]

Si accede all'area privata basata su components/layouts/app.blade.php che comprende una navbar e una sidebar sul lato destro
La sidebar è contestuale, si modifica a seconda della URL
Di default si apre appunto su /dashboard e la sidebar mostra differenti menu a seconda di user_id: se user_id=1 mostra anche tutti i menu di gestione dell'intera applicazione) se user_id=2 mostra i menu per EPP.
Per tutti gli altri id invece.
	Collections (group)

## ==Badge==
==anche se non ha la tipica forma della voce di menu, permette di entrare direttamente nella gestione CRUD della collection.==

#FlorenceEGI #EGI #marketplace #whitepaper #EPP #tokenomics #creator #Frangette #MVP #roadmap #traits #mint #rebind 
