<?php

declare(strict_types=1);

namespace App\Domain\Pos;

use App\Domain\Accounting\AutoJournalService;
use App\Domain\Crm\LoyaltyService;
use App\Domain\Finance\CashLedgerService;
use App\Domain\Inventory\StockService;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderItemModifier;
use App\Models\PosOrderPayment;
use App\Models\PosShift;
use App\Models\PosTable;
use App\Models\PosTableSession;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Voucher;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class PosOrderService
{
    public function __construct(
        private readonly StockService $stockService = new StockService,
        private readonly AutoJournalService $journalService = new AutoJournalService,
        private readonly LoyaltyService $loyaltyService = new LoyaltyService,
        private readonly ModifierService $modifierService = new ModifierService,
        private readonly PosTableService $tableService = new PosTableService,
        private readonly CashLedgerService $cashLedgerService = new CashLedgerService
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
     * Complete a POS checkout transaction (Direct / Walk-in Cashier).
     *
     * @param array<int, array{
     *     product_id?: string|null,
     *     product_name?: string|null,
     *     unit_price: float|int,
     *     quantity: float|int,
     *     discount_amount?: float|int|null,
     *     notes?: string|null,
     *     batch_number?: string|null,
     *     serial_number?: string|null,
     *     selected_modifiers?: array<int, string>|null
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
            $existingOrder = null;
            if (! empty($attributes['existing_order_id'])) {
                $existingOrder = PosOrder::where('business_id', $business->id)
                    ->whereNotIn('status', [PosOrder::STATUS_COMPLETED, PosOrder::STATUS_VOIDED])
                    ->with('items.modifiers')
                    ->find($attributes['existing_order_id']);
            }

            $orderNumber = $existingOrder ? $existingOrder->order_number : ($attributes['order_number'] ?? $this->generateOrderNumber($business));
            $locationId = $attributes['location_id'] ?? $shift?->location_id ?? Location::where('business_id', $business->id)->where('is_primary', true)->value('id') ?? Location::where('business_id', $business->id)->value('id');
            $customerId = ! empty($attributes['customer_id']) ? (string) $attributes['customer_id'] : null;
            $customer = $customerId ? Customer::find($customerId) : null;

            // 1. Calculate items subtotal, modifiers and HPP
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
                $baseUnitPrice = (float) ($product?->selling_price ?? $row['unit_price'] ?? 0.0);

                // Modifiers validation and server-side calculation
                $selectedModifiers = $row['selected_modifiers'] ?? [];
                $modifierSnapshots = [];
                $modPriceDelta = 0.0;

                if ($product && ! empty($selectedModifiers)) {
                    $modResolution = $this->modifierService->validateAndResolveModifiers($product, $selectedModifiers, $locationId);
                    $modPriceDelta = $modResolution['total_price_delta'];
                    $modifierSnapshots = $modResolution['snapshots'];
                }

                $unitPrice = $baseUnitPrice + $modPriceDelta;

                // Unit HPP from Product base_cost or active BOM/cost model
                $unitHpp = 0.0;
                if ($product) {
                    $unitHpp = (float) $product->base_cost;
                    if ($unitHpp <= 0 && $product->activeCostModel) {
                        $costModel = $product->activeCostModel;
                        $latestRun = $costModel->relationLoaded('costingRuns')
                            ? $costModel->costingRuns->sortByDesc('created_at')->first()
                            : $costModel->costingRuns()->with('result')->latest()->first();
                        $unitHpp = (float) ($latestRun?->result?->hpp_per_unit ?? 0.0);
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
                    'modifiers' => $modifierSnapshots,
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

            // 6. Create or Update PosOrder (Settle Table Order)
            if ($existingOrder) {
                // Delete previous unpaid items and their modifiers before saving finalized items
                foreach ($existingOrder->items as $oldItem) {
                    $oldItem->modifiers()->delete();
                    $oldItem->delete();
                }

                $existingOrder->update([
                    'location_id' => $locationId,
                    'pos_shift_id' => $shift?->id,
                    'user_id' => $cashier->id,
                    'customer_id' => $customer?->id,
                    'order_date' => Carbon::today()->toDateString(),
                    'status' => PosOrder::STATUS_COMPLETED,
                    'order_type' => $attributes['order_type'] ?? ($existingOrder->order_type ?? 'dine_in'),
                    'pos_table_id' => $attributes['pos_table_id'] ?? $existingOrder->pos_table_id,
                    'pos_table_session_id' => $attributes['pos_table_session_id'] ?? $existingOrder->pos_table_session_id,
                    'table_or_reference' => $attributes['table_or_reference'] ?? $existingOrder->table_or_reference,
                    'customer_name_guest' => $attributes['customer_name_guest'] ?? $existingOrder->customer_name_guest,
                    'customer_phone_guest' => $attributes['customer_phone_guest'] ?? $existingOrder->customer_phone_guest,
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
                    'notes' => $attributes['notes'] ?? $existingOrder->notes,
                ]);

                $order = $existingOrder;
            } else {
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
                    'order_source' => $attributes['order_source'] ?? PosOrder::SOURCE_POS,
                    'pos_table_id' => $attributes['pos_table_id'] ?? null,
                    'pos_table_session_id' => $attributes['pos_table_session_id'] ?? null,
                    'table_or_reference' => $attributes['table_or_reference'] ?? null,
                    'customer_name_guest' => $attributes['customer_name_guest'] ?? ($customer?->name ?? 'Pelanggan Umum'),
                    'customer_phone_guest' => $attributes['customer_phone_guest'] ?? null,
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
            }

            // 7. Save Line Items, Modifiers and Deduct Inventory Stock
            foreach ($processedItems as $itemInfo) {
                $orderItem = PosOrderItem::create([
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

                // Save modifier snapshots
                foreach ($itemInfo['modifiers'] as $modSnap) {
                    PosOrderItemModifier::create([
                        'pos_order_item_id' => $orderItem->id,
                        'modifier_group_id' => $modSnap['modifier_group_id'],
                        'modifier_option_id' => $modSnap['modifier_option_id'],
                        'modifier_group_name' => $modSnap['modifier_group_name'],
                        'modifier_option_name' => $modSnap['modifier_option_name'],
                        'unit_price' => $modSnap['unit_price'],
                        'quantity' => $modSnap['quantity'],
                        'subtotal' => $modSnap['subtotal'],
                        'material_snapshot' => $modSnap['material_snapshot'],
                    ]);

                    // Deduct stock for modifier materials
                    if (! empty($modSnap['material_snapshot']) && $locationId) {
                        foreach ($modSnap['material_snapshot'] as $mat) {
                            $totalMatQty = ((float) $mat['quantity']) * $itemInfo['quantity'];
                            if ($totalMatQty > 0) {
                                $this->stockService->recordMovement(
                                    businessId: $business->id,
                                    locationId: $locationId,
                                    productId: null,
                                    movementType: StockMovement::TYPE_POS_SALE,
                                    quantityChange: -abs($totalMatQty),
                                    unitCost: 0.0,
                                    referenceId: $order->id,
                                    referenceNumber: $order->order_number,
                                    notes: "Modifier {$modSnap['modifier_option_name']} untuk {$itemInfo['product_name']} #{$order->order_number}",
                                    userId: $cashier->id,
                                    materialId: $mat['material_id']
                                );
                            }
                        }
                    }
                }

                // Base Product stock reduction
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

            // 8. Save Payments & Inflow Tracking
            $remainingChange = (float) $order->change_amount;
            foreach ($paymentsData as $p) {
                $payMethod = (string) ($p['payment_method'] ?? 'cash');
                $payAmount = (float) ($p['amount'] ?? 0.0);
                if ($payAmount <= 0) {
                    continue;
                }

                $payment = PosOrderPayment::create([
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
                } elseif ($payMethod !== PosOrderPayment::METHOD_LOYALTY_POINTS) {
                    $netCashIn = $payAmount;
                    if ($payMethod === PosOrderPayment::METHOD_CASH && $remainingChange > 0) {
                        $deduct = min($payAmount, $remainingChange);
                        $netCashIn -= $deduct;
                        $remainingChange -= $deduct;
                    }

                    if ($netCashIn > 0) {
                        $methodLabel = match ($payMethod) {
                            PosOrderPayment::METHOD_CASH => 'Tunai',
                            PosOrderPayment::METHOD_QRIS => 'QRIS',
                            PosOrderPayment::METHOD_TRANSFER => 'Transfer Bank',
                            PosOrderPayment::METHOD_EDC_DEBIT => 'EDC Debit',
                            PosOrderPayment::METHOD_EDC_CREDIT => 'EDC Kredit',
                            default => ucfirst(str_replace('_', ' ', $payMethod)),
                        };

                        $this->cashLedgerService->recordInflow(
                            business: $business,
                            amount: $netCashIn,
                            referenceType: 'pos_order',
                            referenceId: $payment->id,
                            description: "Penerimaan POS #{$order->order_number} ({$methodLabel})",
                            method: $payMethod,
                            userId: $cashier->id
                        );
                    }
                }
            }

            // 9. Customer Loyalty Points
            if ($customer) {
                $this->loyaltyService->awardPointsForOrder($customer, $order);
            }

            // 10. Automatic Accounting Journal
            $this->journalService->recordPosSaleJournal($order);

            // 11. Sync Table Status & Close Session if all orders completed
            $session = $order->tableSession;
            if ($session && $session->canBeClosed()) {
                $this->tableService->closeSession($session);
            } elseif ($order->pos_table_id && $order->posTable) {
                $this->tableService->syncTableStatus($order->posTable);
            }

            return $order->load(['items.modifiers', 'payments', 'customer', 'location', 'posTable']);
        });
    }

    /**
     * Submit an incoming order from a Customer scanning a Table QR code.
     * Starts in 'pending' status without immediate inventory deduction.
     *
     * @param array<int, array{
     *     product_id: string,
     *     quantity: float|int,
     *     selected_modifiers?: array<int, string>,
     *     notes?: string|null
     * }> $itemsData
     */
    public function createQrOrder(
        PosTable $table,
        string $customerName,
        string $customerPhone,
        array $itemsData,
        ?string $orderNotes = null
    ): PosOrder {
        $customerName = trim($customerName);
        $customerPhone = trim($customerPhone);

        if ($customerName === '') {
            throw new DomainException('Nama pelanggan wajib diisi.');
        }

        if ($customerPhone === '') {
            throw new DomainException('Nomor WhatsApp / HP wajib diisi.');
        }

        if (empty($itemsData)) {
            throw new DomainException('Pesanan tidak boleh kosong.');
        }

        $business = $table->business;
        if (! $business || ! $business->is_active) {
            throw new DomainException('Bisnis sedang tidak aktif menerima pesanan.');
        }

        if (! $table->is_active) {
            throw new DomainException('Meja sedang nonaktif.');
        }

        $locationId = $table->location_id
            ?? Location::where('business_id', $business->id)->where('is_primary', true)->value('id')
            ?? Location::where('business_id', $business->id)->value('id');

        return DB::transaction(function () use ($business, $table, $customerName, $customerPhone, $itemsData, $orderNotes, $locationId) {
            // Get or create table session
            $session = $this->tableService->getOrCreateActiveSession($table, $customerName, $customerPhone);
            $orderNumber = $this->generateOrderNumber($business);

            $subtotal = 0.0;
            $totalHpp = 0.0;
            $processedItems = [];

            foreach ($itemsData as $row) {
                $qty = (float) ($row['quantity'] ?? 1);
                if ($qty <= 0) {
                    continue;
                }

                $productId = (string) ($row['product_id'] ?? '');
                $product = Product::where('business_id', $business->id)
                    ->where('is_active', true)
                    ->findOrFail($productId);

                // Check product base availability
                $stock = $product->calculateEffectiveStock($locationId);
                if ($stock < $qty && ! $business->allow_negative_stock) {
                    throw new DomainException("Stok produk '{$product->name}' tidak mencukupi (tersedia: {$stock}).");
                }

                $baseUnitPrice = (float) $product->selling_price;

                // Validate and resolve modifiers
                $selectedModifiers = $row['selected_modifiers'] ?? [];
                $modResult = $this->modifierService->validateAndResolveModifiers($product, $selectedModifiers, $locationId);

                $unitPrice = $baseUnitPrice + $modResult['total_price_delta'];
                $lineSubtotal = $qty * $unitPrice;
                $unitHpp = (float) $product->base_cost;
                $lineHpp = $qty * $unitHpp;

                $subtotal += $lineSubtotal;
                $totalHpp += $lineHpp;

                $processedItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_code' => $product->code,
                    'unit_price' => $unitPrice,
                    'unit_cost_hpp' => $unitHpp,
                    'quantity' => $qty,
                    'subtotal' => $lineSubtotal,
                    'total_price' => $lineSubtotal,
                    'total_hpp' => $lineHpp,
                    'notes' => $row['notes'] ?? null,
                    'modifiers' => $modResult['snapshots'],
                ];
            }

            // Tax & Service Charge
            $taxPercent = $business->pos_enable_tax ? (float) $business->pos_tax_percent : 0.0;
            $servicePercent = $business->pos_enable_service_charge ? (float) $business->pos_service_charge_percent : 0.0;

            $taxAmount = ($subtotal * $taxPercent) / 100.0;
            $serviceChargeAmount = ($subtotal * $servicePercent) / 100.0;
            $finalTotal = round($subtotal + $taxAmount + $serviceChargeAmount, $business->currency_precision ?? 0);

            $order = PosOrder::create([
                'business_id' => $business->id,
                'location_id' => $locationId,
                'user_id' => $business->users()->first()?->id ?? null,
                'order_number' => $orderNumber,
                'order_date' => Carbon::today()->toDateString(),
                'status' => PosOrder::STATUS_PENDING,
                'order_type' => 'dine_in',
                'order_source' => PosOrder::SOURCE_QR_TABLE,
                'pos_table_id' => $table->id,
                'pos_table_session_id' => $session->id,
                'table_or_reference' => $table->table_number,
                'customer_name_guest' => $customerName,
                'customer_phone_guest' => $customerPhone,
                'subtotal' => $subtotal,
                'tax_percentage' => $taxPercent,
                'tax_amount' => $taxAmount,
                'service_charge_percentage' => $servicePercent,
                'service_charge_amount' => $serviceChargeAmount,
                'total_amount' => $finalTotal,
                'paid_amount' => 0.0,
                'change_amount' => 0.0,
                'total_hpp_cost' => $totalHpp,
                'total_gross_profit' => max(0.0, $subtotal - $totalHpp),
                'notes' => $orderNotes,
            ]);

            foreach ($processedItems as $itemInfo) {
                $orderItem = PosOrderItem::create([
                    'pos_order_id' => $order->id,
                    'product_id' => $itemInfo['product_id'],
                    'product_name' => $itemInfo['product_name'],
                    'product_code' => $itemInfo['product_code'],
                    'unit_price' => $itemInfo['unit_price'],
                    'unit_cost_hpp' => $itemInfo['unit_cost_hpp'],
                    'quantity' => $itemInfo['quantity'],
                    'subtotal' => $itemInfo['subtotal'],
                    'discount_amount' => 0.0,
                    'total_price' => $itemInfo['total_price'],
                    'total_hpp' => $itemInfo['total_hpp'],
                    'notes' => $itemInfo['notes'],
                ]);

                foreach ($itemInfo['modifiers'] as $modSnap) {
                    PosOrderItemModifier::create([
                        'pos_order_item_id' => $orderItem->id,
                        'modifier_group_id' => $modSnap['modifier_group_id'],
                        'modifier_option_id' => $modSnap['modifier_option_id'],
                        'modifier_group_name' => $modSnap['modifier_group_name'],
                        'modifier_option_name' => $modSnap['modifier_option_name'],
                        'unit_price' => $modSnap['unit_price'],
                        'quantity' => $modSnap['quantity'],
                        'subtotal' => $modSnap['subtotal'],
                        'material_snapshot' => $modSnap['material_snapshot'],
                    ]);
                }
            }

            $table->update(['status' => PosTable::STATUS_OCCUPIED]);

            return $order->load(['items.modifiers', 'posTable', 'tableSession']);
        });
    }

    /**
     * Cashier accepts an incoming QR order.
     */
    public function acceptQrOrder(PosOrder $order, User $cashier): PosOrder
    {
        if ($order->status !== PosOrder::STATUS_PENDING) {
            throw new DomainException('Hanya pesanan berstatus pending yang dapat diterima.');
        }

        $order->update([
            'status' => PosOrder::STATUS_CONFIRMED,
            'user_id' => $cashier->id,
        ]);

        if ($order->posTable) {
            $order->posTable->update(['status' => PosTable::STATUS_PREPARING]);
        }

        return $order->load(['items.modifiers', 'posTable']);
    }

    /**
     * Cashier rejects an incoming QR order with mandatory reason.
     */
    public function rejectQrOrder(PosOrder $order, User $cashier, string $reason): PosOrder
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw new DomainException('Alasan penolakan pesanan wajib diisi.');
        }

        if ($order->status !== PosOrder::STATUS_PENDING) {
            throw new DomainException('Hanya pesanan berstatus pending yang dapat ditolak.');
        }

        $order->update([
            'status' => PosOrder::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'rejected_by' => $cashier->id,
            'rejected_at' => now(),
        ]);

        if ($order->posTable) {
            $this->tableService->syncTableStatus($order->posTable);
        }

        return $order;
    }

    /**
     * Update order preparation status (e.g. from Kitchen/Bar).
     */
    public function updateOrderStatus(PosOrder $order, string $status, ?User $user = null): PosOrder
    {
        $validStatuses = [
            PosOrder::STATUS_CONFIRMED,
            PosOrder::STATUS_PREPARING,
            PosOrder::STATUS_READY,
            PosOrder::STATUS_SERVED,
            PosOrder::STATUS_WAITING_PAYMENT,
        ];

        if (! in_array($status, $validStatuses, true)) {
            throw new DomainException("Status pesanan '{$status}' tidak valid.");
        }

        $order->update(['status' => $status]);

        if ($order->posTable) {
            $this->tableService->syncTableStatus($order->posTable);
        }

        return $order;
    }

    /**
     * Process payment for an active Table QR Order (Completes transaction, deducts inventory, journals).
     *
     * @param array<int, array{payment_method: string, amount: float|int, reference_number?: string|null}> $paymentsData
     */
    public function payQrOrder(
        PosOrder $order,
        array $paymentsData,
        User $cashier,
        ?PosShift $shift = null
    ): PosOrder {
        if ($order->status === PosOrder::STATUS_COMPLETED) {
            throw new DomainException('Pesanan ini sudah lunas.');
        }

        if (empty($paymentsData)) {
            throw new InvalidArgumentException('Metode pembayaran wajib ditentukan.');
        }

        $business = $order->business;
        $locationId = $order->location_id ?? $shift?->location_id;

        return DB::transaction(function () use ($order, $paymentsData, $cashier, $shift, $business, $locationId) {
            $totalPaid = 0.0;
            foreach ($paymentsData as $p) {
                $totalPaid += (float) ($p['amount'] ?? 0.0);
            }

            if ($totalPaid < $order->total_amount) {
                throw new DomainException('Jumlah pembayaran kurang dari total tagihan pesanan.');
            }

            $changeAmount = max(0.0, $totalPaid - $order->total_amount);

            $order->update([
                'status' => PosOrder::STATUS_COMPLETED,
                'user_id' => $cashier->id,
                'pos_shift_id' => $shift?->id,
                'paid_amount' => $totalPaid,
                'change_amount' => $changeAmount,
            ]);

            // Deduct stock for base items and modifier materials
            foreach ($order->items as $item) {
                // Modifiers stock deduction
                foreach ($item->modifiers as $mod) {
                    if (! empty($mod->material_snapshot) && $locationId) {
                        foreach ($mod->material_snapshot as $mat) {
                            $totalMatQty = ((float) $mat['quantity']) * (float) $item->quantity;
                            if ($totalMatQty > 0) {
                                $this->stockService->recordMovement(
                                    businessId: $business->id,
                                    locationId: $locationId,
                                    productId: null,
                                    movementType: StockMovement::TYPE_POS_SALE,
                                    quantityChange: -abs($totalMatQty),
                                    unitCost: 0.0,
                                    referenceId: $order->id,
                                    referenceNumber: $order->order_number,
                                    notes: "Modifier {$mod->modifier_option_name} untuk {$item->product_name} #{$order->order_number}",
                                    userId: $cashier->id,
                                    materialId: $mat['material_id']
                                );
                            }
                        }
                    }
                }

                // Base product stock deduction
                if ($item->product_id && $locationId) {
                    $this->stockService->deductForPosSale(
                        businessId: $business->id,
                        locationId: $locationId,
                        productId: $item->product_id,
                        quantity: (float) $item->quantity,
                        unitCost: (float) $item->unit_cost_hpp,
                        orderId: $order->id,
                        orderNumber: $order->order_number,
                        userId: $cashier->id
                    );
                }
            }

            // Save payments & Inflow Tracking
            $remainingChange = (float) $order->change_amount;
            foreach ($paymentsData as $p) {
                $payMethod = (string) ($p['payment_method'] ?? 'cash');
                $amount = (float) ($p['amount'] ?? 0.0);
                if ($amount <= 0) continue;

                $payment = PosOrderPayment::create([
                    'pos_order_id' => $order->id,
                    'payment_method' => $payMethod,
                    'amount' => $amount,
                    'reference_number' => $p['reference_number'] ?? null,
                    'fee_amount' => 0.0,
                    'net_amount' => $amount,
                    'status' => 'paid',
                ]);

                if (! in_array($payMethod, [PosOrderPayment::METHOD_CUSTOMER_CREDIT, PosOrderPayment::METHOD_LOYALTY_POINTS], true)) {
                    $netCashIn = $amount;
                    if ($payMethod === PosOrderPayment::METHOD_CASH && $remainingChange > 0) {
                        $deduct = min($amount, $remainingChange);
                        $netCashIn -= $deduct;
                        $remainingChange -= $deduct;
                    }

                    if ($netCashIn > 0) {
                        $methodLabel = match ($payMethod) {
                            PosOrderPayment::METHOD_CASH => 'Tunai',
                            PosOrderPayment::METHOD_QRIS => 'QRIS',
                            PosOrderPayment::METHOD_TRANSFER => 'Transfer Bank',
                            PosOrderPayment::METHOD_EDC_DEBIT => 'EDC Debit',
                            PosOrderPayment::METHOD_EDC_CREDIT => 'EDC Kredit',
                            default => ucfirst(str_replace('_', ' ', $payMethod)),
                        };

                        $this->cashLedgerService->recordInflow(
                            business: $business,
                            amount: $netCashIn,
                            referenceType: 'pos_order',
                            referenceId: $payment->id,
                            description: "Penerimaan POS #{$order->order_number} ({$methodLabel})",
                            method: $payMethod,
                            userId: $cashier->id
                        );
                    }
                }
            }

            // Auto Journal
            $this->journalService->recordPosSaleJournal($order);

            // Table Session closing if all orders in session are finished
            $session = $order->tableSession;
            if ($session && $session->canBeClosed()) {
                $this->tableService->closeSession($session);
            } elseif ($order->posTable) {
                $this->tableService->syncTableStatus($order->posTable);
            }

            return $order->load(['items.modifiers', 'payments', 'posTable', 'tableSession']);
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
                    // Restore modifier materials
                    foreach ($item->modifiers as $mod) {
                        if (! empty($mod->material_snapshot)) {
                            foreach ($mod->material_snapshot as $mat) {
                                $totalMatQty = ((float) $mat['quantity']) * (float) $item->quantity;
                                if ($totalMatQty > 0) {
                                    $this->stockService->recordMovement(
                                        businessId: $order->business_id,
                                        locationId: $order->location_id,
                                        productId: null,
                                        movementType: StockMovement::TYPE_POS_REFUND,
                                        quantityChange: abs($totalMatQty),
                                        unitCost: 0.0,
                                        referenceId: $order->id,
                                        referenceNumber: $order->order_number,
                                        notes: "Void Modifier {$mod->modifier_option_name} untuk {$item->product_name} #{$order->order_number}",
                                        userId: $user->id,
                                        materialId: $mat['material_id']
                                    );
                                }
                            }
                        }
                    }

                    // Restore base product stock
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

            if ($order->posTable) {
                $this->tableService->syncTableStatus($order->posTable);
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
            $order = PosOrder::with(['items.modifiers', 'payments'])->lockForUpdate()->findOrFail($order->id);
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
                    // Restore modifier materials
                    foreach ($item->modifiers as $mod) {
                        if (! empty($mod->material_snapshot)) {
                            foreach ($mod->material_snapshot as $mat) {
                                $totalMatQty = ((float) $mat['quantity']) * (float) $item->quantity;
                                if ($totalMatQty > 0) {
                                    $this->stockService->recordMovement(
                                        businessId: $order->business_id,
                                        locationId: $order->location_id,
                                        productId: null,
                                        movementType: StockMovement::TYPE_POS_REFUND,
                                        quantityChange: abs($totalMatQty),
                                        unitCost: 0.0,
                                        referenceId: $order->id,
                                        referenceNumber: $order->order_number,
                                        notes: "Refund Modifier {$mod->modifier_option_name} untuk {$item->product_name} #{$order->order_number}",
                                        userId: $user->id,
                                        materialId: $mat['material_id']
                                    );
                                }
                            }
                        }
                    }

                    // Restore base product stock
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
