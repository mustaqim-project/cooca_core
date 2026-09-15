<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\CommerceOrder;
use App\Models\CommerceOrderBatch;
use App\Models\CommercePaymentMethod;
use App\Models\CommerceStoreSetting;
use App\Models\Location;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerPoBatchTest extends TestCase
{
    use RefreshDatabase;

    private User $merchantUser;
    private Business $business;
    private Location $location;
    private Product $product;
    private CommerceStoreSetting $setting;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);

        $this->merchantUser = User::create([
            'name' => 'Pemilik Konveksi B2B',
            'email' => 'owner@konveksib2b.com',
            'phone' => '081299998888',
            'password' => bcrypt('password123'),
        ]);
        $this->merchantUser->forceFill(['email_verified_at' => now()])->save();

        $this->business = Business::create([
            'name' => 'Sentosa Uniform & Garment',
            'slug' => 'sentosa-uniform',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->merchantUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->merchantUser->update(['active_business_id' => $this->business->id]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Workshop Sentosa',
            'is_default' => true,
        ]);

        $pcs = Unit::where('code', 'pcs')->first() ?? Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Pieces',
            'code' => 'pcs',
            'is_standard' => true,
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $pcs->id,
            'name' => 'Kemeja Seragam Kantor PT',
            'type' => 'goods',
            'selling_price' => 150000,
            'is_active' => true,
        ]);

        $this->setting = CommerceStoreSetting::create([
            'business_id' => $this->business->id,
            'is_storefront_enabled' => true,
            'allow_customer_po' => true,
        ]);

        Context::setBusiness($this->business);
    }

    public function test_customer_can_submit_customer_po_with_multiple_scheduled_delivery_batches(): void
    {
        $payload = [
            'customer_name' => 'Pak Hendra (Procurement Manager)',
            'customer_phone' => '081211112222',
            'customer_email' => 'hendra@corp.co.id',
            'company_name' => 'PT Mega Perkasa Mandiri Tbk',
            'customer_po_number' => 'PO-MPM-2026-X09',
            'shipping_address' => 'Gedung Wisma Perkasa Lantai 12, Jl. Jendral Sudirman Kav 45, Jakarta',
            'notes' => 'Pengiriman dibagi menjadi 3 termin sesuai kesepakatan penawaran.',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'quantity' => 300,
                    'unit_price' => 150000,
                    'notes' => 'Ukuran M (100), L (150), XL (50)',
                ],
            ],
            'batches' => [
                [
                    'scheduled_date' => Carbon::now()->addDays(7)->toDateString(),
                    'scheduled_time_slot' => 'Pagi (08:00 - 11:30)',
                    'quantity' => 100,
                    'shipping_address' => 'Gedung Wisma Perkasa Lantai 12',
                    'notes' => 'Drop 1: 100 pcs',
                ],
                [
                    'scheduled_date' => Carbon::now()->addDays(14)->toDateString(),
                    'scheduled_time_slot' => 'Pagi (08:00 - 11:30)',
                    'quantity' => 100,
                    'shipping_address' => 'Gedung Wisma Perkasa Lantai 12',
                    'notes' => 'Drop 2: 100 pcs',
                ],
                [
                    'scheduled_date' => Carbon::now()->addDays(21)->toDateString(),
                    'scheduled_time_slot' => 'Pagi (08:00 - 11:30)',
                    'quantity' => 100,
                    'shipping_address' => 'Gudang Logistik Cikarang',
                    'notes' => 'Drop 3: 100 pcs ke gudang',
                ],
            ],
        ];

        $response = $this->postJson(route('public.storefront.customer_po', $this->business->slug), $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.customer_po_number', 'PO-MPM-2026-X09')
            ->assertJsonPath('order.company_name', 'PT Mega Perkasa Mandiri Tbk')
            ->assertJsonPath('order.order_type', CommerceOrder::TYPE_PO_BATCH)
            ->assertJsonPath('order.batches_count', 3)
            ->assertJsonPath('order.total_amount', 45000000);

        $this->assertDatabaseHas('commerce_orders', [
            'business_id' => $this->business->id,
            'customer_po_number' => 'PO-MPM-2026-X09',
            'company_name' => 'PT Mega Perkasa Mandiri Tbk',
            'order_type' => CommerceOrder::TYPE_PO_BATCH,
            'subtotal' => 45000000,
        ]);

        $order = CommerceOrder::where('customer_po_number', 'PO-MPM-2026-X09')->first();
        $this->assertNotNull($order);
        $this->assertCount(3, $order->batches);

        $this->assertEquals('BATCH-01', $order->batches[0]->batch_code);
        $this->assertEquals(100.0, (float) $order->batches[0]->quantity);
        $this->assertEquals(CommerceOrderBatch::STATUS_SCHEDULED, $order->batches[0]->status);
    }

    public function test_customer_po_is_rejected_when_merchant_disables_po_mode(): void
    {
        $this->setting->update(['allow_customer_po' => false]);

        $payload = [
            'customer_name' => 'Budi',
            'customer_phone' => '081233334444',
            'company_name' => 'PT Coba',
            'items' => [
                [
                    'product_name' => 'Custom Uniform',
                    'quantity' => 50,
                ],
            ],
            'batches' => [
                [
                    'scheduled_date' => Carbon::now()->addDays(5)->toDateString(),
                    'quantity' => 50,
                ],
            ],
        ];

        $response = $this->postJson(route('public.storefront.customer_po', $this->business->slug), $payload);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_merchant_can_update_batch_status_and_completes_order_when_all_drops_delivered(): void
    {
        $poService = app(\App\Domain\Commerce\Storefront\CustomerPoBatchService::class);
        $order = $poService->createCustomerPoWithBatches(
            business: $this->business,
            customerData: [
                'name' => 'PT Citra Sejahtera',
                'phone' => '081255554444',
                'company_name' => 'PT Citra Sejahtera',
                'customer_po_number' => 'PO-CS-001',
            ],
            itemsData: [
                [
                    'product_name' => 'Seragam Kerja',
                    'unit_price' => 100000,
                    'quantity' => 20,
                ],
            ],
            batchesData: [
                [
                    'scheduled_date' => Carbon::now()->addDays(2)->toDateString(),
                    'quantity' => 10,
                ],
                [
                    'scheduled_date' => Carbon::now()->addDays(4)->toDateString(),
                    'quantity' => 10,
                ],
            ],
            options: [
                'status' => CommerceOrder::STATUS_PROCESSING,
            ]
        );

        $this->assertEquals(CommerceOrder::STATUS_PROCESSING, $order->status);
        $batch1 = $order->batches()->where('batch_number', 1)->first();
        $batch2 = $order->batches()->where('batch_number', 2)->first();

        // 1. Merchant marks Batch 1 as shipped
        $response1 = $this->actingAs($this->merchantUser)
            ->withSession([
                'active_business_id' => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->merchantUser->id,
            ])
            ->post(route('storefront.orders.batches.status', [$order, $batch1]), [
                'status' => 'shipped',
                'tracking_number' => 'KURIR-INTERNAL-01',
            ]);

        $response1->assertRedirect();
        $batch1->refresh();
        $this->assertEquals(CommerceOrderBatch::STATUS_SHIPPED, $batch1->status);
        $this->assertEquals('KURIR-INTERNAL-01', $batch1->tracking_number);

        // 2. Merchant marks Batch 1 as delivered
        $this->actingAs($this->merchantUser)
            ->withSession([
                'active_business_id' => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->merchantUser->id,
            ])
            ->post(route('storefront.orders.batches.status', [$order, $batch1]), [
                'status' => 'delivered',
            ]);

        $batch1->refresh();
        $this->assertEquals(CommerceOrderBatch::STATUS_DELIVERED, $batch1->status);
        $this->assertNotNull($batch1->delivered_at);

        // Order should still be processing because Batch 2 is still scheduled
        $order->refresh();
        $this->assertEquals(CommerceOrder::STATUS_PROCESSING, $order->status);
        $this->assertEquals(1, $order->delivered_batches_count);
        $this->assertEquals(2, $order->total_batches_count);

        // 3. Merchant marks Batch 2 as delivered
        $this->actingAs($this->merchantUser)
            ->withSession([
                'active_business_id' => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->merchantUser->id,
            ])
            ->post(route('storefront.orders.batches.status', [$order, $batch2]), [
                'status' => 'delivered',
            ]);

        $batch2->refresh();
        $this->assertEquals(CommerceOrderBatch::STATUS_DELIVERED, $batch2->status);

        // All batches delivered -> parent order should be completed!
        $order->refresh();
        $this->assertEquals(CommerceOrder::STATUS_COMPLETED, $order->status);
        $this->assertEquals(2, $order->delivered_batches_count);
    }

    public function test_tenant_isolation_prevents_unauthorized_merchant_from_updating_other_business_batches(): void
    {
        $otherMerchant = User::create([
            'name' => 'Merchant Toko Lain',
            'email' => 'other@tokolain.com',
            'phone' => '081277778888',
            'password' => bcrypt('password123'),
        ]);
        $otherMerchant->forceFill(['email_verified_at' => now()])->save();

        $otherBiz = Business::create([
            'name' => 'Toko Busana Lain',
            'slug' => 'toko-busana-lain',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);
        $otherBiz->users()->attach($otherMerchant->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);

        $poService = app(\App\Domain\Commerce\Storefront\CustomerPoBatchService::class);
        $order = $poService->createCustomerPoWithBatches(
            business: $this->business,
            customerData: [
                'name' => 'Client',
                'phone' => '081299990000',
            ],
            itemsData: [['product_name' => 'Item', 'quantity' => 10]],
            batchesData: [['scheduled_date' => Carbon::now()->addDay()->toDateString(), 'quantity' => 10]]
        );
        $batch = $order->batches->first();

        // Other merchant attempts to update batch
        $response = $this->actingAs($otherMerchant)
            ->withSession([
                'active_business_id' => $otherBiz->id,
                'auth_wa_otp_verified_user_id' => $otherMerchant->id,
            ])
            ->post(route('storefront.orders.batches.status', [$order, $batch]), [
                'status' => 'delivered',
            ]);

        $response->assertForbidden();
    }

    public function test_order_tracking_page_renders_po_batches_timeline(): void
    {
        $poService = app(\App\Domain\Commerce\Storefront\CustomerPoBatchService::class);
        $order = $poService->createCustomerPoWithBatches(
            business: $this->business,
            customerData: [
                'name' => 'PT Maju Bersama',
                'phone' => '081233445566',
                'company_name' => 'PT Maju Bersama',
                'customer_po_number' => 'PO-MB-999',
            ],
            itemsData: [
                [
                    'product_name' => 'Kemeja Batik Korporat',
                    'unit_price' => 200000,
                    'quantity' => 50,
                ],
            ],
            batchesData: [
                [
                    'scheduled_date' => Carbon::now()->addDays(3)->toDateString(),
                    'scheduled_time_slot' => 'Pagi (09:00)',
                    'quantity' => 25,
                ],
                [
                    'scheduled_date' => Carbon::now()->addDays(10)->toDateString(),
                    'scheduled_time_slot' => 'Siang (13:00)',
                    'quantity' => 25,
                ],
            ]
        );

        $response = $this->get(route('public.storefront.order.track', [
            'slug' => $this->business->slug,
            'token' => $order->tracking_token,
        ]));

        $response->assertOk()
            ->assertSee('Jadwal Pengiriman Bertahap (PO Batches)')
            ->assertSee('BATCH-01')
            ->assertSee('BATCH-02')
            ->assertSee('Kemeja Batik Korporat');
    }
}
