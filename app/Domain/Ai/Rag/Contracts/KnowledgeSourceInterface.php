<?php

declare(strict_types=1);

namespace App\Domain\Ai\Rag\Contracts;

use App\Models\Business;
use App\Models\User;

interface KnowledgeSourceInterface
{
    /**
     * Unique identifier for this knowledge source.
     */
    public function getSourceId(): string;

    /**
     * Human-readable label for this knowledge source.
     */
    public function getLabel(): string;

    /**
     * Retrieve knowledge chunks relevant to the query for the given business.
     * Every chunk must return:
     * - 'content': string (dense factual summary)
     * - 'citation': string (source document reference e.g., '[Sumber: Faktur #INV-001]')
     * - 'score': float (relevance score 0.0 - 1.0)
     * - 'metadata': array (additional tags like category, entity_id, date)
     *
     * @param array<string, mixed> $options
     * @return array<int, array{content: string, citation: string, score: float, metadata: array<string, mixed>}>
     */
    public function retrieve(Business $business, ?User $user, string $query, array $options = []): array;
}
