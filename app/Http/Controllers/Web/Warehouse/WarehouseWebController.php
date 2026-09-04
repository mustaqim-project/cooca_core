<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\StockMovement;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class WarehouseWebController extends Controller
{
    /**
     * Warehouse Management Hub — semua gudang/lokasi dalam satu dashboard.
     */
    public function index(): View
    {
        $business = Context::requireBusiness();

        $locations = Location::where('business_id', $business->id)
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

        // Recent movements (last 10, all warehouses)
        $recentMovements = StockMovement::where('business_id', $business->id)
            ->with(['product', 'location'])
            ->latest()
            ->limit(8)
            ->get();

        return view('app.warehouse.index', compact(
            'business',
            'locations',
            'totalValuation',
            'totalLowStock',
            'totalWarehouses',
            'activeWarehouses',
            'recentMovements'
        ));
    }

    /**
     * Buat gudang/lokasi baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'type'    => ['required', 'string', 'in:outlet,warehouse,central_kitchen'],
            'code'    => ['nullable', 'string', 'max:50'],
            'phone'   => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        // Generate unique slug
        $baseSlug = Str::slug($validated['name']);
        $slug     = $baseSlug;
        $counter  = 1;
        while (Location::where('business_id', $business->id)->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        Location::create([
            'business_id' => $business->id,
            'name'        => $validated['name'],
            'slug'        => $slug,
            'type'        => $validated['type'],
            'code'        => $validated['code'] ?? null,
            'phone'       => $validated['phone'] ?? null,
            'address'     => $validated['address'] ?? null,
            'is_primary'  => false,
            'is_active'   => true,
        ]);

        return redirect()->route('warehouse.index')
            ->with('success', "Gudang \"{$validated['name']}\" berhasil ditambahkan!");
    }

    /**
     * Detail gudang — stok produk, riwayat penerimaan, dan aktivitas terkini.
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

        return view('app.warehouse.show', compact(
            'business',
            'location',
            'stocks',
            'totalValuation',
            'lowStockCount',
            'receipts',
            'recentMovements',
            'otherLocations'
        ));
    }

    /**
     * Update data gudang/lokasi.
     */
    public function update(Request $request, Location $location): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($location->business_id === $business->id, 403);

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'type'      => ['required', 'string', 'in:outlet,warehouse,central_kitchen'],
            'code'      => ['nullable', 'string', 'max:50'],
            'phone'     => ['nullable', 'string', 'max:50'],
            'address'   => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ]);

        $location->update([
            'name'      => $validated['name'],
            'type'      => $validated['type'],
            'code'      => $validated['code'] ?? null,
            'phone'     => $validated['phone'] ?? null,
            'address'   => $validated['address'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? $location->is_active),
        ]);

        return redirect()->route('warehouse.index')
            ->with('success', "Gudang \"{$location->name}\" berhasil diperbarui.");
    }

    /**
     * Hapus gudang — hanya jika tidak ada stok aktif.
     */
    public function destroy(Location $location): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless($location->business_id === $business->id, 403);

        if ($location->is_primary) {
            return back()->with('error', 'Gudang/outlet utama tidak dapat dihapus. Nonaktifkan saja jika tidak diperlukan.');
        }

        $hasStock = InventoryStock::where('business_id', $business->id)
            ->where('location_id', $location->id)
            ->where('quantity', '>', 0)
            ->exists();

        if ($hasStock) {
            return back()->with('error', 'Gudang ini masih memiliki stok aktif. Kosongkan stok terlebih dahulu sebelum menghapus.');
        }

        $name = $location->name;
        $location->delete();

        return redirect()->route('warehouse.index')
            ->with('success', "Gudang \"{$name}\" berhasil dihapus.");
    }
}
