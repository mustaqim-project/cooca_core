<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class BusinessMembershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_member_of_multiple_businesses_and_sees_only_theirs(): void
    {
        $userA = User::create([
            'name' => 'User A',
            'email' => 'usera@example.com',
            'password' => 'password123',
        ]);

        $userB = User::create([
            'name' => 'User B',
            'email' => 'userb@example.com',
            'password' => 'password123',
        ]);

        $biz1 = Business::create(['name' => 'Business Satu']);
        $biz2 = Business::create(['name' => 'Business Dua']);
        $bizOther = Business::create(['name' => 'Business Lain']);

        $biz1->users()->attach($userA->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $biz2->users()->attach($userA->id, ['id' => (string) Str::uuid(), 'role' => 'manager']);
        $bizOther->users()->attach($userB->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);

        $tokenA = $userA->createToken('tokenA')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$tokenA)
            ->getJson('/api/v1/me/businesses');

        $response->assertOk()
            ->assertJsonCount(2, 'businesses');

        $businessNames = collect($response->json('businesses'))->pluck('name')->all();
        $this->assertContains('Business Satu', $businessNames);
        $this->assertContains('Business Dua', $businessNames);
        $this->assertNotContains('Business Lain', $businessNames);
    }
}
