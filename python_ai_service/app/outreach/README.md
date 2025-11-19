# Sistema di Outreach Comuni NATAN

Sistema completo per gestire la campagna di outreach verso i 273 comuni della Toscana.

## 🎯 Obiettivo

Ottenere accesso agli atti amministrativi del 100% dei comuni toscani attraverso:
1. **Collaborazione tecnica** (metodo preferito)
2. **Accesso civico semplice** (Art. 5 D.lgs 33/2013)
3. **Escalation legale** (ANAC, TAR se necessario)

## 📁 Struttura File

```
app/outreach/
├── comuni_outreach_manager.py    # Gestione stato comuni e tracking
├── send_emails_comuni.py         # Invio email programmato
├── pec_finder.py                 # Ricerca automatica PEC
├── comuni_outreach_status.json   # Database stato (auto-generato)
└── pec_comuni_cache.json         # Cache PEC trovate (auto-generato)
```

## 🚀 Quick Start

### 1. Trova PEC Comuni

```bash
# Trova automaticamente PEC di tutti i comuni
cd /home/fabio/dev/NATAN_LOC/python_ai_service/app/outreach
python3 pec_finder.py
```

Output:
- **pec_comuni_cache.json**: Cache PEC trovate
- Fonti: IndicePA (ufficiale), sito comune, pattern generati

### 2. Inizializza Database Comuni

```python
from comuni_outreach_manager import ComuniOutreachManager
import json

manager = ComuniOutreachManager()

# Carica comuni da trivella_brutale_results.json
with open('../scrapers/trivella_brutale_results.json', 'r') as f:
    risultati = json.load(f)
    
    for comune in risultati['comuni']:
        manager.inizializza_comune(
            nome=comune['comune'],
            url=comune['url'],
            abitanti=comune['abitanti'],
            funzionante_tecnico=comune['funzionante']
        )

# Dashboard
print(manager.genera_dashboard())
```

### 3. Visualizza Dashboard

```bash
python3 comuni_outreach_manager.py
```

Output:
```
╔══════════════════════════════════════════════════════════════╗
║         DASHBOARD OUTREACH COMUNI - Progetto NATAN          ║
╚══════════════════════════════════════════════════════════════╝

📊 STATO GENERALE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Totale comuni:              255
Abitanti totali:            3.692.555

✅ FUNZIONANTI
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Tecnico (scraping):         3 comuni
Collaborazione attiva:      0 comuni
Coverage popolazione:       15.2%
Abitanti coperti:           562.000

📧 OUTREACH IN CORSO
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Non contattati:             252 comuni
Email 1 inviata:            0 comuni
Email 2 inviata (follow):   0 comuni
```

### 4. Test Invio Email (Dry Run)

```bash
# Simula invio senza inviare realmente
python3 send_emails_comuni.py

# Simula invio di 10 email
python3 send_emails_comuni.py --max 10
```

Output:
```
🧪 DRY RUN - Email NON inviata
============================================================
To: protocollo@pec.comune.pisa.it
Subject: Progetto NATAN - Sistema AI per migliorare l'accesso...

Spett.le Comune di Pisa,
...
```

### 5. Invio Reale (con conferma)

```bash
# INVIO REALE - Richiede conferma
python3 send_emails_comuni.py --send --max 5
```

Input richiesto:
- Email mittente (es. fabio@natan.it)
- Password SMTP
- Conferma esplicita

Rate limiting:
- Max 10 email/ora (anti-spam)
- Pausa 10 secondi tra email

## 📊 Workflow Completo

```
┌─────────────────┐
│ Trivella Brutale│  ← Identifica comuni funzionanti tecnicamente
└────────┬────────┘
         ↓
┌─────────────────┐
│  PEC Finder     │  ← Trova indirizzi PEC comuni
└────────┬────────┘
         ↓
┌─────────────────┐
│ Outreach Manager│  ← Inizializza database stati
└────────┬────────┘
         ↓
┌─────────────────┐
│  Email 1        │  ← Presentazione collaborativa (giorno 0)
│  (Introduzione) │
└────────┬────────┘
         ↓
      Attesa 15 giorni
         ↓
    ┌───────┴────────┐
    │                │
    ↓                ↓
┌─────────┐    ┌─────────────┐
│Risposta │    │Nessuna      │
│Positiva │    │Risposta     │
└────┬────┘    └──────┬──────┘
     ↓                ↓
┌─────────┐    ┌─────────────┐
│Collabora│    │  Email 2    │  ← Follow-up + Accesso Civico
│zione    │    │  (FOIA)     │  ← Art. 5 D.lgs 33/2013
└─────────┘    └──────┬──────┘
                      ↓
                 Attesa 30 giorni
                      ↓
                 ┌────────┴─────────┐
                 │                  │
                 ↓                  ↓
           ┌─────────┐      ┌──────────────┐
           │Risposta │      │Nessuna       │
           │Positiva │      │Risposta      │
           └─────────┘      └──────┬───────┘
                                   ↓
                            ┌──────────────┐
                            │Segnalazione  │  ← ANAC
                            │ANAC          │  ← Art. 5, c. 7
                            └──────┬───────┘
                                   ↓
                              Attesa 30 giorni
                                   ↓
                            ┌──────────────┐
                            │Ricorso TAR   │  ← Ultima ratio
                            └──────────────┘
```

