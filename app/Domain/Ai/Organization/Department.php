<?php

declare(strict_types=1);

namespace App\Domain\Ai\Organization;

enum Department: string
{
    case EXECUTIVE = 'executive';
    case SALES = 'sales';
    case MARKETING = 'marketing';
    case FINANCE = 'finance';
    case OPERATIONS = 'operations';
    case PEOPLE = 'people';

    public function label(): string
    {
        return match ($this) {
            self::EXECUTIVE => 'Executive Suite',
            self::SALES => 'Sales Department',
            self::MARKETING => 'Marketing Department',
            self::FINANCE => 'Finance Department',
            self::OPERATIONS => 'Operations Department',
            self::PEOPLE => 'People & HR Department',
        };
    }

    public function executiveLead(): ExecutiveRole
    {
        return match ($this) {
            self::EXECUTIVE => ExecutiveRole::CEO,
            self::SALES => ExecutiveRole::SALES_DIRECTOR,
            self::MARKETING => ExecutiveRole::CMO,
            self::FINANCE => ExecutiveRole::CFO,
            self::OPERATIONS => ExecutiveRole::COO,
            self::PEOPLE => ExecutiveRole::HR_LEAD,
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::EXECUTIVE => 'briefcase',
            self::SALES => 'badge-dollar-sign',
            self::MARKETING => 'sparkles',
            self::FINANCE => 'wallet',
            self::OPERATIONS => 'boxes',
            self::PEOPLE => 'user-check',
        };
    }
}
