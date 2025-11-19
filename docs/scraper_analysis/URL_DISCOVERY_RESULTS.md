# 🎯 DISCOVERY URL COMUNI - RISULTATI FINALI

**Data**: 13 novembre 2025  
**Fase**: Ricerca URL completata per 12/13 comuni

---

## ✅ COMUNI ACCESSIBILI - URL CONFERMATI

### **🟢 TIER 1 - Pronti Immediato (4 comuni)**

#### **1. FIRENZE** (382k abitanti)
```
URL Albo: https://accessoconcertificato.comune.fi.it/AOL/Affissione/ComuneFi/Page
URL API:  https://accessoconcertificato.comune.fi.it/trasparenza-atti-cat/searchAtti
Platform: Custom (Drupal base + API REST)
Status: ✅ PRODUCTION-READY (già implementato)
```

#### **2. AREZZO** (99k abitanti)
```
URL: https://www.comune.arezzo.it/albo-pretorio
Platform: Drupal
Status: ✅ ACCESSIBILE
```

#### **3. SIENA** (53k abitanti)
```
URL: https://www.comune.siena.it/albo-pretorio
Platform: Drupal
Status: ✅ ACCESSIBILE
```

#### **4. LUCCA** (89k abitanti)
```
URL: https://www.comune.lucca.it/albo-pretorio
Platform: WordPress
Status: ✅ ACCESSIBILE
```

---

### **🟢 TIER 1.5 - Vendor "Trasparenza Valutazione Merito" (4 comuni)**

**🎯 SCOPERTA IMPORTANTE**: 4 comuni usano STESSO VENDOR!

#### **5. LIVORNO** (157k abitanti)
```
URL: https://livorno.trasparenza-valutazione-merito.it/web/trasparenza/albo-pretorio
Platform: Trasparenza Valutazione Merito (Vendor)
Status: ✅ ACCESSIBILE (JS-heavy)
```

#### **6. GROSSETO** (82k abitanti)
```
URL: https://grosseto.trasparenza-valutazione-merito.it/
Platform: Trasparenza Valutazione Merito (Vendor)
Status: ✅ ACCESSIBILE (JS-heavy)
```

#### **7. PISTOIA** (90k abitanti)
```
URL: https://pistoia.trasparenza-valutazione-merito.it/web/trasparenzaj/albo-pretorio
Platform: Trasparenza Valutazione Merito (Vendor)
Status: ✅ ACCESSIBILE (JS-heavy)
```

#### **8. CARRARA** (62k abitanti)
```
URL: https://carrara.trasparenza-valutazione-merito.it/web/trasparenza/albo-pretorio
Platform: Trasparenza Valutazione Merito (Vendor)
Status: ✅ ACCESSIBILE (JS-heavy)
```

**Note Vendor**:
- ⚠️ Contenuto caricato dinamicamente via JavaScript
- ⚠️ Richiede browser automation (Playwright) o API discovery
- ✅ Pattern IDENTICO tra i 4 comuni → UN SOLO scraper necessario
- 🎯 ROI ALTISSIMO: 1 scraper = 4 comuni (31% del totale!)

---

### **🟡 TIER 2 - Vendor Diversi (2 comuni)**

#### **9. MASSA** (68k abitanti)
```
URL: https://cloud.urbi.it/urbi/progs/urp/ur1ME001.sto?DB_NAME=n201312
Platform: URBI (Vendor Cloud)
Status: ✅ ACCESSIBILE (JS-heavy, query string based)
```

#### **10. PISA** (90k abitanti)
```
URL: https://albopretorio.comune.pisa.it/
Platform: Unknown (da identificare)
Status: ✅ ACCESSIBILE
```

---

### **🟡 TIER 2.5 - Richiedono Analisi Complessa (2 comuni)**

#### **11. PRATO** (195k abitanti)
```
URL: https://trasparenza.comune.prato.it/
Platform: Istituzionale (Custom)
Status: ✅ ACCESSIBILE
```

