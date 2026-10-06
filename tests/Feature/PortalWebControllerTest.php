<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\Location;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PortalWebControllerTest extends TestCase
{
    use RefreshDatabase;

    private Business $businessA;
    private Business $businessB;
    private User $employeeA;
    private User $employeeB;
    private Role $staffRole;
    private Location $locationA;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('id');
        Context::flush();

        // Business A
        $this->businessA = Business::create([
            'name' => 'Cooca Coffee Jakarta',
            'slug' => 'cooca-coffee-jakarta',
            'email' => 'jakarta@coocacoffee.com',
            'phone' => '081234567891',
            'city' => 'Jakarta Selatan',
            'address' => 'Jl. Senopati No. 10 Jakarta',
            'is_active' => true,
        ]);

        $this->locationA = Location::create([
            'business_id' => $this->businessA->id,
            'name' => 'Outlet Senopati',
            'code' => 'OUT-SNT-01',
            'type' => 'outlet',
            'latitude' => -6.2297,
            'longitude' => 106.8074,
            'geofence_radius_meters' => 50,
            'is_primary' => true,
            'is_active' => true,
        ]);

        $this->employeeA = User::create([
            'name' => 'Budi Barista',
            'email' => 'budi@coocacoffee.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'phone' => '081234567892',
            'active_business_id' => $this->businessA->id,
        ]);

        $this->staffRole = Role::create([
            'business_id' => $this->businessA->id,
            'name' => 'Barista & Kasir',
            'slug' => 'barista-kasir',
            'is_system' => false,
        ]);

        // Assign pos.terminal permission to staffRole
        $terminalPermission = Permission::firstOrCreate(
            ['slug' => 'pos.terminal'],
            ['name' => 'POS Terminal Access', 'module' => 'pos']
        );
        RolePermission::create([
            'role_id' => $this->staffRole->id,
            'permission_id' => $terminalPermission->id,
        ]);

        $this->businessA->users()->attach($this->employeeA->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'role_id' => $this->staffRole->id,
            'primary_location_id' => $this->locationA->id,
            'job_title' => 'Senior Barista',
            'employment_type' => 'permanent',
            'base_salary' => 5000000,
            'fixed_allowances' => 500000,
            'variable_allowances' => 300000,
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'Budi Barista',
            'whatsapp_number' => '081234567892',
            'tax_ptkp_status' => 'TK/0',
            'bpjs_tk_enabled' => true,
            'bpjs_kes_enabled' => true,
            'is_active' => true,
        ]);

        // Business B (for multi-tenant isolation testing)
        $this->businessB = Business::create([
            'name' => 'Resto Bandung Juara',
            'slug' => 'resto-bandung-juara',
            'email' => 'bandung@restojuara.com',
            'phone' => '081234567893',
            'is_active' => true,
        ]);

        $this->employeeB = User::create([
            'name' => 'Asep Bandung',
            'email' => 'asep@restojuara.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'phone' => '081234567894',
            'active_business_id' => $this->businessB->id,
        ]);

        $this->businessB->users()->attach($this->employeeB->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'job_title' => 'Cook Helper',
            'employment_type' => 'contract',
            'base_salary' => 3500000,
            'is_active' => true,
        ]);
    }

    public function test_employee_can_access_portal_with_complete_personal_profile(): void
    {
        // 1. Create a today attendance record
        Attendance::create([
            'business_id' => $this->businessA->id,
            'user_id' => $this->employeeA->id,
            'date' => Carbon::now('Asia/Jakarta')->toDateString(),
            'clock_in_at' => Carbon::now('Asia/Jakarta')->setTime(8, 0, 0),
            'clock_in_status' => Attendance::CLOCK_IN_ON_TIME,
            'status' => Attendance::STATUS_PRESENT,
        ]);

        $response = $this->actingAs($this->employeeA)->get(route('portal'));

        $response->assertOk();
        $response->assertSee('Halo, Budi Barista');
        $response->assertSee('Cooca Coffee Jakarta');
        $response->assertSee('Senior Barista');
        $response->assertSee('BCA');
        $response->assertSee('1234567890');
        $response->assertSee('Saldo Cuti Tahunan');
        $response->assertSee('Presensi', false);
        $response->assertSee('Data Kepegawaian', false);
        $response->assertSee('Slip Gaji Digital', false);
    }

    public function test_employee_can_view_own_payslip_in_portal(): void
    {
        $payroll = Payroll::create([
            'title' => 'Gaji September 2026',
            'business_id' => $this->businessA->id,
            'user_id' => $this->employeeA->id,
            'period_month' => 9,
            'period_year' => 2026,
            'status' => Payroll::STATUS_APPROVED,
            'total_gross' => 5800000,
            'total_deductions' => 300000,
            'total_net' => 5500000,
            'total_company_cost' => 6100000,
            'employee_count' => 1,
        ]);

        $payslipA = PayrollItem::create([
            'payroll_id' => $payroll->id,
            'title' => 'Gaji September 2026',
            'business_id' => $this->businessA->id,
            'user_id' => $this->employeeA->id,
            'employee_name' => 'Budi Barista',
            'job_title' => 'Senior Barista',
            'employment_type' => 'permanent',
            'base_salary' => 5000000,
            'fixed_allowances' => 500000,
            'variable_allowances' => 300000,
            'gross_pay' => 5800000,
            'total_deductions' => 300000,
            'take_home_pay' => 5500000,
            'status' => PayrollItem::STATUS_APPROVED,
        ]);

        // Accessible via portal payslips show route
        $response = $this->actingAs($this->employeeA)
            ->get(route('portal.payslips.show', $payslipA->id));

        $response->assertOk();
        $response->assertSee('SLIP GAJI');
        $response->assertSee('Budi Barista');
        $response->assertSee('5.500.000');
    }

    public function test_employee_cannot_view_other_employee_payslip_cross_tenant_or_user(): void
    {
        $payrollB = Payroll::create([
            'title' => 'Gaji September 2026',
            'business_id' => $this->businessB->id,
            'user_id' => $this->employeeB->id,
            'period_month' => 9,
            'period_year' => 2026,
            'status' => Payroll::STATUS_APPROVED,
            'total_gross' => 3500000,
            'total_deductions' => 100000,
            'total_net' => 3400000,
            'total_company_cost' => 3600000,
            'employee_count' => 1,
        ]);

        $payslipB = PayrollItem::create([
            'payroll_id' => $payrollB->id,
            'business_id' => $this->businessB->id,
            'user_id' => $this->employeeB->id,
            'employee_name' => 'Asep Bandung',
            'job_title' => 'Cook Helper',
            'employment_type' => 'contract',
            'base_salary' => 3500000,
            'gross_pay' => 3500000,
            'total_deductions' => 100000,
            'take_home_pay' => 3400000,
            'status' => PayrollItem::STATUS_APPROVED,
        ]);

        // Employee A from Business A attempting to view Payslip B from Business B
        $response = $this->actingAs($this->employeeA)
            ->get(route('portal.payslips.show', $payslipB->id));

        $response->assertForbidden();
    }

    public function test_employee_can_submit_attendance_correction_ticket_from_portal(): void
    {
        $targetDate = Carbon::today()->subDay()->toDateString();

        $response = $this->actingAs($this->employeeA)
            ->post(route('portal.corrections.store'), [
                'target_date' => $targetDate,
                'correction_type' => 'both',
                'proposed_clock_in' => '08:00',
                'proposed_clock_out' => '17:00',
                'proposed_status' => 'present',
                'reason' => 'Sinyal GPS kantor tidak terdeteksi saat tiba di outlet pagi kemarin',
            ]);

        $response->assertRedirect(route('portal', ['tab' => 'history']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_corrections', [
            'business_id' => $this->businessA->id,
            'user_id' => $this->employeeA->id,
            'target_date' => $targetDate . ' 00:00:00',
            'proposed_status' => 'present',
            'status' => AttendanceCorrection::STATUS_PENDING,
        ]);
    }

    public function test_employee_can_access_pos_terminal_at_pos_route(): void
    {
        $response = $this->actingAs($this->employeeA)
            ->get(route('pos.terminal'));

        $response->assertOk();
        $response->assertSee(__('pos.terminal_title'));
    }

    public function test_employee_portal_loads_cleanly_with_active_attendance_exception(): void
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        \App\Models\AttendanceException::create([
            'business_id' => $this->businessA->id,
            'user_id' => $this->employeeA->id,
            'exception_mode' => \App\Models\AttendanceException::MODE_WFH,
            'start_date' => $today,
            'end_date' => Carbon::now('Asia/Jakarta')->addDays(2)->toDateString(),
            'location_name' => 'Home Office Jakarta',
            'reason' => 'Dispensasi Work From Home (WFH)',
            'status' => \App\Models\AttendanceException::STATUS_APPROVED,
        ]);

        $response = $this->actingAs($this->employeeA)->get(route('portal'));

        $response->assertOk();
        $response->assertSee('Home Office Jakarta');
        $response->assertSee('WFH');
    }

    public function test_portal_auto_hides_modules_disabled_by_industry_template(): void
    {
        // Give employee permissions for both POS Retail and POS Dine-In (Kitchen & Tables)
        $kitchenPermission = Permission::firstOrCreate(
            ['slug' => 'pos.kitchen'],
            ['name' => 'Kitchen Access', 'module' => 'pos']
        );
        $tablesPermission = Permission::firstOrCreate(
            ['slug' => 'pos.tables'],
            ['name' => 'Tables Access', 'module' => 'pos']
        );
        RolePermission::firstOrCreate(['role_id' => $this->staffRole->id, 'permission_id' => $kitchenPermission->id]);
        RolePermission::firstOrCreate(['role_id' => $this->staffRole->id, 'permission_id' => $tablesPermission->id]);

        // Disable POS Dine-In module for business (e.g. Retail / Bengkel template)
        $this->businessA->update([
            'disabled_modules' => [\App\Domain\Template\ModuleRegistry::MODULE_POS_DINEIN],
        ]);

        $response = $this->actingAs($this->employeeA)->get(route('portal'));

        $response->assertOk();
        // Mesin Kasir is enabled and permitted
        $response->assertSee('Mesin Kasir (POS)');
        // Dapur KDS and Denah Meja are automatically hidden despite permissions
        $response->assertDontSee('Dapur KDS');
        $response->assertDontSee('Denah Meja');
    }

    public function test_portal_displays_modules_when_enabled_for_business(): void
    {
        // Give employee permissions for POS Kitchen
        $kitchenPermission = Permission::firstOrCreate(
            ['slug' => 'pos.kitchen'],
            ['name' => 'Kitchen Access', 'module' => 'pos']
        );
        RolePermission::firstOrCreate(['role_id' => $this->staffRole->id, 'permission_id' => $kitchenPermission->id]);

        // Enable all modules for business (F&B Restaurant template)
        $this->businessA->update([
            'disabled_modules' => [],
        ]);

        $response = $this->actingAs($this->employeeA)->get(route('portal'));

        $response->assertOk();
        $response->assertSee('Mesin Kasir (POS)');
        $response->assertSee('Dapur KDS');
    }
}
