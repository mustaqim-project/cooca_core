<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Pos;

use App\Domain\Pos\PosShiftService;
use App\Domain\Printer\PrinterManager;
use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\PosPrinter;
use App\Models\PosRegister;
use App\Models\PosShift;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PosShiftController extends Controller
{
    public function __construct(
        private readonly PosShiftService $shiftService = new PosShiftService,
        private readonly PrinterManager $printerManager = new PrinterManager
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
                'success' => true,
                'active_shift' => null,
                'shift' => null,
                'data' => null,
                'message' => 'Tidak ada shift kasir yang aktif saat ini.',
            ], Response::HTTP_OK);
        }

        $summary = $this->shiftService->getShiftSummary($activeShift);
        $loadedShift = $activeShift->load(['user:id,name', 'location:id,name', 'register:id,name']);

        return response()->json([
            'success' => true,
            'active_shift' => $loadedShift,
            'shift' => $loadedShift,
            'data' => [
                'id' => $loadedShift->id,
                'cashier_name' => $loadedShift->user?->name ?? $user?->name ?? 'Kasir',
                'location_name' => $loadedShift->location?->name ?? 'Utama',
                'opening_cash' => (float) $loadedShift->opening_cash,
                'status' => $loadedShift->status,
                'opened_at' => $loadedShift->opened_at,
                'total_cash_sales' => (float) $loadedShift->total_cash_sales,
                'total_non_cash_sales' => (float) $loadedShift->total_non_cash_sales,
                'expected_cash' => (float) ($loadedShift->opening_cash + $loadedShift->total_cash_sales),
            ],
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
            'opening_cash' => ['nullable', 'numeric', 'min:0'],
            'location_id' => ['nullable', 'string', 'exists:locations,id'],
            'pos_register_id' => ['nullable', 'string', 'exists:pos_registers,id'],
            'notes' => ['nullable', 'string', 'max:255'],
            'opening_denominations' => ['nullable', 'array'],
        ]);

        $openingCash = (float) ($validated['opening_cash'] ?? 0.0);
        $openingDenominations = (array) ($validated['opening_denominations'] ?? []);

        $locationId = $validated['location_id'] ?? null;
        if (! $locationId) {
            $locationId = Location::where('business_id', $business->id)->where('is_primary', true)->value('id')
                ?? Location::where('business_id', $business->id)->value('id');
        }

        $shift = $this->shiftService->openShift(
            business: $business,
            user: $user,
            openingCash: $openingCash,
            posRegisterId: $validated['pos_register_id'] ?? null,
            locationId: $locationId,
            notes: $validated['notes'] ?? null,
            openingDenominations: $openingDenominations
        );

        $loadedShift = $shift->load(['user:id,name', 'location:id,name', 'register:id,name']);

        return response()->json([
            'success' => true,
            'message' => 'Shift kasir berhasil dibuka dengan modal awal Rp ' . number_format((float) $shift->opening_cash, 0, ',', '.'),
            'shift' => $loadedShift,
            'data' => [
                'id' => $loadedShift->id,
                'cashier_name' => $loadedShift->user?->name ?? $user->name,
                'location_name' => $loadedShift->location?->name ?? 'Utama',
                'opening_cash' => (float) $loadedShift->opening_cash,
                'status' => $loadedShift->status,
                'opened_at' => $loadedShift->opened_at,
                'total_cash_sales' => (float) $loadedShift->total_cash_sales,
                'total_non_cash_sales' => (float) $loadedShift->total_non_cash_sales,
                'expected_cash' => (float) ($loadedShift->opening_cash + $loadedShift->total_cash_sales),
            ],
        ], Response::HTTP_CREATED);
    }

    /**
     * Get live summary of shift for closing modal.
     */
    public function summary(Request $request, PosShift $posShift): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($posShift->business_id !== $business->id) {
            return response()->json(['success' => false, 'message' => 'Shift tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $summary = $this->shiftService->getShiftSummary($posShift);
        $loadedShift = $posShift->load(['user:id,name', 'location:id,name', 'register:id,name']);

        return response()->json([
            'success' => true,
            'shift' => $loadedShift,
            'data' => $loadedShift,
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
            return response()->json(['success' => false, 'message' => 'Shift tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'closing_cash_actual' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
            'cashier_notes' => ['nullable', 'string', 'max:500'],
            'closing_denominations' => ['nullable', 'array'],
        ]);

        $actualCash = (float) ($validated['closing_cash_actual'] ?? 0.0);
        $closingDenominations = (array) ($validated['closing_denominations'] ?? []);

        $closed = $this->shiftService->closeShift(
            shift: $posShift,
            actualCash: $actualCash,
            notes: $validated['notes'] ?? null,
            closingDenominations: $closingDenominations,
            cashierNotes: $validated['cashier_notes'] ?? null
        );

        $loadedClosed = $closed->load(['user:id,name', 'location:id,name', 'register:id,name']);

        return response()->json([
            'success' => true,
            'message' => 'Shift kasir berhasil ditutup dan direkonsiliasi.',
            'shift' => $loadedClosed,
            'data' => [
                'id' => $loadedClosed->id,
                'cashier_name' => $loadedClosed->user?->name ?? 'Kasir',
                'location_name' => $loadedClosed->location?->name ?? 'Utama',
                'opening_cash' => (float) $loadedClosed->opening_cash,
                'status' => $loadedClosed->status,
                'closed_at' => $loadedClosed->closed_at,
                'closing_cash_actual' => (float) $loadedClosed->closing_cash_actual,
                'closing_cash_expected' => (float) $loadedClosed->closing_cash_expected,
                'cash_difference' => (float) $loadedClosed->cash_difference,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Record cash in / cash out during shift.
     */
    public function recordCashMovement(Request $request, PosShift $posShift): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($posShift->business_id !== $business->id) {
            return response()->json(['success' => false, 'message' => 'Shift tidak ditemukan.'], Response::HTTP_NOT_FOUND);
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
            'success' => true,
            'message' => "{$label} sebesar Rp " . number_format((float) $movement->amount, 0, ',', '.') . ' berhasil dicatat.',
            'movement' => $movement,
        ], Response::HTTP_CREATED);
    }

    /**
     * Direct print shift summary report to ESC/POS thermal printer.
     */
    public function printSummary(Request $request, PosShift $posShift): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($posShift->business_id !== $business->id) {
            return response()->json(['message' => 'Shift tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $printerId = $request->input('printer_id');
        $printer = $printerId ? PosPrinter::where('business_id', $business->id)->find($printerId) : null;

        $result = $this->printerManager->printCashierShift($posShift, $printer);

        return response()->json($result, Response::HTTP_OK);
    }
}
