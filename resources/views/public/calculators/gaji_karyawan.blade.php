@extends('layouts.public_marketing')

@section('title', 'Kalkulator Gaji Karyawan UMKM & Upah Harian Online | Cooca UMKM')
@section('description', 'Kalkulator penghitungan gaji staf dan karyawan UMKM online. Hitung gaji pokok harian/bulanan, tunjangan makan, uang lembur, dan potongan kasbon secara transparan.')
@section('keywords', 'kalkulator gaji karyawan, hitung upah harian umkm, rumus lembur karyawan toko, payroll sederhana excel, slip gaji online')

@section('content')
<div class="pt-10 pb-20">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-slate-400 mb-6">
            <a href="{{ route('landing') }}" class="hover:text-white">Beranda</a>
            <span>/</span>
            <a href="{{ route('kalkulator.index') }}" class="hover:text-white">Kalkulator</a>
            <span>/</span>
            <span class="text-purple-400 font-semibold">Kalkulator Gaji Karyawan</span>
        </nav>

        <!-- Header -->
        <div class="text-center max-w-2xl mx-auto mb-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-500/10 border border-purple-500/20 text-purple-400 font-bold text-xs mb-3">
                <i data-lucide="users" class="w-3.5 h-3.5"></i>
                <span>Payroll &amp; Upah UMKM</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight">Kalkulator Gaji &amp; Upah Karyawan</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-2">
                Hitung rincian take-home pay staf toko, barista kafe, atau montir bengkel Anda secara adil dan transparan.
            </p>
        </div>

        <!-- Calculator Interactive App -->
        <div class="glass-card p-6 sm:p-8 rounded-3xl mb-12" x-data="{
            wageType: 'monthly', // 'monthly' or 'daily'
            baseWage: 2500000,
            workingDays: 26,
            allowance: 300000,   // Uang makan / transport
            overtimeHours: 8,    // Jam lembur
            overtimeRate: 20000, // Tarif lembur per jam
            deductions: 100000,  // Kasbon / potongan absen

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
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left: Inputs -->
                <div class="lg:col-span-7 space-y-5">
                    <!-- Tipe Upah -->
                    <div class="flex items-center p-1 bg-slate-950 rounded-xl border border-slate-800 text-xs">
                        <button type="button" @click="wageType = 'monthly'; baseWage = 2500000" :class="wageType === 'monthly' ? 'bg-purple-600 text-white shadow-lg' : 'text-slate-400'" class="w-1/2 py-2 rounded-lg font-bold transition-all">
                            Gaji Pokok Bulanan
                        </button>
                        <button type="button" @click="wageType = 'daily'; baseWage = 90000" :class="wageType === 'daily' ? 'bg-purple-600 text-white shadow-lg' : 'text-slate-400'" class="w-1/2 py-2 rounded-lg font-bold transition-all">
                            Upah Harian Lepas
                        </button>
                    </div>

                    <!-- Nominal Pokok -->
                    <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800">
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-xs font-bold text-slate-300 uppercase tracking-wide" x-text="wageType === 'monthly' ? 'Gaji Pokok Bulanan' : 'Upah Pokok per Hari'"></label>
                            <span class="font-mono text-sm font-bold text-white">Rp <span x-text="Number(baseWage).toLocaleString('id-ID')"></span></span>
                        </div>
                        <input type="range" x-model.number="baseWage" :min="wageType === 'monthly' ? 1000000 : 50000" :max="wageType === 'monthly' ? 10000000 : 300000" :step="wageType === 'monthly' ? 100000 : 5000" class="w-full accent-purple-500">
                    </div>

                    <!-- Jumlah Hari Kerja (khusus harian) -->
                    <div x-show="wageType === 'daily'" class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800">
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-xs font-bold text-slate-300 uppercase tracking-wide">Jumlah Hari Masuk Kerja</label>
                            <span class="font-mono text-sm font-bold text-white"><span x-text="workingDays"></span> Hari</span>
                        </div>
                        <input type="range" x-model.number="workingDays" min="1" max="31" step="1" class="w-full accent-purple-500">
                    </div>

                    <!-- Tunjangan & Lembur -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800">
                            <label class="text-xs font-bold text-slate-300 uppercase tracking-wide block mb-1">Tunjangan Makan &amp; Transport</label>
                            <input type="number" x-model.number="allowance" class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white font-mono text-sm focus:outline-none">
                        </div>
                        <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800">
                            <label class="text-xs font-bold text-slate-300 uppercase tracking-wide block mb-1">Potongan Kasbon / Absen</label>
                            <input type="number" x-model.number="deductions" class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white font-mono text-sm focus:outline-none">
                        </div>
                    </div>

                    <!-- Lembur -->
                    <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800">
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-xs font-bold text-slate-300 uppercase tracking-wide">Uang Lembur (<span x-text="overtimeHours"></span> Jam × Rp <span x-text="Number(overtimeRate).toLocaleString('id-ID')"></span>)</label>
                            <span class="font-mono text-sm font-bold text-purple-400">Rp <span x-text="totalOvertime.toLocaleString('id-ID')"></span></span>
                        </div>
                        <input type="range" x-model.number="overtimeHours" min="0" max="60" step="1" class="w-full accent-purple-500">
                    </div>
                </div>

                <!-- Right: Slip Gaji Ringkas -->
                <div class="lg:col-span-5 p-6 rounded-2xl bg-slate-950/90 border border-purple-500/30 flex flex-col justify-between">
                    <div class="space-y-4">
                        <span class="text-xs font-bold uppercase tracking-wider text-purple-400 block">Estimasi Slip Gaji Karyawan</span>

                        <div class="space-y-2 text-xs border-b border-slate-800 pb-4">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Upah Pokok:</span>
                                <span class="font-mono font-bold text-white">Rp <span x-text="totalBase.toLocaleString('id-ID')"></span></span>
                            </div>
                            <div class="flex justify-between text-emerald-400">
                                <span>+ Tunjangan:</span>
                                <span class="font-mono">Rp <span x-text="Number(allowance).toLocaleString('id-ID')"></span></span>
                            </div>
                            <div class="flex justify-between text-emerald-400">
                                <span>+ Upah Lembur:</span>
                                <span class="font-mono">Rp <span x-text="totalOvertime.toLocaleString('id-ID')"></span></span>
                            </div>
                            <div class="flex justify-between text-rose-400 pt-2 border-t border-slate-800">
                                <span>- Potongan Kasbon:</span>
                                <span class="font-mono">Rp <span x-text="Number(deductions).toLocaleString('id-ID')"></span></span>
                            </div>
                        </div>

                        <!-- Take Home Pay -->
                        <div class="p-4 rounded-xl bg-purple-950/40 border border-purple-500/30">
                            <span class="text-[10px] font-bold uppercase text-slate-400 block">Total Diterima Karyawan (Take Home Pay)</span>
                            <div class="text-3xl font-black text-emerald-400 font-mono mt-1">
                                Rp <span x-text="netSalary.toLocaleString('id-ID')"></span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-800 mt-6">
                        <a href="{{ route('register') }}" class="w-full glow-btn py-3 rounded-xl text-white font-bold text-xs flex items-center justify-center gap-2">
                            <span>Mulai Kelola Karyawan Gratis</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
