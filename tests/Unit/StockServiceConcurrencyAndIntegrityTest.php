<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Inventory\StockService;
use App\Models\Business;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceConcurrencyAndIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_stock_movement_atomically_with_balance(): void
    {
        $business = Business::create([
            'name' => 'Test Business',
            'slug' => 'test-business',
            'currency' => 'IDR',
            'allow_negative_stock' => false,
        ]);

        $location = Location::create([
            'business_id' => $business->id,
            'name' => 'Gudang Utama',
            'slug' => 'gudang-utama',
            'type' => 'warehouse',
        ]);

        $unit = Unit::create([
            'business_id' => $business->id,
            'code' => 'pcs',
            'name' => 'Pcs',
            'category' => 'quantity',
        ]);

        $product = Product::create([
            'business_id' => $business->id,
            'name' => 'Kopi Robusta 250g',
            'code' => 'KOP-001',
            'output_unit_id' => $unit->id,
            'base_cost' => 15000,
            'selling_price' => 30000,
        ]);

        $service = new StockService();

        // 1. Initial In
        $in = $service->recordMovement(
            businessId: $business->id,
            locationId: $location->id,
            productId: $product->id,
            movementType: StockMovement::TYPE_INITIAL,
            quantityChange: 10.0,
            unitCost: 15000.0,
            referenceNumber: 'INIT-01'
        );

        $this->assertEquals(10.0, $in->balance_after);
        $stock = $service->getOrCreateStock($business->id, $location->id, $product->id);
        $this->assertEquals(10.0, (float) $stock->quantity);

        // 2. Invoice Sale Deduction
        $sale = $service->deductForInvoiceSale(
            businessId: $business->id,
            locationId: $location->id,
            productId: $product->id,
            quantity: 3.0,
            unitCost: 15000.0,
            invoiceId: 'inv-123',
            invoiceNumber: 'INV-202609-0001'
        );

        $this->assertEquals(7.0, $sale->balance_after);
        $this->assertEquals(StockMovement::TYPE_INVOICE_SALE, $sale->movement_type);

        // 3. Prevent Negative Stock when allow_negative_stock is false
        $this->expectException(InsufficientStockException::class);
        $service->deductForInvoiceSale(
            businessId: $business->id,
            locationId: $location->id,
            productId: $product->id,
            quantity: 8.0, // only 7 left
            unitCost: 15000.0,
            invoiceId: 'inv-124',
            invoiceNumber: 'INV-202609-0002'
        );
    }
}
