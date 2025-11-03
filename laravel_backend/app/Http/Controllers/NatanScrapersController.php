<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Services\PythonScraperService;

/**
 * @package App\Http\Controllers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC - Python Scrapers Management)
 * @date 2025-01-28
 * @purpose Gestione interfaccia per esecuzione scraper Python con MongoDB import
 */
class NatanScrapersController extends Controller
{
    protected PythonScraperService $scraperService;

    public function __construct(PythonScraperService $scraperService)
    {
        $this->scraperService = $scraperService;
    }

    /**
     * Lista scraper disponibili
     */
    public function index(): View
    {
        $scrapers = $this->getAvailableScrapers();
        
        // Calcola statistiche
        $stats = [
            'total' => count($scrapers),
            'available' => count($scrapers),
            'total_documents' => $this->getTotalDocumentsInMongoDB(),
        ];

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

        return view('natan.scrapers.show', [
            'scraper' => $scraper,
        ]);
    }

    /**
     * Esegui scraper Python
     */
    public function run(Request $request, string $scraperId): JsonResponse
    {
        $validated = $request->validate([
            'year_single' => 'nullable|integer|min:2000|max:2030',
            'year_range' => 'nullable|string|regex:/^\d{4}-\d{4}$/',
            'dry_run' => 'nullable|boolean',
            'mongodb_import' => 'nullable|boolean',
            'download_pdfs' => 'nullable|boolean',
            'tenant_id' => 'nullable|integer|min:1',
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
            $result = $this->scraperService->executeScraper(
                $scraper,
                $validated
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview/Dry-run scraper
     */
    public function preview(Request $request, string $scraperId): JsonResponse
    {
        $validated = $request->validate([
            'year_single' => 'nullable|integer|min:2000|max:2030',
            'year_range' => 'nullable|string|regex:/^\d{4}-\d{4}$/',
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
            // Force dry-run
            $validated['dry_run'] = true;
            $validated['mongodb_import'] = false;
            
            $result = $this->scraperService->executeScraper(
                $scraper,
                $validated
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lista scraper Python disponibili
     */
    protected function getAvailableScrapers(): array
    {
        return [
            [
                'id' => 'firenze_deliberazioni',
                'name' => 'Deliberazioni Comune di Firenze',
                'script' => 'scrape_firenze_deliberazioni.py',
                'type' => 'api',
                'source_entity' => 'Comune di Firenze',
                'description' => 'Scraper per deliberazioni di giunta e consiglio, determinazioni dirigenziali dal portale trasparenza',
                'base_url' => 'https://accessoconcertificato.comune.fi.it',
                'legal_basis' => 'D.Lgs 33/2013 - Trasparenza Amministrativa',
            ],
            [
                'id' => 'albo_firenze_v2',
                'name' => 'Albo Pretorio Firenze (v2)',
                'script' => 'scrape_albo_firenze_v2.py',
                'type' => 'html',
                'source_entity' => 'Comune di Firenze',
                'description' => 'Scraper HTML per atti dall\'albo pretorio online',
                'base_url' => 'https://accessoconcertificato.comune.fi.it',
                'legal_basis' => 'D.Lgs 33/2013 - Trasparenza Amministrativa',
            ],
            [
                'id' => 'albo_pretorio_firenze',
                'name' => 'Albo Pretorio Firenze',
                'script' => 'scrape_albo_pretorio_firenze.py',
                'type' => 'html',
                'source_entity' => 'Comune di Firenze',
                'description' => 'Scraper alternativo per albo pretorio online',
                'base_url' => 'https://albopretorionline.comune.fi.it',
                'legal_basis' => 'D.Lgs 33/2013 - Trasparenza Amministrativa',
            ],
        ];
    }

    /**
     * Get total documents in MongoDB
     */
    protected function getTotalDocumentsInMongoDB(): int
    {
        // TODO: Implement MongoDB document count via API or service
        // For now return 0 - will be implemented when MongoDB integration is ready
        return 0;
    }
}

