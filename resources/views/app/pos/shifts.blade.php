@extends('layouts.app', [
    'title' => 'Sesi Shift Kasir',
    'headerTitle' => 'Sesi Shift Kasir',
    'headerSubtitle' => 'Kelola pembukaan shift kasir, mutasi kas, dan rekonsiliasi laci uang fisik (cash drawer)',
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    showOpenModal: false,
    showCloseModal: false,
    showMovementModal: false,
    selectedShiftId: null,
    actualCash: 0,
    expectedCash: 0,
    openCloseModal(shiftId, expected) {
        this.selectedShiftId = shiftId;
        this.expectedCash = expected;
        this.actualCash = expected;
        this.showCloseModal = true;
    },
    openMovement(shiftId) {
        this.selectedShiftId = shiftId;
        this.showMovementModal = true;
    }
}">
    
    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <!-- Breadcrumb minimal -->
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <span class="text-black/70 dark:text-white/70 font-medium">POS</span>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Sesi Shift</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Sesi Shift Kasir</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Kelola pembukaan shift, mutasi kas laci, dan rekonsiliasi uang fisik (cash drawer)</p>
        </div>

        <!-- Toolbar Actions -->
        <div class="flex items-center gap-2 w-full sm:w-auto">
            @if(\App\Support\Context::hasPermission('pos.terminal'))
            <a href="{{ route('pos.terminal') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
                <span>Terminal POS</span>
            </a>
            @endif

            @if(\App\Support\Context::hasPermission('pos.terminal') || \App\Support\Context::hasPermission('pos.orders'))
            <button type="button" @click="showOpenModal = true" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Buka Shift Baru</span>
            </button>
            @endif
        </div>
    </header>

    <!-- Feedback Notification -->
    @if(session('success'))
    <div class="rounded-[12px] bg-[#34C759]/12 border border-[#34C759]/20 px-4 py-3 flex items-center gap-2.5 text-[13px] text-[#248A3D] dark:text-[#30D158]">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="rounded-[12px] bg-[#FF3B30]/12 border border-[#FF3B30]/20 px-4 py-3 flex items-center gap-2.5 text-[13px] text-[#C41E17] dark:text-[#FF453A]">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
        </svg>
        <span class="font-medium">{{ session('error') }}</span>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 2. ACTIVE SHIFT COCKPIT (Apple HIG Inset Material)    -->
    <!-- ===================================================== -->
    @if($activeShift)
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start sm:items-center gap-3.5">
            <div class="w-11 h-11 rounded-[10px] bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] animate-pulse"></span> Shift Aktif
                    </span>
                    <span class="text-[12px] text-black/45 dark:text-white/45">
                        Dibuka {{ $activeShift->opened_at->format('d/m/Y H:i') }} ({{ $activeShift->opened_at->diffForHumans() }})
                    </span>
                </div>
                <div class="text-[15px] font-semibold text-black dark:text-white">
                    {{ $activeShift->user->name ?? 'Kasir' }} · <span class="text-black/60 dark:text-white/60 font-normal">Modal Awal:</span> <span class="tabular-nums text-[#34C759] dark:text-[#30D158]">Rp {{ number_format($activeShift->opening_cash, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 self-end sm:self-auto">
            @if(\App\Support\Context::hasPermission('finance.cash_bank') || \App\Support\Context::hasPermission('pos.orders'))
            <button type="button" @click="openMovement('{{ $activeShift->id }}')" class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                </svg>
                <span>Kas Masuk/Keluar</span>
            </button>
            @endif
            @if(\App\Support\Context::hasPermission('pos.orders') || \App\Support\Context::hasPermission('pos.supervisor_pin'))
            <button type="button" @click="openCloseModal('{{ $activeShift->id }}', {{ $activeShift->opening_cash + $activeShift->total_cash_sales + $activeShift->total_cash_in - $activeShift->total_cash_out }})" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
                <span>Tutup Shift Ini</span>
            </button>
            @endif
        </div>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 3. DENSE DATA TABLE (DESKTOP & TABLET)                 -->
    <!-- ===================================================== -->
    <div class="hidden sm:block rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="px-4 py-3 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
            <h3 class="text-[13px] font-semibold text-black dark:text-white">Riwayat Sesi Shift</h3>
            <span class="text-[12px] text-black/45 dark:text-white/45">Semua data sesi tercatat</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10">
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Kasir / Outlet</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Waktu Buka / Tutup</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Modal Awal</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Penjualan Tunai</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Uang Fisik / Harapan</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Selisih Kas</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($shifts as $shift)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3">
                            <div class="font-medium text-black dark:text-white">{{ $shift->user->name ?? 'Kasir' }}</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45">{{ $shift->location->name ?? 'Outlet Utama' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-black/80 dark:text-white/80 tabular-nums">{{ $shift->opened_at->format('d/m/Y H:i') }}</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums">
                                {{ $shift->closed_at ? $shift->closed_at->format('d/m/Y H:i') : 'Masih Terbuka' }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums font-medium text-black/70 dark:text-white/70">
                            Rp {{ number_format($shift->opening_cash, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums font-semibold text-[#34C759] dark:text-[#30D158]">
                            Rp {{ number_format($shift->total_cash_sales, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums">
                            @if($shift->status === 'closed')
                                <div class="font-semibold text-black dark:text-white">Rp {{ number_format($shift->closing_cash_actual ?? 0, 0, ',', '.') }}</div>
                                <div class="text-[11px] text-black/40 dark:text-white/40">Harapan: Rp {{ number_format($shift->closing_cash_expected ?? 0, 0, ',', '.') }}</div>
                            @else
                                <span class="text-black/30 dark:text-white/30">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums font-semibold">
                            @if($shift->status === 'closed')
                                @php $diff = $shift->cash_difference ?? 0; @endphp
                                <span class="{{ $diff == 0 ? 'text-[#34C759] dark:text-[#30D158]' : ($diff > 0 ? 'text-[#007AFF]' : 'text-[#FF3B30]') }}">
                                    {{ $diff >= 0 ? '+' : '' }}Rp {{ number_format($diff, 0, ',', '.') }}
                                </span>
                            @else
                                <span class="text-black/30 dark:text-white/30">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($shift->status === 'open')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Terbuka
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-black/[0.06] dark:bg-white/[0.08] text-black/55 dark:text-white/55">
                                    Ditutup
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-black/40 dark:text-white/40">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-10 h-10 text-black/20 dark:text-white/20 mb-2" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-[14px] font-medium">Belum ada riwayat shift kasir tercatat.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($shifts->hasPages())
        <div class="p-3 border-t border-black/5 dark:border-white/10">
            {{ $shifts->links() }}
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 4. MOBILE GROUPED INSET LIST (Standar iOS List View)   -->
    <!-- ===================================================== -->
    <div class="sm:hidden rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
        @forelse($shifts as $shift)
        <div class="p-3.5 space-y-2.5 active:bg-black/[0.02] dark:active:bg-white/[0.03] transition-colors">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-[15px] font-medium text-black dark:text-white">{{ $shift->user->name ?? 'Kasir' }}</p>
                    <p class="text-[12px] text-black/45 dark:text-white/45">{{ $shift->location->name ?? 'Outlet Utama' }}</p>
                </div>
                @if($shift->status === 'open')
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Terbuka
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-black/[0.06] dark:bg-white/[0.08] text-black/55 dark:text-white/55">
                        Ditutup
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-2 text-[12px] bg-black/[0.02] dark:bg-white/[0.03] p-2.5 rounded-[10px]">
                <div>
                    <span class="text-black/40 dark:text-white/40 block">Modal Awal</span>
                    <span class="tabular-nums font-medium text-black dark:text-white">Rp {{ number_format($shift->opening_cash, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="text-black/40 dark:text-white/40 block">Penjualan Tunai</span>
                    <span class="tabular-nums font-semibold text-[#34C759] dark:text-[#30D158]">Rp {{ number_format($shift->total_cash_sales, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="text-black/40 dark:text-white/40 block">Buka</span>
                    <span class="tabular-nums text-black/60 dark:text-white/60">{{ $shift->opened_at->format('d/m H:i') }}</span>
                </div>
                <div>
                    <span class="text-black/40 dark:text-white/40 block">Selisih Kas</span>
                    @if($shift->status === 'closed')
                        @php $diff = $shift->cash_difference ?? 0; @endphp
                        <span class="tabular-nums font-semibold {{ $diff == 0 ? 'text-[#34C759]' : ($diff > 0 ? 'text-[#007AFF]' : 'text-[#FF3B30]') }}">
                            {{ $diff >= 0 ? '+' : '' }}Rp {{ number_format($diff, 0, ',', '.') }}
                        </span>
                    @else
                        <span class="text-black/40 dark:text-white/40">-</span>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="p-8 text-center text-black/40 dark:text-white/40 text-[13px]">
            Belum ada riwayat shift kasir tercatat.
        </div>
        @endforelse

        @if($shifts->hasPages())
        <div class="p-3 border-t border-black/5 dark:border-white/10">
            {{ $shifts->links() }}
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 5. MODAL BUKA SHIFT (Apple Sheet Presentation)        -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('pos.terminal') || \App\Support\Context::hasPermission('pos.orders'))
    <div x-show="showOpenModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-[2px] p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-full max-w-md rounded-[16px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.25)] p-5 sm:p-6 space-y-4"
            @click.away="showOpenModal = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                <h3 class="text-[17px] font-semibold text-black dark:text-white">Buka Shift Kasir Baru</h3>
                <button type="button" @click="showOpenModal = false" class="w-7 h-7 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:bg-black/10 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('pos.shifts.open') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Pilih Outlet</label>
                    <select name="location_id" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Modal Awal Kasir (Rp)</label>
                    <input type="number" name="opening_cash" value="100000" class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] font-semibold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Catatan (Opsional)</label>
                    <input type="text" name="notes" placeholder="Catatan pembukaan shift..." class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showOpenModal = false" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.06] dark:hover:bg-white/[0.08] transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                        Buka Shift
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 6. MODAL TUTUP SHIFT & REKONSILIASI (Apple Sheet)    -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('pos.orders') || \App\Support\Context::hasPermission('pos.supervisor_pin'))
    <div x-show="showCloseModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-[2px] p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-full max-w-md rounded-[16px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.25)] p-5 sm:p-6 space-y-4"
            @click.away="showCloseModal = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                <h3 class="text-[17px] font-semibold text-black dark:text-white">Tutup Shift &amp; Rekonsiliasi Kas</h3>
                <button type="button" @click="showCloseModal = false" class="w-7 h-7 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:bg-black/10 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form :action="'{{ url('/pos/shifts') }}/' + selectedShiftId + '/close'" method="POST" class="space-y-4">
                @csrf
                <div class="p-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 flex items-center justify-between">
                    <span class="text-[13px] text-black/60 dark:text-white/60">Total Harapan di Laci:</span>
                    <span class="font-bold text-[15px] tabular-nums text-[#34C759] dark:text-[#30D158]" x-text="'Rp ' + Number(expectedCash).toLocaleString('id-ID')"></span>
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Hitungan Uang Fisik Aktual (Rp)</label>
                    <input type="number" name="closing_cash_actual" x-model.number="actualCash" class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 flex items-center justify-between text-[13px]">
                    <span class="text-black/60 dark:text-white/60">Selisih Kas:</span>
                    <span :class="(actualCash - expectedCash) === 0 ? 'text-[#34C759] dark:text-[#30D158] font-bold' : ((actualCash - expectedCash) > 0 ? 'text-[#007AFF] font-bold' : 'text-[#FF3B30] font-bold')" 
                          class="tabular-nums"
                          x-text="'Rp ' + Number(actualCash - expectedCash).toLocaleString('id-ID')"></span>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showCloseModal = false" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.06] dark:hover:bg-white/[0.08] transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.97] active:opacity-80 transition-all">
                        Tutup &amp; Rekonsiliasi
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 7. MODAL MUTASI KAS (Apple Sheet Presentation)        -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('finance.cash_bank') || \App\Support\Context::hasPermission('pos.orders'))
    <div x-show="showMovementModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-[2px] p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-full max-w-md rounded-[16px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.25)] p-5 sm:p-6 space-y-4"
            @click.away="showMovementModal = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                <h3 class="text-[17px] font-semibold text-black dark:text-white">Catat Kas Masuk / Keluar</h3>
                <button type="button" @click="showMovementModal = false" class="w-7 h-7 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:bg-black/10 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form :action="'{{ url('/pos/shifts') }}/' + selectedShiftId + '/cash-movement'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Tipe Mutasi</label>
                    <select name="type" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="cash_in">Kas Masuk (Tambah Modal/Uang Pecahan)</option>
                        <option value="cash_out">Kas Keluar (Operasional/Beli Barang/Setor)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Nominal (Rp)</label>
                    <input type="number" name="amount" required class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Alasan / Keterangan</label>
                    <input type="text" name="reason" placeholder="Alasan kas masuk/keluar..." required class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showMovementModal = false" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.06] dark:hover:bg-white/[0.08] transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                        Simpan Mutasi
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
