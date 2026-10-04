# Struktur Organisasi & Hierarki COOCA AI Digital Company

> **Layer:** Organizational Hierarchy & Authority Routing  
> **Enums:** `ExecutiveRole`, `Department`, `AgentRole`, `CompanyHierarchy`

---

## 1. Struktur Eksekutif (C-Level AI Suite)

COOCA AI mendefinisikan lapisan kepemimpinan strategis yang memantau kesehatan bisnis secara makro dan mengoordinasikan instruksi ke divisi operasional:

### 1.1 AI CEO (Chief Executive Officer)
- **Peran:** Pemimpin strategis tertinggi perusahaan digital.
- **Fokus Kerja:**
  - Evaluasi performa bisnis lintas divisi (sales, keuangan, operasional, SDM).
  - Health check harian otomatis pukul 07:00 WIB.
  - Perumusan strategi pertumbuhan, alokasi anggaran, dan mitigasi risiko usaha.
  - Rekomendasi taktis untuk pemilik usaha (Owner).

### 1.2 AI COO (Chief Operating Officer)
- **Peran:** Pengawas operasional dan rantai pasok harian.
- **Fokus Kerja:**
  - Ketersediaan stok dan kelancaran operasional gudang.
  - Pengawasan ketepatan pesanan pembelian (Purchase Order) dan penerimaan barang.
  - Deteksi anomali fraud kasir, diskon berlebih, dan void transaksi mencurigakan.
  - Optimasi efisiensi operasional harian.

### 1.3 AI CFO (Chief Financial Officer)
- **Peran:** Pengendali keuangan, likuiditas, dan margin laba.
- **Fokus Kerja:**
  - Analisis margin kotor, margin bersih, dan break-even point (BEP).
  - Monitoring piutang pelanggan (*Accounts Receivable*) dan umur piutang (*aging*).
  - Manajemen arus kas dan kewajiban hutang supplier (*Accounts Payable*).

### 1.4 AI CMO (Chief Marketing Officer)
- **Peran:** Perancang pertumbuhan pasar dan promosi.
- **Fokus Kerja:**
  - Evaluasi performa kampanye promosi dan efektivitas channel (WhatsApp, Toko Online).
  - Strategi penjangkauan pelanggan dan re-engagement segmen tidak aktif.
  - Perencanaan kalender konten media sosial.

### 1.5 AI HR Lead (People & Culture Director)
- **Peran:** Pengawas produktivitas dan kepatuhan staf.
- **Fokus Kerja:**
  - Evaluasi kehadiran, keterlambatan, dan jam kerja karyawan.
  - Penilaian beban kerja antar shift operasional.
  - Rekomendasi pembinaan atau pengakuan staf berprestasi.

---

## 2. Departemen & Squad (5 Functional Pods)

| Departemen | Deskripsi | Agen Spesialis di Bawah Naungan |
| :--- | :--- | :--- |
| **Executive** | Koordinasi kepemimpinan dan kebijakan strategis | AI Business Analyst |
| **Sales** | Pendapatan, transaksi POS, B2B, dan kepuasan pelanggan | AI Sales Specialist, AI Customer Support Specialist |
| **Operations** | Rantai pasok, logistik, pergudangan, dan purchasing | AI Inventory Specialist, AI Purchasing Specialist, AI Marketplace Manager |
| **Finance** | Akuntansi, kas & bank, penagihan piutang, dan pelaporan | AI Finance Specialist, AI Reporting Analyst |
| **Marketing** | Promosi digital, interaksi pelanggan, dan konten | AI Marketing Specialist, AI Content Creator, AI Social Media Manager |
| **People** | Manajemen kehadiran dan produktivitas staf | AI HR Specialist |

---

## 3. Matriks Delegasi & Routing Otomatis

Setiap kali pengguna mengajukan pertanyaan atau perintah di AI Office UI, `CompanyHierarchy::routeIntent()` menganalisis kata kunci dan konteks tugas:

```
[ User Input ]
      │
      ├─► Kata kunci: 'beli', 'po', 'vendor', 'supplier' ──► Operations Dept ──► Purchasing Specialist
      ├─► Kata kunci: 'stok', 'gudang', 'opname', 'habis' ──► Operations Dept ──► Inventory Specialist
      ├─► Kata kunci: 'faktur', 'tagihan', 'piutang', 'margin' ──► Finance Dept ──► Finance Specialist
      ├─► Kata kunci: 'omzet', 'jual', 'kasir', 'promo' ──────► Sales Dept ──► Sales Specialist
      ├─► Kata kunci: 'iklan', 'konten', 'instagram', 'wa' ───► Marketing Dept ──► Marketing / Social Media Specialist
      ├─► Kata kunci: 'karyawan', 'absen', 'gaji', 'shift' ──► People Dept ──► HR Specialist
      └─► Analisis menyeluruh / umum ────────────────────────► Executive Dept ──► AI Business Analyst (CEO)
```
