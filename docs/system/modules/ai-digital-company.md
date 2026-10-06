# Modul AI Digital Company (Autonomous Workforce & Executive Hierarchy)

> **Status:** COMPLETE  
> **Domain Terkait:** `app/Domain/Ai/`  
> **Tabel Basis Data:** `ai_provider_configs`, `ai_tasks`, `ai_action_proposals`, `ai_work_histories`  
> **Antarmuka Pengguna:** `resources/views/app/ai/` (`office.blade.php`, `actions.blade.php`, `history.blade.php`, `providers.blade.php`)  
> **Perintah CLI:** `php artisan cooca:ai-daily-business-check`

---

## 1. Tujuan & Nilai Bisnis

Modul **COOCA AI Digital Company** mentransformasikan kecerdasan buatan dari sekadar asisten percakapan teks pasif menjadi **angkatan kerja digital otonom (*Digital Workforce*)** yang bekerja terstruktur untuk memajukan bisnis UMKM dan perusahaan pengguna COOCA.

Dengan paradigma **"LLM may reason, but Laravel remains the authority"**, modul ini menghadirkan perpaduan antara kecerdasan penalaran model bahasa besar dengan keandalan, determinisme, dan keamanan ketat ekosistem Laravel. Manusia (Owner/Supervisor) tetap menjadi pemegang keputusan akhir (*Maker-Checker Gate*) sebelum data operasional bermutasi.

---

## 2. Struktur Organisasi & 3 Lingkungan Kantor Digital (3 Distinct AI Offices)

COOCA AI beroperasi sebagai satu organisasi digital tunggal yang memiliki 3 lingkungan kantor kerja terpadu:

```text
                    BUSINESS OWNER (Human Director)
                                 │
                                 ▼
                         ┌───────────────┐
                         │    AI CEO      │
                         └───────┬───────┘
                                 │
          ┌──────────────────────┼──────────────────────┐
          │                      │                      │
          ▼                      ▼                      ▼
   EXECUTIVE OFFICE      OPERATIONS OFFICE       GROWTH OFFICE
   (/cooca-ai/office/     (/cooca-ai/office/     (/cooca-ai/office/
       executive)            operations)               growth)
          │                      │                      │
          ▼                      ▼                      ▼
      Strategy               Operations               Growth
      Finance                Inventory               Marketing
      Reporting              Purchasing                Sales
      Business              Marketplace               Content
                                                   Social Media
                                                     Customer
```

### 2.1 Office 01 — Executive Office (`/cooca-ai/office/executive`)
- **Pimpinan:** AI CEO (Chief Executive Officer) & AI CFO (Chief Financial Officer).
- **Agen Spesialis:**
  - `Business Agent`: Analisis makro kesehatan bisnis, deteksi deviasi kinerja, dan diagnosa prioritas strategis.
  - `Finance Agent`: Pemantauan arus kas (cashflow), laba kotor, margin bersih, dan deteksi anomali finansial.
  - `Reporting Agent`: Penyusunan rekapitulasi harian, mingguan, bulanan, dan ringkasan eksekutif.
- **Visual Metaphor:** Ruang komando eksekutif modern enterprise (Apple HIG Bento) dengan widget KPI `tabular-nums`, kartu prioritas strategis, dan eskalasi risiko.

### 2.2 Office 02 — Operations Office (`/cooca-ai/office/operations`)
- **Pimpinan:** AI COO (Chief Operating Officer) — *"Daily operations under control"*.
- **Agen Spesialis:**
  - `Inventory Agent`: Pemantauan stok gudang harian, peringatan batas reorder point (ROP), dan dead-stock.
  - `Purchasing Agent`: Analisis harga dan performa pemasok, rekomendasi timing pengadaan, dan persiapan draf PO.
  - `Marketplace Agent`: Monitoring pesanan online, sinkronisasi stok dan harga multi-channel (Shopee, Tokopedia, TikTok Shop).
- **Visual Metaphor:** Ruang kontrol operasional modern dengan indikator stok kritis, metrik katalog, dan antrean PO.

