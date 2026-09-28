# Rencana Aksi & Implementasi: Hardening Keamanan Cyber, Proteksi Fraud Pengalihan Struk POS & UI Sadar Konteks 20 Industri pada Modul WhatsApp Gateway & Broadcast

**Referensi Dokumen:** [`docs/prd/PRD-11-WHATSAPP-GATEWAY-AND-BROADCAST-MULTI-INDUSTRY.md`](file:///c:/laragon/www/cooca_core/docs/prd/PRD-11-WHATSAPP-GATEWAY-AND-BROADCAST-MULTI-INDUSTRY.md)  
**ID Dokumen:** `PLAN-11-WHATSAPP-HARDENING-ANTI-FRAUD-MULTI-INDUSTRY`  
**Penanggung Jawab:** Security Auditor, Principal Laravel Architect, UI/UX Engineer, QA Engineer  
**Status:** COMPLETED & VERIFIED 100%  

---

## 1. Ringkasan Eksekutif & Sasaran Teknis

Rencana implementasi ini dirancang untuk mengeksekusi perbaikan menyeluruh terhadap modul **WhatsApp Gateway & Broadcast Komunikasi (`resources/views/app/whatsapp`)** dan komponen backend terkait (`WhatsAppWebController`, `WhatsAppBroadcastWebController`, `MetaWhatsAppOnboardingController`, `WhatsAppGatewayService`, `WhatsAppTemplateService`, `PosTerminalWebController`).

Perbaikan ini menyelesaikan 8 masalah utama:
1. **Broken Access Control pada Tautan Struk WhatsApp Pelanggan:** Memperbaiki `public.receipt` agar dapat dibuka langsung oleh pelanggan dari WhatsApp tanpa terhadang error `Context::requireBusiness()` 403.
2. **Mitigasi Serangan Server-Side Request Forgery (SSRF):** Memvalidasi `media_url` agar menolak resolusi IP lokal (`localhost`, `127.0.0.1`), LAN privat RFC 1918, dan link-local cloud metadata (`169.254.169.254`).
3. **Pencegahan Toll Fraud & WhatsApp Bombing:** Menambahkan middleware `throttle:5,1` pada endpoint pengujian `POST /whatsapp/test` serta pembersih otomatis nomor telepon ke standar E.164.
4. **Proteksi Fraud Pengalihan Nomor Struk Kasir (Anti-Lapping Guard):** Mencatat log audit berbobot peringatan (`receipt.phone_override`) saat kasir mengganti nomor telepon penerima struk transaksi.
5. **Perlindungan Data Pribadi (PII Masking):** Menyensor 4 digit tengah nomor telepon pelanggan pada tampilan log dan penerima broadcast untuk peran non-owner.
6. **Pencegahan Duplicate Broadcast Dispatch:** Mengimplementasikan *Idempotency Key Lock* berbasis cache atomik 5 menit untuk mencegah pesan ganda saat terjadi klik berulang atau koneksi lambat.
7. **Penyatuan Antarmuka Bento Apple HIG (UI Unification):** Mengonsolidasi pembuatan broadcast ke Modal Sheet XXL di `broadcast.blade.php`, menghindari fragmentasi halaman `create.blade.php`.
8. **Personalisasi Sadar Konteks 20 Sektor Industri:** Menyediakan tag adaptif (`{nopol}`, `{no_rak}`, `{no_spk}`, `{proyek}`), peringatan jam istirahat (*Quiet Hours*), dan guardrail kepatuhan Meta Policy pada sektor farmasi/apotek.

---

## 2. Peta Fase Implementasi (5 Tahapan)

```text
┌────────────────────────────────────────────────────────────────────────┐
│ FASE 1: Hardening Keamanan Cyber & Scoping Tautan Struk Publik (P1)   │
├────────────────────────────────────────────────────────────────────────┤
│ FASE 2: Proteksi Fraud Struk Kasir, Idempotency & Masking PI (P1)      │
├────────────────────────────────────────────────────────────────────────┤
│ FASE 3: Penyatuan Antarmuka Bento Apple HIG & Modal-First XXL (P2)     │
├────────────────────────────────────────────────────────────────────────┤
│ FASE 4: Penataan Antarmuka Sadar Konteks (Context-Aware UI) 20 Sektor  │
├────────────────────────────────────────────────────────────────────────┤
│ FASE 5: Pengujian Otomatis Lolos 100%, Verifikasi & Dokumentasi (P1)   │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Rincian Teknis Eksekusi Per Fase

---

### FASE 1: Hardening Keamanan Cyber & Scoping Tautan Struk Publik (P1) - [SELESAI - VERIFIED 100%]

**Tujuan:** Memastikan tautan struk dapat dibuka oleh pelanggan umum di HP mereka tanpa error 403, menutup celah SSRF, dan mencegah toll fraud pada form tes.

#### Langkah 1.1: Pemisahan Akses Struk Publik dari Sesi Merchant
* **Berkas:** [`app/Http/Controllers/Web/Pos/PosTerminalWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Pos/PosTerminalWebController.php) & [`routes/public.php`](file:///c:/laragon/www/cooca_core/routes/public.php)
* **Tindakan:**
  - Pada method `printReceipt(PosOrder $order, Request $request)`:
    - Izinkan akses publik jika request berasal dari rute `public.receipt`:
      ```php
      // Jika request publik (dari tautan WhatsApp / QR struk), gunakan business dari order
      $business = $order->business;
      if (! $business) {
          abort(404, 'Data bisnis transaksi tidak ditemukan.');
      }
      ```
    - Jika user login sebagai staf merchant, tetap jalankan validasi multi-tenant `abort_unless($order->business_id === $currentBiz->id, 403)`.
    - Pastikan pesanan berstatus valid (`paid` atau `completed`).

#### Langkah 1.2: Validasi Anti-SSRF pada Media URL Broadcast
* **Berkas:** [`app/Http/Controllers/Web/WhatsApp/WhatsAppBroadcastWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/WhatsApp/WhatsAppBroadcastWebController.php)
* **Tindakan:**
  - Tambahkan custom validator atau helper sanitasi untuk `media_url`:
    ```php
    $url = trim($validated['media_url'] ?? '');
    if (!empty($url)) {
        $parsed = parse_url($url);
        if (($parsed['scheme'] ?? '') !== 'https') {
            throw ValidationException::withMessages(['media_url' => 'URL media wajib menggunakan protokol https:// yang aman.']);
        }
        $host = $parsed['host'] ?? '';
        $ip = gethostbyname($host);
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            throw ValidationException::withMessages(['media_url' => 'URL media tidak valid atau mengarah ke alamat jaringan lokal/privat.']);
        }
    }
    ```

#### Langkah 1.3: Rate Limiting & Normalisasi E.164 pada Pengiriman Pesan Uji Coba
* **Berkas:** [`routes/owner.php`](file:///c:/laragon/www/cooca_core/routes/owner.php) & [`app/Http/Controllers/Web/WhatsApp/WhatsAppWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/WhatsApp/WhatsAppWebController.php)
* **Tindakan:**
  - Tambahkan middleware `throttle:5,1` pada route `whatsapp.test`:
    ```php
    Route::post('/test', [WhatsAppWebController::class, 'testSend'])
        ->middleware('throttle:5,1')
        ->name('test');
    ```
  - Pada method `testSend()`, lakukan normalisasi nomor HP secara otomatis sebelum dikirim:
    ```php
    $phone = preg_replace('/[^0-9]/', '', $request->input('phone'));
    if (str_starts_with($phone, '0')) {
        $phone = '62' . substr($phone, 1);
    }
    ```

