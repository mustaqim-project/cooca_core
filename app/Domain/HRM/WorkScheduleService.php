<?php

declare(strict_types=1);

namespace App\Domain\HRM;

use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\EmployeeSchedule;
use App\Models\Location;
use App\Models\User;
use App\Models\WorkShift;
use App\Support\TimezoneHelper;
use Carbon\Carbon;

final class WorkScheduleService
{
    /**
     * Resolve the active shift or schedule for an employee at a specific moment in the outlet's timezone.
     *
     * Precedence order:
     * 1. Specific Date Roster Schedule (roster tanggal khusus)
     * 2. Recurring Day-of-Week Schedule (jadwal mingguan efektif)
     * 3. Yesterday's Overnight Shift check (if currently in early morning before shift end)
     * 4. Employee's Default Shift in Business Membership
     * 5. Business / Outlet Operating Hours fallback
     * 6. None (Explicitly unscheduled)
     *
     * @return array<string, mixed>
     */
    public function resolveActiveShift(
        Business $business,
        User $user,
        ?Location $location = null,
        ?Carbon $at = null
    ): array {
        $timezone = TimezoneHelper::resolve($business, $location);
        $localNow = $at ? $at->copy()->setTimezone($timezone) : TimezoneHelper::now($business, $location);
        $localDate = $localNow->toDateString();
        $dayOfWeek = strtolower($localNow->format('l'));

        // 1. Early morning overnight shift check from yesterday
        // If local time is before 12:00 noon, check if yesterday had an overnight shift that is currently active
        if ($localNow->hour < 12) {
            $yesterdayDate = $localNow->copy()->subDay()->toDateString();
            $yesterdayDayOfWeek = strtolower($localNow->copy()->subDay()->format('l'));

            $yesterdaySchedule = EmployeeSchedule::where('business_id', $business->id)
                ->where('user_id', $user->id)
                ->where(function ($q) use ($yesterdayDate, $yesterdayDayOfWeek) {
                    $q->where(function ($sq) use ($yesterdayDate) {
                        $sq->specificDate()->whereDate('specific_date', $yesterdayDate);
                    })->orWhere(function ($rq) use ($yesterdayDayOfWeek, $yesterdayDate) {
                        $rq->recurring()->where('day_of_week', $yesterdayDayOfWeek)->effectiveOn($yesterdayDate);
                    });
                })
                ->where('is_off_day', false)
                ->with('workShift')
                ->first();

            if ($yesterdaySchedule && $yesterdaySchedule->workShift && $yesterdaySchedule->workShift->is_active && $yesterdaySchedule->workShift->isOvernight()) {
                $shift = $yesterdaySchedule->workShift;
                $schedEnd = $shift->getScheduledEndCarbon($yesterdayDate, $timezone);

                // If currently within shift window + 2 hour leeway, associate with yesterday's overnight shift
                if ($localNow->lte($schedEnd->copy()->addHours(2))) {
                    return $this->buildShiftResult(
                        $shift,
                        $yesterdayDate,
                        $timezone,
                        $localNow,
                        'yesterday_overnight',
                        $yesterdaySchedule->location_id
                    );
                }
            }
        }

        // 2. Check Specific Date Roster for today
        $specificSchedule = EmployeeSchedule::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->specificDate()
            ->whereDate('specific_date', $localDate)
            ->with('workShift')
            ->first();

        if ($specificSchedule) {
            if ($specificSchedule->is_off_day) {
                return $this->buildOffDayResult($timezone, $localNow, 'Roster Libur (OFF) Terjadwal');
            }

            if ($specificSchedule->workShift && $specificSchedule->workShift->is_active) {
                return $this->buildShiftResult(
                    $specificSchedule->workShift,
                    $localDate,
                    $timezone,
                    $localNow,
                    'specific_date',
                    $specificSchedule->location_id
                );
            }
        }

        // 3. Check Recurring Weekly Schedule for today's day of week
        $recurringSchedule = EmployeeSchedule::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->recurring()
            ->where('day_of_week', $dayOfWeek)
            ->effectiveOn($localDate)
            ->with('workShift')
            ->first();

        if ($recurringSchedule) {
            if ($recurringSchedule->is_off_day) {
                return $this->buildOffDayResult($timezone, $localNow, 'Jadwal Libur Mingguan (OFF)');
            }

            if ($recurringSchedule->workShift && $recurringSchedule->workShift->is_active) {
                return $this->buildShiftResult(
                    $recurringSchedule->workShift,
                    $localDate,
                    $timezone,
                    $localNow,
                    'recurring',
                    $recurringSchedule->location_id
                );
            }
        }

        // 4. Check Employee Default Shift in Business Membership
        $membership = BusinessMembership::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->with('defaultShift')
            ->first();

        if ($membership?->defaultShift && $membership->defaultShift->is_active) {
            return $this->buildShiftResult(
                $membership->defaultShift,
                $localDate,
                $timezone,
                $localNow,
                'membership_default',
                $membership->primary_location_id
            );
        }

        // 5. Explicit Unscheduled (Do NOT use outlet operating hours as employee shift!)
        // Operating hours are operational boundaries of the business/outlet, not employee work shifts.
        return [
            'has_schedule' => false,
            'is_off_day' => false,
            'shift' => null,
            'work_shift_id' => null,
            'shift_name' => 'Bebas Jadwal / Tanpa Shift',
            'shift_code' => null,
            'start_time' => null,
            'end_time' => null,
            'scheduled_start' => null,
            'scheduled_end' => null,
            'base_date' => $localDate,
            'timezone' => $timezone,
            'grace_period_minutes' => 0,
            'break_duration_minutes' => 0,
            'is_overnight' => false,
            'source' => 'none',
            'status_label' => 'Bebas Jadwal',
        ];
    }

