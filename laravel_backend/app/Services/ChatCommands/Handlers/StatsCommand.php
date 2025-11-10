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
 * @purpose Restituisce statistiche rapide sugli atti (solo ruoli pa_entity_admin)
 */
final class StatsCommand implements CommandHandlerInterface
{
    private const SUPPORTED = ['stats', 'statistiche', 'diagnostica'];

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

        $limit = $this->resolveLimit(
            $input->getArgument('limite') ?? $input->getArgument('limit'),
            (int) config('natan.commands.stats.default_limit', 10)
        );

        $tenantId = $context->tenant()?->id
            ?? $user->tenant_id
            ?? app('currentTenantId')
            ?? 2;

        $payload = array_filter([
            'tenant_id' => $tenantId,
            'date_from' => $input->getArgument('dal'),
            'date_to' => $input->getArgument('al'),
            'limit' => $limit,
        ], static fn ($value) => $value !== null);

        $response = $this->gateway->fetchStats($payload);

        $rows = collect($response['rows'] ?? [])->map(function (array $item) {
            $typeLabel = $item['document_type'] ?? __('natan.commands.values.unknown_type');

            return [
                'title' => $typeLabel ?: __('natan.commands.values.unknown_type'),
                'metadata' => [[
                    'label' => __('natan.commands.fields.count'),
                    'value' => (string) ($item['count'] ?? 0),
                ]],
            ];
        })->values()->all();

        $totalActs = (int) ($response['total_acts'] ?? 0);

        $message = trans_choice('natan.commands.stats.messages.summary', $totalActs, [
            'count' => $totalActs,
            'from' => $fromDate
                ? $fromDate->translatedFormat('d/m/Y')
                : __('natan.commands.values.no_limit'),
            'to' => $toDate
                ? $toDate->translatedFormat('d/m/Y')
                : __('natan.commands.values.no_limit'),
        ]);

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
                'count' => count($rows),
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


