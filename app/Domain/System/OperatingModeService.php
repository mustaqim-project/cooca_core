<?php

declare(strict_types=1);

namespace App\Domain\System;

use App\Models\Business;
use App\Models\User;

final class OperatingModeService
{
    /**
     * Ambil role eksplisit user di dalam bisnis tertentu dari tabel membership (business_users).
     * Tidak bergantung pada global scope/Context agar aman dipakai dari mana saja.
     */
    private function userRole(Business $business, ?User $user): ?string
    {
        if ($user === null) {
            return null;
        }

        return $user->memberships()
            ->where('business_id', $business->id)
            ->first()?->role;
    }

    /**
     * Determine if the business is running in Solo-Owner Mode (1 user) or Team Mode (>1 users).
     */
    public function isSoloMode(Business $business): bool
    {
        // Hitung pengguna terasosiasi dengan bisnis ini
        $userCount = $business->users()->count();

        return $userCount <= 1;
    }

    /**
     * Determine if supervisor PIN / approval modal can be bypassed.
     * In Solo Mode, the owner IS the cashier, so approval modals are bypassed for seamless UX.
     */
    public function canBypassSupervisor(Business $business, ?User $user = null): bool
    {
        if ($this->isSoloMode($business)) {
            return true;
        }

        if ($this->userRole($business, $user) === 'owner') {
            return true;
        }

        return false;
    }

    /**
     * In Team Mode, cashiers/sales staff are barred from viewing sensitive recipe BOM HPP base cost and margins.
     */
    public function shouldHideCostFromCashier(Business $business, ?User $user = null): bool
    {
        if ($this->isSoloMode($business)) {
            return false; // Solo owner always has full visibility
        }

        // If user is owner or admin, they can see costs; regular staff/cashier cannot
        return !in_array($this->userRole($business, $user), ['owner', 'admin'], true);
    }

    /**
     * Get summary metadata of current operating mode.
     */
    public function getOperatingModeProfile(Business $business): array
    {
        $isSolo = $this->isSoloMode($business);

        return [
            'mode' => $isSolo ? 'solo' : 'team',
            'label' => $isSolo ? 'Solo-Owner Mode (Operasional Mandiri)' : 'Team / Delegated Mode',
            'description' => $isSolo
                ? 'Operasional 1 orang: Tanpa birokrasi, bypass PIN supervisor, dan pintasan 1-klik pembelian langsung ke stok.'
                : 'Operasional terdelegasi: Pemisahan tugas, proteksi rahasia margin HPP, dan rekonsiliasi kas laci kasir.',
            'active_users_count' => $business->users()->count(),
            'bypass_supervisor_pin' => $isSolo,
            'instant_stock_in_enabled' => true,
        ];
    }
}
