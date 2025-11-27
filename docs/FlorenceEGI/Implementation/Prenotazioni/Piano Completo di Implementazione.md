# Piano Completo di Implementazione: Sistema di Prenotazioni EGI – FlorenceEGI

## Premessa

Questo documento descrive in modo dettagliato e sistemico il piano di implementazione del sistema di prenotazioni EGI all'interno della piattaforma FlorenceEGI. È il frutto di una co-progettazione tra Fabio e Claude, basata sui principi di Oracode 2.0, conformità GDPR, SEO avanzato e un’architettura modulare e scalabile. Il sistema rappresenta un pilastro strategico per la sostenibilità economica ed etica della piattaforma.

---

## 1. STRUTTURA DATABASE

### 1.1 Tabella `reservations` (Estensione)

- `offer_amount_eur` (decimal, 10, 2)
    
- `offer_amount_algo` (decimal, 18, 8)
    
- `expires_at` (nullable)
    
- `is_current` (boolean, default true)
    
- `superseded_by_id` (FK a reservations, nullable)
    

### 1.2 Tabella `egi_reservation_certificates`

- `id` (PK, bigint)    
- `reservation_id` (FK)    
- `egi_id` (FK)    
- `user_id` (FK nullable)    
- `wallet_address` (string)    
- `reservation_type` (enum: 'strong', 'weak')    
- `offer_amount_eur` (decimal)    
- `offer_amount_algo` (decimal)    
- `certificate_uuid` (string, unico)    
- `signature_hash` (SHA256)    
- `is_superseded` (boolean, default false)    
- `is_current_highest` (boolean, default true)    
- `pdf_path` (string)    
- `public_url` (string)    
- `created_at / updated_at` (timestamps)    

### 1.3 Indici e Constraint

- Indice su `certificate_uuid`    
- Indice su `egi_id` + `is_current_highest`    
- Indice su `wallet_address`    
- `on delete cascade` dove appropriato
    

---

## 2. MODELLI ELOQUENT

### 2.1 Modello `Reservation`

- Estensione relazioni
    
- Relazione `egiReservationCertificate()`
    
- Metodo `getOfferAmountAlgo()`
    
- Metodo `isCurrentHighest()`
    
- Metodo `getReservationPriority()`
    
- Metodo `createCertificate()`
    

### 2.2 Modello `EgiReservationCertificate`

- Relazioni: reservation, egi, user
    
- Metodo `verifySignature()`
    
- Metodo `generatePDF()`
    
- Accessor `getPublicUrlAttribute()`
    

### 2.3 Altri Modelli

- `Egi::getCurrentHighestReservation()`
    
- `User::getActiveReservations()`
    

---

## 3. SERVIZI E HELPERS

### 3.1 `ReservationService`

- `createReservation()`
    
- `updateReservationStatus()`
    
- `getReservationHistory()`
    
- `getHighestPriorityReservation()`
    
- `cancelReservation()`
    

### 3.2 `CertificateGeneratorService`

- `generateCertificate()`
    
- `generateSignatureHash()`
    
- `createPDF()`
    
- `storePDF()`
    

### 3.3 `ReservationPriorityService`

- `compareReservations()`
    
- `markSupersededReservations()`
    

### 3.4 `CurrencyService`

- `convertEurToAlgo()`
    
- `getAlgoExchangeRate()`
    

---

## 4. CONTROLLERS

### 4.1 `ReservationController`

- `reserve()` (Web)
    
- `apiReserve()` (API)
    

### 4.2 `EgiReservationCertificateController`

- `show()`
    
- `download()`
    
- `verify()`
    
- `listByEgi()`
    
- `listByUser()`
    

### 4.3 `ReservationHistoryController`

- `index()`
    
- `showEgiHistory()`
    

---

## 5. GESTIONE ERRORI – UEM

### 5.1 Codici Errore Specifici

- `RESERVATION_EGI_NOT_AVAILABLE`
    
- `RESERVATION_AMOUNT_TOO_LOW`
    
- `RESERVATION_UNAUTHORIZED`
    
- `RESERVATION_CERTIFICATE_GENERATION_FAILED`
    
- `RESERVATION_CERTIFICATE_NOT_FOUND`
    
- `RESERVATION_ALREADY_EXISTS`
    

### 5.2 Integrazione con ULM

- Configurazione in `config/error-manager.php`
    
- Logging dettagliato
    

---

## 6. FRONTEND TYPESCRIPT

### 6.1 `reservationService.ts`

- `createReservation()`
    
- `cancelReservation()`
    
- `getReservationDetails()`
    

### 6.2 `reservationUIManager.ts`

- Gestione form + feedback UI
    
- Cronologia dinamica
    

### 6.3 Componenti UI

- Modale prenotazione
    
- Badge visuali (strong/weak)
    
- Cronologia utente
    

---

## 7. VISTE BLADE

### 7.1 `reservation-form.blade.php`

- Input offerta + stima ALGO
    
