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

        foreach ($itemsData as $row) {
            $qty = (float) ($row['quantity'] ?? 0);
            if ($qty <= 0.0) {
                throw new InvalidArgumentException('Jumlah pesanan untuk setiap produk harus lebih dari 0.');
            }
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
            $hasPreorder = false;
            $maxPreorderLeadDays = 0;

            // Process Items and Reserve Stock Atomically
            foreach ($itemsData as $row) {
                $qty = (float) ($row['quantity'] ?? 0);
                if ($qty <= 0.0) {
                    throw new InvalidArgumentException('Jumlah pesanan untuk setiap produk harus lebih dari 0.');
                }

                $productId = (string) ($row['product_id'] ?? '');
                $product = Product::where('business_id', $business->id)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->findOrFail($productId);

                if (! ($product->show_in_website ?? true)) {
                    throw new DomainException("Produk '{$product->name}' tidak tersedia untuk pemesanan online.");
                }

                if ($product->isPreorder()) {
                    $hasPreorder = true;
                    $leadDays = (int) ($product->preorder_lead_days ?? 1);
                    if ($leadDays > $maxPreorderLeadDays) {
                        $maxPreorderLeadDays = $leadDays;
                    }
                }

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

            if (empty($processedItems)) {
                throw new InvalidArgumentException('Pesanan harus memiliki minimal 1 produk dengan jumlah lebih dari 0.');
            }

            if ($hasPreorder || ! empty($options['scheduled_date'])) {
                if (empty($options['scheduled_date'])) {
                    throw new DomainException('Pesanan Anda memuat produk Pre-Order. Silakan tentukan tanggal jadwal pengiriman/pengambilan.');
                }
                $targetDate = Carbon::parse($options['scheduled_date'])->startOfDay();
                if ($hasPreorder) {
                    $earliestDate = Carbon::today()->addDays($maxPreorderLeadDays);
                    if ($targetDate->isBefore($earliestDate)) {
                        throw new DomainException("Produk Pre-Order dalam pesanan Anda membutuhkan waktu persiapan minimal {$maxPreorderLeadDays} hari (paling cepat tanggal {$this->formatIndonesianDate($earliestDate, true)}).");
                    }
                }

                // Concurrency Safety: Lock store setting & order items under transaction to prevent overbooking
                $lockedSetting = CommerceStoreSetting::where('business_id', $business->id)->lockForUpdate()->first() ?? $setting;
                $quota = (int) ($lockedSetting->daily_order_quota ?? 0);
                $quotaMetric = $lockedSetting->quota_metric ?? 'orders';
                $quotaUnit = $lockedSetting->preorder_quota_unit ?? 'PCS';
                $batchMode = $lockedSetting->batch_dates_mode ?? 'operating_days';

                if ($batchMode === 'custom_dates' && ! empty($lockedSetting->custom_batch_dates)) {
                    foreach ($lockedSetting->custom_batch_dates as $cbd) {
                        if (($cbd['date'] ?? '') === $targetDate->toDateString() && isset($cbd['quota']) && is_numeric($cbd['quota'])) {
                            $quota = (int) $cbd['quota'];
                            break;
                        }
                    }
                }

                if ($quota > 0) {
                    if ($quotaMetric === 'quantity') {
                        $existingQty = (float) CommerceOrderItem::whereHas('order', function ($q) use ($business, $targetDate): void {
                            $q->where('business_id', $business->id)
                                ->whereDate('scheduled_date', $targetDate->toDateString())
                                ->whereNotIn('status', [CommerceOrder::STATUS_CANCELLED, CommerceOrder::STATUS_EXPIRED]);
                        })->lockForUpdate()->sum('quantity');

                        $incomingQty = (float) array_sum(array_map(fn ($it): float => (float) ($it['quantity'] ?? 0), $itemsData));
                        $remainingQuota = max(0, $quota - (int) $existingQty);

                        if (($existingQty + $incomingQty) > $quota) {
                            throw new DomainException("Sisa kuota untuk batch tanggal {$this->formatIndonesianDate($targetDate, false)} tersisa {$remainingQuota} {$quotaUnit}. Pesanan Anda ({$incomingQty} {$quotaUnit}) melebihi kuota yang tersedia.");
                        }
                    } else {
                        $existingCount = CommerceOrder::where('business_id', $business->id)
                            ->whereDate('scheduled_date', $targetDate->toDateString())
                            ->whereNotIn('status', [CommerceOrder::STATUS_CANCELLED, CommerceOrder::STATUS_EXPIRED])
                            ->lockForUpdate()
                            ->count();

                        if ($existingCount >= $quota) {
                            throw new DomainException("Kapasitas kuota pesanan untuk tanggal {$this->formatIndonesianDate($targetDate, false)} sudah penuh (Maks. {$quota} pesanan). Silakan pilih tanggal lain.");
                        }
                    }
                }
            }

            if ($subtotal <= 0.0) {
                throw new DomainException('Total nilai pesanan harus lebih dari Rp 0.');
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
                'global_customer_id' => auth('customer')->id() ?? null,
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
                'notes' => $options['order_notes'] ?? ($options['notes'] ?? ($customerData['notes'] ?? null)),
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

        if (! $setting->is_storefront_enabled || (! ($setting->allow_scheduled_order ?? true) && ! ($setting->allow_customer_po ?? false))) {
            throw new DomainException('Layanan Pesanan Terjadwal / Batch sedang dinonaktifkan oleh toko.');
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

        // Validate strict batch restriction when custom date is disabled
        $allowCustomDate = (bool) ($setting->allow_custom_date ?? true);
        $batchMode = $setting->batch_dates_mode ?? 'operating_days';
        if (! $allowCustomDate) {
            if ($batchMode === 'custom_dates') {
                $customDates = array_filter((array) ($setting->custom_batch_dates ?? []), fn ($b) => ($b['date'] ?? '') === $targetDate->toDateString());
                if (empty($customDates)) {
                    throw new DomainException('Toko hanya menerima pemesanan pada jadwal batch yang telah ditentukan.');
                }
            } else {
                $operatingDays = (array) ($setting->operating_days ?? []);
                $dayName = strtolower($targetDate->format('l'));
                if (! empty($operatingDays) && ! in_array($dayName, $operatingDays, true)) {
                    throw new DomainException('Toko hanya menerima pemesanan pada jadwal batch pengiriman yang telah ditentukan.');
                }
            }
        }

        // Validate quota (by item quantity or order count)
        $quota = (int) ($setting->daily_order_quota ?? 0);
        $quotaMetric = $setting->quota_metric ?? 'orders';
        $quotaUnit = $setting->preorder_quota_unit ?? 'PCS';

        if ($batchMode === 'custom_dates' && ! empty($setting->custom_batch_dates)) {
            foreach ($setting->custom_batch_dates as $cbd) {
                if (($cbd['date'] ?? '') === $targetDate->toDateString() && isset($cbd['quota']) && is_numeric($cbd['quota'])) {
                    $quota = (int) $cbd['quota'];
                    break;
                }
            }
        }

        if ($quota > 0) {
            if ($quotaMetric === 'quantity') {
                $existingQty = (float) CommerceOrderItem::whereHas('order', function ($q) use ($business, $targetDate): void {
                    $q->where('business_id', $business->id)
                        ->whereDate('scheduled_date', $targetDate->toDateString())
                        ->whereNotIn('status', [CommerceOrder::STATUS_CANCELLED, CommerceOrder::STATUS_EXPIRED]);
                })->sum('quantity');

                $incomingQty = (float) array_sum(array_map(fn ($it): float => (float) ($it['quantity'] ?? 0), $itemsData));
                $remainingQuota = max(0, $quota - (int) $existingQty);

                if (($existingQty + $incomingQty) > $quota) {
                    throw new DomainException("Sisa kuota untuk batch tanggal {$this->formatIndonesianDate($targetDate, false)} tersisa {$remainingQuota} {$quotaUnit}. Pesanan Anda ({$incomingQty} {$quotaUnit}) melebihi kuota yang tersedia.");
                }
            } else {
                $existingCount = CommerceOrder::where('business_id', $business->id)
                    ->whereDate('scheduled_date', $targetDate->toDateString())
                    ->whereNotIn('status', [CommerceOrder::STATUS_CANCELLED, CommerceOrder::STATUS_EXPIRED])
                    ->count();

                if ($existingCount >= $quota) {
                    throw new DomainException("Kapasitas kuota pesanan untuk tanggal {$this->formatIndonesianDate($targetDate, false)} sudah penuh (Maks. {$quota} pesanan). Silakan pilih tanggal lain.");
                }
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

        foreach ($itemsData as $row) {
            $qty = (float) ($row['quantity'] ?? 0);
            if ($qty <= 0.0) {
                throw new InvalidArgumentException('Jumlah permintaan untuk setiap barang harus lebih dari 0.');
            }
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
                $qty = (float) ($row['quantity'] ?? 0);
                if ($qty <= 0.0) {
                    throw new InvalidArgumentException('Jumlah permintaan untuk setiap barang harus lebih dari 0.');
                }

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

            if (empty($processedItems)) {
                throw new InvalidArgumentException('Daftar permintaan barang tidak boleh kosong.');
            }

            $order = CommerceOrder::create([
                'business_id' => $business->id,
                'location_id' => $locationId,
                'customer_id' => $customer->id,
                'global_customer_id' => auth('customer')->id() ?? null,
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
                'notes' => $options['order_notes'] ?? ($options['notes'] ?? ($customerData['notes'] ?? null)),
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

    /**
     * Format Carbon date deterministically into Indonesian locale format.
     */
    public function formatIndonesianDate(Carbon $date, bool $withDay = false): string
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $dateStr = $date->day . ' ' . ($months[$date->month] ?? $date->format('F')) . ' ' . $date->year;
        if ($withDay) {
            return ($days[$date->dayOfWeek] ?? $date->format('l')) . ', ' . $dateStr;
        }

        return $dateStr;
    }
}
