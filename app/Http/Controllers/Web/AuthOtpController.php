<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\WhatsApp\AdminWhatsAppService;
use App\Domain\WhatsApp\WhatsAppTrustedDeviceService;
use App\Http\Controllers\Controller;
use App\Models\AccountRecoveryRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

final class AuthOtpController extends Controller
{
    public function show(Request $request, AdminWhatsAppService $adminWa, WhatsAppTrustedDeviceService $trustedDevice): View|RedirectResponse
    {
        if (! auth('web')->check()) {
            return redirect()->route('login');
        }

        $user = auth('web')->user();

        // 1. Karyawan / user tambahan pada bisnis tidak perlu OTP
        if (! $user->isBusinessOwner()) {
            return redirect()->route('dashboard');
        }

        $userPhone = $this->normalizePhone((string) ($user->phone ?: $user->activeBusiness?->phone));

        // 2. Jika nomor HP owner sudah terverifikasi seumur hidup, langsung ke dashboard
        if ($user->isPhoneVerified() && $userPhone) {
            $request->session()->put('auth_wa_otp_verified_user_id', $user->id);
            $request->session()->put('auth_wa_otp_verified_at', $user->phone_verified_at->timestamp);

            return redirect()->route('dashboard');
        }

        // Jika user sudah terverifikasi OTP di sesi ini, langsung alihkan ke dashboard
        if ($request->session()->get('auth_wa_otp_verified_user_id') === $user->id) {
            return redirect()->route('dashboard');
        }

        // Jika browser/perangkat ini adalah Trusted Device yang masih valid, tandai sesi dan langsung alihkan ke dashboard
        if ($userPhone && $trustedDevice->isTrusted($request, $user, $userPhone)) {
            if (! $user->isPhoneVerified()) {
                $user->update(['phone_verified_at' => now()]);
            }
            $request->session()->put('auth_wa_otp_verified_user_id', $user->id);
            $request->session()->put('auth_wa_otp_verified_at', now()->timestamp);

            return redirect()->route('dashboard');
        }
        $challenge = $request->session()->get('auth_wa_otp_challenge');

        // Jika nomor HP pada sesi challenge berbeda dengan nomor aktif user di database (misal pemulihan akun disetujui),
        // atau challenge belum ada / sudah expired, perbarui sesi dan kirimkan OTP ke nomor baru.
        if (
            ! is_array($challenge)
            || empty($challenge)
            || ($challenge['user_id'] ?? null) !== $user->id
            || ($userPhone && ($challenge['phone'] ?? null) !== $userPhone)
            || (int) ($challenge['expires_at'] ?? 0) < now()->timestamp
        ) {
            if ($userPhone) {
                $otp = (string) random_int(100000, 999999);
                $result = $adminWa->sendOtp($userPhone, $otp);
                $sent = (bool) ($result['success'] ?? false);

                // In local dev, allow testing OTP even if WhatsApp server is offline
                if (! $sent && app()->isLocal()) {
                    \Illuminate\Support\Facades\Log::info("[DEV-OTP] WhatsApp server offline. Local OTP for {$userPhone} ({$user->email}): {$otp} (or master testing OTP: 123456)");
                }

                $challenge = [
                    'user_id' => $user->id,
                    'phone' => $userPhone,
                    'otp_hash' => ($sent || app()->isLocal()) ? Hash::make($otp) : null,
                    'expires_at' => now()->addMinutes(10)->timestamp,
                    'attempts' => 0,
                    'last_sent_at' => now()->timestamp,
                    'delivery_error' => ($sent || app()->isLocal()) ? null : 'OTP gagal dikirim. Coba lagi.',
                ];
                $request->session()->put('auth_wa_otp_challenge', $challenge);
            } else {
                $challenge = is_array($challenge) ? $challenge : [];
            }
        }

        $deliveryError = $challenge['delivery_error'] ?? null;
        if ($deliveryError) {
            // Normalisasi pesan jika sesi lama masih menyimpan redaksi teks sebelumnya
            $deliveryError = 'OTP gagal dikirim. Coba lagi.';
        }

        $activeRecovery = AccountRecoveryRequest::where('user_id', $user->id)
            ->latest('created_at')
            ->first();

        return view('auth.otp', [
            'phone' => $this->maskPhone((string) ($challenge['phone'] ?? $userPhone ?? '')),
            'expiresAt' => (int) ($challenge['expires_at'] ?? 0),
            'resendIn' => max(0, ((int) ($challenge['last_sent_at'] ?? 0)) + 60 - now()->timestamp),
            'deliveryError' => $deliveryError,
            'activeRecovery' => $activeRecovery,
        ]);
    }

