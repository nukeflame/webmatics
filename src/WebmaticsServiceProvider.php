<?php

namespace Nukeflame\Webmatics;

use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Support\ServiceProvider;
use Nukeflame\Webmatics\Console\Commands\PruneRequestLogs;
use Nukeflame\Webmatics\Http\Middleware\MonitBasicAuth;
use Nukeflame\Webmatics\Http\Middleware\TrackCoverRequest;

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
        $this->app['router']->aliasMiddleware(
            'monit.auth',
            MonitBasicAuth::class
        );

        $this->app['router']->aliasMiddleware(
            'track.requests',
            TrackCoverRequest::class
        );

        $this->app->make(HttpKernel::class)->pushMiddleware(TrackCoverRequest::class);

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

        $viewsPath = __DIR__ . '/../resources';
        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, 'monit');
        }

        if ($this->app->runningInConsole()) {
            $this->commands([PruneRequestLogs::class]);
        }
    }
}
