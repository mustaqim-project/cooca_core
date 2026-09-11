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
use Illuminate\Support\Facades\Schema;
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
        $locations = Location::where('business_id', $business->id)->latest()->get();
        $members = $business->memberships()->with('user')->get();
        $suppliers = \App\Models\Supplier::where('business_id', $business->id)->latest()->get();
        $materialCategories = \App\Models\MaterialCategory::where('business_id', $business->id)->latest()->get();
        $productCategories = \App\Models\ProductCategory::where('business_id', $business->id)->latest()->get();
        $customUnits = \App\Models\Unit::where('business_id', $business->id)->latest()->get();
        $systemUnits = \App\Models\Unit::whereNull('business_id')->orderBy('name')->get();
        $availableUnits = $systemUnits->concat($customUnits);
        $unitConversions = \App\Models\UnitConversion::where('business_id', $business->id)
            ->with(['fromUnit', 'toUnit'])
            ->latest()->get();

        $canAddMember = app(\App\Domain\Billing\EntitlementService::class)->canAddMember($business);
        $roles = \App\Models\Role::whereNull('business_id')
            ->orWhere('business_id', $business->id)
            ->orderBy('business_id')
            ->orderBy('name')
            ->get();

        return view('app.settings.index', compact(
            'business', 'templates', 'currencies', 'locations', 'members',
            'suppliers', 'materialCategories', 'productCategories', 'customUnits', 'systemUnits', 'availableUnits',
            'unitConversions', 'canAddMember', 'roles'
        ));
    }

    /**
     * Update business settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'tax_identification_number' => ['nullable', 'string', 'max:50'],
            'pos_enable_tax' => ['nullable', 'boolean'],
            'pos_show_product_images' => ['nullable', 'boolean'],
            'pos_tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pos_receipt_footer_note' => ['nullable', 'string', 'max:500'],
            'pos_receipt_wa_template' => ['nullable', 'string', 'max:2000'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:100'],
            'bank_account_holder' => ['nullable', 'string', 'max:150'],
            'currency' => ['nullable', 'string', 'max:10'],
            'currency_code' => ['nullable', 'string', 'max:10'],
            'rounding_strategy' => ['nullable', 'string'],
        ]);

        $updateData = [];

        if (array_key_exists('name', $validated) && $validated['name']) {
            $updateData['name'] = $validated['name'];
        }
        if (array_key_exists('phone', $validated)) {
            $updateData['phone'] = $validated['phone'];
        }
        if (array_key_exists('email', $validated)) {
            $updateData['email'] = $validated['email'];
        }
        if (array_key_exists('address', $validated)) {
            $updateData['address'] = $validated['address'];
        }
        if (array_key_exists('tax_identification_number', $validated)) {
            $updateData['tax_identification_number'] = $validated['tax_identification_number'];
        }
        if ($request->has('pos_enable_tax')) {
            $updateData['pos_enable_tax'] = $request->boolean('pos_enable_tax');
        }
        if ($request->has('pos_show_product_images')) {
            $updateData['pos_show_product_images'] = $request->boolean('pos_show_product_images');
        }
        if (array_key_exists('pos_tax_percent', $validated)) {
            $updateData['pos_tax_percent'] = (float) ($validated['pos_tax_percent'] ?? 0);
        }
        if (array_key_exists('pos_receipt_footer_note', $validated)) {
            $updateData['pos_receipt_footer_note'] = $validated['pos_receipt_footer_note'];
        }
        if (array_key_exists('pos_receipt_wa_template', $validated)) {
            $updateData['pos_receipt_wa_template'] = $validated['pos_receipt_wa_template'];
            // Sync to WhatsAppSession if exists
            \App\Models\WhatsAppSession::where('business_id', $business->id)->update([
                'receipt_template' => $validated['pos_receipt_wa_template'],
            ]);
        }
        if (array_key_exists('bank_name', $validated)) {
            $updateData['bank_name'] = $validated['bank_name'];
        }
        if (array_key_exists('bank_account_number', $validated)) {
            $updateData['bank_account_number'] = $validated['bank_account_number'];
        }
        if (array_key_exists('bank_account_holder', $validated)) {
            $updateData['bank_account_holder'] = $validated['bank_account_holder'];
        }
        if ($request->filled('currency') || $request->filled('currency_code')) {
            $updateData['currency'] = $validated['currency'] ?? $validated['currency_code'];
        }
        if ($request->filled('rounding_strategy')) {
            $updateData['rounding_strategy'] = $validated['rounding_strategy'];
        }

        $trackingService = app(\App\Domain\Storage\StorageTrackingService::class);
        $owner = app(\App\Domain\Storage\OwnerStorageQuotaService::class)->ownerForBusiness($business);

        // Handle remove logo
        if ($request->boolean('remove_logo')) {
            if ($business->logo_path) {
                $trackingService->deleteFile($business->logo_path, 'public');
            }
            $updateData['logo_path'] = null;
        }

        // Handle upload new logo
        if ($request->hasFile('logo')) {
            if ($owner) {
                $trackingService->assertCanUpload($owner, (int) $request->file('logo')->getSize(), 'logo');
            }
            if ($business->logo_path) {
                $trackingService->deleteFile($business->logo_path, 'public');
            }
            $path = $request->file('logo')->store('businesses/' . $business->id . '/logo', 'public');
            if ($owner) {
                $trackingService->recordUpload(
                    file: $request->file('logo'),
                    filePath: $path,
                    category: \App\Models\StorageFile::CATEGORY_BUSINESS_LOGO,
                    module: 'settings',
                    owner: $owner,
                    business: $business,
                    uploader: $request->user()
                );
            }
            $updateData['logo_path'] = $path;
        }

        if (! empty($updateData)) {
            $business->update($updateData);
        }

        $tab = $request->input('_tab', 'general');
        $msg = $tab === 'wa_receipt' ? 'Template pesan WhatsApp struk berhasil diperbarui.' : 'Profil bisnis dan pengaturan berhasil diperbarui.';

        return back()->with('success', $msg)->with('active_tab', $tab);
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
        abort_unless(Context::hasPermission('users.manage'), 403, 'Anda tidak memiliki izin mengelola anggota tim.');
        $entitlement = app(\App\Domain\Billing\EntitlementService::class);

        if (! $entitlement->canAddMember($business)) {
            return back()->with('error', 'Paket Free Plan dibatasi untuk 1 pengguna (Solo Owner). Silakan upgrade ke Cooca UMKM untuk menambahkan karyawan tanpa batas.');
        }

        if (! $request->filled('role_id')) {
            $roleSlug = $request->input('role', 'staff');
            $roleObj = \App\Models\Role::where('slug', $roleSlug)->first()
                ?? \App\Models\Role::firstOrCreate(
                    ['slug' => $roleSlug, 'business_id' => $business->id],
                    ['name' => ucfirst((string) $roleSlug)]
                );
            $request->merge(['role_id' => $roleObj->id]);
        }

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email'],
            'password' => ['nullable', 'string', 'min:6'],
            'role_id' => ['required', 'uuid', 'exists:roles,id'],
        ]);

        $selectedRole = \App\Models\Role::where('id', $validated['role_id'])
            ->where(function ($query) use ($business): void {
                $query->whereNull('business_id')->orWhere('business_id', $business->id);
            })
            ->firstOrFail();

        $user = \App\Models\User::where('email', $validated['email'])->first();

        if (! $user) {
            // Auto create employee account with owner-specified password
            $user = \App\Models\User::create([
                'name' => ! empty($validated['name']) ? $validated['name'] : explode('@', $validated['email'])[0],
                'email' => $validated['email'],
                'password' => \Illuminate\Support\Facades\Hash::make($validated['password'] ?? 'password123'),
                'email_verified_at' => now(),
            ]);
        }

        if ($business->users()->where('users.id', $user->id)->exists()) {
            return back()->with('error', 'Pengguna tersebut sudah menjadi anggota workspace bisnis ini.');
        }

        $business->users()->attach($user->id, [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'role' => $selectedRole->slug,
            'role_id' => $selectedRole->id,
        ]);

        // If user doesn't have an active business context set, link this business
        if (! $user->active_business_id) {
            $user->update(['active_business_id' => $business->id]);
        }

        return back()->with('success', "Karyawan {$user->name} ({$validated['email']}) berhasil ditambahkan dengan role " . strtoupper($selectedRole->name ?? $selectedRole->slug) . ". Karyawan dapat langsung login di /login dengan email & password yang didaftarkan.");
    }

    /**
     * Update employee role in business workspace.
     */
    public function updateMemberRole(Request $request, \App\Models\BusinessMembership $member): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless(Context::hasPermission('users.manage'), 403, 'Anda tidak memiliki izin mengelola anggota tim.');

        if ($member->business_id !== $business->id) {
            abort(403);
        }

        $validated = $request->validate([
            'role_id' => ['required', 'uuid', 'exists:roles,id'],
        ]);

        $selectedRole = \App\Models\Role::where('id', $validated['role_id'])
            ->where(function ($query) use ($business): void {
                $query->whereNull('business_id')->orWhere('business_id', $business->id);
            })
            ->firstOrFail();

        if ($member->role === 'owner' && $selectedRole->slug !== 'owner' && $business->memberships()->where('role', 'owner')->count() <= 1) {
            return back()->with('error', 'Tidak dapat mengubah role satu-satunya Owner bisnis.');
        }

        $member->update(['role' => $selectedRole->slug, 'role_id' => $selectedRole->id]);

        return back()->with('success', "Role akses untuk anggota tim berhasil diubah menjadi " . strtoupper($selectedRole->name ?? $selectedRole->slug) . ".");
    }

    /**
     * Remove employee from business workspace.
     */
    public function destroyMember(\App\Models\BusinessMembership $member): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless(Context::hasPermission('users.manage'), 403, 'Anda tidak memiliki izin mengelola anggota tim.');

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
