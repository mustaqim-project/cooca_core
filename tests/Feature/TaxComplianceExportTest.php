<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\Invoice;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TaxComplianceExportTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->owner = User::create([
            'name' => 'Pemilik Usaha Pajak',
            'email' => 'pajak.owner@cooca.id',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone' => '6281234567892',
        ]);

        $this->business = Business::create([
            'name' => 'PT Makmur Sejahtera',
            'slug' => 'pt-makmur-sejahtera',
            'tax_id' => '0123456789012345',
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code' => BusinessSubscription::PLAN_PREMIUM_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
        ]);
    }

    public function test_export_ebupot_csv_stream(): void
    {
        $payroll = Payroll::create([
            'business_id' => $this->business->id,
            'period_year' => 2026,
            'period_month' => 9,
            'title' => 'Gaji September 2026',
            'status' => Payroll::STATUS_APPROVED,
            'total_gross_pay' => 7000000,
            'total_pph21' => 35000,
        ]);

        PayrollItem::create([
            'business_id' => $this->business->id,
            'payroll_id' => $payroll->id,
            'user_id' => $this->owner->id,
            'employee_name' => 'Budi Santoso',
            'employment_type' => 'permanent',
            'gross_pay' => 7000000,
            'pph21_amount' => 35000,
            'pph21_ter_rate' => 0.005,
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('tax.export.ebupot', ['year' => 2026, 'month' => 9]));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('ebupot_pph21', (string) $response->headers->get('content-disposition'));

        // Capture streamed content
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('Masa Pajak', $content);
        $this->assertStringContainsString('NPWP/NIK Pemotong', $content);
        $this->assertStringContainsString('Budi Santoso', $content);
        $this->assertStringContainsString('21-100-01', $content);
        $this->assertStringContainsString('35000', $content);
    }

    public function test_export_pph_final_csv_stream(): void
    {
        $customer = \App\Models\Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Pelanggan Tetap',
            'phone' => '081299998888',
        ]);

        Invoice::create([
            'business_id' => $this->business->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-2026-001',
            'invoice_date' => '2026-09-10',
            'due_date' => '2026-09-15',
            'status' => Invoice::STATUS_PAID,
            'subtotal' => 50000000,
            'total_amount' => 50000000,
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('tax.export.pph_final', ['year' => 2026, 'taxpayer_type' => 'corporate']));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('rekap_pph_final_umkm', (string) $response->headers->get('content-disposition'));

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('Masa Pajak', $content);
        $this->assertStringContainsString('Nama Usaha', $content);
        $this->assertStringContainsString('411128', $content); // KAP PPh Final
        $this->assertStringContainsString('420', $content);    // KJS UMKM 0.5%
        $this->assertStringContainsString('50000000', $content);
    }
}
