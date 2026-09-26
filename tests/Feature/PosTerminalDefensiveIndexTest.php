<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Location;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PosTerminalDefensiveIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_terminal_renders_when_business_has_no_locations(): void
    {
        Context::flush();

        $user = User::create([
            'name' => 'Owner Test',
            'email' => 'owner_noloc@example.com',
            'password' => 'secret123',
            'email_verified_at' => now(),
            'phone' => '081234567890',
            'phone_verified_at' => now(),
        ]);

        $business = Business::create([
            'name' => 'Bisnis Tanpa Lokasi',
            'slug' => 'bisnis-tanpa-lokasi',
        ]);

        $business->users()->attach($user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $user->update(['active_business_id' => $business->id]);
        Context::setBusiness($business);

        $this->actingAs($user);
        session(['active_business_id' => $business->id]);

        $response = $this->get(route('pos.terminal'));

        $response->assertStatus(200);
    }

    public function test_pos_terminal_redirects_guest_to_login(): void
    {
        Context::flush();

        $response = $this->get(route('pos.terminal'));

        $response->assertRedirect(route('login'));
    }

    public function test_pos_terminal_redirects_user_without_business(): void
    {
        Context::flush();

        $user = User::create([
            'name' => 'User Without Business',
            'email' => 'nobiz@example.com',
            'password' => 'secret123',
            'email_verified_at' => now(),
            'phone' => '081234567891',
            'phone_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('pos.terminal'));

        $response->assertRedirect(route('businesses.select'));
    }
}
