<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Finance;

use App\Domain\Finance\CashLedgerService;
use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\Invoice;
use App\Models\PosOrder;
use App\Models\PosOrderPayment;
use App\Models\SupplierInvoice;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use InvalidArgumentException;

final class CashLedgerWebController extends Controller
{
    public function __construct(private readonly CashLedgerService $service = new CashLedgerService) {}

    public function index(Request $request): View
    {
        $business = Context::requireBusiness();
        $period = $request->get('period', 'today');

        $posPaymentsQuery = PosOrderPayment::whereHas('order', function ($q) use ($business) {
            $q->where('business_id', $business->id)
                ->where('status', PosOrder::STATUS_COMPLETED);
        })->where('status', 'paid');

        if ($period === 'today') {
            $posPaymentsQuery->whereDate('created_at', now()->toDateString());
        } elseif ($period === 'month') {
            $posPaymentsQuery->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month);
        }

        $posSummaryByMethod = (clone $posPaymentsQuery)
            ->select('payment_method', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as total_count'))
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        $totalPosRevenue = (float) (clone $posPaymentsQuery)->sum('amount');

        return view('app.finance.cash-bank.index', [
            'business' => $business,
            'accounts' => CashAccount::where('business_id', $business->id)->where('is_active', true)->orderBy('name')->get(),
            'transactions' => CashTransaction::with('cashAccount')->where('business_id', $business->id)->latest('transaction_date')->latest('created_at')->limit(30)->get(),
            'posSummaryByMethod' => $posSummaryByMethod,
            'totalPosRevenue' => $totalPosRevenue,
            'activePeriod' => $period,
        ]);
    }

    public function ledger(Request $request): View
    {
        $business = Context::requireBusiness();
        $account = $request->filled('account_id')
            ? CashAccount::where('business_id', $business->id)->findOrFail($request->string('account_id')->toString())
            : null;

        $query = CashTransaction::with('cashAccount')
            ->where('business_id', $business->id)
            ->when($account, fn ($q) => $q->where('cash_account_id', $account->id));

        if ($request->filled('type') && in_array($request->get('type'), ['in', 'out', 'transfer'], true)) {
            $query->where('type', $request->get('type'));
        }

        if ($request->filled('method')) {
            $m = strtolower(trim($request->string('method')->toString()));
            $searchKeyword = match ($m) {
                'cash' => 'Tunai',
                'qris' => 'QRIS',
                'transfer', 'bank_transfer' => 'Transfer Bank',
                'edc_debit', 'edc_credit', 'edc' => 'EDC',
                default => $m,
            };
            $query->where('description', 'like', "%{$searchKeyword}%");
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('transaction_date', [$request->get('start_date'), $request->get('end_date')]);
        }

        if ($request->filled('search')) {
            $search = '%' . trim($request->string('search')->toString()) . '%';
            $query->where('description', 'like', $search);
        }

        $transactions = $query->latest('transaction_date')->latest('created_at')->paginate(50)->withQueryString();

        return view('app.finance.cash-bank.ledger', [
            'business' => $business,
            'accounts' => CashAccount::where('business_id', $business->id)->orderBy('name')->get(),
            'account' => $account,
            'transactions' => $transactions,
        ]);
    }

    public function storeInflow(Request $request): RedirectResponse
    {
        return $this->storeManual($request, true);
    }

    public function storeOutflow(Request $request): RedirectResponse
    {
        return $this->storeManual($request, false);
    }

