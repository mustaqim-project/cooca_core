# Alur Kerja Media Sosial Omnichannel, Penjadwalan Konten & Moderasi (Social Media Omnichannel Flow)

> **Status:** COMPLETE  
> **Domain Terkait:** `app/Domain/SocialMedia/`, `app/Domain/Storage/`, `app/Domain/Billing/`  
> **Controller Terkait:** `SocialMediaWebController`, `MetaSocialMediaWebhookController`, `AdminSocialMediaController`  
> **View Terkait:** `resources/views/app/social_media/index.blade.php`, `posts.blade.php`, `calendar.blade.php`, `inbox.blade.php`, `insights.blade.php`  
> **Job & Command:** `App\Jobs\SocialMedia\PublishSocialMediaTargetJob`, `social-media:publish-scheduled`, `social-media:purge-published`  
> **Tabel Basis Data:** `social_media_accounts`, `social_media_posts`, `social_post_targets`, `social_post_media`, `social_media_comments`, `storage_files`, `quota_monthly_usages`

---

## 1. Ikhtisar Alur Kerja (Workflow Overview)

Modul **Media Sosial Omnichannel & Publikasi Konten** (`resources/views/app/social_media`) adalah sentra pemasaran digital terpadu bagi merchant Cooca untuk mempublikasikan materi promosi, menjadwalkan kalender kampanye, dan memoderasi interaksi pelanggan di 5 jaringan media sosial utama: **Facebook Pages, Instagram Business, Threads, TikTok, dan LinkedIn**.

Modul ini menjalankan 5 pilar fungsional utama:
1. **Otorisasi Terpadu Multi-Platform (1-Klik OAuth 2.0):**
   - **Meta:** Facebook Login for Business dialog untuk memperoleh Long-Lived User Access Token (60 hari) yang ditukar menjadi Permanent Page Access Tokens untuk Facebook Page dan akun Instagram Bisnis terkait.
   - **TikTok:** Content Posting API v2 via Authorization Code dengan State Verification, auto-refresh token 365 hari.
   - **LinkedIn:** OpenID Connect & Community Management API (`w_member_social`, `openid`, `profile`, `email`) untuk publikasi feed UGC Post dan aset media.
2. **Unified Social Media Composer (Omnichannel Dispatch):**
   - Satu kanvas pembuatan konten untuk multi-saluran sekaligus dengan opsi teks caption global atau kustomisasi khusus per saluran (*Channel Caption Overrides*).
   - Penegakan aturan ketat Cooca: **Maksimal 5 hashtag unik per postingan** guna menjaga estetika dan mencegah penalti algoritma spam.
   - Format multimedia: Foto tunggal, Album Carousel (2–10 media campuran foto/video), Video MP4/MOV, Reels (9:16 vertikal), dan Teks murni.
3. **Mesin Penjadwalan Fleksibel & Kalender Visual:**
   - Tiga moda publikasi: `all_now` (seketika), `all_same` (serentak pada tanggal & jam tertentu), atau `per_channel` (waktu spesifik berbeda untuk masing-masing saluran).
   - Kalender visual bulanan interaktif dengan lencana saluran dan indikator status pengiriman.
4. **Sentra Interaksi & Moderasi Komentar (Multi-Channel Inbox):**
   - Penarikan komentar pelanggan masuk secara real-time melalui webhook resmi Meta (`/api/v1/social-media/meta/webhook`).
   - Fitur balas langsung dari dashboard Cooca melalui API resmi atas nama akun resmi toko.
5. **Analitik Keterlibatan & Pemantauan Performa Organik:**
   - 6 KPI metrik agregat: Tayangan (Impressions), Jangkauan (Reach), Keterlibatan (Engagement), Suka (Likes), Komentar (Comments), dan Bagikan (Shares).
   - Tombol *Tarik Live* per postingan untuk sinkronisasi metrik langsung ke server penyedia.
   - Kebijakan Nol Meta Ads: 100% fokus pada pertumbuhan organik dan interaksi autentik pelanggan.

---

## 2. Diagram Rantai 11-Node Hulu-ke-Hilir (End-to-End Execution Flow)

