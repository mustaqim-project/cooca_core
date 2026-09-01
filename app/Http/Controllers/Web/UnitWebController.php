<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class UnitWebController extends Controller
{
    /**
     * Store a new custom unit of measurement (via regular form or AJAX modal).
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30'],
            'name' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string', 'in:weight,volume,length,quantity,time,area,custom'],
            'default_precision' => ['nullable', 'integer', 'min:0', 'max:6'],
        ]);

        $unit = Unit::create([
            'business_id' => $business->id,
            'code' => $validated['code'],
            'name' => $validated['name'],
            'category' => $validated['category'],
            'is_base' => false,
            'default_precision' => (int) ($validated['default_precision'] ?? 0),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Satuan output baru berhasil ditambahkan.',
                'unit' => $unit,
            ], 201);
        }

        return back()->with('success', 'Satuan output baru berhasil ditambahkan.');
    }

    /**
     * Update an existing custom unit.
     */
    public function update(Request $request, Unit $unit): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();

        if ($unit->business_id !== $business->id) {
            return back()->with('error', 'Satuan standar sistem tidak dapat diedit.');
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30'],
            'name' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string', 'in:weight,volume,length,quantity,time,area,custom'],
            'default_precision' => ['nullable', 'integer', 'min:0', 'max:6'],
        ]);

        $unit->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Satuan berhasil diperbarui.',
                'unit' => $unit,
            ]);
        }

        return back()->with('success', 'Satuan berhasil diperbarui.');
    }

    /**
     * Delete a custom unit (only tenant-owned).
     */
    public function destroy(Unit $unit): RedirectResponse
    {
        $business = Context::requireBusiness();

        if ($unit->business_id !== $business->id) {
            return back()->with('error', 'Satuan standar sistem tidak dapat dihapus.');
        }

        $unit->delete();

        return back()->with('success', 'Satuan berhasil dihapus.');
    }
}
