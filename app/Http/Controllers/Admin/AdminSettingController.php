<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

final class AdminSettingController extends Controller
{
    /**
     * Retrieve all unified platform, Google OAuth, and SMTP settings data for views.
     *
     * @return array<string, mixed>
     */
    public static function getUnifiedSettingData(): array
    {
        // Canonical Production Base URL (Defaults strictly to https://cooca.id, never localhost/127.0.0.1 or umkm.cooca.id)
        $rawAppUrl = (string) (SystemSetting::get('app_url') ?: config('app.url', 'https://cooca.id'));
        $appUrl = rtrim($rawAppUrl, '/');
        if (empty($appUrl) || str_contains($appUrl, '127.0.0.1') || str_contains($appUrl, 'localhost') || str_contains($appUrl, 'umkm.cooca.id')) {
            $appUrl = 'https://cooca.id';
        }

        $canonicalUrl = static fn (string $path): string => $appUrl . '/' . ltrim($path, '/');

        $googleRedirectUri = (string) (SystemSetting::get('google_redirect_uri') ?: '');
        if (empty($googleRedirectUri) || str_contains($googleRedirectUri, 'umkm.cooca.id') || str_contains($googleRedirectUri, '127.0.0.1') || str_contains($googleRedirectUri, 'localhost')) {
            $googleRedirectUri = $canonicalUrl('/auth/google/callback');
        }

        $googleCustomerRedirectUri = (string) (SystemSetting::get('google_customer_redirect_uri') ?: '');
        if (empty($googleCustomerRedirectUri) || str_contains($googleCustomerRedirectUri, 'umkm.cooca.id') || str_contains($googleCustomerRedirectUri, '127.0.0.1') || str_contains($googleCustomerRedirectUri, 'localhost')) {
            $googleCustomerRedirectUri = $canonicalUrl('/customer/auth/google/callback');
        }

        return [
            'appUrl' => $appUrl,
            'googleClientId' => SystemSetting::get('google_client_id') ?? config('services.google.client_id', ''),
            'googleClientSecret' => SystemSetting::get('google_client_secret') ?? config('services.google.client_secret', ''),
            'googleRedirectUri' => $googleRedirectUri,
            'googleCustomerRedirectUri' => $googleCustomerRedirectUri,
            'allowGoogleLogin' => SystemSetting::get('allow_google_login', '1'),
            'allowCustomerGoogleLogin' => SystemSetting::get('allow_customer_google_login', '1'),
            'appName' => SystemSetting::get('app_name', config('app.name', 'Universal HPP Calculator')),

            // Subscription Pricing Settings
            'subscriptionPriceMonthly' => SystemSetting::get('subscription_price_monthly', '25000'),
            'subscriptionPriceAnnual' => SystemSetting::get('subscription_price_annual', '250000'),
            'subscriptionAiTokensMonthly' => SystemSetting::get('subscription_ai_tokens_monthly', '10000000'),
            'subscriptionAnnualDiscountBadge' => SystemSetting::get('subscription_annual_discount_badge', 'Hemat 2 Bulan'),
            'aiTokenTopupPrice' => SystemSetting::get('ai_token_topup_price', '50000'),
            'aiTokenTopupAmount' => SystemSetting::get('ai_token_topup_amount', '1000000'),
            'ownerStorageLimitGb' => SystemSetting::get('owner_storage_limit_gb', '3'),
            'storageTopupPrice' => SystemSetting::get('storage_topup_price', '50000'),
            'storageTopupGb' => SystemSetting::get('storage_topup_gb', '1'),

            // SMTP & Email Configuration
            'mailMailer' => SystemSetting::get('mail_mailer') ?? config('mail.default', 'smtp'),
            'mailHost' => SystemSetting::get('mail_host') ?? config('mail.mailers.smtp.host', 'smtp.gmail.com'),
            'mailPort' => SystemSetting::get('mail_port') ?? (string) config('mail.mailers.smtp.port', 587),
            'mailUsername' => SystemSetting::get('mail_username') ?? config('mail.mailers.smtp.username', ''),
            'mailPassword' => '',
            'mailEncryption' => SystemSetting::get('mail_encryption') ?? config('mail.mailers.smtp.encryption', 'tls'),
            'mailFromAddress' => SystemSetting::get('mail_from_address') ?? config('mail.from.address', 'no-reply@cooca.id'),
            'mailFromName' => SystemSetting::get('mail_from_name') ?? config('mail.from.name', 'Cooca Platform'),

            // Social Media Platform Configuration (Meta & TikTok)
            'metaSocialAppId'        => SystemSetting::get('social_media_app_id', ''),
            'metaSocialAppSecret'    => SystemSetting::get('social_media_app_secret', ''),
            'metaSocialWebhookToken' => SystemSetting::get('social_media_webhook_verify_token', 'cooca_meta_social_webhook_token'),
            'metaSocialGraphVersion' => SystemSetting::get('social_media_graph_version', 'v21.0'),
            'metaSocialGraphUrl'     => SystemSetting::get('social_media_graph_url', 'https://graph.facebook.com'),
            'metaSocialWebhookUrl'   => (string) (SystemSetting::get('social_media_webhook_url') ?: $canonicalUrl('/api/v1/social-media/meta/webhook')),

            'tiktokClientKey'        => SystemSetting::get('tiktok_client_key', ''),
            'tiktokClientSecret'     => SystemSetting::get('tiktok_client_secret', ''),
            'tiktokRedirectUri'      => (string) (SystemSetting::get('tiktok_redirect_uri') ?: $canonicalUrl('/social-media/tiktok/callback')),
            'tiktokApiUrl'           => SystemSetting::get('tiktok_api_url', 'https://open.tiktokapis.com/v2/'),
            'tiktokAuthUrl'          => SystemSetting::get('tiktok_auth_url', 'https://www.tiktok.com/v2/auth/authorize/'),

            // Instagram Platform Dedicated Configuration (Cooca-IG)
            'instagramAppId'              => (string) (SystemSetting::get('instagram_app_id') ?: SystemSetting::get('social_media_app_id', '')),
            'instagramAppName'            => (string) SystemSetting::get('instagram_app_name', 'Cooca-IG'),
            'instagramAppSecret'          => (string) (SystemSetting::get('instagram_app_secret') ?: SystemSetting::get('social_media_app_secret', '')),
            'instagramHasAppSecret'       => ! empty(SystemSetting::get('instagram_app_secret') ?: SystemSetting::get('social_media_app_secret', '')),
            'instagramAccountId'          => (string) SystemSetting::get('instagram_account_id', '17841439846162016'),
            'instagramGraphUserId'        => (string) SystemSetting::get('instagram_graph_user_id', '28475871372070145'),
            'instagramUsername'           => (string) SystemSetting::get('instagram_username', 'cooca.indonesia'),
            'instagramAccessToken'        => (string) SystemSetting::get('instagram_access_token', ''),
            'instagramHasAccessToken'     => ! empty(SystemSetting::get('instagram_access_token', '')),
            'instagramAccountType'        => (string) SystemSetting::get('instagram_account_type', 'MEDIA_CREATOR'),
            'instagramMediaCount'         => (string) SystemSetting::get('instagram_media_count', '11'),
            'instagramProfilePicture'     => (string) SystemSetting::get('instagram_profile_picture_url', ''),
            'instagramStatus'             => (string) SystemSetting::get('instagram_status', 'active'),
            'instagramVerifiedAt'         => (string) SystemSetting::get('instagram_verified_at', ''),

            // TriPay Payment Gateway Configuration (Model B - Platform Centralized)
            'tripayMerchantCode'     => SystemSetting::get('tripay_merchant_code') ?? config('services.tripay.merchant_code', ''),
            'tripayApiKey'           => SystemSetting::get('tripay_api_key') ?? config('services.tripay.api_key', ''),
            'tripayPrivateKey'       => SystemSetting::get('tripay_private_key') ?? config('services.tripay.private_key', ''),
            'tripayHasPrivateKey'    => ! empty(SystemSetting::get('tripay_private_key') ?? config('services.tripay.private_key', '')),
            'tripayIsProduction'     => SystemSetting::get('tripay_is_production') !== null ? filter_var(SystemSetting::get('tripay_is_production'), FILTER_VALIDATE_BOOLEAN) : (bool) config('services.tripay.is_production', false),
            'tripaySandboxUrl'       => SystemSetting::get('tripay_sandbox_url') ?? config('services.tripay.sandbox_url', 'https://tripay.co.id/api-sandbox/'),
            'tripayProdUrl'          => SystemSetting::get('tripay_prod_url') ?? config('services.tripay.prod_url', 'https://tripay.co.id/api/'),
            'tripayCallbackUrl'      => (string) (SystemSetting::get('tripay_callback_url') ?: $canonicalUrl('/api/v1/payment/tripay/callback')),

            // Meta WhatsApp Cloud API Configuration (Official Tech Provider)
            'metaWaAppId'            => SystemSetting::get('meta_wa_app_id') ?? config('services.meta_whatsapp.app_id', ''),
            'metaWaAppSecret'        => SystemSetting::get('meta_wa_app_secret') ?? config('services.meta_whatsapp.app_secret', ''),
            'metaWaHasAppSecret'     => ! empty(SystemSetting::get('meta_wa_app_secret') ?? config('services.meta_whatsapp.app_secret', '')),
            'metaWaPhoneNumberId'    => SystemSetting::get('meta_wa_phone_number_id') ?? config('services.meta_whatsapp.phone_number_id', ''),
            'metaWaWabaId'           => SystemSetting::get('meta_wa_waba_id') ?? config('services.meta_whatsapp.waba_id', ''),
            'metaWaToken'            => SystemSetting::get('meta_wa_token') ?? config('services.meta_whatsapp.token', ''),
            'metaWaHasToken'         => ! empty(SystemSetting::get('meta_wa_token') ?? config('services.meta_whatsapp.token', '')),
            'metaWaWebhookVerifyToken' => SystemSetting::get('meta_wa_webhook_verify_token') ?? config('services.meta_whatsapp.webhook_verify_token', 'cooca_meta_wa_webhook_secret'),
            'metaWaConfigId'         => SystemSetting::get('meta_wa_config_id') ?? config('services.meta_whatsapp.config_id', ''),
            'metaWaOtpTemplate'      => SystemSetting::get('meta_wa_otp_template', 'cooca_otp'),
            'waOtpActive'            => filter_var(SystemSetting::get('wa_otp_active', '1'), FILTER_VALIDATE_BOOLEAN),
            'waBlastActive'          => filter_var(SystemSetting::get('wa_blast_active', '1'), FILTER_VALIDATE_BOOLEAN),
            'waBotStatus'            => app(\App\Domain\WhatsApp\AdminWhatsAppService::class)->getStatus(),
            'metaWaGraphVersion'     => SystemSetting::get('meta_wa_graph_version') ?? config('services.meta_whatsapp.version', 'v25.0'),
            'metaWaGraphUrl'         => SystemSetting::get('meta_wa_graph_url') ?? config('services.meta_whatsapp.graph_url', 'https://graph.facebook.com'),
            'metaWaWebhookUrl'       => (string) (SystemSetting::get('meta_wa_webhook_url') ?: $canonicalUrl('/api/v1/wa/meta/webhook')),

            // Biteship Logistics Aggregator Configuration
            'biteshipApiKey'         => SystemSetting::get('biteship_api_key') ?? config('services.biteship.api_key', ''),
            'biteshipHasApiKey'      => ! empty(SystemSetting::get('biteship_api_key') ?? config('services.biteship.api_key', '')),
            'biteshipBaseUrl'        => SystemSetting::get('biteship_base_url') ?? config('services.biteship.base_url', 'https://api.biteship.com'),
            'biteshipEnvironment'    => SystemSetting::get('biteship_environment') ?? config('services.biteship.environment', 'production'),
            'biteshipServiceFee'     => (string) (SystemSetting::get('biteship_service_fee') ?? config('services.biteship.service_fee', '1000')),
            'biteshipWebhookUrl'     => (string) (SystemSetting::get('biteship_webhook_url') ?: $canonicalUrl('/api/v1/shipping/biteship/webhook')),
        ];
    }

