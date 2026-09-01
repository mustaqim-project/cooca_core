<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Billing\EntitlementService;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminSubscriptionController extends Controller
{
    public function __construct(
        private readonly EntitlementService $entitlementService = new EntitlementService
    ) {}

    /**
     * Display a listing of all subscription payment orders for platform admin.
     */
    public function index(Request $request): View
    {
        $status = $request->get('status', 'all');
        $search = $request->get('search');

        $query = SubscriptionPayment::with(['business', 'user', 'approver'])
            ->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('sender_account_name', 'like', "%{$search}%")
                  ->orWhereHas('business', fn ($b) => $b->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        $payments = $query->paginate(15)->withQueryString();

        // Metrics for top status cards
        $pendingCount = SubscriptionPayment::awaitingApproval()->count();
        $approvedCount = SubscriptionPayment::approved()->count();
        $totalRevenue = SubscriptionPayment::approved()->sum('total_payable');

        return view('admin.subscriptions.index', compact('payments', 'status', 'search', 'pendingCount', 'approvedCount', 'totalRevenue'));
    }

    /**
     * Display subscription payment verification detail.
     */
    public function show(SubscriptionPayment $payment): View
    {
        $payment->load(['business.subscription', 'user', 'approver']);
        $methodDetails = $payment->getPaymentMethodDetails();

        return view('admin.subscriptions.show', compact('payment', 'methodDetails'));
    }

    /**
     * Approve subscription payment and immediately activate Core plan.
     */
    public function approve(Request $request, SubscriptionPayment $payment): RedirectResponse
    {
        $admin = auth('admin')->user();
        abort_unless($admin !== null, 403);

        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->entitlementService->approvePayment(
            payment: $payment,
            admin: $admin,
            adminNotes: $validated['admin_notes'] ?? 'Diverifikasi dan disetujui oleh Administrator Platform'
        );

        $message = match ($payment->payment_type) {
            'ai_token' => "Pembayaran #{$payment->order_number} disetujui. Batch token AI telah ditambahkan.",
            'storage' => "Pembayaran #{$payment->order_number} disetujui. Kapasitas storage owner telah ditambahkan.",
            default => "Pembayaran #{$payment->order_number} berhasil disetujui! Paket Cooca Core untuk bisnis {$payment->business->name} telah aktif.",
        };

        return redirect()->route('admin.subscriptions.show', $payment)->with('success', $message);
    }

    /**
     * Reject subscription payment with explanation note.
     */
    public function reject(Request $request, SubscriptionPayment $payment): RedirectResponse
    {
        $admin = auth('admin')->user();
        abort_unless($admin !== null, 403);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $this->entitlementService->rejectPayment(
            payment: $payment,
            admin: $admin,
            reason: $validated['reason']
        );

        return redirect()->route('admin.subscriptions.show', $payment)
            ->with('warning', "Pembayaran #{$payment->order_number} telah ditolak dengan alasan: {$validated['reason']}");
    }
}
