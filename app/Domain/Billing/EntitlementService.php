<?php

declare(strict_types=1);

namespace App\Domain\Billing;

use App\Models\Admin;
use App\Models\AiTokenUsage;
use App\Models\BomHeader;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\SubscriptionPayment;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

final class EntitlementService
{
    public const FREE_PRODUCT_LIMIT = 50;
    public const FREE_RECIPE_LIMIT = 20;
    public const FREE_INVOICE_MONTHLY_LIMIT = 10;
    public const DEFAULT_CORE_AI_MONTHLY_TOKENS = 10_000_000;
    public const DEFAULT_MONTHLY_PRICE = 129_000.0;
    public const DEFAULT_ANNUAL_PRICE = 1_290_000.0;

    /**
     * Get configured monthly price for Core plan.
     */
    public function getMonthlyPrice(): float
    {
        $val = SystemSetting::get('subscription_price_monthly');

        return $val !== null && is_numeric($val) ? (float) $val : self::DEFAULT_MONTHLY_PRICE;
    }

    /**
     * Get configured annual price for Core plan.
     */
    public function getAnnualPrice(): float
    {
        $val = SystemSetting::get('subscription_price_annual');

        return $val !== null && is_numeric($val) ? (float) $val : self::DEFAULT_ANNUAL_PRICE;
    }

    /**
     * Get configured monthly AI token allowance for Core plan.
     */
    public function getCoreMonthlyAiTokens(): int
    {
        $val = SystemSetting::get('subscription_ai_tokens_monthly');

        return $val !== null && is_numeric($val) ? (int) $val : self::DEFAULT_CORE_AI_MONTHLY_TOKENS;
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

        // Auto-check monthly AI token reset if Core plan
        if ($sub->isCorePlan() && $sub->last_token_reset_at) {
            $lastReset = Carbon::parse($sub->last_token_reset_at);
            if ($lastReset->diffInMonths(Carbon::today()) >= 1) {
                $sub->update([
                    'ai_tokens_remaining' => $this->getCoreMonthlyAiTokens(),
                    'last_token_reset_at' => Carbon::today()->toDateString(),
                ]);
            }
        }

        return $sub;
    }

    /**
     * Can business create a new product?
     */
    public function canCreateProduct(Business $business): bool
    {
        $sub = $this->getSubscription($business);
        if ($sub->isCorePlan()) {
            return true;
        }

        $currentProducts = Product::where('business_id', $business->id)->count();

        return $currentProducts < self::FREE_PRODUCT_LIMIT;
    }

    /**
     * Can business create a new recipe (BOM)?
     */
    public function canCreateRecipe(Business $business): bool
    {
        $sub = $this->getSubscription($business);
        if ($sub->isCorePlan()) {
            return true;
        }

        $currentRecipes = BomHeader::whereHas('costModel', function ($q) use ($business) {
            $q->where('business_id', $business->id);
        })->count();

        return $currentRecipes < self::FREE_RECIPE_LIMIT;
    }

    /**
     * Can business issue a new sales invoice this month?
     */
    public function canCreateInvoiceThisMonth(Business $business): bool
    {
        $sub = $this->getSubscription($business);
        if ($sub->isCorePlan()) {
            return true;
        }

        $currentMonthInvoices = Invoice::where('business_id', $business->id)
            ->whereMonth('invoice_date', Carbon::today()->month)
            ->whereYear('invoice_date', Carbon::today()->year)
            ->count();

        return $currentMonthInvoices < self::FREE_INVOICE_MONTHLY_LIMIT;
    }

    /**
     * Can business access AI capabilities?
     */
    public function canAccessAi(Business $business): bool
    {
        $sub = $this->getSubscription($business);

        // Core plan active and has tokens remaining
        return $sub->isCorePlan() && $sub->ai_tokens_remaining > 0;
    }

    /**
     * Can business add team members / employees?
     * Free plan is restricted to 1 user (Solo Owner).
     * Core plan allows unlimited employees.
     */
    public function canAddMember(Business $business): bool
    {
        $sub = $this->getSubscription($business);
        if ($sub->isCorePlan()) {
            return true;
        }

        $userCount = $business->users()->count();

        return $userCount < 1;
    }

    /**
     * Deduct AI token usage.
     */
    public function deductAiTokens(Business $business, int $tokens, ?string $intent = null, ?User $user = null): bool
    {
        $sub = $this->getSubscription($business);
        if (!$sub->isCorePlan()) {
            return false;
        }

        if ($sub->ai_tokens_remaining < $tokens) {
            return false;
        }

        $sub->decrement('ai_tokens_remaining', $tokens);

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
    }

    /**
     * Upgrade or set plan for business.
     */
    public function upgradeToCore(Business $business, string $cycle = 'monthly'): BusinessSubscription
    {
        $sub = $this->getSubscription($business);
        $isAnnual = $cycle === 'annual';
        $price = $isAnnual ? $this->getAnnualPrice() : $this->getMonthlyPrice();
        $aiTokens = $this->getCoreMonthlyAiTokens();

        $sub->update([
            'plan_code' => $isAnnual ? BusinessSubscription::PLAN_CORE_ANNUAL : BusinessSubscription::PLAN_CORE_MONTHLY,
            'price' => $price,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => Carbon::now(),
            'ends_at' => $isAnnual ? Carbon::now()->addYear() : Carbon::now()->addMonth(),
            'ai_tokens_monthly_allowance' => $aiTokens,
            'ai_tokens_remaining' => $aiTokens,
            'last_token_reset_at' => Carbon::today()->toDateString(),
        ]);

        return $sub;
    }

