<?php

namespace App\Providers;

use App\Services\ChatCommands\NaturalLanguageQueryService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\ChatCommands\CommandParser::class);

        $this->app->singleton(\App\Services\ChatCommands\CommandRegistry::class, function ($app) {
            $registry = new \App\Services\ChatCommands\CommandRegistry();

            $registry->register($app->make(\App\Services\ChatCommands\Handlers\AttoCommand::class));
            $registry->register($app->make(\App\Services\ChatCommands\Handlers\AttiCommand::class));
            $registry->register($app->make(\App\Services\ChatCommands\Handlers\StatsCommand::class));

            return $registry;
        });

        $this->app->singleton(\App\Services\ChatCommands\NatanChatCommandService::class, function ($app) {
            return new \App\Services\ChatCommands\NatanChatCommandService(
                $app->make(\App\Services\ChatCommands\CommandParser::class),
                $app->make(\App\Services\ChatCommands\CommandRegistry::class)
            );
        });

        $this->app->singleton(NaturalLanguageQueryService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('natan-natural-query', function (Request $request) {
            $config = config('natan.natural_query.rate_limit', []);
            $maxAttempts = (int) ($config['max_attempts'] ?? 20);
            $decayMinutes = (int) ($config['decay_minutes'] ?? 5);

            $keyParts = [
                $request->user()?->id ?? 'guest',
                $request->ip(),
            ];

            return Limit::perMinutes($decayMinutes, $maxAttempts)
                ->by(implode('|', $keyParts))
                ->response(function () use ($decayMinutes) {
                    return response()->json([
                        'success' => false,
                        'code' => 'throttled',
                        'message' => __('natan.commands.natural.errors.throttled', [
                            'minutes' => $decayMinutes,
                        ]),
                        'retry_after_minutes' => $decayMinutes,
                    ], 429);
                });
        });
    }
}
