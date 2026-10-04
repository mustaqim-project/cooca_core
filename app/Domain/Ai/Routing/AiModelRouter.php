<?php

declare(strict_types=1);

namespace App\Domain\Ai\Routing;

final class AiModelRouter
{
    public const TIER_FAST = 'fast';
    public const TIER_BALANCED = 'balanced';
    public const TIER_REASONING = 'reasoning';

    /**
     * Determine optimal model tier for a given task type or intent.
     */
    public function determineTier(string $intent, string $taskType = 'chat'): string
    {
        $q = strtolower($intent);

        // Deep strategic analysis or troubleshooting complex declines
        if (str_contains($q, 'turun') || str_contains($q, 'penyebab') || str_contains($q, 'strategi') || str_contains($q, 'evaluasi') || $taskType === 'diagnosis') {
            return self::TIER_REASONING;
        }

        // Content generation
        if (str_contains($q, 'konten') || str_contains($q, 'caption') || str_contains($q, 'promo') || str_contains($q, 'copywriting')) {
            return self::TIER_BALANCED;
        }

        // Simple queries, metrics, reports, status checks
        return self::TIER_FAST;
    }

    /**
     * Select actual model name for a provider based on tier preference.
     */
    public function selectModel(string $providerName, string $tier, string $defaultModel): string
    {
        // If merchant explicitly selected a custom model, keep it
        if ($defaultModel !== '') {
            return $defaultModel;
        }

        return match ($providerName) {
            'openai' => match ($tier) {
                self::TIER_REASONING => 'o3-mini',
                self::TIER_BALANCED => 'gpt-4o',
                default => 'gpt-4o-mini',
            },
            'gemini' => match ($tier) {
                self::TIER_REASONING => 'gemini-2.5-pro',
                self::TIER_BALANCED => 'gemini-2.5-pro',
                default => 'gemini-2.5-flash',
            },
            'anthropic' => match ($tier) {
                self::TIER_REASONING => 'claude-opus-4-5-20251101',
                self::TIER_BALANCED => 'claude-sonnet-4-5-20250929',
                default => 'claude-haiku-4-5-20251001',
            },
            'openrouter' => match ($tier) {
                self::TIER_REASONING => 'deepseek/deepseek-r1',
                self::TIER_BALANCED => 'meta-llama/llama-3.3-70b-instruct',
                default => 'google/gemini-2.5-flash',
            },
            default => 'cooca-rule-engine-v1',
        };
    }
}
