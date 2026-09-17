@extends('layouts.app', ['title' => 'Detail Rekonsiliasi Settlement #' . $settlement->settlement_number])

@section('content')
<div class="max-w-[1000px] mx-auto space-y-6 pb-16">

    {{-- ========================================================== --}}
    {{-- TOOLBAR / PAGE HEADER                                      --}}
    {{-- ========================================================== --}}
    <header class="rounded-[16px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-5 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('finance.settlements.index') }}" class="hover:text-black dark:hover:text-white transition">Rekonsiliasi Gateway</a>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Detail Settlement</span>
            </nav>
            <h1 class="text-[20px] font-bold text-black dark:text-white tracking-tight flex items-center gap-2.5">
                <span>Settlement #{{ $settlement->settlement_number }}</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                    Terekonsiliasi
                </span>
            </h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Diproses pada {{ \Carbon\Carbon::parse($settlement->settlement_date)->format('d F Y') }} oleh {{ $settlement->reconciledBy?->name ?? 'Sistem' }}</p>
        </div>
        <div>
            <a href="{{ route('finance.settlements.index') }}"
                class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Kembali ke Daftar</span>
            </a>
        </div>
    </header>

    {{-- ========================================================== --}}
    {{-- SETTLEMENT SUMMARY BENTO CARDS                             --}}
    {{-- ========================================================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-4.5 space-y-1 shadow-sm">
            <span class="text-xs text-black/50 dark:text-white/50 block font-medium">Total Nominal Bruto</span>
            <span class="text-xl font-extrabold text-black dark:text-white tabular-nums">
                {{ $business->currency_symbol }} {{ number_format($settlement->gross_amount, 0, ',', '.') }}
            </span>
            <span class="text-[11px] text-black/45 dark:text-white/45 block">Akun Clearing: 1-1005 (Gateway Escrow)</span>
        </div>

        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-4.5 space-y-1 shadow-sm">
            <span class="text-xs text-black/50 dark:text-white/50 block font-medium">Beban Administrasi MDR</span>
            <span class="text-xl font-extrabold text-[#FF3B30] tabular-nums">
                {{ $business->currency_symbol }} {{ number_format($settlement->fee_amount, 0, ',', '.') }}
            </span>
            <span class="text-[11px] text-black/45 dark:text-white/45 block">Akun Beban: 6-6003 (Biaya Gateway)</span>
        </div>

        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-4.5 space-y-1 shadow-sm">
            <span class="text-xs text-black/50 dark:text-white/50 block font-medium">Bersih Masuk Rekening Bank</span>
            <span class="text-xl font-extrabold text-[#34C759] dark:text-[#30D158] tabular-nums">
                {{ $business->currency_symbol }} {{ number_format($settlement->net_amount, 0, ',', '.') }}
            </span>
            <span class="text-[11px] text-black/45 dark:text-white/45 block">{{ $settlement->destination_bank ?? 'Rekening Bank Toko' }}</span>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- ALLOCATIONS BREAKDOWN TABLE                                --}}
    {{-- ========================================================== --}}
    <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-sm">
        <div class="px-5 sm:px-6 py-4 border-b border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.01]">
            <h2 class="text-[15px] font-bold text-black dark:text-white">Daftar Transaksi yang Teralokasi ({{ $settlement->allocations->count() }} Item)</h2>
            <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Rincian pesanan yang dicairkan dalam nomor batch settlement ini</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-black/[0.02] dark:bg-white/[0.03] border-b border-black/5 dark:border-white/10 text-black/60 dark:text-white/60 font-semibold">
                    <tr>
                        <th class="py-3 px-5">Tipe Pembayaran</th>
                        <th class="py-3 px-5">ID Referensi Pembayaran</th>
                        <th class="py-3 px-5 text-right">Nominal Teralokasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5 text-black/80 dark:text-white/80">
                    @foreach($settlement->allocations as $allocation)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition">
                            <td class="py-3 px-5">
                                <span class="px-2 py-0.5 rounded-md font-semibold text-[10px]"
                                    :class="'{{ $allocation->payment_type }}' === 'commerce_order' ? 'bg-[#AF52DE]/10 text-[#AF52DE]' : 'bg-[#007AFF]/10 text-[#007AFF]'">
                                    {{ $allocation->payment_type === 'commerce_order' ? 'Toko Online Storefront' : ($allocation->payment_type === 'pos_order_payment' ? 'Kasir POS / QR Meja' : 'Faktur Invoice') }}
                                </span>
                            </td>
                            <td class="py-3 px-5 font-mono text-[11px] text-black/60 dark:text-white/60">
                                {{ $allocation->payment_id }}
                            </td>
                            <td class="py-3 px-5 text-right font-bold tabular-nums text-black dark:text-white">
                                {{ $business->currency_symbol }} {{ number_format($allocation->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($settlement->notes)
            <div class="p-4 border-t border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.01] text-xs">
                <span class="font-semibold text-black dark:text-white">Catatan:</span>
                <span class="text-black/60 dark:text-white/60 ml-1">{{ $settlement->notes }}</span>
            </div>
        @endif
    </div>

</div>
@endsection
