<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPromo;
use App\Models\SubscriptionPromoUsage;
use Carbon\Carbon;
use Database\Seeders\SubscriptionPromoSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminPromoController extends Controller
{
    /**
     * Display a listing of promos and usage analytics.
     */
    public function index(Request $request): View
    {
        $status = $request->get('status', 'all');
        $search = $request->get('search');
        $tab = $request->get('tab', 'promos'); // 'promos' or 'usages'

        $query = SubscriptionPromo::withCount('usages')->latest();

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $promos = $query->paginate(15)->withQueryString();

        // Metrics Cockpit
        $totalActivePromos = SubscriptionPromo::where('is_active', true)->count();
        $totalUsagesCount = SubscriptionPromoUsage::count();
        $totalDiscountGiven = (float) SubscriptionPromoUsage::sum('discount_amount');
        $topPromo = SubscriptionPromo::orderByDesc('used_count')->first();

        // Recent Usages Log
        $usagesQuery = SubscriptionPromoUsage::with(['promo', 'business', 'user', 'payment'])->latest();
        if (! empty($search) && $tab === 'usages') {
            $usagesQuery->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('promo', fn ($p) => $p->where('code', 'like', "%{$search}%"))
                    ->orWhereHas('business', fn ($b) => $b->where('name', 'like', "%{$search}%"));
            });
        }
        $usages = $usagesQuery->paginate(20, ['*'], 'usages_page')->withQueryString();

        return view('admin.promos.index', compact(
            'promos',
            'usages',
            'tab',
            'status',
            'search',
            'totalActivePromos',
            'totalUsagesCount',
            'totalDiscountGiven',
            'topPromo'
        ));
    }

    /**
     * Store a newly created promo code.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePromo($request);
        $validated['code'] = strtoupper(trim($validated['code']));

        SubscriptionPromo::create($validated);

        return redirect()->route('admin.promos.index')
            ->with('success', "Kode promo {$validated['code']} berhasil diterbitkan.");
    }

    /**
     * Update the specified promo code.
     */
    public function update(Request $request, SubscriptionPromo $promo): RedirectResponse
    {
        $validated = $this->validatePromo($request, $promo->id);
        $validated['code'] = strtoupper(trim($validated['code']));

        $promo->update($validated);

        return redirect()->route('admin.promos.index')
            ->with('success', "Kode promo {$promo->code} berhasil diperbarui.");
    }

    /**
     * Toggle active status of a promo.
     */
    public function toggle(SubscriptionPromo $promo): RedirectResponse
    {
        $promo->update(['is_active' => ! $promo->is_active]);

        return back()->with('success', $promo->is_active ? "Promo {$promo->code} diaktifkan." : "Promo {$promo->code} dinonaktifkan.");
    }

    /**
     * Remove the specified promo code.
     */
    public function destroy(SubscriptionPromo $promo): RedirectResponse
    {
        $code = $promo->code;
        $promo->delete();

        return redirect()->route('admin.promos.index')
            ->with('success', "Kode promo {$code} berhasil dihapus.");
    }

    /**
     * Seed or reset default best practice subscription promo schemes.
     */
    public function seedDefaults(): RedirectResponse
    {
        (new SubscriptionPromoSeeder())->run();

        return back()->with('success', 'Skema promo unggulan berhasil dimuat dan disinkronkan ke katalog promo!');
    }

    /**
     * Validate request input for promo.
     *
     * @return array<string, mixed>
     */
    private function validatePromo(Request $request, ?string $promoId = null): array
    {
        $uniqueRule = 'unique:subscription_promos,code' . ($promoId ? ",{$promoId}" : '');

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:32', 'regex:/^[A-Z0-9_\-]+$/i', $uniqueRule],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0.01'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_per_business_limit' => ['nullable', 'integer', 'min:1'],
            'applicable_tiers' => ['nullable', 'array'],
            'applicable_tiers.*' => ['string', 'in:standard,premium,prestige'],
            'applicable_cycles' => ['nullable', 'array'],
            'applicable_cycles.*' => ['string', 'in:monthly,annual'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['min_order_amount'] = (float) ($validated['min_order_amount'] ?? 0);
        $validated['usage_per_business_limit'] = (int) ($validated['usage_per_business_limit'] ?? 1);

        if ($validated['discount_type'] === 'percentage' && (float) $validated['discount_value'] > 100) {
            $validated['discount_value'] = 100.0;
        }

        return $validated;
    }
}
