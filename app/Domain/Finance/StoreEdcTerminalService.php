<?php

declare(strict_types=1);

namespace App\Domain\Finance;

use App\Models\Business;
use App\Models\StoreEdcTerminal;
use DomainException;
use Illuminate\Support\Facades\DB;

final class StoreEdcTerminalService
{
    /**
     * Mendaftarkan mesin EDC baru untuk toko/cabang.
     *
     * @param array{
     *     bank_name: string,
     *     terminal_name: string,
     *     terminal_id_tid: string,
     *     merchant_id_mid?: string|null,
     *     location_id?: string|null,
     *     mdr_debit_percent?: float|int|null,
     *     mdr_credit_percent?: float|int|null,
     *     settlement_account_info?: string|null,
     *     is_active?: bool
     * } $data
     */
    public function registerTerminal(Business $business, array $data): StoreEdcTerminal
    {
        $tid = trim((string) ($data['terminal_id_tid'] ?? ''));
        if (empty($tid)) {
            throw new DomainException('Nomor Terminal ID (TID) mesin EDC wajib diisi.');
        }

        $exists = StoreEdcTerminal::where('business_id', $business->id)
            ->where('terminal_id_tid', $tid)
            ->exists();

        if ($exists) {
            throw new DomainException("Mesin EDC dengan Terminal ID {$tid} sudah terdaftar di bisnis ini.");
        }

        return StoreEdcTerminal::create([
            'business_id' => $business->id,
            'location_id' => $data['location_id'] ?? null,
            'bank_name' => trim((string) $data['bank_name']),
            'terminal_name' => trim((string) $data['terminal_name']),
            'terminal_id_tid' => $tid,
            'merchant_id_mid' => ! empty($data['merchant_id_mid']) ? trim((string) $data['merchant_id_mid']) : null,
            'mdr_debit_percent' => isset($data['mdr_debit_percent']) ? (float) $data['mdr_debit_percent'] : 0.15,
            'mdr_credit_percent' => isset($data['mdr_credit_percent']) ? (float) $data['mdr_credit_percent'] : 1.50,
            'settlement_account_info' => ! empty($data['settlement_account_info']) ? trim((string) $data['settlement_account_info']) : null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
    }

    /**
     * Update profil mesin EDC.
     */
    public function updateTerminal(StoreEdcTerminal $terminal, array $data): StoreEdcTerminal
    {
        $tid = trim((string) ($data['terminal_id_tid'] ?? $terminal->terminal_id_tid));

        $exists = StoreEdcTerminal::where('business_id', $terminal->business_id)
            ->where('terminal_id_tid', $tid)
            ->where('id', '!=', $terminal->id)
            ->exists();

        if ($exists) {
            throw new DomainException("Mesin EDC dengan Terminal ID {$tid} sudah digunakan oleh mesin lain.");
        }

        $terminal->update([
            'location_id' => $data['location_id'] ?? $terminal->location_id,
            'bank_name' => trim((string) ($data['bank_name'] ?? $terminal->bank_name)),
            'terminal_name' => trim((string) ($data['terminal_name'] ?? $terminal->terminal_name)),
            'terminal_id_tid' => $tid,
            'merchant_id_mid' => isset($data['merchant_id_mid']) ? trim((string) $data['merchant_id_mid']) : $terminal->merchant_id_mid,
            'mdr_debit_percent' => isset($data['mdr_debit_percent']) ? (float) $data['mdr_debit_percent'] : $terminal->mdr_debit_percent,
            'mdr_credit_percent' => isset($data['mdr_credit_percent']) ? (float) $data['mdr_credit_percent'] : $terminal->mdr_credit_percent,
            'settlement_account_info' => isset($data['settlement_account_info']) ? trim((string) $data['settlement_account_info']) : $terminal->settlement_account_info,
            'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : $terminal->is_active,
        ]);

        return $terminal->fresh();
    }

    /**
     * Toggle status aktif mesin EDC.
     */
    public function toggleActive(StoreEdcTerminal $terminal): StoreEdcTerminal
    {
        $terminal->update(['is_active' => ! $terminal->is_active]);

        return $terminal->fresh();
    }

    /**
     * Ambil semua mesin EDC untuk bisnis, opsional filter per lokasi.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, StoreEdcTerminal>
     */
    public function getTerminalsForBusiness(Business $business, ?string $locationId = null)
    {
        $query = StoreEdcTerminal::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('bank_name')
            ->orderBy('terminal_name');

        if ($locationId !== null) {
            $query->where(function ($q) use ($locationId) {
                $q->where('location_id', $locationId)
                    ->orWhereNull('location_id');
            });
        }

        return $query->get();
    }

    /**
     * Hapus mesin EDC.
     */
    public function deleteTerminal(StoreEdcTerminal $terminal): void
    {
        $terminal->delete();
    }
}
