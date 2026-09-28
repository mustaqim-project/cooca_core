<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ProductGalleryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;
    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);

        $this->user = User::create([
            'name' => 'Owner Bisnis Galeri',
            'email' => 'owner-galeri@cooca.test',
            'phone' => '081234567890',
            'password' => bcrypt('password123'),
        ]);
        $this->user->forceFill(['email_verified_at' => now()])->save();

        $this->business = Business::create([
            'name' => 'Toko Kasur Nyaman',
            'slug' => 'toko-kasur',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->user->update(['active_business_id' => $this->business->id]);

        Context::setBusiness($this->business);

        $this->unit = Unit::firstOrCreate(
            ['code' => 'pcs'],
            ['name' => 'Pcs', 'category' => 'count', 'is_base' => true]
        );
    }

    public function test_product_can_be_created_with_thumbnail_and_multiple_gallery_images(): void
    {
        $this->actingAs($this->user);

        $thumbnail = UploadedFile::fake()->image('kasur_thumb.jpg', 600, 600);
        $gallery1 = UploadedFile::fake()->image('kasur_galeri_1.jpg', 600, 600);
        $gallery2 = UploadedFile::fake()->image('kasur_galeri_2.jpg', 600, 600);
        $gallery3 = UploadedFile::fake()->image('kasur_galeri_3.jpg', 600, 600);

        $response = $this->post(route('products.store'), [
            'name' => 'Kasur Springbed Alpha X',
            'output_unit_id' => $this->unit->id,
            'selling_price' => 2500000,
            'base_cost' => 1500000,
            'costing_method' => 'simple',
            'image' => $thumbnail,
            'gallery_images' => [$gallery1, $gallery2, $gallery3],
        ]);

        $response->assertRedirect(route('products.index'));

        $product = Product::where('name', 'Kasur Springbed Alpha X')->first();
        $this->assertNotNull($product);
        $this->assertNotNull($product->image_path);
        Storage::disk('public')->assertExists($product->image_path);

        $galleryImages = ProductImage::where('product_id', $product->id)->orderBy('sort_order')->get();
        $this->assertCount(3, $galleryImages);
        $this->assertEquals(1, $galleryImages[0]->sort_order);
        $this->assertEquals(2, $galleryImages[1]->sort_order);
        $this->assertEquals(3, $galleryImages[2]->sort_order);

        foreach ($galleryImages as $gImg) {
            Storage::disk('public')->assertExists($gImg->image_path);
        }

        // Test all_images accessor
        $all = $product->all_images;
        $this->assertCount(4, $all); // 1 thumbnail + 3 gallery images
        $this->assertTrue($all[0]['is_primary']);
        $this->assertFalse($all[1]['is_primary']);
    }

    public function test_product_can_be_updated_with_new_gallery_and_delete_specific_gallery_items(): void
    {
        $this->actingAs($this->user);

        $product = Product::create([
            'business_id' => $this->business->id,
            'type' => Product::TYPE_GOODS,
            'name' => 'Kasur Latex Deluxe',
            'output_unit_id' => $this->unit->id,
            'selling_price' => 3000000,
            'base_cost' => 2000000,
        ]);

        $img1 = ProductImage::create([
            'business_id' => $this->business->id,
            'product_id' => $product->id,
            'image_path' => 'tenants/' . $this->business->id . '/products/galeri1.jpg',
            'sort_order' => 1,
        ]);
        $img2 = ProductImage::create([
            'business_id' => $this->business->id,
            'product_id' => $product->id,
            'image_path' => 'tenants/' . $this->business->id . '/products/galeri2.jpg',
            'sort_order' => 2,
        ]);

        Storage::disk('public')->put($img1->image_path, 'fake1');
        Storage::disk('public')->put($img2->image_path, 'fake2');

        $newGallery = UploadedFile::fake()->image('galeri3.jpg', 600, 600);

        $response = $this->put(route('products.update', $product->id), [
            'name' => 'Kasur Latex Deluxe Updated',
            'output_unit_id' => $this->unit->id,
            'selling_price' => 3200000,
            'remove_gallery_ids' => [$img1->id],
            'gallery_images' => [$newGallery],
        ]);

        $response->assertRedirect(route('products.index'));

        // img1 should be deleted from DB and storage
        $this->assertDatabaseMissing('product_images', ['id' => $img1->id]);
        Storage::disk('public')->assertMissing($img1->image_path);

        // img2 should still exist
        $this->assertDatabaseHas('product_images', ['id' => $img2->id]);
        Storage::disk('public')->assertExists($img2->image_path);

        // New image should be added
        $remaining = ProductImage::where('product_id', $product->id)->get();
        $this->assertCount(2, $remaining);
    }

    public function test_single_gallery_image_can_be_deleted_via_route(): void
    {
        $this->actingAs($this->user);

        $product = Product::create([
            'business_id' => $this->business->id,
            'type' => Product::TYPE_GOODS,
            'name' => 'Bantal Bulu Angsa',
            'output_unit_id' => $this->unit->id,
            'selling_price' => 150000,
        ]);

        $img = ProductImage::create([
            'business_id' => $this->business->id,
            'product_id' => $product->id,
            'image_path' => 'tenants/' . $this->business->id . '/products/bantal.jpg',
            'sort_order' => 1,
        ]);
        Storage::disk('public')->put($img->image_path, 'content');

        $response = $this->delete(route('products.gallery.destroy', [$product->id, $img->id]));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('product_images', ['id' => $img->id]);
        Storage::disk('public')->assertMissing($img->image_path);
    }

    public function test_product_deletion_cleans_up_all_gallery_images(): void
    {
        $this->actingAs($this->user);

        $product = Product::create([
            'business_id' => $this->business->id,
            'type' => Product::TYPE_GOODS,
            'name' => 'Sprei Katun Jepang',
            'output_unit_id' => $this->unit->id,
            'selling_price' => 200000,
            'image_path' => 'tenants/' . $this->business->id . '/products/sprei_thumb.jpg',
        ]);
        Storage::disk('public')->put($product->image_path, 'thumb');

        $img1 = ProductImage::create([
            'business_id' => $this->business->id,
            'product_id' => $product->id,
            'image_path' => 'tenants/' . $this->business->id . '/products/sprei_g1.jpg',
            'sort_order' => 1,
        ]);
        $img2 = ProductImage::create([
            'business_id' => $this->business->id,
            'product_id' => $product->id,
            'image_path' => 'tenants/' . $this->business->id . '/products/sprei_g2.jpg',
            'sort_order' => 2,
        ]);
        Storage::disk('public')->put($img1->image_path, 'g1');
        Storage::disk('public')->put($img2->image_path, 'g2');

        $response = $this->delete(route('products.destroy', $product->id));
        $response->assertRedirect(route('products.index'));

        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $this->assertDatabaseMissing('product_images', ['id' => $img1->id]);
        $this->assertDatabaseMissing('product_images', ['id' => $img2->id]);

        Storage::disk('public')->assertMissing($product->image_path);
        Storage::disk('public')->assertMissing($img1->image_path);
        Storage::disk('public')->assertMissing($img2->image_path);
    }

    public function test_public_storefront_pdp_renders_product_gallery(): void
    {
        $product = Product::create([
            'business_id' => $this->business->id,
            'type' => Product::TYPE_GOODS,
            'name' => 'Kasur In The Box Royale',
            'slug' => 'kasur-in-the-box-royale',
            'output_unit_id' => $this->unit->id,
            'selling_price' => 4500000,
            'show_in_website' => true,
            'show_price_on_web' => true,
            'is_active' => true,
            'image_path' => 'tenants/' . $this->business->id . '/products/in_the_box_thumb.jpg',
        ]);

        ProductImage::create([
            'business_id' => $this->business->id,
            'product_id' => $product->id,
            'image_path' => 'tenants/' . $this->business->id . '/products/in_the_box_g1.jpg',
            'sort_order' => 1,
        ]);

        $response = $this->get('/' . $this->business->slug . '/produk/' . $product->slug);

        $response->assertOk();
        $response->assertSee('Kasur In The Box Royale');
        $response->assertSee('in_the_box_thumb.jpg', false);
        $response->assertSee('in_the_box_g1.jpg', false);
    }
}