    public function verify(Request $request, WhatsAppTrustedDeviceService $trustedDevice): RedirectResponse
    {
        $validated = $request->validate(['otp' => ['required', 'digits:6']]);
        $challenge = $request->session()->get('auth_wa_otp_challenge');
        $user = auth('web')->user();

        if (! is_array($challenge) || ($challenge['user_id'] ?? null) !== $user->id) {
            return redirect()->route('auth.otp')->withErrors(['otp' => 'Sesi OTP tidak ditemukan. Minta OTP baru.']);
        }
        if ((int) ($challenge['expires_at'] ?? 0) < now()->timestamp) {
            $request->session()->forget('auth_wa_otp_challenge');
            return back()->withErrors(['otp' => 'Kode OTP sudah kedaluwarsa.']);
        }

        // Defense-in-depth: batas percobaan global per akun (tidak dapat di-reset via session baru).
        $cacheKey = 'otp_attempts:auth:' . $user->id;
        $cachedAttempts = ((int) Cache::get($cacheKey, 0)) + 1;
        if ($cachedAttempts > 5) {
            Cache::forget($cacheKey);
            $request->session()->forget('auth_wa_otp_challenge');

            return back()->withErrors(['otp' => 'Terlalu banyak percobaan OTP. Minta OTP baru.']);
        }

        $attempts = (int) ($challenge['attempts'] ?? 0) + 1;
        if ($attempts > 5) {
            $request->session()->forget('auth_wa_otp_challenge');
            return back()->withErrors(['otp' => 'Batas percobaan OTP terlampaui. Minta OTP baru.']);
        }
        $isLocalTestingOtp = app()->isLocal() && $validated['otp'] === '123456';

        if (! $isLocalTestingOtp && (empty($challenge['otp_hash']) || ! Hash::check($validated['otp'], $challenge['otp_hash']))) {
            Cache::put($cacheKey, $cachedAttempts, now()->addMinutes(10));
            $challenge['attempts'] = $attempts;
            $request->session()->put('auth_wa_otp_challenge', $challenge);
            return back()->withErrors(['otp' => 'Kode OTP salah. Sisa percobaan: ' . (5 - $attempts) . '.']);
        }

        Cache::forget($cacheKey);
        $request->session()->forget('auth_wa_otp_challenge');
        $request->session()->regenerate();
        $request->session()->put('auth_wa_otp_verified_user_id', $user->id);
        $request->session()->put('auth_wa_otp_verified_at', now()->timestamp);

        // Verifikasi seumur hidup di database hingga ganti nomor HP
        $user->update(['phone_verified_at' => now()]);

        $targetPhone = $this->normalizePhone((string) ($user->phone ?: $user->activeBusiness?->phone)) ?: (string) ($challenge['phone'] ?? '');
        if ($targetPhone !== '') {
            $trustedDevice->trustDevice($user, $targetPhone);
        }

        $intended = (string) $request->session()->get('url.intended', '');
        if ($intended !== '' && (str_contains($intended, '/auth/otp') || str_contains($intended, '/login'))) {
            $request->session()->forget('url.intended');
        }

        return redirect()->intended(route('dashboard'))->with('success', 'Verifikasi WhatsApp berhasil.');
    }

    public function resend(Request $request, AdminWhatsAppService $adminWa): RedirectResponse
    {
        $challenge = $request->session()->get('auth_wa_otp_challenge');
        $user = auth('web')->user();

        if (! $user->isBusinessOwner() || $user->isPhoneVerified()) {
            return redirect()->route('dashboard');
        }

        if (! is_array($challenge) || ($challenge['user_id'] ?? null) !== $user->id) {
            return redirect()->route('auth.otp')->withErrors(['otp' => 'Sesi OTP tidak ditemukan.']);
        }
        if ((int) ($challenge['last_sent_at'] ?? 0) > now()->subSeconds(60)->timestamp) {
            return back()->withErrors(['otp' => 'Tunggu 60 detik sebelum meminta OTP baru.']);
        }

        $userPhone = $this->normalizePhone((string) ($user->phone ?: $user->activeBusiness?->phone));
        $targetPhone = $userPhone ?: (string) ($challenge['phone'] ?? '');

        $otp = (string) random_int(100000, 999999);
        $result = $adminWa->sendOtp($targetPhone, $otp);
        $sent = (bool) ($result['success'] ?? false);

        if (! $sent && ! app()->isLocal()) {
            $challenge['phone'] = $targetPhone;
            $challenge['delivery_error'] = 'OTP gagal dikirim. Coba lagi.';
            $request->session()->put('auth_wa_otp_challenge', $challenge);

            return back()->withErrors(['otp' => 'OTP gagal dikirim. Coba lagi.']);
        }

        if (! $sent && app()->isLocal()) {
            \Illuminate\Support\Facades\Log::info("[DEV-OTP] Resend WhatsApp server offline. Local OTP for {$targetPhone} ({$user->email}): {$otp} (or master testing OTP: 123456)");
        }

        $challenge['phone'] = $targetPhone;
        $challenge['otp_hash'] = Hash::make($otp);
        $challenge['expires_at'] = now()->addMinutes(10)->timestamp;
        $challenge['attempts'] = 0;
        $challenge['last_sent_at'] = now()->timestamp;
        $challenge['delivery_error'] = null;
        Cache::forget('otp_attempts:auth:' . $user->id);
        $request->session()->put('auth_wa_otp_challenge', $challenge);

        return back()->with('status', 'OTP baru telah dikirim ke WhatsApp Anda.');
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
            : ($phone !== '' ? $phone : 'nomor WhatsApp owner');
    }
}
