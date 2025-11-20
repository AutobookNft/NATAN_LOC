<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\PythonScraperService;
use App\Services\Gdpr\AuditLogService;
use App\Enums\Gdpr\GdprActivityCategory;
use Ultra\UltraLogManager\UltraLogManager;
use Ultra\ErrorManager\Interfaces\ErrorManagerInterface;

/**
 * @package App\Http\Controllers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 2.0.0 (NATAN_LOC - Python Scrapers Management with UEM/ULM/GDPR)
 * @date 2025-11-05
 * @purpose Gestione interfaccia per esecuzione scraper Python con compliance GDPR completa
 *
 * GDPR-COMPLIANT:
 * - UEM: Gestione errori strutturata
 * - ULM: Logging operativo strutturato
 * - GDPR Audit: Tracciamento completo azioni utente per compliance PA
 */
class NatanScrapersController extends Controller
{
    protected PythonScraperService $scraperService;
    protected UltraLogManager $logger;
    protected ErrorManagerInterface $errorManager;
    protected AuditLogService $auditService;

    public function __construct(
        PythonScraperService $scraperService,
        UltraLogManager $logger,
        ErrorManagerInterface $errorManager,
        AuditLogService $auditService
    ) {
        $this->scraperService = $scraperService;
        $this->logger = $logger;
        $this->errorManager = $errorManager;
        $this->auditService = $auditService;
    }

    /**
     * Lista scraper disponibili
     */
    public function index(): View
    {
        $scrapers = $this->getAvailableScrapers();

        // Calcola statistiche
        $totalDocuments = $this->getTotalDocumentsInMongoDB();
        
        $stats = [
            'total' => count($scrapers),
            'available' => count($scrapers),
            'total_documents' => $totalDocuments,
        ];
        
        $this->logger->info('[NatanScrapersController] Index page stats', [
            'stats' => $stats,
            'log_category' => 'SCRAPER_STATS'
        ]);

        return view('natan.scrapers.index', [
            'scrapers' => $scrapers,
            'stats' => $stats,
        ]);
    }

    /**
     * Dettaglio singolo scraper
     */
    public function show(string $scraperId): View
    {
        $scrapers = $this->getAvailableScrapers();
        $scraper = collect($scrapers)->firstWhere('id', $scraperId);

        if (!$scraper) {
            abort(404, 'Scraper non trovato');
        }

        // Calcola statistiche per questo scraper
        $tenantId = Auth::user()?->tenant_id ?? app('currentTenantId') ?? 2;
        $totalDocuments = $this->getTotalDocumentsInMongoDBForScraper($scraperId, $tenantId);
        
        // Trova ultima esecuzione (dal progress file più recente)
        $lastExecution = $this->getLastExecutionTime($scraperId);
        
        $stats = [
            'total_documents' => $totalDocuments,
            'last_execution' => $lastExecution,
        ];
        
        $this->logger->info('[NatanScrapersController] Show page stats', [
            'scraper_id' => $scraperId,
            'stats' => $stats,
            'log_category' => 'SCRAPER_STATS'
        ]);

        return view('natan.scrapers.show', [
            'scraper' => $scraper,
            'stats' => $stats,
        ]);
    }

