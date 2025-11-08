<?php

declare(strict_types=1);

namespace App\Services\ChatCommands\Handlers;

use App\Models\PaAct;
use App\Services\ChatCommands\CommandContext;
use App\Services\ChatCommands\CommandInput;
use App\Services\ChatCommands\CommandResult;
use App\Services\ChatCommands\Contracts\CommandHandlerInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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
        $query = PaAct::query();

        $filtersApplied = false;

        $documentIds = $input->getArgumentList('numero');
        if (!empty($documentIds)) {
            $query->whereIn('document_id', $documentIds);
            $filtersApplied = true;
        }

        $protocolNumbers = $input->getArgumentList('protocollo');
        if (!empty($protocolNumbers)) {
            $query->whereIn('protocol_number', $protocolNumbers);
            $filtersApplied = true;
        }

        $types = $input->getArgumentList('tipo');
        if (!empty($types)) {
            $query->where(function (Builder $builder) use ($types): void {
                foreach ($types as $type) {
                    $builder->orWhere('document_type', 'like', '%' . $type . '%');
                }
            });
            $filtersApplied = true;
        }

        $departments = $input->getArgumentList('dipartimento');
        if (!empty($departments)) {
            $query->where(function (Builder $builder) use ($departments): void {
                foreach ($departments as $department) {
                    $builder->orWhere('department', 'like', '%' . $department . '%');
                }
            });
            $filtersApplied = true;
        }

        $responsibles = $input->getArgumentList('responsabile');
        if (!empty($responsibles)) {
            $query->where(function (Builder $builder) use ($responsibles): void {
                foreach ($responsibles as $responsible) {
                    $builder->orWhere('responsible', 'like', '%' . $responsible . '%');
                }
            });
            $filtersApplied = true;
        }

        $text = $input->getArgument('testo') ?? $input->getArgument('titolo');
        if ($text) {
            $query->where(function (Builder $builder) use ($text): void {
                $builder
                    ->where('title', 'like', '%' . $text . '%')
                    ->orWhere('description', 'like', '%' . $text . '%');
            });
            $filtersApplied = true;
        }

        $fromDate = $this->parseDateArgument($input->getArgument('dal'));
        if ($fromDate) {
            $query->whereDate('protocol_date', '>=', $fromDate);
            $filtersApplied = true;
        }

        $toDate = $this->parseDateArgument($input->getArgument('al'));
        if ($toDate) {
            $query->whereDate('protocol_date', '<=', $toDate);
            $filtersApplied = true;
        }

        if (!$filtersApplied) {
            Log::warning('[ChatCommand][Atti] No filters provided, falling back to recent items', [
                'user_id' => $user->id,
            ]);
        }

        $limit = $this->resolveLimit(
            $input->getArgument('limite') ?? $input->getArgument('limit'),
            (int) config('natan.commands.default_limit', 10)
        );

        if ($limit !== null) {
            $query->limit($limit);
        }

        $acts = $query
            ->orderByDesc('protocol_date')
            ->orderByDesc('created_at')
            ->get();

        if ($acts->isEmpty()) {
            return new CommandResult(
                $input->getName(),
                __('natan.commands.atti.messages.empty')
            );
        }

        $rows = $acts->map(function (PaAct $act) {
            $metadata = [
                [
                    'label' => __('natan.commands.fields.document_id'),
                    'value' => $act->document_id,
                ],
            ];

            if ($act->protocol_number) {
                $metadata[] = [
                    'label' => __('natan.commands.fields.protocol_number'),
                    'value' => $act->protocol_number,
                ];
            }

            if ($act->protocol_date) {
                $metadata[] = [
                    'label' => __('natan.commands.fields.protocol_date'),
                    'value' => $act->protocol_date instanceof Carbon
                        ? $act->protocol_date->translatedFormat('d/m/Y')
                        : Carbon::parse($act->protocol_date)->translatedFormat('d/m/Y'),
                ];
            }

            if ($act->document_type) {
                $metadata[] = [
                    'label' => __('natan.commands.fields.document_type'),
                    'value' => $act->document_type,
                ];
            }

            if ($act->department) {
                $metadata[] = [
                    'label' => __('natan.commands.fields.department'),
                    'value' => $act->department,
                ];
            }

            return [
                'title' => $act->title ?: __('natan.commands.values.no_title'),
                'description' => $act->description ?: null,
                'metadata' => $metadata,
                'link' => [
                    'url' => route('natan.documents.view', ['documentId' => $act->document_id]),
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
            'filters_applied' => $filtersApplied,
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
                'filters_applied' => $filtersApplied,
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
}


