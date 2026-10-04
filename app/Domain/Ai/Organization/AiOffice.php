<?php

declare(strict_types=1);

namespace App\Domain\Ai\Organization;

use App\Models\AiActionProposal;
use App\Models\AiTask;
use App\Models\AiWorkHistory;
use App\Models\Business;
use Illuminate\Support\Collection;

enum AiOffice: string
{
    case EXECUTIVE = 'executive';
    case OPERATIONS = 'operations';
    case GROWTH = 'growth';

    public function label(): string
    {
        return match ($this) {
            self::EXECUTIVE => 'Executive Office',
            self::OPERATIONS => 'Operations Office',
            self::GROWTH => 'Growth Office',
        };
    }

    public function tagline(): string
    {
        return match ($this) {
            self::EXECUTIVE => 'Business strategy, finance & decisions',
            self::OPERATIONS => 'Inventory, purchasing & marketplace',
            self::GROWTH => 'Marketing, sales, content & customers',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::EXECUTIVE => 'Pusat komando eksekutif untuk arah strategis, pengawasan kesehatan bisnis, analitik keuangan, mitigasi risiko, dan eskalasi keputusan bisnis.',
            self::OPERATIONS => 'Pusat kendali operasional harian untuk menjamin ketersediaan stok, pengadaan bahan baku, manajemen pemasok, dan integrasi omnichannel marketplace.',
            self::GROWTH => 'Pusat pertumbuhan komersial dan kreatif untuk strategi pemasaran, kampanye promosi, produksi naskah konten, interaksi media sosial, dan retensi pelanggan.',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::EXECUTIVE => 'crown',
            self::OPERATIONS => 'cpu',
            self::GROWTH => 'sparkles',
        };
    }

    public function routeName(): string
    {
        return match ($this) {
            self::EXECUTIVE => 'cooca-ai.office.executive',
            self::OPERATIONS => 'cooca-ai.office.operations',
            self::GROWTH => 'cooca-ai.office.growth',
        };
    }

    public function themeColor(): string
    {
        return match ($this) {
            self::EXECUTIVE => 'amber',
            self::OPERATIONS => 'blue',
            self::GROWTH => 'purple',
        };
    }

    /**
     * Get the executive roles overseeing this office.
     *
     * @return array<int, ExecutiveRole>
     */
    public function executiveLeads(): array
    {
        return match ($this) {
            self::EXECUTIVE => [ExecutiveRole::CEO, ExecutiveRole::CFO],
            self::OPERATIONS => [ExecutiveRole::COO],
            self::GROWTH => [ExecutiveRole::CMO, ExecutiveRole::SALES_DIRECTOR],
        };
    }

    /**
     * Get all specialized agent roles belonging to this office.
     *
     * @return array<int, AgentRole>
     */
    public function agentRoles(): array
    {
        return match ($this) {
            self::EXECUTIVE => [
                AgentRole::BUSINESS,
                AgentRole::FINANCE,
                AgentRole::REPORTING,
            ],
            self::OPERATIONS => [
                AgentRole::INVENTORY,
                AgentRole::PURCHASING,
                AgentRole::MARKETPLACE,
            ],
            self::GROWTH => [
                AgentRole::MARKETING,
                AgentRole::CONTENT,
                AgentRole::SOCIAL_MEDIA,
                AgentRole::SALES,
                AgentRole::CUSTOMER,
            ],
        };
    }

    /**
     * Resolve which office an agent belongs to.
     */
    public static function fromAgent(AgentRole $agent): self
    {
        return match ($agent) {
            AgentRole::BUSINESS, AgentRole::FINANCE, AgentRole::REPORTING => self::EXECUTIVE,
            AgentRole::INVENTORY, AgentRole::PURCHASING, AgentRole::MARKETPLACE => self::OPERATIONS,
            AgentRole::MARKETING, AgentRole::CONTENT, AgentRole::SOCIAL_MEDIA, AgentRole::SALES, AgentRole::CUSTOMER => self::GROWTH,
            AgentRole::HR => self::OPERATIONS, // HR operations auxiliary
        };
    }