### 2.3 Office 03 — Growth & People Office (`/cooca-ai/office/growth`)
- **Pimpinan:** AI CMO (Chief Marketing Officer), AI Sales Director, & AI HR Lead.
- **Agen Spesialis:**
  - `Marketing Agent`: Evaluasi efektivitas diskon/promosi dan perumusan kampanye pemasaran.
  - `Content Agent`: Copywriting promosi, naskah caption media sosial, dan ide konten berkala.
  - `Social Media Agent`: Penjadwalan publikasi postingan, manajemen kanal medsos, dan tracking tayang.
  - `Sales Agent`: Analisis produk terlaris vs anjlok, deviasi target cabang, dan tren pendapatan.
  - `Customer Agent`: Segmentasi RFM (VIP vs Dormant) dan perumusan pesan broadcast reaktivasi.
  - `HR Agent`: Rekapitulasi absensi kehadiran staf harian, kedisiplinan shift kasir POS, dan rasio kehadiran.
- **Visual Metaphor:** Ruang kerja kreatif, komersial, dan SDM modern dengan antrean kampanye promosi, analitik pelanggan, dan pemantauan jam kerja staf.

### 2.4 Struktur 18 AI Agent (6 Eksekutif + 12 Spesialis) & Pemetaan AI Tools
Sistem menyediakan routing deterministik presisi tinggi melalui `CompanyHierarchy::routeTopicToTeam()` dan `AiToolRegistry`:
1. **6 Eksekutif C-Level:**
   - `AI CEO` (Direktur Utama / Strategic Suite)
   - `AI CFO` (Direktur Keuangan / Financial Health)
   - `AI COO` (Direktur Operasional / Supply Chain & Inventory)
   - `AI CMO` (Direktur Pemasaran / Campaign & Branding)
   - `AI Sales Director` (Direktur Penjualan / Pipeline & Customer Retention)
   - `AI HR Lead` (Pimpinan SDM / Workforce & Attendance Governance)
2. **12 Agen Spesialis & Pemetaan Tool:**
   - `Business Agent`: `GetSalesSummary`, `GetStockLevels`, `GetFinancialHealth`, `DetectAnomaliesAndFraud`, `GetTopProducts`, `GetCustomerSummary`.
   - `Sales Agent`: `GetSalesSummary`, `GetSalesTrend`, `GetTopProducts`, `DraftInvoiceProposal`.
   - `Customer Agent`: `GetCustomerSummary`, `GetSalesSummary`.
   - `Inventory Agent`: `GetStockLevels`, `GetTopProducts`.
   - `Purchasing Agent`: `GetStockLevels`, `DraftPurchaseOrderProposal`.
   - `Marketplace Agent`: `GetSalesSummary`, `GetStockLevels`, `GetTopProducts`.
   - `Finance Agent`: `GetFinancialHealth`, `DetectAnomaliesAndFraud`, `GetSalesSummary`.
   - `Reporting Agent`: `GetSalesSummary`, `GetFinancialHealth`, `GetTopProducts`, `GetStockLevels`.
   - `Marketing Agent`: `GetCustomerSummary`, `GetTopProducts`, `DraftMarketingCampaignProposal`.
   - `Content Agent`: `GetTopProducts`, `DraftSocialPostProposal`.
   - `Social Media Agent`: `GetTopProducts`, `DraftSocialPostProposal`.
   - `HR Agent`: `GetAttendanceSummary` (membaca real database `attendances` & register shift kasir POS).

### 2.5 Main AI Office Entry / Lobby (`/cooca-ai`)
- Gerbang komando utama perusahaan digital yang menampilkan:
  - 3 Kartu Bento Ruang Kantor dengan metrik status live dari database (jumlah agen aktif, tugas aktif, usulan pending, dan indikator kesehatan kantor).
  - *Cross-Office Collaboration Timeline Widget*: Memvisualisasikan alur kerja nyata antar departemen (misal: Sales mendeteksi penurunan omzet → Business Agent menganalisis akar masalah → AI CEO mengarahkan prioritas → AI CMO merumuskan promo → Content & Marketing menyiapkan draf materi → Owner menyetujui → Social Media Agent mengeksekusi tayang).
  - Quick Action banner usulan aksi pending yang memerlukan persetujuan pemilik usaha.

