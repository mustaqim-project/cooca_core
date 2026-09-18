@extends('layouts.admin', [
    'title' => 'CMS Paket & Harga - Admin Console',
    'headerTitle' => 'CMS Paket & Harga',
    'headerSubtitle' => 'Kelola katalog paket subscription, top-up token AI, dan storage yang dijual di halaman checkout tenant',
])

@php
    $tabMap = [
        App\Models\BillingPackage::TYPE_SUBSCRIPTION => [
            'label' => 'Paket & Durasi Subscription',
            'icon' => 'layers-3',
            'tint' => 'text-[#007AFF] dark:text-[#0A84FF]',
            'bgTint' => 'bg-[#007AFF]/10',
            'activeText' => 'bg-[#007AFF] text-white shadow-sm',
        ],
        App\Models\BillingPackage::TYPE_AI_TOKEN => [
            'label' => 'Paket Top Up Token AI',
            'icon' => 'sparkles',
            'tint' => 'text-[#AF52DE] dark:text-[#BF5AF2]',
            'bgTint' => 'bg-[#AF52DE]/10',
            'activeText' => 'bg-[#AF52DE] text-white shadow-sm',
        ],
        App\Models\BillingPackage::TYPE_STORAGE => [
            'label' => 'Paket Top Up Storage',
            'icon' => 'hard-drive',
            'tint' => 'text-[#30B0C7] dark:text-[#40C8E0]',
            'bgTint' => 'bg-[#30B0C7]/10',
            'activeText' => 'bg-[#30B0C7] text-white shadow-sm',
        ],
    ];
@endphp