    /**
     * Calculate live backend statistics for this office scoped to tenant business.
     *
     * @return array{
     *     code: string,
     *     name: string,
     *     tagline: string,
     *     description: string,
     *     icon: string,
     *     route: string,
     *     theme: string,
     *     total_agents: int,
     *     active_agents_count: int,
     *     active_tasks_count: int,
     *     pending_approvals_count: int,
     *     health_status: string,
     *     health_label: string,
     *     agent_statuses: array<string, array<string, mixed>>,
     *     recent_tasks: Collection,
     *     pending_proposals: Collection
     * }
     */
    public function getOfficeStats(Business $business): array
    {
        $agentRoles = $this->agentRoles();
        $agentRoleStrings = array_map(fn(AgentRole $r) => $r->value, $agentRoles);

        // 1. Fetch pending proposals for this office
        $pendingProposals = AiActionProposal::where('business_id', $business->id)
            ->whereIn('agent', $agentRoleStrings)
            ->where('status', AiActionProposal::STATUS_PENDING)
            ->latest()
            ->get();

        $pendingApprovalsCount = $pendingProposals->count();

        // 2. Fetch active tasks for this office (running, waiting_approval, pending)
        $activeTasks = AiTask::where('business_id', $business->id)
            ->whereIn('agent', $agentRoleStrings)
            ->whereIn('status', [AiTask::STATUS_PENDING, AiTask::STATUS_RUNNING, AiTask::STATUS_WAITING_APPROVAL])
            ->get();

        $activeTasksCount = $activeTasks->count();

        // 3. Resolve status for each agent in this office with customizable avatars
        $activeAgentsCount = 0;
        $agentStatuses = [];
        $avatars = \App\Models\AiAgentAvatar::getAvatarsForBusiness($business->id);

        foreach ($agentRoles as $agent) {
            $latestTask = AiTask::where('business_id', $business->id)
                ->where('agent', $agent->value)
                ->latest()
                ->first();

            $pendingProp = $pendingProposals->firstWhere('agent', $agent->value);

            $status = 'IDLE';
            $statusLabel = 'Tersedia';
            $currentWork = 'Tidak ada tugas aktif';

            if ($latestTask && $latestTask->isRunning()) {
                $status = 'WORKING';
                $statusLabel = 'Sedang Bekerja';
                $currentWork = $latestTask->input;
                $activeAgentsCount++;
            } elseif ($pendingProp || ($latestTask && $latestTask->isWaitingApproval())) {
                $status = 'WAITING_APPROVAL';
                $statusLabel = 'Menunggu Persetujuan';
                $currentWork = $pendingProp?->title ?? $latestTask?->input ?? 'Menunggu tinjauan pemilik';
                $activeAgentsCount++;
            } elseif ($latestTask && $latestTask->isFailed()) {
                $status = 'FAILED';
                $statusLabel = 'Perlu Perhatian';
                $currentWork = $latestTask->error ?? 'Gagal memproses tugas';
            } elseif ($latestTask && $latestTask->isCompleted() && $latestTask->completed_at && $latestTask->completed_at->gt(now()->subHours(6))) {
                $status = 'COMPLETED';
                $statusLabel = 'Selesai';
                $currentWork = 'Tugas terakhir diselesaikan';
            }

            $avatar = $avatars[$agent->value] ?? null;

            $agentStatuses[$agent->value] = [
                'role' => $agent->value,
                'name' => $avatar['name'] ?? $agent->label(),
                'department' => $agent->department()->value,
                'lead' => $agent->executiveLead()->label(),
                'description' => $agent->description(),
                'icon' => $agent->icon(),
                'status' => $status,
                'status_label' => $statusLabel,
                'current_work' => $currentWork,
                'last_active' => $latestTask?->created_at?->diffForHumans() ?? 'Belum pernah bertugas',
                'is_executive_lead' => false,
                'avatar' => $avatar,
            ];
        }

        // Also resolve live status for executive leads overseeing this office
        foreach ($this->executiveLeads() as $execLead) {
            $latestExecTask = AiTask::where('business_id', $business->id)
                ->where(function ($q) use ($execLead) {
                    $q->where('executive_role', $execLead->value)
                      ->orWhere('agent', $execLead->value);
                })
                ->latest()
                ->first();

            $pendingProp = $pendingProposals->first(function ($p) use ($execLead) {
                return $p->executive_role === $execLead->value || $p->agent === $execLead->value;
            });

            $status = 'IDLE';
            $statusLabel = 'Tersedia';
            $currentWork = 'Memantau strategi & koordinasi divisi';

            if ($latestExecTask && $latestExecTask->isRunning()) {
                $status = 'WORKING';
                $statusLabel = 'Sedang Bekerja';
                $currentWork = $latestExecTask->input;
                $activeAgentsCount++;
            } elseif ($pendingProp || ($latestExecTask && $latestExecTask->isWaitingApproval())) {
                $status = 'WAITING_APPROVAL';
                $statusLabel = 'Menunggu Persetujuan';
                $currentWork = $pendingProp?->title ?? $latestExecTask?->input ?? 'Menunggu tinjauan pemilik';
                $activeAgentsCount++;
            } elseif ($latestExecTask && $latestExecTask->isFailed()) {
                $status = 'FAILED';
                $statusLabel = 'Perlu Perhatian';
                $currentWork = $latestExecTask->error ?? 'Gagal memproses tugas';
            } elseif ($latestExecTask && $latestExecTask->isCompleted() && $latestExecTask->completed_at && $latestExecTask->completed_at->gt(now()->subHours(6))) {
                $status = 'COMPLETED';
                $statusLabel = 'Selesai';
                $currentWork = 'Sesi evaluasi selesai';
            }

            $avatar = $avatars[$execLead->value] ?? null;

            $agentStatuses[$execLead->value] = [
                'role' => $execLead->value,
                'name' => $avatar['name'] ?? $execLead->label(),
                'department' => 'executive',
                'lead' => $execLead->label(),
                'description' => $execLead->title(),
                'icon' => $execLead->icon(),
                'status' => $status,
                'status_label' => $statusLabel,
                'current_work' => $currentWork,
                'last_active' => $latestExecTask?->created_at?->diffForHumans() ?? 'Siaga',
                'is_executive_lead' => true,
                'avatar' => $avatar,
            ];
        }

        // 4. Recent tasks for this office
        $recentTasks = AiTask::where('business_id', $business->id)
            ->whereIn('agent', $agentRoleStrings)
            ->latest()
            ->take(5)
            ->get();

        // 5. Office Health calculation
        $healthStatus = 'optimal';
        $healthLabel = 'Optimal & Siaga';

        if ($pendingApprovalsCount > 0) {
            $healthStatus = 'attention_required';
            $healthLabel = "{$pendingApprovalsCount} Usulan Menunggu";
        } elseif ($activeTasksCount > 0) {
            $healthStatus = 'active';
            $healthLabel = "{$activeTasksCount} Tugas Berjalan";
        }

        return [
            'code' => $this->value,
            'name' => $this->label(),
            'tagline' => $this->tagline(),
            'description' => $this->description(),
            'icon' => $this->icon(),
            'route' => $this->routeName(),
            'theme' => $this->themeColor(),
            'total_agents' => count($agentRoles),
            'active_agents_count' => $activeAgentsCount,
            'active_tasks_count' => $activeTasksCount,
            'pending_approvals_count' => $pendingApprovalsCount,
            'health_status' => $healthStatus,
            'health_label' => $healthLabel,
            'agent_statuses' => $agentStatuses,
            'recent_tasks' => $recentTasks,
            'pending_proposals' => $pendingProposals,
        ];
    }
}
