<?php

declare(strict_types=1);

namespace App\Domain\Ai\Providers;

use App\Domain\Ai\Support\AiStructuredOutputParser;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

final class OpenAiProvider implements AiProviderInterface
{
    private const BASE_URL = 'https://api.openai.com/v1';

    public function getProviderName(): string
    {
        return 'openai';
    }

    public function getAvailableModels(): array
    {
        return [
            ['id' => 'gpt-4o', 'name' => 'GPT-4o (Omnimodal Flagship)', 'tier' => 'balanced'],
            ['id' => 'gpt-4o-mini', 'name' => 'GPT-4o Mini (Hemat, Cepat & Cerdas)', 'tier' => 'fast'],
            ['id' => 'o3-mini', 'name' => 'o3-mini (High-Performance Reasoning)', 'tier' => 'reasoning'],
            ['id' => 'o1', 'name' => 'o1 (Deep Logic & Multimodal Reasoning)', 'tier' => 'reasoning'],
            ['id' => 'o1-mini', 'name' => 'o1-mini (Efisien Reasoning)', 'tier' => 'reasoning'],
            ['id' => 'gpt-4-turbo', 'name' => 'GPT-4 Turbo (128K Konteks)', 'tier' => 'balanced'],
            ['id' => 'gpt-3.5-turbo', 'name' => 'GPT-3.5 Turbo (Legacy)', 'tier' => 'fast'],
        ];
    }

    /**
     * Sanitize API key by stripping quotes, whitespace, Bearer prefixes.
     */
    public function sanitizeApiKey(string $apiKey): string
    {
        $key = trim($apiKey, " \t\n\r\0\x0B\"'");
        if (str_starts_with(strtolower($key), 'bearer ')) {
            $key = trim(substr($key, 7));
        }

        return trim($key);
    }

    public function testConnection(string $apiKey, string $model): array
    {
        $sanitizedKey = $this->sanitizeApiKey($apiKey);
        if ($sanitizedKey === '') {
            return [
                'success' => false,
                'message' => 'API Key OpenAI kosong atau format tidak valid.',
            ];
        }

        $targetModel = trim($model) ?: 'gpt-4o-mini';

        try {
            $response = Http::withToken($sanitizedKey)
                ->timeout(12)
                ->post(self::BASE_URL . '/chat/completions', [
                    'model' => $targetModel,
                    'messages' => [
                        ['role' => 'user', 'content' => 'Ping. Respond with single word "OK".'],
                    ],
                    'max_tokens' => 20,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => "Koneksi ke OpenAI ({$targetModel}) berhasil terverifikasi!",
                ];
            }

            $status = $response->status();
            $err = $response->json('error.message') ?? $response->body();

            if ($status === 401 || str_contains(strtolower($err), 'invalid api key')) {
                return [
                    'success' => false,
                    'message' => 'Kunci API OpenAI tidak valid (401). Pastikan kunci diawali "sk-" atau "sk-proj-" dan tidak memiliki spasi tambahan.',
                ];
            }

            if ($status === 429 || str_contains(strtolower($err), 'quota') || str_contains(strtolower($err), 'credit')) {
                return [
                    'success' => false,
                    'message' => 'Gagal terhubung ke OpenAI: Kuota kredit akun OpenAI Anda habis. Silakan isi billing di platform.openai.com/settings/organization/billing.',
                ];
            }

            return [
                'success' => false,
                'message' => "Gagal terhubung ke OpenAI ({$status}): " . $err,
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Koneksi OpenAI timeout/error: ' . $e->getMessage(),
            ];
        }
    }

    public function chat(string $apiKey, string $model, array $messages, array $options = []): array
    {
        $response = Http::withToken($this->sanitizeApiKey($apiKey))
            ->timeout($options['timeout'] ?? 30)
            ->post(self::BASE_URL . '/chat/completions', [
                'model' => $model ?: 'gpt-4o-mini',
                'messages' => $messages,
                'temperature' => $options['temperature'] ?? 0.2,
                'max_tokens' => $options['max_tokens'] ?? 2000,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('OpenAI Error: ' . ($response->json('error.message') ?? $response->body()));
        }

        $json = $response->json();
        $content = (string) ($json['choices'][0]['message']['content'] ?? '');

        return [
            'content' => $content,
            'raw' => $json,
            'usage' => [
                'prompt_tokens' => (int) ($json['usage']['prompt_tokens'] ?? 0),
                'completion_tokens' => (int) ($json['usage']['completion_tokens'] ?? 0),
                'total_tokens' => (int) ($json['usage']['total_tokens'] ?? 0),
            ],
        ];
    }

    public function structuredPrompt(string $apiKey, string $model, string $systemPrompt, string $userPrompt, array $options = []): array
    {
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt . "\nOutput MUST be valid RFC-8259 JSON only. No markdown formatting, no backticks, no comments."],
            ['role' => 'user', 'content' => $userPrompt],
        ];

        $payload = [
            'model' => $model ?: 'gpt-4o-mini',
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.1,
            'response_format' => ['type' => 'json_object'],
        ];

        $response = Http::withToken($this->sanitizeApiKey($apiKey))
            ->timeout($options['timeout'] ?? 30)
            ->post(self::BASE_URL . '/chat/completions', $payload);

        if (! $response->successful()) {
            throw new RuntimeException('OpenAI Error: ' . ($response->json('error.message') ?? $response->body()));
        }

        $content = (string) ($response->json('choices.0.message.content') ?? '{}');

        return AiStructuredOutputParser::parse($content);
    }
}
