<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\AuditLog;
use App\Models\BomHeader;
use App\Models\BomItem;
use App\Models\Business;
use App\Models\CostModel;
use App\Models\Material;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class InventoryManageBomTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'inventory_manage_bom';
    }

    public function getDescription(): string
    {
        return 'Mengelola formula resep (Bill of Materials / BOM) produk: melihat bahan penyusun produk, menetapkan takaran resep, dan menghitung total modal HPP produk secara otomatis.';
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
                'action' => [
                    'type' => 'string',
                    'enum' => ['get', 'set_recipe'],
                    'default' => 'get',
                    'description' => 'Aksi: get (lihat resep bahan produk saat ini), set_recipe (atur/perbarui daftar bahan resep produk).',
                ],
                'product_id' => [
                    'type' => 'string',
                    'description' => 'UUID atau nama lengkap produk.',
                ],
                'items' => [
                    'type' => 'array',
                    'description' => 'Daftar takaran bahan baku resep (wajib untuk set_recipe).',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'material_id' => [
                                'type' => 'string',
                                'description' => 'UUID atau nama bahan baku.',
                            ],
                            'quantity' => [
                                'type' => 'number',
                                'description' => 'Jumlah takaran yang digunakan per porsi/unit produk.',
                            ],
                            'waste_percentage' => [
                                'type' => 'number',
                                'default' => 0,
                                'description' => 'Persentase limbah/waste pembuangan (misal: 5 untuk 5%).',
                            ],
                        ],
                        'required' => ['material_id', 'quantity'],
                    ],
                ],
            ],
            'required' => ['product_id'],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $productId = trim((string) ($arguments['product_id'] ?? ''));
        if ($productId === '') {
            throw new InvalidArgumentException('product_id tidak boleh kosong.');
        }

        $product = Product::where('business_id', $business->id)
            ->where(function ($q) use ($productId) {
                $q->where('id', $productId)
                    ->orWhere('code', $productId)
                    ->orWhere('name', $productId);
            })
            ->first();

        if (! $product) {
            throw new InvalidArgumentException("Produk '{$productId}' tidak ditemukan dalam katalog bisnis.");
        }

        $action = trim((string) ($arguments['action'] ?? 'get'));

        return match ($action) {
            'set_recipe' => $this->setRecipe($business, $user, $product, $arguments),
            default      => $this->getRecipe($business, $product),
        };
    }

    private function getRecipe(Business $business, Product $product): array
    {
        $costModel = CostModel::where('business_id', $business->id)
            ->where('product_id', $product->id)
            ->first();

        if (! $costModel) {
            return [
                'status' => 'success',
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'selling_price' => (float) $product->selling_price,
                    'base_cost' => (float) $product->base_cost,
                ],
                'has_recipe' => false,
                'recipe_items' => [],
                'total_recipe_cost' => (float) $product->base_cost,
                'message' => "Produk '{$product->name}' belum memiliki formula resep BOM.",
            ];
        }

        $bomHeader = BomHeader::where('cost_model_id', $costModel->id)->first();
        if (! $bomHeader) {
            return [
                'status' => 'success',
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'selling_price' => (float) $product->selling_price,
                    'base_cost' => (float) $product->base_cost,
                ],
                'has_recipe' => false,
                'recipe_items' => [],
                'total_recipe_cost' => (float) $product->base_cost,
                'message' => "Produk '{$product->name}' belum memiliki rincian bahan BOM.",
            ];
        }

        $items = BomItem::where('bom_header_id', $bomHeader->id)
            ->with(['material.latestPrice', 'unit'])
            ->get();

        $totalHpp = 0.0;
        $formattedItems = $items->map(function (BomItem $item) use (&$totalHpp) {
            $mat = $item->material;
            $unitCost = $mat?->latestPrice ? (float) ($mat->latestPrice->purchase_price ?? $mat->latestPrice->effective_cost ?? 0.0) : 0.0;
            $qty = (float) $item->quantity;
            $waste = (float) $item->waste_percentage;
            $grossQty = $qty * (1 + ($waste / 100));
            $subtotalCost = $grossQty * $unitCost;
            $totalHpp += $subtotalCost;

            return [
                'material_id' => $mat?->id,
                'material_name' => $mat?->name ?? 'Unknown',
                'net_quantity' => $qty,
                'waste_percentage' => $waste,
                'unit' => $item->unit?->name ?? $mat?->unit?->name ?? 'unit',
                'unit_cost' => $unitCost,
                'subtotal_cost' => round($subtotalCost, 2),
                'subtotal_cost_formatted' => 'Rp ' . number_format($subtotalCost, 0, ',', '.'),
            ];
        });

        return [
            'status' => 'success',
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'selling_price' => (float) $product->selling_price,
                'current_base_cost' => (float) $product->base_cost,
            ],
            'has_recipe' => true,
            'total_recipe_cost' => round($totalHpp, 2),
            'total_recipe_cost_formatted' => 'Rp ' . number_format($totalHpp, 0, ',', '.'),
            'recipe_items' => $formattedItems,
        ];
    }

    private function setRecipe(Business $business, ?User $user, Product $product, array $args): array
    {
        $inputItems = (array) ($args['items'] ?? []);
        if (empty($inputItems)) {
            throw new InvalidArgumentException('items resep tidak boleh kosong.');
        }

        $result = DB::transaction(function () use ($business, $user, $product, $inputItems) {
            $costModel = CostModel::firstOrCreate(
                ['business_id' => $business->id, 'product_id' => $product->id],
                [
                    'name' => "Model HPP - {$product->name}",
                    'method' => CostModel::METHOD_RECIPE_BOM,
                ]
            );

            $bomHeader = BomHeader::firstOrCreate(
                ['cost_model_id' => $costModel->id],
                [
                    'name' => "Resep BOM {$product->name}",
                    'type' => BomHeader::TYPE_RECIPE,
                    'level' => 1,
                ]
            );

            // Delete old items and insert fresh
            BomItem::where('bom_header_id', $bomHeader->id)->delete();

            $totalCalculatedHpp = 0.0;
            $savedItems = [];

            foreach ($inputItems as $idx => $raw) {
                $matRef = trim((string) ($raw['material_id'] ?? ''));
                $qty = (float) ($raw['quantity'] ?? 0.0);
                $waste = (float) ($raw['waste_percentage'] ?? 0.0);

                if ($matRef === '' || $qty <= 0) {
                    continue;
                }

                $mat = Material::where('business_id', $business->id)
                    ->where(function ($q) use ($matRef) {
                        $q->where('id', $matRef)
                            ->orWhere('code', $matRef)
                            ->orWhere('name', $matRef);
                    })
                    ->first();

                if (! $mat) {
                    continue;
                }

                $bomItem = BomItem::create([
                    'bom_header_id' => $bomHeader->id,
                    'material_id' => $mat->id,
                    'quantity' => $qty,
                    'unit_id' => $mat->unit_id,
                    'waste_percentage' => $waste,
                    'sort_order' => $idx + 1,
                ]);

                $unitCost = $mat->latestPrice ? (float) ($mat->latestPrice->purchase_price ?? $mat->latestPrice->effective_cost ?? 0.0) : 0.0;
                $grossQty = $qty * (1 + ($waste / 100));
                $subtotalCost = $grossQty * $unitCost;
                $totalCalculatedHpp += $subtotalCost;

                $savedItems[] = [
                    'material_name' => $mat->name,
                    'quantity' => $qty,
                    'unit' => $mat->unit?->name ?? 'unit',
                    'subtotal_cost' => round($subtotalCost, 2),
                ];
            }

            // Automatically update Product base_cost to reflect calculated HPP!
            $finalHpp = round($totalCalculatedHpp, 2);
            $product->update([
                'base_cost' => $finalHpp,
            ]);

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => $user?->id,
                'action' => 'mcp.bom_set_recipe',
                'auditable_type' => Product::class,
                'auditable_id' => $product->id,
                'new_values' => ['base_cost' => $finalHpp, 'items_count' => count($savedItems)],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent() ?: 'COOCA-MCP-Agent',
            ]);

            return [
                'final_hpp' => $finalHpp,
                'items' => $savedItems,
            ];
        });

        return [
            'status' => 'success',
            'success' => true,
            'product_name' => $product->name,
            'calculated_hpp' => $result['final_hpp'],
            'total_calculated_hpp' => $result['final_hpp'],
            'selling_price' => (float) $product->selling_price,
            'margin_nominal' => (float) ($product->selling_price - $result['final_hpp']),
            'items_count' => count($result['items']),
            'items' => $result['items'],
            'message' => "Formula resep BOM untuk '{$product->name}' berhasil diperbarui. HPP modal otomatis disesuaikan menjadi Rp " . number_format($result['final_hpp'], 0, ',', '.') . ".",
        ];
    }
}