    /**
     * Calculate precision clock-in details against resolved schedule.
     *
     * @param array<string, mixed> $scheduleInfo
     * @param Carbon $clockInTime Time of clock-in in outlet timezone
     * @return array<string, mixed>
     */
    public function evaluateClockIn(array $scheduleInfo, Carbon $clockInTime): array
    {
        $scheduledStart = $scheduleInfo['scheduled_start'] ?? null;
        $graceMinutes = (int) ($scheduleInfo['grace_period_minutes'] ?? 0);

        if (! $scheduledStart instanceof Carbon) {
            return [
                'is_late' => false,
                'late_minutes' => 0,
                'early_in_minutes' => 0,
                'clock_in_status' => 'on_time',
                'overall_status' => 'present',
                'status_badge' => 'ON_TIME',
                'status_text' => 'Tepat Waktu (Bebas Jadwal)',
                'delay_text' => '0 menit',
            ];
        }

        $scheduledWithGrace = $scheduledStart->copy()->addMinutes($graceMinutes);

        if ($clockInTime->lte($scheduledStart)) {
            // Early arrival
            $earlyMinutes = (int) round(abs($clockInTime->diffInMinutes($scheduledStart)));

            return [
                'is_late' => false,
                'late_minutes' => 0,
                'early_in_minutes' => $earlyMinutes,
                'clock_in_status' => 'on_time',
                'overall_status' => 'present',
                'status_badge' => $earlyMinutes > 0 ? 'EARLY' : 'ON_TIME',
                'status_text' => $earlyMinutes > 0 ? "Lebih Awal ({$earlyMinutes} menit)" : 'Tepat Waktu',
                'delay_text' => '0 menit',
            ];
        }

        if ($clockInTime->lte($scheduledWithGrace)) {
            // Within grace period (Toleransi)
            $diffMinutes = (int) round(abs($clockInTime->diffInMinutes($scheduledStart)));

            return [
                'is_late' => false,
                'late_minutes' => 0,
                'early_in_minutes' => 0,
                'clock_in_status' => 'on_time',
                'overall_status' => 'present',
                'status_badge' => 'ON_TIME',
                'status_text' => "Tepat Waktu (Dalam Toleransi {$graceMinutes}m)",
                'delay_text' => '0 menit',
            ];
        }

        // Passed grace period -> LATE!
        // Lateness is strictly calculated from scheduled start time
        $lateMinutes = (int) round(abs($clockInTime->diffInMinutes($scheduledStart)));

        return [
            'is_late' => true,
            'late_minutes' => $lateMinutes,
            'early_in_minutes' => 0,
            'clock_in_status' => 'late',
            'overall_status' => 'late',
            'status_badge' => 'LATE',
            'status_text' => "Terlambat {$lateMinutes} menit",
            'delay_text' => "{$lateMinutes} menit",
        ];
    }

