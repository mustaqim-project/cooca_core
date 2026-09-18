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
        'redirect' => env('GOOGLE_REDIRECT_URI', 'https://cooca.id/auth/google/callback'),
        'customer_redirect' => env('GOOGLE_CUSTOMER_REDIRECT_URI', rtrim(env('APP_URL', 'http://localhost'), '/') . '/customer/auth/google/callback'),
    ],


    'meta_whatsapp' => [
        'app_id'               => env('META_WA_APP_ID', env('META_APP_ID', '')),
        'app_secret'           => env('META_WA_APP_SECRET', env('META_APP_SECRET', '')),
        'webhook_verify_token' => env('META_WA_WEBHOOK_VERIFY_TOKEN', 'cooca_meta_wa_webhook_secret'),
        'config_id'            => env('META_WA_CONFIG_ID', env('META_WA_EMBEDDED_CONFIG_ID', '')),
        'version'              => env('META_WA_GRAPH_VERSION', env('META_WA_API_VERSION', 'v21.0')),
        'graph_url'            => env('META_WA_GRAPH_URL', 'https://graph.facebook.com'),
        'token'                => env('META_WA_TOKEN', ''),
        'phone_number_id'      => env('META_WA_PHONE_NUMBER_ID', ''),
        'waba_id'              => env('META_WA_WABA_ID', ''),
        'otp_template'         => env('META_WA_OTP_TEMPLATE', 'cooca_otp'),
    ],

    'meta_social' => [
        'app_id'               => env('META_SOCIAL_APP_ID', ''),
        'app_secret'           => env('META_SOCIAL_APP_SECRET', ''),
        'webhook_verify_token' => env('META_SOCIAL_WEBHOOK_VERIFY_TOKEN', 'cooca_meta_social_webhook_token'),
        'graph_version'        => env('META_SOCIAL_GRAPH_VERSION', 'v21.0'),
        'graph_url'            => env('META_SOCIAL_GRAPH_URL', 'https://graph.facebook.com'),
    ],

    'tiktok' => [
        'client_key'    => env('TIKTOK_CLIENT_KEY', ''),
        'client_secret' => env('TIKTOK_CLIENT_SECRET', ''),
        'redirect_uri'  => env('TIKTOK_REDIRECT_URI', 'https://cooca.id/social-media/tiktok/callback'),
        'api_url'       => env('TIKTOK_API_URL', 'https://open.tiktokapis.com/v2/'),
        'auth_url'      => env('TIKTOK_AUTH_URL', 'https://www.tiktok.com/v2/auth/authorize/'),
    ],

    'tripay' => [
        'merchant_code' => env('TRIPAY_MERCHANT_CODE', ''),
        'api_key'       => env('TRIPAY_API_KEY', ''),
        'private_key'   => env('TRIPAY_PRIVATE_KEY', ''),
        'is_production' => (bool) env('TRIPAY_IS_PRODUCTION', false),
        'sandbox_url'   => env('TRIPAY_SANDBOX_URL', 'https://tripay.co.id/api-sandbox/'),
        'prod_url'      => env('TRIPAY_PROD_URL', 'https://tripay.co.id/api/'),
    ],

    'biteship' => [
        'api_key'     => env('BITESHIP_API_KEY', 'biteship_live.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJuYW1lIjoiQ29vY2EgU2hpcHBpbmciLCJ1c2VySWQiOiI2YTcyY2VkN2UzMmQxOTRlNmRmODFjZGIiLCJpYXQiOjE3ODk3MTIzOTB9.x-gMrVA4kpFdQWa-fKeDe7b1TO5c-Y5b0ZnTj3RC1vU'),
        'base_url'    => env('BITESHIP_BASE_URL', 'https://api.biteship.com'),
        'environment' => env('BITESHIP_ENVIRONMENT', 'production'),
        'service_fee' => (float) env('BITESHIP_SERVICE_FEE', 1000.0),
    ],

];

