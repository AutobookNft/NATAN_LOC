# **📋 DOCUMENTO OPERATIVO COMPLETO OS1 - TRANSIZIONE NUOVA CHAT**

## **🎯 INTENZIONALITÀ ESPLICITA - COSA STIAMO FACENDO E PERCHÉ**

### **PROGETTO: CRUD Domini Utente FlorenceEGI MVP**
- **Obiettivo Primario**: Completare sistema gestione dati utente con 3 domini mancanti
- **Deadline Critica**: 30 Giugno 2025 (MVP FlorenceEGI)
- **Valore Generato**: Sistema GDPR-compliant, scalabile globalmente, OS1-nativo

### **DOMINI DA IMPLEMENTARE**:
1. **My Documents** - Upload documenti identità + verifica status
2. **My Invoice Preferences** - Gestione fatturazione con compliance fiscale globale  
3. **My Organization** - Dati aziendali per creator/enterprise/epp_entity

### **FLUSSO FINALE CRITICO**:
4. **Weak→Strong Upgrade** - Trasformazione account debole in forte con:
   - Form completamento dati (nome, cognome, email, password)
   - Verifica via `personal_secret` esistente
   - Auto-creazione record domini applicabili al nuovo ruolo
   - Assegnazione ruolo forte appropriato

---

## **🧱 ARCHITETTURA SISTEMA ESISTENTE - CONTESTO OPERATIVO**

### **STACK TECNICO ATTUALE**:
- **Framework**: Laravel (PHP)
- **Auth System**: FegiAuth (weak/strong authentication)
- **Error Handling**: UEM (Ultra Error Manager)
- **Permissions**: Spatie + custom MenuConditionEvaluator
- **Layout**: Blade components moderni

### **SISTEMA RUOLI IMPLEMENTATO**:
- **weak_connect**: Accesso limitato, 1 collection, no domini avanzati
- **Strong Roles**: creator, patron, collector, enterprise, trader_pro, epp_entity
- **Separation Logic**: Solo creator/enterprise/epp_entity accedono organization data

### **PATTERN ESISTENTI DA SEGUIRE**:
- **GdprController**: Reference per controller structure
- **profile.blade.php**: Reference per layout moderno
- **ContextMenus.php**: Sistema sidebar con permission checks
- **UEM Integration**: Error handling standardizzato

---

## **❌ ERRORI MASSICCI COMMESSI - ANALISI DETTAGLIATA**

### **1. VIOLAZIONI OS1 TOTALI**
- **Zero DocBlock @Oracode** con Purpose/Privacy/Core Logic
- **Zero return types completi** (mancano RedirectResponse per errori)
- **Testi hardcoded** invece di chiavi traduzione
- **Architettura Italia-only** invece di globale
- **Zero FegiAuth integration** (usato auth() standard)

### **2. MANCANZE ARCHITETTURALI CRITICHE**
- **No FiscalValidatorInterface** per scalabilità globale
- **No Configuration-driven** validation rules
- **No weak→strong upgrade logic**
- **No auto-creation domini** per nuovi ruoli

### **3. MANCANZE DOCUMENTAZIONE**
- **No spiegazione contesto** (domini CRUD)
- **No spiegazione obiettivo finale** (weak→strong)
- **No deadline awareness** (30 Giugno)
- **No integration patterns** con esistente

---

## **✅ REGOLE OS1 ASSOLUTE - CHECKLIST OBLIGATORIA**

### **OGNI CLASSE DEVE AVERE**:
```php
/**
 * @Oracode Controller: [Semantic Domain Name] Management
 * 🎯 Purpose: [Why exists, what value generates for FlorenceEGI MVP]
 * 🛡️ Privacy: [GDPR compliance, audit logging, data protection]
 * 🧱 Core Logic: [Main patterns, weak/strong auth, UEM integration]
 * 🌍 Scale: [Global market readiness, fiscal compliance]
 * ⏰ MVP: [How contributes to 30 June deadline]
 * 
 * @package App\Http\Controllers\User
 * @author Padmin D. Curtis (AI Partner OS1-Compliant)
 * @version 1.0.0 (FlorenceEGI MVP - Global Ready)
 * @deadline 2025-06-30
 */
```

### **OGNI METODO DEVE AVERE**:
```php
/**
 * @Oracode Method: [Semantic Action] with Global Compliance
 * 🎯 Purpose: [Exact purpose, business value, user benefit]
 * 📥 Input: [What receives, validation, constraints]
 * 📤 Output: [ALL possible returns - View|RedirectResponse]
 * 🛡️ Privacy: [GDPR audit, data protection measures]
 * 🌍 Scale: [Multi-country support, fiscal validation]
 * 🔐 Auth: [FegiAuth requirements, weak/strong handling]
 * 🧱 Core Logic:
 *   - FegiAuth verification (weak redirect to upgrade)
 *   - Permission validation via checkWeakAuthAccess()
 *   - Country-specific fiscal validation (if applicable)
 *   - Business logic execution with audit logging
 *   - UEM error handling with semantic codes
 * 
 * @param RequestClass $request Validated input with global compliance
 * @return View|RedirectResponse Success view or error/upgrade redirect
 * 
 * @throws \Exception All handled via UEM with codes [DOMAIN]_[ACTION]_FAILED
 */
```

