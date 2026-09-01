<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ActiveBusinessContextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
    }

    public function test_tenant_aware_endpoint_returns_409_conflict_when_no_active_business_selected(): void
    {
        $user = User::create([
            'name' => 'User Without Active Business',
            'email' => 'noactive@example.com',
            'password' => 'password123',
            'active_business_id' => null,
        ]);

        $token = $user->createToken('token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/context/current');

        $response->assertStatus(409)
            ->assertJson([
                'error_code' => 'ACTIVE_BUSINESS_REQUIRED',
            ]);
    }

    public function test_user_can_set_active_business_and_access_tenant_aware_endpoint(): void
    {
        $user = User::create([
            'name' => 'User Active Test',
            'email' => 'activetest@example.com',
            'password' => 'password123',
        ]);

        $business = Business::create(['name' => 'Kopi Mantap']);
        $business->users()->attach($user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'admin',
        ]);

        $token = $user->createToken('token')->plainTextToken;

        // Set active business
        $switchResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/me/active-business', [
                'business_id' => $business->id,
            ]);

        $switchResponse->assertOk()
            ->assertJsonPath('active_business.id', $business->id);

        $this->assertSame($business->id, $user->fresh()->active_business_id);

        // Access tenant-aware endpoint
        $contextResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/context/current');

        $contextResponse->assertOk()
            ->assertJsonPath('business.id', $business->id)
            ->assertJsonPath('business.name', 'Kopi Mantap')
            ->assertJsonPath('role', 'admin');
    }

    public function test_user_cannot_switch_to_unauthorized_business(): void
    {
        $user = User::create([
            'name' => 'Normal User',
            'email' => 'normal@example.com',
            'password' => 'password123',
        ]);

        $otherBusiness = Business::create(['name' => 'Business Orang Lain']);

        $token = $user->createToken('token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/me/active-business', [
                'business_id' => $otherBusiness->id,
            ]);

        $response->assertForbidden();
    }

    public function test_request_with_x_business_id_header_sets_context_dynamically(): void
    {
        $user = User::create([
            'name' => 'Multi Business User',
            'email' => 'multi@example.com',
            'password' => 'password123',
        ]);

        $business = Business::create(['name' => 'Cabang Bandung']);
        $business->users()->attach($user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
        ]);

        $token = $user->createToken('token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->withHeader('X-Business-Id', $business->id)
            ->getJson('/api/v1/context/current');

        $response->assertOk()
            ->assertJsonPath('business.id', $business->id)
            ->assertJsonPath('role', 'staff');
    }
}
