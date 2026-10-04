<?php

declare(strict_types=1);

namespace App\Domain\Ai\Context;

use App\Domain\Ai\Organization\AgentRole;
use App\Domain\Ai\Tools\AiToolRegistry;
use App\Models\Business;
use App\Models\User;

final class AiContextBuilder
{
    public function __construct(
        private readonly AiToolRegistry $toolRegistry = new AiToolRegistry()
    ) {}

    /**
     * Build tenant-scoped, token-efficient factual context for an agent query or task.
     *
     * @param array<int, AgentRole> $participatingAgents
     * @return array<string, mixed>
     */
    public function buildContext(Business $business, ?User $user, array $participatingAgents, string $query): array
    {
        $context = [
            'business' => [
                'name' => $business->name,
                'scale' => $business->business_scale ?? 'umkm',
                'currency' => $business->currency ?? 'IDR',
                'industry' => $business->industry_category ?? 'general',
            ],
            'timestamp' => now()->toIso8601String(),
            'query' => $query,
            'data' => [],
        ];

        // Gather relevant data strictly from designated tools
        $executedTools = [];
        foreach ($participatingAgents as $agent) {
            $agentTools = $this->toolRegistry->getToolsForAgent($agent);

            foreach ($agentTools as $toolName => $tool) {
                // Avoid re-running the same tool multiple times in one context build
                if (isset($executedTools[$toolName])) {
                    continue;
                }

                // Only run read-only tools during context assembly
                if ($tool->getCategory() !== 'READ') {
                    continue;
                }

                try {
                    $context['data'][$toolName] = $tool->execute($business, $user);
                    $executedTools[$toolName] = true;
                } catch (\Throwable $e) {
                    $context['data'][$toolName] = ['error' => 'Gagal mengambil data: ' . $e->getMessage()];
                }
            }
        }

        return $context;
    }
}
