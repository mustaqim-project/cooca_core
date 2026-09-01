<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class GuidedProductTourTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \App\Support\Context::flush();
        $this->seed(\Database\Seeders\DefaultUnitSeeder::class);
        $this->seed(\Database\Seeders\DefaultCostCategorySeeder::class);
        $this->seed(\Database\Seeders\BusinessTemplateSeeder::class);
    }

    public function test_user_onboarding_status_and_lifecycle(): void
    {
        $user = User::create([
            'name' => 'Tour Explorer',
            'email' => 'explorer@example.com',
            'password' => 'password123',
            'onboarding_completed' => false,
            'onboarding_current_step' => 1,
            'onboarding_version' => 1,
        ]);

        $biz = Business::create(['name' => 'Tour Testing Biz']);
        $biz->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $biz->id]);

        // 1. Initial Status Check
        $resStatus = $this->actingAs($user)
            ->withSession(['active_business_id' => $biz->id])
            ->getJson('/onboarding/status')
            ->assertOk()
            ->json();

        $this->assertFalse($resStatus['completed']);
        $this->assertEquals(1, $resStatus['current_step']);

        // 2. Step Progress Update
        $this->actingAs($user)
            ->withSession(['active_business_id' => $biz->id])
            ->postJson('/onboarding/step', ['step' => 4])
            ->assertOk()
            ->assertJson(['success' => true, 'current_step' => 4]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'onboarding_current_step' => 4,
            'onboarding_completed' => 0,
        ]);

        // 3. Complete / Finish Tour
        $this->actingAs($user)
            ->withSession(['active_business_id' => $biz->id])
            ->postJson('/onboarding/complete')
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'onboarding_completed' => 1,
        ]);

        // 4. Restart Tour
        $this->actingAs($user)
            ->withSession(['active_business_id' => $biz->id])
            ->postJson('/onboarding/restart')
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'onboarding_completed' => 0,
            'onboarding_current_step' => 1,
        ]);
    }
}
