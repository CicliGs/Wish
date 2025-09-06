<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Notification Queue Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for notification processing.
    | You can customize queue settings, retry logic, and other options.
    |
    */

    'queue' => [
        'name' => env('NOTIFICATION_QUEUE_NAME', 'notifications'),
        'connection' => env('NOTIFICATION_QUEUE_CONNECTION', 'sync'),
        'timeout' => (int) env('NOTIFICATION_QUEUE_TIMEOUT', 30),
        'tries' => (int) env('NOTIFICATION_QUEUE_TRIES', 3),
        'max_exceptions' => (int) env('NOTIFICATION_QUEUE_MAX_EXCEPTIONS', 3),
        'backoff' => [
            'base_delay' => (int) env('NOTIFICATION_QUEUE_BACKOFF_BASE', 60),
            'max_delay' => (int) env('NOTIFICATION_QUEUE_BACKOFF_MAX', 3600),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Cleanup Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for automatic cleanup of old notifications.
    |
    */

    'cleanup' => [
        'enabled' => env('NOTIFICATION_CLEANUP_ENABLED', true),
        'retention_days' => (int) env('NOTIFICATION_RETENTION_DAYS', 30),
        'batch_size' => (int) env('NOTIFICATION_CLEANUP_BATCH_SIZE', 1000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for notification logging and monitoring.
    |
    */

    'logging' => [
        'enabled' => env('NOTIFICATION_LOGGING_ENABLED', true),
        'level' => env('NOTIFICATION_LOG_LEVEL', 'info'),
        'include_trace' => env('NOTIFICATION_LOG_INCLUDE_TRACE', false),
        'context' => [
            'include_user_data' => env('NOTIFICATION_LOG_INCLUDE_USER_DATA', false),
            'include_notification_data' => env('NOTIFICATION_LOG_INCLUDE_NOTIFICATION_DATA', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Settings for rate limiting notifications to prevent spam.
    |
    */

    'rate_limiting' => [
        'enabled' => env('NOTIFICATION_RATE_LIMITING_ENABLED', false),
        'max_per_user_per_hour' => (int) env('NOTIFICATION_MAX_PER_USER_PER_HOUR', 100),
        'max_per_friend_per_hour' => (int) env('NOTIFICATION_MAX_PER_FRIEND_PER_HOUR', 10),
    ],

];
