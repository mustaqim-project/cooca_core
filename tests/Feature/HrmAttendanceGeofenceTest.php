<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\HRM\AttendanceService;
use App\Domain\HRM\PayrollRunService;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\Location;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class HrmAttendanceGeofenceTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private User $geofencedEmployee;
    private User $freeLocationEmployee;
    private BusinessMembership $geofencedMembership;
    private BusinessMembership $freeMembership;
    private Location $officeLocation;
    private Role $staffRole;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        // 1. Create Business & Owner
        $this->owner = User::create([
            'name' => 'Pak Bos Owner',
            'email' => 'bos@tokokopi.id',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'phone' => '081234567890',
        ]);

        $this->business = Business::create([
            'name' => 'Kopi Sejahtera Utama',
            'slug' => 'kopi-sejahtera-utama',
            'email' => 'kontak@kopisejahtera.id',
            'phone' => '081234567890',
            'address' => 'Jl. Sudirman Kav 21 Jakarta',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);

        $ownerMembership = BusinessMembership::where('business_id', $this->business->id)
            ->where('user_id', $this->owner->id)
            ->first();

        Context::setBusiness($this->business, $ownerMembership);

        // 2. Create Office Location with Geofence Radius
        // Latitude / Longitude: Monas Jakarta (-6.175392, 106.827153)
        $this->officeLocation = Location::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Kantor Pusat Monas',
            'address' => 'Medan Merdeka Barat, Jakarta',
            'latitude' => -6.175392,
            'longitude' => 106.827153,
            'geofence_radius_meters' => 50,
            'is_primary' => true,
            'is_active' => true,
        ]);

        // 3. Staff Role
        $this->staffRole = Role::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Barista & Kasir',
            'slug' => 'barista_kasir',
            'description' => 'Staf outlet',
        ]);

        // 4. Geofenced Employee
        $this->geofencedEmployee = User::create([
            'name' => 'Andi Barista',
            'email' => 'andi@tokokopi.id',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $this->business->users()->attach($this->geofencedEmployee->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'role_id' => $this->staffRole->id,
            'attendance_mode' => BusinessMembership::ATTENDANCE_MODE_GEOFENCED,
            'primary_location_id' => $this->officeLocation->id,
            'job_title' => 'Senior Barista',
            'employment_type' => 'daily_worker',
            'daily_rate' => 150000,
            'base_salary' => 0,
            'is_active' => true,
        ]);

        $this->geofencedEmployee->update(['active_business_id' => $this->business->id]);

        $this->geofencedMembership = BusinessMembership::where('business_id', $this->business->id)
            ->where('user_id', $this->geofencedEmployee->id)
            ->first();

        // 5. Free Location Employee (Sales / Field / Remote)
        $this->freeLocationEmployee = User::create([
            'name' => 'Budi Sales Lapangan',
            'email' => 'budi.sales@tokokopi.id',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $this->business->users()->attach($this->freeLocationEmployee->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'role_id' => $this->staffRole->id,
            'attendance_mode' => BusinessMembership::ATTENDANCE_MODE_FREE,
            'primary_location_id' => null,
            'job_title' => 'Field Canvasser',
            'employment_type' => 'permanent',
            'base_salary' => 6000000,
            'daily_rate' => 0,
            'is_active' => true,
        ]);

        $this->freeLocationEmployee->update(['active_business_id' => $this->business->id]);

        $this->freeMembership = BusinessMembership::where('business_id', $this->business->id)
            ->where('user_id', $this->freeLocationEmployee->id)
            ->first();
    }

    public function test_geofenced_employee_clock_in_success_within_radius(): void
    {
        // ~10 meters from Monas (-6.175392, 106.827153)
        $closeLat = -6.175450;
        $closeLng = 106.827200;

        $response = $this->actingAs($this->geofencedEmployee)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('hrm.attendance.clock-in'), [
                'latitude' => $closeLat,
                'longitude' => $closeLng,
                'accuracy' => 15,
                'address' => 'Area Kantor Pusat Monas',
                'notes' => 'Hadir pagi shift 1',
            ]);

        $response->assertRedirect(route('hrm.index', ['tab' => 'attendance']));
        $response->assertSessionHas('success');

        $attendance = Attendance::where('business_id', $this->business->id)
            ->where('user_id', $this->geofencedEmployee->id)
            ->whereDate('date', now())
            ->first();

        $this->assertNotNull($attendance);
        $this->assertEquals($this->officeLocation->id, $attendance->location_id);
        $this->assertTrue((bool) $attendance->is_geofenced);
        $this->assertNotNull($attendance->clock_in_at);
        $this->assertLessThanOrEqual(50, (int) $attendance->clock_in_distance_meters);
        $this->assertContains($attendance->status, [Attendance::STATUS_PRESENT, Attendance::STATUS_LATE]);
    }

    public function test_geofenced_employee_clock_in_rejected_outside_radius(): void
    {
        // Coordinates ~800 meters away at Pasar Baru (-6.1680, 106.8320)
        $farLat = -6.168000;
        $farLng = 106.832000;

        $response = $this->actingAs($this->geofencedEmployee)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('hrm.attendance.clock-in'), [
                'latitude' => $farLat,
                'longitude' => $farLng,
                'accuracy' => 10,
                'address' => 'Pasar Baru Jakarta',
            ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseMissing('attendances', [
            'business_id' => $this->business->id,
            'user_id' => $this->geofencedEmployee->id,
            'date' => now()->toDateString(),
        ]);
    }

    public function test_free_location_employee_can_clock_in_from_remote_location(): void
    {
        // Coordinates in Bogor (~45 km from Monas)
        $bogorLat = -6.597147;
        $bogorLng = 106.806038;

        $response = $this->actingAs($this->freeLocationEmployee)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('hrm.attendance.clock-in'), [
                'latitude' => $bogorLat,
                'longitude' => $bogorLng,
                'accuracy' => 20,
                'address' => 'Kunjungan Klien Distributor Bogor',
                'notes' => 'Prospek outlet baru area Bogor Kota',
            ]);

        $response->assertRedirect(route('hrm.index', ['tab' => 'attendance']));
        $response->assertSessionHas('success');

        $att = Attendance::where('user_id', $this->freeLocationEmployee->id)->first();
        $this->assertNotNull($att);
        $this->assertEquals(Attendance::CLOCK_IN_FREE_LOCATION, $att->clock_in_status);
        $this->assertFalse((bool) $att->is_geofenced);
        $this->assertEquals('Kunjungan Klien Distributor Bogor', $att->clock_in_address);
    }

    public function test_anti_spoofing_rejects_low_gps_accuracy(): void
    {
        // Close coordinates but accuracy is poor (> 100 meters, e.g. 150m)
        $response = $this->actingAs($this->geofencedEmployee)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('hrm.attendance.clock-in'), [
                'latitude' => -6.175400,
                'longitude' => 106.827160,
                'accuracy' => 150, // Rejection threshold > 100m
            ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseMissing('attendances', [
            'business_id' => $this->business->id,
            'user_id' => $this->geofencedEmployee->id,
            'date' => now()->toDateString(),
        ]);
    }

    public function test_clock_out_calculates_work_duration_and_overtime(): void
    {
        // Pre-create attendance clocked in 9 hours ago (540 minutes)
        $clockInTime = now()->subHours(9);
        $attendance = Attendance::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'user_id' => $this->geofencedEmployee->id,
            'location_id' => $this->officeLocation->id,
            'date' => now()->toDateString(),
            'clock_in_at' => $clockInTime,
            'clock_in_lat' => -6.175392,
            'clock_in_lng' => 106.827153,
            'clock_in_accuracy' => 10,
            'status' => Attendance::STATUS_PRESENT,
            'clock_in_status' => Attendance::CLOCK_IN_ON_TIME,
            'is_geofenced' => true,
        ]);

        $response = $this->actingAs($this->geofencedEmployee)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('hrm.attendance.clock-out'), [
                'latitude' => -6.175395,
                'longitude' => 106.827155,
                'accuracy' => 12,
                'notes' => 'Selesai shift operasional',
            ]);

        $response->assertRedirect(route('hrm.index', ['tab' => 'attendance']));

        $attendance->refresh();
        $this->assertNotNull($attendance->clock_out_at);
        $this->assertGreaterThanOrEqual(530, $attendance->work_duration_minutes);
        // Overtime kicks in for duration > 480 minutes (8 hours)
        $this->assertGreaterThanOrEqual(50, $attendance->overtime_minutes);
    }

    public function test_employee_can_submit_attendance_correction_ticket(): void
    {
        $response = $this->actingAs($this->geofencedEmployee)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('hrm.attendance.corrections.store'), [
                'target_date' => now()->toDateString(),
                'correction_type' => AttendanceCorrection::TYPE_FULL_DAY,
                'proposed_clock_in' => '08:30',
                'proposed_clock_out' => '17:30',
                'proposed_status' => Attendance::STATUS_PRESENT,
                'reason' => 'Perangkat ponsel kehabisan daya saat jam tiba di toko.',
            ]);

        $response->assertRedirect(route('hrm.index', ['tab' => 'corrections']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_corrections', [
            'business_id' => $this->business->id,
            'user_id' => $this->geofencedEmployee->id,
            'correction_type' => AttendanceCorrection::TYPE_FULL_DAY,
            'status' => AttendanceCorrection::STATUS_PENDING,
        ]);

        $ticket = AttendanceCorrection::where('user_id', $this->geofencedEmployee->id)->first();
        $this->assertStringStartsWith('COR-', $ticket->correction_number);
    }

    public function test_manager_can_approve_correction_ticket_with_audit_log_and_recalculation(): void
    {
        // 1. Create a pending correction ticket
        $ticket = AttendanceCorrection::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'user_id' => $this->geofencedEmployee->id,
            'correction_number' => 'COR-202609-00001',
            'target_date' => now()->toDateString(),
            'correction_type' => AttendanceCorrection::TYPE_FULL_DAY,
            'proposed_clock_in' => '08:00',
            'proposed_clock_out' => '17:00',
            'proposed_status' => Attendance::STATUS_PRESENT,
            'reason' => 'Lupa membawa smartphone saat dinas pagi.',
            'status' => AttendanceCorrection::STATUS_PENDING,
        ]);

        // 2. Manager approves the ticket
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('hrm.attendance.corrections.approve', $ticket->id), [
                'review_notes' => 'Disetujui berdasarkan konfirmasi supervisor toko.',
            ]);

        $response->assertRedirect(route('hrm.index', ['tab' => 'corrections']));
        $response->assertSessionHas('success');

        $ticket->refresh();
        $this->assertEquals(AttendanceCorrection::STATUS_APPROVED, $ticket->status);
        $this->assertEquals($this->owner->id, $ticket->reviewed_by);

        // Target attendance record must now exist and be marked as corrected
        $attendance = Attendance::where('business_id', $this->business->id)
            ->where('user_id', $this->geofencedEmployee->id)
            ->whereDate('date', now())
            ->first();

        $this->assertNotNull($attendance);
        $this->assertTrue($attendance->is_corrected);
        $this->assertEquals(Attendance::STATUS_PRESENT, $attendance->status);
        $this->assertEquals(540, $attendance->work_duration_minutes); // 08:00 to 17:00 = 9 hours (540 mins)

        // Audit Log Entry
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'auditable_type' => AttendanceCorrection::class,
            'auditable_id' => $ticket->id,
            'action' => 'attendance.correction_approved',
            'risk_level' => AuditLog::RISK_MEDIUM,
        ]);
    }

    public function test_manager_can_reject_correction_ticket_with_reason(): void
    {
        $ticket = AttendanceCorrection::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'user_id' => $this->geofencedEmployee->id,
            'correction_number' => 'COR-202609-00002',
            'target_date' => now()->toDateString(),
            'correction_type' => AttendanceCorrection::TYPE_CLOCK_IN_ONLY,
            'proposed_clock_in' => '08:00',
            'reason' => 'Klaim hadir tepat waktu tanpa bukti.',
            'status' => AttendanceCorrection::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('hrm.attendance.corrections.reject', $ticket->id), [
                'reason' => 'Rekaman CCTV menunjukkan Anda tiba pukul 10:15 WIB.',
            ]);

        $response->assertRedirect(route('hrm.index', ['tab' => 'corrections']));

        $ticket->refresh();
        $this->assertEquals(AttendanceCorrection::STATUS_REJECTED, $ticket->status);
        $this->assertEquals('Rekaman CCTV menunjukkan Anda tiba pukul 10:15 WIB.', $ticket->review_notes);

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'auditable_type' => AttendanceCorrection::class,
            'auditable_id' => $ticket->id,
            'action' => 'attendance.correction_rejected',
        ]);
    }

    public function test_payroll_run_service_automatically_uses_clean_attendance_count(): void
    {
        $service = app(PayrollRunService::class);
        $startDate = Carbon::parse('2026-09-01');
        $endDate = Carbon::parse('2026-09-30');

        // Create 4 present attendances and 1 late attendance (total 5 valid attendances)
        for ($i = 1; $i <= 4; $i++) {
            Attendance::create([
                'id' => (string) Str::uuid(),
                'business_id' => $this->business->id,
                'user_id' => $this->geofencedEmployee->id,
                'date' => Carbon::parse("2026-09-0{$i}"),
                'clock_in_at' => Carbon::parse("2026-09-0{$i} 08:30:00"),
                'clock_out_at' => Carbon::parse("2026-09-0{$i} 17:00:00"),
                'work_duration_minutes' => 510,
                'status' => Attendance::STATUS_PRESENT,
            ]);
        }

        // 1 Late attendance (still counts as worked)
        Attendance::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'user_id' => $this->geofencedEmployee->id,
            'date' => Carbon::parse('2026-09-05'),
            'clock_in_at' => Carbon::parse('2026-09-05 09:45:00'),
            'clock_out_at' => Carbon::parse('2026-09-05 17:00:00'),
            'work_duration_minutes' => 435,
            'status' => Attendance::STATUS_LATE,
        ]);

        // 1 Absent attendance (should NOT count as worked)
        Attendance::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'user_id' => $this->geofencedEmployee->id,
            'date' => Carbon::parse('2026-09-06'),
            'status' => Attendance::STATUS_ABSENT,
        ]);

        // Generate payroll run for September 2026 (month 9, year 2026)
        $payroll = $service->generatePayrollRun($this->business, 9, 2026);

        $dailyWorkerItem = $payroll->items->firstWhere('user_id', $this->geofencedEmployee->id);
        $this->assertNotNull($dailyWorkerItem);

        // Daily worker has daily_rate = 150000. 5 days worked = 750000.
        $this->assertEquals(5, $dailyWorkerItem->days_worked);
        $this->assertEquals(750000, (float) $dailyWorkerItem->gross_pay);
    }

    public function test_tenant_isolation_prevents_cross_business_ticket_approval(): void
    {
        // Create another tenant
        $otherBusiness = Business::create([
            'name' => 'Kedai Kopi Lain',
            'slug' => 'kedai-kopi-lain',
            'is_active' => true,
        ]);

        $otherOwner = User::create([
            'name' => 'Owner Lain',
            'email' => 'owner.lain@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $otherBusiness->users()->attach($otherOwner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $ticket = AttendanceCorrection::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'user_id' => $this->geofencedEmployee->id,
            'correction_number' => 'COR-202609-00999',
            'target_date' => now()->toDateString(),
            'correction_type' => AttendanceCorrection::TYPE_CLOCK_IN_ONLY,
            'proposed_clock_in' => '08:00',
            'reason' => 'Uji isolasi tenant.',
            'status' => AttendanceCorrection::STATUS_PENDING,
        ]);

        // Other owner attempts to approve this ticket
        $response = $this->actingAs($otherOwner)
            ->withSession(['active_business_id' => $otherBusiness->id])
            ->post(route('hrm.attendance.corrections.approve', $ticket->id), [
                'review_notes' => 'Illegal cross-tenant approval',
            ]);

        // Should return 404 or 403
        $this->assertContains($response->status(), [403, 404]);

        $ticket->refresh();
        $this->assertEquals(AttendanceCorrection::STATUS_PENDING, $ticket->status);
    }
}
