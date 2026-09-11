<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\WhatsApp\AdminWhatsAppService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

final class RequireWhatsAppOtp
{
    public function __construct(private readonly AdminWhatsAppService $adminWa) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! auth('web')->check() || $this->isExempt($request)) {
            return $next($request);
        }

        $user = auth('web')->user();
        $verifiedUserId = $request->session()->get('auth_wa_otp_verified_user_id');
        if ($verifiedUserId === $user->id) {
            return $next($request);
        }

        $challenge = $request->session()->get('auth_wa_otp_challenge');
        if (is_array($challenge) && ($challenge['user_id'] ?? null) === $user->id) {
            if ((int) ($challenge['expires_at'] ?? 0) >= now()->timestamp) {
                $this->rememberIntendedUrl($request);
                return redirect()->route('auth.otp');
            }

            $request->session()->forget('auth_wa_otp_challenge');
        }

        $phone = $this->normalizePhone((string) ($user->phone ?: $user->activeBusiness?->phone));
        if ($phone === null) {
            $this->rememberIntendedUrl($request);
            return redirect()->route('profile.complete')->withErrors([
                'phone' => 'Verifikasi OTP memerlukan nomor WhatsApp owner. Lengkapi nomor WhatsApp terlebih dahulu.',
            ]);
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
            'delivery_error' => $sent ? null : 'OTP gagal dikirim. Pastikan WhatsApp Admin Cooca sedang terhubung.',
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
            'profile.complete.save'
        );
    }

    private function rememberIntendedUrl(Request $request): void
    {
        if (! $request->session()->has('url.intended')) {
            $request->session()->put('url.intended', $request->fullUrl());
        }
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
