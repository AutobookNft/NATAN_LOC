# 📋 Piano Definitivo: Refactoring Sistema Prenotazioni e Gestione Valuta

**Data:** 13 Agosto 2025  
**Versione:** 3.2 (Corretto con Dependency Injection per UEM)  
**Status:** DEFINITIVE - READY FOR IMPLEMENTATION

## 🎯 1. OBIETTIVO STRATEGICO

Evolvere il sistema di prenotazioni EGI in una piattaforma enterprise-ready, disaccoppiando la logica di business dalla valuta FIAT. Il sistema deve:

- **Pensare in FIAT:** Offrire a Creator e Acquirenti un'esperienza utente basata sulla loro valuta locale (EUR, USD), astraendo la complessità della crypto.
- **Operare in ALGO:** Usare ALGO come unica, immutabile fonte di verità per tutte le transazioni e i confronti di valore a livello di database.
- **Essere Trasparente e Auditabile:** Registrare ogni dettaglio della conversione al momento della transazione per garantire massima trasparenza e tracciabilità futura.
- **Essere Robusto:** Gestire la volatilità dei tassi di cambio e gli errori di servizio in modo controllato e professionale tramite UEM/ULM.

## 🚨 2. PROBLEMI CRITICI DA RISOLVERE

- **Schema Database Inadeguato:** Mancano campi fondamentali per tracciare la valuta FIAT, il prezzo in ALGO e il tasso di cambio al momento della prenotazione.
- **Logica di Rilancio Fragile:** La validazione delle offerte successive non è robusta e non gestisce correttamente la volatilità della valuta.
- **Mancanza di un Oracolo per il Cambio:** Assenza di un servizio centralizzato e affidabile per ottenere i tassi di cambio.
- **Gestione Errori Non Standardizzata:** Uso misto di eccezioni generiche e UEM.

## 🏗️ 3. PIANO DI IMPLEMENTAZIONE

### FASE 1: Architettura del Database (Fonte di Verità)

#### 1.1. Migration per la Tabella reservations

Creare una nuova migration per aggiornare la tabella reservations.

```bash
php artisan make:migration update_reservations_for_multi_currency --table=reservations
```

Contenuto della Migration:

```php
Schema::table('reservations', function (Blueprint $table) {
    // 1. Rimuovere il vecchio campo, se esiste ancora
    if (Schema::hasColumn('reservations', 'offer_amount_eur')) {
        $table->dropColumn('offer_amount_eur');
    }

    // 2. Aggiungere i nuovi campi per la gestione completa della valuta
    $table->decimal('offer_amount_fiat', 12, 2)->after('egi_id');
    $table->string('fiat_currency', 3)->default('USD')->after('offer_amount_fiat');
    $table->unsignedBigInteger('offer_amount_algo')->after('fiat_currency')->comment('Prezzo in microALGO');
    $table->decimal('exchange_rate', 18, 8)->after('offer_amount_algo');
    $table->timestamp('exchange_timestamp')->after('exchange_rate');

    // 3. Assicurarsi che gli indici siano presenti
    $table->index('fiat_currency');
    $table->index('offer_amount_algo');
});
```

#### 1.2. Aggiornamento del Model Reservation.php

```php
// app/Models/Reservation.php
protected $fillable = [
    // ... campi esistenti
    'offer_amount_fiat',
    'fiat_currency',
    'offer_amount_algo',
    'exchange_rate',
    'exchange_timestamp',
];

protected $casts = [
    'offer_amount_fiat' => 'decimal:2',
    'offer_amount_algo' => 'integer', // Trattato come intero (microALGO)
    'exchange_rate' => 'decimal:8',
    'exchange_timestamp' => 'datetime',
];
```

### FASE 2: Il Servizio Oracolo (CurrencyService)

#### 2.1. Creazione del Servizio

Creare una nuova classe di servizio dedicata, iniettando le dipendenze necessarie.

File: `app/Services/CurrencyService.php`

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Ultra\ErrorManager\Interfaces\ErrorManagerInterface;
// Aggiungere ULM se necessario per il logging

class CurrencyService
{
    private const CACHE_KEY_PREFIX = 'currency_rate_';
    private const CACHE_TTL_SECONDS = 60; // Cache per 1 minuto

    public function __construct(
        protected ErrorManagerInterface $errorManager
        // Inietta qui ULM se serve
    ) {}

