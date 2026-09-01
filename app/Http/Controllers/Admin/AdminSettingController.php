<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminSettingController extends Controller
{
    /**
     * Display Google API & System Settings view.
     */
    public function index(): View
    {
        $googleClientId = SystemSetting::get('google_client_id') ?? config('services.google.client_id', '');
        $googleClientSecret = SystemSetting::get('google_client_secret') ?? config('services.google.client_secret', '');
        $googleRedirectUri = SystemSetting::get('google_redirect_uri') ?? config('services.google.redirect', url('/auth/google/callback'));
        $allowGoogleLogin = SystemSetting::get('allow_google_login', '1');
        $appName = SystemSetting::get('app_name', config('app.name', 'Universal HPP Calculator'));

        // Subscription Pricing Settings
        $subscriptionPriceMonthly = SystemSetting::get('subscription_price_monthly', '129000');
        $subscriptionPriceAnnual = SystemSetting::get('subscription_price_annual', '1290000');
        $subscriptionAiTokensMonthly = SystemSetting::get('subscription_ai_tokens_monthly', '10000000');
        $subscriptionAnnualDiscountBadge = SystemSetting::get('subscription_annual_discount_badge', 'Hemat 2 Bulan');
        $aiTokenTopupPrice = SystemSetting::get('ai_token_topup_price', '50000');
        $aiTokenTopupAmount = SystemSetting::get('ai_token_topup_amount', '1000000');
        $ownerStorageLimitGb = SystemSetting::get('owner_storage_limit_gb', '3');
        $storageTopupPrice = SystemSetting::get('storage_topup_price', '50000');
        $storageTopupGb = SystemSetting::get('storage_topup_gb', '1');

        return view('admin.settings.index', compact(
            'googleClientId',
            'googleClientSecret',
            'googleRedirectUri',
            'allowGoogleLogin',
            'appName',
            'subscriptionPriceMonthly',
            'subscriptionPriceAnnual',
            'subscriptionAiTokensMonthly',
            'subscriptionAnnualDiscountBadge', 'aiTokenTopupPrice', 'aiTokenTopupAmount',
            'ownerStorageLimitGb', 'storageTopupPrice', 'storageTopupGb'
        ));
    }

    /**
     * Update Google API and system settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'google_client_id' => ['nullable', 'string', 'max:500'],
            'google_client_secret' => ['nullable', 'string', 'max:500'],
            'google_redirect_uri' => ['nullable', 'string', 'max:500'],
            'allow_google_login' => ['nullable', 'boolean'],
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

        SystemSetting::set('app_name', $validated['app_name'], 'general');
        SystemSetting::set('google_client_id', $validated['google_client_id'] ?? '', 'google_api');

        if (! empty($validated['google_client_secret'])) {
            SystemSetting::set('google_client_secret', $validated['google_client_secret'], 'google_api', true);
        }

        SystemSetting::set('google_redirect_uri', $validated['google_redirect_uri'] ?? url('/auth/google/callback'), 'google_api');
        SystemSetting::set('allow_google_login', $request->has('allow_google_login') ? '1' : '0', 'google_api');

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

        return redirect()->route('admin.settings.index')->with('success', 'Konfigurasi Google API, Harga Langganan, dan Sistem berhasil disimpan.');
    }
}
