<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Crm\CustomerPaymentTermReminderService;
use App\Mail\CustomerPaymentTermReminderMail;
use App\Models\Business;
use App\Models\Customer;
use App\Models\CustomerTermReminder;
use App\Models\Invoice;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerPaymentTermReminderTest extends TestCase
{
    use RefreshDatabase;

    private Business $businessA;
    private Business $businessB;
    private User $ownerA;
    private User $ownerB;
    private Customer $customerA;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        config(['app.locale' => 'id']);
        app()->setLocale('id');
        $this->withSession(['locale' => 'id']);

        // Create Business A & Owner A
        $this->ownerA = User::create([
            'name' => 'Owner Distribusi A',
            'email' => 'owner-a@distribusi.test',
            'phone' => '081234567891',
            'password' => 'password',
        ]);
        $this->businessA = Business::create(['name' => 'PT Distribusi Niaga A']);
        $this->businessA->users()->attach($this->ownerA->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->ownerA->update(['active_business_id' => $this->businessA->id]);

        // Create Business B & Owner B
        $this->ownerB = User::create([
            'name' => 'Owner Bengkel B',
            'email' => 'owner-b@bengkel.test',
            'phone' => '081234567892',
            'password' => 'password',
        ]);
        $this->businessB = Business::create(['name' => 'Bengkel Sejahtera B']);
        $this->businessB->users()->attach($this->ownerB->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->ownerB->update(['active_business_id' => $this->businessB->id]);

        // Customer in Business A
        $this->customerA = Customer::create([
            'business_id' => $this->businessA->id,
            'name' => 'Budi Santoso',
            'company_name' => 'CV Sumber Lancar',
            'phone' => '081299998888',
            'email' => 'budi@sumberlancar.test',
            'payment_terms_days' => 30,
        ]);
    }

    public function test_scheduled_command_evaluates_upcoming_due_and_overdue_invoices(): void
    {
        Mail::fake();

        $today = Carbon::parse('2026-10-15');

        // Invoice 1: Upcoming in 3 days (due 2026-10-18)
        $invUpcoming = Invoice::create([
            'business_id' => $this->businessA->id,
            'customer_id' => $this->customerA->id,
            'invoice_number' => 'INV-UPCOMING-01',
            'invoice_date' => '2026-09-18',
            'due_date' => '2026-10-18',
            'status' => Invoice::STATUS_SENT,
            'total_amount' => 1500000,
            'paid_amount' => 0,
            'balance_due' => 1500000,
            'payment_terms' => 'Net 30',
        ]);

        // Invoice 2: Due Today (due 2026-10-15)
        $invToday = Invoice::create([
            'business_id' => $this->businessA->id,
            'customer_id' => $this->customerA->id,
            'invoice_number' => 'INV-TODAY-02',
            'invoice_date' => '2026-09-15',
            'due_date' => '2026-10-15',
            'status' => Invoice::STATUS_UNPAID,
            'total_amount' => 2500000,
            'paid_amount' => 500000,
            'balance_due' => 2000000,
            'payment_terms' => 'Net 30',
        ]);

        // Invoice 3: Overdue by 3 days (due 2026-10-12)
        $invOverdue = Invoice::create([
            'business_id' => $this->businessA->id,
            'customer_id' => $this->customerA->id,
            'invoice_number' => 'INV-OVERDUE-03',
            'invoice_date' => '2026-09-12',
            'due_date' => '2026-10-12',
            'status' => Invoice::STATUS_OVERDUE,
            'total_amount' => 3000000,
            'paid_amount' => 0,
            'balance_due' => 3000000,
            'payment_terms' => 'Net 30',
        ]);

        // Invoice 4: Fully Paid (should not trigger reminder)
        $invPaid = Invoice::create([
            'business_id' => $this->businessA->id,
            'customer_id' => $this->customerA->id,
            'invoice_number' => 'INV-PAID-04',
            'invoice_date' => '2026-09-10',
            'due_date' => '2026-10-10',
            'status' => Invoice::STATUS_PAID,
            'total_amount' => 1000000,
            'paid_amount' => 1000000,
            'balance_due' => 0,
            'payment_terms' => 'Net 30',
        ]);

        // Run console command
        $this->artisan('customers:send-term-reminders', [
            '--business' => $this->businessA->id,
            '--date' => '2026-10-15',
        ])->assertSuccessful();

        // Verify reminder log table
        $this->assertDatabaseHas('customer_term_reminders', [
            'business_id' => $this->businessA->id,
            'invoice_id' => $invUpcoming->id,
            'reminder_type' => CustomerTermReminder::TYPE_UPCOMING_H3,
            'status' => CustomerTermReminder::STATUS_SENT,
        ]);

        $this->assertDatabaseHas('customer_term_reminders', [
            'business_id' => $this->businessA->id,
            'invoice_id' => $invToday->id,
            'reminder_type' => CustomerTermReminder::TYPE_DUE_DATE,
            'status' => CustomerTermReminder::STATUS_SENT,
        ]);

        $this->assertDatabaseHas('customer_term_reminders', [
            'business_id' => $this->businessA->id,
            'invoice_id' => $invOverdue->id,
            'reminder_type' => CustomerTermReminder::TYPE_OVERDUE,
            'status' => CustomerTermReminder::STATUS_SENT,
        ]);

        $this->assertDatabaseMissing('customer_term_reminders', [
            'invoice_id' => $invPaid->id,
        ]);

        // Verify 3 emails were dispatched
        Mail::assertSent(CustomerPaymentTermReminderMail::class, 3);
    }

    public function test_anti_spam_guard_prevents_duplicate_reminders_on_same_day(): void
    {
        Mail::fake();

        $invToday = Invoice::create([
            'business_id' => $this->businessA->id,
            'customer_id' => $this->customerA->id,
            'invoice_number' => 'INV-DEDUP-01',
            'invoice_date' => '2026-09-15',
            'due_date' => '2026-10-15',
            'status' => Invoice::STATUS_UNPAID,
            'total_amount' => 2000000,
            'paid_amount' => 0,
            'balance_due' => 2000000,
        ]);

        // First run: should dispatch
        $service = app(CustomerPaymentTermReminderService::class);
        $result1 = $service->sendScheduledDueReminders($this->businessA, Carbon::parse('2026-10-15'));
        $this->assertEquals(1, $result1['sent']);
        $this->assertEquals(0, $result1['skipped']);

        // Second run on the same date: anti-spam guard should skip
        $result2 = $service->sendScheduledDueReminders($this->businessA, Carbon::parse('2026-10-15'));
        $this->assertEquals(0, $result2['sent']);
        $this->assertEquals(1, $result2['skipped']);

        // Ensure database only contains 1 record
        $this->assertEquals(1, CustomerTermReminder::where('invoice_id', $invToday->id)->count());
    }

    public function test_manual_web_route_dispatches_reminder_and_redirects_with_feedback(): void
    {
        Mail::fake();
        Context::setBusiness($this->businessA);

        $invoice = Invoice::create([
            'business_id' => $this->businessA->id,
            'customer_id' => $this->customerA->id,
            'invoice_number' => 'INV-MANUAL-01',
            'invoice_date' => '2026-09-20',
            'due_date' => '2026-10-20',
            'status' => Invoice::STATUS_SENT,
            'total_amount' => 4500000,
            'paid_amount' => 0,
            'balance_due' => 4500000,
        ]);

        $response = $this->actingAs($this->ownerA)->post(route('invoices.remind', $invoice), [
            'channel' => 'both',
            'custom_notes' => 'Tolong dicek kembali ya pak Budi.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $response->assertSessionHas('wa_url');

        $this->assertDatabaseHas('customer_term_reminders', [
            'business_id' => $this->businessA->id,
            'invoice_id' => $invoice->id,
            'reminder_type' => CustomerTermReminder::TYPE_MANUAL,
            'channel' => 'both',
            'status' => CustomerTermReminder::STATUS_SENT,
        ]);

        Mail::assertSent(CustomerPaymentTermReminderMail::class, function ($mail) use ($invoice) {
            return $mail->invoice->id === $invoice->id
                && $mail->customNotes === 'Tolong dicek kembali ya pak Budi.';
        });
    }

    public function test_multi_tenant_isolation_prevents_unauthorized_reminder_dispatch(): void
    {
        Context::setBusiness($this->businessB);

        $invoiceA = Invoice::create([
            'business_id' => $this->businessA->id,
            'customer_id' => $this->customerA->id,
            'invoice_number' => 'INV-TENANT-A',
            'invoice_date' => '2026-09-20',
            'due_date' => '2026-10-20',
            'status' => Invoice::STATUS_SENT,
            'total_amount' => 5000000,
            'paid_amount' => 0,
            'balance_due' => 5000000,
        ]);

        // Owner B tries to trigger reminder for Invoice A
        $response = $this->actingAs($this->ownerB)->post(route('invoices.remind', $invoiceA), [
            'channel' => 'both',
        ]);

        $response->assertNotFound();

        $this->assertDatabaseMissing('customer_term_reminders', [
            'invoice_id' => $invoiceA->id,
        ]);
    }
}