    /**
     * Ottiene il tasso di cambio attuale da ALGO a una valuta FIAT.
     * Implementa caching e failover.
     */
    public function getAlgoToFiatRate(string $fiatCurrency = 'USD'): ?array
    {
        $cacheKey = self::CACHE_KEY_PREFIX . strtoupper($fiatCurrency);

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($fiatCurrency) {
            try {
                $response = Http::get('https://api.coingecko.com/api/v3/simple/price', [
                    'ids' => 'algorand',
                    'vs_currencies' => strtolower($fiatCurrency),
                ]);

                if ($response->failed() || !isset($response->json()['algorand'][strtolower($fiatCurrency)])) {
                    throw new \Exception('CoinGecko API request failed or returned invalid data.');
                }

                return [
                    'rate' => (float) $response->json()['algorand'][strtolower($fiatCurrency)],
                    'timestamp' => now(),
                ];

            } catch (\Exception $e) {
                // Gestione errore standardizzata con UEM iniettato
                $this->errorManager->handle('CURRENCY_EXCHANGE_SERVICE_FAILED', [
                    'currency' => $fiatCurrency,
                    'error' => $e->getMessage(),
                ], $e);
                // Ritorna null per essere gestito a monte. L'errore è già stato processato da UEM.
                return null;
            }
        });
    }

    /**
     * Converte un importo FIAT in microALGO.
     */
    public function convertFiatToMicroAlgo(float $fiatAmount, float $rate): int
    {
        if ($rate <= 0) return 0;
        $algoAmount = $fiatAmount / $rate;
        return (int) ($algoAmount * 1_000_000); // Converte in microALGO
    }

    /**
     * Converte microALGO in un importo FIAT.
     */
    public function convertMicroAlgoToFiat(int $microAlgoAmount, float $rate): float
    {
        $algoAmount = $microAlgoAmount / 1_000_000;
        return $algoAmount * $rate;
    }
}
```

### FASE 3: Refactoring del ReservationService

#### 3.1. Iniezione delle Dipendenze

Iniettare CurrencyService e ErrorManagerInterface nel costruttore del ReservationService.

```php
// In app/Services/ReservationService.php
public function __construct(
    protected CurrencyService $currencyService,
    protected ErrorManagerInterface $errorManager
    // ... altre dipendenze come ULM ...
) {}
```

#### 3.2. Flusso di Conversione e Validazione (Logica Chiave)

La funzione createReservation deve essere riscritta per seguire questo flusso:

```php
// In app/Services/ReservationService.php

public function createReservation(array $data): Reservation
{
    return DB::transaction(function () use ($data) {
        // 1. VALIDAZIONE INPUT
        $egi = $this->validateEgiIsReservable($data['egi_id']);
        $user = auth()->user();
        $offerFiat = (float) $data['offer_amount_fiat'];
        $fiatCurrency = $data['fiat_currency'] ?? 'USD';

        // 2. OTTENERE TASSO DI CAMBIO ATTUALE
        $exchangeData = $this->currencyService->getAlgoToFiatRate($fiatCurrency);
        if (!$exchangeData) {
            // Se il servizio ritorna null, l'errore è già stato gestito.
            // Qui lanciamo un'eccezione che verrà catturata dal controller,
            // che a sua volta userà UEM per mostrare un messaggio all'utente.
            // UEM ha già loggato l'errore critico dal CurrencyService.
            throw new \RuntimeException('Failed to retrieve currency exchange rate.');
        }
        $currentRate = $exchangeData['rate'];
        $offerAlgo = $this->currencyService->convertFiatToMicroAlgo($offerFiat, $currentRate);

        // 3. VALIDAZIONE RILANCIO (CONFRONTO IN ALGO)
        $this->validateRelaunchAmountInAlgo($egi, $offerAlgo);

        // 4. CREAZIONE NUOVA PRENOTAZIONE
        $newReservation = Reservation::create([
            'user_id' => $user->id,
            'egi_id' => $egi->id,
            'offer_amount_fiat' => $offerFiat,
            'fiat_currency' => $fiatCurrency,
            'offer_amount_algo' => $offerAlgo,
            'exchange_rate' => $currentRate,
            'exchange_timestamp' => $exchangeData['timestamp'],
            'status' => 'active',
        ]);

        // 5. INVALIDAZIONE PRENOTAZIONI PRECEDENTI
        $this->supersedePreviousReservations($egi, $newReservation->id);
        
        // 6. LOGGING E EVENTI
        // $this->logger->info(...);

        return $newReservation;
    });
}

