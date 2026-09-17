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
use App\Domain\Storage\OwnerStorageQuotaService;
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

        $query = Product::goods()
            ->with(['category', 'outputUnit', 'costModels.latestVersion'])
            ->latest();

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $products = $query->paginate(15)->withQueryString();
        $categories = ProductCategory::where('business_id', $business->id)->get();
        $units = Unit::available()->orderBy('name')->get();

        // Optional preselect for edit modal (?edit=<id>) - used when arriving from calculator.
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
     * Toggle POS product image visibility.
     */
    public function togglePosImageVisibility(Request $request): \Illuminate\Http\JsonResponse
    {
        $business = Context::requireBusiness();

        $request->validate([
            'show_images' => ['nullable', 'boolean'],
        ]);

        $newValue = $request->has('show_images')
            ? $request->boolean('show_images')
            : ! (bool) $business->pos_show_product_images;

        $business->update([
            'pos_show_product_images' => $newValue,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $business->pos_show_product_images
                ? 'Gambar produk kini DITAMPILKAN pada terminal POS.'
                : 'Gambar produk kini DISEMBUNYIKAN pada terminal POS.',
            'pos_show_product_images' => (bool) $business->pos_show_product_images,
        ]);
    }

    /**
     * Quick toggle product setting (channel visibility, web price, preorder, is_active).
     */
    public function toggleSetting(Request $request, Product $product): \Illuminate\Http\JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless($product->business_id === $business->id, 403);

        $request->validate([
            'field' => ['required', 'string', 'in:show_in_website,show_in_pos,show_in_sales_order,show_price_on_web,is_preorder,is_active'],
            'value' => ['nullable', 'boolean'],
        ]);

        $field = (string) $request->input('field');
        $newValue = $request->has('value')
            ? $request->boolean('value')
            : ! (bool) $product->{$field};

        $product->update([
            $field => $newValue,
        ]);

        $labels = [
            'show_in_website' => 'Etalase Web',
            'show_in_pos' => 'Kasir POS',
            'show_in_sales_order' => 'Faktur SO',
            'show_price_on_web' => 'Harga di Web',
            'is_preorder' => 'Sistem Pre-Order',
            'is_active' => 'Status Produk',
        ];
        $label = $labels[$field] ?? $field;
        $stateText = $newValue ? 'diaktifkan' : 'dinonaktifkan';

        return response()->json([
            'status' => 'success',
            'message' => "Pengaturan {$label} untuk '{$product->name}' berhasil {$stateText}.",
            'field' => $field,
            'value' => (bool) $newValue,
            'product_id' => $product->id,
        ]);
    }

    /**
     * Store a new product and auto-create default CostModel.
     */
    public function store(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $trackingService = app(\App\Domain\Storage\StorageTrackingService::class);
        $owner = app(OwnerStorageQuotaService::class)->ownerForBusiness($business);
        if ($owner && $request->hasFile('image')) {
            $trackingService->assertCanUpload($owner, (int) $request->file('image')->getSize(), 'image');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'product_category_id' => ['nullable', 'exists:product_categories,id'],
            'output_unit_id' => ['required', 'exists:units,id'],
            'sku' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'code')->where(fn($query) => $query->where('business_id', $business->id)),
            ],
            'selling_price' => ['nullable', 'numeric', 'gte:0'],
            'base_cost' => ['nullable', 'numeric', 'gte:0'],
            'min_stock' => ['nullable', 'numeric', 'gte:0'],
            'business_type_hint' => ['nullable', 'string'],
            'description' => ['nullable', 'string', 'max:2000'],
            'costing_method' => ['required', 'string', 'in:simple,per_unit,recipe_bom,job,process,abc,service,retail,custom'],
            'show_in_website' => ['nullable', 'boolean'],
            'show_in_pos' => ['nullable', 'boolean'],
            'show_in_sales_order' => ['nullable', 'boolean'],
            'show_price_on_web' => ['nullable', 'boolean'],
            'is_preorder' => ['nullable', 'boolean'],
            'preorder_mode' => ['nullable', 'string', 'in:merchant_batch,customer_schedule'],
            'preorder_lead_days' => ['nullable', 'integer', 'min:0', 'max:90'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:max_width=2400,max_height=2400'],
        ]);

        /** @var Product $product */
        $product = Product::create([
            'business_id' => $business->id,
            'type' => Product::TYPE_GOODS,
            'category_id' => $validated['category_id'] ?? $validated['product_category_id'] ?? null,
            'output_unit_id' => $validated['output_unit_id'],
            'code' => $validated['sku'] ?? null,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'business_type_hint' => $validated['business_type_hint'] ?? 'general',
            'selling_price' => (float) ($validated['selling_price'] ?? 0),
            'base_cost' => (float) ($validated['base_cost'] ?? 0),
            'min_stock' => (float) ($validated['min_stock'] ?? 0),
            'show_in_website' => $request->has('show_in_website') ? $request->boolean('show_in_website') : true,
            'show_in_pos' => $request->has('show_in_pos') ? $request->boolean('show_in_pos') : true,
            'show_in_sales_order' => $request->has('show_in_sales_order') ? $request->boolean('show_in_sales_order') : true,
            'show_price_on_web' => $request->has('show_price_on_web') ? $request->boolean('show_price_on_web') : true,
            'is_preorder' => $request->boolean('is_preorder'),
            'preorder_mode' => $validated['preorder_mode'] ?? Product::PREORDER_MODE_SCHEDULE,
            'preorder_lead_days' => (int) ($validated['preorder_lead_days'] ?? 1),
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('businesses/' . $business->id . '/products', 'public');
            $product->update(['image_path' => $imagePath]);
            if ($owner) {
                $trackingService->recordUpload(
                    file: $request->file('image'),
                    filePath: $imagePath,
                    category: \App\Models\StorageFile::CATEGORY_PRODUCT_IMAGE,
                    module: 'product',
                    owner: $owner,
                    business: $business,
                    uploader: $request->user()
                );
            }
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
        abort_unless($product->business_id === $business->id, 403);

        $trackingService = app(\App\Domain\Storage\StorageTrackingService::class);
        $owner = app(OwnerStorageQuotaService::class)->ownerForBusiness($business);
        if ($owner && $request->hasFile('image')) {
            $trackingService->assertCanUpload($owner, (int) $request->file('image')->getSize(), 'image');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'output_unit_id' => ['required', 'exists:units,id'],
            'sku' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'code')
                    ->ignore($product->id)
                    ->where(fn($query) => $query->where('business_id', $product->business_id)),
            ],
            'base_cost' => ['nullable', 'numeric', 'gte:0'],
            'selling_price' => ['nullable', 'numeric', 'gte:0'],
            'min_stock' => ['nullable', 'numeric', 'gte:0'],
            'is_active' => ['nullable', 'boolean'],
            'show_in_website' => ['nullable', 'boolean'],
            'show_in_pos' => ['nullable', 'boolean'],
            'show_in_sales_order' => ['nullable', 'boolean'],
            'show_price_on_web' => ['nullable', 'boolean'],
            'is_preorder' => ['nullable', 'boolean'],
            'preorder_mode' => ['nullable', 'string', 'in:merchant_batch,customer_schedule'],
            'preorder_lead_days' => ['nullable', 'integer', 'min:0', 'max:90'],
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
            'is_active' => $request->boolean('is_active'),
            'show_in_website' => $request->boolean('show_in_website'),
            'show_in_pos' => $request->boolean('show_in_pos'),
            'show_in_sales_order' => $request->boolean('show_in_sales_order'),
            'show_price_on_web' => $request->boolean('show_price_on_web'),
            'is_preorder' => $request->boolean('is_preorder'),
            'preorder_mode' => $validated['preorder_mode'] ?? $product->preorder_mode,
            'preorder_lead_days' => isset($validated['preorder_lead_days']) ? (int) $validated['preorder_lead_days'] : $product->preorder_lead_days,
            'description' => $validated['description'] ?? $product->description,
        ]);

        if ($request->boolean('remove_image') && $product->image_path) {
            $trackingService->deleteFile($product->image_path, 'public');
            $product->update(['image_path' => null]);
        } elseif ($request->hasFile('image')) {
            $oldImagePath = $product->image_path;
            $newImagePath = $request->file('image')->store('businesses/' . $business->id . '/products', 'public');
            $product->update(['image_path' => $newImagePath]);
            if ($owner) {
                $trackingService->recordUpload(
                    file: $request->file('image'),
                    filePath: $newImagePath,
                    category: \App\Models\StorageFile::CATEGORY_PRODUCT_IMAGE,
                    module: 'product',
                    owner: $owner,
                    business: $business,
                    uploader: $request->user()
                );
            }
            if ($oldImagePath) {
                $trackingService->deleteFile($oldImagePath, 'public');
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
        abort_unless($product->business_id === $business->id, 403);

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

        // Scope materials to current business only - prevents cross-tenant data leakage
        $materials = Material::with(['unit', 'prices'])
            ->where('business_id', $business->id)
            ->get();
        $units = Unit::available()->orderBy('name')->get();

        // Calculate rolled-up BOM explosion
        $explosion = $this->bomService->explode($bomHeader);

        return view('app.products.bom', compact('business', 'product', 'costModel', 'bomHeader', 'materials', 'units', 'explosion'));
    }

    /**
     * Add item to BOM.
     */
    public function addBomItem(Request $request, BomHeader $bomHeader): RedirectResponse
    {
        $business = Context::requireBusiness();

        // IDOR guard: ensure BomHeader belongs to current business via CostModel
        abort_unless(
            $bomHeader->costModel?->business_id === $business->id,
            403
        );

        $validated = $request->validate([
            'material_id' => [
                'required',
                'exists:materials,id',
                // Ensure the selected material belongs to this business
                Rule::exists('materials', 'id')->where('business_id', $business->id),
            ],
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
        $business = Context::requireBusiness();

        // IDOR guard: traverse BomItem → BomHeader → CostModel → business_id
        abort_unless(
            $bomItem->header?->costModel?->business_id === $business->id,
            403
        );

        $bomItem->delete();

        return back()->with('success', 'Komponen berhasil dihapus dari BOM.');
    }

    /**
     * Delete product.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($product->business_id === $business->id, 404);

        if ($product->image_path) {
            app(\App\Domain\Storage\StorageTrackingService::class)->deleteFile($product->image_path, 'public');
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }
}
