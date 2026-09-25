<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Location;
use App\Models\Permission;
use App\Models\PosOrder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AnalyticsWebTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private Location $outlet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::create([
            'name' => 'Warkop Bento Nusantara',
            'business_scale' => 'umkm',
        ]);

        $this->owner = User::create([
            'name' => 'Owner Bento',
            'email' => 'owner@bento.test',
            'password' => 'password123',
        ]);

        $ownerRole = Role::firstOrCreate(
            ['slug' => 'owner', 'business_id' => null],
            ['name' => 'Owner', 'description' => 'Owner with full access']
        );

        $perm = Permission::firstOrCreate(
            ['slug' => 'reports.view'],
            ['name' => 'View Reports', 'description' => 'View business reports and analytics']
        );
        $ownerRole->permissions()->syncWithoutDetaching([$perm->id]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole->id,
        ]);
        $this->owner->update(['active_business_id' => $this->business->id]);

        $this->outlet = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Utama',
            'type' => 'outlet',
            'is_active' => true,
        ]);
    }

    public function test_analytics_page_renders_successfully(): void
    {
        PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->outlet->id,
            'user_id' => $this->owner->id,
            'order_number' => 'POS-2026-001',
            'order_date' => now()->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'total_amount' => 150000,
            'paid_amount' => 150000,
            'total_hpp_cost' => 80000,
            'total_gross_profit' => 70000,
            'created_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('analytics.index'));

        $response->assertOk();
        $response->assertSee('Analitik Bisnis &amp; Tren Pertumbuhan', false);
        $response->assertSee('Total Omzet Penjualan', false);
        $response->assertSee('Laba Kotor (Gross Profit)', false);
        $response->assertSee('Metode Pembayaran', false);
    }

    public function test_analytics_json_api_returns_structured_metrics(): void
    {
        PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->outlet->id,
            'user_id' => $this->owner->id,
            'order_number' => 'POS-2026-002',
            'order_date' => now()->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'total_amount' => 500000,
            'paid_amount' => 500000,
            'total_hpp_cost' => 250000,
            'total_gross_profit' => 250000,
            'created_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('analytics.index', ['period' => 'today']));

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.period', 'today');
        $this->assertEquals(500000, $response->json('data.current.revenue'));
        $this->assertEquals(250000, $response->json('data.current.gross_profit'));
    }

    public function test_analytics_handles_custom_date_range(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('analytics.index', [
                'period' => 'custom',
                'from' => '2026-01-01',
                'to' => '2026-01-31',
            ]));

        $response->assertOk();
        $response->assertSee('2026-01-01', false);
        $response->assertSee('2026-01-31', false);
    }
}
