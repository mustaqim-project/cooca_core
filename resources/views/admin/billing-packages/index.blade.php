@extends('layouts.admin', [
    'title' => 'CMS Paket & Harga — Admin Console',
    'headerTitle' => 'CMS Paket & Harga',
    'headerSubtitle' => 'Kelola katalog paket subscription, top-up token AI, dan storage yang dijual di halaman checkout tenant'
])

@php
    $tabMap = [
        App\Models\BillingPackage::TYPE_SUBSCRIPTION => ['label' => 'Paket & Durasi Subscription', 'icon' => 'layers', 'bg' => 'bg-emerald-500'],
        App\Models\BillingPackage::TYPE_AI_TOKEN => ['label' => 'Paket Top Up Token', 'icon' => 'sparkles', 'bg' => 'bg-amber-400'],
        App\Models\BillingPackage::TYPE_STORAGE => ['label' => 'Paket Top Up Storage', 'icon' => 'hard-drive', 'bg' => 'bg-cyan-400'],
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
            <div class="w-11 h-11 rounded-2xl bg-indigo-500/15 border border-indigo-500/30 flex items-center justify-center text-indigo-400 shrink-0">
                <i data-lucide="layers-3" class="w-5 h-5"></i>
            </div>
            <div>
                <h1 class="text-lg font-black text-white tracking-tight">Katalog Paket Billing Platform</h1>
                <p class="text-xs text-slate-400 mt-0.5">Semua paket aktif langsung tersedia di halaman checkout tenant. Harga dikelola terpusat di sini.</p>
            </div>
        </div>
        <span class="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-[11px] font-bold text-slate-300 flex items-center gap-1.5 w-fit">
            <i data-lucide="package" class="w-3.5 h-3.5 text-cyan-400"></i>
            <span>{{ $packages->count() }} paket pada katalog ini</span>
        </span>
    </div>

    <!-- Catalog Type Tabs -->
    <div class="glass-card rounded-2xl p-2 border border-slate-800 flex flex-wrap items-center gap-1.5">
        @foreach($tabMap as $tabKey => $tabItem)
        <a href="{{ route('admin.billing-packages.index', $tabKey) }}"
           class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $type === $tabKey ? $tabItem['bg'] . ' text-slate-950 shadow' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
            <i data-lucide="{{ $tabItem['icon'] }}" class="w-4 h-4"></i>
            <span>{{ $tabItem['label'] }}</span>
            @if(isset($counts[$tabKey]) && $counts[$tabKey] > 0)
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ $type === $tabKey ? 'bg-slate-950/20 text-slate-950' : 'bg-slate-800 text-slate-400' }}">{{ $counts[$tabKey] }}</span>
            @endif
        </a>
        @endforeach
    </div>
