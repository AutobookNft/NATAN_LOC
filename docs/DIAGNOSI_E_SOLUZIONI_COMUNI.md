# Sistema di Diagnosi e Suggerimenti - Progetto NATAN

## 🎯 Filosofia

**Non siamo critici, siamo partner.**

Ogni comune che presenta barriere tecniche all'accesso dei dati pubblici riceve:
1. **Diagnosi tecnica precisa** del problema
2. **Soluzioni concrete** con stime costi/tempi
3. **Email costruttiva** che enfatizza collaborazione e benefici

Questo dimostra la nostra genuina volontà di **dare servizio**, non solo di denunciare.

## 📊 Risultati Analisi Comuni Toscana

### Problemi Identificati

Dai 5 comuni analizzati:

| Problema | Comuni | % | Gravità |
|----------|--------|---|---------|
| **Nessuna API pubblica** | 4/5 | 80% | Media |
| **Form troppo complessi** | 3/5 | 60% | Bassa |
| **CAPTCHA su dati pubblici** | 1/5 | 20% | Alta |
| **Timeout/Errori rete** | 1/5 | 20% | Alta |

### Analisi per Comune

#### 🏛️ Pisa
- **URL**: https://albopretorio.comune.pisa.it/
- **Problema principale**: Nessuna API pubblica
- **Soluzione suggerita**: API REST in JSON (€500-1.000, 2-3 giorni)
- **Priorità**: Media
- **Fattibilità fix**: Alta

#### 🏛️ Livorno  
- **URL**: https://www.comune.livorno.it/trasparenza-vm/albo-pretorio
- **Problemi**: 
  1. CAPTCHA su dati pubblici ⚠️
  2. Form 25 campi (troppo complesso)
  3. Nessuna API
- **Soluzione principale**: Rimuovere CAPTCHA (€0, 1 ora)
- **Priorità**: Alta (CAPTCHA viola accessibilità)
- **Fattibilità fix**: Molto alta

#### 🏛️ Arezzo
- **URL**: https://servizionline.comune.arezzo.it/jattipubblicazioni/
- **Problemi**:
  1. Form 13 campi
  2. Nessuna API
- **Soluzione suggerita**: Semplificare form + aggiungere API (€800-1.200, 3 giorni)
- **Priorità**: Media
- **Fattibilità fix**: Alta

#### 🏛️ Siena
- **URL**: https://www.comune.siena.it/albo-pretorio
- **Problemi**:
  1. Form 17 campi
  2. Nessuna API
- **Soluzione suggerita**: API REST o export CSV (€500-800, 2 giorni)
- **Priorità**: Media
- **Fattibilità fix**: Alta

#### 🏛️ Lucca
- **URL**: https://lucca.trasparenza-valutazione-merito.it/
- **Problemi**: Timeout connessione (possibile infrastruttura sovraccarica)
- **Necessità**: Analisi approfondita infrastruttura
- **Priorità**: Media
- **Fattibilità fix**: Da valutare

## 📧 Strategia Comunicativa

### Template Email Generato

Ogni comune riceve email personalizzata con:

1. **Presentazione rispettosa** del progetto NATAN
2. **Descrizione problema tecnico** specifica (es. "CAPTCHA su dati pubblici")
3. **Base normativa** (Art. 35 D.lgs 33/2013, WCAG 2.1)
4. **2-3 Soluzioni concrete** con stime:
   - Costo (€0 - €3.000)
   - Tempo (1 ora - 7 giorni)
   - Priorità (bassa/media/alta)
5. **Benefici per il comune**:
   - Conformità normativa
   - Riduzione carico URP
   - Migliore servizio cittadini
   - Riconoscimento "Comune Virtuoso"
6. **Offerta supporto gratuito**:
   - Consulenza tecnica
   - Test soluzione
   - Documentazione caso successo

### Tono dell'Email

✅ **Cosa facciamo**:
- Riconosciamo il lavoro della PA
- Offriamo soluzioni concrete
- Enfatizziamo benefici comuni
- Disponibilità a collaborare gratuitamente

❌ **Cosa evitiamo**:
- Tono accusatorio
- Minacce legali immediate
- Critica pubblica prima del contatto
- Richieste senza offrire supporto

### Esempio Email (Livorno - CAPTCHA)

