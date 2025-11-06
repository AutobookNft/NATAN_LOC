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
        
        // Calcola costi reali dai messaggi (assistant messages con tokens)
        $totalCost = 0.0;
        $thisMonthCost = 0.0;
        $lastMonthCost = 0.0;
        $totalMessagesWithCost = 0;
        
        // Get all assistant messages with tokens
        $assistantMessages = NatanChatMessage::where('role', 'assistant')
            ->where(function($query) {
                $query->whereNotNull('tokens_input')
                      ->orWhereNotNull('tokens_output');
            })
            ->get();
        
        foreach ($assistantMessages as $msg) {
            if ($msg->tokens_input || $msg->tokens_output) {
                $cost = \App\Services\CostCalculator::calculateCost([
                    'input' => $msg->tokens_input ?? 0,
                    'output' => $msg->tokens_output ?? 0,
                ], $msg->ai_model);
                
                $totalCost += $cost;
                $totalMessagesWithCost++;
                
                // This month
                if ($msg->created_at->year === now()->year && $msg->created_at->month === now()->month) {
                    $thisMonthCost += $cost;
                }
                
                // Last month
                $lastMonth = now()->subMonth();
                if ($msg->created_at->year === $lastMonth->year && $msg->created_at->month === $lastMonth->month) {
                    $lastMonthCost += $cost;
                }
            }
        }
        
        // Calculate average cost per message
        $averagePerMessage = $totalMessagesWithCost > 0 
            ? $totalCost / $totalMessagesWithCost 
            : 0.0;
        
        $costsEstimate = [
            'total_estimated' => $totalCost,
            'this_month' => $thisMonthCost,
            'last_month' => $lastMonthCost,
            'average_per_message' => $averagePerMessage,
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


