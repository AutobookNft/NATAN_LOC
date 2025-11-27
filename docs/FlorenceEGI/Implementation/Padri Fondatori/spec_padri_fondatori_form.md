**Specifica Tecnica: Form "Issue Certificate" Padri Fondatori**

**Obiettivo**
Realizzare un form web GDPR-compliant per l'emissione live (in sala) di certificati digitali (ASA Algorand) e generazione immediata di PDF, con invio via email, log su database e gestione manuale del prisma fisico.

---

## 1. Architettura Generale

- **Frontend**: pagina unica (HTML + TypeScript)
- **Backend**: Laravel (PHP 8+)
- **Database**: MySQL (o MariaDB)
- **AlgorandService**: client Algorand (PHP o microservizio Node.js)
- **PDF Generator**: Dompdf o Snappy (wkhtmltopdf)
- **Email**: Mailer (Mailgun/Sendgrid)

---

## 2. File di Configurazione

**Percorso**: `config/founders.php` o file JSON in `storage/app/`

```php
return [
  'roundName'             => 'PadriFondatori1',
  'totalTokens'           => 40,
  'priceEur'              => 250.00,
  'metadataTemplateUrl'   => 'ipfs://Qm.../founders/{index}.json',
  'certificateTemplate'   => resource_path('views/certificate-tpl.html'),
  'treasuryAccount'       => env('ALGOTREASURY_ADDRESS'),
];
```

- `{index}` è placeholder per il numero progressivo del token (1–40).

---

## 3. Database

### Tabella `founder_certificates`
```sql
CREATE TABLE founder_certificates (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  `index` INT UNIQUE NOT NULL,
  asa_id VARCHAR(20) UNIQUE NOT NULL,
  tx_id VARCHAR(60) UNIQUE NOT NULL,
  investor_name VARCHAR(200) NOT NULL,
  investor_email VARCHAR(200) NOT NULL,
  issued_at TIMESTAMP NOT NULL,
  prisma_ordered BOOLEAN DEFAULT FALSE,
  prisma_paid BOOLEAN DEFAULT FALSE,
  prisma_shipped BOOLEAN DEFAULT FALSE,
  tracking_code VARCHAR(100) NULL,
  pdf_path VARCHAR(255) NOT NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

### Modello Eloquent
```php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FounderCertificate extends Model {
  protected $fillable = [
    'index','asa_id','tx_id','investor_name',
    'investor_email','issued_at','prisma_ordered',
    'prisma_paid','prisma_shipped','tracking_code','pdf_path'
  ];
  protected $casts = [
    'issued_at'=>'datetime',
    'prisma_ordered'=>'boolean',
    'prisma_paid'=>'boolean',
    'prisma_shipped'=>'boolean'
  ];
}
```

---

## 4. API Endpoint

### `POST /api/founders/issue`

- **Input (JSON o form-data)**:
  - `name` (string, required)
  - `email` (string, required, email)
  - `wallet` (string, nullable, valid Algorand address)

- **Validazione**:
  ```php
  $data = $request->validate([
    'name'=>'required|string|max:200',
    'email'=>'required|email|max:200',
    'wallet'=>'nullable|string|algorand_address',
  ]);
  ```

- **Processo**:
  1. Calcolare `nextIndex` = min(1..totalTokens non ancora in DB).
  2. Chiamare `AlgorandService::mintFounderToken($nextIndex)`:
     - crea ASA 1/1 con metadata URL
     - trasferisce 1 unità a `treasuryAccount`
     - restituisce `[asaId, txId]`
  3. Se `wallet` fornito, chiamare `transferAsset(treasury, wallet, asaId, 1)` e marcare trasferimento.
  4. Generare PDF:
     - Caricare view `certificate-tpl` (HTML) con variabili:
       `name, asaId, txId, issued_at, roundName`.
     - Salvare pdf in `storage/app/pdfs/cert_{index}.pdf`.
  5. Inviare email:
     - A `email`
     - Oggetto: "Il tuo Certificato FlorenceEGI"
     - View email: `emails.founder-certificate`
     - Allegato: pdf generato
  6. Salvare record in DB `founder_certificates`.

- **Output (JSON)**:
  ```json
  {
    "asa_id": "12345678",
    "tx_id": "ABCDEF...",
    "pdf_url": "/storage/pdfs/cert_7.pdf",
    "transferred": true|false
  }
  ```
- **Sicurezza**: CSRF, rate limiting, HTTPS.

---

## 5. GDPR Compliance

1. **Informativa breve** accanto al form: descrizione breve del trattamento.
2. **Privacy Policy** linkata, con:
   - Finalità: emissione certificato, invio email, gestione spedizione.
   - Base giuridica: esecuzione di contratto.
   - Diritti: accesso, rettifica, cancellazione, opposizione.
3. **HTTPS** sul sito.
4. **DPA** con Mail provider.
5. **Retention**: conservazione dati per max 5 anni, poi cancellazione.

---

## 6. Flusso Prisma (Manuale)

- Usare tabella `founder_certificates` per tracciare stati: `prisma_ordered`, `prisma_paid`, `prisma_shipped`, `tracking_code`.
- Esempi di query per aggiornare batch:
  ```php
  FounderCertificate::where('prisma_ordered', false)
    ->limit(30)
    ->update(['prisma_ordered' => true]);
  ```

---

**Fine della Specifica**

Si prega di implementare esattamente quanto sopra e segnalare eventuali dubbi o esigenze aggiuntive. Buon lavoro! GDPR Compliance GDPR Compliance

1. **Informativa breve** accanto al form: descrizione breve del trattamento.
2. **Privacy Policy** linkata, con:
   - Finalità: emissione certificato, invio email, gestione spedizione.
   - Base giuridica: esecuzione di contratto.
   - Diritti: accesso, rettifica, cancellazione, opposizione.
3. **HTTPS** sul sito.
4. **DPA** con Mail provider.
5. **Retention**: conservazione dati per max 5 anni, poi cancellazione.

---

## 8. Flusso Prisma (Manuale)

- Usare tabella `founder_certificates` per tracciare stati: `prisma_ordered`, `prisma_paid`, `prisma_shipped`, `tracking_code`.
- Esempi di query per aggiornare batch:
  ```php
  FounderCertificate::where('prisma_ordered', false)
    ->limit(30)
    ->update(['prisma_ordered' => true]);
  ```

---

**Fine della Specifica**

Si prega di implementare esattamente quanto sopra e segnalare eventuali dubbi o esigenze aggiuntive. Buon lavoro!")}

