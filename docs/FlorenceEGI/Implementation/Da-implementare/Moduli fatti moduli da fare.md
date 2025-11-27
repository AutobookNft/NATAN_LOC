MVP locale.
Modulo iscrizione strong e weak, fatto.
Modulo FegiAuth per gestione account weak e strong, fatto.
Modulo GDPR completo, con gestione base dei dati utente, fatto.
Modulo gestione IVA localizzato, fatto.
Modulo traduzioni it, pt, fr, es, en, de, fatto
Modulo creazione collection, fatto.
Modulo upload file immagini e metadata con UUM, (creazione degli EGI), fatto
Modulo creazione e gestione dei team con tanto di gestione degli wallet, fatto
Modulo gestion e notifiche per inviti e modifiche ai wallet dei memebri del team, fatto.
Modulo creazione delle bio singole e capitoli con gestione di immagini mediante Spatie media upload, fatto.
Modulo gestione immagini di profilo utente, mediante Spatie Media Upload, fatto.
Modulo Assistente e maggiordomo Natan, che permette l'accesso guidato alle varie aree con spiegazioni specifiche, fatto ma con l'accesso alle aree in under construction.
Modulo prenotazioni con emisisone di certificato e possibilità di like su db, di collection e EGI, fatto
Modulo CRUD su EGI, fatto.
Modulo Dashboard con accesso ai dati dell'utente, CRUD su le sue collection, GDPR, 
Guest home con link a collection creator, arketips, ed EPP, fatto.
Creator home, con link alle collection del creator, al suo portolio, biografia, community, impatto, fatto. Solo biografie si apre realmente, le altre sezioni sono under constuction.
UEM, fatto e funziona con test effettuati.
ULM, fatto e funziona con test effettuati.
UTM, fatto e funziona con test effettuati.
UUM, fatto e funziona con test effettuati.
Deploy staging. FATTO
UX:
	Registration: con consensi completi GDPR, possibilità di scegliere con quale ruolo registrarsi: Creator, Collector, Mecenate; EPP; Company, Trader Pro
	Dashboard: accesso a tutto il vasto modulo GDPR, Dati personali, Documentazione
	Guest home: accessibile a tutti ma dinamica, se user si connette weak può creare EGI rimanendo su quella pagina, si apre la modale UUM. Se utente si logga rimane sempre sulla medesima pagina ma la nav-links si modifica mettendo a disposizione menu adeguati al tipo di utente loggato: creator, collector, mecenate, EPP, company.
	Creator home
	Collection home
	Collector home
	tutte con index; home e show
	per Creator e Collector ci sono sotto menu: collection; portfolio; biografia, community
I layout includono la vista header.blade.php la quale include a sua volta la vista  nav-links.blade.php Queste due usano Spatie permission per visualizzare i menu in base al ruolo dell'utente loggato
	 Egi: CRUD, si apre la vista comune a tutti, ma se user ne è il creator allora visualizza anche la sezione CRUD. Da qui è possibile lasciare like e prenotare. La vista mostra anche la cronologia delle prenotazioni.
	 EGI-Card (blade component) è un concentrato di funzioni visibili a seconda del contesto in cui la card viene utilizzata e da chi. Permette di effettuare la prenotazione, mostra o nasconde il pulsante prenotazione a seconda del contesto, mostra un pulsante cronologia che apre un pupop con la cronologia delle prenotazioni

Cosa rimane da fare lato backend web2.0

Modulo +Segui
Modulo Articoli sui EGI (solo da parte di mecenati e collezionisti)
Modulo fatturazione.
Modulo reportistica
Modulo utilities
Modulo traits
Modulo EPP (modulo grande...)
Modulo documentazione lato utente
Modulo creator portfolio. (in progress)
Modulo metriche di impatto EPP (in progress)
Ampliare la gestione delle notifiche fino a farla diventare un sistema di comunicazioni fra utenti interno, prima bacth e poi push...
Modulo Social:  (solo per chi ha danto il consenso)
	bacheca centrale di comunicazioni fra strong user
	comunicazioni push fra utenti
Debugging generale
Modulo metriche pubbliche
Tutta la parte del marketplace su blockchain Algorand: occorre registrate domino, https, progettare microservice di collegamento fra Laravel e Algornad (già sviluppato in una forma per ora semplice, per la gestione del minimarketplace dei Padri Fondatori)
Tutti i moduli di cui sopra non facevano parte di MVP,  fanno parte della fase tre, ovvero quella che segue la messa in staging della piattaforma.

Credo di aver detto tutto, forse ho tralasciato qualcosa ma queste sono le cose principali. dimmi cosa ne pensi per ora.