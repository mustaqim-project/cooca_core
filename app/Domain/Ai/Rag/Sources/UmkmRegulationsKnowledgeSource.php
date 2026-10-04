<?php

declare(strict_types=1);

namespace App\Domain\Ai\Rag\Sources;

use App\Domain\Ai\Rag\Contracts\KnowledgeSourceInterface;
use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Str;

final class UmkmRegulationsKnowledgeSource implements KnowledgeSourceInterface
{
    public function getSourceId(): string
    {
        return 'umkm_regulations_and_formulas';
    }

    public function getLabel(): string
    {
        return 'Regulasi Bisnis UMKM Indonesia, SOP Anti-Fraud & Formula Finansial';
    }

    /**
     * @return array<int, array{content: string, citation: string, score: float, metadata: array<string, mixed>}>
     */
    public function retrieve(Business $business, ?User $user, string $query, array $options = []): array
    {
        $lowerQuery = mb_strtolower($query);
        $chunks = [];

        // 1. Pajak UMKM PP 55/2022 (PPh Final 0.5%)
        if (Str::contains($lowerQuery, ['pajak', 'pph', 'pp 55', 'pajak umkm', 'final', 'djp', 'omset', 'omzet'])) {
            $chunks[] = [
                'content' => "Regulasi Pajak UMKM (PP 55/2022): Tarif PPh Final UMKM adalah 0,5% dari omzet bruto bulanan. Khusus Wajib Pajak Orang Pribadi (WP OP), omzet sampai dengan Rp 500.000.000 dalam 1 tahun pajak TIDAK dikenai PPh Final (bebas pajak). Jika melewati Rp 500 juta, pajak 0,5% hanya dihitung atas kelebihan omzet tersebut. Masa berlaku fasilitas: WP OP maksimal 7 tahun, CV/Firma 4 tahun, PT 3 tahun.",
                'citation' => "[Regulasi: PP No. 55 Tahun 2022 & UU HPP]",
                'score' => 0.94,
                'metadata' => ['category' => 'tax', 'regulation' => 'PP 55/2022'],
            ];
        }

        // 2. Pajak PPN 11% & Faktur Pajak
        if (Str::contains($lowerQuery, ['ppn', 'faktur pajak', 'pkp', 'pajak pertambahan nilai', 'ppn 11'])) {
            $chunks[] = [
                'content' => "Regulasi PPN UU HPP: Tarif Pajak Pertambahan Nilai (PPN) yang berlaku umum adalah 11%. Kewajiban PKP (Pengusaha Kena Pajak) hanya berlaku bagi pengusaha dengan peredaran bruto/omzet melebihi Rp 4.800.000.000 setahun. UMKM non-PKP dilarang memungut PPN atau menerbitkan Faktur Pajak standar.",
                'citation' => "[Regulasi: UU No. 7 Tahun 2021 tentang Harmonisasi Peraturan Perpajakan (HPP)]",
                'score' => 0.92,
                'metadata' => ['category' => 'tax', 'regulation' => 'UU HPP PPN 11%'],
            ];
        }

        // 3. PPh 21 TER (Tarif Efektif Rata-Rata) Gaji Karyawan
        if (Str::contains($lowerQuery, ['pph 21', 'pph21', 'gaji', 'karyawan', 'payroll', 'ter', 'pp 58'])) {
            $chunks[] = [
                'content' => "Pemotongan PPh 21 TER (PP 58/2023 & PMK 168/2023): Menghitung pemotongan PPh Pasal 21 bulanan masa Januari-November menggunakan Tarif Efektif Bulanan (Kategori A, B, atau C berdasarkan status PTKP PTKP TK/0 s.d K/3). Penghasilan bruto s.d Rp 5.400.000/bulan tarif TER 0%. Masa pajak Desember dihitung menggunakan tarif progresif Pasal 17 ayat (1) huruf a UU PPh diselisihkan dengan total TER yang telah dipotong.",
                'citation' => "[Regulasi: PP 58/2023 & PMK 168/2023 Skema PPh 21 TER]",
                'score' => 0.93,
                'metadata' => ['category' => 'payroll_tax', 'regulation' => 'PPh 21 TER'],
            ];
        }

        // 4. SOP Anti-Fraud Kasir, Void, & Pencegahan Kebocoran Kas
        if (Str::contains($lowerQuery, ['kasir', 'fraud', 'curang', 'anomali', 'void', 'refund', 'selisih', 'kas', 'sop', 'laci'])) {
            $chunks[] = [
                'content' => "SOP Pengawasan Kasir & Anti-Fraud COOCA: (1) Setiap pembatalan nota (Void) atau Retur/Refund wajib menyertakan otorisasi PIN/Approval dari Supervisor atau Owner. (2) Tutup kasir wajib melakukan 'Blind Cash Count' (kasir input fisik tanpa melihat saldo sistem). (3) Toleransi selisih kas fisik vs sistem maksimal Rp 10.000; selisih di atas Rp 50.000 wajib investigasi form audit. (4) Laci kasir dilarang dibuka di luar transaksi resmi (No-Sale trigger wajib diaudit).",
                'citation' => "[SOP Keamanan Kasir & Internal Audit COOCA]",
                'score' => 0.96,
                'metadata' => ['category' => 'internal_control', 'sop' => 'pos_cashier_anti_fraud'],
            ];
        }

        // 5. Rumus Safety Stock, ROP, dan Manajemen Persediaan
        if (Str::contains($lowerQuery, ['rop', 'safety stock', 'reorder', 'stok', 'persediaan', 'gudang', 'restock', 'habis', 'menipis', 'eoq'])) {
            $chunks[] = [
                'content' => "Formula Restock & Persediaan Otomatis: (1) Safety Stock (SS) = (Penjualan Harian Maksimal * Lead Time Hari Maksimal) - (Rata-rata Penjualan Harian * Rata-rata Lead Time). (2) Reorder Point (ROP) = (Rata-rata Penjualan Harian * Lead Time) + Safety Stock. Saat stok mencapai atau di bawah ROP, sistem AI Pembelian wajib mengajukan Draft PO ke Supplier. (3) Dead Stock didefinisikan jika barang tidak bergerak > 60 hari.",
                'citation' => "[Standar Manajemen Rantai Pasok & Inventory COOCA]",
                'score' => 0.95,
                'metadata' => ['category' => 'inventory_formula', 'sop' => 'supply_chain_rop'],
            ];
        }

        // 6. Formula Rasio Keuangan & Profitabilitas
        if (Str::contains($lowerQuery, ['laba', 'profit', 'margin', 'rasio', 'rugi', 'keuangan', 'hukum', 'kpi', 'gpm', 'npm', 'runway'])) {
            $chunks[] = [
                'content' => "Formula Rasio Finansial Bisnis: (1) Gross Profit Margin (GPM) = ((Total Penjualan - Total HPP) / Total Penjualan) * 100%. (2) Net Profit Margin (NPM) = (Laba Bersih Setelah Biaya Operasional / Total Penjualan) * 100%. (3) Cash Runway (Bulan) = Total Saldo Kas & Bank / Rata-rata Biaya Operasional Tetap Bulanan (Burn Rate). Target minimal Cash Runway UMKM yang sehat adalah >= 3 bulan.",
                'citation' => "[Pedoman Manajemen Keuangan & KPI Bisnis COOCA]",
                'score' => 0.91,
                'metadata' => ['category' => 'financial_formulas', 'sop' => 'financial_health_kpis'],
            ];
        }

        return $chunks;
    }
}
