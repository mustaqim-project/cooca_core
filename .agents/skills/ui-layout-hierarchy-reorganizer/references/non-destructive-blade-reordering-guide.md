# Panduan Teknis Penyusunan Ulang Blade & Layout (Non-Destructive Reordering)

Dokumen ini memuat standar teknis untuk menyusun ulang template Blade, Alpine.js, dan HTML secara **100% aman dan non-destruktif** tanpa mematahkan koneksi data backend, variabel Eloquent, atau interaktivitas JavaScript di COOCA ID & POS.

---

## 🛡️ 1. Prinsip Sakral Non-Destruktif

Saat Anda menyusun ulang hierarki tampilan:
```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                        DAFTAR ELEMEN YANG DILARANG DIHAPUS/DIRUBAH                     │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ 1. VARIABEL BLADE      : $orders, $kpis, $business, $summary, $user, $categories       │
│ 2. LOGIKA KONTROL BLADE: @if, @else, @foreach, @forelse, @empty, @can, @auth, @switch  │
│ 3. FORM INPUTS & CSRF  : @csrf, @method('PUT'), name="...", id="...", value="..."      │
│ 4. REACTIVE STATE (JS) : x-data="{...}", @click="...", x-show="...", x-model="..."     │
│ 5. AJAX & ROUTE URLS   : route('...'), url('...'), fetch('...'), headers: {...}        │
│ 6. MODAL & DRAWER IDS  : id="modalCreateCustomer", id="drawerDetailOrder"              │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 🔄 2. Pola Penataan Ulang yang Aman (Cut, Wrap & Place)

Lakukan penyusunan ulang dengan metode **Blok Modular Terenkapsulasi**:

### Langkah 1 — Identifikasi & Isolasi Blok Mandiri
Tandai setiap blok fungsional dengan komentar komentar awal dan akhir yang jelas:
```blade
{{-- [BLOK 1] HEADER & TITLE --}}
...
{{-- [BLOK 2] KPI METRICS --}}
...
{{-- [BLOK 3] FILTER TOOLBAR --}}
...
{{-- [BLOK 4] CHARTS & ANALYTICS --}}
...
{{-- [BLOK 5] DATA TABLE --}}
```

### Langkah 2 — Bungkus dalam Kontainer Grid Baru
Pindahkan blok-blok tersebut ke dalam struktur Bento Grid yang baru tanpa mengubah isi dalam (*inner HTML*) dari blok tersebut:

```blade
@extends('layouts.app', ['title' => __('dashboard.page_title')])

@section('content')
<div class="space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- ========================================================================= --}}
    {{-- ZONA 1: ANCHOR & EXECUTIVE SNAPSHOT                                       --}}
    {{-- ========================================================================= --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
                {{ __('nav.overview') }}
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                    {{ __('dashboard.title') }}
                </h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                    {{ __('dashboard.status_live') }}
                </span>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                {{ __('dashboard.subtitle') }}
            </p>
        </div>

        {{-- Aksi Utama di Kanan Atas --}}
        <div class="flex items-center gap-3">
            @if (\App\Support\Context::hasPermission('pos.terminal'))
                <a href="{{ route('pos.terminal') }}" class="btn btn-primary shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    {{ __('pos.open_cashier') }}
                </a>
            @endif
        </div>
    </div>

    {{-- 4 Kartu Bento KPI --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- KPI 1 --}}
        <div class="bento-card p-5 bg-white dark:bg-[#1C1C1E] border border-gray-200 dark:border-gray-800 rounded-2xl">
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                {{ __('dashboard.kpi_omzet') }}
            </div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-2 tabular-nums">
                {{ format_rupiah($kpi['total_sales'] ?? 0) }}
            </div>
            <div class="text-xs text-emerald-600 dark:text-emerald-400 mt-1 font-medium">
                ↑ +12.5% {{ __('dashboard.vs_last_month') }}
            </div>
        </div>
        {{-- KPI 2, 3, 4 ... --}}
    </div>

    {{-- ========================================================================= --}}
    {{-- ZONA 2: CORE ANALYTICAL & TACTICAL WORKFLOWS                              --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- Grafik Utama (8 Kolom) --}}
        <div class="lg:col-span-8 bg-white dark:bg-[#1C1C1E] border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-gray-100 dark:border-gray-800 gap-3">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">
                    {{ __('dashboard.chart_trend_title') }}
                </h3>
                {{-- Filter Terpadu --}}
                <div class="flex items-center gap-2">
                    @include('app.partials.period-filter')
                </div>
            </div>
            <div class="mt-4 h-72">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>

        {{-- Komposisi & Quick Action (4 Kolom) --}}
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-white dark:bg-[#1C1C1E] border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('dashboard.channel_title') }}
                </h3>
                <div class="h-56">
                    <canvas id="channelDonutChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- ZONA 3: DETAILED LEDGER & OPERATIONAL LIST                                 --}}
    {{-- ========================================================================= --}}
    <div class="bg-white dark:bg-[#1C1C1E] border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <h3 class="text-base font-bold text-gray-900 dark:text-white">
                {{ __('dashboard.recent_transactions') }}
            </h3>
            <a href="{{ route('orders.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">
                {{ __('common.view_all') }} →
            </a>
        </div>
        <div class="overflow-x-auto">
            {{-- Table content --}}
        </div>
    </div>

</div>
@endsection
```

---

## 🧪 3. Checklist Verifikasi Pasca-Reordering

Setelah menyusun ulang layout:
1. [ ] Apakah semua tombol aksi masih memicu event atau route yang benar?
2. [ ] Apakah semua form submit masih mengirim data ke URL yang valid dengan token CSRF?
3. [ ] Apakah modal dialog masih bisa terbuka dan tertutup dengan benar (`x-show` / `bootstrap modal`)?
4. [ ] Apakah tidak ada variabel Blade yang hilang (`Undefined variable` error saat dirender)?
5. [ ] Apakah tampilan rapi, tidak bertumpuk (*zero overlap*), dan proporsional di desktop maupun mobile?
