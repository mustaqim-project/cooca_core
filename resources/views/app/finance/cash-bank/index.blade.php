@extends('layouts.app', ['title' => 'Kas & Rekening Bank'])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-5 pb-12" x-data="{
        showTransferModal: false,
        showInflowModal: false,
        showOutflowModal: false,
        showAccountModal: false,
        editAccountModal: false,
        currentAccount: { id: '', name: '', type: 'bank', is_active: true }
    }">

        {{-- ========================================================== --}}
        {{-- TOOLBAR / PAGE HEADER --}}
        {{-- ========================================================== --}}
        <header
            class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                    <i data-lucide="chevron-right" class="w-3 h-3 opacity-40"></i>
                    <span class="text-black/70 dark:text-white/70 font-medium">Keuangan</span>
                    <i data-lucide="chevron-right" class="w-3 h-3 opacity-40"></i>
                    <span class="text-black dark:text-white font-medium">Kas &amp; Rekening Bank</span>
                </nav>
                <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Kas &amp; Rekening Bank</h1>
                <p class="text-[13px] text-black/50 dark:text-white/50">Kelola multi-rekening bank operasional, buku kasir toko, dan mutasi arus dana</p>
            </div>
            <div class="flex items-center flex-wrap gap-2 w-full sm:w-auto">
                @if (\App\Support\Context::hasPermission('finance.cash_bank'))
                    <button type="button" @click="showAccountModal = true"
                        class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                        <i data-lucide="plus-circle" class="w-4 h-4 text-[#007AFF]"></i>
                        <span>+ Rekening Baru</span>
                    </button>
                    <button type="button" @click="showInflowModal = true"
                        class="h-9 px-3.5 rounded-[10px] text-[13px] font-semibold text-white bg-[#34C759] hover:bg-[#2DBE50] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(52,199,89,0.25)]">
                        <i data-lucide="arrow-down-left" class="w-4 h-4"></i>
                        <span>Kas Masuk</span>
                    </button>
                    <button type="button" @click="showOutflowModal = true"
                        class="h-9 px-3.5 rounded-[10px] text-[13px] font-semibold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(255,59,48,0.25)]">
                        <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
                        <span>Kas Keluar</span>
                    </button>
                    <button type="button" @click="showTransferModal = true"
                        class="h-9 px-3.5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                        <i data-lucide="arrow-left-right" class="w-4 h-4"></i>
                        <span>Transfer</span>
                    </button>
                @endif
                @if (\App\Support\Context::hasPermission('accounting.view') || \App\Support\Context::hasPermission('finance.cash_bank'))
                    <a href="{{ route('finance.cash-bank.ledger') }}"
                        class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                        <i data-lucide="book-open" class="w-4 h-4 text-[#FF9500]"></i>
                        <span>Buku Kas</span>
                    </a>
                @endif
            </div>
        </header>

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
            {{-- MODAL TAMBAH REKENING / AKUN KAS BARU --}}
            <div x-show="showAccountModal" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/30 backdrop-blur-sm"
                x-transition.opacity>
                <div class="w-full max-w-md rounded-[20px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.25)] overflow-hidden"
                    @click.away="showAccountModal = false">
                    <div class="flex items-center justify-between px-5 pt-5 pb-4 border-b border-black/5 dark:border-white/10">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-[#007AFF]/12 text-[#007AFF] flex items-center justify-center">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                            </div>
                            <h3 class="text-[16px] font-semibold text-black dark:text-white">Tambah Rekening / Akun Kas</h3>
                        </div>
                        <button type="button" @click="showAccountModal = false"
                            class="w-8 h-8 rounded-full bg-black/[0.06] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:bg-black/10 flex items-center justify-center transition-colors">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('finance.cash-bank.accounts.store') }}" class="px-5 py-4 space-y-3.5">
                        @csrf
                        <div>
                            <label class="{{ $modalLabelCls }}">Nama Rekening / Akun Kas *</label>
                            <input name="name" required placeholder="Contoh: Rekening BCA Operasional / Kasir Toko"
                                class="{{ $modalInputCls }}">
                        </div>
                        <div>
                            <label class="{{ $modalLabelCls }}">Tipe Akun *</label>
                            <select name="type" required class="{{ $modalSelectCls }}">
                                <option value="bank">Rekening Bank (BCA, Mandiri, BRI, BNI, dll.)</option>
                                <option value="cash">Kas Tunai / Kasir Toko</option>
                                <option value="ewallet">E-Wallet / Saldo QRIS Gateway</option>
                            </select>
                        </div>
                        <div>
                            <label class="{{ $modalLabelCls }}">Saldo Awal Pembukaan (Rp)</label>
                            <input name="opening_balance" type="number" min="0" step="1" placeholder="0"
                                class="{{ $modalInputCls }} tabular-nums font-semibold">
                            <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Kosongkan jika rekening baru dimulai dari saldo nol.</p>
                        </div>
                        <div class="flex items-center gap-2 pt-2 border-t border-black/5 dark:border-white/10">
                            <button type="button" @click="showAccountModal = false"
                                class="flex-1 h-11 rounded-[10px] text-[14px] font-medium text-black/60 dark:text-white/60 hover:bg-black/5 transition-all">Batal</button>
                            <button type="submit"
                                class="flex-1 h-11 rounded-[10px] text-[14px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all shadow-sm">Simpan Rekening</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- MODAL EDIT REKENING --}}
            <div x-show="editAccountModal" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/30 backdrop-blur-sm"
                x-transition.opacity>
                <div class="w-full max-w-md rounded-[20px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.25)] overflow-hidden"
                    @click.away="editAccountModal = false">
                    <div class="flex items-center justify-between px-5 pt-5 pb-4 border-b border-black/5 dark:border-white/10">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-[#FF9500]/12 text-[#FF9500] flex items-center justify-center">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                            </div>
                            <h3 class="text-[16px] font-semibold text-black dark:text-white">Ubah Data Rekening</h3>
                        </div>
                        <button type="button" @click="editAccountModal = false"
                            class="w-8 h-8 rounded-full bg-black/[0.06] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:bg-black/10 flex items-center justify-center transition-colors">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <form method="POST" :action="'{{ url('finance/cash-bank/accounts') }}/' + currentAccount.id" class="px-5 py-4 space-y-3.5">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="{{ $modalLabelCls }}">Nama Rekening / Akun Kas *</label>
                            <input name="name" x-model="currentAccount.name" required class="{{ $modalInputCls }}">
                        </div>
                        <div>
                            <label class="{{ $modalLabelCls }}">Tipe Akun *</label>
                            <select name="type" x-model="currentAccount.type" required class="{{ $modalSelectCls }}">
                                <option value="bank">Rekening Bank</option>
                                <option value="cash">Kas Tunai / Kasir Toko</option>
                                <option value="ewallet">E-Wallet / QRIS</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2 pt-2 border-t border-black/5 dark:border-white/10">
                            <button type="button" @click="editAccountModal = false"
                                class="flex-1 h-11 rounded-[10px] text-[14px] font-medium text-black/60 dark:text-white/60 hover:bg-black/5 transition-all">Batal</button>
                            <button type="submit"
                                class="flex-1 h-11 rounded-[10px] text-[14px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all shadow-sm">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- MODAL KAS MASUK --}}
            <div x-show="showInflowModal" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/30 backdrop-blur-sm"
                x-transition.opacity>
                <div class="w-full max-w-md rounded-[20px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.25)] overflow-hidden"
                    @click.away="showInflowModal = false">
                    <div class="flex items-center justify-between px-5 pt-5 pb-4 border-b border-black/5 dark:border-white/10">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-[#34C759]/15 text-[#34C759] flex items-center justify-center">
                                <i data-lucide="arrow-down-left" class="w-4 h-4"></i>
                            </div>
                            <h3 class="text-[16px] font-semibold text-black dark:text-white">Catat Kas Masuk</h3>
                        </div>
                        <button type="button" @click="showInflowModal = false"
                            class="w-8 h-8 rounded-full bg-black/[0.06] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:bg-black/10 flex items-center justify-center transition-colors">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('finance.cash-bank.inflow') }}" class="px-5 py-4 space-y-3.5">
                        @csrf
                        <div>
                            <label class="{{ $modalLabelCls }}">Rekening Kas / Bank Penerima *</label>
                            <select name="account_id" class="{{ $modalSelectCls }}">
                                <option value="">Otomatis (Berdasarkan Metode)</option>
                                @foreach ($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }} ({{ strtoupper($acc->type) }}) - Rp {{ number_format($acc->current_balance, 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="{{ $modalLabelCls }}">Metode Pembayaran</label>
                            <select name="account_method" class="{{ $modalSelectCls }}">
                                <option value="cash">Kas Tunai (Cash)</option>
                                <option value="bank_transfer">Rekening Bank</option>
                                <option value="qris">QRIS / E-Wallet</option>
                            </select>
                        </div>
                        <div>
                            <label class="{{ $modalLabelCls }}">Nominal (Rp) *</label>
                            <input name="amount" type="number" min="1" step="1" required placeholder="500000" class="{{ $modalInputCls }} tabular-nums font-semibold">
                        </div>
                        <div>
                            <label class="{{ $modalLabelCls }}">Keterangan Penerimaan *</label>
                            <input name="description" required placeholder="Contoh: Setoran modal awal atau pendapatan non-POS" class="{{ $modalInputCls }}">
                        </div>
                        <div class="flex items-center gap-2 pt-2 border-t border-black/5 dark:border-white/10">
                            <button type="button" @click="showInflowModal = false"
                                class="flex-1 h-11 rounded-[10px] text-[14px] font-medium text-black/60 dark:text-white/60 hover:bg-black/5 transition-all">Batal</button>
                            <button type="submit"
                                class="flex-1 h-11 rounded-[10px] text-[14px] font-semibold text-white bg-[#34C759] hover:bg-[#2DBE50] active:scale-[0.97] transition-all shadow-sm">Simpan Kas Masuk</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- MODAL KAS KELUAR --}}
            <div x-show="showOutflowModal" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/30 backdrop-blur-sm"
                x-transition.opacity>
                <div class="w-full max-w-md rounded-[20px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.25)] overflow-hidden"
                    @click.away="showOutflowModal = false">
                    <div class="flex items-center justify-between px-5 pt-5 pb-4 border-b border-black/5 dark:border-white/10">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-[#FF3B30]/12 text-[#FF3B30] flex items-center justify-center">
                                <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
                            </div>
                            <h3 class="text-[16px] font-semibold text-black dark:text-white">Catat Pengeluaran Kas Keluar</h3>
                        </div>
                        <button type="button" @click="showOutflowModal = false"
                            class="w-8 h-8 rounded-full bg-black/[0.06] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:bg-black/10 flex items-center justify-center transition-colors">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('finance.cash-bank.outflow') }}" class="px-5 py-4 space-y-3.5">
                        @csrf
                        <div>
                            <label class="{{ $modalLabelCls }}">Sumber Rekening Kas / Bank</label>
                            <select name="account_id" class="{{ $modalSelectCls }}">
                                <option value="">Otomatis (Berdasarkan Metode)</option>
                                @foreach ($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }} ({{ strtoupper($acc->type) }}) - Saldo: Rp {{ number_format($acc->current_balance, 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="{{ $modalLabelCls }}">Metode Pembayaran</label>
                            <select name="account_method" class="{{ $modalSelectCls }}">
                                <option value="cash">Kas Tunai (Cash)</option>
                                <option value="bank_transfer">Rekening Bank</option>
                                <option value="qris">QRIS / E-Wallet</option>
                            </select>
                        </div>
                        <div>
                            <label class="{{ $modalLabelCls }}">Nominal (Rp) *</label>
                            <input name="amount" type="number" min="1" step="1" required placeholder="250000" class="{{ $modalInputCls }} tabular-nums font-semibold">
                        </div>
                        <div>
                            <label class="{{ $modalLabelCls }}">Keterangan Pengeluaran *</label>
                            <input name="description" required placeholder="Contoh: Pengambilan prive atau pengeluaran khusus" class="{{ $modalInputCls }}">
                        </div>
                        <div class="flex items-center gap-2 pt-2 border-t border-black/5 dark:border-white/10">
                            <button type="button" @click="showOutflowModal = false"
                                class="flex-1 h-11 rounded-[10px] text-[14px] font-medium text-black/60 dark:text-white/60 hover:bg-black/5 transition-all">Batal</button>
                            <button type="submit"
                                class="flex-1 h-11 rounded-[10px] text-[14px] font-semibold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.97] transition-all shadow-sm">Simpan Kas Keluar</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- MODAL TRANSFER ANTAR REKENING --}}
            <div x-show="showTransferModal" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/30 backdrop-blur-sm"
                x-transition.opacity>
                <div class="w-full max-w-md rounded-[20px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.25)] overflow-hidden"
                    @click.away="showTransferModal = false">
                    <div class="flex items-center justify-between px-5 pt-5 pb-4 border-b border-black/5 dark:border-white/10">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-[#007AFF]/12 text-[#007AFF] flex items-center justify-center">
                                <i data-lucide="arrow-left-right" class="w-4 h-4"></i>
                            </div>
                            <h3 class="text-[16px] font-semibold text-black dark:text-white">Transfer Antar Rekening</h3>
                        </div>
                        <button type="button" @click="showTransferModal = false"
                            class="w-8 h-8 rounded-full bg-black/[0.06] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:bg-black/10 flex items-center justify-center transition-colors">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('finance.cash-bank.transfer') }}" class="px-5 py-4 space-y-3.5">
                        @csrf
                        <div>
                            <label class="{{ $modalLabelCls }}">Dari Rekening Asal *</label>
                            <select name="from_account_id" required class="{{ $modalSelectCls }}">
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }} (Rp {{ number_format($account->current_balance, 0, ',', '.') }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="{{ $modalLabelCls }}">Ke Rekening Tujuan *</label>
                            <select name="to_account_id" required class="{{ $modalSelectCls }}">
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }} (Rp {{ number_format($account->current_balance, 0, ',', '.') }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="{{ $modalLabelCls }}">Nominal Transfer (Rp) *</label>
                            <input name="amount" type="number" min="1" step="1" required placeholder="1000000" class="{{ $modalInputCls }} tabular-nums font-semibold">
                        </div>
                        <div>
                            <label class="{{ $modalLabelCls }}">Keterangan Transfer *</label>
                            <input name="description" required placeholder="Contoh: Setoran uang tunai kasir ke rekening BCA" class="{{ $modalInputCls }}">
                        </div>
                        <div class="flex items-center gap-2 pt-2 border-t border-black/5 dark:border-white/10">
                            <button type="button" @click="showTransferModal = false"
                                class="flex-1 h-11 rounded-[10px] text-[14px] font-medium text-black/60 dark:text-white/60 hover:bg-black/5 transition-all">Batal</button>
                            <button type="submit"
                                class="flex-1 h-11 rounded-[10px] text-[14px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all shadow-sm">Eksekusi Transfer</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

    </div>
@endsection
