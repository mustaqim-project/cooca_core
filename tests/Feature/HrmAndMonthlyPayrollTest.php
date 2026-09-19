<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\EmployeeLoan;
use App\Models\Expense;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class HrmAndMonthlyPayrollTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private Role $staffRole;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->owner = User::create([
            'name' => 'Pak Owner Cooca',
            'email' => 'owner@coocabakery.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'phone' => '081234567890',
        ]);

        $this->business = Business::create([
            'name' => 'Cooca Bakery Perkasa',
            'slug' => 'cooca-bakery-perkasa',
            'email' => 'contact@coocabakery.com',
            'phone' => '081234567890',
            'address' => 'Jl. Sudirman No. 45 Jakarta',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);

        $membership = BusinessMembership::where('business_id', $this->business->id)
            ->where('user_id', $this->owner->id)
            ->first();

        Context::setBusiness($this->business, $membership);

        $this->staffRole = Role::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Staf Operasional',
            'slug' => 'staff_ops',
            'description' => 'Staf toko & produksi',
        ]);
    }

    public function test_hrm_hub_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('hrm.index'));

        $response->assertStatus(200);
        $response->assertSee('Manajemen SDM &amp; Penggajian', false);
        $response->assertSee('Total Staf Aktif', false);
    }

    public function test_can_store_and_update_employee_hrm_profile(): void
    {
        // 1. Store Employee
        $storeResponse = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('hrm.employees.store'), [
                'name' => 'Budi Santoso',
                'email' => 'budi@coocabakery.com',
                'role_id' => $this->staffRole->id,
                'job_title' => 'Baker Utama',
                'employment_type' => 'permanent',
                'join_date' => '2025-01-15',
                'base_salary' => 8000000,
                'daily_rate' => 0,
                'fixed_allowances' => 1000000,
                'variable_allowances' => 500000,
                'tax_ptkp_status' => 'K/1',
                'bpjs_tk_enabled' => '1',
                'bpjs_kes_enabled' => '1',
                'bank_name' => 'BCA',
                'bank_account_number' => '8881234567',
                'bank_account_holder' => 'Budi Santoso',
                'whatsapp_number' => '081298765432',
            ]);

        $storeResponse->assertRedirect(route('hrm.index', ['tab' => 'employees']));
        $storeResponse->assertSessionHas('success');

        $budiUser = User::where('email', 'budi@coocabakery.com')->first();
        $this->assertNotNull($budiUser);
        $this->assertEquals('Budi Santoso', $budiUser->name);

        $membership = BusinessMembership::where('business_id', $this->business->id)
            ->where('user_id', $budiUser->id)
            ->first();

        $this->assertNotNull($membership);
        $this->assertEquals(8000000.0, (float) $membership->base_salary);
        $this->assertEquals(1000000.0, (float) $membership->fixed_allowances);
        $this->assertEquals('K/1', $membership->tax_ptkp_status);
        $this->assertTrue((bool) $membership->bpjs_tk_enabled);
        $this->assertTrue((bool) $membership->bpjs_kes_enabled);
        $this->assertEquals('BCA', $membership->bank_name);

        // 2. Update Employee
        $updateResponse = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->put(route('hrm.employees.update', $membership->id), [
                'name' => 'Budi Santoso (Promoted)',
                'role_id' => $this->staffRole->id,
                'job_title' => 'Head Baker & Supervisor',
                'employment_type' => 'permanent',
                'join_date' => '2025-01-15',
                'base_salary' => 9500000,
                'daily_rate' => 0,
                'fixed_allowances' => 1200000,
                'variable_allowances' => 600000,
                'tax_ptkp_status' => 'K/2',
                'bpjs_tk_enabled' => '1',
                'bpjs_kes_enabled' => '1',
                'bank_name' => 'Mandiri',
                'bank_account_number' => '1230009876543',
                'bank_account_holder' => 'Budi Santoso',
                'whatsapp_number' => '081298765432',
            ]);

        $updateResponse->assertRedirect(route('hrm.index', ['tab' => 'employees']));
        $membership->refresh();
        $this->assertEquals(9500000.0, (float) $membership->base_salary);
        $this->assertEquals('K/2', $membership->tax_ptkp_status);
        $this->assertEquals('Mandiri', $membership->bank_name);
    }

    public function test_can_create_and_cancel_employee_loan(): void
    {
        $employeeUser = User::create([
            'name' => 'Rian Kasir',
            'email' => 'rian@coocabakery.com',
            'password' => bcrypt('password123'),
        ]);

        $this->business->users()->attach($employeeUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'cashier',
            'is_active' => true,
        ]);

        // Create Loan
        $loanResponse = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('hrm.loans.store'), [
                'user_id' => $employeeUser->id,
                'amount' => 3000000,
                'tenor_months' => 3,
                'loan_date' => '2026-09-01',
                'purpose' => 'Biaya Darurat Medis',
            ]);

        $loanResponse->assertRedirect(route('hrm.index', ['tab' => 'loans']));

        $loan = EmployeeLoan::where('business_id', $this->business->id)
            ->where('user_id', $employeeUser->id)
            ->first();

        $this->assertNotNull($loan);
        $this->assertEquals(3000000.0, (float) $loan->amount);
        $this->assertEquals(1000000.0, (float) $loan->monthly_installment);
        $this->assertEquals(3000000.0, (float) $loan->remaining_balance);
        $this->assertEquals(EmployeeLoan::STATUS_ACTIVE, $loan->status);

        // Cancel Loan
        $cancelResponse = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('hrm.loans.cancel', $loan->id));

        $cancelResponse->assertRedirect(route('hrm.index', ['tab' => 'loans']));
        $loan->refresh();
        $this->assertEquals(EmployeeLoan::STATUS_CANCELLED, $loan->status);
    }

    public function test_can_generate_monthly_payroll_batch_with_precise_calculations(): void
    {
        // Setup Employee 1: Permanent
        $emp1 = User::create([
            'name' => 'Siti Staff',
            'email' => 'siti@coocabakery.com',
            'password' => bcrypt('password123'),
        ]);
        $this->business->users()->attach($emp1->id, [
            'id' => (string) Str::uuid(),
            'role' => $this->staffRole->slug,
            'role_id' => $this->staffRole->id,
            'employment_type' => 'permanent',
            'base_salary' => 10000000,
            'fixed_allowances' => 1000000,
            'tax_ptkp_status' => 'TK/0',
            'bpjs_tk_enabled' => true,
            'bpjs_kes_enabled' => true,
            'is_active' => true,
        ]);

        // Setup Employee 2: Daily Worker
        $emp2 = User::create([
            'name' => 'Joko Harian',
            'email' => 'joko@coocabakery.com',
            'password' => bcrypt('password123'),
        ]);
        $this->business->users()->attach($emp2->id, [
            'id' => (string) Str::uuid(),
            'role' => $this->staffRole->slug,
            'role_id' => $this->staffRole->id,
            'employment_type' => 'daily_worker',
            'base_salary' => 0,
            'daily_rate' => 200000,
            'tax_ptkp_status' => 'TK/0',
            'bpjs_tk_enabled' => false,
            'bpjs_kes_enabled' => false,
            'is_active' => true,
        ]);

        // Generate Payroll Batch
        $generateResponse = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('hrm.payrolls.store'), [
                'period_month' => 9,
                'period_year' => 2026,
                'include_thr' => '0',
                'notes' => 'Batch Gaji September 2026',
                'employees' => [
                    $emp1->id => [
                        'base_salary' => 10000000,
                        'fixed_allowances' => 1000000,
                        'variable_allowances' => 200000,
                        'overtime_pay' => 300000,
                        'commissions' => 500000,
                        'days_worked' => 22,
                        'overtime_hours' => 5,
                        'loan_deduction' => 0,
                        'other_deductions' => 0,
                        'notes' => '',
                    ],
                    $emp2->id => [
                        'days_worked' => 20,
                        'overtime_pay' => 0,
                        'commissions' => 0,
                        'overtime_hours' => 0,
                        'loan_deduction' => 0,
                        'other_deductions' => 0,
                        'notes' => '',
                    ],
                ],
            ]);

        $payroll = Payroll::where('business_id', $this->business->id)
            ->where('period_month', 9)
            ->where('period_year', 2026)
            ->first();

        $this->assertNotNull($payroll);
        $generateResponse->assertRedirect(route('hrm.payrolls.show', $payroll->id));
        $this->assertEquals(Payroll::STATUS_DRAFT, $payroll->status);
        // Includes Owner, emp1, emp2 = 3
        $this->assertEquals(3, $payroll->items()->count());

        // Verify Emp 1 Payroll Item
        $item1 = $payroll->items()->where('user_id', $emp1->id)->first();
        $this->assertNotNull($item1);
        $this->assertEquals(10000000.0, (float) $item1->base_salary);
        // Gross = 10jt + 1jt + 200k + 300k + 500k = 12jt
        $this->assertEquals(12000000.0, (float) $item1->gross_salary);
        $this->assertNotEmpty($item1->payslip_token);
        $this->assertEquals(64, strlen($item1->payslip_token));
        // Deductions exist (BPJS + PPh 21)
        $this->assertGreaterThan(0, (float) $item1->tax_pph21);
        $this->assertGreaterThan(0, (float) $item1->bpjs_tk_employee);
        $this->assertGreaterThan(0, (float) $item1->bpjs_kes_employee);
        $this->assertEquals(
            (float) $item1->gross_salary - (float) $item1->total_deductions,
            (float) $item1->net_salary
        );

        // Verify Emp 2 Payroll Item (Daily: 20 * 200.000 = 4.000.000)
        $item2 = $payroll->items()->where('user_id', $emp2->id)->first();
        $this->assertNotNull($item2);
        $this->assertEquals(4000000.0, (float) $item2->gross_salary);
        $this->assertEquals(4000000.0, (float) $item2->net_salary);
    }

    public function test_approval_and_payment_workflow_updates_loans_and_financial_expense(): void
    {
        $employee = User::create([
            'name' => 'Agus Driver',
            'email' => 'agus@coocabakery.com',
            'password' => bcrypt('password123'),
        ]);
        $this->business->users()->attach($employee->id, [
            'id' => (string) Str::uuid(),
            'role' => $this->staffRole->slug,
            'role_id' => $this->staffRole->id,
            'employment_type' => 'permanent',
            'base_salary' => 6000000,
            'fixed_allowances' => 500000,
            'tax_ptkp_status' => 'TK/0',
            'bpjs_tk_enabled' => false,
            'bpjs_kes_enabled' => false,
            'is_active' => true,
        ]);

        // Create Active Loan
        $loan = EmployeeLoan::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'user_id' => $employee->id,
            'loan_number' => 'LN-TEST-001',
            'loan_date' => '2026-08-01',
            'amount' => 2000000,
            'tenor_months' => 4,
            'monthly_installment' => 500000,
            'remaining_balance' => 2000000,
            'status' => EmployeeLoan::STATUS_ACTIVE,
        ]);

        // Create Payroll Batch with 500.000 Loan deduction
        $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('hrm.payrolls.store'), [
                'period_month' => 9,
                'period_year' => 2026,
                'employees' => [
                    $employee->id => [
                        'base_salary' => 6000000,
                        'fixed_allowances' => 500000,
                        'variable_allowances' => 0,
                        'overtime_pay' => 0,
                        'commissions' => 0,
                        'days_worked' => 25,
                        'overtime_hours' => 0,
                        'loan_deduction' => 500000,
                        'other_deductions' => 0,
                        'notes' => 'Potongan kasbon ke-1',
                    ],
                ],
            ]);

        $payroll = Payroll::where('business_id', $this->business->id)
            ->where('period_month', 9)
            ->firstOrFail();

        $this->assertEquals(Payroll::STATUS_DRAFT, $payroll->status);

        // 1. Approve
        $approveResponse = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('hrm.payrolls.approve', $payroll->id));

        $approveResponse->assertRedirect();
        $payroll->refresh();
        $this->assertEquals(Payroll::STATUS_APPROVED, $payroll->status);

        // 2. Pay
        $payResponse = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('hrm.payrolls.pay', $payroll->id), [
                'payment_method' => 'bank_transfer',
                'notes' => 'Payroll via BCA Bisnis',
            ]);

        $payResponse->assertRedirect();
        $payroll->refresh();
        $this->assertEquals(Payroll::STATUS_PAID, $payroll->status);

        // Check loan reduction
        $loan->refresh();
        $this->assertEquals(1500000.0, (float) $loan->remaining_balance);
        $this->assertEquals(EmployeeLoan::STATUS_ACTIVE, $loan->status);

        // Check automated Expense record created
        $expense = Expense::where('business_id', $this->business->id)
            ->where('category', 'Gaji & Karyawan')
            ->first();

        $this->assertNotNull($expense);
        $this->assertEquals('Gaji & Karyawan', $expense->category);
        $this->assertGreaterThan(0, (float) $expense->amount);
    }

    public function test_digital_payslip_accessible_authenticated_and_public_token(): void
    {
        $employee = User::create([
            'name' => 'Dewi Admin',
            'email' => 'dewi@coocabakery.com',
            'password' => bcrypt('password123'),
            'phone' => '089988776655',
        ]);
        $this->business->users()->attach($employee->id, [
            'id' => (string) Str::uuid(),
            'role' => $this->staffRole->slug,
            'role_id' => $this->staffRole->id,
            'employment_type' => 'permanent',
            'base_salary' => 7000000,
            'tax_ptkp_status' => 'TK/0',
            'is_active' => true,
        ]);

        $payroll = Payroll::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'period_month' => 9,
            'period_year' => 2026,
            'title' => 'Penggajian September 2026',
            'total_gross_pay' => 7000000,
            'total_take_home_pay' => 6800000,
            'total_company_cost' => 7500000,
            'status' => Payroll::STATUS_PAID,
            'paid_at' => now(),
        ]);

        $item = PayrollItem::create([
            'id' => (string) Str::uuid(),
            'payroll_id' => $payroll->id,
            'business_id' => $this->business->id,
            'user_id' => $employee->id,
            'employee_name' => $employee->name,
            'employment_type' => 'permanent',
            'base_salary' => 7000000,
            'gross_pay' => 7000000,
            'total_deductions' => 200000,
            'take_home_pay' => 6800000,
            'company_total_cost' => 7500000,
            'days_worked' => 25,
        ]);

        // Auth view
        $authResponse = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('hrm.payslips.show', $item->id));

        $authResponse->assertStatus(200);
        $authResponse->assertSee('Dewi Admin');
        $authResponse->assertSee('SLIP GAJI KARYAWAN');
        $authResponse->assertSee(number_format(6800000, 0, ',', '.'));

        // Public view (No Login Required)
        $publicResponse = $this->get(route('public.payslip', $item->payslip_token));

        $publicResponse->assertStatus(200);
        $publicResponse->assertSee('Dewi Admin');
        $publicResponse->assertSee('SLIP GAJI KARYAWAN');
    }

    public function test_tenant_isolation_prevents_unauthorized_cross_business_access(): void
    {
        // Business 2 and User 2
        $otherUser = User::create([
            'name' => 'Pak Competitor',
            'email' => 'other@competitor.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'phone' => '089876543210',
        ]);
        $otherBusiness = Business::create([
            'name' => 'Resto Lain',
            'slug' => 'resto-lain',
            'is_active' => true,
        ]);
        $otherBusiness->users()->attach($otherUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $otherUser->update(['active_business_id' => $otherBusiness->id]);

        // Business 1 Payroll
        $payroll = Payroll::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'period_month' => 9,
            'period_year' => 2026,
            'title' => 'Penggajian Rahasia',
            'status' => Payroll::STATUS_DRAFT,
        ]);

        // Acting as User 2 trying to view Business 1 payroll
        $response = $this->actingAs($otherUser)
            ->withSession(['active_business_id' => $otherBusiness->id])
            ->get(route('hrm.payrolls.show', $payroll->id));

        $response->assertStatus(404);
    }
}
