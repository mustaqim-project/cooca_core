# Alur Kerja WhatsApp Gateway, Struk Kasir POS & Broadcast Promosi (WhatsApp Gateway & Broadcast Flow)

> **Status:** COMPLETE  
> **Domain Terkait:** `app/Domain/WhatsApp/`, `app/Domain/Pos/`, `app/Domain/Customer/`, `app/Domain/Billing/`  
> **Controller Terkait:** `WhatsAppWebController`, `WhatsAppBroadcastWebController`, `MetaWhatsAppOnboardingController`, `PosTerminalWebController`  
> **View Terkait:** `resources/views/app/whatsapp/index.blade.php`, `broadcast.blade.php`, `create.blade.php`, `broadcast_detail.blade.php`, `logs.blade.php`  
> **Job & Event:** `App\Jobs\WhatsApp\SendWhatsAppBroadcastJob`  
> **Tabel Basis Data:** `whatsapp_accounts`, `whatsapp_sessions`, `whatsapp_broadcast_campaigns`, `whatsapp_broadcast_recipients`, `whatsapp_message_logs`, `pos_orders`, `customers`

---

## 1. Ikhtisar Alur Kerja (Workflow Overview)

Modul **WhatsApp Gateway & Otomasi Komunikasi** (`resources/views/app/whatsapp`) adalah tulang punggung interaksi langsung antara merchant Cooca dan pelanggan akhir (*customer-facing communication engine*). Modul ini menjalankan 4 pilar fungsional utama:
1. **Otorisasi & Onboarding Resmi Meta Cloud API (Graph API v26.0):** Pendaftaran mandiri 1-klik (*Embedded Signup via Facebook SDK*) untuk menautkan WhatsApp Business Account (WABA) toko tanpa pembuatan aplikasi Facebook manual.
2. **Otomasi Struk Digital POS (Paperless Receipt):** Pengiriman gambar struk belanja beresolusi tinggi beserta teks sambutan personal seketika setelah kasir menyelesaikan transaksi pembayaran di terminal kasir POS.
3. **Penyusunan & Distribusi Broadcast Promosi Massal:** Segmentasi pelanggan berdasarkan tier keanggotaan loyalitas, simulator smartphone live WYSIWYG, dan pengiriman pesan massal ber-throttle melalui antrean latar belakang (*background queue worker*).
4. **Audit Trail & Log Komunikasi Terisolasi:** Pencatatan transaksional seluruh pesan terkirim, gagal, maupun tertunda lengkap dengan nomor tujuan, status delivery, dan pesan kendala sistem.

---

## 2. Diagram Rantai 11-Node Hulu-ke-Hilir (End-to-End Execution Flow)