@section('content')
    <div class="space-y-6 max-w-7xl w-full min-w-0 mx-auto pb-28 lg:pb-10" x-data="{
        packages: {{ \Illuminate\Support\Js::from(
            $packages->mapWithKeys(
                fn($p) => [
                    $p->id => [
                        'id' => $p->id,
                        'name' => $p->name,
                        'description' => $p->description ?? '',
                        'price' => (string) $p->price,
                        'duration_days' => $p->duration_days,
                        'token_quantity' => $p->token_quantity,
                        'token_expiry_days' => $p->token_expiry_days,
                        'storage_gb' => $p->storage_bytes ? round($p->storage_bytes / 1073741824, 2) : null,
                    ],
                ],
            ),
        ) }},
        editOpen: false,
        editForm: { id: '', name: '', description: '', price: '', duration_days: '', token_quantity: '', token_expiry_days: '', storage_gb: '' },
        openEdit(id) {
            this.editForm = Object.assign({}, this.packages[id]);
            this.editOpen = true;
        },
        closeEdit() { this.editOpen = false; }
    }">

        <!-- Page Header Bento -->
        <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-xl p-5 sm:p-6 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-[16px] bg-[#007AFF]/10 flex items-center justify-center text-[#007AFF] dark:text-[#0A84FF] shrink-0">
                    <i data-lucide="layers-3" class="w-6 h-6" stroke-width="1.5"></i>
                </div>
                <div>
                    <h1 class="text-[19px] sm:text-[20px] font-bold text-black dark:text-white tracking-tight">Katalog Paket Billing Platform</h1>
                    <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Semua paket aktif langsung tersedia di checkout tenant. Harga terpusat dan konsisten.</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[12px] font-semibold bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 border border-black/[0.04] dark:border-white/[0.06]">
                    <i data-lucide="package" class="w-4 h-4 text-[#30B0C7] dark:text-[#40C8E0]" stroke-width="1.5"></i>
                    <span class="tabular-nums font-bold">{{ $packages->count() }}</span>
                    <span>Paket di Katalog</span>
                </span>
            </div>
        </div>

        <!-- Apple Pill Segmented Control -->
        <div class="inline-flex flex-wrap p-1.5 rounded-[18px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.04] dark:border-white/[0.06] text-[13px] font-medium gap-1">
            @foreach ($tabMap as $tabKey => $tabItem)
                <a href="{{ route('admin.billing-packages.index', $tabKey) }}"
                    class="px-4 py-2 rounded-[12px] inline-flex items-center gap-2 {{ $type === $tabKey ? $tabItem['activeText'] : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.03] dark:hover:bg-white/[0.05]' }} transition-all font-semibold active:scale-[0.98]">
                    <i data-lucide="{{ $tabItem['icon'] }}" class="w-4 h-4 {{ $type === $tabKey ? '' : $tabItem['tint'] }}" stroke-width="1.5"></i>
                    <span>{{ $tabItem['label'] }}</span>
                    @if (isset($counts[$tabKey]) && $counts[$tabKey] > 0)
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold tabular-nums {{ $type === $tabKey ? 'bg-white/20 text-white' : 'bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55' }}">
                            {{ $counts[$tabKey] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>

        <!-- Informational Callout per Catalog Type -->
        @if ($type === App\Models\BillingPackage::TYPE_AI_TOKEN)
            <div class="rounded-[18px] p-4 bg-[#AF52DE]/8 border border-[#AF52DE]/20 text-[12px] text-black/70 dark:text-white/70 flex items-start gap-3 backdrop-blur-sm">
                <div class="w-7 h-7 rounded-[8px] bg-[#AF52DE]/15 flex items-center justify-center text-[#AF52DE] dark:text-[#BF5AF2] shrink-0 mt-0.5">
                    <i data-lucide="info" class="w-4 h-4" stroke-width="2"></i>
                </div>
                <div class="leading-relaxed">
                    <span class="font-bold text-[#7C3AA6] dark:text-[#BF5AF2]">Katalog Top-Up Token AI:</span> Menampilkan paket top-up yang dapat dibeli tenant secara mandiri. Untuk kuota manual perorangan, gunakan menu <a href="{{ route('admin.ai-tokens.index') }}" class="text-[#AF52DE] dark:text-[#BF5AF2] hover:underline font-bold">Monitoring Token AI</a>.
                </div>
            </div>
        @elseif($type === App\Models\BillingPackage::TYPE_STORAGE)
            <div class="rounded-[18px] p-4 bg-[#30B0C7]/8 border border-[#30B0C7]/20 text-[12px] text-black/70 dark:text-white/70 flex items-start gap-3 backdrop-blur-sm">
                <div class="w-7 h-7 rounded-[8px] bg-[#30B0C7]/15 flex items-center justify-center text-[#30B0C7] dark:text-[#40C8E0] shrink-0 mt-0.5">
                    <i data-lucide="info" class="w-4 h-4" stroke-width="2"></i>
                </div>
                <div class="leading-relaxed">
                    <span class="font-bold text-[#1F7A89] dark:text-[#40C8E0]">Katalog Top-Up Storage:</span> Kapasitas storage bersifat permanen dan diakumulasikan ke kapasitas dasar akun owner. Buat opsi paket GB berbeda sesuai kebutuhan tenant.
                </div>
            </div>
        @endif

        <!-- Default Pricing Bento Box (Single Source of Truth) -->
        @if ($type === App\Models\BillingPackage::TYPE_SUBSCRIPTION)
            <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-[#5856D6]/20 backdrop-blur-xl p-5 sm:p-6 shadow-sm">
                <form method="POST" action="{{ route('admin.settings.billing') }}" class="space-y-4">
                    @csrf
                    <div class="flex items-center justify-between pb-3.5 border-b border-black/[0.06] dark:border-white/[0.08]">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-[10px] bg-[#5856D6]/10 flex items-center justify-center text-[#5856D6] dark:text-[#5E5CE6]">
                                <i data-lucide="settings-2" class="w-4 h-4" stroke-width="1.5"></i>
                            </div>
                            <div>
                                <h3 class="text-[14px] font-bold text-[#5856D6] dark:text-[#5E5CE6]">Harga Bawaan &amp; Kuota Langganan Cooca</h3>
                                <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">Nilai fallback yang dipakai saat tenant memilih Core tanpa paket khusus.</p>
                            </div>
                        </div>
                        <button type="submit"
                            class="h-9 px-4 rounded-[12px] text-[12px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all inline-flex items-center gap-1.5 shadow-sm">
                            <i data-lucide="save" class="w-3.5 h-3.5" stroke-width="2"></i>
                            <span>Simpan Default</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-1">
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Bulanan (Rp)</label>
                            <input type="number" name="subscription_price_monthly"
                                value="{{ $subscriptionPriceMonthly ?? 129000 }}" min="0" step="1000"
                                class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] tabular-nums font-bold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition">
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Tahunan (Rp)</label>
                            <input type="number" name="subscription_price_annual"
                                value="{{ $subscriptionPriceAnnual ?? 1290000 }}" min="0" step="1000"
                                class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] tabular-nums font-bold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition">
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Kuota Token AI / Bulan</label>
                            <input type="number" name="subscription_ai_tokens_monthly"
                                value="{{ $subscriptionAiTokensMonthly ?? 10000000 }}" min="0" step="100000"
                                class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] tabular-nums font-bold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition">
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Badge Promo Tahunan</label>
                            <input type="text" name="subscription_annual_discount_badge"
                                value="{{ $subscriptionAnnualDiscountBadge ?? 'Hemat 2 Bulan' }}" maxlength="64"
                                class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] font-bold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition">
                        </div>
                    </div>
                </form>
            </div>
        @elseif($type === App\Models\BillingPackage::TYPE_AI_TOKEN)
            <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-[#AF52DE]/20 backdrop-blur-xl p-5 sm:p-6 shadow-sm">
                <form method="POST" action="{{ route('admin.settings.billing') }}" class="space-y-4">
                    @csrf
                    <div class="flex items-center justify-between pb-3.5 border-b border-black/[0.06] dark:border-white/[0.08]">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-[10px] bg-[#AF52DE]/10 flex items-center justify-center text-[#AF52DE] dark:text-[#BF5AF2]">
                                <i data-lucide="sparkles" class="w-4 h-4" stroke-width="1.5"></i>
                            </div>
                            <div>
                                <h3 class="text-[14px] font-bold text-[#AF52DE] dark:text-[#BF5AF2]">Default Top-Up Token Instant (Non-Katalog)</h3>
                                <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">Dipakai bila tenant melakukan top up instan tanpa memilih paket katalog.</p>
                            </div>
                        </div>
                        <button type="submit"
                            class="h-9 px-4 rounded-[12px] text-[12px] font-bold text-white bg-[#AF52DE] hover:bg-[#9A45C6] active:scale-[0.98] transition-all inline-flex items-center gap-1.5 shadow-sm">
                            <i data-lucide="save" class="w-3.5 h-3.5" stroke-width="2"></i>
                            <span>Simpan Default</span>
                        </button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Harga per Top Up (Rp)</label>
                            <input type="number" name="ai_token_topup_price" value="{{ $aiTokenTopupPrice ?? 50000 }}" min="0" step="1000"
                                class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] tabular-nums font-bold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#AF52DE]/40">
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Jumlah Token per Top Up</label>
                            <input type="number" name="ai_token_topup_amount" value="{{ $aiTokenTopupAmount ?? 1000000 }}" min="1" step="10000"
                                class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] tabular-nums font-bold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#AF52DE]/40">
                        </div>
                    </div>
                </form>
            </div>
        @elseif($type === App\Models\BillingPackage::TYPE_STORAGE)
            <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-[#30B0C7]/20 backdrop-blur-xl p-5 sm:p-6 shadow-sm">
                <form method="POST" action="{{ route('admin.settings.billing') }}" class="space-y-4">
                    @csrf
                    <div class="flex items-center justify-between pb-3.5 border-b border-black/[0.06] dark:border-white/[0.08]">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-[10px] bg-[#30B0C7]/10 flex items-center justify-center text-[#30B0C7] dark:text-[#40C8E0]">
                                <i data-lucide="hard-drive" class="w-4 h-4" stroke-width="1.5"></i>
                            </div>
                            <div>
                                <h3 class="text-[14px] font-bold text-[#30B0C7] dark:text-[#40C8E0]">Default Top-Up Storage &amp; Kapasitas Dasar Owner</h3>
                                <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">Kapasitas dasar berlaku untuk semua workspace milik owner.</p>
                            </div>
                        </div>
                        <button type="submit"
                            class="h-9 px-4 rounded-[12px] text-[12px] font-bold text-white bg-[#30B0C7] hover:bg-[#2998AF] active:scale-[0.98] transition-all inline-flex items-center gap-1.5 shadow-sm">
                            <i data-lucide="save" class="w-3.5 h-3.5" stroke-width="2"></i>
                            <span>Simpan Default</span>
                        </button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1">
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Harga per Top Up (Rp)</label>
                            <input type="number" name="storage_topup_price" value="{{ $storageTopupPrice ?? 50000 }}" min="0" step="1000"
                                class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] tabular-nums font-bold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#30B0C7]/40">
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">GB per Top Up</label>
                            <input type="number" name="storage_topup_gb" value="{{ $storageTopupGb ?? 1 }}" min="1"
                                class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] tabular-nums font-bold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#30B0C7]/40">
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Kapasitas Dasar Owner (GB)</label>
                            <input type="number" name="owner_storage_limit_gb" value="{{ $ownerStorageLimitGb ?? 3 }}" min="1"
                                class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] tabular-nums font-bold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#30B0C7]/40">
                        </div>
                    </div>
                </form>
            </div>
        @endif

        <!-- Main 2-Column Grid: Create Form + Package Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-[360px_1fr] gap-6 items-start">

            <!-- Create Package Bento Form -->
            <form method="POST" action="{{ route('admin.billing-packages.store') }}"
                class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-xl p-5 sm:p-6 space-y-4 shadow-sm lg:sticky lg:top-20">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">

                <div class="border-b border-black/[0.06] dark:border-white/[0.08] pb-3.5">
                    <h2 class="text-[15px] font-bold text-black dark:text-white flex items-center gap-2">
                        <i data-lucide="plus-circle" class="w-4.5 h-4.5 text-[#34C759] dark:text-[#30D158]" stroke-width="2"></i>
                        <span>Tambah Paket Baru</span>
                    </h2>
                    <p class="text-[11px] text-black/50 dark:text-white/50 mt-1">Paket langsung muncul di opsi checkout tenant.</p>
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Nama Paket <span class="text-[#FF3B30]">*</span></label>
                    <input name="name" required maxlength="120"
                        placeholder="Contoh: Core 90 Hari / Token 50 Juta / Storage 2 GB"
                        class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition">
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Harga (Rp) <span class="text-[#FF3B30]">*</span></label>
                    <input type="number" name="price" required min="0" step="any" placeholder="0"
                        class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] tabular-nums font-bold text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition">
                    <p class="text-[11px] text-[#34C759] dark:text-[#30D158] mt-1.5 font-medium inline-flex items-center gap-1.5">
                        <i data-lucide="info" class="w-3.5 h-3.5 shrink-0"></i>
                        <span>Set <strong>0</strong> untuk promo marketing/trial gratis (aktif instan tanpa pembayaran).</span>
                    </p>
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Deskripsi Singkat</label>
                    <textarea name="description" rows="2" maxlength="1000" placeholder="Keterangan manfaat paket..."
                        class="w-full px-3.5 py-2.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] leading-relaxed text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition"></textarea>
                </div>

                @if ($type === App\Models\BillingPackage::TYPE_SUBSCRIPTION)
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Durasi (hari) <span class="text-[#FF3B30]">*</span></label>
                            <input type="number" name="duration_days" required min="1" placeholder="30"
                                class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] tabular-nums font-bold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition">
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Token AI (opsional)</label>
                            <input type="number" name="token_quantity" min="1" placeholder="10000000"
                                class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition">
                        </div>
                    </div>
                @elseif($type === App\Models\BillingPackage::TYPE_AI_TOKEN)
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Jumlah Token <span class="text-[#FF3B30]">*</span></label>
                            <input type="number" name="token_quantity" required min="1" placeholder="50000000"
                                class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] tabular-nums font-bold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#AF52DE]/40 transition">
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Masa Berlaku (hari) <span class="text-[#FF3B30]">*</span></label>
                            <input type="number" name="token_expiry_days" required min="1" value="30"
                                class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] tabular-nums font-bold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#AF52DE]/40 transition">
                        </div>
                    </div>
                @else
                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Tambahan Storage (GB) <span class="text-[#FF3B30]">*</span></label>
                        <input type="number" name="storage_gb" required min="0.01" step="0.01" placeholder="2"
                            class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] tabular-nums font-bold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#30B0C7]/40 transition">
                    </div>
                @endif

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Nomor Urutan Tampil</label>
                    <input type="number" name="sort_order" min="0" max="999" value="0"
                        class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition">
                </div>

                <button type="submit"
                    class="w-full h-11 rounded-[14px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all inline-flex items-center justify-center gap-2 shadow-sm shadow-[#007AFF]/25">
                    <i data-lucide="plus" class="w-4 h-4" stroke-width="2"></i>
                    <span>Simpan Paket Baru</span>
                </button>
            </form>

            <!-- Package Cards List Bento -->
            <div class="space-y-4">
                @forelse($packages as $package)
                    <div class="rounded-[22px] p-5 sm:p-6 border backdrop-blur-xl transition-all {{ $package->is_active ? 'border-black/[0.06] dark:border-white/[0.08] bg-white/80 dark:bg-[#1C1C1E]/80 shadow-sm hover:shadow-md' : 'border-black/[0.08] dark:border-white/[0.1] bg-black/[0.02] dark:bg-white/[0.02] opacity-85' }}">
                        <!-- Card Header -->
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2.5 flex-wrap">
                                    <h3 class="text-[16px] font-bold text-black dark:text-white truncate">{{ $package->name }}</h3>
                                    @if ($package->is_active)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                            <i data-lucide="check" class="w-3 h-3 stroke-[2.5]"></i>
                                            <span>Aktif</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55">
                                            <i data-lucide="pause" class="w-3 h-3 stroke-[2.5]"></i>
                                            <span>Nonaktif</span>
                                        </span>
                                    @endif
                                </div>
                                @if ($package->description)
                                    <p class="text-[12px] text-black/55 dark:text-white/55 mt-1 leading-relaxed">{{ $package->description }}</p>
                                @endif
                                <div class="text-[11px] text-black/40 dark:text-white/40 font-mono mt-1">Kode: {{ $package->code }}</div>
                            </div>

                            <form method="POST" action="{{ route('admin.billing-packages.toggle', $package) }}">
                                @csrf
                                <button type="submit"
                                    class="h-8 px-3 rounded-[10px] text-[12px] font-semibold transition-all inline-flex items-center gap-1.5 {{ $package->is_active ? 'bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A] hover:bg-[#FF3B30]/15' : 'bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] hover:bg-[#34C759]/15' }} active:scale-[0.98]">
                                    <i data-lucide="{{ $package->is_active ? 'pause-circle' : 'play-circle' }}" class="w-3.5 h-3.5" stroke-width="1.75"></i>
                                    <span>{{ $package->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</span>
                                </button>
                            </form>
                        </div>

                        <!-- Package Metrics Bento Grid -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4">
                            <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06]">
                                <div class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Harga</div>
                                <div class="tabular-nums font-extrabold text-[#34C759] dark:text-[#30D158] text-[16px] sm:text-[17px] mt-0.5">
                                    @if ((float) $package->price <= 0)
                                        <span class="inline-flex items-center gap-1.5 text-[#34C759] dark:text-[#30D158]">
                                            <span>Rp 0</span>
                                            <span class="text-[10px] px-2 py-0.5 rounded-md bg-[#34C759]/15 font-bold">PROMO GRATIS</span>
                                        </span>
                                    @else
                                        Rp {{ number_format($package->price, 0, ',', '.') }}
                                    @endif
                                </div>
                            </div>

                            @if ($type === App\Models\BillingPackage::TYPE_SUBSCRIPTION)
                                <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06]">
                                    <div class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Durasi</div>
                                    <div class="tabular-nums font-bold text-black dark:text-white text-[16px] sm:text-[17px] mt-0.5">{{ $package->duration_days }} hari</div>
                                </div>
                                <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] col-span-2 sm:col-span-1">
                                    <div class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Token AI Paket</div>
                                    <div class="tabular-nums font-bold text-[#AF52DE] dark:text-[#BF5AF2] text-[16px] sm:text-[17px] mt-0.5">
                                        {{ $package->token_quantity ? number_format($package->token_quantity, 0, ',', '.') : '-' }}
                                    </div>
                                </div>
                            @elseif($type === App\Models\BillingPackage::TYPE_AI_TOKEN)
                                <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06]">
                                    <div class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Jumlah Token</div>
                                    <div class="tabular-nums font-bold text-[#AF52DE] dark:text-[#BF5AF2] text-[16px] sm:text-[17px] mt-0.5">{{ number_format($package->token_quantity, 0, ',', '.') }}</div>
                                </div>
                                <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] col-span-2 sm:col-span-1">
                                    <div class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Masa Berlaku</div>
                                    <div class="tabular-nums font-bold text-black dark:text-white text-[16px] sm:text-[17px] mt-0.5">{{ $package->token_expiry_days ?? 30 }} hari</div>
                                </div>
                            @else
                                <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06]">
                                    <div class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Kapasitas Storage</div>
                                    <div class="tabular-nums font-bold text-[#30B0C7] dark:text-[#40C8E0] text-[16px] sm:text-[17px] mt-0.5">
                                        {{ number_format(($package->storage_bytes ?? 0) / 1073741824, 2, ',', '.') }} GB
                                    </div>
                                </div>
                                <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] col-span-2 sm:col-span-1">
                                    <div class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Masa Aktif</div>
                                    <div class="font-bold text-[#34C759] dark:text-[#30D158] text-[16px] sm:text-[17px] mt-0.5">Permanen</div>
                                </div>
                            @endif
                        </div>

                        <!-- Card Actions -->
                        <div class="border-t border-black/[0.04] dark:border-white/[0.06] pt-3.5 mt-4 flex items-center justify-end gap-2">
                            <button type="button" @click="openEdit('{{ $package->id }}')"
                                class="h-8 px-3.5 rounded-[10px] text-[12px] font-semibold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.98] transition-all inline-flex items-center gap-1.5">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5" stroke-width="2"></i>
                                <span>Edit Paket</span>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="rounded-[24px] p-12 text-center border border-dashed border-black/15 dark:border-white/15 bg-black/[0.01] dark:bg-white/[0.02]">
                        <div class="w-14 h-14 mx-auto rounded-[16px] bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center text-black/35 dark:text-white/35 mb-3">
                            <i data-lucide="package-open" class="w-7 h-7" stroke-width="1.5"></i>
                        </div>
                        <h4 class="text-[16px] font-bold text-black dark:text-white">Belum ada paket pada katalog ini</h4>
                        <p class="text-[12px] text-black/50 dark:text-white/50 mt-1 max-w-sm mx-auto">Gunakan formulir di samping untuk menambahkan paket baru pada katalog {{ strtolower($tabMap[$type]['label'] ?? 'ini') }}.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Edit Package Modal (Apple Inset Dialog / Sheet) -->
        <div x-show="editOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-md transition-all">
            <div @click.away="closeEdit()"
                class="rounded-[24px] sm:rounded-[28px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/15 p-6 sm:p-7 w-full max-w-lg space-y-4 max-h-[90vh] overflow-y-auto shadow-2xl">
                
                <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3.5">
                    <h4 class="text-[16px] font-bold text-black dark:text-white flex items-center gap-2">
                        <i data-lucide="edit-3" class="w-4.5 h-4.5 text-[#007AFF] dark:text-[#0A84FF]" stroke-width="2"></i>
                        <span>Edit Detail Paket</span>
                    </h4>
                    <button type="button" @click="closeEdit()"
                        class="p-1.5 rounded-[10px] text-black/40 dark:text-white/40 hover:bg-black/5 dark:hover:bg-white/10 active:scale-[0.98] transition-colors">
                        <i data-lucide="x" class="w-5 h-5" stroke-width="2"></i>
                    </button>
                </div>

                <form :action="'/admin/billing-packages/' + editForm.id" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="type" value="{{ $type }}">

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Nama Paket <span class="text-[#FF3B30]">*</span></label>
                        <input type="text" name="name" x-model="editForm.name" required maxlength="120"
                            class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition">
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Harga (Rp) <span class="text-[#FF3B30]">*</span></label>
                        <input type="number" name="price" x-model="editForm.price" required min="0" step="any"
                            class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] tabular-nums font-bold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition">
                        <p class="text-[11px] text-[#34C759] dark:text-[#30D158] mt-1.5 font-medium inline-flex items-center gap-1.5">
                            <i data-lucide="info" class="w-3.5 h-3.5 shrink-0"></i>
                            <span>Set <strong>0</strong> untuk promo trial gratis (tanpa konfirmasi pembayaran).</span>
                        </p>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Deskripsi</label>
                        <textarea name="description" x-model="editForm.description" rows="2" maxlength="1000"
                            class="w-full px-3.5 py-2.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] leading-relaxed text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition"></textarea>
                    </div>

                    @if ($type === App\Models\BillingPackage::TYPE_SUBSCRIPTION)
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Durasi (hari) <span class="text-[#FF3B30]">*</span></label>
                                <input type="number" name="duration_days" x-model="editForm.duration_days" required min="1"
                                    class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] tabular-nums font-bold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition">
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Token AI (opsional)</label>
                                <input type="number" name="token_quantity" x-model="editForm.token_quantity" min="1"
                                    class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition">
                            </div>
                        </div>
                    @elseif($type === App\Models\BillingPackage::TYPE_AI_TOKEN)
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Jumlah Token <span class="text-[#FF3B30]">*</span></label>
                                <input type="number" name="token_quantity" x-model="editForm.token_quantity" required min="1"
                                    class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] tabular-nums font-bold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#AF52DE]/40 transition">
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Masa Berlaku (hari) <span class="text-[#FF3B30]">*</span></label>
                                <input type="number" name="token_expiry_days" x-model="editForm.token_expiry_days" required min="1"
                                    class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] tabular-nums font-bold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#AF52DE]/40 transition">
                            </div>
                        </div>
                    @else
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Storage (GB) <span class="text-[#FF3B30]">*</span></label>
                            <input type="number" name="storage_gb" x-model="editForm.storage_gb" required min="0.01" step="0.01"
                                class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] tabular-nums font-bold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#30B0C7]/40 transition">
                        </div>
                    @endif

                    <div class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-end gap-2.5">
                        <button type="button" @click="closeEdit()"
                            class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] active:scale-[0.98] transition">Batal</button>
                        <button type="submit"
                            class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all inline-flex items-center gap-2 shadow-sm shadow-[#007AFF]/25">
                            <i data-lucide="save" class="w-4 h-4" stroke-width="2"></i>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