    /**
     * Display Google API, System Settings, Social Media & SMTP Unified Hub view.
     */
    public function index(Request $request): View
    {
        $data = static::getUnifiedSettingData();
        $data['defaultTab'] = $request->query('tab', 'system');

        return view('admin.settings.index', $data);
    }

    /**
     * Update Google API, system, social media, and billing settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => ['nullable', 'string', 'max:255'],
            'app_url'  => ['nullable', 'url', 'max:255'],
            'google_client_id' => ['nullable', 'string', 'max:500'],
            'google_client_secret' => ['nullable', 'string', 'max:500'],
            'google_redirect_uri' => ['nullable', 'string', 'max:500'],
            'google_customer_redirect_uri' => ['nullable', 'string', 'max:500'],
            'allow_google_login' => ['nullable', 'boolean'],
            'allow_customer_google_login' => ['nullable', 'boolean'],
            'subscription_price_monthly' => ['nullable', 'numeric', 'min:0'],
            'subscription_price_annual' => ['nullable', 'numeric', 'min:0'],
            'subscription_ai_tokens_monthly' => ['nullable', 'integer', 'min:0'],
            'subscription_annual_discount_badge' => ['nullable', 'string', 'max:64'],
            'ai_token_topup_price' => ['nullable', 'numeric', 'min:0'],
            'ai_token_topup_amount' => ['nullable', 'integer', 'min:1'],
            'owner_storage_limit_gb' => ['nullable', 'integer', 'min:1'],
            'storage_topup_price' => ['nullable', 'numeric', 'min:0'],
            'storage_topup_gb' => ['nullable', 'integer', 'min:1'],

            // Social Media Platform Settings (Meta & TikTok)
            'social_media_app_id'               => ['nullable', 'string', 'max:100'],
            'social_media_app_secret'           => ['nullable', 'string', 'max:150'],
            'social_media_webhook_verify_token' => ['nullable', 'string', 'max:150'],
            'social_media_graph_version'        => ['nullable', 'string', 'max:20'],
            'social_media_graph_url'            => ['nullable', 'url', 'max:200'],

            'tiktok_client_key'                 => ['nullable', 'string', 'max:100'],
            'tiktok_client_secret'              => ['nullable', 'string', 'max:150'],
            'tiktok_api_url'                    => ['nullable', 'url', 'max:200'],
            'tiktok_auth_url'                   => ['nullable', 'url', 'max:200'],

            // TriPay Payment Gateway Settings
            'tripay_merchant_code'              => ['nullable', 'string', 'max:100'],
            'tripay_api_key'                    => ['nullable', 'string', 'max:255'],
            'tripay_private_key'                => ['nullable', 'string', 'max:255'],
            'tripay_is_production'              => ['nullable', 'boolean'],
            'tripay_sandbox_url'                => ['nullable', 'url', 'max:255'],
            'tripay_prod_url'                   => ['nullable', 'url', 'max:255'],

            // Meta WhatsApp Cloud API Settings
            'meta_wa_app_id'                    => ['nullable', 'string', 'max:100'],
            'meta_wa_app_secret'                => ['nullable', 'string', 'max:255'],
            'meta_wa_phone_number_id'           => ['nullable', 'string', 'max:100'],
            'meta_wa_waba_id'                   => ['nullable', 'string', 'max:100'],
            'meta_wa_token'                     => ['nullable', 'string', 'max:1000'],
            'meta_wa_webhook_verify_token'      => ['nullable', 'string', 'max:150'],
            'meta_wa_config_id'                 => ['nullable', 'string', 'max:100'],
            'meta_wa_otp_template'              => ['nullable', 'string', 'max:100'],
            'wa_otp_active'                     => ['nullable', 'boolean'],
            'wa_blast_active'                   => ['nullable', 'boolean'],
            'meta_wa_graph_version'             => ['nullable', 'string', 'max:20'],
            'meta_wa_graph_url'                 => ['nullable', 'url', 'max:255'],

            // Instagram Dedicated Platform Settings
            'instagram_app_id'                  => ['nullable', 'string', 'max:100'],
            'instagram_app_name'                => ['nullable', 'string', 'max:100'],
            'instagram_app_secret'              => ['nullable', 'string', 'max:255'],
            'instagram_account_id'              => ['nullable', 'string', 'max:100'],
            'instagram_username'                => ['nullable', 'string', 'max:100'],
            'instagram_access_token'            => ['nullable', 'string', 'max:1000'],

            // Biteship Logistics Aggregator API Settings
            'biteship_api_key'                  => ['nullable', 'string', 'max:500'],
            'biteship_base_url'                 => ['nullable', 'url', 'max:255'],
            'biteship_environment'              => ['nullable', 'string', 'in:sandbox,production'],
            'biteship_service_fee'              => ['nullable', 'numeric', 'min:0'],
        ]);

        if (! empty($validated['app_name'])) {
            SystemSetting::set('app_name', $validated['app_name'], 'general');
        }

        if (array_key_exists('app_url', $validated) && ! empty($validated['app_url'])) {
            $normalizedAppUrl = rtrim($validated['app_url'], '/');
            if (str_contains($normalizedAppUrl, '127.0.0.1') || str_contains($normalizedAppUrl, 'localhost') || str_contains($normalizedAppUrl, 'umkm.cooca.id')) {
                $normalizedAppUrl = 'https://cooca.id';
            }
            SystemSetting::set('app_url', $normalizedAppUrl, 'general');
        } elseif (! SystemSetting::get('app_url')) {
            SystemSetting::set('app_url', 'https://cooca.id', 'general');
        }

        if (array_key_exists('google_client_id', $validated)) {
            SystemSetting::set('google_client_id', $validated['google_client_id'] ?? '', 'google_api');
        }

        if (! empty($validated['google_client_secret'])) {
            SystemSetting::set('google_client_secret', $validated['google_client_secret'], 'google_api', true);
        }

        if (array_key_exists('google_redirect_uri', $validated)) {
            $redirectUri = trim((string) ($validated['google_redirect_uri'] ?? ''));
            if (empty($redirectUri) || str_contains($redirectUri, '127.0.0.1') || str_contains($redirectUri, 'localhost') || str_contains($redirectUri, 'umkm.cooca.id')) {
                $redirectUri = 'https://cooca.id/auth/google/callback';
            }
            SystemSetting::set('google_redirect_uri', $redirectUri, 'google_api');
        }

        if (array_key_exists('google_customer_redirect_uri', $validated)) {
            $customerRedirectUri = trim((string) ($validated['google_customer_redirect_uri'] ?? ''));
            if (empty($customerRedirectUri) || str_contains($customerRedirectUri, '127.0.0.1') || str_contains($customerRedirectUri, 'localhost') || str_contains($customerRedirectUri, 'umkm.cooca.id')) {
                $customerRedirectUri = 'https://cooca.id/customer/auth/google/callback';
            }
            SystemSetting::set('google_customer_redirect_uri', $customerRedirectUri, 'google_api');
        }

        if ($request->has('allow_google_login') || $request->has('google_client_id')) {
            SystemSetting::set('allow_google_login', $request->has('allow_google_login') ? '1' : '0', 'google_api');
        }
        if ($request->has('allow_customer_google_login') || $request->has('google_client_id')) {
            SystemSetting::set('allow_customer_google_login', $request->has('allow_customer_google_login') ? '1' : '0', 'google_api');
        }

        // Save Social Media Meta Settings
        if (array_key_exists('social_media_app_id', $validated)) {
            SystemSetting::set('social_media_app_id', trim((string) $validated['social_media_app_id']), 'social_media');
        }
        if (! empty($validated['social_media_app_secret'])) {
            SystemSetting::set('social_media_app_secret', trim((string) $validated['social_media_app_secret']), 'social_media', true);
        }
        if (array_key_exists('social_media_webhook_verify_token', $validated)) {
            SystemSetting::set('social_media_webhook_verify_token', trim((string) $validated['social_media_webhook_verify_token']), 'social_media');
        }
        if (array_key_exists('social_media_graph_version', $validated)) {
            SystemSetting::set('social_media_graph_version', trim((string) $validated['social_media_graph_version']), 'social_media');
        }
        if (array_key_exists('social_media_graph_url', $validated)) {
            SystemSetting::set('social_media_graph_url', trim((string) $validated['social_media_graph_url']), 'social_media');
        }

        // Save Social Media TikTok Settings
        if (array_key_exists('tiktok_client_key', $validated)) {
            SystemSetting::set('tiktok_client_key', trim((string) $validated['tiktok_client_key']), 'social_media');
        }
        if (! empty($validated['tiktok_client_secret'])) {
            SystemSetting::set('tiktok_client_secret', trim((string) $validated['tiktok_client_secret']), 'social_media', true);
        }
        if (array_key_exists('tiktok_api_url', $validated)) {
            SystemSetting::set('tiktok_api_url', trim((string) $validated['tiktok_api_url']), 'social_media');
        }
        if (array_key_exists('tiktok_auth_url', $validated)) {
            SystemSetting::set('tiktok_auth_url', trim((string) $validated['tiktok_auth_url']), 'social_media');
        }

        // Save TriPay Gateway Settings
        if (array_key_exists('tripay_merchant_code', $validated)) {
            SystemSetting::set('tripay_merchant_code', trim((string) $validated['tripay_merchant_code']), 'payment');
        }
        if (array_key_exists('tripay_api_key', $validated)) {
            SystemSetting::set('tripay_api_key', trim((string) $validated['tripay_api_key']), 'payment');
        }
        if (! empty($validated['tripay_private_key'])) {
            SystemSetting::set('tripay_private_key', trim((string) $validated['tripay_private_key']), 'payment', true);
        }
        if ($request->has('tripay_is_production')) {
            SystemSetting::set('tripay_is_production', $request->boolean('tripay_is_production') ? '1' : '0', 'payment');
        }
        if (array_key_exists('tripay_sandbox_url', $validated)) {
            SystemSetting::set('tripay_sandbox_url', trim((string) $validated['tripay_sandbox_url']), 'payment');
        }
        if (array_key_exists('tripay_prod_url', $validated)) {
            SystemSetting::set('tripay_prod_url', trim((string) $validated['tripay_prod_url']), 'payment');
        }

        // Save Meta WhatsApp Cloud API Settings
        if (array_key_exists('meta_wa_app_id', $validated)) {
            SystemSetting::set('meta_wa_app_id', trim((string) $validated['meta_wa_app_id']), 'whatsapp');
        }
        if (! empty($validated['meta_wa_app_secret'])) {
            SystemSetting::set('meta_wa_app_secret', trim((string) $validated['meta_wa_app_secret']), 'whatsapp', true);
        }
        if (array_key_exists('meta_wa_phone_number_id', $validated)) {
            SystemSetting::set('meta_wa_phone_number_id', trim((string) $validated['meta_wa_phone_number_id']), 'whatsapp');
        }
        if (array_key_exists('meta_wa_waba_id', $validated)) {
            SystemSetting::set('meta_wa_waba_id', trim((string) $validated['meta_wa_waba_id']), 'whatsapp');
        }
        if (! empty($validated['meta_wa_token'])) {
            SystemSetting::set('meta_wa_token', trim((string) $validated['meta_wa_token']), 'whatsapp', true);
        }
        if (array_key_exists('meta_wa_webhook_verify_token', $validated)) {
            SystemSetting::set('meta_wa_webhook_verify_token', trim((string) $validated['meta_wa_webhook_verify_token']), 'whatsapp');
        }
        if (array_key_exists('meta_wa_config_id', $validated)) {
            SystemSetting::set('meta_wa_config_id', trim((string) $validated['meta_wa_config_id']), 'whatsapp');
        }
        if (array_key_exists('meta_wa_otp_template', $validated)) {
            SystemSetting::set('meta_wa_otp_template', trim((string) $validated['meta_wa_otp_template']), 'whatsapp');
        }
        if ($request->has('wa_otp_active')) {
            SystemSetting::set('wa_otp_active', $request->boolean('wa_otp_active') ? '1' : '0', 'whatsapp');
        }
        if ($request->has('wa_blast_active')) {
            SystemSetting::set('wa_blast_active', $request->boolean('wa_blast_active') ? '1' : '0', 'whatsapp');
        }
        if (array_key_exists('meta_wa_graph_version', $validated)) {
            SystemSetting::set('meta_wa_graph_version', trim((string) $validated['meta_wa_graph_version']), 'whatsapp');
        }
        if (array_key_exists('meta_wa_graph_url', $validated)) {
            SystemSetting::set('meta_wa_graph_url', trim((string) $validated['meta_wa_graph_url']), 'whatsapp');
        }

        // Save Instagram Platform Settings
        if (array_key_exists('instagram_app_id', $validated)) {
            SystemSetting::set('instagram_app_id', trim((string) $validated['instagram_app_id']), 'social_media');
        }
        if (array_key_exists('instagram_app_name', $validated)) {
            SystemSetting::set('instagram_app_name', trim((string) $validated['instagram_app_name']), 'social_media');
        }
        if (! empty($validated['instagram_app_secret'])) {
            SystemSetting::set('instagram_app_secret', trim((string) $validated['instagram_app_secret']), 'social_media', true);
        }
        if (array_key_exists('instagram_account_id', $validated)) {
            SystemSetting::set('instagram_account_id', trim((string) $validated['instagram_account_id']), 'social_media');
        }
        if (array_key_exists('instagram_username', $validated)) {
            SystemSetting::set('instagram_username', trim((string) $validated['instagram_username']), 'social_media');
        }
        if (! empty($validated['instagram_access_token'])) {
            SystemSetting::set('instagram_access_token', trim((string) $validated['instagram_access_token']), 'social_media', true);
        }

        // Save Biteship Logistics Settings
        if (array_key_exists('biteship_api_key', $validated)) {
            SystemSetting::set('biteship_api_key', trim((string) $validated['biteship_api_key']), 'shipping');
        }
        if (array_key_exists('biteship_base_url', $validated)) {
            SystemSetting::set('biteship_base_url', trim((string) $validated['biteship_base_url']), 'shipping');
        }
        if (array_key_exists('biteship_environment', $validated)) {
            SystemSetting::set('biteship_environment', trim((string) $validated['biteship_environment']), 'shipping');
        }
        if (array_key_exists('biteship_service_fee', $validated) && $validated['biteship_service_fee'] !== null) {
            SystemSetting::set('biteship_service_fee', (string) $validated['biteship_service_fee'], 'shipping');
        }

        // Save Subscription Pricing if provided
        if (isset($validated['subscription_price_monthly'])) {
            SystemSetting::set('subscription_price_monthly', (string) $validated['subscription_price_monthly'], 'billing');
        }
        if (isset($validated['subscription_price_annual'])) {
            SystemSetting::set('subscription_price_annual', (string) $validated['subscription_price_annual'], 'billing');
        }
        if (isset($validated['subscription_ai_tokens_monthly'])) {
            SystemSetting::set('subscription_ai_tokens_monthly', (string) $validated['subscription_ai_tokens_monthly'], 'billing');
        }
        if (isset($validated['subscription_annual_discount_badge'])) {
            SystemSetting::set('subscription_annual_discount_badge', $validated['subscription_annual_discount_badge'], 'billing');
        }
        foreach (['ai_token_topup_price', 'ai_token_topup_amount', 'owner_storage_limit_gb', 'storage_topup_price', 'storage_topup_gb'] as $setting) {
            if (isset($validated[$setting])) SystemSetting::set($setting, (string) $validated[$setting], 'billing');
        }

        $activeTab = (string) $request->input('active_tab', 'system');

        return redirect()->route('admin.settings.index', ['tab' => $activeTab])
            ->with('success', 'Konfigurasi platform berhasil disimpan ke database.');
    }

    /**
     * Update billing catalog default pricing (kept under CMS Paket & Harga).
     */
    public function updateBilling(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subscription_price_monthly' => ['nullable', 'numeric', 'min:0'],
            'subscription_price_annual' => ['nullable', 'numeric', 'min:0'],
            'subscription_ai_tokens_monthly' => ['nullable', 'integer', 'min:0'],
            'subscription_annual_discount_badge' => ['nullable', 'string', 'max:64'],
            'ai_token_topup_price' => ['nullable', 'numeric', 'min:0'],
            'ai_token_topup_amount' => ['nullable', 'integer', 'min:1'],
            'owner_storage_limit_gb' => ['nullable', 'integer', 'min:1'],
            'storage_topup_price' => ['nullable', 'numeric', 'min:0'],
            'storage_topup_gb' => ['nullable', 'integer', 'min:1'],
        ]);

        foreach (
            [
                'subscription_price_monthly',
                'subscription_price_annual',
                'subscription_ai_tokens_monthly',
                'subscription_annual_discount_badge',
                'ai_token_topup_price',
                'ai_token_topup_amount',
                'owner_storage_limit_gb',
                'storage_topup_price',
                'storage_topup_gb',
            ] as $setting
        ) {
            if (isset($validated[$setting])) {
                SystemSetting::set($setting, (string) $validated[$setting], 'billing');
            }
        }

        return back()->with('success', 'Harga & kuota default billing Cooca berhasil diperbarui.');
    }

    /**
     * Test validity of Meta and TikTok credentials saved in database.
     */
    public function testSocialMediaConfig(): JsonResponse
    {
        $metaAppId = (string) SystemSetting::get('social_media_app_id', '');
        $metaAppSecret = (string) SystemSetting::get('social_media_app_secret', '');
        $metaVersion = (string) SystemSetting::get('social_media_graph_version', 'v21.0');
        $metaGraphUrl = rtrim((string) SystemSetting::get('social_media_graph_url', 'https://graph.facebook.com'), '/');

        $tiktokKey = (string) SystemSetting::get('tiktok_client_key', '');
        $tiktokSecret = (string) SystemSetting::get('tiktok_client_secret', '');

        $results = [
            'meta' => [
                'configured' => ! empty($metaAppId) && ! empty($metaAppSecret),
                'app_id'     => $metaAppId ?: null,
                'status'     => 'unconfigured',
                'message'    => 'Meta App ID atau Secret belum diisi.',
            ],
            'tiktok' => [
                'configured' => ! empty($tiktokKey) && ! empty($tiktokSecret),
                'client_key' => $tiktokKey ?: null,
                'status'     => 'unconfigured',
                'message'    => 'TikTok Client Key atau Secret belum diisi.',
            ],
        ];

        // Validate Meta Credentials via Graph API
        if ($results['meta']['configured']) {
            try {
                $response = Http::timeout(5)->get("{$metaGraphUrl}/{$metaVersion}/{$metaAppId}", [
                    'access_token' => "{$metaAppId}|{$metaAppSecret}",
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $appName = $data['name'] ?? 'Meta Application';
                    $results['meta']['status'] = 'valid';
                    $results['meta']['message'] = "Kredensial Meta valid! Terhubung ke aplikasi: [{$appName}].";
                } else {
                    $err = $response->json('error.message') ?? 'Autentikasi Meta App ditolak.';
                    $results['meta']['status'] = 'invalid';
                    $results['meta']['message'] = "Validasi Meta gagal: {$err}";
                }
            } catch (\Throwable $e) {
                $results['meta']['status'] = 'error';
                $results['meta']['message'] = 'Koneksi ke Meta Graph API gagal: ' . $e->getMessage();
            }
        }

        // Validate TikTok Credentials format
        if ($results['tiktok']['configured']) {
            if (strlen($tiktokKey) >= 5 && strlen($tiktokSecret) >= 10) {
                $results['tiktok']['status'] = 'valid';
                $results['tiktok']['message'] = 'Format kredensial TikTok Developer valid dan siap digunakan untuk OAuth 2.0.';
            } else {
                $results['tiktok']['status'] = 'invalid';
                $results['tiktok']['message'] = 'Format TikTok Client Key atau Secret tidak sesuai standar TikTok Developer.';
            }
        }

        // Validate Instagram API Credentials if configured
        $igToken = (string) (SystemSetting::get('instagram_access_token') ?: SystemSetting::get('social_media_app_token', ''));
        $results['instagram'] = [
            'configured' => ! empty($igToken),
            'status'     => 'unconfigured',
            'message'    => 'Token Akses Instagram belum dikonfigurasi.',
        ];

        if ($results['instagram']['configured']) {
            /** @var \App\Domain\SocialMedia\AdminSocialMediaService $socialService */
            $socialService = app(\App\Domain\SocialMedia\AdminSocialMediaService::class);
            $igRes = $socialService->verifyInstagramCredentials($igToken);

            if ($igRes['success'] ?? false) {
                $igData = (array) ($igRes['data'] ?? []);
                $igUser = $igData['username'] ?? 'cooca.indonesia';
                $igCount = $igData['media_count'] ?? 0;
                $results['instagram']['status'] = 'valid';
                $results['instagram']['message'] = "Kredensial Instagram valid! Terhubung ke: @{$igUser} ({$igCount} postingan).";
                $results['instagram']['data'] = $igData;
            } else {
                $results['instagram']['status'] = 'invalid';
                $results['instagram']['message'] = 'Validasi Instagram gagal: ' . ($igRes['error'] ?? 'Token tidak valid.');
            }
        }

        return response()->json([
            'success' => true,
            'data'    => $results,
        ]);
    }

    /**
     * Test connection to Instagram API for Business / Creator account.
     */
    public function testInstagramConfig(): JsonResponse
    {
        /** @var \App\Domain\SocialMedia\AdminSocialMediaService $socialService */
        $socialService = app(\App\Domain\SocialMedia\AdminSocialMediaService::class);
        $result = $socialService->verifyInstagramCredentials();

        if ($result['success'] ?? false) {
            $data = (array) ($result['data'] ?? []);
            $username = $data['username'] ?? 'cooca.indonesia';
            $type = $data['account_type'] ?? 'MEDIA_CREATOR';
            $count = $data['media_count'] ?? 0;

            return response()->json([
                'success' => true,
                'message' => "Koneksi Instagram API BERHASIL! Terhubung ke: @{$username} ({$type}) dengan {$count} postingan aktif.",
                'data'    => $data,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Validasi Instagram API gagal: ' . ($result['error'] ?? 'Autentikasi ditolak'),
        ], 422);
    }

    /**
     * Test connection to TriPay Payment Gateway API.
     */
    public function testTripayConfig(): JsonResponse
    {
        $apiKey = (string) (SystemSetting::get('tripay_api_key') ?: config('services.tripay.api_key', ''));
        $settingProd = SystemSetting::get('tripay_is_production');
        $isProd = $settingProd !== null
            ? filter_var($settingProd, FILTER_VALIDATE_BOOLEAN)
            : (bool) config('services.tripay.is_production', false);

        $sandboxUrl = rtrim((string) (SystemSetting::get('tripay_sandbox_url') ?: config('services.tripay.sandbox_url', 'https://tripay.co.id/api-sandbox/')), '/') . '/';
        $prodUrl = rtrim((string) (SystemSetting::get('tripay_prod_url') ?: config('services.tripay.prod_url', 'https://tripay.co.id/api/')), '/') . '/';
        $baseUrl = $isProd ? $prodUrl : $sandboxUrl;
        $modeText = $isProd ? 'Production' : 'Sandbox';

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'API Key TriPay belum dikonfigurasi.',
            ], 422);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
            ])->timeout(8)->get($baseUrl . 'payment/channel');

            if ($response->successful() && ($response->json('success') ?? false)) {
                $data = (array) $response->json('data', []);
                $channelCount = count($data);

                return response()->json([
                    'success' => true,
                    'message' => "Koneksi ke TriPay Gateway ({$modeText}) BERHASIL! Ditemukan {$channelCount} grup kanal pembayaran aktif.",
                    'data' => [
                        'mode' => $modeText,
                        'channels_count' => $channelCount,
                        'base_url' => $baseUrl,
                    ],
                ]);
            }

            $errMsg = $response->json('message') ?? ('HTTP ' . $response->status() . ' - Autentikasi ditolak');

            return response()->json([
                'success' => false,
                'message' => "Validasi TriPay ({$modeText}) gagal: {$errMsg}",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Koneksi ke server TriPay gagal: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Test connection to Meta WhatsApp Cloud API.
     */
    public function testWhatsAppConfig(): JsonResponse
    {
        $token = (string) (SystemSetting::get('meta_wa_token') ?: config('services.meta_whatsapp.token', ''));
        $phoneId = (string) (SystemSetting::get('meta_wa_phone_number_id') ?: config('services.meta_whatsapp.phone_number_id', ''));

        if (empty($token) || empty($phoneId)) {
            return response()->json([
                'success' => false,
                'message' => 'Token Akses Meta dan Phone Number ID belum dikonfigurasi.',
            ], 422);
        }

        /** @var \App\Domain\WhatsApp\Drivers\MetaWhatsAppCloudDriver $metaDriver */
        $metaDriver = app(\App\Domain\WhatsApp\Drivers\MetaWhatsAppCloudDriver::class);
        $result = $metaDriver->verifyCredentials($token, $phoneId);

        if ($result['success'] ?? false) {
            $details = (array) ($result['data'] ?? []);
            $name = $details['verified_name'] ?? 'WhatsApp Business';
            $phone = $details['display_phone_number'] ?? $phoneId;
            $quality = $details['quality_rating'] ?? 'GREEN';

            return response()->json([
                'success' => true,
                'message' => "Koneksi Meta WhatsApp Cloud API VALID! Terhubung ke: {$name} ({$phone}) - Rating Kualitas: {$quality}.",
                'data' => $details,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Validasi Meta WhatsApp gagal: ' . ($result['error'] ?? 'Autentikasi ditolak'),
        ]);
    }

    /**
     * Test connection to Biteship Logistics API.
     */
    public function testBiteshipConfig(): JsonResponse
    {
        /** @var \App\Domain\Shipping\BiteshipService $biteshipService */
        $biteshipService = app(\App\Domain\Shipping\BiteshipService::class);
        $result = $biteshipService->testConnection();

        if ($result['success'] ?? false) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data'    => $result['data'] ?? [],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Validasi Biteship API gagal.',
        ], 422);
    }
}
