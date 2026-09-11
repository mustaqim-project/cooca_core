<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\WhatsApp\AdminWhatsAppService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

final class AuthOtpController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if (! auth('web')->check()) {
            return redirect()->route('login');
        }

        $challenge = $request->session()->get('auth_wa_otp_challenge', []);
        $user = auth('web')->user();

        return view('auth.otp', [
            'phone' => $this->maskPhone((string) ($challenge['phone'] ?? $user->phone ?? '')),
            'expiresAt' => (int) ($challenge['expires_at'] ?? 0),
            'deliveryError' => $challenge['delivery_error'] ?? null,
        ]);
    }

    public function verify(Request $request): RedirectResponse
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

        $attempts = (int) ($challenge['attempts'] ?? 0) + 1;
        if ($attempts > 5) {
            $request->session()->forget('auth_wa_otp_challenge');
            return back()->withErrors(['otp' => 'Batas percobaan OTP terlampaui. Minta OTP baru.']);
        }
        if (empty($challenge['otp_hash']) || ! Hash::check($validated['otp'], $challenge['otp_hash'])) {
            $challenge['attempts'] = $attempts;
            $request->session()->put('auth_wa_otp_challenge', $challenge);
            return back()->withErrors(['otp' => 'Kode OTP salah. Sisa percobaan: ' . (5 - $attempts) . '.']);
        }

        $request->session()->forget('auth_wa_otp_challenge');
        $request->session()->put('auth_wa_otp_verified_user_id', $user->id);
        $request->session()->put('auth_wa_otp_verified_at', now()->timestamp);

        return redirect()->intended(route('dashboard'))->with('success', 'Verifikasi WhatsApp berhasil.');
    }

    public function resend(Request $request, AdminWhatsAppService $adminWa): RedirectResponse
    {
        $challenge = $request->session()->get('auth_wa_otp_challenge');
        $user = auth('web')->user();
        if (! is_array($challenge) || ($challenge['user_id'] ?? null) !== $user->id) {
            return redirect()->route('auth.otp')->withErrors(['otp' => 'Sesi OTP tidak ditemukan.']);
        }
        if ((int) ($challenge['last_sent_at'] ?? 0) > now()->subSeconds(60)->timestamp) {
            return back()->withErrors(['otp' => 'Tunggu 60 detik sebelum meminta OTP baru.']);
        }

        $otp = (string) random_int(100000, 999999);
        $result = $adminWa->sendMessage((string) $challenge['phone'], "Kode OTP keamanan login Cooca Anda adalah *{$otp}*. Kode ini berlaku 10 menit. Jangan bagikan kode ini kepada siapa pun.");
        if (! ($result['success'] ?? false)) {
            return back()->withErrors(['otp' => 'OTP gagal dikirim. Pastikan WhatsApp Admin Cooca sedang terhubung.']);
        }

        $challenge['otp_hash'] = Hash::make($otp);
        $challenge['expires_at'] = now()->addMinutes(10)->timestamp;
        $challenge['attempts'] = 0;
        $challenge['last_sent_at'] = now()->timestamp;
        $challenge['delivery_error'] = null;
        $request->session()->put('auth_wa_otp_challenge', $challenge);

        return back()->with('status', 'OTP baru telah dikirim ke WhatsApp Anda.');
    }

    private function maskPhone(string $phone): string
    {
        return strlen($phone) > 6
            ? substr($phone, 0, 4) . str_repeat('*', max(2, strlen($phone) - 7)) . substr($phone, -3)
            : ($phone !== '' ? $phone : 'nomor WhatsApp owner');
    }
}
