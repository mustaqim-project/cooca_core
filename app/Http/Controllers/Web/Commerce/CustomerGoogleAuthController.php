<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Commerce;

use App\Http\Controllers\Controller;
use App\Models\GlobalCustomer;
use App\Models\SystemSetting;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

/**
 * Google OAuth for the customer guard.
 * Uses a dedicated redirect URI: /customer/auth/google/callback
 */
final class CustomerGoogleAuthController extends Controller
{
    private function configureGoogle(): bool
    {
        if (SystemSetting::get('allow_customer_google_login', '1') !== '1') {
            return false;
        }

        $clientId     = SystemSetting::get('google_client_id')     ?: config('services.google.client_id');
        $clientSecret = SystemSetting::get('google_client_secret') ?: config('services.google.client_secret');
        $redirectUri  = SystemSetting::get('google_customer_redirect_uri')
            ?: config('services.google.customer_redirect', url('/customer/auth/google/callback'));

        if (empty($clientId) || empty($clientSecret)) {
            return false;
        }

        config([
            'services.google.client_id'     => $clientId,
            'services.google.client_secret' => $clientSecret,
            'services.google.redirect'      => $redirectUri,
        ]);

        return true;
    }

    /**
     * Redirect to Google consent screen.
     */
    public function redirect(): RedirectResponse
    {
        if (SystemSetting::get('allow_customer_google_login', '1') !== '1') {
            return redirect()->route('customer.login')
                ->withErrors(['google' => 'Login Google untuk pelanggan sedang dinonaktifkan oleh administrator.']);
        }

        if (request()->has('redirect')) {
            $redirectUrl = (string) request()->query('redirect');
            if (str_starts_with($redirectUrl, '/') || filter_var($redirectUrl, FILTER_VALIDATE_URL)) {
                session()->put('url.intended', $redirectUrl);
            }
        }

        if (! $this->configureGoogle()) {
            return redirect()->route('customer.login')
                ->withErrors(['google' => 'Google Login belum dikonfigurasi. Hubungi administrator.']);
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google callback -- find or create GlobalCustomer, then login.
     */
    public function callback(): RedirectResponse
    {
        if (SystemSetting::get('allow_customer_google_login', '1') !== '1') {
            return redirect()->route('customer.login')
                ->withErrors(['google' => 'Login Google untuk pelanggan sedang dinonaktifkan oleh administrator.']);
        }

        if (! $this->configureGoogle()) {
            return redirect()->route('customer.login')
                ->withErrors(['google' => 'Konfigurasi Google tidak valid atau dinonaktifkan.']);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Exception $e) {
            return redirect()->route('customer.login')
                ->withErrors(['google' => 'Gagal autentikasi Google: ' . $e->getMessage()]);
        }

        // Find by google_id first, then fall back to email
        $customer = GlobalCustomer::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($customer) {
            // Update google_id + avatar in case this is an email-first match
            $customer->update([
                'google_id'  => $googleUser->getId(),
                'avatar_url' => $googleUser->getAvatar(),
                // Mark email as verified since Google confirmed it
                'email_verified_at' => $customer->email_verified_at ?? now(),
            ]);
        } else {
            // First time -- create global identity (phone filled later in profile completion)
            $customer = GlobalCustomer::create([
                'google_id'         => $googleUser->getId(),
                'name'              => $googleUser->getName() ?? 'Pelanggan',
                'email'             => $googleUser->getEmail(),
                'avatar_url'        => $googleUser->getAvatar(),
                'email_verified_at' => now(),
            ]);
        }

        Auth::guard('customer')->login($customer, remember: true);
        request()->session()->regenerate();

        // Upgrade any guest CRM Customer with same phone (if phone already set)
        if ($customer->phone) {
            \App\Models\Customer::where('phone', $customer->phone)
                ->whereNull('password')
                ->update([
                    'name'      => $customer->name,
                    'email'     => $customer->email,
                    'is_active' => true,
                ]);
        }

        $intended = session()->pull('url.intended', route('customer.dashboard'));

        return redirect($intended)->with('success', 'Selamat datang, ' . $customer->name . '!');
    }
}