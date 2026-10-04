<?php

declare(strict_types=1);

namespace App\Domain\Ai\Providers;

use App\Domain\Ai\Support\AiStructuredOutputParser;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

final class OpenRouterProvider implements AiProviderInterface
{
    private const BASE_URL = 'https://openrouter.ai/api/v1';

    public function getProviderName(): string
    {
        return 'openrouter';
    }

    public function getAvailableModels(): array
    {
        return [
            ['id' => 'anthropic/claude-3.7-sonnet', 'name' => 'Claude 3.7 Sonnet via OpenRouter', 'tier' => 'reasoning'],
            ['id' => 'anthropic/claude-3.5-sonnet', 'name' => 'Claude 3.5 Sonnet via OpenRouter', 'tier' => 'balanced'],
            ['id' => 'anthropic/claude-3.5-haiku', 'name' => 'Claude 3.5 Haiku via OpenRouter', 'tier' => 'fast'],
            ['id' => 'openai/gpt-4o', 'name' => 'GPT-4o via OpenRouter', 'tier' => 'balanced'],
            ['id' => 'openai/gpt-4o-mini', 'name' => 'GPT-4o Mini via OpenRouter', 'tier' => 'fast'],
            ['id' => 'google/gemini-2.5-flash', 'name' => 'Gemini 2.5 Flash via OpenRouter', 'tier' => 'fast'],
            ['id' => 'deepseek/deepseek-r1', 'name' => 'DeepSeek R1 (High Reasoning) via OpenRouter', 'tier' => 'reasoning'],
            ['id' => 'deepseek/deepseek-chat', 'name' => 'DeepSeek V3 Chat via OpenRouter', 'tier' => 'fast'],
            ['id' => 'meta-llama/llama-3.3-70b-instruct', 'name' => 'Llama 3.3 70B (Open Source Premium)', 'tier' => 'balanced'],
            ['id' => 'mistralai/mistral-large-2411', 'name' => 'Mistral Large 2 via OpenRouter', 'tier' => 'balanced'],
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
                'message' => 'API Key OpenRouter kosong atau format tidak valid.',
            ];
        }

        $targetModel = trim($model) ?: 'google/gemini-2.5-flash';

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $sanitizedKey,
                'HTTP-Referer' => config('app.url', 'https://cooca.id'),
                'X-Title' => 'COOCA AI Digital Company',
            ])->timeout(12)->post(self::BASE_URL . '/chat/completions', [
                'model' => $targetModel,
                'messages' => [
                    ['role' => 'user', 'content' => 'Ping. Respond with single word "OK".'],
                ],
                'max_tokens' => 20,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => "Koneksi ke OpenRouter ({$targetModel}) berhasil terverifikasi!",
                ];
            }

            $status = $response->status();
            $err = $response->json('error.message') ?? $response->body();

            if ($status === 401) {
                return [
                    'success' => false,
                    'message' => 'Kunci API OpenRouter tidak valid (401). Pastikan kunci diawali "sk-or-v1-" dan tidak terpotong.',
                ];
            }

            if ($status === 402 || str_contains(strtolower($err), 'credit')) {
                return [
                    'success' => false,
                    'message' => 'Gagal terhubung ke OpenRouter: Akun OpenRouter Anda tidak memiliki kredit saldo yang cukup. Silakan top up di openrouter.ai/credits.',
                ];
            }

            return [
                'success' => false,
                'message' => "Gagal terhubung ke OpenRouter ({$status}): " . $err,
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Koneksi OpenRouter timeout/error: ' . $e->getMessage(),
            ];
        }
    }

    public function chat(string $apiKey, string $model, array $messages, array $options = []): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->sanitizeApiKey($apiKey),
            'HTTP-Referer' => config('app.url', 'https://cooca.id'),
            'X-Title' => 'COOCA AI Digital Company',
        ])->timeout($options['timeout'] ?? 30)->post(self::BASE_URL . '/chat/completions', [
            'model' => $model ?: 'google/gemini-2.5-flash',
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.2,
            'max_tokens' => $options['max_tokens'] ?? 2000,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('OpenRouter Error: ' . ($response->json('error.message') ?? $response->body()));
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
            ['role' => 'system', 'content' => $systemPrompt . "\nOutput MUST be valid RFC-8259 JSON only. No markdown formatting, no backticks."],
            ['role' => 'user', 'content' => $userPrompt],
        ];

        $payload = [
            'model' => $model ?: 'google/gemini-2.5-flash',
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.1,
            'response_format' => ['type' => 'json_object'],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->sanitizeApiKey($apiKey),
            'HTTP-Referer' => config('app.url', 'https://cooca.id'),
            'X-Title' => 'COOCA AI Digital Company',
        ])->timeout($options['timeout'] ?? 30)->post(self::BASE_URL . '/chat/completions', $payload);

        if (! $response->successful()) {
            throw new RuntimeException('OpenRouter Error: ' . ($response->json('error.message') ?? $response->body()));
        }

        $content = (string) ($response->json('choices.0.message.content') ?? '{}');

        return AiStructuredOutputParser::parse($content);
    }
}
