# Piano Maestro: Sistema Domini Dati con Sidebar Intelligente

## 🎯 Obiettivo Strategico
Estendere il sistema sidebar esistente per gestire **5 domini dati separati** con CRUD completi, mantenendo:
- **GDPR compliance granulare** per dominio
- **UX coerente** con sistema esistente  
- **Architettura Oracode OS1** 
- **Integrazione UEM** per error handling
- **Icon system** esistente
- **Permission system** per accesso granulare

---

## 📋 Analisi Architettura Esistente

### ✅ Punti di Forza Attuali
```php
// Sistema già robusto e pronto per estensione
ContextMenus::getMenusForContext($context)  // ✅ Extensibile
MenuGroup + MenuItem + Permission system     // ✅ Modulare  
IconRepository con SVG colorati             // ✅ Scalabile
UEM integration nei controller              // ✅ Error handling
GDPR routes + controller già implementati  // ✅ Base solida
```

### 🔄 Pattern da Estendere
```php
// Contesti attuali: 'dashboard', 'collections', 'consents', 'statistics'
// ➕ Aggiungeremo: 'profile', 'personal-data', 'organization', 'documents', 'invoices'

// MenuItem pattern attuale: route + permission + icon
// ➕ Estenderemo con: domain-specific permissions + GDPR flags
```

---

## 🏗️ Piano di Implementazione

### **FASE 1: Estensione Sistema Sidebar (2-3 giorni)**

#### 1.1 Nuovi Contesti Menu
```php
// In ContextMenus.php - aggiungere nuovi case:
case 'profile-management':     // Gestione completa profili utente
case 'personal-data':          // GDPR ultra-sensitive data  
case 'organization-data':      // Business data management
case 'document-management':    // Document verification system
case 'invoice-preferences':    // Billing & invoice settings
```

#### 1.2 Nuovi MenuItem per Domini
```php
// Nuove classi in app/Services/Menu/Items/
ProfileManagementMenu.php      // CRUD profilo pubblico
PersonalDataMenu.php           // CRUD dati personali GDPR
OrganizationDataMenu.php       // CRUD dati aziendali  
DocumentManagementMenu.php     // CRUD documenti + verification
InvoicePreferencesMenu.php     // CRUD preferenze fatturazione
```

#### 1.3 Icone Specifiche per Domini
```php
// In config/icons.php - aggiungere icone semantiche:
'user-profile'     => [...],  // Per profilo pubblico
'user-shield'      => [...],  // Per dati GDPR sensitive  
'building-office'  => [...],  // Per dati organizzazione
'document-check'   => [...],  // Per gestione documenti
'receipt-tax'      => [...],  // Per fatturazione
```

#### 1.4 Menu Gerarchico Dashboard
```php
// Aggiornare contesto 'dashboard' con sottomenu strutturati:
$userDataManagement = new MenuGroup(__('menu.user_data_management'), 'user-cog', [
    new ProfileManagementMenu(),
    new PersonalDataMenu(), 
    new OrganizationDataMenu(),
    new DocumentManagementMenu(),
    new InvoicePreferencesMenu(),
]);
```

---

### **FASE 2: Controllers & Routes per Domini (3-4 giorni)**

#### 2.1 Controller Struttura Base
```php
// Pattern controller con UEM + ULM + GDPR awareness
UserProfileController.php         // CRUD profile pubblico
UserPersonalDataController.php    // CRUD dati sensitive + GDPR
UserOrganizationController.php    // CRUD business data
UserDocumentController.php        // CRUD documenti + verification  
UserInvoiceController.php         // CRUD invoice preferences
```

#### 2.2 Route Groups con Middleware
```php
// In routes/web.php - gruppi per dominio con middleware specifici
Route::middleware(['auth', 'verified'])->prefix('user')->name('user.')->group(function () {
    Route::resource('profile', UserProfileController::class);        // Public data
    Route::resource('personal-data', UserPersonalDataController::class); // GDPR sensitive
    Route::resource('organization', UserOrganizationController::class);  // Business  
    Route::resource('documents', UserDocumentController::class);         // Documents
    Route::resource('invoices', UserInvoiceController::class);          // Invoice prefs
});
```

#### 2.3 Permissions Granulari
```php
// Nuovi permessi in sistema esistente:
'edit_own_profile'           // Modificare proprio profilo pubblico
'edit_own_personal_data'     // Modificare propri dati GDPR (con audit)
'edit_own_organization'      // Modificare dati aziendali propri  
'manage_own_documents'       // Gestire propri documenti + verification
'manage_invoice_preferences' // Gestire preferenze fatturazione
```

---

### **FASE 3: Viste & UX per Domini (4-5 giorni)**

#### 3.1 Layout Base per Domini
```php
// Template base estendendo app-layout esistente:
user/profile/index.blade.php       // Lista + quick edit profilo
user/profile/edit.blade.php        // Form completo profilo pubblico
user/personal-data/edit.blade.php  // Form GDPR-aware dati sensitive
user/organization/edit.blade.php   // Form business registration data
user/documents/index.blade.php     // Lista documenti + upload
user/invoices/edit.blade.php       // Preferenze fatturazione
```

