<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Template\ModuleRegistry;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\InventoryStock;
use App\Models\JournalEntry;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class WarehouseSecurityAndEntitlementHardeningTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;
    private Business $businessA;
    private Unit $unitA;

    private User $userB;
    private Business $businessB;
    private Unit $unitB;

    protected function setUp(): void
    {
        parent::setUp();

        // Tenant A
        $this->userA = User::factory()->create(['email' => 'tenant_a@cooca.id']);
        $this->businessA = Business::create([
            'owner_id' => $this->userA->id,
            'name'     => 'Tenant A Enterprise',
        ]);
        $this->userA->businesses()->attach($this->businessA->id, ['role' => 'owner', 'status' => 'active']);
        $this->unitA = Unit::create([
            'business_id' => $this->businessA->id,
            'code'        => 'pcs',
            'name'        => 'Pieces',
            'category'    => Unit::CATEGORY_QUANTITY,
        ]);

        // Tenant B
        $this->userB = User::factory()->create(['email' => 'tenant_b@cooca.id']);
        $this->businessB = Business::create([
            'owner_id' => $this->userB->id,
            'name'     => 'Tenant B Enterprise',
        ]);
        $this->userB->businesses()->attach($this->businessB->id, ['role' => 'owner', 'status' => 'active']);
        $this->unitB = Unit::create([
            'business_id' => $this->businessB->id,
            'code'        => 'pcs',
            'name'        => 'Pieces',
            'category'    => Unit::CATEGORY_QUANTITY,
        ]);
    }

    public function test_quick_adjust_rejects_location_belonging_to_another_tenant(): void
    {
        Context::setBusiness($this->businessA);

        $locB = Location::create([
            'business_id' => $this->businessB->id,
            'name'        => 'Gudang Tenant B',
            'type'        => 'warehouse',
            'is_active'   => true,
        ]);

        $prodA = Product::create([
            'business_id'    => $this->businessA->id,
            'name'           => 'Kopi Robusta',
            'type'           => Product::TYPE_GOODS,
            'output_unit_id' => $this->unitA->id,
            'is_active'      => true,
        ]);

        $response = $this->actingAs($this->userA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('inventory.stocks.adjust'), [
                'location_id'  => $locB->id, // belongs to Tenant B!
                'product_id'   => $prodA->id,
                'new_quantity' => 10,
            ]);

        $response->assertSessionHasErrors('location_id');
    }

    public function test_quick_adjust_rejects_product_belonging_to_another_tenant(): void
    {
        Context::setBusiness($this->businessA);

        $locA = Location::create([
            'business_id' => $this->businessA->id,
            'name'        => 'Gudang Tenant A',
            'type'        => 'warehouse',
            'is_active'   => true,
        ]);

        $prodB = Product::create([
            'business_id'    => $this->businessB->id,
            'name'           => 'Bahan Rahasia Tenant B',
            'type'           => Product::TYPE_GOODS,
            'output_unit_id' => $this->unitB->id,
            'is_active'      => true,
        ]);

        $response = $this->actingAs($this->userA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('inventory.stocks.adjust'), [
                'location_id'  => $locA->id,
                'product_id'   => $prodB->id, // belongs to Tenant B!
                'new_quantity' => 10,
            ]);

        $response->assertSessionHasErrors('product_id');
    }

    public function test_quick_adjust_records_reason_code_in_stock_movement_notes(): void
    {
        Context::setBusiness($this->businessA);

        $locA = Location::create([
            'business_id' => $this->businessA->id,
            'name'        => 'Gudang Utama A',
            'type'        => 'warehouse',
            'is_active'   => true,
        ]);

        $prodA = Product::create([
            'business_id'    => $this->businessA->id,
            'name'           => 'Sirup Vanila',
            'type'           => Product::TYPE_GOODS,
            'output_unit_id' => $this->unitA->id,
            'is_active'      => true,
        ]);

        $response = $this->actingAs($this->userA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('inventory.stocks.adjust'), [
                'location_id'  => $locA->id,
                'product_id'   => $prodA->id,
                'new_quantity' => 25,
                'unit_cost'    => 50000,
                'reason_code'  => 'opname_variance',
                'notes'        => 'Selisih fisik vs sistem',
            ]);

        $response->assertRedirect();

        $movement = StockMovement::where('business_id', $this->businessA->id)
            ->where('location_id', $locA->id)
            ->where('product_id', $prodA->id)
            ->latest()
            ->first();

        $this->assertNotNull($movement);
        $this->assertStringContainsString('[opname_variance]', $movement->notes);
        $this->assertStringContainsString('Selisih fisik vs sistem', $movement->notes);
    }

    public function test_receive_transfer_aborts_404_if_transfer_belongs_to_another_tenant(): void
    {
        Context::setBusiness($this->businessB);

        $locB1 = Location::create([
            'business_id' => $this->businessB->id,
            'name'        => 'Gudang Pusat B',
            'type'        => 'warehouse',
            'is_active'   => true,
        ]);
        $locB2 = Location::create([
            'business_id' => $this->businessB->id,
            'name'        => 'Outlet B1',
            'type'        => 'outlet',
            'is_active'   => true,
        ]);

        $transferB = StockTransfer::create([
            'business_id'             => $this->businessB->id,
            'source_location_id'      => $locB1->id,
            'destination_location_id' => $locB2->id,
            'transfer_number'         => 'TRF-TEST-B-001',
            'transfer_date'           => now()->toDateString(),
            'status'                  => StockTransfer::STATUS_IN_TRANSIT,
            'created_by'              => $this->userB->id,
        ]);

        // User A attempts to receive User B's transfer
        Context::setBusiness($this->businessA);

        $response = $this->actingAs($this->userA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('inventory.transfers.receive', $transferB));

        $response->assertNotFound();
    }

    public function test_geo_routes_have_throttle_middleware(): void
    {
        $routes = collect(Route::getRoutes()->getRoutes());

        $searchRoute = $routes->first(fn ($r) => $r->getName() === 'geo.search-areas');
        $this->assertNotNull($searchRoute);
        $this->assertContains('throttle:60,1', $searchRoute->gatherMiddleware());

        $reverseRoute = $routes->first(fn ($r) => $r->getName() === 'geo.reverse-geocode');
        $this->assertNotNull($reverseRoute);
        $this->assertContains('throttle:60,1', $reverseRoute->gatherMiddleware());
    }

    public function test_quick_adjust_requires_supervisor_pin_when_quantity_shrinkage_exceeds_threshold(): void
    {
        Context::setBusiness($this->businessA);

        $loc = Location::create([
            'business_id' => $this->businessA->id,
            'name'        => 'Gudang Utama',
            'type'        => 'warehouse',
            'is_active'   => true,
        ]);

        $prod = Product::create([
            'business_id'    => $this->businessA->id,
            'name'           => 'Beras Premium',
            'type'           => Product::TYPE_GOODS,
            'output_unit_id' => $this->unitA->id,
            'is_active'      => true,
        ]);

        // Initial stock = 20
        InventoryStock::create([
            'business_id' => $this->businessA->id,
            'location_id' => $loc->id,
            'product_id'  => $prod->id,
            'quantity'    => 20,
            'last_cost'   => 10000,
        ]);

        // Shrinkage: 20 -> 5 (diff = -15, which is > 10 units) without PIN
        $response = $this->actingAs($this->userA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('inventory.stocks.adjust'), [
                'location_id'  => $loc->id,
                'product_id'   => $prod->id,
                'new_quantity' => 5,
                'unit_cost'    => 10000,
                'reason_code'  => 'damaged',
            ]);

        $response->assertSessionHasErrors('supervisor_pin');
    }

    public function test_quick_adjust_requires_supervisor_pin_when_valuation_shrinkage_exceeds_threshold(): void
    {
        Context::setBusiness($this->businessA);

        $loc = Location::create([
            'business_id' => $this->businessA->id,
            'name'        => 'Gudang Utama',
            'type'        => 'warehouse',
            'is_active'   => true,
        ]);

        $prod = Product::create([
            'business_id'    => $this->businessA->id,
            'name'           => 'Daging Wagyu A5',
            'type'           => Product::TYPE_GOODS,
            'output_unit_id' => $this->unitA->id,
            'is_active'      => true,
        ]);

        // Initial stock = 5, cost = 50.000 per unit
        InventoryStock::create([
            'business_id' => $this->businessA->id,
            'location_id' => $loc->id,
            'product_id'  => $prod->id,
            'quantity'    => 5,
            'last_cost'   => 50000,
        ]);

        // Shrinkage: 5 -> 2 (diff = -3 units, but valuation diff = 3 * 50.000 = Rp 150.000 > Rp 100.000)
        $response = $this->actingAs($this->userA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('inventory.stocks.adjust'), [
                'location_id'  => $loc->id,
                'product_id'   => $prod->id,
                'new_quantity' => 2,
                'unit_cost'    => 50000,
                'reason_code'  => 'expired',
            ]);

        $response->assertSessionHasErrors('supervisor_pin');
    }

    public function test_quick_adjust_succeeds_with_valid_supervisor_pin_on_shrinkage(): void
    {
        Context::setBusiness($this->businessA);
        $this->businessA->update(['pos_supervisor_pin' => Hash::make('654321')]);

        $loc = Location::create([
            'business_id' => $this->businessA->id,
            'name'        => 'Gudang Utama',
            'type'        => 'warehouse',
            'is_active'   => true,
        ]);

        $prod = Product::create([
            'business_id'    => $this->businessA->id,
            'name'           => 'Tepung Terigu',
            'type'           => Product::TYPE_GOODS,
            'output_unit_id' => $this->unitA->id,
            'is_active'      => true,
        ]);

        InventoryStock::create([
            'business_id' => $this->businessA->id,
            'location_id' => $loc->id,
            'product_id'  => $prod->id,
            'quantity'    => 30,
            'last_cost'   => 10000,
        ]);

        // Shrinkage: 30 -> 10 (diff = -20) with valid PIN
        $response = $this->actingAs($this->userA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('inventory.stocks.adjust'), [
                'location_id'    => $loc->id,
                'product_id'     => $prod->id,
                'new_quantity'   => 10,
                'unit_cost'      => 10000,
                'reason_code'    => 'damaged',
                'supervisor_pin' => '654321',
            ]);

        $response->assertRedirect();

        $stock = InventoryStock::where('business_id', $this->businessA->id)
            ->where('location_id', $loc->id)
            ->where('product_id', $prod->id)
            ->first();

        $this->assertEquals(10, (float) $stock->quantity);
    }

    public function test_quick_adjust_rejects_other_reason_if_notes_less_than_10_characters(): void
    {
        Context::setBusiness($this->businessA);

        $loc = Location::create([
            'business_id' => $this->businessA->id,
            'name'        => 'Gudang Utama',
            'type'        => 'warehouse',
            'is_active'   => true,
        ]);

        $prod = Product::create([
            'business_id'    => $this->businessA->id,
            'name'           => 'Gula Pasir',
            'type'           => Product::TYPE_GOODS,
            'output_unit_id' => $this->unitA->id,
            'is_active'      => true,
        ]);

        $response = $this->actingAs($this->userA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('inventory.stocks.adjust'), [
                'location_id'  => $loc->id,
                'product_id'   => $prod->id,
                'new_quantity' => 15,
                'unit_cost'    => 10000,
                'reason_code'  => 'other',
                'notes'        => 'rusak', // < 10 chars!
            ]);

        $response->assertSessionHasErrors('notes');
    }

    public function test_quick_adjust_creates_balanced_auto_journal_for_negative_adjustment(): void
    {
        Context::setBusiness($this->businessA);

        $loc = Location::create([
            'business_id' => $this->businessA->id,
            'name'        => 'Gudang Utama',
            'type'        => 'warehouse',
            'is_active'   => true,
        ]);

        $prod = Product::create([
            'business_id'    => $this->businessA->id,
            'name'           => 'Minyak Goreng',
            'type'           => Product::TYPE_GOODS,
            'output_unit_id' => $this->unitA->id,
            'is_active'      => true,
        ]);

        InventoryStock::create([
            'business_id' => $this->businessA->id,
            'location_id' => $loc->id,
            'product_id'  => $prod->id,
            'quantity'    => 10,
            'last_cost'   => 20000,
        ]);

        // Adjust: 10 -> 6 (diff = -4, amount = 4 * 20.000 = Rp 80.000)
        $response = $this->actingAs($this->userA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('inventory.stocks.adjust'), [
                'location_id'  => $loc->id,
                'product_id'   => $prod->id,
                'new_quantity' => 6,
                'unit_cost'    => 20000,
                'reason_code'  => 'damaged',
                'notes'        => 'Kemasan bocor di rak gudang',
            ]);

        $response->assertRedirect();

        $journal = JournalEntry::where('business_id', $this->businessA->id)
            ->where('reference_type', JournalEntry::REF_STOCK_ADJUSTMENT)
            ->latest()
            ->first();

        $this->assertNotNull($journal);
        $this->assertEquals(80000, (float) $journal->total_debit);
        $this->assertEquals(80000, (float) $journal->total_credit);

        $lines = $journal->lines()->with('account')->get();
        $this->assertCount(2, $lines);

        $debitLine = $lines->firstWhere('type', 'debit');
        $creditLine = $lines->firstWhere('type', 'credit');

        // Debit: 6-6004 (Beban Kerugian Selisih Persediaan)
        $this->assertEquals('6-6004', $debitLine->account->code);
        // Credit: 1-1004 (Persediaan Barang Dagang)
        $this->assertEquals('1-1004', $creditLine->account->code);
    }

    public function test_quick_adjust_creates_balanced_auto_journal_for_positive_adjustment(): void
    {
        Context::setBusiness($this->businessA);

        $loc = Location::create([
            'business_id' => $this->businessA->id,
            'name'        => 'Gudang Utama',
            'type'        => 'warehouse',
            'is_active'   => true,
        ]);

        $prod = Product::create([
            'business_id'    => $this->businessA->id,
            'name'           => 'Kopi Arabika',
            'type'           => Product::TYPE_GOODS,
            'output_unit_id' => $this->unitA->id,
            'is_active'      => true,
        ]);

        InventoryStock::create([
            'business_id' => $this->businessA->id,
            'location_id' => $loc->id,
            'product_id'  => $prod->id,
            'quantity'    => 5,
            'last_cost'   => 15000,
        ]);

        // Adjust: 5 -> 10 (diff = +5, amount = 5 * 15.000 = Rp 75.000)
        $response = $this->actingAs($this->userA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('inventory.stocks.adjust'), [
                'location_id'  => $loc->id,
                'product_id'   => $prod->id,
                'new_quantity' => 10,
                'unit_cost'    => 15000,
                'reason_code'  => 'initial_balance',
                'notes'        => 'Saldo awal ditemukan',
            ]);

        $response->assertRedirect();

        $journal = JournalEntry::where('business_id', $this->businessA->id)
            ->where('reference_type', JournalEntry::REF_STOCK_ADJUSTMENT)
            ->latest()
            ->first();

        $this->assertNotNull($journal);
        $this->assertEquals(75000, (float) $journal->total_debit);
        $this->assertEquals(75000, (float) $journal->total_credit);

        $lines = $journal->lines()->with('account')->get();
        $this->assertCount(2, $lines);

        $debitLine = $lines->firstWhere('type', 'debit');
        $creditLine = $lines->firstWhere('type', 'credit');

        // Debit: 1-1004 (Persediaan Barang Dagang)
        $this->assertEquals('1-1004', $debitLine->account->code);
        // Credit: 7-7004 (Pendapatan Selisih Stok)
        $this->assertEquals('7-7004', $creditLine->account->code);
    }

    public function test_primary_location_cannot_be_deleted(): void
    {
        Context::setBusiness($this->businessA);

        $loc = Location::create([
            'business_id' => $this->businessA->id,
            'name'        => 'Gudang Pusat A',
            'type'        => 'warehouse',
            'is_primary'  => true,
            'is_active'   => true,
        ]);

        $response = $this->actingAs($this->userA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->delete(route('warehouse.destroy', $loc));

        $response->assertSessionHas('error');
        $this->assertNotNull(Location::find($loc->id));
    }

    public function test_location_with_stock_cannot_be_deleted(): void
    {
        Context::setBusiness($this->businessA);

        $loc = Location::create([
            'business_id' => $this->businessA->id,
            'name'        => 'Gudang Cabang Stok',
            'type'        => 'warehouse',
            'is_primary'  => false,
            'is_active'   => true,
        ]);

        $prod = Product::create([
            'business_id'    => $this->businessA->id,
            'name'           => 'Barang A',
            'type'           => Product::TYPE_GOODS,
            'output_unit_id' => $this->unitA->id,
            'is_active'      => true,
        ]);

        InventoryStock::create([
            'business_id' => $this->businessA->id,
            'location_id' => $loc->id,
            'product_id'  => $prod->id,
            'quantity'    => 10,
            'last_cost'   => 5000,
        ]);

        $response = $this->actingAs($this->userA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->delete(route('warehouse.destroy', $loc));

        $response->assertSessionHas('error', 'Gudang ini masih memiliki stok aktif. Kosongkan stok terlebih dahulu sebelum menghapus.');
        $this->assertNotNull(Location::find($loc->id));
    }

    public function test_location_without_history_is_hard_deleted(): void
    {
        Context::setBusiness($this->businessA);

        $loc = Location::create([
            'business_id' => $this->businessA->id,
            'name'        => 'Gudang Sementara Non-Historis',
            'type'        => 'warehouse',
            'is_primary'  => false,
            'is_active'   => true,
        ]);

        $response = $this->actingAs($this->userA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->delete(route('warehouse.destroy', $loc));

        $response->assertRedirect(route('warehouse.index'));
        $response->assertSessionHas('success', 'Gudang "Gudang Sementara Non-Historis" berhasil dihapus.');
        $this->assertNull(Location::find($loc->id));
    }

    public function test_location_with_transaction_history_is_gracefully_deactivated_not_deleted(): void
    {
        Context::setBusiness($this->businessA);

        $loc = Location::create([
            'business_id' => $this->businessA->id,
            'name'        => 'Gudang Bersejarah Transaksi',
            'type'        => 'warehouse',
            'is_primary'  => false,
            'is_active'   => true,
        ]);

        $prod = Product::create([
            'business_id'    => $this->businessA->id,
            'name'           => 'Barang Riwayat',
            'type'           => Product::TYPE_GOODS,
            'output_unit_id' => $this->unitA->id,
            'is_active'      => true,
        ]);

        // Catat mutasi historis dengan sisa stok 0
        StockMovement::create([
            'business_id'     => $this->businessA->id,
            'location_id'     => $loc->id,
            'product_id'      => $prod->id,
            'movement_type'   => StockMovement::TYPE_INITIAL,
            'quantity_change' => 0,
            'balance_after'   => 0,
            'unit_cost'       => 10000,
            'total_cost'      => 0,
        ]);

        $response = $this->actingAs($this->userA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->delete(route('warehouse.destroy', $loc));

        $response->assertRedirect(route('warehouse.index'));
        $response->assertSessionHas('success');
        $this->assertStringContainsString('memiliki riwayat transaksi masa lalu sehingga telah dinonaktifkan dengan aman', (string) session('success'));

        // Location must NOT be hard deleted from database
        $freshLoc = Location::find($loc->id);
        $this->assertNotNull($freshLoc);
        $this->assertFalse((bool) $freshLoc->is_active);
    }

    public function test_location_deactivation_records_high_risk_audit_log(): void
    {
        Context::setBusiness($this->businessA);

        $loc = Location::create([
            'business_id' => $this->businessA->id,
            'name'        => 'Gudang Transit Audit Log',
            'type'        => 'warehouse',
            'is_primary'  => false,
            'is_active'   => true,
        ]);

        $prod = Product::create([
            'business_id'    => $this->businessA->id,
            'name'           => 'Produk Jejak',
            'type'           => Product::TYPE_GOODS,
            'output_unit_id' => $this->unitA->id,
            'is_active'      => true,
        ]);

        StockMovement::create([
            'business_id'     => $this->businessA->id,
            'location_id'     => $loc->id,
            'product_id'      => $prod->id,
            'movement_type'   => StockMovement::TYPE_INITIAL,
            'quantity_change' => 0,
            'balance_after'   => 0,
            'unit_cost'       => 10000,
            'total_cost'      => 0,
        ]);

        $this->actingAs($this->userA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->delete(route('warehouse.destroy', $loc));

        $auditLog = AuditLog::where('business_id', $this->businessA->id)
            ->where('auditable_type', Location::class)
            ->where('auditable_id', $loc->id)
            ->where('action', 'updated')
            ->latest()
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals(AuditLog::RISK_HIGH, $auditLog->risk_level);
        $this->assertEquals('Penonaktifan Gudang/Lokasi Operasional', $auditLog->risk_reason);
    }

    public function test_high_loss_stock_adjustment_records_high_risk_audit_log(): void
    {
        Context::setBusiness($this->businessA);

        $loc = Location::create([
            'business_id' => $this->businessA->id,
            'name'        => 'Gudang Makanan Beku Audit',
            'type'        => 'warehouse',
            'is_active'   => true,
        ]);

        $this->actingAs($this->userA)
            ->withSession(['active_business_id' => $this->businessA->id]);

        $adjustment = StockAdjustment::create([
            'business_id'       => $this->businessA->id,
            'location_id'       => $loc->id,
            'adjustment_number' => 'ADJ-FRAUD-001',
            'adjustment_date'   => now(),
            'reason'            => 'Kerusakan massal barang',
            'status'            => 'approved',
            'total_loss_cost'   => 250000,
            'created_by'        => $this->userA->id,
        ]);

        $auditLog = AuditLog::where('business_id', $this->businessA->id)
            ->where('auditable_type', StockAdjustment::class)
            ->where('auditable_id', $adjustment->id)
            ->latest()
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals(AuditLog::RISK_HIGH, $auditLog->risk_level);
        $this->assertEquals('Penyesuaian Kerugian Stok Bernilai Tinggi', $auditLog->risk_reason);
    }

    public function test_context_aware_ui_hides_recipe_bom_materials_button_when_module_disabled(): void
    {
        Context::setBusiness($this->businessA);

        $this->businessA->update([
            'disabled_modules' => [ModuleRegistry::MODULE_RECIPE_BOM],
        ]);

        $response = $this->actingAs($this->userA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('warehouse.index'));

        $response->assertOk();
        $response->assertDontSee('Katalog Bahan');
    }

    public function test_context_aware_ui_shows_recipe_bom_materials_button_when_module_enabled(): void
    {
        Context::setBusiness($this->businessA);

        $this->businessA->update([
            'disabled_modules' => [],
        ]);

        $response = $this->actingAs($this->userA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('warehouse.index'));

        $response->assertOk();
        $response->assertSee('Katalog Bahan');
    }

    public function test_context_aware_ui_hides_storefront_shipping_button_when_module_disabled(): void
    {
        Context::setBusiness($this->businessA);

        $this->businessA->update([
            'disabled_modules' => [
                ModuleRegistry::MODULE_MERCHANT_SHIPPING,
                ModuleRegistry::MODULE_STOREFRONT_CHECKOUT,
            ],
        ]);

        $response = $this->actingAs($this->userA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('warehouse.index'));

        $response->assertOk();
        $response->assertDontSee('Pengiriman Storefront');
    }

    public function test_context_aware_ui_adapts_location_type_for_fnb_industry(): void
    {
        Context::setBusiness($this->businessA);

        $this->businessA->update([
            'template_code' => 'fnb_coffee_shop',
        ]);

        $response = $this->actingAs($this->userA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('warehouse.index'));

        $response->assertOk();
        $response->assertSee('Dapur Pusat (Central Kitchen)');
    }

    public function test_context_aware_ui_adapts_location_type_for_manufacturing_industry(): void
    {
        Context::setBusiness($this->businessA);

        $this->businessA->update([
            'template_code' => 'mfg_apparel',
        ]);

        $response = $this->actingAs($this->userA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('warehouse.index'));

        $response->assertOk();
        $response->assertSee('Pabrik / Workshop Produksi');
        $response->assertDontSee('Dapur Pusat (Central Kitchen)');
    }

    public function test_context_aware_ui_adapts_location_type_for_service_contractor_industry(): void
    {
        Context::setBusiness($this->businessA);

        $this->businessA->update([
            'template_code' => 'service_contractor',
        ]);

        $response = $this->actingAs($this->userA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('warehouse.index'));

        $response->assertOk();
        $response->assertSee('Basecamp / Workshop Proyek');
        $response->assertDontSee('Dapur Pusat (Central Kitchen)');
    }

    public function test_context_aware_ui_hides_central_kitchen_for_workshop_service(): void
    {
        Context::setBusiness($this->businessA);

        $this->businessA->update([
            'template_code' => 'service_workshop',
        ]);

        $response = $this->actingAs($this->userA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('warehouse.index'));

        $response->assertOk();
        $response->assertDontSee('Dapur Pusat (Central Kitchen)');
        $response->assertDontSee('Pabrik / Workshop Produksi');
        $response->assertDontSee('Basecamp / Workshop Proyek');
    }
}

