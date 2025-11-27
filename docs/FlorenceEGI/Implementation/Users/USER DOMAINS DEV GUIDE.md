# FlorenceEGI - Developer Guide
## GDPR-Compliant User Domains Management

**Document Version:** 1.0  
**Date:** 6 Giugno 2025  
**Target Audience:** FlorenceEGI Development Team  
**Platform:** FlorenceEGI - Digital Renaissance Platform  

---

## Executive Summary for Developers

Questo documento descrive l'architettura unificata di FlorenceEGI per la gestione dei domini utente in compliance GDPR. Il sistema integra il **ConsentService GDPR esistente** con i **User Domain Controllers**, creando un'architettura dove privacy e funzionalità lavorano insieme senza conflitti.

### Key Concepts

- **Un solo sistema di consensi** - ConsentService gestisce tutto via `user_consents`
- **Domini segregati** - Ogni tipo di dato ha il suo controller e tabella
- **Authentication a livelli** - Weak/Strong auth per diversi gradi di accesso
- **Audit trail nativo** - Ogni operazione è tracciata automaticamente
- **Self-service privacy** - Utenti gestiscono autonomamente i propri dati

### For Developers: What This Means

- 🔧 **ConsentService è il single source of truth** per tutti i consensi
- 🔧 **User Domain Controllers** gestiscono solo i dati specifici del dominio
- 🔧 **FegiAuth helper** determina cosa può fare l'utente in base all'auth level
- 🔧 **Audit logging** è automatico, non serve implementarlo manualmente
- 🔧 **Error handling UEM** è già configurato per privacy compliance

---

## 1. Architettura Generale

### 1.1 Separazione delle Responsabilità

```
┌─────────────────────┐    ┌──────────────────────┐    ┌─────────────────────┐
│   ConsentService    │    │  User Domain         │    │  GdprAuditLog      │
│                     │    │  Controllers         │    │                     │
│ • Gestisce consensi │    │                      │    │ • Audit trail      │
│ • user_consents     │    │ • PersonalData       │    │ • Immutable logs   │
│ • Versioning        │    │ • Documents          │    │ • Compliance proof │
│ • History           │    │ • Organization       │    │ • Breach detection │
│                     │    │ • InvoicePreferences │    │                     │
└─────────────────────┘    └──────────────────────┘    └─────────────────────┘
```

### 1.2 Data Storage Strategy

**Consensi GDPR:**
- **Tabella:** `user_consents`  
- **Gestione:** ConsentService esclusivamente
- **Caratteristiche:** Immutable, versionati, audit completo

**Dati Utente per Dominio:**
- **Personal Data:** `user_personal_data` (indirizzo, telefono, fiscal data)
- **Documents:** `user_documents` (upload, sharing, retention)  
- **Organization:** `user_organizations` (business data, team management)
- **Invoice Preferences:** `user_invoice_preferences` (financial settings)

**Audit & Compliance:**
- **Tabella:** `gdpr_audit_logs`
- **Scope:** Ogni operazione su dati personali
- **Features:** Hash integrity, retention policies, export capabilities

### 1.3 Integration Points

**ConsentService ↔ Domain Controllers:**
- Domain controllers **leggono** consent status via ConsentService
- Domain controllers **non gestiscono** consensi direttamente
- Consent updates **sempre** via ConsentService

**FegiAuth ↔ Permissions:**
- Weak auth: permessi limitati (`edit_own_profile_data`)
- Strong auth: accesso completo ai domini
- Upgrade prompts automatici quando necessario

---

## 2. Flusso Registrazione Utente

### 2.1 User Registration Flow

```
1. User compila form registrazione
   ├── Dati account (email, password)
   ├── Consensi GDPR (marketing, analytics, profiling)
   └── Dati base opzionali

2. RegisterUserController processa
   ├── Crea User record
   ├── Chiama ConsentService.createDefaultConsents()
   └── Inizializza domini base (se forniti)

3. ConsentService salva in user_consents
   ├── functional: true (sempre required)
   ├── personal_data_processing: true (sempre required)
   ├── marketing: user choice
   ├── analytics: user choice
   └── profiling: user choice

4. Audit logging automatico
   ├── Registration event logged
   ├── Consent grants logged
   └── IP/User-Agent tracking

5. Email verification + Welcome flow
```

