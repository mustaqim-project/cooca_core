<?php

declare(strict_types=1);

namespace App\Domain\WhatsApp;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

final class WhatsAppTrustedDeviceService
{
    public const COOKIE_NAME = 'cooca_wa_trusted_device';
    public const DEFAULT_EXPIRY_DAYS = 60;

    /**
     * Check if the current browser/device is recognized as a trusted device for the user and phone.
     */
    public function isTrusted(Request $request, User $user, string $phone): bool
    {
        $raw = $request->cookie(self::COOKIE_NAME);
        if (! $raw || ! is_string($raw)) {
            return false;
        }

        $data = json_decode($raw, true);
        if (! is_array($data)) {
            return false;
        }

        if (($data['user_id'] ?? null) !== $user->id) {
            return false;
        }

        if (! hash_equals((string) ($data['phone_hash'] ?? ''), sha1($phone))) {
            return false;
        }

        $verifiedAt = (int) ($data['verified_at'] ?? 0);
        $maxAge = ($data['expiry_days'] ?? self::DEFAULT_EXPIRY_DAYS) * 86400;

        return $verifiedAt > 0 && (time() - $verifiedAt) <= $maxAge;
    }

    /**
     * Issue a secure long-lived trusted device cookie.
     */
    public function trustDevice(User $user, string $phone, int $days = self::DEFAULT_EXPIRY_DAYS): void
    {
        $payload = json_encode([
            'user_id' => $user->id,
            'phone_hash' => sha1($phone),
            'verified_at' => now()->timestamp,
            'expiry_days' => $days,
        ]);

        Cookie::queue(Cookie::make(
            self::COOKIE_NAME,
            $payload,
            60 * 24 * $days,
            null,
            null,
            null,
            true // HttpOnly
        ));
    }

    /**
     * Revoke trusted device status.
     */
    public function forgetDevice(): void
    {
        Cookie::queue(Cookie::forget(self::COOKIE_NAME));
    }
}
