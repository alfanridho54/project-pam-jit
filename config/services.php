<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'proxmox' => [
        'host' => env('PROXMOX_HOST'),
        'port' => (int) env('PROXMOX_PORT', 8006),
        'node' => env('PROXMOX_NODE'),
        'token_id' => env('PROXMOX_TOKEN_ID'),
        'token_secret' => env('PROXMOX_TOKEN_SECRET'),
        'verify_ssl' => (bool) env('PROXMOX_VERIFY_SSL', true),
    ],

    'temporary_credentials' => [
        'enabled' => env('PAM_JIT_TEMP_CREDENTIALS_ENABLED', true),
        'username_prefix' => env('PAM_JIT_TEMP_CREDENTIALS_USERNAME_PREFIX', 'jit'),
        'cleanup_mode' => env('PAM_JIT_TEMP_CREDENTIALS_CLEANUP_MODE', 'disable'),
        'default_shell' => env('PAM_JIT_TEMP_CREDENTIALS_DEFAULT_SHELL', '/bin/bash'),
        'home_base' => env('PAM_JIT_TEMP_CREDENTIALS_HOME_BASE', '/home'),
        'password_length' => (int) env('PAM_JIT_TEMP_CREDENTIALS_PASSWORD_LENGTH', 24),
    ],

];
