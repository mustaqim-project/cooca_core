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
        return [
            'googleClientId' => SystemSetting::get('google_client_id') ?? config('services.google.client_id', ''),
            'googleClientSecret' => SystemSetting::get('google_client_secret') ?? config('services.google.client_secret', ''),
            'googleRedirectUri' => SystemSetting::get('google_redirect_uri') ?? config('services.google.redirect', url('/auth/google/callback')),
            'googleCustomerRedirectUri' => SystemSetting::get('google_customer_redirect_uri') ?? config('services.google.customer_redirect', url('/customer/auth/google/callback')),
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
            'metaSocialWebhookUrl'   => url('/api/v1/social-media/meta/webhook'),

            'tiktokClientKey'        => SystemSetting::get('tiktok_client_key', ''),
            'tiktokClientSecret'     => SystemSetting::get('tiktok_client_secret', ''),
            'tiktokRedirectUri'      => route('social-media.tiktok.callback'),
            'tiktokApiUrl'           => SystemSetting::get('tiktok_api_url', 'https://open.tiktokapis.com/v2/'),
            'tiktokAuthUrl'          => SystemSetting::get('tiktok_auth_url', 'https://www.tiktok.com/v2/auth/authorize/'),
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
        ]);

        if (! empty($validated['app_name'])) {
            SystemSetting::set('app_name', $validated['app_name'], 'general');
        }

        if (array_key_exists('google_client_id', $validated)) {
            SystemSetting::set('google_client_id', $validated['google_client_id'] ?? '', 'google_api');
        }

        if (! empty($validated['google_client_secret'])) {
            SystemSetting::set('google_client_secret', $validated['google_client_secret'], 'google_api', true);
        }

        if (array_key_exists('google_redirect_uri', $validated)) {
            SystemSetting::set('google_redirect_uri', $validated['google_redirect_uri'] ?? url('/auth/google/callback'), 'google_api');
        }

        if (array_key_exists('google_customer_redirect_uri', $validated)) {
            SystemSetting::set('google_customer_redirect_uri', $validated['google_customer_redirect_uri'] ?? url('/customer/auth/google/callback'), 'google_api');
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

        return response()->json([
            'success' => true,
            'data'    => $results,
        ]);
    }
}
