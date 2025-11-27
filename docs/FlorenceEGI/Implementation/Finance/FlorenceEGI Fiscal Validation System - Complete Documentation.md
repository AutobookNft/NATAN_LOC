# 📋 **FlorenceEGI Fiscal Validation System - Complete Documentation**

_Enterprise-Grade Fiscal Validation for Global MVP Markets_  
**Version:** 1.0.0 - OS1 Compliant  
**Author:** Padmin D. Curtis (AI Partner OS1-Compliant)  
**Date:** 2025-06-06  
**Deadline:** FlorenceEGI MVP - 30 June 2025

---

## **Abstract**

Il **Fiscal Validation System** di FlorenceEGI è un sistema modulare di validazione di codici fiscali e partite IVA progettato per supportare i **6 mercati MVP** (Italia, Portogallo, Francia, Spagna, Inghilterra, Germania) con precisione enterprise-grade e compliance locale completa.

### **Integrazione con User Domains System**

Il sistema si integra perfettamente nel **User Domains System** come layer di validazione critico per il **Personal Data Domain**, fornendo:

- **Validazione Real-time:** Durante l'inserimento dei dati fiscali nel modulo Personal Data
- **Business Logic Validation:** Verifica coerenza tra tipo business dichiarato e codice fiscale
- **UEM Integration:** Gestione errori unificata con il sistema Ultra Error Manager
- **i18n Compliance:** Messaggi di errore localizzati usando le translation keys del sistema
- **GDPR Compliance:** Validazione locale senza chiamate API esterne per protezione privacy

Il sistema opera come **dependency** del `PersonalDataController` e viene utilizzato attraverso il `FiscalValidatorFactory` per selezionare il validator appropriato basato sul paese dell'utente, garantendo validazione accurata per ogni mercato MVP.

---

## **🏗️ Architettura del Sistema**

### **Pattern Factory OS1-Compliant**

```php
// Factory Pattern per selezione validator
$validator = FiscalValidatorFactory::create($countryCode);
$result = $validator->validateTaxCode($userInput, $businessType);

if ($result->failed()) {
    // Integrazione con UEM per error handling
    return $this->errorManager->handle('FISCAL_VALIDATION_ERROR', [
        'validator_message' => $result->errorMessage,
        'country' => $countryCode
    ]);
}
```

### **Struttura Modulare**

```
App/Services/Fiscal/
├── FiscalValidatorInterface.php      # Interface unificata
├── FiscalValidatorFactory.php        # Factory OS1-compliant 
├── ValidationResult.php              # Result container
└── Validators/
    ├── GenericFiscalValidator.php     # Fallback universale
    ├── ItalyFiscalValidator.php       # IT - Codice Fiscale + P.IVA
    ├── PortugalFiscalValidator.php    # PT - NIF
    ├── FranceFiscalValidator.php      # FR - SIREN/SIRET
    ├── SpainFiscalValidator.php       # ES - DNI/NIE/CIF
    ├── EnglandFiscalValidator.php     # EN - UTR/VAT
    └── GermanyFiscalValidator.php     # DE - Steuer-IdNr/USt-IdNr
```

---

## **🌍 Validatori per Paese - Regole Specifiche**

### **1. 🔧 Generic Fiscal Validator (Fallback)**

**Scopo:** Fallback universale per paesi non-MVP

**Regole di Validazione:**

- **Tax Code:** 6-20 caratteri alfanumerici + separatori comuni (-, ., spazio)
- **VAT Number:** 8-15 caratteri alfanumerici
- **Business Logic:** Campi base + validazione condizionale VAT per business types

**Formati Accettati:**

```
Tax Code: ABC123DEF456 (6-20 char, alphanumeric + separators)
VAT: 12345678 (8-15 char, alphanumeric)
```

**Livello Validazione:** `basic_format` - Solo formato e lunghezza, nessun checksum

---

### **2. 🇮🇹 Italy Fiscal Validator (Più Complesso)**

**Scopo:** Mercato primario FlorenceEGI - Validazione completa Codice Fiscale e Partita IVA italiana

#### **Codice Fiscale (16 caratteri)**

**Formato:** `RSSMRA80A01H501X`

- **Posizioni 1-6:** Cognome + Nome (3+3 caratteri consonanti/vocali)
- **Posizioni 7-8:** Anno nascita (2 cifre)
- **Posizione 9:** Mese nascita (lettera: A=Gen, B=Feb, C=Mar, D=Apr, E=Mag, H=Giu, L=Lug, M=Ago, P=Set, R=Ott, S=Nov, T=Dic)
- **Posizioni 10-11:** Giorno nascita + genere (01-31 M, 41-71 F)
- **Posizioni 12-15:** Codice comune nascita
- **Posizione 16:** Check digit (lettera)

