<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminErrorLogTest extends TestCase
{
    use RefreshDatabase;

    private string $testLogFile;
    private string $testLogPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testLogFile = 'test-diagnostics-' . uniqid() . '.log';
        $this->testLogPath = storage_path('logs/' . $this->testLogFile);

        $sampleLogs = implode("\n", [
            '[2026-09-16 06:10:00] local.INFO: Application booted successfully [] []',
            '[2026-09-16 06:15:30] local.WARNING: High memory usage threshold exceeded {"memory": "92MB"} []',
            '[2026-09-16 06:20:15] local.ERROR: RuntimeException: DatabaseConnectionTimeoutException in /app/Service.php:42',
            '#0 /app/Database/Connection.php(112): executeQuery()',
            '#1 /app/Http/Kernel.php(45): handleRequest()',
            '[2026-09-16 06:25:00] local.CRITICAL: Fatal Error in Worker Process [] []',
        ]);

        File::put($this->testLogPath, $sampleLogs);
    }

    protected function tearDown(): void
    {
        if (File::exists($this->testLogPath)) {
            File::delete($this->testLogPath);
        }

        parent::tearDown();
    }

    private function makeAdmin(): Admin
    {
        return Admin::factory()->create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@cooca.id',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_error_logs(): void
    {
        $response = $this->get(route('admin.error-logs.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_access_error_logs_and_view_diagnostic_stats(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.error-logs.index', [
            'file' => $this->testLogFile,
        ]));

        $response->assertOk();
        $response->assertSee('Log Error &amp; Diagnostik Sistem', false);
        $response->assertSee('Pusat Diagnostik &amp; Log Sistem', false);
        $response->assertSee('Total Baris Log');
        $response->assertSee('Errors &amp; Critical', false);
        $response->assertSee('DatabaseConnectionTimeoutException');
        $response->assertSee($this->testLogFile);
    }

    public function test_admin_can_filter_logs_by_level(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.error-logs.index', [
            'file' => $this->testLogFile,
            'level' => 'error',
        ]));

        $response->assertOk();
        $response->assertSee('DatabaseConnectionTimeoutException');
        $response->assertSee('Fatal Error in Worker Process');
        $response->assertDontSee('High memory usage threshold exceeded');
    }

    public function test_admin_can_search_logs_by_keyword(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.error-logs.index', [
            'file' => $this->testLogFile,
            'search' => 'DatabaseConnectionTimeoutException',
        ]));

        $response->assertOk();
        $response->assertSee('DatabaseConnectionTimeoutException');
        $response->assertDontSee('Fatal Error in Worker Process');
    }

    public function test_admin_can_request_logs_as_json(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->getJson(route('admin.error-logs.index', [
            'file' => $this->testLogFile,
        ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'file',
            'stats' => ['total', 'errors', 'warnings', 'info'],
            'logs',
        ]);
        $this->assertEquals(4, $response->json('stats.total'));
    }

    public function test_admin_can_download_raw_log_file(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.error-logs.download', [
            'file' => $this->testLogFile,
        ]));

        $response->assertOk();
        $this->assertTrue(str_contains((string) $response->headers->get('content-type'), 'text/plain'));
    }

    public function test_admin_can_clear_log_file(): void
    {
        $admin = $this->makeAdmin();

        $this->assertNotEmpty(File::get($this->testLogPath));

        $response = $this->actingAs($admin, 'admin')->delete(route('admin.error-logs.clear'), [
            'file' => $this->testLogFile,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEmpty(File::get($this->testLogPath));
    }

    public function test_path_traversal_attempts_are_safely_sanitized(): void
    {
        $admin = $this->makeAdmin();

        // Attempting path traversal should be stripped by basename() to .env
        $response = $this->actingAs($admin, 'admin')->get(route('admin.error-logs.download', [
            'file' => '../../.env',
        ]));

        // Since storage/logs/.env does not exist, it safely redirects with error
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_daily_log_file_rotation_and_metadata_are_properly_recognized(): void
    {
        $admin = $this->makeAdmin();
        $todayLogFile = 'laravel-' . date('Y-m-d') . '.log';

        // Default channel configuration check
        $this->assertEquals('daily', config('logging.default'));

        $response = $this->actingAs($admin, 'admin')->get(route('admin.error-logs.index'));

        $response->assertOk();
        $response->assertSee('Rotasi Harian (30 Hari)');
        $response->assertSee($todayLogFile);

        // JSON check for files_metadata structure
        $jsonResponse = $this->actingAs($admin, 'admin')->getJson(route('admin.error-logs.index'));
        $jsonResponse->assertOk();
        $filesMeta = $jsonResponse->json('files_metadata');
        $this->assertIsArray($filesMeta);
        $this->assertArrayHasKey($todayLogFile, $filesMeta);
        $this->assertTrue($filesMeta[$todayLogFile]['is_today']);
        $this->assertEquals($todayLogFile, $filesMeta[$todayLogFile]['name']);
    }
}
