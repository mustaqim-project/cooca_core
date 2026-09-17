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

        // 6-Month Trends for Visual Charts (Apple HIG Chart.js)
        $chartMonths = [];
        $businessMonthlyTrend = [];
        $userMonthlyTrend = [];
        $revenueMonthlyTrend = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $year = $date->year;
            $month = $date->month;
            $chartMonths[] = $date->format('M y');

            $businessMonthlyTrend[] = Business::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();

            $userMonthlyTrend[] = User::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();

            $revenueMonthlyTrend[] = (int) SubscriptionPayment::where('status', SubscriptionPayment::STATUS_APPROVED)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->sum('total_payable');
        }

        // Subscription plan distribution
        $subscriptionBreakdown = [
            'free' => $freeSubscribersCount,
            'monthly' => $coreMonthlyCount,
            'annual' => $coreAnnualCount,
        ];

        // Ecosystem activity metrics
        $ecosystemStats = [
            'total_products' => $totalProducts,
            'total_costing_runs' => $totalCostingRuns,
            'total_ai_tokens_k' => (int) round($totalAiTokensConsumed / 1000),
            'total_businesses' => $totalBusinesses,
            'total_users' => $totalUsers,
        ];

        $conversionRate = $totalBusinesses > 0 ? round(($totalPaidSubscribers / $totalBusinesses) * 100, 1) : 0;
        $avgProductsPerBusiness = $totalBusinesses > 0 ? round($totalProducts / $totalBusinesses, 1) : 0;
        $avgCostingRunsPerBusiness = $totalBusinesses > 0 ? round($totalCostingRuns / $totalBusinesses, 1) : 0;

        // TriPay Centralized Payment Gateway Hub Metrics (Multi-tenant Platform Overview)
        $commerceGatewayGmv = (float) \App\Models\CommerceOrder::withoutGlobalScopes()
            ->where('payment_gateway', \App\Models\CommerceOrder::GATEWAY_TRIPAY)
            ->where('payment_status', \App\Models\CommerceOrder::PAYMENT_PAID)
            ->sum('total_amount');
        $commerceGatewayCount = \App\Models\CommerceOrder::withoutGlobalScopes()
            ->where('payment_gateway', \App\Models\CommerceOrder::GATEWAY_TRIPAY)
            ->where('payment_status', \App\Models\CommerceOrder::PAYMENT_PAID)
            ->count();
        $commerceGatewayFee = (float) \App\Models\CommerceOrder::withoutGlobalScopes()
            ->where('payment_gateway', \App\Models\CommerceOrder::GATEWAY_TRIPAY)
            ->where('payment_status', \App\Models\CommerceOrder::PAYMENT_PAID)
            ->sum('gateway_fee');

        $posGatewayGmv = (float) \App\Models\PosOrder::withoutGlobalScopes()
            ->where('payment_gateway', \App\Models\PosOrder::GATEWAY_TRIPAY)
            ->where('paid_amount', '>', 0)
            ->sum('paid_amount');
        $posGatewayCount = \App\Models\PosOrder::withoutGlobalScopes()
            ->where('payment_gateway', \App\Models\PosOrder::GATEWAY_TRIPAY)
            ->where('paid_amount', '>', 0)
            ->count();
        $posGatewayFee = (float) \App\Models\PosOrder::withoutGlobalScopes()
            ->where('payment_gateway', \App\Models\PosOrder::GATEWAY_TRIPAY)
            ->where('paid_amount', '>', 0)
            ->sum('gateway_fee');

        $subGatewayGmv = (float) SubscriptionPayment::where('status', SubscriptionPayment::STATUS_APPROVED)
            ->where('payment_method', 'like', 'tripay%')
            ->sum('total_payable');
        $subGatewayCount = SubscriptionPayment::where('status', SubscriptionPayment::STATUS_APPROVED)
            ->where('payment_method', 'like', 'tripay%')
            ->count();
        $subGatewayFee = (float) SubscriptionPayment::where('status', SubscriptionPayment::STATUS_APPROVED)
            ->where('payment_method', 'like', 'tripay%')
            ->sum('gateway_fee');

        $tripayTotalGmv = $commerceGatewayGmv + $posGatewayGmv + $subGatewayGmv;
        $tripayTotalCount = $commerceGatewayCount + $posGatewayCount + $subGatewayCount;
        $tripayTotalMdr = $commerceGatewayFee + $posGatewayFee + $subGatewayFee;
        $tripayNetVolume = max(0.0, $tripayTotalGmv - $tripayTotalMdr);

        // Webhook callback health metrics
        $totalCallbacks = \App\Models\PaymentGatewayCallbackLog::count();
        $successCallbacks = \App\Models\PaymentGatewayCallbackLog::where('status', \App\Models\PaymentGatewayCallbackLog::STATUS_SUCCESS)->count();
        $webhookHealthRate = $totalCallbacks > 0 ? round(($successCallbacks / $totalCallbacks) * 100, 1) : 100.0;
        $recentCallbacks = \App\Models\PaymentGatewayCallbackLog::latest()->take(5)->get();

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
            'recentBusinesses',
            'chartMonths',
            'businessMonthlyTrend',
            'userMonthlyTrend',
            'revenueMonthlyTrend',
            'subscriptionBreakdown',
            'ecosystemStats',
            'conversionRate',
            'avgProductsPerBusiness',
            'avgCostingRunsPerBusiness',
            'tripayTotalGmv',
            'tripayTotalCount',
            'tripayTotalMdr',
            'tripayNetVolume',
            'commerceGatewayGmv',
            'commerceGatewayCount',
            'posGatewayGmv',
            'posGatewayCount',
            'subGatewayGmv',
            'subGatewayCount',
            'webhookHealthRate',
            'totalCallbacks',
            'recentCallbacks'
        ));
    }
}
