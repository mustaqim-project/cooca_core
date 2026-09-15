<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LandingPageAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_login_and_register_links_on_landing_page(): void
    {
        $response = $this->get(route('landing'));

        $response->assertStatus(200);
        $response->assertSee(route('login'));
        $response->assertSee(route('register'));
        $response->assertDontSee(route('admin.dashboard'));
        $response->assertDontSee('Ke Dashboard');
    }

    public function test_authenticated_user_sees_dashboard_link_and_no_login_register(): void
    {
        $user = User::create([
            'name' => 'Owner Bisnis',
            'email' => 'owner@example.com',
            'password' => 'password123',
            'email_verified_at' => now(),
        ]);
        $business = Business::create([
            'name' => 'Kopi Barokah',
            'slug' => 'kopi-barokah',
            'currency' => 'IDR',
            'is_active' => true,
        ]);
        $user->businesses()->attach($business->id, ['role' => 'owner']);
        $user->forceFill(['active_business_id' => $business->id])->save();

        $response = $this->actingAs($user, 'web')->get(route('landing'));

        $response->assertStatus(200);
        $response->assertSee(route('dashboard'));
        $response->assertSee('Ke Dashboard');
        $response->assertDontSee(route('login'));
        $response->assertDontSee(route('register'));
    }

    public function test_authenticated_admin_sees_admin_dashboard_link_and_no_login_register(): void
    {
        $admin = Admin::create([
            'name' => 'Admin Platform',
            'email' => 'admin@cooca.id',
            'password' => 'secret123',
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('landing'));

        $response->assertStatus(200);
        $response->assertSee(route('admin.dashboard'));
        $response->assertSee('Dashboard Admin');
        $response->assertDontSee(route('login'));
        $response->assertDontSee(route('register'));
    }
}
