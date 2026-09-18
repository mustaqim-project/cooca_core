<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Commerce;

use App\Domain\Shipping\BiteshipService;
use App\Http\Controllers\Controller;
use App\Models\CommerceShippingRule;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

final class MerchantShippingRuleController extends Controller
{
    private BiteshipService $biteshipService;

    public function __construct(?BiteshipService $biteshipService = null)
    {
        $this->biteshipService = $biteshipService ?? new BiteshipService();
    }

    /**
     * Display merchant shipping settings and Biteship logistics hub.
     */
    public function index(): View
    {
        $business = Context::business();
        abort_unless($business, 404);
        abort_unless(Context::hasPermission('storefront.shipping.manage'), 403);

        $rules = CommerceShippingRule::where('business_id', $business->id)
            ->ordered()
            ->get();

        $storeSetting = $business->commerceStoreSetting ?? $business->storeSetting;

        $isBiteshipConfigured = $this->biteshipService->isConfigured();
        $biteshipApiKey = $this->biteshipService->getApiKey();
        $availableCouriers = $this->biteshipService->getDefaultCouriers();
        $enabledCouriers = $storeSetting?->biteship_enabled_couriers ?? ['jne', 'jnt', 'sicepat', 'anteraja', 'gosend', 'grab'];

        return view('app.storefront.shipping.index', compact(
            'business',
            'rules',
            'storeSetting',
            'isBiteshipConfigured',
            'biteshipApiKey',
            'availableCouriers',
            'enabledCouriers'
        ));
    }

    /**
     * Save store origin address and enabled couriers for Biteship.
     */
    public function saveOrigin(Request $request): RedirectResponse
    {
        $business = Context::business();
        abort_unless($business, 404);
        abort_unless(Context::hasPermission('storefront.shipping.manage'), 403);

        $validated = $request->validate([
            'origin_contact_name'       => ['required', 'string', 'max:150'],
            'origin_contact_phone'      => ['required', 'string', 'max:30'],
            'origin_address'            => ['required', 'string', 'max:500'],
            'origin_postal_code'        => ['required', 'string', 'max:10'],
            'origin_latitude'           => ['nullable', 'numeric', 'between:-90,90'],
            'origin_longitude'          => ['nullable', 'numeric', 'between:-180,180'],
            'origin_area_id'            => ['nullable', 'string', 'max:100'],
            'biteship_enabled_couriers' => ['nullable', 'array'],
            'biteship_enabled_couriers.*' => ['string'],
        ], [
            'origin_contact_name.required'  => 'Nama PIC pengirim toko wajib diisi.',
            'origin_contact_phone.required' => 'Nomor WhatsApp / telepon toko wajib diisi.',
            'origin_address.required'       => 'Alamat penjemputan / lokasi toko wajib diisi.',
            'origin_postal_code.required'   => 'Kode pos lokasi toko wajib diisi untuk perhitungan tarif kurir.',
        ]);

        $storeSetting = $business->commerceStoreSetting ?? $business->storeSetting;
        if (! $storeSetting) {
            $storeSetting = \App\Models\CommerceStoreSetting::create([
                'business_id' => $business->id,
            ]);
        }

        $couriers = $validated['biteship_enabled_couriers'] ?? ['jne', 'jnt', 'sicepat', 'anteraja', 'gosend', 'grab'];

        // Synchronize store origin with Biteship Locations API (POST /v1/locations)
        $locationPayload = [
            'name'          => $business->name . ' - Toko Utama',
            'contact_name'  => $validated['origin_contact_name'],
            'contact_phone' => $validated['origin_contact_phone'],
            'address'       => $validated['origin_address'],
            'note'          => 'Lokasi asal penjemputan paket toko ' . $business->name,
            'postal_code'   => (int) $validated['origin_postal_code'],
            'latitude'      => isset($validated['origin_latitude']) ? (float) $validated['origin_latitude'] : null,
            'longitude'     => isset($validated['origin_longitude']) ? (float) $validated['origin_longitude'] : null,
            'type'          => 'origin',
        ];

        $originLocationId = $storeSetting->origin_location_id;
        if (! empty($originLocationId)) {
            $locResult = $this->biteshipService->updateLocation($originLocationId, $locationPayload);
        } else {
            $locResult = $this->biteshipService->createLocation($locationPayload);
            if (! empty($locResult['id'])) {
                $originLocationId = $locResult['id'];
            }
        }

        $storeSetting->update([
            'origin_contact_name'       => $validated['origin_contact_name'],
            'origin_contact_phone'      => $validated['origin_contact_phone'],
            'origin_address'            => $validated['origin_address'],
            'origin_postal_code'        => $validated['origin_postal_code'],
            'origin_latitude'           => isset($validated['origin_latitude']) ? (float) $validated['origin_latitude'] : null,
            'origin_longitude'          => isset($validated['origin_longitude']) ? (float) $validated['origin_longitude'] : null,
            'origin_area_id'            => $validated['origin_area_id'] ?? null,
            'origin_location_id'        => $originLocationId,
            'biteship_enabled_couriers' => $couriers,
        ]);

        return redirect()->route('storefront.shipping.index')->with('success', 'Konfigurasi alamat asal toko dan lokasi resmi Biteship (' . ($originLocationId ?: 'Tersinkronisasi') . ') berhasil disimpan.');
    }

