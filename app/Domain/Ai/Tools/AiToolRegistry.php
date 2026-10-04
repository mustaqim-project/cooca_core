<?php

declare(strict_types=1);

namespace App\Domain\Ai\Tools;

use App\Domain\Ai\Organization\AgentRole;
use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use InvalidArgumentException;
use RuntimeException;

final class AiToolRegistry
{
    /**
     * @var array<string, AiToolInterface>
     */
    private array $tools = [];

    public function __construct()
    {
        $this->register(new GetSalesSummaryTool());
        $this->register(new GetSalesTrendTool());
        $this->register(new GetTopProductsTool());
        $this->register(new GetStockLevelsTool());
        $this->register(new DetectAnomaliesAndFraudTool());
        $this->register(new GetCustomerSummaryTool());
        $this->register(new GetFinancialHealthTool());
        $this->register(new GetAttendanceSummaryTool());
        $this->register(new DraftInvoiceProposalTool());
        $this->register(new DraftPurchaseOrderProposalTool());
        $this->register(new DraftMarketingCampaignProposalTool());
        $this->register(new DraftSocialPostProposalTool());
    }

    public function register(AiToolInterface $tool): void
    {
        $this->tools[$tool->getName()] = $tool;
    }

    public function getTool(string $name): AiToolInterface
    {
        if (! isset($this->tools[$name])) {
            throw new InvalidArgumentException("AI Tool '{$name}' tidak terdaftar.");
        }

        return $this->tools[$name];
    }

    /**
     * @return array<string, AiToolInterface>
     */
    public function getAllTools(): array
    {
        return $this->tools;
    }

    /**
     * Get available tools mapped to a specific agent.
     *
     * @return array<string, AiToolInterface>
     */
    public function getToolsForAgent(AgentRole $agent): array
    {
        return match ($agent) {
            AgentRole::BUSINESS => [
                'GetSalesSummary' => $this->tools['GetSalesSummary'],
                'GetStockLevels' => $this->tools['GetStockLevels'],
                'GetFinancialHealth' => $this->tools['GetFinancialHealth'],
                'DetectAnomaliesAndFraud' => $this->tools['DetectAnomaliesAndFraud'],
            ],
            AgentRole::SALES => [
                'GetSalesSummary' => $this->tools['GetSalesSummary'],
                'GetSalesTrend' => $this->tools['GetSalesTrend'],
                'GetTopProducts' => $this->tools['GetTopProducts'],
                'DraftInvoiceProposal' => $this->tools['DraftInvoiceProposal'],
            ],
            AgentRole::CUSTOMER => [
                'GetCustomerSummary' => $this->tools['GetCustomerSummary'],
            ],
            AgentRole::INVENTORY => [
                'GetStockLevels' => $this->tools['GetStockLevels'],
            ],
            AgentRole::PURCHASING => [
                'GetStockLevels' => $this->tools['GetStockLevels'],
                'DraftPurchaseOrderProposal' => $this->tools['DraftPurchaseOrderProposal'],
            ],
            AgentRole::FINANCE => [
                'GetFinancialHealth' => $this->tools['GetFinancialHealth'],
                'DetectAnomaliesAndFraud' => $this->tools['DetectAnomaliesAndFraud'],
            ],
            AgentRole::REPORTING => [
                'GetSalesSummary' => $this->tools['GetSalesSummary'],
                'GetFinancialHealth' => $this->tools['GetFinancialHealth'],
            ],
            AgentRole::MARKETING => [
                'GetCustomerSummary' => $this->tools['GetCustomerSummary'],
                'DraftMarketingCampaignProposal' => $this->tools['DraftMarketingCampaignProposal'],
            ],
            AgentRole::CONTENT, AgentRole::SOCIAL_MEDIA => [
                'DraftSocialPostProposal' => $this->tools['DraftSocialPostProposal'],
            ],
            AgentRole::HR => [
                'GetAttendanceSummary' => $this->tools['GetAttendanceSummary'],
            ],
            default => $this->tools,
        };
    }

    /**
     * Execute a tool securely with tenant and permission validation.
     *
     * @param array<string, mixed> $arguments
     * @return array<string, mixed>
     */
    public function executeTool(string $toolName, Business $business, ?User $user, array $arguments = []): array
    {
        $tool = $this->getTool($toolName);

        // Permission check
        $requiredPerm = $tool->getRequiredPermission();
        if ($requiredPerm !== null && $user !== null) {
            if (! Context::isAdminOrOwner() && ! in_array($requiredPerm, Context::permissions(), true)) {
                throw new RuntimeException("Akses ditolak: User tidak memiliki hak akses '{$requiredPerm}' untuk menjalankan tool {$toolName}.");
            }
        }

        return $tool->execute($business, $user, $arguments);
    }
}