<!-- Informational callout per catalog type -->
    @if($type === App\Models\BillingPackage::TYPE_AI_TOKEN)
    <div class="p-4 rounded-2xl bg-amber-950/30 border border-amber-500/20 text-xs text-amber-200/90 flex items-start gap-2.5">
        <i data-lucide="info" class="w-4 h-4 text-amber-400 mt-0.5 shrink-0"></i>
        <div>
            <span class="font-bold text-amber-200">Catatan:</span> katalog ini menampilkan <em>paket top-up token yang dijual</em> ke tenant. Untuk pemberian token manual (operasional), gunakan menu
            <a href="{{ route('admin.ai-tokens.index') }}" class="text-amber-300 hover:underline font-bold">Monitoring Token AI</a>.
        </div>
    </div>
    @elseif($type === App\Models\BillingPackage::TYPE_STORAGE)
    <div class="p-4 rounded-2xl bg-cyan-950/30 border border-cyan-500/20 text-xs text-cyan-200/90 flex items-start gap-2.5">
        <i data-lucide="info" class="w-4 h-4 text-cyan-400 mt-0.5 shrink-0"></i>
        <div>
            <span class="font-bold text-cyan-200">Catatan:</span> kapasitas storage yang dibeli bersifat permanen dan ditambahkan ke kapasitas dasar owner. Buat paket berbeda untuk nominal GB bervariasi.
        </div>
    </div>
    @endif

    <!-- Default Core pricing (single source of truth for subscription catalog) -->
    @if($type === App\Models\BillingPackage::TYPE_SUBSCRIPTION)
    <div class="glass-card rounded-2xl border border-indigo-500/30 p-5">
        <form method="POST" action="{{ route('admin.settings.billing') }}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 items-end">
            @csrf
            <div class="md:col-span-2 xl:col-span-4">
                <p class="text-xs font-bold text-indigo-300 flex items-center gap-2">
                    <i data-lucide="settings-2" class="w-4 h-4"></i>
                    Harga Bawaan & Kuota Cooca UMKM
                </p>
                <p class="text-[11px] text-slate-500 mt-0.5">Nilai default yang dipakai saat tenant memilih Core tanpa paket katalog. Dikelola di satu tempat ini.</p>
            </div>
            <div>
                <label class="block font-semibold text-slate-300 mb-1.5 text-xs">Bulanan (Rp)</label>
                <input type="number" name="subscription_price_monthly" value="{{ $subscriptionPriceMonthly ?? 129000 }}" min="0" step="1000"
                       class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl text-white font-mono text-xs">
            </div>
            <div>
                <label class="block font-semibold text-slate-300 mb-1.5 text-xs">Tahunan (Rp)</label>
                <input type="number" name="subscription_price_annual" value="{{ $subscriptionPriceAnnual ?? 1290000 }}" min="0" step="1000"
                       class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl text-white font-mono text-xs">
            </div>
            <div>
                <label class="block font-semibold text-slate-300 mb-1.5 text-xs">Kuota Token AI / Bulan</label>
                <input type="number" name="subscription_ai_tokens_monthly" value="{{ $subscriptionAiTokensMonthly ?? 10000000 }}" min="0" step="100000"
                       class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl text-white font-mono text-xs">
            </div>
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="block font-semibold text-slate-300 mb-1.5 text-xs">Badge Promo Tahunan</label>
                    <input type="text" name="subscription_annual_discount_badge" value="{{ $subscriptionAnnualDiscountBadge ?? 'Hemat 2 Bulan' }}" maxlength="64"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl text-white text-xs">
                </div>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition inline-flex items-center gap-1.5 shrink-0">
                    <i data-lucide="save" class="w-3.5 h-3.5"></i>
                    <span class="hidden sm:inline">Simpan Default</span>
                </button>
            </div>
        </form>
    </div>
    @endif
<!-- Default token / storage top-up pricing (non-catalog) -->
    @if($type === App\Models\BillingPackage::TYPE_AI_TOKEN)
    <div class="glass-card rounded-2xl border border-amber-500/30 p-5">
        <form method="POST" action="{{ route('admin.settings.billing') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            @csrf
            <div class="md:col-span-2 lg:col-span-4">
                <p class="text-xs font-bold text-amber-300 flex items-center gap-2">
                    <i data-lucide="settings-2" class="w-4 h-4"></i>
                    Default Top-Up Token Instant (Non-Katalog)
                </p>
                <p class="text-[11px] text-slate-500 mt-0.5">Dipakai saat tenant melakukan top-up tanpa memilih paket dari katalog. Nilai katalog yang aktif akan menimpa nilai default ini.</p>
            </div>
            <div>
                <label class="block font-semibold text-slate-300 mb-1.5 text-xs">Harga per Top Up (Rp)</label>
                <input type="number" name="ai_token_topup_price" value="{{ $aiTokenTopupPrice ?? 50000 }}" min="0" step="1000"
                       class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl text-white font-mono text-xs">
            </div>
            <div>
                <label class="block font-semibold text-slate-300 mb-1.5 text-xs">Jumlah Token per Top Up</label>
                <input type="number" name="ai_token_topup_amount" value="{{ $aiTokenTopupAmount ?? 1000000 }}" min="1" step="10000"
                       class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl text-white font-mono text-xs">
            </div>
            <div class="flex items-end justify-end md:col-span-2 lg:col-span-2">
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold transition inline-flex items-center gap-1.5">
                    <i data-lucide="save" class="w-3.5 h-3.5"></i>
                    Simpan Default Top-Up
                </button>
            </div>
        </form>
    </div>
    @elseif($type === App\Models\BillingPackage::TYPE_STORAGE)
    <div class="glass-card rounded-2xl border border-cyan-500/30 p-5">
        <form method="POST" action="{{ route('admin.settings.billing') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            @csrf
            <div class="md:col-span-2 lg:col-span-4">
                <p class="text-xs font-bold text-cyan-300 flex items-center gap-2">
                    <i data-lucide="settings-2" class="w-4 h-4"></i>
                    Default Top-Up Storage & Kapasitas Dasar Owner
                </p>
                <p class="text-[11px] text-slate-500 mt-0.5">Kapasitas dasar owner berlaku untuk seluruh bisnis miliknya; nilai default top-up dipakai bila tenant tidak memilih paket katalog.</p>
            </div>
            <div>
                <label class="block font-semibold text-slate-300 mb-1.5 text-xs">Harga per Top Up (Rp)</label>
                <input type="number" name="storage_topup_price" value="{{ $storageTopupPrice ?? 50000 }}" min="0" step="1000"
                       class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-cyan-500 rounded-xl text-white font-mono text-xs">
            </div>
            <div>
                <label class="block font-semibold text-slate-300 mb-1.5 text-xs">GB per Top Up default</label>
                <input type="number" name="storage_topup_gb" value="{{ $storageTopupGb ?? 1 }}" min="1"
                       class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-cyan-500 rounded-xl text-white font-mono text-xs">
            </div>
            <div>
                <label class="block font-semibold text-slate-300 mb-1.5 text-xs">Kapasitas Dasar Owner (GB)</label>
                <input type="number" name="owner_storage_limit_gb" value="{{ $ownerStorageLimitGb ?? 3 }}" min="1"
                       class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-cyan-500 rounded-xl text-white font-mono text-xs">
            </div>
            <div class="flex items-end justify-end">
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-xs font-bold transition inline-flex items-center gap-1.5">
                    <i data-lucide="save" class="w-3.5 h-3.5"></i>
                    Simpan Default Top-Up
                </button>
            </div>
        </form>
    </div>
    @endif
