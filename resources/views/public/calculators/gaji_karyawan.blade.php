@extends('layouts.public_marketing')

@section('title', 'Kalkulator Gaji Karyawan UMKM & Upah Harian Online | Cooca UMKM')
@section('description', 'Kalkulator penghitungan gaji staf dan karyawan UMKM online. Hitung gaji pokok harian/bulanan, tunjangan makan, uang lembur, dan potongan kasbon secara transparan.')
@section('keywords', 'kalkulator gaji karyawan, hitung upah harian umkm, rumus lembur karyawan toko, payroll sederhana excel, slip gaji online')

@section('content')
<div class="pt-8 pb-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-[#6E6E73] dark:text-[#86868B]">
            <a href="{{ route('landing') }}" class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Beranda</a>
            <span>/</span>
            <a href="{{ route('kalkulator.index') }}" class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Kalkulator</a>
            <span>/</span>
            <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold">Kalkulator Gaji Karyawan</span>
        </nav>

        <!-- Header -->
        <div class="text-center max-w-2xl mx-auto space-y-3">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-bold text-xs">
                <i data-lucide="users" class="w-3.5 h-3.5"></i>
                <span>Payroll &amp; Upah Staf UMKM</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">Kalkulator Gaji &amp; Upah Karyawan</h1>
            <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] max-w-xl mx-auto leading-relaxed">
                Hitung rincian take-home pay staf toko, barista kafe, atau montir bengkel Anda secara adil dan transparan.
            </p>
        </div>

        <!-- Calculator Interactive App (2-Column Bento System) -->
        <div class="glass-card p-6 sm:p-8 rounded-[28px]" x-data="{
            wageType: 'monthly',
            baseWage: 2500000,
            workingDays: 26,
            allowance: 300000,
            overtimeHours: 8,
            overtimeRate: 20000,
            deductions: 100000,

            get totalBase() {
                if (this.wageType === 'daily') {
                    return (parseFloat(this.baseWage) || 0) * (parseInt(this.workingDays) || 0);
                }
                return parseFloat(this.baseWage) || 0;
            },
            get totalOvertime() {
                return (parseFloat(this.overtimeHours) || 0) * (parseFloat(this.overtimeRate) || 0);
            },
            get grossSalary() {
                return this.totalBase + (parseFloat(this.allowance) || 0) + this.totalOvertime;
            },
            get netSalary() {
                return Math.max(0, this.grossSalary - (parseFloat(this.deductions) || 0));
            }
        }">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- Left: Apple Inset Input Controls (7 Kolom) -->
                <div class="lg:col-span-7 space-y-4">
                    <!-- Segmented Switcher -->
                    <div class="inline-flex p-1 bg-black/[0.04] dark:bg-white/[0.06] rounded-full border border-black/[0.04] dark:border-white/[0.06] text-xs font-semibold w-full sm:w-auto">
                        <button type="button" @click="wageType = 'monthly'; baseWage = 2500000" :class="wageType === 'monthly' ? 'bg-white dark:bg-[#1C1C1E] text-[#1D1D1F] dark:text-[#F5F5F7] shadow-sm' : 'text-[#6E6E73] dark:text-[#86868B]'" class="px-5 py-1.5 rounded-full transition-all">
                            Gaji Pokok Bulanan
                        </button>
                        <button type="button" @click="wageType = 'daily'; baseWage = 90000" :class="wageType === 'daily' ? 'bg-white dark:bg-[#1C1C1E] text-[#1D1D1F] dark:text-[#F5F5F7] shadow-sm' : 'text-[#6E6E73] dark:text-[#86868B]'" class="px-5 py-1.5 rounded-full transition-all">
                            Upah Harian Lepas
                        </button>
                    </div>

                    <!-- Nominal Pokok -->
                    <div class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] uppercase tracking-wide" x-text="wageType === 'monthly' ? 'Gaji Pokok Bulanan' : 'Upah Pokok per Hari'"></label>
                            <span class="font-mono text-sm font-bold text-[#007AFF] dark:text-[#0A84FF]">Rp <span x-text="Number(baseWage).toLocaleString('id-ID')"></span></span>
                        </div>
                        <input type="range" x-model.number="baseWage" :min="wageType === 'monthly' ? 1000000 : 50000" :max="wageType === 'monthly' ? 10000000 : 300000" :step="wageType === 'monthly' ? 100000 : 5000" class="w-full accent-[#007AFF] cursor-pointer">
                    </div>

                    <!-- Jumlah Hari Kerja (khusus harian) -->
                    <div x-show="wageType === 'daily'" class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] uppercase tracking-wide">Jumlah Hari Masuk Kerja</label>
                            <span class="font-mono text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]"><span x-text="workingDays"></span> Hari</span>
                        </div>
                        <input type="range" x-model.number="workingDays" min="1" max="31" step="1" class="w-full accent-[#007AFF] cursor-pointer">
                    </div>

                    <!-- Tunjangan & Potongan -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                            <label class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] uppercase tracking-wide block">Tunjangan Makan &amp; Transport</label>
                            <input type="number" x-model.number="allowance" class="w-full h-11 px-3.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[#1D1D1F] dark:text-[#F5F5F7] font-mono text-sm focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all">
                        </div>
                        <div class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                            <label class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] uppercase tracking-wide block">Potongan Kasbon / Absen</label>
                            <input type="number" x-model.number="deductions" class="w-full h-11 px-3.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[#1D1D1F] dark:text-[#F5F5F7] font-mono text-sm focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all">
                        </div>
                    </div>

                    <!-- Lembur -->
                    <div class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] uppercase tracking-wide">Uang Lembur (<span x-text="overtimeHours"></span> Jam × Rp <span x-text="Number(overtimeRate).toLocaleString('id-ID')"></span>)</label>
                            <span class="font-mono text-sm font-bold text-[#007AFF] dark:text-[#0A84FF]">Rp <span x-text="totalOvertime.toLocaleString('id-ID')"></span></span>
                        </div>
                        <input type="range" x-model.number="overtimeHours" min="0" max="60" step="1" class="w-full accent-[#007AFF] cursor-pointer">
                    </div>
                </div>

                <!-- Right: Sticky Bento Output Card (5 Kolom) -->
                <div class="lg:col-span-5 sticky top-24 space-y-5">
                    <div class="glass-card p-6 sm:p-7 rounded-[26px] space-y-5 bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                        <span class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF] block">Estimasi Slip Gaji Karyawan</span>

                        <div class="space-y-2 text-xs border-b border-black/[0.04] dark:border-white/[0.06] pb-3">
                            <div class="flex justify-between text-[#6E6E73] dark:text-[#86868B]">
                                <span>Upah Pokok:</span>
                                <span class="font-mono font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Rp <span x-text="totalBase.toLocaleString('id-ID')"></span></span>
                            </div>
                            <div class="flex justify-between text-[#34C759] dark:text-[#30D158]">
                                <span>+ Tunjangan:</span>
                                <span class="font-mono">Rp <span x-text="Number(allowance).toLocaleString('id-ID')"></span></span>
                            </div>
                            <div class="flex justify-between text-[#34C759] dark:text-[#30D158]">
                                <span>+ Upah Lembur:</span>
                                <span class="font-mono">Rp <span x-text="totalOvertime.toLocaleString('id-ID')"></span></span>
                            </div>
                            <div class="flex justify-between text-[#FF3B30] dark:text-[#FF453A] pt-1.5 border-t border-black/[0.04] dark:border-white/[0.06]">
                                <span>- Potongan Kasbon:</span>
                                <span class="font-mono">Rp <span x-text="Number(deductions).toLocaleString('id-ID')"></span></span>
                            </div>
                        </div>

                        <!-- Take Home Pay -->
                        <div class="p-4 rounded-[18px] bg-[#34C759]/10 border border-[#34C759]/20">
                            <span class="text-[10px] font-bold uppercase text-[#6E6E73] dark:text-[#86868B] block">Total Diterima Karyawan (Take Home Pay)</span>
                            <div class="text-3xl font-black text-[#34C759] dark:text-[#30D158] font-mono mt-1">
                                Rp <span x-text="netSalary.toLocaleString('id-ID')"></span>
                            </div>
                        </div>

                        <div class="pt-2">
                            <a href="{{ route('register') }}" class="w-full glow-btn py-3.5 rounded-[14px] text-white font-semibold text-xs flex items-center justify-center gap-2 active:scale-[0.98] transition-transform">
                                <span>Mulai Kelola Karyawan Gratis</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
