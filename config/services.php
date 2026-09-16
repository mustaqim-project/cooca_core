<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
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
        'redirect' => env('GOOGLE_REDIRECT_URI', 'https://umkm.cooca.id/auth/google/callback'),
        'customer_redirect' => env('GOOGLE_CUSTOMER_REDIRECT_URI', rtrim(env('APP_URL', 'http://localhost'), '/') . '/customer/auth/google/callback'),
    ],

    'wa_server' => [
        'url' => env('WA_SERVER_URL', 'http://127.0.0.1:3000'),
        'token' => env('WA_WORKER_TOKEN', 'secret-worker-token'),
    ],

    'meta_whatsapp' => [
        'version'         => env('META_WA_API_VERSION', 'v20.0'),
        'token'           => env('META_WA_TOKEN', ''),
        'phone_number_id' => env('META_WA_PHONE_NUMBER_ID', ''),
        'waba_id'         => env('META_WA_WABA_ID', ''),
        'otp_template'    => env('META_WA_OTP_TEMPLATE', 'cooca_otp'),
    ],

];