    public function transfer(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        $validated = $request->validate([
            'from_account_id' => ['required', 'exists:cash_accounts,id'],
            'to_account_id' => ['required', 'exists:cash_accounts,id', 'different:from_account_id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        $from = CashAccount::where('business_id', $business->id)->findOrFail($validated['from_account_id']);
        $to = CashAccount::where('business_id', $business->id)->findOrFail($validated['to_account_id']);

        try {
            $this->service->transfer($from, $to, (float) $validated['amount'], $validated['description'], auth()->id());
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->withErrors(['amount' => $exception->getMessage()]);
        }

        return back()->with('success', 'Transfer antar rekening sebesar Rp ' . number_format((float) $validated['amount'], 0, ',', '.') . ' berhasil dicatat.');
    }

    public function receivables(Request $request): View
    {
        $business = Context::requireBusiness();

        $baseQuery = Invoice::where('business_id', $business->id)
            ->where('balance_due', '>', 0)
            ->whereNotIn('status', [Invoice::STATUS_DRAFT, Invoice::STATUS_VOID]);

        // Executive Aggregate KPIs across all records for this business
        $totalReceivable = (float) (clone $baseQuery)->sum('balance_due');
        $notDue = (float) (clone $baseQuery)->where(function ($q) {
            $q->whereNull('due_date')->orWhereDate('due_date', '>=', now()->toDateString());
        })->sum('balance_due');
        $overdue1to30 = (float) (clone $baseQuery)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->whereDate('due_date', '>=', now()->subDays(30)->toDateString())
            ->sum('balance_due');
        $overdue30plus = (float) (clone $baseQuery)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->subDays(30)->toDateString())
            ->sum('balance_due');

        $query = Invoice::with('customer')
            ->where('business_id', $business->id)
            ->where('balance_due', '>', 0)
            ->whereNotIn('status', [Invoice::STATUS_DRAFT, Invoice::STATUS_VOID]);

        // Search filter
        if ($request->filled('search')) {
            $search = '%' . trim($request->string('search')->toString()) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', $search)
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', $search));
            });
        }

        // Aging bucket filter
        $bucket = $request->string('bucket', 'all')->toString();
        if ($bucket === 'not_due') {
            $query->where(function ($q) {
                $q->whereNull('due_date')->orWhereDate('due_date', '>=', now()->toDateString());
            });
        } elseif ($bucket === 'overdue_1_30') {
            $query->whereNotNull('due_date')
                ->whereDate('due_date', '<', now()->toDateString())
                ->whereDate('due_date', '>=', now()->subDays(30)->toDateString());
        } elseif ($bucket === 'overdue_30_plus') {
            $query->whereNotNull('due_date')
                ->whereDate('due_date', '<', now()->subDays(30)->toDateString());
        }

        $invoices = $query->orderBy('due_date')->paginate(30)->withQueryString();

        return view('app.finance.receivables', compact(
            'invoices',
            'totalReceivable',
            'notDue',
            'overdue1to30',
            'overdue30plus',
            'bucket'
        ));
    }

    public function payables(Request $request): View
    {
        $business = Context::requireBusiness();

        $baseQuery = SupplierInvoice::where('business_id', $business->id)
            ->where('balance_due', '>', 0)
            ->where('status', '!=', SupplierInvoice::STATUS_VOID);

        // Executive Aggregate KPIs across all records for this business
        $totalPayable = (float) (clone $baseQuery)->sum('balance_due');
        $notDue = (float) (clone $baseQuery)->where(function ($q) {
            $q->whereNull('due_date')->orWhereDate('due_date', '>=', now()->toDateString());
        })->sum('balance_due');
        $overdue1to30 = (float) (clone $baseQuery)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->whereDate('due_date', '>=', now()->subDays(30)->toDateString())
            ->sum('balance_due');
        $overdue30plus = (float) (clone $baseQuery)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->subDays(30)->toDateString())
            ->sum('balance_due');

        $query = SupplierInvoice::with('supplier')
            ->where('business_id', $business->id)
            ->where('balance_due', '>', 0)
            ->where('status', '!=', SupplierInvoice::STATUS_VOID);

        // Search filter
        if ($request->filled('search')) {
            $search = '%' . trim($request->string('search')->toString()) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', $search)
                    ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', $search));
            });
        }

        // Aging bucket filter
        $bucket = $request->string('bucket', 'all')->toString();
        if ($bucket === 'not_due') {
            $query->where(function ($q) {
                $q->whereNull('due_date')->orWhereDate('due_date', '>=', now()->toDateString());
            });
        } elseif ($bucket === 'overdue_1_30') {
            $query->whereNotNull('due_date')
                ->whereDate('due_date', '<', now()->toDateString())
                ->whereDate('due_date', '>=', now()->subDays(30)->toDateString());
        } elseif ($bucket === 'overdue_30_plus') {
            $query->whereNotNull('due_date')
                ->whereDate('due_date', '<', now()->subDays(30)->toDateString());
        }

        $invoices = $query->orderBy('due_date')->paginate(30)->withQueryString();

        return view('app.finance.payables', compact(
            'invoices',
            'totalPayable',
            'notDue',
            'overdue1to30',
            'overdue30plus',
            'bucket'
        ));
    }

    private function storeManual(Request $request, bool $inflow): RedirectResponse
    {
        $business = Context::requireBusiness();
        $validated = $request->validate([
            'account_id' => ['nullable', 'exists:cash_accounts,id'],
            'account_method' => ['nullable', 'in:cash,bank_transfer,qris'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        $account = null;
        if (! empty($validated['account_id'])) {
            $account = CashAccount::where('business_id', $business->id)->find($validated['account_id']);
        }

        $method = $validated['account_method'] ?? ($account?->type === CashAccount::TYPE_CASH ? 'cash' : 'bank_transfer');
        $referenceId = (string) str()->uuid();

        try {
            $inflow
                ? $this->service->recordInflow($business, (float) $validated['amount'], 'manual_cash', $referenceId, $validated['description'], $method, auth()->id(), $account)
                : $this->service->recordOutflow($business, (float) $validated['amount'], 'manual_cash', $referenceId, $validated['description'], $method, auth()->id(), $account);
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->withErrors(['amount' => $exception->getMessage()]);
        }

        return back()->with('success', 'Mutasi kas berhasil dicatat.');
    }
}
