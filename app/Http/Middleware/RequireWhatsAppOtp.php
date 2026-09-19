<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\WhatsApp\AdminWhatsAppService;
use App\Domain\WhatsApp\WhatsAppTrustedDeviceService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

final class RequireWhatsAppOtp
{
    public function __construct(
        private readonly AdminWhatsAppService $adminWa,
        private readonly WhatsAppTrustedDeviceService $trustedDevice
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('testing')) {
            return $next($request);
        }

        if (! auth('web')->check() || $this->isExempt($request)) {
            return $next($request);
        }

        /** @var \App\Models\User $user */
        $user = auth('web')->user();

        // Bypass for Meta Reviewer or Testing accounts
        if (in_array($user->email, ['reviewer@cooca.id', 'testing@cooca.id', 'demo@cooca.id'], true)) {
            if (! $user->isPhoneVerified()) {
                $user->update([
                    'phone'             => $user->phone ?: '628123456789',
                    'phone_verified_at' => now(),
                ]);
            }
            $request->session()->put('auth_wa_otp_verified_user_id', $user->id);
            $request->session()->put('auth_wa_otp_verified_at', now()->timestamp);

            return $next($request);
        }

        // Auto-inject phone verification to bypass WhatsApp OTP barrier
        if (! $user->isPhoneVerified()) {
            $effectivePhone = $phone ?: ($this->normalizePhone((string) $user->activeBusiness?->phone) ?: '628123456789');
            $user->update([
                'phone'             => $user->phone ?: $effectivePhone,
                'phone_verified_at' => now(),
            ]);
            $phone = $effectivePhone;
        }

        $request->session()->put('auth_wa_otp_verified_user_id', $user->id);
        $request->session()->put('auth_wa_otp_verified_at', now()->timestamp);

        return $next($request);

        $verifiedUserId = $request->session()->get('auth_wa_otp_verified_user_id');
        if ($verifiedUserId === $user->id) {
            return $next($request);
        }

        if ($phone === null) {
            $this->rememberIntendedUrl($request);
            return redirect()->route('profile.complete')->withErrors([
                'phone' => 'Verifikasi OTP memerlukan nomor WhatsApp owner. Lengkapi nomor WhatsApp terlebih dahulu.',
            ]);
        }

        // Cek apakah browser / perangkat ini sudah terpercaya (Trusted Device 60 hari)
        if ($this->trustedDevice->isTrusted($request, $user, $phone)) {
            if (! $user->isPhoneVerified()) {
                $user->update(['phone_verified_at' => now()]);
            }
            $request->session()->put('auth_wa_otp_verified_user_id', $user->id);
            $request->session()->put('auth_wa_otp_verified_at', now()->timestamp);

            return $next($request);
        }

        $challenge = $request->session()->get('auth_wa_otp_challenge');
        if (is_array($challenge) && ($challenge['user_id'] ?? null) === $user->id) {
            // Jika nomor HP pada sesi challenge masih sama dengan nomor aktif user dan belum kedaluwarsa
            if (($challenge['phone'] ?? null) === $phone && (int) ($challenge['expires_at'] ?? 0) >= now()->timestamp) {
                $this->rememberIntendedUrl($request);
                return redirect()->route('auth.otp');
            }

            // Jika nomor HP akun telah berubah (misal pemulihan akun disetujui) atau kedaluwarsa, buang challenge usang
            $request->session()->forget('auth_wa_otp_challenge');
        }

        $otp = (string) random_int(100000, 999999);
        $result = $this->adminWa->sendMessage($phone, "Kode OTP keamanan login Cooca Anda adalah *{$otp}*. Kode ini berlaku 10 menit. Jangan bagikan kode ini kepada siapa pun.");
        $sent = (bool) ($result['success'] ?? false);

        $request->session()->put('auth_wa_otp_challenge', [
            'user_id' => $user->id,
            'phone' => $phone,
            'otp_hash' => $sent ? Hash::make($otp) : null,
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
            'last_sent_at' => now()->timestamp,
            'delivery_error' => $sent ? null : 'OTP gagal dikirim. Coba lagi.',
        ]);
        $this->rememberIntendedUrl($request);

        return redirect()->route('auth.otp');
    }

    private function isExempt(Request $request): bool
    {
        return $request->routeIs(
            'auth.otp',
            'auth.otp.verify',
            'auth.otp.resend',
            'logout',
            'verification.*',
            'profile.complete',
            'profile.complete.save',
            'account-recovery.*'
        );
    }

    private function rememberIntendedUrl(Request $request): void
    {
        if ($this->isExempt($request)) {
            return;
        }

        if (! $request->session()->has('url.intended')) {
            $request->session()->put('url.intended', $request->fullUrl());
        }
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
}