    /**
     * Esegui scraper Python
     */
    public function run(Request $request, string $scraperId): JsonResponse
    {
        // Aumenta timeout per esecuzione scraper (30 minuti)
        set_time_limit(1800);
        ini_set('max_execution_time', '1800');
        // FormData invia boolean come stringhe "1"/"0" o "true"/"false", quindi convertiamo
        $input = $request->all();

        // ULM: Log raw input per debug
        $this->logger->info('[NatanScrapersController] Raw input received', [
            'raw_input' => $input,
            'mongodb_import_raw' => $input['mongodb_import'] ?? 'not set',
            'mongodb_import_type' => gettype($input['mongodb_import'] ?? null),
            'log_category' => 'SCRAPER_INPUT_DEBUG'
        ]);

        // Funzione helper per convertire vari formati in boolean
        $convertToBoolean = function ($value) {
            if ($value === null || $value === '') {
                return null;
            }
            if (is_bool($value)) {
                return $value;
            }
            if (is_string($value)) {
                $value = strtolower(trim($value));
                // "1", "true", "yes", "on" => true
                if (in_array($value, ['1', 'true', 'yes', 'on', 'si', 'sì'])) {
                    return true;
                }
                // "0", "false", "no", "off" => false
                if (in_array($value, ['0', 'false', 'no', 'off', 'non'])) {
                    return false;
                }
                return null;
            }
            return (bool)$value;
        };

        // Converti stringhe in boolean
        if (isset($input['mongodb_import'])) {
            $originalValue = $input['mongodb_import'];
            $convertedValue = $convertToBoolean($input['mongodb_import']);
            $input['mongodb_import'] = $convertedValue;
            $this->logger->info('[NatanScrapersController] Boolean conversion', [
                'field' => 'mongodb_import',
                'original' => $originalValue,
                'original_type' => gettype($originalValue),
                'original_string' => (string)$originalValue,
                'converted' => $convertedValue,
                'converted_type' => gettype($convertedValue),
                'converted_string' => (string)$convertedValue,
                'is_true' => $convertedValue === true,
                'is_false' => $convertedValue === false,
                'log_category' => 'SCRAPER_INPUT_DEBUG'
            ]);
        } else {
            // Se mongodb_import non è presente, loggiamo per debug
            $this->logger->info('[NatanScrapersController] mongodb_import not set in input', [
                'input_keys' => array_keys($input),
                'log_category' => 'SCRAPER_INPUT_DEBUG'
            ]);
        }
        if (isset($input['dry_run'])) {
            $input['dry_run'] = $convertToBoolean($input['dry_run']);
        }
        if (isset($input['download_pdfs'])) {
            $input['download_pdfs'] = $convertToBoolean($input['download_pdfs']);
        }

        // Sostituisci l'input con i valori convertiti
        $request->merge($input);

        $validated = $request->validate([
            'year_single' => 'nullable|integer|min:2000|max:2030',
            'year_range' => 'nullable|string|regex:/^\d{4}-\d{4}$/',
            'dry_run' => 'nullable|boolean',
            'mongodb_import' => 'nullable|boolean',
            'download_pdfs' => 'nullable|boolean',
            'tenant_id' => 'nullable|integer|min:1',
            'comune_slug' => 'nullable|string|max:100', // Per Compliance Scanner
        ]);

        $scrapers = $this->getAvailableScrapers();
        $scraper = collect($scrapers)->firstWhere('id', $scraperId);

        if (!$scraper) {
            return response()->json([
                'success' => false,
                'error' => 'Scraper non trovato'
            ], 404);
        }

        try {
            // ULM: Log input
            $this->logger->info('[NatanScrapersController] Run scraper request', [
                'scraper_id' => $scraperId,
                'validated' => $validated,
                'user_id' => Auth::user()?->id,
                'tenant_id' => Auth::user()?->tenant_id ?? app('currentTenantId'),
                'log_category' => 'SCRAPER_CONTROLLER'
            ]);

            $result = $this->scraperService->executeScraper(
                $scraper,
                $validated
            );

            // Verifica se ci sono errori anche se success = true
            if (isset($result['success']) && $result['success'] === false) {
                $this->logger->error('[NatanScrapersController] Scraper execution failed', [
                    'scraper_id' => $scraperId,
                    'error' => $result['error'] ?? 'unknown',
                    'error_code' => $result['error_code'] ?? 'unknown',
                    'log_category' => 'SCRAPER_CONTROLLER'
                ]);
                return response()->json($result, 500);
            }

            // Verifica errori in output
            $output = $result['output'] ?? '';
            $errorOutput = $result['error_output'] ?? '';

            if (!empty($errorOutput) && preg_match('/ModuleNotFoundError|ImportError|Traceback/i', $errorOutput)) {
                $this->logger->error('[NatanScrapersController] Python error detected', [
                    'scraper_id' => $scraperId,
                    'error_output' => substr($errorOutput, 0, 500),
                    'log_category' => 'SCRAPER_CONTROLLER'
                ]);
                return response()->json([
                    'success' => false,
                    'error' => 'Errore Python: ' . substr($errorOutput, 0, 300),
                    'details' => $errorOutput,
                    'output' => substr($output, 0, 500)
                ], 500);
            }

            // Restituisci anche il nome del file di progresso per il polling
            // (già presente in $result, non serve riassegnarlo)

            // ULM: Log success
            $this->logger->info('[NatanScrapersController] Scraper execution completed', [
                'scraper_id' => $scraperId,
                'stats' => $result['stats'] ?? [],
                'has_progress_file' => !empty($result['progress_file']),
                'log_category' => 'SCRAPER_CONTROLLER'
            ]);

            return response()->json($result);
        } catch (\Exception $e) {
            // UEM: Handle unexpected exceptions
            return $this->errorManager->handle('SCRAPER_EXECUTION_FAILED', [
                'scraper_id' => $scraperId,
                'error_message' => $e->getMessage(),
                'error_type' => get_class($e),
                'user_id' => Auth::user()?->id,
                'tenant_id' => Auth::user()?->tenant_id ?? app('currentTenantId'),
            ], $e);
        }
    }