---

### FASE 2: Proteksi Fraud Struk Kasir, Idempotency & Masking PII (P1) - [SELESAI - VERIFIED 100%]

**Tujuan:** Mengamankan integritas pengiriman struk kasir, mencegah blast duplikat, dan melindungi data pribadi pelanggan.

#### Langkah 2.1: Audit Logging Pengalihan Nomor Telepon Struk Kasir
* **Berkas:** [`app/Http/Controllers/Web/WhatsApp/WhatsAppWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/WhatsApp/WhatsAppWebController.php)
* **Tindakan:**
  - Pada method `sendOrderReceipt()`:
    - Bandingkan nomor input `$request->input('phone')` dengan nomor pelanggan asli di order `$order->customer?->phone`.
    - Jika berbeda secara signifikan, catat rekaman ke tabel `audit_logs`:
      ```php
      if ($customPhone && $originalPhone && $customPhone !== $originalPhone) {
          \App\Models\AuditLog::create([
              'business_id' => $business->id,
              'user_id'     => auth()->id(),
              'action'      => 'receipt.phone_override',
              'auditable_type' => PosOrder::class,
              'auditable_id'   => $order->id,
              'payload_before' => ['phone' => $originalPhone],
              'payload_after'  => ['phone' => $customPhone],
              'reason_notes'   => 'Kasir mengalihkan nomor struk digital transaksi.',
              'ip_address'     => $request->ip(),
              'user_agent'     => $request->userAgent(),
          ]);
      }
      ```

#### Langkah 2.2: Idempotency Key Lock pada Pembuatan Broadcast
* **Berkas:** [`app/Http/Controllers/Web/WhatsApp/WhatsAppBroadcastWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/WhatsApp/WhatsAppBroadcastWebController.php)
* **Tindakan:**
  - Tambahkan lock atomik cache 300 detik pada method `store()`:
    ```php
    $idempotencyHash = md5($business->id . ':' . trim($validated['title']) . ':' . trim($validated['message']));
    $lockKey = "broadcast_lock_{$idempotencyHash}";
    if (! Cache::add($lockKey, true, 300)) {
        return back()->withErrors(['title' => 'Kampanye broadcast yang identik baru saja dijadwalkan. Mohon tunggu proses pengiriman selesai untuk mencegah pesan duplikat ke pelanggan.'])->withInput();
    }
    ```