```text
┌───────────────────────────────────────────────────────────────────────────────────────────────────┐
│ [Node 1] Aksi Pengguna / Pemicu Sistem (User Trigger)                                             │
│ - Kasir klik [Selesai Bayar] di POS (Auto-Send Struk)                                             │
│ - Owner menyusun kampanye blast di modal/form [Buat Blast Promosi]                                │
│ - Admin/Owner klik [Kirim Pesan Tes] di konsol pengujian                                          │
│ - Owner menautkan nomor toko via [Hubungkan dengan WhatsApp Resmi (1-Klik Meta)]                  │
└────────────────────────────────────────────────┬──────────────────────────────────────────────────┘
                                                 │
                                                 ▼
┌───────────────────────────────────────────────────────────────────────────────────────────────────┐
│ [Node 2] Frontend Blade UI & Alpine.js Simulator (WYSIWYG & Interaksi)                            │
│ - Live Smartphone Simulator (Dynamic Island / Notch Frame, balon chat hijau WA, centang dua)      │
│ - Real-time variable replacement preview ({nama}, {poin}, {tier}, {bisnis})                       │
│ - Segmented filter quick-switch (All, Bronze, Silver, Gold, VIP) & live counter kontak             │
│ - Status badge koneksi WABA (GREEN / YELLOW / RED quality rating & messaging tier limit)          │
└────────────────────────────────────────────────┬──────────────────────────────────────────────────┘
                                                 │
                                                 ▼
┌───────────────────────────────────────────────────────────────────────────────────────────────────┐
│ [Node 3] Lapisan Route & Middleware Keamanan (Security Gateways)                                  │
│ - `auth:web`: Verifikasi sesi login aktif merchant                                               │
│ - `business.active`: Penguncian tenant aktif                                                      │
│ - `require.permission:whatsapp.view|whatsapp.manage`: Pengamanan otorisasi peran RBAC            │
│ - `entitlement:whatsapp`: Pengecekan limitasi paket langganan SaaS (Free Solo 10 pesan/bln)       │
└────────────────────────────────────────────────┬──────────────────────────────────────────────────┘
                                                 │
                                                 ▼
┌───────────────────────────────────────────────────────────────────────────────────────────────────┐
│ [Node 4] Web Controller Dispatcher                                                                │
│ - `WhatsAppWebController`: Pengaturan struk POS, test send, dan inspeksi log                     │
│ - `WhatsAppBroadcastWebController`: Estimasi audiens, orkestrasi kampanye blast                  │
│ - `MetaWhatsAppOnboardingController`: Tukar authorization code Meta SDK ke Long-Lived Token       │
└────────────────────────────────────────────────┬──────────────────────────────────────────────────┘
                                                 │
                                                 ▼
┌───────────────────────────────────────────────────────────────────────────────────────────────────┐
│ [Node 5] Validasi Input & Sanitasi Muatan (Validation Layer)                                      │
│ - Nomor telepon dibersihkan ke format E.164 (08xxx / +62xxx -> 628xxx)                            │
│ - Panjang teks dibatasi maksimal 2.000 karakter                                                  │
│ - Media URL diverifikasi tipe scheme `https://`                                                   │
└────────────────────────────────────────────────┬──────────────────────────────────────────────────┘
                                                 │
                                                 ▼
┌───────────────────────────────────────────────────────────────────────────────────────────────────┐
│ [Node 6] Scoping Multi-Tenant & Anti-IDOR Guard                                                   │
│ - Seluruh entitas di-bind eksplisit ke `Context::requireBusiness()->id`                           │
│ - `PosOrder`, `Customer`, `WhatsAppAccount`, `WhatsAppBroadcastCampaign` wajib berpemilik sama    │
└────────────────────────────────────────────────┬──────────────────────────────────────────────────┘
                                                 │
                                                 ▼
┌───────────────────────────────────────────────────────────────────────────────────────────────────┐
│ [Node 7] Layanan Domain (Domain Service Layer)                                                    │
│ - `WhatsAppGatewayService`: Orkestrator pengiriman, anti-duplicate cache lock 60s, kuota resolver│
│ - `WhatsAppTemplateService`: Penyusun template resmi Meta (Utility Struk & Marketing Blast)       │
│ - `PosReceiptImageService`: Generator gambar struk digital PNG (58mm/80mm)                        │
└────────────────────────────────────────────────┬──────────────────────────────────────────────────┘
                                                 │
                                                 ▼
┌───────────────────────────────────────────────────────────────────────────────────────────────────┐
│ [Node 8] Antrean Asinkron Latar Belakang (Laravel Queue Offloading)                               │
│ - `SendWhatsAppBroadcastJob`: Diproses di worker background (timeout 600 detik)                   │
│ - Throttling rate-limiting 150ms per pesan untuk mematuhi Meta Cloud API Limit                    │
└────────────────────────────────────────────────┬──────────────────────────────────────────────────┘
                                                 │
                                                 ▼
┌───────────────────────────────────────────────────────────────────────────────────────────────────┐
│ [Node 9] Pengiriman Gateway Eksternal (External WhatsApp API Gateway)                             │
│ - Meta WhatsApp Cloud API (Graph API v26.0): Request HTTP POST ke `/{phone_number_id}/messages`   │
│ - Autentikasi System User Permanent Token / Long-Lived Token resmi                                │
└────────────────────────────────────────────────┬──────────────────────────────────────────────────┘
                                                 │
                                                 ▼
