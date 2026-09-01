<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\OperatingMode\OperatingModeResolver;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminBusinessController extends Controller
{
    /**
     * Display a listing of all tenant businesses.
     */
    public function index(Request $request): View
    {
        $query = Business::with(['subscription', 'users'])
            ->withCount(['products', 'bomHeaders'])
            ->latest();

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = (string) $request->get('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'suspended') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('plan')) {
            $plan = (string) $request->get('plan');
            if ($plan === 'core') {
                $query->whereHas('subscription', function ($q) {
                    $q->whereIn('plan_code', [BusinessSubscription::PLAN_CORE_MONTHLY, BusinessSubscription::PLAN_CORE_ANNUAL])
                        ->where('status', BusinessSubscription::STATUS_ACTIVE);
                });
            } elseif ($plan === 'free') {
                $query->where(function ($q) {
                    $q->whereDoesntHave('subscription')
                        ->orWhereHas('subscription', function ($sq) {
                            $sq->where('plan_code', BusinessSubscription::PLAN_FREE)
                                ->orWhere('status', '!=', BusinessSubscription::STATUS_ACTIVE);
                        });
                });
            }
        }

        $businesses = $query->paginate(15)->withQueryString();

        $totalBusinesses = Business::count();
        $activeBusinesses = Business::where('is_active', true)->count();
        $suspendedBusinesses = Business::where('is_active', false)->count();
        $coreBusinesses = BusinessSubscription::whereIn('plan_code', [BusinessSubscription::PLAN_CORE_MONTHLY, BusinessSubscription::PLAN_CORE_ANNUAL])
            ->where('status', BusinessSubscription::STATUS_ACTIVE)
            ->distinct('business_id')
            ->count('business_id');

        return view('admin.businesses.index', compact(
            'businesses',
            'totalBusinesses',
            'activeBusinesses',
            'suspendedBusinesses',
            'coreBusinesses'
        ));
    }

    /**
     * Display detailed workspace view for a single business.
     */
    public function show(Business $business): View
    {
        $business->load([
            'subscription',
            'subscriptions' => fn ($q) => $q->latest(),
            'subscriptionPayments' => fn ($q) => $q->latest()->take(10),
            'users',
        ]);

        $isSoloMode = OperatingModeResolver::isSoloMode($business);

        $productCount = $business->products()->count();
        $bomCount = $business->bomHeaders()->count();
        $invoiceCount = $business->invoices()->count();
        $posOrderCount = $business->posOrders()->count();
        $totalAiTokensUsed = (int) $business->aiTokenUsages()->sum('total_tokens');

        return view('admin.businesses.show', compact(
            'business',
            'isSoloMode',
            'productCount',
            'bomCount',
            'invoiceCount',
            'posOrderCount',
            'totalAiTokensUsed'
        ));
    }

    /**
     * Toggle the active/suspended status of a business workspace.
     */
    public function toggleStatus(Request $request, Business $business): RedirectResponse
    {
        if ($business->is_active) {
            $reason = (string) $request->input('reason', 'Ditangguhkan oleh Superadmin');
            $business->update([
                'is_active' => false,
                'suspended_reason' => $reason,
                'suspended_at' => now(),
            ]);

            return redirect()->back()
                ->with('success', "Workspace bisnis {$business->name} berhasil ditangguhkan.");
        }

        $business->update([
            'is_active' => true,
            'suspended_reason' => null,
            'suspended_at' => null,
        ]);

        return redirect()->back()
            ->with('success', "Workspace bisnis {$business->name} berhasil diaktifkan kembali.");
    }
}
