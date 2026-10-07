---
name: laravel-permission-audit
description: Mengecek (audit), menambahkan role & permission, memindahkan deklarasi middleware permission dari route ke controller (Controller-Level RBAC), serta mendaftarkan hak akses ke tabel database dan seeder di Laravel (Spatie / Native RBAC / Multi-Tenant). Gunakan skill ini setiap kali user menyebut role, permission, hak akses, RBAC, otorisasi, "pindahkan permission ke controller", "daftarkan permission ke seeder/tabel", "cek akses", "tambah permission", "tambah role", "user tidak bisa akses", "403", atau proteksi modul baru di Laravel.
---

# LARAVEL RBAC, PERMISSION AUDIT & CONTROLLER-LEVEL AUTHORIZATION SKILL

Skill operasional ini memandu AI Agent dalam mengeksekusi **audit keamanan otorisasi, penambahan role & permission baru, migrasi middleware permission dari routes ke controller (Clean Routing & Encapsulated RBAC), serta sinkronisasi hak akses ke tabel database, seeder, dan antarmuka UI/Blade** di seluruh ekosistem Laravel & COOCA ID.

---

## 🧭 File Referensi Pendukung

* [`references/controller-middleware-migration-guide.md`](file:///c:/laragon/www/cooca_core/.agents/skills/laravel-permission/references/controller-middleware-migration-guide.md) — Panduan teknis memindahkan middleware permission dari routes ke Controller (Modern `HasMiddleware` vs Constructor vs Method Guard).
* [`references/rbac-database-and-seeder-matrix.md`](file:///c:/laragon/www/cooca_core/.agents/skills/laravel-permission/references/rbac-database-and-seeder-matrix.md) — Skema database RBAC (`permissions`, `roles`, `permission_role`), Single Source of Truth `RbacSeeder.php`, dan matriks pemetaan role bawaan.
* [`references/permission-audit-and-security-checklist.md`](file:///c:/laragon/www/cooca_core/.agents/skills/laravel-permission/references/permission-audit-and-security-checklist.md) — Protokol audit 7-tahap, mitigasi celah keamanan IDOR, dan skrip pengujian otomatis (Feature Test).

---

## 🏛️ 3 Alur Kerja Utama Skill

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                        3 PILAR OPERASIONAL ROLE & PERMISSION                           │
├─────────────────────────┬──────────────────────────┬───────────────────────────────────┤
│ 1. CEK / AUDIT AKSES    │ 2. MIGRASI KE CONTROLLER │ 3. DAFTARKAN ROLE & PERMISSION    │
│ • Deteksi route bocor   │ • Hapus middleware dari  │ • Daftarkan di RbacSeeder.php     │
│   tanpa proteksi auth   │   routes/owner.php / web │ • Single Source of Truth format   │
│ • Audit celah IDOR      │ • Pasang HasMiddleware   │   'modul.fitur.aksi'              │
│ • Temukan permission    │   interface di Controller│ • Petakan ke System Default Roles │
│   phantom (belum discript) • Peta method 'only: [...]│ • Sinkronkan ke DB & UI Blade   │
└─────────────────────────┴──────────────────────────┴───────────────────────────────────┘
```

---

## 🔍 PILAR 1: Mode Audit Otorisasi (Cek Akses)

Saat user meminta audit otorisasi atau memeriksa route yang rentan:

1. **Pindai Route & Endpoint Tulis:**
   Periksa seluruh route `POST`, `PUT`, `PATCH`, dan `DELETE` di `routes/web.php`, `routes/owner.php`, `routes/api.php`:
   - Pastikan setiap route memiliki penjagaan permission (baik di level route maupun di controller).
   - Deteksi route yang tertinggal atau lupa diberi proteksi (contoh kasus: `material-unit-conversions.store` atau `destroy`).

2. **Audit Celah IDOR (Insecure Direct Object References):**
   Pastikan controller tidak hanya memeriksa permission, tetapi juga memvalidasi kepemilikan tenant:
   ```php
   $business = Context::requireBusiness();
   // Pastikan query dibatasi ke tenant aktif
   $customer = Customer::where('business_id', $business->id)->findOrFail($id);
   ```

3. **Deteksi Phantom & Orphan Permissions:**
   - **Phantom:** Permission dipakai di Controller / Blade (`Context::hasPermission('...')`) tetapi belum terdaftar di `RbacSeeder.php` atau tabel `permissions`.
   - **Orphan:** Permission terdaftar di seeder tetapi tidak pernah dipakai di controller atau view mana pun.

---

## 🚀 PILAR 2: Mode Migrasi Middleware Permission ke Controller

Saat user meminta memindahkan permission dari file route ke controller untuk menghasilkan *Clean Routing*:

### Standar Implementasi: Modern Laravel 11+ `HasMiddleware` Interface

Tambahkan antarmuka `Illuminate\Routing\Controllers\HasMiddleware` pada controller dan petakan permission per method aksi menggunakan `only: [...]`:

```php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Support\Context;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final class ProductCategoryWebController extends Controller implements HasMiddleware
{
    /**
     * Daftarkan middleware permission langsung di dalam controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('require.permission:master_data.product_categories.manage', only: [
                'store',
                'update',
                'destroy',
            ]),
        ];
    }

    // method store, update, destroy...
}
```

### Bersihkan File Route:
Hapus seluruh `->middleware('require.permission:...')` yang sudah dienkapsulasi di controller:

```php
// SEBELUM:
Route::post('/product-categories', [ProductCategoryWebController::class, 'store'])->middleware('require.permission:master_data.product_categories.manage')->name('product-categories.store');

// SESUDAH:
Route::post('/product-categories', [ProductCategoryWebController::class, 'store'])->name('product-categories.store');
```

---

## 🗄️ PILAR 3: Mode Daftarkan Permission & Role ke Database & Seeder

Saat ada modul baru atau permission baru yang perlu didaftarkan:

### 1. Standar Penamaan Permission Hierarkis:
Gunakan format terstandarisasi: **`{modul}.{submodul}.{aksi}`** (huruf kecil, snake_case):
* `master_data.product_categories.view` / `master_data.product_categories.manage`
* `master_data.units.view` / `master_data.units.manage`
* `customers.view` / `customers.create` / `customers.edit` / `customers.delete` / `customers.export`
* `purchasing.view` / `purchasing.manage` / `purchasing.bills`

### 2. Daftarkan di `database/seeders/RbacSeeder.php`:

```php
// 1. Tambahkan ke permissionMatrix()
'master_data.units.manage' => ['Kelola Satuan & Konversi', 'master_data', 'Menambah, mengubah satuan unit dan rumus konversi barang/bahan.'],

// 2. Petakan ke rolePermissionMatrix() untuk System Roles yang berwenang
'admin' => [
    'master_data.product_categories.manage',
    'master_data.units.manage',
    'customers.create',
    'customers.edit',
    'customers.delete',
],
'warehouse' => [
    'master_data.units.view',
    'inventory.manage',
],
```

### 3. Eksekusi Seeder Idempoten:
Jalankan seeder dengan aman tanpa menghapus data custom role bisnis yang ada:
```bash
php artisan db:seed --class=RbacSeeder --force
```

---

## 🖥️ PILAR 4: Sinkronisasi ke Antarmuka UI (Blade & Sidebar)

Otorisasi server adalah benteng utama, sedangkan UI adalah kenyamanan pengguna:

```blade
{{-- Tombol Tambah --}}
@if (\App\Support\Context::hasPermission('customers.create'))
    <button type="button" @click="openModal = true" class="btn btn-primary">
        {{ __('customers.btn_create') }}
    </button>
@endif

{{-- Tombol Hapus --}}
@if (\App\Support\Context::hasPermission('customers.delete'))
    <button type="button" @click="confirmDelete(id)" class="btn btn-danger-soft">
        {{ __('common.delete') }}
    </button>
@endif
```

---

## 🧪 PILAR 5: Pengujian Otomatis Otorisasi (Test Suite)

Setiap controller yang diproteksi wajib memiliki automated feature test:

1. **Skenario Berhak (Authorized):** Request dengan user ber-role `admin`/`owner` $\rightarrow$ Return status `200 OK` atau `302 Redirect` sukses.
2. **Skenario Ditolak (Unauthorized):** Request dengan user ber-role terbatas tanpa permission $\rightarrow$ Return status `403 Forbidden`.
3. **Skenario Guest (Unauthenticated):** Request tanpa login session $\rightarrow$ Return status `302 Redirect` ke halaman login.