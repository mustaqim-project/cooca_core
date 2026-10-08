<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Business;
use App\Models\GlobalCustomer;
use App\Models\MobileDeviceToken;
use App\Models\PosOrder;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class MobileDeviceAndSyncApiTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private User $cashier;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->owner = User::create([
            'name' => 'Owner Bisnis',
            'email' => 'owner@device-test.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $this->cashier = User::create([
            'name' => 'Kasir Handal',
            'email' => 'kasir@device-test.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'Kopi Mantap Jiwa',
            'slug' => 'kopi-mantap-jiwa',
            'email' => 'kopi@mantap.com',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->cashier->id, [
            'id' => (string) Str::uuid(),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
        $this->cashier->update(['active_business_id' => $this->business->id]);

        $unit = Unit::create([
            'business_id' => $this->business->id,
            'code' => 'CUP',
            'name' => 'Cup',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Kopi Susu Gula Aren',
            'code' => 'KOP-001',
            'selling_price' => 18000,
            'base_cost' => 8000,
            'output_unit_id' => $unit->id,
            'is_active' => true,
            'min_stock' => 10,
        ]);
    }

    public function test_b2b_user_can_register_and_unregister_device_fcm_token(): void
    {
        Sanctum::actingAs($this->owner);
        Context::setBusiness($this->business);

        $tokenPayload = [
            'token' => 'fcm_b2b_sample_token_xyz_12345',
            'platform' => 'android',
            'device_model' => 'Samsung Galaxy S24 Ultra',
            'app_version' => '1.0.0',
        ];

        // 1. Register Token
        $response = $this->postJson('/api/v1/devices/fcm-token', $tokenPayload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.platform', 'android')
            ->assertJsonPath('data.app_type', 'cooca_my_own');

        $this->assertDatabaseHas('mobile_device_tokens', [
            'user_id' => $this->owner->id,
            'business_id' => $this->business->id,
            'token' => 'fcm_b2b_sample_token_xyz_12345',
            'is_active' => true,
            'app_type' => 'cooca_my_own',
        ]);

        // 2. Unregister Token
        $unregisterResponse = $this->deleteJson('/api/v1/devices/fcm-token', [
            'token' => 'fcm_b2b_sample_token_xyz_12345',
        ]);

        $unregisterResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('mobile_device_tokens', [
            'token' => 'fcm_b2b_sample_token_xyz_12345',
            'is_active' => false,
        ]);
    }

    public function test_customer_can_register_and_unregister_device_fcm_token(): void
    {
        $customer = GlobalCustomer::create([
            'name' => 'Budi Marketplace',
            'email' => 'budi@marketplace-test.com',
            'phone' => '081298765432',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        Sanctum::actingAs($customer);

        $tokenPayload = [
            'token' => 'fcm_b2c_customer_token_abc_67890',
            'platform' => 'ios',
            'device_model' => 'iPhone 16 Pro',
            'app_version' => '1.2.0',
        ];

        // 1. Register Token
        $response = $this->postJson('/api/v1/customer/devices/fcm-token', $tokenPayload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.platform', 'ios')
            ->assertJsonPath('data.app_type', 'cooca_customer');

        $this->assertDatabaseHas('mobile_device_tokens', [
            'global_customer_id' => $customer->id,
            'token' => 'fcm_b2c_customer_token_abc_67890',
            'is_active' => true,
            'app_type' => 'cooca_customer',
        ]);

        // 2. Unregister Token
        $unregisterResponse = $this->deleteJson('/api/v1/customer/devices/fcm-token', [
            'token' => 'fcm_b2c_customer_token_abc_67890',
        ]);

        $unregisterResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('mobile_device_tokens', [
            'token' => 'fcm_b2c_customer_token_abc_67890',
            'is_active' => false,
        ]);
    }

    public function test_cashier_can_batch_sync_offline_pos_orders_with_idempotency(): void
    {
        Sanctum::actingAs($this->cashier);
        Context::setBusiness($this->business);

        $clientUuid = (string) Str::uuid();

        $batchPayload = [
            'transactions' => [
                [
                    'client_uuid' => $clientUuid,
                    'offline_created_at' => now()->toDateTimeString(),
                    'order_type' => 'dine_in',
                    'table_or_reference' => 'Meja VIP 01',
                    'customer_name_guest' => 'Tamu Offline',
                    'items' => [
                        [
                            'product_id' => $this->product->id,
                            'product_name' => $this->product->name,
                            'unit_price' => 18000,
                            'quantity' => 2,
                            'discount_amount' => 0,
                        ],
                    ],
                    'payments' => [
                        [
                            'payment_method' => 'cash',
                            'amount' => 36000,
                        ],
                    ],
                ],
            ],
        ];

        // 1. Sinkronisasi Pertama: Berhasil Disimpan
        $response1 = $this->postJson('/api/v1/pos/sync/batch', $batchPayload);

        $response1->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('summary.total_submitted', 1)
            ->assertJsonPath('summary.total_synced', 1)
            ->assertJsonPath('summary.total_duplicates', 0)
            ->assertJsonPath('results.0.status', 'synced');

        $this->assertDatabaseHas('pos_orders', [
            'business_id' => $this->business->id,
            'client_uuid' => $clientUuid,
            'table_or_reference' => 'Meja VIP 01',
        ]);

        // 2. Sinkronisasi Kedua dengan client_uuid sama: Idempotent Skip
        $response2 = $this->postJson('/api/v1/pos/sync/batch', $batchPayload);

        $response2->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('summary.total_submitted', 1)
            ->assertJsonPath('summary.total_synced', 0)
            ->assertJsonPath('summary.total_duplicates', 1)
            ->assertJsonPath('results.0.status', 'duplicate_skipped');
    }

    public function test_owner_can_view_real_time_pulse_dashboard_metrics(): void
    {
        Sanctum::actingAs($this->owner);
        Context::setBusiness($this->business);

        // Buat pesanan contoh hari ini
        PosOrder::create([
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'order_number' => 'POS-20261008-0001',
            'order_date' => now()->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'total_amount' => 54000,
            'paid_amount' => 54000,
            'total_hpp_cost' => 24000,
            'total_gross_profit' => 30000,
        ]);

        $response = $this->getJson('/api/v1/mobile/dashboard/pulse');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.business.id', $this->business->id)
            ->assertJsonPath('data.sales_today.total_transactions', 1)
            ->assertJsonPath('data.sales_today.total_revenue', 54000)
            ->assertJsonPath('data.profitability.gross_profit', 30000)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'business',
                    'sales_today' => ['total_revenue', 'total_transactions', 'avg_ticket', 'growth_pct'],
                    'profitability' => ['gross_profit', 'margin_pct'],
                    'operations',
                    'critical_low_stocks',
                    'hourly_trend',
                ],
            ]);
    }
}
