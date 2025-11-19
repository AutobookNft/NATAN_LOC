# 🔍 ANALISI COMUNI TOSCANI - RISULTATI DETTAGLIATI

**Data analisi**: 13 novembre 2025  
**Comuni analizzati**: 13  
**URL testati**: 26  
**URL accessibili**: 8/26 (31%)

---

## 📊 RISULTATI ANALISI AUTOMATICA

### **Comuni Accessibili ✅**

#### **1. FIRENZE (FI)** - Pop: 382,258
- ✅ **URL 1**: https://accessoconcertificato.comune.fi.it/AOL/Affissione/ComuneFi/Page
  - **Platform**: Drupal
  - **Status**: Accessibile
  - **Note**: Sistema custom, albo pretorio HTML
  
- ✅ **URL 2**: https://accessoconcertificato.comune.fi.it/trasparenza-atti/
  - **Platform**: Unknown (custom API)
  - **Status**: Accessibile
  - **Note**: Sistema API REST per deliberazioni/determinazioni

**Valutazione**: 🏆 **ECCELLENTE** - Doppio sistema (HTML + API), già studiato

---

#### **2. AREZZO (AR)** - Pop: 99,543
- ✅ **URL**: https://www.comune.arezzo.it/albo-pretorio
  - **Platform**: Drupal
  - **Status**: Accessibile
  - **Note**: CMS Drupal standard

**Valutazione**: ✅ **BUONO** - Sistema standard, facile da scrappare

---

#### **3. SIENA (SI)** - Pop: 53,903
- ✅ **URL**: https://www.comune.siena.it/albo-pretorio
  - **Platform**: Drupal
  - **Status**: Accessibile
  - **Note**: CMS Drupal standard

**Valutazione**: ✅ **BUONO** - Sistema standard

---

#### **4. LUCCA (LU)** - Pop: 89,046
- ✅ **URL**: https://www.comune.lucca.it/albo-pretorio
  - **Platform**: WordPress
  - **Status**: Accessibile
  - **Note**: Plugin WordPress per albo pretorio

**Valutazione**: ✅ **BUONO** - WordPress con plugin dedicato

---

#### **5. PRATO (PO)** - Pop: 195,763
- ✅ **URL**: https://trasparenza.comune.prato.it/
  - **Platform**: Istituzionale (custom)
  - **Status**: Accessibile
  - **Note**: Portale trasparenza custom

**Valutazione**: ⚠️ **MEDIO** - Sistema custom, richiede analisi manuale

---

#### **6. PISA (PI)** - Pop: 90,745
- ✅ **URL**: https://albopretorio.comune.pisa.it/
  - **Platform**: Unknown
  - **Status**: Accessibile
  - **Note**: Subdomain dedicato

**Valutazione**: ⚠️ **DA VERIFICARE** - Piattaforma sconosciuta

---

#### **7. BAGNO A RIPOLI (FI)** - Pop: 26,035
- ✅ **URL**: https://trasparenza.comune.bagno-a-ripoli.fi.it/web/trasparenza/menu-albo-pretorio
  - **Platform**: Custom (JavaScript-heavy, possibile Halley/Civilia)
  - **Status**: Accessibile
  - **Note**: Portale trasparenza con albo integrato, richiede rendering JavaScript

**Valutazione**: ⚠️ **MEDIO-COMPLESSO** - Sito JS-heavy, serve Selenium/Playwright

---

### **Comuni NON Accessibili ❌**

#### **8. LIVORNO (LI)** - Pop: 157,139
- ❌ URL testati non funzionanti
- 🔍 **RICERCA MANUALE NECESSARIA**

#### **9. GROSSETO (GR)** - Pop: 82,284
- ❌ URL testati non funzionanti
- 🔍 **RICERCA MANUALE NECESSARIA**

#### **9. MASSA (MS)** - Pop: 68,511
- ❌ URL testati non funzionanti
- 🔍 **RICERCA MANUALE NECESSARIA**

#### **10. PISTOIA (PT)** - Pop: 90,363
- ❌ URL testati non funzionanti
- 🔍 **RICERCA MANUALE NECESSARIA**

