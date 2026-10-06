<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Domain\HRM\Biometrics\FaceVerificationService;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceException;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\Location;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class AttendanceApiTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private User $hrManager;
    private User $employee;
    private Location $officeLocation;
    private FaceVerificationService $faceService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->faceService = app(FaceVerificationService::class);

        // 1. Business & Owner
        $this->owner = User::create([
            'name' => 'Pak Bos Owner',
            'email' => 'owner@cooca-api-test.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'PT Cooca Solusi Digital',
            'slug' => 'cooca-solusi-digital',
            'email' => 'info@cooca-api-test.com',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);

        // 2. HR Manager
        $this->hrManager = User::create([
            'name' => 'HR Specialist Linda',
            'email' => 'linda@cooca-api-test.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $this->business->users()->attach($this->hrManager->id, [
            'id' => (string) Str::uuid(),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->hrManager->update(['active_business_id' => $this->business->id]);

        // 3. Employee
        $this->employee = User::create([
            'name' => 'Dedi Barista',
            'email' => 'dedi@cooca-api-test.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $this->business->users()->attach($this->employee->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'attendance_mode' => 'geofenced',
            'is_active' => true,
        ]);

        $this->employee->update(['active_business_id' => $this->business->id]);

        // 4. Office Location (Monas, Jakarta: -6.175392, 106.827153, Radius: 50m)
        $this->officeLocation = Location::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Kantor Pusat Jakarta',
            'address' => 'Jl. Medan Merdeka Barat No. 1, Jakarta',
            'latitude' => -6.175392,
            'longitude' => 106.827153,
            'geofence_radius_meters' => 50,
            'is_primary' => true,
            'is_active' => true,
        ]);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->getJson('/api/v1/attendance/today');
        $response->assertStatus(401);
    }

    public function test_api_check_in_successful_with_face_and_geofence(): void
    {
        Carbon::setTestNow('2026-10-05 08:00:00');

        $faceKey = 'dedi-registered-biometric-face-data';
        $this->faceService->registerFaceTemplate($this->business, $this->employee, $faceKey);

        Sanctum::actingAs($this->employee);
        Context::setBusiness($this->business, BusinessMembership::where('business_id', $this->business->id)->where('user_id', $this->employee->id)->first());

        $response = $this->postJson('/api/v1/attendance/check-in', [
            'latitude' => -6.175390,
            'longitude' => 106.827150,
            'face_data' => $faceKey,
            'location_id' => $this->officeLocation->id,
            'notes' => 'Presensi shift pagi',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'date' => '2026-10-05',
                    'clock_in_status' => Attendance::CLOCK_IN_ON_TIME,
                    'status' => Attendance::STATUS_PRESENT,
                    'face_verified' => true,
                ],
            ]);

        $this->assertDatabaseHas('attendances', [
            'business_id' => $this->business->id,
            'user_id' => $this->employee->id,
            'status' => Attendance::STATUS_PRESENT,
            'face_verified' => 1,
        ]);

        Carbon::setTestNow();
    }

    public function test_api_check_in_rejected_when_outside_geofence(): void
    {
        Carbon::setTestNow('2026-10-05 08:00:00');

        Sanctum::actingAs($this->employee);
        Context::setBusiness($this->business, BusinessMembership::where('business_id', $this->business->id)->where('user_id', $this->employee->id)->first());

        // Location 5 km away from office
        $response = $this->postJson('/api/v1/attendance/check-in', [
            'latitude' => -6.200000,
            'longitude' => 106.850000,
            'location_id' => $this->officeLocation->id,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertStringContainsString('Presensi ditolak: Anda berada di luar radius kantor', $response->json('message'));

        Carbon::setTestNow();
    }

    public function test_api_check_in_with_exception_policy(): void
    {
        Carbon::setTestNow('2026-10-05 08:00:00');

        // Create WFA exception policy
        AttendanceException::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'user_id' => $this->employee->id,
            'exception_mode' => AttendanceException::MODE_WFA,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
            'location_name' => 'WFA Seluruh Indonesia',
            'reason' => 'Penugasan WFA',
            'status' => AttendanceException::STATUS_APPROVED,
            'approved_by' => $this->hrManager->id,
            'approved_at' => now(),
        ]);

        Sanctum::actingAs($this->employee);
        Context::setBusiness($this->business, BusinessMembership::where('business_id', $this->business->id)->where('user_id', $this->employee->id)->first());

        // Clock-in from remote location
        $response = $this->postJson('/api/v1/attendance/check-in', [
            'latitude' => -7.257472,
            'longitude' => 112.752090, // Surabaya
            'notes' => 'Presensi Surabaya',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'clock_in_status' => Attendance::CLOCK_IN_FREE_LOCATION,
                ],
            ]);

        Carbon::setTestNow();
    }

    public function test_api_check_out_successful(): void
    {
        Carbon::setTestNow('2026-10-05 08:00:00');

        Attendance::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'user_id' => $this->employee->id,
            'date' => '2026-10-05',
            'location_id' => $this->officeLocation->id,
            'clock_in_at' => now(),
            'clock_in_lat' => -6.175392,
            'clock_in_lng' => 106.827153,
            'clock_in_status' => Attendance::CLOCK_IN_ON_TIME,
            'status' => Attendance::STATUS_PRESENT,
            'is_geofenced' => true,
        ]);

        Sanctum::actingAs($this->employee);
        Context::setBusiness($this->business, BusinessMembership::where('business_id', $this->business->id)->where('user_id', $this->employee->id)->first());

        Carbon::setTestNow('2026-10-05 17:00:00');

        $response = $this->postJson('/api/v1/attendance/check-out', [
            'latitude' => -6.175390,
            'longitude' => 106.827150,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'work_duration_minutes' => 540, // 9 hours
                ],
            ]);

        Carbon::setTestNow();
    }

    public function test_api_today_endpoint_returns_attendance_and_active_exception(): void
    {
        Carbon::setTestNow('2026-10-05 08:00:00');

        Sanctum::actingAs($this->employee);
        Context::setBusiness($this->business, BusinessMembership::where('business_id', $this->business->id)->where('user_id', $this->employee->id)->first());

        $response = $this->getJson('/api/v1/attendance/today');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'server_time',
                    'date',
                    'employee' => [
                        'id',
                        'name',
                        'attendance_mode',
                        'face_registered',
                    ],
                    'attendance',
                    'active_exception',
                ],
            ]);

        Carbon::setTestNow();
    }

    public function test_api_history_and_summary_endpoints(): void
    {
        Sanctum::actingAs($this->owner);
        Context::setBusiness($this->business, BusinessMembership::where('business_id', $this->business->id)->where('user_id', $this->owner->id)->first());

        $summaryRes = $this->getJson('/api/v1/attendance/summary');
        $summaryRes->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'date',
                    'total_employees',
                    'present_count',
                    'late_count',
                    'absent_count',
                    'pending_corrections',
                ],
            ]);

        $historyRes = $this->getJson('/api/v1/attendance/history');
        $historyRes->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'meta' => ['total', 'current_page'],
            ]);
    }

    public function test_api_face_registration_and_verification_endpoints(): void
    {
        Sanctum::actingAs($this->employee);
        Context::setBusiness($this->business, BusinessMembership::where('business_id', $this->business->id)->where('user_id', $this->employee->id)->first());

        $faceSample = 'base64-or-raw-biometric-face-string-dedi';

        // 1. Register face
        $regResponse = $this->postJson('/api/v1/attendance/face-template/register', [
            'face_data' => $faceSample,
        ]);
        $regResponse->assertStatus(200)->assertJson(['success' => true]);

        // 2. Verify registered face -> 200 OK
        $verResponse = $this->postJson('/api/v1/attendance/face-template/verify', [
            'face_data' => $faceSample,
        ]);
        $verResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['verified' => true],
            ]);

        // 3. Verify wrong face -> 422
        $wrongResponse = $this->postJson('/api/v1/attendance/face-template/verify', [
            'face_data' => 'wrong-stranger-face',
        ]);
        $wrongResponse->assertStatus(422)
            ->assertJson([
                'success' => false,
                'data' => ['verified' => false],
            ]);
    }

    public function test_api_correction_lifecycle_workflow(): void
    {
        // 1. Employee submits correction ticket
        Sanctum::actingAs($this->employee);
        Context::setBusiness($this->business, BusinessMembership::where('business_id', $this->business->id)->where('user_id', $this->employee->id)->first());

        $storeRes = $this->postJson('/api/v1/attendance/corrections', [
            'target_date' => '2026-10-01',
            'correction_type' => 'full_day',
            'proposed_clock_in' => '08:00',
            'proposed_clock_out' => '17:00',
            'proposed_status' => 'present',
            'reason' => 'Mesin absensi cabang mati listrik seharian.',
        ]);

        $storeRes->assertStatus(201);
        $ticketId = $storeRes->json('data.id');

        // 2. HR requests revision
        Sanctum::actingAs($this->hrManager);
        Context::setBusiness($this->business, BusinessMembership::where('business_id', $this->business->id)->where('user_id', $this->hrManager->id)->first());

        $revRes = $this->postJson("/api/v1/attendance/corrections/{$ticketId}/request-revision", [
            'review_notes' => 'Tolong lampirkan keterangan saksi supervisor.',
        ]);
        $revRes->assertStatus(200)
            ->assertJson(['success' => true]);

        // 3. Employee edits and resubmits
        Sanctum::actingAs($this->employee);
        Context::setBusiness($this->business, BusinessMembership::where('business_id', $this->business->id)->where('user_id', $this->employee->id)->first());

        $updateRes = $this->putJson("/api/v1/attendance/corrections/{$ticketId}", [
            'reason' => 'Mesin absensi mati listrik (telah dikonfirmasi supervisor cabang).',
        ]);
        $updateRes->assertStatus(200)
            ->assertJson(['success' => true]);

        // 4. HR approves ticket
        Sanctum::actingAs($this->hrManager);
        Context::setBusiness($this->business, BusinessMembership::where('business_id', $this->business->id)->where('user_id', $this->hrManager->id)->first());

        $approveRes = $this->postJson("/api/v1/attendance/corrections/{$ticketId}/approve", [
            'review_notes' => 'Disetujui.',
        ]);
        $approveRes->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verify Attendance record updated in DB
        $attendance = Attendance::where('business_id', $this->business->id)
            ->where('user_id', $this->employee->id)
            ->whereDate('date', '2026-10-01')
            ->first();

        $this->assertNotNull($attendance);
        $this->assertEquals(Attendance::STATUS_PRESENT, $attendance->status);
    }

    public function test_api_security_idor_and_unauthorized_approval_prevention(): void
    {
        // Employee creates a correction ticket
        Sanctum::actingAs($this->employee);
        Context::setBusiness($this->business, BusinessMembership::where('business_id', $this->business->id)->where('user_id', $this->employee->id)->first());

        $storeRes = $this->postJson('/api/v1/attendance/corrections', [
            'target_date' => '2026-10-02',
            'correction_type' => 'full_day',
            'proposed_clock_in' => '08:00',
            'proposed_clock_out' => '17:00',
            'reason' => 'Tiket untuk uji otorisasi keamanan.',
        ]);

        $ticketId = $storeRes->json('data.id');

        // Non-HR employee attempts to approve their own ticket -> MUST BE FORBIDDEN (403)
        $attackRes = $this->postJson("/api/v1/attendance/corrections/{$ticketId}/approve");
        $attackRes->assertStatus(403);

        // Non-HR employee attempts to create attendance exception -> MUST BE FORBIDDEN (403)
        $excRes = $this->postJson('/api/v1/attendance/exceptions', [
            'user_id' => $this->employee->id,
            'policy_type' => 'wfa',
            'name' => 'Fake Exception',
            'reason' => 'Privilege escalation attack',
            'effective_from' => '2026-10-01',
        ]);
        $excRes->assertStatus(403);
    }
}
