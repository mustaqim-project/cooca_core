<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Product\BomExplosionService;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BomHeader;
use App\Models\BomItem;
use App\Models\CostModel;
use App\Models\BranchProductPrice;
use App\Models\Location;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductBundleItem;
use App\Models\ProductCategory;
use App\Models\ProductChannelPrice;
use App\Models\ProductImage;
use App\Models\Unit;
use App\Support\Context;
use App\Domain\Storage\OwnerStorageQuotaService;
use App\Domain\Storage\TenantStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class ProductWebController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('require.permission:products.view', only: ['index', 'bom', 'branchPrices']),
            new Middleware(['require.permission:products.create', 'entitlement:product'], only: ['store']),
            new Middleware('require.permission:products.edit', only: ['update', 'toggleSetting', 'togglePosImageVisibility', 'deleteGalleryImage']),
            new Middleware(['require.permission:products.edit', 'entitlement:recipe'], only: ['addBomItem', 'removeBomItem']),
            new Middleware(['require.permission:products.edit', 'entitlement:branch_pricing'], only: ['updateBranchPrices']),
            new Middleware('entitlement:branch_pricing', only: ['branchPrices']),
            new Middleware('require.permission:products.delete', only: ['destroy']),
        ];
    }

    public function __construct(private readonly BomExplosionService $bomService = new BomExplosionService) {}

    /**
     * List products.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();

        $query = Product::goods()
            ->with(['category', 'outputUnit', 'costModels.latestVersion', 'bundleItems.childProduct', 'channelPrices', 'images', 'marketplaceMappings'])
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
        $allProducts = Product::goods()
            ->where('business_id', $business->id)
            ->where('is_bundle', false)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'selling_price', 'base_cost']);

        $marketplaceCategories = \App\Domain\Marketplace\MarketplaceCategoryRegistry::all();
        $isPharmacy = $business->isPharmacy();
        $isServiceSector = $business->isServiceSector();

        // Optional preselect for edit modal (?edit=<id>) - used when arriving from calculator.
        $editProductId = $request->get('edit');
        $editProduct = null;
        if ($editProductId) {
            $editProduct = Product::with(['category', 'outputUnit', 'bundleItems.childProduct', 'channelPrices', 'marketplaceMappings'])
                ->where('id', $editProductId)
                ->where('business_id', $business->id)
                ->first();
            if (! $editProduct) {
                $editProductId = null;
            }
        }

        return view('app.products.index', compact(
            'business',
            'products',
            'categories',
            'units',
            'editProductId',
            'editProduct',
            'allProducts',
            'marketplaceCategories',
            'isPharmacy',
            'isServiceSector'
        ));
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
        $totalBytes = 0;
        if ($request->hasFile('image')) {
            $totalBytes += (int) $request->file('image')->getSize();
        }
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $gFile) {
                if ($gFile) {
                    $totalBytes += (int) $gFile->getSize();
                }
            }
        }
        if ($owner && $totalBytes > 0) {
            $trackingService->assertCanUpload($owner, $totalBytes, 'image');
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
            'weight' => ['nullable', 'numeric', 'gte:0'],
            'length' => ['nullable', 'numeric', 'gte:0'],
            'width' => ['nullable', 'numeric', 'gte:0'],
            'height' => ['nullable', 'numeric', 'gte:0'],
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
            'is_bundle' => ['nullable', 'boolean'],
            'bundle_items' => ['nullable', 'array'],
            'bundle_items.*.child_product_id' => ['required_with:bundle_items', 'exists:products,id'],
            'bundle_items.*.quantity' => ['required_with:bundle_items', 'numeric', 'gt:0'],
            'channel_prices' => ['nullable', 'array'],
            'channel_prices.*' => ['nullable', 'numeric', 'gte:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:max_width=2400,max_height=2400'],
            'gallery_images' => ['nullable', 'array', 'max:10'],
            'gallery_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:max_width=2400,max_height=2400'],
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
            'weight' => isset($validated['weight']) ? (float) $validated['weight'] : 200.0,
            'length' => isset($validated['length']) ? (float) $validated['length'] : null,
            'width' => isset($validated['width']) ? (float) $validated['width'] : null,
            'height' => isset($validated['height']) ? (float) $validated['height'] : null,
            'show_in_website' => $request->has('show_in_website') ? $request->boolean('show_in_website') : true,
            'show_in_pos' => $request->has('show_in_pos') ? $request->boolean('show_in_pos') : true,
            'show_in_sales_order' => $request->has('show_in_sales_order') ? $request->boolean('show_in_sales_order') : true,
            'show_price_on_web' => $request->has('show_price_on_web') ? $request->boolean('show_price_on_web') : true,
            'is_preorder' => $request->boolean('is_preorder'),
            'preorder_mode' => $validated['preorder_mode'] ?? Product::PREORDER_MODE_SCHEDULE,
            'preorder_lead_days' => (int) ($validated['preorder_lead_days'] ?? 1),
            'is_bundle' => $request->boolean('is_bundle'),
        ]);

        if ($request->boolean('is_bundle') && $request->filled('bundle_items')) {
            foreach ($request->input('bundle_items') as $bItem) {
                if (!empty($bItem['child_product_id']) && (float) ($bItem['quantity'] ?? 0) > 0) {
                    ProductBundleItem::create([
                        'business_id' => $business->id,
                        'parent_product_id' => $product->id,
                        'child_product_id' => $bItem['child_product_id'],
                        'quantity' => (float) $bItem['quantity'],
                    ]);
                }
            }
        }

        if ($request->filled('channel_prices')) {
            $allowedChannels = ['dine_in', 'takeaway', 'gofood', 'grabfood', 'shopeefood'];
            foreach ($request->input('channel_prices') as $channel => $price) {
                if (in_array($channel, $allowedChannels, true) && $price !== null && $price !== '') {
                    ProductChannelPrice::create([
                        'business_id' => $business->id,
                        'product_id' => $product->id,
                        'channel' => $channel,
                        'price' => (float) $price,
                    ]);
                }
            }
        }

        if ($request->hasFile('image')) {
            $dir = TenantStorage::publicDir($business, TenantStorage::FOLDER_PRODUCTS);
            $imagePath = $request->file('image')->store($dir, 'public');
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

        if ($request->hasFile('gallery_images')) {
            $dir = TenantStorage::publicDir($business, TenantStorage::FOLDER_PRODUCTS);
            foreach ($request->file('gallery_images') as $index => $gFile) {
                if ($gFile && $gFile->isValid()) {
                    $gPath = $gFile->store($dir, 'public');
                    ProductImage::create([
                        'business_id' => $business->id,
                        'product_id' => $product->id,
                        'image_path' => $gPath,
                        'sort_order' => $index + 1,
                    ]);
                    if ($owner) {
                        $trackingService->recordUpload(
                            file: $gFile,
                            filePath: $gPath,
                            category: \App\Models\StorageFile::CATEGORY_PRODUCT_IMAGE,
                            module: 'product',
                            owner: $owner,
                            business: $business,
                            uploader: $request->user()
                        );
                    }
                }
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

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $request->user()?->id,
            'auditable_type' => Product::class,
            'auditable_id' => $product->id,
            'action' => 'product.created',
            'risk_level' => AuditLog::RISK_LOW,
            'new_values' => [
                'name' => $product->name,
                'code' => $product->code,
                'selling_price' => (float) $product->selling_price,
                'base_cost' => (float) $product->base_cost,
                'is_bundle' => (bool) $product->is_bundle,
                'is_preorder' => (bool) $product->is_preorder,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('products.index')->with('success', __('products.created_success'));
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
        $totalBytes = 0;
        if ($request->hasFile('image')) {
            $totalBytes += (int) $request->file('image')->getSize();
        }
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $gFile) {
                if ($gFile) {
                    $totalBytes += (int) $gFile->getSize();
                }
            }
        }
        if ($owner && $totalBytes > 0) {
            $trackingService->assertCanUpload($owner, $totalBytes, 'image');
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
            'weight' => ['nullable', 'numeric', 'gte:0'],
            'length' => ['nullable', 'numeric', 'gte:0'],
            'width' => ['nullable', 'numeric', 'gte:0'],
            'height' => ['nullable', 'numeric', 'gte:0'],
            'is_active' => ['nullable', 'boolean'],
            'show_in_website' => ['nullable', 'boolean'],
            'show_in_pos' => ['nullable', 'boolean'],
            'show_in_sales_order' => ['nullable', 'boolean'],
            'show_price_on_web' => ['nullable', 'boolean'],
            'is_preorder' => ['nullable', 'boolean'],
            'preorder_mode' => ['nullable', 'string', 'in:merchant_batch,customer_schedule'],
            'preorder_lead_days' => ['nullable', 'integer', 'min:0', 'max:90'],
            'is_bundle' => ['nullable', 'boolean'],
            'bundle_items' => ['nullable', 'array'],
            'bundle_items.*.child_product_id' => ['required_with:bundle_items', 'exists:products,id'],
            'bundle_items.*.quantity' => ['required_with:bundle_items', 'numeric', 'gt:0'],
            'channel_prices' => ['nullable', 'array'],
            'channel_prices.*' => ['nullable', 'numeric', 'gte:0'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:max_width=2400,max_height=2400'],
            'remove_image' => ['nullable', 'boolean'],
            'gallery_images' => ['nullable', 'array', 'max:10'],
            'gallery_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:max_width=2400,max_height=2400'],
            'remove_gallery_ids' => ['nullable', 'array'],
            'remove_gallery_ids.*' => ['string'],
        ]);

        $oldValues = [
            'name' => $product->name,
            'code' => $product->code,
            'selling_price' => (float) $product->selling_price,
            'base_cost' => (float) $product->base_cost,
            'is_active' => (bool) $product->is_active,
        ];

        $product->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'] ?? null,
            'output_unit_id' => $validated['output_unit_id'],
            'code' => $validated['sku'] ?? $product->code,
            'base_cost' => (float) ($validated['base_cost'] ?? $product->base_cost),
            'selling_price' => (float) ($validated['selling_price'] ?? $product->selling_price),
            'min_stock' => (float) ($validated['min_stock'] ?? $product->min_stock),
            'weight' => isset($validated['weight']) ? (float) $validated['weight'] : (float) ($product->weight ?? 200.0),
            'length' => isset($validated['length']) ? (float) $validated['length'] : $product->length,
            'width' => isset($validated['width']) ? (float) $validated['width'] : $product->width,
            'height' => isset($validated['height']) ? (float) $validated['height'] : $product->height,
            'is_active' => $request->boolean('is_active'),
            'show_in_website' => $request->boolean('show_in_website'),
            'show_in_pos' => $request->boolean('show_in_pos'),
            'show_in_sales_order' => $request->boolean('show_in_sales_order'),
            'show_price_on_web' => $request->boolean('show_price_on_web'),
            'is_preorder' => $request->boolean('is_preorder'),
            'preorder_mode' => $validated['preorder_mode'] ?? $product->preorder_mode,
            'preorder_lead_days' => isset($validated['preorder_lead_days']) ? (int) $validated['preorder_lead_days'] : $product->preorder_lead_days,
            'is_bundle' => $request->boolean('is_bundle'),
            'description' => $validated['description'] ?? $product->description,
        ]);

        // Sync Bundle Items
        ProductBundleItem::where('parent_product_id', $product->id)->delete();
        if ($request->boolean('is_bundle') && $request->filled('bundle_items')) {
            foreach ($request->input('bundle_items') as $bItem) {
                if (!empty($bItem['child_product_id']) && (float) ($bItem['quantity'] ?? 0) > 0) {
                    if ($bItem['child_product_id'] === $product->id) {
                        continue;
                    }
                    ProductBundleItem::create([
                        'business_id' => $business->id,
                        'parent_product_id' => $product->id,
                        'child_product_id' => $bItem['child_product_id'],
                        'quantity' => (float) $bItem['quantity'],
                    ]);
                }
            }
        }

        // Sync Channel Prices
        if ($request->has('channel_prices')) {
            $allowedChannels = ['dine_in', 'takeaway', 'gofood', 'grabfood', 'shopeefood'];
            ProductChannelPrice::where('product_id', $product->id)->delete();
            foreach ((array) $request->input('channel_prices', []) as $channel => $price) {
                if (in_array($channel, $allowedChannels, true) && $price !== null && $price !== '') {
                    ProductChannelPrice::create([
                        'business_id' => $business->id,
                        'product_id' => $product->id,
                        'channel' => $channel,
                        'price' => (float) $price,
                    ]);
                }
            }
        }

        if ($request->boolean('remove_image') && $product->image_path) {
            $trackingService->deleteFile($product->image_path, 'public');
            $product->update(['image_path' => null]);
        } elseif ($request->hasFile('image')) {
            $oldImagePath = $product->image_path;
            $dir = TenantStorage::publicDir($business, TenantStorage::FOLDER_PRODUCTS);
            $newImagePath = $request->file('image')->store($dir, 'public');
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

        // Delete specified gallery images
        if ($request->filled('remove_gallery_ids')) {
            $imagesToDelete = ProductImage::where('business_id', $business->id)
                ->where('product_id', $product->id)
                ->whereIn('id', (array) $request->input('remove_gallery_ids'))
                ->get();

            foreach ($imagesToDelete as $img) {
                if ($img->image_path) {
                    $trackingService->deleteFile($img->image_path, 'public');
                }
                $img->delete();
            }
        }

        // Add new gallery images
        if ($request->hasFile('gallery_images')) {
            $dir = TenantStorage::publicDir($business, TenantStorage::FOLDER_PRODUCTS);
            $currentMaxSort = (int) ProductImage::where('product_id', $product->id)->max('sort_order');
            foreach ($request->file('gallery_images') as $index => $gFile) {
                if ($gFile && $gFile->isValid()) {
                    $gPath = $gFile->store($dir, 'public');
                    ProductImage::create([
                        'business_id' => $business->id,
                        'product_id' => $product->id,
                        'image_path' => $gPath,
                        'sort_order' => $currentMaxSort + $index + 1,
                    ]);
                    if ($owner) {
                        $trackingService->recordUpload(
                            file: $gFile,
                            filePath: $gPath,
                            category: \App\Models\StorageFile::CATEGORY_PRODUCT_IMAGE,
                            module: 'product',
                            owner: $owner,
                            business: $business,
                            uploader: $request->user()
                        );
                    }
                }
            }
        }

        $newSellingPrice = (float) ($validated['selling_price'] ?? $product->selling_price);
        $newBaseCost = (float) ($validated['base_cost'] ?? $product->base_cost);
        $isPriceChanged = ($oldValues['selling_price'] !== $newSellingPrice || $oldValues['base_cost'] !== $newBaseCost);

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $request->user()?->id,
            'auditable_type' => Product::class,
            'auditable_id' => $product->id,
            'action' => $isPriceChanged ? 'product.price_updated' : 'product.updated',
            'risk_level' => $isPriceChanged ? AuditLog::RISK_MEDIUM : AuditLog::RISK_LOW,
            'risk_reason' => $isPriceChanged ? 'Perubahan harga jual atau HPP produk katalog' : null,
            'old_values' => $oldValues,
            'new_values' => [
                'name' => $product->name,
                'code' => $product->code,
                'selling_price' => (float) $product->selling_price,
                'base_cost' => (float) $product->base_cost,
                'is_active' => (bool) $product->is_active,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('products.index')->with('success', __('products.updated_success'));
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

        return back()->with('success', __('products.recipe_saved'));
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

        return back()->with('success', __('products.recipe_saved'));
    }

    /**
     * Delete product.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($product->business_id === $business->id, 404);

        $trackingService = app(\App\Domain\Storage\StorageTrackingService::class);

        if ($product->image_path) {
            $trackingService->deleteFile($product->image_path, 'public');
        }

        // Delete all gallery images and their storage files
        $galleryImages = ProductImage::where('business_id', $business->id)
            ->where('product_id', $product->id)
            ->get();
        foreach ($galleryImages as $img) {
            if ($img->image_path) {
                $trackingService->deleteFile($img->image_path, 'public');
            }
            $img->delete();
        }

        $oldData = [
            'name' => $product->name,
            'code' => $product->code,
            'selling_price' => (float) $product->selling_price,
            'base_cost' => (float) $product->base_cost,
        ];

        $product->delete();

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => request()->user()?->id,
            'auditable_type' => Product::class,
            'auditable_id' => $product->id,
            'action' => 'product.deleted',
            'risk_level' => AuditLog::RISK_MEDIUM,
            'risk_reason' => 'Penghapusan produk katalog',
            'old_values' => $oldData,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('products.index')->with('success', __('products.deleted_success'));
    }

    /**
     * Delete a single gallery image from a product.
     */
    public function deleteGalleryImage(Product $product, ProductImage $image): JsonResponse|RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($product->business_id === $business->id, 403);
        abort_unless($image->business_id === $business->id && $image->product_id === $product->id, 404);

        if ($image->image_path) {
            app(\App\Domain\Storage\StorageTrackingService::class)->deleteFile($image->image_path, 'public');
        }
        $image->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Foto galeri berhasil dihapus.',
            ]);
        }

        return back()->with('success', 'Foto galeri berhasil dihapus.');
    }

    /**
     * Get branch prices for a product (JSON).
     */
    public function branchPrices(Product $product): JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless($product->business_id === $business->id, 404);

        $locations = Location::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'code']);

        $existingPrices = BranchProductPrice::where('business_id', $business->id)
            ->where('product_id', $product->id)
            ->get()
            ->keyBy('location_id');

        $branchData = $locations->map(function ($loc) use ($existingPrices, $product) {
            $priceObj = $existingPrices->get($loc->id);
            $hasPriceOverride = $priceObj !== null && $priceObj->price !== null;

            return [
                'location_id' => $loc->id,
                'location_name' => $loc->name,
                'location_type' => $loc->type,
                'location_code' => $loc->code,
                'price' => $hasPriceOverride ? (float) $priceObj->price : (float) $product->selling_price,
                'cost_price' => ($priceObj && $priceObj->cost_price !== null) ? (float) $priceObj->cost_price : (float) $product->base_cost,
                'is_available' => $priceObj ? (bool) $priceObj->is_available : true,
                'has_override' => $priceObj !== null,
                'has_price_override' => $hasPriceOverride,
            ];
        });

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'base_cost' => (float) $product->base_cost,
                'selling_price' => (float) $product->selling_price,
            ],
            'branches' => $branchData,
        ]);
    }

    /**
     * Update branch-specific prices and availability for a product.
     */
    public function updateBranchPrices(Request $request, Product $product)
    {
        $business = Context::requireBusiness();
        abort_unless($product->business_id === $business->id, 404);

        $validated = $request->validate([
            'prices' => ['required', 'array'],
            'prices.*.location_id' => [
                'required',
                'string',
                Rule::exists('locations', 'id')->where('business_id', $business->id),
            ],
            'prices.*.price' => ['nullable', 'numeric', 'gte:0'],
            'prices.*.cost_price' => ['nullable', 'numeric', 'gte:0'],
            'prices.*.is_available' => ['nullable', 'boolean'],
            'prices.*.use_custom' => ['nullable', 'boolean'],
            'prices.*.reset' => ['nullable', 'boolean'],
        ]);

        foreach ($validated['prices'] as $item) {
            $locationId = $item['location_id'];
            $isAvailable = isset($item['is_available']) ? (bool) $item['is_available'] : true;
            $shouldReset = !empty($item['reset']);
            $hasCustomPrice = isset($item['price']) && $item['price'] !== null && $item['price'] !== '';

            // Clean reset to master: delete record if available and requested to reset
            if ($shouldReset && $isAvailable) {
                BranchProductPrice::where('business_id', $business->id)
                    ->where('product_id', $product->id)
                    ->where('location_id', $locationId)
                    ->delete();
                continue;
            }

            // Either explicitly disabled or has a custom price override
            if (! $isAvailable || $hasCustomPrice) {
                BranchProductPrice::updateOrCreate(
                    [
                        'business_id' => $business->id,
                        'product_id' => $product->id,
                        'location_id' => $locationId,
                    ],
                    [
                        'is_available' => $isAvailable,
                        'price' => $hasCustomPrice ? (float) $item['price'] : null,
                        'cost_price' => (isset($item['cost_price']) && $item['cost_price'] !== null && $item['cost_price'] !== '') ? (float) $item['cost_price'] : null,
                    ]
                );
            } else {
                // If it is available and has no custom price, remove any stale override
                BranchProductPrice::where('business_id', $business->id)
                    ->where('product_id', $product->id)
                    ->where('location_id', $locationId)
                    ->delete();
            }
        }

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $request->user()?->id,
            'auditable_type' => Product::class,
            'auditable_id' => $product->id,
            'action' => 'product.branch_prices_updated',
            'risk_level' => AuditLog::RISK_MEDIUM,
            'risk_reason' => 'Perubahan harga khusus cabang/outlet untuk produk',
            'new_values' => [
                'prices' => $validated['prices'],
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('products.price_updated'),
            ]);
        }

        return back()->with('success', __('products.price_updated'));
    }
}
