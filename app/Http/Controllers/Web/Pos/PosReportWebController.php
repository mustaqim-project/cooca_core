<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Pos;

use App\Domain\Report\Pos\DTOs\PosReportFilterDTO;
use App\Domain\Report\Pos\PosReportingService;
use App\Domain\Report\SalesReportService;
use App\Exports\PosReportExport;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\PosShift;
use App\Models\ProductCategory;
use App\Support\Context;
use App\Support\Math\FinancialMath;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PosReportWebController extends Controller
{
    public function __construct(
        private readonly PosReportingService $reportingService = new PosReportingService,
        private readonly SalesReportService $salesReport = new SalesReportService
    ) {}

    /**
     * Display POS Analytics Dashboard.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();

        // 1. Multi-Tenant Anti-IDOR Validation
        $locationId = $request->query('location_id');
        if (!empty($locationId) && !Location::where('business_id', $business->id)->where('id', $locationId)->exists()) {
            $request->merge(['location_id' => null]);
        }

        $userId = $request->query('user_id');
        if (!empty($userId) && !$business->users()->where('users.id', $userId)->exists()) {
            $request->merge(['user_id' => null]);
        }

        $posShiftId = $request->query('pos_shift_id');
        if (!empty($posShiftId) && !PosShift::where('business_id', $business->id)->where('id', $posShiftId)->exists()) {
            $request->merge(['pos_shift_id' => null]);
        }

        $categoryId = $request->query('category_id');
        if (!empty($categoryId) && !ProductCategory::where('business_id', $business->id)->where('id', $categoryId)->exists()) {
            $request->merge(['category_id' => null]);
        }

        // 2. Build Standardized Filter DTO
        $filter = PosReportFilterDTO::fromRequest($request, $business->id);
        $startDate = $filter->startDate;
        $endDate = $filter->endDate;

        // 3. Tab Routing
        $validTabs = [
            'overview', 'transactions', 'products', 'categories', 'cashiers',
            'outlets', 'payments', 'discounts', 'refunds', 'voids',
            'shifts', 'hourly', 'customers', 'channels', 'profitability'
        ];
        $activeTab = (string) $request->query('tab', 'overview');
        if (!in_array($activeTab, $validTabs, true)) {
            $activeTab = 'overview';
        }

        // 4. Dropdown Filter Options
        $locations = Location::where('business_id', $business->id)->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $cashiers = $business->users()->orderBy('name')->get(['users.id', 'users.name']);
        $categories = ProductCategory::where('business_id', $business->id)->orderBy('name')->get(['id', 'name']);
        $shifts = PosShift::where('business_id', $business->id)->with('user')->latest('opened_at')->limit(30)->get(['id', 'user_id', 'opened_at', 'status']);

        // 5. KPI Summary (Single Source of Truth)
        $kpi = $this->reportingService->getKpiSummary($filter);

        $totalRevenue = $kpi->netSales;
        $totalSubtotal = $kpi->subtotal;
        $totalDiscount = $kpi->orderDiscount;
        $totalVoucherDiscount = $kpi->voucherDiscount;
        $totalPointsDiscount = $kpi->pointsDiscount;
        $totalTax = $kpi->taxAmount;
        $totalServiceCharge = $kpi->serviceChargeAmount;
        $totalRounding = $kpi->roundingAmount;
        $totalHpp = $kpi->totalHpp;
        $totalGrossProfit = $kpi->grossProfit;
        $grossMarginPercent = $kpi->grossMarginPercent;
        $ordersCount = $kpi->totalOrders;
        $averageOrderValue = $kpi->averageOrderValue;
        $goodsRevenue = $kpi->goodsRevenue;
        $goodsQty = $kpi->goodsQuantity;
        $servicesRevenue = $kpi->servicesRevenue;
        $servicesQty = $kpi->servicesQuantity;
        $todayRevenue = $kpi->todayRevenue;
        $todayOrders = $kpi->todayOrders;
        $averageSellingPrice = $kpi->averageSellingPrice;
        $averageCostPrice = $kpi->averageCostPrice;

        // 6. Sub-aggregations via PosReportingService
        $dailyTrend = $this->reportingService->getDailySalesTrend($filter);
        $hourlyData = $this->reportingService->getHourlyHeatmap($filter);
        $paymentMethods = $this->reportingService->getPaymentMethodBreakdown($filter);
        $topProducts = $this->reportingService->getProductPerformance($filter, 50);
        $categoryPerformance = $this->reportingService->getCategoryPerformance($filter);
        $cashierPerformance = $this->reportingService->getCashierPerformance($filter);
        $outletPerformance = $this->reportingService->getOutletPerformance($filter);
        $discountBreakdown = $this->reportingService->getDiscountBreakdown($filter);
        $discountAnalytics = $this->reportingService->analyzeDiscountsAndPromotions($filter);
        $refundSummary = $this->reportingService->getRefundSummary($filter);
        $voidAudit = $this->reportingService->auditVoidsAndFraud($filter);
        $reconciliation = $this->reportingService->reconcile($filter);
        $shiftReconciliation = $this->reportingService->getShiftReconciliationList($filter);
        $channelSales = $this->reportingService->getSalesChannelBreakdown($filter);
        $customerMatrix = $this->reportingService->getCustomerSalesMatrix($filter, 50);
        $marginAnalytics = $this->reportingService->analyzeMarginAndProfitability($filter);
        $transactions = $this->reportingService->getTransactionLedger($filter, 25);

        // 7. Legacy snapshot compatibility
        $snapshotReport = $this->salesReport->summary($business->id, $startDate, $endDate, $filter->locationId);
        $snapshotTotalQty = (float) $snapshotReport['summary']['total_quantity'];
        $snapshotTotalSales = (float) $snapshotReport['summary']['total_sales'];
        $snapshotTotalModal = (float) $snapshotReport['summary']['total_modal'];
        $snapshotGrossProfit = (float) $snapshotReport['summary']['total_gross_profit'];
        $snapshotMarginPercent = (float) $snapshotReport['summary']['margin_percentage'];
        $productAveragePrices = $snapshotReport['by_product'];

        return view('app.pos.reports', compact(
            'business',
            'filter',
            'activeTab',
            'locations',
            'cashiers',
            'categories',
            'shifts',
            'startDate',
            'endDate',
            'kpi',
            'totalRevenue',
            'goodsRevenue',
            'goodsQty',
            'servicesRevenue',
            'servicesQty',
            'totalSubtotal',
            'totalDiscount',
            'totalVoucherDiscount',
            'totalPointsDiscount',
            'totalTax',
            'totalServiceCharge',
            'totalRounding',
            'totalHpp',
            'totalGrossProfit',
            'grossMarginPercent',
            'ordersCount',
            'averageOrderValue',
            'todayRevenue',
            'todayOrders',
            'dailyTrend',
            'hourlyData',
            'paymentMethods',
            'topProducts',
            'categoryPerformance',
            'cashierPerformance',
            'outletPerformance',
            'discountBreakdown',
            'discountAnalytics',
            'refundSummary',
            'voidAudit',
            'reconciliation',
            'shiftReconciliation',
            'channelSales',
            'customerMatrix',
            'marginAnalytics',
            'transactions',
            'averageSellingPrice',
            'averageCostPrice',
            'snapshotTotalQty',
            'snapshotTotalSales',
            'snapshotTotalModal',
            'snapshotGrossProfit',
            'snapshotMarginPercent',
            'productAveragePrices'
        ));
    }

    /**
     * AJAX Endpoint untuk Quick-View Detail Transaksi (Slide-Over Drawer).
     */
    public function orderDetail(PosOrder $order): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($order->business_id !== $business->id) {
            abort(403, 'Akses tidak sah ke data transaksi bisnis lain.');
        }

        $order->load([
            'customer',
            'user',
            'location',
            'posShift.user',
            'technician',
            'voidedByUser',
            'refundedByUser',
            'payments',
            'items.product.category',
            'items.modifiers',
        ]);

        $subtotal = (float) $order->subtotal;
        $orderDiscount = (float) $order->discount_amount;
        $voucherDiscount = (float) $order->voucher_discount_amount;
        $pointsDiscount = (float) $order->points_discount_amount;
        $totalDiscount = $orderDiscount + $voucherDiscount + $pointsDiscount;
        $taxAmount = (float) $order->tax_amount;
        $serviceChargeAmount = (float) $order->service_charge_amount;
        $roundingAmount = (float) $order->rounding_amount;
        $totalAmount = (float) $order->total_amount;
        $paidAmount = (float) $order->paid_amount;
        $changeAmount = (float) $order->change_amount;
        $totalHpp = (float) $order->total_hpp_cost;
        $grossProfit = (float) $order->total_gross_profit;
        $grossMarginPercent = $totalAmount > 0 ? ($grossProfit / $totalAmount) * 100 : 0.0;

        // Channel & Online Food Delivery Meta Calculation
        $channelCode = strtolower((string) ($order->sales_channel ?? 'pos_direct'));
        $channelConfigs = [
            'shopeefood' => [
                'name' => 'ShopeeFood',
                'type' => 'online_delivery',
                'fee_percent' => 20.0,
                'badge_bg' => '#EE4D2D',
                'badge_text' => '#FFFFFF',
                'icon' => 'utensils',
            ],
            'gofood' => [
                'name' => 'GoFood (GoBiz)',
                'type' => 'online_delivery',
                'fee_percent' => 20.0,
                'badge_bg' => '#EE2724',
                'badge_text' => '#FFFFFF',
                'icon' => 'bike',
            ],
            'grabfood' => [
                'name' => 'GrabFood',
                'type' => 'online_delivery',
                'fee_percent' => 25.0,
                'badge_bg' => '#00B14F',
                'badge_text' => '#FFFFFF',
                'icon' => 'bike',
            ],
            'storefront' => [
                'name' => 'Toko Online Storefront',
                'type' => 'direct_online',
                'fee_percent' => 0.0,
                'badge_bg' => '#007AFF',
                'badge_text' => '#FFFFFF',
                'icon' => 'globe',
            ],
            'dine_in' => [
                'name' => 'Dine-In (Makan di Tempat)',
                'type' => 'direct_pos',
                'fee_percent' => 0.0,
                'badge_bg' => '#AF52DE',
                'badge_text' => '#FFFFFF',
                'icon' => 'utensils-crossed',
            ],
            'takeaway' => [
                'name' => 'Takeaway (Bawa Pulang)',
                'type' => 'direct_pos',
                'fee_percent' => 0.0,
                'badge_bg' => '#FF9500',
                'badge_text' => '#FFFFFF',
                'icon' => 'shopping-bag',
            ],
            'pos_direct' => [
                'name' => 'Kasir Langsung (POS Direct)',
                'type' => 'direct_pos',
                'fee_percent' => 0.0,
                'badge_bg' => '#34C759',
                'badge_text' => '#FFFFFF',
                'icon' => 'receipt',
            ],
            'pos' => [
                'name' => 'Kasir POS',
                'type' => 'direct_pos',
                'fee_percent' => 0.0,
                'badge_bg' => '#34C759',
                'badge_text' => '#FFFFFF',
                'icon' => 'receipt',
            ],
        ];

        $channelCfg = $channelConfigs[$channelCode] ?? [
            'name' => ucwords(str_replace('_', ' ', $channelCode)),
            'type' => 'other',
            'fee_percent' => 0.0,
            'badge_bg' => '#8E8E93',
            'badge_text' => '#FFFFFF',
            'icon' => 'tag',
        ];

        $feePercent = (float) $channelCfg['fee_percent'];
        $feeAmount = FinancialMath::roundFinancial($totalAmount * ($feePercent / 100.0));
        $netMerchantPayout = FinancialMath::roundFinancial($totalAmount - $feeAmount);
        $realGrossProfit = FinancialMath::roundFinancial($netMerchantPayout - $totalHpp);
        $realMarginPercent = FinancialMath::calculateMargin($realGrossProfit, $netMerchantPayout);

        $itemsFormatted = $order->items->map(function ($item) {
            $unitPrice = (float) $item->unit_price;
            $unitCost = (float) $item->unit_cost_hpp;
            $qty = (float) $item->quantity;
            $itemSubtotal = (float) $item->subtotal;
            $itemDiscount = (float) $item->discount_amount;
            $itemTotalPrice = (float) $item->total_price;
            $itemTotalHpp = (float) $item->total_hpp;
            $itemProfit = $itemTotalPrice - $itemTotalHpp;
            $itemMargin = $itemTotalPrice > 0 ? ($itemProfit / $itemTotalPrice) * 100 : 0.0;

            $modifiersFormatted = $item->modifiers->map(function ($mod) {
                return [
                    'id' => $mod->id,
                    'name' => $mod->modifier_group_name ?? $mod->name ?? 'Pilihan',
                    'option_name' => $mod->modifier_option_name ?? $mod->option_name ?? $mod->name ?? '-',
                    'price' => (float) ($mod->unit_price ?? $mod->price ?? 0),
                ];
            });

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product_name ?? $item->product?->name ?? 'Item Custom',
                'code' => $item->product_code ?? $item->product?->code ?? '-',
                'type' => $item->product?->type ?? 'goods',
                'category_name' => $item->product?->category?->name ?? '-',
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'unit_cost_hpp' => $unitCost,
                'discount_amount' => $itemDiscount,
                'subtotal' => $itemSubtotal,
                'total_price' => $itemTotalPrice,
                'total_hpp' => $itemTotalHpp,
                'gross_profit' => $itemProfit,
                'margin_percent' => round($itemMargin, 1),
                'notes' => $item->notes,
                'batch_number' => $item->batch_number,
                'expired_date' => $item->expired_date?->format('d/m/Y'),
                'serial_number' => $item->serial_number,
                'dosage_instructions' => $item->dosage_instructions,
                'modifiers' => $modifiersFormatted,
            ];
        });

        $paymentsFormatted = $order->payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'payment_method' => $payment->payment_method,
                'method_label' => match ($payment->payment_method) {
                    'cash' => 'Tunai (Kas Kasir)',
                    'qris', 'qris_dynamic' => 'QRIS TriPay / Cooca Pay',
                    'edc_debit' => 'EDC Kartu Debit Bank',
                    'edc_credit' => 'EDC Kartu Kredit',
                    'transfer' => 'Transfer Bank Langsung',
                    'customer_credit' => 'Piutang / Kasbon',
                    'loyalty_points' => 'Poin Loyalitas Member',
                    default => ucfirst((string) $payment->payment_method),
                },
                'amount' => (float) $payment->amount,
                'fee_amount' => (float) $payment->fee_amount,
                'net_amount' => (float) $payment->net_amount,
                'reference_number' => $payment->reference_number,
                'status' => $payment->status,
                'notes' => $payment->notes,
                'paid_at' => $payment->created_at?->format('d/m/Y H:i:s'),
            ];
        });

        $hasIndustryData = !empty($order->vehicle_license_plate)
            || !empty($order->laundry_weight_kg)
            || !empty($order->rack_location)
            || !empty($order->technician_id)
            || !empty($order->service_notes)
            || !empty($order->notes);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'order_date' => $order->order_date ? Carbon::parse($order->order_date)->format('d/m/Y') : '-',
                'created_at' => $order->created_at?->format('d/m/Y H:i:s'),
                'status' => $order->status,
                'order_type' => $order->order_type ?? 'dine_in',
                'sales_channel' => $order->sales_channel ?? 'pos_direct',
                'table_or_reference' => $order->table_or_reference,
                'external_order_ref' => $order->external_order_ref,
                'customer' => [
                    'name' => $order->customer?->name ?? $order->customer_name_guest ?? 'Pelanggan Umum (Guest)',
                    'phone' => $order->customer?->phone ?? $order->customer_phone_guest ?? '-',
                    'email' => $order->customer?->email ?? '-',
                ],
                'cashier' => [
                    'name' => $order->user?->name ?? 'Kasir Utama',
                    'email' => $order->user?->email ?? '-',
                ],
                'location' => [
                    'name' => $order->location?->name ?? 'Outlet Utama',
                    'address' => $order->location?->address ?? '-',
                ],
                'shift' => $order->posShift ? [
                    'id' => $order->posShift->id,
                    'shift_number' => $order->posShift->shift_number,
                    'opened_at' => $order->posShift->opened_at ? Carbon::parse($order->posShift->opened_at)->format('d/m/Y H:i') : '-',
                ] : null,
                'channel_meta' => [
                    'channel_code' => $channelCode,
                    'channel_name' => $channelCfg['name'],
                    'channel_type' => $channelCfg['type'],
                    'platform_fee_percent' => $feePercent,
                    'platform_fee_amount' => $feeAmount,
                    'net_merchant_payout' => $netMerchantPayout,
                    'real_gross_profit' => $realGrossProfit,
                    'real_margin_percent' => round($realMarginPercent, 1),
                    'external_order_ref' => $order->external_order_ref ?? $order->table_or_reference,
                    'badge_bg' => $channelCfg['badge_bg'],
                    'badge_text' => $channelCfg['badge_text'],
                    'icon' => $channelCfg['icon'],
                ],
                'industry_meta' => [
                    'industry_type' => $business->industry_type ?? 'retail',
                    'vehicle_license_plate' => $order->vehicle_license_plate,
                    'vehicle_model' => $order->vehicle_model,
                    'vehicle_mileage' => $order->vehicle_mileage,
                    'technician_name' => $order->technician?->name,
                    'service_notes' => $order->service_notes,
                    'laundry_weight_kg' => $order->laundry_weight_kg ? (float) $order->laundry_weight_kg : null,
                    'rack_location' => $order->rack_location,
                    'estimated_completion_at' => $order->estimated_completion_at ? Carbon::parse($order->estimated_completion_at)->format('d/m/Y H:i') : null,
                    'laundry_status' => $order->laundry_status,
                    'notes' => $order->notes,
                    'has_industry_data' => $hasIndustryData,
                ],
                'financial' => [
                    'subtotal' => $subtotal,
                    'order_discount' => $orderDiscount,
                    'voucher_code' => $order->voucher_code,
                    'voucher_discount' => $voucherDiscount,
                    'points_discount' => $pointsDiscount,
                    'total_discount' => $totalDiscount,
                    'tax_percentage' => (float) $order->tax_percentage,
                    'tax_amount' => $taxAmount,
                    'service_charge_percentage' => (float) $order->service_charge_percentage,
                    'service_charge_amount' => $serviceChargeAmount,
                    'rounding_amount' => $roundingAmount,
                    'total_amount' => $totalAmount,
                    'paid_amount' => $paidAmount,
                    'change_amount' => $changeAmount,
                    'total_hpp' => $totalHpp,
                    'gross_profit' => $grossProfit,
                    'gross_margin_percent' => round($grossMarginPercent, 1),
                ],
                'audit' => [
                    'printed_count' => (int) ($order->print_count ?? $order->printed_count ?? 0),
                    'last_printed_at' => $order->last_printed_at ? Carbon::parse($order->last_printed_at)->format('d/m/Y H:i:s') : null,
                    'void_reason' => $order->void_reason,
                    'voided_at' => $order->voided_at ? Carbon::parse($order->voided_at)->format('d/m/Y H:i:s') : null,
                    'voided_by' => $order->voidedByUser?->name,
                    'refund_reason' => $order->refund_reason,
                    'refunded_at' => $order->refunded_at ? Carbon::parse($order->refunded_at)->format('d/m/Y H:i:s') : null,
                    'refunded_by' => $order->refundedByUser?->name,
                    'gateway_reference' => $order->gateway_reference,
                    'sync_status' => $order->payment_gateway ? 'Gateway Integrated (' . $order->payment_gateway . ')' : 'Direct POS Settlement',
                ],
                'items' => $itemsFormatted,
                'payments' => $paymentsFormatted,
            ],
        ]);
    }

    /**
     * Export POS Sales ke Master Excel 9-Sheet (XLSX) standar COOCA.
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $business = Context::requireBusiness();

        // 1. Multi-Tenant Anti-IDOR Validation
        $locationId = $request->query('location_id');
        if (!empty($locationId) && !Location::where('business_id', $business->id)->where('id', $locationId)->exists()) {
            $request->merge(['location_id' => null]);
        }

        $userId = $request->query('user_id');
        if (!empty($userId) && !$business->users()->where('users.id', $userId)->exists()) {
            $request->merge(['user_id' => null]);
        }

        $posShiftId = $request->query('pos_shift_id');
        if (!empty($posShiftId) && !PosShift::where('business_id', $business->id)->where('id', $posShiftId)->exists()) {
            $request->merge(['pos_shift_id' => null]);
        }

        $categoryId = $request->query('category_id');
        if (!empty($categoryId) && !ProductCategory::where('business_id', $business->id)->where('id', $categoryId)->exists()) {
            $request->merge(['category_id' => null]);
        }

        // 2. Build Filter DTO
        $filter = PosReportFilterDTO::fromRequest($request, $business->id);

        if ($request->get('format') === 'csv') {
            $orders = $this->reportingService->buildBaseOrdersQuery($filter)
                ->with(['customer', 'user', 'location', 'payments', 'items.product'])
                ->orderBy('order_date')
                ->orderBy('created_at')
                ->get();

            $filename = 'laporan-penjualan-pos-' . $filter->startDate->format('Ymd') . '-' . $filter->endDate->format('Ymd') . '.csv';
            $headers = [
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Pragma'              => 'no-cache',
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                'Expires'             => '0',
            ];

            return response()->stream(function () use ($orders, $business, $filter): void {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF");
                $this->exportPosDetailCsv($file, $orders, $business, $filter->startDate, $filter->endDate);
                fclose($file);
            }, 200, $headers);
        }

        $exporter = new PosReportExport($this->reportingService);
        return $exporter->download($business, $filter);
    }

    /**
     * Tampilan Cetak Resmi / Print-to-PDF Laporan Eksekutif POS & Rekonsiliasi (Bento Apple HIG A4/F4).
     */
    public function printSummary(Request $request): View
    {
        $business = Context::requireBusiness();

        // Multi-Tenant Anti-IDOR Validation
        $locationId = $request->query('location_id');
        if (!empty($locationId) && !Location::where('business_id', $business->id)->where('id', $locationId)->exists()) {
            $request->merge(['location_id' => null]);
        }

        $userId = $request->query('user_id');
        if (!empty($userId) && !$business->users()->where('users.id', $userId)->exists()) {
            $request->merge(['user_id' => null]);
        }

        $filter = PosReportFilterDTO::fromRequest($request, $business->id);

        $kpi = $this->reportingService->getKpiSummary($filter);
        $channels = $this->reportingService->getSalesChannelBreakdown($filter);
        $reconciliation = $this->reportingService->reconcile($filter);
        $topProducts = $this->reportingService->getProductPerformance($filter, 10);
        $paymentMethods = $this->reportingService->getPaymentMethodBreakdown($filter);
        $recentTransactions = $this->reportingService->buildBaseOrdersQuery($filter)
            ->with(['customer', 'user', 'location', 'payments', 'technician'])
            ->latest('order_date')
            ->latest('created_at')
            ->limit(25)
            ->get();

        $locationName = $filter->locationId 
            ? (Location::where('business_id', $business->id)->find($filter->locationId)?->name ?? 'Seluruh Outlet')
            : 'Seluruh Outlet / Lokasi';

        return view('app.pos.reports.print_summary', compact(
            'business',
            'filter',
            'kpi',
            'channels',
            'reconciliation',
            'topProducts',
            'paymentMethods',
            'recentTransactions',
            'locationName'
        ));
    }

    /**
     * Menulis seluruh bagian laporan detail ke dalam file CSV.
     *
     * @param resource $file
     * @param \Illuminate\Support\Collection<int, PosOrder> $orders
     */
    private function exportPosDetailCsv($file, $orders, Business $business, Carbon $startDate, Carbon $endDate): void
    {
        $orders = $orders->values();

        // KPI periode (angka sama dengan ringkasan halaman)
        $totalRevenue         = (float) $orders->sum('total_amount');
        $totalSubtotal        = (float) $orders->sum('subtotal');
        $totalDiscount        = (float) $orders->sum('discount_amount');
        $totalVoucherDiscount = (float) $orders->sum('voucher_discount_amount');
        $totalPointsDiscount  = (float) $orders->sum('points_discount_amount');
        $totalTax             = (float) $orders->sum('tax_amount');
        $totalServiceCharge   = (float) $orders->sum('service_charge_amount');
        $totalRounding        = (float) $orders->sum('rounding_amount');
        $totalHpp             = (float) $orders->sum('total_hpp_cost');
        $totalGrossProfit     = (float) $orders->sum('total_gross_profit');
        $ordersCount          = $orders->count();
        $averageOrderValue    = $ordersCount > 0 ? $totalRevenue / $ordersCount : 0.0;
        $grossMarginPercent   = $totalRevenue > 0 ? ($totalGrossProfit / $totalRevenue) * 100 : 0.0;

        $periodLabel = $startDate->format('d/m/Y') . ' s/d ' . $endDate->format('d/m/Y');

        fputcsv($file, ['LAPORAN PENJUALAN KASIR POS - DETAIL TRANSAKSI, ITEM & PEMBAYARAN']);
        fputcsv($file, ['Bisnis', $business->name]);
        fputcsv($file, ['Periode', $periodLabel]);
        fputcsv($file, ['Tanggal Cetak', date('d/m/Y H:i:s')]);
        fputcsv($file, []);

        fputcsv($file, ['RINGKASAN KINERJA', 'NOMINAL (' . $business->currency_symbol . ')']);
        fputcsv($file, ['Jumlah Transaksi', $ordersCount]);
        fputcsv($file, ['Subtotal Penjualan', round($totalSubtotal, 2)]);
        fputcsv($file, ['Total Diskon Order', round($totalDiscount, 2)]);
        fputcsv($file, ['Total Diskon Voucher', round($totalVoucherDiscount, 2)]);
        fputcsv($file, ['Total Diskon Poin', round($totalPointsDiscount, 2)]);
        fputcsv($file, ['Total Pajak / PPN', round($totalTax, 2)]);
        fputcsv($file, ['Total Service Charge', round($totalServiceCharge, 2)]);
        fputcsv($file, ['Total Pembulatan (Rounding)', round($totalRounding, 2)]);
        fputcsv($file, ['Total Penjualan (Grand Total)', round($totalRevenue, 2)]);
        fputcsv($file, ['Rata-rata Nilai Transaksi (AOV)', round($averageOrderValue, 2)]);
        fputcsv($file, ['Total HPP / Modal', round($totalHpp, 2)]);
        fputcsv($file, ['Total Laba Kotor', round($totalGrossProfit, 2)]);
        fputcsv($file, ['Margin Laba Kotor (%)', round($grossMarginPercent, 2) . '%']);
        fputcsv($file, []);
        // 1. Rincian Transaksi per Order
        fputcsv($file, ['--- 1. RINCIAN TRANSAKSI (PER ORDER) ---']);
        fputcsv($file, [
            'No. Order', 'Tanggal', 'Jam', 'Outlet', 'Kasir', 'Pelanggan', 'Tipe Pelanggan', 'Tipe Order',
            'Meja / Referensi', 'Jumlah Item', 'Subtotal', 'Tipe Diskon', 'Nilai Diskon', 'Diskon Order',
            'Kode Voucher', 'Diskon Voucher', 'Diskon Poin', 'Pajak (%)', 'Pajak',
            'Service Charge (%)', 'Service Charge', 'Pembulatan', 'Total Bayar', 'Dibayar', 'Kembalian',
            'HPP / Modal', 'Laba Kotor', 'Margin (%)', 'Metode Pembayaran', 'Status',
        ]);
        foreach ($orders as $o) {
            $payMethods = $o->payments->map(fn ($p) => $this->paymentMethodLabel($p->payment_method) . ': ' . number_format((float) $p->amount, 2, ',', '.'))->implode(' | ');
            $margin = $o->total_amount > 0 ? round(($o->total_gross_profit / $o->total_amount) * 100, 2) : 0;
            $custName = $o->customer?->name ?: ($o->customer_name_guest ?: 'Umum');
            $custType = $o->customer ? 'Member' : 'Guest / Umum';

            fputcsv($file, [
                $o->order_number,
                $o->order_date?->format('Y-m-d') ?? $o->created_at?->format('Y-m-d') ?? '-',
                $o->created_at?->format('H:i:s') ?? '-',
                $o->location->name ?? '-',
                $o->user->name ?? '-',
                $custName,
                $custType,
                strtoupper(str_replace('_', ' ', (string) $o->order_type)),
                $o->table_or_reference ?? '-',
                (int) round($o->items->sum('quantity')),
                round((float) $o->subtotal, 2),
                $this->discountTypeLabel($o->discount_type, (float) $o->discount_value, (float) $o->discount_amount),
                round((float) $o->discount_value, 2),
                round((float) $o->discount_amount, 2),
                $o->voucher_code ?: ($o->voucher_discount_amount > 0 ? 'Voucher' : '-'),
                round((float) $o->voucher_discount_amount, 2),
                round((float) $o->points_discount_amount, 2),
                round((float) $o->tax_percentage, 2),
                round((float) $o->tax_amount, 2),
                round((float) $o->service_charge_percentage, 2),
                round((float) $o->service_charge_amount, 2),
                round((float) $o->rounding_amount, 2),
                round((float) $o->total_amount, 2),
                round((float) $o->paid_amount, 2),
                round((float) $o->change_amount, 2),
                round((float) $o->total_hpp_cost, 2),
                round((float) $o->total_gross_profit, 2),
                $margin . '%',
                $payMethods ?: '-',
                strtoupper(str_replace('_', ' ', (string) $o->status)),
            ]);
        }
        fputcsv($file, ['TOTAL TRANSAKSI', $ordersCount]);
        fputcsv($file, []);

        // 2. Detail Item per Transaksi
        fputcsv($file, ['--- 2. DETAIL ITEM PER TRANSAKSI ---']);
        fputcsv($file, [
            'No. Order', 'Tanggal', 'Outlet', 'Kasir', 'Pelanggan', 'Kode Produk / SKU', 'Nama Produk',
            'Qty', 'Harga Satuan', 'Diskon Item', 'Subtotal Item', 'Total Item', 'HPP / Unit', 'Total HPP Item', 'Laba Item',
        ]);
        foreach ($orders as $o) {
            $custName = $o->customer?->name ?: ($o->customer_name_guest ?: 'Umum');
            foreach ($o->items as $it) {
                $totalHppItem = (float) $it->total_hpp > 0 ? (float) $it->total_hpp : ((float) $it->quantity * (float) $it->unit_cost_hpp);
                $profitItem = (float) $it->total_price - $totalHppItem;
                $sku = $it->product_code ?: ($it->product?->code ?: ($it->product?->sku ?: '-'));

                fputcsv($file, [
                    $o->order_number,
                    $o->order_date?->format('Y-m-d') ?? $o->created_at?->format('Y-m-d') ?? '-',
                    $o->location->name ?? '-',
                    $o->user->name ?? '-',
                    $custName,
                    $sku,
                    $it->product_name,
                    $this->formatQty($it->quantity),
                    round((float) $it->unit_price, 2),
                    round((float) $it->discount_amount, 2),
                    round((float) $it->subtotal, 2),
                    round((float) $it->total_price, 2),
                    round((float) $it->unit_cost_hpp, 2),
                    round($totalHppItem, 2),
                    round($profitItem, 2),
                ]);
            }
        }
        fputcsv($file, []);
        // 3. Rincian Pembayaran per Metode
        fputcsv($file, ['--- 3. RINCIAN PEMBAYARAN (PER METODE) ---']);
        fputcsv($file, ['No. Order', 'Tanggal', 'Metode Pembayaran', 'Nominal', 'Fee', 'Net', 'Status', 'No. Referensi', 'Keterangan']);
        foreach ($orders as $o) {
            foreach ($o->payments as $p) {
                $net = $p->net_amount !== null ? (float) $p->net_amount : (float) $p->amount;

                fputcsv($file, [
                    $o->order_number,
                    $o->order_date?->format('Y-m-d') ?? $o->created_at?->format('Y-m-d') ?? '-',
                    $this->paymentMethodLabel($p->payment_method),
                    round((float) $p->amount, 2),
                    round((float) $p->fee_amount, 2),
                    round($net, 2),
                    strtoupper(str_replace('_', ' ', (string) ($p->status ?? 'success'))),
                    $p->reference_number ?? '-',
                    $p->notes ?? '-',
                ]);
            }
        }
        fputcsv($file, []);

        // 4. Ringkasan Metode Pembayaran
        $paymentsFlat = $orders->flatMap(fn ($o) => $o->payments)->values();

        fputcsv($file, ['--- 4. RINGKASAN METODE PEMBAYARAN ---']);
        fputcsv($file, ['Metode Pembayaran', 'Jumlah Pembayaran', 'Total Nominal', 'Total Fee', 'Total Net']);
        foreach ($paymentsFlat->groupBy('payment_method') as $method => $payments) {
            fputcsv($file, [
                $this->paymentMethodLabel((string) $method),
                $payments->count(),
                round((float) $payments->sum('amount'), 2),
                round((float) $payments->sum(fn ($p) => (float) $p->fee_amount), 2),
                round((float) $payments->sum(fn ($p) => $p->net_amount !== null ? (float) $p->net_amount : (float) $p->amount), 2),
            ]);
        }
        fputcsv($file, [
            'TOTAL KESELURUHAN',
            $paymentsFlat->count(),
            round((float) $paymentsFlat->sum('amount'), 2),
            round((float) $paymentsFlat->sum(fn ($p) => (float) $p->fee_amount), 2),
            round((float) $paymentsFlat->sum(fn ($p) => $p->net_amount !== null ? (float) $p->net_amount : (float) $p->amount), 2),
        ]);
        fputcsv($file, []);

        // 5. Ringkasan Penjualan per Produk
        $itemsFlat = $orders->flatMap(fn ($o) => $o->items)->values();
        $productTotals = $itemsFlat->groupBy(fn ($it) => $it->product_id ?: ($it->product_code ?: $it->product_name));

        fputcsv($file, ['--- 5. RINGKASAN PENJUALAN PER PRODUK ---']);
        fputcsv($file, ['Kode Produk / SKU', 'Nama Produk', 'Qty Terjual', 'Total Penjualan', 'Total HPP', 'Laba Kotor', 'Margin (%)']);
        foreach ($productTotals as $productItems) {
            $firstItem = $productItems->first();
            $sku = $firstItem->product_code ?: ($firstItem->product?->code ?: ($firstItem->product?->sku ?: '-'));
            $qty = (float) $productItems->sum('quantity');
            $sales = (float) $productItems->sum('total_price');
            $hpp = (float) $productItems->sum(fn ($it) => (float) $it->total_hpp > 0 ? (float) $it->total_hpp : ((float) $it->quantity * (float) $it->unit_cost_hpp));
            $profit = $sales - $hpp;
            $margin = $sales > 0 ? ($profit / $sales) * 100 : 0.0;

            fputcsv($file, [
                $sku,
                $firstItem->product_name,
                $this->formatQty($qty),
                round($sales, 2),
                round($hpp, 2),
                round($profit, 2),
                round($margin, 2) . '%',
            ]);
        }

        $itemsTotalSales = (float) $itemsFlat->sum('total_price');
        $itemsTotalHpp = (float) $itemsFlat->sum(fn ($it) => (float) $it->total_hpp > 0 ? (float) $it->total_hpp : ((float) $it->quantity * (float) $it->unit_cost_hpp));
        $itemsTotalProfit = $itemsTotalSales - $itemsTotalHpp;
        $itemsTotalMargin = $itemsTotalSales > 0 ? round(($itemsTotalProfit / $itemsTotalSales) * 100, 2) : 0.0;

        fputcsv($file, [
            'TOTAL KESELURUHAN', '',
            $this->formatQty((float) $itemsFlat->sum('quantity')),
            round($itemsTotalSales, 2),
            round($itemsTotalHpp, 2),
            round($itemsTotalProfit, 2),
            $itemsTotalMargin . '%',
        ]);
        fputcsv($file, []);
        fputcsv($file, ['--- AKHIR LAPORAN ---']);
    }

    /**
     * Format label tipe diskon POS ('percentage' => Persentase, 'fixed' => Nominal).
     */
    private function discountTypeLabel(?string $type, float $discountValue = 0.0, float $discountAmount = 0.0): string
    {
        if ($discountValue <= 0 && $discountAmount <= 0) {
            return 'Tanpa Diskon';
        }
        return match (strtolower((string) $type)) {
            'percentage' => 'Persentase (%)',
            'fixed' => 'Nominal',
            '', 'none', 'null', '0' => 'Tanpa Diskon',
            default => ucfirst(str_replace('_', ' ', (string) $type)),
        };
    }

    /**
     * Format label metode pembayaran POS ke nama yang mudah dibaca.
     */
    private function paymentMethodLabel(string $method): string
    {
        return match ($method) {
            'cash' => 'TUNAI',
            'qris' => 'QRIS',
            'transfer' => 'TRANSFER BANK',
            'edc_debit' => 'EDC DEBIT',
            'edc_credit' => 'EDC KREDIT',
            'customer_credit' => 'KREDIT PELANGGAN (PIUTANG)',
            'loyalty_points' => 'POIN LOYALITAS',
            default => strtoupper(str_replace('_', ' ', $method)),
        };
    }

    /**
     * Format qty agar tidak membawa angka desimal berlebihan (8000 => 8, 1500 => 1.5).
     */
    private function formatQty(float $quantity): string
    {
        $formatted = rtrim(rtrim(number_format($quantity, 4, '.', ''), '0'), '.');

        return $formatted === '' ? '0' : $formatted;
    }
}
