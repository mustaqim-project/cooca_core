<?php

declare(strict_types=1);

namespace App\Domain\Ai\Organization;

enum ExecutiveRole: string
{
    case CEO = 'ceo';
    case COO = 'coo';
    case CFO = 'cfo';
    case CMO = 'cmo';
    case HR_LEAD = 'hr_lead';
    case SALES_DIRECTOR = 'sales_director';

    public function label(): string
    {
        return match ($this) {
            self::CEO => 'AI CEO',
            self::COO => 'AI COO',
            self::CFO => 'AI CFO',
            self::CMO => 'AI CMO',
            self::HR_LEAD => 'AI HR Lead',
            self::SALES_DIRECTOR => 'AI Sales Director',
        };
    }

    public function title(): string
    {
        return match ($this) {
            self::CEO => 'Chief Executive Officer (Strategi & Kesehatan Bisnis)',
            self::COO => 'Chief Operating Officer (Orkestrator Operasional)',
            self::CFO => 'Chief Financial Officer (Keuangan & Akuntansi)',
            self::CMO => 'Chief Marketing Officer (Pemasaran & Pertumbuhan)',
            self::HR_LEAD => 'Head of People & Culture (SDM & Produktivitas)',
            self::SALES_DIRECTOR => 'Sales Director (Penjualan & Relasi Pelanggan)',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::CEO => 'crown',
            self::COO => 'cpu',
            self::CFO => 'banknote',
            self::CMO => 'megaphone',
            self::HR_LEAD => 'users',
            self::SALES_DIRECTOR => 'trending-up',
        };
    }
}
