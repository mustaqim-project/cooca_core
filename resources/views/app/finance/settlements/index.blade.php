@extends('layouts.app', ['title' => 'Cooca Pay Payout Hub'])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="payoutHubApp()">

    {{-- ========================================================== --}}
    {{-- TOOLBAR / PAGE HEADER                                      --}}
    {{-- ========================================================== --}}
    <x-module-header
        title="Cooca Pay Payout Hub"
        subtitle="Kelola pencairan dana QRIS, Toko Online & pembayaran gateway ke rekening bank terverifikasi">
        <a href="{{ route('finance.cash-bank.ledger') }}"
            class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] transition-all flex items-center justify-center gap-1.5">
            <i data-lucide="book-open" class="w-4 h-4"></i>
            <span>Buku Kas &amp; Ledger</span>
        </a>
        <a href="{{ route('finance.journals.index') }}"
            class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5">
            <i data-lucide="split" class="w-4 h-4"></i>
            <span>Jurnal Otomatis</span>
        </a>
    </x-module-header>

    {{-- ========================================================== --}}
    {{-- MODULE TABS (SSOT)                                         --}}
    {{-- ========================================================== --}}
    <x-module-tabs module="finance" />

    {{-- ========================================================== --}}
    {{-- BENTO KPI CARDS: COOCA PAY CLEARING STATS                  --}}
    {{-- ========================================================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Unsettled Gross --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 space-y-2 shadow-sm">
            <div class="flex items-center justify-between text-xs text-black/50 dark:text-white/50">
                <span class="font-medium text-[11px] uppercase tracking-wider">Dana Mengendap</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500]/10 text-[#D97706] dark:text-[#FBBF24]">Cooca Pay Escrow</span>
            </div>
            <div class="text-[22px] font-extrabold text-black dark:text-white tabular-nums tracking-tight">
                {{ $business->currency_symbol }} {{ number_format($unsettledData['summary']['total_gross'] ?? 0, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-black/45 dark:text-white/45 flex items-center justify-between">
                <span>{{ $unsettledData['summary']['count'] ?? 0 }} transaksi siap dicairkan</span>
                <span class="font-mono text-[10px]">QRIS + Online</span>
            </div>
        </div>

        {{-- Card 2: Estimated Gateway Fee --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 space-y-2 shadow-sm">
            <div class="flex items-center justify-between text-xs text-black/50 dark:text-white/50">
                <span class="font-medium text-[11px] uppercase tracking-wider">Total Fee MDR</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF3B30]/10 text-[#FF3B30]">Potongan</span>
            </div>
            <div class="text-[22px] font-extrabold text-[#FF3B30] tabular-nums tracking-tight">
                {{ $business->currency_symbol }} {{ number_format($unsettledData['summary']['total_fee'] ?? 0, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-black/45 dark:text-white/45">
                Rp 750 + 0,7% per transaksi QRIS
            </div>
        </div>

        {{-- Card 3: Net Payout Estimate --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 space-y-2 shadow-sm">
            <div class="flex items-center justify-between text-xs text-black/50 dark:text-white/50">
                <span class="font-medium text-[11px] uppercase tracking-wider">Estimasi Bersih Cair</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]">Bank Anda</span>
            </div>
            <div class="text-[22px] font-extrabold text-[#34C759] dark:text-[#30D158] tabular-nums tracking-tight">
                {{ $business->currency_symbol }} {{ number_format($unsettledData['summary']['total_net'] ?? 0, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-black/45 dark:text-white/45">
                Dana bersih setelah potongan MDR
            </div>
        </div>

        {{-- Card 4: Historical Completed Payouts --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 space-y-2 shadow-sm">
            <div class="flex items-center justify-between text-xs text-black/50 dark:text-white/50">
                <span class="font-medium text-[11px] uppercase tracking-wider">Payout Selesai</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#007AFF]/10 text-[#007AFF]">Ditransfer</span>
            </div>
            <div class="text-[22px] font-extrabold text-[#007AFF] tabular-nums tracking-tight">
                {{ $business->currency_symbol }} {{ number_format($settlements->sum('net_amount'), 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-black/45 dark:text-white/45">
                {{ $settlements->total() }} riwayat pencairan
            </div>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- SECTION: REKENING PENARIKAN TERVERIFIKASI                  --}}
    {{-- ========================================================== --}}
    @if($payoutBankAccounts->isEmpty())
        <div class="rounded-[16px] bg-[#FF9500]/5 dark:bg-[#FF9500]/10 border border-[#FF9500]/20 p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-[#FF9500]/15 flex items-center justify-center shrink-0">
                    <i data-lucide="shield-alert" class="w-5 h-5 text-[#FF9500]"></i>
                </div>
                <div>
                    <h3 class="text-[14px] font-bold text-[#D97706] dark:text-[#FBBF24]">Belum Ada Rekening Penarikan Terverifikasi</h3>
                    <p class="text-xs text-black/60 dark:text-white/60 mt-0.5">Daftarkan rekening bank penarikan di menu Pengaturan → Rekening Penarikan Terverifikasi agar bisa mengajukan pencairan melalui Payout Hub.</p>
                </div>
            </div>
            <a href="{{ route('finance.payout-accounts.index') }}"
                class="h-10 px-5 rounded-[10px] bg-[#FF9500] hover:bg-[#E68900] text-white text-xs font-bold flex items-center justify-center gap-1.5 shrink-0 transition active:scale-[0.97]">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                Daftarkan Rekening
            </a>
        </div>
    @endif

    {{-- ========================================================== --}}
    {{-- SECTION: DAFTAR TRANSAKSI SIAP PENCAIRAN (ACTION)          --}}
    {{-- ========================================================== --}}
    <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-sm">
        <div class="px-5 sm:px-6 py-4 border-b border-black/5 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-black/[0.01] dark:bg-white/[0.01]">
            <div>
                <h2 class="text-[16px] font-bold text-black dark:text-white">Pencairan Dana Cooca Pay</h2>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Pilih transaksi QRIS atau toko online yang telah lunas untuk diajukan pencairan ke rekening bank terverifikasi</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="toggleSelectAll()"
                    class="h-8 px-3 rounded-[8px] text-xs font-semibold text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition">
                    <span x-text="selectedItems.length === unsettledItems.length && unsettledItems.length > 0 ? 'Batalkan Semua' : 'Pilih Semua'"></span>
                </button>
            </div>
        </div>

        {{-- Table of Unsettled Orders --}}
        <div class="overflow-x-auto max-h-[380px] overflow-y-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="sticky top-0 bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-sm border-b border-black/5 dark:border-white/10 text-black/60 dark:text-white/60 font-semibold z-10">
                    <tr>
                        <th class="py-2.5 px-4 w-10 text-center">
                            <input type="checkbox" @change="toggleSelectAll()" :checked="selectedItems.length === unsettledItems.length && unsettledItems.length > 0"
                                class="rounded border-black/20 text-[#007AFF] focus:ring-0 focus:ring-offset-0">
                        </th>
                        <th class="py-2.5 px-4">No. Order / Ref</th>
                        <th class="py-2.5 px-4">Kanal / Sumber</th>
                        <th class="py-2.5 px-4">Pelanggan</th>
                        <th class="py-2.5 px-4">Tanggal Pembayaran</th>
                        <th class="py-2.5 px-4 text-right">Nominal Bruto</th>
                        <th class="py-2.5 px-4 text-right">Fee MDR</th>
                        <th class="py-2.5 px-4 text-right">Nominal Bersih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5 text-black/80 dark:text-white/80">
                    <template x-for="item in unsettledItems" :key="item.payment_type + '-' + item.payment_id">
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer"
                            @click="toggleItem(item)">
                            <td class="py-3 px-4 text-center" @click.stop>
                                <input type="checkbox" :value="item.payment_type + '-' + item.payment_id"
                                    :checked="isSelected(item)"
                                    @change="toggleItem(item)"
                                    class="rounded border-black/20 text-[#007AFF] focus:ring-0 focus:ring-offset-0">
                            </td>
                            <td class="py-3 px-4 font-mono font-medium">
                                <span class="text-black dark:text-white" x-text="'#' + item.order_number"></span>
                                <div class="text-[10px] text-black/45 dark:text-white/45 font-sans" x-text="item.reference"></div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-md font-semibold text-[10px]"
                                    :class="item.payment_type === 'commerce_order' ? 'bg-[#AF52DE]/10 text-[#AF52DE]' : 'bg-[#007AFF]/10 text-[#007AFF]'"
                                    x-text="item.source + ' (' + item.channel + ')'"></span>
                            </td>
                            <td class="py-3 px-4 font-medium" x-text="item.customer"></td>
                            <td class="py-3 px-4 text-black/60 dark:text-white/60" x-text="item.date"></td>
                            <td class="py-3 px-4 text-right font-medium tabular-nums" x-text="formatRupiah(item.gross_amount)"></td>
                            <td class="py-3 px-4 text-right text-[#FF3B30] tabular-nums" x-text="formatRupiah(item.fee_amount)"></td>
                            <td class="py-3 px-4 text-right font-bold text-[#34C759] dark:text-[#30D158] tabular-nums" x-text="formatRupiah(item.net_amount)"></td>
                        </tr>
                    </template>
                    <tr x-show="unsettledItems.length === 0">
                        <td colspan="8" class="py-12 text-center text-black/40 dark:text-white/40">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <div class="w-10 h-10 rounded-full bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                                </div>
                                <span class="text-[13px] font-medium text-black/70 dark:text-white/70">Seluruh pembayaran Cooca Pay telah dicairkan</span>
                                <span class="text-[11px] text-black/40 dark:text-white/40">Tidak ada dana mengendap yang tertunda</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Payout Action Bar --}}
        <div x-show="unsettledItems.length > 0" class="p-5 sm:p-6 border-t border-black/5 dark:border-white/10 bg-black/[0.015] dark:bg-white/[0.02]">
            <form @submit.prevent="submitPayout()" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    {{-- Rekening Penarikan Terverifikasi --}}
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1.5">Rekening Penarikan Terverifikasi *</label>
                        <select x-model="payoutBankAccountId" required
                            class="w-full h-11 sm:h-10 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 px-3 text-[16px] sm:text-xs text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="">-- Pilih Rekening Terverifikasi --</option>
                            @foreach($payoutBankAccounts as $pba)
                                <option value="{{ $pba->id }}" {{ $pba->is_primary ? 'selected' : '' }}>
                                    {{ $pba->bank_name }} - {{ $pba->account_number }} (a.n {{ $pba->account_holder_name }}){{ $pba->is_primary ? ' ★ Utama' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-black/40 dark:text-white/40 mt-1">
                            Nama pemilik rekening wajib sama dengan: <strong class="text-black/80 dark:text-white/80">{{ $ownerName }}</strong>
                        </p>
                    </div>

                    {{-- Mode Pencairan --}}
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1.5">Mode Pencairan</label>
                        <select x-model="payoutMode"
                            class="w-full h-11 sm:h-10 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 px-3 text-[16px] sm:text-xs text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="manual">🔄 Penarikan Manual (Langsung Ajukan)</option>
                            <option value="auto_h1">⏰ Auto-Payout H+1 (Besok Pagi 09:00 WIB)</option>
                        </select>
                    </div>

                    {{-- Catatan --}}
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1.5">Catatan (Opsional)</label>
                        <input type="text" x-model="settlementNotes" placeholder="Contoh: Pencairan batch harian Cooca Pay"
                            class="w-full h-11 sm:h-10 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 px-3 text-[16px] sm:text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                </div>

                {{-- Auto-Payout H+1 Info Banner --}}
                <div x-show="payoutMode === 'auto_h1'" x-cloak
                    class="rounded-[12px] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 border border-[#007AFF]/20 p-3.5 flex items-start gap-3">
                    <i data-lucide="clock" class="w-5 h-5 text-[#007AFF] mt-0.5 shrink-0"></i>
                    <div class="text-xs text-black/70 dark:text-white/70">
                        <strong class="text-[#007AFF]">Mode Auto-Payout H+1:</strong>
                        Pencairan akan dijadwalkan otomatis untuk besok pagi pukul <strong>09:00 WIB</strong>.
                        Admin COOCA akan memproses transfer dan mengirimkan bukti bayar serta notifikasi WhatsApp setelah dana berhasil dikirim.
                    </div>
                </div>

                {{-- Summary & Submit --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-3 border-t border-black/5 dark:border-white/10">
                    <div class="flex flex-wrap items-center gap-4 text-xs">
                        <div>
                            <span class="text-black/50 dark:text-white/50">Dipilih:</span>
                            <span class="font-bold text-black dark:text-white" x-text="selectedItems.length + ' transaksi'"></span>
                        </div>
                        <div>
                            <span class="text-black/50 dark:text-white/50">Total Bruto:</span>
                            <span class="font-bold tabular-nums text-black dark:text-white" x-text="formatRupiah(calculateGross())"></span>
                        </div>
                        <div>
                            <span class="text-black/50 dark:text-white/50">Total Fee:</span>
                            <span class="font-bold tabular-nums text-[#FF3B30]" x-text="formatRupiah(calculateFee())"></span>
                        </div>
                        <div>
                            <span class="text-black/50 dark:text-white/50">Dana Masuk Bank:</span>
                            <span class="font-extrabold tabular-nums text-[#34C759] dark:text-[#30D158] text-sm" x-text="formatRupiah(calculateNet())"></span>
                        </div>
                    </div>

                    <button type="submit"
                        :disabled="selectedItems.length === 0 || !payoutBankAccountId || isProcessing"
                        class="min-h-[44px] h-11 sm:h-10 px-6 rounded-[10px] bg-[#34C759] hover:bg-[#28A745] active:scale-[0.98] text-white text-xs font-bold transition shadow-sm disabled:opacity-40 flex items-center justify-center gap-2 shrink-0">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        <span x-text="isProcessing ? 'Mengirim Pengajuan...' : (payoutMode === 'auto_h1' ? 'Jadwalkan Auto-Payout H+1' : 'Ajukan Pencairan ke COOCA')"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- SECTION: RIWAYAT SETTLEMENT & PENCAIRAN SELESAI            --}}
    {{-- ========================================================== --}}
    <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-sm">
        <div class="px-5 sm:px-6 py-4 border-b border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.01]">
            <h2 class="text-[16px] font-bold text-black dark:text-white">Riwayat Payout &amp; Pencairan Dana</h2>
            <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Daftar pengajuan pencairan dan bukti transfer dari Finance COOCA</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-black/[0.02] dark:bg-white/[0.03] border-b border-black/5 dark:border-white/10 text-black/60 dark:text-white/60 font-semibold">
                    <tr>
                        <th class="py-3 px-5">No. Settlement</th>
                        <th class="py-3 px-5">Tanggal</th>
                        <th class="py-3 px-5">Rekening Tujuan</th>
                        <th class="py-3 px-5">Mode</th>
                        <th class="py-3 px-5 text-right">Bruto</th>
                        <th class="py-3 px-5 text-right">Fee MDR</th>
                        <th class="py-3 px-5 text-right">Bersih Masuk Bank</th>
                        <th class="py-3 px-5 text-center">Status</th>
                        <th class="py-3 px-5 text-right">Bukti &amp; Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5 text-black/80 dark:text-white/80">
                    @forelse($settlements as $settlement)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition">
                            <td class="py-3.5 px-5 font-mono font-bold text-black dark:text-white">
                                {{ $settlement->settlement_number }}
                            </td>
                            <td class="py-3.5 px-5 text-black/60 dark:text-white/60">
                                {{ \Carbon\Carbon::parse($settlement->settlement_date)->format('d M Y') }}
                            </td>
                            <td class="py-3.5 px-5 font-medium">
                                {{ $settlement->destination_bank ?? 'Rekening Kas Utama' }}
                            </td>
                            <td class="py-3.5 px-5">
                                @if($settlement->payout_mode === 'auto_h1')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#007AFF]/10 text-[#007AFF]">Auto H+1</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">Manual</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-right font-medium tabular-nums">
                                {{ $business->currency_symbol }} {{ number_format($settlement->gross_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-5 text-right text-[#FF3B30] tabular-nums">
                                {{ $business->currency_symbol }} {{ number_format($settlement->fee_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-5 text-right font-extrabold text-[#34C759] dark:text-[#30D158] tabular-nums">
                                {{ $business->currency_symbol }} {{ number_format($settlement->net_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-5 text-center">
                                @if($settlement->status === 'completed')
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                        Ditransfer ✓
                                    </span>
                                @elseif($settlement->status === 'pending')
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#D97706] dark:text-[#FBBF24]">
                                        Menunggu Transfer
                                    </span>
                                @elseif($settlement->status === 'rejected')
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/15 text-[#FF3B30]">
                                        Ditolak
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-black/10 text-black/70">
                                        {{ ucfirst($settlement->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($settlement->proof_image_path)
                                        <button type="button"
                                            @click="openProofModal('{{ $settlement->proof_image_url }}', '{{ $settlement->settlement_number }}', '{{ number_format($settlement->net_amount, 0, ',', '.') }}', '{{ $settlement->destination_bank }}', '{{ $settlement->transferred_at ? $settlement->transferred_at->format('d M Y H:i') : '-' }}', '{{ addslashes($settlement->admin_notes ?? '') }}')"
                                            class="h-8 px-2.5 rounded-[8px] text-[11px] font-semibold text-[#34C759] bg-[#34C759]/10 hover:bg-[#34C759]/20 transition flex items-center gap-1 shadow-xs">
                                            <i data-lucide="image" class="w-3.5 h-3.5"></i>
                                            <span>Bukti Bayar</span>
                                        </button>
                                    @elseif($settlement->status === 'pending')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-medium text-[#FF9500] bg-[#FF9500]/10">
                                            Menunggu Bukti
                                        </span>
                                    @endif

                                    <a href="{{ route('finance.settlements.show', $settlement) }}"
                                        class="h-8 px-2.5 rounded-[8px] text-[11px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition inline-flex items-center gap-1">
                                        <span>Detail</span>
                                        <i data-lucide="chevron-right" class="w-3 h-3"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-black/40 dark:text-white/40 text-xs">
                                Belum ada riwayat pengajuan pencairan Cooca Pay.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($settlements->hasPages())
            <div class="p-4 border-t border-black/5 dark:border-white/10">
                {{ $settlements->links() }}
            </div>
        @endif
    </div>

    {{-- ========================================================== --}}
    {{-- MODAL PREVIEW BUKTI TRANSFER (APPLE HIG)                  --}}
    {{-- ========================================================== --}}
    <div x-show="showProofModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-md"
        @keydown.escape.window="showProofModal = false">
        <div class="w-full max-w-lg rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden"
            @click.outside="showProofModal = false">

            <div class="px-6 py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                <div>
                    <h3 class="text-[15px] font-bold text-black dark:text-white">Bukti Transfer Pembayaran</h3>
                    <p class="text-[11.5px] text-black/50 dark:text-white/50" x-text="'Settlement #' + proofSettlementNumber"></p>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="proofImageUrl" target="_blank" download title="Unduh Bukti"
                        class="p-2 rounded-lg hover:bg-black/5 dark:hover:bg-white/10 text-black/60 dark:text-white/60">
                        <i data-lucide="download" class="w-4 h-4"></i>
                    </a>
                    <button type="button" @click="showProofModal = false" class="p-2 rounded-lg hover:bg-black/5 dark:hover:bg-white/10 text-black/40 dark:text-white/40">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <div class="p-6 space-y-4">
                <div class="rounded-[18px] bg-black/[0.03] dark:bg-black/40 border border-black/[0.05] dark:border-white/5 p-2 flex items-center justify-center overflow-hidden">
                    <img :src="proofImageUrl" alt="Bukti Transfer Bank" class="max-h-[360px] w-auto rounded-[12px] object-contain shadow-xs">
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                    <div>
                        <span class="text-black/45 dark:text-white/45 block text-[11px]">Nominal Cair Masuk Bank:</span>
                        <span class="font-extrabold text-[#34C759] text-[13px] tabular-nums" x-text="'Rp ' + proofNetAmount"></span>
                    </div>
                    <div>
                        <span class="text-black/45 dark:text-white/45 block text-[11px]">Rekening Tujuan:</span>
                        <span class="font-semibold text-black dark:text-white" x-text="proofDestinationBank"></span>
                    </div>
                    <div>
                        <span class="text-black/45 dark:text-white/45 block text-[11px]">Waktu Transfer Admin:</span>
                        <span class="font-medium text-black dark:text-white" x-text="proofTransferredAt"></span>
                    </div>
                    <div x-show="proofAdminNotes">
                        <span class="text-black/45 dark:text-white/45 block text-[11px]">Catatan Admin COOCA:</span>
                        <span class="font-medium text-black dark:text-white" x-text="proofAdminNotes"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ========================================================== --}}
{{-- ALPINE.JS COOCA PAY PAYOUT HUB ENGINE                     --}}
{{-- ========================================================== --}}
<script>
function payoutHubApp() {
    return {
        csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        unsettledItems: @json($unsettledData['items'] ?? []),
        selectedItems: [],
        payoutBankAccountId: '{{ $payoutBankAccounts->firstWhere("is_primary", true)?->id ?? $payoutBankAccounts->first()?->id ?? "" }}',
        payoutMode: 'manual',
        settlementNotes: '',
        isProcessing: false,

        showProofModal: false,
        proofImageUrl: '',
        proofSettlementNumber: '',
        proofNetAmount: '',
        proofDestinationBank: '',
        proofTransferredAt: '',
        proofAdminNotes: '',

        init() {
            this.selectedItems = this.unsettledItems.map(i => i.payment_type + '-' + i.payment_id);
        },

        openProofModal(url, number, net, bank, time, notes) {
            this.proofImageUrl = url;
            this.proofSettlementNumber = number;
            this.proofNetAmount = net;
            this.proofDestinationBank = bank;
            this.proofTransferredAt = time;
            this.proofAdminNotes = notes;
            this.showProofModal = true;
        },

        isSelected(item) {
            const key = item.payment_type + '-' + item.payment_id;
            return this.selectedItems.includes(key);
        },

        toggleItem(item) {
            const key = item.payment_type + '-' + item.payment_id;
            const idx = this.selectedItems.indexOf(key);
            if (idx > -1) {
                this.selectedItems.splice(idx, 1);
            } else {
                this.selectedItems.push(key);
            }
        },

        toggleSelectAll() {
            if (this.selectedItems.length === this.unsettledItems.length) {
                this.selectedItems = [];
            } else {
                this.selectedItems = this.unsettledItems.map(i => i.payment_type + '-' + i.payment_id);
            }
        },

        getSelectedObjects() {
            return this.unsettledItems.filter(i => this.selectedItems.includes(i.payment_type + '-' + i.payment_id));
        },

        calculateGross() {
            return this.getSelectedObjects().reduce((acc, i) => acc + Number(i.gross_amount || 0), 0);
        },

        calculateFee() {
            return this.getSelectedObjects().reduce((acc, i) => acc + Number(i.fee_amount || 0), 0);
        },

        calculateNet() {
            return Math.max(0, this.calculateGross() - this.calculateFee());
        },

        formatRupiah(num) {
            return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
        },

        async submitPayout() {
            if (this.selectedItems.length === 0) {
                alert('Pilih minimal satu transaksi untuk dicairkan.');
                return;
            }
            if (!this.payoutBankAccountId) {
                alert('Pilih rekening penarikan terverifikasi.');
                return;
            }

            const allocations = this.getSelectedObjects().map(i => ({
                payment_type: i.payment_type,
                payment_id: i.payment_id,
                amount: i.gross_amount
            }));

            this.isProcessing = true;
            try {
                const res = await fetch("{{ route('finance.settlements.reconcile') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: JSON.stringify({
                        payout_bank_account_id: this.payoutBankAccountId,
                        payout_mode: this.payoutMode,
                        notes: this.settlementNotes,
                        allocations: allocations,
                        fee_amount: this.calculateFee()
                    })
                });

                const data = await res.json();
                if (data.success) {
                    this.showPayoutModal = false;
                    const selectedKeys = [...this.selectedItems];
                    this.unsettledItems = this.unsettledItems.filter(i => !selectedKeys.includes(i.payment_type + '-' + i.payment_id));
                    this.selectedItems = [];
                    if (window.AppAlert) {
                        AppAlert.success(data.message || 'Pencairan saldo gateway berhasil diproses.');
                    } else {
                        alert(data.message || 'Pencairan saldo gateway berhasil diproses.');
                    }
                } else {
                    if (window.AppAlert) {
                        AppAlert.error(data.message || 'Gagal memproses pengajuan pencairan.');
                    } else {
                        alert(data.message || 'Gagal memproses pengajuan pencairan.');
                    }
                }
            } catch (e) {
                if (window.AppAlert) {
                    AppAlert.error('Terjadi kesalahan: ' + e.message);
                } else {
                    alert('Terjadi kesalahan: ' + e.message);
                }
            } finally {
                this.isProcessing = false;
            }
        }
    };
}
</script>
@endsection
