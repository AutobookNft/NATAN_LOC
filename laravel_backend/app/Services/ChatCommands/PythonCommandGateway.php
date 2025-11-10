<?php

declare(strict_types=1);

namespace App\Services\ChatCommands;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Ultra\UltraLogManager\UltraLogManager;

/**
 * @package App\Services\ChatCommands
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (FlorenceEGI - Direct Command Queries)
 * @date 2025-11-08
 * @purpose Gateway HTTP verso il servizio Python per i comandi chat
 */
final class PythonCommandGateway
{
    public function __construct(
        private UltraLogManager $logger,
    ) {
    }

    /**
     * Esegue il comando @atto sul servizio Python
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function fetchAtto(array $payload): array
    {
        return $this->post('/api/v1/commands/atto', $payload);
    }

    /**
     * Esegue il comando @atti sul servizio Python
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function fetchAtti(array $payload): array
    {
        return $this->post('/api/v1/commands/atti', $payload);
    }

    /**
     * Esegue il comando @stats sul servizio Python
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function fetchStats(array $payload): array
    {
        return $this->post('/api/v1/commands/stats', $payload);
    }

    /**
     * @return array<string, mixed>
     */
    public function executeNaturalQuery(int $tenantId, int $userId, string $text, ?int $limit = null): array
    {
        $payload = [
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'text' => $text,
        ];

        if ($limit !== null) {
            $payload['limit'] = $limit;
        }

        return $this->post('/api/v1/commands/natural-query', $payload, false);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @throws RuntimeException Quando tutti gli endpoint candidati risultano irraggiungibili
     */
    private function post(string $endpoint, array $payload, bool $requireSuccess = true): array
    {
        $errors = [];

        foreach ($this->getPythonApiCandidateUrls() as $baseUrl) {
            $url = rtrim($baseUrl, '/') . $endpoint;

            try {
                $this->logger->debug('[PythonCommandGateway] POST request', [
                    'url' => $url,
                    'payload' => $payload,
                    'log_category' => 'CHAT_COMMAND_GATEWAY',
                ]);

                $response = Http::timeout(25)->post($url, $payload);

                if ($response->successful()) {
                    $json = $response->json();
                    if (is_array($json)) {
                        if (!$requireSuccess) {
                            return $json;
                        }

                        if ($json['success'] ?? false) {
                            return $json;
                        }

                        $errors[] = [
                            'url' => $url,
                            'status' => $response->status(),
                            'body' => substr($response->body(), 0, 500),
                        ];
                        continue;
                    }

                    $errors[] = [
                        'url' => $url,
                        'status' => $response->status(),
                        'body' => 'Invalid JSON response',
                    ];
                    continue;
                }

                $errors[] = [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 500),
                ];
            } catch (\Throwable $exception) {
                $errors[] = [
                    'url' => $url,
                    'status' => null,
                    'body' => $exception->getMessage(),
                ];
            }
        }

        $this->logger->error('[PythonCommandGateway] All endpoints failed', [
            'endpoint' => $endpoint,
            'payload' => $payload,
            'errors' => $errors,
            'log_category' => 'CHAT_COMMAND_GATEWAY',
        ]);

        throw new RuntimeException(__('natan.commands.errors.gateway_unreachable'));
    }

    /**
     * @return array<int, string>
     */
    private function getPythonApiCandidateUrls(): array
    {
        $urls = [];

        $configuredUrl = config('services.python_ai.url', 'http://localhost:8001');
        if (!empty($configuredUrl)) {
            $urls[] = rtrim((string) $configuredUrl, '/');
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
}


