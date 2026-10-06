<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\HRM\AttendanceService;
use App\Domain\HRM\WorkScheduleService;
use App\Models\Attendance;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\EmployeeSchedule;
use App\Models\Location;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkShift;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HrmWorkShiftAndScheduleAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private User $employee;
    private BusinessMembership $membership;
    private Location $jakartaLocation;
    private Location $makassarLocation;
    private Role $staffRole;
    private AttendanceService $attendanceService;
    private WorkScheduleService $scheduleService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->attendanceService = app(AttendanceService::class);
        $this->scheduleService = app(WorkScheduleService::class);

        // 1. Create Business & Owner
        $this->owner = User::create([
            'name' => 'Pak Owner',
            'email' => 'owner@cooca-test.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'phone' => '081234567890',
        ]);

        $this->business = Business::create([
            'name' => 'PT Cooca Retail Nusantara',
            'slug' => 'cooca-retail-nusantara',
            'email' => 'halo@coocaretail.com',
            'phone' => '081234567890',
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

        // 2. Locations
        // Jakarta (WIB / Asia/Jakarta)
        $this->jakartaLocation = Location::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Outlet Jakarta Pusat',
            'address' => 'Jl. Thamrin No. 1, Jakarta',
            'latitude' => -6.190000,
            'longitude' => 106.820000,
            'geofence_radius_meters' => 100,
            'timezone' => 'Asia/Jakarta',
            'is_primary' => true,
            'is_active' => true,
        ]);

        // Makassar (WITA / Asia/Makassar)
        $this->makassarLocation = Location::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Outlet Makassar Losari',
            'address' => 'Jl. Penghibur No. 10, Makassar',
            'latitude' => -5.140000,
            'longitude' => 119.410000,
            'geofence_radius_meters' => 100,
            'timezone' => 'Asia/Makassar',
            'timezone_mode' => 'custom',
            'is_primary' => false,
            'is_active' => true,
        ]);

        // 3. Staff Role & Employee
        $this->staffRole = Role::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Staf Operasional',
            'slug' => 'staf_operasional',
        ]);

        $this->employee = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@cooca-test.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $this->business->users()->attach($this->employee->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'role_id' => $this->staffRole->id,
            'attendance_mode' => BusinessMembership::ATTENDANCE_MODE_GEOFENCED,
            'primary_location_id' => $this->jakartaLocation->id,
            'job_title' => 'Barista Leader',
            'employment_type' => 'permanent',
            'is_active' => true,
        ]);

        $this->employee->update(['active_business_id' => $this->business->id]);

        $this->membership = BusinessMembership::where('business_id', $this->business->id)
            ->where('user_id', $this->employee->id)
            ->first();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(); // Reset time mockery
        parent::tearDown();
    }

    public function test_clock_in_on_time_within_grace_period(): void
    {
        // Shift Pagi: 08:00 - 17:00, Grace Period: 10 menit
        $shift = WorkShift::create([
            'business_id' => $this->business->id,
            'location_id' => $this->jakartaLocation->id,
            'name' => 'Shift Pagi',
            'code' => 'SP-01',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'grace_period_minutes' => 10,
            'break_duration_minutes' => 60,
            'is_overnight' => false,
            'is_active' => true,
        ]);

        $this->membership->update(['default_shift_id' => $shift->id]);

        // Scenario 1: Clock in at 07:55 (5 minutes early)
        Carbon::setTestNow(Carbon::parse('2026-10-05 07:55:00', 'Asia/Jakarta'));

        $attendance1 = $this->attendanceService->clockIn($this->business, $this->employee, [
            'latitude' => -6.190000,
            'longitude' => 106.820000,
            'accuracy' => 10,
        ]);

        $this->assertEquals(Attendance::STATUS_PRESENT, $attendance1->status);
        $this->assertEquals(Attendance::CLOCK_IN_ON_TIME, $attendance1->clock_in_status);
        $this->assertEquals(0, $attendance1->late_minutes);
        $this->assertEquals(5, $attendance1->early_in_minutes);
        $this->assertEquals($shift->id, $attendance1->work_shift_id);
        $this->assertEquals('Shift Pagi', $attendance1->shift_name);

        // Feedback verification
        $feedback1 = $this->attendanceService->buildClockInFeedback($attendance1, 'Asia/Jakarta');
        $this->assertEquals('early', $feedback1['status']);
        $this->assertEquals(0, $feedback1['late_minutes']);
        $this->assertEquals(5, $feedback1['early_in_minutes']);
        $this->assertStringContainsStringIgnoringCase('LEBIH AWAL', $feedback1['status_label']);
    }

    public function test_clock_in_within_grace_period_is_on_time(): void
    {
        // Shift Pagi: 08:00 - 17:00, Grace Period: 10 menit
        $shift = WorkShift::create([
            'business_id' => $this->business->id,
            'location_id' => $this->jakartaLocation->id,
            'name' => 'Shift Pagi',
            'code' => 'SP-01',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'grace_period_minutes' => 10,
            'is_overnight' => false,
            'is_active' => true,
        ]);

        $this->membership->update(['default_shift_id' => $shift->id]);

        // Clock in at 08:08 (8 minutes after start, but within 10 minute grace period)
        Carbon::setTestNow(Carbon::parse('2026-10-05 08:08:00', 'Asia/Jakarta'));

        $attendance = $this->attendanceService->clockIn($this->business, $this->employee, [
            'latitude' => -6.190000,
            'longitude' => 106.820000,
            'accuracy' => 10,
        ]);

        $this->assertEquals(Attendance::STATUS_PRESENT, $attendance->status);
        $this->assertEquals(Attendance::CLOCK_IN_ON_TIME, $attendance->clock_in_status);
        $this->assertEquals(0, $attendance->late_minutes);
        $this->assertEquals(0, $attendance->early_in_minutes);

        $feedback = $this->attendanceService->buildClockInFeedback($attendance, 'Asia/Jakarta');
        $this->assertEquals(0, $feedback['late_minutes']);
        $this->assertEquals('on_time', $feedback['status']);
    }

    public function test_clock_in_late_exceeding_grace_period(): void
    {
        // Shift Pagi: 08:00 - 17:00, Grace Period: 10 menit
        $shift = WorkShift::create([
            'business_id' => $this->business->id,
            'location_id' => $this->jakartaLocation->id,
            'name' => 'Shift Pagi',
            'code' => 'SP-01',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'grace_period_minutes' => 10,
            'is_overnight' => false,
            'is_active' => true,
        ]);

        $this->membership->update(['default_shift_id' => $shift->id]);

        // Clock in at 08:17 (17 minutes after start, exceeds 10 minute grace period)
        Carbon::setTestNow(Carbon::parse('2026-10-05 08:17:00', 'Asia/Jakarta'));

        $attendance = $this->attendanceService->clockIn($this->business, $this->employee, [
            'latitude' => -6.190000,
            'longitude' => 106.820000,
            'accuracy' => 10,
        ]);

        // Lateness must be calculated from 08:00 scheduled start = 17 minutes!
        $this->assertEquals(Attendance::STATUS_LATE, $attendance->status);
        $this->assertEquals(Attendance::CLOCK_IN_LATE, $attendance->clock_in_status);
        $this->assertEquals(17, $attendance->late_minutes);
        $this->assertEquals(0, $attendance->early_in_minutes);

        // Feedback verification
        $feedback = $this->attendanceService->buildClockInFeedback($attendance, 'Asia/Jakarta');
        $this->assertEquals('late', $feedback['status']);
        $this->assertEquals(17, $feedback['late_minutes']);
        $this->assertEquals('08:00', $feedback['scheduled_start_time']);
        $this->assertEquals('08:17', $feedback['actual_clock_in_time']);
        $this->assertStringContainsStringIgnoringCase('Terlambat 17 menit', $feedback['message']);
    }

    public function test_clock_in_late_with_zero_grace_period(): void
    {
        // Shift: Grace Period = 0 (Strict)
        $shift = WorkShift::create([
            'business_id' => $this->business->id,
            'name' => 'Shift Strict',
            'code' => 'SS-01',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'grace_period_minutes' => 0,
            'is_overnight' => false,
            'is_active' => true,
        ]);

        $this->membership->update(['default_shift_id' => $shift->id]);

        // Clock in at 08:01 (1 minute late)
        Carbon::setTestNow(Carbon::parse('2026-10-05 08:01:00', 'Asia/Jakarta'));

        $attendance = $this->attendanceService->clockIn($this->business, $this->employee, [
            'latitude' => -6.190000,
            'longitude' => 106.820000,
            'accuracy' => 10,
        ]);

        $this->assertEquals(Attendance::STATUS_LATE, $attendance->status);
        $this->assertEquals(Attendance::CLOCK_IN_LATE, $attendance->clock_in_status);
        $this->assertEquals(1, $attendance->late_minutes);
    }

    public function test_overnight_shift_treated_as_single_session_across_midnight(): void
    {
        // Shift Malam: 22:00 - 06:00 (+1 hari)
        $overnightShift = WorkShift::create([
            'business_id' => $this->business->id,
            'location_id' => $this->jakartaLocation->id,
            'name' => 'Shift Malam',
            'code' => 'SM-01',
            'start_time' => '22:00',
            'end_time' => '06:00',
            'is_overnight' => true,
            'grace_period_minutes' => 15,
            'is_active' => true,
        ]);

        $this->membership->update(['default_shift_id' => $overnightShift->id]);

        // Step 1: Clock in at night 05 Oct 2026 22:00:00 WIB
        Carbon::setTestNow(Carbon::parse('2026-10-05 22:00:00', 'Asia/Jakarta'));

        $attendance = $this->attendanceService->clockIn($this->business, $this->employee, [
            'latitude' => -6.190000,
            'longitude' => 106.820000,
            'accuracy' => 10,
        ]);

        $this->assertEquals('2026-10-05', $attendance->date->toDateString());
        $this->assertEquals(Attendance::STATUS_PRESENT, $attendance->status);
        $this->assertEquals(0, $attendance->late_minutes);
        $this->assertNotNull($attendance->scheduled_start_at);
        $this->assertNotNull($attendance->scheduled_end_at);

        // Scheduled end must be next morning 06 Oct 2026 06:00 WIB
        $schedEndLocal = $attendance->scheduled_end_at->copy()->setTimezone('Asia/Jakarta');
        $this->assertEquals('2026-10-06 06:00', $schedEndLocal->format('Y-m-d H:i'));

        // Step 2: Clock out next morning 06 Oct 2026 06:00:00 WIB
        Carbon::setTestNow(Carbon::parse('2026-10-06 06:00:00', 'Asia/Jakarta'));

        $completedAttendance = $this->attendanceService->clockOut($this->business, $this->employee, [
            'latitude' => -6.190000,
            'longitude' => 106.820000,
            'accuracy' => 10,
        ]);

        // Must still be the SAME single attendance record for 2026-10-05
        $this->assertEquals($attendance->id, $completedAttendance->id);
        $this->assertEquals('2026-10-05', $completedAttendance->date->toDateString());
        $this->assertNotNull($completedAttendance->clock_out_at);
        $this->assertEquals(480, $completedAttendance->work_duration_minutes); // 8 hours = 480 min
        $this->assertEquals(Attendance::CLOCK_OUT_NORMAL, $completedAttendance->clock_out_status);

        // Ensure only 1 attendance record was created in the database
        $count = Attendance::where('business_id', $this->business->id)
            ->where('user_id', $this->employee->id)
            ->count();
        $this->assertEquals(1, $count);
    }

    public function test_multi_branch_timezone_evaluation(): void
    {
        // Re-assign employee to Makassar location (WITA / Asia/Makassar)
        $this->membership->update([
            'primary_location_id' => $this->makassarLocation->id,
            'default_shift_id' => null,
        ]);

        $makassarShift = WorkShift::create([
            'business_id' => $this->business->id,
            'location_id' => $this->makassarLocation->id,
            'name' => 'Shift Pagi Makassar',
            'code' => 'MKS-01',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'grace_period_minutes' => 10,
            'is_overnight' => false,
            'is_active' => true,
        ]);

        $this->membership->update(['default_shift_id' => $makassarShift->id]);

        // When it is 08:05 in Makassar (WITA, UTC+8), in WIB (UTC+7) it is 07:05, and in UTC it is 00:05.
        // If system used server time without outlet timezone, 08:05 Makassar would be miscalculated!
        Carbon::setTestNow(Carbon::parse('2026-10-05 08:05:00', 'Asia/Makassar'));

        $attendance = $this->attendanceService->clockIn($this->business, $this->employee, [
            'latitude' => -5.140000,
            'longitude' => 119.410000,
            'accuracy' => 10,
        ]);

        $this->assertEquals(Attendance::STATUS_PRESENT, $attendance->status);
        $this->assertEquals(0, $attendance->late_minutes);
        $this->assertEquals('Asia/Makassar', $attendance->timezone);

        $feedback = $this->attendanceService->buildClockInFeedback($attendance, 'Asia/Makassar');
        $this->assertEquals('08:00', $feedback['scheduled_start_time']);
        $this->assertEquals('08:05', $feedback['actual_clock_in_time']);
        $this->assertStringContainsString('WITA', $feedback['message']);
    }

    public function test_roster_day_by_day_variance_and_off_day(): void
    {
        // Shift Pagi (08:00 - 16:00)
        $shiftPagi = WorkShift::create([
            'business_id' => $this->business->id,
            'name' => 'Shift Pagi',
            'code' => 'SP',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'is_active' => true,
        ]);

        // Shift Siang (14:00 - 22:00)
        $shiftSiang = WorkShift::create([
            'business_id' => $this->business->id,
            'name' => 'Shift Siang',
            'code' => 'SS',
            'start_time' => '14:00',
            'end_time' => '22:00',
            'is_active' => true,
        ]);

        // Roster Setup:
        // Monday -> Shift Pagi
        EmployeeSchedule::create([
            'business_id' => $this->business->id,
            'user_id' => $this->employee->id,
            'work_shift_id' => $shiftPagi->id,
            'schedule_type' => EmployeeSchedule::TYPE_RECURRING,
            'day_of_week' => EmployeeSchedule::DAY_MONDAY,
            'is_off_day' => false,
            'effective_date' => '2026-10-01',
        ]);

        // Tuesday -> Shift Siang
        EmployeeSchedule::create([
            'business_id' => $this->business->id,
            'user_id' => $this->employee->id,
            'work_shift_id' => $shiftSiang->id,
            'schedule_type' => EmployeeSchedule::TYPE_RECURRING,
            'day_of_week' => EmployeeSchedule::DAY_TUESDAY,
            'is_off_day' => false,
            'effective_date' => '2026-10-01',
        ]);

        // Wednesday -> OFF Day
        EmployeeSchedule::create([
            'business_id' => $this->business->id,
            'user_id' => $this->employee->id,
            'work_shift_id' => null,
            'schedule_type' => EmployeeSchedule::TYPE_RECURRING,
            'day_of_week' => EmployeeSchedule::DAY_WEDNESDAY,
            'is_off_day' => true,
            'effective_date' => '2026-10-01',
        ]);

        // Day 1: Monday 2026-10-05 08:00 WIB -> Resolves Shift Pagi
        $resolvedMonday = $this->scheduleService->resolveActiveShift(
            $this->business,
            $this->employee,
            $this->jakartaLocation,
            Carbon::parse('2026-10-05 08:00:00', 'Asia/Jakarta')
        );
        $this->assertEquals($shiftPagi->id, $resolvedMonday['work_shift_id']);
        $this->assertEquals('Shift Pagi', $resolvedMonday['shift_name']);
        $this->assertFalse($resolvedMonday['is_off_day']);

        // Day 2: Tuesday 2026-10-06 14:00 WIB -> Resolves Shift Siang
        $resolvedTuesday = $this->scheduleService->resolveActiveShift(
            $this->business,
            $this->employee,
            $this->jakartaLocation,
            Carbon::parse('2026-10-06 14:00:00', 'Asia/Jakarta')
        );
        $this->assertEquals($shiftSiang->id, $resolvedTuesday['work_shift_id']);
        $this->assertEquals('Shift Siang', $resolvedTuesday['shift_name']);
        $this->assertFalse($resolvedTuesday['is_off_day']);

        // Day 3: Wednesday 2026-10-07 09:00 WIB -> Resolves OFF Day and rejects clock in
        Carbon::setTestNow(Carbon::parse('2026-10-07 09:00:00', 'Asia/Jakarta'));

        $this->expectException(ValidationException::class);
        $this->attendanceService->clockIn($this->business, $this->employee, [
            'latitude' => -6.190000,
            'longitude' => 106.820000,
            'accuracy' => 10,
        ]);
    }

    public function test_historical_immutability_when_shift_or_roster_changes(): void
    {
        // 01 October: Employee works under Shift A (08:00 - 16:00)
        $shiftA = WorkShift::create([
            'business_id' => $this->business->id,
            'name' => 'Shift A',
            'code' => 'SA',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'grace_period_minutes' => 0,
            'is_active' => true,
        ]);

        $this->membership->update(['default_shift_id' => $shiftA->id]);

        Carbon::setTestNow(Carbon::parse('2026-10-01 08:15:00', 'Asia/Jakarta'));

        $pastAttendance = $this->attendanceService->clockIn($this->business, $this->employee, [
            'latitude' => -6.190000,
            'longitude' => 106.820000,
            'accuracy' => 10,
        ]);

        $this->assertEquals('Shift A', $pastAttendance->shift_name);
        $this->assertEquals(15, $pastAttendance->late_minutes);
        $this->assertEquals(Attendance::STATUS_LATE, $pastAttendance->status);

        // Later on 10 October: Admin changes employee default shift to Shift B (14:00 - 22:00)
        $shiftB = WorkShift::create([
            'business_id' => $this->business->id,
            'name' => 'Shift B',
            'code' => 'SB',
            'start_time' => '14:00',
            'end_time' => '22:00',
            'is_active' => true,
        ]);

        $this->membership->update(['default_shift_id' => $shiftB->id]);

        // Re-fetch past attendance from database
        $refetched = Attendance::find($pastAttendance->id);

        // Past record must remain 100% IMMUTABLE!
        $this->assertEquals('Shift A', $refetched->shift_name);
        $this->assertEquals($shiftA->id, $refetched->work_shift_id);
        $this->assertEquals(15, $refetched->late_minutes);
        $this->assertEquals(Attendance::STATUS_LATE, $refetched->status);
    }

    public function test_unscheduled_employee_without_shift_has_no_fake_operating_hours(): void
    {
        // No shift assigned, no roster schedule
        $this->membership->update(['default_shift_id' => null]);

        // Even if clocked in at 10:30, employee is NOT marked late because there is no schedule
        Carbon::setTestNow(Carbon::parse('2026-10-05 10:30:00', 'Asia/Jakarta'));

        $attendance = $this->attendanceService->clockIn($this->business, $this->employee, [
            'latitude' => -6.190000,
            'longitude' => 106.820000,
            'accuracy' => 10,
        ]);

        $this->assertNull($attendance->work_shift_id);
        $this->assertEquals('Bebas Jadwal / Tanpa Shift', $attendance->shift_name);
        $this->assertNull($attendance->scheduled_start_at);
        $this->assertNull($attendance->scheduled_end_at);
        $this->assertEquals(0, $attendance->late_minutes);
        $this->assertEquals(0, $attendance->early_in_minutes);
        $this->assertEquals(Attendance::STATUS_PRESENT, $attendance->status);
        $this->assertEquals(Attendance::CLOCK_IN_ON_TIME, $attendance->clock_in_status);
    }

    public function test_early_clock_out_calculation(): void
    {
        $shift = WorkShift::create([
            'business_id' => $this->business->id,
            'location_id' => $this->jakartaLocation->id,
            'name' => 'Shift Siang',
            'code' => 'SS-01',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'is_active' => true,
        ]);

        $this->membership->update(['default_shift_id' => $shift->id]);

        // Clock in at 08:00
        Carbon::setTestNow(Carbon::parse('2026-10-05 08:00:00', 'Asia/Jakarta'));
        $this->attendanceService->clockIn($this->business, $this->employee, [
            'latitude' => -6.190000,
            'longitude' => 106.820000,
            'accuracy' => 10,
        ]);

        // Clock out early at 16:30 (30 minutes early)
        Carbon::setTestNow(Carbon::parse('2026-10-05 16:30:00', 'Asia/Jakarta'));
        $completed = $this->attendanceService->clockOut($this->business, $this->employee, [
            'latitude' => -6.190000,
            'longitude' => 106.820000,
            'accuracy' => 10,
        ]);

        $this->assertEquals(Attendance::CLOCK_OUT_EARLY, $completed->clock_out_status);
        $this->assertEquals(510, $completed->work_duration_minutes); // 8.5 hours = 510 minutes
    }

    public function test_api_clock_in_returns_rich_feedback_payload(): void
    {
        $shift = WorkShift::create([
            'business_id' => $this->business->id,
            'location_id' => $this->jakartaLocation->id,
            'name' => 'Shift Pagi',
            'code' => 'SP-01',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'grace_period_minutes' => 10,
            'is_active' => true,
        ]);

        $this->membership->update(['default_shift_id' => $shift->id]);

        Carbon::setTestNow(Carbon::parse('2026-10-05 08:18:00', 'Asia/Jakarta'));

        Sanctum::actingAs($this->employee);
        Context::setBusiness($this->business, $this->membership);

        $response = $this->withHeader('X-Business-Id', $this->business->id)
            ->postJson('/api/v1/attendance/clock-in', [
                'latitude' => -6.190000,
                'longitude' => 106.820000,
                'accuracy' => 10,
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'clock_in_at',
                'status',
                'shift_name',
                'scheduled_start_time',
                'late_minutes',
                'early_in_minutes',
                'status_label',
                'feedback' => [
                    'status',
                    'status_label',
                    'late_minutes',
                    'early_in_minutes',
                    'scheduled_start_time',
                    'actual_clock_in_time',
                    'shift_name',
                    'timezone',
                    'badge_variant',
                    'message',
                ],
            ],
        ]);

        $feedback = $response->json('data.feedback');
        $this->assertEquals('late', $feedback['status']);
        $this->assertEquals(18, $feedback['late_minutes']);
        $this->assertEquals('08:00', $feedback['scheduled_start_time']);
        $this->assertEquals('08:18', $feedback['actual_clock_in_time']);
        $this->assertEquals('Shift Pagi', $feedback['shift_name']);
        $this->assertStringContainsStringIgnoringCase('Terlambat 18 menit', $feedback['message']);
    }
}
