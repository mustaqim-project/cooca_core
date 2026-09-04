<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Import\DataImportService;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Inventory\StockService;
use App\Models\BomHeader;
use App\Models\Business;
use App\Models\CostModel;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Material;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class MaterialMasterStockBusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Location $location;
    private Unit $unitPcs;
    private Unit $unitGram;
    private Unit $unitMl;
    private StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();

        \App\Support\Context::flush();

        $this->business = Business::create([
            'name' => 'Coffee & Bakery Lab',
            'slug' => 'coffee-bakery-lab',
            'email' => 'owner@coffeelab.test',
            'phone' => '081234567890',
            'is_active' => true,
            'allow_negative_stock' => false,
        ]);

        \App\Support\Context::setBusiness($this->business);

        $this->unitPcs = Unit::firstOrCreate(['business_id' => $this->business->id, 'code' => 'pcs'], ['name' => 'Pcs', 'category' => 'quantity']);
        $this->unitGram = Unit::firstOrCreate(['business_id' => $this->business->id, 'code' => 'gram'], ['name' => 'Gram', 'category' => 'weight']);
        $this->unitMl = Unit::firstOrCreate(['business_id' => $this->business->id, 'code' => 'ml'], ['name' => 'Mililiter', 'category' => 'volume']);

        $this->user = User::factory()->create([
            'email' => 'staff@coffeelab.test',
        ]);
        $this->user->businesses()->attach($this->business->id, [
            'role' => 'owner',
            'is_owner' => true,
        ]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Utama',
            'is_active' => true,
        ]);

        $this->stockService = new StockService();
    }

    protected function tearDown(): void
    {
        \App\Support\Context::flush();
        parent::tearDown();
    }

    /**
     * Rule 01 & Rule 13: Direct Material product sales deduct constituent Material stock.
     */
    public function test_direct_material_product_deducts_material_master_stock(): void
    {
        // 1. Create Master Material
        $materialPan = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Panci Teflon 24cm',
            'code' => 'MAT-PAN-01',
            'unit_id' => $this->unitPcs->id,
        ]);

        // 2. Initial Stock on Material: 20 pcs
        $this->stockService->recordMovement(
            businessId: $this->business->id,
            locationId: $this->location->id,
            movementType: StockMovement::TYPE_INITIAL,
            quantityChange: 20,
            unitCost: 75000,
            referenceId: null,
            referenceNumber: 'INIT-01',
            notes: 'Saldo Awal Panci',
            userId: $this->user->id,
            materialId: $materialPan->id
        );

        // Verify stock is held in InventoryStock with material_id
        $invStock = InventoryStock::where('business_id', $this->business->id)
            ->where('material_id', $materialPan->id)
            ->where('location_id', $this->location->id)
            ->first();

        $this->assertNotNull($invStock);
        $this->assertEquals(20.0, (float) $invStock->quantity);

        // 3. Create Product with Direct Material Link
        $productPan = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Panci Teflon Super 24cm',
            'code' => 'PRD-PAN-01',
            'selling_price' => 120000,
            'direct_material_id' => $materialPan->id,
            'output_unit_id' => $this->unitPcs->id,
            'is_active' => true,
        ]);

        // 4. Sell 3 units of ProductPan
        $this->stockService->deductForProductSale(
            businessId: $this->business->id,
            locationId: $this->location->id,
            product: $productPan,
            productQuantity: 3,
            unitCost: 75000,
            orderId: 'pos-trx-001',
            orderNumber: 'TRX-POS-001',
            userId: $this->user->id
        );

        // 5. Verify Material stock reduced by 3 (from 20 to 17)
        $invStock->refresh();
        $this->assertEquals(17.0, (float) $invStock->quantity);

        // Verify movement was recorded on material_id
        $movement = StockMovement::where('business_id', $this->business->id)
            ->where('material_id', $materialPan->id)
            ->where('movement_type', StockMovement::TYPE_POS_SALE)
            ->latest('id')
            ->first();

        $this->assertNotNull($movement);
        $this->assertEquals(-3.0, (float) $movement->quantity_change);
    }

    /**
     * Rule 02 & Rule 14: Recipe / BOM Multi-Material deducts each constituent material proportionally.
     */
    public function test_recipe_bom_multi_material_deducts_all_constituent_materials(): void
    {
        // 1. Create Materials
        $coffeeBean = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Biji Kopi Robusta Blend',
            'code' => 'MAT-KOP-01',
            'unit_id' => $this->unitGram->id,
        ]);
        $freshMilk = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Susu UHT Fresh',
            'code' => 'MAT-MLK-01',
            'unit_id' => $this->unitMl->id,
        ]);
        $cupPlastik = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Cup Plastik 14oz',
            'code' => 'MAT-CUP-01',
            'unit_id' => $this->unitPcs->id,
        ]);

        // Initial Stocks: 1000g coffee, 5000ml milk, 100 pcs cup
        $this->stockService->recordMovement(
            businessId: $this->business->id,
            locationId: $this->location->id,
            movementType: StockMovement::TYPE_INITIAL,
            quantityChange: 1000,
            unitCost: 150,
            referenceId: null,
            referenceNumber: 'INIT-KOP',
            notes: 'Stok awal kopi',
            userId: $this->user->id,
            materialId: $coffeeBean->id
        );
        $this->stockService->recordMovement(
            businessId: $this->business->id,
            locationId: $this->location->id,
            movementType: StockMovement::TYPE_INITIAL,
            quantityChange: 5000,
            unitCost: 20,
            referenceId: null,
            referenceNumber: 'INIT-MLK',
            notes: 'Stok awal susu',
            userId: $this->user->id,
            materialId: $freshMilk->id
        );
        $this->stockService->recordMovement(
            businessId: $this->business->id,
            locationId: $this->location->id,
            movementType: StockMovement::TYPE_INITIAL,
            quantityChange: 100,
            unitCost: 650,
            referenceId: null,
            referenceNumber: 'INIT-CUP',
            notes: 'Stok awal cup',
            userId: $this->user->id,
            materialId: $cupPlastik->id
        );

        // 2. Create Product Kopi Susu with Recipe (18g coffee, 120ml milk, 1 cup)
        $productKopi = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Kopi Susu Gula Aren',
            'code' => 'KOP-AREN',
            'selling_price' => 18000,
            'output_unit_id' => $this->unitPcs->id,
            'is_active' => true,
        ]);

        $costModel = $productKopi->costModels()->create([
            'business_id' => $this->business->id,
            'name' => 'Model HPP Kopi Susu',
            'method' => CostModel::METHOD_RECIPE_BOM,
            'is_active' => true,
        ]);
        $bomHeader = $costModel->bomHeaders()->create([
            'type' => BomHeader::TYPE_RECIPE,
            'name' => 'Resep Kopi Susu',
            'level' => 1,
        ]);
        $bomHeader->items()->createMany([
            ['material_id' => $coffeeBean->id, 'quantity' => 18, 'unit_id' => $this->unitGram->id, 'waste_percentage' => 0, 'is_mandatory' => true],
            ['material_id' => $freshMilk->id, 'quantity' => 120, 'unit_id' => $this->unitMl->id, 'waste_percentage' => 0, 'is_mandatory' => true],
            ['material_id' => $cupPlastik->id, 'quantity' => 1, 'unit_id' => $this->unitPcs->id, 'waste_percentage' => 0, 'is_mandatory' => true],
        ]);

        // 3. Sell 5 cups of Kopi Susu
        $this->stockService->deductForProductSale(
            businessId: $this->business->id,
            locationId: $this->location->id,
            product: $productKopi,
            productQuantity: 5,
            unitCost: 7500,
            orderId: 'pos-trx-002',
            orderNumber: 'TRX-POS-002',
            userId: $this->user->id
        );

        // 4. Verify deductions:
        // Coffee: 1000 - (5 * 18) = 910
        // Milk: 5000 - (5 * 120) = 4400
        // Cup: 100 - (5 * 1) = 95
        $coffeeStock = InventoryStock::where('material_id', $coffeeBean->id)->where('location_id', $this->location->id)->first();
        $milkStock = InventoryStock::where('material_id', $freshMilk->id)->where('location_id', $this->location->id)->first();
        $cupStock = InventoryStock::where('material_id', $cupPlastik->id)->where('location_id', $this->location->id)->first();

        $this->assertEquals(910.0, (float) $coffeeStock->quantity);
        $this->assertEquals(4400.0, (float) $milkStock->quantity);
        $this->assertEquals(95.0, (float) $cupStock->quantity);
    }

    /**
     * Rule 17: Block negative stock when allow_negative_stock is false.
     */
    public function test_blocks_sale_when_stock_is_insufficient_and_negative_stock_disallowed(): void
    {
        $this->business->update(['allow_negative_stock' => false]);

        $material = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Sirup Karamel',
            'code' => 'MAT-SRP-01',
            'unit_id' => $this->unitMl->id,
        ]);

        // Initial stock only 50ml
        $this->stockService->recordMovement(
            businessId: $this->business->id,
            locationId: $this->location->id,
            movementType: StockMovement::TYPE_INITIAL,
            quantityChange: 50,
            unitCost: 100,
            userId: $this->user->id,
            materialId: $material->id
        );

        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Karamel Macchiato',
            'direct_material_id' => $material->id,
            'output_unit_id' => $this->unitPcs->id,
            'is_active' => true,
        ]);

        // Attempting to deduct 100 units should throw InsufficientStockException
        $this->expectException(InsufficientStockException::class);

        $this->stockService->deductForProductSale(
            businessId: $this->business->id,
            locationId: $this->location->id,
            product: $product,
            productQuantity: 100,
            unitCost: 5000,
            orderId: 'pos-trx-fail',
            orderNumber: 'TRX-FAIL',
            userId: $this->user->id
        );
    }

    /**
     * Rule 18, 19, 20: Stock Opname on Material records audited StockMovement.
     */
    public function test_stock_opname_reconciles_material_stock_and_creates_audited_movement(): void
    {
        $material = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Biji Kopi Arabika',
            'code' => 'MAT-KOP-ARB',
            'unit_id' => $this->unitGram->id,
        ]);

        // System stock = 500g
        $this->stockService->recordMovement(
            businessId: $this->business->id,
            locationId: $this->location->id,
            movementType: StockMovement::TYPE_INITIAL,
            quantityChange: 500,
            unitCost: 250,
            userId: $this->user->id,
            materialId: $material->id
        );

        // Physical count = 480g (variance = -20g)
        $opname = StockOpname::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'opname_number' => 'OPN-TEST-001',
            'opname_date' => now(),
            'status' => StockOpname::STATUS_IN_PROGRESS,
            'conducted_by' => $this->user->id,
        ]);

        $opname->items()->create([
            'material_id' => $material->id,
            'system_quantity' => 500,
            'physical_quantity' => 480,
            'difference_quantity' => -20,
            'unit_cost' => 250,
            'total_difference_cost' => -5000,
        ]);

        // Reconcile
        $this->stockService->reconcileStockOpname($opname, $this->user);

        // Verify stock updated to 480
        $stock = InventoryStock::where('material_id', $material->id)->where('location_id', $this->location->id)->first();
        $this->assertEquals(480.0, (float) $stock->quantity);

        // Verify StockMovement recorded
        $movement = StockMovement::where('material_id', $material->id)
            ->where('movement_type', StockMovement::TYPE_OPNAME)
            ->first();

        $this->assertNotNull($movement);
        $this->assertEquals(-20.0, (float) $movement->quantity_change);
        $this->assertEquals($opname->id, $movement->reference_id);
    }

    /**
     * Rule 04 & 07: Inventory import rejects missing Material master data (No auto-creation).
     */
    public function test_inventory_import_rejects_missing_material_master_data(): void
    {
        $importService = new DataImportService($this->stockService);

        // Create a temporary spreadsheet with non-existent material
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Nama Bahan Baku *');
        $sheet->setCellValue('B1', 'Kode / SKU Bahan');
        $sheet->setCellValue('C1', 'Lokasi / Outlet / Gudang *');
        $sheet->setCellValue('D1', 'Jumlah Stok *');
        $sheet->setCellValue('E1', 'Satuan *');

        $sheet->setCellValue('A2', 'Bahan Siluman Tidak Terdaftar');
        $sheet->setCellValue('B2', 'MAT-GHOST');
        $sheet->setCellValue('C2', 'Outlet Utama');
        $sheet->setCellValue('D2', 50);
        $sheet->setCellValue('E2', 'pcs');

        $tempPath = tempnam(sys_get_temp_dir(), 'test_inv_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $uploadedFile = new UploadedFile($tempPath, 'test_inv.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $preview = $importService->parseAndPreviewInventory($this->business, $uploadedFile);

        // Must flag as error because material does not exist in Master Data
        $this->assertEquals(1, $preview['error_count']);
        $this->assertEquals('error', $preview['rows'][0]['status']);
        $this->assertStringContainsString('belum terdaftar di Master Data', $preview['rows'][0]['status_reason']);

        // When executing with this error row, it must be skipped and NO material/product created
        $result = $importService->executeInventoryImport($this->business, $preview['rows']);
        $this->assertEquals(0, $result['imported']);
        $this->assertEquals(1, $result['skipped']);

        $this->assertDatabaseMissing('materials', ['name' => 'Bahan Siluman Tidak Terdaftar']);
        $this->assertDatabaseMissing('products', ['name' => 'Bahan Siluman Tidak Terdaftar']);

        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }
}
