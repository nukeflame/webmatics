<?php

use Illuminate\Support\Facades\Route;
use Nukeflame\Webmatics\Http\Controllers\CoverRequestController;


Route::middleware(config('monit.route_middleware', ['web', 'auth']))
    ->prefix(config('monit.route_prefix', 'monit'))
    ->name('monit.')
    ->group(function () {
        Route::get('/',        [CoverRequestController::class, 'index'])->name('index');
        Route::get('/metrics', [CoverRequestController::class, 'metrics'])->name('metrics');
        Route::get('/logs',    [CoverRequestController::class, 'logs'])->name('logs');
    });
