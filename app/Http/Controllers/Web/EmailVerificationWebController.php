<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Mail\DynamicMailConfig;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class EmailVerificationWebController extends Controller
{
    /**
     * Show email verification prompt.
     */
    public function notice(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // Bypass for reviewer, testing accounts, or explicit ?bypass=1 parameter
        if ($user && ($request->has('bypass') || in_array($user->email, ['reviewer@cooca.id', 'testing@cooca.id', 'demo@cooca.id'], true))) {
            if (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            $intended = (string) $request->session()->get('url.intended', '');
            if ($intended !== '' && (str_contains($intended, '/email/verify') || str_contains($intended, '/auth/otp'))) {
                $request->session()->forget('url.intended');
            }

            return redirect()->intended(route('dashboard'))->with('success', 'Alamat email berhasil diverifikasi.');
        }

        if ($request->user()->hasVerifiedEmail()) {
            $intended = (string) $request->session()->get('url.intended', '');
            if ($intended !== '' && (str_contains($intended, '/email/verify') || str_contains($intended, '/auth/otp'))) {
                $request->session()->forget('url.intended');
            }

            return redirect()->intended(route('dashboard'));
        }

        return view('auth.verify-email');
    }

    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verify(Request $request, string $id, string $hash): RedirectResponse
    {
        /** @var User|null $user */
        $user = User::find($id);

        if (! $user) {
            abort(404, 'Pengguna tidak ditemukan.');
        }

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Tautan verifikasi tidak valid.');
        }

        $intended = (string) $request->session()->get('url.intended', '');
        if ($intended !== '' && (str_contains($intended, '/email/verify') || str_contains($intended, '/auth/otp'))) {
            $request->session()->forget('url.intended');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard').'?verified=1')->with('status', 'Alamat email Anda telah diverifikasi sebelumnya.');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->intended(route('dashboard').'?verified=1')->with('success', 'Selamat! Alamat email Anda telah berhasil diverifikasi.');
    }

    /**
     * Resend the email verification notification.
     */
    public function resend(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        DynamicMailConfig::bootstrap();

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
