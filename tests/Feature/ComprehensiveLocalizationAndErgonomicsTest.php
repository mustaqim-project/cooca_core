<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\App;
use Tests\TestCase;

/**
 * Validates Full-Stack Dual-Language Localization (ID/EN),
 * Dictionary Key Parity across 7 Modules, and Boomer Reassurance Microcopy (Fase 8).
 */
class ComprehensiveLocalizationAndErgonomicsTest extends TestCase
{
    private array $modules = [
        'common',
        'inventory',
        'pos',
        'products',
        'social_media',
        'warehouse',
        'tax',
        'marketplace',
        'finance',
    ];

    /**
     * Test that every domain language dictionary exists in both ID and EN
     * and maintains 100% key parity with no missing translations.
     */
    public function test_all_domain_dictionaries_have_100_percent_key_parity(): void
    {
        foreach ($this->modules as $module) {
            $idFile = base_path("lang/id/{$module}.php");
            $enFile = base_path("lang/en/{$module}.php");

            $this->assertFileExists($idFile, "Indonesian language file for {$module} must exist.");
            $this->assertFileExists($enFile, "English language file for {$module} must exist.");

            $idKeys = array_keys(require $idFile);
            $enKeys = array_keys(require $enFile);

            sort($idKeys);
            sort($enKeys);

            $missingInEn = array_diff($idKeys, $enKeys);
            $missingInId = array_diff($enKeys, $idKeys);

            $this->assertEmpty($missingInEn, "Keys missing in lang/en/{$module}.php: " . implode(', ', $missingInEn));
            $this->assertEmpty($missingInId, "Keys missing in lang/id/{$module}.php: " . implode(', ', $missingInId));
        }
    }

    /**
     * Test that translation helper resolves keys accurately in both locales
     * and performs dynamic parameter substitution.
     */
    public function test_dynamic_locale_switching_and_parameter_interpolation(): void
    {
        // 1. Indonesian (ID)
        App::setLocale('id');
        $this->assertEquals('Produk berhasil ditambahkan.', __('products.created_success'));
        $this->assertEquals('Don\'t worry: Your past transaction history and bookkeeping records remain safe and secure.', __('common.no_panic_microcopy', [], 'en'));
        $this->assertEquals('Tenang: Riwayat data dan pembukuan masa lalu Anda tetap aman tersimpan.', __('common.no_panic_microcopy'));
        $this->assertEquals('Transaksi #ORD-101 berhasil dibatalkan (void).', __('pos.order_voided_successfully', ['order_number' => 'ORD-101']));
        $this->assertEquals('Saluran toko Shopee (Toko Berkah) berhasil dihubungkan.', __('marketplace.channel_connected', ['channel' => 'Shopee', 'shop_name' => 'Toko Berkah']));

        // 2. English (EN)
        App::setLocale('en');
        $this->assertEquals('Product has been created successfully.', __('products.created_success'));
        $this->assertEquals('Don\'t worry: Your past transaction history and bookkeeping records remain safe and secure.', __('common.no_panic_microcopy'));
        $this->assertEquals('Transaction #ORD-101 has been voided successfully.', __('pos.order_voided_successfully', ['order_number' => 'ORD-101']));
        $this->assertEquals('Shopee channel store (Toko Berkah) has been connected successfully.', __('marketplace.channel_connected', ['channel' => 'Shopee', 'shop_name' => 'Toko Berkah']));
        $this->assertEquals('Live analytics and metrics data have been refreshed.', __('social_media.insights_refreshed'));
        $this->assertEquals('Warehouse / branch has been added successfully.', __('warehouse.created_success'));
        $this->assertEquals('Tax compliance simulation has been calculated successfully.', __('tax.simulation_calculated'));
        $this->assertEquals('Operational expense entry has been saved successfully.', __('finance.expense_saved'));
    }

    /**
     * Test that reassurance microcopy exists and provides clear, comforting guidance.
     */
    public function test_reassurance_no_panic_microcopy_availability(): void
    {
        App::setLocale('id');
        $idMicrocopy = __('common.no_panic_microcopy');
        $this->assertNotEmpty($idMicrocopy);
        $this->assertStringContainsString('Tenang', $idMicrocopy);
        $this->assertStringContainsString('aman', $idMicrocopy);

        App::setLocale('en');
        $enMicrocopy = __('common.no_panic_microcopy');
        $this->assertNotEmpty($enMicrocopy);
        $this->assertStringContainsString('worry', $enMicrocopy);
        $this->assertStringContainsString('safe', $enMicrocopy);
    }
}
