<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Test suite for verifying Phase 5 Blade Localization & I18N injection across POS ecosystem.
 *
 * @covers \App\Http\Controllers\App\PosOrderWebController
 * @covers \App\Http\Controllers\App\PosShiftWebController
 * @covers \App\Http\Controllers\App\PosTableWebController
 * @covers \App\Http\Controllers\App\PosKitchenWebController
 */
class PosAuditPhase5BladeLocalizationTest extends TestCase
{
    /**
     * Verify that both Indonesian and English POS language dictionary files exist and have 100% key parity.
     */
    public function test_pos_language_dictionaries_exist_and_have_exact_key_parity(): void
    {
        $idPath = base_path('lang/id/pos.php');
        $enPath = base_path('lang/en/pos.php');

        $this->assertFileExists($idPath, 'Indonesian POS language file lang/id/pos.php must exist.');
        $this->assertFileExists($enPath, 'English POS language file lang/en/pos.php must exist.');

        $idKeys = require $idPath;
        $enKeys = require $enPath;

        $this->assertIsArray($idKeys, 'lang/id/pos.php must return an array of translation keys.');
        $this->assertIsArray($enKeys, 'lang/en/pos.php must return an array of translation keys.');
        $this->assertNotEmpty($idKeys, 'lang/id/pos.php must not be empty.');
        $this->assertNotEmpty($enKeys, 'lang/en/pos.php must not be empty.');

        // Verify key parity between id and en
        $missingInEn = array_diff_key($idKeys, $enKeys);
        $missingInId = array_diff_key($enKeys, $idKeys);

        $this->assertEmpty($missingInEn, 'Keys in lang/id/pos.php missing from lang/en/pos.php: ' . implode(', ', array_keys($missingInEn)));
        $this->assertEmpty($missingInId, 'Keys in lang/en/pos.php missing from lang/id/pos.php: ' . implode(', ', array_keys($missingInId)));

        // Verify key count is at least 150+
        $this->assertGreaterThanOrEqual(150, count($idKeys), 'POS translation dictionary must contain comprehensive domain keys.');
    }

    /**
     * Verify that all 10 POS Blade view files exist in the repository.
     */
    public function test_all_10_pos_blade_view_files_exist(): void
    {
        $views = [
            'terminal' => resource_path('views/app/pos/terminal.blade.php'),
            'orders' => resource_path('views/app/pos/orders.blade.php'),
            'shifts' => resource_path('views/app/pos/shifts.blade.php'),
            'kitchen' => resource_path('views/app/pos/kitchen.blade.php'),
            'tables' => resource_path('views/app/pos/tables.blade.php'),
            'reports' => resource_path('views/app/pos/reports.blade.php'),
            'printers' => resource_path('views/app/pos/printers/index.blade.php'),
            'receipt' => resource_path('views/app/pos/receipt.blade.php'),
            'prep_sheet' => resource_path('views/app/pos/prep_sheet.blade.php'),
            'qr_card' => resource_path('views/app/pos/qr-card.blade.php'),
        ];

        foreach ($views as $name => $path) {
            $this->assertFileExists($path, "POS Blade view file for {$name} must exist at {$path}.");
        }
    }

    /**
     * Verify that terminal.blade.php injects window.COOCA_I18N and implements translatable quick customer modal.
     */
    public function test_terminal_blade_injects_cooca_i18n_and_quick_customer_translations(): void
    {
        $terminalPath = resource_path('views/app/pos/terminal.blade.php');
        $content = File::get($terminalPath);

        // 1. Dynamic root html lang
        $this->assertStringContainsString('<html lang="{{ str_replace(\'_\', \'-\', app()->getLocale()) }}"', $content);

        // 2. Global JS I18N dictionary injection
        $this->assertStringContainsString('window.COOCA_I18N = @json(__(\'pos\'));', $content);

        // 3. Quick Customer Modal localization
        $this->assertStringContainsString('__(\'pos.quick_customer_title\')', $content);
        $this->assertStringContainsString('__(\'pos.quick_customer_desc\')', $content);
        $this->assertStringContainsString('__(\'pos.customer_name\')', $content);
        $this->assertStringContainsString('__(\'pos.customer_phone\')', $content);
        $this->assertStringContainsString('__(\'pos.save_customer\')', $content);
        $this->assertStringContainsString('__(\'pos.saving\')', $content);
    }

    /**
     * Verify standalone POS view files have dynamic HTML locale root attributes.
     */
    public function test_standalone_views_have_dynamic_html_locale_tags(): void
    {
        $standaloneViews = [
            'terminal' => resource_path('views/app/pos/terminal.blade.php'),
            'receipt' => resource_path('views/app/pos/receipt.blade.php'),
            'qr_card' => resource_path('views/app/pos/qr-card.blade.php'),
        ];

        foreach ($standaloneViews as $name => $path) {
            $content = File::get($path);
            $this->assertStringContainsString(
                'app()->getLocale()',
                $content,
                "Standalone view {$name} must include dynamic app()->getLocale() in its html tag."
            );
        }
    }

    /**
     * Verify Laravel translation resolution for both 'id' and 'en' locales.
     */
    public function test_laravel_translation_resolution_in_both_locales(): void
    {
        // Test Indonesian
        App::setLocale('id');
        $this->assertEquals('Terminal Kasir POS', __('pos.terminal_title'));
        $this->assertEquals('Tambah Pelanggan Cepat', __('pos.quick_customer_title'));
        $this->assertEquals('Simpan Pelanggan', __('pos.save_customer'));
        $this->assertEquals('Batal', __('pos.cancel'));

        // Test English
        App::setLocale('en');
        $this->assertEquals('POS Cashier Terminal', __('pos.terminal_title'));
        $this->assertEquals('Quick Add Customer', __('pos.quick_customer_title'));
        $this->assertEquals('Save Customer', __('pos.save_customer'));
        $this->assertEquals('Cancel', __('pos.cancel'));

        // Reset to default
        App::setLocale('id');
    }

    protected function tearDown(): void
    {
        App::setLocale('id');
        parent::tearDown();
    }
}
