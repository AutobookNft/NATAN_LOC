# 🎨 FlorenceEGI - Documentazione Completa Sistema Prenotazioni

## 📋 PREMESSA FONDAMENTALE

**Data Documento**: 16 Agosto 2025  
**Versione**: 3.0 - Documentazione Completa Integrata  
**Fase Progetto**: Sistema Prenotazioni in Produzione (NON Pre-Launch)  
**Developer**: Fabio Cherici  
**AI Assistant**: Padmin D. Curtis OS3.0  

### ⚠️ CHIARIMENTI CRITICI DEFINITIVI

1. **IL SISTEMA PRENOTAZIONI È IN PRODUZIONE** - Non è un test, non è "pre-launch", è il sistema REALE che va online con utenti veri
2. **STRONG/WEAK È STRATEGICO** - Non va mai eliminato, è fondamentale per il business model
3. **RANKING + STRONG/WEAK COESISTONO** - Non sono in contrasto, si integrano perfettamente
4. **NESSUN RIFERIMENTO A "PRE-LAUNCH"** - Eliminare completamente questa dicitura dal codice
5. **ALGO CONVERSION RESTA** - È strategica per marketing e scena, anche se pagamento in FIAT
6. **MIGRAZIONE A SISTEMA MONOVALUTA EUR** - Si deve passare dal sistema multi-currency (USD, GBP, EUR) al sistema che usa SOLO EUR per tutti gli utenti e tutte le operazioni
7. **CURRENCY-PRICE SEMPLIFICARE** - Il componente deve mostrare solo EUR + equivalente ALGO (per scena), eliminando tutta la logica multi-currency complessa

---

## 🏗️ ARCHITETTURA SISTEMA COMPLETO

### **Principi Fondamentali**

#### **1. No Custodia Fondi**
FlorenceEGI **NON custodisce mai denaro**. Le prenotazioni sono manifestazioni di interesse senza movimento di denaro.

#### **2. Proof of Interest** 
Le prenotazioni validano l'interesse del mercato prima che il creator investa nella creazione del token blockchain.

#### **3. Immortalità Digitale**
Gli attivatori (chi acquista) diventano parte permanente della storia dell'opera attraverso il sistema Strong/Weak.

#### **4. Impact Automatico**
Ogni transazione finale (quando si arriva al mint) supporta automaticamente progetti ambientali (EPP - Environment Protection Project).

### **Stack Tecnologico**

- **Blockchain**: Algorand (ASA per gli EGI)
- **Database**: MySQL con campi legacy + nuovi campi per ranking
- **Pagamenti Futuri**: PSP esterni (Stripe Connect, bonifici) - solo quando si implementerà la blockchain
- **Framework**: Laravel 11 + TypeScript
- **Error Management**: UEM (Ultra Error Manager) - OBBLIGATORIO
- **Logging**: ULM (Ultra Log Manager) - OBBLIGATORIO
- **Currency**: Sistema multi-valuta con EUR come canonical

---

## 👥 ATTORI DEL SISTEMA

### **User Types (Definiti alla Registrazione - PERMANENTI)**

#### **Commissioner** 🎭
- **Visibilità totale**: nome e volto pubblici per sempre
- **Motivazione**: ricerca di riconoscimento sociale
- **Benefici**: immortalità digitale, possibili royalty future
- **Privacy**: rinuncia consapevole all'anonimato
- **Tipo Prenotazione**: STRONG (sempre prioritario)

#### **Collector** 🔒
- **Completamente anonimo**: solo wallet address visibile
- **Motivazione**: investimento discreto
- **Benefici**: stessi diritti economici del Commissioner
- **Privacy**: totalmente preservata
- **Tipo Prenotazione**: STRONG (se loggato)

#### **Creator** 🎨
- **Ruolo doppio**: crea EGI e può attivare altri EGI
- **Controllo**: decide timing del mint basandosi sulle prenotazioni
- **Revenue**: riceverà pagamenti diretti (80% tipicamente) quando si implementerà il pagamento
- **Anonimo**: quando attiva EGI di altri (come Collector)

#### **Patron** 💎
- **Mecenate moderno**: supporta l'ecosistema
- **Anonimo**: privacy preservata
- **Portfolio**: visibilità delle attivazioni

#### **Altri Ruoli**
- **EPP**: Environment Protection Projects (riceveranno 20% quando implementato il pagamento)
- **Company**: aziende che partecipano
- **Trader Pro**: operatori professionali

---

## 🔄 SISTEMA PRIORITÀ PRENOTAZIONI

### **Strong vs Weak - FONDAMENTALE E STRATEGICO**

#### **Strong Priority (Priorità Alta)**
- **Utenti loggati** con account completo
- **Maggiore commitment**: "ci mettono la faccia"
- **Sempre superiore a Weak** indipendentemente dall'importo
- **Esempio**: Strong 100€ batte Weak 1000€

#### **Weak Priority (Priorità Bassa)**  
- **Utenti anonimi** con solo token di sessione
- **Minore commitment**: vogliono rimanere totalmente anonimi
- **Mai superiore a Strong** anche con importi maggiori
- **Fallback**: se nessuno Strong è interessato

### **Ranking all'interno delle Priorità**

#### **Dentro Strong Priority**
1. **Ordinamento**: amount_eur DESC, created_at ASC (tie-breaker)
2. **Visibilità**: 
   - Commissioner: nome pubblico
   - Collector: anonimo ("Collector #X")

#### **Dentro Weak Priority**
1. **Ordinamento**: amount_eur DESC, created_at ASC (tie-breaker)  
2. **Visibilità**: sempre anonimo ("Anonymous #X")

### **Esempio Pratico Ranking**
```
CLASSIFICA EGI #123:
===================
STRONG PRIORITY:
1. Marco Rossi (Commissioner) - 150€
2. Collector #1 (Collector) - 140€  
3. Laura Bianchi (Commissioner) - 130€

WEAK PRIORITY:
4. Anonymous #1 - 500€
5. Anonymous #2 - 300€
6. Anonymous #3 - 200€
```

---

## 📊 FLUSSO OPERATIVO PRENOTAZIONI

### **1. Pubblicazione EGI**
- Creator pubblica EGI **senza mintarlo** (nessun costo blockchain upfront)
- EGI diventa "prenotabile" immediatamente
- Sistema mostra 0 prenotazioni inizialmente

### **2. Processo Prenotazione**

