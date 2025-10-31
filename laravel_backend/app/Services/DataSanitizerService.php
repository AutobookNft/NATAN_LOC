<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Egi;
use Illuminate\Support\Collection;
use Ultra\UltraLogManager\UltraLogManager;

/**
 * Service per sanitizzazione dati prima dell'invio all'AI
 *
 * GDPR DATA ISOLATION:
 * Questo servizio garantisce che SOLO i metadati pubblici vengano
 * estratti dagli atti PA e passati all'AI. Dati sensibili come
 * firme digitali, nominativi, file paths, IP vengono SEMPRE esclusi.
 *
 * AUDIT TRAIL:
 * Ogni operazione di sanitizzazione è loggata per compliance GDPR.
 */
class DataSanitizerService {
    private UltraLogManager $logger;

    /**
     * Campi pubblici che POSSONO essere inviati all'AI
     */
    private const PUBLIC_FIELDS = [
        'id',
        'pa_protocol_number',
        'pa_date',
        'pa_type',
        'pa_title',
        'pa_direction',              // Direzione comunale (es: "Direzione Servizi Tecnici")
        'pa_amount',
        'pa_anchored',
        'pa_anchored_at',
        'pa_algorand_txid',
        'pa_algorand_network',
        'created_at',
        'updated_at',
    ];

    /**
     * Campi PRIVATI che NON devono MAI essere inviati all'AI
     */
    private const PRIVATE_FIELDS = [
        'file_path',                // Percorso filesystem
        'original_name',            // Potrebbe contenere nominativi
        'digital_signature',        // Firma digitale P7M
        'user_id',                  // ID utente che ha caricato
        'ip_address',               // IP di upload
        'user_agent',               // Browser info
        'session_id',               // Session data
        'metadata',                 // Potrebbe contenere dati non filtrati
    ];

    public function __construct(UltraLogManager $logger) {
        $this->logger = $logger;
    }

    /**
     * Sanitizza un singolo atto PA
     *
     * @param Egi $act L'atto PA da sanitizzare
     * @return array Dati pubblici sicuri per l'AI
     */
    public function sanitizeAct(Egi $act): array {
        // Estrai direzione da original_data se disponibile
        $direzione = null;
        if (isset($act->jsonMetadata['original_data']['direzione'])) {
            $direzione = $act->jsonMetadata['original_data']['direzione'];
        }

        $publicData = [
            'id' => $act->id,
            'protocol_number' => $act->pa_protocol_number,
            'date' => $act->pa_protocol_date?->format('Y-m-d'),
            'type' => $act->pa_act_type,
            'title' => $this->sanitizeTitle($act->title),
            'direction' => $direzione,  // Direzione comunale che ha emesso l'atto
            'amount' => $act->jsonMetadata['amount'] ?? null,
            'blockchain_anchored' => $act->pa_anchored,
            'blockchain_txid' => $act->jsonMetadata['algorand_txid'] ?? null,
            'created_at' => $act->created_at?->format('Y-m-d H:i:s'),
        ];

        // NO logging per singoli atti - troppo verbose (500 atti per query)
        // Collection-level logging in sanitizeActsCollection() è sufficiente

        return $publicData;
    }

    /**
     * Sanitizza una collection di atti PA rimuovendo dati potenzialmente sensibili
     * PRESERVES similarity scores from RagService (needed for avg_relevance calculation)
     *
     * @param Collection $acts Collection of Egi or array['act' => Egi, 'similarity' => float]
     * @return array Sanitized acts with preserved similarity
     */
    public function sanitizeActsCollection(Collection $acts): array {
        $sanitized = $acts->map(function ($item) {
            // Handle both old format (Egi object) and new format (array with 'act' and 'similarity')
            $act = is_array($item) ? ($item['act'] ?? $item) : $item;
            $similarity = is_array($item) ? ($item['similarity'] ?? null) : null;

            // If still not an Egi instance, skip
            if (!$act instanceof Egi) {
                return null;
            }

            $sanitizedAct = $this->sanitizeAct($act);

            // PRESERVE similarity score if present (needed for avg_relevance)
            if ($similarity !== null) {
                $sanitizedAct['similarity'] = $similarity;
            }

            return $sanitizedAct;
        })->filter()->toArray();

        $this->logger->info('[DataSanitizer] Collection sanitized', [
            'acts_count' => count($sanitized),
        ]);

        return $sanitized;
    }

