<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AdminSmtpManagementTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::create([
            'name' => 'SMTP Admin',
            'email' => 'smtp-admin@test.local',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_open_settings_and_smtp_cms(): void
    {
        $this->actingAs($this->admin, 'admin')->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('Pengaturan SMTP Email');
        $this->actingAs($this->admin, 'admin')->get(route('admin.smtp.index'))
            ->assertOk()
            ->assertSee('Parameter Server SMTP')
            ->assertSee('Simpan Pengaturan SMTP');
    }

    public function test_admin_smtp_settings_are_saved_to_database(): void
    {
        $this->actingAs($this->admin, 'admin')->post(route('admin.smtp.update'), [
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.example.test',
            'mail_port' => 465,
            'mail_username' => 'no-reply@example.test',
            'mail_password' => 'secret-smtp-password',
            'mail_encryption' => 'ssl',
            'mail_from_address' => 'no-reply@example.test',
            'mail_from_name' => 'Cooca Test',
        ])->assertRedirect(route('admin.smtp.index'));

        $this->assertSame('smtp.example.test', SystemSetting::get('mail_host'));
        $this->assertSame('465', SystemSetting::get('mail_port'));
        $this->assertSame('ssl', SystemSetting::get('mail_encryption'));
        $this->assertSame('secret-smtp-password', SystemSetting::get('mail_password'));
        $this->assertSame('Cooca Test', SystemSetting::get('mail_from_name'));
        $this->assertSame('smtps', Config::get('mail.mailers.smtp.scheme'));
    }
}
