@extends('layouts.app', [
    'title' => 'Pajak & Kepatuhan Usaha',
    'headerTitle' => 'Kepatuhan Pajak & Hasil Penjualan',
    'headerSubtitle' => 'Kalkulasi PPh Laba Bersih Usaha (UU HPP / Pasal 31E & 17), PPh Final UMKM 0.5% (PP 55/2022), PPh 21 TER, BPJS, dan THR'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-16" x-data="{
    activeSimTab: 'net_income',
    taxpayerType: '{{ $isIndividual ? 'individual' : 'corporate' }}',
    ptkpStatus: '{{ $ptkpStatus }}',
    currentYear: {{ $currentYear }},

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
            if (data.success) this.netIncomeResult = data.data;
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
            if (data.success) this.umkmResult = data.data;
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
            if (data.success) this.pph21Result = data.data;
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
            if (data.success) this.payrollResult = data.data;
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
            if (data.success) this.salesTaxResult = data.data;
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

    {{-- Filter Bar & Header Controls --}}
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 p-4 rounded-2xl bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                <i data-lucide="scale" class="w-5 h-5"></i>
            </div>
            <div>
                <h2 class="text-sm font-semibold text-black dark:text-white">Tax Compliance Engine &amp; Hasil Penjualan</h2>
                <p class="text-xs text-black/60 dark:text-white/60">Tahun Pajak {{ $currentYear }} &bull; {{ $business->name }}</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <form method="GET" action="{{ route('tax.index') }}" class="flex flex-wrap items-center gap-2 text-xs">
                <select name="taxpayer_type" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    <option value="individual" {{ $isIndividual ? 'selected' : '' }}>Wajib Pajak Orang Pribadi</option>
                    <option value="corporate" {{ ! $isIndividual ? 'selected' : '' }}>Badan Usaha PT/CV (Pasal 31E)</option>
                </select>

                @if ($isIndividual)
                    <select name="ptkp_status" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        <option value="TK/0" {{ $ptkpStatus === 'TK/0' ? 'selected' : '' }}>PTKP: TK/0 (Rp 54 Juta)</option>
                        <option value="TK/1" {{ $ptkpStatus === 'TK/1' ? 'selected' : '' }}>PTKP: TK/1 (Rp 58.5 Juta)</option>
                        <option value="TK/2" {{ $ptkpStatus === 'TK/2' ? 'selected' : '' }}>PTKP: TK/2 (Rp 63 Juta)</option>
                        <option value="TK/3" {{ $ptkpStatus === 'TK/3' ? 'selected' : '' }}>PTKP: TK/3 (Rp 67.5 Juta)</option>
                        <option value="K/0" {{ $ptkpStatus === 'K/0' ? 'selected' : '' }}>PTKP: K/0 (Rp 58.5 Juta)</option>
                        <option value="K/1" {{ $ptkpStatus === 'K/1' ? 'selected' : '' }}>PTKP: K/1 (Rp 63 Juta)</option>
                        <option value="K/2" {{ $ptkpStatus === 'K/2' ? 'selected' : '' }}>PTKP: K/2 (Rp 67.5 Juta)</option>
                        <option value="K/3" {{ $ptkpStatus === 'K/3' ? 'selected' : '' }}>PTKP: K/3 (Rp 72 Juta)</option>
                    </select>
                @endif

                <select name="year" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    @for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                        <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                    @endfor
                </select>
            </form>

            <a href="{{ route('tax.export.net_income', ['year' => $currentYear, 'taxpayer_type' => $isIndividual ? 'individual' : 'corporate', 'ptkp_status' => $ptkpStatus]) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 hover:bg-emerald-500/15 transition-colors flex items-center gap-1.5 shrink-0"
               title="Export CSV Laporan Pajak Hasil Penjualan & Laba Bersih">
                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                <span>Export Pajak Laba Bersih (CSV)</span>
            </a>

            <a href="{{ route('tax.export.pph_final', ['year' => $currentYear, 'taxpayer_type' => $isIndividual ? 'individual' : 'corporate']) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition-colors flex items-center gap-1.5 shrink-0"
               title="Export CSV Rekapitulasi PPh Final UMKM 0.5%">
                <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                <span>Export PPh Final (CSV)</span>
            </a>

            <a href="{{ route('tax.export.ebupot', ['year' => $currentYear, 'month' => date('n')]) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold text-[#5856D6] bg-[#5856D6]/10 hover:bg-[#5856D6]/15 transition-colors flex items-center gap-1.5 shrink-0"
               title="Export CSV DJP e-Bupot 21/26">
                <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                <span>Export e-Bupot 21/26</span>
            </a>
        </div>
    </div>

    {{-- Bento Grid 1: Ringkasan Hasil Penjualan & Laba Rugi Fiskal (KPI Cards) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        {{-- Card 1: Total Pendapatan Bersih (Net Sales Revenue) --}}
        <div class="p-5 rounded-2xl bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-black/60 dark:text-white/60">Pendapatan Bersih Penjualan</span>
                <div class="w-7 h-7 rounded-lg bg-blue-500/10 text-blue-500 flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold tracking-tight text-black dark:text-white font-mono tabular-nums">
                    Rp {{ number_format($netIncomeSummary['total_revenue_year'], 0, ',', '.') }}
                </div>
                <p class="text-xs text-black/50 dark:text-white/50 mt-1">
                    Total transaksi POS, Invoice &amp; Toko Online tahun {{ $currentYear }}
                </p>
            </div>
        </div>

        {{-- Card 2: Biaya Modal (HPP) & Biaya Operasional --}}
        <div class="p-5 rounded-2xl bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-black/60 dark:text-white/60">Modal (HPP) &amp; Beban Operasional</span>
                <div class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-4">
                @php
                    $totalCosts = $netIncomeSummary['total_cogs_year'] + $netIncomeSummary['total_expenses_year'];
                @endphp
                <div class="text-2xl font-bold tracking-tight text-black dark:text-white font-mono tabular-nums">
                    Rp {{ number_format($totalCosts, 0, ',', '.') }}
                </div>
                <div class="flex items-center justify-between text-[11px] text-black/50 dark:text-white/50 mt-1">
                    <span>HPP: Rp {{ number_format($netIncomeSummary['total_cogs_year'], 0, ',', '.') }}</span>
                    <span>Beban: Rp {{ number_format($netIncomeSummary['total_expenses_year'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Card 3: Laba Bersih Usaha Sebelum Pajak --}}
        <div class="p-5 rounded-2xl bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-black/60 dark:text-white/60">Laba Bersih Operasional</span>
                <div class="w-7 h-7 rounded-lg {{ $netIncomeSummary['total_net_income_year'] >= 0 ? 'bg-emerald-500/10 text-emerald-500' : 'bg-red-500/10 text-red-500' }} flex items-center justify-center">
                    <i data-lucide="{{ $netIncomeSummary['total_net_income_year'] >= 0 ? 'badge-percent' : 'alert-circle' }}" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold tracking-tight {{ $netIncomeSummary['total_net_income_year'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }} font-mono tabular-nums">
                    Rp {{ number_format($netIncomeSummary['total_net_income_year'], 0, ',', '.') }}
                </div>
                <p class="text-xs text-black/50 dark:text-white/50 mt-1">
                    {{ $netIncomeSummary['total_net_income_year'] >= 0 ? 'Laba Bersih = Pendapatan - HPP - Beban' : 'Rugi Usaha (Pajak Laba Bersih Rp 0)' }}
                </p>
            </div>
        </div>

        {{-- Card 4: PPh Laba Bersih Terutang & Optimasi Pajak --}}
        <div class="p-5 rounded-2xl bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-black/60 dark:text-white/60">PPh Terutang Laba Bersih</span>
                @php
                    $annualCalc = $netIncomeSummary['annual_calculation'];
                    $recommendedScheme = $annualCalc['comparison']['recommended_scheme'] ?? 'equal';
                    $taxSavings = (float) ($annualCalc['comparison']['tax_savings'] ?? 0);
                @endphp
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $recommendedScheme === 'net_income' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-blue-500/10 text-blue-600 dark:text-blue-400' }}">
                    {{ $annualCalc['comparison']['recommendation_label'] ?? 'Optimal' }}
                </span>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold tracking-tight text-black dark:text-white font-mono tabular-nums">
                    Rp {{ number_format($netIncomeSummary['total_net_tax_year'], 0, ',', '.') }}
                </div>
                <p class="text-xs text-black/50 dark:text-white/50 mt-1">
                    {{ ! $isIndividual ? 'Fasilitas Pasal 31E UU PPh (Tarif 11%)' : "Tarif Progresif UU HPP (PTKP {$ptkpStatus})" }}
                </p>
            </div>
        </div>
    </div>

    {{-- Bento Grid 2: Tabel Rekapitulasi 12 Bulan Laba Rugi Fiskal & Pajak Hasil Penjualan --}}
    <div class="p-5 rounded-2xl bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
            <div>
                <h3 class="text-sm font-semibold text-black dark:text-white">Rekapitulasi 12 Bulan Laba Rugi &amp; Pajak Hasil Penjualan</h3>
                <p class="text-xs text-black/60 dark:text-white/60">Kalkulasi akurat berdasarkan omzet riil, modal bahan (HPP), pengeluaran operasional, dan komparasi skema fiskal</p>
            </div>

            {{-- Tax Law Indicator Badge --}}
            <div class="flex items-center gap-2 p-2 rounded-xl bg-black/[0.03] dark:bg-white/[0.04] text-[11px] text-black/70 dark:text-white/70">
                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                <span>Dasar Hukum: <strong class="text-black dark:text-white">{{ ! $isIndividual ? 'Pasal 31E UU PPh (Diskon 50%)' : 'Pasal 17 UU HPP No. 7/2021' }}</strong></span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10 text-black/50 dark:text-white/50 font-medium">
                        <th class="py-2.5 px-3">Masa Pajak</th>
                        <th class="py-2.5 px-3 text-right">Pendapatan Bersih</th>
                        <th class="py-2.5 px-3 text-right">Biaya Modal (HPP)</th>
                        <th class="py-2.5 px-3 text-right">Laba Kotor</th>
                        <th class="py-2.5 px-3 text-right">Beban Operasional</th>
                        <th class="py-2.5 px-3 text-right">Laba Bersih</th>
                        <th class="py-2.5 px-3 text-right">PPh Laba Bersih</th>
                        <th class="py-2.5 px-3 text-right">PPh Final 0.5%</th>
                        <th class="py-2.5 px-3 text-center">Skema Rekomendasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5 font-mono">
                    @foreach ($netIncomeSummary['monthly_breakdown'] as $row)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition">
                            <td class="py-2.5 px-3 font-sans font-medium text-black dark:text-white">
                                {{ $row['month_name'] }}
                            </td>
                            <td class="py-2.5 px-3 text-right text-black/80 dark:text-white/80 tabular-nums">
                                Rp {{ number_format($row['revenue'], 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-right text-black/60 dark:text-white/60 tabular-nums">
                                Rp {{ number_format($row['cogs'], 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-medium text-black dark:text-white tabular-nums">
                                Rp {{ number_format($row['gross_profit'], 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-right text-black/60 dark:text-white/60 tabular-nums">
                                Rp {{ number_format($row['expenses'], 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-semibold {{ $row['net_income'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' }} tabular-nums">
                                Rp {{ number_format($row['net_income'], 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-bold text-black dark:text-white tabular-nums">
                                Rp {{ number_format($row['tax_amount'], 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-right text-black/60 dark:text-white/60 tabular-nums">
                                Rp {{ number_format($row['umkm_final_amount'], 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-center font-sans">
                                @if ($row['recommendation'] === 'net_income')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                        Laba Bersih (Hemat)
                                    </span>
                                @elseif ($row['recommendation'] === 'umkm_final')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-500/10 text-blue-600 dark:text-blue-400">
                                        PPh Final 0.5%
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-500/10 text-slate-600 dark:text-slate-400">
                                        Setara
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t-2 border-black/10 dark:border-white/10 font-mono font-bold bg-black/[0.02] dark:bg-white/[0.02]">
                    <tr>
                        <td class="py-3 px-3 font-sans text-black dark:text-white">TOTAL TAHUNAN</td>
                        <td class="py-3 px-3 text-right text-black dark:text-white tabular-nums">Rp {{ number_format($netIncomeSummary['total_revenue_year'], 0, ',', '.') }}</td>
                        <td class="py-3 px-3 text-right text-black/70 dark:text-white/70 tabular-nums">Rp {{ number_format($netIncomeSummary['total_cogs_year'], 0, ',', '.') }}</td>
                        <td class="py-3 px-3 text-right text-black dark:text-white tabular-nums">Rp {{ number_format($netIncomeSummary['total_gross_profit_year'], 0, ',', '.') }}</td>
                        <td class="py-3 px-3 text-right text-black/70 dark:text-white/70 tabular-nums">Rp {{ number_format($netIncomeSummary['total_expenses_year'], 0, ',', '.') }}</td>
                        <td class="py-3 px-3 text-right {{ $netIncomeSummary['total_net_income_year'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' }} tabular-nums">Rp {{ number_format($netIncomeSummary['total_net_income_year'], 0, ',', '.') }}</td>
                        <td class="py-3 px-3 text-right text-emerald-600 dark:text-emerald-400 tabular-nums">Rp {{ number_format($netIncomeSummary['total_net_tax_year'], 0, ',', '.') }}</td>
                        <td class="py-3 px-3 text-right text-blue-600 dark:text-blue-400 tabular-nums">Rp {{ number_format($netIncomeSummary['total_umkm_final_year'], 0, ',', '.') }}</td>
                        <td class="py-3 px-3 text-center font-sans text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                            {{ $annualCalc['comparison']['recommendation_label'] ?? '-' }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Bento Grid 3: Tabel Rekapitulasi PPh Final UMKM 0.5% (PP 55/2022) --}}
    <div class="p-5 rounded-2xl bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-sm" x-data="{ showFinalTable: true }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
            <div class="flex items-center gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-black dark:text-white">Rekapitulasi Bulanan PPh Final UMKM 0.5% (PP 55/2022)</h3>
                    <p class="text-xs text-black/60 dark:text-white/60">Pantauan omzet peredaran bruto dan kewajiban setor per masa pajak</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                {{-- DJP Billing Quick Info Badge --}}
                <div class="flex items-center gap-2 p-2 rounded-xl bg-black/[0.03] dark:bg-white/[0.04] text-[11px] text-black/70 dark:text-white/70">
                    <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                    <span>Kode Billing DJP: <strong class="text-black dark:text-white">KAP 411128</strong> &bull; <strong class="text-black dark:text-white">KJS 420</strong></span>
                </div>

                <button type="button" @click="showFinalTable = !showFinalTable" class="px-3 py-1.5 rounded-xl border border-black/10 dark:border-white/10 text-xs font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">
                    <span x-text="showFinalTable ? 'Sembunyikan' : 'Tampilkan'"></span>
                </button>
            </div>
        </div>

        <div x-show="showFinalTable" class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10 text-black/50 dark:text-white/50 font-medium">
                        <th class="py-2.5 px-3">Masa Pajak</th>
                        <th class="py-2.5 px-3 text-right">Omzet Invoice</th>
                        <th class="py-2.5 px-3 text-right">Omzet POS</th>
                        <th class="py-2.5 px-3 text-right">Total Omzet</th>
                        <th class="py-2.5 px-3 text-right">Kumulatif Omzet</th>
                        <th class="py-2.5 px-3 text-right">DPP Kena Pajak</th>
                        <th class="py-2.5 px-3 text-right">PPh Final (0.5%)</th>
                        <th class="py-2.5 px-3 text-center">Status Ketetapan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5 font-mono">
                    @foreach ($umkmSummary['monthly_breakdown'] as $row)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition">
                            <td class="py-2.5 px-3 font-sans font-medium text-black dark:text-white">
                                {{ $row['month_name'] }}
                            </td>
                            <td class="py-2.5 px-3 text-right text-black/70 dark:text-white/70 tabular-nums">
                                Rp {{ number_format($row['invoice_revenue'], 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-right text-black/70 dark:text-white/70 tabular-nums">
                                Rp {{ number_format($row['pos_revenue'], 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-semibold text-black dark:text-white tabular-nums">
                                Rp {{ number_format($row['gross_revenue'], 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-right text-black/60 dark:text-white/60 tabular-nums">
                                Rp {{ number_format($row['cumulative_revenue'], 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-right text-black/70 dark:text-white/70 tabular-nums">
                                Rp {{ number_format($row['taxable_revenue'], 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">
                                Rp {{ number_format($row['tax_amount'], 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-center font-sans">
                                @if ($row['tax_amount'] <= 0)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                        Bebas Pajak (0%)
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-500/10 text-amber-600 dark:text-amber-400">
                                        Terutang 0.5%
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Bento Grid 4: Interactive Simulators Suite (Tabbed Interface) --}}
    <div class="p-6 rounded-2xl bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-black/5 dark:border-white/10 pb-4 mb-6">
            <div>
                <h3 class="text-sm font-semibold text-black dark:text-white">Kalkulator Pajak &amp; Payroll Interaktif</h3>
                <p class="text-xs text-black/60 dark:text-white/60">Simulasikan perhitungan real-time sesuai regulasi ketenagakerjaan dan perpajakan Indonesia</p>
            </div>

            {{-- Segmented Tab Picker (Apple HIG style) --}}
            <div class="flex items-center p-1 rounded-xl bg-black/5 dark:bg-white/10 text-xs font-medium self-start lg:self-auto overflow-x-auto max-w-full">
                <button type="button" @click="activeSimTab = 'net_income'" :class="activeSimTab === 'net_income' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    PPh Laba Bersih (Penjualan)
                </button>
                <button type="button" @click="activeSimTab = 'umkm'" :class="activeSimTab === 'umkm' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    PPh Final UMKM (0.5%)
                </button>
                <button type="button" @click="activeSimTab = 'pph21'" :class="activeSimTab === 'pph21' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    PPh 21 TER
                </button>
                <button type="button" @click="activeSimTab = 'payroll'" :class="activeSimTab === 'payroll' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    Payroll, BPJS &amp; THR
                </button>
                <button type="button" @click="activeSimTab = 'sales'" :class="activeSimTab === 'sales' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    PB1 &amp; PPN Penjualan
                </button>
            </div>
        </div>

        {{-- TAB 1: SIMULATOR PPH LABA BERSIH & HASIL PENJUALAN --}}
        <div x-show="activeSimTab === 'net_income'" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- Input Column (5 cols) --}}
                <div class="lg:col-span-5 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-black/70 dark:text-white/70 mb-1">Peredaran Bruto / Penjualan Bersih (Rp)</label>
                        <input type="number" x-model.number="netIncomeInput.gross_revenue" @input.debounce.300ms="runNetIncomeSim()" class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-sm text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        <p class="text-[11px] text-black/50 dark:text-white/50 mt-1">Total seluruh pendapatan penjualan produk dan jasa</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-black/70 dark:text-white/70 mb-1">Biaya Modal / HPP (Rp)</label>
                            <input type="number" x-model.number="netIncomeInput.cogs" @input.debounce.300ms="runNetIncomeSim()" class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-sm text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-black/70 dark:text-white/70 mb-1">Beban Operasional (Rp)</label>
                            <input type="number" x-model.number="netIncomeInput.operating_expenses" @input.debounce.300ms="runNetIncomeSim()" class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-sm text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-black/70 dark:text-white/70 mb-1">Bentuk Badan Hukum / Wajib Pajak</label>
                        <select x-model="netIncomeInput.is_corporate" @change="runNetIncomeSim()" class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option :value="false">Orang Pribadi (Tarif Progresif 5 Layer UU HPP)</option>
                            <option :value="true">Badan Usaha PT/CV (Pasal 31E UU PPh - Diskon 50% / Tarif 11%)</option>
                        </select>
                    </div>

                    <template x-if="!netIncomeInput.is_corporate">
                        <div class="space-y-3 pt-1">
                            <div>
                                <label class="block text-xs font-medium text-black/70 dark:text-white/70 mb-1">Status Tanggungan PTKP</label>
                                <select x-model="netIncomeInput.ptkp_status" @change="runNetIncomeSim()" class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                    <option value="TK/0">TK/0 - Belum Menikah, Tanpa Tanggungan (Rp 54 Juta)</option>
                                    <option value="TK/1">TK/1 - Belum Menikah, 1 Tanggungan (Rp 58.5 Juta)</option>
                                    <option value="TK/2">TK/2 - Belum Menikah, 2 Tanggungan (Rp 63 Juta)</option>
                                    <option value="TK/3">TK/3 - Belum Menikah, 3 Tanggungan (Rp 67.5 Juta)</option>
                                    <option value="K/0">K/0 - Menikah, Tanpa Tanggungan (Rp 58.5 Juta)</option>
                                    <option value="K/1">K/1 - Menikah, 1 Tanggungan (Rp 63 Juta)</option>
                                    <option value="K/2">K/2 - Menikah, 2 Tanggungan (Rp 67.5 Juta)</option>
                                    <option value="K/3">K/3 - Menikah, 3 Tanggungan (Rp 72 Juta)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-black/70 dark:text-white/70 mb-1">Metode Penghitungan</label>
                                <select x-model.number="netIncomeInput.nppn_rate" @change="runNetIncomeSim()" class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                    <option :value="0">Pembukuan Riil (Pendapatan - HPP - Beban Operasional)</option>
                                    <option :value="0.20">NPPN Norma 20% (Perdagangan Eceran / Toko)</option>
                                    <option :value="0.25">NPPN Norma 25% (Jasa Konsultasi / Servis)</option>
                                    <option :value="0.30">NPPN Norma 30% (Jasa Profesional / Restoran)</option>
                                    <option :value="0.50">NPPN Norma 50% (Pekerjaan Bebas / Ahli)</option>
                                </select>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Output Column (7 cols) --}}
                <div class="lg:col-span-7 p-5 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/10 space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-semibold text-black/70 dark:text-white/70">Hasil Analisis Laba Rugi &amp; Beban Pajak:</h4>
                        <template x-if="netIncomeResult">
                            <span class="text-[11px] font-medium text-[#007AFF]" x-text="netIncomeResult.tax_scheme"></span>
                        </template>
                    </div>

                    <template x-if="netIncomeResult">
                        <div class="space-y-3 text-xs font-mono">
                            {{-- Baris Laba Rugi --}}
                            <div class="grid grid-cols-2 gap-3 p-3 rounded-xl bg-black/[0.02] dark:bg-white/[0.03]">
                                <div>
                                    <span class="text-black/50 dark:text-white/50 block text-[11px]">Laba Kotor (Gross Profit):</span>
                                    <span class="font-bold text-black dark:text-white text-sm" x-text="formatRupiah(netIncomeResult.gross_profit)"></span>
                                    <span class="text-[10px] text-black/40 dark:text-white/40 block mt-0.5" x-text="'Margin: ' + netIncomeResult.gross_margin_percent + '%'"></span>
                                </div>
                                <div>
                                    <span class="text-black/50 dark:text-white/50 block text-[11px]">Laba Bersih Operasional:</span>
                                    <span class="font-bold text-sm" :class="netIncomeResult.net_operating_income >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500'" x-text="formatRupiah(netIncomeResult.net_operating_income)"></span>
                                    <span class="text-[10px] text-black/40 dark:text-white/40 block mt-0.5" x-text="'Net Margin: ' + netIncomeResult.net_margin_percent + '%'"></span>
                                </div>
                            </div>

                            {{-- Rincian Penghitungan Pajak --}}
                            <div class="space-y-2 pt-1">
                                <div class="flex justify-between">
                                    <span class="text-black/60 dark:text-white/60">Penghasilan Kena Pajak (PKP):</span>
                                    <span class="font-bold text-black dark:text-white" x-text="formatRupiah(netIncomeResult.taxable_income)"></span>
                                </div>

                                <template x-if="netIncomeResult.ptkp_amount > 0">
                                    <div class="flex justify-between text-[11px]">
                                        <span class="text-black/60 dark:text-white/60">Pengurang PTKP (<span x-text="netIncomeResult.ptkp_status"></span>):</span>
                                        <span class="font-medium text-emerald-600 dark:text-emerald-400" x-text="'- ' + formatRupiah(netIncomeResult.ptkp_amount)"></span>
                                    </div>
                                </template>

                                <div class="flex justify-between">
                                    <span class="text-black/60 dark:text-white/60">PPh Terutang Laba Bersih:</span>
                                    <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="formatRupiah(netIncomeResult.tax_amount)"></span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-black/60 dark:text-white/60">Pembanding PPh Final UMKM 0.5%:</span>
                                    <span class="font-bold text-blue-600 dark:text-blue-400" x-text="formatRupiah(netIncomeResult.comparison.umkm_final_amount)"></span>
                                </div>
                            </div>

                            {{-- Tax Optimization Advisor Box --}}
                            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 font-sans" :class="netIncomeResult.comparison.recommended_scheme === 'net_income' ? 'bg-emerald-500/10 text-emerald-950 dark:text-emerald-100' : 'bg-blue-500/10 text-blue-950 dark:text-blue-100'">
                                <div class="flex items-center justify-between font-bold text-xs">
                                    <span class="flex items-center gap-1.5">
                                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                                        <span x-text="netIncomeResult.comparison.recommendation_label"></span>
                                    </span>
                                    <template x-if="netIncomeResult.comparison.tax_savings > 0">
                                        <span class="text-[11px] font-semibold underline" x-text="'Hemat ' + formatRupiah(netIncomeResult.comparison.tax_savings)"></span>
                                    </template>
                                </div>
                                <p class="text-[11px] mt-1 text-black/70 dark:text-white/80" x-text="netIncomeResult.comparison.rationale"></p>
                            </div>

                            {{-- Net Profit after Tax --}}
                            <div class="pt-3 border-t border-black/5 dark:border-white/10 flex justify-between items-center text-sm font-bold">
                                <span class="text-black dark:text-white font-sans">Laba Bersih Bersih Setelah Pajak (EAT):</span>
                                <span class="text-emerald-600 dark:text-emerald-400" x-text="formatRupiah(netIncomeResult.net_profit_after_tax)"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- TAB 2: SIMULATOR PPH FINAL UMKM --}}
        <div x-show="activeSimTab === 'umkm'" class="space-y-6" style="display: none;">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-black/70 dark:text-white/70 mb-1">Omzet Bulan Berjalan (Rp)</label>
                        <input type="number" x-model.number="umkmInput.monthly_revenue" @input.debounce.300ms="runUmkmSim()" class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-sm text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-black/70 dark:text-white/70 mb-1">Akumulasi Omzet Bulan-Bulan Sebelumnya (Tahun yang Sama)</label>
                        <input type="number" x-model.number="umkmInput.prior_cumulative" @input.debounce.300ms="runUmkmSim()" class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-sm text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-black/70 dark:text-white/70 mb-1">Status Wajib Pajak</label>
                        <select x-model="umkmInput.is_individual" @change="runUmkmSim()" class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option :value="true">Orang Pribadi (Mendapat Hak Bebas Pajak s/d Rp 500 Juta)</option>
                            <option :value="false">Badan Usaha PT/CV (Tarif 0.5% sejak Rupiah pertama)</option>
                        </select>
                    </div>
                </div>

                {{-- Hasil Perhitungan UMKM --}}
                <div class="p-5 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/10 space-y-3">
                    <h4 class="text-xs font-semibold text-black/70 dark:text-white/70">Hasil Kalkulasi PP 55/2022:</h4>
                    <template x-if="umkmResult">
                        <div class="space-y-2.5 text-xs font-mono">
                            <div class="flex justify-between">
                                <span class="text-black/60 dark:text-white/60">Kumulatif Omzet Baru:</span>
                                <span class="font-bold text-black dark:text-white" x-text="formatRupiah(umkmResult.new_cumulative)"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-black/60 dark:text-white/60">DPP Dikenakan Pajak:</span>
                                <span class="font-bold text-black dark:text-white" x-text="formatRupiah(umkmResult.taxable_revenue)"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-black/60 dark:text-white/60">Tarif Pajak:</span>
                                <span class="font-bold text-[#007AFF]" x-text="umkmResult.tax_rate_percent"></span>
                            </div>
                            <div class="pt-2 border-t border-black/5 dark:border-white/10 flex justify-between items-center text-sm font-bold">
                                <span class="text-black dark:text-white font-sans">PPh Final Disetor:</span>
                                <span class="text-emerald-600 dark:text-emerald-400" x-text="formatRupiah(umkmResult.tax_amount)"></span>
                            </div>
                            <p class="text-[11px] font-sans text-black/60 dark:text-white/60 mt-2 p-2.5 rounded-lg bg-black/[0.03] dark:bg-white/[0.05]" x-text="umkmResult.status"></p>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- TAB 3: SIMULATOR PPH 21 TER --}}
        <div x-show="activeSimTab === 'pph21'" class="space-y-6" style="display: none;">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-black/70 dark:text-white/70 mb-1">Skema Perhitungan</label>
                        <select x-model="pph21Input.calc_type" @change="runPph21Sim()" class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="monthly_ter">Bulanan Reguler (TER PP 58/2023)</option>
                            <option value="december">Masa Desember (Rekonsiliasi Pasal 17 UU HPP)</option>
                            <option value="daily_worker">Pekerja Harian Lepas (Daily Worker)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-black/70 dark:text-white/70 mb-1">
                            <span x-text="pph21Input.calc_type === 'december' ? 'Total Bruto Setahun (Rp)' : (pph21Input.calc_type === 'daily_worker' ? 'Upah Harian (Rp)' : 'Penghasilan Bruto Bulan Ini (Rp)')"></span>
                        </label>
                        <input type="number" x-model.number="pph21Input.gross_wage" @input.debounce.300ms="runPph21Sim()" class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-sm text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-black/70 dark:text-white/70 mb-1">Status PTKP Karyawan</label>
                        <select x-model="pph21Input.ptkp_status" @change="runPph21Sim()" class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="TK/0">TK/0 (Lajang, Tanpa Tanggungan - TER A)</option>
                            <option value="TK/1">TK/1 (Lajang, 1 Tanggungan - TER A)</option>
                            <option value="K/0">K/0 (Menikah, Tanpa Tanggungan - TER A)</option>
                            <option value="TK/2">TK/2 (Lajang, 2 Tanggungan - TER B)</option>
                            <option value="TK/3">TK/3 (Lajang, 3 Tanggungan - TER B)</option>
                            <option value="K/1">K/1 (Menikah, 1 Tanggungan - TER B)</option>
                            <option value="K/2">K/2 (Menikah, 2 Tanggungan - TER B)</option>
                            <option value="K/3">K/3 (Menikah, 3 Tanggungan - TER C)</option>
                        </select>
                    </div>

                    <template x-if="pph21Input.calc_type === 'december'">
                        <div class="space-y-3 pt-2">
                            <div>
                                <label class="block text-xs font-medium text-black/70 dark:text-white/70 mb-1">Total Iuran JHT &amp; JP Karyawan Setahun (Rp)</label>
                                <input type="number" x-model.number="pph21Input.annual_deductions" @input.debounce.300ms="runPph21Sim()" class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-sm text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-black/70 dark:text-white/70 mb-1">Total PPh 21 Telah Dipotong (Jan - Nov)</label>
                                <input type="number" x-model.number="pph21Input.tax_paid_before" @input.debounce.300ms="runPph21Sim()" class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-sm text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Output PPh 21 --}}
                <div class="p-5 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/10 space-y-3">
                    <h4 class="text-xs font-semibold text-black/70 dark:text-white/70">Hasil Perhitungan PPh 21:</h4>
                    <template x-if="pph21Result">
                        <div class="space-y-2.5 text-xs font-mono">
                            <template x-if="pph21Input.calc_type === 'monthly_ter'">
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-black/60 dark:text-white/60">Kategori TER:</span>
                                        <span class="font-bold text-[#007AFF]">Kategori <span x-text="pph21Result.ter_category"></span></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-black/60 dark:text-white/60">Tarif Efektif:</span>
                                        <span class="font-bold text-black dark:text-white" x-text="pph21Result.ter_rate_percent"></span>
                                    </div>
                                    <div class="pt-2 border-t border-black/5 dark:border-white/10 flex justify-between items-center text-sm font-bold">
                                        <span class="text-black dark:text-white font-sans">Potongan PPh 21:</span>
                                        <span class="text-red-600 dark:text-red-400" x-text="formatRupiah(pph21Result.pph21_amount)"></span>
                                    </div>
                                </div>
                            </template>

                            <template x-if="pph21Input.calc_type === 'december'">
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-black/60 dark:text-white/60">Biaya Jabatan (5% max 6jt):</span>
                                        <span class="font-bold text-black dark:text-white" x-text="formatRupiah(pph21Result.biaya_jabatan)"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-black/60 dark:text-white/60">Penghasilan Kena Pajak (PKP):</span>
                                        <span class="font-bold text-black dark:text-white" x-text="formatRupiah(pph21Result.pkp)"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-black/60 dark:text-white/60">PPh 21 Setahun Penuh:</span>
                                        <span class="font-bold text-black dark:text-white" x-text="formatRupiah(pph21Result.annual_pph21_total)"></span>
                                    </div>
                                    <div class="pt-2 border-t border-black/5 dark:border-white/10 flex justify-between items-center text-sm font-bold">
                                        <span class="text-black dark:text-white font-sans">PPh 21 Masa Desember:</span>
                                        <span class="text-red-600 dark:text-red-400" x-text="formatRupiah(pph21Result.pph21_december)"></span>
                                    </div>
                                </div>
                            </template>

                            <template x-if="pph21Input.calc_type === 'daily_worker'">
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-black/60 dark:text-white/60">Kategori Pekerja:</span>
                                        <span class="font-medium text-black dark:text-white" x-text="pph21Result.category"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-black/60 dark:text-white/60">Tarif:</span>
                                        <span class="font-bold text-[#007AFF]" x-text="pph21Result.tax_rate_percent"></span>
                                    </div>
                                    <div class="pt-2 border-t border-black/5 dark:border-white/10 flex justify-between items-center text-sm font-bold">
                                        <span class="text-black dark:text-white font-sans">Potongan PPh 21 Harian:</span>
                                        <span class="text-red-600 dark:text-red-400" x-text="formatRupiah(pph21Result.pph21_daily_amount)"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- TAB 4: SIMULATOR PAYROLL, BPJS & THR BERDASARKAN JOIN DATE --}}
        <div x-show="activeSimTab === 'payroll'" class="space-y-6" style="display: none;">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-black/70 dark:text-white/70 mb-1">Tipe Pekerja</label>
                            <select x-model="payrollInput.employment_type" @change="runPayrollSim()" class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                <option value="permanent">Karyawan Tetap / Kontrak</option>
                                <option value="daily_worker">Pekerja Harian Lepas</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-black/70 dark:text-white/70 mb-1">Tanggal Bergabung (Join Date)</label>
                            <input type="date" x-model="payrollInput.join_date" @change="runPayrollSim()" class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                    </div>

                    <template x-if="payrollInput.employment_type === 'permanent'">
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-black/70 dark:text-white/70 mb-1">Gaji Pokok (Rp)</label>
                                    <input type="number" x-model.number="payrollInput.base_salary" @input.debounce.300ms="runPayrollSim()" class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-xs text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-black/70 dark:text-white/70 mb-1">Tunjangan Tetap (Rp)</label>
                                    <input type="number" x-model.number="payrollInput.fixed_allowances" @input.debounce.300ms="runPayrollSim()" class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-xs text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-black/70 dark:text-white/70 mb-1">Potongan Cicilan Kasbon (Rp)</label>
                                    <input type="number" x-model.number="payrollInput.loan_deduction" @input.debounce.300ms="runPayrollSim()" class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-xs text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-black/70 dark:text-white/70 mb-1">Cairkan THR Bulan Ini?</label>
                                    <select x-model="payrollInput.include_thr" @change="runPayrollSim()" class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                        <option :value="true">Ya, Hitung Prorata THR</option>
                                        <option :value="false">Tidak (Bulan Biasa)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="payrollInput.employment_type === 'daily_worker'">
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-black/70 dark:text-white/70 mb-1">Upah Harian (Rp)</label>
                                    <input type="number" x-model.number="payrollInput.daily_rate" @input.debounce.300ms="runPayrollSim()" class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-xs text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-black/70 dark:text-white/70 mb-1">Jumlah Hari Kerja</label>
                                    <input type="number" x-model.number="payrollInput.days_worked" @input.debounce.300ms="runPayrollSim()" class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-xs text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Output Payroll & THR --}}
                <div class="p-5 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/10 space-y-3">
                    <h4 class="text-xs font-semibold text-black/70 dark:text-white/70">Rincian Slip Gaji &amp; Beban Perusahaan:</h4>
                    <template x-if="payrollResult">
                        <div class="space-y-2 text-xs font-mono">
                            <div class="flex justify-between">
                                <span class="text-black/60 dark:text-white/60">Total Bruto Penghasilan:</span>
                                <span class="font-bold text-black dark:text-white" x-text="formatRupiah(payrollResult.earnings.employee_gross_pay || payrollResult.earnings.gross_pay)"></span>
                            </div>

                            <template x-if="payrollResult.earnings.thr_amount > 0">
                                <div class="p-2 rounded-lg bg-purple-500/10 text-purple-600 dark:text-purple-400 font-sans text-[11px] space-y-0.5">
                                    <div class="flex justify-between font-bold">
                                        <span>THR Keagamaan Prorata:</span>
                                        <span x-text="formatRupiah(payrollResult.earnings.thr_amount)"></span>
                                    </div>
                                    <p class="text-[10px]" x-text="payrollResult.earnings.thr_details?.calculation_formula"></p>
                                </div>
                            </template>

                            <template x-if="payrollResult.bpjs">
                                <div class="pt-2 border-t border-black/5 dark:border-white/10 space-y-1 text-[11px]">
                                    <div class="flex justify-between">
                                        <span class="text-black/60 dark:text-white/60">Iuran BPJS Karyawan:</span>
                                        <span class="text-red-500" x-text="'- ' + formatRupiah(payrollResult.deductions.bpjs_employee)"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-black/60 dark:text-white/60">Potongan PPh 21 TER:</span>
                                        <span class="text-red-500" x-text="'- ' + formatRupiah(payrollResult.deductions.pph21)"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-black/60 dark:text-white/60">Potongan Cicilan Kasbon:</span>
                                        <span class="text-red-500" x-text="'- ' + formatRupiah(payrollResult.deductions.loan_installment)"></span>
                                    </div>
                                </div>
                            </template>

                            <div class="pt-3 border-t border-black/5 dark:border-white/10 flex justify-between items-center text-sm font-bold">
                                <span class="text-black dark:text-white font-sans">Take Home Pay Karyawan:</span>
                                <span class="text-emerald-600 dark:text-emerald-400" x-text="formatRupiah(payrollResult.take_home_pay)"></span>
                            </div>

                            <div class="flex justify-between items-center text-[11px] pt-1 text-black/50 dark:text-white/50">
                                <span>Total Beban Biaya Perusahaan:</span>
                                <span class="font-bold" x-text="formatRupiah(payrollResult.company_total_cost)"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- TAB 5: SIMULATOR PAJAK PENJUALAN (PB1 & PPN) --}}
        <div x-show="activeSimTab === 'sales'" class="space-y-6" style="display: none;">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-black/70 dark:text-white/70 mb-1">Nilai Belanja Subtotal (Rp)</label>
                        <input type="number" x-model.number="salesTaxInput.subtotal" @input.debounce.300ms="runSalesTaxSim()" class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-sm text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-black/70 dark:text-white/70 mb-1">Diskon (Rp)</label>
                            <input type="number" x-model.number="salesTaxInput.discount" @input.debounce.300ms="runSalesTaxSim()" class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-sm text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-black/70 dark:text-white/70 mb-1">Service Charge (%)</label>
                            <select x-model.number="salesTaxInput.service_charge_rate" @change="runSalesTaxSim()" class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                <option :value="0">0% (Tanpa Layanan)</option>
                                <option :value="0.05">5% (Restoran Standard)</option>
                                <option :value="0.07">7% (Hotel / Lounge)</option>
                                <option :value="0.10">10% (Fine Dining)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-black/70 dark:text-white/70 mb-1">Tipe Pajak</label>
                            <select x-model="salesTaxInput.tax_type" @change="runSalesTaxSim()" class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                <option value="pb1">PB1 Restoran / Kafe (10%)</option>
                                <option value="ppn_11">PPN Standar (11%)</option>
                                <option value="ppn_12">PPN Standar (12%)</option>
                                <option value="none">Bebas Pajak (0%)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-black/70 dark:text-white/70 mb-1">Metode Harga</label>
                            <select x-model="salesTaxInput.is_inclusive" @change="runSalesTaxSim()" class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                <option :value="false">Harga Eksklusif Pajak</option>
                                <option :value="true">Harga Inklusif Pajak</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Output Sales Tax --}}
                <div class="p-5 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/10 space-y-3">
                    <h4 class="text-xs font-semibold text-black/70 dark:text-white/70">Struktur Struk Kasir / Invoice:</h4>
                    <template x-if="salesTaxResult">
                        <div class="space-y-2 text-xs font-mono">
                            <div class="flex justify-between">
                                <span class="text-black/60 dark:text-white/60">Subtotal Netto:</span>
                                <span class="font-bold text-black dark:text-white" x-text="formatRupiah(salesTaxResult.net_subtotal)"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-black/60 dark:text-white/60">Biaya Layanan:</span>
                                <span class="font-bold text-black dark:text-white" x-text="formatRupiah(salesTaxResult.service_charge)"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-black/60 dark:text-white/60">Dasar Pengenaan Pajak (DPP):</span>
                                <span class="font-bold text-black dark:text-white" x-text="formatRupiah(salesTaxResult.dpp)"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-black/60 dark:text-white/60">Pajak (<span x-text="salesTaxResult.tax_rate_percent"></span>):</span>
                                <span class="font-bold text-blue-600 dark:text-blue-400" x-text="formatRupiah(salesTaxResult.tax_amount)"></span>
                            </div>
                            <div class="pt-3 border-t border-black/5 dark:border-white/10 flex justify-between items-center text-sm font-bold">
                                <span class="text-black dark:text-white font-sans">Grand Total Transaksi:</span>
                                <span class="text-emerald-600 dark:text-emerald-400" x-text="formatRupiah(salesTaxResult.grand_total)"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
