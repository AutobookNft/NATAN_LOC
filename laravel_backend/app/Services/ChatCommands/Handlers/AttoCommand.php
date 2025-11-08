<?php

declare(strict_types=1);

namespace App\Services\ChatCommands\Handlers;

use App\Models\PaAct;
use App\Services\ChatCommands\CommandContext;
use App\Services\ChatCommands\CommandInput;
use App\Services\ChatCommands\CommandResult;
use App\Services\ChatCommands\Contracts\CommandHandlerInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use RuntimeException;

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

        $query = PaAct::query();

        if ($documentId) {
            $query->where('document_id', $documentId);
        }

        if ($protocolNumber) {
            $query->where('protocol_number', $protocolNumber);
        }

        $document = $query->first();

        if (!$document) {
            return new CommandResult(
                $input->getName(),
                __('natan.commands.atto.messages.not_found', [
                    'document_id' => $documentId ?? 'N/A',
                    'protocol_number' => $protocolNumber ?? 'N/A',
                ])
            );
        }

        $link = route('natan.documents.view', ['documentId' => $document->document_id]);

        $metadata = [
            [
                'label' => __('natan.commands.fields.document_id'),
                'value' => $document->document_id,
            ],
        ];

        if ($document->protocol_number) {
            $metadata[] = [
                'label' => __('natan.commands.fields.protocol_number'),
                'value' => $document->protocol_number,
            ];
        }

        if ($document->protocol_date) {
            $protocolDate = $document->protocol_date instanceof Carbon
                ? $document->protocol_date->translatedFormat('d/m/Y')
                : Carbon::parse($document->protocol_date)->translatedFormat('d/m/Y');

            $metadata[] = [
                'label' => __('natan.commands.fields.protocol_date'),
                'value' => $protocolDate,
            ];
        }

        if ($document->document_type) {
            $metadata[] = [
                'label' => __('natan.commands.fields.document_type'),
                'value' => $document->document_type,
            ];
        }

        if ($document->department) {
            $metadata[] = [
                'label' => __('natan.commands.fields.department'),
                'value' => $document->department,
            ];
        }

        if ($document->blockchain_anchored !== null) {
            $metadata[] = [
                'label' => __('natan.commands.fields.blockchain_status'),
                'value' => $document->blockchain_anchored
                    ? __('natan.commands.values.blockchain_yes')
                    : __('natan.commands.values.blockchain_no'),
            ];
        }

        $message = __('natan.commands.atto.messages.found', [
            'title' => $document->title ?: __('natan.commands.values.no_title'),
        ]);

        $rows = [[
            'title' => $document->title ?: __('natan.commands.values.no_title'),
            'description' => $document->description ?: null,
            'metadata' => $metadata,
            'link' => [
                'url' => $link,
                'label' => __('natan.commands.links.open_document'),
            ],
        ]];

        Log::info('[ChatCommand][Atto] Document retrieved', [
            'user_id' => $user->id,
            'document_id' => $document->document_id,
            'tenant_id' => $context->tenant()?->id,
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
                return $value;
            }
        }

        return null;
    }
}


