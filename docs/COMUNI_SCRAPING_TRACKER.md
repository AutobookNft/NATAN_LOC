# 📊 Comuni Scraping Tracker - Sistema di Tracking Comuni Scrapati

**Versione**: 1.0.0  
**Data**: 2025-01-28  
**Autore**: Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici

---

## 🎯 Scopo

Sistema di tracking per evitare duplicati e generare report per comuni già scrapati.

**Problema risolto:**
- Quando si rilancia lo scraper generale, riparte dai comuni non ancora scrapati
- Genera report per comuni già scrapati senza doverli riscrapare
- Mantiene mappa completa di tutti i comuni processati

---

## 📋 Componenti

### **1. ComuniScrapingTracker** (`comuni_tracker.py`)

Classe principale per tracking comuni scrapati in MongoDB.

**Collection MongoDB:** `scraped_comuni`

**Struttura documento:**
```json
{
    "comune_slug": "firenze",
    "comune_nome": "Firenze",
    "scraped_at": "2025-01-28T10:30:00Z",
    "atti_estratti": 2275,
    "compliance_score": 0.85,
    "violations_count": 2,
    "metodo_usato": "API REST Firenze (diretta)",
    "json_backup_path": "storage/testing/compliance_scanner/json/atti_firenze_20250128_103000.json",
    "pdf_report_path": "storage/testing/compliance_scanner/reports/firenze_20250128_103000.pdf",
    "landing_page_url": "https://...",
    "email_sent": true,
    "tenant_id": 1,
    "status": "completed"
}
```

**Metodi principali:**

#### `mark_comune_scraped()`
Salva/aggiorna tracking di un comune dopo scraping completato.

**Parametri:**
- `comune_slug`: Slug del comune (es: "firenze")
- `atti_estratti`: Numero atti estratti
- `compliance_score`: Score conformità (0-1)
- `violations_count`: Numero violazioni
- `metodo_usato`: Metodo scraping usato
- `json_backup_path`: Path JSON backup
- `pdf_report_path`: Path report PDF
- `landing_page_url`: URL landing page
- `email_sent`: Se email inviata
- `tenant_id`: ID tenant
- `status`: Status (completed, failed, partial)

**Returns:** `True` se salvato con successo

---

#### `is_comune_scraped()`
Verifica se un comune è già stato scrapato.

**Parametri:**
- `comune_slug`: Slug del comune
- `tenant_id`: ID tenant (opzionale)

**Returns:** `True` se già scrapato, `False` altrimenti

---

#### `get_scraped_comuni()`
Ottiene lista di tutti i comuni già scrapati.

**Parametri:**
- `tenant_id`: Filtra per tenant (opzionale)
- `status`: Filtra per status (completed, failed, partial) (opzionale)

**Returns:** Lista di documenti comuni scrapati

---

#### `get_unscraped_comuni()`
Filtra lista comuni restituendo solo quelli non ancora scrapati.

**Parametri:**
- `comuni_list`: Lista di slug comuni da verificare
- `tenant_id`: ID tenant (opzionale)

**Returns:** Lista di slug comuni non ancora scrapati

---

#### `get_comune_info()`
Ottiene informazioni dettagliate su un comune già scrapato.

**Parametri:**
- `comune_slug`: Slug del comune
- `tenant_id`: ID tenant (opzionale)

**Returns:** Documento con info comune o `None` se non trovato

---

#### `get_comuni_stats()`
Ottiene statistiche aggregate sui comuni scrapati.

**Parametri:**
- `tenant_id`: ID tenant per filtrare (opzionale)

**Returns:** Dict con statistiche:
```json
{
    "total_scraped": 2,
    "total_atti": 2402,
    "total_violations": 3,
    "comuni_completed": 2,
    "comuni_partial": 0,
    "comuni_failed": 0,
    "avg_compliance_score": 0.85
}
```

---

### **2. Integrazione nel Compliance Scanner**

Il `AlboPretorioComplianceScanner` ora salva automaticamente il tracking dopo ogni scraping completato (non dry-run).

**Quando viene salvato:**
- Dopo scraping completato con successo (`atti_count > 0`)
- Solo se `dry_run=False`
- Status determinato automaticamente:
  - `completed`: atti estratti e nessuna violazione
  - `partial`: atti estratti ma con violazioni
  - `failed`: nessun atto estratto

---

### **3. Endpoint FastAPI** (`/api/v1/admin/compliance-scanner/...`)

#### **GET `/admin/compliance-scanner/comuni-scrapati`**
Ottiene lista di tutti i comuni già scrapati.

**Query params:**
- `tenant_id`: Filtra per tenant (opzionale)
- `status`: Filtra per status (completed, failed, partial) (opzionale)

**Response:**
```json
{
    "total": 2,
    "comuni": [
        {
            "comune_slug": "firenze",
            "comune_nome": "Firenze",
            "scraped_at": "2025-01-28T10:30:00Z",
            "atti_estratti": 2275,
            "compliance_score": 0.85,
            ...
        }
    ]
}
```

---

#### **GET `/admin/compliance-scanner/comuni-non-scrapati`**
Ottiene lista di comuni non ancora scrapati dalla lista standard.

**Query params:**
- `tenant_id`: Filtra per tenant (opzionale)

**Response:**
```json
{
    "total": 10,
    "comuni": ["empoli", "pisa", "prato", ...]
}
```

---

#### **GET `/admin/compliance-scanner/stats`**
Ottiene statistiche aggregate sui comuni scrapati.

**Query params:**
- `tenant_id`: Filtra per tenant (opzionale)

