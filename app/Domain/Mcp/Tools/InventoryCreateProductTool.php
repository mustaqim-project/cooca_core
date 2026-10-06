<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\AuditLog;
use App\Models\Business;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class InventoryCreateProductTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'inventory_create_product';
    }

    public function getDescription(): string
    {
        return 'Membuat master produk baru di katalog COOCA, menetapkan barcode/SKU, HPP (modal), harga jual, dan stok awal.';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:products:manage';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'name' => [
                    'type' => 'string',
                    'description' => 'Nama produk lengkap.',
                ],
                'sku' => [
                    'type' => 'string',
                    'description' => 'Optional SKU/Barcode produk. Jika kosong, sistem COOCA membuatkan SKU otomatis.',
                ],
                'category_name' => [
                    'type' => 'string',
                    'description' => 'Nama kategori produk (misal: Makanan, Minuman, Suku Cadang, Layanan).',
                ],
                'cost_price' => [
                    'type' => 'number',
                    'minimum' => 0,
                    'description' => 'Harga modal beli/HPP per unit dalam Rupiah.',
                ],
                'selling_price' => [
                    'type' => 'number',
                    'minimum' => 0,
                    'description' => 'Harga jual resmi ke konsumen per unit dalam Rupiah.',
                ],
                'initial_stock' => [
                    'type' => 'number',
                    'default' => 0,
                    'description' => 'Jumlah stok awal yang tersedia saat produk dibuat.',
                ],
                'unit_name' => [
                    'type' => 'string',
                    'default' => 'pcs',
                    'description' => 'Satuan unit (pcs, cup, porsi, botol, box, kg).',
                ],
            ],
            'required' => ['name', 'cost_price', 'selling_price'],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $name = trim((string) ($arguments['name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('Nama produk tidak boleh kosong.');
        }

        $costPrice = (float) ($arguments['cost_price'] ?? 0);
        $sellingPrice = (float) ($arguments['selling_price'] ?? 0);
        $initialStock = max(0.0, (float) ($arguments['initial_stock'] ?? 0));
        $sku = ! empty($arguments['sku']) ? trim((string) $arguments['sku']) : 'PRD-' . strtoupper(Str::random(6));

        // Resolve or create Category
        $categoryId = null;
        if (! empty($arguments['category_name'])) {
            $catName = trim((string) $arguments['category_name']);
            $cat = ProductCategory::firstOrCreate(
                ['business_id' => $business->id, 'name' => $catName],
                ['business_id' => $business->id, 'name' => $catName, 'slug' => Str::slug($catName)]
            );
            $categoryId = $cat->id;
        }

        // Resolve or create Unit
        $unitName = ! empty($arguments['unit_name']) ? strtolower(trim((string) $arguments['unit_name'])) : 'pcs';
        $unit = Unit::where('business_id', $business->id)->where('name', $unitName)->first()
            ?? Unit::whereNull('business_id')->where('name', $unitName)->first()
            ?? Unit::firstOrCreate(
                ['business_id' => $business->id, 'name' => $unitName],
                [
                    'code'              => strtoupper(substr($unitName, 0, 10)),
                    'category'          => 'quantity',
                    'is_base'           => true,
                    'default_precision' => 0,
                ]
            );

        $product = DB::transaction(function () use ($business, $user, $name, $sku, $categoryId, $costPrice, $sellingPrice, $unit, $initialStock) {
            $prod = Product::create([
                'business_id' => $business->id,
                'name' => $name,
                'code' => $sku,
                'category_id' => $categoryId,
                'base_cost' => $costPrice,
                'selling_price' => $sellingPrice,
                'output_unit_id' => $unit?->id,
                'type' => Product::TYPE_GOODS,
                'is_active' => true,
                'show_in_pos' => true,
                'show_in_website' => true,
            ]);

            if ($initialStock > 0) {
                $location = Location::where('business_id', $business->id)->first();
                if ($location !== null) {
                    InventoryStock::create([
                        'business_id' => $business->id,
                        'location_id' => $location->id,
                        'product_id' => $prod->id,
                        'quantity' => $initialStock,
                        'reserved_quantity' => 0,
                        'last_cost' => $costPrice,
                        'avg_purchase_cost' => $costPrice,
                    ]);
                }
            }

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => $user?->id,
                'action' => 'mcp.inventory_create_product',
                'auditable_type' => Product::class,
                'auditable_id' => $prod->id,
                'old_values' => null,
                'new_values' => [
                    'name' => $prod->name,
                    'code' => $prod->code,
                    'cost_price' => $costPrice,
                    'selling_price' => $sellingPrice,
                    'initial_stock' => $initialStock,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent() ?: 'COOCA-MCP-Agent',
            ]);

            return $prod;
        });

        $marginPercent = $sellingPrice > 0 ? round((($sellingPrice - $costPrice) / $sellingPrice) * 100, 1) : 0;

        return [
            'status' => 'success',
            'product_id' => $product->id,
            'name' => $product->name,
            'sku' => $product->code,
            'cost_price' => $costPrice,
            'selling_price' => $sellingPrice,
            'margin_percent' => "{$marginPercent}%",
            'initial_stock' => $initialStock,
            'message' => "Produk '{$product->name}' (SKU: {$product->code}) berhasil ditambahkan ke katalog COOCA dengan harga jual Rp " . number_format($sellingPrice, 0, ',', '.') . " dan stok awal {$initialStock}.",
        ];
    }
}
