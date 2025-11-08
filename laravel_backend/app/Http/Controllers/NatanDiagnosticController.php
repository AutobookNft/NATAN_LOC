<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Ultra\UltraLogManager\UltraLogManager;
use Ultra\ErrorManager\Interfaces\ErrorManagerInterface;

/**
 * @package App\Http\Controllers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (FlorenceEGI - Diagnostic Endpoint)
 * @date 2025-11-07
 * @purpose Diagnostic endpoint per verificare retrieval e debugging NO_DATA
 */
class NatanDiagnosticController extends Controller
{
    protected UltraLogManager $logger;
    protected ErrorManagerInterface $errorManager;
    protected string $pythonApiUrl;
    /**
     * @var array<int,int>
     */
    protected array $fallbackPythonPorts = [8000, 8001, 9000];

    public function __construct(
        UltraLogManager $logger,
        ErrorManagerInterface $errorManager
    ) {
        $this->logger = $logger;
        $this->errorManager = $errorManager;

        $configuredUrl = config('services.python_ai.url', 'http://localhost:8000');
        $this->pythonApiUrl = rtrim($configuredUrl, '/');

        $dynamicPortFile = '/tmp/natan_python_port.txt';
        if (file_exists($dynamicPortFile)) {
            $portValue = trim((string) file_get_contents($dynamicPortFile));
            if (is_numeric($portValue)) {
                $this->pythonApiUrl = sprintf('http://localhost:%d', (int) $portValue);
            }
        }
    }

    /**
     * Get candidate Python API base URLs (configured + fallbacks)
     *
     * @return array<int,string>
     */
    protected function getCandidatePythonUrls(): array
    {
        $urls = [];
        $urls[] = $this->pythonApiUrl;

        foreach ($this->fallbackPythonPorts as $port) {
            $urls[] = sprintf('http://localhost:%d', $port);
        }

        return array_values(array_unique(array_filter($urls)));
    }

    /**
     * Diagnostica retrieval - Proxy verso Python FastAPI
     */
    public function retrieval(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'required|integer|min:1',
            'query' => 'nullable|string|max:1000',
        ]);

        try {
            $this->logger->info('[NatanDiagnosticController] Diagnostic retrieval request', [
                'tenant_id' => $validated['tenant_id'],
                'has_query' => !empty($validated['query']),
                'initial_python_api_url' => $this->pythonApiUrl,
                'log_category' => 'DIAGNOSTIC'
            ]);

            $payload = [
                'tenant_id' => $validated['tenant_id'],
                'query' => $validated['query'] ?? null,
            ];

            $candidateUrls = $this->getCandidatePythonUrls();
            $errors = [];

            foreach ($candidateUrls as $candidateUrl) {
                try {
                    $endpoint = rtrim($candidateUrl, '/') . '/api/v1/diagnostic/retrieval';
                    $this->logger->info('[NatanDiagnosticController] Trying Python diagnostic endpoint', [
                        'url' => $endpoint,
                        'log_category' => 'DIAGNOSTIC'
                    ]);

                    $response = Http::timeout(30)->post($endpoint, $payload);

                    if ($response->successful()) {
                        $data = $response->json();
                        $data['python_url_used'] = $candidateUrl;
                        return response()->json($data);
                    }

                    $errors[] = [
                        'url' => $candidateUrl,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ];
                } catch (\Throwable $throwable) {
                    $errors[] = [
                        'url' => $candidateUrl,
                        'status' => null,
                        'body' => $throwable->getMessage(),
                    ];
                }
            }

            return response()->json([
                'success' => false,
                'error' => 'Python API non raggiungibile',
                'tried_urls' => $candidateUrls,
                'diagnostic_errors' => $errors,
            ], 503);

        } catch (\Exception $e) {
            // UEM: Handle error
            return $this->errorManager->handle('DIAGNOSTIC_ERROR', [
                'tenant_id' => $validated['tenant_id'] ?? null,
                'error_message' => $e->getMessage(),
                'error_type' => get_class($e),
            ], $e);
        }
    }

    /**
     * Diagnostica rapida MongoDB
     */
    public function mongodb(Request $request, int $tenantId): JsonResponse
    {
        try {
            $this->logger->info('[NatanDiagnosticController] MongoDB diagnostic request', [
                'tenant_id' => $tenantId,
                'initial_python_api_url' => $this->pythonApiUrl,
                'log_category' => 'DIAGNOSTIC'
            ]);

            $candidateUrls = $this->getCandidatePythonUrls();
            $errors = [];

            foreach ($candidateUrls as $candidateUrl) {
                try {
                    $endpoint = rtrim($candidateUrl, '/') . "/api/v1/diagnostic/mongodb/{$tenantId}";
                    $response = Http::timeout(20)->get($endpoint);

                    if ($response->successful()) {
                        $data = $response->json();
                        $data['python_url_used'] = $candidateUrl;
                        return response()->json($data);
                    }

                    $errors[] = [
                        'url' => $candidateUrl,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ];
                } catch (\Throwable $throwable) {
                    $errors[] = [
                        'url' => $candidateUrl,
                        'status' => null,
                        'body' => $throwable->getMessage(),
                    ];
                }
            }

            return response()->json([
                'success' => false,
                'error' => 'Python API non raggiungibile',
                'tried_urls' => $candidateUrls,
                'diagnostic_errors' => $errors,
            ], 503);

        } catch (\Exception $e) {
            return $this->errorManager->handle('DIAGNOSTIC_ERROR', [
                'tenant_id' => $tenantId,
                'error_message' => $e->getMessage(),
            ], $e);
        }
    }

    /**
     * Diagnostica completa USE pipeline (proxy diretto a Python use/query)
     */
    public function useQuery(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'required|integer|min:1',
            'query' => 'required|string|max:1000',
            'persona' => 'nullable|string|max:50',
        ]);

        try {
            $this->logger->info('[NatanDiagnosticController] USE pipeline diagnostic request', [
                'tenant_id' => $validated['tenant_id'],
                'persona' => $validated['persona'] ?? 'strategic',
                'initial_python_api_url' => $this->pythonApiUrl,
                'log_category' => 'DIAGNOSTIC'
            ]);

            $payload = [
                'question' => $validated['query'],
                'tenant_id' => $validated['tenant_id'],
                'persona' => $validated['persona'] ?? 'strategic',
                'model' => config('natan.default_model', 'anthropic.sonnet-3.5'),
                'debug' => true,
            ];

            $candidateUrls = $this->getCandidatePythonUrls();
            $errors = [];

            foreach ($candidateUrls as $candidateUrl) {
                try {
                    $endpoint = rtrim($candidateUrl, '/') . '/api/v1/use/query';
                    $response = Http::timeout(120)->post($endpoint, $payload);

                    if ($response->successful()) {
                        $data = $response->json();
                        $data['python_url_used'] = $candidateUrl;
                        return response()->json($data);
                    }

                    $errors[] = [
                        'url' => $candidateUrl,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ];
                } catch (\Throwable $throwable) {
                    $errors[] = [
                        'url' => $candidateUrl,
                        'status' => null,
                        'body' => $throwable->getMessage(),
                    ];
                }
            }

            return response()->json([
                'success' => false,
                'error' => 'Python USE endpoint non raggiungibile',
                'tried_urls' => $candidateUrls,
                'diagnostic_errors' => $errors,
            ], 503);

        } catch (\Exception $e) {
            return $this->errorManager->handle('DIAGNOSTIC_ERROR', [
                'tenant_id' => $validated['tenant_id'] ?? null,
                'error_message' => $e->getMessage(),
                'error_type' => get_class($e),
            ], $e);
        }
    }
}

