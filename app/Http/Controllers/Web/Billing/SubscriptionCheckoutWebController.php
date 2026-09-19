<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Billing;

use App\Domain\Billing\EntitlementService;
use App\Domain\Payment\TripayService;
use App\Http\Controllers\Controller;
use App\Models\BusinessSubscription;
use App\Models\PaymentAccount;
use App\Models\BillingPackage;
use App\Models\SubscriptionPayment;
use App\Models\SystemSetting;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

final class SubscriptionCheckoutWebController extends Controller
{
    public function __construct(
        private readonly EntitlementService $entitlementService = new EntitlementService(),
        private readonly TripayService $tripayService = new TripayService()
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
            && !$request->is('patungan')
            && !$request->has('package_id')
            && !$request->has('type')
            && (SystemSetting::get('subscription_price_monthly') !== null
                || SystemSetting::get('subscription_price_annual') !== null)) {
            // Keep free / promo trial packages (price <= 0) visible even when default system settings are configured
            $packages = $packages->filter(static fn (BillingPackage $package): bool => (float) $package->price <= 0.0)->values();
        }
        $packagesData = $packages->map(fn (BillingPackage $package): array => [
            'id' => $package->id,
            'name' => $package->name,
            'price' => $package->price,
            'duration_days' => $package->duration_days,
            'token_quantity' => $package->token_quantity,
            'storage_bytes' => $package->storage_bytes,
        ])->values()->all();

        // Ensure default TriPay payment accounts exist
        foreach (PaymentAccount::getDefaultAccounts() as $account) {
            PaymentAccount::firstOrCreate(['bank_code' => $account['bank_code']], $account);
        }

        $paymentAccounts = PaymentAccount::active()->ordered()->get();
        $annualDiscountBadge = SystemSetting::get('subscription_annual_discount_badge', 'Hemat 2 Bulan');
        $currentUsage = $this->entitlementService->getUsageSummary($business);

        $selectedTier = $request->get('tier', BusinessSubscription::TIER_STANDARD);
        if (!in_array($selectedTier, [BusinessSubscription::TIER_STANDARD, BusinessSubscription::TIER_PREMIUM, BusinessSubscription::TIER_PRESTIGE], true)) {
            $selectedTier = BusinessSubscription::TIER_STANDARD;
        }

