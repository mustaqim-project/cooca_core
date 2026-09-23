@extends('layouts.public_marketing')

@section('title', 'Kalkulator Gaji Karyawan UMKM & Upah Harian Online | COOCA')
@section('description', 'Kalkulator penghitungan gaji staf dan karyawan UMKM online. Hitung gaji pokok harian/bulanan, tunjangan makan, uang lembur, dan potongan kasbon secara transparan.')
@section('og_title', 'Kalkulator Gaji Karyawan UMKM & Upah Harian Online | COOCA')
@section('og_description', 'Kelola perhitungan payroll karyawan gerai Anda secara rapi: gaji pokok, tunjangan, dan lembur.')
@section('canonical', route('kalkulator.gaji-karyawan'))
@section('og_type', 'website')
@section('keywords', 'kalkulator gaji karyawan, hitung upah harian umkm, rumus lembur karyawan toko, payroll sederhana excel, slip gaji online')

@push('seo')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "WebApplication",
  "name": "Kalkulator Gaji Karyawan & Payroll UMKM COOCA",
  "url": "{{ route('kalkulator.gaji-karyawan') }}",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "All",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "IDR"
  },
  "description": "Kalkulator penghitungan upah harian, bulanan, tunjangan makan, dan lembur staf toko/kafe."
}
</script>
@endpush

