<?php

declare(strict_types=1);

namespace App\Domain\HRM;

use App\Models\AttendanceException;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

final class AttendanceExceptionService
{
    /**
     * Create and approve an attendance exception policy (WFH, Field Work, Business Trip, Temporary Assignment).
     *
     * @param Business $business
     * @param User $employee
     * @param array{
     *     exception_mode: string,
     *     start_date: string,
     *     end_date: string,
     *     allowed_latitude?: float|null,
     *     allowed_longitude?: float|null,
     *     allowed_radius_meters?: int|null,
     *     location_name?: string|null,
     *     reason: string,
     *     notes?: string|null
     * } $data
     * @param User $approvedBy
     * @return AttendanceException
     */
    public function createException(
        Business $business,
        User|array $employeeOrData,
        array|User $dataOrApprover,
        ?User $approvedBy = null
    ): AttendanceException {
        if ($employeeOrData instanceof User) {
            $employee = $employeeOrData;
            $data = is_array($dataOrApprover) ? $dataOrApprover : [];
            $approver = $approvedBy ?? auth()->user();
        } else {
            $data = $employeeOrData;
            $approver = $dataOrApprover instanceof User ? $dataOrApprover : auth()->user();
            $employee = isset($data['user_id']) ? User::findOrFail($data['user_id']) : auth()->user();
        }

        $startDateStr = $data['start_date'] ?? $data['effective_from'] ?? now()->toDateString();
        $endDateStr = $data['end_date'] ?? $data['effective_until'] ?? $startDateStr;

        $startDate = Carbon::parse($startDateStr)->startOfDay();
        $endDate = Carbon::parse($endDateStr)->endOfDay();

        if ($endDate->lt($startDate)) {
            throw new InvalidArgumentException('Tanggal akhir penugasan/kebijakan tidak boleh lebih awal dari tanggal mulai.');
        }

        $mode = $data['exception_mode'] ?? $data['policy_type'] ?? AttendanceException::MODE_WFH;
        $name = $data['location_name'] ?? $data['name'] ?? ucfirst(str_replace('_', ' ', (string) $mode));
        $radius = (int) ($data['allowed_radius_meters'] ?? $data['radius_meters'] ?? 100);

        $exception = AttendanceException::create([
            'id' => (string) Str::uuid(),
            'business_id' => $business->id,
            'user_id' => $employee->id,
            'exception_mode' => $mode,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'allowed_latitude' => isset($data['allowed_latitude']) ? (float) $data['allowed_latitude'] : null,
            'allowed_longitude' => isset($data['allowed_longitude']) ? (float) $data['allowed_longitude'] : null,
            'allowed_radius_meters' => $radius,
            'location_name' => $name,
            'reason' => $data['reason'] ?? 'Dispensasi presensi khusus',
            'notes' => $data['notes'] ?? null,
            'status' => AttendanceException::STATUS_APPROVED,
            'approved_by' => $approver?->id,
            'approved_at' => now(),
        ]);

        AuditLog::create([
            'id' => (string) Str::uuid(),
            'business_id' => $business->id,
            'user_id' => $approver?->id ?? $business->users()->first()?->id,
            'auditable_type' => AttendanceException::class,
            'auditable_id' => $exception->id,
            'action' => 'attendance.exception_created',
            'risk_level' => AuditLog::RISK_MEDIUM,
            'risk_reason' => 'Pemberian izin presensi khusus di luar geofence normal kantor.',
            'notes' => "Pemberian dispensasi presensi {$exception->exception_mode} untuk {$employee->name} periode {$startDate->toDateString()} s/d {$endDate->toDateString()}.",
            'new_values' => $exception->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return $exception;
    }

    /**
     * Get active exception policy for employee on a given date.
     */
    public function getActiveException(Business $business, User $employee, Carbon|string $date): ?AttendanceException
    {
        $targetDate = $date instanceof Carbon ? $date->toDateString() : $date;

        return AttendanceException::where('business_id', $business->id)
            ->where('user_id', $employee->id)
            ->where('status', AttendanceException::STATUS_APPROVED)
            ->whereDate('start_date', '<=', $targetDate)
            ->whereDate('end_date', '>=', $targetDate)
            ->latest('created_at')
            ->first();
    }

    /**
     * Evaluate if employee's current location is allowed under active exception policy.
     *
     * @return array{is_exception: bool, exception: ?AttendanceException, allowed: bool, reason: string}
     */
    public function evaluateLocationException(
        Business $business,
        User $employee,
        ?float $lat,
        ?float $lng,
        int $calculatedDistance = 0,
        ?Carbon $date = null
    ): array {
        $evaluationDate = $date ?? Carbon::today();
        $activeException = $this->getActiveException($business, $employee, $evaluationDate);

        if (! $activeException) {
            return [
                'is_exception' => false,
                'exception' => null,
                'allowed' => false,
                'reason' => 'Tidak ada kebijakan khusus aktif.',
            ];
        }

        // Mode WFA (Work From Anywhere) or Business Trip without coordinates
        if ($activeException->exception_mode === AttendanceException::MODE_WFA ||
            ($activeException->exception_mode === AttendanceException::MODE_BUSINESS_TRIP && $activeException->allowed_latitude === null)) {
            return [
                'is_exception' => true,
                'exception' => $activeException,
                'allowed' => true,
                'reason' => "Izin presensi bebas lokasi ({$activeException->exception_mode}) aktif: {$activeException->reason}",
            ];
        }

        // Mode WFH or Field Work with specified coordinates
        if ($activeException->allowed_latitude !== null && $activeException->allowed_longitude !== null) {
            if ($lat !== null && $lng !== null) {
                $calculatedDistance = $this->calculateDistanceMeters(
                    $lat,
                    $lng,
                    (float) $activeException->allowed_latitude,
                    (float) $activeException->allowed_longitude
                );
            }
        }

        $allowed = $activeException->allowsCoordinates($lat, $lng, $calculatedDistance);

        return [
            'is_exception' => true,
            'exception' => $activeException,
            'allowed' => $allowed,
            'reason' => $allowed
                ? "Presensi diizinkan sesuai penugasan {$activeException->exception_mode} ({$activeException->location_name})."
                : "Presensi di luar radius penugasan khusus {$activeException->exception_mode} ({$activeException->location_name}).",
        ];
    }

    /**
     * Calculate geodesic distance between two GPS coordinates using Haversine formula.
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
     * Revoke an active exception policy.
     */
    public function revokeException(
        Business $business,
        AttendanceException $exception,
        User $actor,
        string $reason
    ): AttendanceException {
        if ($exception->business_id !== $business->id) {
            throw new RuntimeException('Kebijakan presensi tidak ditemukan pada workspace aktif.');
        }

        $exception->update([
            'status' => AttendanceException::STATUS_REVOKED,
            'notes' => $exception->notes . ' [Dicabut oleh ' . $actor->name . ': ' . $reason . ']',
        ]);

        AuditLog::create([
            'id' => (string) Str::uuid(),
            'business_id' => $business->id,
            'user_id' => $actor->id,
            'auditable_type' => AttendanceException::class,
            'auditable_id' => $exception->id,
            'action' => 'attendance.exception_revoked',
            'risk_level' => AuditLog::RISK_LOW,
            'risk_reason' => 'Pencabutan dispensasi presensi khusus karyawan.',
            'notes' => "Pencabutan dispensasi presensi {$exception->id} untuk {$exception->user->name}. Alasan: {$reason}",
            'old_values' => ['status' => AttendanceException::STATUS_APPROVED],
            'new_values' => ['status' => AttendanceException::STATUS_REVOKED, 'revoke_reason' => $reason],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return $exception;
    }
}
