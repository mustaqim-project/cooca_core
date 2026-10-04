<?php

declare(strict_types=1);

namespace App\Domain\Ai\Tools;

use App\Models\Business;
use App\Models\Customer;
use App\Models\User;

final class DraftMarketingCampaignProposalTool extends BaseAiTool
{
    public function getName(): string
    {
        return 'DraftMarketingCampaignProposal';
    }

    public function getDescription(): string
    {
        return 'Menyusun usulan kampanye promosi reaktivasi pelanggan atau program diskon berkala oleh AI Marketing Agent.';
    }

    public function getCategory(): string
    {
        return 'WRITE';
    }

    public function getRiskLevel(): string
    {
        return 'MEDIUM';
    }

    public function requiresHumanApproval(): bool
    {
        return true;
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $dormantCount = Customer::where('business_id', $business->id)->count();
        $targetAudience = max(1, (int) round($dormantCount * 0.4));

        return [
            'action_type' => 'launch_marketing_campaign',
            'title' => 'Kampanye Reaktivasi Pelanggan Akhir Pekan',
            'description' => "Penawaran promo voucher diskon 15% untuk {$targetAudience} pelanggan segmen dormant & at-risk via WhatsApp dan Storefront.",
            'reason' => 'Meningkatkan frekuensi transaksi dan mengaktifkan kembali pelanggan pasif.',
            'estimated_cost' => 0.00,
            'payload' => [
                'campaign_name' => 'Weekend Customer Comeback',
                'discount_percent' => 15,
                'target_segment' => 'dormant_and_at_risk',
                'target_audience_count' => $targetAudience,
                'channels' => ['whatsapp', 'storefront'],
            ],
        ];
    }
}
