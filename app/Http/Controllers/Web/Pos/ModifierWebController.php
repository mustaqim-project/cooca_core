<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Pos;

use App\Domain\Pos\ModifierService;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Material;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\Product;
use App\Models\Unit;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

final class ModifierWebController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('require.permission:pos.modifiers'),
        ];
    }

    public function __construct(
        private readonly ModifierService $modifierService = new ModifierService
    ) {}

    /**
     * Display modifier groups management dashboard.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();

        $groups = $this->modifierService->getGroups($business);
        $products = Product::where('business_id', $business->id)->where('is_active', true)->orderBy('name')->get();
        $materials = Material::where('business_id', $business->id)->whereNull('discontinued_at')->with('unit')->orderBy('name')->get();
        $units = Unit::all();

        return view('app.products.modifiers', compact('business', 'groups', 'products', 'materials', 'units'));
    }

    /**
     * Store a new modifier group.
     */
    public function storeGroup(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();

        if ($request->has('is_required')) {
            $request->merge(['is_required' => filter_var($request->input('is_required'), FILTER_VALIDATE_BOOLEAN)]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:255'],
            'selection_type' => ['required', 'string', 'in:single,multiple'],
            'min_selection' => ['required', 'integer', 'min:0'],
            'max_selection' => ['required', 'integer', 'min:1'],
            'is_required' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => [
                'string',
                Rule::exists('products', 'id')->where('business_id', $business->id),
            ],
        ]);

        try {
            $group = $this->modifierService->createGroup($business, $validated);

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => $request->user()?->id,
                'auditable_type' => ModifierGroup::class,
                'auditable_id' => $group->id,
                'action' => 'modifier_group.created',
                'risk_level' => AuditLog::RISK_LOW,
                'new_values' => ['name' => $group->name, 'selection_type' => $group->selection_type],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => __('products.group_created_success'), 'group' => $group]);
            }

            return redirect()->route('pos.modifiers.index')->with('success', __('products.group_created_success'));
        } catch (Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Update an existing modifier group.
     */
    public function updateGroup(Request $request, ModifierGroup $group): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        if ($group->business_id !== $business->id) {
            abort(403);
        }

        if ($request->has('is_required')) {
            $request->merge(['is_required' => filter_var($request->input('is_required'), FILTER_VALIDATE_BOOLEAN)]);
        }
        if ($request->has('is_active')) {
            $request->merge(['is_active' => filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN)]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:255'],
            'selection_type' => ['required', 'string', 'in:single,multiple'],
            'min_selection' => ['required', 'integer', 'min:0'],
            'max_selection' => ['required', 'integer', 'min:1'],
            'is_required' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => [
                'string',
                Rule::exists('products', 'id')->where('business_id', $business->id),
            ],
        ]);

        try {
            $this->modifierService->updateGroup($group, $validated);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => __('products.group_updated_success'), 'group' => $group]);
            }

            return redirect()->route('pos.modifiers.index')->with('success', __('products.group_updated_success'));
        } catch (Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete a modifier group.
     */
    public function destroyGroup(Request $request, ModifierGroup $group): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        if ($group->business_id !== $business->id) {
            abort(403);
        }

        try {
            $groupName = $group->name;
            $groupId = $group->id;

            $this->modifierService->deleteGroup($group);

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => $request->user()?->id,
                'auditable_type' => ModifierGroup::class,
                'auditable_id' => $groupId,
                'action' => 'modifier_group.deleted',
                'risk_level' => AuditLog::RISK_MEDIUM,
                'risk_reason' => 'Penghapusan grup modifier / varian produk',
                'old_values' => ['name' => $groupName],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => __('products.group_deleted_success')]);
            }

            return redirect()->route('pos.modifiers.index')->with('success', __('products.group_deleted_success'));
        } catch (Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Add an option to a modifier group.
     */
    public function storeOption(Request $request, ModifierGroup $group): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        if ($group->business_id !== $business->id) {
            abort(403);
        }

        $affectsMaterial = filter_var($request->input('affects_material', false), FILTER_VALIDATE_BOOLEAN);

        // Sanitize materials array
        $rawMaterials = $request->input('materials');
        $filteredMaterials = [];
        if ($affectsMaterial && is_array($rawMaterials)) {
            foreach ($rawMaterials as $item) {
                if (is_array($item) && ! empty($item['material_id'])) {
                    $filteredMaterials[] = [
                        'material_id' => (string) $item['material_id'],
                        'quantity' => (float) ($item['quantity'] ?? 1),
                        'unit_id' => ! empty($item['unit_id']) ? (string) $item['unit_id'] : null,
                    ];
                }
            }
        }

        $request->merge([
            'affects_material' => $affectsMaterial,
            'materials' => $filteredMaterials,
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'price_delta' => ['nullable', 'numeric', 'min:0'],
            'affects_material' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
            'materials' => ['nullable', 'array'],
            'materials.*.material_id' => [
                'required',
                'string',
                Rule::exists('materials', 'id')->where('business_id', $business->id),
            ],
            'materials.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'materials.*.unit_id' => ['nullable', 'string', 'exists:units,id'],
        ]);

        $validated['affects_material'] = $affectsMaterial;
        $validated['materials'] = $filteredMaterials;

        try {
            $option = $this->modifierService->addOption($group, $validated);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => __('products.option_created_success'), 'option' => $option]);
            }

            return redirect()->route('pos.modifiers.index')->with('success', __('products.option_created_success'));
        } catch (Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Update an option.
     */
    public function updateOption(Request $request, ModifierOption $option): RedirectResponse|JsonResponse
    {
        $group = $option->group;
        $business = Context::requireBusiness();
        if (! $group || $group->business_id !== $business->id) {
            abort(403);
        }

        $oldPriceDelta = (float) $option->price_delta;
        $affectsMaterial = filter_var($request->input('affects_material', false), FILTER_VALIDATE_BOOLEAN);

        // Sanitize materials array
        $rawMaterials = $request->input('materials');
        $filteredMaterials = [];
        if ($affectsMaterial && is_array($rawMaterials)) {
            foreach ($rawMaterials as $item) {
                if (is_array($item) && ! empty($item['material_id'])) {
                    $filteredMaterials[] = [
                        'material_id' => (string) $item['material_id'],
                        'quantity' => (float) ($item['quantity'] ?? 1),
                        'unit_id' => ! empty($item['unit_id']) ? (string) $item['unit_id'] : null,
                    ];
                }
            }
        }

        $request->merge([
            'affects_material' => $affectsMaterial,
            'materials' => $filteredMaterials,
            'is_active' => $request->has('is_active') ? filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN) : true,
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'price_delta' => ['nullable', 'numeric', 'min:0'],
            'affects_material' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
            'materials' => ['nullable', 'array'],
            'materials.*.material_id' => [
                'required',
                'string',
                Rule::exists('materials', 'id')->where('business_id', $business->id),
            ],
            'materials.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'materials.*.unit_id' => ['nullable', 'string', 'exists:units,id'],
        ]);

        $validated['affects_material'] = $affectsMaterial;
        $validated['materials'] = $filteredMaterials;

        try {
            $this->modifierService->updateOption($option, $validated);

            $newPriceDelta = (float) ($validated['price_delta'] ?? 0);
            if ($oldPriceDelta !== $newPriceDelta) {
                AuditLog::create([
                    'business_id' => $business->id,
                    'user_id' => $request->user()?->id,
                    'auditable_type' => ModifierOption::class,
                    'auditable_id' => $option->id,
                    'action' => 'modifier_option.price_delta_updated',
                    'risk_level' => AuditLog::RISK_LOW,
                    'old_values' => ['price_delta' => $oldPriceDelta],
                    'new_values' => ['price_delta' => $newPriceDelta],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => now(),
                ]);
            }

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => __('products.option_updated_success'), 'option' => $option]);
            }

            return redirect()->route('pos.modifiers.index')->with('success', __('products.option_updated_success'));
        } catch (Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete an option.
     */
    public function destroyOption(Request $request, ModifierOption $option): RedirectResponse|JsonResponse
    {
        $group = $option->group;
        $business = Context::requireBusiness();
        if (! $group || $group->business_id !== $business->id) {
            abort(403);
        }

        try {
            $optionName = $option->name;
            $optionId = $option->id;
            $priceDelta = (float) $option->price_delta;

            $this->modifierService->deleteOption($option);

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => $request->user()?->id,
                'auditable_type' => ModifierOption::class,
                'auditable_id' => $optionId,
                'action' => 'modifier_option.deleted',
                'risk_level' => AuditLog::RISK_MEDIUM,
                'risk_reason' => 'Penghapusan opsi varian modifier',
                'old_values' => ['name' => $optionName, 'price_delta' => $priceDelta],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => __('products.option_deleted_success')]);
            }

            return redirect()->route('pos.modifiers.index')->with('success', __('products.option_deleted_success'));
        } catch (Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
