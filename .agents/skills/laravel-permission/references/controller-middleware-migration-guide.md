# Panduan Migrasi Middleware Permission: Dari Route ke Controller

Dokumen ini memuat standar teknis dan panduan praktis untuk memindahkan deklarasi middleware permission dari file routing (`routes/web.php`, `routes/owner.php`, `routes/api.php`) langsung ke dalam **Controller** di Laravel 10/11/12+ pada ekosistem COOCA ID & POS.

---

## 🎯 1. Mengapa Memindahkan Permission ke Controller?

1. **Clean Routing Architecture:** Menghilangkan tumpukan rantai `->middleware('require.permission:...')` yang panjang dan berulang di file routes, membuat file routing ringkas, deklaratif, dan mudah dibaca.
2. **Encapsulated Security (Benteng Melekat):** Otorisasi melekat langsung pada class/method controller. Jika ada route baru, alias route, atau webhook yang memanggil method tersebut, otorisasi tetap ditegakkan secara otomatis tanpa khawatir developer lupa menambahkan middleware di route.
3. **Granular Action Mapping:** Lebih mudah memetakan permission berbeda untuk setiap method (`index`, `store`, `update`, `destroy`) dalam satu file controller.
4. **Zero Route Pollution:** Memisahkan urusan *routing URL/Name* dengan urusan *internal controller business authorization*.

---

## 🏛️ 2. Pola Implementasi di Controller (Pilih Sesuai Versi Laravel & Kebutuhan)

---

### Pola A: Modern Laravel 11/12+ `HasMiddleware` Interface (Sangat Direkomendasikan ⭐)

Laravel 11+ memperkenalkan antarmuka resmi `Illuminate\Routing\Controllers\HasMiddleware` dan class `Illuminate\Routing\Controllers\Middleware`.

#### Contoh: `CustomerWebController.php`
```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

final class CustomerWebController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            // Memeriksa izin melihat daftar & detail pelanggan
            new Middleware('require.permission:customers.view', only: ['index', 'show']),
            
            // Memeriksa izin membuat pelanggan baru + kuota tenant
            new Middleware(['require.permission:customers.create', 'entitlement:customer'], only: ['store']),
            
            // Memeriksa izin mengubah data pelanggan
            new Middleware('require.permission:customers.edit', only: ['update']),
            
            // Memeriksa izin menghapus pelanggan
            new Middleware('require.permission:customers.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View|RedirectResponse
    {
        $business = Context::requireBusiness();
        // ...
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        // ...
    }

    public function update(Request $request, Customer $customer): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        // ...
    }

    public function destroy(Request $request, Customer $customer): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        // ...
    }
}
```

#### Contoh: `ProductCategoryWebController.php`
```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final class ProductCategoryWebController extends Controller implements HasMiddleware
{
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

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        // ...
    }

    public function update(Request $request, ProductCategory $category): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        // ...
    }

    public function destroy(Request $request, ProductCategory $category): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        // ...
    }
}
```

---

### Pola B: Constructor Middleware (`$this->middleware`) (Kompatibel Laravel 9/10/11)

Jika controller mewarisi `App\Http\Controllers\Controller` klasik:

```php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

final class UnitWebController extends Controller
{
    public function __construct()
    {
        $this->middleware('require.permission:master_data.units.manage')->only([
            'store',
            'update',
            'destroy',
        ]);
    }

    // method store, update, destroy...
}
```

---

### Pola C: In-Method Context Guarding (`Context::requirePermission`)

Untuk proteksi logika yang sangat dinamis atau kondisional di dalam method:

```php
public function destroy(Request $request, Customer $customer): RedirectResponse|JsonResponse
{
    $business = Context::requireBusiness();
    
    // Explicit Guarding
    Context::requirePermission('customers.delete');

    // Validasi kepemilikan tenant (Anti-IDOR)
    if ($customer->business_id !== $business->id) {
        abort(403, 'Anda tidak memiliki akses ke data pelanggan ini.');
    }

    $customer->delete();

    return back()->with('success', 'Pelanggan berhasil dihapus.');
}
```

---

## 🔄 3. Perbandingan Sebelum vs Sesudah di File `routes/owner.php`

