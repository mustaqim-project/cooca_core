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
     * Start a verified email and WhatsApp number change for the business owner.
     */
    public function requestContactChange(Request $request, AdminWhatsAppService $adminWa): RedirectResponse
    {
        abort_unless(Context::isOwner(), 403, 'Hanya Owner bisnis yang dapat mengubah email dan nomor WhatsApp.');

        /** @var User $user */
        $user = Context::user();
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['required', 'string', 'min:10', 'max:30'],
        ]);
        $phone = $this->normalizePhone($validated['phone']);

        if ($phone === null) {
            return back()->withErrors(['phone' => 'Nomor WhatsApp tidak valid. Gunakan format 081234567890 atau 628123456789.'])->withInput();
        }

        $otp = (string) random_int(100000, 999999);
        $result = $adminWa->sendMessage($phone, "Kode OTP perubahan kontak Cooca Anda adalah *{$otp}*. Kode ini berlaku 10 menit. Jangan bagikan kode ini kepada siapa pun.");

        if (! ($result['success'] ?? false)) {
            return back()->withErrors(['phone' => 'OTP gagal dikirim. Pastikan WhatsApp Admin Cooca sedang terhubung.'])->withInput();
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
     * Verify the WhatsApp OTP and send an email verification link to the new address.
     */
    public function verifyContactChange(Request $request): RedirectResponse
    {
        abort_unless(Context::isOwner(), 403, 'Hanya Owner bisnis yang dapat mengubah email dan nomor WhatsApp.');

        $validated = $request->validate(['otp' => ['required', 'digits:6']]);
        $pending = $request->session()->get('profile_contact_change');
        $user = Context::user();

        if (! is_array($pending) || ($pending['user_id'] ?? null) !== $user->id) {
            return redirect()->route('profile.edit')->withErrors(['otp' => 'Sesi OTP tidak ditemukan. Silakan minta OTP baru.']);
        }
        if ((int) ($pending['expires_at'] ?? 0) < now()->timestamp) {
            $request->session()->forget('profile_contact_change');
            return back()->withErrors(['otp' => 'Kode OTP sudah kedaluwarsa.']);
        }

        $attempts = (int) ($pending['attempts'] ?? 0) + 1;
        if ($attempts > 5) {
            $request->session()->forget('profile_contact_change');
            return back()->withErrors(['otp' => 'Batas percobaan OTP terlampaui. Minta OTP baru.']);
        }
        if (! Hash::check($validated['otp'], (string) ($pending['otp_hash'] ?? ''))) {
            $pending['attempts'] = $attempts;
            $request->session()->put('profile_contact_change', $pending);
            return back()->withErrors(['otp' => 'Kode OTP salah. Sisa percobaan: ' . (5 - $attempts) . '.']);
        }

        $user->forceFill([
            'email' => $pending['email'],
            'phone' => $pending['phone'],
            'email_verified_at' => null,
        ])->save();
        $request->session()->forget('profile_contact_change');

        DynamicMailConfig::bootstrap();
        $user->sendEmailVerificationNotification();

        return redirect()->route('profile.edit')->with('success', 'Email dan nomor WhatsApp berhasil diperbarui. Tautan verifikasi telah dikirim ke email baru.');
    }

    /**
     * Resend the pending contact-change OTP.
     */
    public function resendContactChange(Request $request, AdminWhatsAppService $adminWa): RedirectResponse
    {
        abort_unless(Context::isOwner(), 403, 'Hanya Owner bisnis yang dapat mengubah email dan nomor WhatsApp.');

        $pending = $request->session()->get('profile_contact_change');
        $user = Context::user();
        if (! is_array($pending) || ($pending['user_id'] ?? null) !== $user->id) {
            return redirect()->route('profile.edit')->withErrors(['otp' => 'Sesi OTP tidak ditemukan.']);
        }
        if ((int) ($pending['last_sent_at'] ?? 0) > now()->subSeconds(60)->timestamp) {
            return back()->withErrors(['otp' => 'Tunggu 60 detik sebelum meminta OTP baru.']);
        }

        $otp = (string) random_int(100000, 999999);
        $result = $adminWa->sendMessage((string) $pending['phone'], "Kode OTP perubahan kontak Cooca Anda adalah *{$otp}*. Kode ini berlaku 10 menit. Jangan bagikan kode ini kepada siapa pun.");
        if (! ($result['success'] ?? false)) {
            return back()->withErrors(['otp' => 'OTP gagal dikirim. Pastikan WhatsApp Admin Cooca sedang terhubung.']);
        }

        $pending['otp_hash'] = Hash::make($otp);
        $pending['expires_at'] = now()->addMinutes(10)->timestamp;
        $pending['attempts'] = 0;
        $pending['last_sent_at'] = now()->timestamp;
        $request->session()->put('profile_contact_change', $pending);

        return back()->with('status', 'OTP baru telah dikirim ke WhatsApp Anda.');
    }

    private function normalizePhone(string $phone): ?string
    {
        $phone = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($phone, '0')) {
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
