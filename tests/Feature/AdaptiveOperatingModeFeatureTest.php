<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\System\OperatingModeService;
use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdaptiveOperatingModeFeatureTest extends TestCase
{
    use RefreshDatabase;

    private OperatingModeService $modeService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->modeService = new OperatingModeService();
    }

    public function test_business_with_one_user_is_in_solo_mode(): void
    {
        $business = Business::create(['name' => 'Toko Solo']);
        $owner = User::create([
            'name' => 'Single Owner',
            'email' => 'single_owner@example.com',
            'password' => 'password123',
        ]);

        $business->users()->attach($owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->assertTrue($this->modeService->isSoloMode($business));
        $this->assertTrue($this->modeService->canBypassSupervisor($business, $owner));
        $this->assertFalse($this->modeService->shouldHideCostFromCashier($business, $owner));

        $profile = $this->modeService->getOperatingModeProfile($business);
        $this->assertEquals('solo', $profile['mode']);
        $this->assertTrue($profile['bypass_supervisor_pin']);
    }

    public function test_business_with_multiple_users_is_in_team_mode(): void
    {
        $business = Business::create(['name' => 'Toko Tim']);
        $owner = User::create([
            'name' => 'Owner Bisnis',
            'email' => 'owner_bisnis@example.com',
            'password' => 'password123',
        ]);
        $cashier = User::create([
            'name' => 'Staff Kasir',
            'email' => 'cashier@example.com',
            'password' => 'password123',
        ]);

        $business->users()->attach($owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $business->users()->attach($cashier->id, [
            'id' => (string) Str::uuid(),
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $this->assertFalse($this->modeService->isSoloMode($business));
        
        $profile = $this->modeService->getOperatingModeProfile($business);
        $this->assertEquals('team', $profile['mode']);
        $this->assertFalse($profile['bypass_supervisor_pin']);
    }
}
