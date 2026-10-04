# Audit & Rencana Arsitektur: COOCA AI Digital Company
## AI Workforce, Executive Hierarchy, Departments, Human Approval Gate, & AI Office

> **Dokumen Audit & Rencana Rekayasa Sistem (PHASE 0 — AUDIT)**  
> **Target:** Platform SaaS ERP COOCA — Lapisan AI Digital Workforce Multi-Tenant  
> **Status:** PROPOSED & READY FOR APPROVAL (Interactive Confirmation Gate)  
> **Standar Kepatuhan:** `docs/agent.md` & `cooca-agent-directive` (Bento Apple HIG, Zero-Emoji, Tenant Isolation, Fraud Guard, Immutable Audit)

---

## 1. Executive Summary & Visi Sistem

COOCA AI Digital Company adalah lapisan kecerdasan buatan (*AI Workforce*) tingkat perusahaan yang terintegrasi secara *native* ke dalam monolit Laravel COOCA. Sistem ini bukan sekadar chatbot atau AI generatif biasa, melainkan bertindak layaknya **perusahaan digital terstruktur** (*Digital Company*) yang memiliki:
- **Executive Layer**: AI CEO (Strategi & Kesehatan Bisnis Global) dan AI COO (Orkestrator Operasional).
- **5 Departemen**: Sales, Marketing, Finance, Operations, dan People.
- **12 Specialized AI Agents**: Business, Sales, Inventory, Finance, Customer, Marketing, Content, Social Media, Marketplace, Purchasing, HR, dan Reporting Agent.
- **Human Approval Gate & Action Proposals**: Seluruh tindakan berisiko (MEDIUM, HIGH, CRITICAL) wajib melalui usulan terstruktur (*Action Proposal*), validasi ulang (*revalidation*), idempotency guard, dan persetujuan eksplisit pemilik usaha (*Human-in-the-Loop*).
- **AI Office Command Center**: Antarmuka visual Apple HIG Bento yang mencerminkan status riil backend (tanpa data fiktif, tanpa simulasi palsu, dan tanpa emoji).
- **BYOAI (Bring Your Own AI)**: Dukungan multi-provider (OpenAI, Google Gemini, Anthropic, OpenRouter) dengan penyimpanan kredensial terenkripsi *at-rest* dan proteksi kebocoran *zero-leakage*.

---

## 2. Hasil Audit Sistem Eksisting (Current System State)

### A. Arsitektur & Fondasi Inti
- **Framework & Runtime**: Laravel 13.17 pada PHP 8.3. Monolit modular teruji tanpa kebutuhan service eksternal seperti Python, Node microservice, atau Vector DB.
- **Multi-Tenant Isolation**: Setiap entitas bisnis terikat secara ketat pada `business_id` melalui trait `BelongsToBusiness`, global scope `BusinessScope`, serta penyedia konteks tunggal `App\Support\Context::business()`.
- **RBAC & Authorization**: Berjalan di atas middleware `RequirePermission` dan `Context::hasPermission()`, menjamin hak akses hierarkis (Owner, Admin, Supervisor, Kasir, Staff).
- **Queue & Background Jobs**: Menggunakan Laravel Queue dengan driver `database` (`jobs` table). Kompatibel penuh untuk pemrosesan asinkron AI tanpa dependensi Redis.
- **Scheduler**: Berjalan melalui `routes/console.php` untuk otomasi periodik harian dan berkala.
- **Immutable Audit Trail**: Model `AuditLog` sudah menerapkan proteksi immutabilitas mutlak di tingkat Eloquent (larangan `update` dan `delete`).

### B. Kode AI Eksisting & Reusable Components
1. **`App\Domain\Ai\AiSalesAnalysisService`**:
   - Memiliki pustaka algoritma analitik statistik & heuristik yang sangat matang:
     - Peramalan deret waktu omzet (*Linear Regression + Day-of-Week Seasonality Decomposition*).
     - Prediksi kehabisan stok inventori & kalkulasi *Reorder Point (ROP)*.
     - Deteksi anomali shift kasir & indikasi fraud (void abnormal, selisih laci kas).
     - Matriks profitabilitas menu/produk BCG (*Stars, Cash Cows, Question Marks, Dogs*).
     - Segmentasi pelanggan RFM (*Recency, Frequency, Monetary*).
   - Menjadi landasan analitik *read-only* utama yang dapat langsung dibungkus sebagai AI Tools.
2. **`App\Domain\Billing\EntitlementService` & `AiTokenUsage`**:
   - Sistem pemotongan token AI dan kuota langganan SaaS sudah terimplementasi secara akurat.
