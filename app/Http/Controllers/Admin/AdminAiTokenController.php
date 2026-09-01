<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiTokenUsage;
use App\Models\Business;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class AdminAiTokenController extends Controller
{
    /**
     * Display AI token monitoring dashboard and usage metrics.
     */
    public function index(Request $request): View
    {
        $startOfMonth = Carbon::now()->startOfMonth();

        $totalTokensAllTime = (int) AiTokenUsage::sum('total_tokens');
        $totalTokensMonth = (int) AiTokenUsage::where('created_at', '>=', $startOfMonth)->sum('total_tokens');
        $totalQueriesCount = AiTokenUsage::count();
        $activeAiBusinessesCount = AiTokenUsage::where('created_at', '>=', $startOfMonth)
            ->distinct('business_id')
            ->count('business_id');

        // Usage by Intent Breakdown
        $intentBreakdown = AiTokenUsage::select('intent', DB::raw('SUM(total_tokens) as total_tokens'), DB::raw('COUNT(*) as queries_count'))
            ->groupBy('intent')
            ->orderByDesc('total_tokens')
            ->get();

        // Top 10 Consuming Businesses
        $topBusinesses = AiTokenUsage::select('business_id', DB::raw('SUM(total_tokens) as total_tokens'), DB::raw('COUNT(*) as queries_count'))
            ->groupBy('business_id')
            ->orderByDesc('total_tokens')
            ->take(10)
            ->get()
            ->map(function ($item) {
                $business = Business::find($item->business_id);
                return [
                    'business_id' => $item->business_id,
                    'business_name' => $business?->name ?? 'Bisnis Dihapus',
                    'slug' => $business?->slug ?? '',
                    'total_tokens' => (int) $item->total_tokens,
                    'queries_count' => (int) $item->queries_count,
                ];
            });

        // Recent 20 AI Token Activity logs
        $recentUsages = AiTokenUsage::with(['user'])
            ->latest()
            ->take(20)
            ->get()
            ->map(function ($usage) {
                $business = Business::find($usage->business_id);
                $usage->business_name = $business?->name ?? 'Bisnis Dihapus';
                return $usage;
            });

        // All businesses list for grant modal
        $allBusinesses = Business::orderBy('name')->get(['id', 'name', 'slug']);

        return view('admin.ai_tokens.index', compact(
            'totalTokensAllTime',
            'totalTokensMonth',
            'totalQueriesCount',
            'activeAiBusinessesCount',
            'intentBreakdown',
            'topBusinesses',
            'recentUsages',
            'allBusinesses'
        ));
    }

    /**
     * Manually grant / top-up AI tokens to a business.
     */
    public function grant(Request $request, Business $business)
    {
        $request->validate([
            'tokens' => 'required|integer|min:100000',
            'reason' => 'nullable|string|max:255',
        ]);

        $sub = $business->subscription;
        if ($sub) {
            $sub->increment('ai_tokens_remaining', (int) $request->tokens);
        }

        return back()->with('success', "Berhasil menambahkan " . number_format((int)$request->tokens) . " token AI ke bisnis {$business->name}.");
    }
}