```text
┌───────────────────────────────────────────────────────────────────────────────────────────────────┐
│ [Node 1] Aksi Pengguna / Pemicu Sistem (User Action / Trigger)                                    │
│ - Merchant menautkan akun via [Hubungkan Meta] / [Hubungkan TikTok] / [Hubungkan LinkedIn]        │
│ - Merchant membuka modal [Unified Composer] dan memilih target akun, format media, serta caption   │
│ - Merchant memilih waktu tayang: [Semua Sekarang], [Jadwal Serentak], atau [Beda per Saluran]    │
│ - Merchant membalas komentar pelanggan di [Kotak Masuk & Komentar]                                │
│ - Cron scheduler mengeksekusi `social-media:publish-scheduled` setiap menit                      │
└────────────────────────────────────────────────┬──────────────────────────────────────────────────┘
                                                 │
                                                 ▼
┌───────────────────────────────────────────────────────────────────────────────────────────────────┐
│ [Node 2] Frontend Blade UI & Alpine.js Engine (Bento Apple HIG v2.0)                              │
│ - Alpine.js `socialPostsManager()`: Menghitung tagar unik real-time (`hashtagCount <= 5`)        │
│ - Media Tray: Pratinjau lokal via `URL.createObjectURL()`, reordering carousel tray, hapus media  │
│ - Character counter dinamis berbasis batasan platform terketat yang dipilih                       │
│ - Sub-tab navigasi persist: Koneksi Akun, Posting Konten, Kalender Konten, Inbox, Analitik       │
└────────────────────────────────────────────────┬──────────────────────────────────────────────────┘
                                                 │
                                                 ▼
┌───────────────────────────────────────────────────────────────────────────────────────────────────┐
│ [Node 3] Lapisan Route & Middleware Keamanan (Security Gateways)                                  │
│ - `auth:web`: Memastikan sesi login merchant aktif                                                │
│ - `business.active`: Penguncian tenant context aktif                                              │
│ - `require.permission:whatsapp.view` (Eksisting) -> Refactor: `social_media.view|manage`          │
│ - `entitlement:social_post`: Kuota gratis 3 post/bulan atau Add-on Unlimited (Rp 89.000/bln)     │
└────────────────────────────────────────────────┬──────────────────────────────────────────────────┘
                                                 │
                                                 ▼
┌───────────────────────────────────────────────────────────────────────────────────────────────────┐
│ [Node 4] Web Controller Dispatcher                                                                │
│ - `SocialMediaWebController@posts`: Render feed postingan berpaginasi (15 item)                   │
│ - `SocialMediaWebController@storePost`: Orkestrator validasi, upload media, & pembuatan entitas   │
│ - `SocialMediaWebController@retryTarget`: Retry surgical untuk target saluran yang gagal          │
│ - `SocialMediaWebController@replyComment`: Pengiriman balasan komentar ke API platform            │
└────────────────────────────────────────────────┬──────────────────────────────────────────────────┘
                                                 │
                                                 ▼
┌───────────────────────────────────────────────────────────────────────────────────────────────────┐
│ [Node 5] Validasi Input & Sanitasi Muatan (Validation Layer)                                      │
│ - `SocialMediaContentValidator`: Validasi batas 5 hashtag unik per post & batas panjang karakter  │
│ - Validasi berkas: MIME type `image/*` / `video/*`, ukuran berkas maks 100 MB                     │
│ - Validasi tipe konten: Minimal 2 dan maksimal 10 berkas untuk Instagram Carousel                 │
└────────────────────────────────────────────────┬──────────────────────────────────────────────────┘
                                                 │
                                                 ▼
┌───────────────────────────────────────────────────────────────────────────────────────────────────┐
│ [Node 6] Scoping Multi-Tenant & Anti-IDOR Guard                                                   │
│ - Seluruh query diikat eksplisit ke `Context::requireBusiness()->id`                              │
│ - Pemeriksaan kepemilikan akun: `SocialMediaAccount::where('business_id', $business->id)`        │
│ - Validasi silang target retry: Target wajib berelasi dengan pos milik bisnis aktif               │
└────────────────────────────────────────────────┬──────────────────────────────────────────────────┘
                                                 │
                                                 ▼
┌───────────────────────────────────────────────────────────────────────────────────────────────────┐
│ [Node 7] Layanan Domain & Penyimpanan Terisolasi (Domain Service Layer)                           │
│ - `TenantStorage::publicDir()`: Menempatkan berkas di `storage/app/public/business/{id}/social_media`│
│ - `StorageTrackingService`: Mencatat riwayat upload berkas sementara (`is_temporary = true`)      │
│ - `SocialMediaManager`: Provider factory (`meta`, `tiktok`, `linkedin`)                          │
└────────────────────────────────────────────────┬──────────────────────────────────────────────────┘
                                                 │
                                                 ▼
┌───────────────────────────────────────────────────────────────────────────────────────────────────┐
│ [Node 8] Antrean Asinkron & Penjadwalan Latar Belakang (Queue Worker & Scheduler)                 │
│ - `all_now`: Target non-Meta atau multi-target didispatch ke `PublishSocialMediaTargetJob`       │
│ - `scheduled`: Target berstatus `scheduled` dipindai oleh scheduler tiap menit                    │
│ - Job menjalankan backoff eksponensial (3x percobaan) dan timeout 120 detik                      │
└────────────────────────────────────────────────┬──────────────────────────────────────────────────┘
                                                 │
                                                 ▼
┌───────────────────────────────────────────────────────────────────────────────────────────────────┐
│ [Node 9] Pengiriman Gateway Eksternal (External Social Media APIs)                                │
│ - Meta Graph API (v26.0): `POST /{page_id}/feed`, `POST /{ig_user_id}/media`, `POST /{id}/comments`│
│ - TikTok Content Posting API: `POST /v2/post/publish/video/init/`                                 │
│ - LinkedIn Share API: `POST /rest/posts` & Asset Upload Digital Media                             │
└────────────────────────────────────────────────┬──────────────────────────────────────────────────┘
                                                 │
                                                 ▼
┌───────────────────────────────────────────────────────────────────────────────────────────────────┐
│ [Node 10] Pencatatan Transaksional & Jejak Audit (Audit Trail & Telemetry)                        │
│ - Pembaruan `social_post_targets`: Status `published` / `failed`, `platform_post_id`, error log   │
│ - `post->syncStatusFromTargets()`: Menyelaraskan status pos induk (`published`/`partially_failed`)│
│ - `social-media:purge-published`: Membersihkan berkas fisik lokal setelah 1x24 jam publikasi     │
└────────────────────────────────────────────────┬──────────────────────────────────────────────────┘
                                                 │
                                                 ▼
┌───────────────────────────────────────────────────────────────────────────────────────────────────┐
│ [Node 11] Respons UI Real-Time & Feedback Visual (Feedback Loop)                                 │
│ - Kartu postingan menampilkan rincian target per saluran dengan tombol `[Retry]` pada yang gagal   │
│ - Lencana status real-time di tabel komentar inbox saat dibalas                                   │
│ - Pembaruan metrik analitik live pada klik tombol `[Tarik Live]`                                 │
└───────────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Rincian Alur Operasional Spesifik

### 3.1 Alur Publikasi Multi-Saluran (Omnichannel Publishing Flow)
1. Merchant membuka halaman `Posting Konten` (`/social-media/posts`) dan menekan tombol `Tulis Postingan Baru`.
2. Modal Sheet XXL terbuka menampilkan:
   - Pemilih akun saluran aktif berformat Bento (Facebook, Instagram, Threads, TikTok, LinkedIn).
   - Pemilih format media: Foto, Carousel (2–10 berkas), Video, Reels (9:16), atau Teks.
   - Media tray interaktif dengan indikator urutan, thumbnail pratinjau, dan tombol geser kiri/kanan.
   - Textarea caption utama dengan penghitung tagar unik otomatis (peringatan merah jika > 5 tagar).
   - Accordion kustomisasi caption opsional per saluran.
   - Tiga moda jadwal waktu publikasi.
3. Saat form disubmit:
   - Backend memverifikasi kuota bulanan (`entitlement:social_post`).
   - Berkas media disimpan ke direktori terisolasi tenant dan dicatat oleh `StorageTrackingService`.
   - Entitas pos induk `SocialMediaPost` dibuat berstatus `publishing`.
   - Entitas anak `SocialPostMedia` dibuat untuk masing-masing berkas media.
   - Untuk setiap saluran terpilih, dibuat record `SocialPostTarget`. Jika mode jadwal aktif, status diset `scheduled`. Jika instan, status diset `pending` dan job antrean `PublishSocialMediaTargetJob` seketika didispatch.
4. Jika salah satu target saluran gagal (misal: token Instagram kedaluwarsa sementara Facebook berhasil), status pos induk berubah menjadi `partially_failed`. Kartu postingan di UI menampilkan lencana oranye *"Sebagian Gagal"* dan menyediakan tombol mandiri `[Retry Target]` hanya pada saluran yang gagal tanpa mengirim ulang ke saluran yang sudah berhasil.

### 3.2 Alur Pembersihan Storage Otomatis (Auto-Purge Disk Preservation)
1. Untuk mencegah kepenuhan kapasitas storage disk server lokal akibat penumpukan berkas gambar dan video media sosial yang sudah terunggah ke CDN Meta/TikTok/LinkedIn, Cooca menerapkan **Siklus Pembersihan 1x24 Jam**:
2. Cron job harian `social-media:purge-published` dijalankan setiap pukul 02:00 dini hari (`routes/console.php`).
3. Sistem mencari seluruh pos berstatus `published` yang dipublikasikan lebih dari 24 jam lalu (`published_at <= now()->subDay()`).
4. Berkas fisik lokal pada storage disk dihapus tuntas, kolom `local_media_paths` dikosongkan, dan record pelacakan `StorageFile` ditandai telah dipurging, sementara URL publik eksternal dan riwayat postingan tetap utuh di database.

---

## 4. Matriks Do's & Don'ts untuk 20 Sektor Industri Bisnis

Komunikasi media sosial toko UMKM memiliki implikasi hukum, citra merek, dan efektivitas konversi yang sangat berbeda antar sektor industri. Sistem Cooca menegakkan panduan kontekstual berikut:

| Klaster Industri | Sektor Usaha | Do's (Praktik Terbaik & Wajib Terap) | Don'ts (Larangan Keras / Fraud Risk) |
|---|---|---|---|
| **1. Kuliner & F&B (5)** | • Restoran / Rumah Makan<br/>• Cafe & Coffee Shop<br/>• Bakery & Cake Shop<br/>• Cloud Kitchen<br/>• Katering & Prasmanan | • Gunakan video Reels/TikTok gerak lambat (*food appeal*) dengan pencahayaan terang.<br/>• Cantumkan CTA jelas ke link Storefront Cooca / Reservasi Meja.<br/>• Publikasikan promo 1-2 jam sebelum jam makan (10:30 lunch, 16:30 dinner).<br/>• Tampilkan sertifikasi Halal, nomor izin edar, dan transparansi bahan alergen (kacang, susu). | • **DILARANG** mempublikasikan foto makanan basi, tumpah, atau pencahayaan gelap.<br/>• Dilarang mengklaim porsi berlebihan yang jauh berbeda dari sajian kasir aktual (*misleading advertising*).<br/>• Dilarang mengabaikan komplain higienitas di komentar publik; wajib segera direspons sopan dalam 15 menit. |
| **2. Manufaktur & HPP (5)** | • Konveksi & Garment<br/>• Logam & Plastik Presisi<br/>• Mebel & Kayu<br/>• Kerajinan Tangan<br/>• Percetakan & Offset | • Unggah video proses produksi (mesin bordir, laser cutting, finishing kayu).<br/>• Manfaatkan LinkedIn untuk menampilkan kapasitas pabrik, ketepatan QC, dan sertifikasi ISO.<br/>• Jelaskan tingkatan harga grosir / maklon / tier volume pesanan.<br/>• Tampilkan testimoni kepuasan klien korporasi / B2B. | • **HARAM MUTLAK** mempublikasikan desain cetak/produk pesanan klien yang terikat perjanjian kerahasiaan (NDA breach).<br/>• Dilarang mengklaim spesifikasi bahan palsu yang tidak sesuai dengan formulasi BOM sistem Cooca.<br/>• Hindari caption bahasa slang gaul di LinkedIn B2B. |
| **3. Ritel & Apotek (2)** | • Reseller & Minimarket<br/>• Apotek & Toko Obat | • Buat konten unboxing bundle hemat, diskon gajian, dan tips belanja bijak.<br/>• Apotek: Edukasi kesehatan umum, pencegahan penyakit musiman, dan cara penyimpanan obat di rumah yang benar.<br/>• Informasikan jam operasional dan ketersediaan layanan tebus resep. | • **HARAM MUTLAK** mempromosikan obat keras (Daftar G / lingkaran merah), antibiotik, atau obat dengan resep dokter di medsos (Pelanggaran berat BPOM RI, Kemenkes & Kebijakan Meta/TikTok Healthcare — risiko pidana dan pemblokiran permanen akun!).<br/>• Dilarang mengklaim suplemen/herbal dapat "menyembuhkan kanker/diabetes seketika". |
| **4. Jasa Operasional (4)** | • Bengkel Mobil & Motor<br/>• Barbershop & Salon<br/>• Laundry Kiloan & Satuan<br/>• Auto Detailing | • Tampilkan konten *Before-After* yang kontras dan dramatis (cat kusam vs glossy ceramic coating, rambut kusut vs pompadour fade cut).<br/>• Berikan tips perawatan rutin mandiri di rumah (kapan ganti oli, cara mencuci jas wol).<br/>• Pasang stiker lokasi toko dan link reservasi WhatsApp. | • **HARAM MUTLAK** memperlihatkan plat nomor kendaraan (`nopol`) pelanggan tanpa disensor/blur (pelanggaran privasi PII & kerentanan doxxing!).<br/>• Dilarang mempublikasikan wajah pelanggan salon tanpa persetujuan eksplisit.<br/>• Dilarang menjelekkan bengkel/salon kompetitor di caption. |
| **5. Jasa Proyek (3)** | • Kontraktor Bangunan<br/>• Event Organizer & WO<br/>• Digital Agency / IT | • Tampilkan studi kasus proyek: Tantangan klien ➔ Solusi desain ➔ Hasil eksekusi fisik.<br/>• Bagikan dokumentasi *time-lapse* progres pengerjaan di lapangan.<br/>• Gunakan LinkedIn untuk menjangkau manajer pengadaan (procurement) dan direktur operasional. | • **HARAM** mempublikasikan foto pernikahan sebelum pengantin resmi memasuki venue / sebelum jam acara.<br/>• Dilarang menampilkan denah keamanan atau interior sensitif gedung klien komersial.<br/>• Dilarang mencantumkan tombol beli instan kasir ritel yang merendahkan nilai profesional jasa proyek. |
| **6. Distribusi & Agro (2)** | • Distributor FMCG<br/>• Peternakan & Pertanian | • Publikasikan keberangkatan armada logistik, kebersihan gudang pusat, dan ketersediaan tonase stok siap kirim.<br/>• Pertanian: Video panen segar, teknik hidroponik, dan uji bebas pestisida.<br/>• Buka peluang kemitraan agen / sub-distributor / grosir baru melalui Facebook dan LinkedIn. | • **HARAM** memperlihatkan hewan ternak dalam kondisi tersiksa, sakit, atau kandang kumuh (memicu sanksi *Animal Cruelty Policy* Meta/TikTok).<br/>• Dilarang memposting produk kadaluarsa atau kemasan rusak di gudang.<br/>• Dilarang mencantumkan harga grosir rahasia yang merusak harga pasar pengecer. |

---

## 5. Keamanan Cyber, Proteksi Fraud & Mitigasi Human Error

### 5.1 Perlindungan Keamanan Cyber
1. **Pencegahan SSRF (Server-Side Request Forgery) pada `media_url`:**
   - Parameter `media_url` wajib berprotokol `https://`.
   - Sistem memverifikasi DNS dan menolak resolusi IP lokal / privat (`127.0.0.1`, `10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16`, `169.254.169.254`).
   - Melakukan pra-inspeksi header HTTP `Content-Type` untuk memastikan tipe berkas sah (`image/jpeg`, `image/png`, `video/mp4`).
2. **Pemberantasan Celah DOM XSS di Kotak Masuk:**
   - Seluruh data pengirim dan pesan komentar di `inbox.blade.php` dilewatkan menggunakan helper Blade `@js()` yang aman dari injeksi kutip tunggal atau karakter pemutus skrip.
   - Peniadaan properti berbahaya `.innerHTML` pada manipulasi lencana status, digantikan dengan penataan DOM aman.
3. **Pemisahan Hak Akses RBAC (Granular Permission Scoping):**
   - Menghapus ketergantungan salah sasaran pada middleware `require.permission:whatsapp.view`.
   - Mengukuhkan permission resmi khusus: `social_media.view` untuk hak baca dasbor & kalender, dan `social_media.manage` untuk hak publikasi postingan, penautan akun, dan pemutusan integrasi.

### 5.2 Skema Fraud Internal & Mitigasi
1. **Maker-Checker pada Publikasi Konten Sensitif:**
   - Untuk mencegah sabotase akun resmi toko oleh oknum staf/kasir yang memiliki akses perangkat toko, postingan yang dibuat oleh staf non-owner berstatus `draft_pending_review` dan wajib disetujui oleh Owner sebelum diunggah ke publik.
2. **Pendeteksi Rekening Liar & Phishing (Anti-Account Hijacking):**
   - Heuristik caption memindai pola nomor rekening bank (BCA, Mandiri, BRI, BNI) dan dompet digital pada materi promosi. Jika nomor rekening tidak terdaftar di modul Akun Bank resmi Cooca, sistem menahan publikasi dan memberi peringatan fraud ke Owner.
3. **Jejak Audit Immutable Pembuat Konten:**
   - Setiap pos mencatat `user_id` pembuat asli, IP address, dan timestamp. Riwayat ini tidak dapat diubah oleh staf toko.

### 5.3 Perlindungan Human Error (Penenang Jiwa UMKM)
1. **Peringatan Jam Senyap (Quiet Hours Shield):**
   - Peringatan visual halus jika merchant memilih jam posting antara 22:00 s/d 06:00 WIB guna melindungi jangkauan algoritma dan mencegah gangguan ke pengikut di malam hari.
2. **Penghitung Tagar Otomatis (Max 5 Unique Rule):**
   - Input caption otomatis menghitung jumlah tagar unik, mengabaikan duplikasi huruf besar/kecil, dan menonaktifkan tombol submit jika melebihi 5 tagar dengan pesan panduan yang ramah.
3. **Deteksi Asimetri Aspek Rasio Media:**
   - Peringatan cerdas jika video 16:9 landscape diunggah untuk format Reels atau TikTok, menyarankan format vertikal 9:16 untuk hasil optimal.

---

## 6. Definition of Done & Standar Verifikasi

Setiap modifikasi pada modul Media Sosial wajib memenuhi checklist kelayakan:
1. `php -l` seluruh controller, service, job, command, dan view Blade bebas dari syntax error.
2. Pengujian fitur `tests/Feature/SocialMedia/` lolos 100% (52+ tests, 0 failure, 0 error).
3. Desain antarmuka mematuhi standar Bento Apple HIG:
   - Form modal berukuran XXL (`max-w-5xl` / `xl:max-w-6xl` di desktop, full-width sheet di mobile).
   - Zero emoji Unicode di antarmuka (hanya ikon Lucide `<i data-lucide="...">`).
   - Zero dialog native `alert()` / `confirm()`, beralih ke `AppAlert` frosted glass toast.
4. Token kredensial OAuth tersimpan terenkripsi AES-256 dan tidak pernah terekspos dalam teks terbuka di view Blade.
