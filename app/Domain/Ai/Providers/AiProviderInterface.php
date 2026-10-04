<?php

declare(strict_types=1);

namespace App\Domain\Ai\Providers;

interface AiProviderInterface
{
    /**
     * Get unique provider identifier (openai, gemini, anthropic, openrouter, fallback).
     */
    public function getProviderName(): string;

    /**
     * Test connection with given credentials and model.
     *
     * @return array{success: bool, message: string}
     */
    public function testConnection(string $apiKey, string $model): array;

    /**
     * Send chat completion request.
     *
     * @param array<int, array{role: string, content: string}> $messages
     * @param array<string, mixed> $options
     * @return array{content: string, raw: array<string, mixed>, usage: array<string, int>}
     */
    public function chat(string $apiKey, string $model, array $messages, array $options = []): array;

    /**
     * Send structured prompt expecting validated JSON output.
     *
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function structuredPrompt(string $apiKey, string $model, string $systemPrompt, string $userPrompt, array $options = []): array;

    /**
     * Get list of recommended model identifiers for this provider.
     *
     * @return array<int, array{id: string, name: string, tier: string}>
     */
    public function getAvailableModels(): array;
}
