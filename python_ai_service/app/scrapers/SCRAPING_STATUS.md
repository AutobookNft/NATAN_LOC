# STATO SCRAPING ALBO PRETORIO - 12 COMUNI TOSCANA

## ✅ FUNZIONANTI (1/12 = 8.3%)

### Firenze - 538,167 abitanti
- **Metodo**: API REST
- **Endpoint**: `https://accessoconcertificato.comune.fi.it/trasparenza-atti-cat/searchAtti`
- **Atti recuperati**: 415 delibere 2025
- **Implementazione**: `scripts/scrape_firenze_deliberazioni.py`
- **Status**: ✅ PRODUZIONE

---

## ❌ IMPOSSIBILI CON HTTP STATICO (2/12 = 16.7%)

### Arezzo - 97,303 abitanti
- **Problema**: Form con JavaScript obbligatorio
- **URL testato**: `https://servizionline.comune.arezzo.it/jattipubblicazioni/`
- **Analisi**: POST con tutti i parametri corretti (organo, annoAtto, dadata, adata, ordinamento=1, sort=-, resultXPag=50) ritorna sempre il form
- **Test Playwright**: 1 delibera (keyword nel DOM) ma nessun link risultati
- **Conclusione**: Sistema richiede esecuzione JavaScript completa, probabilmente rendering lato client con AJAX
- **TAG**: **REQUIRES_BROWSER_AUTOMATION**

### Siena - 53,903 abitanti
- **Problema**: Albo non trovato/accessibile
- **URL testato**: `https://www.comune.siena.it/albo-pretorio`
- **Analisi**: Pagina esiste ma 0 link ad atti, 0 iframe, 0 contenuti albo
- **Test Playwright**: 0 atti anche con lazy loading
- **Conclusione**: Albo non accessibile via web o URL sconosciuto
- **TAG**: **ALBO_NOT_FOUND**

---

## 🔒 CLOUDFLARE PROTECTED (4/12 = 33.3%)

### Livorno - 154,624 abitanti
- **Piattaforma**: TrasparenzaVM
- **URL**: `https://trasparenza.comune.livorno.it`
- **Errore**: Timeout / Cloudflare challenge
- **TAG**: **CLOUDFLARE**

### Grosseto - 81,969 abitanti
- **Piattaforma**: TrasparenzaVM
- **URL**: `https://trasparenza.comune.grosseto.it`
- **Errore**: Timeout / Cloudflare challenge
- **TAG**: **CLOUDFLARE**

### Pistoia - 89,864 abitanti
- **Piattaforma**: TrasparenzaVM
- **URL**: `https://trasparenza.comune.pistoia.it`
- **Errore**: Timeout / Cloudflare challenge
- **TAG**: **CLOUDFLARE**

### Carrara - 60,833 abitanti
- **Piattaforma**: TrasparenzaVM
- **URL**: `https://trasparenza.comune.carrara.ms.it`
- **Errore**: Timeout / Cloudflare challenge
- **TAG**: **CLOUDFLARE**

---

## 🔄 INVESTIGATI - RICHIEDONO JAVASCRIPT (5/12 = 41.7%)

### Prato - 195,640 abitanti
- **URL base**: `https://www.comune.prato.it`
- **Analisi**: Nessun link albo trovato, detection TrasparenzaVM
- **API**: `/rest/albo` ritorna HTML non JSON
- **Piattaforma**: TrasparenzaVM (possibile)
- **TAG**: **ALBO_NOT_FOUND** (necessita ricerca manuale approfondita)

### Pisa - 89,158 abitanti
- **URL albo**: `https://albopretorio.comune.pisa.it/`
- **Analisi**: Form POST (action="#"), 0 atti con Playwright, nessuna tabella risultati
- **Piattaforma**: TrasparenzaVM
- **Problema**: Form interattivo JavaScript-based senza risultati accessibili via DOM
- **TAG**: **REQUIRES_BROWSER_AUTOMATION_ADVANCED**

### Lucca - 88,824 abitanti
- **URL albo**: `https://lucca.trasparenza-valutazione-merito.it/web/trasparenza/albo-pretorio`
- **Analisi**: 0 atti visibili con Playwright
- **Piattaforma**: TrasparenzaVM
- **Problema**: Potenziale Cloudflare o rendering dinamico AJAX
- **TAG**: **REQUIRES_BROWSER_AUTOMATION** (TrasparenzaVM = Cloudflare protection storica)

### Bagno a Ripoli - 25,095 abitanti
- **URL albo**: `https://trasparenza.comune.bagno-a-ripoli.fi.it/web/trasparenza/menu-albo-pretorio`
- **Analisi**: Form POST (action="#"), 0 atti con Playwright
- **Piattaforma**: Drupal
- **Problema**: Form interattivo senza risultati accessibili
- **TAG**: **REQUIRES_BROWSER_AUTOMATION_ADVANCED**