    /**
     * Test live rate calculation directly from merchant dashboard.
     */
    public function testRate(Request $request): JsonResponse
    {
        $business = Context::business();
        abort_unless($business, 404);
        abort_unless(Context::hasPermission('storefront.shipping.manage'), 403);

        $validated = $request->validate([
            'destination_postal_code' => ['required', 'string', 'max:10'],
            'weight_grams'            => ['nullable', 'integer', 'min:10'],
            'item_value'              => ['nullable', 'numeric', 'min:1000'],
        ]);

        $storeSetting = $business->commerceStoreSetting ?? $business->storeSetting;

        if (empty($storeSetting?->origin_postal_code)) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan lengkapi dan simpan Kode Pos Asal Toko terlebih dahulu sebelum melakukan uji coba tarif.',
            ], 422);
        }

        $origin = [
            'postal_code' => $storeSetting->origin_postal_code,
            'latitude'    => $storeSetting->origin_latitude,
            'longitude'   => $storeSetting->origin_longitude,
            'area_id'     => $storeSetting->origin_area_id,
            'address'     => $storeSetting->origin_address,
            'location_id' => $storeSetting->origin_location_id,
        ];

        $destination = [
            'postal_code' => $validated['destination_postal_code'],
        ];

        $items = [
            [
                'name'     => 'Uji Coba Paket',
                'value'    => (float) ($validated['item_value'] ?? 50000),
                'weight'   => (int) ($validated['weight_grams'] ?? 250),
                'quantity' => 1,
            ],
        ];

        $couriers = $storeSetting->biteship_enabled_couriers ?? ['jne', 'sicepat', 'jnt', 'anteraja', 'gosend', 'grab'];

        $res = $this->biteshipService->getRates($origin, $destination, $items, $couriers);

        return response()->json($res);
    }

    /**
     * Search areas or postal codes for autocomplete.
     */
    public function searchAreas(Request $request): JsonResponse
    {
        $query = (string) $request->query('query', '');
        $res = $this->biteshipService->searchAreas($query);

        return response()->json($res);
    }

    /**
     * Store a new shipping calculation rule (legacy compatibility).
     */
    public function store(Request $request): RedirectResponse
    {
        $business = Context::business();
        abort_unless($business, 404);
        abort_unless(Context::hasPermission('storefront.shipping.manage'), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'rule_type' => ['required', 'string', 'in:flat,distance_tier,free_threshold'],
            'rate_amount' => ['required', 'numeric', 'min:0'],
            'min_distance_km' => ['nullable', 'numeric', 'min:0'],
            'max_distance_km' => ['nullable', 'numeric', 'min:0'],
            'min_order_for_free' => ['nullable', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        CommerceShippingRule::create([
            'business_id' => $business->id,
            'name' => $validated['name'],
            'rule_type' => $validated['rule_type'],
            'rate_amount' => (float) $validated['rate_amount'],
            'min_distance_km' => isset($validated['min_distance_km']) ? (float) $validated['min_distance_km'] : null,
            'max_distance_km' => isset($validated['max_distance_km']) ? (float) $validated['max_distance_km'] : null,
            'min_order_for_free' => isset($validated['min_order_for_free']) ? (float) $validated['min_order_for_free'] : null,
            'is_active' => true,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return back()->with('success', "Aturan ongkir '{$validated['name']}' berhasil ditambahkan.");
    }

    /**
     * Update an existing shipping calculation rule (legacy compatibility).
     */
    public function update(Request $request, CommerceShippingRule $shippingRule): RedirectResponse
    {
        $business = Context::business();
        abort_unless($business && $shippingRule->business_id === $business->id, 403);
        abort_unless(Context::hasPermission('storefront.shipping.manage'), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'rule_type' => ['required', 'string', 'in:flat,distance_tier,free_threshold'],
            'rate_amount' => ['required', 'numeric', 'min:0'],
            'min_distance_km' => ['nullable', 'numeric', 'min:0'],
            'max_distance_km' => ['nullable', 'numeric', 'min:0'],
            'min_order_for_free' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $shippingRule->update([
            'name' => $validated['name'],
            'rule_type' => $validated['rule_type'],
            'rate_amount' => (float) $validated['rate_amount'],
            'min_distance_km' => isset($validated['min_distance_km']) ? (float) $validated['min_distance_km'] : null,
            'max_distance_km' => isset($validated['max_distance_km']) ? (float) $validated['max_distance_km'] : null,
            'min_order_for_free' => isset($validated['min_order_for_free']) ? (float) $validated['min_order_for_free'] : null,
            'is_active' => isset($validated['is_active']) ? (bool) $validated['is_active'] : $shippingRule->is_active,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return back()->with('success', "Aturan ongkir '{$shippingRule->name}' berhasil diperbarui.");
    }

    /**
     * Toggle active state of a shipping rule (legacy compatibility).
     */
    public function toggle(CommerceShippingRule $shippingRule): RedirectResponse
    {
        $business = Context::business();
        abort_unless($business && $shippingRule->business_id === $business->id, 403);
        abort_unless(Context::hasPermission('storefront.shipping.manage'), 403);

        $shippingRule->update(['is_active' => ! $shippingRule->is_active]);

        $statusLabel = $shippingRule->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Aturan ongkir '{$shippingRule->name}' berhasil {$statusLabel}.");
    }

    /**
     * Delete a shipping rule (legacy compatibility).
     */
    public function destroy(CommerceShippingRule $shippingRule): RedirectResponse
    {
        $business = Context::business();
        abort_unless($business && $shippingRule->business_id === $business->id, 403);
        abort_unless(Context::hasPermission('storefront.shipping.manage'), 403);

        $name = $shippingRule->name;
        $shippingRule->delete();

        return back()->with('success', "Aturan ongkir '{$name}' telah dihapus.");
    }
}
