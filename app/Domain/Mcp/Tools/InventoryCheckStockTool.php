<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\Business;
use App\Models\Product;
use App\Models\User;

final class InventoryCheckStockTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'inventory_check_stock';
    }

    public function getDescription(): string
    {
        return 'Memeriksa jumlah stok produk tertentu atau mendapatkan daftar barang kritis yang stoknya menipis (low-stock warning).';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:products:read';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'search_query' => [
                    'type' => 'string',
                    'description' => 'Optional kata kunci pencarian nama produk atau SKU.',
                ],
                'only_low_stock' => [
                    'type' => 'boolean',
                    'default' => false,
                    'description' => 'Jika true, hanya tampilkan produk yang stoknya di bawah batas minimum (reorder point).',
                ],
            ],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $query = Product::where('business_id', $business->id)
            ->where('is_active', true)
            ->with(['stocks']);

        if (! empty($arguments['search_query'])) {
            $search = '%' . trim((string) $arguments['search_query']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('code', 'like', $search);
            });
        }

        $products = $query->take(50)->get();

        $items = [];
        $lowStockCount = 0;

        foreach ($products as $prod) {
            $currentStock = (float) $prod->stocks->sum('quantity');
            $minStock = (float) ($prod->min_stock ?? 5);
            $isLowStock = $currentStock <= $minStock;

            if ($isLowStock) {
                $lowStockCount++;
            }

            if (! empty($arguments['only_low_stock']) && ! $isLowStock) {
                continue;
            }

            $status = $currentStock <= 0 ? 'HABIS' : ($isLowStock ? 'KRITIS' : 'AMAN');

            $items[] = [
                'id' => $prod->id,
                'name' => $prod->name,
                'sku' => $prod->code,
                'current_stock' => $currentStock,
                'min_stock' => $minStock,
                'status' => $status,
                'selling_price' => (float) $prod->selling_price,
            ];
        }

        return [
            'status' => 'success',
            'total_found' => count($items),
            'low_stock_alerts_count' => $lowStockCount,
            'items' => $items,
        ];
    }
}
