Saya akan melakukan audit read-only berbasis source code. Saya mulai dengan memetakan permukaan sistem dan membaca konfigurasi autentikasi/otorisasi, lalu menelusuri modul berisiko tinggi seperti payment, POS, inventory, upload, API, dan AI; tidak ada source code yang akan diubah.

Read memory

Map security attack surface

Read 

Read 

Searched for files matching `cooca_core` · `app/Http/Controllers/**/*.php`, 141 matches

Searched for files matching `cooca_core` · `app/Models/**/*.php`, 134 matches

Searched for files matching `cooca_core` · `database/migrations/*.php`, 97 matches

Read memory

Searched for files matching `cooca_core` · `app/Http/Middleware/*.php`, 7 matches

Searched for regex `BusinessScope|BelongsToBusiness|authorize\(|Gate::|can\(|permission|role|middleware\(` (`**/app/**/*.php`), 751 results

Searched for regex `Storage::|UploadedFile|->store|storeAs|move\(|upload|file\(|image\(|mimes|mimetypes` (`**/app/**/*.php`), 298 results

Read , lines 1 to 260

Read , lines 1 to 320

Read 

Searched for files matching `cooca_core` · `app/Policies/*.php`, 1 match

Searched for regex `Route::(get|post|put|patch|delete)\([^\n]*(receipt|payment|subscription|webhook|admin|storage|download|proof|qr|ai|upload)` (`**/routes/*.php`), 134 results

Read 

Read 

Read 

Read 

Read 

Read 

Read , lines 575 to 689

Read , lines 360 to 555

Read 

Read 

Read , lines 1 to 260

Read 

Read 

Read 

Searched for regex `function (printReceipt|receiptImage)|public function (printReceipt|receiptImage)` (`**/app/Http/Controllers/Web/Pos/PosTerminalWebController.php`), 2 results

Read , lines 250 to 614