```
Spett.le Comune di Livorno,

Durante l'analisi tecnica del vostro Albo Pretorio per il progetto NATAN, 
abbiamo riscontrato alcune criticità nell'accesso automatizzato ai dati 
pubblici che vorremmo segnalarvi in modo costruttivo.

## 📋 Problema Identificato

Il vostro Albo Pretorio utilizza CAPTCHA per l'accesso.

Problema: Il CAPTCHA impedisce l'accesso automatizzato e crea barriere 
per utenti con disabilità visive.

Violazione accessibilità: Contrasta le WCAG 2.1 (Web Content Accessibility Guidelines).

## ✅ Soluzione Proposta

**Soluzione Ideale**: Rimuovere CAPTCHA per dati pubblici
- Dati già pubblici → non servono protezioni
- Mantenere CAPTCHA solo per form di invio (se presenti)
- Implementazione: 1 ora
- Costo: Zero

**Soluzione Alternativa**: Rate Limiting Intelligente
- Max 100 richieste/minuto per IP
- Blocco temporaneo in caso abuso
- Nessun CAPTCHA per accesso normale
- Implementazione: 1 giorno
- Costo: ~€200-400

## 🎯 Benefici della Soluzione

✅ Conformità normativa: Aderenza all'Art. 35 D.lgs 33/2013 (formato aperto)
✅ Migliore servizio cittadini: Accesso facilitato alle informazioni
✅ Riduzione carico URP: Meno richieste manuali di documentazione
✅ Interoperabilità: Dati utilizzabili da altri servizi digitali
✅ Trasparenza proattiva: Il Comune come esempio di PA digitale

## 🤝 Nostra Disponibilità

Siamo disponibili a:
- Fornire consulenza tecnica gratuita
- Testare la soluzione implementata
- Documentare il vostro Comune come caso di successo
- Supportare l'ufficio tecnico nell'implementazione

Restiamo a disposizione per ogni chiarimento e supporto tecnico.

Cordiali saluti,

Fabio Massacci
Progetto NATAN
Email: fabio@natan.it
```

## 🔧 Soluzioni Tecniche Proposte

### 1. API REST Pubblica (Problema: no_api)

**Descrizione**: Endpoint JSON per accesso programmatico

**Implementazione Minima**:
```
GET /api/albo/atti?anno=2025&tipo=delibera&page=1&per_page=50

Response:
{
  "total": 1234,
  "page": 1,
  "per_page": 50,
  "atti": [
    {
      "id": "DG-2025-001",
      "tipo": "Delibera di Giunta",
      "numero": "1",
      "anno": "2025",
      "data": "2025-01-15",
      "oggetto": "Approvazione bilancio...",
      "data_pubblicazione": "2025-01-20",
      "url_pdf": "https://..."
    }
  ]
}
```

**Costo**: €500-1.000 (una tantum)
**Tempo**: 2-3 giorni sviluppo
**Manutenzione**: Nessuna (usa DB esistente)

### 2. Export CSV/JSON Risultati (Alternativa low-cost)

**Descrizione**: Pulsante "Esporta" su pagina risultati ricerca

**Implementazione**:
- Pulsante "Scarica CSV" / "Scarica JSON"
- Genera file con risultati ricerca corrente
- Nessuna API, solo export on-demand

**Costo**: €300-500
**Tempo**: 1-2 giorni
**Vantaggio**: Già soddisfa obbligo "formato aperto"

### 3. Rimozione CAPTCHA (Problema: captcha)

**Descrizione**: Eliminare CAPTCHA su sezioni pubbliche

