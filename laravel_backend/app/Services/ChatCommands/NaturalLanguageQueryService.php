<?php

declare(strict_types=1);

namespace App\Services\ChatCommands;

use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Ultra\UltraLogManager\UltraLogManager;

/**
 * @package App\Services\ChatCommands
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (FlorenceEGI - Natural Query Layer 0)
 * @date 2025-11-09
 * @purpose Interpreta richieste naturali e restituisce CommandResult basato su MongoDB
 */
final class NaturalLanguageQueryService
{
    public function __construct(
        private PythonCommandGateway $gateway,
        private UltraLogManager $logger
    ) {
    }

    /**
     * @return array{
     *     success: bool,
     *     code: string|null,
     *     message: string,
     *     result: CommandResult|null,
     *     metadata: array<string,mixed>
     * }
     */
    public function execute(string $text, User $user, ?Tenant $tenant = null): array
    {
        $tenant ??= $user->tenant;
        $tenantId = $tenant?->id ?? app('currentTenantId') ?? 2;

        $this->logger->info('[NaturalLanguageQueryService] Processing natural query', [
            'user_id' => $user->id,
            'tenant_id' => $tenantId,
            'snippet' => mb_substr($text, 0, 200),
            'log_category' => 'NATURAL_QUERY',
        ]);

        try {
            $this->logger->debug('[NaturalLanguageQueryService] Sending to Python gateway', [
                'text' => $text,
            ]);
            $response = $this->gateway->executeNaturalQuery(
                $tenantId,
                (int) $user->id,
                $text
            );
            $this->logger->debug('[NaturalLanguageQueryService] Response from Python gateway', [
                'raw_response' => $response,
            ]);
        } catch (\Throwable $throwable) {
            Log::error('[NaturalLanguageQueryService] Gateway failure', [
                'user_id' => $user->id,
                'tenant_id' => $tenantId,
                'message' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'code' => 'gateway_unreachable',
                'message' => __('natan.commands.errors.gateway_unreachable'),
                'result' => null,
                'metadata' => [],
            ];
        }

        $success = (bool) ($response['success'] ?? false);
        $code = $response['code'] ?? null;
        $ragHint = $response['rag_hint'] ?? null;

        if (!$success) {
            $ragHint = $response['rag_hint'] ?? null;
            if (
                $code === 'no_results'
                && is_array($ragHint)
                && ($ragHint['should_run'] ?? false) === true
            ) {
                $this->logger->info('[NaturalLanguageQueryService] No documents but analytical intent detected, delegating to RAG', [
                    'user_id' => $user->id,
                    'tenant_id' => $tenantId,
                    'original_text' => $text,
                    'log_category' => 'NATURAL_QUERY',
                ]);

                return [
                    'success' => false,
                    'code' => 'analysis_required',
                    'message' => '',
                    'result' => null,
                    'metadata' => [
                        'rag_hint' => $ragHint,
                        'query' => $response['query'] ?? null,
                        'fallback' => $response['fallback'] ?? null,
                        'original_text' => $response['original_text'] ?? $text,
                    ],
                ];
            }

            $message = $this->resolveErrorMessage($code, $response);

            return [
                'success' => false,
                'code' => $code,
                'message' => $message,
                'result' => null,
                'metadata' => Arr::only($response, ['rag_hint', 'query', 'reason', 'retry_after_minutes']),
            ];
        }

        if (is_array($ragHint) && ($ragHint['should_run'] ?? false) === true) {
            $this->logger->info('[NaturalLanguageQueryService] Redirecting to RAG for analytical query', [
                'user_id' => $user->id,
                'tenant_id' => $tenantId,
                'original_text' => $text,
                'rag_hint' => $ragHint,
                'log_category' => 'NATURAL_QUERY',
            ]);

            return [
                'success' => false,
                'code' => 'analysis_required',
                'message' => '',
                'result' => null,
                'metadata' => [
                    'rag_hint' => $ragHint,
                    'query' => $response['query'] ?? null,
                    'fallback' => $response['fallback'] ?? null,
                    'original_text' => $response['original_text'] ?? $text,
                ],
            ];
        }

        $documents = $response['documents'] ?? [];
        $limit = (int) ($response['limit'] ?? 20);
        $total = (int) ($response['total'] ?? count($documents));
        $queryLabel = $response['normalized_query'] ?? $text;
        $originalText = $response['original_text'] ?? $text;
        $fallback = $response['fallback'] ?? null;

        $this->logger->debug('[NaturalLanguageQueryService] Formatting response', [
            'original_text' => $originalText,
            'normalized_query' => $queryLabel,
            'total' => $total,
            'limit' => $limit,
            'document_count' => count($documents),
            'log_category' => 'NATURAL_QUERY',
        ]);
        Log::debug('[NaturalLanguageQueryService] Debug originalText', [
            'original_text' => $originalText,
            'documents_total' => $total,
            'response_original_text' => $response['original_text'] ?? null,
        ]);

        $rows = $this->buildRows($documents);

        $messageKey = $total > 0
            ? 'natan.commands.natural.messages.summary'
            : 'natan.commands.natural.messages.no_results';

        $message = __($messageKey, [
            'query' => $originalText,
            'count' => $total,
            'limit' => $limit,
        ]);

        if (is_array($fallback) && ($fallback['type'] ?? null) === 'expanded_date_range') {
            $message .= ' ' . __('natan.commands.natural.messages.expanded_date_range', [
                'days_before' => (int) ($fallback['days_before'] ?? 0),
                'days_after' => (int) ($fallback['days_after'] ?? 0),
            ]);
            $message = trim($message);
        }

        $result = new CommandResult(
            'natural_query',
            $message,
            $rows,
            [
                'count' => $total,
                'limit' => $limit,
                'rag_hint' => $response['rag_hint'] ?? null,
                'query' => $response['query'] ?? null,
                'normalized_query' => $queryLabel,
                'fallback' => $fallback,
            ]
        );

        return [
            'success' => true,
            'code' => null,
            'message' => $message,
            'result' => $result,
            'metadata' => [
                'rag_hint' => $ragHint,
                'query' => $response['query'] ?? null,
                'fallback' => $fallback,
            ],
        ];
    }

    /**
     * @param array<int,array<string,mixed>> $documents
     * @return array<int,array<string,mixed>>
     */
    private function buildRows(array $documents): array
    {
        $rows = [];

        foreach ($documents as $document) {
            $metadata = [];

            if (!empty($document['protocol_number'])) {
                $metadata[] = [
                    'label' => __('natan.commands.fields.protocol_number'),
                    'value' => (string) $document['protocol_number'],
                ];
            }

            if (!empty($document['protocol_date'])) {
                try {
                    $date = Carbon::parse($document['protocol_date'])->translatedFormat('d/m/Y');
                } catch (\Throwable) {
                    $date = (string) $document['protocol_date'];
                }

                $metadata[] = [
                    'label' => __('natan.commands.fields.protocol_date'),
                    'value' => $date,
                ];
            }

            if (!empty($document['document_type'])) {
                $metadata[] = [
                    'label' => __('natan.commands.fields.document_type'),
                    'value' => (string) $document['document_type'],
                ];
            }

            if (!empty($document['department'])) {
                $metadata[] = [
                    'label' => __('natan.commands.fields.department'),
                    'value' => (string) $document['department'],
                ];
            }

            if (array_key_exists('blockchain_anchored', $document)) {
                $metadata[] = [
                    'label' => __('natan.commands.fields.blockchain_status'),
                    'value' => $document['blockchain_anchored']
                        ? __('natan.commands.values.blockchain_yes')
                        : __('natan.commands.values.blockchain_no'),
                ];
            }

            $rows[] = [
                'title' => $document['title'] ?? __('natan.commands.values.no_title'),
                'description' => $document['description'] ?? null,
                'metadata' => $metadata,
                'link' => [
                    'url' => route('natan.documents.view', [
                        'documentId' => $document['document_id'],
                    ]),
                    'label' => __('natan.commands.links.open_document'),
                ],
            ];
        }

        return $rows;
    }

    /**
     * @param array<string,mixed> $response
     */
    private function resolveErrorMessage(?string $code, array $response): string
    {
        return match ($code) {
            'blacklisted' => __('natan.commands.natural.errors.blacklisted'),
            'throttled' => __('natan.commands.natural.errors.throttled', [
                'minutes' => Arr::get($response, 'retry_after_minutes', 1),
            ]),
            'parse_failed' => __('natan.commands.natural.errors.parse_failed'),
            'no_results' => __('natan.commands.natural.messages.no_results', [
                'query' => Arr::get($response, 'original_text')
                    ?? Arr::get($response, 'normalized_query')
                    ?? __('natan.commands.values.no_title'),
            ]),
            default => __('natan.commands.errors.gateway_unreachable'),
        };
    }
}


