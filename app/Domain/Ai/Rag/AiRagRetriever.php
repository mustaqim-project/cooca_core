<?php

declare(strict_types=1);

namespace App\Domain\Ai\Rag;

use App\Domain\Ai\Rag\Contracts\KnowledgeSourceInterface;
use App\Domain\Ai\Rag\Sources\AiSystemCapabilitiesKnowledgeSource;
use App\Domain\Ai\Rag\Sources\TenantMasterDataKnowledgeSource;
use App\Domain\Ai\Rag\Sources\TenantOperationalHistoryKnowledgeSource;
use App\Domain\Ai\Rag\Sources\UmkmRegulationsKnowledgeSource;
use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\Log;

final class AiRagRetriever
{
    /**
     * @var array<int, KnowledgeSourceInterface>
     */
    private array $sources;

    /**
     * @param array<int, KnowledgeSourceInterface>|null $sources
     */
    public function __construct(?array $sources = null)
    {
        $this->sources = $sources ?? [
            new TenantMasterDataKnowledgeSource(),
            new TenantOperationalHistoryKnowledgeSource(),
            new UmkmRegulationsKnowledgeSource(),
            new AiSystemCapabilitiesKnowledgeSource(),
        ];
    }

    /**
     * Register a new knowledge source.
     */
    public function addSource(KnowledgeSourceInterface $source): void
    {
        $this->sources[] = $source;
    }

    /**
     * Retrieve knowledge chunks relevant to the user's query across all registered sources.
     *
     * @param array<string, mixed> $options
     * @return array<int, array{content: string, citation: string, score: float, metadata: array<string, mixed>}>
     */
    public function retrieveRelevantKnowledge(
        Business $business,
        ?User $user,
        string $query,
        int $maxChunks = 6,
        float $minScore = 0.68,
        array $options = []
    ): array {
        $allChunks = [];

        foreach ($this->sources as $source) {
            try {
                $sourceChunks = $source->retrieve($business, $user, $query, $options);
                foreach ($sourceChunks as $chunk) {
                    if (($chunk['score'] ?? 0.0) >= $minScore) {
                        $allChunks[] = $chunk;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("Gagal mengambil data dari RAG source {$source->getSourceId()}: " . $e->getMessage(), [
                    'business_id' => $business->id,
                    'query' => $query,
                ]);
            }
        }

        // Sort by relevance score descending
        usort($allChunks, static fn($a, $b) => ($b['score'] <=> $a['score']));

        return array_slice($allChunks, 0, $maxChunks);
    }

    /**
     * Format retrieved chunks into a standardized grounding block for the LLM prompt.
     *
     * @param array<int, array{content: string, citation: string, score: float, metadata: array<string, mixed>}> $chunks
     */
    public function formatKnowledgeForPrompt(array $chunks): string
    {
        if (empty($chunks)) {
            return '';
        }

        $lines = [];
        $lines[] = "=== FAKTA & DOKUMEN SISTEM TERVERIFIKASI (RAG GROUNDING) ===";
        $lines[] = "Gunakan data nyata di bawah sebagai satu-satunya sumber kebenaran faktual. DILARANG berhalusinasi atau mereka-reka angka/nama produk/status. Jika informasi tidak ada di bawah, nyatakan dengan jujur 'Data tidak ditemukan dalam sistem'. Wajib sebutkan sitasi rujukan (e.g. [Sumber:...]) saat mengutip fakta.";
        $lines[] = "";

        foreach ($chunks as $index => $chunk) {
            $num = $index + 1;
            $citation = $chunk['citation'] ?? '[Dokumen Sistem]';
            $content = trim($chunk['content'] ?? '');
            $lines[] = "{$num}. {$citation} : {$content}";
        }

        $lines[] = "============================================================";

        return implode("\n", $lines);
    }

    /**
     * Combined retrieve & format helper.
     */
    public function retrieveAndFormat(
        Business $business,
        ?User $user,
        string $query,
        int $maxChunks = 6,
        float $minScore = 0.68
    ): string {
        $chunks = $this->retrieveRelevantKnowledge($business, $user, $query, $maxChunks, $minScore);
        return $this->formatKnowledgeForPrompt($chunks);
    }
}