### 2.2 Default Consents Strategy

**Always Granted (Legal Basis: Legitimate Interest):**
- `functional` - Platform operation
- `personal_data_processing` - Profile management necessity

**User Choice (Legal Basis: Consent):**
- `marketing` - Email marketing, newsletters  
- `analytics` - Usage analytics, improvement
- `profiling` - Personalized recommendations

### 2.3 Post-Registration Domain Initialization

**Lazy Initialization Pattern:**
- User domains tables **non** vengono create durante registrazione
- Domain records vengono creati **al primo accesso** (`firstOrCreate`)
- Permette registrazione veloce, popolamento progressivo

---

## 3. User Domains Architecture

### 3.1 Domain Controller Pattern

**Ogni domain ha la stessa struttura:**

```
BaseUserDomainController (abstract)
├── checkWeakAuthAccess()     # Verifica permission livello dominio
├── auditDataAccess()         # Logging automatico operazioni
├── requireIdentityVerification() # Per operazioni sensibili
├── respondError()            # UEM integration
└── getMvpCountries()         # 6 nazioni supportate

PersonalDataController extends BaseUserDomainController
├── getRequiredDomainPermission() → 'edit_own_personal_data'
├── index()                   # Lista/form dati personali
├── update()                  # Aggiorna dati + consensi
├── export()                  # Right to data portability  
└── destroy()                 # Right to erasure
```

### 3.2 Permission Strategy

**Weak Authentication Users:**
- `edit_own_profile_data` - Solo dati base, non sensibili
- Redirect automatico per upgrade su dati sensibili

**Strong Authentication Users:**  
- `edit_own_personal_data` - Full access personal data
- `edit_own_organization_data` - Business data (se applicable)
- `manage_own_documents` - Document management
- `manage_own_invoice_preferences` - Financial preferences

### 3.3 Data Access Patterns

**Read Pattern:**
```
1. Controller verifica authentication level
2. Se weak auth → controlla permissions specifiche
3. Carica dati dominio (user_personal_data, etc.)
4. Carica consent status via ConsentService
5. Merge dei dati per la view
6. Audit log dell'accesso
```

**Write Pattern:**
```
1. Validation via Form Request (country-specific)
2. Identity verification (se richiesta)
3. Check consensi appropriati
4. Transaction per update atomico
5. Update dati dominio + consensi separatamente
6. Audit log delle modifiche
7. Success response con summary
```

---

## 4. GDPR Consent Integration

### 4.1 Consent Reading in Domain Controllers

**Pattern nel controller index():**
```
// Carica dati dominio
$personalData = $this->getOrCreatePersonalData($user);

// Carica consent status dal sistema GDPR  
$consentService = app(ConsentService::class);
$gdprConsents = [
    'personal_data_processing' => $consentService->hasConsent($user, 'personal_data_processing'),
    'marketing' => $consentService->hasConsent($user, 'marketing'),
    // ... altri consensi
];

// Passa entrambi alla view
return view('domain.index', [
    'personalData' => $personalData,
    'gdprConsents' => $gdprConsents,
]);
```

### 4.2 Consent Updates in Domain Controllers

**Pattern nel controller update():**
```
// Verifica consenso per operazione sensibile
if (!$consentService->hasConsent($user, 'personal_data_processing')) {
    return $this->respondError('GDPR_VIOLATION_ATTEMPT', ...);
}

// Update dati dominio
$personalData = $this->processPersonalDataUpdate($user, $validatedData);

// Update consensi se modificati (separatamente)
if ($request->has('gdpr_consents')) {
    $consentService->updateConsents($user, $request->input('gdpr_consents'));
}
```

### 4.3 Consent Validation Strategy

**Level 1: Required Functional Consents**
- Sempre verificati automaticamente
- Blocco totale se mancanti
- Non modificabili dall'utente

**Level 2: Domain-Specific Consents**  
- Verificati per operazioni specifiche
- Error handling graceful con upgrade prompts
- Modificabili dall'utente

**Level 3: Optional Marketing Consents**
- Non bloccanti per funzionalità core
- Influenzano comunicazioni e features opzionali

---

## 5. Authentication Levels & Data Access

### 5.1 Weak Authentication ("FEGI Connect")

**Caratteristiche:**
- Wallet-based connection
- Session-based (non persistent login)
- Accesso limitato ai dati

