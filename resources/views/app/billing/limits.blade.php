@extends('layouts.app', ['title' => 'Paket Langganan & Kuota Penggunaan'])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-500/20 text-purple-400 border border-purple-500/30">
                    SAAS SUBSCRIPTION & ENTITLEMENT
                </span>
                <span class="text-xs text-slate-400 font-mono">cooca.id Billing</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight mt-1">Paket Langganan & Kuota Bisnis</h1>
            <p class="text-xs text-slate-400 mt-0.5">Pantau kapasitas sumber daya bisnis Anda dan nikmati fitur tanpa batas dengan Cooca Core.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('billing.history') }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 text-xs font-bold transition flex items-center gap-1.5">
                <i data-lucide="receipt" class="w-3.5 h-3.5 text-emerald-400"></i>
                <span>Riwayat Tagihan</span>
            </a>
            @if($usage['is_core'])
                <span class="px-3.5 py-1.5 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-black flex items-center gap-1.5">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    <span>{{ $usage['plan_label'] }} (Aktif)</span>
                </span>
            @else
                <span class="px-3.5 py-1.5 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 text-xs font-bold flex items-center gap-1.5">
                    <i data-lucide="sparkles" class="w-4 h-4 text-amber-400"></i>
                    <span>Paket Free (Maksimal 50 Produk)</span>
                </span>
            @endif
        </div>
    </div>

    <!-- Alert / No Data Punishment Banner -->
    <div class="glass-card rounded-2xl p-4 border border-emerald-500/30 bg-emerald-950/20 flex items-start gap-3">
        <div class="p-2 rounded-xl bg-emerald-500/20 text-emerald-400 shrink-0">
            <i data-lucide="heart-handshake" class="w-5 h-5"></i>
        </div>
        <div class="text-xs space-y-1">
            <h4 class="font-bold text-white">Komitmen Privasi: No Data Punishment</h4>
            <p class="text-slate-300">Data bisnis Anda adalah hak milik Anda sepenuhnya. Jika langganan Anda berakhir, Cooca Core <strong class="text-emerald-400">tidak akan pernah menghapus data Anda</strong>. Seluruh data historis tetap dapat dilihat, dicari, dan diekspor kapan saja.</p>
        </div>
    </div>

    <!-- Resource Consumption Bento Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Produk -->
        <div class="glass-card rounded-2xl p-5 border border-slate-800 space-y-3">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-xs font-bold uppercase tracking-wider">Katalog Produk</span>
                <i data-lucide="box" class="w-4 h-4 text-slate-500"></i>
            </div>
            <div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-black font-mono text-white">{{ $usage['products']['used'] }}</span>
                    <span class="text-xs text-slate-400 font-mono">/ {{ $usage['products']['limit'] ? $usage['products']['limit'] . ' item' : '∞ Unlimited' }}</span>
                </div>
            </div>
            <!-- Progress Bar -->
            <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                <div class="bg-emerald-500 h-full rounded-full transition-all" style="width: {{ $usage['products']['percent'] }}%"></div>
            </div>
            <p class="text-[11px] text-slate-500">
                {{ $usage['products']['is_reached'] ? 'Batas kuota produk tercapai!' : 'Tersedia untuk penjualan kasir & invoice.' }}
            </p>
        </div>

        <!-- 2. Resep BOM -->
        <div class="glass-card rounded-2xl p-5 border border-slate-800 space-y-3">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-xs font-bold uppercase tracking-wider">Resep HPP (BOM)</span>
                <i data-lucide="chef-hat" class="w-4 h-4 text-slate-500"></i>
            </div>
            <div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-black font-mono text-white">{{ $usage['recipes']['used'] }}</span>
                    <span class="text-xs text-slate-400 font-mono">/ {{ $usage['recipes']['limit'] ? $usage['recipes']['limit'] . ' resep' : '∞ Unlimited' }}</span>
                </div>
            </div>
            <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                <div class="bg-cyan-500 h-full rounded-full transition-all" style="width: {{ $usage['recipes']['percent'] }}%"></div>
            </div>
            <p class="text-[11px] text-slate-500">
                {{ $usage['recipes']['is_reached'] ? 'Batas kuota resep tercapai!' : 'Kalkulasi HPP biaya bahan modal.' }}
            </p>
        </div>

        <!-- 3. Invoices per Bulan -->
        <div class="glass-card rounded-2xl p-5 border border-slate-800 space-y-3">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-xs font-bold uppercase tracking-wider">Faktur Bulan Ini</span>
                <i data-lucide="receipt" class="w-4 h-4 text-slate-500"></i>
            </div>
            <div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-black font-mono text-white">{{ $usage['invoices_this_month']['used'] }}</span>
                    <span class="text-xs text-slate-400 font-mono">/ {{ $usage['invoices_this_month']['limit'] ? $usage['invoices_this_month']['limit'] . ' inv' : '∞ Unlimited' }}</span>
                </div>
            </div>
            <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                <div class="bg-purple-500 h-full rounded-full transition-all" style="width: {{ $usage['invoices_this_month']['percent'] }}%"></div>
            </div>
            <p class="text-[11px] text-slate-500">
                {{ $usage['invoices_this_month']['is_reached'] ? 'Batas kuota faktur tercapai!' : 'Reset otomatis setiap awal bulan.' }}
            </p>
        </div>

        <!-- 4. AI Tokens -->
        <div class="glass-card rounded-2xl p-5 border border-slate-800 space-y-3">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-xs font-bold uppercase tracking-wider">Token AI Bulanan</span>
                <i data-lucide="bot" class="w-4 h-4 text-slate-500"></i>
            </div>
            <div>
                <div class="flex items-baseline gap-1.5">
                    @if($usage['is_core'])
                    <span class="text-xl font-black font-mono text-white">{{ number_format($usage['ai_tokens']['remaining'], 0, ',', '.') }}</span>
                    <span class="text-[10px] text-slate-400">sisa</span>
                    @else
                    <span class="text-base font-bold text-slate-500">Khusus Core</span>
                    @endif
                </div>
            </div>
            <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                <div class="bg-amber-500 h-full rounded-full transition-all" style="width: {{ $usage['ai_tokens']['percent'] }}%"></div>
            </div>
            <p class="text-[11px] text-slate-500">
                10 Juta token/bulan untuk peramalan & asisten tanya jawab.
            </p>
        </div>
    </div>

    <!-- Pricing Upgrade Card -->
    @if(!$usage['is_core'])
    <div class="glass-card rounded-3xl p-8 border border-purple-500/30 bg-gradient-to-br from-purple-950/30 via-slate-900 to-slate-950 space-y-6">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <div class="space-y-2">
                <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-purple-500/20 text-purple-300 border border-purple-500/30">
                    TINGKATKAN KE COOCA CORE
                </span>
                <h3 class="text-2xl font-black text-white">Buka Seluruh Potensi Bisnis Anda Tanpa Batas</h3>
                <p class="text-xs text-slate-400 max-w-xl">
                    Dapatkan katalog produk unlimited, multi-gudang, transaksi tanpa batas, integrasi WhatsApp, dan 10 Juta Token AI setiap bulan hanya seharga Rp129.000/bulan.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <a href="{{ route('billing.checkout', ['cycle' => 'monthly']) }}"
                   class="px-5 py-3 rounded-2xl bg-gradient-to-r from-purple-500 to-indigo-500 hover:from-purple-400 hover:to-indigo-400 text-white text-xs font-black shadow-xl shadow-purple-500/20 transition flex items-center gap-2">
                    <i data-lucide="zap" class="w-4 h-4"></i>
                    <span>Pilih Bulanan (Rp 129.000/bln)</span>
                </a>

                <a href="{{ route('billing.checkout', ['cycle' => 'annual']) }}"
                   class="px-5 py-3 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-black shadow-xl shadow-emerald-500/20 transition flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    <span>Pilih Tahunan Hemat 2 Bulan (Rp 1.290.000/thn)</span>
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
