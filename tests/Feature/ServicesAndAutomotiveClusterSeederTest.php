<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\CommerceReservation;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Database\Seeders\ServicesAndAutomotiveClusterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class ServicesAndAutomotiveClusterSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ServicesAndAutomotiveClusterSeeder::class);
    }

    public function test_six_services_and_automotive_businesses_are_seeded(): void
    {
        $expectedTemplates = [
            'service_workshop'       => 'garasi-otomotif-prima',
            'service_autodetailing'  => 'kilau-auto-detailing',
            'service_barbershop'     => 'gentleman-cut-barbershop',
            'service_laundry'        => 'freshclean-laundry',
            'service_event'          => 'vow-vision-event-organizer',
            'service_agency'         => 'nexus-digital-creative',
        ];

        foreach ($expectedTemplates as $templateCode => $slug) {
            $business = Business::where('slug', $slug)->first();
            $this->assertNotNull($business, "Bisnis dengan slug {$slug} harus ada.");
            $this->assertEquals($templateCode, $business->template_code);
            $this->assertTrue($business->isServiceSector(), "Bisnis {$slug} harus teridentifikasi sebagai sektor jasa.");
        }
    }

    public function test_owner_and_staff_accounts_can_authenticate_and_have_active_businesses(): void
    {
        $accounts = [
            // Bengkel
            ['email' => 'bengkel.owner@cooca.id', 'role' => 'owner', 'biz_slug' => 'garasi-otomotif-prima'],
            ['email' => 'mekanik.agus@cooca.id', 'role' => 'staff', 'biz_slug' => 'garasi-otomotif-prima'],
            ['email' => 'mekanik.doni@cooca.id', 'role' => 'staff', 'biz_slug' => 'garasi-otomotif-prima'],
            // Detailing
            ['email' => 'detailing.owner@cooca.id', 'role' => 'owner', 'biz_slug' => 'kilau-auto-detailing'],
            ['email' => 'detailer.bayu@cooca.id', 'role' => 'staff', 'biz_slug' => 'kilau-auto-detailing'],
            // Barbershop
            ['email' => 'barbershop.owner@cooca.id', 'role' => 'owner', 'biz_slug' => 'gentleman-cut-barbershop'],
            ['email' => 'kapster.budi@cooca.id', 'role' => 'staff', 'biz_slug' => 'gentleman-cut-barbershop'],
            // Laundry
            ['email' => 'laundry.owner@cooca.id', 'role' => 'owner', 'biz_slug' => 'freshclean-laundry'],
            ['email' => 'operator.siti@cooca.id', 'role' => 'staff', 'biz_slug' => 'freshclean-laundry'],
            // Event Organizer
            ['email' => 'event.owner@cooca.id', 'role' => 'owner', 'biz_slug' => 'vow-vision-event-organizer'],
            ['email' => 'planner.anisa@cooca.id', 'role' => 'staff', 'biz_slug' => 'vow-vision-event-organizer'],
            // Creative Agency
            ['email' => 'agency.owner@cooca.id', 'role' => 'owner', 'biz_slug' => 'nexus-digital-creative'],
            ['email' => 'designer.kenzo@cooca.id', 'role' => 'staff', 'biz_slug' => 'nexus-digital-creative'],
        ];

        foreach ($accounts as $acc) {
            $user = User::where('email', $acc['email'])->first();
            $this->assertNotNull($user, "User {$acc['email']} harus terdaftar.");
            $this->assertTrue(Hash::check('password123', $user->password), "Password akun {$acc['email']} harus valid.");
            
            $business = Business::where('slug', $acc['biz_slug'])->first();
            $this->assertEquals($business->id, $user->active_business_id, "Active business id harus cocok.");
            $this->assertTrue($user->businesses->contains($business), "User {$acc['email']} harus memiliki relasi ke bisnis {$acc['biz_slug']}.");
        }
    }

    public function test_services_and_spareparts_are_correctly_categorized_and_typed(): void
    {
        $workshopBiz = Business::where('slug', 'garasi-otomotif-prima')->first();
        $this->assertNotNull($workshopBiz);

        // Verify services (type = 'service')
        $services = Product::where('business_id', $workshopBiz->id)->where('type', Product::TYPE_SERVICE)->get();
        $this->assertGreaterThanOrEqual(5, $services->count());
        $this->assertTrue($services->contains('code', 'SRV-WS-001'));

        // Verify physical goods (type = 'goods' with inventory stock)
        $goods = Product::where('business_id', $workshopBiz->id)->where('type', Product::TYPE_GOODS)->get();
        $this->assertGreaterThanOrEqual(5, $goods->count());
        $this->assertTrue($goods->contains('code', 'PART-OIL-01'));

        $oil = Product::where('business_id', $workshopBiz->id)->where('code', 'PART-OIL-01')->first();
        $stock = InventoryStock::where('product_id', $oil->id)->first();
        $this->assertNotNull($stock);
        $this->assertEquals(25.0, (float) $stock->quantity);
    }

    public function test_pos_orders_contain_spk_vehicle_and_laundry_data(): void
    {
        $workshopBiz = Business::where('slug', 'garasi-otomotif-prima')->first();
        $workshopOrder = PosOrder::where('business_id', $workshopBiz->id)
            ->where('vehicle_license_plate', 'B 1988 XYZ')
            ->first();

        $this->assertNotNull($workshopOrder, "Order bengkel dengan nopol B 1988 XYZ harus ada.");
        $this->assertEquals('Toyota Innova Zenix 2023', $workshopOrder->vehicle_model);
        $this->assertEquals(18450, $workshopOrder->vehicle_mileage);
        $this->assertNotNull($workshopOrder->technician_id);

        $laundryBiz = Business::where('slug', 'freshclean-laundry')->first();
        $laundryOrder = PosOrder::where('business_id', $laundryBiz->id)
            ->where('rack_location', 'Rak B-04')
            ->first();

        $this->assertNotNull($laundryOrder, "Order laundry dengan lokasi Rak B-04 harus ada.");
        $this->assertEquals(6.50, (float) $laundryOrder->laundry_weight_kg);
        $this->assertEquals('ironing', $laundryOrder->laundry_status);
    }

    public function test_commerce_reservations_are_scheduled_for_service_businesses(): void
    {
        $barberBiz = Business::where('slug', 'gentleman-cut-barbershop')->first();
        $barberReservation = CommerceReservation::where('business_id', $barberBiz->id)
            ->where('customer_name', 'Reza Rahadian')
            ->first();

        $this->assertNotNull($barberReservation, "Reservasi barbershop untuk Reza Rahadian harus ada.");
        $this->assertEquals('14:00', substr((string) $barberReservation->time_slot, 0, 5));
        $this->assertEquals(CommerceReservation::STATUS_CONFIRMED, $barberReservation->status);
    }
}