**Implementazione**:
- Configurazione web server: whitelist /albo-pretorio/*
- Mantenere CAPTCHA solo su form invio dati

**Costo**: €0 (solo configurazione)
**Tempo**: 30 minuti - 1 ora
**Vantaggio**: Migliora accessibilità (WCAG 2.1 compliance)

### 4. Semplificazione Form (Problema: form_complex)

**Descrizione**: Ridurre campi form ricerca

**Prima** (17 campi):
- Tipo atto, Numero, Anno, Data da, Data a, Oggetto, Settore, UO, Responsabile, Stato, Categoria, Sottocategoria, Tag, Ordinamento, Risultati per pagina, ecc.

**Dopo** (5 campi essenziali):
- Tipo atto (dropdown)
- Data da / Data a
- Oggetto (ricerca testo)
- Risultati per pagina

**Costo**: €500-800
**Tempo**: 2-3 giorni
**Vantaggio**: Migliore UX per cittadini

### 5. Cloudflare Whitelist (Problema: cloudflare_protection)

**Descrizione**: Configurare Cloudflare per permettere accesso legittimo

**Implementazione Cloudflare**:
```
Firewall Rules:
- Rule 1: Allow /albo-pretorio/* (bypass anti-bot)
- Rule 2: Rate limit 100 req/min (anti-abuse)
- Rule 3: Block malicious bots (mantiene sicurezza)
```

**Costo**: €0 (configurazione Cloudflare)
**Tempo**: 30 minuti
**Vantaggio**: Mantiene protezione DDoS, permette accesso legittimo

## 📊 ROI per i Comuni

### Investimento
- **Soluzione minima** (export CSV): €300-500, 1-2 giorni
- **Soluzione ideale** (API REST): €1.000-2.000, 3-5 giorni

### Ritorni (annuali)

1. **Riduzione carico URP**
   - Stima: 50 richieste/anno evitate
   - Tempo risparmiato: ~25 ore/anno (30 min/richiesta)
   - Valore: €500-750/anno (dipendente comunale €20-30/ora)

2. **Conformità normativa**
   - Evita sanzioni ANAC per violazione trasparenza
   - Evita ricorsi TAR cittadini
   - Valore: Non quantificabile ma significativo

3. **Reputazione**
   - Riconoscimento "Comune Virtuoso"
   - Visibilità media positiva
   - Attrattività investimenti/residenti

4. **Interoperabilità**
   - Dati riutilizzabili da altri servizi PA
   - Integrazione con piattaforme regionali/nazionali
   - Open data compliance

**Payback period**: 1-2 anni (solo riduzione carico URP)

## 🎯 Piano d'Azione

### Fase 1: Invio Email Costruttive (Settimana 1-2)
- [x] Generare diagnosi tecnica automatica
- [x] Creare email personalizzate con soluzioni
- [ ] Inviare a comuni problematici (5 già analizzati)
- [ ] Tracking risposte

### Fase 2: Supporto Tecnico (Settimana 3-8)
- [ ] Videoconferenze con comuni interessati
- [ ] Consulenza tecnica gratuita
- [ ] Documentazione implementazione
- [ ] Test soluzioni implementate

### Fase 3: Casi di Successo (Mese 3-4)
- [ ] Documentare comuni che hanno fixato
- [ ] Pubblicare case study positivi
- [ ] Riconoscimento "Comuni Virtuosi"
- [ ] Media coverage positivo

### Fase 4: Escalation (Solo se necessario, Mese 5+)
- [ ] Follow-up FOIA comuni non rispondenti
- [ ] Segnalazione ANAC (solo dopo 60 giorni)
- [ ] Valutazione ricorso TAR (ultima ratio)

## 📈 Metriche di Successo

### KPI Tecnici
- **Comuni con API**: Target 50% entro 6 mesi
- **Comuni CAPTCHA-free**: Target 100% entro 3 mesi
- **Comuni form semplificati**: Target 30% entro 6 mesi

### KPI Relazionali
- **Tasso risposta email**: Benchmark 30-40%
- **Comuni in collaborazione attiva**: Target 10-15%
- **Feedback positivo**: Target >80%

### KPI Impatto
- **Atti scrapabili**: Da 542 (2 comuni) a >50.000 (target 100 comuni)
- **Copertura popolazione**: Da 15% a >60%
- **Comuni conformi D.lgs 33/2013**: Da ~20% a >80%

## 🤝 Valore Aggiunto Progetto NATAN

### Per i Comuni
1. **Consulenza tecnica gratuita** (valore: €1.000-3.000)
2. **Test e validazione soluzioni** (valore: €500-1.000)
3. **Documentazione e case study** (visibilità)
4. **Supporto conformità normativa** (evita sanzioni)

### Per i Cittadini
1. **Accesso facilitato atti amministrativi**
2. **AI assistant per comprensione atti**
3. **Notifiche proattive temi interesse**
4. **Servizio gratuito e open source**

### Per la PA nel Complesso
1. **Standard de facto per API albo pretorio**
2. **Riduzione frammentazione piattaforme**
3. **Cultura trasparenza e open data**
4. **Innovazione sociale bottom-up**

## 📚 Documentazione Tecnica

- **Diagnostic Scraper**: `/app/scrapers/diagnostic_scraper.py`
- **Report JSON**: `diagnostic_results.json`
- **Email Suggerimenti**: `emails_suggerimenti/*.txt`
- **Template Comunicazioni**: `/docs/COMUNICAZIONI_COMUNI.md`

## 🔗 Link Utili

- **Progetto NATAN**: https://github.com/AutobookNft/NATAN_LOC
- **D.lgs 33/2013**: https://www.normattiva.it/uri-res/N2Ls?urn:nir:stato:decreto.legislativo:2013-03-14;33
- **WCAG 2.1**: https://www.w3.org/WAI/WCAG21/quickref/
- **IndicePA**: https://indicepa.gov.it/

---

**Ultimo aggiornamento**: 13 Novembre 2025  
**Versione**: 1.0  
**Autore**: Fabio Massacci - Progetto NATAN
