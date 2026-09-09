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
     * Display a listing of all subscription payment orders or tenant subscriptions for platform admin.
     */
    public function index(Request $request): View
    {
        $view = $request->get('view', 'payments'); // 'payments' or 'tenants'
        $status = $request->get('status', 'all');
        $type = $request->get('type', 'all');
        $method = $request->get('method', 'all');
        $search = $request->get('search');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Status counts for Payments
        $paymentCounts = [
            'all' => SubscriptionPayment::count(),
            'awaiting_approval' => SubscriptionPayment::where('status', SubscriptionPayment::STATUS_AWAITING_APPROVAL)->count(),
            'approved' => SubscriptionPayment::where('status', SubscriptionPayment::STATUS_APPROVED)->count(),
            'pending' => SubscriptionPayment::where('status', SubscriptionPayment::STATUS_PENDING)->count(),
            'rejected' => SubscriptionPayment::where('status', SubscriptionPayment::STATUS_REJECTED)->count(),
            'cancelled' => SubscriptionPayment::where('status', SubscriptionPayment::STATUS_CANCELLED)->count(),
        ];

        // Type counts for Payments
        $typeCounts = [
            'all' => SubscriptionPayment::count(),
            'subscription' => SubscriptionPayment::where('payment_type', 'subscription')->count(),
            'ai_token' => SubscriptionPayment::where('payment_type', 'ai_token')->count(),
            'storage' => SubscriptionPayment::where('payment_type', 'storage')->count(),
        ];

        $pendingCount = $paymentCounts['awaiting_approval'];
        $approvedCount = $paymentCounts['approved'];
        $totalRevenue = (float) SubscriptionPayment::where('status', SubscriptionPayment::STATUS_APPROVED)->sum('total_payable');
        $awaitingRevenue = (float) SubscriptionPayment::where('status', SubscriptionPayment::STATUS_AWAITING_APPROVAL)->sum('total_payable');

        // Tenant Subscription Counts
        $now = now();
        $tenantCounts = [
            'all' => \App\Models\Business::count(),
            'core_active' => \App\Models\Business::whereHas('subscription', function ($q) {
                $q->whereIn('plan_code', [\App\Models\BusinessSubscription::PLAN_CORE_MONTHLY, \App\Models\BusinessSubscription::PLAN_CORE_ANNUAL])
                    ->where('status', \App\Models\BusinessSubscription::STATUS_ACTIVE);
            })->count(),
            'free' => \App\Models\Business::where(function ($q) {
                $q->whereDoesntHave('subscription')
                    ->orWhereHas('subscription', function ($sq) {
                        $sq->where('plan_code', \App\Models\BusinessSubscription::PLAN_FREE)
                            ->orWhere('status', '!=', \App\Models\BusinessSubscription::STATUS_ACTIVE);
                    });
            })->count(),
            'expiring_soon' => \App\Models\Business::whereHas('subscription', function ($q) use ($now) {
                $q->where('status', \App\Models\BusinessSubscription::STATUS_ACTIVE)
                    ->whereNotNull('ends_at')
                    ->whereBetween('ends_at', [$now, $now->copy()->addDays(7)]);
            })->count(),
            'expired' => \App\Models\Business::whereHas('subscription', function ($q) use ($now) {
                $q->where('status', \App\Models\BusinessSubscription::STATUS_EXPIRED)
                    ->orWhere(function ($sq) use ($now) {
                        $sq->where('status', \App\Models\BusinessSubscription::STATUS_ACTIVE)
                            ->whereNotNull('ends_at')
                            ->where('ends_at', '<', $now);
                    });
            })->count(),
        ];

        $payments = null;
        $tenants = null;

        if ($view === 'tenants') {
            $tenantQuery = \App\Models\Business::with(['subscription', 'users'])
                ->withCount('products')
                ->latest();

            if ($status === 'core_active') {
                $tenantQuery->whereHas('subscription', function ($q) {
                    $q->whereIn('plan_code', [\App\Models\BusinessSubscription::PLAN_CORE_MONTHLY, \App\Models\BusinessSubscription::PLAN_CORE_ANNUAL])
                        ->where('status', \App\Models\BusinessSubscription::STATUS_ACTIVE);
                });
            } elseif ($status === 'free') {
                $tenantQuery->where(function ($q) {
                    $q->whereDoesntHave('subscription')
                        ->orWhereHas('subscription', function ($sq) {
                            $sq->where('plan_code', \App\Models\BusinessSubscription::PLAN_FREE)
                                ->orWhere('status', '!=', \App\Models\BusinessSubscription::STATUS_ACTIVE);
                        });
                });
            } elseif ($status === 'expiring_soon') {
                $tenantQuery->whereHas('subscription', function ($q) use ($now) {
                    $q->where('status', \App\Models\BusinessSubscription::STATUS_ACTIVE)
                        ->whereNotNull('ends_at')
                        ->whereBetween('ends_at', [$now, $now->copy()->addDays(7)]);
                });
            } elseif ($status === 'expired') {
                $tenantQuery->whereHas('subscription', function ($q) use ($now) {
                    $q->where('status', \App\Models\BusinessSubscription::STATUS_EXPIRED)
                        ->orWhere(function ($sq) use ($now) {
                            $sq->where('status', \App\Models\BusinessSubscription::STATUS_ACTIVE)
                                ->whereNotNull('ends_at')
                                ->where('ends_at', '<', $now);
                        });
                });
            }

            if ($search) {
                $tenantQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhereHas('users', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
                });
            }

            $tenants = $tenantQuery->paginate(15)->withQueryString();
        } else {
            // Default: 'payments' view
            $query = SubscriptionPayment::with(['business.subscription', 'user', 'approver', 'billingPackage'])
                ->orderByDesc('created_at');

            if ($status !== 'all') {
                $query->where('status', $status);
            }

            if ($type !== 'all') {
                $query->where('payment_type', $type);
            }

            if ($method !== 'all') {
                $query->where('payment_method', $method);
            }

            if ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhere('package_name', 'like', "%{$search}%")
                      ->orWhere('sender_account_name', 'like', "%{$search}%")
                      ->orWhere('sender_account_number', 'like', "%{$search}%")
                      ->orWhere('sender_bank', 'like', "%{$search}%")
                      ->orWhereHas('business', fn ($b) => $b->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%"))
                      ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
                });
            }

            $payments = $query->paginate(15)->withQueryString();
        }

        return view('admin.subscriptions.index', compact(
            'view',
            'payments',
            'tenants',
            'status',
            'type',
            'method',
            'search',
            'dateFrom',
            'dateTo',
            'pendingCount',
            'approvedCount',
            'totalRevenue',
            'awaitingRevenue',
            'paymentCounts',
            'typeCounts',
            'tenantCounts'
        ));
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
            default => "Pembayaran #{$payment->order_number} berhasil disetujui! Paket Cooca UMKM untuk bisnis {$payment->business->name} telah aktif.",
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