#### Langkah 2.3: Masking PII Nomor Telepon Pelanggan pada Tabel Log
* **Berkas:** [`resources/views/app/whatsapp/broadcast_detail.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/whatsapp/broadcast_detail.blade.php) & [`resources/views/app/whatsapp/logs.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/whatsapp/logs.blade.php)
* **Tindakan:**
  - Periksa status kepemilikan user: `\App\Support\Context::isOwner()`.
  - Jika bukan Owner, tampilkan nomor tersensor:
    ```blade
    @php
        $displayPhone = \App\Support\Context::isOwner() 
            ? $item->phone_number 
            : substr($item->phone_number, 0, 4) . '••••' . substr($item->phone_number, -4);
    @endphp
    <span class="tabular-nums font-mono">{{ $displayPhone }}</span>
    ```

---

### FASE 3: Penyatuan Antarmuka Bento Apple HIG & Modal-First XXL (P2) - [SELESAI - VERIFIED 100%]

**Tujuan:** Mengeliminasi redundansi halaman `create.blade.php` dan mematangkan UX modal komposer tanpa reload/navigasi melompat.

#### Langkah 3.1: Pengalihan Anggun Route `broadcast.create` ke Modal Sheet
* **Berkas:** [`app/Http/Controllers/Web/WhatsApp/WhatsAppBroadcastWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/WhatsApp/WhatsAppBroadcastWebController.php)
* **Tindakan:**
  - Ubah method `create()` untuk mengarahkan pengguna secara elegan ke halaman indeks dengan query parameter:
    ```php
    public function create(): RedirectResponse
    {
        return redirect()->route('whatsapp.broadcast.index', ['open_composer' => 1]);
    }
    ```
  - Pada `broadcast.blade.php`, inisialisasi state Alpine.js:
    `createModalOpen: {{ request()->boolean('open_composer') ? 'true' : 'false' }}`
  - Ubah tombol di `broadcast_detail.blade.php` yang sebelumnya mengarah ke `broadcast.create` agar mengarah ke `route('whatsapp.broadcast.index', ['open_composer' => 1])`.

#### Langkah 3.2: Proteksi Double-Submit & Indikator Loading Alpine.js
* **Berkas:** [`resources/views/app/whatsapp/broadcast.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/whatsapp/broadcast.blade.php)
* **Tindakan:**
  - Tambahkan state `submitting: false` pada Alpine.js.
  - Saat tombol disubmit, ubah menjadi `submitting = true`, kunci tombol dengan `x-bind:disabled="submitting"`, tampilkan icon spinner `<i data-lucide="loader-2" class="animate-spin">`, dan ubah label teks.

---

### FASE 4: Penataan Antarmuka Sadar Konteks (*Context-Aware UI*) untuk 20 Sektor Industri (P2) - [SELESAI - VERIFIED 100%]

**Tujuan:** Memberikan pengalaman aplikasi yang relevan secara industri dan menjaga kepatuhan hukum/kebijakan Meta.

