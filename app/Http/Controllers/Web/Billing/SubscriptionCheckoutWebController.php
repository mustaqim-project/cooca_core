<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Billing;

use App\Domain\Billing\EntitlementService;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionPayment;
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
        $cycle = $request->get('cycle', 'monthly');
        if (!in_array($cycle, ['monthly', 'annual'], true)) {
            $cycle = 'monthly';
        }

        $basePrice = $cycle === 'annual' ? 1290000 : 129000;
        $paymentMethods = SubscriptionPayment::PAYMENT_METHODS;
        $currentUsage = $this->entitlementService->getUsageSummary($business);

        return view('app.billing.checkout', compact('business', 'cycle', 'basePrice', 'paymentMethods', 'currentUsage'));
    }

    /**
     * Create a new subscription payment order.
     */
    public function store(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $validated = $request->validate([
            'cycle' => ['required', 'string', 'in:monthly,annual'],
            'payment_method' => ['required', 'string', 'in:bca,mandiri,bri,qris'],
        ]);

        $payment = $this->entitlementService->createPaymentOrder(
            business: $business,
            user: $user,
            cycle: $validated['cycle'],
            paymentMethod: $validated['payment_method']
        );

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
