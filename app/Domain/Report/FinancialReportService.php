<?php

declare(strict_types=1);

namespace App\Domain\Report;

use App\Models\Business;
use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\Expense;
use App\Models\InventoryStock;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\PosOrder;
use App\Models\PosOrderPayment;
use App\Models\Product;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\SupplierInvoice;
use App\Models\SupplierPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class FinancialReportService
{
    /**
     * 1. Laporan Laba Rugi Komprehensif (Income Statement / Profit & Loss).
     * Menggabungkan transaksi POS + Invoice - Retur - HPP Aktual (Snapshot) - Beban Operasional.
     *
     * @return array<string, mixed>
     */
    public function getIncomeStatement(Business $business, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ? $startDate->copy()->startOfDay() : now()->startOfMonth()->startOfDay();
        $endDate   = $endDate ? $endDate->copy()->endOfDay() : now()->endOfMonth()->endOfDay();

        // 1.1 POS Revenue & COGS
        $posOrders = PosOrder::where('business_id', $business->id)
            ->whereIn('status', [PosOrder::STATUS_COMPLETED, PosOrder::STATUS_PARTIAL_REFUND])
            ->whereBetween('order_date', [$startDate, $endDate])
            ->with('items')
            ->get();

        $posGrossRevenue = (float) $posOrders->sum('subtotal');
        $posDiscounts    = (float) $posOrders->sum('discount_amount') + (float) $posOrders->sum('voucher_discount_amount') + (float) $posOrders->sum('points_discount_amount');
        $posNetRevenue   = max(0, $posGrossRevenue - $posDiscounts);
        $posTax          = (float) $posOrders->sum('tax_amount');
        $posServiceFee   = (float) $posOrders->sum('service_charge_amount');

        $posCogs = 0.0;
        foreach ($posOrders as $order) {
            foreach ($order->items as $item) {
                $posCogs += (float) ($item->total_hpp > 0 ? $item->total_hpp : ($item->quantity * $item->unit_cost_hpp));
            }
        }

        // 1.2 Invoice Revenue & COGS
        $invoices = Invoice::where('business_id', $business->id)
            ->whereNotIn('status', [Invoice::STATUS_DRAFT, Invoice::STATUS_VOID])
            ->whereBetween('invoice_date', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->with('items')
            ->get();

        $invoiceGrossRevenue = (float) $invoices->sum('subtotal');
        $invoiceDiscounts    = (float) $invoices->sum('discount_amount');
        $invoiceNetRevenue   = max(0, $invoiceGrossRevenue - $invoiceDiscounts);
        $invoiceTax          = (float) $invoices->sum('tax_amount');

        $invoiceCogs = 0.0;
        foreach ($invoices as $invoice) {
            foreach ($invoice->items as $item) {
                $invoiceCogs += (float) ($item->total_hpp > 0 ? $item->total_hpp : ($item->quantity * $item->unit_hpp));
            }
        }

        // 1.3 Sales Returns (Pengurang Penjualan & Pemulih HPP)
        $salesReturns = SalesReturn::where('business_id', $business->id)
            ->whereIn('status', [SalesReturn::STATUS_APPROVED, SalesReturn::STATUS_COMPLETED])
            ->whereBetween('return_date', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->with('items')
            ->get();

        $totalReturnsAmount = (float) $salesReturns->sum('total_amount');
        $returnsCogsRecovery = 0.0;
        foreach ($salesReturns as $ret) {
            foreach ($ret->items as $item) {
                $returnsCogsRecovery += (float) ($item->quantity * $item->unit_hpp);
            }
        }

        // 1.4 Net Revenue & Net COGS
        $totalGrossSales = $posGrossRevenue + $invoiceGrossRevenue;
        $totalDiscounts  = $posDiscounts + $invoiceDiscounts;
        $totalNetSales   = max(0, ($totalGrossSales - $totalDiscounts) - $totalReturnsAmount);

        $totalCogs = max(0, ($posCogs + $invoiceCogs) - $returnsCogsRecovery);
        $grossProfit = $totalNetSales - $totalCogs;
        $grossProfitMargin = $totalNetSales > 0 ? round(($grossProfit / $totalNetSales) * 100, 2) : 0.0;

        // 1.5 Operating Expenses
        $expenses = Expense::where('business_id', $business->id)
            ->whereBetween('expense_date', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->get();

        $expenseCategories = [];
        foreach ($expenses as $exp) {
            $cat = $exp->category ?: 'Lain-lain';
            $expenseCategories[$cat] = ($expenseCategories[$cat] ?? 0.0) + (float) $exp->amount;
        }
        $totalOperatingExpenses = (float) array_sum($expenseCategories);

        // 1.6 Net Profit
        $netOperatingProfit = $grossProfit - $totalOperatingExpenses;
        $netProfitMargin = $totalNetSales > 0 ? round(($netOperatingProfit / $totalNetSales) * 100, 2) : 0.0;

        return [
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date'   => $endDate->toDateString(),
                'label'      => $startDate->format('d M Y') . ' — ' . $endDate->format('d M Y'),
            ],
            'revenues' => [
                'pos_gross_sales'     => $posGrossRevenue,
                'pos_discounts'       => $posDiscounts,
                'pos_net_sales'       => $posNetRevenue,
                'pos_tax'             => $posTax,
                'pos_service_fee'     => $posServiceFee,
                'invoice_gross_sales' => $invoiceGrossRevenue,
                'invoice_discounts'   => $invoiceDiscounts,
                'invoice_net_sales'   => $invoiceNetRevenue,
                'invoice_tax'         => $invoiceTax,
                'total_gross_sales'   => $totalGrossSales,
                'total_discounts'     => $totalDiscounts,
                'sales_returns'       => $totalReturnsAmount,
                'net_sales'           => $totalNetSales,
            ],
            'cogs' => [
                'pos_cogs'              => $posCogs,
                'invoice_cogs'          => $invoiceCogs,
                'returns_cogs_recovery' => $returnsCogsRecovery,
                'total_cogs'            => $totalCogs,
            ],
            'gross_profit' => [
                'amount' => $grossProfit,
                'margin' => $grossProfitMargin,
            ],
            'expenses' => [
                'by_category' => $expenseCategories,
                'total'       => $totalOperatingExpenses,
            ],
            'net_profit' => [
                'amount' => $netOperatingProfit,
                'margin' => $netProfitMargin,
            ],
        ];
    }

    /**
     * 2. Laporan Arus Kas (Cash Flow Statement).
     * Merekap aliran kas masuk (Inflows) dan keluar (Outflows) riil periode ini.
     *
     * @return array<string, mixed>
     */
    public function getCashFlowStatement(Business $business, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ? $startDate->copy()->startOfDay() : now()->startOfMonth()->startOfDay();
        $endDate   = $endDate ? $endDate->copy()->endOfDay() : now()->endOfMonth()->endOfDay();

        // 2.1 Cash Inflows
        // POS Payments
        $posPayments = PosOrderPayment::whereHas('order', function ($q) use ($business, $startDate, $endDate) {
            $q->where('business_id', $business->id)
              ->whereNotIn('status', [PosOrder::STATUS_VOIDED, PosOrder::STATUS_DRAFT_HELD])
              ->whereBetween('order_date', [$startDate, $endDate]);
        })->sum('amount');

        // Invoice Customer Payments
        $invoicePayments = InvoicePayment::whereHas('invoice', function ($q) use ($business) {
            $q->where('business_id', $business->id);
        })->whereBetween('payment_date', [$startDate->startOfDay(), $endDate->endOfDay()])
          ->sum('amount');

        // General Cash In
        $cashInTransactions = (float) CashTransaction::where('business_id', $business->id)
            ->where('type', CashTransaction::TYPE_IN)
            ->whereBetween('transaction_date', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->sum('amount');

        $totalInflow = (float) $posPayments + (float) $invoicePayments + $cashInTransactions;

        // 2.2 Cash Outflows
        // Supplier Payments (AP)
        $supplierPayments = (float) SupplierPayment::where('business_id', $business->id)
            ->whereBetween('payment_date', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->sum('amount');

        // Operating Expenses Paid
        $expensesPaid = (float) Expense::where('business_id', $business->id)
            ->whereBetween('expense_date', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->sum('amount');

        // Cash Refunds from Sales Returns
        $refundsPaid = (float) SalesReturn::where('business_id', $business->id)
            ->where('status', SalesReturn::STATUS_COMPLETED)
            ->where('refund_method', SalesReturn::REFUND_CASH)
            ->whereBetween('return_date', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->sum('total_amount');

        // General Cash Out
        $cashOutTransactions = (float) CashTransaction::where('business_id', $business->id)
            ->where('type', CashTransaction::TYPE_OUT)
            ->whereBetween('transaction_date', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->sum('amount');

        $totalOutflow = $supplierPayments + $expensesPaid + $refundsPaid + $cashOutTransactions;
        $netCashFlow  = $totalInflow - $totalOutflow;

        // 2.3 Current Balances from Tenant Cash & Bank Accounts
        // Catatan: `payment_accounts` adalah rekening platform/SaaS (lihat docs/implementation_plan.md),/
        // bukan ledger kas tenant — saldo kas tenant hanya berasal dari `cash_accounts`.
        $cashAccounts = CashAccount::where('business_id', $business->id)
            ->where('is_active', true)
            ->get();
        $totalAccountBalance = (float) $cashAccounts->sum('current_balance');

        return [
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date'   => $endDate->toDateString(),
                'label'      => $startDate->format('d M Y') . ' — ' . $endDate->format('d M Y'),
            ],
            'inflows' => [
                'pos_payments'     => (float) $posPayments,
                'invoice_payments' => (float) $invoicePayments,
                'direct_cash_in'   => $cashInTransactions,
                'total'            => $totalInflow,
            ],
            'outflows' => [
                'supplier_payments' => $supplierPayments,
                'expenses'          => $expensesPaid,
                'cash_refunds'      => $refundsPaid,
                'direct_cash_out'   => $cashOutTransactions,
                'total'             => $totalOutflow,
            ],
            'net_cash_flow' => $netCashFlow,
            'accounts' => [
                'cash_accounts' => $cashAccounts->map(fn($a) => [
                    'name'    => $a->name,
                    'type'    => $a->type,
                    'balance' => (float) $a->current_balance,
                ])->values()->all(),
                'total_balance' => $totalAccountBalance,
            ],
        ];
    }

    /**
     * 3. AR/AP Aging Report (Umur Piutang & Hutang).
     *
     * @return array<string, mixed>
     */
    public function getAgingSummary(Business $business): array
    {
        $today = Carbon::today();

        // 3.1 AR Aging (Piutang Pelanggan dari Invoice)
        $unpaidInvoices = Invoice::with('customer')
            ->where('business_id', $business->id)
            ->whereNotIn('status', [Invoice::STATUS_DRAFT, Invoice::STATUS_VOID, Invoice::STATUS_PAID])
            ->where('balance_due', '>', 0)
            ->get();

        $arBuckets = [
            'current' => 0.0, // Belum jatuh tempo
            '1_30'    => 0.0, // 1-30 hari
            '31_60'   => 0.0, // 31-60 hari
            '61_90'   => 0.0, // 61-90 hari
            'over_90' => 0.0, // > 90 hari (kritis)
        ];

        $arDetails = [];
        foreach ($unpaidInvoices as $inv) {
            $dueDate = $inv->due_date ? Carbon::parse($inv->due_date) : Carbon::parse($inv->invoice_date)->addDays(30);
            $daysOverdue = $dueDate->isPast() ? (int) $dueDate->diffInDays($today) : 0;
            $balance = (float) $inv->balance_due;

            if ($daysOverdue <= 0) {
                $arBuckets['current'] += $balance;
                $bucketLabel = 'Belum Jatuh Tempo';
            } elseif ($daysOverdue <= 30) {
                $arBuckets['1_30'] += $balance;
                $bucketLabel = '1 - 30 Hari';
            } elseif ($daysOverdue <= 60) {
                $arBuckets['31_60'] += $balance;
                $bucketLabel = '31 - 60 Hari';
            } elseif ($daysOverdue <= 90) {
                $arBuckets['61_90'] += $balance;
                $bucketLabel = '61 - 90 Hari';
            } else {
                $arBuckets['over_90'] += $balance;
                $bucketLabel = '> 90 Hari (Macet)';
            }

            $arDetails[] = [
                'invoice_id'     => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'customer_name'  => $inv->customer?->name ?? 'Pelanggan Umum',
                'invoice_date'   => $inv->invoice_date?->format('d/m/Y'),
                'due_date'       => $dueDate->format('d/m/Y'),
                'days_overdue'   => $daysOverdue,
                'total_amount'   => (float) $inv->total_amount,
                'balance_due'    => $balance,
                'bucket'         => $bucketLabel,
            ];
        }

        // 3.2 AP Aging (Hutang Supplier)
        $unpaidBills = SupplierInvoice::with('supplier')
            ->where('business_id', $business->id)
            ->whereNotIn('status', ['paid', 'void'])
            ->where('balance_due', '>', 0)
            ->get();

        $apBuckets = [
            'current' => 0.0,
            '1_30'    => 0.0,
            '31_60'   => 0.0,
            'over_60' => 0.0,
        ];

        $apDetails = [];
        foreach ($unpaidBills as $bill) {
            $dueDate = $bill->due_date ? Carbon::parse($bill->due_date) : Carbon::parse($bill->invoice_date)->addDays(30);
            $daysOverdue = $dueDate->isPast() ? (int) $dueDate->diffInDays($today) : 0;
            $balance = (float) $bill->balance_due;

            if ($daysOverdue <= 0) {
                $apBuckets['current'] += $balance;
                $bucketLabel = 'Belum Jatuh Tempo';
            } elseif ($daysOverdue <= 30) {
                $apBuckets['1_30'] += $balance;
                $bucketLabel = '1 - 30 Hari';
            } elseif ($daysOverdue <= 60) {
                $apBuckets['31_60'] += $balance;
                $bucketLabel = '31 - 60 Hari';
            } else {
                $apBuckets['over_60'] += $balance;
                $bucketLabel = '> 60 Hari';
            }

            $apDetails[] = [
                'bill_id'        => $bill->id,
                'invoice_number' => $bill->invoice_number,
                'supplier_name'  => $bill->supplier?->name ?? 'Supplier',
                'invoice_date'   => $bill->invoice_date ? date('d/m/Y', strtotime((string)$bill->invoice_date)) : '-',
                'due_date'       => $dueDate->format('d/m/Y'),
                'days_overdue'   => $daysOverdue,
                'total_amount'   => (float) $bill->total_amount,
                'balance_due'    => $balance,
                'bucket'         => $bucketLabel,
            ];
        }

        return [
            'ar' => [
                'buckets'       => $arBuckets,
                'total_balance' => (float) array_sum($arBuckets),
                'details'       => $arDetails,
            ],
            'ap' => [
                'buckets'       => $apBuckets,
                'total_balance' => (float) array_sum($apBuckets),
                'details'       => $apDetails,
            ],
        ];
    }

    /**
     * 4. Laporan Valuasi Stok & Analisis Perputaran (Turnover / Fast & Dead Stock).
     *
     * @return array<string, mixed>
     */
    public function getStockValuationAndTurnover(Business $business): array
    {
        $products = Product::with(['category', 'outputUnit', 'stocks.location', 'costModels.latestVersion'])
            ->where('business_id', $business->id)
            ->where('is_active', true)
            ->get();

        $thirtyDaysAgo = now()->subDays(30);

        // Sales aggregation 30 hari terakhir
        $posSalesByProduct = DB::table('pos_order_items')
            ->join('pos_orders', 'pos_order_items.pos_order_id', '=', 'pos_orders.id')
            ->where('pos_orders.business_id', $business->id)
            ->whereIn('pos_orders.status', [PosOrder::STATUS_COMPLETED, PosOrder::STATUS_PARTIAL_REFUND])
            ->where('pos_orders.order_date', '>=', $thirtyDaysAgo)
            ->groupBy('pos_order_items.product_id')
            ->select('pos_order_items.product_id', DB::raw('SUM(pos_order_items.quantity) as total_qty'), DB::raw('SUM(pos_order_items.subtotal) as total_revenue'))
            ->get()
            ->keyBy('product_id');

        $invoiceSalesByProduct = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoices.business_id', $business->id)
            ->whereNotIn('invoices.status', [Invoice::STATUS_DRAFT, Invoice::STATUS_VOID])
            ->where('invoices.invoice_date', '>=', $thirtyDaysAgo->toDateString())
            ->groupBy('invoice_items.product_id')
            ->select('invoice_items.product_id', DB::raw('SUM(invoice_items.quantity) as total_qty'), DB::raw('SUM(invoice_items.subtotal) as total_revenue'))
            ->get()
            ->keyBy('product_id');

        $totalValuation = 0.0;
        $totalPhysicalUnits = 0.0;
        $items = [];
        $categoryBreakdown = [];

        foreach ($products as $p) {
            $totalQty = (float) $p->stocks->sum('quantity');
            $unitCost = (float) ($p->stocks->avg('avg_purchase_cost') ?: ($p->costModels->first()?->latestVersion?->hpp_per_unit ?: $p->purchase_price ?: 0.0));
            $valuation = $totalQty * $unitCost;

            $totalPhysicalUnits += $totalQty;
            $totalValuation += $valuation;

            $soldQty = (float) (($posSalesByProduct[$p->id]->total_qty ?? 0) + ($invoiceSalesByProduct[$p->id]->total_qty ?? 0));
            $soldRevenue = (float) (($posSalesByProduct[$p->id]->total_revenue ?? 0) + ($invoiceSalesByProduct[$p->id]->total_revenue ?? 0));

            // Velocity classification
            if ($soldQty >= 20) {
                $velocity = 'fast_moving';
                $velocityLabel = 'Fast Moving';
            } elseif ($soldQty > 0) {
                $velocity = 'medium_moving';
                $velocityLabel = 'Reguler';
            } elseif ($totalQty > 0) {
                $velocity = 'dead_stock';
                $velocityLabel = 'Slow / Dead Stock';
            } else {
                $velocity = 'out_of_stock';
                $velocityLabel = 'Habis';
            }

            $catName = $p->category?->name ?? 'Umum';
            $categoryBreakdown[$catName] = ($categoryBreakdown[$catName] ?? 0.0) + $valuation;

            $items[] = [
                'product_id'     => $p->id,
                'name'           => $p->name,
                'sku'            => $p->sku ?? '-',
                'category'       => $catName,
                'unit'           => $p->outputUnit?->name ?? 'pcs',
                'current_stock'  => $totalQty,
                'unit_cost'      => $unitCost,
                'valuation'      => $valuation,
                'sold_30d_qty'   => $soldQty,
                'sold_30d_rev'   => $soldRevenue,
                'velocity'       => $velocity,
                'velocity_label' => $velocityLabel,
            ];
        }

        // Urutkan Fast moving dan Dead stock
        $fastMoving = collect($items)->where('sold_30d_qty', '>', 0)->sortByDesc('sold_30d_qty')->take(5)->values()->all();
        $deadStock  = collect($items)->where('velocity', 'dead_stock')->sortByDesc('valuation')->take(5)->values()->all();

        return [
            'summary' => [
                'total_valuation'      => $totalValuation,
                'total_physical_units' => $totalPhysicalUnits,
                'total_products'       => count($products),
                'category_breakdown'   => $categoryBreakdown,
            ],
            'fast_moving' => $fastMoving,
            'dead_stock'  => $deadStock,
            'all_items'   => $items,
        ];
    }

    /**
     * Detail transaksi periodik untuk export Excel Laba Rugi.


     * @return array<string, mixed>
     */
    public function getIncomeStatementDetail(Business $business, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ? $startDate->copy()->startOfDay() : now()->startOfMonth()->startOfDay();
        $endDate   = $endDate ? $endDate->copy()->endOfDay() : now()->endOfMonth()->endOfDay();

        $posOrders = PosOrder::with('customer')
            ->where('business_id', $business->id)
            ->whereIn('status', [PosOrder::STATUS_COMPLETED, PosOrder::STATUS_PARTIAL_REFUND])
            ->whereBetween('order_date', [$startDate, $endDate])
            ->orderBy('order_date')
            ->get()
            ->map(fn (PosOrder $o) => [
                'order_number' => $o->order_number,
                'order_date'    => $o->order_date?->format('d/m/Y H:i'),
                'customer'      => $o->customer?->name ?? $o->customer_name_guest ?? '-',
                'status'        => $o->status,
                'subtotal'     => (float) $o->subtotal,
                'discount'     => (float) $o->discount_amount + (float) $o->voucher_discount_amount + (float) $o->points_discount_amount,
                'tax'          => (float) $o->tax_amount,
                'service_fee'  => (float) $o->service_charge_amount,
                'total'        => (float) $o->total_amount,
            ])->values()->all();

        $invoices = Invoice::with('customer')
            ->where('business_id', $business->id)
            ->whereNotIn('status', [Invoice::STATUS_DRAFT, Invoice::STATUS_VOID])
            ->whereBetween('invoice_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('invoice_date')
            ->get()
            ->map(fn (Invoice $i) => [
                'invoice_number' => $i->invoice_number,
                'invoice_date'    => $i->invoice_date?->format('d/m/Y'),
                'customer'        => $i->customer?->name ?? 'Pelanggan Umum',
                'status'          => $i->status,
                'subtotal'        => (float) $i->subtotal,
                'discount'       => (float) $i->discount_amount,
                'tax'            => (float) $i->tax_amount,
                'total'          => (float) $i->total_amount,
                'paid'           => (float) $i->paid_amount,
                'balance_due'    => (float) $i->balance_due,
            ])->values()->all();

        $returns = SalesReturn::with('items')
            ->where('business_id', $business->id)
            ->whereIn('status', [SalesReturn::STATUS_APPROVED, SalesReturn::STATUS_COMPLETED])
            ->whereBetween('return_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('return_date')
            ->get()
            ->map(fn (SalesReturn $r) => [
                'return_number' => $r->return_number,
                'return_date'    => $r->return_date?->format('d/m/Y'),
                'status'         => $r->status,
                'refund_method' => $r->refund_method ?? '-',
                'total_amount'   => (float) $r->total_amount,
                'reason'         => $r->reason ?? '-',
            ])->values()->all();

        $expenses = Expense::where('business_id', $business->id)
            ->whereBetween('expense_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('expense_date')
            ->get()
            ->map(fn (Expense $e) => [
                'expense_number' => $e->expense_number ?? '-',
                'expense_date'    => $e->expense_date?->format('d/m/Y'),
                'category'        => $e->category ?? 'Lain-lain',
                'amount'          => (float) $e->amount,
                'payment_method' => $e->payment_method ?? '-',
                'description'     => $e->description ?? '-',
            ])->values()->all();

        return [
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date'   => $endDate->toDateString(),
                'label'      => $startDate->format('d M Y') . ' — ' . $endDate->format('d M Y'),
            ],
            'pos_orders' => $posOrders,
            'invoices' => $invoices,
            'returns' => $returns,
            'expenses' => $expenses,
        ];
    }

    /**
     * Detail mutasi kas periodik untuk export Excel Arus Kas.


     * @return array<string, mixed>
     */
    public function getCashFlowDetail(Business $business, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ? $startDate->copy()->startOfDay() : now()->startOfMonth()->startOfDay();
        $endDate   = $endDate ? $endDate->copy()->endOfDay() : now()->endOfMonth()->endOfDay();

        $transactions = [];

        PosOrderPayment::with('order')
            ->whereHas('order', function ($q) use ($business, $startDate, $endDate) {
                $q->where('business_id', $business->id)
                  ->whereNotIn('status', [PosOrder::STATUS_VOIDED, PosOrder::STATUS_DRAFT_HELD])
                  ->whereBetween('order_date', [$startDate, $endDate]);
            })
            ->get()
            ->each(function (PosOrderPayment $p) use (&$transactions): void {
                $transactions[] = [
                    'tanggal'   => $p->order?->order_date?->format('d/m/Y H:i'),
                    'jenis'     => 'Penerimaan Kasir POS',
                    'referensi' => $p->order?->order_number ?? '-',
                    'metode'    => $p->payment_method ?? '-',
                    'keterangan' => $p->notes ?? '-',
                    'jumlah'     => (float) $p->amount,
                    'arah'       => 'in',
                ];
            });

        InvoicePayment::with('invoice')
            ->whereHas('invoice', function ($q) use ($business) {
                $q->where('business_id', $business->id);
            })
            ->whereBetween('payment_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->each(function (InvoicePayment $p) use (&$transactions): void {
                $transactions[] = [
                    'tanggal'   => $p->payment_date?->format('d/m/Y'),
                    'jenis'     => 'Pelunasan Piutang Invoice',
                    'referensi' => $p->payment_number ?? $p->invoice?->invoice_number ?? '-',
                    'metode'    => $p->payment_method ?? '-',
                    'keterangan' => $p->notes ?? '-',
                    'jumlah'     => (float) $p->amount,
                    'arah'       => 'in',
                ];
            });

        CashTransaction::with('cashAccount')
            ->where('business_id', $business->id)
            ->whereIn('type', [CashTransaction::TYPE_IN, CashTransaction::TYPE_OUT])
            ->whereBetween('transaction_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->each(function (CashTransaction $ct) use (&$transactions): void {
                $isIn = $ct->type === CashTransaction::TYPE_IN;
                $transactions[] = [
                    'tanggal'   => $ct->transaction_date?->format('d/m/Y'),
                    'jenis'     => $isIn ? 'Kas Masuk Langsung' : 'Kas Keluar Langsung',
                    'referensi' => $ct->reference_id ?? $ct->cashAccount?->name ?? '-',
                    'metode'    => $ct->cashAccount?->type ?? '-',
                    'keterangan' => $ct->description ?? '-',
                    'jumlah'     => (float) $ct->amount,
                    'arah'       => $isIn ? 'in' : 'out',
                ];
            });

        SupplierPayment::with('supplierInvoice.supplier')
            ->where('business_id', $business->id)
            ->whereBetween('payment_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->each(function (SupplierPayment $sp) use (&$transactions): void {
                $transactions[] = [
                    'tanggal'   => $sp->payment_date?->format('d/m/Y'),
                    'jenis'     => 'Bayar Hutang Supplier (AP)',
                    'referensi' => $sp->payment_number ?? $sp->supplierInvoice?->invoice_number ?? '-',
                    'metode'    => $sp->payment_method ?? '-',
                    'keterangan' => $sp->notes ?? ($sp->supplierInvoice?->supplier?->name ?? '-'),
                    'jumlah'     => (float) $sp->amount,
                    'arah'       => 'out',
                ];
            });

        SalesReturn::where('business_id', $business->id)
            ->where('status', SalesReturn::STATUS_COMPLETED)
            ->where('refund_method', SalesReturn::REFUND_CASH)
            ->whereBetween('return_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->each(function (SalesReturn $sr) use (&$transactions): void {
                $transactions[] = [
                    'tanggal'   => $sr->return_date?->format('d/m/Y'),
                    'jenis'     => 'Refund Kas Retur Penjualan',
                    'referensi' => $sr->return_number,
                    'metode'    => 'cash_refund',
                    'keterangan' => $sr->reason ?? '-',
                    'jumlah'     => (float) $sr->total_amount,
                    'arah'       => 'out',
                ];
            });

        usort($transactions, static function (array $a, array $b): int {
            return strcmp((string) $a['tanggal'] ?? '', (string) $b['tanggal'] ?? '');
        });

        $cashAccounts = CashAccount::where('business_id', $business->id)
            ->where('is_active', true)
            ->get()
            ->map(fn (CashAccount $ca) => [
                'name'    => $ca->name,
                'type'    => $ca->type,
                'balance' => (float) $ca->current_balance,
            ])->values()->all();

        return [
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date'   => $endDate->toDateString(),
                'label'      => $startDate->format('d M Y') . ' — ' . $endDate->format('d M Y'),
            ],
            'transactions' => $transactions,
            'accounts' => $cashAccounts,
        ];
    }
}
