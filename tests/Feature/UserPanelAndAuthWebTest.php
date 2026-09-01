<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\CostModel;
use App\Models\Material;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\BusinessTemplateSeeder;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class UserPanelAndAuthWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
        $this->seed(BusinessTemplateSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_login_and_register_views_are_accessible(): void
    {
        $this->get('/login')->assertOk()->assertSee('Cooca Core');
        $this->get('/register')->assertOk()->assertSee('Daftarkan Bisnis Anda');
    }

    public function test_user_registration_creates_user_and_business_with_template(): void
    {
        $response = $this->post('/register', [
            'name' => 'Owner Resto',
            'email' => 'owner_resto@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'business_name' => 'Restoran Nusantara',
            'template_code' => 'fnb_resto',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', ['email' => 'owner_resto@example.com']);
        $this->assertDatabaseHas('businesses', ['name' => 'Restoran Nusantara']);
        $this->assertDatabaseHas('cost_components', ['name' => 'Bahan Baku Makanan']);
    }

    public function test_dashboard_renders_with_active_business_context(): void
    {
        $user = User::create(['name' => 'Chef', 'email' => 'chef@example.com', 'password' => 'password123']);
        $biz = Business::create(['name' => 'Cafe Aroma']);
        $biz->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $biz->id]);

        $response = $this->actingAs($user)
            ->withSession(['active_business_id' => $biz->id])
            ->get('/dashboard');

        $response->assertOk()
            ->assertSee('Cafe Aroma')
            ->assertSee('Omzet Hari Ini');
    }

    public function test_calculator_view_and_ajax_calculation(): void
    {
        $user = User::create(['name' => 'Baker', 'email' => 'baker@example.com', 'password' => 'password123']);
        $biz = Business::create(['name' => 'Bakery Lezat']);
        $biz->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $biz->id]);

        $pcs = Unit::where('code', 'pcs')->firstOrFail();
        $product = Product::create(['business_id' => $biz->id, 'name' => 'Croissant', 'output_unit_id' => $pcs->id]);
        $costModel = CostModel::create(['business_id' => $biz->id, 'product_id' => $product->id, 'name' => 'Model Croissant', 'method' => CostModel::METHOD_SIMPLE]);

        // 1. Calculator Web Page
        $this->actingAs($user)
            ->withSession(['active_business_id' => $biz->id])
            ->get('/calculator')
            ->assertOk()
            ->assertSee('Kalkulator HPP & Penetapan Harga');

        // 2. AJAX calculation endpoint
        $resCalc = $this->actingAs($user)
            ->withSession(['active_business_id' => $biz->id])
            ->getJson("/calculator/calculate/{$costModel->id}")
            ->assertOk();

        $this->assertArrayHasKey('result', $resCalc->json());
    }

    public function test_materials_and_products_web_management(): void
    {
        $user = User::create(['name' => 'Manager', 'email' => 'mgr@example.com', 'password' => 'password123']);
        $biz = Business::create(['name' => 'Pabrik Tekstil']);
        $biz->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $biz->id]);

        $meter = Unit::where('code', 'm')->firstOrFail();
        $pcs = Unit::where('code', 'pcs')->firstOrFail();

        // 1. Add Material via Web form
        $this->actingAs($user)
            ->withSession(['active_business_id' => $biz->id])
            ->post('/materials', [
                'name' => 'Kain Katun Rayon',
                'unit_id' => $meter->id,
                'yield_percentage' => 100,
                'waste_percentage' => 5,
                'purchase_price' => 25000,
            ])
            ->assertRedirect('/materials');

        $this->assertDatabaseHas('materials', ['name' => 'Kain Katun Rayon', 'business_id' => $biz->id]);

        // 2. Add Product via Web form
        $this->actingAs($user)
            ->withSession(['active_business_id' => $biz->id])
            ->post('/products', [
                'name' => 'Kemeja Katun Pria',
                'output_unit_id' => $pcs->id,
                'costing_method' => 'recipe_bom',
            ])
            ->assertRedirect('/products');

        $this->assertDatabaseHas('products', ['name' => 'Kemeja Katun Pria', 'business_id' => $biz->id]);

        // 3. CMS CRUD: Supplier
        $resSupplier = $this->actingAs($user)
            ->withSession(['active_business_id' => $biz->id])
            ->post('/suppliers', [
                'name' => 'PT Tekstil Maju Jaya',
                'contact_person' => 'Budi',
                'phone' => '0812345678',
            ]);
        $this->assertDatabaseHas('suppliers', ['name' => 'PT Tekstil Maju Jaya', 'business_id' => $biz->id]);

        // 4. CMS CRUD: Custom Unit
        $this->actingAs($user)
            ->withSession(['active_business_id' => $biz->id])
            ->post('/units', [
                'code' => 'roll',
                'name' => 'Roll Kain',
                'category' => 'length',
            ]);
        $this->assertDatabaseHas('units', ['code' => 'roll', 'business_id' => $biz->id]);

        // 5. CMS CRUD: Product Category
        $this->actingAs($user)
            ->withSession(['active_business_id' => $biz->id])
            ->post('/product-categories', [
                'name' => 'Pakaian Dewasa',
                'description' => 'Katalog fashion dewasa',
            ]);
        $this->assertDatabaseHas('product_categories', ['name' => 'Pakaian Dewasa', 'business_id' => $biz->id]);

        // 6. CMS CRUD: Material Category
        $this->actingAs($user)
            ->withSession(['active_business_id' => $biz->id])
            ->post('/material-categories', [
                'name' => 'Kain & Tekstil',
                'description' => 'Bahan mentah kain',
            ]);
        $this->assertDatabaseHas('material_categories', ['name' => 'Kain & Tekstil', 'business_id' => $biz->id]);

        $this->assertDatabaseHas('products', ['name' => 'Kemeja Katun Pria', 'business_id' => $biz->id]);
    }

    public function test_simulator_and_profitability_views_render(): void
    {
        $user = User::create(['name' => 'Analyst', 'email' => 'analyst@example.com', 'password' => 'password123']);
        $biz = Business::create(['name' => 'Solusi Industri']);
        $biz->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $biz->id]);

        $this->actingAs($user)
            ->withSession(['active_business_id' => $biz->id])
            ->get('/simulator')
            ->assertOk()
            ->assertSee('What-If & Sensitivity Simulator');

        $this->actingAs($user)
            ->withSession(['active_business_id' => $biz->id])
            ->get('/profitability')
            ->assertOk()
            ->assertSee('Break-Even Point (BEP)');

        $this->actingAs($user)
            ->withSession(['active_business_id' => $biz->id])
            ->get('/reports')
            ->assertOk()
            ->assertSee('Laporan HPP & Analitik Struktur Biaya');

        $this->actingAs($user)
            ->withSession(['active_business_id' => $biz->id])
            ->get('/labor-machines')
            ->assertOk()
            ->assertSee('Tarif Tenaga Kerja Langsung');

        $this->actingAs($user)
            ->withSession(['active_business_id' => $biz->id])
            ->get('/settings')
            ->assertOk()
            ->assertSee('Pengaturan Bisnis & Template Industri');
    }

    public function test_tenant_switcher(): void
    {
        $user = User::create(['name' => 'Multi Owner', 'email' => 'multi@example.com', 'password' => 'password123']);
        $biz1 = Business::create(['name' => 'Bisnis A']);
        $biz2 = Business::create(['name' => 'Bisnis B']);
        $biz1->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $biz2->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $biz1->id]);

        $this->actingAs($user)
            ->get('/select-business')
            ->assertOk()
            ->assertSee('Bisnis A')
            ->assertSee('Bisnis B');

        $this->actingAs($user)
            ->post('/select-business', ['business_id' => $biz2->id])
            ->assertRedirect('/dashboard');

        $this->assertEquals($biz2->id, $user->fresh()->active_business_id);
    }

    public function test_free_plan_restricts_adding_employees(): void
    {
        $owner = User::create(['name' => 'Solo Owner', 'email' => 'solo@example.com', 'password' => 'password123']);
        $biz = Business::create(['name' => 'Kedai Solo']);
        $biz->users()->attach($owner->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $owner->update(['active_business_id' => $biz->id]);

        $employee = User::create(['name' => 'Staf Kasir', 'email' => 'kasir@example.com', 'password' => 'password123']);

        // Attempt to add employee on Free Plan
        $response = $this->actingAs($owner)
            ->withSession(['active_business_id' => $biz->id])
            ->post(route('settings.members.store'), [
                'email' => 'kasir@example.com',
                'role' => 'cashier',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('business_users', ['user_id' => $employee->id, 'business_id' => $biz->id]);
    }

    public function test_owner_can_add_and_remove_employee_in_core_plan(): void
    {
        $owner = User::create(['name' => 'Owner Maju', 'email' => 'maju@example.com', 'password' => 'password123']);
        $biz = Business::create(['name' => 'Resto Maju']);
        $biz->users()->attach($owner->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $owner->update(['active_business_id' => $biz->id]);

        // Upgrade to Core Plan
        app(\App\Domain\Billing\EntitlementService::class)->upgradeToCore($biz, 'monthly');

        $employee = User::create(['name' => 'Siti Kasir', 'email' => 'siti@example.com', 'password' => 'password123']);

        // Add employee
        $addResponse = $this->actingAs($owner)
            ->withSession(['active_business_id' => $biz->id])
            ->post(route('settings.members.store'), [
                'email' => 'siti@example.com',
                'role' => 'cashier',
            ]);

        $addResponse->assertRedirect();
        $addResponse->assertSessionHas('success');
        $this->assertDatabaseHas('business_users', [
            'business_id' => $biz->id,
            'user_id' => $employee->id,
            'role' => 'cashier',
        ]);

        $membership = \App\Models\BusinessMembership::where('business_id', $biz->id)
            ->where('user_id', $employee->id)
            ->firstOrFail();

        // Remove employee
        $removeResponse = $this->actingAs($owner)
            ->withSession(['active_business_id' => $biz->id])
            ->delete(route('settings.members.destroy', $membership->id));

        $removeResponse->assertRedirect();
        $removeResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('business_users', [
            'business_id' => $biz->id,
            'user_id' => $employee->id,
        ]);
    }
}
