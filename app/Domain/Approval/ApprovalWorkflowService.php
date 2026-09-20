<?php

declare(strict_types=1);

namespace App\Domain\Approval;

use App\Models\ApprovalLog;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRule;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\User;
use App\Support\Context;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

final class ApprovalWorkflowService
{
    /**
     * Evaluate whether a document requires an approval workflow and create the request if needed.
     * Supports either:
     * - evaluateAndCreateRequest(Model $document, ?float $amount = null, ?User $requester = null)
     * - evaluateAndCreateRequest(Business $business, string $documentType, string $documentId, float $amount, User $requester)
     */
    public function evaluateAndCreateRequest(
        Business|Model $businessOrDocument,
        string|float|null $documentTypeOrAmount = null,
        string|User|null $documentIdOrRequester = null,
        ?float $amount = null,
        ?User $requester = null
    ): ?ApprovalRequest {
        if ($businessOrDocument instanceof Model && ! ($businessOrDocument instanceof Business)) {
            $document = $businessOrDocument;
            /** @var Business|null $business */
            $business = $document->business ?? Business::find($document->business_id) ?? Context::business();
            $documentType = match (get_class($document)) {
                \App\Models\PurchaseOrder::class => ApprovalRule::DOC_PURCHASE_ORDER,
                \App\Models\Expense::class => ApprovalRule::DOC_EXPENSE,
                \App\Models\SupplierInvoice::class => ApprovalRule::DOC_SUPPLIER_INVOICE,
                default => 'purchase_order',
            };
            $documentId = (string) $document->id;
            $amount = is_numeric($documentTypeOrAmount) ? (float) $documentTypeOrAmount : (float) ($document->total_amount ?? $document->amount ?? 0);
            $requester = $documentIdOrRequester instanceof User ? $documentIdOrRequester : Context::user();
        } else {
            /** @var Business $business */
            $business = $businessOrDocument;
            $documentType = (string) $documentTypeOrAmount;
            $documentId = (string) $documentIdOrRequester;
            $amount = (float) $amount;
            $requester = $requester;
        }

        if (! $business || ! $requester) {
            return null;
        }

        // If business is UMKM and has no active rules, skip approval workflow
        $query = ApprovalRule::where('business_id', $business->id)
            ->forDocument($documentType, $amount);

        if ($business->isUmkm() && ! $query->exists()) {
            return null;
        }

        /** @var ApprovalRule|null $rule */
        $rule = $query->orderBy('min_amount', 'desc')->first();

        // If no rule matches, document passes without approval
        if (! $rule) {
            return null;
        }

        // Check if an existing request already exists for this document
        $existing = ApprovalRequest::where('business_id', $business->id)
            ->where('document_type', $documentType)
            ->where('document_id', $documentId)
            ->first();

        if ($existing) {
            if ($existing->isPending()) {
                return $existing;
            }

            // If previously rejected and resubmitted, reset request to level 1 pending
            $existing->update([
                'rule_id' => $rule->id,
                'amount' => $amount,
                'current_level' => 1,
                'total_levels' => $rule->required_levels,
                'status' => ApprovalRequest::STATUS_PENDING,
                'rejection_reason' => null,
                'approved_at' => null,
                'rejected_at' => null,
            ]);

            return $existing;
        }

        /** @var ApprovalRequest $request */
        $request = ApprovalRequest::create([
            'business_id' => $business->id,
            'document_type' => $documentType,
            'document_id' => $documentId,
            'requester_id' => $requester->id,
            'rule_id' => $rule->id,
            'amount' => $amount,
            'current_level' => 1,
            'total_levels' => $rule->required_levels,
            'status' => ApprovalRequest::STATUS_PENDING,
        ]);

        return $request;
    }

    /**
     * Determine if a user has authority to approve at the request's current level.
     */
    public function canUserApprove(ApprovalRequest $request, User $user): bool
    {
        // Must belong to the same business
        $membership = BusinessMembership::where('business_id', $request->business_id)
            ->where('user_id', $user->id)
            ->first();

        if (! $membership) {
            return false;
        }

        // Owner always has omnipotent approval authority
        if ($membership->role === 'owner') {
            return true;
        }

        // Maker cannot approve their own document unless they are the owner
        if ($request->requester_id === $user->id) {
            return false;
        }

        $userRoleSlug = $membership->customRole?->slug ?? $membership->role;

        // Admin has high level approval authority
        if ($userRoleSlug === 'admin') {
            return true;
        }

        $requiredRoleSlug = $request->rule?->getRoleForLevel($request->current_level) ?? 'supervisor';

        return strtolower((string) $userRoleSlug) === strtolower((string) $requiredRoleSlug);
    }

