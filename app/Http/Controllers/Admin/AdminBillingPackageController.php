<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillingPackage;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class AdminBillingPackageController extends Controller
{
    public function index(string $type = BillingPackage::TYPE_SUBSCRIPTION): View
    {
        abort_unless(in_array($type, BillingPackage::TYPES, true), 404);
        $packages = BillingPackage::where('type', $type)->orderBy('sort_order')->orderBy('name')->get();
        $counts = BillingPackage::select('type', DB::raw('COUNT(*) as total'))->groupBy('type')->pluck('total', 'type');

        // Default Core pricing shown on the subscription catalog
        $subscriptionPriceMonthly = SystemSetting::get('subscription_price_monthly', '129000');
        $subscriptionPriceAnnual = SystemSetting::get('subscription_price_annual', '1290000');
        $subscriptionAiTokensMonthly = SystemSetting::get('subscription_ai_tokens_monthly', '10000000');
        $subscriptionAnnualDiscountBadge = SystemSetting::get('subscription_annual_discount_badge', 'Hemat 2 Bulan');

        // Default top-up pricing for token & storage catalogs
        $aiTokenTopupPrice = SystemSetting::get('ai_token_topup_price', '50000');
        $aiTokenTopupAmount = SystemSetting::get('ai_token_topup_amount', '1000000');
        $ownerStorageLimitGb = SystemSetting::get('owner_storage_limit_gb', '3');
        $storageTopupPrice = SystemSetting::get('storage_topup_price', '50000');
        $storageTopupGb = SystemSetting::get('storage_topup_gb', '1');

        return view('admin.billing-packages.index', compact(
            'type',
            'packages',
            'counts',
            'subscriptionPriceMonthly',
            'subscriptionPriceAnnual',
            'subscriptionAiTokensMonthly',
            'subscriptionAnnualDiscountBadge',
            'aiTokenTopupPrice',
            'aiTokenTopupAmount',
            'ownerStorageLimitGb',
            'storageTopupPrice',
            'storageTopupGb'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePackage($request);
        $type = $validated['type'];
        $slug = Str::slug($validated['name']);
        $baseSlug = $slug;
        $counter = 2;
        while (BillingPackage::where('type', $type)->where('code', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        BillingPackage::create($this->packageData($validated, $slug));
        return redirect()->route('admin.billing-packages.index', $type)->with('success', 'Paket billing berhasil dibuat.');
    }

    public function update(Request $request, BillingPackage $billingPackage): RedirectResponse
    {
        $validated = $this->validatePackage($request, false);
        abort_unless($billingPackage->type === $validated['type'], 422);
        $billingPackage->update($this->packageData($validated, $billingPackage->code));
        return redirect()->route('admin.billing-packages.index', $billingPackage->type)->with('success', 'Paket billing berhasil diperbarui.');
    }

    public function toggle(BillingPackage $billingPackage): RedirectResponse
    {
        $billingPackage->update(['is_active' => ! $billingPackage->is_active]);
        return back()->with('success', $billingPackage->is_active ? 'Paket diaktifkan.' : 'Paket dinonaktifkan.');
    }

    /** @return array<string, mixed> */
    private function validatePackage(Request $request, bool $creating = true): array
    {
        return $request->validate([
            'type' => ['required', 'in:' . implode(',', BillingPackage::TYPES)],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_days' => ['nullable', 'integer', 'min:1', 'required_if:type,subscription'],
            'token_quantity' => ['nullable', 'integer', 'min:1', 'required_if:type,ai_token'],
            'token_expiry_days' => ['nullable', 'integer', 'min:1', 'required_if:type,ai_token'],
            'storage_gb' => ['nullable', 'numeric', 'min:0.01', 'required_if:type,storage'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    /** @param array<string, mixed> $validated @return array<string, mixed> */
    private function packageData(array $validated, string $code): array
    {
        return [
            'type' => $validated['type'],
            'code' => $code,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'duration_days' => $validated['type'] === BillingPackage::TYPE_SUBSCRIPTION ? $validated['duration_days'] : null,
            'token_quantity' => $validated['type'] === BillingPackage::TYPE_SUBSCRIPTION || $validated['type'] === BillingPackage::TYPE_AI_TOKEN ? ($validated['token_quantity'] ?? null) : null,
            'storage_bytes' => $validated['type'] === BillingPackage::TYPE_STORAGE ? (int) round((float) $validated['storage_gb'] * 1073741824) : null,
            'token_expiry_days' => $validated['type'] === BillingPackage::TYPE_AI_TOKEN ? $validated['token_expiry_days'] : null,
            'sort_order' => $validated['sort_order'] ?? 0,
        ];
    }
}