#### **11. CARRARA (MS)** - Pop: 62,592
- ❌ URL testati non funzionanti
- 🔍 **RICERCA MANUALE NECESSARIA**

#### **12. VIAREGGIO (LU)** - Pop: 62,467
- ❌ URL testati non funzionanti
- 🔍 **RICERCA MANUALE NECESSARIA**

---

## 🏗️ PIATTAFORME RILEVATE

### **1. Drupal** - 3 comuni (Firenze, Arezzo, Siena)
**+ Bagno a Ripoli (base Drupal ma custom JS-heavy)
**Caratteristiche:**
- CMS open source diffuso nella PA italiana
- Struttura DOM prevedibile
- Pattern HTML simili
- Spesso con moduli per albo pretorio

**Difficoltà scraping**: 🟢 BASSA
**Generalizzabilità**: 🟢 ALTA

---

### **2. WordPress** - 1 comune (Lucca)
**Caratteristiche:**
- CMS molto diffuso
- Plugin dedicati per albo pretorio (es: "Albo Pretorio On Line")
- Struttura DOM varia in base al plugin

**Difficoltà scraping**: 🟡 MEDIA
**Generalizzabilità**: 🟡 MEDIA (dipende da plugin)

---

### **3. Custom/Istituzionale** - 2 comuni (Firenze API, Prato)
**Caratteristiche:**
- Sviluppi custom
- Può avere API REST (come Firenze)
- Struttura variabile

**Difficoltà scraping**: 🟡 MEDIA-ALTA
**Generalizzabilità**: 🔴 BASSA

---

### **4. JavaScript-Heavy Custom** - 1 comune (Bagno a Ripoli)
**Caratteristiche:**
- Rendering JavaScript necessario
- Possibile base Drupal con overlay custom
- Richiede Selenium/Playwright per scraping
- 149 script tags rilevati
- Contenuto dinamico caricato via JS

**Difficoltà scraping**: 🔴 ALTA (serve browser automation)
**Generalizzabilità**: 🔴 BASSA

---

### **5. Unknown** - 1 comune (Pisa)
**Caratteristiche:**
- Da analizzare manualmente
- Potrebbe essere Halley, Insiel, J-Iris o altro vendor

**Difficoltà scraping**: ❓ DA VALUTARE
**Generalizzabilità**: ❓ DA VALUTARE

---

## 🎯 PATTERN COMUNI RILEVATI

### **Layout Patterns**
- **Card Layout**: Comune (Firenze, altri Drupal)
- **Table Layout**: Presente in alcuni
- **List Layout**: Meno comune
- **Pagination**: Quasi sempre presente

### **Elementi DOM Ricorrenti**
```css
/* Selettori comuni per atti */
.card.concorso-card (Firenze)
.view-albo-pretorio (Drupal)
.atto, .delibera, .determina (generici)
```

### **Metadata Comuni**
- Numero atto / protocollo
- Data pubblicazione (inizio/fine)
- Oggetto / descrizione
- Tipo atto (delibera, determina, ordinanza, etc.)
- Allegati PDF

### **PDF Links Pattern**
- Quasi sempre presenti
- Formato: `/uploads/.../*.pdf`
- Count: 1-5 PDF per atto in media

---

## 🔍 RICERCA MANUALE URL CORRETTI

### **Metodo di Ricerca**
Per i comuni non accessibili, ricerca manuale con Google:
```
site:comune.[nome].it "albo pretorio"
"albo pretorio online" comune [nome]
```

### **URL Alternativi da Testare**

#### **LIVORNO**
- https://www.comune.livorno.it/ (homepage, cerca link albo)
- https://servizi.comune.livorno.it/
- Possibile vendor esterno (Halley, Insiel)

#### **GROSSETO**
- https://www.comune.grosseto.it/ (homepage)
- https://servizi.comune.grosseto.it/

#### **MASSA**
- https://www.comune.massa.it/ (possibile .it invece di .ms.it)
- https://servizi.comune.massa.it/

#### **PISTOIA**
- https://www.comune.pistoia.it/ (homepage)
- https://servizi.comune.pistoia.it/

