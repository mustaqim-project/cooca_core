# Modul CMS Artikel, Edukasi, & Taksonomi Konten (Content Management System)

> **Status:** VERIFIED  
> **Domain Terkait:** `App\Models\Post`, `App\Models\PostCategory`, `App\Models\PostCluster`, `App\Http\Controllers\Admin\AdminPostController`, `App\Http\Controllers\PublicBlogController`  
> **Tabel Basis Data:** `posts`, `post_categories`, `post_clusters`  
> **Arsitektur UI:** Bento Apple HIG v2.0 (Modal-First, Dual-Sync Taxonomy, Free TinyMCE Editor)

---

## 1. Tujuan & Nilai Bisnis

Modul CMS Artikel & Edukasi Cooca mengelola publikasi konten artikel blog, panduan langkah operasional bisnis (*Cluster K - Tutorial*), dan materi edukasi wawasan bisnis UMKM (*Cluster O - Edukasi*). Modul ini berfungsi sebagai kanal edukasi strategis (*content marketing & SEO engine*) yang mengalirkan calon merchant UMKM organik ke ekosistem Cooca ERP & POS.

---

## 2. Arsitektur Taksonomi & Dual-Sync Backward Compatibility

Konten dikelompokkan ke dalam dua dimensi taksonomi:
1. **Cluster Pilar Strategis (`post_clusters`):**
   - **Cluster K (Tutorial "Cara"):** Panduan langkah demi langkah teknis operasional bisnis (misal: "Cara Menghitung HPP Kafe", "Panduan Stock Opname Gudang").
   - **Cluster O (Edukasi Topikal):** Artikel wawasan manajemen, strategi margin, dan pertumbuhan bisnis UMKM.
2. **Kategori Topikal (`post_categories`):**
   - Topik spesifik (HPP & Biaya, Operasional & Stok, Pemasaran Digital, Pembukuan & Finansial, Layanan Pelanggan, Pajak & Legalitas).

```
┌─────────────────────────────────────────────────────────────┐
│                 CLUSTER PILAR (POST CLUSTERS)               │
│        • Cluster K (Tutorial)     • Cluster O (Edukasi)     │
└──────────────────────────────┬──────────────────────────────┘
                               │ Digabungkan dengan
                               ▼
┌─────────────────────────────────────────────────────────────┐
│                 KATEGORI TOPIKAL (POST CATEGORIES)          │
│   • HPP & Biaya   • Operasional & Stok   • Pemasaran dsb.   │
└──────────────────────────────┬──────────────────────────────┘
                               │ Memayungi
                               ▼
┌─────────────────────────────────────────────────────────────┐
│                     ARTIKEL BLOG (POSTS)                    │
│   • cluster_id + cluster (dual-sync)                        │
│   • category_id + category (dual-sync)                      │
│   • content (TinyMCE HTML)                                  │
│   • excerpt (Plain Text Ringkas)                            │
└─────────────────────────────────────────────────────────────┘
```

### Mekanisme Dual-Sync
Untuk menjaga 100% *backward compatibility* pada rute dan kueri frontend blog publik (`/blog`, `/blog/{slug}`, scopes `scopeTutorial`, `scopeEdukasi`), controller `AdminPostController` secara otomatis menyinkronkan:
- `cluster_id` (integer foreign key) $\leftrightarrow$ `cluster` (string code: `tutorial`, `edukasi`).
- `category_id` (integer foreign key) $\leftrightarrow$ `category` (string name: `HPP & Biaya`, dsb.).

---

## 3. Fitur Utama Modul CMS

### 3.1 Integrasi Visual Text Editor TinyMCE Free
- Menggunakan CDN resmi TinyMCE Free: `https://cdn.tiny.cloud/1/2a7oruubgvqukc1gach4pq8j3pm4q12ooot480ieevdxu15m/tinymce/8/tinymce.min.js`.
- Ditargetkan secara presisi pada `#post-content` (name="content") pada form create dan edit.
- Textarea `excerpt` tetap berupa plain text ringkas 1–2 kalimat tanpa TinyMCE untuk kartu preview feed.
- Event submission form dilengkapi pemanggilan otomatis `tinymce.triggerSave()` guna menjamin konten kaya terkirim utuh ke database.

