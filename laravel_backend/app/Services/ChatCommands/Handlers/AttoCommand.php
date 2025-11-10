<?php

declare(strict_types=1);

namespace App\Services\ChatCommands\Handlers;

use App\Services\ChatCommands\CommandContext;
use App\Services\ChatCommands\CommandInput;
use App\Services\ChatCommands\CommandResult;
use App\Services\ChatCommands\PythonCommandGateway;
use App\Services\ChatCommands\Contracts\CommandHandlerInterface;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Carbon\Carbon;

/**
 * @package App\Services\ChatCommands\Handlers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (FlorenceEGI - Direct Command Queries)
 * @date 2025-11-08
 * @purpose Restituisce un singolo atto/documento in base al document_id o protocol_number
 */
final class AttoCommand implements CommandHandlerInterface
{
    private const SUPPORTED = ['atto', 'documento'];

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

        $documentId = $this->resolveDocumentIdentifier($input);
        $protocolNumber = $input->getArgument('protocollo');

        if (!$documentId && !$protocolNumber) {
            throw new RuntimeException(__('natan.commands.atto.errors.identifier_required'));
        }

        $tenantId = $context->tenant()?->id
            ?? $user->tenant_id
            ?? app('currentTenantId')
            ?? 2;

        $payload = array_filter([
            'tenant_id' => $tenantId,
            'document_id' => $documentId,
            'protocol_number' => $protocolNumber,
        ], static fn ($value) => $value !== null);

        $response = $this->gateway->fetchAtto($payload);

        if (!($response['success'] ?? false) || empty($response['rows'])) {
            return new CommandResult(
                $input->getName(),
                __('natan.commands.atto.messages.not_found', [
                    'document_id' => $documentId ?? 'N/A',
                    'protocol_number' => $protocolNumber ?? 'N/A',
                ])
            );
        }

        $record = $response['rows'][0];

        $link = route('natan.documents.view', ['documentId' => $record['document_id']]);

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
            $protocolDate = Carbon::parse($record['protocol_date_iso'])->translatedFormat('d/m/Y');

            $metadata[] = [
                'label' => __('natan.commands.fields.protocol_date'),
                'value' => $protocolDate,
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

        $message = __('natan.commands.atto.messages.found', [
            'title' => $record['title'] ?: __('natan.commands.values.no_title'),
        ]);

        $rows = [[
            'title' => $record['title'] ?: __('natan.commands.values.no_title'),
            'description' => $record['description'] ?? null,
            'metadata' => $metadata,
            'link' => [
                'url' => $link,
                'label' => __('natan.commands.links.open_document'),
            ],
        ]];

        Log::info('[ChatCommand][Atto] Document retrieved', [
            'user_id' => $user->id,
            'document_id' => $record['document_id'],
            'tenant_id' => $tenantId,
        ]);

        return new CommandResult(
            $input->getName(),
            $message,
            $rows,
            [
                'count' => 1,
            ]
        );
    }

    private function resolveDocumentIdentifier(CommandInput $input): ?string
    {
        $keys = ['numero', 'documento', 'document_id', 'id'];

        foreach ($keys as $key) {
            $value = $input->getArgument($key);

            if ($value) {
                return trim($value);
            }
        }

        return null;
    }
}