    /**
     * Calculate precision clock-out details against resolved schedule.
     *
     * @param array<string, mixed> $scheduleInfo
     * @param Carbon $clockInTime
     * @param Carbon $clockOutTime
     * @return array<string, mixed>
     */
    public function evaluateClockOut(array $scheduleInfo, Carbon $clockInTime, Carbon $clockOutTime): array
    {
        $durationMinutes = (int) round(abs($clockOutTime->diffInMinutes($clockInTime)));
        $scheduledEnd = $scheduleInfo['scheduled_end'] ?? null;

        $earlyLeaveMinutes = 0;
        $overtimeMinutes = 0;
        $clockOutStatus = 'normal';

        if ($scheduledEnd instanceof Carbon) {
            if ($clockOutTime->lt($scheduledEnd)) {
                $earlyLeaveMinutes = (int) round(abs($scheduledEnd->diffInMinutes($clockOutTime)));
                $clockOutStatus = 'early_leave';
            } elseif ($clockOutTime->gt($scheduledEnd)) {
                $overtimeMinutes = (int) round(abs($clockOutTime->diffInMinutes($scheduledEnd)));
                $clockOutStatus = $overtimeMinutes >= 30 ? 'overtime' : 'normal';
            }
        } else {
            // Fallback standard 8-hour workday (480 minutes)
            if ($durationMinutes > 480) {
                $overtimeMinutes = $durationMinutes - 480;
                $clockOutStatus = 'overtime';
            }
        }

        return [
            'work_duration_minutes' => $durationMinutes,
            'early_leave_minutes' => $earlyLeaveMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'clock_out_status' => $clockOutStatus,
        ];
    }

    private function buildShiftResult(
        WorkShift $shift,
        string $baseDate,
        string $timezone,
        Carbon $localNow,
        string $source,
        ?string $locationId = null
    ): array {
        return [
            'has_schedule' => true,
            'is_off_day' => false,
            'shift' => $shift,
            'work_shift_id' => $shift->id,
            'shift_name' => $shift->name,
            'shift_code' => $shift->code,
            'start_time' => $shift->start_time,
            'end_time' => $shift->end_time,
            'scheduled_start' => $shift->getScheduledStartCarbon($baseDate, $timezone),
            'scheduled_end' => $shift->getScheduledEndCarbon($baseDate, $timezone),
            'base_date' => $baseDate,
            'timezone' => $timezone,
            'grace_period_minutes' => (int) $shift->grace_period_minutes,
            'break_duration_minutes' => (int) $shift->break_duration_minutes,
            'is_overnight' => $shift->isOvernight(),
            'source' => $source,
            'status_label' => $shift->formatted_hours,
            'location_id' => $locationId ?: $shift->location_id,
        ];
    }

    private function buildOffDayResult(string $timezone, Carbon $localNow, string $statusLabel): array
    {
        return [
            'has_schedule' => true,
            'is_off_day' => true,
            'shift' => null,
            'work_shift_id' => null,
            'shift_name' => 'Hari Libur (OFF)',
            'shift_code' => 'OFF',
            'start_time' => null,
            'end_time' => null,
            'scheduled_start' => null,
            'scheduled_end' => null,
            'base_date' => $localNow->toDateString(),
            'timezone' => $timezone,
            'grace_period_minutes' => 0,
            'break_duration_minutes' => 0,
            'is_overnight' => false,
            'source' => 'off_day',
            'status_label' => $statusLabel,
        ];
    }
}
