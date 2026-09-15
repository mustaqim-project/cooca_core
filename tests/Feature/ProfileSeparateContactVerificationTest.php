<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\WhatsApp\AdminWhatsAppService;
use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProfileSeparateContactVerificationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->owner = User::create([
            'name' => 'Owner Bisnis',
            'email' => 'owner@cooca.id',
            'phone' => '628111222333',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'Bisnis Owner',
            'slug' => 'bisnis-owner',
            'phone' => '628111222333',
            'currency' => 'IDR',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);

        session([
            'auth_wa_otp_verified_user_id' => $this->owner->id,
        ]);
    }

    public function test_profile_page_displays_separate_phone_and_email_cards(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['auth_wa_otp_verified_user_id' => $this->owner->id])
            ->get(route('profile.edit'));

        $response->assertStatus(200);
        $response->assertSee('Ganti Nomor WhatsApp');
        $response->assertSee('Kirim OTP WhatsApp');
        $response->assertSee('Ganti Alamat Email');
        $response->assertSee('Ubah &amp; Verifikasi Email', false);
    }

    public function test_owner_can_request_phone_change_separately_and_verify_with_otp(): void
    {
        // Mock AdminWhatsAppService
        $this->mock(AdminWhatsAppService::class, function ($mock) {
            $mock->shouldReceive('sendMessage')
                ->once()
                ->andReturn(['success' => true]);
        });

        $newPhone = '082199887766';
        $normalizedPhone = '6282199887766';

        // 1. Submit phone change request
        $response = $this->actingAs($this->owner)
            ->withSession(['auth_wa_otp_verified_user_id' => $this->owner->id])
            ->post(route('profile.phone.request'), [
                'phone' => $newPhone,
            ]);

        $response->assertRedirect(route('profile.contact.verify'));
        $this->assertTrue(session()->has('profile_phone_change'));

        // 2. Set OTP in session
        $otp = '654321';
        $pending = session('profile_phone_change');
        $pending['otp_hash'] = Hash::make($otp);

        // 3. Submit OTP verification
        $verifyResponse = $this->actingAs($this->owner)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->owner->id,
                'profile_phone_change' => $pending,
            ])
            ->post(route('profile.contact.verify.submit'), [
                'otp' => $otp,
            ]);

        $verifyResponse->assertRedirect(route('profile.edit'));
        $verifyResponse->assertSessionHas('success');

        $this->owner->refresh();
        $this->assertSame($normalizedPhone, $this->owner->phone);
        // Email remains unchanged
        $this->assertSame('owner@cooca.id', $this->owner->email);
    }

    public function test_owner_can_update_email_separately_with_password_verification(): void
    {
        Notification::fake();

        $newEmail = 'owner.baru@cooca.id';

        $response = $this->actingAs($this->owner)
            ->withSession(['auth_wa_otp_verified_user_id' => $this->owner->id])
            ->put(route('profile.email.update'), [
                'email' => $newEmail,
                'current_password' => 'password123',
            ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('success');

        $this->owner->refresh();
        $this->assertSame($newEmail, $this->owner->email);
        $this->assertNull($this->owner->email_verified_at);

        // WhatsApp number remains unchanged
        $this->assertSame('628111222333', $this->owner->phone);

        Notification::assertSentTo($this->owner, VerifyEmail::class);
    }

    public function test_email_update_fails_with_invalid_password(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['auth_wa_otp_verified_user_id' => $this->owner->id])
            ->put(route('profile.email.update'), [
                'email' => 'owner.salah@cooca.id',
                'current_password' => 'wrongpassword',
            ]);

        $response->assertSessionHasErrors(['current_password']);
        $this->owner->refresh();
        $this->assertSame('owner@cooca.id', $this->owner->email);
    }
}
