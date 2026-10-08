<?php

declare(strict_types=1);

namespace Tests\Feature\Billing;

use App\Mail\PaymentApprovedInvoiceMail;
use App\Models\BillingPackage;
use App\Models\Business;
use App\Models\SubscriptionPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class PaymentApprovedInvoicePdfMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_approved_invoice_mail_has_valid_envelope_content_and_pdf_attachment(): void
    {
        $user = User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@example.com',
        ]);

        $business = Business::create([
            'name' => 'Kopi Sejahtera Nusantara',
            'currency' => 'IDR',
            'email' => 'admin@kopisejahtera.id',
            'phone' => '081234567890',
            'address' => 'Jl. Sudirman No. 45, Jakarta Pusat',
        ]);
        $business->users()->attach($user->id, [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'role' => 'owner',
        ]);

        $package = BillingPackage::create([
            'type' => BillingPackage::TYPE_SUBSCRIPTION,
            'code' => 'cooca_pro_monthly',
            'name' => 'Paket Cooca Pro',
            'price' => 149000,
            'duration_days' => 30,
            'is_active' => true,
        ]);

        $payment = SubscriptionPayment::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-SUB-202610-001',
            'billing_package_id' => $package->id,
            'package_name_snapshot' => $package->name,
            'plan_code' => $package->code,
            'package_duration_days' => $package->duration_days,
            'payment_type' => BillingPackage::TYPE_SUBSCRIPTION,
            'amount' => 149000,
            'package_price' => 149000,
            'discount_amount' => 0,
            'gateway_fee' => 0,
            'payment_method' => SubscriptionPayment::METHOD_QRIS,
            'status' => SubscriptionPayment::STATUS_APPROVED,
            'approved_at' => now(),
        ]);

        $mailable = new PaymentApprovedInvoiceMail($payment);

        // 1. Envelope check
        $envelope = $mailable->envelope();
        $this->assertStringContainsString('[Kwitansi Resmi & Invoice] Pembayaran Terverifikasi:', $envelope->subject);
        $this->assertStringContainsString('Paket Cooca Pro', $envelope->subject);

        // 2. Attachments check
        $attachments = $mailable->attachments();
        $this->assertCount(1, $attachments);

        $attachment = $attachments[0];
        $invoiceShortId = strtoupper(substr($payment->id, 0, 8));
        $this->assertSame("Invoice-Cooca-INV-{$invoiceShortId}.pdf", $attachment->as);
        $this->assertSame('application/pdf', $attachment->mime);

        // 3. PDF generation validation
        $pdfContent = $mailable->generatePdfContent("INV-{$invoiceShortId}");
        $this->assertNotEmpty($pdfContent);
        // Verify valid PDF file header (%PDF-)
        $this->assertStringStartsWith('%PDF-', $pdfContent);

        // 4. Mailable rendering view check
        $html = $mailable->render();
        $this->assertStringContainsString('Pembayaran Berhasil Diverifikasi', $html);
        $this->assertStringContainsString('Kopi Sejahtera Nusantara', $html);
        $this->assertStringContainsString('File Faktur & Kwitansi PDF Terlampir', $html);
    }

    public function test_tenant_can_download_server_side_pdf_invoice_via_web_endpoint(): void
    {
        $user = User::factory()->create(['name' => 'Owner Test', 'email' => 'owner@example.com']);
        $business = Business::create(['name' => 'Usaha Maju Cooca', 'currency' => 'IDR']);
        $business->users()->attach($user->id, ['id' => (string) \Illuminate\Support\Str::uuid(), 'role' => 'owner']);

        $package = BillingPackage::create([
            'type' => BillingPackage::TYPE_SUBSCRIPTION,
            'code' => 'core_monthly',
            'name' => 'Paket Cooca Pro',
            'price' => 149000,
            'duration_days' => 30,
            'is_active' => true,
        ]);

        $payment = SubscriptionPayment::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-SUB-PDF-001',
            'billing_package_id' => $package->id,
            'package_name_snapshot' => $package->name,
            'plan_code' => $package->code,
            'package_duration_days' => $package->duration_days,
            'payment_type' => BillingPackage::TYPE_SUBSCRIPTION,
            'amount' => 149000,
            'status' => SubscriptionPayment::STATUS_APPROVED,
            'approved_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('billing.payment.invoice', [
            'payment' => $payment,
            'download' => 'pdf',
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $invoiceShortId = strtoupper(substr($payment->id, 0, 8));
        $response->assertHeader('Content-Disposition', 'inline; filename="Invoice-Cooca-INV-' . $invoiceShortId . '.pdf"');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }
}
