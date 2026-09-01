<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Pos;

use App\Domain\Pos\PosShiftService;
use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\PosRegister;
use App\Models\PosShift;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PosShiftController extends Controller
{
    public function __construct(
        private readonly PosShiftService $shiftService = new PosShiftService
    ) {}

    /**
     * List cashier shifts with pagination and filters.
     */
    public function index(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $query = PosShift::where('business_id', $business->id)
            ->with(['user:id,name,email', 'location:id,name', 'register:id,name'])
            ->latest('opened_at');

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('date')) {
            $query->whereDate('opened_at', $request->get('date'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }

        $shifts = $query->paginate(20);

        return response()->json([
            'shifts' => $shifts->items(),
            'pagination' => [
                'current_page' => $shifts->currentPage(),
                'last_page' => $shifts->lastPage(),
                'per_page' => $shifts->perPage(),
                'total' => $shifts->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Get cashier's currently active shift.
     */
    public function current(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();
        $locationId = $request->query('location_id');

        $activeShift = $this->shiftService->getActiveShift($business, $user, $locationId);

        if (! $activeShift) {
            return response()->json([
                'active_shift' => null,
                'message' => 'Tidak ada shift kasir yang aktif saat ini.',
            ], Response::HTTP_OK);
        }

        $summary = $this->shiftService->getShiftSummary($activeShift);

        return response()->json([
            'active_shift' => $activeShift->load(['user:id,name', 'location:id,name']),
            'summary' => $summary,
        ], Response::HTTP_OK);
    }

    /**
     * Open a new cashier shift with opening cash balance.
     */
    public function open(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        $validated = $request->validate([
            'opening_cash' => ['required', 'numeric', 'min:0'],
            'location_id' => ['nullable', 'string', 'exists:locations,id'],
            'pos_register_id' => ['nullable', 'string', 'exists:pos_registers,id'],
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

        return response()->json([
            'message' => 'Shift kasir berhasil dibuka dengan modal awal Rp ' . number_format((float) $shift->opening_cash, 0, ',', '.'),
            'shift' => $shift->load(['user:id,name', 'location:id,name']),
        ], Response::HTTP_CREATED);
    }

    /**
     * Get live summary of shift for closing modal.
     */
    public function summary(Request $request, PosShift $posShift): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($posShift->business_id !== $business->id) {
            return response()->json(['message' => 'Shift tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $summary = $this->shiftService->getShiftSummary($posShift);

        return response()->json([
            'shift' => $posShift->load(['user:id,name', 'location:id,name']),
            'summary' => $summary,
        ], Response::HTTP_OK);
    }

    /**
     * Close a shift and record cash reconciliation.
     */
    public function close(Request $request, PosShift $posShift): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($posShift->business_id !== $business->id) {
            return response()->json(['message' => 'Shift tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'closing_cash_actual' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $closed = $this->shiftService->closeShift(
            shift: $posShift,
            actualCash: (float) $validated['closing_cash_actual'],
            notes: $validated['notes'] ?? null
        );

        return response()->json([
            'message' => 'Shift kasir berhasil ditutup dan direkonsiliasi.',
            'shift' => $closed,
        ], Response::HTTP_OK);
    }

    /**
     * Record cash in / cash out during shift.
     */
    public function recordCashMovement(Request $request, PosShift $posShift): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($posShift->business_id !== $business->id) {
            return response()->json(['message' => 'Shift tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $user = $request->user();

        $validated = $request->validate([
            'type' => ['required', 'in:cash_in,cash_out'],
            'amount' => ['required', 'numeric', 'min:1'],
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $movement = $this->shiftService->recordCashMovement(
            shift: $posShift,
            user: $user,
            type: $validated['type'],
            amount: (float) $validated['amount'],
            reason: $validated['reason'],
            notes: $validated['notes'] ?? null
        );

        $label = $validated['type'] === 'cash_in' ? 'Kas Masuk' : 'Kas Keluar';

        return response()->json([
            'message' => "{$label} sebesar Rp " . number_format((float) $movement->amount, 0, ',', '.') . ' berhasil dicatat.',
            'movement' => $movement,
        ], Response::HTTP_CREATED);
    }
}
