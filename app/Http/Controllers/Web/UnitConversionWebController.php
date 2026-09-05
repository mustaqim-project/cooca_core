<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\UnitConversion;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class UnitConversionWebController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'from_unit_id' => ['required', 'string', 'exists:units,id'],
            'to_unit_id' => ['required', 'string', 'exists:units,id', 'different:from_unit_id'],
            'factor' => ['required', 'numeric', 'gt:0'],
        ]);

        $fromUnit = Unit::where(function ($query) use ($business): void {
            $query->whereNull('business_id')
                ->orWhere('business_id', $business->id);
        })->findOrFail($validated['from_unit_id']);

        $toUnit = Unit::where(function ($query) use ($business): void {
            $query->whereNull('business_id')
                ->orWhere('business_id', $business->id);
        })->findOrFail($validated['to_unit_id']);

        if ($fromUnit->category !== $toUnit->category && $fromUnit->category !== Unit::CATEGORY_CUSTOM && $toUnit->category !== Unit::CATEGORY_CUSTOM) {
            return back()->withErrors([
                'from_unit_id' => 'Konversi hanya boleh antar satuan dengan kategori yang sama, atau salah satunya kategori kustom.',
            ]);
        }

        UnitConversion::updateOrCreate(
            [
                'business_id' => $business->id,
                'from_unit_id' => $fromUnit->id,
                'to_unit_id' => $toUnit->id,
            ],
            [
                'factor' => $validated['factor'],
            ]
        );

        return back()->with('success', 'Konversi satuan berhasil disimpan.');
    }

    public function destroy(UnitConversion $unitConversion): RedirectResponse
    {
        $business = Context::requireBusiness();

        if ($unitConversion->business_id !== $business->id) {
            return back()->with('error', 'Anda tidak memiliki izin untuk menghapus konversi ini.');
        }

        $unitConversion->delete();

        return back()->with('success', 'Konversi satuan berhasil dihapus.');
    }
}
