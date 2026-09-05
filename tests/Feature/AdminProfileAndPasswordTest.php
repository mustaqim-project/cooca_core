<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminProfileAndPasswordTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(array $override = []): Admin
    {
        return Admin::factory()->create(array_merge([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('secret123'),
            'role' => 'super_admin',
            'is_active' => true,
        ], $override));
    }

    public function test_admin_can_view_profile_page(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.profile.index'));

        $response->assertStatus(200);
        $response->assertSee('Profil Administrator');
        $response->assertSee('Ganti Kata Sandi');
    }

    public function test_admin_can_update_password(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->put(route('admin.profile.password'), [
                'current_password' => 'secret123',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // Refresh admin from DB
        $admin->refresh();

        // Check if new password matches
        $this->assertTrue(Hash::check('newpassword123', $admin->password));
    }

    public function test_admin_can_login_with_new_password_after_updating(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.profile.password'), [
                'current_password' => 'secret123',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ])
            ->assertSessionHasNoErrors();

        // Logout
        $this->post(route('admin.logout'));

        // Attempt login with new password
        $response = $this->post(route('admin.login.submit'), [
            'email' => $admin->email,
            'password' => 'newpassword123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_admin_password_update_fails_with_incorrect_current_password(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->put(route('admin.profile.password'), [
                'current_password' => 'wrongcurrentpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertSessionHasErrors('current_password');
    }

    public function test_admin_password_update_fails_with_mismatched_confirmation(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->put(route('admin.profile.password'), [
                'current_password' => 'secret123',
                'password' => 'newpassword123',
                'password_confirmation' => 'differentpassword',
            ]);

        $response->assertSessionHasErrors('password');
    }
}
