# Workflow Sistem: Operasional Kasir, Shift, Dapur (KDS) & Laporan POS

## 1. Siklus Kerja Kasir & Sesi Shift
- **Pembukaan Shift:** Kasir menginput uang modal fisik awal laci (didukung *Denominations Calculator* lembar/koin).
- **Operasional Transaksi:** Mendukung multi-metode bayar (Tunai, QRIS Statis/Dinamis, EDC Kartu Debit/Kredit, Transfer Bank, Piutang Pelanggan). Harga dan modifier divalidasi mutlak di server (*Server-Authoritative*).
- **Penutupan Shift (Blind Cash Count):** Kasir menginput uang fisik akhir tanpa melihat ekspektasi sistem. Selisih kas otomatis dihitung, dilaporkan ke Owner, dan terjurnal sebagai selisih kas (*Cash Over/Short*).

## 2. Otorisasi Supervisor & Anti-Fraud
- **Aksi Sensitif:** Void transaksi, refund penjualan, dan pembukaan paksa laci kas fisik (*Manual Drawer Pop*) wajib memasukkan PIN Supervisor ter-hash Bcrypt.
- **Proteksi Brute-Force:** 3 kali kegagalan input PIN mengunci otorisasi selama 10 menit dan mengirimkan audit log ke Owner.

## 3. Ekspor & Pembukuan
- **Auto-Journal:** Setiap transaksi terbayar otomatis membentuk jurnal akuntansi piutang/kas terhadap pendapatan dan HPP terhadap persediaan barang.
- **Multi-Sheet XLSX:** Laporan POS diekspor dalam 2 sheet (Executive KPI Bento Card & Transaction Ledger ber-filter dan ber-formula).
