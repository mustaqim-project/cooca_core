<?php

declare(strict_types=1);

namespace App\Domain\Ai;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class MarkdownKnowledgeService
{
    private const CACHE_PREFIX = 'cooca_system_knowledge_v1_';

    /**
     * Map of keywords to direct action links in COOCA.
     *
     * @var array<string, array{label: string, url: string, icon: string, roles: array<string>}>
     */
    private array $actionLinkRegistry = [
        'kasir' => ['label' => 'Buka Terminal Kasir POS', 'url' => '/pos', 'icon' => 'shopping-cart', 'roles' => ['all']],
        'pos' => ['label' => 'Buka Terminal Kasir POS', 'url' => '/pos', 'icon' => 'shopping-cart', 'roles' => ['all']],
        'printer' => ['label' => 'Pengaturan Printer Kasir', 'url' => '/settings/pos/printers', 'icon' => 'printer', 'roles' => ['owner', 'admin', 'supervisor']],
        'shift' => ['label' => 'Rekap Kasir & Tutup Shift', 'url' => '/pos/shifts', 'icon' => 'clock', 'roles' => ['all']],
        'stok' => ['label' => 'Katalog Produk & Stok', 'url' => '/products', 'icon' => 'package', 'roles' => ['all']],
        'bahan' => ['label' => 'Master Bahan Baku & BOM', 'url' => '/materials', 'icon' => 'layers', 'roles' => ['all']],
        'gudang' => ['label' => 'Manajemen Gudang & Mutasi', 'url' => '/inventory/movements', 'icon' => 'warehouse', 'roles' => ['owner', 'admin', 'warehouse']],
        'whatsapp' => ['label' => 'Pusat Integrasi WhatsApp', 'url' => '/settings/whatsapp', 'icon' => 'message-circle', 'roles' => ['owner', 'admin']],
        'wa' => ['label' => 'Pusat Integrasi WhatsApp', 'url' => '/settings/whatsapp', 'icon' => 'message-circle', 'roles' => ['owner', 'admin']],
        'marketplace' => ['label' => 'Integrasi Toko Online / Marketplace', 'url' => '/marketplace/accounts', 'icon' => 'globe', 'roles' => ['owner', 'admin']],
        'jurnal' => ['label' => 'Buku Jurnal Akuntansi', 'url' => '/accounting/journals', 'icon' => 'book-open', 'roles' => ['owner', 'accountant', 'admin']],
        'buku_besar' => ['label' => 'Buku Besar & COA', 'url' => '/accounting/accounts', 'icon' => 'file-text', 'roles' => ['owner', 'accountant', 'admin']],
        'laba_rugi' => ['label' => 'Laporan Laba Rugi Finansial', 'url' => '/reports/profit-loss', 'icon' => 'bar-chart-2', 'roles' => ['owner', 'accountant', 'admin']],
        'pajak' => ['label' => 'Pengaturan & Rekap Pajak', 'url' => '/tax/settings', 'icon' => 'percent', 'roles' => ['owner', 'accountant', 'admin']],
        'gaji' => ['label' => 'Penggajian & Komisi (HRM)', 'url' => '/hrm/payrolls', 'icon' => 'users', 'roles' => ['owner', 'admin', 'hr']],
        'karyawan' => ['label' => 'Kelola Staf & Otoritas', 'url' => '/settings/users', 'icon' => 'user-check', 'roles' => ['owner', 'admin']],
        'supplier' => ['label' => 'Direktori Supplier & PO', 'url' => '/suppliers', 'icon' => 'truck', 'roles' => ['owner', 'admin', 'purchasing']],
        'po' => ['label' => 'Pesanan Pembelian (PO)', 'url' => '/purchase-orders', 'icon' => 'file-plus', 'roles' => ['owner', 'admin', 'purchasing']],
        'pelanggan' => ['label' => 'Direktori Pelanggan & Piutang', 'url' => '/customers', 'icon' => 'user', 'roles' => ['all']],
        'piutang' => ['label' => 'Daftar Piutang & Pengingat', 'url' => '/invoices', 'icon' => 'alert-circle', 'roles' => ['all']],
        'storefront' => ['label' => 'Toko Online Publik (Storefront)', 'url' => '/storefront/settings', 'icon' => 'shopping-bag', 'roles' => ['owner', 'admin']],
        'biteship' => ['label' => 'Pengaturan Ekspedisi Biteship', 'url' => '/settings/shipping', 'icon' => 'navigation', 'roles' => ['owner', 'admin']],
        'sop' => ['label' => 'Pusat SOP Usaha Internal', 'url' => '/settings/sop', 'icon' => 'file-check', 'roles' => ['owner', 'admin', 'supervisor']],
        'meja' => ['label' => 'Manajemen Meja Dine-In', 'url' => '/pos/tables', 'icon' => 'grid', 'roles' => ['all']],
        'paket' => ['label' => 'Paket Langganan & Kuota', 'url' => '/billing/packages', 'icon' => 'shield-check', 'roles' => ['owner']],
    ];

