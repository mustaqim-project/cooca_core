<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class BusinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_business_and_gets_uuid_and_slug(): void
    {
        $user = User::create([
            'name' => 'Owner Satu',
            'email' => 'owner1@example.com',
            'password' => 'password123',
        ]);

        $token = $user->createToken('token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/businesses', [
                'name' => 'Resto Sedap Rasa',
                'description' => 'Restoran masakan nusantara',
            ]);

        $response->assertCreated()
            ->assertJsonPath('business.name', 'Resto Sedap Rasa')
            ->assertJsonPath('business.slug', 'resto-sedap-rasa');

        $businessId = $response->json('business.id');
        $this->assertTrue(Str::isUuid($businessId));

        $this->assertDatabaseHas('businesses', [
            'id' => $businessId,
            'slug' => 'resto-sedap-rasa',
        ]);

        // Verify creator is attached as owner
        $this->assertDatabaseHas('business_users', [
            'business_id' => $businessId,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);
    }

    public function test_two_businesses_with_same_name_generate_different_global_slugs(): void
    {
        $user = User::create([
            'name' => 'Owner Dua',
            'email' => 'owner2@example.com',
            'password' => 'password123',
        ]);

        $token = $user->createToken('token')->plainTextToken;

        $response1 = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/businesses', [
                'name' => 'Dapur Nusantara',
            ]);

        $response2 = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/businesses', [
                'name' => 'Dapur Nusantara',
            ]);

        $response1->assertCreated()
            ->assertJsonPath('business.slug', 'dapur-nusantara');

        $response2->assertCreated()
            ->assertJsonPath('business.slug', 'dapur-nusantara-2');
    }

    public function test_member_can_view_business_by_slug_while_non_member_is_forbidden(): void
    {
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => 'password123',
        ]);

        $stranger = User::create([
            'name' => 'Stranger',
            'email' => 'stranger@example.com',
            'password' => 'password123',
        ]);

        $business = Business::create([
            'name' => 'Kedai Kopi Senja',
        ]);
        $business->users()->attach($owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $ownerToken = $owner->createToken('owner_token')->plainTextToken;
        $strangerToken = $stranger->createToken('stranger_token')->plainTextToken;

        // Owner can access
        $this->actingAs($owner, 'sanctum')
            ->getJson('/api/v1/businesses/'.$business->slug)
            ->assertOk()
            ->assertJsonPath('business.name', 'Kedai Kopi Senja');

        // Stranger is forbidden (403)
        $this->actingAs($stranger, 'sanctum')
            ->getJson('/api/v1/businesses/'.$business->slug)
            ->assertForbidden();
    }
}
