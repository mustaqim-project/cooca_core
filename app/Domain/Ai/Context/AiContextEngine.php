<?php

declare(strict_types=1);

namespace App\Domain\Ai\Context;

use App\Domain\Ai\Context\Directives\AgentRoleDirectives;
use App\Domain\Ai\Organization\AgentRole;
use App\Domain\Ai\Rag\AiRagRetriever;
use App\Domain\Ai\Tools\AiToolRegistry;
use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\Log;

final class AiContextEngine
{
    private const DEFAULT_MAX_TOKENS = 6000;

    public function __construct(
        private readonly AiRagRetriever $ragRetriever = new AiRagRetriever(),
        private readonly AiToolRegistry $toolRegistry = new AiToolRegistry()
    ) {
    }

    /**
     * Assemble the complete 5-layer engineered context for prompt injection.
     *
     * @param array<int, AgentRole|string> $participatingAgents
     * @param array<string, mixed> $teamRouting
     * @return array{
     *     system_prompt: string,
     *     assembled_context: array<string, mixed>,
     *     rag_citations: array<int, string>,
     *     token_estimate: int
     * }
     */
    public function assembleEngineeredPrompt(
        Business $business,
        ?User $user,
        string $query,
        array $participatingAgents,
        array $teamRouting = [],
        int $maxTokenBudget = self::DEFAULT_MAX_TOKENS
    ): array {
        // --- Layer 1: Identity & Cognitive Role Directives ---
        $roleDirectivesText = AgentRoleDirectives::formatTeamDirectives($participatingAgents);

        // --- Layer 2: Live Operational Tool Grounding ---
        $toolResults = $this->gatherOperationalToolData($business, $user, $participatingAgents);

        // --- Layer 3: Factual RAG Document Chunks ---
        $ragChunks = $this->ragRetriever->retrieveRelevantKnowledge($business, $user, $query, 6, 0.68);
        $ragFormattedText = $this->ragRetriever->formatKnowledgeForPrompt($ragChunks);
        $citations = array_map(fn($c) => $c['citation'] ?? '[Dokumen Terverifikasi]', $ragChunks);

        // --- Layer 4: Memory & Task Context ---
        $assembledContext = [
            'business' => [
                'id' => $business->id,
                'name' => $business->name,
                'scale' => $business->business_scale ?? 'umkm',
                'currency' => $business->currency ?? 'IDR',
                'industry' => $business->industry_category ?? 'general',
            ],
            'timestamp' => now()->toIso8601String(),
            'query' => $query,
            'team' => $teamRouting['team_name'] ?? 'Tim Digital Terpadu',
            'room' => $teamRouting['room'] ?? 'Ruang Kerja Terpadu',
            'is_executive_task' => ! empty($teamRouting['is_executive']),
            'tools_data' => $toolResults,
            'rag_sources_count' => count($ragChunks),
        ];

        // --- Layer 5: Policy Guardrails & Strict Output Contract ---
        $systemPrompt = $this->composeFinalSystemPrompt(
            $business,
            $participatingAgents,
            $teamRouting,
            $roleDirectivesText,
            $ragFormattedText,
            $assembledContext
        );

        $tokenEstimate = (int) ceil(mb_strlen($systemPrompt) / 4);

        // If exceeding token budget, trim non-critical tool data safely
        if ($tokenEstimate > $maxTokenBudget) {
            Log::info("Context token estimate ({$tokenEstimate}) exceeds budget ({$maxTokenBudget}), pruning tool data.");
            $assembledContext['tools_data'] = array_slice($toolResults, 0, 2, true);
            $systemPrompt = $this->composeFinalSystemPrompt(
                $business,
                $participatingAgents,
                $teamRouting,
                $roleDirectivesText,
                $ragFormattedText,
                $assembledContext
            );
            $tokenEstimate = (int) ceil(mb_strlen($systemPrompt) / 4);
        }

        return [
            'system_prompt' => $systemPrompt,
            'assembled_context' => $assembledContext,
            'rag_citations' => $citations,
            'token_estimate' => $tokenEstimate,
        ];
    }

    /**
     * Gather read-only operational metrics from designated tools.
     *
     * @param array<int, AgentRole|string> $agents
     * @return array<string, mixed>
     */
    private function gatherOperationalToolData(Business $business, ?User $user, array $agents): array
    {
        $data = [];
        $executed = [];

        foreach ($agents as $agent) {
            $role = $agent instanceof AgentRole ? $agent : AgentRole::tryFrom((string) $agent);
            if (! $role) {
                continue;
            }

            $tools = $this->toolRegistry->getToolsForAgent($role);
            foreach ($tools as $name => $tool) {
                if (isset($executed[$name]) || $tool->getCategory() !== 'READ') {
                    continue;
                }

                try {
                    $data[$name] = $tool->execute($business, $user);
                    $executed[$name] = true;
                } catch (\Throwable $e) {
                    $data[$name] = ['error' => 'Gagal membaca data: ' . $e->getMessage()];
                }
            }
        }

        return $data;
    }

