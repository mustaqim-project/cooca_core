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

        return view('admin.settings.index', compact(
            'googleClientId',
            'googleClientSecret',
            'googleRedirectUri',
            'allowGoogleLogin',
            'appName',
            'subscriptionPriceMonthly',
            'subscriptionPriceAnnual',
            'subscriptionAiTokensMonthly',
            'subscriptionAnnualDiscountBadge'
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
            'subscription_price_monthly' => ['required', 'numeric', 'min:0'],
            'subscription_price_annual' => ['required', 'numeric', 'min:0'],
            'subscription_ai_tokens_monthly' => ['required', 'integer', 'min:0'],
            'subscription_annual_discount_badge' => ['nullable', 'string', 'max:64'],
        ]);

        SystemSetting::set('app_name', $validated['app_name'], 'general');
        SystemSetting::set('google_client_id', $validated['google_client_id'] ?? '', 'google_api');

        if (! empty($validated['google_client_secret'])) {
            SystemSetting::set('google_client_secret', $validated['google_client_secret'], 'google_api', true);
        }

        SystemSetting::set('google_redirect_uri', $validated['google_redirect_uri'] ?? url('/auth/google/callback'), 'google_api');
        SystemSetting::set('allow_google_login', $request->has('allow_google_login') ? '1' : '0', 'google_api');

        // Save Subscription Pricing
        SystemSetting::set('subscription_price_monthly', (string) $validated['subscription_price_monthly'], 'billing');
        SystemSetting::set('subscription_price_annual', (string) $validated['subscription_price_annual'], 'billing');
        SystemSetting::set('subscription_ai_tokens_monthly', (string) $validated['subscription_ai_tokens_monthly'], 'billing');
        SystemSetting::set('subscription_annual_discount_badge', $validated['subscription_annual_discount_badge'] ?? 'Hemat 2 Bulan', 'billing');

        return redirect()->route('admin.settings.index')->with('success', 'Konfigurasi Google API, Harga Langganan, dan Sistem berhasil disimpan.');
    }
}
