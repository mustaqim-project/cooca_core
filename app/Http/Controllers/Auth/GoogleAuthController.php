<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Domain\WhatsApp\AdminWhatsAppService;
use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

final class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google OAuth provider.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        $clientId = SystemSetting::get('google_client_id') ?: config('services.google.client_id');
        $clientSecret = SystemSetting::get('google_client_secret') ?: config('services.google.client_secret');
        $redirectUrl = SystemSetting::get('google_redirect_uri') ?: config('services.google.redirect');

        if (empty($clientId) || empty($clientSecret)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google Login belum dikonfigurasi oleh Administrator. Silakan gunakan login email/password.',
            ]);
        }

        // Dynamically set Socialite config from database/settings
        config([
            'services.google.client_id' => $clientId,
            'services.google.client_secret' => $clientSecret,
            'services.google.redirect' => $redirectUrl,
        ]);

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle callback from Google.
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        $clientId = SystemSetting::get('google_client_id') ?: config('services.google.client_id');
        $clientSecret = SystemSetting::get('google_client_secret') ?: config('services.google.client_secret');
        $redirectUrl = SystemSetting::get('google_redirect_uri') ?: config('services.google.redirect');

        if (! empty($clientId) && ! empty($clientSecret)) {
            config([
                'services.google.client_id' => $clientId,
                'services.google.client_secret' => $clientSecret,
                'services.google.redirect' => $redirectUrl,
            ]);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Exception $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Gagal melakukan otentikasi dengan Google: '.$e->getMessage(),
            ]);
        }

        // Existing Google users can continue signing in without a new OTP.
        $existingUser = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($existingUser) {
            $existingUser->update([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
            ]);

            return $this->loginUser($existingUser);
        }

        request()->session()->put('pending_google_registration', [
            'name' => $googleUser->getName() ?? 'Pengguna Google',
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
        ]);

        return redirect()->route('register.google');
    }

    public function showGoogleRegistration(Request $request): View|RedirectResponse
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('dashboard');
        }

        $pending = $request->session()->get('pending_google_registration');
        if (! is_array($pending) || empty($pending['email'])) {
            return redirect()->route('register');
        }

        if (User::where('email', $pending['email'])->exists()) {
            $request->session()->forget('pending_google_registration');
            return redirect()->route('login')->with('info', 'Akun dengan email Google ini sudah terdaftar. Silakan masuk.');
        }

        $templates = \App\Models\BusinessTypeTemplate::all();
        $templateSummaries = [];
        foreach ($templates as $tmpl) {
            $summary = \App\Domain\Template\ModuleRegistry::getFeaturesSummaryForTemplate($tmpl->code);
            $templateSummaries[$tmpl->code] = [
                'name' => $tmpl->name,
                'category' => strtoupper($tmpl->industry_category),
                'enabled' => $summary['enabled'],
                'disabled' => $summary['disabled'],
            ];
        }

        return view('auth.google-register', compact('pending', 'templates', 'templateSummaries'));
    }

    public function beginGoogleRegistration(Request $request, AdminWhatsAppService $adminWa): RedirectResponse
    {
        $pendingGoogle = $request->session()->get('pending_google_registration');
        if (! is_array($pendingGoogle) || empty($pendingGoogle['email'])) {
            return redirect()->route('register');
        }

        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255', 'unique:businesses,name'],
            'phone' => ['required', 'string', 'min:10', 'max:20'],
            'template_code' => ['nullable', 'string', 'exists:business_type_templates,code'],
        ]);
        $phone = $this->normalizePhone($validated['phone']);
        if ($phone === null) {
            return back()->withErrors(['phone' => 'Nomor WhatsApp tidak valid. Gunakan format 081234567890 atau 628123456789.'])->withInput();
        }

        $otp = (string) random_int(100000, 999999);
        $result = $adminWa->sendOtp($phone, $otp);
        if (! ($result['success'] ?? false)) {
            return back()->withErrors(['phone' => 'OTP gagal dikirim. Coba lagi.'])->withInput();
        }

        $request->session()->put('pending_registration', [
            ...$pendingGoogle,
            'phone' => $phone,
            'business_name' => $validated['business_name'],
            'template_code' => $validated['template_code'] ?? null,
            'password' => Hash::make(Str::random(32)),
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
            'last_sent_at' => now()->timestamp,
        ]);
        $request->session()->forget('pending_google_registration');

        return redirect()->route('register.verify')->with('status', 'Kode OTP telah dikirim ke WhatsApp pemilik bisnis.');
    }

    private function loginUser(User $user): RedirectResponse
    {
        Auth::guard('web')->login($user, true);

        request()->session()->regenerate();
        request()->session()->put('auth_wa_otp_verified_user_id', $user->id);
        request()->session()->put('auth_wa_otp_verified_at', now()->timestamp);

        if (! $user->active_business_id) {
            $firstBusiness = $user->businesses()->first();

            if ($firstBusiness !== null) {
                $user->update(['active_business_id' => $firstBusiness->id]);
                $user->active_business_id = $firstBusiness->id;
            }
        }

        if ($user->active_business_id) {
            request()->session()->put('active_business_id', $user->active_business_id);
        }

        $intended = (string) request()->session()->get('url.intended', '');
        if ($intended !== '' && (str_contains($intended, '/auth/otp') || str_contains($intended, '/login'))) {
            request()->session()->forget('url.intended');
        }

        return redirect()->intended(route('dashboard'))->with('success', 'Selamat datang, '.$user->name.'!');
    }

    private function normalizePhone(string $phone): ?string
    {
        $phone = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        return preg_match('/^62[1-9][0-9]{7,13}$/', $phone) ? $phone : null;
    }
}
