<?php

declare(strict_types=1);

namespace App\Domain\Ai\Tools;

use App\Domain\Ai\AiSalesAnalysisService;
use App\Models\Attendance;
use App\Models\Business;
use App\Models\User;
use Carbon\Carbon;

final class GetAttendanceSummaryTool extends BaseAiTool
{
    public function __construct(
        private readonly AiSalesAnalysisService $salesService = new AiSalesAnalysisService()
    ) {}

    public function getName(): string
    {
        return 'GetAttendanceSummary';
    }

    public function getDescription(): string
    {
        return 'Mengambil ringkasan absensi kehadiran karyawan hari ini, tingkat kepatuhan jam kerja, serta performa shift kasir oleh AI HR Agent.';
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $today = now()->toDateString();
        $totalStaff = $business->users()->count();

        $attendancesToday = Attendance::with('user:id,name')
            ->where('business_id', $business->id)
            ->whereDate('date', $today)
            ->get();

        $presentCount = $attendancesToday->whereIn('status', [Attendance::STATUS_PRESENT, Attendance::STATUS_LATE, Attendance::STATUS_HALF_DAY])->count();
        $lateCount = $attendancesToday->where('status', Attendance::STATUS_LATE)->count();
        $leaveCount = $attendancesToday->whereIn('status', [Attendance::STATUS_LEAVE, Attendance::STATUS_SICK])->count();

        $todayRoster = $attendancesToday->map(fn($a) => [
            'user_name' => $a->user?->name ?? 'Karyawan',
            'status' => $a->status ?? 'present',
            'clock_in' => $a->clock_in_at ? Carbon::parse($a->clock_in_at)->format('H:i') : null,
            'clock_out' => $a->clock_out_at ? Carbon::parse($a->clock_out_at)->format('H:i') : null,
            'late_minutes' => $a->late_minutes ?? 0,
        ])->values()->all();

        $cashiers = $this->salesService->getCashierPerformance($business);

        return [
            'date' => $today,
            'total_staff' => $totalStaff,
            'present_today' => $presentCount,
            'late_today' => $lateCount,
            'leave_or_sick' => $leaveCount,
            'attendance_rate_percent' => $totalStaff > 0 ? round(($presentCount / $totalStaff) * 100, 1) : 100,
            'today_roster' => array_slice($todayRoster, 0, 10),
            'cashier_scorecards' => array_slice($cashiers, 0, 5),
            'staff_count' => $totalStaff,
            'performance_scorecards' => array_slice($cashiers, 0, 5),
        ];
    }
}
