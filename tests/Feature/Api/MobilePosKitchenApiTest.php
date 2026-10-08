<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Business;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\Product;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class MobilePosKitchenApiTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private Business $otherBusiness;
    private User $owner;
    private User $kitchenStaff;
    private Location $kitchenLocation;
    private Product $foodProduct;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        // 1. Setup Business & Owner
        $this->owner = User::create([
            'name' => 'Owner Resto',
            'email' => 'owner@resto-kds.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'Resto Cooca Kitchen',
            'slug' => 'resto-cooca-kitchen',
            'email' => 'kitchen@cooca.com',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);

        // 2. Kitchen Location
        $this->kitchenLocation = Location::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Dapur Utama',
            'slug' => 'dapur-utama',
            'is_active' => true,
            'is_primary' => true,
        ]);

        // 3. Unit & Product
        $unit = \App\Models\Unit::create([
            'business_id' => $this->business->id,
            'code' => 'PORSI',
            'name' => 'Porsi',
            'category' => \App\Models\Unit::CATEGORY_QUANTITY,
        ]);

        $this->foodProduct = Product::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'output_unit_id' => $unit->id,
            'name' => 'Nasi Goreng Spesial',
            'slug' => 'nasi-goreng-spesial',
            'selling_price' => 35000,
            'is_active' => true,
            'status' => 'published',
        ]);

        // 4. Other Business for Tenant Isolation Test
        $this->otherBusiness = Business::create([
            'name' => 'Kafe Tetangga',
            'slug' => 'kafe-tetangga',
            'is_active' => true,
        ]);
    }

    public function test_guest_cannot_access_kitchen_orders(): void
    {
        $response = $this->getJson('/api/v1/pos/kitchen/orders');
        $response->assertStatus(401);
    }

    public function test_can_fetch_active_kitchen_orders(): void
    {
        Sanctum::actingAs($this->owner);

        // Create 1 incoming order and 1 cooking order
        $orderIncoming = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->kitchenLocation->id,
            'user_id' => $this->owner->id,
            'order_number' => 'ORD-KDS-001',
            'order_date' => now()->toDateString(),
            'status' => PosOrder::STATUS_CONFIRMED,
            'total_amount' => 35000,
        ]);

        PosOrderItem::create([
            'pos_order_id' => $orderIncoming->id,
            'product_id' => $this->foodProduct->id,
            'product_name' => $this->foodProduct->name,
            'unit_price' => 35000,
            'quantity' => 1,
            'total_price' => 35000,
        ]);

        $orderCooking = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->kitchenLocation->id,
            'user_id' => $this->owner->id,
            'order_number' => 'ORD-KDS-002',
            'order_date' => now()->toDateString(),
            'status' => PosOrder::STATUS_PREPARING,
            'total_amount' => 70000,
        ]);

        $otherLocation = Location::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->otherBusiness->id,
            'name' => 'Outlet Tetangga',
            'is_active' => true,
        ]);

        // Other business order (must NOT appear)
        PosOrder::create([
            'business_id' => $this->otherBusiness->id,
            'location_id' => $otherLocation->id,
            'user_id' => $this->owner->id,
            'order_number' => 'ORD-OTHER-999',
            'order_date' => now()->toDateString(),
            'status' => PosOrder::STATUS_CONFIRMED,
            'total_amount' => 50000,
        ]);

        $response = $this->getJson('/api/v1/pos/kitchen/orders', [
            'X-Business-Id' => $this->business->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'summary' => [
                'total_active' => 2,
                'incoming_count' => 1,
                'cooking_count' => 1,
                'ready_count' => 0,
            ],
        ]);
        $response->assertJsonMissing(['order_number' => 'ORD-OTHER-999']);
    }

    public function test_can_update_kitchen_order_status(): void
    {
        Sanctum::actingAs($this->owner);

        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->kitchenLocation->id,
            'user_id' => $this->owner->id,
            'order_number' => 'ORD-KDS-COOK-01',
            'order_date' => now()->toDateString(),
            'status' => PosOrder::STATUS_CONFIRMED,
            'total_amount' => 35000,
        ]);

        // Step 1: Update to preparing
        $resPrep = $this->postJson("/api/v1/pos/kitchen/orders/{$order->id}/status", [
            'status' => 'preparing',
        ], [
            'X-Business-Id' => $this->business->id,
        ]);

        $resPrep->assertStatus(200);
        $this->assertEquals(PosOrder::STATUS_PREPARING, $order->fresh()->status);

        // Step 2: Update to ready
        $resReady = $this->postJson("/api/v1/pos/kitchen/orders/{$order->id}/status", [
            'status' => 'ready',
        ], [
            'X-Business-Id' => $this->business->id,
        ]);

        $resReady->assertStatus(200);
        $this->assertEquals(PosOrder::STATUS_READY, $order->fresh()->status);
    }

    public function test_can_fetch_prep_sheet(): void
    {
        Sanctum::actingAs($this->owner);

        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->kitchenLocation->id,
            'user_id' => $this->owner->id,
            'order_number' => 'ORD-KDS-PREP-01',
            'order_date' => now()->toDateString(),
            'status' => PosOrder::STATUS_CONFIRMED,
            'total_amount' => 70000,
        ]);

        PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_id' => $this->foodProduct->id,
            'product_name' => $this->foodProduct->name,
            'unit_price' => 35000,
            'quantity' => 2,
            'total_price' => 70000,
        ]);

        $response = $this->getJson('/api/v1/pos/kitchen/prep-sheet', [
            'X-Business-Id' => $this->business->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'menu_portions' => [
                    [
                        'product_name' => 'Nasi Goreng Spesial',
                        'total_quantity' => 2,
                    ],
                ],
            ],
        ]);
    }
}
