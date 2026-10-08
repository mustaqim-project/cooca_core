<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Domain\Material\MaterialCostService;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\MaterialPrice;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class InventoryManageMaterialTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'inventory_manage_material';
    }

    public function getDescription(): string
    {
        return 'Mengelola data bahan baku (raw materials) untuk resep/BOM produk COOCA: melihat daftar stok dan harga bahan, membuat bahan baru, dan memperbarui harga perolehan.';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:inventory:manage';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['list', 'create', 'update_price'],
                    'default' => 'list',
                    'description' => 'Aksi: list (daftar bahan baku), create (tambah bahan baru), update_price (perbarui harga beli bahan).',
                ],
                'search' => [
                    'type' => 'string',
                    'description' => 'Kata kunci pencarian nama atau kode bahan baku.',
                ],
                'material_id' => [
                    'type' => 'string',
                    'description' => 'UUID atau nama bahan baku (untuk update_price).',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Nama lengkap bahan baku (misal: "Biji Kopi Arabika Gayo", "Susu Full Cream Fresh").',
                ],
                'sku' => [
                    'type' => 'string',
                    'description' => 'Kode unik bahan baku atau barcode.',
                ],
                'unit_name' => [
                    'type' => 'string',
                    'default' => 'gram',
                    'description' => 'Satuan unit bahan baku (gram, kg, ml, liter, pcs, lembar, butir).',
                ],
                'purchase_price' => [
                    'type' => 'number',
                    'description' => 'Harga beli/perolehan bahan per satuan unit dalam Rupiah.',
                ],
                'category_name' => [
                    'type' => 'string',
                    'description' => 'Kategori bahan (misal: "Dairy & Milk", "Coffee Beans", "Packaging").',
                ],
                'supplier_name' => [
                    'type' => 'string',
                    'description' => 'Nama supplier atau vendor pemasok bahan.',
                ],
            ],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $action = trim((string) ($arguments['action'] ?? 'list'));

        return match ($action) {
            'create'        => $this->createMaterial($business, $user, $arguments),
            'update_price'  => $this->updatePrice($business, $user, $arguments),
            default         => $this->listMaterials($business, $arguments),
        };
    }

    private function listMaterials(Business $business, array $args): array
    {
        $query = Material::where('business_id', $business->id)
            ->whereNull('discontinued_at')
            ->with(['unit', 'category', 'supplier', 'latestPrice']);

        if (! empty($args['search'])) {
            $search = trim((string) $args['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $materials = $query->orderBy('name')->limit(50)->get();

        $data = $materials->map(function (Material $m) {
            $price = $m->latestPrice;
            $unitPrice = $price ? (float) $price->effective_cost : 0.0;

            return [
                'id' => $m->id,
                'name' => $m->name,
                'code' => $m->code,
                'category' => $m->category?->name ?? 'Uncategorized',
                'unit' => $m->unit?->name ?? 'unit',
                'supplier' => $m->supplier?->name ?? '-',
                'effective_price' => $unitPrice,
                'price_formatted' => 'Rp ' . number_format($unitPrice, 0, ',', '.') . ' / ' . ($m->unit?->name ?? 'unit'),
            ];
        });

        return [
            'status' => 'success',
            'total' => $materials->count(),
            'materials' => $data,
        ];
    }

    private function createMaterial(Business $business, ?User $user, array $args): array
    {
        $name = trim((string) ($args['name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('Nama bahan baku (name) tidak boleh kosong.');
        }

        $purchasePrice = (float) ($args['purchase_price'] ?? ($args['purchasing_price'] ?? ($args['price'] ?? 0.0)));
        if ($purchasePrice <= 0) {
            throw new InvalidArgumentException('Harga beli bahan baku (purchase_price) harus lebih dari 0.');
        }

        // Resolve Unit
        $unit = null;
        if (! empty($args['unit_id'])) {
            $unit = Unit::where('business_id', $business->id)->find($args['unit_id'])
                ?? Unit::whereNull('business_id')->find($args['unit_id']);
        }

        if (! $unit) {
            $unitName = ! empty($args['unit_name']) ? strtolower(trim((string) $args['unit_name'])) : 'gram';
            $unit = Unit::where('business_id', $business->id)->where('name', $unitName)->first()
                ?? Unit::whereNull('business_id')->where('name', $unitName)->first()
                ?? Unit::create([
                    'business_id' => $business->id,
                    'name' => $unitName,
                    'code' => strtoupper(substr($unitName, 0, 8)),
                    'category' => 'weight',
                    'is_base' => true,
                ]);
        }

        // Resolve Category
        $categoryId = null;
        if (! empty($args['category_name'])) {
            $catName = trim((string) $args['category_name']);
            $cat = MaterialCategory::firstOrCreate(
                ['business_id' => $business->id, 'name' => $catName],
                ['business_id' => $business->id, 'name' => $catName, 'slug' => Str::slug($catName)]
            );
            $categoryId = $cat->id;
        }

        // Resolve Supplier
        $supplierId = null;
        if (! empty($args['supplier_name'])) {
            $supName = trim((string) $args['supplier_name']);
            $sup = Supplier::firstOrCreate(
                ['business_id' => $business->id, 'name' => $supName],
                ['business_id' => $business->id, 'name' => $supName, 'is_active' => true]
            );
            $supplierId = $sup->id;
        }

        $sku = ! empty($args['sku']) ? trim((string) $args['sku']) : 'MAT-' . strtoupper(Str::random(6));

        $material = DB::transaction(function () use ($business, $user, $name, $sku, $categoryId, $supplierId, $unit, $purchasePrice) {
            $mat = Material::create([
                'business_id' => $business->id,
                'name' => $name,
                'code' => $sku,
                'category_id' => $categoryId,
                'supplier_id' => $supplierId,
                'unit_id' => $unit->id,
            ]);

            $costService = app(MaterialCostService::class);
            $price = MaterialPrice::create([
                'business_id' => $business->id,
                'material_id' => $mat->id,
                'supplier_id' => $supplierId,
                'purchase_price' => $purchasePrice,
                'shipping_cost' => 0.0,
                'handling_cost' => 0.0,
                'discount_amount' => 0.0,
                'yield_percentage' => 100.0,
                'waste_percentage' => 0.0,
                'purchase_unit_id' => $unit->id,
                'effective_cost' => $purchasePrice,
                'effective_date' => now()->toDateString(),
                'notes' => 'Didaftarkan otomatis via COOCA AI MCP',
            ]);

            $price->update([
                'effective_cost' => $costService->calculateEffectiveAcquisitionCost($price),
            ]);

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => $user?->id,
                'action' => 'mcp.material_create',
                'auditable_type' => Material::class,
                'auditable_id' => $mat->id,
                'new_values' => ['name' => $name, 'price' => $purchasePrice],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent() ?: 'COOCA-MCP-Agent',
            ]);

            return $mat;
        });

        return [
            'status' => 'success',
            'success' => true,
            'material_id' => $material->id,
            'name' => $material->name,
            'sku' => $material->code,
            'unit' => $unit->name,
            'purchase_price' => $purchasePrice,
            'material' => [
                'id' => $material->id,
                'name' => $material->name,
                'code' => $material->code,
            ],
            'message' => "Bahan baku '{$material->name}' (SKU: {$material->code}) berhasil disimpan dengan harga Rp " . number_format($purchasePrice, 0, ',', '.') . "/{$unit->name}.",
        ];
    }

    private function updatePrice(Business $business, ?User $user, array $args): array
    {
        $materialId = trim((string) ($args['material_id'] ?? ''));
        $newPrice = (float) ($args['purchase_price'] ?? 0.0);

        if ($materialId === '' || $newPrice <= 0) {
            throw new InvalidArgumentException('material_id dan purchase_price (> 0) wajib disertakan.');
        }

        $mat = Material::where('business_id', $business->id)
            ->where(function ($q) use ($materialId) {
                $q->where('id', $materialId)->orWhere('name', $materialId)->orWhere('code', $materialId);
            })
            ->first();

        if (! $mat) {
            throw new InvalidArgumentException("Bahan baku '{$materialId}' tidak ditemukan.");
        }

        $costService = app(MaterialCostService::class);
        $price = MaterialPrice::create([
            'business_id' => $business->id,
            'material_id' => $mat->id,
            'supplier_id' => $mat->supplier_id,
            'purchase_price' => $newPrice,
            'shipping_cost' => 0.0,
            'handling_cost' => 0.0,
            'discount_amount' => 0.0,
            'yield_percentage' => 100.0,
            'waste_percentage' => 0.0,
            'purchase_unit_id' => $mat->unit_id,
            'effective_cost' => $newPrice,
            'effective_date' => now()->toDateString(),
            'notes' => 'Update harga beli via COOCA AI MCP',
        ]);

        $effectiveCost = $costService->calculateEffectiveAcquisitionCost($price);
        $price->update(['effective_cost' => $effectiveCost]);

        return [
            'status' => 'success',
            'material_id' => $mat->id,
            'name' => $mat->name,
            'new_effective_cost' => $effectiveCost,
            'message' => "Harga beli bahan baku '{$mat->name}' berhasil diperbarui menjadi Rp " . number_format($effectiveCost, 0, ',', '.') . " per {$mat->unit?->name}.",
        ];
    }
}