    /**
     * Sanitizza il titolo dell'atto rimuovendo potenziali nominativi
     *
     * NOTE: Gli atti PA sono pubblici per natura (albo pretorio),
     * ma applichiamo comunque sanitizzazione conservativa.
     */
    private function sanitizeTitle(?string $title): ?string {
        if (empty($title)) {
            return null;
        }

        // Pattern per identificare possibili nominativi
        // Es: "Dott. Mario Rossi", "Sig.ra Maria Bianchi", "Dr. Giovanni Verdi"
        $patterns = [
            '/\b(Dott\.|Dr\.|Sig\.|Sig\.ra|Ing\.|Avv\.|Prof\.)\s+[A-Z][a-z]+\s+[A-Z][a-z]+\b/i',
            '/\b[A-Z][a-z]+\s+[A-Z][a-z]+\b/', // Nome Cognome generici
        ];

        $sanitized = $title;
        foreach ($patterns as $pattern) {
            $sanitized = preg_replace($pattern, '[NOME]', $sanitized);
        }

        // NO logging per singole redactions - troppo verbose
        // Se serve debug, attivare temporaneamente

        return $sanitized;
    }

    /**
     * Crea un riassunto testuale degli atti per il contesto AI
     *
     * @param Collection $acts Collezione di atti
     * @return string Riassunto testuale
     */
    public function createActsSummary(Collection $acts): string {
        if ($acts->isEmpty()) {
            return "Nessun atto PA presente nel sistema.";
        }

        $count = $acts->count();
        $summary = "ATTI PA PRESENTI NEL SISTEMA (totale: {$count}):\n\n";

        // Se ci sono molti atti (>50), usa formato compatto
        if ($count > 50) {
            $summary .= "[FORMATO COMPATTO - {$count} atti]\n\n";
            foreach ($acts as $index => $item) {
                // Extract Egi from array if RagService returns ['act' => Egi, 'similarity' => float]
                $act = is_array($item) ? ($item['act'] ?? $item) : $item;
                if (!$act instanceof \App\Models\Egi) {
                    continue; // Skip invalid items
                }

                $sanitized = $this->sanitizeAct($act);
                $summary .= sprintf(
                    "%d. [%s] %s - Prot.%s (%s) - Dir: %s\n",
                    $index + 1,
                    $sanitized['type'] ?? 'N/D',
                    mb_substr($sanitized['title'] ?? 'Senza titolo', 0, 60),
                    $sanitized['protocol_number'] ?? 'N/D',
                    $sanitized['date'] ?? 'N/D',
                    $sanitized['direction'] ?? 'N/D'
                );
            }
        } else {
            // Formato esteso per pochi atti
            foreach ($acts as $index => $item) {
                // Extract Egi from array if RagService returns ['act' => Egi, 'similarity' => float]
                $act = is_array($item) ? ($item['act'] ?? $item) : $item;
                if (!$act instanceof \App\Models\Egi) {
                    continue; // Skip invalid items
                }

                $sanitized = $this->sanitizeAct($act);
                $summary .= sprintf(
                    "%d. [%s] %s\n   Protocollo: %s | Data: %s | Direzione: %s\n   Importo: %s€ | Blockchain: %s\n\n",
                    $index + 1,
                    $sanitized['type'] ?? 'N/D',
                    $sanitized['title'] ?? 'Senza titolo',
                    $sanitized['protocol_number'] ?? 'N/D',
                    $sanitized['date'] ?? 'N/D',
                    $sanitized['direction'] ?? 'N/D',
                    $sanitized['amount'] ? number_format((float)$sanitized['amount'], 2, ',', '.') : 'N/D',
                    $sanitized['blockchain_anchored'] ? '✓ Certificato' : '✗ Non certificato'
                );
            }
        }

        return $summary;
    }

