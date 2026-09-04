<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BomHeader;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\BusinessSubscription;
use App\Models\Material;
use App\Models\Product;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ProductAndRecipeImportTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);

        $this->owner = User::create([
            'name' => 'Import Owner',
            'email' => 'import-owner@test.local',
            'password' => 'password123',
        ]);

        $this->business = Business::create([
            'name' => 'Kedai Import Kopi',
            'currency' => 'IDR',
        ]);

        $ownerRole = Role::where('slug', 'owner')->firstOrFail();
        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole->id,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business, BusinessMembership::where('business_id', $this->business->id)->where('user_id', $this->owner->id)->first());

        // Default units
        Unit::firstOrCreate(['code' => 'pcs'], ['name' => 'Pcs', 'category' => 'quantity', 'is_base' => true]);
        Unit::firstOrCreate(['code' => 'cup'], ['name' => 'Cup', 'category' => 'quantity', 'is_base' => false]);
        Unit::firstOrCreate(['code' => 'gram'], ['name' => 'Gram', 'category' => 'weight', 'is_base' => false]);
        Unit::firstOrCreate(['code' => 'ml'], ['name' => 'Mililiter', 'category' => 'volume', 'is_base' => false]);
    }

    private function activateCoreSubscription(): void
    {
        BusinessSubscription::updateOrCreate(
            ['business_id' => $this->business->id],
            [
                'plan_code' => BusinessSubscription::PLAN_CORE_MONTHLY,
                'status' => BusinessSubscription::STATUS_ACTIVE,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonth(),
            ]
        );
    }

    public function test_user_can_visit_import_index_page(): void
    {
        $response = $this->actingAs($this->owner)->get(route('import.index'));
        $response->assertStatus(200);
        $response->assertSee('Import Massal Data Bisnis');
    }

    public function test_user_can_download_material_and_product_and_recipe_templates(): void
    {
        // Material Excel & CSV template
        $matXlsx = $this->actingAs($this->owner)->get(route('import.materials.template', ['format' => 'xlsx']));
        $matXlsx->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', (string) $matXlsx->headers->get('Content-Type'));

        $matCsv = $this->actingAs($this->owner)->get(route('import.materials.template', ['format' => 'csv']));
        $matCsv->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $matCsv->headers->get('Content-Type'));

        // Product Excel template
        $responseXlsx = $this->actingAs($this->owner)->get(route('import.products.template', ['format' => 'xlsx']));
        $responseXlsx->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', (string) $responseXlsx->headers->get('Content-Type'));

        // Product CSV template
        $responseCsv = $this->actingAs($this->owner)->get(route('import.products.template', ['format' => 'csv']));
        $responseCsv->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $responseCsv->headers->get('Content-Type'));

        // Recipe Excel template
        $recipeXlsx = $this->actingAs($this->owner)->get(route('import.recipes.template', ['format' => 'xlsx']));
        $recipeXlsx->assertStatus(200);

        // Recipe CSV template
        $recipeCsv = $this->actingAs($this->owner)->get(route('import.recipes.template', ['format' => 'csv']));
        $recipeCsv->assertStatus(200);
    }

    public function test_pro_user_can_preview_and_execute_material_import(): void
    {
        $this->activateCoreSubscription();

        Material::create([
            'business_id' => $this->business->id,
            'name' => 'Susu Kental Manis',
            'slug' => 'susu-kental-manis',
            'code' => 'MAT-SKM-01',
            'unit_id' => Unit::where('code', 'ml')->value('id'),
        ]);

        $csvContent = implode("\n", [
            'Nama Bahan Baku,Kode / SKU,Kategori Bahan,Satuan Dasar,Harga Beli Standar,Nama Pemasok / Supplier,Deskripsi / Catatan',
            'Susu Kental Manis,MAT-SKM-01,Dairy,ml,15,PT Susu Enak,Duplikat DB', // DB Duplicate
            'Gula Cair Fruktosa,MAT-GUL-01,Pemanis,ml,25,UD Gula Manis,Gula cair murni', // Valid New
            'Gula Cair Fruktosa,MAT-GUL-02,Pemanis,ml,25,UD Gula Manis,Duplikat File', // File Duplicate
            ',MAT-ERR-01,Lainnya,pcs,100,Supplier A,Missing Name', // Error
        ]);

        $file = UploadedFile::fake()->createWithContent('materials_preview.csv', $csvContent);

        // 1. Preview
        $previewResponse = $this->actingAs($this->owner)->postJson(route('import.materials.preview'), [
            'file' => $file,
        ]);

        $previewResponse->assertStatus(200);
        $previewResponse->assertJsonPath('data.total_rows', 4);
        $previewResponse->assertJsonPath('data.valid_count', 1);
        $previewResponse->assertJsonPath('data.duplicate_count', 2);
        $previewResponse->assertJsonPath('data.error_count', 1);

        // 2. Execute
        $rows = [
            [
                'name' => 'Gula Cair Fruktosa',
                'sku' => 'MAT-GUL-01',
                'category' => 'Pemanis',
                'unit' => 'ml',
                'unit_id' => Unit::where('code', 'ml')->value('id'),
                'purchase_price' => 25,
                'supplier_name' => 'UD Gula Manis',
                'description' => 'Gula cair murni',
                'status' => 'valid',
            ],
        ];

        $execResponse = $this->actingAs($this->owner)->postJson(route('import.materials.execute'), [
            'rows' => $rows,
            'duplicate_strategy' => 'skip',
        ]);

        $execResponse->assertStatus(200);
        $execResponse->assertJsonPath('result.imported', 1);

        $this->assertDatabaseHas('materials', [
            'business_id' => $this->business->id,
            'name' => 'Gula Cair Fruktosa',
            'code' => 'MAT-GUL-01',
        ]);
    }

    public function test_free_tier_cannot_preview_or_execute_import_due_to_entitlement(): void
    {
        // Business starts with free subscription
        $csvContent = "Nama Produk,SKU,Kategori,Satuan Output,Harga Jual\nEs Kopi,KOP-1,Minuman,cup,15000\n";
        $file = UploadedFile::fake()->createWithContent('products.csv', $csvContent);

        // Preview blocked
        $previewResponse = $this->actingAs($this->owner)->post(route('import.products.preview'), [
            'file' => $file,
        ]);
        $previewResponse->assertRedirect(route('billing.limits'));

        // Execute blocked
        $execResponse = $this->actingAs($this->owner)->post(route('import.products.execute'), [
            'rows' => [
                ['name' => 'Es Kopi', 'sku' => 'KOP-1', 'unit' => 'cup', 'selling_price' => 15000],
            ],
            'duplicate_strategy' => 'skip',
        ]);
        $execResponse->assertRedirect(route('billing.limits'));
    }

    public function test_pro_user_can_preview_products_with_duplicate_detection(): void
    {
        $this->activateCoreSubscription();

        // Create an existing product in DB
        Product::create([
            'business_id' => $this->business->id,
            'name' => 'Kopi Latte',
            'slug' => 'kopi-latte',
            'code' => 'LAT-001',
            'output_unit_id' => Unit::where('code', 'cup')->value('id'),
            'selling_price' => 20000,
            'base_cost' => 8000,
        ]);

        $csvContent = implode("\n", [
            'Nama Produk,Kode / SKU,Kategori,Satuan Output,Harga Jual,HPP Awal,Stok Minimal,Deskripsi',
            'Kopi Latte,LAT-001,Minuman,cup,20000,8000,5,Duplikat DB', // DB Duplicate
            'Matcha Ice,MAT-001,Minuman,cup,22000,9000,10,Matcha Latte', // Valid New
            'Matcha Ice,MAT-002,Minuman,cup,22000,9000,10,Duplikat File', // File Duplicate
            ',ERR-001,Minuman,cup,15000,5000,0,Tanpa Nama', // Error missing name
        ]);

        $file = UploadedFile::fake()->createWithContent('products_preview.csv', $csvContent);

        $response = $this->actingAs($this->owner)->postJson(route('import.products.preview'), [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.total_rows', 4);
        $response->assertJsonPath('data.valid_count', 1);
        $response->assertJsonPath('data.duplicate_count', 2);
        $response->assertJsonPath('data.error_count', 1);
    }

    public function test_pro_user_can_execute_product_import(): void
    {
        $this->activateCoreSubscription();

        $rows = [
            [
                'name' => 'Roti Bakar Cokelat',
                'sku' => 'ROT-001',
                'category' => 'Makanan',
                'unit' => 'pcs',
                'unit_id' => Unit::where('code', 'pcs')->value('id'),
                'selling_price' => 15000,
                'base_cost' => 6000,
                'min_stock' => 5,
                'description' => 'Roti panggang cokelat renyah',
                'status' => 'valid',
            ],
            [
                'name' => 'Kopi Tubruk',
                'sku' => 'TUB-001',
                'category' => 'Minuman',
                'unit' => 'cup',
                'unit_id' => Unit::where('code', 'cup')->value('id'),
                'selling_price' => 10000,
                'base_cost' => 3000,
                'min_stock' => 10,
                'description' => 'Kopi hitam tradisional',
                'status' => 'valid',
            ],
        ];

        $response = $this->actingAs($this->owner)->postJson(route('import.products.execute'), [
            'rows' => $rows,
            'duplicate_strategy' => 'skip',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('result.imported', 2);

        $this->assertDatabaseHas('products', [
            'business_id' => $this->business->id,
            'name' => 'Roti Bakar Cokelat',
            'code' => 'ROT-001',
        ]);

        $this->assertDatabaseHas('products', [
            'business_id' => $this->business->id,
            'name' => 'Kopi Tubruk',
            'code' => 'TUB-001',
        ]);

        // Verify CostModel was automatically created
        $roti = Product::where('business_id', $this->business->id)->where('code', 'ROT-001')->firstOrFail();
        $this->assertTrue($roti->costModels()->where('is_active', true)->exists());
    }

    public function test_pro_user_can_preview_and_execute_recipe_import(): void
    {
        $this->activateCoreSubscription();

        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Espresso Freddo',
            'slug' => 'espresso-freddo',
            'code' => 'ESP-001',
            'output_unit_id' => Unit::where('code', 'cup')->value('id'),
            'selling_price' => 25000,
        ]);

        $material1 = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Biji Kopi Arabika',
            'slug' => 'biji-kopi-arabika',
            'code' => 'MAT-KOP-01',
            'unit_id' => Unit::where('code', 'gram')->value('id'),
        ]);

        $material2 = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Es Batu Kristal',
            'slug' => 'es-batu-kristal',
            'code' => 'MAT-ICE-01',
            'unit_id' => Unit::where('code', 'gram')->value('id'),
        ]);

        // 1. Preview recipe file
        $csvContent = implode("\n", [
            'Nama / SKU Produk Jadi,Nama / SKU Bahan Baku,Jumlah Pemakaian,Satuan Bahan,Susut / Waste %,Catatan Bahan',
            'Espresso Freddo,Biji Kopi Arabika,18,gram,2,Shot extraction',
            'Espresso Freddo,Es Batu Kristal,150,gram,0,Ice cubes',
            'Produk Gaib,Es Batu Kristal,100,gram,0,Invalid product', // Error
        ]);

        $file = UploadedFile::fake()->createWithContent('recipes_preview.csv', $csvContent);

        $previewResponse = $this->actingAs($this->owner)->postJson(route('import.recipes.preview'), [
            'file' => $file,
        ]);

        $previewResponse->assertStatus(200);
        $previewResponse->assertJsonPath('data.total_rows', 3);
        $previewResponse->assertJsonPath('data.valid_count', 2);
        $previewResponse->assertJsonPath('data.error_count', 1);

        // 2. Execute recipe import
        $rows = [
            [
                'product_id' => $product->id,
                'material_id' => $material1->id,
                'quantity' => 18,
                'unit_id' => Unit::where('code', 'gram')->value('id'),
                'waste_percentage' => 2,
                'notes' => 'Shot extraction',
                'status' => 'valid',
            ],
            [
                'product_id' => $product->id,
                'material_id' => $material2->id,
                'quantity' => 150,
                'unit_id' => Unit::where('code', 'gram')->value('id'),
                'waste_percentage' => 0,
                'notes' => 'Ice cubes',
                'status' => 'valid',
            ],
        ];

        $execResponse = $this->actingAs($this->owner)->postJson(route('import.recipes.execute'), [
            'rows' => $rows,
            'duplicate_strategy' => 'skip',
        ]);

        $execResponse->assertStatus(200);
        $execResponse->assertJsonPath('result.imported', 2);

        $bomHeader = BomHeader::whereHas('costModel', fn ($q) => $q->where('product_id', $product->id))->firstOrFail();
        $this->assertSame(2, $bomHeader->items()->count());
        $this->assertTrue($bomHeader->items()->where('material_id', $material1->id)->exists());
        $this->assertTrue($bomHeader->items()->where('material_id', $material2->id)->exists());
    }
}