### 3.2 Apple Pill Segmented Control & Manajemen Taksonomi
- Antarmuka utama `/admin/posts` mengusung 3-tab navigasi bergaya Apple:
  1. **Semua Artikel:** Daftar artikel dengan filter pencarian, cluster, kategori, dan status publish.
  2. **Kategori Post:** Tabel data master kategori dengan jumlah artikel, urutan sort, dan modal edit/tambah.
  3. **Cluster Konten:** Tabel master cluster pilar konten.
- Mengadopsi Apple Inset Modal Sheets untuk operasi tambah/edit kategori dan cluster tanpa reload halaman.
- Tombol aksi inline `[ + Kategori Baru ]` pada form create/edit memungkinkan penambahan kategori baru via AJAX secara instan tanpa mereset input form artikel yang sedang ditulis.

### 3.3 Optimasi Mesin Pencari (SEO Ready)
- Otomatisasi generate slug ramah SEO dari judul artikel.
- Kolom `meta_title` dan `meta_description` kustom untuk integrasi Google Search Snippet dan kartu sosial OpenGraph.

---

## 4. Kepatuhan Desain & Keamanan

1. **Bento Apple HIG v2.0:** Sudut membulat squircle `rounded-[20px]`, hairline border `border-black/[0.06] dark:border-white/[0.08]`, tactile press feedback `active:scale-[0.98]`.
2. **Zero Unicode Emoji Rule:** 100% bebas karakter emoji unicode visual, hanya menggunakan ikon resmi Lucide (`file-text`, `tag`, `layers`, `wrench`, `graduation-cap`, dll).
3. **Anti-Pill-Abuse & Anti-AI-Template:** Maksimal satu badge status per entitas, bebas titik pulsa palsu (*fake pulse dots*), dan angka pembaca/artikel disajikan dalam tipografi murni `tabular-nums`.
4. **Anti-Accidental Data Loss:** Penghapusan kategori atau cluster tidak menghapus artikel secara kaskade, melainkan melakukan *nullify/disassociate* relasi secara aman.
5. **iOS Safari Anti-Auto-Zoom:** Seluruh kolom input dan textarea form mengadopsi ukuran font minimal 16px (`text-[16px] sm:text-[13px]`).

---

## 5. Modul CMS Kebijakan & Dokumen Legalitas (`legal_pages`)

Selain artikel blog, CMS Cooca menaungi pengelolaan dokumen kepatuhan hukum dan regulasi resmi platform melalui model `App\Models\LegalPage`:

### 5.1 Struktur Data & Arsitektur Segmentasi
Tabel `legal_pages` dirancang dengan pemisahan klausul spesifik untuk audiens yang berbeda:
* `content_general`: Ketentuan umum, landasan hukum (UU PDP No. 27/2022 & KUHPerdata), integrasi pihak ketiga (TriPay, Biteship, WhatsApp, Meta, TikTok), dan batasan tanggung jawab platform.
* `content_owner`: Ketentuan khusus pemilik bisnis UMKM (langganan SaaS, sistem escrow settlement, HPP, kuota AI/storage, pengemasan paket kurir, AML/KYB).
* `content_customer`: Ketentuan khusus pembeli toko & pengunjung (pembayaran pesanan, alamat kirim, retur dengan video unboxing, kuitansi digital WhatsApp, larangan order fiktif).

### 5.2 Tata Kelola Admin & Tampilan Publik
* **Admin CMS (`/admin/legal-pages`):** Superadmin dapat menyunting naskah hukum menggunakan visual editor TinyMCE, memperbarui nomor versi dokumen, tanggal efektif, dan meta tag SEO.
* **Portal Publik (`/privacy` & `/terms`):** Mengadopsi filter segmentasi audiens interaktif berbasis Alpine.js (*Semua Ketentuan*, *Khusus Pemilik Usaha*, *Khusus Pelanggan Toko*), Table of Contents (TOC) sticky navigasi cepat, dan tombol cetak/PDF instan.
