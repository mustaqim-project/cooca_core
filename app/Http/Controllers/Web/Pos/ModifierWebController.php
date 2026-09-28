<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Pos;

use App\Domain\Pos\ModifierService;
use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\Product;
use App\Models\Unit;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

final class ModifierWebController extends Controller
{
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
            'product_ids.*' => ['string', 'exists:products,id'],
        ]);

        try {
            $group = $this->modifierService->createGroup($business, $validated);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Grup modifier berhasil dibuat.', 'group' => $group]);
            }

            return redirect()->route('pos.modifiers.index')->with('success', "Grup modifier '{$group->name}' berhasil dibuat.");
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
            'product_ids.*' => ['string', 'exists:products,id'],
        ]);

        try {
            $this->modifierService->updateGroup($group, $validated);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Grup modifier berhasil diperbarui.', 'group' => $group]);
            }

            return redirect()->route('pos.modifiers.index')->with('success', "Grup modifier '{$group->name}' berhasil diperbarui.");
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
            $this->modifierService->deleteGroup($group);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Grup modifier berhasil dihapus.']);
            }

            return redirect()->route('pos.modifiers.index')->with('success', 'Grup modifier berhasil dihapus.');
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
            'materials.*.material_id' => ['required', 'string', 'exists:materials,id'],
            'materials.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'materials.*.unit_id' => ['nullable', 'string', 'exists:units,id'],
        ]);

        $validated['affects_material'] = $affectsMaterial;
        $validated['materials'] = $filteredMaterials;

        try {
            $option = $this->modifierService->addOption($group, $validated);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Opsi varian berhasil ditambahkan.', 'option' => $option]);
            }

            return redirect()->route('pos.modifiers.index')->with('success', "Opsi '{$option->name}' berhasil ditambahkan.");
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
            'materials.*.material_id' => ['required', 'string', 'exists:materials,id'],
            'materials.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'materials.*.unit_id' => ['nullable', 'string', 'exists:units,id'],
        ]);

        $validated['affects_material'] = $affectsMaterial;
        $validated['materials'] = $filteredMaterials;

        try {
            $this->modifierService->updateOption($option, $validated);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Opsi varian berhasil diperbarui.', 'option' => $option]);
            }

            return redirect()->route('pos.modifiers.index')->with('success', "Opsi '{$option->name}' berhasil diperbarui.");
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
            $this->modifierService->deleteOption($option);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Opsi varian berhasil dihapus.']);
            }

            return redirect()->route('pos.modifiers.index')->with('success', 'Opsi varian berhasil dihapus.');
        } catch (Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
