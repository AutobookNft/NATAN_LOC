---
task: Prova
---
# Lista delle cose che rimangono da fare
1. #### - Gestire User Domani + GDPR 
	1. Personal data
		1. ~~Debug: crea un record user_consent dei consensi che ha dato, ogni volta che si salva il record dal form personal data~~
	2. Organization
	3. Social
	4. Documentation
	5. Gestione notifiche GDPR
2. ~~#### - Creare la Home page per l'artista, in cui si naviga all'interno delle sue pagine~~
3. ~~Nella Portfolio dei creator occorre togliefre tutte le stat coperte da privacy e creare una pagina sul suo profilo persola con tutte le statistiche che deve vedere solamente lui~~
4. Occorre gestire bene lo scenario per i Collector.
	1. Essi non hanno le collection ma solo il portfolio con tutti gli EGI acquistati
	2. Hanno anch'essi un ottima batteria di statistiche
	3. Posso sopprimere il concetto di commissioner e creare il Co Creator, e sostituire Attivatore con Co Creatro, invece per ora il verbo attivare va bene. 
	4. Devo pensare bene all'area Enterprise
5. ~~Devo inserire i traits e le utility come metadata nel certificato~~
	1. ~~Il certificato va implementato con tutti i dati che un opera deve avere~~
		1. ~~Tecnica, Stile...~~
6. #### - Devo gestire le traduzioni per  'activity_categories' in config/gdpr.php. Le category sono usate in AuditLogService. Name e Description per ora sono hardcoded in inglese.
7. #### - GDPR Export data, implementare interfaccia, Il backend è completo anche per le biografie.
8. #### - Controllare tutte le classi e verificare che UEM sia stato ben implementato (in molte classi viene usato ULM al posto di UEM)
9. #### - Allineare tutte le label sui consensi fra le varie finestre: registration, personal-data, gdpr/consent, consent/preferences
	1. Anche valutare che non ci siano dati ridondanti o consensi ambigui oppure duplicati con differenti nomi
10. ==#### - Finire i termini di servizio (in progress)==
11. ==#### - Finire gestione notifiche Gdpr (in progress)==
12. #### - ~~Rifattorizzare tutte le classi della prenotazione con OS2, eliminando Facades e gestendo log e UEM~~
13. #### - Viste delle Policy
	1. ~~Privacy_policy~~
	2. Cookies-policy
	3. data processing agreement
	4. gdpr notice
	5. ~~consent form~~
