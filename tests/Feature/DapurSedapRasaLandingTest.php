<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\CommerceOrder;
use App\Models\CommerceStoreSetting;
use App\Models\PosOrder;
use App\Models\PosTable;
use App\Models\Product;
use Database\Seeders\DapurSedapRasaSeeder;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DapurSedapRasaLandingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DapurSedapRasaSeeder::class);
    }

    public function test_dapur_sedap_rasa_landing_page_renders_successfully(): void
    {
        $response = $this->get('/dapur-sedap-rasa');

        $response->assertOk();
        $response->assertSee('Dapur Sedap Rasa');
        $response->assertSee('Cita Rasa Nusantara Autentik');
        $response->assertSee('Bento Rapat');
        $response->assertSee('Tumpeng Mini');
    }

    public function test_dapur_sedap_rasa_has_both_offline_and_online_po_capabilities(): void
    {
        $business = Business::where('slug', 'dapur-sedap-rasa')->firstOrFail();
        $setting = CommerceStoreSetting::where('business_id', $business->id)->firstOrFail();

        // 1. Storefront Settings verify both Offline (pickup) and Online PO capabilities
        $this->assertTrue($setting->is_storefront_enabled);
        $this->assertTrue($setting->allow_pickup, 'Offline takeaway/pickup must be allowed');
        $this->assertTrue($setting->allow_delivery, 'Catering delivery must be allowed');
        $this->assertTrue($setting->allow_scheduled_order, 'Scheduled order must be allowed');
        $this->assertTrue($setting->allow_customer_po, 'Customer PO must be allowed');
        $this->assertTrue($setting->allow_reservation, 'Dine-in reservation must be allowed');

        // 2. POS Tables verify Offline Dine-in capability
        $tableCount = PosTable::where('business_id', $business->id)->count();
        $this->assertGreaterThanOrEqual(10, $tableCount);

        // 3. Products verify both Direct Order and Pre-Order categories
        $preorderProducts = Product::where('business_id', $business->id)->where('is_preorder', true)->get();
        $directProducts = Product::where('business_id', $business->id)->where('is_preorder', false)->get();

        $this->assertGreaterThanOrEqual(5, $preorderProducts->count(), 'Must have catering PO products');
        $this->assertGreaterThanOrEqual(5, $directProducts->count(), 'Must have direct order products');

        // 4. Sample Transactions exist for offline & online PO
        $this->assertTrue(PosOrder::where('business_id', $business->id)->exists(), 'Offline POS order must exist');
        $this->assertTrue(CommerceOrder::where('business_id', $business->id)->where('order_type', CommerceOrder::TYPE_CUSTOMER_PO)->exists(), 'Online Customer PO must exist');
    }

    public function test_dapur_sedap_rasa_landing_contains_products_and_payment_methods(): void
    {
        $response = $this->get('/dapur-sedap-rasa');

        $response->assertOk();
        // Regular dishes
        $response->assertSee('Nasi Ayam Bakar Bumbu Madu');
        $response->assertSee('Paket Nasi Rendang Daging Sapi Payakumbuh');
        $response->assertSee('Rawon Daging Sapi Surabaya');

        // Catering PO products
        $response->assertSee('Paket Bento Meeting Nasi Liwet Ayam Lengkuas');
        $response->assertSee('Nasi Tumpeng Mini Nusantara Kuning Komplit');
        $response->assertSee('Paket Katering Prasmanan Nusantara');

        // Payment instructions
        $response->assertSee('BCA');
        $response->assertSee('Mandiri');
        $response->assertSee('QRIS');
    }

    public function test_dapur_sedap_rasa_legacy_route_renders_successfully(): void
    {
        $response = $this->get('/b/dapur-sedap-rasa');

        $response->assertOk();
        $response->assertSee('Dapur Sedap Rasa');
        $response->assertSee('Pesan Bareng (Group Order)');
    }

    public function test_dapur_sedap_rasa_landing_with_group_order_token(): void
    {
        $response = $this->get('/b/dapur-sedap-rasa?group_order=test-dummy-token');

        $response->assertOk();
        $response->assertSee('Dapur Sedap Rasa');
    }
}
