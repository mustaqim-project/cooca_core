<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\WhatsApp\AdminWhatsAppService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

/**
 * Require that the GlobalCustomer has verified their phone number via WhatsApp OTP.
 * Verification is permanent (phone_verified_at stored on the model) so this check
 * is essentially free after the first time.
 *
 * If not verified, sends OTP automatically and redirects to the OTP page.
 */
final class RequireCustomerOtp
{
    public function __construct(
        private readonly ?AdminWhatsAppService $wa = null
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $customer = auth('customer')->user();

        if ($customer === null) {
            return $next($request);
        }

        // Already permanently verified
        if ($customer->isPhoneVerified()) {
            return $next($request);
        }

        // Phone missing -- profile gate should have caught this, but be safe
        if (empty($customer->phone)) {
            if (! $request->session()->has('url.intended')) {
                $request->session()->put('url.intended', $request->header('Referer') ?: $request->fullUrl());
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success'          => false,
                    'requires_profile' => true,
                    'redirect_url'     => route('customer.profile.complete'),
                    'message'          => 'Lengkapi nomor WhatsApp Anda terlebih dahulu.',
                ], 403);
            }

            return redirect()->route('customer.profile.complete')
                ->with('info', 'Lengkapi nomor WhatsApp Anda terlebih dahulu.');
        }

        // Normalise phone for sending
        $phone = $this->normalizePhone((string) $customer->phone);

        // Save intended destination
        if (! $request->session()->has('url.intended')) {
            $request->session()->put('url.intended', $request->header('Referer') ?: $request->fullUrl());
        }

        // Auto-send OTP if no active challenge yet
        $challenge = $request->session()->get('customer_otp_challenge');
        $hasActive = is_array($challenge)
            && ($challenge['customer_id'] ?? null) === $customer->id
            && (int) ($challenge['expires_at'] ?? 0) >= now()->timestamp;

        if (! $hasActive && $phone !== null) {
            $otp    = (string) random_int(100000, 999999);
            $wa     = $this->wa ?? app(AdminWhatsAppService::class);
            $result = $wa->sendMessage(
                $phone,
                "Kode verifikasi WhatsApp COOCA Anda adalah *{$otp}*. Berlaku 10 menit. Jangan bagikan kode ini."
            );

            $sent = (bool) ($result['success'] ?? false);
            $isLocal = app()->environment('local', 'testing');

            $request->session()->put('customer_otp_challenge', [
                'customer_id'   => $customer->id,
                'phone'         => $phone,
                'otp_hash'      => ($sent || $isLocal) ? Hash::make($otp) : null,
                'expires_at'    => now()->addMinutes(10)->timestamp,
                'attempts'      => 0,
                'last_sent_at'  => now()->timestamp,
                'delivery_error'=> $sent ? null : 'OTP gagal dikirim.',
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success'      => false,
                'requires_otp' => true,
                'redirect_url' => route('customer.otp'),
                'message'      => 'Verifikasi nomor WhatsApp terlebih dahulu via OTP.',
            ], 403);
        }

        return redirect()->route('customer.otp');
    }

    private function normalizePhone(string $phone): ?string
    {
        $phone = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (str_starts_with($phone, '8')) {
            $phone = '62' . $phone;
        }
        return preg_match('/^62[1-9][0-9]{7,13}$/', $phone) ? $phone : null;
    }
}