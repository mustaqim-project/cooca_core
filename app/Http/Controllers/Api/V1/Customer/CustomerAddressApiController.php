<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\GlobalCustomer;
use App\Models\GlobalCustomerAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CustomerAddressApiController extends Controller
{
    private function customer(Request $request): GlobalCustomer
    {
        /** @var GlobalCustomer $user */
        $user = $request->user();
        return $user;
    }

    /**
     * List saved delivery addresses.
     * GET /api/v1/customer/addresses
     */
    public function index(Request $request): JsonResponse
    {
        $addresses = $this->customer($request)
            ->addresses()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $addresses,
        ], Response::HTTP_OK);
    }

    /**
     * Store new delivery address.
     * POST /api/v1/customer/addresses
     */
    public function store(Request $request): JsonResponse
    {
        $customer = $this->customer($request);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'min:2', 'max:150'],
            'recipient_phone' => ['required', 'string', 'min:8', 'max:30'],
            'full_address' => ['required', 'string', 'min:5', 'max:1000'],
            'village' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'biteship_area_id' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_default' => ['nullable', 'boolean'],
            'delivery_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $hasAddresses = $customer->addresses()->exists();
        $isDefault = $validated['is_default'] ?? (! $hasAddresses);

        if ($isDefault) {
            $customer->addresses()->update(['is_default' => false]);
        }

        $address = $customer->addresses()->create(array_merge($validated, [
            'is_default' => $isDefault,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Alamat pengiriman berhasil disimpan.',
            'data' => $address,
        ], Response::HTTP_CREATED);
    }

    /**
     * Update delivery address.
     * PUT /api/v1/customer/addresses/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $customer = $this->customer($request);
        $address = $customer->addresses()->where('id', $id)->first();

        if (! $address) {
            return response()->json(['success' => false, 'message' => 'Alamat tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'label' => ['sometimes', 'required', 'string', 'max:50'],
            'recipient_name' => ['sometimes', 'required', 'string', 'min:2', 'max:150'],
            'recipient_phone' => ['sometimes', 'required', 'string', 'min:8', 'max:30'],
            'full_address' => ['sometimes', 'required', 'string', 'min:5', 'max:1000'],
            'village' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'biteship_area_id' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_default' => ['nullable', 'boolean'],
            'delivery_notes' => ['nullable', 'string', 'max:500'],
        ]);

        if (! empty($validated['is_default'])) {
            $customer->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Alamat pengiriman berhasil diperbarui.',
            'data' => $address->refresh(),
        ], Response::HTTP_OK);
    }

    /**
     * Set default delivery address.
     * POST /api/v1/customer/addresses/{id}/default
     */
    public function setDefault(Request $request, string $id): JsonResponse
    {
        $customer = $this->customer($request);
        $address = $customer->addresses()->where('id', $id)->first();

        if (! $address) {
            return response()->json(['success' => false, 'message' => 'Alamat tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $customer->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Alamat utama berhasil diatur.',
            'data' => $address->refresh(),
        ], Response::HTTP_OK);
    }

    /**
     * Delete delivery address.
     * DELETE /api/v1/customer/addresses/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $customer = $this->customer($request);
        $address = $customer->addresses()->where('id', $id)->first();

        if (! $address) {
            return response()->json(['success' => false, 'message' => 'Alamat tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $next = $customer->addresses()->first();
            $next?->update(['is_default' => true]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Alamat pengiriman berhasil dihapus.',
        ], Response::HTTP_OK);
    }
}
