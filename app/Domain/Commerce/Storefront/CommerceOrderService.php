<?php

declare(strict_types=1);

namespace App\Domain\Commerce\Storefront;

use App\Domain\Inventory\StockService;
use App\Domain\WhatsApp\WhatsAppGatewayService;
use App\Models\Business;
use App\Models\CommerceOrder;
use App\Models\CommerceOrderItem;
use App\Models\CommercePaymentMethod;
use App\Models\CommerceStoreSetting;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Product;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class CommerceOrderService
{
    public function __construct(
        private readonly StockService $stockService = new StockService(),
        private readonly WhatsAppGatewayService $waGateway = new WhatsAppGatewayService()
    ) {}

    /**
     * Generate unique Storefront Order Number: ORD-YYYYMMDD-XXXX
     */
    public function generateOrderNumber(Business $business): string
    {
        $todayPrefix = 'ORD-' . date('Ymd') . '-';
        $latest = CommerceOrder::where('order_number', 'like', "{$todayPrefix}%")
            ->orderByDesc('order_number')
            ->first();

        if ($latest) {
            $lastSeq = (int) substr($latest->order_number, -4);
            $seq = str_pad((string) ($lastSeq + 1), 4, '0', STR_PAD_LEFT);
        } else {
            $seq = '0001';
        }

        $candidate = $todayPrefix . $seq;
        while (CommerceOrder::where('order_number', $candidate)->exists()) {
            $seq = str_pad((string) (((int) $seq) + 1), 4, '0', STR_PAD_LEFT);
            $candidate = $todayPrefix . $seq;
        }

        return $candidate;
    }

    /**
     * Generate secure cryptographic random tracking token for public customer URL.
     */
    public function generateTrackingToken(): string
    {
        return hash('sha256', Str::random(40) . microtime(true));
    }

    /**
     * Process Direct Checkout order from the public storefront.
     *
     * @param array{
     *     name: string,
     *     phone: string,
     *     email?: string|null,
     *     address?: string|null,
     *     notes?: string|null
     * } $customerData
     * @param array<int, array{
     *     product_id: string,
     *     quantity: float|int,
     *     notes?: string|null,
     *     selected_modifiers?: array<int, string>
     * }> $itemsData
     * @param array<string, mixed> $options
     */
    public function createCheckoutOrder(
        Business $business,
        array $customerData,
        array $itemsData,
        string $fulfillmentType,
        ?string $paymentMethodId = null,
        array $options = []
    ): CommerceOrder {
        if (! $business->is_active) {
            throw new DomainException('Bisnis sedang tidak aktif menerima pesanan.');
        }

        // 1. Validate Store Settings
        $setting = CommerceStoreSetting::firstOrCreate(
            ['business_id' => $business->id],
            [
                'is_storefront_enabled' => true,
                'allow_pickup' => true,
                'allow_delivery' => true,
                'order_auto_cancel_minutes' => 60,
            ]
        );

        if (! $setting->is_storefront_enabled) {
            throw new DomainException('Toko online saat ini sedang ditutup oleh pemilik bisnis.');
        }

        if ($fulfillmentType === CommerceOrder::FULFILLMENT_PICKUP && ! $setting->allow_pickup) {
            throw new DomainException('Metode ambil di toko (pickup) sedang dinonaktifkan.');
        }

        if ($fulfillmentType === CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY && ! $setting->allow_delivery) {
            throw new DomainException('Metode pengiriman ke alamat (delivery) sedang dinonaktifkan.');
        }

        // 2. Validate Customer Data
        $custName = trim($customerData['name'] ?? '');
        $custPhone = $this->normalizePhone((string) ($customerData['phone'] ?? ''));

        if ($custName === '') {
            throw new InvalidArgumentException('Nama pemesan wajib diisi.');
        }

        if ($custPhone === '' || strlen($custPhone) < 9) {
            throw new InvalidArgumentException('Nomor WhatsApp pemesan tidak valid.');
        }

        $address = trim($customerData['address'] ?? '');
        if ($fulfillmentType === CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY && $address === '') {
            throw new InvalidArgumentException('Alamat pengiriman wajib diisi untuk layanan antar.');
        }

        if (empty($itemsData)) {
            throw new InvalidArgumentException('Keranjang belanja tidak boleh kosong.');
        }

        // 3. Resolve Location
        $locationId = Location::where('business_id', $business->id)->where('is_primary', true)->value('id')
            ?? Location::where('business_id', $business->id)->value('id');

        if (! $locationId) {
            throw new DomainException('Lokasi operasional toko belum dikonfigurasi.');
        }

        // 4. Resolve Payment Method
        $paymentMethod = null;
        if ($paymentMethodId) {
            $paymentMethod = CommercePaymentMethod::where('business_id', $business->id)
                ->where('is_active', true)
                ->find($paymentMethodId);
        }

        return DB::transaction(function () use (
            $business,
            $setting,
            $locationId,
            $customerData,
            $custName,
            $custPhone,
            $address,
            $itemsData,
            $fulfillmentType,
            $paymentMethod,
            $options
        ) {
            // Find or Auto-Create CRM Customer, or link logged in customer
            $authCustomer = auth('customer')->user();
            if ($authCustomer instanceof Customer) {
                $customer = $authCustomer;
                if (! $customer->shipping_address && $address) {
                    $customer->update(['shipping_address' => $address]);
                }
            } else {
                $altPhone = str_starts_with($custPhone, '62')
                    ? '0' . substr($custPhone, 2)
                    : (str_starts_with($custPhone, '0') ? '62' . substr($custPhone, 1) : $custPhone);

                $customer = Customer::where(function ($q) use ($business) {
                    $q->where('business_id', $business->id)->orWhereNull('business_id');
                })
                ->where(function ($pq) use ($custPhone, $altPhone) {
                    $pq->where('phone', $custPhone)->orWhere('phone', $altPhone);
                })
                ->first();

                if (! $customer) {
                    $customer = Customer::create([
                        'business_id' => $business->id,
                        'name' => $custName,
                        'phone' => $custPhone,
                        'email' => $customerData['email'] ?? null,
                        'shipping_address' => $address ?: null,
                        'segment' => 'retail',
                        'is_active' => true,
                    ]);
                }
            }

            $orderNumber = $this->generateOrderNumber($business);
            $trackingToken = $this->generateTrackingToken();
            $autoCancelMinutes = $setting->order_auto_cancel_minutes ?: 60;
            $reservedUntil = Carbon::now()->addMinutes($autoCancelMinutes);

            $subtotal = 0.0;
            $processedItems = [];

            // Process Items and Reserve Stock Atomically
            foreach ($itemsData as $row) {
                $qty = (float) ($row['quantity'] ?? 1);
                if ($qty <= 0) {
                    continue;
                }

                $productId = (string) ($row['product_id'] ?? '');
                $product = Product::where('business_id', $business->id)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->findOrFail($productId);

                $unitPrice = (float) $product->selling_price;
                $lineSubtotal = $qty * $unitPrice;
                $subtotal += $lineSubtotal;

                // Reserve Stock for physical goods
                if ($product->isGoods()) {
                    $this->stockService->reserveProductStock(
                        businessId: $business->id,
                        locationId: $locationId,
                        product: $product,
                        productQuantity: $qty
                    );
                }

                $processedItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_type' => $product->type ?? Product::TYPE_GOODS,
                    'unit_price' => $unitPrice,
                    'quantity' => $qty,
                    'subtotal' => $lineSubtotal,
                    'notes' => $row['notes'] ?? null,
                    'modifiers_snapshot' => $row['selected_modifiers'] ?? null,
                ];
            }

            if ($subtotal < (float) $setting->min_order_amount) {
                $formattedMin = number_format((float) $setting->min_order_amount, 0, ',', '.');
                throw new DomainException("Minimum total belanja adalah Rp {$formattedMin}.");
            }

            $shippingCost = 0.0;
            $shippingRuleId = $options['shipping_rule_id'] ?? null;
            if ($fulfillmentType === CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY) {
                $shippingResult = (new CommerceShippingService())->calculateShipping(
                    business: $business,
                    subtotal: $subtotal,
                    distanceKm: isset($options['distance_km']) ? (float) $options['distance_km'] : null,
                    preferredRuleId: $shippingRuleId
                );
                $shippingCost = (float) $shippingResult['shipping_fee'];
                $shippingRuleId = $shippingResult['applied_rule_id'];
            }

            $totalAmount = $subtotal + $shippingCost;

            // Create Order
            $order = CommerceOrder::create([
                'business_id' => $business->id,
                'location_id' => $locationId,
                'customer_id' => $customer->id,
                'payment_method_id' => $paymentMethod?->id,
                'shipping_rule_id' => $shippingRuleId,
                'order_number' => $orderNumber,
                'tracking_token' => $trackingToken,
                'order_type' => $options['order_type'] ?? CommerceOrder::TYPE_DIRECT_CHECKOUT,
                'fulfillment_type' => $fulfillmentType,
                'status' => CommerceOrder::STATUS_PENDING_PAYMENT,
                'payment_status' => CommerceOrder::PAYMENT_UNPAID,
                'customer_name' => $custName,
                'customer_phone' => $custPhone,
                'customer_email' => $customerData['email'] ?? null,
                'shipping_address' => $fulfillmentType === CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY ? $address : null,
                'shipping_notes' => $customerData['notes'] ?? null,
                'scheduled_date' => $options['scheduled_date'] ?? null,
                'scheduled_time_slot' => $options['scheduled_time_slot'] ?? null,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'discount_amount' => 0.0,
                'total_amount' => $totalAmount,
                'reserved_until' => $reservedUntil,
                'notes' => $options['order_notes'] ?? null,
            ]);

            foreach ($processedItems as $item) {
                CommerceOrderItem::create([
                    'commerce_order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'product_type' => $item['product_type'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                    'notes' => $item['notes'],
                    'modifiers_snapshot' => $item['modifiers_snapshot'],
                ]);
            }

            // Send async / safe notification
            $this->sendOrderCreatedNotification($order, $business, $paymentMethod);

            return $order;
        });
    }

    /**
     * Create a scheduled order with lead time, cut-off time, and capacity quota enforcement.
     */
    public function createScheduledOrder(
        Business $business,
        array $customerData,
        array $itemsData,
        string $fulfillmentType,
        string $scheduledDate,
        ?string $scheduledTimeSlot = null,
        ?string $paymentMethodId = null,
        array $options = []
    ): CommerceOrder {
        if (! $business->is_active) {
            throw new DomainException('Bisnis sedang tidak aktif menerima pesanan.');
        }

        $setting = CommerceStoreSetting::firstOrCreate(
            ['business_id' => $business->id],
            ['is_storefront_enabled' => true, 'allow_scheduled_order' => true]
        );

        if (! $setting->is_storefront_enabled || ! ($setting->allow_scheduled_order ?? true)) {
            throw new DomainException('Layanan Pesanan Terjadwal sedang dinonaktifkan oleh toko.');
        }

        // Validate scheduled date format
        $targetDate = Carbon::parse($scheduledDate)->startOfDay();
        if ($targetDate->isPast() && ! $targetDate->isToday()) {
            throw new DomainException('Tanggal pesanan terjadwal tidak boleh di masa lampau.');
        }

        // Validate lead time
        $leadTimeHours = (int) ($setting->lead_time_hours ?? 0);
        if ($leadTimeHours > 0) {
            $earliestAllowed = Carbon::now()->addHours($leadTimeHours);
            if ($targetDate->endOfDay()->isBefore($earliestAllowed)) {
                throw new DomainException("Toko membutuhkan waktu persiapan minimum {$leadTimeHours} jam. Silakan pilih tanggal setelah {$earliestAllowed->translatedFormat('d F Y H:i')}.");
            }
        }

        // Validate cut-off time for next-day orders
        if (! empty($setting->cut_off_time)) {
            $cutoffToday = Carbon::parse($setting->cut_off_time);
            if ($targetDate->isTomorrow() && Carbon::now()->isAfter($cutoffToday)) {
                $cutoffStr = $cutoffToday->format('H:i');
                throw new DomainException("Batas pemesanan untuk besok telah ditutup (jam cut-off: {$cutoffStr} WIB). Silakan pilih tanggal berikutnya.");
            }
        }

        // Validate daily quota
        $quota = (int) ($setting->daily_order_quota ?? 0);
        if ($quota > 0) {
            $existingCount = CommerceOrder::where('business_id', $business->id)
                ->whereDate('scheduled_date', $targetDate->toDateString())
                ->whereNotIn('status', [CommerceOrder::STATUS_CANCELLED, CommerceOrder::STATUS_EXPIRED])
                ->count();

            if ($existingCount >= $quota) {
                throw new DomainException("Kapasitas kuota pesanan untuk tanggal {$targetDate->translatedFormat('d F Y')} sudah penuh (Maks. {$quota} pesanan). Silakan pilih tanggal lain.");
            }
        }

        $mergedOptions = array_merge($options, [
            'scheduled_date' => $targetDate->toDateString(),
            'scheduled_time_slot' => $scheduledTimeSlot,
            'order_type' => CommerceOrder::TYPE_SCHEDULED_ORDER,
        ]);

        return $this->createCheckoutOrder(
            business: $business,
            customerData: $customerData,
            itemsData: $itemsData,
            fulfillmentType: $fulfillmentType,
            paymentMethodId: $paymentMethodId,
            options: $mergedOptions
        );
    }

    /**
     * Create a customer custom request order (RFQ / catering / custom goods) pending merchant review.
     */
    public function createRequestOrder(
        Business $business,
        array $customerData,
        array $itemsData,
        string $fulfillmentType,
        array $options = []
    ): CommerceOrder {
        if (! $business->is_active) {
            throw new DomainException('Bisnis sedang tidak aktif menerima pesanan.');
        }

        $setting = CommerceStoreSetting::firstOrCreate(
            ['business_id' => $business->id],
            ['is_storefront_enabled' => true, 'allow_request_order' => true]
        );

        if (! $setting->is_storefront_enabled || ! ($setting->allow_request_order ?? true)) {
            throw new DomainException('Layanan Request Order (pesanan khusus) sedang tidak aktif.');
        }

        $custName = trim($customerData['name'] ?? '');
        $custPhone = preg_replace('/[^0-9]/', '', (string) ($customerData['phone'] ?? ''));
        if (str_starts_with($custPhone, '0')) {
            $custPhone = '62' . substr($custPhone, 1);
        }

        if ($custName === '') {
            throw new InvalidArgumentException('Nama pemesan wajib diisi.');
        }
        if ($custPhone === '' || strlen($custPhone) < 9) {
            throw new InvalidArgumentException('Nomor WhatsApp pemesan tidak valid.');
        }

        $address = trim($customerData['address'] ?? '');
        if ($fulfillmentType === CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY && $address === '') {
            throw new InvalidArgumentException('Alamat pengiriman wajib diisi untuk layanan antar.');
        }

        if (empty($itemsData)) {
            throw new InvalidArgumentException('Daftar permintaan barang tidak boleh kosong.');
        }

        $locationId = Location::where('business_id', $business->id)->where('is_primary', true)->value('id')
            ?? Location::where('business_id', $business->id)->value('id');

        if (! $locationId) {
            throw new DomainException('Lokasi operasional toko belum dikonfigurasi.');
        }

        return DB::transaction(function () use (
            $business,
            $locationId,
            $customerData,
            $custName,
            $custPhone,
            $address,
            $itemsData,
            $fulfillmentType,
            $options
        ) {
            $authCustomer = auth('customer')->user();
            if ($authCustomer instanceof Customer) {
                $customer = $authCustomer;
                if (! $customer->shipping_address && $address) {
                    $customer->update(['shipping_address' => $address]);
                }
            } else {
                $altPhone = str_starts_with($custPhone, '62')
                    ? '0' . substr($custPhone, 2)
                    : (str_starts_with($custPhone, '0') ? '62' . substr($custPhone, 1) : $custPhone);

                $customer = Customer::where(function ($q) use ($business) {
                    $q->where('business_id', $business->id)->orWhereNull('business_id');
                })
                ->where(function ($pq) use ($custPhone, $altPhone) {
                    $pq->where('phone', $custPhone)->orWhere('phone', $altPhone);
                })
                ->first();

                if (! $customer) {
                    $customer = Customer::create([
                        'business_id' => $business->id,
                        'name' => $custName,
                        'phone' => $custPhone,
                        'email' => $customerData['email'] ?? null,
                        'shipping_address' => $address ?: null,
                        'segment' => 'retail',
                        'is_active' => true,
                    ]);
                }
            }

            $orderNumber = $this->generateOrderNumber($business);
            $trackingToken = $this->generateTrackingToken();

            $subtotal = 0.0;
            $processedItems = [];

            foreach ($itemsData as $row) {
                $qty = (float) ($row['quantity'] ?? 1);
                if ($qty <= 0) continue;

                $productId = ! empty($row['product_id']) ? (string) $row['product_id'] : null;
                $productName = trim($row['product_name'] ?? '');
                $unitPrice = (float) ($row['unit_price'] ?? 0.0);

                if ($productId) {
                    $product = Product::where('business_id', $business->id)->find($productId);
                    if ($product) {
                        $productName = $product->name;
                        if ($unitPrice <= 0.0) {
                            $unitPrice = (float) $product->selling_price;
                        }
                    }
                }

                if ($productName === '') {
                    $productName = 'Item Permintaan Khusus';
                }

                $lineSubtotal = $qty * $unitPrice;
                $subtotal += $lineSubtotal;

                $processedItems[] = [
                    'product_id' => $productId,
                    'product_name' => $productName,
                    'product_type' => $row['product_type'] ?? Product::TYPE_GOODS,
                    'unit_price' => $unitPrice,
                    'quantity' => $qty,
                    'subtotal' => $lineSubtotal,
                    'notes' => $row['notes'] ?? null,
                ];
            }

            $order = CommerceOrder::create([
                'business_id' => $business->id,
                'location_id' => $locationId,
                'customer_id' => $customer->id,
                'order_number' => $orderNumber,
                'tracking_token' => $trackingToken,
                'order_type' => CommerceOrder::TYPE_REQUEST_ORDER,
                'fulfillment_type' => $fulfillmentType,
                'status' => CommerceOrder::STATUS_PENDING_REVIEW,
                'payment_status' => CommerceOrder::PAYMENT_UNPAID,
                'customer_name' => $custName,
                'customer_phone' => $custPhone,
                'customer_email' => $customerData['email'] ?? null,
                'shipping_address' => $fulfillmentType === CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY ? $address : null,
                'shipping_notes' => $customerData['notes'] ?? null,
                'scheduled_date' => $options['scheduled_date'] ?? null,
                'scheduled_time_slot' => $options['scheduled_time_slot'] ?? null,
                'subtotal' => $subtotal,
                'shipping_cost' => 0.0,
                'discount_amount' => 0.0,
                'total_amount' => $subtotal,
                'notes' => $options['order_notes'] ?? null,
            ]);

            foreach ($processedItems as $item) {
                CommerceOrderItem::create([
                    'commerce_order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'product_type' => $item['product_type'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                    'notes' => $item['notes'],
                ]);
            }

            return $order;
        });
    }

    /**
     * Submit finalized quotation for a customer Request Order and transition to pending_payment.
     */
    public function quoteRequestOrder(
        CommerceOrder $order,
        array $itemsQuotation,
        float $shippingCost,
        ?string $paymentMethodId = null,
        ?string $notes = null
    ): CommerceOrder {
        if ($order->order_type !== CommerceOrder::TYPE_REQUEST_ORDER) {
            throw new DomainException('Penawaran harga hanya dapat diberikan pada pesanan berjenis Request Order.');
        }

        if ($order->status !== CommerceOrder::STATUS_PENDING_REVIEW) {
            throw new DomainException('Status pesanan saat ini tidak dalam tahap peninjauan (Pending Review).');
        }

        return DB::transaction(function () use ($order, $itemsQuotation, $shippingCost, $paymentMethodId, $notes) {
            $subtotal = 0.0;

            foreach ($itemsQuotation as $quoted) {
                $itemId = $quoted['id'] ?? null;
                $item = $order->items()->findOrFail($itemId);
                $newUnitPrice = (float) ($quoted['unit_price'] ?? $item->unit_price);
                $newQty = (float) ($quoted['quantity'] ?? $item->quantity);
                $lineSubtotal = $newUnitPrice * $newQty;
                $subtotal += $lineSubtotal;

                $item->update([
                    'unit_price' => $newUnitPrice,
                    'quantity' => $newQty,
                    'subtotal' => $lineSubtotal,
                ]);

                // If product is linked and is physical goods, reserve stock now!
                if ($item->product_id && $item->product && $item->product->isGoods()) {
                    $this->stockService->reserveProductStock(
                        businessId: $order->business_id,
                        locationId: $order->location_id,
                        product: $item->product,
                        productQuantity: $newQty
                    );
                }
            }

            $totalAmount = $subtotal + max(0.0, $shippingCost);
            $autoCancelMinutes = $order->business->storeSetting?->order_auto_cancel_minutes ?: 1440;
            $reservedUntil = Carbon::now()->addMinutes($autoCancelMinutes);

            $order->update([
                'status' => CommerceOrder::STATUS_PENDING_PAYMENT,
                'subtotal' => $subtotal,
                'shipping_cost' => max(0.0, $shippingCost),
                'total_amount' => $totalAmount,
                'payment_method_id' => $paymentMethodId ?: $order->payment_method_id,
                'reserved_until' => $reservedUntil,
                'notes' => $notes ?: $order->notes,
            ]);

            // Notify customer via WhatsApp about the quotation
            try {
                $trackingUrl = url("/b/{$order->business->slug}/order/{$order->tracking_token}");
                $totalFmt = number_format($totalAmount, 0, ',', '.');
                $msg = "Halo *{$order->customer_name}*, penawaran harga untuk pesanan khusus Anda di *{$order->business->name}* sudah siap!\n\n";
                $msg .= "📋 *No. Pesanan:* #{$order->order_number}\n";
                $msg .= "💰 *Total Biaya:* Rp {$totalFmt}\n\n";
                $msg .= "Silakan periksa detail penawaran dan lakukan konfirmasi pembayaran melalui tautan berikut:\n";
                $msg .= "👉 {$trackingUrl}\n\n";
                $msg .= "Terima kasih!";
                $this->waGateway->sendMessage($order->business, $order->customer_phone, $msg);
            } catch (\Throwable $e) {
                Log::warning("[CommerceOrderService] WA quotation notif failed: " . $e->getMessage());
            }

            return $order->fresh(['items.product', 'paymentMethod']);
        });
    }

    /**
     * Dispatch WhatsApp notification safely.
     */
    private function sendOrderCreatedNotification(CommerceOrder $order, Business $business, ?CommercePaymentMethod $paymentMethod): void
    {
        try {
            $trackingUrl = url("/b/{$business->slug}/order/{$order->tracking_token}");
            $totalFmt = number_format((float) $order->total_amount, 0, ',', '.');

            $msg = "Halo *{$order->customer_name}*, pesanan Anda di *{$business->name}* berhasil dibuat!\n\n";
            $msg .= "📋 *No. Pesanan:* #{$order->order_number}\n";
            $msg .= "💰 *Total Pembayaran:* Rp {$totalFmt}\n";

            if ($paymentMethod) {
                $msg .= "🏦 *Metode:* {$paymentMethod->bank_name}\n";
                if ($paymentMethod->account_number) {
                    $msg .= "🔢 *No. Rekening:* {$paymentMethod->account_number} (a.n {$paymentMethod->account_holder})\n";
                }
            }

            $msg .= "\nSilakan selesaikan pembayaran dan unggah bukti transfer melalui tautan resmi berikut:\n";
            $msg .= "👉 {$trackingUrl}\n\n";
            $msg .= "Terima kasih!";

            $this->waGateway->sendMessage($business, $order->customer_phone, $msg);
        } catch (\Throwable $e) {
            Log::warning("[CommerceOrderService] WA notif failed: " . $e->getMessage());
        }
    }

    /**
     * Normalize phone number to Indonesian international format (628...).
     */
    public function normalizePhone(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        }

        return $clean;
    }
}