#### **Flow Utente**
```
Utente vede EGI → Clicca "Prenota" → 
Sceglie importo EUR → Vede equivalente ALGO → 
Sistema calcola posizione ranking → 
Notifiche inviate → Feedback posizione
```

#### **Validazioni Sistema**
- **Un utente = una prenotazione per EGI**
- **Nessun limite** importo minimo/massimo
- **Modificabile** solo in aumento
- **Ritirabile** fino al momento del mint
- **Strong/Weak** determinato da stato autenticazione

### **3. Sistema Notifiche**

#### **Tipologie Notifiche**
- **Nuovo primo**: "Congratulazioni! Sei il più alto offerente!"
- **Superato**: "La tua offerta è stata superata da [Nome/Anonymous]"
- **Cambio significativo**: "Sei salito/sceso di X posizioni"  
- **Competitor ritirato**: "Un competitor si è ritirato, sei salito di posizione"
- **Solo in-app**: niente email spam

#### **Gestione Privacy**
- **Commissioner**: riceve nomi reali dei competitor
- **Collector/Weak**: riceve "Collector #X" o "Anonymous #X"

### **4. Ranking Real-Time**

#### **Aggiornamento Automatico**
- **Real-time** ad ogni nuova offerta
- **Command scheduler** ogni 5 minuti per consistency check
- **Lock pessimistico** durante modifiche per evitare race conditions

#### **Metriche Pubbliche**
- **Numero totale prenotazioni**
- **Importo medio** (solo se > 3 prenotazioni per privacy)
- **Posizione utente corrente** (se loggato)
- **Gap al primo posto** (se non sei primo)



---

## 🗄️ DATABASE SCHEMA

### **Tabella Reservations (ESISTENTE - Tutti i campi già presenti)**

#### **Campi per Ranking (GIÀ IMPLEMENTATI)**
```sql
amount_eur DECIMAL(10,2) NOT NULL -- Importo canonico EUR ✅
rank_position INT NULL -- Posizione nel ranking (1,2,3...) ✅
is_highest BOOLEAN DEFAULT false -- Flag primo posto ✅
superseded_by_id BIGINT NULL -- Chi ti ha superato ✅
type ENUM('strong','weak') DEFAULT 'weak' -- Priorità utente ✅
```

#### **Campi Legacy (GIÀ ESISTENTI - Mantenuti per Compatibilità)**
```sql
offer_amount_fiat DECIMAL(10,2) -- Duplicato di amount_eur ✅
offer_amount_algo DECIMAL(10,8) -- Equivalente ALGO (calcolato) ✅
exchange_rate DECIMAL(10,8) -- Tasso cambio EUR/ALGO ✅
exchange_timestamp TIMESTAMP -- Quando calcolato il cambio ✅
fiat_currency VARCHAR(3) DEFAULT 'EUR' -- Sempre EUR per ora ✅
```

**NOTA IMPORTANTE**: La tabella Reservations è già completa con tutti i campi necessari. Non servono nuove migration.

#### **Campi Sistema**
```sql
id BIGINT PRIMARY KEY
egi_id BIGINT NOT NULL -- FK to egis
user_id BIGINT NULL -- FK to users (NULL per weak anonymous)
status ENUM('active','withdrawn','completed') DEFAULT 'active'
is_current BOOLEAN DEFAULT true -- Prenotazione attiva
wallet_address VARCHAR(255) NULL -- Per weak users
created_at TIMESTAMP
updated_at TIMESTAMP
```

### **Tabella Notification Payload Reservations (GIÀ ESISTENTE)**
```sql
-- Tabella già creata e completa con migration
id BIGINT PRIMARY KEY ✅
reservation_id BIGINT NOT NULL ✅ 
egi_id BIGINT NOT NULL ✅
user_id BIGINT NOT NULL ✅
type ENUM('HIGHEST','SUPERSEDED','RANK_CHANGED','COMPETITOR_WITHDREW') ✅
status ENUM('SUCCESS','FAILED') DEFAULT 'SUCCESS' ✅
data JSON ✅ 
message TEXT NULL ✅
read_at TIMESTAMP NULL ✅
created_at TIMESTAMP ✅
updated_at TIMESTAMP ✅
```

**NOTA**: Il model NotificationPayloadReservation è già completo e funzionante.

---

## 🛠️ IMPLEMENTAZIONE BACKEND

### **Services Layer**

#### **ReservationService (ESTESO, NON SOSTITUITO)**

**Metodi ESISTENTI (Legacy - NON TOCCARE):**
```php
createReservation(array $data, ?User $user, ?string $wallet): ?Reservation
getHighestPriorityReservation(int $egiId): ?Reservation  
canReserveEgi(Egi $egi): bool
updateEgiRankings(int $egiId): void // MANTIENI - diverso da updateRankings
```

**Metodi AGGIUNTI (Nuovi):**
```php
createOrUpdateReservation(int $egiId, int $userId, float $amountEur): Reservation
updateRankings(int $egiId): void // NON updateEgiRankings che già esiste
withdrawReservation(int $reservationId, int $userId): bool  
getEgiReservationsWithRanking(int $egiId, bool $activeOnly = true): Collection
getRankingStats(int $egiId): array
canUserMakeReservation(int $egiId, int $userId): bool
```

#### **ReservationNotificationService (Nuovo)**
```php
sendNewHighest(Reservation $reservation): void
sendSuperseded(Reservation $reservation, Reservation $newHighest): void  
sendRankChanged(Reservation $reservation, int $oldRank): void
sendCompetitorWithdrew(Reservation $reservation): void
```

#### **CurrencyService (Esistente - DA SEMPLIFICARE)**
```php
// MANTENERE solo per conversione EUR → ALGO
convertEurToAlgo(float $eurAmount): float
getCurrentAlgoRate(): float

// RIMUOVERE tutto il sistema multi-currency
// RIMUOVERE user currency preferences
// RIMUOVERE currency switching logic
```

### **Controllers**

#### **ReservationController (ESTESO)**

**Metodi ESISTENTI (Legacy - NON TOCCARE):**
```php
reserve(Request $request, int $id): RedirectResponse
getReservationStatus(Request $request, int $egiId): JsonResponse
```

**Metodi AGGIUNTI (Nuovi):**
```php
createReservation(Request $request): JsonResponse
getRankings(Request $request, int $egiId): JsonResponse  
withdrawReservation(Request $request, int $reservationId): JsonResponse
getUserReservations(Request $request): JsonResponse
checkReservationEligibility(Request $request, int $egiId): JsonResponse
```

