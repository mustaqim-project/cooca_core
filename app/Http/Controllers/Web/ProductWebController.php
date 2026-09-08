<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Product\BomExplosionService;
use App\Http\Controllers\Controller;
use App\Models\BomHeader;
use App\Models\BomItem;
use App\Models\CostModel;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class ProductWebController extends Controller
{
    public function __construct(private readonly BomExplosionService $bomService = new BomExplosionService) {}

    /**
     * List products.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();

        $query = Product::with(['category', 'outputUnit', 'costModels.latestVersion'])
            ->latest();

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $products = $query->paginate(15)->withQueryString();
        $categories = ProductCategory::where('business_id', $business->id)->get();
        $units = Unit::available()->orderBy('name')->get();

        // Optional preselect for edit modal (?edit=<id>) — used when arriving from calculator.
        $editProductId = $request->get('edit');
        $editProduct = null;
        if ($editProductId) {
            $editProduct = Product::with(['category', 'outputUnit'])
                ->where('id', $editProductId)
                ->where('business_id', $business->id)
                ->first();
            if (! $editProduct) {
                $editProductId = null;
            }
        }

        return view('app.products.index', compact('business', 'products', 'categories', 'units', 'editProductId', 'editProduct'));
    }

    /**
     * Store a new product and auto-create default CostModel.
     */
    public function store(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'product_category_id' => ['nullable', 'exists:product_categories,id'],
            'output_unit_id' => ['required', 'exists:units,id'],
            'sku' => [
                'nullable', 'string', 'max:100',
                Rule::unique('products', 'code')->where(fn ($query) => $query->where('business_id', $business->id)),
            ],
            'selling_price' => ['nullable', 'numeric', 'gte:0'],
            'base_cost' => ['nullable', 'numeric', 'gte:0'],
            'min_stock' => ['nullable', 'numeric', 'gte:0'],
            'business_type_hint' => ['nullable', 'string'],
            'costing_method' => ['required', 'string', 'in:simple,per_unit,recipe_bom,job,process,abc,service,retail,custom'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:max_width=2400,max_height=2400'],
        ]);

        /** @var Product $product */
        $product = Product::create([
            'business_id' => $business->id,
            'category_id' => $validated['category_id'] ?? $validated['product_category_id'] ?? null,
            'output_unit_id' => $validated['output_unit_id'],
            'code' => $validated['sku'] ?? null,
            'name' => $validated['name'],
            'business_type_hint' => $validated['business_type_hint'] ?? 'general',
            'selling_price' => (float) ($validated['selling_price'] ?? 0),
            'base_cost' => (float) ($validated['base_cost'] ?? 0),
            'min_stock' => (float) ($validated['min_stock'] ?? 0),
        ]);

        if ($request->hasFile('image')) {
            $product->update(['image_path' => $request->file('image')->store('products/' . $business->id, 'public')]);
        }

        // Auto create primary CostModel
        CostModel::create([
            'business_id' => $business->id,
            'product_id' => $product->id,
            'name' => "Model HPP Utama - {$product->name}",
            'method' => $validated['costing_method'],
            'is_active' => true,
        ]);

        return redirect()->route('products.index')->with('success', 'Produk dan Model Biaya berhasil dibuat.');
    }

    /**
     * Update an existing product.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($product->business_id === $business->id, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'output_unit_id' => ['required', 'exists:units,id'],
            'sku' => [
                'nullable', 'string', 'max:100',
                Rule::unique('products', 'code')
                    ->ignore($product->id)
                    ->where(fn ($query) => $query->where('business_id', $product->business_id)),
            ],
            'base_cost' => ['nullable', 'numeric', 'gte:0'],
            'selling_price' => ['nullable', 'numeric', 'gte:0'],
            'min_stock' => ['nullable', 'numeric', 'gte:0'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:max_width=2400,max_height=2400'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        $product->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'] ?? null,
            'output_unit_id' => $validated['output_unit_id'],
            'code' => $validated['sku'] ?? $product->code,
            'base_cost' => (float) ($validated['base_cost'] ?? $product->base_cost),
            'selling_price' => (float) ($validated['selling_price'] ?? $product->selling_price),
            'min_stock' => (float) ($validated['min_stock'] ?? $product->min_stock),
            'is_active' => $request->has('is_active') ? (bool) $request->get('is_active') : $product->is_active,
            'description' => $validated['description'] ?? $product->description,
        ]);

        if ($request->boolean('remove_image') && $product->image_path) {
            Storage::disk('public')->delete($product->image_path);
            $product->update(['image_path' => null]);
        } elseif ($request->hasFile('image')) {
            $oldImagePath = $product->image_path;
            $newImagePath = $request->file('image')->store('products/' . $business->id, 'public');
            $product->update(['image_path' => $newImagePath]);
            if ($oldImagePath) {
                Storage::disk('public')->delete($oldImagePath);
            }
        }

        return redirect()->route('products.index')->with('success', "Produk '{$product->name}' berhasil diperbarui.");
    }

    /**
     * Show interactive visual BOM builder.
     */
    public function bom(Product $product): View
    {
        $business = Context::requireBusiness();

        $costModel = $product->costModels()->firstOrCreate(
            ['is_active' => true],
            [
                'business_id' => $business->id,
                'name' => "Model HPP Utama - {$product->name}",
                'method' => CostModel::METHOD_RECIPE_BOM,
            ]
        );

        $bomHeader = $costModel->bomHeaders()->firstOrCreate(
            ['cost_model_id' => $costModel->id],
            [
                'type' => BomHeader::TYPE_RECIPE,
                'name' => "Resep / BOM {$product->name}",
                'level' => 1,
            ]
        );

        $bomHeader->load(['items.material.prices', 'items.material.unit', 'items.unit']);

        $materials = Material::with(['unit', 'prices'])->get();
        $units = Unit::all();

        // Calculate rolled-up BOM explosion
        $explosion = $this->bomService->explode($bomHeader);

        return view('app.products.bom', compact('business', 'product', 'costModel', 'bomHeader', 'materials', 'units', 'explosion'));
    }

    /**
     * Add item to BOM.
     */
    public function addBomItem(Request $request, BomHeader $bomHeader): RedirectResponse
    {
        $validated = $request->validate([
            'material_id' => ['required', 'exists:materials,id'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit_id' => ['required', 'exists:units,id'],
            'waste_percentage' => ['nullable', 'numeric', 'gte:0', 'lte:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $bomHeader->items()->create([
            'material_id' => $validated['material_id'],
            'quantity' => (float) $validated['quantity'],
            'unit_id' => $validated['unit_id'],
            'waste_percentage' => (float) ($validated['waste_percentage'] ?? 0.0),
            'notes' => $validated['notes'] ?? null,
            'is_mandatory' => true,
        ]);

        return back()->with('success', 'Bahan berhasil ditambahkan ke resep/BOM.');
    }

    /**
     * Remove item from BOM.
     */
    public function removeBomItem(BomItem $bomItem): RedirectResponse
    {
        $bomItem->delete();

        return back()->with('success', 'Komponen berhasil dihapus dari BOM.');
    }

    /**
     * Delete product.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }
}
