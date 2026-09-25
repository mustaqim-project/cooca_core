<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\User;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingSubscriptionCheckoutActiveChannelsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
    }

    public function test_checkout_page_renders_only_active_tripay_channels_qris(): void
    {
        $user = User::factory()->create([
            'email' => 'owner@cooca.test',
        ]);

        $business = Business::create([
            'name' => 'Demo Resto Pro',
            'slug' => 'demo-resto-pro',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $business->users()->attach($user->id, ['role' => 'owner']);

        $response = $this->actingAs($user)
            ->withSession(['active_business_id' => $business->id])
            ->get(route('billing.checkout'));

        $response->assertOk();
        $response->assertSee('QRIS Dinamis');
        $response->assertSee('Standar Bank Indonesia');
        $response->assertSee('Livin Mandiri');
        $response->assertSee('BCA');
        $response->assertSee('BRImo');

        // Verify inactive Virtual Account dummy channels are NOT rendered
        $response->assertDontSee('Permata Virtual Account');
        $response->assertDontSee('Gerai Indomaret');
        $response->assertDontSee('Gerai Alfamart');
    }
}