### **ARCHITETTURA GLOBALE OBBLIGATORIA**:
- **FiscalValidatorInterface** + Factory per tutti i paesi
- **Configuration files** per regole fiscali per paese  
- **FegiAuth integration** totale
- **UEM error handling** standardizzato
- **Audit logging** GDPR-compliant

---

## **📋 PIANO LAVORO COMPLETO - SEQUENZA ESATTA**

### **FASE 0: FONDAMENTA GLOBALI (45 min)**
1. **Fiscal Architecture**:
   - `FiscalValidatorInterface` + Factory
   - Validators: IT, DE, FR, US, EU-Generic, Global-Generic  
   - Config files: `config/fiscal-rules/[country].php`

2. **Translation Architecture**:
   - `resources/lang/it/user_documents.php`
   - `resources/lang/it/user_invoice.php`
   - `resources/lang/it/user_organization.php`
   - `resources/lang/it/validation.php` (estensioni)

3. **Base Controller OS1**:
   - `BaseUserDomainController` con FegiAuth + UEM
   - Utility methods: `checkWeakAuthAccess()`, `redirectToUpgrade()`
   - Audit logging integration

### **FASE 1: REQUEST VALIDATION GLOBALE (60 min)**
1. **StoreDocumentRequest**:
   - File validation sicura
   - Document type validation
   - User ownership checks
   - OS1 documentation completa

2. **UpdateInvoicePreferencesRequest**:
   - Fiscal validation globale via Factory
   - Country-specific rules dynamic
   - Business type conditional validation
   - Italian VAT/Tax code validation

3. **UpdateOrganizationRequest**:
   - Role-based access validation
   - Organization type validation globale
   - Legal representative requirements
   - Verification reset logic

### **FASE 2: CONTROLLERS DOMINI (90 min)**
1. **UserDocumentsController**:
   - CRUD completo con FegiAuth
   - Upload sicuro + verification status
   - Download con audit trail
   - Strong auth requirement

2. **UserInvoicePreferencesController**:
   - Edit/Update preferences globali
   - Invoice history view
   - PDF download con audit
   - Fiscal compliance per paese

3. **UserOrganizationController**:
   - Edit/Update dati organizzazione
   - Verification status tracking
   - Role separation logic
   - EPP compliance checking

### **FASE 3: VISTE MODERNE BLADE (75 min)**
1. **Layout Components**:
   - `<x-user-domain-layout>` base
   - `<x-upgrade-prompt>` per weak auth
   - `<x-fiscal-fields>` country-dynamic

2. **Documents Views**:
   - `user.documents.index` con upload button
   - `user.documents.create` form upload
   - `user.documents.show` details + download

3. **Invoice Views**:
   - `user.invoice-preferences.edit` form globale
   - `user.invoice-preferences.invoices` history
   - Country-specific field rendering

4. **Organization Views**:
   - `user.organization.edit` form role-aware
   - `user.organization.verification-status`
   - EPP compliance tracking

### **FASE 4: ROUTES + SIDEBAR (30 min)**
1. **Routes RESTful**:
   - Resource routes per ogni dominio
   - Middleware FegiAuth appropriati
   - Named routes semantici

2. **Sidebar Extension**:
   - MenuGroup "My Data Management"
   - Permission-based visibility
   - Icons colorate + traduzioni

### **FASE 5: WEAK→STRONG UPGRADE LOGIC (60 min)**
1. **UpgradeAccountController**:
   - `showUpgradeForm()`: Form completamento dati
   - `processUpgrade()`: Verifica personal_secret
   - `createDomainRecords()`: Auto-creazione record applicabili

2. **Upgrade Views**:
   - Form name/surname/email/password
   - personal_secret verification
   - Role selection logic

3. **Domain Records Creation**:
   - InvoicePreferences per tutti i ruoli strong
   - OrganizationData solo per creator/enterprise/epp_entity
   - Default values appropriati per ruolo

### **FASE 6: TESTING ESSENZIALE (45 min)**
1. **Permission Tests**:
   - Weak auth access denied
   - Strong auth access granted
   - Role-based organization access

2. **Fiscal Validation Tests**:
   - Italian VAT/Tax validation
   - German/French validation
   - Generic fallback validation

3. **Upgrade Flow Tests**:
   - personal_secret verification
   - Domain records creation
   - Role assignment logic

---

## **📁 FILES NECESSARI KNOWLEDGE BASE**

### **CRITICAL OS1 DOCUMENTS**:
1. **White Paper Oracode System 1 (OS1).md** - Principi fondamentali assoluti
2. **FegiAuth System Architettura.md** - Sistema auth weak/strong
3. **FegiAuth.php** - Helper implementation

