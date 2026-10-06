<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Pos;

use App\Domain\Pos\PosShiftService;
use App\Domain\Printer\PrinterManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pos\PosCloseShiftRequest;
use App\Http\Requests\Pos\PosOpenShiftRequest;
use App\Models\Location;
use App\Models\PosPrinter;
use App\Models\PosRegister;
use App\Models\PosShift;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PosShiftWebController extends Controller
{
    public function __construct(
        private readonly PosShiftService $shiftService = new PosShiftService,
        private readonly PrinterManager $printerManager = new PrinterManager
    ) {}

    /**
     * Display list of shifts.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $query = PosShift::where('business_id', $business->id)
            ->with(['user', 'location', 'register'])
            ->latest('opened_at');

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('date')) {
            $query->whereDate('opened_at', $request->get('date'));
        }

        $shifts = $query->paginate(15)->withQueryString();
        $locations = Location::where('business_id', $business->id)->where('is_active', true)->get();
        $registers = PosRegister::where('business_id', $business->id)->where('is_active', true)->get();
        $activeShift = $this->shiftService->getActiveShift($business, $user);

        return view('app.pos.shifts', compact('business', 'shifts', 'locations', 'registers', 'activeShift'));
    }

    /**
     * Open a new shift.
     */
    public function open(PosOpenShiftRequest $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $validated = $request->validated();

        $openingCash = (float) ($validated['opening_cash'] ?? 0.0);
        $openingDenominations = (array) ($validated['opening_denominations'] ?? []);

        $shift = $this->shiftService->openShift(
            business: $business,
            user: $user,
            openingCash: $openingCash,
            posRegisterId: $validated['pos_register_id'] ?? null,
            locationId: $validated['location_id'] ?? null,
            notes: $validated['notes'] ?? null,
            openingDenominations: $openingDenominations
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('pos.shift_opened', ['amount' => 'Rp ' . number_format((float) $shift->opening_cash, 0, ',', '.')]),
                'shift' => $shift->load(['user', 'location', 'register']),
            ]);
        }

        return redirect()->back()->with('success', __('pos.shift_opened', ['amount' => 'Rp ' . number_format((float) $shift->opening_cash, 0, ',', '.')]));
    }

    /**
     * Get live summary of shift for closing modal.
     */
    public function summary(PosShift $shift): JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless($shift->business_id === $business->id, 403);

        $isOwnerOrSupervisor = Context::isOwner() || Context::isAdminOrOwner() || Context::hasPermission('pos.supervisor_pin');

        $summary = $this->shiftService->getShiftSummary($shift);
        if (! $isOwnerOrSupervisor) {
            // Mask expected_cash and cash difference for regular cashiers to enforce Strict Blind Cash Count
            $summary['expected_cash'] = null;
            $summary['cash_difference'] = null;
        }

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'shift' => $shift->load(['user', 'location', 'register']),
            'is_blind_count' => ! $isOwnerOrSupervisor,
        ]);
    }

    /**
     * Close a shift and record cash reconciliation.
     */
    public function close(PosCloseShiftRequest $request, PosShift $shift): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless($shift->business_id === $business->id, 403);

        $validated = $request->validated();

        $actualCash = (float) ($validated['closing_cash_actual'] ?? 0.0);
        $closingDenominations = (array) ($validated['closing_denominations'] ?? []);

        $closed = $this->shiftService->closeShift(
            shift: $shift,
            actualCash: $actualCash,
            notes: $validated['notes'] ?? null,
            closingDenominations: $closingDenominations,
            cashierNotes: $validated['cashier_notes'] ?? null
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('pos.shift_closed'),
                'shift' => $closed->load(['user', 'location', 'register']),
            ]);
        }

        return redirect()->back()->with('success', __('pos.shift_closed'));
    }

    /**
     * Record cash in / cash out during shift.
     */
    public function recordCashMovement(Request $request, PosShift $shift): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless($shift->business_id === $business->id, 403);

        $user = auth()->user();

        $validated = $request->validate([
            'type' => ['required', 'in:cash_in,cash_out'],
            'amount' => ['required', 'numeric', 'min:1'],
            'category' => ['nullable', 'string', 'max:50'],
            'other_description' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $cat = $validated['category'] ?? null;
        if ($cat === 'other' && empty(trim($validated['other_description'] ?? '')) && empty(trim($validated['reason'] ?? ''))) {
            $msg = __('finance.category_other_required', [], null) ?: 'Keterangan rincian wajib diisi ketika kategori Lainnya dipilih.';
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                    'errors' => ['other_description' => [$msg]],
                ], 422);
            }
            return back()->withInput()->withErrors(['other_description' => $msg]);
        }

        $inputReason = trim($validated['reason'] ?? '');
        if ($cat === 'other') {
            $otherDesc = trim($validated['other_description'] ?? '') ?: $inputReason;
            $finalReason = $otherDesc ? "[Lainnya] {$otherDesc}" : '[Lainnya]';
        } elseif ($cat) {
            $label = $validated['type'] === 'cash_in'
                ? (__("finance.inflow_categories.{$cat}") !== "finance.inflow_categories.{$cat}" ? __("finance.inflow_categories.{$cat}") : ucfirst(str_replace('_', ' ', $cat)))
                : (__("finance.categories.{$cat}") !== "finance.categories.{$cat}" ? __("finance.categories.{$cat}") : ucfirst(str_replace('_', ' ', $cat)));
            $finalReason = $inputReason ? "[{$label}] {$inputReason}" : "[{$label}]";
        } else {
            $finalReason = $inputReason ?: ($validated['type'] === 'cash_in' ? 'Kas Masuk' : 'Kas Keluar');
        }

        $movement = $this->shiftService->recordCashMovement(
            shift: $shift,
            user: $user,
            type: $validated['type'],
            amount: (float) $validated['amount'],
            reason: $finalReason,
            notes: $validated['notes'] ?? null
        );

        $label = $validated['type'] === 'cash_in' ? __('pos.movement_cash_in') : __('pos.movement_cash_out');
        $formattedAmount = number_format($movement->amount, 0, ',', '.');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('pos.movement_recorded_success', ['type' => $label, 'amount' => $formattedAmount]),
                'movement' => $movement,
            ]);
        }

        return redirect()->back()->with('success', __('pos.movement_recorded_flash', ['type' => $label]));
    }

    /**
     * Direct print shift summary report to ESC/POS thermal printer.
     */
    public function printSummary(Request $request, PosShift $shift): JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless($shift->business_id === $business->id, 403);

        $printerId = $request->input('printer_id');
        $printer = $printerId ? PosPrinter::where('business_id', $business->id)->find($printerId) : null;

        $result = $this->printerManager->printCashierShift($shift, $printer);

        return response()->json($result);
    }
}
