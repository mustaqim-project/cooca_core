<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\TemplateLead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLeadsManagementTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'name' => 'Super Admin Cooca',
            'email' => 'admin@cooca.id',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);
    }

    public function test_guest_cannot_access_admin_leads(): void
    {
        $response = $this->get(route('admin.leads.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_leads_index_with_bento_cards_and_data(): void
    {
        TemplateLead::create([
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'email' => 'budi@warungmakan.com',
            'business_name' => 'Warung Nasi Enak',
            'template_slug' => 'pembukuan-warung-excel',
            'template_name' => 'Pembukuan Warung Excel',
            'ip_address' => '127.0.0.1',
        ]);

        TemplateLead::create([
            'name' => 'Siti Rahma',
            'phone' => '085678901234',
            'email' => 'siti@kafe.com',
            'business_name' => 'Kopi Senja',
            'template_slug' => 'laporan-keuangan-sederhana',
            'template_name' => 'Laporan Keuangan Sederhana',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.leads.index'));

        $response->assertStatus(200);
        $response->assertSee('Database Leads Pengunduh Template');
        $response->assertSee('Total Prospek (Leads)');
        $response->assertSee('Leads Masuk Hari Ini');
        $response->assertSee('Template Terpopuler');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Warung Nasi Enak');
        $response->assertSee('Siti Rahma');
        $response->assertSee('Kopi Senja');
        $response->assertSee('Chat WhatsApp');
        $response->assertSee('Ekspor Spreadsheet (CSV)');
    }

    public function test_admin_can_filter_leads_by_search_query(): void
    {
        TemplateLead::create([
            'name' => 'Rian Pratama',
            'phone' => '081122334455',
            'email' => 'rian@bengkel.com',
            'business_name' => 'Bengkel Motor Sejahtera',
            'template_slug' => 'invoice-sederhana',
            'template_name' => 'Invoice Sederhana',
        ]);

        TemplateLead::create([
            'name' => 'Dewi Lestari',
            'phone' => '082233445566',
            'email' => 'dewi@salon.com',
            'business_name' => 'Salon Cantik',
            'template_slug' => 'stok-opname-excel',
            'template_name' => 'Stok Opname Excel',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.leads.index', ['search' => 'Bengkel']));

        $response->assertStatus(200);
        $response->assertSee('Rian Pratama');
        $response->assertDontSee('Dewi Lestari');
    }

    public function test_admin_can_filter_leads_by_template_slug(): void
    {
        TemplateLead::create([
            'name' => 'Hendra',
            'phone' => '081987654321',
            'template_slug' => 'stok-opname-excel',
            'template_name' => 'Stok Opname Excel',
        ]);

        TemplateLead::create([
            'name' => 'Maya',
            'phone' => '081234432112',
            'template_slug' => 'invoice-sederhana',
            'template_name' => 'Invoice Sederhana',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.leads.index', ['template' => 'stok-opname-excel']));

        $response->assertStatus(200);
        $response->assertSee('Hendra');
        $response->assertDontSee('Maya');
    }

    public function test_admin_can_export_leads_csv(): void
    {
        TemplateLead::create([
            'name' => 'Agus Salim',
            'phone' => '087811223344',
            'email' => 'agus@toko.com',
            'business_name' => 'Toko Kelontong Agus',
            'template_slug' => 'pembukuan-warung-excel',
            'template_name' => 'Pembukuan Warung Excel',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.leads.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertTrue(str_contains((string) $response->headers->get('Content-Disposition'), 'attachment; filename="leads_template_'));

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('Nama', $content);
        $this->assertStringContainsString('No WhatsApp/HP', $content);
        $this->assertStringContainsString('Agus Salim', $content);
        $this->assertStringContainsString('087811223344', $content);
        $this->assertStringContainsString('Toko Kelontong Agus', $content);
    }
}
