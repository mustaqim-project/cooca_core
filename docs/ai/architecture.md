# Arsitektur COOCA AI Digital Company

> **Status:** Production Ready  
> **Prinsip Utama:** *"LLM May Reason, But Laravel Remains The Authority"*  
> **Teknologi:** PHP 8.2+, Laravel 11/12, PostgreSQL / SQLite / MySQL, Alpine.js, Tailwind CSS (Bento Apple HIG).  
> **Dependencies:** Zero Python, Zero Node.js microservice, Zero external queue/Redis requirement.

---

## 1. Filosofi & Paradigma

COOCA AI bukan sekadar chatbot atau antarmuka teks biasa. COOCA AI adalah **Digital Company / Autonomous Workforce Layer** yang dirancang sebagai bagian organik dari sistem operasi bisnis COOCA ERP.

### Perbedaan Paradigma
| Fitur / Karakteristik | Chatbot Konvensional | COOCA AI Digital Company |
| :--- | :--- | :--- |
| **Identitas** | Asisten tunggal tanpa struktur | Struktur organisasi lengkap (Hierarki Eksekutif & Departemen) |
| **Kewenangan** | LLM langsung memicu eksekusi | Maker-checker gate (Manusia pemegang keputusan akhir) |
| **Eksekusi Database** | Rentan halusinasi SQL liar | Terkunci pada Laravel Tools teruji, transaksi DB, & audit trail |
| **Kemandirian Infrastruktur** | Butuh Python microservice / Celery | 100% Native Laravel Ecosystem (Artisan, Service, Job, Cache) |
| **Model Provider** | Terkunci pada satu vendor tunggal | Multi-Provider BYOAI (OpenAI, Gemini, Anthropic, OpenRouter + Offline Fallback) |
| **Isolasi Tenant** | Mengandalkan filter manual | Diisolasi oleh `business_id`, `BelongsToBusiness`, dan Tenant Context |

---

## 2. Diagram Arsitektur Hulu ke Hilir

```
[ Owner / Executive User ]
          │
          ▼
┌────────────────────────────────────────────────────────┐
│  AI Office UI (Bento Apple HIG - Ultra High Polish)    │
│  - Executive Suites (CEO, COO)                         │
│  - 5 Department Pods (Sales, Marketing, Ops, HR, Fin)  │
│  - Action Center (Maker-Checker Review Modal)          │
│  - Multi-Provider Settings (BYOAI Encrypted Vault)    │
└─────────────────────────┬──────────────────────────────┘
                          │ HTTP Request / Webhook
                          ▼
┌────────────────────────────────────────────────────────┐
│  AiCompanyWebController                                │
│  - Input Sanitization & Tenant Context Enforcer        │
│  - Rate Limiting & Token Allowance Check               │
└─────────────────────────┬──────────────────────────────┘
                          │
                          ▼
┌────────────────────────────────────────────────────────┐
│  AiOrchestrator                                        │
│  ┌──────────────────────────────────────────────────┐  │
│  │ 1. Model Router (Rule-based / Cost / Latency)    │  │
│  │ 2. Context Builder (Tenant Isolated Snapshot)    │  │
│  │ 3. Provider Manager (OpenAI, Gemini, Anthropic,  │  │
│  │    OpenRouter, or Offline Fallback Engine)       │  │
│  │ 4. Tool Registry (12 Read/Propose Tools)         │  │
│  │ 5. Action Policy & Risk Evaluation               │  │
│  └──────────────────────────────────────────────────┘  │
└─────────────────────────┬──────────────────────────────┘
                          │
         ┌────────────────┴────────────────┐
         ▼                                 ▼
┌───────────────────────────┐   ┌───────────────────────────────┐
│ Read Tools (Immediate)    │   │ Propose Tools (Action Gate)   │
│ - Sales & Trend Analytics │   │ - Draft Invoice               │
│ - Stock & OPNAME Health   │   │ - Draft Purchase Order        │
│ - Anomaly & Fraud Guard   │   │ - Draft Marketing Campaign    │
│ - Financial Health Ratios │   │ - Draft Social Media Post     │
└─────────────┬─────────────┘   └───────────────┬───────────────┘
              │                                 │ Writes
              ▼                                 ▼
┌───────────────────────────┐   ┌───────────────────────────────┐
│ Direct Insight Report     │   │ ai_action_proposals (PENDING) │
│ Returned to UI            │   └───────────────┬───────────────┘
└───────────────────────────┘                   │
                                                ▼ Human Maker-Checker
                                ┌───────────────────────────────┐
                                │ Human Approval Gate           │
                                │ (Owner / Supervisor Review)   │
                                └───────────────┬───────────────┘
                                                │ Approve / Reject
                                                ▼
                                ┌───────────────────────────────┐
                                │ AiActionExecutor              │
                                │ - Idempotency Lock            │
                                │ - State Revalidation          │
                                │ - DB Transaction Commit       │
                                │ - Immutable AuditLog          │
                                └───────────────────────────────┘
```

