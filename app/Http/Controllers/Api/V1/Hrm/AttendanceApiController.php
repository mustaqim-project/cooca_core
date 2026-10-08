<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Hrm;

use App\Domain\HRM\AttendanceExceptionService;
use App\Domain\HRM\AttendanceService;
use App\Domain\HRM\Biometrics\FaceVerificationService;
use App\Domain\HRM\WorkScheduleService;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceException;
use App\Models\BusinessMembership;
use App\Models\EmployeeCommission;
use App\Models\EmployeeLoan;
use App\Models\Location;
use App\Models\PayrollItem;
use App\Models\User;
use App\Support\Context;
use App\Support\TimezoneHelper;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class AttendanceApiController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
        private readonly FaceVerificationService $faceVerificationService,
        private readonly AttendanceExceptionService $exceptionService
    ) {}

    /**
     * Clock-In via REST API with Biometric, Geofence, & Exception Policy evaluation.
     * POST /api/v1/attendance/check-in
     */
    public function checkIn(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $validated = $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable'],
            'face_data' => ['nullable'],
            'location_id' => ['nullable', 'uuid', 'exists:locations,id'],
        ]);

        try {
            $attendance = $this->attendanceService->clockIn($business, $user, $validated);
            $feedback = $this->attendanceService->buildClockInFeedback($attendance);

            return response()->json([
                'success' => true,
                'message' => $feedback['message'],
                'data' => array_merge($feedback, [
                    'feedback' => $feedback,
                    'id' => $attendance->id,
                    'date' => $attendance->date?->toDateString(),
                    'clock_in_at' => $attendance->clock_in_at?->toIso8601String(),
                    'clock_in_status' => $attendance->clock_in_status,
                    'status' => $attendance->status,
                    'face_verified' => $attendance->face_verified,
                    'face_similarity_score' => $attendance->face_similarity_score,
                    'location_name' => $attendance->location?->name,
                    'exception_policy_id' => $attendance->exception_policy_id,
                ]),
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Clock-Out via REST API with Duration & Overtime calculation.
     * POST /api/v1/attendance/check-out
     */
    public function checkOut(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $validated = $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable'],
            'face_data' => ['nullable'],
        ]);

        try {
            $attendance = $this->attendanceService->clockOut($business, $user, $validated);
            $feedback = $this->attendanceService->buildClockOutFeedback($attendance);

            return response()->json([
                'success' => true,
                'message' => $feedback['message'],
                'data' => array_merge($feedback, [
                    'feedback' => $feedback,
                    'id' => $attendance->id,
                    'date' => $attendance->date?->toDateString(),
                    'clock_in_at' => $attendance->clock_in_at?->toIso8601String(),
                    'clock_out_at' => $attendance->clock_out_at?->toIso8601String(),
                    'status' => $attendance->status,
                ]),
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get current user's attendance status today, enriched with location & shift context.
     * GET /api/v1/attendance/today
     */
    public function today(): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $membership = BusinessMembership::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->with(['roleModel', 'location'])
            ->first();

        $location = $membership?->location
            ?? Location::where('business_id', $business->id)->where('is_primary', true)->first()
            ?? Location::where('business_id', $business->id)->first();

        $timezone = TimezoneHelper::resolve($business, $location);
        $tzAbbr = TimezoneHelper::abbreviation($timezone);
        $localNow = TimezoneHelper::now($business, $location);
        $today = $localNow->toDateString();

        $attendance = Attendance::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->with(['location', 'exceptionPolicy', 'correction'])
            ->first();

        $activeException = $this->exceptionService->getActiveException($business, $user, $localNow);

        $workScheduleService = app(WorkScheduleService::class);
        $activeShift = $workScheduleService->resolveActiveShift($business, $user, $location, $localNow);

        // 7 days performance metrics
        $sevenDaysAgo = $localNow->copy()->subDays(6)->toDateString();
        $recentAttendances = Attendance::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->whereDate('date', '>=', $sevenDaysAgo)
            ->get();
        $presentDaysCount = $recentAttendances->whereNotNull('clock_in_at')->count();
        $onTimeDaysCount = $recentAttendances->where('clock_in_status', Attendance::CLOCK_IN_ON_TIME)->count();
        $lateDaysCount = $recentAttendances->filter(fn(Attendance $a) => $a->clock_in_status === Attendance::CLOCK_IN_LATE || $a->status === Attendance::STATUS_LATE)->count();
        $totalWorkMinutes = (int) $recentAttendances->sum('work_duration_minutes');
        $totalWorkHours = round($totalWorkMinutes / 60, 1);

        return response()->json([
            'success' => true,
            'data' => [
                'server_time' => $localNow->toIso8601String(),
                'date' => $today,
                'timezone' => $timezone,
                'tz_abbr' => $tzAbbr,
                'employee' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? $membership?->whatsapp_number,
                    'role' => $membership?->roleModel?->name ?? ($membership?->role ?? 'staff'),
                    'job_title' => $membership?->job_title,
                    'attendance_mode' => $membership?->attendance_mode ?? 'geofenced',
                    'face_registered' => ! empty($membership?->face_biometric_template),
                    'face_registered_at' => $membership?->face_registered_at?->toIso8601String(),
                ],
                'location' => [
                    'id' => $location?->id,
                    'name' => $location?->name ?? 'Kantor Utama',
                    'city' => $location?->city ?? $business->city ?? 'Jakarta',
                    'latitude' => $location?->latitude ? (float) $location->latitude : -6.2088,
                    'longitude' => $location?->longitude ? (float) $location->longitude : 106.8456,
                    'geofence_radius_meters' => (int) ($location?->geofence_radius_meters ?: 50),
                ],
                'active_shift' => [
                    'shift_name' => $activeShift['shift_name'] ?? 'Bebas Jadwal',
                    'scheduled_start' => $activeShift['scheduled_start'] ?? null,
                    'scheduled_end' => $activeShift['scheduled_end'] ?? null,
                    'is_off_day' => (bool) ($activeShift['is_off_day'] ?? false),
                    'has_schedule' => (bool) ($activeShift['has_schedule'] ?? false),
                    'grace_period_minutes' => (int) ($activeShift['grace_period_minutes'] ?? 0),
                    'is_overnight' => (bool) ($activeShift['is_overnight'] ?? false),
                ],
                'attendance' => $attendance ? [
                    'id' => $attendance->id,
                    'clock_in_at' => $attendance->clock_in_at?->toIso8601String(),
                    'clock_out_at' => $attendance->clock_out_at?->toIso8601String(),
                    'clock_in_status' => $attendance->clock_in_status,
                    'status' => $attendance->status,
                    'work_duration_minutes' => $attendance->work_duration_minutes,
                    'formatted_duration' => $attendance->formatted_work_duration,
                    'late_minutes' => $attendance->late_minutes,
                    'overtime_minutes' => $attendance->overtime_minutes,
                    'early_leave_minutes' => $attendance->early_leave_minutes,
                    'face_verified' => $attendance->face_verified,
                    'face_similarity_score' => $attendance->face_similarity_score,
                    'has_correction' => (bool) $attendance->correction,
                    'correction_status' => $attendance->correction?->status,
                ] : null,
                'active_exception' => $activeException ? [
                    'id' => $activeException->id,
                    'policy_type' => $activeException->policy_type,
                    'name' => $activeException->name,
                    'reason' => $activeException->reason,
                    'radius_meters' => $activeException->radius_meters,
                    'effective_until' => $activeException->effective_until?->toDateString(),
                ] : null,
                'stats_7days' => [
                    'present_days' => $presentDaysCount,
                    'on_time_days' => $onTimeDaysCount,
                    'late_days' => $lateDaysCount,
                    'total_work_hours' => $totalWorkHours,
                ],
            ],
        ], 200);
    }

    /**
     * Complete Unified Portal Hub payload matching app/portal web interface.
     * GET /api/v1/attendance/portal
     */
    public function portal(): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $membership = BusinessMembership::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->with(['roleModel', 'location'])
            ->first();

        $location = $membership?->location
            ?? Location::where('business_id', $business->id)->where('is_primary', true)->first()
            ?? Location::where('business_id', $business->id)->first();

        $timezone = TimezoneHelper::resolve($business, $location);
        $tzAbbr = TimezoneHelper::abbreviation($timezone);
        $localNow = TimezoneHelper::now($business, $location);
        $today = $localNow->toDateString();
        $currentYear = $localNow->year;

        // Shift resolution
        $workScheduleService = app(WorkScheduleService::class);
        $activeShift = $workScheduleService->resolveActiveShift($business, $user, $location, $localNow);

        // Tenure calculation
        $joinDate = $membership?->join_date ?? $membership?->created_at ?? $user->created_at;
        $tenureYears = (int) $joinDate->diffInYears(now());
        $tenureMonths = (int) ($joinDate->diffInMonths(now()) % 12);
        $tenureText = $tenureYears > 0 ? "{$tenureYears} Tahun {$tenureMonths} Bulan" : "{$tenureMonths} Bulan";

        // Leave quota
        $leaveAllowance = 12;
        $leaveUsed = Attendance::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->whereIn('status', [Attendance::STATUS_LEAVE])
            ->whereYear('date', $currentYear)
            ->count();
        $leaveRemaining = max(0, $leaveAllowance - $leaveUsed);

        // Loans / Kasbon
        $loans = EmployeeLoan::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();
        $activeLoan = $loans->firstWhere('status', EmployeeLoan::STATUS_ACTIVE);

        // Commissions
        $commissions = EmployeeCommission::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->where('status', EmployeeCommission::STATUS_APPROVED)
            ->sum('earned_amount');

        // Today Attendance
        $todayAttendance = Attendance::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        // Active Exception
        $activeException = $this->exceptionService->getActiveException($business, $user, $localNow);

        // Past 7 Days metrics
        $sevenDaysAgo = $localNow->copy()->subDays(6)->toDateString();
        $recentAttendances = Attendance::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->whereDate('date', '>=', $sevenDaysAgo)
            ->orderByDesc('date')
            ->get();

        $presentDaysCount = $recentAttendances->whereNotNull('clock_in_at')->count();
        $onTimeDaysCount = $recentAttendances->where('clock_in_status', Attendance::CLOCK_IN_ON_TIME)->count();
        $lateDaysCount = $recentAttendances->filter(fn(Attendance $a) => $a->clock_in_status === Attendance::CLOCK_IN_LATE || $a->status === Attendance::STATUS_LATE)->count();
        $totalWorkMinutes = (int) $recentAttendances->sum('work_duration_minutes');
        $totalWorkHours = round($totalWorkMinutes / 60, 1);

        // Monthly stats
        $monthlyAttendances = Attendance::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->whereMonth('date', $localNow->month)
            ->whereYear('date', $localNow->year)
            ->orderByDesc('date')
            ->get();

        $monthlyStats = [
            'present' => $monthlyAttendances->whereIn('status', [Attendance::STATUS_PRESENT, Attendance::STATUS_LATE])->count(),
            'late' => $monthlyAttendances->where('status', Attendance::STATUS_LATE)->count(),
            'overtime_minutes' => (int) $monthlyAttendances->sum('overtime_minutes'),
            'leave' => $monthlyAttendances->where('status', Attendance::STATUS_LEAVE)->count(),
            'sick' => $monthlyAttendances->where('status', Attendance::STATUS_SICK)->count(),
            'total_hours' => round(((int) $monthlyAttendances->sum('work_duration_minutes')) / 60, 1),
        ];

        // Recent Corrections
        $recentCorrections = AttendanceCorrection::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // Recent Payslips
        $recentPayslips = PayrollItem::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->with('payroll')
            ->orderByDesc('created_at')
            ->take(6)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'server_time' => $localNow->toIso8601String(),
                'date' => $today,
                'timezone' => $timezone,
                'tz_abbr' => $tzAbbr,
                'business' => [
                    'id' => $business->id,
                    'name' => $business->name,
                    'slug' => $business->slug,
                    'city' => $business->city ?? 'Jakarta',
                ],
                'employee' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? $membership?->whatsapp_number,
                    'role' => $membership?->roleModel?->name ?? ($membership?->role ?? 'staff'),
                    'job_title' => $membership?->job_title ?? 'Karyawan',
                    'tenure_text' => $tenureText,
                    'attendance_mode' => $membership?->attendance_mode ?? 'geofenced',
                    'face_registered' => ! empty($membership?->face_biometric_template),
                    'face_registered_at' => $membership?->face_registered_at?->toIso8601String(),
                    'leave_allowance' => $leaveAllowance,
                    'leave_used' => $leaveUsed,
                    'leave_remaining' => $leaveRemaining,
                    'active_loan' => $activeLoan ? [
                        'loan_number' => $activeLoan->loan_number,
                        'amount' => (float) $activeLoan->amount,
                        'remaining_balance' => (float) $activeLoan->remaining_balance,
                        'monthly_installment' => (float) $activeLoan->monthly_installment,
                        'status' => $activeLoan->status,
                    ] : null,
                    'total_commissions' => (float) $commissions,
                    'bank' => [
                        'name' => $membership?->bank_name ?? 'Belum diatur',
                        'account_number' => $membership?->bank_account_number ?? '-',
                        'account_holder' => $membership?->bank_account_holder ?? $user->name,
                    ],
                    'compensation' => [
                        'base_salary' => (float) ($membership?->base_salary ?? 0),
                        'fixed_allowances' => (float) ($membership?->fixed_allowances ?? 0),
                    ],
                    'tax' => [
                        'nik_ktp' => $membership?->nik_ktp ?? '-',
                        'npwp' => $membership?->npwp ?? '-',
                        'tax_ptkp_status' => $membership?->tax_ptkp_status ?? 'TK/0',
                    ],
                ],
                'location' => [
                    'id' => $location?->id,
                    'name' => $location?->name ?? 'Kantor Utama',
                    'city' => $location?->city ?? $business->city ?? 'Jakarta',
                    'latitude' => $location?->latitude ? (float) $location->latitude : -6.2088,
                    'longitude' => $location?->longitude ? (float) $location->longitude : 106.8456,
                    'geofence_radius_meters' => (int) ($location?->geofence_radius_meters ?: 50),
                ],
                'active_shift' => [
                    'shift_name' => $activeShift['shift_name'] ?? 'Bebas Jadwal',
                    'scheduled_start' => $activeShift['scheduled_start'] ?? null,
                    'scheduled_end' => $activeShift['scheduled_end'] ?? null,
                    'is_off_day' => (bool) ($activeShift['is_off_day'] ?? false),
                    'has_schedule' => (bool) ($activeShift['has_schedule'] ?? false),
                    'grace_period_minutes' => (int) ($activeShift['grace_period_minutes'] ?? 0),
                    'is_overnight' => (bool) ($activeShift['is_overnight'] ?? false),
                ],
                'today_attendance' => $todayAttendance ? [
                    'id' => $todayAttendance->id,
                    'clock_in_at' => $todayAttendance->clock_in_at?->toIso8601String(),
                    'clock_out_at' => $todayAttendance->clock_out_at?->toIso8601String(),
                    'clock_in_status' => $todayAttendance->clock_in_status,
                    'status' => $todayAttendance->status,
                    'work_duration_minutes' => $todayAttendance->work_duration_minutes,
                    'formatted_duration' => $todayAttendance->formatted_work_duration,
                    'late_minutes' => $todayAttendance->late_minutes,
                    'overtime_minutes' => $todayAttendance->overtime_minutes,
                    'early_leave_minutes' => $todayAttendance->early_leave_minutes,
                    'face_verified' => $todayAttendance->face_verified,
                    'face_similarity_score' => $todayAttendance->face_similarity_score,
                ] : null,
                'active_exception' => $activeException ? [
                    'id' => $activeException->id,
                    'policy_type' => $activeException->policy_type,
                    'name' => $activeException->name,
                    'reason' => $activeException->reason,
                    'radius_meters' => $activeException->radius_meters,
                    'effective_until' => $activeException->effective_until?->toDateString(),
                ] : null,
                'stats_7days' => [
                    'present_days' => $presentDaysCount,
                    'on_time_days' => $onTimeDaysCount,
                    'late_days' => $lateDaysCount,
                    'total_work_hours' => $totalWorkHours,
                ],
                'monthly_stats' => $monthlyStats,
                'recent_attendances' => collect($recentAttendances)->map(function (Attendance $att) {
                    return [
                        'id' => $att->id,
                        'date' => $att->date?->toDateString(),
                        'clock_in_at' => $att->clock_in_at?->toIso8601String(),
                        'clock_out_at' => $att->clock_out_at?->toIso8601String(),
                        'clock_in_status' => $att->clock_in_status,
                        'status' => $att->status,
                        'work_duration_minutes' => $att->work_duration_minutes,
                        'formatted_duration' => $att->formatted_work_duration,
                        'late_minutes' => $att->late_minutes,
                        'face_verified' => $att->face_verified,
                    ];
                }),
                'recent_corrections' => collect($recentCorrections)->map(function (AttendanceCorrection $c) {
                    return [
                        'id' => $c->id,
                        'correction_number' => $c->correction_number,
                        'target_date' => $c->target_date?->toDateString(),
                        'correction_type' => $c->correction_type,
                        'status' => $c->status,
                        'reason' => $c->reason,
                        'created_at' => $c->created_at?->toIso8601String(),
                    ];
                }),
                'recent_payslips' => collect($recentPayslips)->map(function (PayrollItem $p) {
                    return [
                        'id' => $p->id,
                        'period' => $p->payroll?->title ?? ($p->payroll ? "{$p->payroll->period_month}/{$p->payroll->period_year}" : $p->created_at->format('F Y')),
                        'gross_pay' => (float) $p->gross_pay,
                        'total_deductions' => (float) $p->total_deductions,
                        'take_home_pay' => (float) $p->take_home_pay,
                        'status' => $p->status,
                        'created_at' => $p->created_at?->toIso8601String(),
                    ];
                }),
            ],
        ], 200);
    }

    /**
     * Get paginated payslips for the authenticated employee.
     * GET /api/v1/attendance/payslips
     */
    public function payslips(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $items = PayrollItem::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->with('payroll')
            ->orderByDesc('created_at')
            ->paginate((int) $request->query('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => collect($items->items())->map(function (PayrollItem $item) {
                return [
                    'id' => $item->id,
                    'period' => $item->payroll?->title ?? ($item->payroll ? "{$item->payroll->period_month}/{$item->payroll->period_year}" : $item->created_at->format('F Y')),
                    'employee_name' => $item->employee_name,
                    'job_title' => $item->job_title,
                    'base_salary' => (float) $item->base_salary,
                    'fixed_allowances' => (float) $item->fixed_allowances,
                    'variable_allowances' => (float) $item->variable_allowances,
                    'overtime_pay' => (float) $item->overtime_pay,
                    'commissions' => (float) $item->commissions,
                    'gross_pay' => (float) $item->gross_pay,
                    'bpjs_tk_employee' => (float) $item->bpjs_tk_employee,
                    'bpjs_kes_employee' => (float) $item->bpjs_kes_employee,
                    'pph21_amount' => (float) $item->pph21_amount,
                    'loan_deduction' => (float) $item->loan_deduction,
                    'other_deductions' => (float) $item->other_deductions,
                    'total_deductions' => (float) $item->total_deductions,
                    'take_home_pay' => (float) $item->take_home_pay,
                    'bank_name' => $item->bank_name,
                    'bank_account_number' => $item->bank_account_number,
                    'bank_account_holder' => $item->bank_account_holder,
                    'status' => $item->status,
                    'created_at' => $item->created_at?->toIso8601String(),
                ];
            }),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'total' => $items->total(),
            ],
        ], 200);
    }

    /**
     * Show single payslip details.
     * GET /api/v1/attendance/payslips/{item}
     */
    public function showPayslip(PayrollItem $item): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        if ($item->business_id !== $business->id) {
            return response()->json(['success' => false, 'message' => 'Slip gaji tidak ditemukan.'], 404);
        }

        if ($item->user_id !== $user->id && ! Context::hasPermission('users.view')) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $item->id,
                'period' => $item->payroll?->title ?? ($item->payroll ? "{$item->payroll->period_month}/{$item->payroll->period_year}" : $item->created_at->format('F Y')),
                'employee_name' => $item->employee_name,
                'job_title' => $item->job_title,
                'join_date' => $item->join_date?->toDateString(),
                'tenure_months' => $item->tenure_months,
                'days_worked' => $item->days_worked,
                'base_salary' => (float) $item->base_salary,
                'fixed_allowances' => (float) $item->fixed_allowances,
                'variable_allowances' => (float) $item->variable_allowances,
                'overtime_pay' => (float) $item->overtime_pay,
                'commissions' => (float) $item->commissions,
                'gross_pay' => (float) $item->gross_pay,
                'bpjs_tk_employee' => (float) $item->bpjs_tk_employee,
                'bpjs_kes_employee' => (float) $item->bpjs_kes_employee,
                'pph21_amount' => (float) $item->pph21_amount,
                'loan_deduction' => (float) $item->loan_deduction,
                'other_deductions' => (float) $item->other_deductions,
                'total_deductions' => (float) $item->total_deductions,
                'take_home_pay' => (float) $item->take_home_pay,
                'bank_name' => $item->bank_name,
                'bank_account_number' => $item->bank_account_number,
                'bank_account_holder' => $item->bank_account_holder,
                'status' => $item->status,
                'created_at' => $item->created_at?->toIso8601String(),
            ],
        ], 200);
    }

    /**
     * Get attendance history with filters and pagination.
     * GET /api/v1/attendance/history
     */
    public function history(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();
        $isManager = Context::hasPermission('users.manage') || $user->isBusinessOwner();

        $query = Attendance::where('business_id', $business->id)
            ->with(['user', 'location', 'exceptionPolicy']);

        // Non-managers can only view their own attendance history
        if (! $isManager || ! $request->filled('user_id')) {
            if (! $isManager) {
                $query->where('user_id', $user->id);
            }
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->query('user_id'));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->query('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->query('end_date'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $attendances = $query->orderByDesc('date')
            ->orderByDesc('clock_in_at')
            ->paginate((int) $request->query('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $attendances->items(),
            'meta' => [
                'current_page' => $attendances->currentPage(),
                'last_page' => $attendances->lastPage(),
                'per_page' => $attendances->perPage(),
                'total' => $attendances->total(),
            ],
        ], 200);
    }

    /**
     * Get attendance summary metrics for today / month.
     * GET /api/v1/attendance/summary
     */
    public function summary(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $date = $request->query('date', now()->toDateString());

        $totalMembers = BusinessMembership::where('business_id', $business->id)->count();

        $presentCount = Attendance::where('business_id', $business->id)
            ->whereDate('date', $date)
            ->whereIn('status', [Attendance::STATUS_PRESENT, Attendance::STATUS_LATE, Attendance::STATUS_HALF_DAY])
            ->count();

        $lateCount = Attendance::where('business_id', $business->id)
            ->whereDate('date', $date)
            ->where(function ($q): void {
                $q->where('status', Attendance::STATUS_LATE)
                    ->orWhere('clock_in_status', Attendance::CLOCK_IN_LATE);
            })
            ->count();

        $onTimeCount = max(0, $presentCount - $lateCount);

        $pendingCorrections = AttendanceCorrection::where('business_id', $business->id)
            ->where('status', AttendanceCorrection::STATUS_PENDING)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $date,
                'total_employees' => $totalMembers,
                'present_count' => $presentCount,
                'on_time_count' => $onTimeCount,
                'late_count' => $lateCount,
                'absent_count' => max(0, $totalMembers - $presentCount),
                'pending_corrections' => $pendingCorrections,
            ],
        ], 200);
    }

    /**
     * Submit attendance correction ticket.
     * POST /api/v1/attendance/corrections
     */
    public function storeCorrection(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $validated = $request->validate([
            'target_date' => ['required', 'date', 'before_or_equal:today'],
            'correction_type' => ['required', 'in:clock_in_only,clock_out_only,full_day,status_only'],
            'proposed_clock_in' => ['nullable', 'date_format:H:i'],
            'proposed_clock_out' => ['nullable', 'date_format:H:i'],
            'proposed_status' => ['nullable', 'in:present,late,half_day,leave,sick'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        try {
            $ticket = $this->attendanceService->createCorrectionTicket($business, $user, $validated);

            return response()->json([
                'success' => true,
                'message' => "Tiket perbaikan absensi {$ticket->correction_number} berhasil diajukan dan sedang menunggu tinjauan HR.",
                'data' => $ticket,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * List attendance corrections with filter.
     * GET /api/v1/attendance/corrections
     */
    public function listCorrections(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();
        $isManager = Context::hasPermission('users.manage') || $user->isBusinessOwner();

        $query = AttendanceCorrection::where('business_id', $business->id)
            ->with(['user', 'attendance', 'reviewer']);

        if (! $isManager) {
            $query->where('user_id', $user->id);
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->query('user_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $corrections = $query->orderByDesc('created_at')
            ->paginate((int) $request->query('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $corrections->items(),
            'meta' => [
                'current_page' => $corrections->currentPage(),
                'last_page' => $corrections->lastPage(),
                'total' => $corrections->total(),
            ],
        ], 200);
    }

    /**
     * Approve attendance correction ticket.
     * POST /api/v1/attendance/corrections/{correction}/approve
     */
    public function approveCorrection(Request $request, AttendanceCorrection $correction): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();
        $isOwner = $user->isBusinessOwner();

        if (! Context::isAdminOrOwner() && ! Context::hasPermission('users.manage') && ! $isOwner) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak otorisasi untuk menyetujui tiket koreksi absensi.'], 403);
        }

        if ($correction->business_id !== $business->id) {
            return response()->json(['success' => false, 'message' => 'Tiket koreksi tidak ditemukan.'], 404);
        }

        try {
            $this->attendanceService->approveCorrection($business, $correction, $user, $request->input('review_notes'));

            return response()->json([
                'success' => true,
                'message' => "Tiket {$correction->correction_number} berhasil disetujui. Data absensi telah diperbarui secara sinkron.",
                'data' => $correction->fresh(['attendance', 'reviewer']),
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Reject attendance correction ticket.
     * POST /api/v1/attendance/corrections/{correction}/reject
     */
    public function rejectCorrection(Request $request, AttendanceCorrection $correction): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();
        $isOwner = $user->isBusinessOwner();

        if (! Context::isAdminOrOwner() && ! Context::hasPermission('users.manage') && ! $isOwner) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak otorisasi untuk menolak tiket koreksi absensi.'], 403);
        }

        if ($correction->business_id !== $business->id) {
            return response()->json(['success' => false, 'message' => 'Tiket koreksi tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        try {
            $this->attendanceService->rejectCorrection($business, $correction, $user, $validated['reason']);

            return response()->json([
                'success' => true,
                'message' => "Tiket {$correction->correction_number} telah ditolak.",
                'data' => $correction->fresh(['reviewer']),
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Request revision on attendance correction ticket.
     * POST /api/v1/attendance/corrections/{correction}/request-revision
     */
    public function requestRevision(Request $request, AttendanceCorrection $correction): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();
        $isOwner = $user->isBusinessOwner();

        if (! Context::isAdminOrOwner() && ! Context::hasPermission('users.manage') && ! $isOwner) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak otorisasi meminta revisi tiket.'], 403);
        }

        if ($correction->business_id !== $business->id) {
            return response()->json(['success' => false, 'message' => 'Tiket koreksi tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'review_notes' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        try {
            $this->attendanceService->requestRevision($business, $correction, $user, $validated['review_notes']);

            return response()->json([
                'success' => true,
                'message' => "Permintaan revisi tiket {$correction->correction_number} berhasil dikirim ke karyawan.",
                'data' => $correction->fresh(['reviewer']),
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update and resubmit attendance correction ticket.
     * PUT /api/v1/attendance/corrections/{correction}
     */
    public function updateCorrection(Request $request, AttendanceCorrection $correction): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        if ($correction->business_id !== $business->id) {
            return response()->json(['success' => false, 'message' => 'Tiket koreksi tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'correction_type' => ['nullable', 'in:clock_in_only,clock_out_only,full_day,status_only'],
            'proposed_clock_in' => ['nullable', 'date_format:H:i'],
            'proposed_clock_out' => ['nullable', 'date_format:H:i'],
            'proposed_status' => ['nullable', 'in:present,late,half_day,leave,sick'],
            'reason' => ['nullable', 'string', 'min:5', 'max:1000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        try {
            $this->attendanceService->updateAndResubmitCorrection($business, $correction, $user, $validated);

            return response()->json([
                'success' => true,
                'message' => "Tiket {$correction->correction_number} berhasil diperbaiki dan diajukan ulang ke HR.",
                'data' => $correction->fresh(),
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Register or update employee face biometric template.
     * POST /api/v1/attendance/face-template/register
     */
    public function registerFace(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $validated = $request->validate([
            'user_id' => ['nullable', 'uuid', 'exists:users,id'],
            'face_data' => ['nullable'],
            'photo' => ['nullable'],
        ]);

        $targetUser = $user;
        if (! empty($validated['user_id']) && $validated['user_id'] !== $user->id) {
            if (! Context::isAdminOrOwner() && ! Context::hasPermission('users.manage') && ! $user->isBusinessOwner()) {
                return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak mendaftarkan biometrik staf lain.'], 403);
            }
            $targetUser = User::findOrFail($validated['user_id']);
        }

        $faceSource = $request->file('photo') ?? $validated['face_data'] ?? $request->input('photo');
        if (empty($faceSource)) {
            return response()->json(['success' => false, 'message' => 'Data biometrik wajah (photo / face_data) wajib disediakan.'], 422);
        }

        try {
            $this->faceVerificationService->registerFaceTemplate($business, $targetUser, $faceSource);

            return response()->json([
                'success' => true,
                'message' => "Template biometrik wajah karyawan {$targetUser->name} berhasil didaftarkan dan dienkripsi secara aman.",
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Test verification of a captured face against registered template.
     * POST /api/v1/attendance/face-template/verify
     */
    public function verifyFace(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $faceSource = $request->file('photo') ?? $request->input('face_data') ?? $request->input('photo');
        if (empty($faceSource)) {
            return response()->json(['success' => false, 'message' => 'Data tangkapan wajah wajib disertakan.'], 422);
        }

        $result = $this->faceVerificationService->verifyFace($business, $user, $faceSource);

        $statusCode = $result['verified'] ? 200 : 422;

        return response()->json([
            'success' => $result['verified'],
            'message' => $result['message'],
            'data' => [
                'verified' => $result['verified'],
                'similarity' => $result['similarity'],
                'similarity_percent' => round($result['similarity'] * 100, 1),
                'threshold_percent' => round(($request->input('threshold') ?? FaceVerificationService::DEFAULT_SIMILARITY_THRESHOLD) * 100),
                'error' => $result['error'] ?? null,
            ],
        ], $statusCode);
    }

    /**
     * Create attendance exception policy (WFH, WFA, Field Work, Business Trip).
     * POST /api/v1/attendance/exceptions
     */
    public function storeException(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        if (! Context::isAdminOrOwner() && ! Context::hasPermission('users.manage') && ! $user->isBusinessOwner()) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak membuat dispensasi presensi.'], 403);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'policy_type' => ['required', 'in:wfh,wfa,field_work,business_trip,temporary_assignment'],
            'name' => ['required', 'string', 'max:255'],
            'reason' => ['required', 'string', 'max:500'],
            'allowed_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'allowed_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'radius_meters' => ['nullable', 'integer', 'min:10', 'max:50000'],
            'effective_from' => ['required', 'date'],
            'effective_until' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        try {
            $exception = $this->exceptionService->createException($business, $validated, $user);

            return response()->json([
                'success' => true,
                'message' => "Kebijakan dispensasi presensi '{$exception->name}' berhasil dibuat.",
                'data' => $exception->load('user'),
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * List attendance exceptions for the business.
     * GET /api/v1/attendance/exceptions
     */
    public function listExceptions(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();
        $isManager = Context::hasPermission('users.manage') || $user->isBusinessOwner();

        $query = AttendanceException::where('business_id', $business->id)
            ->with(['user', 'approver']);

        if (! $isManager) {
            $query->where('user_id', $user->id);
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->query('user_id'));
        }

        if ($request->filled('exception_mode')) {
            $query->where('exception_mode', $request->query('exception_mode'));
        } elseif ($request->filled('policy_type')) {
            $query->where('exception_mode', $request->query('policy_type'));
        }

        $exceptions = $query->orderByDesc('start_date')
            ->paginate((int) $request->query('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $exceptions->items(),
            'meta' => [
                'current_page' => $exceptions->currentPage(),
                'last_page' => $exceptions->lastPage(),
                'total' => $exceptions->total(),
            ],
        ], 200);
    }
}
