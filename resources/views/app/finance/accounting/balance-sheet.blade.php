@extends('layouts.app', ['title' => 'Neraca Keuangan (Balance Sheet)'])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-5 pb-16">

        {{-- ========================================================== --}}
        {{-- TOOLBAR / PAGE HEADER                                      --}}
        {{-- ========================================================== --}}
        <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                    <span class="text-black/70 dark:text-white/70">Keuangan &amp; Akuntansi</span>
                    <span>›</span>
                    <span class="text-black dark:text-white font-medium">Neraca Keuangan</span>
                </nav>
                <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Laporan Posisi Keuangan (Neraca)</h1>
                <p class="text-[13px] text-black/50 dark:text-white/50">Standar SAK EMKM resmi: Aktiva (Aset) = Pasiva (Kewajiban + Ekuitas)</p>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <form method="GET" action="{{ route('finance.balance-sheet') }}" class="flex items-center gap-2">
                    <div class="relative">
                        <input type="date" name="as_of_date" value="{{ $asOfDate->toDateString() }}"
                            onchange="this.form.submit()"
                            class="h-9 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                    </div>
                </form>
                <button type="button" onclick="window.print()"
                    class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-colors flex items-center gap-1.5">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Cetak</span>
                </button>
            </div>
        </header>

        {{-- ========================================================== --}}
        {{-- BALANCE STATUS HIGHLIGHT CARD (APPLE HIG BENTO)            --}}
        {{-- ========================================================== --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-medium text-black/45 dark:text-white/45 uppercase tracking-wide">Total Aktiva (Aset)</p>
                    <p class="text-[22px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF] mt-1">
                        Rp {{ number_format($sheet['summary']['total_assets'], 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Per {{ $sheet['formatted_date'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-medium text-black/45 dark:text-white/45 uppercase tracking-wide">Total Pasiva (Kewajiban + Modal)</p>
                    <p class="text-[22px] font-bold tabular-nums text-[#5856D6] dark:text-[#5E5CE6] mt-1">
                        Rp {{ number_format($sheet['summary']['total_liabilities_and_equity'], 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Hutang &amp; Ekuitas pemilik</p>
                </div>
                <div class="w-10 h-10 rounded-[12px] bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center shrink-0">
                    <i data-lucide="scale" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-medium text-black/45 dark:text-white/45 uppercase tracking-wide">Status Keseimbangan</p>
                    <div class="mt-1.5 flex items-center gap-1.5">
                        @if($sheet['summary']['is_balanced'])
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[12px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                                Seimbang (Valid 100%)
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[12px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                                Selisih Rp {{ number_format(abs($sheet['summary']['difference']), 0, ',', '.') }}
                            </span>
                        @endif
                    </div>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">
                        {{ $sheet['summary']['is_balanced'] ? 'Aktiva sama dengan pasiva' : 'Periksa penyesuaian jurnal' }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-[12px] {{ $sheet['summary']['is_balanced'] ? 'bg-[#34C759]/10 text-[#34C759]' : 'bg-[#FF3B30]/10 text-[#FF3B30]' }} flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $sheet['summary']['is_balanced'] ? 'shield-check' : 'alert-circle' }}" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        {{-- ========================================================== --}}
        {{-- BALANCE SHEET DUAL-COLUMN BENTO                            --}}
        {{-- ========================================================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            {{-- ------------------------------------------------------ --}}
            {{-- KOLOM KIRI: AKTIVA (ASET)                              --}}
            {{-- ------------------------------------------------------ --}}
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="px-5 py-4 border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#007AFF]"></span>
                            <h2 class="text-[15px] font-semibold text-black dark:text-white">ASET / AKTIVA</h2>
                        </div>
                        <span class="text-[12px] text-black/40 dark:text-white/40">Sisi Kiri Neraca</span>
                    </div>

                    {{-- 1. Aset Lancar --}}
                    <div class="p-5 space-y-3">
                        <div class="flex items-center justify-between pb-1 border-b border-black/[0.06] dark:border-white/[0.06]">
                            <h3 class="text-[13px] font-bold text-black/75 dark:text-white/75 uppercase tracking-wide">Aset Lancar</h3>
                            <span class="text-[11px] text-black/40 dark:text-white/40">Kas, Bank, Piutang, Persediaan</span>
                        </div>

                        <div class="divide-y divide-black/[0.03] dark:divide-white/[0.04]">
                            @forelse($sheet['assets']['current'] as $item)
                                <div class="py-2 flex items-center justify-between text-[13px]">
                                    <div class="flex items-center gap-2 min-w-0 pr-2">
                                        <span class="tabular-nums font-mono text-[11px] text-black/40 dark:text-white/40 shrink-0">{{ $item['code'] }}</span>
                                        <span class="text-black/80 dark:text-white/80 truncate">{{ $item['name'] }}</span>
                                    </div>
                                    <span class="tabular-nums font-semibold text-black dark:text-white shrink-0">
                                        Rp {{ number_format($item['balance'], 0, ',', '.') }}
                                    </span>
                                </div>
                            @empty
                                <div class="py-2 text-[12px] text-black/40 dark:text-white/40">Belum ada akun aset lancar dengan saldo.</div>
                            @endforelse
                        </div>

                        <div class="pt-2 flex items-center justify-between text-[13px] font-semibold border-t border-dashed border-black/10 dark:border-white/10">
                            <span class="text-black/60 dark:text-white/60">Subtotal Aset Lancar</span>
                            <span class="tabular-nums text-black dark:text-white">
                                Rp {{ number_format($sheet['assets']['total_current'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    {{-- 2. Aset Tidak Lancar / Tetap --}}
                    <div class="p-5 pt-0 space-y-3">
                        <div class="flex items-center justify-between pb-1 border-b border-black/[0.06] dark:border-white/[0.06]">
                            <h3 class="text-[13px] font-bold text-black/75 dark:text-white/75 uppercase tracking-wide">Aset Tidak Lancar (Aset Tetap)</h3>
                            <span class="text-[11px] text-black/40 dark:text-white/40">Peralatan, Mesin &amp; Kendaraan</span>
                        </div>

                        <div class="divide-y divide-black/[0.03] dark:divide-white/[0.04]">
                            @forelse($sheet['assets']['non_current'] as $item)
                                <div class="py-2 flex items-center justify-between text-[13px]">
                                    <div class="flex items-center gap-2 min-w-0 pr-2">
                                        <span class="tabular-nums font-mono text-[11px] text-black/40 dark:text-white/40 shrink-0">{{ $item['code'] }}</span>
                                        <span class="text-black/80 dark:text-white/80 truncate">{{ $item['name'] }}</span>
                                    </div>
                                    <span class="tabular-nums font-semibold text-black dark:text-white shrink-0">
                                        Rp {{ number_format($item['balance'], 0, ',', '.') }}
                                    </span>
                                </div>
                            @empty
                                <div class="py-2 text-[12px] text-black/40 dark:text-white/40">Tidak ada aset tidak lancar tercatat.</div>
                            @endforelse
                        </div>

                        <div class="pt-2 flex items-center justify-between text-[13px] font-semibold border-t border-dashed border-black/10 dark:border-white/10">
                            <span class="text-black/60 dark:text-white/60">Subtotal Aset Tetap</span>
                            <span class="tabular-nums text-black dark:text-white">
                                Rp {{ number_format($sheet['assets']['total_non_current'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Total Aktiva Footer --}}
                <div class="p-5 bg-black/[0.03] dark:bg-white/[0.03] border-t border-black/5 dark:border-white/10 flex items-center justify-between">
                    <span class="text-[15px] font-bold text-black dark:text-white tracking-tight">TOTAL AKTIVA (ASET)</span>
                    <span class="text-[18px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF]">
                        Rp {{ number_format($sheet['summary']['total_assets'], 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- ------------------------------------------------------ --}}
            {{-- KOLOM KANAN: PASIVA (KEWAJIBAN & EKUITAS)              --}}
            {{-- ------------------------------------------------------ --}}
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="px-5 py-4 border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#5856D6]"></span>
                            <h2 class="text-[15px] font-semibold text-black dark:text-white">KEWAJIBAN &amp; EKUITAS (PASIVA)</h2>
                        </div>
                        <span class="text-[12px] text-black/40 dark:text-white/40">Sisi Kanan Neraca</span>
                    </div>

                    {{-- 1. Kewajiban Jangka Pendek --}}
                    <div class="p-5 space-y-3">
                        <div class="flex items-center justify-between pb-1 border-b border-black/[0.06] dark:border-white/[0.06]">
                            <h3 class="text-[13px] font-bold text-black/75 dark:text-white/75 uppercase tracking-wide">Kewajiban / Hutang Lancar</h3>
                            <span class="text-[11px] text-black/40 dark:text-white/40">Hutang Usaha &amp; Pajak</span>
                        </div>

                        <div class="divide-y divide-black/[0.03] dark:divide-white/[0.04]">
                            @forelse($sheet['liabilities']['current'] as $item)
                                <div class="py-2 flex items-center justify-between text-[13px]">
                                    <div class="flex items-center gap-2 min-w-0 pr-2">
                                        <span class="tabular-nums font-mono text-[11px] text-black/40 dark:text-white/40 shrink-0">{{ $item['code'] }}</span>
                                        <span class="text-black/80 dark:text-white/80 truncate">{{ $item['name'] }}</span>
                                    </div>
                                    <span class="tabular-nums font-semibold text-black dark:text-white shrink-0">
                                        Rp {{ number_format($item['balance'], 0, ',', '.') }}
                                    </span>
                                </div>
                            @empty
                                <div class="py-2 text-[12px] text-black/40 dark:text-white/40">Belum ada kewajiban lancar tercatat.</div>
                            @endforelse
                        </div>

                        <div class="pt-2 flex items-center justify-between text-[13px] font-semibold border-t border-dashed border-black/10 dark:border-white/10">
                            <span class="text-black/60 dark:text-white/60">Subtotal Kewajiban</span>
                            <span class="tabular-nums text-black dark:text-white">
                                Rp {{ number_format($sheet['liabilities']['total'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    {{-- 2. Ekuitas / Modal --}}
                    <div class="p-5 pt-0 space-y-3">
                        <div class="flex items-center justify-between pb-1 border-b border-black/[0.06] dark:border-white/[0.06]">
                            <h3 class="text-[13px] font-bold text-black/75 dark:text-white/75 uppercase tracking-wide">Ekuitas &amp; Modal Pemilik</h3>
                            <span class="text-[11px] text-black/40 dark:text-white/40">Modal, Saldo Laba &amp; Laba Berjalan</span>
                        </div>

                        <div class="divide-y divide-black/[0.03] dark:divide-white/[0.04]">
                            @foreach($sheet['equity']['accounts'] as $item)
                                <div class="py-2 flex items-center justify-between text-[13px]">
                                    <div class="flex items-center gap-2 min-w-0 pr-2">
                                        <span class="tabular-nums font-mono text-[11px] text-black/40 dark:text-white/40 shrink-0">{{ $item['code'] }}</span>
                                        <span class="text-black/80 dark:text-white/80 truncate">{{ $item['name'] }}</span>
                                    </div>
                                    <span class="tabular-nums font-semibold text-black dark:text-white shrink-0">
                                        Rp {{ number_format($item['balance'], 0, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach

                            {{-- Laba Bersih Periode Berjalan --}}
                            <div class="py-2 flex items-center justify-between text-[13px] bg-[#34C759]/5 px-2.5 rounded-[8px]">
                                <div class="flex items-center gap-2 min-w-0 pr-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] shrink-0"></span>
                                    <span class="font-medium text-[#248A3D] dark:text-[#30D158]">Laba Bersih Periode Berjalan (P&amp;L)</span>
                                </div>
                                <span class="tabular-nums font-bold text-[#248A3D] dark:text-[#30D158] shrink-0">
                                    Rp {{ number_format($sheet['equity']['current_earnings'], 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <div class="pt-2 flex items-center justify-between text-[13px] font-semibold border-t border-dashed border-black/10 dark:border-white/10">
                            <span class="text-black/60 dark:text-white/60">Subtotal Ekuitas</span>
                            <span class="tabular-nums text-black dark:text-white">
                                Rp {{ number_format($sheet['equity']['total'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Total Pasiva Footer --}}
                <div class="p-5 bg-black/[0.03] dark:bg-white/[0.03] border-t border-black/5 dark:border-white/10 flex items-center justify-between">
                    <span class="text-[15px] font-bold text-black dark:text-white tracking-tight">TOTAL PASIVA (KEWAJIBAN + EKUITAS)</span>
                    <span class="text-[18px] font-bold tabular-nums text-[#5856D6] dark:text-[#5E5CE6]">
                        Rp {{ number_format($sheet['summary']['total_liabilities_and_equity'], 0, ',', '.') }}
                    </span>
                </div>
            </div>

        </div>

    </div>
@endsection
