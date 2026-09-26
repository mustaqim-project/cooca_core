@extends('layouts.app', ['title' => 'Neraca Saldo (Trial Balance)'])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-5 pb-16">

        {{-- ========================================================== --}}
        {{-- TOOLBAR / PAGE HEADER                                      --}}
        {{-- ========================================================== --}}
        <header class="rounded-[16px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
            <div>
                <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                    <span class="text-black/70 dark:text-white/70">Keuangan &amp; Akuntansi</span>
                    <i data-lucide="chevron-right" class="w-3 h-3 opacity-40"></i>
                    <span class="text-black dark:text-white font-medium">Neraca Saldo</span>
                </nav>
                <h1 class="text-[20px] sm:text-[22px] font-bold text-black dark:text-white tracking-tight">Neraca Saldo (Trial Balance)</h1>
                <p class="text-[13px] text-black/50 dark:text-white/50">Daftar saldo awal, mutasi pembukuan debit/kredit, dan saldo penutupan seluruh akun</p>
            </div>
            <div class="flex items-center gap-2.5 w-full sm:w-auto">
                <form method="GET" action="{{ route('finance.trial-balance') }}" class="flex items-center gap-2">
                    <input type="date" name="start_date" value="{{ $startDate->toDateString() }}"
                        class="h-11 sm:h-9 px-2.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[12px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                    <span class="text-black/30 dark:text-white/30 text-[12px]">–</span>
                    <input type="date" name="end_date" value="{{ $endDate->toDateString() }}"
                        class="h-11 sm:h-9 px-2.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[12px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                    <button type="submit"
                        class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] bg-[#007AFF] text-white text-[13px] font-semibold hover:bg-[#0071E3] transition-colors flex items-center justify-center">
                        Filter
                    </button>
                </form>
                <button type="button" onclick="window.print()"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-colors flex items-center justify-center gap-1.5">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Cetak</span>
                </button>
            </div>
        </header>

        {{-- ========================================================== --}}
        {{-- BALANCE STATUS HIGHLIGHT CARD (APPLE HIG BENTO)            --}}
        {{-- ========================================================== --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 flex items-center justify-between shadow-xs">
                <div>
                    <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Total Saldo Akhir Debit</p>
                    <p class="text-[22px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] mt-1">
                        Rp {{ number_format($trial['totals']['ending_debit'], 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Seluruh akun bertipe debit</p>
                </div>
                <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                    <i data-lucide="arrow-down-left" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 flex items-center justify-between shadow-xs">
                <div>
                    <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Total Saldo Akhir Kredit</p>
                    <p class="text-[22px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF] mt-1">
                        Rp {{ number_format($trial['totals']['ending_credit'], 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Seluruh akun bertipe kredit</p>
                </div>
                <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                    <i data-lucide="arrow-up-right" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 flex items-center justify-between shadow-xs">
                <div>
                    <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Keseimbangan Neraca Saldo</p>
                    <div class="mt-1.5 flex items-center gap-1.5">
                        @if($trial['totals']['is_balanced'])
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[12px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                                Seimbang (Valid 100%)
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[12px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                                Selisih Rp {{ number_format(abs($trial['totals']['difference']), 0, ',', '.') }}
                            </span>
                        @endif
                    </div>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">
                        {{ $trial['totals']['is_balanced'] ? 'Debit sama dengan kredit' : 'Periksa jurnal pembukuan' }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-[12px] {{ $trial['totals']['is_balanced'] ? 'bg-[#34C759]/10 text-[#34C759]' : 'bg-[#FF3B30]/10 text-[#FF3B30]' }} flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $trial['totals']['is_balanced'] ? 'shield-check' : 'alert-triangle' }}" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        {{-- ========================================================== --}}
        {{-- TRIAL BALANCE TABLE                                        --}}
        {{-- ========================================================== --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02]">
                            <th rowspan="2" class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">Akun (COA)</th>
                            <th colspan="2" class="px-4 py-2 text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45 text-center border-l border-black/[0.06] dark:border-white/[0.08]">Saldo Awal</th>
                            <th colspan="2" class="px-4 py-2 text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45 text-center border-l border-black/[0.06] dark:border-white/[0.08]">Mutasi Periode</th>
                            <th colspan="2" class="px-4 py-2 text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45 text-center border-l border-black/[0.06] dark:border-white/[0.08]">Saldo Akhir</th>
                        </tr>
                        <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.01] dark:bg-white/[0.01]">
                            <th class="px-3 py-1.5 text-[11px] font-semibold text-black/45 dark:text-white/45 text-right border-l border-black/[0.04] dark:border-white/[0.04]">Debit</th>
                            <th class="px-3 py-1.5 text-[11px] font-semibold text-black/45 dark:text-white/45 text-right">Kredit</th>
                            <th class="px-3 py-1.5 text-[11px] font-semibold text-black/45 dark:text-white/45 text-right border-l border-black/[0.04] dark:border-white/[0.04]">Debit</th>
                            <th class="px-3 py-1.5 text-[11px] font-semibold text-black/45 dark:text-white/45 text-right">Kredit</th>
                            <th class="px-3 py-1.5 text-[11px] font-semibold text-black/45 dark:text-white/45 text-right border-l border-black/[0.04] dark:border-white/[0.04]">Debit</th>
                            <th class="px-3 py-1.5 text-[11px] font-semibold text-black/45 dark:text-white/45 text-right">Kredit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($trial['rows'] as $row)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                <td class="px-4 sm:px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="tabular-nums font-semibold font-mono text-[12px] text-[#007AFF] dark:text-[#0A84FF]">{{ $row['code'] }}</span>
                                        <a href="{{ route('finance.general-ledger', ['account_id' => $row['id']]) }}" class="font-medium text-black dark:text-white hover:underline">
                                            {{ $row['name'] }}
                                        </a>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/10 text-black/50 dark:text-white/50">
                                            {{ $row['type_label'] }}
                                        </span>
                                    </div>
                                </td>
                                {{-- Saldo Awal --}}
                                <td class="px-3 py-3 text-right tabular-nums border-l border-black/[0.03] dark:border-white/[0.03]">
                                    {{ $row['beginning_debit'] > 0 ? number_format($row['beginning_debit'], 0, ',', '.') : '–' }}
                                </td>
                                <td class="px-3 py-3 text-right tabular-nums">
                                    {{ $row['beginning_credit'] > 0 ? number_format($row['beginning_credit'], 0, ',', '.') : '–' }}
                                </td>
                                {{-- Mutasi Periode --}}
                                <td class="px-3 py-3 text-right tabular-nums border-l border-black/[0.03] dark:border-white/[0.03]">
                                    {{ $row['movement_debit'] > 0 ? number_format($row['movement_debit'], 0, ',', '.') : '–' }}
                                </td>
                                <td class="px-3 py-3 text-right tabular-nums">
                                    {{ $row['movement_credit'] > 0 ? number_format($row['movement_credit'], 0, ',', '.') : '–' }}
                                </td>
                                {{-- Saldo Akhir --}}
                                <td class="px-3 py-3 text-right tabular-nums font-semibold text-black dark:text-white border-l border-black/[0.03] dark:border-white/[0.03]">
                                    {{ $row['ending_debit'] > 0 ? number_format($row['ending_debit'], 0, ',', '.') : '–' }}
                                </td>
                                <td class="px-3 py-3 text-right tabular-nums font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                                    {{ $row['ending_credit'] > 0 ? number_format($row['ending_credit'], 0, ',', '.') : '–' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-black/40 dark:text-white/40">
                                    Belum ada mutasi akun pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-black/[0.1] dark:border-white/[0.1] bg-black/[0.03] dark:bg-white/[0.03] font-bold text-[13px]">
                            <td class="px-4 sm:px-5 py-3 text-black dark:text-white">TOTAL KONSOLIDASI</td>
                            <td class="px-3 py-3 text-right tabular-nums border-l border-black/[0.04]">
                                {{ number_format($trial['totals']['beginning_debit'], 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-3 text-right tabular-nums">
                                {{ number_format($trial['totals']['beginning_credit'], 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-3 text-right tabular-nums border-l border-black/[0.04]">
                                {{ number_format($trial['totals']['movement_debit'], 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-3 text-right tabular-nums">
                                {{ number_format($trial['totals']['movement_credit'], 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-3 text-right tabular-nums text-[#34C759] dark:text-[#30D158] border-l border-black/[0.04]">
                                Rp {{ number_format($trial['totals']['ending_debit'], 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-3 text-right tabular-nums text-[#007AFF] dark:text-[#0A84FF]">
                                Rp {{ number_format($trial['totals']['ending_credit'], 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
@endsection
