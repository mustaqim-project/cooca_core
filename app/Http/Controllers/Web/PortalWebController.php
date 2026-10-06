<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\HRM\AttendanceService;
use App\Domain\HRM\Biometrics\FaceVerificationService;
use App\Domain\HRM\PayrollRunService;
use App\Domain\Template\ModuleRegistry;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceException;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\EmployeeCommission;
use App\Models\EmployeeLoan;
use App\Models\Location;
use App\Models\PayrollItem;
use App\Support\Context;
use App\Support\TimezoneHelper;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

final class PortalWebController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
        private readonly FaceVerificationService $faceVerificationService,
        private readonly PayrollRunService $payrollRunService
    ) {}

    /**
     * Display the employee personal portal, attendance center, profile, and payslips.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        // 1. Current user membership & role details (Strict Multi-Tenant Isolation)
        $membership = BusinessMembership::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->with(['roleModel', 'location'])
            ->first();

        // 2. Office location / branch for geofencing, timezone & shifts
        $location = $membership?->location
            ?? Location::where('business_id', $business->id)->where('is_primary', true)->first()
            ?? Location::where('business_id', $business->id)->first();

        $timezone = TimezoneHelper::resolve($business, $location);
        $tzAbbr = TimezoneHelper::abbreviation($timezone);
        $localNow = TimezoneHelper::now($business, $location);
        $today = $localNow->toDateString();
        $currentYear = $localNow->year;

        // Active shift resolution for today
        $workScheduleService = app(\App\Domain\HRM\WorkScheduleService::class);
        $activeShift = $workScheduleService->resolveActiveShift($business, $user, $location, $localNow);

        // 3. Calculate Tenure (Masa Kerja)
        $joinDate = $membership?->join_date ?? $membership?->created_at ?? $user->created_at;
        $tenureYears = (int) $joinDate->diffInYears(now());
        $tenureMonths = (int) ($joinDate->diffInMonths(now()) % 12);
        $tenureText = $tenureYears > 0
            ? "{$tenureYears} Tahun {$tenureMonths} Bulan"
            : "{$tenureMonths} Bulan";

        // 4. Leave Quota & Balance (Hak Cuti Tahunan)
        $leaveAllowance = 12; // 12 hari hak cuti tahunan
        $leaveUsed = Attendance::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->whereIn('status', [Attendance::STATUS_LEAVE])
            ->whereYear('date', $currentYear)
            ->count();
        $leaveRemaining = max(0, $leaveAllowance - $leaveUsed);

        // 5. Employee Loans & Kasbon Aktif
        $loans = EmployeeLoan::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();
        $activeLoan = $loans->firstWhere('status', EmployeeLoan::STATUS_ACTIVE);

        // 6. Employee Sales Commissions (Komisi Kinerja)
        $commissions = EmployeeCommission::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->take(15)
            ->get();
        $totalEarnedCommissions = (float) $commissions->where('status', EmployeeCommission::STATUS_APPROVED)->sum('amount');

        // 7. Today's attendance record (Outlet timezone context)
        $todayAttendance = Attendance::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        // 8. Active Exception Policy for Today (WFH, WFA, Dinas Luar)
        $activeException = AttendanceException::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->where('status', AttendanceException::STATUS_APPROVED)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->latest('created_at')
            ->first();

        // 9. Past 7 days attendance history
        $sevenDaysAgo = $localNow->copy()->subDays(6)->toDateString();
        $recentAttendances = Attendance::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->whereDate('date', '>=', $sevenDaysAgo)
            ->orderByDesc('date')
            ->get();

        // 9. Seven days summary metrics
        $presentDaysCount = $recentAttendances->whereNotNull('clock_in_at')->count();
        $onTimeDaysCount = $recentAttendances->where('clock_in_status', Attendance::CLOCK_IN_ON_TIME)->count();
        $lateDaysCount = $recentAttendances->filter(function (Attendance $att) {
            return $att->clock_in_status === Attendance::CLOCK_IN_LATE || $att->status === Attendance::STATUS_LATE;
        })->count();
        $totalWorkMinutes = (int) $recentAttendances->sum('work_duration_minutes');
        $totalWorkHours = round($totalWorkMinutes / 60, 1);

        // 10. Full Monthly Attendance History (Current Month)
        $monthlyAttendances = Attendance::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->whereMonth('date', Carbon::now('Asia/Jakarta')->month)
            ->whereYear('date', Carbon::now('Asia/Jakarta')->year)
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

        // 11. Attendance Correction Tickets
        $attendanceCorrections = AttendanceCorrection::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        // 12. Employee Personal Payslips (Slip Gaji Digital)
        $payslips = PayrollItem::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->with('payroll')
            ->orderByDesc('created_at')
            ->get();

        // 13. Office location coordinates & city for geofencing & weather
        $defaultLat = $location?->latitude ? (float) $location->latitude : -6.2088;
        $defaultLng = $location?->longitude ? (float) $location->longitude : 106.8456;
        $cityName = $location?->city ?? $business->city ?? 'Jakarta';

        // 14. Authorized quick-access modules for this user (Context-aware industry & RBAC filtering)
        $isOwner = Context::isOwner();
        $candidateModules = [
            [
                'name' => 'Mesin Kasir (POS)',
                'module_key' => ModuleRegistry::MODULE_POS_RETAIL,
                'permission' => 'pos.terminal',
                'route' => route('pos.terminal'),
                'icon' => 'calculator',
                'color' => '#007AFF',
                'desc' => 'Buka terminal transaksi kasir toko',
            ],
            [
                'name' => 'Pesanan Kasir',
                'module_key' => ModuleRegistry::MODULE_POS_RETAIL,
                'permission' => 'pos.orders',
                'route' => route('pos.orders.index'),
                'icon' => 'receipt',
                'color' => '#5856D6',
                'desc' => 'Daftar transaksi kasir & shift kas',
            ],
            [
                'name' => 'Dapur KDS',
                'module_key' => ModuleRegistry::MODULE_POS_DINEIN,
                'permission' => 'pos.kitchen',
                'route' => route('pos.kitchen.index'),
                'icon' => 'utensils',
                'color' => '#FF9500',
                'desc' => 'Layar pesanan dapur & barista',
            ],
            [
                'name' => 'Denah Meja',
                'module_key' => ModuleRegistry::MODULE_POS_DINEIN,
                'permission' => 'pos.tables',
                'route' => route('pos.tables.index'),
                'icon' => 'layout-grid',
                'color' => '#34C759',
                'desc' => 'Status meja & nomor reservasi',
            ],
            [
                'name' => 'Stok & Mutasi',
                'module_key' => ModuleRegistry::MODULE_INVENTORY_WAREHOUSE,
                'permission' => 'inventory.view',
                'route' => route('inventory.stocks'),
                'icon' => 'clipboard-list',
                'color' => '#5E5CE6',
                'desc' => 'Cek mutasi stok & kartu opname',
            ],
            [
                'name' => 'Gudang & Lokasi',
                'module_key' => ModuleRegistry::MODULE_INVENTORY_WAREHOUSE,
                'permission' => 'warehouse.view',
                'route' => route('warehouse.index'),
                'icon' => 'warehouse',
                'color' => '#AF52DE',
                'desc' => 'Manajemen multi-lokasi & rak gudang',
            ],
            [
                'name' => 'Katalog Produk',
                'module_key' => null, // Core module
                'permission' => 'products.view',
                'route' => route('products.index'),
                'icon' => 'package',
                'color' => '#0A84FF',
                'desc' => 'Daftar produk, menu & resep',
            ],
            [
                'name' => 'Bahan Baku',
                'module_key' => ModuleRegistry::MODULE_RECIPE_BOM,
                'permission' => 'materials.view',
                'route' => route('materials.index'),
                'icon' => 'boxes',
                'color' => '#30B0C7',
                'desc' => 'Daftar bahan baku & harga beli',
            ],
            [
                'name' => 'Data Pelanggan',
                'module_key' => ModuleRegistry::MODULE_CRM_LOYALTY,
                'permission' => 'customers.view',
                'route' => route('customers.index'),
                'icon' => 'users',
                'color' => '#32D74B',
                'desc' => 'Kontak pelanggan & keanggotaan',
            ],
            [
                'name' => 'Kas & Bank',
                'module_key' => ModuleRegistry::MODULE_ACCOUNTING_CORPORATE,
                'permission' => 'finance.cash_bank',
                'route' => route('finance.cash-bank.index'),
                'icon' => 'wallet',
                'color' => '#30D158',
                'desc' => 'Mutasi kas masuk dan kas keluar',
            ],
            [
                'name' => 'Asisten AI',
                'module_key' => null, // Global assistant
                'permission' => 'ai.access',
                'route' => route('pos.ai.index'),
                'icon' => 'sparkles',
                'color' => '#BF5AF2',
                'desc' => 'Konsultasi pintar & analisis',
            ],
        ];

        $quickModules = array_values(array_filter($candidateModules, function (array $mod) use ($isOwner, $business): bool {
            // 1. Industry-aware module enablement check
            if (! empty($mod['module_key']) && ! $business->isModuleEnabled($mod['module_key'])) {
                return false;
            }

            // 2. User authorization / RBAC check
            if ($isOwner) {
                return true;
            }

            return Context::hasPermission($mod['permission']);
        }));

        $activeTab = $request->query('tab', 'attendance');

        return view('app.portal.index', compact(
            'business',
            'user',
            'membership',
            'tenureText',
            'leaveAllowance',
            'leaveUsed',
            'leaveRemaining',
            'loans',
            'activeLoan',
            'commissions',
            'totalEarnedCommissions',
            'todayAttendance',
            'activeException',
            'recentAttendances',
            'presentDaysCount',
            'onTimeDaysCount',
            'lateDaysCount',
            'totalWorkMinutes',
            'totalWorkHours',
            'monthlyAttendances',
            'monthlyStats',
            'attendanceCorrections',
            'payslips',
            'location',
            'defaultLat',
            'defaultLng',
            'cityName',
            'quickModules',
            'activeTab',
            'timezone',
            'tzAbbr',
            'activeShift',
            'localNow'
        ));
    }

    /**
     * Show detailed individual payslip for this authenticated employee.
     */
    public function showPayslip(Request $request, PayrollItem $item): View
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        // Strict Tenant & User Ownership Guardrail
        if ($item->business_id !== $business->id) {
            abort(403, 'Slip gaji ini tidak berada di workspace bisnis Anda.');
        }

        if ($item->user_id !== $user->id && ! Context::hasPermission('users.view')) {
            abort(403, 'Akses ditolak: Anda hanya diperbolehkan melihat slip gaji milik Anda sendiri.');
        }

        $whatsappMessage = $this->payrollRunService->buildWhatsAppSlipMessage($item);

        return view('app.hrm.payroll.payslip', [
            'business' => $business,
            'item' => $item,
            'whatsappMessage' => $whatsappMessage,
            'isPublic' => false,
        ]);
    }

    /**
     * Handle biometric face enrollment from employee portal.
     */
    public function registerFace(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $request->validate([
            'photo' => ['required'],
        ]);

        try {
            $template = $this->faceVerificationService->registerFaceTemplate(
                $business,
                $user,
                $request->input('photo')
            );

            return response()->json([
                'success' => true,
                'message' => 'Template biometrik wajah berhasil didaftarkan dan dienkripsi (AES-256).',
                'registered_at' => now('Asia/Jakarta')->toIso8601String(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendaftarkan biometrik wajah: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify employee biometric face capture prior to recording attendance.
     */
    public function verifyFace(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $request->validate([
            'photo' => ['required'],
            'threshold' => ['nullable', 'numeric'],
        ]);

        try {
            $threshold = (float) ($request->input('threshold') ?? FaceVerificationService::DEFAULT_SIMILARITY_THRESHOLD);
            $result = $this->faceVerificationService->verifyFace(
                $business,
                $user,
                $request->input('photo'),
                $threshold
            );

            return response()->json([
                'success' => $result['verified'],
                'verified' => $result['verified'],
                'similarity' => $result['similarity'],
                'similarity_percent' => round($result['similarity'] * 100, 1),
                'threshold_percent' => round($threshold * 100),
                'error' => $result['error'] ?? null,
                'message' => $result['message'],
            ], $result['verified'] ? 200 : 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'verified' => false,
                'message' => 'Gagal memverifikasi biometrik wajah: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit an attendance correction ticket from employee portal.
     */
    public function storeCorrection(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $validated = $request->validate([
            'attendance_id' => ['nullable', 'uuid', 'exists:attendances,id'],
            'target_date' => ['required', 'date'],
            'correction_type' => ['required', 'string', 'in:clock_in,clock_out,both,status_only,permit,leave,sick'],
            'proposed_clock_in' => ['nullable', 'date_format:H:i'],
            'proposed_clock_out' => ['nullable', 'date_format:H:i'],
            'proposed_status' => ['nullable', 'string', 'in:present,late,leave,sick,permit,alpha'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        try {
            $ticket = $this->attendanceService->createCorrectionTicket($business, $user, $validated);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Tiket koreksi {$ticket->correction_number} berhasil diajukan dan menunggu persetujuan HR/Manager.",
                    'ticket' => $ticket,
                ]);
            }

            return redirect()->route('portal', ['tab' => 'history'])
                ->with('success', "Tiket koreksi {$ticket->correction_number} berhasil diajukan dan menunggu persetujuan HR/Manager.");
        } catch (ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => collect($e->errors())->flatten()->first(),
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        } catch (Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengajukan koreksi: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->route('portal', ['tab' => 'history'])
                ->with('error', 'Gagal mengajukan koreksi: ' . $e->getMessage());
        }
    }
}
