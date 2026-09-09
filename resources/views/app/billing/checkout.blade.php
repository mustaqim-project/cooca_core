@extends('layouts.app', [
    'title' => 'Checkout Paket & Pembayaran — Cooca UMKM',
    'headerTitle' => 'Pilih Paket & Metode Pembayaran',
    'headerSubtitle' => 'Ikut program Patungan Cooca UMKM untuk akses fitur tanpa batas dan kolaborasi bisnis'
])

@section('content')
@php
    $defaultSelectedCode = $paymentAccounts->first()?->bank_code ?? 'bca';

    // Build duration tiers from packages for subscription type
    $durationTiers = [];
    if ($type === 'subscription') {
        foreach ($packagesData as $pkg) {
            $days = $pkg['duration_days'];
            if ($days <= 0) continue;
            if (!isset($durationTiers[$days])) {
                if ($days <= 31) $label = '1 Bulan';
                elseif ($days <= 62) $label = '2 Bulan';
                elseif ($days <= 95) $label = '3 Bulan';
                elseif ($days <= 185) $label = '6 Bulan';
                elseif ($days <= 370) $label = '1 Tahun';
                elseif ($days <= 740) $label = '2 Tahun';
                else $label = $days . ' Hari';

                $durationTiers[$days] = [
                    'days'  => $days,
                    'label' => $label,
                    'price' => $pkg['price'],
                ];
            } else {
                // keep lowest price for this tier
                if ($pkg['price'] < $durationTiers[$days]['price']) {
                    $durationTiers[$days]['price'] = $pkg['price'];
                }
            }
        }
        ksort($durationTiers);
        $durationTiers = array_values($durationTiers);
    }

    // Also include free/promo packages (price = 0)
    $freePackages = array_values(array_filter($packagesData, fn($p) => $p['price'] <= 0));
    $hasFreePackages = count($freePackages) > 0;

    // Default duration selection: prefer the cycle param, or first tier
    $defaultDurationDays = 30;
    if (!empty($durationTiers)) {
        if ($cycle === 'annual') {
            foreach ($durationTiers as $tier) {
                if ($tier['days'] >= 360) { $defaultDurationDays = $tier['days']; break; }
            }
            if ($defaultDurationDays === 30) $defaultDurationDays = end($durationTiers)['days'];
        } else {
            $defaultDurationDays = $durationTiers[0]['days'];
        }
    }

    // Find first package matching defaultDurationDays
    $defaultPackageId = '';
    foreach ($packagesData as $pkg) {
        if ($pkg['duration_days'] === $defaultDurationDays) {
            $defaultPackageId = $pkg['id'];
            break;
        }
    }
    if (!$defaultPackageId && !empty($packagesData)) {
        $defaultPackageId = $packagesData[0]['id'];
    }
@endphp

