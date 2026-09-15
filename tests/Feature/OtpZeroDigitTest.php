<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class OtpZeroDigitTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name' => 'Zero Digit User',
            'email' => 'zero@cooca.id',
            'phone' => '6282114468467',
            'password' => 'secret123',
        ]);

        $this->business = Business::create([
            'name' => 'Usaha Zero',
            'slug' => 'usaha-zero',
            'phone' => '6282114468467',
            'currency' => 'IDR',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
    }

    public function test_otp_with_trailing_zero_validates_and_authenticates(): void
    {
        $this->actingAs($this->user);

        $otpCode = '797430'; // trailing zero

        session()->put('auth_wa_otp_challenge', [
            'user_id' => $this->user->id,
            'phone' => '6282114468467',
            'otp_hash' => Hash::make($otpCode),
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
            'last_sent_at' => now()->timestamp,
        ]);

        $response = $this->post(route('auth.otp.verify'), [
            'otp' => $otpCode,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('dashboard'));

        $this->assertSame($this->user->id, session('auth_wa_otp_verified_user_id'));
        $this->assertNull(session('auth_wa_otp_challenge'));
    }

    public function test_otp_with_leading_zero_validates_and_authenticates(): void
    {
        $this->actingAs($this->user);

        $otpCode = '012345'; // leading zero

        session()->put('auth_wa_otp_challenge', [
            'user_id' => $this->user->id,
            'phone' => '6282114468467',
            'otp_hash' => Hash::make($otpCode),
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
            'last_sent_at' => now()->timestamp,
        ]);

        $response = $this->post(route('auth.otp.verify'), [
            'otp' => $otpCode,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('dashboard'));

        $this->assertSame($this->user->id, session('auth_wa_otp_verified_user_id'));
        $this->assertNull(session('auth_wa_otp_challenge'));
    }
}
