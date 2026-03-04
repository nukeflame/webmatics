<?php

namespace Nukeflame\Webmatics;

use Illuminate\Support\ServiceProvider;

class AnalyzerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Analyzer::class, fn() => new Analyzer());

        $configFile = __DIR__ . '/../config/webmatics.php';
        if (is_file($configFile)) {
            $this->mergeConfigFrom($configFile, 'webmatics');
        }
    }

    public function boot(): void
    {
        $configFile = __DIR__ . '/../config/webmatics.php';
        if (is_file($configFile)) {
            $this->publishes([
                $configFile => config_path('webmatics.php'),
            ], 'webmatics-config');
        }

        $migrationPath = __DIR__ . '/../database/migrations';
        if (is_dir($migrationPath)) {
            $this->loadMigrationsFrom($migrationPath);
        }

        $routesFile = __DIR__ . '/../routes/web.php';
        if (is_file($routesFile)) {
            $this->loadRoutesFrom($routesFile);
        }

        $viewsPath = __DIR__ . '/../resources/views';
        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, 'webmatics');
        }
    }
}