<div class="space-y-6 pb-12" x-data="{
    cycle: '{{ $cycle }}',
    orderType: '{{ $type }}',
    selectedDurationDays: {{ $defaultDurationDays }},
    packageId: '{{ $defaultPackageId }}',
    paymentMethod: '{{ $defaultSelectedCode }}',
    monthlyPrice: {{ (int)$monthlyPrice }},
    annualPrice: {{ (int)$annualPrice }},
    topupPrice: {{ (int)$topupPrice }},
    isSubmitting: false,
    durationTiers: {{ \Illuminate\Support\Js::from($durationTiers) }},
    packages: {{ \Illuminate\Support\Js::from($packagesData) }},

    get filteredPackages() {
        if (this.orderType !== 'subscription') return this.packages;
        return this.packages.filter(p => p.duration_days === this.selectedDurationDays || p.price <= 0);
    },

    get currentPrice() {
        const selected = this.packageId ? this.packages.find(item => item.id === this.packageId) : null;
        if (selected) return Number(selected.price);
        return this.orderType === 'subscription'
            ? (this.cycle === 'annual' ? this.annualPrice : this.monthlyPrice)
            : this.topupPrice;
    },

    selectDuration(days) {
        this.selectedDurationDays = days;
        this.cycle = days >= 360 ? 'annual' : 'monthly';
        // Auto-select first package for this duration
        const match = this.packages.find(p => p.duration_days === days);
        if (match) this.packageId = match.id;
    },

    formatRupiah(val) {
        return new Intl.NumberFormat('id-ID').format(val || 0);
    },

    formatDays(days) {
        if (days <= 31) return '1 bln';
        if (days <= 62) return '2 bln';
        if (days <= 95) return '3 bln';
        if (days <= 185) return '6 bln';
        if (days <= 370) return '1 thn';
        if (days <= 740) return '2 thn';
        return days + ' hari';
    }
}">

    <!-- 0. Standard Breadcrumb Bar -->
    <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 print:hidden" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors flex items-center gap-1.5 font-bold text-slate-900 dark:text-white">
            <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
            <span>Dashboard</span>
        </a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600"></i>
        <a href="{{ route('billing.limits') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
            <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
            <span>Langganan &amp; Billing</span>
        </a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600"></i>
        <span class="text-slate-900 dark:text-white font-bold flex items-center gap-1.5">
            <span>Checkout Pembayaran</span>
        </span>
    </nav>

    <!-- 1. Top Header Banner (Seukuran Dashboard Penuh) -->
    <div class="bg-white dark:bg-slate-900/90 p-5 sm:p-6 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 transition-colors">
        <div class="space-y-1.5 max-w-3xl">
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full px-2.5 py-0.5 text-[10px] sm:text-xs font-bold border inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200/90 dark:border-emerald-800/90">
                    <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                    <span>Checkout Langganan &amp; Top Up</span>
                </span>
                <span class="rounded-full px-2.5 py-0.5 text-[10px] sm:text-xs font-bold border inline-flex items-center gap-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 font-mono">
                    Workspace: {{ $business->name }}
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                Pilih Paket &amp; Metode Pembayaran
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                Ikut program Patungan Cooca UMKM untuk akses fitur tanpa batas dan kolaborasi bisnis berlisensi resmi.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
            <!-- Adaptive Step Indicator -->
            <div class="flex items-center gap-2 text-xs bg-slate-100/90 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 px-3 py-1.5 rounded-xl shadow-2xs">
                @if($type === 'subscription')
                <span class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-bold">
                    <span class="w-4 h-4 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px] font-black">1</span>
                    <span class="hidden sm:inline">Durasi</span>
                </span>
                <span class="text-slate-400 dark:text-slate-600" aria-hidden="true">→</span>
                @endif
                <span class="flex items-center gap-1.5 {{ $type === 'subscription' ? 'text-slate-500 dark:text-slate-400' : 'text-emerald-600 dark:text-emerald-400 font-bold' }}">
                    <span class="w-4 h-4 rounded-full {{ $type === 'subscription' ? 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300' : 'bg-emerald-600 text-white' }} flex items-center justify-center text-[10px] font-bold">{{ $type === 'subscription' ? '2' : '1' }}</span>
                    <span class="hidden sm:inline">Paket</span>
                </span>
                <span class="text-slate-400 dark:text-slate-600" aria-hidden="true">→</span>
                <span class="flex items-center gap-1.5 text-slate-400 dark:text-slate-500">
                    <span class="w-4 h-4 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400 flex items-center justify-center text-[10px]">{{ $type === 'subscription' ? '3' : '2' }}</span>
                    <span class="hidden sm:inline">Bayar</span>
                </span>
            </div>

            <a href="{{ route('billing.limits') }}"
                class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/80 transition cursor-pointer shadow-2xs flex items-center justify-center gap-1.5 flex-1 sm:flex-none">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali</span>
            </a>
            <a href="{{ route('billing.history') }}"
                class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/80 transition cursor-pointer shadow-2xs flex items-center justify-center gap-1.5 flex-1 sm:flex-none">
                <i data-lucide="receipt" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                <span>Riwayat Tagihan</span>
            </a>
        </div>
    </div>

    <!-- Mobile Sticky Price Bar -->
    <div class="lg:hidden rounded-2xl p-4 border border-emerald-200 dark:border-emerald-500/30 bg-white/95 dark:bg-slate-900/95 flex items-center justify-between shadow-xs backdrop-blur-md">
        <div>
            <span class="text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 block tracking-wider font-mono">Estimasi Total Tagihan</span>
            <span class="text-lg font-black font-mono text-emerald-600 dark:text-emerald-400" x-text="currentPrice <= 0 ? 'Rp 0 (Gratis Promo)' : ('Rp ' + formatRupiah(currentPrice))"></span>
        </div>
        <div class="text-right">
            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 block">Workspace Aktif</span>
            <span class="text-xs font-bold text-slate-900 dark:text-white truncate max-w-[140px] block font-mono">{{ $business->name }}</span>
        </div>
    </div>

    <!-- Main Checkout Form -->
    <form method="POST" action="{{ route('billing.order.store') }}" @submit="isSubmitting = true" class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 items-start">
        @csrf
        <input type="hidden" name="cycle" :value="cycle">
        <input type="hidden" name="order_type" value="{{ $type }}">
        <input type="hidden" name="package_id" :value="packageId">
        <input type="hidden" name="payment_method" :value="paymentMethod">

        <!-- Left Column: Step Cards (2 cols) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- STEP 1: Duration Selector (Subscription only) -->
            @if($type === 'subscription' && count($durationTiers) > 0)
            <section aria-labelledby="duration-selection-heading" class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-5 sm:p-6 space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 border border-emerald-200 dark:border-emerald-500/30 flex items-center justify-center shrink-0">
                            <span class="text-emerald-700 dark:text-emerald-400 font-black text-sm">1</span>
                        </div>
                        <div>
                            <h3 id="duration-selection-heading" class="text-sm sm:text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                                <i data-lucide="calendar-range" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                                <span>Pilih Durasi Langganan</span>
                            </h3>
                            <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5">Durasi lebih panjang memberikan nilai lebih hemat per bulan.</p>
                        </div>
                    </div>
                    <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold border inline-flex items-center gap-1 bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border-amber-200/90 dark:border-amber-800/90 font-mono uppercase tracking-wider self-start sm:self-auto">
                        Fleksibel · Tanpa Auto-Debet
                    </span>
                </div>

                <!-- Duration Tier Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-{{ count($durationTiers) >= 4 ? '4' : count($durationTiers) }} gap-3" role="radiogroup" aria-label="Pilih Durasi Langganan">
                    @foreach($durationTiers as $idx => $tier)
                    @php
                        $tierMonths = max(1, round($tier['days'] / 30));
                        $perMonth = $tier['price'] > 0 ? round($tier['price'] / $tierMonths) : 0;
                        $isBestValue = false;
                        if (count($durationTiers) > 1 && $tier['price'] > 0) {
                            $minPerMonth = PHP_INT_MAX;
                            foreach ($durationTiers as $t) {
                                if ($t['price'] <= 0) continue;
                                $m = max(1, round($t['days'] / 30));
                                $pm = round($t['price'] / $m);
                                if ($pm < $minPerMonth) $minPerMonth = $pm;
                            }
                            $isBestValue = ($perMonth === $minPerMonth && $idx > 0);
                        }
                    @endphp
                    <div role="radio"
                         tabindex="0"
                         :aria-checked="selectedDurationDays === {{ $tier['days'] }} ? 'true' : 'false'"
                         @click="selectDuration({{ $tier['days'] }})"
                         @keydown.space.prevent="selectDuration({{ $tier['days'] }})"
                         @keydown.enter.prevent="selectDuration({{ $tier['days'] }})"
                         :class="selectedDurationDays === {{ $tier['days'] }} ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/30 ring-1 ring-emerald-500/50 shadow-xs' : 'border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/60 hover:border-slate-300 dark:hover:border-slate-700'"
                         class="cursor-pointer rounded-2xl border p-4 transition-all relative overflow-hidden flex flex-col justify-between group focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:outline-none">

                        @if($isBestValue)
                        <div class="absolute top-0 right-0 px-2 py-0.5 bg-gradient-to-r from-amber-500 to-orange-400 text-white text-[9px] font-black uppercase tracking-wider rounded-bl-xl rounded-tr-2xl">
                            Terbaik
                        </div>
                        @endif

                        <div>
                            <div class="font-black text-slate-900 dark:text-white text-sm sm:text-base transition-colors"
                                 :class="selectedDurationDays === {{ $tier['days'] }} ? 'text-emerald-700 dark:text-emerald-300' : ''">
                                {{ $tier['label'] }}
                            </div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono mt-0.5">{{ $tier['days'] }} hari aktif</div>

                            @if($tier['price'] > 0)
                            <div class="mt-2.5">
                                <div class="text-base sm:text-lg font-black font-mono text-emerald-600 dark:text-emerald-400">
                                    Rp {{ number_format($tier['price'], 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">
                                    ≈ Rp {{ number_format($perMonth, 0, ',', '.') }} / bln
                                </div>
                            </div>
                            @else
                            <div class="mt-2.5">
                                <span class="text-base sm:text-lg font-black font-mono text-amber-600 dark:text-amber-400">Rp 0</span>
                                <div class="text-[10px] font-black text-amber-700 dark:text-amber-300 uppercase tracking-wide mt-0.5">Promo Trial</div>
                            </div>
                            @endif
                        </div>

                        <!-- Radio check indicator -->
                        <div class="mt-3 flex items-center gap-2">
                            <div class="w-4 h-4 rounded-full border border-slate-300 dark:border-slate-700 flex items-center justify-center shrink-0 transition"
                                 :class="selectedDurationDays === {{ $tier['days'] }} ? 'border-emerald-500 bg-emerald-600 text-white' : ''">
                                <div x-show="selectedDurationDays === {{ $tier['days'] }}" class="w-1.5 h-1.5 rounded-full bg-white" aria-hidden="true"></div>
                            </div>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold"
                                  :class="selectedDurationDays === {{ $tier['days'] }} ? 'text-emerald-700 dark:text-emerald-400 font-bold' : ''">
                                {{ $tier['days'] >= 360 ? 'Tahunan' : ($tier['days'] >= 90 ? 'Multi-Bulan' : 'Bulanan') }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if(count($durationTiers) > 1)
                <template x-if="selectedDurationDays > 31">
                    <div class="flex items-start gap-3 p-4 rounded-xl bg-teal-50 dark:bg-teal-950/30 border border-teal-200 dark:border-teal-500/30 text-xs">
                        <i data-lucide="trending-down" class="w-4 h-4 text-teal-600 dark:text-teal-400 shrink-0 mt-0.5" aria-hidden="true"></i>
                        <div class="text-teal-800 dark:text-teal-300 leading-relaxed">
                            <strong class="text-slate-900 dark:text-white">Hemat lebih banyak</strong> dengan durasi yang lebih panjang! Bayar sekali, nikmati akses penuh selama periode aktif tanpa khawatir diperpanjang otomatis. Data bisnis Anda tetap aman meski langganan berakhir.
                        </div>
                    </div>
                </template>
                @endif
            </section>
            @endif

            <!-- STEP 2: Package Selection Cards -->
            @if($packages->isNotEmpty())
            <section aria-labelledby="package-selection-heading" class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-5 sm:p-6 space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-4">
                    <div class="flex items-center gap-3">
                        @if($type === 'subscription')
                        <div class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center shrink-0">
                            <span class="text-slate-600 dark:text-slate-400 font-black text-sm">2</span>
                        </div>
                        @endif
                        <div>
                            <h3 id="package-selection-heading" class="text-sm sm:text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                                <i data-lucide="package" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                                <span>{{ $type === 'subscription' ? 'Pilih Opsi Paket Patungan' : ($type === 'ai_token' ? 'Pilih Paket Top Up Token AI' : 'Pilih Paket Cloud Storage') }}</span>
                            </h3>
                            <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                {{ $type === 'subscription' ? 'Harga resmi program gotong royong UMKM berlisensi penuh.' : 'Paket isi ulang kuota sumber daya bisnis.' }}
                            </p>
                        </div>
                    </div>
                    @if($type === 'subscription')
                    <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold border inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200/90 dark:border-emerald-800/90 uppercase tracking-wider self-start sm:self-auto font-mono">
                        Patungan Terbuka
                    </span>
                    @endif
                </div>

                @if($type === 'subscription' && count($durationTiers) > 0)
                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                    <i data-lucide="filter" class="w-3.5 h-3.5 text-slate-400" aria-hidden="true"></i>
                    <span>Menampilkan paket untuk durasi:</span>
                    <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400"
                          x-text="durationTiers.find(t => t.days === selectedDurationDays)?.label ?? 'Semua'"></span>
                    <span class="text-slate-400">(<span x-text="filteredPackages.filter(p => p.price > 0).length"></span> tersedia)</span>
                </div>
                @endif

                <!-- Package Radio Group -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5" role="radiogroup" aria-label="Pilihan Paket">
                    @foreach($packages as $package)
                    <div role="radio"
                         tabindex="0"
                         :aria-checked="packageId === '{{ $package->id }}' ? 'true' : 'false'"
                         x-show="orderType !== 'subscription' || filteredPackages.some(p => p.id === '{{ $package->id }}')"
                         @click="packageId = '{{ $package->id }}'; cycle = '{{ $package->duration_days >= 360 ? 'annual' : 'monthly' }}'"
                         @keydown.space.prevent="packageId = '{{ $package->id }}'; cycle = '{{ $package->duration_days >= 360 ? 'annual' : 'monthly' }}'"
                         @keydown.enter.prevent="packageId = '{{ $package->id }}'; cycle = '{{ $package->duration_days >= 360 ? 'annual' : 'monthly' }}'"
                         :class="packageId === '{{ $package->id }}' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/30 shadow-xs ring-1 ring-emerald-500/50' : 'border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/60 hover:border-slate-300 dark:hover:border-slate-700'"
                         class="cursor-pointer text-left rounded-2xl border p-4 sm:p-5 transition-all relative overflow-hidden flex flex-col justify-between group focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:outline-none">

                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <span class="font-extrabold text-slate-900 dark:text-white text-sm sm:text-base group-hover:text-emerald-600 dark:group-hover:text-emerald-300 transition-colors">{{ $package->name }}</span>
                                <div class="w-5 h-5 rounded-full border border-slate-300 dark:border-slate-700 shrink-0 flex items-center justify-center transition"
                                     :class="packageId === '{{ $package->id }}' ? 'border-emerald-500 bg-emerald-600 text-white' : ''">
                                    <span x-show="packageId === '{{ $package->id }}'" class="w-2 h-2 rounded-full bg-white" aria-hidden="true"></span>
                                </div>
                            </div>

                            @if($package->price <= 0)
                                <div class="flex items-center gap-2 mt-3">
                                    <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono">Rp 0</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-100 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30 animate-pulse font-mono">
                                        PROMO TRIAL GRATIS
                                    </span>
                                </div>
                            @else
                                <div class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono mt-3">
                                    Rp {{ number_format($package->price, 0, ',', '.') }}
                                </div>
                            @endif

                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1.5 leading-relaxed">{{ $package->description }}</p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80">
                            @if($type === 'subscription')
                                <span class="text-xs font-semibold text-cyan-700 dark:text-cyan-300 flex items-center gap-1.5">
                                    <i data-lucide="clock" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                    <span>Durasi {{ $package->duration_days }} Hari @if($package->price <= 0) (Aktivasi Instan) @endif</span>
                                </span>
                            @elseif($type === 'ai_token')
                                <span class="text-xs font-semibold text-amber-700 dark:text-amber-300 flex items-center gap-1.5">
                                    <i data-lucide="bot" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                    <span>{{ number_format($package->token_quantity, 0, ',', '.') }} Token AI</span>
                                </span>
                            @else
                                <span class="text-xs font-semibold text-cyan-700 dark:text-cyan-300 flex items-center gap-1.5">
                                    <i data-lucide="hard-drive" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                    <span>{{ number_format(($package->storage_bytes ?? 0) / 1073741824, 2, ',', '.') }} GB Permanen</span>
                                </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                @if($type === 'subscription')
                <div x-show="filteredPackages.filter(p => p.price > 0).length === 0" x-cloak
                     class="p-6 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 text-center space-y-2 text-xs text-slate-500 dark:text-slate-400">
                    <i data-lucide="package-x" class="w-8 h-8 text-slate-400 mx-auto" aria-hidden="true"></i>
                    <div class="font-bold text-slate-700 dark:text-slate-300">Belum ada paket untuk durasi ini</div>
                    <div>Silakan pilih durasi lain atau hubungi admin untuk informasi paket tersedia.</div>
                </div>
                @endif
            </section>
            @endif

            <!-- STEP 3: Payment Method Selector (CRITICAL: Contains exact text 'Pilih Metode Pembayaran') -->
            <section aria-labelledby="payment-channels-heading" class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-5 sm:p-6 space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center shrink-0">
                            <span class="text-slate-600 dark:text-slate-400 font-black text-sm">{{ $type === 'subscription' ? '3' : '2' }}</span>
                        </div>
                        <div>
                            <h3 id="payment-channels-heading" class="text-sm sm:text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                                <i data-lucide="wallet" class="w-4 h-4 text-cyan-600 dark:text-cyan-400" aria-hidden="true"></i>
                                <span>Pilih Metode Pembayaran</span>
                            </h3>
                            <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5">Transfer via rekening bank nasional atau scan kode QRIS standar BI.</p>
                        </div>
                    </div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-mono self-start sm:self-auto" x-text="currentPrice <= 0 ? 'Bebas Biaya (Rp 0)' : 'Verifikasi Cepat 5–15 Menit'"></span>
                </div>

                <!-- Free Promo Notice -->
                <div x-show="currentPrice <= 0" x-cloak class="p-4 sm:p-5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-500/40 flex items-start gap-4 shadow-2xs">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 border border-emerald-200 dark:border-emerald-500/30 flex items-center justify-center text-emerald-700 dark:text-emerald-400 shrink-0" aria-hidden="true">
                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                    </div>
                    <div class="space-y-1 text-xs">
                        <h4 class="font-bold text-slate-900 dark:text-white text-sm">Paket Bebas Biaya — Promo Trial Aktif Otomatis</h4>
                        <p class="text-emerald-800 dark:text-emerald-300 leading-relaxed">
                            Anda memilih paket promo khusus (Rp 0). Bisnis Anda <strong>tidak perlu melakukan transfer dana</strong> maupun mengunggah bukti bayar. Fitur Cooca UMKM akan langsung aktif seketika setelah menekan tombol konfirmasi.
                        </p>
                    </div>
                </div>

                <!-- Payment Accounts Gateway List -->
                <div x-show="currentPrice > 0" class="space-y-3" role="radiogroup" aria-label="Metode Pembayaran">
                    @forelse($paymentAccounts as $account)
                    <div role="radio"
                         tabindex="0"
                         :aria-checked="paymentMethod === '{{ $account->bank_code }}' ? 'true' : 'false'"
                         @click="paymentMethod = '{{ $account->bank_code }}'"
                         @keydown.space.prevent="paymentMethod = '{{ $account->bank_code }}'"
                         @keydown.enter.prevent="paymentMethod = '{{ $account->bank_code }}'"
                         class="cursor-pointer rounded-2xl p-4 sm:p-5 border transition-all flex items-center justify-between gap-4 focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:outline-none"
                         :class="paymentMethod === '{{ $account->bank_code }}' ? 'bg-emerald-50/40 dark:bg-slate-900 border-emerald-500 shadow-xs ring-1 ring-emerald-500/40' : 'bg-slate-50/50 dark:bg-slate-950/60 border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'">

                        <div class="flex items-center gap-4 min-w-0">
                            <!-- Bank / Gateway Brand Icon -->
                            <div class="w-12 h-12 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 flex items-center justify-center shrink-0 shadow-2xs">
                                @if($account->type === \App\Models\PaymentAccount::TYPE_QRIS)
                                    <i data-lucide="qr-code" class="w-6 h-6 text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                                @elseif(str_contains(strtolower($account->bank_name), 'bca'))
                                    <span class="font-black text-xs text-blue-600 dark:text-blue-400 font-mono tracking-tighter">BCA</span>
                                @elseif(str_contains(strtolower($account->bank_name), 'mandiri'))
                                    <span class="font-black text-xs text-amber-600 dark:text-amber-400 font-mono tracking-tighter">MANDIRI</span>
                                @elseif(str_contains(strtolower($account->bank_name), 'bri'))
                                    <span class="font-black text-xs text-cyan-600 dark:text-cyan-400 font-mono tracking-tighter">BRI</span>
                                @elseif(str_contains(strtolower($account->bank_name), 'bni'))
                                    <span class="font-black text-xs text-orange-600 dark:text-orange-400 font-mono tracking-tighter">BNI</span>
                                @else
                                    <i data-lucide="{{ $account->icon ?: 'credit-card' }}" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" aria-hidden="true"></i>
                                @endif
                            </div>

                            <div class="min-w-0">
                                <div class="font-extrabold text-sm text-slate-900 dark:text-white flex items-center gap-2 flex-wrap">
                                    <span>{{ $account->bank_name }}</span>
                                    @if($account->type === \App\Models\PaymentAccount::TYPE_QRIS)
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold border inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200/90 dark:border-emerald-800/90 font-mono">
                                            Instant Scan / Semua M-Banking &amp; e-Wallet
                                        </span>
                                    @else
                                        <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider font-mono">Transfer Bank</span>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-mono truncate">
                                    {{ $account->account_number }} · a.n. <strong class="text-slate-700 dark:text-slate-300">{{ $account->account_name }}</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Radio Check Indicator -->
                        <div class="w-5 h-5 rounded-full border border-slate-300 dark:border-slate-700 flex items-center justify-center shrink-0 transition"
                             :class="paymentMethod === '{{ $account->bank_code }}' ? 'border-emerald-500 bg-emerald-600 text-white' : ''">
                            <div x-show="paymentMethod === '{{ $account->bank_code }}'" class="w-2 h-2 rounded-full bg-white" aria-hidden="true"></div>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 text-xs text-center space-y-1">
                        <i data-lucide="alert-circle" class="w-6 h-6 text-slate-400 mx-auto mb-1" aria-hidden="true"></i>
                        <div class="font-bold text-slate-800 dark:text-white">Belum ada rekening pembayaran yang aktif.</div>
                        <div>Silakan hubungi administrator sistem untuk mengaktifkan saluran pembayaran.</div>
                    </div>
                    @endforelse
                </div>
            </section>

        </div>

        <!-- Right Column: Order Summary & Guarantee (Sticky) -->
        <div class="space-y-6 lg:sticky lg:top-24">
            <div class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-5 sm:p-6 space-y-6">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 font-mono">Ringkasan Pesanan</span>
                    <h4 class="text-base sm:text-lg font-black text-slate-900 dark:text-white mt-1">Cooca UMKM — Patungan</h4>
                    <p class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold mt-0.5 flex items-center gap-1.5">
                        <i data-lucide="store" class="w-3.5 h-3.5" aria-hidden="true"></i>
                        <span class="truncate font-mono">Workspace: {{ $business->name }}</span>
                    </p>
                </div>

                <!-- Selected Duration Summary (subscription only) -->
                @if($type === 'subscription' && count($durationTiers) > 0)
                <div class="flex items-center justify-between text-xs border border-slate-200 dark:border-slate-800/60 rounded-xl p-3 bg-slate-50 dark:bg-slate-950/60">
                    <div class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                        <i data-lucide="calendar-check" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0" aria-hidden="true"></i>
                        <span>Durasi dipilih</span>
                    </div>
                    <span class="font-black text-emerald-700 dark:text-emerald-300 font-mono"
                          x-text="durationTiers.find(t => t.days === selectedDurationDays)?.label ?? '-'"></span>
                </div>
                @endif

                <!-- Included Entitlements -->
                <div class="space-y-2.5 text-xs text-slate-600 dark:text-slate-300">
                    <div class="flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" aria-hidden="true"></i>
                        <span>Katalog Produk &amp; Resep Tanpa Batas</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" aria-hidden="true"></i>
                        <span>Multi-Gudang &amp; Multi-Outlet Kasir POS</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" aria-hidden="true"></i>
                        <span>Import &amp; Export Excel Multi-Sheet Lengkap</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" aria-hidden="true"></i>
                        <span>Akses Asisten Pintar AI (Gemini Flash)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" aria-hidden="true"></i>
                        <span>Komitmen <em>No Data Punishment</em> (Aman)</span>
                    </div>
                </div>

                <!-- Price Breakdown -->
                <div class="border-t border-slate-100 dark:border-slate-800 pt-4 space-y-2.5 text-xs">
                    <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                        <span>{{ $type === 'subscription' ? 'Nominal Paket:' : 'Harga Top Up:' }}</span>
                        <span class="font-mono text-slate-900 dark:text-white font-bold">
                            <span x-text="currentPrice <= 0 ? 'Rp 0 (Gratis Promo)' : ('Rp ' + formatRupiah(currentPrice))"></span>
                        </span>
                    </div>
                    <div x-show="currentPrice > 0" class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                        <span>Kode Verifikasi Unik:</span>
                        <span class="font-mono text-amber-600 dark:text-amber-300 text-[11px] font-semibold">3 Digit di invoice</span>
                    </div>
                    <div class="border-t border-slate-200 dark:border-slate-800/80 pt-3 flex items-baseline justify-between">
                        <span class="font-bold text-slate-900 dark:text-white text-sm">Estimasi Total:</span>
                        <span class="font-black text-xl sm:text-2xl text-emerald-600 dark:text-emerald-400 font-mono" x-text="currentPrice <= 0 ? 'Rp 0' : ('Rp ' + formatRupiah(currentPrice))"></span>
                    </div>
                </div>

                <!-- Submit Button with Double-Submit Prevention -->
                <button type="submit"
                        :disabled="isSubmitting"
                        class="w-full py-3 rounded-xl text-xs sm:text-sm font-bold shadow-sm transition-all flex items-center justify-center gap-2 group cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:outline-none"
                        :class="currentPrice <= 0 ? 'bg-amber-500 hover:bg-amber-400 text-slate-950 shadow-amber-500/20' : 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-600/20 active:scale-[0.98]'">
                    <span x-show="isSubmitting" class="animate-spin w-4 h-4 border-2 border-current border-t-transparent rounded-full" aria-hidden="true"></span>
                    <i data-lucide="sparkles" class="w-4 h-4" x-show="!isSubmitting && currentPrice <= 0" aria-hidden="true"></i>
                    <span x-text="isSubmitting ? 'Memproses Pesanan...' : (currentPrice <= 0 ? 'Aktifkan Promo Trial Sekarang (Gratis)' : 'Lanjutkan Pembayaran')"></span>
                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform" x-show="!isSubmitting && currentPrice > 0" aria-hidden="true"></i>
                </button>

                <p class="text-[10px] text-center text-slate-500 dark:text-slate-400 leading-relaxed"
                   x-text="currentPrice <= 0 ? 'Paket trial langsung aktif seketika tanpa perlu bayar maupun menunggu verifikasi manual admin.' : 'Setelah klik tombol di atas, Anda akan mendapatkan nomor rekening resmi dan form upload bukti transfer.'">
                </p>

                <!-- Trust Badges -->
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-center gap-4 text-[10px] text-slate-500 dark:text-slate-400">
                    <span class="flex items-center gap-1">
                        <i data-lucide="lock" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                        <span>Aman &amp; Terenkripsi</span>
                    </span>
                    <span class="flex items-center gap-1">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-cyan-600 dark:text-cyan-400" aria-hidden="true"></i>
                        <span>Data Tidak Dihapus</span>
                    </span>
                    <span class="flex items-center gap-1">
                        <i data-lucide="zap" class="w-3.5 h-3.5 text-amber-500 dark:text-amber-400" aria-hidden="true"></i>
                        <span>Aktivasi Cepat</span>
                    </span>
                </div>
            </div>
        </div>

    </form>
</div>
@endsection
