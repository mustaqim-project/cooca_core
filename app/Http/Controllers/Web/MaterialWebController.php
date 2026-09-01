<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Material\MaterialCostService;
use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\MaterialPrice;
use App\Models\Supplier;
use App\Models\Unit;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class MaterialWebController extends Controller
{
    public function __construct(private readonly MaterialCostService $costService = new MaterialCostService) {}

    /**
     * Show materials list.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();

        $query = Material::with(['category', 'unit', 'supplier', 'prices'])
            ->latest();

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        $materials = $query->paginate(15)->withQueryString();
        $categories = MaterialCategory::where('business_id', $business->id)->get();
        $suppliers = Supplier::where('business_id', $business->id)->get();
        $units = Unit::available()->orderBy('name')->get();

        return view('app.materials.index', compact('business', 'materials', 'categories', 'suppliers', 'units'));
    }

    /**
     * Store a new material with initial price history.
     */
    public function store(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:material_categories,id'],
            'material_category_id' => ['nullable', 'exists:material_categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'sku' => ['nullable', 'string', 'max:100'],
            'yield_percentage' => ['required', 'numeric', 'gte:1', 'lte:500'],
            'waste_percentage' => ['required', 'numeric', 'gte:0', 'lte:100'],
            'allow_yield_over_100' => ['nullable', 'boolean'],
            'purchase_price' => ['required', 'numeric', 'gt:0'],
            'shipping_cost' => ['nullable', 'numeric', 'gte:0'],
            'discount_amount' => ['nullable', 'numeric', 'gte:0'],
        ]);

        /** @var Material $material */
        $material = Material::create([
            'business_id' => $business->id,
            'category_id' => $validated['category_id'] ?? $validated['material_category_id'] ?? null,
            'supplier_id' => $validated['supplier_id'] ?? null,
            'unit_id' => $validated['unit_id'],
            'code' => $validated['sku'] ?? null,
            'name' => $validated['name'],
        ]);

        $purchasePrice = (float) $validated['purchase_price'];
        $shipping = (float) ($validated['shipping_cost'] ?? 0.0);
        $discount = (float) ($validated['discount_amount'] ?? 0.0);

        /** @var MaterialPrice $price */
        $price = MaterialPrice::create([
            'business_id' => $business->id,
            'material_id' => $material->id,
            'supplier_id' => $validated['supplier_id'] ?? null,
            'purchase_price' => $purchasePrice,
            'shipping_cost' => $shipping,
            'handling_cost' => 0.0,
            'import_cost' => 0.0,
            'discount_amount' => $discount,
            'yield_percentage' => (float) $validated['yield_percentage'],
            'waste_percentage' => (float) $validated['waste_percentage'],
            'purchase_unit_id' => $validated['unit_id'],
            'effective_cost' => $purchasePrice + $shipping - $discount,
            'effective_date' => now()->toDateString(),
            'notes' => 'Harga awal saat pembuatan bahan.',
        ]);

        $price->update([
            'effective_cost' => $this->costService->calculateEffectiveAcquisitionCost($price),
        ]);

        return redirect()->route('materials.index')->with('success', 'Bahan baku berhasil ditambahkan.');
    }

    /**
     * Update an existing material.
     */
    public function update(Request $request, Material $material): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:material_categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'sku' => ['nullable', 'string', 'max:100'],
        ]);

        $material->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'] ?? null,
            'supplier_id' => $validated['supplier_id'] ?? null,
            'unit_id' => $validated['unit_id'],
            'code' => $validated['sku'] ?? $material->code,
        ]);

        return redirect()->route('materials.index')->with('success', "Bahan baku '{$material->name}' berhasil diperbarui.");
    }

    /**
     * Add new price update for material.
     */
    public function storePrice(Request $request, Material $material): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'purchase_price' => ['required', 'numeric', 'gt:0'],
            'shipping_cost' => ['nullable', 'numeric', 'gte:0'],
            'discount_amount' => ['nullable', 'numeric', 'gte:0'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $purchasePrice = (float) $validated['purchase_price'];
        $shipping = (float) ($validated['shipping_cost'] ?? 0.0);
        $discount = (float) ($validated['discount_amount'] ?? 0.0);

        $latestPrice = $material->prices()->latest()->first();

        /** @var MaterialPrice $price */
        $price = MaterialPrice::create([
            'business_id' => $business->id,
            'material_id' => $material->id,
            'supplier_id' => $validated['supplier_id'] ?? $material->supplier_id,
            'purchase_price' => $purchasePrice,
            'shipping_cost' => $shipping,
            'handling_cost' => 0.0,
            'import_cost' => 0.0,
            'discount_amount' => $discount,
            'yield_percentage' => (float) ($latestPrice?->yield_percentage ?? 100.0),
            'waste_percentage' => (float) ($latestPrice?->waste_percentage ?? 0.0),
            'purchase_unit_id' => $material->unit_id,
            'effective_cost' => $purchasePrice + $shipping - $discount,
            'effective_date' => now()->toDateString(),
            'notes' => $validated['notes'] ?? 'Pembaruan harga bahan.',
        ]);

        $price->update([
            'effective_cost' => $this->costService->calculateEffectiveAcquisitionCost($price),
        ]);

        return redirect()->route('materials.index')->with('success', 'Harga bahan berhasil diperbarui.');
    }

    /**
     * Delete material.
     */
    public function destroy(Material $material): RedirectResponse
    {
        $material->delete();

        return redirect()->route('materials.index')->with('success', 'Bahan berhasil dihapus.');
    }
}