    /**
     * Get all indexed system knowledge chunks (auto-cached with file hash invalidation).
     *
     * @return array<int, array{
     *     source_file: string,
     *     module: string,
     *     title: string,
     *     content: string,
     *     sectors: array<string>,
     *     roles: array<string>,
     *     keywords: string,
     *     actions: array<array{label: string, url: string, icon: string}>
     * }>
     */
    public function getSystemKnowledge(): array
    {
        $docsPath = base_path('docs');
        $files = $this->collectDocFiles($docsPath);

        $hashKey = $this->calculateFilesHash($files);
        $cacheKey = self::CACHE_PREFIX . $hashKey;

        return Cache::remember($cacheKey, 86400, function () use ($files): array {
            $allKnowledge = [];

            foreach ($files as $filePath) {
                $fileContent = (string) @file_get_contents($filePath);
                if (empty(trim($fileContent))) {
                    continue;
                }

                $fileName = basename($filePath);
                $moduleKey = pathinfo($fileName, PATHINFO_FILENAME);
                $chunks = $this->parseMarkdownToChunks($filePath, $fileContent, $moduleKey);

                foreach ($chunks as $c) {
                    $allKnowledge[] = $c;
                }
            }

            return $allKnowledge;
        });
    }

    /**
     * Match and return relevant action links based on query or context.
     *
     * @param array<string> $allowedRoles
     * @return array<int, array{label: string, url: string, icon: string}>
     */
    public function resolveActionLinks(string $text, array $allowedRoles = ['all']): array
    {
        $textLower = strtolower($text);
        $matched = [];

        foreach ($this->actionLinkRegistry as $keyword => $action) {
            if (str_contains($textLower, $keyword)) {
                // Check role compatibility
                $roles = $action['roles'];
                $isAllowed = in_array('all', $roles, true) || in_array('all', $allowedRoles, true);
                if (!$isAllowed) {
                    foreach ($allowedRoles as $r) {
                        if (in_array($r, $roles, true)) {
                            $isAllowed = true;
                            break;
                        }
                    }
                }

                if ($isAllowed) {
                    $matched[$action['url']] = [
                        'label' => $action['label'],
                        'url' => $action['url'],
                        'icon' => $action['icon'],
                    ];
                }
            }
        }

        return array_values($matched);
    }

    /**
     * Collect markdown guide files from `docs/` and `docs/system/modules/`.
     *
     * @return array<int, string>
     */
    private function collectDocFiles(string $baseDocsPath): array
    {
        $files = [];

        // 1. Root guides
        $rootGuides = [
            $baseDocsPath . '/SYSTEM_GUIDE.md',
            $baseDocsPath . '/PANDUAN_SISTEM_KEUANGAN_FINANCE.md',
            $baseDocsPath . '/ANALISA_MODUL_DAN_FITUR_COOCA.md',
        ];

        foreach ($rootGuides as $rg) {
            if (file_exists($rg)) {
                $files[] = $rg;
            }
        }

        // 2. System modules
        $modulesDir = $baseDocsPath . '/system/modules';
        if (is_dir($modulesDir)) {
            $found = glob($modulesDir . '/*.md');
            if ($found) {
                foreach ($found as $m) {
                    $files[] = $m;
                }
            }
        }

        return $files;
    }

    /**
     * Calculate hash of file modification timestamps for cache busting.
     *
     * @param array<int, string> $files
     */
    private function calculateFilesHash(array $files): string
    {
        $timestamps = [];
        foreach ($files as $f) {
            $timestamps[] = $f . '_' . (@filemtime($f) ?: 0);
        }
        return md5(implode('|', $timestamps));
    }

    /**
     * Parse a markdown file into structured knowledge units.
     *
     * @return array<int, array{
     *     source_file: string,
     *     module: string,
     *     title: string,
     *     content: string,
     *     sectors: array<string>,
     *     roles: array<string>,
     *     keywords: string,
     *     actions: array<array{label: string, url: string, icon: string}>
     * }>
     */
    private function parseMarkdownToChunks(string $filePath, string $content, string $moduleKey): array
    {
        $lines = explode("\n", $content);
        $chunks = [];

        $currentTitle = basename($filePath);
        $currentBuffer = [];
        $currentSectors = $this->detectSectorsFromText($moduleKey . ' ' . $content);
        $currentRoles = $this->detectRolesFromText($moduleKey . ' ' . $content);

        foreach ($lines as $line) {
            // If line is Heading 1, 2, or 3 (#, ##, ###)
            if (preg_match('/^(#{1,3})\s+(.+)$/', $line, $hMatches)) {
                if (!empty($currentBuffer)) {
                    $chunkText = implode("\n", $currentBuffer);
                    if (strlen(trim($chunkText)) > 30) {
                        $chunks[] = [
                            'source_file' => basename($filePath),
                            'module' => $moduleKey,
                            'title' => $currentTitle,
                            'content' => trim($chunkText),
                            'sectors' => $currentSectors,
                            'roles' => $currentRoles,
                            'keywords' => $this->extractKeywords($currentTitle . ' ' . $chunkText),
                            'actions' => $this->resolveActionLinks($currentTitle . ' ' . $chunkText),
                        ];
                    }
                    $currentBuffer = [];
                }
                $currentTitle = trim($hMatches[2]);
            } else {
                $currentBuffer[] = $line;
            }
        }

        if (!empty($currentBuffer)) {
            $chunkText = implode("\n", $currentBuffer);
            if (strlen(trim($chunkText)) > 30) {
                $chunks[] = [
                    'source_file' => basename($filePath),
                    'module' => $moduleKey,
                    'title' => $currentTitle,
                    'content' => trim($chunkText),
                    'sectors' => $currentSectors,
                    'roles' => $currentRoles,
                    'keywords' => $this->extractKeywords($currentTitle . ' ' . $chunkText),
                    'actions' => $this->resolveActionLinks($currentTitle . ' ' . $chunkText),
                ];
            }
        }

        return $chunks;
    }

