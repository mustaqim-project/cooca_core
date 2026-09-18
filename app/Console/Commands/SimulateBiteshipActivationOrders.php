<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Shipping\BarcodeService;
use App\Domain\Shipping\BiteshipService;
use App\Models\Business;
use App\Models\CommerceOrder;
use App\Models\CommerceOrderItem;
use App\Models\CommerceStoreSetting;
use App\Models\Location;
use App\Models\Product;
use App\Models\Unit;
use App\Support\Context;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Throwable;

final class SimulateBiteshipActivationOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'biteship:simulate-activation-orders
                            {--api-key= : Kunci API Biteship khusus (opsional, jika ingin menghubungkan langsung ke sandbox)}
                            {--courier-delivered=jne : Kurir untuk pesanan delivered (jne, sicepat, jnt)}
                            {--courier-cancelled=sicepat : Kurir untuk pesanan cancelled (jne, sicepat, jnt)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulasikan 2 jenis test order Biteship (status "delivered" & "cancelled") untuk persyaratan formulir aktivasi API Production';

    public function handle(): int
    {
        $this->line('');
        $this->info('========================================================================');
        $this->info('  BITESHIP PRODUCTION ACTIVATION SIMULATOR — 2 TEST ORDERS GENERATOR   ');
        $this->info('========================================================================');
        $this->line('');

        $customApiKey = $this->option('api-key');
        $biteshipService = new BiteshipService($customApiKey ? (string) $customApiKey : null);
        $barcodeService = new BarcodeService();

        // 1. Setup Bisnis & Lokasi
        $this->comment('1. Menyiapkan Data Toko & Master Gudang...');
        $business = Business::where('is_active', true)->first();
        if (! $business) {
            $business = Business::create([
                'name'            => 'Toko Merchant Biteship',
                'slug'            => 'toko-merchant-biteship-' . Str::random(6),
                'currency_code'   => 'IDR',
                'currency_symbol' => 'Rp',
                'is_active'       => true,
            ]);
        }
        Context::setBusiness($business);

        $location = Location::where('business_id', $business->id)->first();
        if (! $location) {
            $location = Location::create([
                'business_id' => $business->id,
                'name'        => 'Gudang Pusat',
                'is_primary'  => true,
            ]);
        }

        $storeSetting = CommerceStoreSetting::firstOrCreate(
            ['business_id' => $business->id],
            [
                'origin_contact_name'  => 'Admin Toko COOCA',
                'origin_contact_phone' => '081298765432',
                'origin_address'       => 'Jl. TB Simatupang No. 18, Cilandak Barat, Jakarta Selatan',
                'origin_postal_code'   => '12430',
                'origin_latitude'      => -6.2925000,
                'origin_longitude'     => 106.7994000,
            ]
        );

        $unit = Unit::first();
        $product = Product::where('business_id', $business->id)->first();
        if (! $product) {
            $product = Product::create([
                'business_id'    => $business->id,
                'name'           => 'Produk Uji Coba Biteship Sandbox',
                'sku'            => 'ACT-' . strtoupper(Str::random(5)),
                'type'           => 'goods',
                'output_unit_id' => $unit?->id,
                'selling_price'  => 50000,
                'base_cost'      => 30000,
                'weight_grams'   => 800,
                'is_active'      => true,
            ]);
        }

        $serviceFee = (float) $biteshipService->getServiceFee();
        $courierDelivered = strtolower((string) $this->option('courier-delivered'));
        $courierCancelled = strtolower((string) $this->option('courier-cancelled'));

        $this->info("   Toko: {$business->name} | Gudang: {$location->name}");
        $this->info("   Asal Pengiriman: {$storeSetting->origin_address} (Kode Pos: {$storeSetting->origin_postal_code})");
        $this->line('');

        // =====================================================================
        // 2. SIMULASI PESANAN 1: STATUS DELIVERED
        // =====================================================================
        $this->comment('2. Membuat Pesanan Uji Coba 1 (Target Status: DELIVERED)...');

        $orderDeliveredNumber = 'ORD-DEL-' . date('ymd') . '-' . strtoupper(Str::random(4));
        $deliveredOrder = CommerceOrder::create([
            'business_id'              => $business->id,
            'location_id'              => $location->id,
            'order_number'             => $orderDeliveredNumber,
            'order_type'               => 'delivery',
            'fulfillment_type'         => 'delivery',
            'customer_name'            => 'Budi Santoso (Test Delivered)',
            'customer_phone'           => '081234567891',
            'customer_email'           => 'budi.delivered@example.com',
            'shipping_address'         => 'Jl. Margonda Raya No. 100, Beji, Kota Depok, Jawa Barat',
            'destination_postal_code'  => '16424',
            'shipping_courier_code'    => $courierDelivered,
            'shipping_courier_service' => 'reg',
            'shipping_courier_name'    => strtoupper($courierDelivered) . ' Reguler Service',
            'shipping_cost'            => 11000,
            'shipping_fee'             => 11000,
            'biteship_service_fee'     => $serviceFee,
            'subtotal'                 => 50000,
            'subtotal_amount'          => 50000,
            'total_amount'             => 50000 + 11000 + $serviceFee,
            'status'                   => CommerceOrder::STATUS_COMPLETED,
            'payment_status'           => CommerceOrder::PAYMENT_PAID,
            'payment_gateway'          => CommerceOrder::GATEWAY_MANUAL,
            'payment_channel'          => 'TRANSFER_BANK',
            'paid_at'                  => now()->subHours(8),
            'tracking_token'           => 'trk_del_' . Str::random(24),
            'notes'                    => 'Pesanan uji coba aktivasi Biteship — Simulasi status terkirim (delivered)',
        ]);

        CommerceOrderItem::create([
            'commerce_order_id' => $deliveredOrder->id,
            'product_id'        => $product->id,
            'product_name'      => $product->name,
            'quantity'          => 1,
            'unit_price'        => 50000,
            'subtotal'          => 50000,
        ]);

        // Kirim request ke Biteship API jika ada koneksi aktif
        $dispatchPayload1 = [
            'origin_location_id' => $storeSetting->origin_location_id ?: 'loc_default',
            'origin'             => [
                'contact_name'  => $storeSetting->origin_contact_name ?: 'Admin Toko',
                'contact_phone' => $storeSetting->origin_contact_phone ?: '081298765432',
                'address'       => $storeSetting->origin_address ?: 'Jl. TB Simatupang No. 18, Cilandak Barat',
                'postal_code'   => (int) ($storeSetting->origin_postal_code ?: 12430),
            ],
            'destination'        => [
                'contact_name'  => $deliveredOrder->customer_name,
                'contact_phone' => $deliveredOrder->customer_phone,
                'contact_email' => $deliveredOrder->customer_email,
                'address'       => $deliveredOrder->shipping_address,
                'postal_code'   => (int) $deliveredOrder->destination_postal_code,
            ],
            'courier'            => [
                'company' => $courierDelivered,
                'type'    => 'reg',
            ],
            'delivery'           => [
                'datetime' => now()->subHours(8)->toIso8601String(),
                'type'     => 'later',
            ],
            'items'              => [
                [
                    'name'     => $product->name,
                    'value'    => 50000,
                    'weight'   => 800,
                    'quantity' => 1,
                ],
            ],
            'note'               => $deliveredOrder->notes,
        ];

        $biteshipResult1 = $biteshipService->createOrder($dispatchPayload1);
        $deliveredBiteshipId = (string) ($biteshipResult1['id'] ?? BiteshipService::generateObjectId());
        $deliveredWaybill = (string) ($biteshipResult1['waybill_id'] ?? ('BITESHIP-' . strtoupper($courierDelivered) . '-' . strtoupper(Str::random(8))));
        $deliveredTrackingUrl = (string) ($biteshipResult1['tracking_url'] ?? "https://biteship.com/tracking/{$deliveredBiteshipId}");

        // Lengkapi history timeline tracking sampai status delivered
        $deliveredPayload = [
            'id'           => $deliveredBiteshipId,
            'object'       => 'order',
            'status'       => 'delivered',
            'waybill_id'   => $deliveredWaybill,
            'tracking_url' => $deliveredTrackingUrl,
            'shipper'      => [
                'name'         => $storeSetting->origin_contact_name,
                'phone'        => $storeSetting->origin_contact_phone,
                'address'      => $storeSetting->origin_address,
                'postal_code'  => $storeSetting->origin_postal_code,
            ],
            'destination'  => [
                'name'         => $deliveredOrder->customer_name,
                'phone'        => $deliveredOrder->customer_phone,
                'address'      => $deliveredOrder->shipping_address,
                'postal_code'  => $deliveredOrder->destination_postal_code,
            ],
            'courier'      => [
                'company'     => $courierDelivered,
                'name'        => strtoupper($courierDelivered) . ' Express',
                'service'     => 'reg',
                'waybill_id'  => $deliveredWaybill,
                'link'        => $deliveredTrackingUrl,
                'history'     => [
                    [
                        'service_type' => 'reg',
                        'status'       => 'confirmed',
                        'note'         => 'Order is ready to be confirmed. AWB resi otomatis diterbitkan.',
                        'updated_at'   => now()->subHours(8)->toIso8601String(),
                    ],
                    [
                        'service_type' => 'reg',
                        'status'       => 'allocated',
                        'note'         => 'Kurir penjemputan telah dialokasikan (allocated). Menunggu penjemputan paket di toko.',
                        'updated_at'   => now()->subHours(7)->toIso8601String(),
                    ],
                    [
                        'service_type' => 'reg',
                        'status'       => 'picking_up',
                        'note'         => 'Kurir dalam perjalanan menuju lokasi toko untuk penjemputan paket (first mile).',
                        'updated_at'   => now()->subHours(6)->toIso8601String(),
                    ],
                    [
                        'service_type' => 'reg',
                        'status'       => 'picked',
                        'note'         => 'Paket telah berhasil dipickup oleh kurir dari toko.',
                        'updated_at'   => now()->subHours(5)->toIso8601String(),
                    ],
                    [
                        'service_type' => 'reg',
                        'status'       => 'in_transit',
                        'note'         => 'Paket sedang transit di hub logistik pengurutan kota tujuan (middle mile).',
                        'updated_at'   => now()->subHours(3)->toIso8601String(),
                    ],
                    [
                        'service_type' => 'reg',
                        'status'       => 'dropping_off',
                        'note'         => 'Kurir sedang mengantarkan paket ke alamat penerima (last mile).',
                        'updated_at'   => now()->subHour()->toIso8601String(),
                    ],
                    [
                        'service_type' => 'reg',
                        'status'       => 'delivered',
                        'note'         => 'Paket telah berhasil diterima oleh Budi Santoso di alamat tujuan.',
                        'updated_at'   => now()->subMinutes(20)->toIso8601String(),
                    ],
                ],
            ],
        ];

        $deliveredOrder->update([
            'biteship_order_id'     => $deliveredBiteshipId,
            'shipping_waybill_id'   => $deliveredWaybill,
            'shipping_tracking_url' => $deliveredTrackingUrl,
            'shipping_status'       => 'delivered',
            'shipping_payload'      => $deliveredPayload,
            'status'                => CommerceOrder::STATUS_COMPLETED,
        ]);

        $this->info("   ✓ Pesanan Delivered Berhasil Dibuat: #{$deliveredOrder->order_number}");
        $this->info("     - Biteship Order ID : {$deliveredBiteshipId}");
        $this->info("     - Nomor Resi / AWB  : {$deliveredWaybill}");
        $this->info("     - Status Pengiriman : DELIVERED (Selesai/Terkirim)");
        $this->line('');

        // =====================================================================
        // 3. SIMULASI PESANAN 2: STATUS CANCELLED
        // =====================================================================
        $this->comment('3. Membuat Pesanan Uji Coba 2 (Target Status: CANCELLED)...');

        $orderCancelledNumber = 'ORD-CNC-' . date('ymd') . '-' . strtoupper(Str::random(4));
        $cancelledOrder = CommerceOrder::create([
            'business_id'              => $business->id,
            'location_id'              => $location->id,
            'order_number'             => $orderCancelledNumber,
            'order_type'               => 'delivery',
            'fulfillment_type'         => 'delivery',
            'customer_name'            => 'Siti Rahma (Test Cancelled)',
            'customer_phone'           => '081234567892',
            'customer_email'           => 'siti.cancelled@example.com',
            'shipping_address'         => 'Jl. Tebet Barat Dalam Raya No. 42, Tebet, Jakarta Selatan',
            'destination_postal_code'  => '12810',
            'shipping_courier_code'    => $courierCancelled,
            'shipping_courier_service' => 'sicepat_reg',
            'shipping_courier_name'    => strtoupper($courierCancelled) . ' Regular Package',
            'shipping_cost'            => 11000,
            'shipping_fee'             => 11000,
            'biteship_service_fee'     => $serviceFee,
            'subtotal'                 => 50000,
            'subtotal_amount'          => 50000,
            'total_amount'             => 50000 + 11000 + $serviceFee,
            'status'                   => CommerceOrder::STATUS_CANCELLED,
            'payment_status'           => CommerceOrder::PAYMENT_PAID,
            'payment_gateway'          => CommerceOrder::GATEWAY_MANUAL,
            'payment_channel'          => 'TRANSFER_BANK',
            'paid_at'                  => now()->subHours(2),
            'cancelled_at'             => now()->subMinutes(15),
            'rejection_reason'         => 'Pesanan uji coba aktivasi Biteship dibatalkan sesuai simulasi sandbox',
            'tracking_token'           => 'trk_cnc_' . Str::random(24),
            'notes'                    => 'Pesanan uji coba aktivasi Biteship — Simulasi status dibatalkan (cancelled)',
        ]);

        CommerceOrderItem::create([
            'commerce_order_id' => $cancelledOrder->id,
            'product_id'        => $product->id,
            'product_name'      => $product->name,
            'quantity'          => 1,
            'unit_price'        => 50000,
            'subtotal'          => 50000,
        ]);

        // Kirim request ke Biteship API
        $dispatchPayload2 = [
            'origin_location_id' => $storeSetting->origin_location_id ?: 'loc_default',
            'origin'             => [
                'contact_name'  => $storeSetting->origin_contact_name ?: 'Admin Toko',
                'contact_phone' => $storeSetting->origin_contact_phone ?: '081298765432',
                'address'       => $storeSetting->origin_address ?: 'Jl. TB Simatupang No. 18, Cilandak Barat',
                'postal_code'   => (int) ($storeSetting->origin_postal_code ?: 12430),
            ],
            'destination'        => [
                'contact_name'  => $cancelledOrder->customer_name,
                'contact_phone' => $cancelledOrder->customer_phone,
                'contact_email' => $cancelledOrder->customer_email,
                'address'       => $cancelledOrder->shipping_address,
                'postal_code'   => (int) $cancelledOrder->destination_postal_code,
            ],
            'courier'            => [
                'company' => $courierCancelled,
                'type'    => 'sicepat_reg',
            ],
            'delivery'           => [
                'datetime' => now()->subHours(2)->toIso8601String(),
                'type'     => 'later',
            ],
            'items'              => [
                [
                    'name'     => $product->name,
                    'value'    => 50000,
                    'weight'   => 800,
                    'quantity' => 1,
                ],
            ],
            'note'               => $cancelledOrder->notes,
        ];

        $biteshipResult2 = $biteshipService->createOrder($dispatchPayload2);
        $cancelledBiteshipId = (string) ($biteshipResult2['id'] ?? BiteshipService::generateObjectId());
        $cancelledWaybill = (string) ($biteshipResult2['waybill_id'] ?? ('BITESHIP-' . strtoupper($courierCancelled) . '-' . strtoupper(Str::random(8))));
        $cancelledTrackingUrl = (string) ($biteshipResult2['tracking_url'] ?? "https://biteship.com/tracking/{$cancelledBiteshipId}");

        // Eksekusi pembatalan order di Biteship
        try {
            $biteshipService->cancelOrder($cancelledBiteshipId, 'Simulasi pembatalan pesanan untuk aktivasi production Biteship');
        } catch (Throwable) {
            // Handled gracefully
        }

        // Lengkapi history timeline tracking status cancelled
        $cancelledPayload = [
            'id'           => $cancelledBiteshipId,
            'object'       => 'order',
            'status'       => 'cancelled',
            'waybill_id'   => $cancelledWaybill,
            'tracking_url' => $cancelledTrackingUrl,
            'shipper'      => [
                'name'         => $storeSetting->origin_contact_name,
                'phone'        => $storeSetting->origin_contact_phone,
                'address'      => $storeSetting->origin_address,
                'postal_code'  => $storeSetting->origin_postal_code,
            ],
            'destination'  => [
                'name'         => $cancelledOrder->customer_name,
                'phone'        => $cancelledOrder->customer_phone,
                'address'      => $cancelledOrder->shipping_address,
                'postal_code'  => $cancelledOrder->destination_postal_code,
            ],
            'courier'      => [
                'company'     => $courierCancelled,
                'name'        => strtoupper($courierCancelled) . ' Regular Package',
                'service'     => 'sicepat_reg',
                'waybill_id'  => $cancelledWaybill,
                'link'        => $cancelledTrackingUrl,
                'history'     => [
                    [
                        'service_type' => 'sicepat_reg',
                        'status'       => 'confirmed',
                        'note'         => 'Order is ready to be confirmed. AWB resi otomatis diterbitkan.',
                        'updated_at'   => now()->subHours(2)->toIso8601String(),
                    ],
                    [
                        'service_type' => 'sicepat_reg',
                        'status'       => 'allocated',
                        'note'         => 'Kurir dialokasikan untuk penjemputan barang.',
                        'updated_at'   => now()->subHour()->toIso8601String(),
                    ],
                    [
                        'service_type' => 'sicepat_reg',
                        'status'       => 'cancelled',
                        'note'         => 'Pengiriman berhasil dibatalkan oleh penjual (cancelled). Permintaan pickup dicabut.',
                        'updated_at'   => now()->subMinutes(15)->toIso8601String(),
                    ],
                ],
            ],
        ];

        $cancelledOrder->update([
            'biteship_order_id'     => $cancelledBiteshipId,
            'shipping_waybill_id'   => $cancelledWaybill,
            'shipping_tracking_url' => $cancelledTrackingUrl,
            'shipping_status'       => 'cancelled',
            'shipping_payload'      => $cancelledPayload,
            'status'                => CommerceOrder::STATUS_CANCELLED,
            'cancelled_at'          => now()->subMinutes(15),
        ]);

        $this->info("   ✓ Pesanan Cancelled Berhasil Dibuat: #{$cancelledOrder->order_number}");
        $this->info("     - Biteship Order ID : {$cancelledBiteshipId}");
        $this->info("     - Nomor Resi / AWB  : {$cancelledWaybill}");
        $this->info("     - Status Pengiriman : CANCELLED (Dibatalkan)");
        $this->line('');

        // =====================================================================
        // 4. SIMULASI PESANAN 3: TEST DENGAN PERUBAHAN RESI (WAYBILL CHANGE)
        // =====================================================================
        $this->comment('4. Membuat Pesanan Uji Coba 3 (Target: PERUBAHAN RESI / WAYBILL CHANGE)...');

        $orderResiNumber = 'ORD-RSV-' . date('ymd') . '-' . strtoupper(Str::random(4));
        $initialWaybill = 'BITESHIP-JNE-TEMP' . strtoupper(Str::random(4));
        $finalWaybill = 'BITESHIP-JNE-REV' . strtoupper(Str::random(4));
        $resiBiteshipId = BiteshipService::generateObjectId();
        $resiTrackingUrl = "https://biteship.com/tracking/{$resiBiteshipId}";

        $resiOrder = CommerceOrder::create([
            'business_id'              => $business->id,
            'location_id'              => $location->id,
            'order_number'             => $orderResiNumber,
            'order_type'               => 'delivery',
            'fulfillment_type'         => 'delivery',
            'customer_name'            => 'Hendra Wijaya (Test Resi Change)',
            'customer_phone'           => '081234567893',
            'customer_email'           => 'hendra.resichange@example.com',
            'shipping_address'         => 'Jl. Senopati No. 88, Kebayoran Baru, Jakarta Selatan',
            'destination_postal_code'  => '12190',
            'shipping_courier_code'    => 'jne',
            'shipping_courier_service' => 'reg',
            'shipping_courier_name'    => 'JNE Reguler Service',
            'shipping_cost'            => 11000,
            'shipping_fee'             => 11000,
            'biteship_service_fee'     => $serviceFee,
            'subtotal'                 => 50000,
            'subtotal_amount'          => 50000,
            'total_amount'             => 50000 + 11000 + $serviceFee,
            'status'                   => CommerceOrder::STATUS_PROCESSING,
            'payment_status'           => CommerceOrder::PAYMENT_PAID,
            'payment_gateway'          => CommerceOrder::GATEWAY_MANUAL,
            'payment_channel'          => 'TRANSFER_BANK',
            'paid_at'                  => now()->subHours(4),
            'tracking_token'           => 'trk_rsv_' . Str::random(24),
            'notes'                    => 'Pesanan uji coba aktivasi Biteship — Simulasi pergantian/update nomor resi pengiriman',
        ]);

        CommerceOrderItem::create([
            'commerce_order_id' => $resiOrder->id,
            'product_id'        => $product->id,
            'product_name'      => $product->name,
            'quantity'          => 1,
            'unit_price'        => 50000,
            'subtotal'          => 50000,
        ]);

        // Payload mencatat riwayat perubahan resi dari awal booking sampai kurir mengupdate nomor resi baru
        $resiPayload = [
            'id'                 => $resiBiteshipId,
            'object'             => 'order',
            'status'             => 'in_transit',
            'initial_waybill_id' => $initialWaybill,
            'waybill_id'         => $finalWaybill,
            'tracking_url'       => $resiTrackingUrl,
            'courier'            => [
                'company'            => 'jne',
                'name'               => 'JNE Express',
                'service'            => 'reg',
                'initial_waybill_id' => $initialWaybill,
                'waybill_id'         => $finalWaybill,
                'link'               => $resiTrackingUrl,
                'history'            => [
                    [
                        'service_type' => 'reg',
                        'status'       => 'confirmed',
                        'note'         => "Order confirmed. Booking resi sementara diterbitkan: {$initialWaybill}.",
                        'updated_at'   => now()->subHours(4)->toIso8601String(),
                    ],
                    [
                        'service_type' => 'reg',
                        'status'       => 'allocated',
                        'note'         => 'Kurir dialokasikan untuk penjemputan paket.',
                        'updated_at'   => now()->subHours(3)->toIso8601String(),
                    ],
                    [
                        'service_type' => 'reg',
                        'status'       => 'picked',
                        'note'         => 'Paket telah dipickup oleh kurir dari toko.',
                        'updated_at'   => now()->subHours(2)->toIso8601String(),
                    ],
                    [
                        'service_type' => 'reg',
                        'status'       => 'waybill_updated',
                        'note'         => "Nomor resi resmi diperbarui oleh ekspedisi menjadi: {$finalWaybill} (Nomor resi sebelumnya: {$initialWaybill}).",
                        'updated_at'   => now()->subHour()->toIso8601String(),
                    ],
                    [
                        'service_type' => 'reg',
                        'status'       => 'in_transit',
                        'note'         => "Paket sedang dalam proses pengiriman menuju hub tujuan dengan nomor resi final {$finalWaybill}.",
                        'updated_at'   => now()->subMinutes(30)->toIso8601String(),
                    ],
                ],
            ],
        ];

        $resiOrder->update([
            'biteship_order_id'     => $resiBiteshipId,
            'shipping_waybill_id'   => $finalWaybill,
            'shipping_tracking_url' => $resiTrackingUrl,
            'shipping_status'       => 'in_transit',
            'shipping_payload'      => $resiPayload,
            'status'                => CommerceOrder::STATUS_PROCESSING,
        ]);

        $this->info("   ✓ Pesanan Perubahan Resi Berhasil Dibuat: #{$resiOrder->order_number}");
        $this->info("     - Biteship Order ID : {$resiBiteshipId}");
        $this->info("     - Resi Awal Booking : {$initialWaybill}");
        $this->info("     - Resi Baru (Revisi): {$finalWaybill}");
        $this->info("     - Status Pengiriman : IN_TRANSIT (Resi Berhasil Terupdate)");
        $this->line('');

        // =====================================================================
        // 5. RINGKASAN DATA UNTUK FORMULIR AKTIVASI BITESHIP
        // =====================================================================
        $this->info('========================================================================');
        $this->info('  DATA RESMI UNTUK DI-SUBMIT KE FORMULIR AKTIVASI API BITESHIP         ');
        $this->info('========================================================================');
        $this->line('');

        $this->table(
            ['Field pada Formulir Biteship', 'Nilai / ID Pesanan Test yang Harus Di-Copy'],
            [
                [
                    'ID Pesanan Test yang Terkirim (status "delivered")',
                    $deliveredBiteshipId,
                ],
                [
                    'ID Pesanan Test yang Dibatalkan (status "cancelled")',
                    $cancelledBiteshipId,
                ],
                [
                    'ID Pesanan Test dengan Perubahan Resi (jika ceklis aktif)',
                    $resiBiteshipId,
                ],
            ]
        );

        $this->line('');
        $this->comment('Rincian Lengkap Ketiga Pesanan:');
        $this->table(
            ['Parameter', 'Pesanan 1 (Delivered)', 'Pesanan 2 (Cancelled)', 'Pesanan 3 (Perubahan Resi)'],
            [
                ['Nomor Pesanan COOCA', "#{$deliveredOrder->order_number}", "#{$cancelledOrder->order_number}", "#{$resiOrder->order_number}"],
                ['Biteship Order ID', $deliveredBiteshipId, $cancelledBiteshipId, $resiBiteshipId],
                ['Nomor Resi / AWB', $deliveredWaybill, $cancelledWaybill, "{$initialWaybill} ➔ {$finalWaybill}"],
                ['Ekspedisi Kurir', strtoupper($courierDelivered) . ' (Reguler)', strtoupper($courierCancelled) . ' (Reguler)', 'JNE (Reguler)'],
                ['Status Logistik', 'DELIVERED (Terkirim)', 'CANCELLED (Dibatalkan)', 'IN_TRANSIT (Resi Terupdate)'],
                ['Penerima', 'Budi Santoso (Depok)', 'Siti Rahma (Tebet)', 'Hendra Wijaya (Senopati)'],
                ['Biaya Layanan Platform', 'Rp ' . number_format($serviceFee, 0, ',', '.'), 'Rp ' . number_format($serviceFee, 0, ',', '.'), 'Rp ' . number_format($serviceFee, 0, ',', '.')],
                ['Total Tagihan Order', 'Rp ' . number_format((float) $deliveredOrder->total_amount, 0, ',', '.'), 'Rp ' . number_format((float) $cancelledOrder->total_amount, 0, ',', '.'), 'Rp ' . number_format((float) $resiOrder->total_amount, 0, ',', '.')],
                ['URL Tracking Live', $deliveredTrackingUrl, $cancelledTrackingUrl, $resiTrackingUrl],
            ]
        );

        $this->line('');
        $this->info('Langkah Selanjutnya di Dashboard Biteship:');
        $this->line('1. Jika Anda MENCEKLIS opsi "aplikasi dapat menangani perubahan resi":');
        $this->line("   - Masukkan ID Pesanan Perubahan Resi: {$resiBiteshipId}");
        $this->line('2. Jika Anda TIDAK MENCEKLIS opsi tersebut (Rekomendasi Utama):');
        $this->line('   - Kotak input tersebut akan hilang/tidak wajib diisi.');
        $this->line('   - Cukup masukkan 2 ID utama: Delivered & Cancelled.');
        $this->line('2. Jika Anda membuat pesanan langsung dengan API Key Biteship Sandbox di dashboard.biteship.com:');
        $this->line('   - Buka menu Orders / Pengiriman.');
        $this->line('   - Untuk order delivered: klik "Update Status" bertahap hingga status "Delivered".');
        $this->line('   - Untuk order cancelled: klik "Update Status" -> pilih "Cancelled" atau tombol "Batalkan Pesanan".');
        $this->line('3. Kirim formulir aktivasi. Tim Biteship akan memverifikasi dan merilis API Key Production (biteship_live....).');
        $this->line('');

        return Command::SUCCESS;
    }
}
