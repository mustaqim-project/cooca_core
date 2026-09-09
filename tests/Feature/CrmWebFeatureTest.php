<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\CustomerPointHistory;
use App\Models\User;
use App\Models\Voucher;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CrmWebFeatureTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->owner = User::create([
            'name' => 'Owner CRM',
            'email' => 'crm-owner@cooca.test',
            'password' => 'password',
        ]);

        $this->business = Business::create(['name' => 'Toko Retail Makmur']);
        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);
    }

    public function test_owner_can_view_crm_members_page(): void
    {
        Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'email' => 'budi@test.local',
            'membership_tier' => 'gold',
            'segment' => 'vip',
            'points_balance' => 1250,
            'total_spent' => 2500000,
            'current_credit_balance' => 150000,
        ]);

        $response = $this->actingAs($this->owner)->get(route('crm.members.index'));

        $response->assertOk();
        $response->assertSee('CRM & Membership Pelanggan');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Gold');
        $response->assertSee('VIP');
        $response->assertSee('1.250');
        $response->assertSee('150.000');
    }

    public function test_crm_members_filter_by_search_and_tier(): void
    {
        Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Andi Platinum',
            'phone' => '0811111111',
            'membership_tier' => 'platinum',
        ]);
        Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Citra Bronze',
            'phone' => '0822222222',
            'membership_tier' => 'bronze',
        ]);

        $response = $this->actingAs($this->owner)->get(route('crm.members.index', [
            'search' => 'Andi',
            'tier' => 'platinum',
        ]));

        $response->assertOk();
        $response->assertSee('Andi Platinum');
        $response->assertDontSee('Citra Bronze');
    }

    public function test_owner_can_view_customer_point_histories_api(): void
    {
        $customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Dewi Pelanggan',
            'phone' => '0833333333',
            'points_balance' => 500,
        ]);

        CustomerPointHistory::create([
            'business_id' => $this->business->id,
            'customer_id' => $customer->id,
            'points_change' => 500,
            'type' => CustomerPointHistory::TYPE_POS_EARN,
            'notes' => 'Pembelian Kasir POS #INV-001',
        ]);

        $response = $this->actingAs($this->owner)->getJson(route('crm.customers.points', $customer));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonFragment([
            'notes' => 'Pembelian Kasir POS #INV-001',
        ]);
    }

    public function test_owner_can_record_credit_repayment(): void
    {
        $customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Eko Tempo',
            'phone' => '0844444444',
            'current_credit_balance' => 200000,
        ]);

        $response = $this->actingAs($this->owner)->post(route('crm.customers.credit-payment', $customer), [
            'amount' => 100000,
            'notes' => 'Cicilan 1 transfer BCA',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $customer->refresh();
        $this->assertEquals(100000, (float) $customer->current_credit_balance);
    }

    public function test_owner_can_view_vouchers_index_page(): void
    {
        Voucher::create([
            'business_id' => $this->business->id,
            'code' => 'PROMOHEBAT',
            'name' => 'Diskon Pembukaan 20%',
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'min_order_amount' => 50000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->owner)->get(route('crm.vouchers.index'));

        $response->assertOk();
        $response->assertSee('Voucher & Kode Promo Kasir');
        $response->assertSee('PROMOHEBAT');
        $response->assertSee('Diskon Pembukaan 20%');
        $response->assertSee('20%');
    }

    public function test_owner_can_store_and_toggle_voucher(): void
    {
        $response = $this->actingAs($this->owner)->post(route('crm.vouchers.store'), [
            'code' => 'HEMAT50K',
            'name' => 'Potongan 50 Ribu',
            'discount_type' => 'fixed',
            'discount_value' => 50000,
            'min_order_amount' => 100000,
            'tier_eligibility' => 'gold',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('vouchers', [
            'business_id' => $this->business->id,
            'code' => 'HEMAT50K',
            'discount_value' => 50000,
            'tier_eligibility' => 'gold',
            'is_active' => true,
        ]);

        $voucher = Voucher::where('code', 'HEMAT50K')->firstOrFail();

        // Toggle to inactive
        $toggleResponse = $this->actingAs($this->owner)->post(route('crm.vouchers.toggle', $voucher));
        $toggleResponse->assertRedirect();
        $voucher->refresh();
        $this->assertFalse((bool) $voucher->is_active);
    }
}
