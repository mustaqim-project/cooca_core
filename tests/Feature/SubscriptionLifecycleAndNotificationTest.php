<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Console\Commands\ProcessSubscriptionLifecycleCommand;
use App\Domain\Billing\EntitlementService;
use App\Mail\PaymentApprovedInvoiceMail;
use App\Mail\PaymentUploadedAdminMail;
use App\Mail\PlanExpiryReminderMail;
use App\Models\Admin;
use App\Models\BillingPackage;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\SubscriptionPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubscriptionLifecycleAndNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_submit_payment_proof_sends_email_to_admin(): void
    {
        Mail::fake();
        Storage::fake('public');

        $user = User::factory()->create(['name' => 'Owner Test', 'email' => 'owner@example.com']);
        $business = Business::create(['name' => 'Kedai Kopi Test', 'currency' => 'IDR']);
        $business->users()->attach($user->id, ['id' => (string) \Illuminate\Support\Str::uuid(), 'role' => 'owner']);

        $package = BillingPackage::create([
            'type' => BillingPackage::TYPE_SUBSCRIPTION,
            'code' => 'core_monthly',
            'name' => 'Paket Cooca UMKM Pro',
            'price' => 149000,
            'duration_days' => 30,
            'is_active' => true,
        ]);

        $payment = SubscriptionPayment::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-SUB-001',
            'billing_package_id' => $package->id,
            'package_name_snapshot' => $package->name,
            'plan_code' => $package->code,
            'package_duration_days' => $package->duration_days,
            'payment_type' => BillingPackage::TYPE_SUBSCRIPTION,
            'amount' => 149000,
            'status' => SubscriptionPayment::STATUS_PENDING,
        ]);

        $file = UploadedFile::fake()->create('struk.jpg', 500, 'image/jpeg');

        $entitlement = app(EntitlementService::class);
        $entitlement->submitPaymentProof($payment, $file, [
            'sender_bank' => 'BCA',
            'sender_account_name' => 'Owner Test',
            'sender_account_number' => '1234567890',
        ]);

        Mail::assertSent(PaymentUploadedAdminMail::class, function ($mail) {
            return $mail->hasTo('agungmustaqim28@gmail.com');
        });
    }

    public function test_approve_payment_sends_invoice_to_business_owner(): void
    {
        Mail::fake();

        $user = User::factory()->create(['name' => 'Owner Test', 'email' => 'owner@example.com']);
        $business = Business::create(['name' => 'Kedai Kopi Test', 'currency' => 'IDR', 'email' => 'owner@example.com']);
        $business->users()->attach($user->id, ['id' => (string) \Illuminate\Support\Str::uuid(), 'role' => 'owner']);

        $admin = Admin::create([
            'name' => 'Admin Test',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_super_admin' => true,
        ]);

        $package = BillingPackage::create([
            'type' => BillingPackage::TYPE_SUBSCRIPTION,
            'code' => 'core_monthly',
            'name' => 'Paket Cooca UMKM Pro',
            'price' => 149000,
            'duration_days' => 30,
            'is_active' => true,
        ]);

        $payment = SubscriptionPayment::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-SUB-002',
            'billing_package_id' => $package->id,
            'package_name_snapshot' => $package->name,
            'plan_code' => $package->code,
            'package_duration_days' => $package->duration_days,
            'payment_type' => BillingPackage::TYPE_SUBSCRIPTION,
            'amount' => 149000,
            'status' => SubscriptionPayment::STATUS_AWAITING_APPROVAL,
        ]);

        $entitlement = app(EntitlementService::class);
        $entitlement->approvePayment($payment, $admin, 'Disetujui');

        Mail::assertSent(PaymentApprovedInvoiceMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_process_subscriptions_command_expires_past_subscriptions_and_sends_reminders(): void
    {
        Mail::fake();

        $user = User::factory()->create(['name' => 'Owner Test', 'email' => 'owner@example.com']);
        $business = Business::create(['name' => 'Kedai Kopi Test', 'currency' => 'IDR']);
        $business->users()->attach($user->id, ['id' => (string) \Illuminate\Support\Str::uuid(), 'role' => 'owner']);

        // Subscription expiring in 7 days (H-7)
        BusinessSubscription::create([
            'business_id' => $business->id,
            'plan_code' => 'core_monthly',
            'price' => 149000,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => Carbon::now()->subDays(23),
            'ends_at' => Carbon::today()->addDays(7),
        ]);

        // Subscription already expired yesterday
        $business2 = Business::create(['name' => 'Usaha Expired', 'currency' => 'IDR']);
        $subExpired = BusinessSubscription::create([
            'business_id' => $business2->id,
            'plan_code' => 'core_monthly',
            'price' => 149000,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => Carbon::now()->subDays(35),
            'ends_at' => Carbon::now()->subDay(),
        ]);

        $this->artisan('cooca:process-subscriptions')
            ->assertSuccessful();

        // Check expired sub was marked expired
        $subExpired->refresh();
        $this->assertEquals(BusinessSubscription::STATUS_EXPIRED, $subExpired->status);

        // Check H-7 reminder email was sent
        Mail::assertSent(PlanExpiryReminderMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email) && $mail->daysRemaining === 7;
        });
    }
}
