<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Template\BusinessTemplateService;
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
     * Handle web register.
     */
    public function register(Request $request, BusinessTemplateService $templateService): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'business_name' => ['required', 'string', 'max:255'],
            'template_code' => ['nullable', 'string', 'exists:business_type_templates,code'],
        ]);

        /** @var User $user */
        $user = DB::transaction(function () use ($validated, $templateService): User {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $business = Business::create([
                'name' => $validated['business_name'],
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
            if (! empty($validated['template_code'])) {
                /** @var BusinessTypeTemplate $tmpl */
                $tmpl = BusinessTypeTemplate::where('code', $validated['template_code'])->first();
                if ($tmpl) {
                    $templateService->apply($business, $tmpl);
                }
            }

            return $user;
        });

        Auth::guard('web')->login($user);
        $request->session()->regenerate();
        session(['active_business_id' => $user->active_business_id]);

        return redirect()->route('dashboard')->with('success', 'Selamat datang! Bisnis Anda telah berhasil dibuat.');
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
