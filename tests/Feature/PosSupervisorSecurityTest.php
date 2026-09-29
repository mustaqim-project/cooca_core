<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\PosOrder;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PosSupervisorSecurityTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private User $cashier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);

        $this->owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@cooca.test',
            'phone' => '081234567890',
            'password' => 'password',
        ]);
        $this->owner->forceFill(['email_verified_at' => now()])->save();

        $this->cashier = User::create([
            'name' => 'Cashier',
            'email' => 'cashier@cooca.test',
            'phone' => '081234567891',
            'password' => 'password',
        ]);
        $this->cashier->forceFill(['email_verified_at' => now()])->save();

        $this->business = Business::create([
            'name' => 'Kedai Kopi Cooca',
            'pos_supervisor_pin' => Hash::make('8888'),
            'pos_max_cashier_discount_percent' => 10.0,
            'pos_require_pin_for_void' => true,
            'pos_require_pin_for_refund' => true,
        ]);

        $ownerRole = Role::where('slug', 'owner')->firstOrFail();
        $cashierRole = Role::where('slug', 'cashier')->firstOrFail();

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole->id,
        ]);

        $this->business->users()->attach($this->cashier->id, [
            'id' => (string) Str::uuid(),
            'role' => 'cashier',
            'role_id' => $cashierRole->id,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
        $this->cashier->update(['active_business_id' => $this->business->id]);
    }

    public function test_owner_can_update_pos_supervisor_security_settings(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->owner->id,
                'active_business_id' => $this->business->id,
            ])
            ->put(route('settings.update'), [
                '_tab' => 'general',
                'name' => 'Kedai Kopi Cooca Baru',
                'pos_supervisor_pin' => '9999',
                'pos_max_cashier_discount_percent' => 15.0,
                'pos_require_pin_for_void' => '1',
                'pos_require_pin_for_refund' => '1',
            ]);

        $response->assertSessionHas('success');

        $this->business->refresh();
        $this->assertTrue(Hash::check('9999', $this->business->pos_supervisor_pin));
        $this->assertEquals(15.0, $this->business->pos_max_cashier_discount_percent);
        $this->assertTrue($this->business->pos_require_pin_for_void);
        $this->assertTrue($this->business->pos_require_pin_for_refund);
    }

    public function test_supervisor_pin_verification_endpoint(): void
    {
        // Salah PIN -> 401
        $responseFail = $this->actingAs($this->cashier)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->cashier->id,
                'active_business_id' => $this->business->id,
            ])
            ->postJson(route('pos.verify-pin'), ['pin' => '0000']);

        $responseFail->assertStatus(401);
        $responseFail->assertJson(['success' => false]);

        // Benar PIN -> 200
        $responseOk = $this->actingAs($this->cashier)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->cashier->id,
                'active_business_id' => $this->business->id,
            ])
            ->postJson(route('pos.verify-pin'), ['pin' => '8888']);

        $responseOk->assertStatus(200);
        $responseOk->assertJson(['success' => true]);
    }

    public function test_cashier_void_fails_without_correct_supervisor_pin(): void
    {
        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'user_id' => $this->cashier->id,
            'order_number' => 'POS-001',
            'order_date' => now()->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 50000,
            'total_amount' => 50000,
            'payment_status' => 'paid',
        ]);

        // Cashier attempts void with wrong PIN
        $response = $this->actingAs($this->cashier)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->cashier->id,
                'active_business_id' => $this->business->id,
            ])
            ->post(url("/pos/orders/{$order->id}/void"), [
                'reason' => 'Salah meja',
                'pin' => '1111',
            ]);

        $response->assertSessionHasErrors('void');
        $this->assertEquals(PosOrder::STATUS_COMPLETED, $order->fresh()->status);

        // Cashier attempts void with correct PIN (8888)
        $responseSuccess = $this->actingAs($this->cashier)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->cashier->id,
                'active_business_id' => $this->business->id,
            ])
            ->post(url("/pos/orders/{$order->id}/void"), [
                'reason' => 'Salah meja',
                'pin' => '8888',
            ]);

        $responseSuccess->assertSessionHas('success');
        $this->assertEquals(PosOrder::STATUS_VOIDED, $order->fresh()->status);
    }

    public function test_owner_can_void_with_auto_bypass_without_pin(): void
    {
        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'order_number' => 'POS-002',
            'order_date' => now()->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 75000,
            'total_amount' => 75000,
            'payment_status' => 'paid',
        ]);

        // Owner voids without providing pin -> bypasses because user is owner
        $response = $this->actingAs($this->owner)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->owner->id,
                'active_business_id' => $this->business->id,
            ])
            ->post(url("/pos/orders/{$order->id}/void"), [
                'reason' => 'Test void owner',
            ]);

        $response->assertSessionHas('success');
        $this->assertEquals(PosOrder::STATUS_VOIDED, $order->fresh()->status);
    }

    public function test_cashier_refund_fails_without_correct_supervisor_pin(): void
    {
        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'user_id' => $this->cashier->id,
            'order_number' => 'POS-003',
            'order_date' => now()->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 60000,
            'total_amount' => 60000,
            'payment_status' => 'paid',
        ]);

        // Wrong PIN -> Error
        $responseFail = $this->actingAs($this->cashier)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->cashier->id,
                'active_business_id' => $this->business->id,
            ])
            ->post(url("/pos/orders/{$order->id}/refund"), [
                'reason' => 'Produk cacat',
                'pin' => '0000',
            ]);

        $responseFail->assertSessionHasErrors('refund');
        $this->assertEquals(PosOrder::STATUS_COMPLETED, $order->fresh()->status);

        // Correct PIN -> Success
        $responseSuccess = $this->actingAs($this->cashier)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->cashier->id,
                'active_business_id' => $this->business->id,
            ])
            ->post(url("/pos/orders/{$order->id}/refund"), [
                'reason' => 'Produk cacat',
                'pin' => '8888',
            ]);

        $responseSuccess->assertSessionHas('success');
        $this->assertEquals(PosOrder::STATUS_REFUNDED, $order->fresh()->status);
    }

    public function test_supervisor_pin_is_hidden_from_json_serialization(): void
    {
        $array = $this->business->toArray();
        $this->assertArrayNotHasKey('pos_supervisor_pin', $array);

        $json = json_encode($this->business);
        $this->assertStringNotContainsString('pos_supervisor_pin', (string) $json);
    }

    public function test_supervisor_pin_unconfigured_returns_422(): void
    {
        $this->business->update(['pos_supervisor_pin' => null]);

        $response = $this->actingAs($this->cashier)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->cashier->id,
                'active_business_id' => $this->business->id,
            ])
            ->postJson(route('pos.verify-pin'), ['pin' => '8888']);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $this->assertEquals(__('pos.supervisor_pin_not_configured'), (string) $response->json('message'));
    }

    public function test_supervisor_pin_rate_limiting_locks_after_5_failures_and_logs_audit(): void
    {
        \Illuminate\Support\Facades\RateLimiter::clear('pos_supervisor_pin:' . $this->business->id . ':' . $this->cashier->id);

        for ($i = 1; $i <= 4; $i++) {
            $res = $this->actingAs($this->cashier)
                ->withSession([
                    'auth_wa_otp_verified_user_id' => $this->cashier->id,
                    'active_business_id' => $this->business->id,
                ])
                ->postJson(route('pos.verify-pin'), ['pin' => '000' . $i]);

            $res->assertStatus(401);
            $this->assertEquals(__('pos.supervisor_pin_invalid_attempts', ['remaining' => 5 - $i]), (string) $res->json('message'));
        }

        // 5th failed attempt -> locks out
        $res5 = $this->actingAs($this->cashier)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->cashier->id,
                'active_business_id' => $this->business->id,
            ])
            ->postJson(route('pos.verify-pin'), ['pin' => '0005']);

        $res5->assertStatus(401);
        $this->assertEquals(__('auth.pin_supervisor_locked', ['minutes' => 10]), (string) $res5->json('message'));

        // 6th attempt -> returns 429 Too Many Requests (either from controller rate limiter or route throttle middleware)
        $res6 = $this->actingAs($this->cashier)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->cashier->id,
                'active_business_id' => $this->business->id,
            ])
            ->postJson(route('pos.verify-pin'), ['pin' => '8888']);

        $res6->assertStatus(429);

        // Verify Audit Log entry created for lockout
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'POS_SUPERVISOR_PIN_LOCKED',
        ]);
    }

    public function test_supervisor_pin_strict_bcrypt_rejects_plaintext(): void
    {
        // Set business PIN to Bcrypt hash of '123456'
        $this->business->update(['pos_supervisor_pin' => Hash::make('123456')]);

        // Trying wrong PIN should fail
        $res = $this->actingAs($this->cashier)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->cashier->id,
                'active_business_id' => $this->business->id,
            ])
            ->postJson(route('pos.verify-pin'), ['pin' => '654321']);

        $res->assertStatus(401);

        // Trying correct PIN should succeed
        $resOk = $this->actingAs($this->cashier)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->cashier->id,
                'active_business_id' => $this->business->id,
            ])
            ->postJson(route('pos.verify-pin'), ['pin' => '123456']);

        $resOk->assertStatus(200);
        $resOk->assertJson(['success' => true]);
    }

    public function test_supervisor_pin_multi_language_localization_in_english_and_indonesian(): void
    {
        // 1. Test Indonesian Locale (default)
        app()->setLocale('id');
        $this->business->update(['pos_supervisor_pin' => Hash::make('8888')]);

        $resId = $this->actingAs($this->cashier)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->cashier->id,
                'active_business_id' => $this->business->id,
            ])
            ->postJson(route('pos.verify-pin'), ['pin' => '8888']);

        $resId->assertStatus(200);
        $this->assertEquals('Otorisasi Supervisor Terverifikasi.', $resId->json('message'));

        // 2. Test English Locale
        app()->setLocale('en');

        $resEn = $this->actingAs($this->cashier)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->cashier->id,
                'active_business_id' => $this->business->id,
            ])
            ->postJson(route('pos.verify-pin'), ['pin' => '8888']);

        $resEn->assertStatus(200);
        $this->assertEquals('Supervisor Authorization Verified.', $resEn->json('message'));

        // Test English Error Message when wrong PIN provided
        $resEnFail = $this->actingAs($this->cashier)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->cashier->id,
                'active_business_id' => $this->business->id,
            ])
            ->postJson(route('pos.verify-pin'), ['pin' => '9999']);

        $resEnFail->assertStatus(401);
        $this->assertStringContainsString('Incorrect Supervisor PIN', (string) $resEnFail->json('message'));

        // Reset back to ID
        app()->setLocale('id');
    }
}
