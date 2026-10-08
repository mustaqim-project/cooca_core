<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Hrm;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\EmployeeLoan;
use App\Models\EmployeeSchedule;
use App\Models\Location;
use App\Models\Payroll;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkShift;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class HrmApiController extends Controller
{
    /**
     * Unified HRM Hub Dashboard: Metrics, Employees, Attendances, Corrections, Shifts, Payrolls, Loans.
     * GET /api/v1/hrm/hub
     */
    public function hub(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $today = now()->toDateString();

        // 1. Memberships / Staff
        $memberships = BusinessMembership::where('business_id', $business->id)
            ->with(['user', 'location', 'defaultShift', 'customRole'])
            ->get();

        // Ensure default shifts exist if none found
        $shiftsCount = WorkShift::where('business_id', $business->id)->count();
        if ($shiftsCount === 0) {
            $this->ensureDefaultShifts($business);
        }

        $workShifts = WorkShift::where('business_id', $business->id)
            ->with('location')
            ->orderBy('start_time')
            ->get();

        // 2. Attendance Summary Today
        $todayPresentCount = Attendance::where('business_id', $business->id)
            ->whereDate('date', $today)
            ->whereIn('status', [Attendance::STATUS_PRESENT, Attendance::STATUS_LATE, Attendance::STATUS_HALF_DAY])
            ->count();

        $todayLateCount = Attendance::where('business_id', $business->id)
            ->whereDate('date', $today)
            ->where(function ($q): void {
                $q->where('status', Attendance::STATUS_LATE)
                    ->orWhere('clock_in_status', Attendance::CLOCK_IN_LATE);
            })
            ->count();

        $todayFreeCount = Attendance::where('business_id', $business->id)
            ->whereDate('date', $today)
            ->where('clock_in_status', Attendance::CLOCK_IN_FREE_LOCATION)
            ->count();

        $pendingCorrectionsCount = AttendanceCorrection::where('business_id', $business->id)
            ->where('status', AttendanceCorrection::STATUS_PENDING)
            ->count();

        // 3. Loans & Payrolls
        $loans = EmployeeLoan::where('business_id', $business->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->get();

        $payrolls = Payroll::where('business_id', $business->id)
            ->with(['processedBy', 'approvedBy'])
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->take(12)
            ->get();

        $latestPaidPayroll = $payrolls->firstWhere('status', Payroll::STATUS_PAID);

        // 4. Corrections
        $corrections = AttendanceCorrection::where('business_id', $business->id)
            ->with(['user', 'reviewer'])
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        // 5. Today attendances
        $todayAttendances = Attendance::where('business_id', $business->id)
            ->whereDate('date', $today)
            ->with(['user', 'location', 'workShift'])
            ->orderByDesc('created_at')
            ->get();

        // 6. Locations & Roles
        $locations = Location::where('business_id', $business->id)->where('is_active', true)->get();
        $roles = Role::where(function ($q) use ($business): void {
            $q->where('business_id', $business->id)->orWhereNull('business_id');
        })->orderBy('name')->get();

        // Formatted Employees
        $formattedEmployees = $memberships->map(function ($m): array {
            return [
                'id' => (string) $m->id,
                'user_id' => (string) $m->user_id,
                'name' => $m->user?->name ?? 'Tanpa Nama',
                'email' => $m->user?->email ?? '-',
                'phone' => $m->user?->phone ?? $m->whatsapp_number ?? '-',
                'avatar_url' => $m->user?->avatar_url,
                'role' => (string) $m->role,
                'role_name' => $m->customRole?->name ?? ucfirst((string) $m->role),
                'job_title' => $m->job_title ?: ($m->customRole?->name ?? ucfirst((string) $m->role)),
                'employment_type' => $m->employment_type ?? 'permanent',
                'join_date' => $m->join_date ? $m->join_date->format('Y-m-d') : null,
                'tenure' => $m->join_date ? $m->join_date->diffForHumans(null, true) : 'Baru bergabung',
                'base_salary' => (float) $m->base_salary,
                'daily_rate' => (float) $m->daily_rate,
                'fixed_allowances' => (float) $m->fixed_allowances,
                'variable_allowances' => (float) $m->variable_allowances,
                'bank_name' => $m->bank_name ?? '-',
                'bank_account_number' => $m->bank_account_number ?? '-',
                'bank_account_holder' => $m->bank_account_holder ?? '-',
                'nik_ktp' => $m->nik_ktp ?? '-',
                'npwp' => $m->npwp ?? '-',
                'tax_ptkp_status' => $m->tax_ptkp_status ?? 'TK/0',
                'bpjs_tk_enabled' => (bool) $m->bpjs_tk_enabled,
                'bpjs_tk_number' => $m->bpjs_tk_number ?? '-',
                'bpjs_kes_enabled' => (bool) $m->bpjs_kes_enabled,
                'bpjs_kes_number' => $m->bpjs_kes_number ?? '-',
                'bpjs_dependents_count' => (int) $m->bpjs_dependents_count,
                'has_face_registered' => $m->hasFaceRegistered(),
                'face_registered_at' => $m->face_registered_at ? $m->face_registered_at->format('d M Y H:i') : null,
                'location_id' => $m->primary_location_id,
                'location_name' => $m->location?->name ?? 'Semua Lokasi',
                'default_shift_id' => $m->default_shift_id,
                'default_shift_name' => $m->defaultShift?->name ?? 'Shift Normal',
                'attendance_mode' => $m->attendance_mode ?? 'geofenced',
            ];
        });

        return response()->json([
            'success' => true,
            'business' => [
                'id' => (string) $business->id,
                'name' => $business->name,
            ],
            'kpis' => [
                'total_staff' => $memberships->count(),
                'total_base_salary' => (float) $memberships->sum('base_salary'),
                'total_active_loans' => (float) $loans->where('status', EmployeeLoan::STATUS_ACTIVE)->sum('remaining_balance'),
                'latest_payroll_thp' => $latestPaidPayroll ? (float) $latestPaidPayroll->total_take_home_pay : 0.0,
                'latest_payroll_period' => $latestPaidPayroll ? $latestPaidPayroll->formatted_period : 'Belum Ada',
                'today_present' => $todayPresentCount,
                'today_late' => $todayLateCount,
                'today_free_location' => $todayFreeCount,
                'pending_corrections_count' => $pendingCorrectionsCount,
                'total_face_enrolled' => $memberships->filter(fn ($m) => $m->hasFaceRegistered())->count(),
                'total_bpjs_tk' => $memberships->where('bpjs_tk_enabled', true)->count(),
                'total_bpjs_kes' => $memberships->where('bpjs_kes_enabled', true)->count(),
            ],
            'employees' => $formattedEmployees,
            'today_attendances' => $todayAttendances->map(function ($att): array {
                return [
                    'id' => (string) $att->id,
                    'user_id' => (string) $att->user_id,
                    'employee_name' => $att->user?->name ?? 'Staf',
                    'clock_in_at' => $att->clock_in_at ? Carbon::parse($att->clock_in_at)->format('H:i') : null,
                    'clock_out_at' => $att->clock_out_at ? Carbon::parse($att->clock_out_at)->format('H:i') : null,
                    'status' => $att->status,
                    'clock_in_status' => $att->clock_in_status,
                    'minutes_late' => (int) ($att->minutes_late ?? 0),
                    'shift_name' => $att->workShift?->name ?? 'Reguler',
                    'location_name' => $att->location?->name ?? 'Outlet',
                ];
            }),
            'corrections' => $corrections->map(function ($c): array {
                return [
                    'id' => (string) $c->id,
                    'user_id' => (string) $c->user_id,
                    'employee_name' => $c->user?->name ?? 'Staf',
                    'target_date' => $c->target_date ? Carbon::parse($c->target_date)->format('d M Y') : '-',
                    'correction_type' => $c->correction_type,
                    'proposed_clock_in' => $c->proposed_clock_in ? substr((string) $c->proposed_clock_in, 0, 5) : '--:--',
                    'proposed_clock_out' => $c->proposed_clock_out ? substr((string) $c->proposed_clock_out, 0, 5) : '--:--',
                    'reason' => $c->reason ?? '-',
                    'status' => $c->status,
                    'reviewer_name' => $c->reviewer?->name,
                    'created_at' => $c->created_at ? $c->created_at->format('d M Y H:i') : '-',
                ];
            }),
            'shifts' => $workShifts->map(function ($s): array {
                return [
                    'id' => (string) $s->id,
                    'name' => $s->name,
                    'code' => $s->code,
                    'start_time' => substr((string) $s->start_time, 0, 5),
                    'end_time' => substr((string) $s->end_time, 0, 5),
                    'break_duration_minutes' => (int) $s->break_duration_minutes,
                    'grace_period_minutes' => (int) $s->grace_period_minutes,
                    'is_overnight' => (bool) $s->is_overnight,
                    'is_active' => (bool) $s->is_active,
                    'color' => $s->color ?? '#3B82F6',
                    'location_name' => $s->location?->name ?? 'Semua Lokasi',
                ];
            }),
            'payrolls' => $payrolls->map(function ($p): array {
                return [
                    'id' => (string) $p->id,
                    'title' => $p->title,
                    'period' => $p->formatted_period ?? ($p->period_month . '/' . $p->period_year),
                    'period_month' => (int) $p->period_month,
                    'period_year' => (int) $p->period_year,
                    'status' => $p->status,
                    'total_gross_pay' => (float) $p->total_gross_pay,
                    'total_take_home_pay' => (float) $p->total_take_home_pay,
                    'total_employees_count' => (int) $p->total_employees_count,
                    'paid_at' => $p->paid_at ? Carbon::parse($p->paid_at)->format('d M Y') : null,
                ];
            }),
            'loans' => $loans->map(function ($l): array {
                return [
                    'id' => (string) $l->id,
                    'loan_number' => $l->loan_number,
                    'user_id' => (string) $l->user_id,
                    'employee_name' => $l->user?->name ?? 'Staf',
                    'amount' => (float) $l->amount,
                    'tenor_months' => (int) $l->tenor_months,
                    'monthly_installment' => (float) $l->monthly_installment,
                    'remaining_balance' => (float) $l->remaining_balance,
                    'status' => $l->status,
                    'purpose' => $l->purpose ?? 'Kasbon',
                    'loan_date' => $l->loan_date ? Carbon::parse($l->loan_date)->format('d M Y') : '-',
                ];
            }),
            'roles' => $roles->map(fn ($r) => ['id' => (string) $r->id, 'name' => $r->name]),
            'locations' => $locations->map(fn ($loc) => ['id' => (string) $loc->id, 'name' => $loc->name]),
        ]);
    }

    /**
     * Store new employee.
     * POST /api/v1/hrm/employees
     */
    public function storeEmployee(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['nullable', 'string', 'in:owner,manager,cashier,staff'],
            'role_id' => ['nullable', 'uuid', 'exists:roles,id'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'employment_type' => ['nullable', 'in:permanent,contract,daily_worker'],
            'join_date' => ['nullable', 'date'],
            'base_salary' => ['nullable', 'numeric', 'min:0'],
            'daily_rate' => ['nullable', 'numeric', 'min:0'],
            'fixed_allowances' => ['nullable', 'numeric', 'min:0'],
            'tax_ptkp_status' => ['nullable', 'string'],
            'nik_ktp' => ['nullable', 'string', 'max:30'],
            'npwp' => ['nullable', 'string', 'max:30'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
            'bpjs_tk_enabled' => ['nullable', 'boolean'],
            'bpjs_kes_enabled' => ['nullable', 'boolean'],
            'primary_location_id' => ['nullable', 'uuid', 'exists:locations,id'],
            'default_shift_id' => ['nullable', 'uuid', 'exists:work_shifts,id'],
        ]);

        $user = User::where('email', $validated['email'])->first();
        if (! $user) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password'] ?? 'password123'),
                'email_verified_at' => now(),
            ]);
        }

        // Check if already member
        $existing = BusinessMembership::where('business_id', $business->id)->where('user_id', $user->id)->first();
        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan dengan email ini sudah terdaftar di unit usaha ini.',
            ], 422);
        }

        $membership = BusinessMembership::create([
            'id' => (string) Str::uuid(),
            'business_id' => $business->id,
            'user_id' => $user->id,
            'role' => $validated['role'] ?? 'staff',
            'role_id' => $validated['role_id'] ?? null,
            'job_title' => $validated['job_title'] ?? ($validated['role'] ?? 'Staff'),
            'employment_type' => $validated['employment_type'] ?? 'permanent',
            'join_date' => $validated['join_date'] ?? now()->toDateString(),
            'base_salary' => (float) ($validated['base_salary'] ?? 0),
            'daily_rate' => (float) ($validated['daily_rate'] ?? 0),
            'fixed_allowances' => (float) ($validated['fixed_allowances'] ?? 0),
            'tax_ptkp_status' => $validated['tax_ptkp_status'] ?? 'TK/0',
            'nik_ktp' => $validated['nik_ktp'] ?? null,
            'npwp' => $validated['npwp'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'bank_account_holder' => $validated['bank_account_holder'] ?? $user->name,
            'whatsapp_number' => $validated['phone'] ?? null,
            'bpjs_tk_enabled' => ! empty($validated['bpjs_tk_enabled']),
            'bpjs_kes_enabled' => ! empty($validated['bpjs_kes_enabled']),
            'primary_location_id' => $validated['primary_location_id'] ?? null,
            'default_shift_id' => $validated['default_shift_id'] ?? null,
            'attendance_mode' => BusinessMembership::ATTENDANCE_MODE_GEOFENCED,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Karyawan berhasil ditambahkan.',
            'employee_id' => $membership->id,
        ], 201);
    }

    /**
     * Update employee profile & payroll settings.
     * PUT /api/v1/hrm/employees/{membership}
     */
    public function updateEmployee(Request $request, string $id): JsonResponse
    {
        $business = Context::requireBusiness();
        $membership = BusinessMembership::where('business_id', $business->id)->where('id', $id)->firstOrFail();

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'string', 'in:owner,manager,cashier,staff'],
            'employment_type' => ['nullable', 'in:permanent,contract,daily_worker'],
            'join_date' => ['nullable', 'date'],
            'base_salary' => ['nullable', 'numeric', 'min:0'],
            'daily_rate' => ['nullable', 'numeric', 'min:0'],
            'fixed_allowances' => ['nullable', 'numeric', 'min:0'],
            'tax_ptkp_status' => ['nullable', 'string'],
            'nik_ktp' => ['nullable', 'string', 'max:30'],
            'npwp' => ['nullable', 'string', 'max:30'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
            'bpjs_tk_enabled' => ['nullable', 'boolean'],
            'bpjs_kes_enabled' => ['nullable', 'boolean'],
            'primary_location_id' => ['nullable', 'uuid', 'exists:locations,id'],
            'default_shift_id' => ['nullable', 'uuid', 'exists:work_shifts,id'],
        ]);

        if (isset($validated['name']) && $membership->user) {
            $membership->user->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? $membership->user->phone,
            ]);
        }

        $membership->update(array_filter([
            'job_title' => $validated['job_title'] ?? $membership->job_title,
            'role' => $validated['role'] ?? $membership->role,
            'employment_type' => $validated['employment_type'] ?? $membership->employment_type,
            'join_date' => $validated['join_date'] ?? $membership->join_date,
            'base_salary' => isset($validated['base_salary']) ? (float) $validated['base_salary'] : $membership->base_salary,
            'daily_rate' => isset($validated['daily_rate']) ? (float) $validated['daily_rate'] : $membership->daily_rate,
            'fixed_allowances' => isset($validated['fixed_allowances']) ? (float) $validated['fixed_allowances'] : $membership->fixed_allowances,
            'tax_ptkp_status' => $validated['tax_ptkp_status'] ?? $membership->tax_ptkp_status,
            'nik_ktp' => $validated['nik_ktp'] ?? $membership->nik_ktp,
            'npwp' => $validated['npwp'] ?? $membership->npwp,
            'bank_name' => $validated['bank_name'] ?? $membership->bank_name,
            'bank_account_number' => $validated['bank_account_number'] ?? $membership->bank_account_number,
            'bank_account_holder' => $validated['bank_account_holder'] ?? $membership->bank_account_holder,
            'whatsapp_number' => $validated['phone'] ?? $membership->whatsapp_number,
            'bpjs_tk_enabled' => isset($validated['bpjs_tk_enabled']) ? (bool) $validated['bpjs_tk_enabled'] : $membership->bpjs_tk_enabled,
            'bpjs_kes_enabled' => isset($validated['bpjs_kes_enabled']) ? (bool) $validated['bpjs_kes_enabled'] : $membership->bpjs_kes_enabled,
            'primary_location_id' => $validated['primary_location_id'] ?? $membership->primary_location_id,
            'default_shift_id' => $validated['default_shift_id'] ?? $membership->default_shift_id,
        ], fn ($val) => $val !== null));

        return response()->json([
            'success' => true,
            'message' => 'Profil karyawan berhasil diperbarui.',
        ]);
    }

    /**
     * Store new Work Shift.
     * POST /api/v1/hrm/shifts
     */
    public function storeShift(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:20'],
            'start_time' => ['required', 'string'],
            'end_time' => ['required', 'string'],
            'break_duration_minutes' => ['nullable', 'integer', 'min:0'],
            'grace_period_minutes' => ['nullable', 'integer', 'min:0'],
            'color' => ['nullable', 'string', 'max:20'],
            'location_id' => ['nullable', 'uuid', 'exists:locations,id'],
        ]);

        $isOvernight = strcmp($validated['start_time'], $validated['end_time']) > 0;

        $shift = WorkShift::create([
            'id' => (string) Str::uuid(),
            'business_id' => $business->id,
            'location_id' => $validated['location_id'] ?? null,
            'name' => $validated['name'],
            'code' => $validated['code'] ?? strtoupper(substr($validated['name'], 0, 3)),
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'break_duration_minutes' => (int) ($validated['break_duration_minutes'] ?? 60),
            'grace_period_minutes' => (int) ($validated['grace_period_minutes'] ?? 15),
            'is_overnight' => $isOvernight,
            'is_active' => true,
            'color' => $validated['color'] ?? '#3B82F6',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shift kerja berhasil ditambahkan.',
            'shift' => $shift,
        ], 201);
    }

    /**
     * Store an employee loan (kasbon).
     * POST /api/v1/hrm/loans
     */
    public function storeLoan(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:10000'],
            'tenor_months' => ['required', 'integer', 'min:1', 'max:60'],
            'loan_date' => ['nullable', 'date'],
            'purpose' => ['nullable', 'string', 'max:255'],
        ]);

        $amount = (float) $validated['amount'];
        $tenor = (int) $validated['tenor_months'];
        $monthlyInstallment = round($amount / $tenor, 2);
        $loanNumber = 'LN-' . date('Ym') . '-' . strtoupper(Str::random(5));

        $loan = EmployeeLoan::create([
            'id' => (string) Str::uuid(),
            'business_id' => $business->id,
            'user_id' => $validated['user_id'],
            'loan_number' => $loanNumber,
            'loan_date' => $validated['loan_date'] ?? now()->toDateString(),
            'amount' => $amount,
            'tenor_months' => $tenor,
            'monthly_installment' => $monthlyInstallment,
            'remaining_balance' => $amount,
            'status' => EmployeeLoan::STATUS_ACTIVE,
            'purpose' => $validated['purpose'] ?? 'Kasbon Karyawan',
            'approved_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan kasbon berhasil dicatat dan disetujui.',
            'loan' => $loan,
        ], 201);
    }

    /**
     * Approve an attendance correction ticket.
     * POST /api/v1/hrm/corrections/{correction}/approve
     */
    public function approveCorrection(Request $request, string $id): JsonResponse
    {
        $business = Context::requireBusiness();
        $correction = AttendanceCorrection::where('business_id', $business->id)->where('id', $id)->firstOrFail();

        $correction->update([
            'status' => AttendanceCorrection::STATUS_APPROVED,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'notes' => $request->input('notes', 'Disetujui oleh Administrator/HR'),
        ]);

        // Sync or update attendance record if exists
        if ($correction->attendance_id) {
            $attendance = Attendance::find($correction->attendance_id);
            if ($attendance) {
                if ($correction->proposed_clock_in) {
                    $attendance->clock_in_at = Carbon::parse($attendance->date->toDateString() . ' ' . $correction->proposed_clock_in);
                    $attendance->status = Attendance::STATUS_PRESENT;
                }
                if ($correction->proposed_clock_out) {
                    $attendance->clock_out_at = Carbon::parse($attendance->date->toDateString() . ' ' . $correction->proposed_clock_out);
                }
                $attendance->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Tiket koreksi presensi berhasil disetujui.',
        ]);
    }

    /**
     * Reject an attendance correction ticket.
     * POST /api/v1/hrm/corrections/{correction}/reject
     */
    public function rejectCorrection(Request $request, string $id): JsonResponse
    {
        $business = Context::requireBusiness();
        $correction = AttendanceCorrection::where('business_id', $business->id)->where('id', $id)->firstOrFail();

        $correction->update([
            'status' => AttendanceCorrection::STATUS_REJECTED,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'notes' => $request->input('notes', 'Ditolak oleh Administrator/HR'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tiket koreksi presensi telah ditolak.',
        ]);
    }

    /**
     * Ensure standard shifts are seeded for a business.
     */
    private function ensureDefaultShifts(Business $business): void
    {
        WorkShift::create([
            'id' => (string) Str::uuid(),
            'business_id' => $business->id,
            'name' => 'Shift Pagi (General)',
            'code' => 'SPG',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'break_duration_minutes' => 60,
            'grace_period_minutes' => 15,
            'is_overnight' => false,
            'is_active' => true,
            'color' => '#3B82F6',
            'description' => 'Jam operasional kantor & toko reguler',
        ]);

        WorkShift::create([
            'id' => (string) Str::uuid(),
            'business_id' => $business->id,
            'name' => 'Shift Sore / Malam',
            'code' => 'SML',
            'start_time' => '13:00',
            'end_time' => '21:00',
            'break_duration_minutes' => 45,
            'grace_period_minutes' => 15,
            'is_overnight' => false,
            'is_active' => true,
            'color' => '#10B981',
            'description' => 'Shift operasional sore hingga malam',
        ]);
    }
}
