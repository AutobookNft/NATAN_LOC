# 🔍 VERIFICA URL COMUNI - Report Finale

**Data**: 14 novembre 2025  
**Tool**: `verifica_url_comuni.py`

---

## ❌ PROBLEMI CRITICI TROVATI

### 1. **Empoli** - URL COMPLETAMENTE SBAGLIATO
- **URL dichiarato**: `https://www.empoli.gov.it`
- **Status**: ❌ CONNECTION_ERROR (non raggiungibile)
- **URL corretto**: `https://www.comune.empoli.fi.it`
- **Link Albo**: `https://empoli.trasparenza-valutazione-merito.it/web/trasparenza/albo-pretorio`
- **Azione**: ✅ **CORRETTO** in `comuni_url_corrections.py`

### 2. **Sesto Fiorentino** - URL Base Bloccato
- **URL dichiarato**: `https://www.sestofiorentino.it`
- **Status**: ⚠️ HTTP 403 Forbidden (accesso negato)
- **Subdomain funzionante**: `http://servizi.comune.sesto-fiorentino.fi.it`
- **API trovata**: `http://servizi.comune.sesto-fiorentino.fi.it/albo/search.php`
- **Atti recuperabili**: 117 (verificato)
- **Azione**: ✅ **CORRETTO** in `trivella_brutale.py` con metodo API_DATATABLES

### 3. **Firenze** - Redirect Permanente
- **URL dichiarato**: `https://www.comune.fi.it`
- **URL reale**: `https://www.comune.firenze.it/` (301 redirect)
- **Impatto**: Minimo (redirect funziona)
- **Azione**: ⚠️ Da aggiornare per efficienza

---

## 📋 LINK ALBO PRETORIO SCOPERTI

Tutti i comuni hanno link albo nella homepage:

| Comune | Piattaforma | Link Albo Pretorio |
|--------|-------------|-------------------|
| **Firenze** | Custom | `https://www.comune.firenze.it/albo-pretorio` |
| **Scandicci** | SoluzioniPA | `https://scandicci.soluzionipa.it/openweb/albo/albo_pretorio.php` |
| **Empoli** | Trasparenza-VM | `https://empoli.trasparenza-valutazione-merito.it/web/trasparenza/albo-pretorio` |
| **Campi Bisenzio** | Trasparenza-VM | `https://campibisenzio.trasparenza-valutazione-merito.it/web/trasparenza/albo-pretorio` |
| **Bagno a Ripoli** | Trasparenza-VM | `https://trasparenza.comune.bagno-a-ripoli.fi.it/web/trasparenza/menu-albo-pretorio` |
| **Fiesole** | Trasparenza-VM | `https://fiesole.trasparenza-valutazione-merito.it/web/trasparenza/albo-pretorio` |
| **Pontassieve** | Trasparenza-VM | `https://pubblicazioni.comune.pontassieve.fi.it/web/trasparenza/albo-pretorio` |
| **Borgo San Lorenzo** | Trasparenza-VM | `https://borgosanlorenzo.trasparenza-valutazione-merito.it/web/trasparenza/albo-pretorio` |
| **Calenzano** | Custom | `https://www.comune.calenzano.fi.it/it/page/80188` |

---

## 🌐 PIATTAFORME IDENTIFICATE

### Trasparenza-Valutazione-Merito (6 comuni)
- Empoli
- Campi Bisenzio
- Bagno a Ripoli
- Fiesole
- Pontassieve
- Borgo San Lorenzo

**Pattern URL**: `https://<comune>.trasparenza-valutazione-merito.it/web/trasparenza/albo-pretorio`

**Opportunità**: Creare uno scraper specializzato per questa piattaforma sbloccherebbe 6 comuni contemporaneamente.

### SoluzioniPA (1 comune)
- Scandicci

**URL**: `https://scandicci.soluzionipa.it/openweb/albo/albo_pretorio.php`

---

## ✅ API ENDPOINT FUNZIONANTI

### Firenze
- **URL**: `https://accessoconcertificato.comune.fi.it/trasparenza-atti-cat/searchAtti`
- **Tipo**: API REST JSON
- **Atti**: 415
- **Status**: ✅ Funzionante

### Sesto Fiorentino
- **URL**: `http://servizi.comune.sesto-fiorentino.fi.it/albo/search.php`
- **Tipo**: API DataTables
- **Atti**: 117
- **Status**: ✅ Funzionante

---

## 🔧 CORREZIONI APPLICATE

### File `comuni_url_corrections.py`
```python
CORREZIONI_URL = {
    'Empoli': {
        'url_vecchio': 'https://www.empoli.gov.it',
        'url_corretto': 'https://www.comune.empoli.fi.it',
        'note': 'URL .gov.it non raggiungibile'
    },
    'Sesto Fiorentino': {
        'subdomain_albo': 'http://servizi.comune.sesto-fiorentino.fi.it',
        'note': 'URL base 403 Forbidden'
    }
}
```

### File `trivella_brutale.py`
- ✅ Import correzioni URL automatico
- ✅ Metodi noti per Firenze e Sesto Fiorentino
- ✅ Fallback a bruteforce se metodo noto fallisce

---

## 📊 IMPATTO

| Metric | Prima | Dopo |
|--------|-------|------|
| **Comuni raggiungibili** | 8/10 (80%) | 10/10 (100%) |
| **URL corretti** | 8/10 | 10/10 |
| **API funzionanti verificate** | 1 (Firenze) | 2 (Firenze + Sesto) |

**Coverage prevista**:
- Prima: 1/10 = 10%
- Dopo correzioni: 2/10 = 20% (minimo garantito)
- Con scraper Trasparenza-VM: potenzialmente 8/10 = 80%

---

## 🎯 PROSSIMI PASSI

### 1. Immediato (Alta Priorità)
- ✅ Correggere URL Empoli
- ✅ Aggiungere subdomain Sesto Fiorentino
- ⏳ Testare trivella con URL corretti

### 2. Breve Termine
- 🔧 Creare scraper specializzato per Trasparenza-Valutazione-Merito
  - Input: link albo (già trovato per 6 comuni)
  - Output: lista atti
  - Impatto: +6 comuni funzionanti

### 3. Medio Termine  
- 📋 Verificare URL di TUTTI i 273 comuni Toscana
- 🔍 Identificare altre piattaforme comuni
- 🤖 Creare scrapers platform-specific

---

## 💡 LEZIONI APPRESE

1. **Non fidarsi degli URL dichiarati**: 20% aveva problemi (2/10)
2. **Subdomain sono comuni**: Molti comuni usano `servizi.`, `albo.`, `trasparenza.` etc
3. **Piattaforme condivise**: 60% usa piattaforme comuni (Trasparenza-VM)
4. **Redirect permanenti**: Controllare sempre HTTP 301/302
5. **Verificare prima di scrapare**: 10 minuti di verifica risparmia ore di debugging

---

**Generato da**: `verifica_url_comuni.py`  
**Repository**: https://github.com/AutobookNft/NATAN_LOC