@section('content')
    <div x-data="{
        refreshIcons() {
            this.$nextTick(() => {
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });
        }
    }" x-init="refreshIcons()"
    class="w-full font-sans antialiased bg-[#F2F2F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] transition-colors pt-8 pb-24">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-xs text-[#6E6E73] dark:text-[#86868B]">
                <a href="{{ route('landing') }}"
                    class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Beranda</a>
                <span>/</span>
                <a href="{{ route('kalkulator.index') }}"
                    class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Kalkulator</a>
                <span>/</span>
                <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold">Kalkulator Gaji Karyawan</span>
            </nav>

            <!-- ═══ HERO SECTION (Mandatory 2-Grid Layout) ═══ -->
            <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

                <!-- KIRI: Headline & Penjelasan (7 Cols) -->
                <div class="lg:col-span-7 space-y-5 text-left">
                    <div class="space-y-2">
                        <p
                            class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                            Payroll &amp; Upah Staf UMKM
                        </p>
                        <h1
                            class="text-3xl sm:text-4xl md:text-5xl lg:text-[3.25rem] font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-[1.15] text-balance break-words">
                            Kalkulator Gaji &amp; <span class="text-[#007AFF] dark:text-[#0A84FF]">Upah Karyawan</span>
                        </h1>
                    </div>

                    <p class="text-base sm:text-lg text-[#48484A] dark:text-[#AEAEB2] leading-relaxed max-w-xl font-normal text-pretty break-words">
                        Hitung rincian gaji bersih (take-home pay) staf toko, barista kafe, kasir, atau montir bengkel Anda
                        secara adil, rapi, dan bebas sengketa.
                    </p>

                    <!-- Reassurance Points for UMKM 40-65 -->
                    <div
                        class="pt-1 flex flex-wrap items-center gap-y-2 gap-x-5 text-xs text-[#6E6E73] dark:text-[#86868B]">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
                            <span>Gaji Pokok Harian / Bulanan</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
                            <span>Hitung Lembur &amp; Tunjangan Kehadiran</span>
                        </div>
                    </div>
                </div>

                <!-- KANAN: Visual Formula Preview Card (5 Cols) -->
                <div class="lg:col-span-5">
                    <div
                        class="bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[24px] shadow-sm p-5 sm:p-6 space-y-4">
                        <div
                            class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#FF5F56] border border-black/10"></span>
                                <span class="w-3 h-3 rounded-full bg-[#FFBD2E] border border-black/10"></span>
                                <span class="w-3 h-3 rounded-full bg-[#27C93F] border border-black/10"></span>
                            </div>
                            <span class="text-xs font-semibold text-[#8E8E93] dark:text-[#98989D]">Komponen
                                Penggajian</span>
                            <div class="w-6"></div>
                        </div>

                        <div class="space-y-2.5 text-xs text-[#48484A] dark:text-[#AEAEB2]">
                            <div
                                class="flex items-center justify-between p-2.5 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E]">
                                <span class="font-medium">Gaji Pokok Dasar</span>
                                <span class="font-mono font-bold text-[#007AFF] dark:text-[#0A84FF]">Upah Tetap</span>
                            </div>
                            <div
                                class="flex items-center justify-between p-2.5 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E]">
                                <span class="font-medium">Tunjangan Makan &amp; Transport</span>
                                <span class="font-mono font-bold text-[#34C759] dark:text-[#30D158]">+ Tunjangan</span>
                            </div>
                            <div
                                class="flex items-center justify-between p-2.5 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E]">
                                <span class="font-medium">Uang Lembur &amp; Bonus Target</span>
                                <span class="font-mono font-bold text-[#FF9500] dark:text-[#FF9F0A]">+ Lembur</span>
                            </div>
                        </div>

                        <div
                            class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between text-xs">
                            <span class="text-[#6E6E73] dark:text-[#86868B]">Total Diterima:</span>
                            <span class="font-bold text-[#34C759] dark:text-[#30D158]">= Take Home Pay</span>
                        </div>
                    </div>
                </div>

            </section>

            <!-- ═══ CALCULATOR INTERACTIVE APP ═══ -->
            <div class="bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-sm p-6 sm:p-8 rounded-[24px]"
                x-data="{
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
                        <div
                            class="inline-flex p-1 bg-black/[0.04] dark:bg-white/[0.06] rounded-[12px] border border-black/[0.04] dark:border-white/[0.06] text-xs font-semibold w-full sm:w-auto">
                            <button type="button" @click="wageType = 'monthly'; baseWage = 2500000"
                                :class="wageType === 'monthly' ?
                                    'bg-white dark:bg-[#1C1C1E] text-[#1D1D1F] dark:text-[#F5F5F7] shadow-sm' :
                                    'text-[#6E6E73] dark:text-[#86868B]'"
                                class="px-5 py-1.5 rounded-[8px] transition-all">
                                Gaji Pokok Bulanan
                            </button>
                            <button type="button" @click="wageType = 'daily'; baseWage = 90000"
                                :class="wageType === 'daily' ?
                                    'bg-white dark:bg-[#1C1C1E] text-[#1D1D1F] dark:text-[#F5F5F7] shadow-sm' :
                                    'text-[#6E6E73] dark:text-[#86868B]'"
                                class="px-5 py-1.5 rounded-[8px] transition-all">
                                Upah Harian Lepas
                            </button>
                        </div>

                        <!-- Nominal Pokok -->
                        <div
                            class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                            <div class="flex justify-between items-center">
                                <label class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] uppercase tracking-wide"
                                    x-text="wageType === 'monthly' ? 'Gaji Pokok Bulanan' : 'Upah Pokok per Hari'"></label>
                                <span class="font-mono text-sm font-bold text-[#007AFF] dark:text-[#0A84FF]">Rp <span
                                        x-text="Number(baseWage).toLocaleString('id-ID')"></span></span>
                            </div>
                            <input type="range" x-model.number="baseWage" :min="wageType === 'monthly' ? 1000000 : 50000"
                                :max="wageType === 'monthly' ? 10000000 : 300000"
                                :step="wageType === 'monthly' ? 100000 : 5000"
                                class="w-full accent-[#007AFF] cursor-pointer">
                        </div>

                        <!-- Jumlah Hari Kerja (khusus harian) -->
                        <div x-show="wageType === 'daily'"
                            class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                            <div class="flex justify-between items-center">
                                <label
                                    class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] uppercase tracking-wide">Jumlah
                                    Hari Masuk Kerja</label>
                                <span class="font-mono text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]"><span
                                        x-text="workingDays"></span> Hari</span>
                            </div>
                            <input type="range" x-model.number="workingDays" min="1" max="31" step="1"
                                class="w-full accent-[#007AFF] cursor-pointer">
                        </div>

                        <!-- Tunjangan & Potongan -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div
                                class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                                <label
                                    class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] uppercase tracking-wide block">Tunjangan
                                    Makan &amp; Transport</label>
                                <input type="number" x-model.number="allowance"
                                    class="w-full h-11 px-3.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[#1D1D1F] dark:text-[#F5F5F7] font-mono text-[16px] sm:text-sm focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all">
                            </div>
                            <div
                                class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                                <label
                                    class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] uppercase tracking-wide block">Potongan
                                    Kasbon / Absen</label>
                                <input type="number" x-model.number="deductions"
                                    class="w-full h-11 px-3.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[#1D1D1F] dark:text-[#F5F5F7] font-mono text-[16px] sm:text-sm focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all">
                            </div>
                        </div>

                        <!-- Lembur -->
                        <div
                            class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                            <div class="flex justify-between items-center">
                                <label
                                    class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] uppercase tracking-wide">Uang
                                    Lembur (<span x-text="overtimeHours"></span> Jam × Rp <span
                                        x-text="Number(overtimeRate).toLocaleString('id-ID')"></span>)</label>
                                <span class="font-mono text-sm font-bold text-[#007AFF] dark:text-[#0A84FF]">Rp <span
                                        x-text="totalOvertime.toLocaleString('id-ID')"></span></span>
                            </div>
                            <input type="range" x-model.number="overtimeHours" min="0" max="60"
                                step="1" class="w-full accent-[#007AFF] cursor-pointer">
                        </div>
                    </div>

                    <!-- Right: Sticky Bento Output Card (5 Kolom) -->
                    <div class="lg:col-span-5 sticky top-24 space-y-5">
                        <div
                            class="glass-card p-6 sm:p-7 rounded-[26px] space-y-5 bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                            <span
                                class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF] block">Estimasi
                                Slip Gaji Karyawan</span>

                            <div class="space-y-2 text-xs border-b border-black/[0.04] dark:border-white/[0.06] pb-3">
                                <div class="flex justify-between text-[#6E6E73] dark:text-[#86868B]">
                                    <span>Upah Pokok:</span>
                                    <span class="font-mono font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Rp <span
                                            x-text="totalBase.toLocaleString('id-ID')"></span></span>
                                </div>
                                <div class="flex justify-between text-[#34C759] dark:text-[#30D158]">
                                    <span>+ Tunjangan:</span>
                                    <span class="font-mono">Rp <span
                                            x-text="Number(allowance).toLocaleString('id-ID')"></span></span>
                                </div>
                                <div class="flex justify-between text-[#34C759] dark:text-[#30D158]">
                                    <span>+ Upah Lembur:</span>
                                    <span class="font-mono">Rp <span
                                            x-text="totalOvertime.toLocaleString('id-ID')"></span></span>
                                </div>
                                <div
                                    class="flex justify-between text-[#FF3B30] dark:text-[#FF453A] pt-1.5 border-t border-black/[0.04] dark:border-white/[0.06]">
                                    <span>- Potongan Kasbon:</span>
                                    <span class="font-mono">Rp <span
                                            x-text="Number(deductions).toLocaleString('id-ID')"></span></span>
                                </div>
                            </div>

                            <!-- Take Home Pay -->
                            <div class="p-4 rounded-[18px] bg-[#34C759]/10 border border-[#34C759]/20">
                                <span class="text-xs font-bold uppercase text-[#6E6E73] dark:text-[#86868B] block">Total
                                    Diterima Karyawan (Take Home Pay)</span>
                                <div class="text-3xl font-black text-[#34C759] dark:text-[#30D158] font-mono mt-1">
                                    Rp <span x-text="netSalary.toLocaleString('id-ID')"></span>
                                </div>
                            </div>

                            <div class="pt-2">
                                <a href="{{ route('register') }}"
                                    class="w-full glow-btn py-3.5 rounded-[14px] text-white font-semibold text-xs flex items-center justify-center gap-2 active:scale-[0.98] transition-transform">
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
