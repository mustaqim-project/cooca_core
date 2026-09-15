<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ExcelTemplate;
use App\Models\TemplateLead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class AdminExcelTemplateManagementTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->admin = Admin::create([
            'name' => 'Super Admin Cooca',
            'email' => 'admin@cooca.id',
            'password' => Hash::make('secret123'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_templates_list_and_auto_seed(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.templates.index'));

        $response->assertOk();
        $response->assertSee('CMS Template Excel');
        $response->assertSee('Upload Template Baru');
        // Default templates seeded
        $this->assertDatabaseHas('excel_templates', [
            'slug' => 'pembukuan-warung-excel',
        ]);
    }

    public function test_admin_can_upload_new_excel_template_file(): void
    {
        $fakeExcel = UploadedFile::fake()->create('Template_Kalkulator_Gaji_UMKM.xlsx', 150, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.templates.store'), [
                'name' => 'Template Payroll & Gaji Karyawan UMKM',
                'slug' => 'payroll-gaji-karyawan-excel',
                'category' => 'Operasional & SDM',
                'description' => 'Format excel hitung gaji pokok, tunjangan, dan lembur karyawan.',
                'highlights' => "Hitung lembur otomatis\nSlip gaji siap cetak\nPotongan BPJS & kasbon",
                'sort_order' => 5,
                'is_active' => '1',
                'excel_file' => $fakeExcel,
            ]);

        $response->assertRedirect(route('admin.templates.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('excel_templates', [
            'name' => 'Template Payroll & Gaji Karyawan UMKM',
            'slug' => 'payroll-gaji-karyawan-excel',
            'category' => 'Operasional & SDM',
            'format' => 'XLSX',
        ]);

        $template = ExcelTemplate::where('slug', 'payroll-gaji-karyawan-excel')->first();
        $this->assertNotNull($template);
        $this->assertCount(3, $template->highlights);
        Storage::disk('public')->assertExists($template->file_path);
    }

    public function test_admin_can_update_template_and_replace_excel_file(): void
    {
        $oldFile = UploadedFile::fake()->create('old_template.xlsx', 100);
        $storedOldPath = $oldFile->storeAs('templates', 'old_template.xlsx', 'public');

        $template = ExcelTemplate::create([
            'name' => 'Template Lama',
            'slug' => 'template-lama',
            'category' => 'Buku Kas',
            'format' => 'XLSX',
            'file_path' => $storedOldPath,
            'file_name' => 'old_template.xlsx',
            'file_size' => 1000,
            'is_active' => true,
        ]);

        $newFile = UploadedFile::fake()->create('new_template_v2.xlsx', 250);

        $response = $this->actingAs($this->admin, 'admin')
            ->put(route('admin.templates.update', $template), [
                'name' => 'Template Baru Revisi',
                'slug' => 'template-baru-revisi',
                'category' => 'Laporan Finansial',
                'description' => 'Deskripsi baru',
                'highlights' => "Poin 1\nPoin 2",
                'sort_order' => 1,
                'is_active' => '1',
                'excel_file' => $newFile,
            ]);

        $response->assertRedirect(route('admin.templates.index'));

        $template->refresh();
        $this->assertEquals('Template Baru Revisi', $template->name);
        $this->assertEquals('template-baru-revisi', $template->slug);
        $this->assertEquals('Laporan Finansial', $template->category);

        // Old file deleted, new file stored
        Storage::disk('public')->assertMissing($storedOldPath);
        Storage::disk('public')->assertExists($template->file_path);
    }

    public function test_admin_can_toggle_and_delete_template(): void
    {
        $file = UploadedFile::fake()->create('delete_me.xlsx', 100);
        $filePath = $file->storeAs('templates', 'delete_me.xlsx', 'public');

        $template = ExcelTemplate::create([
            'name' => 'Template Toggle Test',
            'slug' => 'template-toggle-test',
            'category' => 'Inventori',
            'format' => 'XLSX',
            'file_path' => $filePath,
            'file_name' => 'delete_me.xlsx',
            'file_size' => 1000,
            'is_active' => true,
        ]);

        // Toggle
        $toggleRes = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.templates.toggle', $template));
        $toggleRes->assertRedirect();
        $this->assertFalse($template->fresh()->is_active);

        // Delete
        $delRes = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.templates.destroy', $template));
        $delRes->assertRedirect(route('admin.templates.index'));

        $this->assertDatabaseMissing('excel_templates', ['id' => $template->id]);
        Storage::disk('public')->assertMissing($filePath);
    }

    public function test_admin_can_download_uploaded_excel_file(): void
    {
        $file = UploadedFile::fake()->create('Template_Download_Admin.xlsx', 120);
        $filePath = $file->storeAs('templates', 'Template_Download_Admin.xlsx', 'public');

        $template = ExcelTemplate::create([
            'name' => 'Template Download Test',
            'slug' => 'template-download-test',
            'category' => 'Buku Kas',
            'format' => 'XLSX',
            'file_path' => $filePath,
            'file_name' => 'Template_Download_Admin.xlsx',
            'file_size' => 120000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.templates.download', $template));

        $response->assertOk();
        $this->assertTrue($response->headers->contains('content-disposition', 'attachment; filename=Template_Download_Admin.xlsx'));
    }

    public function test_visitor_can_view_uploaded_template_and_download_file(): void
    {
        $file = UploadedFile::fake()->create('Template_Buku_Kas_Warung.xlsx', 200);
        $filePath = $file->storeAs('templates', 'Template_Buku_Kas_Warung.xlsx', 'public');

        $template = ExcelTemplate::create([
            'name' => 'Template Buku Kas Warung',
            'slug' => 'template-buku-kas-warung',
            'category' => 'Buku Kas',
            'format' => 'XLSX',
            'description' => 'Buku kas praktis untuk pedagang warung kelontong',
            'highlights' => ['Pemasukan & Pengeluaran', 'Saldo Akhir Harian'],
            'file_path' => $filePath,
            'file_name' => 'Template_Buku_Kas_Warung.xlsx',
            'file_size' => 204800,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // 1. Visitor views index
        $indexRes = $this->get(route('template.index'));
        $indexRes->assertOk();
        $indexRes->assertSee('Template Buku Kas Warung');

        // 2. Visitor views show detail
        $showRes = $this->get(route('template.show', $template->slug));
        $showRes->assertOk();
        $showRes->assertSee('Template Buku Kas Warung');
        $showRes->assertSee('Pemasukan & Pengeluaran');

        // 3. Visitor submits lead capture form
        $leadRes = $this->postJson(route('template.download', $template->slug), [
            'name' => 'Pak Ahmad',
            'phone' => '081299887766',
            'email' => 'ahmad@warung.id',
            'business_name' => 'Warung Ahmad Barokah',
        ]);

        $leadRes->assertOk();
        $leadRes->assertJson([
            'success' => true,
            'file_name' => 'Template_Buku_Kas_Warung.xlsx',
        ]);

        $this->assertDatabaseHas('template_leads', [
            'name' => 'Pak Ahmad',
            'phone' => '081299887766',
            'template_slug' => 'template-buku-kas-warung',
        ]);

        $this->assertEquals(1, $template->fresh()->downloads_count);

        // 4. Visitor downloads file via download route
        $downloadRes = $this->get(route('template.file', $template->slug));
        $downloadRes->assertOk();
        $this->assertTrue($downloadRes->headers->contains('content-disposition', 'attachment; filename=Template_Buku_Kas_Warung.xlsx'));
    }
}