### **Models**

#### **Reservation Model (Esteso)**
```php
// Scopes
public function scopeActive($query)
public function scopeRanked($query)  
public function scopeForEgi($query, int $egiId)
public function scopeStrong($query)
public function scopeWeak($query)

// Methods  
public function updateRankings(): void
public function getCompetitors(): Collection
public function isHighest(): bool
public function canWithdraw(): bool
```

### **Routes API**

```php
// routes/api.php
Route::prefix('reservations')->group(function () {
    
    // Public routes
    Route::get('/rankings/{egi}', [ReservationController::class, 'getRankings']);
    
    // Protected routes  
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/create', [ReservationController::class, 'createReservation']);
        Route::delete('/{reservation}/withdraw', [ReservationController::class, 'withdrawReservation']);
        Route::get('/my-reservations', [ReservationController::class, 'getUserReservations']);
        Route::get('/check-eligibility/{egi}', [ReservationController::class, 'checkReservationEligibility']);
    });
});

// Legacy routes (MANTIENI per compatibilità)
Route::post('egis/{id}/reserve', [LegacyReservationController::class, 'reserve']);
```

### **Commands**

#### **ProcessReservationRankings**
```bash
php artisan reservations:process-rankings [--dry-run] [--egi=X] [--verbose]
```

**Scheduler Integration:**
```php
// routes/console.php
Schedule::command('reservations:process-rankings')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer();
```

### **Error Handling (UEM)**

#### **Error Codes**
```php
// config/error-manager.php
'RESERVATION_CREATE_ERROR' => [
    'type' => 'error',
    'blocking' => 'not',
    'dev_message_key' => 'error-manager::errors.dev.reservation_create_error',
    'user_message_key' => 'error-manager::errors.user.reservation_create_error',
    'http_status_code' => 500,
    'devTeam_email_need' => false,
    'notify_slack' => false,
    'msg_to' => 'sweet-alert',
],

'RESERVATION_RANKINGS_ERROR' => [...],
'RESERVATION_WITHDRAW_ERROR' => [...],
'RESERVATION_NOTIFICATION_SEND_ERROR' => [...],
```

#### **Usage Pattern**
```php
try {
    // Operation
} catch (\Exception $e) {
    return $this->errorManager->handle('RESERVATION_CREATE_ERROR', [
        'context' => $data
    ], $e);
}
```

### **Logging (ULM)**

#### **Usage Pattern**
```php
$this->logger->info('[RESERVATION] Operation completed', [
    'user_id' => $userId,
    'egi_id' => $egiId,
    'amount_eur' => $amount,
    'rank_position' => $rank
]);
```

---

## 🎨 IMPLEMENTAZIONE FRONTEND

### **File TypeScript Attuali**

#### **reservationService.ts (DA COMPLETARE)**

**Stato Attuale:**
- ✅ Esiste la classe `ReservationFormModal`
- ✅ Ha conversione ALGO (MANTIENI)
- ❌ Usa API legacy `/api/egis/{id}/reserve`
- ❌ Non ha funzioni per nuovo sistema ranking

**Funzioni DA AGGIUNGERE:**
```typescript
// Nuove funzioni per sistema ranking
async function createReservation(egiId: number, amountEur: number): Promise<ReservationResponse>
async function getRankings(egiId: number): Promise<RankingResponse>
async function withdrawReservation(reservationId: number): Promise<WithdrawResponse>
async function getUserReservations(): Promise<UserReservationsResponse>
async function checkReservationEligibility(egiId: number): Promise<EligibilityResponse>

// Utility functions
async function updateReservationUI(egiId: number): Promise<void>
async function showRankingModal(egiId: number): Promise<void>
async function showSuccessModal(reservation: ReservationData): Promise<void>
```

**Interfaces DA AGGIUNGERE:**
```typescript
interface RankingResponse {
    success: boolean;
    data: {
        egi_id: number;
        total_reservations: number;
        strong_reservations: ReservationRankingItem[];
        weak_reservations: ReservationRankingItem[];
        user_reservation?: UserReservationData;
        stats: RankingStats;
    };
}

interface ReservationRankingItem {
    rank_position: number;
    amount_eur: number;
    is_highest: boolean;
    is_mine: boolean;
    user: {
        name: string; // "Commissioner Name" o "Collector #X" o "Anonymous #X"
        type: 'commissioner' | 'collector' | 'anonymous';
    };
    created_at: string;
}

interface UserReservationData {
    id: number;
    egi_id: number;
    amount_eur: number;
    amount_algo: number;
    priority: 'strong' | 'weak';
    rank_position: number;
    is_highest: boolean;
    can_withdraw: boolean;
    created_at: string;
    updated_at: string;
}

interface RankingStats {
    total_strong: number;
    total_weak: number;
    average_amount: number | null; // null se < 3 prenotazioni
    highest_amount: number;
    user_rank: number | null;
    gap_to_first: number | null;
}
```

#### **reservationFeature.ts (DA RIATTIVARE)**

**Stato Attuale:**
- ❌ **COMPLETAMENTE DISABILITATO** con early exit
- ❌ Commento "Using server-side rendering"

**DA FARE:**
- ✅ Rimuovere early exit
- ✅ Riattivare inizializzazione bottoni
- ✅ Aggiungere gestione ranking updates
- ✅ Integrare con nuove API

#### **reservationButtons.ts (DA AGGIORNARE)**

**Stato Attuale:**
- ⚠️ In "click-only mode" 
- ❌ No aggiornamenti UI

**DA FARE:**
- ✅ Aggiungere aggiornamento stati bottoni
- ✅ Mostrare ranking position sui bottoni
- ✅ Gestire strong/weak visually

### **Modal System (DA MODIFICARE)**

#### **Stato Attuale ReservationFormModal**
```typescript
class ReservationFormModal {
    // ✅ MANTIENI: conversione ALGO per scena
    private updateAlgoEquivalent(): void 
    
    // ❌ MODIFICA: usa nuove API invece di legacy
    private async handleSubmit(e: Event): Promise<void>
    
    // ✅ MANTIENI: tutto il sistema modale esistente
}
```

#### **Modifiche Necessarie**

