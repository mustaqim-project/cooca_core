@extends('layouts.app', ['title' => 'Detail Retur Penjualan ' . $return->return_number])

@section('content')
<div class="max-w-[1100px] mx-auto space-y-6 pb-12" x-data="{
    approveModalOpen: false,
    completeModalOpen: false,

    promptApprove() {
        this.approveModalOpen = true;
    },
    submitApprove() {
        this.approveModalOpen = false;
        document.getElementById('approve-form').submit();
    },

    promptComplete() {
        this.completeModalOpen = true;
    },
    submitComplete() {
        this.completeModalOpen = false;
        document.getElementById('complete-form').submit();
    }
}">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 print:hidden">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <a href="{{ route('sales.returns.index') }}" class="hover:text-[#007AFF] transition-colors">Retur Penjualan</a>
                <span>›</span>
                <span class="text-black dark:text-white font-mono font-medium">{{ $return->return_number }}</span>
            </nav>
            <div class="flex items-center gap-2.5">
                <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight font-mono">{{ $return->return_number }}</h1>
                @if($return->status === 'completed')
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Selesai &amp; Direstock
                    </span>
                @elseif($return->status === 'approved')
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span> Disetujui
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span> Draft (Menunggu Persetujuan)
                    </span>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- State-driven action triggers -->
            @if($return->status === 'draft')
                <button type="button" @click="promptApprove()"
                    class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <span>Setujui Retur</span>
                </button>

                <form id="approve-form" method="POST" action="{{ route('sales.returns.approve', $return) }}" class="hidden">
                    @csrf
                </form>
            @elseif($return->status === 'approved')
                <button type="button" @click="promptComplete()"
                    class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#34C759] hover:bg-[#30B350] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(52,199,89,0.25)]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Selesaikan &amp; Restock</span>
                </button>

                <form id="complete-form" method="POST" action="{{ route('sales.returns.complete', $return) }}" class="hidden">
                    @csrf
                </form>
            @endif

            <!-- Print Button -->
            <button type="button" onclick="window.print()"
                class="h-9 px-3 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                </svg>
                <span>Cetak Nota</span>
            </button>

            <!-- Back Link -->
            <a href="{{ route('sales.returns.index') }}"
                class="h-9 px-3 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition flex items-center">
                <span>Kembali</span>
            </a>
        </div>
    </header>

    <!-- Flash Notifications (Apple Banner Style) -->
    @if(session('success'))
        <div class="rounded-[14px] bg-[#34C759]/12 border border-[#34C759]/20 px-4 py-3 text-[13px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-3">
            <span class="w-2 h-2 rounded-full bg-[#34C759] shrink-0"></span>
            <div class="flex-1 font-medium">{{ session('success') }}</div>
        </div>
    @endif
    @if($errors->any())
        <div class="rounded-[14px] bg-[#FF3B30]/12 border border-[#FF3B30]/20 px-4 py-3 text-[13px] text-[#C41E17] dark:text-[#FF453A] flex items-center gap-3">
            <span class="w-2 h-2 rounded-full bg-[#FF3B30] shrink-0"></span>
            <div class="flex-1 font-medium">
                @foreach($errors->all() as $err)
                    <div>{{ $err }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Workflow Progress Stepper (Apple HIG Style) -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 print:hidden">
        <span class="text-[11px] font-semibold text-black/40 dark:text-white/40 uppercase tracking-wider block mb-3">
            Alur Proses Retur Penjualan
        </span>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <!-- Step 1: Draft -->
            <div class="rounded-[10px] p-3.5 border {{ $return->status === 'draft' ? 'bg-[#FF9500]/8 border-[#FF9500]/20' : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/5 dark:border-white/5' }} flex items-start gap-3">
                <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 {{ in_array($return->status, ['draft', 'approved', 'completed']) ? 'bg-[#FF9500] text-white' : 'bg-black/10 dark:bg-white/10 text-black/40' }} font-bold text-[11px]">
                    @if(in_array($return->status, ['approved', 'completed']))
                        ✓
                    @else
                        1
                    @endif
                </div>
                <div>
                    <h3 class="text-[13px] font-semibold text-black dark:text-white">1. Pengajuan Draft</h3>
                    <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">Item &amp; kuantitas retur dicatat ke sistem.</p>
                    <span class="text-[10px] tabular-nums text-black/40 dark:text-white/40 block mt-1">{{ $return->created_at->format('d M Y, H:i') }}</span>
                </div>
            </div>

            <!-- Step 2: Approved -->
            <div class="rounded-[10px] p-3.5 border {{ $return->status === 'approved' ? 'bg-[#007AFF]/8 border-[#007AFF]/20' : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/5 dark:border-white/5' }} flex items-start gap-3">
                <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 {{ in_array($return->status, ['approved', 'completed']) ? 'bg-[#007AFF] text-white' : 'bg-black/10 dark:bg-white/10 text-black/40' }} font-bold text-[11px]">
                    @if($return->status === 'completed')
                        ✓
                    @else
                        2
                    @endif
                </div>
                <div>
                    <h3 class="text-[13px] font-semibold text-black dark:text-white">2. Persetujuan Retur</h3>
                    <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">Disetujui untuk pemeriksaan fisik barang.</p>
                    <span class="text-[10px] tabular-nums text-black/40 dark:text-white/40 block mt-1">
                        {{ $return->approved_at ? $return->approved_at->format('d M Y, H:i') : ($return->status === 'approved' ? 'Sedang diverifikasi' : 'Menunggu approval') }}
                    </span>
                </div>
            </div>

            <!-- Step 3: Completed -->
            <div class="rounded-[10px] p-3.5 border {{ $return->status === 'completed' ? 'bg-[#34C759]/8 border-[#34C759]/20' : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/5 dark:border-white/5' }} flex items-start gap-3">
                <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 {{ $return->status === 'completed' ? 'bg-[#34C759] text-white' : 'bg-black/10 dark:bg-white/10 text-black/40' }} font-bold text-[11px]">
                    @if($return->status === 'completed')
                        ✓
                    @else
                        3
                    @endif
                </div>
                <div>
                    <h3 class="text-[13px] font-semibold text-black dark:text-white">3. Selesai &amp; Restock</h3>
                    <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">Stok gudang bertambah &amp; kompensasi terekam.</p>
                    <span class="text-[10px] tabular-nums text-black/40 dark:text-white/40 block mt-1">
                        {{ $return->completed_at ? $return->completed_at->format('d M Y, H:i') : 'Menunggu penyelesaian' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Grid: Origin Reference + Compensation -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Card 1: Sumber Transaksi & Pelanggan -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-3">
            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/5 pb-2.5">
                <h3 class="text-[13px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">
                    Sumber Transaksi &amp; Pelanggan
                </h3>
                @if($return->invoice)
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-[#007AFF]/10 text-[#007AFF]">
                        Faktur B2B
                    </span>
                @elseif($return->posOrder)
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-[#5856D6]/10 text-[#5856D6]">
                        Kasir POS
                    </span>
                @else
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-black/5 dark:bg-white/5 text-black/50 dark:text-white/50">
                        Manual
                    </span>
                @endif
            </div>

            <div class="space-y-2 text-[13px]">
                <div class="flex justify-between items-center py-1 border-b border-black/[0.04] dark:border-white/[0.04]">
                    <span class="text-black/50 dark:text-white/50">Nomor Dokumen Asal:</span>
                    <span class="font-semibold tabular-nums text-black dark:text-white">
                        @if($return->invoice)
                            {{ $return->invoice->invoice_number }}
                        @elseif($return->posOrder)
                            {{ $return->posOrder->order_number }}
                        @else
                            —
                        @endif
                    </span>
                </div>

                <div class="flex justify-between items-center py-1 border-b border-black/[0.04] dark:border-white/[0.04]">
                    <span class="text-black/50 dark:text-white/50">Nama Pelanggan:</span>
                    <span class="font-semibold text-black dark:text-white">
                        @if($return->invoice)
                            {{ $return->invoice->customer?->name ?? 'Pelanggan Umum' }}
                        @elseif($return->posOrder)
                            {{ $return->posOrder->customer?->name ?? ($return->posOrder->customer_name_guest ?: 'Tamu Kasir') }}
                        @else
                            {{ $return->customer?->name ?? 'Pelanggan Umum' }}
                        @endif
                    </span>
                </div>

                @if($return->location)
                <div class="flex justify-between items-center py-1 border-b border-black/[0.04] dark:border-white/[0.04]">
                    <span class="text-black/50 dark:text-white/50">Gudang Restock:</span>
                    <span class="font-medium text-black dark:text-white">{{ $return->location->name }}</span>
                </div>
                @endif

                <div class="pt-1">
                    <span class="text-black/50 dark:text-white/50 block text-[12px] mb-1">Alasan Retur Pelanggan:</span>
                    <div class="p-3 rounded-[8px] bg-black/[0.02] dark:bg-white/[0.03] text-black/80 dark:text-white/80 italic text-[12px]">
                        &ldquo;{{ $return->reason ?: 'Tidak ada catatan spesifik.' }}&rdquo;
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Kompensasi & Nilai Retur -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-3">
            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/5 pb-2.5">
                <h3 class="text-[13px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">
                    Kompensasi &amp; Nilai Retur
                </h3>
                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-[#FF3B30]/10 text-[#FF3B30]">
                    Refund
                </span>
            </div>

            <div class="space-y-3 text-[13px]">
                <div>
                    <span class="text-[12px] text-black/45 dark:text-white/45 block">Total Nilai Refund / Kompensasi:</span>
                    <div class="text-[24px] font-bold tabular-nums text-[#FF3B30] mt-0.5">
                        Rp {{ number_format($return->total_amount, 0, ',', '.') }}
                    </div>
                </div>

                <div class="p-3 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-black/50 dark:text-white/50">Metode Kompensasi:</span>
                        @if(($return->refund_method ?? 'cash_refund') === 'cash_refund')
                            <span class="font-semibold text-black dark:text-white">Tunai (Cash Refund)</span>
                        @elseif(($return->refund_method ?? '') === 'credit_note')
                            <span class="font-semibold text-[#007AFF]">Pemotongan Tagihan (Credit Note)</span>
                        @else
                            <span class="font-semibold text-[#FF9500]">Deposit (Store Credit)</span>
                        @endif
                    </div>
                    <p class="text-[11px] text-black/45 dark:text-white/45 leading-relaxed">
                        @if(($return->refund_method ?? 'cash_refund') === 'cash_refund')
                            Pengembalian uang tunai langsung kepada pelanggan dari kas kasir / rekening operasional.
                        @elseif(($return->refund_method ?? '') === 'credit_note')
                            Nilai retur akan dikurangkan langsung dari sisa kewajiban piutang pada faktur penjualan.
                        @else
                            Nilai retur disimpan sebagai saldo deposit pelanggan untuk pembelian berikutnya.
                        @endif
                    </p>
                </div>

                <div class="flex items-center justify-between pt-1 text-[12px]">
                    <span class="text-black/50 dark:text-white/50">Status Restock Fisik:</span>
                    @if($return->status === 'completed')
                        <span class="text-[#34C759] dark:text-[#30D158] font-semibold flex items-center gap-1">
                            <span>✓</span> Stok telah kembali ke gudang
                        </span>
                    @else
                        <span class="text-[#FF9500] font-medium">Menunggu penyelesaian fisik</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table Card -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="p-4 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
            <h3 class="text-[15px] font-semibold text-black dark:text-white">
                Daftar Barang yang Diretur Pelanggan
            </h3>
            <span class="text-[12px] tabular-nums text-black/50 dark:text-white/50">
                Total: <strong class="text-black dark:text-white">{{ $return->items->count() }}</strong> Jenis Item
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10 text-black/40 dark:text-white/40 text-[11px] font-semibold uppercase tracking-wide">
                        <th class="py-2.5 px-4 w-12 text-center">#</th>
                        <th class="py-2.5 px-4">Barang / Item Produk</th>
                        <th class="py-2.5 px-4 text-center">Kuantitas Retur</th>
                        <th class="py-2.5 px-4 text-right">Harga Satuan Asal</th>
                        <th class="py-2.5 px-4 text-right">Nilai Retur (Subtotal)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($return->items as $idx => $item)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="py-3 px-4 text-center tabular-nums text-black/40 dark:text-white/40">
                            {{ $idx + 1 }}
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-medium text-black dark:text-white">
                                {{ $item->item_name }}
                            </div>
                            @if($item->product?->sku)
                                <div class="text-[11px] font-mono text-black/40 dark:text-white/40">
                                    SKU: {{ $item->product->sku }}
                                </div>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center tabular-nums font-semibold text-black dark:text-white">
                            {{ number_format($item->quantity, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums text-black/60 dark:text-white/60">
                            Rp {{ number_format($item->unit_price ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums font-semibold text-[#FF3B30]">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-black/40 dark:text-white/40 italic">
                            Tidak ada rincian item dalam dokumen retur ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="border-t border-black/5 dark:border-white/10">
                    <tr>
                        <td colspan="4" class="py-3 px-4 text-right font-semibold text-black dark:text-white">
                            Grand Total Nilai Retur:
                        </td>
                        <td class="py-3 px-4 text-right font-bold tabular-nums text-[#FF3B30] text-[15px]">
                            Rp {{ number_format($return->total_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Print Signatures (Visible on Print Only) -->
    <div class="hidden print:grid grid-cols-3 gap-6 pt-12 text-center text-xs text-black/70">
        <div class="space-y-16">
            <p class="font-semibold">Diajukan oleh (Pelanggan),</p>
            <div>
                <p class="font-bold border-b border-black/30 pb-1 mx-8">
                    {{ $return->invoice?->customer?->name ?? ($return->posOrder?->customer?->name ?? ($return->customer?->name ?? 'Pelanggan')) }}
                </p>
                <p class="text-[10px] text-black/50 mt-1">Tanda Tangan &amp; Nama Jelas</p>
            </div>
        </div>

        <div class="space-y-16">
            <p class="font-semibold">Diterima Fisik (Staff Gudang),</p>
            <div>
                <p class="font-bold border-b border-black/30 pb-1 mx-8">( ........................................ )</p>
                <p class="text-[10px] text-black/50 mt-1">Petugas Verifikasi Fisik</p>
            </div>
        </div>

        <div class="space-y-16">
            <p class="font-semibold">Disetujui oleh (Supervisor / Kasir),</p>
            <div>
                <p class="font-bold border-b border-black/30 pb-1 mx-8">( ........................................ )</p>
                <p class="text-[10px] text-black/50 mt-1">Authorized Signature</p>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- APPLE ALERT DIALOG: SETUJUI RETUR                     -->
    <!-- ===================================================== -->
    <div x-show="approveModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px]"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-[290px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
            @click.away="approveModalOpen = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="px-4 pt-5 pb-4">
                <p class="text-[17px] font-semibold text-black dark:text-white">Setujui Retur?</p>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                    Status dokumen akan disetujui untuk pemeriksaan fisik barang yang dikembalikan.
                </p>
            </div>

            <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                <button type="button" @click="approveModalOpen = false" class="py-3 text-black/60 dark:text-white/60 border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Batal
                </button>
                <button type="button" @click="submitApprove()" class="py-3 text-[#007AFF] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Setujui
                </button>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- APPLE ALERT DIALOG: SELESAIKAN & RESTOCK              -->
    <!-- ===================================================== -->
    <div x-show="completeModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px]"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-[290px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
            @click.away="completeModalOpen = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="px-4 pt-5 pb-4">
                <p class="text-[17px] font-semibold text-black dark:text-white">Selesaikan &amp; Restock?</p>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                    Sistem akan mengembalikan kuantitas barang ke stok gudang dan mencatat penyesuaian kompensasi.
                </p>
            </div>

            <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                <button type="button" @click="completeModalOpen = false" class="py-3 text-black/60 dark:text-white/60 border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Batal
                </button>
                <button type="button" @click="submitComplete()" class="py-3 text-[#34C759] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Selesaikan
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
