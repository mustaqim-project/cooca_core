<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Finance;

use App\Domain\Finance\CashLedgerService;
use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

final class CashLedgerController extends Controller
{
    public function __construct(private readonly CashLedgerService $service = new CashLedgerService) {}

    public function accounts(): JsonResponse
    {
        $business = Context::requireBusiness();
        $accounts = CashAccount::where('business_id', $business->id)->where('is_active', true)->orderBy('name')->get();
        return response()->json(['accounts' => $accounts]);
    }

    public function transactions(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $query = CashTransaction::with('cashAccount')->where('business_id', $business->id)->latest('transaction_date')->latest('created_at');
        if ($request->filled('account_id')) $query->where('cash_account_id', $request->string('account_id')->toString());
        if ($request->filled('type')) $query->where('type', $request->string('type')->toString());
        if ($request->filled('reference_type')) $query->where('reference_type', $request->string('reference_type')->toString());
        if ($request->filled('date_from')) $query->whereDate('transaction_date', '>=', $request->date('date_from'));
        if ($request->filled('date_to')) $query->whereDate('transaction_date', '<=', $request->date('date_to'));
        $transactions = $query->paginate(min(100, max(1, $request->integer('per_page', 20))));
        return response()->json(['transactions' => $transactions->items(), 'pagination' => ['current_page' => $transactions->currentPage(), 'last_page' => $transactions->lastPage(), 'per_page' => $transactions->perPage(), 'total' => $transactions->total()]]);
    }

    public function inflow(Request $request): JsonResponse { return $this->record($request, true); }
    public function outflow(Request $request): JsonResponse { return $this->record($request, false); }

    public function transfer(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $validated = $request->validate(['from_account_id' => ['required', 'exists:cash_accounts,id'], 'to_account_id' => ['required', 'exists:cash_accounts,id'], 'amount' => ['required', 'numeric', 'gt:0'], 'description' => ['required', 'string', 'max:255']]);
        $from = CashAccount::where('business_id', $business->id)->findOrFail($validated['from_account_id']);
        $to = CashAccount::where('business_id', $business->id)->findOrFail($validated['to_account_id']);
        try { $entries = $this->service->transfer($from, $to, (float) $validated['amount'], $validated['description'], $request->user()?->id); }
        catch (InvalidArgumentException $exception) { return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY); }
        return response()->json(['message' => 'Transfer kas berhasil dicatat.', 'transactions' => $entries], Response::HTTP_CREATED);
    }

    private function record(Request $request, bool $inflow): JsonResponse
    {
        $business = Context::requireBusiness();
        $validated = $request->validate(['account_method' => ['required', 'in:cash,bank_transfer,qris'], 'amount' => ['required', 'numeric', 'gt:0'], 'description' => ['required', 'string', 'max:255'], 'reference_id' => ['nullable', 'string', 'max:64']]);
        $referenceId = $validated['reference_id'] ?? (string) Str::uuid();
        try {
            $transaction = $inflow
                ? $this->service->recordInflow($business, (float) $validated['amount'], 'manual_cash', $referenceId, $validated['description'], $validated['account_method'], $request->user()?->id)
                : $this->service->recordOutflow($business, (float) $validated['amount'], 'manual_cash', $referenceId, $validated['description'], $validated['account_method'], $request->user()?->id);
        } catch (InvalidArgumentException $exception) { return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY); }
        return response()->json(['message' => $inflow ? 'Pemasukan kas berhasil dicatat.' : 'Pengeluaran kas berhasil dicatat.', 'transaction' => $transaction], Response::HTTP_CREATED);
    }
}
