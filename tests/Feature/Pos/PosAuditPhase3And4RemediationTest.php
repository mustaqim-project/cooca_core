<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PosAuditPhase3And4RemediationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $businessWorkshop;
    private Business $businessLaundry;
    private Business $businessCafe;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->owner = User::create([
            'name' => 'Owner Multi Bisnis',
            'email' => 'owner.multi@example.com',
            'password' => bcrypt('password123'),
        ]);
        $this->owner->forceFill(['email_verified_at' => now()])->save();

        $this->businessWorkshop = Business::create([
            'name' => 'Bengkel Motor Jaya Sentosa',
            'email' => 'bengkel@example.com',
            'phone' => '0811111111',
            'template_code' => 'service_workshop',
        ]);
        $this->businessWorkshop->users()->attach($this->owner->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);

        $this->businessLaundry = Business::create([
            'name' => 'Super Clean Kiloan Laundry',
            'email' => 'laundry@example.com',
            'phone' => '0822222222',
            'template_code' => 'service_laundry',
        ]);
        $this->businessLaundry->users()->attach($this->owner->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);

        $this->businessCafe = Business::create([
            'name' => 'Kopi Senja Utama',
            'email' => 'cafe@example.com',
            'phone' => '0833333333',
            'template_code' => 'fnb_coffeeshop',
        ]);
        $this->businessCafe->users()->attach($this->owner->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
    }

    /**
     * Test Phase 3 (F-05): Business model industry classification and vertical context isolation.
     */
    public function test_business_model_identifies_industry_sectors_accurately_f05(): void
    {
        // 1. Workshop identification
        $this->assertTrue($this->businessWorkshop->isWorkshop());
        $this->assertFalse($this->businessWorkshop->isLaundry());
        $this->assertFalse($this->businessWorkshop->isFoodIndustry());

        // 2. Laundry identification
        $this->assertTrue($this->businessLaundry->isLaundry());
        $this->assertFalse($this->businessLaundry->isWorkshop());
        $this->assertFalse($this->businessLaundry->isFoodIndustry());

        // 3. F&B Cafe identification
        $this->assertTrue($this->businessCafe->isFoodIndustry());
        $this->assertFalse($this->businessCafe->isWorkshop());
        $this->assertFalse($this->businessCafe->isLaundry());
    }

    /**
     * Test Phase 4 (F-03/F-13): Full translation dictionaries exist and are synchronized between ID and EN.
     */
    public function test_pos_translation_dictionaries_are_complete_and_synchronized(): void
    {
        $idKeys = include resource_path('../lang/id/pos.php');
        $enKeys = include resource_path('../lang/en/pos.php');

        $this->assertIsArray($idKeys);
        $this->assertIsArray($enKeys);
        $this->assertGreaterThanOrEqual(100, count($idKeys));
        $this->assertGreaterThanOrEqual(100, count($enKeys));

        // Required Core Navigation & Terminal Keys
        $criticalKeys = [
            'terminal_title',
            'orders_title',
            'shifts_title',
            'tables_title',
            'kitchen_title',
            'prep_sheet_title',
            'printers_title',
            'search_products',
            'cart',
            'empty_cart',
            'subtotal',
            'discount',
            'tax',
            'total',
            'charge_payment',
            'payment_method',
            'method_cash',
            'method_qris',
            'method_edc_debit',
            'split_payment',
            'open_shift',
            'close_shift',
            'opening_cash',
            'blind_cash_count',
            'denomination_breakdown',
            'workshop_spk',
            'license_plate',
            'technician',
            'laundry_service',
            'laundry_weight',
            'rack_location',
            'supervisor_pin_title',
            'void_order',
            'refund_order',
            'reprint_warning',
        ];

        foreach ($criticalKeys as $key) {
            $this->assertArrayHasKey($key, $idKeys, "Key '{$key}' missing in lang/id/pos.php");
            $this->assertArrayHasKey($key, $enKeys, "Key '{$key}' missing in lang/en/pos.php");
            $this->assertNotEmpty($idKeys[$key], "Key '{$key}' is empty in lang/id/pos.php");
            $this->assertNotEmpty($enKeys[$key], "Key '{$key}' is empty in lang/en/pos.php");
        }

        // Verify that ID and EN have matching key sets
        $missingInEn = array_diff_key($idKeys, $enKeys);
        $missingInId = array_diff_key($enKeys, $idKeys);

        $this->assertEmpty($missingInEn, 'Keys present in ID but missing in EN: ' . implode(', ', array_keys($missingInEn)));
        $this->assertEmpty($missingInId, 'Keys present in EN but missing in ID: ' . implode(', ', array_keys($missingInId)));
    }

    /**
     * Test Phase 3 (F-19): Item detail modal wraps pharmacy attributes in conditional module check.
     */
    public function test_item_detail_modal_wraps_pharmacy_attributes_conditionally_f19(): void
    {
        $viewContent = file_get_contents(resource_path('views/app/pos/terminal.blade.php'));
        $this->assertStringContainsString('isPharmacy()', $viewContent);
        $this->assertStringContainsString('industry_pharmacy', $viewContent);
    }
}
