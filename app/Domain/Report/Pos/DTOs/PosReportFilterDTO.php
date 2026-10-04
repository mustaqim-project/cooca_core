<?php

declare(strict_types=1);

namespace App\Domain\Report\Pos\DTOs;

use Carbon\Carbon;
use Illuminate\Http\Request;

final class PosReportFilterDTO
{
    public function __construct(
        public readonly string $businessId,
        public readonly Carbon $startDate,
        public readonly Carbon $endDate,
        public readonly ?string $locationId = null,
        public readonly ?string $userId = null,
        public readonly ?string $posShiftId = null,
        public readonly ?string $customerId = null,
        public readonly ?string $categoryId = null,
        public readonly ?string $productId = null,
        public readonly ?string $paymentMethod = null,
        public readonly ?string $salesChannel = null,
        public readonly ?string $orderType = null,
        public readonly ?string $status = null,
        public readonly string $preset = 'custom',
        public readonly bool $includeComparison = false
    ) {}

    public static function fromRequest(Request $request, string $businessId): self
    {
        $preset = (string) $request->get('preset', 'this_month');
        $startDate = null;
        $endDate = null;

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->get('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->get('end_date'))->endOfDay();
            $preset = $request->get('preset', 'custom');
        } else {
            switch ($preset) {
                case 'today':
                    $startDate = Carbon::today()->startOfDay();
                    $endDate = Carbon::today()->endOfDay();
                    break;
                case 'yesterday':
                    $startDate = Carbon::yesterday()->startOfDay();
                    $endDate = Carbon::yesterday()->endOfDay();
                    break;
                case '7days':
                    $startDate = Carbon::today()->subDays(6)->startOfDay();
                    $endDate = Carbon::today()->endOfDay();
                    break;
                case 'this_week':
                    $startDate = Carbon::now()->startOfWeek()->startOfDay();
                    $endDate = Carbon::now()->endOfWeek()->endOfDay();
                    break;
                case 'last_week':
                    $startDate = Carbon::now()->subWeek()->startOfWeek()->startOfDay();
                    $endDate = Carbon::now()->subWeek()->endOfWeek()->endOfDay();
                    break;
                case 'last_month':
                    $startDate = Carbon::now()->subMonth()->startOfMonth()->startOfDay();
                    $endDate = Carbon::now()->subMonth()->endOfMonth()->endOfDay();
                    break;
                case 'this_year':
                    $startDate = Carbon::now()->startOfYear()->startOfDay();
                    $endDate = Carbon::now()->endOfYear()->endOfDay();
                    break;
                case '30days':
                    $startDate = Carbon::today()->subDays(29)->startOfDay();
                    $endDate = Carbon::today()->endOfDay();
                    break;
                case 'this_month':
                default:
                    $startDate = Carbon::today()->startOfMonth()->startOfDay();
                    $endDate = Carbon::today()->endOfMonth()->endOfDay();
                    $preset = 'this_month';
                    break;
            }
        }

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        return new self(
            businessId: $businessId,
            startDate: $startDate,
            endDate: $endDate,
            locationId: $request->filled('location_id') ? (string) $request->get('location_id') : null,
            userId: $request->filled('user_id') ? (string) $request->get('user_id') : null,
            posShiftId: $request->filled('pos_shift_id') ? (string) $request->get('pos_shift_id') : null,
            customerId: $request->filled('customer_id') ? (string) $request->get('customer_id') : null,
            categoryId: $request->filled('category_id') ? (string) $request->get('category_id') : null,
            productId: $request->filled('product_id') ? (string) $request->get('product_id') : null,
            paymentMethod: $request->filled('payment_method') ? (string) $request->get('payment_method') : null,
            salesChannel: $request->filled('sales_channel') ? (string) $request->get('sales_channel') : null,
            orderType: $request->filled('order_type') ? (string) $request->get('order_type') : null,
            status: $request->filled('status') ? (string) $request->get('status') : null,
            preset: (string) $preset,
            includeComparison: $request->boolean('include_comparison', true)
        );
    }

    /**
     * Dapatkan rentang tanggal periode sebelumnya untuk perbandingan (MoM, WoW, YoY).
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public function getPreviousPeriod(): array
    {
        $diffInDays = $this->startDate->diffInDays($this->endDate) + 1;
        $prevEnd = $this->startDate->copy()->subSecond();
        $prevStart = $prevEnd->copy()->subDays((int) $diffInDays - 1)->startOfDay();

        return [$prevStart, $prevEnd];
    }
}
