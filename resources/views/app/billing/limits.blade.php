@extends('layouts.app', [
    'title' => 'Paket Langganan & Kuota Penggunaan — Cooca UMKM',
    'headerTitle' => 'Paket Langganan & Kuota Bisnis',
    'headerSubtitle' => 'Pantau kapasitas sumber daya, kelola kuota transaksi, dan nikmati fitur tanpa batas'
])

@section('content')
<div class="max-w-6xl mx-auto space-y-6 sm:space-y-8">

    <!-- Standard Breadcrumb & Executive Page Header -->
    <nav class="flex flex-wrap items-center justify-between gap-4 pb-2 border-b border-slate-800/80" aria-label="Breadcrumb">
        <ol class="flex items-center gap-2 text-xs text-slate-400">
            <li>
                <a href="{{ route('dashboard') }}" class="hover:text-white transition-colors focus-visible:ring-2 focus-visible:ring-emerald-500 rounded px-1">
                    Dashboard
                </a>
            </li>
            <li class="text-slate-600" aria-hidden="true">/</li>
            <li>
                <span class="text-slate-200 font-semibold" aria-current="page">Paket &amp; Kuota</span>
            </li>
            <li class="hidden sm:inline text-slate-600" aria-hidden="true">•</li>
            <li class="hidden sm:inline">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    SaaS Entitlement
                </span>
            </li>
        </ol>

        <div class="flex items-center gap-2.5 shrink-0">
            <a href="{{ route('billing.history') }}"
                class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white border border-slate-800 hover:border-slate-700 text-xs font-bold transition-all shadow-sm flex items-center gap-2 focus-visible:ring-2 focus-visible:ring-emerald-500">
                <i data-lucide="receipt" class="w-4 h-4 text-emerald-400" aria-hidden="true"></i>
                <span>Riwayat Tagihan</span>
            </a>
            @if ($usage['is_core'])
                <a href="{{ route('billing.checkout') }}"
                    class="px-3.5 py-2 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 hover:border-emerald-500/50 text-xs font-black flex items-center gap-2 transition-all shadow-sm focus-visible:ring-2 focus-visible:ring-emerald-500">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 shadow-sm shadow-emerald-400 animate-pulse" aria-hidden="true"></span>
                    <span>{{ $usage['plan_label'] }}</span>
                </a>
            @else
                <a href="{{ route('billing.checkout') }}"
                    class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-slate-950 text-xs font-black shadow-lg shadow-emerald-500/20 transition-all hover:scale-105 active:scale-95 flex items-center gap-1.5 focus-visible:ring-2 focus-visible:ring-emerald-500">
                    <i data-lucide="sparkles" class="w-4 h-4" aria-hidden="true"></i>
                    <span>Ikut Patungan</span>
                </a>
            @endif
        </div>
    </nav>

    <!-- Page Title & Purpose Overview -->
    <div class="space-y-1">
        <div class="flex items-center gap-2">
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Paket Langganan & Kuota Bisnis</h1>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider {{ $usage['is_core'] ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-slate-800 text-slate-400 border border-slate-700' }}">
                {{ $usage['is_core'] ? 'Enterprise Pro' : 'Free Solo' }}
            </span>
        </div>
        <p class="text-xs sm:text-sm text-slate-400 max-w-3xl leading-relaxed">
            Pantau pemakaian sumber daya bisnis Anda secara real-time. Nikmati fitur komersial terintegrasi tanpa batas dengan lisensi patungan Cooca UMKM.
        </p>
    </div>

    <!-- Active Plan Showcase or Upgrade Conversion Banner -->
    @if ($usage['is_core'])
        <!-- Active Core Plan Executive Card -->
        <section aria-labelledby="active-plan-heading" class="rounded-3xl p-6 sm:p-8 border border-emerald-500/40 bg-gradient-to-br from-emerald-950/40 via-slate-900 to-slate-950 shadow-2xl relative overflow-hidden backdrop-blur-xl">
            <div class="absolute -right-16 -top-16 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>
            
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 relative z-10">
                <div class="space-y-3 max-w-2xl">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 flex items-center gap-1.5 shadow-sm">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-400" aria-hidden="true"></i>
                            <span>STATUS LANGGANAN AKTIF</span>
                        </span>
                        @if(!empty($usage['ends_at']))
                            <span class="text-xs text-slate-400 font-mono">
                                Berlaku s/d <strong class="text-white font-semibold">{{ $usage['ends_at'] }}</strong>
                            </span>
                        @endif
                    </div>

                    <h2 id="active-plan-heading" class="text-xl sm:text-2xl font-black text-white tracking-tight">
                        Bisnis Anda Berjalan di Paket <span class="text-emerald-400">{{ $usage['plan_label'] }}</span>
                    </h2>
                    
                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                        Seluruh limit transaksi POS, faktur penjualan, katalog produk, resep BOM, dan multi-gudang telah terbuka penuh tanpa batas (*Unlimited*).
                    </p>

                    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 pt-1 text-xs text-slate-300">
                        <span class="flex items-center gap-1.5 text-emerald-300 font-medium">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400 shrink-0" aria-hidden="true"></i>
                            <span>Multi-Gudang &amp; Cabang</span>
                        </span>
                        <span class="flex items-center gap-1.5 text-emerald-300 font-medium">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400 shrink-0" aria-hidden="true"></i>
                            <span>Export / Import Excel Lengkap</span>
                        </span>
                        <span class="flex items-center gap-1.5 text-emerald-300 font-medium">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400 shrink-0" aria-hidden="true"></i>
                            <span>Transaksi Kasir Unlimited</span>
                        </span>
                        <span class="flex items-center gap-1.5 text-emerald-300 font-medium">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400 shrink-0" aria-hidden="true"></i>
                            <span>Bot WhatsApp Struk Kasir</span>
                        </span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto shrink-0">
                    <a href="{{ route('billing.checkout') }}"
                        class="px-5 py-3 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 text-xs font-black shadow-xl shadow-emerald-500/20 transition-all flex items-center justify-center gap-2 hover:scale-105 active:scale-95 focus-visible:ring-2 focus-visible:ring-emerald-500">
                        <i data-lucide="refresh-cw" class="w-4 h-4" aria-hidden="true"></i>
                        <span>Perpanjang Masa Aktif</span>
                    </a>
                    <a href="{{ route('billing.history') }}"
                        class="px-4 py-3 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition-all flex items-center justify-center gap-2 border border-slate-700 focus-visible:ring-2 focus-visible:ring-emerald-500">
                        <i data-lucide="receipt" class="w-4 h-4 text-slate-400" aria-hidden="true"></i>
                        <span>Lihat Invoice</span>
                    </a>
                </div>
            </div>
        </section>
    @else
        <!-- Free Plan Upgrade Hero Banner -->
        <section aria-labelledby="upgrade-plan-heading" class="rounded-3xl p-6 sm:p-8 border border-emerald-500/40 bg-gradient-to-br from-emerald-950/40 via-slate-900 to-slate-950 shadow-2xl relative overflow-hidden backdrop-blur-xl">
            <div class="absolute -right-20 -top-20 w-80 h-80 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>
            
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 relative z-10">
                <div class="space-y-3 max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 shadow-sm">
                        <i data-lucide="zap" class="w-3.5 h-3.5 text-amber-400" aria-hidden="true"></i>
                        <span>PROGRAM PATUNGAN SAAS COOCA UMKM</span>
                    </div>

                    <h2 id="upgrade-plan-heading" class="text-2xl sm:text-3xl font-black text-white tracking-tight leading-tight">
                        Buka Seluruh Potensi Bisnis Anda <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-teal-300">Tanpa Batas</span>
                    </h2>

                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                        Tingkatkan dari Paket Free ke Cooca UMKM mulai dari <strong class="text-white font-mono">Rp {{ number_format($monthlyPrice, 0, ',', '.') }}/bulan</strong>. Dapatkan produk &amp; resep unlimited, multi-gudang, transaksi tanpa batas, serta integrasi ekspor Excel lengkap.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto shrink-0">
                    <a href="{{ route('billing.checkout', ['cycle' => 'monthly']) }}"
                        class="px-5 py-3.5 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-slate-950 text-xs font-black shadow-xl shadow-emerald-500/20 transition-all flex items-center justify-center gap-2 hover:scale-105 active:scale-95 focus-visible:ring-2 focus-visible:ring-emerald-500">
                        <i data-lucide="zap" class="w-4 h-4" aria-hidden="true"></i>
                        <span>Bulanan (Rp {{ number_format($monthlyPrice, 0, ',', '.') }}/bln)</span>
                    </a>

                    <a href="{{ route('billing.checkout', ['cycle' => 'annual']) }}"
                        class="px-5 py-3.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-teal-300 border border-teal-500/40 hover:border-teal-400 text-xs font-black shadow-xl transition-all flex items-center justify-center gap-2 hover:scale-105 active:scale-95 focus-visible:ring-2 focus-visible:ring-emerald-500">
                        <i data-lucide="sparkles" class="w-4 h-4 text-amber-300" aria-hidden="true"></i>
                        <span>Tahunan ({{ $annualDiscountBadge }} · Rp {{ number_format($annualPrice, 0, ',', '.') }}/thn)</span>
                    </a>
                </div>
            </div>
        </section>
    @endif

    <!-- SECTION 1: Special Infrastructure Hub (Storage Cloud & AI Engine) -->
    <section aria-labelledby="infra-hub-heading" class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 border-b border-slate-800 pb-3">
            <div class="flex items-center gap-2">
                <i data-lucide="cpu" class="w-4 h-4 text-cyan-400" aria-hidden="true"></i>
                <h2 id="infra-hub-heading" class="text-sm font-extrabold uppercase tracking-wider text-slate-200">
                    Infrastruktur Cloud &amp; Intelegensi AI
                </h2>
            </div>
            <span class="text-xs text-slate-500 font-mono">Daya komputasi &amp; penyimpanan dokumen terproteksi</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- 1. Cloud Storage Owner -->
            <div class="rounded-3xl p-6 border border-cyan-500/30 bg-gradient-to-br from-cyan-950/20 via-slate-900/90 to-slate-950 flex flex-col justify-between gap-5 shadow-xl backdrop-blur-md relative overflow-hidden">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-cyan-300 text-xs font-black uppercase tracking-wider">
                            <div class="p-2 rounded-xl bg-cyan-500/15 text-cyan-400" aria-hidden="true">
                                <i data-lucide="hard-drive" class="w-4 h-4"></i>
                            </div>
                            <span>Penyimpanan Cloud Bisnis</span>
                        </div>
                        <span class="text-[10px] font-black px-2.5 py-1 rounded-full bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">
                            Aset Terproteksi
                        </span>
                    </div>

                    <div class="flex items-baseline gap-2 pt-1">
                        <span class="text-3xl font-black font-mono text-white">
                            {{ number_format($usage['storage']['used_mb'] ?? 0, 1, ',', '.') }} <span class="text-lg font-bold text-slate-400">MB</span>
                        </span>
                        <span class="text-xs text-slate-400 font-mono">
                            / {{ number_format($usage['storage']['limit_gb'] ?? 1, 1, ',', '.') }} GB Kapasitas
                        </span>
                    </div>

                    <!-- Storage Progress Bar with ARIA -->
                    @php $storagePercent = min(100, max(0, (int)($usage['storage']['percentage'] ?? 0))); @endphp
                    <div class="space-y-1.5" role="progressbar" aria-valuenow="{{ $storagePercent }}" aria-valuemin="0" aria-valuemax="100" aria-label="Persentase Pemakaian Storage">
                        <div class="w-full bg-slate-950 rounded-full h-2.5 overflow-hidden p-0.5 border border-slate-800">
                            <div class="h-full rounded-full transition-all duration-500 {{ $storagePercent >= 90 ? 'bg-rose-500 shadow-sm shadow-rose-500/50' : ($storagePercent >= 75 ? 'bg-amber-400 shadow-sm shadow-amber-400/50' : 'bg-gradient-to-r from-cyan-500 to-teal-400') }}"
                                style="width: {{ $storagePercent }}%"></div>
                        </div>
                        <div class="flex justify-between text-[11px] text-slate-400 font-mono">
                            <span>Terpakai: <strong class="text-slate-200">{{ $storagePercent }}%</strong></span>
                            <span>Sisa: <strong class="text-slate-200">{{ max(0, 100 - $storagePercent) }}%</strong></span>
                        </div>
                    </div>

                    <p class="text-xs text-slate-400 leading-relaxed">
                        Kapasitas penyimpanan aman untuk foto katalog produk, dokumen faktur digital, dan lampiran struk transfer bank.
                    </p>
                </div>

                <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between">
                    <span class="text-xs text-slate-400 font-medium">Tambah kuota permanen</span>
                    <a href="{{ route('billing.checkout', ['type' => 'storage']) }}"
                        class="px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-xs font-black flex items-center gap-1.5 shadow-lg shadow-cyan-500/20 transition-all hover:scale-105 active:scale-95 focus-visible:ring-2 focus-visible:ring-cyan-400">
                        <i data-lucide="plus" class="w-3.5 h-3.5" aria-hidden="true"></i>
                        <span>Top Up Storage</span>
                    </a>
                </div>
            </div>

            <!-- 2. AI Tokens (Gemini Flash Intelligence) -->
            <div class="rounded-3xl p-6 border border-amber-500/30 bg-gradient-to-br from-amber-950/20 via-slate-900/90 to-slate-950 flex flex-col justify-between gap-5 shadow-xl backdrop-blur-md relative overflow-hidden">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-amber-300 text-xs font-black uppercase tracking-wider">
                            <div class="p-2 rounded-xl bg-amber-500/15 text-amber-400" aria-hidden="true">
                                <i data-lucide="bot" class="w-4 h-4"></i>
                            </div>
                            <span>Token Asisten AI Bisnis</span>
                        </div>
                        <span class="text-[10px] font-black px-2.5 py-1 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30">
                            Gemini 2.5 Engine
                        </span>
                    </div>

                    <div class="flex items-baseline gap-2 pt-1">
                        <span class="text-3xl font-black font-mono text-white">
                            {{ number_format($usage['ai_tokens']['remaining'] ?? 0, 0, ',', '.') }}
                        </span>
                        <span class="text-xs text-slate-400 font-mono">Token Tersisa</span>
                    </div>

                    <!-- AI Token Usage Bar with ARIA -->
                    @php $tokenPercent = min(100, max(0, (int)($usage['ai_tokens']['percent'] ?? 0))); @endphp
                    <div class="space-y-1.5" role="progressbar" aria-valuenow="{{ $tokenPercent }}" aria-valuemin="0" aria-valuemax="100" aria-label="Persentase Pemakaian Token AI">
                        <div class="w-full bg-slate-950 rounded-full h-2.5 overflow-hidden p-0.5 border border-slate-800">
                            <div class="h-full rounded-full bg-gradient-to-r from-amber-500 to-yellow-400 transition-all duration-500"
                                style="width: {{ $tokenPercent }}%"></div>
                        </div>
                        <div class="flex justify-between text-[11px] text-slate-400 font-mono">
                            <span>Terpakai: <strong class="text-slate-200">{{ $tokenPercent }}%</strong></span>
                            <span class="text-amber-300 font-medium">Asisten Otomatis Aktif</span>
                        </div>
                    </div>

                    <p class="text-xs text-slate-400 leading-relaxed">
                        Daya komputasi untuk peramalan tren omzet kasir, simulasi margin HPP resep otomatis, dan konsultasi operasional Cooca AI.
                    </p>
                </div>

                <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between">
                    <span class="text-xs text-slate-400 font-medium">Beli paket isi ulang</span>
                    <a href="{{ route('billing.checkout', ['type' => 'ai_token']) }}"
                        class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-400 to-yellow-400 hover:from-amber-300 hover:to-yellow-300 text-slate-950 text-xs font-black flex items-center gap-1.5 shadow-lg shadow-amber-500/20 transition-all hover:scale-105 active:scale-95 focus-visible:ring-2 focus-visible:ring-amber-400">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5" aria-hidden="true"></i>
                        <span>Top Up Token AI</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 2: Monthly Commercial Quotas (POS, B2B Invoices, Purchase Orders) -->
    <section aria-labelledby="monthly-quotas-heading" class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 border-b border-slate-800 pb-3">
            <div class="flex items-center gap-2">
                <i data-lucide="calendar-check" class="w-4 h-4 text-purple-400" aria-hidden="true"></i>
                <h2 id="monthly-quotas-heading" class="text-sm font-extrabold uppercase tracking-wider text-slate-200">
                    Kuota Transaksi Bulanan
                </h2>
            </div>
            <span class="text-xs text-slate-500 font-mono">Reset otomatis setiap tanggal 1 awal bulan (00:00 WIB)</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- 1. POS Orders -->
            @php 
                $pos = $usage['pos_this_month'] ?? [];
                $posPercent = $usage['is_core'] ? 100 : min(100, max(0, (int)($pos['percent'] ?? 0)));
                $posReached = $pos['is_reached'] ?? false;
            @endphp
            <div class="rounded-2xl p-5 border {{ $posReached ? 'border-rose-500/50 bg-rose-950/15' : 'border-slate-800 bg-slate-900/80' }} space-y-3 flex flex-col justify-between backdrop-blur-md shadow-lg transition-all hover:border-slate-700">
                <div>
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-xs font-bold uppercase tracking-wider">Transaksi Kasir POS</span>
                        <div class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400" aria-hidden="true">
                            <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                        </div>
                    </div>
                    
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl font-black font-mono {{ $posReached ? 'text-rose-400' : 'text-white' }}">
                            {{ number_format($pos['used'] ?? 0, 0, ',', '.') }}
                        </span>
                        <span class="text-xs text-slate-400 font-mono">
                            / {{ $pos['limit'] ? number_format($pos['limit'], 0, ',', '.') . ' struk' : '∞ Unlimited' }}
                        </span>
                    </div>

                    <div class="w-full bg-slate-950 rounded-full h-2 overflow-hidden mt-3 border border-slate-800" role="progressbar" aria-valuenow="{{ $posPercent }}" aria-valuemin="0" aria-valuemax="100" aria-label="Kuota Transaksi Kasir POS">
                        <div class="h-full rounded-full transition-all duration-500 {{ $posReached ? 'bg-rose-500' : ($posPercent >= 80 ? 'bg-amber-400' : 'bg-emerald-500') }}"
                            style="width: {{ $posPercent }}%"></div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between text-[11px]">
                    <span class="text-slate-400">{{ $usage['is_core'] ? 'Bebas transaksi kasir' : 'Maks. 100 struk/bln (Free)' }}</span>
                    @if($posReached)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/40">Batas Tercapai</span>
                    @elseif($usage['is_core'])
                        <span class="text-emerald-400 font-bold font-mono">UNLIMITED</span>
                    @endif
                </div>
            </div>

            <!-- 2. Invoices (B2B) -->
            @php 
                $inv = $usage['invoices_this_month'] ?? [];
                $invPercent = $usage['is_core'] ? 100 : min(100, max(0, (int)($inv['percent'] ?? 0)));
                $invReached = $inv['is_reached'] ?? false;
            @endphp
            <div class="rounded-2xl p-5 border {{ $invReached ? 'border-rose-500/50 bg-rose-950/15' : 'border-slate-800 bg-slate-900/80' }} space-y-3 flex flex-col justify-between backdrop-blur-md shadow-lg transition-all hover:border-slate-700">
                <div>
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-xs font-bold uppercase tracking-wider">Faktur Penjualan (B2B)</span>
                        <div class="p-1.5 rounded-lg bg-purple-500/10 text-purple-400" aria-hidden="true">
                            <i data-lucide="receipt" class="w-4 h-4"></i>
                        </div>
                    </div>

                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl font-black font-mono {{ $invReached ? 'text-rose-400' : 'text-white' }}">
                            {{ number_format($inv['used'] ?? 0, 0, ',', '.') }}
                        </span>
                        <span class="text-xs text-slate-400 font-mono">
                            / {{ $inv['limit'] ? number_format($inv['limit'], 0, ',', '.') . ' faktur' : '∞ Unlimited' }}
                        </span>
                    </div>

                    <div class="w-full bg-slate-950 rounded-full h-2 overflow-hidden mt-3 border border-slate-800" role="progressbar" aria-valuenow="{{ $invPercent }}" aria-valuemin="0" aria-valuemax="100" aria-label="Kuota Faktur Penjualan B2B">
                        <div class="h-full rounded-full transition-all duration-500 {{ $invReached ? 'bg-rose-500' : ($invPercent >= 80 ? 'bg-amber-400' : 'bg-purple-500') }}"
                            style="width: {{ $invPercent }}%"></div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between text-[11px]">
                    <span class="text-slate-400">{{ $usage['is_core'] ? 'Bebas cetak faktur digital' : 'Maks. 10 faktur/bln (Free)' }}</span>
                    @if($invReached)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/40">Batas Tercapai</span>
                    @elseif($usage['is_core'])
                        <span class="text-purple-400 font-bold font-mono">UNLIMITED</span>
                    @endif
                </div>
            </div>

            <!-- 3. Purchase Orders (PO) -->
            @php 
                $po = $usage['po_this_month'] ?? [];
                $poPercent = $usage['is_core'] ? 100 : min(100, max(0, (int)($po['percent'] ?? 0)));
                $poReached = $po['is_reached'] ?? false;
            @endphp
            <div class="rounded-2xl p-5 border {{ $poReached ? 'border-rose-500/50 bg-rose-950/15' : 'border-slate-800 bg-slate-900/80' }} space-y-3 flex flex-col justify-between backdrop-blur-md shadow-lg transition-all hover:border-slate-700">
                <div>
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-xs font-bold uppercase tracking-wider">Purchase Order (PO)</span>
                        <div class="p-1.5 rounded-lg bg-cyan-500/10 text-cyan-400" aria-hidden="true">
                            <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                        </div>
                    </div>

                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl font-black font-mono {{ $poReached ? 'text-rose-400' : 'text-white' }}">
                            {{ number_format($po['used'] ?? 0, 0, ',', '.') }}
                        </span>
                        <span class="text-xs text-slate-400 font-mono">
                            / {{ $po['limit'] ? number_format($po['limit'], 0, ',', '.') . ' PO' : '∞ Unlimited' }}
                        </span>
                    </div>

                    <div class="w-full bg-slate-950 rounded-full h-2 overflow-hidden mt-3 border border-slate-800" role="progressbar" aria-valuenow="{{ $poPercent }}" aria-valuemin="0" aria-valuemax="100" aria-label="Kuota Purchase Order Supplier">
                        <div class="h-full rounded-full transition-all duration-500 {{ $poReached ? 'bg-rose-500' : ($poPercent >= 80 ? 'bg-amber-400' : 'bg-cyan-500') }}"
                            style="width: {{ $poPercent }}%"></div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between text-[11px]">
                    <span class="text-slate-400">{{ $usage['is_core'] ? 'Bebas order supplier' : 'Maks. 10 PO/bln (Free)' }}</span>
                    @if($poReached)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/40">Batas Tercapai</span>
                    @elseif($usage['is_core'])
                        <span class="text-cyan-400 font-bold font-mono">UNLIMITED</span>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 3: Master Data & Catalog Capacity (Organized 8-card grid) -->
    <section aria-labelledby="catalog-capacity-heading" class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 border-b border-slate-800 pb-3">
            <div class="flex items-center gap-2">
                <i data-lucide="database" class="w-4 h-4 text-emerald-400" aria-hidden="true"></i>
                <h2 id="catalog-capacity-heading" class="text-sm font-extrabold uppercase tracking-wider text-slate-200">
                    Master Data &amp; Kapasitas Entitas Bisnis
                </h2>
            </div>
            <span class="text-xs text-slate-500 font-mono">Penyimpanan basis data operasional terstruktur</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Katalog Produk -->
            @php $prd = $usage['products'] ?? []; @endphp
            <div class="rounded-2xl p-4 border {{ ($prd['is_reached'] ?? false) ? 'border-rose-500/50 bg-rose-950/15' : 'border-slate-800 bg-slate-900/80' }} space-y-2 flex flex-col justify-between shadow-md transition-all hover:border-slate-700">
                <div>
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-xs font-bold uppercase tracking-wider">Katalog Produk</span>
                        <i data-lucide="box" class="w-4 h-4 text-emerald-400" aria-hidden="true"></i>
                    </div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-2xl font-black font-mono {{ ($prd['is_reached'] ?? false) ? 'text-rose-400' : 'text-white' }}">{{ $prd['used'] ?? 0 }}</span>
                        <span class="text-xs text-slate-400 font-mono">/ {{ $prd['limit'] ? $prd['limit'] . ' item' : '∞ Unlimited' }}</span>
                    </div>
                    <div class="w-full bg-slate-950 rounded-full h-1.5 overflow-hidden mt-2 border border-slate-800" role="progressbar" aria-valuenow="{{ $usage['is_core'] ? 100 : min(100, $prd['percent'] ?? 0) }}" aria-valuemin="0" aria-valuemax="100" aria-label="Kapasitas Produk">
                        <div class="h-full rounded-full transition-all duration-500 {{ ($prd['is_reached'] ?? false) ? 'bg-rose-500' : 'bg-emerald-500' }}"
                            style="width: {{ $usage['is_core'] ? 100 : min(100, $prd['percent'] ?? 0) }}%"></div>
                    </div>
                </div>
                <div class="text-[11px] text-slate-400 pt-1 font-mono">{{ $usage['is_core'] ? 'Katalog tanpa batas' : 'Maks. 50 produk (Free)' }}</div>
            </div>

            <!-- 2. Bahan Baku -->
            @php $mat = $usage['materials'] ?? []; @endphp
            <div class="rounded-2xl p-4 border {{ ($mat['is_reached'] ?? false) ? 'border-rose-500/50 bg-rose-950/15' : 'border-slate-800 bg-slate-900/80' }} space-y-2 flex flex-col justify-between shadow-md transition-all hover:border-slate-700">
                <div>
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-xs font-bold uppercase tracking-wider">Bahan Baku</span>
                        <i data-lucide="layers" class="w-4 h-4 text-cyan-400" aria-hidden="true"></i>
                    </div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-2xl font-black font-mono {{ ($mat['is_reached'] ?? false) ? 'text-rose-400' : 'text-white' }}">{{ $mat['used'] ?? 0 }}</span>
                        <span class="text-xs text-slate-400 font-mono">/ {{ $mat['limit'] ? $mat['limit'] . ' item' : '∞ Unlimited' }}</span>
                    </div>
                    <div class="w-full bg-slate-950 rounded-full h-1.5 overflow-hidden mt-2 border border-slate-800" role="progressbar" aria-valuenow="{{ $usage['is_core'] ? 100 : min(100, $mat['percent'] ?? 0) }}" aria-valuemin="0" aria-valuemax="100" aria-label="Kapasitas Bahan Baku">
                        <div class="h-full rounded-full transition-all duration-500 {{ ($mat['is_reached'] ?? false) ? 'bg-rose-500' : 'bg-cyan-500' }}"
                            style="width: {{ $usage['is_core'] ? 100 : min(100, $mat['percent'] ?? 0) }}%"></div>
                    </div>
                </div>
                <div class="text-[11px] text-slate-400 pt-1 font-mono">{{ $usage['is_core'] ? 'Bahan baku tanpa batas' : 'Maks. 20 bahan baku (Free)' }}</div>
            </div>

            <!-- 3. Resep HPP (BOM) -->
            @php $rcp = $usage['recipes'] ?? []; @endphp
            <div class="rounded-2xl p-4 border {{ ($rcp['is_reached'] ?? false) ? 'border-rose-500/50 bg-rose-950/15' : 'border-slate-800 bg-slate-900/80' }} space-y-2 flex flex-col justify-between shadow-md transition-all hover:border-slate-700">
                <div>
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-xs font-bold uppercase tracking-wider">Resep HPP (BOM)</span>
                        <i data-lucide="chef-hat" class="w-4 h-4 text-amber-400" aria-hidden="true"></i>
                    </div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-2xl font-black font-mono {{ ($rcp['is_reached'] ?? false) ? 'text-rose-400' : 'text-white' }}">{{ $rcp['used'] ?? 0 }}</span>
                        <span class="text-xs text-slate-400 font-mono">/ {{ $rcp['limit'] ? $rcp['limit'] . ' resep' : '∞ Unlimited' }}</span>
                    </div>
                    <div class="w-full bg-slate-950 rounded-full h-1.5 overflow-hidden mt-2 border border-slate-800" role="progressbar" aria-valuenow="{{ $usage['is_core'] ? 100 : min(100, $rcp['percent'] ?? 0) }}" aria-valuemin="0" aria-valuemax="100" aria-label="Kapasitas Resep">
                        <div class="h-full rounded-full transition-all duration-500 {{ ($rcp['is_reached'] ?? false) ? 'bg-rose-500' : 'bg-amber-500' }}"
                            style="width: {{ $usage['is_core'] ? 100 : min(100, $rcp['percent'] ?? 0) }}%"></div>
                    </div>
                </div>
                <div class="text-[11px] text-slate-400 pt-1 font-mono">{{ $usage['is_core'] ? 'Resep tanpa batas' : 'Maks. 20 resep (Free)' }}</div>
            </div>

            <!-- 4. Pelanggan CRM -->
            @php $cst = $usage['customers'] ?? []; @endphp
            <div class="rounded-2xl p-4 border {{ ($cst['is_reached'] ?? false) ? 'border-rose-500/50 bg-rose-950/15' : 'border-slate-800 bg-slate-900/80' }} space-y-2 flex flex-col justify-between shadow-md transition-all hover:border-slate-700">
                <div>
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-xs font-bold uppercase tracking-wider">Pelanggan CRM</span>
                        <i data-lucide="users" class="w-4 h-4 text-indigo-400" aria-hidden="true"></i>
                    </div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-2xl font-black font-mono {{ ($cst['is_reached'] ?? false) ? 'text-rose-400' : 'text-white' }}">{{ $cst['used'] ?? 0 }}</span>
                        <span class="text-xs text-slate-400 font-mono">/ {{ $cst['limit'] ? $cst['limit'] . ' kontak' : '∞ Unlimited' }}</span>
                    </div>
                    <div class="w-full bg-slate-950 rounded-full h-1.5 overflow-hidden mt-2 border border-slate-800" role="progressbar" aria-valuenow="{{ $usage['is_core'] ? 100 : min(100, $cst['percent'] ?? 0) }}" aria-valuemin="0" aria-valuemax="100" aria-label="Kapasitas Pelanggan">
                        <div class="h-full rounded-full transition-all duration-500 {{ ($cst['is_reached'] ?? false) ? 'bg-rose-500' : 'bg-indigo-500' }}"
                            style="width: {{ $usage['is_core'] ? 100 : min(100, $cst['percent'] ?? 0) }}%"></div>
                    </div>
                </div>
                <div class="text-[11px] text-slate-400 pt-1 font-mono">{{ $usage['is_core'] ? 'Database kontak unlimited' : 'Maks. 30 kontak (Free)' }}</div>
            </div>

            <!-- 5. Pemasok / Supplier -->
            @php $sup = $usage['suppliers'] ?? []; @endphp
            <div class="rounded-2xl p-4 border {{ ($sup['is_reached'] ?? false) ? 'border-rose-500/50 bg-rose-950/15' : 'border-slate-800 bg-slate-900/80' }} space-y-2 flex flex-col justify-between shadow-md transition-all hover:border-slate-700">
                <div>
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-xs font-bold uppercase tracking-wider">Pemasok / Vendor</span>
                        <i data-lucide="truck" class="w-4 h-4 text-rose-400" aria-hidden="true"></i>
                    </div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-2xl font-black font-mono {{ ($sup['is_reached'] ?? false) ? 'text-rose-400' : 'text-white' }}">{{ $sup['used'] ?? 0 }}</span>
                        <span class="text-xs text-slate-400 font-mono">/ {{ $sup['limit'] ? $sup['limit'] . ' vendor' : '∞ Unlimited' }}</span>
                    </div>
                    <div class="w-full bg-slate-950 rounded-full h-1.5 overflow-hidden mt-2 border border-slate-800" role="progressbar" aria-valuenow="{{ $usage['is_core'] ? 100 : min(100, $sup['percent'] ?? 0) }}" aria-valuemin="0" aria-valuemax="100" aria-label="Kapasitas Supplier">
                        <div class="h-full rounded-full transition-all duration-500 {{ ($sup['is_reached'] ?? false) ? 'bg-rose-500' : 'bg-rose-500' }}"
                            style="width: {{ $usage['is_core'] ? 100 : min(100, $sup['percent'] ?? 0) }}%"></div>
                    </div>
                </div>
                <div class="text-[11px] text-slate-400 pt-1 font-mono">{{ $usage['is_core'] ? 'Daftar vendor tanpa batas' : 'Maks. 20 vendor (Free)' }}</div>
            </div>

            <!-- 6. Outlet & Gudang -->
            <div class="rounded-2xl p-4 border border-slate-800 bg-slate-900/80 space-y-2 flex flex-col justify-between shadow-md transition-all hover:border-slate-700">
                <div>
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-xs font-bold uppercase tracking-wider">Outlet &amp; Gudang</span>
                        <i data-lucide="store" class="w-4 h-4 text-teal-400" aria-hidden="true"></i>
                    </div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-2xl font-black font-mono text-white">{{ ($usage['outlets']['used'] ?? 0) + ($usage['warehouses']['used'] ?? 0) }}</span>
                        <span class="text-xs text-slate-400 font-mono">/ {{ $usage['is_core'] ? '∞ Unlimited' : '1 Toko + 1 Gudang' }}</span>
                    </div>
                    <div class="w-full bg-slate-950 rounded-full h-1.5 overflow-hidden mt-2 border border-slate-800" role="progressbar" aria-valuenow="{{ $usage['is_core'] ? 100 : min(100, ($usage['outlets']['percent'] ?? 0)) }}" aria-valuemin="0" aria-valuemax="100" aria-label="Kapasitas Outlet dan Gudang">
                        <div class="h-full rounded-full bg-teal-500 transition-all duration-500"
                            style="width: {{ $usage['is_core'] ? 100 : min(100, ($usage['outlets']['percent'] ?? 0)) }}%"></div>
                    </div>
                </div>
                <div class="text-[11px] text-slate-400 pt-1 font-mono">{{ $usage['is_core'] ? 'Multi-cabang & multi-gudang' : '1 cabang outlet (Free)' }}</div>
            </div>

            <!-- 7. Pengguna / Karyawan -->
            <div class="rounded-2xl p-4 border border-slate-800 bg-slate-900/80 space-y-2 flex flex-col justify-between shadow-md transition-all hover:border-slate-700">
                <div>
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-xs font-bold uppercase tracking-wider">Karyawan / Kasir</span>
                        <i data-lucide="user-check" class="w-4 h-4 text-blue-400" aria-hidden="true"></i>
                    </div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-2xl font-black font-mono text-white">{{ $usage['users']['used'] ?? 0 }}</span>
                        <span class="text-xs text-slate-400 font-mono">/ {{ $usage['is_core'] ? '∞ Unlimited' : 'Solo Owner' }}</span>
                    </div>
                    <div class="w-full bg-slate-950 rounded-full h-1.5 overflow-hidden mt-2 border border-slate-800" role="progressbar" aria-valuenow="{{ $usage['is_core'] ? 100 : min(100, ($usage['users']['percent'] ?? 0)) }}" aria-valuemin="0" aria-valuemax="100" aria-label="Kapasitas Karyawan Kasir">
                        <div class="h-full rounded-full bg-blue-500 transition-all duration-500"
                            style="width: {{ $usage['is_core'] ? 100 : min(100, ($usage['users']['percent'] ?? 0)) }}%"></div>
                    </div>
                </div>
                <div class="text-[11px] text-slate-400 pt-1 font-mono">{{ $usage['is_core'] ? 'Multi-user kasir & admin' : 'Hanya Owner (Free)' }}</div>
            </div>

            <!-- 8. Entitas Bisnis -->
            <div class="rounded-2xl p-4 border border-slate-800 bg-slate-900/80 space-y-2 flex flex-col justify-between shadow-md transition-all hover:border-slate-700">
                <div>
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-xs font-bold uppercase tracking-wider">Entitas Bisnis</span>
                        <i data-lucide="building-2" class="w-4 h-4 text-violet-400" aria-hidden="true"></i>
                    </div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-2xl font-black font-mono text-white">{{ $usage['businesses']['used'] ?? 1 }}</span>
                        <span class="text-xs text-slate-400 font-mono">/ {{ $usage['is_core'] ? '∞ Unlimited' : '1 Bisnis' }}</span>
                    </div>
                    <div class="w-full bg-slate-950 rounded-full h-1.5 overflow-hidden mt-2 border border-slate-800" role="progressbar" aria-valuenow="{{ $usage['is_core'] ? 100 : min(100, ($usage['businesses']['percent'] ?? 0)) }}" aria-valuemin="0" aria-valuemax="100" aria-label="Kapasitas Entitas Bisnis">
                        <div class="h-full rounded-full bg-violet-500 transition-all duration-500"
                            style="width: {{ $usage['is_core'] ? 100 : min(100, ($usage['businesses']['percent'] ?? 0)) }}%"></div>
                    </div>
                </div>
                <div class="text-[11px] text-slate-400 pt-1 font-mono">{{ $usage['is_core'] ? 'Multi-bisnis dalam 1 akun' : '1 bisnis tunggal (Free)' }}</div>
            </div>
        </div>
    </section>

    <!-- SECTION 4: Feature Comparison Matrix (SaaS Enterprise Grade) -->
    <section aria-labelledby="matrix-heading" class="rounded-3xl p-6 sm:p-8 border border-slate-800 bg-slate-900/90 shadow-2xl backdrop-blur-xl space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-5">
            <div>
                <h3 id="matrix-heading" class="text-lg font-black text-white flex items-center gap-2">
                    <i data-lucide="columns-3" class="w-5 h-5 text-emerald-400" aria-hidden="true"></i>
                    <span>Matriks Perbandingan Kemampuan Paket</span>
                </h3>
                <p class="text-xs text-slate-400 mt-1">Perbedaan hak akses dan skala bisnis antara Paket Free Solo dan Program Patungan Cooca UMKM.</p>
            </div>
            @if(!$usage['is_core'])
                <a href="{{ route('billing.checkout') }}"
                    class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 text-slate-950 font-black text-xs hover:from-emerald-400 hover:to-teal-300 shadow-md shadow-emerald-500/20 transition-all self-start sm:self-auto flex items-center gap-1.5 focus-visible:ring-2 focus-visible:ring-emerald-500">
                    <i data-lucide="zap" class="w-3.5 h-3.5" aria-hidden="true"></i>
                    <span>Upgrade Sekarang</span>
                </a>
            @endif
        </div>

        <!-- Matrix Table Container with Horizontal Scroll Notice on Mobile -->
        <div class="relative overflow-x-auto -mx-6 sm:mx-0 px-6 sm:px-0">
            <table class="w-full text-left text-xs min-w-[620px] border-collapse" aria-label="Tabel Matriks Perbandingan Fitur Paket">
                <thead class="border-b border-slate-800 text-slate-400 font-mono uppercase text-[10px]">
                    <tr>
                        <th scope="col" class="py-3 px-4 w-1/2">Fitur &amp; Kemampuan Utama</th>
                        <th scope="col" class="py-3 px-4 text-center w-1/4">Paket Free (Solo)</th>
                        <th scope="col" class="py-3 px-4 text-center w-1/4 bg-emerald-950/30 text-emerald-300 rounded-t-xl font-bold border-t border-x border-emerald-500/30">
                            Cooca UMKM (Patungan)
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-sans">
                    <tr class="hover:bg-slate-850/40 transition-colors">
                        <td class="py-3.5 px-4 font-semibold text-white">Katalog Produk &amp; SKU Varian</td>
                        <td class="py-3.5 px-4 text-center text-slate-400 font-mono">Maks. 50 Item</td>
                        <td class="py-3.5 px-4 text-center font-mono font-bold text-emerald-300 bg-emerald-950/20 border-x border-emerald-500/30">
                            ∞ Tanpa Batas
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-850/40 transition-colors">
                        <td class="py-3.5 px-4 font-semibold text-white">Resep HPP / Bill of Materials (BOM)</td>
                        <td class="py-3.5 px-4 text-center text-slate-400 font-mono">Maks. 20 Resep</td>
                        <td class="py-3.5 px-4 text-center font-mono font-bold text-emerald-300 bg-emerald-950/20 border-x border-emerald-500/30">
                            ∞ Tanpa Batas
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-850/40 transition-colors">
                        <td class="py-3.5 px-4 font-semibold text-white">Transaksi Kasir POS Per Bulan</td>
                        <td class="py-3.5 px-4 text-center text-slate-400 font-mono">100 Struk / bln</td>
                        <td class="py-3.5 px-4 text-center font-mono font-bold text-emerald-300 bg-emerald-950/20 border-x border-emerald-500/30">
                            ∞ Tanpa Batas
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-850/40 transition-colors">
                        <td class="py-3.5 px-4 font-semibold text-white">Faktur Penjualan &amp; Surat Jalan (B2B)</td>
                        <td class="py-3.5 px-4 text-center text-slate-400 font-mono">10 Faktur / bln</td>
                        <td class="py-3.5 px-4 text-center font-mono font-bold text-emerald-300 bg-emerald-950/20 border-x border-emerald-500/30">
                            ∞ Tanpa Batas
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-850/40 transition-colors">
                        <td class="py-3.5 px-4 font-semibold text-white">Multi-Cabang Outlet &amp; Gudang Terpisah</td>
                        <td class="py-3.5 px-4 text-center text-slate-500">1 Toko Tunggal</td>
                        <td class="py-3.5 px-4 text-center font-mono font-bold text-emerald-300 bg-emerald-950/20 border-x border-emerald-500/30">
                            Multi-Gudang Aktif
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-850/40 transition-colors">
                        <td class="py-3.5 px-4 font-semibold text-white">Ekspor Laporan Excel Multi-Sheet Komprehensif</td>
                        <td class="py-3.5 px-4 text-center text-slate-600">
                            <i data-lucide="x" class="w-4 h-4 mx-auto text-slate-600" aria-label="Tidak Tersedia"></i>
                        </td>
                        <td class="py-3.5 px-4 text-center text-emerald-400 bg-emerald-950/20 border-x border-emerald-500/30 font-bold">
                            <i data-lucide="check" class="w-4 h-4 mx-auto text-emerald-400 font-black" aria-label="Tersedia"></i>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-850/40 transition-colors">
                        <td class="py-3.5 px-4 font-semibold text-white">Impor Massal Produk &amp; Resep Excel</td>
                        <td class="py-3.5 px-4 text-center text-slate-600">
                            <i data-lucide="x" class="w-4 h-4 mx-auto text-slate-600" aria-label="Tidak Tersedia"></i>
                        </td>
                        <td class="py-3.5 px-4 text-center text-emerald-400 bg-emerald-950/20 border-x border-emerald-500/30 font-bold">
                            <i data-lucide="check" class="w-4 h-4 mx-auto text-emerald-400 font-black" aria-label="Tersedia"></i>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-850/40 transition-colors">
                        <td class="py-3.5 px-4 font-semibold text-white">Akun Karyawan &amp; Hak Akses Kasir/Admin</td>
                        <td class="py-3.5 px-4 text-center text-slate-500">Solo Owner Only</td>
                        <td class="py-3.5 px-4 text-center font-mono font-bold text-emerald-300 bg-emerald-950/20 border-x border-emerald-500/30">
                            Multi-User Terkendali
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-850/40 transition-colors">
                        <td class="py-3.5 px-4 font-semibold text-white">Kirim Struk Otomatis Bot WhatsApp Kasir</td>
                        <td class="py-3.5 px-4 text-center text-slate-500">Manual Share</td>
                        <td class="py-3.5 px-4 text-center text-emerald-400 bg-emerald-950/20 border-x border-emerald-500/30">
                            <i data-lucide="check" class="w-4 h-4 mx-auto text-emerald-400 font-black" aria-label="Tersedia"></i>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- SECTION 5: Trust, Continuity & Privacy Commitment -->
    <section aria-labelledby="commitment-heading" class="rounded-2xl p-5 sm:p-6 border border-emerald-500/30 bg-emerald-950/15 flex flex-col sm:flex-row items-start sm:items-center gap-4 backdrop-blur-md">
        <div class="p-3 rounded-2xl bg-emerald-500/20 text-emerald-400 shrink-0" aria-hidden="true">
            <i data-lucide="heart-handshake" class="w-6 h-6"></i>
        </div>
        <div class="text-xs space-y-1 flex-1">
            <h4 id="commitment-heading" class="font-extrabold text-white text-sm">Komitmen Privasi: Bebas Dari Hukuman Data (No Data Punishment)</h4>
            <p class="text-slate-300 leading-relaxed">
                Data bisnis Anda adalah aset milik Anda seutuhnya. Jika langganan berakhir atau mencapai kuota pemakaian, Cooca UMKM <strong class="text-emerald-300 font-semibold">tidak akan pernah menghapus, membatasi baca, ataupun mengunci akses data riwayat Anda</strong>. Seluruh laporan transaksi, pembukuan kas, dan rekam jejak stok tetap dapat diekspor dan dilihat kapan saja.
            </p>
        </div>
    </section>

</div>
@endsection
