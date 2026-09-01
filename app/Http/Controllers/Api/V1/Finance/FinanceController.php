<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Finance;

use App\Models\ChartOfAccount;
use App\Models\Expense;
use App\Models\InvoicePayment;
use App\Models\JournalEntry;
use App\Support\Context;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class FinanceController extends Controller
{
    /**
     * Finance dashboard – P&L, receivables, payables.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $year  = (int) $request->input('year',  now()->year);
        $month = (int) $request->input('month', now()->month);

        $startDate = now()->setYear($year)->setMonth($month)->startOfMonth()->toDateString();
        $endDate   = now()->setYear($year)->setMonth($month)->endOfMonth()->toDateString();

        // Revenue from invoice payments in period
        $revenue = (float) InvoicePayment::where('business_id', $business->id)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount');

        // Expenses in period
        $totalExpenses = (float) Expense::where('business_id', $business->id)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        // Expenses by category
        $expensesByCategory = Expense::where('business_id', $business->id)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        // Monthly revenue trend (6 months)
        $revenueTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $mStart = now()->subMonths($i)->startOfMonth()->toDateString();
            $mEnd   = now()->subMonths($i)->endOfMonth()->toDateString();
            $revenueTrend[] = [
                'period' => now()->subMonths($i)->format('M Y'),
                'revenue' => (float) InvoicePayment::where('business_id', $business->id)
                    ->whereBetween('payment_date', [$mStart, $mEnd])
                    ->sum('amount'),
                'expenses' => (float) Expense::where('business_id', $business->id)
                    ->whereBetween('expense_date', [$mStart, $mEnd])
                    ->sum('amount'),
            ];
        }

        return response()->json([
            'period' => ['year' => $year, 'month' => $month, 'label' => now()->setYear($year)->setMonth($month)->format('F Y')],
            'summary' => [
                'revenue'    => $revenue,
                'expenses'   => $totalExpenses,
                'net_income' => round($revenue - $totalExpenses, 2),
            ],
            'expenses_by_category' => $expensesByCategory,
            'revenue_trend'        => $revenueTrend,
        ], Response::HTTP_OK);
    }

    /**
     * List expenses.
     */
    public function expenses(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $query = Expense::where('business_id', $business->id)
            ->with(['recorder:id,name', 'location:id,name'])
            ->latest('expense_date');

        if ($request->filled('category')) {
            $query->where('category', $request->get('category'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('expense_date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('expense_date', '<=', $request->get('date_to'));
        }

        $expenses = $query->paginate(20);

        $totalThisMonth = (float) Expense::where('business_id', $business->id)
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');

        return response()->json([
            'expenses' => $expenses->items(),
            'total_this_month' => $totalThisMonth,
            'pagination' => [
                'current_page' => $expenses->currentPage(),
                'last_page' => $expenses->lastPage(),
                'per_page' => $expenses->perPage(),
                'total' => $expenses->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Record an expense.
     */
    public function storeExpense(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        $validated = $request->validate([
            'expense_date'    => ['required', 'date'],
            'category'        => ['required', 'string', 'max:100'],
            'amount'          => ['required', 'numeric', 'min:0.01'],
            'payment_method'  => ['required', 'string', 'in:cash,bank_transfer,credit,other'],
            'account_id'      => ['nullable', 'exists:chart_of_accounts,id'],
            'location_id'     => ['nullable', 'exists:locations,id'],
            'description'     => ['nullable', 'string', 'max:500'],
        ]);

        $expense = Expense::create([
            'business_id' => $business->id,
            'expense_number' => 'EXP-' . now()->format('Ymd') . '-' . random_int(1000, 9999),
            ...$validated,
            'recorded_by' => $user?->id,
        ]);

        return response()->json([
            'message' => 'Pengeluaran berhasil dicatat.',
            'expense' => $expense,
        ], Response::HTTP_CREATED);
    }

    /**
     * Chart of accounts.
     */
    public function chartOfAccounts(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $accounts = ChartOfAccount::where('business_id', $business->id)
            ->orderBy('code')
            ->get();

        return response()->json([
            'accounts' => $accounts,
        ], Response::HTTP_OK);
    }

    /**
     * List journal entries.
     */
    public function journalEntries(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $query = JournalEntry::where('business_id', $business->id)
            ->with(['lines', 'creator:id,name'])
            ->latest('entry_date');

        if ($request->filled('date_from')) {
            $query->whereDate('entry_date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('entry_date', '<=', $request->get('date_to'));
        }

        if ($request->filled('reference_type')) {
            $query->where('reference_type', $request->get('reference_type'));
        }

        $entries = $query->paginate(20);

        return response()->json([
            'journal_entries' => $entries->items(),
            'pagination' => [
                'current_page' => $entries->currentPage(),
                'last_page' => $entries->lastPage(),
                'per_page' => $entries->perPage(),
                'total' => $entries->total(),
            ],
        ], Response::HTTP_OK);
    }
}
