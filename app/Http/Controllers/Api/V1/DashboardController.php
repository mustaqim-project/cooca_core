<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\InventoryStock;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\PosOrder;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class DashboardController extends Controller
{
    /**
     * Comprehensive mobile dashboard summary.
     */
    public function summary(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        // ── POS Today ────────────────────────────────────────────
        $posToday = PosOrder::where('business_id', $business->id)
            ->whereDate('created_at', $today)
            ->whereIn('status', ['completed', 'closed'])
            ->selectRaw('COUNT(*) as transactions, COALESCE(SUM(total_amount), 0) as revenue')
            ->first();

        // ── POS This Month ────────────────────────────────────────
        $posMonth = PosOrder::where('business_id', $business->id)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->whereIn('status', ['completed', 'closed'])
            ->selectRaw('COUNT(*) as transactions, COALESCE(SUM(total_amount), 0) as revenue')
            ->first();

        // ── Invoicing ─────────────────────────────────────────────
        $unpaidInvoices = Invoice::where('business_id', $business->id)
            ->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_UNPAID, Invoice::STATUS_PARTIALLY_PAID, Invoice::STATUS_OVERDUE])
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(balance_due), 0) as total_receivable')
            ->first();

        $overdueInvoices = Invoice::where('business_id', $business->id)
            ->where('status', Invoice::STATUS_OVERDUE)
            ->count();

        // ── Payments received this month ──────────────────────────
        $paymentsThisMonth = (float) InvoicePayment::where('business_id', $business->id)
            ->whereBetween('payment_date', [$monthStart, $monthEnd])
            ->sum('amount');

        // ── Expenses this month ───────────────────────────────────
        $expensesThisMonth = (float) Expense::where('business_id', $business->id)
            ->whereBetween('expense_date', [$monthStart, $monthEnd])
            ->sum('amount');

        // ── Inventory alerts ─────────────────────────────────────
        $lowStockCount = InventoryStock::whereHas('product', fn($q) => $q->where('business_id', $business->id))
            ->join('products', 'inventory_stocks.product_id', '=', 'products.id')
            ->whereColumn('inventory_stocks.quantity', '<=', 'products.min_stock')
            ->count();

        // ── Open POs ─────────────────────────────────────────────
        $openPurchaseOrders = PurchaseOrder::where('business_id', $business->id)
            ->whereIn('status', ['draft', 'confirmed', 'sent', 'partial'])
            ->count();

        // ── Customers ─────────────────────────────────────────────
        $totalCustomers = Customer::where('business_id', $business->id)->where('is_active', true)->count();
        $newCustomersMonth = Customer::where('business_id', $business->id)
            ->whereDate('created_at', '>=', $monthStart)
            ->count();

        // ── Revenue Trend (7 days) ────────────────────────────────
        $revenueTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $revenueTrend[] = [
                'date' => $date,
                'label' => now()->subDays($i)->format('D'),
                'pos_revenue' => (float) PosOrder::where('business_id', $business->id)
                    ->whereDate('created_at', $date)
                    ->whereIn('status', ['completed', 'closed'])
                    ->sum('total_amount'),
                'invoice_payments' => (float) InvoicePayment::where('business_id', $business->id)
                    ->whereDate('payment_date', $date)
                    ->sum('amount'),
            ];
        }

        // ── Top Products (this month) ─────────────────────────────
        $topProducts = PosOrder::where('pos_orders.business_id', $business->id)
            ->join('pos_order_items', 'pos_orders.id', '=', 'pos_order_items.pos_order_id')
            ->join('products', 'pos_order_items.product_id', '=', 'products.id')
            ->whereIn('pos_orders.status', ['completed', 'closed'])
            ->whereBetween('pos_orders.created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->selectRaw('products.id, products.name, SUM(pos_order_items.quantity) as total_qty, SUM(pos_order_items.subtotal) as total_revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        return response()->json([
            'business' => [
                'id' => $business->id,
                'name' => $business->name,
                'currency' => $business->currency ?? 'IDR',
                'currency_symbol' => $business->currency_symbol,
                'logo_url' => $business->logo_url,
            ],
            'pos' => [
                'today_transactions' => (int) ($posToday->transactions ?? 0),
                'today_revenue' => (float) ($posToday->revenue ?? 0),
                'month_transactions' => (int) ($posMonth->transactions ?? 0),
                'month_revenue' => (float) ($posMonth->revenue ?? 0),
            ],
            'invoicing' => [
                'unpaid_count' => (int) ($unpaidInvoices->count ?? 0),
                'total_receivable' => (float) ($unpaidInvoices->total_receivable ?? 0),
                'overdue_count' => $overdueInvoices,
                'payments_this_month' => $paymentsThisMonth,
            ],
            'finance' => [
                'net_revenue_this_month' => round($paymentsThisMonth + (float) ($posMonth->revenue ?? 0), 2),
                'expenses_this_month' => $expensesThisMonth,
                'net_profit_this_month' => round(
                    ($paymentsThisMonth + (float) ($posMonth->revenue ?? 0)) - $expensesThisMonth,
                    2
                ),
            ],
            'inventory' => [
                'low_stock_count' => $lowStockCount,
            ],
            'purchasing' => [
                'open_purchase_orders' => $openPurchaseOrders,
            ],
            'customers' => [
                'total_active' => $totalCustomers,
                'new_this_month' => $newCustomersMonth,
            ],
            'revenue_trend' => $revenueTrend,
            'top_products' => $topProducts,
            'generated_at' => now()->toIso8601String(),
        ], Response::HTTP_OK);
    }
}