- Info regole di priorità
    
- Checkbox consensi
    

### 7.2 `certificates/show.blade.php`

- Layout professionale
    
- Badge tipo prenotazione
    
- QR code
    
- Hash firma
    

### 7.3 `user/reservations/index.blade.php`

- Elenco prenotazioni attive e passate
    

### 7.4 `egis/reservations/history.blade.php`

- Timeline con offerte, priorità e stato
    

---

## 8. ROUTES

### 8.1 Web

- `POST /egis/{egi}/reserve`
    
- `GET /egi-certificates/{uuid}`
    
- `GET /egi-certificates/{uuid}/download`
    
- `GET /egi-certificates/{uuid}/verify`
    
- `GET /my-reservations`
    
- `GET /egis/{egi}/reservations/history`
    

### 8.2 API

- `POST /api/egis/{egi}/reserve`
    
- `GET /api/my-reservations`
    
- `GET /api/egis/{egi}/reservation-status`
    
- `DELETE /api/reservations/{id}`
    

---

## 9. UI/UX

### 9.1 UI Integrati

- Badge su card EGI
    
- Indicatori “EGI riservato”
    
- Animazioni conferma
    

### 9.2 Notifiche

- Email conferma + link certificato
    
- Dashboard utente
    
- Notifica creator
    

---

## 10. GDPR & SICUREZZA

### 10.1 GDPR

- Consenso esplicito
    
- Privacy Policy
    
- Annotazioni `@privacy-safe`
    

### 10.2 Sicurezza

- Validazione hash
    
- Protezione replay
    
- Rate limiting
    
- Logging conforme
    

---

## 11. SEO & SCHEMA.ORG

### 11.1 SEO

- Title e description dinamici
    
- Sitemap
    
- OpenGraph
    

### 11.2 Schema.org

- JSON-LD `Offer`, `ReservationStatus`
    
- Campi: `price`, `priceCurrency`, `validFrom`
    

---

## 12. TESTING

### 12.1 Test Unitari

- Reservation model
    
- Certificate generator
    
- Currency service
    

### 12.2 Test Funzionali

- Creazione prenotazione
    
- Generazione certificato
    
- Verifica priorità
    

### 12.3 Test UEM

- Risposte errori
    
- Validazione form
    

---

## 13. JOBS

### 13.1 Scheduler

- `UpdateReservationStatusJob`
    
- `SendReservationReminderJob`
    
- `SyncExchangeRatesJob`
    

---

## 14. DOCUMENTAZIONE

### 14.1 Sviluppatore

- PHPDoc con Oracode 2.0
    
- Guida UEM
    
- Diagrammi
    

### 14.2 Utente

- FAQ prenotazioni
    
- Guida verifica certificati
    
- Spiegazione regole priorità
    

---

## Considerazioni Finali

- **Temporalità**: valutare se introdurre validità massima di una prenotazione
    
- **Monitoraggio**: dashboard admin (strong vs weak, conversioni)
    
- **Lingue**: supporto traduzioni
    
- **Scalabilità**: gestire picchi
    
- **Fallback**: gestire errori PDF
    

---

## #TAG TEMATICI

### 🗃️ Database e struttura dati

#db #reservations #certificates #structure #foreign_keys #constraints #migrations

### 🧠 Modelli Eloquent

#eloquent #model #reservation_model #certificate_model #priority_logic #relations

### 🔧 Servizi e helpers

#service_layer #reservation_service #certificate_service #currency_service #priority_service

### 🎮 Controllers

#controllers #reservation_controller #certificate_controller #api_controller #history_controller

### 🚨 Error handling

#uem #error_codes #ulm #error_handling #laravel_logging #validation_errors

### 🧩 Frontend e TypeScript

#frontend #typescript #reservation_ui #reservation_form #reservation_ts #ts_service #ts_ui_manager

### 📄 Blade templates

#blade #views #reservation_form #certificate_view #user_dashboard #history_view

### 🌐 Routing

#routes #web_routes #api_routes #endpoint_structure #seo_urls

### 🔐 Sicurezza & GDPR

#gdpr #security #privacy #rate_limiting #replay_protection #privacy_policy

### 🔍 SEO & schema.org

#seo #schema_org #opengraph #sitemap #json_ld

### 🧪 Testing

#testing #unit_test #functional_test #oracode_tests #phpunit #uem_test

### 🛠️ Jobs & scheduler

#jobs #scheduler #reminders #exchange_sync #reservation_status

### 📝 Documentazione

#documentation #phpdoc #user_guide #developer_doc #faq #oracode_docs

### 🧭 Logica certificati

#priority #strong_vs_weak #certificate_generation #signature_hash #pdf_generator #superseded_logic

### 🌍 UI/UX e notifiche

#i18n #translations #ux_design #badges #notifications #user_experience

### 📊 Monitoraggio e admin

#admin_dashboard #metrics #reservation_monitoring #conversion_rates #analytics