<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Billing;

use App\Domain\Billing\EntitlementService;
use App\Http\Controllers\Controller;
use App\Models\PaymentAccount;
use App\Models\BillingPackage;
use App\Models\SubscriptionPayment;
use App\Models\SystemSetting;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SubscriptionCheckoutWebController extends Controller
{
    public function __construct(
        private readonly EntitlementService $entitlementService = new EntitlementService
    ) {}

    /**
     * Show subscription checkout page with plan summary and payment method options.
     */
    public function checkout(Request $request): View
    {
        $business = Context::requireBusiness();
        $type = $request->get('type', 'subscription');
        if (!in_array($type, ['subscription', 'ai_token', 'storage'], true)) $type = 'subscription';
        $packageMode = $request->has('type');
        $cycle = $request->get('cycle', 'monthly');
        if (!in_array($cycle, ['monthly', 'annual'], true)) {
            $cycle = 'monthly';
        }

        $monthlyPrice = $this->entitlementService->getMonthlyPrice();
        $annualPrice = $this->entitlementService->getAnnualPrice();
        $basePrice = $cycle === 'annual' ? $annualPrice : $monthlyPrice;
        $topupPrice = $type === 'ai_token' ? $this->entitlementService->getTokenTopupPrice() : $this->entitlementService->getStorageTopupPrice();
        $topupQuantity = $type === 'ai_token' ? $this->entitlementService->getTokenTopupAmount() : $this->entitlementService->getStorageTopupBytes();
        $packages = $this->entitlementService->activePackages($type);
        if ($type === 'subscription'
            && (SystemSetting::get('subscription_price_monthly') !== null
                || SystemSetting::get('subscription_price_annual') !== null)) {
            $packages = $packages->filter(static fn (BillingPackage $package): bool => false)->values();
        }
        $packagesData = $packages->map(fn (BillingPackage $package): array => [
            'id' => $package->id,
            'name' => $package->name,
            'price' => $package->price,
            'duration_days' => $package->duration_days,
            'token_quantity' => $package->token_quantity,
            'storage_bytes' => $package->storage_bytes,
        ])->values()->all();

        // Auto-seed default accounts if table is empty
        if (PaymentAccount::count() === 0) {
            foreach (PaymentAccount::getDefaultAccounts() as $account) {
                PaymentAccount::create($account);
            }
        }

        $paymentAccounts = PaymentAccount::active()->ordered()->get();
        $annualDiscountBadge = SystemSetting::get('subscription_annual_discount_badge', 'Hemat 2 Bulan');
        $currentUsage = $this->entitlementService->getUsageSummary($business);

        return view('app.billing.checkout', compact(
            'business',
            'type',
            'cycle',
            'basePrice',
            'monthlyPrice',
            'annualPrice',
            'annualDiscountBadge',
            'paymentAccounts',
            'currentUsage'
            , 'topupPrice', 'topupQuantity', 'packages', 'packagesData', 'packageMode'
        ));
    }

    /**
     * Create a new subscription payment order.
     */
    public function store(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        // Get allowed bank codes
        $validCodes = PaymentAccount::active()->pluck('bank_code')->toArray();
        if (empty($validCodes)) {
            $validCodes = array_keys(SubscriptionPayment::PAYMENT_METHODS);
        }

        $validated = $request->validate([
            'order_type' => ['nullable', 'string', 'in:subscription,ai_token,storage'],
            'package_id' => ['nullable', 'uuid', 'exists:billing_packages,id'],
            'cycle' => ['nullable', 'string', 'in:monthly,annual'],
            'payment_method' => ['required', 'string', 'in:' . implode(',', $validCodes)],
        ]);

        $orderType = $validated['order_type'] ?? 'subscription';
        $package = !empty($validated['package_id'])
            ? BillingPackage::active()->where('id', $validated['package_id'])->where('type', $orderType)->firstOrFail()
            : null;

        // If subscription order without explicit package_id, resolve from BillingPackage catalog
        if (!$package && $orderType === 'subscription'
            && SystemSetting::get('subscription_price_monthly') === null
            && SystemSetting::get('subscription_price_annual') === null) {
            $cycle = $validated['cycle'] ?? 'monthly';
            $package = $cycle === 'annual'
                ? BillingPackage::active()->where('type', BillingPackage::TYPE_SUBSCRIPTION)->where(fn ($q) => $q->where('code', 'core-annual')->orWhere('duration_days', '>=', 360))->orderBy('sort_order')->first()
                : BillingPackage::active()->where('type', BillingPackage::TYPE_SUBSCRIPTION)->where(fn ($q) => $q->where('code', 'core-monthly')->orWhere('duration_days', '<=', 31))->orderBy('sort_order')->first();
        }

        $payment = $package
            ? $this->entitlementService->createPackageOrder($business, $user, $package, $validated['payment_method'])
            : ($orderType === 'ai_token'
            ? $this->entitlementService->createTokenTopupOrder($business, $user, $validated['payment_method'])
            : ($orderType === 'storage'
                ? $this->entitlementService->createStorageTopupOrder($business, $user, $validated['payment_method'])
                : $this->entitlementService->createPaymentOrder($business, $user, $validated['cycle'] ?? 'monthly', $validated['payment_method'])));

        return redirect()->route('billing.payment.show', $payment)
            ->with('success', "Pesanan #{$payment->order_number} berhasil dibuat. Silakan selesaikan pembayaran sesuai nominal unik.");
    }

    /**
     * Show payment instructions and proof upload form.
     */
    public function payment(SubscriptionPayment $payment): View
    {
        $business = Context::requireBusiness();
        abort_unless($payment->business_id === $business->id, 403);

        $methodDetails = $payment->getPaymentMethodDetails();

        return view('app.billing.payment', compact('business', 'payment', 'methodDetails'));
    }

    /**
     * Upload transfer receipt proof for verification.
     */
    public function uploadProof(Request $request, SubscriptionPayment $payment): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($payment->business_id === $business->id, 403);

        $validated = $request->validate([
            'payment_proof' => ['required', 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:5120'],
            'sender_bank' => ['nullable', 'string', 'max:64'],
            'sender_account_name' => ['required', 'string', 'max:128'],
            'sender_account_number' => ['nullable', 'string', 'max:64'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->entitlementService->submitPaymentProof(
            payment: $payment,
            file: $request->file('payment_proof'),
            senderData: [
                'sender_bank' => $validated['sender_bank'] ?? null,
                'sender_account_name' => $validated['sender_account_name'],
                'sender_account_number' => $validated['sender_account_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return back()->with('success', 'Bukti transfer berhasil diunggah! Tim Billing Cooca akan memverifikasi dalam 15-30 menit.');
    }

    /**
     * Show payment orders history for the active business.
     */
    public function history(Request $request): View
    {
        $business = Context::requireBusiness();

        $payments = SubscriptionPayment::where('business_id', $business->id)
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('app.billing.history', compact('business', 'payments'));
    }
}