---

## 3. Komponen Utama

### 3.1 Domain Layer (`app/Domain/Ai/`)
1. **Organization:**
   - `ExecutiveRole`: Hierarki C-Level (CEO, COO, CFO, CMO, HR Lead, Sales Director).
   - `Department`: 5 Squad fungsional (Executive, Sales, Marketing, Finance, Operations, People).
   - `AgentRole`: 12 Agen spesialis dengan wewenang terfokus.
   - `CompanyHierarchy`: Routing matrix dari intensi instruksi ke agen penanggung jawab.

2. **Providers & Routing:**
   - `AiProviderInterface`: Kontrak baku komunikasi model.
   - `OpenAiProvider`, `GeminiProvider`, `AnthropicProvider`, `OpenRouterProvider`: Integrasi multi-vendor.
   - `RuleBasedFallbackProvider`: Mesin penalaran offline 100% lokal berbasis template analisis bisnis nyata.
   - `AiProviderManager`: Resolusi konfigurasi terenkripsi per-tenant.
   - `AiModelRouter`: Pemilihan provider berdasarkan biaya, latensi, dan kompleksitas tugas.

3. **Context Engine (`AiContextBuilder`):**
   - Menghimpun agregat metrik bisnis secara deterministik: Omzet 30 hari, tren penjualan, item stok kritis, piutang, hutang jatuh tempo, dan metrik absensi karyawan.
   - Tidak membocorkan data tenant lain (`tenant-safe snapshot`).

4. **Policy & Action Gate (`AiActionPolicy`):**
   - Klasifikasi risiko aksi: `low`, `medium`, `high`, `critical`.
   - Menentukan apakah suatu proposal butuh persetujuan manusia (`requiresApproval`).
   - Verifikasi hak akses pengguna untuk mengeksekusi proposal (`canApprove`).

5. **Execution & Idempotency (`AiActionExecutor`):**
   - Menjaga integritas data saat proposal disetujui.
   - Menggunakan kunci idempotensi `ACT-xxxx` untuk mencegah eksekusi ganda.
   - Melakukan re-validasi data sebelum transaksi basis data dieksekusi.
   - Mencatat ke `audit_logs` dan `ai_work_histories`.

---

## 4. Keamanan & Integritas Data

1. **Zero Raw SQL Injection:**
   Semua mutasi dilakukan melalui model Eloquent resmi Laravel dan domain service teruji.
2. **Kriptografi API Key:**
   API key penyedia AI disimpan dengan enkripsi simetris AES-256-CBC (`$casts = ['api_key' => 'encrypted']`) dan tidak pernah dikirim polos ke klien (`$hidden = ['api_key']`, aksesor bertopeng `••••••••`).
3. **Multi-Tenant Isolation:**
   Setiap tabel AI (`ai_tasks`, `ai_action_proposals`, `ai_work_histories`, `ai_provider_configs`) memegang `business_id` dengan referensi foreign key berindeks dan divalidasi via `Context::business()`.