## 📝 Template Email

### Email 1: Presentazione Collaborativa

**Quando**: Primo contatto
**Tono**: Positivo, collaborativo, enfasi su benefici comuni
**Obiettivo**: Ottenere collaborazione volontaria

Punti chiave:
- Presentazione progetto NATAN (AI per trasparenza)
- Benefici per il comune:
  - Riduce carico URP
  - Migliora conformità normativa
  - Valorizza lavoro PA
  - Nessun costo
- Richiesta: Accesso dati formato aperto (JSON/CSV/XML)

### Email 2: Follow-up + Accesso Civico

**Quando**: Dopo 15 giorni senza risposta
**Tono**: Professionale, formale ma non ostile
**Obiettivo**: Formalizzare richiesta FOIA

Punti chiave:
- Richiamo email precedente
- Formalizzazione **Accesso Civico Semplice** (Art. 5 D.lgs 33/2013)
- Specifica dati richiesti
- Termine 30 giorni per risposta (obbligatorio per legge)

### Email 3: Ringraziamento "Comuni Virtuosi"

**Quando**: Dopo risposta positiva/collaborazione
**Tono**: Entusiasta, riconoscimento pubblico
**Obiettivo**: Rinforzare comportamento positivo

Punti chiave:
- Ringraziamento per collaborazione
- Riconoscimento "Comune Virtuoso"
- Offerta visibilità pubblica (social media, report)
- Richiesta testimonial (opzionale)

## 🎛️ Stati Comuni

```python
class StatoComune(Enum):
    NON_CONTATTATO           # Mai contattato
    EMAIL1_INVIATA           # Email introduttiva inviata
    EMAIL2_INVIATA           # Follow-up + FOIA inviato
    RISPOSTA_RICEVUTA        # Risposta ricevuta (qualsiasi)
    COLLABORAZIONE_ATTIVA    # Stanno collaborando
    ACCESSO_CIVICO_FORMALE   # FOIA formale inviato
    SEGNALAZIONE_ANAC        # Segnalato ad ANAC
    FUNZIONANTE_TECNICO      # Scraping funziona
    RIFIUTO                  # Rifiuto esplicito
```

## 📈 Metriche di Successo

### KPI Principali

1. **Coverage popolazione**: % abitanti coperti (target: 100%)
2. **Comuni funzionanti**: Tecnico + Collaborazione (target: 273)
3. **Tasso risposta email 1**: % comuni che rispondono (benchmark: 20-30%)
4. **Tasso conversione collaborazione**: % comuni collaboranti (target: 10-15%)

### Tracking

```bash
# Esporta report CSV per analisi
python3 -c "
from comuni_outreach_manager import ComuniOutreachManager
mgr = ComuniOutreachManager()
mgr.esporta_report_csv('report_$(date +%Y%m%d).csv')
"
```

Output: `report_YYYYMMDD.csv`

Colonne:
- Comune
- Abitanti
- Stato
- Funzionante Tecnico
- Data Primo Contatto
- Ultima Azione
- Note

## ⚖️ Note Legali

### Accesso Civico Semplice

**Base normativa**: Art. 5, c. 1 D.lgs 33/2013

> L'obbligo previsto dalla normativa vigente in capo alle pubbliche amministrazioni di pubblicare documenti, informazioni o dati comporta il diritto di chiunque di richiedere i medesimi, nei casi in cui sia stata omessa la loro pubblicazione.

**Caratteristiche**:
- ✅ Non servono motivazioni
- ✅ Risposta obbligatoria entro 30 giorni
- ✅ Gratuito
- ✅ Silenzio = Rifiuto (ricorribile)

