<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerWebFeatureTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->owner = User::create([
            'name' => 'Owner Commercial',
            'email' => 'commercial-owner@cooca.test',
            'password' => 'password',
        ]);

        $this->business = Business::create(['name' => 'PT Distribusi Niaga Jaya']);
        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);
    }

    public function test_owner_can_view_customers_index_page(): void
    {
        Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Ahmad Zaki',
            'company_name' => 'PT Maju Bersama Sentosa',
            'code' => 'CUST-001',
            'phone' => '081288889999',
            'email' => 'zaki@majubersama.com',
            'billing_address' => 'Gedung Cyber 2 Lt 10 Jakarta',
            'shipping_address' => 'Gudang Pulogadung',
            'tax_identification_number' => '01.234.567.8-901.000',
            'payment_terms_days' => 30,
            'notes' => 'Klien prioritas B2B',
        ]);

        $response = $this->actingAs($this->owner)->get(route('customers.index'));

        $response->assertOk();
        $response->assertSee('Direktori Klien &amp; Pelanggan', false);
        $response->assertSee('Ahmad Zaki');
        $response->assertSee('PT Maju Bersama Sentosa');
        $response->assertSee('CUST-001');
        $response->assertSee('Net 30 Hari');
    }

    public function test_owner_can_search_customers(): void
    {
        Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Bambang Sudiro',
            'company_name' => 'CV Sumber Rezeki',
            'code' => 'CUST-BAMBANG',
        ]);

        Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Citra Lestari',
            'company_name' => 'Toko Citra Abadi',
            'code' => 'CUST-CITRA',
        ]);

        $response = $this->actingAs($this->owner)->get(route('customers.index', [
            'search' => 'Bambang',
        ]));

        $response->assertOk();
        $response->assertSee('Bambang Sudiro');
        $response->assertSee('CV Sumber Rezeki');
        $response->assertDontSee('Citra Lestari');
    }

    public function test_owner_can_store_new_customer(): void
    {
        $response = $this->actingAs($this->owner)->post(route('customers.store'), [
            'name' => 'Dedi Kurniawan',
            'company_name' => 'PT Logistik Prima Nusantara',
            'code' => 'CUST-DEDI',
            'email' => 'dedi@prima.co.id',
            'phone' => '081377778888',
            'billing_address' => 'Jl. Sudirman No. 45 Jakarta Pusat',
            'shipping_address' => 'Kawasan Industri MM2100 Cikarang',
            'tax_identification_number' => '02.456.789.0-123.000',
            'payment_terms_days' => 45,
            'notes' => 'Termin khusus 45 hari tempo',
        ]);

        $response->assertRedirect(route('customers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('customers', [
            'business_id' => $this->business->id,
            'name' => 'Dedi Kurniawan',
            'company_name' => 'PT Logistik Prima Nusantara',
            'code' => 'CUST-DEDI',
            'email' => 'dedi@prima.co.id',
            'payment_terms_days' => 45,
        ]);
    }

    public function test_owner_can_update_customer(): void
    {
        $customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Eko Prasetyo',
            'company_name' => 'CV Karya Gemilang',
            'phone' => '081512345678',
            'payment_terms_days' => 14,
        ]);

        $response = $this->actingAs($this->owner)->put(route('customers.update', $customer), [
            'name' => 'Eko Prasetyo M.Sc',
            'company_name' => 'PT Karya Gemilang Sukses',
            'phone' => '081512349999',
            'payment_terms_days' => 60,
        ]);

        $response->assertRedirect(route('customers.index'));
        $response->assertSessionHas('success');

        $customer->refresh();
        $this->assertEquals('Eko Prasetyo M.Sc', $customer->name);
        $this->assertEquals('PT Karya Gemilang Sukses', $customer->company_name);
        $this->assertEquals('081512349999', $customer->phone);
        $this->assertEquals(60, $customer->payment_terms_days);
    }

    public function test_owner_can_delete_customer(): void
    {
        $customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Fajar Nugraha',
            'phone' => '081699990000',
        ]);

        $response = $this->actingAs($this->owner)->delete(route('customers.destroy', $customer));

        $response->assertRedirect(route('customers.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('customers', [
            'id' => $customer->id,
        ]);
    }
}
