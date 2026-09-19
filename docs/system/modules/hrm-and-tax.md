# Modul Ketenagakerjaan (HRM) & Kepatuhan Pajak (Tax Compliance Engine)

> **Status:** VERIFIED  
> **Domain Terkait:** `app/Domain/HRM/`, `app/Domain/Tax/`, `app/Http/Controllers/Web/TaxWebController.php`  
> **Tabel Basis Data:** `employee_loans`, `employee_loan_installments`, `spk_commissions`, `branch_product_prices`  
> **Antarmuka Utama:** `/tax` (`resources/views/app/tax/index.blade.php`)

---

## 1. Tujuan & Nilai Bisnis

Modul ini mengintegrasikan seluruh kepatuhan ketenagakerjaan dan regulasi perpajakan Republik Indonesia secara terotomasi ke dalam ekosistem COOCA:
1. **Penggajian & HRM Otomatis:** Perhitungan Gaji Pokok, Tunjangan, Overtime, Komisi SPK/Transaksi, Pemotongan Cicilan Pinjaman/Kasbon, dan BPJS.
2. **Kepatuhan BPJS Ketenagakerjaan & Kesehatan:** Sesuai PP 44/2015, PP 45/2015, dan Perpres 64/2020 dengan plafon upah resmi.
3. **THR Berbasis Tanggal Bergabung:** Sesuai Permenaker No. 6/2016 (penuh bagi masa kerja $\ge 12$ bulan, prorata bagi $1 \le \text{bulan} < 12$, dan Rp 0 jika $< 1$ bulan; rata-rata 12 bulan terakhir bagi pekerja harian lepas/daily worker).
4. **Pajak Penghasilan (PPh 21 TER & Pasal 17):** Kepatuhan penuh PP 58/2023 & PMK 168/2023 dengan klasifikasi TER Kategori A, B, C dan rekonsiliasi masa pajak Desember dengan tarif progresif Pasal 17 UU HPP.
5. **PPh 21 Pekerja Harian Lepas (Daily Worker):** Threshold harian Rp 450.000 dan kumulatif bulanan Rp 2.500.000 / Rp 10.000.000.
6. **PPh Final UMKM 0.5% (PP 55/2022):** Pemantauan omzet kumulatif tahunan dengan batas bebas pajak hingga Rp 500.000.000 untuk Wajib Pajak Orang Pribadi, serta tarif efektif 0.5% dari omzet kena pajak di atas ambang batas.
7. **Pajak Restoran / PB1 & PPN:** Fleksibilitas PB1 10% untuk F&B atau PPN 11%/12% untuk ritel/manufaktur dengan dukungan sistem harga inklusif vs eksklusif dan service charge.

---

## 2. Arsitektur Domain Services

### 2.1 BPJSCalculationService (`app/Domain/HRM/BPJSCalculationService.php`)
* **JHT (Jaminan Hari Tua):** Perusahaan 3.7%, Karyawan 2.0%.
* **JKK (Jaminan Kecelakaan Kerja):** Tingkat risiko sangat rendah (0.24%), rendah (0.54%), sedang (0.89%), tinggi (1.27%), sangat tinggi (1.74%).
* **JKM (Jaminan Kematian):** Perusahaan 0.3%.
* **JP (Jaminan Pensiun):** Perusahaan 2.0%, Karyawan 1.0% (Plafon batas maksimal upah: Rp 10.042.300).
* **BPJS Kesehatan:** Perusahaan 4.0%, Karyawan 1.0% (Plafon batas maksimal upah: Rp 12.000.000).

### 2.2 THRCalculationService (`app/Domain/HRM/THRCalculationService.php`)
* **Masa Kerja $\ge 12$ Bulan:** $\text{THR} = 1 \times \text{Upah 1 Bulan}$.
* **Masa Kerja $1 \le m < 12$ Bulan:** $\text{THR} = \frac{m}{12} \times \text{Upah 1 Bulan}$.
* **Masa Kerja $< 1$ Bulan:** $\text{THR} = \text{Rp } 0$ (Belum berhak menerima).
* **Pekerja Harian Lepas:** Upah 1 bulan dihitung dari rata-rata upah yang diterima per bulan selama 12 bulan terakhir.

