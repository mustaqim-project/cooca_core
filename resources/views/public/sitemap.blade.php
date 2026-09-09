@extends('layouts.public_marketing')

@section('title', 'Peta Situs (HTML Sitemap) — Cooca UMKM')
@section('description', 'Jelajahi seluruh halaman resmi Cooca UMKM: kalkulator bisnis gratis, panduan HPP & BEP, solusi kasir per industri, template pembukuan Excel, dan direktori bisnis.')
@section('keywords', 'sitemap cooca, peta situs, navigasi cooca umkm, daftar kalkulator bisnis, direktori software kasir')

@section('content')
<main class="relative z-10 pt-28 pb-20 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
    <!-- Header Hero -->
    <div class="text-center max-w-3xl mx-auto mb-14">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
            </svg>
            <span>Arsitektur & Navigasi Terbuka</span>
        </div>
        <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-white tracking-tight leading-tight">
            Peta Situs Resmi <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-teal-300">Cooca UMKM</span>
        </h1>
        <p class="mt-4 text-sm sm:text-base text-slate-300 leading-relaxed">
            Daftar lengkap seluruh halaman publik, modul kalkulator, solusi vertikal industri, dan pustaka edukasi gratis yang terindeks resmi di sistem Cooca.
        </p>
        <div class="mt-4 inline-flex items-center gap-3 text-xs text-slate-400 font-mono">
            <span class="px-2.5 py-1 rounded-md bg-slate-900 border border-slate-800">Total Halaman Terindeks: <strong class="text-emerald-400 font-bold">{{ $totalUrls }}</strong> URL</span>
            <a href="{{ url('/sitemap.xml') }}" target="_blank" class="text-indigo-400 hover:text-indigo-300 underline flex items-center gap-1">
                <span>Lihat XML Raw</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
            </a>
        </div>
    </div>

    <!-- Sitemap Grid by Categories -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($groupedUrls as $category => $items)
            <div class="rounded-2xl bg-slate-900/70 border border-slate-800/80 p-6 backdrop-blur-xl flex flex-col justify-between hover:border-slate-700 transition duration-200">
                <div>
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-800">
                        <h2 class="font-bold text-base text-white flex items-center gap-2">
                            @if(str_contains(strtolower($category), 'kalkulator'))
                                <span class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400">🧮</span>
                            @elseif(str_contains(strtolower($category), 'solusi'))
                                <span class="p-1.5 rounded-lg bg-indigo-500/10 text-indigo-400">🎯</span>
                            @elseif(str_contains(strtolower($category), 'template'))
                                <span class="p-1.5 rounded-lg bg-amber-500/10 text-amber-400">📑</span>
                            @elseif(str_contains(strtolower($category), 'artikel') || str_contains(strtolower($category), 'edukasi'))
                                <span class="p-1.5 rounded-lg bg-cyan-500/10 text-cyan-400">📚</span>
                            @elseif(str_contains(strtolower($category), 'bisnis'))
                                <span class="p-1.5 rounded-lg bg-teal-500/10 text-teal-400">🏪</span>
                            @else
                                <span class="p-1.5 rounded-lg bg-slate-800 text-slate-300">🔗</span>
                            @endif
                            <span>{{ $category }}</span>
                        </h2>
                        <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-slate-800 text-slate-400 font-semibold">
                            {{ count($items) }} Link
                        </span>
                    </div>

                    <ul class="space-y-2.5">
                        @foreach($items as $item)
                            <li>
                                <a href="{{ $item['loc'] }}"
                                   class="group flex items-start justify-between text-xs text-slate-300 hover:text-emerald-400 transition">
                                    <span class="line-clamp-2 pr-2 leading-relaxed group-hover:translate-x-0.5 transition-transform duration-150">
                                        {{ $item['title'] }}
                                    </span>
                                    <span class="text-[9px] font-mono text-slate-500 shrink-0 mt-0.5" title="Prioritas SEO">
                                        P:{{ $item['priority'] }}
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endforeach
    </div>

    <!-- SEO Directive Summary Box -->
    <div class="mt-14 p-6 sm:p-8 rounded-2xl bg-slate-900/80 border border-slate-800 backdrop-blur-xl">
        <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>Kebijakan Indexing Mesin Pencari (Search Engine Directives)</span>
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs text-slate-400 leading-relaxed">
            <div class="p-4 rounded-xl bg-slate-950/60 border border-emerald-500/20">
                <div class="font-bold text-emerald-400 mb-1 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span>INDEX, FOLLOW (Publik & Terindeks)</span>
                </div>
                <p>Seluruh halaman pada peta situs ini (Homepage, Tools Kalkulator Bisnis, Solusi Industri, Template Excel, Artikel Blog, dan Halaman Profil Bisnis Aktif) diinstruksikan kepada bot Google, Bing, dan bot AI untuk di-crawl dan diindeks secara penuh.</p>
            </div>
            <div class="p-4 rounded-xl bg-slate-950/60 border border-rose-500/20">
                <div class="font-bold text-rose-400 mb-1 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                    <span>NOINDEX, NOFOLLOW (Privasi & Terlindungi)</span>
                </div>
                <p>Area aplikasi internal seperti Dasbor Transaksi (`/dashboard`), Kasir POS (`/pos`), Master Stok Gudang (`/inventory`), Faktur (`/invoices`), Pembelian (`/purchasing`), Laporan Keuangan Rahasia (`/reports`), Panel Admin (`/admin`), dan formulir autentikasi (`/login`, `/register`) secara tegas dilarang untuk diindeks guna menjaga kerahasiaan data pengguna.</p>
            </div>
        </div>
    </div>
</main>
@endsection
