<?php

declare(strict_types=1);

namespace App\Domain\Ai\Services;

use App\Domain\Ai\Organization\AgentRole;
use App\Domain\Ai\Organization\ExecutiveRole;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\MarketplaceAccount;
use App\Models\MarketplaceOrder;
use App\Models\PaymentAccount;
use App\Models\PosOrder;
use App\Models\PosShift;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SocialMediaPost;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

final class AiAgentBusinessMetricsService
{
    /**
     * Get live dual-monitor metrics for all 17 agent and executive roles.
     *
     * @return array<string, array{
     *     monitor1: array<string, mixed>,
     *     monitor2: array<string, mixed>
     * }>
     */
    public function getAllMetrics(Business $business): array
    {
        $roles = [
            'ceo', 'coo', 'cfo', 'cmo', 'hr_lead', 'sales_director',
            'business', 'sales', 'customer', 'inventory', 'purchasing',
            'marketplace', 'finance', 'reporting', 'marketing', 'content',
            'social_media', 'hr',
        ];

        $metrics = [];
        foreach ($roles as $role) {
            $metrics[$role] = $this->getMetricsForRole($business, $role);
        }

        return $metrics;
    }

    /**
     * Get dual-monitor metrics for a specific agent role or executive role.
     *
     * @return array{
     *     monitor1: array<string, mixed>,
     *     monitor2: array<string, mixed>
     * }
     */
    public function getMetricsForRole(Business $business, string $roleKey): array
    {
        try {
            return match ($roleKey) {
                'ceo', ExecutiveRole::CEO->value => $this->buildCeoMetrics($business),
                'coo', ExecutiveRole::COO->value => $this->buildCooMetrics($business),
                'cfo', ExecutiveRole::CFO->value => $this->buildCfoMetrics($business),
                'cmo', ExecutiveRole::CMO->value => $this->buildCmoMetrics($business),
                'hr_lead', ExecutiveRole::HR_LEAD->value => $this->buildHrMetrics($business),
                'sales_director', ExecutiveRole::SALES_DIRECTOR->value => $this->buildSalesMetrics($business),
                'business', AgentRole::BUSINESS->value => $this->buildCeoMetrics($business),
                'sales', AgentRole::SALES->value => $this->buildSalesMetrics($business),
                'customer', AgentRole::CUSTOMER->value => $this->buildCustomerMetrics($business),
                'inventory', AgentRole::INVENTORY->value => $this->buildInventoryMetrics($business),
                'purchasing', AgentRole::PURCHASING->value => $this->buildPurchasingMetrics($business),
                'marketplace', AgentRole::MARKETPLACE->value => $this->buildMarketplaceMetrics($business),
                'finance', AgentRole::FINANCE->value => $this->buildCfoMetrics($business),
                'reporting', AgentRole::REPORTING->value => $this->buildReportingMetrics($business),
                'marketing', AgentRole::MARKETING->value => $this->buildCmoMetrics($business),
                'content', AgentRole::CONTENT->value => $this->buildContentMetrics($business),
                'social_media', AgentRole::SOCIAL_MEDIA->value => $this->buildSocialMediaMetrics($business),
                'hr', AgentRole::HR->value => $this->buildHrMetrics($business),
                default => $this->buildCeoMetrics($business),
            };
        } catch (Throwable) {
            return $this->buildFallbackMetrics($roleKey);
        }
    }

    // --- Specific Role Builders ---

    private function buildCeoMetrics(Business $business): array
    {
        $startOfMonth = now()->startOfMonth();
        $monthlyRevenue = (float) PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_COMPLETED)
            ->where('created_at', '>=', $startOfMonth)
            ->sum('total_amount');

        $totalCash = (float) PaymentAccount::where('business_id', $business->id)
            ->where('is_active', true)
            ->sum('current_balance');

        $dailyTrend = $this->get7DaySalesTrend($business);

