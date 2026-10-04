<?php

namespace Tests\Feature\Pos;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PosAuditPhase8HigPaletteAndPollingTest extends TestCase
{
    /**
     * Test 1: Verifikasi prep_sheet.blade.php bebas dari class emerald dan menggunakan palet Apple HIG (#34C759, #007AFF, #AF52DE, #FF3B30).
     */
    public function test_prep_sheet_blade_uses_apple_hig_palette_without_emerald_classes(): void
    {
        $viewPath = resource_path('views/app/pos/prep_sheet.blade.php');
        $this->assertFileExists($viewPath);

        $content = file_get_contents($viewPath);

        // Tidak boleh ada class emerald-* non-standar
        $this->assertStringNotContainsString('emerald-', $content, 'prep_sheet.blade.php tidak boleh menggunakan class non-standar emerald-*');

        // Harus menggunakan token Apple HIG
        $this->assertStringContainsString('#34C759', $content, 'prep_sheet.blade.php harus menggunakan Apple System Green #34C759');
        $this->assertStringContainsString('#007AFF', $content, 'prep_sheet.blade.php harus menggunakan Apple System Blue #007AFF');
        $this->assertStringContainsString('#AF52DE', $content, 'prep_sheet.blade.php harus menggunakan Apple System Purple #AF52DE');
        $this->assertStringContainsString('#FF3B30', $content, 'prep_sheet.blade.php harus menggunakan Apple System Red #FF3B30');
    }

    /**
     * Test 2: Verifikasi terminal.blade.php mengimplementasikan state koneksi online/offline, listener, dan topbar badge.
     */
    public function test_terminal_blade_implements_connectivity_detection_and_hig_badge(): void
    {
        $viewPath = resource_path('views/app/pos/terminal.blade.php');
        $this->assertFileExists($viewPath);

        $content = file_get_contents($viewPath);

        // State isOnline
        $this->assertStringContainsString('isOnline:', $content, 'terminal.blade.php harus menginisialisasi state isOnline');
        $this->assertStringContainsString("window.addEventListener('online'", $content, 'terminal.blade.php harus memantau event online');
        $this->assertStringContainsString("window.addEventListener('offline'", $content, 'terminal.blade.php harus memantau event offline');

        // Badge Topbar & Drawer
        $this->assertStringContainsString("x-text=\"isOnline ? 'Online' : 'Offline'\"", $content, 'terminal.blade.php harus merender teks indikator online/offline');
        $this->assertStringContainsString('#34C759', $content, 'terminal.blade.php harus menggunakan #34C759 untuk status online');
        $this->assertStringContainsString('#FF3B30', $content, 'terminal.blade.php harus menggunakan #FF3B30 untuk status offline');
    }

    /**
     * Test 3: Verifikasi terminal.blade.php dan kitchen.blade.php mengimplementasikan Visibility-Aware Smart Polling.
     */
    public function test_terminal_and_kitchen_implement_page_visibility_smart_polling(): void
    {
        $terminalPath = resource_path('views/app/pos/terminal.blade.php');
        $kitchenPath = resource_path('views/app/pos/kitchen.blade.php');

        $this->assertFileExists($terminalPath);
        $this->assertFileExists($kitchenPath);

        $terminalContent = file_get_contents($terminalPath);
        $kitchenContent = file_get_contents($kitchenPath);

        // Terminal smart polling
        $this->assertStringContainsString('document.hidden', $terminalContent, 'terminal.blade.php harus memeriksa document.hidden sebelum polling');
        $this->assertStringContainsString("'visibilitychange'", $terminalContent, 'terminal.blade.php harus mendengarkan event visibilitychange');

        // Kitchen KDS smart polling
        $this->assertStringContainsString('document.hidden', $kitchenContent, 'kitchen.blade.php harus memeriksa document.hidden sebelum polling');
        $this->assertStringContainsString("'visibilitychange'", $kitchenContent, 'kitchen.blade.php harus mendengarkan event visibilitychange');
    }
}
