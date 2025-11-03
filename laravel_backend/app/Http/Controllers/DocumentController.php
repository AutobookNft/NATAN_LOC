<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PaAct;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * @package App\Http\Controllers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-02
 * @purpose Controller per gestione documenti (PA Acts, contratti, report, etc.)
 * 
 * CONTESTO: /home/fabio/NATAN_LOC
 * PERCORSO FILE: app/Http/Controllers/DocumentController.php
 * 
 * Gestisce operazioni CRUD per documenti multi-tenant:
 * - Lista documenti con filtro e ricerca
 * - Visualizzazione dettagli documento
 * - Upload nuovi documenti (da implementare)
 * - Eliminazione documenti
 */
class DocumentController extends Controller
{
    /**
     * Lista tutti i documenti con ricerca e filtro
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // IMPORTANTE: PaAct ha già Global Scope TenantScope, quindi mostra solo documenti del tenant corrente
        $query = PaAct::query();
        
        // Ricerca per titolo, protocollo, descrizione
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('protocol_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('issuer', 'like', "%{$search}%");
            });
        }
        
        // Filtro per tipo documento
        if ($request->filled('document_type')) {
            $query->where('document_type', $request->get('document_type'));
        }
        
        // Filtro per categoria
        if ($request->filled('act_category')) {
            $query->where('act_category', $request->get('act_category'));
        }
        
        // Filtro per status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        
        // Ordinamento (default: data protocollo DESC)
        $sortBy = $request->get('sort_by', 'protocol_date');
        $sortDir = $request->get('sort_dir', 'desc');
        
        // Validazione sort_by per sicurezza
        $allowedSorts = ['protocol_date', 'title', 'document_type', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'protocol_date';
        }
        $sortDir = strtolower($sortDir) === 'desc' ? 'desc' : 'asc';
        
        $query->orderBy($sortBy, $sortDir);
        
        // Paginazione
        $perPage = $request->get('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50, 100]) ? (int)$perPage : 15;
        $documents = $query->paginate($perPage)->withQueryString();
        
        // Statistiche
        $stats = [
            'total' => PaAct::count(),
            'by_type' => PaAct::selectRaw('document_type, COUNT(*) as count')
                ->groupBy('document_type')
                ->pluck('count', 'document_type')
                ->toArray(),
        ];
        
        return view('natan.documents.index', [
            'documents' => $documents,
            'stats' => $stats,
            'filters' => $request->only(['search', 'document_type', 'act_category', 'status', 'sort_by', 'sort_dir', 'per_page']),
        ]);
    }
    
    /**
     * Mostra dettagli di un documento
     * 
     * @param PaAct $document
     * @return View
     */
    public function show(PaAct $document): View
    {
        return view('natan.documents.show', [
            'document' => $document,
        ]);
    }
}



