<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\PaAct;
use App\Models\NatanChatMessage;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Ultra\UltraLogManager\UltraLogManager;

/**
 * @package App\Http\Controllers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 2.0.0 (NATAN_LOC - MongoDB Statistics Integration)
 * @date 2025-11-06
 * @purpose Controller per dashboard statistiche e analytics con integrazione MongoDB
 * 
 * CONTESTO: /home/fabio/NATAN_LOC
 * PERCORSO FILE: app/Http/Controllers/StatisticsController.php
 * 
 * Gestisce dashboard statistiche per il tenant corrente:
 * - Statistiche documenti (da MongoDB)
 * - Statistiche conversazioni (da Laravel)
 * - Statistiche utilizzo
 */
class StatisticsController extends Controller
{
    protected UltraLogManager $logger;

    public function __construct(UltraLogManager $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Dashboard statistiche principale
     * 
     * @param Request $request
     * @return View
     */
    public function dashboard(Request $request): View
    {
        // Ottieni tenant corrente
        $tenantId = app()->bound('currentTenantId') ? app('currentTenantId') : 2;
        
        // Statistiche documenti da MongoDB (filtra per anno corrente per by_month)
        $documentsStats = $this->getMongoDBDocumentsStatistics($tenantId, now()->year);
        
        // Statistiche conversazioni da Laravel
        $conversationsStats = [
            'total' => NatanChatMessage::distinct('session_id')->count(),
            'total_messages' => NatanChatMessage::count(),
            'by_month' => NatanChatMessage::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
                ->whereYear('created_at', now()->year)
                ->groupBy('year', 'month')
                ->orderBy('month', 'asc')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [sprintf('%d-%02d', $item->year, $item->month) => $item->count];
                })
                ->toArray(),
        ];
        
        // Statistiche generali
        $generalStats = [
            'tenant' => $tenantId ? Tenant::find($tenantId) : null,
            'total_documents' => $documentsStats['total'],
            'total_conversations' => $conversationsStats['total'],
            'total_messages' => $conversationsStats['total_messages'],
        ];
        
        $this->logger->info('[StatisticsController] Dashboard statistics calculated', [
            'tenant_id' => $tenantId,
            'total_documents' => $documentsStats['total'],
            'total_conversations' => $conversationsStats['total'],
            'log_category' => 'STATISTICS_DASHBOARD'
        ]);
        
        return view('natan.statistics.dashboard', [
            'generalStats' => $generalStats,
            'documentsStats' => $documentsStats,
            'conversationsStats' => $conversationsStats,
        ]);
    }

    /**
     * Get documents statistics from MongoDB
     * 
     * @param int $tenantId
     * @param int|null $year Optional year filter
     * @return array
     */
    protected function getMongoDBDocumentsStatistics(int $tenantId, ?int $year = null): array
    {
        try {
            // Esegui script Python per ottenere statistiche MongoDB
            $pythonExecutable = base_path('../python_ai_service/venv/bin/python');
            if (!file_exists($pythonExecutable)) {
                $pythonExecutable = 'python3';
            }
            
            $scriptPath = base_path('../python_ai_service/app/scripts/get_mongodb_statistics.py');
            
            $command = sprintf(
                '%s %s --tenant-id %d%s',
                escapeshellarg($pythonExecutable),
                escapeshellarg($scriptPath),
                $tenantId,
                $year ? ' --year ' . $year : ''
            );
            
            $this->logger->info('[StatisticsController] Executing MongoDB statistics command', [
                'tenant_id' => $tenantId,
                'year' => $year,
                'command' => $command,
                'log_category' => 'STATISTICS_MONGODB'
            ]);
            
            $output = shell_exec($command . ' 2>&1');
            $stats = json_decode($output, true);
            
            if (!$stats || isset($stats['error'])) {
                $this->logger->warning('[StatisticsController] MongoDB statistics failed', [
                    'tenant_id' => $tenantId,
                    'error' => $stats['error'] ?? 'Invalid JSON response',
                    'output' => substr($output, 0, 500),
                    'log_category' => 'STATISTICS_MONGODB'
                ]);
                
                // Fallback: return empty stats
                return [
                    'total' => 0,
                    'by_type' => [],
                    'by_month' => []
                ];
            }
            
            $this->logger->info('[StatisticsController] MongoDB statistics retrieved', [
                'tenant_id' => $tenantId,
                'total' => $stats['total'] ?? 0,
                'by_type_count' => count($stats['by_type'] ?? []),
                'by_month_count' => count($stats['by_month'] ?? []),
                'log_category' => 'STATISTICS_MONGODB'
            ]);
            
            return [
                'total' => $stats['total'] ?? 0,
                'by_type' => $stats['by_type'] ?? [],
                'by_month' => $stats['by_month'] ?? []
            ];
        } catch (\Exception $e) {
            $this->logger->error('[StatisticsController] MongoDB statistics exception', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'log_category' => 'STATISTICS_MONGODB'
            ]);
            
            // Fallback: return empty stats
            return [
                'total' => 0,
                'by_type' => [],
                'by_month' => []
            ];
        }
    }
}



