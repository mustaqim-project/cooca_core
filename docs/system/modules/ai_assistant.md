# Modul AI Assistant & Multi-Tenant SOP Knowledge Hub

> **Status Modul:** ACTIVE & PRODUCTION READY  
> **Arsitektur:** Hybrid Retrieval-Augmented Generation (RAG) + Multi-Tenant PDF Ingestion + Industry-Aware 20 Sektor  
> **UI Standard:** Bento Apple HIG Modal Sheet XXL (`Ctrl + K` / Global Trigger)

---

## 1. Ringkasan Eksekutif

Modul **COOCA Smart AI Assistant & SOP Knowledge Hub** adalah asisten cerdas berbasis pengetahuan mendalam untuk platform ERP SaaS COOCA. Modul ini menyediakan panduan operasional real-time bagi Business Owner maupun staf/kasir end-user untuk dua domain utama:

1. **SOP Internal Usaha (Multi-Tenant Isolated)**: Business Owner dapat mengunggah dokumen operasional internal usaha dalam bentuk **PDF**. Sistem mengekstrak teks, memecahnya per halaman/bagian (*chunking*), dan mengindeksnya dengan proteksi ketat `WHERE business_id = ?`. Data usaha satu tidak akan pernah bocor ke usaha lain.
2. **Panduan Penggunaan Fitur COOCA (Global Knowledge & 20 Sektor Industri)**: Otomatis membaca dokumentasi resmi (`docs/system/modules/*.md` dan `docs/SYSTEM_GUIDE.md`) secara dinamis (*hot-reload*) serta menyesuaikan instruksi dengan sektor industri tenant aktif (*Bengkel, FnB, Ritel, Laundry, Konveksi, dll.*) dan peran pengguna (*Owner, Kasir, Gudang, Akuntan*).

---

## 2. Arsitektur Komponen Hulu-ke-Hilir

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│ 1. INGESTION LAYER                                                                          │
│  ├─ Tenant SOP: Owner upload PDF di `/settings/sop` -> `TenantSopIngestionService`          │
│  │   └─ Storage: `storage/app/tenants/{id}/sops/` -> DB: `tenant_sop_documents/chunks`      │
│  └─ System Guide: `MarkdownKnowledgeService` (Auto-scan `docs/` & Auto-Cache Invalidation)  │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│ 2. REASONING & RETRIEVAL ENGINE (`AiSystemGuideService`)                                    │
│  ├─ Multi-Stage Filter: Tenant SOP (`business_id`) + Modul Sektor + Role User               │
│  ├─ Hybrid Engine: High-Precision Local Formatter (Offline/Free) + Google Gemini AI Gateway  │
│  └─ Smart Action Generator: Menghasilkan tombol direct link ke halaman setting/fitur        │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│ 3. USER INTERFACE (`bento-ai-assistant-modal.blade.php`)                                    │
│  ├─ Modal Sheet XXL Full-Size (Shortcut `Ctrl + K` / `Cmd + K` & Floating Bubble)          │
│  ├─ Tab Filter: Semua, SOP Usaha Kami, Fitur Sistem COOCA                                   │
│  └─ Halaman Pengelolaan Dokumen SOP Owner (`/settings/sop`)                                 │
└─────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Struktur Database & Model

1. `tenant_sop_documents`:
   * `id` (UUID), `business_id` (FK Businesses), `user_id` (FK Users), `title`, `file_name`, `file_path`, `file_size`, `total_pages`, `total_chunks`, `status` (`processing`, `ready`, `failed`), `error_message`, timestamps.
2. `tenant_sop_chunks`:
   * `id` (UUID), `business_id` (FK Businesses), `document_id` (FK TenantSopDocuments), `page_number`, `section_title`, `content_text`, `keywords`, timestamps.
3. `ai_conversations`:
   * `id` (UUID), `business_id` (FK), `user_id` (FK), `title`, `scope` (`all`, `sop`, `system`), `last_activity_at`, timestamps.
4. `ai_messages`:
   * `id` (UUID), `conversation_id` (FK), `business_id` (FK), `user_id` (FK), `role` (`user`, `assistant`, `system`), `content`, `sources` (JSON), `action_buttons` (JSON), `tokens_used`, timestamps.

---

## 4. Endpoints & Route Mapping

| Method | URI | Controller Action | Name | Middleware / Permission |
| :--- | :--- | :--- | :--- | :--- |
| `POST` | `/assistant/ask` | `AiAssistantWebController@ask` | `assistant.ask` | `auth:web, wa.otp` |
| `GET` | `/assistant/prompts` | `AiAssistantWebController@getPrompts` | `assistant.prompts` | `auth:web, wa.otp` |
| `GET` | `/assistant/history` | `AiAssistantWebController@getHistory` | `assistant.history` | `auth:web, wa.otp` |
| `POST` | `/assistant/clear` | `AiAssistantWebController@clearHistory` | `assistant.clear` | `auth:web, wa.otp` |
| `GET` | `/settings/sop` | `TenantSopWebController@index` | `settings.sop.index` | `require.permission:settings.view` |
| `POST` | `/settings/sop` | `TenantSopWebController@store` | `settings.sop.store` | `require.permission:settings.edit` |
| `DELETE` | `/settings/sop/{id}` | `TenantSopWebController@destroy` | `settings.sop.destroy` | `require.permission:settings.edit` |

---

## 5. Protokol Keamanan & Anti-Leakage Multi-Tenant

1. **Zero Cross-Tenant Leakage**: Setiap pembacaan tabel SOP selalu memfilter `business_id` melalui `Context::requireBusiness()->id`.
2. **Role Content Isolation**: Fitur keuangan strategis atau setting sensitif tidak ditampilkan pada staf operasional tanpa hak akses yang sesuai.
3. **No External Binary Requirement**: Ekstraksi teks PDF menggunakan parser pure-PHP stream decoder dengan fallback text recovery yang aman dan mandiri di server lokal.
