<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Device;

use App\Http\Controllers\Controller;
use App\Models\GlobalCustomer;
use App\Models\MobileDeviceToken;
use App\Models\User;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class MobileDeviceTokenApiController extends Controller
{
    /**
     * Register or update FCM device token for B2B user (Owner / Staff).
     * POST /api/v1/devices/fcm-token
     */
    public function registerB2bToken(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $business = Context::business();

        $validated = $request->validate([
            'token' => ['required', 'string', 'max:500'],
            'platform' => ['required', 'string', 'in:android,ios,web'],
            'device_model' => ['nullable', 'string', 'max:100'],
            'app_version' => ['nullable', 'string', 'max:20'],
        ]);

        $deviceToken = MobileDeviceToken::updateOrCreate(
            [
                'token' => $validated['token'],
                'app_type' => 'cooca_my_own',
            ],
            [
                'user_id' => $user->id,
                'business_id' => $business?->id,
                'platform' => $validated['platform'],
                'device_model' => $validated['device_model'] ?? null,
                'app_version' => $validated['app_version'] ?? null,
                'last_seen_at' => now(),
                'is_active' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Token notifikasi perangkat B2B berhasil didaftarkan.',
            'data' => [
                'id' => $deviceToken->id,
                'platform' => $deviceToken->platform,
                'app_type' => $deviceToken->app_type,
                'is_active' => $deviceToken->is_active,
                'registered_at' => $deviceToken->updated_at?->toIso8601String(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Unregister FCM device token on B2B logout.
     * DELETE /api/v1/devices/fcm-token
     */
    public function unregisterB2bToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:500'],
        ]);

        MobileDeviceToken::where('token', $validated['token'])
            ->where('app_type', 'cooca_my_own')
            ->update([
                'is_active' => false,
                'last_seen_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Token notifikasi perangkat B2B berhasil dinonaktifkan.',
        ], Response::HTTP_OK);
    }

    /**
     * Register or update FCM device token for B2C Customer.
     * POST /api/v1/customer/devices/fcm-token
     */
    public function registerCustomerToken(Request $request): JsonResponse
    {
        /** @var GlobalCustomer $customer */
        $customer = $request->user();

        $validated = $request->validate([
            'token' => ['required', 'string', 'max:500'],
            'platform' => ['required', 'string', 'in:android,ios,web'],
            'device_model' => ['nullable', 'string', 'max:100'],
            'app_version' => ['nullable', 'string', 'max:20'],
        ]);

        $deviceToken = MobileDeviceToken::updateOrCreate(
            [
                'token' => $validated['token'],
                'app_type' => 'cooca_customer',
            ],
            [
                'global_customer_id' => $customer->id,
                'platform' => $validated['platform'],
                'device_model' => $validated['device_model'] ?? null,
                'app_version' => $validated['app_version'] ?? null,
                'last_seen_at' => now(),
                'is_active' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Token notifikasi perangkat pembeli berhasil didaftarkan.',
            'data' => [
                'id' => $deviceToken->id,
                'platform' => $deviceToken->platform,
                'app_type' => $deviceToken->app_type,
                'is_active' => $deviceToken->is_active,
                'registered_at' => $deviceToken->updated_at?->toIso8601String(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Unregister FCM device token on B2C customer logout.
     * DELETE /api/v1/customer/devices/fcm-token
     */
    public function unregisterCustomerToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:500'],
        ]);

        MobileDeviceToken::where('token', $validated['token'])
            ->where('app_type', 'cooca_customer')
            ->update([
                'is_active' => false,
                'last_seen_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Token notifikasi perangkat pembeli berhasil dinonaktifkan.',
        ], Response::HTTP_OK);
    }
}
