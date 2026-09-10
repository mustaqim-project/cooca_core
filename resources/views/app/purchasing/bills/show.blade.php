@extends('layouts.app', [
    'title' => 'Detail Hutang Supplier ' . $invoice->invoice_number,
    'headerTitle' => 'Detail Hutang Supplier',
    'headerSubtitle' => 'Rincian faktur masuk pemasok dan riwayat pembayaran'
])

@section('content')
<div class="max-w-[1080px] mx-auto space-y-6 pb-12">
    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / HEADER (macOS Sonoma Style)              -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <!-- Breadcrumb minimal -->
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <a href="{{ route('purchasing.bills.index') }}" class="hover:text-[#007AFF] transition-colors">Tagihan &amp; Hutang</a>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">{{ $invoice->invoice_number }}</span>
            </nav>
            <div class="flex items-center gap-2.5">
                <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">{{ $invoice->invoice_number }}</h1>
                @if($invoice->status === 'paid')
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Lunas
                    </span>
                @elseif($invoice->status === 'partial')
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span> Sebagian
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> Belum Bayar
                    </span>
                @endif
            </div>
            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">
                {{ $invoice->supplier->name }} &bull; GR {{ $invoice->goodsReceipt->receipt_number ?? '-' }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('purchasing.bills.index') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Kembali ke Daftar</span>
            </a>
        </div>
    </header>

    @if(session('success'))
    <div class="rounded-[12px] bg-[#34C759]/12 border border-[#34C759]/20 px-4 py-3 text-[13px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 2. FINANCIAL KPI CARDS                                -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        <!-- Total Tagihan (Neutral) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Tagihan</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums text-black dark:text-white">
                    Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                </span>
                <span class="text-[11px] text-black/40 dark:text-white/40">Kewajiban</span>
            </div>
        </div>

        <!-- Terbayar (System Green) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Sudah Terbayar</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">
                    Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}
                </span>
                <span class="text-[11px] font-semibold text-[#34C759] dark:text-[#30D158]">Selesai</span>
            </div>
        </div>

        <!-- Sisa Hutang (System Orange) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Sisa Hutang</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums {{ $invoice->balance_due > 0 ? 'text-[#FF9500] dark:text-[#FF9F0A]' : 'text-black dark:text-white' }}">
                    Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}
                </span>
                <span class="text-[11px] font-semibold {{ $invoice->balance_due > 0 ? 'text-[#FF9500] dark:text-[#FF9F0A]' : 'text-black/40 dark:text-white/40' }}">
                    {{ $invoice->balance_due > 0 ? 'Tertunggak' : 'Nol' }}
                </span>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. PAYMENT RECORD FORM                                -->
    <!-- ===================================================== -->
    @if($invoice->balance_due > 0)
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-4">
        <div class="border-b border-black/5 dark:border-white/10 pb-3 flex items-center justify-between">
            <div>
                <h2 class="text-[15px] font-semibold text-black dark:text-white">Catat Pembayaran Hutang</h2>
                <p class="text-[12px] text-black/50 dark:text-white/50">Simpan pelunasan kas atau transfer bank untuk mengurangi saldo hutang vendor ini.</p>
            </div>
            <span class="text-[12px] font-medium text-[#FF9500] dark:text-[#FF9F0A] tabular-nums">
                Maks: Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}
            </span>
        </div>

        @error('amount')
            <p class="text-[12px] font-medium text-[#FF3B30] dark:text-[#FF453A]">{{ $message }}</p>
        @enderror

        <form method="POST" action="{{ route('purchasing.bills.payments.store', $invoice) }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">Nominal Pembayaran (Rp) *</label>
                    <input name="amount" type="number" min="0.01" max="{{ $invoice->balance_due }}" step="0.01" placeholder="Contoh: 500000" required value="{{ old('amount') }}"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[14px] font-medium tabular-nums text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">Tanggal Bayar *</label>
                    <input name="payment_date" type="date" value="{{ old('payment_date', now()->format('Y-m-d')) }}" required
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">Metode Pembayaran *</label>
                    <select name="payment_method" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="bank_transfer">Transfer Bank</option>
                        <option value="cash">Kas Tunai</option>
                        <option value="qris">QRIS</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full h-10 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        <span>Simpan Pembayaran</span>
                    </button>
                </div>
            </div>

            <div>
                <input name="reference_number" placeholder="Nomor referensi / bukti transfer bank (opsional)"
                       class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>
        </form>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 4. PAYMENT HISTORY TABLE                              -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="px-4 py-3 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
            <h3 class="text-[14px] font-semibold text-black dark:text-white">Riwayat Pembayaran Faktur</h3>
            <span class="text-[12px] text-black/50 dark:text-white/50 tabular-nums">{{ count($invoice->payments) }} Pembayaran</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10">
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Nomor Transaksi</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Tanggal</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Metode</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Nominal Terbayar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($invoice->payments as $payment)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3 font-medium text-black dark:text-white tabular-nums">
                            {{ $payment->payment_number }}
                        </td>
                        <td class="px-4 py-3 text-black/60 dark:text-white/60 tabular-nums">
                            {{ $payment->payment_date->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-[6px] text-[11px] font-medium bg-black/[0.06] dark:bg-white/[0.08] text-black/70 dark:text-white/70">
                                {{ strtoupper($payment->payment_method) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums font-semibold text-[#34C759] dark:text-[#30D158]">
                            Rp {{ number_format($payment->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-[13px] text-black/40 dark:text-white/40">
                            Belum ada riwayat pembayaran yang tercatat untuk faktur ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
