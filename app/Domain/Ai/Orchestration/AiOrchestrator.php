<?php

declare(strict_types=1);

namespace App\Domain\Ai\Orchestration;

use App\Domain\Ai\Context\AiContextBuilder;
use App\Domain\Ai\Context\AiContextEngine;
use App\Domain\Ai\Execution\AiActionExecutor;
use App\Domain\Ai\Organization\AgentRole;
use App\Domain\Ai\Organization\CompanyHierarchy;
use App\Domain\Ai\Organization\ExecutiveRole;
use App\Domain\Ai\Policy\AiActionPolicy;
use App\Domain\Ai\Providers\AiProviderManager;
use App\Domain\Ai\Routing\AiModelRouter;
use App\Domain\Ai\Support\AiStructuredOutputParser;
use App\Domain\Ai\Tools\AiToolRegistry;
use App\Domain\Billing\EntitlementService;
use App\Models\AiActionProposal;
use App\Models\AiTask;
use App\Models\AiWorkHistory;
use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Str;
use Throwable;

final class AiOrchestrator
{
    private const MAX_STEPS = 15;
    private const MAX_TOOL_CALLS = 25;

    public function __construct(
        private readonly AiProviderManager $providerManager = new AiProviderManager(),
        private readonly AiContextBuilder $contextBuilder = new AiContextBuilder(),
        private readonly AiModelRouter $modelRouter = new AiModelRouter(),
        private readonly AiToolRegistry $toolRegistry = new AiToolRegistry(),
        private readonly AiActionPolicy $actionPolicy = new AiActionPolicy(),
        private readonly AiActionExecutor $actionExecutor = new AiActionExecutor(),
        private readonly AiContextEngine $contextEngine = new AiContextEngine(),
    ) {}

