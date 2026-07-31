<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Master Switch
    |--------------------------------------------------------------------------
    */
    'enabled' => env('GUPA_PANEL_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Sync Interval (in minutes)
    |--------------------------------------------------------------------------
    | How often the scheduler dispatches sync jobs to tenants.
    | Default: 1 minute.
    */
    'sync_interval' => env('GUPA_PANEL_SYNC_INTERVAL', 1),

    /*
    |--------------------------------------------------------------------------
    | Sync Toggles
    |--------------------------------------------------------------------------
    */
    'sync_blocked_ips' => env('GUPA_PANEL_SYNC_BLOCKED_IPS', true),
    'sync_blacklists' => env('GUPA_PANEL_SYNC_BLACKLISTS', true),
    'sync_whitelists' => env('GUPA_PANEL_SYNC_WHITELISTS', true),

    /*
    |--------------------------------------------------------------------------
    | Log Sync Batch Size
    |--------------------------------------------------------------------------
    | How many unsynced logs are pulled from a tenant per run.
    */
    'log_sync_batch' => env('GUPA_PANEL_LOG_SYNC_BATCH', 1000),

    /*
    |--------------------------------------------------------------------------
    | False Positive Detection
    |--------------------------------------------------------------------------
    */
    'false_positive' => [
        'enabled' => env('GUPA_PANEL_FP_ENABLED', true),
        'threshold' => env('GUPA_PANEL_FP_THRESHOLD', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    */
    'route_prefix' => env('GUPA_PANEL_ROUTE_PREFIX', 'gupa-panel'),

];
