<?php

declare(strict_types=1);

namespace App\Domain\Import;

use App\Domain\Inventory\StockService;
use App\Models\BomHeader;
use App\Models\BomItem;
use App\Models\Business;
use App\Models\CostModel;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockMovement;
use App\Models\Unit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class DataImportService
{
    public function __construct(
        private readonly StockService $stockService = new StockService()
    ) {}

    // ── Template Generators ───────────────────────────────────────────────────

    /**
     * Download Excel / CSV template for Product Import.
     */
    public function downloadProductTemplate(string $format = 'xlsx'): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Produk');

        $headers = [
            'A1' => 'Nama Produk *',
            'B1' => 'Kode / SKU',
            'C1' => 'Kategori',
            'D1' => 'Satuan Output *',
            'E1' => 'Harga Jual *',
            'F1' => 'HPP Awal (Biaya Modal)',
            'G1' => 'Stok Minimal',
            'H1' => 'Deskripsi Produk',
            'I1' => 'Bahan Baku Terkait (SKU/Nama)',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Style header row
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F766E'], // Teal 700
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '0D5E56'],
                ],
            ],
        ];
        $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Sample rows
        $sampleData = [
            ['Kopi Susu Gula Aren', 'KOP-001', 'Minuman Kopi', 'cup', 18000, 7500, 10, 'Espresso double shot dengan susu creamy dan gula aren asli.', 'Biji Kopi Robusta Blend'],
            ['Croissant Butter Spesial', 'ROT-001', 'Bakery & Pastry', 'pcs', 22000, 9000, 5, 'Croissant butter renyah lapis mentega Prancis.', 'Tepung Terigu Cakra Kembar'],
            ['Matcha Latte Ice', 'MIN-002', 'Minuman Non-Kopi', 'cup', 20000, 8500, 15, 'Matcha premium Jepang dengan susu evaporasi.', ''],
            ['T-Shirt Merchandise Hitam', 'MRC-001', 'Merchandise', 'pcs', 95000, 45000, 20, 'Kaos katun combed 24s dengan sablon plastisol.', ''],
        ];

        $rowIdx = 2;
        foreach ($sampleData as $row) {
            $colIdx = 1;
            foreach ($row as $value) {
                $sheet->setCellValueExplicit(
                    Coordinate::stringFromColumnIndex($colIdx) . $rowIdx,
                    $value,
                    is_numeric($value) ? \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC : \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );
                $colIdx++;
            }
            $rowIdx++;
        }

        // Auto-fit columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'cooca_template_import_produk_' . date('Ymd') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');

        return new StreamedResponse(function () use ($spreadsheet, $format): void {
            if ($format === 'csv') {
                $writer = new Csv($spreadsheet);
                $writer->setUseBOM(true);
            } else {
                $writer = new Xlsx($spreadsheet);
            }
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Download Excel / CSV template for Recipe / BOM Import.
     */
    public function downloadRecipeTemplate(string $format = 'xlsx'): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Resep BOM');

        $headers = [
            'A1' => 'Nama / SKU Produk Jadi *',
            'B1' => 'Nama / SKU Bahan Baku *',
            'C1' => 'Jumlah Pemakaian *',
            'D1' => 'Satuan Bahan *',
            'E1' => 'Susut / Waste %',
            'F1' => 'Catatan Bahan',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1D4ED8'], // Blue 700
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '1E40AF'],
                ],
            ],
        ];
        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Sample rows showing multi-material recipe for "Kopi Susu Gula Aren"
        $sampleData = [
            ['Kopi Susu Gula Aren', 'Biji Kopi Robusta Blend', 18, 'gram', 2, 'Espresso extraction 36ml'],
            ['Kopi Susu Gula Aren', 'Susu UHT Full Cream', 120, 'ml', 0, 'Fresh cold milk'],
            ['Kopi Susu Gula Aren', 'Gula Aren Cair', 25, 'ml', 0, 'Manis level standard'],
            ['Kopi Susu Gula Aren', 'Cup Plastik 14oz + Tutup', 1, 'pcs', 1, 'Kemasan take-away'],
            ['Croissant Butter Spesial', 'Tepung Terigu Protein Tinggi', 100, 'gram', 3, 'Adonan dasar laminasi'],
            ['Croissant Butter Spesial', 'Butter Lembaran Sheet', 40, 'gram', 1, 'Lipatan butter Prancis'],
        ];

        $rowIdx = 2;
        foreach ($sampleData as $row) {
            $colIdx = 1;
            foreach ($row as $value) {
                $sheet->setCellValueExplicit(
                    Coordinate::stringFromColumnIndex($colIdx) . $rowIdx,
                    $value,
                    is_numeric($value) ? \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC : \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );
                $colIdx++;
            }
            $rowIdx++;
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'cooca_template_import_resep_bom_' . date('Ymd') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');

        return new StreamedResponse(function () use ($spreadsheet, $format): void {
            if ($format === 'csv') {
                $writer = new Csv($spreadsheet);
                $writer->setUseBOM(true);
            } else {
                $writer = new Xlsx($spreadsheet);
            }
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Download Excel / CSV template for Material Import.
     */
    public function downloadMaterialTemplate(string $format = 'xlsx'): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Bahan Baku');

        $headers = [
            'A1' => 'Nama Bahan Baku *',
            'B1' => 'Kode / SKU',
            'C1' => 'Kategori Bahan',
            'D1' => 'Satuan Dasar *',
            'E1' => 'Harga Beli Standar',
            'F1' => 'Nama Pemasok / Supplier',
            'G1' => 'Deskripsi / Catatan',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '059669'], // Emerald 600
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '047857'],
                ],
            ],
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $sampleData = [
            ['Biji Kopi Robusta Blend', 'MAT-KOP-01', 'Biji Kopi & Bubuk', 'gram', 150, 'CV Kopi Nusantara', 'Roast medium-dark untuk espresso'],
            ['Susu UHT Full Cream', 'MAT-DRY-01', 'Dairy & Susu', 'ml', 20, 'PT Sumber Segar Dairy', 'Kemasan karton 1000ml plain'],
            ['Gula Aren Cair Organik', 'MAT-SWT-01', 'Pemanis & Sirup', 'ml', 35, 'UD Manis Aren Alami', 'Briket gula aren cair murni'],
            ['Cup Plastik 14oz + Tutup', 'MAT-PKG-01', 'Kemasan & Packaging', 'pcs', 650, 'Toko Plastik Maju', 'Cup inject tebal food grade'],
            ['Tepung Terigu Cakra Kembar', 'MAT-FLR-01', 'Tepung & Gandum', 'gram', 14, 'Distributor Sembako Jaya', 'Protein tinggi untuk roti pastry'],
        ];

        $rowIdx = 2;
        foreach ($sampleData as $row) {
            $colIdx = 1;
            foreach ($row as $value) {
                $sheet->setCellValueExplicit(
                    Coordinate::stringFromColumnIndex($colIdx) . $rowIdx,
                    $value,
                    is_numeric($value) ? \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC : \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );
                $colIdx++;
            }
            $rowIdx++;
        }

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'cooca_template_import_bahan_baku_' . date('Ymd') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');

        return new StreamedResponse(function () use ($spreadsheet, $format): void {
            if ($format === 'csv') {
                $writer = new Csv($spreadsheet);
                $writer->setUseBOM(true);
            } else {
                $writer = new Xlsx($spreadsheet);
            }
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Download Excel / CSV template for Inventory / Stock Import.
     */
    public function downloadInventoryTemplate(string $format = 'xlsx'): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Stok Awal');

        $headers = [
            'A1' => 'Nama Bahan Baku *',
            'B1' => 'Kode / SKU Bahan',
            'C1' => 'Lokasi / Outlet / Gudang *',
            'D1' => 'Jumlah Stok *',
            'E1' => 'Satuan *',
            'F1' => 'Biaya Pokok / HPP per Unit',
            'G1' => 'Catatan / Alasan Saldo',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '047857'], // Emerald 700
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '065F46'],
                ],
            ],
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $sampleData = [
            ['Biji Kopi Robusta Blend', 'MAT-KOP-01', 'Gudang Bahan Baku', 10000, 'gram', 150, 'Stok awal gudang'],
            ['Susu UHT Full Cream', 'MAT-DRY-01', 'Gudang Bahan Baku', 24, 'pcs', 18000, 'Stok awal bahan'],
            ['Cup Plastik 14oz + Tutup', 'MAT-PKG-01', 'Outlet Utama', 500, 'pcs', 650, 'Stok awal packaging'],
            ['Sirup Gula Aren', 'MAT-SWT-01', 'Outlet Utama', 10, 'liter', 35000, 'Saldo awal operasional'],
        ];

        $rowIdx = 2;
        foreach ($sampleData as $row) {
            $colIdx = 1;
            foreach ($row as $value) {
                $sheet->setCellValueExplicit(
                    Coordinate::stringFromColumnIndex($colIdx) . $rowIdx,
                    $value,
                    is_numeric($value) ? \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC : \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );
                $colIdx++;
            }
            $rowIdx++;
        }

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'cooca_template_import_stok_inventory_' . date('Ymd') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');

        return new StreamedResponse(function () use ($spreadsheet, $format): void {
            if ($format === 'csv') {
                $writer = new Csv($spreadsheet);
                $writer->setUseBOM(true);
            } else {
                $writer = new Xlsx($spreadsheet);
            }
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    // ── Material Parsing & Preview ────────────────────────────────────────────

    /**
     * Parse and validate uploaded material file before saving.
     *
     * @return array{
     *     total_rows: int,
     *     valid_count: int,
     *     duplicate_count: int,
     *     error_count: int,
     *     rows: array<int, array<string, mixed>>
     * }
     */
    public function parseAndPreviewMaterials(Business $business, UploadedFile $file): array
    {
        $rawRows = $this->readSpreadsheetRows($file);
        if (empty($rawRows)) {
            return [
                'total_rows' => 0,
                'valid_count' => 0,
                'duplicate_count' => 0,
                'error_count' => 0,
                'rows' => [],
            ];
        }

        // Preload materials in DB for duplicate check
        $existingMaterials = Material::withoutGlobalScopes()->where('business_id', $business->id)->get();
        $dbNames = $existingMaterials->pluck('name')->map(fn ($n) => strtolower(trim((string) $n)))->flip()->all();
        $dbSkus = $existingMaterials->pluck('code')->filter()->map(fn ($s) => strtolower(trim((string) $s)))->flip()->all();

        // Available units
        $units = Unit::available()->get();
        $unitMap = [];
        foreach ($units as $u) {
            $unitMap[strtolower($u->code)] = $u;
            $unitMap[strtolower($u->name)] = $u;
        }

        $parsedRows = [];
        $seenFileNames = [];
        $seenFileSkus = [];
        $validCount = 0;
        $duplicateCount = 0;
        $errorCount = 0;

        foreach ($rawRows as $index => $row) {
            $rowNumber = $index + 2;
            $name = trim((string) ($row['name'] ?? $row['material_name_or_sku'] ?? ''));
            $sku = trim((string) ($row['sku'] ?? ''));
            $category = trim((string) ($row['category'] ?? ''));
            $unitStr = trim((string) ($row['unit'] ?? ''));
            $purchasePrice = (float) ($row['purchase_price'] ?? $row['base_cost'] ?? 0);
            $supplierName = trim((string) ($row['supplier'] ?? ''));
            $description = trim((string) ($row['description'] ?? $row['notes'] ?? ''));

            $errors = [];
            $status = 'valid';
            $statusReason = 'Siap diimport sebagai bahan baku baru.';

            if ($name === '') {
                $errors[] = 'Nama bahan baku tidak boleh kosong.';
            }

            if ($unitStr === '') {
                $unitStr = 'pcs';
            }

            $resolvedUnit = $unitMap[strtolower($unitStr)] ?? $unitMap['pcs'] ?? null;
            if (!$resolvedUnit) {
                $resolvedUnit = Unit::firstOrCreate(['code' => 'pcs'], ['name' => 'Pcs', 'category' => 'quantity']);
            }

            if ($purchasePrice < 0) {
                $errors[] = 'Harga beli standar tidak boleh negatif.';
            }

            // Check duplicates
            $lowerName = strtolower($name);
            $lowerSku = strtolower($sku);

            $isDbDuplicate = false;
            $isFileDuplicate = false;

            if ($name !== '') {
                if (isset($dbNames[$lowerName])) {
                    $isDbDuplicate = true;
                    $statusReason = "Nama bahan sudah terdaftar di database ({$name}).";
                } elseif ($sku !== '' && isset($dbSkus[$lowerSku])) {
                    $isDbDuplicate = true;
                    $statusReason = "Kode/SKU bahan sudah terdaftar di database ({$sku}).";
                } elseif (isset($seenFileNames[$lowerName])) {
                    $isFileDuplicate = true;
                    $statusReason = "Duplikat dengan baris sebelumnya dalam file (Nama: {$name}).";
                } elseif ($sku !== '' && isset($seenFileSkus[$lowerSku])) {
                    $isFileDuplicate = true;
                    $statusReason = "Duplikat dengan baris sebelumnya dalam file (SKU: {$sku}).";
                }
            }

            if (!empty($errors)) {
                $status = 'error';
                $statusReason = implode(' ', $errors);
                $errorCount++;
            } elseif ($isDbDuplicate || $isFileDuplicate) {
                $status = 'duplicate';
                $duplicateCount++;
            } else {
                $validCount++;
            }

            if ($name !== '') {
                $seenFileNames[$lowerName] = true;
            }
            if ($sku !== '') {
                $seenFileSkus[$lowerSku] = true;
            }

            $parsedRows[] = [
                'row_number' => $rowNumber,
                'name' => $name,
                'sku' => $sku,
                'category' => $category,
                'unit' => $resolvedUnit->code ?? 'pcs',
                'unit_id' => $resolvedUnit->id ?? null,
                'purchase_price' => $purchasePrice,
                'supplier_name' => $supplierName,
                'description' => $description,
                'status' => $status,
                'status_reason' => $statusReason,
                'errors' => $errors,
            ];
        }

        return [
            'total_rows' => count($parsedRows),
            'valid_count' => $validCount,
            'duplicate_count' => $duplicateCount,
            'error_count' => $errorCount,
            'rows' => $parsedRows,
        ];
    }

    /**
     * Execute batch material import.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  string  $duplicateStrategy  'skip' | 'update'
     * @return array{imported: int, updated: int, skipped: int}
     */
    public function executeMaterialImport(Business $business, array $rows, string $duplicateStrategy = 'skip'): array
    {
        $imported = 0;
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($business, $rows, $duplicateStrategy, &$imported, &$updated, &$skipped): void {
            $categoryCache = [];
            $supplierCache = [];

            foreach ($rows as $row) {
                $status = $row['status'] ?? 'valid';
                if ($status === 'error') {
                    $skipped++;
                    continue;
                }

                $name = trim((string) ($row['name'] ?? ''));
                if ($name === '') {
                    $skipped++;
                    continue;
                }

                $sku = !empty($row['sku']) ? trim((string) $row['sku']) : null;
                $categoryName = trim((string) ($row['category'] ?? ''));
                $supplierName = trim((string) ($row['supplier_name'] ?? ''));
                $unitId = $row['unit_id'] ?? null;
                $purchasePrice = (float) ($row['purchase_price'] ?? 0);
                $description = trim((string) ($row['description'] ?? ''));

                // Resolve MaterialCategory
                $categoryId = null;
                if ($categoryName !== '') {
                    $catKey = strtolower($categoryName);
                    if (!isset($categoryCache[$catKey])) {
                        $cat = \App\Models\MaterialCategory::withoutGlobalScopes()->firstOrCreate(
                            ['business_id' => $business->id, 'name' => $categoryName],
                            ['slug' => Str::slug($categoryName)]
                        );
                        $categoryCache[$catKey] = $cat->id;
                    }
                    $categoryId = $categoryCache[$catKey];
                }

                // Resolve Supplier
                $supplierId = null;
                if ($supplierName !== '') {
                    $supKey = strtolower($supplierName);
                    if (!isset($supplierCache[$supKey])) {
                        $sup = \App\Models\Supplier::withoutGlobalScopes()->firstOrCreate(
                            ['business_id' => $business->id, 'name' => $supplierName],
                            ['slug' => Str::slug($supplierName)]
                        );
                        $supplierCache[$supKey] = $sup->id;
                    }
                    $supplierId = $supplierCache[$supKey];
                }

                if (!$unitId) {
                    $defaultUnit = Unit::where('code', 'pcs')->first() ?? Unit::first();
                    $unitId = $defaultUnit?->id;
                }

                $existing = Material::withoutGlobalScopes()->where('business_id', $business->id)
                    ->where(function ($q) use ($name, $sku): void {
                        $q->where('name', $name);
                        if ($sku) {
                            $q->orWhere('code', $sku);
                        }
                    })->first();

                if ($existing) {
                    if ($duplicateStrategy === 'update') {
                        $existing->update([
                            'code' => $sku ?: $existing->code,
                            'category_id' => $categoryId ?: $existing->category_id,
                            'supplier_id' => $supplierId ?: $existing->supplier_id,
                            'unit_id' => $unitId ?: $existing->unit_id,
                            'description' => $description ?: $existing->description,
                        ]);

                        if ($purchasePrice > 0) {
                            $existing->prices()->create([
                                'business_id' => $business->id,
                                'purchase_price' => $purchasePrice,
                                'supplier_id' => $supplierId ?: $existing->supplier_id,
                                'purchase_unit_id' => $unitId ?: $existing->unit_id,
                                'effective_date' => now(),
                                'notes' => 'Diperbarui via import Excel',
                            ]);

                            app(\App\Domain\Calculation\HppPropagationService::class)
                                ->refreshForMaterial($existing->id);
                        }
                        $updated++;
                    } else {
                        $skipped++;
                    }
                    continue;
                }

                $slug = Str::slug($name);
                if (Material::withoutGlobalScopes()->where('business_id', $business->id)->where('slug', $slug)->exists()) {
                    $slug .= '-' . Str::lower(Str::random(4));
                }

                $material = Material::create([
                    'business_id' => $business->id,
                    'name' => $name,
                    'slug' => $slug,
                    'code' => $sku,
                    'category_id' => $categoryId,
                    'supplier_id' => $supplierId,
                    'unit_id' => $unitId,
                    'description' => $description ?: null,
                ]);

                if ($purchasePrice > 0) {
                    $material->prices()->create([
                        'business_id' => $business->id,
                        'purchase_price' => $purchasePrice,
                        'supplier_id' => $supplierId,
                        'purchase_unit_id' => $unitId,
                        'effective_date' => now(),
                        'notes' => 'Harga awal import Excel',
                    ]);
                }

                $imported++;
            }
        });

        return [
            'imported' => $imported,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    // ── Product Parsing & Preview ─────────────────────────────────────────────

    /**
     * Parse and validate uploaded product file before saving.
     *
     * @return array{
     *     total_rows: int,
     *     valid_count: int,
     *     duplicate_count: int,
     *     error_count: int,
     *     rows: array<int, array<string, mixed>>
     * }
     */
    public function parseAndPreviewProducts(Business $business, UploadedFile $file): array
    {
        $rawRows = $this->readSpreadsheetRows($file);
        if (empty($rawRows)) {
            return [
                'total_rows' => 0,
                'valid_count' => 0,
                'duplicate_count' => 0,
                'error_count' => 0,
                'rows' => [],
            ];
        }

        // Existing products in DB for duplicate check
        $existingProducts = Product::withoutGlobalScopes()->where('business_id', $business->id)->get();
        $dbNames = $existingProducts->pluck('name')->map(fn ($n) => strtolower(trim((string) $n)))->flip()->all();
        $dbSkus = $existingProducts->pluck('code')->filter()->map(fn ($s) => strtolower(trim((string) $s)))->flip()->all();

        // Preload materials for strict Material-First validation
        $existingMaterials = Material::withoutGlobalScopes()->where('business_id', $business->id)->get();
        $materialBySku = [];
        $materialByName = [];
        foreach ($existingMaterials as $m) {
            if ($m->code) {
                $materialBySku[strtolower(trim($m->code))] = $m;
            }
            $materialByName[strtolower(trim($m->name))] = $m;
        }

        // Available units
        $units = Unit::available()->get();
        $unitMap = [];
        foreach ($units as $u) {
            $unitMap[strtolower($u->code)] = $u;
            $unitMap[strtolower($u->name)] = $u;
        }

        $parsedRows = [];
        $seenFileNames = [];
        $seenFileSkus = [];
        $validCount = 0;
        $duplicateCount = 0;
        $errorCount = 0;

        foreach ($rawRows as $index => $row) {
            $rowNumber = $index + 2; // offset for 1-based header row
            $name = trim((string) ($row['name'] ?? ''));
            $sku = trim((string) ($row['sku'] ?? ''));
            $category = trim((string) ($row['category'] ?? ''));
            $unitStr = trim((string) ($row['unit'] ?? ''));
            $sellingPrice = (float) ($row['selling_price'] ?? 0);
            $baseCost = (float) ($row['base_cost'] ?? 0);
            $minStock = (float) ($row['min_stock'] ?? 0);
            $description = trim((string) ($row['description'] ?? ''));
            $matIdent = trim((string) ($row['material_ident'] ?? $row['material_name_or_sku'] ?? $row['material'] ?? ''));

            $errors = [];
            $status = 'valid';
            $statusReason = 'Siap diimport sebagai produk baru.';

            // 1. Validate required fields
            if ($name === '') {
                $errors[] = 'Nama produk tidak boleh kosong.';
            }

            if ($unitStr === '') {
                $unitStr = 'pcs';
            }

            $resolvedUnit = $unitMap[strtolower($unitStr)] ?? $unitMap['pcs'] ?? null;
            if (!$resolvedUnit) {
                $resolvedUnit = Unit::firstOrCreate(['code' => 'pcs'], ['name' => 'Pcs', 'category' => 'quantity']);
            }

            if ($sellingPrice < 0) {
                $errors[] = 'Harga jual tidak boleh negatif.';
            }

            // 2. Strict Material Validation if material is specified
            $resolvedMaterial = null;
            if ($matIdent !== '') {
                $lowerMat = strtolower($matIdent);
                $resolvedMaterial = $materialBySku[$lowerMat] ?? $materialByName[$lowerMat] ?? null;
                if (!$resolvedMaterial) {
                    $errors[] = "Bahan baku '{$matIdent}' belum terdaftar di Master Data. Daftarkan bahan baku di Master Data terlebih dahulu.";
                }
            }

            // 3. Check duplicates
            $lowerName = strtolower($name);
            $lowerSku = strtolower($sku);

            $isDbDuplicate = false;
            $isFileDuplicate = false;

            if ($name !== '') {
                if (isset($dbNames[$lowerName])) {
                    $isDbDuplicate = true;
                    $statusReason = "Nama produk sudah terdaftar di database ({$name}).";
                } elseif ($sku !== '' && isset($dbSkus[$lowerSku])) {
                    $isDbDuplicate = true;
                    $statusReason = "Kode/SKU sudah terdaftar di database ({$sku}).";
                } elseif (isset($seenFileNames[$lowerName])) {
                    $isFileDuplicate = true;
                    $statusReason = "Duplikat dengan baris sebelumnya dalam file (Nama: {$name}).";
                } elseif ($sku !== '' && isset($seenFileSkus[$lowerSku])) {
                    $isFileDuplicate = true;
                    $statusReason = "Duplikat dengan baris sebelumnya dalam file (SKU: {$sku}).";
                }
            }

            if (!empty($errors)) {
                $status = 'error';
                $statusReason = implode(' ', $errors);
                $errorCount++;
            } elseif ($isDbDuplicate || $isFileDuplicate) {
                $status = 'duplicate';
                $duplicateCount++;
            } else {
                $validCount++;
            }

            if ($name !== '') {
                $seenFileNames[$lowerName] = true;
            }
            if ($sku !== '') {
                $seenFileSkus[$lowerSku] = true;
            }

            $parsedRows[] = [
                'row_number' => $rowNumber,
                'name' => $name,
                'sku' => $sku,
                'category' => $category,
                'unit' => $resolvedUnit->code ?? 'pcs',
                'unit_id' => $resolvedUnit->id ?? null,
                'selling_price' => $sellingPrice,
                'base_cost' => $baseCost,
                'min_stock' => $minStock,
                'description' => $description,
                'material_ident' => $matIdent,
                'material_id' => $resolvedMaterial?->id,
                'material_name' => $resolvedMaterial?->name,
                'status' => $status,
                'status_reason' => $statusReason,
                'errors' => $errors,
            ];
        }

        return [
            'total_rows' => count($parsedRows),
            'valid_count' => $validCount,
            'duplicate_count' => $duplicateCount,
            'error_count' => $errorCount,
            'rows' => $parsedRows,
        ];
    }

    /**
     * Execute batch product import.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  string  $duplicateStrategy  'skip' | 'update'
     * @return array{imported: int, updated: int, skipped: int}
     */
    public function executeProductImport(Business $business, array $rows, string $duplicateStrategy = 'skip'): array
    {
        $imported = 0;
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($business, $rows, $duplicateStrategy, &$imported, &$updated, &$skipped): void {
            $categoryCache = [];

            foreach ($rows as $row) {
                $status = $row['status'] ?? 'valid';
                if ($status === 'error') {
                    $skipped++;
                    continue;
                }

                $name = trim((string) ($row['name'] ?? ''));
                if ($name === '') {
                    $skipped++;
                    continue;
                }

                $sku = !empty($row['sku']) ? trim((string) $row['sku']) : null;
                $categoryName = trim((string) ($row['category'] ?? ''));
                $unitId = $row['unit_id'] ?? null;
                $sellingPrice = (float) ($row['selling_price'] ?? 0);
                $baseCost = (float) ($row['base_cost'] ?? 0);
                $minStock = (float) ($row['min_stock'] ?? 0);
                $description = trim((string) ($row['description'] ?? ''));
                $materialId = $row['material_id'] ?? null;

                // Resolve category
                $categoryId = null;
                if ($categoryName !== '') {
                    $catKey = strtolower($categoryName);
                    if (!isset($categoryCache[$catKey])) {
                        $cat = ProductCategory::firstOrCreate(
                            ['business_id' => $business->id, 'name' => $categoryName],
                            ['slug' => Str::slug($categoryName)]
                        );
                        $categoryCache[$catKey] = $cat->id;
                    }
                    $categoryId = $categoryCache[$catKey];
                }

                // Fallback unit
                if (!$unitId) {
                    $defaultUnit = Unit::where('code', 'pcs')->first() ?? Unit::first();
                    $unitId = $defaultUnit?->id;
                }

                // Check existing product in DB
                $existing = Product::withoutGlobalScopes()->where('business_id', $business->id)
                    ->where(function ($q) use ($name, $sku): void {
                        $q->where('name', $name);
                        if ($sku) {
                            $q->orWhere('code', $sku);
                        }
                    })->first();

                if ($existing) {
                    if ($duplicateStrategy === 'update') {
                        $existing->update([
                            'code' => $sku ?: $existing->code,
                            'category_id' => $categoryId ?: $existing->category_id,
                            'output_unit_id' => $unitId ?: $existing->output_unit_id,
                            'selling_price' => $sellingPrice > 0 ? $sellingPrice : $existing->selling_price,
                            'base_cost' => $baseCost > 0 ? $baseCost : $existing->base_cost,
                            'min_stock' => $minStock >= 0 ? $minStock : $existing->min_stock,
                            'description' => $description ?: $existing->description,
                        ]);

                        if ($materialId) {
                            $costModel = $existing->costModels()->firstOrCreate(
                                ['is_active' => true],
                                [
                                    'business_id' => $business->id,
                                    'name' => "Model HPP Utama - {$existing->name}",
                                    'method' => CostModel::METHOD_RECIPE_BOM,
                                ]
                            );
                            $bomHeader = $costModel->bomHeaders()->firstOrCreate(
                                ['cost_model_id' => $costModel->id],
                                ['type' => BomHeader::TYPE_RECIPE, 'name' => "Resep {$existing->name}", 'level' => 1]
                            );
                            $bomHeader->items()->updateOrCreate(
                                ['material_id' => $materialId],
                                ['quantity' => 1, 'unit_id' => $unitId, 'is_mandatory' => true]
                            );
                        }

                        $updated++;
                    } else {
                        $skipped++;
                    }
                    continue;
                }

                // Create new Product
                $slug = Str::slug($name);
                if (Product::withoutGlobalScopes()->where('business_id', $business->id)->where('slug', $slug)->exists()) {
                    $slug .= '-' . Str::lower(Str::random(4));
                }

                $product = Product::create([
                    'business_id' => $business->id,
                    'name' => $name,
                    'slug' => $slug,
                    'code' => $sku,
                    'category_id' => $categoryId,
                    'output_unit_id' => $unitId,
                    'selling_price' => $sellingPrice,
                    'base_cost' => $baseCost,
                    'min_stock' => $minStock,
                    'description' => $description ?: null,
                    'direct_material_id' => $materialId,
                    'is_active' => true,
                ]);

                // Create default active CostModel
                $costModel = $product->costModels()->create([
                    'business_id' => $business->id,
                    'name' => "Model HPP Utama - {$product->name}",
                    'method' => CostModel::METHOD_RECIPE_BOM,
                    'is_active' => true,
                ]);

                // Link Material if provided
                if ($materialId) {
                    $bomHeader = $costModel->bomHeaders()->create([
                        'type' => BomHeader::TYPE_RECIPE,
                        'name' => "Resep {$product->name}",
                        'level' => 1,
                    ]);
                    $bomHeader->items()->create([
                        'material_id' => $materialId,
                        'quantity' => 1,
                        'unit_id' => $unitId,
                        'is_mandatory' => true,
                    ]);
                }

                $imported++;
            }
        });

        return [
            'imported' => $imported,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    // ── Recipe / BOM Parsing & Preview ────────────────────────────────────────

    /**
     * Parse and validate uploaded recipe / BOM file before saving.
     *
     * @return array{
     *     total_rows: int,
     *     valid_count: int,
     *     duplicate_count: int,
     *     error_count: int,
     *     rows: array<int, array<string, mixed>>
     * }
     */
    public function parseAndPreviewRecipes(Business $business, UploadedFile $file): array
    {
        $rawRows = $this->readSpreadsheetRows($file);
        if (empty($rawRows)) {
            return [
                'total_rows' => 0,
                'valid_count' => 0,
                'duplicate_count' => 0,
                'error_count' => 0,
                'rows' => [],
            ];
        }

        // Preload products in business
        $products = Product::withoutGlobalScopes()->where('business_id', $business->id)->with(['costModels.bomHeaders.items'])->get();
        $productBySku = [];
        $productByName = [];
        foreach ($products as $p) {
            if ($p->code) {
                $productBySku[strtolower(trim($p->code))] = $p;
            }
            $productByName[strtolower(trim($p->name))] = $p;
        }

        // Preload materials in business - STRICT Master Data Requirement
        $materials = Material::withoutGlobalScopes()->where('business_id', $business->id)->with('unit')->get();
        $materialBySku = [];
        $materialByName = [];
        foreach ($materials as $m) {
            if ($m->code) {
                $materialBySku[strtolower(trim($m->code))] = $m;
            }
            $materialByName[strtolower(trim($m->name))] = $m;
        }

        // Preload units
        $units = Unit::available()->get();
        $unitMap = [];
        foreach ($units as $u) {
            $unitMap[strtolower($u->code)] = $u;
            $unitMap[strtolower($u->name)] = $u;
        }

        $parsedRows = [];
        $seenPairs = [];
        $validCount = 0;
        $duplicateCount = 0;
        $errorCount = 0;

        foreach ($rawRows as $index => $row) {
            $rowNumber = $index + 2;
            $prodIdent = trim((string) ($row['product_ident'] ?? $row['product_name_or_sku'] ?? $row['product'] ?? ''));
            $matIdent = trim((string) ($row['material_ident'] ?? $row['material_name_or_sku'] ?? $row['material'] ?? ''));
            $quantity = (float) ($row['quantity'] ?? 0);
            $unitStr = trim((string) ($row['unit'] ?? ''));
            $waste = (float) ($row['waste_percentage'] ?? $row['waste'] ?? 0);
            $notes = trim((string) ($row['notes'] ?? ''));

            $errors = [];
            $status = 'valid';
            $statusReason = 'Siap ditambahkan ke resep BOM produk.';

            // 1. Resolve Product
            $lowerProd = strtolower($prodIdent);
            $product = $productBySku[$lowerProd] ?? $productByName[$lowerProd] ?? null;
            if (!$product) {
                $errors[] = "Produk '{$prodIdent}' tidak ditemukan di katalog bisnis Anda.";
            }

            // 2. Resolve Material (Strict Master Data Check)
            $lowerMat = strtolower($matIdent);
            $material = $materialBySku[$lowerMat] ?? $materialByName[$lowerMat] ?? null;
            if (!$material) {
                $errors[] = "Bahan baku '{$matIdent}' belum terdaftar di Master Data. Daftarkan di Master Data terlebih dahulu.";
            }

            // 3. Resolve Unit
            $resolvedUnit = $unitMap[strtolower($unitStr)] ?? $material?->unit ?? $unitMap['pcs'] ?? null;
            if (!$resolvedUnit) {
                $resolvedUnit = Unit::firstOrCreate(['code' => 'pcs'], ['name' => 'Pcs', 'category' => 'quantity']);
            }

            // 4. Validate quantity
            if ($quantity <= 0) {
                $errors[] = 'Jumlah pemakaian bahan harus lebih besar dari 0.';
            }
            if ($waste < 0 || $waste > 100) {
                $errors[] = 'Persentase susut / waste harus antara 0 - 100%.';
            }

            // 5. Check Duplicate in Recipe
            $isDbDuplicate = false;
            $isFileDuplicate = false;

            if ($product && $material) {
                $pairKey = $product->id . '_' . $material->id;

                // Check in existing BOM items of active cost model
                $activeModel = $product->costModels->firstWhere('is_active', true) ?? $product->costModels->first();
                $activeBomHeader = $activeModel?->bomHeaders->first();
                if ($activeBomHeader && $activeBomHeader->items->contains('material_id', $material->id)) {
                    $isDbDuplicate = true;
                    $statusReason = "Bahan '{$material->name}' sudah ada di resep produk '{$product->name}'.";
                } elseif (isset($seenPairs[$pairKey])) {
                    $isFileDuplicate = true;
                    $statusReason = "Duplikat bahan '{$material->name}' pada produk '{$product->name}' di baris sebelumnya.";
                }

                $seenPairs[$pairKey] = true;
            }

            if (!empty($errors)) {
                $status = 'error';
                $statusReason = implode(' ', $errors);
                $errorCount++;
            } elseif ($isDbDuplicate || $isFileDuplicate) {
                $status = 'duplicate';
                $duplicateCount++;
            } else {
                $validCount++;
            }

            $parsedRows[] = [
                'row_number' => $rowNumber,
                'product_ident' => $prodIdent,
                'product_id' => $product?->id,
                'product_name' => $product?->name ?? $prodIdent,
                'material_ident' => $matIdent,
                'material_id' => $material?->id,
                'material_name' => $material?->name ?? $matIdent,
                'quantity' => $quantity,
                'unit' => $resolvedUnit->code ?? 'pcs',
                'unit_id' => $resolvedUnit->id ?? null,
                'waste_percentage' => $waste,
                'notes' => $notes,
                'status' => $status,
                'status_reason' => $statusReason,
                'errors' => $errors,
            ];
        }

        return [
            'total_rows' => count($parsedRows),
            'valid_count' => $validCount,
            'duplicate_count' => $duplicateCount,
            'error_count' => $errorCount,
            'rows' => $parsedRows,
        ];
    }

    /**
     * Execute batch recipe / BOM import.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  string  $duplicateStrategy  'skip' | 'update'
     * @return array{imported: int, updated: int, skipped: int}
     */
    public function executeRecipeImport(Business $business, array $rows, string $duplicateStrategy = 'skip'): array
    {
        $imported = 0;
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($business, $rows, $duplicateStrategy, &$imported, &$updated, &$skipped): void {
            foreach ($rows as $row) {
                $status = $row['status'] ?? 'valid';
                if ($status === 'error') {
                    $skipped++;
                    continue;
                }

                $productId = $row['product_id'] ?? null;
                $materialId = $row['material_id'] ?? null;
                $quantity = (float) ($row['quantity'] ?? 0);
                $unitId = $row['unit_id'] ?? null;
                $waste = (float) ($row['waste_percentage'] ?? 0);
                $notes = !empty($row['notes']) ? (string) $row['notes'] : null;

                if (!$productId || !$materialId || $quantity <= 0) {
                    $skipped++;
                    continue;
                }

                $product = Product::withoutGlobalScopes()->where('business_id', $business->id)->find($productId);
                if (!$product) {
                    $skipped++;
                    continue;
                }

                // Ensure CostModel exists
                $costModel = $product->costModels()->firstOrCreate(
                    ['is_active' => true],
                    [
                        'business_id' => $business->id,
                        'name' => "Model HPP Utama - {$product->name}",
                        'method' => CostModel::METHOD_RECIPE_BOM,
                    ]
                );

                // Ensure BomHeader exists
                $bomHeader = $costModel->bomHeaders()->firstOrCreate(
                    ['cost_model_id' => $costModel->id],
                    [
                        'type' => BomHeader::TYPE_RECIPE,
                        'name' => "Resep / BOM {$product->name}",
                        'level' => 1,
                    ]
                );

                $existingItem = $bomHeader->items()->where('material_id', $materialId)->first();

                if ($existingItem) {
                    if ($duplicateStrategy === 'update') {
                        $existingItem->update([
                            'quantity' => $quantity,
                            'unit_id' => $unitId ?: $existingItem->unit_id,
                            'waste_percentage' => $waste,
                            'notes' => $notes ?: $existingItem->notes,
                        ]);
                        $updated++;
                    } else {
                        $skipped++;
                    }
                    continue;
                }

                $bomHeader->items()->create([
                    'material_id' => $materialId,
                    'quantity' => $quantity,
                    'unit_id' => $unitId,
                    'waste_percentage' => $waste,
                    'notes' => $notes,
                    'is_mandatory' => true,
                ]);

                $imported++;
            }
        });

        return [
            'imported' => $imported,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    // ── Inventory / Stock Parsing & Preview ───────────────────────────────────

    /**
     * Parse and validate uploaded inventory / initial stock spreadsheet.
     *
     * @return array{
     *     total_rows: int,
     *     valid_count: int,
     *     duplicate_count: int,
     *     error_count: int,
     *     rows: array<int, array<string, mixed>>
     * }
     */
    public function parseAndPreviewInventory(Business $business, UploadedFile $file): array
    {
        $rawRows = $this->readSpreadsheetRows($file);
        if (empty($rawRows)) {
            return [
                'total_rows' => 0,
                'valid_count' => 0,
                'duplicate_count' => 0,
                'error_count' => 0,
                'rows' => [],
            ];
        }

        // Preload Locations in business
        $locations = Location::withoutGlobalScopes()->where('business_id', $business->id)->where('is_active', true)->get();
        $locationMap = [];
        foreach ($locations as $loc) {
            $locationMap[strtolower(trim($loc->name))] = $loc;
            if ($loc->code) {
                $locationMap[strtolower(trim($loc->code))] = $loc;
            }
        }
        $defaultLocation = $locations->first();

        // Preload Materials in business - Strict Master Data Requirement (Rule 01, 04, 07)
        $materials = Material::withoutGlobalScopes()->where('business_id', $business->id)->with('unit', 'latestPrice')->get();
        $materialBySku = [];
        $materialByName = [];
        foreach ($materials as $m) {
            if ($m->code) {
                $materialBySku[strtolower(trim($m->code))] = $m;
            }
            $materialByName[strtolower(trim($m->name))] = $m;
        }

        // Preload existing material stocks in DB
        $existingStocks = InventoryStock::withoutGlobalScopes()
            ->where('business_id', $business->id)
            ->whereNotNull('material_id')
            ->get();
        $stockMap = [];
        foreach ($existingStocks as $st) {
            $stockMap[$st->material_id . '_' . $st->location_id] = $st;
        }

        // Preload units
        $units = Unit::available()->get();
        $unitMap = [];
        foreach ($units as $u) {
            $unitMap[strtolower($u->code)] = $u;
            $unitMap[strtolower($u->name)] = $u;
        }

        $parsedRows = [];
        $seenPairs = [];
        $validCount = 0;
        $duplicateCount = 0;
        $errorCount = 0;

        foreach ($rawRows as $index => $row) {
            $rowNumber = $index + 2;
            $itemIdent = trim((string) ($row['name'] ?? $row['material_name_or_sku'] ?? $row['material'] ?? $row['item_name_or_sku'] ?? $row['product'] ?? ''));
            $sku = trim((string) ($row['sku'] ?? $row['code'] ?? ''));
            $locIdent = trim((string) ($row['location'] ?? $row['gudang'] ?? ''));
            $quantity = (float) ($row['quantity'] ?? $row['stok'] ?? $row['jumlah'] ?? 0);
            $unitStr = trim((string) ($row['unit'] ?? $row['satuan'] ?? ''));
            $unitCost = (float) ($row['unit_cost'] ?? $row['base_cost'] ?? $row['hpp'] ?? $row['harga_beli'] ?? 0);
            $notes = trim((string) ($row['notes'] ?? $row['catatan'] ?? ''));

            $errors = [];
            $status = 'valid';
            $statusReason = 'Siap dicatat ke mutasi saldo awal stok bahan baku.';

            // 1. Validate Item Name / SKU
            if ($itemIdent === '' && $sku === '') {
                $errors[] = 'Nama bahan baku atau kode/SKU tidak boleh kosong.';
            }

            // 2. Resolve Material (Master Data MUST exist - Rule 04 & 07)
            $resolvedMaterial = null;
            $lookupKeySku = $sku !== '' ? strtolower($sku) : '';
            $lookupKeyName = $itemIdent !== '' ? strtolower($itemIdent) : '';

            if ($lookupKeySku !== '' && isset($materialBySku[$lookupKeySku])) {
                $resolvedMaterial = $materialBySku[$lookupKeySku];
            } elseif ($lookupKeyName !== '' && isset($materialByName[$lookupKeyName])) {
                $resolvedMaterial = $materialByName[$lookupKeyName];
            }

            if (!$resolvedMaterial) {
                $identDisplay = $itemIdent ?: $sku;
                $errors[] = "Bahan baku '{$identDisplay}' belum terdaftar di Master Data. Sesuai aturan sistem, Inventory berbasis Material. Silakan buat atau import Bahan Baku terlebih dahulu.";
            }

            // 3. Resolve Location
            $resolvedLocation = null;
            if ($locIdent !== '') {
                $resolvedLocation = $locationMap[strtolower($locIdent)] ?? null;
                if (!$resolvedLocation) {
                    $errors[] = "Lokasi / Cabang '{$locIdent}' tidak ditemukan di bisnis Anda.";
                }
            } else {
                $resolvedLocation = $defaultLocation;
                if (!$resolvedLocation) {
                    $errors[] = 'Belum ada data lokasi/outlet terdaftar di bisnis Anda.';
                }
            }

            // 4. Resolve Unit
            $resolvedUnit = $unitMap[strtolower($unitStr)] 
                ?? $resolvedMaterial?->unit 
                ?? $unitMap['pcs'] 
                ?? null;

            if (!$resolvedUnit) {
                $resolvedUnit = Unit::firstOrCreate(['code' => 'pcs'], ['name' => 'Pcs', 'category' => 'quantity']);
            }

            // 5. Validate Quantity & Cost
            if ($quantity <= 0) {
                $errors[] = 'Jumlah stok harus lebih besar dari 0.';
            }
            if ($unitCost < 0) {
                $errors[] = 'Biaya pokok / HPP per unit tidak boleh negatif.';
            }

            // 6. Duplicate Checks (Level A: File Duplicate, Level B: Database Duplicate)
            $isDbDuplicate = false;
            $isFileDuplicate = false;

            $effectiveMatId = $resolvedMaterial?->id;
            $effectiveLocId = $resolvedLocation?->id;

            if ($effectiveMatId && $effectiveLocId) {
                $pairKey = $effectiveMatId . '_' . $effectiveLocId;

                // Check in-file duplicate
                if (isset($seenPairs[$pairKey])) {
                    $isFileDuplicate = true;
                    $statusReason = 'Duplikat di baris sebelumnya dalam file untuk bahan dan lokasi yang sama.';
                }

                // Check DB duplicate
                if (isset($stockMap[$pairKey])) {
                    $currentStock = $stockMap[$pairKey];
                    if ((float) $currentStock->quantity > 0) {
                        $isDbDuplicate = true;
                        $statusReason = "Sudah memiliki saldo stok di lokasi ini ({$currentStock->quantity} {$resolvedUnit->code}).";
                    }
                }

                $seenPairs[$pairKey] = true;
            }

            if (!empty($errors)) {
                $status = 'error';
                $statusReason = implode(' ', $errors);
                $errorCount++;
            } elseif ($isFileDuplicate || $isDbDuplicate) {
                $status = 'duplicate';
                $duplicateCount++;
            } else {
                $validCount++;
            }

            $displayName = $resolvedMaterial?->name ?? ($itemIdent ?: $sku);
            $displaySku = $resolvedMaterial?->code ?? $sku;
            $resolvedCost = $unitCost > 0 ? $unitCost : (float) ($resolvedMaterial?->latestPrice?->purchase_price ?? 0);

            $parsedRows[] = [
                'row_number' => $rowNumber,
                'item_name' => $displayName,
                'sku' => $displaySku,
                'material_id' => $resolvedMaterial?->id,
                'product_id' => null,
                'location_name' => $resolvedLocation?->name ?? $locIdent,
                'location_id' => $resolvedLocation?->id,
                'quantity' => $quantity,
                'unit' => $resolvedUnit->code ?? 'pcs',
                'unit_id' => $resolvedUnit->id ?? null,
                'unit_cost' => $resolvedCost,
                'notes' => $notes ?: 'Saldo Awal Import Excel',
                'status' => $status,
                'status_reason' => $statusReason,
                'errors' => $errors,
            ];
        }

        return [
            'total_rows' => count($parsedRows),
            'valid_count' => $validCount,
            'duplicate_count' => $duplicateCount,
            'error_count' => $errorCount,
            'rows' => $parsedRows,
        ];
    }

    /**
     * Execute batch inventory stock import using existing StockService and transaction workflow.
     * Follows Rule 01, 04, 07: Strictly Material-based inventory. NO auto-creation of master data.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  string  $duplicateStrategy  'skip' | 'adjust' | 'initial'
     * @return array{imported: int, updated: int, skipped: int}
     */
    public function executeInventoryImport(
        Business $business,
        array $rows,
        string $duplicateStrategy = 'skip',
        ?string $userId = null
    ): array {
        $imported = 0;
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($business, $rows, $duplicateStrategy, $userId, &$imported, &$updated, &$skipped): void {
            foreach ($rows as $row) {
                $status = $row['status'] ?? 'valid';
                if ($status === 'error') {
                    $skipped++;
                    continue;
                }

                $materialId = $row['material_id'] ?? null;
                $locationId = $row['location_id'] ?? null;
                $quantity = (float) ($row['quantity'] ?? 0);
                $unitCost = (float) ($row['unit_cost'] ?? 0);
                $notes = !empty($row['notes']) ? (string) $row['notes'] : 'Import Saldo Stok Bahan Baku Excel';

                if (!$materialId || !$locationId || $quantity <= 0) {
                    $skipped++;
                    continue;
                }

                // Verify Material exists in business (Rule 07)
                $material = Material::withoutGlobalScopes()
                    ->where('business_id', $business->id)
                    ->find($materialId);

                if (!$material) {
                    $skipped++;
                    continue;
                }

                // Check existing stock in location
                $existingStock = InventoryStock::withoutGlobalScopes()
                    ->where('business_id', $business->id)
                    ->where('location_id', $locationId)
                    ->where('material_id', $materialId)
                    ->first();

                $isDuplicate = $existingStock && (float) $existingStock->quantity > 0;

                if ($isDuplicate && $duplicateStrategy === 'skip') {
                    $skipped++;
                    continue;
                }

                $movementType = ($isDuplicate && $duplicateStrategy === 'adjust') 
                    ? StockMovement::TYPE_ADJUSTMENT 
                    : StockMovement::TYPE_INITIAL;

                // Mutate stock via existing atomic StockService on Master Material
                $this->stockService->recordMovement(
                    businessId: $business->id,
                    locationId: $locationId,
                    movementType: $movementType,
                    quantityChange: $quantity,
                    unitCost: $unitCost,
                    referenceId: null,
                    referenceNumber: null,
                    notes: $notes,
                    userId: $userId,
                    materialId: $materialId
                );

                if ($isDuplicate) {
                    $updated++;
                } else {
                    $imported++;
                }
            }
        });

        return [
            'imported' => $imported,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    // ── Internal Helpers ──────────────────────────────────────────────────────

    /**
     * Read and normalize raw rows from uploaded spreadsheet.
     *
     * @return array<int, array<string, mixed>>
     */
    private function readSpreadsheetRows(UploadedFile $file): array
    {
        $realPath = $file->getRealPath();
        $spreadsheet = IOFactory::load($realPath);
        $worksheet = $spreadsheet->getActiveSheet();
        $sheetData = $worksheet->toArray(null, true, true, true);

        if (empty($sheetData)) {
            return [];
        }

        // Find header row (first row with non-empty cells)
        $headerRow = null;
        $dataRows = [];

        foreach ($sheetData as $row) {
            $nonEmpty = array_filter($row, fn ($val) => $val !== null && trim((string) $val) !== '');
            if (empty($nonEmpty)) {
                continue;
            }

            if ($headerRow === null) {
                $headerRow = $row;
                continue;
            }

            $dataRows[] = $row;
        }

        if (!$headerRow) {
            return [];
        }

        // Normalize header keys
        $colMap = [];
        foreach ($headerRow as $colLetter => $headerText) {
            $normalized = $this->normalizeHeaderKey((string) $headerText);
            if ($normalized !== '') {
                $colMap[$colLetter] = $normalized;
            }
        }

        $result = [];
        foreach ($dataRows as $row) {
            $rowData = [];
            $hasAnyValue = false;

            foreach ($colMap as $colLetter => $key) {
                $val = $row[$colLetter] ?? null;
                if ($val !== null && trim((string) $val) !== '') {
                    $hasAnyValue = true;
                }
                $rowData[$key] = $val;
            }

            if ($hasAnyValue) {
                $result[] = $rowData;
            }
        }

        return $result;
    }

    /**
     * Match variations of column headers to standard keys.
     */
    private function normalizeHeaderKey(string $text): string
    {
        $t = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $text)));

        // Check notes & instructions first to avoid colliding with 'catatan bahan'
        if (str_contains($t, 'catatan') || str_contains($t, 'notes') || str_contains($t, 'instruksi') || str_contains($t, 'keteranganbahan') || str_contains($t, 'alasan')) {
            return 'notes';
        }

        // Location aliases
        if (str_contains($t, 'lokasi') || str_contains($t, 'location') || str_contains($t, 'outlet') || str_contains($t, 'gudang') || str_contains($t, 'cabang')) {
            return 'location';
        }

        // Material relationship alias
        if (str_contains($t, 'bahanbakuter') || str_contains($t, 'relasibahan') || str_contains($t, 'materialterkait')) {
            return 'material_ident';
        }

        // Recipe specific aliases (check before generic 'nama' / 'produk')
        if (str_contains($t, 'produkjadi') || str_contains($t, 'outputproduct') || str_contains($t, 'resepuntuk')) {
            return 'product_name_or_sku';
        }
        if (str_contains($t, 'namabahan') || str_contains($t, 'bahanbaku') || str_contains($t, 'material') || str_contains($t, 'ingredient') || str_contains($t, 'komponen') || $t === 'bahan') {
            return 'material_name_or_sku';
        }
        if (str_contains($t, 'jumlah') || str_contains($t, 'qty') || str_contains($t, 'quantity') || str_contains($t, 'pemakaian') || str_contains($t, 'takaran') || str_contains($t, 'stok') || str_contains($t, 'saldo')) {
            return 'quantity';
        }
        if (str_contains($t, 'susut') || str_contains($t, 'waste') || str_contains($t, 'scrap')) {
            return 'waste_percentage';
        }

        // Product specific aliases
        if (str_contains($t, 'namaproduk') || $t === 'produk' || $t === 'product' || $t === 'nama' || $t === 'item') {
            return 'name';
        }
        if (str_contains($t, 'sku') || str_contains($t, 'kodeproduk') || $t === 'kode' || $t === 'code') {
            return 'sku';
        }
        if (str_contains($t, 'kategori') || str_contains($t, 'category')) {
            return 'category';
        }
        if (str_contains($t, 'satuan') || str_contains($t, 'unit') || str_contains($t, 'uom')) {
            return 'unit';
        }
        if (str_contains($t, 'supplier') || str_contains($t, 'pemasok') || str_contains($t, 'vendor')) {
            return 'supplier';
        }
        if (str_contains($t, 'hargabeli') || str_contains($t, 'purchaseprice') || str_contains($t, 'hargastandar')) {
            return 'purchase_price';
        }
        if (str_contains($t, 'hargajual') || str_contains($t, 'sellingprice') || str_contains($t, 'harga')) {
            return 'selling_price';
        }
        if (str_contains($t, 'hpp') || str_contains($t, 'modal') || str_contains($t, 'basecost') || str_contains($t, 'cost') || str_contains($t, 'biayapokok') || str_contains($t, 'unitcost')) {
            return 'unit_cost';
        }
        if (str_contains($t, 'stokmin') || str_contains($t, 'minstock') || str_contains($t, 'minimal')) {
            return 'min_stock';
        }
        if (str_contains($t, 'deskripsi') || str_contains($t, 'description') || str_contains($t, 'keterangan')) {
            return 'description';
        }

        return $t;
    }
}
