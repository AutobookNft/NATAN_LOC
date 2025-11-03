<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * @package App\Services
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC - Python Scraper Execution)
 * @date 2025-01-28
 * @purpose Service per eseguire script Python scraper e parsare output
 */
class PythonScraperService
{
    /**
     * Esegue uno scraper Python
     */
    public function executeScraper(array $scraper, array $options): array
    {
        try {
            $script = $scraper['script'];
            $scriptsPath = base_path('../EGI/scripts');
            $scriptPath = $scriptsPath . '/' . $script;

            if (!file_exists($scriptPath)) {
                throw new \Exception("Script Python non trovato: {$scriptPath}");
            }

            // Build command arguments
            $args = $this->buildCommandArgs($options);
            
            // Build command
            $command = sprintf(
                'cd %s && python3 %s %s 2>&1',
                escapeshellarg($scriptsPath),
                escapeshellarg($script),
                implode(' ', array_map('escapeshellarg', $args))
            );

            Log::info('[PythonScraperService] Executing scraper', [
                'scraper' => $scraper['id'],
                'script' => $script,
                'command' => $command,
                'options' => $options
            ]);

            // Execute with timeout (30 minutes max)
            $timeout = 1800;
            $descriptors = [
                0 => ['pipe', 'r'],  // stdin
                1 => ['pipe', 'w'],  // stdout
                2 => ['pipe', 'w'],  // stderr
            ];

            $process = proc_open($command, $descriptors, $pipes);

            if (!is_resource($process)) {
                throw new \Exception('Impossibile avviare il processo Python');
            }

            // Close stdin
            fclose($pipes[0]);

            // Read output (non-blocking)
            $output = '';
            $errorOutput = '';
            $startTime = time();

            stream_set_blocking($pipes[1], false);
            stream_set_blocking($pipes[2], false);

            while (true) {
                $status = proc_get_status($process);

                // Read stdout
                while (($line = fgets($pipes[1])) !== false) {
                    $output .= $line;
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
                    throw new \Exception("Timeout esecuzione scraper (max {$timeout}s)");
                }

                usleep(100000); // 100ms
            }

            // Close pipes
            fclose($pipes[1]);
            fclose($pipes[2]);

            // Get exit code
            $exitCode = proc_close($process);

            // Parse output for stats and costs
            $stats = $this->parseOutput($output);
            $costs = $this->parseCosts($output);

            if ($exitCode !== 0) {
                throw new \Exception("Script Python fallito con exit code {$exitCode}. Error: " . substr($errorOutput, 0, 500));
            }

            return [
                'success' => true,
                'output' => $output,
                'error_output' => $errorOutput,
                'stats' => $stats,
                'costs' => $costs,
                'exit_code' => $exitCode
            ];
        } catch (\Exception $e) {
            Log::error('[PythonScraperService] Scraper execution error', [
                'scraper' => $scraper['id'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
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
        if (!empty($options['year_single'])) {
            $args[] = '--anno';
            $args[] = (string)$options['year_single'];
        } elseif (!empty($options['year_range'])) {
            $range = explode('-', $options['year_range']);
            if (count($range) === 2) {
                $args[] = '--anno-inizio';
                $args[] = trim($range[0]);
                $args[] = '--anno-fine';
                $args[] = trim($range[1]);
            }
        }

        // Options
        if (!empty($options['dry_run'])) {
            $args[] = '--dry-run';
        }

        if (!empty($options['mongodb_import'])) {
            $args[] = '--mongodb';
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

        // Extract stats from report
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
        if (preg_match('/Totale atti estratti:\s*(\d+)/i', $output, $matches)) {
            $stats['processed'] = (int)$matches[1];
        }
        if (preg_match('/✅.*Totale atti estratti:\s*(\d+)/i', $output, $matches)) {
            $stats['processed'] = (int)$matches[1];
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