**Algoritmo Checksum:**

```php
// Tabelle odd/even per calcolo check digit
$oddTable = ['0'=>1, '1'=>0, '2'=>5, 'A'=>1, 'B'=>0, 'C'=>5, ...];
$evenTable = ['0'=>0, '1'=>1, '2'=>2, 'A'=>0, 'B'=>1, 'C'=>2, ...];

// Calcolo: somma pesata posizioni dispari/pari → modulo 26 → lettera
```

**Business Logic:**

- Estrazione data nascita e validazione temporale (non futuro, non >150 anni)
- Estrazione genere (M/F) dal giorno

#### **Partita IVA (11 cifre)**

**Formato:** `12345678901`

- **Lunghezza:** Esattamente 11 cifre numeriche
- **Algoritmo:** Luhn modificato specifico italiano

**Algoritmo Checksum:**

```php
// Calcolo weighted sum con alternanza odd/even
for ($i = 0; $i < 10; $i++) {
    if ($i % 2 === 0) { // Posizioni dispari
        $sum += $digit;
    } else { // Posizioni pari
        $doubled = $digit * 2;
        $sum += ($doubled > 9) ? ($doubled - 9) : $doubled;
    }
}
$checkDigit = (10 - ($sum % 10)) % 10;
```

---

### **3. 🇵🇹 Portugal Fiscal Validator**

**Scopo:** NIF (Número de Identificação Fiscal) unificato per tax code e VAT

#### **NIF (9 cifre)**

**Formato:** `123456789`

- **Lunghezza:** Esattamente 9 cifre numeriche
- **Prefissi Entità:**
    - `1, 2, 3` → Persone fisiche
    - `5, 6, 7, 8, 9` → Aziende/Enti collettivi
    - `4` → Enti pubblici

**Algoritmo Checksum:**

```php
$weights = [9, 8, 7, 6, 5, 4, 3, 2];
// Weighted sum primi 8 cifre
$remainder = $sum % 11;
$checkDigit = ($remainder < 2) ? 0 : (11 - $remainder);
```

**Business Logic:**

- Validazione coerenza prefisso vs business type dichiarato
- NIF serve sia come tax code che VAT number

---

### **4. 🇫🇷 France Fiscal Validator**

**Scopo:** Sistema SIREN/SIRET per identificazione aziende e stabilimenti

#### **SIREN (9 cifre) - Tax Code**

**Formato:** `123456789`

- **Lunghezza:** 9 cifre numeriche
- **Algoritmo:** Luhn standard
- **Ruolo:** Identificativo azienda

#### **SIRET (14 cifre) - VAT Number**

**Formato:** `12345678912345`

- **Struttura:** SIREN (9) + NIC (5)
- **SIREN:** Primi 9 cifre (numero azienda)
- **NIC:** Cifre 10-14 (numero stabilimento)
- **Validazione:** Luhn su SIREN + Luhn su SIRET completo

**Algoritmo Luhn:**

```php
// Standard Luhn con alternanza moltiplicazione x2
for ($i = strlen($number) - 1; $i >= 0; $i--) {
    if ($alternate) {
        $digit *= 2;
        if ($digit > 9) $digit = ($digit % 10) + 1;
    }
    $sum += $digit;
    $alternate = !$alternate;
}
return ($sum % 10) === 0;
```

---

### **5. 🇪🇸 Spain Fiscal Validator (Sistema Triplo)**

**Scopo:** Auto-detection e validazione DNI, NIE, CIF spagnoli

#### **DNI (8 cifre + lettera) - Persone fisiche spagnole**

**Formato:** `12345678Z`

- **Struttura:** 8 cifre + 1 lettera check
- **Algoritmo:** Modulo 23 con tabella lettere

```php
$letters = ['T','R','W','A','G','M','Y','F','P','D','X','B','N','J','Z','S','Q','V','H','L','C','K','E'];
$checkLetter = $letters[(int)$numbers % 23];
```

#### **NIE (lettera + 7 cifre + lettera) - Stranieri residenti**

**Formato:** `X1234567L`

- **Struttura:** X/Y/Z + 7 cifre + lettera check
- **Algoritmo:** Conversione lettera iniziale (X→0, Y→1, Z→2) + modulo 23

#### **CIF (lettera + 7 cifre + cifra/lettera) - Aziende**

**Formato:** `A12345674` o `A1234567J`

