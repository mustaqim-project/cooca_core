<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SubscriptionExpiryAndCancellationTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name' => 'Owner Kedai Kopi',
            'email' => 'owner@kedaikopi.com',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone' => '6281234567899',
            'phone_verified_at' => now(),
        ]);

        $this->business = Business::create(['name' => 'Kedai Kopi Mantap']);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);
    }

    public function test_payment_pending_within_15_minutes_is_not_expired(): void
    {
        $payment = SubscriptionPayment::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'order_number' => 'ORD-EXP-001',
            'payment_type' => 'subscription',
            'plan_code' => 'standard',
            'cycle' => 'monthly',
            'amount' => 29000,
            'total_payable' => 29000,
            'payment_method' => SubscriptionPayment::METHOD_QRIS,
            'payment_gateway' => SubscriptionPayment::GATEWAY_TRIPAY,
            'gateway_reference' => 'DEV-T12345678',
            'gateway_qr_url' => 'https://tripay.co.id/qr/DEV-T12345678',
            'gateway_expired_at' => now()->addMinutes(15),
            'status' => SubscriptionPayment::STATUS_PENDING,
        ]);

        $this->assertFalse($payment->isExpired());
        $this->assertFalse($payment->isCancelled());
    }

    public function test_payment_is_automatically_cancelled_when_visited_after_15_minutes(): void
    {
        $this->actingAs($this->user);

        // Created 20 minutes ago
        $payment = SubscriptionPayment::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'order_number' => 'ORD-EXP-002',
            'payment_type' => 'subscription',
            'plan_code' => 'standard',
            'cycle' => 'monthly',
            'amount' => 29000,
            'total_payable' => 29000,
            'payment_method' => SubscriptionPayment::METHOD_QRIS,
            'payment_gateway' => SubscriptionPayment::GATEWAY_TRIPAY,
            'gateway_reference' => 'DEV-T99999999',
            'gateway_qr_url' => 'https://tripay.co.id/qr/DEV-T99999999',
            'gateway_expired_at' => now()->subMinutes(5), // expired 5 minutes ago
            'status' => SubscriptionPayment::STATUS_PENDING,
        ]);

        $this->assertTrue($payment->isExpired());

        // Visiting the payment page triggers auto-cancel
        $response = $this->get(route('billing.payment.show', $payment));
        $response->assertStatus(200);

        $payment->refresh();
        $this->assertEquals(SubscriptionPayment::STATUS_CANCELLED, $payment->status);
        $this->assertTrue($payment->isCancelled());

        // Check response sees expired banner and reorder button
        $response->assertSee('Tagihan Kadaluwarsa / Dibatalkan');
        $response->assertSee('Batas Waktu Pembayaran Telah Habis');
        $response->assertSee('Ajukan Pembayaran Ulang');
    }

    public function test_check_status_endpoint_returns_expired_flag_and_null_qr(): void
    {
        $this->actingAs($this->user);

        $payment = SubscriptionPayment::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'order_number' => 'ORD-EXP-003',
            'payment_type' => 'subscription',
            'plan_code' => 'standard',
            'cycle' => 'monthly',
            'amount' => 29000,
            'total_payable' => 29000,
            'payment_method' => SubscriptionPayment::METHOD_QRIS,
            'payment_gateway' => SubscriptionPayment::GATEWAY_TRIPAY,
            'gateway_reference' => 'DEV-T88888888',
            'gateway_qr_url' => 'https://tripay.co.id/qr/DEV-T88888888',
            'gateway_expired_at' => now()->subMinute(),
            'status' => SubscriptionPayment::STATUS_PENDING,
        ]);

        $response = $this->getJson(route('billing.payment.status', $payment));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'cancelled',
            'is_paid' => false,
            'is_cancelled' => true,
            'is_expired' => true,
            'has_qr' => false,
            'qr_url' => null,
        ]);
        $response->assertJsonStructure(['reorder_url']);
    }
}