private function validateRelaunchAmountInAlgo(Egi $egi, int $newOfferInMicroAlgo): void
{
    $latestReservation = $egi->reservations()->where('status', 'active')->latest()->first();
    
    $baseCurrency = $egi->base_currency ?? 'USD';
    $basePriceRateData = $this->currencyService->getAlgoToFiatRate($baseCurrency);
    if (!$basePriceRateData) {
        throw new \RuntimeException('Failed to retrieve base currency exchange rate for validation.');
    }

    $minAmountInMicroAlgo = $latestReservation 
        ? $latestReservation->offer_amount_algo 
        : $this->currencyService->convertFiatToMicroAlgo($egi->base_price, $basePriceRateData['rate']);

    if ($newOfferInMicroAlgo <= $minAmountInMicroAlgo) {
        // UEM gestirà questa eccezione, che è di tipo 'blocking' e mostrerà un messaggio all'utente.
        $this->errorManager->handle('RESERVATION_RELAUNCH_INSUFFICIENT_AMOUNT', [
            'new_offer_algo' => $newOfferInMicroAlgo,
            'required_algo' => $minAmountInMicroAlgo,
        ]);
    }
}
```

### FASE 4: API, Frontend e UEM

#### 4.1. Endpoint API

Creare un endpoint per il polling del front-end.

File: `routes/api.php`

```php
Route::get('/v1/currency/rate/{fiatCurrency}', [CurrencyController::class, 'getRate']);
```

Controller:
Il CurrencyController userà il CurrencyService per restituire il tasso di cambio in formato JSON.

#### 4.2. Logica Frontend

- **Polling:** Uno script JS chiama l'endpoint ogni 15-30 secondi.
- **Aggiornamento UI:** Tutti i prezzi FIAT visibili sulla pagina vengono aggiornati dinamicamente.
- **Form di Prenotazione:** Quando l'utente inserisce un importo in FIAT, un piccolo testo sotto il campo mostra la stima in ALGO.
- **Conferma:** Prima di inviare il form, mostrare un modale di conferma: "Stai offrendo 100 USD (circa 200.123 ALGO). Confermi?"

#### 4.3. Codici di Errore UEM

Aggiornare `config/error-manager.php` con i codici necessari.

```php
'RESERVATION_RELAUNCH_INSUFFICIENT_AMOUNT' => [ /* ... */ ],
'CURRENCY_EXCHANGE_SERVICE_FAILED' => [ /* ... */ ],
'EGI_NOT_RESERVABLE' => [ /* ... */ ],
```

### FASE 5: Testing

La strategia di testing deve coprire i nuovi componenti critici.

#### Unit Test per CurrencyService:
- test_fetches_rate_from_primary_api (con Mock di Http).
- test_uses_cache_on_subsequent_calls.
- test_handles_api_failure_gracefully.
- test_fiat_to_micro_algo_conversion_is_correct.
- test_micro_algo_to_fiat_conversion_is_correct.

#### Unit Test per ReservationService:
- test_rejects_offer_if_currency_service_fails.
- test_correctly_compares_offers_in_algo.
- test_saves_all_currency_data_on_creation.

#### Feature Test:
- Simulare un intero flusso di prenotazione e rilancio, verificando che i dati vengano salvati correttamente nel database.

## 📋 CHECKLIST FINALE

- [ ] DB: Migration creata e testata. Model aggiornato.
- [ ] Service: CurrencyService implementato con caching e gestione errori.
- [ ] Service: ReservationService refattorizzato per usare il CurrencyService e la logica di confronto in ALGO.
- [ ] API: Endpoint per il tasso di cambio creato e funzionante.
- [ ] Frontend: Logica di polling implementata per l'aggiornamento dei prezzi.
- [ ] UEM: Codici di errore configurati e tradotti.
- [ ] Testing: Unit e Feature test scritti per coprire i nuovi flussi.

## 🎯 OBIETTIVO FINALE

Un sistema di prenotazioni che astrae completamente la complessità della crypto per l'utente, garantendo al contempo la massima precisione e trasparenza a livello di dati.
