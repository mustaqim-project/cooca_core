<?php

declare(strict_types=1);

namespace Tests\Feature\WhatsApp;

use App\Exports\WhatsAppLogsExport;
use App\Models\Business;
use App\Models\User;
use App\Models\WhatsAppMessageLog;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WhatsAppLogsExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RbacSeeder::class);
        app()->setLocale('id');
    }

    private function createMerchant(): array
    {
        $user = User::factory()->create([
            'name'  => 'Pemilik Toko',
            'email' => 'owner_' . Str::random(8) . '@cooca.id',
        ]);

        $business = Business::create([
            'user_id'  => $user->id,
            'name'     => 'Kopi Kenangan Sejahtera',
            'status'   => 'active',
            'currency' => 'IDR',
        ]);

        $business->users()->attach($user->id, [
            'id'   => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $user->update(['active_business_id' => $business->id]);
        Context::setBusiness($business);

        app(\App\Domain\Billing\EntitlementService::class)->upgradeToCore($business, 'monthly');

        return [$user, $business];
    }

    public function test_merchant_can_export_whatsapp_logs_to_excel_stream(): void
    {
        [$user, $business] = $this->createMerchant();

        WhatsAppMessageLog::create([
            'business_id'     => $business->id,
            'type'            => 'receipt',
            'recipient_name'  => 'Budi Santoso',
            'recipient_phone' => '081234567890',
            'message'         => 'Struk belanja kopi #ORD-001',
            'status'          => 'sent',
        ]);

        $response = $this->actingAs($user)->get(route('whatsapp.logs.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('Laporan_Komunikasi_WhatsApp', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.xlsx', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_export_generator_builds_valid_two_sheet_spreadsheet_with_kpi_and_ledger(): void
    {
        [$user, $business] = $this->createMerchant();

        // 1. Struk POS sukses
        WhatsAppMessageLog::create([
            'business_id'     => $business->id,
            'type'            => 'receipt',
            'recipient_name'  => 'Andi Wijaya',
            'recipient_phone' => '081234567891',
            'message'         => 'Struk POS #1',
            'status'          => 'sent',
        ]);

        // 2. Blast promosi sukses
        WhatsAppMessageLog::create([
            'business_id'     => $business->id,
            'type'            => 'broadcast',
            'recipient_name'  => 'Siti Rahma',
            'recipient_phone' => '085712345678',
            'message'         => 'Promo Merdeka 50%',
            'status'          => 'sent',
        ]);

        // 3. Uji coba gagal
        WhatsAppMessageLog::create([
            'business_id'     => $business->id,
            'type'            => 'test',
            'recipient_name'  => 'Tester',
            'recipient_phone' => '089912345678',
            'message'         => 'Test message',
            'status'          => 'failed',
            'error_message'   => 'Recipient not registered on WhatsApp',
        ]);

        /** @var WhatsAppLogsExport $export */
        $export = app(WhatsAppLogsExport::class);
        $spreadsheet = $export->generate($business, [], true);

        // Verifikasi ada 2 Sheet
        $this->assertEquals(2, $spreadsheet->getSheetCount());

        $sheet1 = $spreadsheet->getSheet(0);
        $this->assertEquals('Ringkasan Eksekutif', $sheet1->getTitle());
        $this->assertStringContainsString('KOPI KENANGAN SEJAHTERA', (string) $sheet1->getCell('A2')->getValue());

        // Verifikasi Nilai KPI di Sheet 1
        $this->assertEquals(2, $sheet1->getCell('A7')->getValue()); // 2 sent
        $this->assertEquals(1, $sheet1->getCell('E7')->getValue()); // 1 receipt
        $this->assertEquals(1, $sheet1->getCell('G7')->getValue()); // 1 broadcast

        // Verifikasi Sheet 2: Ledger
        $sheet2 = $spreadsheet->getSheet(1);
        $this->assertEquals('Rincian Log Lengkap', $sheet2->getTitle());
        $this->assertEquals('ID Transaksi', $sheet2->getCell('A4')->getValue());
        $this->assertEquals('Nomor WhatsApp', $sheet2->getCell('D4')->getValue());
        $this->assertEquals('Ringkasan Isi Pesan', $sheet2->getCell('F4')->getValue());

        // Verifikasi baris data pada Sheet 2 (Ada 3 log terdaftar)
        $recipients = [
            $sheet2->getCell('C5')->getValue(),
            $sheet2->getCell('C6')->getValue(),
            $sheet2->getCell('C7')->getValue(),
        ];
        $this->assertContains('Andi Wijaya', $recipients);
        $this->assertContains('Siti Rahma', $recipients);
        $this->assertContains('Tester', $recipients);

        $phones = [
            $sheet2->getCell('D5')->getValue(),
            $sheet2->getCell('D6')->getValue(),
            $sheet2->getCell('D7')->getValue(),
        ];
        $this->assertContains('081234567891', $phones);
        $this->assertContains('085712345678', $phones);
        $this->assertContains('089912345678', $phones);
    }

    public function test_export_enforces_multi_tenant_isolation(): void
    {
        [$userA, $businessA] = $this->createMerchant();

        // Buat Business B
        $userB = User::factory()->create(['name' => 'Owner B', 'email' => 'b@cooca.id']);
        $businessB = Business::create([
            'user_id'  => $userB->id,
            'name'     => 'Bisnis Tetangga',
            'status'   => 'active',
            'currency' => 'IDR',
        ]);
        $businessB->users()->attach($userB->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);

        // Log milik Bisnis A
        WhatsAppMessageLog::create([
            'business_id'     => $businessA->id,
            'type'            => 'receipt',
            'recipient_name'  => 'Pelanggan Bisnis A',
            'recipient_phone' => '081111111111',
            'message'         => 'Struk A',
            'status'          => 'sent',
        ]);

        // Log milik Bisnis B
        WhatsAppMessageLog::create([
            'business_id'     => $businessB->id,
            'type'            => 'broadcast',
            'recipient_name'  => 'Pelanggan Bisnis B',
            'recipient_phone' => '082222222222',
            'message'         => 'Promo Rahasia B',
            'status'          => 'sent',
        ]);

        /** @var WhatsAppLogsExport $export */
        $export = app(WhatsAppLogsExport::class);
        $spreadsheetA = $export->generate($businessA, [], true);
        $sheet2A = $spreadsheetA->getSheet(1);

        // Hanya boleh ada 1 data row (baris 5)
        $this->assertEquals('Pelanggan Bisnis A', $sheet2A->getCell('C5')->getValue());
        $this->assertNull($sheet2A->getCell('C6')->getValue());
    }

    public function test_export_masks_pii_for_non_owner(): void
    {
        [$owner, $business] = $this->createMerchant();

        // Buat staf admin (non-owner)
        $staff = User::factory()->create(['name' => 'Staf Kasir', 'email' => 'staff@cooca.id']);
        $business->users()->attach($staff->id, ['id' => (string) Str::uuid(), 'role' => 'cashier']);
        $staff->update(['active_business_id' => $business->id]);

        WhatsAppMessageLog::create([
            'business_id'     => $business->id,
            'type'            => 'receipt',
            'recipient_name'  => 'Doni Salman',
            'recipient_phone' => '081234567890',
            'message'         => 'Struk #999',
            'status'          => 'sent',
        ]);

        /** @var WhatsAppLogsExport $export */
        $export = app(WhatsAppLogsExport::class);

        // 1. Non-Owner Export -> PII Masked
        $spreadsheetNonOwner = $export->generate($business, [], false);
        $sheetNonOwner = $spreadsheetNonOwner->getSheet(1);
        $this->assertEquals('0812••••7890', $sheetNonOwner->getCell('D5')->getValue());

        // 2. Owner Export -> Full Raw Phone
        $spreadsheetOwner = $export->generate($business, [], true);
        $sheetOwner = $spreadsheetOwner->getSheet(1);
        $this->assertEquals('081234567890', $sheetOwner->getCell('D5')->getValue());
    }

    public function test_export_applies_type_filters(): void
    {
        [$user, $business] = $this->createMerchant();

        WhatsAppMessageLog::create([
            'business_id'     => $business->id,
            'type'            => 'receipt',
            'recipient_name'  => 'Receipt Customer',
            'recipient_phone' => '081111111111',
            'message'         => 'Struk POS',
            'status'          => 'sent',
        ]);

        WhatsAppMessageLog::create([
            'business_id'     => $business->id,
            'type'            => 'broadcast',
            'recipient_name'  => 'Broadcast Customer',
            'recipient_phone' => '082222222222',
            'message'         => 'Broadcast Promo',
            'status'          => 'sent',
        ]);

        /** @var WhatsAppLogsExport $export */
        $export = app(WhatsAppLogsExport::class);

        // Filter type: 'broadcast'
        $spreadsheet = $export->generate($business, ['type' => 'broadcast'], true);
        $sheet2 = $spreadsheet->getSheet(1);

        $this->assertEquals('Broadcast Customer', $sheet2->getCell('C5')->getValue());
        $this->assertNull($sheet2->getCell('C6')->getValue());
    }
}