3. **Domain ERP Services Siap Pakai**:
   - **Sales & POS**: `SalesPipelineService`, `PosOrder`, `Invoice`, `Quotation`.
   - **Inventory & Gudang**: `StockService`, `InventoryStock`, `Location`.
   - **Finance & Akuntansi**: `AccountingReportService`, `AutoJournalService`, `CashLedgerService`, `PaymentSettlementService`.
   - **Purchasing**: `GoodsReceiptService`, `SupplierInvoiceService`, `PurchaseOrder`.
   - **CRM**: `Customer`, `CustomerPaymentTermReminderService`.
   - **Social Media**: `SocialMediaService`, `SocialMediaManager`, `SocialMediaPost`.
   - **Marketplace**: `MarketplaceManagerService`, `MarketplaceSyncService`.
   - **HRM**: `AttendanceService`, `PayrollCalculationService`, `PayrollRunService`.
   - **Notifikasi**: Tri-Channel Dispatcher (In-App UI Toast/Bell, Email HTML Apple HIG, WhatsApp Meta Cloud API).

### C. Analisis Celah & Komponen yang Belum Tersedia (Missing Components)
1. **Belum Ada Abstraksi Multi-Provider BYOAI**: Kredensial AI masih statis di `.env` (Gemini API key), belum ada tabel konfigurasi per-tenant `ai_provider_configs` untuk OpenAI, Gemini, Anthropic, dan OpenRouter.
2. **Belum Ada Skema AI Tasks & Action Proposals Terpadu**: Transaksi AI sebelumnya hanya menghasilkan objek array ad-hoc tanpa status pelacakan formal (`PENDING`, `RUNNING`, `WAITING_APPROVAL`, `COMPLETED`, `FAILED`).
3. **Belum Ada AI Work History Terstruktur**: Riwayat pekerjaan AI belum tercatat sebagai entitas audit kerja berkala yang dapat ditinjau oleh Business Owner.
4. **Belum Ada Tool Registry Formal**: Belum ada class registry yang mendeklarasikan nama tool, skema input, permission wajib, tingkat risiko, dan approval requirement.
5. **Belum Ada AI Office UI**: Halaman AI saat ini (`resources/views/app/ai/index.blade.php`) masih berupa dashboard analitik POS tradisional yang memuat elemen emoji dan belum mencerminkan hierarki organisasi Digital Company.

---

## 3. Identifikasi Risiko & Mitigasi (Risk & Guardrail Matrix)

| Kategori Risiko | Potensi Dampak | Mitigasi Arsitektur |
| :--- | :--- | :--- |
| **Cross-Tenant Data Leakage** | Tenant A membaca data penjualan atau stok Tenant B. | Seluruh query, context builder, dan tool handler WAJIB menyertakan `business_id` aktif via `Context::requireBusiness()`. Global scope `BusinessScope` aktif. |
| **Arbitrary LLM Execution** | LLM mengeksekusi aksi database atau finansial secara sepihak. | LLM HANYA diizinkan menghasilkan *Action Proposal* terstruktur (JSON). Eksekusi database dilakukan 100% oleh kode PHP Laravel setelah Human Approval. |
| **Credential & Key Exposure** | API key LLM milik merchant bocor ke browser atau log. | Enkripsi wajib di database (`Crypt::encryptString`), masking penuh di UI (`••••••••`), properti `$hidden` di model, dan filter sanitasi pada error log. |
| **Stale Action Execution** | Owner menyetujui PO/promo saat stok atau harga sudah berubah. | Protokol **Action Revalidation**: Sebelum eksekusi tindakan yang telah disetujui, sistem memeriksa kembali kondisi data terkini. Jika data berubah drastis, status aksi dibatalkan (*invalidated*). |
| **Duplicate Action Execution** | Klik ganda atau pengulangan aksi menerbitkan 2 PO atau broadcast ganda. | **Idempotency Key** unik pada setiap proposal. Eksekusi dibungkus dalam `DB::transaction()` dengan status *lock-for-update*. |
| **Infinite LLM Loop & High Cost** | LLM terjebak dalam siklus pemanggilan tool berulang-ulang. | Hard constraints: `max_steps = 15`, `max_tool_calls = 20`, timeout 30 detik, serta fail-safe fallback ke rule-based engine. |

---

## 4. Cetak Biru Arsitektur COOCA AI Digital Company