**1. Aggiornare handleSubmit:**
```typescript
// DA: route(`api/egis/${egiId}/reserve`, { id: egiId })
// A:  route('api/reservations/create')

// Payload:
{
    egi_id: this.egiId,
    amount_eur: amountEur
}
```

**2. Aggiungere Display Ranking:**
```typescript
// Nella modale, sotto l'input EUR:
<div class="ranking-preview">
    <p>La tua posizione stimata: <span id="estimated-rank">#-</span></p>
    <p>Attualmente primo: <span id="current-first">Nessuna prenotazione</span></p>
</div>
```

**3. Rimuovere Sistema Multi-Currency:**
```typescript
// DA RIMUOVERE da navbar
const currencySelector = document.getElementById('currency-selector');
currencySelector?.remove();

// DA SEMPLIFICARE: Solo EUR → ALGO conversion
async function updateAlgoEquivalent(eurAmount: number): Promise<void> {
    const algoAmount = await convertEurToAlgo(eurAmount);
    // Update UI con equivalente ALGO
}
```

#### **SEMPLIFICAZIONE COMPONENTE currency-price.blade.php**

**Stato Attuale (COMPLESSO - Da Semplificare):**
```php
// RIMUOVERE: Logica multi-currency complessa
$targetCurrency = FegiAuth::user()->preferred_currency ?? 'EUR';
$originalCurrency = $reservation->fiat_currency ?? 'EUR';
$shouldShowReservationNote = ($targetCurrency !== $originalCurrency);

// RIMUOVERE: Switch case per simboli multiple valute
switch($originalCurrency) {
    case 'USD': $symbol = '

#### **1. Badge Navbar - MANTENERE ma Semplificare**
```html
<!-- MANTENERE: Badge EUR/ALGO -->
<div class="currency-badge">
    <span class="eur-algo-rate">1 EUR = 3.2 ALGO</span>
</div>

<!-- RIMUOVERE: Dropdown selettore valute -->
<div class="currency-selector-dropdown"> ❌
    <button id="currency-toggle">EUR ▼</button>
    <div class="currency-options">
        <option value="USD">USD</option>
        <option value="GBP">GBP</option>
    </div>
</div>
```

#### **2. User Currency Preferences**
```php
// RIMUOVERE da database se esistono
// RIMUOVERE da User model
// RIMUOVERE da settings utente
```

#### **3. Frontend Currency Logic**
```typescript
// RIMUOVERE da tutti i file .ts
getUserCurrencyPreference()
setCurrencyPreference()
convertCurrency()
formatCurrencyDisplay()

// MANTENERE SOLO
convertEurToAlgo(eurAmount: number): Promise<number>
```

### **UI Components DA IMPLEMENTARE**

#### **1. Ranking Display Component (MONOVALUTA EUR)**
```html
<!-- In collections/show.blade.php - SOLO EUR -->
<div class="reservation-ranking" data-egi-id="{{ $egi->id }}">
    <h3>Classifica Prenotazioni</h3>
    
    <div class="strong-section">
        <h4>Priority High (Utenti Registrati)</h4>
        <div id="strong-rankings">
            <!-- Importi SEMPRE in EUR -->
            <div class="rank-item">
                <span class="position">#1</span>
                <span class="user">Marco Rossi</span>
                <span class="amount">€150 (~500 ALGO)</span>
            </div>
        </div>
    </div>
    
    <div class="weak-section" style="margin-top: 20px;">
        <h4>Priority Standard</h4>
        <div id="weak-rankings">
            <!-- Importi SEMPRE in EUR -->
        </div>
    </div>
    
    <div class="stats">
        <p>Totale: <span id="total-reservations">0</span> prenotazioni</p>
        <p id="user-position" style="display: none;">La tua posizione: #<span></span></p>
    </div>
