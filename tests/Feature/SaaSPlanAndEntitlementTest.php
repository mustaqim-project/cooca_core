<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Billing\EntitlementService;
use App\Models\Admin;
use App\Models\Business;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

class SaaSPlanAndEntitlementTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Unit $unit;
    private ProductCategory $category;
    private EntitlementService $entitlementService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name' => 'Owner SaaS',
            'email' => 'owner_saas@example.com',
            'password' => 'password123',
        ]);

        $this->business = Business::create(['name' => 'Cooca Bakery SaaS']);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->unit = Unit::create([
            'business_id' => $this->business->id,
            'code' => 'PCS',
            'name' => 'Pieces',
            'symbol' => 'pcs',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);

        $this->category = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Pastry',
            'slug' => 'pastry',
        ]);

        $this->entitlementService = new EntitlementService();
    }

    public function test_new_business_defaults_to_free_plan(): void
    {
        $sub = $this->entitlementService->getSubscription($this->business);

        $this->assertEquals('free', $sub->plan_code);
        $this->assertTrue($sub->isFreePlan());
        $this->assertFalse($sub->isCorePlan());
        $this->assertTrue($this->entitlementService->canCreateProduct($this->business));
        $this->assertFalse($this->entitlementService->canAccessAi($this->business));
    }

    public function test_free_plan_blocks_product_creation_at_fifty_items(): void
    {
        // Create 50 products
        for ($i = 1; $i <= 50; $i++) {
            Product::create([
                'business_id' => $this->business->id,
                'category_id' => $this->category->id,
                'output_unit_id' => $this->unit->id,
                'code' => "PRD-{$i}",
                'name' => "Produk Ke {$i}",
                'selling_price' => 10000,
                'base_cost' => 5000,
                'is_active' => true,
            ]);
        }

        $this->assertFalse($this->entitlementService->canCreateProduct($this->business));

        // When requesting via web controller, middleware redirects
        $this->actingAs($this->user);
        session(['active_business_id' => $this->business->id]);

        $response = $this->post(route('products.store'), [
            'name' => 'Produk ke-51',
            'selling_price' => 20000,
        ]);

        $response->assertRedirect(route('billing.limits'));
        $response->assertSessionHas('error');
    }

    public function test_upgrade_to_core_unlocks_unlimited_and_ai_tokens(): void
    {
        // 50 products exist
        for ($i = 1; $i <= 50; $i++) {
            Product::create([
                'business_id' => $this->business->id,
                'category_id' => $this->category->id,
                'output_unit_id' => $this->unit->id,
                'code' => "PRD-{$i}",
                'name' => "Produk Ke {$i}",
                'selling_price' => 10000,
                'base_cost' => 5000,
                'is_active' => true,
            ]);
        }

        $sub = $this->entitlementService->upgradeToCore($this->business, 'monthly');

        $this->assertTrue($sub->isCorePlan());
        $this->assertEquals(0, $sub->ai_tokens_monthly_allowance);
        $this->assertTrue($this->entitlementService->canCreateProduct($this->business));

        // AI requires top up
        $this->assertFalse($this->entitlementService->canAccessAi($this->business));

        $admin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@cooca.id',
            'password' => 'secret123',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $topup = $this->entitlementService->createTokenTopupOrder($this->business, $this->user);
        $this->entitlementService->submitPaymentProof($topup, UploadedFile::fake()->image('proof.png'));
        $this->entitlementService->approvePayment($topup, $admin);

        $this->assertTrue($this->entitlementService->canAccessAi($this->business));

        // Deduct AI tokens
        $deducted = $this->entitlementService->deductAiTokens($this->business, 2500, 'sales_query', $this->user);
        $this->assertTrue($deducted);

        $this->assertDatabaseHas('ai_token_usages', [
            'business_id' => $this->business->id,
            'total_tokens' => 2500,
        ]);
    }

    public function test_billing_limits_view_is_accessible(): void
    {
        $this->actingAs($this->user);
        session(['active_business_id' => $this->business->id]);

        $response = $this->get(route('billing.limits'));
        $response->assertStatus(200);
        $response->assertSee('Paket Langganan & Kuota Bisnis', false);
        $response->assertSee('No Data Punishment');
    }
}
