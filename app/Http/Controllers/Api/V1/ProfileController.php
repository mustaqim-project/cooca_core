<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\Response;

final class ProfileController extends Controller
{
    /**
     * Get authenticated user profile + businesses.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        $businesses = $user->businesses()
            ->with(['subscription'])
            ->get()
            ->map(fn($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'logo_url' => $b->logo_url,
                'currency' => $b->currency ?? 'IDR',
                'subscription_plan' => $b->subscription?->plan,
                'subscription_status' => $b->subscription?->status,
            ]);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? null,
                'avatar_url' => $user->avatar_url ?? null,
            ],
            'businesses' => $businesses,
        ], Response::HTTP_OK);
    }

    /**
     * Update profile info.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? null,
                'avatar_url' => $user->avatar_url ?? null,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if (!Hash::check((string) $request->input('current_password'), $user->password)) {
            return response()->json([
                'message' => 'Kata sandi lama tidak sesuai.',
                'errors' => ['current_password' => ['Kata sandi lama tidak sesuai.']],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->update(['password' => Hash::make((string) $request->input('password'))]);

        return response()->json([
            'message' => 'Kata sandi berhasil diubah.',
        ], Response::HTTP_OK);
    }

    /**
     * Get business profile.
     */
    public function businessProfile(Request $request, Business $business): JsonResponse
    {
        $user = $request->user();

        // Ensure user belongs to business
        if (!$user->businesses()->where('businesses.id', $business->id)->exists()) {
            return response()->json(['message' => 'Akses ditolak.'], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'business' => $business->append(['logo_url', 'currency_symbol']),
        ], Response::HTTP_OK);
    }

    /**
     * Update business profile.
     */
    public function updateBusinessProfile(Request $request, Business $business): JsonResponse
    {
        $user = $request->user();

        if (!$user->businesses()->where('businesses.id', $business->id)->exists()) {
            return response()->json(['message' => 'Akses ditolak.'], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'tax_identification_number' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
            'currency' => ['nullable', 'string', 'in:IDR,USD,EUR,SGD,MYR,JPY'],
            'pos_receipt_footer_note' => ['nullable', 'string', 'max:300'],
        ]);

        $business->update($validated);

        return response()->json([
            'message' => 'Profil bisnis berhasil diperbarui.',
            'business' => $business->fresh()->append(['logo_url', 'currency_symbol']),
        ], Response::HTTP_OK);
    }

    /**
     * Logout current device token.
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var \Laravel\Sanctum\PersonalAccessToken $token */
        $token = $request->user()->currentAccessToken();
        $token->delete();

        return response()->json([
            'message' => 'Berhasil keluar.',
        ], Response::HTTP_OK);
    }

    /**
     * Logout from all devices.
     */
    public function logoutAllDevices(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Berhasil keluar dari semua perangkat.',
        ], Response::HTTP_OK);
    }
}