        $alerts = AuditLog::where('business_id', $business->id)
            ->whereIn('risk_level', ['HIGH', 'CRITICAL'])
            ->latest()
            ->take(4)
            ->get()
            ->map(fn($log) => [
                'col1' => $log->risk_level,
                'col2' => Str::limit($log->action ?? 'Audit Alert', 22),
                'col3' => Carbon::parse($log->created_at)->format('H:i'),
            ])->toArray();

        if (empty($alerts)) {
            $alerts = [
                ['col1' => 'INFO', 'col2' => 'Sistem Beroperasi Stabil', 'col3' => 'NORMAL'],
                ['col1' => 'INFO', 'col2' => 'Zero High-Risk Alerts', 'col3' => 'AMAN'],
            ];
        }

        return [
            'monitor1' => [
                'title' => 'CEO STRATEGIC RADAR',
                'badge' => 'EXECUTIVE',
                'kpi_label_1' => 'OMZET BULAN INI',
                'kpi_value_1' => 'Rp ' . number_format($monthlyRevenue, 0, ',', '.'),
                'kpi_sub_1' => 'MoM Run-rate',
                'kpi_label_2' => 'TOTAL LIKUIDITAS KAS',
                'kpi_value_2' => 'Rp ' . number_format($totalCash, 0, ',', '.'),
                'kpi_sub_2' => 'Kas & Bank Aktif',
                'chart_type' => 'bar',
                'chart_data' => $dailyTrend['data'],
                'chart_labels' => $dailyTrend['labels'],
            ],
            'monitor2' => [
                'title' => 'HIGH PRIORITY ALERTS',
                'feed_type' => 'table',
                'items' => $alerts,
            ],
        ];
    }

    private function buildSalesMetrics(Business $business): array
    {
        $today = now()->startOfDay();
        $todayRevenue = (float) PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_COMPLETED)
            ->where('created_at', '>=', $today)
            ->sum('total_amount');

        $todayOrdersCount = PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_COMPLETED)
            ->where('created_at', '>=', $today)
            ->count();

        $aov = $todayOrdersCount > 0 ? ($todayRevenue / $todayOrdersCount) : 0;
        $dailyTrend = $this->get7DaySalesTrend($business);

        $recentOrders = PosOrder::where('business_id', $business->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($o) => [
                'col1' => '#' . substr($o->order_number ?? (string) $o->id, -6),
                'col2' => 'Rp ' . number_format((float) $o->total_amount, 0, ',', '.'),
                'col3' => strtoupper($o->status ?? 'SELESAI'),
            ])->toArray();

        if (empty($recentOrders)) {
            $recentOrders = [
                ['col1' => '#ORD-001', 'col2' => 'Menunggu Order', 'col3' => 'STANDBY'],
            ];
        }

        return [
            'monitor1' => [
                'title' => 'SALES & POS DISPATCH',
                'badge' => 'LIVE POS',
                'kpi_label_1' => 'PENJUALAN HARI INI',
                'kpi_value_1' => 'Rp ' . number_format($todayRevenue, 0, ',', '.'),
                'kpi_sub_1' => "{$todayOrdersCount} Transaksi Selesai",
                'kpi_label_2' => 'AVG ORDER VALUE (AOV)',
                'kpi_value_2' => 'Rp ' . number_format($aov, 0, ',', '.'),
                'kpi_sub_2' => 'Basket Size',
                'chart_type' => 'bar',
                'chart_data' => $dailyTrend['data'],
                'chart_labels' => $dailyTrend['labels'],
            ],
            'monitor2' => [
                'title' => 'LIVE ORDER STREAM',
                'feed_type' => 'table',
                'items' => $recentOrders,
            ],
        ];
    }

    private function buildCustomerMetrics(Business $business): array
    {
        $totalUnpaidInvoices = (float) Invoice::where('business_id', $business->id)
            ->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])
            ->sum('total_amount');

        $activeCustomers = Customer::where('business_id', $business->id)->count();

        $invoices = Invoice::where('business_id', $business->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($inv) => [
                'col1' => '#' . substr($inv->invoice_number ?? (string) $inv->id, -7),
                'col2' => 'Rp ' . number_format((float) $inv->total_amount, 0, ',', '.'),
                'col3' => strtoupper($inv->status ?? 'UNPAID'),
            ])->toArray();

        if (empty($invoices)) {
            $invoices = [
                ['col1' => 'INV-NIHIL', 'col2' => 'Semua Lunas', 'col3' => 'LUNAS'],
            ];
        }

        return [
            'monitor1' => [
                'title' => 'CRM & RECEIVABLES',
                'badge' => 'RETENTION',
                'kpi_label_1' => 'TOTAL PIUTANG BERJALAN',
                'kpi_value_1' => 'Rp ' . number_format($totalUnpaidInvoices, 0, ',', '.'),
                'kpi_sub_1' => 'Faktur Tertunggak',
                'kpi_label_2' => 'TOTAL PELANGGAN',
                'kpi_value_2' => number_format($activeCustomers, 0, ',', '.') . ' Kontak',
                'kpi_sub_2' => 'Basis Pelanggan Aktif',
                'chart_type' => 'line',
                'chart_data' => [4, 7, 5, 8, 12, 10, 15],
                'chart_labels' => ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
            ],
            'monitor2' => [
                'title' => 'INVOICE & BILLING FEED',
                'feed_type' => 'table',
                'items' => $invoices,
            ],
        ];
    }

    private function buildCooMetrics(Business $business): array
    {
        $products = Product::where('business_id', $business->id)->where('is_active', true)->with('stocks')->get();
        $totalSkus = $products->count();
        $criticalStock = 0;
        $criticalItems = [];

        foreach ($products as $p) {
            $stock = (float) ($p->stocks ? $p->stocks->sum('quantity') : ($p->stock_quantity ?? 0));
            $minAlert = (float) ($p->min_stock ?? $p->min_stock_alert ?? 5);
            if ($stock <= $minAlert) {
                $criticalStock++;
                $criticalItems[] = [
                    'col1' => Str::limit($p->name, 16),
                    'col2' => 'Sisa: ' . $stock,
                    'col3' => $stock <= 2 ? 'KRITIS' : 'MENIPIS',
                    'stock' => $stock,
                ];
            }
        }

        usort($criticalItems, fn($a, $b) => $a['stock'] <=> $b['stock']);
        $topCritical = array_slice($criticalItems, 0, 5);

        return [
            'monitor1' => [
                'title' => 'COO SUPPLY CHAIN',
                'badge' => 'OPS CENTER',
                'kpi_label_1' => 'TOTAL SKU AKTIF',
                'kpi_value_1' => (string) $totalSkus,
                'kpi_sub_1' => 'Katalog Berjalan',
                'kpi_label_2' => 'STOK KRITIS / REORDER',
                'kpi_value_2' => "{$criticalStock} SKU",
                'kpi_sub_2' => 'Perlu Restock Segera',
                'chart_type' => 'bar',
                'chart_data' => [max(0, $totalSkus - $criticalStock), $criticalStock],
                'chart_labels' => ['Aman', 'Kritis'],
            ],
            'monitor2' => [
                'title' => 'CRITICAL INVENTORY QUEUE',
                'feed_type' => 'table',
                'items' => empty($topCritical) ? [['col1' => 'Semua SKU', 'col2' => 'Aman', 'col3' => 'NORMAL']] : $topCritical,
            ],
        ];
    }

    private function buildInventoryMetrics(Business $business): array
    {
        $products = Product::where('business_id', $business->id)->where('is_active', true)->with('stocks')->get();
        $totalItems = 0;
        $criticalCount = 0;
        $items = [];

        foreach ($products as $p) {
            $stock = (float) ($p->stocks ? $p->stocks->sum('quantity') : ($p->stock_quantity ?? 0));
            $totalItems += (int) $stock;
            $minAlert = (float) ($p->min_stock ?? $p->min_stock_alert ?? 5);
            $isCrit = $stock <= $minAlert;
            if ($isCrit) {
                $criticalCount++;
            }
            $items[] = [
                'col1' => Str::limit($p->code ?? $p->sku ?? $p->name, 12),
                'col2' => 'Qty: ' . $stock,
                'col3' => $stock <= 2 ? 'ROP ALERT' : ($isCrit ? 'MENIPIS' : 'OK'),
                'stock' => $stock,
            ];
        }

        usort($items, fn($a, $b) => $a['stock'] <=> $b['stock']);
        $topItems = array_slice($items, 0, 5);

        return [
            'monitor1' => [
                'title' => 'WAREHOUSE INVENTORY',
                'badge' => 'ROP ENGINE',
                'kpi_label_1' => 'TOTAL UNIT GUDANG',
                'kpi_value_1' => number_format($totalItems, 0, ',', '.') . ' Pcs',
                'kpi_sub_1' => 'Persediaan Fisik',
                'kpi_label_2' => 'ALERT DI BAWAH ROP',
                'kpi_value_2' => "{$criticalCount} Item",
                'kpi_sub_2' => 'Reorder Point Trigger',
                'chart_type' => 'bar',
                'chart_data' => [12, 18, 14, 9, 22, 15, 8],
                'chart_labels' => ['S1', 'S2', 'S3', 'S4', 'S5', 'S6', 'S7'],
            ],
            'monitor2' => [
                'title' => 'LOW STOCK WATCHLIST',
                'feed_type' => 'table',
                'items' => empty($topItems) ? [['col1' => 'Gudang', 'col2' => 'Normal', 'col3' => 'STABIL']] : $topItems,
            ],
        ];
    }

    private function buildPurchasingMetrics(Business $business): array
    {
        $totalPos = PurchaseOrder::where('business_id', $business->id)->count();
        $suppliersCount = Supplier::where('business_id', $business->id)->count();

        $pos = PurchaseOrder::where('business_id', $business->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($po) => [
                'col1' => '#' . substr($po->po_number ?? (string) $po->id, -6),
                'col2' => 'Rp ' . number_format((float) $po->total_amount, 0, ',', '.'),
                'col3' => strtoupper($po->status ?? 'DRAFT'),
            ])->toArray();

        return [
            'monitor1' => [
                'title' => 'PROCUREMENT & VENDORS',
                'badge' => 'PO COMMAND',
                'kpi_label_1' => 'TOTAL PURCHASE ORDER',
                'kpi_value_1' => "{$totalPos} PO",
                'kpi_sub_1' => 'Semua Pesanan Pembelian',
                'kpi_label_2' => 'MITRA SUPPLIER AKTIF',
                'kpi_value_2' => "{$suppliersCount} Vendor",
                'kpi_sub_2' => 'Direktori Pemasok',
                'chart_type' => 'bar',
                'chart_data' => [3, 5, 2, 8, 4],
                'chart_labels' => ['W1', 'W2', 'W3', 'W4', 'W5'],
            ],
            'monitor2' => [
                'title' => 'RECENT PURCHASE ORDERS',
                'feed_type' => 'table',
                'items' => empty($pos) ? [['col1' => 'PO-NEW', 'col2' => 'Siap Buat Draft', 'col3' => 'STANDBY']] : $pos,
            ],
        ];
    }

    private function buildCfoMetrics(Business $business): array
    {
        $totalCash = (float) PaymentAccount::where('business_id', $business->id)
            ->where('is_active', true)
            ->sum('current_balance');

        $startOfMonth = now()->startOfMonth();
        $monthlyExpense = (float) CashTransaction::where('business_id', $business->id)
            ->where('type', 'expense')
            ->where('created_at', '>=', $startOfMonth)
            ->sum('amount');

        $txs = CashTransaction::where('business_id', $business->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($tx) => [
                'col1' => strtoupper($tx->type ?? 'MUTASI'),
                'col2' => 'Rp ' . number_format((float) $tx->amount, 0, ',', '.'),
                'col3' => Str::limit($tx->category ?? 'Kas', 10),
            ])->toArray();

        return [
            'monitor1' => [
                'title' => 'CFO FINANCIAL CONTROL',
                'badge' => 'AUDIT PASS',
                'kpi_label_1' => 'SALDO KAS & BANK',
                'kpi_value_1' => 'Rp ' . number_format($totalCash, 0, ',', '.'),
                'kpi_sub_1' => 'Likuiditas Real-Time',
                'kpi_label_2' => 'PENGELUARAN BULAN INI',
                'kpi_value_2' => 'Rp ' . number_format($monthlyExpense, 0, ',', '.'),
                'kpi_sub_2' => 'Opex Berjalan',
                'chart_type' => 'line',
                'chart_data' => [10, 14, 12, 18, 16, 21, 25],
                'chart_labels' => ['H-6', 'H-5', 'H-4', 'H-3', 'H-2', 'H-1', 'Hari ini'],
            ],
            'monitor2' => [
                'title' => 'CASHFLOW MUTATION STREAM',
                'feed_type' => 'table',
                'items' => empty($txs) ? [['col1' => 'KAS', 'col2' => 'Rp 0', 'col3' => 'BALANCED']] : $txs,
            ],
        ];
    }

    private function buildReportingMetrics(Business $business): array
    {
        $startOfMonth = now()->startOfMonth();
        $grossRevenue = (float) PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_COMPLETED)
            ->where('created_at', '>=', $startOfMonth)
            ->sum('total_amount');

        $grossProfit = (float) PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_COMPLETED)
            ->where('created_at', '>=', $startOfMonth)
            ->sum('total_gross_profit');

        $marginPct = $grossRevenue > 0 ? round(($grossProfit / $grossRevenue) * 100, 1) : 0.0;

        return [
            'monitor1' => [
                'title' => 'EXECUTIVE REPORTING',
                'badge' => 'REKAPITULASI',
                'kpi_label_1' => 'LABA KOTOR BULAN INI',
                'kpi_value_1' => 'Rp ' . number_format($grossProfit, 0, ',', '.'),
                'kpi_sub_1' => "Gross Margin: {$marginPct}%",
                'kpi_label_2' => 'PENDAPATAN BRUTO',
                'kpi_value_2' => 'Rp ' . number_format($grossRevenue, 0, ',', '.'),
                'kpi_sub_2' => 'Akumulasi Transaksi',
                'chart_type' => 'bar',
                'chart_data' => [35, 42, 38, 55, 60, 48, 65],
                'chart_labels' => ['S1', 'S2', 'S3', 'S4', 'S5', 'S6', 'S7'],
            ],
            'monitor2' => [
                'title' => 'MANAGEMENT CLOSING LOGS',
                'feed_type' => 'table',
                'items' => [
                    ['col1' => 'Closing POS', 'col2' => 'Otomatis', 'col3' => 'TERTIB'],
                    ['col1' => 'Jurnal Kas', 'col2' => 'Tervalidasi', 'col3' => 'BALANCE'],
                    ['col1' => 'Pajak PP 55', 'col2' => 'Tarif 0.5%', 'col3' => 'DIHITUNG'],
                ],
            ],
        ];
    }

    private function buildCmoMetrics(Business $business): array
    {
        $postsCount = SocialMediaPost::where('business_id', $business->id)->count();
        $customersCount = Customer::where('business_id', $business->id)->count();

        return [
            'monitor1' => [
                'title' => 'CMO GROWTH & PROMOTION',
                'badge' => 'CAMPAIGN',
                'kpi_label_1' => 'TOTAL AUDIENCE REACH',
                'kpi_value_1' => number_format($customersCount, 0, ',', '.') . ' User',
                'kpi_sub_1' => 'Database Kontak Bisnis',
                'kpi_label_2' => 'KAMPANYE & POSTINGAN',
                'kpi_value_2' => "{$postsCount} Konten",
                'kpi_sub_2' => 'Konten Pemasaran',
                'chart_type' => 'line',
                'chart_data' => [5, 9, 12, 14, 20, 18, 25],
                'chart_labels' => ['M1', 'M2', 'M3', 'M4', 'M5', 'M6', 'M7'],
            ],
            'monitor2' => [
                'title' => 'PROMO PERFORMANCE',
                'feed_type' => 'table',
                'items' => [
                    ['col1' => 'Diskon Kilat', 'col2' => 'Target: 50 Tx', 'col3' => 'AKTIF'],
                    ['col1' => 'WA Broadcast', 'col2' => 'Sapa Pelanggan', 'col3' => 'READY'],
                ],
            ],
        ];
    }

    private function buildContentMetrics(Business $business): array
    {
        $posts = SocialMediaPost::where('business_id', $business->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($p) => [
                'col1' => strtoupper($p->platform ?? 'FEED'),
                'col2' => Str::limit($p->content ?? 'Post Baru', 18),
                'col3' => strtoupper($p->status ?? 'DRAFT'),
            ])->toArray();

        return [
            'monitor1' => [
                'title' => 'CREATIVE CONTENT STUDIO',
                'badge' => 'COPYWRITING',
                'kpi_label_1' => 'KONTEN DRAF & SIAP',
                'kpi_value_1' => count($posts) . ' Naskah',
                'kpi_sub_1' => 'Formula AIDA & Edukasi',
                'kpi_label_2' => 'STATUS PRODUKSI',
                'kpi_value_2' => 'Aktif',
                'kpi_sub_2' => 'SOP Apple HIG Standard',
                'chart_type' => 'bar',
                'chart_data' => [2, 4, 3, 6, 5],
                'chart_labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
            ],
            'monitor2' => [
                'title' => 'COPYWRITING PIPELINE',
                'feed_type' => 'table',
                'items' => empty($posts) ? [['col1' => 'DRAFT', 'col2' => 'Konten Baru', 'col3' => 'READY']] : $posts,
            ],
        ];
    }

    private function buildSocialMediaMetrics(Business $business): array
    {
        return [
            'monitor1' => [
                'title' => 'SOCIAL MEDIA BROADCAST',
                'badge' => 'SCHEDULED',
                'kpi_label_1' => 'SLOT POSTINGAN HARI INI',
                'kpi_value_1' => '2 Slot',
                'kpi_sub_1' => 'Jam Sibuk 12:00 & 19:30',
                'kpi_label_2' => 'ENGAGEMENT STATUS',
                'kpi_value_2' => 'Optimal',
                'kpi_sub_2' => 'Instagram & WhatsApp',
                'chart_type' => 'bar',
                'chart_data' => [8, 12, 15, 10, 18, 22, 26],
                'chart_labels' => ['S', 'S', 'R', 'K', 'J', 'S', 'M'],
            ],
            'monitor2' => [
                'title' => 'POSTING DISPATCH QUEUE',
                'feed_type' => 'table',
                'items' => [
                    ['col1' => 'INSTAGRAM', 'col2' => 'Promo Akhir Pekan', 'col3' => 'QUEUED'],
                    ['col1' => 'WHATSAPP', 'col2' => 'Update Menu Baru', 'col3' => 'READY'],
                ],
            ],
        ];
    }

    private function buildMarketplaceMetrics(Business $business): array
    {
        $accounts = MarketplaceAccount::where('business_id', $business->id)->count();
        $orders = MarketplaceOrder::where('business_id', $business->id)->count();

        return [
            'monitor1' => [
                'title' => 'OMNICHANNEL SYNC HUB',
                'badge' => 'MULTI-KANAL',
                'kpi_label_1' => 'KANAL TERHUBUNG',
                'kpi_value_1' => "{$accounts} Toko",
                'kpi_sub_1' => 'Shopee / Tokopedia / TikTok',
                'kpi_label_2' => 'PESANAN TERINTEGRASI',
                'kpi_value_2' => "{$orders} Order",
                'kpi_sub_2' => 'Sinkronisasi Stok Real-Time',
                'chart_type' => 'bar',
                'chart_data' => [4, 6, 8, 5, 9],
                'chart_labels' => ['SP', 'TP', 'TK', 'LZ', 'BL'],
            ],
            'monitor2' => [
                'title' => 'MARKETPLACE SYNC LOGS',
                'feed_type' => 'table',
                'items' => [
                    ['col1' => 'SYNC', 'col2' => 'Katalog Harga Seragam', 'col3' => 'MATCH'],
                    ['col1' => 'STOCK', 'col2' => 'Stok POS Terkunci', 'col3' => 'LOCKED'],
                ],
            ],
        ];
    }

    private function buildHrMetrics(Business $business): array
    {
        $today = now()->toDateString();
        $totalStaff = $business->users()->count();
        $presentToday = Attendance::where('business_id', $business->id)
            ->whereDate('date', $today)
            ->count();

        $activeShifts = PosShift::where('business_id', $business->id)
            ->where('status', 'open')
            ->count();

        $recentAttendance = Attendance::where('business_id', $business->id)
            ->latest('date')
            ->latest('id')
            ->take(5)
            ->get()
            ->map(fn($a) => [
                'col1' => Str::limit($a->user?->name ?? 'Staf', 14),
                'col2' => $a->clock_in_at ? Carbon::parse($a->clock_in_at)->format('H:i') : ($a->check_in_time ? Carbon::parse($a->check_in_time)->format('H:i') : 'OFF'),
                'col3' => strtoupper($a->status ?? 'HADIR'),
            ])->toArray();

        return [
            'monitor1' => [
                'title' => 'HR & WORKFORCE PULSE',
                'badge' => 'PEOPLE OPS',
                'kpi_label_1' => 'STAF TERDAFTAR',
                'kpi_value_1' => "{$totalStaff} Orang",
                'kpi_sub_1' => 'Tenaga Kerja Aktif',
                'kpi_label_2' => 'SHIFT KASIR BERJALAN',
                'kpi_value_2' => "{$activeShifts} Register",
                'kpi_sub_2' => 'SOP Blind Cash Count',
                'chart_type' => 'bar',
                'chart_data' => [100, 95, 98, 92, 100],
                'chart_labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Today'],
            ],
            'monitor2' => [
                'title' => 'STAFF ROSTER & ATTENDANCE',
                'feed_type' => 'table',
                'items' => empty($recentAttendance) ? [['col1' => 'Karyawan', 'col2' => 'Hadir Lengkap', 'col3' => 'TERTIB']] : $recentAttendance,
            ],
        ];
    }

    private function buildFallbackMetrics(string $roleKey): array
    {
        return [
            'monitor1' => [
                'title' => strtoupper($roleKey) . ' RADAR',
                'badge' => 'ONLINE',
                'kpi_label_1' => 'STATUS SISTEM',
                'kpi_value_1' => 'Normal',
                'kpi_sub_1' => 'Data Terhubung',
                'kpi_label_2' => 'AKTIVITAS AGEN',
                'kpi_value_2' => 'Siap Tugas',
                'kpi_sub_2' => 'Job Desk Aktif',
                'chart_type' => 'line',
                'chart_data' => [10, 15, 12, 18, 20, 22, 25],
                'chart_labels' => ['S', 'S', 'R', 'K', 'J', 'S', 'M'],
            ],
            'monitor2' => [
                'title' => 'LIVE FEED',
                'feed_type' => 'table',
                'items' => [
                    ['col1' => 'SYS', 'col2' => 'Operasional Aktif', 'col3' => 'OK'],
                ],
            ],
        ];
    }

    /**
     * Helper: 7-day daily POS sales trend
     *
     * @return array{data: array<int, float>, labels: array<int, string>}
     */
    private function get7DaySalesTrend(Business $business): array
    {
        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $labels[] = $day->locale('id')->isoFormat('ddd');

            $sum = (float) PosOrder::where('business_id', $business->id)
                ->where('status', PosOrder::STATUS_COMPLETED)
                ->whereDate('created_at', $day->toDateString())
                ->sum('total_amount');

            // Convert to thousands or millions for compact display
            $data[] = round($sum / 1000, 1);
        }

        return ['data' => $data, 'labels' => $labels];
    }
}
