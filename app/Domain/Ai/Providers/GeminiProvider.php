<?php

declare(strict_types=1);

namespace App\Domain\Ai\Providers;

use App\Domain\Ai\Support\AiStructuredOutputParser;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

final class GeminiProvider implements AiProviderInterface
{
    private const BASE_URL = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function getProviderName(): string
    {
        return 'gemini';
    }

    public function getAvailableModels(): array
    {
        return [
            ['id' => 'gemini-2.5-flash', 'name' => 'Gemini 2.5 Flash (Generasi Terbaru, Super Kilat)', 'tier' => 'fast'],
            ['id' => 'gemini-2.5-pro', 'name' => 'Gemini 2.5 Pro (Reasoning & Multimodal Tingkat Tinggi)', 'tier' => 'balanced'],
            ['id' => 'gemini-2.0-flash', 'name' => 'Gemini 2.0 Flash (Next-Gen Production)', 'tier' => 'fast'],
            ['id' => 'gemini-2.0-flash-thinking-exp', 'name' => 'Gemini 2.0 Flash Thinking (Deep Reasoning Mode)', 'tier' => 'reasoning'],
            ['id' => 'gemini-1.5-flash', 'name' => 'Gemini 1.5 Flash (Legacy Stabil & Ringan)', 'tier' => 'fast'],
            ['id' => 'gemini-1.5-flash-8b', 'name' => 'Gemini 1.5 Flash 8B (Ultra Hemat & Cepat)', 'tier' => 'fast'],
            ['id' => 'gemini-1.5-pro', 'name' => 'Gemini 1.5 Pro (Konteks Panjang 2M Token)', 'tier' => 'balanced'],
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
                'message' => 'API Key Gemini kosong atau format tidak valid.',
            ];
        }

        try {
            $targetModel = trim($model) ?: 'gemini-2.5-flash';
            $url = self::BASE_URL . "/{$targetModel}:generateContent?key=" . urlencode($sanitizedKey);

            $response = Http::timeout(12)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => 'Ping. Respond with single word "OK".'],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 10,
                ],
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => "Koneksi ke Google Gemini ({$targetModel}) berhasil terverifikasi!",
                ];
            }

            $status = $response->status();
            $err = $response->json('error.message') ?? $response->body();

            if ($status === 400 && str_contains(strtolower($err), 'api_key')) {
                return [
                    'success' => false,
                    'message' => 'Kunci API Google Gemini tidak valid. Pastikan Anda menyalin kunci dari Google AI Studio (aistudio.google.com).',
                ];
            }

            return [
                'success' => false,
                'message' => "Gagal terhubung ke Gemini ({$status}): " . $err,
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Koneksi Gemini timeout/error: ' . $e->getMessage(),
            ];
        }
    }

    public function chat(string $apiKey, string $model, array $messages, array $options = []): array
    {
        $targetModel = trim($model) ?: 'gemini-2.5-flash';
        $sanitizedKey = $this->sanitizeApiKey($apiKey);
        $url = self::BASE_URL . "/{$targetModel}:generateContent?key=" . urlencode($sanitizedKey);

        $contents = [];
        $systemInstruction = null;

        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                $systemInstruction = ['parts' => [['text' => $msg['content']]]];
                continue;
            }

            $role = $msg['role'] === 'assistant' ? 'model' : 'user';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $msg['content']]],
            ];
        }

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.2,
                'maxOutputTokens' => $options['max_tokens'] ?? 2000,
            ],
        ];

        if ($systemInstruction) {
            $payload['systemInstruction'] = $systemInstruction;
        }

        $response = Http::timeout($options['timeout'] ?? 30)->post($url, $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Gemini Error: ' . ($response->json('error.message') ?? $response->body()));
        }

        $json = $response->json();
        $content = (string) ($json['candidates'][0]['content']['parts'][0]['text'] ?? '');

        return [
            'content' => $content,
            'raw' => $json,
            'usage' => [
                'prompt_tokens' => (int) ($json['usageMetadata']['promptTokenCount'] ?? 0),
                'completion_tokens' => (int) ($json['usageMetadata']['candidatesTokenCount'] ?? 0),
                'total_tokens' => (int) ($json['usageMetadata']['totalTokenCount'] ?? 0),
            ],
        ];
    }

    public function structuredPrompt(string $apiKey, string $model, string $systemPrompt, string $userPrompt, array $options = []): array
    {
        $targetModel = trim($model) ?: 'gemini-2.5-flash';
        $sanitizedKey = $this->sanitizeApiKey($apiKey);
        $url = self::BASE_URL . "/{$targetModel}:generateContent?key=" . urlencode($sanitizedKey);

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $systemPrompt . "\nUser request: " . $userPrompt . "\nOutput MUST be strictly RFC-8259 compliant JSON with NO markdown blocks."],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.1,
                'responseMimeType' => 'application/json',
            ],
        ];

        $response = Http::timeout($options['timeout'] ?? 30)->post($url, $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Gemini Error: ' . ($response->json('error.message') ?? $response->body()));
        }

        $content = (string) ($response->json('candidates.0.content.parts.0.text') ?? '{}');

        return AiStructuredOutputParser::parse($content);
    }
}