        return view('app.billing.checkout', compact(
            'business',
            'type',
            'cycle',
            'selectedTier',
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
     * Create a new subscription payment order or activate free promo package immediately.
     */
    public function store(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $orderType = $request->input('order_type', 'subscription');
        $packageId = $request->input('package_id');
        $package = !empty($packageId)
            ? BillingPackage::active()->where('id', $packageId)->where('type', $orderType)->first()
            : null;

        $isFreePackage = $package && (float) $package->price <= 0.0;

        // Get allowed bank codes
        $validCodes = PaymentAccount::active()->pluck('bank_code')->toArray();
        $validCodes = array_unique(array_merge($validCodes, array_keys(SubscriptionPayment::PAYMENT_METHODS)));
        $validCodes[] = SubscriptionPayment::METHOD_FREE_PROMO;

        $validated = $request->validate([
            'order_type' => ['nullable', 'string', 'in:subscription,ai_token,storage'],
            'package_id' => ['nullable', 'uuid', 'exists:billing_packages,id'],
            'cycle' => ['nullable', 'string', 'in:monthly,annual'],
            'tier' => ['nullable', 'string', 'in:standard,premium,prestige'],
            'payment_method' => [$isFreePackage ? 'nullable' : 'required', 'string', 'in:' . implode(',', $validCodes)],
        ]);

        // If free package (price = 0): Instant activation, no payment & no confirmation needed!
        if ($isFreePackage) {
            $payment = $this->entitlementService->activateFreePackage($business, $user, $package);

            return redirect()->route('dashboard')
                ->with('success', "Selamat! Paket promo '{$package->name}' ({$payment->package_duration_days} Hari Trial Pro) berhasil diaktifkan secara instan tanpa perlu transfer pembayaran.");
        }

        $paymentMethod = $validated['payment_method'] ?? SubscriptionPayment::METHOD_QRIS;

        $payment = ($package && $orderType !== 'subscription')
            ? $this->entitlementService->createPackageOrder($business, $user, $package, $paymentMethod)
            : ($orderType === 'ai_token'
            ? $this->entitlementService->createTokenTopupOrder($business, $user, $paymentMethod)
            : ($orderType === 'storage'
                ? $this->entitlementService->createStorageTopupOrder($business, $user, $paymentMethod)
                : $this->entitlementService->createPaymentOrder(
                    $business,
                    $user,
                    $validated['cycle'] ?? 'monthly',
                    $paymentMethod,
                    $validated['tier'] ?? $request->input('tier', BusinessSubscription::TIER_STANDARD)
                )));

        // Automatically trigger TriPay transaction for all non-free orders
        if ($payment->payment_method !== SubscriptionPayment::METHOD_FREE_PROMO) {
            $channelCode = $payment->getTripayChannelCode();
            try {
                $tripayRes = $this->tripayService->createSubscriptionTransaction($payment, $channelCode);
                if ($tripayRes['success'] ?? false) {
                    $payment->update([
                        'payment_gateway'   => SubscriptionPayment::GATEWAY_TRIPAY,
                        'gateway_reference' => $tripayRes['reference'] ?? null,
                        'gateway_pay_code'  => $tripayRes['pay_code'] ?? null,
                        'gateway_pay_url'   => $tripayRes['checkout_url'] ?? null,
                        'gateway_qr_url'    => $tripayRes['qr_url'] ?? null,
                        'gateway_qr_string' => $tripayRes['qr_string'] ?? null,
                        'gateway_fee'       => (float) ($tripayRes['fee'] ?? 0.0),
                        'gateway_expired_at'=> isset($tripayRes['expired_time']) ? Carbon::createFromTimestamp($tripayRes['expired_time']) : null,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('[SubscriptionCheckout] TriPay subscription auto-init failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('billing.payment.show', $payment)
            ->with('success', "Pesanan #{$payment->order_number} berhasil dibuat. Silakan selesaikan pembayaran via TriPay Payment Gateway.");
    }

    /**
     * Show payment instructions and proof upload form.
     */
    public function payment(SubscriptionPayment $payment): View
    {
        $business = Context::requireBusiness();
        abort_unless($payment->business_id === $business->id, 403);

        // If payment gateway not initialized and order is pending, initialize TriPay
        if (empty($payment->gateway_reference) && $payment->status === SubscriptionPayment::STATUS_PENDING && $payment->payment_method !== SubscriptionPayment::METHOD_FREE_PROMO) {
            try {
                $channelCode = $payment->getTripayChannelCode();
                $tripayRes = $this->tripayService->createSubscriptionTransaction($payment, $channelCode);
                if ($tripayRes['success'] ?? false) {
                    $payment->update([
                        'payment_gateway'   => SubscriptionPayment::GATEWAY_TRIPAY,
                        'gateway_reference' => $tripayRes['reference'] ?? null,
                        'gateway_pay_code'  => $tripayRes['pay_code'] ?? null,
                        'gateway_pay_url'   => $tripayRes['checkout_url'] ?? null,
                        'gateway_qr_url'    => $tripayRes['qr_url'] ?? null,
                        'gateway_qr_string' => $tripayRes['qr_string'] ?? null,
                        'gateway_fee'       => (float) ($tripayRes['fee'] ?? 0.0),
                        'gateway_expired_at'=> isset($tripayRes['expired_time']) ? Carbon::createFromTimestamp($tripayRes['expired_time']) : null,
                    ]);
                    $payment->refresh();
                }
            } catch (\Throwable $e) {
                Log::warning('[SubscriptionCheckout] TriPay subscription auto-init failed: ' . $e->getMessage());
            }
        }

        $methodDetails = $payment->getPaymentMethodDetails();

        return view('app.billing.payment', compact('business', 'payment', 'methodDetails'));
    }

    public function checkStatus(SubscriptionPayment $payment): JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless($payment->business_id === $business->id, 403);

        return response()->json([
            'success' => true,
            'status'  => $payment->status,
            'is_paid' => $payment->isPaid(),
        ]);
    }

    /**
     * View and download official subscription payment invoice / receipt.
     */
    public function invoice(SubscriptionPayment $payment): View
    {
        $business = Context::requireBusiness();
        abort_unless($payment->business_id === $business->id, 403);

        $methodDetails = $payment->getPaymentMethodDetails();

        return view('app.billing.invoice', compact('business', 'payment', 'methodDetails'));
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

    /**
     * View transfer receipt proof securely (Superadmin or tenant owner).
     */
    public function viewProof(SubscriptionPayment $payment): \Symfony\Component\HttpFoundation\Response
    {
        $isAdmin = auth('admin')->check();
        $user = auth()->user();
        $isPayer = $user && $payment->user_id === $user->id;

        $business = Context::business();
        $isTenantMember = false;
        if ($business && $user && $payment->business_id === $business->id) {
            $isTenantMember = $business->users()->where('users.id', $user->id)->exists();
        }

        abort_unless($isAdmin || $isPayer || $isTenantMember, 403);

        $path = $payment->payment_proof_path;
        abort_unless($path, 404);

        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($path)) {
            return \Illuminate\Support\Facades\Storage::disk('local')->response($path);
        }

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->response($path);
        }

        if (file_exists(public_path($path))) {
            return response()->file(public_path($path));
        }

        abort(404);
    }
}