    /**
     * Crea statistiche aggregate sicure per l'AI
     *
     * @param Collection $acts Collezione di atti
     * @return array Statistiche pubbliche
     */
    public function createStatsContext(Collection $acts, ?int $userId = null): array {
        // Stats BASE dal campione
        $stats = [
            'total_acts_in_sample' => $acts->count(),  // Atti nel campione recuperato
            'anchored_acts_in_sample' => $acts->where('pa_anchored', true)->count(),
            'date_range_sample' => [
                'first' => $acts->min('pa_protocol_date')?->format('Y-m-d'),
                'last' => $acts->max('pa_protocol_date')?->format('Y-m-d'),
            ],
        ];

        // Se userId fornito, calcola STATS COMPLETE su TUTTO il database
        if ($userId) {
            // Totale atti
            $stats['total_acts_in_database'] = Egi::where('user_id', $userId)
                ->whereNotNull('pa_protocol_number')
                ->count();

            // Conta per TIPO (su TUTTI gli atti)
            $byTypeDb = Egi::where('user_id', $userId)
                ->whereNotNull('pa_protocol_number')
                ->selectRaw('pa_act_type, COUNT(*) as count')
                ->groupBy('pa_act_type')
                ->pluck('count', 'pa_act_type')
                ->toArray();
            $stats['by_type_database'] = $byTypeDb;

            // Conta per DIREZIONE (su TUTTI gli atti - estrae da jsonMetadata)
            $allActs = Egi::where('user_id', $userId)
                ->whereNotNull('pa_protocol_number')
                ->select('jsonMetadata')
                ->get();

            $byDirectionDb = [];
            foreach ($allActs as $act) {
                $direzione = $act->jsonMetadata['original_data']['direzione'] ?? 'Non specificata';
                $byDirectionDb[$direzione] = ($byDirectionDb[$direzione] ?? 0) + 1;
            }
            arsort($byDirectionDb);
            $stats['by_direction_database'] = $byDirectionDb;

            // Range date REALE (su TUTTI gli atti)
            $dateRange = Egi::where('user_id', $userId)
                ->whereNotNull('pa_protocol_number')
                ->whereNotNull('pa_protocol_date')
                ->selectRaw('MIN(pa_protocol_date) as first, MAX(pa_protocol_date) as last')
                ->first();

            $stats['date_range_database'] = [
                'first' => $dateRange->first ? date('Y-m-d', strtotime($dateRange->first)) : null,
                'last' => $dateRange->last ? date('Y-m-d', strtotime($dateRange->last)) : null,
            ];
        }

        $this->logger->info('[DataSanitizer] Stats context created', [
            'sample_size' => $stats['total_acts_in_sample'],
            'database_total' => $stats['total_acts_in_database'] ?? 'N/A',
        ]);

        return $stats;
    }

    /**
     * Valida che un array di dati non contenga campi privati
     *
     * @param array $data Dati da validare
     * @return bool True se i dati sono sicuri
     * @throws \RuntimeException Se sono presenti campi privati
     */
    public function validateSafeData(array $data): bool {
        foreach (self::PRIVATE_FIELDS as $privateField) {
            if (array_key_exists($privateField, $data)) {
                $this->logger->error('[DataSanitizer][GDPR VIOLATION] Private field detected in payload', [
                    'field' => $privateField,
                    'data_keys' => array_keys($data),
                ]);

                throw new \RuntimeException(
                    "GDPR VIOLATION: Private field '$privateField' detected in AI data payload"
                );
            }
        }

        return true;
    }
}
