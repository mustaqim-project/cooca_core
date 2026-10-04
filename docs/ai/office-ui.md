# Panduan Antarmuka Pengguna (AI Office UI)

> **Layer:** Bento Apple Human Interface Guidelines (HIG) Design System  
> **Source:** `resources/views/app/ai/` (`office.blade.php`, `actions.blade.php`, `history.blade.php`, `providers.blade.php`)

---

## 1. Filosofi Desain Bento Apple HIG

Antarmuka COOCA AI dirancang menyerupai "Kantor Digital Eksekutif" yang modern, tenang, dan profesional. Mengikuti standar Bento Apple HIG:

1. **Struktur Grid Bento:**
   - Kartu modular dengan sudut membulat proporsional (`rounded-[20px]`).
   - Garis batas halus (`border border-black/[0.06] dark:border-white/[0.08]`).
   - Efek kedalaman kaca halus (*subtle glassmorphism* & *backdrop-blur*).
2. **Tipografi Bersih & Elegan:**
   - Hierarki teks jelas dengan pelacakan huruf rapat (`tracking-tight`).
   - Warna teks kontras tinggi untuk keterbacaan optimal (`text-black dark:text-white`).
3. **Standar Ikonografi Bebas Emoji (Zero Emoji Mandate):**
   - Tidak menggunakan emotikon grafis informal (seperti 🤖, 💼, 📊, 🚀).
   - Seluruh indikator visual menggunakan ikon vektor standar Lucide SVG (`<i data-lucide="...">`).

---

## 2. Peta Antarmuka & Halaman Utama

### 2.1 AI Office (`/ai` atau `/ai/office`)
- **Executive Suite:**
  - Kartu AI CEO dengan status kesehatan bisnis makro dan tombol pemicu *Daily Check*.
  - Kartu AI COO dengan monitor ketersediaan stok pergudangan dan tombol deteksi fraud/anomali.
- **5 Department Pods:**
  - Pod Penjualan (Sales Pod), Pod Pemasaran (Marketing Pod), Pod Operasional (Ops Pod), Pod Keuangan (Finance Pod), dan Pod SDM (HR Pod).
  - Setiap pod menampilkan status tim, ringkasan metrik terkini, dan kartu meja (*desk*) agen spesialis.
- **Modal Konsultasi Interaktif:**
  - Menampilkan modal konsultasi multi-agen dengan pemilihan peran yang fleksibel, riwayat percakapan yang bersih, dan tampilan kartu rekomendasi proposal.

### 2.2 Action Center (`/ai/actions`)
- **Pusat Pengambilan Keputusan Maker-Checker:**
  - Tab penyaring status: *Menunggu Persetujuan* (Pending), *Disetujui* (Approved), *Dieksekusi* (Executed), dan *Ditolak* (Rejected).
  - Kartu proposal dengan lencana tingkat risiko berkode warna (Hijau untuk Rendah, Oranye untuk Sedang, Merah untuk Tinggi/Kritis).
- **Modal Peninjauan Terperinci (Full Review Sheet):**
  - **WHY:** Mengapa agen merekomendasikan aksi ini.
  - **WHAT:** Spesifikasi detail barang, pihak terkait, dan nilai transaksi.
  - **IMPACT:** Dampak finansial dan operasional terhadap kas bisnis.
  - **CHANGES:** Pratinjau mutasi data sebelum dieksekusi.
  - Tombol aksi tegas: *Tolak Proposal* (dengan alasan) atau *Setujui & Eksekusi Sekarang*.

### 2.3 Riwayat Tugas & Audit (`/ai/history`)
- Menampilkan rekaman kronologis setiap tugas yang dikerjakan oleh masing-masing agen, durasi komputasi (*latency ms*), konsumsi token, dan status penyelesaian.

### 2.4 Konfigurasi Multi-Provider (`/ai/providers`)
- Tempat pemilik usaha mendaftarkan kunci API vendor AI mereka sendiri (OpenAI, Gemini, Anthropic, OpenRouter) dengan enkripsi penuh di sisi server dan tombol uji latensi koneksi.
