<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Crm;

use App\Domain\Crm\LoyaltyService;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerPointHistory;
use App\Models\Voucher;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CrmWebController extends Controller
{
    public function __construct(
        private readonly LoyaltyService $loyaltyService = new LoyaltyService
    ) {}

    /**
     * Display members list with loyalty tiers, points, and credit.
     */
    public function members(Request $request): View
    {
        $business = Context::requireBusiness();

        $query = Customer::where('business_id', $business->id)
            ->withCount('posOrders')
            ->latest('total_spent');

        if ($request->filled('tier')) {
            $query->where('membership_tier', $request->get('tier'));
        }

        if ($request->filled('segment')) {
            $query->where('segment', $request->get('segment'));
        }

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $query->paginate(20)->withQueryString();

        $totalMembers = Customer::where('business_id', $business->id)->count();
        $totalPointsIssued = Customer::where('business_id', $business->id)->sum('points_balance');
        $totalCreditReceivable = Customer::where('business_id', $business->id)->sum('current_credit_balance');

        return view('app.crm.members', compact(
            'business',
            'customers',
            'totalMembers',
            'totalPointsIssued',
            'totalCreditReceivable'
        ));
    }

    /**
     * View customer point history.
     */
    public function pointHistories(Customer $customer): JsonResponse
    {
        $histories = $customer->pointHistories()->latest()->limit(50)->get();
        return response()->json(['success' => true, 'histories' => $histories]);
    }

    /**
     * Record customer credit repayment (piutang).
     */
    public function recordCreditPayment(Request $request, Customer $customer): RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $this->loyaltyService->recordCustomerCreditPayment(
            customer: $customer,
            amount: (float) $validated['amount'],
            notes: $validated['notes'] ?? 'Pelunasan piutang pelanggan',
            user: $user
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Pembayaran piutang berhasil dicatat.']);
        }

        return redirect()->back()->with('success', 'Pembayaran piutang pelanggan berhasil dicatat!');
    }

    /**
     * Vouchers list & creation.
     */
    public function vouchers(Request $request): View
    {
        $business = Context::requireBusiness();

        $vouchers = Voucher::where('business_id', $business->id)
            ->latest('created_at')
            ->paginate(15);

        return view('app.crm.vouchers', compact('business', 'vouchers'));
    }

    /**
     * Store new voucher.
     */
    public function storeVoucher(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:100'],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'tier_eligibility' => ['nullable', 'string'],
        ]);

        Voucher::create([
            'business_id' => $business->id,
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'discount_type' => $validated['discount_type'],
            'discount_value' => (float) $validated['discount_value'],
            'min_order_amount' => (float) ($validated['min_order_amount'] ?? 0),
            'max_discount_amount' => ! empty($validated['max_discount_amount']) ? (float) $validated['max_discount_amount'] : null,
            'valid_from' => $validated['valid_from'] ?? null,
            'valid_until' => $validated['valid_until'] ?? null,
            'usage_limit' => ! empty($validated['usage_limit']) ? (int) $validated['usage_limit'] : null,
            'tier_eligibility' => $validated['tier_eligibility'] ?? 'all',
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', "Voucher {$validated['code']} berhasil ditambahkan.");
    }

    /**
     * Toggle voucher status.
     */
    public function toggleVoucher(Voucher $voucher): RedirectResponse
    {
        $voucher->update(['is_active' => ! $voucher->is_active]);
        $status = $voucher->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Voucher {$voucher->code} berhasil {$status}.");
    }
}
