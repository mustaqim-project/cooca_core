<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\HRM\Exports\PayrollTwoPartExcelExport;
use App\Models\Business;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HrmTwoPartExcelExportTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Payroll $payroll;
    private PayrollItem $item1;
    private PayrollItem $item2;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name' => 'HR Director Cooca',
            'email' => 'hr_director@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'phone' => '6281299990001',
        ]);

        $this->business = Business::create([
            'name' => 'Cooca Nusantara Culinary',
            'slug' => 'cooca-nusantara-culinary',
            'email' => 'info@coocaculinary.test',
            'phone' => '6281299990002',
            'address' => 'Jl. Sudirman No. 45, Jakarta Pusat',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->user->id, [
            'role' => 'owner',
            'is_active' => true,
        ]);

        Context::setBusiness($this->business);

        // Create Payroll Batch
        $this->payroll = Payroll::create([
            'business_id' => $this->business->id,
            'title' => 'Penggajian Karyawan - September 2026',
            'payroll_number' => 'PAY-202609-001',
            'period_month' => 9,
            'period_year' => 2026,
            'start_date' => Carbon::create(2026, 9, 1),
            'end_date' => Carbon::create(2026, 9, 30),
            'payment_date' => Carbon::create(2026, 9, 30),
            'status' => Payroll::STATUS_APPROVED,
            'total_employees_count' => 2,
            'total_gross_pay' => 17_500_000,
            'total_take_home_pay' => 15_850_000,
            'total_company_cost' => 19_200_000,
            'total_pph21' => 350_000,
            'total_bpjs_company' => 1_700_000,
            'total_bpjs_employee' => 600_000,
            'total_loan_deductions' => 700_000,
        ]);

        // Employee Users
        $employeeUser1 = User::create([
            'name' => 'Ahmad Fauzi',
            'email' => 'ahmad.fauzi@coocaculinary.test',
            'password' => bcrypt('password123'),
            'phone' => '6281234567801',
        ]);
        $this->business->users()->attach($employeeUser1->id, ['role' => 'staff', 'is_active' => true]);

        $employeeUser2 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@coocaculinary.test',
            'password' => bcrypt('password123'),
            'phone' => '6281234567802',
        ]);
        $this->business->users()->attach($employeeUser2->id, ['role' => 'staff', 'is_active' => true]);

        // Item 1: Monthly Staff
        $this->item1 = PayrollItem::create([
            'payroll_id' => $this->payroll->id,
            'business_id' => $this->business->id,
            'user_id' => $employeeUser1->id,
            'employee_name' => 'Ahmad Fauzi',
            'job_title' => 'Head Chef',
            'employment_type' => 'permanent',
            'tenure_months' => 24,
            'base_salary' => 10_000_000,
            'fixed_allowances' => 1_500_000,
            'variable_allowances' => 500_000,
            'overtime_pay' => 500_000,
            'commissions' => 0,
            'thr_amount' => 0,
            'gross_pay' => 12_500_000,
            'bpjs_tk_company' => 750_000,
            'bpjs_tk_employee' => 300_000,
            'bpjs_kes_company' => 400_000,
            'bpjs_kes_employee' => 100_000,
            'pph21_amount' => 250_000,
            'pph21_ter_category' => 'A',
            'pph21_ter_rate' => 2.0,
            'loan_deduction' => 500_000,
            'other_deductions' => 0,
            'total_deductions' => 1_150_000,
            'take_home_pay' => 11_350_000,
            'company_total_cost' => 13_650_000,
            'bank_name' => 'BCA',
            'bank_account_number' => '8881234567',
            'bank_account_holder' => 'Ahmad Fauzi',
            'whatsapp_number' => '081234567801',
            'status' => 'approved',
        ]);

        // Item 2: Daily Worker
        $this->item2 = PayrollItem::create([
            'payroll_id' => $this->payroll->id,
            'business_id' => $this->business->id,
            'user_id' => $employeeUser2->id,
            'employee_name' => 'Budi Santoso',
            'job_title' => 'Kitchen Helper',
            'employment_type' => 'daily_worker',
            'daily_rate' => 200_000,
            'days_worked' => 25,
            'base_salary' => 5_000_000,
            'fixed_allowances' => 0,
            'variable_allowances' => 0,
            'overtime_pay' => 0,
            'commissions' => 0,
            'thr_amount' => 0,
            'gross_pay' => 5_000_000,
            'bpjs_tk_company' => 350_000,
            'bpjs_tk_employee' => 150_000,
            'bpjs_kes_company' => 200_000,
            'bpjs_kes_employee' => 50_000,
            'pph21_amount' => 100_000,
            'pph21_ter_category' => 'A',
            'pph21_ter_rate' => 2.0,
            'loan_deduction' => 200_000,
            'other_deductions' => 0,
            'total_deductions' => 500_000,
            'take_home_pay' => 4_500_000,
            'company_total_cost' => 5_550_000,
            'bank_name' => 'Mandiri',
            'bank_account_number' => '1370009876543',
            'bank_account_holder' => 'Budi Santoso',
            'whatsapp_number' => '081234567802',
            'status' => 'approved',
        ]);
    }

    public function test_exporter_creates_valid_two_sheet_spreadsheet_structure(): void
    {
        $exporter = new PayrollTwoPartExcelExport($this->payroll, $this->business);
        $spreadsheet = $exporter->buildSpreadsheet();

        // 1. Verify Sheet Count
        $this->assertSame(2, $spreadsheet->getSheetCount(), 'Spreadsheet must contain exactly 2 sheets.');

        // 2. Verify Sheet Names
        $sheetNames = $spreadsheet->getSheetNames();
        $this->assertSame('Ringkasan Eksekutif', $sheetNames[0]);
        $this->assertSame('Buku Besar Penggajian', $sheetNames[1]);

        // 3. Verify Sheet 1 (Executive Summary) content
        $sheet1 = $spreadsheet->getSheet(0);
        $this->assertStringContainsString('COOCA NUSANTARA CULINARY', (string)$sheet1->getCell('A1')->getValue());
        $this->assertSame('RINGKASAN EKSEKUTIF PENGGAJIAN BULANAN', $sheet1->getCell('A2')->getValue());
        $this->assertSame('TOTAL KARYAWAN', $sheet1->getCell('A6')->getValue());
        $this->assertSame('TOTAL TAKE-HOME PAY', $sheet1->getCell('C6')->getValue());
        $this->assertSame('TOTAL BEBAN PERUSAHAAN', $sheet1->getCell('E6')->getValue());
        $this->assertSame('TOTAL POTONGAN PPH 21', $sheet1->getCell('G6')->getValue());

        // 4. Verify Sheet 2 (General Ledger) content & formulas
        $sheet2 = $spreadsheet->getSheet(1);
        $this->assertSame('No', $sheet2->getCell('A5')->getValue());
        $this->assertSame('Nama Karyawan', $sheet2->getCell('B5')->getValue());
        $this->assertSame('Gaji Pokok (Rp)', $sheet2->getCell('I5')->getValue());
        $this->assertSame('Total Bruto (Rp)', $sheet2->getCell('O5')->getValue());
        $this->assertSame('Total Potongan (Rp)', $sheet2->getCell('U5')->getValue());
        $this->assertSame('Gaji Bersih / THP (Rp)', $sheet2->getCell('V5')->getValue());
        $this->assertSame('Beban Perusahaan (Rp)', $sheet2->getCell('Y5')->getValue());

        // Employee Rows
        $this->assertSame(1, $sheet2->getCell('A6')->getValue());
        $this->assertSame('Ahmad Fauzi', $sheet2->getCell('B6')->getValue());
        $this->assertSame(2, $sheet2->getCell('A7')->getValue());
        $this->assertSame('Budi Santoso', $sheet2->getCell('B7')->getValue());

        // Total Row Formulas
        $this->assertSame('TOTAL KESELURUHAN', $sheet2->getCell('A8')->getValue());
        $this->assertSame('=SUM(I6:I7)', $sheet2->getCell('I8')->getValue());
        $this->assertSame('=SUM(O6:O7)', $sheet2->getCell('O8')->getValue());
        $this->assertSame('=SUM(U6:U7)', $sheet2->getCell('U8')->getValue());
        $this->assertSame('=SUM(V6:V7)', $sheet2->getCell('V8')->getValue());
        $this->assertSame('=SUM(Y6:Y7)', $sheet2->getCell('Y8')->getValue());

        // Freeze Pane & AutoFilter
        $this->assertSame('C6', $sheet2->getFreezePane());
        $this->assertNotEmpty($sheet2->getAutoFilter()->getRange());
    }

    public function test_owner_can_download_excel_export_stream_with_proper_headers(): void
    {
        Context::setBusiness($this->business);

        $response = $this->actingAs($this->user)
            ->get(route('hrm.payrolls.export-excel', $this->payroll->id));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('attachment; filename="Laporan_Penggajian_cooca-nusantara-culinary_2026_09.xlsx"', $response->headers->get('content-disposition'));
    }

    public function test_multi_tenant_isolation_prevents_unauthorized_payroll_excel_export(): void
    {
        // Create second tenant
        $user2 = User::create([
            'name' => 'Intruder Tenant',
            'email' => 'intruder@otherbusiness.test',
            'password' => bcrypt('password123'),
        ]);
        $business2 = Business::create([
            'name' => 'Different Business',
            'slug' => 'different-business',
            'email' => 'contact@different.test',
            'is_active' => true,
        ]);
        $business2->users()->attach($user2->id, ['role' => 'owner', 'is_active' => true]);

        Context::setBusiness($business2);

        // Attempting to export payroll of business 1 as user 2
        $response = $this->actingAs($user2)
            ->get(route('hrm.payrolls.export-excel', $this->payroll->id));

        $this->assertTrue(in_array($response->getStatusCode(), [403, 404], true), 'Cross-tenant export must be rejected with 403 or 404.');
    }

    public function test_payroll_show_page_renders_excel_button_and_interactive_drilldown_elements(): void
    {
        Context::setBusiness($this->business);

        $response = $this->actingAs($this->user)
            ->withSession(['locale' => 'id'])
            ->get(route('hrm.payrolls.show', $this->payroll->id));

        $response->assertStatus(200);
        
        // Assert Excel XLSX button exists
        $response->assertSee('Ekspor Excel (XLSX)');
        $response->assertSee(route('hrm.payrolls.export-excel', $this->payroll->id));

        // Assert Interactive Drill-Down Drawer elements exist
        $response->assertSee('showDrillDownModal');
        $response->assertSee('selectedItem');
        $response->assertSee('Beban Riil Perusahaan (Kontribusi Pemberi Kerja)');
        $response->assertSee('Komponen Pendapatan (Bruto)');
        $response->assertSee('Potongan Gaji Staf');
    }
}