### 2.3 PPh21CalculationService (`app/Domain/Tax/PPh21CalculationService.php`)
* **Kategori TER A:** TK/0 (PTKP Rp 54 jt), TK/1 (PTKP Rp 58.5 jt), K/0 (PTKP Rp 58.5 jt).
* **Kategori TER B:** TK/2, TK/3, K/1, K/2.
* **Kategori TER C:** K/3 (PTKP Rp 72 jt).
* **Masa Desember:** Rekonsiliasi akumulasi penghasilan bruto setahun dikurangi biaya jabatan (5%, max Rp 6jt/thn), iuran pensiun, dan PTKP, kemudian dikenakan tarif progresif Pasal 17 ayat (1) huruf a (5%, 15%, 25%, 30%, 35%) dan dikurangi total PPh 21 yang telah dipotong dari Januari s.d. November.
* **Pekerja Harian Lepas:** Pembebasan upah harian $\le$ Rp 450.000 dan kumulatif $\le$ Rp 2.500.000.

### 2.4 PPhFinalUMKMService (`app/Domain/Tax/PPhFinalUMKMService.php`)
* Pelacakan otomatis dari akumulasi invoice penjualan dan POS order berstatus selesai.
* **Batas Bebas Pajak Rp 500.000.000:** Khusus Wajib Pajak Orang Pribadi per tahun pajak.
* **Tarif 0.5%:** Dikenakan hanya atas selisih omzet di atas Rp 500.000.000 atau omzet penuh untuk Badan Usaha (PT/CV).

### 2.5 SalesTaxService (`app/Domain/Tax/SalesTaxService.php`)
* Menghitung subtotal setelah diskon, ditambah service charge (jika ada), kemudian dikalikan tarif pajak (PB1 10% atau PPN 11%/12%).
* Mendukung mode **Inklusif** (pajak tertera di dalam harga) dan **Eksklusif** (pajak ditambahkan di akhir kalkulasi kasir).

---

## 3. Matriks Entitlement Sesuai Tier Plan

| Fitur / Modul | Free (Rp 0) | Standard (Rp 29k) | Premium (Rp 89k) | Prestige (Rp 199k) |
|---|---|---|---|---|
| **PPh Final UMKM 0.5% (PP 55/2022)** | Ya (Dashboard) | Ya | Ya | Ya |
| **Kalkulasi PB1 / PPN & Service Charge** | Ya (Dasar) | Ya | Ya | Ya |
| **Kalkulasi BPJS Ketenagakerjaan & Kesehatan** | - | - | Ya | Ya |
| **Kalkulasi THR Prorata Join Date** | - | - | Ya | Ya |
| **Pengelolaan Kasbon & Pinjaman Karyawan** | - | - | Ya | Ya |
| **Manajemen Daily Worker (Pekerja Harian)** | - | - | Ya | Ya |
| **Kalkulasi PPh 21 TER (PP 58/2023) & Ps. 17** | - | - | - | Ya |
| **Slip Gaji Otomatis via WhatsApp** | - | - | - | Ya |

---

## 4. Mesin Penggajian Bulanan (`PayrollRunService`)