### A. Struktur Organisasi Digital Workforce
```text
                         BUSINESS OWNER
                               │
                       [FINAL AUTHORITY]
                               │
                         ┌─────▼─────┐
                         │  AI CEO   │ ── Strategic Health, High-Level Priorities, Executive Summary
                         └─────┬─────┘
                               │
                         ┌─────▼─────┐
                         │  AI COO   │ ── Operational Orchestration, Task Distribution, Monitoring
                         └─────┬─────┘
                               │
       ┌───────────────┬───────┼───────────────┬───────────────┐
       ↓               ↓       ↓               ↓               ↓
   MARKETING         SALES   FINANCE      OPERATIONS        PEOPLE
    (AI CMO)     (Sales Dir) (AI CFO)      (AI COO)       (HR Lead)
       │               │       │               │               │
  ┌────┴────┐      ┌───┴───┐ ┌─┴─────────┐ ┌───┴──────────┐   │
  │Marketing│      │Sales  │ │Finance    │ │Inventory     │   │
  │Content  │      │Cust.  │ │Reporting  │ │Purchasing    │ ┌─┴──────┐
  │Social   │      │       │ │           │ │Marketplace   │ │HR Agent│
  └─────────┘      └───────┘ └───────────┘ └──────────────┘ └────────┘
                               │
                        ACTION PROPOSALS
                               │
                         HUMAN APPROVAL
                               │
                            EXECUTE
                               │
                            VERIFY
                               │
                         WORK HISTORY
```

### B. Spesifikasi 12 Specialized AI Agents & Domain Handlers
1. **Business Agent (Executive Level)**: Mendiagnosis performa menyeluruh, mendeteksi korelasi lintas modul (omzet vs stok vs promo), memicu investigasi tim.
2. **Sales Agent (Sales Department)**: Menganalisis tren penjualan, produk laris vs anjlok, deviasi performa cabang. (*Tools: GetSalesSummary, GetSalesTrend, GetTopProducts, GetDecliningProducts*).
3. **Customer Agent (Sales Department)**: Menganalisis pelanggan tidur/dormant, retensi pelanggan, keterlambatan pembayaran termin kasbon. (*Tools: GetDormantCustomers, GetCustomerRfm, TriggerPaymentReminder*).
4. **Inventory Agent (Operations Department)**: Memantau stok menipis, dead stock, kalkulasi ROP/reorder. (*Tools: GetStockLevels, GetLowStockAlerts, CalculateReorderSuggestion*).
5. **Purchasing Agent (Operations Department)**: Evaluasi pemasok, rekomendasi timing pembelian bahan, draf PO. (*Tools: DraftPurchaseOrder, GetSupplierPerformance*).
6. **Marketplace Agent (Operations Department)**: Pemantauan sinkronisasi multi-kanal (Shopee, TikTok, Tokopedia). (*Tools: GetMarketplaceSyncStatus, DraftMarketplacePriceSync*).
7. **Finance Agent (Finance Department)**: Memantau arus kas, margin laba kotor & bersih, deteksi anomali pengeluaran. (*Tools: GetFinancialHealthSummary, GetCashflowSummary, DetectFinancialAnomalies*).
8. **Reporting Agent (Finance Department)**: Penyusunan laporan eksekutif harian, mingguan, dan rekapitulasi performa. (*Tools: GenerateExecutiveDailyDigest, GenerateManagementReport*).
9. **Marketing Agent (Marketing Department)**: Perumusan strategi promosi, analisis kampanye, target audiens. (*Tools: DraftMarketingCampaign, CreatePromoBundleDraft*).
10. **Content Agent (Marketing Department)**: Pembuatan draf caption medsos, ide konten, copywriting produk. (*Tools: GenerateContentDraft, GenerateProductCaption*).
11. **Social Media Agent (Marketing Department)**: Penjadwalan posting multi-platform, persiapan publikasi. (*Tools: DraftSocialPost, ScheduleSocialPost*).
12. **HR Agent (People Department)**: Rekapitulasi absensi staf, jam kerja, produktivitas kasir. (*Tools: GetAttendanceProductivitySummary*).

---

## 5. Rencana Skema Database (New Migrations)

### 1. `ai_provider_configs`
- `id` (uuid, PK)
- `business_id` (foreign uuid to `businesses`)
- `provider` (string: `openai`, `gemini`, `anthropic`, `openrouter`)
- `api_key` (text, encrypted at rest)
- `model` (string)
- `is_active` (boolean, default true)
- `is_default` (boolean, default false)
- `settings` (json, nullable)
- `tested_at` (timestamp, nullable)
- `status` (string: `connected`, `error`, `untested`)
- `last_error` (text, nullable)
- `timestamps`

### 2. `ai_tasks`
- `id` (uuid, PK)
- `business_id` (foreign uuid to `businesses`)
- `user_id` (foreign uuid to `users`, nullable)
- `executive_role` (string: `ceo`, `coo`, `cfo`, `cmo`, `hr_lead`)
- `department` (string: `sales`, `marketing`, `finance`, `operations`, `people`)
- `agent` (string: nama dari 12 agent)
- `type` (string: `chat`, `diagnosis`, `scheduled_monitoring`, `workflow`)
- `status` (string: `pending`, `running`, `waiting_approval`, `completed`, `failed`, `cancelled`)
- `priority` (string: `low`, `normal`, `high`, `urgent`)
- `input` (text / json)
- `context` (json, nullable)
- `result` (json, nullable)
- `error` (text, nullable)
- `steps_count` (integer, default 0)
- `started_at` (timestamp, nullable)
- `completed_at` (timestamp, nullable)
- `timestamps`

