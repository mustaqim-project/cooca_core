<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Warehouse;

use App\Domain\Billing\EntitlementService;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\CommerceStoreSetting;
use App\Models\GoodsReceipt;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\StockTransfer;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class WarehouseWebController extends Controller
{
    public function __construct(
        private readonly EntitlementService $entitlementService = new EntitlementService
    ) {}
    /**
     * Warehouse Management Hub - semua gudang/lokasi dalam satu dashboard.
     */
    public function index(): View
    {
        $business = Context::requireBusiness();

        $locations = Location::where('business_id', $business->id)
            ->with(['parent', 'children'])
            ->withCount(['stocks as total_products' => function ($q) {
                $q->where('quantity', '>', 0);
            }])
            ->get()
            ->map(function (Location $loc) use ($business) {
                $loc->total_valuation = InventoryStock::where('business_id', $business->id)
                    ->where('location_id', $loc->id)
                    ->selectRaw('SUM(quantity * last_cost) as val')
                    ->value('val') ?? 0.0;

                $loc->low_stock_count = InventoryStock::where('business_id', $business->id)
                    ->where('location_id', $loc->id)
                    ->whereHas('product', function ($q) {
                        $q->whereRaw('inventory_stocks.quantity <= products.min_stock AND products.min_stock > 0');
                    })
                    ->count();

                $loc->last_receipt = GoodsReceipt::where('business_id', $business->id)
                    ->where('location_id', $loc->id)
                    ->latest('receipt_date')
                    ->value('receipt_date');

                return $loc;
            });

        // Global KPIs
        $totalValuation  = $locations->sum('total_valuation');
        $totalLowStock   = $locations->sum('low_stock_count');
        $totalWarehouses = $locations->count();
        $activeWarehouses = $locations->where('is_active', true)->count();
        $totalSkuCount = InventoryStock::where('business_id', $business->id)->where('quantity', '>', 0)->distinct('product_id')->count('product_id');
        $totalStockUnits = (float) (InventoryStock::where('business_id', $business->id)->sum('quantity') ?? 0);

        // Recent movements (last 10, all warehouses)
        $recentMovements = StockMovement::where('business_id', $business->id)
            ->with(['product', 'location'])
            ->latest()
            ->limit(8)
            ->get();

        $storeSetting = \App\Models\CommerceStoreSetting::where('business_id', $business->id)->first();

        $parentOutlets = Location::where('business_id', $business->id)
            ->whereNull('parent_id')
            ->whereIn('type', ['outlet', 'store', 'central_kitchen'])
            ->orderBy('name')
            ->get();

        return view('app.warehouse.index', compact(
            'business',
            'locations',
            'parentOutlets',
            'storeSetting',
            'totalValuation',
            'totalLowStock',
            'totalWarehouses',
            'activeWarehouses',
            'totalSkuCount',
            'totalStockUnits',
            'recentMovements'
        ));
    }

    /**
     * Buat gudang/lokasi baru.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'parent_id'              => ['nullable', 'uuid', Rule::exists('locations', 'id')->where('business_id', $business->id)],
            'name'                   => ['required', 'string', 'max:100'],
            'type'                   => ['required', 'string', 'in:outlet,warehouse,central_kitchen'],
            'code'                   => ['nullable', 'string', 'max:50'],
            'phone'                  => ['nullable', 'string', 'max:50'],
            'address'                => ['nullable', 'string', 'max:500'],
            'province'               => ['nullable', 'string', 'max:100'],
            'city'                   => ['nullable', 'string', 'max:100'],
            'district'               => ['nullable', 'string', 'max:100'],
            'village'                => ['nullable', 'string', 'max:100'],
            'postal_code'            => ['nullable', 'string', 'max:20'],
            'biteship_area_id'       => ['nullable', 'string', 'max:100'],
            'latitude'               => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'              => ['nullable', 'numeric', 'between:-180,180'],
            'geofence_radius'        => ['nullable', 'integer', 'min:10', 'max:10000'],
            'geofence_radius_meters' => ['nullable', 'integer', 'min:10', 'max:10000'],
            'is_primary'             => ['nullable', 'boolean'],
        ]);

        $locationType = $validated['type'];
        if (! $this->entitlementService->canCreateLocation($business, $locationType)) {
            $label = in_array($locationType, ['outlet', 'store'], true) ? __('warehouse.types.outlet') : ($locationType === 'central_kitchen' ? __('warehouse.types.central_kitchen') : __('warehouse.types.warehouse'));
            $quotaMsg = __('warehouse.messages.quota_exceeded', ['type' => $label]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $quotaMsg,
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', $quotaMsg);
        }

        // Generate unique slug
        $baseSlug = Str::slug($validated['name']);
        $slug     = $baseSlug;
        $counter  = 1;
        while (Location::where('business_id', $business->id)->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $geofenceRadius = $validated['geofence_radius_meters'] ?? $validated['geofence_radius'] ?? 100;

        $isPrimary = $request->boolean('is_primary');
        if (!Location::where('business_id', $business->id)->exists()) {
            $isPrimary = true;
        }

        if ($isPrimary) {
            Location::where('business_id', $business->id)->update(['is_primary' => false]);
        }

        $location = Location::create([
            'business_id'             => $business->id,
            'parent_id'               => ! empty($validated['parent_id']) ? $validated['parent_id'] : null,
            'name'                    => $validated['name'],
            'slug'                    => $slug,
            'type'                    => $validated['type'],
            'code'                    => $validated['code'] ?? null,
            'phone'                   => $validated['phone'] ?? null,
            'address'                 => $validated['address'] ?? null,
            'province'                => $validated['province'] ?? null,
            'city'                    => $validated['city'] ?? null,
            'district'                => $validated['district'] ?? null,
            'village'                 => $validated['village'] ?? null,
            'postal_code'             => $validated['postal_code'] ?? null,
            'biteship_area_id'        => $validated['biteship_area_id'] ?? null,
            'latitude'                => $validated['latitude'] ?? null,
            'longitude'               => $validated['longitude'] ?? null,
            'geofence_radius_meters'  => (int) $geofenceRadius,
            'is_online_fulfillment'   => $request->boolean('is_online_fulfillment', true),
            'allow_storefront_pickup' => $request->boolean('allow_storefront_pickup', true),
            'is_primary'              => $isPrimary,
            'is_active'               => true,
        ]);

        if ($isPrimary) {
            $storeSetting = CommerceStoreSetting::firstOrCreate(['business_id' => $business->id]);
            $storeSetting->update([
                'origin_location_id' => $location->id,
                'origin_area_id'     => $location->biteship_area_id ?? $storeSetting->origin_area_id,
                'origin_address'     => $location->address ?? $storeSetting->origin_address,
                'origin_postal_code' => $location->postal_code ?? $storeSetting->origin_postal_code,
                'origin_latitude'    => $location->latitude ?? $storeSetting->origin_latitude,
                'origin_longitude'   => $location->longitude ?? $storeSetting->origin_longitude,
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('warehouse.messages.created_success'),
                'location' => $location,
            ], 201);
        }

        return redirect()->route('warehouse.index')
            ->with('success', __('warehouse.messages.created_success'));
    }

    /**
     * Detail gudang - stok produk, riwayat penerimaan, dan aktivitas terkini.
     */
    public function show(Location $location): View
    {
        $business = Context::requireBusiness();
        abort_unless($location->business_id === $business->id, 403);

        // Stok produk di gudang ini
        $stocks = InventoryStock::where('business_id', $business->id)
            ->where('location_id', $location->id)
            ->with(['product.outputUnit', 'product.category'])
            ->orderByDesc('quantity')
            ->paginate(20)
            ->withQueryString();

        // Total aset di gudang ini
        $totalValuation = InventoryStock::where('business_id', $business->id)
            ->where('location_id', $location->id)
            ->selectRaw('SUM(quantity * last_cost) as val')
            ->value('val') ?? 0.0;

        $lowStockCount = InventoryStock::where('business_id', $business->id)
            ->where('location_id', $location->id)
            ->whereHas('product', function ($q) {
                $q->whereRaw('inventory_stocks.quantity <= products.min_stock AND products.min_stock > 0');
            })
            ->count();

        // Riwayat penerimaan barang (Goods Receipts) di gudang ini
        $receipts = GoodsReceipt::where('business_id', $business->id)
            ->where('location_id', $location->id)
            ->with(['supplier', 'purchaseOrder', 'receiver', 'items.product'])
            ->latest('receipt_date')
            ->limit(10)
            ->get();

        // Riwayat mutasi stok terkini
        $recentMovements = StockMovement::where('business_id', $business->id)
            ->where('location_id', $location->id)
            ->with(['product.outputUnit', 'creator'])
            ->latest()
            ->limit(15)
            ->get();

        // Semua gudang lain (untuk transfer)
        $otherLocations = Location::where('business_id', $business->id)
            ->where('id', '!=', $location->id)
            ->where('is_active', true)
            ->get();

        // Pengajuan penyesuaian stok bernilai tinggi yang menunggu approval (Maker-Checker)
        $pendingAdjustments = StockAdjustment::where('business_id', $business->id)
            ->where('location_id', $location->id)
            ->where('status', 'pending_approval')
            ->with(['creator', 'items.product.outputUnit'])
            ->latest()
            ->get();

        return view('app.warehouse.show', compact(
            'business',
            'location',
            'stocks',
            'totalValuation',
            'lowStockCount',
            'receipts',
            'recentMovements',
            'otherLocations',
            'pendingAdjustments'
        ));
    }

    /**
     * Update data gudang/lokasi.
     */
    public function update(Request $request, Location $location): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless($location->business_id === $business->id, 403);

        $validated = $request->validate([
            'parent_id'              => ['nullable', 'uuid', Rule::exists('locations', 'id')->where('business_id', $business->id)],
            'name'                   => ['required', 'string', 'max:100'],
            'type'                   => ['required', 'string', 'in:outlet,warehouse,central_kitchen'],
            'code'                   => ['nullable', 'string', 'max:50'],
            'phone'                  => ['nullable', 'string', 'max:50'],
            'address'                => ['nullable', 'string', 'max:500'],
            'province'               => ['nullable', 'string', 'max:100'],
            'city'                   => ['nullable', 'string', 'max:100'],
            'district'               => ['nullable', 'string', 'max:100'],
            'village'                => ['nullable', 'string', 'max:100'],
            'postal_code'            => ['nullable', 'string', 'max:20'],
            'biteship_area_id'       => ['nullable', 'string', 'max:100'],
            'latitude'               => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'              => ['nullable', 'numeric', 'between:-180,180'],
            'geofence_radius'        => ['nullable', 'integer', 'min:10', 'max:10000'],
            'geofence_radius_meters' => ['nullable', 'integer', 'min:10', 'max:10000'],
            'is_active'              => ['boolean'],
            'is_primary'             => ['nullable', 'boolean'],
        ]);

        $parentId = ! empty($validated['parent_id']) ? $validated['parent_id'] : null;
        if ($parentId !== null) {
            if ($parentId === $location->id) {
                throw ValidationException::withMessages([
                    'parent_id' => __('warehouse.validation.parent_self'),
                ]);
            }
            
            // Check if $parentId is an arbitrary descendant of $location
            $curr = Location::where('business_id', $business->id)->find($parentId);
            while ($curr && ! empty($curr->parent_id)) {
                if ($curr->parent_id === $location->id) {
                    throw ValidationException::withMessages([
                        'parent_id' => __('warehouse.validation.parent_descendant'),
                    ]);
                }
                $curr = Location::where('business_id', $business->id)->find($curr->parent_id);
            }
        }

        $isPrimary = $request->boolean('is_primary');
        if ($isPrimary) {
            Location::where('business_id', $business->id)->where('id', '!=', $location->id)->update(['is_primary' => false]);
        }

        $updateData = [
            'parent_id'               => $parentId,
            'name'                    => $validated['name'],
            'type'                    => $validated['type'],
            'code'                    => $validated['code'] ?? null,
            'phone'                   => $validated['phone'] ?? null,
            'address'                 => $validated['address'] ?? null,
            'province'                => $validated['province'] ?? $location->province,
            'city'                    => $validated['city'] ?? $location->city,
            'district'                => $validated['district'] ?? $location->district,
            'village'                 => $validated['village'] ?? $location->village,
            'postal_code'             => $validated['postal_code'] ?? $location->postal_code,
            'biteship_area_id'        => $validated['biteship_area_id'] ?? $location->biteship_area_id,
            'is_active'               => (bool) ($validated['is_active'] ?? $location->is_active),
            'is_online_fulfillment'   => $request->boolean('is_online_fulfillment'),
            'allow_storefront_pickup' => $request->boolean('allow_storefront_pickup'),
            'is_primary'              => $isPrimary ?: $location->is_primary,
        ];

        if (array_key_exists('latitude', $validated)) {
            $updateData['latitude'] = $validated['latitude'];
        }
        if (array_key_exists('longitude', $validated)) {
            $updateData['longitude'] = $validated['longitude'];
        }
        if (isset($validated['geofence_radius_meters'])) {
            $updateData['geofence_radius_meters'] = (int) $validated['geofence_radius_meters'];
        } elseif (isset($validated['geofence_radius'])) {
            $updateData['geofence_radius_meters'] = (int) $validated['geofence_radius'];
        }

        $location->update($updateData);

        $storeSetting = CommerceStoreSetting::where('business_id', $business->id)->first();
        if ($location->is_primary || ($storeSetting && $storeSetting->origin_location_id === $location->id)) {
            if (! $storeSetting) {
                $storeSetting = CommerceStoreSetting::create(['business_id' => $business->id]);
            }
            $storeSetting->update([
                'origin_location_id' => $location->id,
                'origin_area_id'     => $location->biteship_area_id ?? $storeSetting->origin_area_id,
                'origin_address'     => $location->address ?? $storeSetting->origin_address,
                'origin_postal_code' => $location->postal_code ?? $storeSetting->origin_postal_code,
                'origin_latitude'    => $location->latitude ?? $storeSetting->origin_latitude,
                'origin_longitude'   => $location->longitude ?? $storeSetting->origin_longitude,
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('warehouse.messages.updated_success'),
                'location' => $location,
            ]);
        }

        return redirect()->route('warehouse.index')
            ->with('success', __('warehouse.messages.updated_success'));
    }

    /**
     * Hapus gudang - hanya jika tidak ada stok aktif.
     */
    public function destroy(Location $location): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless($location->business_id === $business->id, 403);

        if ($location->is_primary) {
            $msg = __('warehouse.cannot_delete_primary');
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $hasStock = InventoryStock::where('business_id', $business->id)
            ->where('location_id', $location->id)
            ->where('quantity', '>', 0)
            ->exists();

        if ($hasStock) {
            $msg = __('warehouse.cannot_delete_has_stock');
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        // Non-Destructive Location Archival Guard (§FR-05)
        $hasHistory = StockMovement::where('location_id', $location->id)->exists()
            || GoodsReceipt::where('location_id', $location->id)->exists()
            || PosOrder::where('location_id', $location->id)->exists()
            || StockTransfer::where('source_location_id', $location->id)->orWhere('destination_location_id', $location->id)->exists()
            || StockOpname::where('location_id', $location->id)->exists()
            || StockAdjustment::where('location_id', $location->id)->exists()
            || Attendance::where('location_id', $location->id)->exists();

        if ($hasHistory) {
            $location->update(['is_active' => false]);
            $msg = __('warehouse.messages.deactivated_due_to_history', ['name' => $location->name]);

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => true, 'message' => $msg, 'deactivated' => true]);
            }

            return redirect()->route('warehouse.index')
                ->with('success', $msg);
        }

        $location->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => __('warehouse.messages.deleted_success')]);
        }

        return redirect()->route('warehouse.index')
            ->with('success', __('warehouse.messages.deleted_success'));
    }
}
