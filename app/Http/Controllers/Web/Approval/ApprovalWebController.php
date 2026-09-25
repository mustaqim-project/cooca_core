<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Approval;

use App\Domain\Approval\ApprovalWorkflowService;
use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRule;
use App\Models\Business;
use App\Models\Role;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ApprovalWebController extends Controller
{
    public function __construct(
        private readonly ApprovalWorkflowService $workflowService
    ) {}

    /**
     * Display pending approvals inbox.
     */
    public function inbox(Request $request): View
    {
        $business = Context::requireBusiness();
        $user = Context::user();

        $filterType = $request->query('type');
        $pendingTickets = $this->workflowService->getPendingRequestsForUser($business, $user, $filterType);

        $counts = [
            'all' => $this->workflowService->getPendingRequestsForUser($business, $user)->count(),
            'purchase_order' => $this->workflowService->getPendingRequestsForUser($business, $user, ApprovalRule::DOC_PURCHASE_ORDER)->count(),
            'expense' => $this->workflowService->getPendingRequestsForUser($business, $user, ApprovalRule::DOC_EXPENSE)->count(),
            'supplier_invoice' => $this->workflowService->getPendingRequestsForUser($business, $user, ApprovalRule::DOC_SUPPLIER_INVOICE)->count(),
        ];

        return view('app.approvals.inbox', compact('business', 'pendingTickets', 'filterType', 'counts'));
    }

    /**
     * Display approval history.
     */
    public function history(Request $request): View
    {
        $business = Context::requireBusiness();
        $filterType = $request->query('type');
        $historyTickets = $this->workflowService->getHistoryRequests($business, $filterType);

        return view('app.approvals.history', compact('business', 'historyTickets', 'filterType'));
    }

    /**
     * Approve a pending ticket at current level.
     */
    public function approve(ApprovalRequest $approvalRequest, Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($approvalRequest->business_id === $business->id, 404);

        $user = Context::user();
        if (! $this->workflowService->canUserApprove($approvalRequest, $user)) {
            abort(403, 'Anda tidak memiliki hak otorisasi untuk menyetujui dokumen pada level ini.');
        }

        $notes = $request->input('notes');

        try {
            $updated = $this->workflowService->approve($approvalRequest, $user, $notes);

            $statusMsg = $updated->isApproved()
                ? 'Dokumen telah disetujui sepenuhnya pada seluruh tingkat otorisasi (Siap Dicairkan/Dieksekusi).'
                : "Persetujuan Level {$approvalRequest->current_level} berhasil dicatat. Dokumen berlanjut ke Level {$updated->current_level}.";

            return back()->with('success', $statusMsg);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menyetujui dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Reject a pending ticket with mandatory notes.
     */
    public function reject(ApprovalRequest $approvalRequest, Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($approvalRequest->business_id === $business->id, 404);

        $user = Context::user();
        if (! $this->workflowService->canUserApprove($approvalRequest, $user)) {
            abort(403, 'Anda tidak memiliki hak otorisasi untuk menolak dokumen pada level ini.');
        }

        $reason = $request->input('reason') ?? $request->input('notes');
        if (empty($reason) || strlen(trim((string) $reason)) < 3) {
            return back()->with('error', 'Catatan alasan penolakan wajib diisi untuk rekam jejak audit pimpinan (minimal 3 karakter).');
        }

        try {
            $this->workflowService->reject($approvalRequest, $user, (string) $reason);

            return back()->with('success', 'Dokumen berhasil ditolak. Pembuat draf (Maker) telah dinotifikasi dengan catatan penolakan.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menolak dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Approval rules management for Owner/Admin.
     */
    public function rulesIndex(): View
    {
        $business = Context::requireBusiness();
        $rules = ApprovalRule::where('business_id', $business->id)
            ->orderBy('document_type')
            ->orderBy('min_amount')
            ->get();

        $availableRoles = Role::where(function ($q) use ($business) {
            $q->where('business_id', $business->id)->orWhereNull('business_id');
        })->get();

        return view('app.approvals.rules', compact('business', 'rules', 'availableRoles'));
    }

    /**
     * Store new approval rule.
     */
    public function rulesStore(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'document_type' => ['required', 'string', 'in:purchase_order,expense,supplier_invoice'],
            'name' => ['nullable', 'string', 'max:100'],
            'min_amount' => ['required', 'numeric', 'gte:0'],
            'max_amount' => ['nullable', 'numeric', 'gte:min_amount'],
            'required_levels' => ['required', 'integer', 'between:1,3'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $levels = (int) $validated['required_levels'];
        $docType = (string) $validated['document_type'];

        $validated['name'] = $request->input('name') ?: ('Aturan Plafon ' . ucfirst(str_replace('_', ' ', $docType)));
        $validated['business_id'] = $business->id;
        $validated['approver_role_level_1'] = (string) ($request->input('approver_role_level_1') ?? $request->input('level_1_role') ?? 'supervisor');
        $validated['approver_role_level_2'] = $levels >= 2 ? (string) ($request->input('approver_role_level_2') ?? $request->input('level_2_role') ?? 'manager') : null;
        $validated['approver_role_level_3'] = $levels >= 3 ? (string) ($request->input('approver_role_level_3') ?? $request->input('level_3_role') ?? 'owner') : null;
        $validated['is_active'] = $request->boolean('is_active', true);

        ApprovalRule::create($validated);

        return back()->with('success', 'Aturan otorisasi dokumen berhasil ditambahkan.');
    }

    /**
     * Update existing approval rule.
     */
    public function rulesUpdate(ApprovalRule $approvalRule, Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($approvalRule->business_id === $business->id, 404);

        $validated = $request->validate([
            'document_type' => ['nullable', 'string', 'in:purchase_order,expense,supplier_invoice'],
            'name' => ['nullable', 'string', 'max:100'],
            'min_amount' => ['required', 'numeric', 'gte:0'],
            'max_amount' => ['nullable', 'numeric', 'gte:min_amount'],
            'required_levels' => ['required', 'integer', 'between:1,3'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $levels = (int) $validated['required_levels'];

        $validated['name'] = $request->input('name') ?: $approvalRule->name;
        if (! empty($validated['document_type'])) {
            $approvalRule->document_type = (string) $validated['document_type'];
        }
        $validated['approver_role_level_1'] = (string) ($request->input('approver_role_level_1') ?? $request->input('level_1_role') ?? $approvalRule->approver_role_level_1);
        $validated['approver_role_level_2'] = $levels >= 2 ? (string) ($request->input('approver_role_level_2') ?? $request->input('level_2_role') ?? 'manager') : null;
        $validated['approver_role_level_3'] = $levels >= 3 ? (string) ($request->input('approver_role_level_3') ?? $request->input('level_3_role') ?? 'owner') : null;
        $validated['is_active'] = $request->boolean('is_active', true);

        $approvalRule->update($validated);

        return back()->with('success', 'Aturan otorisasi dokumen berhasil diperbarui.');
    }

    /**
     * Delete approval rule.
     */
    public function rulesDestroy(ApprovalRule $approvalRule): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($approvalRule->business_id === $business->id, 404);

        $approvalRule->delete();

        return back()->with('success', 'Aturan otorisasi dokumen berhasil dihapus.');
    }
}
