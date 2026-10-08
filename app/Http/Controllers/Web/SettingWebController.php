<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Billing\EntitlementService;
use App\Domain\Storage\TenantStorage;
use App\Domain\Template\BusinessTemplateService;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BusinessTypeTemplate;
use App\Models\CommerceStoreSetting;
use App\Models\Currency;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class SettingWebController extends Controller
{
    public function __construct(
        private readonly BusinessTemplateService $templateService = new BusinessTemplateService,
        private readonly EntitlementService $entitlementService = new EntitlementService
    ) {}

    /**
     * Show settings dashboard.
     */
    public function index(): View
    {
        $business = Context::requireBusiness();

        $templates = BusinessTypeTemplate::all();
        $currencies = Currency::all();
        $locations = Location::where('business_id', $business->id)->latest()->get();
        $branches = Location::where('business_id', $business->id)
            ->whereIn('type', ['outlet', 'store', 'central_kitchen'])
            ->with(['children'])
            ->latest()
            ->get();
        $allModules = \App\Domain\Template\ModuleRegistry::definitions();
        $timezones = \App\Support\TimezoneHelper::supportedTimezones();
        $operatingHours = $business->getOperatingHours();
        $currentTimezone = $business->getTimezone();
        $whatsappAccount = \App\Models\WhatsAppAccount::where('business_id', $business->id)
            ->where('status', 'active')
            ->first();

        return view('app.settings.index', compact(
            'business',
            'templates',
            'currencies',
            'locations',
            'branches',
            'allModules',
            'timezones',
            'operatingHours',
            'currentTimezone',
            'whatsappAccount'
        ));
    }

    /**
     * Update business settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255', Rule::unique('businesses', 'name')->ignore($business->id)],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
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
            'pos_supervisor_pin' => ['nullable', 'string', 'digits_between:4,8'],
            'pos_max_cashier_discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pos_require_pin_for_void' => ['nullable', 'boolean'],
            'pos_require_pin_for_refund' => ['nullable', 'boolean'],
            'pos_auto_send_kds' => ['nullable', 'boolean'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:100'],
            'bank_account_holder' => ['nullable', 'string', 'max:150'],
            'currency' => ['nullable', 'string', 'max:10'],
            'currency_code' => ['nullable', 'string', 'max:10'],
            'rounding_strategy' => ['nullable', 'string'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'operating_hours_json' => ['nullable', 'string'],
            'operating_hours' => ['nullable', 'array'],
        ]);

        $originalData = $business->getOriginal();
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
        if ($request->has('pos_auto_send_kds') || $request->input('_tab') === 'general') {
            $updateData['pos_auto_send_kds'] = $request->boolean('pos_auto_send_kds');
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
        if ($request->filled('pos_supervisor_pin')) {
            $updateData['pos_supervisor_pin'] = \Illuminate\Support\Facades\Hash::make($request->input('pos_supervisor_pin'));
        }
        if ($request->has('pos_max_cashier_discount_percent')) {
            $discountVal = $request->input('pos_max_cashier_discount_percent');
            $updateData['pos_max_cashier_discount_percent'] = ($discountVal !== null && $discountVal !== '') ? (float) $discountVal : null;
        }
        if ($request->input('_tab') === 'general') {
            $updateData['pos_require_pin_for_void'] = $request->boolean('pos_require_pin_for_void');
            $updateData['pos_require_pin_for_refund'] = $request->boolean('pos_require_pin_for_refund');
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
            $dir = TenantStorage::publicDir($business, TenantStorage::FOLDER_LOGO);
            $path = $request->file('logo')->store($dir, 'public');
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

        if ($request->filled('timezone')) {
            $tz = trim((string) $request->input('timezone'));
            if (\App\Support\TimezoneHelper::isValid($tz)) {
                $updateData['timezone'] = $tz;
            }
        }

        if ($request->has('operating_hours_json') && ! empty($request->input('operating_hours_json'))) {
            $decodedHours = json_decode((string) $request->input('operating_hours_json'), true);
            if (is_array($decodedHours)) {
                $normalizedHours = \App\Support\TimezoneHelper::normalizeOperatingHours($decodedHours);
                $updateData['operating_hours'] = $normalizedHours;
                \App\Models\BusinessLandingPage::where('business_id', $business->id)->update([
                    'operational_hours' => $normalizedHours,
                ]);
            }
        } elseif ($request->has('operating_hours') && is_array($request->input('operating_hours'))) {
            $normalizedHours = \App\Support\TimezoneHelper::normalizeOperatingHours($request->input('operating_hours'));
            $updateData['operating_hours'] = $normalizedHours;
            \App\Models\BusinessLandingPage::where('business_id', $business->id)->update([
                'operational_hours' => $normalizedHours,
            ]);
        }

        if (! empty($updateData)) {
            $business->update($updateData);

            // Immutable Audit Trail Logging for Sensitive Settings
            $sensitiveFields = [
                'bank_name',
                'bank_account_number',
                'bank_account_holder',
                'pos_supervisor_pin',
                'pos_max_cashier_discount_percent',
                'pos_require_pin_for_void',
                'pos_require_pin_for_refund',
                'pos_auto_send_kds',
                'rounding_strategy',
                'timezone',
            ];
            $changedSensitive = array_intersect_key($updateData, array_flip($sensitiveFields));
            if (! empty($changedSensitive)) {
                $maskedOld = [];
                $maskedNew = [];
                foreach ($changedSensitive as $k => $val) {
                    $oldVal = $originalData[$k] ?? null;
                    $newVal = $val;
                    if ($k === 'pos_supervisor_pin') {
                        $oldVal = ! empty($originalData['pos_supervisor_pin']) ? '••••••' : 'DEFAULT_PIN';
                        $newVal = '••••••';
                    }
                    $maskedOld[$k] = $oldVal;
                    $maskedNew[$k] = $newVal;
                }

                \App\Models\AuditLog::create([
                    'business_id' => $business->id,
                    'user_id' => $request->user()?->id,
                    'auditable_type' => \App\Models\Business::class,
                    'auditable_id' => $business->id,
                    'action' => 'settings.security_updated',
                    'risk_level' => (isset($changedSensitive['bank_account_number']) || isset($changedSensitive['pos_supervisor_pin'])) ? \App\Models\AuditLog::RISK_HIGH : \App\Models\AuditLog::RISK_MEDIUM,
                    'risk_reason' => 'Perubahan parameter rekening bank / otorisasi kasir / zona waktu usaha.',
                    'notes' => 'Memperbarui parameter pengaturan: ' . implode(', ', array_keys($changedSensitive)),
                    'ip_address' => $request->ip(),
                    'user_agent' => (string) $request->userAgent(),
                    'old_values' => $maskedOld,
                    'new_values' => $maskedNew,
                    'created_at' => now(),
                ]);
            }
        }

        $tab = $request->input('_tab', 'general');
        $msg = match ($tab) {
            'wa_receipt' => 'Template pesan WhatsApp struk berhasil diperbarui.',
            'operations' => 'Pengaturan zona waktu dan jam operasional usaha berhasil diperbarui.',
            default => 'Profil bisnis dan pengaturan berhasil diperbarui.',
        };

        return redirect()->route('settings.index', ['tab' => $tab])
            ->with('success', $msg)
            ->with('active_tab', $tab);
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

        // Sync template profile and default disabled modules
        $disabledModules = \App\Domain\Template\ModuleRegistry::getDisabledModulesForTemplate($template->code);
        $oldTemplate = $business->template_code;
        $oldCategory = $business->industry_category;

        $business->update([
            'template_code' => $template->code,
            'industry_category' => $template->industry_category,
            'disabled_modules' => $disabledModules,
        ]);

        \App\Models\AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $request->user()?->id,
            'auditable_type' => \App\Models\Business::class,
            'auditable_id' => $business->id,
            'action' => 'settings.template_applied',
            'risk_level' => \App\Models\AuditLog::RISK_MEDIUM,
            'risk_reason' => 'Penerapan template industri baru pada workspace usaha.',
            'notes' => "Menerapkan preset industri '{$template->name}' ({$template->code})",
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'old_values' => ['template_code' => $oldTemplate, 'industry_category' => $oldCategory],
            'new_values' => ['template_code' => $template->code, 'industry_category' => $template->industry_category],
            'created_at' => now(),
        ]);

        return redirect()->route('settings.index', ['tab' => 'templates'])
            ->with('success', "Template '{$template->name}' berhasil diterapkan ({$result['components_created']} komponen biaya ditambahkan). Modul fitur telah disesuaikan otomatis.");
    }

    /**
     * Update functional module toggles for the active business.
     */
    public function updateModules(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless(Context::isOwner(), 403, 'Hanya Business Owner yang berwenang mengatur modul fitur.');

        $allModuleDefs = \App\Domain\Template\ModuleRegistry::definitions();
        $allModuleKeys = array_keys($allModuleDefs);

        // Check if single module toggle or bulk update
        if ($request->has('module_key')) {
            $validated = $request->validate([
                'module_key' => ['required', 'string', 'in:' . implode(',', $allModuleKeys)],
                'enabled' => ['required', 'boolean'],
            ]);

            $moduleKey = $validated['module_key'];
            $isEnabled = (bool) $validated['enabled'];
            $moduleName = $allModuleDefs[$moduleKey]['name'] ?? $moduleKey;

            $oldDisabled = $business->disabled_modules;
            if ($oldDisabled === null && ! empty($business->template_code)) {
                $oldDisabled = \App\Domain\Template\ModuleRegistry::getDisabledModulesForTemplate($business->template_code);
            }
            $oldDisabled = $oldDisabled ?? [];

            if ($isEnabled) {
                $business->enableModule($moduleKey);
            } else {
                $business->disableModule($moduleKey);
            }

            $business->refresh();
            $newDisabled = $business->disabled_modules ?? [];

            \App\Models\AuditLog::create([
                'business_id' => $business->id,
                'user_id' => $request->user()?->id,
                'auditable_type' => \App\Models\Business::class,
                'auditable_id' => $business->id,
                'action' => 'settings.modules_updated',
                'risk_level' => \App\Models\AuditLog::RISK_MEDIUM,
                'risk_reason' => 'Perubahan konfigurasi modul fitur (' . $moduleName . ') dan visibilitas sidebar aplikasi.',
                'notes' => ($isEnabled ? 'Mengaktifkan' : 'Menonaktifkan') . " modul {$moduleName} ({$moduleKey})",
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'old_values' => ['disabled_modules' => $oldDisabled],
                'new_values' => ['disabled_modules' => $newDisabled],
                'created_at' => now(),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                $statusText = $isEnabled ? 'diaktifkan' : 'dinonaktifkan';

                return response()->json([
                    'success' => true,
                    'message' => "Modul {$moduleName} berhasil {$statusText}.",
                    'module_key' => $moduleKey,
                    'is_enabled' => $isEnabled,
                    'disabled_modules' => $newDisabled,
                    'enabled_count' => count($allModuleKeys) - count($newDisabled),
                    'total_count' => count($allModuleKeys),
                ]);
            }

            return back()->with('success', "Modul {$moduleName} berhasil diperbarui.")->with('active_tab', 'modules');
        }

        $validated = $request->validate([
            'enabled_modules' => ['nullable', 'array'],
            'enabled_modules.*' => ['string', 'in:' . implode(',', $allModuleKeys)],
        ]);

        $enabled = $validated['enabled_modules'] ?? [];
        $disabled = array_values(array_diff($allModuleKeys, $enabled));
        $oldDisabled = $business->disabled_modules ?? [];

        $business->update([
            'disabled_modules' => $disabled,
        ]);

        \App\Models\AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $request->user()?->id,
            'auditable_type' => \App\Models\Business::class,
            'auditable_id' => $business->id,
            'action' => 'settings.modules_updated',
            'risk_level' => \App\Models\AuditLog::RISK_MEDIUM,
            'risk_reason' => 'Perubahan konfigurasi modul fitur dan visibilitas sidebar aplikasi.',
            'notes' => 'Memperbarui modul bisnis. Modul dinonaktifkan: ' . count($disabled) . ' dari ' . count($allModuleKeys),
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'old_values' => ['disabled_modules' => $oldDisabled],
            'new_values' => ['disabled_modules' => $disabled],
            'created_at' => now(),
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Konfigurasi modul fitur bisnis Anda berhasil diperbarui.',
                'disabled_modules' => $disabled,
                'enabled_count' => count($allModuleKeys) - count($disabled),
                'total_count' => count($allModuleKeys),
            ]);
        }

        return back()->with('success', 'Konfigurasi modul fitur bisnis Anda berhasil diperbarui.')->with('active_tab', 'modules');
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
            return back()->with('error', 'Paket Free Plan dibatasi untuk 1 pengguna (Solo Owner). Silakan upgrade ke Cooca untuk menambahkan karyawan tanpa batas.');
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

    /**
     * Simpan cabang / outlet baru dari Pengaturan Bisnis.
     */
    public function storeBranch(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless(Context::hasPermission('settings.edit') || Context::isAdminOrOwner(), 403);

        $validated = $request->validate([
            'name'                   => ['required', 'string', 'max:100'],
            'type'                   => ['nullable', 'string', 'in:outlet,store,central_kitchen'],
            'code'                   => ['nullable', 'string', 'max:50'],
            'phone'                  => ['nullable', 'string', 'max:50'],
            'address'                => ['nullable', 'string', 'max:500'],
            'province'               => ['nullable', 'string', 'max:100'],
            'city'                   => ['nullable', 'string', 'max:100'],
            'district'               => ['nullable', 'string', 'max:100'],
            'village'                => ['nullable', 'string', 'max:100'],
            'postal_code'            => ['nullable', 'string', 'max:20'],
            'biteship_area_id'       => ['nullable', 'string', 'max:100'],
            'latitude'               => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'              => ['nullable', 'numeric', 'between:-180,180'],
            'geofence_radius_meters' => ['nullable', 'integer', 'min:10', 'max:10000'],
            'is_primary'             => ['nullable', 'boolean'],
            'timezone_mode'          => ['nullable', 'string', 'in:inherit,custom'],
            'timezone'               => ['nullable', 'string', 'max:50'],
            'operating_hours_mode'   => ['nullable', 'string', 'in:inherit,custom'],
            'operating_hours_json'   => ['nullable', 'string'],
            'operating_hours'        => ['nullable', 'array'],
            'is_online_fulfillment'  => ['nullable', 'boolean'],
            'allow_storefront_pickup'=> ['nullable', 'boolean'],
        ]);

        $locationType = $validated['type'] ?? 'outlet';
        if (! $this->entitlementService->canCreateLocation($business, $locationType)) {
            $label = __('settings.branch_type_outlet');
            $quotaMsg = __('warehouse.messages.quota_exceeded', ['type' => $label]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $quotaMsg,
                ], 422);
            }

            return redirect()->route('settings.index', ['tab' => 'branches'])
                ->withInput()
                ->with('error', $quotaMsg);
        }

        // Generate unique slug
        $baseSlug = Str::slug($validated['name']);
        $slug     = $baseSlug;
        $counter  = 1;
        while (Location::where('business_id', $business->id)->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $geofenceRadius = $validated['geofence_radius_meters'] ?? 100;

        $isPrimary = $request->boolean('is_primary');
        if (!Location::where('business_id', $business->id)->whereIn('type', ['outlet', 'store'])->exists()) {
            $isPrimary = true;
        }

        if ($isPrimary) {
            Location::where('business_id', $business->id)->update(['is_primary' => false]);
        }

        $tzMode = $validated['timezone_mode'] ?? 'inherit';
        $locTimezone = null;
        if ($tzMode === 'custom' && ! empty($validated['timezone']) && \App\Support\TimezoneHelper::isValid($validated['timezone'])) {
            $locTimezone = trim((string) $validated['timezone']);
        }

        $ohMode = $validated['operating_hours_mode'] ?? 'inherit';
        $locOperatingHours = null;
        if ($ohMode === 'custom') {
            if (! empty($request->input('operating_hours_json'))) {
                $decoded = json_decode((string) $request->input('operating_hours_json'), true);
                if (is_array($decoded)) {
                    $locOperatingHours = \App\Support\TimezoneHelper::normalizeOperatingHours($decoded);
                }
            } elseif (is_array($request->input('operating_hours'))) {
                $locOperatingHours = \App\Support\TimezoneHelper::normalizeOperatingHours($request->input('operating_hours'));
            }
        }

        $branch = Location::create([
            'business_id'             => $business->id,
            'parent_id'               => null,
            'name'                    => $validated['name'],
            'slug'                    => $slug,
            'type'                    => $locationType,
            'code'                    => $validated['code'] ?? null,
            'phone'                   => $validated['phone'] ?? null,
            'address'                 => $validated['address'] ?? null,
            'province'                => $validated['province'] ?? null,
            'city'                    => $validated['city'] ?? null,
            'district'                => $validated['district'] ?? null,
            'village'                 => $validated['village'] ?? null,
            'postal_code'             => $validated['postal_code'] ?? null,
            'biteship_area_id'        => $validated['biteship_area_id'] ?? null,
            'latitude'                => $validated['latitude'] ?? null,
            'longitude'               => $validated['longitude'] ?? null,
            'geofence_radius_meters'  => (int) $geofenceRadius,
            'timezone_mode'           => $tzMode,
            'timezone'                => $locTimezone,
            'operating_hours_mode'    => $ohMode,
            'operating_hours'         => $locOperatingHours,
            'is_online_fulfillment'   => $request->boolean('is_online_fulfillment', true),
            'allow_storefront_pickup' => $request->boolean('allow_storefront_pickup', true),
            'is_primary'              => $isPrimary,
            'is_active'               => true,
        ]);

        if ($isPrimary) {
            $storeSetting = CommerceStoreSetting::firstOrCreate(['business_id' => $business->id]);
            $storeSetting->update([
                'origin_location_id' => $branch->id,
                'origin_area_id'     => $branch->biteship_area_id ?? $storeSetting->origin_area_id,
                'origin_address'     => $branch->address ?? $storeSetting->origin_address,
                'origin_postal_code' => $branch->postal_code ?? $storeSetting->origin_postal_code,
                'origin_latitude'    => $branch->latitude ?? $storeSetting->origin_latitude,
                'origin_longitude'   => $branch->longitude ?? $storeSetting->origin_longitude,
            ]);
        }

        if (class_exists(\App\Models\AuditLog::class)) {
            \App\Models\AuditLog::create([
                'business_id'    => $business->id,
                'user_id'        => $request->user()?->id,
                'auditable_type' => \App\Models\Location::class,
                'auditable_id'   => $branch->id,
                'action'         => 'settings.branch_created',
                'risk_level'     => \App\Models\AuditLog::RISK_MEDIUM,
                'risk_reason'    => 'Penambahan cabang toko baru via Settings Hub.',
                'notes'          => "Cabang '{$branch->name}' berhasil ditambahkan via Settings Hub",
                'ip_address'     => $request->ip(),
                'user_agent'     => (string) $request->userAgent(),
                'old_values'     => [],
                'new_values'     => $branch->toArray(),
                'created_at'     => now(),
            ]);
        }

        $successMsg = __('settings.branch_created_success', ['name' => $branch->name]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'branch'  => $branch,
            ], 201);
        }

        return redirect()->route('settings.index', ['tab' => 'branches'])
            ->with('success', $successMsg);
    }

    /**
     * Perbarui data cabang / outlet dari Pengaturan Bisnis.
     */
    public function updateBranch(Request $request, Location $location): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless(Context::hasPermission('settings.edit') || Context::isAdminOrOwner(), 403);
        abort_unless($location->business_id === $business->id, 403);

        $validated = $request->validate([
            'name'                   => ['required', 'string', 'max:100'],
            'code'                   => ['nullable', 'string', 'max:50'],
            'phone'                  => ['nullable', 'string', 'max:50'],
            'address'                => ['nullable', 'string', 'max:500'],
            'province'               => ['nullable', 'string', 'max:100'],
            'city'                   => ['nullable', 'string', 'max:100'],
            'district'               => ['nullable', 'string', 'max:100'],
            'village'                => ['nullable', 'string', 'max:100'],
            'postal_code'            => ['nullable', 'string', 'max:20'],
            'biteship_area_id'       => ['nullable', 'string', 'max:100'],
            'latitude'               => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'              => ['nullable', 'numeric', 'between:-180,180'],
            'geofence_radius_meters' => ['nullable', 'integer', 'min:10', 'max:10000'],
            'timezone_mode'          => ['nullable', 'string', 'in:inherit,custom'],
            'timezone'               => ['nullable', 'string', 'max:50'],
            'operating_hours_mode'   => ['nullable', 'string', 'in:inherit,custom'],
            'operating_hours_json'   => ['nullable', 'string'],
            'operating_hours'        => ['nullable', 'array'],
            'is_active'              => ['nullable', 'boolean'],
            'is_primary'             => ['nullable', 'boolean'],
            'is_online_fulfillment'  => ['nullable', 'boolean'],
            'allow_storefront_pickup'=> ['nullable', 'boolean'],
        ]);

        $isPrimary = $request->boolean('is_primary');
        if ($isPrimary) {
            Location::where('business_id', $business->id)->where('id', '!=', $location->id)->update(['is_primary' => false]);
        }

        $oldValues = $location->toArray();

        $updateData = [
            'name'                    => $validated['name'],
            'code'                    => $validated['code'] ?? null,
            'phone'                   => $validated['phone'] ?? null,
            'address'                 => $validated['address'] ?? null,
            'province'                => $validated['province'] ?? $location->province,
            'city'                    => $validated['city'] ?? $location->city,
            'district'                => $validated['district'] ?? $location->district,
            'village'                 => $validated['village'] ?? $location->village,
            'postal_code'             => $validated['postal_code'] ?? $location->postal_code,
            'biteship_area_id'        => $validated['biteship_area_id'] ?? $location->biteship_area_id,
            'is_active'               => $request->has('is_active') ? $request->boolean('is_active') : $location->is_active,
            'is_online_fulfillment'   => $request->boolean('is_online_fulfillment', true),
            'allow_storefront_pickup' => $request->boolean('allow_storefront_pickup', true),
            'is_primary'              => $isPrimary ?: $location->is_primary,
        ];

        if (array_key_exists('latitude', $validated)) {
            $updateData['latitude'] = $validated['latitude'];
        }
        if (array_key_exists('longitude', $validated)) {
            $updateData['longitude'] = $validated['longitude'];
        }
        if (isset($validated['geofence_radius_meters'])) {
            $updateData['geofence_radius_meters'] = (int) $validated['geofence_radius_meters'];
        }

        if ($request->has('timezone_mode')) {
            $tzMode = $validated['timezone_mode'] ?? 'inherit';
            $updateData['timezone_mode'] = $tzMode;
            if ($tzMode === 'custom' && ! empty($validated['timezone']) && \App\Support\TimezoneHelper::isValid($validated['timezone'])) {
                $updateData['timezone'] = trim((string) $validated['timezone']);
            } else {
                $updateData['timezone'] = null;
            }
        }

        if ($request->has('operating_hours_mode')) {
            $ohMode = $validated['operating_hours_mode'] ?? 'inherit';
            $updateData['operating_hours_mode'] = $ohMode;
            if ($ohMode === 'custom') {
                if (! empty($request->input('operating_hours_json'))) {
                    $decoded = json_decode((string) $request->input('operating_hours_json'), true);
                    if (is_array($decoded)) {
                        $updateData['operating_hours'] = \App\Support\TimezoneHelper::normalizeOperatingHours($decoded);
                    }
                } elseif (is_array($request->input('operating_hours'))) {
                    $updateData['operating_hours'] = \App\Support\TimezoneHelper::normalizeOperatingHours($request->input('operating_hours'));
                }
            } else {
                $updateData['operating_hours'] = null;
            }
        }

        $location->update($updateData);

        if ($location->is_primary) {
            $storeSetting = CommerceStoreSetting::firstOrCreate(['business_id' => $business->id]);
            $storeSetting->update([
                'origin_location_id' => $location->id,
                'origin_area_id'     => $location->biteship_area_id ?? $storeSetting->origin_area_id,
                'origin_address'     => $location->address ?? $storeSetting->origin_address,
                'origin_postal_code' => $location->postal_code ?? $storeSetting->origin_postal_code,
                'origin_latitude'    => $location->latitude ?? $storeSetting->origin_latitude,
                'origin_longitude'   => $location->longitude ?? $storeSetting->origin_longitude,
            ]);
        }

        if (class_exists(\App\Models\AuditLog::class)) {
            \App\Models\AuditLog::create([
                'business_id'    => $business->id,
                'user_id'        => $request->user()?->id,
                'auditable_type' => \App\Models\Location::class,
                'auditable_id'   => $location->id,
                'action'         => 'settings.branch_updated',
                'risk_level'     => \App\Models\AuditLog::RISK_MEDIUM,
                'risk_reason'    => 'Pembaruan data cabang toko via Settings Hub.',
                'notes'          => "Cabang '{$location->name}' diperbarui via Settings Hub",
                'ip_address'     => $request->ip(),
                'user_agent'     => (string) $request->userAgent(),
                'old_values'     => $oldValues,
                'new_values'     => $location->toArray(),
                'created_at'     => now(),
            ]);
        }

        $successMsg = __('settings.branch_updated_success', ['name' => $location->name]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'branch'  => $location,
            ]);
        }

        return redirect()->route('settings.index', ['tab' => 'branches'])
            ->with('success', $successMsg);
    }

    /**
     * Hapus atau nonaktifkan cabang / outlet dari Pengaturan Bisnis.
     */
    public function destroyBranch(Location $location): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless(Context::hasPermission('settings.edit') || Context::isAdminOrOwner(), 403);
        abort_unless($location->business_id === $business->id, 403);

        if ($location->is_primary) {
            $msg = __('settings.branch_cannot_delete_primary');
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        // Cek apakah cabang ini memiliki gudang yang masih terhubung (child warehouses)
        $childWarehousesCount = $location->children()->count();
        if ($childWarehousesCount > 0) {
            $msg = __('settings.branch_cannot_delete_has_warehouses', ['count' => $childWarehousesCount]);
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        // Cek stok langsung di lokasi ini (jika ada)
        $hasStock = InventoryStock::where('business_id', $business->id)
            ->where('location_id', $location->id)
            ->where('quantity', '>', 0)
            ->exists();

        if ($hasStock) {
            $msg = __('warehouse.cannot_delete_has_stock');
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        // Non-destructive check (jika ada transaksi pos, riwayat, absensi, dll)
        $hasHistory = \App\Models\StockMovement::where('location_id', $location->id)->exists()
            || \App\Models\GoodsReceipt::where('location_id', $location->id)->exists()
            || \App\Models\PosOrder::where('location_id', $location->id)->exists()
            || \App\Models\StockTransfer::where('source_location_id', $location->id)->orWhere('destination_location_id', $location->id)->exists()
            || \App\Models\StockOpname::where('location_id', $location->id)->exists()
            || \App\Models\StockAdjustment::where('location_id', $location->id)->exists()
            || \App\Models\Attendance::where('location_id', $location->id)->exists();

        if ($hasHistory) {
            $location->update(['is_active' => false]);
            $msg = __('settings.branch_deactivated_due_to_history', ['name' => $location->name]);

            if (class_exists(\App\Models\AuditLog::class)) {
                \App\Models\AuditLog::create([
                    'business_id'    => $business->id,
                    'user_id'        => request()->user()?->id,
                    'auditable_type' => \App\Models\Location::class,
                    'auditable_id'   => $location->id,
                    'action'         => 'settings.branch_deactivated',
                    'risk_level'     => \App\Models\AuditLog::RISK_MEDIUM,
                    'risk_reason'    => 'Cabang dinonaktifkan karena memiliki riwayat operasional tersimpan.',
                    'notes'          => "Cabang '{$location->name}' dinonaktifkan karena memiliki rekam jejak historis",
                    'ip_address'     => request()->ip(),
                    'user_agent'     => (string) request()->userAgent(),
                    'old_values'     => ['is_active' => true],
                    'new_values'     => ['is_active' => false],
                    'created_at'     => now(),
                ]);
            }

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }
            return redirect()->route('settings.index', ['tab' => 'branches'])->with('success', $msg);
        }

        $locName = $location->name;
        $locId = $location->id;
        $location->delete();

        if (class_exists(\App\Models\AuditLog::class)) {
            \App\Models\AuditLog::create([
                'business_id'    => $business->id,
                'user_id'        => request()->user()?->id,
                'auditable_type' => \App\Models\Location::class,
                'auditable_id'   => $locId,
                'action'         => 'settings.branch_deleted',
                'risk_level'     => \App\Models\AuditLog::RISK_HIGH,
                'risk_reason'    => 'Penghapusan cabang toko permanen dari sistem.',
                'notes'          => "Cabang '{$locName}' berhasil dihapus permanen",
                'ip_address'     => request()->ip(),
                'user_agent'     => (string) request()->userAgent(),
                'old_values'     => ['name' => $locName],
                'new_values'     => [],
                'created_at'     => now(),
            ]);
        }

        $msg = __('settings.branch_deleted_success', ['name' => $locName]);
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }
        return redirect()->route('settings.index', ['tab' => 'branches'])->with('success', $msg);
    }
}
