@extends('layouts.app', [
    'title' => 'Checkout Paket & Pembayaran - Cooca UMKM',
    'headerTitle' => 'Pilih Paket & Metode Pembayaran',
    'headerSubtitle' => 'Ikut program Patungan Cooca UMKM untuk akses fitur tanpa batas dan kolaborasi bisnis',
])

@section('content')
    @php
        $defaultSelectedCode = $paymentAccounts->first()?->bank_code ?? 'bca';

        // Build duration tiers from packages for subscription type
        $durationTiers = [];
        if ($type === 'subscription') {
            foreach ($packagesData as $pkg) {
                $days = $pkg['duration_days'];
                if ($days <= 0) {
                    continue;
                }
                if (!isset($durationTiers[$days])) {
                    if ($days <= 31) {
                        $label = '1 Bulan';
                    } elseif ($days <= 62) {
                        $label = '2 Bulan';
                    } elseif ($days <= 95) {
                        $label = '3 Bulan';
                    } elseif ($days <= 185) {
                        $label = '6 Bulan';
                    } elseif ($days <= 370) {
                        $label = '1 Tahun';
                    } elseif ($days <= 740) {
                        $label = '2 Tahun';
                    } else {
                        $label = $days . ' Hari';
                    }

                    $durationTiers[$days] = [
                        'days' => $days,
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

        if ($type === 'subscription' && empty($durationTiers)) {
            $durationTiers = [
                [
                    'days' => 30,
                    'label' => '1 Bulan',
                    'price' => (float) $monthlyPrice,
                ],
                [
                    'days' => 365,
                    'label' => '1 Tahun',
                    'price' => (float) $annualPrice,
                ],
            ];
        }

        // Also include free/promo packages (price = 0)
        $freePackages = array_values(array_filter($packagesData, fn($p) => $p['price'] <= 0));
        $hasFreePackages = count($freePackages) > 0;

        // Default duration selection: prefer the cycle param, or first tier
        $defaultDurationDays = 30;
        if (!empty($durationTiers)) {
            if ($cycle === 'annual') {
                foreach ($durationTiers as $tier) {
                    if ($tier['days'] >= 360) {
                        $defaultDurationDays = $tier['days'];
                        break;
                    }
                }
                if ($defaultDurationDays === 30) {
                    $defaultDurationDays = end($durationTiers)['days'];
                }
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

    <div class="space-y-6 pb-28 lg:pb-10" x-data="{
        cycle: '{{ $cycle }}',
        orderType: '{{ $type }}',
        selectedDurationDays: {{ $defaultDurationDays }},
        packageId: '{{ $defaultPackageId }}',
        paymentMethod: '{{ $defaultSelectedCode }}',
        monthlyPrice: {{ (int) $monthlyPrice }},
        annualPrice: {{ (int) $annualPrice }},
        topupPrice: {{ (int) $topupPrice }},
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
            return this.orderType === 'subscription' ?
                (this.cycle === 'annual' ? this.annualPrice : this.monthlyPrice) :
                this.topupPrice;
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
        <nav class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 print:hidden" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}"
                class="hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition-colors flex items-center gap-1.5 font-medium text-black dark:text-white">
                <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                <span>Dashboard</span>
            </a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-gray-400 dark:text-gray-600"></i>
            <a href="{{ route('billing.limits') }}"
                class="hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition-colors flex items-center gap-1.5 text-gray-500 dark:text-gray-400">
                <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
                <span>Langganan &amp; Billing</span>
            </a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-gray-400 dark:text-gray-600"></i>
            <span class="text-black dark:text-white font-semibold flex items-center gap-1.5">
                <span>Checkout Pembayaran</span>
            </span>
        </nav>

        <!-- 1. Top Header Banner -->
        <div
            class="bg-white dark:bg-[#1C1C1E] p-5 sm:p-6 rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 transition-all">
            <div class="space-y-1 max-w-3xl">
                <div class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 font-mono">
                    Workspace: {{ $business->name }}
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-black dark:text-white tracking-tight">
                    Pilih Paket &amp; Metode Pembayaran
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                    Ikut program Patungan Cooca UMKM untuk akses fitur tanpa batas dan kolaborasi bisnis berlisensi resmi.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
                <!-- Adaptive Step Indicator (Apple Pill Style) -->
                <div
                    class="inline-flex items-center gap-1.5 text-xs bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.04] dark:border-white/[0.06] px-3 py-1.5 rounded-[12px]">
                    @if ($type === 'subscription')
                        <span class="flex items-center gap-1 text-[#007AFF] font-semibold">
                            <span
                                class="w-4 h-4 rounded-full bg-[#007AFF] text-white flex items-center justify-center text-[10px] font-bold">1</span>
                            <span class="hidden sm:inline">Durasi</span>
                        </span>
                        <span class="text-black/30 dark:text-white/30" aria-hidden="true">→</span>
                    @endif
                    <span
                        class="flex items-center gap-1 {{ $type === 'subscription' ? 'text-gray-500 dark:text-gray-400' : 'text-[#007AFF] font-semibold' }}">
                        <span
                            class="w-4 h-4 rounded-full {{ $type === 'subscription' ? 'bg-black/10 dark:bg-white/10 text-gray-700 dark:text-gray-300' : 'bg-[#007AFF] text-white' }} flex items-center justify-center text-[10px] font-bold">{{ $type === 'subscription' ? '2' : '1' }}</span>
                        <span class="hidden sm:inline">Paket</span>
                    </span>
                    <span class="text-black/30 dark:text-white/30" aria-hidden="true">→</span>
                    <span class="flex items-center gap-1 text-gray-400 dark:text-gray-500">
                        <span
                            class="w-4 h-4 rounded-full bg-black/10 dark:bg-white/10 text-gray-500 dark:text-gray-400 flex items-center justify-center text-[10px] font-semibold">{{ $type === 'subscription' ? '3' : '2' }}</span>
                        <span class="hidden sm:inline">Bayar</span>
                    </span>
                </div>

                <a href="{{ route('billing.limits') }}"
                    class="h-10 px-3.5 rounded-[12px] text-xs font-semibold text-gray-700 dark:text-gray-300 bg-black/[0.03] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] hover:bg-black/[0.06] dark:hover:bg-white/[0.1] active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-1.5 flex-1 sm:flex-none">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Kembali</span>
                </a>
                <a href="{{ route('billing.history') }}"
                    class="h-10 px-3.5 rounded-[12px] text-xs font-semibold text-gray-700 dark:text-gray-300 bg-black/[0.03] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] hover:bg-black/[0.06] dark:hover:bg-white/[0.1] active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-1.5 flex-1 sm:flex-none">
                    <i data-lucide="receipt" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Riwayat Tagihan</span>
                </a>
            </div>
        </div>

        <!-- Mobile Sticky Price Bar -->
        <div
            class="lg:hidden rounded-[16px] p-4 border border-black/[0.06] dark:border-white/[0.08] bg-white/90 dark:bg-[#1C1C1E]/90 flex items-center justify-between shadow-[0_2px_8px_rgba(0,0,0,0.04)] backdrop-blur-md">
            <div>
                <span
                    class="text-[10px] uppercase font-semibold text-gray-500 dark:text-gray-400 block tracking-wider font-mono">Estimasi
                    Total Tagihan</span>
                <span class="text-lg font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF]"
                    x-text="currentPrice <= 0 ? 'Rp 0 (Gratis Promo)' : ('Rp ' + formatRupiah(currentPrice))"></span>
            </div>
            <div class="text-right">
                <span class="text-[10px] font-medium text-gray-500 dark:text-gray-400 block">Workspace Aktif</span>
                <span
                    class="text-xs font-semibold text-black dark:text-white truncate max-w-[140px] block font-mono">{{ $business->name }}</span>
            </div>
        </div>

        <!-- Main Checkout Form -->
        <form method="POST" action="{{ route('billing.order.store') }}" @submit="isSubmitting = true"
            class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 items-start">
            @csrf
            <input type="hidden" name="cycle" :value="cycle">
            <input type="hidden" name="order_type" value="{{ $type }}">
            <input type="hidden" name="package_id" :value="packageId">
            <input type="hidden" name="payment_method" :value="paymentMethod">

            <!-- Left Column: Step Cards (2 cols) -->
            <div class="lg:col-span-2 space-y-6">

                <!-- STEP 1: Duration Selector (Subscription only) -->
                @if ($type === 'subscription' && count($durationTiers) > 0)
                    <section aria-labelledby="duration-selection-heading"
                        class="bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] p-5 sm:p-6 space-y-5">
                        <div
                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-[10px] bg-blue-50 dark:bg-blue-900/30 border border-blue-200/60 dark:border-blue-800/60 flex items-center justify-center shrink-0">
                                    <span class="text-[#007AFF] font-bold text-sm">1</span>
                                </div>
                                <div>
                                    <h3 id="duration-selection-heading"
                                        class="text-sm sm:text-base font-bold text-black dark:text-white flex items-center gap-2">
                                        <i data-lucide="calendar-range"
                                            class="w-4 h-4 text-[#007AFF]" aria-hidden="true"></i>
                                        <span>Pilih Durasi Langganan</span>
                                    </h3>
                                    <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 mt-0.5">Durasi lebih
                                        panjang memberikan nilai lebih hemat per bulan.</p>
                                </div>
                            </div>
                            <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 font-mono self-start sm:self-auto">
                                Fleksibel · Tanpa Auto-Debet
                            </span>
                        </div>

                        <!-- Duration Tier Grid -->
                        <div class="grid grid-cols-2 sm:grid-cols-{{ count($durationTiers) >= 4 ? '4' : count($durationTiers) }} gap-3"
                            role="radiogroup" aria-label="Pilih Durasi Langganan">
                            @foreach ($durationTiers as $idx => $tier)
                                @php
                                    $tierMonths = max(1, round($tier['days'] / 30));
                                    $perMonth = $tier['price'] > 0 ? round($tier['price'] / $tierMonths) : 0;
                                    $isBestValue = false;
                                    if (count($durationTiers) > 1 && $tier['price'] > 0) {
                                        $minPerMonth = PHP_INT_MAX;
                                        foreach ($durationTiers as $t) {
                                            if ($t['price'] <= 0) {
                                                continue;
                                            }
                                            $m = max(1, round($t['days'] / 30));
                                            $pm = round($t['price'] / $m);
                                            if ($pm < $minPerMonth) {
                                                $minPerMonth = $pm;
                                            }
                                        }
                                        $isBestValue = $perMonth === $minPerMonth && $idx > 0;
                                    }
                                @endphp
                                <div role="radio" tabindex="0"
                                    :aria-checked="selectedDurationDays === {{ $tier['days'] }} ? 'true' : 'false'"
                                    @click="selectDuration({{ $tier['days'] }})"
                                    @keydown.space.prevent="selectDuration({{ $tier['days'] }})"
                                    @keydown.enter.prevent="selectDuration({{ $tier['days'] }})"
                                    :class="selectedDurationDays === {{ $tier['days'] }} ?
                                        'border-[#007AFF] bg-blue-50/40 dark:bg-blue-900/20 ring-1 ring-[#007AFF]' :
                                        'border-black/[0.06] dark:border-white/[0.08] bg-black/[0.01] dark:bg-white/[0.02] hover:border-black/[0.12] dark:hover:border-white/[0.14]'"
                                    class="cursor-pointer rounded-[16px] border p-4 transition-all relative overflow-hidden flex flex-col justify-between group active:scale-[0.98] focus-visible:ring-2 focus-visible:ring-[#007AFF] focus-visible:outline-none">

                                    @if ($isBestValue)
                                        <div
                                            class="absolute top-0 right-0 px-2 py-0.5 bg-[#FF9500] text-white text-[9px] font-bold uppercase tracking-wider rounded-bl-[10px]">
                                            Terbaik
                                        </div>
                                    @endif

                                    <div>
                                        <div class="font-bold text-black dark:text-white text-sm sm:text-base transition-colors"
                                            :class="selectedDurationDays === {{ $tier['days'] }} ?
                                                'text-[#007AFF] dark:text-[#0A84FF]' : ''">
                                            {{ $tier['label'] }}
                                        </div>
                                        <div class="text-[10px] text-gray-500 dark:text-gray-400 font-mono mt-0.5">
                                            {{ $tier['days'] }} hari aktif</div>

                                        @if ($tier['price'] > 0)
                                            <div class="mt-2.5">
                                                <div
                                                    class="text-base sm:text-lg font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF]">
                                                    Rp {{ number_format($tier['price'], 0, ',', '.') }}
                                                </div>
                                                <div class="text-[10px] text-gray-500 dark:text-gray-400 font-mono tabular-nums">
                                                    ≈ Rp {{ number_format($perMonth, 0, ',', '.') }} / bln
                                                </div>
                                            </div>
                                        @else
                                            <div class="mt-2.5">
                                                <span
                                                    class="text-base sm:text-lg font-bold tabular-nums text-[#FF9500]">Rp 0</span>
                                                <div
                                                    class="text-[10px] font-semibold text-[#FF9500] uppercase tracking-wide mt-0.5">
                                                    Promo Trial</div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Radio check indicator -->
                                    <div class="mt-3 flex items-center gap-2">
                                        <div class="w-4 h-4 rounded-full border border-gray-300 dark:border-gray-600 flex items-center justify-center shrink-0 transition"
                                            :class="selectedDurationDays === {{ $tier['days'] }} ?
                                                'border-[#007AFF] bg-[#007AFF] text-white' : ''">
                                            <div x-show="selectedDurationDays === {{ $tier['days'] }}"
                                                class="w-1.5 h-1.5 rounded-full bg-white" aria-hidden="true"></div>
                                        </div>
                                        <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium"
                                            :class="selectedDurationDays === {{ $tier['days'] }} ?
                                                'text-[#007AFF] dark:text-[#0A84FF] font-semibold' : ''">
                                            {{ $tier['days'] >= 360 ? 'Tahunan' : ($tier['days'] >= 90 ? 'Multi-Bulan' : 'Bulanan') }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if (count($durationTiers) > 1)
                            <template x-if="selectedDurationDays > 31">
                                <div
                                    class="flex items-start gap-3 p-3.5 sm:p-4 rounded-[14px] bg-blue-50/60 dark:bg-blue-900/20 border border-blue-200/50 dark:border-blue-800/40 text-xs text-blue-900 dark:text-blue-300">
                                    <i data-lucide="trending-down"
                                        class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"
                                        aria-hidden="true"></i>
                                    <div class="leading-relaxed text-gray-700 dark:text-gray-300">
                                        <strong class="text-black dark:text-white">Hemat lebih banyak</strong> dengan
                                        durasi yang lebih panjang. Bayar satu kali di awal, nikmati akses penuh tanpa perpanjangan otomatis. Data bisnis Anda tetap terjaga aman.
                                    </div>
                                </div>
                            </template>
                        @endif
                    </section>
                @endif

                <!-- STEP 2: Package Selection Cards -->
                @if ($packages->isNotEmpty())
                    <section aria-labelledby="package-selection-heading"
                        class="bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] p-5 sm:p-6 space-y-5">
                        <div
                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                            <div class="flex items-center gap-3">
                                @if ($type === 'subscription')
                                    <div
                                        class="w-8 h-8 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] flex items-center justify-center shrink-0">
                                        <span class="text-gray-600 dark:text-gray-400 font-bold text-sm">2</span>
                                    </div>
                                @endif
                                <div>
                                    <h3 id="package-selection-heading"
                                        class="text-sm sm:text-base font-bold text-black dark:text-white flex items-center gap-2">
                                        <i data-lucide="package" class="w-4 h-4 text-[#007AFF]"
                                            aria-hidden="true"></i>
                                        <span>{{ $type === 'subscription' ? 'Pilih Opsi Paket Patungan' : ($type === 'ai_token' ? 'Pilih Paket Top Up Token AI' : 'Pilih Paket Cloud Storage') }}</span>
                                    </h3>
                                    <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        {{ $type === 'subscription' ? 'Katalog resmi program gotong royong UMKM berlisensi penuh.' : 'Paket isi ulang kuota sumber daya bisnis.' }}
                                    </p>
                                </div>
                            </div>
                            <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 font-mono self-start sm:self-auto">
                                Patungan Terbuka
                            </span>
                        </div>

                        @if ($type === 'subscription' && count($durationTiers) > 0)
                            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <i data-lucide="filter" class="w-3.5 h-3.5 text-gray-400" aria-hidden="true"></i>
                                <span>Menampilkan paket durasi:</span>
                                <span class="font-mono font-bold text-[#007AFF]"
                                    x-text="durationTiers.find(t => t.days === selectedDurationDays)?.label ?? 'Semua'"></span>
                                <span class="text-gray-400">(<span
                                        x-text="filteredPackages.filter(p => p.price > 0).length"></span> tersedia)</span>
                            </div>
                        @endif

                        <!-- Package Radio Group -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5" role="radiogroup"
                            aria-label="Pilihan Paket">
                            @foreach ($packages as $package)
                                <div role="radio" tabindex="0"
                                    :aria-checked="packageId === '{{ $package->id }}' ? 'true' : 'false'"
                                    x-show="orderType !== 'subscription' || filteredPackages.some(p => p.id === '{{ $package->id }}')"
                                    @click="packageId = '{{ $package->id }}'; cycle = '{{ $package->duration_days >= 360 ? 'annual' : 'monthly' }}'"
                                    @keydown.space.prevent="packageId = '{{ $package->id }}'; cycle = '{{ $package->duration_days >= 360 ? 'annual' : 'monthly' }}'"
                                    @keydown.enter.prevent="packageId = '{{ $package->id }}'; cycle = '{{ $package->duration_days >= 360 ? 'annual' : 'monthly' }}'"
                                    :class="packageId === '{{ $package->id }}' ?
                                        'border-[#007AFF] bg-blue-50/40 dark:bg-blue-900/20 ring-1 ring-[#007AFF]' :
                                        'border-black/[0.06] dark:border-white/[0.08] bg-black/[0.01] dark:bg-white/[0.02] hover:border-black/[0.12] dark:hover:border-white/[0.14]'"
                                    class="cursor-pointer text-left rounded-[16px] border p-4 sm:p-5 transition-all relative overflow-hidden flex flex-col justify-between group active:scale-[0.98] focus-visible:ring-2 focus-visible:ring-[#007AFF] focus-visible:outline-none">

                                    <div>
                                        <div class="flex items-start justify-between gap-2">
                                            <span
                                                class="font-bold text-black dark:text-white text-sm sm:text-base group-hover:text-[#007AFF] transition-colors">{{ $package->name }}</span>
                                            <div class="w-5 h-5 rounded-full border border-gray-300 dark:border-gray-600 shrink-0 flex items-center justify-center transition"
                                                :class="packageId === '{{ $package->id }}' ?
                                                    'border-[#007AFF] bg-[#007AFF] text-white' : ''">
                                                <span x-show="packageId === '{{ $package->id }}'"
                                                    class="w-2 h-2 rounded-full bg-white" aria-hidden="true"></span>
                                            </div>
                                        </div>

                                        @if ($package->price <= 0)
                                            <div class="flex items-center gap-2 mt-3">
                                                <span
                                                    class="text-2xl font-bold tabular-nums text-[#34C759]">Rp 0</span>
                                                <span
                                                    class="px-2 py-0.5 rounded-[8px] text-[10px] font-bold uppercase bg-amber-50 dark:bg-amber-950/40 text-[#FF9500] border border-amber-200/80 dark:border-amber-800/80 font-mono">
                                                    Promo Trial
                                                </span>
                                            </div>
                                        @else
                                            <div
                                                class="text-xl sm:text-2xl font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF] mt-3">
                                                Rp {{ number_format($package->price, 0, ',', '.') }}
                                            </div>
                                        @endif

                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 leading-relaxed">
                                            {{ $package->description }}</p>
                                    </div>

                                    <div class="mt-4 pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                                        @if ($type === 'subscription')
                                            <span
                                                class="text-xs font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                                                <i data-lucide="clock" class="w-3.5 h-3.5 text-gray-400" aria-hidden="true"></i>
                                                <span>Durasi {{ $package->duration_days }} Hari @if ($package->price <= 0)
                                                        (Aktivasi Instan)
                                                    @endif
                                                </span>
                                            </span>
                                        @elseif($type === 'ai_token')
                                            <span
                                                class="text-xs font-semibold text-[#FF9500] flex items-center gap-1.5">
                                                <i data-lucide="bot" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                                <span>{{ number_format($package->token_quantity, 0, ',', '.') }} Token
                                                    AI</span>
                                            </span>
                                        @else
                                            <span
                                                class="text-xs font-semibold text-[#007AFF] flex items-center gap-1.5">
                                                <i data-lucide="hard-drive" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                                <span>{{ number_format(($package->storage_bytes ?? 0) / 1073741824, 2, ',', '.') }}
                                                    GB Permanen</span>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if ($type === 'subscription')
                            <div x-show="filteredPackages.filter(p => p.price > 0).length === 0" x-cloak
                                class="p-6 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] text-center space-y-2 text-xs text-gray-500 dark:text-gray-400">
                                <i data-lucide="package-x" class="w-8 h-8 text-gray-400 mx-auto" aria-hidden="true"></i>
                                <div class="font-semibold text-black dark:text-white">Belum ada paket untuk durasi ini</div>
                                <div>Silakan pilih durasi lain atau hubungi admin untuk informasi paket yang tersedia.</div>
                            </div>
                        @endif
                    </section>
                @endif

                <!-- STEP 3: Payment Method Selector (Contains exact text 'Pilih Metode Pembayaran') -->
                <section aria-labelledby="payment-channels-heading"
                    class="bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] p-5 sm:p-6 space-y-5">
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] flex items-center justify-center shrink-0">
                                <span
                                    class="text-gray-600 dark:text-gray-400 font-bold text-sm">{{ $type === 'subscription' ? '3' : '2' }}</span>
                            </div>
                            <div>
                                <h3 id="payment-channels-heading"
                                    class="text-sm sm:text-base font-bold text-black dark:text-white flex items-center gap-2">
                                    <i data-lucide="wallet" class="w-4 h-4 text-[#007AFF]"
                                        aria-hidden="true"></i>
                                    <span>Pilih Metode Pembayaran</span>
                                </h3>
                                <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 mt-0.5">Transfer via
                                    rekening bank nasional atau scan kode QRIS standar Bank Indonesia.</p>
                            </div>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 font-mono self-start sm:self-auto"
                            x-text="currentPrice <= 0 ? 'Bebas Biaya (Rp 0)' : 'Verifikasi Cepat 5–15 Menit'"></span>
                    </div>

                    <!-- Free Promo Notice -->
                    <div x-show="currentPrice <= 0" x-cloak
                        class="p-4 sm:p-5 rounded-[16px] bg-green-50/60 dark:bg-green-950/30 border border-green-200/60 dark:border-green-800/60 flex items-start gap-4">
                        <div class="w-10 h-10 rounded-[12px] bg-green-100 dark:bg-green-900/40 text-[#34C759] flex items-center justify-center shrink-0"
                            aria-hidden="true">
                            <i data-lucide="sparkles" class="w-5 h-5"></i>
                        </div>
                        <div class="space-y-1 text-xs">
                            <h4 class="font-bold text-black dark:text-white text-sm">Paket Bebas Biaya - Promo Trial
                                Aktif Otomatis</h4>
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                                Anda memilih paket promo khusus (Rp 0). Bisnis Anda <strong>tidak perlu melakukan transfer
                                    dana</strong> maupun mengunggah bukti bayar. Fitur Cooca UMKM akan langsung aktif
                                seketika setelah menekan tombol konfirmasi.
                            </p>
                        </div>
                    </div>

                    <!-- Payment Accounts Gateway List -->
                    <div x-show="currentPrice > 0" class="space-y-3" role="radiogroup" aria-label="Metode Pembayaran">
                        @forelse($paymentAccounts as $account)
                            <div role="radio" tabindex="0"
                                :aria-checked="paymentMethod === '{{ $account->bank_code }}' ? 'true' : 'false'"
                                @click="paymentMethod = '{{ $account->bank_code }}'"
                                @keydown.space.prevent="paymentMethod = '{{ $account->bank_code }}'"
                                @keydown.enter.prevent="paymentMethod = '{{ $account->bank_code }}'"
                                class="cursor-pointer rounded-[16px] p-4 sm:p-5 border transition-all flex items-center justify-between gap-4 active:scale-[0.98] focus-visible:ring-2 focus-visible:ring-[#007AFF] focus-visible:outline-none"
                                :class="paymentMethod === '{{ $account->bank_code }}' ?
                                    'bg-blue-50/40 dark:bg-blue-900/20 border-[#007AFF] ring-1 ring-[#007AFF]' :
                                    'bg-black/[0.01] dark:bg-white/[0.02] border-black/[0.06] dark:border-white/[0.08] hover:border-black/[0.12] dark:hover:border-white/[0.14]'">

                                <div class="flex items-center gap-4 min-w-0">
                                    <!-- Bank / Gateway Brand Icon -->
                                    <div
                                        class="w-12 h-12 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/[0.06] dark:border-white/[0.08] flex items-center justify-center shrink-0 shadow-xs">
                                        @if ($account->type === \App\Models\PaymentAccount::TYPE_QRIS)
                                            <i data-lucide="qr-code"
                                                class="w-6 h-6 text-[#34C759]"
                                                aria-hidden="true"></i>
                                        @elseif(str_contains(strtolower($account->bank_name), 'bca'))
                                            <span
                                                class="font-bold text-xs text-[#007AFF] font-mono tracking-tighter">BCA</span>
                                        @elseif(str_contains(strtolower($account->bank_name), 'mandiri'))
                                            <span
                                                class="font-bold text-xs text-[#FF9500] font-mono tracking-tighter">MANDIRI</span>
                                        @elseif(str_contains(strtolower($account->bank_name), 'bri'))
                                            <span
                                                class="font-bold text-xs text-[#007AFF] font-mono tracking-tighter">BRI</span>
                                        @elseif(str_contains(strtolower($account->bank_name), 'bni'))
                                            <span
                                                class="font-bold text-xs text-[#FF9500] font-mono tracking-tighter">BNI</span>
                                        @else
                                            <i data-lucide="{{ $account->icon ?: 'credit-card' }}"
                                                class="w-6 h-6 text-[#007AFF]"
                                                aria-hidden="true"></i>
                                        @endif
                                    </div>

                                    <div class="min-w-0">
                                        <div
                                            class="font-bold text-sm text-black dark:text-white flex items-center gap-2 flex-wrap">
                                            <span>{{ $account->bank_name }}</span>
                                            @if ($account->type === \App\Models\PaymentAccount::TYPE_QRIS)
                                                <span class="text-[11px] font-semibold text-[#34C759]">
                                                    (QRIS Standar BI)
                                                </span>
                                            @else
                                                <span
                                                    class="text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider font-mono">Transfer
                                                    Bank</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 font-mono truncate">
                                            {{ $account->account_number }} · a.n. <strong
                                                class="text-gray-800 dark:text-gray-200 font-semibold">{{ $account->account_name }}</strong>
                                        </div>
                                    </div>
                                </div>

                                <!-- Radio Check Indicator -->
                                <div class="w-5 h-5 rounded-full border border-gray-300 dark:border-gray-600 flex items-center justify-center shrink-0 transition"
                                    :class="paymentMethod === '{{ $account->bank_code }}' ?
                                        'border-[#007AFF] bg-[#007AFF] text-white' : ''">
                                    <div x-show="paymentMethod === '{{ $account->bank_code }}'"
                                        class="w-2 h-2 rounded-full bg-white" aria-hidden="true"></div>
                                </div>
                            </div>
                        @empty
                            <div
                                class="p-6 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] text-gray-500 dark:text-gray-400 text-xs text-center space-y-1">
                                <i data-lucide="alert-circle" class="w-6 h-6 text-gray-400 mx-auto mb-1"
                                    aria-hidden="true"></i>
                                <div class="font-bold text-black dark:text-white">Belum ada rekening pembayaran yang
                                    aktif.</div>
                                <div>Silakan hubungi administrator sistem untuk mengaktifkan saluran pembayaran.</div>
                            </div>
                        @endforelse
                    </div>
                </section>

            </div>

            <!-- Right Column: Order Summary & Guarantee (Sticky) -->
            <div class="space-y-6 lg:sticky lg:top-24">
                <div
                    class="bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] p-5 sm:p-6 space-y-5">
                    <div class="border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                        <span
                            class="text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 font-mono">Ringkasan
                            Pesanan</span>
                        <h4 class="text-base sm:text-lg font-bold text-black dark:text-white mt-1">Cooca UMKM -
                            Patungan</h4>
                        <p
                            class="text-xs text-[#007AFF] font-medium mt-0.5 flex items-center gap-1.5">
                            <i data-lucide="store" class="w-3.5 h-3.5" aria-hidden="true"></i>
                            <span class="truncate font-mono">Workspace: {{ $business->name }}</span>
                        </p>
                    </div>

                    <!-- Selected Duration Summary (subscription only) -->
                    @if ($type === 'subscription' && count($durationTiers) > 0)
                        <div
                            class="flex items-center justify-between text-xs border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] p-3 bg-black/[0.02] dark:bg-white/[0.03]">
                            <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                <i data-lucide="calendar-check"
                                    class="w-3.5 h-3.5 text-[#007AFF] shrink-0"
                                    aria-hidden="true"></i>
                                <span>Durasi dipilih</span>
                            </div>
                            <span class="font-bold text-[#007AFF] font-mono"
                                x-text="durationTiers.find(t => t.days === selectedDurationDays)?.label ?? '-'"></span>
                        </div>
                    @endif

                    <!-- Included Entitlements -->
                    <div class="space-y-2 text-xs text-gray-600 dark:text-gray-300">
                        <div class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-[#34C759] shrink-0"
                                aria-hidden="true"></i>
                            <span>Katalog Produk &amp; Resep Tanpa Batas</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-[#34C759] shrink-0"
                                aria-hidden="true"></i>
                            <span>Multi-Gudang &amp; Multi-Outlet Kasir POS</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-[#34C759] shrink-0"
                                aria-hidden="true"></i>
                            <span>Import &amp; Export Excel Multi-Sheet Lengkap</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-[#34C759] shrink-0"
                                aria-hidden="true"></i>
                            <span>Akses Asisten Pintar AI (Gemini Flash)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-[#34C759] shrink-0"
                                aria-hidden="true"></i>
                            <span>Komitmen <em>No Data Punishment</em> (Aman)</span>
                        </div>
                    </div>

                    <!-- Price Breakdown -->
                    <div class="border-t border-black/[0.06] dark:border-white/[0.08] pt-4 space-y-2 text-xs">
                        <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                            <span>{{ $type === 'subscription' ? 'Nominal Paket:' : 'Harga Top Up:' }}</span>
                            <span class="font-mono tabular-nums text-black dark:text-white font-bold">
                                <span
                                    x-text="currentPrice <= 0 ? 'Rp 0 (Gratis Promo)' : ('Rp ' + formatRupiah(currentPrice))"></span>
                            </span>
                        </div>
                        <div x-show="currentPrice > 0"
                            class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                            <span>Kode Verifikasi Unik:</span>
                            <span class="font-mono text-[#FF9500] text-[11px] font-semibold">3 Digit di invoice</span>
                        </div>
                        <div
                            class="border-t border-black/[0.06] dark:border-white/[0.08] pt-3 flex items-baseline justify-between">
                            <span class="font-bold text-black dark:text-white text-sm">Estimasi Total:</span>
                            <span class="font-bold text-xl sm:text-2xl text-[#007AFF] dark:text-[#0A84FF] font-mono tabular-nums"
                                x-text="currentPrice <= 0 ? 'Rp 0' : ('Rp ' + formatRupiah(currentPrice))"></span>
                        </div>
                    </div>

                    <!-- Submit Button with Double-Submit Prevention -->
                    @if (\App\Support\Context::hasPermission('billing.manage'))
                        <button type="submit" :disabled="isSubmitting"
                            class="w-full h-12 rounded-[14px] text-[15px] font-semibold shadow-sm transition-all flex items-center justify-center gap-2 group cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed active:scale-[0.98] focus-visible:ring-2 focus-visible:ring-[#007AFF] focus-visible:outline-none"
                            :class="currentPrice <= 0 ? 'bg-[#FF9500] hover:bg-[#FF9F0A] text-white' :
                                'bg-[#007AFF] hover:bg-[#0071E3] text-white'">
                            <span x-show="isSubmitting"
                                class="animate-spin w-4 h-4 border-2 border-current border-t-transparent rounded-full"
                                aria-hidden="true"></span>
                            <i data-lucide="sparkles" class="w-4 h-4" x-show="!isSubmitting && currentPrice <= 0"
                                aria-hidden="true"></i>
                            <span
                                x-text="isSubmitting ? 'Memproses Pesanan...' : (currentPrice <= 0 ? 'Aktifkan Promo Trial Sekarang' : 'Lanjutkan Pembayaran')"></span>
                            <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"
                                x-show="!isSubmitting && currentPrice > 0" aria-hidden="true"></i>
                        </button>

                        <p class="text-[11px] text-center text-gray-500 dark:text-gray-400 leading-relaxed"
                            x-text="currentPrice <= 0 ? 'Paket trial langsung aktif seketika tanpa perlu bayar maupun verifikasi manual.' : 'Setelah klik tombol di atas, Anda akan mendapatkan nomor rekening resmi dan formulir upload bukti transfer.'">
                        </p>
                    @else
                        <div
                            class="p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-[#FF9500] text-xs rounded-[12px] text-center font-medium">
                            Anda hanya memiliki hak akses melihat (read-only). Hubungi Owner untuk melakukan upgrade paket.
                        </div>
                    @endif

                    <!-- Trust Indicators -->
                    <div
                        class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-center gap-4 text-[10px] text-gray-500 dark:text-gray-400">
                        <span class="flex items-center gap-1">
                            <i data-lucide="lock" class="w-3.5 h-3.5 text-[#34C759]"
                                aria-hidden="true"></i>
                            <span>Aman Terenkripsi</span>
                        </span>
                        <span class="flex items-center gap-1">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#007AFF]"
                                aria-hidden="true"></i>
                            <span>Data Terlindungi</span>
                        </span>
                        <span class="flex items-center gap-1">
                            <i data-lucide="zap" class="w-3.5 h-3.5 text-[#FF9500]"
                                aria-hidden="true"></i>
                            <span>Aktivasi Cepat</span>
                        </span>
                    </div>
                </div>
            </div>

        </form>
    </div>
@endsection
