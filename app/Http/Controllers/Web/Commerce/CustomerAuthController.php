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

        $redirectTo = $request->input('redirect_to');
        if ($redirectTo && str_starts_with($redirectTo, '/')) {
            return redirect($redirectTo)->with('success', "Selamat datang kembali, {$customer->name}!");
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

        $redirectTo = $request->input('redirect_to');
        if ($redirectTo && str_starts_with($redirectTo, '/')) {
            return redirect($redirectTo)->with('success', 'Akun berhasil dibuat! Selamat berbelanja.');
        }

        return redirect()->route('customer.dashboard')->with('success', 'Akun berhasil dibuat! Selamat datang di COOCA UMKM.');
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
