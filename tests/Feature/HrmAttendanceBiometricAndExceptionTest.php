<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\HRM\AttendanceExceptionService;
use App\Domain\HRM\AttendanceService;
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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

final class HrmAttendanceBiometricAndExceptionTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private User $hrManager;
    private User $employee;
    private BusinessMembership $employeeMembership;
    private Location $officeLocation;
    private FaceVerificationService $faceService;
    private AttendanceService $attendanceService;
    private AttendanceExceptionService $exceptionService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->faceService = app(FaceVerificationService::class);
        $this->attendanceService = app(AttendanceService::class);
        $this->exceptionService = app(AttendanceExceptionService::class);

        // 1. Business & Owner
        $this->owner = User::create([
            'name' => 'Owner Bisnis Cooca',
            'email' => 'owner@cooca-test.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'PT Cooca Biometrics Nusantara',
            'slug' => 'cooca-biometrics-nusantara',
            'email' => 'corporate@cooca-test.com',
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
            'name' => 'HR Manager Siti',
            'email' => 'hr@cooca-test.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $this->business->users()->attach($this->hrManager->id, [
            'id' => (string) Str::uuid(),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // 3. Employee
        $this->employee = User::create([
            'name' => 'Budi Staf Lapangan',
            'email' => 'budi@cooca-test.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $this->business->users()->attach($this->employee->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'attendance_mode' => 'geofenced',
            'is_active' => true,
        ]);

        $this->employeeMembership = BusinessMembership::where('business_id', $this->business->id)
            ->where('user_id', $this->employee->id)
            ->firstOrFail();

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

        Context::setBusiness($this->business, BusinessMembership::where('business_id', $this->business->id)->where('user_id', $this->owner->id)->first());
    }

    public function test_face_template_registration_encrypts_data_and_hides_from_serialization(): void
    {
        $faceSample = 'sample-biometric-face-token-budi-unique-key-12345';
        $registered = $this->faceService->registerFaceTemplate($this->business, $this->employee, $faceSample);

        $this->assertTrue($registered);

        $membership = $this->employeeMembership->fresh();
        $this->assertNotNull($membership->face_biometric_template);
        $this->assertNotNull($membership->face_registered_at);

        // Verify that raw database column is encrypted and not equal to plain text
        $rawDbValue = $membership->getRawOriginal('face_biometric_template');
        $this->assertNotEquals($faceSample, $rawDbValue);

        // Verify decryptable with Crypt
        $decrypted = Crypt::decryptString($rawDbValue);
        $this->assertJson($decrypted);
        $vector = json_decode($decrypted, true);
        $this->assertIsArray($vector);
        $this->assertCount(128, $vector);

        // Verify hidden on serialization
        $serialized = $membership->toArray();
        $this->assertArrayNotHasKey('face_biometric_template', $serialized);
    }

    public function test_zero_permanent_photo_retention_cleans_temporary_files(): void
    {
        $this->faceService->registerFaceTemplate($this->business, $this->employee, 'face-reference-data');

        // Create temporary image file
        $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'face_test_' . uniqid('', true) . '.jpg';
        file_put_contents($tempPath, 'fake-jpeg-image-binary-stream-1234567890');
        $this->assertFileExists($tempPath);

        $uploadedFile = new UploadedFile($tempPath, 'selfie.jpg', 'image/jpeg', null, true);

        // Verify face
        $this->faceService->verifyFace($this->business, $this->employee, $uploadedFile);

        // The temporary capture file MUST be deleted immediately to comply with Zero-Retention privacy
        $this->assertFileDoesNotExist($tempPath);
    }

    public function test_face_verification_matching_and_mismatch_rejection(): void
    {
        $faceDataBudi = 'biometric-vector-for-budi-secure-hash';
        $this->faceService->registerFaceTemplate($this->business, $this->employee, $faceDataBudi);

        // 1. Same face data -> Match (100% similarity)
        $resultMatch = $this->faceService->verifyFace($this->business, $this->employee, $faceDataBudi);
        $this->assertTrue($resultMatch['verified']);
        $this->assertGreaterThanOrEqual(0.80, $resultMatch['similarity']);

        // 2. Different face data -> Mismatch
        $resultMismatch = $this->faceService->verifyFace($this->business, $this->employee, 'completely-different-face-stranger');
        $this->assertFalse($resultMismatch['verified']);
        $this->assertEquals('FACE_MISMATCH', $resultMismatch['error']);
    }

    public function test_clock_in_with_biometric_face_verification(): void
    {
        Carbon::setTestNow('2026-10-05 08:00:00');
        $faceData = 'budi-valid-face-signature-abc';
        $this->faceService->registerFaceTemplate($this->business, $this->employee, $faceData);

        // Clock in inside office geofence with matching face
        $attendance = $this->attendanceService->clockIn($this->business, $this->employee, [
            'latitude' => -6.175390,
            'longitude' => 106.827150,
            'face_data' => $faceData,
            'location_id' => $this->officeLocation->id,
        ]);

        $this->assertInstanceOf(Attendance::class, $attendance);
        $this->assertTrue($attendance->face_verified);
        $this->assertGreaterThanOrEqual(0.80, (float) $attendance->face_similarity_score);
        $this->assertEquals(Attendance::STATUS_PRESENT, $attendance->status);
        $this->assertEquals(Attendance::CLOCK_IN_ON_TIME, $attendance->clock_in_status);

        Carbon::setTestNow();
    }

    public function test_clock_in_fails_when_face_verification_fails(): void
    {
        Carbon::setTestNow('2026-10-05 08:00:00');
        $this->faceService->registerFaceTemplate($this->business, $this->employee, 'budi-original-face');

        $this->expectException(ValidationException::class);

        // Clock in with stranger's face
        $this->attendanceService->clockIn($this->business, $this->employee, [
            'latitude' => -6.175390,
            'longitude' => 106.827150,
            'face_data' => 'stranger-fraudulent-face',
            'location_id' => $this->officeLocation->id,
        ]);

        Carbon::setTestNow();
    }

    public function test_attendance_exception_policy_wfa_allows_any_location(): void
    {
        Carbon::setTestNow('2026-10-05 08:00:00');

        // Create WFA Exception for Budi
        $exception = $this->exceptionService->createException($this->business, [
            'user_id' => $this->employee->id,
            'policy_type' => AttendanceException::TYPE_WFA,
            'name' => 'Dispensasi Kerja Fleksibel WFA',
            'reason' => 'Program Remote Pilot 2026',
            'effective_from' => '2026-10-01',
            'effective_until' => '2026-10-31',
        ], $this->hrManager);

        $this->assertInstanceOf(AttendanceException::class, $exception);

        // Clock in far away (e.g. Bandung / Bali: -8.670458, 115.212629)
        $attendance = $this->attendanceService->clockIn($this->business, $this->employee, [
            'latitude' => -8.670458,
            'longitude' => 115.212629,
            'notes' => 'Presensi dari Bali Hub',
        ]);

        $this->assertInstanceOf(Attendance::class, $attendance);
        $this->assertEquals(Attendance::CLOCK_IN_FREE_LOCATION, $attendance->clock_in_status);
        $this->assertEquals($exception->id, $attendance->exception_policy_id);

        Carbon::setTestNow();
    }

    public function test_attendance_exception_policy_wfh_validates_custom_geofence(): void
    {
        Carbon::setTestNow('2026-10-05 08:00:00');

        // Budi's home coordinates in Bogor (-6.595038, 106.816635)
        $exception = $this->exceptionService->createException($this->business, [
            'user_id' => $this->employee->id,
            'policy_type' => AttendanceException::TYPE_WFH,
            'name' => 'WFH Rumah Bogor',
            'reason' => 'Jadwal WFH Senin & Kamis',
            'allowed_latitude' => -6.595038,
            'allowed_longitude' => 106.816635,
            'radius_meters' => 100,
            'effective_from' => '2026-10-01',
            'effective_until' => '2026-10-31',
        ], $this->hrManager);

        // 1. Inside WFH Radius (Bogor Home) -> SUCCESS
        $attendance = $this->attendanceService->clockIn($this->business, $this->employee, [
            'latitude' => -6.595050,
            'longitude' => 106.816640,
            'notes' => 'WFH Rumah',
        ]);
        $this->assertEquals($exception->id, $attendance->exception_policy_id);

        Carbon::setTestNow();
    }

    public function test_expired_attendance_exception_falls_back_to_office_geofence(): void
    {
        Carbon::setTestNow('2026-10-05 08:00:00');

        // Exception expired yesterday
        $this->exceptionService->createException($this->business, [
            'user_id' => $this->employee->id,
            'policy_type' => AttendanceException::TYPE_WFA,
            'name' => 'Expired Exception',
            'reason' => 'Dinas Kemarin',
            'effective_from' => '2026-10-01',
            'effective_until' => '2026-10-04',
        ], $this->hrManager);

        // Attempt clock in outside office location -> MUST BE REJECTED
        $this->expectException(ValidationException::class);

        $this->attendanceService->clockIn($this->business, $this->employee, [
            'latitude' => -8.670458,
            'longitude' => 115.212629,
        ]);

        Carbon::setTestNow();
    }

    public function test_shift_grace_period_and_late_calculation(): void
    {
        // 1. Clock in within grace period (08:10 WIB <= 08:15) -> On Time
        Carbon::setTestNow('2026-10-05 08:10:00');
        $attOnTime = $this->attendanceService->clockIn($this->business, $this->employee, [
            'latitude' => -6.175390,
            'longitude' => 106.827150,
            'grace_period_minutes' => 15,
        ]);
        $this->assertEquals(0, $attOnTime->late_minutes);
        $this->assertEquals(Attendance::CLOCK_IN_ON_TIME, $attOnTime->clock_in_status);
        $attOnTime->delete();

        // 2. Clock in past grace period (08:35 WIB) -> Late by 35 minutes
        Carbon::setTestNow('2026-10-05 08:35:00');
        $attLate = $this->attendanceService->clockIn($this->business, $this->employee, [
            'latitude' => -6.175390,
            'longitude' => 106.827150,
        ]);
        $this->assertEquals(35, $attLate->late_minutes);
        $this->assertEquals(Attendance::CLOCK_IN_LATE, $attLate->clock_in_status);
        $this->assertEquals(Attendance::STATUS_LATE, $attLate->status);

        Carbon::setTestNow();
    }

    public function test_clock_out_calculates_work_duration_and_overtime(): void
    {
        Carbon::setTestNow('2026-10-05 08:00:00');
        $attendance = $this->attendanceService->clockIn($this->business, $this->employee, [
            'latitude' => -6.175390,
            'longitude' => 106.827150,
        ]);

        // Clock out at 18:30 WIB (9.5 hours work duration, 90 minutes overtime past 17:00)
        Carbon::setTestNow('2026-10-05 18:30:00');
        $updated = $this->attendanceService->clockOut($this->business, $this->employee, [
            'latitude' => -6.175390,
            'longitude' => 106.827150,
            'shift_end' => '17:00:00',
        ]);

        $this->assertEquals(630, $updated->work_duration_minutes); // 10.5 hours = 630 mins
        $this->assertEquals(150, $updated->overtime_minutes); // 630 - 480 standard mins = 150 mins
        $this->assertEquals(0, $updated->early_leave_minutes);

        Carbon::setTestNow();
    }

    public function test_attendance_correction_lifecycle_revision_and_anti_self_approval(): void
    {
        // 1. Employee submits correction
        $ticket = $this->attendanceService->createCorrectionTicket($this->business, $this->employee, [
            'target_date' => '2026-10-01',
            'correction_type' => AttendanceCorrection::TYPE_FULL_DAY,
            'proposed_clock_in' => '08:00',
            'proposed_clock_out' => '17:00',
            'proposed_status' => Attendance::STATUS_PRESENT,
            'reason' => 'Lupa presensi karena pemadaman listrik di cabang.',
        ]);

        $this->assertEquals(AttendanceCorrection::STATUS_PENDING, $ticket->status);

        // 2. Anti-Self Approval Security: Employee cannot approve their own ticket
        try {
            $this->attendanceService->approveCorrection($this->business, $ticket, $this->employee, 'Self approval attempt');
            $this->fail('Expected RuntimeException for self approval was not thrown.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('tiket perbaikan absensi milik Anda sendiri', $e->getMessage());
        }

        // 3. HR requests revision
        $revisedTicket = $this->attendanceService->requestRevision($this->business, $ticket, $this->hrManager, 'Lampirkan bukti foto kegiatan operasional.');
        $this->assertEquals(AttendanceCorrection::STATUS_REVISION, $revisedTicket->status);
        $this->assertEquals('Lampirkan bukti foto kegiatan operasional.', $revisedTicket->review_notes);

        // 4. Employee updates and resubmits
        $resubmittedTicket = $this->attendanceService->updateAndResubmitCorrection($this->business, $revisedTicket, $this->employee, [
            'reason' => 'Lupa presensi karena pemadaman listrik (surat keterangan PLN terlampir).',
        ]);
        $this->assertEquals(AttendanceCorrection::STATUS_PENDING, $resubmittedTicket->status);

        // 5. HR Approves ticket
        $approvedAttendance = $this->attendanceService->approveCorrection($this->business, $resubmittedTicket, $this->hrManager, 'Disetujui setelah diverifikasi.');
        $this->assertInstanceOf(Attendance::class, $approvedAttendance);
        $this->assertEquals(AttendanceCorrection::STATUS_APPROVED, $resubmittedTicket->fresh()->status);

        // 6. Verify Attendance record created/synced atomically
        $attendance = Attendance::where('business_id', $this->business->id)
            ->where('user_id', $this->employee->id)
            ->whereDate('date', '2026-10-01')
            ->first();

        $this->assertNotNull($attendance);
        $this->assertEquals('08:00', $attendance->clock_in_at->format('H:i'));
        $this->assertEquals('17:00', $attendance->clock_out_at->format('H:i'));
        $this->assertEquals(540, $attendance->work_duration_minutes);
        $this->assertEquals(Attendance::STATUS_PRESENT, $attendance->status);
    }
}
