<?php

declare(strict_types=1);

namespace App\Domain\Tax;

final class SalesTaxService
{
    public const TYPE_NONE = 'none';
    public const TYPE_PB1 = 'pb1'; // Pajak Restoran / PBJT 10%
    public const TYPE_PPN_11 = 'ppn_11'; // PPN 11%
    public const TYPE_PPN_12 = 'ppn_12'; // PPN 12%

    public const PB1_RATE = 0.10; // 10%
    public const PPN_11_RATE = 0.11; // 11%
    public const PPN_12_RATE = 0.12; // 12%

    /**
     * Hitung pajak transaksi penjualan (POS / Invoice).
     *
     * @param float $subtotal Total nilai belanja sebelum pajak dan diskon
     * @param float $discount Nominal potongan harga
     * @param float $serviceChargeRate Persentase biaya layanan (contoh 0.05 untuk resto 5%)
     * @param string $taxType Tipe pajak ('none', 'pb1', 'ppn_11', 'ppn_12')
     * @param bool $isInclusive Apakah harga di sistem sudah termasuk pajak (tax inclusive)
     * @return array<string, mixed>
     */
    public function calculate(
        float $subtotal,
        float $discount = 0.0,
        float $serviceChargeRate = 0.0,
        string $taxType = self::TYPE_NONE,
        bool $isInclusive = false
    ): array {
        $netSubtotal = max(0.0, $subtotal - $discount);
        $taxRate = $this->getTaxRate($taxType);

        if ($taxType === self::TYPE_NONE || $taxRate <= 0.0) {
            $serviceCharge = round($netSubtotal * $serviceChargeRate, 2);
            $grandTotal = $netSubtotal + $serviceCharge;

            return [
                'subtotal' => $subtotal,
                'discount' => $discount,
                'net_subtotal' => $netSubtotal,
                'service_charge_rate' => $serviceChargeRate,
                'service_charge' => $serviceCharge,
                'dpp' => $netSubtotal + $serviceCharge,
                'tax_type' => self::TYPE_NONE,
                'tax_rate' => 0.0,
                'tax_amount' => 0.0,
                'is_inclusive' => false,
                'grand_total' => $grandTotal,
            ];
        }

        if ($isInclusive) {
            // Jika inklusif pajak:
            // Grand Total = netSubtotal + serviceCharge
            // DPP = Grand Total / (1 + taxRate)
            // Tax = Grand Total - DPP
            $serviceCharge = round($netSubtotal * $serviceChargeRate, 2);
            $totalGross = $netSubtotal + $serviceCharge;
            $dpp = round($totalGross / (1.0 + $taxRate), 2);
            $taxAmount = round($totalGross - $dpp, 2);

            return [
                'subtotal' => $subtotal,
                'discount' => $discount,
                'net_subtotal' => $netSubtotal,
                'service_charge_rate' => $serviceChargeRate,
                'service_charge' => $serviceCharge,
                'dpp' => $dpp,
                'tax_type' => $taxType,
                'tax_rate' => $taxRate,
                'tax_rate_percent' => ($taxRate * 100) . '%',
                'tax_amount' => $taxAmount,
                'is_inclusive' => true,
                'grand_total' => $totalGross,
            ];
        }

        // Eksklusif pajak:
        // Service charge dihitung dari net subtotal
        // Dasar Pengenaan Pajak (DPP) = Net Subtotal + Service Charge
        $serviceCharge = round($netSubtotal * $serviceChargeRate, 2);
        $dpp = $netSubtotal + $serviceCharge;
        $taxAmount = round($dpp * $taxRate, 2);
        $grandTotal = $dpp + $taxAmount;

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'net_subtotal' => $netSubtotal,
            'service_charge_rate' => $serviceChargeRate,
            'service_charge' => $serviceCharge,
            'dpp' => $dpp,
            'tax_type' => $taxType,
            'tax_rate' => $taxRate,
            'tax_rate_percent' => ($taxRate * 100) . '%',
            'tax_amount' => $taxAmount,
            'is_inclusive' => false,
            'grand_total' => $grandTotal,
        ];
    }

    public function getTaxRate(string $type): float
    {
        return match ($type) {
            self::TYPE_PB1 => self::PB1_RATE,
            self::TYPE_PPN_11 => self::PPN_11_RATE,
            self::TYPE_PPN_12 => self::PPN_12_RATE,
            default => 0.0,
        };
    }
}
