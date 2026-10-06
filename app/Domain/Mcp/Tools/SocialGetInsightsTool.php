<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Domain\SocialMedia\SocialMediaService;
use App\Models\Business;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use App\Models\User;

final class SocialGetInsightsTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'social_get_insights';
    }

    public function getDescription(): string
    {
        return 'Melihat performa engagement media sosial bisnis: impresi, jangkauan, jumlah postingan, dan status akun terhubung.';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:social:read';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'days' => [
                    'type' => 'integer',
                    'default' => 30,
                    'description' => 'Rentang hari analisis data (7, 14, 30, atau 90 hari).',
                ],
            ],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $days = max(1, (int) ($arguments['days'] ?? 30));
        $startDate = now()->subDays($days);

        $socialService = app(SocialMediaService::class);
        $summary = $socialService->getSummary($business);

        $recentPosts = SocialMediaPost::where('business_id', $business->id)
            ->where('created_at', '>=', $startDate)
            ->with(['targets'])
            ->latest()
            ->take(10)
            ->get();

        $connectedAccounts = SocialMediaAccount::where('business_id', $business->id)
            ->where('status', 'active')
            ->get()
            ->map(fn ($acc) => [
                'platform' => $acc->platform,
                'name' => $acc->account_name,
                'username' => $acc->username,
            ]);

        $postsList = $recentPosts->map(fn ($p) => [
            'id' => $p->id,
            'platform' => $p->platform,
            'caption_preview' => mb_strimwidth((string) $p->content, 0, 80, '...'),
            'status' => $p->status,
            'published_at' => $p->published_at?->format('Y-m-d H:i') ?? $p->scheduled_at?->format('Y-m-d H:i'),
            'metrics' => $p->metrics ?? [],
        ]);

        return [
            'status' => 'success',
            'period_days' => $days,
            'summary' => $summary,
            'connected_accounts' => $connectedAccounts,
            'recent_posts' => $postsList,
        ];
    }
}
