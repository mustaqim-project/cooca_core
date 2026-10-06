<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\HRM\Biometrics\FaceVerificationService;
use App\Models\Attendance;
use App\Models\AttendanceException;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\BusinessSubscription;
use App\Models\Location;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AttendanceConcurrencyAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    private Business $businessA;
    private Business $businessB;
    private User $employeeA;
    private User $employeeB;
    private BusinessMembership $membershipA;
    private Location $outletSenopati;
    private Location $outletBandung;
    private FaceVerificationService $faceService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->faceService = app(FaceVerificationService::class);

        // 1. Business A (Jakarta)
        $this->businessA = Business::create([
            'name' => 'Cooca Bakery Senopati',
            'slug' => 'cooca-bakery-senopati',
            'email' => 'senopati@coocabakery.com',
            'phone' => '081298765432',
            'city' => 'Jakarta Selatan',
            'address' => 'Jl. Senopati No. 88, Jakarta Selatan',
            'is_active' => true,
        ]);

        BusinessSubscription::create([
            'business_id' => $this->businessA->id,
            'plan_code' => BusinessSubscription::PLAN_PRESTIGE_MONTHLY,
            'price' => 199000,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addYear(),
        ]);

        // Primary Location for Business A (Senopati: -6.229700, 106.807400, Radius 50m)
        $this->outletSenopati = Location::create([
            'business_id' => $this->businessA->id,
            'name' => 'Outlet Senopati Utama',
            'code' => 'LOC-SNT-01',
            'type' => 'outlet',
            'latitude' => -6.229700,
            'longitude' => 106.807400,
            'geofence_radius_meters' => 50,
            'is_primary' => true,
            'is_active' => true,
        ]);

        // Employee A
        $this->employeeA = User::create([
            'name' => 'Dewi Lestari',
            'email' => 'dewi@coocabakery.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'phone' => '081298765433',
            'active_business_id' => $this->businessA->id,
        ]);

        $roleA = Role::create([
            'business_id' => $this->businessA->id,
            'name' => 'Senior Baker',
            'slug' => 'senior-baker',
            'is_system' => false,
        ]);

        $this->businessA->users()->attach($this->employeeA->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'role_id' => $roleA->id,
            'primary_location_id' => $this->outletSenopati->id,
            'job_title' => 'Senior Baker',
            'employment_type' => 'permanent',
            'base_salary' => 5000000,
            'is_active' => true,
        ]);

        $this->membershipA = BusinessMembership::where('business_id', $this->businessA->id)
            ->where('user_id', $this->employeeA->id)
            ->firstOrFail();

        // 2. Business B (Bandung - for Cross-Tenant attack testing)
        $this->businessB = Business::create([
            'name' => 'Cooca Bakery Bandung',
            'slug' => 'cooca-bakery-bandung',
            'email' => 'bandung@coocabakery.com',
            'phone' => '081298765434',
            'city' => 'Bandung',
            'is_active' => true,
        ]);

        $this->outletBandung = Location::create([
            'business_id' => $this->businessB->id,
            'name' => 'Outlet Dago Bandung',
            'code' => 'LOC-DGO-01',
            'type' => 'outlet',
            'latitude' => -6.890000,
            'longitude' => 107.610000,
            'geofence_radius_meters' => 50,
            'is_primary' => true,
            'is_active' => true,
        ]);

        $this->employeeB = User::create([
            'name' => 'Asep Surasep',
            'email' => 'asep@coocabakery.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'phone' => '081298765435',
            'active_business_id' => $this->businessB->id,
        ]);

        $this->businessB->users()->attach($this->employeeB->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'primary_location_id' => $this->outletBandung->id,
            'job_title' => 'Junior Baker',
            'is_active' => true,
        ]);
    }

    /**
     * Helper to create a valid base64 PNG image string.
     */
    private function createSamplePngBase64(int $red = 100, int $green = 150, int $blue = 200): string
    {
        $im = imagecreatetruecolor(60, 60);
        $bg = imagecolorallocate($im, $red, $green, $blue);
        imagefilledrectangle($im, 0, 0, 59, 59, $bg);
        // Add contrasting shapes so multi-block gradient descriptors have distinct values
        $fg = imagecolorallocate($im, 255 - $red, 255 - $green, 255 - $blue);
        imagefilledellipse($im, 30, 30, 20, 30, $fg);
        ob_start();
        imagepng($im);
        $contents = (string) ob_get_clean();
        imagedestroy($im);

        return 'data:image/png;base64,' . base64_encode($contents);
    }

    /**
     * 1. Test Biometric Face Matching Rejection when similarity is low or mismatched.
     */
    public function test_clock_in_rejected_when_face_biometric_does_not_match(): void
    {
        // Register Employee A with Image 1
        $faceSampleA = $this->createSamplePngBase64(30, 80, 200);
        $this->faceService->registerFaceTemplate($this->businessA, $this->employeeA, $faceSampleA);

        // Attempt Clock-in with completely different visual pattern / colors
        $faceSampleDifferent = $this->createSamplePngBase64(240, 240, 10);

        $response = $this->actingAs($this->employeeA)
            ->postJson(route('hrm.attendance.clock-in'), [
                'latitude' => -6.229700,
                'longitude' => 106.807400,
                'accuracy' => 15.0,
                'location_id' => $this->outletSenopati->id,
                'face_data' => $faceSampleDifferent,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['face']);
        $this->assertStringContainsString('tidak cocok', $response->json('errors.face.0'));
        $this->assertDatabaseMissing('attendances', [
            'business_id' => $this->businessA->id,
            'user_id' => $this->employeeA->id,
        ]);
    }

    /**
     * 2. Test Geofence rejection when employee is outside allowed radius.
     */
    public function test_clock_in_rejected_when_outside_geofence_radius(): void
    {
        // Valid face enrolled
        $faceSampleA = $this->createSamplePngBase64(80, 120, 160);
        $this->faceService->registerFaceTemplate($this->businessA, $this->employeeA, $faceSampleA);

        // Location is Senopati (-6.229700, 106.807400).
        // Send coordinate 1.5 km away in Blok M (-6.244000, 106.799000).
        $response = $this->actingAs($this->employeeA)
            ->postJson(route('hrm.attendance.clock-in'), [
                'latitude' => -6.244000,
                'longitude' => 106.799000,
                'accuracy' => 10.0,
                'location_id' => $this->outletSenopati->id,
                'face_data' => $faceSampleA,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['location']);
        $this->assertStringContainsString('radius', $response->json('errors.location.0'));
    }

    /**
     * 3. Test GPS accuracy rejection when accuracy is too low (>100m).
     */
    public function test_clock_in_rejected_when_gps_accuracy_is_degraded(): void
    {
        $faceSampleA = $this->createSamplePngBase64(80, 120, 160);
        $this->faceService->registerFaceTemplate($this->businessA, $this->employeeA, $faceSampleA);

        // Accuracy is 300m (exceeds 250m threshold)
        $response = $this->actingAs($this->employeeA)
            ->postJson(route('hrm.attendance.clock-in'), [
                'latitude' => -6.229700,
                'longitude' => 106.807400,
                'accuracy' => 300.0,
                'location_id' => $this->outletSenopati->id,
                'face_data' => $faceSampleA,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['gps']);
        $this->assertStringContainsString('Akurasi sinyal GPS', $response->json('errors.gps.0'));
    }

    /**
     * 4. Test WFH / WFA approved exception allows clock-in from remote location.
     */
    public function test_wfh_approved_exception_bypasses_geofence_radius(): void
    {
        $faceSampleA = $this->createSamplePngBase64(80, 120, 160);
        $this->faceService->registerFaceTemplate($this->businessA, $this->employeeA, $faceSampleA);

        $today = Carbon::now('Asia/Jakarta')->toDateString();
        Carbon::setTestNow(Carbon::parse($today . ' 07:55:00', 'Asia/Jakarta'));

        // Create approved WFH exception
        AttendanceException::create([
            'business_id' => $this->businessA->id,
            'user_id' => $this->employeeA->id,
            'exception_mode' => AttendanceException::MODE_WFH,
            'start_date' => $today,
            'end_date' => $today,
            'location_name' => 'Rumah Karyawan Jakarta',
            'reason' => 'Work From Home (WFH) persetujuan manajemen',
            'status' => AttendanceException::STATUS_APPROVED,
        ]);

        // Clock in from remote location (Blok M)
        $response = $this->actingAs($this->employeeA)
            ->postJson(route('hrm.attendance.clock-in'), [
                'latitude' => -6.244000,
                'longitude' => 106.799000,
                'accuracy' => 20.0,
                'face_data' => $faceSampleA,
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('attendances', [
            'business_id' => $this->businessA->id,
            'user_id' => $this->employeeA->id,
            'status' => Attendance::STATUS_PRESENT,
        ]);

        Carbon::setTestNow();
    }

    /**
     * 5. Test Pessimistic Lock & Idempotency: Rapid duplicate check-in is rejected cleanly.
     */
    public function test_rapid_duplicate_clock_in_is_rejected_without_race_condition(): void
    {
        $faceSampleA = $this->createSamplePngBase64(80, 120, 160);
        $this->faceService->registerFaceTemplate($this->businessA, $this->employeeA, $faceSampleA);

        // 1st Clock In -> Success
        $firstResponse = $this->actingAs($this->employeeA)
            ->postJson(route('hrm.attendance.clock-in'), [
                'latitude' => -6.229700,
                'longitude' => 106.807400,
                'accuracy' => 15.0,
                'location_id' => $this->outletSenopati->id,
                'face_data' => $faceSampleA,
            ]);

        $firstResponse->assertOk();

        // 2nd Immediate Clock In -> Throws validation error (Already clocked in)
        $secondResponse = $this->actingAs($this->employeeA)
            ->postJson(route('hrm.attendance.clock-in'), [
                'latitude' => -6.229700,
                'longitude' => 106.807400,
                'accuracy' => 15.0,
                'location_id' => $this->outletSenopati->id,
                'face_data' => $faceSampleA,
            ]);

        $secondResponse->assertStatus(422);
        $this->assertStringContainsString('sudah melakukan presensi masuk', $secondResponse->json('message') ?? '');

        // Verify only 1 attendance row exists
        $this->assertEquals(1, Attendance::where('business_id', $this->businessA->id)->where('user_id', $this->employeeA->id)->count());
    }

    /**
     * 6. Test Clock-out before Clock-in is rejected.
     */
    public function test_clock_out_before_clock_in_is_rejected(): void
    {
        $response = $this->actingAs($this->employeeA)
            ->postJson(route('hrm.attendance.clock-out'), [
                'latitude' => -6.229700,
                'longitude' => 106.807400,
                'accuracy' => 15.0,
                'location_id' => $this->outletSenopati->id,
            ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('Belum ada catatan presensi masuk', $response->json('message') ?? '');
    }

    /**
     * 7. Test Complete Shift Working Hours & Overtime Calculation upon Clock-Out.
     */
    public function test_clock_out_calculates_working_hours_accurately(): void
    {
        $faceSampleA = $this->createSamplePngBase64(80, 120, 160);
        $this->faceService->registerFaceTemplate($this->businessA, $this->employeeA, $faceSampleA);

        $today = Carbon::now('Asia/Jakarta')->toDateString();

        // 1. Clock in at 08:00 WIB
        Carbon::setTestNow(Carbon::parse($today . ' 08:00:00', 'Asia/Jakarta'));

        $clockInResponse = $this->actingAs($this->employeeA)
            ->postJson(route('hrm.attendance.clock-in'), [
                'latitude' => -6.229700,
                'longitude' => 106.807400,
                'accuracy' => 10.0,
                'location_id' => $this->outletSenopati->id,
                'face_data' => $faceSampleA,
            ]);

        $clockInResponse->assertOk();

        // 2. Freeze time at 17:30 WIB (9.5 hours total = 570 mins, 90 mins overtime past standard 480 mins)
        Carbon::setTestNow(Carbon::parse($today . ' 17:30:00', 'Asia/Jakarta'));

        $response = $this->actingAs($this->employeeA)
            ->postJson(route('hrm.attendance.clock-out'), [
                'latitude' => -6.229700,
                'longitude' => 106.807400,
                'accuracy' => 10.0,
                'notes' => 'Selesai shift operasional sore',
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $attendance = Attendance::where('business_id', $this->businessA->id)
            ->where('user_id', $this->employeeA->id)
            ->firstOrFail();

        $this->assertNotNull($attendance->clock_out_at);
        $this->assertEquals(570, $attendance->work_duration_minutes);
        $this->assertEquals(90, $attendance->overtime_minutes);
        $this->assertEquals(Attendance::CLOCK_OUT_OVERTIME, $attendance->clock_out_status);

        Carbon::setTestNow(); // Clear frozen time
    }

    /**
     * 8. Test Multi-Tenant Attack: Employee cannot clock in using Location from another tenant.
     */
    public function test_cross_tenant_location_clock_in_is_rejected(): void
    {
        $faceSampleA = $this->createSamplePngBase64(80, 120, 160);
        $this->faceService->registerFaceTemplate($this->businessA, $this->employeeA, $faceSampleA);

        // Employee A belongs to Business A, attempting to use location_id of Business B (outletBandung)
        $response = $this->actingAs($this->employeeA)
            ->postJson(route('hrm.attendance.clock-in'), [
                'latitude' => -6.890000,
                'longitude' => 107.610000,
                'accuracy' => 10.0,
                'location_id' => $this->outletBandung->id,
                'face_data' => $faceSampleA,
            ]);

        // Cross-tenant location resolution will fallback to Business A's primary location (Senopati),
        // causing geofence radius rejection against Senopati coordinates (~120km away in Bandung)
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['location']);
        $this->assertStringContainsString('radius', $response->json('errors.location.0'));
    }
}
