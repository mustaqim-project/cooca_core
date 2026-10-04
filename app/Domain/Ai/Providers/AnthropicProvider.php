<?php

declare(strict_types=1);

namespace App\Domain\Ai\Providers;

use App\Domain\Ai\Support\AiStructuredOutputParser;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

final class AnthropicProvider implements AiProviderInterface
{
    private const BASE_URL = 'https://api.anthropic.com/v1';
    private const ANTHROPIC_VERSION = '2023-06-01';

    public function getProviderName(): string
    {
        return 'anthropic';
    }

    public function getAvailableModels(): array
    {
        return [
            ['id' => 'claude-haiku-4-5-20251001', 'name' => 'Claude Haiku 4.5 (Super Kilat ~1.3s, Efisien & Hemat)', 'tier' => 'fast'],
            ['id' => 'claude-sonnet-4-5-20250929', 'name' => 'Claude Sonnet 4.5 (Analisis Bisnis Seimbang & Mendalam)', 'tier' => 'balanced'],
            ['id' => 'claude-sonnet-4-6', 'name' => 'Claude Sonnet 4.6 (Next-Gen Performa Tinggi)', 'tier' => 'balanced'],
            ['id' => 'claude-sonnet-5', 'name' => 'Claude Sonnet 5 (Flagship Generasi 5)', 'tier' => 'balanced'],
            ['id' => 'claude-sonnet-5-5', 'name' => 'Claude Sonnet 5.5 (Flagship Ultra)', 'tier' => 'balanced'],
            ['id' => 'claude-opus-4-5-20251101', 'name' => 'Claude Opus 4.5 (Deep Reasoning & Kompleksitas Tinggi)', 'tier' => 'reasoning'],
            ['id' => 'claude-opus-4-6', 'name' => 'Claude Opus 4.6 (Ultra Deep Reasoning)', 'tier' => 'reasoning'],
            ['id' => 'claude-opus-4-7', 'name' => 'Claude Opus 4.7', 'tier' => 'reasoning'],
            ['id' => 'claude-opus-4-8', 'name' => 'Claude Opus 4.8', 'tier' => 'reasoning'],
            ['id' => 'claude-opus-5', 'name' => 'Claude Opus 5', 'tier' => 'reasoning'],
            ['id' => 'claude-opus-5-5', 'name' => 'Claude Opus 5.5', 'tier' => 'reasoning'],
            ['id' => 'claude-fable-5', 'name' => 'Claude Fable 5', 'tier' => 'balanced'],
            ['id' => 'claude-fable-5-1', 'name' => 'Claude Fable 5.1', 'tier' => 'balanced'],
            ['id' => 'claude-3-7-sonnet-latest', 'name' => 'Claude 3.7 Sonnet (Hybrid Reasoning, Legacy)', 'tier' => 'reasoning'],
            ['id' => 'claude-3-5-sonnet-latest', 'name' => 'Claude 3.5 Sonnet (Legacy)', 'tier' => 'balanced'],
            ['id' => 'claude-3-5-haiku-latest', 'name' => 'Claude 3.5 Haiku (Legacy)', 'tier' => 'fast'],
        ];
    }

    /**
     * Dynamically fetch list of accessible models from Anthropic API using provided key.
     *
     * @return array<int, array{id: string, name: string, tier: string}>
     */
    public function fetchAvailableModels(string $apiKey): array
    {
        $sanitizedKey = $this->sanitizeApiKey($apiKey);
        if ($sanitizedKey === '') {
            return $this->getAvailableModels();
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $sanitizedKey,
                'anthropic-version' => self::ANTHROPIC_VERSION,
            ])->timeout(10)->get(self::BASE_URL . '/models');

