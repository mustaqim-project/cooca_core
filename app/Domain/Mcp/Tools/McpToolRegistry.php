<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\McpAccessToken;
use InvalidArgumentException;

final class McpToolRegistry
{
    /**
     * @var array<string, McpToolInterface>
     */
    private array $tools = [];

    public function __construct()
    {
        $this->register(new FinanceRecordExpenseTool());
        $this->register(new FinanceGetCashAndBankBalancesTool());
        $this->register(new FinanceManageInvoiceTool());
        $this->register(new InventoryCreateProductTool());
        $this->register(new InventoryCheckStockTool());
        $this->register(new ModifierManageTool());
        $this->register(new InventoryManageMaterialTool());
        $this->register(new InventoryManageBomTool());
        $this->register(new InventoryStockOpnameTool());
        $this->register(new PurchasingManageSupplierTool());
        $this->register(new PurchasingManageOrderTool());
        $this->register(new SalesManageQuotationTool());
        $this->register(new SalesManageOrderTool());
        $this->register(new SocialSchedulePostTool());
        $this->register(new SocialGetInsightsTool());
        $this->register(new ReportGetProfitLossTool());
        $this->register(new AnalyticsGetSalesForecastTool());
        $this->register(new CrmSearchCustomerTool());
        $this->register(new WhatsappSendNotificationTool());
    }

    public function register(McpToolInterface $tool): void
    {
        $this->tools[$tool->getName()] = $tool;
    }

    public function hasTool(string $name): bool
    {
        return isset($this->tools[$name]);
    }

    public function getTool(string $name): McpToolInterface
    {
        if (! isset($this->tools[$name])) {
            throw new InvalidArgumentException("Tool MCP '{$name}' tidak ditemukan dalam katalog COOCA.");
        }

        return $this->tools[$name];
    }

    /**
     * @return array<string, McpToolInterface>
     */
    public function getAllTools(): array
    {
        return $this->tools;
    }

    /**
     * Get tools allowed for a specific token's abilities.
     *
     * @return array<string, McpToolInterface>
     */
    public function getToolsForToken(?McpAccessToken $token): array
    {
        if ($token === null) {
            return $this->tools;
        }

        $allowed = [];
        foreach ($this->tools as $name => $tool) {
            if ($token->hasAbility($tool->getRequiredAbility())) {
                $allowed[$name] = $tool;
            }
        }

        return $allowed;
    }
}
