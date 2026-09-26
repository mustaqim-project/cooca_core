<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class CommunicationNavigationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
        $this->seed(RbacSeeder::class);

        $this->owner = User::create([
            'name'              => 'Owner Communication Nav Test',
            'email'             => 'owner_comm_' . Str::random(6) . '@test.local',
            'phone'             => '62812' . rand(10000000, 99999999),
            'password'          => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name'             => 'Bisnis Komunikasi & Omnichannel Audit',
            'currency'         => 'IDR',
            'currency_code'    => 'IDR',
            'currency_symbol'  => 'Rp',
            'business_scale'   => Business::SCALE_CORPORATE,
            'disabled_modules' => [],
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id'   => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
    }

    // ==========================================
    // COMMUNICATION HUB TESTS (3 VIEWS)
    // ==========================================

    public function test_whatsapp_index_renders_module_header_and_persistent_communication_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('whatsapp.index'));

        $response->assertOk();
        $response->assertSee('WhatsApp Gateway &amp; Otomasi Bisnis', false);
        $this->assertCommunicationTabsPresent($response);
    }

    public function test_whatsapp_broadcast_index_renders_module_header_and_persistent_communication_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('whatsapp.broadcast.index'));

        $response->assertOk();
        $response->assertSee('Blast Promosi WhatsApp');
        $this->assertCommunicationTabsPresent($response);
    }

    public function test_social_media_index_renders_module_header_and_persistent_communication_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('social-media.index'));

        $response->assertOk();
        $response->assertSee('Pengelolaan Media Sosial &amp; Konten Terpadu', false);
        $this->assertCommunicationTabsPresent($response);
    }

    // ==========================================
    // HELPER ASSERTIONS
    // ==========================================

    private function assertCommunicationTabsPresent($response): void
    {
        $response->assertSee('WhatsApp Inbox');
        $response->assertSee('WhatsApp Broadcast');
        $response->assertSee('Media Sosial');
        $response->assertSee(route('whatsapp.index'));
        $response->assertSee(route('whatsapp.broadcast.index'));
        $response->assertSee(route('social-media.index'));
    }
}
