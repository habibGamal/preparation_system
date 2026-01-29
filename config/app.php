<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default User Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration defines the default user credentials that will be
    | used in local development environments. It is particularly useful
    | for quickly logging into the application without needing
    | to create a user manually.
    |
    */

    'default_user' => [
        'name' => env('DEFAULT_USER_NAME', 'Admin'),
        'email' => env('DEFAULT_USER_EMAIL', 'admin@example.com'),
        'password' => env('DEFAULT_USER_PASSWORD', 'password'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Instance Management
    |--------------------------------------------------------------------------
    |
    | These values are used for managing multiple application instances
    | through the management operations system.
    |
    */

    'id' => env('APP_ID', 'larament_default'),
    'external_url' => env('APP_EXTERNAL_URL', env('APP_URL', 'http://localhost')),
    'manage_operations_url' => env('MANAGE_OPERATIONS_URL', 'http://localhost:8009'),
    'management_secret_key' => env('MANAGEMENT_SECRET_KEY', 'default-secret-key'),
];