┌───────────────────────────────────────────────────────────────────────────────────────────────────┐
│ [Node 10] Pencatatan Transaksional & Jejak Audit (Audit Trail & Telemetry)                        │
│ - `whatsapp_message_logs`: Menyimpan status `sent` / `failed`, error message, & recipient phone  │
│ - `whatsapp_broadcast_recipients`: Status detail per penerima kampanye                           │
│ - `QuotaMonthlyUsage`: Increment kuota bulanan tenant                                            │
└────────────────────────────────────────────────┬──────────────────────────────────────────────────┘
                                                 │
                                                 ▼
┌───────────────────────────────────────────────────────────────────────────────────────────────────┐
│ [Node 11] Respons UI Real-Time & Feedback Visual (Feedback Loop)                                 │
│ - Polling live status kampanye (`/whatsapp/broadcast/{id}` via JSON) setiap 3 detik              │
│ - Progress counter: Total Target, Berhasil Terkirim, Gagal Terkirim, Tingkat Sukses (%)          │
│ - Tombol Fallback Manual: Buka WhatsApp Web / HP (`https://wa.me/...`) jika nomor tujuan bermasalah│
└───────────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Rincian Alur Operasional Spesifik

### 3.1 Alur Pengiriman Struk Kasir POS Otomatis
1. Kasir memproses pembayaran pesanan di terminal kasir POS (`PosTerminalWebController@charge`).
2. Setelah transaksi sukses, modal struk kasir terbuka. Jika pelanggan memiliki nomor telepon dan sakelar `auto_send_receipt` bernilai aktif (`true`), sistem memanggil endpoint `POST /whatsapp/orders/{order}/receipt`.
3. `WhatsAppGatewayService@sendReceipt` melakukan pemeriksaan berlapis:
   - **Atomic Cache Lock:** Kunci cache `wa_receipt_sending_{order_id}` aktif selama 15 detik untuk mencegah pengiriman ganda akibat klik ganda kasir (*anti double-click*).
   - **Anti-Duplicate Window:** Memeriksa apakah struk pesanan yang sama sudah berstatus `sent` dalam 60 detik terakhir.
   - **Receipt Image Rendering:** `PosReceiptImageService` membuat gambar PNG struk belanja dan mengunggahnya ke storage publik.
   - **Message Construction:** Menggabungkan teks salam personal (`Halo Kak *{customer_name}*`), tautan struk digital (`https://.../receipt/{order}`), dan catatan kaki toko (`receipt_template`).
   - **Meta Dispatch:** Mengirim pesan gambar dan teks melalui `WhatsAppClient`.
   - **Log Pencatatan:** Menyimpan log ke tabel `whatsapp_message_logs` dengan tipe `receipt`.

### 3.2 Alur Pembuatan & Eksekusi Broadcast Promosi
1. Merchant membuka menu `Blast Promosi` (`/whatsapp/broadcast`) dan menekan tombol `Buat Blast Promosi Baru`.
2. Modal Sheet XXL terbuka menampilkan formulir di sisi kiri dan Live Smartphone Simulator di sisi kanan.
3. Merchant memilih filter target audiens:
   - `all`: Seluruh pelanggan aktif yang memiliki nomor telepon valid.
   - `bronze`, `silver`, `gold`, `vip`: Pelanggan yang berada di tier loyalitas tertentu.
4. Live simulator secara reaktif mengganti tag `{nama}`, `{poin}`, `{tier}`, `{bisnis}` dengan data simulasi pelanggan contoh.
5. Saat tombol `Kirim Blast` ditekan, prompt konfirmasi muncul meminta kepastian jumlah penerima.
6. Backend `WhatsAppBroadcastWebController@store` memvalidasi input, membuat entitas `WhatsAppBroadcastCampaign` berstatus `processing`, dan mendispatch job `SendWhatsAppBroadcastJob` ke antrean latar belakang.
7. Worker mengeksekusi pengiriman satu per satu dengan jeda aman 150ms per nomor (*throttling*).
8. Merchant diarahkan ke halaman detail kampanye (`broadcast_detail.blade.php`) yang melakukan polling berkala hingga status berganti menjadi `completed`.

---

## 4. Matriks Do's & Don'ts untuk 20 Sektor Industri Bisnis

Sistem WhatsApp Cooca melayani 20 sektor bisnis UMKM di 6 klaster industri. Gaya komunikasi, jenis pesan, dan pembatasan aturan wajib disesuaikan secara sadar konteks (*Context-Aware*):