    /**
     * Build the definitive system instruction prompt.
     *
     * @param array<int, AgentRole|string> $agents
     * @param array<string, mixed> $teamRouting
     * @param array<string, mixed> $context
     */
    private function composeFinalSystemPrompt(
        Business $business,
        array $agents,
        array $teamRouting,
        string $roleDirectivesText,
        string $ragFormattedText,
        array $context
    ): string {
        $agentList = implode(', ', array_map(function ($a) {
            if ($a instanceof AgentRole) {
                return $a->label();
            }
            return AgentRole::tryFrom((string) $a)?->label() ?? (string) $a;
        }, $agents));

        $teamName = $teamRouting['team_name'] ?? 'Tim Digital Terpadu';
        $teamRoom = $teamRouting['room'] ?? 'Ruang Kerja';
        $isExecutive = ! empty($teamRouting['is_executive']);
        $contextJson = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $assignmentText = $isExecutive
            ? "TUGAS EKSEKUTIF DIREKSI: Ditangani langsung oleh jajaran C-Level di Executive Suite untuk arah strategis, evaluasi komprehensif, dan kesehatan bisnis menyeluruh."
            : "PENUGASAN TIM: Ditugaskan kepada {$teamName} di {$teamRoom}. Anggota pelaksana ({$agentList}) berkolaborasi erat secara presisi sesuai spesialisasi masing-masing.";

        return <<<PROMPT
Anda adalah AI Coordinator & Otak Utama untuk platform ERP COOCA (Autonomous Digital Company).
Tenant Aktif: "{$business->name}" (Skala: {$business->business_scale}, Mata Uang: {$business->currency})

=== 1. PENUGASAN & STRUKTUR TIM ===
{$assignmentText}

=== 2. JOB DESK, FORMULA & BATASAN KERJA AGEN PELAKSANA ===
{$roleDirectivesText}

=== 3. PRINSIP KERJA & ATURAN ANTI-HALUSINASI (SANGAT KETAT) ===
1. Manusia (Business Owner / Checker) adalah otoritas mutlak (Human-in-the-Loop). Setiap usulan mutasi (faktur, order pembelian, pengeluaran kas, kampanye promosi) berstatus DRAFT dan memerlukan persetujuan.
2. ZERO HALLUCINATION & FACTUAL CITATION:
   - Gunakan HANYA data aktual dari bagian "DATA OPERASIONAL NYATA" dan "FAKTA SISTEM (RAG GROUNDING)".
   - DILARANG mereka-reka nama produk, harga, saldo, nomor faktur, atau nama supplier yang tidak tertera di data.
   - JIKA INFORMASI TIDAK TERDAPAT PADA DATA DI BAWAH, WAJIB NYATAKAN: "Data tidak ditemukan dalam sistem." Dilarang mengarang asumsi seolah-olah data tersedia.
   - Sertakan sitasi dokumen (e.g., [Katalog Produk:...], [Transaksi POS:...], [Regulasi:...]) saat menyebut fakta spesifik.
3. Gaya Bahasa: Bahasa Indonesia baku, profesional, analitis, padat, to-the-point (Standar Apple HIG). ZERO EMOJI (dilarang menggunakan emoji apapun).

{$ragFormattedText}

=== 4. DATA OPERASIONAL NYATA TENANT ===
{$contextJson}

=== 5. FORMAT OUTPUT WAJIB (STRICT JSON) ===
Keluarkan respons HANYA dalam format JSON valid tanpa teks pengantar di luar blok JSON:
{
  "summary": "Ringkasan eksekutif 2-3 kalimat yang padat dan langsung ke inti permasalahan/kondisi bisnis",
  "findings": [
    "Temuan faktual 1 berdasarkan angka atau data terverifikasi (sertakan sitasi)",
    "Temuan faktual 2"
  ],
  "recommendations": [
    "Rekomendasi spesifik dan terukur 1 dengan formula yang tepat",
    "Rekomendasi terukur 2"
  ],
  "actions": [
    {
      "tool": "NamaTool (contoh: DraftInvoiceProposal, DraftPurchaseOrderProposal, DraftMarketingCampaignProposal, DraftSocialPostProposal)",
      "action_type": "tipe_aksi_unik",
      "risk_level": "LOW|MEDIUM|HIGH",
      "title": "Judul Usulan Aksi Konkret",
      "description": "Deskripsi rinci usulan aksi",
      "reason": "Dasar pertimbangan bisnis",
      "estimated_cost": 0,
      "payload": {}
    }
  ]
}
PROMPT;
    }
}
