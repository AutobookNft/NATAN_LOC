# ECO_DEBT_CORE_LIBRARY

**Version:** 0.1.0  
**Author:** FlorenceEGI – OS3 Framework  
**License:** MIT  
**Purpose:** Libreria universale per la misurazione del *debito ecologico digitale* di funzioni software, conforme al protocollo **EcoDebt Protocol (EDP)**.

---

## 🌱 OVERVIEW

`eco-debt-core` fornisce API, middleware e helper per calcolare, loggare e inviare stime di **energia consumata (kWh)** e **emissioni equivalenti (kg CO₂e)** prodotte da un’operazione software.

Può essere usata:
- come **libreria autonoma** (Node/TS o PHP)
- come **bridge per Ultra Log Manager (ULM)** di FlorenceEGI
- come **SDK per EcoDebt Protocol (EDP)** esterno

---

## ⚙️ ARCHITECTURE

```
eco-debt-core/
 ├── src/
 │   ├── EcoDebtClient.ts        → invio dati a ledger o API
 │   ├── Estimator.ts            → calcolo kWh / CO₂e
 │   ├── Logger.ts               → log locale + integrazione ULM
 │   ├── Middleware/
 │   │     ├── LaravelMiddleware.php
 │   │     └── NodeMiddleware.ts
 │   ├── Config/
 │   │     └── envmetrics.json   → coefficienti per funzione
 │   └── index.ts
 ├── tests/
 ├── README.md
 └── package.json
```

---

## 🧩 INSTALLATION

```bash
npm install eco-debt-core
# oppure per Laravel
composer require florenceegi/eco-debt-core
```

---

## 🔧 CONFIGURATION (`envmetrics.json`)

```json
{
  "defaults": {
    "E_NET": 0.06,
    "E_CPU": 0.05,
    "FACTOR_CO2E": 0.46
  },
  "functions": {
    "upload_media": {"kwh": 0.0007, "co2e": 0.0003},
    "mint_egi": {"kwh": 0.0011, "co2e": 0.0005},
    "ai_inference": {"kwh": 0.002, "co2e": 0.001}
  }
}
```

---

## 🧠 CORE API (TypeScript)

```ts
import { EcoDebtClient, estimate } from "eco-debt-core";

const metrics = estimate({
  bytes: 1024000,          // dimensione risorsa
  responseTime: 0.45       // secondi
});

await EcoDebtClient.log({
  function: "ai_inference",
  energy_kwh: metrics.kwh,
  debt_co2e: metrics.co2e,
  tags: { app: "FlorenceEGI" }
});
```

---

## 🧰 PHP / LARAVEL MIDDLEWARE

```php
use FlorenceEGI\EcoDebtCore\EcoDebt;

class EcoDebtMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        EcoDebt::log($request->route()->getName(), [
            'duration' => microtime(true) - LARAVEL_START,
            'energy_kwh' => 0.0007,
            'debt_co2e' => 0.0003
        ]);

        return $response;
    }
}
```

Registra il middleware in `app/Http/Kernel.php` sotto il gruppo `web`.

---

## 🧾 DATA MODEL

| Campo | Tipo | Descrizione |
|-------|------|-------------|
| `function` | string | Nome logico dell’operazione |
| `energy_kwh` | float | Energia stimata in kWh |
| `debt_co2e` | float | Emissioni in kg CO₂e |
| `compensated_co2e` | float | (opzionale) quantità compensata |
| `status` | string | `debt_open`, `neutralized`, `regenerative` |
| `tags` | object | Metadati liberi (lang, env, version) |
| `timestamp` | datetime | ISO-8601 |

---

## 🔗 API ENDPOINT (EcoDebt Protocol 1.0)

```http
POST /api/ecodebt/open
Content-Type: application/json
Authorization: Bearer <API_KEY>

{
  "function": "mint_egi",
  "energy_kwh": 0.0011,
  "debt_co2e": 0.0005,
  "tags": {"app": "FlorenceEGI","lang":"php"}
}
```

**Response**
```json
{"status":"logged","eco_ref":"ECO-2025-00412"}
```

---

## 🔒 GDPR + ULM INTEGRATION

Se presente il modulo ULM, la libreria aggiunge automaticamente un log di categoria `ECO_DEBT`:

```php
$this->ultraLogger->info('ECO_DEBT', [
   'function' => $fn,
   'energy_kwh' => $kwh,
   'debt_co2e' => $co2e,
   'status' => 'debt_open'
]);
```

I dati non contengono PII. AuditTrail registra l’azione come **Environmental Activity** con categoria `GDPRActivityCategory::ENV_IMPACT_LOG`.

---

## 🧮 FORMULA BASE

```
energy_kwh_total = (bytes / 1GB) * E_NET + (responseTime_sec * E_CPU / 3600)
debt_co2e = energy_kwh_total * FACTOR_CO2E
```

Parametri di default:
- `E_NET` = 0.06 kWh/GB  
- `E_CPU` = 0.05 kW  
- `FACTOR_CO2E` = 0.46 kg CO₂e/kWh (Italia)

---

## ✅ STATUS VALUES

| Stato | Significato |
|-------|--------------|
| `debt_open` | debito ecologico aperto, non compensato |
| `neutralized` | compensato tramite EPP |
| `regenerative` | oltre la compensazione (impatto positivo) |

---

## 🧭 EXTENSIONS

- **eco-debt-laravel** → bridge middleware Laravel  
- **eco-debt-node** → Express middleware  
- **eco-debt-analyzer** → CLI per batch esterni (EcoScanner)  
- **eco-debt-ulm** → plug-in per Ultra Log Manager  

---

## 🚀 ROADMAP

| Version | Feature | Status |
|----------|----------|---------|
| 0.1.0 | Calcolo base + log JSON | ✅ |
| 0.2.0 | Middleware Laravel / Node | 🚧 |
| 0.3.0 | API Client EDP 1.0 | 🚧 |
| 0.4.0 | Integrazione ULM Bridge | ⏳ |
| 1.0.0 | Notarizzazione Algorand + Audit EPP | 🔮 |

---

## 📚 LICENSE & ETHICS

Open Source (MIT).  
Uso esclusivamente per finalità di **autovalutazione ambientale volontaria**.  
Nessuna raccolta di dati personali o profilazione.  
Compatibile con Direttiva UE 2024/1083 (ESG Digital Accountability).

---

**FlorenceEGI / Frangette 2025**  
> “Ogni funzione che consuma energia genera un debito.  
>  Misurarlo è il primo atto di consapevolezza.”  