- **Struttura:** Lettera org + 7 cifre + check digit/letter
- **Tipi Organizzazione:**
    - `A` → Sociedad Anónima
    - `B` → Sociedad de Responsabilidad Limitada
    - `G` → Asociación
    - `[...]` → Altri tipi specifici

**Algoritmo CIF:**

```php
// Somma alternata odd/even con moltiplicazione
$checkDigit = (10 - ($sum % 10)) % 10;
$checkLetter = substr('JABCDEFGHI', $checkDigit, 1);
// Alcuni tipi usano cifra, altri lettera
```

---

### **6. 🇬🇧 England Fiscal Validator**

**Scopo:** UTR per tax code, VAT number con suffissi per business complessi

#### **UTR (10 cifre) - Unique Taxpayer Reference**

**Formato:** `1234567890`

- **Lunghezza:** 10 cifre numeriche
- **Algoritmo:** Weighted sum con modulo 23 + casi speciali

```php
$weights = [6, 7, 8, 9, 10, 5, 4, 3, 2];
// Calcolo con casi speciali per remainder 0 e 1
if ($remainder === 0 || $remainder === 1) {
    $expectedCheckDigit = 0;
} else {
    $expectedCheckDigit = 23 - $remainder;
}
```

#### **VAT Number (9 cifre + suffisso opzionale)**

**Formato:** `123456789` o `12345678901`

- **Core:** 9 cifre base
- **Suffisso:** 2 cifre opzionali per group/divisional registration
- **Algoritmo:** Weighted sum + modulo 97

```php
$weights = [8, 7, 6, 5, 4, 3, 2];
$expectedCheckDigits = 97 - ($sum % 97);
// Casi speciali per remainder ≤ 1
```

---

### **7. 🇩🇪 Germany Fiscal Validator (Più Rigoroso)**

**Scopo:** Steuer-IdNr con business rules rigorose, USt-IdNr con prefix DE

#### **Steuerliche Identifikationsnummer (11 cifre)**

**Formato:** `12345678901`

- **Lunghezza:** 11 cifre numeriche
- **Business Rules:**
    - Non può iniziare con 0
    - Massimo 3 ripetizioni per cifra
    - Massimo 2 cifre diverse con ripetizioni

```php
// Validazione business rules
if ($steuerId[0] === '0') return false;
if ($digitCount > 3) return false; // Per qualsiasi cifra
if ($repeatedDigits > 2) return false; // Troppi tipi diversi ripetuti
```

**Algoritmo Checksum:**

```php
$product = 10;
for ($i = 0; $i < 10; $i++) {
    $sum = ($digit + $product) % 10;
    if ($sum === 0) $sum = 10;
    $product = ($sum * 2) % 11;
}
$checkDigit = (11 - $product) % 10;
```

#### **USt-IdNr (DE + 9 cifre) - VAT Number**

**Formato:** `DE123456789`

- **Struttura:** DE prefix obbligatorio + 9 cifre
- **Algoritmo:** Weighted sum + modulo 11 (caso speciale: resto 10 = invalido)

```php
// Weighted sum con posizioni
for ($i = 0; $i < 8; $i++) {
    $sum += (int)$numericPart[$i] * ($i + 1);
}
$checkDigit = $sum % 11;
if ($checkDigit === 10) return false; // Caso speciale
```

---

## **🔧 Integrazione con User Domains**

### **Usage Pattern nel PersonalDataController**

```php
class PersonalDataController extends BaseUserDomainController 
{
    public function update(UpdatePersonalDataRequest $request) 
    {
        $country = $this->getUserCountry(); // IT, PT, FR, ES, EN, DE
        $validator = FiscalValidatorFactory::create($country);
        
        $result = $validator->validateTaxCode(
            $request->tax_code, 
            $request->business_type
        );
        
        if ($result->failed()) {
            return $this->errorManager->handle('FISCAL_VALIDATION_ERROR', [
                'message' => $result->errorMessage,
                'context' => $result->context,
                'country' => $country
            ]);
        }
        
        // Salva il valore formattato
        $user->personal_data->tax_code = $result->formattedValue;
        // ...
    }
}
```

### **Frontend TypeScript Integration**

```typescript
// Real-time validation nel form Personal Data
class PersonalDataManager {
    async validateFiscalCode(taxCode: string, country: string): Promise<ValidationResult> {
        const response = await fetch('/api/fiscal/validate', {
            method: 'POST',
            body: JSON.stringify({ tax_code: taxCode, country: country })
        });
        
        if (!response.ok) {
            return UEM.handleServerError(response);
        }
        
        return await response.json();
    }
}
```

