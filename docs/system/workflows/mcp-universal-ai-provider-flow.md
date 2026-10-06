# Alur Kerja Universal Integrasi MCP Server COOCA dengan Berbagai AI Provider

> **Layer 2: Workflow Specification**  
> **Status:** Production-Ready  
> **Target:** Panduan Implementasi Alur Kerja (Workflow) untuk 6 Kategori AI Provider.

---

## 1. Alur Autentikasi & Resolusi Tenant

```
[ AI Client / Provider ]
          │
          │ 1. Kirim Request + Header "Authorization: Bearer cooca_mcp_..."
          ▼
[ EnsureMcpTokenValid Middleware ]
          │
          │ 2. Hash SHA-256 token & cari di tabel mcp_access_tokens
          │ 3. Periksa is_active & expires_at
          ▼
[ McpTokenAuthenticator ]
          │
          │ 4. Bind Tenant: Context::setBusiness($token->business)
          │ 5. Periksa token ability scope
          ▼
[ McpProtocolEngine / REST Bridge ]
          │
          │ 6. Eksekusi Tool (Domain Logic, DB Transaction, CashLedger & AutoJournal)
          │ 7. Audit Log ke mcp_activity_logs
          ▼
[ Response JSON-RPC 2.0 / REST Output ]
```

---

## 2. Panduan Setup per Provider AI

### 2.1 Claude Desktop (Anthropic)
Tambahkan ke berkas konfigurasi `claude_desktop_config.json`:
```json
{
  "mcpServers": {
    "cooca-erp": {
      "command": "php",
      "args": [
        "c:\\laragon\\www\\cooca_core\\artisan",
        "mcp:serve",
        "--token=cooca_mcp_YOUR_SECRET_TOKEN"
      ]
    }
  }
}
```

### 2.2 Cursor IDE & Antigravity IDE
Tambahkan ke `.cursor/mcp.json` di direktori workspace:
```json
{
  "mcpServers": {
    "cooca-erp": {
      "command": "php",
      "args": [
        "artisan",
        "mcp:serve",
        "--token=cooca_mcp_YOUR_SECRET_TOKEN"
      ]
    }
  }
}
```

### 2.3 OpenAI ChatGPT (Custom GPT Actions)
1. Buka ChatGPT GPT Builder -> Tab **Configure** -> **Create new action**.
2. Masukkan URL OpenAPI Schema: `https://your-domain.com/api/v1/mcp/openapi.json`.
3. Pada Authentication Type, pilih **API Key**, Auth Type **Bearer**, masukkan token COOCA MCP.
4. Semua tool otomatis tersedia sebagai actions yang dapat dipanggil oleh ChatGPT.

### 2.4 Google Gemini (Python / REST Bridge)
Gunakan REST Direct Bridge:
```python
import requests

url = "https://your-domain.com/api/v1/mcp/tools/finance_get_cash_and_bank_balances/execute"
headers = {
    "Authorization": "Bearer cooca_mcp_YOUR_SECRET_TOKEN",
    "Content-Type": "application/json"
}

response = requests.post(url, headers=headers, json={})
print(response.json())
```

### 2.5 Ollama & Local Open-Source LLMs (LangChain / LlamaIndex)
Hubungkan via Remote SSE atau Stdio Command menggunakan MCP Client adapter:
```python
from mcp import ClientSession, StdioServerParameters
from mcp.client.stdio import stdio_client

params = StdioServerParameters(
    command="php",
    args=["artisan", "mcp:serve", "--token=cooca_mcp_YOUR_TOKEN"]
)
```

### 2.6 n8n & Dify Workflow Automation
- **n8n:** Gunakan HTTP Request node dengan Method `POST`, endpoint `https://your-domain.com/api/v1/mcp/tools/finance_record_expense/execute`, Header `Authorization: Bearer {token}`.
- **Dify:** Import OpenAPI Spec dari `https://your-domain.com/api/v1/mcp/openapi.json` ke Tools menu.
