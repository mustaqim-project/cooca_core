<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Calculation\CalculationEngine;
use App\Domain\Calculation\CostingResultService;
use App\Domain\Pricing\PricingEngine;
use App\Http\Controllers\Controller;
use App\Models\CostingRun;
use App\Models\CostModel;
use App\Models\Fee;
use App\Models\Material;
use App\Models\PricingRule;
use App\Models\Product;
use App\Models\Unit;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class CalculatorWebController extends Controller
{
    public function __construct(
        private readonly CalculationEngine $calculationEngine = new CalculationEngine,
        private readonly PricingEngine $pricingEngine = new PricingEngine,
        private readonly CostingResultService $costingResultService = new CostingResultService
    ) {}

    /**
     * Show live HPP calculator page.
     */
    public function index(): View
    {
        $business = Context::requireBusiness();

        $products = Product::with(['costModels.labors.laborRate', 'costModels.machines.machine', 'costModels.bomHeaders.items.material.prices', 'outputUnit'])
            ->latest()
            ->get();

        $materials = Material::with(['unit', 'prices', 'supplier'])
            ->get();

        $units = Unit::all();
        $fees = Fee::where('is_active', true)->get();
        $pricingRules = PricingRule::where('is_active', true)->get();

        // Recent Saved Costing Runs
        $recentRuns = CostingRun::with(['costModel.product', 'result', 'triggeredByUser'])
            ->where('business_id', $business->id)
            ->latest()
            ->limit(10)
            ->get();

        return view('app.calculator', compact('business', 'products', 'materials', 'units', 'fees', 'pricingRules', 'recentRuns'));
    }

    /**
     * Execute live costing calculation and return detailed breakdown JSON.
     */
    public function calculate(CostModel $costModel): JsonResponse
    {
        $result = $this->calculationEngine->calculate($costModel);

        return response()->json([
            'result' => $result->toArray(),
        ]);
    }

    /**
     * Persist calculated costing result into CostingRun & CostingResult history.
     */
    public function saveResult(Request $request): JsonResponse
    {
        $request->validate([
            'cost_model_id' => ['required', 'exists:cost_models,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        /** @var CostModel $costModel */
        $costModel = CostModel::findOrFail($request->get('cost_model_id'));
        $dto = $this->calculationEngine->calculate($costModel);

        $run = $this->costingResultService->persist($costModel, $dto, CostingRun::RUN_TYPE_MANUAL);

        if ($request->filled('notes')) {
            $run->update(['notes' => (string) $request->get('notes')]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Hasil kalkulasi HPP berhasil disimpan secara resmi.',
            'run' => [
                'id' => $run->id,
                'created_at' => $run->created_at?->translatedFormat('d M Y H:i'),
                'total_hpp' => (float) $run->result?->total_hpp,
                'hpp_per_unit' => (float) $run->result?->hpp_per_unit,
                'product_name' => $costModel->product?->name,
                'notes' => $run->notes,
            ],
        ]);
    }

    /**
     * Apply calculated HPP and recommended selling price directly to the Product master.
     */
    public function applyToProduct(Request $request): JsonResponse
    {
        $request->validate([
            'cost_model_id' => ['nullable', 'exists:cost_models,id'],
            'product_id' => ['nullable', 'exists:products,id'],
            'base_cost' => ['nullable', 'numeric', 'gte:0'],
            'selling_price' => ['nullable', 'numeric', 'gte:0'],
        ]);

        $product = null;
        $hppPerUnit = 0.0;

        if ($request->filled('cost_model_id')) {
            /** @var CostModel $costModel */
            $costModel = CostModel::with('product')->findOrFail($request->get('cost_model_id'));
            $dto = $this->calculationEngine->calculate($costModel);
            $product = $costModel->product;
            $hppPerUnit = (float) $dto->hppPerUnit;
        } elseif ($request->filled('product_id')) {
            /** @var Product $product */
            $product = Product::findOrFail($request->get('product_id'));
            $hppPerUnit = (float) ($request->get('base_cost') ?? $product->base_cost);
        }

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter product_id atau cost_model_id diperlukan.',
            ], 422);
        }

        $sellingPrice = $request->filled('selling_price')
            ? (float) $request->get('selling_price')
            : ($hppPerUnit > 0 ? round($hppPerUnit / 0.6) : 0.0); // Default 40% margin

        $product->update([
            'base_cost' => $hppPerUnit,
            'selling_price' => $sellingPrice,
        ]);

        return response()->json([
            'success' => true,
            'message' => "HPP (Rp " . number_format($hppPerUnit, 0, ',', '.') . ") & Harga Jual (Rp " . number_format($sellingPrice, 0, ',', '.') . ") berhasil diterapkan ke produk {$product->name}.",
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'base_cost' => $product->base_cost,
                'selling_price' => $product->selling_price,
            ],
        ]);
    }

    /**
     * Export all saved costing calculation runs to Excel CSV spreadsheet.
     */
    public function exportExcel(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $business = Context::requireBusiness();

        $runs = CostingRun::where('business_id', $business->id)
            ->with(['costModel.product.category', 'costModel.product.outputUnit', 'result'])
            ->latest()
            ->get();

        $filename = 'Riwayat_Kalkulasi_HPP_' . \Illuminate\Support\Str::slug($business->name) . '_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($runs, $business): void {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel compatibility

            fputcsv($file, ['DATA LENGKAP ARSIP RIWAYAT KALKULASI HPP & PRICING']);
            fputcsv($file, ['Nama Bisnis', $business->name]);
            fputcsv($file, ['Mata Uang', $business->currency_code . ' (' . $business->currency_symbol . ')']);
            fputcsv($file, ['Waktu Export', date('d F Y H:i:s')]);
            fputcsv($file, []);

            fputcsv($file, [
                'No',
                'Waktu Kalkulasi',
                'Nama Produk',
                'Kategori',
                'Satuan',
                'Metode HPP',
                'Biaya Bahan Baku (Material)',
                'Biaya Tenaga Kerja (Labor)',
                'Biaya Mesin & Utilitas',
                'Biaya Overhead (BOP)',
                'Total HPP Batch',
                'HPP per Unit (Modal Bersih)',
                'Harga Jual Standar (Margin 40%)',
                'Potensi Laba Kotor per Unit',
                'Catatan Kalkulasi',
                'Tipe Run',
            ]);

            $no = 1;
            foreach ($runs as $r) {
                $prod = $r->costModel?->product;
                $res = $r->result;
                $hppUnit = (float) ($res?->hpp_per_unit ?? 0);
                $recPrice = $hppUnit > 0 ? round($hppUnit / 0.6) : 0;
                $profit = $recPrice - $hppUnit;

                fputcsv($file, [
                    $no++,
                    $r->created_at?->format('Y-m-d H:i:s'),
                    $prod?->name ?? 'Produk',
                    $prod?->category?->name ?? 'Umum',
                    $prod?->outputUnit?->name ?? 'pcs',
                    strtoupper((string) ($r->costModel?->method ?? 'recipe_bom')),
                    round((float) ($res?->total_material_cost ?? 0)),
                    round((float) ($res?->total_labor_cost ?? 0)),
                    round((float) ($res?->total_machine_cost ?? 0)),
                    round((float) ($res?->total_overhead_cost ?? 0)),
                    round((float) ($res?->total_hpp ?? 0)),
                    round($hppUnit),
                    round($recPrice),
                    round($profit),
                    $r->notes ?? '-',
                    strtoupper((string) $r->run_type),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Quick create a product from the Simplified 3-Pillar Calculator.
     */
    public function quickCreateProduct(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'material_cost' => ['required', 'numeric', 'gte:0'],
            'labor_cost' => ['nullable', 'numeric', 'gte:0'],
            'overhead_cost' => ['nullable', 'numeric', 'gte:0'],
            'selling_price' => ['required', 'numeric', 'gte:0'],
        ]);

        $materialCost = (float) $validated['material_cost'];
        $laborCost = (float) ($validated['labor_cost'] ?? 0);
        $overheadCost = (float) ($validated['overhead_cost'] ?? 0);
        $totalHpp = $materialCost + $laborCost + $overheadCost;
        $sellingPrice = (float) $validated['selling_price'];

        // Get default unit or create PCS
        $unit = Unit::where('business_id', $business->id)->first()
            ?? Unit::create([
                'business_id' => $business->id,
                'code' => 'PCS',
                'name' => 'Pieces',
                'symbol' => 'pcs',
                'category' => Unit::CATEGORY_QUANTITY,
            ]);

        $product = Product::create([
            'business_id' => $business->id,
            'name' => $validated['name'],
            'sku' => 'PRD-' . strtoupper(Str::random(6)),
            'output_unit_id' => $unit->id,
            'base_cost' => $totalHpp,
            'selling_price' => $sellingPrice,
            'is_active' => true,
        ]);

        // Automatically create a Simple Cost Model for this product
        $marginPct = $sellingPrice > 0 ? round((($sellingPrice - $totalHpp) / $sellingPrice) * 100, 2) : 0.0;

        CostModel::create([
            'business_id' => $business->id,
            'product_id' => $product->id,
            'name' => 'HPP Sederhana - ' . $product->name,
            'method' => CostModel::METHOD_SIMPLE,
            'output_basis' => CostModel::BASIS_SELLABLE,
            'formula_definition' => [
                'material_cost' => $materialCost,
                'labor_cost' => $laborCost,
                'overhead_cost' => $overheadCost,
                'margin_pct' => $marginPct,
            ],
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Produk '{$product->name}' berhasil dibuat dengan HPP Rp " . number_format($totalHpp, 0, ',', '.') . " dan Harga Jual Rp " . number_format($sellingPrice, 0, ',', '.') . ". Siap dijual di kasir POS!",
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'base_cost' => $product->base_cost,
                'selling_price' => $product->selling_price,
            ],
        ]);
    }
}

