<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AiTokenUsage;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminPlatformManagementTest extends TestCase
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

    public function test_admin_dashboard_renders_with_saas_financial_metrics_and_token_stats(): void
    {
        // Add active core monthly subscription
        BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code' => BusinessSubscription::PLAN_CORE_MONTHLY,
            'price' => 129000,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => Carbon::now(),
            'ends_at' => Carbon::now()->addMonth(),
            'ai_tokens_monthly_allowance' => 10000000,
            'ai_tokens_remaining' => 9500000,
        ]);

        // Add pending payment approval
        SubscriptionPayment::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'order_number' => 'SUB-202608-9999',
            'plan_code' => 'core_monthly',
            'billing_cycle' => 'monthly',
            'base_price' => 129000,
            'verification_unique_code' => 123,
            'total_amount' => 129123,
            'payment_channel' => 'manual_transfer',
            'bank_destination' => 'BCA',
            'status' => SubscriptionPayment::STATUS_AWAITING_APPROVAL,
            'payment_proof_path' => 'proofs/test.jpg',
            'proof_uploaded_at' => Carbon::now(),
        ]);

        // Add AI token usage
        AiTokenUsage::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'model_name' => 'gemini-2.5-flash',
            'input_tokens' => 1500,
            'output_tokens' => 500,
            'total_tokens' => 2000,
            'intent' => 'sales_forecasting',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Statistik &amp; Ringkasan Platform SaaS', false);
        $response->assertSee('MRR (Monthly Recurring)');
        $response->assertSee('129.000');
        $response->assertSee('Verifikasi Pembayaran Diperlukan');
        $response->assertSee('Token AI Global');
        $response->assertSee('Warung Kopi Nusantara');
    }

    public function test_admin_can_view_businesses_list_and_filter(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.businesses.index'));

        $response->assertStatus(200);
        $response->assertSee('Manajemen Tenant Bisnis UMKM');
        $response->assertSee('Warung Kopi Nusantara');

        // Test with search filter
        $searchResponse = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.businesses.index', ['search' => 'Warung Kopi']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Warung Kopi Nusantara');
    }

    public function test_admin_can_view_business_detail_and_toggle_status(): void
    {
        $showResponse = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.businesses.show', $this->business->id));

        $showResponse->assertStatus(200);
        $showResponse->assertSee('Workspace: Warung Kopi Nusantara');
        $showResponse->assertSee('Tangguhkan Workspace (Suspend)');

        // Suspend business
        $toggleResponse = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.businesses.toggle-status', $this->business->id), [
                'reason' => 'Verifikasi dokumen',
            ]);

        $toggleResponse->assertRedirect();
        $this->assertFalse((bool) $this->business->fresh()->is_active);
        $this->assertSame('Verifikasi dokumen', $this->business->fresh()->suspended_reason);

        // Reactivate business
        $reactivateResponse = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.businesses.toggle-status', $this->business->id));

        $reactivateResponse->assertRedirect();
        $this->assertTrue((bool) $this->business->fresh()->is_active);
        $this->assertNull($this->business->fresh()->suspended_reason);
    }

    public function test_admin_can_view_ai_token_monitoring_dashboard(): void
    {
        AiTokenUsage::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'model_name' => 'gemini-2.5-flash',
            'input_tokens' => 2500,
            'output_tokens' => 500,
            'total_tokens' => 3000,
            'intent' => 'anomaly_fraud_detection',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.ai-tokens.index'));

        $response->assertStatus(200);
        $response->assertSee('Monitoring Token AI Google Gemini');
        $response->assertSee('anomaly fraud detection');
        $response->assertSee('Warung Kopi Nusantara');
    }
}