#### **CARRARA**
- https://www.comune.carrara.ms.it/ (homepage, 404 su albo-pretorio)
- Possibile subdomain o vendor esterno

#### **VIAREGGIO**
- https://www.comune.viareggio.lu.it/ (homepage)
- https://servizi.comune.viareggio.lu.it/

---

## 📋 PROSSIMI STEP

### **Step 1: Ricerca Manuale URL Corretti** ⏳
Per i 6 comuni non accessibili:
1. Cercare su Google sito ufficiale
2. Navigare homepage → "Albo Pretorio"
3. Identificare URL reale e piattaforma
4. Aggiornare database comuni

### **Step 2: Analisi Approfondita Piattaforme Rilevate** ⏳
Per ogni piattaforma (Drupal, WordPress, Custom):
1. Analizzare struttura DOM dettagliata
2. Identificare selettori CSS/XPath affidabili
3. Testare paginazione e filtri
4. Verificare API disponibili (if any)
5. Studiare rate limiting e protezioni

### **Step 3: Identificazione Vendor Software** ⏳
Cercare vendor software più diffusi in Italia:
- **Halley** (Halley Informatica)
- **Insiel** (Insiel Mercato)
- **J-Iris** (J-Iris Sistemi)
- **AmtAB** (Amt-Ab.it)
- **Civilia** (Engineering)

### **Step 4: Espansione Analisi** ⏳
Aggiungere altri comuni toscani:
- Piccoli comuni (< 20k abitanti)
- Medi comuni (20k-50k abitanti)
- Verificare se vendor diffonde con dimensione comune

---

## 💡 INSIGHT PRELIMINARI

### **Osservazioni Chiave**

1. **Frammentazione Alta**: 
   - 4+ piattaforme diverse solo in 7 comuni accessibili
   - Ogni comune può avere soluzione custom
   - No standard nazionale evidente

2. **Drupal Diffuso**:
   - 3/7 comuni usano Drupal
   - Pattern simile, potenzialmente generalizzabile
   - Moduli albo pretorio condivisi?

3. **URL Non Standard**:
   - Solo 29% URL testati accessibili
   - Molti comuni usano subdomain dedicati
   - Pattern `/albo-pretorio` non universale

4. **Necessità Ricerca Manuale**:
   - Impossibile predire URL con certezza
   - Serve database URL mantenuto
   - Possibile integrazione API ANPR/ISTAT per URL ufficiali

### **Implicazioni per Sistema Multi-Tenant**

✅ **PRO**:
- Pattern DOM simili tra comuni stessa piattaforma
- MongoDB multi-tenant già pronto
- Chunking/embeddings già implementato

⚠️ **SFIDE**:
- Serve scraper per ogni piattaforma (Drupal, WordPress, Custom)
- Auto-detection piattaforma critico
- URL discovery/maintenance complesso
- Fallback strategy necessaria per DOM changes

---

## 🎯 RACCOMANDAZIONI ARCHITETTURA

### **1. Platform-Specific Scrapers**
```python
BaseAlboScraper (abstract)
├── DrupalAlboScraper (Firenze, Arezzo, Siena)
├── WordPressAlboScraper (Lucca)
├── HalleyScraper (TBD)
├── InsielScraper (TBD)
└── GenericAlboScraper (fallback)
```

### **2. Auto-Detection Engine**
- Analizza HTML/headers
- Rileva CMS/vendor
- Seleziona scraper appropriato
- Confidence scoring

### **3. Configuration Database**
```python
comune_config = {
    'nome': 'Firenze',
    'url': 'https://...',
    'platform': 'drupal',
    'scraper_class': 'DrupalAlboScraper',
    'selectors': {...},
    'rate_limit': 1.0,  # seconds between requests
    'last_scraped': '2025-11-13T10:00:00Z'
}
```

### **4. Robust Error Handling**
- Retry con exponential backoff
- Fallback a GenericScraper se platform-specific fallisce
- Alert system per scraping fallito
- Manual review queue

---

**Status**: ✅ Analisi automatica COMPLETATA  
**Next**: 🔍 Ricerca manuale URL + Analisi approfondita piattaforme
