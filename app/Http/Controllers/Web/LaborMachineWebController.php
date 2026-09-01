<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Labor\LaborCostService;
use App\Domain\Machine\MachineCostService;
use App\Http\Controllers\Controller;
use App\Models\LaborRate;
use App\Models\Machine;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class LaborMachineWebController extends Controller
{
    public function __construct(
        private readonly LaborCostService $laborService = new LaborCostService,
        private readonly MachineCostService $machineService = new MachineCostService
    ) {}

    /**
     * Show labor rates and machines management.
     */
    public function index(): View
    {
        $business = Context::requireBusiness();

        $laborRates = LaborRate::latest()->get();
        $machines = Machine::latest()->get();

        return view('app.labor-machines.index', compact('business', 'laborRates', 'machines'));
    }

    /**
     * Store new labor rate.
     */
    public function storeLabor(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'basis' => ['required', 'string', 'in:hourly,daily,monthly,per_unit,per_project,per_task'],
            'rate_amount' => ['required', 'numeric', 'gt:0'],
            'working_days_per_month' => ['nullable', 'numeric', 'gt:0'],
            'working_hours_per_day' => ['nullable', 'numeric', 'gt:0'],
            'utilization_percentage' => ['nullable', 'numeric', 'gt:0', 'lte:100'],
            'is_subcontractor' => ['nullable', 'boolean'],
        ]);

        LaborRate::create([
            'business_id' => $business->id,
            'name' => $validated['name'],
            'basis' => $validated['basis'],
            'rate_amount' => (float) $validated['rate_amount'],
            'working_days_per_month' => (int) ($validated['working_days_per_month'] ?? 22),
            'working_hours_per_day' => (float) ($validated['working_hours_per_day'] ?? 8),
            'utilization_rate' => (float) ($validated['utilization_percentage'] ?? 80),
            'is_subcontractor' => (bool) ($validated['is_subcontractor'] ?? false),
        ]);

        return redirect()->route('labor-machines.index')->with('success', 'Tarif tenaga kerja berhasil ditambahkan.');
    }

    /**
     * Update an existing labor rate.
     */
    public function updateLabor(Request $request, LaborRate $laborRate): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'basis' => ['required', 'string', 'in:hourly,daily,monthly,per_unit,per_project,per_task'],
            'rate_amount' => ['required', 'numeric', 'gt:0'],
            'working_days_per_month' => ['nullable', 'numeric', 'gt:0'],
            'working_hours_per_day' => ['nullable', 'numeric', 'gt:0'],
            'utilization_percentage' => ['nullable', 'numeric', 'gt:0', 'lte:100'],
            'is_subcontractor' => ['nullable', 'boolean'],
        ]);

        $laborRate->update([
            'name' => $validated['name'],
            'basis' => $validated['basis'],
            'rate_amount' => (float) $validated['rate_amount'],
            'working_days_per_month' => (int) ($validated['working_days_per_month'] ?? 22),
            'working_hours_per_day' => (float) ($validated['working_hours_per_day'] ?? 8),
            'utilization_rate' => (float) ($validated['utilization_percentage'] ?? 80),
            'is_subcontractor' => (bool) ($validated['is_subcontractor'] ?? false),
        ]);

        return redirect()->route('labor-machines.index')->with('success', 'Tarif tenaga kerja berhasil diperbarui.');
    }

    /**
     * Store new machine.
     */
    public function storeMachine(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'purchase_price' => ['required', 'numeric', 'gt:0'],
            'useful_life_hours' => ['required', 'numeric', 'gt:0'],
            'power_kw' => ['nullable', 'numeric', 'gte:0'],
            'electricity_cost_per_kwh' => ['nullable', 'numeric', 'gte:0'],
            'maintenance_cost_per_hour' => ['nullable', 'numeric', 'gte:0'],
            'salvage_value' => ['nullable', 'numeric', 'gte:0'],
        ]);

        $powerKw = (float) ($validated['power_kw'] ?? 0);
        $elecRate = (float) ($validated['electricity_cost_per_kwh'] ?? 1500);

        Machine::create([
            'business_id' => $business->id,
            'name' => $validated['name'],
            'purchase_price' => (float) $validated['purchase_price'],
            'residual_value' => (float) ($validated['salvage_value'] ?? 0),
            'useful_life_hours' => (float) $validated['useful_life_hours'],
            'maintenance_cost_per_hour' => (float) ($validated['maintenance_cost_per_hour'] ?? 0),
            'electricity_cost_per_hour' => $powerKw * $elecRate,
        ]);

        return redirect()->route('labor-machines.index')->with('success', 'Mesin / Peralatan berhasil ditambahkan.');
    }

    /**
     * Update an existing machine.
     */
    public function updateMachine(Request $request, Machine $machine): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'purchase_price' => ['required', 'numeric', 'gt:0'],
            'useful_life_hours' => ['required', 'numeric', 'gt:0'],
            'power_kw' => ['nullable', 'numeric', 'gte:0'],
            'electricity_cost_per_kwh' => ['nullable', 'numeric', 'gte:0'],
            'maintenance_cost_per_hour' => ['nullable', 'numeric', 'gte:0'],
            'salvage_value' => ['nullable', 'numeric', 'gte:0'],
        ]);

        $powerKw = (float) ($validated['power_kw'] ?? 0);
        $elecRate = (float) ($validated['electricity_cost_per_kwh'] ?? 1500);

        $machine->update([
            'name' => $validated['name'],
            'purchase_price' => (float) $validated['purchase_price'],
            'residual_value' => (float) ($validated['salvage_value'] ?? 0),
            'useful_life_hours' => (float) $validated['useful_life_hours'],
            'maintenance_cost_per_hour' => (float) ($validated['maintenance_cost_per_hour'] ?? 0),
            'electricity_cost_per_hour' => $powerKw * $elecRate,
        ]);

        return redirect()->route('labor-machines.index')->with('success', 'Data mesin berhasil diperbarui.');
    }

    /**
     * Delete labor rate.
     */
    public function destroyLabor(LaborRate $laborRate): RedirectResponse
    {
        $laborRate->delete();

        return redirect()->route('labor-machines.index')->with('success', 'Tarif tenaga kerja berhasil dihapus.');
    }

    /**
     * Delete machine.
     */
    public function destroyMachine(Machine $machine): RedirectResponse
    {
        $machine->delete();

        return redirect()->route('labor-machines.index')->with('success', 'Mesin / Peralatan berhasil dihapus.');
    }
}
