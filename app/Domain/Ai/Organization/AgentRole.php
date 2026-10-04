<?php

declare(strict_types=1);

namespace App\Domain\Ai\Organization;

enum AgentRole: string
{
    case BUSINESS = 'business';
    case SALES = 'sales';
    case CUSTOMER = 'customer';
    case INVENTORY = 'inventory';
    case PURCHASING = 'purchasing';
    case MARKETPLACE = 'marketplace';
    case FINANCE = 'finance';
    case REPORTING = 'reporting';
    case MARKETING = 'marketing';
    case CONTENT = 'content';
    case SOCIAL_MEDIA = 'social_media';
    case HR = 'hr';

    public function label(): string
    {
        return match ($this) {
            self::BUSINESS => 'Business Agent',
            self::SALES => 'Sales Agent',
            self::CUSTOMER => 'Customer Agent',
            self::INVENTORY => 'Inventory Agent',
            self::PURCHASING => 'Purchasing Agent',
            self::MARKETPLACE => 'Marketplace Agent',
            self::FINANCE => 'Finance Agent',
            self::REPORTING => 'Reporting Agent',
            self::MARKETING => 'Marketing Agent',
            self::CONTENT => 'Content Agent',
            self::SOCIAL_MEDIA => 'Social Media Agent',
            self::HR => 'HR Agent',
        };
    }

    public function department(): Department
    {
        return match ($this) {
            self::BUSINESS => Department::EXECUTIVE,
            self::SALES, self::CUSTOMER => Department::SALES,
            self::INVENTORY, self::PURCHASING, self::MARKETPLACE => Department::OPERATIONS,
            self::FINANCE, self::REPORTING => Department::FINANCE,
            self::MARKETING, self::CONTENT, self::SOCIAL_MEDIA => Department::MARKETING,
            self::HR => Department::PEOPLE,
        };
    }

    public function executiveLead(): ExecutiveRole
    {
        return match ($this) {
            self::BUSINESS => ExecutiveRole::CEO,
            self::SALES, self::CUSTOMER => ExecutiveRole::SALES_DIRECTOR,
            self::INVENTORY, self::PURCHASING, self::MARKETPLACE => ExecutiveRole::COO,
            self::FINANCE, self::REPORTING => ExecutiveRole::CFO,
            self::MARKETING, self::CONTENT, self::SOCIAL_MEDIA => ExecutiveRole::CMO,
            self::HR => ExecutiveRole::HR_LEAD,
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::BUSINESS => 'Analisis komprehensif kesehatan bisnis, deteksi anomali lintas modul, dan diagnosa prioritas strategis.',
            self::SALES => 'Analisis tren penjualan, pendapatan produk, produk laris vs anjlok, dan deviasi omzet cabang.',
            self::CUSTOMER => 'Segmentasi RFM, identifikasi pelanggan pasif (dormant), loyalitas, dan pemantauan jatuh tempo tagihan.',
            self::INVENTORY => 'Pemantauan stok kritis, prediksi kehabisan stok, dead stock, dan saran titik pemesanan ulang (ROP).',
            self::PURCHASING => 'Analisis riwayat pemasok, rekomendasi timing pengadaan, dan persiapan draf Purchase Order (PO).',
            self::MARKETPLACE => 'Sinkronisasi harga dan stok lintas marketplace (Shopee, Tokopedia, TikTok Shop) serta analitik multi-kanal.',
            self::FINANCE => 'Analisis arus kas (cashflow), laba kotor, margin bersih, dan deteksi anomali pengeluaran kas.',
            self::REPORTING => 'Penyusunan rekapitulasi harian, mingguan, bulanan, dan ringkasan eksekutif untuk manajemen.',
            self::MARKETING => 'Perumusan strategi promosi, ide diskon berkala, dan penargetan segmen pelanggan berharga tinggi.',
            self::CONTENT => 'Penyusunan naskah copywriting, ide konten media sosial, caption promosi, dan varian konten penawaran.',
            self::SOCIAL_MEDIA => 'Manajemen jadwal tayang postingan medsos, persiapan materi visual, dan monitoring status publikasi.',
            self::HR => 'Pemantauan absensi karyawan, produktivitas kasir, dan ringkasan data jam kerja tenaga kerja.',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::BUSINESS => 'line-chart',
            self::SALES => 'trending-up',
            self::CUSTOMER => 'user-check',
            self::INVENTORY => 'boxes',
            self::PURCHASING => 'shopping-cart',
            self::MARKETPLACE => 'store',
            self::FINANCE => 'coins',
            self::REPORTING => 'file-text',
            self::MARKETING => 'target',
            self::CONTENT => 'pen-tool',
            self::SOCIAL_MEDIA => 'share-2',
            self::HR => 'users',
        };
    }
}