#### 3.2 Componenti Riutilizzabili
```php
// Blade components per consistency:
<x-domain-form-card>           // Card base per form domini
<x-gdpr-sensitive-notice>      // Notice per dati ultra-sensitive  
<x-data-retention-info>        // Info retention per dominio
<x-audit-trail-display>       // Mostra audit trail modifiche
```

#### 3.3 UX Patterns Specifici
```php
// Pattern UX per ogni dominio:
Profile: Card layout, inline editing, preview pubblico
Personal Data: Form sicuro, double confirmation, audit display
Organization: Wizard multi-step, document upload, verification status  
Documents: Gallery view, status badges, expiration alerts
Invoices: Simple form, test invoice generation, preferences
```

---

### **FASE 4: GDPR Integration & Security (2-3 giorni)**

#### 4.1 Audit Trail per Domini
```php
// Estendere AuditLogService per domini specifici:
$auditService->logDataChange(
    $user, 
    'personal_data_updated',     // Azione specifica dominio
    $changes,                    // Cosa è cambiato  
    'personal_data'              // Categoria dominio
);
```

#### 4.2 Data Export per Domini
```php
// Estendere GdprController per export granulare:
Route::get('/export-profile-data', [GdprController::class, 'exportProfileData']);
Route::get('/export-personal-data', [GdprController::class, 'exportPersonalData']);
Route::get('/export-organization-data', [GdprController::class, 'exportOrganizationData']);
```

#### 4.3 Consent Management per Domini
```php
// Consent specifici per ogni dominio:
'profile_data_processing'      // Consenso elaborazione profilo pubblico
'personal_data_processing'     // Consenso dati ultra-sensitive  
'organization_data_processing' // Consenso dati business
'document_processing'          // Consenso scan documenti + OCR
```

---

### **FASE 5: Testing & Validation (2-3 giorni)**

#### 5.1 Test Suite per Domini
```php
// Test per ogni controller + dominio:
UserProfileControllerTest.php        // CRUD + permissions + UEM
UserPersonalDataControllerTest.php   // CRUD + GDPR + audit trail
// ... etc per ogni dominio
```

#### 5.2 Integration Testing  
```php
// Test integrazione sidebar + domini:
SidebarDomainIntegrationTest.php     // Menu rendering + permissions
GdprDomainComplianceTest.php         // Export + audit trail + consent
```

---

## 🎛️ Configurazione & Customizzazione

### Menu Configuration
```php
// config/user-domains.php - configurazione centrale domini
return [
    'profile' => [
        'enabled' => true,
        'requires_verification' => false,
        'gdpr_sensitive' => false,
        'icon' => 'user-profile',
        'permissions' => ['edit_own_profile'],
    ],
    'personal_data' => [
        'enabled' => true, 
        'requires_verification' => true,
        'gdpr_sensitive' => true,
        'icon' => 'user-shield',
        'permissions' => ['edit_own_personal_data'],
        'audit_required' => true,
    ],
    // ... etc
];
```

### UEM Error Codes
```php
// In config/error-manager.php - errori specifici domini:
'USER_PROFILE_UPDATE_FAILED' => [...],
'PERSONAL_DATA_GDPR_VIOLATION' => [...], 
'ORGANIZATION_VALIDATION_FAILED' => [...],
'DOCUMENT_UPLOAD_SECURITY_ERROR' => [...],
'INVOICE_PREFERENCES_INVALID' => [...],
```

---

## 🚀 Deliverables Finali

### ✅ Risultato Atteso
1. **5 domini dati** completamente gestiti via sidebar
2. **CRUD completi** per ogni dominio con UX specifica
3. **GDPR compliance** granulare per dominio  
4. **Audit trail** completo per modifiche sensitive
5. **Permission system** granulare per accesso
6. **Error handling** robusto con UEM
7. **Export/Import** dati per dominio
8. **Testing coverage** completa

### 📊 Metriche Successo
- **Zero breaking changes** su funzionalità esistenti
- **100% GDPR compliance** per dati sensitive
- **< 200ms response time** per caricamento sidebar
- **95%+ test coverage** su nuove funzionalità
- **Accessibilità WCAG 2.1** su tutte le viste

---

## 🤔 Punti di Discussione

### 1. **Priorità Domini**
Quale dominio implementiamo per primo? Suggerisco:
1. `user-profile` (meno critico, buon testing ground)
2. `personal-data` (più complesso, GDPR critical)
3. `organization` + `documents` + `invoices`

### 2. **UX Sidebar**  
- Sottomenu collapsed di default?
- Indicatori stato completamento domini?
- Notifiche per dati mancanti/scaduti?

### 3. **Permissions Strategy**
- Permessi globali vs per-dominio?
- Admin override per supporto utenti?
- Role-based vs capability-based?

### 4. **GDPR Granularità**
- Consent separati per dominio?
- Export parziale vs completo?
- Retention policy diverse per dominio?

---

**Cosa ne pensi di questo piano? Da quale fase vuoi iniziare?** 🔥