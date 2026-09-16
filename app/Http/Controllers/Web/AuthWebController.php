<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Template\BusinessTemplateService;
use App\Domain\WhatsApp\AdminWhatsAppService;
use App\Domain\WhatsApp\WhatsAppTrustedDeviceService;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessTypeTemplate;
use App\Models\User;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class AuthWebController extends Controller
{
    /**
     * Show login form.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle web login.
     */
    public function login(Request $request, WhatsAppTrustedDeviceService $trustedDevice): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('web')->attempt($credentials, (bool) $request->boolean('remember'))) {
            $request->session()->regenerate();

            /** @var User $user */
            $user = Auth::guard('web')->user();

            // Set active business context
            if (! $user->active_business_id) {
                $firstBusiness = $user->businesses()->first();
                if ($firstBusiness) {
                    $user->update(['active_business_id' => $firstBusiness->id]);
                    session(['active_business_id' => $firstBusiness->id]);
                }
            } else {
                session(['active_business_id' => $user->active_business_id]);
            }

            // Jika user adalah karyawan (non-owner) atau nomor HP owner sudah terverifikasi seumur hidup
            $userPhone = $this->normalizePhone((string) ($user->phone ?: $user->activeBusiness?->phone));
            if (! $user->isBusinessOwner() || ($user->isPhoneVerified() && $userPhone)) {
                $request->session()->put('auth_wa_otp_verified_user_id', $user->id);
                $request->session()->put('auth_wa_otp_verified_at', now()->timestamp);
            } elseif ($userPhone && $trustedDevice->isTrusted($request, $user, $userPhone)) {
                if (! $user->isPhoneVerified()) {
                    $user->update(['phone_verified_at' => now()]);
                }
                $request->session()->put('auth_wa_otp_verified_user_id', $user->id);
                $request->session()->put('auth_wa_otp_verified_at', now()->timestamp);
            } elseif (app()->isLocal() && in_array($user->email, ['testing@cooca.id', 'demo@cooca.id'])) {
                if ($userPhone) {
                    $trustedDevice->trustDevice($user, $userPhone);
                    $user->update(['phone_verified_at' => now()]);
                }
                $request->session()->put('auth_wa_otp_verified_user_id', $user->id);
                $request->session()->put('auth_wa_otp_verified_at', now()->timestamp);
            }

            // Hindari redirect loop jika url.intended menunjuk ke halaman OTP atau login
            $intended = (string) $request->session()->get('url.intended', '');
            if ($intended !== '' && (str_contains($intended, '/auth/otp') || str_contains($intended, '/login'))) {
                $request->session()->forget('url.intended');
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Kredensial yang diberikan tidak cocok dengan catatan kami.',
        ])->onlyInput('email');
    }

    /**
     * Show register form.
     */
    public function showRegister(): View|RedirectResponse
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('dashboard');
        }

        $templates = BusinessTypeTemplate::all();
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

        return view('auth.register', compact('templates', 'templateSummaries'));
    }

    /**
     * Show the WhatsApp OTP verification form for a pending registration.
     */
    public function showRegisterOtp(Request $request): View|RedirectResponse
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('dashboard');
        }

        $pending = $request->session()->get('pending_registration');

        if (! is_array($pending) || empty($pending['phone'])) {
            return redirect()->route('register')->withErrors([
                'register' => 'Tidak ada pendaftaran yang menunggu verifikasi.',
            ]);
        }

        // Jika akun email atau nomor HP ini ternyata sudah berhasil didaftarkan, arahkan ke login
        if (User::where('email', $pending['email'] ?? '')->orWhere('phone', $pending['phone'])->exists()) {
            $request->session()->forget('pending_registration');
            return redirect()->route('login')->with('info', 'Pendaftaran akun telah selesai. Silakan masuk dengan kata sandi Anda.');
        }

        return view('auth.register-otp', [
            'phone' => $this->maskPhone((string) $pending['phone']),
            'expiresAt' => (int) ($pending['expires_at'] ?? 0),
            'resendIn' => max(0, ((int) ($pending['last_sent_at'] ?? 0)) + 60 - now()->timestamp),
            'deliveryError' => $pending['delivery_error'] ?? null,
        ]);
    }

    /**
     * Handle web register.
     */
    public function register(Request $request, AdminWhatsAppService $adminWa): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'min:10', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'business_name' => ['required', 'string', 'max:255', 'unique:businesses,name'],
            'template_code' => ['nullable', 'string', 'exists:business_type_templates,code'],
        ]);

        $phone = $this->normalizePhone((string) $validated['phone']);
        if ($phone === null) {
            return back()->withErrors(['phone' => 'Nomor WhatsApp tidak valid. Gunakan format 081234567890 atau 628123456789.'])->withInput();
        }

        $otp = (string) random_int(100000, 999999);
        $pending = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $phone,
            'password' => Hash::make($validated['password']),
            'business_name' => $validated['business_name'],
            'template_code' => $validated['template_code'] ?? null,
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
            'last_sent_at' => now()->timestamp,
        ];

        $result = $adminWa->sendOtp($phone, $otp);
        if (! ($result['success'] ?? false)) {
            // Pola konsisten: tetap lanjut ke halaman OTP, user bisa mencoba "Kirim ulang OTP".
            $pending['otp_hash'] = null;
            $pending['delivery_error'] = 'OTP gagal dikirim. Coba lagi.';
            $request->session()->put('pending_registration', $pending);

            return redirect()->route('register.verify')->with('status', 'Data pendaftaran diterima, namun OTP belum terkirim. Silakan tekan "Kirim ulang OTP".');
        }

        $request->session()->put('pending_registration', $pending);

        return redirect()->route('register.verify')->with('status', 'Kode OTP telah dikirim ke WhatsApp pemilik bisnis.');
    }

    /**
     * Verify the registration OTP and create the account only after success.
     */
    public function verifyRegisterOtp(Request $request, BusinessTemplateService $templateService): RedirectResponse
    {
        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);
        $pending = $request->session()->get('pending_registration');

        if (! is_array($pending) || empty($pending['phone'])) {
            return redirect()->route('register')->withErrors(['register' => 'Sesi OTP tidak ditemukan. Silakan daftar kembali.']);
        }

        if (empty($pending['otp_hash'])) {
            return redirect()->route('register.verify')->withErrors(['otp' => 'OTP belum berhasil dikirim. Tekan "Kirim ulang OTP" terlebih dahulu.']);
        }

        if ((int) ($pending['expires_at'] ?? 0) < now()->timestamp) {
            $request->session()->forget('pending_registration');
            return redirect()->route('register')->withErrors(['otp' => 'Kode OTP sudah kedaluwarsa. Silakan daftar kembali.']);
        }

        // Defense-in-depth: batas percobaan global per nomor (tidak dapat di-reset via session baru).
        $cacheKey = 'otp_attempts:register:' . $pending['phone'];
        $cachedAttempts = ((int) Cache::get($cacheKey, 0)) + 1;
        if ($cachedAttempts > 5) {
            Cache::forget($cacheKey);
            $request->session()->forget('pending_registration');
            return redirect()->route('register')->withErrors(['otp' => 'Terlalu banyak percobaan OTP. Silakan daftar kembali.']);
        }

        $attempts = (int) ($pending['attempts'] ?? 0) + 1;
        if ($attempts > 5) {
            $request->session()->forget('pending_registration');
            return redirect()->route('register')->withErrors(['otp' => 'Batas percobaan OTP terlampaui. Silakan daftar kembali.']);
        }

        if (! Hash::check($validated['otp'], $pending['otp_hash'])) {
            Cache::put($cacheKey, $cachedAttempts, now()->addMinutes(10));
            $pending['attempts'] = $attempts;
            $request->session()->put('pending_registration', $pending);
            return back()->withErrors(['otp' => 'Kode OTP salah. Sisa percobaan: ' . (5 - $attempts) . '.']);
        }

        Cache::forget($cacheKey);

        if (Business::where('name', $pending['business_name'])->exists()) {
            $request->session()->forget('pending_registration');
            return redirect()->route('register')->withErrors([
                'business_name' => 'Nama bisnis sudah digunakan. Silakan gunakan nama bisnis lain.',
            ])->withInput(['business_name' => $pending['business_name']]);
        }

        /** @var User $user */
        $user = DB::transaction(function () use ($pending, $templateService): User {
            $user = User::create([
                'name' => $pending['name'],
                'email' => $pending['email'],
                'phone' => $pending['phone'],
                'phone_verified_at' => now(),
                'password' => $pending['password'],
                'google_id' => $pending['google_id'] ?? null,
                'avatar' => $pending['avatar'] ?? null,
                'email_verified_at' => isset($pending['google_id']) ? now() : null,
            ]);

            $templateCode = $pending['template_code'] ?? null;
            $tmpl = ! empty($templateCode) ? BusinessTypeTemplate::where('code', $templateCode)->first() : null;
            $disabledModules = $tmpl ? \App\Domain\Template\ModuleRegistry::getDisabledModulesForTemplate($tmpl->code) : [];

            $business = Business::create([
                'name' => $pending['business_name'],
                'phone' => $pending['phone'],
                'currency' => 'IDR',
                'currency_precision' => 0,
                'rounding_strategy' => Business::ROUNDING_ROUND_100,
                'industry_category' => $tmpl?->industry_category,
                'template_code' => $tmpl?->code,
                'disabled_modules' => $disabledModules,
            ]);

            $business->users()->attach($user->id, [
                'id' => (string) Str::uuid(),
                'role' => 'owner',
            ]);

            $user->update(['active_business_id' => $business->id]);

            // Apply preset template if selected
            if ($tmpl) {
                $templateService->apply($business, $tmpl);
            }

            return $user;
        });

        \App\Domain\Mail\DynamicMailConfig::bootstrap();
        event(new \Illuminate\Auth\Events\Registered($user));

        Auth::guard('web')->login($user);
        $request->session()->forget('pending_registration');
        $request->session()->regenerate();
        $request->session()->put('auth_wa_otp_verified_user_id', $user->id);
        $request->session()->put('auth_wa_otp_verified_at', now()->timestamp);
        session(['active_business_id' => $user->active_business_id]);

        $phone = $this->normalizePhone((string) $user->phone);
        if ($phone) {
            app(WhatsAppTrustedDeviceService::class)->trustDevice($user, $phone);
        }

        // Jika mendaftar dengan Google, email sudah diverifikasi oleh Google, langsung ke dashboard
        if (isset($pending['google_id'])) {
            return redirect()->intended(route('dashboard'))->with('success', 'Selamat datang! Bisnis Anda telah berhasil didaftarkan.');
        }

        return redirect()->route('verification.notice')->with('status', 'Selamat datang! Bisnis Anda telah berhasil dibuat. Tautan verifikasi email telah dikirimkan ke alamat email Anda.');
    }

    /**
     * Resend the OTP for the pending registration.
     */
    public function resendRegisterOtp(Request $request, AdminWhatsAppService $adminWa): RedirectResponse
    {
        $pending = $request->session()->get('pending_registration');
        if (! is_array($pending) || empty($pending['phone'])) {
            return redirect()->route('register')->withErrors(['register' => 'Sesi OTP tidak ditemukan. Silakan daftar kembali.']);
        }

        if ((int) ($pending['last_sent_at'] ?? 0) > now()->subSeconds(60)->timestamp) {
            return back()->withErrors(['otp' => 'Tunggu 60 detik sebelum meminta OTP baru.']);
        }

        $otp = (string) random_int(100000, 999999);
        $result = $adminWa->sendOtp((string) $pending['phone'], $otp);
        if (! ($result['success'] ?? false)) {
            return back()->withErrors(['otp' => 'OTP gagal dikirim. Coba lagi.']);
        }

        $pending['otp_hash'] = Hash::make($otp);
        $pending['expires_at'] = now()->addMinutes(10)->timestamp;
        $pending['attempts'] = 0;
        $pending['last_sent_at'] = now()->timestamp;
        $pending['delivery_error'] = null;
        Cache::forget('otp_attempts:register:' . $pending['phone']);
        $request->session()->put('pending_registration', $pending);

        return back()->with('status', 'OTP baru telah dikirim ke WhatsApp pemilik bisnis.');
    }

    /**
     * Change the WhatsApp number of a pending registration and send a fresh OTP.
     */
    public function changeRegisterPhone(Request $request, AdminWhatsAppService $adminWa): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'min:10', 'max:20'],
        ]);

        $pending = $request->session()->get('pending_registration');
        if (! is_array($pending) || empty($pending['phone'])) {
            return redirect()->route('register')->withErrors(['register' => 'Sesi OTP tidak ditemukan. Silakan daftar kembali.']);
        }

        $phone = $this->normalizePhone((string) $validated['phone']);
        if ($phone === null) {
            return back()->withErrors(['phone' => 'Nomor WhatsApp tidak valid. Gunakan format 081234567890 atau 628123456789.'])->withInput();
        }
        if ($phone === $pending['phone']) {
            return back()->withErrors(['phone' => 'Nomor sama dengan nomor sebelumnya. Gunakan nomor lain.'])->withInput();
        }

        $otp = (string) random_int(100000, 999999);
        $result = $adminWa->sendOtp($phone, $otp);
        $sent = (bool) ($result['success'] ?? false);

        $pending['phone'] = $phone;
        $pending['otp_hash'] = $sent ? Hash::make($otp) : null;
        $pending['expires_at'] = now()->addMinutes(10)->timestamp;
        $pending['attempts'] = 0;
        $pending['last_sent_at'] = now()->timestamp;
        $pending['delivery_error'] = $sent ? null : 'OTP gagal dikirim. Coba lagi.';
        Cache::forget('otp_attempts:register:' . $phone);
        $request->session()->put('pending_registration', $pending);

        if (! $sent) {
            return back()->withErrors(['phone' => 'OTP gagal dikirim. Coba lagi.'])->withInput();
        }

        return back()->with('status', 'Nomor WhatsApp diperbarui. OTP baru telah dikirim ke nomor tersebut.');
    }

    private function normalizePhone(string $phone): ?string
    {
        $phone = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($phone, '8')) {
            $phone = '62' . $phone;
        } elseif (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        return preg_match('/^62[1-9][0-9]{7,13}$/', $phone) ? $phone : null;
    }

    private function maskPhone(string $phone): string
    {
        return strlen($phone) > 6
            ? substr($phone, 0, 4) . str_repeat('*', max(2, strlen($phone) - 7)) . substr($phone, -3)
            : $phone;
    }

    /**
     * Show business selection modal/page.
     */
    public function selectBusiness(): View
    {
        /** @var User $user */
        $user = Auth::guard('web')->user();
        $businesses = $user->businesses()->get();
        $templates = BusinessTypeTemplate::all();
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

        return view('auth.select-business', compact('businesses', 'templates', 'templateSummaries'));
    }

    /**
     * Create and attach a new business to the authenticated user.
     */
    public function storeBusiness(Request $request, BusinessTemplateService $templateService): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:businesses,name'],
            'template_code' => ['nullable', 'string', 'exists:business_type_templates,code'],
            'currency' => ['nullable', 'string', 'max:3'],
        ]);

        /** @var User $user */
        $user = Auth::guard('web')->user();

        $entitlement = app(\App\Domain\Billing\EntitlementService::class);
        if (! $entitlement->canCreateBusiness($user)) {
            return redirect()->route('billing.limits')->with('error', 'Paket Free dibatasi untuk 1 bisnis per akun. Silakan tingkatkan ke paket Cooca untuk mengelola banyak cabang/bisnis.');
        }

        $business = DB::transaction(function () use ($user, $validated, $templateService): Business {
            $templateCode = $validated['template_code'] ?? null;
            $tmpl = ! empty($templateCode) ? BusinessTypeTemplate::where('code', $templateCode)->first() : null;
            $disabledModules = $tmpl ? \App\Domain\Template\ModuleRegistry::getDisabledModulesForTemplate($tmpl->code) : [];

            $business = Business::create([
                'name' => $validated['name'],
                'currency' => $validated['currency'] ?? 'IDR',
                'currency_precision' => 0,
                'rounding_strategy' => Business::ROUNDING_ROUND_100,
                'industry_category' => $tmpl?->industry_category,
                'template_code' => $tmpl?->code,
                'disabled_modules' => $disabledModules,
            ]);

            $business->users()->attach($user->id, [
                'id' => (string) Str::uuid(),
                'role' => 'owner',
            ]);

            $user->update(['active_business_id' => $business->id]);

            // Apply preset template if selected
            if ($tmpl) {
                $templateService->apply($business, $tmpl);
            }

            return $business;
        });

        session(['active_business_id' => $business->id]);

        return redirect()->route('dashboard')->with('success', "Bisnis '{$business->name}' berhasil ditambahkan dan diaktifkan.");
    }

    /**
     * Switch active business tenant.
     */
    public function switchBusiness(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_id' => ['required', 'string', 'exists:businesses,id'],
        ]);

        /** @var User $user */
        $user = Auth::guard('web')->user();

        // Check user belongs to this business
        $hasAccess = $user->businesses()->where('businesses.id', $validated['business_id'])->exists();
        if (! $hasAccess) {
            abort(403, 'Anda tidak memiliki akses ke bisnis ini.');
        }

        $user->update(['active_business_id' => $validated['business_id']]);
        session(['active_business_id' => $validated['business_id']]);

        return redirect()->route('dashboard')->with('success', 'Berhasil beralih bisnis.');
    }

    /**
     * Show profile completion form for onboarding owners.
     */
    public function showCompleteProfile(): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::guard('web')->user();
        $business = Context::hasBusiness() ? Context::business() : $user->businesses()->first();

        // Jika data profil & usaha sudah lengkap, cegah akses ulang dan langsung redirect ke dashboard
        $userPhoneMissing = empty(trim((string) ($user->phone ?? '')));
        $userNameMissing = empty(trim((string) ($user->name ?? '')));
        $businessNameMissing = ! $business || empty(trim((string) ($business->name ?? ''))) || str_starts_with($business->name, 'Usaha Saya') || str_starts_with($business->name, 'Usaha Pengguna');

        if (! $userPhoneMissing && ! $userNameMissing && ! $businessNameMissing) {
            return redirect()->route('dashboard');
        }

        return view('auth.complete-profile', compact('user', 'business'));
    }

    /**
     * Update and save completed profile details (Name, Phone, Business Name).
     */
    public function updateCompleteProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'business_name' => ['required', 'string', 'max:255', 'unique:businesses,name,' . optional(Context::business())->id],
        ]);

        /** @var User $user */
        $user = Auth::guard('web')->user();

        $phoneChanged = $user->phone !== $validated['phone'];
        $userUpdates = [
            'name' => $validated['name'],
            'phone' => $validated['phone'],
        ];
        if ($phoneChanged) {
            $userUpdates['phone_verified_at'] = null;
        }

        $user->update($userUpdates);

        $business = Context::hasBusiness() ? Context::business() : $user->businesses()->first();
        if ($business) {
            $business->update([
                'name' => $validated['business_name'],
                'phone' => $validated['phone'],
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Profil dan identitas bisnis Anda berhasil diperbarui.');
    }

    /**
     * Handle web logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
