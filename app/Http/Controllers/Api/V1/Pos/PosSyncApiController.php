<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Pos;

use App\Domain\POS\PosOrderService;
use App\Domain\POS\PosShiftService;
use App\Http\Controllers\Controller;
use App\Models\PosOrder;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class PosSyncApiController extends Controller
{
    public function __construct(
        private readonly PosOrderService $orderService = new PosOrderService,
        private readonly PosShiftService $shiftService = new PosShiftService
    ) {}

    /**
     * Batch sync offline POS orders with idempotency by client_uuid.
     * POST /api/v1/pos/sync/batch
     */
    public function batchSync(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'transactions' => ['required', 'array', 'min:1', 'max:50'],
            'transactions.*.client_uuid' => ['required', 'string', 'max:64'],
            'transactions.*.offline_created_at' => ['nullable', 'date'],
            'transactions.*.items' => ['required', 'array', 'min:1'],
            'transactions.*.items.*.product_id' => ['nullable', 'string'],
            'transactions.*.items.*.product_name' => ['required', 'string'],
            'transactions.*.items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'transactions.*.items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'transactions.*.items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'transactions.*.items.*.notes' => ['nullable', 'string'],
            'transactions.*.items.*.selected_modifiers' => ['nullable', 'array'],
            'transactions.*.payments' => ['required', 'array', 'min:1'],
            'transactions.*.payments.*.payment_method' => ['required', 'string'],
            'transactions.*.payments.*.amount' => ['required', 'numeric', 'min:0'],
            'transactions.*.payments.*.reference_number' => ['nullable', 'string'],
            'transactions.*.customer_id' => ['nullable', 'string'],
            'transactions.*.customer_name_guest' => ['nullable', 'string', 'max:150'],
            'transactions.*.order_type' => ['nullable', 'string', 'in:dine_in,takeaway,delivery'],
            'transactions.*.sales_channel' => ['nullable', 'string'],
            'transactions.*.table_or_reference' => ['nullable', 'string', 'max:100'],
            'transactions.*.discount_type' => ['nullable', 'string', 'in:fixed,percentage'],
            'transactions.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'transactions.*.voucher_code' => ['nullable', 'string'],
            'transactions.*.notes' => ['nullable', 'string', 'max:500'],
            'transactions.*.location_id' => ['nullable', 'string'],
            'transactions.*.pos_register_id' => ['nullable', 'string'],
        ]);

        $results = [];
        $syncedCount = 0;
        $duplicateCount = 0;
        $failedCount = 0;

        foreach ($validated['transactions'] as $tx) {
            $clientUuid = (string) $tx['client_uuid'];

            // 1. Idempotency Check: apakah transaksi ini sudah pernah disinkronkan?
            $existingOrder = PosOrder::where('business_id', $business->id)
                ->where('client_uuid', $clientUuid)
                ->first();

            if ($existingOrder !== null) {
                $results[] = [
                    'client_uuid' => $clientUuid,
                    'status' => 'duplicate_skipped',
                    'order_id' => $existingOrder->id,
                    'order_number' => $existingOrder->order_number,
                    'total_amount' => (float) $existingOrder->total_amount,
                    'message' => 'Transaksi offline sudah tersinkronisasi sebelumnya (idempotent skip).',
                ];
                $duplicateCount++;
                continue;
            }

            // 2. Eksekusi Checkout untuk Transaksi Baru
            try {
                $locationId = $tx['location_id'] ?? null;
                $activeShift = $this->shiftService->getActiveShift($business, $user, $locationId);

                $orderDate = ! empty($tx['offline_created_at'])
                    ? Carbon::parse($tx['offline_created_at'])->toDateString()
                    : now()->toDateString();

                $order = $this->orderService->checkout(
                    business: $business,
                    cashier: $user,
                    itemsData: $tx['items'],
                    paymentsData: $tx['payments'],
                    attributes: [
                        'customer_id' => $tx['customer_id'] ?? null,
                        'customer_name_guest' => $tx['customer_name_guest'] ?? null,
                        'order_type' => $tx['order_type'] ?? 'takeaway',
                        'sales_channel' => $tx['sales_channel'] ?? ($tx['order_type'] ?? 'takeaway'),
                        'pos_register_id' => $tx['pos_register_id'] ?? $activeShift?->pos_register_id ?? null,
                        'table_or_reference' => $tx['table_or_reference'] ?? null,
                        'discount_type' => $tx['discount_type'] ?? 'fixed',
                        'discount_value' => (float) ($tx['discount_value'] ?? 0),
                        'voucher_code' => $tx['voucher_code'] ?? null,
                        'notes' => $tx['notes'] ?? null,
                        'location_id' => $locationId,
                        'order_date' => $orderDate,
                    ],
                    shift: $activeShift
                );

                // Tandai client_uuid pada nota pesanan
                $order->client_uuid = $clientUuid;
                $order->save();

                $results[] = [
                    'client_uuid' => $clientUuid,
                    'status' => 'synced',
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => (float) $order->total_amount,
                    'message' => 'Transaksi offline berhasil disimpan ke sistem pusat.',
                ];
                $syncedCount++;
            } catch (Throwable $e) {
                $results[] = [
                    'client_uuid' => $clientUuid,
                    'status' => 'failed',
                    'order_id' => null,
                    'order_number' => null,
                    'message' => 'Gagal memproses transaksi: ' . $e->getMessage(),
                ];
                $failedCount++;
            }
        }

        return response()->json([
            'success' => $failedCount === 0,
            'message' => 'Sinkronisasi transaksi offline POS selesai diproses.',
            'summary' => [
                'total_submitted' => count($validated['transactions']),
                'total_synced' => $syncedCount,
                'total_duplicates' => $duplicateCount,
                'total_failed' => $failedCount,
            ],
            'results' => $results,
        ], Response::HTTP_OK);
    }
}
