<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Pos\PosOrderService;
use App\Domain\Pos\PosShiftService;
use App\Domain\Pos\PosTableService;
use App\Models\Business;
use App\Models\Customer;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
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

final class PosTableOrderToCartTest extends TestCase
{
    use RefreshDatabase;

    private User $cashier;
    private Business $business;
    private Location $location;
    private Product $foodProduct;
    private Product $drinkProduct;
    private PosTable $table;
    private ModifierOption $spicyOption;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->cashier = User::create([
            'name' => 'Kasir Resto',
            'email' => 'kasir.resto@example.com',
            'password' => 'password123',
        ]);

        $this->business = Business::create([
            'name' => 'Restoran Nusantara Rasa Test',
            'pos_enable_tax' => false,
            'pos_enable_service_charge' => false,
        ]);

        $this->business->users()->attach($this->cashier->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $this->cashier->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Resto Utama',
            'is_primary' => true,
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
            'slug' => 'makanan-utama',
        ]);

        $this->foodProduct = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'output_unit_id' => $unit->id,
            'code' => 'NAS-01',
            'name' => 'Nasi Goreng Spesial',
            'slug' => 'nasi-goreng-spesial',
            'base_cost' => 15000,
            'selling_price' => 30000,
            'is_active' => true,
        ]);

        $this->drinkProduct = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'output_unit_id' => $unit->id,
            'code' => 'ES-01',
            'name' => 'Es Teh Manis',
            'slug' => 'es-teh-manis',
            'base_cost' => 2000,
            'selling_price' => 6000,
            'is_active' => true,
        ]);

        // Stock initial
        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->foodProduct->id,
            'quantity' => 100,
            'last_cost' => 15000,
        ]);
        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->drinkProduct->id,
            'quantity' => 100,
            'last_cost' => 2000,
        ]);

        // Modifier group
        $modGroup = ModifierGroup::create([
            'business_id' => $this->business->id,
            'name' => 'Level Pedas',
            'selection_type' => 'single',
            'is_required' => false,
            'is_active' => true,
        ]);
        $this->spicyOption = ModifierOption::create([
            'modifier_group_id' => $modGroup->id,
            'name' => 'Pedas Banget',
            'price_delta' => 2000,
            'is_active' => true,
        ]);
        $this->foodProduct->modifierGroups()->attach($modGroup->id, [
            'id' => (string) Str::uuid(),
            'sort_order' => 1,
        ]);

        // Table
        $tableService = new PosTableService();
        $this->table = $tableService->createTable($this->business, [
            'table_number' => 'Meja 05',
            'capacity' => 4,
            'location_id' => $this->location->id,
        ]);
    }

    public function test_tables_json_returns_rich_item_details_for_pos_cart(): void
    {
        $this->actingAs($this->cashier);

        $orderService = new PosOrderService();
        $qrOrder = $orderService->createQrOrder(
            table: $this->table,
            customerName: 'Bpk. Hendra',
            customerPhone: '081298765432',
            itemsData: [
                [
                    'product_id' => $this->foodProduct->id,
                    'quantity' => 2,
                    'selected_modifiers' => [$this->spicyOption->id],
                    'notes' => 'Sedikit minyak',
                ],
            ]
        );

        $response = $this->getJson(route('pos.tables.index'));
        $response->assertOk();
        $response->assertJsonPath('success', true);

        $tableData = collect($response->json('tables'))->firstWhere('table_number', 'Meja 05');
        $this->assertNotNull($tableData);
        $this->assertNotNull($tableData['active_session']);
        $this->assertSame('Bpk. Hendra', $tableData['active_session']['customer_name']);

        $items = $tableData['active_session']['orders'][0]['items'];
        $this->assertCount(1, $items);
        $this->assertSame($this->foodProduct->id, $items[0]['product_id']);
        $this->assertSame('Nasi Goreng Spesial', $items[0]['product_name']);
        $this->assertEquals(2, $items[0]['quantity']);
        $this->assertEquals(32000, $items[0]['unit_price']); // 30000 + 2000
        $this->assertEquals(64000, $items[0]['total_price']);
        $this->assertStringContainsString('Pedas Banget', $items[0]['modifiers_summary']);
        $this->assertSame('Sedikit minyak', $items[0]['notes']);
    }

    public function test_cashier_can_checkout_table_order_with_existing_order_id_and_close_session(): void
    {
        $this->actingAs($this->cashier);

        // Open cashier shift
        $this->postJson(route('pos.shifts.open'), [
            'location_id' => $this->location->id,
            'opening_cash' => 100000,
        ]);

        $orderService = new PosOrderService();
        $qrOrder = $orderService->createQrOrder(
            table: $this->table,
            customerName: 'Ibu Ratna',
            customerPhone: '081122334455',
            itemsData: [
                [
                    'product_id' => $this->foodProduct->id,
                    'quantity' => 1,
                    'selected_modifiers' => [$this->spicyOption->id],
                    'notes' => 'Pedas mantap',
                ],
            ]
        );

        $this->table->refresh();
        $this->assertSame(PosTable::STATUS_OCCUPIED, $this->table->status);

        // Cashier loads items to cart, adds 1 extra Es Teh Manis at the counter, and completes payment
        $payload = [
            'location_id' => $this->location->id,
            'pos_table_id' => $this->table->id,
            'pos_table_session_id' => $this->table->activeSession->id,
            'existing_order_id' => $qrOrder->id,
            'customer_name_guest' => 'Ibu Ratna',
            'order_type' => 'dine_in',
            'items' => [
                [
                    'product_id' => $this->foodProduct->id,
                    'product_name' => $this->foodProduct->name,
                    'quantity' => 1,
                    'unit_price' => 32000,
                    'selected_modifiers' => [$this->spicyOption->id],
                    'modifiers_summary' => 'Level Pedas: Pedas Banget',
                    'notes' => 'Pedas mantap',
                ],
                [
                    'product_id' => $this->drinkProduct->id,
                    'product_name' => $this->drinkProduct->name,
                    'quantity' => 1,
                    'unit_price' => 6000,
                    'selected_modifiers' => [],
                    'notes' => 'Kurang manis',
                ],
            ],
            'payments' => [
                ['payment_method' => 'cash', 'amount' => 40000],
            ],
        ];

        $checkoutResp = $this->postJson(route('pos.checkout'), $payload);
        $checkoutResp->assertOk();
        $checkoutResp->assertJsonPath('success', true);

        // Verify order is finalized under original order number
        $qrOrder->refresh();
        $this->assertSame(PosOrder::STATUS_COMPLETED, $qrOrder->status);
        $this->assertEquals(38000, $qrOrder->total_amount); // 32000 + 6000
        $this->assertEquals(40000, $qrOrder->paid_amount);
        $this->assertEquals(2000, $qrOrder->change_amount);
        $this->assertCount(2, $qrOrder->items);

        // Verify table session is closed and table is available again
        $this->table->refresh();
        $this->assertSame(PosTable::STATUS_AVAILABLE, $this->table->status);
        $this->assertNull($this->table->activeSession);

        // Verify inventory stock was properly deducted
        $foodStock = InventoryStock::where('location_id', $this->location->id)->where('product_id', $this->foodProduct->id)->first();
        $this->assertEquals(99, (float) $foodStock->quantity); // 100 - 1
        $drinkStock = InventoryStock::where('location_id', $this->location->id)->where('product_id', $this->drinkProduct->id)->first();
        $this->assertEquals(99, (float) $drinkStock->quantity); // 100 - 1
    }
}
