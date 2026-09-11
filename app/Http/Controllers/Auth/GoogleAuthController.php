<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Domain\Template\BusinessTemplateService;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessTypeTemplate;
use App\Models\SystemSetting;
use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

final class GoogleAuthController extends Controller
{
    public function __construct(private readonly BusinessTemplateService $templateService = new BusinessTemplateService) {}

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

        /** @var User $user */
        $user = DB::transaction(function () use ($googleUser): User {
            // Find user by google_id or email
            $existingUser = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            if ($existingUser) {
                $existingUser->update([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]);

                return $existingUser;
            }

            // Create new user
            $newUser = User::create([
                'name' => $googleUser->getName() ?? 'Pengguna Google',
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'password' => Hash::make(Str::random(32)),
            ]);

            // Create default starter business for new user
            $business = Business::create([
                'name' => 'Usaha '.($googleUser->getName() ?? 'Saya'),
                'currency' => 'IDR',
                'currency_precision' => 0,
                'rounding_strategy' => Business::ROUNDING_ROUND_100,
            ]);

            $business->users()->attach($newUser->id, [
                'id' => (string) Str::uuid(),
                'role' => 'owner',
            ]);

            $newUser->update([
                'active_business_id' => $business->id,
            ]);

            // Apply default general template
            $defaultTemplate = BusinessTypeTemplate::where('code', 'mfg_general')->first()
                ?? BusinessTypeTemplate::first();

            if ($defaultTemplate !== null) {
                $this->templateService->apply($business, $defaultTemplate);
            }

            return $newUser;
        });

        Auth::guard('web')->login($user, true);

        request()->session()->regenerate();

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

        return redirect()->intended(route('dashboard'))->with('success', 'Selamat datang, '.$user->name.'!');
    }
}
