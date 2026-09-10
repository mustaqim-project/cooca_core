@extends('layouts.app', [
    'title' => 'Buat Retur Pembelian',
    'headerTitle' => 'Buat Retur Pembelian',
    'headerSubtitle' => 'Formulir pengembalian fisik barang ke vendor dari Goods Receipt yang telah diterima'
])

@section('content')
<div class="max-w-[880px] mx-auto space-y-6 pb-12">
    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / HEADER (macOS Sonoma Style)              -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <!-- Breadcrumb minimal -->
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <a href="{{ route('purchase.returns.index') }}" class="hover:text-[#007AFF] transition-colors">Retur Pembelian</a>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Form Retur Baru</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Formulir Retur Pembelian</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Kembalikan fisik barang ke vendor dari Goods Receipt yang telah diterima.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('purchase.returns.index') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Batal &amp; Kembali</span>
            </a>
        </div>
    </header>

    @if($errors->any())
    <div class="rounded-[12px] bg-[#FF3B30]/12 border border-[#FF3B30]/20 p-4 text-[13px] text-[#C41E17] dark:text-[#FF453A] space-y-1">
        @foreach($errors->all() as $error)
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <span>{{ $error }}</span>
            </div>
        @endforeach
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 2. RETURN CREATION FORM                               -->
    <!-- ===================================================== -->
    <form method="POST" action="{{ route('purchase.returns.store') }}" class="space-y-6">
        @csrf

        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5">
            <!-- Dokumen Penerimaan -->
            <div>
                <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">Pilih Penerimaan Barang (Goods Receipt) <span class="text-[#FF3B30]">*</span></label>
                <select name="goods_receipt_id" required class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    <option value="">-- Pilih Dokumen Goods Receipt --</option>
                    @foreach($receipts as $receipt)
                        <option value="{{ $receipt->id }}">
                            {{ $receipt->receipt_number }} — {{ $receipt->supplier?->name ?? 'Supplier' }} ({{ $receipt->received_date?->format('d/m/Y') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Detail Item yang Dikembalikan -->
            <div class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 p-4 space-y-3">
                <div class="text-[13px] font-semibold text-black dark:text-white">Detail Baris Item yang Dikembalikan</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-medium text-black/60 dark:text-white/60 mb-1">ID Item Goods Receipt <span class="text-[#FF3B30]">*</span></label>
                        <input name="items[0][goods_receipt_item_id]" placeholder="Masukkan ID baris item GR" required
                               class="w-full h-10 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-3 text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition shadow-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-black/60 dark:text-white/60 mb-1">Kuantitas Dikembalikan (Qty) <span class="text-[#FF3B30]">*</span></label>
                        <input name="items[0][quantity]" type="number" min="0.01" step="any" placeholder="Contoh: 5" required
                               class="w-full h-10 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-3 text-[13px] font-semibold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition shadow-xs">
                    </div>
                </div>
            </div>

            <!-- Alasan Pengembalian -->
            <div>
                <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">Alasan Pengembalian / Catatan Klaim <span class="text-[#FF3B30]">*</span></label>
                <input name="reason" placeholder="Contoh: Kemasan bocor saat pengiriman, produk kedaluwarsa, dsb." required
                       class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-black/5 dark:border-white/10">
                <a href="{{ route('purchase.returns.index') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.97] active:opacity-80 transition-all flex items-center">
                    Batal
                </a>
                <button type="submit" class="h-9 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <span>Simpan Draft Retur</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
