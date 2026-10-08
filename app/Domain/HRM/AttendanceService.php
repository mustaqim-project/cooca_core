<?php

declare(strict_types=1);

namespace App\Domain\HRM;

use App\Domain\HRM\Biometrics\FaceVerificationService;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\Location;
use App\Models\User;
use App\Support\TimezoneHelper;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

final class AttendanceService
{
    public const MAX_GPS_ACCURACY_METERS = 250.0;

    public function __construct(
        private readonly FaceVerificationService $faceService = new FaceVerificationService(),
        private readonly AttendanceExceptionService $exceptionService = new AttendanceExceptionService(),
        private readonly WorkScheduleService $scheduleService = new WorkScheduleService()
    ) {}

    /**
     * Calculate geodesic distance between two points using the Haversine formula (in meters).
     */
    public function calculateDistanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): int
    {
        $earthRadius = 6371000; // meters

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return (int) round($earthRadius * $c);
    }

    /**
     * Record daily clock-in with geofence validation, biometric face verification, and anti-spoofing checks.
     *
     * @param Business $business
     * @param User $user
     * @param array<string, mixed> $data
     * @return Attendance
     * @throws ValidationException
     */
    public function clockIn(Business $business, User $user, array $data): Attendance
    {
        $today = $data['date'] ?? now()->toDateString();

        // 1. Anti-spoofing GPS accuracy verification
        $accuracy = isset($data['accuracy']) ? (float) $data['accuracy'] : null;
        if ($accuracy !== null && $accuracy > self::MAX_GPS_ACCURACY_METERS) {
            $maxAcc = (int) self::MAX_GPS_ACCURACY_METERS;
            throw ValidationException::withMessages([
                'gps' => "Akurasi sinyal GPS perangkat Anda terlalu rendah ({$accuracy}m > {$maxAcc}m) atau terdeteksi sinyal simulasi/palsu. Pastikan GPS aktif dalam mode akurasi tinggi.",
            ]);
        }

        // 2. Biometric Face Verification (if provided or enforced)
        $faceVerified = false;
        $faceScore = null;
        $faceData = $data['face_data'] ?? $data['face_embedding'] ?? $data['photo'] ?? null;

        if (! empty($faceData)) {
            $membershipForFace = BusinessMembership::where('business_id', $business->id)
                ->where('user_id', $user->id)
                ->first();

            // Auto-enroll initial face template on first clock-in if employee hasn't registered yet
            if ($membershipForFace && empty($membershipForFace->face_biometric_template)) {
                try {
                    $this->faceService->registerFaceTemplate($business, $user, $faceData);
                    $faceVerified = true;
                    $faceScore = 1.0;
                } catch (\Throwable $e) {
                    Log::warning("Auto-enrollment face on clockIn failed for user {$user->id}: " . $e->getMessage());
                }
            } else {
                $threshold = (float) ($data['face_threshold'] ?? FaceVerificationService::DEFAULT_SIMILARITY_THRESHOLD);
                $verificationResult = $this->faceService->verifyFace($business, $user, $faceData, $threshold);

                if (! $verificationResult['verified']) {
                    throw ValidationException::withMessages([
                        'face' => $verificationResult['message'],
                    ]);
                }

                $faceVerified = true;
                $faceScore = $verificationResult['similarity'];
            }
        }

        // 3. Resolve Location Policy & Exception Engine
        $membership = BusinessMembership::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->first();

        $attendanceMode = $membership?->attendance_mode ?? BusinessMembership::ATTENDANCE_MODE_GEOFENCED;
        $isFreeLocation = $attendanceMode === BusinessMembership::ATTENDANCE_MODE_FREE;

        $userLat = isset($data['latitude']) && $data['latitude'] !== '' && $data['latitude'] !== null ? (float) $data['latitude'] : null;
        $userLng = isset($data['longitude']) && $data['longitude'] !== '' && $data['longitude'] !== null ? (float) $data['longitude'] : null;

        $targetLocation = null;
        $calculatedDistance = null;
        $exceptionPolicyId = null;
        $clockInStatus = Attendance::CLOCK_IN_ON_TIME;

        // Check if employee has active exception policy (WFH, Field Work, Business Trip, etc.)
        $exceptionEval = $this->exceptionService->evaluateLocationException($business, $user, $userLat, $userLng, 0, now('Asia/Jakarta'));
        if ($exceptionEval['is_exception']) {
            if (! $exceptionEval['allowed']) {
                throw ValidationException::withMessages([
                    'location' => $exceptionEval['reason'],
                ]);
            }
            $isFreeLocation = true;
            $exceptionPolicyId = $exceptionEval['exception']->id;
            $clockInStatus = Attendance::CLOCK_IN_FREE_LOCATION;
        }

        $targetLocationId = $data['location_id'] ?? $membership?->primary_location_id;
        if ($targetLocationId) {
            $targetLocation = Location::where('business_id', $business->id)->where('id', $targetLocationId)->first();
        }

        if (! $targetLocation) {
            $targetLocation = Location::where('business_id', $business->id)->where('is_primary', true)->first()
                ?? Location::where('business_id', $business->id)->first();
        }

        if ($isFreeLocation && ! $exceptionPolicyId) {
            $clockInStatus = Attendance::CLOCK_IN_FREE_LOCATION;
        } elseif (! $isFreeLocation) {
            // Mode Geofenced: If target location has coordinates configured, verify distance
            if ($targetLocation && $targetLocation->latitude !== null && $targetLocation->longitude !== null) {
                if ($userLat === null || $userLng === null) {
                    throw ValidationException::withMessages([
                        'location' => "Izin lokasi GPS wajib diaktifkan untuk presensi kantor ({$targetLocation->name}). Silakan izinkan akses lokasi pada browser/aplikasi Anda.",
                    ]);
                }

                $calculatedDistance = $this->calculateDistanceMeters(
                    $userLat,
                    $userLng,
                    (float) $targetLocation->latitude,
                    (float) $targetLocation->longitude
                );

                $allowedRadius = $targetLocation->geofence_radius_meters ?: 50;

                if ($calculatedDistance > $allowedRadius) {
                    throw ValidationException::withMessages([
                        'location' => "Presensi ditolak: Anda berada di luar radius kantor \"{$targetLocation->name}\". Jarak Anda saat ini: {$calculatedDistance} meter (Batas maksimal: {$allowedRadius} meter). Silakan mendekat ke lokasi kantor.",
                    ]);
                }
            }
        }

        // 4. Resolve Work Shift Schedule & Multi-Timezone Lateness
        $currentTime = now();
        $targetLocationTz = TimezoneHelper::resolve($business, $targetLocation);
        $localNow = $currentTime->copy()->setTimezone($targetLocationTz);

        $workShiftId = null;
        $shiftName = null;
        $scheduledStartAt = null;
        $scheduledEndAt = null;
        $earlyInMinutes = 0;
        $baseDate = $today;

        if (! empty($data['shift_start'])) {
            // Explicit backward-compatible override (e.g. testing)
            $shiftStartStr = (string) $data['shift_start'];
            $graceMinutes = (int) ($data['grace_period_minutes'] ?? 0);
            $scheduledStart = Carbon::parse($today . ' ' . $shiftStartStr, $targetLocationTz);
            $scheduledStartWithGrace = $scheduledStart->copy()->addMinutes($graceMinutes);

            $lateMinutes = 0;
            if ($localNow->gt($scheduledStartWithGrace)) {
                $lateMinutes = (int) round(abs($localNow->diffInMinutes($scheduledStart)));
                if (! $isFreeLocation) {
                    $clockInStatus = Attendance::CLOCK_IN_LATE;
                }
            }
            $overallStatus = $lateMinutes > 0 ? Attendance::STATUS_LATE : Attendance::STATUS_PRESENT;
            $scheduledStartAt = $scheduledStart->utc();
            $shiftName = "Shift {$shiftStartStr}";
        } else {
            // Intelligent Schedule Resolution (Roster, Weekly, Default Shift, Operating Hours)
            $scheduleInfo = $this->scheduleService->resolveActiveShift($business, $user, $targetLocation, $currentTime);

            if (! empty($scheduleInfo['is_off_day'])) {
                throw ValidationException::withMessages([
                    'attendance' => 'Hari ini adalah hari libur (OFF) sesuai jadwal kerja Anda. Presensi masuk tidak dapat dicatat pada hari libur.',
                ]);
            }

            $eval = $this->scheduleService->evaluateClockIn($scheduleInfo, $localNow);
            $lateMinutes = (int) $eval['late_minutes'];
            $earlyInMinutes = (int) $eval['early_in_minutes'];

            if (! $isFreeLocation) {
                $clockInStatus = $eval['clock_in_status'] === 'late' ? Attendance::CLOCK_IN_LATE : Attendance::CLOCK_IN_ON_TIME;
            }
            $overallStatus = $eval['overall_status'];
            $workShiftId = $scheduleInfo['work_shift_id'] ?? null;
            $shiftName = $scheduleInfo['shift_name'] ?? null;
            $scheduledStartAt = isset($scheduleInfo['scheduled_start']) && $scheduleInfo['scheduled_start'] instanceof Carbon
                ? $scheduleInfo['scheduled_start']->copy()->utc()
                : null;
            $scheduledEndAt = isset($scheduleInfo['scheduled_end']) && $scheduleInfo['scheduled_end'] instanceof Carbon
                ? $scheduleInfo['scheduled_end']->copy()->utc()
                : null;
            $baseDate = $scheduleInfo['base_date'] ?? $today;
        }

        // 5. Atomic Persist with Concurrency & Race Condition Lock
        return DB::transaction(function () use (
            $business,
            $user,
            $baseDate,
            $targetLocation,
            $currentTime,
            $userLat,
            $userLng,
            $calculatedDistance,
            $data,
            $clockInStatus,
            $lateMinutes,
            $earlyInMinutes,
            $overallStatus,
            $isFreeLocation,
            $faceVerified,
            $faceScore,
            $exceptionPolicyId,
            $workShiftId,
            $shiftName,
            $scheduledStartAt,
            $scheduledEndAt,
            $targetLocationTz
        ): Attendance {
            // Check for existing open or today's attendance
            $attendance = Attendance::where('business_id', $business->id)
                ->where('user_id', $user->id)
                ->where(function ($q) use ($baseDate) {
                    $q->whereDate('date', $baseDate)
                        ->orWhere(function ($oq) {
                            $oq->whereNotNull('clock_in_at')->whereNull('clock_out_at');
                        });
                })
                ->lockForUpdate()
                ->first();

            if ($attendance && $attendance->clock_in_at !== null) {
                $clockInLocal = $attendance->clock_in_at->copy()->setTimezone($attendance->timezone ?: $targetLocationTz);
                $tzAbbr = TimezoneHelper::abbreviation($attendance->timezone ?: $targetLocationTz);
                throw ValidationException::withMessages([
                    'attendance' => "Anda sudah melakukan presensi masuk (clock-in) untuk sesi ini pada jam {$clockInLocal->format('H:i')} {$tzAbbr}.",
                ]);
            }

            if (! $attendance) {
                $attendance = new Attendance([
                    'id' => (string) Str::uuid(),
                    'business_id' => $business->id,
                    'user_id' => $user->id,
                    'date' => $baseDate,
                ]);
            }

            $attendance->location_id = $targetLocation?->id;
            $attendance->work_shift_id = $workShiftId;
            $attendance->shift_name = $shiftName;
            $attendance->scheduled_start_at = $scheduledStartAt;
            $attendance->scheduled_end_at = $scheduledEndAt;
            $attendance->clock_in_at = $currentTime;
            $attendance->clock_in_lat = $userLat;
            $attendance->clock_in_lng = $userLng;
            $attendance->clock_in_distance_meters = $calculatedDistance;
            $attendance->clock_in_address = $data['address'] ?? null;
            $attendance->clock_in_photo = null; // Zero permanent photo retention for privacy compliance
            $attendance->clock_in_status = $clockInStatus;
            $attendance->clock_in_notes = $data['notes'] ?? null;
            $attendance->early_in_minutes = $earlyInMinutes;
            $attendance->late_minutes = $lateMinutes;
            $attendance->timezone = $targetLocationTz;
            $attendance->status = $overallStatus;
            $attendance->is_geofenced = ! $isFreeLocation;
            $attendance->face_verified = $faceVerified;
            $attendance->face_similarity_score = $faceScore;
            $attendance->exception_policy_id = $exceptionPolicyId;
            $attendance->save();

            // Immutable Audit Log
            $localTimeFormatted = $currentTime->copy()->setTimezone($targetLocationTz)->format('H:i:s');
            $tzAbbr = TimezoneHelper::abbreviation($targetLocationTz);

            AuditLog::create([
                'id' => (string) Str::uuid(),
                'business_id' => $business->id,
                'user_id' => $user->id,
                'auditable_type' => Attendance::class,
                'auditable_id' => $attendance->id,
                'action' => 'attendance.clock_in',
                'risk_level' => AuditLog::RISK_LOW,
                'risk_reason' => 'Pencatatan presensi masuk kerja harian.',
                'notes' => "Presensi masuk oleh {$user->name} jam {$localTimeFormatted} {$tzAbbr} ({$clockInStatus}, shift: " . ($shiftName ?: 'Umum') . "). Jarak: " . ($calculatedDistance !== null ? "{$calculatedDistance}m" : 'Bebas') . ($faceVerified ? ", Wajah Terverifikasi ({$faceScore})" : ''),
                'new_values' => [
                    'date' => $baseDate,
                    'clock_in_at' => $currentTime->toIso8601String(),
                    'status' => $overallStatus,
                    'shift' => $shiftName,
                    'late_minutes' => $lateMinutes,
                    'early_in_minutes' => $earlyInMinutes,
                    'distance_meters' => $calculatedDistance,
                    'face_verified' => $faceVerified,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);

            return $attendance;
        });
    }

    /**
     * Record daily clock-out and calculate work duration based on authoritative server clock.
     *
     * @param Business $business
     * @param User $user
     * @param array<string, mixed> $data
     * @return Attendance
     * @throws ValidationException
     */
    public function clockOut(Business $business, User $user, array $data): Attendance
    {
        $today = $data['date'] ?? now()->toDateString();

        // Anti-spoofing accuracy verification
        $accuracy = isset($data['accuracy']) ? (float) $data['accuracy'] : null;
        if ($accuracy !== null && $accuracy > self::MAX_GPS_ACCURACY_METERS) {
            $maxAcc = (int) self::MAX_GPS_ACCURACY_METERS;
            throw ValidationException::withMessages([
                'gps' => "Akurasi sinyal GPS perangkat Anda terlalu rendah ({$accuracy}m > {$maxAcc}m). Pastikan GPS aktif dalam mode akurasi tinggi.",
            ]);
        }

        return DB::transaction(function () use ($business, $user, $today, $data): Attendance {
            // First check for active uncompleted attendance session (supports overnight cross-day shifts)
            $attendance = Attendance::where('business_id', $business->id)
                ->where('user_id', $user->id)
                ->whereNotNull('clock_in_at')
                ->whereNull('clock_out_at')
                ->orderByDesc('clock_in_at')
                ->lockForUpdate()
                ->first();

            if (! $attendance) {
                // Fallback to today's date
                $attendance = Attendance::where('business_id', $business->id)
                    ->where('user_id', $user->id)
                    ->whereDate('date', $today)
                    ->lockForUpdate()
                    ->first();
            }

            if (! $attendance || $attendance->clock_in_at === null) {
                throw ValidationException::withMessages([
                    'attendance' => 'Belum ada catatan presensi masuk (clock-in) yang aktif. Silakan lakukan clock-in terlebih dahulu atau ajukan tiket koreksi.',
                ]);
            }

            $effectiveTz = $attendance->timezone ?: TimezoneHelper::resolve($business, $attendance->location);
            $tzAbbr = TimezoneHelper::abbreviation($effectiveTz);

            if ($attendance->clock_out_at !== null) {
                $clockOutLocal = $attendance->clock_out_at->copy()->setTimezone($effectiveTz);
                throw ValidationException::withMessages([
                    'attendance' => "Anda sudah melakukan presensi pulang (clock-out) untuk sesi ini pada jam {$clockOutLocal->format('H:i')} {$tzAbbr}.",
                ]);
            }

            // Biometric Face Verification on clock-out (if provided)
            $faceVerified = $attendance->face_verified;
            $faceScore = $attendance->face_similarity_score;
            $faceData = $data['face_data'] ?? $data['face_embedding'] ?? $data['photo'] ?? null;

            if (! empty($faceData)) {
                $membershipForFace = BusinessMembership::where('business_id', $business->id)
                    ->where('user_id', $user->id)
                    ->first();

                if ($membershipForFace && empty($membershipForFace->face_biometric_template)) {
                    try {
                        $this->faceService->registerFaceTemplate($business, $user, $faceData);
                        $faceVerified = true;
                        $faceScore = 1.0;
                    } catch (\Throwable $e) {
                        Log::warning("Auto-enrollment face on clockOut failed for user {$user->id}: " . $e->getMessage());
                    }
                } else {
                    $threshold = (float) ($data['face_threshold'] ?? FaceVerificationService::DEFAULT_SIMILARITY_THRESHOLD);
                    $verificationResult = $this->faceService->verifyFace($business, $user, $faceData, $threshold);

                    if (! $verificationResult['verified']) {
                        throw ValidationException::withMessages([
                            'face' => $verificationResult['message'],
                        ]);
                    }
                    $faceVerified = true;
                    $faceScore = $verificationResult['similarity'];
                }
            }

            $membership = BusinessMembership::where('business_id', $business->id)
                ->where('user_id', $user->id)
                ->first();

            $attendanceMode = $membership?->attendance_mode ?? BusinessMembership::ATTENDANCE_MODE_GEOFENCED;
            $isFreeLocation = $attendanceMode === BusinessMembership::ATTENDANCE_MODE_FREE || $attendance->exception_policy_id !== null;

            $userLat = isset($data['latitude']) && $data['latitude'] !== '' && $data['latitude'] !== null ? (float) $data['latitude'] : null;
            $userLng = isset($data['longitude']) && $data['longitude'] !== '' && $data['longitude'] !== null ? (float) $data['longitude'] : null;

            $calculatedDistance = null;
            $targetLocation = $attendance->location;

            if (! $isFreeLocation && $targetLocation && $targetLocation->latitude !== null && $targetLocation->longitude !== null) {
                if ($userLat !== null && $userLng !== null) {
                    $calculatedDistance = $this->calculateDistanceMeters(
                        $userLat,
                        $userLng,
                        (float) $targetLocation->latitude,
                        (float) $targetLocation->longitude
                    );

                    $allowedRadius = $targetLocation->geofence_radius_meters ?: 50;
                    if ($calculatedDistance > $allowedRadius) {
                        throw ValidationException::withMessages([
                            'location' => "Presensi pulang ditolak: Anda berada di luar radius kantor \"{$targetLocation->name}\" ({$calculatedDistance}m > {$allowedRadius}m).",
                        ]);
                    }
                }
            }

            $clockOutTime = now();
            $clockInTime = $attendance->clock_in_at;
            $workDurationMinutes = (int) round(abs($clockOutTime->diffInMinutes($clockInTime)));

            // Calculate early leave and overtime against scheduled end
            $scheduledEnd = null;
            if ($attendance->scheduled_end_at !== null) {
                $scheduledEnd = $attendance->scheduled_end_at->copy()->setTimezone($effectiveTz);
            } elseif (! empty($data['shift_end'])) {
                $scheduledEnd = Carbon::parse($today . ' ' . $data['shift_end'], $effectiveTz);
            }

            $earlyLeaveMinutes = 0;
            $overtimeMinutes = 0;
            $clockOutLocal = $clockOutTime->copy()->setTimezone($effectiveTz);

            if ($scheduledEnd instanceof Carbon) {
                if ($clockOutLocal->lt($scheduledEnd)) {
                    $earlyLeaveMinutes = (int) round(abs($scheduledEnd->diffInMinutes($clockOutLocal)));
                } elseif ($clockOutLocal->gt($scheduledEnd)) {
                    $overtimeMinutes = (int) round(abs($clockOutLocal->diffInMinutes($scheduledEnd)));
                }
            } else {
                $standardWorkMinutes = (int) ($data['standard_work_minutes'] ?? 480);
                if ($workDurationMinutes > $standardWorkMinutes) {
                    $overtimeMinutes = $workDurationMinutes - $standardWorkMinutes;
                }
            }

            $clockOutStatus = Attendance::CLOCK_OUT_NORMAL;
            if ($isFreeLocation) {
                $clockOutStatus = Attendance::CLOCK_OUT_FREE_LOCATION;
            } elseif ($overtimeMinutes >= 30) {
                $clockOutStatus = Attendance::CLOCK_OUT_OVERTIME;
            } elseif ($earlyLeaveMinutes > 0) {
                $clockOutStatus = Attendance::CLOCK_OUT_EARLY;
            }

            $attendance->clock_out_at = $clockOutTime;
            $attendance->clock_out_lat = $userLat;
            $attendance->clock_out_lng = $userLng;
            $attendance->clock_out_distance_meters = $calculatedDistance;
            $attendance->clock_out_address = $data['address'] ?? null;
            $attendance->clock_out_photo = null; // Zero permanent photo retention
            $attendance->clock_out_status = $clockOutStatus;
            $attendance->clock_out_notes = $data['notes'] ?? null;
            $attendance->work_duration_minutes = $workDurationMinutes;
            $attendance->early_leave_minutes = $earlyLeaveMinutes;
            $attendance->overtime_minutes = $overtimeMinutes;
            $attendance->face_verified = $faceVerified;
            $attendance->face_similarity_score = $faceScore;
            $attendance->save();

            // Immutable Audit Log
            AuditLog::create([
                'id' => (string) Str::uuid(),
                'business_id' => $business->id,
                'user_id' => $user->id,
                'auditable_type' => Attendance::class,
                'auditable_id' => $attendance->id,
                'action' => 'attendance.clock_out',
                'risk_level' => AuditLog::RISK_LOW,
                'risk_reason' => 'Pencatatan presensi pulang kerja harian.',
                'notes' => "Presensi pulang oleh {$user->name} jam {$clockOutLocal->format('H:i:s')} {$tzAbbr} ({$clockOutStatus}, durasi {$workDurationMinutes} mnt).",
                'new_values' => [
                    'date' => $attendance->date?->toDateString() ?: $today,
                    'clock_out_at' => $clockOutTime->toIso8601String(),
                    'work_duration_minutes' => $workDurationMinutes,
                    'overtime_minutes' => $overtimeMinutes,
                    'early_leave_minutes' => $earlyLeaveMinutes,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);

            return $attendance;
        });
    }

    /**
     * Submit an attendance correction ticket ("Ajukan Perbaikan Absen").
     *
     * @param Business $business
     * @param User $user
     * @param array<string, mixed> $data
     * @return AttendanceCorrection
     */
    public function createCorrectionTicket(Business $business, User $user, array $data): AttendanceCorrection
    {
        $targetDate = $data['target_date'];

        $existingAttendance = Attendance::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->whereDate('date', $targetDate)
            ->first();

        $correctionNumber = 'COR-' . date('Ym') . '-' . strtoupper(Str::random(5));

        $attachmentPath = null;
        if (isset($data['attachment']) && $data['attachment'] instanceof UploadedFile) {
            $attachmentPath = $data['attachment']->store('attendance/corrections', 'public');
        }

        return AttendanceCorrection::create([
            'id' => (string) Str::uuid(),
            'business_id' => $business->id,
            'user_id' => $user->id,
            'attendance_id' => $existingAttendance?->id,
            'correction_number' => $correctionNumber,
            'target_date' => $targetDate,
            'correction_type' => $data['correction_type'],
            'proposed_clock_in' => $data['proposed_clock_in'] ?? null,
            'proposed_clock_out' => $data['proposed_clock_out'] ?? null,
            'proposed_status' => $data['proposed_status'] ?? Attendance::STATUS_PRESENT,
            'reason' => $data['reason'],
            'attachment_path' => $attachmentPath,
            'status' => AttendanceCorrection::STATUS_PENDING,
        ]);
    }

    /**
     * Update and resubmit an attendance correction ticket after revision request.
     */
    public function updateAndResubmitCorrection(
        Business $business,
        AttendanceCorrection $ticket,
        User $user,
        array $data
    ): AttendanceCorrection {
        if ($ticket->business_id !== $business->id) {
            throw new RuntimeException('Tiket koreksi tidak sesuai dengan workspace bisnis aktif.');
        }

        if ($ticket->user_id !== $user->id) {
            throw new RuntimeException('Anda hanya dapat memperbarui tiket koreksi milik Anda sendiri.');
        }

        if (! in_array($ticket->status, [AttendanceCorrection::STATUS_PENDING, AttendanceCorrection::STATUS_REVISION], true)) {
            throw new RuntimeException("Tiket {$ticket->correction_number} berstatus {$ticket->status} dan tidak dapat diedit kembali.");
        }

        $attachmentPath = $ticket->attachment_path;
        if (isset($data['attachment']) && $data['attachment'] instanceof UploadedFile) {
            $attachmentPath = $data['attachment']->store('attendance/corrections', 'public');
        }

        $ticket->update([
            'correction_type' => $data['correction_type'] ?? $ticket->correction_type,
            'proposed_clock_in' => $data['proposed_clock_in'] ?? $ticket->proposed_clock_in,
            'proposed_clock_out' => $data['proposed_clock_out'] ?? $ticket->proposed_clock_out,
            'proposed_status' => $data['proposed_status'] ?? $ticket->proposed_status,
            'reason' => $data['reason'] ?? $ticket->reason,
            'attachment_path' => $attachmentPath,
            'status' => AttendanceCorrection::STATUS_PENDING, // Reset to pending for HR re-review
        ]);

        return $ticket;
    }

    /**
     * Approve an attendance correction ticket, apply changes to attendances table, and record audit log.
     *
     * @param Business $business
     * @param AttendanceCorrection $ticket
     * @param User $reviewer
     * @param string|null $notes
     * @return Attendance
     */
    public function approveCorrection(
        Business $business,
        AttendanceCorrection $ticket,
        User $reviewer,
        ?string $notes = null
    ): Attendance {
        if ($ticket->business_id !== $business->id) {
            throw new RuntimeException('Tiket koreksi tidak sesuai dengan workspace bisnis aktif.');
        }

        if ($ticket->user_id === $reviewer->id) {
            throw new RuntimeException('Otorisasi ditolak: Anda tidak diperbolehkan menyetujui tiket perbaikan absensi milik Anda sendiri.');
        }

        if (! in_array($ticket->status, [AttendanceCorrection::STATUS_PENDING, AttendanceCorrection::STATUS_REVISION], true)) {
            throw new RuntimeException("Tiket {$ticket->correction_number} sudah berstatus {$ticket->status} dan tidak dapat diubah kembali.");
        }

        return DB::transaction(function () use ($business, $ticket, $reviewer, $notes): Attendance {
            $targetDate = $ticket->target_date->toDateString();

            $attendance = Attendance::where('business_id', $business->id)
                ->where('user_id', $ticket->user_id)
                ->whereDate('date', $targetDate)
                ->first();

            $oldValues = $attendance ? [
                'clock_in_at' => $attendance->clock_in_at?->toIso8601String(),
                'clock_out_at' => $attendance->clock_out_at?->toIso8601String(),
                'work_duration_minutes' => $attendance->work_duration_minutes,
                'status' => $attendance->status,
            ] : null;

            if (! $attendance) {
                $attendance = new Attendance([
                    'id' => (string) Str::uuid(),
                    'business_id' => $business->id,
                    'user_id' => $ticket->user_id,
                    'date' => $targetDate,
                ]);
            }

            if ($ticket->proposed_clock_in) {
                $attendance->clock_in_at = Carbon::parse($targetDate . ' ' . $ticket->proposed_clock_in);
                $attendance->clock_in_status = Attendance::CLOCK_IN_ON_TIME;
            }

            if ($ticket->proposed_clock_out) {
                $attendance->clock_out_at = Carbon::parse($targetDate . ' ' . $ticket->proposed_clock_out);
                $attendance->clock_out_status = Attendance::CLOCK_OUT_NORMAL;
            }

            if ($attendance->clock_in_at && $attendance->clock_out_at) {
                $duration = (int) $attendance->clock_in_at->diffInMinutes($attendance->clock_out_at);
                $attendance->work_duration_minutes = $duration;
                $attendance->overtime_minutes = max(0, $duration - 480);
                $attendance->early_leave_minutes = 0;
                $attendance->late_minutes = 0;
            }

            $attendance->status = $ticket->proposed_status ?: Attendance::STATUS_PRESENT;
            $attendance->is_corrected = true;
            $attendance->attendance_correction_id = $ticket->id;
            $attendance->save();

            // Update ticket
            $ticket->update([
                'status' => AttendanceCorrection::STATUS_APPROVED,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'review_notes' => $notes,
                'attendance_id' => $attendance->id,
            ]);

            // Audit log
            AuditLog::create([
                'id' => (string) Str::uuid(),
                'business_id' => $business->id,
                'user_id' => $reviewer->id,
                'auditable_type' => AttendanceCorrection::class,
                'auditable_id' => $ticket->id,
                'action' => 'attendance.correction_approved',
                'risk_level' => AuditLog::RISK_MEDIUM,
                'risk_reason' => 'Perubahan data kehadiran resmi karyawan melalui tiket koreksi.',
                'notes' => "Persetujuan koreksi presensi {$ticket->correction_number} untuk {$ticket->user->name} tanggal {$targetDate}.",
                'old_values' => $oldValues,
                'new_values' => [
                    'clock_in_at' => $attendance->clock_in_at?->toIso8601String(),
                    'clock_out_at' => $attendance->clock_out_at?->toIso8601String(),
                    'work_duration_minutes' => $attendance->work_duration_minutes,
                    'status' => $attendance->status,
                    'is_corrected' => true,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);

            return $attendance;
        });
    }

    /**
     * Request a revision on an attendance correction ticket.
     */
    public function requestRevision(
        Business $business,
        AttendanceCorrection $ticket,
        User $reviewer,
        string $notes
    ): AttendanceCorrection {
        if ($ticket->business_id !== $business->id) {
            throw new RuntimeException('Tiket koreksi tidak sesuai dengan workspace bisnis aktif.');
        }

        if ($ticket->user_id === $reviewer->id) {
            throw new RuntimeException('Otorisasi ditolak: Anda tidak diperbolehkan meminta revisi pada tiket Anda sendiri.');
        }

        if (! $ticket->isPending()) {
            throw new RuntimeException("Tiket {$ticket->correction_number} sudah berstatus {$ticket->status} dan tidak dapat diminta revisi.");
        }

        $ticket->update([
            'status' => AttendanceCorrection::STATUS_REVISION,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        AuditLog::create([
            'id' => (string) Str::uuid(),
            'business_id' => $business->id,
            'user_id' => $reviewer->id,
            'auditable_type' => AttendanceCorrection::class,
            'auditable_id' => $ticket->id,
            'action' => 'attendance.correction_revision_requested',
            'risk_level' => AuditLog::RISK_LOW,
            'risk_reason' => 'Permintaan revisi berkas/keterangan tiket perbaikan absensi.',
            'notes' => "Permintaan revisi tiket koreksi presensi {$ticket->correction_number} untuk {$ticket->user->name}. Catatan: {$notes}",
            'old_values' => ['status' => AttendanceCorrection::STATUS_PENDING],
            'new_values' => ['status' => AttendanceCorrection::STATUS_REVISION, 'notes' => $notes],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return $ticket;
    }

    /**
     * Reject an attendance correction ticket.
     *
     * @param Business $business
     * @param AttendanceCorrection $ticket
     * @param User $reviewer
     * @param string $reason
     * @return AttendanceCorrection
     */
    public function rejectCorrection(
        Business $business,
        AttendanceCorrection $ticket,
        User $reviewer,
        string $reason
    ): AttendanceCorrection {
        if ($ticket->business_id !== $business->id) {
            throw new RuntimeException('Tiket koreksi tidak sesuai dengan workspace bisnis aktif.');
        }

        if ($ticket->user_id === $reviewer->id) {
            throw new RuntimeException('Otorisasi ditolak: Anda tidak diperbolehkan menolak tiket perbaikan absensi milik Anda sendiri.');
        }

        if (! in_array($ticket->status, [AttendanceCorrection::STATUS_PENDING, AttendanceCorrection::STATUS_REVISION], true)) {
            throw new RuntimeException("Tiket {$ticket->correction_number} sudah berstatus {$ticket->status} dan tidak dapat diubah kembali.");
        }

        $ticket->update([
            'status' => AttendanceCorrection::STATUS_REJECTED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_notes' => $reason,
        ]);

        AuditLog::create([
            'id' => (string) Str::uuid(),
            'business_id' => $business->id,
            'user_id' => $reviewer->id,
            'auditable_type' => AttendanceCorrection::class,
            'auditable_id' => $ticket->id,
            'action' => 'attendance.correction_rejected',
            'risk_level' => AuditLog::RISK_LOW,
            'risk_reason' => 'Penolakan tiket perbaikan absensi karyawan.',
            'notes' => "Penolakan tiket koreksi presensi {$ticket->correction_number} untuk {$ticket->user->name}. Alasan: {$reason}",
            'old_values' => ['status' => AttendanceCorrection::STATUS_PENDING],
            'new_values' => ['status' => AttendanceCorrection::STATUS_REJECTED, 'reason' => $reason],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return $ticket;
    }

    /**
     * Build rich, human-readable feedback for clock-in (status, delay, scheduled hours, timezone).
     *
     * @return array<string, mixed>
     */
    public function buildClockInFeedback(Attendance $attendance): array
    {
        $effectiveTz = $attendance->timezone ?: TimezoneHelper::DEFAULT_TIMEZONE;
        $tzAbbr = TimezoneHelper::abbreviation($effectiveTz);
        $clockInLocal = $attendance->clock_in_at?->copy()->setTimezone($effectiveTz);
        $clockInTimeStr = $clockInLocal ? $clockInLocal->format('H:i') : '-';

        $scheduledStartLocal = $attendance->scheduled_start_at?->copy()->setTimezone($effectiveTz);
        $scheduledStartTimeStr = $scheduledStartLocal ? $scheduledStartLocal->format('H:i') : null;

        $isLate = $attendance->late_minutes > 0 || $attendance->clock_in_status === Attendance::CLOCK_IN_LATE;
        $isEarly = (int) $attendance->early_in_minutes > 0;

        if ($isLate) {
            $statusLabel = 'TERLAMBAT';
            $statusColor = 'red';
            $statusDetail = "Terlambat {$attendance->late_minutes} menit";
            $message = "Presensi masuk berhasil dicatat pukul {$clockInTimeStr} {$tzAbbr}. Status: TERLAMBAT {$attendance->late_minutes} menit"
                . ($scheduledStartTimeStr ? " (Jadwal masuk: {$scheduledStartTimeStr} {$tzAbbr}" . ($attendance->shift_name ? ", Shift: {$attendance->shift_name}" : '') . ')' : '');
        } elseif ($isEarly) {
            $statusLabel = 'LEBIH AWAL';
            $statusColor = 'blue';
            $statusDetail = "Lebih awal {$attendance->early_in_minutes} menit";
            $message = "Presensi masuk berhasil dicatat pukul {$clockInTimeStr} {$tzAbbr}. Status: TEPAT WAKTU (Lebih awal {$attendance->early_in_minutes} menit"
                . ($scheduledStartTimeStr ? ", Jadwal: {$scheduledStartTimeStr} {$tzAbbr}" : '') . ')';
        } elseif ($attendance->scheduled_start_at) {
            $statusLabel = 'TEPAT WAKTU';
            $statusColor = 'green';
            $statusDetail = 'Tepat waktu sesuai jadwal';
            $message = "Presensi masuk berhasil dicatat pukul {$clockInTimeStr} {$tzAbbr}. Status: TEPAT WAKTU"
                . ($scheduledStartTimeStr ? " (Jadwal masuk: {$scheduledStartTimeStr} {$tzAbbr}" . ($attendance->shift_name ? ", Shift: {$attendance->shift_name}" : '') . ')' : '');
        } else {
            $statusLabel = 'BEBAS JADWAL';
            $statusColor = 'gray';
            $statusDetail = 'Bebas jadwal kerja';
            $message = "Presensi masuk berhasil dicatat pukul {$clockInTimeStr} {$tzAbbr}. Status: TEPAT WAKTU (Bebas Jadwal)";
        }

        return [
            'status' => $isLate ? 'late' : ($isEarly ? 'early' : 'on_time'),
            'badge_variant' => $isLate ? 'danger' : ($isEarly ? 'info' : 'success'),
            'clock_in_time' => $clockInTimeStr,
            'actual_clock_in_time' => $clockInTimeStr,
            'timezone' => $effectiveTz,
            'timezone_abbr' => $tzAbbr,
            'scheduled_start_time' => $scheduledStartTimeStr,
            'shift_name' => $attendance->shift_name,
            'status_label' => $statusLabel,
            'status_color' => $statusColor,
            'status_detail' => $statusDetail,
            'late_minutes' => (int) $attendance->late_minutes,
            'early_in_minutes' => (int) $attendance->early_in_minutes,
            'is_late' => $isLate,
            'message' => $message,
        ];
    }

    /**
     * Build rich, human-readable feedback for clock-out (status, early leave, overtime, work duration).
     *
     * @return array<string, mixed>
     */
    public function buildClockOutFeedback(Attendance $attendance): array
    {
        $effectiveTz = $attendance->timezone ?: TimezoneHelper::DEFAULT_TIMEZONE;
        $tzAbbr = TimezoneHelper::abbreviation($effectiveTz);
        $clockOutLocal = $attendance->clock_out_at?->copy()->setTimezone($effectiveTz);
        $clockOutTimeStr = $clockOutLocal ? $clockOutLocal->format('H:i') : '-';

        $scheduledEndLocal = $attendance->scheduled_end_at?->copy()->setTimezone($effectiveTz);
        $scheduledEndTimeStr = $scheduledEndLocal ? $scheduledEndLocal->format('H:i') : null;

        $isEarlyLeave = (int) $attendance->early_leave_minutes > 0;
        $isOvertime = (int) $attendance->overtime_minutes > 0;

        $durationFormatted = $attendance->formatted_work_duration;

        if ($isEarlyLeave) {
            $statusLabel = 'PULANG CEPAT';
            $statusColor = 'amber';
            $statusDetail = "Pulang lebih awal {$attendance->early_leave_minutes} menit";
            $message = "Presensi pulang berhasil dicatat pukul {$clockOutTimeStr} {$tzAbbr}. Pulang lebih awal {$attendance->early_leave_minutes} menit"
                . ($scheduledEndTimeStr ? " (Jadwal pulang: {$scheduledEndTimeStr} {$tzAbbr}, Durasi: {$durationFormatted})" : " (Durasi: {$durationFormatted})");
        } elseif ($isOvertime) {
            $hours = intdiv((int) $attendance->overtime_minutes, 60);
            $mins = (int) $attendance->overtime_minutes % 60;
            $otStr = $hours > 0 ? "{$hours} jam " . ($mins > 0 ? "{$mins} mnt" : '') : "{$mins} menit";
            $statusLabel = 'LEMBUR';
            $statusColor = 'indigo';
            $statusDetail = "Lembur {$otStr}";
            $message = "Presensi pulang berhasil dicatat pukul {$clockOutTimeStr} {$tzAbbr}. Lembur {$otStr}"
                . ($scheduledEndTimeStr ? " (Jadwal selesai: {$scheduledEndTimeStr} {$tzAbbr}, Total durasi: {$durationFormatted})" : " (Total durasi: {$durationFormatted})");
        } else {
            $statusLabel = 'SESUAI JADWAL';
            $statusColor = 'green';
            $statusDetail = 'Selesai sesuai jadwal';
            $message = "Presensi pulang berhasil dicatat pukul {$clockOutTimeStr} {$tzAbbr}. Sesuai jadwal kerja (Durasi: {$durationFormatted}).";
        }

        return [
            'clock_out_time' => $clockOutTimeStr,
            'timezone' => $effectiveTz,
            'timezone_abbr' => $tzAbbr,
            'scheduled_end_time' => $scheduledEndTimeStr,
            'status_label' => $statusLabel,
            'status_color' => $statusColor,
            'status_detail' => $statusDetail,
            'work_duration_minutes' => (int) $attendance->work_duration_minutes,
            'formatted_duration' => $durationFormatted,
            'early_leave_minutes' => (int) $attendance->early_leave_minutes,
            'overtime_minutes' => (int) $attendance->overtime_minutes,
            'message' => $message,
        ];
    }
}
