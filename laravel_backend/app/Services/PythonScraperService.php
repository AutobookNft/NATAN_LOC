<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Gdpr\AuditLogService;
use App\Enums\Gdpr\GdprActivityCategory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Ultra\UltraLogManager\UltraLogManager;
use Ultra\ErrorManager\Interfaces\ErrorManagerInterface;

/**
 * @package App\Services
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 2.0.0 (NATAN_LOC - Python Scraper Execution with UEM/ULM/GDPR)
 * @date 2025-11-05
 * @purpose Service per eseguire script Python scraper con compliance GDPR completa
 * 
 * GDPR-COMPLIANT:
 * - UEM: Gestione errori strutturata
 * - ULM: Logging operativo strutturato
 * - GDPR Audit: Tracciamento completo azioni utente per compliance PA
 */
class PythonScraperService
{
    protected UltraLogManager $logger;
    protected ErrorManagerInterface $errorManager;
    protected AuditLogService $auditService;

    public function __construct(
        UltraLogManager $logger,
        ErrorManagerInterface $errorManager,
        AuditLogService $auditService
    ) {
        $this->logger = $logger;
        $this->errorManager = $errorManager;
        $this->auditService = $auditService;
    }

    /**
     * Esegue uno scraper Python con compliance GDPR completa
     */
    public function executeScraper(array $scraper, array $options): array
    {
        $user = Auth::user();
        $tenantId = $user?->tenant_id ?? app('currentTenantId');
        
        try {
            // 1. ULM: Log start operation
            $this->logger->info('[PythonScraperService] Scraper execution started', [
                'scraper_id' => $scraper['id'] ?? 'unknown',
                'user_id' => $user?->id,
                'tenant_id' => $tenantId,
                'options' => $options,
                'log_category' => 'SCRAPER_OPERATION'
            ]);

            $script = $scraper['script'];
            $scriptsPath = base_path('../scripts');
            $scriptPath = $scriptsPath . '/' . $script;

            if (!file_exists($scriptPath)) {
                // UEM: Log error (but don't return JsonResponse - return array instead)
                $this->errorManager->handle('SCRAPER_SCRIPT_NOT_FOUND', [
                    'scraper_id' => $scraper['id'] ?? 'unknown',
                    'script_path' => $scriptPath,
                    'user_id' => $user?->id,
                    'tenant_id' => $tenantId,
                ], new \Exception("Script Python non trovato: {$scriptPath}"));
                
                // Return array format for service method
                return [
                    'success' => false,
                    'error' => 'Script Python non trovato',
                    'script_path' => $scriptPath,
                    'output' => '',
                    'error_output' => "Script non trovato: {$scriptPath}",
                ];
            }

            // Build command arguments
            $args = $this->buildCommandArgs($options);
            
            // Find Python executable (prefer venv if exists)
            $pythonExecutable = 'python3';
            // Use absolute path resolved to avoid issues
            $baseDir = dirname(base_path());
            $venvPython = $baseDir . '/python_ai_service/venv/bin/python';
            
            // Resolve to absolute path
            $venvPython = realpath($venvPython) ?: $venvPython;
            
            if (file_exists($venvPython)) {
                $pythonExecutable = $venvPython;
                // ULM: Log venv Python usage
                $this->logger->info('[PythonScraperService] Using venv Python', [
                    'path' => $venvPython,
                    'base_dir' => $baseDir,
                    'log_category' => 'SCRAPER_CONFIG'
                ]);
            } else {
                // ULM: Log warning for system Python usage
                $this->logger->warning('[PythonScraperService] Venv Python not found, using system python3', [
                    'venv_path' => $venvPython,
                    'base_dir' => $baseDir,
                    'base_path' => base_path(),
                    'log_category' => 'SCRAPER_CONFIG'
                ]);
            }
            
            // Build command - venv Python should automatically find its packages
            // PYTHONPATH will be set in proc_open env array
            $command = sprintf(
                'cd %s && %s %s %s 2>&1',
                escapeshellarg($scriptsPath),
                escapeshellarg($pythonExecutable),
                escapeshellarg($script),
                implode(' ', array_map('escapeshellarg', $args))
            );

            // ULM: Log command execution with full command details
            $this->logger->info('[PythonScraperService] Executing scraper command', [
                'scraper_id' => $scraper['id'] ?? 'unknown',
                'script' => $script,
                'python_executable' => $pythonExecutable,
                'options' => $options,
                'args' => $args,
                'command_preview' => substr($command, 0, 200),
                'log_category' => 'SCRAPER_EXECUTION'
            ]);

            // Execute with timeout (30 minutes max)
            $timeout = 1800;
            $descriptors = [
                0 => ['pipe', 'r'],  // stdin
                1 => ['pipe', 'w'],  // stdout
                2 => ['pipe', 'w'],  // stderr
            ];
            
            // Prepare environment variables for venv Python execution
            // venv Python needs VIRTUAL_ENV and PATH to find its packages correctly
            $env = [];
            
            // Copy all current environment variables
            foreach ($_ENV as $key => $value) {
                $env[$key] = $value;
            }
            foreach ($_SERVER as $key => $value) {
                if (strpos($key, 'PHP_') === 0) {
                    continue; // Skip PHP-specific vars
                }
                if (!isset($env[$key])) {
                    $env[$key] = $value;
                }
            }
            
            // If using venv Python, set VIRTUAL_ENV to help Python find packages
            if (strpos($pythonExecutable, 'venv/bin/python') !== false) {
                $venvBaseDir = dirname($pythonExecutable, 2);
                $env['VIRTUAL_ENV'] = $venvBaseDir;
                
                // Ensure PATH includes venv bin directory
                $venvBinDir = dirname($pythonExecutable);
                $currentPath = $env['PATH'] ?? getenv('PATH') ?: '';
                $env['PATH'] = $venvBinDir . ':' . $currentPath;
            }
            
            // Always set PYTHONPATH to include scripts directory and venv site-packages
            if (!empty($sitePackagesGlob) && file_exists($sitePackagesGlob[0])) {
                $currentPythonPath = $env['PYTHONPATH'] ?? getenv('PYTHONPATH') ?: '';
                $env['PYTHONPATH'] = $scriptsPath . ($currentPythonPath ? ':' . $currentPythonPath : '') . ':' . $sitePackagesGlob[0];
            }
            
            // ULM: Log environment setup
            $this->logger->info('[PythonScraperService] Environment configured for scraper', [
                'python_executable' => $pythonExecutable,
                'virtual_env' => $env['VIRTUAL_ENV'] ?? 'not set',
                'pythonpath' => $env['PYTHONPATH'] ?? 'not set',
                'site_packages' => $sitePackagesGlob[0] ?? 'not found',
                'scripts_path' => $scriptsPath,
                'path' => substr($env['PATH'] ?? 'not set', 0, 150),
                'log_category' => 'SCRAPER_ENV'
            ]);

            $process = proc_open($command, $descriptors, $pipes, null, $env);

            if (!is_resource($process)) {
                // UEM: Log error (but don't return JsonResponse - return array instead)
                $this->errorManager->handle('SCRAPER_EXECUTION_FAILED', [
                    'scraper_id' => $scraper['id'] ?? 'unknown',
                    'error_message' => 'Impossibile avviare il processo Python',
                    'user_id' => $user?->id,
                    'tenant_id' => $tenantId,
                ], new \Exception('Impossibile avviare il processo Python'));
                
                // Return array format for service method
                return [
                    'success' => false,
                    'error' => 'Impossibile avviare il processo Python',
                    'output' => '',
                    'error_output' => 'Impossibile avviare il processo Python',
                ];
            }

            // Close stdin
            fclose($pipes[0]);

            // Read output (non-blocking)
            $output = '';
            $errorOutput = '';
            $startTime = time();
            
            // Progress tracking file
            $progressFile = storage_path('logs/scraper_progress_' . $scraper['id'] . '_' . time() . '.json');
            $progressData = [
                'scraper_id' => $scraper['id'] ?? 'unknown',
                'started_at' => date('Y-m-d H:i:s'),
                'status' => 'running',
                'documents' => [],
                'stats' => [
                    'processed' => 0,
                    'saved' => 0,
                    'skipped' => 0,
                    'errors' => 0,
                    'error_details' => [],
                    'chunks' => 0,
                    'documents' => 0
                ],
                'last_update' => date('Y-m-d H:i:s')
            ];
            file_put_contents($progressFile, json_encode($progressData, JSON_PRETTY_PRINT));

            stream_set_blocking($pipes[1], false);
            stream_set_blocking($pipes[2], false);

            while (true) {
                $status = proc_get_status($process);

                // Read stdout
                while (($line = fgets($pipes[1])) !== false) {
                    $output .= $line;
                    
                    // Cattura messaggi [PROGRESS] dallo scraper Python
                    if (preg_match('/\[PROGRESS\](.+)/', $line, $matches)) {
                        try {
                            $progressMessage = json_decode(trim($matches[1]), true);
                            if ($progressMessage && isset($progressMessage['type']) && $progressMessage['type'] === 'progress') {
                                // Aggiorna progress data
                                $progressData['documents'][] = [
                                    'document' => $progressMessage['document'] ?? [],
                                    'stats' => $progressMessage['stats'] ?? [],
                                    'timestamp' => date('Y-m-d H:i:s')
                                ];
                                
                                // Aggiorna statistiche
                                if (isset($progressMessage['stats'])) {
                                    $progressData['stats'] = [
                                        'processed' => $progressMessage['stats']['processed'] ?? $progressData['stats']['processed'],
                                        'saved' => $progressMessage['stats']['saved'] ?? $progressMessage['stats']['processed'] ?? $progressData['stats']['saved'],
                                        'skipped' => $progressMessage['stats']['skipped'] ?? $progressData['stats']['skipped'],
                                        'errors' => $progressMessage['stats']['errors'] ?? $progressData['stats']['errors'],
                                        'chunks' => $progressMessage['stats']['total_chunks'] ?? $progressMessage['stats']['chunks'] ?? $progressData['stats']['chunks'] ?? 0,
                                        'documents' => $progressMessage['stats']['documents'] ?? $progressData['stats']['documents'] ?? 0
                                    ];
                                    
                                    // Aggiorna error_details se presenti
                                    if (isset($progressMessage['stats']['error_details']) && is_array($progressMessage['stats']['error_details'])) {
                                        // Mantieni solo errori unici per evitare duplicati
                                        $existingErrors = array_column($progressData['stats']['error_details'] ?? [], 'atto');
                                        foreach ($progressMessage['stats']['error_details'] as $errorDetail) {
                                            $attoNum = $errorDetail['atto'] ?? '';
                                            if (!in_array($attoNum, $existingErrors)) {
                                                $progressData['stats']['error_details'][] = $errorDetail;
                                                $existingErrors[] = $attoNum;
                                            }
                                        }
                                    }
                                }
                                
                                $progressData['last_update'] = date('Y-m-d H:i:s');
                                
                                // Salva progress file (mantieni solo ultimi 100 documenti per non appesantire)
                                if (count($progressData['documents']) > 100) {
                                    $progressData['documents'] = array_slice($progressData['documents'], -100);
                                }
                                file_put_contents($progressFile, json_encode($progressData, JSON_PRETTY_PRINT));
                            }
                        } catch (\Exception $e) {
                            // Ignora errori parsing progress
                        }
                    }
                }

                // Read stderr
                while (($line = fgets($pipes[2])) !== false) {
                    $errorOutput .= $line;
                }

                // Check if process finished
                if (!$status['running']) {
                    break;
                }

                // Check timeout
                if ((time() - $startTime) > $timeout) {
                    proc_terminate($process, SIGTERM);
                    // UEM: Log error (but don't return JsonResponse - return array instead)
                    $this->errorManager->handle('SCRAPER_TIMEOUT', [
                        'scraper_id' => $scraper['id'] ?? 'unknown',
                        'timeout_seconds' => $timeout,
                        'user_id' => $user?->id,
                        'tenant_id' => $tenantId,
                    ], new \Exception("Timeout esecuzione scraper (max {$timeout}s)"));
                    
                    // Return array format for service method
                    return [
                        'success' => false,
                        'error' => "Timeout esecuzione scraper (max {$timeout}s)",
                        'error_code' => 'SCRAPER_TIMEOUT',
                        'timeout_seconds' => $timeout,
                        'output' => substr($output, 0, 1000),
                        'error_output' => substr($errorOutput, 0, 1000),
                    ];
                }

                usleep(100000); // 100ms
            }
            
            // Read any remaining output after process finished
            stream_set_blocking($pipes[1], true);
            stream_set_blocking($pipes[2], true);
            while (($line = fgets($pipes[1])) !== false) {
                $output .= $line;
            }
            while (($line = fgets($pipes[2])) !== false) {
                $errorOutput .= $line;
            }

            // Close pipes
            fclose($pipes[1]);
            fclose($pipes[2]);

            // Get exit code
            $exitCode = proc_close($process);

            // Parse output for stats and costs
            $stats = $this->parseOutput($output);
            $costs = $this->parseCosts($output);

            // Check for errors in output (even if exit code is 0)
            $errorMessages = [];
            if (!empty($errorOutput)) {
                $errorMessages[] = "stderr: " . substr($errorOutput, 0, 500);
            }
            
            // Check for common Python errors in output
            if (preg_match('/ModuleNotFoundError|ImportError|Traceback/i', $output . $errorOutput, $matches)) {
                $errorMessages[] = "Errore Python rilevato: " . $matches[0];
                // Extract full error if Traceback found
                if (preg_match('/Traceback.*?(?=\n\n|\n[a-zA-Z]|\Z)/s', $output . $errorOutput, $traceback)) {
                    $errorMessages[] = "Traceback: " . substr($traceback[0], 0, 500);
                }
            }
            
            if ($exitCode !== 0) {
                $errorMsg = "Script Python fallito con exit code {$exitCode}";
                if (!empty($errorMessages)) {
                    $errorMsg .= ". " . implode(" | ", $errorMessages);
                } elseif (!empty($errorOutput)) {
                    $errorMsg .= ". Errore: " . substr($errorOutput, 0, 300);
                } elseif (!empty($output)) {
                    $errorMsg .= ". Output: " . substr($output, 0, 200);
                } else {
                    $errorMsg .= ". Nessun output disponibile";
                }
                
                // UEM: Log error (but don't return JsonResponse - return array instead)
                $errorCode = preg_match('/ModuleNotFoundError|ImportError|Traceback/i', $errorMsg) 
                    ? 'SCRAPER_PYTHON_ERROR' 
                    : 'SCRAPER_EXECUTION_FAILED';
                
                $this->errorManager->handle($errorCode, [
                    'scraper_id' => $scraper['id'] ?? 'unknown',
                    'error_message' => substr($errorMsg, 0, 500),
                    'exit_code' => $exitCode,
                    'user_id' => $user?->id,
                    'tenant_id' => $tenantId,
                ], new \Exception($errorMsg));
                
                // Return array format for service method
                return [
                    'success' => false,
                    'error' => substr($errorMsg, 0, 300),
                    'error_code' => $errorCode,
                    'exit_code' => $exitCode,
                    'output' => substr($output, 0, 1000),
                    'error_output' => substr($errorOutput, 0, 1000),
                ];
            }
            
            // Warning se ci sono messaggi di errore ma exit code 0
            if (!empty($errorMessages)) {
                // ULM: Log warning (non-blocking)
                $this->logger->warning('[PythonScraperService] Warning durante esecuzione scraper', [
                    'scraper_id' => $scraper['id'] ?? 'unknown',
                    'warnings' => $errorMessages,
                    'output_preview' => substr($output, 0, 500),
                    'log_category' => 'SCRAPER_WARNING'
                ]);
            }

            // Update progress file with final status
            if (isset($progressFile) && file_exists($progressFile)) {
                $progressData = json_decode(file_get_contents($progressFile), true);
                if ($progressData) {
                    $progressData['status'] = $exitCode === 0 ? 'completed' : 'failed';
                    $progressData['completed_at'] = date('Y-m-d H:i:s');
                    $progressData['stats'] = $stats;
                    file_put_contents($progressFile, json_encode($progressData, JSON_PRETTY_PRINT));
                }
            }
            
            // 2. ULM: Log success
            $this->logger->info('[PythonScraperService] Scraper execution completed successfully', [
                'scraper_id' => $scraper['id'] ?? 'unknown',
                'user_id' => $user?->id,
                'tenant_id' => $tenantId,
                'stats' => $stats,
                'costs' => $costs,
                'exit_code' => $exitCode,
                'progress_file' => $progressFile ?? null,
                'log_category' => 'SCRAPER_SUCCESS'
            ]);

            // 3. GDPR: Log user action (scraper execution)
            if ($user) {
                $this->auditService->logUserAction(
                    $user,
                    'scraper_executed',
                    [
                        'scraper_id' => $scraper['id'] ?? 'unknown',
                        'script' => $script,
                        'options' => $options,
                        'stats' => $stats,
                        'costs' => $costs,
                        'exit_code' => $exitCode,
                    ],
                    GdprActivityCategory::DATA_ACCESS
                );
            }

            return [
                'success' => true,
                'output' => $output,
                'error_output' => $errorOutput,
                'stats' => $stats,
                'costs' => $costs,
                'exit_code' => $exitCode,
                'progress_file' => basename($progressFile ?? '')
            ];
        } catch (\Exception $e) {
            // UEM: Log error (but don't return JsonResponse - return array instead)
            $this->errorManager->handle('SCRAPER_EXECUTION_FAILED', [
                'scraper_id' => $scraper['id'] ?? 'unknown',
                'error_message' => $e->getMessage(),
                'error_type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => $user?->id,
                'tenant_id' => $tenantId,
            ], $e);
            
            // Return array format for service method
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => 'SCRAPER_EXECUTION_FAILED',
                'error_type' => get_class($e),
                'output' => '',
                'error_output' => $e->getTraceAsString(),
            ];
        }
    }

    /**
     * Build command arguments from options
     */
    protected function buildCommandArgs(array $options): array
    {
        $args = [];

        // Year selection
        // Check both year_single and year (for compatibility)
        $yearSingle = $options['year_single'] ?? $options['year'] ?? null;
        
        if (!empty($yearSingle)) {
            // Per year_single, usa anno-inizio e anno-fine uguali
            $args[] = '--anno-inizio';
            $args[] = (string)$yearSingle;
            $args[] = '--anno-fine';
            $args[] = (string)$yearSingle;
        } elseif (!empty($options['year_range'])) {
            $range = explode('-', $options['year_range']);
            if (count($range) === 2) {
                $args[] = '--anno-inizio';
                $args[] = trim($range[0]);
                $args[] = '--anno-fine';
                $args[] = trim($range[1]);
            }
        } else {
            // Default: use current year if no year specified
            $currentYear = (int)date('Y');
            $args[] = '--anno-inizio';
            $args[] = (string)$currentYear;
            $args[] = '--anno-fine';
            $args[] = (string)$currentYear;
        }

        // Options
        // NOTE: Not all scrapers support --dry-run, so we skip it for firenze_deliberazioni
        // if (!empty($options['dry_run'])) {
        //     $args[] = '--dry-run';
        // }

        // MongoDB import flag
        if (!empty($options['mongodb_import']) && $options['mongodb_import'] !== false) {
            $args[] = '--mongodb';
            // ULM: Log MongoDB import enabled
            $this->logger->info('[PythonScraperService] MongoDB import enabled', [
                'mongodb_import' => $options['mongodb_import'],
                'tenant_id' => $options['tenant_id'] ?? null,
                'log_category' => 'SCRAPER_MONGODB'
            ]);
        } else {
            // ULM: Log MongoDB import disabled
            $this->logger->info('[PythonScraperService] MongoDB import disabled', [
                'mongodb_import' => $options['mongodb_import'] ?? null,
                'log_category' => 'SCRAPER_MONGODB'
            ]);
        }

        if (!empty($options['download_pdfs'])) {
            $args[] = '--download-pdfs';
        }

        if (!empty($options['tenant_id'])) {
            $args[] = '--tenant-id';
            $args[] = (string)$options['tenant_id'];
        }

        return $args;
    }

    /**
     * Parse Python output for statistics
     */
    protected function parseOutput(string $output): array
    {
        $stats = [
            'processed' => 0,
            'saved' => 0,
            'skipped' => 0,
            'chunks' => 0,
            'documents' => 0,
            'errors' => 0
        ];

        // Extract stats from report - Firenze Deliberazioni scraper format
        // Pattern principale: "📊 Totale atti estratti: {number}"
        if (preg_match('/📊\s*Totale atti estratti:\s*(\d+)/i', $output, $matches)) {
            $stats['processed'] = (int)$matches[1];
        }
        // Pattern alternativo senza emoji
        if (preg_match('/Totale atti estratti:\s*(\d+)/i', $output, $matches)) {
            $stats['processed'] = (int)$matches[1];
        }
        
        // Se non trovato, somma tutti gli atti trovati per tipo
        // Pattern: "✅ X atti trovati" o "📊 Totale {tipo}: X atti"
        if ($stats['processed'] === 0) {
            $totalAtti = 0;
            // Pattern per "✅ X atti trovati"
            if (preg_match_all('/✅\s*(\d+)\s*atti trovati/i', $output, $matches)) {
                foreach ($matches[1] as $count) {
                    $totalAtti += (int)$count;
                }
            }
            // Pattern per "📊 Totale {tipo}: X atti"
            if (preg_match_all('/📊\s*Totale.*?:\s*(\d+)\s*atti/i', $output, $matches)) {
                foreach ($matches[1] as $count) {
                    $totalAtti += (int)$count;
                }
            }
            if ($totalAtti > 0) {
                $stats['processed'] = $totalAtti;
            }
        }

        // Altri pattern generici
        if (preg_match('/Processati:\s*(\d+)/i', $output, $matches)) {
            $stats['processed'] = (int)$matches[1];
        }
        if (preg_match('/✅ Processati:\s*(\d+)/i', $output, $matches)) {
            $stats['processed'] = (int)$matches[1];
        }
        if (preg_match('/Saltati:\s*(\d+)/i', $output, $matches)) {
            $stats['skipped'] = (int)$matches[1];
        }
        if (preg_match('/⏭️.*Saltati:\s*(\d+)/i', $output, $matches)) {
            $stats['skipped'] = (int)$matches[1];
        }
        if (preg_match('/Errori:\s*(\d+)/i', $output, $matches)) {
            $stats['errors'] = (int)$matches[1];
        }
        if (preg_match('/Chunks totali:\s*(\d+)/i', $output, $matches)) {
            $stats['chunks'] = (int)$matches[1];
        }
        if (preg_match('/📝.*Chunks totali:\s*(\d+)/i', $output, $matches)) {
            $stats['chunks'] = (int)$matches[1];
        }
        if (preg_match('/Documenti:\s*(\d+)/i', $output, $matches)) {
            $stats['documents'] = (int)$matches[1];
        }
        
        // MongoDB stats se presenti
        if (preg_match('/📊\s*Statistiche MongoDB:/i', $output)) {
            if (preg_match('/✅\s*Importati:\s*(\d+)/i', $output, $matches)) {
                $stats['saved'] = (int)$matches[1];
            }
            if (preg_match('/⚠️\s*Saltati:\s*(\d+)/i', $output, $matches)) {
                $stats['skipped'] = (int)$matches[1];
            }
            if (preg_match('/❌\s*Errori:\s*(\d+)/i', $output, $matches)) {
                $stats['errors'] = (int)$matches[1];
            }
            if (preg_match('/📄\s*Totale chunks:\s*(\d+)/i', $output, $matches)) {
                $stats['chunks'] = (int)$matches[1];
            }
        }

        return $stats;
    }

    /**
     * Parse Python output for costs
     */
    protected function parseCosts(string $output): array
    {
        $costs = [
            'tokens' => 0,
            'cost_usd' => '0.00',
            'cost_eur' => '0.00',
            'model' => null
        ];

        if (preg_match('/🎫.*Token usati:\s*([\d,]+)/i', $output, $matches)) {
            $costs['tokens'] = (int)str_replace(',', '', $matches[1]);
        }
        if (preg_match('/💵.*Costo USD:\s*\$([\d.]+)/i', $output, $matches)) {
            $costs['cost_usd'] = $matches[1];
        }
        if (preg_match('/💶.*Costo EUR:\s*€([\d.]+)/i', $output, $matches)) {
            $costs['cost_eur'] = $matches[1];
        }
        if (preg_match('/🤖.*Modello:\s*([^\n]+)/i', $output, $matches)) {
            $costs['model'] = trim($matches[1]);
        }

        return $costs;
    }
}






