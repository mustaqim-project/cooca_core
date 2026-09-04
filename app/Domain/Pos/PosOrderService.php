<?php

declare(strict_types=1);

namespace App\Domain\Pos;

use App\Domain\Accounting\AutoJournalService;
use App\Domain\Crm\LoyaltyService;
use App\Domain\Inventory\StockService;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\PosShift;
use App\Models\Product;
use App\Models\User;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class PosOrderService
{
    public function __construct(
        private readonly StockService $stockService = new StockService,
        private readonly AutoJournalService $journalService = new AutoJournalService,
        private readonly LoyaltyService $loyaltyService = new LoyaltyService
    ) {}

    /**
     * Generate unique POS Order number: POS-YYYYMMDD-XXXX
     */
    public function generateOrderNumber(Business $business): string
    {
        $todayPrefix = 'POS-' . date('Ymd') . '-';
        $latest = PosOrder::where('business_id', $business->id)
            ->where('order_number', 'like', "{$todayPrefix}%")
            ->orderByDesc('order_number')
            ->first();

        if ($latest) {
            $lastSeq = (int) substr($latest->order_number, -4);
            $seq = str_pad((string) ($lastSeq + 1), 4, '0', STR_PAD_LEFT);
        } else {
            $seq = '0001';
        }

        return $todayPrefix . $seq;
    }

    /**
     * Complete a POS checkout transaction.
     *
     * @param array<int, array{
     *     product_id?: string|null,
     *     product_name?: string|null,
     *     unit_price: float|int,
     *     quantity: float|int,
     *     discount_amount?: float|int|null,
     *     notes?: string|null,
     *     batch_number?: string|null,
     *     serial_number?: string|null
     * }> $itemsData
     * @param array<int, array{
     *     payment_method: string,
     *     amount: float|int,
     *     reference_number?: string|null
     * }> $paymentsData
     * @param array<string, mixed> $attributes
     */
    public function checkout(
        Business $business,
        User $cashier,
        array $itemsData,
        array $paymentsData,
        array $attributes = [],
        ?PosShift $shift = null
    ): PosOrder {
        if (empty($itemsData)) {
            throw new InvalidArgumentException('Keranjang transaksi kasir tidak boleh kosong.');
        }

        if (empty($paymentsData)) {
            throw new InvalidArgumentException('Metode pembayaran harus ditentukan minimal 1 metode.');
        }

        $entitlement = app(\App\Domain\Billing\EntitlementService::class);
        $sub = $entitlement->getSubscription($business);
        if (! $sub->isCorePlan()) {
            $allowed = $entitlement->incrementMonthlyUsage($business, \App\Models\QuotaMonthlyUsage::TYPE_POS, \App\Domain\Billing\EntitlementService::FREE_POS_MONTHLY_LIMIT);
            if (! $allowed) {
                throw new \DomainException('Batas kuota transaksi POS bulanan (maks. 100 transaksi/bulan untuk Free Plan) telah tercapai. Silakan tingkatkan ke paket Cooca UMKM.');
            }
        }

        return DB::transaction(function () use ($business, $cashier, $itemsData, $paymentsData, $attributes, $shift) {
            $orderNumber = $attributes['order_number'] ?? $this->generateOrderNumber($business);
            $locationId = $attributes['location_id'] ?? $shift?->location_id ?? Location::where('business_id', $business->id)->where('is_primary', true)->value('id') ?? Location::where('business_id', $business->id)->value('id');
            $customerId = ! empty($attributes['customer_id']) ? (string) $attributes['customer_id'] : null;
            $customer = $customerId ? Customer::find($customerId) : null;

            // 1. Calculate items subtotal and HPP
            $subtotal = 0.0;
            $totalHpp = 0.0;
            $processedItems = [];

            foreach ($itemsData as $row) {
                $qty = (float) ($row['quantity'] ?? 1);
                if ($qty <= 0) {
                    continue;
                }

                $productId = ! empty($row['product_id']) ? (string) $row['product_id'] : null;
                $product = $productId ? Product::find($productId) : null;

                $productName = $row['product_name'] ?? $product?->name ?? 'Item Custom';
                $productCode = $product?->code;
                $unitPrice = (float) ($row['unit_price'] ?? $product?->selling_price ?? 0.0);

                // Unit HPP from Product base_cost or active BOM/cost model
                $unitHpp = 0.0;
                if ($product) {
                    $unitHpp = (float) $product->base_cost;
                    if ($unitHpp <= 0 && $product->activeCostModel) {
                        $unitHpp = (float) ($product->activeCostModel->latestResult?->total_cost ?? 0.0);
                    }
                }

                $lineDiscount = (float) ($row['discount_amount'] ?? 0.0);
                $lineSubtotal = ($qty * $unitPrice);
                $lineTotal = max(0.0, $lineSubtotal - $lineDiscount);
                $lineHpp = ($qty * $unitHpp);

                $subtotal += $lineSubtotal;
                $totalHpp += $lineHpp;

                $processedItems[] = [
                    'product' => $product,
                    'product_id' => $productId,
                    'product_name' => $productName,
                    'product_code' => $productCode,
                    'unit_price' => $unitPrice,
                    'unit_cost_hpp' => $unitHpp,
                    'quantity' => $qty,
                    'subtotal' => $lineSubtotal,
                    'discount_amount' => $lineDiscount,
                    'total_price' => $lineTotal,
                    'total_hpp' => $lineHpp,
                    'batch_number' => $row['batch_number'] ?? null,
                    'serial_number' => $row['serial_number'] ?? null,
                    'notes' => $row['notes'] ?? null,
                ];
            }

            // 2. Order-level discounts & vouchers
            $discountType = $attributes['discount_type'] ?? 'fixed';
            $discountValue = (float) ($attributes['discount_value'] ?? 0.0);
            $discountAmount = 0.0;

            if ($discountValue > 0) {
                if ($discountType === 'percentage') {
                    $discountAmount = ($subtotal * min(100.0, $discountValue)) / 100.0;
                } else {
                    $discountAmount = min($subtotal, $discountValue);
                }
            }

            // Voucher validation
            $voucherCode = ! empty($attributes['voucher_code']) ? trim((string) $attributes['voucher_code']) : null;
            $voucherDiscount = 0.0;
            if ($voucherCode) {
                $voucher = Voucher::where('business_id', $business->id)->where('code', $voucherCode)->first();
                if ($voucher && $voucher->isValidForAmount($subtotal)) {
                    $voucherDiscount = $voucher->calculateDiscount($subtotal);
                    $voucher->increment('used_count');
                }
            }

            // 3. Tax & Service Charge
            $taxPercent = isset($attributes['tax_percentage'])
                ? (float) $attributes['tax_percentage']
                : ($business->pos_enable_tax ? (float) $business->pos_tax_percent : 0.0);

            $servicePercent = isset($attributes['service_charge_percentage'])
                ? (float) $attributes['service_charge_percentage']
                : ($business->pos_enable_service_charge ? (float) $business->pos_service_charge_percent : 0.0);

            $taxableAmount = max(0.0, $subtotal - $discountAmount - $voucherDiscount);
            $taxAmount = ($taxableAmount * $taxPercent) / 100.0;
            $serviceChargeAmount = ($taxableAmount * $servicePercent) / 100.0;

            // 4. Grand Total & Rounding
            $rawTotal = $taxableAmount + $taxAmount + $serviceChargeAmount;
            $roundedTotal = match ($business->rounding_strategy) {
                Business::ROUNDING_ROUND_100 => round($rawTotal / 100) * 100,
                Business::ROUNDING_ROUND_500 => round($rawTotal / 500) * 500,
                Business::ROUNDING_ROUND_1000 => round($rawTotal / 1000) * 1000,
                Business::ROUNDING_CEIL => ceil($rawTotal),
                Business::ROUNDING_FLOOR => floor($rawTotal),
                default => round($rawTotal, $business->currency_precision ?? 0),
            };
            $roundingAmount = $roundedTotal - $rawTotal;
            $finalTotal = max(0.0, $roundedTotal);

            // 5. Total Paid & Change
            $totalPaid = 0.0;
            foreach ($paymentsData as $p) {
                $totalPaid += (float) ($p['amount'] ?? 0.0);
            }
            $changeAmount = max(0.0, $totalPaid - $finalTotal);

            // 6. Create PosOrder
            $order = PosOrder::create([
                'business_id' => $business->id,
                'location_id' => $locationId,
                'pos_shift_id' => $shift?->id,
                'user_id' => $cashier->id,
                'customer_id' => $customer?->id,
                'order_number' => $orderNumber,
                'order_date' => Carbon::today()->toDateString(),
                'status' => PosOrder::STATUS_COMPLETED,
                'order_type' => $attributes['order_type'] ?? 'takeaway',
                'table_or_reference' => $attributes['table_or_reference'] ?? null,
                'customer_name_guest' => $attributes['customer_name_guest'] ?? ($customer?->name ?? 'Pelanggan Umum'),
                'subtotal' => $subtotal,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'discount_amount' => $discountAmount,
                'voucher_code' => $voucherCode,
                'voucher_discount_amount' => $voucherDiscount,
                'tax_percentage' => $taxPercent,
                'tax_amount' => $taxAmount,
                'service_charge_percentage' => $servicePercent,
                'service_charge_amount' => $serviceChargeAmount,
                'rounding_amount' => $roundingAmount,
                'total_amount' => $finalTotal,
                'paid_amount' => $totalPaid,
                'change_amount' => $changeAmount,
                'total_hpp_cost' => $totalHpp,
                'total_gross_profit' => max(0.0, ($subtotal - $discountAmount - $voucherDiscount) - $totalHpp),
                'notes' => $attributes['notes'] ?? null,
            ]);

            // 7. Save Line Items and Deduct Inventory Stock
            foreach ($processedItems as $itemInfo) {
                PosOrderItem::create([
                    'pos_order_id' => $order->id,
                    'product_id' => $itemInfo['product_id'],
                    'product_name' => $itemInfo['product_name'],
                    'product_code' => $itemInfo['product_code'],
                    'unit_price' => $itemInfo['unit_price'],
                    'unit_cost_hpp' => $itemInfo['unit_cost_hpp'],
                    'quantity' => $itemInfo['quantity'],
                    'subtotal' => $itemInfo['subtotal'],
                    'discount_amount' => $itemInfo['discount_amount'],
                    'total_price' => $itemInfo['total_price'],
                    'total_hpp' => $itemInfo['total_hpp'],
                    'batch_number' => $itemInfo['batch_number'],
                    'serial_number' => $itemInfo['serial_number'],
                    'notes' => $itemInfo['notes'],
                ]);

                // Stock reduction
                if ($itemInfo['product_id'] && $locationId) {
                    $this->stockService->deductForPosSale(
                        businessId: $business->id,
                        locationId: $locationId,
                        productId: $itemInfo['product_id'],
                        quantity: $itemInfo['quantity'],
                        unitCost: $itemInfo['unit_cost_hpp'],
                        orderId: $order->id,
                        orderNumber: $order->order_number,
                        userId: $cashier->id
                    );
                }
            }

            // 8. Save Payments
            foreach ($paymentsData as $p) {
                $payMethod = (string) ($p['payment_method'] ?? 'cash');
                $payAmount = (float) ($p['amount'] ?? 0.0);
                if ($payAmount <= 0) {
                    continue;
                }

                PosOrderPayment::create([
                    'pos_order_id' => $order->id,
                    'payment_method' => $payMethod,
                    'amount' => $payAmount,
                    'reference_number' => $p['reference_number'] ?? null,
                    'fee_amount' => 0.0,
                    'net_amount' => $payAmount,
                    'status' => 'paid',
                ]);

                // Handle Customer Store Credit (Piutang)
                if ($payMethod === PosOrderPayment::METHOD_CUSTOMER_CREDIT && $customer) {
                    $this->loyaltyService->recordCustomerCreditCharge($customer, $order, $payAmount, $cashier);
                }
            }

            // 9. Customer Loyalty Points
            if ($customer) {
                $this->loyaltyService->awardPointsForOrder($customer, $order);
            }

            // 10. Automatic Accounting Journal
            $this->journalService->recordPosSaleJournal($order);

            return $order->load(['items', 'payments', 'customer', 'location']);
        });
    }

    /**
     * Put a cart on hold.
     */
    public function holdOrder(
        Business $business,
        User $cashier,
        array $itemsData,
        string $holdLabel,
        ?PosShift $shift = null
    ): PosOrder {
        $orderNumber = 'HOLD-' . date('His') . '-' . rand(100, 999);
        $locationId = $shift?->location_id ?? Location::where('business_id', $business->id)->where('is_primary', true)->value('id');

        return DB::transaction(function () use ($business, $cashier, $itemsData, $holdLabel, $orderNumber, $locationId, $shift) {
            $subtotal = 0.0;
            foreach ($itemsData as $row) {
                $qty = (float) ($row['quantity'] ?? 1);
                $price = (float) ($row['unit_price'] ?? 0);
                $subtotal += ($qty * $price);
            }

            $order = PosOrder::create([
                'business_id' => $business->id,
                'location_id' => $locationId,
                'pos_shift_id' => $shift?->id,
                'user_id' => $cashier->id,
                'order_number' => $orderNumber,
                'order_date' => Carbon::today()->toDateString(),
                'status' => PosOrder::STATUS_DRAFT_HELD,
                'hold_label' => $holdLabel,
                'held_at' => now(),
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
            ]);

            foreach ($itemsData as $row) {
                PosOrderItem::create([
                    'pos_order_id' => $order->id,
                    'product_id' => $row['product_id'] ?? null,
                    'product_name' => $row['product_name'] ?? 'Item',
                    'unit_price' => (float) ($row['unit_price'] ?? 0),
                    'quantity' => (float) ($row['quantity'] ?? 1),
                    'subtotal' => (float) ($row['quantity'] ?? 1) * (float) ($row['unit_price'] ?? 0),
                    'total_price' => (float) ($row['quantity'] ?? 1) * (float) ($row['unit_price'] ?? 0),
                ]);
            }

            return $order;
        });
    }

    /**
     * Process Void order.
     */
    public function voidOrder(PosOrder $order, User $user, string $reason): PosOrder
    {
        return DB::transaction(function () use ($order, $user, $reason) {
            $order->update([
                'status' => PosOrder::STATUS_VOIDED,
                'void_reason' => $reason,
                'voided_by' => $user->id,
                'voided_at' => now(),
            ]);

            // Restore inventory
            if ($order->location_id) {
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        $this->stockService->restoreForPosRefund(
                            businessId: $order->business_id,
                            locationId: $order->location_id,
                            productId: $item->product_id,
                            quantity: (float) $item->quantity,
                            unitCost: (float) $item->unit_cost_hpp,
                            orderId: $order->id,
                            orderNumber: $order->order_number,
                            userId: $user->id
                        );
                    }
                }
            }

            return $order;
        });
    }

    /**
     * Process Refund / Return order.
     */
    public function refundOrder(PosOrder $order, User $user, string $reason, bool $restoreStock = true): PosOrder
    {
        return DB::transaction(function () use ($order, $user, $reason, $restoreStock) {
            $order = PosOrder::with(['items', 'payments'])->lockForUpdate()->findOrFail($order->id);
            if ($order->status !== PosOrder::STATUS_COMPLETED) {
                throw new InvalidArgumentException('Hanya transaksi POS completed yang dapat direfund penuh.');
            }

            $order->update([
                'status' => PosOrder::STATUS_REFUNDED,
                'refund_reason' => $reason,
                'refunded_by' => $user->id,
                'refunded_at' => now(),
            ]);

            if ($restoreStock && $order->location_id) {
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        $this->stockService->restoreForPosRefund(
                            businessId: $order->business_id,
                            locationId: $order->location_id,
                            productId: $item->product_id,
                            quantity: (float) $item->quantity,
                            unitCost: (float) $item->unit_cost_hpp,
                            orderId: $order->id,
                            orderNumber: $order->order_number,
                            userId: $user->id
                        );
                    }
                }
            }

            return $order;
        });
    }
}
