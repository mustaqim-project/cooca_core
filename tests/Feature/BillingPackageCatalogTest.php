<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Billing\EntitlementService;
use App\Models\Admin;
use App\Models\BillingPackage;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class BillingPackageCatalogTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::create(['name' => 'Package Owner', 'email' => 'package-owner@test.local', 'password' => 'password']);
        $this->business = Business::create(['name' => 'Package Business']);
        $this->business->users()->attach($this->user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);
        $this->admin = Admin::create(['name' => 'Package Admin', 'email' => 'package-admin@test.local', 'password' => Hash::make('password'), 'role' => 'super_admin', 'is_active' => true]);
    }

    public function test_default_billing_catalog_route_works_without_explicit_type(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.billing-packages.index'))
            ->assertOk()
            ->assertSee('Paket & Durasi Subscription');
    }

    public function test_each_billing_catalog_is_available_in_admin_cms(): void
    {
        $this->actingAs($this->admin, 'admin')->get(route('admin.billing-packages.index', 'subscription'))->assertOk()->assertSee('Paket & Durasi Subscription');
        $this->actingAs($this->admin, 'admin')->get(route('admin.billing-packages.index', 'ai_token'))->assertOk()->assertSee('Paket Top Up Token');
        $this->actingAs($this->admin, 'admin')->get(route('admin.billing-packages.index', 'storage'))->assertOk()->assertSee('Paket Top Up Storage');
    }

    public function test_package_order_snapshots_admin_price_and_subscription_duration(): void
    {
        $package = BillingPackage::create(['type' => 'subscription', 'code' => 'core-90', 'name' => 'Core 90 Hari', 'price' => 300000, 'duration_days' => 90, 'token_quantity' => 5000000, 'is_active' => true]);
        $payment = app(EntitlementService::class)->createPackageOrder($this->business, $this->user, $package);
        $package->update(['price' => 1, 'duration_days' => 1]);
        $payment->refresh();
        $this->assertSame(300000.0, (float) $payment->amount);
        $this->assertSame(90, $payment->package_duration_days);
        $this->assertSame($package->id, $payment->billing_package_id);
    }

    public function test_token_and_storage_packages_are_applied_after_approval(): void
    {
        $service = app(EntitlementService::class);
        $tokenPackage = BillingPackage::create(['type' => 'ai_token', 'code' => 'token-50m', 'name' => '50 Juta Token', 'price' => 300000, 'token_quantity' => 50000000, 'token_expiry_days' => 30, 'is_active' => true]);
        $storagePackage = BillingPackage::create(['type' => 'storage', 'code' => 'storage-2gb', 'name' => 'Storage 2 GB', 'price' => 100000, 'storage_bytes' => 2 * 1073741824, 'is_active' => true]);
        foreach ([$service->createPackageOrder($this->business, $this->user, $tokenPackage), $service->createPackageOrder($this->business, $this->user, $storagePackage)] as $payment) {
            $service->submitPaymentProof($payment, UploadedFile::fake()->image('proof.png'));
            $service->approvePayment($payment, $this->admin);
        }
        $this->assertDatabaseHas('ai_token_topups', ['purchased_tokens' => 50000000]);
        $this->assertDatabaseHas('owner_storage_topups', ['storage_bytes' => 2 * 1073741824]);
    }
}
