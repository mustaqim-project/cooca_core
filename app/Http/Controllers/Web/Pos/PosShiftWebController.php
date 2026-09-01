<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Pos;

use App\Domain\Pos\PosShiftService;
use App\Http\Controllers\Controller;
use App\Models\Location;
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
        private readonly PosShiftService $shiftService = new PosShiftService
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
    public function open(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $validated = $request->validate([
            'opening_cash' => ['required', 'numeric', 'min:0'],
            'location_id' => ['nullable', 'string'],
            'pos_register_id' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $shift = $this->shiftService->openShift(
            business: $business,
            user: $user,
            openingCash: (float) $validated['opening_cash'],
            posRegisterId: $validated['pos_register_id'] ?? null,
            locationId: $validated['location_id'] ?? null,
            notes: $validated['notes'] ?? null
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Shift kasir berhasil dibuka dengan modal awal Rp ' . number_format($shift->opening_cash, 0, ',', '.'),
                'shift' => $shift,
            ]);
        }

        return redirect()->back()->with('success', 'Shift kasir berhasil dibuka!');
    }

    /**
     * Get live summary of shift for closing modal.
     */
    public function summary(PosShift $shift): JsonResponse
    {
        $summary = $this->shiftService->getShiftSummary($shift);
        return response()->json(['success' => true, 'summary' => $summary]);
    }

    /**
     * Close a shift and record cash reconciliation.
     */
    public function close(Request $request, PosShift $shift): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'closing_cash_actual' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $closed = $this->shiftService->closeShift(
            shift: $shift,
            actualCash: (float) $validated['closing_cash_actual'],
            notes: $validated['notes'] ?? null
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Shift kasir berhasil ditutup dan direkonsiliasi.',
                'shift' => $closed,
            ]);
        }

        return redirect()->back()->with('success', 'Shift kasir berhasil ditutup!');
    }

    /**
     * Record cash in / cash out during shift.
     */
    public function recordCashMovement(Request $request, PosShift $shift): RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'type' => ['required', 'in:cash_in,cash_out'],
            'amount' => ['required', 'numeric', 'min:1'],
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $movement = $this->shiftService->recordCashMovement(
            shift: $shift,
            user: $user,
            type: $validated['type'],
            amount: (float) $validated['amount'],
            reason: $validated['reason'],
            notes: $validated['notes'] ?? null
        );

        $label = $validated['type'] === 'cash_in' ? 'Kas Masuk' : 'Kas Keluar';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$label} sebesar Rp " . number_format($movement->amount, 0, ',', '.') . " berhasil dicatat.",
                'movement' => $movement,
            ]);
        }

        return redirect()->back()->with('success', "{$label} berhasil dicatat!");
    }
}
