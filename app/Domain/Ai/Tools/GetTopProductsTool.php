<?php

declare(strict_types=1);

namespace App\Domain\Ai\Tools;

use App\Models\Business;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\User;

final class GetTopProductsTool extends BaseAiTool
{
    public function getName(): string
    {
        return 'GetTopProducts';
    }

    public function getDescription(): string
    {
        return 'Mengambil daftar produk paling laris (top volume & top omzet) dan produk dengan performa penjualan terendah.';
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $topItems = PosOrderItem::whereHas('order', fn($q) => $q->where('business_id', $business->id)->where('status', PosOrder::STATUS_COMPLETED))
            ->selectRaw('product_name, SUM(quantity) as total_qty, SUM(total_price) as total_revenue, SUM(total_price - COALESCE(total_hpp, 0)) as total_profit')
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->take(10)
            ->get();

        return [
            'top_products' => $topItems->map(fn($item) => [
                'name' => $item->product_name,
                'quantity_sold' => (float) $item->total_qty,
                'total_revenue' => (float) $item->total_revenue,
                'total_profit' => (float) $item->total_profit,
            ])->toArray(),
        ];
    }
}
