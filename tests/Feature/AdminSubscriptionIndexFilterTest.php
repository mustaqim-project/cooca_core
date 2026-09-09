<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminSubscriptionIndexFilterTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private Business $business;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->admin = Admin::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@cooca.id',
            'password' => 'supersecret123',
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'name' => 'Budi Owner',
            'email' => 'budi@warungkopi.com',
            'password' => 'password123',
        ]);

        $this->business = Business::create([
            'name' => 'Warung Kopi Nusantara',
            'slug' => 'warung-kopi-nusantara',
            'currency' => 'IDR',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
    }

    public function test_admin_can_view_subscriptions_index_with_status_all(): void
    {
        // 1. Create a subscription payment order
        SubscriptionPayment::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'order_number' => 'SUB-202609-0001',
            'payment_type' => 'subscription',
            'plan_code' => 'core_monthly',
            'cycle' => 'monthly',
            'package_name' => 'Cooca Core Bulanan',
            'package_duration_days' => 30,
            'amount' => 129000,
            'unique_code' => 456,
            'total_payable' => 129456,
            'payment_method' => 'bca',
            'status' => SubscriptionPayment::STATUS_APPROVED,
            'approved_by' => $this->admin->id,
            'approved_at' => now(),
        ]);

        // 2. Create an AI token topup order
        SubscriptionPayment::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'order_number' => 'PKG-202609-0001',
            'payment_type' => 'ai_token',
            'plan_code' => 'topup',
            'package_name' => '1 Juta Token AI',
            'topup_quantity' => 1000000,
            'amount' => 50000,
            'unique_code' => 123,
            'total_payable' => 50123,
            'payment_method' => 'qris',
            'status' => SubscriptionPayment::STATUS_AWAITING_APPROVAL,
        ]);

        // 3. Create a pending storage topup order
        SubscriptionPayment::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'order_number' => 'PKG-202609-0002',
            'payment_type' => 'storage',
            'plan_code' => 'topup',
            'package_name' => 'Extra 10 GB Storage',
            'topup_storage_bytes' => 10737418240,
            'amount' => 30000,
            'unique_code' => 789,
            'total_payable' => 30789,
            'payment_method' => 'mandiri',
            'status' => SubscriptionPayment::STATUS_PENDING,
        ]);

        // Request with ?status=all
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.subscriptions.index', ['status' => 'all']));

        $response->assertStatus(200);
        $response->assertSee('SUB-202609-0001');
        $response->assertSee('PKG-202609-0001');
        $response->assertSee('PKG-202609-0002');

        // Check accurate package column
        $response->assertSee('Cooca Core Bulanan');
        $response->assertSee('1 Juta Token AI');
        $response->assertSee('+1.000.000 Token AI');
        $response->assertSee('Extra 10 GB Storage');
        $response->assertSee('+10 GB Storage');

        // Check accurate type badges
        $response->assertSee('Langganan SaaS');
        $response->assertSee('Topup Token AI');
        $response->assertSee('Topup Storage');

        // Check accurate tab badges
        $response->assertSee('Semua Status');
        $response->assertSee('Perlu Verifikasi');
        $response->assertSee('Disetujui');
        $response->assertSee('Belum Bayar (Pending)');
    }

    public function test_admin_can_filter_subscriptions_by_status_and_type(): void
    {
        SubscriptionPayment::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'order_number' => 'SUB-APP-001',
            'payment_type' => 'subscription',
            'plan_code' => 'core_monthly',
            'amount' => 129000,
            'total_payable' => 129000,
            'payment_method' => 'bca',
            'status' => SubscriptionPayment::STATUS_APPROVED,
        ]);

        SubscriptionPayment::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'order_number' => 'SUB-WAIT-002',
            'payment_type' => 'subscription',
            'plan_code' => 'core_monthly',
            'amount' => 129000,
            'total_payable' => 129000,
            'payment_method' => 'bca',
            'status' => SubscriptionPayment::STATUS_AWAITING_APPROVAL,
        ]);

        // Filter status=awaiting_approval
        $resWait = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.subscriptions.index', ['status' => 'awaiting_approval']));
        $resWait->assertStatus(200);
        $resWait->assertSee('SUB-WAIT-002');
        $resWait->assertDontSee('SUB-APP-001');

        // Filter status=approved
        $resApp = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.subscriptions.index', ['status' => 'approved']));
        $resApp->assertStatus(200);
        $resApp->assertSee('SUB-APP-001');
        $resApp->assertDontSee('SUB-WAIT-002');
    }

    public function test_admin_can_switch_to_tenant_subscriptions_view(): void
    {
        // Attach active subscription to business
        BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code' => BusinessSubscription::PLAN_CORE_MONTHLY,
            'price' => 129000,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => Carbon::now(),
            'ends_at' => Carbon::now()->addDays(20),
            'ai_tokens_monthly_allowance' => 10000000,
            'ai_tokens_remaining' => 8500000,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.subscriptions.index', ['view' => 'tenants']));

        $response->assertStatus(200);
        $response->assertSee('Status Langganan Tenant Bisnis');
        $response->assertSee('Warung Kopi Nusantara');
        $response->assertSee('Core Bulanan');
        $response->assertSee('8.500.000');
        $response->assertSee('Sisa');
    }
}
