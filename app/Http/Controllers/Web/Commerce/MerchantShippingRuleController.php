<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Commerce;

use App\Http\Controllers\Controller;
use App\Models\CommerceShippingRule;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class MerchantShippingRuleController extends Controller
{
    /**
     * Display merchant shipping settings and rule list.
     */
    public function index(): View
    {
        $business = Context::business();
        abort_unless($business, 404);
        abort_unless(Context::hasPermission('storefront.shipping.manage'), 403);

        $rules = CommerceShippingRule::where('business_id', $business->id)
            ->ordered()
            ->get();

        $storeSetting = $business->storeSetting;

        return view('app.storefront.shipping.index', compact('business', 'rules', 'storeSetting'));
    }

    /**
     * Store a new shipping calculation rule.
     */
    public function store(Request $request): RedirectResponse
    {
        $business = Context::business();
        abort_unless($business, 404);
        abort_unless(Context::hasPermission('storefront.shipping.manage'), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'rule_type' => ['required', 'string', 'in:flat,distance_tier,free_threshold'],
            'rate_amount' => ['required', 'numeric', 'min:0'],
            'min_distance_km' => ['nullable', 'numeric', 'min:0'],
            'max_distance_km' => ['nullable', 'numeric', 'min:0'],
            'min_order_for_free' => ['nullable', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        CommerceShippingRule::create([
            'business_id' => $business->id,
            'name' => $validated['name'],
            'rule_type' => $validated['rule_type'],
            'rate_amount' => (float) $validated['rate_amount'],
            'min_distance_km' => isset($validated['min_distance_km']) ? (float) $validated['min_distance_km'] : null,
            'max_distance_km' => isset($validated['max_distance_km']) ? (float) $validated['max_distance_km'] : null,
            'min_order_for_free' => isset($validated['min_order_for_free']) ? (float) $validated['min_order_for_free'] : null,
            'is_active' => true,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return back()->with('success', "Aturan ongkir '{$validated['name']}' berhasil ditambahkan.");
    }

    /**
     * Update an existing shipping calculation rule.
     */
    public function update(Request $request, CommerceShippingRule $shippingRule): RedirectResponse
    {
        $business = Context::business();
        abort_unless($business && $shippingRule->business_id === $business->id, 403);
        abort_unless(Context::hasPermission('storefront.shipping.manage'), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'rule_type' => ['required', 'string', 'in:flat,distance_tier,free_threshold'],
            'rate_amount' => ['required', 'numeric', 'min:0'],
            'min_distance_km' => ['nullable', 'numeric', 'min:0'],
            'max_distance_km' => ['nullable', 'numeric', 'min:0'],
            'min_order_for_free' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $shippingRule->update([
            'name' => $validated['name'],
            'rule_type' => $validated['rule_type'],
            'rate_amount' => (float) $validated['rate_amount'],
            'min_distance_km' => isset($validated['min_distance_km']) ? (float) $validated['min_distance_km'] : null,
            'max_distance_km' => isset($validated['max_distance_km']) ? (float) $validated['max_distance_km'] : null,
            'min_order_for_free' => isset($validated['min_order_for_free']) ? (float) $validated['min_order_for_free'] : null,
            'is_active' => isset($validated['is_active']) ? (bool) $validated['is_active'] : $shippingRule->is_active,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return back()->with('success', "Aturan ongkir '{$shippingRule->name}' berhasil diperbarui.");
    }

    /**
     * Toggle active state of a shipping rule.
     */
    public function toggle(CommerceShippingRule $shippingRule): RedirectResponse
    {
        $business = Context::business();
        abort_unless($business && $shippingRule->business_id === $business->id, 403);
        abort_unless(Context::hasPermission('storefront.shipping.manage'), 403);

        $shippingRule->update(['is_active' => ! $shippingRule->is_active]);

        $statusLabel = $shippingRule->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Aturan ongkir '{$shippingRule->name}' berhasil {$statusLabel}.");
    }

    /**
     * Delete a shipping rule.
     */
    public function destroy(CommerceShippingRule $shippingRule): RedirectResponse
    {
        $business = Context::business();
        abort_unless($business && $shippingRule->business_id === $business->id, 403);
        abort_unless(Context::hasPermission('storefront.shipping.manage'), 403);

        $name = $shippingRule->name;
        $shippingRule->delete();

        return back()->with('success', "Aturan ongkir '{$name}' telah dihapus.");
    }
}
