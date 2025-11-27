# ✅ Prossimi Step FlorenceEGI – Sezione Collezionisti

## 🧠 1. Logica Follow (solo per i collezionisti)

### Implementazione Database
- **Migrazione DB**: tabella `collector_follows` (es. `follower_id`, `followed_collector_id`, timestamps)
- **Restrizione ruoli**: solo i collezionisti possono essere seguiti
- **Query**: collezionisti più seguiti (classifica), "i miei seguiti", follower count

### User Interface
- **UI**: bottone "Segui" sulla scheda collector + sezione "Collezionisti che segui" nella Community
- **Notifiche (eventuale)**: "Hai un nuovo follower"

---

## ✍️ 2. Articoli degli EGI acquistati

### Implementazione Database
- **Migrazione DB**: tabella `collector_articles` (`collector_id`, `egi_id`, `title`, `body`, `published_at`, ecc.)
- **Permessi**: solo chi ha acquistato quell'EGI può scrivere articoli su di esso

### User Interface
- **Form articolo** nella pagina EGI → solo per acquirente
- **Tab "Articoli"** nella pagina Community
- **Mostra articoli** nella scheda dell'EGI
- **Moderazione (eventuale)**

---

## 🧑‍🎨 3. Collector Home Page

### Struttura Tecnica
- **Rotte + controller dedicato**
- **Visuale clone di Artist Home Page**, ma con contenuti specifici per collezionisti:

### Sezioni Personalizzate
- **`Opere in evidenza`** → EGI acquistati
- **`Collezioni recenti`** → collezioni automatiche da acquisti
- **`Impatto`** → punti EPP
- **`Biografia`**, **`Community`** → attivi

---

## 🌱 4. Statistiche Impatto EPP (MVP)

### Logica Business
- **Query aggregata**: somma degli € derivanti dalle prenotazioni/acquisti → `epp_id = 2`
- **Assegnazione**: l'importo va associato al collector, non al creator

### User Interface
- **Home page collector**: scheda "Impatto ambientale"
- **Community**: ranking dei maggiori contribuenti
- **(Futuro) EPP page**: mostra i nomi dei committenti top

---

## 🧭 5. Implementazione menu Collector Home Page

### Sezioni del Menu

#### **Overview**
- Sintesi con impatto, collezioni recenti, articoli scritti

#### **Portfolio** 
- Tutti gli EGI acquistati

#### **Collezioni**
- Lista collezioni automatiche

#### **Biografia**
- Sezione pubblica modificabile

#### **Community**
- Articoli scritti
- Follower / seguiti  
- EGI piaciuti

---

## 🎯 Obiettivo Finale

**Completare il ciclo utente fino al "riconoscimento post-acquisto"**

Trasformare l'esperienza del collezionista da semplice acquisto a partecipazione attiva nella community, con strumenti per esprimere opinioni, seguire altri collezionisti e dimostrare il proprio impatto ambientale attraverso gli acquisti EPP.

---

## 📋 Priorità Implementazione

1. **Alta Priorità**: Collector Home Page + Menu (punto 3 + 5)
2. **Media Priorità**: Statistiche EPP (punto 4) 
3. **Bassa Priorità**: Sistema Follow (punto 1) + Articoli EGI (punto 2)

*Roadmap progettata per massimizzare l'engagement dei collezionisti e creare una community attiva attorno agli acquisti di EGI.*