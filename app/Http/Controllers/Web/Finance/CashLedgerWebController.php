<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Finance;

use App\Domain\Finance\CashLedgerService;
use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\Invoice;
use App\Models\SupplierInvoice;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

final class CashLedgerWebController extends Controller
{
    public function __construct(private readonly CashLedgerService $service = new CashLedgerService) {}

    public function index(): View
    {
        $business = Context::requireBusiness();
        return view('app.finance.cash-bank.index', [
            'business' => $business,
            'accounts' => CashAccount::where('business_id', $business->id)->where('is_active', true)->orderBy('name')->get(),
            'transactions' => CashTransaction::with('cashAccount')->where('business_id', $business->id)->latest('transaction_date')->latest('created_at')->limit(30)->get(),
        ]);
    }

    public function ledger(Request $request): View
    {
        $business = Context::requireBusiness();
        $account = $request->filled('account_id')
            ? CashAccount::where('business_id', $business->id)->findOrFail($request->string('account_id')->toString())
            : null;
        $transactions = CashTransaction::where('business_id', $business->id)
            ->when($account, fn ($query) => $query->where('cash_account_id', $account->id))
            ->latest('transaction_date')->latest('created_at')->paginate(50)->withQueryString();
        return view('app.finance.cash-bank.ledger', ['business' => $business, 'accounts' => CashAccount::where('business_id', $business->id)->orderBy('name')->get(), 'account' => $account, 'transactions' => $transactions]);
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
        $validated = $request->validate(['from_account_id' => ['required', 'exists:cash_accounts,id'], 'to_account_id' => ['required', 'exists:cash_accounts,id'], 'amount' => ['required', 'numeric', 'gt:0'], 'description' => ['required', 'string', 'max:255']]);
        $from = CashAccount::where('business_id', $business->id)->findOrFail($validated['from_account_id']);
        $to = CashAccount::where('business_id', $business->id)->findOrFail($validated['to_account_id']);
        try { $this->service->transfer($from, $to, (float) $validated['amount'], $validated['description'], auth()->id()); }
        catch (InvalidArgumentException $exception) { return back()->withErrors(['amount' => $exception->getMessage()]); }
        return back()->with('success', 'Transfer antar akun berhasil dicatat.');
    }

    public function receivables(): View
    {
        $business = Context::requireBusiness();
        return view('app.finance.receivables', ['invoices' => Invoice::with('customer')->where('business_id', $business->id)->where('balance_due', '>', 0)->whereNotIn('status', [Invoice::STATUS_DRAFT, Invoice::STATUS_VOID])->orderBy('due_date')->paginate(30)]);
    }

    public function payables(): View
    {
        $business = Context::requireBusiness();
        return view('app.finance.payables', ['invoices' => SupplierInvoice::with('supplier')->where('business_id', $business->id)->where('balance_due', '>', 0)->orderBy('due_date')->paginate(30)]);
    }

    private function storeManual(Request $request, bool $inflow): RedirectResponse
    {
        $business = Context::requireBusiness();
        $validated = $request->validate(['account_method' => ['required', 'in:cash,bank_transfer,qris'], 'amount' => ['required', 'numeric', 'gt:0'], 'description' => ['required', 'string', 'max:255']]);
        $referenceId = (string) str()->uuid();
        try {
            $inflow
                ? $this->service->recordInflow($business, (float) $validated['amount'], 'manual_cash', $referenceId, $validated['description'], $validated['account_method'], auth()->id())
                : $this->service->recordOutflow($business, (float) $validated['amount'], 'manual_cash', $referenceId, $validated['description'], $validated['account_method'], auth()->id());
        } catch (InvalidArgumentException $exception) { return back()->withErrors(['amount' => $exception->getMessage()]); }
        return back()->with('success', 'Mutasi kas berhasil dicatat.');
    }
}
