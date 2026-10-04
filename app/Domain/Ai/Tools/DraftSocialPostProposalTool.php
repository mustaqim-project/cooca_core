<?php

declare(strict_types=1);

namespace App\Domain\Ai\Tools;

use App\Models\Business;
use App\Models\User;

final class DraftSocialPostProposalTool extends BaseAiTool
{
    public function getName(): string
    {
        return 'DraftSocialPostProposal';
    }

    public function getDescription(): string
    {
        return 'Menyusun materi draf postingan media sosial (Instagram, TikTok, Facebook) yang disiapkan oleh AI Content & Social Media Agent.';
    }

    public function getCategory(): string
    {
        return 'EXTERNAL';
    }

    public function getRiskLevel(): string
    {
        return 'HIGH';
    }

    public function requiresHumanApproval(): bool
    {
        return true;
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $bizName = $business->name;
        $caption = (string) ($arguments['caption'] ?? "Kabar gembira dari {$bizName}! Nikmati menu favorit dan penawaran spesial minggu ini langsung di outlet kami.");

        return [
            'action_type' => 'publish_social_post',
            'title' => "Publikasi Postingan Medsos: {$bizName}",
            'description' => "Penayangan postingan promosi ke saluran media sosial bisnis.",
            'reason' => 'Meningkatkan kesadaran merek (brand awareness) dan mengumumkan promo terpadu.',
            'estimated_cost' => 0.00,
            'payload' => [
                'caption' => $caption,
                'platforms' => ['instagram', 'facebook', 'tiktok'],
                'scheduled_for' => now()->addHours(2)->toDateTimeString(),
            ],
        ];
    }
}