#### **12. BAGNO A RIPOLI** (26k abitanti)
```
URL: https://trasparenza.comune.bagno-a-ripoli.fi.it/web/trasparenza/menu-albo-pretorio
Platform: Custom JS-heavy (base Drupal)
Status: ✅ ACCESSIBILE (149 script tags, molto complesso)
```

---

### **🔴 TIER 3 - URL Non Trovato (1 comune)**

#### **13. VIAREGGIO** (62k abitanti)
```
Status: ⚠️ URL NON IDENTIFICATO
Tentativi falliti:
- https://www.comune.viareggio.lu.it/albo-pretorio (404)
- https://servizi.comune.viareggio.lu.it/albo (404)
- https://albopretorio.comune.viareggio.lu.it/ (connection error)
- https://viareggio.trasparenza-valutazione-merito.it/ (SSL error)

Azione richiesta: Ricerca manuale più approfondita o skip temporaneo
```

---

## 📊 SUMMARY STATISTICHE

| Categoria | Comuni | % | Note |
|-----------|--------|---|------|
| **Drupal standard** | 3 | 23% | Firenze, Arezzo, Siena |
| **WordPress** | 1 | 8% | Lucca |
| **Vendor "Trasparenza VM"** | 4 | 31% | Livorno, Grosseto, Pistoia, Carrara |
| **Vendor URBI** | 1 | 8% | Massa |
| **Custom/Complessi** | 3 | 23% | Prato, Bagno a Ripoli, Pisa |
| **Non trovati** | 1 | 8% | Viareggio |
| **TOTALE ACCESSIBILI** | **12/13** | **92%** | 🎉 Ottimo risultato! |

---

## 🏆 PRIORITÀ IMPLEMENTAZIONE

### **🔥 PRIORITÀ ALTISSIMA - Quick Wins (7 comuni = 54%)**

#### **Gruppo 1: Drupal Standard** (3 comuni)
- Firenze (già fatto ✅)
- Arezzo
- Siena

**Effort**: 1 scraper generico = 8-12 ore  
**ROI**: ALTISSIMO (pattern identico)

#### **Gruppo 2: Vendor "Trasparenza VM"** (4 comuni)
- Livorno
- Grosseto
- Pistoia
- Carrara

**Effort**: 1 scraper generico = 10-15 ore (richiede Playwright)  
**ROI**: MASSIMO (31% comuni con 1 scraper!)

---

### **🟡 PRIORITÀ ALTA - Individual Scrapers (2 comuni = 15%)**

#### **WordPress** (1 comune)
- Lucca

**Effort**: 6-10 ore  
**ROI**: ALTO (WordPress diffuso in piccoli comuni)

#### **Vendor URBI** (1 comune)
- Massa

**Effort**: 8-12 ore  
**ROI**: MEDIO (URBI potrebbe essere usato altrove)

---

### **🟢 PRIORITÀ MEDIA - Post-MVP (3 comuni = 23%)**

#### **Custom/Unknown**
- Pisa (Unknown vendor)
- Prato (Custom)

**Effort**: 4-8 ore ciascuno  
**ROI**: BASSO (soluzioni custom, no riuso)

---

### **⚪ PRIORITÀ BASSA - Complessi (2 comuni = 15%)**

#### **JavaScript-Heavy**
- Bagno a Ripoli (149 script tags)

**Effort**: 6-12 ore  
**ROI**: BASSO (molto complesso, comune piccolo)

#### **Non Trovato**
- Viareggio (skip o ricerca manuale approfondita)

---

## 🎯 ROADMAP IMPLEMENTAZIONE CONSIGLIATA

### **SPRINT 1 - Foundation (Settimana 1-2)**
1. ✅ Setup base architecture
2. ✅ Refactor `DrupalAlboScraper` generico (base Firenze)
3. ✅ Test su Arezzo e Siena
4. ✅ MongoDB integration verificata

**Deliverable**: 3 comuni funzionanti (23%)

---

