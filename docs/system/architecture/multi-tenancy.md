# Arsitektur Multi-Tenancy & Isolasi Data (Multi-Tenant Architecture)

> **Status:** COMPLETE  
> **Komponen Kunci:** `App\Support\Context.php`, Middleware `SetActiveBusiness`, Model `Business`, Model `BusinessUser`  
> **Skema Basis Data:** Shared Database with Tenant Foreign Key Scoping (`business_id`).

---

## 1. Model Multi-Tenancy Cooca

Cooca mengadopsi model **Shared Database with Column-Based Tenant Isolation**. Seluruh tenant (bisnis) berada dalam satu basis data MySQL/PostgreSQL yang sama demi efisiensi biaya infrastruktur dan kecepatan perawatan migrasi, namun dipagari secara ketat pada lapisan ORM dan Middleware.

```
┌─────────────────────────────────────────────────────────────┐
│                    SHARED DATABASE INSTANCE                 │
├─────────────────────────────────────────────────────────────┤
│ TABEL: products, materials, pos_orders, cash_accounts, etc. │
│                                                             │
│  [Row 1: business_id = 101] ───► Diisolasi untuk Toko A     │
│  [Row 2: business_id = 101] ───► Diisolasi untuk Toko A     │
│  [Row 3: business_id = 205] ───► Diisolasi untuk Toko B     │
│  [Row 4: business_id = 308] ───► Diisolasi untuk Toko C     │
└─────────────────────────────────────────────────────────────┘
```

---

## 2. Resolver Konteks Bisnis (`Context::requireBusiness()`)

Setiap *request* yang masuk ke panel operasional pemilik usaha atau kasir melewati middleware yang menetapkan bisnis aktif ke dalam *singleton context*:

```php
namespace App\Support;

class Context
{
    /**
     * Mendapatkan entitas Business aktif atau melempar Exception jika belum diset.
     */
    public static function requireBusiness(): Business
    {
        $business = static::getBusiness();
        
        if (! $business) {
            throw new \RuntimeException('Konteks bisnis aktif tidak ditemukan.');
        }

        return $business;
    }
}
```

### Aturan Baku Query Developer (Do's & Don'ts)

```php
// ✅ BENAR & AMAN (Menggunakan Scoping Tenant Aktif):
$business = \App\Support\Context::requireBusiness();
$orders = Order::where('business_id', $business->id)->latest()->get();

// ✅ BENAR (Melalui Relasi Eloquent):
$products = $business->products()->where('is_active', true)->get();

// ❌ SALAH (BAHAYA! Kebocoran Data Antar Tenant):
$orders = Order::latest()->get(); // MENGAMBIL PESANAN MILIK SELURUH TENANT!
```

---

## 3. Hubungan Pengguna & Multi-Bisnis (Multi-Membership)

Satu akun pengguna (`User`) dapat memiliki atau terdaftar di lebih dari satu bisnis (misal: pengusaha yang memiliki 3 cabang kedai kopi dan 1 salon kecantikan):

```
User (id: 42, Budi Santoso)
  │
  ├── BusinessUser (business_id: 101, role: 'owner') ──► Kopi Cooca Cabang Sudirman
  └── BusinessUser (business_id: 102, role: 'owner') ──► Kopi Cooca Cabang Senopati
```

Pengguna dapat berpindah konteks bisnis secara mulus (*switch business*) melalui antarmuka pemilih bisnis tanpa perlu login ulang.

---

## 4. Hubungan Pelanggan Global vs Tenant Lokal

Untuk pembeli toko online (*Storefront*), sistem memisahkan antara profil global dan data transaksi lokal:
* `GlobalCustomer`: Menyimpan identitas akun, email, password/Google ID, dan nomor WhatsApp.
* `Customer` (Lokal Tenant): Menyimpan data transaksi, limit piutang, poin loyalitas, dan catatan khusus toko tersebut terhadap pelanggan.
* `CustomerCart`: Diikat ke `business_id` toko terkait untuk menjamin keranjang belanja toko A tidak tercampur saat pelanggan membuka toko B.

---

## 5. Konvensi Skema Basis Data (Database Conventions)

1. **Foreign Key `business_id`:** Setiap tabel yang menyimpan data milik tenant wajib memiliki kolom `business_id unsignedBigInteger` yang berelasi ke `businesses.id` dengan onDelete cascade atau restrict sesuai kebutuhan bisnis.
2. **Compound Indexing:** Setiap pencarian yang sering dieksekusi wajib memiliki indeks gabungan yang menyertakan `business_id` di urutan pertama:
   ```php
   $table->index(['business_id', 'status', 'created_at']);
   $table->unique(['business_id', 'code']); // Penomoran kode unik per bisnis
   ```
3. **Audit Trail Tenant:** Setiap log audit sistem menyertakan `business_id` dan `user_id` untuk kemudahan investigasi keamanan.