### 2.5 3D Virtual Office & Office Facilities Engine (Pengalaman "Watch My AI Agents Work Now")
- **Komponen Master:** [`resources/views/app/ai/partials/virtual_office_canvas.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/ai/partials/virtual_office_canvas.blade.php) & Engine 3D [`public/js/ai/virtual-office-3d.js`](file:///c:/laragon/www/cooca_core/public/js/ai/virtual-office-3d.js).
- **Konsep & Arsitektur 3D:**
  - Terinspirasi oleh konsep *Pixel Agents* (Nate Herk), menghadirkan visualisasi ruangan kantor 3D penuh berbasis WebGL (Three.js r128 + OrbitControls) dengan performa 60 FPS stabil tanpa aset file eksternal yang membebani jaringan.
  - **Karakter Humanoid 3D ("Karakter 3D Langsung"):**
    - Avatar 3D proporsional lengkap dengan kepala, gaya rambut, kacamata/visor, pakaian divisi kerja, dan lengan berartikulasi.
    - Kursi kerja swivel 3D yang berayun lembut saat agen bekerja.
    - Animasi ketik keyboard mekanik dinamis saat `WORKING`, pose kontemplasi saat `ANALYZING`, cincin dan halo oranye berdenyut saat `WAITING_APPROVAL`, dan nafas rileks saat `IDLE`.
    - 3D Billboard Sprite mengambang di atas kepala agen menampilkan Nama Role, status kerja, dan *floating thought bubble* dengan ringkasan tugas riil.
  - **Fasilitas Kantor 3D Lengkap ("Fasilitas Kantor 3D"):**
    1. **Pantry & Coffee Bar Station:** Meja kitchen island kayu & marmer, mesin espresso chrome bertekanan dengan cangkir kopi, toples kaca, dan bar stools.
    2. **Water Cooler Dispenser:** Unit dispenser berdiri dengan galon air biru transparan, kran dingin/panas, dan gelembung air beranimasi naik ke atas botol.
    3. **Lounge & Breakout Area:** Sofa sudut L kontemporer dengan bantal aksen, meja kopi bundar Skandinavia dengan laptop dan majalah, karpet rajut bundar, serta floor arc lamp hangat.
    4. **Executive Boardroom / Meeting Facility:** Meja rapat oval besar berlapis walnut, 6 kursi jaring ergonomis, dan proyektor hologram data di tengah meja.
    5. **AI Core Server Room:** 3 rak server 42U dengan pintu perforated mesh dan 24 LED berkedip multi-warna secara dinamis, serta ventilasi pendingin berpenghangat biru.
    6. **Logistics & Pallet Bay:** Palet kayu industri dengan tumpukan kardus pengiriman berstiker "FRAGILE" dan barcode label.
    7. **Biophilic Greenery:** Tanaman 3D Monstera, Snake Plant, dan Ficus dalam pot keramik & terakota.
    8. **Bookshelves & Trophy Vitrine:** Rak buku kayu tinggi dengan buku warna-warni dan piala emas kejuaraan.
  - **Kontrol Interaktif & Navigasi:**
    - Full 360° OrbitControls (rotasi, pan, zoom).
    - 6x Camera Presets: Overview, Direksi, Operasional, Growth, Pantry/Lounge, dan Server.
    - Raycasting Mouse Click: Mengklik meja agen menggerakkan kamera secara halus (*smooth lerp*) mendekati meja agen dan membuka Desk Inspector Console untuk konsultasi 1-klik (*"Tugaskan / Bicara"*).
    - Switcher Suasana Siang / Malam Cyberpunk (Sunlight vs Moody Neon & LED Glow).
    - Switcher 3 Mode: `[🪐 3D Virtual Office Floor]` / `[📐 2D Floor Plan Blueprint]` / `[📊 Bento Analytics Grid]`.

### 2.6 Dual-Monitor Data Bisnis Nyata (Per Bidang 17 Agen di Canvas 3D)
- Setiap meja kerja 17 agen di Virtual Office 3D dilengkapi **dua monitor interaktif beresolusi tinggi (512x256)** yang merender data nyata dari database tenant aktif (`Context::requireBusiness()`):
  - **Monitor 1 (Strategic KPI & Trend Chart):** Metrik agregat finansial/operasional (omzet MTD, kas likuid, status ROP, persentase margin) dan visualisasi chart 7 hari (bar chart / line chart dinamis).
  - **Monitor 2 (Live Operational Feed & Data Real):** Feed tabel live 3 kolom berisi rekod operasional faktual (5 transaksi kasir terakhir, 5 faktur piutang jatuh tempo, watchlist produk di bawah batas aman ROP, log audit keamanan berisiko tinggi).
- **Auto-Polling Background 30 Detik:** Script [`public/js/ai/virtual-office-3d.js`](file:///c:/laragon/www/cooca_core/public/js/ai/virtual-office-3d.js) secara berkala mengambil data mutakhir via `GET /cooca-ai/live-metrics` tanpa perlu reload halaman (`location.reload()` dilarang) dan memperbarui tekstur canvas secara mulus.

---

## 3. Sistem RAG (Retrieval-Augmented Generation) Multi-Tenant

Terletak di namespace `app/Domain/Ai/Rag/`:
1. **`TenantMasterDataKnowledgeSource`**: Mengambil metadata produk (SKU, nama, harga jual, base cost/HPP, safety stock, stok riil via `stocks`), supplier aktif, pelanggan terdaftar, dan rekening kas/bank aktif. Query terisolasi secara mutlak (`where business_id = $business->id`).
2. **`TenantOperationalHistoryKnowledgeSource`**: Mengindeks histori faktur (`Invoice`), pesanan kasir (`PosOrder`), pengadaan (`PurchaseOrder`), mutasi kas (`CashTransaction`), dan catatan audit (`AuditLog`).
3. **`UmkmRegulationsKnowledgeSource`**: Pengetahuan regulasi dan kepatuhan hukum UMKM Indonesia:
   - Tarif PPh Final UMKM PP 55/2022 (0,5% batas omzet Rp 500 Juta untuk Orang Pribadi).
   - Skema PPh 21 TER PP 58/2023 (Kategori A, B, C).
   - Tarif PPN 11% Faktur Pajak.
   - SOP Kasir Anti-Fraud (Blind Cash Count, otorisasi supervisor untuk void, batas toleransi selisih kas Rp 10.000).
   - Rumus matematika Safety Stock dan Reorder Point (ROP = Lead Time Demand + Safety Stock).
4. **`AiSystemCapabilitiesKnowledgeSource`**: Katalog tool AI terdaftar, skema input JSON (`getInputSchema()`), serta kebijakan Maker-Checker.
5. **`AiRagRetriever`**: Menggabungkan seluruh sumber pengetahuan, melakukan pembobotan skor relevansi, menyaring berdasarkan ambang batas (*threshold*), dan menyusun blok sitasi:
   `=== FAKTA & DOKUMEN SISTEM TERVERIFIKASI (RAG GROUNDING) ===`

---

## 4. Context Engineering Pipeline 5-Layer & 17 Role Directives

Terletak di namespace `app/Domain/Ai/Context/`:
1. **`AgentRoleDirectives`**: Pustaka instruksi kognitif mendalam yang mendefinisikan persona, misi utama, KPI terukur, rumus matematis, batasan wewenang (*boundary constraints*), dan SOP untuk seluruh 17 peran agen/eksekutif.
2. **`AiContextEngine`**: Merakit prompt sistem 5-layer:
   - **Layer 1: Identity & Meta-Directives:** Integritas sistem, larangan kebocoran data rahasia, maker-checker.
   - **Layer 2: Agent Role Directives & KPI Desk:** Misi peran spesifik yang dipanggil sesuai routing tugas.
   - **Layer 3: Tenant Business Profile:** Skala usaha (UMKM), profil tenant aktif, mata uang IDR.
   - **Layer 4: RAG Factual Knowledge Injection:** Fakta sistem yang disematkan secara dinamis berdasarkan pencarian kata kunci query.
   - **Layer 5: Anti-Hallucination Guardrails & Token Budgeting:** Kewajiban mencantumkan sitasi sumber (misal `[Katalog Produk: #KGA-001]`). Jika data tidak ada dalam fakta terverifikasi, AI diwajibkan menjawab tegas: *"Data tidak ditemukan dalam sistem"*. Dilengkapi estimasi anggaran token dinamis untuk efisiensi biaya LLM.

---

## 5. Status Backend Nyata (No Fake Animations)

Setiap agen dan kantor merefleksikan status siklus hidup nyata berbasis baris data `ai_tasks` dan `ai_action_proposals`:
- `IDLE`: Siaga dan tersedia.
- `WORKING` / `ANALYZING`: Sedang memproses komputasi atau mengeksekusi alat bantu (tool).
- `WAITING_APPROVAL`: Telah merumuskan proposal aksi dan menunggu konfirmasi pemilik usaha.
- `COMPLETED`: Tugas selesai sukses dalam 6 jam terakhir.
- `FAILED`: Memerlukan perhatian teknis akibat kegagalan tugas.

Dilarang keras menggunakan animasi mengetik palsu atau avatar kartun yang mengelabui pengguna. Prinsip utama: **"SHOW AI WORKING, NOT AI THINKING"**.

---

## 6. Pilar Arsitektur & Keamanan Enterprise

1. **Maker-Checker Action Gate & Eksekutor Multi-Aksi (`AiActionExecutor`):**
   AI tidak pernah menulis langsung ke tabel operasional tanpa otorisasi. Seluruh aksi diajukan sebagai proposal di tabel `ai_action_proposals` dengan status `PENDING` untuk ditinjau oleh pemegang wewenang di Action Center (`/cooca-ai/actions`). Eksekusi aksi mendukung seluruh spectrum kebutuhan bisnis:
   - **Sales & Billing:** `create_invoice`, `create_quotation`.
   - **Procurement:** `create_purchase_order`.
   - **Marketing & Growth:** `launch_marketing_campaign`, `publish_social_post`, `revenue_acceleration`, `revenue_growth_initiative`.
   - **Finance & Cost Control:** `cost_structure_audit`, `cost_audit_and_optimization`, `financial_audit` (menghitung break-even, riwayat OPEX riil vs POS, dan roadmap efisiensi biaya).
   - **Tech & Data Ops:** `system_maintenance`, `data_integrity_check`, `database_optimization`.
   - **Strategic Fallback:** `strategic_directive` (penerapan direktif strategis dengan pencatatan audit log tanpa resiko fatal unhandled exception).
2. **Idempotensi & Transaksi Atomik:**
   Setiap proposal dilengkapi `idempotency_key` (`ACT-xxxx`). Proposal yang telah dieksekusi dikunci agar tidak dapat dieksekusi ganda jika tombol ditekan berkali-kali. Eksekusi dilakukan di dalam `DB::transaction` atomik dengan re-validasi status data fisik.
3. **Isolasi Multi-Tenant Mutlak:**
   Setiap tabel AI memegang kolom `business_id` berindeks. Context tenant terisolasi penuh; tenant lain tidak dapat membaca proposal, memicu tugas, atau mengintip kunci provider milik tenant lain.
4. **Kriptografi API Key (AES-256-CBC):**
   Kunci API penyedia AI dienkripsi saat tersimpan di database (`$casts = ['api_key' => 'encrypted']`), disembunyikan dari array/JSON serialisasi (`$hidden = ['api_key']`), dan ditampilkan bertopeng (`••••••••`).
5. **Multi-Provider BYOAI (Bring Your Own AI):**
   Mendukung integrasi langsung ke OpenAI, Google Gemini, Anthropic Claude, OpenRouter, serta **Rule-Based Fallback Engine (100% Offline)** tanpa ketergantungan API pihak ketiga.
6. **Zero External Dependency & Zero Native Dialogs:**
   100% Native PHP/Laravel tanpa Node.js microservice. Seluruh interaksi UI mengeliminasi dialog browser `alert()` dan `confirm()` ke suite modal Apple HIG `AppAlert` (`AppAlert.confirm`, `AppAlert.success`, `AppAlert.error`).

---

## 7. Rute & Antarmuka Bento Apple HIG

- `GET /cooca-ai`: Lobi Utama Komando Digital (Overview 3 kantor, metrik agregat, timeline kolaborasi).
- `GET /cooca-ai/office/executive`: Executive Office (AI CEO & CFO, KPI omzet/kas/margin/piutang).
- `GET /cooca-ai/office/operations`: Operations Office (AI COO, peringatan stok kritis ROP, pengadaan & marketplace).
- `GET /cooca-ai/office/growth`: Growth Office (AI CMO & Sales Director, produk terlaris, konten & pelanggan).
- `GET /cooca-ai/live-metrics`: Endpoint JSON real-time metrik dual monitor 17 agen (terproteksi auth & entitlement AI).
- `GET /cooca-ai/actions`: Action Center (Daftar proposal maker-checker pending, disetujui, ditolak).
- `POST /cooca-ai/actions/{proposal}/approve`: Endpoint eksekusi persetujuan proposal.
- `POST /cooca-ai/actions/{proposal}/reject`: Endpoint penolakan proposal dengan alasan.
- `GET /cooca-ai/history`: Riwayat komputasi, durasi tugas, dan log audit agen.
- `GET /cooca-ai/providers`: Manajemen kunci API multi-vendor terenkripsi AES-256 (BYOAI) dilengkapi tautan langsung ke portal resmi konsol pengembang (OpenAI Platform, Google AI Studio, Anthropic Console, OpenRouter).
- `POST /cooca-ai/providers`: Menyimpan konfigurasi provider, enkripsi kunci API, dan pengaturan default provider.
- `POST /cooca-ai/providers/test`: Pengujian latensi koneksi (ms), verifikasi handshake, dan auto-fallback model aktif.
- `POST /cooca-ai/providers/detect-models`: Penemuan model dinamis (*Dynamic Model Discovery*) via endpoint resmi vendor (`GET /v1/models`).
- `POST /cooca-ai/ask`: Endpoint Pusat Chat AI Virtual Office (Gambar 1) multi-turn conversation, selector agen (CFO, CEO, COO, CMO, Sales, HR), chip prompt rekomendasi kontekstual, penyimpanan riwayat sesi berbasis `localStorage`, dan penegakan BYOAI (otomatis redirect ke `/cooca-ai/providers` jika provider belum dikonfigurasi).
- `POST /cooca-ai/daily-check`: Pemicu manual evaluasi kesehatan bisnis harian oleh AI CEO.
- `GET /ai` & `GET /ai/office`: Redirect otomatis ke `/cooca-ai` untuk kompatibilitas mundur.

---

## 8. Penghapusan Sistem Top Up Token & Penegakan BYOAI

Sistem COOCA sepenuhnya menganut arsitektur **Bring Your Own AI (BYOAI)**:
- **Zero Token Fees:** Tidak ada sistem pembelian atau top up token platform AI.
- **Direct Official Links:** Di halaman `/cooca-ai/providers`, pengguna diberikan panduan dan tautan langsung ke situs resmi developer provider untuk membuat akun atau menyalin API Key milik mereka.
- **Provider Guard:** Jika pengguna belum memiliki API key aktif di sistem, aksi pembukaan chat Tanya AI langsung mengarahkan pengguna ke halaman konfigurasi `/cooca-ai/providers`.

---

## 9. Pengujian & Kepatuhan Standar

Modul ini telah teruji penuh melalui unit dan feature test di:
- `tests/Feature/AiCompanyWorkflowTest.php` (6 skenario: alur kerja lobi, proposal aksi, revisi/penolakan, canonical redirects, guard BYOAI `ask()` missing provider, dan eksekusi chat berdasar agen aktif).
- `tests/Feature/AiProviderManagementTest.php` (4 skenario: zero plaintext credential exposure di UI/JSON, pengujian latensi provider & update status connected, dynamic model discovery, enkripsi AES-256 & pergantian default provider).
- `tests/Feature/AiRagAndContextEngineeringTest.php` (9 skenario: RAG tenant isolation, pencarian master data & histori, kepatuhan PP 55 / PPh 21 TER, 17 role directives, 5-layer prompt assembly, estimasi token, metrik dual monitor real, proteksi endpoint live metrics).
- `tests/Feature/AiOfficeEnvironmentsTest.php` (8 skenario: render lobi, 3 kantor terpisah, viewport 3D Three.js dengan fasilitas, isolasi tenant, kalkulasi status riil agen di database).
- `tests/Feature/AiDigitalCompanyTest.php` (6 skenario: otorisasi, maker-checker, idempotensi, kriptografi).
- `tests/Feature/AiSpecialistAgentsRoutingTest.php` (4 skenario: routing presisi 12 spesialis tanpa kolaps peran, tool registry mapping lengkap, akurasi kalkulasi staf & presensi faktual HRM).
- Seluruh pengujian passing 100% (57/57 AI tests, 480+ assertions, 0 errors, 0 failures).

