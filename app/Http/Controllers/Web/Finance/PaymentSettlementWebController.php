<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Finance;

use App\Domain\Finance\PaymentSettlementService;
use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\MerchantPayoutBankAccount;
use App\Models\PaymentSettlement;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

final class PaymentSettlementWebController extends Controller
{
    public function __construct(
        private readonly PaymentSettlementService $settlementService = new PaymentSettlementService
    ) {}

    /**
     * Display Cooca Pay Payout Hub dashboard.
     */
    public function index(Request $request): View|JsonResponse
    {
        $business = Context::requireBusiness();

        $settlements = PaymentSettlement::where('business_id', $business->id)
            ->with(['allocations', 'reconciledBy', 'payoutBankAccount'])
            ->latest('settlement_date')
            ->paginate(15);

        $unsettledData = $this->settlementService->getUnsettledPayments($business);

        $bankAccounts = CashAccount::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Verified payout bank accounts for Payout Hub
        $payoutBankAccounts = MerchantPayoutBankAccount::where('business_id', $business->id)
            ->where('is_verified', true)
            ->orderByDesc('is_primary')
            ->orderBy('bank_name')
            ->get();

        // Owner name for identity matching display
        $ownerName = $business->users()->wherePivot('role', 'owner')->first()?->name ?? $business->name;

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'settlements' => $settlements,
                'unsettled' => $unsettledData,
            ]);
        }

        return view('app.finance.settlements.index', compact(
            'business',
            'settlements',
            'unsettledData',
            'bankAccounts',
            'payoutBankAccounts',
            'ownerName'
        ));
    }

    /**
     * Get unsettled gateway transactions via AJAX.
     */
    public function getUnsettled(): JsonResponse
    {
        $business = Context::requireBusiness();
        $unsettled = $this->settlementService->getUnsettledPayments($business);

        return response()->json([
            'success' => true,
            'data' => $unsettled,
        ]);
    }

    /**
     * Process Cooca Pay payout request — prefers verified MerchantPayoutBankAccount, falls back to legacy CashAccount.
     */
    public function reconcile(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $validated = $request->validate([
            'payout_bank_account_id' => ['nullable', 'string', 'exists:merchant_payout_bank_accounts,id'],
            'payout_mode'           => ['nullable', 'string', 'in:manual,auto_h1'],
            'settlement_number'     => ['nullable', 'string', 'max:50'],
            'settlement_date'       => ['nullable', 'date'],
            'destination_bank'      => ['nullable', 'string', 'max:100'],
            'notes'                 => ['nullable', 'string', 'max:500'],
            'allocations'           => ['required', 'array', 'min:1'],
            'allocations.*.payment_type' => ['required', 'string', 'in:pos_order_payment,commerce_order,invoice_payment'],
            'allocations.*.payment_id'   => ['required', 'string'],
            'allocations.*.amount'       => ['required', 'numeric', 'min:0.01'],
            'fee_amount'            => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $allocations = $validated['allocations'];
            $payoutMode = $validated['payout_mode'] ?? PaymentSettlement::PAYOUT_MODE_MANUAL;

            // Prefer verified payout bank account (Payout Hub flow)
            if (! empty($validated['payout_bank_account_id'])) {
                $bankAccount = MerchantPayoutBankAccount::where('business_id', $business->id)
                    ->where('id', $validated['payout_bank_account_id'])
                    ->firstOrFail();

                $settlement = $this->settlementService->requestPayoutWithVerifiedBank(
                    business: $business,
                    bankAccount: $bankAccount,
                    allocations: $allocations,
                    userId: $user?->id,
                    payoutMode: $payoutMode,
                    notes: $validated['notes'] ?? null
                );
            } else {
                // Legacy flow (fallback): free-text destination bank
                $grossAmount = 0.0;
                foreach ($allocations as $item) {
                    $grossAmount += round((float) $item['amount'], 2);
                }
                $feeAmount = round((float) ($validated['fee_amount'] ?? 0), 2);
                $netAmount = max(0.0, $grossAmount - $feeAmount);

                $settlementNumber = $validated['settlement_number']
                    ?: ('SETTLE-' . Carbon::today()->format('Ymd') . '-' . strtoupper(Str::random(5)));

                $data = [
                    'settlement_number' => $settlementNumber,
                    'settlement_date'   => $validated['settlement_date'] ?? Carbon::today()->toDateString(),
                    'payment_channel'   => 'tripay',
                    'gross_amount'      => $grossAmount,
                    'fee_amount'        => $feeAmount,
                    'net_amount'        => $netAmount,
                    'destination_bank'  => $validated['destination_bank'] ?? null,
                    'notes'             => $validated['notes'] ?? 'Pencairan saldo gateway ke rekening bank',
                ];

                $settlement = $this->settlementService->reconcile(
                    business: $business,
                    data: $data,
                    allocations: $allocations,
                    userId: $user?->id,
                    immediateComplete: false
                );
            }

            $message = "Pengajuan pencairan saldo #{$settlement->settlement_number} sebesar Rp "
                . number_format($settlement->net_amount, 0, ',', '.')
                . " berhasil dikirim ke Admin COOCA. Bukti transfer akan tersedia setelah dana ditransfer.";

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'settlement' => $settlement,
                ]);
            }

            return redirect()->route('finance.settlements.index')->with('success', $message);
        } catch (Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Show settlement allocation details.
     */
    public function show(PaymentSettlement $settlement): View|JsonResponse
    {
        $business = Context::requireBusiness();
        if ($settlement->business_id !== $business->id) {
            abort(403);
        }

        $settlement->load(['allocations', 'reconciledBy', 'admin']);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'settlement' => $settlement,
            ]);
        }

        return view('app.finance.settlements.show', compact('business', 'settlement'));
    }
}
