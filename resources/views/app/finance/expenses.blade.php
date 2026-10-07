@extends('layouts.app', ['title' => 'Beban Operasional Toko'])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-5 pb-28 lg:pb-12" x-data="{ showCreateModal: false }">

        {{-- ========================================================== --}}
        {{-- TOOLBAR / PAGE HEADER                                      --}}
        {{-- ========================================================== --}}
        <x-module-header
            title="Beban & Biaya Operasional"
            subtitle="Catat pengeluaran dan biaya operasional toko dengan penjurnalan otomatis double-entry">
            @if (\App\Support\Context::hasPermission('accounting.view') || \App\Support\Context::hasPermission('expenses.manage'))
                <a href="{{ route('finance.journals.index') }}"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-2">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-[#5856D6]"></i>
                    <span>Buku Jurnal</span>
                </a>
            @endif
            @if (\App\Support\Context::hasPermission('expenses.manage'))
                <button @click="showCreateModal = true"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-2 shadow-[0_2px_8px_rgba(255,59,48,0.3)]">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Catat Biaya Baru</span>
                </button>
            @endif
        </x-module-header>

        {{-- ========================================================== --}}
        {{-- MODULE TABS (SSOT)                                         --}}
        {{-- ========================================================== --}}
        <x-module-tabs module="finance" />

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
                    @php
                        $standardCategories = [
                            'operational' => 'Operasional Toko & Kantor',
                            'utilities' => 'Listrik, Air & Gas',
                            'internet_phone' => 'Internet & Komunikasi',
                            'supplies' => 'Kemasan & Plastik',
                            'salaries' => 'Gaji & Upah Karyawan',
                            'consumption' => 'Konsumsi & Makan Staf',
                            'logistics' => 'Transportasi & Logistik',
                            'rent' => 'Sewa Tempat & Gedung',
                            'maintenance' => 'Perawatan & Servis Alat',
                            'marketing' => 'Pemasaran & Iklan',
                            'taxes_legal' => 'Pajak & Retribusi',
                            'bank_admin' => 'Biaya Admin Bank & MDR',
                            'cash_advance' => 'Kasbon Karyawan',
                            'other' => 'Lain-lain / Others',
                        ];
                    @endphp
                    @foreach ($standardCategories as $catKey => $catLabel)
                        <option value="{{ $catKey }}" @selected(request('category') === $catKey)>{{ $catLabel }}</option>
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
                                            'operational' => 'Operasional Toko',
                                            'utilities' => 'Listrik, Air & Gas',
                                            'internet_phone' => 'Internet & Pulsa',
                                            'supplies' => 'Kemasan & Plastik',
                                            'salaries' => 'Gaji & Upah',
                                            'consumption' => 'Konsumsi Staf',
                                            'logistics' => 'Transport & Bensin',
                                            'rent' => 'Sewa Gedung/Ruko',
                                            'maintenance' => 'Perawatan & Servis',
                                            'marketing' => 'Pemasaran & Iklan',
                                            'taxes_legal' => 'Pajak & Retribusi',
                                            'bank_admin' => 'Admin Bank & MDR',
                                            'cash_advance' => 'Kasbon Karyawan',
                                            'owner_draw' => 'Prive Pemilik',
                                            'other' => 'Lain-lain / Others',
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
                            <span class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-black/30 dark:text-white/30 cursor-not-allowed flex items-center"><i data-lucide="chevron-left" class="w-3.5 h-3.5 mr-1"></i> Sebelumnya</span>
                        @else
                            <a href="{{ $expenses->previousPageUrl() }}"
                                class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 flex items-center transition"><i data-lucide="chevron-left" class="w-3.5 h-3.5 mr-1"></i> Sebelumnya</a>
                        @endif

                        @if ($expenses->hasMorePages())
                            <a href="{{ $expenses->nextPageUrl() }}"
                                class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 flex items-center transition">Selanjutnya <i data-lucide="chevron-right" class="w-3.5 h-3.5 ml-1"></i></a>
                        @else
                            <span class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-black/30 dark:text-white/30 cursor-not-allowed flex items-center">Selanjutnya <i data-lucide="chevron-right" class="w-3.5 h-3.5 ml-1"></i></span>
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
                                            'operational' => 'Operasional Toko',
                                            'utilities' => 'Listrik, Air & Gas',
                                            'internet_phone' => 'Internet & Pulsa',
                                            'supplies' => 'Kemasan & Plastik',
                                            'salaries' => 'Gaji & Upah',
                                            'consumption' => 'Konsumsi Staf',
                                            'logistics' => 'Transport & Bensin',
                                            'rent' => 'Sewa Gedung/Ruko',
                                            'maintenance' => 'Perawatan & Servis',
                                            'marketing' => 'Pemasaran & Iklan',
                                            'taxes_legal' => 'Pajak & Retribusi',
                                            'bank_admin' => 'Admin Bank & MDR',
                                            'cash_advance' => 'Kasbon Karyawan',
                                            'owner_draw' => 'Prive Pemilik',
                                            'other' => 'Lain-lain / Others',
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
        {{-- MODAL - CATAT BIAYA (BENTO APPLE HIG v2.0 XXL CANVAS)      --}}
        {{-- ========================================================== --}}
        <div x-show="showCreateModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-black/40 backdrop-blur-md"
            x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            @keydown.escape.window="showCreateModal = false">

            <div class="w-full max-w-[95vw] lg:max-w-5xl 2xl:max-w-[1250px] mx-auto rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_25px_70px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[92vh]"
                @click.away="showCreateModal = false"
                x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                x-data="{
                    isSubmitting: false,
                    category: 'operational',
                    amount: '',
                    payment_method: 'cash',
                    cash_account_id: '{{ $cashAccounts->first()?->id ?? '' }}',
                    account_id: '',
                    location_id: '',
                    description: '',
                    otherDescription: '',
                    receiptPreview: null,
                    setQuickAmount(val) {
                        this.amount = val;
                    },
                    handleFileSelect(e) {
                        const file = e.target.files[0];
                        if (file && file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = (evt) => { this.receiptPreview = evt.target.result; };
                            reader.readAsDataURL(file);
                        } else {
                            this.receiptPreview = null;
                        }
                    },
                    get categoryLabel() {
                        const map = {
                            'operational': 'Operasional Toko & Kantor',
                            'utilities': 'Listrik, Air & Gas',
                            'internet_phone': 'Internet & Komunikasi',
                            'supplies': 'Kemasan & Plastik',
                            'salaries': 'Gaji & Upah Staf',
                            'consumption': 'Konsumsi & Makan Staf',
                            'logistics': 'Transportasi & Bensin',
                            'rent': 'Sewa Tempat & Gedung',
                            'maintenance': 'Perawatan & Servis Alat',
                            'marketing': 'Pemasaran & Iklan',
                            'taxes_legal': 'Pajak & Retribusi',
                            'bank_admin': 'Biaya Admin Bank & MDR',
                            'cash_advance': 'Kasbon Karyawan',
                            'other': 'Lain-lain / Others'
                        };
                        return map[this.category] || 'Beban Operasional';
                    }
                }">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-6 sm:px-8 py-5 border-b border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02]">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-[14px] bg-[#FF3B30]/12 text-[#FF3B30] flex items-center justify-center shrink-0 shadow-sm">
                            <i data-lucide="receipt" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white tracking-tight">Catat Beban Operasional</h3>
                            <p class="text-[12.5px] text-black/50 dark:text-white/50">Formulir pengeluaran toko terintegrasi jurnal akuntansi double-entry & saldo kas otomatis</p>
                        </div>
                    </div>
                    <button type="button" @click="showCreateModal = false"
                        class="w-9 h-9 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 flex items-center justify-center transition-all cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                {{-- Form Body --}}
                <form action="{{ route('finance.expenses.store') }}" method="POST" enctype="multipart/form-data"
                    @submit="if(category === 'other' && !otherDescription.trim()){ alert('Silakan isi rincian keterangan wajib untuk kategori Lainnya.'); isSubmitting = false; $event.preventDefault(); return false; } isSubmitting = true"
                    class="flex-1 overflow-y-auto p-5 sm:p-8 space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                        {{-- Left Column (7/12) --}}
                        <div class="lg:col-span-7 space-y-5">
                            
                            {{-- Bento Card: Kategori Biaya Visual (14 Kategori Komprehensif) --}}
                            <div class="p-5 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3.5">
                                <div class="flex items-center justify-between">
                                    <label class="block text-[13px] font-bold text-black/80 dark:text-white/80">Kategori Biaya Operasional *</label>
                                    <span class="text-[11.5px] text-black/40 dark:text-white/40" x-text="categoryLabel">Pilih jenis pengeluaran</span>
                                </div>
                                <input type="hidden" name="category" :value="category">
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 max-h-[300px] overflow-y-auto pr-1">
                                    <button type="button" @click="category = 'operational'"
                                        :class="category === 'operational' ? 'bg-[#007AFF] text-white shadow-md shadow-[#007AFF]/25 border-transparent' : 'bg-white dark:bg-[#2C2C2E] text-black/80 dark:text-white/80 border-black/5 dark:border-white/10 hover:border-black/15'"
                                        class="p-2.5 sm:p-3 rounded-[14px] border text-left flex flex-col justify-between transition-all cursor-pointer">
                                        <i data-lucide="store" class="w-4 h-4 mb-1.5" :class="category === 'operational' ? 'text-white' : 'text-[#007AFF]'"></i>
                                        <span class="text-[12px] font-bold">Operasional Toko</span>
                                        <span class="text-[10px] opacity-70">ATK, pembersih, umum</span>
                                    </button>

                                    <button type="button" @click="category = 'utilities'"
                                        :class="category === 'utilities' ? 'bg-[#FF9500] text-white shadow-md shadow-[#FF9500]/25 border-transparent' : 'bg-white dark:bg-[#2C2C2E] text-black/80 dark:text-white/80 border-black/5 dark:border-white/10 hover:border-black/15'"
                                        class="p-2.5 sm:p-3 rounded-[14px] border text-left flex flex-col justify-between transition-all cursor-pointer">
                                        <i data-lucide="zap" class="w-4 h-4 mb-1.5" :class="category === 'utilities' ? 'text-white' : 'text-[#FF9500]'"></i>
                                        <span class="text-[12px] font-bold">Listrik, Air & Gas</span>
                                        <span class="text-[10px] opacity-70">PLN, PAM, tabung LPG</span>
                                    </button>

                                    <button type="button" @click="category = 'internet_phone'"
                                        :class="category === 'internet_phone' ? 'bg-[#32ADE6] text-white shadow-md shadow-[#32ADE6]/25 border-transparent' : 'bg-white dark:bg-[#2C2C2E] text-black/80 dark:text-white/80 border-black/5 dark:border-white/10 hover:border-black/15'"
                                        class="p-2.5 sm:p-3 rounded-[14px] border text-left flex flex-col justify-between transition-all cursor-pointer">
                                        <i data-lucide="wifi" class="w-4 h-4 mb-1.5" :class="category === 'internet_phone' ? 'text-white' : 'text-[#32ADE6]'"></i>
                                        <span class="text-[12px] font-bold">Internet & Pulsa</span>
                                        <span class="text-[10px] opacity-70">Wi-Fi toko, kuota kasir</span>
                                    </button>

                                    <button type="button" @click="category = 'supplies'"
                                        :class="category === 'supplies' ? 'bg-[#5856D6] text-white shadow-md shadow-[#5856D6]/25 border-transparent' : 'bg-white dark:bg-[#2C2C2E] text-black/80 dark:text-white/80 border-black/5 dark:border-white/10 hover:border-black/15'"
                                        class="p-2.5 sm:p-3 rounded-[14px] border text-left flex flex-col justify-between transition-all cursor-pointer">
                                        <i data-lucide="package" class="w-4 h-4 mb-1.5" :class="category === 'supplies' ? 'text-white' : 'text-[#5856D6]'"></i>
                                        <span class="text-[12px] font-bold">Kemasan & Plastik</span>
                                        <span class="text-[10px] opacity-70">Plastik, dus, cup, lakban</span>
                                    </button>

                                    <button type="button" @click="category = 'salaries'"
                                        :class="category === 'salaries' ? 'bg-[#34C759] text-white shadow-md shadow-[#34C759]/25 border-transparent' : 'bg-white dark:bg-[#2C2C2E] text-black/80 dark:text-white/80 border-black/5 dark:border-white/10 hover:border-black/15'"
                                        class="p-2.5 sm:p-3 rounded-[14px] border text-left flex flex-col justify-between transition-all cursor-pointer">
                                        <i data-lucide="users" class="w-4 h-4 mb-1.5" :class="category === 'salaries' ? 'text-white' : 'text-[#34C759]'"></i>
                                        <span class="text-[12px] font-bold">Gaji & Upah</span>
                                        <span class="text-[10px] opacity-70">Gaji staf, upah harian</span>
                                    </button>

                                    <button type="button" @click="category = 'consumption'"
                                        :class="category === 'consumption' ? 'bg-[#FF2D55] text-white shadow-md shadow-[#FF2D55]/25 border-transparent' : 'bg-white dark:bg-[#2C2C2E] text-black/80 dark:text-white/80 border-black/5 dark:border-white/10 hover:border-black/15'"
                                        class="p-2.5 sm:p-3 rounded-[14px] border text-left flex flex-col justify-between transition-all cursor-pointer">
                                        <i data-lucide="utensils" class="w-4 h-4 mb-1.5" :class="category === 'consumption' ? 'text-white' : 'text-[#FF2D55]'"></i>
                                        <span class="text-[12px] font-bold">Konsumsi Staf</span>
                                        <span class="text-[10px] opacity-70">Makan siang, snack rapat</span>
                                    </button>

                                    <button type="button" @click="category = 'logistics'"
                                        :class="category === 'logistics' ? 'bg-[#FF9500] text-white shadow-md shadow-[#FF9500]/25 border-transparent' : 'bg-white dark:bg-[#2C2C2E] text-black/80 dark:text-white/80 border-black/5 dark:border-white/10 hover:border-black/15'"
                                        class="p-2.5 sm:p-3 rounded-[14px] border text-left flex flex-col justify-between transition-all cursor-pointer">
                                        <i data-lucide="truck" class="w-4 h-4 mb-1.5" :class="category === 'logistics' ? 'text-white' : 'text-[#FF9500]'"></i>
                                        <span class="text-[12px] font-bold">Transport & Bensin</span>
                                        <span class="text-[10px] opacity-70">BBM, kurir instan, tol</span>
                                    </button>

                                    <button type="button" @click="category = 'rent'"
                                        :class="category === 'rent' ? 'bg-[#5856D6] text-white shadow-md shadow-[#5856D6]/25 border-transparent' : 'bg-white dark:bg-[#2C2C2E] text-black/80 dark:text-white/80 border-black/5 dark:border-white/10 hover:border-black/15'"
                                        class="p-2.5 sm:p-3 rounded-[14px] border text-left flex flex-col justify-between transition-all cursor-pointer">
                                        <i data-lucide="building-2" class="w-4 h-4 mb-1.5" :class="category === 'rent' ? 'text-white' : 'text-[#5856D6]'"></i>
                                        <span class="text-[12px] font-bold">Sewa Tempat</span>
                                        <span class="text-[10px] opacity-70">Sewa ruko, booth, IPL</span>
                                    </button>

                                    <button type="button" @click="category = 'maintenance'"
                                        :class="category === 'maintenance' ? 'bg-[#AF52DE] text-white shadow-md shadow-[#AF52DE]/25 border-transparent' : 'bg-white dark:bg-[#2C2C2E] text-black/80 dark:text-white/80 border-black/5 dark:border-white/10 hover:border-black/15'"
                                        class="p-2.5 sm:p-3 rounded-[14px] border text-left flex flex-col justify-between transition-all cursor-pointer">
                                        <i data-lucide="wrench" class="w-4 h-4 mb-1.5" :class="category === 'maintenance' ? 'text-white' : 'text-[#AF52DE]'"></i>
                                        <span class="text-[12px] font-bold">Perawatan & Servis</span>
                                        <span class="text-[10px] opacity-70">Servis alat, reparasi AC</span>
                                    </button>

                                    <button type="button" @click="category = 'marketing'"
                                        :class="category === 'marketing' ? 'bg-[#FF3B30] text-white shadow-md shadow-[#FF3B30]/25 border-transparent' : 'bg-white dark:bg-[#2C2C2E] text-black/80 dark:text-white/80 border-black/5 dark:border-white/10 hover:border-black/15'"
                                        class="p-2.5 sm:p-3 rounded-[14px] border text-left flex flex-col justify-between transition-all cursor-pointer">
                                        <i data-lucide="megaphone" class="w-4 h-4 mb-1.5" :class="category === 'marketing' ? 'text-white' : 'text-[#FF3B30]'"></i>
                                        <span class="text-[12px] font-bold">Pemasaran & Iklan</span>
                                        <span class="text-[10px] opacity-70">Meta ads, cetak spanduk</span>
                                    </button>

                                    <button type="button" @click="category = 'taxes_legal'"
                                        :class="category === 'taxes_legal' ? 'bg-[#8E8E93] text-white shadow-md shadow-[#8E8E93]/25 border-transparent' : 'bg-white dark:bg-[#2C2C2E] text-black/80 dark:text-white/80 border-black/5 dark:border-white/10 hover:border-black/15'"
                                        class="p-2.5 sm:p-3 rounded-[14px] border text-left flex flex-col justify-between transition-all cursor-pointer">
                                        <i data-lucide="scale" class="w-4 h-4 mb-1.5" :class="category === 'taxes_legal' ? 'text-white' : 'text-[#8E8E93]'"></i>
                                        <span class="text-[12px] font-bold">Pajak & Retribusi</span>
                                        <span class="text-[10px] opacity-70">Pajak daerah, retribusi</span>
                                    </button>

                                    <button type="button" @click="category = 'bank_admin'"
                                        :class="category === 'bank_admin' ? 'bg-[#007AFF] text-white shadow-md shadow-[#007AFF]/25 border-transparent' : 'bg-white dark:bg-[#2C2C2E] text-black/80 dark:text-white/80 border-black/5 dark:border-white/10 hover:border-black/15'"
                                        class="p-2.5 sm:p-3 rounded-[14px] border text-left flex flex-col justify-between transition-all cursor-pointer">
                                        <i data-lucide="credit-card" class="w-4 h-4 mb-1.5" :class="category === 'bank_admin' ? 'text-white' : 'text-[#007AFF]'"></i>
                                        <span class="text-[12px] font-bold">Admin Bank & MDR</span>
                                        <span class="text-[10px] opacity-70">Admin rekening, MDR EDC</span>
                                    </button>

                                    <button type="button" @click="category = 'cash_advance'"
                                        :class="category === 'cash_advance' ? 'bg-[#FF9500] text-white shadow-md shadow-[#FF9500]/25 border-transparent' : 'bg-white dark:bg-[#2C2C2E] text-black/80 dark:text-white/80 border-black/5 dark:border-white/10 hover:border-black/15'"
                                        class="p-2.5 sm:p-3 rounded-[14px] border text-left flex flex-col justify-between transition-all cursor-pointer">
                                        <i data-lucide="hand-coins" class="w-4 h-4 mb-1.5" :class="category === 'cash_advance' ? 'text-white' : 'text-[#FF9500]'"></i>
                                        <span class="text-[12px] font-bold">Kasbon Karyawan</span>
                                        <span class="text-[10px] opacity-70">Talangan kasbon sementara</span>
                                    </button>

                                    <button type="button" @click="category = 'other'"
                                        :class="category === 'other' ? 'bg-[#8E8E93] text-white shadow-md shadow-[#8E8E93]/25 border-transparent' : 'bg-white dark:bg-[#2C2C2E] text-black/80 dark:text-white/80 border-black/5 dark:border-white/10 hover:border-black/15'"
                                        class="p-2.5 sm:p-3 rounded-[14px] border text-left flex flex-col justify-between transition-all cursor-pointer">
                                        <i data-lucide="more-horizontal" class="w-4 h-4 mb-1.5" :class="category === 'other' ? 'text-white' : 'text-[#8E8E93]'"></i>
                                        <span class="text-[12px] font-bold">Lain-lain / Others</span>
                                        <span class="text-[10px] opacity-70">Wajib isi rincian detail</span>
                                    </button>
                                </div>
                            </div>

                            {{-- Bento Card: Nominal Pengeluaran XXL --}}
                            <div class="p-5 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3.5">
                                <label class="block text-[13px] font-bold text-black/80 dark:text-white/80">Nominal Pengeluaran (Rp) *</label>
                                <div class="relative">
                                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 font-bold text-lg sm:text-xl">
                                        Rp
                                    </div>
                                    <input type="number" name="amount" x-model="amount" required min="1" step="any" placeholder="0"
                                        class="w-full h-14 pl-14 pr-4 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[14px] text-2xl sm:text-3xl font-extrabold tabular-nums text-black dark:text-white placeholder:text-black/20 dark:placeholder:text-white/20 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition-all shadow-inner">
                                </div>

                                {{-- Quick Suggestion Pills --}}
                                <div class="flex flex-wrap items-center gap-2 pt-1">
                                    <span class="text-[11.5px] text-black/45 dark:text-white/45 font-medium">Nominal Cepat:</span>
                                    <button type="button" @click="setQuickAmount(50000)" class="px-2.5 py-1 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[12px] font-semibold text-black/75 dark:text-white/75 transition cursor-pointer">Rp 50 rb</button>
                                    <button type="button" @click="setQuickAmount(100000)" class="px-2.5 py-1 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[12px] font-semibold text-black/75 dark:text-white/75 transition cursor-pointer">Rp 100 rb</button>
                                    <button type="button" @click="setQuickAmount(250000)" class="px-2.5 py-1 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[12px] font-semibold text-black/75 dark:text-white/75 transition cursor-pointer">Rp 250 rb</button>
                                    <button type="button" @click="setQuickAmount(500000)" class="px-2.5 py-1 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[12px] font-semibold text-black/75 dark:text-white/75 transition cursor-pointer">Rp 500 rb</button>
                                    <button type="button" @click="setQuickAmount(1000000)" class="px-2.5 py-1 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[12px] font-semibold text-black/75 dark:text-white/75 transition cursor-pointer">Rp 1 jt</button>
                                </div>
                            </div>

                            {{-- Tanggal & Outlet --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75">Tanggal Transaksi *</label>
                                    <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required
                                        class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75">Outlet / Cabang</label>
                                    <select name="location_id" x-model="location_id"
                                        class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                        <option value="">Outlet Utama (Pusat)</option>
                                        @foreach ($locations as $loc)
                                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Form Keterangan Standar (Muncul Saat Bukan 'other') --}}
                            <div class="space-y-1.5" x-show="category !== 'other'">
                                <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75">Keterangan / Rincian Beban</label>
                                <input type="text" name="description" x-model="description"
                                    placeholder="Misal: Pembelian kemasan takeaway & kantong plastik 500 pcs..."
                                    class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>

                            {{-- FORM KHUSUS WAJIB DIISI SAAT OTHERS --}}
                            <div x-show="category === 'other'" x-transition class="p-4 rounded-[16px] bg-amber-500/10 dark:bg-amber-500/15 border-2 border-amber-500/30 space-y-2">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="alert-circle" class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0"></i>
                                    <label class="block text-[13px] font-bold text-amber-800 dark:text-amber-300">
                                        Rincian Keterangan Wajib (Kategori Lainnya) <span class="text-[#FF3B30]">*</span>
                                    </label>
                                </div>
                                <input type="text" name="other_description" x-model="otherDescription" :required="category === 'other'"
                                    placeholder="Wajib diisi: rincikan peruntukan biaya pengeluaran ini secara jelas..."
                                    class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-amber-500/40 rounded-[12px] px-3.5 text-[16px] sm:text-[14px] font-medium text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-amber-500">
                                <p class="text-[11px] text-amber-700/80 dark:text-amber-300/80 leading-relaxed">
                                    Karena Anda memilih kategori Lain-lain, sistem mewajibkan rincian keterangan ini untuk transparansi dan audit pembukuan.
                                </p>
                            </div>

                        </div>

                        {{-- Right Column (5/12) --}}
                        <div class="lg:col-span-5 space-y-5">
                            
                            {{-- Bento Card: Sumber Kas & Akun COA --}}
                            <div class="p-5 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-4">
                                <h4 class="text-[13px] font-bold text-black/80 dark:text-white/80">Sumber Kas & Pembayaran</h4>
                                
                                <div class="space-y-1.5">
                                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">Metode Bayar *</label>
                                    <select name="payment_method" x-model="payment_method" required
                                        class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                        <option value="cash">Kas Tunai Kasir</option>
                                        <option value="petty_cash">Kas Kecil (Petty Cash)</option>
                                        <option value="bank_transfer">Transfer Rekening Bank</option>
                                    </select>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">Rekening Kas / Bank</label>
                                    <select name="cash_account_id" x-model="cash_account_id"
                                        class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                        <option value="">Otomatis Sesuai Metode</option>
                                        @foreach ($cashAccounts as $ca)
                                            <option value="{{ $ca->id }}">
                                                {{ $ca->name }} (Saldo: {{ $ca->current_balance < 0 ? '-Rp ' . number_format(abs($ca->current_balance), 0, ',', '.') . ' [Defisit/Minus]' : 'Rp ' . number_format($ca->current_balance, 0, ',', '.') }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="text-[11px] text-black/45 dark:text-white/45">
                                        Fleksibel: pengeluaran tetap dapat dicatat walau saldo 0 (menjadi saldo minus / talangan kas).
                                    </p>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">Akun Beban Buku Besar (COA)</label>
                                    <select name="account_id" x-model="account_id"
                                        class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                        <option value="">Otomatis (Standar Akuntansi)</option>
                                        @foreach ($accounts as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->code }} – {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Bento Card: Unggah Bukti Nota / Kwitansi --}}
                            <div class="p-5 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="block text-[13px] font-bold text-black/80 dark:text-white/80">Bukti Nota / Struk Fisik</label>
                                    <span class="text-[11px] text-black/40 dark:text-white/40">Opsional (Maks 5MB)</span>
                                </div>
                                
                                <div class="relative border-2 border-dashed border-black/15 dark:border-white/15 rounded-[16px] p-4 text-center hover:border-[#007AFF] dark:hover:border-[#007AFF] transition bg-white/50 dark:bg-[#2C2C2E]/50">
                                    <input type="file" name="receipt_image" accept="image/jpeg,image/png,image/webp,application/pdf"
                                        @change="handleFileSelect"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                    
                                    <template x-if="!receiptPreview">
                                        <div class="space-y-2 py-2">
                                            <div class="w-10 h-10 rounded-full bg-black/5 dark:bg-white/5 mx-auto flex items-center justify-center text-black/50 dark:text-white/50">
                                                <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                                            </div>
                                            <p class="text-[12.5px] font-medium text-black/75 dark:text-white/75">Tarik foto struk atau <span class="text-[#007AFF] font-bold">pilih berkas</span></p>
                                            <p class="text-[11px] text-black/40 dark:text-white/40">JPG, PNG, WEBP, atau PDF</p>
                                        </div>
                                    </template>

                                    <template x-if="receiptPreview">
                                        <div class="relative rounded-[12px] overflow-hidden max-h-[140px] flex items-center justify-center bg-black/5 dark:bg-white/5">
                                            <img :src="receiptPreview" class="max-h-[140px] object-contain rounded-[10px]">
                                            <div class="absolute bottom-1 right-1 px-2 py-0.5 rounded-[6px] bg-black/60 backdrop-blur-sm text-white text-[10px] font-semibold">
                                                Pratinjau Nota
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Bento Card: Pratinjau Jurnal Live --}}
                            <div class="p-4 rounded-[16px] bg-[#007AFF]/[0.06] dark:bg-[#007AFF]/[0.1] border border-[#007AFF]/20 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-[12px] font-bold text-[#007AFF]">
                                        <i data-lucide="book-open-check" class="w-4 h-4"></i>
                                        <span>Pratinjau Jurnal Otomatis</span>
                                    </div>
                                    <span class="text-[10.5px] font-semibold text-[#007AFF]/80 uppercase tracking-wider">Double-Entry</span>
                                </div>
                                <div class="text-[11.5px] space-y-1 text-black/75 dark:text-white/75 font-mono">
                                    <div class="flex justify-between">
                                        <span>(D) Beban: <strong class="font-sans" x-text="categoryLabel"></strong></span>
                                        <span class="font-bold text-black dark:text-white" x-text="amount ? 'Rp ' + Number(amount).toLocaleString('id-ID') : 'Rp 0'"></span>
                                    </div>
                                    <div class="flex justify-between text-black/60 dark:text-white/60 pl-3">
                                        <span>(K) Kas/Bank (<span class="capitalize" x-text="payment_method"></span>)</span>
                                        <span class="font-bold text-black dark:text-white" x-text="amount ? 'Rp ' + Number(amount).toLocaleString('id-ID') : 'Rp 0'"></span>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                    {{-- Footer Action Bar --}}
                    <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-5 border-t border-black/5 dark:border-white/10 bg-white dark:bg-[#1C1C1E] sticky bottom-0 z-20">
                        <button type="button" @click="showCreateModal = false"
                            :disabled="isSubmitting"
                            class="w-full sm:w-auto min-h-[48px] px-6 rounded-[14px] text-[14px] font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 active:opacity-70 transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                            :disabled="isSubmitting"
                            class="w-full sm:w-auto min-h-[48px] px-8 rounded-[14px] text-[14.5px] font-bold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.98] transition-all shadow-[0_4px_16px_rgba(255,59,48,0.35)] flex items-center justify-center gap-2 cursor-pointer">
                            <template x-if="isSubmitting">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                            </template>
                            <i data-lucide="check" class="w-4 h-4" x-show="!isSubmitting"></i>
                            <span x-text="isSubmitting ? 'Menyimpan Pengeluaran...' : 'Simpan Pengeluaran'">Simpan Pengeluaran</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
@endsection

