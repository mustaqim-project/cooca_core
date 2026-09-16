<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Domain\WhatsApp\AdminWhatsAppService;
use App\Models\Admin;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\User;
use App\Models\WhatsAppAdminBlast;
use App\Models\WhatsAppSubscriptionReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminWhatsAppFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(array $override = []): Admin
    {
        return Admin::factory()->create(array_merge([
            'name' => 'Super Administrator',
            'email' => 'superadmin@cooca.id',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'is_active' => true,
        ], $override));
    }

    private function makeTenant(): array
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'phone' => '081298765432',
        ]);

        $business = Business::create([
            'user_id' => $user->id,
            'name' => 'Kopi Sejahtera UMKM',
            'status' => 'active',
            'currency' => 'IDR',
        ]);

        return [$user, $business];
    }

    public function test_unauthenticated_user_cannot_access_admin_whatsapp(): void
    {
        $response = $this->get(route('admin.whatsapp.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_whatsapp_index_with_bento_tiles(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.whatsapp.index'));

        $response->assertOk();
        $response->assertSee('WhatsApp Admin Center');
        $response->assertSee('Status Gateway');
        $response->assertSee('Pengingat Hari Ini');
        $response->assertSee('Jangkauan Owner');
        $response->assertSee('Keberhasilan Kirim');
        $response->assertSee('Status &amp; Sesi QR', false);
        $response->assertSee('Pengingat Langganan');
        $response->assertSee('Broadcast Bisnis Owner');
        $response->assertSee('Template Notifikasi');
    }

    public function test_admin_can_navigate_tabs_via_query_param(): void
    {
        $admin = $this->makeAdmin();

        foreach (['connection', 'reminders', 'blast', 'templates'] as $tab) {
            $response = $this->actingAs($admin, 'admin')->get(route('admin.whatsapp.index', ['tab' => $tab]));
            $response->assertOk();
        }
    }

    public function test_admin_can_check_status_via_ajax(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->getJson(route('admin.whatsapp.status'));

        $response->assertOk();
        $response->assertJsonStructure(['status']);
    }

    public function test_admin_can_get_qr_via_ajax(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->getJson(route('admin.whatsapp.qr'));

        $response->assertOk();
    }

    public function test_admin_test_send_validates_input(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->postJson(route('admin.whatsapp.test'), [
                'phone' => '',
                'message' => '',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['phone', 'message']);
    }

    public function test_admin_can_update_reminder_templates(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->from(route('admin.whatsapp.index', ['tab' => 'templates']))
            ->post(route('admin.whatsapp.reminders.templates'), [
                'template_h7' => 'Halo {owner}, paket {paket} bisnis {bisnis} akan berakhir dalam 7 hari.',
                'template_h3' => 'Halo {owner}, paket {paket} akan berakhir 3 hari lagi. Bayar di {link_bayar}',
                'template_h1' => 'Peringatan: Besok paket {paket} akan berakhir!',
                'template_h0' => 'Hari ini paket {paket} Anda telah jatuh tempo.',
            ]);

        $response->assertRedirect(route('admin.whatsapp.index', ['tab' => 'templates']));
        $response->assertSessionHas('success');
    }

    public function test_admin_can_store_blast_and_view_detail(): void
    {
        $admin = $this->makeAdmin();
        [$user, $business] = $this->makeTenant();

        $this->mock(AdminWhatsAppService::class, function ($mock) {
            $mock->shouldReceive('sendAdminBlast')->once()->andReturnUsing(function ($blast) {
                $blast->update([
                    'total_recipients' => 1,
                    'total_sent' => 1,
                    'total_failed' => 0,
                    'status' => 'completed',
                ]);
            });
        });

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.whatsapp.blasts.store'), [
                'title' => 'Pengumuman Update Cooca v2.5',
                'target_filter' => 'all_owners',
                'message' => 'Halo {owner}, Cooca kini hadir dengan antarmuka Apple HIG Bento Grid!',
                'media_url' => 'https://example.com/banner.jpg',
            ]);

        $blast = WhatsAppAdminBlast::where('title', 'Pengumuman Update Cooca v2.5')->first();
        $this->assertNotNull($blast);

        $response->assertRedirect(route('admin.whatsapp.blasts.show', $blast));

        // Test viewing blast detail page
        $showResponse = $this->actingAs($admin, 'admin')->get(route('admin.whatsapp.blasts.show', $blast));
        $showResponse->assertOk();
        $showResponse->assertSee('Detail Broadcast');
        $showResponse->assertSee('Pengumuman Update Cooca v2.5');
        $showResponse->assertSee('Tampilan Pesan WhatsApp (Chat Mockup)');
        $showResponse->assertSee('Daftar Status Pengiriman Per Bisnis Owner');
    }
}
