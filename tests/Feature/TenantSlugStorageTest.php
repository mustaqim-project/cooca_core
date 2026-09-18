<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Storage\TenantStorage;
use App\Models\Business;
use App\Models\CostCategory;
use App\Models\Expense;
use App\Models\Product;
use App\Models\SubscriptionPackage;
use App\Models\SubscriptionPayment;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantSlugStorageTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        Storage::fake('public');
        Storage::fake('local');

        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);

        $this->owner = User::create([
            'name' => 'Owner Bengkel Bagema',
            'email' => 'owner@bagema.test',
            'email_verified_at' => now(),
            'phone' => '081299990001',
            'phone_verified_at' => now(),
            'password' => bcrypt('password123'),
        ]);

        $this->business = Business::create([
            'name' => 'Bengkel Bagema Motor',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'phone' => '081299990001',
            'email' => 'info@bagema.test',
        ]);

        $this->business->users()->attach($this->owner->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $this->owner->update(['active_business_id' => $this->business->id]);

        $this->unit = Unit::where('code', 'pcs')->first() ?? Unit::create(['name' => 'Pieces', 'code' => 'pcs', 'business_id' => $this->business->id]);
    }

    public function test_product_image_is_stored_in_bisnis_slug_products_folder(): void
    {
        $fakeImage = UploadedFile::fake()->image('oli-mesin.jpg', 600, 600);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post('/products', [
                'name' => 'Oli Mesin Matic Expert',
                'output_unit_id' => $this->unit->id,
                'selling_price' => 75000,
                'base_cost' => 50000,
                'costing_method' => 'simple',
                'image' => $fakeImage,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $product = Product::where('business_id', $this->business->id)->where('name', 'Oli Mesin Matic Expert')->firstOrFail();

        $this->assertNotNull($product->image_path);
        $slug = $this->business->slug;
        $this->assertStringStartsWith("bisnis/{$slug}/products/", $product->image_path);
        $this->assertTrue(str_contains($product->image_url, "bisnis/{$slug}/products/"));

        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_business_logo_is_stored_in_bisnis_slug_logo_folder(): void
    {
        $fakeLogo = UploadedFile::fake()->image('logo-bagema.png', 400, 400);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->put('/settings', [
                'name' => 'Bengkel Bagema Motor',
                'logo' => $fakeLogo,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->business->refresh();
        $slug = $this->business->slug;

        $this->assertNotNull($this->business->logo_path);
        $this->assertStringStartsWith("bisnis/{$slug}/logo/", $this->business->logo_path);
        $this->assertTrue(str_contains($this->business->logo_url, "bisnis/{$slug}/logo/"));

        Storage::disk('public')->assertExists($this->business->logo_path);
    }

    public function test_renaming_business_slug_moves_physical_folders_and_updates_database(): void
    {
        $oldSlug = $this->business->slug;
        $oldProductPath = "bisnis/{$oldSlug}/products/oli-special.jpg";

        Storage::disk('public')->put($oldProductPath, 'fake-image-bytes');

        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Oli Special Bagema',
            'output_unit_id' => $this->unit->id,
            'costing_method' => 'simple',
            'base_cost' => 45000,
            'selling_price' => 65000,
            'image_path' => $oldProductPath,
        ]);

        // Trigger rename slug
        $newSlug = 'bagema-auto-service';
        $this->business->update([
            'name' => 'Bagema Auto Service',
            'slug' => $newSlug,
        ]);

        $newProductPath = "bisnis/{$newSlug}/products/oli-special.jpg";

        // File must be physically moved to new slug dir
        Storage::disk('public')->assertExists($newProductPath);
        Storage::disk('public')->assertMissing($oldProductPath);

        // Product image_path in DB must be updated
        $product->refresh();
        $this->assertEquals($newProductPath, $product->image_path);
    }

    public function test_billing_payment_proof_is_saved_to_private_disk_and_gated(): void
    {
        $package = \App\Models\BillingPackage::first() ?? \App\Models\BillingPackage::create([
            'name' => 'Pro Plan',
            'code' => 'pro',
            'tier' => 'pro',
            'price_monthly' => 150000,
            'price_yearly' => 1500000,
            'features' => ['all'],
            'is_active' => true,
        ]);

        $fakeProof = UploadedFile::fake()->image('bukti-transfer-bank.jpg', 800, 1000);

        // Upload through private disk
        $filename = 'proof-' . Str::random(16) . '.jpg';
        $privatePath = 'billing-proofs/' . $filename;
        Storage::disk('local')->put($privatePath, $fakeProof->getContent());

        $payment = SubscriptionPayment::create([
            'user_id' => $this->owner->id,
            'business_id' => $this->business->id,
            'billing_package_id' => $package->id,
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'plan_code' => 'pro',
            'package_name' => 'Pro Plan',
            'package_duration_days' => 30,
            'cycle' => 'monthly',
            'amount' => 150000,
            'base_amount' => 150000,
            'total_amount' => 150000,
            'payment_method' => SubscriptionPayment::METHOD_BCA,
            'status' => SubscriptionPayment::STATUS_AWAITING_APPROVAL,
            'payment_proof_path' => $privatePath,
        ]);

        // Direct public access must NOT exist
        Storage::disk('public')->assertMissing($privatePath);
        Storage::disk('local')->assertExists($privatePath);

        // Owner can access via gated route
        $ownerResponse = $this->actingAs($this->owner)
            ->get(route('billing.payment.proof', $payment->id));

        $ownerResponse->assertOk();

        // Unauthorized stranger with their own business gets 403 Forbidden
        $strangerBusiness = Business::create([
            'name' => 'Bisnis Hacker',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'phone' => '081299990009',
            'email' => 'stranger@example.com',
        ]);

        $stranger = User::create([
            'name' => 'Stranger Hacker',
            'email' => 'stranger@example.com',
            'email_verified_at' => now(),
            'phone' => '081299990009',
            'phone_verified_at' => now(),
            'password' => bcrypt('password123'),
        ]);

        $strangerBusiness->users()->attach($stranger->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $stranger->update(['active_business_id' => $strangerBusiness->id]);

        Context::flush();
        $strangerResponse = $this->actingAs($stranger)
            ->withSession(['active_business_id' => $strangerBusiness->id])
            ->get(route('billing.payment.proof', $payment->id));

        $strangerResponse->assertForbidden();
    }

    public function test_expense_receipt_is_saved_to_private_disk_and_gated(): void
    {
        // Inject initial cash so expense balance check passes
        app(\App\Domain\Finance\CashLedgerService::class)->recordInflow(
            $this->business,
            1000000,
            'initial_balance',
            (string) Str::uuid(),
            'Saldo Kas Awal',
            'cash',
            $this->owner->id
        );

        $fakeReceipt = UploadedFile::fake()->image('nota-bensin.jpg', 600, 800);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post('/finance/expenses', [
                'category' => 'operasional',
                'expense_date' => now()->format('Y-m-d'),
                'amount' => 125000,
                'payment_method' => 'cash',
                'description' => 'Beli Bensin Mobil Operasional',
                'receipt_image' => $fakeReceipt,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $expense = Expense::where('business_id', $this->business->id)
            ->where('description', 'Beli Bensin Mobil Operasional')
            ->firstOrFail();

        $this->assertNotNull($expense->receipt_image_path);
        $slug = $this->business->slug;

        // Must be in private disk 'local'
        $this->assertStringStartsWith("bisnis/{$slug}/expenses/", $expense->receipt_image_path);
        Storage::disk('local')->assertExists($expense->receipt_image_path);
        Storage::disk('public')->assertMissing($expense->receipt_image_path);

        // Owner/authorized user can access via gated route
        $authorizedResponse = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('finance.expenses.receipt', $expense->id));

        $authorizedResponse->assertOk();

        // Other business user cannot access (403)
        $otherBusiness = Business::create([
            'name' => 'Toko Sebelah',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'phone' => '081299990008',
            'email' => 'sebelah@example.com',
        ]);
        $otherUser = User::create([
            'name' => 'User Toko Sebelah',
            'email' => 'sebelah@example.com',
            'email_verified_at' => now(),
            'phone' => '081299990008',
            'phone_verified_at' => now(),
            'password' => bcrypt('password123'),
        ]);
        $otherBusiness->users()->attach($otherUser->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $otherUser->update(['active_business_id' => $otherBusiness->id]);

        $otherResponse = $this->actingAs($otherUser)
            ->withSession(['active_business_id' => $otherBusiness->id])
            ->get(route('finance.expenses.receipt', $expense->id));

        $otherResponse->assertForbidden();
    }
}