### 4.1 Siklus Hidup Penggajian (Payroll Lifecycle)
1. **Draf (`draft`):** Operator/Owner membuka `/hrm/payrolls/create`, memilih bulan dan tahun, opsi THR prorata, dan meninjau input variabel (kehadiran, lembur, komisi SPK, potongan kasbon). Sistem menghitung gaji kotor, pajak PPh 21 TER, iuran BPJS, dan gaji bersih (Take Home Pay).
2. **Disetujui (`approved`):** Batch penggajian divalidasi oleh otoritas berwenang (Owner/HR Supervisor). Rincian slip gaji terkunci.
3. **Dibayar (`paid`):**
   - Menandai batch dan seluruh item sebagai `paid`.
   - **Auto-Deduction Kasbon:** Mengurangi sisa saldo (`remaining_balance`) pinjaman staf pada tabel `employee_loans`. Jika lunas, status pinjaman berubah menjadi `completed`.
   - **Komisi SPK:** Mengubah status komisi staf untuk bulan tersebut menjadi `paid`.
   - **Pencatatan Otomatis Beban Keuangan:** Menerbitkan data `Expense` otomatis (`expense_number: EXP-PAY-YYYYMM`, kategori: `'Gaji & Karyawan'`, jumlah: `total_company_cost`) pada modul keuangan (`finance.expenses`).

### 4.2 Skema Data & Model
* **`Payroll` (`app/Models/Payroll.php`):**
  - Kolom: `business_id`, `period_month`, `period_year`, `title`, `status`, `total_gross_pay`, `total_deductions`, `total_take_home_pay`, `total_company_cost`, `total_bpjs_company`, `total_bpjs_employee`, `total_pph21`, `total_loan_deductions`, `total_employees_count`, `processed_by`, `approved_by`, `paid_at`, `payment_method`, `notes`.
  - Helper & Accessors: `payroll_number`, `formatted_period`, `total_net_salary`, `total_gross_salary`, `payment_date`.
* **`PayrollItem` (`app/Models/PayrollItem.php`):**
  - Kolom: `payroll_id`, `business_id`, `user_id`, `employee_name`, `job_title`, `employment_type`, `join_date`, `base_salary`, `daily_rate`, `days_worked`, `fixed_allowances`, `variable_allowances`, `overtime_pay`, `commissions`, `thr_amount`, `gross_pay`, `bpjs_tk_company`, `bpjs_tk_employee`, `bpjs_kes_company`, `bpjs_kes_employee`, `pph21_amount`, `pph21_ter_category`, `pph21_ter_rate`, `loan_deduction`, `other_deductions`, `total_deductions`, `take_home_pay`, `company_total_cost`, `bank_name`, `bank_account_number`, `bank_account_holder`, `whatsapp_number`, `payslip_token`, `status`, `calculation_payload`, `notes`.
  - Token Keamanan: `payslip_token` (64 karakter kriptografis acak) otomatis dibuat saat instansiasi model.

---

## 5. Pengelolaan Pinjaman & Kasbon Karyawan (`employee_loans`)

* **Pencatatan Kasbon:** Input jumlah pinjaman, tenor bulan, tanggal pencairan, dan tujuan. Sistem secara otomatis menghitung cicilan bulanan (`monthly_installment = amount / tenor_months`).
* **Pelacakan Saldo:** `remaining_balance` berkurang otomatis setiap kali batch penggajian bulanan berstatus `paid`.
* **Status Pinjaman:** `active`, `completed`, `cancelled`.

---

## 6. Slip Gaji Digital Apple HIG (`/hrm/payslips/{item}` & `/payslip/{token}`)

* **Desain Apple Bento HIG:** Hirarki tipografi murni, bebas emoji (hanya icon Lucide SVG), layout responsif untuk mobile dan desktop.
* **Fitur Utama Slip:**
  1. **Cetak Fleksibel:** Siap cetak dokumen formal A4 dan printer thermal kasir 80mm via `window.print()`.
  2. **Unduh PDF:** Integrasi client-side download langsung via `html2pdf.js`.
  3. **Integrasi WhatsApp:** Tombol kirim ke WhatsApp karyawan dengan format teks rapi dan tautan verifikasi online.
  4. **Akses Publik Aman:** Rute `/payslip/{token}` memungkinkan karyawan mengakses slip gaji mereka tanpa memerlukan akun/login ke dashboard admin.