| Klaster Industri | Sektor Usaha | Do's (Praktik Terbaik & Wajib Terap) | Don'ts (Larangan Keras / Haram) |
|---|---|---|---|
| **1. Kuliner & F&B (5)** | • Restoran / Rumah Makan<br/>• Cafe & Coffee Shop<br/>• Bakery & Cake Shop<br/>• Cloud Kitchen<br/>• Katering & Prasmanan | • Tampilkan rincian pesanan, nomor meja dine-in / take-away pada struk.<br/>• Blast promo saat jam santai (10:00–11:30 atau 15:30–17:00).<br/>• Sediakan tombol tautan pelacakan kurir online delivery.<br/>• Gunakan tag personal `{nama}` dan `{poin}` loyalitas. | • **DILARANG** mengirim blast promosi di atas pukul 21:00 atau sebelum 08:00 WIB.<br/>• Dilarang mengirim blast menu tanpa foto banner yang menggugah selera.<br/>• Dilarang mengirim struk tanpa detail item makanan yang dipesan. |
| **2. Manufaktur & HPP (5)** | • Konveksi & Garment<br/>• Logam & Plastik Presisi<br/>• Mebel & Kayu<br/>• Kerajinan Tangan<br/>• Percetakan & Offset | • Notifikasi Surat Perintah Kerja (SPK) & Proofing Desain selesai.<br/>• Pemberitahuan barang jadi siap kirim/ambil.<br/>• Pengiriman faktur invoice termin pembayaran (Down Payment / Pelunasan).<br/>• Cantumkan nomor PO/SPK pelanggan di setiap pesan. | • **HARAM** mengirim blast promosi ritel generik bertuliskan "Tunjukkan pesan ini ke kasir" (tidak relevan dengan B2B/maklon).<br/>• Dilarang mengirim pesan tanpa konfirmasi spesifikasi pesanan. |
| **3. Ritel & Apotek (2)** | • Reseller & Minimarket<br/>• Apotek & Toko Obat | • Struk kasir digital lengkap dengan nomor batch & masa kadaluarsa (Apotek).<br/>• Notifikasi tebus obat racikan siap diambil.<br/>• Pengingat pengobatan rutin (Refill Reminder H-3 sebelum obat habis). | • **HARAM MUTLAK** mempromosikan obat keras (Daftar G), antibiotik, atau obat resep via blast promosi (Melanggar Meta Health Policy & Regulasi BPOM RI - risiko sanksi hukum dan ban permanen WABA!). |
| **4. Jasa Operasional (4)** | • Bengkel Mobil & Motor<br/>• Barbershop & Salon<br/>• Laundry Kiloan & Satuan<br/>• Auto Detailing | • Bengkel: Notifikasi kendaraan selesai diservis, rincian penggantian sparepart, dan Pengingat Servis Berkala (Periodic Reminder H-7 ganti oli).<br/>• Laundry: Notifikasi cucian selesai dicuci/disetrika dengan nomor rak simpan & berat kiloan.<br/>• Salon: Pengingat jadwal appointment / reservasi kapster. | • **HARAM** mengirim pesan servis bengkel tanpa nomor polisi kendaraan (`{nopol}`).<br/>• Laundry: Dilarang mengirim pesan siap ambil tanpa kode nota / nomor rak penyimpanan fisik.<br/>• Dilarang mengirim blast massal tanpa opsi unsubscribe. |
| **5. Jasa Proyek (3)** | • Kontraktor Bangunan<br/>• Event Organizer & WO<br/>• Digital Agency / IT | • Update laporan mingguan progres fisik proyek.<br/>• Notifikasi tagihan termin invoice & Berita Acara Serah Terima (BAST).<br/>• WO: Notifikasi konfirmasi rundown & briefing keluarga/panitia. | • **HARAM** memunculkan istilah struk kasir ritel "kasir POS" pada bisnis kontraktor/agency.<br/>• Dilarang mengirim pesan promosi massal acak ke klien korporasi B2B. |
| **6. Distribusi & Agro (2)** | • Distributor FMCG<br/>• Peternakan & Pertanian | • Konfirmasi Delivery Order (DO) armada truk berangkat.<br/>• Salinan Surat Jalan digital dan rincian karton barang.<br/>• Rekap tagihan piutang toko (Statement of Account) jatuh tempo. | • **HARAM** mengirim pesan berformat kasir meja restoran dine-in.<br/>• Dilarang mengirim pesan tanpa nomor faktur penjualan resmi. |

