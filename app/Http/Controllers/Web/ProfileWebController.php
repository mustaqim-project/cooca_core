<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Mail\DynamicMailConfig;
use App\Domain\WhatsApp\AdminWhatsAppService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

final class ProfileWebController extends Controller
{
    /**
     * Show user profile and password management page.
     */
    public function edit(): View
    {
        $user = Context::user();
        $business = Context::business();

        return view('app.profile.edit', compact('user', 'business'));
    }

    /**
     * Update user profile details.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Context::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,webp,jpg', 'max:4096'],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);

        $trackingService = app(\App\Domain\Storage\StorageTrackingService::class);
        $avatarPath = $user->avatar;

        if ($request->boolean('remove_avatar') && $avatarPath) {
            if (! str_starts_with($avatarPath, 'http')) {
                $trackingService->deleteFile($avatarPath, 'public');
            }
            $avatarPath = null;
        } elseif ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $trackingService->assertCanUpload($user, (int) $file->getSize(), 'avatar');
            $oldAvatar = $avatarPath;
            $avatarPath = $file->store('avatars/' . $user->id, 'public');
            $trackingService->recordUpload(
                file: $file,
                filePath: $avatarPath,
                category: \App\Models\StorageFile::CATEGORY_OWNER_AVATAR,
                module: 'profile',
                owner: $user,
                uploader: $user
            );
            if ($oldAvatar && ! str_starts_with($oldAvatar, 'http')) {
                $trackingService->deleteFile($oldAvatar, 'public');
            }
        }

        $user->update([
            'name' => $validated['name'],
            'avatar' => $avatarPath,
        ]);

        return back()->with('success', 'Profil akun Anda berhasil diperbarui.');
    }

    /**
     * Start a verified WhatsApp number change for the business owner.
     */
    public function requestPhoneChange(Request $request, AdminWhatsAppService $adminWa): RedirectResponse
    {
        abort_unless(Context::isOwner(), 403, 'Hanya Owner bisnis yang dapat mengubah nomor WhatsApp.');

        /** @var User $user */
        $user = Context::user();
        $validated = $request->validate([
            'phone' => ['required', 'string', 'min:9', 'max:30'],
        ], [
            'phone.required' => 'Nomor WhatsApp baru wajib diisi.',
            'phone.min' => 'Nomor WhatsApp minimal 9 digit.',
        ]);

        $phone = $this->normalizePhone($validated['phone']);
        if ($phone === null) {
            return back()->withErrors(['phone' => 'Nomor WhatsApp tidak valid. Gunakan format 081234567890 atau 628123456789.'])->withInput();
        }

        if ($phone === $user->phone) {
            return back()->withErrors(['phone' => 'Nomor WhatsApp baru sama dengan nomor WhatsApp saat ini.'])->withInput();
        }

        $otp = (string) random_int(100000, 999999);
        $result = $adminWa->sendMessage($phone, "Kode OTP perubahan nomor WhatsApp Cooca Anda adalah *{$otp}*. Kode ini berlaku 10 menit. Jangan bagikan kode ini kepada siapa pun.");

        if (! ($result['success'] ?? false)) {
            return back()->withErrors(['phone' => 'OTP gagal dikirim. Coba lagi.'])->withInput();
        }

        $request->session()->put('profile_phone_change', [
            'user_id' => $user->id,
            'phone' => $phone,
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
            'last_sent_at' => now()->timestamp,
        ]);

        return redirect()->route('profile.contact.verify')->with('status', 'Kode OTP telah dikirim ke nomor WhatsApp baru (' . $phone . ').');
    }

