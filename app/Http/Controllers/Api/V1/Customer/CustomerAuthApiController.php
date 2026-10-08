<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Customer;

use App\Domain\WhatsApp\AdminWhatsAppService;
use App\Http\Controllers\Controller;
use App\Models\GlobalCustomer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

final class CustomerAuthApiController extends Controller
{
    public function __construct(
        private readonly ?AdminWhatsAppService $wa = null
    ) {}

    /**
     * Register new customer account.
     * POST /api/v1/customer/auth/register
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'email' => ['nullable', 'email', 'max:150', 'unique:global_customers,email'],
            'phone' => ['required', 'string', 'min:8', 'max:30'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $phone = $this->normalizePhone($validated['phone']);

        $existing = GlobalCustomer::where('phone', $phone)->first();
        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor telepon ini sudah terdaftar. Silakan login.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $customer = GlobalCustomer::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $phone,
            'password' => Hash::make($validated['password']),
        ]);

        $token = $customer->createToken('customer_mobile_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran akun berhasil.',
            'customer' => $customer,
            'token' => $token,
        ], Response::HTTP_CREATED);
    }

    /**
     * Login customer account via email or phone.
     * POST /api/v1/customer/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string'], // email or phone
            'password' => ['required', 'string'],
        ]);

        $login = $validated['login'];
        $phoneNormalized = $this->normalizePhone($login);

        $customer = GlobalCustomer::where(function ($q) use ($login, $phoneNormalized): void {
            $q->where('email', $login)
                ->orWhere('phone', $login);
            if ($phoneNormalized) {
                $q->orWhere('phone', $phoneNormalized);
            }
        })->first();

        if (! $customer || ! $customer->password || ! Hash::check($validated['password'], $customer->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email/nomor telepon atau kata sandi tidak cocok.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $token = $customer->createToken('customer_mobile_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'customer' => $customer,
            'token' => $token,
        ], Response::HTTP_OK);
    }

    /**
     * Google SSO Login / Token Exchange.
     * POST /api/v1/customer/auth/google
     */
    public function googleLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'google_id' => ['required', 'string'],
            'email' => ['required', 'email', 'max:150'],
            'name' => ['required', 'string', 'max:150'],
            'avatar_url' => ['nullable', 'string', 'url'],
        ]);

        $customer = GlobalCustomer::where('google_id', $validated['google_id'])
            ->orWhere('email', $validated['email'])
            ->first();

        if ($customer) {
            $customer->update([
                'google_id' => $validated['google_id'],
                'avatar_url' => $validated['avatar_url'] ?? $customer->avatar_url,
                'email_verified_at' => $customer->email_verified_at ?? now(),
            ]);
        } else {
            $customer = GlobalCustomer::create([
                'google_id' => $validated['google_id'],
                'email' => $validated['email'],
                'name' => $validated['name'],
                'avatar_url' => $validated['avatar_url'] ?? null,
                'email_verified_at' => now(),
            ]);
        }

        $token = $customer->createToken('customer_mobile_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login Google berhasil.',
            'customer' => $customer,
            'token' => $token,
        ], Response::HTTP_OK);
    }

    /**
     * Send WhatsApp OTP for customer phone verification.
     * POST /api/v1/customer/auth/otp/send
     */
    public function sendOtp(Request $request): JsonResponse
    {
        /** @var GlobalCustomer $customer */
        $customer = $request->user();
        if (! $customer instanceof GlobalCustomer) {
            return response()->json(['success' => false, 'message' => 'Unauthorized customer.'], Response::HTTP_UNAUTHORIZED);
        }

        $phoneInput = $request->input('phone') ?? $customer->phone;
        if (empty($phoneInput)) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor WhatsApp belum terdaftar.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $phone = $this->normalizePhone((string) $phoneInput);
        $otp = (string) random_int(100000, 999999);

        $waService = $this->wa ?? app(AdminWhatsAppService::class);
        $waResult = $waService->sendOtp($phone, $otp);
        $isSent = (bool) ($waResult['success'] ?? false);
        $isLocal = app()->environment('local', 'testing');

        Cache::put("customer_otp_{$customer->id}", [
            'phone' => $phone,
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
        ], now()->addMinutes(10));

        return response()->json([
            'success' => true,
            'message' => ($isSent || $isLocal)
                ? 'Kode OTP telah dikirimkan ke nomor WhatsApp Anda.'
                : 'Sistem pengiriman OTP sedang sibuk. Silakan coba sesaat lagi.',
            'phone_masked' => substr($phone, 0, 4) . '••••' . substr($phone, -4),
            'expires_in_seconds' => 600,
        ], Response::HTTP_OK);
    }

    /**
     * Verify WhatsApp OTP code.
     * POST /api/v1/customer/auth/otp/verify
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        /** @var GlobalCustomer $customer */
        $customer = $request->user();
        if (! $customer instanceof GlobalCustomer) {
            return response()->json(['success' => false, 'message' => 'Unauthorized customer.'], Response::HTTP_UNAUTHORIZED);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $cached = Cache::get("customer_otp_{$customer->id}");
        if (! $cached) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP telah kadaluarsa atau belum diminta. Silakan minta kode baru.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! Hash::check($validated['code'], $cached['otp_hash'])) {
            $cached['attempts'] = ($cached['attempts'] ?? 0) + 1;
            Cache::put("customer_otp_{$customer->id}", $cached, now()->addMinutes(5));

            return response()->json([
                'success' => false,
                'message' => 'Kode OTP tidak sesuai. Pastikan 6-digit kode sudah benar.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $customer->update([
            'phone' => $cached['phone'] ?? $customer->phone,
            'phone_verified_at' => now(),
        ]);

        Cache::forget("customer_otp_{$customer->id}");

        return response()->json([
            'success' => true,
            'message' => 'Nomor WhatsApp berhasil diverifikasi seumur hidup.',
            'customer' => $customer->refresh(),
        ], Response::HTTP_OK);
    }

    /**
     * Get authenticated customer profile.
     * GET /api/v1/customer/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $customer = $request->user();

        return response()->json([
            'success' => true,
            'customer' => $customer,
        ], Response::HTTP_OK);
    }

    /**
     * Logout and revoke current token.
     * POST /api/v1/customer/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user !== null && method_exists($user, 'currentAccessToken') && $user->currentAccessToken() !== null) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ], Response::HTTP_OK);
    }

    private function normalizePhone(string $phone): string
    {
        $clean = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($clean, '0')) {
            return '62' . substr($clean, 1);
        }
        if (str_starts_with($clean, '8')) {
            return '62' . $clean;
        }

        return $clean;
    }
}