14. #### - Su Register, completa gestione della verifica delle email
15. ==#### - Su Register rifare la gestione dei consensi (in progress)==
16. #### - Controllare utente "collector" quando atterra su dashboard ->gdr->consent, da errore: "null returned", verificare cosa gli manca come permessi in quanto collector, e comunque l'errore deve essere gestito.
17. #### - GdprController occorre implementare FegiAuth.
18. #### - Su utente loggato decidere cosa visualizzare nel pulsante Wallet Connet dopo login
19. #### - Verificare la corretta gestione della cache
20. #### - Trasferimenti interni ed esterni degli EGI
21. #### - Documentazione per utenti
22. #### - Mettere a punto il sistema della fatturazione, occhio al rapporto fra mecenate e creator, tutto deve essere in automatico
23. #### - Creare tutti i documenti per GDPR (in progress)
24. #### - Gestione DROP
25. 59. #### - Gestione ebook
26. #### - Gestione salvataggio su IPFS
27. #### - Rivedere e perfezionare upload multiserver con secure fallback
28. #### - Gestione CDN
29. #### - Contratti, documenti di accordo tra le parti, fatture, report fiscali
30. #### - Gestione change crypto/FIAT
31. #### - Rivedere copia dati account su genera nuovo walllet
32. #### - Sempre su genera wallet sistemare le due traduzioni di titolo e sotto titolo
33. #### - Fare vero refresh di UUM
34. ==#### - Crea wallet inserire in automatico il wallet dello user==
35. ==#### - Cosa significa verificare certificato e perché non funziona==
36. ==#### - Occorre creare gestione EPP==
37. ==#### - Occorre creare sezione in cui si visualizzano tutti i certificati==
38. ==#### - Nella propria dashboard si deve avere elenco degli EGI prenotati, con possibilità di vedere i certificati==
39. ==#### - Trasformazione del proprio account da debole a forte==
40. #### - ~~Layout di UUM deve essere mobile first~~
41. ==#### - Creazione del White Paper definitivo (in progress)==
42. ==#### - Creazione del Business Plain==
43. ==#### - Riflettere a proposito della composizione del prezzo dell'EGI, costituito dal valore dell'NFT puro e quello dell'opera fisica. (come viene gestita la spartizione delle royalty fra creator e azienda in questo caso?)==
44. ==#### - Creare tutte le pagine di spiegazione per ogni attore==
45. #### - ~~Creazione badge per navbar delle notifiche~~
46. #### - ~~UserRoleService deve essere rivisto perché c'è confusione negli userType e nei role, e verificare perché in fase di registrazione all'utente viene assegnato il ruolo di admin anziché di creator~~
47. ~~#### - CRUD EGI(in progress)~~
48. #### ~~- Aggiungere schedina per attore Trader pro per gli EGI pt~~
49. #### - Fare tutta la gestione EGI pt. Occorre studiare bene cosa rende gli NFT "virali e ambiti". (studio approfondito di mercato)
50. #### ~~- Su nuova collection, assicurarsi che di default sia pubblicata~~
51. #### ~~- Su user connesso debole non scrive current_collection_id su tabella users~~
52. #### - ~~Debuggare Natan assistant~~ 
53. #### - ~~Bug su creazione wallet, duplica tutti gli wallet~~
54. #### - ~~Controllare traduzioni su like da disconnesso~~
55. #### - ~~Creazione del file di traduzione: reservation.php~~
56. #### - ~~Fino a quando utente non crea un immagine per head collection card, nella home page occorre vedere nella card un EGI a caso. Se non ha EGI occorre un immagine di default.~~
57. #### - ~~Navbar utente loggato c'è un problema con il menu My Galleries che non si aggiorna e la lista delle gallerie in cui è invitato è errata~~
58. ~~#### - Layout degli EGI va rivisto~~
59. #### - ~~Gestione della bio~~
60. #### - ~~Nella dashboard per utenti forti, sistemare la visualizzazione delle notifiche~~
61. #### - ~~Decidere come gestire i traits in MVP~~
62. #### - ~~Decidere cosa fare con utility per MVP~~
63. #### - Gestione statistiche per utenti con registrazione debole
64. #### - ~~Layout generale da estendere a tutte le view~~
65. #### - ~~Adeguare perfettamente tutti i layout~~
66. #### - ~~Doppo prenotazione non mostra il certificato~~. 


<hr></hr>

<span style={{ color: 'green', fontWeight: 'bold' }}>
 ## Collection
<h3 style={{ color: 'red'}}>Log Channel: `collection`</h3>
</span>

### - Dati di testata 
#### - Gestione degli e-book
#### - Inclusione/Esclusione Item all'interno delle DROP
#### - Rivedere logica bind/unbind delle cover
#### - Gestione delle traduzioni mediante Spatie LARAVEL-TRANSLATABLE e Google Traductor
#### - Gestione storage multi server delle immagini per gli EGI
vedi: https://spatie.be/docs/laravel-translatable/v6/introduction
#### - Documento di accordo tra le parti
#### ~~- Gestione immagini di testata~~ definire esattamente quali immagini occorrono e quali dimensioni sono ideali
#### - Verifica funzionamento di ogni metodo
#### - Logging e commenti su ogni Classe, Metodo, e riga di codice
#### - Authorization
#### - Validation
#### - Sanitification
#### - Forbbiden Word Checker
#### - Tests



#### - adeguare in tutta l'applicazione i tag alt e title
#### - gestire le keyword SEO
#### - gestione footer
#### - gestione CDN
#### - gestione file su IPFS e salvataggio su più server


