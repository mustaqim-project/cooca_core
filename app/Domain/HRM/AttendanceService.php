<?php

declare(strict_types=1);

namespace App\Domain\HRM;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\Location;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

final class AttendanceService
{
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
     * Record daily clock-in with geofence validation and anti-spoofing verification.
     *
     * @param Business $business
     * @param User $user
     * @param array<string, mixed> $data
     * @return Attendance
     * @throws ValidationException
     */
    public function clockIn(Business $business, User $user, array $data): Attendance
    {
        $today = now()->toDateString();

        // 1. Check existing attendance today
        $attendance = Attendance::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($attendance && $attendance->clock_in_at !== null) {
            throw ValidationException::withMessages([
                'attendance' => 'Anda sudah melakukan presensi masuk (clock-in) untuk hari ini pada jam ' . $attendance->clock_in_at->format('H:i') . ' WIB.',
            ]);
        }

        // 2. Anti-spoofing accuracy verification
        $accuracy = isset($data['accuracy']) ? (float) $data['accuracy'] : null;
        if ($accuracy !== null && $accuracy > 100.0) {
            throw ValidationException::withMessages([
                'gps' => "Akurasi sinyal GPS perangkat Anda terlalu rendah ({$accuracy}m > 100m) atau terdeteksi sinyal simulasi/palsu. Pastikan GPS aktif dalam mode akurasi tinggi.",
            ]);
        }

        // 3. Dual Location Policy check
        $membership = BusinessMembership::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->first();

        $attendanceMode = $membership?->attendance_mode ?? BusinessMembership::ATTENDANCE_MODE_GEOFENCED;
        $isFreeLocation = $attendanceMode === BusinessMembership::ATTENDANCE_MODE_FREE;

        $userLat = isset($data['latitude']) && $data['latitude'] !== '' && $data['latitude'] !== null ? (float) $data['latitude'] : null;
        $userLng = isset($data['longitude']) && $data['longitude'] !== '' && $data['longitude'] !== null ? (float) $data['longitude'] : null;

        $targetLocation = null;
        $calculatedDistance = null;
        $clockInStatus = Attendance::CLOCK_IN_ON_TIME;

        if ($isFreeLocation) {
            $clockInStatus = Attendance::CLOCK_IN_FREE_LOCATION;
        } else {
            // Mode Geofenced: Resolve target location
            $targetLocationId = $data['location_id'] ?? $membership?->primary_location_id;
            if ($targetLocationId) {
                $targetLocation = Location::where('business_id', $business->id)->where('id', $targetLocationId)->first();
            }

            if (! $targetLocation) {
                $targetLocation = Location::where('business_id', $business->id)->where('is_primary', true)->first()
                    ?? Location::where('business_id', $business->id)->first();
            }

            // If target location has coordinates configured, verify distance
            if ($targetLocation && $targetLocation->latitude !== null && $targetLocation->longitude !== null) {
                if ($userLat === null || $userLng === null) {
                    throw ValidationException::withMessages([
                        'location' => "Izin lokasi GPS wajib diaktifkan untuk presensi kantor ({$targetLocation->name}). Silakan izinkan akses lokasi pada browser Anda.",
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

        // 4. Determine lateness (standard shift threshold: 09:00 WIB)
        $currentTime = now();
        $scheduledStart = Carbon::parse($today . ' 09:00:00');
        $lateMinutes = 0;

        if ($currentTime->gt($scheduledStart)) {
            $lateMinutes = (int) $currentTime->diffInMinutes($scheduledStart);
            if (! $isFreeLocation) {
                $clockInStatus = Attendance::CLOCK_IN_LATE;
            }
        }

        $overallStatus = $lateMinutes > 0 ? Attendance::STATUS_LATE : Attendance::STATUS_PRESENT;

        // 5. Handle optional selfie photo
        $photoPath = $this->storeAttendancePhoto($data['photo'] ?? null);

        // 6. Persist Attendance
        if (! $attendance) {
            $attendance = new Attendance([
                'id' => (string) Str::uuid(),
                'business_id' => $business->id,
                'user_id' => $user->id,
                'date' => $today,
            ]);
        }

        $attendance->location_id = $targetLocation?->id;
        $attendance->clock_in_at = $currentTime;
        $attendance->clock_in_lat = $userLat;
        $attendance->clock_in_lng = $userLng;
        $attendance->clock_in_distance_meters = $calculatedDistance;
        $attendance->clock_in_address = $data['address'] ?? null;
        $attendance->clock_in_photo = $photoPath ?? $attendance->clock_in_photo;
        $attendance->clock_in_status = $clockInStatus;
        $attendance->clock_in_notes = $data['notes'] ?? null;
        $attendance->late_minutes = $lateMinutes;
        $attendance->status = $overallStatus;
        $attendance->is_geofenced = ! $isFreeLocation;
        $attendance->save();

        return $attendance;
    }

    /**
     * Record daily clock-out and calculate work duration.
     *
     * @param Business $business
     * @param User $user
     * @param array<string, mixed> $data
     * @return Attendance
     * @throws ValidationException
     */
    public function clockOut(Business $business, User $user, array $data): Attendance
    {
        $today = now()->toDateString();

        $attendance = Attendance::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (! $attendance || $attendance->clock_in_at === null) {
            throw ValidationException::withMessages([
                'attendance' => 'Belum ada catatan presensi masuk (clock-in) untuk hari ini. Silakan lakukan clock-in terlebih dahulu atau ajukan tiket koreksi.',
            ]);
        }

        if ($attendance->clock_out_at !== null) {
            throw ValidationException::withMessages([
                'attendance' => 'Anda sudah melakukan presensi pulang (clock-out) hari ini pada jam ' . $attendance->clock_out_at->format('H:i') . ' WIB.',
            ]);
        }

        // Anti-spoofing accuracy verification
        $accuracy = isset($data['accuracy']) ? (float) $data['accuracy'] : null;
        if ($accuracy !== null && $accuracy > 100.0) {
            throw ValidationException::withMessages([
                'gps' => "Akurasi sinyal GPS perangkat Anda terlalu rendah ({$accuracy}m > 100m). Pastikan GPS aktif dalam mode akurasi tinggi.",
            ]);
        }

        $membership = BusinessMembership::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->first();

        $attendanceMode = $membership?->attendance_mode ?? BusinessMembership::ATTENDANCE_MODE_GEOFENCED;
        $isFreeLocation = $attendanceMode === BusinessMembership::ATTENDANCE_MODE_FREE;

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
        $workDurationMinutes = (int) $attendance->clock_in_at->diffInMinutes($clockOutTime);

        // Standard 8 hours = 480 minutes
        $standardWorkMinutes = 480;
        $overtimeMinutes = max(0, $workDurationMinutes - $standardWorkMinutes);

        // Early leave check (if leave before 17:00 and worked less than standard shift)
        $scheduledEnd = Carbon::parse($today . ' 17:00:00');
        $earlyLeaveMinutes = 0;
        if ($clockOutTime->lt($scheduledEnd) && $workDurationMinutes < $standardWorkMinutes) {
            $earlyLeaveMinutes = (int) $clockOutTime->diffInMinutes($scheduledEnd);
        }

        $clockOutStatus = Attendance::CLOCK_OUT_NORMAL;
        if ($isFreeLocation) {
            $clockOutStatus = Attendance::CLOCK_OUT_FREE_LOCATION;
        } elseif ($overtimeMinutes > 0) {
            $clockOutStatus = Attendance::CLOCK_OUT_OVERTIME;
        } elseif ($earlyLeaveMinutes > 0) {
            $clockOutStatus = Attendance::CLOCK_OUT_EARLY;
        }

        $photoPath = $this->storeAttendancePhoto($data['photo'] ?? null);

        $attendance->clock_out_at = $clockOutTime;
        $attendance->clock_out_lat = $userLat;
        $attendance->clock_out_lng = $userLng;
        $attendance->clock_out_distance_meters = $calculatedDistance;
        $attendance->clock_out_address = $data['address'] ?? null;
        $attendance->clock_out_photo = $photoPath ?? $attendance->clock_out_photo;
        $attendance->clock_out_status = $clockOutStatus;
        $attendance->clock_out_notes = $data['notes'] ?? null;
        $attendance->work_duration_minutes = $workDurationMinutes;
        $attendance->early_leave_minutes = $earlyLeaveMinutes;
        $attendance->overtime_minutes = $overtimeMinutes;
        $attendance->save();

        return $attendance;
    }

    /**
     * Submit an attendance correction ticket.
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

        if (! $ticket->isPending()) {
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

        if (! $ticket->isPending()) {
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
     * Helper to store attendance photo from file or base64 data URL.
     */
    private function storeAttendancePhoto(mixed $photo): ?string
    {
        if (! $photo) {
            return null;
        }

        if ($photo instanceof UploadedFile) {
            return $photo->store('attendance/photos', 'public');
        }

        if (is_string($photo) && str_starts_with($photo, 'data:image')) {
            $parts = explode(',', $photo, 2);
            if (count($parts) === 2) {
                $decoded = base64_decode($parts[1], true);
                if ($decoded !== false) {
                    $filename = 'attendance/photos/' . Str::uuid() . '.jpg';
                    Storage::disk('public')->put($filename, $decoded);
                    return $filename;
                }
            }
        }

        return null;
    }
}
