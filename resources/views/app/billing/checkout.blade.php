@extends('layouts.app', [
    'title' => __('billing.checkout_title') . ' - Cooca',
    'headerTitle' => __('billing.breadcrumb_checkout'),
    'headerSubtitle' => __('billing.checkout_subtitle'),
])

@section('content')
    @php
        $defaultSelectedCode = $paymentAccounts->first()?->bank_code ?? 'qris';

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
        selectedTier: '{{ $selectedTier ?? 'standard' }}',
        tierPlans: {
            standard: {
                key: 'standard',
                name: 'Standard Plan',
                label: 'Standard',
                badge: 'UMKM Pemula',
                badgeColor: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
                monthly: {{ (float) ($allTierPrices['standard']['monthly'] ?? 29000) }},
                annual: {{ (float) ($allTierPrices['standard']['annual'] ?? 290000) }},
                popular: false,
                desc: 'Cocok untuk 1 usaha rintisan dengan 100 produk dan kasir POS digital.',
                features: ['1 Bisnis Cooca', '100 Produk & 20 Formula Resep', '1.000 Transaksi Kasir/bln', '2 Lokasi (Toko & Gudang)', '3 Akun Staf / Karyawan', '5 Meja Kasir Dine-in', 'POS Kasir Digital & Offline Sync', 'Ekspor / Impor Excel', '50 Notifikasi WhatsApp/bln']
            },
            premium: {
                key: 'premium',
                name: 'Premium Plan',
                label: 'Premium',
                badge: 'Paling Populer',
                badgeColor: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800',
                monthly: {{ (float) ($allTierPrices['premium']['monthly'] ?? 89000) }},
                annual: {{ (float) ($allTierPrices['premium']['annual'] ?? 890000) }},
                popular: true,
                desc: 'Solusi lengkap multi-cabang, resep & kasir unlimited, KDS, dan otomasi stok.',
                features: ['3 Bisnis (Kelola 3 Brand)', 'Produk & Resep Unlimited', 'Transaksi Kasir Unlimited', '5 Cabang & Meja Unlimited', '10 Akun Staf / Karyawan', 'Kitchen Display System (KDS)', 'Transfer Antar Cabang & Multi-Price', 'Komisi Kasir & Kasbon', '200 Notifikasi WhatsApp/bln']
            },
            prestige: {
                key: 'prestige',
                name: 'Prestige Plan',
                label: 'Prestige',
                badge: 'Enterprise UMKM',
                badgeColor: 'bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-400 border-purple-200 dark:border-purple-800',
                monthly: {{ (float) ($allTierPrices['prestige']['monthly'] ?? 199000) }},
                annual: {{ (float) ($allTierPrices['prestige']['annual'] ?? 1990000) }},
                popular: false,
                desc: 'Kapasitas penuh enterprise tanpa batas, pajak PPh 21 TER, dan slip gaji WhatsApp.',
                features: ['Bisnis & Cabang Unlimited', 'Semua Fitur Tanpa Batas', 'Karyawan / Staf Unlimited', 'Modul Pajak PPh 21 TER (PP 58/2023)', 'Auto Slip Gaji via WhatsApp', 'Akuntansi Jurnal Otomatis Terpadu', '1.000 Notifikasi WhatsApp/bln', 'Prioritas Dukungan Teknis 24/7']
            }
        },
        selectedDurationDays: {{ $defaultDurationDays }},
        packageId: '{{ $defaultPackageId }}',
        paymentMethod: '{{ $defaultSelectedCode }}',
        monthlyPrice: {{ (int) $monthlyPrice }},
        annualPrice: {{ (int) $annualPrice }},
        topupPrice: {{ (int) $topupPrice }},
        isSubmitting: false,
        durationTiers: {{ \Illuminate\Support\Js::from($durationTiers) }},
        packages: {{ \Illuminate\Support\Js::from($packagesData) }},
        
        // Promo Voucher State
        promoCodeInput: '{{ old('promo_code', request('promo', '')) }}',
        appliedPromo: null,
        promoLoading: false,
        promoError: '',
        promoSuccessMessage: '',

        init() {
            if (this.promoCodeInput.trim()) {
                this.validateAndApplyPromo();
            }
        },

        async validateAndApplyPromo() {
            const code = this.promoCodeInput.trim().toUpperCase();
            if (!code) {
                this.promoError = 'Silakan masukkan kode promo / voucher.';
                return;
            }
            this.promoLoading = true;
            this.promoError = '';
            this.promoSuccessMessage = '';
            try {
                const res = await fetch('{{ route('billing.promo.validate') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        code: code,
                        tier: this.selectedTier,
                        cycle: this.cycle,
                        order_type: this.orderType,
                        package_id: this.packageId
                    })
                });
                const data = await res.json();
                if (res.ok && data.valid) {
                    this.appliedPromo = data;
                    this.promoCodeInput = data.promo.code;
                    this.promoSuccessMessage = data.message;
                } else {
                    this.appliedPromo = null;
                    this.promoError = data.message || 'Kode promo tidak dapat digunakan.';
                }
            } catch (err) {
                this.appliedPromo = null;
                this.promoError = 'Gagal memeriksa promo. Periksa koneksi Anda.';
            } finally {
                this.promoLoading = false;
            }
        },

        removePromo() {
            this.appliedPromo = null;
            this.promoCodeInput = '';
            this.promoError = '';
            this.promoSuccessMessage = '';
        },

        onPlanChanged() {
            if (this.appliedPromo) {
                this.validateAndApplyPromo();
            }
        },
    
        get filteredPackages() {
            if (this.orderType !== 'subscription') return this.packages;
            return this.packages.filter(p => p.duration_days === this.selectedDurationDays || p.price <= 0);
        },
    
        get currentPrice() {
            const selectedPkg = this.packageId ? this.packages.find(item => item.id === this.packageId) : null;
            if (selectedPkg && Number(selectedPkg.price) <= 0) {
                return 0;
            }
            if (this.orderType === 'subscription') {
                if (selectedPkg && !['standard', 'premium', 'prestige'].includes(this.selectedTier)) {
                    return Number(selectedPkg.price);
                }
                const plan = this.tierPlans[this.selectedTier] || this.tierPlans.standard;
                return this.cycle === 'annual' ? plan.annual : plan.monthly;
            }
            if (selectedPkg) return Number(selectedPkg.price);
            return this.topupPrice;
        },

        get discountAmount() {
            if (!this.appliedPromo) return 0;
            const base = this.currentPrice;
            if (this.appliedPromo.promo.discount_type === 'percentage') {
                const percent = Number(this.appliedPromo.promo.discount_value) || 0;
                let disc = (base * percent) / 100;
                if (this.appliedPromo.promo.max_discount_amount) {
                    disc = Math.min(disc, Number(this.appliedPromo.promo.max_discount_amount));
                }
                return Math.round(disc);
            }
            return Math.min(base, Number(this.appliedPromo.promo.discount_value) || 0);
        },

        get totalPayable() {
            return Math.max(0, this.currentPrice - this.discountAmount);
        },
    
        selectDuration(days) {
            this.selectedDurationDays = days;
            this.cycle = days >= 360 ? 'annual' : 'monthly';
            const match = this.packages.find(p => p.duration_days === days);
            if (match) this.packageId = match.id;
            this.onPlanChanged();
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

        <!-- Standard 3-Row Module Header Bento Apple HIG -->
        <x-module-header
            :title="__('billing.checkout_title')"
            :subtitle="__('billing.checkout_subtitle')"
            :breadcrumbs="[
                ['label' => __('billing.breadcrumb_billing'), 'route' => 'billing.limits'],
                ['label' => __('billing.breadcrumb_checkout')],
            ]"
            :badge="$business->name"
        >
            <x-slot:actions>
                <a href="{{ route('billing.limits') }}"
                    class="h-10 px-4 rounded-[12px] text-xs font-semibold text-slate-700 dark:text-slate-300 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] active:scale-[0.98] transition flex items-center gap-2">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>{{ __('billing.back') }}</span>
                </a>
                <a href="{{ route('billing.history') }}"
                    class="h-10 px-4 rounded-[12px] text-xs font-semibold text-slate-700 dark:text-slate-300 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] active:scale-[0.98] transition flex items-center gap-2">
                    <i data-lucide="receipt" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>{{ __('billing.action_history') }}</span>
                </a>
            </x-slot:actions>
        </x-module-header>

        <!-- Submodule Navigation Tabs -->
        <x-module-tabs module="billing" class="mt-2 mb-2" />

        <!-- Mobile Sticky Price Bar -->
        <div
            class="lg:hidden rounded-[16px] p-4 border border-black/[0.06] dark:border-white/[0.08] bg-white/90 dark:bg-[#1C1C1E]/90 flex items-center justify-between shadow-[0_2px_8px_rgba(0,0,0,0.04)] backdrop-blur-md">
            <div>
                <span
                    class="text-[10px] uppercase font-semibold text-gray-500 dark:text-gray-400 block tracking-wider font-mono">{{ __('billing.estimated_bill') }}</span>
                <span class="text-lg font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF]"
                    x-text="'Rp ' + formatRupiah(currentPrice)"></span>
            </div>
            <div class="text-right">
                <span class="text-[10px] font-medium text-gray-500 dark:text-gray-400 block">{{ __('billing.active_workspace') }}</span>
                <span
                    class="text-xs font-semibold text-black dark:text-white truncate max-w-[140px] block font-mono">{{ $business->name }}</span>
            </div>
        </div>

        <!-- Main Checkout Form -->
        <form method="POST" action="{{ route('billing.order.store') }}" @submit="isSubmitting = true"
            class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 items-start">
            @csrf
            <input type="hidden" name="cycle" :value="cycle">
            <input type="hidden" name="tier" :value="selectedTier">
            <input type="hidden" name="order_type" value="{{ $type }}">
            <input type="hidden" name="package_id" :value="packageId">
            <input type="hidden" name="payment_method" :value="paymentMethod">
            <input type="hidden" name="promo_code" :value="appliedPromo ? appliedPromo.promo.code : ''">

            <!-- Left Column: Step Cards (2 cols) -->
            <div class="lg:col-span-2 space-y-6">

                <!-- STEP 1: Subscription Tier Selector (Subscription type) -->
                @if ($type === 'subscription')
                    <section aria-labelledby="tier-selection-heading"
                        class="bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] p-5 sm:p-6 space-y-6">
                        <div
                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-[10px] bg-blue-50 dark:bg-blue-900/30 border border-blue-200/60 dark:border-blue-800/60 flex items-center justify-center shrink-0">
                                    <span class="text-[#007AFF] font-bold text-sm">1</span>
                                </div>
                                <div>
                                    <h3 id="tier-selection-heading"
                                        class="text-sm sm:text-base font-bold text-black dark:text-white flex items-center gap-2">
                                        <i data-lucide="layers" class="w-4 h-4 text-[#007AFF]" aria-hidden="true"></i>
                                        <span>{{ __('billing.tier_selection_heading') }}</span>
                                    </h3>
                                    <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ __('billing.choose_plan_subtitle') }}</p>
                                </div>
                            </div>

                            <!-- Segmented Cycle Toggle (Monthly vs Annual) -->
                            <div
                                class="inline-flex p-1 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] self-start sm:self-auto">
                                <button type="button" @click="cycle = 'monthly'; onPlanChanged();"
                                    :class="cycle === 'monthly' ?
                                        'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs' :
                                        'text-gray-500 hover:text-black dark:hover:text-white'"
                                    class="px-3 py-1.5 rounded-[9px] text-xs font-semibold transition-all">
                                    {{ __('billing.monthly') }}
                                </button>
                                <button type="button" @click="cycle = 'annual'; onPlanChanged();"
                                    :class="cycle === 'annual' ?
                                        'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs' :
                                        'text-gray-500 hover:text-black dark:hover:text-white'"
                                    class="px-3 py-1.5 rounded-[9px] text-xs font-semibold transition-all flex items-center gap-1.5">
                                    <span>{{ __('billing.annual') }}</span>
                                    <span
                                        class="px-1.5 py-0.5 text-[9px] font-bold rounded-full bg-[#34C759] text-white">{{ __('billing.annual_badge') }}</span>
                                </button>
                            </div>
                        </div>

                        @if ($packages->where('price', '<=', 0)->isNotEmpty())
                            <!-- Promo / Trial Packages Banner -->
                            <div class="space-y-3">
                                <div class="text-xs font-bold text-[#FF9500] uppercase tracking-wider font-mono flex items-center gap-1.5">
                                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                                    <span>{{ __('billing.free_promo_offer_title') }}</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    @foreach ($packages->where('price', '<=', 0) as $promoPkg)
                                        <div role="radio" tabindex="0"
                                            :aria-checked="packageId === '{{ $promoPkg->id }}' ? 'true' : 'false'"
                                            @click="packageId = '{{ $promoPkg->id }}'; selectedTier = 'promo'; onPlanChanged();"
                                            @keydown.space.prevent="packageId = '{{ $promoPkg->id }}'; selectedTier = 'promo'; onPlanChanged();"
                                            @keydown.enter.prevent="packageId = '{{ $promoPkg->id }}'; selectedTier = 'promo'; onPlanChanged();"
                                            :class="packageId === '{{ $promoPkg->id }}' ?
                                                'border-[#34C759] bg-green-50/40 dark:bg-green-950/30 ring-2 ring-[#34C759]' :
                                                'border-black/[0.06] dark:border-white/[0.08] bg-black/[0.01] dark:bg-white/[0.02] hover:border-black/[0.14]'"
                                            class="cursor-pointer rounded-[18px] border p-4 sm:p-5 transition-all relative flex flex-col justify-between group active:scale-[0.98] focus-visible:outline-none">
                                            <div class="space-y-2.5">
                                                <div class="flex items-center justify-between">
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono tracking-wider border uppercase bg-green-100 text-green-800 dark:bg-green-900/60 dark:text-green-300 border-green-300 dark:border-green-700">
                                                        {{ __('billing.free_promo_badge') }}
                                                    </span>
                                                    <div class="w-5 h-5 rounded-full border border-gray-300 dark:border-gray-600 flex items-center justify-center shrink-0 transition"
                                                        :class="packageId === '{{ $promoPkg->id }}' ? 'border-[#34C759] bg-[#34C759] text-white' : ''">
                                                        <div x-show="packageId === '{{ $promoPkg->id }}'" class="w-2 h-2 rounded-full bg-white"></div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h4 class="text-base font-bold text-black dark:text-white">{{ $promoPkg->name }}</h4>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $promoPkg->description ?: 'Akses seluruh fitur Pro tanpa biaya.' }}</p>
                                                </div>
                                                <div class="pt-2 border-t border-black/[0.06] dark:border-white/[0.08] flex items-baseline justify-between">
                                                    <div class="text-lg font-bold font-mono tabular-nums text-[#34C759]">{{ __('billing.free_rp_zero') }}</div>
                                                    <span class="text-[11px] font-mono text-gray-500 dark:text-gray-400">{{ __('billing.active_duration_days', ['days' => $promoPkg->duration_days]) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- 3-Tier Bento Selection Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4" role="radiogroup"
                            aria-label="Pilih Paket Langganan">
                            <!-- Standard Tier -->
                            <div role="radio" tabindex="0"
                                :aria-checked="selectedTier === 'standard' ? 'true' : 'false'"
                                @click="selectedTier = 'standard'; onPlanChanged();" @keydown.space.prevent="selectedTier = 'standard'; onPlanChanged();"
                                @keydown.enter.prevent="selectedTier = 'standard'; onPlanChanged();"
                                :class="selectedTier === 'standard' ?
                                    'border-[#007AFF] bg-blue-50/30 dark:bg-blue-900/20 ring-2 ring-[#007AFF]' :
                                    'border-black/[0.06] dark:border-white/[0.08] bg-black/[0.01] dark:bg-white/[0.02] hover:border-black/[0.14] dark:hover:border-white/[0.16]'"
                                class="cursor-pointer rounded-[18px] border p-4 sm:p-5 transition-all relative flex flex-col justify-between group active:scale-[0.98] focus-visible:outline-none">
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono tracking-wider border uppercase bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800">
                                            UMKM Pemula
                                        </span>
                                        <div class="w-5 h-5 rounded-full border border-gray-300 dark:border-gray-600 flex items-center justify-center shrink-0 transition"
                                            :class="selectedTier === 'standard' ? 'border-[#007AFF] bg-[#007AFF] text-white' :
                                                ''">
                                            <div x-show="selectedTier === 'standard'" class="w-2 h-2 rounded-full bg-white">
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="text-base font-bold text-black dark:text-white">Standard Plan</h4>
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">Untuk 1
                                            usaha rintisan dengan kasir POS digital.</p>
                                    </div>
                                    <div class="pt-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                                        <div class="text-xl font-bold font-mono tabular-nums text-black dark:text-white"
                                            x-text="'Rp ' + formatRupiah(cycle === 'annual' ? tierPlans.standard.annual : tierPlans.standard.monthly)"></div>
                                        <div class="text-[10px] text-gray-500 dark:text-gray-400 font-mono mt-0.5"
                                            x-text="cycle === 'annual' ? 'per tahun (≈ Rp ' + formatRupiah(Math.round(tierPlans.standard.annual / 12)) + '/bln)' : 'per bulan (tagihan fleksibel)'">
                                        </div>
                                    </div>
                                    <ul class="space-y-1.5 pt-2 text-[11px] text-gray-600 dark:text-gray-300">
                                        <li class="flex items-center gap-1.5"><i data-lucide="check"
                                                class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i><span>1 Bisnis
                                                Cooca</span></li>
                                        <li class="flex items-center gap-1.5"><i data-lucide="check"
                                                class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i><span>100 Produk &amp; 20
                                                Formula Resep</span></li>
                                        <li class="flex items-center gap-1.5"><i data-lucide="check"
                                                class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i><span>1.000 Transaksi
                                                Kasir/bln</span></li>
                                        <li class="flex items-center gap-1.5"><i data-lucide="check"
                                                class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i><span>2 Lokasi
                                                (Toko &amp; Gudang)</span></li>
                                        <li class="flex items-center gap-1.5"><i data-lucide="check"
                                                class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i><span>3 Akun Staf /
                                                Karyawan</span></li>
                                        <li class="flex items-center gap-1.5"><i data-lucide="check"
                                                class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i><span>5 Meja Kasir
                                                Dine-in</span></li>
                                        <li class="flex items-center gap-1.5"><i data-lucide="check"
                                                class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i><span>POS Kasir &amp;
                                                Offline Sync</span></li>
                                        <li class="flex items-center gap-1.5"><i data-lucide="check"
                                                class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i><span>Ekspor / Impor
                                                Excel</span></li>
                                        <li class="flex items-center gap-1.5"><i data-lucide="check"
                                                class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i><span>50 Notifikasi WA /
                                                bln</span></li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Premium Tier (Most Popular) -->
                            <div role="radio" tabindex="0"
                                :aria-checked="selectedTier === 'premium' ? 'true' : 'false'"
                                @click="selectedTier = 'premium'; onPlanChanged();" @keydown.space.prevent="selectedTier = 'premium'; onPlanChanged();"
                                @keydown.enter.prevent="selectedTier = 'premium'; onPlanChanged();"
                                :class="selectedTier === 'premium' ?
                                    'border-[#007AFF] bg-blue-50/30 dark:bg-blue-900/20 ring-2 ring-[#007AFF]' :
                                    'border-black/[0.06] dark:border-white/[0.08] bg-black/[0.01] dark:bg-white/[0.02] hover:border-black/[0.14] dark:hover:border-white/[0.16]'"
                                class="cursor-pointer rounded-[18px] border p-4 sm:p-5 transition-all relative flex flex-col justify-between group active:scale-[0.98] focus-visible:outline-none">
                                <div
                                    class="absolute -top-3 left-1/2 -translate-x-1/2 px-2.5 py-0.5 bg-[#007AFF] text-white text-[9px] font-bold uppercase tracking-wider rounded-full shadow-xs">
                                    Paling Populer
                                </div>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between mt-1">
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono tracking-wider border uppercase bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800">
                                            Scale-Up UMKM
                                        </span>
                                        <div class="w-5 h-5 rounded-full border border-gray-300 dark:border-gray-600 flex items-center justify-center shrink-0 transition"
                                            :class="selectedTier === 'premium' ? 'border-[#007AFF] bg-[#007AFF] text-white' :
                                                ''">
                                            <div x-show="selectedTier === 'premium'"
                                                class="w-2 h-2 rounded-full bg-white"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="text-base font-bold text-black dark:text-white">Premium Plan</h4>
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                                            Multi-cabang, KDS dapur, transfer stok, komisi kasir.</p>
                                    </div>
                                    <div class="pt-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                                        <div class="text-xl font-bold font-mono tabular-nums text-[#007AFF] dark:text-[#0A84FF]"
                                            x-text="'Rp ' + formatRupiah(cycle === 'annual' ? tierPlans.premium.annual : tierPlans.premium.monthly)"></div>
                                        <div class="text-[10px] text-gray-500 dark:text-gray-400 font-mono mt-0.5"
                                            x-text="cycle === 'annual' ? 'per tahun (≈ Rp ' + formatRupiah(Math.round(tierPlans.premium.annual / 12)) + '/bln)' : 'per bulan (tagihan fleksibel)'">
                                        </div>
                                    </div>
                                    <ul class="space-y-1.5 pt-2 text-[11px] text-gray-600 dark:text-gray-300">
                                        <li class="flex items-center gap-1.5"><i data-lucide="check"
                                                class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i><span><strong>3
                                                     Bisnis</strong> (Kelola 3 Brand)</span></li>
                                        <li class="flex items-center gap-1.5"><i data-lucide="check"
                                                class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i><span>Produk &amp; Resep
                                                <strong>Unlimited</strong></span></li>
                                        <li class="flex items-center gap-1.5"><i data-lucide="check"
                                                class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i><span>Transaksi Kasir
                                                <strong>Unlimited</strong></span></li>
                                        <li class="flex items-center gap-1.5"><i data-lucide="check"
                                                class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i><span>5 Cabang &amp; Meja
                                                Unlimited</span></li>
                                        <li class="flex items-center gap-1.5"><i data-lucide="check"
                                                class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i><span>10 Akun Staf /
                                                Karyawan</span></li>
                                        <li class="flex items-center gap-1.5"><i data-lucide="check"
                                                class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i><span>Kitchen Display System
                                                (KDS)</span></li>
                                        <li class="flex items-center gap-1.5"><i data-lucide="check"
                                                class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i><span>Transfer Antar Gudang
                                                &amp; Multi-Price</span></li>
                                        <li class="flex items-center gap-1.5"><i data-lucide="check"
                                                class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i><span>Komisi Kasir &amp;
                                                Kasbon</span></li>
                                        <li class="flex items-center gap-1.5"><i data-lucide="check"
                                                class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i><span>200 Notifikasi WA /
                                                bln</span></li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Prestige Tier (Enterprise) -->
                            <div role="radio" tabindex="0"
                                :aria-checked="selectedTier === 'prestige' ? 'true' : 'false'"
                                @click="selectedTier = 'prestige'; onPlanChanged();" @keydown.space.prevent="selectedTier = 'prestige'; onPlanChanged();"
                                @keydown.enter.prevent="selectedTier = 'prestige'; onPlanChanged();"
                                :class="selectedTier === 'prestige' ?
                                    'border-[#007AFF] bg-blue-50/30 dark:bg-blue-900/20 ring-2 ring-[#007AFF]' :
                                    'border-black/[0.06] dark:border-white/[0.08] bg-black/[0.01] dark:bg-white/[0.02] hover:border-black/[0.14] dark:hover:border-white/[0.16]'"
                                class="cursor-pointer rounded-[18px] border p-4 sm:p-5 transition-all relative flex flex-col justify-between group active:scale-[0.98] focus-visible:outline-none">
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono tracking-wider border uppercase bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-400 border-purple-200 dark:border-purple-800">
                                            Enterprise UMKM
                                        </span>
                                        <div class="w-5 h-5 rounded-full border border-gray-300 dark:border-gray-600 flex items-center justify-center shrink-0 transition"
                                            :class="selectedTier === 'prestige' ? 'border-[#007AFF] bg-[#007AFF] text-white' :
                                                ''">
                                            <div x-show="selectedTier === 'prestige'"
                                                class="w-2 h-2 rounded-full bg-white"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="text-base font-bold text-black dark:text-white">Prestige Plan</h4>
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                                            Kapasitas unlimited penuh, pajak PPh 21 TER, &amp; slip WA.</p>
                                    </div>
                                    <div class="pt-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                                        <div class="text-xl font-bold font-mono tabular-nums text-purple-600 dark:text-purple-400"
                                            x-text="'Rp ' + formatRupiah(cycle === 'annual' ? tierPlans.prestige.annual : tierPlans.prestige.monthly)"></div>
                                        <div class="text-[10px] text-gray-500 dark:text-gray-400 font-mono mt-0.5"
                                            x-text="cycle === 'annual' ? 'per tahun (≈ Rp ' + formatRupiah(Math.round(tierPlans.prestige.annual / 12)) + '/bln)' : 'per bulan (tagihan fleksibel)'">
                                        </div>
                                    </div>
                                    <ul class="space-y-1.5 pt-2 text-[11px] text-gray-600 dark:text-gray-300">
                                        <li class="flex items-center gap-1.5"><i data-lucide="check"
                                                class="w-3.5 h-3.5 text-purple-600 shrink-0"></i><span><strong>Bisnis &amp;
                                                     Cabang Unlimited</strong></span></li>
                                        <li class="flex items-center gap-1.5"><i data-lucide="check"
                                                class="w-3.5 h-3.5 text-purple-600 shrink-0"></i><span>Karyawan / Staf
                                                <strong>Unlimited</strong></span></li>
                                        <li class="flex items-center gap-1.5"><i data-lucide="check"
                                                class="w-3.5 h-3.5 text-purple-600 shrink-0"></i><span>Pajak PPh 21 TER (PP
                                                58/2023)</span></li>
                                        <li class="flex items-center gap-1.5"><i data-lucide="check"
                                                class="w-3.5 h-3.5 text-purple-600 shrink-0"></i><span>Auto Kirim Slip Gaji
                                                WhatsApp</span></li>
                                        <li class="flex items-center gap-1.5"><i data-lucide="check"
                                                class="w-3.5 h-3.5 text-purple-600 shrink-0"></i><span>Akuntansi Jurnal
                                                Otomatis</span></li>
                                        <li class="flex items-center gap-1.5"><i data-lucide="check"
                                                class="w-3.5 h-3.5 text-purple-600 shrink-0"></i><span>1.000 Notifikasi
                                                WhatsApp/bln</span></li>
                                        <li class="flex items-center gap-1.5"><i data-lucide="check"
                                                class="w-3.5 h-3.5 text-purple-600 shrink-0"></i><span>Prioritas Dukungan
                                                Teknis 24/7</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Info Banner -->
                        <div
                            class="flex items-start gap-3 p-3.5 sm:p-4 rounded-[14px] bg-blue-50/60 dark:bg-blue-900/20 border border-blue-200/50 dark:border-blue-800/40 text-xs text-blue-900 dark:text-blue-300">
                            <i data-lucide="shield-check" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"
                                aria-hidden="true"></i>
                            <div class="leading-relaxed text-gray-700 dark:text-gray-300">
                                <strong class="text-black dark:text-white">Jaminan Keamanan Cooca:</strong> Pembayaran
                                diproses langsung oleh TriPay Payment Gateway resmi terlisensi BI. Jika masa aktif berakhir,
                                akun memasuki masa tenggang (Grace Period 3 hari) di mana kasir POS tetap aktif, dan data
                                bisnis Anda tidak pernah dihapus (<em>No Data Punishment</em>).
                            </div>
                        </div>
                    </section>
                @else
                    <!-- Topup Storage Package Selector -->
                    @if ($packages->isNotEmpty())
                        <section aria-labelledby="package-selection-heading"
                            class="bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] p-5 sm:p-6 space-y-5">
                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-[10px] bg-blue-50 dark:bg-blue-900/30 border border-blue-200/60 dark:border-blue-800/60 flex items-center justify-center shrink-0">
                                        <span class="text-[#007AFF] font-bold text-sm">1</span>
                                    </div>
                                    <div>
                                        <h3 id="package-selection-heading"
                                            class="text-sm sm:text-base font-bold text-black dark:text-white flex items-center gap-2">
                                            <i data-lucide="package" class="w-4 h-4 text-[#007AFF]"
                                                aria-hidden="true"></i>
                                            <span>Pilih Paket Cloud Storage</span>
                                        </h3>
                                        <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            Paket isi ulang kuota penyimpanan cloud bisnis Cooca.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5" role="radiogroup"
                                aria-label="Pilihan Paket">
                                @foreach ($packages as $package)
                                    <div role="radio" tabindex="0"
                                        :aria-checked="packageId === '{{ $package->id }}' ? 'true' : 'false'"
                                        @click="packageId = '{{ $package->id }}'"
                                        @keydown.space.prevent="packageId = '{{ $package->id }}'"
                                        @keydown.enter.prevent="packageId = '{{ $package->id }}'"
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
                                            <div
                                                class="text-xl sm:text-2xl font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF] mt-3">
                                                Rp {{ number_format($package->price, 0, ',', '.') }}
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 leading-relaxed">
                                                {{ $package->description }}</p>
                                        </div>
                                        <div class="mt-4 pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                                            <span
                                                class="text-xs font-semibold text-[#007AFF] flex items-center gap-1.5">
                                                <i data-lucide="hard-drive" class="w-3.5 h-3.5"
                                                    aria-hidden="true"></i>
                                                <span>{{ number_format(($package->storage_bytes ?? 0) / 1073741824, 2, ',', '.') }}
                                                    GB Permanen</span>
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                @endif

                <!-- STEP 2: Payment Method Selector (TriPay Gateway) -->
                <section aria-labelledby="payment-channels-heading"
                    class="bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] p-5 sm:p-6 space-y-5">
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] flex items-center justify-center shrink-0">
                                <span class="text-gray-600 dark:text-gray-400 font-bold text-sm">2</span>
                            </div>
                            <div>
                                <h3 id="payment-channels-heading"
                                    class="text-sm sm:text-base font-bold text-black dark:text-white flex items-center gap-2">
                                    <i data-lucide="wallet" class="w-4 h-4 text-[#007AFF]" aria-hidden="true"></i>
                                    <span>{{ __('billing.step_choose_payment') }}</span>
                                </h3>
                                <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ __('billing.step_choose_payment_subtitle') }}
                                </p>
                            </div>
                        </div>
                        <span
                            class="text-xs text-[#34C759] font-mono font-semibold self-start sm:self-auto flex items-center gap-1">
                            <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                            <span>{{ __('billing.instant_verification_247') }}</span>
                        </span>
                    </div>

                    <!-- Free Promo Notice -->
                    <div x-show="currentPrice <= 0" x-cloak
                        class="p-4 sm:p-5 rounded-[16px] bg-green-50/60 dark:bg-green-950/30 border border-green-200/60 dark:border-green-800/60 flex items-start gap-4">
                        <div class="w-10 h-10 rounded-[12px] bg-green-100 dark:bg-green-900/40 text-[#34C759] flex items-center justify-center shrink-0"
                            aria-hidden="true">
                            <i data-lucide="sparkles" class="w-5 h-5"></i>
                        </div>
                        <div class="space-y-1 text-xs">
                            <h4 class="font-bold text-black dark:text-white text-sm">{{ __('billing.free_promo_banner_title') }}</h4>
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                                {{ __('billing.free_promo_banner_desc') }}
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
                                        @if ($account->type === \App\Models\PaymentAccount::TYPE_QRIS || $account->bank_code === 'qris')
                                            <i data-lucide="qr-code" class="w-6 h-6 text-[#34C759]"
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
                                        @elseif(str_contains(strtolower($account->bank_name), 'permata'))
                                            <span
                                                class="font-bold text-xs text-emerald-600 font-mono tracking-tighter">PERMATA</span>
                                        @elseif(str_contains(strtolower($account->bank_name), 'cimb'))
                                            <span
                                                class="font-bold text-xs text-red-600 font-mono tracking-tighter">CIMB</span>
                                        @else
                                            <i data-lucide="{{ $account->icon ?: 'credit-card' }}"
                                                class="w-6 h-6 text-[#007AFF]" aria-hidden="true"></i>
                                        @endif
                                    </div>

                                    <div class="min-w-0">
                                        <div
                                            class="font-bold text-sm text-black dark:text-white flex items-center gap-2 flex-wrap">
                                            <span>{{ $account->bank_name }}</span>
                                            @if ($account->type === \App\Models\PaymentAccount::TYPE_QRIS || $account->bank_code === 'qris')
                                                <span class="text-[11px] font-semibold text-[#34C759]">
                                                    Standar Bank Indonesia
                                                </span>
                                            @else
                                                <span
                                                    class="text-[10px] font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wider font-mono">
                                                    {{ strtoupper($account->bank_code) }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 font-mono truncate">
                                            <span>{{ __('billing.official_tripay_gateway') }}</span> · a.n. <strong
                                                class="text-gray-800 dark:text-gray-200 font-semibold">{{ $account->account_name ?: 'Cooca ID' }}</strong>
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
                                <div class="font-bold text-black dark:text-white">{{ __('billing.channel_initializing') }}
                                </div>
                                <div>{{ __('billing.channel_initializing_sub') }}</div>
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
                            class="text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 font-mono">{{ __('billing.order_summary') }}</span>
                        <h4 class="text-base sm:text-lg font-bold text-black dark:text-white mt-1"
                            x-text="orderType === 'subscription' ? (tierPlans[selectedTier]?.name || 'Cooca Subscription') : 'Top Up Kuota Cooca'">
                        </h4>
                        <p class="text-xs text-[#007AFF] font-medium mt-0.5 flex items-center gap-1.5">
                            <i data-lucide="store" class="w-3.5 h-3.5" aria-hidden="true"></i>
                            <span class="truncate font-mono">{{ __('billing.active_workspace') }}: {{ $business->name }}</span>
                        </p>
                    </div>

                    <!-- Selected Duration Summary (subscription only) -->
                    <template x-if="orderType === 'subscription'">
                        <div
                            class="flex items-center justify-between text-xs border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] p-3 bg-black/[0.02] dark:bg-white/[0.03]">
                            <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                <i data-lucide="calendar-check" class="w-3.5 h-3.5 text-[#007AFF] shrink-0"
                                    aria-hidden="true"></i>
                                <span>{{ __('billing.payment_cycle') }}</span>
                            </div>
                            <span class="font-bold text-[#007AFF] font-mono"
                                x-text="cycle === 'annual' ? '{{ __('billing.annual') }} ({{ __('billing.annual_badge') }})' : '{{ __('billing.monthly') }}'"></span>
                        </div>
                    </template>

                    <!-- Included Entitlements (Dynamic from Selected Tier) -->
                    <div class="space-y-2 text-xs text-gray-600 dark:text-gray-300">
                        <template x-if="orderType === 'subscription'">
                            <div class="space-y-2">
                                <template x-for="(feat, idx) in (tierPlans[selectedTier]?.features || [])"
                                    :key="idx">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="check" class="w-4 h-4 text-[#34C759] shrink-0"
                                            aria-hidden="true"></i>
                                        <span x-text="feat"></span>
                                    </div>
                                </template>
                                <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                                    <i data-lucide="globe" class="w-4 h-4 text-[#007AFF] shrink-0"
                                        aria-hidden="true"></i>
                                    <span>Storefront: cooca.id/{{ $business->slug }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                                    <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759] shrink-0"
                                        aria-hidden="true"></i>
                                    <span>{{ __('billing.guarantee_no_data_punishment') }}</span>
                                </div>
                            </div>
                        </template>
                        <template x-if="orderType !== 'subscription'">
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="check" class="w-4 h-4 text-[#34C759] shrink-0"
                                        aria-hidden="true"></i>
                                    <span>{{ __('billing.auto_quota_addition') }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i data-lucide="check" class="w-4 h-4 text-[#34C759] shrink-0"
                                        aria-hidden="true"></i>
                                    <span>{{ __('billing.permanent_quota_duration') }}</span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Promo / Voucher Module Bento Apple HIG -->
                    <div class="border-t border-black/[0.06] dark:border-white/[0.08] pt-4 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <label class="text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60 font-mono flex items-center gap-1.5">
                                <i data-lucide="ticket-percent" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Kupon Promo / Diskon</span>
                            </label>
                            <template x-if="appliedPromo">
                                <button type="button" @click="removePromo()" class="text-[11px] font-semibold text-red-500 hover:underline">
                                    Hapus Kupon
                                </button>
                            </template>
                        </div>

                        <!-- Active Promo Banner -->
                        <template x-if="appliedPromo">
                            <div class="p-3 rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/25 flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-[8px] bg-[#34C759] text-white flex items-center justify-center shrink-0">
                                        <i data-lucide="check" class="w-4 h-4" stroke-width="2.5"></i>
                                    </div>
                                    <div>
                                        <div class="text-[12.5px] font-bold font-mono text-black dark:text-white" x-text="appliedPromo.promo.code"></div>
                                        <div class="text-[11px] text-[#34C759] dark:text-[#30D158] font-semibold" x-text="appliedPromo.promo.formatted_discount + ' berhasil diterapkan'"></div>
                                    </div>
                                </div>
                                <span class="text-[12px] font-extrabold font-mono text-[#34C759] tabular-nums" x-text="'- Rp ' + formatRupiah(discountAmount)"></span>
                            </div>
                        </template>

                        <!-- Promo Input & Button -->
                        <template x-if="!appliedPromo">
                            <div>
                                <div class="flex items-center gap-2">
                                    <div class="relative flex-1">
                                        <input type="text" x-model="promoCodeInput" @keydown.enter.prevent="validateAndApplyPromo()"
                                            placeholder="Masukkan kode promo..."
                                            class="w-full h-10 pl-8 pr-3 rounded-[12px] text-[12.5px] uppercase font-mono font-bold bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                        <i data-lucide="tag" class="w-3.5 h-3.5 absolute left-2.5 top-3.5 text-black/40 dark:text-white/40"></i>
                                    </div>
                                    <button type="button" @click="validateAndApplyPromo()" :disabled="promoLoading || !promoCodeInput.trim()"
                                        class="h-10 px-3.5 rounded-[12px] text-[12.5px] font-semibold bg-black/[0.05] dark:bg-white/[0.08] hover:bg-[#007AFF] hover:text-white text-black dark:text-white transition-all disabled:opacity-40 disabled:pointer-events-none flex items-center gap-1.5 shrink-0">
                                        <span x-show="promoLoading" class="animate-spin w-3.5 h-3.5 border-2 border-current border-t-transparent rounded-full"></span>
                                        <span x-text="promoLoading ? 'Cek...' : 'Terapkan'"></span>
                                    </button>
                                </div>
                                <p x-show="promoError" x-text="promoError" class="text-[11px] text-red-500 font-medium mt-1.5 pl-1"></p>
                            </div>
                        </template>
                    </div>

                    <!-- Price Breakdown -->
                    <div class="border-t border-black/[0.06] dark:border-white/[0.08] pt-4 space-y-2 text-xs">
                        <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                            <span>{{ __('billing.estimated_bill') }}:</span>
                            <span class="font-mono tabular-nums text-black dark:text-white font-bold"
                                x-text="currentPrice <= 0 ? '{{ __('billing.free_rp_zero') }}' : ('Rp ' + formatRupiah(currentPrice))">
                            </span>
                        </div>

                        <!-- Diskon Kupon Promo -->
                        <div x-show="discountAmount > 0" class="flex items-center justify-between text-[#34C759] dark:text-[#30D158] font-semibold">
                            <span class="flex items-center gap-1">
                                <i data-lucide="ticket" class="w-3.5 h-3.5"></i>
                                <span x-text="'Potongan Promo (' + (appliedPromo?.promo?.code || '') + '):'"></span>
                            </span>
                            <span class="font-mono tabular-nums font-bold" x-text="'- Rp ' + formatRupiah(discountAmount)"></span>
                        </div>

                        <div x-show="totalPayable > 0"
                            class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                            <span>{{ __('billing.payment_gateway') }}</span>
                            <span class="font-mono text-[#007AFF] text-[11px] font-semibold">{{ __('billing.payment_gateway_tripay') }}</span>
                        </div>
                        <div
                            class="border-t border-black/[0.06] dark:border-white/[0.08] pt-3 flex items-baseline justify-between">
                            <span class="font-bold text-black dark:text-white text-sm">{{ __('billing.total_pay') }}:</span>
                            <span
                                class="font-bold text-xl sm:text-2xl text-[#007AFF] dark:text-[#0A84FF] font-mono tabular-nums"
                                x-text="totalPayable <= 0 ? 'Rp 0 (Gratis Promo)' : ('Rp ' + formatRupiah(totalPayable))"></span>
                        </div>
                    </div>

                    <!-- Submit Button with Double-Submit Prevention -->
                    @if (\App\Support\Context::hasPermission('billing.manage'))
                        <button type="submit" :disabled="isSubmitting"
                            class="w-full h-12 rounded-[14px] text-[15px] font-semibold shadow-sm transition-all flex items-center justify-center gap-2 group cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed active:scale-[0.98] focus-visible:ring-2 focus-visible:ring-[#007AFF] focus-visible:outline-none"
                            :class="totalPayable <= 0 ? 'bg-[#34C759] hover:bg-[#30D158] text-white shadow-[#34C759]/25' :
                                'bg-[#007AFF] hover:bg-[#0071E3] text-white shadow-[#007AFF]/25'">
                            <span x-show="isSubmitting"
                                class="animate-spin w-4 h-4 border-2 border-current border-t-transparent rounded-full"
                                aria-hidden="true"></span>
                            <i data-lucide="sparkles" class="w-4 h-4" x-show="!isSubmitting && totalPayable <= 0"
                                aria-hidden="true"></i>
                            <span
                                x-text="isSubmitting ? '{{ __('billing.process_order') }}' : (totalPayable <= 0 ? 'Aktifkan Paket dengan Voucher Promo' : '{{ __('billing.proceed_payment_tripay') }}')"></span>
                            <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"
                                x-show="!isSubmitting && totalPayable > 0" aria-hidden="true"></i>
                        </button>

                        <p class="text-[11px] text-center text-gray-500 dark:text-gray-400 leading-relaxed">
                            {{ __('billing.invoice_auto_notice') }}
                        </p>
                    @else
                        <div
                            class="p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-[#FF9500] text-xs rounded-[12px] text-center font-medium">
                            {{ __('billing.readonly_warning') }}
                        </div>
                    @endif

                    <!-- Trust Indicators -->
                    <div
                        class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-center gap-4 text-[10px] text-gray-500 dark:text-gray-400">
                        <span class="flex items-center gap-1">
                            <i data-lucide="lock" class="w-3.5 h-3.5 text-[#34C759]" aria-hidden="true"></i>
                            <span>{{ __('billing.encrypted_safe') }}</span>
                        </span>
                        <span class="flex items-center gap-1">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#007AFF]" aria-hidden="true"></i>
                            <span>{{ __('billing.data_protected') }}</span>
                        </span>
                        <span class="flex items-center gap-1">
                            <i data-lucide="zap" class="w-3.5 h-3.5 text-[#FF9500]" aria-hidden="true"></i>
                            <span>{{ __('billing.instant_activation') }}</span>
                        </span>
                    </div>
                </div>
            </div>

        </form>
    </div>
@endsection
