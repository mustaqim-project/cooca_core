# Template Rencana Implementasi Sistem Adaptif 20 Industri (Implementation Plan)

Gunakan cetak biru ini saat menyusun rencana teknis implementasi bertahap untuk merealisasikan rekomendasi dari PRD Sistem Adaptif Lintas Industri.

---

```markdown
# Rencana Implementasi Bertahap (Implementation Plan)
## Penyesuaian Antarmuka & Penegakan Do's/Don'ts untuk 20 Sektor Industri

> **Dokumen Terkait:** [PRD-Sistem-Adaptif-[Nama-Modul]](file:///c:/laragon/www/cooca_core/docs/...)  
> **Status:** READY FOR STAGED EXECUTION  
> **Target Modul:** [Nama Modul, contoh: Master Produk, POS Terminal, Order Management]  
> **Prinsip Eksekusi:** Zero Irrelevant Clutter, Zero Regression, 100% Automated Testing.

---

## 📅 1. Roadmap Eksekusi Bertahap (Phased Roadmap)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ FASE 1: AUDIT NAVIGASI & GATING BLADE CONDITIONAL (Hari 1)                  │
│         • Bungkus menu, tab, & bento box dengan isModuleEnabled()           │
├─────────────────────────────────────────────────────────────────────────────┤
│                                      ↓                                      │
│ FASE 2: ADAPTASI FORMULIR & SANITASI STATE ALPINES (Hari 2)                 │
│         • Pastikan modal input tidak menampilkan section industri lain      │
├─────────────────────────────────────────────────────────────────────────────┤
│                                      ↓                                      │
│ FASE 3: VALIDASI BACKEND KONDISIONAL & DYNAMIC TERMINOLOGY (Hari 3)         │
│         • Sesuaikan FormRequest dengan sometimes/when • Helper label dinamis│
├─────────────────────────────────────────────────────────────────────────────┤
│                                      ↓                                      │
│ FASE 4: AUTOMATED TESTING MULTI-INDUSTRI & REGRESI (Hari 4)                 │
│         • Pengujian seeder 20 industri • php artisan test 100% PASS         │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 🗂️ 2. Matriks Berkas Sumber Kode Terdampak

| Berkas Sumber Kode | Lapisan Arsitektur | Rencana Modifikasi Spesifik |
|---|---|---|
| `resources/views/app/[module]/index.blade.php` | UI / Blade View | Bungkus section bento dan tab dengan `@if($business->isModuleEnabled(...))` |
| `resources/views/app/[module]/partials/form.blade.php` | Form Modal | Sembunyikan field input yang tidak relevan dengan industri aktif |
| `app/Http/Requests/[Module]Request.php` | Validation Layer | Jadikan validasi field modular kondisional (`Rule::when(...)`) |
| `app/Domain/Template/ModuleRegistry.php` | Domain Registry | Pastikan pemetaan `templateDisabledModulesMap` mencakup modul baru |
| `app/Support/IndustryHelper.php` | Support Helper | Sediakan helper terminologi dinamis (`IndustryHelper::term('table')`) |
| `tests/Feature/[Module]MultiIndustryTest.php` | Automated Test | Uji perilaku form dan tampilan pada tenant FnB, Bengkel, dan Ritel |

---

## 🛠️ 3. Pola Kode Implementasi Standar

### 1. Pola Gating pada Antarmuka Blade
```html
{{-- Contoh: Hanya tampilkan Bento Resep BOM jika modul aktif --}}
@php
    $biz = \App\Support\Context::business();
    $showBom = $biz ? $biz->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_RECIPE_BOM) : true;
    $showChannelPricing = $biz ? $biz->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_POS_DINEIN) : true;
@endphp

@if($showBom)
    <div class="bento-card ...">
        {{-- Formulir Resep BOM --}}
    </div>
@endif

@if($showChannelPricing)
    <div class="bento-card ...">
        {{-- Formulir Multi-Harga Saluran POS (F&B Delivery) --}}
    </div>
@endif
```

### 2. Pola Validasi Backend Kondisional (FormRequest)
```php
public function rules(): array
{
    $business = \App\Support\Context::requireBusiness();

    $rules = [
        'name' => ['required', 'string', 'max:255'],
        'price' => ['required', 'numeric', 'min:0'],
    ];

    // Hanya validasi resep jika modul resep aktif untuk industri ini
    if ($business->isModuleEnabled(ModuleRegistry::MODULE_RECIPE_BOM)) {
        $rules['materials'] = ['nullable', 'array'];
        $rules['materials.*.id'] = ['required_with:materials', 'uuid', 'exists:materials,id'];
        $rules['materials.*.quantity'] = ['required_with:materials', 'numeric', 'min:0.0001'];
    }

    return $rules;
}
```

---

## 🧪 4. Skenario Pengujian Otomatis (Automated Testing Suite)

Buat file pengujian: `tests/Feature/[Module]MultiIndustryAdaptiveTest.php`.

Minimal uji 3 klaster berbeda:
1. **Tenant F&B (`fnb_resto`)**:
   - Memastikan menu Meja dan KDS muncul.
   - Memastikan form BOM resep tersedia.
   - Memastikan field servis bengkel tidak muncul.
2. **Tenant Bengkel (`service_workshop`)**:
   - Memastikan menu Meja dan KDS TIDAK muncul.
   - Memastikan form resep BOM TIDAK muncul.
   - Memastikan field jasa dan sparepart tersedia.
3. **Tenant Ritel / Minimarket (`retail_reseller`)**:
   - Memastikan scanner barcode dan stok opname tersedia.
   - Memastikan resep masakan dan meja resto TIDAK muncul.

---

## 📋 5. Checklist Verifikasi Akhir

- [ ] Lolos verifikasi sintaks: `php -l [file]`.
- [ ] Route list tidak mengalami exception: `php artisan route:list`.
- [ ] Uji multi-industri lolos 100%: `php artisan test --filter=[Module]MultiIndustryAdaptiveTest`.
- [ ] Tidak ada regresi pada modul lain: `php artisan test`.
- [ ] Catat riwayat pekerjaan ke `docs/AiWorkHistory.md`.
- [ ] Dokumentasikan di `docs/system/` dan `docs/SYSTEM_GUIDE.md`.
```