#### Langkah 4.1: Kamus Tag Personal Adaptif Per Sektor Industri
* **Berkas:** [`resources/views/app/whatsapp/broadcast.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/whatsapp/broadcast.blade.php)
* **Tindakan:**
  - Evaluasi `$business->template_code` dan tampilkan tombol chip tag personal yang relevan:
    - **F&B (`fnb_*`):** `{nama}`, `{poin}`, `{meja}`, `{bisnis}`
    - **Bengkel (`service_workshop`):** `{nama}`, `{nopol}`, `{servis_terakhir}`, `{bisnis}`
    - **Laundry (`service_laundry`):** `{nama}`, `{no_rak}`, `{berat_kg}`, `{bisnis}`
    - **Manufaktur/Garment (`mfg_*`):** `{nama}`, `{no_spk}`, `{produk}`, `{bisnis}`
    - **Kontraktor (`service_contractor`):** `{nama}`, `{proyek}`, `{termin}`, `{bisnis}`
    - **Apotek (`retail_pharmacy`):** `{nama}`, `{no_resep}`, `{bisnis}`
  - Perbarui fungsi JavaScript `updatePreview()` di Alpine.js agar mensimulasikan nilai tag adaptif sesuai sektor bisnis merchant.

#### Langkah 4.2: Peringatan Kepatuhan Meta Health Policy untuk Apotek
* **Berkas:** [`resources/views/app/whatsapp/broadcast.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/whatsapp/broadcast.blade.php)
* **Tindakan:**
  - Tampilkan banner peringatan keamanan pada komposer broadcast jika `$business->template_code === 'retail_pharmacy'`:
    ```blade
    @if ($business->template_code === 'retail_pharmacy')
        <div class="p-4 rounded-[16px] bg-[#FF9500]/12 border border-[#FF9500]/25 text-[#B25E00] dark:text-[#FF9F0A] text-[12.5px] space-y-1">
            <div class="font-bold flex items-center gap-1.5">
                <i data-lucide="shield-alert" class="w-4 h-4"></i>
                <span>Peringatan Kebijakan Farmasi Meta & BPOM:</span>
            </div>
            <p class="leading-relaxed">
                Dilarang mempromosikan obat keras (Daftar G), antibiotik, atau obat resep dokter via broadcast WhatsApp. Pelanggaran dapat mengakibatkan nomor WhatsApp toko diblokir permanen oleh Meta.
            </p>
        </div>
    @endif
    ```

#### Langkah 4.3: Deteksi Jam Istirahat (Quiet Hours Warning)
* **Berkas:** [`resources/views/app/whatsapp/broadcast.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/whatsapp/broadcast.blade.php)
* **Tindakan:**
  - Evaluasi jam waktu lokal di frontend (`new Date().getHours()`). Jika waktu saat ini antara pukul 21:00 hingga 08:00, tampilkan banner oranye lembut:
    *"Perhatian: Saat ini di luar jam operasional wajar (malam hari). Mengirim pesan massal sekarang dapat memicu komplain pelanggan dan penurunan skor kualitas nomor WhatsApp toko Anda."*

---

### FASE 5: Pengujian Otomatis Lolos 100%, Verifikasi & Dokumentasi (P1) - [SELESAI - VERIFIED 100%]

**Tujuan:** Membuktikan seluruh perbaikan lolos pengujian otomatis nyata tanpa regresi.

#### Langkah 5.1: Pengayaan Test Suite Otomatis
* **Berkas:** [`tests/Feature/WhatsApp/MerchantWhatsAppWebFeatureTest.php`](file:///c:/laragon/www/cooca_core/tests/Feature/WhatsApp/MerchantWhatsAppWebFeatureTest.php)
* **Tindakan:**
  - Tambahkan `$this->seed(\Database\Seeders\RbacSeeder::class);` di `setUp()`.
  - Tambahkan test case:
    1. `test_public_guest_can_view_receipt_without_authentication()`
    2. `test_broadcast_rejects_ssrf_media_url()`
    3. `test_whatsapp_test_endpoint_is_rate_limited()`
    4. `test_broadcast_creation_enforces_idempotency_lock()`
    5. `test_phone_override_on_receipt_records_audit_log()`
    6. `test_non_owner_sees_masked_phone_numbers_in_logs()`

#### Langkah 5.2: Verifikasi & Hardening
* **Tindakan:**
  - Jalankan `php -l` pada seluruh berkas yang disentuh.
  - Jalankan `php artisan test tests/Feature/WhatsApp/MerchantWhatsAppWebFeatureTest.php`.
  - Pastikan hasil pengujian **LOLOS 100% (0 fail, 0 error)**.

#### Langkah 5.3: Pembaruan Riwayat Kerja & Panduan Sistem
* **Berkas:** [`docs/AiWorkHistory.md`](file:///c:/laragon/www/cooca_core/docs/AiWorkHistory.md) & [`docs/SYSTEM_GUIDE.md`](file:///c:/laragon/www/cooca_core/docs/SYSTEM_GUIDE.md)
* **Tindakan:** Catat seluruh implementasi dengan 7 komponen wajib sesuai direktif operasional.
