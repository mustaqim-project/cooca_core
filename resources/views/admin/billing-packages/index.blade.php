@extends('layouts.admin', [
    'title' => 'CMS Paket & Harga — Admin Console',
    'headerTitle' => 'CMS Paket & Harga',
    'headerSubtitle' => 'Kelola katalog paket subscription, top-up token AI, dan storage yang dijual di halaman checkout tenant'
])

@php
    $tabMap = [
        App\Models\BillingPackage::TYPE_SUBSCRIPTION => ['label' => 'Paket & Durasi Subscription', 'icon' => 'layers', 'tint' => 'text-[#007AFF] dark:text-[#0A84FF]', 'bgTint' => 'bg-[#007AFF]/10', 'activeText' => 'bg-[#007AFF] text-white'],
        App\Models\BillingPackage::TYPE_AI_TOKEN => ['label' => 'Paket Top Up Token', 'icon' => 'sparkles', 'tint' => 'text-[#AF52DE] dark:text-[#BF5AF2]', 'bgTint' => 'bg-[#AF52DE]/10', 'activeText' => 'bg-[#AF52DE] text-white'],
        App\Models\BillingPackage::TYPE_STORAGE => ['label' => 'Paket Top Up Storage', 'icon' => 'hard-drive', 'tint' => 'text-[#30B0C7] dark:text-[#40C8E0]', 'bgTint' => 'bg-[#30B0C7]/10', 'activeText' => 'bg-[#30B0C7] text-white'],
    ];
@endphp

