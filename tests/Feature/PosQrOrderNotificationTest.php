<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Pos\PosOrderService;
use App\Domain\Pos\PosShiftService;
use App\Domain\Pos\PosTableService;
use App\Models\Business;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosTable;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PosQrOrderNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $cashier;
    private Business $business;
    private Location $location;
    private Product $foodProduct;
    private PosTable $table;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->cashier = User::create([
            'name' => 'Kasir Terminal',
            'email' => 'kasir.terminal@example.com',
            'password' => 'password123',
        ]);

        $this->business = Business::create([
            'name' => 'Resto Notifikasi Test',
            'pos_enable_tax' => false,
            'pos_enable_service_charge' => false,
        ]);

        $this->business->users()->attach($this->cashier->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Main Dining',
            'code' => 'MD-01',
            'is_active' => true,
        ]);

        $unit = Unit::create([
            'business_id' => $this->business->id,
            'code' => 'PORSI',
            'name' => 'Porsi',
            'symbol' => 'porsi',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);

        $category = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Makanan Utama',
        ]);

        $this->foodProduct = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'output_unit_id' => $unit->id,
            'name' => 'Bebek Betutu Khas Bali',
            'code' => 'BB-01',
            'type' => 'finished_good',
            'selling_price' => 65000,
            'is_active' => true,
        ]);

        $this->table = PosTable::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'table_number' => 'Meja 09',
            'name' => 'Meja 09 Garden',
            'capacity' => 4,
            'status' => 'available',
            'is_active' => true,
        ]);

        \App\Models\InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->foodProduct->id,
            'quantity' => 50,
        ]);

        // Open shift
        app(PosShiftService::class)->openShift(
            $this->business,
            $this->cashier,
            100000.0,
            null,
            $this->location->id
        );
    }

    public function test_incoming_orders_endpoint_returns_notification_payload_for_qr_order(): void
    {
        Context::setBusiness($this->business);
        $this->actingAs($this->cashier);

        // Customer creates QR order via table
        $order = app(PosOrderService::class)->createQrOrder(
            table: $this->table,
            customerName: 'Ibu Rahma',
            customerPhone: '08123456789',
            itemsData: [
                [
                    'product_id' => $this->foodProduct->id,
                    'quantity' => 2,
                    'notes' => 'Tolong cabai dipisah',
                ],
            ]
        );

        $response = $this->withSession([
            'current_business_id' => $this->business->id,
        ])->getJson(route('pos.incoming-orders'));

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'orders' => [
                '*' => [
                    'id',
                    'order_number',
                    'table_number',
                    'customer_name',
                    'customer_phone',
                    'total_amount',
                    'created_at_time',
                    'notes',
                    'items' => [
                        '*' => [
                            'id',
                            'product_id',
                            'product_name',
                            'quantity',
                            'unit_price',
                            'total_price',
                        ],
                    ],
                ],
            ],
        ]);

        $orders = $response->json('orders');
        $this->assertCount(1, $orders);

        $firstOrder = $orders[0];
        $this->assertEquals($order->id, $firstOrder['id']);
        $this->assertEquals('Meja 09', $firstOrder['table_number']);
        $this->assertEquals('Ibu Rahma', $firstOrder['customer_name']);
        $this->assertEquals(130000, $firstOrder['total_amount']);
        $this->assertCount(1, $firstOrder['items']);
        $this->assertEquals('Bebek Betutu Khas Bali', $firstOrder['items'][0]['product_name']);
        $this->assertEquals(2, $firstOrder['items'][0]['quantity']);
    }

    public function test_cashier_can_accept_incoming_order_and_load_session(): void
    {
        Context::setBusiness($this->business);
        $this->actingAs($this->cashier);

        $order = app(PosOrderService::class)->createQrOrder(
            table: $this->table,
            customerName: 'Bpk Handoko',
            customerPhone: '0811223344',
            itemsData: [
                [
                    'product_id' => $this->foodProduct->id,
                    'quantity' => 1,
                ],
            ]
        );

        $response = $this->withSession([
            'current_business_id' => $this->business->id,
        ])->postJson(route('pos.incoming-orders.accept', $order->id));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('pos_orders', [
            'id' => $order->id,
            'status' => 'confirmed',
        ]);
    }
}
