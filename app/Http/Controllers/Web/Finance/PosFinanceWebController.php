<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Finance;

use App\Domain\Accounting\AutoJournalService;
use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\Expense;
use App\Models\JournalEntry;
use App\Models\Location;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PosFinanceWebController extends Controller
{
    public function __construct(
        private readonly AutoJournalService $journalService = new AutoJournalService
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

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('entry_date', [$request->get('start_date'), $request->get('end_date')]);
        }

        $entries = $query->paginate(20)->withQueryString();

        $accounts = ChartOfAccount::where('business_id', $business->id)
            ->withCount('lines')
            ->orderBy('code')
            ->get();

        $totalDebit = JournalEntry::where('business_id', $business->id)->sum('total_debit');
        $totalCredit = JournalEntry::where('business_id', $business->id)->sum('total_credit');

        return view('app.finance.journals', compact('business', 'entries', 'accounts', 'totalDebit', 'totalCredit'));
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

        $expenses = $query->paginate(20)->withQueryString();
        $locations = Location::where('business_id', $business->id)->where('is_active', true)->get();
        $accounts = ChartOfAccount::where('business_id', $business->id)->where('type', ChartOfAccount::TYPE_EXPENSE)->get();

        $totalExpensesThisMonth = Expense::where('business_id', $business->id)
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');

        return view('app.finance.expenses', compact('business', 'expenses', 'locations', 'accounts', 'totalExpensesThisMonth'));
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
            'account_id' => ['nullable', 'string'],
            'location_id' => ['nullable', 'string'],
            'description' => ['required', 'string', 'max:255'],
        ]);

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

        return redirect()->back()->with('success', "Biaya operasional #{$expenseNumber} berhasil dicatat.");
    }
}