    /**
     * Usage summary for dashboard and limits meter.
     */
    public function getUsageSummary(Business $business): array
    {
        $sub = $this->getSubscription($business);
        $isCore = $sub->isCorePlan();
        $monthlyPriceFormatted = number_format($this->getMonthlyPrice(), 0, ',', '.');
        $annualPriceFormatted = number_format($this->getAnnualPrice(), 0, ',', '.');

        $productCount = Product::where('business_id', $business->id)->count();
        $recipeCount = BomHeader::whereHas('costModel', function ($q) use ($business) {
            $q->where('business_id', $business->id);
        })->count();
        $invoiceMonthCount = Invoice::where('business_id', $business->id)
            ->whereMonth('invoice_date', Carbon::today()->month)
            ->whereYear('invoice_date', Carbon::today()->year)
            ->count();

        $aiAllowance = $isCore ? (int) $sub->ai_tokens_monthly_allowance : 0;
        $aiRemaining = $isCore ? (int) $sub->ai_tokens_remaining : 0;
        $aiUsed = max(0, $aiAllowance - $aiRemaining);

        return [
            'plan_code' => $sub->plan_code,
            'plan_label' => $isCore ? ($sub->plan_code === BusinessSubscription::PLAN_CORE_ANNUAL ? "Core Annual (Rp{$annualPriceFormatted}/thn)" : "Core Monthly (Rp{$monthlyPriceFormatted}/bln)") : 'Free Plan (Rp0)',
            'is_core' => $isCore,
            'status' => $sub->status,
            'ends_at' => $sub->ends_at?->format('d M Y'),
            'products' => [
                'used' => $productCount,
                'limit' => $isCore ? null : self::FREE_PRODUCT_LIMIT,
                'percent' => $isCore ? 0 : min(100, round(($productCount / self::FREE_PRODUCT_LIMIT) * 100)),
                'is_reached' => !$isCore && $productCount >= self::FREE_PRODUCT_LIMIT,
            ],
            'recipes' => [
                'used' => $recipeCount,
                'limit' => $isCore ? null : self::FREE_RECIPE_LIMIT,
                'percent' => $isCore ? 0 : min(100, round(($recipeCount / self::FREE_RECIPE_LIMIT) * 100)),
                'is_reached' => !$isCore && $recipeCount >= self::FREE_RECIPE_LIMIT,
            ],
            'invoices_this_month' => [
                'used' => $invoiceMonthCount,
                'limit' => $isCore ? null : self::FREE_INVOICE_MONTHLY_LIMIT,
                'percent' => $isCore ? 0 : min(100, round(($invoiceMonthCount / self::FREE_INVOICE_MONTHLY_LIMIT) * 100)),
                'is_reached' => !$isCore && $invoiceMonthCount >= self::FREE_INVOICE_MONTHLY_LIMIT,
            ],
            'ai_tokens' => [
                'allowance' => $aiAllowance,
                'used' => $aiUsed,
                'remaining' => $aiRemaining,
                'percent' => $aiAllowance > 0 ? min(100, round(($aiUsed / $aiAllowance) * 100)) : 0,
            ],
        ];
    }

    /**
     * Create a pending subscription payment order with a unique verification code.
     */
    public function createPaymentOrder(
        Business $business,
        User $user,
        string $cycle = 'monthly',
        string $paymentMethod = SubscriptionPayment::METHOD_BCA
    ): SubscriptionPayment {
        $isAnnual = $cycle === 'annual';
        $planCode = $isAnnual ? BusinessSubscription::PLAN_CORE_ANNUAL : BusinessSubscription::PLAN_CORE_MONTHLY;
        $baseAmount = $isAnnual ? $this->getAnnualPrice() : $this->getMonthlyPrice();

        // Generate 3-digit random code between 100 and 999
        $uniqueCode = random_int(100, 999);
        $totalPayable = $baseAmount + $uniqueCode;

        // Generate unique order number SUB-YYYYMM-XXXX
        $prefix = 'SUB-' . date('Ym') . '-';
        $latest = SubscriptionPayment::where('order_number', 'LIKE', $prefix . '%')
            ->orderByDesc('order_number')
            ->value('order_number');
        $seq = 1;
        if ($latest && preg_match('/-(\d+)$/', $latest, $matches)) {
            $seq = ((int) $matches[1]) + 1;
        }
        $orderNumber = $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);

        return SubscriptionPayment::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'order_number' => $orderNumber,
            'plan_code' => $planCode,
            'cycle' => $isAnnual ? 'annual' : 'monthly',
            'amount' => $baseAmount,
            'unique_code' => $uniqueCode,
            'total_payable' => $totalPayable,
            'payment_method' => $paymentMethod,
            'status' => SubscriptionPayment::STATUS_PENDING,
        ]);
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
        $path = $file->store('payment-proofs', 'public');

        $payment->update([
            'payment_proof_path' => $path,
            'sender_bank' => $senderData['sender_bank'] ?? null,
            'sender_account_name' => $senderData['sender_account_name'] ?? null,
            'sender_account_number' => $senderData['sender_account_number'] ?? null,
            'notes' => $senderData['notes'] ?? null,
            'proof_uploaded_at' => Carbon::now(),
            'status' => SubscriptionPayment::STATUS_AWAITING_APPROVAL,
        ]);

        return $payment->fresh();
    }

    /**
     * Admin approves subscription payment and automatically activates Core subscription.
     */
    public function approvePayment(
        SubscriptionPayment $payment,
        Admin $admin,
        ?string $adminNotes = null
    ): SubscriptionPayment {
        return DB::transaction(function () use ($payment, $admin, $adminNotes) {
            $payment->update([
                'status' => SubscriptionPayment::STATUS_APPROVED,
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now(),
                'admin_notes' => $adminNotes,
            ]);

            // Automatically upgrade business to Core
            $this->upgradeToCore($payment->business, $payment->cycle);

            return $payment->fresh();
        });
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
