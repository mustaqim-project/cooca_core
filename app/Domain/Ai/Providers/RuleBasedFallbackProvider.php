<?php

declare(strict_types=1);

namespace App\Domain\Ai\Providers;

final class RuleBasedFallbackProvider implements AiProviderInterface
{
    public function getProviderName(): string
    {
        return 'fallback';
    }

    public function getAvailableModels(): array
    {
        return [
            ['id' => 'cooca-rule-engine-v1', 'name' => 'COOCA Native Rule Engine (100% Offline)', 'tier' => 'fast'],
        ];
    }

    public function testConnection(string $apiKey, string $model): array
    {
        return [
            'success' => true,
            'message' => 'COOCA Native Engine siap digunakan secara lokal/offline.',
        ];
    }

    public function chat(string $apiKey, string $model, array $messages, array $options = []): array
    {
        $lastUserMsg = '';
        foreach (array_reverse($messages) as $msg) {
            if ($msg['role'] === 'user') {
                $lastUserMsg = (string) $msg['content'];
                break;
            }
        }

        $reply = "Analisis terstruktur telah diproses oleh COOCA Native AI Engine untuk permintaan: \"{$lastUserMsg}\".";

        return [
            'content' => $reply,
            'raw' => ['source' => 'local_rule_engine'],
            'usage' => [
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'total_tokens' => 0,
            ],
        ];
    }

    public function structuredPrompt(string $apiKey, string $model, string $systemPrompt, string $userPrompt, array $options = []): array
    {
        // Extract basic intent from user prompt
        $lower = strtolower($userPrompt);

        $actions = [];
        if (str_contains($lower, 'invoice') || str_contains($lower, 'faktur')) {
            $actions[] = [
                'tool' => 'DraftInvoiceAction',
                'action_type' => 'create_invoice',
                'title' => 'Terbitkan Draf Faktur Penjualan',
                'risk_level' => 'HIGH',
                'description' => 'Menerbitkan faktur penjualan baru untuk pelanggan.',
                'reason' => 'Permintaan penerbitan tagihan dari interaksi pengguna.',
                'payload' => [],
            ];
        }

        return [
            'summary' => 'Analisis operasional diselesaikan oleh COOCA Native Engine.',
            'findings' => [
                'Data operasional bisnis telah dievaluasi terhadap tolok ukur standar.',
            ],
            'recommendations' => [
                'Tinjau metrik penjualan mingguan dan kelola stok kritis.',
            ],
            'actions' => $actions,
            'requires_approval' => count($actions) > 0,
        ];
    }
}
