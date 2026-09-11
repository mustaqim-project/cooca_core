<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Template\BusinessTemplateService;
use App\Domain\WhatsApp\AdminWhatsAppService;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessTypeTemplate;
use App\Models\User;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
    public function login(Request $request): RedirectResponse
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

        return view('auth.register', compact('templates'));
    }

    /**
     * Show the WhatsApp OTP verification form for a pending registration.
     */
    public function showRegisterOtp(Request $request): View|RedirectResponse
    {
        $pending = $request->session()->get('pending_registration');

        if (! is_array($pending) || empty($pending['phone'])) {
            return redirect()->route('register')->withErrors([
                'register' => 'Tidak ada pendaftaran yang menunggu verifikasi.',
            ]);
        }

        return view('auth.register-otp', [
            'phone' => $this->maskPhone((string) $pending['phone']),
            'expiresAt' => (int) ($pending['expires_at'] ?? 0),
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
            'business_name' => ['required', 'string', 'max:255'],
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

        $result = $adminWa->sendMessage($phone, "Kode OTP pendaftaran Cooca Anda adalah *{$otp}*. Kode ini berlaku 10 menit. Jangan bagikan kode ini kepada siapa pun.");
        if (! ($result['success'] ?? false)) {
            return back()->withErrors([
                'phone' => 'OTP gagal dikirim. Pastikan WhatsApp Admin Cooca sedang terhubung, lalu coba lagi.',
            ])->withInput();
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

        if (! is_array($pending) || empty($pending['otp_hash'])) {
            return redirect()->route('register')->withErrors(['register' => 'Sesi OTP tidak ditemukan. Silakan daftar kembali.']);
        }

        if ((int) ($pending['expires_at'] ?? 0) < now()->timestamp) {
            $request->session()->forget('pending_registration');
            return redirect()->route('register')->withErrors(['otp' => 'Kode OTP sudah kedaluwarsa. Silakan daftar kembali.']);
        }

        $attempts = (int) ($pending['attempts'] ?? 0) + 1;
        if ($attempts > 5) {
            $request->session()->forget('pending_registration');
            return redirect()->route('register')->withErrors(['otp' => 'Batas percobaan OTP terlampaui. Silakan daftar kembali.']);
        }

        if (! Hash::check($validated['otp'], $pending['otp_hash'])) {
            $pending['attempts'] = $attempts;
            $request->session()->put('pending_registration', $pending);
            return back()->withErrors(['otp' => 'Kode OTP salah. Sisa percobaan: ' . (5 - $attempts) . '.']);
        }

        /** @var User $user */
        $user = DB::transaction(function () use ($pending, $templateService): User {
            $user = User::create([
                'name' => $pending['name'],
                'email' => $pending['email'],
                'phone' => $pending['phone'],
                'password' => $pending['password'],
                'google_id' => $pending['google_id'] ?? null,
                'avatar' => $pending['avatar'] ?? null,
                'email_verified_at' => isset($pending['google_id']) ? now() : null,
            ]);

            $business = Business::create([
                'name' => $pending['business_name'],
                'phone' => $pending['phone'],
                'currency' => 'IDR',
                'currency_precision' => 0,
                'rounding_strategy' => Business::ROUNDING_ROUND_100,
            ]);

            $business->users()->attach($user->id, [
                'id' => (string) Str::uuid(),
                'role' => 'owner',
            ]);

            $user->update(['active_business_id' => $business->id]);

            // Apply preset template if selected
            if (! empty($pending['template_code'])) {
                /** @var BusinessTypeTemplate $tmpl */
                $tmpl = BusinessTypeTemplate::where('code', $pending['template_code'])->first();
                if ($tmpl) {
                    $templateService->apply($business, $tmpl);
                }
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

        return redirect()->route('dashboard')->with('success', 'Selamat datang! Bisnis Anda telah berhasil dibuat. Tautan verifikasi email telah dikirimkan ke alamat email Anda.');
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
        $result = $adminWa->sendMessage((string) $pending['phone'], "Kode OTP pendaftaran Cooca Anda adalah *{$otp}*. Kode ini berlaku 10 menit. Jangan bagikan kode ini kepada siapa pun.");
        if (! ($result['success'] ?? false)) {
            return back()->withErrors(['otp' => 'OTP gagal dikirim. Pastikan WhatsApp Admin Cooca sedang terhubung.']);
        }

        $pending['otp_hash'] = Hash::make($otp);
        $pending['expires_at'] = now()->addMinutes(10)->timestamp;
        $pending['attempts'] = 0;
        $pending['last_sent_at'] = now()->timestamp;
        $request->session()->put('pending_registration', $pending);

        return back()->with('status', 'OTP baru telah dikirim ke WhatsApp pemilik bisnis.');
    }

    private function normalizePhone(string $phone): ?string
    {
        $phone = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($phone, '0')) {
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

        return view('auth.select-business', compact('businesses', 'templates'));
    }

    /**
     * Create and attach a new business to the authenticated user.
     */
    public function storeBusiness(Request $request, BusinessTemplateService $templateService): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'template_code' => ['nullable', 'string', 'exists:business_type_templates,code'],
            'currency' => ['nullable', 'string', 'max:3'],
        ]);

        /** @var User $user */
        $user = Auth::guard('web')->user();

        $entitlement = app(\App\Domain\Billing\EntitlementService::class);
        if (! $entitlement->canCreateBusiness($user)) {
            return redirect()->route('billing.limits')->with('error', 'Paket Free dibatasi untuk 1 bisnis per akun. Silakan tingkatkan ke paket Cooca UMKM untuk mengelola banyak cabang/bisnis.');
        }

        $business = DB::transaction(function () use ($user, $validated, $templateService): Business {
            $business = Business::create([
                'name' => $validated['name'],
                'currency' => $validated['currency'] ?? 'IDR',
                'currency_precision' => 0,
                'rounding_strategy' => Business::ROUNDING_ROUND_100,
            ]);

            $business->users()->attach($user->id, [
                'id' => (string) Str::uuid(),
                'role' => 'owner',
            ]);

            $user->update(['active_business_id' => $business->id]);

            // Apply preset template if selected
            if (! empty($validated['template_code'])) {
                /** @var BusinessTypeTemplate $tmpl */
                $tmpl = BusinessTypeTemplate::where('code', $validated['template_code'])->first();
                if ($tmpl) {
                    $templateService->apply($business, $tmpl);
                }
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
            'business_name' => ['required', 'string', 'max:255'],
        ]);

        /** @var User $user */
        $user = Auth::guard('web')->user();

        $user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
        ]);

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
