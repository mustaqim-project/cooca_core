@extends('layouts.app', ['title' => 'Beban Operasional Toko'])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-5 pb-16" x-data="{ showCreateModal: false }">

        {{-- ========================================================== --}}
        {{-- TOOLBAR / PAGE HEADER                                      --}}
        {{-- ========================================================== --}}
        <header class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
            <div>
                <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                    <span class="text-black/70 dark:text-white/70 font-medium">Keuangan &amp; Kas</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                    <span class="text-black dark:text-white font-semibold">Beban Operasional</span>
                </nav>
                <h1 class="text-[20px] sm:text-[22px] font-bold text-black dark:text-white tracking-tight">Beban &amp; Biaya Operasional</h1>
                <p class="text-[13px] text-black/50 dark:text-white/50">Catat pengeluaran dan biaya operasional toko dengan penjurnalan otomatis double-entry</p>
            </div>
            <div class="flex items-center gap-2.5 w-full sm:w-auto">
                @if (\App\Support\Context::hasPermission('accounting.view') || \App\Support\Context::hasPermission('expenses.manage'))
                    <a href="{{ route('finance.journals.index') }}"
                        class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all flex items-center gap-2">
                        <i data-lucide="book-marked" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                        <span>Lihat Buku Jurnal</span>
                    </a>
                @endif
                @if (\App\Support\Context::hasPermission('expenses.manage'))
                    <button @click="showCreateModal = true"
                        class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.98] transition-all flex items-center gap-2 shadow-[0_2px_8px_rgba(255,59,48,0.3)]">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        <span>Catat Biaya Baru</span>
                    </button>
                @endif
            </div>
        </header>

        {{-- Flash Alerts --}}
        @if (session('success'))
            <div class="rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/20 px-4 py-3 flex items-center gap-2.5 text-[13px] font-semibold text-[#248A3D] dark:text-[#30D158]">
                <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error') || $errors->any())
            <div class="rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 px-4 py-3 flex items-start gap-2.5 text-[13px] font-semibold text-[#C41E17] dark:text-[#FF453A]">
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
        {{-- BENTO KPI TILES                                            --}}
        {{-- ========================================================== --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45">Beban Bulan Ini</span>
                    <div class="w-8 h-8 rounded-[10px] bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center">
                        <i data-lucide="trending-down" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-[18px] sm:text-[22px] font-extrabold tabular-nums text-[#FF3B30] dark:text-[#FF453A] tracking-tight">
                        Rp {{ number_format($totalExpensesThisMonth, 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">Total pengeluaran operasional</p>
                </div>
            </div>

            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45">Jumlah Transaksi</span>
                    <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                        <i data-lucide="receipt" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-[18px] sm:text-[22px] font-extrabold tabular-nums text-black dark:text-white tracking-tight">
                        {{ $expenses->total() }}
                    </p>
                    <p class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">Nota biaya tercatat</p>
                </div>
            </div>

            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45">Rata-Rata Biaya</span>
                    <div class="w-8 h-8 rounded-[10px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center">
                        <i data-lucide="calculator" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-[18px] sm:text-[22px] font-extrabold tabular-nums text-black dark:text-white tracking-tight">
                        Rp {{ number_format($expenses->total() > 0 ? $totalExpensesThisMonth / max(1, $expenses->total()) : 0, 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">Rata-rata per catatan</p>
                </div>
            </div>

            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45">Penjurnalan</span>
                    <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        Double-Entry
                    </span>
                    <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Otomatis mutasi kas &amp; akun beban</p>
                </div>
            </div>
        </div>

        {{-- ========================================================== --}}
        {{-- SEARCH & FILTER TOOLBAR                                    --}}
        {{-- ========================================================== --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 shadow-sm">
            <form method="GET" action="{{ route('finance.expenses.index') }}" class="flex flex-wrap items-center gap-2.5">
                <div class="relative flex-1 min-w-[220px]">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nomor bukti / deskripsi / kategori..."
                        class="w-full h-11 sm:h-9 pl-9 pr-3.5 text-[16px] sm:text-[13px] bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>
                <select name="category"
                    class="h-11 sm:h-9 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories ?? [] as $cat)
                        <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ ucfirst($cat) }}</option>
                    @endforeach
                </select>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                    class="h-11 sm:h-9 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                <span class="text-black/30 dark:text-white/30 text-[12px]">–</span>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                    class="h-11 sm:h-9 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                <button type="submit"
                    class="h-11 sm:h-9 px-4 rounded-[10px] bg-[#007AFF] text-white text-[13px] font-semibold hover:bg-[#0071E3] transition-colors flex items-center gap-1.5 shadow-xs">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Filter</span>
                </button>
                @if (request('search') || request('category') || request('start_date') || request('end_date'))
                    <a href="{{ route('finance.expenses.index') }}"
                        class="h-11 sm:h-9 px-3 rounded-[10px] bg-black/5 dark:bg-white/5 text-black/60 dark:text-white/60 hover:bg-black/10 dark:hover:bg-white/10 text-[13px] font-medium flex items-center transition-colors">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- ========================================================== --}}
        {{-- EXPENSES TABLE (DESKTOP & TABLET)                          --}}
        {{-- ========================================================== --}}
        <div class="hidden sm:block rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px] min-w-[600px]">
                    <thead>
                        <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02]">
                            <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-black/45 dark:text-white/45">
                                No. Bukti / Tanggal
                            </th>
                            <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-black/45 dark:text-white/45">
                                Kategori &amp; Keterangan
                            </th>
                            <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-black/45 dark:text-white/45">
                                Outlet / Lokasi
                            </th>
                            <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-black/45 dark:text-white/45">
                                Metode Bayar
                            </th>
                            <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-black/45 dark:text-white/45 text-right">
                                Nominal Biaya
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($expenses as $ex)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                <td class="px-4 py-3.5">
                                    <div class="font-bold tabular-nums text-black dark:text-white">
                                        #{{ $ex->expense_number }}
                                    </div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 tabular-nums">
                                        {{ $ex->expense_date->format('d M Y') }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    @php
                                        $catMap = [
                                            'operational' => 'Operasional',
                                            'utilities' => 'Listrik & Air',
                                            'supplies' => 'Kemasan',
                                            'salaries' => 'Gaji & Upah',
                                            'maintenance' => 'Perawatan',
                                            'other' => 'Lain-lain',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                                        {{ $catMap[$ex->category] ?? $ex->category }}
                                    </span>
                                    <div class="text-[13px] font-medium text-black/80 dark:text-white/80 mt-1">
                                        {{ $ex->description }}
                                    </div>
                                    @if ($ex->proof_url)
                                        <div class="mt-1">
                                            <a href="{{ $ex->proof_url }}" target="_blank"
                                                class="inline-flex items-center gap-1 text-[11px] text-[#007AFF] font-semibold hover:underline">
                                                <i data-lucide="paperclip" class="w-3.5 h-3.5"></i>
                                                <span>Bukti Nota</span>
                                            </a>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-black/60 dark:text-white/60">
                                    {{ $ex->location->name ?? 'Outlet Utama' }}
                                </td>
                                <td class="px-4 py-3.5 text-[12px] text-black/60 dark:text-white/60 capitalize">
                                    {{ str_replace('_', ' ', $ex->payment_method) }}
                                </td>
                                <td class="px-4 py-3.5 text-right tabular-nums font-bold text-[#FF3B30] dark:text-[#FF453A] text-[14px]">
                                    Rp {{ number_format($ex->amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-12 h-12 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.04] text-black/30 dark:text-white/30 flex items-center justify-center">
                                            <i data-lucide="receipt" class="w-6 h-6"></i>
                                        </div>
                                        <div>
                                            <p class="text-[15px] font-bold text-black/70 dark:text-white/70">Belum Ada Pencatatan Biaya</p>
                                            <p class="text-[13px] text-black/40 dark:text-white/40 mt-0.5">Catat pengeluaran operasional pertama Anda dengan mudah</p>
                                        </div>
                                        @if (\App\Support\Context::hasPermission('expenses.manage'))
                                            <button @click="showCreateModal = true"
                                                class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-xs">
                                                <i data-lucide="plus" class="w-4 h-4"></i>
                                                <span>Catat Biaya Baru</span>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($expenses->hasPages())
                <div class="px-5 py-3.5 border-t border-black/5 dark:border-white/10 flex items-center justify-between text-[13px] text-black/60 dark:text-white/60">
                    <div>
                        Menampilkan <span class="font-bold text-black dark:text-white tabular-nums">{{ $expenses->firstItem() }}–{{ $expenses->lastItem() }}</span>
                        dari <span class="font-bold text-black dark:text-white tabular-nums">{{ $expenses->total() }}</span> catatan
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($expenses->onFirstPage())
                            <span class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-black/30 dark:text-white/30 cursor-not-allowed">‹ Sebelumnya</span>
                        @else
                            <a href="{{ $expenses->previousPageUrl() }}"
                                class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 flex items-center transition">‹ Sebelumnya</a>
                        @endif

                        @if ($expenses->hasMorePages())
                            <a href="{{ $expenses->nextPageUrl() }}"
                                class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 flex items-center transition">Selanjutnya ›</a>
                        @else
                            <span class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-black/30 dark:text-white/30 cursor-not-allowed">Selanjutnya ›</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- ========================================================== --}}
        {{-- MOBILE GROUPED INSET LIST (Apple iOS HIG)                  --}}
        {{-- ========================================================== --}}
        <div class="sm:hidden rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06] shadow-sm">
            @forelse($expenses as $ex)
                @php
                    $catMap = [
                        'operational' => 'Operasional',
                        'utilities' => 'Listrik & Air',
                        'supplies' => 'Kemasan',
                        'salaries' => 'Gaji & Upah',
                        'maintenance' => 'Perawatan',
                        'other' => 'Lain-lain',
                    ];
                @endphp
                <div class="p-4 space-y-2.5 active:bg-black/[0.02] dark:active:bg-white/[0.03] transition-colors">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-[14px] tabular-nums text-black dark:text-white">#{{ $ex->expense_number }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                                    {{ $catMap[$ex->category] ?? $ex->category }}
                                </span>
                            </div>
                            <div class="text-[12px] text-black/45 dark:text-white/45 mt-0.5">
                                {{ $ex->expense_date->format('d M Y') }} · {{ $ex->location->name ?? 'Outlet Utama' }}
                            </div>
                        </div>
                        <div class="text-right tabular-nums font-extrabold text-[15px] text-[#FF3B30] dark:text-[#FF453A] shrink-0">
                            Rp {{ number_format($ex->amount, 0, ',', '.') }}
                        </div>
                    </div>

                    @if ($ex->description)
                        <p class="text-[13px] text-black/75 dark:text-white/75 line-clamp-2">
                            {{ $ex->description }}
                        </p>
                    @endif

                    <div class="flex items-center justify-between pt-1.5 text-[11px] text-black/45 dark:text-white/45 border-t border-black/[0.03] dark:border-white/[0.04]">
                        <span class="capitalize">Via: {{ str_replace('_', ' ', $ex->payment_method) }}</span>
                        @if ($ex->proof_url)
                            <a href="{{ $ex->proof_url }}" target="_blank"
                                class="text-[#007AFF] font-semibold hover:underline inline-flex items-center gap-1">
                                <i data-lucide="paperclip" class="w-3 h-3"></i>
                                <span>Bukti Nota</span>
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-12 text-center text-black/45 dark:text-white/45 text-[13px]">
                    <p>Belum ada pencatatan biaya.</p>
                    @if (\App\Support\Context::hasPermission('expenses.manage'))
                        <button type="button" @click="showCreateModal = true"
                            class="mt-2 text-[#FF3B30] font-semibold text-[13px] hover:underline inline-flex items-center gap-1">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Catat Biaya Baru</span>
                        </button>
                    @endif
                </div>
            @endforelse

            @if ($expenses->hasPages())
                <div class="p-3 border-t border-black/5 dark:border-white/10">
                    {{ $expenses->links() }}
                </div>
            @endif
        </div>

        {{-- ========================================================== --}}
        {{-- MODAL - CATAT BIAYA (APPLE SHEET MACOS FLOATING)           --}}
        {{-- ========================================================== --}}
        <div x-show="showCreateModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="w-full max-w-lg rounded-[22px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden"
                @click.away="showCreateModal = false" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95">

                {{-- Sheet Header --}}
                <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-black/5 dark:border-white/10">
                    <div>
                        <h3 class="text-[17px] font-bold text-black dark:text-white">Catat Beban Operasional</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Input biaya toko &amp; mutasi kas/bank otomatis</p>
                    </div>
                    <button type="button" @click="showCreateModal = false"
                        class="w-8 h-8 rounded-full bg-black/[0.06] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:bg-black/[0.10] dark:hover:bg-white/[0.12] flex items-center justify-center transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                {{-- Form --}}
                <form action="{{ route('finance.expenses.store') }}" method="POST" enctype="multipart/form-data" class="px-6 py-5 space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="block text-[13px] font-semibold text-black/70 dark:text-white/70">Tanggal Biaya *</label>
                            <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-[13px] font-semibold text-black/70 dark:text-white/70">Kategori Biaya *</label>
                            <select name="category" required
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                <option value="operational">Operasional Toko</option>
                                <option value="utilities">Listrik, Air &amp; Internet</option>
                                <option value="supplies">Kemasan &amp; Plastik</option>
                                <option value="salaries">Gaji &amp; Upah Kasir</option>
                                <option value="maintenance">Perawatan &amp; Reparasi</option>
                                <option value="other">Lain-lain</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="block text-[13px] font-semibold text-black/70 dark:text-white/70">Nominal Biaya (Rp) *</label>
                            <input type="number" name="amount" required placeholder="0" min="1"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[15px] tabular-nums font-bold text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-[13px] font-semibold text-black/70 dark:text-white/70">Metode Bayar *</label>
                            <select name="payment_method" required
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                <option value="cash">Kas Tunai Kasir</option>
                                <option value="petty_cash">Kas Kecil (Petty Cash)</option>
                                <option value="bank_transfer">Transfer Rekening Bank</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-semibold text-black/70 dark:text-white/70">Sumber Rekening / Akun Kas</label>
                        <select name="cash_account_id"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="">Otomatis Sesuai Metode</option>
                            @foreach ($cashAccounts as $ca)
                                <option value="{{ $ca->id }}">{{ $ca->name }} (Saldo: Rp {{ number_format($ca->current_balance, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-semibold text-black/70 dark:text-white/70">Akun Beban (COA)</label>
                        <select name="account_id"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="">Otomatis (Standar Akuntansi)</option>
                            @foreach ($accounts as $coa)
                                <option value="{{ $coa->id }}">{{ $coa->code }} – {{ $coa->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-semibold text-black/70 dark:text-white/70">Outlet / Cabang</label>
                        <select name="location_id"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="">Outlet Utama (Pusat)</option>
                            @foreach ($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-semibold text-black/70 dark:text-white/70">Keterangan Biaya *</label>
                        <input type="text" name="description" required
                            placeholder="Misal: Beli gas LPG 3kg &amp; sabun cuci piring..."
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-semibold text-black/70 dark:text-white/70">Foto / Scan Nota Bukti (Opsional)</label>
                        <input type="file" name="receipt_image" accept="image/jpeg,image/png,image/webp,application/pdf"
                            class="w-full text-[13px] text-black/70 dark:text-white/70 file:mr-3 file:py-2.5 file:px-4 file:rounded-[8px] file:border-0 file:text-[12px] file:font-semibold file:bg-black/[0.06] dark:file:bg-white/[0.08] file:text-black dark:file:text-white hover:file:bg-black/[0.10] cursor-pointer">
                    </div>

                    <div class="flex items-center gap-3 pt-3 border-t border-black/5 dark:border-white/10">
                        <button type="button" @click="showCreateModal = false"
                            class="flex-1 h-11 rounded-[12px] text-[14px] font-medium text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 active:opacity-70 transition-all">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 h-11 rounded-[12px] text-[14px] font-bold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.98] transition-all shadow-[0_2px_8px_rgba(255,59,48,0.3)]">
                            Simpan Pengeluaran
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection

