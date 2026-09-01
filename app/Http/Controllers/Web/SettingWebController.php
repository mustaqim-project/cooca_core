<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Template\BusinessTemplateService;
use App\Http\Controllers\Controller;
use App\Models\BusinessTypeTemplate;
use App\Models\Currency;
use App\Models\Location;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SettingWebController extends Controller
{
    public function __construct(private readonly BusinessTemplateService $templateService = new BusinessTemplateService) {}

    /**
     * Show settings dashboard.
     */
    public function index(): View
    {
        $business = Context::requireBusiness();

        $templates = BusinessTypeTemplate::all();
        $currencies = Currency::all();
        $locations = Location::latest()->get();
        $members = $business->memberships()->with('user')->get();
        $suppliers = \App\Models\Supplier::where('business_id', $business->id)->latest()->get();
        $materialCategories = \App\Models\MaterialCategory::where('business_id', $business->id)->latest()->get();
        $productCategories = \App\Models\ProductCategory::where('business_id', $business->id)->latest()->get();
        $customUnits = \App\Models\Unit::where('business_id', $business->id)->latest()->get();
        $systemUnits = \App\Models\Unit::whereNull('business_id')->orderBy('name')->get();
        $canAddMember = app(\App\Domain\Billing\EntitlementService::class)->canAddMember($business);

        return view('app.settings.index', compact(
            'business', 'templates', 'currencies', 'locations', 'members',
            'suppliers', 'materialCategories', 'productCategories', 'customUnits', 'systemUnits', 'canAddMember'
        ));
    }

    /**
     * Update business settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'tax_identification_number' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:100'],
            'bank_account_holder' => ['nullable', 'string', 'max:150'],
            'currency' => ['nullable', 'string', 'max:10'],
            'currency_code' => ['nullable', 'string', 'max:10'],
            'rounding_strategy' => ['required', 'string'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'tax_identification_number' => $validated['tax_identification_number'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'bank_account_holder' => $validated['bank_account_holder'] ?? null,
            'currency' => $validated['currency'] ?? $validated['currency_code'] ?? $business->currency,
            'rounding_strategy' => $validated['rounding_strategy'],
        ];

        // Handle remove logo
        if ($request->boolean('remove_logo')) {
            if ($business->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($business->logo_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($business->logo_path);
            }
            $updateData['logo_path'] = null;
        }

        // Handle upload new logo
        if ($request->hasFile('logo')) {
            if ($business->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($business->logo_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($business->logo_path);
            }
            $path = $request->file('logo')->store('business_logos', 'public');
            $updateData['logo_path'] = $path;
        }

        $business->update($updateData);

        return back()->with('success', 'Profil bisnis dan logo berhasil diperbarui.');
    }

    /**
     * 1-Click Apply Industry Template to existing business.
     */
    public function applyTemplate(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'template_code' => ['required', 'string', 'exists:business_type_templates,code'],
        ]);

        /** @var BusinessTypeTemplate $template */
        $template = BusinessTypeTemplate::where('code', $validated['template_code'])->firstOrFail();

        $result = $this->templateService->apply($business, $template);

        return back()->with('success', "Template '{$template->name}' berhasil diterapkan ({$result['components_created']} komponen biaya ditambahkan).");
    }

    /**
     * Add employee / team member to business workspace.
     */
    public function storeMember(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        $entitlement = app(\App\Domain\Billing\EntitlementService::class);

        if (! $entitlement->canAddMember($business)) {
            return back()->with('error', 'Paket Free Plan dibatasi untuk 1 pengguna (Solo Owner). Silakan upgrade ke Cooca Core untuk menambahkan karyawan tanpa batas.');
        }

        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'role' => ['required', 'string', 'in:owner,admin,cashier,staff,inventory'],
        ]);

        $user = \App\Models\User::where('email', $validated['email'])->first();

        if (! $user) {
            return back()->with('error', "Pengguna dengan email {$validated['email']} belum terdaftar di Cooca Core. Minta karyawan untuk mendaftar akun terlebih dahulu.");
        }

        if ($business->users()->where('users.id', $user->id)->exists()) {
            return back()->with('error', 'Pengguna tersebut sudah menjadi anggota workspace bisnis ini.');
        }

        $business->users()->attach($user->id, [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'role' => $validated['role'],
        ]);

        return back()->with('success', "Karyawan {$user->name} ({$validated['email']}) berhasil ditambahkan sebagai {$validated['role']}.");
    }

    /**
     * Remove employee from business workspace.
     */
    public function destroyMember(\App\Models\BusinessMembership $member): RedirectResponse
    {
        $business = Context::requireBusiness();

        if ($member->business_id !== $business->id) {
            abort(403);
        }

        if ($member->role === 'owner' && $business->memberships()->where('role', 'owner')->count() <= 1) {
            return back()->with('error', 'Tidak dapat menghapus satu-satunya Owner bisnis.');
        }

        $member->delete();

        return back()->with('success', 'Anggota tim berhasil dihapus dari workspace.');
    }
}
