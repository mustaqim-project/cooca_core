@extends('layouts.public_marketing')

@section('title', 'Kalkulator PPh Final UMKM 0.5% (PP 55/2022) Online Gratis | Cooca UMKM')
@section('description', 'Kalkulator simulasi pajak PPh Final 0.5% UMKM online gratis sesuai UU HPP dan PP 55/2022. Lengkap dengan perhitungan batas omzet Rp 500 juta bebas pajak per tahun.')
@section('keywords', 'kalkulator pph final 0.5, hitung pajak umkm online, pp 55 2022 pajak umkm, batas omzet 500 juta bebas pajak, cara setor pph final bulanan')

@section('content')
<div class="pt-10 pb-20">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-slate-400 mb-6">
            <a href="{{ route('landing') }}" class="hover:text-white">Beranda</a>
            <span>/</span>
            <a href="{{ route('kalkulator.index') }}" class="hover:text-white">Kalkulator</a>
            <span>/</span>
            <span class="text-rose-400 font-semibold">Kalkulator PPh Final</span>
        </nav>

        <!-- Header -->
        <div class="text-center max-w-2xl mx-auto mb-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-400 font-bold text-xs mb-3">
                <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                <span>Aturan Resmi PP 55/2022</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight">Kalkulator PPh Final UMKM 0.5%</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-2">
                Ketahui kewajiban pajak Anda. Wajib Pajak Orang Pribadi berhak atas fasilitas omzet s.d <strong>Rp 500 Juta Bebas Pajak</strong> setiap tahun pajak.
            </p>
        </div>

        <!-- Calculator Interactive App -->
        <div class="glass-card p-6 sm:p-8 rounded-3xl mb-12" x-data="{
            taxpayerType: 'individual', // 'individual' (ada threshold 500jt) or 'corporate' (langsung kena 0.5%)
            cumulativePriorRevenue: 350000000, // Omzet kumulatif bulan-bulan sebelumnya di tahun berjalan
            currentMonthRevenue: 45000000,     // Omzet bulan berjalan ini

            get newCumulative() {
                return (parseFloat(this.cumulativePriorRevenue) || 0) + (parseFloat(this.currentMonthRevenue) || 0);
            },
            get taxableRevenue() {
                if (this.taxpayerType === 'corporate') {
                    return parseFloat(this.currentMonthRevenue) || 0;
                }
                const threshold = 500000000;
                const prior = parseFloat(this.cumulativePriorRevenue) || 0;
                const curr = parseFloat(this.currentMonthRevenue) || 0;
                const total = prior + curr;

                if (total <= threshold) {
                    return 0; // Masih di bawah 500jt bebas pajak
                } else if (prior < threshold && total > threshold) {
                    return total - threshold; // Hanya porsi yang melewati 500jt
                } else {
                    return curr; // Sudah lewat 500jt sejak bulan sebelumnya, seluruh omzet bulan ini kena
                }
            },
            get taxDue() {
                return Math.round(this.taxableRevenue * 0.005);
            }
        }">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left: Inputs -->
                <div class="lg:col-span-7 space-y-5">
                    <!-- Tipe Wajib Pajak -->
                    <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800">
                        <label class="text-xs font-bold text-slate-300 uppercase tracking-wide block mb-2">Bentuk Usaha / Wajib Pajak</label>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <button type="button" @click="taxpayerType = 'individual'" :class="taxpayerType === 'individual' ? 'bg-rose-600 text-white shadow-lg' : 'text-slate-400 bg-slate-900'" class="p-2.5 rounded-xl font-bold transition-all border border-slate-800">
                                Orang Pribadi (Bebas 500 Juta)
                            </button>
                            <button type="button" @click="taxpayerType = 'corporate'" :class="taxpayerType === 'corporate' ? 'bg-rose-600 text-white shadow-lg' : 'text-slate-400 bg-slate-900'" class="p-2.5 rounded-xl font-bold transition-all border border-slate-800">
                                Badan (CV / PT - Kena Langsung)
                            </button>
                        </div>
                    </div>

                    <!-- Omzet Kumulatif Sebelumnya (Khusus Orang Pribadi) -->
                    <div x-show="taxpayerType === 'individual'" class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800">
                        <div class="flex justify-between items-center mb-1">
                            <label class="text-xs font-bold text-slate-300 uppercase tracking-wide">Omzet Kumulatif s.d Bulan Lalu</label>
                            <span class="font-mono text-sm font-bold text-white">Rp <span x-text="Number(cumulativePriorRevenue).toLocaleString('id-ID')"></span></span>
                        </div>
                        <input type="range" x-model.number="cumulativePriorRevenue" min="0" max="600000000" step="5000000" class="w-full accent-rose-500">
                        <div class="flex justify-between text-[10px] text-slate-500 mt-1">
                            <span>Rp 0</span>
                            <span class="text-emerald-400 font-bold">Batas Bebas: Rp 500 Juta</span>
                            <span>Rp 600 Juta+</span>
                        </div>
                    </div>

                    <!-- Omzet Bulan Ini -->
                    <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800">
                        <div class="flex justify-between items-center mb-1">
                            <label class="text-xs font-bold text-white uppercase tracking-wide">Omzet Penjualan Bulan Ini</label>
                            <span class="font-mono text-sm font-bold text-rose-400">Rp <span x-text="Number(currentMonthRevenue).toLocaleString('id-ID')"></span></span>
                        </div>
                        <input type="range" x-model.number="currentMonthRevenue" min="1000000" max="150000000" step="1000000" class="w-full accent-rose-500">
                    </div>
                </div>

                <!-- Right: Summary -->
                <div class="lg:col-span-5 p-6 rounded-2xl bg-slate-950/90 border border-rose-500/30 flex flex-col justify-between">
                    <div class="space-y-4">
                        <span class="text-xs font-bold uppercase tracking-wider text-rose-400 block">Kewajiban Setor PPh Final Bulan Ini</span>

                        <div class="p-4 rounded-xl bg-rose-950/40 border border-rose-500/30">
                            <span class="text-[10px] font-bold uppercase text-slate-400 block">Total Pajak Terutang (0.5%)</span>
                            <div class="text-3xl font-black text-rose-400 font-mono mt-1">
                                Rp <span x-text="taxDue.toLocaleString('id-ID')"></span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-2">
                                <span x-show="taxDue === 0" class="text-emerald-400 font-bold">Alhamdulillah! Omzet Anda masih dalam batas bebas pajak PP 55/2022.</span>
                                <span x-show="taxDue > 0">Disetor paling lambat tanggal 15 bulan berikutnya melalui kode billing 411128-420.</span>
                            </p>
                        </div>

                        <div class="space-y-2 text-xs border-t border-slate-800 pt-3">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Omzet Kena Pajak (DPP):</span>
                                <span class="font-mono font-bold text-white">Rp <span x-text="taxableRevenue.toLocaleString('id-ID')"></span></span>
                            </div>
                            <div class="flex justify-between" x-show="taxpayerType === 'individual'">
                                <span class="text-slate-400">Total Akumulasi Omzet Tahun Ini:</span>
                                <span class="font-mono text-slate-300">Rp <span x-text="newCumulative.toLocaleString('id-ID')"></span></span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-800 mt-6">
                        <a href="{{ route('register') }}" class="w-full glow-btn py-3 rounded-xl text-white font-bold text-xs flex items-center justify-center gap-2">
                            <span>Buka Akun Kasir Otomatis</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
