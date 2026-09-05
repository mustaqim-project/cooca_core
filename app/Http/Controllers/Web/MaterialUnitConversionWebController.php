<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialUnitConversion;
use App\Models\Supplier;
use App\Models\Unit;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class MaterialUnitConversionWebController extends Controller
{
    /**
     * Store a business-scoped material conversion mapping.
     */
    public function store(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'material_id' => ['required', 'string', 'exists:materials,id'],
            'supplier_id' => ['nullable', 'string', 'exists:suppliers,id'],
            'from_unit_id' => ['required', 'string', 'exists:units,id'],
            'to_unit_id' => ['required', 'string', 'exists:units,id', 'different:from_unit_id'],
            'factor' => ['required', 'numeric', 'gt:0'],
            'effective_from' => ['required', 'date'],
            'effective_until' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $material = Material::where('business_id', $business->id)->findOrFail($validated['material_id']);
        $fromUnit = Unit::where('business_id', $business->id)->orWhereNull('business_id')->findOrFail($validated['from_unit_id']);
        $toUnit = Unit::where('business_id', $business->id)->orWhereNull('business_id')->findOrFail($validated['to_unit_id']);

        if ($fromUnit->category !== $toUnit->category && $fromUnit->category !== Unit::CATEGORY_CUSTOM && $toUnit->category !== Unit::CATEGORY_CUSTOM) {
            return back()->withErrors([
                'material_id' => 'Konversi antar kategori yang berbeda hanya boleh dibuat lewat bridge custom.',
            ]);
        }

        if (! empty($validated['supplier_id'])) {
            $supplier = Supplier::where('business_id', $business->id)->findOrFail($validated['supplier_id']);
            $validated['supplier_id'] = $supplier->id;
        }

        MaterialUnitConversion::updateOrCreate(
            [
                'business_id' => $business->id,
                'material_id' => $material->id,
                'supplier_id' => $validated['supplier_id'] ?? null,
                'from_unit_id' => $fromUnit->id,
                'to_unit_id' => $toUnit->id,
            ],
            [
                'factor' => (float) $validated['factor'],
                'is_default' => (bool) ($validated['is_default'] ?? false),
                'effective_from' => $validated['effective_from'],
                'effective_until' => $validated['effective_until'] ?? null,
            ]
        );

        return back()->with('success', 'Konversi satuan material berhasil disimpan.');
    }

    /**
     * Delete a business-scoped material conversion mapping.
     */
    public function destroy(MaterialUnitConversion $materialUnitConversion): RedirectResponse
    {
        $business = Context::requireBusiness();

        if ($materialUnitConversion->business_id !== $business->id) {
            return back()->with('error', 'Konversi ini bukan milik bisnis Anda.');
        }

        $materialUnitConversion->delete();

        return back()->with('success', 'Konversi satuan material berhasil dihapus.');
    }
}
