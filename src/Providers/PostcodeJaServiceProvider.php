<?php

namespace BstCo\PostcodeJa\Providers;

use BstCo\PostcodeJa\Console\Commands;
use Illuminate\Support\ServiceProvider;

class PostcodeJaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerCommands();

        if (!app()->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__ . '/../../config/postcode.php', 'postcode');
        }
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    /**
     * Register the console commands for the package.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\CreateCommand::class,
            ]);
        }
    }
}
