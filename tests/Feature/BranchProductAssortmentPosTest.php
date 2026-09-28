<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BranchProductPrice;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\Location;
use App\Models\PosRegister;
use App\Models\PosShift;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductChannelPrice;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BranchProductAssortmentPosTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private Location $outletTebet;
    private Location $outletRestArea;
    private Product $productAvailableEverywhere;
    private Product $productDisabledAtRestArea;
    private PosRegister $registerRestArea;
    private PosShift $activeShiftRestArea;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->owner = User::create([
            'name' => 'Juragan F&B',
            'email' => 'juragan@cooca.id',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone' => '6281234567899',
        ]);

        $this->business = Business::create([
            'name' => 'Kopi Rest Area & Kota',
            'slug' => 'kopi-rest-area-kota',
            'pos_enable_tax' => false,
            'pos_enable_service_charge' => false,
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code' => BusinessSubscription::PLAN_PREMIUM_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
        ]);

        $this->outletTebet = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Cabang Tebet (Kota)',
            'code' => 'TBT-01',
            'type' => 'outlet',
            'is_active' => true,
        ]);

        $this->outletRestArea = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Cabang Rest Area KM 57',
            'code' => 'KM57-01',
            'type' => 'outlet',
            'is_active' => true,
        ]);

        $unit = Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Porsi',
            'code' => 'PORSI',
            'symbol' => 'prs',
            'category' => 'piece',
            'is_active' => true,
        ]);

        $category = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Menu Utama',
            'slug' => 'menu-utama',
        ]);

        // Product 1: Dijual di semua cabang (Normal)
        $this->productAvailableEverywhere = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'name' => 'Kopi Susu Gula Aren',
            'code' => 'KOP-001',
            'selling_price' => 20000,
            'base_cost' => 8000,
            'output_unit_id' => $unit->id,
            'is_active' => true,
        ]);

        // Product 2: Dinonaktifkan di Rest Area KM 57
        $this->productDisabledAtRestArea = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'name' => 'Steak Hotplate Spesial',
            'code' => 'STK-001',
            'selling_price' => 75000,
            'base_cost' => 40000,
            'output_unit_id' => $unit->id,
            'is_active' => true,
        ]);

        // Override: Steak dinonaktifkan di Rest Area KM 57
        BranchProductPrice::create([
            'business_id' => $this->business->id,
            'location_id' => $this->outletRestArea->id,
            'product_id' => $this->productDisabledAtRestArea->id,
            'price' => null,
            'cost_price' => null,
            'is_available' => false,
        ]);

        // Register & Shift di Rest Area untuk pengujian checkout
        $this->registerRestArea = PosRegister::create([
            'business_id' => $this->business->id,
            'location_id' => $this->outletRestArea->id,
            'name' => 'Kasir 1 Rest Area',
            'is_active' => true,
        ]);

        $this->activeShiftRestArea = PosShift::create([
            'business_id' => $this->business->id,
            'location_id' => $this->outletRestArea->id,
            'pos_register_id' => $this->registerRestArea->id,
            'user_id' => $this->owner->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'status' => 'open',
        ]);
    }

    public function test_product_marked_unavailable_at_branch_is_hidden_from_pos_terminal(): void
    {
        // 1. Kasir membuka POS di Rest Area KM 57
        $responseRestArea = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('pos.terminal', ['location_id' => $this->outletRestArea->id]));

        $responseRestArea->assertOk();
        $productsRestArea = $responseRestArea->viewData('products');

        // Pastikan Steak TIDAK MUNCUL di Rest Area
        $this->assertFalse(
            $productsRestArea->contains('id', $this->productDisabledAtRestArea->id),
            'Produk yang dinonaktifkan tidak boleh muncul di katalog POS Rest Area.'
        );
        // Pastikan Kopi Susu TETAP MUNCUL
        $this->assertTrue(
            $productsRestArea->contains('id', $this->productAvailableEverywhere->id)
        );

        // 2. Kasir membuka POS di Cabang Tebet
        $responseTebet = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('pos.terminal', ['location_id' => $this->outletTebet->id]));

        $responseTebet->assertOk();
        $productsTebet = $responseTebet->viewData('products');

        // Pastikan di Tebet KEDUA PRODUK TETAP MUNCUL
        $this->assertTrue(
            $productsTebet->contains('id', $this->productDisabledAtRestArea->id),
            'Steak harus tetap muncul di Cabang Tebet.'
        );
        $this->assertTrue(
            $productsTebet->contains('id', $this->productAvailableEverywhere->id)
        );
    }

    public function test_product_marked_unavailable_at_branch_is_excluded_from_pos_search(): void
    {
        // 1. Cari 'Steak' di Rest Area KM 57
        $responseSearchRestArea = $this->actingAs($this->owner)
            ->getJson(route('pos.search-products', [
                'q' => 'Steak',
                'location_id' => $this->outletRestArea->id,
            ]));

        $responseSearchRestArea->assertOk();
        $this->assertEmpty($responseSearchRestArea->json('products'));

        // 2. Cari 'Steak' di Tebet
        $responseSearchTebet = $this->actingAs($this->owner)
            ->getJson(route('pos.search-products', [
                'q' => 'Steak',
                'location_id' => $this->outletTebet->id,
            ]));

        $responseSearchTebet->assertOk();
        $this->assertNotEmpty($responseSearchTebet->json('products'));
        $this->assertEquals($this->productDisabledAtRestArea->id, $responseSearchTebet->json('products.0.id'));
    }

    public function test_product_with_branch_price_override_applies_and_protects_channel_price(): void
    {
        // Di Rest Area, harga Kopi Susu dinaikkan jadi 28.000
        BranchProductPrice::create([
            'business_id' => $this->business->id,
            'location_id' => $this->outletRestArea->id,
            'product_id' => $this->productAvailableEverywhere->id,
            'price' => 28000,
            'cost_price' => 9500,
            'is_available' => true,
        ]);

        // Setting channel price global untuk GoFood = 24.000 (dihitung dari harga master 20.000)
        ProductChannelPrice::create([
            'business_id' => $this->business->id,
            'product_id' => $this->productAvailableEverywhere->id,
            'channel' => 'gofood',
            'price' => 24000,
        ]);

        // 1. Periksa katalog di Rest Area KM 57
        $responseRestArea = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('pos.terminal', ['location_id' => $this->outletRestArea->id]));

        $productsRestArea = $responseRestArea->viewData('products');
        $kopiRestArea = $productsRestArea->firstWhere('id', $this->productAvailableEverywhere->id);

        $this->assertNotNull($kopiRestArea);
        $this->assertEquals(28000.0, (float) $kopiRestArea->selling_price);

        // Pastikan GoFood di Rest Area terlindungi tidak jatuh ke 24.000 (harus max(24.000, 28.000) = 28.000)
        $this->assertEquals(28000.0, (float) $kopiRestArea->channel_prices['gofood']);

        // 2. Periksa katalog di Tebet
        $responseTebet = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('pos.terminal', ['location_id' => $this->outletTebet->id]));

        $productsTebet = $responseTebet->viewData('products');
        $kopiTebet = $productsTebet->firstWhere('id', $this->productAvailableEverywhere->id);

        $this->assertNotNull($kopiTebet);
        $this->assertEquals(20000.0, (float) $kopiTebet->selling_price);
        $this->assertEquals(24000.0, (float) $kopiTebet->channel_prices['gofood']);
    }

    public function test_checkout_rejects_products_not_available_at_branch(): void
    {
        // Mencoba checkout Steak Hotplate (yang dilarang di Rest Area)
        $response = $this->actingAs($this->owner)
            ->postJson(route('pos.checkout'), [
                'location_id' => $this->outletRestArea->id,
                'order_type' => 'dine_in',
                'sales_channel' => 'dine_in',
                'items' => [
                    [
                        'product_id' => $this->productDisabledAtRestArea->id,
                        'product_name' => $this->productDisabledAtRestArea->name,
                        'unit_price' => 75000,
                        'quantity' => 1,
                    ],
                ],
                'payments' => [
                    [
                        'payment_method' => 'cash',
                        'amount' => 75000,
                    ],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Terdapat produk yang tidak tersedia untuk dijual di cabang ini.');
    }

    public function test_bulk_branch_update_with_mixed_states(): void
    {
        $outletBandung = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Cabang Bandung',
            'code' => 'BDG-01',
            'type' => 'outlet',
            'is_active' => true,
        ]);

        // Kirim update massal 3 cabang sekaligus:
        // 1. Tebet: Reset ke master (is_available = true, reset = true)
        // 2. Rest Area: Custom price 30.000 (is_available = true)
        // 3. Bandung: Dinonaktifkan (is_available = false)
        $response = $this->actingAs($this->owner)
            ->postJson(route('products.branch_prices.update', $this->productAvailableEverywhere->id), [
                'prices' => [
                    [
                        'location_id' => $this->outletTebet->id,
                        'is_available' => true,
                        'reset' => true,
                    ],
                    [
                        'location_id' => $this->outletRestArea->id,
                        'is_available' => true,
                        'price' => 30000,
                        'cost_price' => 11000,
                        'use_custom' => true,
                    ],
                    [
                        'location_id' => $outletBandung->id,
                        'is_available' => false,
                        'price' => null,
                        'use_custom' => false,
                    ],
                ],
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        // Verifikasi database:
        // Tebet: tidak ada baris (clean reset)
        $this->assertDatabaseMissing('branch_product_prices', [
            'product_id' => $this->productAvailableEverywhere->id,
            'location_id' => $this->outletTebet->id,
        ]);

        // Rest Area: custom price 30.000
        $this->assertDatabaseHas('branch_product_prices', [
            'product_id' => $this->productAvailableEverywhere->id,
            'location_id' => $this->outletRestArea->id,
            'price' => 30000,
            'cost_price' => 11000,
            'is_available' => true,
        ]);

        // Bandung: nonaktif
        $this->assertDatabaseHas('branch_product_prices', [
            'product_id' => $this->productAvailableEverywhere->id,
            'location_id' => $outletBandung->id,
            'is_available' => false,
        ]);
    }

    public function test_cross_tenant_branch_isolation_in_pos(): void
    {
        $otherBusiness = Business::create([
            'name' => 'Bisnis Kompetitor',
            'slug' => 'bisnis-kompetitor',
        ]);

        $otherUnit = Unit::create([
            'business_id' => $otherBusiness->id,
            'name' => 'Cup',
            'code' => 'CUP2',
            'category' => 'piece',
            'is_active' => true,
        ]);

        $otherProduct = Product::create([
            'business_id' => $otherBusiness->id,
            'name' => 'Kopi Kompetitor',
            'code' => 'KOMP-01',
            'selling_price' => 50000,
            'output_unit_id' => $otherUnit->id,
            'is_active' => true,
        ]);

        $otherLocation = Location::create([
            'business_id' => $otherBusiness->id,
            'name' => 'Outlet Luar',
            'type' => 'outlet',
            'is_active' => true,
        ]);

        // Set nonaktif di bisnis lain
        BranchProductPrice::create([
            'business_id' => $otherBusiness->id,
            'location_id' => $otherLocation->id,
            'product_id' => $otherProduct->id,
            'is_available' => false,
        ]);

        // Pastikan POS Bisnis A tidak terpengaruh oleh nonaktif bisnis lain
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('pos.terminal', ['location_id' => $this->outletRestArea->id]));

        $response->assertOk();
        $products = $response->viewData('products');

        // Pastikan produk bisnis lain tidak pernah bocor
        $this->assertFalse($products->contains('id', $otherProduct->id));
    }
}

