<?php

declare(strict_types=1);

namespace App\Domain\Ai\Rag\Sources;

use App\Domain\Ai\Rag\Contracts\KnowledgeSourceInterface;
use App\Domain\Ai\Tools\AiToolRegistry;
use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Str;

final class AiSystemCapabilitiesKnowledgeSource implements KnowledgeSourceInterface
{
    public function __construct(
        private readonly ?AiToolRegistry $toolRegistry = null
    ) {
    }

    public function getSourceId(): string
    {
        return 'ai_system_capabilities';
    }

    public function getLabel(): string
    {
        return 'Katalog Kapabilitas AI, Tool Registry & Prinsip Maker-Checker';
    }

    /**
     * @return array<int, array{content: string, citation: string, score: float, metadata: array<string, mixed>}>
     */
    public function retrieve(Business $business, ?User $user, string $query, array $options = []): array
    {
        $lowerQuery = mb_strtolower($query);
        $chunks = [];

        // 1. Maker-Checker Governance Principle
        $chunks[] = [
            'content' => "Prinsip Otonomi & Maker-Checker AI COOCA: AI Agent beroperasi sebagai Penasihat Cerdas & Perancang Draft (Maker). Seluruh aksi mutasi kritis (pembuatan faktur tagihan, penerbitan PO ke supplier, pengeluaran kas, pembuatan kampanye pemasaran) diproses sebagai 'Draft Proposal'. Perubahan status menjadi Final/Sah memerlukan konfirmasi atau persetujuan pengguna (Checker/Owner). Tindakan membaca analitik dan memberikan rekomendasi bersifat otonom tanpa mutasi database.",
            'citation' => "[Kebijakan Tata Kelola AI: Protokol Maker-Checker COOCA]",
            'score' => 0.90,
            'metadata' => ['policy' => 'maker_checker', 'level' => 'mandatory'],
        ];

        // 2. Tools Catalogue & Queries
        $registry = $this->toolRegistry ?? new AiToolRegistry();
        $tools = $registry->getAllTools();

        foreach ($tools as $name => $tool) {
            $desc = $tool->getDescription();
            $paramSchema = json_encode($tool->getInputSchema(), JSON_UNESCAPED_UNICODE);
            $perm = $tool->getRequiredPermission() ?? 'none (read-only)';

            $isMatch = Str::contains($lowerQuery, [mb_strtolower($name), 'tool', 'alat', 'fungsi', 'eksekusi']);
            $score = $isMatch ? 0.92 : 0.65;

            $chunks[] = [
                'content' => "Tool Sistem: {$name} | Deskripsi: {$desc} | Izin Diperlukan: {$perm} | Parameter: {$paramSchema}",
                'citation' => "[Katalog Tool: {$name}]",
                'score' => $score,
                'metadata' => [
                    'tool_name' => $name,
                    'permission' => $perm,
                ],
            ];
        }

        return $chunks;
    }
}