No matches found. Your search pattern might be excluded completely by either the search.exclude settings or .*ignore files.
If you believe that it should have results, you can check into the .*ignore files and the exclude setting (here are some excluded patterns for reference:[**/node_modules,**/bower_components,**/*.code-search]).
Then if you want to include those files you can call the tool again by setting "includeIgnoredFiles" to true.

Read 

Read , lines 1 to 420

Read 

Read 

Read 

No matches found. Your search pattern might be excluded completely by either the search.exclude settings or .*ignore files.
If you believe that it should have results, you can check into the .*ignore files and the exclude setting (here are some excluded patterns for reference:[**/node_modules,**/bower_components,**/*.code-search]).
Then if you want to include those files you can call the tool again by setting "includeIgnoredFiles" to true.

Searched for regex `executeActionDraft|action_type|create_invoice|create_quotation` (`**/app/Domain/Ai/AiSalesAnalysisService.php`), 5 results

Read , lines 395 to 475

Searched for regex `withoutGlobalScopes|withoutGlobalScope|->setEagerLoads|DB::table\(|Model::unguard|unserialize\(|eval\(|shell_exec|exec\(|system\(|proc_open|Http::` (`**/app/**/*.php`), 59 results

No matches found. Your search pattern might be excluded completely by either the search.exclude settings or .*ignore files.
If you believe that it should have results, you can check into the .*ignore files and the exclude setting (here are some excluded patterns for reference:[**/node_modules,**/bower_components,**/*.code-search]).
Then if you want to include those files you can call the tool again by setting "includeIgnoredFiles" to true.

Read 

Read 

Read 

Read , lines 900 to 1041

No matches found. Your search pattern might be excluded completely by either the search.exclude settings or .*ignore files.
If you believe that it should have results, you can check into the .*ignore files and the exclude setting (here are some excluded patterns for reference:[**/node_modules,**/bower_components,**/*.code-search]).
Then if you want to include those files you can call the tool again by setting "includeIgnoredFiles" to true.

Searched for regex `->where\(['"]business_id|whereBusiness|business_id.*request|input\(['"]business_id` (`**/app/Http/Controllers/Api/V1/**/*.php`), 8 results

Read , lines 105 to 180

Read , lines 1 to 270

Read 

Read 

Read , lines 80 to 178

Read 

Searched for files matching `cooca_core` · `composer.lock`, 1 match

Searched for files matching `cooca_core` · `package-lock.json`, no matches

No matches found. Your search pattern might be excluded completely by either the search.exclude settings or .*ignore files.
If you believe that it should have results, you can check into the .*ignore files and the exclude setting (here are some excluded patterns for reference:[**/node_modules,**/bower_components,**/*.code-search]).
Then if you want to include those files you can call the tool again by setting "includeIgnoredFiles" to true.

Read 

Read 

Read 

Read 

Read , lines 430 to 760

Read 

Searched for regex `function (voidOrder|refundOrder)|public function (voidOrder|refundOrder)` (`**/app/Domain/Pos/PosOrderService.php`), 2 results

Read , lines 830 to 1045

Searched for regex `validate\(\[|ValidationException|mimes:|image|file` (`**/app/Http/Controllers/Admin/*.php`), 52 results

No matches found. Your search pattern might be excluded completely by either the search.exclude settings or .*ignore files.
If you believe that it should have results, you can check into the .*ignore files and the exclude setting (here are some excluded patterns for reference:[**/node_modules,**/bower_components,**/*.code-search]).
Then if you want to include those files you can call the tool again by setting "includeIgnoredFiles" to true.

Read 

Read , lines 810 to 885

Read 

Read 

No matches found. Your search pattern might be excluded completely by either the search.exclude settings or .*ignore files.
If you believe that it should have results, you can check into the .*ignore files and the exclude setting (here are some excluded patterns for reference:[**/node_modules,**/bower_components,**/*.code-search]).
Then if you want to include those files you can call the tool again by setting "includeIgnoredFiles" to true.

Searched for regex `"name": "(laravel/framework|laravel/sanctum|laravel/socialite|phpoffice/phpspreadsheet|guzzlehttp/guzzle|symfony/.*)"|"version":` (`**/composer.lock`), 175 results

Read , lines 1 to 210

Read 

Read 

Read 

Read 

Read 

Pemetaan awal sudah berjalan dan agen eksplorasi sedang mengumpulkan bukti lintas modul. Sambil itu saya akan mengambil anchor lokal yang paling menentukan: daftar route, middleware/policy, konfigurasi produksi, dependency, serta pola query dan upload supaya temuan akhir dapat ditautkan ke alur data nyata.

Searched for files matching `**/routes/*.php`, 3 matches

Searched for files matching `**/app/Http/Middleware/**/*.php`, 7 matches

Searched for files matching `**/app/Policies/**/*.php`, 1 match

Searched for files matching `**/app/Models/**/*.php`, 20 matches

Searched for files matching `**/app/Services/**/*.php`, 1 match

Searched for files matching `**/composer.json`, 1 match

Searched for files matching `**/package.json`, 1 match

Searched for files matching `**/config/*.php`, 10 matches

Read , lines 1 to 260

Read , lines 1 to 240

Read 

Read 

Read 

Read 

Read 

Read 

Read 

Pemetaan awal menghasilkan beberapa temuan berbukti: webhook WhatsApp dapat terbuka pada fallback default, operasi finansial/POS API tidak konsisten dilindungi permission, PIN supervisor default `1234`, receipt publik berbasis ID, login web tanpa throttle, dan validasi foreign key lintas tenant. Saya lanjutkan verifikasi second-pass pada implementasi controller/service/model agar severity tidak bergantung pada route saja.

Searched for regex `void|refund|verify-pin|cash-ledger|expenses|stock-transfers|require.permission` (`**/routes/api.php`), 27 results

Searched for regex `expectedToken|secret-worker-token|verifySupervisorPin|pos_supervisor_pin|printReceipt|receiptImage` (`**/app/**/*.php`), 18 results

Searched for regex `exists:(locations|chart_of_accounts|products),id|Rule::exists|business_id` (`**/app/Http/Controllers/Api/V1/**/*.php`), 161 results

Searched for regex `Route::(post|put|patch|delete).*login|throttle` (`**/routes/web.php`), 4 results

Read 

Read , lines 1 to 320

Read 

Read 

Read , lines 370 to 550

Read , lines 1 to 420

Read , lines 110 to 155

Read , lines 55 to 115

Read , lines 1 to 260

Read 

Read 

Read 

No matches found. Your search pattern might be excluded completely by either the search.exclude settings or .*ignore files.
If you believe that it should have results, you can check into the .*ignore files and the exclude setting (here are some excluded patterns for reference:[**/node_modules,**/bower_components,**/*.code-search]).
Then if you want to include those files you can call the tool again by setting "includeIgnoredFiles" to true.

Read , lines 1 to 360

Read 

Read 

Read 

Read 

Read , lines 1 to 180

Read 

Second-pass menunjukkan risiko yang lebih besar dari sekadar permission route: parameter `{business:slug}` dipakai tanpa memastikan sama dengan active context, sehingga endpoint member/business berpotensi menerima tenant lain; POS juga mengambil `Product`/`Customer` via `find()` tanpa membatasi business dan menerima nilai finansial tertentu dari request. Saya sedang memisahkan temuan yang benar-benar exploitable dari indikasi yang hanya perlu hardening.

Searched for regex `function (apply|executeActionDraft|askNaturalLanguage)|createInvoice|createQuotation|payload\[|Invoice::create|Quotation::create` (`**/app/Domain/Ai/**/*.php`), 18 results

Searched for regex `Storage::|storeAs|move\(|public_path\(|UploadedFile|file\(|mimes:|mimetypes:` (`**/app/**/*.php`), 149 results

No matches found. Your search pattern might be excluded completely by either the search.exclude settings or .*ignore files.
If you believe that it should have results, you can check into the .*ignore files and the exclude setting (here are some excluded patterns for reference:[**/node_modules,**/bower_components,**/*.code-search]).
Then if you want to include those files you can call the tool again by setting "includeIgnoredFiles" to true.

No matches found. Your search pattern might be excluded completely by either the search.exclude settings or .*ignore files.
If you believe that it should have results, you can check into the .*ignore files and the exclude setting (here are some excluded patterns for reference:[**/node_modules,**/bower_components,**/*.code-search]).
Then if you want to include those files you can call the tool again by setting "includeIgnoredFiles" to true.

Read 

Read 

Read 

Read 

Read , lines 900 to 1041

Read , lines 85 to 155

Read , lines 45 to 180

Read 

Read 

Read 

Read , lines 390 to 428

Read , lines 125 to 380

No matches found. Your search pattern might be excluded completely by either the search.exclude settings or .*ignore files.
If you believe that it should have results, you can check into the .*ignore files and the exclude setting (here are some excluded patterns for reference:[**/node_modules,**/bower_components,**/*.code-search]).
Then if you want to include those files you can call the tool again by setting "includeIgnoredFiles" to true.

No matches found. Your search pattern might be excluded completely by either the search.exclude settings or .*ignore files.
If you believe that it should have results, you can check into the .*ignore files and the exclude setting (here are some excluded patterns for reference:[**/node_modules,**/bower_components,**/*.code-search]).
Then if you want to include those files you can call the tool again by setting "includeIgnoredFiles" to true.

Searched for files matching `**/public/.htaccess`, 1 match

Searched for files matching `**/wa-server/.env*`, no matches

Searched for files matching `**/.env`, no matches

Read , lines 150 to 220

Read , lines 800 to 875

No matches found. Your search pattern might be excluded completely by either the search.exclude settings or .*ignore files.
If you believe that it should have results, you can check into the .*ignore files and the exclude setting (here are some excluded patterns for reference:[**/node_modules,**/bower_components,**/*.code-search]).
Then if you want to include those files you can call the tool again by setting "includeIgnoredFiles" to true.

No matches found. Your search pattern might be excluded completely by either the search.exclude settings or .*ignore files.
If you believe that it should have results, you can check into the .*ignore files and the exclude setting (here are some excluded patterns for reference:[**/node_modules,**/bower_components,**/*.code-search]).
Then if you want to include those files you can call the tool again by setting "includeIgnoredFiles" to true.

Searched for regex `<script|@json|json_encode|data-.*=|\{!!` (`**/resources/views/**/*.blade.php`), 1001 results

No matches found. Your search pattern might be excluded completely by either the search.exclude settings or .*ignore files.
If you believe that it should have results, you can check into the .*ignore files and the exclude setting (here are some excluded patterns for reference:[**/node_modules,**/bower_components,**/*.code-search]).
Then if you want to include those files you can call the tool again by setting "includeIgnoredFiles" to true.

Read , lines 1 to 35

Read 

Kontrol tambahan: password reset memakai mekanisme Laravel standar, password di-hash, stok memakai `lockForUpdate()`, dan query SQL berbahaya tidak terbukti pada pola yang diperiksa. Namun ada temuan baru yang konkret: endpoint AI action tanpa permission dengan `Customer::find`/`Product::find` lintas tenant, serta API member management yang dapat mengubah tenant target dari URL. Saya akan menyelesaikan inventaris dependency/route dan menulis laporan berbasis bukti, tanpa menyentuh source code.

Ran terminal command: composer audit --no-interaction

Ran terminal command: php artisan route:list --json

Ran terminal command: npm audit --omit=dev --package-lock=false

Read 

Ran terminal command: rg -n "expectedToken|secret-worker-token|verifySupervisorPin|validPin|Route::post\('/login'|Route::post\('/admin/login'|Route::post\('/orders/\{posOrder\}/(void|refund)'|Route::post\('/finance/cash-ledger/(inflows|outflows|transfers)'|Route::post\('/expenses'|Route::post\('/transfers'|Customer::find\(|Product::find\(|exists:chart_of_accounts,id|exists:locations,id|exists:products,id|payment-proofs|Content-Security-Policy|session'|token' =>" app/Http/Controllers/Api/V1/WhatsAppWebhookController.php app/Http/Controllers/Web/Pos/PosTerminalWebController.php app/Http/Controllers/Api/V1/Pos/PosOrderController.php app/Http/Controllers/Api/V1/Finance/CashLedgerController.php app/Http/Controllers/Api/V1/Finance/FinanceController.php app/Http/Controllers/Api/V1/Inventory/StockTransferController.php app/Domain/Ai/AiSalesAnalysisService.php routes/api.php routes/web.php config/services.php config/session.php app/Http/Middleware/SecurityHeaders.php app/Domain/Billing/EntitlementService.php
Completed with input: rg -n "expectedToken|secret-worker-token|verifySupervisorPin|validPin|Route::post\('/login'|Route::post\('/admin/login'|Route::post\('/orders/\{posOrder\}/(void|refund)'|Route::post\('/finance/cash-ledger/(inflows|outflows|transfers)'|Route::post\('/expenses'|Route::post\('/transfers'|Customer::find\(|Product::find\(|exists:chart_of_accounts,id|exists:locations,id|exists:products,id|payment-proofs|Content-Security-Policy|session'|token' =>" app/Http/Controllers/Api/V1/WhatsAppWebhookController.php app/Http/Controllers/Web/Pos/PosTerminalWebController.php app/Http/Controllers/Api/V1/Pos/PosOrderController.php app/Http/Controllers/Api/V1/Finance/CashLedgerController.php app/Http/Controllers/Api/V1/Finance/FinanceController.php app/Http/Controllers/Api/V1/Inventory/StockTransferController.php app/Domain/Ai/AiSalesAnalysisService.php routes/api.php routes/web.php config/services.php config/session.php app/Http/Middleware/SecurityHeaders.php app/Domain/Billing/EntitlementService.php

Ran terminal command: Select-String -Path 'app/Http/Controllers/Api/V1/WhatsAppWebhookController.php','app/Http/Controllers/Web/Pos/PosTerminalWebController.php','app/Http/Controllers/Api/V1/Pos/PosOrderController.php','app/Http/Controllers/Api/V1/Finance/CashLedgerController.php','app/Http/Controllers/Api/V1/Finance/FinanceController.php','app/Http/Controllers/Api/V1/Inventory/StockTransferController.php','app/Domain/Ai/AiSalesAnalysisService.php','routes/api.php','routes/web.php','config/services.php','config/session.php','app/Http/Middleware/SecurityHeaders.php','app/Domain/Billing/EntitlementService.php' -Pattern 'expectedToken|secret-worker-token|verifySupervisorPin|validPin|Route::post\(''/login''|Route::post\(''/admin/login''|orders/\{posOrder\}/(void|refund)|finance/cash-ledger/(inflows|outflows|transfers)|post\(''/expenses''|post\(''/transfers''|Customer::find\(|Product::find\(|exists:chart_of_accounts,id|exists:locations,id|exists:products,id|payment-proofs|Content-Security-Policy|''secure''|''http_only''|''same_site''|token'' =>' | Select-Object Path,LineNumber,Line

Audit evidence sudah cukup untuk laporan final dan tidak ada perubahan source code. Validasi dependency juga selesai: `composer audit` dan `npm audit` tidak melaporkan advisory, tetapi npm tidak memiliki lockfile sehingga hasil JavaScript bersifat terbatas; saya mengambil line anchor terakhir untuk membuat setiap temuan dapat ditindaklanjuti.

Ran terminal command: Select-String -Path 'app/Http/Controllers/Api/V1/WhatsAppWebhookController.php','app/Http/Controllers/Web/Pos/PosTerminalWebController.php','app/Http/Controllers/Api/V1/Finance/FinanceController.php','app/Http/Controllers/Api/V1/Inventory/StockTransferController.php','app/Domain/Ai/AiSalesAnalysisService.php','routes/api.php','routes/web.php','config/services.php','config/session.php','app/Http/Middleware/SecurityHeaders.php','app/Domain/Billing/EntitlementService.php' -Pattern 'expectedToken|validPin|exists:chart_of_accounts,id|exists:locations,id|exists:products,id|Customer::find|Product::find|/orders/\{posOrder\}/void|/orders/\{posOrder\}/refund|/finance/cash-ledger/inflows|/finance/cash-ledger/outflows|/finance/cash-ledger/transfers|/expenses|/transfers|payment-proofs|Content-Security-Policy|secure'' =>|http_only'' =>|same_site'' =>' | ForEach-Object { "{0}:{1}: {2}" -f $_.Path.Replace((Get-Location).Path + '',''), $_.LineNumber, $_.Line.Trim() }

Ran terminal command: Select-String -Path 'app/Http/Controllers/Api/V1/MemberController.php','app/Http/Controllers/Api/V1/BusinessController.php','app/Http/Controllers/Api/V1/Pos/PosOrderController.php','app/Domain/Pos/PosOrderService.php','app/Http/Controllers/Api/V1/AuthController.php','app/Http/Controllers/Web/AuthWebController.php','app/Http/Controllers/Admin/AdminAuthController.php','app/Http/Controllers/Web/Pos/PosTerminalWebController.php' -Pattern 'public function (index|store|update|destroy|void|refund|checkout|login)|Business \$business|Customer::find|Product::find|unit_price|discount_amount|payments\.\*\.amount|password|createToken|session\(\)->regenerate' | ForEach-Object { "{0}:{1}: {2}" -f $_.Path.Replace((Get-Location).Path + '',''), $_.LineNumber, $_.Line.Trim() }

# COOCA UMKM SECURITY AUDIT

Audit dilakukan secara read-only berdasarkan source code, routes, middleware, controllers, services, models, konfigurasi, Blade, API, dependency, queue/scheduler, upload, POS, inventory, finance, AI, dan billing. Tidak ada source code atau data yang diubah.

## 1. Executive Summary

Ditemukan beberapa risiko produksi yang dapat dieksploitasi, terutama:

- bypass authorization pada API finansial, POS, inventory, dan AI;
- cross-tenant object access;
- manipulasi transaksi POS;
- webhook WhatsApp tanpa autentikasi pada konfigurasi default;
- PIN supervisor default;
- receipt dan payment proof yang dapat diakses melalui URL publik;
- login web/admin tanpa throttling khusus.

Tidak ditemukan bukti SQL injection langsung, command injection, atau arbitrary executable upload pada source yang diperiksa.

**Overall Security Score: 42/100**

Confidence audit: **Medium-High**. Source utama berhasil diperiksa, tetapi konfigurasi `.env` production, server Apache/LiteSpeed, database grants, dan infrastruktur hosting tidak tersedia.

---

## 2. System Architecture Reviewed

Komponen yang ditemukan:

- Laravel 13 dengan PHP `^8.3`
- Sanctum API token
- Google OAuth melalui Socialite
- Database session/cache/queue
- Multi-business melalui `business_id` dan `BusinessMembership`
- Custom `Context`, `RequirePermission`, `RequireRole`
- POS, inventory, purchasing, sales, finance, CRM
- Manual subscription payment proof
- WhatsApp webhook/gateway
- AI Assistant dan AI action execution
- Public QR ordering dan public receipt
- Upload image, spreadsheet, dan payment proof
- Scheduled commands untuk subscription, AI quota, WhatsApp, dan sitemap

Alur tenant utama:

```text
Request
 -> SetActiveBusinessContext
 -> X-Business-Id / session / user.active_business_id
 -> BusinessMembership validation
 -> Context
 -> Controller/Service
 -> business_id query scope
```

Kontrol ini tidak diterapkan konsisten di semua controller/service.

---

# 3. Findings

## SEC-001 — POS Checkout Menerima Pembayaran Kurang dari Total

**Severity:** CRITICAL  
**CVSS v3.1:** 9.1  
**Affected Module:** POS  
**Affected File:** `PosOrderService.php:83-84`  
**Affected Class:** `PosOrderService`  
**Affected Method:** `checkout()`  
**Affected Route:** `POST /api/v1/pos/terminal/checkout`, web POS checkout  
**Line Number:** 83-84, 120-138, 220-226  

**Attack Scenario:**  
Authenticated cashier mengirim `payments.*.amount = 0` atau jumlah di bawah `finalTotal`.

**Root Cause:**  
Service menghitung:

```php
$changeAmount = max(0.0, $totalPaid - $finalTotal);
```

tetapi tidak menolak transaksi ketika `$totalPaid < $finalTotal`. Order tetap dibuat berstatus `completed`, stok tetap dikurangi, dan transaksi tetap dicatat.

**Impact:**

- barang dapat dijual tanpa pembayaran penuh;
- manipulasi laporan omzet;
- kehilangan stok;
- fraud oleh cashier/staff;
- transaksi dapat dibuat dengan payment kosong atau bernilai nol.

**Proof of Concept aman:**

```json
{
  "items": [
    {
      "product_id": "<valid-product>",
      "product_name": "Product",
      "unit_price": 100000,
      "quantity": 1
    }
  ],
  "payments": [
    {
      "payment_method": "cash",
      "amount": 0
    }
  ]
}
```

**Expected Secure Behavior:**  
Server menolak transaksi apabila total pembayaran lebih kecil daripada total invoice, kecuali statusnya memang `unpaid` atau `credit` dengan aturan khusus.

**Recommended Fix:**  
Validasi total server-side sebelum membuat order. Validasi juga payment method, status kredit, dan idempotency key.

**Priority:** P0  
**Confidence:** High

---

## SEC-002 — POS API Financial Actions Tidak Memiliki Permission

**Severity:** HIGH  
**CVSS v3.1:** 8.1  
**Affected Module:** POS/Finance  
**Affected Files:** `api.php:401-402`, `CashLedgerController.php:36-55`, `PosOrderController.php:92-139`  
**Affected Methods:** `void()`, `refund()`, `inflow()`, `outflow()`, `transfer()`  

**Attack Scenario:**  
User bisnis yang hanya memiliki akses login mengirim request langsung ke endpoint void, refund, atau cash ledger.

**Root Cause:**  
Routes hanya berada di bawah `auth:sanctum`, `throttle`, dan `business.active`. Tidak ada `require.permission` untuk beberapa operasi sensitif.

**Impact:**

- void transaksi;
- refund/return;
- manipulasi cash inflow/outflow;
- transfer antar rekening;
- potensi kerugian finansial.

**Proof of Concept aman:**

```http
POST /api/v1/pos/orders/{known-order-id}/void
Authorization: Bearer <ordinary-member-token>
```

dengan body valid `reason`, tanpa permission supervisor.

**Expected Secure Behavior:**  
Endpoint hanya bisa digunakan role/permission yang sesuai, misalnya `pos.void`, `pos.refund`, dan `finance.cash_manage`.

**Recommended Fix:**  
Tambahkan permission middleware dan server-side policy/service authorization. Jangan mengandalkan UI atau PIN frontend.

**Priority:** P0  
**Confidence:** High

---

## SEC-003 — WhatsApp Webhook Terbuka pada Fallback Default

**Severity:** HIGH  
**CVSS v3.1:** 8.1  
**Affected Module:** WhatsApp  
**Affected File:** `WhatsAppWebhookController.php:22-29`  
**Affected Class:** `WhatsAppWebhookController`  
**Affected Method:** `handle()`  
**Affected Routes:**

- `POST /wa/webhook`
- `POST /wa/admin-webhook`
- `POST /api/v1/wa/webhook`
- `POST /api/v1/wa/admin-webhook`

**Root Cause:**

```php
$expectedToken = config('services.wa_server.token', 'secret-worker-token');

if ($expectedToken !== 'secret-worker-token' && ...)
```

Jika token tidak dikonfigurasi atau masih memakai fallback, request tidak diwajibkan memiliki token.

**Impact:**

- attacker dapat mengubah status session WhatsApp;
- attacker dapat membuat fake incoming message log;
- potensi poisoning terhadap automation dan audit trail.

**Proof of Concept aman:**

```http
POST /api/v1/wa/webhook
Content-Type: application/json

{
  "session": "known-session-id",
  "status": "connected",
  "sender": "0000000000",
  "message": "audit-test"
}
```

**Expected Secure Behavior:**  
Token wajib selalu ada. Aplikasi harus gagal startup atau menolak request jika secret tidak dikonfigurasi.

**Recommended Fix:**  
Hapus fallback secret, gunakan HMAC signature atau mTLS bila memungkinkan, validasi timestamp dan replay nonce, serta rate limit webhook.

**Priority:** P0  
**Confidence:** High

---

## SEC-004 — Cross-Tenant Business Authorization pada Member API

**Severity:** HIGH  
**CVSS v3.1:** 8.1  
**Affected Module:** Multi-Tenant/RBAC  
**Affected File:** `MemberController.php:29-34`  
**Affected Methods:** `index()`, `store()`, `update()`, `destroy()`  

**Affected Routes:**

- `GET /api/v1/businesses/{business:slug}/members`
- `POST /api/v1/businesses/{business:slug}/members`
- `PATCH /api/v1/businesses/{business:slug}/members/{member}`
- `DELETE /api/v1/businesses/{business:slug}/members/{member}`

**Root Cause:**  
Controller menerima `$business` dari route tetapi tidak membandingkannya dengan `Context::requireBusiness()`. Middleware role memeriksa role dari active business, bukan business route target.

**Attack Scenario:**  
User owner/admin di Business A, yang juga menjadi member biasa di Business B, mempertahankan active context A lalu mengirim request dengan slug Business B.

**Impact:**

- member Business B dapat ditambah, diubah, atau dihapus;
- privilege escalation lintas tenant;
- role owner/admin dapat diberikan ke user lain.

**Proof of Concept aman:**

```http
PATCH /api/v1/businesses/<business-B>/members/<user-id>
Authorization: Bearer <token-active-in-business-A>

{"role":"admin"}
```

**Expected Secure Behavior:**  
Authorization harus dilakukan terhadap `$business` dari route, bukan hanya active context.

**Recommended Fix:**  
Pastikan active context dan route business identik atau resolve membership langsung terhadap route business. Tambahkan policy object-level.

**Priority:** P0  
**Confidence:** High

---

## SEC-005 — AI Action Dapat Menggunakan Customer/Product Tenant Lain

**Severity:** HIGH  
**CVSS v3.1:** 8.1  
**Affected Module:** AI/Invoice  
**Affected File:** `AiSalesAnalysisService.php:930-966`  
**Affected Method:** `executeActionDraft()`  
**Affected Route:** `POST /api/v1/ai/execute-action`

**Root Cause:**

```php
Customer::find($payload['customer_id'])
Product::find($payload['product_id'])
```

Object tidak dibatasi dengan `business_id`.

**Attack Scenario:**  
User pada Business B mengirim ID customer/product milik Business A sebagai payload AI action.

**Impact:**

- invoice Business B dapat merujuk customer Business A;
- product dan harga tenant lain dapat masuk ke dokumen;
- cross-tenant data integrity;
- kemungkinan kebocoran data melalui response dan invoice.

**Expected Secure Behavior:**  
Semua object harus di-resolve dengan `where('business_id', $business->id)` dan diverifikasi menggunakan policy.

**Recommended Fix:**  
Gunakan repository tenant-scoped dan authorization sebelum AI tool execution. Tambahkan permission khusus untuk membuat invoice/quotation.

**Priority:** P1  
**Confidence:** High

---

## SEC-006 — POS Product/Customer Lookup Tidak Tenant-Scoped

**Severity:** HIGH  
**CVSS v3.1:** 7.5  
**Affected Module:** POS  
**Affected File:** `PosOrderService.php:120-138`  
**Affected Method:** `checkout()`  

**Root Cause:**

```php
$customer = $customerId ? Customer::find($customerId) : null;
$product = $productId ? Product::find($productId) : null;
```

Tidak ada pengecekan `business_id` pada object yang diterima.

**Impact:**

- order tenant saat ini dapat memakai customer tenant lain;
- product tenant lain dapat dipakai dalam transaksi;
- stok dan laporan dapat menjadi tidak konsisten;
- foreign object dapat masuk ke order tenant berbeda.

**Recommended Fix:**  
Resolve product/customer melalui query yang selalu memuat `business_id`, dan tolak bila tidak cocok.

**Priority:** P1  
**Confidence:** High

---

## SEC-007 — Foreign-Key Validation Lintas Tenant pada Inventory dan Finance

**Severity:** MEDIUM  
**CVSS v3.1:** 6.5  
**Affected Files:**

- `FinanceController.php:133-134`
- `StockTransferController.php:70-75`

**Affected Routes:**

- `POST /api/v1/finance/expenses`
- `POST /api/v1/inventory/transfers`

**Root Cause:**  
Validation menggunakan:

```php
exists:locations,id
exists:products,id
exists:chart_of_accounts,id
```

tanpa constraint business.

**Impact:**

- expense dapat merujuk akun/lokasi tenant lain;
- stock transfer dapat menerima lokasi/product tenant lain;
- laporan dan integritas data tenant rusak.

**Recommended Fix:**  
Gunakan `Rule::exists(...)->where('business_id', $business->id)` dan ulangi validasi di service.

**Priority:** P1  
**Confidence:** High

---

## SEC-008 — POS Supervisor PIN Default `1234` dan Tidak Di-throttle

**Severity:** MEDIUM  
**CVSS v3.1:** 6.5  
**Affected File:** `PosTerminalWebController.php:405-423`  
**Affected Method:** `verifySupervisorPin()`  
**Affected Route:** `POST /pos/verify-pin`

**Root Cause:**

```php
$validPin = $business->pos_supervisor_pin ?? '1234';
```

PIN dibandingkan langsung, tanpa hashing, rate limit, lockout, atau audit trail.

**Impact:**

- semua business tanpa konfigurasi PIN memiliki credential sama;
- brute force mudah dilakukan;
- supervisor authorization dapat disalahgunakan.

**Catatan:** Source belum membuktikan bahwa endpoint void/refund menerima hasil PIN ini sebagai authorization server-side. Risiko yang terbukti adalah credential default dan brute force.

**Recommended Fix:**  
Wajibkan PIN unik, simpan hash, rate limit, lockout, audit log, dan gunakan authorization server-side pada void/refund.

**Priority:** P1  
**Confidence:** High

---

## SEC-009 — Public Receipt Dapat Diakses Berbasis ID

**Severity:** MEDIUM  
**CVSS v3.1:** 5.3  
**Affected File:** `web.php:101-102`, `PosTerminalWebController.php:378-391`  
**Affected Routes:**

- `GET /receipt/{order}`
- `GET /receipt/{order}/image`

**Root Cause:**  
Endpoint tidak menggunakan authentication, signed URL, QR proof, atau secret receipt token.

**Impact:**

- siapa pun yang memperoleh ID order dapat melihat receipt;
- customer, phone, cashier, location, item, payment, dan total dapat terekspos;
- risiko enumeration bergantung pada ID/order identifier.

**Recommended Fix:**  
Gunakan random receipt token, signed temporary URL, atau proof berupa QR token. Batasi field yang ditampilkan.

**Priority:** P1  
**Confidence:** High

---

## SEC-010 — Login Web dan Admin Tidak Memiliki Throttling Khusus

**Severity:** MEDIUM  
**CVSS v3.1:** 6.5  
**Affected Files:**

- `web.php:116`
- `AuthWebController.php:38-64`
- `AdminAuthController.php:30-52`

**Affected Routes:**

- `POST /login`
- `POST /admin/login`

**Root Cause:**  
Tidak ada middleware throttle pada route atau rate limiter khusus pada controller. API login memiliki throttle, tetapi login web/admin tidak.

**Impact:**

- password guessing;
- credential stuffing;
- risiko lebih tinggi terhadap admin panel.

**Recommended Fix:**  
Gunakan limiter berbasis IP + email, exponential backoff, lockout sementara, dan monitoring failed login.

**Priority:** P1  
**Confidence:** High

---

## SEC-011 — Payment Proof Disimpan di Public Web Root

**Severity:** MEDIUM  
**CVSS v3.1:** 5.3  
**Affected File:** `EntitlementService.php:842-849`  
**Affected Method:** `submitPaymentProof()`

**Root Cause:**

```php
$directory = public_path('payment-proofs');
$file->move($directory, $filename);
```

File bukti transfer disimpan di direktori publik.

**Impact:**

- jika path bocor melalui admin email, log, database, backup, atau future endpoint, file dapat diunduh tanpa authorization;
- bukti transfer dapat berisi rekening dan data personal.

**Recommended Fix:**  
Simpan di private disk di luar web root. Sajikan melalui controller terautorisasi dengan temporary download response.

**Priority:** P1  
**Confidence:** High

---

# 4. Authentication Audit

- Password web/API menggunakan hashing Laravel.
- Session regeneration ditemukan setelah login web/admin.
- Password reset memakai Laravel Password Broker.
- Password reset mengubah `remember_token`.
- API login membuat Sanctum token baru setiap login.
- **NO EVIDENCE FOUND** token expiry/ability/scopes pada token API.
- **Finding:** web/admin login tidak memiliki throttling khusus.
- **Finding:** logout API hanya menghapus current token; logout-all tersedia terpisah.
- Email verification tersedia, tetapi dari route yang diperiksa belum terbukti seluruh modul sensitif mewajibkannya.
- Google OAuth menggunakan Socialite dan callback state default Socialite; tidak ditemukan validasi domain email bisnis khusus.

---

# 5. Authorization / RBAC Audit

Kontrol yang ditemukan:

- `RequirePermission`
- `RequireRole`
- `BusinessMembership`
- `Context`
- `BusinessScope`

Masalah:

- permission API tidak konsisten;
- void/refund/cash ledger/expense/AI action memiliki route tanpa permission;
- owner/admin otomatis memperoleh semua permission melalui `RequirePermission.php`;
- business route parameter tidak selalu dibandingkan dengan active context;
- UI hiding tidak digunakan sebagai satu-satunya bukti, tetapi beberapa endpoint memang tidak punya server-side permission.

---

# 6. IDOR/BOLA Audit

Temuan:

- **SEC-004:** member management lintas tenant.
- **SEC-009:** public receipt berbasis order identifier.

Area yang sudah memiliki pengecekan business pada sebagian besar controller:

- POS order show/void/refund;
- customer CRUD;
- stock transfer show/receive;
- invoice show;
- payment settlement;
- purchase order;
- sales return.

**NO EVIDENCE FOUND** untuk confirmed cross-tenant read pada seluruh endpoint tersebut setelah pemeriksaan source yang tersedia.

---

# 7. Database Audit

**NO EVIDENCE FOUND** untuk SQL injection langsung pada query yang diperiksa.

Ditemukan:

- beberapa `orderByRaw` internal pada permission lookup;
- query builder digunakan pada mayoritas controller;
- stock movement memakai transaction dan `lockForUpdate()`;
- foreign key validation tidak selalu tenant-scoped;
- unique/idempotency protection untuk POS order number dan payment callback belum terbukti memadai;
- invoice/order number generation membaca latest record sebelum membuat nomor baru, sehingga concurrent request berpotensi collision tanpa unique constraint yang terlihat pada source yang diperiksa.

---

# 8. POS Audit

Temuan utama:

- pembayaran kurang dari total dapat menghasilkan order completed: **SEC-001**;
- void/refund tanpa permission konsisten: **SEC-002**;
- product/customer lookup tidak tenant-scoped: **SEC-006**;
- frontend mengirim unit price, discount, payment amount, tetapi server hanya sebagian melakukan re-computation;
- custom item dapat memakai harga dari request;
- discount line diterima dan disimpan tanpa batas terhadap subtotal line;
- tidak ditemukan idempotency key pada checkout;
- stock service sudah menggunakan locking, tetapi order creation dan duplicate checkout protection belum lengkap.

---

# 9. Inventory Audit

Kontrol positif:

- `StockService::recordMovement()` memakai database transaction.
- `lockForUpdate()` digunakan untuk stock row.
- Negative stock dapat dicegah berdasarkan konfigurasi bisnis.

Masalah:

- stock transfer menerima object tenant lain melalui validation umum: **SEC-007**.
- transfer creation route tidak memakai permission inventory, sedangkan receive memakai permission.
- duplicate transfer number menggunakan `rand()` dan tidak tampak memiliki uniqueness enforcement yang dapat diverifikasi dari source terbatas.

---

# 10. Finance Audit

Temuan:

- cash inflow/outflow/transfer tanpa permission: **SEC-002**;
- expense API tanpa permission dan foreign key tenant constraint: **SEC-007**;
- expense amount tervalidasi positif;
- journal/ledger server-side dibuat melalui service, tetapi authorization business-role tidak konsisten.

---

# 11. Payment / Midtrans Audit

**NO EVIDENCE FOUND** untuk implementasi Midtrans webhook/signature verification.

Source yang ditemukan menggunakan manual payment proof dan admin approval. Karena tidak ada kode Midtrans:

- tidak dapat dinyatakan signature verification aman;
- tidak dapat dinyatakan order ID/gross amount/fraud status tervalidasi;
- payment proof disimpan pada public web root: **SEC-011**.

Payment activation harus dipastikan hanya terjadi setelah:

1. proof/callback autentik;
2. amount valid;
3. payment belum diproses;
4. status transition valid;
5. operasi idempotent.

---

# 12. Subscription / License Audit

Subscription entitlement dan quota service ditemukan.

**NO EVIDENCE FOUND** bahwa user biasa dapat langsung mengubah subscription status melalui route yang diperiksa.

Namun:

- manual payment flow meningkatkan kebutuhan audit approval;
- payment proof berada pada public root;
- state transition subscription perlu diuji dengan concurrency dan replay;
- tidak ditemukan implementasi Midtrans sehingga payment authenticity belum dapat dinilai.

---

# 13. File Upload Audit

Upload umumnya menggunakan:

- MIME/extension validation;
- ukuran maksimal;
- image dimension validation;
- random Laravel storage filenames.

**NO EVIDENCE FOUND** arbitrary PHP upload pada endpoint image yang diperiksa.

Risiko yang tetap ada:

- SVG diperbolehkan pada logo melalui `SettingWebController.php:66`;
- SVG dapat menjadi stored XSS jika disajikan inline atau diproses browser;
- payment proof berada di public root: **SEC-011**.

---

# 14. XSS / CSRF Audit

CSRF Laravel digunakan untuk web routes dan API memakai Sanctum.

Temuan hardening:

- CSP masih mengizinkan `'unsafe-inline'` untuk script;
- banyak CDN eksternal digunakan;
- Blade memiliki beberapa raw output seperti SVG/icon dan script data;
- tidak ditemukan bukti stored XSS exploit tanpa menelusuri seluruh field CMS/customer/notes.

**NO EVIDENCE FOUND** confirmed XSS pada source yang berhasil diperiksa.

---

# 15. AI Security Audit

Temuan:

- AI routes berada di authenticated tenant context;
- entitlement token diperiksa pada chat;
- action type dibatasi menjadi `create_invoice` dan `create_quotation`;
- **SEC-005:** customer/product dalam AI action tidak tenant-scoped;
- action execution route tidak memiliki permission khusus;
- AI dapat membuat dokumen finansial setelah payload langsung diterima.

AI tidak boleh menjadi jalur bypass permission. Authorization harus dilakukan sebelum tool/action execution.

---

# 16. Infrastructure and Security Headers

Security headers ditemukan pada API:

- `X-Frame-Options: SAMEORIGIN`
- `X-Content-Type-Options: nosniff`
- `Referrer-Policy`
- CSP

Masalah:

- tidak ditemukan `Strict-Transport-Security`;
- tidak ditemukan `Permissions-Policy`;
- CSP memakai `'unsafe-inline'`;
- `X-XSS-Protection` sudah obsolete;
- `SESSION_SECURE_COOKIE` dikontrol environment dan default-nya tidak dipaksa `true`: `session.php:172-185`.

Production wajib memastikan:

```env
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

HTTPS enforcement dan trusted proxy configuration perlu diverifikasi pada hosting.

---

# 17. Dependency Audit

`composer audit --no-interaction`:

```text
No security vulnerability advisories found.
```

Dependency lock yang terlihat:

- Laravel Framework `v13.25.0`
- Sanctum `v4.3.3`
- Socialite `v5.29.0`
- Guzzle `v7.15.3`
- PhpSpreadsheet `v5.9.0`

`npm audit` melaporkan 0 vulnerability, tetapi repository tidak memiliki `package-lock.json`. Karena itu versi frontend tidak reproducible dan hasil audit npm kurang kuat.

**NO EVIDENCE FOUND** advisory aktif dari dependency lock Composer saat audit dilakukan.

---

# 18. Logging and Audit Trail

Logging ditemukan pada beberapa area, termasuk webhook dan exception.

Belum terbukti secara menyeluruh bahwa aktivitas berikut selalu dicatat:

- role change;
- permission change;
- price change;
- refund/void;
- export;
- API token;
- subscription approval;
- stock adjustment.

Webhook saat ini mencatat payload message ke log. Pastikan log tidak menyimpan token, password, payment secret, atau session cookie.

---

# 19. Attack Chains

## Chain 1: Ordinary Member → Financial Loss

```text
Authenticated member
+ no permission on POS refund/void
+ direct order ID
= unauthorized refund/void
```

Dampak: kehilangan stok dan uang.

## Chain 2: Owner Business A → Business B Privilege Escalation

```text
User owns Business A
+ user belongs to Business B
+ route business not matched to active Context
+ role middleware checks active business
= modify Business B members
```

Dampak: takeover role/member pada tenant lain.

## Chain 3: POS Underpayment

```text
Cashier/member access
+ payment amount accepted from request
+ no paid >= total validation
= completed order with insufficient payment
```

Dampak: financial loss dan inventory loss.

## Chain 4: Public Receipt Enumeration

```text
Public receipt route
+ predictable/known order identifier
+ no signed token
= customer/payment data exposure
```

---

# 20. Security Score

| Area | Score |
|---|---:|
| Authentication | 5/10 |
| Authorization | 3/10 |
| Input Validation | 4/10 |
| Database Security | 6/10 |
| API Security | 3/10 |
| Business Logic | 2/10 |
| Payment Security | 4/10 |
| File Security | 5/10 |
| Session Security | 6/10 |
| Infrastructure Configuration | 5/10 |
| Logging/Audit | 4/10 |
| AI Security | 3/10 |

**Overall Security Score: 42/100**

---

# 21. Remediation Roadmap

## P0 — Fix Immediately

1. Tolak POS checkout jika pembayaran kurang dari total.
2. Tambahkan authorization server-side untuk void, refund, cash ledger, expense, inventory transfer, dan AI action.
3. Hapus fallback WhatsApp token `secret-worker-token`.
4. Cocokkan route business dengan active tenant pada member/business API.
5. Tenant-scope seluruh Product, Customer, Location, Account, dan AI payload lookup.

## P1 — Fix Before Production

1. Hapus PIN default `1234`.
2. Hash PIN, rate-limit, lockout, dan audit supervisor actions.
3. Amankan public receipt dengan signed/random token.
4. Pindahkan payment proof keluar dari `public`.
5. Tambahkan login throttling untuk web dan admin.
6. Tambahkan idempotency pada checkout, refund, payment, dan callback.
7. Tambahkan tenant-scoped `Rule::exists`.
8. Pastikan semua approval workflow menggunakan valid state transition.

## P2 — Fix Soon

1. Tambahkan audit trail komprehensif.
2. Tambahkan token expiry, ability, rotation, dan revoke policy.
3. Tambahkan HSTS dan Permissions-Policy.
4. Hapus `'unsafe-inline'` dari CSP secara bertahap.
5. Tambahkan uniqueness constraint dan locking pada nomor invoice/order/transfer.
6. Tambahkan `package-lock.json`.

## P3 — Hardening

1. Database user production dengan least privilege.
2. Monitoring failed login, webhook abuse, export, refund, dan stock adjustment.
3. Private storage untuk seluruh dokumen sensitif.
4. Security regression tests untuk tenant isolation dan permission matrix.

---

# 22. Second-Pass Review

**Initial Findings:**

- webhook fallback authentication;
- POS/finance API permission gap;
- default supervisor PIN;
- public receipt;
- login throttling;
- cross-tenant foreign references.

**Second Pass Findings:**

- POS underpayment dan transaction completion;
- business route versus active context mismatch;
- AI cross-tenant lookup;
- POS product/customer cross-tenant lookup;
- payment proof public storage;
- CSP/session hardening gaps.

**New Findings:**

- SEC-001 sampai SEC-007 dan SEC-011 dikonfirmasi melalui data flow controller/service.
- AI action authorization perlu permission khusus.

**False Positives Removed:**

- SQL injection tidak dilaporkan karena tidak ada bukti query concatenation exploitable.
- arbitrary PHP upload tidak dilaporkan karena upload image menggunakan validation.
- stock race condition tidak dilaporkan sebagai confirmed vulnerability karena `lockForUpdate()` telah digunakan.
- password plaintext tidak dilaporkan karena password di-hash.

**Final Findings:**

- 1 CRITICAL
- 6 HIGH
- 4 MEDIUM
- 0 LOW confirmed
- Informational hardening items pada headers, dependency lock, audit logging, dan Midtrans absence.

**File yang memerlukan perhatian segera:**

- `PosOrderService.php:83-84`
- `PosOrderController.php:92-139`
- `WhatsAppWebhookController.php:22-29`
- `MemberController.php:29-34`
- `AiSalesAnalysisService.php:930-966`
- `CashLedgerController.php:36-55`
- `EntitlementService.php:842-849`