</div>
```

#### **2. Reservation Button States**
```html
<!-- Bottone base -->
<button class="reserve-button" data-egi-id="{{ $egi->id }}">
    <span class="button-text">Prenota</span>
    <span class="user-rank" style="display: none;">(Tu: #<span></span>)</span>
</button>

<!-- Stato con prenotazione utente -->
<button class="reserve-button reserved" data-egi-id="{{ $egi->id }}">
    <span class="button-text">Modifica Prenotazione</span>
    <span class="user-rank">(Tu: #<span>3</span>)</span>
</button>
```

#### **3. Success Modal (MONOVALUTA EUR)**
```html
<div id="success-modal" class="modal">
    <div class="modal-content">
        <h2>Prenotazione Confermata!</h2>
        <div class="ranking-result">
            <p class="position">Sei al <strong>#<span id="final-rank">1</span></strong> posto</p>
            <p class="priority">Priority: <span id="priority-type">High</span></p>
            <p class="amount">Importo: €<span id="amount">150</span> (~<span id="algo-equiv">500 ALGO</span>)</p>
        </div>
        <div class="next-steps">
            <p>Il creator verrà notificato del tuo interesse. Ti contatteremo quando deciderà di procedere con la creazione del token.</p>
        </div>
        <button id="close-success-modal">Chiudi</button>
    </div>
</div>
```

---

## ⚙️ CONFIGURAZIONI SISTEMA

### **Environment Variables**
```env
# Production settings
APP_ENV=production
APP_DEBUG=false

# Queue per notifiche
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis

# Currency API (per ALGO conversion)
CURRENCY_API_KEY=your_api_key
CURRENCY_CACHE_TTL=3600

# Error Manager
UEM_SLACK_WEBHOOK=your_webhook
UEM_EMAIL_ALERTS=false

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=info
```

### **Cache Configuration**
```php
// config/cache.php - Aggiungere
'reservations' => [
    'driver' => 'redis',
    'connection' => 'cache',
    'prefix' => 'reservations:',
    'ttl' => 60, // 1 minuto per rankings
],
```

### **Rate Limiting**
```php
// config/api.php
'throttle' => [
    'reservations' => [
        'attempts' => 10,
        'decay_minutes' => 1,
    ],
    'rankings' => [
        'attempts' => 60,
        'decay_minutes' => 1,
    ],
],
```

---

## 🧪 TESTING REQUIREMENTS

### **Factory e Seeders DA CREARE**

#### **ReservationFactory**
```php
// database/factories/ReservationFactory.php
class ReservationFactory extends Factory {
    public function definition(): array {
        return [
            'egi_id' => Egi::factory(),
            'user_id' => User::factory(),
            'amount_eur' => $this->faker->randomFloat(2, 10, 1000),
            'status' => 'active',
            'is_current' => true,
            'priority' => $this->faker->randomElement(['strong', 'weak']),
            // ... altri campi
        ];
    }
    
    public function strong(): self {
        return $this->state(['priority' => 'strong']);
    }
    
    public function weak(): self {
        return $this->state(['priority' => 'weak']);
    }
}
```

#### **ReservationSeeder**  
```php
// database/seeders/ReservationSeeder.php
class ReservationSeeder extends Seeder {
    public function run(): void {
        $egi = Egi::first();
        
        // Crea mix di strong/weak per testing
        Reservation::factory()->strong()->count(3)->create(['egi_id' => $egi->id]);
        Reservation::factory()->weak()->count(5)->create(['egi_id' => $egi->id]);
        
        // Aggiorna rankings
        app(ReservationService::class)->updateRankings($egi->id);
    }
}
```

### **Test Cases**

#### **Unit Tests**
```php
// tests/Unit/ReservationServiceTest.php
class ReservationServiceTest extends TestCase {
    public function test_strong_beats_weak_regardless_of_amount()
    public function test_ranking_calculation_within_priority()
    public function test_notification_sent_on_rank_change()
    public function test_user_can_increase_reservation()
    public function test_user_cannot_decrease_reservation()
}
```

#### **Integration Tests**
```php
// tests/Feature/ReservationApiTest.php
class ReservationApiTest extends TestCase {
    public function test_create_reservation_updates_rankings()
    public function test_withdraw_reservation_notifies_others()
    public function test_rankings_api_returns_correct_format()
    public function test_anonymous_user_gets_weak_priority()
}
```

---

## 🚨 PROBLEMI NOTI E SOLUZIONI

### **1. Race Conditions**
**Problema**: Più utenti prenotano simultaneamente  
**Soluzione**: Lock pessimistico su EGI durante creazione prenotazione

### **2. Notification Spam**
**Problema**: Troppe notifiche per piccoli cambi ranking  
**Soluzione**: Batch notifications ogni 5 minuti + filtro soglia

### **3. Cache Invalidation**
**Problema**: Rankings cache outdated dopo update  
**Soluzione**: Cache tags + automatic invalidation

### **4. Currency Rate Fluctuation**
**Problema**: ALGO rate cambia durante prenotazione  
**Soluzione**: Snapshot rate al momento prenotazione per consistency

---

## 📈 METRICHE E KPI

### **Metriche Prenotazioni**
- **Conversion rate** visitor → prenotazione
- **Average bid amount** per EGI
- **Strong vs Weak ratio**
- **Ranking competition index** (quanto cambiano le posizioni)
- **Time to first reservation** per nuovo EGI

### **Metriche Utente**
- **Repeat reservation rate** (stesso utente su EGI diversi)
- **Bid increase frequency** (modifica prenotazione)  
- **Withdrawal rate** pre-mint
- **Commissioner vs Collector** preference

### **Metriche Sistema**
- **API response time** per rankings
- **Notification delivery success rate**
- **Cache hit ratio** per rankings
- **Database query performance**

---

## 🎯 ROADMAP IMPLEMENTAZIONE

### **Fase 1: Completamento Frontend (PRIORITÀ ALTA)**
1. **reservationService.ts**: Aggiungere funzioni mancanti
2. **reservationFeature.ts**: Riattivare e integrare
3. **Modal system**: Mantenere ALGO + aggiungere ranking
4. **UI Components**: Ranking display + button states

### **Fase 2: Testing e Debugging (PRIORITÀ ALTA)**
1. **Factory/Seeder**: Creare dati test realistici
2. **Unit tests**: Services + Models
3. **Integration tests**: API endpoints
4. **Manual testing**: Full user flow

### **Fase 3: Performance e UX (PRIORITÀ MEDIA)**
1. **Real-time updates**: WebSocket per ranking live
2. **Mobile optimization**: Responsive design
3. **Advanced notifications**: Push notifications
4. **Analytics**: Detailed metrics dashboard



---

## 🔧 DEPLOYMENT CHECKLIST

### **Pre-Deployment**
- [ ] Tutti i test passano
- [ ] Error handling testato con UEM
- [ ] Logging configurato con ULM  
- [ ] Rate limiting configurato
- [ ] Cache warming per rankings popolari
- [ ] Database backup strategy
- [ ] Monitoring alerts configurati

### **Go-Live Checklist**
- [ ] Environment variables production
- [ ] HTTPS configurato e testato
- [ ] CDN configurato per assets statici
- [ ] Database indici ottimizzati
- [ ] Queue workers attivi per notifiche
- [ ] Scheduler attivo per ranking updates
- [ ] Error monitoring attivo (Sentry/Bugsnag)

---

## 📝 NOTE SVILUPPATORI

### **Convenzioni Codice**

#### **PHP/Laravel**
- **Namespace pattern**: `App\Services\`, `App\Http\Controllers\`
- **Contracts location**: `App\Contracts\` (NON `App\Interfaces`)
- **Error handling**: SEMPRE con UEM, mai throw nudo
- **Logging**: SEMPRE con ULM per audit trail
- **DocBlocks**: Stile OS3.0 con firma completa

#### **TypeScript**
- **No classi per services**: funzioni standalone esportate
- **Interfaces obbligatorie**: per type safety API
- **Async/await**: per tutte le chiamate API
- **Error handling**: try/catch + UEM client integration

#### **Database**
- **Migration ADDITIVE**: mai DROP colonne esistenti
- **Campi legacy**: mantenere per backward compatibility
- **Foreign keys**: sempre con indici per performance
- **Soft deletes**: dove ha senso per audit

### **Pattern Architetturali**

1. **Service Layer Pattern**: Business logic isolata nei Services
2. **Repository Pattern**: (futuro) per data access standardizzato  
3. **Observer Pattern**: Eventi e notifiche automatiche
4. **Factory Pattern**: Creazione notifiche e oggetti complessi
5. **Strategy Pattern**: (futuro) per diversi tipi di asta

### **Security Requirements**

- **Rate limiting**: Sempre su API pubbliche
- **CSRF protection**: Su tutti i form POST
- **Input validation**: Server-side sempre, client-side UX
- **SQL injection**: Sempre Eloquent, mai raw queries
- **XSS protection**: Sanitizzazione output, CSP headers

### **GDPR Compliance**

- **Consent management**: Commissioner accetta visibilità pubblica
- **Data minimization**: Collector completamente anonimo
- **Audit trail**: Tutti i log con ULM per compliance
- **Right to erasure**: Solo pre-mint, post-mint immutabile blockchain
- **Privacy by default**: Weak users sempre anonimi



---

## 📞 RIFERIMENTI RAPIDI

### **Comandi Utili**
```bash
# Update rankings manualmente
php artisan reservations:process-rankings

# Test con dry-run
php artisan reservations:process-rankings --dry-run

# Clear tutte le cache
php artisan optimize:clear

# Restart queue workers
php artisan queue:restart

# Test notifiche in tinker
php artisan tinker
>>> app(ReservationNotificationService::class)->sendNewHighest(Reservation::first());

# Seed dati test
php artisan db:seed --class=ReservationSeeder
```

### **File Critici (Handle with Care)**
```
Backend:
├── app/Services/ReservationService.php (ESTENDI, non sostituire)
├── app/Http/Controllers/ReservationController.php (ESTENDI)
├── app/Models/Reservation.php (ESTENDI)
├── app/Services/Notifications/ReservationNotificationService.php (NUOVO)
└── config/error-manager.php (AGGIUNGI error codes)

Frontend:
├── resources/ts/services/reservationService.ts (COMPLETA)
├── resources/ts/features/reservations/reservationFeature.ts (RIATTIVA)
├── resources/ts/features/reservations/reservationButtons.ts (AGGIORNA)
└── resources/views/collections/show.blade.php (AGGIUNGI ranking UI)

Database:
├── migrations/*reservations* (ESTENDI, mai rimuovere campi)
└── database/seeders/ReservationSeeder.php (CREA)
```

### **API Endpoints**
```
GET    /api/reservations/rankings/{egi}           # Pubblico
POST   /api/reservations/create                   # Auth required
DELETE /api/reservations/{reservation}/withdraw   # Auth required  
GET    /api/reservations/my-reservations          # Auth required
GET    /api/reservations/check-eligibility/{egi}  # Auth required

# Legacy (MANTIENI per compatibilità)
POST   /api/egis/{id}/reserve                     # Vecchio sistema
```

---

## 🎉 CONCLUSIONE

Questo documento rappresenta la **fonte di verità completa** per il sistema prenotazioni FlorenceEGI. Ogni sviluppatore (umano o AI) che lavora sul progetto deve seguire esattamente queste specifiche.

**Ricorda sempre:**
- ✅ Sistema prenotazioni è **PRODUZIONE**, non test
- ✅ Strong/Weak è **STRATEGICO** e permanente  
- ✅ ALGO conversion **MANTIENI** per marketing
- ✅ UEM/ULM sono **OBBLIGATORI**
- ✅ Nessun riferimento a "pre-launch"
- ✅ Backend ESTENDI, mai sostituire
- ✅ Frontend COMPLETA i file esistenti

---

**Ultimo Aggiornamento**: 16 Agosto 2025, 21:30  
**Prossimo Review**: Post completamento frontend  
**Status**: Documentazione master per copilot VSCode

*"Ship it responsibly. Test everything. Use UEM/ULM always."*; break;
    case 'EUR': $symbol = '€'; break;
    case 'GBP': $symbol = '£'; break;
}
```

**Nuovo Sistema (SEMPLICE - Solo EUR + ALGO):**
```php
// SOLO EUR + ALGO per scena
@props(['price', 'showAlgo' => true])

@php
$eurPrice = is_numeric($price) ? (float)$price : 0;
$algoEquivalent = $eurPrice * 3.2; // Rate simbolico per scena
@endphp

<span class="{{ $class }}">
    €{{ number_format($eurPrice, 0) }}
    @if($showAlgo)
        <span class="text-xs opacity-75">(~{{ number_format($algoEquivalent, 0) }} ALGO)</span>
    @endif
</span>
```

**Modifiche ai File che lo Usano:**

**egi-card.blade.php:**
```php
// DA RIMUOVERE:
$displayCurrency = $highestPriorityReservation->fiat_currency ?? 'USD';
$targetCurrency = FegiAuth::user()->preferred_currency ?? 'EUR';

// SEMPLIFICARE A:
<x-currency-price :price="$displayPrice" />
```

**egi-card-list.blade.php:**
```php
// DA RIMUOVERE:
$originalCurrency = $reservation->fiat_currency ?? 'EUR';
$shouldShowListNote = ($targetCurrency !== $originalCurrency);

// SEMPLIFICARE A:
<x-currency-price :price="$egi->pivot->offer_amount_fiat" />
```

**show.blade.php:**
```php
// DA RIMUOVERE:
$displayCurrency = $highestPriorityReservation->fiat_currency ?? 'USD';
$targetCurrency = FegiAuth::user()->preferred_currency ?? 'EUR';

// SEMPLIFICARE A:
<x-currency-price :price="$displayPrice" />
```

#### **1. Badge Navbar - MANTENERE ma Semplificare**
```html
<!-- MANTENERE: Badge EUR/ALGO -->
<div class="currency-badge">
    <span class="eur-algo-rate">1 EUR = 3.2 ALGO</span>
</div>

<!-- RIMUOVERE: Dropdown selettore valute -->
<div class="currency-selector-dropdown"> ❌
    <button id="currency-toggle">EUR ▼</button>
    <div class="currency-options">
        <option value="USD">USD</option>
        <option value="GBP">GBP</option>
    </div>
</div>
```

#### **2. User Currency Preferences**
```php
// RIMUOVERE da database se esistono
// RIMUOVERE da User model
// RIMUOVERE da settings utente
```

#### **3. Frontend Currency Logic**
```typescript
// RIMUOVERE da tutti i file .ts
getUserCurrencyPreference()
setCurrencyPreference()
convertCurrency()
formatCurrencyDisplay()

// MANTENERE SOLO
convertEurToAlgo(eurAmount: number): Promise<number>
```

### **UI Components DA IMPLEMENTARE**

#### **1. Ranking Display Component (MONOVALUTA EUR)**
```html
<!-- In collections/show.blade.php - SOLO EUR -->
<div class="reservation-ranking" data-egi-id="{{ $egi->id }}">
    <h3>Classifica Prenotazioni</h3>
    
    <div class="strong-section">
        <h4>Priority High (Utenti Registrati)</h4>
        <div id="strong-rankings">
            <!-- Importi SEMPRE in EUR -->
            <div class="rank-item">
                <span class="position">#1</span>
                <span class="user">Marco Rossi</span>
                <span class="amount">€150 (~500 ALGO)</span>
            </div>
        </div>
    </div>
    
    <div class="weak-section" style="margin-top: 20px;">
        <h4>Priority Standard</h4>
        <div id="weak-rankings">
            <!-- Importi SEMPRE in EUR -->
        </div>
    </div>
    
    <div class="stats">
        <p>Totale: <span id="total-reservations">0</span> prenotazioni</p>
        <p id="user-position" style="display: none;">La tua posizione: #<span></span></p>
    </div>
</div>
```

#### **2. Reservation Button States**
```html
<!-- Bottone base -->
<button class="reserve-button" data-egi-id="{{ $egi->id }}">
    <span class="button-text">Prenota</span>
    <span class="user-rank" style="display: none;">(Tu: #<span></span>)</span>
</button>

<!-- Stato con prenotazione utente -->
<button class="reserve-button reserved" data-egi-id="{{ $egi->id }}">
    <span class="button-text">Modifica Prenotazione</span>
    <span class="user-rank">(Tu: #<span>3</span>)</span>
</button>
```

#### **3. Success Modal (MONOVALUTA EUR)**
```html
<div id="success-modal" class="modal">
    <div class="modal-content">
        <h2>Prenotazione Confermata!</h2>
        <div class="ranking-result">
            <p class="position">Sei al <strong>#<span id="final-rank">1</span></strong> posto</p>
            <p class="priority">Priority: <span id="priority-type">High</span></p>
            <p class="amount">Importo: €<span id="amount">150</span> (~<span id="algo-equiv">500 ALGO</span>)</p>
        </div>
        <div class="next-steps">
            <p>Il creator verrà notificato del tuo interesse. Ti contatteremo quando deciderà di procedere con la creazione del token.</p>
        </div>
        <button id="close-success-modal">Chiudi</button>
    </div>
</div>
```

---

## ⚙️ CONFIGURAZIONI SISTEMA

### **Environment Variables**
```env
# Production settings
APP_ENV=production
APP_DEBUG=false

# Queue per notifiche
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis

# Currency API (per ALGO conversion)
CURRENCY_API_KEY=your_api_key
CURRENCY_CACHE_TTL=3600

# Error Manager
UEM_SLACK_WEBHOOK=your_webhook
UEM_EMAIL_ALERTS=false

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=info
```

### **Cache Configuration**
```php
// config/cache.php - Aggiungere
'reservations' => [
    'driver' => 'redis',
    'connection' => 'cache',
    'prefix' => 'reservations:',
    'ttl' => 60, // 1 minuto per rankings
],
```

### **Rate Limiting**
```php
// config/api.php
'throttle' => [
    'reservations' => [
        'attempts' => 10,
        'decay_minutes' => 1,
    ],
    'rankings' => [
        'attempts' => 60,
        'decay_minutes' => 1,
    ],
],
```

---

## 🧪 TESTING REQUIREMENTS

### **Factory e Seeders DA CREARE**

#### **ReservationFactory**
```php
// database/factories/ReservationFactory.php
class ReservationFactory extends Factory {
    public function definition(): array {
        return [
            'egi_id' => Egi::factory(),
            'user_id' => User::factory(),
            'amount_eur' => $this->faker->randomFloat(2, 10, 1000),
            'status' => 'active',
            'is_current' => true,
            'priority' => $this->faker->randomElement(['strong', 'weak']),
            // ... altri campi
        ];
    }
    
    public function strong(): self {
        return $this->state(['priority' => 'strong']);
    }
    
    public function weak(): self {
        return $this->state(['priority' => 'weak']);
    }
}
```

#### **ReservationSeeder**  
```php
// database/seeders/ReservationSeeder.php
class ReservationSeeder extends Seeder {
    public function run(): void {
        $egi = Egi::first();
        
        // Crea mix di strong/weak per testing
        Reservation::factory()->strong()->count(3)->create(['egi_id' => $egi->id]);
        Reservation::factory()->weak()->count(5)->create(['egi_id' => $egi->id]);
        
        // Aggiorna rankings
        app(ReservationService::class)->updateRankings($egi->id);
    }
}
```

### **Test Cases**

#### **Unit Tests**
```php
// tests/Unit/ReservationServiceTest.php
class ReservationServiceTest extends TestCase {
    public function test_strong_beats_weak_regardless_of_amount()
    public function test_ranking_calculation_within_priority()
    public function test_notification_sent_on_rank_change()
    public function test_user_can_increase_reservation()
    public function test_user_cannot_decrease_reservation()
}
```

#### **Integration Tests**
```php
// tests/Feature/ReservationApiTest.php
class ReservationApiTest extends TestCase {
    public function test_create_reservation_updates_rankings()
    public function test_withdraw_reservation_notifies_others()
    public function test_rankings_api_returns_correct_format()
    public function test_anonymous_user_gets_weak_priority()
}
```

---

## 🚨 PROBLEMI NOTI E SOLUZIONI

### **1. Race Conditions**
**Problema**: Più utenti prenotano simultaneamente  
**Soluzione**: Lock pessimistico su EGI durante creazione prenotazione

### **2. Notification Spam**
**Problema**: Troppe notifiche per piccoli cambi ranking  
**Soluzione**: Batch notifications ogni 5 minuti + filtro soglia

### **3. Cache Invalidation**
**Problema**: Rankings cache outdated dopo update  
**Soluzione**: Cache tags + automatic invalidation

### **4. Currency Rate Fluctuation**
**Problema**: ALGO rate cambia durante prenotazione  
**Soluzione**: Snapshot rate al momento prenotazione per consistency

---

## 📈 METRICHE E KPI

### **Metriche Prenotazioni**
- **Conversion rate** visitor → prenotazione
- **Average bid amount** per EGI
- **Strong vs Weak ratio**
- **Ranking competition index** (quanto cambiano le posizioni)
- **Time to first reservation** per nuovo EGI

### **Metriche Utente**
- **Repeat reservation rate** (stesso utente su EGI diversi)
- **Bid increase frequency** (modifica prenotazione)  
- **Withdrawal rate** pre-mint
- **Commissioner vs Collector** preference

### **Metriche Sistema**
- **API response time** per rankings
- **Notification delivery success rate**
- **Cache hit ratio** per rankings
- **Database query performance**

---

## 🎯 ROADMAP IMPLEMENTAZIONE

### **Fase 1: Completamento Frontend (PRIORITÀ ALTA)**
1. **reservationService.ts**: Aggiungere funzioni mancanti
2. **reservationFeature.ts**: Riattivare e integrare
3. **Modal system**: Mantenere ALGO + aggiungere ranking
4. **UI Components**: Ranking display + button states

### **Fase 2: Testing e Debugging (PRIORITÀ ALTA)**
1. **Factory/Seeder**: Creare dati test realistici
2. **Unit tests**: Services + Models
3. **Integration tests**: API endpoints
4. **Manual testing**: Full user flow

### **Fase 3: Performance e UX (PRIORITÀ MEDIA)**
1. **Real-time updates**: WebSocket per ranking live
2. **Mobile optimization**: Responsive design
3. **Advanced notifications**: Push notifications
4. **Analytics**: Detailed metrics dashboard



---

## 🔧 DEPLOYMENT CHECKLIST

### **Pre-Deployment**
- [ ] Tutti i test passano
- [ ] Error handling testato con UEM
- [ ] Logging configurato con ULM  
- [ ] Rate limiting configurato
- [ ] Cache warming per rankings popolari
- [ ] Database backup strategy
- [ ] Monitoring alerts configurati

### **Go-Live Checklist**
- [ ] Environment variables production
- [ ] HTTPS configurato e testato
- [ ] CDN configurato per assets statici
- [ ] Database indici ottimizzati
- [ ] Queue workers attivi per notifiche
- [ ] Scheduler attivo per ranking updates
- [ ] Error monitoring attivo (Sentry/Bugsnag)

---

## 📝 NOTE SVILUPPATORI

### **Convenzioni Codice**

#### **PHP/Laravel**
- **Namespace pattern**: `App\Services\`, `App\Http\Controllers\`
- **Contracts location**: `App\Contracts\` (NON `App\Interfaces`)
- **Error handling**: SEMPRE con UEM, mai throw nudo
- **Logging**: SEMPRE con ULM per audit trail
- **DocBlocks**: Stile OS3.0 con firma completa

#### **TypeScript**
- **No classi per services**: funzioni standalone esportate
- **Interfaces obbligatorie**: per type safety API
- **Async/await**: per tutte le chiamate API
- **Error handling**: try/catch + UEM client integration

#### **Database**
- **Migration ADDITIVE**: mai DROP colonne esistenti
- **Campi legacy**: mantenere per backward compatibility
- **Foreign keys**: sempre con indici per performance
- **Soft deletes**: dove ha senso per audit

### **Pattern Architetturali**

1. **Service Layer Pattern**: Business logic isolata nei Services
2. **Repository Pattern**: (futuro) per data access standardizzato  
3. **Observer Pattern**: Eventi e notifiche automatiche
4. **Factory Pattern**: Creazione notifiche e oggetti complessi
5. **Strategy Pattern**: (futuro) per diversi tipi di asta

### **Security Requirements**

- **Rate limiting**: Sempre su API pubbliche
- **CSRF protection**: Su tutti i form POST
- **Input validation**: Server-side sempre, client-side UX
- **SQL injection**: Sempre Eloquent, mai raw queries
- **XSS protection**: Sanitizzazione output, CSP headers

### **GDPR Compliance**

- **Consent management**: Commissioner accetta visibilità pubblica
- **Data minimization**: Collector completamente anonimo
- **Audit trail**: Tutti i log con ULM per compliance
- **Right to erasure**: Solo pre-mint, post-mint immutabile blockchain
- **Privacy by default**: Weak users sempre anonimi



---

## 📞 RIFERIMENTI RAPIDI

### **Comandi Utili**
```bash
# Update rankings manualmente
php artisan reservations:process-rankings

# Test con dry-run
php artisan reservations:process-rankings --dry-run

# Clear tutte le cache
php artisan optimize:clear

# Restart queue workers
php artisan queue:restart

# Test notifiche in tinker
php artisan tinker
>>> app(ReservationNotificationService::class)->sendNewHighest(Reservation::first());

# Seed dati test
php artisan db:seed --class=ReservationSeeder
```

### **File Critici (Handle with Care)**
```
Backend:
├── app/Services/ReservationService.php (ESTENDI, non sostituire)
├── app/Http/Controllers/ReservationController.php (ESTENDI)
├── app/Models/Reservation.php (ESTENDI)
├── app/Services/Notifications/ReservationNotificationService.php (NUOVO)
└── config/error-manager.php (AGGIUNGI error codes)

Frontend:
├── resources/ts/services/reservationService.ts (COMPLETA)
├── resources/ts/features/reservations/reservationFeature.ts (RIATTIVA)
├── resources/ts/features/reservations/reservationButtons.ts (AGGIORNA)
└── resources/views/collections/show.blade.php (AGGIUNGI ranking UI)

Database:
├── migrations/*reservations* (ESTENDI, mai rimuovere campi)
└── database/seeders/ReservationSeeder.php (CREA)
```

### **API Endpoints**
```
GET    /api/reservations/rankings/{egi}           # Pubblico
POST   /api/reservations/create                   # Auth required
DELETE /api/reservations/{reservation}/withdraw   # Auth required  
GET    /api/reservations/my-reservations          # Auth required
GET    /api/reservations/check-eligibility/{egi}  # Auth required

# Legacy (MANTIENI per compatibilità)
POST   /api/egis/{id}/reserve                     # Vecchio sistema
```

---

## 🎉 CONCLUSIONE

Questo documento rappresenta la **fonte di verità completa** per il sistema prenotazioni FlorenceEGI. Ogni sviluppatore (umano o AI) che lavora sul progetto deve seguire esattamente queste specifiche.

**Ricorda sempre:**
- ✅ Sistema prenotazioni è **PRODUZIONE**, non test
- ✅ Strong/Weak è **STRATEGICO** e permanente  
- ✅ ALGO conversion **MANTIENI** per marketing
- ✅ UEM/ULM sono **OBBLIGATORI**
- ✅ Nessun riferimento a "pre-launch"
- ✅ Backend ESTENDI, mai sostituire
- ✅ Frontend COMPLETA i file esistenti

---

**Ultimo Aggiornamento**: 16 Agosto 2025, 21:30  
**Prossimo Review**: Post completamento frontend  
**Status**: Documentazione master per copilot VSCode

*"Ship it responsibly. Test everything. Use UEM/ULM always."*