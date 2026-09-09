@extends('layouts.app', ['title' => 'Detail Retur Penjualan ' . $return->return_number])

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-12">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 print:hidden" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors flex items-center gap-1.5">
            <i data-lucide="home" class="w-3.5 h-3.5"></i>
            <span>Dashboard</span>
        </a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600"></i>
        <span>Kasir &amp; Penjualan</span>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600"></i>
        <a href="{{ route('sales.returns.index') }}" class="hover:text-rose-600 dark:hover:text-rose-400 transition-colors">
            Retur Penjualan
        </a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600"></i>
        <span class="text-slate-900 dark:text-white font-semibold font-mono">{{ $return->return_number }}</span>
    </nav>

    <!-- Top Action & Title Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs print:border-none print:shadow-none print:p-0">
        <div class="space-y-1.5">
            <div class="flex flex-wrap items-center gap-2.5">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/80">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    {{ $return->return_number }}
                </span>
                
                @if($return->status === 'completed')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200/90 dark:border-emerald-800/90">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Selesai &amp; Direstock
                    </span>
                @elseif($return->status === 'approved')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400 border border-blue-200/90 dark:border-blue-800/90">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                        Disetujui
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200/90 dark:border-amber-800/90">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        Draft (Menunggu Persetujuan)
                    </span>
                @endif
            </div>

            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                Nota Retur Penjualan
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 flex flex-wrap items-center gap-2">
                <span>Tanggal: <strong class="text-slate-700 dark:text-slate-300 font-mono">{{ $return->return_date ? $return->return_date->translatedFormat('d F Y') : $return->created_at->format('d/m/Y') }}</strong></span>
                <span>&bull;</span>
                <span>Dibuat: <strong class="text-slate-700 dark:text-slate-300 font-mono">{{ $return->created_at->format('d/m/Y H:i') }}</strong></span>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 print:hidden">
            <a href="{{ route('sales.returns.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali</span>
            </a>

            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/80 transition-colors shadow-2xs">
                <i data-lucide="printer" class="w-4 h-4 text-slate-500"></i>
                <span>Cetak Nota</span>
            </button>

            <!-- State-driven action triggers -->
            @if($return->status === 'draft')
                <form id="approve-form" method="POST" action="{{ route('sales.returns.approve', $return) }}">
                    @csrf
                    <button type="button" onclick="confirmApprove()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 shadow-sm shadow-blue-500/20 active:scale-[0.98] transition-all cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Setujui Retur Penjualan</span>
                    </button>
                </form>
            @elseif($return->status === 'approved')
                <form id="complete-form" method="POST" action="{{ route('sales.returns.complete', $return) }}">
                    @csrf
                    <button type="button" onclick="confirmComplete()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm shadow-emerald-500/20 active:scale-[0.98] transition-all cursor-pointer">
                        <i data-lucide="check-check" class="w-4 h-4"></i>
                        <span>Selesaikan &amp; Masukkan ke Stok Gudang</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Alert Success / Error -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800/80 text-emerald-800 dark:text-emerald-300 text-xs flex items-center gap-3">
            <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/80 text-rose-800 dark:text-rose-300 text-xs flex items-center gap-3">
            <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0"></i>
            <div class="font-medium">
                @foreach($errors->all() as $err)
                    <div>{{ $err }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Workflow Progress Stepper -->
    <div class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 p-5 shadow-xs print:hidden">
        <div class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-4">
            Alur Proses Retur Penjualan
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 relative">
            <!-- Step 1: Draft -->
            <div class="flex items-start gap-3 p-3.5 rounded-xl border {{ $return->status === 'draft' ? 'bg-amber-50/50 dark:bg-amber-950/20 border-amber-200 dark:border-amber-800/60' : 'bg-slate-50/60 dark:bg-slate-800/30 border-slate-200/80 dark:border-slate-800/60' }}">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ in_array($return->status, ['draft', 'approved', 'completed']) ? 'bg-amber-500 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }} font-bold text-xs">
                    @if(in_array($return->status, ['approved', 'completed']))
                        <i data-lucide="check" class="w-4 h-4"></i>
                    @else
                        1
                    @endif
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white">1. Pengajuan Draft</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Item &amp; kuantitas retur dicatat ke sistem.</p>
                    <span class="inline-block mt-1 text-[10px] font-mono text-slate-400">{{ $return->created_at->format('d M Y, H:i') }}</span>
                </div>
            </div>

            <!-- Step 2: Approved -->
            <div class="flex items-start gap-3 p-3.5 rounded-xl border {{ $return->status === 'approved' ? 'bg-blue-50/50 dark:bg-blue-950/20 border-blue-200 dark:border-blue-800/60' : (in_array($return->status, ['completed']) ? 'bg-slate-50/60 dark:bg-slate-800/30 border-slate-200/80 dark:border-slate-800/60' : 'bg-slate-50/30 dark:bg-slate-800/10 border-dashed border-slate-200 dark:border-slate-800') }}">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ in_array($return->status, ['approved', 'completed']) ? 'bg-blue-500 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-400' }} font-bold text-xs">
                    @if($return->status === 'completed')
                        <i data-lucide="check" class="w-4 h-4"></i>
                    @else
                        2
                    @endif
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white">2. Persetujuan Retur</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Disetujui untuk pemeriksaan fisik barang.</p>
                    <span class="inline-block mt-1 text-[10px] font-mono text-slate-400">
                        {{ $return->approved_at ? $return->approved_at->format('d M Y, H:i') : ($return->status === 'approved' ? 'Sedang diverifikasi' : 'Menunggu approval') }}
                    </span>
                </div>
            </div>

            <!-- Step 3: Completed -->
            <div class="flex items-start gap-3 p-3.5 rounded-xl border {{ $return->status === 'completed' ? 'bg-emerald-50/50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800/60' : 'bg-slate-50/30 dark:bg-slate-800/10 border-dashed border-slate-200 dark:border-slate-800' }}">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $return->status === 'completed' ? 'bg-emerald-500 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-400' }} font-bold text-xs">
                    @if($return->status === 'completed')
                        <i data-lucide="check-check" class="w-4 h-4"></i>
                    @else
                        3
                    @endif
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white">3. Selesai &amp; Restock</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Stok gudang bertambah &amp; kompensasi terekam.</p>
                    <span class="inline-block mt-1 text-[10px] font-mono text-slate-400">
                        {{ $return->completed_at ? $return->completed_at->format('d M Y, H:i') : 'Menunggu penyelesaian' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Grid: Origin Reference + Compensation -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Card 1: Origin Transaction & Customer Info -->
        <div class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 p-5 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800/80 pb-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="receipt" class="w-4 h-4 text-cyan-600 dark:text-cyan-400"></i>
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        Sumber Transaksi &amp; Pelanggan
                    </h3>
                </div>
                @if($return->invoice)
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                        Faktur B2B
                    </span>
                @elseif($return->posOrder)
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800">
                        Kasir POS
                    </span>
                @else
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                        Manual
                    </span>
                @endif
            </div>

            <div class="space-y-3 text-xs">
                <div class="flex justify-between items-center py-1 border-b border-slate-50 dark:border-slate-800/40">
                    <span class="text-slate-500 dark:text-slate-400">Nomor Dokumen Asal:</span>
                    <span class="font-mono font-bold text-slate-900 dark:text-white">
                        @if($return->invoice)
                            {{ $return->invoice->invoice_number }}
                        @elseif($return->posOrder)
                            {{ $return->posOrder->order_number }}
                        @else
                            -
                        @endif
                    </span>
                </div>

                <div class="flex justify-between items-center py-1 border-b border-slate-50 dark:border-slate-800/40">
                    <span class="text-slate-500 dark:text-slate-400">Nama Pelanggan:</span>
                    <span class="font-bold text-slate-900 dark:text-white">
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
                <div class="flex justify-between items-center py-1 border-b border-slate-50 dark:border-slate-800/40">
                    <span class="text-slate-500 dark:text-slate-400">Lokasi / Gudang Restock:</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $return->location->name }}</span>
                </div>
                @endif

                <div class="pt-2">
                    <span class="text-slate-500 dark:text-slate-400 block mb-1">Alasan Retur Pelanggan:</span>
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60 text-slate-800 dark:text-slate-200 italic">
                        &ldquo;{{ $return->reason ?: 'Tidak ada catatan spesifik.' }}&rdquo;
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Financial & Compensation Summary -->
        <div class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 p-5 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800/80 pb-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="wallet" class="w-4 h-4 text-rose-600 dark:text-rose-400"></i>
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        Kompensasi &amp; Nilai Retur
                    </h3>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                    Refund
                </span>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-slate-500 dark:text-slate-400 text-[11px] block">Total Nilai Kompensasi / Refund:</span>
                    <div class="text-2xl sm:text-3xl font-black text-rose-600 dark:text-rose-400 font-mono tracking-tight mt-1">
                        Rp {{ number_format($return->total_amount, 0, ',', '.') }}
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400 font-medium">Metode Kompensasi:</span>
                        @if(($return->refund_method ?? 'cash_refund') === 'cash_refund')
                            <span class="inline-flex items-center gap-1 font-bold text-slate-900 dark:text-white uppercase">
                                <i data-lucide="banknote" class="w-3.5 h-3.5 text-emerald-600"></i>
                                Tunai (Cash Refund)
                            </span>
                        @elseif(($return->refund_method ?? '') === 'credit_note')
                            <span class="inline-flex items-center gap-1 font-bold text-slate-900 dark:text-white uppercase">
                                <i data-lucide="file-minus" class="w-3.5 h-3.5 text-blue-600"></i>
                                Pemotongan Tagihan (Credit Note)
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 font-bold text-slate-900 dark:text-white uppercase">
                                <i data-lucide="coins" class="w-3.5 h-3.5 text-amber-600"></i>
                                Deposit (Store Credit)
                            </span>
                        @endif
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                        @if(($return->refund_method ?? 'cash_refund') === 'cash_refund')
                            Pengembalian uang tunai langsung kepada pelanggan dari kas kasir / rekening operasional.
                        @elseif(($return->refund_method ?? '') === 'credit_note')
                            Nilai retur akan dikurangkan langsung dari sisa kewajiban piutang pada faktur penjualan.
                        @else
                            Nilai retur disimpan sebagai saldo kredit/deposit yang dapat digunakan pada pembelian berikutnya.
                        @endif
                    </p>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <span class="text-slate-500 dark:text-slate-400">Status Restock Fisik:</span>
                    @if($return->status === 'completed')
                        <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-bold">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            Stok telah kembali ke gudang
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-amber-600 dark:text-amber-400 font-medium">
                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                            Menunggu penyelesaian
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table Card -->
    <div class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 overflow-hidden shadow-xs">
        <div class="p-4 bg-slate-50/80 dark:bg-slate-900/80 border-b border-slate-200/90 dark:border-slate-800/90 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="package-search" class="w-4 h-4 text-cyan-600 dark:text-cyan-400"></i>
                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                    Daftar Barang yang Diretur Pelanggan
                </h3>
            </div>
            <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                Total: <strong class="text-slate-900 dark:text-white">{{ $return->items->count() }}</strong> Jenis Item
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-100/70 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-bold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">Barang / Item Produk</th>
                        <th class="py-3 px-4 text-center">Kuantitas Retur</th>
                        <th class="py-3 px-4 text-right">Harga Satuan Asal</th>
                        <th class="py-3 px-4 text-right">Nilai Retur (Subtotal)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-mono">
                    @forelse($return->items as $idx => $item)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="py-3.5 px-4 text-center text-slate-400 dark:text-slate-500">
                            {{ $idx + 1 }}
                        </td>
                        <td class="py-3.5 px-4 font-sans">
                            <div class="font-bold text-slate-900 dark:text-white">
                                {{ $item->item_name }}
                            </div>
                            @if($item->product?->sku)
                                <div class="text-[11px] font-mono text-slate-400 dark:text-slate-500">
                                    SKU: {{ $item->product->sku }}
                                </div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md text-xs font-black bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/80">
                                {{ number_format($item->quantity, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right text-slate-600 dark:text-slate-400">
                            Rp {{ number_format($item->unit_price ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-black text-slate-900 dark:text-white">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-400 dark:text-slate-500 italic font-sans">
                            Tidak ada rincian item dalam dokumen retur ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-50/90 dark:bg-slate-950/80 font-mono border-t border-slate-200 dark:border-slate-800">
                    <tr>
                        <td colspan="4" class="py-3.5 px-4 text-right font-sans font-bold text-slate-700 dark:text-slate-300">
                            Grand Total Nilai Retur:
                        </td>
                        <td class="py-3.5 px-4 text-right font-black text-rose-600 dark:text-rose-400 text-sm">
                            Rp {{ number_format($return->total_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Print Authorization Signatures (Visible on Print Only) -->
    <div class="hidden print:grid grid-cols-3 gap-6 pt-12 text-center text-xs text-slate-800">
        <div class="space-y-16">
            <p class="font-semibold">Diajukan oleh (Pelanggan),</p>
            <div>
                <p class="font-bold border-b border-slate-800 pb-1 mx-8 font-sans">
                    {{ $return->invoice?->customer?->name ?? ($return->posOrder?->customer?->name ?? ($return->customer?->name ?? 'Pelanggan')) }}
                </p>
                <p class="text-[10px] text-slate-600 mt-1">Tanda Tangan &amp; Nama Jelas</p>
            </div>
        </div>

        <div class="space-y-16">
            <p class="font-semibold">Diterima Fisik (Staff Gudang),</p>
            <div>
                <p class="font-bold border-b border-slate-800 pb-1 mx-8 font-sans">( ........................................ )</p>
                <p class="text-[10px] text-slate-600 mt-1">Petugas Verifikasi Fisik</p>
            </div>
        </div>

        <div class="space-y-16">
            <p class="font-semibold">Disetujui oleh (Supervisor / Kasir),</p>
            <div>
                <p class="font-bold border-b border-slate-800 pb-1 mx-8 font-sans">( ........................................ )</p>
                <p class="text-[10px] text-slate-600 mt-1">Authorized Signature</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmApprove() {
    Swal.fire({
        title: 'Setujui Retur Penjualan?',
        text: 'Status dokumen akan disetujui. Petugas gudang dapat bersiap memverifikasi fisik barang yang diretur.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Setujui',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses Persetujuan...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            document.getElementById('approve-form').submit();
        }
    });
}

function confirmComplete() {
    Swal.fire({
        title: 'Selesaikan & Masukkan ke Stok Gudang?',
        text: 'Sistem akan menambahkan kuantitas barang kembali ke stok inventori gudang dan mencatat penyesuaian kompensasi pengembalian.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#059669',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Selesaikan & Restock',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses Restock...',
                text: 'Memasukkan barang kembali ke stok inventori...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            document.getElementById('complete-form').submit();
        }
    });
}
</script>
@endpush
@endsection
