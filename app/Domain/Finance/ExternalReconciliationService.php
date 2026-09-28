<?php

declare(strict_types=1);

namespace App\Domain\Finance;

use App\Models\Business;
use App\Models\CashAccount;
use App\Models\ExternalAccountReconciliation;
use App\Models\PaymentSettlement;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service untuk Lembar Rekonsiliasi Akun Eksternal
 * dan Dashboard Likuiditas "Where The Money Lives".
 *
 * Akun eksternal = channel di luar otomasi COOCA:
 * kasir tunai, EDC, e-wallet, marketplace.
 * Merchant wajib update saldo awal & akhir setiap bulan.
 */
final class ExternalReconciliationService
{
    // ────────────────────────────────────────────────────────
    // Lembar Rekonsiliasi CRUD
    // ────────────────────────────────────────────────────────

    /**
     * Create or update a reconciliation entry for a given period + channel.
     */
    public function upsertReconciliation(
        Business $business,
        string $channelType,
        string $channelLabel,
        string $period,
        float $openingBalance,
        float $totalInflow,
        float $totalDisbursement,
        float $closingBalance,
        ?string $locationId = null,
        ?string $notes = null,
        ?string $userId = null,
    ): ExternalAccountReconciliation {
        return DB::transaction(function () use (
            $business, $channelType, $channelLabel, $period,
            $openingBalance, $totalInflow, $totalDisbursement,
            $closingBalance, $locationId, $notes, $userId
        ) {
            $recon = ExternalAccountReconciliation::updateOrCreate(
                [
                    'business_id'  => $business->id,
                    'location_id'  => $locationId,
                    'channel_type' => $channelType,
                    'period'       => $period,
                ],
                [
                    'channel_label'       => $channelLabel,
                    'opening_balance'     => $openingBalance,
                    'total_inflow'        => $totalInflow,
                    'total_disbursement'  => $totalDisbursement,
                    'closing_balance'     => $closingBalance,
                    'notes'               => $notes,
                    'submitted_by'        => $userId,
                    'submitted_at'        => now(),
                    'status'              => ExternalAccountReconciliation::STATUS_SUBMITTED,
                ]
            );

            $recon->recalculate();

            // Auto-flag discrepancy
            if ($recon->hasDiscrepancy()) {
                $recon->status = ExternalAccountReconciliation::STATUS_DISCREPANCY;
            }

            $recon->save();

            return $recon;
        });
    }

    /**
     * Get all reconciliation entries for a business & period.
     */
    public function getReconciliationsForPeriod(Business $business, string $period, ?string $locationId = null): Collection
    {
        $query = ExternalAccountReconciliation::where('business_id', $business->id)
            ->forPeriod($period)
            ->orderBy('channel_type');

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        return $query->get();
    }

    /**
     * Get discrepancy summary for a business across all periods.
     */
    public function getDiscrepancySummary(Business $business): Collection
    {
        return ExternalAccountReconciliation::where('business_id', $business->id)
            ->withDiscrepancy()
            ->orderByDesc('period')
            ->get()
            ->groupBy('period');
    }

    // ────────────────────────────────────────────────────────
    // Dashboard Likuiditas: "Where The Money Lives"
    // ────────────────────────────────────────────────────────

    /**
     * Build the comprehensive liquidity snapshot for the merchant dashboard.
     *
     * Returns a structure showing where all the money is:
     * 1. Cash Accounts (Kas, Bank, E-Wallet) — from COOCA ledger
     * 2. Cooca Pay Escrow (unsettled gateway funds)
     * 3. External Channel Balances (EDC, e-wallet, marketplace)
     * 4. Total Liquidity
     */
    public function buildLiquidityDashboard(Business $business, ?string $period = null): array
    {
        $period ??= Carbon::now()->format('Y-m');

        // ── 1. COOCA Internal Cash Accounts ────────────────
        $cashAccounts = CashAccount::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('type')
            ->get()
            ->map(fn ($a) => [
                'id'      => $a->id,
                'name'    => $a->name,
                'type'    => $a->type,
                'balance' => (float) $a->current_balance,
                'source'  => 'cooca_ledger',
            ]);

        $internalTotal = $cashAccounts->sum('balance');

        // ── 2. Cooca Pay Escrow (Unsettled Gateway Funds) ──
        $escrowGross = PaymentSettlement::where('business_id', $business->id)
            ->where('status', PaymentSettlement::STATUS_PENDING)
            ->sum('gross_amount');

        $escrowFee = PaymentSettlement::where('business_id', $business->id)
            ->where('status', PaymentSettlement::STATUS_PENDING)
            ->sum('fee_amount');

        $escrowNet = $escrowGross - $escrowFee;

        // ── 3. External Channel Closing Balances ───────────
        $externalAccounts = ExternalAccountReconciliation::where('business_id', $business->id)
            ->forPeriod($period)
            ->get()
            ->map(fn ($r) => [
                'id'               => $r->id,
                'channel_type'     => $r->channel_type,
                'channel_label'    => $r->channel_label,
                'location'         => $r->location?->name,
                'closing_balance'  => (float) $r->closing_balance,
                'variance'         => (float) $r->variance,
                'has_discrepancy'  => $r->hasDiscrepancy(),
                'status'           => $r->status,
                'source'           => 'merchant_reported',
            ]);

        $externalTotal = $externalAccounts->sum('closing_balance');

        // ── 4. Grand Total Liquidity ───────────────────────
        $totalLiquidity = $internalTotal + $escrowNet + $externalTotal;

        // ── 5. Discrepancy Alerts ──────────────────────────
        $discrepancyCount = $externalAccounts->filter(fn ($a) => $a['has_discrepancy'])->count();
        $totalVariance = $externalAccounts->sum('variance');

        return [
            'period'          => $period,
            'currency'        => $business->currency_symbol ?? 'Rp',
            'internal'        => [
                'accounts' => $cashAccounts->values()->toArray(),
                'total'    => $internalTotal,
            ],
            'escrow'          => [
                'gross'    => (float) $escrowGross,
                'fee'      => (float) $escrowFee,
                'net'      => (float) $escrowNet,
                'count'    => PaymentSettlement::where('business_id', $business->id)
                    ->where('status', PaymentSettlement::STATUS_PENDING)->count(),
            ],
            'external'        => [
                'accounts' => $externalAccounts->values()->toArray(),
                'total'    => $externalTotal,
            ],
            'liquidity'       => [
                'total'             => $totalLiquidity,
                'discrepancy_count' => $discrepancyCount,
                'total_variance'    => $totalVariance,
            ],
        ];
    }
}
