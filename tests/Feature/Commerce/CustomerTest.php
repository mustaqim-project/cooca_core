<?php

declare(strict_types=1);

namespace Tests\Feature\Commerce;

use App\Models\Business;
use App\Models\Customer;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class CustomerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);

        $this->user = User::create([
            'name' => 'Owner Bisnis',
            'email' => 'owner@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->business = Business::create([
            'name' => 'PT Manufaktur Sukses',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
        ]);

        $this->business->users()->attach($this->user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $this->user->update(['active_business_id' => $this->business->id]);
    }

    public function test_can_list_customers(): void
    {
        Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Budi Santoso',
            'company_name' => 'PT Maju Terus',
            'email' => 'budi@majuterus.com',
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->get('/customers');

        $response->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('PT Maju Terus');
    }

    public function test_can_create_customer(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->post('/customers', [
                'name' => 'Siti Nurhaliza',
                'company_name' => 'CV Berkah Bersama',
                'code' => 'CUST-001',
                'email' => 'siti@berkah.com',
                'phone' => '08123456789',
                'billing_address' => 'Jl. Sudirman No. 10 Jakarta',
                'payment_terms_days' => 45,
            ]);

        $response->assertRedirect('/customers');

        $this->assertDatabaseHas('customers', [
            'business_id' => $this->business->id,
            'name' => 'Siti Nurhaliza',
            'company_name' => 'CV Berkah Bersama',
            'code' => 'CUST-001',
            'payment_terms_days' => 45,
        ]);
    }

    public function test_can_update_customer(): void
    {
        $customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Nama Lama',
            'company_name' => 'PT Lama',
            'payment_terms_days' => 14,
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->put("/customers/{$customer->slug}", [
                'name' => 'Nama Baru Diperbarui',
                'company_name' => 'PT Baru Sukses',
                'payment_terms_days' => 30,
            ]);

        $response->assertRedirect('/customers');

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Nama Baru Diperbarui',
            'company_name' => 'PT Baru Sukses',
            'payment_terms_days' => 30,
        ]);
    }

    public function test_can_delete_customer(): void
    {
        $customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Customer Hapus',
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->delete("/customers/{$customer->slug}");

        $response->assertRedirect('/customers');
        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    public function test_tenant_isolation_customers(): void
    {
        $otherBiz = Business::create(['name' => 'Bisnis Lain']);
        $otherCustomer = Customer::create([
            'business_id' => $otherBiz->id,
            'name' => 'Rahasia Bisnis Lain',
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->get('/customers');

        $response->assertOk()
            ->assertDontSee('Rahasia Bisnis Lain');
    }
}
