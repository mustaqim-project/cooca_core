<?php

namespace Tests\Feature\Pos;

use Tests\TestCase;

class PosAuditPhase9FraudPreventionAndMicrocopyTest extends TestCase
{
    /**
     * Test 1: Verifikasi perlindungan anti double-submit dan enter spamming (F-12).
     */
    public function test_checkout_modal_implements_anti_double_submit_and_enter_prevention(): void
    {
        $terminalPath = resource_path('views/app/pos/terminal.blade.php');
        $this->assertFileExists($terminalPath);

        $content = file_get_contents($terminalPath);

        // Directive pencegahan enter spamming
        $this->assertStringContainsString('@keydown.enter.prevent', $content, 'Modal checkout harus memblokir default form submission via enter key');

        // Guard di submitCheckout()
        $this->assertStringContainsString('if (this.isProcessing) return;', $content, 'submitCheckout harus memiliki guard isProcessing');

        // Tombol checkout disabled saat isProcessing
        $this->assertStringContainsString(':disabled="isProcessing', $content, 'Tombol submit checkout harus nonaktif saat isProcessing');
    }

    /**
     * Test 2: Verifikasi sanitasi auto-clamping split payment dan limitasi 5 baris (F-17).
     */
    public function test_split_payment_implements_sanitization_and_max_rows_constraint(): void
    {
        $terminalPath = resource_path('views/app/pos/terminal.blade.php');
        $this->assertFileExists($terminalPath);

        $content = file_get_contents($terminalPath);

        // Fungsi sanitizeSplitAmount
        $this->assertStringContainsString('sanitizeSplitAmount(idx)', $content, 'terminal.blade.php harus memiliki method sanitizeSplitAmount');
        $this->assertStringContainsString('Math.max(0, val)', $content, 'sanitizeSplitAmount harus menjepit nilai minimal 0');

        // Batas 5 baris di addSplitRow()
        $this->assertStringContainsString('splitPaymentRows.length >= 5', $content, 'addSplitRow harus membatasi maksimal 5 baris metode pembayaran');

        // Disabled button pada template
        $this->assertStringContainsString(':disabled="splitPaymentRows.length >= 5"', $content, 'Tombol tambah baris harus nonaktif jika baris split >= 5');
    }

    /**
     * Test 3: Verifikasi No-Panic Microcopy, Security Trust Badge, dan sanitasi memory supervisor PIN (F-21).
     */
    public function test_supervisor_pin_implements_no_panic_microcopy_and_memory_sanitation(): void
    {
        $langIdPath = base_path('lang/id/pos.php');
        $langEnPath = base_path('lang/en/pos.php');
        $terminalPath = resource_path('views/app/pos/terminal.blade.php');

        $this->assertFileExists($langIdPath);
        $this->assertFileExists($langEnPath);
        $this->assertFileExists($terminalPath);

        $langId = require $langIdPath;
        $langEn = require $langEnPath;
        $terminalContent = file_get_contents($terminalPath);

        // Key kamus mikro-copy
        $this->assertArrayHasKey('supervisor_pin_security_note', $langId);
        $this->assertArrayHasKey('supervisor_pin_no_panic_guide', $langId);
        $this->assertArrayHasKey('authorize', $langId);

        $this->assertArrayHasKey('supervisor_pin_security_note', $langEn);
        $this->assertArrayHasKey('supervisor_pin_no_panic_guide', $langEn);
        $this->assertArrayHasKey('authorize', $langEn);

        // Memory sanitation
        $this->assertStringContainsString('closeSupervisorPinModal()', $terminalContent, 'terminal.blade.php harus memiliki method closeSupervisorPinModal');
        $this->assertStringContainsString("this.supervisorPinInput = ''", $terminalContent, 'closeSupervisorPinModal harus membersihkan input PIN dari memory');
    }
}
