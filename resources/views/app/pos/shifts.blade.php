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
    showDetailModal: false,
    selectedShiftId: null,
    selectedShiftData: null,
    actualCash: 0,
    expectedCash: 0,
    cashierNotes: '',
    notes: '',
    isPrinting: false,
    isSubmitting: false,
    
    // Denominations for Opening
    openDenoms: {
        '100000': 0, '50000': 0, '20000': 0, '10000': 0,
        '5000': 0, '2000': 0, '1000': 0, 'coins': 0
    },
    openUseDenoms: false,
    openManualCash: 100000,
    
    // Denominations for Closing
    closeDenoms: {
        '100000': 0, '50000': 0, '20000': 0, '10000': 0,
        '5000': 0, '2000': 0, '1000': 0, 'coins': 0
    },
    closeUseDenoms: true,

    get openCalculatedCash() {
        if (!this.openUseDenoms) return this.openManualCash;
        return (this.openDenoms['100000'] * 100000) +
               (this.openDenoms['50000'] * 50000) +
               (this.openDenoms['20000'] * 20000) +
               (this.openDenoms['10000'] * 10000) +
               (this.openDenoms['5000'] * 5000) +
               (this.openDenoms['2000'] * 2000) +
               (this.openDenoms['1000'] * 1000) +
               (parseInt(this.openDenoms['coins']) || 0);
    },

    get closeCalculatedCash() {
        if (!this.closeUseDenoms) return this.actualCash;
        return (this.closeDenoms['100000'] * 100000) +
               (this.closeDenoms['50000'] * 50000) +
               (this.closeDenoms['20000'] * 20000) +
               (this.closeDenoms['10000'] * 10000) +
               (this.closeDenoms['5000'] * 5000) +
               (this.closeDenoms['2000'] * 2000) +
               (this.closeDenoms['1000'] * 1000) +
               (parseInt(this.closeDenoms['coins']) || 0);
    },

    openCloseModal(shiftId, expected) {
        this.selectedShiftId = shiftId;
        this.expectedCash = expected;
        this.actualCash = 0;
        this.cashierNotes = '';
        this.closeDenoms = {
            '100000': 0, '50000': 0, '20000': 0, '10000': 0,
            '5000': 0, '2000': 0, '1000': 0, 'coins': 0
        };
        this.closeUseDenoms = true;
        this.showCloseModal = true;
    },

    openMovement(shiftId) {
        this.selectedShiftId = shiftId;
        this.showMovementModal = true;
    },

    viewDetail(shift) {
        this.selectedShiftData = shift;
        this.showDetailModal = true;
    },

    async printShiftReport(shiftId) {
        if (this.isPrinting) return;
        this.isPrinting = true;
        try {
            const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
            const res = await fetch('{{ url('/pos/shifts') }}/' + shiftId + '/print', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                }
            });
            const data = await res.json();
            if (data.success) {
                if (window.AppAlert) {
                    window.AppAlert.success(data.message || 'Struk laporan shift berhasil dicetak.');
                }
            } else {
                if (window.AppAlert) {
                    window.AppAlert.error(data.message || 'Gagal mencetak laporan shift.');
                }
            }
        } catch (e) {
            console.error(e);
            if (window.AppAlert) {
                window.AppAlert.error('Terjadi kesalahan koneksi printer.');
            }
        } finally {
            this.isPrinting = false;
        }
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
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-black/30 dark:text-white/30"></i>
                <span class="text-black/70 dark:text-white/70 font-medium">POS</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-black/30 dark:text-white/30"></i>
                <span class="text-black dark:text-white font-medium">Sesi Shift</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Sesi Shift Kasir</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Multi-kasir, multi-terminal POS, penghitungan fisik (blind cash count), dan laporan termal ESC/POS</p>
        </div>

        <!-- Toolbar Actions -->
        <div class="flex items-center gap-2 w-full sm:w-auto">
            @if(\App\Support\Context::hasPermission('pos.terminal'))
            <a href="{{ route('pos.terminal') }}" class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="layout-grid" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                <span>Terminal POS</span>
            </a>
            @endif

            @if(\App\Support\Context::hasPermission('pos.terminal') || \App\Support\Context::hasPermission('pos.orders'))
            <button type="button" @click="showOpenModal = true" class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Buka Shift Baru</span>
            </button>
            @endif
        </div>
    </header>

    <!-- Feedback Notification -->
    @if(session('success'))
    <div class="rounded-[12px] bg-[#34C759]/12 border border-[#34C759]/20 px-4 py-3 flex items-center gap-2.5 text-[13px] text-[#248A3D] dark:text-[#30D158]">
        <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="rounded-[12px] bg-[#FF3B30]/12 border border-[#FF3B30]/20 px-4 py-3 flex items-center gap-2.5 text-[13px] text-[#C41E17] dark:text-[#FF453A]">
        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
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
                <i data-lucide="lock" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] animate-pulse"></span> Shift Aktif
                    </span>
                    @if($activeShift->register)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70">
                        <i data-lucide="monitor" class="w-3 h-3"></i> {{ $activeShift->register->name }}
                    </span>
                    @endif
                    <span class="text-[12px] text-black/45 dark:text-white/45">
                        Dibuka {{ $activeShift->opened_at->format('d/m/Y H:i') }} ({{ $activeShift->opened_at->diffForHumans() }})
                    </span>
                </div>
                <div class="text-[15px] font-semibold text-black dark:text-white">
                    {{ $activeShift->user->name ?? 'Kasir' }} · <span class="text-black/60 dark:text-white/60 font-normal">Modal Awal:</span> <span class="tabular-nums text-[#34C759] dark:text-[#30D158]">Rp {{ number_format((float) $activeShift->opening_cash, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 self-stretch sm:self-auto flex-wrap">
            <button type="button" @click="printShiftReport('{{ $activeShift->id }}')" :disabled="isPrinting" class="flex-1 sm:flex-none min-h-[44px] sm:min-h-0 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="printer" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                <span>Cetak Sementara</span>
            </button>
            @if(\App\Support\Context::hasPermission('finance.cash_bank') || \App\Support\Context::hasPermission('pos.orders'))
            <button type="button" @click="openMovement('{{ $activeShift->id }}')" class="flex-1 sm:flex-none min-h-[44px] sm:min-h-0 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="arrow-left-right" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                <span>Kas Masuk/Keluar</span>
            </button>
            @endif
            @if(\App\Support\Context::hasPermission('pos.orders') || \App\Support\Context::hasPermission('pos.supervisor_pin'))
            <button type="button" @click="openCloseModal('{{ $activeShift->id }}', {{ (float) ($activeShift->opening_cash + $activeShift->total_cash_sales + $activeShift->total_cash_in - $activeShift->total_cash_out) }})" class="w-full sm:w-auto min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                <span>Tutup Shift &amp; Rekonsiliasi</span>
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
            <span class="text-[12px] text-black/45 dark:text-white/45">Semua data sesi &amp; rekonsiliasi kas tercatat</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10">
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Kasir / Terminal</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Waktu Buka / Tutup</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Modal Awal</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Penjualan Tunai</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Fisik / Harapan</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Selisih Kas</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Status</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($shifts as $shift)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3">
                            <div class="font-medium text-black dark:text-white">{{ $shift->user->name ?? 'Kasir' }}</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 flex items-center gap-1.5 mt-0.5">
                                <span>{{ $shift->location->name ?? 'Outlet Utama' }}</span>
                                @if($shift->register)
                                <span>·</span>
                                <span class="font-medium text-black/60 dark:text-white/60">{{ $shift->register->name }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-black/80 dark:text-white/80 tabular-nums">{{ $shift->opened_at->format('d/m/Y H:i') }}</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums">
                                {{ $shift->closed_at ? $shift->closed_at->format('d/m/Y H:i') : 'Masih Terbuka' }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums font-medium text-black/70 dark:text-white/70">
                            Rp {{ number_format((float) $shift->opening_cash, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums font-semibold text-[#34C759] dark:text-[#30D158]">
                            Rp {{ number_format((float) $shift->total_cash_sales, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums">
                            @if($shift->status === 'closed')
                                <div class="font-semibold text-black dark:text-white">Rp {{ number_format((float) ($shift->closing_cash_actual ?? 0), 0, ',', '.') }}</div>
                                <div class="text-[11px] text-black/40 dark:text-white/40">Harapan: Rp {{ number_format((float) ($shift->closing_cash_expected ?? 0), 0, ',', '.') }}</div>
                            @else
                                <span class="text-black/30 dark:text-white/30">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums font-semibold">
                            @if($shift->status === 'closed')
                                @php $diff = (float) ($shift->cash_difference ?? 0); @endphp
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
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" @click="printShiftReport('{{ $shift->id }}')" title="Cetak Struk Shift (ESC/POS)" class="p-1.5 rounded-[8px] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] text-black/60 dark:text-white/60 transition-colors">
                                    <i data-lucide="printer" class="w-4 h-4"></i>
                                </button>
                                @if($shift->status === 'open')
                                <button type="button" @click="openCloseModal('{{ $shift->id }}', {{ (float) ($shift->opening_cash + $shift->total_cash_sales + $shift->total_cash_in - $shift->total_cash_out) }})" title="Tutup Shift" class="p-1.5 rounded-[8px] hover:bg-[#FF3B30]/10 text-[#FF3B30] transition-colors">
                                    <i data-lucide="lock" class="w-4 h-4"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-black/40 dark:text-white/40">
                            <div class="flex flex-col items-center justify-center">
                                <i data-lucide="clock" class="w-10 h-10 text-black/20 dark:text-white/20 mb-2"></i>
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
                    <p class="text-[12px] text-black/45 dark:text-white/45">{{ $shift->location->name ?? 'Outlet Utama' }} @if($shift->register) · {{ $shift->register->name }} @endif</p>
                </div>
                <div class="flex items-center gap-1.5">
                    @if($shift->status === 'open')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Terbuka
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-black/[0.06] dark:bg-white/[0.08] text-black/55 dark:text-white/55">
                            Ditutup
                        </span>
                    @endif
                    <button type="button" @click="printShiftReport('{{ $shift->id }}')" class="p-1 rounded-[6px] bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60">
                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 text-[12px] bg-black/[0.02] dark:bg-white/[0.03] p-2.5 rounded-[10px]">
                <div>
                    <span class="text-black/40 dark:text-white/40 block">Modal Awal</span>
                    <span class="tabular-nums font-medium text-black dark:text-white">Rp {{ number_format((float) $shift->opening_cash, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="text-black/40 dark:text-white/40 block">Penjualan Tunai</span>
                    <span class="tabular-nums font-semibold text-[#34C759] dark:text-[#30D158]">Rp {{ number_format((float) $shift->total_cash_sales, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="text-black/40 dark:text-white/40 block">Buka</span>
                    <span class="tabular-nums text-black/60 dark:text-white/60">{{ $shift->opened_at->format('d/m H:i') }}</span>
                </div>
                <div>
                    <span class="text-black/40 dark:text-white/40 block">Selisih Kas</span>
                    @if($shift->status === 'closed')
                        @php $diff = (float) ($shift->cash_difference ?? 0); @endphp
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
    <!-- 5. MODAL BUKA SHIFT DENGAN PECAHAN UANG (Apple Sheet) -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('pos.terminal') || \App\Support\Context::hasPermission('pos.orders'))
    <div x-show="showOpenModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-md p-4 overflow-y-auto"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-full max-w-5xl xl:max-w-6xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/15 shadow-[0_25px_60px_rgba(0,0,0,0.35)] p-6 sm:p-7 space-y-6 my-8 max-h-[92vh] overflow-y-auto"
            @click.away="showOpenModal = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center justify-between border-b border-black/10 dark:border-white/10 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center font-bold">
                        <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[19px] font-bold text-black dark:text-white tracking-tight">Buka Sesi Shift Kasir Baru</h3>
                        <p class="text-[13px] text-black/50 dark:text-white/50">Tentukan terminal kerja dan input modal kas awal laci fisik</p>
                    </div>
                </div>
                <button type="button" @click="showOpenModal = false" class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:bg-black/10 hover:text-black dark:hover:text-white flex items-center justify-center transition-colors">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('pos.shifts.open') }}" method="POST" @submit="isSubmitting = true" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Kolom Kiri: Form Input & Denominasi (7 Cols) -->
                    <div class="lg:col-span-7 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Pilih Outlet / Cabang *</label>
                                <select name="location_id" class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Terminal / Register</label>
                                <select name="pos_register_id" class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    <option value="">-- Terminal Utama (Default) --</option>
                                    @foreach($registers as $reg)
                                        <option value="{{ $reg->id }}">{{ $reg->name }} ({{ $reg->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Toggle Mode: Direct Input vs Denominations Calculator -->
                        <div class="flex items-center justify-between p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 text-[13px]">
                            <span class="text-black/80 dark:text-white/80 font-medium">Hitung Rinci Pecahan Lembar &amp; Koin Fisik</span>
                            <button type="button" @click="openUseDenoms = !openUseDenoms" class="text-[#007AFF] font-semibold hover:underline">
                                <span x-text="openUseDenoms ? 'Gunakan Input Nominal Sederhana' : 'Buka Rincian Pecahan'"></span>
                            </button>
                        </div>

                        <!-- Denominations Grid -->
                        <div x-show="openUseDenoms" class="space-y-3 p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                            <div class="flex items-center justify-between">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Jumlah Fisik Pecahan Laci Kasir</p>
                                <span class="text-[11px] text-black/40 dark:text-white/40">Otomatis Terakumulasi</span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-[12px]">
                                <div>
                                    <label class="block text-[11px] font-medium text-black/50 dark:text-white/50 mb-1">Rp 100.000</label>
                                    <input type="number" min="0" name="opening_denominations[100000]" x-model.number="openDenoms['100000']" class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[8px] px-2.5 text-[13px] tabular-nums font-semibold text-black dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-black/50 dark:text-white/50 mb-1">Rp 50.000</label>
                                    <input type="number" min="0" name="opening_denominations[50000]" x-model.number="openDenoms['50000']" class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[8px] px-2.5 text-[13px] tabular-nums font-semibold text-black dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-black/50 dark:text-white/50 mb-1">Rp 20.000</label>
                                    <input type="number" min="0" name="opening_denominations[20000]" x-model.number="openDenoms['20000']" class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[8px] px-2.5 text-[13px] tabular-nums font-semibold text-black dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-black/50 dark:text-white/50 mb-1">Rp 10.000</label>
                                    <input type="number" min="0" name="opening_denominations[10000]" x-model.number="openDenoms['10000']" class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[8px] px-2.5 text-[13px] tabular-nums font-semibold text-black dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-black/50 dark:text-white/50 mb-1">Rp 5.000</label>
                                    <input type="number" min="0" name="opening_denominations[5000]" x-model.number="openDenoms['5000']" class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[8px] px-2.5 text-[13px] tabular-nums font-semibold text-black dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-black/50 dark:text-white/50 mb-1">Rp 2.000</label>
                                    <input type="number" min="0" name="opening_denominations[2000]" x-model.number="openDenoms['2000']" class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[8px] px-2.5 text-[13px] tabular-nums font-semibold text-black dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-black/50 dark:text-white/50 mb-1">Rp 1.000</label>
                                    <input type="number" min="0" name="opening_denominations[1000]" x-model.number="openDenoms['1000']" class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[8px] px-2.5 text-[13px] tabular-nums font-semibold text-black dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-black/50 dark:text-white/50 mb-1">Total Koin</label>
                                    <input type="number" min="0" name="opening_denominations[coins]" x-model.number="openDenoms['coins']" placeholder="Rp" class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[8px] px-2.5 text-[13px] tabular-nums font-semibold text-black dark:text-white">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Total Modal Awal Laci (Rp) *</label>
                            <input type="number" name="opening_cash" :value="openCalculatedCash" @input="openManualCash = $event.target.value" :readonly="openUseDenoms" required class="w-full h-12 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[12px] px-4 text-[18px] font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>

                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Catatan Pembukaan (Opsional)</label>
                            <input type="text" name="notes" placeholder="Contoh: Tambahan modal uang kecil Rp 50.000..." class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                    </div>

                    <!-- Kolom Kanan: Bento Information & Guidelines (5 Cols) -->
                    <div class="lg:col-span-5 space-y-4">
                        <div class="p-4 rounded-[18px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 space-y-3">
                            <div class="flex items-center gap-2.5 text-[#007AFF]">
                                <i data-lucide="user-check" class="w-5 h-5"></i>
                                <h4 class="font-bold text-[14px] text-black dark:text-white">Informasi Kasir</h4>
                            </div>
                            <div class="space-y-2 text-[12px] divide-y divide-black/5 dark:divide-white/5">
                                <div class="flex items-center justify-between pt-1">
                                    <span class="text-black/50 dark:text-white/50">Petugas Kasir</span>
                                    <span class="font-semibold text-black dark:text-white">{{ auth()->user()->name ?? 'Kasir Aktif' }}</span>
                                </div>
                                <div class="flex items-center justify-between pt-2">
                                    <span class="text-black/50 dark:text-white/50">Waktu Mulai</span>
                                    <span class="tabular-nums font-medium text-black dark:text-white">{{ now()->format('d/m/Y H:i') }} WIB</span>
                                </div>
                                <div class="flex items-center justify-between pt-2">
                                    <span class="text-black/50 dark:text-white/50">Status Sistem</span>
                                    <span class="inline-flex items-center gap-1 text-[#34C759] font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Siap Bertransaksi
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 rounded-[18px] bg-gradient-to-br from-[#007AFF]/5 to-[#007AFF]/10 border border-[#007AFF]/15 space-y-2.5">
                            <div class="flex items-center gap-2 text-[#007AFF] font-bold text-[13px]">
                                <i data-lucide="shield-alert" class="w-4 h-4"></i>
                                <span>SOP Kasir &amp; Anti-Fraud</span>
                            </div>
                            <ul class="space-y-1.5 text-[12px] text-black/70 dark:text-white/70 list-disc list-inside leading-relaxed">
                                <li>Pastikan uang fisik di laci telah dihitung teliti sebelum shift dimulai.</li>
                                <li>Semua transaksi penjualan wajib tercatat melalui terminal kasir.</li>
                                <li>Setiap penambahan atau pengambilan kas di tengah jam operasional wajib dicatat pada menu <em>Mutasi Kas</em>.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-black/10 dark:border-white/10">
                    <button type="button" @click="showOpenModal = false" class="min-h-[44px] px-5 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.06] dark:hover:bg-white/[0.08] transition-colors">
                        Batal
                    </button>
                    <button type="submit" :disabled="isSubmitting" class="min-h-[44px] px-6 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all shadow-[0_2px_8px_rgba(0,122,255,0.35)] flex items-center gap-2">
                        <template x-if="isSubmitting">
                            <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                        </template>
                        <span x-text="isSubmitting ? 'Membuka Shift...' : 'Konfirmasi Buka Shift'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 6. MODAL TUTUP SHIFT & BLIND CASH COUNT (Apple Sheet) -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('pos.orders') || \App\Support\Context::hasPermission('pos.supervisor_pin'))
    <div x-show="showCloseModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-md p-4 overflow-y-auto"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-full max-w-5xl xl:max-w-6xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/15 shadow-[0_25px_60px_rgba(0,0,0,0.35)] p-6 sm:p-7 space-y-6 my-8 max-h-[92vh] overflow-y-auto"
            @click.away="showCloseModal = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center justify-between border-b border-black/10 dark:border-white/10 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] bg-[#FF3B30]/15 text-[#FF3B30] flex items-center justify-center font-bold">
                        <i data-lucide="lock" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[19px] font-bold text-black dark:text-white tracking-tight">Tutup Shift &amp; Rekonsiliasi Kas</h3>
                        <p class="text-[13px] text-black/50 dark:text-white/50">Penghitungan fisik uang laci tanpa kebocoran estimasi sistem (Blind Cash Count)</p>
                    </div>
                </div>
                <button type="button" @click="showCloseModal = false" class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:bg-black/10 hover:text-black dark:hover:text-white flex items-center justify-center transition-colors">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            @php
                $isSupervisor = \App\Support\Context::isOwner() || auth()->user()?->hasRole('owner') || auth()->user()?->can('pos.supervisor_pin');
            @endphp

            <form :action="'{{ url('/pos/shifts') }}/' + selectedShiftId + '/close'" method="POST" @submit="isSubmitting = true" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Kolom Kiri: Input Fisik Pecahan Kas (7 Cols) -->
                    <div class="lg:col-span-7 space-y-4">
                        <div class="flex items-center justify-between p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 text-[13px]">
                            <span class="text-black/80 dark:text-white/80 font-medium">Hitung Fisik Rinci Pecahan Laci</span>
                            <button type="button" @click="closeUseDenoms = !closeUseDenoms" class="text-[#007AFF] font-semibold hover:underline">
                                <span x-text="closeUseDenoms ? 'Gunakan Input Total Langsung' : 'Rincikan Lembar Pecahan'"></span>
                            </button>
                        </div>

                        <!-- Pecahan Uang Fisik Kasir -->
                        <div x-show="closeUseDenoms" class="space-y-3 p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Jumlah Lembar / Keping Fisik Aktual</p>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-[12px]">
                                <div>
                                    <label class="block text-[11px] font-medium text-black/50 dark:text-white/50 mb-1">Rp 100.000</label>
                                    <input type="number" min="0" name="closing_denominations[100000]" x-model.number="closeDenoms['100000']" class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[8px] px-2.5 text-[13px] tabular-nums font-semibold text-black dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-black/50 dark:text-white/50 mb-1">Rp 50.000</label>
                                    <input type="number" min="0" name="closing_denominations[50000]" x-model.number="closeDenoms['50000']" class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[8px] px-2.5 text-[13px] tabular-nums font-semibold text-black dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-black/50 dark:text-white/50 mb-1">Rp 20.000</label>
                                    <input type="number" min="0" name="closing_denominations[20000]" x-model.number="closeDenoms['20000']" class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[8px] px-2.5 text-[13px] tabular-nums font-semibold text-black dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-black/50 dark:text-white/50 mb-1">Rp 10.000</label>
                                    <input type="number" min="0" name="closing_denominations[10000]" x-model.number="closeDenoms['10000']" class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[8px] px-2.5 text-[13px] tabular-nums font-semibold text-black dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-black/50 dark:text-white/50 mb-1">Rp 5.000</label>
                                    <input type="number" min="0" name="closing_denominations[5000]" x-model.number="closeDenoms['5000']" class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[8px] px-2.5 text-[13px] tabular-nums font-semibold text-black dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-black/50 dark:text-white/50 mb-1">Rp 2.000</label>
                                    <input type="number" min="0" name="closing_denominations[2000]" x-model.number="closeDenoms['2000']" class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[8px] px-2.5 text-[13px] tabular-nums font-semibold text-black dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-black/50 dark:text-white/50 mb-1">Rp 1.000</label>
                                    <input type="number" min="0" name="closing_denominations[1000]" x-model.number="closeDenoms['1000']" class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[8px] px-2.5 text-[13px] tabular-nums font-semibold text-black dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-black/50 dark:text-white/50 mb-1">Total Koin</label>
                                    <input type="number" min="0" name="closing_denominations[coins]" x-model.number="closeDenoms['coins']" placeholder="Rp" class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[8px] px-2.5 text-[13px] tabular-nums font-semibold text-black dark:text-white">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Hitungan Fisik Aktual Kasir (Rp) *</label>
                            <input type="number" name="closing_cash_actual" :value="closeCalculatedCash" @input="actualCash = $event.target.value" :readonly="closeUseDenoms" required class="w-full h-12 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[12px] px-4 text-[18px] font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>

                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Penjelasan / Catatan Kasir</label>
                            <input type="text" name="cashier_notes" x-model="cashierNotes" placeholder="Catatan jika ada selisih uang atau kondisi penutupan shift..." class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                    </div>

                    <!-- Kolom Kanan: Guardrail & Supervisor View (5 Cols) -->
                    <div class="lg:col-span-5 space-y-4">
                        @if($isSupervisor)
                        <div class="p-4 rounded-[18px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 space-y-3">
                            <div class="flex items-center gap-2 text-[#FF9500] font-bold text-[13px]">
                                <i data-lucide="key" class="w-4 h-4"></i>
                                <span>Rekonsiliasi Supervisor (Privileged View)</span>
                            </div>
                            <div class="space-y-2 text-[12px] divide-y divide-black/5 dark:divide-white/5">
                                <div class="flex items-center justify-between pt-1">
                                    <span class="text-black/60 dark:text-white/60">Harapan Sistem:</span>
                                    <span class="font-bold tabular-nums text-[#34C759] dark:text-[#30D158]" x-text="'Rp ' + Number(expectedCash).toLocaleString('id-ID')"></span>
                                </div>
                                <div class="flex items-center justify-between pt-2">
                                    <span class="text-black/60 dark:text-white/60">Fisik Kasir:</span>
                                    <span class="font-bold tabular-nums text-black dark:text-white" x-text="'Rp ' + Number(closeCalculatedCash).toLocaleString('id-ID')"></span>
                                </div>
                                <div class="flex items-center justify-between pt-2 text-[13px]">
                                    <span class="font-semibold text-black dark:text-white">Selisih Kas:</span>
                                    <span :class="(closeCalculatedCash - expectedCash) === 0 ? 'text-[#34C759] dark:text-[#30D158] font-bold' : ((closeCalculatedCash - expectedCash) > 0 ? 'text-[#007AFF] font-bold' : 'text-[#FF3B30] font-bold')" 
                                          class="tabular-nums font-bold"
                                          x-text="((closeCalculatedCash - expectedCash) >= 0 ? '+' : '') + 'Rp ' + Number(closeCalculatedCash - expectedCash).toLocaleString('id-ID')"></span>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="p-4 rounded-[18px] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 border border-[#007AFF]/20 space-y-2.5">
                            <div class="flex items-center gap-2 text-[#007AFF] font-bold text-[13px]">
                                <i data-lucide="shield-check" class="w-4 h-4"></i>
                                <span>Strict Blind Cash Count Aktif</span>
                            </div>
                            <p class="text-[12px] text-black/70 dark:text-white/70 leading-relaxed">
                                Anda wajib menghitung dan menginput seluruh uang fisik di laci secara mandiri tanpa mengetahui saldo ekspektasi sistem. Sistem akan merekonsiliasi selisih secara otomatis ke laporan Owner.
                            </p>
                        </div>
                        @endif

                        <div class="p-4 rounded-[18px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 space-y-2">
                            <h4 class="font-bold text-[13px] text-black dark:text-white flex items-center gap-1.5">
                                <i data-lucide="info" class="w-4 h-4 text-black/50 dark:text-white/50"></i>
                                <span>SOP Penutupan Kasir</span>
                            </h4>
                            <ul class="space-y-1 text-[12px] text-black/60 dark:text-white/60 list-disc list-inside leading-relaxed">
                                <li>Pisahkan uang modal awal dan uang hasil penjualan harian.</li>
                                <li>Cetak struk laporan shift penutupan setelah formulir dikirim.</li>
                                <li>Serahkan laci uang dan dokumen fisik ke supervisor yang bertugas.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-black/10 dark:border-white/10">
                    <button type="button" @click="showCloseModal = false" class="min-h-[44px] px-5 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.06] dark:hover:bg-white/[0.08] transition-colors">
                        Batal
                    </button>
                    <button type="submit" :disabled="isSubmitting" class="min-h-[44px] px-6 rounded-[12px] text-[13px] font-semibold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.97] transition-all shadow-[0_2px_8px_rgba(255,59,48,0.35)] flex items-center gap-2">
                        <template x-if="isSubmitting">
                            <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                        </template>
                        <span x-text="isSubmitting ? 'Merekonsiliasi...' : 'Tutup Shift &amp; Rekonsiliasi'"></span>
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
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-md p-4 overflow-y-auto"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-full max-w-3xl lg:max-w-4xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/15 shadow-[0_25px_60px_rgba(0,0,0,0.35)] p-6 sm:p-7 space-y-6 my-8 max-h-[92vh] overflow-y-auto"
            @click.away="showMovementModal = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center justify-between border-b border-black/10 dark:border-white/10 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center font-bold">
                        <i data-lucide="arrow-left-right" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[19px] font-bold text-black dark:text-white tracking-tight">Catat Mutasi Kas Masuk / Keluar</h3>
                        <p class="text-[13px] text-black/50 dark:text-white/50">Pencatatan uang masuk/keluar laci operasional dengan audit trail terverifikasi</p>
                    </div>
                </div>
                <button type="button" @click="showMovementModal = false" class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:bg-black/10 hover:text-black dark:hover:text-white flex items-center justify-center transition-colors">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form :action="'{{ url('/pos/shifts') }}/' + selectedShiftId + '/cash-movement'" method="POST" @submit="isSubmitting = true" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <div class="lg:col-span-7 space-y-4">
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Tipe Mutasi Kas *</label>
                            <select name="type" class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                <option value="cash_in">Kas Masuk (Tambah Modal / Uang Kembalian Pecahan Kecil)</option>
                                <option value="cash_out">Kas Keluar (Operasional Harian / Pengeluaran Toko / Setor Owner)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Nominal Kas (Rp) *</label>
                            <input type="number" name="amount" min="1" required placeholder="0" class="w-full h-12 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[12px] px-4 text-[18px] font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Alasan / Keterangan Mutasi *</label>
                            <input type="text" name="reason" placeholder="Contoh: Beli es batu darurat, setor tunai kasir..." required class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                    </div>

                    <div class="lg:col-span-5 space-y-4">
                        <div class="p-4 rounded-[18px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 space-y-2">
                            <div class="flex items-center gap-2 text-[#007AFF] font-bold text-[13px]">
                                <i data-lucide="file-text" class="w-4 h-4"></i>
                                <span>Ketentuan Mutasi Kas</span>
                            </div>
                            <p class="text-[12px] text-black/70 dark:text-white/70 leading-relaxed">
                                Mutasi kas langsung mempengaruhi saldo akhir yang diharapkan pada saat shift ditutup. Pastikan bukti nota/struk fisik disimpan untuk diverifikasi supervisor.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-black/10 dark:border-white/10">
                    <button type="button" @click="showMovementModal = false" class="min-h-[44px] px-5 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.06] dark:hover:bg-white/[0.08] transition-colors">
                        Batal
                    </button>
                    <button type="submit" :disabled="isSubmitting" class="min-h-[44px] px-6 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all shadow-[0_2px_8px_rgba(0,122,255,0.35)] flex items-center gap-2">
                        <template x-if="isSubmitting">
                            <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                        </template>
                        <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan Mutasi Kas'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 8. MODAL DETAIL SHIFT (Apple Sheet Presentation)      -->
    <!-- ===================================================== -->
    <div x-show="showDetailModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-md p-4 overflow-y-auto"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-full max-w-4xl lg:max-w-5xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/15 shadow-[0_25px_60px_rgba(0,0,0,0.35)] p-6 sm:p-7 space-y-6 my-8 max-h-[92vh] overflow-y-auto"
            @click.away="showDetailModal = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center justify-between border-b border-black/10 dark:border-white/10 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center font-bold">
                        <i data-lucide="file-check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[19px] font-bold text-black dark:text-white tracking-tight">Rincian Rekonsiliasi Sesi Shift</h3>
                        <p class="text-[13px] text-black/50 dark:text-white/50">Audit komprehensif kas masuk, penjualan tunai, dan mutasi saldo laci</p>
                    </div>
                </div>
                <button type="button" @click="showDetailModal = false" class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:bg-black/10 hover:text-black dark:hover:text-white flex items-center justify-center transition-colors">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <template x-if="selectedShiftData">
                <div class="space-y-6">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
                        <div class="p-4 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5">
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 block">Modal Awal</span>
                            <span class="text-[18px] font-bold tabular-nums text-black dark:text-white mt-1 block" x-text="'Rp ' + Number(selectedShiftData.opening_cash || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="p-4 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5">
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-[#34C759] block">Penjualan Tunai</span>
                            <span class="text-[18px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] mt-1 block" x-text="'Rp ' + Number(selectedShiftData.total_cash_sales || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="p-4 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5">
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 block">Kas Masuk / Keluar</span>
                            <span class="text-[14px] font-semibold tabular-nums text-black dark:text-white mt-1 block" x-text="'+Rp ' + Number(selectedShiftData.total_cash_in || 0).toLocaleString('id-ID') + ' / -Rp ' + Number(selectedShiftData.total_cash_out || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="p-4 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5">
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 block">Selisih Kas</span>
                            <span class="text-[18px] font-bold tabular-nums mt-1 block"
                                  :class="Number(selectedShiftData.cash_difference || 0) === 0 ? 'text-[#34C759]' : (Number(selectedShiftData.cash_difference || 0) > 0 ? 'text-[#007AFF]' : 'text-[#FF3B30]')"
                                  x-text="(Number(selectedShiftData.cash_difference || 0) >= 0 ? '+' : '') + 'Rp ' + Number(selectedShiftData.cash_difference || 0).toLocaleString('id-ID')"></span>
                        </div>
                    </div>

                    <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2">
                        <h4 class="font-bold text-[13px] text-black dark:text-white">Catatan Sesi Kasir</h4>
                        <p class="text-[13px] text-black/70 dark:text-white/70 italic" x-text="selectedShiftData.cashier_notes || selectedShiftData.notes || 'Tidak ada catatan khusus.'"></p>
                    </div>
                </div>
            </template>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-black/10 dark:border-white/10">
                <button type="button" @click="showDetailModal = false" class="min-h-[44px] px-6 rounded-[12px] text-[13px] font-semibold text-black dark:text-white bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>
@endsection
