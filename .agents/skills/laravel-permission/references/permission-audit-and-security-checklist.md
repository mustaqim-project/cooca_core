# Checklist Audit Keamanan Role & Permission (RBAC Security Checklist)

Dokumen ini memuat protokol audit 7-tahap untuk memeriksa celah otorisasi, memvalidasi enkapsulasi permission di controller, mencegah kebocoran data multi-tenant (IDOR), dan memastikan integritas RBAC di seluruh sistem COOCA.

---

## 🔍 Protokol Audit 7-Tahap

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                        PROTOKOL AUDIT ROLE & PERMISSION LARAVEL                        │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ 1. SCANNING ROUTE & MIDDLEWARE GAP                                                     │
│    • Identifikasi semua route POST/PUT/PATCH/DELETE yang belum memiliki permission.   │
│    • Verifikasi apakah route dilindungi di level route atau di level controller.       │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ 2. CONTROLLER ENCAPSULATION AUDIT                                                      │
│    • Periksa apakah controller mengimplementasikan HasMiddleware atau Method Guard.    │
│    • Pastikan method destruktif ('destroy', 'forceDelete') memiliki permission terpisah.│
├────────────────────────────────────────────────────────────────────────────────────────┤
│ 3. IDOR & MULTI-TENANT ISOLATION AUDIT                                                 │
│    • Periksa apakah controller memanggil Context::requireBusiness().                   │
│    • Validasi bahwa query database menyertakan WHERE business_id = $business->id.      │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ 4. SEEDER & DATABASE REGISTRY AUDIT                                                    │
│    • Bandingkan permission yang dipakai di kode vs yang terdaftar di RbacSeeder.php.   │
│    • Deteksi "Phantom Permissions" (dipakai di controller tapi belum ada di database). │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ 5. UI BLADE & NAVIGATION AUDIT                                                         │
│    • Periksa apakah menu sidebar dan action button dibungkus Context::hasPermission(). │
│    • Pastikan UI guard selaras dengan permission di controller.                        │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ 6. SUPERUSER / OWNER PRIVILEGE AUDIT                                                   │
│    • Verifikasi apakah Owner/Admin tetap tunduk pada pembatasan modul bisnis aktif     │
│      ($business->isPermissionEnabled($permission)).                                    │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ 7. AUTOMATED TEST SUITE AUDIT                                                          │
│    • Buat automated feature test untuk menguji 3 skenario: Berhak, 403 Forbidden, dan  │
│      Unauthenticated (Redirect Login).                                                 │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 📋 Matriks Temuan Celah Umum & Cara Perbaikan

| Kode Celah | Nama Celah | Tingkat Risiko | Contoh Kasus | Cara Perbaikan |
|---|---|---|---|---|
| **RBAC-01** | Route Destruktif Tanpa Permission | 🔴 **Kritis** | `DELETE /material-unit-conversions/{id}` tanpa guard | Pasang `HasMiddleware` di controller dengan permission `master_data.units.manage` |
| **RBAC-02** | Phantom Permission | 🟠 **Tinggi** | Controller memanggil `customers.archive` tapi tidak ada di seeder | Daftarkan di `RbacSeeder::permissionMatrix()` dan jalankan `db:seed` |
| **RBAC-03** | IDOR Multi-Tenant | 🔴 **Kritis** | `Customer::destroy($id)` tanpa cek `business_id` | Tambahkan `where('business_id', $business->id)` sebelum eksekusi delete |
| **RBAC-04** | UI Only Guard | 🟡 **Sedang** | Tombol disembunyikan `@if(hasPermission)` tapi endpoint controller terbuka | Pasang middleware `require.permission` pada method controller terkait |
| **RBAC-05** | Role Name Hardcoded | 🟡 **Sedang** | Pengecekan `$user->hasRole('admin')` di controller | Ganti dengan pengecekan permission `Context::hasPermission('...')` |

---

## 🧪 Skrip Pengujian Otomatis (Feature Test Blueprint)

```php
namespace Tests\Feature\Rbac;

use App\Models\Business;
use App\Models\Customer;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);
    }

    public function test_cashier_without_edit_permission_cannot_update_customer(): void
    {
        $business = Business::factory()->create();
        $cashierUser = User::factory()->create();
        $business->memberships()->create([
            'user_id' => $cashierUser->id,
            'role'    => 'cashier', // Kasir tidak memiliki permission customers.edit
        ]);

        $customer = Customer::factory()->create(['business_id' => $business->id]);

        $response = $this->actingAs($cashierUser)
            ->put(route('customers.update', $customer), [
                'name' => 'Nama Pelanggan Baru',
            ]);

        // Wajib mengembalikan 403 Forbidden atau Redirect dengan Flash Error
        $this->assertTrue(in_array($response->status(), [403, 302], true));
    }
}
```
