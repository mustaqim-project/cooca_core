<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Hrm;

use App\Domain\HRM\AttendanceExceptionService;
use App\Domain\HRM\AttendanceService;
use App\Domain\HRM\Biometrics\FaceVerificationService;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceException;
use App\Models\BusinessMembership;
use App\Models\User;
use App\Support\Context;
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
     * Get current user's attendance status today.
     * GET /api/v1/attendance/today
     */
    public function today(): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();
        $today = now()->toDateString();

        $attendance = Attendance::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->with(['location', 'exceptionPolicy', 'correction'])
            ->first();

        $membership = BusinessMembership::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->first();

        $activeException = $this->exceptionService->getActiveException($business, $user, now());

        return response()->json([
            'success' => true,
            'data' => [
                'server_time' => now()->toIso8601String(),
                'date' => $today,
                'employee' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'job_title' => $membership?->job_title,
                    'attendance_mode' => $membership?->attendance_mode ?? 'geofenced',
                    'face_registered' => ! empty($membership?->face_biometric_template),
                    'face_registered_at' => $membership?->face_registered_at?->toIso8601String(),
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