    /**
     * Process an incoming user query or operational prompt through the Digital Company workflow.
     * Tasks are delegated to Teams, except executive tasks handled by CEO, COO, or CFO.
     *
     * @return array<string, mixed>
     */
    public function process(Business $business, ?User $user, string $query, string $taskType = 'chat', ?string $targetTeam = null, ?string $targetAgent = null): array
    {
        $startTime = microtime(true);

        // 1. Identify intent & route to designated Team or specific targeted Agent
        $teamRouting = CompanyHierarchy::routeTopicToTeam($query, $targetTeam, $targetAgent);
        $participatingAgents = $teamRouting['agents'];
        $agentSlugs = $teamRouting['agent_slugs'];
        $primaryAgent = $teamRouting['primary_agent'];
        $isExecutiveTask = $teamRouting['is_executive'];

        // 2. Create tracking task in DB assigned to Team
        $task = AiTask::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'executive_role' => $teamRouting['executive_lead'],
            'department' => $teamRouting['team'],
            'agent' => $primaryAgent->value,
            'type' => $taskType,
            'status' => AiTask::STATUS_RUNNING,
            'priority' => $isExecutiveTask ? AiTask::PRIORITY_HIGH : AiTask::PRIORITY_NORMAL,
            'input' => $query,
            'started_at' => now(),
        ]);

        try {
            // 3. Assemble tenant-scoped factual context & 5-layer engineered prompt with RAG grounding
            $engineered = $this->contextEngine->assembleEngineeredPrompt(
                $business,
                $user,
                $query,
                $participatingAgents,
                $teamRouting
            );

            $systemPrompt = $engineered['system_prompt'];
            $context = $engineered['assembled_context'];
            $citations = $engineered['rag_citations'];

            $task->update([
                'context' => $context,
                'tool_calls_count' => count($context['tools_data'] ?? []),
                'steps_count' => 1,
            ]);

            // 4. Resolve AI provider & model tier
            $resolved = $this->providerManager->resolveForBusiness($business);
            $provider = $resolved['provider'];
            $tier = $this->modelRouter->determineTier($query, $taskType);
            $model = $this->modelRouter->selectModel($provider->getProviderName(), $tier, $resolved['model']);

            // 5. Request reasoning & structured decision from provider
            $structuredOutput = $provider->structuredPrompt(
                $resolved['api_key'],
                $model,
                $systemPrompt,
                $query
            );

            // If structured output failed or returned raw, run parsing & repair
            if ((empty($structuredOutput['summary']) && empty($structuredOutput['findings'])) && ! empty($structuredOutput['raw'])) {
                $parsedFallback = AiStructuredOutputParser::parse((string) $structuredOutput['raw']);
                $structuredOutput = array_merge($structuredOutput, $parsedFallback);
            }

            $resolvedSummary = (string) (! empty($structuredOutput['summary']) 
                ? $structuredOutput['summary'] 
                : ($structuredOutput['raw'] ?? 'Analisis operasional diselesaikan.'));

            // 6. Check & generate Action Proposals
            $createdProposals = $this->processActionProposals($business, $user, $task, $structuredOutput, $primaryAgent);

            $hasPendingApprovals = false;
            foreach ($createdProposals as $prop) {
                if ($prop->isPending()) {
                    $hasPendingApprovals = true;
                    break;
                }
            }

            // 7. Log Work History
            $workHistory = AiWorkHistory::create([
                'business_id' => $business->id,
                'ai_task_id' => $task->id,
                'session_title' => Str::limit($query, 60),
                'executive_summary' => $resolvedSummary,
                'participating_agents' => $agentSlugs,
                'insights_count' => count($structuredOutput['findings'] ?? []),
                'actions_count' => count($createdProposals),
                'approved_count' => 0,
                'rejected_count' => 0,
                'metadata' => [
                    'model' => $model,
                    'provider' => $provider->getProviderName(),
                    'duration_ms' => round((microtime(true) - $startTime) * 1000),
                    'team' => $teamRouting['team'],
                    'team_name' => $teamRouting['team_name'],
                    'room' => $teamRouting['room'],
                    'rag_citations' => $citations,
                    'token_estimate' => $engineered['token_estimate'] ?? 0,
                ],
                'recorded_at' => now(),
            ]);

            // 8. Update task status
            $taskStatus = $hasPendingApprovals ? AiTask::STATUS_WAITING_APPROVAL : AiTask::STATUS_COMPLETED;
            $task->update([
                'status' => $taskStatus,
                'result' => $structuredOutput,
                'completed_at' => now(),
                'steps_count' => 2,
            ]);

            // Deduct SaaS token entitlement if service is available
            try {
                app(EntitlementService::class)->deductAiTokens($business, 1000, 'ai_task', $user);
            } catch (Throwable) {
                // Token deduction fallback
            }

            return [
                'success' => true,
                'task_id' => $task->id,
                'status' => $taskStatus,
                'team' => $teamRouting['team'],
                'team_name' => $teamRouting['team_name'],
                'is_executive_task' => $isExecutiveTask,
                'assigned_room' => $teamRouting['room'],
                'executive_role' => $task->executive_role,
                'department' => $task->department,
                'agent' => $task->agent,
                'participating_agents' => $agentSlugs,
                'summary' => $resolvedSummary,
                'findings' => $structuredOutput['findings'] ?? [],
                'recommendations' => $structuredOutput['recommendations'] ?? [],
                'rag_citations' => $citations,
                'token_estimate' => $engineered['token_estimate'] ?? 0,
                'proposals' => array_map(fn($p) => [
                    'id' => $p->id,
                    'title' => $p->title,
                    'description' => $p->description,
                    'risk_level' => $p->risk_level,
                    'status' => $p->status,
                    'estimated_cost' => (float) $p->estimated_cost,
                    'requires_approval' => $p->isPending(),
                    'tool' => $p->tool,
                    'payload' => $p->payload,
                ], $createdProposals),
                'requires_approval' => $hasPendingApprovals,
            ];
        } catch (Throwable $e) {
            $task->update([
                'status' => AiTask::STATUS_FAILED,
                'error' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            return [
                'success' => false,
                'task_id' => $task->id,
                'message' => 'Gagal memproses analisis AI: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Parse action proposals from LLM output, apply policy, and persist into database.
     *
     * @param array<string, mixed> $structuredOutput
     * @return array<int, AiActionProposal>
     */
    private function processActionProposals(Business $business, ?User $user, AiTask $task, array $structuredOutput, AgentRole $primaryAgent): array
    {
        $proposals = [];
        $rawActions = $structuredOutput['actions'] ?? [];

        foreach ($rawActions as $action) {
            $actionType = (string) ($action['action_type'] ?? 'general_action');
            $risk = strtoupper((string) ($action['risk_level'] ?? AiActionProposal::RISK_MEDIUM));
            $cost = (float) ($action['estimated_cost'] ?? 0.0);
            $tool = (string) ($action['tool'] ?? 'ManualProposal');

            $requiresApproval = $this->actionPolicy->requiresApproval($business, $actionType, $risk, $cost);
            $initialStatus = $requiresApproval ? AiActionProposal::STATUS_PENDING : AiActionProposal::STATUS_APPROVED;

            $idempotencyKey = 'ACT-' . substr(md5($business->id . '-' . $task->id . '-' . $actionType . '-' . microtime()), 0, 16);

            $proposal = AiActionProposal::create([
                'business_id' => $business->id,
                'ai_task_id' => $task->id,
                'executive_role' => $primaryAgent->executiveLead()->value,
                'department' => $primaryAgent->department()->value,
                'agent' => $primaryAgent->value,
                'tool' => $tool,
                'action_type' => $actionType,
                'risk_level' => $risk,
                'title' => (string) ($action['title'] ?? 'Usulan Aksi Bisnis'),
                'description' => (string) ($action['description'] ?? 'Aksi bisnis disiapkan oleh AI Agent.'),
                'reason' => (string) ($action['reason'] ?? 'Dihasilkan dari analisis kesehatan bisnis.'),
                'payload' => is_array($action['payload'] ?? null) ? $action['payload'] : [],
                'estimated_cost' => $cost,
                'status' => $initialStatus,
                'idempotency_key' => $idempotencyKey,
                'created_by' => $user?->id,
            ]);

            $proposals[] = $proposal;
        }

        return $proposals;
    }

    /**
     * @param array<int, AgentRole> $agents
     * @param array<string, mixed> $context
     * @param array<string, mixed> $teamRouting
     */
    private function buildSystemPrompt(array $agents, array $context, array $teamRouting = []): string
    {
        $agentList = implode(', ', array_map(fn($a) => $a->label(), $agents));
        $contextJson = json_encode($context, JSON_UNESCAPED_UNICODE);
        $teamName = $teamRouting['team_name'] ?? 'Tim Digital';
        $teamRoom = $teamRouting['room'] ?? 'Ruang Kerja Terpadu';
        $isExecutive = ! empty($teamRouting['is_executive']);

        $assignmentInstruction = $isExecutive
            ? "Tugas ini adalah mandat eksekutif yang ditangani langsung oleh jajaran Direksi (AI CEO, AI COO, AI CFO) di Executive Suite untuk arah strategis dan evaluasi kesehatan bisnis menyeluruh."
            : "Tugas ini ditugaskan kepada {$teamName} yang berbasis di ruang kerja {$teamRoom}. Seluruh anggota tim ({$agentList}) bekerja sama sebagai satu tim solid, bukan individu terpisah.";

        return <<<PROMPT
Anda adalah AI Coordinator untuk platform COOCA ID (Digital Company).
Hierarki Organisasi & Penugasan Tim:
{$assignmentInstruction}
- AI CEO: Ringkasan Strategis & Keputusan Tertinggi.
- AI COO: Orkestrasi Operasional & Koordinasi Antar Tim.
- Tim Pelaksana: {$agentList}.

PRINSIP WAJIB:
1. Manusia (Business Owner) adalah pemegang otoritas tertinggi (Human-in-the-Loop).
2. Data aktual berasal HANYA dari konteks bisnis yang diberikan di bawah ini. Dilarang mengarang angka, rumus, atau metrik (Zero Hyperbole & Zero Fictional Data).
3. Berikan jawaban dalam Bahasa Indonesia yang formal, ringkas, padat, dan profesional (Apple HIG standard). Hindari emoji sama sekali (Zero Emoji).
4. Jika diperlukan tindakan bisnis konkret (membuat faktur, restock barang, promosi diskon, follow up pelanggan), sertakan usulan aksi di array "actions".

KONTEKS BISNIS AKTUAL:
{$contextJson}

Format Output Wajib (Strict JSON):
{
  "summary": "Ringkasan eksekutif 2-3 kalimat",
  "findings": ["Temuan 1 berdasarkan data", "Temuan 2"],
  "recommendations": ["Rekomendasi 1 yang terukur", "Rekomendasi 2"],
  "actions": [
    {
      "tool": "DraftInvoiceProposal",
      "action_type": "create_invoice",
      "risk_level": "HIGH",
      "title": "Judul Usulan Aksi",
      "description": "Deskripsi singkat perubahan",
      "reason": "Alasan bisnis rekomendasi ini",
      "estimated_cost": 0,
      "payload": {}
    }
  ]
}
PROMPT;
    }


    /**
     * Trigger a comprehensive daily business health review across all departments.
     *
     * @return array<string, mixed>
     */
    public function runDailyBusinessCheck(Business $business, ?User $user = null): array
    {
        return $this->process(
            $business,
            $user,
            'Lakukan evaluasi kesehatan bisnis harian menyeluruh: periksa tren penjualan, ketersediaan stok kritis, pelanggan pasif, dan arus kas.',
            'diagnosis'
        );
    }
}
