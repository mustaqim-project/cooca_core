<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CostModel;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class ServiceWebController extends Controller
{
    /**
     * Display a listing of services/layanan.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();

        $query = Product::services()
            ->where('business_id', $business->id)
            ->with(['category', 'outputUnit'])
            ->latest();

        if ($request->filled('search')) {
            $search = trim((string) $request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $services = $query->paginate(15)->withQueryString();
        $categories = ProductCategory::where('business_id', $business->id)->orderBy('name')->get();
        
        // Find suitable service units (Jasa, Sesi, Jam, Paket, Hari, Orang, Unit)
        $units = Unit::available()->orderBy('name')->get();
        $defaultUnit = $units->first(fn ($u) => in_array(strtolower($u->code), ['jasa', 'sesi', 'jam', 'paket', 'unit', 'org', 'pcs'], true))
            ?? $units->first();

        $totalServicesCount = Product::services()->where('business_id', $business->id)->count();

        // Optional preselect for edit modal (?edit=<id>)
        $editServiceId = $request->get('edit');
        $editService = null;
        if ($editServiceId) {
            $editService = Product::services()
                ->where('id', $editServiceId)
                ->where('business_id', $business->id)
                ->first();
        }

        return view('app.services.index', compact(
            'business',
            'services',
            'categories',
            'units',
            'defaultUnit',
            'totalServicesCount',
            'editService',
            'editServiceId'
        ));
    }

    /**
     * Store a new service with streamlined, boomer-friendly inputs.
     */
    public function store(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'selling_price' => ['required', 'numeric', 'gte:0'],
            'base_cost' => ['nullable', 'numeric', 'gte:0'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'new_category_name' => ['nullable', 'string', 'max:100'],
            'output_unit_id' => ['nullable', 'exists:units,id'],
            'code' => [
                'nullable', 'string', 'max:100',
                Rule::unique('products', 'code')->where(fn ($query) => $query->where('business_id', $business->id)),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'show_in_website' => ['nullable', 'boolean'],
            'show_in_pos' => ['nullable', 'boolean'],
            'show_in_sales_order' => ['nullable', 'boolean'],
            'show_price_on_web' => ['nullable', 'boolean'],
        ]);

        // Auto create category if user typed a new category name directly
        $categoryId = $validated['category_id'] ?? null;
        if (! empty($validated['new_category_name'])) {
            $newCatName = trim((string) $validated['new_category_name']);
            $cat = ProductCategory::firstOrCreate(
                ['business_id' => $business->id, 'name' => $newCatName],
                ['slug' => Str::slug($newCatName)]
            );
            $categoryId = $cat->id;
        }

        // Determine output unit
        $outputUnitId = $validated['output_unit_id'] ?? null;
        if (! $outputUnitId) {
            $unit = Unit::whereIn('code', ['jasa', 'sesi', 'jam', 'paket', 'unit', 'pcs'])->first()
                ?? Unit::first();
            $outputUnitId = $unit?->id;
        }

        /** @var Product $service */
        $service = Product::create([
            'business_id' => $business->id,
            'type' => Product::TYPE_SERVICE,
            'category_id' => $categoryId,
            'output_unit_id' => $outputUnitId,
            'code' => $validated['code'] ?? null,
            'name' => trim((string) $validated['name']),
            'description' => $validated['description'] ?? null,
            'business_type_hint' => 'service',
            'selling_price' => (float) $validated['selling_price'],
            'base_cost' => (float) ($validated['base_cost'] ?? 0),
            'min_stock' => 0,
            'is_active' => true,
            'show_in_website' => $request->has('show_in_website') ? $request->boolean('show_in_website') : true,
            'show_in_pos' => $request->has('show_in_pos') ? $request->boolean('show_in_pos') : true,
            'show_in_sales_order' => $request->has('show_in_sales_order') ? $request->boolean('show_in_sales_order') : true,
            'show_price_on_web' => $request->has('show_price_on_web') ? $request->boolean('show_price_on_web') : true,
        ]);

        // Auto create service CostModel
        CostModel::firstOrCreate(
            ['business_id' => $business->id, 'product_id' => $service->id],
            [
                'name' => 'Model Tarif ' . $service->name,
                'method' => CostModel::METHOD_SERVICE,
                'is_active' => true,
            ]
        );

        return redirect()->route('services.index')->with('success', "Layanan '{$service->name}' berhasil ditambahkan dan siap digunakan di Kasir POS & Faktur.");
    }

    /**
     * Update an existing service.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($product->business_id === $business->id, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'selling_price' => ['required', 'numeric', 'gte:0'],
            'base_cost' => ['nullable', 'numeric', 'gte:0'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'new_category_name' => ['nullable', 'string', 'max:100'],
            'output_unit_id' => ['nullable', 'exists:units,id'],
            'code' => [
                'nullable', 'string', 'max:100',
                Rule::unique('products', 'code')->ignore($product->id)->where(fn ($query) => $query->where('business_id', $business->id)),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
            'show_in_website' => ['nullable', 'boolean'],
            'show_in_pos' => ['nullable', 'boolean'],
            'show_in_sales_order' => ['nullable', 'boolean'],
            'show_price_on_web' => ['nullable', 'boolean'],
        ]);

        $categoryId = $validated['category_id'] ?? $product->category_id;
        if (! empty($validated['new_category_name'])) {
            $newCatName = trim((string) $validated['new_category_name']);
            $cat = ProductCategory::firstOrCreate(
                ['business_id' => $business->id, 'name' => $newCatName],
                ['slug' => Str::slug($newCatName)]
            );
            $categoryId = $cat->id;
        }

        $product->update([
            'name' => trim((string) $validated['name']),
            'category_id' => $categoryId,
            'output_unit_id' => $validated['output_unit_id'] ?? $product->output_unit_id,
            'code' => $validated['code'] ?? $product->code,
            'description' => $validated['description'] ?? $product->description,
            'selling_price' => (float) $validated['selling_price'],
            'base_cost' => (float) ($validated['base_cost'] ?? $product->base_cost),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $product->is_active,
            'show_in_website' => $request->has('show_in_website') ? $request->boolean('show_in_website') : $product->show_in_website,
            'show_in_pos' => $request->has('show_in_pos') ? $request->boolean('show_in_pos') : $product->show_in_pos,
            'show_in_sales_order' => $request->has('show_in_sales_order') ? $request->boolean('show_in_sales_order') : $product->show_in_sales_order,
            'show_price_on_web' => $request->has('show_price_on_web') ? $request->boolean('show_price_on_web') : $product->show_price_on_web,
        ]);

        return redirect()->route('services.index')->with('success', "Layanan '{$product->name}' berhasil diperbarui.");
    }

    /**
     * Remove a service.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($product->business_id === $business->id, 403);

        $name = $product->name;
        $product->delete();

        return redirect()->route('services.index')->with('success', "Layanan '{$name}' telah dihapus.");
    }
}