@section('content')
<div class="space-y-6" x-data="{
    packages: {!! \Illuminate\Support\Js::from($packages->mapWithKeys(fn ($p) => [$p->id => [
        'id' => $p->id,
        'name' => $p->name,
        'description' => $p->description ?? '',
        'price' => (string) $p->price,
        'duration_days' => $p->duration_days,
        'token_quantity' => $p->token_quantity,
        'token_expiry_days' => $p->token_expiry_days,
        'storage_gb' => $p->storage_bytes ? round($p->storage_bytes / 1073741824, 2) : null,
    ]])) !!},
    editOpen: false,
    editForm: { id: '', name: '', description: '', price: '', duration_days: '', token_quantity: '', token_expiry_days: '', storage_gb: '' },
    openEdit(id) { this.editForm = Object.assign({}, this.packages[id]); this.editOpen = true; },
    closeEdit() { this.editOpen = false; }
}">

    <!-- Page Intro -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-[22%] bg-[#007AFF]/10 flex items-center justify-center text-[#007AFF] dark:text-[#0A84FF] shrink-0"><i data-lucide="layers-3" class="w-5 h-5" stroke-width="1.5"></i></div>
            <div>
                <h1 class="text-[20px] font-bold text-black dark:text-white tracking-tight">Katalog Paket Billing Platform</h1>
                <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Semua paket aktif langsung tersedia di halaman checkout tenant. Harga dikelola terpusat di sini.</p>
            </div>
        </div>
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55"><i data-lucide="package" class="w-3.5 h-3.5 text-[#30B0C7] dark:text-[#40C8E0]" stroke-width="1.5"></i><span>{{ $packages->count() }} paket pada katalog ini</span></span>
    </div>

    <!-- Catalog Type Tabs (Segmented Control) -->
    <div class="inline-flex flex-wrap p-0.5 rounded-[9px] bg-black/[0.06] dark:bg-white/[0.08] text-[13px] font-medium">
        @foreach($tabMap as $tabKey => $tabItem)
        <a href="{{ route('admin.billing-packages.index', $tabKey) }}" class="px-3 py-1.5 rounded-[7px] inline-flex items-center gap-1.5 {{ $type === $tabKey ? $tabItem['activeText'] : 'text-black/55 dark:text-white/55 hover:bg-black/5 dark:hover:bg-white/10' }} transition-all">
            <i data-lucide="{{ $tabItem['icon'] }}" class="w-4 h-4 {{ $type === $tabKey ? '' : $tabItem['tint'] }}" stroke-width="1.5"></i>
            <span>{{ $tabItem['label'] }}</span>
            @if(isset($counts[$tabKey]) && $counts[$tabKey] > 0)
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold {{ $type === $tabKey ? 'bg-white/20 text-white' : 'bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55' }}">{{ $counts[$tabKey] }}</span>
            @endif
        </a>
        @endforeach
    </div>

    <!-- Informational callout per catalog type -->
    @if($type === App\Models\BillingPackage::TYPE_AI_TOKEN)
    <div class="rounded-[12px] px-3.5 py-3 bg-[#AF52DE]/8 border border-[#AF52DE]/20 text-[12px] text-black/60 dark:text-white/60 flex items-start gap-2.5">
        <i data-lucide="info" class="w-4 h-4 text-[#AF52DE] dark:text-[#BF5AF2] mt-0.5 shrink-0" stroke-width="1.5"></i>
        <div><span class="font-semibold text-[#7C3AA6] dark:text-[#BF5AF2]">Catatan:</span> katalog ini menampilkan <em class="text-black/70 dark:text-white/70">paket top-up token yang dijual</em> ke tenant. Untuk pemberian token manual (operasional), gunakan menu <a href="{{ route('admin.ai-tokens.index') }}" class="text-[#AF52DE] dark:text-[#BF5AF2] hover:underline font-semibold">Monitoring Token AI</a>.</div>
    </div>
    @elseif($type === App\Models\BillingPackage::TYPE_STORAGE)
    <div class="rounded-[12px] px-3.5 py-3 bg-[#30B0C7]/8 border border-[#30B0C7]/20 text-[12px] text-black/60 dark:text-white/60 flex items-start gap-2.5">
        <i data-lucide="info" class="w-4 h-4 text-[#30B0C7] dark:text-[#40C8E0] mt-0.5 shrink-0" stroke-width="1.5"></i>
        <div><span class="font-semibold text-[#1F7A89] dark:text-[#40C8E0]">Catatan:</span> kapasitas storage yang dibeli bersifat permanen dan ditambahkan ke kapasitas dasar owner. Buat paket berbeda untuk nominal GB bervariasi.</div>
    </div>
    @endif

    <!-- Default Core pricing (single source of truth for subscription catalog) -->
    @if($type === App\Models\BillingPackage::TYPE_SUBSCRIPTION)
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-[#5856D6]/25 p-5">
        <form method="POST" action="{{ route('admin.settings.billing') }}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 items-end">
            @csrf
            <div class="md:col-span-2 xl:col-span-4">
                <p class="text-[13px] font-semibold text-[#5856D6] dark:text-[#5E5CE6] flex items-center gap-2"><i data-lucide="settings-2" class="w-4 h-4" stroke-width="1.5"></i>Harga Bawaan &amp; Kuota Cooca UMKM</p>
                <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">Nilai default yang dipakai saat tenant memilih Core tanpa paket katalog. Dikelola di satu tempat ini.</p>
            </div>
            <div>
                <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Bulanan (Rp)</label>
                <input type="number" name="subscription_price_monthly" value="{{ $subscriptionPriceMonthly ?? 129000 }}" min="0" step="1000" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>
            <div>
                <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Tahunan (Rp)</label>
                <input type="number" name="subscription_price_annual" value="{{ $subscriptionPriceAnnual ?? 1290000 }}" min="0" step="1000" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>
            <div>
                <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Kuota Token AI / Bulan</label>
                <input type="number" name="subscription_ai_tokens_monthly" value="{{ $subscriptionAiTokensMonthly ?? 10000000 }}" min="0" step="100000" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Badge Promo Tahunan</label>
                    <input type="text" name="subscription_annual_discount_badge" value="{{ $subscriptionAnnualDiscountBadge ?? 'Hemat 2 Bulan' }}" maxlength="64" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
                <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all inline-flex items-center gap-1.5 shrink-0 shadow-[0_1px_2px_rgba(0,122,255,0.25)]"><i data-lucide="save" class="w-3.5 h-3.5" stroke-width="1.5"></i><span class="hidden sm:inline">Simpan Default</span></button>
            </div>
        </form>
    </div>
    @endif

    <!-- Default token / storage top-up pricing (non-catalog) -->
    @if($type === App\Models\BillingPackage::TYPE_AI_TOKEN)
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-[#AF52DE]/25 p-5">
        <form method="POST" action="{{ route('admin.settings.billing') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            @csrf
            <div class="md:col-span-2 lg:col-span-4">
                <p class="text-[13px] font-semibold text-[#AF52DE] dark:text-[#BF5AF2] flex items-center gap-2"><i data-lucide="settings-2" class="w-4 h-4" stroke-width="1.5"></i>Default Top-Up Token Instant (Non-Katalog)</p>
                <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">Dipakai saat tenant melakukan top-up tanpa memilih paket dari katalog. Nilai katalog yang aktif akan menimpa nilai default ini.</p>
            </div>
            <div>
                <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Harga per Top Up (Rp)</label>
                <input type="number" name="ai_token_topup_price" value="{{ $aiTokenTopupPrice ?? 50000 }}" min="0" step="1000" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#AF52DE]/50">
            </div>
            <div>
                <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Jumlah Token per Top Up</label>
                <input type="number" name="ai_token_topup_amount" value="{{ $aiTokenTopupAmount ?? 1000000 }}" min="1" step="10000" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#AF52DE]/50">
            </div>
            <div class="flex items-end justify-end md:col-span-2 lg:col-span-2">
                <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#AF52DE] hover:bg-[#9A45C6] active:scale-[0.97] active:opacity-80 transition-all inline-flex items-center gap-1.5"><i data-lucide="save" class="w-3.5 h-3.5" stroke-width="1.5"></i>Simpan Default Top-Up</button>
            </div>
        </form>
    </div>
    @elseif($type === App\Models\BillingPackage::TYPE_STORAGE)
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-[#30B0C7]/25 p-5">
        <form method="POST" action="{{ route('admin.settings.billing') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            @csrf
            <div class="md:col-span-2 lg:col-span-4">
                <p class="text-[13px] font-semibold text-[#30B0C7] dark:text-[#40C8E0] flex items-center gap-2"><i data-lucide="settings-2" class="w-4 h-4" stroke-width="1.5"></i>Default Top-Up Storage &amp; Kapasitas Dasar Owner</p>
                <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">Kapasitas dasar owner berlaku untuk seluruh bisnis miliknya; nilai default top-up dipakai bila tenant tidak memilih paket katalog.</p>
            </div>
            <div>
                <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Harga per Top Up (Rp)</label>
                <input type="number" name="storage_topup_price" value="{{ $storageTopupPrice ?? 50000 }}" min="0" step="1000" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#30B0C7]/50">
            </div>
            <div>
                <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">GB per Top Up default</label>
                <input type="number" name="storage_topup_gb" value="{{ $storageTopupGb ?? 1 }}" min="1" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#30B0C7]/50">
            </div>
            <div>
                <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Kapasitas Dasar Owner (GB)</label>
                <input type="number" name="owner_storage_limit_gb" value="{{ $ownerStorageLimitGb ?? 3 }}" min="1" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#30B0C7]/50">
            </div>
            <div class="flex items-end justify-end">
                <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#30B0C7] hover:bg-[#2998AF] active:scale-[0.97] active:opacity-80 transition-all inline-flex items-center gap-1.5"><i data-lucide="save" class="w-3.5 h-3.5" stroke-width="1.5"></i>Simpan Default Top-Up</button>
            </div>
        </form>
    </div>
    @endif

    <!-- Main 2-Column Grid: Create Form + Package List -->
    <div class="grid grid-cols-1 lg:grid-cols-[340px_1fr] gap-5 items-start">

        <!-- Create Package Form -->
        <form method="POST" action="{{ route('admin.billing-packages.store') }}" class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-4 lg:sticky lg:top-20">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">

            <div class="border-b border-black/5 dark:border-white/10 pb-3">
                <h2 class="text-[14px] font-semibold text-black dark:text-white flex items-center gap-2"><i data-lucide="plus-circle" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]" stroke-width="1.5"></i>Tambah Paket Baru</h2>
                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Paket langsung tersedia di checkout setelah diaktifkan.</p>
            </div>

            <div>
                <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Nama Paket <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span></label>
                <input name="name" required maxlength="120" placeholder="Contoh: Core 90 Hari / Token 50 Juta / Storage 2 GB" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>
            <div>
                <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Harga (Rp) <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span></label>
                <input type="number" name="price" required min="0" step="any" placeholder="0" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] tabular-nums text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                <p class="text-[11px] text-[#34C759]/90 dark:text-[#30D158]/90 mt-1">Set <strong>0</strong> untuk promo marketing (misal promo 15 hari trial pro). Tenant aktif instan tanpa perlu bayar &amp; tanpa verifikasi admin.</p>
            </div>
            <div>
                <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Deskripsi</label>
                <textarea name="description" rows="3" maxlength="1000" class="w-full px-3.5 py-2.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] leading-relaxed text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50"></textarea>
            </div>

            @if($type === App\Models\BillingPackage::TYPE_SUBSCRIPTION)
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Durasi (hari) <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span></label>
                    <input type="number" name="duration_days" required min="1" placeholder="30" class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] tabular-nums text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Token AI (opsional)</label>
                    <input type="number" name="token_quantity" min="1" placeholder="10000000" class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] tabular-nums text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
            </div>
            @elseif($type === App\Models\BillingPackage::TYPE_AI_TOKEN)
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Jumlah Token <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span></label>
                    <input type="number" name="token_quantity" required min="1" placeholder="50000000" class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] tabular-nums text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#AF52DE]/50 transition">
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Masa Berlaku (hari) <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span></label>
                    <input type="number" name="token_expiry_days" required min="1" value="30" class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#AF52DE]/50 transition">
                </div>
            </div>
            @else
            <div>
                <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Tambahan Storage (GB) <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span></label>
                <input type="number" name="storage_gb" required min="0.01" step="0.01" placeholder="2" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] tabular-nums text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#30B0C7]/50 transition">
                <p class="text-[11px] text-[#30B0C7]/80 dark:text-[#40C8E0]/80 mt-1">Storage berlaku selamanya &amp; dijumlahkan dengan kapasitas dasar owner.</p>
            </div>
            @endif

            <div>
                <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Nomor Urutan Tampil</label>
                <input type="number" name="sort_order" min="0" max="999" value="0" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>

            <button type="submit" class="w-full h-10 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all inline-flex items-center justify-center gap-2 shadow-[0_1px_2px_rgba(0,122,255,0.25)]"><i data-lucide="save" class="w-4 h-4" stroke-width="1.5"></i>Simpan Paket</button>
        </form>

        <!-- Package Cards List -->
        <div class="space-y-4">
            @forelse($packages as $package)
            <div class="rounded-[14px] p-5 border {{ $package->is_active ? 'border-black/5 dark:border-white/5 bg-white dark:bg-[#1C1C1E]' : 'border-[#FF3B30]/25 bg-[#FF3B30]/5' }} transition">
                <!-- Card Header -->
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-[14px] font-semibold text-black dark:text-white truncate">{{ $package->name }}</h3>
                            @if($package->is_active)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]"><span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>Aktif</span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]"><span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span>Nonaktif</span>
                            @endif
                        </div>
                        @if($package->description)
                            <p class="text-[11px] text-black/45 dark:text-white/45 mt-1 leading-relaxed">{{ $package->description }}</p>
                        @endif
                        <div class="text-[10px] text-black/40 dark:text-white/40 mt-1.5">Kode: {{ $package->code }}</div>
                    </div>

                    <form method="POST" action="{{ route('admin.billing-packages.toggle', $package) }}">
                        @csrf
                        <button type="submit" title="{{ $package->is_active ? 'Nonaktifkan' : 'Aktifkan' }}" class="h-8 px-2.5 rounded-[8px] text-[12px] font-semibold transition-all inline-flex items-center gap-1.5 {{ $package->is_active ? 'bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A] hover:bg-[#FF3B30]/15' : 'bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] hover:bg-[#34C759]/15' }} active:scale-[0.97] active:opacity-80"><i data-lucide="{{ $package->is_active ? 'pause-circle' : 'play-circle' }}" class="w-3.5 h-3.5" stroke-width="1.5"></i>{{ $package->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                    </form>
                </div>

                <!-- Package Metrics -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4">
                    <div class="p-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05]">
                        <div class="text-[10px] text-black/45 dark:text-white/45 font-semibold">Harga</div>
                        <div class="tabular-nums font-bold text-[#34C759] dark:text-[#30D158] text-[14px] mt-1 flex items-center gap-1.5 flex-wrap">
                            @if((float) $package->price <= 0)
                                <span class="text-[#34C759] dark:text-[#30D158]">Rp 0</span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">PROMO GRATIS / TRIAL</span>
                            @else
                                Rp {{ number_format($package->price, 0, ',', '.') }}
                            @endif
                        </div>
                    </div>
                    @if($type === App\Models\BillingPackage::TYPE_SUBSCRIPTION)
                    <div class="p-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05]">
                        <div class="text-[10px] text-black/45 dark:text-white/45 font-semibold">Durasi</div>
                        <div class="tabular-nums font-semibold text-black dark:text-white text-[14px] mt-1">{{ $package->duration_days }} hari</div>
                    </div>
                    <div class="p-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05]">
                        <div class="text-[10px] text-black/45 dark:text-white/45 font-semibold">Token AI Paket</div>
                        <div class="tabular-nums font-semibold text-black dark:text-white text-[14px] mt-1">{{ $package->token_quantity ? number_format($package->token_quantity, 0, ',', '.') : '—' }}</div>
                    </div>
                    @elseif($type === App\Models\BillingPackage::TYPE_AI_TOKEN)
                    <div class="p-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05]">
                        <div class="text-[10px] text-black/45 dark:text-white/45 font-semibold">Jumlah Token</div>
                        <div class="tabular-nums font-semibold text-black dark:text-white text-[14px] mt-1">{{ number_format($package->token_quantity, 0, ',', '.') }}</div>
                    </div>
                    <div class="p-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05]">
                        <div class="text-[10px] text-black/45 dark:text-white/45 font-semibold">Masa Berlaku</div>
                        <div class="tabular-nums font-semibold text-black dark:text-white text-[14px] mt-1">{{ $package->token_expiry_days ?? 30 }} hari</div>
                    </div>
                    @else
                    <div class="p-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05]">
                        <div class="text-[10px] text-black/45 dark:text-white/45 font-semibold">Kapasitas</div>
                        <div class="tabular-nums font-semibold text-black dark:text-white text-[14px] mt-1">{{ number_format(($package->storage_bytes ?? 0) / 1073741824, 2, ',', '.') }} GB permanen</div>
                    </div>
                    <div class="p-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05]">
                        <div class="text-[10px] text-black/45 dark:text-white/45 font-semibold">Kode Paket</div>
                        <div class="tabular-nums font-semibold text-black dark:text-white text-[14px] mt-1">{{ $package->code }}</div>
                    </div>
                    @endif
                </div>

                <!-- Card Actions -->
                <div class="border-t border-black/5 dark:border-white/10 pt-3 mt-4 flex items-center justify-end gap-2">
                    <button type="button" @click="openEdit('{{ $package->id }}')" class="h-8 px-3 rounded-[8px] text-[12px] font-semibold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] active:opacity-80 transition-all inline-flex items-center gap-1.5"><i data-lucide="edit-3" class="w-3.5 h-3.5" stroke-width="1.5"></i>Edit Paket</button>
                </div>
            </div>
            @empty
            <div class="rounded-[14px] p-12 text-center border border-dashed border-black/10 dark:border-white/15">
                <div class="w-14 h-14 mx-auto rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center text-black/25 dark:text-white/25 mb-3"><i data-lucide="package-open" class="w-6 h-6" stroke-width="1.5"></i></div>
                <p class="text-[15px] font-semibold text-black dark:text-white">Belum ada paket pada katalog ini.</p>
                <p class="text-[12px] text-black/50 dark:text-white/50 mt-1">Gunakan form di samping untuk menambahkan paket pertama.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Edit Package Modal (Sheet) -->
    <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]">
        <div @click.away="closeEdit()" class="sheet-material rounded-[20px] p-6 border border-black/5 dark:border-white/10 w-full max-w-lg space-y-4 max-h-[90vh] overflow-y-auto shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                <h4 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="edit-3" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]" stroke-width="1.5"></i>
                    Edit Paket
                </h4>
                <button type="button" @click="closeEdit()" class="p-1.5 rounded-[6px] text-black/40 dark:text-white/40 hover:bg-black/5 dark:hover:bg-white/10"><i data-lucide="x" class="w-5 h-5" stroke-width="1.5"></i></button>
            </div>

            <form :action="'/admin/billing-packages/' + editForm.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="type" value="{{ $type }}">

                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Nama Paket <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span></label>
                    <input type="text" name="name" x-model="editForm.name" required maxlength="120" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Harga (Rp) <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span></label>
                    <input type="number" name="price" x-model="editForm.price" required min="0" step="any" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    <p class="text-[11px] text-[#34C759]/90 dark:text-[#30D158]/90 mt-1">Set <strong>0</strong> untuk promo marketing (trial gratis tanpa perlu transfer/konfirmasi).</p>
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Deskripsi</label>
                    <textarea name="description" x-model="editForm.description" rows="2" maxlength="1000" class="w-full px-3.5 py-2.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] leading-relaxed text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50"></textarea>
                </div>

                @if($type === App\Models\BillingPackage::TYPE_SUBSCRIPTION)
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Durasi (hari) <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span></label>
                        <input type="number" name="duration_days" x-model="editForm.duration_days" required min="1" class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Token AI (opsional)</label>
                        <input type="number" name="token_quantity" x-model="editForm.token_quantity" min="1" class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>
                @elseif($type === App\Models\BillingPackage::TYPE_AI_TOKEN)
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Jumlah Token <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span></label>
                        <input type="number" name="token_quantity" x-model="editForm.token_quantity" required min="1" class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#AF52DE]/50 transition">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Masa Berlaku (hari) <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span></label>
                        <input type="number" name="token_expiry_days" x-model="editForm.token_expiry_days" required min="1" class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#AF52DE]/50 transition">
                    </div>
                </div>
                @else
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Storage (GB) <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span></label>
                    <input type="number" name="storage_gb" x-model="editForm.storage_gb" required min="0.01" step="0.01" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#30B0C7]/50 transition">
                </div>
                @endif

                <div class="pt-3 border-t border-black/5 dark:border-white/10 flex items-center justify-end gap-2">
                    <button type="button" @click="closeEdit()" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] transition">Batal</button>
                    <button type="submit" class="h-9 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all inline-flex items-center gap-2 shadow-[0_1px_2px_rgba(0,122,255,0.25)]"><i data-lucide="save" class="w-4 h-4" stroke-width="1.5"></i>Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection