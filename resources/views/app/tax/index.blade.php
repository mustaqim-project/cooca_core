@extends('layouts.app', [
    'title' => 'Pajak & Kepatuhan Usaha',
    'headerTitle' => 'Kepatuhan Pajak & Hasil Penjualan',
    'headerSubtitle' => 'Tax Compliance Engine: Kalkulasi PPh Laba Bersih Usaha (UU HPP / Pasal 31E & 17), PPh Final UMKM 0.5% (PP 55/2022), PPh 21 TER, BPJS, dan THR'
])

@section('content')
<div class="space-y-6 pb-28 lg:pb-12" x-data="{
    activeSimTab: 'net_income',
    taxpayerType: '{{ $isIndividual ? 'individual' : 'corporate' }}',
    ptkpStatus: '{{ $ptkpStatus }}',
    currentYear: {{ $currentYear }},

    // Modal Control States
    showExportModal: false,
    showBracketModal: false,
    showBpjsModal: false,
    showNormaModal: false,

    // Simulator PPh Laba Bersih & Hasil Penjualan State
    netIncomeInput: {
        gross_revenue: {{ (float) ($netIncomeSummary['total_revenue_year'] > 0 ? $netIncomeSummary['total_revenue_year'] : 50000000) }},
        cogs: {{ (float) ($netIncomeSummary['total_cogs_year'] > 0 ? $netIncomeSummary['total_cogs_year'] : 25000000) }},
        operating_expenses: {{ (float) ($netIncomeSummary['total_expenses_year'] > 0 ? $netIncomeSummary['total_expenses_year'] : 12000000) }},
        is_corporate: {{ $isIndividual ? 'false' : 'true' }},
        ptkp_status: '{{ $ptkpStatus }}',
        nppn_rate: 0
    },
    netIncomeResult: null,
    netIncomeLoading: false,

    // Simulator UMKM State
    umkmInput: {
        monthly_revenue: 35000000,
        prior_cumulative: {{ (float) ($umkmSummary['total_revenue_year'] ?? 0) }},
        is_individual: {{ $isIndividual ? 'true' : 'false' }}
    },
    umkmResult: null,
    umkmLoading: false,

    // Simulator PPh 21 State
    pph21Input: {
        calc_type: 'monthly_ter',
        gross_wage: 6500000,
        ptkp_status: 'TK/0',
        cumulative_wage: 6500000,
        annual_deductions: 2400000,
        tax_paid_before: 150000
    },
    pph21Result: null,
    pph21Loading: false,

    // Simulator Payroll & THR State
    payrollInput: {
        employment_type: 'permanent',
        base_salary: 5000000,
        fixed_allowances: 1000000,
        variable_allowances: 500000,
        overtime_pay: 0,
        commissions: 0,
        loan_deduction: 250000,
        other_deductions: 0,
        ptkp_status: 'TK/0',
        bpjs_tk_enabled: true,
        bpjs_kes_enabled: true,
        include_thr: true,
        join_date: '{{ date('Y') }}-01-15',
        daily_rate: 150000,
        days_worked: 25,
        prior_cumulative: 0
    },
    payrollResult: null,
    payrollLoading: false,

    // Simulator Sales Tax State
    salesTaxInput: {
        subtotal: 250000,
        discount: 25000,
        service_charge_rate: 0.05,
        tax_type: 'pb1',
        is_inclusive: false
    },
    salesTaxResult: null,
    salesTaxLoading: false,

    formatRupiah(val) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
    },

    async runNetIncomeSim() {
        this.netIncomeLoading = true;
        try {
            const res = await fetch('{{ route('tax.simulate.net_income') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(this.netIncomeInput)
            });
            const data = await res.json();
            if (data.success) {
                this.netIncomeResult = data.data;
                this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
            }
        } catch (e) {
            console.error(e);
        } finally {
            this.netIncomeLoading = false;
        }
    },

    async runUmkmSim() {
        this.umkmLoading = true;
        try {
            const res = await fetch('{{ route('tax.simulate.umkm') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(this.umkmInput)
            });
            const data = await res.json();
            if (data.success) {
                this.umkmResult = data.data;
                this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
            }
        } catch (e) {
            console.error(e);
        } finally {
            this.umkmLoading = false;
        }
    },

    async runPph21Sim() {
        this.pph21Loading = true;
        try {
            const res = await fetch('{{ route('tax.simulate.pph21') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(this.pph21Input)
            });
            const data = await res.json();
            if (data.success) {
                this.pph21Result = data.data;
                this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
            }
        } catch (e) {
            console.error(e);
        } finally {
            this.pph21Loading = false;
        }
    },

    async runPayrollSim() {
        this.payrollLoading = true;
        try {
            const res = await fetch('{{ route('tax.simulate.payroll') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(this.payrollInput)
            });
            const data = await res.json();
            if (data.success) {
                this.payrollResult = data.data;
                this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
            }
        } catch (e) {
            console.error(e);
        } finally {
            this.payrollLoading = false;
        }
    },

    async runSalesTaxSim() {
        this.salesTaxLoading = true;
        try {
            const res = await fetch('{{ route('tax.simulate.sales') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(this.salesTaxInput)
            });
            const data = await res.json();
            if (data.success) {
                this.salesTaxResult = data.data;
                this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
            }
        } catch (e) {
            console.error(e);
        } finally {
            this.salesTaxLoading = false;
        }
    },

    init() {
        this.runNetIncomeSim();
        this.runUmkmSim();
        this.runPph21Sim();
        this.runPayrollSim();
        this.runSalesTaxSim();
    }
}">

    {{-- BENTO EXECUTIVE SUMMARY HEADER (Apple HIG Bento Grid) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Peredaran Bruto / Omzet Riil --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Omzet Riil ({{ $currentYear }})</span>
                <span class="w-8 h-8 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                    {{ 'Rp ' . number_format($netIncomeSummary['total_revenue_year'], 0, ',', '.') }}
                </div>
                <div class="mt-1 flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                    <i data-lucide="calculator" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span>Tersinkron POS, Faktur & Toko Online</span>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                <span class="text-slate-500 dark:text-slate-400">HPP Aktual Terjual</span>
                <span class="font-semibold text-slate-700 dark:text-slate-300">
                    {{ 'Rp ' . number_format($netIncomeSummary['total_cogs_year'], 0, ',', '.') }}
                </span>
            </div>
        </div>

        {{-- Card 2: Laba Operasional Riil --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Laba Bersih Usaha</span>
                <span class="w-8 h-8 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-[#34C759] flex items-center justify-center">
                    <i data-lucide="pie-chart" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold {{ $netIncomeSummary['total_net_income_year'] < 0 ? 'text-[#FF3B30]' : 'text-slate-900 dark:text-white' }} tracking-tight">
                    {{ 'Rp ' . number_format($netIncomeSummary['total_net_income_year'], 0, ',', '.') }}
                </div>
                <div class="mt-1 flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                    <i data-lucide="wallet" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span>Beban Kas: Rp {{ number_format($netIncomeSummary['total_expenses_year'], 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                <span class="text-slate-500 dark:text-slate-400">Status Fiskal</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $netIncomeSummary['total_net_income_year'] <= 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300' }}">
                    {{ $netIncomeSummary['total_net_income_year'] <= 0 ? 'Rugi Operasional' : 'Laba Positif' }}
                </span>
            </div>
        </div>

        {{-- Card 3: Estimasi PPh Terutang Tahunan --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Estimasi PPh Terutang</span>
                <span class="w-8 h-8 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-[#5856D6] flex items-center justify-center">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                    {{ 'Rp ' . number_format($netIncomeSummary['total_net_tax_year'], 0, ',', '.') }}
                </div>
                <div class="mt-1 flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                    <i data-lucide="landmark" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span>{{ $isIndividual ? 'Wajib Pajak Orang Pribadi (' . $ptkpStatus . ')' : 'Wajib Pajak Badan (Pasal 31E)' }}</span>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                <span class="text-slate-500 dark:text-slate-400">PPh Final 0.5% (PP 55)</span>
                <span class="font-semibold text-slate-700 dark:text-slate-300">
                    {{ 'Rp ' . number_format($netIncomeSummary['total_umkm_final_year'], 0, ',', '.') }}
                </span>
            </div>
        </div>

        {{-- Card 4: Tax Optimization Engine (Apple Highlight Bento) --}}
        <div class="bg-slate-900 dark:bg-slate-800 rounded-3xl p-5 text-white shadow-sm relative overflow-hidden flex flex-col justify-between border border-slate-800 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#34C759] animate-pulse"></span>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-300">Tax Optimization Engine</span>
                </div>
                <span class="w-8 h-8 rounded-2xl bg-white/10 text-amber-300 flex items-center justify-center">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-base font-bold text-white tracking-tight line-clamp-1">
                    {{ $netIncomeSummary['annual_calculation']['comparison']['recommendation_label'] ?? 'Analisis Skema Fiskal' }}
                </div>
                <p class="mt-1 text-xs text-slate-300 line-clamp-2 leading-relaxed">
                    {{ $netIncomeSummary['annual_calculation']['comparison']['rationale'] ?? 'Perbandingan otomatis antara skema pembukuan laba bersih dengan PPh Final 0.5%.' }}
                </p>
            </div>
            <div class="mt-3 pt-3 border-t border-white/10 flex items-center justify-between text-xs">
                <span class="text-slate-300">Potensi Efisiensi</span>
                <span class="font-bold text-[#34C759]">
                    {{ 'Rp ' . number_format((float) ($netIncomeSummary['annual_calculation']['comparison']['tax_savings'] ?? 0), 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

    {{-- FILTER CONTROLS & EXPORT ACTION BAR (Apple Segmented Bar) --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('tax.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            {{-- Tahun Pajak --}}
            <div class="flex items-center gap-2">
                <label for="year-select" class="text-xs font-semibold text-slate-600 dark:text-slate-400">Tahun:</label>
                <select id="year-select" name="year" onchange="this.form.submit()" class="h-10 text-sm font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white px-3 focus:ring-2 focus:ring-[#007AFF] focus:border-transparent outline-none">
                    @for($y = (int) date('Y'); $y >= (int) date('Y') - 4; $y--)
                        <option value="{{ $y }}" {{ $currentYear === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            {{-- Jenis Wajib Pajak --}}
            <div class="flex items-center gap-2">
                <label for="taxpayer-select" class="text-xs font-semibold text-slate-600 dark:text-slate-400">Subjek Pajak:</label>
                <select id="taxpayer-select" name="taxpayer_type" onchange="this.form.submit()" class="h-10 text-sm font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white px-3 focus:ring-2 focus:ring-[#007AFF] focus:border-transparent outline-none">
                    <option value="individual" {{ $isIndividual ? 'selected' : '' }}>Orang Pribadi (Usaha)</option>
                    <option value="corporate" {{ ! $isIndividual ? 'selected' : '' }}>Badan Usaha (PT / CV)</option>
                </select>
            </div>

            {{-- Status PTKP jika Orang Pribadi --}}
            @if($isIndividual)
                <div class="flex items-center gap-2">
                    <label for="ptkp-select" class="text-xs font-semibold text-slate-600 dark:text-slate-400">PTKP:</label>
                    <select id="ptkp-select" name="ptkp_status" onchange="this.form.submit()" class="h-10 text-sm font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white px-3 focus:ring-2 focus:ring-[#007AFF] focus:border-transparent outline-none">
                        @foreach(['TK/0', 'TK/1', 'TK/2', 'TK/3', 'K/0', 'K/1', 'K/2', 'K/3'] as $status)
                            <option value="{{ $status }}" {{ $ptkpStatus === $status ? 'selected' : '' }}>{{ $status }} (Rp {{ number_format(\App\Domain\Tax\NetIncomeTaxService::PTKP_VALUES[$status] ?? 54000000, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </form>

        {{-- Export Action Trigger --}}
        <div class="flex items-center gap-2 w-full md:w-auto justify-end">
            <button type="button" @click="showExportModal = true" class="h-11 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 text-sm font-semibold flex items-center justify-center gap-2 transition-colors shadow-sm w-full sm:w-auto">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span>Pusat Ekspor e-Bupot & CSV</span>
            </button>
        </div>
    </div>

    {{-- INTERACTIVE SIMULATOR BENTO (Segmented Control & 5 Simulation Panels) --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        {{-- Apple HIG Segmented Bar --}}
        <div class="p-3 bg-slate-50/80 dark:bg-slate-900/80 border-b border-slate-200/80 dark:border-slate-800">
            <div class="flex overflow-x-auto gap-1.5 p-1 bg-slate-200/60 dark:bg-slate-800/80 rounded-2xl no-scrollbar">
                <button type="button" @click="activeSimTab = 'net_income'" :class="activeSimTab === 'net_income' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-sm font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 font-medium'" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm whitespace-nowrap transition-all min-h-[44px]">
                    <i data-lucide="building-2" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>PPh Laba Bersih (UU HPP / 31E)</span>
                </button>
                <button type="button" @click="activeSimTab = 'umkm'" :class="activeSimTab === 'umkm' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-sm font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 font-medium'" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm whitespace-nowrap transition-all min-h-[44px]">
                    <i data-lucide="store" class="w-4 h-4 text-[#34C759]"></i>
                    <span>PPh Final UMKM 0.5% (PP 55)</span>
                </button>
                <button type="button" @click="activeSimTab = 'pph21'" :class="activeSimTab === 'pph21' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-sm font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 font-medium'" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm whitespace-nowrap transition-all min-h-[44px]">
                    <i data-lucide="users" class="w-4 h-4 text-[#5856D6]"></i>
                    <span>PPh 21 TER Karyawan (PP 58)</span>
                </button>
                <button type="button" @click="activeSimTab = 'payroll'" :class="activeSimTab === 'payroll' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-sm font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 font-medium'" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm whitespace-nowrap transition-all min-h-[44px]">
                    <i data-lucide="badge-dollar-sign" class="w-4 h-4 text-[#FF9500]"></i>
                    <span>Gaji, BPJS & THR Terpadu</span>
                </button>
                <button type="button" @click="activeSimTab = 'sales'" :class="activeSimTab === 'sales' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-sm font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 font-medium'" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm whitespace-nowrap transition-all min-h-[44px]">
                    <i data-lucide="shopping-cart" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Pajak Transaksi (PB1 / PPN)</span>
                </button>
            </div>
        </div>

        {{-- TAB 1: PPH LABA BERSIH & HASIL PENJUALAN --}}
        <div x-show="activeSimTab === 'net_income'" class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- Form Input (Left Column) --}}
                <div class="lg:col-span-5 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i data-lucide="sliders-horizontal" class="w-4 h-4 text-[#007AFF]"></i>
                            <span>Parameter Laba Bersih Usaha</span>
                        </h3>
                        <button type="button" @click="showNormaModal = true" class="text-xs font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                            <i data-lucide="help-circle" class="w-3.5 h-3.5"></i>
                            <span>Panduan Norma (NPPN)</span>
                        </button>
                    </div>

                    {{-- Form Inputs --}}
                    <div>
                        <label for="net-gross-revenue" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Peredaran Bruto / Penjualan Bersih (Rp):
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-semibold text-slate-400">Rp</span>
                            <input id="net-gross-revenue" type="number" x-model.number="netIncomeInput.gross_revenue" @input.debounce.300ms="runNetIncomeSim()" class="w-full pl-10 pr-4 h-11 text-base sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-[#007AFF] focus:border-transparent outline-none">
                        </div>
                    </div>

                    <div>
                        <label for="net-cogs" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Harga Pokok Penjualan / HPP Biaya Modal (Rp):
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-semibold text-slate-400">Rp</span>
                            <input id="net-cogs" type="number" x-model.number="netIncomeInput.cogs" @input.debounce.300ms="runNetIncomeSim()" class="w-full pl-10 pr-4 h-11 text-base sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-[#007AFF] focus:border-transparent outline-none">
                        </div>
                    </div>

                    <div>
                        <label for="net-expenses" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Beban Operasional Usaha / Biaya Kas (Rp):
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-semibold text-slate-400">Rp</span>
                            <input id="net-expenses" type="number" x-model.number="netIncomeInput.operating_expenses" @input.debounce.300ms="runNetIncomeSim()" class="w-full pl-10 pr-4 h-11 text-base sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-[#007AFF] focus:border-transparent outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div>
                            <label for="net-is-corporate" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Subjek Pajak:</label>
                            <select id="net-is-corporate" x-model="netIncomeInput.is_corporate" @change="runNetIncomeSim()" class="w-full h-11 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white px-3 font-medium focus:ring-2 focus:ring-[#007AFF] focus:border-transparent outline-none">
                                <option :value="false">Orang Pribadi</option>
                                <option :value="true">Badan Usaha (PT/CV)</option>
                            </select>
                        </div>
                        <div x-show="!netIncomeInput.is_corporate">
                            <label for="net-ptkp-status" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Status PTKP:</label>
                            <select id="net-ptkp-status" x-model="netIncomeInput.ptkp_status" @change="runNetIncomeSim()" class="w-full h-11 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white px-3 font-medium focus:ring-2 focus:ring-[#007AFF] focus:border-transparent outline-none">
                                @foreach(['TK/0', 'TK/1', 'TK/2', 'TK/3', 'K/0', 'K/1', 'K/2', 'K/3'] as $status)
                                    <option value="{{ $status }}">{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div x-show="netIncomeInput.is_corporate" class="flex items-end">
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 p-2.5 rounded-xl w-full">
                                Fasilitas Pasal 31E UU PPh (Tarif Efektif 11% s/d 4.8M)
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Live Results & Breakdown (Right Column) --}}
                <div class="lg:col-span-7 bg-slate-50/70 dark:bg-slate-800/40 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-slate-200/80 dark:border-slate-700">
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Hasil Simulasi Fiskal Riil</span>
                                <h4 class="text-lg font-bold text-slate-900 dark:text-white" x-text="netIncomeResult ? netIncomeResult.tax_scheme : 'Kalkulasi Fiskal...'"></h4>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-950/80 dark:text-blue-300">
                                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                <span>UU Harmonisasi Perpajakan</span>
                            </span>
                        </div>

                        {{-- Calculation Breakdown Bento --}}
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4">
                            <div class="bg-white dark:bg-slate-900 p-3.5 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-2xs">
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">Laba Kotor (Gross)</div>
                                <div class="text-base font-bold text-slate-900 dark:text-white mt-0.5" x-text="formatRupiah(netIncomeResult?.gross_profit)"></div>
                                <div class="text-[10px] text-slate-400 mt-0.5" x-text="'Margin: ' + (netIncomeResult?.gross_margin_percent || 0) + '%'"></div>
                            </div>
                            <div class="bg-white dark:bg-slate-900 p-3.5 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-2xs">
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">Laba Bersih Usaha</div>
                                <div class="text-base font-bold text-slate-900 dark:text-white mt-0.5" x-text="formatRupiah(netIncomeResult?.net_operating_income)"></div>
                                <div class="text-[10px] text-slate-400 mt-0.5" x-text="'Net Margin: ' + (netIncomeResult?.net_margin_percent || 0) + '%'"></div>
                            </div>
                            <div class="bg-white dark:bg-slate-900 p-3.5 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-2xs col-span-2 sm:col-span-1">
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">Penghasilan Kena Pajak (PKP)</div>
                                <div class="text-base font-bold text-slate-900 dark:text-white mt-0.5" x-text="formatRupiah(netIncomeResult?.taxable_income)"></div>
                                <div class="text-[10px] text-slate-400 mt-0.5" x-show="!netIncomeInput.is_corporate" x-text="'PTKP: ' + formatRupiah(netIncomeResult?.ptkp_amount)"></div>
                                <div class="text-[10px] text-slate-400 mt-0.5" x-show="netIncomeInput.is_corporate">Fasilitas Badan 31E</div>
                            </div>
                        </div>

                        {{-- Total Tax Payable Highlight --}}
                        <div class="mt-4 p-4 rounded-2xl bg-[#007AFF]/10 border border-[#007AFF]/20 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-semibold text-[#007AFF] uppercase tracking-wider">Beban Pajak Penghasilan (PPh) Terutang</span>
                                <div class="text-2xl font-black text-slate-900 dark:text-white mt-0.5" x-text="formatRupiah(netIncomeResult?.tax_amount)"></div>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Tarif Efektif Riil</span>
                                <div class="text-lg font-bold text-[#007AFF]" x-text="(netIncomeResult?.effective_tax_rate_percent || 0) + '%'"></div>
                            </div>
                        </div>

                        {{-- Action Button to view 5-bracket details if Individual --}}
                        <div x-show="!netIncomeInput.is_corporate && netIncomeResult?.tax_details?.brackets?.length" class="mt-3">
                            <button type="button" @click="showBracketModal = true" class="w-full py-2.5 px-3 rounded-xl bg-slate-200/80 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors">
                                <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                                <span>Lihat Rincian 5 Lapisan Tarif Progresif Pasal 17</span>
                            </button>
                        </div>
                    </div>

                    {{-- Comparison with PPh Final 0.5% --}}
                    <div class="mt-4 pt-4 border-t border-slate-200/80 dark:border-slate-700 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#34C759]"></span>
                            <span class="text-slate-600 dark:text-slate-400">Bandingkan PPh Final 0.5%: <strong class="text-slate-900 dark:text-white" x-text="formatRupiah(netIncomeResult?.comparison?.umkm_final_amount)"></strong></span>
                        </div>
                        <div class="font-semibold text-emerald-600 dark:text-emerald-400" x-text="netIncomeResult?.comparison?.recommendation_label"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 2: PPH FINAL UMKM 0.5% (PP 55/2022) --}}
        <div x-show="activeSimTab === 'umkm'" class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- Form Input --}}
                <div class="lg:col-span-5 space-y-4">
                    <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i data-lucide="store" class="w-4 h-4 text-[#34C759]"></i>
                            <span>Parameter PPh Final PP 55/2022</span>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Fasilitas bebas pajak omzet s.d Rp 500 Juta untuk Orang Pribadi.</p>
                    </div>

                    <div>
                        <label for="umkm-monthly-rev" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Peredaran Bruto / Omzet Bulan Ini (Rp):
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-semibold text-slate-400">Rp</span>
                            <input id="umkm-monthly-rev" type="number" x-model.number="umkmInput.monthly_revenue" @input.debounce.300ms="runUmkmSim()" class="w-full pl-10 pr-4 h-11 text-base sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-[#34C759] focus:border-transparent outline-none">
                        </div>
                    </div>

                    <div>
                        <label for="umkm-prior-cum" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Akumulasi Omzet Bulan-Bulan Sebelumnya (Rp):
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-semibold text-slate-400">Rp</span>
                            <input id="umkm-prior-cum" type="number" x-model.number="umkmInput.prior_cumulative" @input.debounce.300ms="runUmkmSim()" class="w-full pl-10 pr-4 h-11 text-base sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-[#34C759] focus:border-transparent outline-none">
                        </div>
                    </div>

                    <div class="pt-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Jenis Wajib Pajak:</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" @click="umkmInput.is_individual = true; runUmkmSim()" :class="umkmInput.is_individual ? 'border-[#34C759] bg-emerald-50 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-300 font-semibold' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400'" class="p-3 rounded-2xl border text-left text-xs transition-all min-h-[44px]">
                                <div class="font-bold">Orang Pribadi</div>
                                <div class="text-[10px] mt-0.5 opacity-80">Threshold Bebas Pajak 500 Jt</div>
                            </button>
                            <button type="button" @click="umkmInput.is_individual = false; runUmkmSim()" :class="!umkmInput.is_individual ? 'border-[#34C759] bg-emerald-50 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-300 font-semibold' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400'" class="p-3 rounded-2xl border text-left text-xs transition-all min-h-[44px]">
                                <div class="font-bold">Badan (PT/CV)</div>
                                <div class="text-[10px] mt-0.5 opacity-80">Langsung 0.5% sejak Rp 1</div>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Live Results --}}
                <div class="lg:col-span-7 bg-slate-50/70 dark:bg-slate-800/40 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-slate-200/80 dark:border-slate-700">
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">PPh Final UMKM 0.5%</span>
                                <h4 class="text-lg font-bold text-slate-900 dark:text-white">Perhitungan Billing Pajak Bulanan</h4>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                <span>PP No. 55 / 2022</span>
                            </span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4">
                            <div class="bg-white dark:bg-slate-900 p-3.5 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-2xs">
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">Akumulasi Omzet Baru</div>
                                <div class="text-base font-bold text-slate-900 dark:text-white mt-0.5" x-text="formatRupiah(umkmResult?.current_cumulative)"></div>
                            </div>
                            <div class="bg-white dark:bg-slate-900 p-3.5 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-2xs">
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">Dasar Pengenaan Pajak (DPP)</div>
                                <div class="text-base font-bold text-slate-900 dark:text-white mt-0.5" x-text="formatRupiah(umkmResult?.taxable_revenue)"></div>
                            </div>
                            <div class="bg-white dark:bg-slate-900 p-3.5 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-2xs col-span-2 sm:col-span-1">
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">Fasilitas Bebas Pajak</div>
                                <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400 mt-1" x-text="umkmResult?.is_under_threshold ? 'Bebas PPh (< 500 Juta)' : 'Dikenakan PPh 0.5%'"></div>
                            </div>
                        </div>

                        {{-- Tax Amount Box --}}
                        <div class="mt-4 p-4 rounded-2xl bg-[#34C759]/10 border border-[#34C759]/20 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-semibold text-[#34C759] uppercase tracking-wider">PPh Final Terutang (Setor Sendiri)</span>
                                <div class="text-2xl font-black text-slate-900 dark:text-white mt-0.5" x-text="formatRupiah(umkmResult?.tax_amount)"></div>
                            </div>
                            <div class="text-right text-xs text-slate-500 dark:text-slate-400">
                                <div>KAP: <strong class="text-slate-900 dark:text-white">411128</strong></div>
                                <div>KJS: <strong class="text-slate-900 dark:text-white">420</strong></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-200/80 dark:border-slate-700 text-xs text-slate-500 dark:text-slate-400">
                        Batas waktu penyetoran PPh Final UMKM adalah tanggal 15 bulan berikutnya melalui Kode Billing DJP Online.
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 3: PPH 21 TER KARYAWAN (PP 58/2023) --}}
        <div x-show="activeSimTab === 'pph21'" class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- Form Input --}}
                <div class="lg:col-span-5 space-y-4">
                    <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i data-lucide="users" class="w-4 h-4 text-[#5856D6]"></i>
                            <span>Parameter PPh 21 TER & Rekonsiliasi</span>
                        </h3>
                    </div>

                    <div>
                        <label for="pph21-calc-type" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Skema Masa Pajak:</label>
                        <select id="pph21-calc-type" x-model="pph21Input.calc_type" @change="runPph21Sim()" class="w-full h-11 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white px-3 font-medium focus:ring-2 focus:ring-[#5856D6] focus:border-transparent outline-none">
                            <option value="monthly_ter">Bulanan Pegawai Tetap (TER A / B / C - Jan s.d Nov)</option>
                            <option value="december">Rekonsiliasi Masa Desember (Pasal 17 Tahunan)</option>
                            <option value="daily_worker">Pegawai Harian Lepas (Tarif Efektif Harian)</option>
                        </select>
                    </div>

                    <div>
                        <label for="pph21-gross-wage" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1" x-text="pph21Input.calc_type === 'daily_worker' ? 'Upah Harian (Rp):' : (pph21Input.calc_type === 'december' ? 'Total Penghasilan Bruto 1 Tahun (Rp):' : 'Penghasilan Bruto Sebulan (Rp):')"></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-semibold text-slate-400">Rp</span>
                            <input id="pph21-gross-wage" type="number" x-model.number="pph21Input.gross_wage" @input.debounce.300ms="runPph21Sim()" class="w-full pl-10 pr-4 h-11 text-base sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-[#5856D6] focus:border-transparent outline-none">
                        </div>
                    </div>

                    <div x-show="pph21Input.calc_type !== 'daily_worker'">
                        <label for="pph21-ptkp-status" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Status PTKP Karyawan:</label>
                        <select id="pph21-ptkp-status" x-model="pph21Input.ptkp_status" @change="runPph21Sim()" class="w-full h-11 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white px-3 font-medium focus:ring-2 focus:ring-[#5856D6] focus:border-transparent outline-none">
                            <option value="TK/0">TK/0 (TER A)</option>
                            <option value="TK/1">TK/1 (TER A)</option>
                            <option value="K/0">K/0 (TER A)</option>
                            <option value="TK/2">TK/2 (TER B)</option>
                            <option value="TK/3">TK/3 (TER B)</option>
                            <option value="K/1">K/1 (TER B)</option>
                            <option value="K/2">K/2 (TER B)</option>
                            <option value="K/3">K/3 (TER C)</option>
                        </select>
                    </div>

                    {{-- Extra inputs for December Reconciliation --}}
                    <div x-show="pph21Input.calc_type === 'december'" class="space-y-3 pt-1">
                        <div>
                            <label for="pph21-deductions" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pengurang Tahunan (Biaya Jabatan + JHT/JP Pekerja) (Rp):</label>
                            <input id="pph21-deductions" type="number" x-model.number="pph21Input.annual_deductions" @input.debounce.300ms="runPph21Sim()" class="w-full px-3.5 h-10 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-[#5856D6] outline-none">
                        </div>
                        <div>
                            <label for="pph21-tax-paid" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">PPh 21 Telah Dipotong Masa Jan-Nov (Rp):</label>
                            <input id="pph21-tax-paid" type="number" x-model.number="pph21Input.tax_paid_before" @input.debounce.300ms="runPph21Sim()" class="w-full px-3.5 h-10 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-[#5856D6] outline-none">
                        </div>
                    </div>
                </div>

                {{-- Live Results --}}
                <div class="lg:col-span-7 bg-slate-50/70 dark:bg-slate-800/40 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-slate-200/80 dark:border-slate-700">
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Hasil Pemotongan PPh 21</span>
                                <h4 class="text-lg font-bold text-slate-900 dark:text-white" x-text="pph21Input.calc_type === 'december' ? 'Rekonsiliasi Masa Desember' : 'PPh 21 Masa Bulanan'"></h4>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-950/80 dark:text-purple-300">
                                <i data-lucide="calculator" class="w-3.5 h-3.5"></i>
                                <span>PP No. 58 / 2023</span>
                            </span>
                        </div>

                        {{-- TER Results --}}
                        <div x-show="pph21Input.calc_type === 'monthly_ter'" class="space-y-4 mt-4">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-white dark:bg-slate-900 p-3.5 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-2xs">
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">Kategori TER</div>
                                    <div class="text-base font-bold text-slate-900 dark:text-white mt-0.5" x-text="pph21Result?.ter_category || '-'"></div>
                                </div>
                                <div class="bg-white dark:bg-slate-900 p-3.5 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-2xs">
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">Tarif Efektif (TER)</div>
                                    <div class="text-base font-bold text-[#5856D6] mt-0.5" x-text="(pph21Result?.ter_rate_percent || 0) + '%'"></div>
                                </div>
                            </div>

                            <div class="p-4 rounded-2xl bg-[#5856D6]/10 border border-[#5856D6]/20 flex items-center justify-between">
                                <div>
                                    <span class="text-xs font-semibold text-[#5856D6] uppercase tracking-wider">Potongan PPh 21 Bulan Ini</span>
                                    <div class="text-2xl font-black text-slate-900 dark:text-white mt-0.5" x-text="formatRupiah(pph21Result?.pph21_monthly_amount)"></div>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Gaji Bersih Diterima</span>
                                    <div class="text-lg font-bold text-slate-900 dark:text-white" x-text="formatRupiah(pph21Result?.take_home_pay)"></div>
                                </div>
                            </div>
                        </div>

                        {{-- December Results --}}
                        <div x-show="pph21Input.calc_type === 'december'" class="space-y-4 mt-4">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-white dark:bg-slate-900 p-3.5 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-2xs">
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">PPh 21 Terutang 1 Tahun</div>
                                    <div class="text-base font-bold text-slate-900 dark:text-white mt-0.5" x-text="formatRupiah(pph21Result?.annual_tax_payable)"></div>
                                </div>
                                <div class="bg-white dark:bg-slate-900 p-3.5 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-2xs">
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">Telah Dipotong Jan-Nov</div>
                                    <div class="text-base font-bold text-slate-900 dark:text-white mt-0.5" x-text="formatRupiah(pph21Result?.tax_already_paid)"></div>
                                </div>
                            </div>

                            <div class="p-4 rounded-2xl bg-[#5856D6]/10 border border-[#5856D6]/20 flex items-center justify-between">
                                <div>
                                    <span class="text-xs font-semibold text-[#5856D6] uppercase tracking-wider">Potongan PPh 21 Masa Desember</span>
                                    <div class="text-2xl font-black text-slate-900 dark:text-white mt-0.5" x-text="formatRupiah(pph21Result?.december_tax_payable)"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Daily Worker Results --}}
                        <div x-show="pph21Input.calc_type === 'daily_worker'" class="space-y-4 mt-4">
                            <div class="p-4 rounded-2xl bg-[#5856D6]/10 border border-[#5856D6]/20 flex items-center justify-between">
                                <div>
                                    <span class="text-xs font-semibold text-[#5856D6] uppercase tracking-wider">Potongan PPh 21 Harian</span>
                                    <div class="text-2xl font-black text-slate-900 dark:text-white mt-0.5" x-text="formatRupiah(pph21Result?.pph21_daily_amount)"></div>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Upah Harian Bersih</span>
                                    <div class="text-lg font-bold text-slate-900 dark:text-white" x-text="formatRupiah(pph21Result?.net_daily_wage)"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-200/80 dark:border-slate-700 text-xs text-slate-500 dark:text-slate-400">
                        Hasil kalkulasi PPh 21 dapat diekspor langsung dalam format DJP e-Bupot 21/26 resmi.
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 4: GAJI, BPJS & THR TERPADU --}}
        <div x-show="activeSimTab === 'payroll'" class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- Form Input --}}
                <div class="lg:col-span-5 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i data-lucide="badge-dollar-sign" class="w-4 h-4 text-[#FF9500]"></i>
                            <span>Komponen Penggajian & THR</span>
                        </h3>
                        <button type="button" @click="showBpjsModal = true" class="text-xs font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                            <i data-lucide="info" class="w-3.5 h-3.5"></i>
                            <span>Rincian BPJS</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="payroll-base-salary" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Gaji Pokok (Rp):</label>
                            <input id="payroll-base-salary" type="number" x-model.number="payrollInput.base_salary" @input.debounce.300ms="runPayrollSim()" class="w-full px-3 h-11 text-base sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-[#FF9500] outline-none">
                        </div>
                        <div>
                            <label for="payroll-fixed-allowance" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tunjangan Tetap (Rp):</label>
                            <input id="payroll-fixed-allowance" type="number" x-model.number="payrollInput.fixed_allowances" @input.debounce.300ms="runPayrollSim()" class="w-full px-3 h-11 text-base sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-[#FF9500] outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="payroll-var-allowance" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tunjangan Tidak Tetap (Rp):</label>
                            <input id="payroll-var-allowance" type="number" x-model.number="payrollInput.variable_allowances" @input.debounce.300ms="runPayrollSim()" class="w-full px-3 h-11 text-base sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-[#FF9500] outline-none">
                        </div>
                        <div>
                            <label for="payroll-loan-deduction" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Potongan Kasbon / Pinjaman (Rp):</label>
                            <input id="payroll-loan-deduction" type="number" x-model.number="payrollInput.loan_deduction" @input.debounce.300ms="runPayrollSim()" class="w-full px-3 h-11 text-base sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-[#FF9500] outline-none">
                        </div>
                    </div>

                    {{-- Checkboxes for BPJS & THR --}}
                    <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-2 text-xs">
                        <label class="flex items-center gap-2 cursor-pointer font-semibold text-slate-800 dark:text-slate-200">
                            <input type="checkbox" x-model="payrollInput.bpjs_tk_enabled" @change="runPayrollSim()" class="rounded text-[#FF9500] focus:ring-[#FF9500] w-4 h-4">
                            <span>Sertakan BPJS Ketenagakerjaan (JKK, JKM, JHT, JP)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer font-semibold text-slate-800 dark:text-slate-200">
                            <input type="checkbox" x-model="payrollInput.bpjs_kes_enabled" @change="runPayrollSim()" class="rounded text-[#FF9500] focus:ring-[#FF9500] w-4 h-4">
                            <span>Sertakan BPJS Kesehatan (4% Perusahaan, 1% Karyawan)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer font-semibold text-slate-800 dark:text-slate-200">
                            <input type="checkbox" x-model="payrollInput.include_thr" @change="runPayrollSim()" class="rounded text-[#FF9500] focus:ring-[#FF9500] w-4 h-4">
                            <span>Hitung THR Keagamaan Pro-Rata</span>
                        </label>
                    </div>
                </div>

                {{-- Live Results --}}
                <div class="lg:col-span-7 bg-slate-50/70 dark:bg-slate-800/40 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-slate-200/80 dark:border-slate-700">
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Ringkasan Payroll & Ketenagakerjaan</span>
                                <h4 class="text-lg font-bold text-slate-900 dark:text-white">Take Home Pay & Beban Perusahaan</h4>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300">
                                <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
                                <span>Permenaker & UU Ketenagakerjaan</span>
                            </span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4">
                            <div class="bg-white dark:bg-slate-900 p-3.5 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-2xs">
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">Total Upah Bruto</div>
                                <div class="text-base font-bold text-slate-900 dark:text-white mt-0.5" x-text="formatRupiah(payrollResult?.gross_pay)"></div>
                            </div>
                            <div class="bg-white dark:bg-slate-900 p-3.5 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-2xs">
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">Potongan Karyawan</div>
                                <div class="text-base font-bold text-[#FF3B30] mt-0.5" x-text="formatRupiah(payrollResult?.total_employee_deductions)"></div>
                                <div class="text-[10px] text-slate-400 mt-0.5">PPh 21 + BPJS + Kasbon</div>
                            </div>
                            <div class="bg-white dark:bg-slate-900 p-3.5 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-2xs col-span-2 sm:col-span-1">
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">THR Pro-Rata</div>
                                <div class="text-base font-bold text-[#FF9500] mt-0.5" x-text="formatRupiah(payrollResult?.thr_amount)"></div>
                                <div class="text-[10px] text-slate-400 mt-0.5" x-text="'Masa Kerja: ' + (payrollResult?.thr_details?.service_months || 12) + ' Bulan'"></div>
                            </div>
                        </div>

                        {{-- Total Take Home Pay Highlight --}}
                        <div class="mt-4 p-4 rounded-2xl bg-[#FF9500]/10 border border-[#FF9500]/20 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-semibold text-[#FF9500] uppercase tracking-wider">Take Home Pay (Gaji Bersih Diterima Karyawan)</span>
                                <div class="text-2xl font-black text-slate-900 dark:text-white mt-0.5" x-text="formatRupiah(payrollResult?.take_home_pay)"></div>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Biaya Perusahaan</span>
                                <div class="text-base font-bold text-slate-900 dark:text-white" x-text="formatRupiah(payrollResult?.total_company_cost)"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-200/80 dark:border-slate-700 flex items-center justify-between text-xs">
                        <span class="text-slate-500 dark:text-slate-400">BPJS Tanggungan Perusahaan:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200" x-text="formatRupiah((payrollResult?.company_bpjs_tk || 0) + (payrollResult?.company_bpjs_kes || 0))"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 5: PAJAK TRANSAKSI (PB1 / PPN) --}}
        <div x-show="activeSimTab === 'sales'" class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- Form Input --}}
                <div class="lg:col-span-5 space-y-4">
                    <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i data-lucide="shopping-cart" class="w-4 h-4 text-[#007AFF]"></i>
                            <span>Parameter Pajak Transaksi Penjualan</span>
                        </h3>
                    </div>

                    <div>
                        <label for="sales-subtotal" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Subtotal Penjualan (Rp):</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-semibold text-slate-400">Rp</span>
                            <input id="sales-subtotal" type="number" x-model.number="salesTaxInput.subtotal" @input.debounce.300ms="runSalesTaxSim()" class="w-full pl-10 pr-4 h-11 text-base sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-[#007AFF] outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="sales-discount" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Diskon Transaksi (Rp):</label>
                            <input id="sales-discount" type="number" x-model.number="salesTaxInput.discount" @input.debounce.300ms="runSalesTaxSim()" class="w-full px-3 h-11 text-base sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-[#007AFF] outline-none">
                        </div>
                        <div>
                            <label for="sales-service-charge" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Service Charge (%):</label>
                            <input id="sales-service-charge" type="number" step="0.01" :value="salesTaxInput.service_charge_rate * 100" @input.debounce.300ms="salesTaxInput.service_charge_rate = $event.target.value / 100; runSalesTaxSim()" class="w-full px-3 h-11 text-base sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-[#007AFF] outline-none">
                        </div>
                    </div>

                    <div>
                        <label for="sales-tax-type" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Pajak Transaksi:</label>
                        <select id="sales-tax-type" x-model="salesTaxInput.tax_type" @change="runSalesTaxSim()" class="w-full h-11 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white px-3 font-medium focus:ring-2 focus:ring-[#007AFF] outline-none">
                            <option value="pb1">PB1 Restoran / Kafe (10% Pajak Daerah)</option>
                            <option value="ppn_11">PPN Standar (11%)</option>
                            <option value="ppn_12">PPN Regulasi Baru (12%)</option>
                            <option value="none">Tanpa Pajak Transaksi (0%)</option>
                        </select>
                    </div>

                    <div class="pt-1">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-800 dark:text-slate-200">
                            <input type="checkbox" x-model="salesTaxInput.is_inclusive" @change="runSalesTaxSim()" class="rounded text-[#007AFF] focus:ring-[#007AFF] w-4 h-4">
                            <span>Harga Sudah Termasuk Pajak (Tax Inclusive)</span>
                        </label>
                    </div>
                </div>

                {{-- Live Results --}}
                <div class="lg:col-span-7 bg-slate-50/70 dark:bg-slate-800/40 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-slate-200/80 dark:border-slate-700">
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Perhitungan Struk & Invoice</span>
                                <h4 class="text-lg font-bold text-slate-900 dark:text-white">Simulasi Pajak Konsumen POS</h4>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-950/80 dark:text-blue-300">
                                <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                                <span>Sinkron Kasir POS</span>
                            </span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4">
                            <div class="bg-white dark:bg-slate-900 p-3.5 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-2xs">
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">Net Sales / DPP</div>
                                <div class="text-base font-bold text-slate-900 dark:text-white mt-0.5" x-text="formatRupiah(salesTaxResult?.net_sales)"></div>
                            </div>
                            <div class="bg-white dark:bg-slate-900 p-3.5 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-2xs">
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">Service Charge</div>
                                <div class="text-base font-bold text-slate-900 dark:text-white mt-0.5" x-text="formatRupiah(salesTaxResult?.service_charge)"></div>
                            </div>
                            <div class="bg-white dark:bg-slate-900 p-3.5 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-2xs col-span-2 sm:col-span-1">
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">Pajak (PB1 / PPN)</div>
                                <div class="text-base font-bold text-[#007AFF] mt-0.5" x-text="formatRupiah(salesTaxResult?.tax_amount)"></div>
                            </div>
                        </div>

                        {{-- Total Grand Total --}}
                        <div class="mt-4 p-4 rounded-2xl bg-[#007AFF]/10 border border-[#007AFF]/20 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-semibold text-[#007AFF] uppercase tracking-wider">Total Tagihan Konsumen (Grand Total)</span>
                                <div class="text-2xl font-black text-slate-900 dark:text-white mt-0.5" x-text="formatRupiah(salesTaxResult?.grand_total)"></div>
                            </div>
                            <div class="text-right text-xs text-slate-500 dark:text-slate-400">
                                <div>Status: <strong class="text-slate-900 dark:text-white" x-text="salesTaxInput.is_inclusive ? 'Harga Inclusive' : 'Harga Exclusive'"></strong></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-200/80 dark:border-slate-700 text-xs text-slate-500 dark:text-slate-400">
                        Pajak PB1/PPN yang tercatat pada sistem POS dapat dipisahkan secara otomatis dari omzet riil saat menyusun Laporan Laba Rugi.
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 12-MONTH FISCAL BREAKDOWN TABLE (Senior-Friendly Bento Table) --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="calendar-range" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Rekapitulasi Fiskal 12 Bulan (Tahun {{ $currentYear }})</span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Dihitung otomatis dari rekonsiliasi data Laporan Laba Rugi, Transaksi POS, dan Pengeluaran Kas.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('tax.export.net_income', ['year' => $currentYear, 'taxpayer_type' => $taxpayerType, 'ptkp_status' => $ptkpStatus]) }}" class="h-9 px-3.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-emerald-600"></i>
                    <span>Unduh CSV Laporan Fiskal</span>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="py-3 px-4">Masa Pajak</th>
                        <th class="py-3 px-4 text-right">Omzet Riil (Rp)</th>
                        <th class="py-3 px-4 text-right">HPP Biaya Modal (Rp)</th>
                        <th class="py-3 px-4 text-right">Beban Operasional (Rp)</th>
                        <th class="py-3 px-4 text-right">Laba Bersih (Rp)</th>
                        <th class="py-3 px-4 text-right">PPh Laba Bersih (Rp)</th>
                        <th class="py-3 px-4 text-right">PPh Final 0.5% (Rp)</th>
                        <th class="py-3 px-4 text-center">Rekomendasi Skema</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs font-medium">
                    @foreach($netIncomeSummary['monthly_breakdown'] as $m => $item)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">
                                {{ $item['month_name'] }}
                            </td>
                            <td class="py-3 px-4 text-right text-slate-700 dark:text-slate-300">
                                {{ number_format($item['revenue'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right text-slate-600 dark:text-slate-400">
                                {{ number_format($item['cogs'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right text-slate-600 dark:text-slate-400">
                                {{ number_format($item['expenses'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold {{ $item['net_income'] < 0 ? 'text-[#FF3B30]' : 'text-slate-900 dark:text-white' }}">
                                {{ number_format($item['net_income'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-[#007AFF]">
                                {{ number_format($item['tax_amount'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-semibold text-slate-700 dark:text-slate-300">
                                {{ number_format($item['umkm_final_amount'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($item['recommendation'] === 'net_income')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/80 dark:text-blue-300">
                                        Laba Bersih (Lebih Hemat)
                                    </span>
                                @elseif($item['recommendation'] === 'umkm_final')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300">
                                        PPh Final 0.5%
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                        Beban Setara
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-100/80 dark:bg-slate-800/80 border-t-2 border-slate-300 dark:border-slate-700 text-xs font-bold text-slate-900 dark:text-white">
                        <td class="py-3.5 px-4 uppercase">KONSOLIDASI TAHUNAN</td>
                        <td class="py-3.5 px-4 text-right">{{ number_format($netIncomeSummary['total_revenue_year'], 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-right">{{ number_format($netIncomeSummary['total_cogs_year'], 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-right">{{ number_format($netIncomeSummary['total_expenses_year'], 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-right text-emerald-600 dark:text-emerald-400">{{ number_format($netIncomeSummary['total_net_income_year'], 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-right text-[#007AFF]">{{ number_format($netIncomeSummary['total_net_tax_year'], 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-right text-slate-700 dark:text-slate-300">{{ number_format($netIncomeSummary['total_umkm_final_year'], 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-black bg-[#34C759] text-white">
                                Rekomendasi Terpilih
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- MODAL SHEET 1: DETAIL 5 LAPISAN TARIF PROGRESIF PASAL 17 --}}
    <div x-show="showBracketModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-xl w-full p-6 border border-slate-200 dark:border-slate-800 shadow-2xl relative" @click.outside="showBracketModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-[#007AFF] flex items-center justify-center">
                        <i data-lucide="layers" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Rincian 5 Lapisan Tarif Progresif</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Pasal 17 ayat (1) huruf a UU HPP No. 7 Tahun 2021</p>
                    </div>
                </div>
                <button type="button" @click="showBracketModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-900 flex items-center justify-center">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="mt-4 space-y-3">
                <template x-for="(b, idx) in (netIncomeResult?.tax_details?.brackets || [])" :key="idx">
                    <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white" x-text="b.bracket"></div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5" x-text="'PKP Dikenakan: ' + formatRupiah(b.amount) + ' @ Tarif ' + b.rate_percent"></div>
                        </div>
                        <div class="text-right font-black text-sm text-[#007AFF]" x-text="formatRupiah(b.tax)"></div>
                    </div>
                </template>
            </div>

            <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total PPh Pasal 17 Terutang:</span>
                <span class="text-lg font-black text-slate-900 dark:text-white" x-text="formatRupiah(netIncomeResult?.tax_amount)"></span>
            </div>
        </div>
    </div>

    {{-- MODAL SHEET 2: RINCIAN KOMPREHENSIF BPJS --}}
    <div x-show="showBpjsModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full p-6 border border-slate-200 dark:border-slate-800 shadow-2xl relative" @click.outside="showBpjsModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-[#FF9500] flex items-center justify-center">
                        <i data-lucide="shield" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Rincian Komponen Iuran BPJS</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Tanggungan Perusahaan vs Potongan Karyawan</p>
                    </div>
                </div>
                <button type="button" @click="showBpjsModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-900 flex items-center justify-center">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="mt-4 space-y-2.5 text-xs">
                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700">
                    <div class="font-bold text-slate-900 dark:text-white mb-1">1. BPJS Ketenagakerjaan</div>
                    <ul class="space-y-1 text-slate-600 dark:text-slate-400">
                        <li>- JKK (Kecelakaan Kerja): Perusahaan 0.24% - 1.74% (Karyawan 0%)</li>
                        <li>- JKM (Kematian): Perusahaan 0.30% (Karyawan 0%)</li>
                        <li>- JHT (Hari Tua): Perusahaan 3.70%, Karyawan 2.00%</li>
                        <li>- JP (Pensiun): Perusahaan 2.00%, Karyawan 1.00% (Cap Upah Rp 10.042.300)</li>
                    </ul>
                </div>

                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700">
                    <div class="font-bold text-slate-900 dark:text-white mb-1">2. BPJS Kesehatan</div>
                    <ul class="space-y-1 text-slate-600 dark:text-slate-400">
                        <li>- Tanggungan Perusahaan: 4.00% (Cap Upah Rp 12.000.000)</li>
                        <li>- Potongan Gaji Karyawan: 1.00%</li>
                    </ul>
                </div>
            </div>

            <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" @click="showBpjsModal = false" class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 text-xs font-semibold">
                    Tutup Panduan
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL SHEET 3: PANDUAN NORMA PENCATATAN (NPPN PASAL 14) --}}
    <div x-show="showNormaModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full p-6 border border-slate-200 dark:border-slate-800 shadow-2xl relative" @click.outside="showNormaModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-[#007AFF] flex items-center justify-center">
                        <i data-lucide="book-open" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Norma Penghitungan (NPPN)</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Pasal 14 UU Pajak Penghasilan</p>
                    </div>
                </div>
                <button type="button" @click="showNormaModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-900 flex items-center justify-center">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="mt-4 space-y-3 text-xs leading-relaxed text-slate-600 dark:text-slate-300">
                <p>
                    Wajib Pajak Orang Pribadi yang melakukan kegiatan usaha atau pekerjaan bebas dengan peredaran bruto kurang dari <strong>Rp 4.800.000.000 per tahun</strong> diperbolehkan menghitung penghasilan neto menggunakan <strong>Norma Penghitungan Penghasilan Neto (NPPN)</strong>.
                </p>
                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700 space-y-1">
                    <div class="font-bold text-slate-900 dark:text-white">Contoh Persentase Norma KLU Umum:</div>
                    <div>- Perdagangan Eceran / Toko Kelontong: 25% - 30%</div>
                    <div>- Jasa Bengkel & Reparasi: 30% - 35%</div>
                    <div>- Restoran / Rumah Makan: 20% - 25%</div>
                </div>
            </div>

            <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" @click="showNormaModal = false" class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 text-xs font-semibold">
                    Tutup Panduan
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL SHEET 4: PUSAT EKSPOR E-BUPOT & REKAP CSV --}}
    <div x-show="showExportModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full p-6 border border-slate-200 dark:border-slate-800 shadow-2xl relative" @click.outside="showExportModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white flex items-center justify-center">
                        <i data-lucide="download-cloud" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Pusat Ekspor Dokumen Fiskal</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Format Resmi DJP Online & Laporan CSV</p>
                    </div>
                </div>
                <button type="button" @click="showExportModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-900 flex items-center justify-center">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="mt-4 space-y-3">
                {{-- Export Option 1: e-Bupot 21/26 --}}
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-slate-900 dark:text-white">DJP e-Bupot 21/26 (CSV)</div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Format impor resmi pemotongan PPh 21 karyawan</div>
                    </div>
                    <a href="{{ route('tax.export.ebupot', ['year' => $currentYear, 'month' => (int) date('n')]) }}" class="h-9 px-3 rounded-xl bg-[#007AFF] hover:bg-[#007AFF]/90 text-white text-xs font-semibold flex items-center gap-1.5 transition-colors shadow-sm">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>Unduh</span>
                    </a>
                </div>

                {{-- Export Option 2: Rekap PPh Final UMKM --}}
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-slate-900 dark:text-white">Rekap PPh Final UMKM 0.5% (CSV)</div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Rekapitulasi 12 bulan & Kode Billing (KAP 411128 / KJS 420)</div>
                    </div>
                    <a href="{{ route('tax.export.pph_final', ['year' => $currentYear, 'taxpayer_type' => $taxpayerType]) }}" class="h-9 px-3 rounded-xl bg-[#34C759] hover:bg-[#34C759]/90 text-white text-xs font-semibold flex items-center gap-1.5 transition-colors shadow-sm">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>Unduh</span>
                    </a>
                </div>

                {{-- Export Option 3: Rekap PPh Laba Bersih Tahunan --}}
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-slate-900 dark:text-white">Laporan Fiskal Laba Bersih (CSV)</div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Konsolidasi SPT Tahunan (Pasal 31E / Pasal 17)</div>
                    </div>
                    <a href="{{ route('tax.export.net_income', ['year' => $currentYear, 'taxpayer_type' => $taxpayerType, 'ptkp_status' => $ptkpStatus]) }}" class="h-9 px-3 rounded-xl bg-[#5856D6] hover:bg-[#5856D6]/90 text-white text-xs font-semibold flex items-center gap-1.5 transition-colors shadow-sm">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>Unduh</span>
                    </a>
                </div>
            </div>

            <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" @click="showExportModal = false" class="w-full py-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-semibold">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
