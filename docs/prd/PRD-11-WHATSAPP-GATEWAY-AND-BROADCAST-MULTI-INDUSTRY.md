# PRD-11: Hardening Keamanan Cyber, Proteksi Fraud Pengalihan Struk POS, & Antarmuka Sadar Konteks 20 Industri pada Modul WhatsApp Gateway & Broadcast

> **Status:** IMPLEMENTED & VERIFIED 100%  
> **Versi:** 1.0  
> **Penanggung Jawab:** Security Auditor, Principal Laravel Architect, UI/UX Engineer, Anti-Fraud Specialist  
> **Modul Terkait:** `resources/views/app/whatsapp/`, `WhatsAppWebController`, `WhatsAppBroadcastWebController`, `MetaWhatsAppOnboardingController`, `WhatsAppGatewayService`, `WhatsAppTemplateService`, `PosTerminalWebController`  
> **Dokumen Terkait:** [`docs/system/workflows/whatsapp-gateway-and-broadcast-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/whatsapp-gateway-and-broadcast-flow.md), [`docs/agent.md`](file:///c:/laragon/www/cooca_core/docs/agent.md), [`docs/SYSTEM_GUIDE.md`](file:///c:/laragon/www/cooca_core/docs/SYSTEM_GUIDE.md)

---

## 1. Ringkasan Eksekutif & Latar Belakang Masalah

Modul **WhatsApp Gateway & Broadcast Komunikasi (`resources/views/app/whatsapp`)** merupakan kanal komunikasi langsung (*direct customer-facing channel*) terpenting di platform COOCA. Modul ini mengotomatiskan pengiriman struk belanja digital kasir POS tanpa kertas, menjembatani pendaftaran mandiri resmi Meta WhatsApp Cloud API (Graph API v26.0), dan menyebarkan pesan promosi tertarget ke kontak pelanggan.

Berdasarkan audit komprehensif hulu-ke-hilir (*end-to-end*) terhadap 5 berkas antarmuka Blade (`index.blade.php`, `broadcast.blade.php`, `create.blade.php`, `broadcast_detail.blade.php`, `logs.blade.php`), controller, service, dan job antrean, ditemukan **8 kesenjangan kritis (*critical gaps*)** yang mencakup aspek keamanan cyber, skema fraud internal kasir, risiko pelanggaran kebijakan Meta, dan ketiadaan adaptasi konteks untuk 20 sektor industri:

1. **Celah Broken Access Control pada Tautan Struk Digital Pelanggan (`public.receipt`):**  
   Tautan struk yang dikirimkan via WhatsApp (`https://cooca.id/receipt/{order}`) mengarah ke `PosTerminalWebController@printReceipt` yang secara langsung memanggil `Context::requireBusiness()`. Pelanggan eksternal yang mengeklik tautan tersebut dari HP mereka akan mengalami crash/HTTP 403 atau diarahkan ke login kasir karena tidak memiliki sesi login merchant aktif.
2. **Celah Server-Side Request Forgery (SSRF) pada URL Media Banner Broadcast:**  
   Formulir broadcast menerima `media_url` dengan validasi standar `nullable|url|max:500`. URL ini diteruskan ke service pengiriman tanpa validasi IP internal/cloud metadata (`169.254.169.254`, `127.0.0.1`, LAN 192.168.x.x), membuka celah bagi aktor jahat untuk memindai jaringan privat server backend.
3. **Ketiadaan Rate Limiting pada Uji Coba Pengiriman Pesan (Toll Fraud / WhatsApp Bombing):**  
   Endpoint `POST /whatsapp/test` tidak dikawal oleh middleware `throttle`. Staf dengan hak akses `whatsapp.manage` dapat membanjiri nomor target eksternal dengan ribuan pesan otomatis dalam hitungan detik, menghabiskan kuota WABA toko dan mencemari reputasi nomor Meta.
4. **Modus Fraud Pengalihan Struk Kasir (Receipt Redirection & Anti-Lapping Evasion):**  
   Endpoint `POST /whatsapp/orders/{order}/receipt` menerima parameter `phone` kustom tanpa validasi integritas atau audit logging. Kasir nakal dapat mengarahkan struk ke nomor WhatsApp miliknya sendiri untuk menutupi manipulasi barang atau menggelapkan uang penjualan tunai.
5. **Kebocoran Data Pribadi Pelanggan (PII Data Exposure) pada Log Penerima:**  
   Tabel audit log penerima broadcast (`broadcast_detail.blade.php`) dan log pesan (`logs.blade.php`) menampilkan nomor telepon pelanggan secara utuh tanpa masking bagi staf operasional biasa, membuka risiko pencurian dan penjualan database kontak konsumen (*customer data exfiltration*).
6. **Kerentanan Double-Submit & Duplicate Broadcast Dispatch:**  
   Pengiriman broadcast tidak dilindungi oleh *idempotency key* di backend. Klik ganda pada form atau gangguan jaringan dapat memicu terciptanya dua entitas kampanye identik dan mendispatch dua job background sekaligus, mengirimkan pesan ganda ke ratusan/ribuan kontak pelanggan.
7. **Duplikasi Antarmuka & Fragmentasi Kode (Anti-Bento Violation):**  
   Terdapat duplikasi fungsional antara modal komposer di `broadcast.blade.php` dan halaman terpisah `create.blade.php`. Tombol di `broadcast_detail.blade.php` masih melompat ke route `broadcast.create` alih-alih mengadopsi prinsip *Modal-First & Full-Size Form Canvas* tanpa lompatan halaman (*zero navigation jumps*).
8. **Ketiadaan Antarmuka Sadar Konteks (*Context-Aware UI*) untuk 20 Sektor Industri:**  
   Variabel personalisasi pesan hanya menyediakan tag ritel umum (`{nama}`, `{poin}`, `{tier}`, `{bisnis}`) dan teks contoh kaku diskon belanja kasir. Bisnis Bengkel (tidak ada tag nopol kendaraan/servis), Laundry (tidak ada nomor rak/nota), Apotek (tidak ada perlindungan bahaya promosi obat keras berisiko ban Meta WABA), dan Kontraktor (tidak ada termin progres) dipaksa menggunakan antarmuka ritel generik.

---

## 2. Analisis Kesenjangan Sistem Existing (Gap Analysis)

```text
┌───────────────────────────────────────┬──────────────────────────────────────────┐
│ KONDISI SEKARANG (CURRENT STATE)       │ KONDISI TARGET (TARGET ENTERPRISE STATE)  │
├───────────────────────────────────────┼──────────────────────────────────────────┤
│ 1. Tautan WA receipt abort 403 saat   │ 1. Public receipt route decoupled dari   │
│    diklik pelanggan dari WhatsApp HP. │    Context::requireBusiness(), aman tamu.│
│ 2. Parameter media_url bebas URL tanpa│ 2. Validasi SSRF ketat (Anti-localhost,  │
│    blokir IP privat/metadata AWS.     │    Anti-169.254, hanya scheme https).    │
│ 3. Endpoint /test tanpa throttle      │ 3. Dikawal throttle:5,1 (maks 5 tes/menit│
│    (Rentan spamming / toll fraud).    │    per user) + sanitasi E.164 otomatis.  │
│ 4. Kasir bebas mengganti nomor struk  │ 4. Pengalihan nomor struk dicatat ke     │
│    tanpa jejak audit suspicious.      │    audit_logs + Supervisor PIN jika beda.│
│ 5. Nomor HP pelanggan tampil polos di  │ 5. Masking parsial PII (0812••••7890)    │
│    seluruh tabel log penerima broadcast│    untuk staf non-Owner.                 │
│ 6. Klik ganda kirim memicu duplikasi  │ 6. Idempotency key 5 menit di backend +  │
│    broadcast ganda ke semua kontak.   │    Alpine submitting disable state.      │
│ 7. Duplikasi file broadcast.blade dan │ 7. Konsolidasi penuh ke Modal-First XXL, │
│    create.blade melanggar standar HIG.│    redirect broadcast.create ke modal.   │
│ 8. Template & simulator hanya ritel,  │ 8. Context-Aware 20 Industri (tag nopol, │
│    tanpa proteksi Meta Ban Apotek/F&B.│    rak, SPK + Meta Policy Compliance).   │
└───────────────────────────────────────┴──────────────────────────────────────────┘
```

---

## 3. Ruang Lingkup Proyek (Scope & Non-Scope)

### Dalam Cakupan (In-Scope):
1. **Perbaikan Tautan Struk Publik Pelanggan:** Restrukturisasi `public.receipt` agar dapat dibuka oleh pelanggan umum tanpa sesi merchant, dengan scoping validasi `order` yang aman.
2. **Hardening Keamanan Cyber:** Validasi SSRF pada `media_url` dan penambahan *throttle rate-limiter* pada endpoint uji coba pesan `POST /whatsapp/test`.
3. **Proteksi Fraud Struk POS:** Deteksi dan audit logging jika nomor tujuan struk dialihkan dari nomor pelanggan master transaksi.
4. **Idempotency & Anti-Double-Submit:** Kunci idempotensi unik berbasis hash (business_id + title + message + date) untuk mencegah blast duplikat.
5. **Perlindungan Data Pribadi (PII Masking):** Sensor 4 digit tengah nomor telepon pelanggan pada view log penerima untuk peran non-owner.
6. **Konsolidasi Antarmuka (UI Unification):** Menjadikan modal sheet XXL di `broadcast.blade.php` sebagai satu-satunya kanvas pembuatan blast (*Modal-First*), serta memberikan fallback redirect anggun pada route `broadcast.create`.
7. **Antarmuka Sadar Konteks 20 Industri:** Penyediaan preset template, kamus tag personal adaptif (`{nopol}`, `{servis}`, `{no_rak}`, `{proyek}`), peringatan jam istirahat (*Quiet Hours*), dan guardrail kepatuhan Meta Policy untuk sektor kesehatan/Apotek.

### Di Luar Cakupan (Non-Scope):
1. Mengubah struktur integrasi Facebook SDK JavaScript v26.0 untuk Embedded Signup.
2. Mengubah skema database inti tabel `whatsapp_accounts` dan `whatsapp_sessions`.
3. Mengubah arsitektur driver antrean Laravel Queue yang telah berjalan.

---

## 4. Kebutuhan Fungsional (Functional Requirements)

### FR-01: Public Customer Receipt Route Hardening
* **Deskripsi:** Endpoint `GET /receipt/{order}` (`public.receipt`) wajib dapat diakses oleh publik tanpa memerlukan autentikasi login atau `Context::requireBusiness()`.
* **Keamanan:** Memastikan pesanan berstatus selesai (`completed`/`paid`), data finansial ditampilkan dalam format cetak struk aman, dan nomor telepon kasir/staf tidak terekspos terbuka.

### FR-02: Perlindungan Anti-SSRF pada URL Media Banner
* **Deskripsi:** Form pembuatan broadcast wajib memvalidasi `media_url` menggunakan filter ketat:
  - Wajib protokol `https://`.
  - Resolusi DNS dilarang mengarah ke alamat IP loopback (`127.0.0.1`, `::1`), alamat link-local/cloud metadata (`169.254.x.x`), atau subnet IP privat RFC 1918 (`10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16`).
  - Ekstensi berkas atau Content-Type wajib berupa berkas gambar (`jpg`, `jpeg`, `png`, `webp`).

### FR-03: Rate Limiting & Auto-Format pada Uji Coba Pesan Tes
* **Deskripsi:**
  - Route `POST /whatsapp/test` wajib dikawal `middleware('throttle:5,1')`.
  - Input nomor telepon dibersihkan secara otomatis di backend: menghapus spasi, strip, tanda kurung, dan mengubah format `08...` atau `+62...` menjadi `62...`.

### FR-04: Deteksi & Audit Fraud Pengalihan Nomor Struk Kasir
* **Deskripsi:** Pada saat pengiriman struk POS manual (`POST /whatsapp/orders/{order}/receipt`), jika parameter `phone` yang dimasukkan berbeda dari nomor telepon pelanggan yang terdaftar pada order:
  - Sistem mencatat rekaman audit trail ke tabel `audit_logs` dengan aksi `receipt.phone_override`.
  - Mencatat detail `user_id` kasir, IP address, nomor asli, dan nomor pengganti.

### FR-05: Idempotency Lock pada Pembuatan Broadcast
* **Deskripsi:** Sebelum menyimpan `WhatsAppBroadcastCampaign`, sistem membuat kunci cache atomik:
  `lock_key = "broadcast_lock_" . md5($businessId . $title . $message)`
  Jika request identik dikirimkan dalam kurun waktu 300 detik (5 menit), request ditolak dengan pesan: *"Kampanye dengan judul dan pesan yang sama baru saja dijadwalkan. Mohon tunggu proses pengiriman selesai."*

### FR-06: Masking PII pada Tabel Log & Penerima Pesan
* **Deskripsi:** Pada `broadcast_detail.blade.php` dan `logs.blade.php`, jika pengguna yang sedang login bukan berstatus `owner` (misal kasir atau staf marketing):
  - Kolom nomor telepon disamarkan (contoh: `0812••••7890` atau `+62 812-••••-7890`).
  - Pemilik usaha (`isOwner()`) tetap dapat melihat nomor lengkap untuk keperluan verifikasi operasional.

### FR-07: Antarmuka Sadar Konteks (Context-Aware UI) 20 Sektor Industri
* **Deskripsi:** Formulir komposer broadcast dan live simulator menyesuaikan chip tag personal dan placeholder pesan sesuai `$business->template_code`:
  - **Klaster F&B:** Tag `{nama}`, `{poin}`, `{meja}`. Contoh: Promo jam santai / menu baru.
  - **Klaster Bengkel:** Tag `{nama}`, `{nopol}`, `{servis_terakhir}`. Contoh: Pengingat ganti oli & servis berkala.
  - **Klaster Laundry:** Tag `{nama}`, `{no_rak}`, `{berat_kg}`. Contoh: Notifikasi cucian selesai & siap ambil.
  - **Klaster Apotek:** Peringatan kepatuhan Meta & BPOM: *"Dilarang mempromosikan obat keras/resep via WhatsApp blast."*
  - **Klaster Kontraktor & Manufaktur:** Tag `{nama}`, `{no_spk}`, `{proyek}`. Contoh: Update penyelesaian SPK produksi.
  - **Peringatan Jam Istirahat (Quiet Hours):** Banner peringatan interaktif jika pengiriman dijadwalkan pada pukul 21:00–08:00 WIB.

### FR-08: Konsolidasi UI & Modal-First Architecture
* **Deskripsi:**
  - Menghapus fragmentasi alur dengan mengarahkan seluruh aksi pembuatan broadcast ke Modal Sheet XXL di `broadcast.blade.php`.
  - Route `whatsapp.broadcast.create` secara anggun dialihkan (*graceful redirect*) ke `route('whatsapp.broadcast.index', ['create' => 1])` dengan otomatis membuka modal form.
  - Form dilengkapi proteksi double-submit: tombol submit dinonaktifkan (`disabled`), menampilkan icon spinner, dan teks berganti menjadi *"Menjadwalkan..."*.

---

## 5. Kriteria Penerimaan (Acceptance Criteria - Gherkin Format)

```gherkin
Feature: Keamanan & Integritas Publik Struk Digital
  Scenario: Pelanggan umum membuka tautan struk WhatsApp dari HP
    Given pelanggan menerima tautan "https://cooca.id/receipt/{order_id}" di WhatsApp
    When pelanggan mengeklik tautan tersebut tanpa sesi login merchant
    Then halaman menampilkan struk digital belanja resmi dengan benar
    And tidak terjadi error 403 atau pengalihan ke halaman login

Feature: Proteksi Cyber Security Anti-SSRF Media URL
  Scenario: Pengguna memasukkan URL IP internal pada formulir broadcast
    Given staf berada di modal komposer broadcast
    When staf menginput media_url bernilai "http://169.254.169.254/latest/meta-data"
    And menekan tombol kirim
    Then sistem menolak dengan pesan validasi "URL media tidak aman atau mengarah ke alamat jaringan lokal"

Feature: Pencegahan Double-Submit & Kampanye Duplikat
  Scenario: Merchant tidak sengaja mengeklik tombol kirim broadcast dua kali
    Given merchant telah mengisi form broadcast
    When tombol submit diklik dua kali secara cepat
    Then request kedua diblokir oleh kunci idempotensi
    And hanya 1 kampanye broadcast dan 1 background job yang dibuat di sistem

Feature: Penegakan Aturan Meta Policy pada Bisnis Apotek
  Scenario: Merchant dengan template bisnis apotek membuka modul broadcast
    Given merchant terdaftar dengan sektor "retail_pharmacy" (Apotek)
    When membuka modal buat blast promosi
    Then tampil kartu peringatan kepatuhan Meta Health Policy
    And pesan menegaskan larangan promosi obat keras / daftar G
```

---

## 6. Matriks Adaptasi 20 Sektor Industri untuk WhatsApp Module

| Klaster Industri | Sektor Usaha | Preset Tag Personal Tambahan | Preset Copywriting / Template Relevan | Aturan Kepatuhan & Safety Guard |
|---|---|---|---|---|
| **F&B (5)** | Resto, Cafe, Bakery, Cloud Kitchen, Catering | `{nama}`, `{poin}`, `{meja}`, `{bisnis}` | Undangan promo kuliner, menu spesial weekend, reservasi meja makan. | Peringatan Quiet Hours (hindari blast di luar jam operasional toko). |
| **Manufaktur (5)** | Garment, Presisi, Mebel, Kerajinan, Percetakan | `{nama}`, `{no_spk}`, `{produk}`, `{bisnis}` | Konfirmasi status pengerjaan SPK, barang jadi siap kirim, termin invoice. | Larangan blast ritel "tunjukkan ke kasir". Wajib B2B format. |
| **Ritel & Apotek (2)** | Reseller, Apotek | `{nama}`, `{poin}`, `{no_resep}`, `{bisnis}` | Notifikasi tebus obat siap ambil, pengingat kontrol rutin kesehatan. | **HARAM PROMOSI OBAT KERAS / DAFTAR G** (Meta & BPOM compliance guard). |
| **Jasa Harian (4)** | Bengkel, Salon, Laundry, Auto Detailing | `{nama}`, `{nopol}`, `{no_rak}`, `{servis}` | Bengkel: Pengingat servis berkala km 5.000 / ganti oli.<br/>Laundry: Notifikasi cucian selesai di rak `{no_rak}`. | Bengkel wajib nomor polisi; Laundry wajib nomor nota & rak simpan. |
| **Jasa Proyek (3)** | Kontraktor, Event Organizer, Agency | `{nama}`, `{proyek}`, `{termin}`, `{bisnis}` | Progres mingguan proyek fisik, pengajuan termin invoice, rundown acara. | Nada formal profesional; dilarang menggunakan format struk kasir ritel. |
| **Distribusi & Agro (2)**| Distributor FMCG, Peternakan/Tani | `{nama}`, `{no_do}`, `{jatuh_tempo}`, `{bisnis}` | Konfirmasi keberangkatan armada DO, pengingat piutang toko jatuh tempo. | Wajib mencantumkan nomor DO / Faktur resmi. |

---

## 7. Rencana Rilis Bertahap (Phased Rollout Plan)

- **Fase 1 (Keamanan & Integritas Data - P1):**
  - Perbaikan rute `public.receipt` agar dapat diakses pelanggan tamu secara aman tanpa 403.
  - Implementasi SSRF validator pada URL media broadcast.
  - Penambahan middleware throttle pada endpoint pengujian `/test`.
- **Fase 2 (Proteksi Fraud & Data Privacy - P1):**
  - Audit logging pengalihan nomor telepon pada pengiriman struk POS manual.
  - Masking PII nomor telepon pelanggan pada view log untuk staf non-owner.
  - Idempotency key lock 5 menit pada pembuatan broadcast.
- **Fase 3 (Konsolidasi Antarmuka Apple HIG - P2):**
  - Pembersihan fragmentasi: Pengalihan `broadcast.create` ke Modal Sheet XXL di `broadcast.blade.php`.
  - State `submitting` Alpine.js & loading spinner pada seluruh tombol aksi WhatsApp.
  - Sanitizer nomor HP otomatis di sisi frontend dan backend.
- **Fase 4 (Personalisasi Sadar Konteks 20 Industri - P2):**
  - Inject kamus tag personal adaptif (`{nopol}`, `{no_rak}`, `{no_spk}`) berdasarkan `$business->template_code`.
  - Banner peringatan Quiet Hours malam hari.
  - Guardrail kepatuhan Meta Policy untuk sektor farmasi/apotek.
