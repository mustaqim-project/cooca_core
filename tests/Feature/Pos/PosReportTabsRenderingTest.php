<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Models\Business;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\PosRegister;
use App\Models\PosShift;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PosReportTabsRenderingTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Location $location;
    private PosRegister $register;
    private ProductCategory $category;
    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->business = Business::create([
            'name' => 'Resto Berkah Nusantara',
            'slug' => 'resto-berkah-nusantara',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'name' => 'Owner Utama',
            'email' => 'owner@berkah.com',
            'password' => bcrypt('password123'),
        ]);
        $this->user->forceFill(['email_verified_at' => now(), 'active_business_id' => $this->business->id])->save();
        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        Context::setBusiness($this->business);
        session(['active_business_id' => $this->business->id]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Cabang Tebet',
            'code' => 'TBT-01',
            'is_active' => true,
        ]);

        $this->register = PosRegister::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'name' => 'POS Utama 1',
            'code' => 'POS-01',
            'is_active' => true,
        ]);

        $this->unit = Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Porsi',
            'code' => 'porsi',
            'symbol' => 'prs',
            'category' => Unit::CATEGORY_QUANTITY,
            'is_active' => true,
        ]);

        $this->category = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Aneka Minuman',
            'slug' => 'aneka-minuman',
            'is_active' => true,
        ]);

        // Create sample shift & order
        $shift = PosShift::create([
            'business_id' => $this->business->id,
            'pos_register_id' => $this->register->id,
            'location_id' => $this->location->id,
            'user_id' => $this->user->id,
            'opened_at' => Carbon::today()->setTime(8, 0),
            'closed_at' => Carbon::today()->setTime(17, 0),
            'opening_cash' => 100000.0,
            'closing_cash_expected' => 200000.0,
            'closing_cash_actual' => 200000.0,
            'cash_difference' => 0.0,
            'total_cash_sales' => 100000.0,
            'status' => PosShift::STATUS_CLOSED,
        ]);

        $prod = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Es Kopi Susu',
            'code' => 'KOP-SUSU',
            'output_unit_id' => $this->unit->id,
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'pos_shift_id' => $shift->id,
            'user_id' => $this->user->id,
            'order_number' => 'ORD-TEST-001',
            'order_date' => Carbon::today()->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 50000.0,
            'total_amount' => 50000.0,
            'paid_amount' => 50000.0,
        ]);

        PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_id' => $prod->id,
            'product_name' => $prod->name,
            'product_code' => $prod->code,
            'unit_price' => 25000.0,
            'unit_cost_hpp' => 10000.0,
            'quantity' => 2,
            'subtotal' => 50000.0,
            'total_price' => 50000.0,
            'total_hpp' => 20000.0,
        ]);

        PosOrderPayment::create([
            'pos_order_id' => $order->id,
            'payment_method' => PosOrderPayment::METHOD_CASH,
            'amount' => 50000.0,
            'status' => 'paid',
        ]);
    }

    public function test_all_15_tabs_render_successfully(): void
    {
        $tabs = [
            'overview',
            'transactions',
            'products',
            'categories',
            'cashiers',
            'outlets',
            'payments',
            'discounts',
            'refunds',
            'voids',
            'shifts',
            'hourly',
            'customers',
            'channels',
            'profitability',
        ];

        foreach ($tabs as $tab) {
            $response = $this->actingAs($this->user)->get(route('pos.reports.index', ['tab' => $tab]));
            $response->assertStatus(200);
            $response->assertViewIs('app.pos.reports');
            $response->assertViewHas('activeTab', $tab);
        }
    }
}
