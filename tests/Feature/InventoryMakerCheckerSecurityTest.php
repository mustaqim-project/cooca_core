<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\InventoryStock;
use App\Models\JournalEntry;
use App\Models\Location;
use App\Models\Product;
use App\Models\Role;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

final class InventoryMakerCheckerSecurityTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private User $staff;
    private Location $location;
    private Product $product;
    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);

        $this->owner = User::create([
            'name' => 'Owner Bisnis',
            'email' => 'owner.inv@cooca.test',
            'phone' => '081234567801',
            'password' => 'password',
        ]);
        $this->owner->forceFill(['email_verified_at' => now()])->save();

        $this->staff = User::create([
            'name' => 'Staff Gudang',
            'email' => 'staff.inv@cooca.test',
            'phone' => '081234567802',
            'password' => 'password',
        ]);
        $this->staff->forceFill(['email_verified_at' => now()])->save();

        $this->business = Business::create([
            'name' => 'Gudang Logistik Cooca',
            'pos_supervisor_pin' => Hash::make('778899'),
        ]);

        $ownerRole = Role::where('slug', 'owner')->firstOrFail();
        $staffRole = Role::where('slug', 'warehouse')->firstOrFail();

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole->id,
        ]);

        $this->business->users()->attach($this->staff->id, [
            'id' => (string) Str::uuid(),
            'role' => 'warehouse',
            'role_id' => $staffRole->id,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
        $this->staff->update(['active_business_id' => $this->business->id]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Gudang Utama',
            'type' => 'warehouse',
            'is_active' => true,
        ]);

        $this->unit = Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Pieces',
            'symbol' => 'pcs',
            'code' => 'PCS',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Biji Kopi Arabika Premium 1kg',
            'code' => 'KOPI-001',
            'type' => Product::TYPE_GOODS,
            'selling_price' => 250000,
            'base_cost' => 150000,
            'is_active' => true,
        ]);
    }

    public function test_low_value_adjustment_by_staff_completes_immediately(): void
    {
        // Initial stock: 10 units at Rp 50.000 cost
        $stock = InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'last_cost' => 50000,
        ]);

        // Staff adjusts from 10 to 9 (-1 unit, loss = Rp 50.000 < Rp 1.000.000 threshold)
        $response = $this->actingAs($this->staff)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->staff->id,
                'active_business_id' => $this->business->id,
            ])
            ->postJson(route('inventory.stocks.adjust'), [
                'location_id' => $this->location->id,
                'product_id' => $this->product->id,
                'new_quantity' => 9,
                'unit_cost' => 50000,
                'reason_code' => 'opname_variance',
                'notes' => 'Selisih hitung 1 botol pecah',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Stock immediately updated
        $this->assertEquals(9.0, (float) $stock->fresh()->quantity);

        // Completed StockAdjustment recorded
        $this->assertDatabaseHas('stock_adjustments', [
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'status' => 'completed',
            'created_by' => $this->staff->id,
        ]);

        // StockMovement recorded
        $this->assertDatabaseHas('stock_movements', [
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity_change' => -1,
        ]);
    }

    public function test_high_value_stock_reduction_by_staff_triggers_maker_checker_pending_approval(): void
    {
        // Initial stock: 20 units at Rp 150.000 cost = Rp 3.000.000 total
        $stock = InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity' => 20,
            'last_cost' => 150000,
        ]);

        // Staff attempts to reduce from 20 to 10 (-10 units, loss = Rp 1.500.000 > Rp 1.000.000 threshold)
        // With Supervisor PIN 778899
        $response = $this->actingAs($this->staff)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->staff->id,
                'active_business_id' => $this->business->id,
            ])
            ->postJson(route('inventory.stocks.adjust'), [
                'location_id' => $this->location->id,
                'product_id' => $this->product->id,
                'new_quantity' => 10,
                'unit_cost' => 150000,
                'reason_code' => 'damaged',
                'notes' => '10 pack basah terkena hujan bocor',
                'supervisor_pin' => '778899',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'pending_approval' => true,
        ]);

        // CRITICAL GUARD: Physical stock must NOT change before approval!
        $this->assertEquals(20.0, (float) $stock->fresh()->quantity);

        // Pending StockAdjustment created
        $adjustment = StockAdjustment::where('business_id', $this->business->id)
            ->where('status', 'pending_approval')
            ->first();

        $this->assertNotNull($adjustment);
        $this->assertEquals(1500000.0, (float) $adjustment->total_loss_cost);
        $this->assertEquals($this->staff->id, $adjustment->created_by);
        $this->assertNull($adjustment->approved_by);

        // Audit log recorded for Maker-Checker
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'STOCK_ADJUSTMENT_PENDING_APPROVAL',
        ]);
    }

    public function test_owner_can_approve_pending_high_value_stock_adjustment(): void
    {
        $stock = InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity' => 20,
            'last_cost' => 150000,
        ]);

        // Create pending adjustment of -10 units (Rp 1.500.000)
        $adjustment = StockAdjustment::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'adjustment_number' => 'ADJ-TEST-001',
            'adjustment_date' => now()->toDateString(),
            'reason' => 'damaged',
            'status' => 'pending_approval',
            'total_loss_cost' => 1500000,
            'notes' => 'Barang rusak gudang',
            'created_by' => $this->staff->id,
        ]);

        $adjustment->items()->create([
            'product_id' => $this->product->id,
            'system_quantity' => 20,
            'adjusted_quantity' => 10,
            'difference_quantity' => -10,
            'unit_cost' => 150000,
            'total_cost' => 1500000,
            'notes' => '10 pack basah',
        ]);

        // Staff tries to approve -> 403 Forbidden
        $responseForbidden = $this->actingAs($this->staff)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->staff->id,
                'active_business_id' => $this->business->id,
            ])
            ->postJson(route('inventory.adjustments.approve', $adjustment->id));

        $responseForbidden->assertStatus(403);
        $this->assertEquals('pending_approval', $adjustment->fresh()->status);

        // Owner approves -> 200 OK
        $responseOwner = $this->actingAs($this->owner)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->owner->id,
                'active_business_id' => $this->business->id,
            ])
            ->postJson(route('inventory.adjustments.approve', $adjustment->id));

        $responseOwner->assertStatus(200);
        $responseOwner->assertJson(['success' => true]);

        // Verify status completed and stock deducted
        $this->assertEquals('completed', $adjustment->fresh()->status);
        $this->assertEquals($this->owner->id, $adjustment->fresh()->approved_by);
        $this->assertNotNull($adjustment->fresh()->approved_at);
        $this->assertEquals(10.0, (float) $stock->fresh()->quantity);

        // Verify StockMovement recorded
        $this->assertDatabaseHas('stock_movements', [
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity_change' => -10,
        ]);

        // Verify Audit Log recorded
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'STOCK_ADJUSTMENT_APPROVED',
        ]);
    }

    public function test_owner_can_reject_pending_stock_adjustment(): void
    {
        $stock = InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity' => 20,
            'last_cost' => 150000,
        ]);

        $adjustment = StockAdjustment::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'adjustment_number' => 'ADJ-TEST-002',
            'adjustment_date' => now()->toDateString(),
            'reason' => 'theft_loss',
            'status' => 'pending_approval',
            'total_loss_cost' => 1500000,
            'notes' => 'Dugaan kehilangan',
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->owner->id,
                'active_business_id' => $this->business->id,
            ])
            ->postJson(route('inventory.adjustments.reject', $adjustment->id), [
                'reason' => 'Periksa rekaman CCTV terlebih dahulu sebelum write-off.',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Status rejected and stock untouched
        $this->assertEquals('rejected', $adjustment->fresh()->status);
        $this->assertEquals(20.0, (float) $stock->fresh()->quantity);
        $this->assertStringContainsString('CCTV', (string) $adjustment->fresh()->notes);

        // Audit Log recorded
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'STOCK_ADJUSTMENT_REJECTED',
        ]);
    }

    public function test_inventory_maker_checker_localization_in_id_and_en(): void
    {
        app()->setLocale('id');

        $stock = InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity' => 20,
            'last_cost' => 150000,
        ]);

        // Test Indonesian response
        $resId = $this->actingAs($this->staff)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->staff->id,
                'active_business_id' => $this->business->id,
            ])
            ->postJson(route('inventory.stocks.adjust'), [
                'location_id' => $this->location->id,
                'product_id' => $this->product->id,
                'new_quantity' => 5,
                'unit_cost' => 150000,
                'reason_code' => 'damaged',
                'notes' => '15 pack rusak',
                'supervisor_pin' => '778899',
            ]);

        $resId->assertStatus(200);
        $this->assertStringContainsString('menunggu persetujuan', (string) $resId->json('message'));

        // Test English response
        app()->setLocale('en');

        $stock2 = InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity' => 30,
            'last_cost' => 150000,
        ]);

        $resEn = $this->actingAs($this->staff)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->staff->id,
                'active_business_id' => $this->business->id,
            ])
            ->postJson(route('inventory.stocks.adjust'), [
                'location_id' => $this->location->id,
                'product_id' => $this->product->id,
                'new_quantity' => 5,
                'unit_cost' => 150000,
                'reason_code' => 'damaged',
                'notes' => '25 pack damaged water leak',
                'supervisor_pin' => '778899',
            ]);

        $resEn->assertStatus(200);
        $this->assertStringContainsString('pending Owner', (string) $resEn->json('message'));

        app()->setLocale('id');
    }

    public function test_custom_configured_approval_rule_threshold_is_enforced_for_stock_adjustment(): void
    {
        // Configure custom ApprovalRule: min_amount = Rp 200.000 (lower than default Rp 1.000.000)
        \App\Models\ApprovalRule::create([
            'business_id' => $this->business->id,
            'document_type' => \App\Models\ApprovalRule::DOC_STOCK_ADJUSTMENT,
            'name' => 'Batas Ketat Penyesuaian Toko Kopi',
            'min_amount' => 200000,
            'required_levels' => 1,
            'approver_role_level_1' => 'supervisor',
            'is_active' => true,
        ]);

        $stock = InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'last_cost' => 150000,
        ]);

        // Staff adjusts from 10 to 8 (-2 units, loss = Rp 300.000)
        // With default Rp 1.000.000, Rp 300.000 would pass immediately without rule.
        // BUT with custom rule of Rp 200.000, Rp 300.000 triggers pending_approval!
        $response = $this->actingAs($this->staff)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->staff->id,
                'active_business_id' => $this->business->id,
            ])
            ->postJson(route('inventory.stocks.adjust'), [
                'location_id' => $this->location->id,
                'product_id' => $this->product->id,
                'new_quantity' => 8,
                'unit_cost' => 150000,
                'reason_code' => 'expired',
                'notes' => '2 pack kadaluarsa',
                'supervisor_pin' => '778899',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'pending_approval' => true,
        ]);

        // Stock remains 10 until approved
        $this->assertEquals(10.0, (float) $stock->fresh()->quantity);
    }
}
