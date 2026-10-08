<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\HasSlug;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use Auditable, HasFactory, HasSlug, HasUuid, SoftDeletes;

    public const ROUNDING_ROUND = 'ROUND';

    public const ROUNDING_CEIL = 'CEIL';

    public const ROUNDING_FLOOR = 'FLOOR';

    public const ROUNDING_ROUND_50 = 'ROUND_50';

    public const ROUNDING_ROUND_100 = 'ROUND_100';

    public const ROUNDING_ROUND_500 = 'ROUND_500';

    public const ROUNDING_ROUND_1000 = 'ROUND_1000';

    public const SCALE_UMKM = 'umkm';

    public const SCALE_CORPORATE = 'corporate';

    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'description',
        'phone',
        'email',
        'address',
        'tax_identification_number',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'currency',
        'rounding_strategy',
        'currency_precision',
        'industry_category',
        'template_code',
        'business_scale',
        'disabled_modules',
        'allow_negative_stock',
        'is_active',
        'suspended_reason',
        'suspended_at',
        'pos_supervisor_pin',
        'pos_max_cashier_discount_percent',
        'pos_require_pin_for_void',
        'pos_require_pin_for_refund',
        'pos_receipt_footer_note',
        'pos_receipt_wa_template',
        'pos_show_product_images',
        'pos_auto_send_kds',
        'pos_enable_tax',
        'pos_tax_percent',
        'pos_enable_service_charge',
        'pos_service_charge_percent',
        'timezone',
        'operating_hours',
    ];

    protected $hidden = [
        'pos_supervisor_pin',
    ];

    protected static function booted(): void
    {
        static::updating(function (Business $business): void {
            if ($business->isDirty('slug')) {
                $oldSlug = (string) $business->getOriginal('slug');
                $newSlug = (string) $business->slug;
                if (! empty($oldSlug) && ! empty($newSlug) && $oldSlug !== $newSlug) {
                    \App\Domain\Storage\TenantStorage::handleSlugRenamed($business, $oldSlug, $newSlug);
                }
            }
        });

        static::deleted(function (Business $business): void {
            app(\App\Domain\Storage\StorageTrackingService::class)->handleBusinessDeleted($business);
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'allow_negative_stock' => 'boolean',
            'is_active' => 'boolean',
            'suspended_at' => 'datetime',
            'currency_precision' => 'integer',
            'disabled_modules' => 'array',
            'operating_hours' => 'array',
            'pos_max_cashier_discount_percent' => 'float',
            'pos_require_pin_for_void' => 'boolean',
            'pos_require_pin_for_refund' => 'boolean',
            'pos_enable_tax' => 'boolean',
            'pos_show_product_images' => 'boolean',
            'pos_auto_send_kds' => 'boolean',
            'pos_tax_percent' => 'float',
            'pos_enable_service_charge' => 'boolean',
            'pos_service_charge_percent' => 'float',
        ];
    }

    public function getTimezone(): string
    {
        return \App\Support\TimezoneHelper::resolve($this);
    }

    public function getOperatingHours(): array
    {
        return \App\Support\TimezoneHelper::normalizeOperatingHours($this->operating_hours);
    }

    public function localNow(): \Carbon\Carbon
    {
        return \App\Support\TimezoneHelper::now($this);
    }

    public function localToday(): string
    {
        return \App\Support\TimezoneHelper::todayString($this);
    }

    public function isOperatingAt(?\Carbon\Carbon $localTime = null): bool
    {
        $time = $localTime ?? $this->localNow();
        return \App\Support\TimezoneHelper::isOperatingAt($this->getOperatingHours(), $time);
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (empty($this->logo_path)) {
            return null;
        }

        return \App\Domain\Storage\TenantStorage::url($this->logo_path);
    }

    public function getCurrencyCodeAttribute(): string
    {
        return $this->currency ?? 'IDR';
    }

    public function getCurrencySymbolAttribute(): string
    {
        return match ($this->currency) {
            'USD' => '$',
            'EUR' => '€',
            'SGD' => 'S$',
            'MYR' => 'RM',
            'JPY' => '¥',
            default => 'Rp',
        };
    }

    public function getCityAttribute(): ?string
    {
        if (empty($this->address)) {
            return null;
        }

        $parts = explode(',', $this->address);
        return trim(end($parts)) ?: $this->address;
    }

    public function getIndustryAttribute(): ?string
    {
        return $this->industry_category;
    }

    public function getStoreLogoUrlAttribute(): ?string
    {
        return $this->logo_url ?: $this->landingPage?->logo_url;
    }


    /**
     * Get users belonging to this business.
     *
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'business_users', 'business_id', 'user_id')
            ->using(BusinessMembership::class)
            ->withPivot(['id', 'role', 'role_id'])
            ->withTimestamps();
    }

    /**
     * Get the primary owner of this business.
     */
    public function owner(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            BusinessMembership::class,
            'business_id',
            'id',
            'id',
            'user_id'
        )->where('business_users.role', 'owner');
    }

    /**
     * Get memberships for this business.
     *
     * @return HasMany<BusinessMembership, $this>
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(BusinessMembership::class);
    }

    public function workShifts(): HasMany
    {
        return $this->hasMany(WorkShift::class);
    }

    public function employeeSchedules(): HasMany
    {
        return $this->hasMany(EmployeeSchedule::class);
    }

    public function subscription(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(BusinessSubscription::class)->latestOfMany();
    }

    public function landingPage(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(BusinessLandingPage::class);
    }

    public function commerceStoreSetting(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CommerceStoreSetting::class);
    }

    public function storeSetting(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CommerceStoreSetting::class);
    }

    public function whatsAppAccount(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(WhatsAppAccount::class);
    }

    public function whatsAppSession(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(WhatsAppSession::class);
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(CommercePaymentMethod::class)->orderBy('sort_order');
    }

    public function commercePaymentMethods(): HasMany
    {
        return $this->hasMany(CommercePaymentMethod::class)->orderBy('sort_order');
    }

    public function shippingRules(): HasMany
    {
        return $this->hasMany(CommerceShippingRule::class);
    }

    public function commerceOrders(): HasMany
    {
        return $this->hasMany(CommerceOrder::class);
    }

    public function payoutBankAccounts(): HasMany
    {
        return $this->hasMany(MerchantPayoutBankAccount::class);
    }

    public function storeEdcTerminals(): HasMany
    {
        return $this->hasMany(StoreEdcTerminal::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(BusinessSubscription::class);
    }

    public function subscriptionPayments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    public function bomHeaders(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(BomHeader::class, CostModel::class, 'business_id', 'cost_model_id');
    }

    public function aiTokenUsages(): HasMany
    {
        return $this->hasMany(AiTokenUsage::class);
    }

    public function aiTokenTopups(): HasMany
    {
        return $this->hasMany(AiTokenTopup::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function posOrders(): HasMany
    {
        return $this->hasMany(PosOrder::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function storageFiles(): HasMany
    {
        return $this->hasMany(StorageFile::class, 'business_id');
    }

    public function socialMediaAccounts(): HasMany
    {
        return $this->hasMany(SocialMediaAccount::class, 'business_id');
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class, 'business_id');
    }

    public function payrollItems(): HasMany
    {
        return $this->hasMany(PayrollItem::class, 'business_id');
    }

    public function employeeLoans(): HasMany
    {
        return $this->hasMany(EmployeeLoan::class, 'business_id');
    }

    /**
     * Determine if a functional module is enabled for this business.
     */
    public function isModuleEnabled(string $moduleKey): bool
    {
        $disabled = $this->disabled_modules;

        if ($disabled === null && ! empty($this->template_code)) {
            $disabled = \App\Domain\Template\ModuleRegistry::getDisabledModulesForTemplate($this->template_code);
        }

        $disabled = $disabled ?? [];

        return ! in_array($moduleKey, $disabled, true);
    }

    /**
     * Determine if a functional module is disabled for this business.
     */
    public function isModuleDisabled(string $moduleKey): bool
    {
        return ! $this->isModuleEnabled($moduleKey);
    }

    /**
     * Check whether a specific permission slug is permitted by active business modules.
     */
    public function isPermissionEnabled(string $permissionSlug): bool
    {
        $moduleKey = \App\Domain\Template\ModuleRegistry::getModuleForPermission($permissionSlug);

        // If permission doesn't belong to any toggleable module (core permission), it's always enabled
        if ($moduleKey === null) {
            return true;
        }

        return $this->isModuleEnabled($moduleKey);
    }

    /**
     * Enable a functional module for this business.
     */
    public function enableModule(string $moduleKey): void
    {
        $disabled = $this->disabled_modules;
        if ($disabled === null && ! empty($this->template_code)) {
            $disabled = \App\Domain\Template\ModuleRegistry::getDisabledModulesForTemplate($this->template_code);
        }
        $disabled = $disabled ?? [];
        $disabled = array_values(array_filter($disabled, fn (string $m): bool => $m !== $moduleKey));
        $this->update(['disabled_modules' => $disabled]);
    }

    /**
     * Disable a functional module for this business.
     */
    public function disableModule(string $moduleKey): void
    {
        $disabled = $this->disabled_modules;
        if ($disabled === null && ! empty($this->template_code)) {
            $disabled = \App\Domain\Template\ModuleRegistry::getDisabledModulesForTemplate($this->template_code);
        }
        $disabled = $disabled ?? [];
        if (! in_array($moduleKey, $disabled, true)) {
            $disabled[] = $moduleKey;
            $this->update(['disabled_modules' => $disabled]);
        }
    }

    /**
     * Canonical public storefront URL for this business (e.g. https://cooca.id/kopi-senja-utama).
     */
    public function getPublicUrlAttribute(): string
    {
        $slug = $this->slug ?: \Illuminate\Support\Str::slug($this->name);

        return url('/' . $slug);
    }

    /**
     * Alias for canonical public storefront URL.
     */
    public function getStorefrontUrlAttribute(): string
    {
        return $this->public_url;
    }

    public function isUmkm(): bool
    {
        return ($this->business_scale ?? self::SCALE_UMKM) === self::SCALE_UMKM;
    }

    public function isCorporate(): bool
    {
        return ($this->business_scale ?? '') === self::SCALE_CORPORATE;
    }

    public function isFoodIndustry(): bool
    {
        $code = strtolower((string) ($this->template_code ?? $this->industry_category ?? ''));
        if (! empty($code)) {
            if (str_starts_with($code, 'fnb_') || in_array($code, ['fnb', 'food', 'kuliner', 'restoran', 'cafe', 'resto', 'bakery', 'catering'], true)) {
                return true;
            }
            if (str_starts_with($code, 'service_') || str_starts_with($code, 'mfg_') || str_starts_with($code, 'retail_') || in_array($code, ['distributor_fmcg', 'agri_farming', 'workshop', 'otomotif', 'bengkel', 'laundry', 'pharmacy', 'apotek'], true)) {
                return false;
            }
        }

        $lowerName = strtolower($this->name ?? '');
        return str_contains($lowerName, 'kopi')
            || str_contains($lowerName, 'cafe')
            || str_contains($lowerName, 'resto')
            || str_contains($lowerName, 'warung');
    }

    /**
     * Determine if this business supports full F&B dine-in features (tables, reservations, KDS, order QR).
     */
    public function hasDineInFeature(): bool
    {
        if (! $this->isFoodIndustry()) {
            return false;
        }

        return $this->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_POS_DINEIN);
    }

    public function isPharmacy(): bool
    {
        $code = strtolower((string) ($this->template_code ?? $this->industry_category ?? ''));
        return $code === 'retail_pharmacy' || str_contains(strtolower($this->name), 'apotek') || str_contains(strtolower($this->name), 'farmasi');
    }

    public function isServiceSector(): bool
    {
        $code = strtolower((string) ($this->template_code ?? $this->industry_category ?? ''));
        $serviceCodes = [
            'service_workshop', 'service_barbershop', 'service_laundry',
            'service_autodetailing', 'service_agency', 'service_contractor',
            'service_event', 'bengkel', 'salon', 'laundry', 'carwash',
        ];
        return in_array($code, $serviceCodes, true);
    }

    public function isWorkshop(): bool
    {
        $code = strtolower((string) ($this->template_code ?? $this->industry_category ?? ''));
        return in_array($code, ['service_workshop', 'service_autodetailing', 'bengkel', 'otomotif', 'carwash', 'servis'], true)
            || str_contains(strtolower($this->name), 'bengkel')
            || str_contains(strtolower($this->name), 'motors')
            || str_contains(strtolower($this->name), 'garage')
            || str_contains(strtolower($this->name), 'servis');
    }

    public function isLaundry(): bool
    {
        $code = strtolower((string) ($this->template_code ?? $this->industry_category ?? ''));
        return in_array($code, ['service_laundry', 'laundry', 'cucian'], true)
            || str_contains(strtolower($this->name), 'laundry')
            || str_contains(strtolower($this->name), 'cucian');
    }

    public function isRetailSector(): bool
    {
        $code = strtolower((string) ($this->template_code ?? $this->industry_category ?? ''));
        return str_starts_with($code, 'retail_')
            || in_array($code, ['retail', 'reseller', 'toko', 'minimarket', 'kelontong', 'distributor_fmcg'], true);
    }

    public function isManufacturingSector(): bool
    {
        $code = strtolower((string) ($this->template_code ?? $this->industry_category ?? ''));
        return str_starts_with($code, 'mfg_')
            || in_array($code, ['manufacturing', 'pabrik', 'produksi', 'konveksi'], true);
    }

    public function approvalRules(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ApprovalRule::class);
    }

    public function approvalRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ApprovalRequest::class);
    }

    public function auditLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Check if the business has a configured supervisor PIN.
     */
    public function hasSupervisorPin(): bool
    {
        return ! empty($this->pos_supervisor_pin);
    }

    /**
     * Validate supervisor PIN using strict Bcrypt hash verification (zero plaintext fallback).
     */
    public function verifySupervisorPin(string $pin): bool
    {
        if ($pin === '' || empty($this->pos_supervisor_pin)) {
            return false;
        }

        return \Illuminate\Support\Facades\Hash::check($pin, (string) $this->pos_supervisor_pin);
    }

    /**
     * Check if terminal orders should automatically route to KDS without paper printing.
     */
    public function autoSendToKds(): bool
    {
        return (bool) ($this->pos_auto_send_kds ?? false);
    }

    public function aiProviderConfigs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AiProviderConfig::class);
    }

    public function aiTasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AiTask::class);
    }

    public function aiActionProposals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AiActionProposal::class);
    }

    public function aiWorkHistories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AiWorkHistory::class);
    }
}