            if ($response->successful()) {
                $data = $response->json('data') ?? [];
                $discovered = [];
                foreach ($data as $m) {
                    $id = (string) ($m['id'] ?? '');
                    $displayName = (string) ($m['display_name'] ?? $id);
                    $line = strtolower((string) ($m['line'] ?? ''));

                    $tier = match (true) {
                        str_contains($line, 'opus') || str_contains($id, 'opus') => 'reasoning',
                        str_contains($line, 'haiku') || str_contains($id, 'haiku') => 'fast',
                        default => 'balanced',
                    };

                    $discovered[] = [
                        'id' => $id,
                        'name' => $displayName . " ({$id})",
                        'tier' => $tier,
                    ];
                }

                if (! empty($discovered)) {
                    return $discovered;
                }
            }
        } catch (Throwable) {
            // Fallback to static catalog on connection/timeout error
        }

        return $this->getAvailableModels();
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
        if (str_starts_with(strtolower($key), 'x-api-key:')) {
            $key = trim(substr($key, 10));
        }

        return trim($key);
    }

    public function testConnection(string $apiKey, string $model): array
    {
        $sanitizedKey = $this->sanitizeApiKey($apiKey);
        if ($sanitizedKey === '') {
            return [
                'success' => false,
                'message' => 'API Key Anthropic kosong atau format tidak valid.',
            ];
        }

        $targetModel = trim($model) ?: 'claude-haiku-4-5-20251001';
        $startTime = microtime(true);

        try {
            $response = Http::withHeaders([
                'x-api-key' => $sanitizedKey,
                'anthropic-version' => self::ANTHROPIC_VERSION,
                'content-type' => 'application/json',
            ])->timeout(15)->post(self::BASE_URL . '/messages', [
                'model' => $targetModel,
                'max_tokens' => 20,
                'messages' => [
                    ['role' => 'user', 'content' => 'Ping. Respond with OK'],
                ],
            ]);

            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $discovered = $this->fetchAvailableModels($sanitizedKey);

                return [
                    'success' => true,
                    'message' => "Koneksi ke Anthropic Claude ({$targetModel}) berhasil terverifikasi! (Latensi: {$latencyMs}ms)",
                    'latency_ms' => $latencyMs,
                    'model' => $targetModel,
                    'available_models' => $discovered,
                ];
            }

            $status = $response->status();
            $errData = $response->json('error');
            $errMsg = is_array($errData) ? ($errData['message'] ?? '') : ($response->json('error.message') ?? $response->body());

            // 1. Check for invalid API key
            if ($status === 401 || str_contains(strtolower($errMsg), 'invalid x-api-key') || str_contains(strtolower($errMsg), 'authentication')) {
                return [
                    'success' => false,
                    'message' => 'Kunci API Anthropic tidak valid (401 invalid x-api-key). Pastikan Anda menyalin seluruh kunci API (diawali "sk-ant-") dari console.anthropic.com tanpa spasi tambahan.',
                ];
            }

            // 2. Check for missing credits / billing
            if ($status === 400 && (str_contains(strtolower($errMsg), 'credit') || str_contains(strtolower($errMsg), 'balance') || str_contains(strtolower($errMsg), 'billing'))) {
                return [
                    'success' => false,
                    'message' => 'Akun Anthropic Anda kehabisan kredit / belum mengisi billing. Isi saldo di https://console.anthropic.com/settings/billing.',
                ];
            }

            // 3. Check for model not found / no access -> Automatically discover models and fallback to working model
            if ($status === 404 || str_contains(strtolower($errMsg), 'not_found') || str_contains(strtolower($errMsg), 'model')) {
                $discovered = $this->fetchAvailableModels($sanitizedKey);
                if (! empty($discovered)) {
                    // Try the best working model from discovered list
                    $fallbackModel = $discovered[0]['id'];
                    $tFallback = microtime(true);
                    $retry = Http::withHeaders([
                        'x-api-key' => $sanitizedKey,
                        'anthropic-version' => self::ANTHROPIC_VERSION,
                        'content-type' => 'application/json',
                    ])->timeout(15)->post(self::BASE_URL . '/messages', [
                        'model' => $fallbackModel,
                        'max_tokens' => 20,
                        'messages' => [
                            ['role' => 'user', 'content' => 'Ping. Respond with OK'],
                        ],
                    ]);

                    $fallbackLatency = (int) round((microtime(true) - $tFallback) * 1000);

                    if ($retry->successful()) {
                        return [
                            'success' => true,
                            'message' => "Model sebelumnya ('{$targetModel}') tidak tersedia, namun koneksi berhasil diverifikasi menggunakan model aktif '{$fallbackModel}'! (Latensi: {$fallbackLatency}ms)",
                            'latency_ms' => $fallbackLatency,
                            'model' => $fallbackModel,
                            'available_models' => $discovered,
                        ];
                    }
                }

                return [
                    'success' => false,
                    'message' => "Model '{$targetModel}' tidak ditemukan atau akun Anda belum memiliki izin ke model ini. Silakan pilih salah satu model aktif yang tersedia di akun Anda.",
                    'available_models' => $discovered ?? [],
                ];
            }

            return [
                'success' => false,
                'message' => "Gagal terhubung ke Anthropic (HTTP {$status}): " . ($errMsg ?: $response->body()),
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Koneksi Anthropic gagal / timeout: ' . $e->getMessage(),
            ];
        }
    }

    public function chat(string $apiKey, string $model, array $messages, array $options = []): array
    {
        $systemPrompt = '';
        $filteredMessages = [];

        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                $systemPrompt .= ($systemPrompt !== '' ? "\n" : '') . $msg['content'];
            } else {
                $filteredMessages[] = [
                    'role' => $msg['role'],
                    'content' => $msg['content'],
                ];
            }
        }

        $payload = [
            'model' => $model ?: 'claude-haiku-4-5-20251001',
            'max_tokens' => $options['max_tokens'] ?? 2000,
            'temperature' => $options['temperature'] ?? 0.2,
            'messages' => $filteredMessages,
        ];

        if ($systemPrompt !== '') {
            $payload['system'] = $systemPrompt;
        }

        $cleanKey = $this->sanitizeApiKey($apiKey);

        $response = Http::withHeaders([
            'x-api-key' => $cleanKey,
            'anthropic-version' => self::ANTHROPIC_VERSION,
            'content-type' => 'application/json',
        ])->timeout($options['timeout'] ?? 30)->post(self::BASE_URL . '/messages', $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Anthropic Error: ' . ($response->json('error.message') ?? $response->body()));
        }

        $json = $response->json();
        $content = (string) ($json['content'][0]['text'] ?? '');

        return [
            'content' => $content,
            'raw' => $json,
            'usage' => [
                'prompt_tokens' => (int) ($json['usage']['input_tokens'] ?? 0),
                'completion_tokens' => (int) ($json['usage']['output_tokens'] ?? 0),
                'total_tokens' => (int) (($json['usage']['input_tokens'] ?? 0) + ($json['usage']['output_tokens'] ?? 0)),
            ],
        ];
    }

    public function structuredPrompt(string $apiKey, string $model, string $systemPrompt, string $userPrompt, array $options = []): array
    {
        $system = $systemPrompt . "\nOutput MUST be valid RFC-8259 JSON only. Do not wrap in markdown or backticks.";

        $payload = [
            'model' => $model ?: 'claude-haiku-4-5-20251001',
            'max_tokens' => $options['max_tokens'] ?? 4096,
            'temperature' => $options['temperature'] ?? 0.1,
            'system' => $system,
            'messages' => [
                ['role' => 'user', 'content' => $userPrompt],
            ],
        ];

        $cleanKey = $this->sanitizeApiKey($apiKey);

        $response = Http::withHeaders([
            'x-api-key' => $cleanKey,
            'anthropic-version' => self::ANTHROPIC_VERSION,
            'content-type' => 'application/json',
        ])->timeout($options['timeout'] ?? 30)->post(self::BASE_URL . '/messages', $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Anthropic Error: ' . ($response->json('error.message') ?? $response->body()));
        }

        $content = (string) ($response->json('content.0.text') ?? '{}');

        return AiStructuredOutputParser::parse($content);
    }
}
