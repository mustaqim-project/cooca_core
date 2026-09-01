<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiTokenUsage;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\CostingRun;
use App\Models\Product;
use App\Models\SubscriptionPayment;
use App\Models\User;
use Illuminate\View\View;

final class AdminDashboardController extends Controller
{
    /**
     * Display the admin overview dashboard with SaaS financial & platform metrics.
     */
    public function index(): View
    {
        $totalUsers = User::count();
        $totalBusinesses = Business::count();
        $totalProducts = Product::withoutGlobalScopes()->count();
        $totalCostingRuns = CostingRun::withoutGlobalScopes()->count();

        // SaaS Subscriptions & MRR / ARR Metrics
        $coreMonthlyCount = BusinessSubscription::where('plan_code', BusinessSubscription::PLAN_CORE_MONTHLY)
            ->where('status', BusinessSubscription::STATUS_ACTIVE)
            ->count();

        $coreAnnualCount = BusinessSubscription::where('plan_code', BusinessSubscription::PLAN_CORE_ANNUAL)
            ->where('status', BusinessSubscription::STATUS_ACTIVE)
            ->count();

        $totalPaidSubscribers = $coreMonthlyCount + $coreAnnualCount;
        $freeSubscribersCount = max(0, $totalBusinesses - $totalPaidSubscribers);

        // MRR calculation: Monthly + (Annual / 12)
        $mrr = ($coreMonthlyCount * 129000) + (int) round(($coreAnnualCount * 1290000) / 12);
        $arr = $mrr * 12;

        // Global AI Token Consumption
        $totalAiTokensConsumed = (int) AiTokenUsage::sum('total_tokens');

        // Pending Payment Approvals Widget
        $pendingSubscriptionsCount = SubscriptionPayment::where('status', SubscriptionPayment::STATUS_AWAITING_APPROVAL)->count();
        $pendingSubscriptions = SubscriptionPayment::with(['business', 'user'])
            ->where('status', SubscriptionPayment::STATUS_AWAITING_APPROVAL)
            ->latest()
            ->take(5)
            ->get();

        $recentUsers = User::with('activeBusiness')->latest()->take(6)->get();
        $recentBusinesses = Business::with(['users', 'subscription'])->latest()->take(6)->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalBusinesses',
            'totalProducts',
            'totalCostingRuns',
            'coreMonthlyCount',
            'coreAnnualCount',
            'freeSubscribersCount',
            'totalPaidSubscribers',
            'mrr',
            'arr',
            'totalAiTokensConsumed',
            'pendingSubscriptionsCount',
            'pendingSubscriptions',
            'recentUsers',
            'recentBusinesses'
        ));
    }
}
