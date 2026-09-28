<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Finance;

use App\Domain\Finance\StoreEdcTerminalService;
use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\StoreEdcTerminal;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

final class StoreEdcTerminalWebController extends Controller
{
    public function __construct(
        private readonly StoreEdcTerminalService $service = new StoreEdcTerminalService
    ) {}

    public function index(Request $request): View
    {
        $business = Context::requireBusiness();
        $locationId = $request->get('location_id');

        $query = StoreEdcTerminal::where('business_id', $business->id)->with('location')->latest();
        if (! empty($locationId)) {
            $query->where('location_id', $locationId);
        }

        $terminals = $query->get();
        $locations = Location::where('business_id', $business->id)->where('is_active', true)->orderBy('name')->get();

        return view('app.finance.edc-terminals.index', compact(
            'business',
            'terminals',
            'locations',
            'locationId'
        ));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:50'],
            'terminal_name' => ['required', 'string', 'max:100'],
            'terminal_id_tid' => ['required', 'string', 'max:50'],
            'merchant_id_mid' => ['nullable', 'string', 'max:50'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'mdr_debit_percent' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'mdr_credit_percent' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'settlement_account_info' => ['nullable', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        try {
            $terminal = $this->service->registerTerminal($business, $validated);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mesin EDC berhasil didaftarkan.',
                    'terminal' => $terminal,
                ]);
            }

            return redirect()->route('finance.edc-terminals.index')
                ->with('success', "Mesin EDC {$terminal->bank_name} ({$terminal->terminal_name} - TID: {$terminal->terminal_id_tid}) berhasil didaftarkan.");
        } catch (Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->withInput()->withErrors(['terminal_id_tid' => $e->getMessage()]);
        }
    }

    public function toggle(StoreEdcTerminal $terminal): RedirectResponse
    {
        $business = Context::requireBusiness();
        if ($terminal->business_id !== $business->id) {
            abort(403);
        }

        $this->service->toggleActive($terminal);

        return back()->with('success', "Status aktif mesin EDC {$terminal->terminal_name} berhasil diperbarui.");
    }

    public function destroy(StoreEdcTerminal $terminal): RedirectResponse
    {
        $business = Context::requireBusiness();
        if ($terminal->business_id !== $business->id) {
            abort(403);
        }

        $this->service->deleteTerminal($terminal);

        return back()->with('success', 'Mesin EDC berhasil dihapus.');
    }
}
