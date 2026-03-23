<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Server Identity
    |--------------------------------------------------------------------------
    | The name used to identify this node in the dashboard. Defaults to the
    | system hostname. Override via APP_SERVER_ID in .env for clarity.
    */
    'server_id' => env('APP_SERVER_ID', gethostname()),

    /*
    |--------------------------------------------------------------------------
    | Dashboard Route Prefix & Middleware
    |--------------------------------------------------------------------------
    */
    'route_prefix'     => env('SERVER_MONITOR_PREFIX', 'monit'),
    'route_middleware'  => ['monit.auth'],

    /*
    |--------------------------------------------------------------------------
    | Auto-Tracking
    |--------------------------------------------------------------------------
    | When true the middleware is automatically appended to the global
    | middleware stack. Set to false to apply it selectively via the
    | 'track.requests' alias on specific route groups.
    */
    'auto_track' => env('SERVER_MONITOR_AUTO_TRACK', true),

    /*
    |--------------------------------------------------------------------------
    | Exclusions
    |--------------------------------------------------------------------------
    | URI patterns (fnmatch-style) and status codes to skip recording.
    */
    'exclude_paths' => [
        '_debugbar*',
        'horizon*',
        'telescope*',
        'livewire/update',
        'monit/*',
        'monit',
    ],

    'exclude_status_codes' => [],

    /*
    |--------------------------------------------------------------------------
    | Retention
    |--------------------------------------------------------------------------
    | Number of days to keep request records. The prune:request-logs command
    | deletes anything older than this. Add it to your scheduler:
    |   $schedule->command('monit:prune')->daily();
    */
    'retention_days' => env('SERVER_MONITOR_RETENTION_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Response Time Thresholds (ms)
    |--------------------------------------------------------------------------
    */
    'thresholds' => [
        'fast' => 100,
        'warn' => 300,
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard — Requests Per Page
    |--------------------------------------------------------------------------
    */
    'per_page' => 50,

    'basic_auth' => [
        'username' => 'nukeflame',
        'password' => 'aK9xP',
    ],

];
