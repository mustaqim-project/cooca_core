# Module: Analytics Suite & Document Approvals (MAR Engine)

## Overview & Scope
Modul **Analitik Bisnis & Tren Pertumbuhan** dan **Pusat Otorisasi Dokumen (Maker-Approver-Releaser / MAR)** dirancang untuk memberikan transparansi operasional, kepatuhan tata kelola, dan visibilitas finansial real-time bagi Business Owner dan jajaran manajemen UMKM Indonesia.

---

## 1. Analytics Suite (`/analytics`)

### Endpoint & Controller
- **Route:** `GET /analytics` (`analytics.index`)
- **Controller:** `App\Http\Controllers\Web\AnalyticsWebController`
- **Middleware:** `auth:web`, `business.active`, `require.permission:reports.view`

### Fitur Utama & Metrik
1. **Agregasi Finansial Multi-Channel:**
   - Omzet Penjualan (POS + Faktur B2B + Pesanan Toko Online).
   - Laba Kotor (Gross Profit) & Gross Margin (%).
   - Estimasi Laba Bersih setelah dikurangi Beban Operasional (`expenses`).
   - Rata-rata Nilai Keranjang (AOV - Average Order Value).
2. **Perbandingan Pertumbuhan (% Growth):**
   - Menghitung persentase pertumbuhan omzet dan laba terhadap periode sebelumnya (Hari Ini vs Kemarin, Bulan Ini vs Bulan Lalu, dll.).
3. **Grafik & Visualisasi Terpadu (Chart.js):**
   - Tren Kurva Penjualan & Laba Kotor Harian / Jam.
   - Jam Sibuk Kasir (Hourly Peak Hours Heatmap 07:00–23:00).
   - Distribusi Metode Pembayaran (QRIS, Tunai, Bank, Kasbon, EDC).
4. **Top 10 Produk & Valuasi Margin:**
   - Kuantitas terjual, omzet kontribusi, dan persentase margin per menu/produk.
5. **Performa Multi-Cabang:**
   - Komparasi omzet, laba kotor, dan total order per cabang/gudang.

---

## 2. Document Approvals (MAR Workflow)

### Endpoint & Controller
- **Inbox:** `GET /approvals` (`approvals.inbox`)
- **History:** `GET /approvals/history` (`approvals.history`)
- **Approve Action:** `POST /approvals/{approvalRequest}/approve` (`approvals.approve`)
- **Reject Action:** `POST /approvals/{approvalRequest}/reject` (`approvals.reject`)
- **Rules Index:** `GET /settings/approval-rules` (`approval-rules.index`)
- **Rules Store:** `POST /settings/approval-rules` (`approval-rules.store`)
- **Rules Update:** `PUT /settings/approval-rules/{approvalRule}` (`approval-rules.update`)
- **Rules Delete:** `DELETE /settings/approval-rules/{approvalRule}` (`approval-rules.destroy`)
- **Controller:** `App\Http\Controllers\Web\Approval\ApprovalWebController`
- **Service:** `App\Domain\Approval\ApprovalWorkflowService`

### Logika & Aturan Bisnis
1. **Pemisahan Peran (Maker - Approver - Releaser):**
   - Dokumen di atas plafon nominal (`min_amount` s/d `max_amount`) otomatis mewajibkan persetujuan berjenjang (Level 1 s/d Level 3).
   - Pembuat draf (Maker) dilarang menyetujui dokumen miliknya sendiri (kecuali berstatus Owner).
   - Penolakan dokumen wajib menyertakan alasan tertulis untuk rekam jejak audit pimpinan.
2. **Keterhubungan Otomasi:**
   - Dokumen yang disetujui penuh (`approved`) dapat melanjutkan proses konfirmasi, penerbitan tagihan supplier, pemotongan stok bahan baku (BOM), dan pencatatan jurnal akuntansi otomatis.

---

## 3. Desain UI & Standar Apple HIG
- **Bento Grid Layout:** Kartu squircle berlayer frosted glass (`backdrop-blur-2xl`), hairline border lembut (`border-black/[0.06] dark:border-white/[0.08]`).
- **Zero Emoji:** 100% menggunakan ikon Lucide semantik.
- **Ergonomi Ramah Boomer:** Tipografi lapang `tabular-nums` untuk nominal Rupiah, target sentuh 44–52px, input mobile minimal 16px anti-auto-zoom iOS.
- **Modal-First:** Formulir persetujuan, penolakan, tambah aturan, dan edit aturan berjalan in-place tanpa navigasi pindah halaman.