### ❌ SEBELUM (Bercampur di file Route):
```php
// Product Categories & Units Master Data
Route::get('/product-categories', [MasterDataWebController::class, 'productCategories'])->middleware('require.permission:master_data.product_categories.view')->name('product-categories.index');
Route::post('/product-categories', [ProductCategoryWebController::class, 'store'])->middleware('require.permission:master_data.product_categories.manage')->name('product-categories.store');
Route::put('/product-categories/{category}', [ProductCategoryWebController::class, 'update'])->middleware('require.permission:master_data.product_categories.manage')->name('product-categories.update');
Route::delete('/product-categories/{category}', [ProductCategoryWebController::class, 'destroy'])->middleware('require.permission:master_data.product_categories.manage')->name('product-categories.destroy');

Route::get('/units', [MasterDataWebController::class, 'units'])->middleware('require.permission:master_data.units.view')->name('units.index');
Route::post('/units', [UnitWebController::class, 'store'])->middleware('require.permission:master_data.units.manage')->name('units.store');
Route::put('/units/{unit}', [UnitWebController::class, 'update'])->middleware('require.permission:master_data.units.manage')->name('units.update');
Route::delete('/units/{unit}', [UnitWebController::class, 'destroy'])->middleware('require.permission:master_data.units.manage')->name('units.destroy');

Route::post('/unit-conversions', [UnitConversionWebController::class, 'store'])->middleware('require.permission:master_data.units.manage')->name('unit-conversions.store');
Route::delete('/unit-conversions/{unitConversion}', [UnitConversionWebController::class, 'destroy'])->middleware('require.permission:master_data.units.manage')->name('unit-conversions.destroy');
Route::post('/material-unit-conversions', [MaterialUnitConversionWebController::class, 'store'])->name('material-unit-conversions.store'); // ⚠️ Celah Keamanan: Belum ada permission
Route::delete('/material-unit-conversions/{materialUnitConversion}', [MaterialUnitConversionWebController::class, 'destroy'])->name('material-unit-conversions.destroy'); // ⚠️ Celah Keamanan

// Customers Management
Route::get('/customers', [CustomerWebController::class, 'index'])->middleware('require.permission:customers.view')->name('customers.index');
Route::post('/customers', [CustomerWebController::class, 'store'])->middleware(['require.permission:customers.create', 'entitlement:customer'])->name('customers.store');
Route::put('/customers/{customer}', [CustomerWebController::class, 'update'])->middleware('require.permission:customers.edit')->name('customers.update');
Route::delete('/customers/{customer}', [CustomerWebController::class, 'destroy'])->middleware('require.permission:customers.delete')->name('customers.destroy');
```

---

### ✅ SESUDAH (Bersih, Rapi & Seluruh Permission Berada di Controller):
```php
// Product Categories & Units Master Data
Route::get('/product-categories', [MasterDataWebController::class, 'productCategories'])->name('product-categories.index');
Route::post('/product-categories', [ProductCategoryWebController::class, 'store'])->name('product-categories.store');
Route::put('/product-categories/{category}', [ProductCategoryWebController::class, 'update'])->name('product-categories.update');
Route::delete('/product-categories/{category}', [ProductCategoryWebController::class, 'destroy'])->name('product-categories.destroy');

Route::get('/units', [MasterDataWebController::class, 'units'])->name('units.index');
Route::post('/units', [UnitWebController::class, 'store'])->name('units.store');
Route::put('/units/{unit}', [UnitWebController::class, 'update'])->name('units.update');
Route::delete('/units/{unit}', [UnitWebController::class, 'destroy'])->name('units.destroy');

Route::post('/unit-conversions', [UnitConversionWebController::class, 'store'])->name('unit-conversions.store');
Route::delete('/unit-conversions/{unitConversion}', [UnitConversionWebController::class, 'destroy'])->name('unit-conversions.destroy');

Route::post('/material-unit-conversions', [MaterialUnitConversionWebController::class, 'store'])->name('material-unit-conversions.store');
Route::delete('/material-unit-conversions/{materialUnitConversion}', [MaterialUnitConversionWebController::class, 'destroy'])->name('material-unit-conversions.destroy');

// Customers Management (Commercial CRM)
Route::get('/customers', [CustomerWebController::class, 'index'])->name('customers.index');
Route::post('/customers', [CustomerWebController::class, 'store'])->name('customers.store');
Route::put('/customers/{customer}', [CustomerWebController::class, 'update'])->name('customers.update');
Route::delete('/customers/{customer}', [CustomerWebController::class, 'destroy'])->name('customers.destroy');
```

---

## 🛡️ 4. Langkah Checklist Migrasi Aman (Zero-Breakage)

1. **Identifikasi Permission yang Dibutuhkan:** Catat seluruh permission yang melekat pada setiap method route.
2. **Buka File Controller Terkait:** Tambahkan `implements HasMiddleware` dan method `public static function middleware(): array`.
3. **Pindahkan Middleware & Atur `only: [...]`:** Pastikan setiap method dipetakan secara presisi ke permission yang benar.
4. **Hapus Deklarasi Middleware di `routes/*.php`:** Bersihkan route dari middleware permission yang sudah dipindahkan.
5. **Jalankan Verifikasi Route:** `php artisan route:list --path=nama-fitur` untuk memastikan route masih terdaftar dengan nama dan aksi yang tepat.
6. **Uji Otorisasi (Test Suite):** Uji akses dengan role yang berwenang (misal `admin` / `owner`) dan role yang dibatasi (misal `cashier`).
