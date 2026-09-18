<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\LegalPage;
use Database\Seeders\LegalPagesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLegalPageTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): Admin
    {
        return Admin::factory()->create([
            'name'      => 'Super Administrator',
            'email'     => 'superadmin@cooca.id',
            'password'  => Hash::make('password123'),
            'role'      => 'super_admin',
            'is_active' => true,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LegalPagesSeeder::class);
    }

    public function test_superadmin_can_view_legal_pages_index_in_admin_panel(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.legal-pages.index'));

        $response->assertOk();
        $response->assertSee('Pusat Tata Kelola Dokumen Legalitas &amp; Privasi', false);
        $response->assertSee('/privacy-policy', false);
        $response->assertSee('/terms-conditions', false);
        $response->assertSee('UU PDP 27/2022', false);
    }

    public function test_superadmin_can_view_edit_legal_page_form_with_tinymce_fields(): void
    {
        $admin = $this->makeAdmin();
        $page = LegalPage::where('slug', 'privacy-policy')->firstOrFail();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.legal-pages.edit', $page));

        $response->assertOk();
        $response->assertSee(e('Edit ' . $page->title), false);
        $response->assertSee('content_general', false);
        $response->assertSee('content_owner', false);
        $response->assertSee('content_customer', false);
        $response->assertSee('tinymce.init', false);
    }

    public function test_superadmin_can_update_legal_page_and_reflect_in_database(): void
    {
        $admin = $this->makeAdmin();
        $page = LegalPage::where('slug', 'privacy-policy')->firstOrFail();

        $response = $this->actingAs($admin, 'admin')->put(route('admin.legal-pages.update', $page), [
            'title'            => 'Kebijakan Privasi Resmi Cooca Updated',
            'subtitle'         => 'Ringkasan privasi yang diperbarui.',
            'version'          => '2.2',
            'effective_date'   => '2026-09-19',
            'content_general'  => '<p>Ketentuan umum teruji.</p>',
            'content_owner'    => '<p>Ketentuan owner teruji.</p>',
            'content_customer' => '<p>Ketentuan customer teruji.</p>',
            'is_published'     => '1',
        ]);

        $response->assertRedirect(route('admin.legal-pages.edit', $page));
        $this->assertDatabaseHas('legal_pages', [
            'slug'    => 'privacy-policy',
            'title'   => 'Kebijakan Privasi Resmi Cooca Updated',
            'version' => '2.2',
        ]);
    }

    public function test_superadmin_can_toggle_published_status_of_legal_page(): void
    {
        $admin = $this->makeAdmin();
        $page = LegalPage::where('slug', 'privacy-policy')->firstOrFail();
        $initialStatus = $page->is_published;

        $response = $this->actingAs($admin, 'admin')->post(route('admin.legal-pages.toggle', $page));

        $response->assertRedirect(route('admin.legal-pages.index'));
        $this->assertSame(! $initialStatus, $page->fresh()->is_published);
    }

    public function test_guest_can_access_public_privacy_policy_and_see_database_content(): void
    {
        $response = $this->get(route('public.privacy'));

        $response->assertOk();
        $response->assertSee('Kebijakan Privasi', false);
        $response->assertSee('UU PDP No. 27/2022', false);
        $response->assertSee('Khusus Pemilik Usaha (Owner)', false);
        $response->assertSee('Khusus Pelanggan Toko (Customer)', false);
        $response->assertSee('TriPay Payment Gateway', false);
        $response->assertSee('Biteship Logistics Aggregator', false);

        // Also test indonesian alias route
        $aliasResponse = $this->get('/kebijakan-privasi');
        $aliasResponse->assertOk();
    }

    public function test_guest_can_access_public_terms_and_conditions_and_see_database_content(): void
    {
        $response = $this->get(route('public.terms'));

        $response->assertOk();
        $response->assertSee('Syarat &amp; Ketentuan Layanan', false);
        $response->assertSee('Pasal 1338 KUHPerdata', false);
        $response->assertSee('Khusus Pemilik Usaha (Owner)', false);
        $response->assertSee('Khusus Pelanggan Toko (Customer)', false);
        $response->assertSee('Kebijakan Barang Terlarang', false);
        $response->assertSee('Video Unboxing', false);

        // Also test indonesian alias route
        $aliasResponse = $this->get('/syarat-ketentuan');
        $aliasResponse->assertOk();
    }

    public function test_unauthenticated_user_cannot_access_admin_legal_pages(): void
    {
        $response = $this->get(route('admin.legal-pages.index'));

        $response->assertRedirect(route('admin.login'));
    }
}
