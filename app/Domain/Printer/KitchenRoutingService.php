<?php

declare(strict_types=1);

namespace App\Domain\Printer;

use App\Models\Business;
use App\Models\PosOrder;
use App\Models\PosPrinter;
use Illuminate\Support\Collection;

class KitchenRoutingService
{
    /**
     * Resolve and group order items by target destination printers.
     *
     * @return array<string, array{
     *     printer: PosPrinter,
     *     station_name: string,
     *     items: array<int, mixed>
     * }>
     */
    public function routeOrderItems(PosOrder $order, Business|string $business, ?string $locationId = null): array
    {
        $order->loadMissing(['items.product.category', 'items.modifiers']);
        $businessId = $business instanceof Business ? $business->id : (string) $business;

        // Find active kitchen/bar printers for this outlet
        $printers = PosPrinter::where('business_id', $businessId)
            ->active()
            ->forLocation($locationId ?? $order->location_id)
            ->where(function ($q) {
                $q->whereJsonContains('assigned_usages', PosPrinter::USAGE_KITCHEN_ORDER)
                  ->orWhereJsonContains('assigned_usages', PosPrinter::USAGE_BAR_ORDER);
            })
            ->get();

        if ($printers->isEmpty()) {
            return [];
        }

        $routed = [];

        foreach ($order->items as $item) {
            $product = $item->product;
            $categoryId = $product?->category_id ?? $product?->product_category_id;
            $categoryName = strtolower((string) ($product?->category?->name ?? ''));

            // Find matching printer for this item
            $matchedPrinter = $this->findBestPrinterForCategory($printers, $categoryId, $categoryName);

            if ($matchedPrinter) {
                $printerId = $matchedPrinter->id;
                $stationName = $matchedPrinter->supportsUsage(PosPrinter::USAGE_BAR_ORDER) ? 'BARISTA / BAR' : 'DAPUR (KITCHEN)';

                if (!isset($routed[$printerId])) {
                    $routed[$printerId] = [
                        'printer' => $matchedPrinter,
                        'station_name' => $stationName,
                        'items' => [],
                    ];
                }

                $routed[$printerId]['items'][] = $item;
            }
        }

        return $routed;
    }

    /**
     * Determine best printer match based on assigned categories and fallback heuristics.
     */
    protected function findBestPrinterForCategory(Collection $printers, ?string $categoryId, string $categoryName): ?PosPrinter
    {
        // 1. Explicit Category Match
        if ($categoryId) {
            foreach ($printers as $p) {
                $assignedCats = $p->assigned_category_ids ?? [];
                if (!empty($assignedCats) && in_array($categoryId, $assignedCats, true)) {
                    return $p;
                }
            }
        }

        // 2. Bar Heuristic for Drinks / Beverages
        $isBeverage = str_contains($categoryName, 'minuman')
            || str_contains($categoryName, 'drink')
            || str_contains($categoryName, 'beverage')
            || str_contains($categoryName, 'kopi')
            || str_contains($categoryName, 'tea')
            || str_contains($categoryName, 'coffee')
            || str_contains($categoryName, 'bar');

        if ($isBeverage) {
            $barPrinter = $printers->first(fn($p) => $p->supportsUsage(PosPrinter::USAGE_BAR_ORDER));
            if ($barPrinter) {
                return $barPrinter;
            }
        }

        // 3. Kitchen Printer Default
        $kitchenPrinter = $printers->first(fn($p) => $p->supportsUsage(PosPrinter::USAGE_KITCHEN_ORDER));
        if ($kitchenPrinter) {
            return $kitchenPrinter;
        }

        return $printers->first();
    }
}
