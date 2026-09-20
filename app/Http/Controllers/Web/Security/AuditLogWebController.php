<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Security;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogWebController extends Controller
{
    /**
     * Resolve the active business or abort.
     */
    protected function resolveBusiness(Request $request): Business
    {
        $business = Context::hasBusiness() ? Context::business() : $request->user()?->activeBusiness;

        if (! $business && $request->user()) {
            $firstMembership = BusinessMembership::where('user_id', $request->user()->id)->first();
            if ($firstMembership) {
                $business = Business::find($firstMembership->business_id);
            }
        }

        if (! $business) {
            abort(403, 'Sesi bisnis tidak ditemukan.');
        }

        return $business;
    }

    /**
     * Authorize user access to Audit Logs.
     */
    protected function authorizeAccess(Request $request, Business $business): void
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        $membership = BusinessMembership::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->first();

        $role = $membership?->role ?? '';

        $isAuthorized = in_array($role, ['owner', 'manager', 'admin'], true)
            || ($user->role ?? null) === 'owner';

        if (! $isAuthorized) {
            abort(403, 'Anda tidak memiliki wewenang untuk mengakses Jejak Audit.');
        }
    }

    /**
     * Display the Audit Log Explorer index view.
     */
    public function index(Request $request): View
    {
        $business = $this->resolveBusiness($request);
        $this->authorizeAccess($request, $business);

        $filters = $request->only(['risk_level', 'module', 'user_id', 'start_date', 'end_date', 'q']);

        $thirtyDaysAgo = now()->subDays(30);

        $totalLogsCount = AuditLog::where('business_id', $business->id)
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();

        $highRiskCount = AuditLog::where('business_id', $business->id)
            ->where('risk_level', AuditLog::RISK_HIGH)
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();

        $alertSentCount = AuditLog::where('business_id', $business->id)
            ->whereNotNull('alert_sent_at')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();

        $logs = AuditLog::where('business_id', $business->id)
            ->with('user')
            ->filter($filters)
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        $users = $business->users()->select('users.id', 'users.name')->get();

        return view('app.security.audit-logs.index', [
            'business' => $business,
            'logs' => $logs,
            'users' => $users,
            'filters' => $filters,
            'totalLogsCount' => $totalLogsCount,
            'highRiskCount' => $highRiskCount,
            'alertSentCount' => $alertSentCount,
        ]);
    }

    /**
     * Retrieve audit log details with visual diff payload.
     */
    public function show(Request $request, AuditLog $auditLog): JsonResponse|View
    {
        $business = $this->resolveBusiness($request);
        $this->authorizeAccess($request, $business);

        // Multi-tenant isolation: silent not-found if cross-tenant
        if ($auditLog->business_id !== $business->id) {
            abort(404);
        }

        $auditLog->load('user');

        $oldValues = $auditLog->old_values ?? [];
        $newValues = $auditLog->new_values ?? [];
        $allKeys = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));

        $diffs = [];
        foreach ($allKeys as $key) {
            $old = $oldValues[$key] ?? null;
            $new = $newValues[$key] ?? null;

            $diffs[] = [
                'key' => $key,
                'label' => ucwords(str_replace('_', ' ', $key)),
                'old' => is_array($old) ? json_encode($old) : (string) ($old ?? '-'),
                'new' => is_array($new) ? json_encode($new) : (string) ($new ?? '-'),
                'is_different' => $old !== $new,
            ];
        }

        if ($request->expectsJson() || $request->ajax() || $request->has('json')) {
            return response()->json([
                'id' => $auditLog->id,
                'action' => $auditLog->action,
                'risk_level' => $auditLog->risk_level,
                'risk_reason' => $auditLog->risk_reason,
                'notes' => $auditLog->notes,
                'module_label' => $auditLog->getModuleLabel(),
                'user_name' => $auditLog->user?->name ?? 'Sistem / Otomatis',
                'user_email' => $auditLog->user?->email ?? '-',
                'ip_address' => $auditLog->ip_address ?? '127.0.0.1',
                'user_agent' => $auditLog->user_agent ?? 'Browser Web',
                'created_at_formatted' => $auditLog->created_at ? $auditLog->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i:s') . ' WIB' : '-',
                'alert_sent_at' => $auditLog->alert_sent_at ? $auditLog->alert_sent_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB' : null,
                'alert_recipient' => $auditLog->alert_recipient,
                'diffs' => $diffs,
            ]);
        }

        return view('app.security.audit-logs.show', [
            'business' => $business,
            'auditLog' => $auditLog,
            'diffs' => $diffs,
        ]);
    }

    /**
     * Export filtered business audit logs to CSV for owner auditing.
     */
    public function exportCsv(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $business = $this->resolveBusiness($request);
        $this->authorizeAccess($request, $business);

        $filters = $request->only(['risk_level', 'module', 'user_id', 'start_date', 'end_date', 'q']);

        $logs = AuditLog::where('business_id', $business->id)
            ->with('user')
            ->filter($filters)
            ->latest('created_at')
            ->get();

        $filename = 'audit-log-' . \Illuminate\Support\Str::slug($business->name) . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($logs): void {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Microsoft Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'Waktu (WIB)',
                'Pelaku (User)',
                'Email',
                'Modul',
                'Aksi',
                'Tingkat Risiko',
                'Alasan Risiko',
                'Catatan / Alasan Input',
                'Alamat IP',
                'Status Alert WA',
                'Penerima WA',
            ]);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->created_at ? $log->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') : '-',
                    $log->user?->name ?? 'Sistem / Otomatis',
                    $log->user?->email ?? '-',
                    $log->getModuleLabel(),
                    strtoupper($log->action),
                    strtoupper($log->risk_level),
                    $log->risk_reason ?: '-',
                    $log->notes ?: '-',
                    $log->ip_address ?: '127.0.0.1',
                    $log->alert_sent_at ? 'Terkirim (' . $log->alert_sent_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') . ')' : '-',
                    $log->alert_recipient ?: '-',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