---

## **📊 Translation Keys Required**

Il sistema richiede le seguenti chiavi di traduzione nel file `user_personal_data.php`:

### **Messaggi di Validazione Generici**

```php
'validation' => [
    'tax_code_required' => 'Il codice fiscale è obbligatorio',
    'vat_number_required' => 'La partita IVA è obbligatoria',
    'tax_code_min_length' => 'Il codice fiscale deve avere almeno :min caratteri',
    'tax_code_max_length' => 'Il codice fiscale non può superare :max caratteri',
    // ...
]
```

### **Messaggi Specifici per Paese**

```php
// Italia
'tax_code_italy_length' => 'Il codice fiscale italiano deve essere di :required caratteri',
'tax_code_italy_format' => 'Il formato del codice fiscale non è valido',
'tax_code_italy_checksum' => 'Il codice fiscale non è corretto (errore checksum)',
'tax_code_italy_future_birth' => 'La data di nascita non può essere nel futuro',

// Portugal
'tax_code_portugal_length' => 'Il NIF portoghese deve essere di :required cifre',
'tax_code_portugal_business_type_mismatch' => 'Il tipo di business non corrisponde al NIF',

// Francia, Spagna, Inghilterra, Germania...
// [Pattern simile per tutti i paesi]
```

---

## **🧪 Testing Strategy**

### **Unit Tests per Validator**

```php
class ItalyFiscalValidatorTest extends TestCase 
{
    public function test_validates_correct_codice_fiscale()
    {
        $validator = new ItalyFiscalValidator();
        $result = $validator->validateTaxCode('RSSMRA80A01H501X');
        
        $this->assertTrue($result->isValid);
        $this->assertEquals('RSSMRA80A01H501X', $result->formattedValue);
    }
    
    public function test_rejects_invalid_checksum()
    {
        $validator = new ItalyFiscalValidator();
        $result = $validator->validateTaxCode('RSSMRA80A01H501Z'); // Wrong check
        
        $this->assertFalse($result->isValid);
        $this->assertStringContains('checksum', $result->errorMessage);
    }
}
```

### **Integration Tests con UEM**

```php
public function test_fiscal_validation_integrates_with_uem()
{
    $response = $this->post('/user/personal-data', [
        'tax_code' => 'INVALID_CODE',
        'country' => 'IT'
    ]);
    
    $response->assertStatus(422);
    $response->assertJson([
        'error' => 'FISCAL_VALIDATION_ERROR',
        'message' => 'Il formato del codice fiscale non è valido'
    ]);
}
```

---

## **📈 Performance & Scalability**

### **Caching Strategy**

- **Validator Instances:** Cached nel Factory per evitare ricostruzione
- **Validation Results:** Opzionale caching per codici già validati
- **Translation Keys:** Cached da Laravel localization system

### **Error Handling**

- **UEM Integration:** Tutti gli errori passano attraverso Error Manager
- **Graceful Degradation:** Generic validator come fallback
- **GDPR Compliance:** Validazione locale, nessuna API esterna

### **Monitoring**

- **ULM Logging:** Tracking validation attempts per paese
- **Metrics:** Success/failure rates per validator
- **Performance:** Tempo medio validazione per algoritmo

---

## **🔄 Maintenance & Updates**

### **Adding New Countries**

1. Implementare `FiscalValidatorInterface`
2. Aggiungere al `$validatorMap` nel Factory
3. Creare translation keys specifiche
4. Aggiungere unit tests completi

### **Algorithm Updates**

- Ogni validator è indipendente
- Updates non impattano altri paesi
- Backward compatibility garantita da interface

### **Compliance Updates**

- Monitoring regulatory changes per paese
- Versioning degli algoritmi per audit trail
- Documentation updates automatici

---

## **✅ Conclusioni**

Il **Fiscal Validation System** di FlorenceEGI fornisce validazione enterprise-grade per tutti i mercati MVP con:

- ✅ **Accuracy:** Algoritmi ufficiali per ogni paese
- ✅ **Performance:** Caching e ottimizzazioni
- ✅ **Scalability:** Pattern modulare per nuovi paesi
- ✅ **Compliance:** GDPR-native, no external APIs
- ✅ **Integration:** Seamless con User Domains e UEM
- ✅ **Maintainability:** OS1-compliant architecture

Il sistema è pronto per l'MVP del 30 Giugno e può scalare facilmente per supportare mercati aggiuntivi post-launch.

---

**Sistema completato e documentato secondo standard OS1.**  
**Ready for Personal Data Domain integration.** 🚀