<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\Business;
use App\Models\User;

interface McpToolInterface
{
    /**
     * Unique MCP tool name (e.g. finance_record_expense).
     */
    public function getName(): string;

    /**
     * Descriptive explanation of what this tool achieves for the AI model.
     */
    public function getDescription(): string;

    /**
     * Standard JSON Schema representation of the tool's input arguments.
     *
     * @return array<string, mixed>
     */
    public function getInputSchema(): array;

    /**
     * The granular token ability required to execute this tool.
     */
    public function getRequiredAbility(): string;

    /**
     * Execute the tool within an isolated business tenant context.
     *
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    public function execute(Business $business, ?User $user, array $arguments = []): array;
}
