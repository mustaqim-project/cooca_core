@extends('layouts.app', ['title' => 'Kas & Rekening Bank'])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-5 pb-28 lg:pb-12" x-data="{
        showTransferModal: false,
        showInflowModal: false,
        showOutflowModal: false,
        showAccountModal: false,
        editAccountModal: false,
        currentAccount: { id: '', name: '', type: 'bank', is_active: true }
    }">

        {{-- ========================================================== --}}
        {{-- TOOLBAR / PAGE HEADER                                      --}}
        {{-- ========================================================== --}}
        <x-module-header
            title="Kas & Rekening Bank"
            subtitle="Kelola multi-rekening bank operasional, buku kasir toko, dan mutasi arus dana">
            @if (\App\Support\Context::hasPermission('finance.cash_bank'))
                <button type="button" @click="showAccountModal = true"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>+ Rekening Baru</span>
                </button>
                <button type="button" @click="showInflowModal = true"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-semibold text-white bg-[#34C759] hover:bg-[#2DBE50] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(52,199,89,0.25)]">
                    <i data-lucide="arrow-down-left" class="w-4 h-4"></i>
                    <span>Kas Masuk</span>
                </button>
                <button type="button" @click="showOutflowModal = true"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-semibold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(255,59,48,0.25)]">
                    <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
                    <span>Kas Keluar</span>
                </button>
                <button type="button" @click="showTransferModal = true"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                    <i data-lucide="arrow-left-right" class="w-4 h-4"></i>
                    <span>Transfer</span>
                </button>
            @endif
            @if (\App\Support\Context::hasPermission('accounting.view') || \App\Support\Context::hasPermission('finance.cash_bank'))
                <a href="{{ route('finance.cash-bank.ledger') }}"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                    <i data-lucide="book-open" class="w-4 h-4 text-[#FF9500]"></i>
                    <span>Buku Kas</span>
                </a>
            @endif
        </x-module-header>

        {{-- ========================================================== --}}
        {{-- MODULE TABS (SSOT)                                         --}}
        {{-- ========================================================== --}}
        <x-module-tabs module="finance" />

        {{-- Flash Message --}}
        @if (session('success'))
            <div
                class="rounded-[12px] bg-[#34C759]/10 border border-[#34C759]/20 px-4 py-3 flex items-center gap-2.5 text-[13px] font-medium text-[#248A3D] dark:text-[#30D158]">
                <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error') || $errors->any())
            <div
                class="rounded-[12px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 px-4 py-3 flex items-start gap-2.5 text-[13px] font-medium text-[#C41E17] dark:text-[#FF453A]">
                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 mt-0.5"></i>
                <div class="space-y-0.5">
                    @if (session('error'))
                        <div>{{ session('error') }}</div>
                    @endif
                    @foreach ($errors->all() as $err)
                        <div>{{ $err }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ========================================================== --}}
        {{-- ACCOUNT CARDS (MULTI-BANK CORPORATE / UMKM)               --}}
        {{-- ========================================================== --}}
        <div>
            <div class="flex items-center justify-between mb-3 px-1">
                <h2 class="text-[14px] font-semibold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="wallet-cards" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Daftar Rekening Bank &amp; Kas Terkelola ({{ $accounts->count() }})</span>
                </h2>
                <button type="button" @click="showAccountModal = true"
                    class="text-[12px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Rekening
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @forelse($accounts as $account)
                    @php
                        $iconName = match ($account->type) {
                            'bank' => 'landmark',
                            'qris', 'ewallet' => 'qr-code',
                            'petty_cash' => 'coins',
                            default => 'banknote',
                        };
                        $badgeColor = match ($account->type) {
                            'bank' => 'text-[#007AFF] bg-[#007AFF]/10 border-[#007AFF]/20',
                            'qris', 'ewallet' => 'text-[#BF5AF2] bg-[#BF5AF2]/10 border-[#BF5AF2]/20',
                            'petty_cash' => 'text-[#FF9500] bg-[#FF9500]/10 border-[#FF9500]/20',
                            default => 'text-[#34C759] bg-[#34C759]/10 border-[#34C759]/20',
                        };
                        $typeLabel = match ($account->type) {
                            'bank' => 'Rekening Bank',
                            'ewallet', 'qris' => 'E-Wallet / QRIS',
                            'petty_cash' => 'Kas Kecil (Petty)',
                            default => 'Kas Tunai Toko',
                        };
                    @endphp
                    <div
                        class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 hover:shadow-[0_2px_12px_rgba(0,0,0,0.06)] transition-all flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-[12px] flex items-center justify-center bg-black/[0.04] dark:bg-white/[0.06] text-black dark:text-white shrink-0">
                                        <i data-lucide="{{ $iconName }}" class="w-5 h-5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="text-[15px] font-semibold text-black dark:text-white leading-tight truncate">
                                            {{ $account->name }}
                                        </h3>
                                        <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-semibold uppercase tracking-wide border mt-1 {{ $badgeColor }}">
                                            {{ $typeLabel }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button type="button"
                                        @click="currentAccount = { id: '{{ $account->id }}', name: '{{ addslashes($account->name) }}', type: '{{ $account->type }}', is_active: {{ $account->is_active ? 'true' : 'false' }} }; editAccountModal = true"
                                        title="Ubah Nama/Tipe Rekening"
                                        class="p-1.5 rounded-[8px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-colors">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] flex items-baseline justify-between">
                            <div>
                                <p class="text-[11px] text-black/45 dark:text-white/45 font-medium uppercase tracking-wide">
                                    Saldo Berjalan
                                </p>
                                <p class="text-[20px] font-bold tabular-nums text-black dark:text-white mt-0.5">
                                    Rp {{ number_format($account->current_balance, 0, ',', '.') }}
                                </p>
                            </div>
                            <a href="{{ route('finance.cash-bank.ledger', ['account_id' => $account->id]) }}"
                                class="text-[13px] font-medium text-[#007AFF] hover:underline flex items-center gap-0.5">
                                Mutasi <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div
                        class="col-span-full rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 py-12 text-center">
                        <i data-lucide="wallet" class="w-8 h-8 text-black/30 dark:text-white/30 mx-auto mb-2"></i>
                        <p class="text-[15px] font-semibold text-black/60 dark:text-white/60">Belum ada akun kas atau rekening bank</p>
                        <p class="text-[13px] text-black/40 dark:text-white/40 mt-1">Klik "+ Rekening Baru" untuk menambahkan rekening bank atau kas operasional pertama</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ========================================================== --}}
        {{-- POS PAYMENT CHANNELS BREAKDOWN --}}
        {{-- ========================================================== --}}
        @php
            $cashAmount = (float) ($posSummaryByMethod['cash']->total_amount ?? 0);
            $cashCount = (int) ($posSummaryByMethod['cash']->total_count ?? 0);

            $qrisAmount = (float) ($posSummaryByMethod['qris']->total_amount ?? 0);
            $qrisCount = (int) ($posSummaryByMethod['qris']->total_count ?? 0);

            $trfAmount =
                (float) (($posSummaryByMethod['transfer']->total_amount ?? 0) +
                    ($posSummaryByMethod['bank_transfer']->total_amount ?? 0));
            $trfCount =
                (int) (($posSummaryByMethod['transfer']->total_count ?? 0) +
                    ($posSummaryByMethod['bank_transfer']->total_count ?? 0));

            $edcAmount =
                (float) (($posSummaryByMethod['edc_debit']->total_amount ?? 0) +
                    ($posSummaryByMethod['edc_credit']->total_amount ?? 0));
            $edcCount =
                (int) (($posSummaryByMethod['edc_debit']->total_count ?? 0) +
                    ($posSummaryByMethod['edc_credit']->total_count ?? 0));

            $creditAmount = (float) ($posSummaryByMethod['customer_credit']->total_amount ?? 0);
            $creditCount = (int) ($posSummaryByMethod['customer_credit']->total_count ?? 0);

            $totalPos = $totalPosRevenue ?? $cashAmount + $qrisAmount + $trfAmount + $edcAmount + $creditAmount;
            $curPeriod = $activePeriod ?? request('period', 'today');
        @endphp

        <div
            class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 space-y-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-[15px] font-semibold text-black dark:text-white">Pemasukan Kasir POS per Saluran Pembayaran</h2>
                        <span
                            class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                            Otomatis
                        </span>
                    </div>
                    <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Tracking penerimaan omset kasir berdasarkan saluran transaksi</p>
                </div>
                <div class="flex items-center gap-1 bg-black/[0.04] dark:bg-white/[0.06] p-1 rounded-[10px]">
                    <a href="{{ route('finance.cash-bank.index', ['period' => 'today']) }}"
                        class="px-3 py-1 rounded-[7px] text-[12px] font-medium transition-colors {{ $curPeriod === 'today' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        Hari Ini
                    </a>
                    <a href="{{ route('finance.cash-bank.index', ['period' => 'month']) }}"
                        class="px-3 py-1 rounded-[7px] text-[12px] font-medium transition-colors {{ $curPeriod === 'month' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        Bulan Ini
                    </a>
                    <a href="{{ route('finance.cash-bank.index', ['period' => 'all']) }}"
                        class="px-3 py-1 rounded-[7px] text-[12px] font-medium transition-colors {{ $curPeriod === 'all' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        Semua Waktu
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                {{-- 1. Cash --}}
                <a href="{{ route('finance.cash-bank.ledger', ['method' => 'cash']) }}"
                    class="rounded-[12px] p-3.5 bg-[#34C759]/[0.06] dark:bg-[#34C759]/[0.10] border border-[#34C759]/20 hover:border-[#34C759]/40 transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-[#248A3D] dark:text-[#30D158] uppercase tracking-wider">Tunai (Cash)</span>
                            <span class="w-6 h-6 rounded-full bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center text-[10px] font-bold">
                                <i data-lucide="banknote" class="w-3.5 h-3.5"></i>
                            </span>
                        </div>
                        <div class="text-[17px] font-bold tabular-nums text-black dark:text-white mt-2">
                            Rp {{ number_format($cashAmount, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="mt-2.5 pt-2 border-t border-[#34C759]/15 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50">
                        <span>{{ $cashCount }} Transaksi</span>
                        <span class="text-[#248A3D] dark:text-[#30D158] group-hover:translate-x-0.5 transition-transform font-medium flex items-center gap-0.5">
                            Buku Kas <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                        </span>
                    </div>
                </a>

                {{-- 2. QRIS --}}
                <a href="{{ route('finance.cash-bank.ledger', ['method' => 'qris']) }}"
                    class="rounded-[12px] p-3.5 bg-[#BF5AF2]/[0.06] dark:bg-[#BF5AF2]/[0.10] border border-[#BF5AF2]/20 hover:border-[#BF5AF2]/40 transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-[#8944AB] dark:text-[#BF5AF2] uppercase tracking-wider">QRIS / E-Wallet</span>
                            <span class="w-6 h-6 rounded-full bg-[#BF5AF2]/20 text-[#8944AB] dark:text-[#BF5AF2] flex items-center justify-center text-[10px] font-bold">
                                <i data-lucide="qr-code" class="w-3.5 h-3.5"></i>
                            </span>
                        </div>
                        <div class="text-[17px] font-bold tabular-nums text-black dark:text-white mt-2">
                            Rp {{ number_format($qrisAmount, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="mt-2.5 pt-2 border-t border-[#BF5AF2]/15 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50">
                        <span>{{ $qrisCount }} Transaksi</span>
                        <span class="text-[#8944AB] dark:text-[#BF5AF2] group-hover:translate-x-0.5 transition-transform font-medium flex items-center gap-0.5">
                            Ledger <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                        </span>
                    </div>
                </a>

                {{-- 3. Transfer Bank --}}
                <a href="{{ route('finance.cash-bank.ledger', ['method' => 'transfer']) }}"
                    class="rounded-[12px] p-3.5 bg-[#007AFF]/[0.06] dark:bg-[#007AFF]/[0.10] border border-[#007AFF]/20 hover:border-[#007AFF]/40 transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-[#007AFF] dark:text-[#0A84FF] uppercase tracking-wider">Transfer Bank</span>
                            <span class="w-6 h-6 rounded-full bg-[#007AFF]/20 text-[#007AFF] flex items-center justify-center text-[10px] font-bold">
                                <i data-lucide="landmark" class="w-3.5 h-3.5"></i>
                            </span>
                        </div>
                        <div class="text-[17px] font-bold tabular-nums text-black dark:text-white mt-2">
                            Rp {{ number_format($trfAmount, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="mt-2.5 pt-2 border-t border-[#007AFF]/15 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50">
                        <span>{{ $trfCount }} Transaksi</span>
                        <span class="text-[#007AFF] group-hover:translate-x-0.5 transition-transform font-medium flex items-center gap-0.5">
                            Ledger <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                        </span>
                    </div>
                </a>

                {{-- 4. EDC Card --}}
                <a href="{{ route('finance.cash-bank.ledger', ['method' => 'edc']) }}"
                    class="rounded-[12px] p-3.5 bg-[#FF9500]/[0.06] dark:bg-[#FF9500]/[0.10] border border-[#FF9500]/20 hover:border-[#FF9500]/40 transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-[#B25E00] dark:text-[#FF9F0A] uppercase tracking-wider">Mesin EDC</span>
                            <span class="w-6 h-6 rounded-full bg-[#FF9500]/20 text-[#B25E00] dark:text-[#FF9F0A] flex items-center justify-center text-[10px] font-bold">
                                <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
                            </span>
                        </div>
                        <div class="text-[17px] font-bold tabular-nums text-black dark:text-white mt-2">
                            Rp {{ number_format($edcAmount, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="mt-2.5 pt-2 border-t border-[#FF9500]/15 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50">
                        <span>{{ $edcCount }} Transaksi</span>
                        <span class="text-[#B25E00] dark:text-[#FF9F0A] group-hover:translate-x-0.5 transition-transform font-medium flex items-center gap-0.5">
                            Ledger <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                        </span>
                    </div>
                </a>

                {{-- 5. Customer Credit --}}
                <a href="{{ route('finance.receivables') }}"
                    class="rounded-[12px] p-3.5 bg-[#FF3B30]/[0.06] dark:bg-[#FF3B30]/[0.10] border border-[#FF3B30]/20 hover:border-[#FF3B30]/40 transition-all group flex flex-col justify-between col-span-2 sm:col-span-1">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-[#C41E17] dark:text-[#FF453A] uppercase tracking-wider">Kasbon (Piutang)</span>
                            <span class="w-6 h-6 rounded-full bg-[#FF3B30]/20 text-[#C41E17] dark:text-[#FF453A] flex items-center justify-center text-[10px] font-bold">
                                <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                            </span>
                        </div>
                        <div class="text-[17px] font-bold tabular-nums text-black dark:text-white mt-2">
                            Rp {{ number_format($creditAmount, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="mt-2.5 pt-2 border-t border-[#FF3B30]/15 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50">
                        <span>{{ $creditCount }} Piutang</span>
                        <span class="text-[#C41E17] dark:text-[#FF453A] group-hover:translate-x-0.5 transition-transform font-medium flex items-center gap-0.5">
                            Daftar <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                        </span>
                    </div>
                </a>
            </div>

            {{-- Revenue Summary Bar --}}
            <div
                class="pt-3 border-t border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="text-[12px] font-medium text-black/60 dark:text-white/60">Total Omset Kasir Terbayar:</span>
                    <span class="text-[16px] font-bold tabular-nums text-black dark:text-white">Rp {{ number_format($totalPos, 0, ',', '.') }}</span>
                </div>
                @if ($totalPos > 0)
                    <div class="flex items-center gap-3 text-[11px] text-black/50 dark:text-white/50">
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-[#34C759]"></span> Tunai: {{ round(($cashAmount / $totalPos) * 100) }}%</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-[#BF5AF2]"></span> QRIS: {{ round(($qrisAmount / $totalPos) * 100) }}%</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-[#007AFF]"></span> Transfer: {{ round(($trfAmount / $totalPos) * 100) }}%</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- ========================================================== --}}
        {{-- RECENT TRANSACTIONS TABLE                                  --}}
        {{-- ========================================================== --}}
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
            <div class="px-4 py-3 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <h2 class="text-[15px] font-semibold text-black dark:text-white">Mutasi Transaksi Kas Terbaru</h2>
                @if (\App\Support\Context::hasPermission('accounting.view') || \App\Support\Context::hasPermission('finance.cash_bank'))
                    <a href="{{ route('finance.cash-bank.ledger') }}"
                        class="text-[13px] font-medium text-[#007AFF] hover:underline flex items-center gap-0.5">
                        Lihat Seluruh Buku Kas <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </a>
                @endif
            </div>
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left text-[13px] min-w-[620px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10">
                            <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Tanggal</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Akun Rekening</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Arus</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Keterangan / Referensi</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Nominal</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Saldo Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($transactions as $transaction)
                            @php
                                $isOut = $transaction->isOutflow();
                                $isTrf = $transaction->isTransfer();
                            @endphp
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                <td class="px-4 py-3 tabular-nums text-black/60 dark:text-white/60">
                                    {{ $transaction->transaction_date->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3 font-medium text-black dark:text-white">
                                    {{ $transaction->cashAccount->name }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($isOut && $isTrf)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/10 text-[#C41E17] dark:text-[#FF453A]">
                                            Transfer Keluar
                                        </span>
                                    @elseif(!$isOut && $isTrf)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]">
                                            Transfer Masuk
                                        </span>
                                    @elseif($isOut)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/10 text-[#C41E17] dark:text-[#FF453A]">
                                            Keluar
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]">
                                            Masuk
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-black/70 dark:text-white/70">{{ $transaction->description }}</td>
                                <td class="px-4 py-3 text-right tabular-nums font-semibold {{ $isOut ? 'text-[#FF3B30] dark:text-[#FF453A]' : 'text-[#34C759] dark:text-[#30D158]' }}">
                                    {{ $isOut ? '−' : '+' }} Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums font-bold text-black dark:text-white">
                                    Rp {{ number_format($transaction->balance_after, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-[13px] text-black/40 dark:text-white/40">
                                    Belum ada riwayat mutasi kas yang tercatat
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- MOBILE GROUPED INSET LIST (Apple iOS HIG) --}}
            <div class="sm:hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                @forelse($transactions as $transaction)
                    @php
                        $isOut = $transaction->isOutflow();
                        $isTrf = $transaction->isTransfer();
                    @endphp
                    <div class="p-3.5 space-y-2 active:bg-black/[0.02] dark:active:bg-white/[0.03] transition-colors">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-[14px] text-black dark:text-white">{{ $transaction->cashAccount->name }}</span>
                                    @if ($isOut && $isTrf)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#FF3B30]/10 text-[#C41E17] dark:text-[#FF453A]">
                                            Transfer Keluar
                                        </span>
                                    @elseif(!$isOut && $isTrf)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]">
                                            Transfer Masuk
                                        </span>
                                    @elseif($isOut)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#FF3B30]/10 text-[#C41E17] dark:text-[#FF453A]">
                                            Keluar
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]">
                                            Masuk
                                        </span>
                                    @endif
                                </div>
                                <div class="text-[12px] text-black/45 dark:text-white/45 mt-0.5 tabular-nums">
                                    {{ $transaction->transaction_date->format('d M Y') }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="tabular-nums font-bold text-[14px] {{ $isOut ? 'text-[#FF3B30] dark:text-[#FF453A]' : 'text-[#34C759] dark:text-[#30D158]' }}">
                                    {{ $isOut ? '−' : '+' }} Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                </div>
                                <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums mt-0.5">
                                    Saldo: Rp {{ number_format($transaction->balance_after, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>

                        @if ($transaction->description)
                            <p class="text-[13px] text-black/75 dark:text-white/75 line-clamp-2">
                                {{ $transaction->description }}
                            </p>
                        @endif
                    </div>
                @empty
                    <div class="py-12 text-center text-[13px] text-black/40 dark:text-white/40">
                        Belum ada riwayat mutasi kas yang tercatat
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ========================================================== --}}
        {{-- MODALS                                                     --}}
        {{-- ========================================================== --}}
        @php
            $modalInputCls = 'w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition';
            $modalSelectCls = 'w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition';
            $modalLabelCls = 'block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5';
        @endphp

        @if (\App\Support\Context::hasPermission('finance.cash_bank'))
            {{-- ====================================================== --}}
            {{-- MODAL TAMBAH REKENING / AKUN KAS BARU (BENTO XXL)      --}}
            {{-- ====================================================== --}}
            <div x-show="showAccountModal" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-black/40 backdrop-blur-md"
                x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                @keydown.escape.window="showAccountModal = false">
                <div class="w-full max-w-full sm:max-w-2xl lg:max-w-3xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_25px_70px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[92vh]"
                    @click.away="showAccountModal = false"
                    x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                    x-data="{ isSubmitting: false, accountType: 'bank', openingBalance: '' }">
                    <div class="flex items-center justify-between px-6 sm:px-8 py-5 border-b border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02]">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-[14px] bg-[#007AFF]/12 text-[#007AFF] flex items-center justify-center shrink-0 shadow-sm">
                                <i data-lucide="plus" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white tracking-tight">Tambah Rekening / Akun Kas</h3>
                                <p class="text-[12.5px] text-black/50 dark:text-white/50">Daftarkan akun kas tunai kasir, rekening bank, atau saldo gateway QRIS</p>
                            </div>
                        </div>
                        <button type="button" @click="showAccountModal = false"
                            class="w-9 h-9 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 flex items-center justify-center transition-all cursor-pointer">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('finance.cash-bank.accounts.store') }}"
                        @submit="isSubmitting = true"
                        class="flex-1 overflow-y-auto p-5 sm:p-8 space-y-6">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            {{-- Kolom Kiri: Informasi Akun --}}
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[13px] font-bold text-black/80 dark:text-white/80 mb-1.5">Nama Rekening / Akun Kas *</label>
                                    <input name="name" required placeholder="Contoh: Rekening BCA Operasional / Kasir Utama"
                                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                </div>
                                <div>
                                    <label class="block text-[13px] font-bold text-black/80 dark:text-white/80 mb-1.5">Tipe Akun Keuangan *</label>
                                    <select name="type" x-model="accountType" required
                                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                        <option value="bank">{{ __('finance.account_type_bank') }}</option>
                                        <option value="cash">{{ __('finance.account_type_cash') }}</option>
                                        <option value="ewallet">{{ __('finance.account_type_ewallet') }}</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Kolom Kanan: Saldo Awal --}}
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[13px] font-bold text-black/80 dark:text-white/80 mb-1.5">{{ __('finance.opening_balance_label') }}</label>
                                    <div class="relative">
                                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 font-bold text-[14px]">Rp</span>
                                        <input name="opening_balance" type="number" min="0" step="1" x-model="openingBalance" placeholder="0"
                                            class="w-full h-11 pl-11 pr-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] text-[16px] sm:text-[15px] font-bold tabular-nums text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                    </div>
                                    <p class="text-[11.5px] text-black/45 dark:text-white/45 mt-1.5">{{ __('finance.opening_balance_hint') }}</p>
                                </div>

                                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-1 text-[12px] text-black/65 dark:text-white/65">
                                    <div class="flex items-center gap-1.5 font-bold text-black dark:text-white">
                                        <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759]"></i>
                                        <span>{{ __('finance.integrated_ledger_title') }}</span>
                                    </div>
                                    <p class="text-[11.5px] leading-relaxed">{{ __('finance.integrated_ledger_desc') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-5 border-t border-black/5 dark:border-white/10">
                            <button type="button" @click="showAccountModal = false" :disabled="isSubmitting"
                                class="w-full sm:w-auto min-h-[48px] px-6 rounded-[14px] text-[14px] font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 transition-all cursor-pointer">{{ __('common.cancel') }}</button>
                            <button type="submit" :disabled="isSubmitting"
                                class="w-full sm:w-auto min-h-[48px] px-8 rounded-[14px] text-[14.5px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all shadow-[0_4px_16px_rgba(0,122,255,0.3)] flex items-center justify-center gap-2 cursor-pointer">
                                <template x-if="isSubmitting">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                </template>
                                <i data-lucide="check" class="w-4 h-4" x-show="!isSubmitting"></i>
                                <span x-text="isSubmitting ? '{{ __('common.saving') }}' : '{{ __('finance.save_account') }}'">{{ __('finance.save_account') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ====================================================== --}}
            {{-- MODAL EDIT REKENING (BENTO XXL)                         --}}
            {{-- ====================================================== --}}
            <div x-show="editAccountModal" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-black/40 backdrop-blur-md"
                x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                @keydown.escape.window="editAccountModal = false">
                <div class="w-full max-w-full sm:max-w-2xl lg:max-w-3xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_25px_70px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[92vh]"
                    @click.away="editAccountModal = false"
                    x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                    x-data="{ isSubmitting: false }">
                    <div class="flex items-center justify-between px-6 sm:px-8 py-5 border-b border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02]">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-[14px] bg-[#FF9500]/12 text-[#FF9500] flex items-center justify-center shrink-0 shadow-sm">
                                <i data-lucide="pencil" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white tracking-tight">{{ __('finance.edit_account_title') }}</h3>
                                <p class="text-[12.5px] text-black/50 dark:text-white/50">{{ __('finance.edit_account_desc') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="editAccountModal = false"
                            class="w-9 h-9 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 flex items-center justify-center transition-all cursor-pointer">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <form method="POST" :action="'{{ url('finance/cash-bank/accounts') }}/' + currentAccount.id"
                        @submit="isSubmitting = true"
                        class="flex-1 overflow-y-auto p-5 sm:p-8 space-y-6">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-[13px] font-bold text-black/80 dark:text-white/80 mb-1.5">{{ __('finance.account_name_label') }}</label>
                                <input name="name" x-model="currentAccount.name" required
                                    class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                            <div>
                                <label class="block text-[13px] font-bold text-black/80 dark:text-white/80 mb-1.5">{{ __('finance.account_type_label') }}</label>
                                <select name="type" x-model="currentAccount.type" required
                                    class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                    <option value="bank">{{ __('finance.account_type_bank') }}</option>
                                    <option value="cash">{{ __('finance.account_type_cash') }}</option>
                                    <option value="ewallet">{{ __('finance.account_type_ewallet') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-5 border-t border-black/5 dark:border-white/10">
                            <button type="button" @click="editAccountModal = false" :disabled="isSubmitting"
                                class="w-full sm:w-auto min-h-[48px] px-6 rounded-[14px] text-[14px] font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 transition-all cursor-pointer">{{ __('common.cancel') }}</button>
                            <button type="submit" :disabled="isSubmitting"
                                class="w-full sm:w-auto min-h-[48px] px-8 rounded-[14px] text-[14.5px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all shadow-[0_4px_16px_rgba(0,122,255,0.3)] flex items-center justify-center gap-2 cursor-pointer">
                                <template x-if="isSubmitting">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                </template>
                                <i data-lucide="check" class="w-4 h-4" x-show="!isSubmitting"></i>
                                <span x-text="isSubmitting ? '{{ __('common.saving') }}' : '{{ __('finance.save_changes') }}'">{{ __('finance.save_changes') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ====================================================== --}}
            {{-- MODAL KAS MASUK (BENTO XXL)                             --}}
            {{-- ====================================================== --}}
            <div x-show="showInflowModal" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-black/40 backdrop-blur-md"
                x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                @keydown.escape.window="showInflowModal = false">
                <div class="w-full max-w-full sm:max-w-3xl lg:max-w-4xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_25px_70px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[92vh]"
                    @click.away="showInflowModal = false"
                    x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                    x-data="{ isSubmitting: false, amount: '', category: 'sales_revenue', otherDescription: '', setQuickAmount(val) { this.amount = val; } }">
                    <div class="flex items-center justify-between px-6 sm:px-8 py-5 border-b border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02]">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-[14px] bg-[#34C759]/15 text-[#34C759] flex items-center justify-center shrink-0 shadow-sm">
                                <i data-lucide="arrow-down-left" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white tracking-tight">{{ __('finance.record_inflow_title') }}</h3>
                                <p class="text-[12.5px] text-black/50 dark:text-white/50">{{ __('finance.record_inflow_desc') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="showInflowModal = false"
                            class="w-9 h-9 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 flex items-center justify-center transition-all cursor-pointer">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('finance.cash-bank.inflow') }}"
                        @submit="isSubmitting = true"
                        class="flex-1 overflow-y-auto p-5 sm:p-8 space-y-6">
                        @csrf
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                            {{-- Kolom Kiri: Detail Akun & Kategori (6/12) --}}
                            <div class="lg:col-span-6 space-y-4">
                                <div>
                                    <label class="block text-[13px] font-bold text-black/80 dark:text-white/80 mb-1.5">{{ __('finance.recipient_account') }}</label>
                                    <select name="account_id"
                                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]">
                                        <option value="">{{ __('finance.auto_by_method') }}</option>
                                        @foreach ($accounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->name }} ({{ strtoupper($acc->type) }}) - Saldo: Rp {{ number_format($acc->current_balance, 0, ',', '.') }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[13px] font-bold text-black/80 dark:text-white/80 mb-1.5">{{ __('finance.payment_method') }}</label>
                                    <select name="account_method"
                                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]">
                                        <option value="cash">{{ __('finance.method_cash') }}</option>
                                        <option value="bank_transfer">{{ __('finance.method_bank_transfer') }}</option>
                                        <option value="qris">{{ __('finance.method_qris') }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[13px] font-bold text-black/80 dark:text-white/80 mb-1.5">{{ __('finance.inflow_category_label') }}</label>
                                    <select name="category" x-model="category" required
                                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]">
                                        @foreach (['sales_revenue', 'capital_injection', 'receivable_payment', 'bank_interest', 'investment', 'asset_sale', 'cash_refund', 'tax_refund', 'other'] as $inflowCat)
                                            <option value="{{ $inflowCat }}">{{ __('finance.inflow_categories.' . $inflowCat) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div x-show="category === 'other'" x-transition class="p-3.5 rounded-[14px] bg-[#FF9500]/10 border border-[#FF9500]/30 space-y-1.5">
                                    <label class="block text-[12.5px] font-bold text-[#FF9500] dark:text-[#FF9F0A] flex items-center gap-1.5">
                                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                        <span>{{ __('finance.other_desc_label') }}</span>
                                    </label>
                                    <input name="other_description" x-model="otherDescription" :required="category === 'other'"
                                        placeholder="{{ __('finance.other_desc_inflow_placeholder') }}"
                                        class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-[#FF9500]/30 rounded-[10px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#FF9500]">
                                    <p class="text-[11px] text-[#FF9500]/80">{{ __('finance.other_desc_inflow_hint') }}</p>
                                </div>
                                <div>
                                    <label class="block text-[13px] font-bold text-black/80 dark:text-white/80 mb-1.5">{{ __('finance.notes_optional') }}</label>
                                    <input name="description" placeholder="{{ __('finance.notes_inflow_placeholder') }}"
                                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#34C759]">
                                </div>
                            </div>

                            {{-- Kolom Kanan: Nominal XXL (6/12) --}}
                            <div class="lg:col-span-6 space-y-4">
                                <div class="p-5 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3.5">
                                    <label class="block text-[13px] font-bold text-black/80 dark:text-white/80">{{ __('finance.amount_in_label') }}</label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 font-bold text-lg sm:text-xl">Rp</span>
                                        <input name="amount" type="number" min="1" step="any" x-model="amount" required placeholder="0"
                                            class="w-full h-14 pl-14 pr-4 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[14px] text-2xl sm:text-3xl font-extrabold tabular-nums text-black dark:text-white placeholder:text-black/20 dark:placeholder:text-white/20 focus:outline-none focus:ring-2 focus:ring-[#34C759] transition-all shadow-inner">
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2 pt-1">
                                        <span class="text-[11.5px] text-black/45 dark:text-white/45 font-medium">{{ __('finance.quick_amount') }}</span>
                                        <button type="button" @click="setQuickAmount(100000)" class="px-2.5 py-1 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[12px] font-semibold text-black/75 dark:text-white/75 transition cursor-pointer">Rp 100 rb</button>
                                        <button type="button" @click="setQuickAmount(500000)" class="px-2.5 py-1 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[12px] font-semibold text-black/75 dark:text-white/75 transition cursor-pointer">Rp 500 rb</button>
                                        <button type="button" @click="setQuickAmount(1000000)" class="px-2.5 py-1 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[12px] font-semibold text-black/75 dark:text-white/75 transition cursor-pointer">Rp 1 jt</button>
                                        <button type="button" @click="setQuickAmount(5000000)" class="px-2.5 py-1 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[12px] font-semibold text-black/75 dark:text-white/75 transition cursor-pointer">Rp 5 jt</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-5 border-t border-black/5 dark:border-white/10">
                            <button type="button" @click="showInflowModal = false" :disabled="isSubmitting"
                                class="w-full sm:w-auto min-h-[48px] px-6 rounded-[14px] text-[14px] font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 transition-all cursor-pointer">{{ __('common.cancel') }}</button>
                            <button type="submit" :disabled="isSubmitting"
                                class="w-full sm:w-auto min-h-[48px] px-8 rounded-[14px] text-[14.5px] font-bold text-white bg-[#34C759] hover:bg-[#2DBE50] active:scale-[0.98] transition-all shadow-[0_4px_16px_rgba(52,199,89,0.3)] flex items-center justify-center gap-2 cursor-pointer">
                                <template x-if="isSubmitting">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                </template>
                                <i data-lucide="check" class="w-4 h-4" x-show="!isSubmitting"></i>
                                <span x-text="isSubmitting ? '{{ __('common.saving') }}' : '{{ __('finance.save_inflow') }}'">{{ __('finance.save_inflow') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ====================================================== --}}
            {{-- MODAL KAS KELUAR (BENTO XXL)                            --}}
            {{-- ====================================================== --}}
            <div x-show="showOutflowModal" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-black/40 backdrop-blur-md"
                x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                @keydown.escape.window="showOutflowModal = false">
                <div class="w-full max-w-full sm:max-w-3xl lg:max-w-4xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_25px_70px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[92vh]"
                    @click.away="showOutflowModal = false"
                    x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                    x-data="{ isSubmitting: false, amount: '', category: 'operational', otherDescription: '', setQuickAmount(val) { this.amount = val; } }">
                    <div class="flex items-center justify-between px-6 sm:px-8 py-5 border-b border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02]">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-[14px] bg-[#FF3B30]/12 text-[#FF3B30] flex items-center justify-center shrink-0 shadow-sm">
                                <i data-lucide="arrow-up-right" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white tracking-tight">{{ __('finance.record_outflow_title') }}</h3>
                                <p class="text-[12.5px] text-black/50 dark:text-white/50">{{ __('finance.record_outflow_desc') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="showOutflowModal = false"
                            class="w-9 h-9 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 flex items-center justify-center transition-all cursor-pointer">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('finance.cash-bank.outflow') }}"
                        @submit="isSubmitting = true"
                        class="flex-1 overflow-y-auto p-5 sm:p-8 space-y-6">
                        @csrf
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                            {{-- Kolom Kiri: Detail Akun & Kategori (6/12) --}}
                            <div class="lg:col-span-6 space-y-4">
                                <div>
                                    <label class="block text-[13px] font-bold text-black/80 dark:text-white/80 mb-1.5">{{ __('finance.source_account') }}</label>
                                    <select name="account_id"
                                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF3B30]">
                                        <option value="">{{ __('finance.auto_by_method') }}</option>
                                        @foreach ($accounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->name }} ({{ strtoupper($acc->type) }}) - Saldo: Rp {{ number_format($acc->current_balance, 0, ',', '.') }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[13px] font-bold text-black/80 dark:text-white/80 mb-1.5">{{ __('finance.payment_method') }}</label>
                                    <select name="account_method"
                                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF3B30]">
                                        <option value="cash">{{ __('finance.method_cash') }}</option>
                                        <option value="bank_transfer">{{ __('finance.method_bank_transfer') }}</option>
                                        <option value="qris">{{ __('finance.method_qris') }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[13px] font-bold text-black/80 dark:text-white/80 mb-1.5">{{ __('finance.outflow_category_label') }}</label>
                                    <select name="category" x-model="category" required
                                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF3B30]">
                                        @foreach (['operational', 'utilities', 'internet_phone', 'supplies', 'salaries', 'consumption', 'logistics', 'rent', 'maintenance', 'marketing', 'taxes_legal', 'bank_admin', 'cash_advance', 'other'] as $outflowCat)
                                            <option value="{{ $outflowCat }}">{{ __('finance.categories.' . $outflowCat) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div x-show="category === 'other'" x-transition class="p-3.5 rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/30 space-y-1.5">
                                    <label class="block text-[12.5px] font-bold text-[#FF3B30] dark:text-[#FF453A] flex items-center gap-1.5">
                                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                        <span>{{ __('finance.other_desc_label') }}</span>
                                    </label>
                                    <input name="other_description" x-model="otherDescription" :required="category === 'other'"
                                        placeholder="{{ __('finance.other_desc_outflow_placeholder') }}"
                                        class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-[#FF3B30]/30 rounded-[10px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#FF3B30]">
                                    <p class="text-[11px] text-[#FF3B30]/80">{{ __('finance.other_desc_outflow_hint') }}</p>
                                </div>
                                <div>
                                    <label class="block text-[13px] font-bold text-black/80 dark:text-white/80 mb-1.5">{{ __('finance.notes_optional') }}</label>
                                    <input name="description" placeholder="{{ __('finance.notes_outflow_placeholder') }}"
                                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#FF3B30]">
                                </div>
                            </div>

                            {{-- Kolom Kanan: Nominal XXL (6/12) --}}
                            <div class="lg:col-span-6 space-y-4">
                                <div class="p-5 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3.5">
                                    <label class="block text-[13px] font-bold text-black/80 dark:text-white/80">{{ __('finance.amount_out_label') }}</label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 font-bold text-lg sm:text-xl">Rp</span>
                                        <input name="amount" type="number" min="1" step="any" x-model="amount" required placeholder="0"
                                            class="w-full h-14 pl-14 pr-4 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[14px] text-2xl sm:text-3xl font-extrabold tabular-nums text-black dark:text-white placeholder:text-black/20 dark:placeholder:text-white/20 focus:outline-none focus:ring-2 focus:ring-[#FF3B30] transition-all shadow-inner">
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2 pt-1">
                                        <span class="text-[11.5px] text-black/45 dark:text-white/45 font-medium">{{ __('finance.quick_amount') }}</span>
                                        <button type="button" @click="setQuickAmount(50000)" class="px-2.5 py-1 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[12px] font-semibold text-black/75 dark:text-white/75 transition cursor-pointer">Rp 50 rb</button>
                                        <button type="button" @click="setQuickAmount(100000)" class="px-2.5 py-1 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[12px] font-semibold text-black/75 dark:text-white/75 transition cursor-pointer">Rp 100 rb</button>
                                        <button type="button" @click="setQuickAmount(250000)" class="px-2.5 py-1 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[12px] font-semibold text-black/75 dark:text-white/75 transition cursor-pointer">Rp 250 rb</button>
                                        <button type="button" @click="setQuickAmount(500000)" class="px-2.5 py-1 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[12px] font-semibold text-black/75 dark:text-white/75 transition cursor-pointer">Rp 500 rb</button>
                                        <button type="button" @click="setQuickAmount(1000000)" class="px-2.5 py-1 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[12px] font-semibold text-black/75 dark:text-white/75 transition cursor-pointer">Rp 1 jt</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-5 border-t border-black/5 dark:border-white/10">
                            <button type="button" @click="showOutflowModal = false" :disabled="isSubmitting"
                                class="w-full sm:w-auto min-h-[48px] px-6 rounded-[14px] text-[14px] font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 transition-all cursor-pointer">{{ __('common.cancel') }}</button>
                            <button type="submit" :disabled="isSubmitting"
                                class="w-full sm:w-auto min-h-[48px] px-8 rounded-[14px] text-[14.5px] font-bold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.98] transition-all shadow-[0_4px_16px_rgba(255,59,48,0.3)] flex items-center justify-center gap-2 cursor-pointer">
                                <template x-if="isSubmitting">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                </template>
                                <i data-lucide="check" class="w-4 h-4" x-show="!isSubmitting"></i>
                                <span x-text="isSubmitting ? '{{ __('common.saving') }}' : '{{ __('finance.save_outflow') }}'">{{ __('finance.save_outflow') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ====================================================== --}}
            {{-- MODAL TRANSFER ANTAR REKENING (BENTO XXL)               --}}
            {{-- ====================================================== --}}
            <div x-show="showTransferModal" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-black/40 backdrop-blur-md"
                x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                @keydown.escape.window="showTransferModal = false">
                <div class="w-full max-w-full sm:max-w-3xl lg:max-w-4xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_25px_70px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[92vh]"
                    @click.away="showTransferModal = false"
                    x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                    x-data="{ isSubmitting: false, amount: '', setQuickAmount(val) { this.amount = val; } }">
                    <div class="flex items-center justify-between px-6 sm:px-8 py-5 border-b border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02]">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-[14px] bg-[#007AFF]/12 text-[#007AFF] flex items-center justify-center shrink-0 shadow-sm">
                                <i data-lucide="arrow-left-right" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white tracking-tight">Transfer Antar Rekening</h3>
                                <p class="text-[12.5px] text-black/50 dark:text-white/50">Pemindahan saldo kas tunai ke rekening bank atau antar rekening internal</p>
                            </div>
                        </div>
                        <button type="button" @click="showTransferModal = false"
                            class="w-9 h-9 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 flex items-center justify-center transition-all cursor-pointer">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('finance.cash-bank.transfer') }}"
                        @submit="isSubmitting = true"
                        class="flex-1 overflow-y-auto p-5 sm:p-8 space-y-6">
                        @csrf
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                            {{-- Kolom Kiri: Rekening Asal & Tujuan (6/12) --}}
                            <div class="lg:col-span-6 space-y-4">
                                <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                                    <div>
                                        <label class="block text-[12px] font-bold text-black/80 dark:text-white/80 mb-1">Dari Rekening Asal (Pengurang Saldo) *</label>
                                        <select name="from_account_id" required
                                            class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                            @foreach ($accounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }} (Saldo: Rp {{ number_format($account->current_balance, 0, ',', '.') }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="flex items-center justify-center py-0.5 text-[#007AFF]">
                                        <i data-lucide="arrow-down" class="w-5 h-5"></i>
                                    </div>

                                    <div>
                                        <label class="block text-[12px] font-bold text-black/80 dark:text-white/80 mb-1">Ke Rekening Tujuan (Penambah Saldo) *</label>
                                        <select name="to_account_id" required
                                            class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                            @foreach ($accounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }} (Saldo: Rp {{ number_format($account->current_balance, 0, ',', '.') }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[13px] font-bold text-black/80 dark:text-white/80 mb-1.5">Keterangan Transfer *</label>
                                    <input name="description" required placeholder="Contoh: Setoran uang tunai kasir toko ke rekening BCA Operasional"
                                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                </div>
                            </div>

                            {{-- Kolom Kanan: Nominal XXL (6/12) --}}
                            <div class="lg:col-span-6 space-y-4">
                                <div class="p-5 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3.5">
                                    <label class="block text-[13px] font-bold text-black/80 dark:text-white/80">Nominal Transfer (Rp) *</label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 font-bold text-lg sm:text-xl">Rp</span>
                                        <input name="amount" type="number" min="1" step="any" x-model="amount" required placeholder="0"
                                            class="w-full h-14 pl-14 pr-4 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[14px] text-2xl sm:text-3xl font-extrabold tabular-nums text-black dark:text-white placeholder:text-black/20 dark:placeholder:text-white/20 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition-all shadow-inner">
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2 pt-1">
                                        <span class="text-[11.5px] text-black/45 dark:text-white/45 font-medium">Nominal Cepat:</span>
                                        <button type="button" @click="setQuickAmount(500000)" class="px-2.5 py-1 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[12px] font-semibold text-black/75 dark:text-white/75 transition cursor-pointer">Rp 500 rb</button>
                                        <button type="button" @click="setQuickAmount(1000000)" class="px-2.5 py-1 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[12px] font-semibold text-black/75 dark:text-white/75 transition cursor-pointer">Rp 1 jt</button>
                                        <button type="button" @click="setQuickAmount(2500000)" class="px-2.5 py-1 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[12px] font-semibold text-black/75 dark:text-white/75 transition cursor-pointer">Rp 2.5 jt</button>
                                        <button type="button" @click="setQuickAmount(5000000)" class="px-2.5 py-1 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[12px] font-semibold text-black/75 dark:text-white/75 transition cursor-pointer">Rp 5 jt</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-5 border-t border-black/5 dark:border-white/10">
                            <button type="button" @click="showTransferModal = false" :disabled="isSubmitting"
                                class="w-full sm:w-auto min-h-[48px] px-6 rounded-[14px] text-[14px] font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 transition-all cursor-pointer">Batal</button>
                            <button type="submit" :disabled="isSubmitting"
                                class="w-full sm:w-auto min-h-[48px] px-8 rounded-[14px] text-[14.5px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all shadow-[0_4px_16px_rgba(0,122,255,0.3)] flex items-center justify-center gap-2 cursor-pointer">
                                <template x-if="isSubmitting">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                </template>
                                <i data-lucide="check" class="w-4 h-4" x-show="!isSubmitting"></i>
                                <span x-text="isSubmitting ? 'Mengeksekusi...' : 'Eksekusi Transfer'">Eksekusi Transfer</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

    </div>
@endsection
