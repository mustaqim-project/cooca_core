<?php

declare(strict_types=1);

namespace App\Domain\Report;

use App\Models\Product;
use App\Models\ProductCostVersion;

final class ReportingService
{
    /**
     * Report 1: HPP per Product List.
     *
     * @return array<int, mixed>
     */
    public function hppPerProduct(): array
    {
        $products = Product::with(['category', 'outputUnit', 'costModels.latestVersion', 'costModels.costingRuns.result'])
            ->latest()
            ->get();

        return $products->map(function (Product $product) {
            $activeModel = $product->costModels->where('is_active', true)->first() ?? $product->costModels->first();
            $latestVersion = $activeModel?->latestVersion;
            $latestRun = $activeModel?->costingRuns()->latest()->first();
            $result = $latestRun?->result;

            $hppPerUnit = (float) ($result?->hpp_per_unit ?? $latestVersion?->hpp_per_unit ?? 0.0);
            $totalHpp = (float) ($result?->total_hpp ?? $latestVersion?->total_hpp ?? 0.0);
            $matCost = (float) ($result?->total_material_cost ?? 0.0);
            $labCost = (float) ($result?->total_labor_cost ?? 0.0);
            $macCost = (float) ($result?->total_machine_cost ?? 0.0);
            $ovhCost = (float) ($result?->total_overhead_cost ?? 0.0);

            // Default target gross margin benchmark (e.g. 40%)
            $targetMarginPct = 40.0;
            $recommendedPrice = $hppPerUnit > 0 ? round($hppPerUnit / (1 - ($targetMarginPct / 100))) : 0.0;
            $grossProfit = $recommendedPrice - $hppPerUnit;
            $markupPct = $hppPerUnit > 0 ? round(($grossProfit / $hppPerUnit) * 100, 1) : 0.0;

            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku ?? '-',
                'category' => $product->category?->name ?? 'Umum',
                'output_unit' => $product->outputUnit?->name ?? 'pcs',
                'costing_method' => $activeModel?->method ?? 'recipe_bom',
                'material_cost' => $matCost,
                'labor_cost' => $labCost,
                'machine_cost' => $macCost,
                'overhead_cost' => $ovhCost,
                'hpp_per_unit' => $hppPerUnit,
                'total_hpp' => $totalHpp,
                'recommended_price' => $recommendedPrice,
                'gross_profit' => $grossProfit,
                'margin_percentage' => $targetMarginPct,
                'markup_percentage' => $markupPct,
                'version_label' => $latestVersion?->version_label ?? 'v1.0',
            ];
        })->all();
    }

    /**
     * Report 2: Aggregate Cost Breakdown Summary.
     *
     * @return array<string, float>
     */
    public function costBreakdownSummary(): array
    {
        $versions = ProductCostVersion::latest()->get();

        $totalMat = 0.0;
        $totalLab = 0.0;
        $totalMac = 0.0;
        $totalOvh = 0.0;
        $totalHpp = 0.0;

        foreach ($versions as $v) {
            $snap = $v->hpp_snapshot;
            $totalMat += (float) ($snap['total_material_cost'] ?? 0.0);
            $totalLab += (float) ($snap['total_labor_cost'] ?? 0.0);
            $totalMac += (float) ($snap['total_machine_cost'] ?? 0.0);
            $totalOvh += (float) ($snap['total_overhead_cost'] ?? 0.0);
            $totalHpp += (float) $v->total_hpp;
        }

        return [
            'total_material_cost' => $totalMat,
            'total_labor_cost' => $totalLab,
            'total_machine_cost' => $totalMac,
            'total_overhead_cost' => $totalOvh,
            'total_hpp' => $totalHpp,
            'material_percentage' => $totalHpp > 0 ? ($totalMat / $totalHpp) * 100 : 0,
            'labor_percentage' => $totalHpp > 0 ? ($totalLab / $totalHpp) * 100 : 0,
            'machine_percentage' => $totalHpp > 0 ? ($totalMac / $totalHpp) * 100 : 0,
            'overhead_percentage' => $totalHpp > 0 ? ($totalOvh / $totalHpp) * 100 : 0,
        ];
    }
}
