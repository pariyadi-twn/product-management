<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Product Stock
    |--------------------------------------------------------------------------
    |
    | Products with a positive stock at or below this value are considered
    | low stock and are highlighted on the catalog and monitoring pages.
    |
    */

    'low_stock_threshold' => (int) env('MONITORING_LOW_STOCK_THRESHOLD', 10),

    /*
    |--------------------------------------------------------------------------
    | Performance Logging
    |--------------------------------------------------------------------------
    |
    | Only requests whose path starts with one of these prefixes are stored
    | in the performance_logs table.
    |
    */

    'log_paths' => [
        'products',
        'activity-logs',
        'monitoring',
        'login',
        'logout',
    ],

    /*
    |--------------------------------------------------------------------------
    | Alert Thresholds
    |--------------------------------------------------------------------------
    */

    'alerts' => [
        'window_minutes' => (int) env('MONITORING_ALERT_WINDOW_MINUTES', 15),
        'error_threshold' => (int) env('MONITORING_ERROR_THRESHOLD', 5),
        'slow_search_ms' => (int) env('MONITORING_SLOW_SEARCH_MS', 500),
        'failed_login_threshold' => (int) env('MONITORING_FAILED_LOGIN_THRESHOLD', 10),
    ],

];
