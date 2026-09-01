<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Business;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\StockOpname;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DocumentNumberGenerator
{
    /**
     * Generate unique concurrency-safe Invoice Number.
     * Format: INV-YYYY-XXXXX
     */
    public static function generateInvoiceNumber(string $businessId, string $prefix = 'INV'): string
    {
        $year = Carbon::now()->format('Y');
        $count = Invoice::where('business_id', $businessId)
            ->whereYear('created_at', $year)
            ->count();

        $nextSeq = str_pad((string)($count + 1), 5, '0', STR_PAD_LEFT);
        return "{$prefix}-{$year}-{$nextSeq}";
    }

    /**
     * Generate unique concurrency-safe Purchase Order Number.
     * Format: PO-YYYY-XXXXX
     */
    public static function generatePoNumber(string $businessId, string $prefix = 'PO'): string
    {
        $year = Carbon::now()->format('Y');
        $count = PurchaseOrder::where('business_id', $businessId)
            ->whereYear('created_at', $year)
            ->count();

        $nextSeq = str_pad((string)($count + 1), 5, '0', STR_PAD_LEFT);
        return "{$prefix}-{$year}-{$nextSeq}";
    }

    /**
     * Generate unique Quotation Number.
     * Format: QUO-YYYY-XXXXX
     */
    public static function generateQuotationNumber(string $businessId, string $prefix = 'QUO'): string
    {
        $year = Carbon::now()->format('Y');
        $count = Quotation::where('business_id', $businessId)
            ->whereYear('created_at', $year)
            ->count();

        $nextSeq = str_pad((string)($count + 1), 5, '0', STR_PAD_LEFT);
        return "{$prefix}-{$year}-{$nextSeq}";
    }

    /**
     * Generate unique Sales Order Number.
     * Format: SO-YYYY-XXXXX
     */
    public static function generateSalesOrderNumber(string $businessId, string $prefix = 'SO'): string
    {
        $year = Carbon::now()->format('Y');
        $count = SalesOrder::where('business_id', $businessId)
            ->whereYear('created_at', $year)
            ->count();

        $nextSeq = str_pad((string)($count + 1), 5, '0', STR_PAD_LEFT);
        return "{$prefix}-{$year}-{$nextSeq}";
    }
}
