<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Billing\EntitlementService;
use App\Domain\Import\DataImportService;
use App\Http\Controllers\Controller;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ImportWebController extends Controller
{
    public function __construct(
        private readonly DataImportService $importService,
        private readonly EntitlementService $entitlementService
    ) {}

    /**
     * Display the Import Hub for Materials, Products, Recipes & Inventory.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();
        $canImport = $this->entitlementService->canImportData($business);
        $activeTab = $request->query('tab', 'products');

        $totalProducts = \App\Models\Product::where('business_id', $business->id)->count();
        $totalMaterials = \App\Models\Material::where('business_id', $business->id)->count();
        $totalRecipes = \App\Models\BomHeader::whereHas('costModel', function ($q) use ($business): void {
            $q->where('business_id', $business->id);
        })->count();
        $totalStocks = \App\Models\InventoryStock::where('business_id', $business->id)->count();

        return view('app.import.index', compact(
            'business',
            'canImport',
            'activeTab',
            'totalProducts',
            'totalMaterials',
            'totalRecipes',
            'totalStocks'
        ));
    }

    /**
     * Download Excel or CSV template for Products.
     */
    public function downloadProductTemplate(Request $request): StreamedResponse
    {
        $format = $request->query('format', 'xlsx') === 'csv' ? 'csv' : 'xlsx';
        return $this->importService->downloadProductTemplate($format);
    }

    /**
     * Download Excel or CSV template for Recipes / BOM.
     */
    public function downloadRecipeTemplate(Request $request): StreamedResponse
    {
        $format = $request->query('format', 'xlsx') === 'csv' ? 'csv' : 'xlsx';
        return $this->importService->downloadRecipeTemplate($format);
    }

    /**
     * Upload and preview parsed products with duplicate checks.
     */
    public function previewProducts(Request $request): JsonResponse|RedirectResponse
    {
        $business = Context::requireBusiness();
        $this->assertCanImport($business);

        $request->validate([
            'file' => ['required', 'file', 'max:5120', 'mimes:xlsx,xls,csv,txt'],
        ]);

        $preview = $this->importService->parseAndPreviewProducts($business, $request->file('file'));

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $preview,
            ]);
        }

        return back()->with('product_preview', $preview)->with('active_tab', 'products');
    }

    /**
     * Execute batch product import.
     */
    public function executeProducts(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        $this->assertCanImport($business);

        $validated = $request->validate([
            'rows' => ['required'],
            'duplicate_strategy' => ['required', 'in:skip,update'],
        ]);

        $rows = is_string($validated['rows']) ? json_decode($validated['rows'], true) : $validated['rows'];
        if (!is_array($rows) || empty($rows)) {
            return back()->withErrors(['rows' => 'Tidak ada data produk yang valid untuk diimport.']);
        }

        $result = $this->importService->executeProductImport($business, $rows, $validated['duplicate_strategy']);

        $message = "Import Produk Selesai: {$result['imported']} produk baru berhasil ditambahkan";
        if ($result['updated'] > 0) {
            $message .= ", {$result['updated']} produk diperbarui";
        }
        if ($result['skipped'] > 0) {
            $message .= ", {$result['skipped']} duplikat/baris bermasalah dilewati";
        }
        $message .= '.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'result' => $result,
                'redirect_url' => route('products.index'),
            ]);
        }

        return redirect()->route('products.index')->with('success', $message);
    }

    /**
     * Upload and preview parsed recipes with duplicate checks.
     */
    public function previewRecipes(Request $request): JsonResponse|RedirectResponse
    {
        $business = Context::requireBusiness();
        $this->assertCanImport($business);

        $request->validate([
            'file' => ['required', 'file', 'max:5120', 'mimes:xlsx,xls,csv,txt'],
        ]);

        $preview = $this->importService->parseAndPreviewRecipes($business, $request->file('file'));

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $preview,
            ]);
        }

        return back()->with('recipe_preview', $preview)->with('active_tab', 'recipes');
    }

    /**
     * Execute batch recipe import.
     */
    public function executeRecipes(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        $this->assertCanImport($business);

        $validated = $request->validate([
            'rows' => ['required'],
            'duplicate_strategy' => ['required', 'in:skip,update'],
        ]);

        $rows = is_string($validated['rows']) ? json_decode($validated['rows'], true) : $validated['rows'];
        if (!is_array($rows) || empty($rows)) {
            return back()->withErrors(['rows' => 'Tidak ada data resep yang valid untuk diimport.']);
        }

        $result = $this->importService->executeRecipeImport($business, $rows, $validated['duplicate_strategy']);

        $message = "Import Resep Selesai: {$result['imported']} bahan resep berhasil ditambahkan";
        if ($result['updated'] > 0) {
            $message .= ", {$result['updated']} bahan resep diperbarui";
        }
        if ($result['skipped'] > 0) {
            $message .= ", {$result['skipped']} duplikat/baris bermasalah dilewati";
        }
        $message .= '.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'result' => $result,
                'redirect_url' => route('products.index'),
            ]);
        }

        return redirect()->route('products.index')->with('success', $message);
    }

    /**
     * Download Excel or CSV template for Materials.
     */
    public function downloadMaterialTemplate(Request $request): StreamedResponse
    {
        $format = $request->query('format', 'xlsx') === 'csv' ? 'csv' : 'xlsx';
        return $this->importService->downloadMaterialTemplate($format);
    }

    /**
     * Upload and preview parsed materials with duplicate checks.
     */
    public function previewMaterials(Request $request): JsonResponse|RedirectResponse
    {
        $business = Context::requireBusiness();
        $this->assertCanImport($business);

        $request->validate([
            'file' => ['required', 'file', 'max:5120', 'mimes:xlsx,xls,csv,txt'],
        ]);

        $preview = $this->importService->parseAndPreviewMaterials($business, $request->file('file'));

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $preview,
            ]);
        }

        return back()->with('material_preview', $preview)->with('active_tab', 'materials');
    }

    /**
     * Execute batch material import.
     */
    public function executeMaterials(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        $this->assertCanImport($business);

        $validated = $request->validate([
            'rows' => ['required'],
            'duplicate_strategy' => ['required', 'in:skip,update'],
        ]);

        $rows = is_string($validated['rows']) ? json_decode($validated['rows'], true) : $validated['rows'];
        if (!is_array($rows) || empty($rows)) {
            return back()->withErrors(['rows' => 'Tidak ada data bahan baku yang valid untuk diimport.']);
        }

        if (collect($rows)->contains(fn ($row): bool => ($row['status'] ?? 'valid') === 'error')) {
            $message = 'Import dibatalkan karena masih ada error kritis pada preview bahan baku.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->withErrors(['rows' => $message]);
        }

        $result = $this->importService->executeMaterialImport($business, $rows, $validated['duplicate_strategy']);

        $message = "Import Bahan Baku Selesai: {$result['imported']} bahan baku baru berhasil ditambahkan";
        if ($result['updated'] > 0) {
            $message .= ", {$result['updated']} bahan baku diperbarui";
        }
        if ($result['skipped'] > 0) {
            $message .= ", {$result['skipped']} duplikat/baris bermasalah dilewati";
        }
        $message .= '.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'result' => $result,
                'redirect_url' => route('materials.index'),
            ]);
        }

        return redirect()->route('materials.index')->with('success', $message);
    }

    /**
     * Download Excel or CSV template for Inventory / Initial Stock.
     */
    public function downloadInventoryTemplate(Request $request): StreamedResponse
    {
        $format = $request->query('format', 'xlsx') === 'csv' ? 'csv' : 'xlsx';
        return $this->importService->downloadInventoryTemplate($format);
    }

    /**
     * Upload and preview parsed inventory / stock data with duplicate checks.
     */
    public function previewInventory(Request $request): JsonResponse|RedirectResponse
    {
        $business = Context::requireBusiness();
        $this->assertCanImport($business);

        $request->validate([
            'file' => ['required', 'file', 'max:5120', 'mimes:xlsx,xls,csv,txt'],
        ]);

        $preview = $this->importService->parseAndPreviewInventory($business, $request->file('file'));

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $preview,
            ]);
        }

        return back()->with('inventory_preview', $preview)->with('active_tab', 'inventory');
    }

    /**
     * Execute batch inventory stock import.
     */
    public function executeInventory(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        $this->assertCanImport($business);

        $validated = $request->validate([
            'rows' => ['required'],
            'duplicate_strategy' => ['required', 'in:skip,adjust,initial'],
        ]);

        $rows = is_string($validated['rows']) ? json_decode($validated['rows'], true) : $validated['rows'];
        if (!is_array($rows) || empty($rows)) {
            return back()->withErrors(['rows' => 'Tidak ada data saldo stok yang valid untuk diimport.']);
        }

        $result = $this->importService->executeInventoryImport(
            $business,
            $rows,
            $validated['duplicate_strategy'],
            auth()->id()
        );

        $message = "Import Saldo Stok Selesai: {$result['imported']} saldo awal berhasil dicatat";
        if ($result['updated'] > 0) {
            $message .= ", {$result['updated']} saldo disesuaikan";
        }
        if ($result['skipped'] > 0) {
            $message .= ", {$result['skipped']} duplikat/baris bermasalah dilewati";
        }
        $message .= '.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'result' => $result,
                'redirect_url' => route('inventory.stocks'),
            ]);
        }

        return redirect()->route('inventory.stocks')->with('success', $message);
    }

    /**
     * Ensure business has active Pro / Patungan / Core plan for import.
     */
    private function assertCanImport(\App\Models\Business $business): void
    {
        if (!$this->entitlementService->canImportData($business)) {
            abort(403, 'Fitur Import Data Excel/CSV memerlukan paket Pro / Patungan. Silakan upgrade paket bisnis Anda.');
        }
    }
}
