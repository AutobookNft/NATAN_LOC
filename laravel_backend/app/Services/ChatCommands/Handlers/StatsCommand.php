<?php

declare(strict_types=1);

namespace App\Services\ChatCommands\Handlers;

use App\Models\PaAct;
use App\Services\ChatCommands\CommandContext;
use App\Services\ChatCommands\CommandInput;
use App\Services\ChatCommands\CommandResult;
use App\Services\ChatCommands\Contracts\CommandHandlerInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * @package App\Services\ChatCommands\Handlers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (FlorenceEGI - Direct Command Queries)
 * @date 2025-11-08
 * @purpose Restituisce statistiche rapide sugli atti (solo ruoli pa_entity_admin)
 */
final class StatsCommand implements CommandHandlerInterface
{
    private const SUPPORTED = ['stats', 'statistiche', 'diagnostica'];

    public function supports(CommandInput $input): bool
    {
        return in_array($input->getName(), self::SUPPORTED, true);
    }

    public function handle(CommandContext $context): CommandResult
    {
        $user = $context->user();

        if (!$user->hasAnyRole(['pa_entity_admin', 'admin', 'superadmin'])) {
            throw new RuntimeException(__('natan.commands.errors.admin_required'));
        }

        $input = $context->input();

        $target = $input->getArgument('target', 'atti');

        if ($target !== 'atti') {
            throw new RuntimeException(__('natan.commands.stats.errors.unsupported_target', [
                'target' => $target,
            ]));
        }

        $fromDate = $this->parseDateArgument($input->getArgument('dal'));
        $toDate = $this->parseDateArgument($input->getArgument('al'));

        $query = PaAct::query();

        if ($fromDate) {
            $query->whereDate('protocol_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('protocol_date', '<=', $toDate);
        }

        $totalActs = $query->count();

        $limit = $this->resolveLimit(
            $input->getArgument('limite') ?? $input->getArgument('limit'),
            (int) config('natan.commands.stats.default_limit', 10)
        );

        $typesQuery = PaAct::query();

        if ($fromDate) {
            $typesQuery->whereDate('protocol_date', '>=', $fromDate);
        }

        if ($toDate) {
            $typesQuery->whereDate('protocol_date', '<=', $toDate);
        }

        $typesQuery
            ->select('document_type', DB::raw('COUNT(*) as total'))
            ->groupBy('document_type')
            ->orderByDesc('total');

        if ($limit !== null) {
            $typesQuery->limit($limit);
        }

        /** @var Collection<int, object> $types */
        $types = $typesQuery->get();

        $message = __('natan.commands.stats.messages.summary', [
            'count' => $totalActs,
            'from' => $fromDate
                ? $fromDate->translatedFormat('d/m/Y')
                : __('natan.commands.values.no_limit'),
            'to' => $toDate
                ? $toDate->translatedFormat('d/m/Y')
                : __('natan.commands.values.no_limit'),
        ]);

        $rows = $types->map(function (object $typeStat) {
            $typeLabel = $typeStat->document_type ?: __('natan.commands.values.unknown_type');

            return [
                'title' => $typeLabel,
                'metadata' => [[
                    'label' => __('natan.commands.fields.count'),
                    'value' => (string) $typeStat->total,
                ]],
            ];
        })->values()->all();

        Log::info('[ChatCommand][Stats] Stats generated', [
            'user_id' => $user->id,
            'total' => $totalActs,
            'from' => $fromDate?->toAtomString(),
            'to' => $toDate?->toAtomString(),
            'limit' => $limit,
        ]);

        return new CommandResult(
            $input->getName(),
            $message,
            $rows,
            [
                'count' => $types->count(),
                'limit' => $limit,
                'total_acts' => $totalActs,
            ]
        );
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
}


