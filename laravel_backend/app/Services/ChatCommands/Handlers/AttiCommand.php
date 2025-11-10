<?php

declare(strict_types=1);

namespace App\Services\ChatCommands\Handlers;

use App\Services\ChatCommands\CommandContext;
use App\Services\ChatCommands\CommandInput;
use App\Services\ChatCommands\CommandResult;
use App\Services\ChatCommands\PythonCommandGateway;
use App\Services\ChatCommands\Contracts\CommandHandlerInterface;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * @package App\Services\ChatCommands\Handlers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (FlorenceEGI - Direct Command Queries)
 * @date 2025-11-08
 * @purpose Ricerca atti/documenti con argomenti compositi e restituisce elenco risultati
 */
final class AttiCommand implements CommandHandlerInterface
{
    private const SUPPORTED = ['atti', 'acts', 'documents'];

    public function __construct(
        private PythonCommandGateway $gateway,
    ) {
    }

    public function supports(CommandInput $input): bool
    {
        return in_array($input->getName(), self::SUPPORTED, true);
    }

    public function handle(CommandContext $context): CommandResult
    {
        $user = $context->user();

        if (!$user->hasAnyRole(['pa_entity', 'pa_entity_admin', 'admin', 'superadmin'])) {
            throw new RuntimeException(__('natan.commands.errors.permission_denied'));
        }

        $input = $context->input();

        $limit = $this->resolveLimit(
            $input->getArgument('limite') ?? $input->getArgument('limit'),
            (int) config('natan.commands.default_limit', 10)
        );

        $tenantId = $context->tenant()?->id
            ?? $user->tenant_id
            ?? app('currentTenantId')
            ?? 2;

        $payload = array_filter([
            'tenant_id' => $tenantId,
            'document_ids' => $this->normalizeList($input->getArgumentList('numero')),
            'protocol_numbers' => $this->normalizeList($input->getArgumentList('protocollo')),
            'types' => $this->normalizeList($input->getArgumentList('tipo')),
            'departments' => $this->normalizeList($input->getArgumentList('dipartimento')),
            'responsibles' => $this->normalizeList($input->getArgumentList('responsabile')),
            'text' => $input->getArgument('testo') ?? $input->getArgument('titolo'),
            'date_from' => $input->getArgument('dal'),
            'date_to' => $input->getArgument('al'),
            'limit' => $limit,
        ], static fn ($value) => $value !== null && $value !== []);

        $response = $this->gateway->fetchAtti($payload);

        if (!($response['success'] ?? false) || empty($response['rows'])) {
            return new CommandResult(
                $input->getName(),
                __('natan.commands.atti.messages.empty')
            );
        }

        $rows = collect($response['rows'])->map(function (array $record) {
            $metadata = [
                [
                    'label' => __('natan.commands.fields.document_id'),
                    'value' => $record['document_id'],
                ],
            ];

            if (!empty($record['protocol_number'])) {
                $metadata[] = [
                    'label' => __('natan.commands.fields.protocol_number'),
                    'value' => $record['protocol_number'],
                ];
            }

            if (!empty($record['protocol_date_iso'])) {
                $metadata[] = [
                    'label' => __('natan.commands.fields.protocol_date'),
                    'value' => Carbon::parse($record['protocol_date_iso'])->translatedFormat('d/m/Y'),
                ];
            }

            if (!empty($record['document_type'])) {
                $metadata[] = [
                    'label' => __('natan.commands.fields.document_type'),
                    'value' => $record['document_type'],
                ];
            }

            if (!empty($record['department'])) {
                $metadata[] = [
                    'label' => __('natan.commands.fields.department'),
                    'value' => $record['department'],
                ];
            }

            return [
                'title' => $record['title'] ?: __('natan.commands.values.no_title'),
                'description' => $record['description'] ?? null,
                'metadata' => $metadata,
                'link' => [
                    'url' => route('natan.documents.view', ['documentId' => $record['document_id']]),
                    'label' => __('natan.commands.links.open_document'),
                ],
            ];
        })->values()->all();

        $message = __('natan.commands.atti.messages.found', [
            'count' => count($rows),
            'limit' => $limit ?? __('natan.commands.values.no_limit'),
        ]);

        Log::info('[ChatCommand][Atti] Acts retrieved', [
            'user_id' => $user->id,
            'filters_applied' => Arr::except($payload, ['tenant_id', 'limit']),
            'result_count' => count($rows),
            'limit' => $limit,
        ]);

        return new CommandResult(
            $input->getName(),
            $message,
            $rows,
            [
                'count' => count($rows),
                'limit' => $limit,
                'filters_applied' => !empty($payload),
            ]
        );
    }

    private function resolveLimit(?string $limitArgument, ?int $default): ?int
    {
        if ($limitArgument === null) {
            return $default;
        }

        if (!is_numeric($limitArgument)) {
            return $default;
        }

        $limit = (int) $limitArgument;

        $maxLimit = (int) config('natan.commands.max_limit', 50);

        if ($limit <= 0) {
            return null;
        }

        return min($limit, $maxLimit);
    }

    private function parseDateArgument(?string $value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param array<int, string> $values
     * @return array<int, string>|null
     */
    private function normalizeList(array $values): ?array
    {
        $normalized = array_values(array_filter(
            array_map(static fn (string $value): string => trim($value), $values),
            static fn (string $value): bool => $value !== ''
        ));

        return $normalized === [] ? null : $normalized;
    }
}


