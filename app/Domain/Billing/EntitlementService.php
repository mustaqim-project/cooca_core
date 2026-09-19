<?php

declare(strict_types=1);

namespace App\Domain\Billing;

use App\Models\Admin;
use App\Models\AiTokenUsage;
use App\Models\BillingPackage;
use App\Models\BomHeader;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\BusinessSubscription;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\AiTokenTopup;
use App\Models\Location;
use App\Models\Material;
use App\Models\OwnerStorageTopup;
use App\Domain\Storage\OwnerStorageQuotaService;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PosOrder;
use App\Models\QuotaMonthlyUsage;
use App\Models\SubscriptionPayment;
use App\Models\Supplier;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class EntitlementService
{
    // ── 4-Tier Quota Mapping (§Blueprint v2.3) ────────────────────────────────
    public const TIER_PRICES = [
        BusinessSubscription::TIER_FREE => [
            'monthly' => 0,
            'annual'  => 0,
        ],
        BusinessSubscription::TIER_STANDARD => [
            'monthly' => 29000,
            'annual'  => 290000,
        ],
        BusinessSubscription::TIER_PREMIUM => [
            'monthly' => 89000,
            'annual'  => 890000,
        ],
        BusinessSubscription::TIER_PRESTIGE => [
            'monthly' => 199000,
            'annual'  => 1990000,
        ],
    ];

    public const TIER_PRODUCT_LIMITS = [
        BusinessSubscription::TIER_FREE => 10,
        BusinessSubscription::TIER_STANDARD => 100, // 100 Item per User Spec
        BusinessSubscription::TIER_PREMIUM => null, // Unlimited
        BusinessSubscription::TIER_PRESTIGE => null, // Unlimited
    ];

    public const TIER_RECIPE_LIMITS = [
        BusinessSubscription::TIER_FREE => 3,
        BusinessSubscription::TIER_STANDARD => 20, // 20 Resep per User Spec
        BusinessSubscription::TIER_PREMIUM => null,
        BusinessSubscription::TIER_PRESTIGE => null,
    ];

    public const TIER_MATERIAL_LIMITS = [
        BusinessSubscription::TIER_FREE => 10,
        BusinessSubscription::TIER_STANDARD => 30,
        BusinessSubscription::TIER_PREMIUM => null,
        BusinessSubscription::TIER_PRESTIGE => null,
    ];

    public const TIER_CUSTOMER_LIMITS = [
        BusinessSubscription::TIER_FREE => 15,
        BusinessSubscription::TIER_STANDARD => 100,
        BusinessSubscription::TIER_PREMIUM => 1000,
        BusinessSubscription::TIER_PRESTIGE => null,
    ];

    public const TIER_SUPPLIER_LIMITS = [
        BusinessSubscription::TIER_FREE => 2,
        BusinessSubscription::TIER_STANDARD => 5,
        BusinessSubscription::TIER_PREMIUM => null, // Unlimited per Blueprint v2.3
        BusinessSubscription::TIER_PRESTIGE => null,
    ];

    public const TIER_OUTLET_LIMITS = [
        BusinessSubscription::TIER_FREE => 1,
        BusinessSubscription::TIER_STANDARD => 2,
        BusinessSubscription::TIER_PREMIUM => 5,
        BusinessSubscription::TIER_PRESTIGE => null,
    ];

    public const TIER_WAREHOUSE_LIMITS = [
        BusinessSubscription::TIER_FREE => 1,
        BusinessSubscription::TIER_STANDARD => 2,
        BusinessSubscription::TIER_PREMIUM => 5,
        BusinessSubscription::TIER_PRESTIGE => null,
    ];

    public const TIER_TOTAL_LOCATION_LIMITS = [
        BusinessSubscription::TIER_FREE => 2, // 1 Toko + 1 Gudang
        BusinessSubscription::TIER_STANDARD => 2, // 2 Lokasi
        BusinessSubscription::TIER_PREMIUM => 5, // 5 Lokasi
        BusinessSubscription::TIER_PRESTIGE => null, // Unlimited
    ];

    public const TIER_USER_PER_OWNER_LIMITS = [
        BusinessSubscription::TIER_FREE => 1,
        BusinessSubscription::TIER_STANDARD => 3,
        BusinessSubscription::TIER_PREMIUM => 10,
        BusinessSubscription::TIER_PRESTIGE => null,
    ];

    public const TIER_BUSINESS_LIMITS = [
        BusinessSubscription::TIER_FREE => 1,
        BusinessSubscription::TIER_STANDARD => 1,
        BusinessSubscription::TIER_PREMIUM => 3,
        BusinessSubscription::TIER_PRESTIGE => null,
    ];

    public const TIER_MONTHLY_POS_LIMITS = [
        BusinessSubscription::TIER_FREE => 30,
        BusinessSubscription::TIER_STANDARD => 1000, // 1.000 / bln per User Spec
        BusinessSubscription::TIER_PREMIUM => null,
        BusinessSubscription::TIER_PRESTIGE => null,
    ];

    public const TIER_MONTHLY_INVOICE_LIMITS = [
        BusinessSubscription::TIER_FREE => 3,
        BusinessSubscription::TIER_STANDARD => 15, // 15 faktur per bulan per Blueprint v2.3
        BusinessSubscription::TIER_PREMIUM => null,
        BusinessSubscription::TIER_PRESTIGE => null,
    ];

    public const TIER_MONTHLY_PO_LIMITS = [
        BusinessSubscription::TIER_FREE => 3,
        BusinessSubscription::TIER_STANDARD => 15, // 15 PO per bulan per Blueprint v2.3
        BusinessSubscription::TIER_PREMIUM => null,
        BusinessSubscription::TIER_PRESTIGE => null,
    ];

    public const TIER_WHATSAPP_LIMITS = [
        BusinessSubscription::TIER_FREE => 10,
        BusinessSubscription::TIER_STANDARD => 50,
        BusinessSubscription::TIER_PREMIUM => 200, // 200 Pesan per User Spec
        BusinessSubscription::TIER_PRESTIGE => 1000,
    ];

    public const TIER_SOCIAL_LIMITS = [
        BusinessSubscription::TIER_FREE => 3,
        BusinessSubscription::TIER_STANDARD => 10,
        BusinessSubscription::TIER_PREMIUM => 30,
        BusinessSubscription::TIER_PRESTIGE => null,
    ];

    public const TIER_TABLE_LIMITS = [
        BusinessSubscription::TIER_FREE => 0,
        BusinessSubscription::TIER_STANDARD => 5,
        BusinessSubscription::TIER_PREMIUM => null,
        BusinessSubscription::TIER_PRESTIGE => null,
    ];

    // ── Business-level Master Data Limits (Legacy Backward Compatibility) ─────
    public const FREE_PRODUCT_LIMIT           = 10;
    public const FREE_RECIPE_LIMIT            = 3;
    public const FREE_MATERIAL_LIMIT          = 10;
    public const FREE_CUSTOMER_LIMIT          = 15;
    public const FREE_SUPPLIER_LIMIT          = 2;
    public const FREE_OUTLET_LIMIT            = 1;
    public const FREE_WAREHOUSE_LIMIT         = 1;

    // ── Business-level Monthly Transaction Quotas (Legacy Backward Compatibility)
    public const FREE_INVOICE_MONTHLY_LIMIT   = 3;
    public const FREE_PO_MONTHLY_LIMIT        = 3;
    public const FREE_POS_MONTHLY_LIMIT       = 30;
    public const FREE_SOCIAL_POST_MONTHLY_LIMIT = 3;
    public const FREE_WHATSAPP_MONTHLY_LIMIT  = 10;

    // ── Owner-level Limits ────────────────────────────────────────────────────
    public const FREE_BUSINESS_LIMIT          = 1;
    public const FREE_USER_PER_OWNER_LIMIT    = 1;

    // ── Plan Pricing & AI ─────────────────────────────────────────────────────
    public const DEFAULT_CORE_AI_MONTHLY_TOKENS = 0;
    public const DEFAULT_MONTHLY_PRICE        = 49_000.0;
    public const DEFAULT_ANNUAL_PRICE         = 490_000.0;
    public const DEFAULT_SOCIAL_MONTHLY_PRICE = 89_000.0;

    public const PRICE_STANDARD_MONTHLY = 29_000.0;
    public const PRICE_STANDARD_ANNUAL  = 290_000.0;
    public const PRICE_PREMIUM_MONTHLY  = 89_000.0;
    public const PRICE_PREMIUM_ANNUAL   = 890_000.0;
    public const PRICE_PRESTIGE_MONTHLY = 199_000.0;
    public const PRICE_PRESTIGE_ANNUAL  = 1_990_000.0;

    /**
     * Get configured monthly price for Core plan (from BillingPackage subscription catalog).
     */
    public function getMonthlyPrice(): float
    {
        $setting = SystemSetting::get('subscription_price_monthly');
        if ($setting !== null && is_numeric($setting)) {
            return (float) $setting;
        }

        $pkgPrice = BillingPackage::where('type', BillingPackage::TYPE_SUBSCRIPTION)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('code', 'core-monthly')
                    ->orWhere('duration_days', '<=', 31);
            })
            ->orderBy('sort_order')
            ->value('price');

        if ($pkgPrice !== null && is_numeric($pkgPrice)) {
            return (float) $pkgPrice;
        }

        return self::DEFAULT_MONTHLY_PRICE;
    }

    /**
     * Get configured annual price for Core plan (from BillingPackage subscription catalog).
     */
    public function getAnnualPrice(): float
    {
        $setting = SystemSetting::get('subscription_price_annual');
        if ($setting !== null && is_numeric($setting)) {
            return (float) $setting;
        }

        $pkgPrice = BillingPackage::where('type', BillingPackage::TYPE_SUBSCRIPTION)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('code', 'core-annual')
                    ->orWhere('duration_days', '>=', 360);
            })
            ->orderBy('sort_order')
            ->value('price');

        if ($pkgPrice !== null && is_numeric($pkgPrice)) {
            return (float) $pkgPrice;
        }

        return self::DEFAULT_ANNUAL_PRICE;
    }

    /**
     * Get configured monthly AI token allowance for Core plan (No free tokens, strictly top-up).
     */
    public function getCoreMonthlyAiTokens(): int
    {
        $value = SystemSetting::get('subscription_ai_tokens_monthly');

        return $value !== null && is_numeric($value) ? (int) $value : self::DEFAULT_CORE_AI_MONTHLY_TOKENS;
    }

    /**
     * Return active admin-managed billing packages for checkout.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, BillingPackage>
     */
    public function activePackages(string $type): \Illuminate\Database\Eloquent\Collection
    {
        return BillingPackage::active()->where('type', $type)->orderBy('sort_order')->orderBy('price')->get();
    }

    /**
     * Get or initialize subscription for business.
     */
    public function getSubscription(Business $business): BusinessSubscription
    {
        /** @var BusinessSubscription $sub */
        $sub = BusinessSubscription::firstOrCreate(
            ['business_id' => $business->id],
            [
                'plan_code' => BusinessSubscription::PLAN_FREE,
                'price' => 0,
                'status' => BusinessSubscription::STATUS_ACTIVE,
                'starts_at' => Carbon::now(),
                'ai_tokens_monthly_allowance' => 0,
                'ai_tokens_remaining' => 0,
                'last_token_reset_at' => Carbon::today()->toDateString(),
            ]
        );

        return $sub;
    }

    /**
     * Get Product limit for business. Null means unlimited.
     */
    public function getProductLimit(Business $business): ?int
    {
        $tier = $this->getSubscription($business)->getTier();
        return array_key_exists($tier, self::TIER_PRODUCT_LIMITS) ? self::TIER_PRODUCT_LIMITS[$tier] : self::FREE_PRODUCT_LIMIT;
    }

    /**
     * Can business create a new product?
     */
    public function canCreateProduct(Business $business): bool
    {
        $limit = $this->getProductLimit($business);
        if ($limit === null) {
            return true;
        }

        $currentProducts = Product::where('business_id', $business->id)->count();

        return $currentProducts < $limit;
    }

    /**
     * Get Recipe limit for business. Null means unlimited.
     */
    public function getRecipeLimit(Business $business): ?int
    {
        $tier = $this->getSubscription($business)->getTier();
        return array_key_exists($tier, self::TIER_RECIPE_LIMITS) ? self::TIER_RECIPE_LIMITS[$tier] : self::FREE_RECIPE_LIMIT;
    }

    /**
     * Can business create a new recipe (BOM)?
     */
    public function canCreateRecipe(Business $business): bool
    {
        $limit = $this->getRecipeLimit($business);
        if ($limit === null) {
            return true;
        }

        $currentRecipes = BomHeader::whereHas('costModel', function ($q) use ($business) {
            $q->where('business_id', $business->id);
        })->count();

        return $currentRecipes < $limit;
    }

    /**
     * Can business issue a new sales invoice this month?
     */
    public function canCreateInvoiceThisMonth(Business $business): bool
    {
        $tier = $this->getSubscription($business)->getTier();
        $limit = array_key_exists($tier, self::TIER_MONTHLY_INVOICE_LIMITS) ? self::TIER_MONTHLY_INVOICE_LIMITS[$tier] : null;
        if ($limit === null) {
            return true;
        }

        $currentMonthInvoices = Invoice::where('business_id', $business->id)
            ->whereMonth('invoice_date', Carbon::today()->month)
            ->whereYear('invoice_date', Carbon::today()->year)
            ->count();

        return $currentMonthInvoices < $limit;
    }

    /**
     * Can business access AI capabilities?
     */
    public function canAccessAi(Business $business): bool
    {
        $sub = $this->getSubscription($business);

        return ($sub->isCorePlan() && $sub->ai_tokens_remaining > 0)
            || $business->aiTokenTopups()->where('remaining_tokens', '>', 0)->where('expires_at', '>', Carbon::now())->exists();
    }

    /**
     * Can business add team members / employees?
     */
    public function canAddMember(Business $business): bool
    {
        $owner = $business->users()->wherePivot('role', 'owner')->first();
        if (! $owner) {
            return false;
        }

        $highestTier = app(OwnerStorageQuotaService::class)->getHighestTierForOwner($owner);
        $userLimit = array_key_exists($highestTier, self::TIER_USER_PER_OWNER_LIMITS) ? self::TIER_USER_PER_OWNER_LIMITS[$highestTier] : 1;

        if ($userLimit === null) {
            return true;
        }

        $currentUserCount = $this->countOwnerUsers($owner);
        return $currentUserCount < $userLimit;
    }

    /**
     * Count total unique members (non-owner) across all businesses owned by this user.
     */
    public function countOwnerUsers(User $owner): int
    {
        return BusinessMembership::whereIn(
            'business_id',
            $owner->businesses()->pluck('businesses.id')
        )->where('role', '!=', 'owner')->count();
    }

    // ── Owner-Level Checks ────────────────────────────────────────────────────
    
    /**
     * Get Business count limit for an owner or business. Null means unlimited.
     */
    public function getBusinessLimit(Business|User $target): ?int
    {
        if ($target instanceof User) {
            $highestTier = app(OwnerStorageQuotaService::class)->getHighestTierForOwner($target);
            return array_key_exists($highestTier, self::TIER_BUSINESS_LIMITS)
                ? self::TIER_BUSINESS_LIMITS[$highestTier]
                : self::FREE_BUSINESS_LIMIT;
        }

        $tier = $this->getSubscription($target)->getTier();
        return array_key_exists($tier, self::TIER_BUSINESS_LIMITS) ? self::TIER_BUSINESS_LIMITS[$tier] : self::FREE_BUSINESS_LIMIT;
    }

    /**
     * Can this owner create another Business?
     * Tier-based business limits (Blueprint v2.3):
     * Free: 1, Standard: 1, Premium: 3, Prestige: Unlimited.
     */
    public function canCreateBusiness(User $owner): bool
    {
        $businessCount = $owner->businesses()->count();
        if ($businessCount === 0) {
            return true;
        }

        $limit = $this->getBusinessLimit($owner);

        if ($limit === null) {
            return true;
        }

        return $businessCount < $limit;
    }

    // ── Business-Level Master Data Checks ─────────────────────────────────────

    /**
     * Get Material limit for business. Null means unlimited.
     */
    public function getMaterialLimit(Business $business): ?int
    {
        $tier = $this->getSubscription($business)->getTier();
        return array_key_exists($tier, self::TIER_MATERIAL_LIMITS) ? self::TIER_MATERIAL_LIMITS[$tier] : self::FREE_MATERIAL_LIMIT;
    }

    /**
     * Can business create a new Material (Bahan Baku)?
     */
    public function canCreateMaterial(Business $business): bool
    {
        $limit = $this->getMaterialLimit($business);
        if ($limit === null) {
            return true;
        }

        $count = Material::where('business_id', $business->id)->count();
        return $count < $limit;
    }

    /**
     * Get Customer limit for business. Null means unlimited.
     */
    public function getCustomerLimit(Business $business): ?int
    {
        $tier = $this->getSubscription($business)->getTier();
        return array_key_exists($tier, self::TIER_CUSTOMER_LIMITS) ? self::TIER_CUSTOMER_LIMITS[$tier] : self::FREE_CUSTOMER_LIMIT;
    }

    /**
     * Can business create a new Customer?
     */
    public function canCreateCustomer(Business $business): bool
    {
        $limit = $this->getCustomerLimit($business);
        if ($limit === null) {
            return true;
        }

        $count = Customer::where('business_id', $business->id)->count();
        return $count < $limit;
    }

    /**
     * Get Supplier limit for business. Null means unlimited.
     */
    public function getSupplierLimit(Business $business): ?int
    {
        $tier = $this->getSubscription($business)->getTier();
        return array_key_exists($tier, self::TIER_SUPPLIER_LIMITS) ? self::TIER_SUPPLIER_LIMITS[$tier] : self::FREE_SUPPLIER_LIMIT;
    }

    /**
     * Can business create a new Supplier?
     */
    public function canCreateSupplier(Business $business): bool
    {
        $limit = $this->getSupplierLimit($business);
        if ($limit === null) {
            return true;
        }

        $count = Supplier::where('business_id', $business->id)->count();
        return $count < $limit;
    }

    /**
     * Get Location limit for business. Null means unlimited.
     */
    public function getLocationLimit(Business $business, string $type): ?int
    {
        $tier = $this->getSubscription($business)->getTier();
        $map = ($type === 'warehouse') ? self::TIER_WAREHOUSE_LIMITS : self::TIER_OUTLET_LIMITS;
        return array_key_exists($tier, $map) ? $map[$tier] : 1;
    }

    /**
     * Get total combined locations (outlets + warehouses) limit for business. Null means unlimited.
     */
    public function getTotalLocationLimit(Business $business): ?int
    {
        $tier = $this->getSubscription($business)->getTier();
        return array_key_exists($tier, self::TIER_TOTAL_LOCATION_LIMITS)
            ? self::TIER_TOTAL_LOCATION_LIMITS[$tier]
            : 2;
    }

    /**
     * Can business create a new Location of given type?
     * type: 'outlet' | 'warehouse' | 'central_kitchen'
     */
    public function canCreateLocation(Business $business, string $type): bool
    {
        $tier = $this->getSubscription($business)->getTier();
        $totalLimit = $this->getTotalLocationLimit($business);
        if ($totalLimit !== null) {
            $totalCount = Location::where('business_id', $business->id)->count();
            if ($totalCount >= $totalLimit) {
                return false;
            }
        }

        // For Free tier: max 1 outlet and max 1 warehouse
        if ($tier === BusinessSubscription::TIER_FREE) {
            $typeCount = Location::where('business_id', $business->id)
                ->where('type', $type)
                ->count();
            if ($typeCount >= 1) {
                return false;
            }
        }

        $limit = $this->getLocationLimit($business, $type);
        if ($limit !== null) {
            $count = Location::where('business_id', $business->id)
                ->where('type', $type)
                ->count();
            if ($count >= $limit) {
                return false;
            }
        }

        return true;
    }

    // ── Monthly Transaction Quota Checks ──────────────────────────────────────

    /**
     * Can business create a new Purchase Order this calendar month?
     */
    public function canCreatePurchaseOrderThisMonth(Business $business): bool
    {
        $tier = $this->getSubscription($business)->getTier();
        $limit = array_key_exists($tier, self::TIER_MONTHLY_PO_LIMITS) ? self::TIER_MONTHLY_PO_LIMITS[$tier] : null;
        if ($limit === null) {
            return true;
        }

        $used = $this->getMonthlyUsage($business, QuotaMonthlyUsage::TYPE_PO);
        return $used < $limit;
    }

    /**
     * Can business process a new POS transaction this calendar month?
     */
    public function canCreatePosTransactionThisMonth(Business $business): bool
    {
        $tier = $this->getSubscription($business)->getTier();
        $limit = array_key_exists($tier, self::TIER_MONTHLY_POS_LIMITS) ? self::TIER_MONTHLY_POS_LIMITS[$tier] : null;
        if ($limit === null) {
            return true;
        }

        $used = $this->getMonthlyUsage($business, QuotaMonthlyUsage::TYPE_POS);
        return $used < $limit;
    }

    /**
     * Check if the business has an active paid Social Media Management Add-on.
     */
    public function hasActiveSocialMediaSubscription(Business $business): bool
    {
        return SubscriptionPayment::where('business_id', $business->id)
            ->where('status', SubscriptionPayment::STATUS_APPROVED)
            ->where(function ($q) {
                $q->where('payment_type', 'social_media')
                    ->orWhere('plan_code', 'addon_social_media')
                    ->orWhere('package_name', 'like', '%Social Media%');
            })
            ->where('created_at', '>=', Carbon::now()->subDays(31))
            ->exists();
    }

    /**
     * Can business schedule a new social media post this calendar month?
     * Free: 3, Standard: 10, Premium: 30, Prestige/Add-on: unlimited.
     */
    public function canScheduleSocialPostThisMonth(Business $business): bool
    {
        if ($this->hasActiveSocialMediaSubscription($business)) {
            return true;
        }

        $tier = $this->getSubscription($business)->getTier();
        $limit = array_key_exists($tier, self::TIER_SOCIAL_LIMITS) ? self::TIER_SOCIAL_LIMITS[$tier] : self::FREE_SOCIAL_POST_MONTHLY_LIMIT;
        if ($limit === null) {
            return true;
        }

        $used = $this->getMonthlyUsage($business, QuotaMonthlyUsage::TYPE_SOCIAL_POST);
        return $used < $limit;
    }

    /**
     * Can business send a WhatsApp notification/blast this calendar month?
     * Tier limits (Blueprint v2.3): Free: 10, Standard: 50, Premium: 300, Prestige: 1000.
     */
    public function canSendWhatsAppThisMonth(Business $business): bool
    {
        $tier = $this->getSubscription($business)->getTier();
        $limit = array_key_exists($tier, self::TIER_WHATSAPP_LIMITS) ? self::TIER_WHATSAPP_LIMITS[$tier] : self::FREE_WHATSAPP_MONTHLY_LIMIT;
        if ($limit === null) {
            return true;
        }

        $used = $this->getMonthlyUsage($business, QuotaMonthlyUsage::TYPE_WHATSAPP);
        return $used < $limit;
    }

    /**
     * Get Table limit for dine-in POS. Null means unlimited.
     */
    public function getTableLimit(Business $business): ?int
    {
        $tier = $this->getSubscription($business)->getTier();
        return array_key_exists($tier, self::TIER_TABLE_LIMITS) ? self::TIER_TABLE_LIMITS[$tier] : 0;
    }

    /**
     * Can business create a new POS Table?
     */
    public function canCreateTable(Business $business): bool
    {
        $limit = $this->getTableLimit($business);
        if ($limit === null) {
            return true;
        }

        $count = \App\Models\PosTable::where('business_id', $business->id)->count();
        return $count < $limit;
    }

    /**
     * Atomically increment monthly usage counter and return new count.
     * Returns false if quota would be exceeded after increment.
     */
    public function incrementMonthlyUsage(Business $business, string $type, int $limit): bool
    {
        return DB::transaction(function () use ($business, $type, $limit): bool {
            $now = Carbon::now();
            $record = QuotaMonthlyUsage::withoutGlobalScopes()->lockForUpdate()->firstOrCreate(
                [
                    'business_id'   => $business->id,
                    'resource_type' => $type,
                    'year'          => $now->year,
                    'month'         => $now->month,
                ],
                ['usage_count' => 0]
            );

            if ($record->usage_count >= $limit) {
                return false;
            }

            $record->increment('usage_count');
            return true;
        });
    }

    /**
     * Get current monthly usage count for a given resource type.
     */
    public function getMonthlyUsage(Business $business, string $type): int
    {
        $now = Carbon::now();
        return (int) (QuotaMonthlyUsage::withoutGlobalScopes()
            ->where('business_id', $business->id)
            ->where('resource_type', $type)
            ->where('year', $now->year)
            ->where('month', $now->month)
            ->value('usage_count') ?? 0);
    }

    // ── Feature Access Checks ─────────────────────────────────────────────────

    /**
     * Can business use Import Data feature? Free = disabled.
     */
    public function canImportData(Business $business): bool
    {
        return $this->getSubscription($business)->isCorePlan();
    }

    /**
     * Can business use Export Data feature? Free = disabled.
     */
    public function canExportData(Business $business): bool
    {
        return $this->getSubscription($business)->isCorePlan();
    }

    // ── Multi-Branch & Central Kitchen Feature Gates (§Blueprint v2.3) ────────

    /**
     * Can business transfer stock between branches/warehouses? (Tier Premium & Prestige)
     */
    public function canTransferStock(Business $business): bool
    {
        return $this->getSubscription($business)->hasTier(BusinessSubscription::TIER_PREMIUM);
    }

    /**
     * Can business set specific product prices per branch? (Tier Premium & Prestige)
     */
    public function canSetBranchPrices(Business $business): bool
    {
        return $this->getSubscription($business)->hasTier(BusinessSubscription::TIER_PREMIUM);
    }

    // ── HRM & Payroll Feature Gates (§Blueprint v2.3) ─────────────────────────

    /**
     * Can business calculate and track employee commissions per transaction/SPK? (Tier Premium & Prestige)
     */
    public function canCalculateCommissions(Business $business): bool
    {
        return $this->getSubscription($business)->hasTier(BusinessSubscription::TIER_PREMIUM);
    }

    /**
     * Can business manage employee loans and automated salary deductions? (Tier Premium & Prestige)
     */
    public function canManageEmployeeLoans(Business $business): bool
    {
        return $this->getSubscription($business)->hasTier(BusinessSubscription::TIER_PREMIUM);
    }

    /**
     * Can business manage Daily Workers / Pekerja Harian Lepas? (Tier Premium & Prestige)
     */
    public function canManageDailyWorkers(Business $business): bool
    {
        return $this->getSubscription($business)->hasTier(BusinessSubscription::TIER_PREMIUM);
    }

    /**
     * Can business calculate BPJS Ketenagakerjaan & Kesehatan? (Tier Premium & Prestige)
     */
    public function canCalculateBPJS(Business $business): bool
    {
        return $this->getSubscription($business)->hasTier(BusinessSubscription::TIER_PREMIUM);
    }

    /**
     * Can business calculate prorated THR based on join date? (Tier Premium & Prestige)
     */
    public function canCalculateTHR(Business $business): bool
    {
        return $this->getSubscription($business)->hasTier(BusinessSubscription::TIER_PREMIUM);
    }

    // ── Tax Compliance Engine Feature Gates (§Blueprint v2.3) ─────────────────

    /**
     * Can business calculate PPh 21 TER (PP 58/2023) and December Pasal 17 reconciliation? (Tier Prestige only)
     */
    public function canCalculatePPh21(Business $business): bool
    {
        return $this->getSubscription($business)->hasTier(BusinessSubscription::TIER_PRESTIGE);
    }

    /**
     * Can business track PPh Final UMKM 0.5% with Rp 500M annual threshold? (All active tiers)
     */
    public function canTrackPPhFinalUMKM(Business $business): bool
    {
        return true;
    }

    /**
     * Can business send automated salary slips via WhatsApp? (Tier Prestige only)
     */
    public function canAutomatePayrollWhatsApp(Business $business): bool
    {
        return $this->getSubscription($business)->hasTier(BusinessSubscription::TIER_PRESTIGE);
    }

    /**
     * Deduct AI token usage.
     */
    public function deductAiTokens(Business $business, int $tokens, ?string $intent = null, ?User $user = null): bool
    {
        if ($tokens <= 0) {
            return false;
        }

        return DB::transaction(function () use ($business, $tokens, $intent, $user): bool {
            $sub = $this->getSubscription($business);
            $remaining = $tokens;
            $batches = $business->aiTokenTopups()
                ->where('remaining_tokens', '>', 0)
                ->where('expires_at', '>', Carbon::now())
                ->orderBy('purchased_at')
                ->orderBy('created_at')
                ->lockForUpdate()
                ->get();
            foreach ($batches as $batch) {
                $deduct = min($remaining, (int) $batch->remaining_tokens);
                $batch->decrement('remaining_tokens', $deduct);
                $remaining -= $deduct;
                if ($remaining === 0) break;
            }
            if ($remaining > 0) {
                if (!$sub->isCorePlan() || $sub->ai_tokens_remaining < $remaining) return false;
                $sub->decrement('ai_tokens_remaining', $remaining);
            }
            AiTokenUsage::create([
                'business_id' => $business->id,
                'user_id' => $user?->id,
                'model_name' => 'gemini-2.5-flash',
                'input_tokens' => (int) ($tokens * 0.7),
                'output_tokens' => (int) ($tokens * 0.3),
                'total_tokens' => $tokens,
                'intent' => $intent ?? 'general_query',
            ]);
            return true;
        });
    }

    public function getTokenTopupAmount(): int
    {
        return max(0, (int) SystemSetting::get('ai_token_topup_amount', '1000000'));
    }
    public function getTokenTopupPrice(): float
    {
        return max(0, (float) SystemSetting::get('ai_token_topup_price', '50000'));
    }
    public function getStorageTopupBytes(): int
    {
        return max(0, (int) SystemSetting::get('storage_topup_gb', '1')) * 1024 * 1024 * 1024;
    }
    public function getStorageTopupPrice(): float
    {
        return max(0, (float) SystemSetting::get('storage_topup_price', '50000'));
    }

    public function createTokenTopupOrder(Business $business, User $user, string $paymentMethod = SubscriptionPayment::METHOD_BCA): SubscriptionPayment
    {
        return $this->createTopupOrder($business, $user, 'ai_token', $this->getTokenTopupAmount(), 0, $this->getTokenTopupPrice(), $paymentMethod);
    }

    public function createStorageTopupOrder(Business $business, User $user, string $paymentMethod = SubscriptionPayment::METHOD_BCA): SubscriptionPayment
    {
        return $this->createTopupOrder($business, $user, 'storage', 0, $this->getStorageTopupBytes(), $this->getStorageTopupPrice(), $paymentMethod);
    }

    private function createTopupOrder(Business $business, User $user, string $type, int $quantity, int $storageBytes, float $amount, string $paymentMethod): SubscriptionPayment
    {
        $uniqueCode = random_int(100, 999);
        $orderNumber = $this->nextOrderNumber('TOP-' . date('Ym') . '-');
        return SubscriptionPayment::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'payment_type' => $type,
            'order_number' => $orderNumber,
            'plan_code' => 'topup',
            'cycle' => 'one_time',
            'amount' => $amount,
            'unique_code' => $uniqueCode,
            'total_payable' => $amount + $uniqueCode,
            'topup_quantity' => $quantity ?: null,
            'topup_storage_bytes' => $storageBytes ?: null,
            'payment_method' => $paymentMethod,
            'status' => SubscriptionPayment::STATUS_PENDING,
        ]);
    }

    /**
     * Susun nomor order unik global untuk subscription_payments.
     * Constraint `order_number` bersifat global (bukan per-bisnis), jadi pencacah
     * dihitung dari seluruh baris tanpa global scope per-bisnis agar tidak bentrok.
     */
    private function nextOrderNumber(string $prefix): string
    {
        $latest = DB::table('subscription_payments')
            ->where('order_number', 'LIKE', $prefix . '%')
            ->orderByDesc('order_number')
            ->value('order_number');

        $seq = 1;
        if ($latest && preg_match('/-(\d+)$/', $latest, $matches)) {
            $seq = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Upgrade or set plan for business.
     */
    public function upgradeToCore(Business $business, string $cycle = 'monthly'): BusinessSubscription
    {
        $sub = $this->getSubscription($business);
        $isAnnual = $cycle === 'annual';
        $price = $isAnnual ? $this->getAnnualPrice() : $this->getMonthlyPrice();

        // Tambah durasi (skema add-on/FIFO(: perpanjang dari ends_at bila langganan masih aktif;
        // bila status non-aktif / sudah lewat waktunya, mulai dari sekarang.
        $currentEnd = ($sub->isActive() && $sub->ends_at && $sub->ends_at->isFuture())
            ? $sub->ends_at->copy()
            : Carbon::now();
        $newEndsAt = $isAnnual ? $currentEnd->addYear() : $currentEnd->addMonth();

        $sub->update([
            'plan_code' => $isAnnual ? BusinessSubscription::PLAN_CORE_ANNUAL : BusinessSubscription::PLAN_CORE_MONTHLY,
            'price' => $price,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => Carbon::now(),
            'ends_at' => $newEndsAt,
            'ai_tokens_monthly_allowance' => 0,
            'last_token_reset_at' => Carbon::today()->toDateString(),
        ]);

        return $sub;
    }

    /**
     * Determine if a business is currently on the Core plan.
     */
    public function isCorePlan(Business $business): bool
    {
        return $this->getSubscription($business)->isCorePlan();
    }

    /**
     * Usage summary for dashboard and limits meter - all 12+ resources.
     */
    public function getUsageSummary(Business $business, bool $forceFresh = false): array
    {
        $cacheKey = "entitlement_usage_summary_{$business->id}";
        if (! $forceFresh && ! app()->environment('testing')) {
            $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $sub = $this->getSubscription($business);
        $isCore = $sub->isCorePlan();
        $monthlyPriceFormatted = number_format($this->getMonthlyPrice(), 0, ',', '.');
        $annualPriceFormatted  = number_format($this->getAnnualPrice(), 0, ',', '.');
        $now = Carbon::now();

        // ── Master data counts ──────────────────────────────────────────────
        $productCount  = Product::where('business_id', $business->id)->count();
        $materialCount = Material::where('business_id', $business->id)->count();
        $customerCount = Customer::where('business_id', $business->id)->count();
        $supplierCount = Supplier::where('business_id', $business->id)->count();
        $outletCount   = Location::where('business_id', $business->id)->where('type', 'outlet')->count();
        $warehouseCount = Location::where('business_id', $business->id)->where('type', 'warehouse')->count();

        $recipeCount = BomHeader::whereHas('costModel', function ($q) use ($business) {
            $q->where('business_id', $business->id);
        })->count();

        $tableCount = \App\Models\PosTable::where('business_id', $business->id)->count();

        // ── Monthly transaction counts ──────────────────────────────────────
        $invoiceMonthCount = Invoice::where('business_id', $business->id)
            ->whereMonth('invoice_date', $now->month)
            ->whereYear('invoice_date',  $now->year)
            ->count();

        $poMonthCount  = $this->getMonthlyUsage($business, QuotaMonthlyUsage::TYPE_PO);
        $posMonthCount = $this->getMonthlyUsage($business, QuotaMonthlyUsage::TYPE_POS);
        $socialPostsMonthCount = $this->getMonthlyUsage($business, QuotaMonthlyUsage::TYPE_SOCIAL_POST);
        $waMonthCount = $this->getMonthlyUsage($business, QuotaMonthlyUsage::TYPE_WHATSAPP);
        $hasSocialAddon = $this->hasActiveSocialMediaSubscription($business);

        // ── Owner-level: Business count & User count ────────────────────────
        $owner = $business->users()->wherePivot('role', 'owner')->first();
        $businessCount = $owner ? $owner->businesses()->count() : 1;
        $userCount     = $owner ? $this->countOwnerUsers($owner) : 0;

        // ── AI tokens ──────────────────────────────────────────────────────
        $aiAllowance = $isCore ? (int) $sub->ai_tokens_monthly_allowance : 0;
        $aiRemaining = $isCore ? (int) $sub->ai_tokens_remaining : 0;
        $activeTopups = $business->aiTokenTopups()->where('expires_at', '>', $now)->get();
        $aiAllowance += (int) $activeTopups->sum('purchased_tokens');
        $aiRemaining += (int) $activeTopups->sum('remaining_tokens');
        $aiUsed = max(0, $aiAllowance - $aiRemaining);

        // ── Storage ─────────────────────────────────────────────────────────
        $storage = $owner ? app(OwnerStorageQuotaService::class)->getSummary($owner) : [
            'used_bytes' => 0,
            'limit_bytes' => 0,
            'used_mb' => 0,
            'limit_gb' => 0,
            'percentage' => 0,
            'is_over_limit' => false,
        ];

        $buildStat = function (int $used, ?int $limit): array {
            return [
                'used'       => $used,
                'limit'      => $limit,
                'percent'    => ($limit && $limit > 0) ? min(100, round(($used / $limit) * 100)) : 0,
                'is_reached' => $limit !== null && $used >= $limit,
                'remaining'  => $limit !== null ? max(0, $limit - $used) : null,
            ];
        };

        $tier = $sub->getTier();
        $tierUserLimit = array_key_exists($tier, self::TIER_USER_PER_OWNER_LIMITS) ? self::TIER_USER_PER_OWNER_LIMITS[$tier] : 1;
        $tierBusinessLimit = array_key_exists($tier, self::TIER_BUSINESS_LIMITS) ? self::TIER_BUSINESS_LIMITS[$tier] : 1;
        $tierTableLimit = array_key_exists($tier, self::TIER_TABLE_LIMITS) ? self::TIER_TABLE_LIMITS[$tier] : null;

        $tierLabel = match ($tier) {
            BusinessSubscription::TIER_PRESTIGE => 'Prestige Plan (Rp199.000/bln)',
            BusinessSubscription::TIER_PREMIUM => 'Premium Plan (Rp89.000/bln)',
            BusinessSubscription::TIER_STANDARD => 'Standard Plan (Rp29.000/bln)',
            default => 'Free Plan (Rp0)',
        };

        $result = [
            'tier'       => $tier,
            'tier_level' => $sub->getTierLevel(),
            'plan_code'  => $sub->plan_code,
            'plan_label' => $tierLabel,
            'is_core' => $isCore,
            'is_past_due' => $sub->isPastDue(),
            'status'  => $sub->status,
            'ends_at' => $sub->ends_at?->format('d M Y'),

            // Owner-level
            'businesses' => $buildStat($businessCount, $tierBusinessLimit),
            'users'      => $buildStat($userCount,     $tierUserLimit),

            // Business master data
            'products'   => $buildStat($productCount,   array_key_exists($tier, self::TIER_PRODUCT_LIMITS) ? self::TIER_PRODUCT_LIMITS[$tier] : self::FREE_PRODUCT_LIMIT),
            'materials'  => $buildStat($materialCount,  array_key_exists($tier, self::TIER_MATERIAL_LIMITS) ? self::TIER_MATERIAL_LIMITS[$tier] : self::FREE_MATERIAL_LIMIT),
            'customers'  => $buildStat($customerCount,  array_key_exists($tier, self::TIER_CUSTOMER_LIMITS) ? self::TIER_CUSTOMER_LIMITS[$tier] : self::FREE_CUSTOMER_LIMIT),
            'suppliers'  => $buildStat($supplierCount,  array_key_exists($tier, self::TIER_SUPPLIER_LIMITS) ? self::TIER_SUPPLIER_LIMITS[$tier] : self::FREE_SUPPLIER_LIMIT),
            'outlets'    => $buildStat($outletCount,    array_key_exists($tier, self::TIER_OUTLET_LIMITS) ? self::TIER_OUTLET_LIMITS[$tier] : self::FREE_OUTLET_LIMIT),
            'warehouses' => $buildStat($warehouseCount, array_key_exists($tier, self::TIER_WAREHOUSE_LIMITS) ? self::TIER_WAREHOUSE_LIMITS[$tier] : self::FREE_WAREHOUSE_LIMIT),
            'locations'  => $buildStat($outletCount + $warehouseCount, $this->getTotalLocationLimit($business)),
            'recipes'    => $buildStat($recipeCount,    array_key_exists($tier, self::TIER_RECIPE_LIMITS) ? self::TIER_RECIPE_LIMITS[$tier] : self::FREE_RECIPE_LIMIT),
            'tables'     => $buildStat($tableCount,     $tierTableLimit),

            // Monthly quotas
            'invoices_this_month' => $buildStat($invoiceMonthCount, array_key_exists($tier, self::TIER_MONTHLY_INVOICE_LIMITS) ? self::TIER_MONTHLY_INVOICE_LIMITS[$tier] : null),
            'po_this_month'       => $buildStat($poMonthCount,      array_key_exists($tier, self::TIER_MONTHLY_PO_LIMITS) ? self::TIER_MONTHLY_PO_LIMITS[$tier] : null),
            'pos_this_month'      => $buildStat($posMonthCount,     array_key_exists($tier, self::TIER_MONTHLY_POS_LIMITS) ? self::TIER_MONTHLY_POS_LIMITS[$tier] : null),
            'social_posts_this_month' => $buildStat($socialPostsMonthCount, $hasSocialAddon ? null : (array_key_exists($tier, self::TIER_SOCIAL_LIMITS) ? self::TIER_SOCIAL_LIMITS[$tier] : self::FREE_SOCIAL_POST_MONTHLY_LIMIT)),
            'whatsapp_this_month'     => $buildStat($waMonthCount,          array_key_exists($tier, self::TIER_WHATSAPP_LIMITS) ? self::TIER_WHATSAPP_LIMITS[$tier] : self::FREE_WHATSAPP_MONTHLY_LIMIT),
            'has_social_addon'        => $hasSocialAddon,

            // AI & Storage
            'ai_tokens' => [
                'allowance' => $aiAllowance,
                'used'      => $aiUsed,
                'remaining' => $aiRemaining,
                'percent'   => $aiAllowance > 0 ? min(100, round(($aiUsed / $aiAllowance) * 100)) : 0,
                'is_free'   => !$isCore && $aiAllowance === 0,
                'is_unlimited' => false,
            ],
            'storage' => $storage,

            // Feature flags
            'can_import' => $isCore,
            'can_export' => $isCore,
            'can_create_table' => $this->canCreateTable($business),
            'can_use_kds' => $sub->hasTier(BusinessSubscription::TIER_PREMIUM),
            'can_transfer_stock' => $this->canTransferStock($business),
            'can_set_branch_prices' => $this->canSetBranchPrices($business),
            'can_calculate_commissions' => $this->canCalculateCommissions($business),
            'can_manage_employee_loans' => $this->canManageEmployeeLoans($business),
            'can_manage_daily_workers' => $this->canManageDailyWorkers($business),
            'can_calculate_bpjs' => $this->canCalculateBPJS($business),
            'can_calculate_thr' => $this->canCalculateTHR($business),
            'can_calculate_pph21' => $this->canCalculatePPh21($business),
            'can_track_pph_final_umkm' => $this->canTrackPPhFinalUMKM($business),
            'can_automate_payroll_wa' => $this->canAutomatePayrollWhatsApp($business),
        ];

        if (! app()->environment('testing')) {
            \Illuminate\Support\Facades\Cache::put($cacheKey, $result, 60);
        }

        return $result;
    }

    /**
     * Clear usage cache for a business.
     */
    public function clearUsageCache(Business $business): void
    {
        \Illuminate\Support\Facades\Cache::forget("entitlement_usage_summary_{$business->id}");
    }

    /**
     * Create a pending subscription payment order with a unique verification code.
     */
    public function createPaymentOrder(
        Business $business,
        User $user,
        string $cycle = 'monthly',
        string $paymentMethod = SubscriptionPayment::METHOD_QRIS,
        string $tier = BusinessSubscription::TIER_STANDARD
    ): SubscriptionPayment {
        $isAnnual = $cycle === 'annual';

        $tier = match (strtolower($tier)) {
            BusinessSubscription::TIER_PRESTIGE => BusinessSubscription::TIER_PRESTIGE,
            BusinessSubscription::TIER_PREMIUM => BusinessSubscription::TIER_PREMIUM,
            default => BusinessSubscription::TIER_STANDARD,
        };

        $planCode = match ($tier) {
            BusinessSubscription::TIER_PRESTIGE => $isAnnual ? BusinessSubscription::PLAN_PRESTIGE_ANNUAL : BusinessSubscription::PLAN_PRESTIGE_MONTHLY,
            BusinessSubscription::TIER_PREMIUM => $isAnnual ? BusinessSubscription::PLAN_PREMIUM_ANNUAL : BusinessSubscription::PLAN_PREMIUM_MONTHLY,
            default => $isAnnual ? BusinessSubscription::PLAN_STANDARD_ANNUAL : BusinessSubscription::PLAN_STANDARD_MONTHLY,
        };

        $baseAmount = match ($tier) {
            BusinessSubscription::TIER_PRESTIGE => $isAnnual ? 1990000.0 : 199000.0,
            BusinessSubscription::TIER_PREMIUM => $isAnnual ? 890000.0 : 89000.0,
            default => $isAnnual ? 290000.0 : 29000.0,
        };

        if ($tier === BusinessSubscription::TIER_STANDARD) {
            if ($isAnnual && SystemSetting::get('subscription_price_annual') !== null) {
                $baseAmount = (float) SystemSetting::get('subscription_price_annual');
            } elseif (! $isAnnual && SystemSetting::get('subscription_price_monthly') !== null) {
                $baseAmount = (float) SystemSetting::get('subscription_price_monthly');
            }
        }

        $isGateway = in_array($paymentMethod, [
            SubscriptionPayment::METHOD_QRIS,
            SubscriptionPayment::METHOD_BCA_VA,
            SubscriptionPayment::METHOD_MANDIRI_VA,
            SubscriptionPayment::METHOD_BRI_VA,
            SubscriptionPayment::METHOD_BNI_VA,
            SubscriptionPayment::METHOD_PERMATA_VA,
            SubscriptionPayment::METHOD_INDOMARET,
            SubscriptionPayment::METHOD_ALFAMART,
            SubscriptionPayment::METHOD_BCA,
            SubscriptionPayment::METHOD_MANDIRI,
            SubscriptionPayment::METHOD_BRI,
        ], true);

        $uniqueCode = $isGateway ? 0 : random_int(100, 999);
        $totalPayable = $baseAmount + $uniqueCode;

        // Generate unique order number SUB-YYYYMM-XXXX
        $orderNumber = $this->nextOrderNumber('SUB-' . date('Ym') . '-');

        $packageName = match ($tier) {
            BusinessSubscription::TIER_PRESTIGE => 'Paket Prestige ' . ($isAnnual ? 'Tahunan' : 'Bulanan'),
            BusinessSubscription::TIER_PREMIUM => 'Paket Premium ' . ($isAnnual ? 'Tahunan' : 'Bulanan'),
            default => 'Paket Standard ' . ($isAnnual ? 'Tahunan' : 'Bulanan'),
        };

        return SubscriptionPayment::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'order_number' => $orderNumber,
            'plan_code' => $planCode,
            'package_name' => $packageName,
            'cycle' => $isAnnual ? 'annual' : 'monthly',
            'amount' => $baseAmount,
            'unique_code' => $uniqueCode,
            'total_payable' => $totalPayable,
            'payment_method' => $paymentMethod,
            'payment_gateway' => $isGateway ? SubscriptionPayment::GATEWAY_TRIPAY : SubscriptionPayment::GATEWAY_MANUAL,
            'status' => SubscriptionPayment::STATUS_PENDING,
        ]);
    }

    public function createPackageOrder(Business $business, User $user, BillingPackage $package, string $paymentMethod = SubscriptionPayment::METHOD_BCA): SubscriptionPayment
    {
        // If package is free / promo trial (price <= 0), activate immediately without payment
        if ((float) $package->price <= 0.0) {
            return $this->activateFreePackage($business, $user, $package);
        }

        $uniqueCode = random_int(100, 999);
        $orderNumber = $this->nextOrderNumber('PKG-' . date('Ym') . '-');
        $paymentType = $package->type;
        $isSubscription = $paymentType === BillingPackage::TYPE_SUBSCRIPTION;

        return SubscriptionPayment::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'payment_type' => $paymentType,
            'billing_package_id' => $package->id,
            'package_name' => $package->name,
            'package_duration_days' => $package->duration_days,
            'order_number' => $orderNumber,
            'plan_code' => $isSubscription ? BusinessSubscription::PLAN_CORE_MONTHLY : 'topup',
            'cycle' => $isSubscription ? 'package' : 'one_time',
            'amount' => $package->price,
            'unique_code' => $uniqueCode,
            'total_payable' => $package->price + $uniqueCode,
            'topup_quantity' => $package->token_quantity,
            'topup_storage_bytes' => $package->storage_bytes,
            'payment_method' => $paymentMethod,
            'status' => SubscriptionPayment::STATUS_PENDING,
        ]);
    }

    /**
     * Activate a free promo package (e.g. 15-day Trial Pro) immediately without payment or confirmation.
     */
    public function activateFreePackage(Business $business, User $user, BillingPackage $package): SubscriptionPayment
    {
        return DB::transaction(function () use ($business, $user, $package) {
            $orderNumber = $this->nextOrderNumber('FREE-' . date('Ym') . '-');
            $paymentType = $package->type;
            $isSubscription = $paymentType === BillingPackage::TYPE_SUBSCRIPTION;
            $durationDays = max(1, (int) ($package->duration_days ?? 15));

            $payment = SubscriptionPayment::create([
                'business_id' => $business->id,
                'user_id' => $user->id,
                'payment_type' => $paymentType,
                'billing_package_id' => $package->id,
                'package_name' => $package->name,
                'package_duration_days' => $durationDays,
                'order_number' => $orderNumber,
                'plan_code' => $isSubscription ? BusinessSubscription::PLAN_CORE_MONTHLY : 'topup',
                'cycle' => $isSubscription ? 'package' : 'one_time',
                'amount' => 0,
                'unique_code' => 0,
                'total_payable' => 0,
                'topup_quantity' => $package->token_quantity,
                'topup_storage_bytes' => $package->storage_bytes,
                'payment_method' => SubscriptionPayment::METHOD_FREE_PROMO,
                'status' => SubscriptionPayment::STATUS_APPROVED,
                'admin_notes' => 'Aktivasi Otomatis Promo Bebas Biaya / Trial Pro ' . $durationDays . ' Hari',
                'approved_at' => Carbon::now(),
            ]);

            if ($isSubscription) {
                $subscription = $this->getSubscription($business);
                $currentEnd = ($subscription->isActive() && $subscription->ends_at && $subscription->ends_at->isFuture())
                    ? $subscription->ends_at
                    : Carbon::now();

                $subscription->update([
                    'plan_code' => BusinessSubscription::PLAN_CORE_MONTHLY,
                    'price' => 0,
                    'status' => BusinessSubscription::STATUS_ACTIVE,
                    'starts_at' => Carbon::now(),
                    'ends_at' => $currentEnd->copy()->addDays($durationDays),
                    'ai_tokens_monthly_allowance' => 0,
                    'last_token_reset_at' => Carbon::today()->toDateString(),
                ]);

                if ($package->token_quantity > 0) {
                    $purchasedAt = Carbon::now();
                    AiTokenTopup::create([
                        'business_id' => $business->id,
                        'payment_id' => $payment->id,
                        'purchased_tokens' => $package->token_quantity,
                        'remaining_tokens' => $package->token_quantity,
                        'purchased_at' => $purchasedAt,
                        'expires_at' => $purchasedAt->copy()->addDays($durationDays),
                    ]);
                }
            } elseif ($paymentType === 'ai_token') {
                $purchasedAt = Carbon::now();
                $expiryDays = $package->token_expiry_days ?? 30;
                AiTokenTopup::create([
                    'business_id' => $business->id,
                    'payment_id' => $payment->id,
                    'purchased_tokens' => $package->token_quantity,
                    'remaining_tokens' => $package->token_quantity,
                    'purchased_at' => $purchasedAt,
                    'expires_at' => $purchasedAt->copy()->addDays($expiryDays),
                ]);
            } elseif ($paymentType === 'storage') {
                $owner = $business->users()->wherePivot('role', 'owner')->firstOrFail();
                OwnerStorageTopup::create([
                    'owner_id' => $owner->id,
                    'payment_id' => $payment->id,
                    'storage_bytes' => $package->storage_bytes,
                    'approved_at' => Carbon::now(),
                ]);
            }

            return $payment;
        });
    }

    /**
     * Submit payment proof file and metadata.
     *
     * @param array<string, mixed> $senderData
     */
    public function submitPaymentProof(
        SubscriptionPayment $payment,
        UploadedFile $file,
        array $senderData = []
    ): SubscriptionPayment {
        $filename = Str::random(40) . '.' . $file->extension();
        $path = 'billing-proofs/' . $filename;
        Storage::disk('local')->putFileAs('billing-proofs', $file, $filename);

        $payment->update([
            'payment_proof_path' => $path,
            'sender_bank' => $senderData['sender_bank'] ?? null,
            'sender_account_name' => $senderData['sender_account_name'] ?? null,
            'sender_account_number' => $senderData['sender_account_number'] ?? null,
            'notes' => $senderData['notes'] ?? null,
            'proof_uploaded_at' => Carbon::now(),
            'status' => SubscriptionPayment::STATUS_AWAITING_APPROVAL,
        ]);

        $freshPayment = $payment->fresh();

        // 1. Send notification email to Administrator (agungmustaqim28@gmail.com)
        try {
            \App\Domain\Mail\DynamicMailConfig::bootstrap();
            \Illuminate\Support\Facades\Mail::to('agungmustaqim28@gmail.com')
                ->send(new \App\Mail\PaymentUploadedAdminMail($freshPayment));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send payment uploaded email to admin: ' . $e->getMessage());
        }

        return $freshPayment;
    }

    /**
     * Admin approves subscription payment and automatically activates Core subscription.
     */
    public function approvePayment(
        SubscriptionPayment $payment,
        ?Admin $admin = null,
        ?string $adminNotes = null
    ): SubscriptionPayment {
        $approvedPayment = DB::transaction(function () use ($payment, $admin, $adminNotes) {
            if (! $payment->isAwaitingApproval() && ! $payment->isPending()) return $payment->fresh();

            $payment->update([
                'status' => SubscriptionPayment::STATUS_APPROVED,
                'approved_by' => $admin?->id,
                'approved_at' => Carbon::now(),
                'admin_notes' => $adminNotes,
            ]);

            if ($payment->payment_type === 'ai_token') {
                $purchasedAt = Carbon::now();
                $expiryDays = $payment->billingPackage?->token_expiry_days ?? 30;
                AiTokenTopup::create([
                    'business_id' => $payment->business_id,
                    'payment_id' => $payment->id,
                    'purchased_tokens' => $payment->topup_quantity,
                    'remaining_tokens' => $payment->topup_quantity,
                    'purchased_at' => $purchasedAt,
                    'expires_at' => $purchasedAt->copy()->addDays($expiryDays),
                ]);
            } elseif ($payment->payment_type === 'storage') {
                $owner = $payment->business->users()->wherePivot('role', 'owner')->firstOrFail();
                OwnerStorageTopup::create([
                    'owner_id' => $owner->id,
                    'payment_id' => $payment->id,
                    'storage_bytes' => $payment->topup_storage_bytes,
                    'approved_at' => Carbon::now(),
                ]);
            } elseif ($payment->billing_package_id && $payment->payment_type === BillingPackage::TYPE_SUBSCRIPTION) {
                $subscription = $this->getSubscription($payment->business);
                $durationDays = max(1, (int) ($payment->package_duration_days ?? 30));
                $currentEnd = ($subscription->isActive() && $subscription->ends_at && $subscription->ends_at->isFuture())
                    ? $subscription->ends_at
                    : Carbon::now();
                $subscription->update([
                    'plan_code' => $payment->plan_code,
                    'price' => $payment->amount,
                    'status' => BusinessSubscription::STATUS_ACTIVE,
                    'starts_at' => Carbon::now(),
                    'ends_at' => $currentEnd->copy()->addDays($durationDays),
                    'ai_tokens_monthly_allowance' => 0,
                    'last_token_reset_at' => Carbon::today()->toDateString(),
                ]);
                $this->clearUsageCache($payment->business);
            } elseif ($payment->plan_code && $payment->plan_code !== BusinessSubscription::PLAN_FREE) {
                $subscription = $this->getSubscription($payment->business);
                $isAnnual = $payment->cycle === 'annual';
                $durationDays = $isAnnual ? 365 : 30;
                $currentEnd = ($subscription->isActive() && $subscription->ends_at && $subscription->ends_at->isFuture())
                    ? $subscription->ends_at
                    : Carbon::now();
                $subscription->update([
                    'plan_code' => $payment->plan_code,
                    'price' => $payment->amount,
                    'status' => BusinessSubscription::STATUS_ACTIVE,
                    'starts_at' => Carbon::now(),
                    'ends_at' => $currentEnd->copy()->addDays($durationDays),
                    'ai_tokens_monthly_allowance' => 0,
                    'last_token_reset_at' => Carbon::today()->toDateString(),
                ]);
                $this->clearUsageCache($payment->business);
            } else {
                $this->upgradeToCore($payment->business, $payment->cycle);
                $this->clearUsageCache($payment->business);
            }

            return $payment->fresh();
        });

        // 2. Send invoice & receipt email to Business Owner
        try {
            \App\Domain\Mail\DynamicMailConfig::bootstrap();
            $owner = $approvedPayment->business?->users()->wherePivot('role', 'owner')->first()
                ?? $approvedPayment->business?->users()->first();
            $ownerEmail = $owner?->email ?? $approvedPayment->business?->email;

            if ($ownerEmail) {
                \Illuminate\Support\Facades\Mail::to($ownerEmail)
                    ->send(new \App\Mail\PaymentApprovedInvoiceMail($approvedPayment));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send payment approved invoice to owner: ' . $e->getMessage());
        }

        return $approvedPayment;
    }

    /**
     * Admin rejects subscription payment with reason.
     */
    public function rejectPayment(
        SubscriptionPayment $payment,
        Admin $admin,
        string $reason
    ): SubscriptionPayment {
        $payment->update([
            'status' => SubscriptionPayment::STATUS_REJECTED,
            'admin_notes' => $reason,
            'rejected_at' => Carbon::now(),
        ]);

        return $payment->fresh();
    }
}
