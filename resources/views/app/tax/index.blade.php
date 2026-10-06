@extends('layouts.app', [
    'title' => __('tax.title'),
    'headerTitle' => __('tax.header_title'),
    'headerSubtitle' => __('tax.header_subtitle')
])

@section('content')
<div class="space-y-6 pb-28 lg:pb-12" x-data="{
    activeSimTab: (function() {
        const rawTab = (new URLSearchParams(window.location.search)).get('tab');
        if (!rawTab) return 'net_income';
        const tab = rawTab.toLowerCase();
        if (['net_income', 'badan', 'pph_badan', 'income', 'laba'].includes(tab)) return 'net_income';
        if (['umkm', 'pp55', 'final'].includes(tab)) return 'umkm';
        if (['pph21', 'ter', 'pp58', 'karyawan'].includes(tab)) return 'pph21';
        if (['payroll', 'gaji', 'bpjs', 'thr'].includes(tab)) return 'payroll';
        if (['sales', 'transaksi', 'pb1', 'ppn'].includes(tab)) return 'sales';
        return 'net_income';
    })(),
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
        // Deep-Linking: Synchronize URL query parameters with active tab
        this.$watch('activeSimTab', (val) => {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', val);
            window.history.replaceState({}, '', url);
        });

        // Ensure current active tab is reflected in URL on load
        const currentTabInUrl = (new URLSearchParams(window.location.search)).get('tab');
        if (!currentTabInUrl || currentTabInUrl !== this.activeSimTab) {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', this.activeSimTab);
            window.history.replaceState({}, '', url);
        }

        this.runNetIncomeSim();
        this.runUmkmSim();
        this.runPph21Sim();
        this.runPayrollSim();
        this.runSalesTaxSim();
    }
}">

    {{-- BENTO EXECUTIVE SUMMARY HEADER (Apple HIG Bento Grid) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Peredaran Bruto / Pendapatan Bersih Penjualan --}}
        <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl p-5 border border-black/10 dark:border-white/10 shadow-sm relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('tax.kpis.revenue_ytd') }}</span>
                <span class="w-8 h-8 rounded-2xl bg-blue-500/10 dark:bg-blue-500/20 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold text-black dark:text-white tracking-tight">
                    {{ 'Rp ' . number_format($netIncomeSummary['total_revenue_year'], 0, ',', '.') }}
                </div>
                <div class="mt-1 flex items-center gap-1.5 text-xs text-black/50 dark:text-white/50">
                    <i data-lucide="calculator" class="w-3.5 h-3.5 text-black/40 dark:text-white/40"></i>
                    <span>{{ __('tax.kpis.revenue_hint', ['year' => $currentYear]) }}</span>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-xs">
                <span class="text-black/50 dark:text-white/50">{{ __('tax.net_income.field_cogs') }}</span>
                <span class="font-semibold text-black/80 dark:text-white/80">
                    {{ 'Rp ' . number_format($netIncomeSummary['total_cogs_year'], 0, ',', '.') }}
                </span>
            </div>
        </div>

        {{-- Card 2: Laba Operasional Riil --}}
        <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl p-5 border border-black/10 dark:border-white/10 shadow-sm relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('tax.kpis.net_income_comm') }}</span>
                <span class="w-8 h-8 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/20 text-[#34C759] flex items-center justify-center">
                    <i data-lucide="pie-chart" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold {{ $netIncomeSummary['total_net_income_year'] < 0 ? 'text-[#FF3B30]' : 'text-black dark:text-white' }} tracking-tight">
                    {{ 'Rp ' . number_format($netIncomeSummary['total_net_income_year'], 0, ',', '.') }}
                </div>
                <div class="mt-1 flex items-center gap-1.5 text-xs text-black/50 dark:text-white/50">
                    <i data-lucide="wallet" class="w-3.5 h-3.5 text-black/40 dark:text-white/40"></i>
                    <span>{{ __('tax.net_income.field_expenses') }}: Rp {{ number_format($netIncomeSummary['total_expenses_year'], 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-xs">
                <span class="text-black/50 dark:text-white/50">{{ __('tax.kpis.net_income_hint') }}</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $netIncomeSummary['total_net_income_year'] <= 0 ? 'bg-amber-500/15 text-amber-600 dark:text-amber-400' : 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400' }}">
                    {{ $netIncomeSummary['total_net_income_year'] <= 0 ? 'Rugi Operasional' : 'Laba Positif' }}
                </span>
            </div>
        </div>

        {{-- Card 3: Estimasi PPh Terutang Tahunan --}}
        <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl p-5 border border-black/10 dark:border-white/10 shadow-sm relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('tax.kpis.tax_net_income_est') }}</span>
                <span class="w-8 h-8 rounded-2xl bg-indigo-500/10 dark:bg-indigo-500/20 text-[#5856D6] flex items-center justify-center">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold text-black dark:text-white tracking-tight">
                    {{ 'Rp ' . number_format($netIncomeSummary['total_net_tax_year'], 0, ',', '.') }}
                </div>
                <div class="mt-1 flex items-center gap-1.5 text-xs text-black/50 dark:text-white/50">
                    <i data-lucide="landmark" class="w-3.5 h-3.5 text-black/40 dark:text-white/40"></i>
                    <span>{{ $isIndividual ? 'Wajib Pajak Orang Pribadi (' . $ptkpStatus . ')' : 'Wajib Pajak Badan (Pasal 31E)' }}</span>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-xs">
                <span class="text-black/50 dark:text-white/50">PPh Final 0.5% (PP 55)</span>
                <span class="font-semibold text-black/80 dark:text-white/80">
                    {{ 'Rp ' . number_format($netIncomeSummary['total_umkm_final_year'], 0, ',', '.') }}
                </span>
            </div>
        </div>

        {{-- Card 4: Tax Optimization Engine (Apple Highlight Bento) --}}
        <div class="bg-[#1C1C1E] dark:bg-[#2C2C2E] rounded-3xl p-5 text-white shadow-sm relative overflow-hidden flex flex-col justify-between border border-black/10 dark:border-white/10">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#34C759] animate-pulse"></span>
                    <span class="text-xs font-semibold uppercase tracking-wider text-white/70">{{ __('tax.actions.calculate') }}</span>
                </div>
                <span class="w-8 h-8 rounded-2xl bg-white/10 text-amber-300 flex items-center justify-center">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-base font-bold text-white tracking-tight line-clamp-1">
                    {{ $netIncomeSummary['annual_calculation']['comparison']['recommendation_label'] ?? 'Analisis Skema Fiskal' }}
                </div>
                <p class="mt-1 text-xs text-white/70 line-clamp-2 leading-relaxed">
                    {{ $netIncomeSummary['annual_calculation']['comparison']['rationale'] ?? 'Perbandingan otomatis antara skema pembukuan laba bersih dengan PPh Final 0.5%.' }}
                </p>
            </div>
            <div class="mt-3 pt-3 border-t border-white/10 flex items-center justify-between text-xs">
                <span class="text-white/70">{{ __('tax.net_income.card_effective_rate') }}</span>
                <span class="font-bold text-[#34C759]">
                    {{ 'Rp ' . number_format((float) ($netIncomeSummary['annual_calculation']['comparison']['tax_savings'] ?? 0), 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

    {{-- FILTER CONTROLS & EXPORT ACTION BAR (Apple Segmented Bar) --}}
    <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl p-4 border border-black/10 dark:border-white/10 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('tax.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            {{-- Tahun Pajak --}}
            <div class="flex items-center gap-2">
                <label for="year-select" class="text-xs font-semibold text-black/60 dark:text-white/60">{{ __('tax.year_filter.label') }}</label>
                <select id="year-select" name="year" onchange="this.form.submit()" class="h-10 text-sm font-medium rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white px-3 focus:ring-2 focus:ring-[#007AFF] focus:border-transparent outline-none">
                    @for($y = (int) date('Y'); $y >= (int) date('Y') - 4; $y--)
                        <option value="{{ $y }}" {{ $currentYear === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            {{-- Jenis Wajib Pajak --}}
            <div class="flex items-center gap-2">
                <label for="taxpayer-select" class="text-xs font-semibold text-black/60 dark:text-white/60">{{ __('tax.year_filter.type_label') }}</label>
                <select id="taxpayer-select" name="taxpayer_type" onchange="this.form.submit()" class="h-10 text-sm font-medium rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white px-3 focus:ring-2 focus:ring-[#007AFF] focus:border-transparent outline-none">
                    <option value="individual" {{ $isIndividual ? 'selected' : '' }}>{{ __('tax.year_filter.type_individual') }}</option>
                    <option value="corporate" {{ ! $isIndividual ? 'selected' : '' }}>{{ __('tax.year_filter.type_corporate') }}</option>
                </select>
            </div>

            {{-- Status PTKP jika Orang Pribadi --}}
            @if($isIndividual)
                <div class="flex items-center gap-2">
                    <label for="ptkp-select" class="text-xs font-semibold text-black/60 dark:text-white/60">{{ __('tax.year_filter.ptkp_label') }}</label>
                    <select id="ptkp-select" name="ptkp_status" onchange="this.form.submit()" class="h-10 text-sm font-medium rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white px-3 focus:ring-2 focus:ring-[#007AFF] focus:border-transparent outline-none">
                        @foreach(['TK/0', 'TK/1', 'TK/2', 'TK/3', 'K/0', 'K/1', 'K/2', 'K/3'] as $status)
                            <option value="{{ $status }}" {{ $ptkpStatus === $status ? 'selected' : '' }}>{{ $status }} (Rp {{ number_format(\App\Domain\Tax\NetIncomeTaxService::PTKP_VALUES[$status] ?? 54000000, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </form>

        {{-- Export Action Trigger --}}
        <div class="flex items-center gap-2 w-full md:w-auto justify-end">
            <button type="button" @click="showExportModal = true" class="h-11 px-4 rounded-xl bg-black dark:bg-white text-white dark:text-black hover:bg-black/80 dark:hover:bg-white/90 text-sm font-semibold flex items-center justify-center gap-2 transition-colors shadow-sm w-full sm:w-auto">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span>{{ __('tax.actions.export') }}</span>
            </button>
        </div>
    </div>

    {{-- INTERACTIVE SIMULATOR BENTO (Segmented Control & 5 Simulation Panels) --}}
    <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl border border-black/10 dark:border-white/10 shadow-sm overflow-hidden">
        {{-- Apple HIG Segmented Bar --}}
        <div class="p-3 bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/10 dark:border-white/10">
            <div class="flex overflow-x-auto gap-1.5 p-1.5 bg-black/[0.05] dark:bg-white/[0.06] rounded-2xl no-scrollbar">
                <button type="button" @click="activeSimTab = 'net_income'" :class="activeSimTab === 'net_income' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold border border-black/5 dark:border-white/10' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm whitespace-nowrap transition-all min-h-[44px]">
                    <i data-lucide="building-2" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>{{ __('tax.tabs.net_income') }}</span>
                </button>
                <button type="button" @click="activeSimTab = 'umkm'" :class="activeSimTab === 'umkm' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold border border-black/5 dark:border-white/10' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm whitespace-nowrap transition-all min-h-[44px]">
                    <i data-lucide="store" class="w-4 h-4 text-[#34C759]"></i>
                    <span>{{ __('tax.tabs.umkm') }}</span>
                </button>
                <button type="button" @click="activeSimTab = 'pph21'" :class="activeSimTab === 'pph21' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold border border-black/5 dark:border-white/10' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm whitespace-nowrap transition-all min-h-[44px]">
                    <i data-lucide="users" class="w-4 h-4 text-[#5856D6]"></i>
                    <span>{{ __('tax.tabs.pph21') }}</span>
                </button>
                <button type="button" @click="activeSimTab = 'payroll'" :class="activeSimTab === 'payroll' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold border border-black/5 dark:border-white/10' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm whitespace-nowrap transition-all min-h-[44px]">
                    <i data-lucide="badge-dollar-sign" class="w-4 h-4 text-[#FF9500]"></i>
                    <span>{{ __('tax.tabs.payroll') }}</span>
                </button>
                <button type="button" @click="activeSimTab = 'sales'" :class="activeSimTab === 'sales' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold border border-black/5 dark:border-white/10' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm whitespace-nowrap transition-all min-h-[44px]">
                    <i data-lucide="shopping-cart" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>{{ __('tax.tabs.sales') }}</span>
                </button>
            </div>
        </div>

        {{-- TAB 1: PPH LABA BERSIH & HASIL PENJUALAN --}}
        <div x-show="activeSimTab === 'net_income'" class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- Form Input (Left Column) --}}
                <div class="lg:col-span-5 space-y-4">
                    <div class="flex items-center justify-between border-b border-black/10 dark:border-white/10 pb-3">
                        <h3 class="text-base font-bold text-black dark:text-white flex items-center gap-2">
                            <i data-lucide="sliders-horizontal" class="w-4 h-4 text-[#007AFF]"></i>
                            <span>{{ __('tax.net_income.title') }}</span>
                        </h3>
                        <button type="button" @click="showNormaModal = true" class="text-xs font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                            <i data-lucide="help-circle" class="w-3.5 h-3.5"></i>
                            <span>{{ __('tax.actions.norma_rules') }}</span>
                        </button>
                    </div>

                    {{-- Form Inputs --}}
                    <div>
                        <label for="net-gross-revenue" class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">
                            {{ __('tax.net_income.field_revenue') }}:
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-semibold text-black/40 dark:text-white/40">Rp</span>
                            <input id="net-gross-revenue" type="number" x-model.number="netIncomeInput.gross_revenue" @input.debounce.300ms="runNetIncomeSim()" class="w-full pl-10 pr-4 h-11 text-base sm:text-sm rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white font-medium focus:ring-2 focus:ring-[#007AFF] focus:border-transparent outline-none">
                        </div>
                    </div>

                    <div>
                        <label for="net-cogs" class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">
                            {{ __('tax.net_income.field_cogs') }}:
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-semibold text-black/40 dark:text-white/40">Rp</span>
                            <input id="net-cogs" type="number" x-model.number="netIncomeInput.cogs" @input.debounce.300ms="runNetIncomeSim()" class="w-full pl-10 pr-4 h-11 text-base sm:text-sm rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white font-medium focus:ring-2 focus:ring-[#007AFF] focus:border-transparent outline-none">
                        </div>
                    </div>

                    <div>
                        <label for="net-expenses" class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">
                            {{ __('tax.net_income.field_expenses') }}:
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-semibold text-black/40 dark:text-white/40">Rp</span>
                            <input id="net-expenses" type="number" x-model.number="netIncomeInput.operating_expenses" @input.debounce.300ms="runNetIncomeSim()" class="w-full pl-10 pr-4 h-11 text-base sm:text-sm rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white font-medium focus:ring-2 focus:ring-[#007AFF] focus:border-transparent outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div>
                            <label for="net-is-corporate" class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">{{ __('tax.net_income.field_entity') }}:</label>
                            <select id="net-is-corporate" x-model="netIncomeInput.is_corporate" @change="runNetIncomeSim()" class="w-full h-11 text-sm rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white px-3 font-medium focus:ring-2 focus:ring-[#007AFF] focus:border-transparent outline-none">
                                <option :value="false">{{ __('tax.net_income.entity_individual') }}</option>
                                <option :value="true">{{ __('tax.net_income.entity_corporate') }}</option>
                            </select>
                        </div>
                        <div x-show="!netIncomeInput.is_corporate">
                            <label for="net-ptkp-status" class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Status PTKP:</label>
                            <select id="net-ptkp-status" x-model="netIncomeInput.ptkp_status" @change="runNetIncomeSim()" class="w-full h-11 text-sm rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white px-3 font-medium focus:ring-2 focus:ring-[#007AFF] focus:border-transparent outline-none">
                                @foreach(['TK/0', 'TK/1', 'TK/2', 'TK/3', 'K/0', 'K/1', 'K/2', 'K/3'] as $status)
                                    <option value="{{ $status }}">{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div x-show="netIncomeInput.is_corporate" class="flex items-end">
                            <div class="text-[11px] text-black/60 dark:text-white/60 bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 p-2.5 rounded-xl w-full">
                                Fasilitas Pasal 31E UU PPh (Tarif Efektif 11% s/d 4.8M)
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Live Results & Breakdown (Right Column) --}}
                <div class="lg:col-span-7 bg-black/[0.02] dark:bg-white/[0.02] rounded-3xl p-6 border border-black/10 dark:border-white/10 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-black/10 dark:border-white/10">
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">{{ __('tax.net_income.results_title') }}</span>
                                <h4 class="text-lg font-bold text-black dark:text-white" x-text="netIncomeResult ? netIncomeResult.tax_scheme : @json(__('tax.net_income.calculating_fiscal'))"></h4>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/10 text-[#007AFF] dark:bg-blue-500/20">
                                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                <span>{{ __('tax.net_income.law_badge') }}</span>
                            </span>
                        </div>

                        {{-- Calculation Breakdown Bento --}}
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4">
                            <div class="bg-white dark:bg-[#1C1C1E] p-3.5 rounded-2xl border border-black/10 dark:border-white/10 shadow-xs">
                                <div class="text-[11px] text-black/50 dark:text-white/50">{{ __('tax.net_income.gross_profit') }}</div>
                                <div class="text-base font-bold text-black dark:text-white mt-0.5" x-text="formatRupiah(netIncomeResult?.gross_profit)"></div>
                                <div class="text-[10px] text-black/40 dark:text-white/40 mt-0.5" x-text="'Margin: ' + (netIncomeResult?.gross_margin_percent || 0) + '%'"></div>
                            </div>
                            <div class="bg-white dark:bg-[#1C1C1E] p-3.5 rounded-2xl border border-black/10 dark:border-white/10 shadow-xs">
                                <div class="text-[11px] text-black/50 dark:text-white/50">{{ __('tax.net_income.net_operating_profit') }}</div>
                                <div class="text-base font-bold text-black dark:text-white mt-0.5" x-text="formatRupiah(netIncomeResult?.net_operating_income)"></div>
                                <div class="text-[10px] text-black/40 dark:text-white/40 mt-0.5" x-text="'Net Margin: ' + (netIncomeResult?.net_margin_percent || 0) + '%'"></div>
                            </div>
                            <div class="bg-white dark:bg-[#1C1C1E] p-3.5 rounded-2xl border border-black/10 dark:border-white/10 shadow-xs col-span-2 sm:col-span-1">
                                <div class="text-[11px] text-black/50 dark:text-white/50">{{ __('tax.net_income.card_pkp') }}</div>
                                <div class="text-base font-bold text-black dark:text-white mt-0.5" x-text="formatRupiah(netIncomeResult?.taxable_income)"></div>
                                <div class="text-[10px] text-black/40 dark:text-white/40 mt-0.5" x-show="!netIncomeInput.is_corporate" x-text="'PTKP: ' + formatRupiah(netIncomeResult?.ptkp_amount)"></div>
                                <div class="text-[10px] text-black/40 dark:text-white/40 mt-0.5" x-show="netIncomeInput.is_corporate">{{ __('tax.net_income.facility_31e_short') }}</div>
                            </div>
                        </div>

                        {{-- Total Tax Payable Highlight --}}
                        <div class="mt-4 p-4 rounded-2xl bg-[#007AFF]/10 border border-[#007AFF]/20 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-semibold text-[#007AFF] uppercase tracking-wider">{{ __('tax.net_income.tax_payable_highlight') }}</span>
                                <div class="text-2xl font-black text-black dark:text-white mt-0.5" x-text="formatRupiah(netIncomeResult?.tax_amount)"></div>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-semibold text-black/50 dark:text-white/50">{{ __('tax.net_income.effective_rate_real') }}</span>
                                <div class="text-lg font-bold text-[#007AFF]" x-text="(netIncomeResult?.effective_tax_rate_percent || 0) + '%'"></div>
                            </div>
                        </div>

                        {{-- Action Button to view 5-bracket details if Individual --}}
                        <div x-show="!netIncomeInput.is_corporate && netIncomeResult?.tax_details?.brackets?.length" class="mt-3">
                            <button type="button" @click="showBracketModal = true" class="w-full py-2.5 px-3 rounded-xl bg-black/[0.05] hover:bg-black/10 dark:bg-white/[0.06] dark:hover:bg-white/10 text-black dark:text-white text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors border border-black/5 dark:border-white/5">
                                <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                                <span>{{ __('tax.net_income.view_brackets_btn') }}</span>
                            </button>
                        </div>
                    </div>

                    {{-- Comparison with PPh Final 0.5% --}}
                    <div class="mt-4 pt-4 border-t border-black/10 dark:border-white/10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#34C759]"></span>
                            <span class="text-black/60 dark:text-white/60">{{ __('tax.net_income.compare_umkm_label') }} <strong class="text-black dark:text-white" x-text="formatRupiah(netIncomeResult?.comparison?.umkm_final_amount)"></strong></span>
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
                    <div class="border-b border-black/10 dark:border-white/10 pb-3">
                        <h3 class="text-base font-bold text-black dark:text-white flex items-center gap-2">
                            <i data-lucide="store" class="w-4 h-4 text-[#34C759]"></i>
                            <span>{{ __('tax.umkm.title') }}</span>
                        </h3>
                        <p class="text-xs text-black/50 dark:text-white/50 mt-1">{{ __('tax.umkm.header_desc') }}</p>
                    </div>

                    <div>
                        <label for="umkm-monthly-rev" class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">
                            {{ __('tax.umkm.field_monthly_revenue') }}:
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-semibold text-black/40 dark:text-white/40">Rp</span>
                            <input id="umkm-monthly-rev" type="number" x-model.number="umkmInput.monthly_revenue" @input.debounce.300ms="runUmkmSim()" class="w-full pl-10 pr-4 h-11 text-base sm:text-sm rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white font-medium focus:ring-2 focus:ring-[#34C759] focus:border-transparent outline-none">
                        </div>
                    </div>

                    <div>
                        <label for="umkm-prior-cum" class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">
                            {{ __('tax.umkm.field_prior_cumulative') }}:
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-semibold text-black/40 dark:text-white/40">Rp</span>
                            <input id="umkm-prior-cum" type="number" x-model.number="umkmInput.prior_cumulative" @input.debounce.300ms="runUmkmSim()" class="w-full pl-10 pr-4 h-11 text-base sm:text-sm rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white font-medium focus:ring-2 focus:ring-[#34C759] focus:border-transparent outline-none">
                        </div>
                    </div>

                    <div class="pt-2">
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-2">{{ __('tax.umkm.field_taxpayer_type') }}:</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" @click="umkmInput.is_individual = true; runUmkmSim()" :class="umkmInput.is_individual ? 'border-[#34C759] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold border-2' : 'border-black/10 dark:border-white/10 text-black/60 dark:text-white/60 bg-black/[0.02] dark:bg-white/[0.04]'" class="p-3 rounded-2xl border text-left text-xs transition-all min-h-[44px]">
                                <div class="font-bold">{{ __('tax.umkm.entity_individual') }}</div>
                                <div class="text-[10px] mt-0.5 opacity-80">{{ __('tax.umkm.entity_individual_desc') }}</div>
                            </button>
                            <button type="button" @click="umkmInput.is_individual = false; runUmkmSim()" :class="!umkmInput.is_individual ? 'border-[#34C759] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold border-2' : 'border-black/10 dark:border-white/10 text-black/60 dark:text-white/60 bg-black/[0.02] dark:bg-white/[0.04]'" class="p-3 rounded-2xl border text-left text-xs transition-all min-h-[44px]">
                                <div class="font-bold">{{ __('tax.umkm.entity_corporate') }}</div>
                                <div class="text-[10px] mt-0.5 opacity-80">{{ __('tax.umkm.entity_corporate_desc') }}</div>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Live Results --}}
                <div class="lg:col-span-7 bg-black/[0.02] dark:bg-white/[0.02] rounded-3xl p-6 border border-black/10 dark:border-white/10 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-black/10 dark:border-white/10">
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">{{ __('tax.umkm.results_subtitle') }}</span>
                                <h4 class="text-lg font-bold text-black dark:text-white">{{ __('tax.umkm.results_title') }}</h4>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-[#34C759] dark:bg-emerald-500/20">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                <span>{{ __('tax.umkm.law_badge') }}</span>
                            </span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4">
                            <div class="bg-white dark:bg-[#1C1C1E] p-3.5 rounded-2xl border border-black/10 dark:border-white/10 shadow-xs">
                                <div class="text-[11px] text-black/50 dark:text-white/50">{{ __('tax.umkm.card_new_cumulative') }}</div>
                                <div class="text-base font-bold text-black dark:text-white mt-0.5" x-text="formatRupiah(umkmResult?.current_cumulative)"></div>
                            </div>
                            <div class="bg-white dark:bg-[#1C1C1E] p-3.5 rounded-2xl border border-black/10 dark:border-white/10 shadow-xs">
                                <div class="text-[11px] text-black/50 dark:text-white/50">{{ __('tax.umkm.card_dpp') }}</div>
                                <div class="text-base font-bold text-black dark:text-white mt-0.5" x-text="formatRupiah(umkmResult?.taxable_revenue)"></div>
                            </div>
                            <div class="bg-white dark:bg-[#1C1C1E] p-3.5 rounded-2xl border border-black/10 dark:border-white/10 shadow-xs col-span-2 sm:col-span-1">
                                <div class="text-[11px] text-black/50 dark:text-white/50">{{ __('tax.umkm.card_exempt_facility') }}</div>
                                <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400 mt-1" x-text="umkmResult?.is_under_threshold ? @json(__('tax.umkm.exempt_active')) : @json(__('tax.umkm.exempt_exceeded'))"></div>
                            </div>
                        </div>

                        {{-- Tax Amount Box --}}
                        <div class="mt-4 p-4 rounded-2xl bg-[#34C759]/10 border border-[#34C759]/20 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-semibold text-[#34C759] uppercase tracking-wider">{{ __('tax.umkm.card_final_tax_due') }}</span>
                                <div class="text-2xl font-black text-black dark:text-white mt-0.5" x-text="formatRupiah(umkmResult?.tax_amount)"></div>
                            </div>
                            <div class="text-right text-xs text-black/50 dark:text-white/50">
                                <div>KAP: <strong class="text-black dark:text-white">411128</strong></div>
                                <div>KJS: <strong class="text-black dark:text-white">420</strong></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-black/10 dark:border-white/10 text-xs text-black/50 dark:text-white/50">
                        {{ __('tax.umkm.due_date_notice') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 3: PPH 21 TER KARYAWAN (PP 58/2023) --}}
        <div x-show="activeSimTab === 'pph21'" class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- Form Input --}}
                <div class="lg:col-span-5 space-y-4">
                    <div class="border-b border-black/10 dark:border-white/10 pb-3">
                        <h3 class="text-base font-bold text-black dark:text-white flex items-center gap-2">
                            <i data-lucide="users" class="w-4 h-4 text-[#5856D6]"></i>
                            <span>{{ __('tax.pph21.title') }}</span>
                        </h3>
                    </div>

                    <div>
                        <label for="pph21-calc-type" class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">{{ __('tax.pph21.field_scheme') }}</label>
                        <select id="pph21-calc-type" x-model="pph21Input.calc_type" @change="runPph21Sim()" class="w-full h-11 text-sm rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white px-3 font-medium focus:ring-2 focus:ring-[#5856D6] focus:border-transparent outline-none">
                            <option value="monthly_ter">{{ __('tax.pph21.scheme_monthly') }}</option>
                            <option value="december">{{ __('tax.pph21.scheme_december') }}</option>
                            <option value="daily_worker">{{ __('tax.pph21.scheme_daily') }}</option>
                        </select>
                    </div>

                    <div>
                        <label for="pph21-gross-wage" class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1" x-text="pph21Input.calc_type === 'daily_worker' ? @json(__('tax.pph21.wage_daily')) : (pph21Input.calc_type === 'december' ? @json(__('tax.pph21.wage_annual')) : @json(__('tax.pph21.wage_monthly'))))"></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-semibold text-black/40 dark:text-white/40">Rp</span>
                            <input id="pph21-gross-wage" type="number" x-model.number="pph21Input.gross_wage" @input.debounce.300ms="runPph21Sim()" class="w-full pl-10 pr-4 h-11 text-base sm:text-sm rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white font-medium focus:ring-2 focus:ring-[#5856D6] focus:border-transparent outline-none">
                        </div>
                    </div>

                    <div x-show="pph21Input.calc_type !== 'daily_worker'">
                        <label for="pph21-ptkp-status" class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">{{ __('tax.pph21.field_ptkp') }}:</label>
                        <select id="pph21-ptkp-status" x-model="pph21Input.ptkp_status" @change="runPph21Sim()" class="w-full h-11 text-sm rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white px-3 font-medium focus:ring-2 focus:ring-[#5856D6] focus:border-transparent outline-none">
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
                            <label for="pph21-deductions" class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">{{ __('tax.pph21.deductions_annual') }}</label>
                            <input id="pph21-deductions" type="number" x-model.number="pph21Input.annual_deductions" @input.debounce.300ms="runPph21Sim()" class="w-full px-3.5 h-10 text-sm rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white font-medium focus:ring-2 focus:ring-[#5856D6] outline-none">
                        </div>
                        <div>
                            <label for="pph21-tax-paid" class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">{{ __('tax.pph21.tax_paid_before') }}</label>
                            <input id="pph21-tax-paid" type="number" x-model.number="pph21Input.tax_paid_before" @input.debounce.300ms="runPph21Sim()" class="w-full px-3.5 h-10 text-sm rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white font-medium focus:ring-2 focus:ring-[#5856D6] outline-none">
                        </div>
                    </div>
                </div>

                {{-- Live Results --}}
                <div class="lg:col-span-7 bg-black/[0.02] dark:bg-white/[0.02] rounded-3xl p-6 border border-black/10 dark:border-white/10 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-black/10 dark:border-white/10">
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">{{ __('tax.pph21.results_title') }}</span>
                                <h4 class="text-lg font-bold text-black dark:text-white" x-text="pph21Input.calc_type === 'december' ? @json(__('tax.pph21.results_december')) : @json(__('tax.pph21.results_monthly'))"></h4>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-purple-500/10 text-[#5856D6] dark:bg-purple-500/20">
                                <i data-lucide="calculator" class="w-3.5 h-3.5"></i>
                                <span>{{ __('tax.pph21.law_badge') }}</span>
                            </span>
                        </div>

                        {{-- TER Results --}}
                        <div x-show="pph21Input.calc_type === 'monthly_ter'" class="space-y-4 mt-4">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-white dark:bg-[#1C1C1E] p-3.5 rounded-2xl border border-black/10 dark:border-white/10 shadow-xs">
                                    <div class="text-[11px] text-black/50 dark:text-white/50">{{ __('tax.pph21.card_category') }}</div>
                                    <div class="text-base font-bold text-black dark:text-white mt-0.5" x-text="pph21Result?.ter_category || '-'"></div>
                                </div>
                                <div class="bg-white dark:bg-[#1C1C1E] p-3.5 rounded-2xl border border-black/10 dark:border-white/10 shadow-xs">
                                    <div class="text-[11px] text-black/50 dark:text-white/50">{{ __('tax.pph21.card_ter_rate') }}</div>
                                    <div class="text-base font-bold text-[#5856D6] mt-0.5" x-text="(pph21Result?.ter_rate_percent || 0) + '%'"></div>
                                </div>
                            </div>

                            <div class="p-4 rounded-2xl bg-[#5856D6]/10 border border-[#5856D6]/20 flex items-center justify-between">
                                <div>
                                    <span class="text-xs font-semibold text-[#5856D6] uppercase tracking-wider">{{ __('tax.pph21.card_monthly_cut') }}</span>
                                    <div class="text-2xl font-black text-black dark:text-white mt-0.5" x-text="formatRupiah(pph21Result?.pph21_monthly_amount)"></div>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-semibold text-black/50 dark:text-white/50">{{ __('tax.pph21.card_net_salary') }}</span>
                                    <div class="text-lg font-bold text-black dark:text-white" x-text="formatRupiah(pph21Result?.take_home_pay)"></div>
                                </div>
                            </div>
                        </div>

                        {{-- December Results --}}
                        <div x-show="pph21Input.calc_type === 'december'" class="space-y-4 mt-4">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-white dark:bg-[#1C1C1E] p-3.5 rounded-2xl border border-black/10 dark:border-white/10 shadow-xs">
                                    <div class="text-[11px] text-black/50 dark:text-white/50">{{ __('tax.pph21.card_annual_due') }}</div>
                                    <div class="text-base font-bold text-black dark:text-white mt-0.5" x-text="formatRupiah(pph21Result?.annual_tax_payable)"></div>
                                </div>
                                <div class="bg-white dark:bg-[#1C1C1E] p-3.5 rounded-2xl border border-black/10 dark:border-white/10 shadow-xs">
                                    <div class="text-[11px] text-black/50 dark:text-white/50">{{ __('tax.pph21.card_already_paid') }}</div>
                                    <div class="text-base font-bold text-black dark:text-white mt-0.5" x-text="formatRupiah(pph21Result?.tax_already_paid)"></div>
                                </div>
                            </div>

                            <div class="p-4 rounded-2xl bg-[#5856D6]/10 border border-[#5856D6]/20 flex items-center justify-between">
                                <div>
                                    <span class="text-xs font-semibold text-[#5856D6] uppercase tracking-wider">{{ __('tax.pph21.card_december_cut') }}</span>
                                    <div class="text-2xl font-black text-black dark:text-white mt-0.5" x-text="formatRupiah(pph21Result?.december_tax_payable)"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Daily Worker Results --}}
                        <div x-show="pph21Input.calc_type === 'daily_worker'" class="space-y-4 mt-4">
                            <div class="p-4 rounded-2xl bg-[#5856D6]/10 border border-[#5856D6]/20 flex items-center justify-between">
                                <div>
                                    <span class="text-xs font-semibold text-[#5856D6] uppercase tracking-wider">{{ __('tax.pph21.card_daily_cut') }}</span>
                                    <div class="text-2xl font-black text-black dark:text-white mt-0.5" x-text="formatRupiah(pph21Result?.pph21_daily_amount)"></div>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-semibold text-black/50 dark:text-white/50">{{ __('tax.pph21.card_net_daily') }}</span>
                                    <div class="text-lg font-bold text-black dark:text-white" x-text="formatRupiah(pph21Result?.net_daily_wage)"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-black/10 dark:border-white/10 text-xs text-black/50 dark:text-white/50">
                        {{ __('tax.pph21.export_hint') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 4: GAJI, BPJS & THR TERPADU --}}
        <div x-show="activeSimTab === 'payroll'" class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- Form Input --}}
                <div class="lg:col-span-5 space-y-4">
                    <div class="flex items-center justify-between border-b border-black/10 dark:border-white/10 pb-3">
                        <h3 class="text-base font-bold text-black dark:text-white flex items-center gap-2">
                            <i data-lucide="badge-dollar-sign" class="w-4 h-4 text-[#FF9500]"></i>
                            <span>{{ __('tax.payroll.title') }}</span>
                        </h3>
                        <button type="button" @click="showBpjsModal = true" class="text-xs font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                            <i data-lucide="info" class="w-3.5 h-3.5"></i>
                            <span>{{ __('tax.actions.bpjs_rules') }}</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="payroll-base-salary" class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">{{ __('tax.payroll.field_base_salary') }}:</label>
                            <input id="payroll-base-salary" type="number" x-model.number="payrollInput.base_salary" @input.debounce.300ms="runPayrollSim()" class="w-full px-3 h-11 text-base sm:text-sm rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white font-medium focus:ring-2 focus:ring-[#FF9500] outline-none">
                        </div>
                        <div>
                            <label for="payroll-fixed-allowance" class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">{{ __('tax.payroll.field_fixed_allowance') }}:</label>
                            <input id="payroll-fixed-allowance" type="number" x-model.number="payrollInput.fixed_allowances" @input.debounce.300ms="runPayrollSim()" class="w-full px-3 h-11 text-base sm:text-sm rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white font-medium focus:ring-2 focus:ring-[#FF9500] outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="payroll-var-allowance" class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">{{ __('tax.payroll.field_variable_allowance') }}:</label>
                            <input id="payroll-var-allowance" type="number" x-model.number="payrollInput.variable_allowances" @input.debounce.300ms="runPayrollSim()" class="w-full px-3 h-11 text-base sm:text-sm rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white font-medium focus:ring-2 focus:ring-[#FF9500] outline-none">
                        </div>
                        <div>
                            <label for="payroll-loan-deduction" class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">{{ __('tax.payroll.field_loan_deduction') }}:</label>
                            <input id="payroll-loan-deduction" type="number" x-model.number="payrollInput.loan_deduction" @input.debounce.300ms="runPayrollSim()" class="w-full px-3 h-11 text-base sm:text-sm rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white font-medium focus:ring-2 focus:ring-[#FF9500] outline-none">
                        </div>
                    </div>

                    {{-- Checkboxes for BPJS & THR --}}
                    <div class="p-3.5 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 text-xs">
                        <label class="flex items-center gap-2 cursor-pointer font-semibold text-black dark:text-white">
                            <input type="checkbox" x-model="payrollInput.bpjs_tk_enabled" @change="runPayrollSim()" class="rounded text-[#FF9500] focus:ring-[#FF9500] w-4 h-4">
                            <span>{{ __('tax.payroll.toggle_bpjs_tk') }}</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer font-semibold text-black dark:text-white">
                            <input type="checkbox" x-model="payrollInput.bpjs_kes_enabled" @change="runPayrollSim()" class="rounded text-[#FF9500] focus:ring-[#FF9500] w-4 h-4">
                            <span>{{ __('tax.payroll.toggle_bpjs_kes') }}</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer font-semibold text-black dark:text-white">
                            <input type="checkbox" x-model="payrollInput.include_thr" @change="runPayrollSim()" class="rounded text-[#FF9500] focus:ring-[#FF9500] w-4 h-4">
                            <span>{{ __('tax.payroll.toggle_thr') }}</span>
                        </label>
                    </div>
                </div>

                {{-- Live Results --}}
                <div class="lg:col-span-7 bg-black/[0.02] dark:bg-white/[0.02] rounded-3xl p-6 border border-black/10 dark:border-white/10 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-black/10 dark:border-white/10">
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">{{ __('tax.payroll.results_subtitle') }}</span>
                                <h4 class="text-lg font-bold text-black dark:text-white">{{ __('tax.payroll.results_title') }}</h4>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-[#FF9500] dark:bg-amber-500/20">
                                <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
                                <span>{{ __('tax.payroll.law_badge') }}</span>
                            </span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4">
                            <div class="bg-white dark:bg-[#1C1C1E] p-3.5 rounded-2xl border border-black/10 dark:border-white/10 shadow-xs">
                                <div class="text-[11px] text-black/50 dark:text-white/50">{{ __('tax.payroll.card_gross_pay') }}</div>
                                <div class="text-base font-bold text-black dark:text-white mt-0.5" x-text="formatRupiah(payrollResult?.gross_pay)"></div>
                            </div>
                            <div class="bg-white dark:bg-[#1C1C1E] p-3.5 rounded-2xl border border-black/10 dark:border-white/10 shadow-xs">
                                <div class="text-[11px] text-black/50 dark:text-white/50">{{ __('tax.payroll.card_employee_deductions') }}</div>
                                <div class="text-base font-bold text-[#FF3B30] mt-0.5" x-text="formatRupiah(payrollResult?.total_employee_deductions)"></div>
                                <div class="text-[10px] text-black/40 dark:text-white/40 mt-0.5">{{ __('tax.payroll.deductions_hint') }}</div>
                            </div>
                            <div class="bg-white dark:bg-[#1C1C1E] p-3.5 rounded-2xl border border-black/10 dark:border-white/10 shadow-xs col-span-2 sm:col-span-1">
                                <div class="text-[11px] text-black/50 dark:text-white/50">{{ __('tax.payroll.card_thr_prorata') }}</div>
                                <div class="text-base font-bold text-[#FF9500] mt-0.5" x-text="formatRupiah(payrollResult?.thr_amount)"></div>
                                <div class="text-[10px] text-black/40 dark:text-white/40 mt-0.5" x-text="'Masa Kerja: ' + (payrollResult?.thr_details?.service_months || 12) + ' Bulan'"></div>
                            </div>
                        </div>

                        {{-- Total Take Home Pay Highlight --}}
                        <div class="mt-4 p-4 rounded-2xl bg-[#FF9500]/10 border border-[#FF9500]/20 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-semibold text-[#FF9500] uppercase tracking-wider">{{ __('tax.payroll.card_take_home_highlight') }}</span>
                                <div class="text-2xl font-black text-black dark:text-white mt-0.5" x-text="formatRupiah(payrollResult?.take_home_pay)"></div>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-semibold text-black/50 dark:text-white/50">{{ __('tax.payroll.card_total_company_cost') }}</span>
                                <div class="text-base font-bold text-black dark:text-white" x-text="formatRupiah(payrollResult?.total_company_cost)"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-black/10 dark:border-white/10 flex items-center justify-between text-xs">
                        <span class="text-black/50 dark:text-white/50">{{ __('tax.payroll.company_bpjs_burden') }}</span>
                        <span class="font-bold text-black dark:text-white" x-text="formatRupiah((payrollResult?.company_bpjs_tk || 0) + (payrollResult?.company_bpjs_kes || 0))"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 5: PAJAK TRANSAKSI (PB1 / PPN) --}}
        <div x-show="activeSimTab === 'sales'" class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- Form Input --}}
                <div class="lg:col-span-5 space-y-4">
                    <div class="border-b border-black/10 dark:border-white/10 pb-3">
                        <h3 class="text-base font-bold text-black dark:text-white flex items-center gap-2">
                            <i data-lucide="shopping-cart" class="w-4 h-4 text-[#007AFF]"></i>
                            <span>{{ __('tax.sales.title') }}</span>
                        </h3>
                    </div>

                    <div>
                        <label for="sales-subtotal" class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">{{ __('tax.sales.field_subtotal') }}:</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-semibold text-black/40 dark:text-white/40">Rp</span>
                            <input id="sales-subtotal" type="number" x-model.number="salesTaxInput.subtotal" @input.debounce.300ms="runSalesTaxSim()" class="w-full pl-10 pr-4 h-11 text-base sm:text-sm rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white font-medium focus:ring-2 focus:ring-[#007AFF] outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="sales-discount" class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">{{ __('tax.sales.field_discount') }}:</label>
                            <input id="sales-discount" type="number" x-model.number="salesTaxInput.discount" @input.debounce.300ms="runSalesTaxSim()" class="w-full px-3 h-11 text-base sm:text-sm rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white font-medium focus:ring-2 focus:ring-[#007AFF] outline-none">
                        </div>
                        <div>
                            <label for="sales-service-charge" class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">{{ __('tax.sales.field_service_charge') }}:</label>
                            <input id="sales-service-charge" type="number" step="0.01" :value="salesTaxInput.service_charge_rate * 100" @input.debounce.300ms="salesTaxInput.service_charge_rate = $event.target.value / 100; runSalesTaxSim()" class="w-full px-3 h-11 text-base sm:text-sm rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white font-medium focus:ring-2 focus:ring-[#007AFF] outline-none">
                        </div>
                    </div>

                    <div>
                        <label for="sales-tax-type" class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">{{ __('tax.sales.field_tax_type') }}:</label>
                        <select id="sales-tax-type" x-model="salesTaxInput.tax_type" @change="runSalesTaxSim()" class="w-full h-11 text-sm rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white px-3 font-medium focus:ring-2 focus:ring-[#007AFF] outline-none">
                            <option value="pb1">{{ __('tax.sales.opt_pb1') }}</option>
                            <option value="ppn_11">{{ __('tax.sales.opt_ppn_11') }}</option>
                            <option value="ppn_12">{{ __('tax.sales.opt_ppn_12') }}</option>
                            <option value="none">{{ __('tax.sales.opt_none') }}</option>
                        </select>
                    </div>

                    <div class="pt-1">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-black/80 dark:text-white/80">
                            <input type="checkbox" x-model="salesTaxInput.is_inclusive" @change="runSalesTaxSim()" class="rounded text-[#007AFF] focus:ring-[#007AFF] w-4 h-4">
                            <span>{{ __('tax.sales.toggle_inclusive') }}</span>
                        </label>
                    </div>
                </div>

                {{-- Live Results --}}
                <div class="lg:col-span-7 bg-black/[0.02] dark:bg-white/[0.02] rounded-3xl p-6 border border-black/10 dark:border-white/10 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-black/10 dark:border-white/10">
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">{{ __('tax.sales.results_subtitle') }}</span>
                                <h4 class="text-lg font-bold text-black dark:text-white">{{ __('tax.sales.results_title') }}</h4>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/10 text-[#007AFF] dark:bg-blue-500/20">
                                <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                                <span>{{ __('tax.sales.law_badge') }}</span>
                            </span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4">
                            <div class="bg-white dark:bg-[#1C1C1E] p-3.5 rounded-2xl border border-black/10 dark:border-white/10 shadow-xs">
                                <div class="text-[11px] text-black/50 dark:text-white/50">{{ __('tax.sales.card_net_sales_dpp') }}</div>
                                <div class="text-base font-bold text-black dark:text-white mt-0.5" x-text="formatRupiah(salesTaxResult?.net_sales)"></div>
                            </div>
                            <div class="bg-white dark:bg-[#1C1C1E] p-3.5 rounded-2xl border border-black/10 dark:border-white/10 shadow-xs">
                                <div class="text-[11px] text-black/50 dark:text-white/50">{{ __('tax.sales.card_service_charge') }}</div>
                                <div class="text-base font-bold text-black dark:text-white mt-0.5" x-text="formatRupiah(salesTaxResult?.service_charge)"></div>
                            </div>
                            <div class="bg-white dark:bg-[#1C1C1E] p-3.5 rounded-2xl border border-black/10 dark:border-white/10 shadow-xs col-span-2 sm:col-span-1">
                                <div class="text-[11px] text-black/50 dark:text-white/50">{{ __('tax.sales.card_tax_pb1_ppn') }}</div>
                                <div class="text-base font-bold text-[#007AFF] mt-0.5" x-text="formatRupiah(salesTaxResult?.tax_amount)"></div>
                            </div>
                        </div>

                        {{-- Total Grand Total --}}
                        <div class="mt-4 p-4 rounded-2xl bg-[#007AFF]/10 border border-[#007AFF]/20 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-semibold text-[#007AFF] uppercase tracking-wider">{{ __('tax.sales.card_grand_total_highlight') }}</span>
                                <div class="text-2xl font-black text-black dark:text-white mt-0.5" x-text="formatRupiah(salesTaxResult?.grand_total)"></div>
                            </div>
                            <div class="text-right text-xs text-black/50 dark:text-white/50">
                                <div>{{ __('tax.sales.status_label') }} <strong class="text-black dark:text-white" x-text="salesTaxInput.is_inclusive ? @json(__('tax.sales.status_inclusive')) : @json(__('tax.sales.status_exclusive'))"></strong></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-black/10 dark:border-white/10 text-xs text-black/50 dark:text-white/50">
                        {{ __('tax.sales.pos_sync_notice') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 12-MONTH FISCAL BREAKDOWN TABLE (Senior-Friendly Bento Table) --}}
    <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl border border-black/10 dark:border-white/10 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-black/10 dark:border-white/10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="calendar-range" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>{{ __('tax.fiscal_table.title', ['year' => $currentYear]) }}</span>
                </h3>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">{{ __('tax.fiscal_table.subtitle') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('tax.export.net_income', ['year' => $currentYear, 'taxpayer_type' => $taxpayerType, 'ptkp_status' => $ptkpStatus]) }}" class="h-9 px-3.5 rounded-xl bg-black/[0.04] hover:bg-black/10 dark:bg-white/[0.06] dark:hover:bg-white/10 text-black dark:text-white text-xs font-semibold flex items-center gap-1.5 transition-colors border border-black/5 dark:border-white/5">
                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-emerald-600"></i>
                    <span>{{ __('tax.fiscal_table.download_csv') }}</span>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/10 dark:border-white/10 text-[11px] font-bold text-black/50 dark:text-white/50 uppercase tracking-wider">
                        <th class="py-3 px-4">{{ __('tax.fiscal_table.col_period') }}</th>
                        <th class="py-3 px-4 text-right">{{ __('tax.fiscal_table.col_revenue') }}</th>
                        <th class="py-3 px-4 text-right">{{ __('tax.fiscal_table.col_cogs') }}</th>
                        <th class="py-3 px-4 text-right">{{ __('tax.fiscal_table.col_expenses') }}</th>
                        <th class="py-3 px-4 text-right">{{ __('tax.fiscal_table.col_net_income') }}</th>
                        <th class="py-3 px-4 text-right">{{ __('tax.fiscal_table.col_tax_net_income') }}</th>
                        <th class="py-3 px-4 text-right">{{ __('tax.fiscal_table.col_tax_umkm') }}</th>
                        <th class="py-3 px-4 text-center">{{ __('tax.fiscal_table.col_recommendation') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5 text-xs font-medium">
                    @foreach($netIncomeSummary['monthly_breakdown'] as $m => $item)
                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 px-4 font-bold text-black dark:text-white">
                                {{ $item['month_name'] }}
                            </td>
                            <td class="py-3 px-4 text-right text-black/80 dark:text-white/80 font-mono">
                                {{ number_format($item['revenue'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right text-black/60 dark:text-white/60 font-mono">
                                {{ number_format($item['cogs'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right text-black/60 dark:text-white/60 font-mono">
                                {{ number_format($item['expenses'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold font-mono {{ $item['net_income'] < 0 ? 'text-[#FF3B30]' : 'text-black dark:text-white' }}">
                                {{ number_format($item['net_income'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold font-mono text-[#007AFF]">
                                {{ number_format($item['tax_amount'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-semibold font-mono text-black/80 dark:text-white/80">
                                {{ number_format($item['umkm_final_amount'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($item['recommendation'] === 'net_income')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/10 text-[#007AFF] dark:bg-blue-500/20">
                                        {{ __('tax.fiscal_table.rec_net_income') }}
                                    </span>
                                @elseif($item['recommendation'] === 'umkm_final')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-[#34C759] dark:bg-emerald-500/20">
                                        {{ __('tax.fiscal_table.rec_umkm') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-black/[0.05] text-black/70 dark:bg-white/10 dark:text-white/70">
                                        {{ __('tax.fiscal_table.rec_equal') }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-black/[0.03] dark:bg-white/[0.04] border-t-2 border-black/10 dark:border-white/10 text-xs font-bold text-black dark:text-white">
                        <td class="py-3.5 px-4 uppercase">{{ __('tax.fiscal_table.total_row') }}</td>
                        <td class="py-3.5 px-4 text-right font-mono">{{ number_format($netIncomeSummary['total_revenue_year'], 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-right font-mono">{{ number_format($netIncomeSummary['total_cogs_year'], 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-right font-mono">{{ number_format($netIncomeSummary['total_expenses_year'], 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-right font-mono text-emerald-600 dark:text-emerald-400">{{ number_format($netIncomeSummary['total_net_income_year'], 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-right font-mono text-[#007AFF]">{{ number_format($netIncomeSummary['total_net_tax_year'], 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-right font-mono text-black/80 dark:text-white/80">{{ number_format($netIncomeSummary['total_umkm_final_year'], 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-black bg-[#34C759] text-white">
                                {{ __('tax.fiscal_table.rec_selected') }}
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- MODAL SHEET 1: DETAIL 5 LAPISAN TARIF PROGRESIF PASAL 17 --}}
    <div x-show="showBracketModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-md" x-cloak>
        <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl max-w-xl w-full p-6 border border-black/10 dark:border-white/10 shadow-2xl relative" @click.outside="showBracketModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-black/10 dark:border-white/10">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-2xl bg-blue-500/10 dark:bg-blue-500/20 text-[#007AFF] flex items-center justify-center">
                        <i data-lucide="layers" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-black dark:text-white">{{ __('tax.brackets_modal.title') }}</h3>
                        <p class="text-xs text-black/50 dark:text-white/50">{{ __('tax.brackets_modal.legal_ref') }}</p>
                    </div>
                </div>
                <button type="button" @click="showBracketModal = false" class="w-8 h-8 rounded-full bg-black/[0.04] dark:bg-white/[0.06] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white flex items-center justify-center">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="mt-4 space-y-3">
                <template x-for="(b, idx) in (netIncomeResult?.tax_details?.brackets || [])" :key="idx">
                    <div class="p-3.5 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-black dark:text-white" x-text="b.bracket"></div>
                            <div class="text-[11px] text-black/50 dark:text-white/50 mt-0.5" x-text="'{{ addslashes(__('tax.brackets_modal.pkp_applied')) }}'.replace(':amount', formatRupiah(b.amount)).replace(':rate', b.rate_percent)"></div>
                        </div>
                        <div class="text-right font-black text-sm text-[#007AFF]" x-text="formatRupiah(b.tax)"></div>
                    </div>
                </template>
            </div>

            <div class="mt-5 pt-4 border-t border-black/10 dark:border-white/10 flex items-center justify-between">
                <span class="text-xs font-semibold text-black/50 dark:text-white/50">{{ __('tax.brackets_modal.total_tax') }}</span>
                <span class="text-lg font-black text-black dark:text-white" x-text="formatRupiah(netIncomeResult?.tax_amount)"></span>
            </div>
        </div>
    </div>

    {{-- MODAL SHEET 2: RINCIAN KOMPREHENSIF BPJS --}}
    <div x-show="showBpjsModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-md" x-cloak>
        <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl max-w-lg w-full p-6 border border-black/10 dark:border-white/10 shadow-2xl relative" @click.outside="showBpjsModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-black/10 dark:border-white/10">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-2xl bg-amber-500/10 dark:bg-amber-500/20 text-[#FF9500] flex items-center justify-center">
                        <i data-lucide="shield" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-black dark:text-white">{{ __('tax.bpjs_modal.title') }}</h3>
                        <p class="text-xs text-black/50 dark:text-white/50">{{ __('tax.bpjs_modal.subtitle') }}</p>
                    </div>
                </div>
                <button type="button" @click="showBpjsModal = false" class="w-8 h-8 rounded-full bg-black/[0.04] dark:bg-white/[0.06] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white flex items-center justify-center">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="mt-4 space-y-2.5 text-xs">
                <div class="p-3 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10">
                    <div class="font-bold text-black dark:text-white mb-1">{{ __('tax.bpjs_modal.item1_title') }}</div>
                    <ul class="space-y-1 text-black/70 dark:text-white/70">
                        <li>{{ __('tax.bpjs_modal.jkk_detail') }}</li>
                        <li>{{ __('tax.bpjs_modal.jkm_detail') }}</li>
                        <li>{{ __('tax.bpjs_modal.jht_detail') }}</li>
                        <li>{{ __('tax.bpjs_modal.jp_detail') }}</li>
                    </ul>
                </div>

                <div class="p-3 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10">
                    <div class="font-bold text-black dark:text-white mb-1">{{ __('tax.bpjs_modal.item2_title') }}</div>
                    <ul class="space-y-1 text-black/70 dark:text-white/70">
                        <li>{{ __('tax.bpjs_modal.kes_company') }}</li>
                        <li>{{ __('tax.bpjs_modal.kes_employee') }}</li>
                    </ul>
                </div>
            </div>

            <div class="mt-5 pt-4 border-t border-black/10 dark:border-white/10">
                <button type="button" @click="showBpjsModal = false" class="w-full py-2.5 rounded-xl bg-black dark:bg-white text-white dark:text-black hover:bg-black/80 dark:hover:bg-white/90 text-xs font-semibold">
                    {{ __('tax.actions.close') }}
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL SHEET 3: PANDUAN NORMA PENCATATAN (NPPN PASAL 14) --}}
    <div x-show="showNormaModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-md" x-cloak>
        <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl max-w-lg w-full p-6 border border-black/10 dark:border-white/10 shadow-2xl relative" @click.outside="showNormaModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-black/10 dark:border-white/10">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-2xl bg-blue-500/10 dark:bg-blue-500/20 text-[#007AFF] flex items-center justify-center">
                        <i data-lucide="book-open" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-black dark:text-white">{{ __('tax.norma_modal.title') }}</h3>
                        <p class="text-xs text-black/50 dark:text-white/50">{{ __('tax.norma_modal.legal_ref') }}</p>
                    </div>
                </div>
                <button type="button" @click="showNormaModal = false" class="w-8 h-8 rounded-full bg-black/[0.04] dark:bg-white/[0.06] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white flex items-center justify-center">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="mt-4 space-y-3 text-xs leading-relaxed text-black/70 dark:text-white/70">
                <p>
                    {!! __('tax.norma_modal.intro') !!}
                </p>
                <div class="p-3.5 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 space-y-1">
                    <div class="font-bold text-black dark:text-white">{{ __('tax.norma_modal.examples_title') }}</div>
                    <div>{{ __('tax.norma_modal.ex_retail') }}</div>
                    <div>{{ __('tax.norma_modal.ex_workshop') }}</div>
                    <div>{{ __('tax.norma_modal.ex_restaurant') }}</div>
                </div>
            </div>

            <div class="mt-5 pt-4 border-t border-black/10 dark:border-white/10">
                <button type="button" @click="showNormaModal = false" class="w-full py-2.5 rounded-xl bg-black dark:bg-white text-white dark:text-black hover:bg-black/80 dark:hover:bg-white/90 text-xs font-semibold">
                    {{ __('tax.actions.close') }}
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL SHEET 4: PUSAT EKSPOR E-BUPOT & REKAP CSV --}}
    <div x-show="showExportModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-md" x-cloak>
        <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl max-w-lg w-full p-6 border border-black/10 dark:border-white/10 shadow-2xl relative" @click.outside="showExportModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-black/10 dark:border-white/10">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-2xl bg-black/[0.04] dark:bg-white/[0.06] text-black dark:text-white flex items-center justify-center">
                        <i data-lucide="download-cloud" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-black dark:text-white">{{ __('tax.export_modal.title') }}</h3>
                        <p class="text-xs text-black/50 dark:text-white/50">{{ __('tax.export_modal.subtitle') }}</p>
                    </div>
                </div>
                <button type="button" @click="showExportModal = false" class="w-8 h-8 rounded-full bg-black/[0.04] dark:bg-white/[0.06] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white flex items-center justify-center">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="mt-4 space-y-3">
                {{-- Export Option 1: e-Bupot 21/26 --}}
                <div class="p-4 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-black dark:text-white">{{ __('tax.export_modal.item_ebupot_title') }}</div>
                        <div class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">{{ __('tax.export_modal.item_ebupot_desc') }}</div>
                    </div>
                    <a href="{{ route('tax.export.ebupot', ['year' => $currentYear, 'month' => (int) date('n')]) }}" class="h-9 px-3 rounded-xl bg-[#007AFF] hover:bg-[#007AFF]/90 text-white text-xs font-semibold flex items-center gap-1.5 transition-colors shadow-sm">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>{{ __('tax.export_modal.download_btn') }}</span>
                    </a>
                </div>

                {{-- Export Option 2: Rekap PPh Final UMKM --}}
                <div class="p-4 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-black dark:text-white">{{ __('tax.export_modal.item_umkm_title') }}</div>
                        <div class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">{{ __('tax.export_modal.item_umkm_desc') }}</div>
                    </div>
                    <a href="{{ route('tax.export.pph_final', ['year' => $currentYear, 'taxpayer_type' => $taxpayerType]) }}" class="h-9 px-3 rounded-xl bg-[#34C759] hover:bg-[#34C759]/90 text-white text-xs font-semibold flex items-center gap-1.5 transition-colors shadow-sm">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>{{ __('tax.export_modal.download_btn') }}</span>
                    </a>
                </div>

                {{-- Export Option 3: Rekap PPh Laba Bersih Tahunan --}}
                <div class="p-4 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-black dark:text-white">{{ __('tax.export_modal.item_net_income_title') }}</div>
                        <div class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">{{ __('tax.export_modal.item_net_income_desc') }}</div>
                    </div>
                    <a href="{{ route('tax.export.net_income', ['year' => $currentYear, 'taxpayer_type' => $taxpayerType, 'ptkp_status' => $ptkpStatus]) }}" class="h-9 px-3 rounded-xl bg-[#5856D6] hover:bg-[#5856D6]/90 text-white text-xs font-semibold flex items-center gap-1.5 transition-colors shadow-sm">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>{{ __('tax.export_modal.download_btn') }}</span>
                    </a>
                </div>
            </div>

            <div class="mt-5 pt-4 border-t border-black/10 dark:border-white/10">
                <button type="button" @click="showExportModal = false" class="w-full py-2.5 rounded-xl bg-black/[0.05] hover:bg-black/10 dark:bg-white/[0.06] dark:hover:bg-white/10 text-black dark:text-white text-xs font-semibold">
                    {{ __('tax.actions.close') }}
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
