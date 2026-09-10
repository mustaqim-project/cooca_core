@extends('layouts.app', [
    'title' => 'Log Pesan WhatsApp — ' . $business->name,
    'headerTitle' => 'Log Komunikasi WhatsApp',
    'headerSubtitle' => 'Audit trail seluruh pesan otomatis, struk kasir POS, dan blast promosi terkirim'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{ filterType: 'all' }">

    <!-- ========================================== -->
    <!-- 0. BREADCRUMB BAR (APPLE MINIMALIST)       -->
    <!-- ========================================== -->
    <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 py-0.5 whitespace-nowrap print:hidden" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors font-medium">Dashboard</a>
        <span>›</span>
        <a href="{{ route('whatsapp.index') }}" class="hover:text-[#007AFF] transition-colors font-medium">WhatsApp Gateway</a>
        <span>›</span>
        <span class="text-black/80 dark:text-white/80 font-medium">Log Pesan</span>
    </nav>

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR & SUB-TABS (macOS Sonoma Toolbar Style)     -->
    <!-- ===================================================== -->
    <header class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 transition-colors shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
        <div>
            <h1 class="text-[20px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight">Log Komunikasi WhatsApp</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Audit trail seluruh pesan otomatis, struk kasir POS, dan blast promosi terkirim</p>
        </div>

        <!-- Apple-Style Segmented Quick Filter -->
        <div class="inline-flex p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] border border-black/5 dark:border-white/5 overflow-x-auto text-[12px] font-medium w-full sm:w-auto">
            <button type="button" @click="filterType = 'all'"
                :class="filterType === 'all' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                class="h-7 px-3 rounded-[8px] transition-all whitespace-nowrap">
                Semua Log
            </button>
            <button type="button" @click="filterType = 'receipt'"
                :class="filterType === 'receipt' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                class="h-7 px-3 rounded-[8px] transition-all whitespace-nowrap flex items-center gap-1.5">
                <span>🧾 Struk POS</span>
            </button>
            <button type="button" @click="filterType = 'broadcast'"
                :class="filterType === 'broadcast' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                class="h-7 px-3 rounded-[8px] transition-all whitespace-nowrap flex items-center gap-1.5">
                <span>📢 Blast Promosi</span>
            </button>
            <button type="button" @click="filterType = 'test'"
                :class="filterType === 'test' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                class="h-7 px-3 rounded-[8px] transition-all whitespace-nowrap flex items-center gap-1.5">
                <span>🔧 Uji Coba</span>
            </button>
        </div>
    </header>

    <!-- Sub-Tabs Segmented Bar -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-2 sm:p-2.5 flex items-center justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
        <div class="inline-flex p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] border border-black/5 dark:border-white/5 w-full sm:w-auto overflow-x-auto text-[13px] font-medium">
            <a href="{{ route('whatsapp.index') }}"
                class="h-8 px-4 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                <i data-lucide="smartphone" class="w-4 h-4"></i>
                <span>Koneksi Gateway</span>
            </a>
            <a href="{{ route('whatsapp.broadcast.index') }}"
                class="h-8 px-4 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                <i data-lucide="megaphone" class="w-4 h-4"></i>
                <span>Blast Promosi</span>
            </a>
            <a href="{{ route('whatsapp.logs.index') }}"
                class="h-8 px-4 rounded-[9px] bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] flex items-center gap-2 whitespace-nowrap">
                <i data-lucide="history" class="w-4 h-4 text-[#007AFF]"></i>
                <span>Log Pesan</span>
            </a>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 2. LOGS DATA TABLE (Apple Dense Table)                -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] overflow-hidden transition-colors">
        <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
            <div>
                <h2 class="text-[14px] font-semibold text-black dark:text-white">Riwayat Komunikasi Keluar</h2>
                <p class="text-[12px] text-black/50 dark:text-white/50">Daftar transaksi pesan WhatsApp bot yang dikirim ke pelanggan</p>
            </div>
            <span class="text-[12px] text-black/40 dark:text-white/40 font-medium tabular-nums">Halaman {{ $logs->currentPage() }} dari {{ $logs->lastPage() }}</span>
        </div>

        @if($logs->isEmpty())
            <div class="text-center py-16 px-4 space-y-3">
                <div class="w-14 h-14 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] text-black/30 dark:text-white/30 flex items-center justify-center mx-auto border border-black/5 dark:border-white/10">
                    <i data-lucide="inbox" class="w-7 h-7"></i>
                </div>
                <div>
                    <h3 class="text-black dark:text-white font-semibold text-[15px]">Belum Ada Riwayat Pesan</h3>
                    <p class="text-black/50 dark:text-white/50 text-[13px] mt-1 max-w-sm mx-auto leading-relaxed">
                        Log pengiriman pesan akan otomatis tercatat setiap kali kasir mengirim struk POS atau menjalankan blast promosi.
                    </p>
                </div>
                <a href="{{ route('whatsapp.index') }}"
                    class="inline-flex items-center gap-1.5 mt-2 text-[13px] font-medium text-[#007AFF] hover:underline">
                    <span>Lihat Status Gateway &rarr;</span>
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px] min-w-[620px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 text-black/40 dark:text-white/40 uppercase tracking-wide text-[11px] font-semibold">
                            <th class="px-5 py-2.5">Penerima Pesan</th>
                            <th class="px-4 py-2.5">Tipe Komunikasi</th>
                            <th class="px-4 py-2.5">Isi Pesan</th>
                            <th class="px-4 py-2.5 text-center">Status</th>
                            <th class="px-5 py-2.5">Waktu Terkirim</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @foreach($logs as $log)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors"
                                x-show="filterType === 'all' || filterType === '{{ $log->type }}'">
                                <td class="px-5 py-3">
                                    <div class="font-semibold text-black dark:text-white">{{ $log->recipient_name }}</div>
                                    <div class="text-black/50 dark:text-white/50 tabular-nums text-[12px]">{{ $log->recipient_phone }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $typeBadge = match($log->type) {
                                            'receipt'   => 'bg-[#30B0C7]/12 text-[#227D8E] dark:text-[#40C8E0]',
                                            'broadcast' => 'bg-[#AF52DE]/12 text-[#7C3AA6] dark:text-[#BF5AF2]',
                                            'test'      => 'bg-[#007AFF]/12 text-[#0062CC] dark:text-[#0A84FF]',
                                            default     => 'bg-black/6 dark:bg-white/8 text-black/60 dark:text-white/60',
                                        };
                                        $typeLabel = match($log->type) {
                                            'receipt'   => 'Struk POS',
                                            'broadcast' => 'Blast Promosi',
                                            'test'      => 'Uji Tes',
                                            default     => ucfirst($log->type),
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $typeBadge }}">
                                        {{ $typeLabel }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 max-w-xs">
                                    <div class="text-black/80 dark:text-white/80 truncate text-[12px]" title="{{ $log->message }}">
                                        {{ Str::limit($log->message, 60) }}
                                    </div>
                                    @if($log->error_message)
                                        <div class="text-[#FF3B30] dark:text-[#FF453A] text-[11px] mt-0.5 truncate font-medium">
                                            ⚠ {{ $log->error_message }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($log->status === 'sent')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                            <i data-lucide="check" class="w-3 h-3"></i>
                                            <span>Terkirim</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                            <i data-lucide="x" class="w-3 h-3"></i>
                                            <span>Gagal</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-black/50 dark:text-white/50 tabular-nums text-[12px] whitespace-nowrap">
                                    {{ $log->created_at->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
                <div class="p-4 border-t border-black/5 dark:border-white/10">
                    {{ $logs->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
