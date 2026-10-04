<?php

declare(strict_types=1);

namespace App\Domain\Ai\Tools;

use App\Models\Business;
use App\Models\User;

abstract class BaseAiTool implements AiToolInterface
{
    public function getCategory(): string
    {
        return 'READ';
    }

    public function getRiskLevel(): string
    {
        return 'LOW';
    }

    public function getRequiredPermission(): ?string
    {
        return null;
    }

    public function requiresHumanApproval(): bool
    {
        return false;
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [],
        ];
    }
}
