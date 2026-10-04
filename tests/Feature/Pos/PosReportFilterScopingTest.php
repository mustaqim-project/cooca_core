<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Domain\Report\Pos\DTOs\PosReportFilterDTO;
use App\Models\Business;
use App\Models\Location;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tests\TestCase;

class PosReportFilterScopingTest extends TestCase
{
    use RefreshDatabase;

    private Business $businessA;
    private Business $businessB;
    private User $userA;
    private User $userB;
    private Location $locationA;
    private Location $locationB;
    private ProductCategory $categoryA;
    private ProductCategory $categoryB;
    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->businessA = Business::create([
            'name' => 'Business Tenant A',
            'slug' => 'tenant-a',
            'is_active' => true,
        ]);

        $this->businessB = Business::create([
            'name' => 'Business Tenant B',
            'slug' => 'tenant-b',
            'is_active' => true,
        ]);

        $this->userA = User::create([
            'name' => 'Cashier A',
            'email' => 'cashierA@example.com',
            'password' => bcrypt('password123'),
        ]);
        $this->userA->forceFill(['email_verified_at' => now(), 'active_business_id' => $this->businessA->id])->save();
        $this->businessA->users()->attach($this->userA->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->userB = User::create([
            'name' => 'Cashier B',
            'email' => 'cashierB@example.com',
            'password' => bcrypt('password123'),
        ]);
        $this->userB->forceFill(['email_verified_at' => now(), 'active_business_id' => $this->businessB->id])->save();
        $this->businessB->users()->attach($this->userB->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->locationA = Location::create([
            'business_id' => $this->businessA->id,
            'name' => 'Outlet A',
            'code' => 'OUT-A',
            'is_active' => true,
        ]);

        $this->locationB = Location::create([
            'business_id' => $this->businessB->id,
            'name' => 'Outlet B',
            'code' => 'OUT-B',
            'is_active' => true,
        ]);

        $this->categoryA = ProductCategory::create([
            'business_id' => $this->businessA->id,
            'name' => 'Category A',
            'slug' => 'cat-a',
            'is_active' => true,
        ]);

        $this->categoryB = ProductCategory::create([
            'business_id' => $this->businessB->id,
            'name' => 'Category B',
            'slug' => 'cat-b',
            'is_active' => true,
        ]);

        $this->unit = Unit::create([
            'business_id' => $this->businessA->id,
            'name' => 'Pcs',
            'code' => 'pcs',
            'symbol' => 'pcs',
            'category' => Unit::CATEGORY_QUANTITY,
            'is_active' => true,
        ]);
    }

    public function test_filter_preset_date_calculations(): void
    {
        // 1. Preset Today
        $reqToday = new Request(['preset' => 'today']);
        $filterToday = PosReportFilterDTO::fromRequest($reqToday, $this->businessA->id);
        $this->assertEquals(Carbon::today()->toDateString(), $filterToday->startDate->toDateString());
        $this->assertEquals(Carbon::today()->toDateString(), $filterToday->endDate->toDateString());

        // 2. Preset Yesterday
        $reqYest = new Request(['preset' => 'yesterday']);
        $filterYest = PosReportFilterDTO::fromRequest($reqYest, $this->businessA->id);
        $this->assertEquals(Carbon::yesterday()->toDateString(), $filterYest->startDate->toDateString());
        $this->assertEquals(Carbon::yesterday()->toDateString(), $filterYest->endDate->toDateString());

        // 3. Preset This Month
        $reqMonth = new Request(['preset' => 'this_month']);
        $filterMonth = PosReportFilterDTO::fromRequest($reqMonth, $this->businessA->id);
        $this->assertEquals(Carbon::today()->startOfMonth()->toDateString(), $filterMonth->startDate->toDateString());
    }

    public function test_controller_filters_cleanly_enforce_multi_tenant_anti_idor(): void
    {
        Context::setBusiness($this->businessA);
        session(['active_business_id' => $this->businessA->id]);

        $response = $this->actingAs($this->userA)->get(route('pos.reports.index', [
            'location_id' => $this->locationB->id, // Tenant B location!
            'category_id' => $this->categoryB->id, // Tenant B category!
            'user_id' => $this->userB->id,         // Tenant B user!
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('filter');
        
        /** @var PosReportFilterDTO $filter */
        $filter = $response->viewData('filter');
        
        // Assert anti-IDOR reset
        $this->assertNull($filter->locationId);
        $this->assertNull($filter->categoryId);
        $this->assertNull($filter->userId);
    }
}
