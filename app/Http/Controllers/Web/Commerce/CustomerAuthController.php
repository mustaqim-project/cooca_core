<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Customer;
use App\Models\GlobalCustomer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

final class CustomerAuthController extends Controller
{
    /**
     * Show customer login form.
     */
    public function showLoginForm(Request $request): View|RedirectResponse
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('customer.dashboard');
        }

        $storeSlug  = $request->query('store');
        $store      = $storeSlug ? Business::where('slug', $storeSlug)->where('is_active', true)->first() : null;
        $redirectTo = $request->query('redirect', '');

        return view('customer.auth.login', compact('store', 'redirectTo'));
    }

    /**
     * Authenticate customer with Phone / Email + Password (global identity).
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required'    => 'Nomor WhatsApp atau Email wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $login    = trim($request->input('login'));
        $password = (string) $request->input('password');
        $remember = $request->boolean('remember');
        $isEmail  = (bool) filter_var($login, FILTER_VALIDATE_EMAIL);

        $customer = GlobalCustomer::where($isEmail ? 'email' : 'phone', $login)->first();

        // Normalize Indonesian phone number (62xxx -> 0xxx)
        if (! $customer && ! $isEmail) {
            $normalizedPhone = preg_replace('/[^0-9]/', '', $login);
            if (str_starts_with($normalizedPhone, '62')) {
                $normalizedPhone = '0' . substr($normalizedPhone, 2);
            }
            $customer = GlobalCustomer::where('phone', $normalizedPhone)->first();
        }

        if (! $customer || ! $customer->password || ! Hash::check($password, $customer->password)) {
            return back()->withErrors([
                'login' => 'Nomor WhatsApp / Email atau kata sandi tidak cocok.',
            ])->withInput($request->only('login', 'remember'));
        }

        Auth::guard('customer')->login($customer, $remember);
        $request->session()->regenerate();

        $redirectTo = $request->input('redirect_to') ?: $request->input('redirect');
        if ($redirectTo) {
            if (str_starts_with($redirectTo, '/') || str_starts_with($redirectTo, url('/')) || str_starts_with($redirectTo, (string) config('app.url'))) {
                return redirect($redirectTo)->with('success', "Selamat datang kembali, {$customer->name}!");
            }
        }

        return redirect()->intended(route('customer.dashboard'))->with('success', "Selamat datang kembali, {$customer->name}!");
    }

    /**
     * Show customer registration form.
     */
    public function showRegisterForm(Request $request): View|RedirectResponse
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('customer.dashboard');
        }

        $storeSlug  = $request->query('store');
        $store      = $storeSlug ? Business::where('slug', $storeSlug)->where('is_active', true)->first() : null;
        $redirectTo = $request->query('redirect', '');

        return view('customer.auth.register', compact('store', 'redirectTo'));
    }

    /**
     * Handle customer registration -- creates a GlobalCustomer (cross-store identity).
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'min:2', 'max:150'],
            'phone'    => ['required', 'string', 'min:8', 'max:30'],
            'email'    => ['nullable', 'email', 'max:150'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'phone.required'     => 'Nomor WhatsApp wajib diisi.',
            'password.required'  => 'Kata sandi wajib diisi.',
            'password.min'       => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $phone = trim($validated['phone']);
        $email = ! empty($validated['email']) ? trim($validated['email']) : null;

        if (GlobalCustomer::where('phone', $phone)->exists()) {
            return back()->withErrors([
                'phone' => 'Nomor WhatsApp ini sudah terdaftar. Silakan masuk.',
            ])->withInput($request->except('password', 'password_confirmation'));
        }

        if ($email && GlobalCustomer::where('email', $email)->exists()) {
            return back()->withErrors([
                'email' => 'Email ini sudah terdaftar. Silakan masuk.',
            ])->withInput($request->except('password', 'password_confirmation'));
        }

        $globalCustomer = GlobalCustomer::create([
            'name'     => $validated['name'],
            'phone'    => $phone,
            'email'    => $email,
            'password' => Hash::make($validated['password']),
        ]);

        // Upgrade any guest CRM Customer records with matching phone
        Customer::where('phone', $phone)
            ->whereNull('password')
            ->update([
                'name'      => $validated['name'],
                'email'     => $email ?? null,
                'password'  => Hash::make($validated['password']),
                'is_active' => true,
            ]);

        Auth::guard('customer')->login($globalCustomer, true);
        $request->session()->regenerate();

        if ($email) {
            \App\Domain\Mail\DynamicMailConfig::bootstrap();
            $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'customer.verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $globalCustomer->id,
                    'hash' => sha1($email),
                ]
            );
            try {
                \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\CustomerVerifyEmailMail($globalCustomer, $verificationUrl));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Gagal mengirim email verifikasi customer: ' . $e->getMessage());
            }

            return redirect()->route('customer.verification.notice')
                ->with('status', 'Akun berhasil dibuat! Silakan periksa email Anda untuk memverifikasi akun.');
        }

        $redirectTo = $request->input('redirect_to');
        if ($redirectTo && str_starts_with($redirectTo, '/')) {
            return redirect($redirectTo)->with('success', 'Akun berhasil dibuat! Selamat berbelanja.');
        }

        return redirect()->route('customer.dashboard')->with('success', 'Akun berhasil dibuat! Selamat datang di Cooca.');
    }

    /**
     * Show customer email verification prompt.
     */
    public function showVerificationNotice(Request $request): View|RedirectResponse
    {
        /** @var GlobalCustomer|null $customer */
        $customer = Auth::guard('customer')->user();
        if (! $customer) {
            return redirect()->route('customer.login');
        }

        if ($customer->hasVerifiedEmail()) {
            return redirect()->route('customer.dashboard');
        }

        return view('customer.auth.verify-email');
    }

    /**
     * Verify customer email via signed link.
     */
    public function verifyEmail(Request $request, string $id, string $hash): RedirectResponse
    {
        /** @var GlobalCustomer|null $customer */
        $customer = GlobalCustomer::find($id);

        if (! $customer) {
            abort(404, 'Akun pelanggan tidak ditemukan.');
        }

        if (! hash_equals((string) $hash, sha1((string) $customer->email))) {
            abort(403, 'Tautan verifikasi tidak valid.');
        }

        if ($customer->hasVerifiedEmail()) {
            return redirect()->route('customer.dashboard')->with('info', 'Email Anda sudah diverifikasi sebelumnya.');
        }

        $customer->update(['email_verified_at' => now()]);

        if (! Auth::guard('customer')->check()) {
            Auth::guard('customer')->login($customer, true);
        }

        return redirect()->route('customer.dashboard')->with('success', 'Email berhasil diverifikasi! Selamat datang di Cooca.');
    }

    /**
     * Resend customer verification email.
     */
    public function resendVerificationEmail(Request $request): RedirectResponse
    {
        /** @var GlobalCustomer|null $customer */
        $customer = Auth::guard('customer')->user();

        if (! $customer) {
            return redirect()->route('customer.login');
        }

        if ($customer->hasVerifiedEmail()) {
            return redirect()->route('customer.dashboard');
        }

        if (! empty($customer->email)) {
            \App\Domain\Mail\DynamicMailConfig::bootstrap();
            $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'customer.verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $customer->id,
                    'hash' => sha1((string) $customer->email),
                ]
            );
            try {
                \Illuminate\Support\Facades\Mail::to($customer->email)->send(new \App\Mail\CustomerVerifyEmailMail($customer, $verificationUrl));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Gagal kirim ulang email verifikasi customer: ' . $e->getMessage());
            }
        }

        return back()->with('status', 'verification-link-sent');
    }

    /**
     * Log the customer out.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login')->with('info', 'Anda telah berhasil keluar.');
    }
}
