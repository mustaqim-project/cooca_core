<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Template\ModuleRegistry;
use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class CheckModuleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
        $this->seed(RbacSeeder::class);

        $this->owner = User::create([
            'name'              => 'Owner Middleware Test',
            'email'             => 'owner_mid_' . Str::random(6) . '@test.local',
            'phone'             => '62812' . rand(10000000, 99999999),
            'password'          => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name'             => 'Bisnis Middleware Audit',
            'currency'         => 'IDR',
            'disabled_modules' => [
                ModuleRegistry::MODULE_STOREFRONT_CHECKOUT,
                ModuleRegistry::MODULE_INVENTORY_WAREHOUSE,
                ModuleRegistry::MODULE_LABOR_MACHINES,
            ],
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id'   => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
    }

    public function test_direct_url_access_to_storefront_is_blocked_when_module_is_disabled(): void
    {
        $response = $this->actingAs($this->owner, 'web')
            ->withSession([
                'active_business_id'           => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->owner->id,
            ])
            ->get(route('storefront.orders.index'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_api_access_to_storefront_returns_403_when_module_is_disabled(): void
    {
        $response = $this->actingAs($this->owner, 'web')
            ->withSession([
                'active_business_id'           => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->owner->id,
            ])
            ->getJson(route('storefront.orders.index'));

        $response->assertForbidden();
        $response->assertJson([
            'success'    => false,
            'error_code' => 'MODULE_DISABLED',
        ]);
    }

    public function test_direct_url_access_to_warehouse_is_blocked_when_module_is_disabled(): void
    {
        $response = $this->actingAs($this->owner, 'web')
            ->withSession([
                'active_business_id'           => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->owner->id,
            ])
            ->get(route('warehouse.index'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_direct_url_access_to_labor_machines_is_blocked_when_module_is_disabled(): void
    {
        $response = $this->actingAs($this->owner, 'web')
            ->withSession([
                'active_business_id'           => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->owner->id,
            ])
            ->get(route('labor-machines.index'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_access_is_allowed_when_module_is_enabled(): void
    {
        // Enable warehouse module
        $this->business->enableModule(ModuleRegistry::MODULE_INVENTORY_WAREHOUSE);
        $this->business->refresh();

        $response = $this->actingAs($this->owner, 'web')
            ->withSession([
                'active_business_id'           => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->owner->id,
            ])
            ->get(route('warehouse.index'));

        $response->assertOk();
    }
}