### 3. `ai_action_proposals`
- `id` (uuid, PK)
- `business_id` (foreign uuid to `businesses`)
- `ai_task_id` (foreign uuid to `ai_tasks`, nullable)
- `executive_role` (string)
- `department` (string)
- `agent` (string)
- `tool` (string: nama tool)
- `action_type` (string)
- `risk_level` (string: `LOW`, `MEDIUM`, `HIGH`, `CRITICAL`)
- `title` (string)
- `description` (text)
- `reason` (text)
- `payload` (json)
- `estimated_cost` (decimal, default 0)
- `status` (string: `pending`, `approved`, `rejected`, `executing`, `completed`, `failed`)
- `idempotency_key` (string, unique per business)
- `created_by` (foreign uuid to `users`, nullable)
- `approved_by` (foreign uuid to `users`, nullable)
- `approved_at` (timestamp, nullable)
- `rejected_by` (foreign uuid to `users`, nullable)
- `rejected_at` (timestamp, nullable)
- `rejection_reason` (text, nullable)
- `executed_at` (timestamp, nullable)
- `result` (json, nullable)
- `error` (text, nullable)
- `timestamps`

### 4. `ai_work_histories`
- `id` (uuid, PK)
- `business_id` (foreign uuid to `businesses`)
- `ai_task_id` (foreign uuid to `ai_tasks`, nullable)
- `session_title` (string)
- `executive_summary` (text)
- `participating_agents` (json)
- `insights_count` (integer, default 0)
- `actions_count` (integer, default 0)
- `approved_count` (integer, default 0)
- `rejected_count` (integer, default 0)
- `metadata` (json, nullable)
- `recorded_at` (timestamp)
- `timestamps`

---

## 6. Rencana Implementasi Bertahap (Phase 1 s/d Phase 8)

1. **Phase 1 — AI Foundation**:
   - Provider Abstraction (`AiProviderInterface`, `OpenAiProvider`, `GeminiProvider`, `AnthropicProvider`, `OpenRouterProvider`, `RuleBasedFallbackProvider`).
   - Credential Management & Encryption Service.
   - Migrations: `ai_provider_configs`, `ai_tasks`, `ai_action_proposals`, `ai_work_histories`.
   - Tool Engine Core: `AiToolRegistry`, `BaseAiTool`, `ToolResult`.
2. **Phase 2 — Organization & Roles**:
   - Enums / Value Objects: `ExecutiveRole`, `Department`, `AgentRole`, `RiskLevel`, `TaskStatus`.
3. **Phase 3 — Orchestrator**:
   - `AiOrchestrator`: Parsing intent, hierarki CEO -> COO, pembagian tugas ke department.
   - `AiContextBuilder`: Tenant-scoped data extractor (efisien & ringkas).
   - `AiModelRouter`: Pemilihan model sesuai bobot analisis (fast vs reasoning).
4. **Phase 4 — Core Agents & Tools**:
   - Business Agent, Sales Agent, Inventory Agent, Finance Agent + tool handlers.
5. **Phase 5 — Action System & Guardrails**:
   - Action Proposal Maker-Checker workflow.
   - Action Revalidation Protocol & Idempotency Lock.
   - Execution & Verification Pipeline via existing domain services.
6. **Phase 6 — Remaining Agents**:
   - Customer, Marketing, Content, Social Media, Marketplace, Purchasing, HR, Reporting Agent.
7. **Phase 7 — AI Office UI (Apple HIG Bento)**:
   - Command Center `/ai` & `/ai/office`: Visual hirarki CEO, COO, Department Wings, dan status riil meja kerja 12 agent.
   - Action Center `/ai/actions`: Antarmuka persetujuan proposal (Full-size modal review Apple HIG).
   - AI Activity Feed & AI Chat interaktif terintegrasi dengan Orchestrator.
   - Integrasi Pengaturan BYOAI di `/settings` (Tab AI Providers) dengan masking aman dan tombol Test Connection.
8. **Phase 8 — Background Automation & Scheduler**:
   - Artisan Command `cooca:ai-daily-business-check` (dapat dijadwalkan via `routes/console.php`).
   - Job asinkron pemrosesan task dan eksekusi aksi yang telah disetujui.
   - Automated Test Suite lengkap (Unit, Feature, Tenant Isolation, Approval Bypass Prevention).

---

## 7. Status Verifikasi Awal
- Source code saat ini telah diaudit dan tidak ditemukan konflik dependensi eksternal.
- Sistem siap memasuki tahap implementasi Phase 1 setelah konfirmasi persetujuan dari pengguna.