    /**
     * Detect applicable business sectors from text tags or module names.
     *
     * @return array<string>
     */
    private function detectSectorsFromText(string $text): array
    {
        $textLower = strtolower($text);
        $sectors = [];

        $sectorMap = [
            'fnb' => ['fnb', 'restoran', 'kafe', 'cafe', 'makanan', 'minuman', 'dine-in', 'meja', 'dapur', 'kot', 'resep'],
            'workshop' => ['bengkel', 'otomotif', 'servis', 'service', 'mekanik', 'sparepart', 'suku cadang', 'kendaraan', 'motor', 'mobil', 'work order'],
            'retail' => ['ritel', 'retail', 'minimarket', 'toko', 'barcode', 'eceran', 'grosir', 'kasir toko', 'sembako'],
            'laundry' => ['laundry', 'cuci', 'setrika', 'kiloan', 'satuan', 'pewangi', 'rak simpan'],
            'manufacturing' => ['konveksi', 'garmen', 'pabrik', 'manufaktur', 'produksi', 'potong pola', 'jahit', 'mesin', 'labor rate'],
            'service' => ['jasa', 'salon', 'barbershop', 'cuci mobil', 'klinik', 'pet shop', 'proyek', 'kontraktor', 'kursus'],
        ];

        foreach ($sectorMap as $sectorKey => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($textLower, $kw)) {
                    $sectors[] = $sectorKey;
                    break;
                }
            }
        }

        return empty($sectors) ? ['all'] : array_unique($sectors);
    }

    /**
     * Detect applicable roles from text.
     *
     * @return array<string>
     */
    private function detectRolesFromText(string $text): array
    {
        $textLower = strtolower($text);
        $roles = [];

        if (str_contains($textLower, 'kasir') || str_contains($textLower, 'pos') || str_contains($textLower, 'transaksi')) {
            $roles[] = 'cashier';
        }
        if (str_contains($textLower, 'gudang') || str_contains($textLower, 'stok') || str_contains($textLower, 'bahan') || str_contains($textLower, 'grn')) {
            $roles[] = 'warehouse';
        }
        if (str_contains($textLower, 'jurnal') || str_contains($textLower, 'akuntansi') || str_contains($textLower, 'pajak') || str_contains($textLower, 'laba rugi') || str_contains($textLower, 'coa')) {
            $roles[] = 'accountant';
        }
        if (str_contains($textLower, 'gaji') || str_contains($textLower, 'payroll') || str_contains($textLower, 'karyawan') || str_contains($textLower, 'bpjs')) {
            $roles[] = 'hr';
        }

        if (empty($roles) || str_contains($textLower, 'owner') || str_contains($textLower, 'pengaturan') || str_contains($textLower, 'integrasi')) {
            $roles[] = 'owner';
            $roles[] = 'admin';
        }

        return array_unique($roles);
    }

    /**
     * Extract unique keywords from text.
     */
    private function extractKeywords(string $text): string
    {
        $words = preg_split('/[\s,\.\(\)\[\]\{\}\"\'\:\;\-\_\/\?\!\+\=]+/', strtolower($text));
        if (!is_array($words)) {
            return '';
        }

        $stopwords = [
            'dan', 'atau', 'yang', 'di', 'ke', 'dari', 'pada', 'untuk', 'dengan', 'adalah', 'ini', 'itu',
            'sebagai', 'oleh', 'dalam', 'akan', 'harus', 'bisa', 'dapat', 'sudah', 'telah', 'agar', 'supaya',
            'maka', 'jika', 'bila', 'saat', 'ketika', 'setelah', 'sebelum', 'karena', 'sebab', 'the', 'and',
            'is', 'in', 'to', 'for', 'of', 'with', 'on', 'at', 'by', 'from', 'an', 'a',
        ];

        $stopwordMap = array_flip($stopwords);
        $unique = [];

        foreach ($words as $w) {
            $w = trim($w);
            if (strlen($w) >= 3 && !isset($stopwordMap[$w]) && !is_numeric($w)) {
                $unique[$w] = true;
            }
        }

        return implode(' ', array_slice(array_keys($unique), 0, 30));
    }
}