### **SPRINT 2 - Vendor "Trasparenza VM" (Settimana 3-4)**
1. 🔍 Analisi manuale API "Trasparenza VM" (DevTools)
2. 🛠️ Implementa `TrasparenzaVMScraper` con Playwright
3. ✅ Test su 4 comuni (Livorno, Grosseto, Pistoia, Carrara)
4. ✅ Verifica multi-tenant MongoDB

**Deliverable**: +4 comuni funzionanti (totale 54%)

---

### **SPRINT 3 - WordPress & URBI (Settimana 5)**
1. 🛠️ Implementa `WordPressAlboScraper` (Lucca)
2. 🛠️ Implementa `URBIScraper` (Massa)
3. ✅ Test e integrazione

**Deliverable**: +2 comuni funzionanti (totale 69%)

---

### **SPRINT 4 - Custom Sites (Settimana 6)**
1. 🔍 Analisi Pisa (identificare vendor)
2. 🛠️ Scraper Pisa
3. 🛠️ Scraper Prato
4. ✅ Test

**Deliverable**: +2 comuni funzionanti (totale 85%)

---

### **POST-MVP - Complessi (Settimana 7+)**
1. 🔍 Analisi approfondita Bagno a Ripoli
2. 🛠️ Playwright-based scraper
3. 🔍 Ricerca approfondita Viareggio (manuale o skip)

**Deliverable**: Sistema completo 12-13 comuni (92-100%)

---

## 💡 INSIGHT STRATEGICI

### **1. Vendor "Trasparenza VM" è una Gold Mine**
- 31% dei comuni usa STESSO software
- Pattern identico → sviluppo 1 volta, deploy 4 comuni
- Probabile che altri comuni italiani usino stesso vendor
- **Scalabilità**: Questo scraper potrebbe funzionare per decine/centinaia di comuni in Italia

### **2. Drupal è Solido e Diffuso**
- 23% comuni analizzati
- Pattern prevedibile e stabile
- Scraper generico altamente riutilizzabile

### **3. JavaScript-Heavy è il Futuro**
- Vendor moderni (Trasparenza VM, Bagno a Ripoli) usano SPA
- Browser automation diventa necessario
- Playwright deve essere parte dell'architettura core

### **4. 92% Success Rate è Eccellente**
- 12/13 comuni accessibili
- Solo Viareggio problematico (può essere skippato temporaneamente)
- Obiettivo realistico: 100% con ricerca manuale aggiuntiva

### **5. ROI Stratificato**
```
SPRINT 1 (Drupal):  23% comuni con effort medio
SPRINT 2 (TVM):     31% comuni con effort medio → MAX ROI
SPRINT 3 (WP+URBI): 15% comuni con effort basso-medio
SPRINT 4 (Custom):  23% comuni con effort alto → LOW ROI
```

**Strategia**: Focus su SPRINT 1-2 per raggiungere 54% comuni con sforzo ragionevole.

---

## 📋 AZIONI IMMEDIATE

### **✅ COMPLETATE**
- [x] Identificati URL 12/13 comuni
- [x] Identificato vendor "Trasparenza VM" (31% comuni)
- [x] Identificato vendor URBI
- [x] Classificati comuni per priorità

### **🔥 PROSSIMI STEP**
1. **Refactor DrupalAlboScraper generico** (base: scraper Firenze esistente)
2. **Test DrupalAlboScraper** su Arezzo e Siena
3. **Analisi manuale API "Trasparenza VM"** (DevTools su Livorno)
4. **Setup Playwright** nel progetto per vendor JS-heavy

### **📚 REGOLA ZERO - Ricerca Necessaria**
- [ ] Best practices Playwright per scraping PA
- [ ] Anti-detection strategies per vendor moderni
- [ ] Rate limiting ottimale per "Trasparenza VM"
- [ ] Reverse engineering API "Trasparenza VM"

---

**Status**: ✅ DISCOVERY COMPLETATA (92% success rate)  
**Next**: 🚀 SPRINT 1 - Implementazione DrupalAlboScraper  
**Decisione Viareggio**: ⏸️ SKIP temporaneo, focus su 12 comuni accessibili
