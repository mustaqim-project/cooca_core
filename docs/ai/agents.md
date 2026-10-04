# Direktori 12 Agen Spesialis COOCA AI Digital Company

> **Layer:** Agent Roles, Specialized Capabilities, & Assigned Tools  
> **Source:** `App\Domain\Ai\Organization\AgentRole` & `App\Domain\Ai\Tools\`

---

## 1. Daftar Lengkap Agen Spesialis

### 1. AI Business Analyst (`business`)
- **Departemen:** Executive
- **Tanggung Jawab:** Menganalisis metrik makro bisnis, mensintesis laporan berkala, dan merumuskan rekomendasi arah strategis bagi pemilik bisnis.
- **Peralatan Kerja:** `GetSalesSummaryTool`, `GetSalesTrendTool`, `GetFinancialHealthTool`.

### 2. AI Sales Specialist (`sales`)
- **Departemen:** Sales
- **Tanggung Jawab:** Memantau penjualan toko fisik (POS) dan B2B, mengidentifikasi produk terlaris (*top-selling*), dan menyusun draft faktur penjualan.
- **Peralatan Kerja:** `GetSalesSummaryTool`, `GetTopProductsTool`, `DraftInvoiceProposalTool`.

### 3. AI Customer Support Specialist (`customer`)
- **Departemen:** Sales
- **Tanggung Jawab:** Menganalisis preferensi dan riwayat pembelian pelanggan, segmentasi pelanggan setia vs dorman, dan penanganan relasi pelanggan.
- **Peralatan Kerja:** `GetCustomerSummaryTool`.

### 4. AI Inventory Specialist (`inventory`)
- **Departemen:** Operations
- **Tanggung Jawab:** Memantau ketersediaan stok fisik gudang, memberikan peringatan saat stok mendekati batas minimum (*low stock warning*), dan mencatat kebutuhan opname.
- **Peralatan Kerja:** `GetStockLevelsTool`.

### 5. AI Purchasing Specialist (`purchasing`)
- **Departemen:** Operations
- **Tanggung Jawab:** Menghubungi supplier terdaftar, menyusun draf Purchase Order (PO) saat stok menipis, dan membandingkan histori harga beli.
- **Peralatan Kerja:** `GetStockLevelsTool`, `DraftPurchaseOrderProposalTool`.

### 6. AI Marketplace Manager (`marketplace`)
- **Departemen:** Operations
- **Tanggung Jawab:** Sinkronisasi katalog dan stok antar platform e-commerce / omnichannel, serta memantau pesanan yang masuk dari channel eksternal.
- **Peralatan Kerja:** `GetStockLevelsTool`, `GetSalesSummaryTool`.

### 7. AI Finance Specialist (`finance`)
- **Departemen:** Finance
- **Tanggung Jawab:** Memantau likuiditas kas & bank, mengawasi piutang jatuh tempo (*Accounts Receivable*), dan memverifikasi biaya pengeluaran operasional.
- **Peralatan Kerja:** `GetFinancialHealthTool`, `DraftInvoiceProposalTool`.

### 8. AI Reporting Analyst (`reporting`)
- **Departemen:** Finance
- **Tanggung Jawab:** Menyajikan laporan performa keuangan komprehensif (Laba Rugi, Neraca, Arus Kas) untuk kebutuhan audit internal dan pelaporan pajak.
- **Peralatan Kerja:** `GetFinancialHealthTool`, `GetSalesTrendTool`.

### 9. AI Marketing Specialist (`marketing`)
- **Departemen:** Marketing
- **Tanggung Jawab:** Merancang promosi penjualan bertarget, diskon musiman, dan kampanye penjangkauan pelanggan via WhatsApp Broadcast.
- **Peralatan Kerja:** `DraftMarketingCampaignProposalTool`, `GetCustomerSummaryTool`.

### 10. AI Content Creator (`content`)
- **Departemen:** Marketing
- **Tanggung Jawab:** Menulis artikel panduan untuk storefront, deskripsi produk menarik berbasis SEO, dan naskah pesan pengumuman.
- **Peralatan Kerja:** `DraftMarketingCampaignProposalTool`, `DraftSocialPostProposalTool`.

### 11. AI Social Media Manager (`social_media`)
- **Departemen:** Marketing
- **Tanggung Jawab:** Menyusun kalender konten postingan media sosial (Instagram, TikTok, Facebook) lengkap dengan caption, tagar relevan, dan draf publikasi.
- **Peralatan Kerja:** `DraftSocialPostProposalTool`.

### 12. AI HR Specialist (`hr`)
- **Departemen:** People
- **Tanggung Jawab:** Memantau ringkasan kehadiran karyawan, kepatuhan jam kerja, keterlambatan, izin, dan kebutuhan lembur staf.
- **Peralatan Kerja:** `GetAttendanceSummaryTool`.

---

## 2. Standar Integritas Output Agen

Setiap agen wajib mematuhi standar komunikasi profesional:
1. **Bahasa Baku & Santun:** Menggunakan Bahasa Indonesia formal, lugas, dan bebas emoji informal berlebihan.
2. **Kesesuaian Angka:** Angka nominal wajib diformat dalam mata uang Rupiah (`Rp 1.500.000`), tanggal dalam format Indonesia (`01 Oktober 2026`).
3. **Pemisahan Fakta & Opini:** Agen wajib menyajikan data fakta terlebih dahulu, diikuti dengan analisis ringkas dan opsi rekomendasi tindak lanjut.
