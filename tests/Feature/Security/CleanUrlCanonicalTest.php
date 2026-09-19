<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CleanUrlCanonicalTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_prefixed_admin_login_redirects_301_to_clean_url(): void
    {
        $response = $this->get('/public/admin/login');

        $response->assertStatus(301);
        $response->assertRedirect('/admin/login');
    }

    public function test_public_prefixed_generic_url_redirects_301_to_clean_url(): void
    {
        $response = $this->get('/public/login');

        $response->assertStatus(301);
        $response->assertRedirect('/login');
    }

    public function test_admin_root_redirects_guest_to_admin_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_root_redirects_authenticated_admin_to_dashboard(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get('/admin');

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_route_generator_never_contains_public_prefix(): void
    {
        $loginUrl = route('admin.login');
        $dashboardUrl = route('admin.dashboard');

        $this->assertStringNotContainsString('/public/', $loginUrl);
        $this->assertStringNotContainsString('/public/', $dashboardUrl);
        $this->assertStringEndsWith('/admin/login', $loginUrl);
        $this->assertStringEndsWith('/admin/dashboard', $dashboardUrl);
    }
}
