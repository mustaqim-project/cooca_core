@extends('layouts.app', ['title' => 'Rekonsiliasi & Settlement Gateway'])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-16" x-data="settlementApp()">

    {{-- ========================================================== --}}
    {{-- TOOLBAR / PAGE HEADER                                      --}}
    {{-- ========================================================== --}}
    <header class="rounded-[16px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-5 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <span>Keuangan &amp; Kas</span>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Rekonsiliasi Gateway</span>
            </nav>
            <h1 class="text-[22px] font-bold text-black dark:text-white tracking-tight">Rekonsiliasi &amp; Settlement Gateway</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Kelola dan rekonsiliasi pencairan dana non-tunai TriPay (QRIS &amp; Online) ke rekening bank operasional</p>
        </div>
        <div class="flex items-center gap-2.5 w-full sm:w-auto">
            <a href="{{ route('finance.cash-bank.ledger') }}"
                class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                </svg>
                <span>Buku Kas &amp; Ledger</span>
            </a>
            <a href="{{ route('finance.journals.index') }}"
                class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                </svg>
                <span>Jurnal Otomatis</span>
            </a>
        </div>
    </header>

    {{-- ========================================================== --}}
    {{-- BENTO KPI CARDS: GATEWAY CLEARING STATS                   --}}
    {{-- ========================================================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Unsettled Gross --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-4.5 space-y-2 shadow-sm">
            <div class="flex items-center justify-between text-xs text-black/50 dark:text-white/50">
                <span class="font-medium">Dana Gateway Mengendap</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500]/10 text-[#D97706] dark:text-[#FBBF24]">Clearing (1-1005)</span>
            </div>
            <div class="text-[22px] font-extrabold text-black dark:text-white tabular-nums tracking-tight">
                {{ $business->currency_symbol }} {{ number_format($unsettledData['summary']['total_gross'] ?? 0, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-black/45 dark:text-white/45 flex items-center justify-between">
                <span>{{ $unsettledData['summary']['count'] ?? 0 }} transaksi siap dicairkan</span>
                <span class="font-mono text-[10px]">TriPay Escrow</span>
            </div>
        </div>

        {{-- Card 2: Estimated Gateway Fee --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-4.5 space-y-2 shadow-sm">
            <div class="flex items-center justify-between text-xs text-black/50 dark:text-white/50">
                <span class="font-medium">Total Fee MDR Gateway</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF3B30]/10 text-[#FF3B30]">Beban (6-6003)</span>
            </div>
            <div class="text-[22px] font-extrabold text-[#FF3B30] tabular-nums tracking-tight">
                {{ $business->currency_symbol }} {{ number_format($unsettledData['summary']['total_fee'] ?? 0, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-black/45 dark:text-white/45">
                Rp 750 + 0,7% per transaksi QRIS
            </div>
        </div>

        {{-- Card 3: Net Payout Estimate --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-4.5 space-y-2 shadow-sm">
            <div class="flex items-center justify-between text-xs text-black/50 dark:text-white/50">
                <span class="font-medium">Estimasi Bersih Masuk Bank</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]">Bank (1-1002)</span>
            </div>
            <div class="text-[22px] font-extrabold text-[#34C759] dark:text-[#30D158] tabular-nums tracking-tight">
                {{ $business->currency_symbol }} {{ number_format($unsettledData['summary']['total_net'] ?? 0, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-black/45 dark:text-white/45">
                Dana bersih setelah potongan MDR
            </div>
        </div>

        {{-- Card 4: Historical Completed Payouts --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-4.5 space-y-2 shadow-sm">
            <div class="flex items-center justify-between text-xs text-black/50 dark:text-white/50">
                <span class="font-medium">Total Settlement Selesai</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#007AFF]/10 text-[#007AFF]">Terekonsiliasi</span>
            </div>
            <div class="text-[22px] font-extrabold text-[#007AFF] tabular-nums tracking-tight">
                {{ $business->currency_symbol }} {{ number_format($settlements->sum('net_amount'), 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-black/45 dark:text-white/45">
                {{ $settlements->total() }} riwayat pencairan tercatat
            </div>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- SECTION: DAFTAR TRANSAKSI GATEWAY SIAP PENCAIRAN (ACTION) --}}
    {{-- ========================================================== --}}
    <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-sm">
        <div class="px-5 sm:px-6 py-4 border-b border-black/5 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-black/[0.01] dark:bg-white/[0.01]">
            <div>
                <h2 class="text-[16px] font-bold text-black dark:text-white">Pencairan Dana Gateway Baru</h2>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Pilih transaksi pesanan meja QRIS atau toko online yang telah lunas via TriPay untuk direkonsiliasi ke rekening bank toko</p>
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
                                <svg class="w-8 h-8 opacity-40 text-[#34C759]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Seluruh pembayaran gateway telah selesai direkonsiliasi. Tidak ada dana mengendap.</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Settlement Action Bar --}}
        <div x-show="unsettledItems.length > 0" class="p-5 sm:p-6 border-t border-black/5 dark:border-white/10 bg-black/[0.015] dark:bg-white/[0.02]">
            <form @submit.prevent="submitReconciliation()" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1.5">Rekening Bank Tujuan Pencairan *</label>
                        <select x-model="destinationBank" required
                            class="w-full h-10 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 px-3 text-xs text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="">-- Pilih Rekening Bank Operasional --</option>
                            @foreach($bankAccounts as $bank)
                                <option value="{{ $bank->name }} ({{ $bank->account_number ?? '-' }})">
                                    {{ $bank->name }} - Saldo: {{ $business->currency_symbol }} {{ number_format($bank->current_balance, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1.5">Tanggal Pencairan / Settlement</label>
                        <input type="date" x-model="settlementDate"
                            class="w-full h-10 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 px-3 text-xs text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1.5">Catatan Rekonsiliasi (Opsional)</label>
                        <input type="text" x-model="settlementNotes" placeholder="Contoh: Pencairan batch mingguan TriPay"
                            class="w-full h-10 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 px-3 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                </div>

                {{-- Summary & Submit Button --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-3 border-t border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-4 text-xs">
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
                        :disabled="selectedItems.length === 0 || !destinationBank || isProcessing"
                        class="h-10 px-6 rounded-[10px] bg-[#34C759] hover:bg-[#28A745] active:scale-[0.98] text-white text-xs font-bold transition shadow-sm disabled:opacity-40 flex items-center justify-center gap-2 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        <span x-text="isProcessing ? 'Memproses Jurnal...' : 'Cairkan &amp; Rekonsiliasi ke Bank'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- SECTION: RIWAYAT SETTLEMENT & PENCAIRAN YANG TELAH SELESAI --}}
    {{-- ========================================================== --}}
    <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-sm">
        <div class="px-5 sm:px-6 py-4 border-b border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.01]">
            <h2 class="text-[16px] font-bold text-black dark:text-white">Riwayat Rekonsiliasi &amp; Payout Gateway</h2>
            <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Daftar settlement yang telah selesai dijurnal ke buku kas dan akun bank operasional</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-black/[0.02] dark:bg-white/[0.03] border-b border-black/5 dark:border-white/10 text-black/60 dark:text-white/60 font-semibold">
                    <tr>
                        <th class="py-3 px-5">No. Settlement</th>
                        <th class="py-3 px-5">Tanggal</th>
                        <th class="py-3 px-5">Rekening Bank Tujuan</th>
                        <th class="py-3 px-5 text-right">Nominal Bruto</th>
                        <th class="py-3 px-5 text-right">Potongan MDR</th>
                        <th class="py-3 px-5 text-right">Bersih Masuk Bank</th>
                        <th class="py-3 px-5 text-center">Jumlah Order</th>
                        <th class="py-3 px-5 text-center">Status</th>
                        <th class="py-3 px-5 text-right">Aksi</th>
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
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-black/[0.05] dark:bg-white/[0.08]">
                                    {{ $settlement->allocations->count() }} transaksi
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                    Terekonsiliasi
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <a href="{{ route('finance.settlements.show', $settlement) }}"
                                    class="h-8 px-3 rounded-[8px] text-[11px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition inline-flex items-center gap-1">
                                    <span>Detail Alokasi</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-black/40 dark:text-white/40 text-xs">
                                Belum ada riwayat rekonsiliasi settlement yang tersimpan.
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

</div>

{{-- ========================================================== --}}
{{-- ALPINE.JS SETTLEMENT CLIENT ENGINE                         --}}
{{-- ========================================================== --}}
<script>
function settlementApp() {
    return {
        csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        unsettledItems: @json($unsettledData['items'] ?? []),
        selectedItems: [],
        destinationBank: '',
        settlementDate: '{{ \Carbon\Carbon::today()->toDateString() }}',
        settlementNotes: '',
        isProcessing: false,

        init() {
            // Auto-select all by default for convenient batching
            this.selectedItems = this.unsettledItems.map(i => i.payment_type + '-' + i.payment_id);
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

        async submitReconciliation() {
            if (this.selectedItems.length === 0) {
                alert('Pilih minimal satu transaksi untuk dicairkan.');
                return;
            }
            if (!this.destinationBank) {
                alert('Pilih rekening bank tujuan pencairan.');
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
                        destination_bank: this.destinationBank,
                        settlement_date: this.settlementDate,
                        notes: this.settlementNotes,
                        allocations: allocations,
                        fee_amount: this.calculateFee()
                    })
                });

                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Gagal memproses settlement.');
                }
            } catch (e) {
                alert('Terjadi kesalahan memproses settlement: ' + e.message);
            } finally {
                this.isProcessing = false;
            }
        }
    };
}
</script>
@endsection
