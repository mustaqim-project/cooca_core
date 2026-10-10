<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Pos;

use App\Domain\Pos\PosTableService;
use App\Domain\Pos\TableQrCodeService;
use App\Http\Controllers\Controller;
use App\Models\CommerceReservation;
use App\Models\PosTable;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class PosTableApiController extends Controller
{
    public function __construct(
        private readonly PosTableService $tableService = new PosTableService,
        private readonly TableQrCodeService $qrCodeService = new TableQrCodeService
    ) {}

    /**
     * Get list of tables with active dining sessions and reservations.
     * GET /api/v1/pos/tables
     */
    public function index(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $locationId = $request->query('location_id') ?? $request->header('X-Location-ID');

        $tables = $this->tableService->getTables($business, $locationId);

        $todayReservations = CommerceReservation::where('business_id', $business->id)
            ->whereDate('reservation_date', Carbon::today()->toDateString())
            ->whereIn('status', [
                CommerceReservation::STATUS_PENDING_CONFIRMATION,
                CommerceReservation::STATUS_CONFIRMED,
                CommerceReservation::STATUS_SEATED,
            ])
            ->with(['posTable', 'product'])
            ->orderBy('time_slot')
            ->get();

        $data = $tables->map(fn (PosTable $t) => [
            'id' => $t->id,
            'table_number' => $t->table_number,
            'name' => $t->name,
            'capacity' => $t->capacity,
            'status' => $t->status,
            'is_active' => (bool) $t->is_active,
            'qr_token' => $t->qr_token,
            'qr_order_url' => $t->qr_token ? route('public.qr.order', $t->qr_token) : null,
            'today_reservation' => $todayReservations->firstWhere('pos_table_id', $t->id)?->only([
                'id', 'reservation_code', 'customer_name', 'customer_phone', 'guest_count', 'time_slot', 'status'
            ]),
            'active_session' => $t->activeSession ? [
                'id' => $t->activeSession->id,
                'session_number' => $t->activeSession->session_number,
                'customer_name' => $t->activeSession->customer_name,
                'customer_phone' => $t->activeSession->customer_phone,
                'total_amount' => (float) $t->activeSession->total_amount,
                'orders' => $t->activeSession->orders->map(fn ($o) => [
                    'id' => $o->id,
                    'order_number' => $o->order_number,
                    'status' => $o->status,
                    'is_paid' => $o->isPaid(),
                    'paid_amount' => (float) $o->paid_amount,
                    'payment_gateway' => $o->payment_gateway,
                    'payment_channel' => $o->payment_channel,
                    'gateway_reference' => $o->gateway_reference,
                    'total_amount' => (float) $o->total_amount,
                    'customer_name_guest' => $o->customer_name_guest,
                    'notes' => $o->notes,
                    'items' => $o->items->map(fn ($item) => [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product_name,
                        'unit_price' => (float) $item->unit_price,
                        'total_price' => (float) $item->total_price,
                        'quantity' => (float) $item->quantity,
                        'unit_symbol' => $item->product?->outputUnit?->symbol ?? '',
                        'modifiers_summary' => $item->modifiers_display_text ?? '',
                        'notes' => $item->notes,
                    ])->values()->all(),
                ])->values()->all(),
            ] : null,
        ])->values()->all();

        return response()->json([
            'success' => true,
            'data' => $data,
            'today_reservations' => $todayReservations->map(fn ($r) => [
                'id' => $r->id,
                'reservation_code' => $r->reservation_code,
                'customer_name' => $r->customer_name,
                'customer_phone' => $r->customer_phone,
                'guest_count' => $r->guest_count,
                'time_slot' => $r->time_slot,
                'status' => $r->status,
                'pos_table_id' => $r->pos_table_id,
                'table_number' => $r->posTable?->table_number ?? $r->posTable?->name,
            ])->values()->all(),
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created table.
     * POST /api/v1/pos/tables
     */
    public function store(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $validated = $request->validate([
            'table_number' => ['required', 'string', 'max:50'],
            'name' => ['nullable', 'string', 'max:100'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'location_id' => ['nullable', 'string', 'exists:locations,id'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $table = $this->tableService->createTable($business, $validated);

            return response()->json([
                'success' => true,
                'message' => "Meja {$table->table_number} berhasil ditambahkan.",
                'data' => $table,
            ], Response::HTTP_CREATED);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Update table details.
     * PUT /api/v1/pos/tables/{table}
     */
    public function update(Request $request, PosTable $table): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($table->business_id !== $business->id) {
            return response()->json([
                'success' => false,
                'message' => 'Meja tidak ditemukan pada bisnis ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'table_number' => ['sometimes', 'string', 'max:50'],
            'name' => ['nullable', 'string', 'max:100'],
            'capacity' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'status' => ['sometimes', 'string', 'in:available,occupied,reserved,cleaning'],
            'is_active' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $updated = $this->tableService->updateTable($table, $validated);

            return response()->json([
                'success' => true,
                'message' => "Meja {$updated->table_number} berhasil diperbarui.",
                'data' => $updated,
            ], Response::HTTP_OK);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Delete a table.
     * DELETE /api/v1/pos/tables/{table}
     */
    public function destroy(PosTable $table): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($table->business_id !== $business->id) {
            return response()->json([
                'success' => false,
                'message' => 'Meja tidak ditemukan pada bisnis ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $this->tableService->deleteTable($table);

            return response()->json([
                'success' => true,
                'message' => "Meja {$table->table_number} berhasil dihapus.",
            ], Response::HTTP_OK);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Regenerate table self-order QR token.
     * POST /api/v1/pos/tables/{table}/regenerate-qr
     */
    public function regenerateQr(PosTable $table): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($table->business_id !== $business->id) {
            return response()->json([
                'success' => false,
                'message' => 'Meja tidak ditemukan pada bisnis ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        $newToken = $this->tableService->regenerateQrToken($table);
        $orderUrl = route('public.qr.order', $newToken);

        return response()->json([
            'success' => true,
            'message' => "QR Code untuk meja {$table->table_number} berhasil diperbarui.",
            'qr_token' => $newToken,
            'qr_order_url' => $orderUrl,
        ], Response::HTTP_OK);
    }

    /**
     * Get QR Card details & SVG for table standee print preview.
     * GET /api/v1/pos/tables/{table}/qr-card
     */
    public function qrCardData(PosTable $table): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($table->business_id !== $business->id) {
            return response()->json([
                'success' => false,
                'message' => 'Meja tidak ditemukan pada bisnis ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        $orderUrl = route('public.qr.order', $table->qr_token);
        $qrSvg = $this->qrCodeService->generateSvg($orderUrl, $business->logo_url, 380);

        return response()->json([
            'success' => true,
            'data' => [
                'table_id' => $table->id,
                'table_number' => $table->table_number,
                'name' => $table->name,
                'capacity' => $table->capacity,
                'qr_token' => $table->qr_token,
                'qr_order_url' => $orderUrl,
                'qr_svg' => $qrSvg,
                'business' => [
                    'id' => $business->id,
                    'name' => $business->name,
                    'logo_url' => $business->logo_url,
                ],
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Close active dining session on table.
     * POST /api/v1/pos/tables/{table}/close-session
     */
    public function closeSession(PosTable $table): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($table->business_id !== $business->id) {
            return response()->json([
                'success' => false,
                'message' => 'Meja tidak ditemukan pada bisnis ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        $session = $table->activeSession;
        if (! $session) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada sesi aktif pada meja ini.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $this->tableService->closeSession($session);

            return response()->json([
                'success' => true,
                'message' => "Sesi meja {$table->table_number} berhasil diselesaikan.",
            ], Response::HTTP_OK);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
