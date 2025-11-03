<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\NatanChatMessage;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

/**
 * @package App\Http\Controllers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-02
 * @purpose Controller per dashboard costi AI e utilizzo servizi
 * 
 * CONTESTO: /home/fabio/NATAN_LOC
 * PERCORSO FILE: app/Http/Controllers/AiCostsController.php
 * 
 * Gestisce dashboard costi AI per il tenant corrente:
 * - Statistiche utilizzo API
 * - Costi per modello
 * - Storico costi
 * - Proiezioni future
 */
class AiCostsController extends Controller
{
    /**
     * Dashboard costi AI principale
     * 
     * @param Request $request
     * @return View
     */
    public function dashboard(Request $request): View
    {
        // Ottieni tenant corrente
        $tenantId = app()->bound('currentTenantId') ? app('currentTenantId') : null;
        
        // Statistiche messaggi (proxy per utilizzo API)
        $messagesStats = [
            'total' => NatanChatMessage::count(),
            'by_month' => NatanChatMessage::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
                ->whereYear('created_at', now()->year)
                ->groupBy('year', 'month')
                ->orderBy('month', 'asc')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [sprintf('%d-%02d', $item->year, $item->month) => $item->count];
                })
                ->toArray(),
            'this_month' => NatanChatMessage::whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count(),
            'last_month' => NatanChatMessage::whereYear('created_at', now()->subMonth()->year)
                ->whereMonth('created_at', now()->subMonth()->month)
                ->count(),
        ];
        
        // Stima costi (placeholder - da integrare con dati reali)
        $costsEstimate = [
            'total_estimated' => $messagesStats['total'] * 0.001, // Stima: €0.001 per messaggio
            'this_month' => $messagesStats['this_month'] * 0.001,
            'last_month' => $messagesStats['last_month'] * 0.001,
            'average_per_message' => 0.001,
        ];
        
        // Statistiche generali
        $generalStats = [
            'tenant' => $tenantId ? Tenant::find($tenantId) : null,
            'total_messages' => $messagesStats['total'],
            'this_month_messages' => $messagesStats['this_month'],
            'estimated_cost' => $costsEstimate['total_estimated'],
            'estimated_cost_this_month' => $costsEstimate['this_month'],
        ];
        
        return view('natan.ai-costs.dashboard', [
            'generalStats' => $generalStats,
            'messagesStats' => $messagesStats,
            'costsEstimate' => $costsEstimate,
        ]);
    }
}