**Permessi disponibili:**
- View basic profile information
- Edit non-sensitive personal data
- Limited document access
- No financial data access

**Upgrade prompts:**
- Automatic quando accede dati sensibili
- Seamless transition preservando session
- Mantiene modifiche in corso

### 5.2 Strong Authentication (Traditional)

**Caratteristiche:**
- Email/password with 2FA
- Persistent login sessions  
- Full data access

**Permessi disponibili:**
- All domain management capabilities
- Sensitive data editing (fiscal codes, etc.)
- Data export/deletion rights
- Organization management
- Financial preferences

### 5.3 Identity Verification Layer

**Per operazioni ultra-sensibili:**
- Data deletion (Right to Erasure)
- Bulk data export
- Financial data changes
- Organization ownership transfer

**Meccanismo:**
- Password re-confirmation
- 2FA verification (se abilitato)
- Time-based verification window (30 min)
- Session-based verification status

---

## 6. Data Subject Rights Implementation

### 6.1 Right of Access (Article 15)

**Self-Service Implementation:**
- Ogni domain controller espone `index()` method
- Visualizzazione completa dati personalizzati per dominio
- Consent history accessible via ConsentService
- Audit trail consultabile dall'utente

### 6.2 Right to Data Portability (Article 20)

**Export Functionality:**
```
Domain Controller → export() method
├── Rate limiting (1 export/30 giorni)
├── Identity verification required
├── Data aggregation from domain tables
├── Format conversion (JSON/CSV/XML)
├── Optional encryption
├── Secure download link
└── Automatic cleanup
```

**Export includes:**
- All personal data from domain
- Consent history and status
- Metadata (creation dates, last updates)
- Audit trail (where legally appropriate)

### 6.3 Right to Rectification (Article 16)

**Update Workflows:**
- Real-time validation (client + server side)
- Country-specific fiscal validation
- Audit trail automatico per ogni modifica
- Conflict resolution per concurrent updates

### 6.4 Right to Erasure (Article 17)

**Deletion Implementation:**
```
Domain Controller → destroy() method
├── Strong authentication required
├── Identity verification mandatory
├── Legal basis verification
├── Dependency checking
├── Cascading deletion across domains
├── Audit record preservation
└── Confirmation notification
```

**Deletion Strategy:**
- **Soft deletion** per legal retention requirements
- **Hard deletion** where legally required
- **Partial deletion** per conflicting legal bases
- **Audit preservation** sempre mantenuto

### 6.5 Right to Restrict Processing (Article 18)

**Processing Restrictions:**
- User-initiated through domain interfaces
- Automatic enforcement in controllers
- Temporary restrictions con expiry
- Granular per data category

---

## 7. Multi-National Compliance

### 7.1 Country-Specific Validation

**FiscalValidatorFactory Pattern:**
```
Factory identifica paese utente
├── Country detection (profile, request headers, IP)
├── Validator selection (IT, PT, FR, ES, EN, DE)
├── Rules loading per business type
├── Validation execution
└── Localized error messages
```

**Supported Business Types:**
- Individual (natural person)
- Business (sole proprietorship)  
- Corporation (legal entity)
- Partnership (joint entity)
- Non-profit (tax-exempt)

### 7.2 Localization Strategy

**Translation Management:**
- Domain-specific translation files
- GDPR notice translations per country
- Cultural adaptation oltre linguistic
- Accessibility compliance (WCAG 2.1 AA)

**Regional Differences:**
- Date/time formats
- Address formats  
- Phone number formats
- Currency handling
- Legal age verification

---

## 8. Error Handling & UEM Integration

### 8.1 GDPR-Aware Error Management

**UltraErrorManager Configuration:**
- Sensitive data sanitization automatic
- GDPR-specific error codes defined
- Privacy-compliant error logging
- User-friendly error messages

**Error Categories:**
- `PERSONAL_DATA_*_ERROR` - Domain operation failures
- `GDPR_VIOLATION_ATTEMPT` - Privacy violation blocks
- `CONSENT_*_ERROR` - Consent management issues
- `EXPORT_*_ERROR` - Data portability failures

### 8.2 Audit Trail Integration

**Automatic Logging:**
- Ogni domain controller operation
- Access attempts (successful e failed)
- Data modifications con before/after
- Consent changes con legal basis

