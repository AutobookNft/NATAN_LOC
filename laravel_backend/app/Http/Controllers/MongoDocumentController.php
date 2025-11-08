<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Ultra\UltraLogManager\UltraLogManager;

/**
 * @package App\Http\Controllers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC - MongoDB Document Viewer)
 * @date 2025-11-06
 * @purpose Controller per visualizzazione documenti da MongoDB
 * 
 * CONTESTO: /home/fabio/NATAN_LOC
 * PERCORSO FILE: app/Http/Controllers/MongoDocumentController.php
 * 
 * Gestisce visualizzazione documenti da MongoDB:
 * - Recupera documento per document_id
 * - Mostra PDF viewer se disponibile
 * - Mostra metadati e contenuto completo
 */
class MongoDocumentController extends Controller
{
    protected UltraLogManager $logger;

    public function __construct(UltraLogManager $logger)
    {
        $this->logger = $logger;
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
     * Visualizza documento da MongoDB
     * 
     * @param Request $request
     * @param string $documentId
     * @return View|\Illuminate\Http\RedirectResponse
     */
    public function view(Request $request, string $documentId): View|\Illuminate\Http\RedirectResponse
    {
        try {
            $tenantId = Auth::user()?->tenant_id ?? app('currentTenantId') ?? 2;
            
            $this->logger->info('[MongoDocumentController] Loading document', [
                'document_id' => $documentId,
                'tenant_id' => $tenantId,
                'log_category' => 'DOCUMENT_VIEW'
            ]);
            
            // Recupera documento da MongoDB
            $document = $this->getDocumentFromMongoDB($documentId, $tenantId);
            
            if (!$document) {
                $this->logger->warning('[MongoDocumentController] Document not found', [
                    'document_id' => $documentId,
                    'tenant_id' => $tenantId,
                    'log_category' => 'DOCUMENT_VIEW'
                ]);
                abort(404, 'Documento non trovato');
            }
            
            $this->logger->info('[MongoDocumentController] Document loaded successfully', [
                'document_id' => $documentId,
                'title' => $document['title'] ?? 'N/A',
                'log_category' => 'DOCUMENT_VIEW'
            ]);
            
            // Genera token signed per accesso PDF (per iframe)
            $pdfToken = encrypt(json_encode([
                'document_id' => $documentId,
                'tenant_id' => $tenantId,
                'expires_at' => now()->addHours(1)->timestamp
            ]));
            
            return view('natan.documents.mongo-view', [
                'document' => $document,
                'documentId' => $documentId,
                'pdfToken' => $pdfToken,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('[MongoDocumentController] Error loading document', [
                'document_id' => $documentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'log_category' => 'DOCUMENT_VIEW'
            ]);
            
            return redirect()->route('natan.chat')
                ->with('error', 'Errore nel caricamento del documento');
        }
    }
    
    /**
     * Get document from MongoDB
     * 
     * @param string $documentId
     * @param int $tenantId
     * @return array|null
     */
    protected function getDocumentFromMongoDB(string $documentId, int $tenantId): ?array
    {
        $candidateUrls = $this->getPythonApiCandidateUrls();
        $errors = [];

        foreach ($candidateUrls as $baseUrl) {
            $endpoint = rtrim($baseUrl, '/') . sprintf(
                '/api/v1/diagnostic/document/%d/%s',
                $tenantId,
                rawurlencode($documentId)
            );

            try {
                $response = Http::timeout(30)->get($endpoint);

                if ($response->successful()) {
                    $data = $response->json();

                    if (($data['success'] ?? false) && isset($data['document']) && is_array($data['document'])) {
                        return $data['document'];
                    }

                    $errors[] = [
                        'url' => $endpoint,
                        'status' => $response->status(),
                        'body' => substr($response->body(), 0, 500),
                    ];
                } else {
                    $errors[] = [
                        'url' => $endpoint,
                        'status' => $response->status(),
                        'body' => substr($response->body(), 0, 500),
                    ];
                }
            } catch (\Throwable $throwable) {
                $errors[] = [
                    'url' => $endpoint,
                    'status' => null,
                    'body' => $throwable->getMessage(),
                ];
            }
        }

        $this->logger->warning('[MongoDocumentController] Unable to load document from Python service', [
            'document_id' => $documentId,
            'tenant_id' => $tenantId,
            'attempted_urls' => $candidateUrls,
            'errors' => $errors,
            'log_category' => 'DOCUMENT_VIEW'
        ]);

        return null;
    }
    
    /**
     * Proxy per servire PDF da URL esterno
     * Risolve problemi di X-Frame-Options e CORS
     * 
     * @param Request $request
     * @param string $documentId
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function pdfProxy(Request $request, string $documentId)
    {
        try {
            // Verifica token di autenticazione se presente (per accesso iframe)
            $token = $request->query('token');
            $tenantId = null;
            
            if ($token) {
                // Verifica token (signed URL)
                try {
                    $decoded = decrypt($token);
                    $data = json_decode($decoded, true);
                    if ($data && isset($data['document_id']) && $data['document_id'] === $documentId) {
                        // Verifica scadenza token
                        if (isset($data['expires_at']) && $data['expires_at'] > time()) {
                            $tenantId = $data['tenant_id'] ?? 2;
                        } else {
                            $this->logger->warning('[MongoDocumentController] PDF token expired', [
                                'document_id' => $documentId,
                                'log_category' => 'PDF_PROXY'
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    $this->logger->warning('[MongoDocumentController] PDF token invalid', [
                        'document_id' => $documentId,
                        'error' => $e->getMessage(),
                        'log_category' => 'PDF_PROXY'
                    ]);
                    // Token non valido, usa autenticazione normale
                }
            }
            
            // Fallback a autenticazione normale se non c'è token valido
            if (!$tenantId) {
                // Se non c'è utente autenticato e non c'è token valido, nega accesso
                if (!Auth::check()) {
                    abort(401, 'Accesso non autorizzato');
                }
                $tenantId = Auth::user()?->tenant_id ?? app('currentTenantId') ?? 2;
            }
            
            $this->logger->info('[MongoDocumentController] PDF proxy request', [
                'document_id' => $documentId,
                'tenant_id' => $tenantId,
                'has_token' => !empty($token),
                'log_category' => 'PDF_PROXY'
            ]);
            
            // Recupera documento da MongoDB
            $document = $this->getDocumentFromMongoDB($documentId, $tenantId);
            
            if (!$document) {
                $this->logger->warning('[MongoDocumentController] Document not found for PDF proxy', [
                    'document_id' => $documentId,
                    'log_category' => 'PDF_PROXY'
                ]);
                abort(404, 'Documento non trovato');
            }
            
            $pdfUrl = $document['metadata']['pdf_url'] ?? $document['pdf_url'] ?? null;
            
            if (!$pdfUrl) {
                $this->logger->warning('[MongoDocumentController] PDF URL not available', [
                    'document_id' => $documentId,
                    'log_category' => 'PDF_PROXY'
                ]);
                abort(404, 'PDF URL non disponibile');
            }
            
            $this->logger->info('[MongoDocumentController] Downloading PDF from URL', [
                'document_id' => $documentId,
                'pdf_url' => $pdfUrl,
                'log_category' => 'PDF_PROXY'
            ]);
            
            // Scarica PDF da URL esterno
            $ch = curl_init($pdfUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; NATAN/1.0)');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/pdf,application/octet-stream,*/*'
            ]);
            
            $pdfContent = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            $curlError = curl_error($ch);
            $contentLength = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
            curl_close($ch);
            
            if ($httpCode !== 200 || !$pdfContent) {
                $this->logger->error('[MongoDocumentController] PDF download failed', [
                    'document_id' => $documentId,
                    'pdf_url' => $pdfUrl,
                    'http_code' => $httpCode,
                    'curl_error' => $curlError,
                    'content_length' => $contentLength,
                    'log_category' => 'PDF_PROXY'
                ]);
                abort(500, 'Errore nel download del PDF: ' . ($curlError ?: "HTTP $httpCode"));
            }
            
            // Verifica che sia effettivamente un PDF
            $isPdf = strpos($contentType, 'application/pdf') !== false || strpos($pdfContent, '%PDF') === 0;
            if (!$isPdf) {
                $this->logger->warning('[MongoDocumentController] PDF content type mismatch', [
                    'document_id' => $documentId,
                    'content_type' => $contentType,
                    'content_preview' => substr($pdfContent, 0, 100),
                    'log_category' => 'PDF_PROXY'
                ]);
                // Non blocchiamo, potrebbe essere un PDF con content-type sbagliato
            }
            
            $this->logger->info('[MongoDocumentController] PDF downloaded successfully', [
                'document_id' => $documentId,
                'content_length' => strlen($pdfContent),
                'content_type' => $contentType,
                'log_category' => 'PDF_PROXY'
            ]);
            
            // Restituisci PDF con header corretti per embedding in iframe
            // Content-Disposition: inline permette la visualizzazione nel browser
            return response($pdfContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="documento.pdf"')
                ->header('Content-Length', strlen($pdfContent))
                ->header('X-Content-Type-Options', 'nosniff')
                ->header('Cache-Control', 'public, max-age=3600')
                ->header('Accept-Ranges', 'bytes')
                ->header('Pragma', 'public');
                
        } catch (\Exception $e) {
            $this->logger->error('[MongoDocumentController] PDF proxy error', [
                'document_id' => $documentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'log_category' => 'PDF_PROXY'
            ]);
            
            abort(500, 'Errore nel caricamento del PDF: ' . $e->getMessage());
        }
    }
}