<!-- Main 2-Column Grid: Create Form + Package List -->
    <div class="grid grid-cols-1 lg:grid-cols-[340px_1fr] gap-5 items-start">

        <!-- Create Package Form -->
        <form method="POST" action="{{ route('admin.billing-packages.store') }}"
              class="glass-card rounded-2xl p-5 border border-slate-800 space-y-4 lg:sticky lg:top-20">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">

            <div class="border-b border-slate-800 pb-3">
                <h2 class="font-bold text-white text-sm flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-emerald-400"></i>
                    Tambah Paket Baru
                </h2>
                <p class="text-[11px] text-slate-500 mt-1">Paket langsung tersedia di checkout setelah diaktifkan.</p>
            </div>

            <div>
                <label class="block font-semibold text-slate-300 mb-1.5 text-xs">Nama Paket <span class="text-rose-400">*</span></label>
                <input name="name" required maxlength="120" placeholder="Contoh: Core 90 Hari / Token 50 Juta / Storage 2 GB"
                       class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl text-white text-xs">
            </div>

            <div>
                <label class="block font-semibold text-slate-300 mb-1.5 text-xs">Harga (Rp) <span class="text-rose-400">*</span></label>
                <input type="number" name="price" required min="0" step="any" placeholder="0"
                       class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl text-white font-mono text-xs">
                <p class="text-[11px] text-emerald-400/90 mt-1">
                    Set <strong>0</strong> untuk promo marketing (misal promo 15 hari trial pro). Tenant aktif instan tanpa perlu bayar & tanpa verifikasi admin.
                </p>
            </div>

            <div>
                <label class="block font-semibold text-slate-300 mb-1.5 text-xs">Deskripsi</label>
                <textarea name="description" rows="3" maxlength="1000" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl text-white text-xs leading-relaxed"></textarea>
            </div>

            @if($type === App\Models\BillingPackage::TYPE_SUBSCRIPTION)
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5 text-xs">Durasi (hari) <span class="text-rose-400">*</span></label>
                    <input type="number" name="duration_days" required min="1" placeholder="30" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl text-white font-mono text-xs">
                </div>
                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5 text-xs">Token AI (opsional)</label>
                    <input type="number" name="token_quantity" min="1" placeholder="10000000" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl text-white font-mono text-xs">
                </div>
            </div>
            @elseif($type === App\Models\BillingPackage::TYPE_AI_TOKEN)
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5 text-xs">Jumlah Token <span class="text-rose-400">*</span></label>
                    <input type="number" name="token_quantity" required min="1" placeholder="50000000" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl text-white font-mono text-xs">
                </div>
                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5 text-xs">Masa Berlaku (hari) <span class="text-rose-400">*</span></label>
                    <input type="number" name="token_expiry_days" required min="1" value="30" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl text-white font-mono text-xs">
                </div>
            </div>
            @else
            <div>
                <label class="block font-semibold text-slate-300 mb-1.5 text-xs">Tambahan Storage (GB) <span class="text-rose-400">*</span></label>
                <input type="number" name="storage_gb" required min="0.01" step="0.01" placeholder="2"
                       class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl text-white font-mono text-xs">
                <p class="text-[11px] text-cyan-300/80 mt-1">Storage berlaku selamanya & dijumlahkan dengan kapasitas dasar owner.</p>
            </div>
            @endif

            <div>
                <label class="block font-semibold text-slate-300 mb-1.5 text-xs">Nomor Urutan Tampil</label>
                <input type="number" name="sort_order" min="0" max="999" value="0" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl text-white font-mono text-xs">
            </div>

            <button type="submit" class="w-full py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 text-xs font-bold shadow-lg shadow-emerald-500/20 transition inline-flex items-center justify-center gap-2">
                <i data-lucide="save" class="w-4 h-4"></i>
                Simpan Paket
            </button>
        </form>
