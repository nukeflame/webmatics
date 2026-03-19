<?php

use Illuminate\Support\Facades\Route;
use Nukeflame\Webmatics\Http\Controllers\CoverRequestController;


Route::middleware(['monit.auth'])
    ->prefix(config('monit.route_prefix', 'monit'))
    ->name('monit.')
    ->group(function () {
        Route::get('/api',        [CoverRequestController::class, 'index'])->name('index');
        Route::get('/api/metrics', [CoverRequestController::class, 'metrics'])->name('metrics');
        Route::get('/api/logs',    [CoverRequestController::class, 'logs'])->name('logs');
    });

Route::get('/monit/logout', function () {
    return response('Logged out', 401, [
        'WWW-Authenticate' => 'Basic realm="Monit Dashboard"',
        'Cache-Control'    => 'no-store, no-cache, must-revalidate',
    ]);
})->name('monit.logout');
