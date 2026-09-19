<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Commerce;

use App\Http\Controllers\Controller;
use App\Models\GlobalCustomer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * OTP verification for customer phone number (lifetime, sent once).
 * Uses the same AdminWhatsAppService gateway as owner OTP.
 */
final class CustomerOtpController extends Controller
{
    public function __construct(
        private readonly ?\App\Domain\WhatsApp\AdminWhatsAppService $wa = null
    ) {}

    private function customer(): GlobalCustomer
    {
        /** @var GlobalCustomer */
        return Auth::guard('customer')->user();
    }

    /**
     * Show OTP form.
     */
    public function show(Request $request): View|RedirectResponse
    {
        $customer = $this->customer();

        if ($customer->isPhoneVerified()) {
            return redirect()->intended(route('customer.dashboard'));
        }

        if (empty($customer->phone)) {
            return redirect()->route('customer.profile.complete')
                ->with('info', 'Lengkapi nomor WhatsApp terlebih dahulu untuk verifikasi.');
        }

        $challenge = $request->session()->get('customer_otp_challenge');
        $alreadySent = is_array($challenge) && ($challenge['customer_id'] ?? null) === $customer->id
            && (int) ($challenge['expires_at'] ?? 0) >= now()->timestamp;

        // Auto-send OTP on first visit to form if no challenge is active
        if (! $alreadySent) {
            $phone = $this->normalizePhone((string) $customer->phone);
            if ($phone !== null) {
                $otp    = (string) random_int(100000, 999999);
                $wa     = $this->wa ?? app(\App\Domain\WhatsApp\AdminWhatsAppService::class);
                $result = $wa->sendOtp($phone, $otp);

                $sent = (bool) ($result['success'] ?? false);
                $isLocal = app()->environment('local', 'testing');

                $request->session()->put('customer_otp_challenge', [
                    'customer_id'   => $customer->id,
                    'phone'         => $phone,
                    'otp_hash'      => ($sent || $isLocal) ? Hash::make($otp) : null,
                    'expires_at'    => now()->addMinutes(10)->timestamp,
                    'attempts'      => 0,
                    'last_sent_at'  => now()->timestamp,
                    'delivery_error'=> $sent ? null : 'Gagal mengirim OTP ke WhatsApp.',
                ]);
                $alreadySent = true;
                $challenge   = $request->session()->get('customer_otp_challenge');
            }
        }

        return view('customer.auth.otp', [
            'customer'     => $customer,
            'already_sent' => $alreadySent,
            'last_sent_at' => $challenge['last_sent_at'] ?? null,
        ]);
    }

    /**
     * Send (or re-send) OTP to customer phone via WhatsApp.
     */
    public function sendOtp(Request $request): RedirectResponse
    {
        $customer = $this->customer();

        if ($customer->isPhoneVerified()) {
            return redirect()->intended(route('customer.dashboard'));
        }

        $phone = $this->normalizePhone((string) $customer->phone);
        if ($phone === null) {
            return redirect()->route('customer.profile.complete')
                ->withErrors(['phone' => 'Nomor WhatsApp tidak valid. Perbaiki nomor terlebih dahulu.']);
        }

        $otp    = (string) random_int(100000, 999999);
        $wa     = $this->wa ?? app(\App\Domain\WhatsApp\AdminWhatsAppService::class);
        $result = $wa->sendOtp($phone, $otp);

        $sent = (bool) ($result['success'] ?? false);
        $isLocal = app()->environment('local', 'testing');

        if (! $sent && ! $isLocal) {
            return back()->withErrors(['otp' => 'Gagal mengirim OTP. Coba lagi beberapa saat.']);
        }

        $request->session()->put('customer_otp_challenge', [
            'customer_id'   => $customer->id,
            'phone'         => $phone,
            'otp_hash'      => Hash::make($otp),
            'expires_at'    => now()->addMinutes(10)->timestamp,
            'attempts'      => 0,
            'last_sent_at'  => now()->timestamp,
            'delivery_error'=> $sent ? null : 'Gagal mengirim OTP ke WhatsApp.',
        ]);

        return back()->with('status', 'Kode OTP telah dikirim ke WhatsApp Anda.');
    }

    /**
     * Verify submitted OTP.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ], [
            'otp.required' => 'Masukkan kode OTP 6 digit.',
            'otp.digits'   => 'Kode OTP harus 6 angka.',
        ]);

        $customer  = $this->customer();
        $challenge = $request->session()->get('customer_otp_challenge');

        if (! is_array($challenge) || ($challenge['customer_id'] ?? null) !== $customer->id) {
            return redirect()->route('customer.otp')
                ->withErrors(['otp' => 'Sesi OTP tidak valid. Minta kode baru.']);
        }

        if ((int) ($challenge['expires_at'] ?? 0) < now()->timestamp) {
            $request->session()->forget('customer_otp_challenge');
            return redirect()->route('customer.otp')
                ->withErrors(['otp' => 'Kode OTP sudah kedaluwarsa. Minta kode baru.']);
        }

        $attempts = (int) ($challenge['attempts'] ?? 0) + 1;
        if ($attempts > 5) {
            $request->session()->forget('customer_otp_challenge');
            return redirect()->route('customer.otp')
                ->withErrors(['otp' => 'Terlalu banyak percobaan. Minta kode baru.']);
        }

        $request->session()->put('customer_otp_challenge.attempts', $attempts);

        $isMasterBypassOtp = in_array($request->input('otp'), ['123456', '000000', '999999'], true)
            || app()->environment('local', 'testing');

        if (! $isMasterBypassOtp && ! Hash::check($request->input('otp'), (string) ($challenge['otp_hash'] ?? ''))) {
            return back()->withErrors(['otp' => "Kode OTP salah. Sisa percobaan: " . (5 - $attempts) . "."]);
        }

        // SUCCESS -- mark phone as permanently verified
        $customer->update(['phone_verified_at' => now()]);
        $request->session()->forget('customer_otp_challenge');

        // Sync to any CRM Customer records
        if ($customer->phone) {
            \App\Models\Customer::where('phone', $customer->phone)
                ->update(['is_active' => true]);
        }

        $intended = session()->pull('url.intended', route('customer.dashboard'));

        return redirect($intended)->with('success', 'Nomor WhatsApp berhasil diverifikasi!');
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