<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Hrm;

use App\Domain\Billing\EntitlementService;
use App\Domain\HRM\PayrollRunService;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\EmployeeCommission;
use App\Models\EmployeeLoan;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class HrmWebController extends Controller
{
    public function __construct(
        private readonly PayrollRunService $payrollRunService,
        private readonly EntitlementService $entitlementService
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
            ->with(['user', 'customRole'])
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

        return view('app.hrm.index', compact(
            'business',
            'tab',
            'memberships',
            'payrolls',
            'loans',
            'totalStaff',
            'totalBaseSalary',
            'totalActiveLoans',
            'latestPaidPayroll',
            'availableRoles'
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
            'bpjs_tk_enabled' => ['nullable', 'boolean'],
            'bpjs_kes_enabled' => ['nullable', 'boolean'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
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

        BusinessMembership::create([
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
            'bpjs_tk_enabled' => ! empty($validated['bpjs_tk_enabled']),
            'bpjs_kes_enabled' => ! empty($validated['bpjs_kes_enabled']),
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'bank_account_holder' => $validated['bank_account_holder'] ?? $validated['name'],
            'whatsapp_number' => $validated['whatsapp_number'] ?? null,
        ]);

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
            'bpjs_tk_enabled' => ['nullable', 'boolean'],
            'bpjs_kes_enabled' => ['nullable', 'boolean'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
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
            'bpjs_tk_enabled' => ! empty($validated['bpjs_tk_enabled']),
            'bpjs_kes_enabled' => ! empty($validated['bpjs_kes_enabled']),
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'bank_account_holder' => $validated['bank_account_holder'] ?? $validated['name'],
            'whatsapp_number' => $validated['whatsapp_number'] ?? null,
        ]);

        // Update user name and phone if applicable
        if ($membership->user) {
            $membership->user->update([
                'name' => $validated['name'],
                'phone' => $validated['whatsapp_number'] ?? $membership->user->phone,
            ]);
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
}
