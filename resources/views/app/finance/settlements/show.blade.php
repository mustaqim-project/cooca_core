@extends('layouts.app', ['title' => 'Detail Rekonsiliasi Settlement #' . $settlement->settlement_number])

@section('content')
<div x-data="{ showProofModal: false }" class="max-w-[1000px] mx-auto space-y-6 pb-16">

    {{-- ========================================================== --}}
    {{-- TOOLBAR / PAGE HEADER                                      --}}
    {{-- ========================================================== --}}
    <header class="rounded-[20px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 px-5 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xs">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('finance.settlements.index') }}" class="hover:text-black dark:hover:text-white transition">Rekonsiliasi Gateway</a>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Detail Settlement</span>
            </nav>
            <h1 class="text-[20px] font-bold text-black dark:text-white tracking-tight flex items-center gap-2.5">
                <span>Settlement #{{ $settlement->settlement_number }}</span>
                @if($settlement->isCompleted())
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        <span>Ditransfer &amp; Selesai</span>
                    </span>
                @elseif($settlement->isPending())
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#FF9500]/15 text-[#D97706] dark:text-[#F59E0B] inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Menunggu Transfer Admin</span>
                    </span>
                @elseif($settlement->isRejected())
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#FF3B30]/15 text-[#FF3B30] inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Ditolak</span>
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#007AFF]/15 text-[#007AFF]">
                        {{ ucfirst($settlement->status) }}
                    </span>
                @endif
            </h1>
            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">
                Diajukan pada {{ \Carbon\Carbon::parse($settlement->settlement_date)->format('d F Y') }}
                @if($settlement->reconciledBy)
                    oleh {{ $settlement->reconciledBy->name }}
                @endif
            </p>
        </div>
        <div>
            <a href="{{ route('finance.settlements.index') }}"
                class="h-9 px-4 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition flex items-center gap-1.5 active:scale-[0.98]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Kembali ke Daftar</span>
            </a>
        </div>
    </header>

    {{-- ========================================================== --}}
    {{-- STATUS SPECIFIC BENTO CARD (BUKTI BAYAR / PENDING / REJECT) --}}
    {{-- ========================================================== --}}
    @if($settlement->isCompleted() && $settlement->proof_image_path)
        {{-- BUKTI TRANSFER ADMIN TERSEDIA --}}
        <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-[#34C759]/25 p-5 sm:p-6 shadow-sm space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-black/5 dark:border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[14px] bg-[#34C759]/15 text-[#34C759] flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white tracking-tight">Bukti Transfer Resmi dari Admin COOCA</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50">Dana pencairan telah ditransfer ke rekening bisnis Anda oleh tim COOCA Platform</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="showProofModal = true"
                        class="h-9 px-3.5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12.5px] font-semibold flex items-center gap-1.5 transition active:scale-[0.98] shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Lihat Bukti Foto</span>
                    </button>
                    <a href="{{ $settlement->proof_image_url }}" download="bukti-transfer-{{ $settlement->settlement_number }}.jpg" target="_blank"
                        class="h-9 px-3.5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] text-black dark:text-white text-[12.5px] font-semibold flex items-center gap-1.5 transition active:scale-[0.98]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        <span>Unduh Struk</span>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-start">
                {{-- Preview Thumbnail --}}
                <div class="md:col-span-4 cursor-pointer group" @click="showProofModal = true">
                    <div class="relative rounded-[16px] overflow-hidden border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] aspect-4/3 flex items-center justify-center">
                        <img src="{{ $settlement->proof_image_url }}" alt="Bukti Transfer Settlement" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-semibold gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6"/></svg>
                            <span>Klik untuk Memperbesar</span>
                        </div>
                    </div>
                </div>

                {{-- Detail Transfer Admin --}}
                <div class="md:col-span-8 space-y-3 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                        <div>
                            <span class="text-black/45 dark:text-white/45 block text-[11px] font-medium uppercase tracking-wider">Waktu Transfer</span>
                            <span class="text-[13px] font-bold text-black dark:text-white tabular-nums block mt-0.5">
                                {{ $settlement->transferred_at ? $settlement->transferred_at->format('d F Y, H:i') . ' WIB' : '-' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-black/45 dark:text-white/45 block text-[11px] font-medium uppercase tracking-wider">Diverifikasi &amp; Dikirim Oleh</span>
                            <span class="text-[13px] font-bold text-black dark:text-white block mt-0.5">
                                {{ $settlement->admin?->name ?? 'Admin COOCA Platform' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-black/45 dark:text-white/45 block text-[11px] font-medium uppercase tracking-wider">Rekening Tujuan Toko</span>
                            <span class="text-[13px] font-bold text-black dark:text-white block mt-0.5">
                                {{ $settlement->destination_bank ?? 'Rekening Bank Toko' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-black/45 dark:text-white/45 block text-[11px] font-medium uppercase tracking-wider">Nominal Bersih Cair</span>
                            <span class="text-[14px] font-extrabold text-[#34C759] dark:text-[#30D158] tabular-nums block mt-0.5">
                                {{ $business->currency_symbol }} {{ number_format($settlement->net_amount, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    @if($settlement->admin_notes)
                        <div class="p-3.5 rounded-[14px] bg-[#34C759]/5 border border-[#34C759]/15 text-xs text-[#248A3D] dark:text-[#30D158]">
                            <span class="font-bold">Catatan Admin COOCA:</span>
                            <span class="ml-1">{{ $settlement->admin_notes }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @elseif($settlement->isPending())
        {{-- PENDING BANNER --}}
        <div class="rounded-[20px] bg-[#FF9500]/5 border border-[#FF9500]/20 p-5 sm:p-6 space-y-3">
            <div class="flex items-start gap-3.5">
                <div class="w-9 h-9 rounded-[12px] bg-[#FF9500]/15 text-[#D97706] dark:text-[#F59E0B] flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-[15px] font-bold text-black dark:text-white">Menunggu Proses Transfer &amp; Unggah Bukti Bayar</h3>
                    <p class="text-[12.5px] text-black/70 dark:text-white/70 leading-relaxed">
                        Permintaan pencairan dana sebesar <strong class="text-black dark:text-white">{{ $business->currency_symbol }} {{ number_format($settlement->net_amount, 0, ',', '.') }}</strong> telah masuk ke antrean platform COOCA. Admin COOCA akan memproses transfer ke rekening <strong>{{ $settlement->destination_bank ?? 'Bank Merchant' }}</strong> dan mengunggah bukti bayar resmi di sini.
                    </p>
                </div>
            </div>
        </div>
    @elseif($settlement->isRejected())
        {{-- REJECTED BANNER --}}
        <div class="rounded-[20px] bg-[#FF3B30]/5 border border-[#FF3B30]/20 p-5 sm:p-6 space-y-3">
            <div class="flex items-start gap-3.5">
                <div class="w-9 h-9 rounded-[12px] bg-[#FF3B30]/15 text-[#FF3B30] flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-[15px] font-bold text-[#FF3B30]">Pengajuan Pencairan Ditolak</h3>
                    <p class="text-[12.5px] text-black/70 dark:text-white/70 leading-relaxed">
                        Alasan penolakan: <strong class="text-black dark:text-white">{{ $settlement->rejection_reason ?: 'Rekening tidak valid atau kendala operasional.' }}</strong>
                    </p>
                    <p class="text-[11.5px] text-black/50 dark:text-white/50">
                        Seluruh transaksi pada pengajuan ini telah dikembalikan ke saldo gateway siap cair sehingga Anda dapat mengajukannya kembali.
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================== --}}
    {{-- SETTLEMENT SUMMARY BENTO CARDS                             --}}
    {{-- ========================================================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 space-y-1 shadow-xs">
            <span class="text-xs text-black/50 dark:text-white/50 block font-medium">Total Nominal Bruto</span>
            <span class="text-xl font-extrabold text-black dark:text-white tabular-nums">
                {{ $business->currency_symbol }} {{ number_format($settlement->gross_amount, 0, ',', '.') }}
            </span>
            <span class="text-[11px] text-black/45 dark:text-white/45 block">Akun Clearing: 1-1005 (Gateway Escrow)</span>
        </div>

        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 space-y-1 shadow-xs">
            <span class="text-xs text-black/50 dark:text-white/50 block font-medium">Beban Administrasi MDR</span>
            <span class="text-xl font-extrabold text-[#FF3B30] tabular-nums">
                {{ $business->currency_symbol }} {{ number_format($settlement->fee_amount, 0, ',', '.') }}
            </span>
            <span class="text-[11px] text-black/45 dark:text-white/45 block">Akun Beban: 6-6003 (Biaya Gateway)</span>
        </div>

        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 space-y-1 shadow-xs">
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
    <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-xs">
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
                <span class="font-semibold text-black dark:text-white">Catatan Merchant:</span>
                <span class="text-black/60 dark:text-white/60 ml-1">{{ $settlement->notes }}</span>
            </div>
        @endif
    </div>

    {{-- ========================================================== --}}
    {{-- MODAL PREVIEW BUKTI TRANSFER (APPLE HIG MODAL)             --}}
    {{-- ========================================================== --}}
    @if($settlement->proof_image_url)
        <div x-show="showProofModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/60 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div @click.away="showProofModal = false"
                class="bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[24px] max-w-2xl w-full p-6 shadow-2xl space-y-4 max-h-[90vh] flex flex-col">
                <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                    <div>
                        <h4 class="text-base font-bold text-black dark:text-white">Bukti Transfer Pencairan</h4>
                        <p class="text-xs text-black/50 dark:text-white/50">Settlement #{{ $settlement->settlement_number }}</p>
                    </div>
                    <button @click="showProofModal = false" class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:bg-black/10 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="flex-1 overflow-auto flex items-center justify-center bg-black/[0.03] dark:bg-white/[0.03] rounded-[16px] p-2">
                    <img src="{{ $settlement->proof_image_url }}" alt="Bukti Transfer" class="max-h-[65vh] w-auto object-contain rounded-[12px] shadow-sm">
                </div>
                <div class="flex items-center justify-between pt-2">
                    <span class="text-xs text-black/50 dark:text-white/50">Ditransfer: {{ $settlement->transferred_at?->format('d M Y, H:i') }} WIB</span>
                    <div class="flex gap-2">
                        <a href="{{ $settlement->proof_image_url }}" download="bukti-transfer-{{ $settlement->settlement_number }}.jpg" target="_blank"
                            class="h-9 px-4 rounded-[12px] bg-[#007AFF] text-white text-xs font-semibold flex items-center gap-1.5 hover:bg-[#0071E3] transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            <span>Unduh File</span>
                        </a>
                        <button type="button" @click="showProofModal = false" class="h-9 px-4 rounded-[12px] bg-black/5 dark:bg-white/10 text-black dark:text-white text-xs font-semibold hover:bg-black/10 transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