    /**
     * Approve the request at the current level and transition forward.
     */
    public function approve(ApprovalRequest $request, User $approver, ?string $notes = null): ApprovalRequest
    {
        if (! $request->isPending()) {
            throw new RuntimeException('Tiket persetujuan ini sudah selesai diproses dan tidak dapat disetujui kembali.');
        }

        if (! $this->canUserApprove($request, $approver)) {
            throw new RuntimeException('Anda tidak memiliki wewenang atau peran yang sesuai untuk menyetujui dokumen pada level ini.');
        }

        return DB::transaction(function () use ($request, $approver, $notes): ApprovalRequest {
            ApprovalLog::create([
                'approval_request_id' => $request->id,
                'level' => $request->current_level,
                'approver_id' => $approver->id,
                'action' => ApprovalLog::ACTION_APPROVED,
                'notes' => $notes,
                'created_at' => now(),
            ]);

            if ($request->current_level < $request->total_levels) {
                $request->increment('current_level');
            } else {
                $request->update([
                    'status' => ApprovalRequest::STATUS_APPROVED,
                    'approved_at' => now(),
                ]);
            }

            return $request->fresh(['logs.approver', 'requester', 'rule']);
        });
    }

    /**
     * Reject the request with mandatory justification.
     */
    public function reject(ApprovalRequest $request, User $approver, string $reason): ApprovalRequest
    {
        if (! $request->isPending()) {
            throw new RuntimeException('Tiket persetujuan ini sudah selesai diproses dan tidak dapat ditolak.');
        }

        $trimmedReason = trim($reason);
        if (empty($trimmedReason)) {
            throw new InvalidArgumentException('Alasan penolakan dokumen wajib diisi untuk rekam jejak audit.');
        }

        if (! $this->canUserApprove($request, $approver)) {
            throw new RuntimeException('Anda tidak memiliki wewenang atau peran yang sesuai untuk menolak dokumen pada level ini.');
        }

        return DB::transaction(function () use ($request, $approver, $trimmedReason): ApprovalRequest {
            ApprovalLog::create([
                'approval_request_id' => $request->id,
                'level' => $request->current_level,
                'approver_id' => $approver->id,
                'action' => ApprovalLog::ACTION_REJECTED,
                'notes' => $trimmedReason,
                'created_at' => now(),
            ]);

            $request->update([
                'status' => ApprovalRequest::STATUS_REJECTED,
                'rejection_reason' => $trimmedReason,
                'rejected_at' => now(),
            ]);

            return $request->fresh(['logs.approver', 'requester', 'rule']);
        });
    }

    /**
     * Get pending approval requests actionable by the user.
     *
     * @return Collection<int, ApprovalRequest>
     */
    public function getPendingRequestsForUser(Business $business, User $user, ?string $documentType = null): Collection
    {
        $query = ApprovalRequest::where('business_id', $business->id)
            ->pending()
            ->with(['requester', 'rule', 'logs.approver'])
            ->orderBy('created_at', 'desc');

        if (! empty($documentType)) {
            $query->where('document_type', $documentType);
        }

        /** @var Collection<int, ApprovalRequest> $requests */
        $requests = $query->get();

        return $requests->filter(fn (ApprovalRequest $req): bool => $this->canUserApprove($req, $user))->values();
    }

    /**
     * Get historical approval requests (Approved / Rejected) for the business.
     *
     * @return Collection<int, ApprovalRequest>
     */
    public function getHistoryRequests(Business $business, ?string $documentType = null, int $limit = 50): Collection
    {
        $query = ApprovalRequest::where('business_id', $business->id)
            ->whereIn('status', [ApprovalRequest::STATUS_APPROVED, ApprovalRequest::STATUS_REJECTED])
            ->with(['requester', 'rule', 'logs.approver'])
            ->orderBy('updated_at', 'desc')
            ->limit($limit);

        if (! empty($documentType)) {
            $query->where('document_type', $documentType);
        }

        return $query->get();
    }

    /**
     * Generate structured status summary for horizontal document stepper.
     *
     * @return array<string, mixed>
     */
    public function getDocumentStepperData(string $documentType, string $documentId, ?User $viewer = null): array
    {
        $request = ApprovalRequest::where('document_type', $documentType)
            ->where('document_id', $documentId)
            ->with(['requester', 'rule', 'logs.approver'])
            ->first();

        if (! $request) {
            return [
                'has_approval' => false,
                'status' => 'direct',
                'current_level' => 1,
                'total_levels' => 1,
                'can_approve' => false,
                'request' => null,
            ];
        }

        return [
            'has_approval' => true,
            'status' => $request->status,
            'current_level' => $request->current_level,
            'total_levels' => $request->total_levels,
            'can_approve' => $viewer ? $this->canUserApprove($request, $viewer) : false,
            'request' => $request,
        ];
    }
}