### **PERMISSION & ROLE SYSTEM**:
4. **RolesAndPermissionsSeeder.php** - Sistema permission aggiornato
5. **MenuConditionEvaluator.php** - Permission evaluation logic

### **EXISTING PATTERNS**:
6. **GdprController.php** - Reference controller structure
7. **profile.blade.php** - Reference modern view layout
8. **ContextMenus.php** - Sidebar system integration

### **DATABASE STRUCTURE**:
9. **User.php model** - Relations e structure
10. **Migration samples** - Database schema understanding


🔥 **HAI ASSOLUTAMENTE RAGIONE - MANCANZA CRITICA IDENTIFICATA!**

## **❌ ERRORE MASSICCIO: DOMINIO PERSONAL DATA SENSIBILI DIMENTICATO**

### **DOMINI COMPLETI DA IMPLEMENTARE:**
1. **My Personal Data** - CRUD dati personali alta sensibilità GDPR 🚨 **MANCAVA!**
2. **My Documents** - Upload documenti + verification status  
3. **My Invoice Preferences** - Fatturazione con compliance fiscale globale
4. **My Organization** - Dati aziendali (solo creator/enterprise/epp_entity)
5. **Weak→Strong Upgrade** - Trasformazione account con auto-creazione domini

---

## **🛡️ PERSONAL DATA SENSIBILI - DOMINIO CRITICO GDPR**

### **TIPOLOGIE DATI SENSIBILI DA GESTIRE:**
- **Dati Anagrafici Completi**: Nome, cognome, data/luogo nascita, cittadinanza
- **Dati Residenza/Domicilio**: Indirizzi completi, codici fiscali
- **Dati Contatto Sensibili**: Telefoni verificati, email secondary  
- **Preferenze Privacy**: Consensi granulari, opt-in/opt-out marketing
- **Dati Biometrici/Identità**: Hash documenti, foto profilo, signature digitali
- **Dati Familiari**: Stato civile, componenti famiglia (se necessario business)

### **OPERAZIONI CRUD GDPR-COMPLIANT:**
- **CREATE**: Raccolta consensi espliciti, validazione identità
- **READ**: Visualizzazione dati con audit trail accesso
- **UPDATE**: Modifiche con verifica identità + audit completo  
- **DELETE**: Cancellazione con retention policy + backup compliance

### **DIRITTI GDPR DA IMPLEMENTARE:**
- **Right to Access**: Export completo dati JSON/PDF
- **Right to Rectification**: Modifica con storico versioni
- **Right to Erasure**: Cancellazione con orphan cleanup
- **Right to Portability**: Export strutturato machine-readable
- **Right to Restriction**: Freeze processing mantenendo storage

---

## **📋 PIANO LAVORO AGGIORNATO COMPLETO**

### **FASE 0: FONDAMENTA GLOBALI (45 min)**
- FiscalValidatorInterface + Factory
- Translation Architecture completa
- BaseUserDomainController OS1 + GDPR

### **FASE 1: REQUEST VALIDATION GLOBALE (75 min)**
1. **UpdatePersonalDataRequest** 🚨 **NUOVO CRITICO**
   - Validation dati sensibili con privacy checks
   - Identity verification requirements  
   - Consent management validation
   - Data retention policy compliance

2. **StoreDocumentRequest**
3. **UpdateInvoicePreferencesRequest** 
4. **UpdateOrganizationRequest**

### **FASE 2: CONTROLLERS DOMINI (120 min)**
1. **UserPersonalDataController** 🚨 **NUOVO CRITICO**
   - `edit()`: Form dati sensibili con privacy notices
   - `update()`: Aggiornamento con audit + identity verification
   - `export()`: GDPR data export JSON/PDF
   - `requestDeletion()`: Right to erasure workflow
   - **Strong auth required sempre + identity re-verification**

2. **UserDocumentsController**
3. **UserInvoicePreferencesController**
4. **UserOrganizationController**

### **FASE 3: VISTE MODERNE BLADE (90 min)**
1. **Personal Data Views** 🚨 **NUOVO CRITICO**
   - `user.personal-data.edit`: Form dati sensibili 
   - `user.personal-data.privacy`: Consent management
   - `user.personal-data.export`: GDPR export interface
   - Privacy notices + consent UI

2. **Documents Views**
3. **Invoice Views**  
4. **Organization Views**

### **FASE 4: GDPR COMPLIANCE SERVICES (45 min)**
1. **PersonalDataExportService** 🚨 **NUOVO**
   - Export completo JSON structured
   - PDF human-readable generation
   - Data anonymization per backup

2. **ConsentManagementService** 🚨 **NUOVO**  
   - Granular consent tracking
   - Consent withdrawal handling
   - Marketing preferences management

3. **DataRetentionService** 🚨 **NUOVO**
   - Retention policy enforcement
   - Automated cleanup workflows
   - Compliance reporting



**Grazie per aver catturato questa mancanza critica! 🛡️**