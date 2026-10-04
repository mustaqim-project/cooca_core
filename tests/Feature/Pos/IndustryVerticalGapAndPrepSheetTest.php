<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Domain\Pos\PosOrderService;
use App\Models\BomHeader;
use App\Models\BomItem;
use App\Models\Business;
use App\Models\CostModel;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Material;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosShift;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class IndustryVerticalGapAndPrepSheetTest extends TestCase
{
    use RefreshDatabase;

    private User $cashier;
    private User $mechanic;
    private Business $business;
    private Location $location;
    private Unit $unit;
    private PosShift $shift;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('id');
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);

        $this->unit = Unit::first();

        $this->cashier = User::create([
            'name' => 'Budi Kasir',
            'email' => 'kasir@bengkelcooca.com',
            'email_verified_at' => now(),
            'phone' => '081234567890',
            'password' => bcrypt('password123'),
        ]);

        $this->mechanic = User::create([
            'name' => 'Pak Joko Mekanik',
            'email' => 'joko@bengkelcooca.com',
            'email_verified_at' => now(),
            'phone' => '081234567891',
            'password' => bcrypt('password123'),
        ]);

        $this->business = Business::create([
            'name' => 'Bengkel & Servis Mitra Cooca',
            'slug' => 'bengkel-mitra-cooca',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->cashier->id, ['id' => (string) Str::uuid(), 'role' => 'cashier']);
        $this->business->users()->attach($this->mechanic->id, ['id' => (string) Str::uuid(), 'role' => 'staff']);
        $this->cashier->update(['active_business_id' => $this->business->id]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Bengkel Pusat',
            'slug' => 'bengkel-pusat',
            'type' => 'outlet',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $this->shift = PosShift::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'shift_number' => 'SFT-001',
            'opening_cash' => 200000,
            'opened_at' => now(),
            'status' => PosShift::STATUS_OPEN,
        ]);
    }

    public function test_bengkel_checkout_persists_vehicle_spk_and_technician(): void
    {
        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Oli Mesin Sintetik 10W-40 1L',
            'slug' => 'oli-mesin-sintetik',
            'type' => Product::TYPE_GOODS,
            'output_unit_id' => $this->unit->id,
            'selling_price' => 120000,
            'base_cost' => 85000,
            'is_active' => true,
            'show_in_pos' => true,
        ]);

        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $product->id,
            'quantity' => 20,
            'last_cost' => 85000,
        ]);

        $orderService = app(PosOrderService::class);

        $order = $orderService->checkout(
            business: $this->business,
            cashier: $this->cashier,
            itemsData: [
                [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => 120000,
                    'quantity' => 1,
                ]
            ],
            paymentsData: [
                [
                    'payment_method' => 'cash',
                    'amount' => 120000,
                ]
            ],
            attributes: [
                'location_id' => $this->location->id,
                'vehicle_license_plate' => 'B 1234 CD',
                'vehicle_model' => 'Honda Jazz RS 2017',
                'vehicle_mileage' => 45800,
                'technician_id' => $this->mechanic->id,
                'service_notes' => 'Ganti oli mesin & filter oli rutin berkala',
            ],
            shift: $this->shift
        );

        $this->assertInstanceOf(PosOrder::class, $order);
        $this->assertEquals('B 1234 CD', $order->vehicle_license_plate);
        $this->assertEquals('Honda Jazz RS 2017', $order->vehicle_model);
        $this->assertEquals(45800, $order->vehicle_mileage);
        $this->assertEquals($this->mechanic->id, $order->technician_id);
        $this->assertEquals('Pak Joko Mekanik', $order->technician->name);
        $this->assertStringContainsString('Ganti oli mesin', $order->service_notes);
    }

    public function test_apotek_checkout_persists_batch_expired_date_and_dosage_instructions(): void
    {
        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Amoxicillin 500mg Strip',
            'slug' => 'amoxicillin-500mg',
            'type' => Product::TYPE_GOODS,
            'output_unit_id' => $this->unit->id,
            'selling_price' => 15000,
            'base_cost' => 8000,
            'is_active' => true,
            'show_in_pos' => true,
        ]);

        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $product->id,
            'quantity' => 100,
            'last_cost' => 8000,
        ]);

        $orderService = app(PosOrderService::class);

        $order = $orderService->checkout(
            business: $this->business,
            cashier: $this->cashier,
            itemsData: [
                [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => 15000,
                    'quantity' => 2,
                    'batch_number' => 'AMX-2026-09',
                    'expired_date' => '2028-09-30',
                    'dosage_instructions' => '3x1 tablet sehari sesudah makan (habiskan)',
                ]
            ],
            paymentsData: [
                [
                    'payment_method' => 'cash',
                    'amount' => 30000,
                ]
            ],
            attributes: [
                'location_id' => $this->location->id,
            ],
            shift: $this->shift
        );

        $item = $order->items->first();
        $this->assertEquals('AMX-2026-09', $item->batch_number);
        $this->assertEquals('2028-09-30', $item->expired_date->format('Y-m-d'));
        $this->assertEquals('3x1 tablet sehari sesudah makan (habiskan)', $item->dosage_instructions);
    }

    public function test_laundry_checkout_persists_weight_rack_and_estimated_completion(): void
    {
        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Cuci Kering Lipat Reguler',
            'slug' => 'cuci-kering-lipat',
            'type' => Product::TYPE_SERVICE,
            'output_unit_id' => $this->unit->id,
            'selling_price' => 8000,
            'base_cost' => 2500,
            'is_active' => true,
            'show_in_pos' => true,
        ]);

        $orderService = app(PosOrderService::class);

        $order = $orderService->checkout(
            business: $this->business,
            cashier: $this->cashier,
            itemsData: [
                [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => 8000,
                    'quantity' => 4.5, // 4.5 kg
                ]
            ],
            paymentsData: [
                [
                    'payment_method' => 'cash',
                    'amount' => 36000,
                ]
            ],
            attributes: [
                'location_id' => $this->location->id,
                'laundry_weight_kg' => 4.5,
                'rack_location' => 'LOKER-A03',
                'estimated_completion_at' => Carbon::tomorrow()->setTime(17, 0)->toDateTimeString(),
                'laundry_status' => PosOrder::LAUNDRY_STATUS_RECEIVED,
            ],
            shift: $this->shift
        );

        $this->assertEquals(4.5, $order->laundry_weight_kg);
        $this->assertEquals('LOKER-A03', $order->rack_location);
        $this->assertEquals(PosOrder::LAUNDRY_STATUS_RECEIVED, $order->laundry_status);
        $this->assertNotNull($order->estimated_completion_at);
    }

    public function test_daily_kitchen_prep_sheet_aggregates_menu_and_bom_requirements(): void
    {
        $owner = User::create([
            'name' => 'Owner Dapur',
            'email' => 'owner_dapur@example.com',
            'email_verified_at' => now(),
            'phone' => '081299998888',
            'password' => bcrypt('password123'),
        ]);
        $this->business->users()->attach($owner->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $owner->update(['active_business_id' => $this->business->id]);
        $this->actingAs($owner);

        \App\Models\BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code' => \App\Models\BusinessSubscription::PLAN_PREMIUM_MONTHLY,
            'price' => 89000,
            'status' => \App\Models\BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addYear(),
        ]);

        // Create Finished Menu Product
        $menuItem = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Nasi Kotak Ayam Bakar Madu',
            'slug' => 'nasi-kotak-ayam-bakar',
            'type' => Product::TYPE_GOODS,
            'output_unit_id' => $this->unit->id,
            'selling_price' => 30000,
            'base_cost' => 15000,
            'is_active' => true,
            'show_in_website' => true,
        ]);

        // Create Raw Material (Ayam Karkas)
        $material = Material::create([
            'business_id' => $this->business->id,
            'unit_id' => $this->unit->id,
            'name' => 'Ayam Karkas Segar',
            'code' => 'MAT-AYAM-01',
            'slug' => 'ayam-karkas-segar',
        ]);

        // Link Recipe via CostModel and BomHeader
        $costModel = CostModel::create([
            'business_id' => $this->business->id,
            'product_id' => $menuItem->id,
            'name' => 'Resep Standar Nasi Kotak',
            'slug' => 'resep-standar-nasi-kotak',
            'method' => CostModel::METHOD_RECIPE_BOM,
        ]);

        $bomHeader = BomHeader::create([
            'cost_model_id' => $costModel->id,
            'type' => BomHeader::TYPE_RECIPE,
            'level' => 1,
            'name' => 'Resep Utama',
        ]);

        // Each portion takes 0.25 kg of Ayam
        BomItem::create([
            'bom_header_id' => $bomHeader->id,
            'material_id' => $material->id,
            'unit_id' => $this->unit->id,
            'quantity' => 0.25,
            'waste_percentage' => 0,
        ]);

        // Seed Material stock: 10 kg
        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'material_id' => $material->id,
            'quantity' => 10,
            'last_cost' => 35000,
        ]);

        // Create a scheduled order for tomorrow: 20 portions
        $targetDate = Carbon::tomorrow()->toDateString();

        \App\Models\CommerceOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'order_number' => 'CAT-001',
            'tracking_token' => (string) Str::uuid(),
            'order_type' => \App\Models\CommerceOrder::TYPE_SCHEDULED_ORDER,
            'fulfillment_type' => \App\Models\CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY,
            'status' => \App\Models\CommerceOrder::STATUS_PROCESSING,
            'payment_status' => \App\Models\CommerceOrder::PAYMENT_PAID,
            'customer_name' => 'Ibu Maya',
            'customer_phone' => '081299887766',
            'scheduled_date' => $targetDate,
            'scheduled_time_slot' => '11:00 - 12:00 (Makan Siang)',
            'subtotal' => 600000,
            'total_amount' => 600000,
        ])->items()->create([
            'product_id' => $menuItem->id,
            'product_name' => $menuItem->name,
            'unit_price' => 30000,
            'quantity' => 20,
            'total_amount' => 600000,
        ]);

        // Request Prep Sheet view
        $response = $this->get(route('pos.kitchen.prep_sheet', ['date' => $targetDate]));

        $response->assertOk();
        $response->assertSee(__('pos.prep_sheet_title'));
        $response->assertSee('Nasi Kotak Ayam Bakar Madu');
        $response->assertSee('Ayam Karkas Segar');
        // 20 portions * 0.25 = 5.00 kg required. Stock is 10 kg -> Should be sufficient (Cukup)
        $response->assertSee('Cukup (+5,0)');
    }
}
