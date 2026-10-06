<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use Illuminate\Support\Facades\App;
use Tests\TestCase;

class PosLanguageParityTest extends TestCase
{
    private string $idPath;
    private string $enPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->idPath = base_path('lang/id/pos.php');
        $this->enPath = base_path('lang/en/pos.php');
    }

    public function test_pos_language_files_exist(): void
    {
        $this->assertFileExists($this->idPath, 'Indonesian POS translation file lang/id/pos.php must exist.');
        $this->assertFileExists($this->enPath, 'English POS translation file lang/en/pos.php must exist.');
    }

    public function test_pos_language_dictionaries_have_exact_one_to_one_key_parity(): void
    {
        $idTranslations = require $this->idPath;
        $enTranslations = require $this->enPath;

        $this->assertIsArray($idTranslations, 'lang/id/pos.php must return an array.');
        $this->assertIsArray($enTranslations, 'lang/en/pos.php must return an array.');

        $missingInEn = array_diff_key($idTranslations, $enTranslations);
        $missingInId = array_diff_key($enTranslations, $idTranslations);

        $this->assertEmpty(
            $missingInEn,
            'Keys present in lang/id/pos.php but missing in lang/en/pos.php: ' . implode(', ', array_keys($missingInEn))
        );

        $this->assertEmpty(
            $missingInId,
            'Keys present in lang/en/pos.php but missing in lang/id/pos.php: ' . implode(', ', array_keys($missingInId))
        );

        $this->assertCount(
            count($idTranslations),
            $enTranslations,
            'Translation key counts between ID and EN must match exactly.'
        );
    }

    public function test_no_empty_translations_in_pos_dictionaries(): void
    {
        $idTranslations = require $this->idPath;
        $enTranslations = require $this->enPath;

        foreach ($idTranslations as $key => $val) {
            $this->assertNotEmpty($val, "Empty Indonesian translation for key: pos.{$key}");
            $this->assertIsString($val, "Indonesian translation must be string for key: pos.{$key}");
        }

        foreach ($enTranslations as $key => $val) {
            $this->assertNotEmpty($val, "Empty English translation for key: pos.{$key}");
            $this->assertIsString($val, "English translation must be string for key: pos.{$key}");
        }
    }

    public function test_laravel_trans_helper_resolves_pos_keys_in_both_locales(): void
    {
        // Test ID locale
        App::setLocale('id');
        $this->assertEquals('Terminal Kasir POS', __('pos.terminal_title'));
        $this->assertEquals('Riwayat Transaksi POS', __('pos.orders_title'));
        $this->assertEquals('Sesi Shift Kasir', __('pos.shifts_title'));
        $this->assertEquals('Manajemen Meja & QR Resto', __('pos.tables_title'));
        $this->assertEquals('Kitchen Display System (KDS)', __('pos.kitchen_title'));
        $this->assertEquals('Lembar Prep Dapur & Katering', __('pos.prep_sheet_title'));
        $this->assertEquals('Printer Hardware & Laci Kas', __('pos.printers_title'));
        $this->assertEquals('Laporan Kasir & Penjualan POS', __('pos.reports_title'));

        // Test EN locale
        App::setLocale('en');
        $this->assertEquals('POS Cashier Terminal', __('pos.terminal_title'));
        $this->assertEquals('POS Transaction History', __('pos.orders_title'));
        $this->assertEquals('Cashier Shift Sessions', __('pos.shifts_title'));
        $this->assertEquals('Dine-in Tables & QR Management', __('pos.tables_title'));
        $this->assertEquals('Kitchen Display System (KDS)', __('pos.kitchen_title'));
        $this->assertEquals('Kitchen & Catering Prep Sheet', __('pos.prep_sheet_title'));
        $this->assertEquals('Hardware Printers & Cash Drawer', __('pos.printers_title'));
        $this->assertEquals('POS Sales & Cashier Reports', __('pos.reports_title'));
    }

    public function test_parametric_translations_work_correctly(): void
    {
        App::setLocale('id');
        $this->assertEquals('5 Item', __('pos.items_count', ['count' => 5]));
        $this->assertEquals('Kapasitas 4 Kursi', __('pos.table_capacity', ['count' => 4]));
        $this->assertEquals('Meja 12', __('pos.table_number_display', ['number' => 12]));
        $this->assertEquals('SALINAN (CETAKAN KE-2)', __('pos.reprint_badge_count', ['count' => 2]));

        App::setLocale('en');
        $this->assertEquals('5 Item(s)', __('pos.items_count', ['count' => 5]));
        $this->assertEquals('Capacity 4 Seats', __('pos.table_capacity', ['count' => 4]));
        $this->assertEquals('Table 12', __('pos.table_number_display', ['number' => 12]));
        $this->assertEquals('Duplicate (Copy #2)', __('pos.reprint_badge_count', ['count' => 2]));
    }

    protected function tearDown(): void
    {
        App::setLocale('id');
        parent::tearDown();
    }
}
