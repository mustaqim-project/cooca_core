<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Business;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class PasswordAndVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_registration_sends_verification_email(): void
    {
        Notification::fake();

        $response = $this->post(route('register'), [
            'name' => 'New Owner', 'email' => 'new-owner@test.local', 'password' => 'password123',
            'password_confirmation' => 'password123', 'business_name' => 'New Business',
        ]);

        $response->assertRedirect(route('dashboard'));
        $user = User::where('email', 'new-owner@test.local')->firstOrFail();
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_owner_can_verify_email_from_signed_link(): void
    {
        $user = User::create(['name' => 'Owner', 'email' => 'verify@test.local', 'password' => 'password123']);
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(10), [
            'id' => $user->id, 'hash' => sha1($user->getEmailForVerification()),
        ]);

        $this->actingAs($user)->get($url)->assertRedirect();
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_admin_login_and_reset_pages_are_available(): void
    {
        $this->get(route('admin.login'))->assertOk()->assertSee(route('admin.password.request'));
        $this->get(route('admin.password.request'))->assertOk();
        $this->get(route('admin.password.reset', ['token' => Str::random(64), 'email' => 'admin@test.local']))->assertOk();
    }

    public function test_api_change_password_revokes_old_tokens(): void
    {
        $biz = Business::create(['name' => 'API Business']);
        $user = User::create(['name' => 'API User', 'email' => 'api-password@test.local', 'password' => Hash::make('old-password')]);
        $biz->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $biz->id]);

        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/profile/change-password', [
                'current_password' => 'old-password', 'password' => 'new-password', 'password_confirmation' => 'new-password',
            ])->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }
}
