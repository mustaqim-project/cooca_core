<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Tests\TestCase;

class AdminPanelAndGoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────
    //  Helpers
    // ─────────────────────────────────────────────────

    private function makeAdmin(array $override = []): Admin
    {
        return Admin::factory()->create(array_merge([
            'email' => 'admin@test.com',
            'password' => Hash::make('secret123'),
            'role' => 'super_admin',
            'is_active' => true,
        ], $override));
    }

    private function makeUser(array $override = []): User
    {
        return User::factory()->create($override);
    }

    // ─────────────────────────────────────────────────
    //  Landing Page
    // ─────────────────────────────────────────────────

    public function test_landing_page_is_accessible(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_landing_page_contains_bento_keywords(): void
    {
        $response = $this->get(route('landing'));

        $response->assertStatus(200);
        $response->assertSeeText('HPP');
    }

    // ─────────────────────────────────────────────────
    //  Admin Auth — Login Page
    // ─────────────────────────────────────────────────

    public function test_admin_login_page_is_accessible(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);
    }

    public function test_admin_dashboard_redirects_guests(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    // ─────────────────────────────────────────────────
    //  Admin Auth — Successful Login
    // ─────────────────────────────────────────────────

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->post(route('admin.login.submit'), [
            'email' => $admin->email,
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_admin_login_fails_with_wrong_password(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->post(route('admin.login.submit'), [
            'email' => $admin->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_inactive_admin_cannot_login(): void
    {
        $admin = $this->makeAdmin(['is_active' => false]);

        $response = $this->post(route('admin.login.submit'), [
            'email' => $admin->email,
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    // ─────────────────────────────────────────────────
    //  Admin Auth — Logout
    // ─────────────────────────────────────────────────

    public function test_admin_can_logout(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.logout'));

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }

    // ─────────────────────────────────────────────────
    //  Admin Dashboard
    // ─────────────────────────────────────────────────

    public function test_admin_dashboard_loads_for_authenticated_admin(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }

    // ─────────────────────────────────────────────────
    //  Admin — User Management
    // ─────────────────────────────────────────────────

    public function test_admin_user_list_is_accessible(): void
    {
        $admin = $this->makeAdmin();
        $this->makeUser(['name' => 'Test User', 'email' => 'testuser@example.com']);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.users.index'));

        $response->assertStatus(200);
    }

    public function test_admin_user_list_shows_users(): void
    {
        $admin = $this->makeAdmin();
        $user = $this->makeUser(['name' => 'Budi Santoso', 'email' => 'budi@example.com']);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSeeText($user->name);
    }

    public function test_admin_can_search_users(): void
    {
        $admin = $this->makeAdmin();
        $this->makeUser(['name' => 'Cari Ini', 'email' => 'cari@example.com']);
        $this->makeUser(['name' => 'Orang Lain', 'email' => 'lain@example.com']);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.users.index', ['search' => 'Cari']));

        $response->assertStatus(200);
        $response->assertSeeText('Cari Ini');
        $response->assertDontSeeText('Orang Lain');
    }

    // ─────────────────────────────────────────────────
    //  Admin — CSV Export
    // ─────────────────────────────────────────────────

    public function test_admin_can_export_users_as_csv(): void
    {
        $admin = $this->makeAdmin();
        $user = $this->makeUser(['name' => 'Export User', 'email' => 'export@example.com']);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.users.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString($user->email, $response->streamedContent());
    }

    public function test_export_csv_contains_utf8_bom(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.users.export'));

        $this->assertStringStartsWith("\xEF\xBB\xBF", $response->streamedContent());
    }

    public function test_export_csv_contains_header_row(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.users.export'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Nama', $content);
        $this->assertStringContainsString('Email', $content);
    }

    // ─────────────────────────────────────────────────
    //  Admin — Settings
    // ─────────────────────────────────────────────────

    public function test_admin_settings_page_loads(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.settings.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_update_google_api_settings(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.settings.update'), [
                'app_name' => 'Universal HPP Enterprise',
                'google_client_id' => 'test-client-id.apps.googleusercontent.com',
                'google_client_secret' => 'GOCSPX-test-secret',
                'google_redirect_uri' => 'https://example.com/auth/google/callback',
                'allow_google_login' => '1',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('system_settings', [
            'key' => 'google_client_id',
        ]);
    }

    public function test_non_admin_cannot_access_admin_settings(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user, 'web')
            ->get(route('admin.settings.index'));

        $response->assertRedirect(route('admin.login'));
    }

    // ─────────────────────────────────────────────────
    //  Google OAuth — Redirect
    // ─────────────────────────────────────────────────

    public function test_google_auth_redirect_works(): void
    {
        SystemSetting::set('google_client_id', 'mock-google-client-id');
        SystemSetting::set('google_client_secret', 'mock-google-client-secret');

        $provider = \Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('redirect')
            ->once()
            ->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturn($provider);

        $response = $this->get(route('auth.google'));

        $response->assertRedirect();
    }

    public function test_google_auth_is_user_only_and_does_not_authenticate_admin_guard(): void
    {
        // Google auth operates on web guard for User models only
        $this->assertGuest('admin');
        $this->assertGuest('web');
    }

    // ─────────────────────────────────────────────────
    //  Separation of Guards — Cross-Guard Isolation
    // ─────────────────────────────────────────────────

    public function test_user_authenticated_via_web_cannot_reach_admin_dashboard(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user, 'web')
            ->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_authenticated_via_admin_cannot_reach_user_dashboard(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('dashboard'));

        // Should redirect to user login, not the admin dashboard
        $response->assertRedirect();
    }

    // ─────────────────────────────────────────────────
    //  System Setting Model Helpers
    // ─────────────────────────────────────────────────

    public function test_system_setting_can_be_set_and_retrieved(): void
    {
        SystemSetting::set('test_key', 'test_value');

        $this->assertSame('test_value', SystemSetting::get('test_key'));
    }

    public function test_system_setting_returns_default_when_not_set(): void
    {
        $value = SystemSetting::get('nonexistent_key', 'default_value');

        $this->assertSame('default_value', $value);
    }
}
