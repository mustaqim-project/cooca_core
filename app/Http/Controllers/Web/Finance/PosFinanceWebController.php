<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Finance;

use App\Domain\Accounting\AutoJournalService;
use App\Domain\Finance\CashLedgerService;
use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\ChartOfAccount;
use App\Models\Expense;
use App\Models\JournalEntry;
use App\Models\Location;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class PosFinanceWebController extends Controller
{
    public function __construct(
        private readonly AutoJournalService $journalService = new AutoJournalService,
        private readonly CashLedgerService $cashLedgerService = new CashLedgerService
    ) {}

    /**
     * Display automated double-entry journal entries.
     */
    public function journals(Request $request): View
    {
        $business = Context::requireBusiness();
        $this->journalService->ensureStandardAccounts($business);

        $query = JournalEntry::where('business_id', $business->id)
            ->with(['lines.account', 'creator'])
            ->latest('entry_date');

        if ($request->filled('reference_type')) {
            $query->where('reference_type', $request->get('reference_type'));
        }

        if ($request->filled('search')) {
            $search = '%' . trim($request->get('search')) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('entry_number', 'like', $search)
                    ->orWhere('description', 'like', $search);
            });
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('entry_date', [$request->get('start_date'), $request->get('end_date')]);
        }

        $entries = $query->paginate(20)->withQueryString();

        $accounts = ChartOfAccount::where('business_id', $business->id)
            ->withCount('lines')
            ->orderBy('code')
            ->get();

        $totalDebit = (float) JournalEntry::where('business_id', $business->id)->sum('total_debit');
        $totalCredit = (float) JournalEntry::where('business_id', $business->id)->sum('total_credit');
        $isBalanced = abs($totalDebit - $totalCredit) < 0.01;

        return view('app.finance.journals', compact('business', 'entries', 'accounts', 'totalDebit', 'totalCredit', 'isBalanced'));
    }

    /**
     * Display and record operational expenses.
     */
    public function expenses(Request $request): View
    {
        $business = Context::requireBusiness();
        $this->journalService->ensureStandardAccounts($business);

        $query = Expense::where('business_id', $business->id)
            ->with(['account', 'location', 'recorder'])
            ->latest('expense_date');

        if ($request->filled('category')) {
            $query->where('category', $request->get('category'));
        }

        if ($request->filled('search')) {
            $search = '%' . trim($request->get('search')) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('expense_number', 'like', $search)
                    ->orWhere('description', 'like', $search)
                    ->orWhere('category', 'like', $search);
            });
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('expense_date', [$request->get('start_date'), $request->get('end_date')]);
        }

        $expenses = $query->paginate(20)->withQueryString();
        $locations = Location::where('business_id', $business->id)->where('is_active', true)->get();
        $accounts = ChartOfAccount::where('business_id', $business->id)->where('type', ChartOfAccount::TYPE_EXPENSE)->orderBy('code')->get();
        $cashAccounts = CashAccount::where('business_id', $business->id)->where('is_active', true)->orderBy('name')->get();

        $totalExpensesThisMonth = (float) Expense::where('business_id', $business->id)
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');

        $categories = Expense::where('business_id', $business->id)->distinct()->pluck('category')->filter()->values();

        return view('app.finance.expenses', compact('business', 'expenses', 'locations', 'accounts', 'cashAccounts', 'totalExpensesThisMonth', 'categories'));
    }

    /**
     * Store an operational expense and record automatic journal.
     */
    public function storeExpense(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $validated = $request->validate([
            'expense_date' => ['required', 'date'],
            'category' => ['required', 'string', 'max:50'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'in:cash,bank_transfer,petty_cash'],
            'cash_account_id' => ['nullable', 'string', 'exists:cash_accounts,id'],
            'account_id' => ['nullable', 'string', 'exists:chart_of_accounts,id'],
            'location_id' => ['nullable', 'string'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        try {
            $expense = DB::transaction(function () use ($business, $user, $validated) {
                $expenseNumber = 'EXP-' . date('Ymd') . '-' . rand(100, 999);

                $expense = Expense::create([
                    'business_id' => $business->id,
                    'location_id' => $validated['location_id'] ?? null,
                    'expense_number' => $expenseNumber,
                    'expense_date' => $validated['expense_date'],
                    'category' => $validated['category'],
                    'amount' => (float) $validated['amount'],
                    'payment_method' => $validated['payment_method'],
                    'account_id' => $validated['account_id'] ?? null,
                    'description' => $validated['description'],
                    'recorded_by' => $user->id,
                ]);

                // Auto-journal
                $this->journalService->recordExpenseJournal($expense, $user);

                $cashAccount = ! empty($validated['cash_account_id'])
                    ? CashAccount::where('business_id', $business->id)->find($validated['cash_account_id'])
                    : null;

                $this->cashLedgerService->recordOutflow(
                    $business,
                    (float) $expense->amount,
                    'expense',
                    $expense->id,
                    "Pengeluaran #{$expense->expense_number}",
                    $expense->payment_method,
                    $user->id,
                    $cashAccount
                );

                return $expense;
            });

            return redirect()->back()->with('success', "Biaya operasional #{$expense->expense_number} berhasil dicatat.");
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->withErrors(['amount' => $e->getMessage()]);
        }
    }
}
