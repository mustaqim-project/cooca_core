<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\InventoryStock;
use App\Models\Invoice;
use App\Models\Material;
use App\Models\PosOrder;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TwentyIndustriesShowcaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }
    private array $expectedEmails = [
        'owner.resto@cooca.id',
        'owner.cafe@cooca.id',
        'owner.bakery@cooca.id',
        'owner.cloudkitchen@cooca.id',
        'owner.catering@cooca.id',
        'owner.frozenfood@cooca.id',
        'owner.garment@cooca.id',
        'owner.precision@cooca.id',
        'owner.furniture@cooca.id',
        'owner.craft@cooca.id',
        'owner.printing@cooca.id',
        'owner.reseller@cooca.id',
        'owner.pharmacy@cooca.id',
        'owner.agency@cooca.id',
        'owner.workshop@cooca.id',
        'owner.barbershop@cooca.id',
        'owner.laundry@cooca.id',
        'owner.contractor@cooca.id',
        'owner.event@cooca.id',
        'owner.farming@cooca.id',
    ];

    public function test_all_twenty_industry_owners_exist_with_complete_master_data(): void
    {
        $this->assertCount(20, $this->expectedEmails);

        foreach ($this->expectedEmails as $email) {
            $user = User::where('email', $email)->first();
            $this->assertNotNull($user, "User {$email} harus terdaftar di database.");

            $business = $user->businesses()->first();
            $this->assertNotNull($business, "Bisnis untuk user {$email} harus terhubung.");

            // Check location
            $this->assertGreaterThanOrEqual(1, $business->locations()->count(), "Bisnis {$business->name} harus memiliki minimal 1 lokasi.");

            // Check materials & stocks
            $materialCount = Material::where('business_id', $business->id)->count();
            $this->assertGreaterThanOrEqual(2, $materialCount, "Bisnis {$business->name} harus memiliki minimal 2 bahan baku.");

            $stockCount = InventoryStock::where('business_id', $business->id)->where('quantity', '>', 0)->count();
            $this->assertGreaterThanOrEqual(2, $stockCount, "Bisnis {$business->name} harus memiliki saldo stok awal.");

            // Check products
            $products = Product::where('business_id', $business->id)->get();
            $this->assertGreaterThanOrEqual(1, $products->count(), "Bisnis {$business->name} harus memiliki produk katalog.");

            foreach ($products as $prod) {
                $this->assertGreaterThan(0, (float) $prod->selling_price, "Harga jual produk {$prod->name} harus > 0");
                $this->assertGreaterThan(0, (float) $prod->base_cost, "HPP produk {$prod->name} harus > 0");
            }

            // Check sample sale transactions (POS Order or Invoice)
            $posCount = PosOrder::where('business_id', $business->id)->count();
            $invCount = Invoice::where('business_id', $business->id)->count();
            $this->assertTrue(($posCount + $invCount) >= 1, "Bisnis {$business->name} harus memiliki transaksi sampel (POS atau Invoice).");
        }
    }
}
