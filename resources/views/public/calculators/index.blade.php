@extends('layouts.public_marketing')

@section('title', 'Koleksi 8 Kalkulator Bisnis UMKM Online Gratis | Cooca UMKM')
@section('description', 'Koleksi kalkulator bisnis gratis untuk UMKM: Kalkulator HPP, BEP Titik Impas, Margin Harga Jual, Laba Bersih, Gaji Karyawan, PPh Final 0.5%, dan Simulasi What-If.')
@section('keywords', 'kalkulator bisnis umkm, kalkulator hpp online, kalkulator bep gratis, hitung harga jual margin, simulasi laba rugi, kalkulator pph 0.5')

@section('content')
<div class="pt-12 pb-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Hero Section -->
        <div class="text-center max-w-3xl mx-auto mb-16">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 font-bold text-xs mb-4">
                <i data-lucide="calculator" class="w-4 h-4"></i>
                <span>Interactive Business Tools</span>
            </div>
            <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight">
                Koleksi <span class="text-gradient-accent">Kalkulator Bisnis</span> UMKM
            </h1>
            <p class="text-sm sm:text-base text-slate-400 mt-4 leading-relaxed">
                Ambil keputusan bisnis lebih cepat, akurat, dan berbasis data. Semua tools dapat Anda gunakan secara instan tanpa perlu registrasi atau berlangganan.
            </p>
        </div>

        <!-- Grid 8 Kalkulator -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

            <!-- 1. HPP -->
            <a href="{{ route('kalkulator.hpp') }}" class="glass-card p-6 rounded-3xl group flex flex-col justify-between">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="layers" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-400">Harga Pokok Penjualan</span>
                    <h2 class="text-lg font-bold text-white mt-1 group-hover:text-indigo-400 transition-colors">Kalkulator HPP</h2>
                    <p class="text-xs text-slate-400 mt-2 leading-relaxed">
                        Hitung biaya bahan baku, tenaga kerja, dan overhead per porsi/produk untuk mengunci harga jual aman.
                    </p>
                </div>
                <div class="mt-6 flex items-center gap-2 text-xs font-bold text-indigo-400">
                    <span>Gunakan Tool</span>
                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </a>

            <!-- 2. BEP -->
            <a href="{{ route('kalkulator.bep') }}" class="glass-card p-6 rounded-3xl group flex flex-col justify-between">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="scale" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400">Titik Impas Usaha</span>
                    <h2 class="text-lg font-bold text-white mt-1 group-hover:text-emerald-400 transition-colors">Kalkulator BEP</h2>
                    <p class="text-xs text-slate-400 mt-2 leading-relaxed">
                        Ketahui berapa nominal omzet dan jumlah unit yang harus terjual agar usaha tidak merugi.
                    </p>
                </div>
                <div class="mt-6 flex items-center gap-2 text-xs font-bold text-emerald-400">
                    <span>Gunakan Tool</span>
                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </a>

            <!-- 3. Harga Jual -->
            <a href="{{ route('kalkulator.harga-jual') }}" class="glass-card p-6 rounded-3xl group flex flex-col justify-between">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="tag" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-400">Markup vs Margin</span>
                    <h2 class="text-lg font-bold text-white mt-1 group-hover:text-amber-400 transition-colors">Kalkulator Harga Jual</h2>
                    <p class="text-xs text-slate-400 mt-2 leading-relaxed">
                        Hindari salah hitung persentase markup vs gross margin untuk menetapkan harga retail ideal.
                    </p>
                </div>
                <div class="mt-6 flex items-center gap-2 text-xs font-bold text-amber-400">
                    <span>Gunakan Tool</span>
                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </a>

            <!-- 4. Laba Bersih -->
            <a href="{{ route('kalkulator.laba-bersih') }}" class="glass-card p-6 rounded-3xl group flex flex-col justify-between">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="pie-chart" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-400">Net Profit Margin</span>
                    <h2 class="text-lg font-bold text-white mt-1 group-hover:text-blue-400 transition-colors">Kalkulator Laba Bersih</h2>
                    <p class="text-xs text-slate-400 mt-2 leading-relaxed">
                        Simulasikan omzet kotor, beban operasional, gaji, listrik, dan pajak hingga menemukan laba bersih riil.
                    </p>
                </div>
                <div class="mt-6 flex items-center gap-2 text-xs font-bold text-blue-400">
                    <span>Gunakan Tool</span>
                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </a>

            <!-- 5. Gaji Karyawan -->
            <a href="{{ route('kalkulator.gaji-karyawan') }}" class="glass-card p-6 rounded-3xl group flex flex-col justify-between">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="users" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-purple-400">Payroll & Upah</span>
                    <h2 class="text-lg font-bold text-white mt-1 group-hover:text-purple-400 transition-colors">Gaji Karyawan</h2>
                    <p class="text-xs text-slate-400 mt-2 leading-relaxed">
                        Hitung gaji harian/bulanan, tunjangan makan, uang lembur, dan total take-home-pay staf Anda.
                    </p>
                </div>
                <div class="mt-6 flex items-center gap-2 text-xs font-bold text-purple-400">
                    <span>Gunakan Tool</span>
                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </a>

            <!-- 6. PPh Final 0.5% -->
            <a href="{{ route('kalkulator.pph-final') }}" class="glass-card p-6 rounded-3xl group flex flex-col justify-between">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400 mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="receipt" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-rose-400">PP 55 / 2022</span>
                    <h2 class="text-lg font-bold text-white mt-1 group-hover:text-rose-400 transition-colors">PPh Final UMKM 0.5%</h2>
                    <p class="text-xs text-slate-400 mt-2 leading-relaxed">
                        Simulasikan pajak UMKM orang pribadi dengan batas omzet kumulatif 500 juta bebas pajak per tahun.
                    </p>
                </div>
                <div class="mt-6 flex items-center gap-2 text-xs font-bold text-rose-400">
                    <span>Gunakan Tool</span>
                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </a>

            <!-- 7. Target Omzet -->
            <a href="{{ route('kalkulator.omzet-harian') }}" class="glass-card p-6 rounded-3xl group flex flex-col justify-between">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="target" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-cyan-400">Sales Breakdown</span>
                    <h2 class="text-lg font-bold text-white mt-1 group-hover:text-cyan-400 transition-colors">Target Omzet Harian</h2>
                    <p class="text-xs text-slate-400 mt-2 leading-relaxed">
                        Ubah target omzet bulanan menjadi target transaksi harian dan rata-rata belanja (average basket size).
                    </p>
                </div>
                <div class="mt-6 flex items-center gap-2 text-xs font-bold text-cyan-400">
                    <span>Gunakan Tool</span>
                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </a>

            <!-- 8. What-If Simulation -->
            <a href="{{ route('kalkulator.simulasi-what-if') }}" class="glass-card p-6 rounded-3xl group flex flex-col justify-between border-cyan-500/30">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-cyan-500/20 border border-cyan-500/40 flex items-center justify-center text-cyan-400 mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="sparkles" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-cyan-400">Sensitivitas Biaya</span>
                    <h2 class="text-lg font-bold text-white mt-1 group-hover:text-cyan-400 transition-colors">Simulasi What-If</h2>
                    <p class="text-xs text-slate-400 mt-2 leading-relaxed">
                        Cek dampak seketika jika harga bahan baku naik 10% atau diskon promo 20% terhadap sisa laba bersih Anda.
                    </p>
                </div>
                <div class="mt-6 flex items-center gap-2 text-xs font-bold text-cyan-400">
                    <span>Mulai Simulasi</span>
                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </a>

        </div>

        <!-- Banner Conversion to App -->
        <div class="mt-20 p-8 sm:p-12 rounded-3xl bg-gradient-to-br from-indigo-900/40 to-slate-900 border border-indigo-500/20 flex flex-col lg:flex-row items-center justify-between gap-8">
            <div class="space-y-3 max-w-xl text-center lg:text-left">
                <span class="text-xs font-bold uppercase text-indigo-400 tracking-wider">Otomatiskan Semuanya</span>
                <h3 class="text-2xl sm:text-3xl font-black text-white">Capek Hitung Manual Terus di Excel?</h3>
                <p class="text-sm text-slate-300 leading-relaxed">
                    Daftar di Cooca UMKM sekarang. Setiap transaksi kasir secara otomatis memotong stok bahan, menghitung HPP riil, dan mencatat laporan laba rugi. <strong>100% Gratis Selamanya</strong>.
                </p>
            </div>
            <div class="shrink-0 flex flex-col sm:flex-row gap-3">
                <a href="{{ route('register') }}" class="glow-btn px-8 py-3.5 rounded-xl text-white font-bold text-sm shadow-xl shadow-indigo-500/25 flex items-center justify-center gap-2">
                    <span>Daftar Gratis Sekarang</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
