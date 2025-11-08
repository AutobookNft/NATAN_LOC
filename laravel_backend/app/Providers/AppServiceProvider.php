<?php

namespace App\Providers;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
