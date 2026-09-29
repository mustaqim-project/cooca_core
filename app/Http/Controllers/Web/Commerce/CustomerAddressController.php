<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Commerce;

use App\Http\Controllers\Controller;
use App\Models\GlobalCustomer;
use App\Models\GlobalCustomerAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class CustomerAddressController extends Controller
{
    private function customer(): GlobalCustomer
    {
        /** @var GlobalCustomer $customer */
        $customer = auth('customer')->user();
        return $customer;
    }

    /**
     * List all saved addresses for the authenticated customer.
     */
    public function index(Request $request): JsonResponse
    {
        $addresses = $this->customer()
            ->addresses()
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success'   => true,
            'addresses' => $addresses,
        ]);
    }

    /**
     * Store a new customer address with Geolocation and Biteship Area data.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $customer = $this->customer();

        $validated = $request->validate([
            'label'            => ['required', 'string', 'max:50'],
            'recipient_name'   => ['required', 'string', 'min:2', 'max:150'],
            'recipient_phone'  => ['required', 'string', 'min:8', 'max:30'],
            'full_address'     => ['required', 'string', 'min:5', 'max:1000'],
            'village'          => ['nullable', 'string', 'max:100'],
            'district'         => ['nullable', 'string', 'max:100'],
            'city'             => ['nullable', 'string', 'max:100'],
            'province'         => ['nullable', 'string', 'max:100'],
            'postal_code'      => ['nullable', 'string', 'max:10'],
            'biteship_area_id' => ['nullable', 'string', 'max:100'],
            'latitude'         => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'        => ['nullable', 'numeric', 'between:-180,180'],
            'notes'            => ['nullable', 'string', 'max:500'],
            'is_default'       => ['nullable', 'boolean'],
        ]);

        $hasExisting = $customer->addresses()->count() > 0;
        $isDefault = ! $hasExisting || filter_var($request->input('is_default', false), FILTER_VALIDATE_BOOLEAN);

        $address = DB::transaction(function () use ($customer, $validated, $isDefault): GlobalCustomerAddress {
            if ($isDefault) {
                $customer->addresses()->update(['is_default' => false]);
            }

            /** @var GlobalCustomerAddress $addr */
            $addr = $customer->addresses()->create([
                'label'            => $validated['label'],
                'recipient_name'   => $validated['recipient_name'],
                'recipient_phone'  => $validated['recipient_phone'],
                'full_address'     => $validated['full_address'],
                'village'          => $validated['village'] ?? null,
                'district'         => $validated['district'] ?? null,
                'city'             => $validated['city'] ?? null,
                'province'         => $validated['province'] ?? null,
                'postal_code'      => $validated['postal_code'] ?? null,
                'biteship_area_id' => $validated['biteship_area_id'] ?? null,
                'latitude'         => isset($validated['latitude']) ? (float) $validated['latitude'] : null,
                'longitude'        => isset($validated['longitude']) ? (float) $validated['longitude'] : null,
                'notes'            => $validated['notes'] ?? null,
                'is_default'       => $isDefault,
            ]);

            if ($isDefault) {
                $customer->update(['shipping_address' => $addr->formatted_address]);
            }

            return $addr;
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Alamat baru berhasil ditambahkan.',
                'address' => $address,
            ]);
        }

        return back()->with('success', 'Alamat pengiriman baru berhasil disimpan.');
    }

    /**
     * Update an existing address.
     */
    public function update(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $customer = $this->customer();
        /** @var GlobalCustomerAddress $address */
        $address = $customer->addresses()->findOrFail($id);

        $validated = $request->validate([
            'label'            => ['required', 'string', 'max:50'],
            'recipient_name'   => ['required', 'string', 'min:2', 'max:150'],
            'recipient_phone'  => ['required', 'string', 'min:8', 'max:30'],
            'full_address'     => ['required', 'string', 'min:5', 'max:1000'],
            'village'          => ['nullable', 'string', 'max:100'],
            'district'         => ['nullable', 'string', 'max:100'],
            'city'             => ['nullable', 'string', 'max:100'],
            'province'         => ['nullable', 'string', 'max:100'],
            'postal_code'      => ['nullable', 'string', 'max:10'],
            'biteship_area_id' => ['nullable', 'string', 'max:100'],
            'latitude'         => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'        => ['nullable', 'numeric', 'between:-180,180'],
            'notes'            => ['nullable', 'string', 'max:500'],
            'is_default'       => ['nullable', 'boolean'],
        ]);

        $isDefault = filter_var($request->input('is_default', $address->is_default), FILTER_VALIDATE_BOOLEAN);

        DB::transaction(function () use ($customer, $address, $validated, $isDefault): void {
            if ($isDefault && ! $address->is_default) {
                $customer->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            }

            $address->update([
                'label'            => $validated['label'],
                'recipient_name'   => $validated['recipient_name'],
                'recipient_phone'  => $validated['recipient_phone'],
                'full_address'     => $validated['full_address'],
                'village'          => $validated['village'] ?? null,
                'district'         => $validated['district'] ?? null,
                'city'             => $validated['city'] ?? null,
                'province'         => $validated['province'] ?? null,
                'postal_code'      => $validated['postal_code'] ?? null,
                'biteship_area_id' => $validated['biteship_area_id'] ?? null,
                'latitude'         => isset($validated['latitude']) ? (float) $validated['latitude'] : null,
                'longitude'        => isset($validated['longitude']) ? (float) $validated['longitude'] : null,
                'notes'            => $validated['notes'] ?? null,
                'is_default'       => $isDefault,
            ]);

            if ($isDefault) {
                $customer->update(['shipping_address' => $address->formatted_address]);
            }
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Alamat berhasil diperbarui.',
                'address' => $address->fresh(),
            ]);
        }

        return back()->with('success', 'Alamat berhasil diperbarui.');
    }

    /**
     * Set a specific address as primary default.
     */
    public function setDefault(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $customer = $this->customer();
        /** @var GlobalCustomerAddress $address */
        $address = $customer->addresses()->findOrFail($id);

        $address->markAsDefault();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Alamat '{$address->label}' dijadikan sebagai alamat utama.",
                'address' => $address,
            ]);
        }

        return back()->with('success', "Alamat '{$address->label}' berhasil dijadikan alamat utama.");
    }

    /**
     * Delete an address.
     */
    public function destroy(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $customer = $this->customer();
        /** @var GlobalCustomerAddress $address */
        $address = $customer->addresses()->findOrFail($id);
        $wasDefault = $address->is_default;

        $address->delete();

        // If the deleted address was default, promote the first remaining address
        if ($wasDefault) {
            $next = $customer->addresses()->first();
            if ($next) {
                $next->markAsDefault();
            } else {
                $customer->update(['shipping_address' => null]);
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Alamat berhasil dihapus.',
            ]);
        }

        return back()->with('success', 'Alamat berhasil dihapus.');
    }
}