<!-- Package Cards List -->
        <div class="space-y-4">
            @forelse($packages as $package)
            <div class="glass-card rounded-2xl p-5 border {{ $package->is_active ? 'border-slate-800' : 'border-rose-500/30 bg-slate-950/40' }} transition">
                <!-- Card Header -->
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-bold text-white text-sm truncate">{{ $package->name }}</h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $package->is_active ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30' : 'bg-rose-500/15 text-rose-300 border border-rose-500/30' }}">
                                {{ $package->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                        @if($package->description)
                            <p class="text-[11px] text-slate-400 mt-1 leading-relaxed">{{ $package->description }}</p>
                        @endif
                        <div class="text-[10px] font-mono text-slate-500 mt-1.5">Kode: {{ $package->code }}</div>
                    </div>

                    <form method="POST" action="{{ route('admin.billing-packages.toggle', $package) }}">
                        @csrf
                        <button type="submit" title="{{ $package->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold border transition inline-flex items-center gap-1.5 {{ $package->is_active ? 'bg-rose-500/10 border-rose-500/30 text-rose-300 hover:bg-rose-500/20' : 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300 hover:bg-emerald-500/20' }}">
                            <i data-lucide="{{ $package->is_active ? 'pause-circle' : 'play-circle' }}" class="w-3.5 h-3.5"></i>
                            {{ $package->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>
                </div>

                <!-- Package Metrics -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4">
                    <div class="p-3 rounded-xl bg-slate-950/70 border border-slate-800">
                        <div class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Harga</div>
                        <div class="font-mono font-black text-emerald-400 text-sm mt-1 flex items-center gap-1.5 flex-wrap">
                            @if((float) $package->price <= 0)
                                <span class="text-emerald-400">Rp 0</span>
                                <span class="text-[10px] px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-sans font-bold border border-emerald-500/30">PROMO GRATIS / TRIAL</span>
                            @else
                                Rp {{ number_format($package->price, 0, ',', '.') }}
                            @endif
                        </div>
                    </div>

                    @if($type === App\Models\BillingPackage::TYPE_SUBSCRIPTION)
                    <div class="p-3 rounded-xl bg-slate-950/70 border border-slate-800">
                        <div class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Durasi</div>
                        <div class="font-mono font-bold text-white text-sm mt-1">{{ $package->duration_days }} hari</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-950/70 border border-slate-800">
                        <div class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Token AI Paket</div>
                        <div class="font-mono font-bold text-white text-sm mt-1">{{ $package->token_quantity ? number_format($package->token_quantity, 0, ',', '.') : '—' }}</div>
                    </div>
                    @elseif($type === App\Models\BillingPackage::TYPE_AI_TOKEN)
                    <div class="p-3 rounded-xl bg-slate-950/70 border border-slate-800">
                        <div class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Jumlah Token</div>
                        <div class="font-mono font-bold text-white text-sm mt-1">{{ number_format($package->token_quantity, 0, ',', '.') }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-950/70 border border-slate-800">
                        <div class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Masa Berlaku</div>
                        <div class="font-mono font-bold text-white text-sm mt-1">{{ $package->token_expiry_days ?? 30 }} hari</div>
                    </div>
                    @else
                    <div class="p-3 rounded-xl bg-slate-950/70 border border-slate-800">
                        <div class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Kapasitas</div>
                        <div class="font-mono font-bold text-white text-sm mt-1">{{ number_format(($package->storage_bytes ?? 0) / 1073741824, 2, ',', '.') }} GB permanen</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-950/70 border border-slate-800">
                        <div class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Kode Paket</div>
                        <div class="font-mono font-bold text-white text-sm mt-1">{{ $package->code }}</div>
                    </div>
                    @endif
                </div>
<!-- Card Actions -->
                <div class="border-t border-slate-800 pt-3 mt-4 flex items-center justify-end gap-2">
                    <button type="button"
                            @click="openEdit('{{ $package->id }}')"
                            class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-bold transition inline-flex items-center gap-1.5">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        Edit Paket
                    </button>
                </div>
            </div>
            @empty
            <div class="glass-card rounded-2xl p-12 text-center border border-dashed border-slate-800">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-600 mb-3">
                    <i data-lucide="package-open" class="w-6 h-6"></i>
                </div>
                <p class="text-sm font-bold text-slate-300">Belum ada paket pada katalog ini.</p>
                <p class="text-xs text-slate-500 mt-1">Gunakan form di samping untuk menambahkan paket pertama.</p>
            </div>
            @endforelse
        </div>
    </div>
<!-- Edit Package Modal -->
    <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div @click.away="closeEdit()" class="glass-card rounded-3xl p-6 border border-slate-700 w-full max-w-lg space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h4 class="font-bold text-white text-sm flex items-center gap-2">
                    <i data-lucide="edit-3" class="w-4 h-4 text-indigo-400"></i>
                    Edit Paket
                </h4>
                <button type="button" @click="closeEdit()" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'/admin/billing-packages/' + editForm.id" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <input type="hidden" name="type" value="{{ $type }}">

                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5">Nama Paket <span class="text-rose-400">*</span></label>
                    <input type="text" name="name" x-model="editForm.name" required maxlength="120"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white text-xs">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5">Harga (Rp) <span class="text-rose-400">*</span></label>
                    <input type="number" name="price" x-model="editForm.price" required min="0" step="any"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono text-xs">
                    <p class="text-[11px] text-emerald-400/90 mt-1">
                        Set <strong>0</strong> untuk promo marketing (trial gratis tanpa perlu transfer/konfirmasi).
                    </p>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5">Deskripsi</label>
                    <textarea name="description" x-model="editForm.description" rows="2" maxlength="1000"
                              class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white text-xs leading-relaxed"></textarea>
                </div>

                @if($type === App\Models\BillingPackage::TYPE_SUBSCRIPTION)
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1.5">Durasi (hari) <span class="text-rose-400">*</span></label>
                        <input type="number" name="duration_days" x-model="editForm.duration_days" required min="1"
                               class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1.5">Token AI (opsional)</label>
                        <input type="number" name="token_quantity" x-model="editForm.token_quantity" min="1"
                               class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono text-xs">
                    </div>
                </div>
                @elseif($type === App\Models\BillingPackage::TYPE_AI_TOKEN)
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1.5">Jumlah Token <span class="text-rose-400">*</span></label>
                        <input type="number" name="token_quantity" x-model="editForm.token_quantity" required min="1"
                               class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1.5">Masa Berlaku (hari) <span class="text-rose-400">*</span></label>
                        <input type="number" name="token_expiry_days" x-model="editForm.token_expiry_days" required min="1"
                               class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono text-xs">
                    </div>
                </div>
                @else
                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5">Storage (GB) <span class="text-rose-400">*</span></label>
                    <input type="number" name="storage_gb" x-model="editForm.storage_gb" required min="0.01" step="0.01"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono text-xs">
                </div>
                @endif

                <div class="pt-3 border-t border-slate-800 flex items-center justify-end gap-2">
                    <button type="button" @click="closeEdit()"
                            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 text-xs font-bold shadow-lg shadow-emerald-500/20 transition inline-flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