**Response:**
```json
{
    "total_scraped": 2,
    "total_atti": 2402,
    "total_violations": 3,
    "avg_compliance_score": 0.85,
    ...
}
```

---

#### **POST `/admin/compliance-scanner/scrape-all`**
Scrapa tutti i comuni standard, saltando quelli già scrapati se richiesto.

**Body params:**
- `skip_scraped`: Se `True`, salta comuni già scrapati (default: `True`)
- `tenant_id`: ID tenant (opzionale)
- `dry_run`: Se `True`, solo dry-run senza estrarre atti (default: `False`)

**Response:**
```json
{
    "total": 10,
    "scraped": 8,
    "failed": 2,
    "results": [
        {
            "comune_slug": "empoli",
            "status": "success",
            "atti_estratti": 150,
            "compliance_score": 80.0,
            "violations_count": 1
        }
    ],
    "skipped_comuni": [...]
}
```

---

#### **POST `/admin/compliance-scanner/generate-reports`**
Genera report PDF per tutti i comuni già scrapati che non hanno ancora report.

**Query params:**
- `tenant_id`: Filtra per tenant (opzionale)

**Response:**
```json
{
    "generated": 5,
    "failed": 0,
    "reports": [
        {
            "comune_slug": "firenze",
            "pdf_path": "storage/testing/compliance_scanner/reports/firenze_20250128_103000.pdf"
        }
    ],
    "errors": []
}
```

---

## 🔄 Flusso Operativo

### **Scenario 1: Scraping iniziale**
```
1. Utente chiama POST /admin/compliance-scan/firenze
2. Compliance Scanner scrapa Firenze
3. Dopo scraping completato, salva tracking in MongoDB
4. Tracking contiene: atti_estratti, compliance_score, metodo_usato, etc.
```

### **Scenario 2: Rilancio scraper generale**
```
1. Utente chiama POST /admin/compliance-scanner/scrape-all?skip_scraped=true
2. Sistema ottiene lista comuni standard
3. Filtra comuni già scrapati usando get_unscraped_comuni()
4. Scrapa solo comuni non ancora processati
5. Salva tracking per ogni nuovo comune scrapato
```

### **Scenario 3: Generazione report per comuni già scrapati**
```
1. Utente chiama POST /admin/compliance-scanner/generate-reports
2. Sistema ottiene tutti i comuni scrapati con status="completed"
3. Per ogni comune senza PDF report:
   - Ricrea ComplianceReport dal tracking
   - Genera PDF usando ReportGenerator
   - Aggiorna tracking con pdf_report_path
```

---

## 📊 Indici MongoDB

Il tracker crea automaticamente indici per performance:

```javascript
// Indice unico su comune_slug + tenant_id (evita duplicati)
db.scraped_comuni.createIndex(
    { "comune_slug": 1, "tenant_id": 1 }, 
    { unique: true }
)

// Indice su scraped_at per ordinamento
db.scraped_comuni.createIndex({ "scraped_at": -1 })

// Indice su status per filtri
db.scraped_comuni.createIndex({ "status": 1 })
```

---

## ✅ Vantaggi

1. **Evita duplicati**: Non riscrapa comuni già processati
2. **Generazione report**: Genera report per comuni già scrapati senza riscrapare
3. **Statistiche**: Statistiche aggregate su tutti i comuni processati
4. **Multi-tenant**: Supporto completo multi-tenant con isolamento dati
5. **Persistenza**: Salvataggio in MongoDB per persistenza e query avanzate

---

## 🚀 Esempi d'Uso

### **Python: Verifica se comune già scrapato**
```python
from app.services.compliance_scanner.comuni_tracker import ComuniScrapingTracker

# Verifica se Firenze è già stato scrapato
if ComuniScrapingTracker.is_comune_scraped("firenze"):
    print("Firenze già scrapato!")
```

### **Python: Ottieni comuni non scrapati**
```python
comuni_standard = ["firenze", "empoli", "pisa", "prato"]
unscraped = ComuniScrapingTracker.get_unscraped_comuni(comuni_standard)
print(f"Comuni da scrapare: {unscraped}")
```

### **Python: Ottieni statistiche**
```python
stats = ComuniScrapingTracker.get_comuni_stats()
print(f"Totale comuni scrapati: {stats['total_scraped']}")
print(f"Totale atti estratti: {stats['total_atti']}")
print(f"Score medio conformità: {stats['avg_compliance_score']}")
```

### **cURL: Scrapa tutti i comuni non scrapati**
```bash
curl -X POST "http://localhost:8001/api/v1/admin/compliance-scanner/scrape-all?skip_scraped=true" \
  -H "Authorization: Bearer admin-token"
```

### **cURL: Ottieni lista comuni scrapati**
```bash
curl "http://localhost:8001/api/v1/admin/compliance-scanner/comuni-scrapati" \
  -H "Authorization: Bearer admin-token"
```

### **cURL: Genera report per comuni già scrapati**
```bash
curl -X POST "http://localhost:8001/api/v1/admin/compliance-scanner/generate-reports" \
  -H "Authorization: Bearer admin-token"
```

---

## 📝 Note Implementative

1. **Fallback JSON**: Se MongoDB non disponibile, il tracking non viene salvato (ma non blocca lo scraping)
2. **Update vs Insert**: Usa `update_one` con `upsert` per aggiornare comuni già tracciati
3. **Status automatico**: Status determinato automaticamente in base a atti estratti e violazioni
4. **Multi-tenant**: Ogni tenant ha isolamento completo dei dati di tracking

---

**Versione**: 1.0.0  
**Status**: ✅ PRODUCTION-READY  
**Ultimo Aggiornamento**: 2025-01-28

