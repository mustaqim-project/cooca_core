<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Payment;

use App\Domain\Billing\EntitlementService;
use App\Domain\Inventory\StockService;
use App\Domain\Payment\TripayService;
use App\Domain\WhatsApp\WhatsAppGatewayService;
use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\CommerceOrder;
use App\Models\PaymentGatewayCallbackLog;
use App\Models\PosOrder;
use App\Models\PosOrderPayment;
use App\Models\Product;
use App\Models\SubscriptionPayment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final class TripayCallbackController extends Controller
{
    public function __construct(
        private readonly TripayService $tripayService = new TripayService(),
        private readonly StockService $stockService = new StockService(),
        private readonly WhatsAppGatewayService $waGateway = new WhatsAppGatewayService(),
        private readonly EntitlementService $entitlementService = new EntitlementService()
    ) {}

    /**
     * Handle incoming webhook notification from TriPay.
     */
    public function handle(Request $request): JsonResponse
    {
        $rawBody = (string) $request->getContent();
        $signatureHeader = $request->header('X-Callback-Signature');
        $callbackEvent = $request->header('X-Callback-Event');

        Log::info('[TripayCallback] Incoming webhook event: ' . ($callbackEvent ?? 'unknown'), [
            'header' => $signatureHeader,
            'body' => $rawBody,
        ]);

        // 1. Verify HMAC-SHA256 Signature
        if (! $this->tripayService->verifyCallbackSignature($rawBody, $signatureHeader)) {
            Log::warning('[TripayCallback] Invalid signature received', [
                'received_signature' => $signatureHeader,
            ]);

            $this->recordCallbackLog(
                request: $request,
                businessId: null,
                merchantRef: null,
                tripayReference: null,
                statusCode: 403,
                status: PaymentGatewayCallbackLog::STATUS_INVALID_SIGNATURE,
                payload: json_decode($rawBody, true) ?: ['raw' => substr($rawBody, 0, 500)],
                responseContent: ['success' => false, 'message' => 'Invalid signature.'],
                errorMessage: 'Invalid signature.'
            );

            return response()->json([
                'success' => false,
                'message' => 'Invalid signature.',
            ], 403);
        }

        $payload = json_decode($rawBody, true);
        if (! is_array($payload)) {
            $this->recordCallbackLog(
                request: $request,
                businessId: null,
                merchantRef: null,
                tripayReference: null,
                statusCode: 400,
                status: PaymentGatewayCallbackLog::STATUS_FAILED,
                payload: null,
                responseContent: ['success' => false, 'message' => 'Invalid JSON payload.'],
                errorMessage: 'Invalid JSON payload.'
            );

            return response()->json([
                'success' => false,
                'message' => 'Invalid JSON payload.',
            ], 400);
        }

        $merchantRef = (string) ($payload['merchant_ref'] ?? '');
        $tripayReference = (string) ($payload['reference'] ?? '');
        $status = strtoupper(trim((string) ($payload['status'] ?? '')));
        $totalFee = (float) ($payload['total_fee'] ?? 0.0);

        if (empty($merchantRef)) {
            $this->recordCallbackLog(
                request: $request,
                businessId: null,
                merchantRef: null,
                tripayReference: $tripayReference ?: null,
                statusCode: 400,
                status: PaymentGatewayCallbackLog::STATUS_FAILED,
                payload: $payload,
                responseContent: ['success' => false, 'message' => 'Missing merchant_ref.'],
                errorMessage: 'Missing merchant_ref.'
            );

            return response()->json([
                'success' => false,
                'message' => 'Missing merchant_ref.',
            ], 400);
        }

        // 2. Resolve Entity Type: CommerceOrder, PosOrder, or SubscriptionPayment
        $commerceOrder = CommerceOrder::where('order_number', $merchantRef)->first();
        $posOrder = PosOrder::where('order_number', $merchantRef)->first();
        $subscriptionPayment = SubscriptionPayment::where('order_number', $merchantRef)->first();

        $response = null;
        $businessId = null;

        if ($commerceOrder) {
            $businessId = $commerceOrder->business_id;
            $response = $this->processCommerceOrder($commerceOrder, $status, $tripayReference, $totalFee, $payload);
        } elseif ($posOrder) {
            $businessId = $posOrder->business_id;
            $response = $this->processPosOrder($posOrder, $status, $tripayReference, $totalFee, $payload);
        } elseif ($subscriptionPayment) {
            $businessId = $subscriptionPayment->business_id;
            $response = $this->processSubscriptionPayment($subscriptionPayment, $status, $tripayReference, $totalFee, $payload);
        }

        if ($response !== null) {
            $isOk = $response->getStatusCode() === 200;
            $responseData = $response->getData(true);
            $this->recordCallbackLog(
                request: $request,
                businessId: $businessId,
                merchantRef: $merchantRef,
                tripayReference: $tripayReference ?: null,
                statusCode: $response->getStatusCode(),
                status: $isOk ? PaymentGatewayCallbackLog::STATUS_SUCCESS : PaymentGatewayCallbackLog::STATUS_FAILED,
                payload: $payload,
                responseContent: $responseData,
                errorMessage: $isOk ? null : ($responseData['message'] ?? 'Error processing order callback')
            );

            return $response;
        }

        Log::warning("[TripayCallback] Reference target not found for #{$merchantRef}");

        $notFoundResponse = [
            'success' => false,
            'message' => "Order #{$merchantRef} not found in any billing system.",
        ];

        $this->recordCallbackLog(
            request: $request,
            businessId: null,
            merchantRef: $merchantRef,
            tripayReference: $tripayReference ?: null,
            statusCode: 404,
            status: PaymentGatewayCallbackLog::STATUS_FAILED,
            payload: $payload,
            responseContent: $notFoundResponse,
            errorMessage: "Order #{$merchantRef} not found in any billing system."
        );

        return response()->json($notFoundResponse, 404);
    }

    private function recordCallbackLog(
        Request $request,
        ?string $businessId,
        ?string $merchantRef,
        ?string $tripayReference,
        int $statusCode,
        string $status,
        ?array $payload,
        mixed $responseContent,
        ?string $errorMessage = null
    ): void {
        try {
            $parsedResponse = is_string($responseContent) ? json_decode($responseContent, true) : (array) $responseContent;
            PaymentGatewayCallbackLog::create([
                'business_id' => $businessId,
                'gateway' => 'tripay',
                'event' => $request->header('X-Callback-Event'),
                'merchant_ref' => $merchantRef,
                'tripay_reference' => $tripayReference,
                'signature' => (string) ($request->header('X-Callback-Signature') ?? ''),
                'ip_address' => $request->ip(),
                'status_code' => $statusCode,
                'status' => $status,
                'payload' => $payload,
                'response_payload' => $parsedResponse,
                'error_message' => $errorMessage,
            ]);
        } catch (Throwable $e) {
            Log::warning('[TripayCallback] Failed to record callback log: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1. Commerce Storefront Order Handler
    // ─────────────────────────────────────────────────────────────────────────

    private function processCommerceOrder(
        CommerceOrder $order,
        string $status,
        string $tripayReference,
        float $totalFee,
        array $payload
    ): JsonResponse {
        if ($order->isPaid()) {
            return response()->json([
                'success' => true,
                'message' => 'Order was already marked as paid.',
            ]);
        }

        try {
            if ($status === 'PAID') {
                $this->handlePaymentSuccess($order, $tripayReference, $totalFee, $payload);
            } elseif (in_array($status, ['EXPIRED', 'FAILED'], true)) {
                $this->handlePaymentFailedOrExpired($order, $status);
            }

            return response()->json(['success' => true]);
        } catch (Throwable $e) {
            Log::error("[TripayCallback] Error processing CommerceOrder #{$order->order_number}: " . $e->getMessage());

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function handlePaymentSuccess(
        CommerceOrder $order,
        string $tripayReference,
        float $totalFee,
        array $payload
    ): void {
        DB::transaction(function () use ($order, $tripayReference, $totalFee, $payload) {
            $paymentChannel = (string) ($payload['payment_method'] ?? ($order->payment_channel ?? 'QRIS'));

            $calculatedFee = strtoupper($paymentChannel) === 'QRIS'
                ? $this->tripayService->calculateQrisFee((float) $order->total_amount)
                : ($totalFee > 0 ? $totalFee : (float) ($order->gateway_fee ?? 0.0));

            $order->update([
                'status' => CommerceOrder::STATUS_PAID,
                'payment_status' => CommerceOrder::PAYMENT_PAID,
                'payment_gateway' => CommerceOrder::GATEWAY_TRIPAY,
                'payment_channel' => $paymentChannel,
                'gateway_reference' => $tripayReference ?: $order->gateway_reference,
                'gateway_fee' => $calculatedFee,
                'paid_at' => Carbon::now(),
                'rejection_reason' => null,
            ]);

            // Commit physical stock for goods
            foreach ($order->items as $item) {
                if ($item->product && $item->product->isGoods()) {
                    $this->stockService->commitProductReservedStock(
                        businessId: $order->business_id,
                        locationId: $order->location_id,
                        product: $item->product,
                        productQuantity: (float) $item->quantity,
                        unitCost: (float) $item->product->base_cost,
                        referenceId: $order->id,
                        referenceNumber: $order->order_number,
                        userId: null
                    );
                }
            }

            // Auto-post to Cash Ledger
            $this->recordStoreCashInflow($order->business_id, $order->net_revenue, "Penjualan Online #{$order->order_number} ({$paymentChannel})", $order->id);
        });

        $this->sendPaymentSuccessWhatsApp($order);
    }

    private function handlePaymentFailedOrExpired(CommerceOrder $order, string $status): void
    {
        DB::transaction(function () use ($order, $status) {
            $order->update([
                'status' => $status === 'EXPIRED' ? CommerceOrder::STATUS_EXPIRED : CommerceOrder::STATUS_CANCELLED,
                'payment_status' => CommerceOrder::PAYMENT_FAILED,
                'cancelled_at' => Carbon::now(),
            ]);

            foreach ($order->items as $item) {
                if ($item->product && $item->product->isGoods()) {
                    $this->stockService->releaseProductReservedStock(
                        businessId: $order->business_id,
                        locationId: $order->location_id,
                        product: $item->product,
                        productQuantity: (float) $item->quantity
                    );
                }
            }
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. POS QR Table Order Handler (Pay-at-Table)
    // ─────────────────────────────────────────────────────────────────────────

    private function processPosOrder(
        PosOrder $order,
        string $status,
        string $tripayReference,
        float $totalFee,
        array $payload
    ): JsonResponse {
        if ($order->isPaid()) {
            return response()->json([
                'success' => true,
                'message' => 'POS Table order was already marked as paid.',
            ]);
        }

        try {
            if ($status === 'PAID') {
                DB::transaction(function () use ($order, $tripayReference, $totalFee, $payload) {
                    $paymentChannel = (string) ($payload['payment_method'] ?? ($order->payment_channel ?? 'QRIS'));
                    $calculatedFee = strtoupper($paymentChannel) === 'QRIS'
                        ? $this->tripayService->calculateQrisFee((float) $order->total_amount)
                        : ($totalFee > 0 ? $totalFee : (float) ($order->gateway_fee ?? 0.0));

                    $order->update([
                        'status' => PosOrder::STATUS_CONFIRMED,
                        'paid_amount' => $order->total_amount,
                        'change_amount' => 0.0,
                        'payment_gateway' => PosOrder::GATEWAY_TRIPAY,
                        'payment_channel' => $paymentChannel,
                        'gateway_reference' => $tripayReference ?: $order->gateway_reference,
                        'gateway_fee' => $calculatedFee,
                    ]);

                    PosOrderPayment::firstOrCreate(
                        [
                            'pos_order_id' => $order->id,
                            'reference_number' => $tripayReference,
                        ],
                        [
                            'payment_method' => 'qris',
                            'amount' => $order->total_amount,
                            'fee_amount' => $calculatedFee,
                            'net_amount' => max(0.0, $order->total_amount - $calculatedFee),
                            'status' => 'paid',
                            'notes' => "Lunas otomatis via TriPay {$paymentChannel} (Meja " . ($order->table_or_reference ?? '-') . ')',
                        ]
                    );

                    // Commit recipe / BOM material stock for each item
                    foreach ($order->items as $item) {
                        if ($item->product_id) {
                            $product = Product::find($item->product_id);
                            if ($product && ! $product->isService()) {
                                $this->stockService->deductForProductSale(
                                    businessId: $order->business_id,
                                    locationId: $order->location_id,
                                    product: $product,
                                    productQuantity: (float) $item->quantity,
                                    unitCost: (float) ($product->base_cost ?? 0.0),
                                    orderId: $order->id,
                                    orderNumber: $order->order_number,
                                    userId: $order->user_id,
                                    movementType: \App\Models\StockMovement::TYPE_POS_SALE,
                                    notes: "Penjualan QR Meja #{$order->order_number}"
                                );
                            }
                        }
                    }

                    // Auto-post to Cash Ledger
                    $this->recordStoreCashInflow(
                        $order->business_id,
                        max(0.0, (float) $order->total_amount - $calculatedFee),
                        "Penjualan QR Meja #{$order->order_number} ({$paymentChannel})",
                        $order->id
                    );
                });

                $this->sendPosTablePaymentWhatsApp($order);
            } elseif (in_array($status, ['EXPIRED', 'FAILED'], true)) {
                $order->update([
                    'status' => PosOrder::STATUS_VOIDED,
                    'void_reason' => 'Pembayaran QRIS Meja kadaluarsa/gagal via gateway.',
                ]);
            }

            return response()->json(['success' => true]);
        } catch (Throwable $e) {
            Log::error("[TripayCallback] Error processing PosOrder #{$order->order_number}: " . $e->getMessage());

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. SaaS Subscription Payment Handler (Platform Subscriptions & AI Tokens)
    // ─────────────────────────────────────────────────────────────────────────

    private function processSubscriptionPayment(
        SubscriptionPayment $payment,
        string $status,
        string $tripayReference,
        float $totalFee,
        array $payload
    ): JsonResponse {
        if ($payment->isApproved()) {
            return response()->json([
                'success' => true,
                'message' => 'Subscription was already approved.',
            ]);
        }

        try {
            if ($status === 'PAID') {
                $paymentChannel = (string) ($payload['payment_method'] ?? 'QRIS');

                $payment->update([
                    'payment_method' => 'tripay_' . strtolower($paymentChannel),
                ]);

                $this->entitlementService->approvePayment(
                    payment: $payment,
                    admin: null,
                    adminNotes: "Auto-approved via TriPay Gateway ({$paymentChannel} - Ref: {$tripayReference})"
                );

                Log::info("[TripayCallback] SaaS Subscription #{$payment->order_number} successfully auto-activated.");
            } elseif (in_array($status, ['EXPIRED', 'FAILED'], true)) {
                $payment->update([
                    'status' => SubscriptionPayment::STATUS_CANCELLED,
                    'admin_notes' => 'Tagihan gateway kadaluarsa atau gagal bayar.',
                ]);
            }

            return response()->json(['success' => true]);
        } catch (Throwable $e) {
            Log::error("[TripayCallback] Error processing SubscriptionPayment #{$payment->order_number}: " . $e->getMessage());

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function recordStoreCashInflow(string $businessId, float $netAmount, string $description, string $referenceId): void
    {
        try {
            $cashAccount = CashAccount::where('business_id', $businessId)
                ->where('type', CashAccount::TYPE_EWALLET)
                ->where('is_active', true)
                ->first()
                ?? CashAccount::where('business_id', $businessId)->where('is_active', true)->first();

            if (! $cashAccount) {
                return;
            }

            $currentBalance = (float) $cashAccount->current_balance;
            $newBalance = $currentBalance + $netAmount;
            $cashAccount->update(['current_balance' => $newBalance]);

            CashTransaction::create([
                'business_id' => $businessId,
                'cash_account_id' => $cashAccount->id,
                'type' => CashTransaction::TYPE_IN,
                'amount' => $netAmount,
                'balance_after' => $newBalance,
                'reference_type' => 'online_order',
                'reference_id' => $referenceId,
                'description' => $description,
                'transaction_date' => Carbon::today()->toDateString(),
                'created_by' => null,
            ]);
        } catch (Throwable $e) {
            Log::warning('[TripayCallback] Cash ledger record skipped: ' . $e->getMessage());
        }
    }

    private function sendPaymentSuccessWhatsApp(CommerceOrder $order): void
    {
        try {
            $business = $order->business;
            if (! $business) {
                return;
            }

            $trackingUrl = url("/{$business->slug}/order/{$order->tracking_token}");
            $channelLabel = $order->payment_channel ?? 'QRIS';

            $msg = "✅ *Pembayaran Berhasil Dikonfirmasi!*\n\n";
            $msg .= "Halo *{$order->customer_name}*, pembayaran sebesar *Rp " . number_format($order->total_amount, 0, ',', '.') . "* via {$channelLabel} untuk pesanan #{$order->order_number} telah berhasil diverifikasi secara otomatis oleh sistem.\n\n";
            $msg .= "Pesanan Anda kini sedang dipersiapkan oleh tim *{$business->name}*.\n\n";
            $msg .= "Pantau status pesanan secara langsung di sini:\n👉 {$trackingUrl}\n\nTerima kasih atas pesanan Anda!";

            $this->waGateway->sendMessage($business, $order->customer_phone, $msg);
        } catch (Throwable $e) {
            Log::warning('[TripayCallback] WA notification to customer failed: ' . $e->getMessage());
        }
    }

    private function sendPosTablePaymentWhatsApp(PosOrder $order): void
    {
        try {
            $phone = $order->customer_phone_guest ?: $order->customer?->phone;
            if (! $phone) {
                return;
            }

            $business = $order->business;
            if (! $business) {
                return;
            }

            $channelLabel = $order->payment_channel ?? 'QRIS';
            $msg = "✅ *Pembayaran Meja Berhasil!*\n\n";
            $msg .= "Terima kasih, pembayaran sebesar *Rp " . number_format($order->total_amount, 0, ',', '.') . "* via {$channelLabel} untuk pesanan Meja *{$order->table_or_reference}* telah berhasil diverifikasi.\n\n";
            $msg .= "Pesanan #{$order->order_number} saat ini langsung disiapkan oleh tim dapur *{$business->name}*.\nSelamat menikmati hidangan Anda!";

            $this->waGateway->sendMessage($business, $phone, $msg);
        } catch (Throwable $e) {
            Log::warning('[TripayCallback] WA notification to table guest failed: ' . $e->getMessage());
        }
    }
}
