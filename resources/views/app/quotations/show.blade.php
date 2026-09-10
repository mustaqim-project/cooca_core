@extends('layouts.app', [
    'title' => 'Penawaran ' . $quotation->quotation_number,
    'headerTitle' => 'Detail Surat Penawaran',
    'headerSubtitle' => 'Dokumen resmi penawaran harga komersial ke pelanggan'
])

@section('content')
<div class="max-w-[1080px] mx-auto space-y-6 pb-12" x-data="{
    convertModalOpen: false,
    openConvert() {
        this.convertModalOpen = true;
    },
    closeConvert() {
        this.convertModalOpen = false;
    },
    submitConvert() {
        document.getElementById('form-convert-so').submit();
    }
}">
    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / ACTION BAR (macOS Sonoma Style)           -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 print:hidden">
        <div>
            <!-- Breadcrumb minimal -->
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <a href="{{ route('sales.quotations.index') }}" class="hover:text-[#007AFF] transition-colors">Penawaran Harga</a>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">{{ $quotation->quotation_number }}</span>
            </nav>
            <div class="flex items-center gap-2.5">
                <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight tabular-nums">{{ $quotation->quotation_number }}</h1>
                @if($quotation->status === 'accepted')
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Disetujui
                    </span>
                @elseif($quotation->status === 'sent')
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span> Terkirim
                    </span>
                @elseif($quotation->status === 'rejected')
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> Ditolak
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-black/[0.06] dark:bg-white/[0.08] text-black/60 dark:text-white/60">
                        {{ strtoupper($quotation->status) }}
                    </span>
                @endif
            </div>
            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">
                Klien: <span class="font-medium text-black dark:text-white">{{ $quotation->customer->name }}</span> &bull; 
                Total: <span class="font-medium text-black dark:text-white tabular-nums">Rp {{ number_format($quotation->total_amount, 0, ',', '.') }}</span>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
            <!-- 1-Click Convert to Sales Order Button -->
            @if(!$quotation->salesOrder && $quotation->status !== 'rejected')
            <form id="form-convert-so" action="{{ route('sales.quotations.convert', $quotation) }}" method="POST" class="inline">
                @csrf
                <button type="button" @click="openConvert()" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12.75 15l3-3m0 0l-3-3m3 3h-7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Ubah ke Sales Order</span>
                </button>
            </form>
            @elseif($quotation->salesOrder)
            <a href="{{ route('sales.orders.show', $quotation->salesOrder) }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Lihat SO ({{ $quotation->salesOrder->so_number }})</span>
            </a>
            @endif

            <!-- Print / Cetak -->
            <button onclick="window.print()" type="button" class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.077-.37-2.2-.37-3.329 0-6.075 4.925-11 11-11s11 4.925 11 11c0 1.129-.13 2.252-.37 3.329M3.75 14.25h16.5M6 18h12m-9 3h6" />
                </svg>
                <span>Cetak</span>
            </button>

            <!-- WhatsApp Share -->
            @php
                $waText = urlencode("Halo {$quotation->customer->name}, berikut kami kirimkan Surat Penawaran Resmi {$quotation->quotation_number} sebesar Rp " . number_format($quotation->total_amount, 0, ',', '.') . ". Terima kasih.");
            @endphp
            @if($quotation->customer->phone)
            <a href="https://api.whatsapp.com/send?phone={{ preg_replace('/[^0-9]/', '', $quotation->customer->phone) }}&text={{ $waText }}" target="_blank"
               class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-[#248A3D] dark:text-[#30D158] bg-[#34C759]/10 hover:bg-[#34C759]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                </svg>
                <span>WhatsApp</span>
            </a>
            @endif

            <a href="{{ route('sales.quotations.index') }}" class="h-9 px-3 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center">
                Tutup
            </a>
        </div>
    </header>

    <!-- ===================================================== -->
    <!-- 2. PRINTABLE QUOTATION SHEET                          -->
    <!-- ===================================================== -->
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 sm:p-10 space-y-8 print:p-0 print:border-none print:shadow-none">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-6 border-b border-black/5 dark:border-white/10 pb-6">
            <div>
                <h2 class="text-[22px] font-bold text-black dark:text-white tracking-tight">{{ $business->name }}</h2>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1">{{ $business->address ?? 'Operasional Bisnis' }}</p>
                <p class="text-[13px] text-black/50 dark:text-white/50">{{ $business->phone ?? '' }} &bull; {{ $business->email ?? '' }}</p>
            </div>

            <div class="text-left sm:text-right">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Surat Penawaran Harga</span>
                <p class="text-[20px] font-bold tabular-nums text-black dark:text-white mt-0.5">{{ $quotation->quotation_number }}</p>
                <div class="mt-2 text-[12px] space-y-0.5 text-black/60 dark:text-white/60 tabular-nums">
                    <p>Tanggal: <span class="font-medium text-black dark:text-white">{{ $quotation->date->format('d M Y') }}</span></p>
                    @if($quotation->expiry_date)
                    <p>Berlaku Sampai: <span class="font-medium text-black dark:text-white">{{ $quotation->expiry_date->format('d M Y') }}</span></p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Kepada / Customer Details -->
        <div class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 max-w-sm">
            <span class="text-[10px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">DITUJUKAN KEPADA:</span>
            <h3 class="text-[15px] font-semibold text-black dark:text-white mt-1">{{ $quotation->customer->name }}</h3>
            @if($quotation->customer->company)
            <p class="text-[13px] text-black/60 dark:text-white/60">{{ $quotation->customer->company }}</p>
            @endif
            @if($quotation->customer->address)
            <p class="text-[13px] text-black/60 dark:text-white/60 mt-1">{{ $quotation->customer->address }}</p>
            @endif
            <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5 tabular-nums">{{ $quotation->customer->phone }}</p>
        </div>

        <!-- Table of Items -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10">
                        <th class="px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 w-12 text-center">No.</th>
                        <th class="px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Deskripsi Item</th>
                        <th class="px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right w-36">Harga Satuan</th>
                        <th class="px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right w-24">Jumlah</th>
                        <th class="px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right w-36">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @foreach($quotation->items as $idx => $item)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-3 py-3 text-center tabular-nums text-black/40 dark:text-white/40">{{ $idx + 1 }}</td>
                        <td class="px-3 py-3">
                            <span class="font-medium text-black dark:text-white">{{ $item->product_name }}</span>
                            @if($item->notes)
                            <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">{{ $item->notes }}</p>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-right tabular-nums text-black/60 dark:text-white/60">
                            Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                        </td>
                        <td class="px-3 py-3 text-right tabular-nums font-medium text-black dark:text-white">
                            {{ (float) $item->quantity }}
                        </td>
                        <td class="px-3 py-3 text-right tabular-nums font-semibold text-black dark:text-white">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Financial Calculation -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-6 pt-4 border-t border-black/5 dark:border-white/10">
            <div class="space-y-1.5 text-[12px] text-black/60 dark:text-white/60 max-w-sm">
                @if($quotation->notes)
                <p class="font-semibold text-black dark:text-white">Catatan Tambahan:</p>
                <p class="whitespace-pre-line text-black/70 dark:text-white/70">{{ $quotation->notes }}</p>
                @endif
                <p class="text-[11px] text-black/40 dark:text-white/40 pt-2">Diterbitkan secara digital oleh Cooca ERP.</p>
            </div>

            <div class="w-full sm:w-72 space-y-2 text-[13px]">
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>Subtotal:</span>
                    <span class="font-semibold tabular-nums text-black dark:text-white">Rp {{ number_format($quotation->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($quotation->discount_amount > 0)
                <div class="flex justify-between text-[#FF3B30] dark:text-[#FF453A]">
                    <span>Diskon:</span>
                    <span class="font-semibold tabular-nums">- Rp {{ number_format($quotation->discount_amount, 0, ',', '.') }}</span>
                </div>
                @endif
                @if($quotation->tax_amount > 0)
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>Pajak (PPN):</span>
                    <span class="font-semibold tabular-nums text-black dark:text-white">Rp {{ number_format($quotation->tax_amount, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="pt-2 border-t border-black/5 dark:border-white/10 flex justify-between text-[16px] font-bold text-black dark:text-white">
                    <span>Total Penawaran:</span>
                    <span class="text-[#007AFF] dark:text-[#0A84FF] tabular-nums">Rp {{ number_format($quotation->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. APPLE ALERT DIALOG (Convert Confirmation)          -->
    <!-- ===================================================== -->
    <div x-show="convertModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px]"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-[300px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
            @click.away="closeConvert()"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="px-5 pt-5 pb-4">
                <p class="text-[17px] font-semibold text-black dark:text-white">Ubah ke Sales Order?</p>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1.5 leading-snug">
                    Surat penawaran ini akan dikonversi menjadi Pesanan Penjualan resmi dan siap diproses di modul penjualan.
                </p>
            </div>

            <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                <button type="button" @click="closeConvert()" class="py-3 text-black/70 dark:text-white/70 border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Batal
                </button>
                <button type="button" @click="submitConvert()" class="py-3 text-[#007AFF] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Konversi
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
