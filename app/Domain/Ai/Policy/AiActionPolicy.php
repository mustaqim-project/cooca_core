<?php

declare(strict_types=1);

namespace App\Domain\Ai\Policy;

use App\Models\AiActionProposal;
use App\Models\Business;
use App\Models\User;
use App\Support\Context;

final class AiActionPolicy
{
    /**
     * Determine whether an action proposal requires human approval before execution.
     */
    public function requiresApproval(Business $business, string $actionType, string $riskLevel, float $estimatedCost = 0.0): bool
    {
        // 1. High and Critical risk ALWAYS require explicit human approval
        if ($riskLevel === AiActionProposal::RISK_CRITICAL || $riskLevel === AiActionProposal::RISK_HIGH) {
            return true;
        }

        // 2. Financial threshold checks
        if ($estimatedCost > 500000) { // e.g. Rp 500.000+ requires human review
            return true;
        }

        // 3. Medium risk with external publishing impacts
        if ($riskLevel === AiActionProposal::RISK_MEDIUM) {
            if (in_array($actionType, ['publish_social_post', 'broadcast_whatsapp', 'change_product_price'], true)) {
                return true;
            }
        }

        // 4. Low risk actions (generating reports, internal analysis) may auto-execute
        return false;
    }

    /**
     * Check if a user has authority to approve an action proposal.
     */
    public function canApprove(Business $business, ?User $user, AiActionProposal $proposal): bool
    {
        if (! $user) {
            return false;
        }

        // Direct check on user's membership role in this business
        $membership = \App\Models\BusinessMembership::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->first();

        if ($membership && in_array($membership->role, ['owner', 'admin'], true)) {
            return true;
        }

        // Owner and Admin can approve all proposals in their business via Context
        if (Context::isOwner() || Context::isAdminOrOwner()) {
            return true;
        }

        // Critical and High actions require Owner or Supervisor permission
        if ($proposal->isHighOrCritical()) {
            return Context::hasPermission('pos.supervisor_pin') || Context::hasPermission('approval.manage');
        }

        return Context::hasPermission('ai.approve');
    }
}
