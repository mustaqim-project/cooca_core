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
        return $request->user()->hasVerifiedEmail()
            ? redirect()->intended(route('dashboard'))
            : view('auth.verify-email');
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
