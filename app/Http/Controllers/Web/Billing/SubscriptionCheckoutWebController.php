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
use App\Models\SubscriptionPromo;
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
    public function checkout(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $business = Context::requireBusiness();
        $type = $request->get('type', 'subscription');
        if ($type === 'ai_token') {
            return redirect()->route('cooca-ai.providers')
                ->with('info', 'Cooca AI ditenagai langsung oleh API Key resmi milik Anda sendiri (BYOAI) tanpa sistem kuota atau pembelian token platform.');
        }
        if (!in_array($type, ['subscription', 'storage'], true)) $type = 'subscription';
        $packageMode = $request->has('type');
        $cycle = $request->get('cycle', 'monthly');
        if (!in_array($cycle, ['monthly', 'annual'], true)) {
            $cycle = 'monthly';
        }

        $allTierPrices = $this->entitlementService->getAllTierPrices();
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

        // 100% Exclusive TriPay Automatic Payment Gateway Channels - Dynamically Filter Active Channels Only
        $activeTripayChannels = $this->tripayService->getActiveChannels();

        // De-duplicate multiple QRIS channels (e.g. TriPay returns both QRIS and QRIS2) into one single premier option
        $seenQris = false;
        $filteredTripayChannels = [];
        foreach ($activeTripayChannels as $ch) {
            $code = strtolower((string) ($ch['code'] ?? ''));
            $isQris = str_contains($code, 'qris') || ($ch['type'] ?? '') === 'qris';
            if ($isQris) {
                if ($seenQris) {
                    continue; // Skip secondary duplicate QRIS channel
                }
                $seenQris = true;
                $ch['code'] = 'qris';
            }
            $filteredTripayChannels[] = $ch;
        }

        $paymentAccounts = collect($filteredTripayChannels)->map(function ($method) {
            $code = strtolower((string) ($method['code'] ?? 'qris'));
            $isQris = str_contains($code, 'qris') || ($method['type'] ?? '') === 'qris';

            return (object) [
                'bank_code' => $code,
                'bank_name' => $isQris ? 'QRIS Dinamis (GoPay, OVO, ShopeePay, BCA, Livin Mandiri, BRImo)' : ($method['name'] ?? strtoupper($code)),
                'type' => $isQris ? PaymentAccount::TYPE_QRIS : ($method['type'] ?? 'virtual_account'),
                'account_number' => $isQris ? 'Scan QR Code Cooca Pay' : ($method['code'] ?? 'Nomor VA Otomatis'),
                'account_name' => 'Cooca ID',
                'icon' => $isQris ? 'qr-code' : ($method['icon'] ?? 'credit-card'),
                'icon_url' => $method['icon_url'] ?? null,
                'color' => $isQris ? 'emerald' : ($method['color'] ?? 'blue'),
                'instructions' => $method['description'] ?? ($method['instructions'] ?? 'Buka aplikasi m-Banking atau e-Wallet apa pun, scan kode QRIS dinamis di layar.'),
            ];
        });

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
            'allTierPrices',
            'annualDiscountBadge',
            'paymentAccounts',
            'currentUsage'
            , 'topupPrice', 'topupQuantity', 'packages', 'packagesData', 'packageMode'
        ));
    }

    /**
     * Validate a promo voucher code via AJAX during checkout.
     */
    public function validatePromo(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $code = strtoupper(trim((string) $request->input('code', '')));
        $tier = (string) $request->input('tier', BusinessSubscription::TIER_STANDARD);
        $cycle = (string) $request->input('cycle', 'monthly');
        $orderType = (string) $request->input('order_type', 'subscription');
        $packageId = $request->input('package_id');

        if (empty($code)) {
            return response()->json([
                'valid' => false,
                'message' => 'Silakan masukkan kode promo / voucher.',
            ], 422);
        }

        $promo = SubscriptionPromo::where('code', $code)->first();
        if (! $promo) {
            return response()->json([
                'valid' => false,
                'message' => "Kode promo '{$code}' tidak ditemukan atau salah ketik.",
            ], 404);
        }

        // Hitung estimasi harga awal
        $originalAmount = 0.0;
        if ($orderType === 'subscription') {
            $originalAmount = $this->entitlementService->getTierPrice($tier, $cycle);
        } elseif (! empty($packageId)) {
            $package = BillingPackage::active()->find($packageId);
            $originalAmount = $package ? (float) $package->price : 0.0;
        } elseif ($orderType === 'storage') {
            $originalAmount = $this->entitlementService->getStorageTopupPrice();
        }

        $validation = $promo->validateFor($originalAmount, $tier, $cycle, $business->id);
        if (! $validation['valid']) {
            return response()->json([
                'valid' => false,
                'message' => $validation['reason'],
            ], 422);
        }

        $discountAmount = $promo->calculateDiscount($originalAmount);
        $finalAmount = max(0.0, $originalAmount - $discountAmount);

        return response()->json([
            'valid' => true,
            'promo' => [
                'id' => $promo->id,
                'code' => $promo->code,
                'name' => $promo->name,
                'discount_type' => $promo->discount_type,
                'discount_value' => (float) $promo->discount_value,
                'formatted_discount' => $promo->formatted_discount,
            ],
            'original_amount' => $originalAmount,
            'discount_amount' => $discountAmount,
            'total_payable' => $finalAmount,
            'formatted_original' => 'Rp ' . number_format($originalAmount, 0, ',', '.'),
            'formatted_discount' => '- Rp ' . number_format($discountAmount, 0, ',', '.'),
            'formatted_total' => 'Rp ' . number_format($finalAmount, 0, ',', '.'),
            'message' => "Kode promo '{$promo->code}' berhasil diterapkan! Anda hemat Rp " . number_format($discountAmount, 0, ',', '.') . '.',
        ]);
    }

    /**
     * Create a new subscription payment order or activate free promo package immediately.
     */
    public function store(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless(Context::isOwner() || Context::hasPermission('billing.manage'), 403, __('billing.only_owner_order_subscription'));
        $user = auth()->user();

        $orderType = $request->input('order_type', 'subscription');
        $packageId = $request->input('package_id');
        $package = !empty($packageId)
            ? BillingPackage::active()->where('id', $packageId)->where('type', $orderType)->first()
            : null;

        $isFreePackage = $package && (float) $package->price <= 0.0;

        // Allowed payment codes: active TriPay Channels + defined methods + Free Promo
        $activeTripayCodes = collect($this->tripayService->getActiveChannels())->pluck('code')->map(fn($c) => strtolower((string) $c))->all();
        $validCodes = array_values(array_unique(array_merge(
            ['qris', SubscriptionPayment::METHOD_FREE_PROMO],
            array_keys(SubscriptionPayment::PAYMENT_METHODS),
            array_keys(SubscriptionPayment::TRIPAY_CHANNELS),
            $activeTripayCodes
        )));

        $validated = $request->validate([
            'order_type' => ['nullable', 'string', 'in:subscription,ai_token,storage'],
            'package_id' => ['nullable', 'uuid', 'exists:billing_packages,id'],
            'cycle' => ['nullable', 'string', 'in:monthly,annual'],
            'tier' => ['nullable', 'string', 'in:standard,premium,prestige'],
            'payment_method' => [$isFreePackage ? 'nullable' : 'required', 'string', 'in:' . implode(',', $validCodes)],
            'promo_code' => ['nullable', 'string', 'max:32'],
        ]);

        // If free package (price = 0): Instant activation, no payment & no confirmation needed!
        if ($isFreePackage) {
            $payment = $this->entitlementService->activateFreePackage($business, $user, $package);

            return redirect()->route('dashboard')
                ->with('success', __('billing.free_package_activated_success', [
                    'name' => $package->name,
                    'days' => $payment->package_duration_days,
                ]));
        }

        // Process Promo Voucher if supplied
        $promo = null;
        if ($request->filled('promo_code')) {
            $promoCode = strtoupper(trim((string) $request->input('promo_code')));
            $foundPromo = SubscriptionPromo::where('code', $promoCode)->first();
            if ($foundPromo) {
                $targetTier = $validated['tier'] ?? $request->input('tier', BusinessSubscription::TIER_STANDARD);
                $targetCycle = $validated['cycle'] ?? 'monthly';
                $rawAmount = ($package && $orderType !== 'subscription')
                    ? (float) $package->price
                    : ($orderType === 'storage'
                        ? $this->entitlementService->getStorageTopupPrice()
                        : $this->entitlementService->getTierPrice($targetTier, $targetCycle));

                $valRes = $foundPromo->validateFor($rawAmount, $targetTier, $targetCycle, $business->id);
                if (! $valRes['valid']) {
                    return back()->with('error', 'Kode promo tidak dapat digunakan: ' . $valRes['reason'])->withInput();
                }
                $promo = $foundPromo;
            } else {
                return back()->with('error', "Kode promo '{$promoCode}' tidak valid atau kadaluarsa.")->withInput();
            }
        }

        $paymentMethod = $validated['payment_method'] ?? SubscriptionPayment::METHOD_QRIS;

        $payment = ($package && $orderType !== 'subscription')
            ? $this->entitlementService->createPackageOrder($business, $user, $package, $paymentMethod, $promo)
            : ($orderType === 'ai_token'
            ? $this->entitlementService->createTokenTopupOrder($business, $user, $paymentMethod)
            : ($orderType === 'storage'
                ? $this->entitlementService->createStorageTopupOrder($business, $user, $paymentMethod)
                : $this->entitlementService->createPaymentOrder(
                    $business,
                    $user,
                    $validated['cycle'] ?? 'monthly',
                    $paymentMethod,
                    $validated['tier'] ?? $request->input('tier', BusinessSubscription::TIER_STANDARD),
                    $promo
                )));

        // If payment is already approved (e.g., 100% discount promo voucher)
        if ($payment->status === SubscriptionPayment::STATUS_APPROVED) {
            return redirect()->route('billing.payment.invoice', $payment)
                ->with('success', "Pembayaran selesai! Paket {$payment->subscription_tier} berhasil diaktifkan dengan voucher {$payment->promo_code}.");
        }

        // Automatically trigger TriPay transaction for all non-free TriPay orders
        $isTripayChannel = array_key_exists($payment->payment_method, SubscriptionPayment::TRIPAY_CHANNELS)
            || array_key_exists($payment->payment_method, SubscriptionPayment::PAYMENT_METHODS)
            || str_starts_with($payment->payment_method, 'tripay_')
            || in_array(strtolower((string) $payment->payment_method), ['qris', 'qris2', 'bcava', 'mandiriva', 'briva', 'bniva', 'permatava'], true);

        if ($payment->payment_method !== SubscriptionPayment::METHOD_FREE_PROMO && $isTripayChannel) {
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
                        'admin_notes'       => null,
                    ]);

                    // Seamless in-app presentation (adopted from qr-order/menu.blade.php)
                    // Keep user in-app on billing.payment.show to display dynamic QRIS & live status
                    return redirect()->route('billing.payment.show', $payment)
                        ->with('success', __('billing.order_created_success', ['order' => $payment->order_number]));
                } else {
                    $errorMsg = $tripayRes['message'] ?? 'Gagal membuat tagihan TriPay';
                    $payment->update([
                        'admin_notes' => 'TriPay Error: ' . $errorMsg,
                    ]);
                    Log::warning("[SubscriptionCheckout] TriPay transaction failed for order {$payment->order_number}: {$errorMsg}");
                }
            } catch (\Throwable $e) {
                $payment->update([
                    'admin_notes' => 'TriPay Exception: ' . $e->getMessage(),
                ]);
                Log::warning('[SubscriptionCheckout] TriPay subscription auto-init failed: ' . $e->getMessage());
            }
        } elseif (! $isTripayChannel && $payment->payment_method !== SubscriptionPayment::METHOD_FREE_PROMO) {
            $payment->update([
                'payment_gateway' => SubscriptionPayment::GATEWAY_MANUAL,
            ]);
        }

        return redirect()->route('billing.payment.show', $payment)
            ->with('success', __('billing.order_created_success', ['order' => $payment->order_number]));
    }

    /**
     * Show payment confirmation and step-by-step transfer guide.
     */
    public function payment(Request $request, SubscriptionPayment $payment): View|JsonResponse
    {
        // Gracefully handle external webhook POSTs accidentally sent to payment page URL
        if ($request->isMethod('POST')) {
            return app(\App\Http\Controllers\Api\V1\Payment\TripayCallbackController::class)->handle($request);
        }

        $business = Context::requireBusiness();
        abort_unless($payment->business_id === $business->id, 403);

        // Auto-cancel if 15-minute payment window has expired
        if ($payment->status === SubscriptionPayment::STATUS_PENDING && $payment->isExpired()) {
            $payment->update([
                'status' => SubscriptionPayment::STATUS_CANCELLED,
                'admin_notes' => 'Tagihan otomatis dibatalkan karena melewati batas pembayaran 15 menit.',
            ]);
            $payment->refresh();
        }

        $isTripayChannel = array_key_exists($payment->payment_method, SubscriptionPayment::TRIPAY_CHANNELS)
            || array_key_exists($payment->payment_method, SubscriptionPayment::PAYMENT_METHODS)
            || str_starts_with($payment->payment_method, 'tripay_')
            || in_array(strtolower((string) $payment->payment_method), ['qris', 'qris2', 'bcava', 'mandiriva', 'briva', 'bniva', 'permatava'], true);

        // If payment gateway not initialized and order is pending, try initialize TriPay (only if not cancelled/expired)
        if (! $payment->isCancelled() && ! $payment->isExpired() && $isTripayChannel && empty($payment->gateway_reference) && $payment->status === SubscriptionPayment::STATUS_PENDING && $payment->payment_method !== SubscriptionPayment::METHOD_FREE_PROMO) {
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
                        'gateway_expired_at'=> isset($tripayRes['expired_time']) ? Carbon::createFromTimestamp($tripayRes['expired_time']) : now()->addMinutes(15),
                        'admin_notes'       => null,
                    ]);
                    $payment->refresh();
                } else {
                    $errorMsg = $tripayRes['message'] ?? 'Gagal membuat tagihan TriPay';
                    $payment->update([
                        'admin_notes' => 'TriPay Error: ' . $errorMsg,
                    ]);
                }
            } catch (\Throwable $e) {
                $payment->update([
                    'admin_notes' => 'TriPay Exception: ' . $e->getMessage(),
                ]);
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

        // Auto-cancel if 15-minute payment window has expired
        if ($payment->status === SubscriptionPayment::STATUS_PENDING && $payment->isExpired()) {
            $payment->update([
                'status' => SubscriptionPayment::STATUS_CANCELLED,
                'admin_notes' => 'Tagihan otomatis dibatalkan karena melewati batas pembayaran 15 menit.',
            ]);
            $payment->refresh();
        }

        $isCancelled = $payment->isCancelled();
        $isExpired = $payment->isExpired() || $isCancelled;

        $isTripayChannel = array_key_exists($payment->payment_method, SubscriptionPayment::TRIPAY_CHANNELS)
            || array_key_exists($payment->payment_method, SubscriptionPayment::PAYMENT_METHODS)
            || str_starts_with($payment->payment_method, 'tripay_')
            || in_array(strtolower((string) $payment->payment_method), ['qris', 'qris2', 'bcava', 'mandiriva', 'briva', 'bniva', 'permatava'], true);

        // If not initialized yet, try to initialize TriPay on status check as well (only if not expired)
        if (! $isExpired && $isTripayChannel && empty($payment->gateway_reference) && $payment->status === SubscriptionPayment::STATUS_PENDING && $payment->payment_method !== SubscriptionPayment::METHOD_FREE_PROMO) {
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
                        'gateway_expired_at'=> isset($tripayRes['expired_time']) ? Carbon::createFromTimestamp($tripayRes['expired_time']) : now()->addMinutes(15),
                        'admin_notes'       => null,
                    ]);
                    $payment->refresh();
                }
            } catch (\Throwable) {
                // Ignore background re-init errors
            }
        }

        $tier = $payment->billingPackage?->slug ?? $payment->plan_code ?? 'standard';
        $cycle = $payment->cycle ?? 'monthly';

        return response()->json([
            'success'           => true,
            'status'            => $payment->status,
            'is_paid'           => $payment->isPaid(),
            'is_cancelled'      => $isCancelled,
            'is_expired'        => $isExpired,
            'is_rejected'       => $payment->isRejected(),
            'has_qr'            => ! $isExpired && (! empty($payment->gateway_qr_url) || ! empty($payment->gateway_qr_string)),
            'qr_url'            => $isExpired ? null : $payment->gateway_qr_url,
            'pay_code'          => $isExpired ? null : $payment->gateway_pay_code,
            'gateway_reference' => $payment->gateway_reference,
            'gateway_error'     => $payment->admin_notes,
            'reorder_url'       => route('billing.checkout', ['tier' => $tier, 'cycle' => $cycle]),
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

        return redirect()->route('billing.payment.show', $payment)->with('info', __('billing.upload_proof_unnecessary'));
    }

    /**
     * Show payment orders history for the active business.
     */
    public function history(Request $request): View
    {
        $business = Context::requireBusiness();

        $query = SubscriptionPayment::where('business_id', $business->id);

        $status = $request->query('status');
        if ($status && in_array($status, ['pending', 'awaiting_approval', 'approved', 'rejected'], true)) {
            $query->where('status', $status);
        }

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('package_name', 'like', "%{$search}%");
            });
        }

        $payments = $query->orderByDesc('created_at')
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