### Formato Aperto

**Base normativa**: Art. 35 D.lgs 33/2013

> Tutti i documenti, le informazioni e i dati oggetto di pubblicazione obbligatoria ai sensi della normativa vigente sono pubblici e chiunque ha diritto di conoscerli, di fruirne gratuitamente, e di utilizzarli e riutilizzarli ai sensi degli articoli 3 e 9.

### Escalation

1. **Nessuna risposta dopo 30 giorni** → Segnalazione ANAC (Art. 5, c. 7)
2. **ANAC non interviene** → Ricorso TAR (tutela giurisdizionale)

### Comuni che Bloccano Accesso

**VIOLAZIONE DI LEGGE** se:
- Cloudflare/bot protection su albo pretorio pubblico
- Captcha su dati obbligatori
- Negazione accesso formato aperto

## 🔧 Configurazione SMTP

### Gmail

```python
sender = EmailSender(
    smtp_server='smtp.gmail.com',
    smtp_port=587
)
sender.configure(
    email='tuo@gmail.com',
    password='app_password'  # Non password normale!
)
```

**Password App**:
1. Account Google → Sicurezza
2. Verifica in due passaggi (attiva)
3. Password per le app → Genera

### Altri Provider

- **Outlook**: smtp.office365.com:587
- **Yahoo**: smtp.mail.yahoo.com:587
- **SMTP personalizzato**: Configura parametri

## 📅 Timeline Suggerita

### Fase 1: Setup (Settimana 1)
- [x] Creare sistema outreach
- [x] Template email
- [x] Ricerca PEC
- [ ] Test dry run 20 comuni

### Fase 2: Primo Contatto (Mese 1-2)
- [ ] Email 1 a tutti i 252 comuni non funzionanti
- [ ] Rate: 10-15 email/giorno
- [ ] Tracking risposte

### Fase 3: Follow-up (Mese 2-3)
- [ ] Email 2 dopo 15 giorni senza risposta
- [ ] Primi FOIA formali
- [ ] Collaborazioni attive

### Fase 4: Escalation (Mese 3-6)
- [ ] Segnalazioni ANAC comuni non rispondenti
- [ ] Campagna media "Comuni Virtuosi"
- [ ] Partnership con associazioni

### Fase 5: Legale (Mese 6-12)
- [ ] Valutazione ricorsi TAR
- [ ] Class action comuni bloccanti
- [ ] Advocacy per modifica normativa

## 🎯 Best Practices

### DO ✅
- Tono sempre professionale e collaborativo
- Enfatizzare benefici comuni e cittadini
- Rispettare rate limiting (anti-spam)
- Documentare TUTTO (tracking)
- Riconoscere pubblicamente "Comuni Virtuosi"
- Usare FOIA solo dopo tentativo collaborativo

### DON'T ❌
- Tono aggressivo o minaccioso
- Spam (rispetta tempi)
- Minacce legali nella prima email
- Pubblicizzare negativamente comuni prima di ANAC
- Bypassare illegalmente protezioni (rimani legale)

## 🤝 Strategia Comunicativa

### Framing

**Non siamo**: Hacker, antagonisti, critici della PA
**Siamo**: Cittadini, innovatori sociali, partner della PA

### Messaggi Chiave

1. **Per i cittadini**: "Diritto di sapere cosa fa la tua amministrazione"
2. **Per i comuni**: "Riduci il carico URP, migliora il servizio"
3. **Per i media**: "Innovazione sociale per trasparenza"
4. **Per il legislatore**: "La legge esiste, facciamola rispettare"

### Social Media

Dopo successi:
- LinkedIn: Post professionali su collaborazioni
- Twitter: Ringraziamenti pubblici "Comuni Virtuosi"
- Blog: Case study collaborazioni di successo

## 📞 Contatti

**Progetto NATAN**
- Email: fabio@natan.it
- GitHub: https://github.com/AutobookNft/NATAN_LOC
- Sito: (in sviluppo)

## 📚 Documentazione Completa

- [Template Comunicazioni](../../docs/COMUNICAZIONI_COMUNI.md)
- [Base Legale](../../docs/BASE_LEGALE_SCRAPING.md) (da creare)
- [Casi di Successo](../../docs/CASI_SUCCESSO.md) (da popolare)

---

**Ultimo aggiornamento**: 2025-01-XX
**Versione**: 1.0
**Autore**: Fabio Massacci
