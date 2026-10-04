<?php

declare(strict_types=1);

namespace App\Domain\Ai\Tools;

use App\Models\Business;
use App\Models\User;

interface AiToolInterface
{
    /**
     * Unique tool identifier.
     */
    public function getName(): string;

    /**
     * Description of what this tool achieves.
     */
    public function getDescription(): string;

    /**
     * Category: READ, WRITE, EXTERNAL, COMMUNICATION, FINANCIAL, ADMINISTRATIVE.
     */
    public function getCategory(): string;

    /**
     * Risk rating: LOW, MEDIUM, HIGH, CRITICAL.
     */
    public function getRiskLevel(): string;

    /**
     * Required permission slug to execute this tool.
     */
    public function getRequiredPermission(): ?string;

    /**
     * Whether this tool creates an action proposal requiring human approval.
     */
    public function requiresHumanApproval(): bool;

    /**
     * JSON Schema for input arguments.
     *
     * @return array<string, mixed>
     */
    public function getInputSchema(): array;

    /**
     * Execute the tool with tenant isolation.
     *
     * @param array<string, mixed> $arguments
     * @return array<string, mixed>
     */
    public function execute(Business $business, ?User $user, array $arguments = []): array;
}