---

## 5. Keamanan Cyber, Proteksi Fraud & Mitigasi Human Error

### 5.1 Celah Keamanan Cyber & Mitigasi
1. **SSRF Guard pada Media Banner:**
   - Parameter `media_url` pada broadcast promosi divalidasi ketat: wajib berawalan `https://`, menolak resolusi IP lokal (`localhost`, `127.0.0.1`, `10.x.x.x`, `192.168.x.x`, `169.254.169.254`), dan hanya menerima tipe konten gambar (`image/jpeg`, `image/png`, `image/webp`).
2. **Anti-Toll Fraud & Rate Limiting pada Konsol Uji Coba:**
   - Endpoint `POST /whatsapp/test` wajib dikawal middleware `throttle:5,1` (maksimal 5 pesan per menit per user) untuk mencegah penyalahgunaan SMS/WhatsApp bombing ke nomor pihak ketiga.
3. **Penyembunyian Data Pribadi Pelanggan (PII Data Protection):**
   - Nomor telepon pelanggan pada tampilan tabel audit log penerima broadcast (`broadcast_detail.blade.php`) dimasking secara parsial (contoh: `0812••••7890`) bagi staf non-owner untuk mencegah pencurian database kontak pelanggan.

### 5.2 Skema Fraud Internal & Mitigasi
1. **Pencegahan Pengalihan Struk Digital (Receipt Redirection Fraud):**
   - Saat kasir mengirim struk via WhatsApp dari terminal kasir, nomor tujuan terkunci secara baku ke nomor pelanggan terdaftar. Jika kasir mengubah nomor telepon secara manual ke nomor lain, aksi tersebut dicatat ke `audit_logs` dengan flag `suspicious_receipt_redirect`.
2. **Pencegahan Phishing & Rekening Palsu pada Broadcast:**
   - Sistem melakukan pemindaian kata kunci (*keyword heuristic analysis*) pada pesan broadcast sebelum dijadwalkan: memblokir pesan yang mencantumkan nomor rekening bank yang berbeda dari nomor rekening resmi yang terdaftar di sistem keuangan Cooca.

### 5.3 Perlindungan Human Error (Penenang Jiwa UMKM)
1. **Pemberitahuan Jam Istirahat (Quiet Hours Warning):**
   - Jika merchant menjadwalkan broadcast antara pukul 21:00 hingga 08:00 WIB, muncul modal peringatan: *"Pesan akan dikirim pada malam hari/dini hari. Pelanggan mungkin merasa terganggu dan menandai nomor toko Anda sebagai SPAM. Disarankan menjadwalkan pada jam 09:00 WIB."*
2. **Pembersih Otomatis Format Nomor HP:**
   - Input nomor telepon pada form uji coba otomatis menghapus karakter spasi, tanda strip, atau awalan `0` menjadi format standar internasional `628...` tanpa memicu error validasi yang membingungkan.
3. **Pencegahan Double-Submit pada Modal Form:**
   - Seluruh tombol submit di modal komposer broadcast dilengkapi state Alpine.js `x-bind:disabled="submitting"`, menampilkan spinner animasi, dan mengubah teks menjadi *"Sedang Menjadwalkan..."* saat diklik.

---

## 6. Definition of Done & Verifikasi Pengujian

Setiap modifikasi atau penambahan pada alur WhatsApp wajib memenuhi kriteria berikut:
1. `php -l` seluruh controller, job, dan view Blade terkait bebas dari syntax error.
2. Pengujian fitur `tests/Feature/WhatsApp/MerchantWhatsAppWebFeatureTest.php` lolos 100% (0 fail, 0 error).
3. Pengujian isolasi multi-tenant (IDOR check) memastikan merchant A tidak dapat melihat log atau kampanye merchant B.
4. Integrasi Meta Cloud API tidak mengekspos access token secara terbuka di HTML/Blade view.