### Massa - 66,294 abitanti
- **URL base**: `https://www.comune.massa.ms.it`
- **Analisi**: Nessun link albo trovato
- **Piattaforma**: Drupal
- **TAG**: **ALBO_NOT_FOUND**

---

## 📊 STATISTICHE FINALI

| Status | Comuni | Popolazione | % Pop |
|--------|--------|-------------|-------|
| ✅ **Funzionanti** | **1** | **538,167** | **34.9%** |
| ❌ JavaScript obbligatorio | 7 | 616,217 | 40.0% |
| 🔒 Cloudflare protected | 4 | 387,290 | 25.1% |
| **TOTALE** | **12** | **1,541,674** | **100%** |

### Breakdown dettagliato:
- **HTTP Static Scraping OK**: 1 comune (Firenze - API REST)
- **Form POST non funzionanti**: 2 comuni (Arezzo, Pisa)
- **TrasparenzaVM + Cloudflare**: 4 comuni (Livorno, Grosseto, Pistoia, Carrara)
- **TrasparenzaVM (probabile Cloudflare)**: 1 comune (Lucca)
- **Albo non trovato**: 3 comuni (Siena, Prato, Massa)
- **Form JavaScript avanzato**: 1 comune (Bagno a Ripoli)

---

## 🎯 CONCLUSIONI E STRATEGIA FINALE

### 🏆 RISULTATO ATTUALE
**1 comune su 12 funzionante (8.3%)** con scraping HTTP statico
- **Firenze**: 415 delibere 2025 recuperate via API REST ✅
- **Coverage popolazione**: 34.9% (538k su 1.5M abitanti)

### 🚧 BLOCCHI PRINCIPALI
1. **Cloudflare Protection**: 4 comuni TrasparenzaVM bloccati completamente
2. **Form POST JavaScript**: Sistemi interattivi che richiedono browser completo (Arezzo, Pisa, Bagno a Ripoli)
3. **Albo non accessibili**: 3 comuni senza link/URL albo trovabili (Siena, Prato, Massa)

### 💡 OPZIONI STRATEGICHE

#### Opzione A: **Accontentarsi di Firenze (CONSERVATIVA)**
- **PRO**: Funziona bene, 415 atti, stabile
- **CONTRO**: Solo 35% popolazione coperta
- **Effort**: 0 ore aggiuntive
- **Raccomandazione**: Se obiettivo è proof-of-concept

#### Opzione B: **Implementare Playwright per form comuni grandi (BILANCIATA)**
- **Target**: Pisa (89k), Lucca (88k) - TrasparenzaVM con form
- **Metodo**: Playwright stealth mode + interazione form completa
- **PRO**: +177k abitanti = 46.4% coverage totale
- **CONTRO**: Overhead browser, più fragile
- **Effort**: 4-6 ore (2-3 ore per comune)
- **Raccomandazione**: **SE vuoi coverage >40%**

#### Opzione C: **Ricerca manuale URL albo per comuni senza link (ESPLORATIVA)**
- **Target**: Prato (195k), Massa (66k)
- **Metodo**: Navigazione manuale sito, Google, contatto URP
- **PRO**: Potenzialmente +261k abitanti
- **CONTRO**: Tempo imprevedibile, URL potrebbero non esistere
- **Effort**: 1-3 ore per comune (imprevedibile)

#### Opzione D: **Espandere a più comuni Toscana (SCALARE)**
- **Logica**: 12 comuni testati = sample, molti altri comuni potrebbero avere API come Firenze
- **Target**: Top 20-30 comuni Toscana per popolazione
- **PRO**: Maggiore probabilità di trovare altri con API/HTML statico
- **CONTRO**: Effort significativo
- **Effort**: 8-15 ore
- **Raccomandazione**: SE obiettivo è sistema nazionale

### 🎲 RACCOMANDAZIONE FINALE

**Se obiettivo = Coverage rapida Toscana**:
→ **Opzione B** (Playwright per Pisa + Lucca) = 46% coverage con 4-6 ore

**Se obiettivo = Scalabilità nazionale**:
→ **Opzione D** (espandere sample) + focus su comuni con API REST trovabili

**Se obiettivo = Proof-of-concept minimo**:
→ **Opzione A** (solo Firenze) = già funzionante

---

### 📋 PROSSIMI STEP IMMEDIATI (se Opzione B)

1. **Implementare PisaAlboScraper** (Playwright)
   - URL: `https://albopretorio.comune.pisa.it/`
   - Form POST interattivo
   - Parsing risultati AJAX
   
2. **Implementare LuccaAlboScraper** (Playwright)
   - URL: `https://lucca.trasparenza-valutazione-merito.it/web/trasparenza/albo-pretorio`
   - TrasparenzaVM (possibile Cloudflare - testare stealth)
   
3. **Test recovery counts** per validare funzionamento

4. **Integrazione MongoDB** per salvare atti recuperati

---

**Ultimo aggiornamento**: 2025-11-13 (investigazione completa 12 comuni)
**Prossima decisione**: Scegliere Opzione A/B/C/D in base a obiettivi progetto
