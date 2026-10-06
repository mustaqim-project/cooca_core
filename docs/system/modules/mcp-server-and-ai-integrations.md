# Modul MCP Server & Universal AI Integrations

> **Layer 2: Architectural Module Specification**  
> **Target:** Arsitektur Server Model Context Protocol (MCP) Multi-Tenant & Adapter Multi-Provider AI.  
> **Status:** Production-Ready  
> **Kepatuhan:** MCP Spec 2024-11-05, OpenAPI 3.1.0, JSON-RPC 2.0, Multi-Tenant Context Isolation.

---

## 1. Ikhtisar & Tujuan Arsitektural

Modul **Model Context Protocol (MCP) Server** COOCA memungkinkan AI Agent eksternal (Claude Desktop, Cursor IDE, Antigravity, OpenAI ChatGPT Custom GPTs, Google Gemini, Ollama Local Models, LangChain, n8n, Dify) untuk berinteraksi langsung secara aman dengan database, ledger, dan mesin bisnis COOCA ERP.

### Prinsip Keamanan & Desain:
1. **Strict Zero-Tenant-Leak Isolation**: Parameter `business_id` **dilarang keras** diekspos ke AI client. AI client dilarang menentukan ID bisnis. Identitas tenant ditentukan 100% dari MCP Bearer Token (`mcp_access_tokens.token_hash`) yang diautentikasi ke `Context::requireBusiness()`.
2. **Double-Entry Accounting & Ledger Integrity**: Setiap pemanggilan tool mutasi (seperti `finance_record_expense`) mengeksekusi `AutoJournalService` dan `CashLedgerService` secara otomatis dalam database transaction atomik.
3. **Universal Multi-Provider Compatibility**:
   - **Local Stdio Transport**: `php artisan mcp:serve` untuk desktop agent lokal (Claude Desktop, Cursor, Antigravity).
   - **Remote SSE Transport**: `GET /api/v1/mcp/sse` + `POST /api/v1/mcp/message` dengan Session ID.
   - **ChatGPT Custom Actions**: Dynamic OpenAPI 3.1.0 spec di `GET /api/v1/mcp/openapi.json`.
   - **REST Direct Bridge**: `POST /api/v1/mcp/tools/{tool}/execute` untuk Gemini, n8n, Dify, Postman.
4. **Audit Logging & Rate Limiting**: Setiap pemanggilan tool dicatat ke `mcp_activity_logs` dengan sanitasi data sensitif (misal: image base64 dipotong), melacak provider hint, waktu eksekusi (ms), dan IP address.

---

## 2. Struktur Database & Skema

### Tabel `mcp_access_tokens`
Menyimpan token akses AI client terenkripsi (SHA-256) per bisnis.

| Kolom | Tipe | Deskripsi |
|---|---|---|
| `id` | UUID | Primary Key |
| `business_id` | UUID | Foreign Key ke `businesses` |
| `user_id` | UUID | Foreign Key ke `users` (Pembuat token) |
| `name` | string | Label token (misal: "Claude Desktop MacBook") |
| `token_hash` | string (64) | Hash SHA-256 dari plaintext token |
| `abilities` | json | Daftar kemampuan yang diizinkan (`['*']`, `['mcp:products:read']`, dsb) |
| `provider_hint` | string | `claude`, `cursor`, `openai`, `gemini`, `ollama`, `custom` |
| `last_used_at` | timestamp | Waktu terakhir token digunakan |
| `expires_at` | timestamp | Waktu kedaluwarsa (opsional) |
| `is_active` | boolean | Saklar aktif/nonaktif instan |

### Tabel `mcp_activity_logs`
Audit trail seluruh aktivitas AI agent.

| Kolom | Tipe | Deskripsi |
|---|---|---|
| `id` | UUID | Primary Key |
| `business_id` | UUID | Foreign Key ke `businesses` |
| `token_id` | UUID | Foreign Key ke `mcp_access_tokens` (nullable) |
| `tool_name` | string | Nama tool MCP yang dipanggil |
| `client_provider`| string | Provider AI pemanggil |
| `arguments_payload`| json | Parameter input (tersanitasi) |
| `response_status` | string | `success` atau `error` |
| `execution_time_ms`| integer | Durasi eksekusi dalam milidetik |
| `ip_address` | string | Alamat IP pemanggil |
| `error_message` | text | Rincian error jika terjadi kegagalan |

---

## 3. Katalog Domain Tools MCP (10 Tools)

| Nama Tool | Ability Scope | Deskripsi Operasional |
|---|---|---|
| `finance_record_expense` | `mcp:expenses:write` | Mencatat beban operasional lengkap dari receipt/struk fisik dengan auto-journal dan cash ledger. |
| `finance_get_cash_and_bank_balances` | `mcp:finance:read` | Membaca likuiditas kas, saldo rekening bank, dan ringkasan mutasi kasir. |
| `inventory_create_product` | `mcp:products:write` | Mendaftarkan produk baru, barcode/SKU, modal HPP, harga jual, dan stok awal. |
| `inventory_check_stock` | `mcp:products:read` | Meneliti stok barang real-time dan deteksi peringatan stok kritis (*low stock*). |
| `social_schedule_post` | `mcp:social:manage` | Menjadwalkan penerbitan materi promosi ke Instagram, TikTok, Facebook, dan X. |
| `social_get_insights` | `mcp:social:read` | Mengambil ringkasan performa kampanye media sosial dan engagement metric. |
| `report_get_profit_loss` | `mcp:reports:read` | Menghitung ringkasan laba rugi: omzet, HPP, laba kotor, beban, dan net margin. |
| `analytics_get_sales_forecast` | `mcp:reports:read` | Memproyeksikan tren omzet masa depan menggunakan AI Forecasting Engine. |
| `crm_search_customer` | `mcp:crm:read` | Mencari data pelanggan loyal, riwayat pembelian, dan status keanggotaan. |
| `whatsapp_send_notification` | `mcp:whatsapp:send` | Mengirimkan notifikasi dan pengingat resmi via WhatsApp Cloud API Meta. |

---

## 4. Format Skema & Multi-Provider Adapters

Class `App\Domain\Mcp\Formatters\McpSchemaFormatter` menyediakan transformasi otomatis:
- `toMcp(array $tools)`: Standar Anthropic MCP Specification `tools/list`.
- `toOpenAi(array $tools)`: Format OpenAI Function Calling (`type: function, function: {...}`).
- `toGemini(array $tools)`: Format Google Gemini `FunctionDeclaration`.
- `toOpenApi3(array $tools, string $serverUrl)`: Spesifikasi OpenAPI 3.1.0 lengkap yang kompatibel dengan ChatGPT Custom GPT Actions, n8n, Dify, dan Swagger.

---

## 5. UI/UX Panel Pengaturan Integrasi AI

Dapat diakses di menu backoffice:
- **URL:** `/settings/integrations/mcp`
- **Desain:** Bento Apple HIG v2.0 (Glassmorphism, Zero-Emoji dengan Lucide Icons, Mobile Action Sheet).
- **Fitur Utama:**
  - Token Management: Buat token baru, salin token aman (sekali tampil), cabut (*revoke*) token.
  - Setup Guide Modal & Tab: Panduan konfigurasi interaktif untuk Claude Desktop (`claude_desktop_config.json`), Cursor IDE (`.cursor/mcp.json`), ChatGPT Actions, Google Gemini Python SDK, Ollama, dan n8n/Dify.
  - Audit Log Realtime: Tabel audit aktivitas AI lengkap dengan status eksekusi dan latency latency tracker.
