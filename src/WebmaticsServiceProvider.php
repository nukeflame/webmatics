<?php

namespace Nukeflame\Webmatics;

use Acentria\ServerMonitor\Http\Middleware\TrackCoverRequest;
use Illuminate\Support\ServiceProvider;
use Nukeflame\Webmatics\Console\Commands\PruneRequestLogs;

class WebmaticsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Analyzer::class, fn() => new Analyzer());

        $configFile = __DIR__ . '/../config/monit.php';
        if (is_file($configFile)) {
            $this->mergeConfigFrom($configFile, 'monit');
        }
    }

    public function boot(): void
    {
        $configFile = __DIR__ . '/../config/monit.php';
        if (is_file($configFile)) {
            $this->publishes([
                $configFile => config_path('monit.php'),
            ], 'monit');
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
            $this->loadViewsFrom($viewsPath, 'monit');
        }

        if ($this->app->runningInConsole()) {
            $this->commands([PruneRequestLogs::class]);
        }

        $this->app['router']->aliasMiddleware(
            'track.requests',
            TrackCoverRequest::class
        );
    }
}
