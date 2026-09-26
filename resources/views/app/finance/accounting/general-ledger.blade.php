@extends('layouts.app', ['title' => 'Buku Besar Umum (General Ledger)'])

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
                    <span class="text-black dark:text-white font-medium">Buku Besar Umum</span>
                </nav>
                <h1 class="text-[20px] sm:text-[22px] font-bold text-black dark:text-white tracking-tight">Buku Besar Umum (General Ledger)</h1>
                <p class="text-[13px] text-black/50 dark:text-white/50">Rincian mutasi transaksi historis per akun dan tautan dokumen sumber asli</p>
            </div>
            <div class="flex items-center gap-2.5 w-full sm:w-auto">
                <a href="{{ route('finance.coa.index') }}"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-colors flex items-center justify-center gap-1.5">
                    <i data-lucide="list-tree" class="w-4 h-4"></i>
                    <span>Daftar Akun (COA)</span>
                </a>
                <button type="button" onclick="window.print()"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-colors flex items-center justify-center gap-1.5">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Cetak</span>
                </button>
            </div>
        </header>

        {{-- ========================================================== --}}
        {{-- ACCOUNT & DATE FILTER BAR                                  --}}
        {{-- ========================================================== --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3.5 shadow-xs">
            <form method="GET" action="{{ route('finance.general-ledger') }}" class="flex flex-wrap items-center gap-2.5">
                <div class="flex-1 min-w-[260px]">
                    <select name="account_id" onchange="this.form.submit()"
                        class="w-full h-11 sm:h-9 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[13px] text-black dark:text-white font-medium focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                        @foreach($ledger['accounts'] as $acc)
                            <option value="{{ $acc->id }}" @selected($ledger['selected_account'] && $ledger['selected_account']->id === $acc->id)>
                                {{ $acc->code }} – {{ $acc->name }} ({{ $acc->getTypeLabel() }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <input type="date" name="start_date" value="{{ $startDate->toDateString() }}"
                    class="h-11 sm:h-9 px-2.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[12px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                <span class="text-black/30 dark:text-white/30 text-[12px]">–</span>
                <input type="date" name="end_date" value="{{ $endDate->toDateString() }}"
                    class="h-11 sm:h-9 px-2.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[12px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                
                <button type="submit"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] bg-[#007AFF] text-white text-[13px] font-semibold hover:bg-[#0071E3] transition-colors flex items-center justify-center">
                    Tampilkan
                </button>
            </form>
        </div>

        {{-- ========================================================== --}}
        {{-- SELECTED ACCOUNT SUMMARY CARDS (BENTO)                     --}}
        {{-- ========================================================== --}}
        @if($ledger['selected_account'])
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4.5 shadow-xs">
                    <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Akun Terpilih</p>
                    <p class="text-[15px] font-bold text-black dark:text-white mt-1 truncate">{{ $ledger['selected_account']->name }}</p>
                    <p class="text-[11px] font-mono font-bold text-[#007AFF] mt-0.5">{{ $ledger['selected_account']->code }} ({{ strtoupper($ledger['selected_account']->normal_balance) }})</p>
                </div>

                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4.5 shadow-xs">
                    <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Saldo Awal</p>
                    <p class="text-[18px] sm:text-[20px] font-bold tabular-nums text-black dark:text-white mt-1">
                        Rp {{ number_format($ledger['beginning_balance'], 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Sebelum {{ $startDate->translatedFormat('d M Y') }}</p>
                </div>

                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4.5 shadow-xs">
                    <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Mutasi Periode</p>
                    <div class="mt-1 flex items-center justify-between text-[13px] tabular-nums">
                        <span class="text-[#34C759] dark:text-[#30D158] font-bold">+{{ number_format($ledger['total_debit'], 0, ',', '.') }}</span>
                        <span class="text-[#FF3B30] font-bold">-{{ number_format($ledger['total_credit'], 0, ',', '.') }}</span>
                    </div>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Debit vs Kredit</p>
                </div>

                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4.5 shadow-xs">
                    <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Saldo Penutupan</p>
                    <p class="text-[18px] sm:text-[20px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF] mt-1">
                        Rp {{ number_format($ledger['ending_balance'], 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Per {{ $endDate->translatedFormat('d M Y') }}</p>
                </div>
            </div>
        @endif

        {{-- ========================================================== --}}
        {{-- GENERAL LEDGER DRILLDOWN TABLE                             --}}
        {{-- ========================================================== --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02]">
                            <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">Tanggal</th>
                            <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">No. Jurnal</th>
                            <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">Referensi &amp; Dokumen</th>
                            <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">Deskripsi / Catatan</th>
                            <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 text-right">Debit (Rp)</th>
                            <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 text-right">Kredit (Rp)</th>
                            <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 text-right">Saldo Berjalan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        {{-- Baris Saldo Awal --}}
                        <tr class="bg-black/[0.01] dark:bg-white/[0.01] font-medium text-black/60 dark:text-white/60">
                            <td class="px-4 sm:px-5 py-2.5">{{ $startDate->translatedFormat('d M Y') }}</td>
                            <td class="px-4 sm:px-5 py-2.5">–</td>
                            <td class="px-4 sm:px-5 py-2.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-[5px] text-[10px] font-semibold bg-black/[0.04] dark:bg-white/[0.06]">
                                    SALDO AWAL
                                </span>
                            </td>
                            <td class="px-4 sm:px-5 py-2.5">Saldo awal akun sebelum periode pencarian</td>
                            <td class="px-4 sm:px-5 py-2.5 text-right tabular-nums">–</td>
                            <td class="px-4 sm:px-5 py-2.5 text-right tabular-nums">–</td>
                            <td class="px-4 sm:px-5 py-2.5 text-right tabular-nums font-semibold text-black dark:text-white">
                                Rp {{ number_format($ledger['beginning_balance'], 0, ',', '.') }}
                            </td>
                        </tr>

                        @forelse($ledger['lines'] as $line)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                <td class="px-4 sm:px-5 py-3 tabular-nums text-black/70 dark:text-white/70">
                                    {{ $line['entry_date'] }}
                                </td>
                                <td class="px-4 sm:px-5 py-3 tabular-nums font-mono font-bold text-[#007AFF] dark:text-[#0A84FF]">
                                    #{{ $line['entry_number'] }}
                                </td>
                                <td class="px-4 sm:px-5 py-3">
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-[5px] text-[10px] font-semibold bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70">
                                            {{ $line['reference_type_label'] }}
                                        </span>
                                        @if($line['source_url'])
                                            <a href="{{ $line['source_url'] }}" target="_blank"
                                                class="inline-flex items-center gap-1 text-[11px] font-medium text-[#007AFF] hover:underline"
                                                title="Buka Dokumen Asli">
                                                <span>Lihat</span>
                                                <i data-lucide="external-link" class="w-3 h-3"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 sm:px-5 py-3 text-black/80 dark:text-white/80">
                                    <span>{{ $line['description'] }}</span>
                                    @if($line['notes'])
                                        <span class="block text-[11px] text-black/40 dark:text-white/40 mt-0.5">{{ $line['notes'] }}</span>
                                    @endif
                                </td>
                                <td class="px-4 sm:px-5 py-3 text-right tabular-nums">
                                    @if($line['debit'] > 0)
                                        <span class="font-semibold text-black dark:text-white">{{ number_format($line['debit'], 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-black/20 dark:text-white/20">–</span>
                                    @endif
                                </td>
                                <td class="px-4 sm:px-5 py-3 text-right tabular-nums">
                                    @if($line['credit'] > 0)
                                        <span class="font-semibold text-[#007AFF] dark:text-[#0A84FF]">{{ number_format($line['credit'], 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-black/20 dark:text-white/20">–</span>
                                    @endif
                                </td>
                                <td class="px-4 sm:px-5 py-3 text-right tabular-nums font-semibold text-black dark:text-white">
                                    Rp {{ number_format($line['running_balance'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-black/40 dark:text-white/40">
                                    Tidak ada mutasi jurnal tambahan pada rentang tanggal ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-black/[0.1] dark:border-white/[0.1] bg-black/[0.03] dark:bg-white/[0.03] font-bold text-[13px]">
                            <td colspan="4" class="px-4 sm:px-5 py-3 text-black dark:text-white">TOTAL PERIODE INI</td>
                            <td class="px-4 sm:px-5 py-3 text-right tabular-nums text-black dark:text-white">
                                Rp {{ number_format($ledger['total_debit'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-right tabular-nums text-[#007AFF] dark:text-[#0A84FF]">
                                Rp {{ number_format($ledger['total_credit'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-right tabular-nums text-[#34C759] dark:text-[#30D158]">
                                Rp {{ number_format($ledger['ending_balance'], 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
@endsection
