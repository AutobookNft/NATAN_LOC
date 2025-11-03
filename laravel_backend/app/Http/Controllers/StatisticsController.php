<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\PaAct;
use App\Models\NatanChatMessage;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * @package App\Http\Controllers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-02
 * @purpose Controller per dashboard statistiche e analytics
 * 
 * CONTESTO: /home/fabio/NATAN_LOC
 * PERCORSO FILE: app/Http/Controllers/StatisticsController.php
 * 
 * Gestisce dashboard statistiche per il tenant corrente:
 * - Statistiche documenti
 * - Statistiche conversazioni
 * - Statistiche utilizzo
 */
class StatisticsController extends Controller
{
    /**
     * Dashboard statistiche principale
     * 
     * @param Request $request
     * @return View
     */
    public function dashboard(Request $request): View
    {
        // Ottieni tenant corrente
        $tenantId = app()->bound('currentTenantId') ? app('currentTenantId') : null;
        
        // Statistiche documenti
        $documentsStats = [
            'total' => PaAct::count(),
            'by_type' => PaAct::selectRaw('document_type, COUNT(*) as count')
                ->groupBy('document_type')
                ->pluck('count', 'document_type')
                ->toArray(),
            'by_month' => PaAct::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
                ->whereYear('created_at', now()->year)
                ->groupBy('year', 'month')
                ->orderBy('month', 'asc')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [sprintf('%d-%02d', $item->year, $item->month) => $item->count];
                })
                ->toArray(),
        ];
        
        // Statistiche conversazioni
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
        
        return view('natan.statistics.dashboard', [
            'generalStats' => $generalStats,
            'documentsStats' => $documentsStats,
            'conversationsStats' => $conversationsStats,
        ]);
    }
}



