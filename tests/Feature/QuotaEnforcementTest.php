<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Billing\EntitlementService;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Material;
use App\Models\PosOrder;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\QuotaMonthlyUsage;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class QuotaEnforcementTest extends TestCase
{
    use RefreshDatabase;

    private function createOwnerAndBusiness(string $plan = 'free'): array
    {
        $owner = User::create([
            'name' => 'Test Owner',
            'email' => 'owner_' . Str::random(5) . '@test.com',
            'password' => 'password123',
        ]);

        $business = Business::create([
            'name' => 'UMKM Kopi Mantap',
            'currency' => 'IDR',
        ]);

        $business->users()->attach($owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $owner->update(['active_business_id' => $business->id]);

        if ($plan === 'core') {
            app(EntitlementService::class)->upgradeToCore($business, 'monthly');
        }

        return [$owner, $business];
    }

    public function test_free_plan_restricts_creating_second_business(): void
    {
        [$owner, $business] = $this->createOwnerAndBusiness('free');

        $response = $this->actingAs($owner)
            ->post(route('businesses.store'), [
                'name' => 'Cabang Kedua',
            ]);

        $response->assertRedirect(route('billing.limits'));
        $response->assertSessionHas('error');
        $this->assertEquals(1, $owner->businesses()->count());
    }

    public function test_free_plan_restricts_export_excel(): void
    {
        [$owner, $business] = $this->createOwnerAndBusiness('free');

        $response = $this->actingAs($owner)
            ->withSession(['active_business_id' => $business->id])
            ->get(route('reports.export-excel'));

        $response->assertRedirect(route('billing.limits'));
        $response->assertSessionHas('error');
    }

    public function test_core_plan_allows_export_excel(): void
    {
        [$owner, $business] = $this->createOwnerAndBusiness('core');

        $response = $this->actingAs($owner)
            ->withSession(['active_business_id' => $business->id])
            ->get(route('reports.export-excel'));

        $response->assertOk();
    }

    public function test_pos_monthly_quota_enforcement(): void
    {
        [$owner, $business] = $this->createOwnerAndBusiness('free');

        // Set quota usage to limit
        QuotaMonthlyUsage::create([
            'business_id' => $business->id,
            'resource_type' => QuotaMonthlyUsage::TYPE_POS,
            'year' => now()->year,
            'month' => now()->month,
            'usage_count' => EntitlementService::FREE_POS_MONTHLY_LIMIT,
        ]);

        $entitlement = app(EntitlementService::class);
        $this->assertFalse($entitlement->canCreatePosTransactionThisMonth($business));
    }

    public function test_po_monthly_quota_enforcement(): void
    {
        [$owner, $business] = $this->createOwnerAndBusiness('free');

        // Set quota usage to limit
        QuotaMonthlyUsage::create([
            'business_id' => $business->id,
            'resource_type' => QuotaMonthlyUsage::TYPE_PO,
            'year' => now()->year,
            'month' => now()->month,
            'usage_count' => EntitlementService::FREE_PO_MONTHLY_LIMIT,
        ]);

        $entitlement = app(EntitlementService::class);
        $this->assertFalse($entitlement->canCreatePurchaseOrderThisMonth($business));
    }

    public function test_usage_summary_returns_all_resources_accurately(): void
    {
        [$owner, $business] = $this->createOwnerAndBusiness('free');

        $summary = app(EntitlementService::class)->getUsageSummary($business);

        $this->assertFalse($summary['is_core']);
        $this->assertEquals(50, $summary['products']['limit']);
        $this->assertEquals(20, $summary['materials']['limit']);
        $this->assertEquals(20, $summary['recipes']['limit']);
        $this->assertEquals(30, $summary['customers']['limit']);
        $this->assertEquals(20, $summary['suppliers']['limit']);
        $this->assertEquals(10, $summary['invoices_this_month']['limit']);
        $this->assertEquals(10, $summary['po_this_month']['limit']);
        $this->assertEquals(100, $summary['pos_this_month']['limit']);
        $this->assertEquals(1, $summary['businesses']['limit']);
        $this->assertEquals(1, $summary['users']['limit']);
    }
}