    /**
     * Ottieni progresso esecuzione scraper
     */
    public function progress(Request $request, string $scraperId): JsonResponse
    {
        // Se check_active=1, trova il progress_file più recente
        if ($request->has('check_active') && $request->get('check_active') == '1') {
            $logsPath = storage_path('logs');
            $pattern = "scraper_progress_{$scraperId}_*.json";
            $files = glob($logsPath . '/' . $pattern);

            if (empty($files)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Nessun file di progresso trovato',
                    'status' => 'not_found'
                ], 404);
            }

            // Prendi il file più recente
            usort($files, function ($a, $b) {
                return filemtime($b) - filemtime($a);
            });

            $progressFile = $files[0];
            $progressFileName = basename($progressFile);
        } else {
            $validated = $request->validate([
                'progress_file' => 'required|string',
            ]);
            $progressFileName = $validated['progress_file'];
            $progressFile = storage_path('logs/' . $progressFileName);
        }

        if (!file_exists($progressFile)) {
            return response()->json([
                'success' => false,
                'error' => 'File di progresso non trovato',
                'status' => 'not_found',
                'progress_file' => $progressFileName
            ], 404);
        }

        try {
            $progressData = json_decode(file_get_contents($progressFile), true);

            if (!$progressData) {
                return response()->json([
                    'success' => false,
                    'error' => 'Errore lettura file di progresso',
                ], 500);
            }

            // Raccogli tutti gli errori da tutti i documenti processati
            $allErrors = [];
            foreach ($progressData['documents'] ?? [] as $doc) {
                if (isset($doc['stats']['error_details']) && is_array($doc['stats']['error_details'])) {
                    foreach ($doc['stats']['error_details'] as $error) {
                        $allErrors[] = $error;
                    }
                }
            }
            
            // Rimuovi duplicati mantenendo solo l'ultimo errore per ogni atto
            $uniqueErrors = [];
            foreach ($allErrors as $error) {
                $attoNum = $error['atto'] ?? 'unknown';
                $uniqueErrors[$attoNum] = $error;
            }
            
            // Includi error_details nelle stats se presenti nel file di progress
            $stats = $progressData['stats'] ?? [];
            if (!empty($uniqueErrors)) {
                $stats['error_details'] = array_values($uniqueErrors);
            } elseif (isset($progressData['stats']['error_details'])) {
                $stats['error_details'] = $progressData['stats']['error_details'];
            }
            
            return response()->json([
                'success' => true,
                'status' => $progressData['status'] ?? 'unknown',
                'stats' => $stats,
                'documents' => array_slice($progressData['documents'] ?? [], -20), // Ultimi 20 documenti
                'total_documents' => count($progressData['documents'] ?? []),
                'started_at' => $progressData['started_at'] ?? null,
                'last_update' => $progressData['last_update'] ?? null,
                'completed_at' => $progressData['completed_at'] ?? null,
                'progress_file' => $progressFileName ?? basename($progressFile),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Errore: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Preview/Dry-run scraper
     */
    public function preview(Request $request, string $scraperId): JsonResponse
    {
        $user = Auth::user();
        $tenantId = $user?->tenant_id ?? app('currentTenantId');

        // ULM: Log method call
        $this->logger->info('[NatanScrapersController] Preview method called', [
            'scraper_id' => $scraperId,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_id' => $user?->id,
            'tenant_id' => $tenantId,
            'log_category' => 'SCRAPER_CONTROLLER'
        ]);

        // Il frontend invia 'year', convertiamo a 'year_single'
        $input = $request->all();

        // ULM: Log raw input
        $this->logger->debug('[NatanScrapersController] Preview raw input', [
            'scraper_id' => $scraperId,
            'input' => $input,
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'log_category' => 'SCRAPER_CONTROLLER'
        ]);

        if (isset($input['year']) && !isset($input['year_single'])) {
            $input['year_single'] = $input['year'];
        }
        $request->merge($input);

        // Verifica se è Compliance Scanner
        $scrapers = $this->getAvailableScrapers();
        $scraper = collect($scrapers)->firstWhere('id', $scraperId);
        $isComplianceScanner = ($scraper['type'] ?? '') === 'compliance_scanner';

        if ($isComplianceScanner) {
            // Per Compliance Scanner, valida comune_slug invece di year
            $validated = $request->validate([
                'comune_slug' => 'required|string|max:100',
            ]);
        } else {
            // Per scraper tradizionali, valida year
            $validated = $request->validate([
                'year_single' => 'nullable|integer|min:2000|max:2030',
                'year_range' => 'nullable|string|regex:/^\d{4}-\d{4}$/',
                'year' => 'nullable|integer|min:2000|max:2030', // Accetta anche 'year' dal frontend
            ]);

            // Se arriva 'year' senza 'year_single', usa 'year'
            if (isset($validated['year']) && !isset($validated['year_single'])) {
                $validated['year_single'] = $validated['year'];
            }
        }

        // ULM: Log validated input
        $this->logger->info('[NatanScrapersController] Preview validated input', [
            'scraper_id' => $scraperId,
            'validated' => $validated,
            'user_id' => $user?->id,
            'tenant_id' => $tenantId,
            'log_category' => 'SCRAPER_CONTROLLER'
        ]);

        if (!$scraper) {
            // UEM: Handle scraper not found
            return $this->errorManager->handle('SCRAPER_SCRIPT_NOT_FOUND', [
                'scraper_id' => $scraperId,
                'user_id' => $user?->id,
                'tenant_id' => $tenantId,
            ], new \Exception("Scraper non trovato: {$scraperId}"));
        }

        try {

            // Force dry-run
            $validated['dry_run'] = true;
            $validated['mongodb_import'] = false;

            $result = $this->scraperService->executeScraper(
                $scraper,
                $validated
            );

            // ULM: Log completion
            $this->logger->info('[NatanScrapersController] Preview scraper completed', [
                'scraper_id' => $scraperId,
                'success' => $result['success'] ?? 'unknown',
                'has_output' => !empty($result['output'] ?? ''),
                'has_error_output' => !empty($result['error_output'] ?? ''),
                'user_id' => $user?->id,
                'tenant_id' => $tenantId,
                'log_category' => 'SCRAPER_CONTROLLER'
            ]);

            // Verifica se ci sono errori anche se success = true
            if (isset($result['success']) && $result['success'] === false) {
                return response()->json($result, 500);
            }

            // Verifica errori in output
            $output = $result['output'] ?? '';
            $errorOutput = $result['error_output'] ?? '';

            if (!empty($errorOutput) && preg_match('/ModuleNotFoundError|ImportError|Traceback/i', $errorOutput)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Errore Python: ' . substr($errorOutput, 0, 300),
                    'details' => $errorOutput,
                    'output' => substr($output, 0, 500)
                ], 500);
            }

            // Estrai dati per il frontend (count, first_act, last_act)
            $responseData = $result;
            
            // Per Compliance Scanner, usa direttamente stats (non cerca JSON in dry-run)
            $isComplianceScanner = ($scraper['type'] ?? '') === 'compliance_scanner';
            
            if ($isComplianceScanner) {
                // Compliance Scanner: usa direttamente stats['atti_estratti'] e stats['atti_sample']
                $responseData['count'] = $result['stats']['atti_estratti'] ?? $result['stats']['processed'] ?? 0;
                $responseData['comune_slug'] = $validated['comune_slug'] ?? null;
                
                // Estrai primo e ultimo atto da atti_sample se disponibile
                $attiSample = $result['stats']['atti_sample'] ?? [];
                if (is_array($attiSample) && count($attiSample) > 0) {
                    $responseData['first_act'] = $this->formatActForResponse($attiSample[0]);
                    if (count($attiSample) > 1) {
                        $responseData['last_act'] = $this->formatActForResponse($attiSample[count($attiSample) - 1]);
                    }
                }
            } else {
                // Scraper tradizionali: cerca JSON salvato
                $firstAct = null;
                $lastAct = null;

                // Cerca file JSON salvati dallo scraper per estrarre atti
                if (preg_match('/Backup JSON salvato:\s*([^\n]+)/i', $result['output'] ?? '', $jsonMatches)) {
                    $rawJsonPath = trim($jsonMatches[1]);
                    
                    $resolvedJsonPath = null;
                    $candidatePaths = [];
                    
                    if (str_starts_with($rawJsonPath, '/')) {
                        $candidatePaths[] = $rawJsonPath;
                    } else {
                        $relativePath = ltrim($rawJsonPath, '/');
                        // Percorso rispetto alla root del progetto (come generato originariamente)
                        $candidatePaths[] = base_path('../' . $relativePath);
                        // Percorso rispetto alla cartella scripts (dove vengono salvati i file)
                        $candidatePaths[] = base_path('../scripts/' . $relativePath);
                        // Percorso rispetto alla root della repository (fallback generico)
                        $candidatePaths[] = base_path('../../' . $relativePath);
                    }
                    
                    foreach ($candidatePaths as $candidatePath) {
                        if (file_exists($candidatePath)) {
                            $resolvedJsonPath = realpath($candidatePath);
                            break;
                        }
                    }
                    
                    if ($resolvedJsonPath && file_exists($resolvedJsonPath)) {
                        try {
                            $jsonContent = json_decode(file_get_contents($resolvedJsonPath), true);
                            if (is_array($jsonContent)) {
                                // Se è un array di atti, prendi primo e ultimo
                                if (isset($jsonContent[0]) && is_array($jsonContent[0])) {
                                    $firstAct = $this->formatActForResponse($jsonContent[0]);
                                }
                                if (count($jsonContent) > 1 && is_array($jsonContent[count($jsonContent) - 1])) {
                                    $lastAct = $this->formatActForResponse($jsonContent[count($jsonContent) - 1]);
                                }
                            } elseif (is_array($jsonContent) && count($jsonContent) > 0) {
                                // Se è un oggetto con chiavi tipo (DG, DC, etc.), prendi da tutti i tipi
                                $allAtti = [];
                                foreach ($jsonContent as $tipoAtti) {
                                    if (is_array($tipoAtti)) {
                                        $allAtti = array_merge($allAtti, $tipoAtti);
                                    }
                                }
                                if (!empty($allAtti)) {
                                    $firstAct = $this->formatActForResponse($allAtti[0]);
                                    $lastAct = $this->formatActForResponse($allAtti[count($allAtti) - 1]);
                                }
                            }
                        } catch (\Exception $e) {
                            $this->logger->warning('[NatanScrapersController] Failed to parse JSON file', [
                                'json_path' => $resolvedJsonPath,
                                'error' => $e->getMessage(),
                                'log_category' => 'SCRAPER_PREVIEW'
                            ]);
                        }
                    } else {
                        $this->logger->warning('[NatanScrapersController] JSON file not found for preview', [
                            'raw_path' => $rawJsonPath,
                            'candidate_paths' => $candidatePaths,
                            'log_category' => 'SCRAPER_PREVIEW'
                        ]);
                    }
                }

                // Usa atti estratti dal JSON se disponibili (sistema esistente)
                if ($firstAct) {
                    $responseData['first_act'] = $firstAct;
                }
                if ($lastAct) {
                    $responseData['last_act'] = $lastAct;
                }
                
                // Per scraper tradizionali, usa processed e year
                $responseData['count'] = $result['stats']['processed'] ?? 0;
                $responseData['year'] = $validated['year_single'] ?? $validated['year'] ?? null;
            }

            return response()->json($responseData);
        } catch (\Exception $e) {
            // UEM: Handle unexpected exceptions
            return $this->errorManager->handle('SCRAPER_EXECUTION_FAILED', [
                'scraper_id' => $scraperId,
                'error_message' => $e->getMessage(),
                'error_type' => get_class($e),
                'user_id' => $user?->id,
                'tenant_id' => $tenantId,
            ], $e);
        }
    }

    /**
     * Formatta un atto per la risposta JSON al frontend
     */
    protected function formatActForResponse(array $atto): array
    {
        return [
            'numero' => $atto['numeroAdozione'] ?? $atto['numero'] ?? '-',
            'data' => $atto['dataAdozione'] ?? $atto['data'] ?? '-',
            'tipo' => $atto['tipoCodice'] ?? $atto['tipo'] ?? '-',
            'oggetto' => $atto['oggetto'] ?? $atto['titolo'] ?? '-',
        ];
    }

    /**
     * Lista scraper Python disponibili
     */
    protected function getAvailableScrapers(): array
    {
        return [
            [
                'id' => 'compliance_scanner',
                'name' => 'Compliance Scanner Albi Pretori',
                'type' => 'compliance_scanner',
                'source_entity' => 'Comuni Toscani',
                'description' => 'Scanner completo conformità Albi Pretori per comuni toscani - L.69/2009 + CAD + AgID 2025. Estrae documenti pubblici e genera report di compliance.',
                'base_url' => 'Multi-comune',
                'legal_basis' => 'L.69/2009 - Trasparenza Amministrativa + CAD + AgID 2025',
                'comuni_supportati' => [
                    'firenze',
                    'sesto_fiorentino',
                    'sesto-fiorentino',
                    'empoli',
                    'pisa',
                    'prato',
                    'arezzo',
                    'livorno',
                    'pistoia',
                    'grosseto',
                    'massa',
                    'lucca',
                    'siena',
                ],
            ],
        ];
    }

    /**
     * Get total documents in MongoDB for current tenant
     */
    protected function getTotalDocumentsInMongoDB(): int
    {
        try {
            $tenantId = Auth::user()?->tenant_id ?? app('currentTenantId') ?? 2;
            $payload = [
                'tenant_id' => $tenantId,
                'limit' => 1,
            ];

            $response = $this->executeMongoStatsRequest($payload);
            $count = (int) ($response['total_acts'] ?? 0);
            
            $this->logger->info('[NatanScrapersController] Total documents count for index', [
                'tenant_id' => $tenantId,
                'count' => $count,
                'payload' => $payload,
                'response' => $response,
                'log_category' => 'SCRAPER_STATS'
            ]);
            
            return $count;
        } catch (\Exception $e) {
            $this->logger->warning('[NatanScrapersController] Failed to count MongoDB documents', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'log_category' => 'SCRAPER_STATS'
            ]);
            return 0;
        }
    }

    /**
     * Get total documents in MongoDB for specific scraper and tenant
     */
    protected function getTotalDocumentsInMongoDBForScraper(?string $scraperId, int $tenantId): int
    {
        try {
            $scraperTypeMap = [
                'firenze_deliberazioni' => 'firenze_deliberazioni',
                'albo_firenze_v2' => 'albo_firenze',
                'albo_pretorio_firenze' => 'albo_pretorio_firenze',
            ];

            $payload = [
                'tenant_id' => $tenantId,
                'limit' => 50,
            ];

            if ($scraperId && isset($scraperTypeMap[$scraperId])) {
                $payload['scraper_type'] = $scraperTypeMap[$scraperId];
            }
            
            $response = $this->executeMongoStatsRequest($payload);
            $count = (int) ($response['total_acts'] ?? 0);
            
            $this->logger->info('[NatanScrapersController] Mongo stats result', [
                'scraper_id' => $scraperId,
                'tenant_id' => $tenantId,
                'count' => $count,
                'payload' => $payload,
                'response' => $response,
                'log_category' => 'SCRAPER_STATS'
            ]);
            
            return max(0, $count);
        } catch (\Exception $e) {
            $this->logger->warning('[NatanScrapersController] Failed to count MongoDB documents for scraper', [
                'scraper_id' => $scraperId,
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
                'log_category' => 'SCRAPER_STATS'
            ]);
            return 0;
        }
    }

    /**
     * Esegue richiesta al servizio Python per statistiche MongoDB
     *
     * @param array<string,mixed> $payload
     * @return array<string,mixed>|null
     */
    protected function executeMongoStatsRequest(array $payload): ?array
    {
        foreach ($this->getPythonApiCandidateUrls() as $baseUrl) {
            $endpoint = rtrim($baseUrl, '/') . '/api/v1/commands/stats';

            try {
                $response = Http::timeout(15)->post($endpoint, $payload);

                if (!$response->successful()) {
                    Log::warning('[NatanScrapersController] Stats endpoint failed', [
                        'endpoint' => $endpoint,
                        'status' => $response->status(),
                        'body' => substr($response->body(), 0, 200),
                    ]);
                    continue;
                }

                $data = $response->json();
                if (!is_array($data) || !($data['success'] ?? false)) {
                    Log::warning('[NatanScrapersController] Stats endpoint invalid payload', [
                        'endpoint' => $endpoint,
                        'payload' => $data,
                    ]);
                    continue;
                }

                return $data;
            } catch (\Throwable $exception) {
                Log::error('[NatanScrapersController] Stats endpoint error', [
                    'endpoint' => $endpoint,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return null;
    }

    /**
     * Restituisce gli endpoint candidati del servizio Python FastAPI
     *
     * @return array<int,string>
     */
    protected function getPythonApiCandidateUrls(): array
    {
        $urls = [];

        $configuredUrl = config('services.python_ai.url', 'http://localhost:8001');
        if (!empty($configuredUrl)) {
            $urls[] = rtrim($configuredUrl, '/');
        }

        $dynamicPortFile = '/tmp/natan_python_port.txt';
        if (file_exists($dynamicPortFile)) {
            $portValue = trim((string) file_get_contents($dynamicPortFile));
            if (is_numeric($portValue)) {
                $urls[] = sprintf('http://localhost:%d', (int) $portValue);
            }
        }

        foreach ([8001, 8000, 9000] as $port) {
            $urls[] = sprintf('http://localhost:%d', $port);
        }

        return array_values(array_unique(array_filter($urls)));
    }

    /**
     * Get last execution time for scraper
     */
    protected function getLastExecutionTime(string $scraperId): ?string
    {
        try {
            $logsPath = storage_path('logs');
            $pattern = "scraper_progress_{$scraperId}_*.json";
            $files = glob($logsPath . '/' . $pattern);
            
            if (empty($files)) {
                return null;
            }
            
            // Prendi il file più recente
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            
            $progressFile = $files[0];
            $progressData = json_decode(file_get_contents($progressFile), true);
            
            if ($progressData && isset($progressData['completed_at'])) {
                return $progressData['completed_at'];
            } elseif ($progressData && isset($progressData['started_at'])) {
                return $progressData['started_at'];
            }
            
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