**Audit Record Structure:**
```
GdprAuditLog {
    user_id: target user
    action_type: operation performed
    domain_controller: which controller
    context_data: operation details
    legal_basis: GDPR article reference
    ip_address: privacy-compliant tracking
    record_hash: integrity verification
}
```

---

## 9. Developer Best Practices

### 9.1 Adding New Domains

**Steps per nuovo domain:**

1. **Create Domain Controller**
   - Extend `BaseUserDomainController`
   - Implement `getRequiredDomainPermission()`
   - Add standard CRUD methods

2. **Create Domain Model**
   - User relationship
   - Proper fillable/guarded
   - Audit trail integration

3. **Add Permissions**
   - Define in `RolesAndPermissionsSeeder`
   - Assign to appropriate user roles
   - Test weak/strong auth scenarios

4. **Create Form Requests**
   - Country-specific validation
   - GDPR compliance checks
   - Error message localization

5. **Update ConsentService**
   - Add domain-specific consents se necessari
   - Update registration flow
   - Test consent integration

### 9.2 Working with ConsentService

**Do:**
- Sempre usare ConsentService per consent operations
- Check consent before sensitive operations
- Log consent changes appropriately
- Handle consent withdrawal gracefully

**Don't:**
- Non modificare user_consents direttamente
- Non duplicare consent logic nei domain controllers
- Non ignorare consent requirements per "convenience"
- Non esporre consent internals nelle API

### 9.3 Handling Authentication Levels

**Pattern per permission checking:**
```
// Nel domain controller
$accessCheck = $this->checkWeakAuthAccess();
if ($accessCheck !== true) {
    return $accessCheck; // Redirect to upgrade or login
}

// Per operazioni sensibili
if (sensitive_operation && !FegiAuth::isStrongAuth()) {
    return $this->redirectToUpgrade();
}
```

### 9.4 Audit Trail Best Practices

**What to log:**
- Data access attempts
- Modification operations  
- Export/download activities
- Consent changes
- Authentication events

**What NOT to log:**
- Sensitive data content (solo metadata)
- Authentication credentials
- Internal system operations
- Debug information

---

## 10. Testing & Quality Assurance

### 10.1 GDPR Compliance Testing

**Test Categories:**

**Consent Flow Testing:**
- Registration consent collection
- Consent update workflows
- Consent withdrawal handling
- Consent history accuracy

**Data Rights Testing:**
- Export completeness e accuracy
- Deletion verification
- Access control enforcement  
- Rectification workflows

**Authentication Testing:**
- Weak auth permission limits
- Strong auth capabilities
- Upgrade flow functionality
- Identity verification

**Audit Trail Testing:**
- Log completeness
- Integrity verification
- Retention compliance
- Export capabilities

### 10.2 Multi-National Testing

**Per ogni paese MVP:**
- Fiscal validation accuracy
- Localization correctness
- Cultural appropriateness
- Legal requirement compliance

### 10.3 Performance & Scalability

**Load Testing:**
- Consent checking performance
- Audit logging overhead
- Export generation time
- Database query optimization

---

## Conclusion

L'architettura GDPR-compliant di FlorenceEGI offre agli sviluppatori:

- **Consistency** - Pattern uniformi per tutti i domini
- **Compliance** - GDPR native, non bolt-on
- **Flexibility** - Estendibile per nuovi domini e paesi
- **Maintainability** - Separazione responsabilità chiara
- **User Empowerment** - Self-service privacy management

### Key Takeaways for Developers

1. **ConsentService è il single source of truth** per tutti i consensi
2. **BaseUserDomainController** fornisce foundation compliance per nuovi domini
3. **FegiAuth** determina capabilities basate su authentication level
4. **Audit logging** è automatico e non richiede implementazione manuale
5. **UEM error handling** è pre-configurato per privacy compliance

### Next Steps

- Implementazione domini rimanenti (Documents, Organization, Invoice Preferences)
- Estensione ConsentService per domini specifici se necessario
- Testing comprehensive dei flussi GDPR
- Performance optimization per scala enterprise
- Documentazione API per frontend integration

---

**Document maintained by:** FlorenceEGI Development Team  
**Last updated:** 6 Giugno 2025  
**Version control:** Track changes in development documentation repo