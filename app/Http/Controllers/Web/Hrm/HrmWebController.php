<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Hrm;

use App\Domain\Billing\EntitlementService;
use App\Domain\HRM\AttendanceService;
use App\Domain\HRM\Biometrics\FaceVerificationService;
use App\Domain\HRM\Exports\PayrollTwoPartExcelExport;
use App\Domain\HRM\PayrollRunService;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\EmployeeCommission;
use App\Models\EmployeeLoan;
use App\Models\EmployeeSchedule;
use App\Models\Location;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkShift;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class HrmWebController extends Controller
{
    public function __construct(
        private readonly PayrollRunService $payrollRunService,
        private readonly EntitlementService $entitlementService,
        private readonly AttendanceService $attendanceService,
        private readonly FaceVerificationService $faceVerificationService
    ) {}

    /**
     * Display HRM Hub (Employees, Payroll Runs, Loans & Kasbon).
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();
        $tab = $request->query('tab', 'employees');

        // 1. Employees list
        $memberships = BusinessMembership::where('business_id', $business->id)
            ->with(['user', 'customRole', 'defaultShift', 'location'])
            ->get();

        // 2. Payroll runs
        $payrolls = Payroll::where('business_id', $business->id)
            ->with(['processedBy', 'approvedBy'])
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->paginate(15, ['*'], 'payrolls_page')
            ->withQueryString();

        // 3. Employee loans
        $loans = EmployeeLoan::where('business_id', $business->id)
            ->with(['user', 'approver'])
            ->orderByDesc('created_at')
            ->paginate(15, ['*'], 'loans_page')
            ->withQueryString();

        // Summary Statistics
        $totalStaff = $memberships->count();
        $totalBaseSalary = (float) $memberships->sum('base_salary');
        $totalActiveLoans = (float) EmployeeLoan::where('business_id', $business->id)
            ->where('status', EmployeeLoan::STATUS_ACTIVE)
            ->sum('remaining_balance');

        $latestPaidPayroll = Payroll::where('business_id', $business->id)
            ->where('status', Payroll::STATUS_PAID)
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->first();

        $availableRoles = Role::where(function ($q) use ($business): void {
            $q->where('business_id', $business->id)->orWhereNull('business_id');
        })->orderBy('name')->get();

        // 4. Attendances log query with filters & shift eager load
        $today = now()->toDateString();
        $attDate = $request->query('att_date', $today);
        $attUserId = $request->query('att_user_id');
        $attStatus = $request->query('att_status');

        $attendancesQuery = Attendance::where('business_id', $business->id)
            ->with(['user', 'location', 'workShift', 'correction'])
            ->orderByDesc('date')
            ->orderByDesc('created_at');

        if ($attDate) {
            $attendancesQuery->whereDate('date', $attDate);
        }
        if ($attUserId) {
            $attendancesQuery->where('user_id', $attUserId);
        }
        if ($attStatus) {
            $attendancesQuery->where('status', $attStatus);
        }

        $attendances = $attendancesQuery->paginate(15, ['*'], 'attendances_page')->withQueryString();

        // 5. Work Shifts query
        $workShifts = WorkShift::where('business_id', $business->id)
            ->with('location')
            ->orderBy('start_time')
            ->get();

        // 6. Employee Schedules (Rosters & Daily Assignments)
        $schUserId = $request->query('sch_user_id');
        $schShiftId = $request->query('sch_shift_id');
        $schType = $request->query('sch_type');

        $schedulesQuery = EmployeeSchedule::where('business_id', $business->id)
            ->with(['user', 'location', 'workShift'])
            ->orderBy('schedule_type')
            ->orderBy('day_of_week')
            ->orderBy('specific_date');

        if ($schUserId) {
            $schedulesQuery->where('user_id', $schUserId);
        }
        if ($schShiftId) {
            $schedulesQuery->where('work_shift_id', $schShiftId);
        }
        if ($schType) {
            $schedulesQuery->where('schedule_type', $schType);
        }

        $employeeSchedules = $schedulesQuery->paginate(15, ['*'], 'schedules_page')->withQueryString();

        // 7. Attendance correction tickets query
        $corStatus = $request->query('cor_status');
        $correctionsQuery = AttendanceCorrection::where('business_id', $business->id)
            ->with(['user', 'attendance', 'reviewer'])
            ->orderByDesc('created_at');

        if ($corStatus) {
            $correctionsQuery->where('status', $corStatus);
        }

        $corrections = $correctionsQuery->paginate(15, ['*'], 'corrections_page')->withQueryString();

        // 6. Attendance summary metrics for today
        $todayPresentCount = Attendance::where('business_id', $business->id)
            ->whereDate('date', $today)
            ->whereIn('status', [Attendance::STATUS_PRESENT, Attendance::STATUS_LATE, Attendance::STATUS_HALF_DAY])
            ->count();

        $todayLateCount = Attendance::where('business_id', $business->id)
            ->whereDate('date', $today)
            ->where(function ($q) {
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

        // 7. Current user's attendance status today (for clock widget)
        $currentUserAttendance = Attendance::where('business_id', $business->id)
            ->where('user_id', auth()->id())
            ->whereDate('date', $today)
            ->first();

        $currentUserMembership = BusinessMembership::where('business_id', $business->id)
            ->where('user_id', auth()->id())
            ->first();

        // 8. Locations for geofence and office selection
        $locations = Location::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $primaryLocation = $locations->firstWhere('is_primary', true) ?? $locations->first();

        // 9. All business locations for location management tab
        $allLocations = Location::where('business_id', $business->id)
            ->orderByDesc('is_primary')
            ->orderBy('name')
            ->get();

        // 10. Face Biometric & BPJS Stats
        $totalFaceEnrolled = $memberships->filter(fn ($m) => $m->hasFaceRegistered())->count();
        $totalBpjsTk = $memberships->where('bpjs_tk_enabled', true)->count();
        $totalBpjsKes = $memberships->where('bpjs_kes_enabled', true)->count();

        return view('app.hrm.index', compact(
            'business',
            'tab',
            'memberships',
            'payrolls',
            'loans',
            'attendances',
            'corrections',
            'todayPresentCount',
            'todayLateCount',
            'todayFreeCount',
            'pendingCorrectionsCount',
            'currentUserAttendance',
            'currentUserMembership',
            'locations',
            'allLocations',
            'primaryLocation',
            'attDate',
            'attUserId',
            'attStatus',
            'corStatus',
            'totalStaff',
            'totalBaseSalary',
            'totalActiveLoans',
            'latestPaidPayroll',
            'availableRoles',
            'totalFaceEnrolled',
            'totalBpjsTk',
            'totalBpjsKes',
            'workShifts',
            'employeeSchedules',
            'schUserId',
            'schShiftId',
            'schType'
        ));
    }

    /**
     * Store a new employee with full HRM profile and login credentials.
     */
    public function storeEmployee(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless(Context::hasPermission('users.manage'), 403, 'Anda tidak memiliki izin mengelola staf.');

        if (! $this->entitlementService->canAddMember($business)) {
            return back()->with('error', 'Kapasitas anggota staf pada paket Anda telah mencapai batas maksimal. Silakan tingkatkan ke paket Cooca untuk menambah staf tanpa batas.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:6'],
            'role_id' => ['required', 'uuid', 'exists:roles,id'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'employment_type' => ['required', 'in:permanent,contract,daily_worker'],
            'join_date' => ['nullable', 'date'],
            'base_salary' => ['nullable', 'numeric', 'min:0'],
            'daily_rate' => ['nullable', 'numeric', 'min:0'],
            'fixed_allowances' => ['nullable', 'numeric', 'min:0'],
            'variable_allowances' => ['nullable', 'numeric', 'min:0'],
            'tax_ptkp_status' => ['required', 'in:TK/0,TK/1,TK/2,TK/3,K/0,K/1,K/2,K/3'],
            'nik_ktp' => ['nullable', 'string', 'max:30'],
            'npwp' => ['nullable', 'string', 'max:30'],
            'bpjs_tk_enabled' => ['nullable', 'boolean'],
            'bpjs_tk_number' => ['nullable', 'string', 'max:50'],
            'bpjs_kes_enabled' => ['nullable', 'boolean'],
            'bpjs_kes_number' => ['nullable', 'string', 'max:50'],
            'bpjs_dependents_count' => ['nullable', 'integer', 'min:0', 'max:10'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
            'attendance_mode' => ['nullable', 'string', 'in:geofenced,free'],
            'primary_location_id' => ['nullable', 'uuid', 'exists:locations,id'],
            'default_shift_id' => ['nullable', 'uuid', 'exists:work_shifts,id'],
            'photo' => ['nullable'],
        ]);

        $selectedRole = Role::where('id', $validated['role_id'])
            ->where(function ($q) use ($business): void {
                $q->whereNull('business_id')->orWhere('business_id', $business->id);
            })->firstOrFail();

        $user = User::where('email', $validated['email'])->first();
        if (! $user) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['whatsapp_number'] ?? null,
                'password' => Hash::make($validated['password'] ?? 'password123'),
                'email_verified_at' => now(),
            ]);
        }

        if ($business->users()->where('users.id', $user->id)->exists()) {
            return back()->with('error', 'Pengguna dengan email tersebut sudah menjadi anggota tim bisnis ini.');
        }

        $membership = BusinessMembership::create([
            'id' => (string) Str::uuid(),
            'business_id' => $business->id,
            'user_id' => $user->id,
            'role' => $selectedRole->slug,
            'role_id' => $selectedRole->id,
            'job_title' => $validated['job_title'] ?? $selectedRole->name,
            'employment_type' => $validated['employment_type'],
            'join_date' => $validated['join_date'] ?? now()->toDateString(),
            'base_salary' => (float) ($validated['base_salary'] ?? 0.0),
            'daily_rate' => (float) ($validated['daily_rate'] ?? 0.0),
            'fixed_allowances' => (float) ($validated['fixed_allowances'] ?? 0.0),
            'variable_allowances' => (float) ($validated['variable_allowances'] ?? 0.0),
            'tax_ptkp_status' => $validated['tax_ptkp_status'],
            'nik_ktp' => $validated['nik_ktp'] ?? null,
            'npwp' => $validated['npwp'] ?? null,
            'bpjs_tk_enabled' => ! empty($validated['bpjs_tk_enabled']),
            'bpjs_tk_number' => $validated['bpjs_tk_number'] ?? null,
            'bpjs_kes_enabled' => ! empty($validated['bpjs_kes_enabled']),
            'bpjs_kes_number' => $validated['bpjs_kes_number'] ?? null,
            'bpjs_dependents_count' => (int) ($validated['bpjs_dependents_count'] ?? 0),
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'bank_account_holder' => $validated['bank_account_holder'] ?? $validated['name'],
            'whatsapp_number' => $validated['whatsapp_number'] ?? null,
            'attendance_mode' => $validated['attendance_mode'] ?? 'geofenced',
            'primary_location_id' => $validated['primary_location_id'] ?? null,
            'default_shift_id' => $validated['default_shift_id'] ?? null,
        ]);

        // Register initial face photo if provided
        $photoData = $request->file('photo') ?? $request->input('photo');
        if (! empty($photoData)) {
            try {
                $this->faceVerificationService->registerFaceTemplate($business, $user, $photoData);
            } catch (\Throwable $e) {
                // Log and continue gracefully
            }
        }

        return redirect()->route('hrm.index', ['tab' => 'employees'])
            ->with('success', "Data karyawan {$validated['name']} berhasil disimpan dengan profil HRM lengkap.");
    }

    /**
     * Update an employee's HRM salary and personal data.
     */
    public function updateEmployee(Request $request, string $membershipId): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless(Context::hasPermission('users.manage'), 403, 'Anda tidak memiliki izin mengedit profil staf.');

        $membership = BusinessMembership::where('business_id', $business->id)
            ->where('id', $membershipId)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role_id' => ['required', 'uuid', 'exists:roles,id'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'employment_type' => ['required', 'in:permanent,contract,daily_worker'],
            'join_date' => ['nullable', 'date'],
            'base_salary' => ['nullable', 'numeric', 'min:0'],
            'daily_rate' => ['nullable', 'numeric', 'min:0'],
            'fixed_allowances' => ['nullable', 'numeric', 'min:0'],
            'variable_allowances' => ['nullable', 'numeric', 'min:0'],
            'tax_ptkp_status' => ['required', 'in:TK/0,TK/1,TK/2,TK/3,K/0,K/1,K/2,K/3'],
            'nik_ktp' => ['nullable', 'string', 'max:30'],
            'npwp' => ['nullable', 'string', 'max:30'],
            'bpjs_tk_enabled' => ['nullable', 'boolean'],
            'bpjs_tk_number' => ['nullable', 'string', 'max:50'],
            'bpjs_kes_enabled' => ['nullable', 'boolean'],
            'bpjs_kes_number' => ['nullable', 'string', 'max:50'],
            'bpjs_dependents_count' => ['nullable', 'integer', 'min:0', 'max:10'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
            'attendance_mode' => ['nullable', 'string', 'in:geofenced,free'],
            'primary_location_id' => ['nullable', 'uuid', 'exists:locations,id'],
            'default_shift_id' => ['nullable', 'uuid', 'exists:work_shifts,id'],
            'photo' => ['nullable'],
        ]);

        $selectedRole = Role::where('id', $validated['role_id'])
            ->where(function ($q) use ($business): void {
                $q->whereNull('business_id')->orWhere('business_id', $business->id);
            })->firstOrFail();

        $membership->update([
            'role' => $selectedRole->slug,
            'role_id' => $selectedRole->id,
            'job_title' => $validated['job_title'] ?? $selectedRole->name,
            'employment_type' => $validated['employment_type'],
            'join_date' => $validated['join_date'] ?? $membership->join_date,
            'base_salary' => (float) ($validated['base_salary'] ?? 0.0),
            'daily_rate' => (float) ($validated['daily_rate'] ?? 0.0),
            'fixed_allowances' => (float) ($validated['fixed_allowances'] ?? 0.0),
            'variable_allowances' => (float) ($validated['variable_allowances'] ?? 0.0),
            'tax_ptkp_status' => $validated['tax_ptkp_status'],
            'nik_ktp' => $validated['nik_ktp'] ?? $membership->nik_ktp,
            'npwp' => $validated['npwp'] ?? $membership->npwp,
            'bpjs_tk_enabled' => ! empty($validated['bpjs_tk_enabled']),
            'bpjs_tk_number' => $validated['bpjs_tk_number'] ?? $membership->bpjs_tk_number,
            'bpjs_kes_enabled' => ! empty($validated['bpjs_kes_enabled']),
            'bpjs_kes_number' => $validated['bpjs_kes_number'] ?? $membership->bpjs_kes_number,
            'bpjs_dependents_count' => isset($validated['bpjs_dependents_count']) ? (int) $validated['bpjs_dependents_count'] : $membership->bpjs_dependents_count,
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'bank_account_holder' => $validated['bank_account_holder'] ?? $validated['name'],
            'whatsapp_number' => $validated['whatsapp_number'] ?? null,
            'attendance_mode' => $validated['attendance_mode'] ?? $membership->attendance_mode ?? 'geofenced',
            'primary_location_id' => $validated['primary_location_id'] ?? $membership->primary_location_id,
            'default_shift_id' => array_key_exists('default_shift_id', $validated) ? $validated['default_shift_id'] : $membership->default_shift_id,
        ]);

        // Update user name and phone if applicable
        if ($membership->user) {
            $membership->user->update([
                'name' => $validated['name'],
                'phone' => $validated['whatsapp_number'] ?? $membership->user->phone,
            ]);

            // Register/update face photo if provided
            $photoData = $request->file('photo') ?? $request->input('photo');
            if (! empty($photoData)) {
                try {
                    $this->faceVerificationService->registerFaceTemplate($business, $membership->user, $photoData);
                } catch (\Throwable $e) {
                    // Log and continue gracefully
                }
            }
        }

        return redirect()->route('hrm.index', ['tab' => 'employees'])
            ->with('success', "Profil HRM {$validated['name']} berhasil diperbarui.");
    }

    /**
     * Remove employee from business.
     */
    public function destroyEmployee(string $membershipId): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless(Context::hasPermission('users.manage'), 403, 'Anda tidak memiliki izin menghapus staf.');

        $membership = BusinessMembership::where('business_id', $business->id)
            ->where('id', $membershipId)
            ->firstOrFail();

        if ($membership->role === 'owner') {
            return back()->with('error', 'Akun pemilik bisnis utama (Owner) tidak dapat dihapus.');
        }

        $empName = $membership->user?->name ?? 'Karyawan';
        $membership->delete();

        return redirect()->route('hrm.index', ['tab' => 'employees'])
            ->with('success', "Keanggotaan {$empName} berhasil dihapus dari workspace.");
    }

    /**
     * Store an employee loan (kasbon).
     */
    public function storeLoan(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless(Context::hasPermission('users.manage'), 403, 'Anda tidak memiliki izin mencatat kasbon.');

        $validated = $request->validate([
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:10000'],
            'tenor_months' => ['required', 'integer', 'min:1', 'max:60'],
            'loan_date' => ['required', 'date'],
            'purpose' => ['nullable', 'string', 'max:255'],
        ]);

        $amount = (float) $validated['amount'];
        $tenor = (int) $validated['tenor_months'];
        $monthlyInstallment = round($amount / $tenor, 2);

        $loanNumber = 'LN-' . date('Ym') . '-' . strtoupper(Str::random(5));

        EmployeeLoan::create([
            'id' => (string) Str::uuid(),
            'business_id' => $business->id,
            'user_id' => $validated['user_id'],
            'loan_number' => $loanNumber,
            'loan_date' => $validated['loan_date'],
            'amount' => $amount,
            'tenor_months' => $tenor,
            'monthly_installment' => $monthlyInstallment,
            'remaining_balance' => $amount,
            'status' => EmployeeLoan::STATUS_ACTIVE,
            'purpose' => $validated['purpose'] ?? 'Kasbon Operasional',
            'approved_by' => auth()->id(),
        ]);

        return redirect()->route('hrm.index', ['tab' => 'loans'])
            ->with('success', "Pinjaman {$loanNumber} sebesar Rp " . number_format($amount, 0, ',', '.') . " berhasil dicatat.");
    }

    /**
     * Cancel an active or pending loan.
     */
    public function cancelLoan(EmployeeLoan $loan): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless(Context::hasPermission('users.manage'), 403, 'Anda tidak memiliki izin membatalkan kasbon.');

        if ($loan->business_id !== $business->id) {
            abort(404);
        }

        $loan->update(['status' => EmployeeLoan::STATUS_CANCELLED]);

        return redirect()->route('hrm.index', ['tab' => 'loans'])
            ->with('success', "Pinjaman {$loan->loan_number} berhasil dibatalkan.");
    }

    /**
     * Form to create a monthly payroll batch.
     */
    public function createPayroll(Request $request): View
    {
        $business = Context::requireBusiness();
        abort_unless(Context::hasPermission('users.manage'), 403, 'Anda tidak memiliki izin membuat penggajian.');

        $month = (int) $request->query('month', (int) now()->month);
        $year = (int) $request->query('year', (int) now()->year);

        $memberships = BusinessMembership::where('business_id', $business->id)
            ->with(['user', 'customRole'])
            ->get();

        // Check active loans and earned commissions for each user
        $activeLoans = EmployeeLoan::where('business_id', $business->id)
            ->where('status', EmployeeLoan::STATUS_ACTIVE)
            ->where('remaining_balance', '>', 0)
            ->get()
            ->keyBy('user_id');

        $earnedCommissions = EmployeeCommission::where('business_id', $business->id)
            ->whereIn('status', [EmployeeCommission::STATUS_EARNED, EmployeeCommission::STATUS_APPROVED])
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get()
            ->groupBy('user_id')
            ->map(fn ($group) => $group->sum('earned_amount'));

        return view('app.hrm.payroll.create', compact(
            'business',
            'month',
            'year',
            'memberships',
            'activeLoans',
            'earnedCommissions'
        ));
    }

    /**
     * Store and generate the monthly payroll batch.
     */
    public function storePayroll(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless(Context::hasPermission('users.manage'), 403, 'Anda tidak memiliki izin membuat penggajian.');

        $validated = $request->validate([
            'period_month' => ['required', 'integer', 'between:1,12'],
            'period_year' => ['required', 'integer', 'between:2020,2050'],
            'include_thr' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
            'employees' => ['required', 'array'],
        ]);

        $month = (int) $validated['period_month'];
        $year = (int) $validated['period_year'];
        $includeThr = ! empty($validated['include_thr']);

        $payroll = $this->payrollRunService->generatePayrollRun(
            business: $business,
            month: $month,
            year: $year,
            employeeInputs: $validated['employees'],
            operator: auth()->user(),
            includeThr: $includeThr,
            notes: $validated['notes'] ?? null
        );

        return redirect()->route('hrm.payrolls.show', $payroll->id)
            ->with('success', "Batch {$payroll->title} berhasil dikalkulasi dan disimpan sebagai draf.");
    }

    /**
     * Show payroll batch details and all employee payslips.
     */
    public function showPayroll(Payroll $payroll): View
    {
        $business = Context::requireBusiness();
        if ($payroll->business_id !== $business->id) {
            abort(404);
        }

        $payroll->load(['items.user', 'processedBy', 'approvedBy']);

        return view('app.hrm.payroll.show', compact('business', 'payroll'));
    }

    /**
     * Approve payroll batch.
     */
    public function approvePayroll(Payroll $payroll): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless(Context::hasPermission('users.manage'), 403, 'Anda tidak memiliki izin menyetujui penggajian.');

        if ($payroll->business_id !== $business->id) {
            abort(404);
        }

        $this->payrollRunService->approvePayroll($payroll, auth()->user());

        return back()->with('success', "Penggajian {$payroll->title} telah disetujui.");
    }

    /**
     * Mark payroll batch as paid.
     */
    public function payPayroll(Request $request, Payroll $payroll): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless(Context::hasPermission('users.manage'), 403, 'Anda tidak memiliki izin memproses pembayaran gaji.');

        if ($payroll->business_id !== $business->id) {
            abort(404);
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:bank_transfer,cash,multi'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $this->payrollRunService->markPayrollPaid(
            payroll: $payroll,
            paymentMethod: $validated['payment_method'],
            notes: $validated['notes'] ?? null,
            operator: auth()->user()
        );

        return back()->with('success', "Penggajian {$payroll->title} telah ditandai DIBAYAR. Potongan kasbon dan beban keuangan telah terupdate otomatis.");
    }

    /**
     * Delete a draft payroll.
     */
    public function destroyPayroll(Payroll $payroll): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless(Context::hasPermission('users.manage'), 403, 'Anda tidak memiliki izin menghapus penggajian.');

        if ($payroll->business_id !== $business->id) {
            abort(404);
        }

        $title = $payroll->title;
        $this->payrollRunService->deleteDraft($payroll);

        return redirect()->route('hrm.index', ['tab' => 'payrolls'])
            ->with('success', "Draf {$title} berhasil dihapus.");
    }

    /**
     * Show digital payslip Apple HIG.
     */
    public function showPayslip(PayrollItem $item): View
    {
        $business = Context::requireBusiness();
        if ($item->business_id !== $business->id) {
            abort(404);
        }

        $item->load(['payroll.business', 'user']);
        $whatsappMessage = $this->payrollRunService->buildWhatsAppSlipMessage($item);

        return view('app.hrm.payroll.payslip', compact('business', 'item', 'whatsappMessage'));
    }

    /**
     * Public secure payslip view using token.
     */
    public function publicPayslip(string $token): View
    {
        $item = PayrollItem::where('payslip_token', $token)
            ->with(['payroll.business', 'user'])
            ->firstOrFail();

        $business = $item->payroll?->business ?? Business::findOrFail($item->business_id);
        $whatsappMessage = $this->payrollRunService->buildWhatsAppSlipMessage($item);

        return view('app.hrm.payroll.payslip', [
            'business' => $business,
            'item' => $item,
            'whatsappMessage' => $whatsappMessage,
            'isPublic' => true,
        ]);
    }

    /**
     * Export complete 2-Sheet Excel workbook (Executive Summary + Detailed Ledger).
     */
    public function exportPayrollExcel(Payroll $payroll): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $business = Context::requireBusiness();
        if ($payroll->business_id !== $business->id) {
            abort(404);
        }

        $exporter = new PayrollTwoPartExcelExport($payroll, $business);

        return $exporter->download();
    }

    /**
     * Export full payroll batch recap to Excel-compatible CSV with UTF-8 BOM.
     */
    public function exportPayrollCsv(Payroll $payroll): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $business = Context::requireBusiness();
        if ($payroll->business_id !== $business->id) {
            abort(404);
        }

        $payroll->load(['items.user']);
        $filename = "rekap_gaji_{$payroll->period_year}_" . str_pad((string)$payroll->period_month, 2, '0', STR_PAD_LEFT) . ".csv";

        return response()->streamDownload(function () use ($payroll, $business) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            // Company & Batch Header Info
            fputcsv($handle, [$business->name]);
            fputcsv($handle, ["REKAP PENGGAJIAN KARYAWAN - " . Str::upper($payroll->title)]);
            fputcsv($handle, ["Status: " . Str::upper($payroll->status), "Total Karyawan: " . $payroll->total_employees_count]);
            fputcsv($handle, []);

            // Data Table Headers
            fputcsv($handle, [
                'No',
                'Nama Karyawan',
                'NIK / NPWP',
                'Jabatan',
                'Status Kerja',
                'PTKP',
                'Gaji Pokok / Upah (Rp)',
                'Tunj. Tetap (Rp)',
                'Tunj. Variabel (Rp)',
                'Lembur (Rp)',
                'Komisi (Rp)',
                'THR (Rp)',
                'BPJS TK Perusahaan (Rp)',
                'BPJS Kes Perusahaan (Rp)',
                'Total Gaji Bruto (Rp)',
                'Pot. BPJS TK Karyawan (Rp)',
                'Pot. BPJS Kes Karyawan (Rp)',
                'Pot. PPh 21 TER (Rp)',
                'Pot. Kasbon (Rp)',
                'Pot. Lainnya (Rp)',
                'Total Potongan (Rp)',
                'Gaji Bersih / THP (Rp)',
                'Total Beban Perusahaan (Rp)',
            ]);

            $idx = 1;
            foreach ($payroll->items as $item) {
                fputcsv($handle, [
                    $idx++,
                    $item->employee_name,
                    $item->user?->nik ?? $item->user?->npwp ?? '-',
                    $item->job_title ?: 'Staf',
                    $item->employment_type === 'daily_worker' ? 'Pekerja Harian' : 'Karyawan',
                    $item->tax_ptkp_status ?: 'TK/0',
                    (int) ($item->employment_type === 'daily_worker' ? ($item->daily_rate * $item->days_worked) : $item->base_salary),
                    (int) $item->fixed_allowances,
                    (int) $item->variable_allowances,
                    (int) $item->overtime_pay,
                    (int) $item->commissions,
                    (int) $item->thr_allowance,
                    (int) $item->bpjs_tk_company,
                    (int) $item->bpjs_kes_company,
                    (int) $item->gross_salary,
                    (int) $item->bpjs_tk_employee,
                    (int) $item->bpjs_kes_employee,
                    (int) $item->pph21_amount,
                    (int) $item->loan_deduction,
                    (int) $item->other_deductions,
                    (int) $item->total_deductions,
                    (int) $item->take_home_pay,
                    (int) $item->total_company_cost,
                ]);
            }

            // Summary Totals Row
            fputcsv($handle, []);
            fputcsv($handle, [
                'TOTAL',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                (int) $payroll->total_bpjs_company,
                '',
                (int) $payroll->total_gross_pay,
                (int) $payroll->total_bpjs_employee,
                '',
                (int) $payroll->total_pph21,
                (int) $payroll->total_loan_deductions,
                '',
                (int) $payroll->total_deductions,
                (int) $payroll->total_take_home_pay,
                (int) $payroll->total_company_cost,
            ]);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export bank transfer disbursement batch CSV (BCA / Mandiri / Corporate Transfer format).
     */
    public function exportPayrollBankCsv(Payroll $payroll): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $business = Context::requireBusiness();
        if ($payroll->business_id !== $business->id) {
            abort(404);
        }

        $payroll->load(['items.user']);
        $filename = "bank_transfer_payroll_{$payroll->period_year}_" . str_pad((string)$payroll->period_month, 2, '0', STR_PAD_LEFT) . ".csv";

        return response()->streamDownload(function () use ($payroll, $business) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Nomor Rekening',
                'Nama Pemilik Rekening',
                'Nama Bank',
                'Nominal Transfer (Rp)',
                'Berita / Keterangan',
                'Email Notifikasi',
            ]);

            foreach ($payroll->items as $item) {
                if ($item->take_home_pay <= 0) {
                    continue;
                }

                $bankName = $item->user?->bank_name ?? 'BCA';
                $bankAccount = $item->user?->bank_account_number ?? '-';
                $desc = "Gaji {$payroll->formatted_period} - {$business->name}";

                fputcsv($handle, [
                    $bankAccount,
                    $item->employee_name,
                    $bankName,
                    (int) $item->take_home_pay,
                    $desc,
                    $item->user?->email ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Handle daily employee clock-in.
     */
    public function clockIn(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $validated = $request->validate([
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'accuracy' => ['nullable', 'numeric'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable'],
            'face_data' => ['nullable'],
            'face_threshold' => ['nullable', 'numeric'],
            'location_id' => ['nullable', 'uuid', 'exists:locations,id'],
        ]);

        try {
            $attendance = $this->attendanceService->clockIn($business, $user, $validated);
            $feedback = $this->attendanceService->buildClockInFeedback($attendance);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $feedback['message'],
                    'feedback' => $feedback,
                    'attendance' => $attendance,
                ]);
            }

            if ($request->headers->get('referer') && str_contains($request->headers->get('referer'), '/portal')) {
                return redirect()->route('portal')
                    ->with('success', $feedback['message']);
            }

            return redirect()->route('hrm.index', ['tab' => 'attendance'])
                ->with('success', $feedback['message']);
        } catch (ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => collect($e->errors())->flatten()->first(),
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 400);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Handle daily employee clock-out.
     */
    public function clockOut(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $validated = $request->validate([
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'accuracy' => ['nullable', 'numeric'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable'],
            'face_data' => ['nullable'],
            'face_threshold' => ['nullable', 'numeric'],
        ]);

        try {
            $attendance = $this->attendanceService->clockOut($business, $user, $validated);
            $feedback = $this->attendanceService->buildClockOutFeedback($attendance);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $feedback['message'],
                    'feedback' => $feedback,
                    'attendance' => $attendance,
                ]);
            }

            if ($request->headers->get('referer') && str_contains($request->headers->get('referer'), '/portal')) {
                return redirect()->route('portal')
                    ->with('success', $feedback['message']);
            }

            return redirect()->route('hrm.index', ['tab' => 'attendance'])
                ->with('success', $feedback['message']);
        } catch (ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => collect($e->errors())->flatten()->first(),
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 400);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Store attendance correction ticket.
     */
    public function storeCorrection(Request $request): RedirectResponse
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

        $ticket = $this->attendanceService->createCorrectionTicket($business, $user, $validated);

        return redirect()->route('hrm.index', ['tab' => 'corrections'])
            ->with('success', "Tiket perbaikan absensi {$ticket->correction_number} berhasil diajukan dan sedang menunggu tinjauan atasan.");
    }

    /**
     * Approve attendance correction ticket.
     */
    public function approveCorrection(AttendanceCorrection $correction, Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        $isOwner = auth()->user()->isBusinessOwner();
        abort_unless(Context::hasPermission('users.manage') || $isOwner, 403, 'Anda tidak memiliki hak otorisasi untuk menyetujui tiket koreksi absensi.');

        if ($correction->business_id !== $business->id) {
            abort(404);
        }

        $notes = $request->input('review_notes');

        try {
            $this->attendanceService->approveCorrection($business, $correction, auth()->user(), $notes);

            return redirect()->route('hrm.index', ['tab' => 'corrections'])
                ->with('success', "Tiket {$correction->correction_number} berhasil disetujui. Jam kerja dan status absensi telah disinkronkan.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menyetujui tiket: ' . $e->getMessage());
        }
    }

    /**
     * Reject attendance correction ticket.
     */
    public function rejectCorrection(AttendanceCorrection $correction, Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        $isOwner = auth()->user()->isBusinessOwner();
        abort_unless(Context::hasPermission('users.manage') || $isOwner, 403, 'Anda tidak memiliki hak otorisasi untuk menolak tiket koreksi absensi.');

        if ($correction->business_id !== $business->id) {
            abort(404);
        }

        $reason = $request->input('reason');
        if (empty($reason) || strlen(trim((string) $reason)) < 3) {
            return back()->with('error', 'Alasan penolakan tiket koreksi wajib diisi (minimal 3 karakter).');
        }

        try {
            $this->attendanceService->rejectCorrection($business, $correction, auth()->user(), (string) $reason);

            return redirect()->route('hrm.index', ['tab' => 'corrections'])
                ->with('success', "Tiket {$correction->correction_number} telah ditolak dengan catatan alasan.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menolak tiket: ' . $e->getMessage());
        }
    }

    /**
     * Handle biometric face enrollment for an employee from HRM.
     */
    public function registerEmployeeFace(Request $request, string $membershipId): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless(Context::hasPermission('users.manage'), 403, 'Anda tidak memiliki izin mengelola biometrik staf.');

        $membership = BusinessMembership::where('business_id', $business->id)
            ->where('id', $membershipId)
            ->with('user')
            ->firstOrFail();

        $request->validate([
            'photo' => ['required'],
        ]);

        $photoData = $request->file('photo') ?? $request->input('photo');

        try {
            $this->faceVerificationService->registerFaceTemplate(
                $business,
                $membership->user,
                $photoData
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Biometrik wajah {$membership->user->name} berhasil didaftarkan dan dienkripsi (AES-256).",
                    'registered_at' => now('Asia/Jakarta')->toIso8601String(),
                ]);
            }

            return redirect()->route('hrm.index', ['tab' => 'employees'])
                ->with('success', "Biometrik wajah {$membership->user->name} berhasil didaftarkan dan dienkripsi.");
        } catch (ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => collect($e->errors())->flatten()->first(),
                ], 422);
            }
            throw $e;
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mendaftarkan biometrik wajah: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Gagal mendaftarkan biometrik wajah: ' . $e->getMessage());
        }
    }

    /**
     * Store new attendance office location / geofence branch.
     */
    public function storeLocation(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless(Context::hasPermission('users.manage'), 403, 'Anda tidak memiliki izin mengelola lokasi presensi.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'geofence_radius_meters' => ['required', 'integer', 'min:10', 'max:5000'],
            'is_primary' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $isPrimary = ! empty($validated['is_primary']);
        if ($isPrimary) {
            Location::where('business_id', $business->id)->update(['is_primary' => false]);
        }

        Location::create([
            'id' => (string) Str::uuid(),
            'business_id' => $business->id,
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? $business->city ?? 'Jakarta',
            'latitude' => (float) $validated['latitude'],
            'longitude' => (float) $validated['longitude'],
            'geofence_radius_meters' => (int) $validated['geofence_radius_meters'],
            'is_primary' => $isPrimary,
            'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : true,
        ]);

        return redirect()->route('hrm.index', ['tab' => 'locations'])
            ->with('success', "Lokasi kantor \"{$validated['name']}\" berhasil ditambahkan dengan radius {$validated['geofence_radius_meters']} meter.");
    }

    /**
     * Update attendance office location coordinates and geofence radius.
     */
    public function updateLocation(Request $request, Location $location): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless(Context::hasPermission('users.manage'), 403, 'Anda tidak memiliki izin mengelola lokasi presensi.');

        if ($location->business_id !== $business->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'geofence_radius_meters' => ['required', 'integer', 'min:10', 'max:5000'],
            'is_primary' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $isPrimary = ! empty($validated['is_primary']);
        if ($isPrimary && ! $location->is_primary) {
            Location::where('business_id', $business->id)->update(['is_primary' => false]);
        }

        $location->update([
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? $location->city,
            'latitude' => (float) $validated['latitude'],
            'longitude' => (float) $validated['longitude'],
            'geofence_radius_meters' => (int) $validated['geofence_radius_meters'],
            'is_primary' => $isPrimary,
            'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : true,
        ]);

        return redirect()->route('hrm.index', ['tab' => 'locations'])
            ->with('success', "Pengaturan lokasi \"{$location->name}\" berhasil diperbarui.");
    }

    /**
     * Delete attendance location.
     */
    public function destroyLocation(Location $location): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless(Context::hasPermission('users.manage'), 403, 'Anda tidak memiliki izin menghapus lokasi presensi.');

        if ($location->business_id !== $business->id) {
            abort(404);
        }

        $locationCount = Location::where('business_id', $business->id)->count();
        if ($locationCount <= 1) {
            return back()->with('error', 'Tidak dapat menghapus lokasi satu-satunya di workspace bisnis.');
        }

        $locName = $location->name;
        $location->delete();

        return redirect()->route('hrm.index', ['tab' => 'locations'])
            ->with('success', "Lokasi \"{$locName}\" berhasil dihapus.");
    }

    /**
     * Store a new work shift.
     */
    public function storeShift(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless(Context::hasPermission('users.manage'), 403, 'Anda tidak memiliki izin mengelola shift kerja.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:20'],
            'location_id' => ['nullable', 'uuid', 'exists:locations,id'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'break_duration_minutes' => ['nullable', 'integer', 'min:0', 'max:480'],
            'grace_period_minutes' => ['nullable', 'integer', 'min:0', 'max:120'],
            'is_overnight' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'color' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $isOvernight = ! empty($validated['is_overnight']) || strcmp($validated['start_time'], $validated['end_time']) > 0;

        WorkShift::create([
            'id' => (string) Str::uuid(),
            'business_id' => $business->id,
            'location_id' => $validated['location_id'] ?? null,
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'break_duration_minutes' => (int) ($validated['break_duration_minutes'] ?? 0),
            'grace_period_minutes' => (int) ($validated['grace_period_minutes'] ?? 0),
            'is_overnight' => $isOvernight,
            'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : true,
            'color' => $validated['color'] ?? '#3B82F6',
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('hrm.index', ['tab' => 'shifts'])
            ->with('success', "Shift kerja \"{$validated['name']}\" ({$validated['start_time']} - {$validated['end_time']}) berhasil ditambahkan.");
    }

    /**
     * Update an existing work shift.
     */
    public function updateShift(Request $request, WorkShift $shift): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless(Context::hasPermission('users.manage'), 403, 'Anda tidak memiliki izin mengedit shift kerja.');

        if ($shift->business_id !== $business->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:20'],
            'location_id' => ['nullable', 'uuid', 'exists:locations,id'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'break_duration_minutes' => ['nullable', 'integer', 'min:0', 'max:480'],
            'grace_period_minutes' => ['nullable', 'integer', 'min:0', 'max:120'],
            'is_overnight' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'color' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $isOvernight = ! empty($validated['is_overnight']) || strcmp($validated['start_time'], $validated['end_time']) > 0;

        $shift->update([
            'location_id' => $validated['location_id'] ?? null,
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'break_duration_minutes' => (int) ($validated['break_duration_minutes'] ?? 0),
            'grace_period_minutes' => (int) ($validated['grace_period_minutes'] ?? 0),
            'is_overnight' => $isOvernight,
            'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : $shift->is_active,
            'color' => $validated['color'] ?? $shift->color,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('hrm.index', ['tab' => 'shifts'])
            ->with('success', "Shift kerja \"{$shift->name}\" berhasil diperbarui.");
    }

    /**
     * Delete a work shift.
     */
    public function destroyShift(WorkShift $shift): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless(Context::hasPermission('users.manage'), 403, 'Anda tidak memiliki izin menghapus shift kerja.');

        if ($shift->business_id !== $business->id) {
            abort(404);
        }

        // Safety check: if used in attendances, deactivate instead of hard delete to preserve historical integrity
        if ($shift->attendances()->exists() || $shift->schedules()->exists()) {
            $shift->update(['is_active' => false]);

            return redirect()->route('hrm.index', ['tab' => 'shifts'])
                ->with('success', "Shift \"{$shift->name}\" telah dinonaktifkan karena memiliki riwayat absensi atau jadwal karyawan.");
        }

        $shiftName = $shift->name;
        $shift->delete();

        return redirect()->route('hrm.index', ['tab' => 'shifts'])
            ->with('success', "Shift kerja \"{$shiftName}\" berhasil dihapus.");
    }

    /**
     * Store a new employee schedule (roster or specific date).
     */
    public function storeSchedule(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless(Context::hasPermission('users.manage'), 403, 'Anda tidak memiliki izin mengelola jadwal kerja.');

        $validated = $request->validate([
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'location_id' => ['nullable', 'uuid', 'exists:locations,id'],
            'work_shift_id' => ['nullable', 'uuid', 'exists:work_shifts,id'],
            'schedule_type' => ['required', 'in:recurring,specific_date'],
            'day_of_week' => ['nullable', 'required_if:schedule_type,recurring', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'specific_date' => ['nullable', 'required_if:schedule_type,specific_date', 'date'],
            'is_off_day' => ['nullable', 'boolean'],
            'effective_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:effective_date'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $isOffDay = ! empty($validated['is_off_day']);
        if (! $isOffDay && empty($validated['work_shift_id'])) {
            return back()->with('error', 'Pilih shift kerja jika bukan hari libur / off.');
        }

        EmployeeSchedule::create([
            'id' => (string) Str::uuid(),
            'business_id' => $business->id,
            'user_id' => $validated['user_id'],
            'location_id' => $validated['location_id'] ?? null,
            'work_shift_id' => $isOffDay ? null : ($validated['work_shift_id'] ?? null),
            'schedule_type' => $validated['schedule_type'],
            'day_of_week' => $validated['schedule_type'] === EmployeeSchedule::TYPE_RECURRING ? ($validated['day_of_week'] ?? null) : null,
            'specific_date' => $validated['schedule_type'] === EmployeeSchedule::TYPE_SPECIFIC_DATE ? ($validated['specific_date'] ?? null) : null,
            'is_off_day' => $isOffDay,
            'effective_date' => $validated['effective_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('hrm.index', ['tab' => 'schedules'])
            ->with('success', 'Jadwal kerja karyawan berhasil disimpan.');
    }

    /**
     * Update an employee schedule.
     */
    public function updateSchedule(Request $request, EmployeeSchedule $schedule): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless(Context::hasPermission('users.manage'), 403, 'Anda tidak memiliki izin mengedit jadwal kerja.');

        if ($schedule->business_id !== $business->id) {
            abort(404);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'location_id' => ['nullable', 'uuid', 'exists:locations,id'],
            'work_shift_id' => ['nullable', 'uuid', 'exists:work_shifts,id'],
            'schedule_type' => ['required', 'in:recurring,specific_date'],
            'day_of_week' => ['nullable', 'required_if:schedule_type,recurring', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'specific_date' => ['nullable', 'required_if:schedule_type,specific_date', 'date'],
            'is_off_day' => ['nullable', 'boolean'],
            'effective_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:effective_date'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $isOffDay = ! empty($validated['is_off_day']);
        if (! $isOffDay && empty($validated['work_shift_id'])) {
            return back()->with('error', 'Pilih shift kerja jika bukan hari libur / off.');
        }

        $schedule->update([
            'user_id' => $validated['user_id'],
            'location_id' => $validated['location_id'] ?? null,
            'work_shift_id' => $isOffDay ? null : ($validated['work_shift_id'] ?? null),
            'schedule_type' => $validated['schedule_type'],
            'day_of_week' => $validated['schedule_type'] === EmployeeSchedule::TYPE_RECURRING ? ($validated['day_of_week'] ?? null) : null,
            'specific_date' => $validated['schedule_type'] === EmployeeSchedule::TYPE_SPECIFIC_DATE ? ($validated['specific_date'] ?? null) : null,
            'is_off_day' => $isOffDay,
            'effective_date' => $validated['effective_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('hrm.index', ['tab' => 'schedules'])
            ->with('success', 'Jadwal kerja karyawan berhasil diperbarui.');
    }

    /**
     * Delete an employee schedule.
     */
    public function destroySchedule(EmployeeSchedule $schedule): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless(Context::hasPermission('users.manage'), 403, 'Anda tidak memiliki izin menghapus jadwal kerja.');

        if ($schedule->business_id !== $business->id) {
            abort(404);
        }

        $schedule->delete();

        return redirect()->route('hrm.index', ['tab' => 'schedules'])
            ->with('success', 'Jadwal kerja karyawan berhasil dihapus.');
    }
}