    /**
     * Update user email address and trigger verification email link.
     */
    public function updateEmail(Request $request): RedirectResponse
    {
        abort_unless(Context::isOwner(), 403, 'Hanya Owner bisnis yang dapat mengubah email akun.');

        /** @var User $user */
        $user = Context::user();

        $rules = [
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ];
        if (! $user->google_id && $user->password) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $validated = $request->validate($rules, [
            'email.required' => 'Alamat email baru wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Alamat email tersebut sudah digunakan oleh akun lain.',
            'current_password.required' => 'Kata sandi saat ini wajib diisi untuk verifikasi keamanan.',
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
        ]);

        $newEmail = strtolower(trim($validated['email']));
        if ($newEmail === strtolower(trim($user->email))) {
            return back()->with('info', 'Alamat email baru sama dengan alamat email saat ini.');
        }

        $user->forceFill([
            'email' => $newEmail,
            'email_verified_at' => null,
        ])->save();

        DynamicMailConfig::bootstrap();
        $user->sendEmailVerificationNotification();

        return redirect()->route('profile.edit')->with('success', "Alamat email berhasil diubah ke {$newEmail}. Tautan verifikasi telah dikirim ke kotak masuk email baru Anda.");
    }

    /**
     * Legacy support: Start a verified email and WhatsApp number change for the business owner.
     */
    public function requestContactChange(Request $request, AdminWhatsAppService $adminWa): RedirectResponse
    {
        abort_unless(Context::isOwner(), 403, 'Hanya Owner bisnis yang dapat mengubah email dan nomor WhatsApp.');

        /** @var User $user */
        $user = Context::user();
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['required', 'string', 'min:9', 'max:30'],
        ]);
        $phone = $this->normalizePhone($validated['phone']);

        if ($phone === null) {
            return back()->withErrors(['phone' => 'Nomor WhatsApp tidak valid. Gunakan format 081234567890 atau 628123456789.'])->withInput();
        }

        $otp = (string) random_int(100000, 999999);
        $result = $adminWa->sendMessage($phone, "Kode OTP perubahan kontak Cooca Anda adalah *{$otp}*. Kode ini berlaku 10 menit. Jangan bagikan kode ini kepada siapa pun.");

        if (! ($result['success'] ?? false)) {
            return back()->withErrors(['phone' => 'OTP gagal dikirim. Coba lagi.'])->withInput();
        }

        $request->session()->put('profile_contact_change', [
            'user_id' => $user->id,
            'email' => $validated['email'],
            'phone' => $phone,
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
            'last_sent_at' => now()->timestamp,
        ]);

        return redirect()->route('profile.contact.verify')->with('status', 'OTP telah dikirim ke nomor WhatsApp baru.');
    }

    /**
     * Verify the WhatsApp OTP and update contact details.
     */
    public function verifyContactChange(Request $request): RedirectResponse
    {
        abort_unless(Context::isOwner(), 403, 'Hanya Owner bisnis yang dapat mengubah nomor WhatsApp.');

        $validated = $request->validate(['otp' => ['required', 'digits:6']]);
        $pending = $request->session()->get('profile_phone_change') ?: $request->session()->get('profile_contact_change');
        $user = Context::user();

        if (! is_array($pending) || ($pending['user_id'] ?? null) !== $user->id) {
            return redirect()->route('profile.edit')->withErrors(['otp' => 'Sesi OTP tidak ditemukan. Silakan minta OTP baru.']);
        }
        if ((int) ($pending['expires_at'] ?? 0) < now()->timestamp) {
            $request->session()->forget('profile_phone_change');
            $request->session()->forget('profile_contact_change');
            return back()->withErrors(['otp' => 'Kode OTP sudah kedaluwarsa.']);
        }

        // Defense-in-depth: batas percobaan global per akun (tidak dapat di-reset via session baru).
        $cacheKey = 'otp_attempts:contact:' . $user->id;
        $cachedAttempts = ((int) Cache::get($cacheKey, 0)) + 1;
        if ($cachedAttempts > 5) {
            Cache::forget($cacheKey);
            $request->session()->forget('profile_phone_change');
            $request->session()->forget('profile_contact_change');
            return back()->withErrors(['otp' => 'Terlalu banyak percobaan OTP. Minta OTP baru.']);
        }

        $attempts = (int) ($pending['attempts'] ?? 0) + 1;
        if ($attempts > 5) {
            $request->session()->forget('profile_phone_change');
            $request->session()->forget('profile_contact_change');
            return back()->withErrors(['otp' => 'Batas percobaan OTP terlampaui. Minta OTP baru.']);
        }
        if (! Hash::check($validated['otp'], (string) ($pending['otp_hash'] ?? ''))) {
            Cache::put($cacheKey, $cachedAttempts, now()->addMinutes(10));
            $pending['attempts'] = $attempts;
            $sessionKey = $request->session()->has('profile_phone_change') ? 'profile_phone_change' : 'profile_contact_change';
            $request->session()->put($sessionKey, $pending);
            return back()->withErrors(['otp' => 'Kode OTP salah. Sisa percobaan: ' . (5 - $attempts) . '.']);
        }

        Cache::forget($cacheKey);

        $oldPhone = $user->phone;
        $updates = [
            'phone' => $pending['phone'],
        ];

        // Jika sesi legacy mencakup pembaruan email
        if (! empty($pending['email']) && $pending['email'] !== $user->email) {
            $updates['email'] = $pending['email'];
            $updates['email_verified_at'] = null;
        }

        $user->forceFill($updates)->save();

        if ($user->activeBusiness && (empty($user->activeBusiness->phone) || $user->activeBusiness->phone === $oldPhone)) {
            $user->activeBusiness->update(['phone' => $pending['phone']]);
        }

        $request->session()->forget('profile_phone_change');
        $request->session()->forget('profile_contact_change');
        $request->session()->regenerate();

        if (! empty($updates['email'])) {
            DynamicMailConfig::bootstrap();
            $user->sendEmailVerificationNotification();
        }

        return redirect()->route('profile.edit')->with('success', 'Nomor WhatsApp akun Anda berhasil diperbarui.');
    }

    /**
     * Resend the pending phone-change OTP.
     */
    public function resendContactChange(Request $request, AdminWhatsAppService $adminWa): RedirectResponse
    {
        abort_unless(Context::isOwner(), 403, 'Hanya Owner bisnis yang dapat mengubah nomor WhatsApp.');

        $pending = $request->session()->get('profile_phone_change') ?: $request->session()->get('profile_contact_change');
        $user = Context::user();
        if (! is_array($pending) || ($pending['user_id'] ?? null) !== $user->id) {
            return redirect()->route('profile.edit')->withErrors(['otp' => 'Sesi OTP tidak ditemukan.']);
        }
        if ((int) ($pending['last_sent_at'] ?? 0) > now()->subSeconds(60)->timestamp) {
            return back()->withErrors(['otp' => 'Tunggu 60 detik sebelum meminta OTP baru.']);
        }

        $otp = (string) random_int(100000, 999999);
        $result = $adminWa->sendMessage((string) $pending['phone'], "Kode OTP perubahan nomor WhatsApp Cooca Anda adalah *{$otp}*. Kode ini berlaku 10 menit. Jangan bagikan kode ini kepada siapa pun.");
        if (! ($result['success'] ?? false)) {
            return back()->withErrors(['otp' => 'OTP gagal dikirim. Coba lagi.']);
        }

        $pending['otp_hash'] = Hash::make($otp);
        $pending['expires_at'] = now()->addMinutes(10)->timestamp;
        $pending['attempts'] = 0;
        $pending['last_sent_at'] = now()->timestamp;
        Cache::forget('otp_attempts:contact:' . $user->id);
        $sessionKey = $request->session()->has('profile_phone_change') ? 'profile_phone_change' : 'profile_contact_change';
        $request->session()->put($sessionKey, $pending);

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

    /**
     * Update user password (Ganti Password).
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Context::user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', PasswordRule::default()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Kata sandi berhasil diubah.');
    }
}
