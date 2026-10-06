# PRODUCT REQUIREMENT DOCUMENT (PRD) & SYSTEM WORKFLOW
## COOCA Multi-Tenant Model Context Protocol (MCP) Server

| Metadata | Keterangan |
| :--- | :--- |
| **Dokumen ID** | `COOCA-PRD-MCP-2026-001` |
| **Versi** | `1.0.0 (Comprehensive Release)` |
| **Status** | `Draft Approved for Architecture Review` |
| **Target Sistem** | Core SaaS COOCA ERP (`cooca_core`) |
| **Author** | Principal AI Architect & Laravel System Engineer |
| **Tanggal Efektif** | 07 Oktober 2026 |

---

## DAFTAR ISI
1. [Executive Summary & Visi Produk](#1-executive-summary--visi-produk)
2. [Prinsip Desain & Tenant Isolation Guardrails](#2-prinsip-desain--tenant-isolation-guardrails)
3. [Arsitektur Sistem & Protokol Transport](#3-arsitektur-sistem--protokol-transport)
4. [Katalog Tools MCP (Tool Definitions & JSON Schema)](#4-katalog-tools-mcp-tool-definitions--json-schema)
   - 4.1. Modul Keuangan & OCR Nota (`finance.*`)
   - 4.2. Modul Produk & Inventori (`inventory.*`, `product.*`)
   - 4.3. Modul Media Sosial & Pemasaran (`social.*`)
   - 4.4. Modul Laporan & Business Intelligence (`report.*`, `analytics.*`)
   - 4.5. Modul CRM & Notifikasi WhatsApp (`crm.*`, `whatsapp.*`)
5. [Workflow & Sequence Diagram End-to-End](#5-workflow--sequence-diagram-end-to-end)
   - 5.1. Workflow 1: Foto Nota Pengeluaran → Auto-Journaling
   - 5.2. Workflow 2: Input Produk Massal dari Teks/Katalog Bebas
   - 5.3. Workflow 3: Pembuatan & Penjadwalan Konten Sosial Media
   - 5.4. Workflow 4: Analisis Keuangan Eksekutif (AI CFO Chat)
6. [Spesifikasi UI Owner Panel (Bento Apple HIG)](#6-spesifikasi-ui-owner-panel-bento-apple-hig)
7. [Matriks Keamanan, Error Handling & Quota](#7-matriks-keamanan-error-handling--quota)
8. [Skema Database & Migrasi](#8-skema-database--migrasi)
9. [Roadmap Implementasi Bertahap](#9-roadmap-implementasi-bertahap)

---

## 1. Executive Summary & Visi Produk

### 1.1 Latar Belakang Masalah
Pelaku UMKM dan pemilik bisnis operasional (ritel, bengkel, resto/kafe) sering mengalami *operational friction*:
1. **Beban Input Nota Pengeluaran:** Karyawan atau pemilik menumpuk puluhan lembar nota fisik per minggu. Proses ketik manual tanggal, kategori, dan nominal ke form ERP sangat rentan keliru (*typo*) dan sering ditunda.
2. **Katalog Produk yang Rumit:** Menambahkan produk baru, mengubah harga pokok/jual, atau stok awal dari invoice supplier membutuhkan navigasi form bertingkat.
3. **Pemasaran Terabaikan:** Pemilik bisnis tidak memiliki waktu rutin untuk membuat caption, mendesain jadwal konten promosi media sosial, atau memantau engagement.
4. **Hambatan Akses Data:** Pemilik bisnis sering membutuhkan jawaban cepat seperti *"Berapa laba bersih saya minggu ini?"* tanpa harus mengunduh file spreadsheet atau membuka laporan akuntansi yang rumit.

### 1.2 Visi Solusi: Zero-Manual Operation via MCP
Dengan mengintegrasikan **Model Context Protocol (MCP)** ke dalam COOCA, pemilik bisnis dapat menggunakan AI pilihan mereka (Claude Desktop, ChatGPT macOS, Antigravity IDE, Cursor, atau Custom Assistant) untuk mengoperasikan bisnis secara natural lewat suara/teks/gambar, dengan jaminan:
* **Tindakan Nyata (Actionable):** AI bukan sekadar menjawab teks, melainkan melakukan aksi mutasi nyata ke database COOCA.
* **Otomasi Terintegrasi:** Setiap pencatatan otomatis memicu aturan akuntansi (*double-entry journal*), mutasi stok, dan pencatatan audit log.
* **Isolasi Penuh:** AI terikat secara permanen pada `business_id` aktif milik pemilik akun tanpa ada celah manipulasi lintas tenant.

---

## 2. Prinsip Desain & Tenant Isolation Guardrails

> [!IMPORTANT]
> **Hard Multi-Tenant Rule:** AI dilarang mengirimkan parameter `business_id` dalam tool call. Parameter `business_id` harus di-resolve secara mutlak oleh backend COOCA dari token autentikasi yang divalidasi.

```mermaid
flowchart TD
    Client[AI Client: Claude / ChatGPT / Cursor] -->|1. Request MCP Tool Call + Bearer Token| Gateway[COOCA MCP Gateway]
    Gateway -->|2. Validate Token| Sanctum[(Sanctum Token Registry)]
    Sanctum -->|3. Resolves User & Business ID| Ctx[Context::setBusiness and requireBusiness]
    
    subgraph Isolated Tenant Context [Tenant Isolation Sandbox: business_id = 42]
        Ctx --> Controller[MCP Tool Dispatcher]
        Controller --> DomainFinance[Domain / Finance Service]
        Controller --> DomainProduct[Domain / Product Service]
        Controller --> DomainSocial[Domain / Social Media Service]
        Controller --> DomainReport[Domain / Report Service]
    end
    
    DomainFinance --> TenantDB[(COOCA Tenant Data Scoped)]
    DomainProduct --> TenantDB
    DomainSocial --> TenantDB
```

### Prinsip Utama Guardrails:
1. **Zero-Trust Input Context:** Parameter `business_id`, `tenant_id`, atau `user_id` tidak pernah diterima dari argumen tool call JSON.
2. **Controller-Level RBAC & Scope Abilities:** Setiap token MCP memiliki batasan hak akses (`abilities`), misalnya `['mcp:expenses:write', 'mcp:products:manage', 'mcp:reports:read']`.
3. **Automatic Audit Trail:** Setiap eksekusi tool dicatat pada tabel `audit_logs` dan `mcp_activity_logs` dengan IP address, nama tool, payload ringkas, dan status eksekusi.
4. **Idempotency Safeguard:** Mendukung parameter `idempotency_key` pada operasi finansial untuk mencegah pencatatan pengeluaran ganda saat retry jaringan.

---

## 3. Arsitektur Sistem & Protokol Transport

COOCA menyediakan dua mode transport protokol MCP:

### 3.1 Mode 1: Remote HTTP Server-Sent Events (SSE) — *Recommended*
AI Client terhubung langsung ke COOCA Cloud melalui HTTPS SSE tanpa perlu instalasi runtime Node.js/Python lokal pada laptop user.
* **Endpoint Inisialisasi SSE:** `GET https://api.cooca.id/v1/mcp/sse`
* **Endpoint Pesan JSON-RPC:** `POST https://api.cooca.id/v1/mcp/message?sessionId={session_id}`
* **Header Wajib:**
  ```http
  Authorization: Bearer cooca_mcp_live_xxxxxxxxxxxxxxxxxxxxxxxx
  Accept: text/event-stream
  Content-Type: application/json
  ```

### 3.2 Mode 2: Local Stdio Gateway (Node.js CLI Wrapper)
Untuk client lokal seperti Claude Desktop yang menggunakan file `claude_desktop_config.json`:
```json
{
  "mcpServers": {
    "cooca-erp": {
      "command": "npx",
      "args": ["-y", "@cooca/mcp-server"],
      "env": {
        "COOCA_API_URL": "https://api.cooca.id/v1",
        "COOCA_API_TOKEN": "cooca_mcp_live_e587b1c49..."
      }
    }
  }
}
```

---

## 4. Katalog Tools MCP (Tool Definitions & JSON Schema)

Berikut spesifikasi resmi tool yang diregistrasikan ke AI Client:

### 4.1 Modul Keuangan & OCR Nota (`finance.*`)

#### Tool 1: `finance_record_expense`
Digunakan oleh AI setelah mengekstrak informasi struk/nota pengeluaran.

```json
{
  "name": "finance_record_expense",
  "description": "Mencatat bukti pengeluaran/beban operasional bisnis, menyimpan file nota ke vault tenant, dan otomatis membukukan jurnal akuntansi kas keluar.",
  "inputSchema": {
    "type": "object",
    "properties": {
      "expense_date": {
        "type": "string",
        "format": "date",
        "description": "Tanggal nota dalam format YYYY-MM-DD. Jika tidak tertera, gunakan tanggal hari ini."
      },
      "category": {
        "type": "string",
        "enum": ["Bahan Baku", "Operasional", "Transportasi & Bensin", "Gaji & Upah", "Sewa & Utilitas", "Pemasaran", "Lain-lain"],
        "description": "Kategori beban yang paling relevan dengan jenis pengeluaran."
      },
      "amount": {
        "type": "number",
        "minimum": 1,
        "description": "Nominal total pengeluaran dalam Rupiah (angka murni tanpa titik/koma)."
      },
      "payment_method": {
        "type": "string",
        "enum": ["petty_cash", "cash", "bank_transfer"],
        "default": "cash",
        "description": "Metode pembayaran kasir atau rekening asal pembayaran."
      },
      "description": {
        "type": "string",
        "maxLength": 255,
        "description": "Keterangan rincian barang/jasa yang dibeli beserta nama toko/merchant."
      },
      "receipt_image_base64": {
        "type": "string",
        "description": "Optional: String base64 gambar nota (JPEG/PNG/WEBP/PDF) untuk diarsipkan sebagai bukti resmi."
      },
      "receipt_file_name": {
        "type": "string",
        "default": "nota.jpg",
        "description": "Nama file bukti nota."
      },
      "idempotency_key": {
        "type": "string",
        "description": "Kunci unik (UUID) untuk mencegah pengulangan transaksi nota yang sama."
      }
    },
    "required": ["expense_date", "category", "amount", "description"]
  }
}
```

#### Tool 2: `finance_get_cash_and_bank_balances`
Memberikan ringkasan saldo kas fisik kasir, kas kecil (petty cash), dan akun bank bisnis.
```json
{
  "name": "finance_get_cash_and_bank_balances",
  "description": "Melihat saldo terkini seluruh akun kas toko, petty cash kasir, dan rekening bank aktif milik bisnis.",
  "inputSchema": {
    "type": "object",
    "properties": {
      "location_id": {
        "type": "string",
        "description": "Optional: Filter saldo kas untuk cabang atau outlet tertentu."
      }
    }
  }
}
```

---

### 4.2 Modul Produk & Inventori (`inventory.*`, `product.*`)

#### Tool 3: `inventory_create_product`
Menambahkan produk atau material baru ke katalog toko lengkap dengan harga dan stok awal.
```json
{
  "name": "inventory_create_product",
  "description": "Membuat master produk baru di katalog COOCA, menetapkan barcode/SKU, HPP (modal), harga jual, dan stok awal.",
  "inputSchema": {
    "type": "object",
    "properties": {
      "name": {
        "type": "string",
        "description": "Nama produk lengkap."
      },
      "sku": {
        "type": "string",
        "description": "Optional SKU/Barcode produk. Jika kosong, sistem COOCA membuatkan SKU otomatis."
      },
      "category_name": {
        "type": "string",
        "description": "Nama kategori produk (misal: Makanan, Minuman, Suku Cadang, Layanan)."
      },
      "cost_price": {
        "type": "number",
        "minimum": 0,
        "description": "Harga modal beli/HPP per unit dalam Rupiah."
      },
      "selling_price": {
        "type": "number",
        "minimum": 0,
        "description": "Harga jual resmi ke konsumen per unit dalam Rupiah."
      },
      "initial_stock": {
        "type": "number",
        "default": 0,
        "description": "Jumlah stok awal yang tersedia saat produk dibuat."
      },
      "unit_name": {
        "type": "string",
        "default": "pcs",
        "description": "Satuan unit (pcs, cup, porsi, botol, box, kg)."
      }
    },
    "required": ["name", "cost_price", "selling_price"]
  }
}
```

#### Tool 4: `inventory_check_stock`
Cek ketersediaan stok produk atau daftar barang yang berada di bawah stok minimum.
```json
{
  "name": "inventory_check_stock",
  "description": "Memeriksa jumlah stok produk tertentu atau mendapatkan daftar barang kritis yang stoknya menipis (low-stock warning).",
  "inputSchema": {
    "type": "object",
    "properties": {
      "search_query": {
        "type": "string",
        "description": "Optional kata kunci pencarian nama produk atau SKU."
      },
      "only_low_stock": {
        "type": "boolean",
        "default": false,
        "description": "Jika true, hanya tampilkan produk yang stoknya di bawah batas minimum (reorder point)."
      }
    }
  }
}
```

---

### 4.3 Modul Media Sosial & Pemasaran (`social.*`)

#### Tool 5: `social_schedule_post`
Menjadwalkan penerbitan materi promosi ke berbagai kanal media sosial terhubung via COOCA `SocialMediaManager`.
```json
{
  "name": "social_schedule_post",
  "description": "Menjadwalkan posting konten promosi ke kanal media sosial (Instagram, TikTok, Facebook Page, X).",
  "inputSchema": {
    "type": "object",
    "properties": {
      "caption": {
        "type": "string",
        "description": "Teks caption konten promosi termasuk hashtag dan CTA (Call to Action)."
      },
      "channels": {
        "type": "array",
        "items": {
          "type": "string",
          "enum": ["instagram", "facebook", "tiktok", "twitter"]
        },
        "description": "Daftar channel tujuan publikasi."
      },
      "scheduled_at": {
        "type": "string",
        "format": "date-time",
        "description": "Waktu jadwal tayang format ISO-8601 (misal: 2026-10-08T17:00:00+07:00). Kosongkan jika ingin langsung diterbitkan (publish now)."
      },
      "media_urls": {
        "type": "array",
        "items": { "type": "string" },
        "description": "Daftar URL gambar/video dari cloud storage yang akan diunggah."
      }
    },
    "required": ["caption", "channels"]
  }
}
```

#### Tool 6: `social_get_insights`
Mengambil metrik performa postingan dan pertumbuhan jangkauan media sosial.
```json
{
  "name": "social_get_insights",
  "description": "Melihat performa engagement media sosial bisnis: impresi, jangkauan, jumlah like, komentar, dan postingan terbaik.",
  "inputSchema": {
    "type": "object",
    "properties": {
      "days": {
        "type": "integer",
        "default": 30,
        "description": "Rentang hari analisis data (7, 14, 30, atau 90 hari)."
      }
    }
  }
}
```

---

### 4.4 Modul Laporan & Business Intelligence (`report.*`, `analytics.*`)

#### Tool 7: `report_get_profit_loss`
Menghitung laporan laba rugi bisnis secara komprehensif.
```json
{
  "name": "report_get_profit_loss",
  "description": "Menghitung ringkasan laba rugi bisnis: total omzet, HPP (COGS), laba kotor, rincian biaya operasional, dan laba bersih (Net Profit).",
  "inputSchema": {
    "type": "object",
    "properties": {
      "start_date": {
        "type": "string",
        "format": "date",
        "description": "Tanggal awal periode analisis (YYYY-MM-DD)."
      },
      "end_date": {
        "type": "string",
        "format": "date",
        "description": "Tanggal akhir periode analisis (YYYY-MM-DD)."
      }
    },
    "required": ["start_date", "end_date"]
  }
}
```

#### Tool 8: `analytics_get_sales_forecast`
Mengakses modul Machine Learning COOCA (`AiSalesAnalysisService`) untuk melihat tren dan proyeksi penjualan.
```json
{
  "name": "analytics_get_sales_forecast",
  "description": "Mengambil prediksi omzet masa depan dan rekomendasi stok reorder menggunakan engine analitik COOCA.",
  "inputSchema": {
    "type": "object",
    "properties": {
      "forecast_days": {
        "type": "integer",
        "default": 14,
        "description": "Jumlah hari proyeksi ke depan (misal: 7, 14, 30 hari)."
      }
    }
  }
}
```

---

### 4.5 Modul CRM & Notifikasi WhatsApp (`crm.*`, `whatsapp.*`)

#### Tool 9: `crm_search_customer`
Mencari data riwayat belanja pelanggan, poin loyalitas, dan status piutang.
```json
{
  "name": "crm_search_customer",
  "description": "Mencari profil pelanggan berdasarkan nama atau nomor telepon WhatsApp, melihat poin loyalitas dan riwayat transaksi.",
  "inputSchema": {
    "type": "object",
    "properties": {
      "query": {
        "type": "string",
        "description": "Nama pelanggan atau nomor HP/WhatsApp."
      }
    },
    "required": ["query"]
  }
}
```

#### Tool 10: `whatsapp_send_notification`
Mengirimkan pesan resmi via Gateway WhatsApp COOCA (Render Baileys / Meta Cloud API) ke pelanggan atau staf.
```json
{
  "name": "whatsapp_send_notification",
  "description": "Mengirimkan pesan WhatsApp resmi dari nomor bisnis ke pelanggan (struk, pengingat janji temu, penawaran harga).",
  "inputSchema": {
    "type": "object",
    "properties": {
      "phone_number": {
        "type": "string",
        "description": "Nomor WhatsApp tujuan dengan kode negara (contoh: 6281234567890)."
      },
      "message": {
        "type": "string",
        "description": "Isi pesan teks WhatsApp."
      }
    },
    "required": ["phone_number", "message"]
  }
}
```

---

## 5. Workflow & Sequence Diagram End-to-End

### 5.1 Workflow 1: Foto Nota Pengeluaran → Auto-Journaling

```mermaid
sequenceDiagram
    autonumber
    actor Owner as Pemilik Bisnis
    participant LLM as AI Client (Claude/Cursor)
    participant MCP as COOCA MCP Server
    participant Storage as TenantStorage & Quota
    participant ExpenseSvc as Expense & Accounting Service
    participant DB as Database COOCA (Scoped)

    Owner->>LLM: Upload foto nota parkir/belanja + "Catat nota ini"
    Note over LLM: AI Vision menganalisis teks nota:<br/>Nominal: Rp 120.000<br/>Kategori: Operasional<br/>Keterangan: Nota Bensin Mobil Toko
    
    LLM->>MCP: Call Tool `finance_record_expense`<br/>(amount, category, description, base64_image)
    
    activate MCP
    Note over MCP: Validasi Token & Binding Context<br/>Context::requireBusiness()
    
    MCP->>Storage: assertCanUpload() & store(receipt_image)
    Storage-->>MCP: file_path: "bisnis/bengkel-bagema/expenses/nota-01.jpg"
    
    MCP->>ExpenseSvc: createExpenseRecord()
    ExpenseSvc->>DB: INSERT into `expenses` (business_id, amount, ...)
    
    ExpenseSvc->>ExpenseSvc: generateDoubleEntryJournal()
    ExpenseSvc->>DB: INSERT into `journal_entries`<br/>[Debit] Beban Operasional 120.000<br/>[Kredit] Kas Toko 120.000
    
    ExpenseSvc->>DB: Log into `audit_logs`
    
    MCP-->>LLM: Return JSON: { status: "success", expense_number: "EXP-202610-0089", amount: 120000 }
    deactivate MCP
    
    LLM-->>Owner: "✅ Nota berhasil dibukukan dengan No. EXP-202610-0089 sebesar Rp 120.000. Jurnal akuntansi kas keluar sudah otomatis terbit."
```

---

### 5.2 Workflow 2: Input Produk Massal dari Teks/Katalog Bebas

```mermaid
sequenceDiagram
    autonumber
    actor Owner as Pemilik Bisnis
    participant LLM as AI Client
    participant MCP as COOCA MCP Server
    participant ProductSvc as Product & Inventory Service
    participant DB as Database COOCA

    Owner->>LLM: "Tambah produk baru: Kopi Susu Creamy modal 10rb jual 18rb stok 40, dan Teh Tarik modal 5rb jual 12rb stok 30"
    
    Note over LLM: AI menyusun array entitas produk
    loop Untuk Setiap Produk
        LLM->>MCP: Call Tool `inventory_create_product`
        MCP->>ProductSvc: validate & createProduct()
        ProductSvc->>DB: INSERT into `products` (business_id, name, cost_price, selling_price)
        ProductSvc->>DB: INSERT into `inventory_movements` (initial stock adjustment)
        MCP-->>LLM: Result SKU & ID Produk
    end
    
    LLM-->>Owner: "✅ 2 Produk berhasil didaftarkan ke katalog toko lengkap dengan stok awal dan perhitungan margin keuntungan."
```

---

### 5.3 Workflow 3: Pembuatan & Penjadwalan Konten Sosial Media

```mermaid
flowchart TD
    Start[User: Buatkan promo weekend untuk Instagram & TikTok] --> Brainstorm[AI merancang Headline, Copywriting, Hashtags]
    Brainstorm --> ToolCall[AI memanggil Tool social_schedule_post]
    ToolCall --> MCPAuth[MCP Server validasi scope token social_media.manage]
    MCPAuth --> SMM[SocialMediaManager Service]
    SMM --> SavePost[Simpan record SocialMediaPost]
    SavePost --> SaveTarget[Simpan SocialPostTarget untuk IG dan TikTok]
    SaveTarget --> QueueJob[Dispatch PublishSocialMediaTargetJob ke antrean Redis/DB]
    QueueJob --> Confirmation[Kembalikan status Terjadwal ke AI]
    Confirmation --> FeedbackUser[AI memberi preview jadwal ke User]
```

---

### 5.4 Workflow 4: Analisis Keuangan Eksekutif (AI CFO Chat)

```mermaid
sequenceDiagram
    autonumber
    actor Owner as Pemilik Bisnis
    participant LLM as AI Client
    participant MCP as COOCA MCP Server
    participant ReportSvc as Report & Analytics Service

    Owner->>LLM: "Berapa laba bersih saya bulan lalu dan pengeluaran terbesar di mana?"
    LLM->>MCP: Call Tool `report_get_profit_loss` (start: 2026-09-01, end: 2026-09-30)
    MCP->>ReportSvc: calculateProfitAndLoss(business)
    ReportSvc-->>MCP: { gross_revenue: 45000000, cogs: 22000000, total_expenses: 8500000, net_profit: 14500000, top_expense_category: "Gaji & Upah" }
    MCP-->>LLM: Return Financial Matrix
    
    Note over LLM: AI menganalisis data keuangan secara cerdas
    LLM-->>Owner: "Bulan lalu omzet Anda Rp 45.000.000 dengan laba bersih Rp 14.500.000 (margin 32.2%). Pengeluaran terbesar adalah Gaji & Upah (Rp 5.200.000)."
```

---

## 6. Spesifikasi UI Owner Panel (Bento Apple HIG)

Fitur manajemen kunci MCP diimplementasikan pada portal Owner COOCA di rute:
`/settings/integrations/mcp` (*Settings Hub Integration*).

### 6.1 Desain Layout Kartu Bento (Apple HIG Standard)
* **Kartu 1: Status & Switch Master MCP (Hero Card 2-Kolom)**
  * Status: Toggle On/Off *Koneksi AI Assistant (MCP)*
  * Info: Status sesi terhubung aktif, jumlah tool call 30 hari terakhir.
* **Kartu 2: Manajemen Kunci Akses (Security Token Card)**
  * Tombol *Generate New MCP Token*.
  * Masked Key display (`cooca_mcp_live_••••••••••••••••3a8f`) dengan tombol copy instan.
  * Selector hak akses (*Permission Scopes*): Checkbox granular untuk Keuangan, Produk, Social Media, Laporan, WhatsApp.
* **Kartu 3: One-Click Config Setup (Helper Card)**
  * Tab pemilih client: `Claude Desktop` | `Cursor / Antigravity` | `Custom REST/SSE`.
  * Blok kode JSON yang otomatis menyertakan URL dan Token aktif untuk disalin dengan 1 klik.
* **Kartu 4: Live Activity & Audit Log (Activity Feed)**
  * Tabel riwayat interaksi AI: Waktu, Nama Tool, Parameter Ringkas, IP Client, Status (Success/Error).

---

## 7. Matriks Keamanan, Error Handling & Quota

### 7.1 Response Code & Error Format
Setiap eksekusi MCP mengikuti standar JSON-RPC 2.0 error handling:

```json
{
  "jsonrpc": "2.0",
  "error": {
    "code": -32001,
    "message": "Tenant Quota Exceeded: Storage limit reached for receipt uploads.",
    "data": {
      "current_usage_mb": 512.4,
      "max_limit_mb": 500.0,
      "action_required": "Upgrade subscription plan or purge unneeded media."
    }
  },
  "id": 4
}
```

### 7.2 Standar Kode Kesalahan
| Kode | HTTP Padanan | Deskripsi | Mitigasi AI |
| :--- | :--- | :--- | :--- |
| `-32000` | 401 Unauthorized | Token tidak valid atau kedaluwarsa. | Beritahu user untuk memperbarui token di COOCA. |
| `-32001` | 403 Forbidden | Token tidak memiliki izin scope untuk tool ini. | Beritahu user untuk mencentang izin pada pengaturan MCP. |
| `-32002` | 422 Unprocessable | Argumen tidak memenuhi validasi (contoh: amount < 0). | AI memvalidasi ulang data dari user sebelum memanggil lagi. |
| `-32003` | 429 Too Many Req | Rate limit per menit terlampaui (Tier Limit). | AI menunggu cooldown exponential backoff. |
| `-32004` | 402 Payment Req | Kuota bulanan fitur AI/Storage habis. | Berikan saran upgrade paket langganan. |

### 7.3 Rate Limiting per Paket Langganan
* **Paket Starter:** Maksimal 30 request/menit, kuota 500 tool calls/bulan.
* **Paket Pro:** Maksimal 120 request/menit, kuota 3.000 tool calls/bulan.
* **Paket Enterprise:** Maksimal 300 request/menit, kuota unlimited.

---

## 8. Skema Database & Migrasi

Tabel baru yang dibutuhkan untuk mengelola token dan riwayat interaksi MCP:

### 8.1 Tabel `mcp_access_tokens`
```sql
CREATE TABLE `mcp_access_tokens` (
  `id` VARCHAR(36) PRIMARY KEY,
  `business_id` VARCHAR(36) NOT NULL,
  `user_id` VARCHAR(36) NOT NULL,
  `name` VARCHAR(100) NOT NULL COMMENT 'Nama perangkat/aplikasi client',
  `token_hash` VARCHAR(64) NOT NULL UNIQUE COMMENT 'SHA-256 hash dari token',
  `abilities` JSON NOT NULL COMMENT 'Daftar scope yang diizinkan',
  `last_used_at` TIMESTAMP NULL,
  `expires_at` TIMESTAMP NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_mcp_tokens_business` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_mcp_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
);
```

### 8.2 Tabel `mcp_activity_logs`
```sql
CREATE TABLE `mcp_activity_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `business_id` VARCHAR(36) NOT NULL,
  `token_id` VARCHAR(36) NULL,
  `tool_name` VARCHAR(100) NOT NULL,
  `arguments_payload` JSON NULL COMMENT 'Sanitized input arguments',
  `response_status` VARCHAR(20) NOT NULL COMMENT 'success / error',
  `execution_time_ms` INT UNSIGNED NOT NULL,
  `ip_address` VARCHAR(45) NULL,
  `error_message` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_mcp_logs_biz_time` (`business_id`, `created_at`),
  INDEX `idx_mcp_logs_tool` (`tool_name`)
);
```

---

## 9. Roadmap Implementasi Bertahap

```mermaid
gantt
    title Roadmap Peluncuran Fitur COOCA MCP
    dateFormat  YYYY-MM-DD
    section Fase 1: Fondasi & Keamanan
    Skema Database & Sanctum Scopes     :done, 2026-10-08, 3d
    Remote SSE MCP Controller           :active, 2026-10-11, 4d
    UI Kelola Token di Owner Panel      :2026-10-15, 3d
    
    section Fase 2: Core Tools (Nota & Produk)
    Tool Keuangan & Auto-Journaling     :2026-10-18, 4d
    Tool Katalog Produk & Stok Opname   :2026-10-22, 3d
    Pengujian End-to-End dengan Claude  :2026-10-25, 3d
    
    section Fase 3: Social & Reporting
    Tool SocialMediaManager & Antrean   :2026-10-28, 4d
    Tool Laporan Laba Rugi & AI Advisor :2026-11-01, 3d
    
    section Fase 4: Hardening & Rilis
    Rate Limiting, Kuota & Billing Gate :2026-11-04, 3d
    Dokumentasi Pengguna & Video Panduan:2026-11-07, 3d
```

### Milestone & Deliverables
1. **Milestone 1 (MVP Quick Win):**
   * Endpoint SSE berfungsi.
   * Tool `finance_record_expense` berjalan sempurna: upload gambar nota di AI langsung menerbitkan transaksi pengeluaran dan jurnal akuntansi di COOCA.
2. **Milestone 2 (Katalog & Inventori):**
   * Tool `inventory_create_product` dan `inventory_check_stock` siap pakai.
3. **Milestone 3 (Omnichannel & Social Media):**
   * Penjadwalan postingan media sosial langsung dari chat AI.
   * Laporan ringkasan eksekutif laba-rugi & performa penjualan.

---

> [!TIP]
> Dokumen PRD ini menjadi acuan tunggal (*single source of truth*) dalam pengembangan modul integrasi AI COOCA. Setiap perubahan kode controller dan service harus mematuhi pembatasan isolasi tenant yang tertulis dalam dokumen ini.
