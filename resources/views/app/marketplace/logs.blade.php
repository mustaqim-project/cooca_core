@extends('layouts.app', [
    'title' => 'Log Sinkronisasi Marketplace - ' . $business->name,
    'headerTitle' => 'Log & Audit Sinkronisasi Marketplace',
    'headerSubtitle' => 'Audit trail real-time untuk sinkronisasi harga, stok, pesanan, dan penerimaan webhook marketplace',
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="{
    detailModal: false,
    selectedLog: null,
    viewDetail(log) {
        this.selectedLog = log;
        this.detailModal = true;
    }
}">

    <!-- ========================================== -->
    <!-- 0. BREADCRUMB BAR (APPLE MINIMALIST)       -->
    <!-- ========================================== -->
    <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 py-0.5 whitespace-nowrap print:hidden" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors font-medium">Dashboard</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
        <a href="{{ route('marketplace-hub.index') }}" class="hover:text-[#007AFF] transition-colors font-medium">Marketplace Hub</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
        <span class="text-black/80 dark:text-white/80 font-medium">Log &amp; Audit Sinkronisasi</span>
    </nav>

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER                               -->
    <!-- ===================================================== -->
    <header class="rounded-[20px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5 shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
        <div class="space-y-1.5 max-w-2xl">
            <h1 class="text-[20px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight">
                Log &amp; Audit Aktivitas Integrasi
            </h1>
            <p class="text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                Catatan komprehensif seluruh transaksi data sinkronisasi harga, perubahan stok fisik, dan tangkapan webhook pesanan dari Shopee, TikTok Shop, dan Tokopedia.
            </p>
        </div>

        <div class="flex items-center gap-2.5 w-full lg:w-auto">
            <a href="{{ route('marketplace-hub.index') }}"
                class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] flex items-center justify-center gap-1.5 cursor-pointer">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Hub</span>
            </a>
        </div>
    </header>

    <!-- ===================================================== -->
    <!-- 2. FILTER BAR                                         -->
    <!-- ===================================================== -->
    <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-3.5 sm:p-4 backdrop-blur-md flex flex-col sm:flex-row items-center justify-between gap-3 shadow-xs">
        <form method="GET" action="{{ route('marketplace-hub.logs') }}" class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
            <!-- Channel Filter -->
            <select name="channel" onchange="this.form.submit()"
                class="h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.06] dark:border-white/[0.08] text-[13px] text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30">
                <option value="">Semua Saluran (All Channels)</option>
                <option value="shopee" {{ request('channel') === 'shopee' ? 'selected' : '' }}>Shopee</option>
                <option value="tiktok_shop" {{ request('channel') === 'tiktok_shop' ? 'selected' : '' }}>TikTok Shop</option>
                <option value="tokopedia" {{ request('channel') === 'tokopedia' ? 'selected' : '' }}>Tokopedia</option>
            </select>

            <!-- Status Filter -->
            <select name="status" onchange="this.form.submit()"
                class="h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.06] dark:border-white/[0.08] text-[13px] text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30">
                <option value="">Semua Status</option>
                <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Sukses (Success)</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Gagal / Error (Failed)</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Sedang Diproses (Pending)</option>
            </select>

            @if(request()->filled('channel') || request()->filled('status'))
                <a href="{{ route('marketplace-hub.logs') }}" class="h-10 px-3 rounded-[12px] text-[12.5px] font-medium text-black/50 hover:text-black dark:hover:text-white flex items-center">
                    Reset Filter
                </a>
            @endif
        </form>

        <div class="text-[12px] text-black/60 dark:text-white/60">
            <span>Total Catatan: <strong>{{ $logs->total() }}</strong> Entri</span>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. BENTO LOGS AUDIT TABLE                             -->
    <!-- ===================================================== -->
    <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px] border-collapse">
                <thead>
                    <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02] text-black/60 dark:text-white/60 font-semibold text-[11.5px] uppercase tracking-wider">
                        <th class="py-3.5 px-4 sm:px-6">Aktivitas / Event</th>
                        <th class="py-3.5 px-4">Saluran</th>
                        <th class="py-3.5 px-4">Status &amp; Keterangan</th>
                        <th class="py-3.5 px-4 text-center">Durasi</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Waktu Eksekusi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($logs as $log)
                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors cursor-pointer"
                            @click="viewDetail({{ json_encode($log) }})">
                            <!-- Col 1: Event Type -->
                            <td class="py-4 px-4 sm:px-6">
                                <div class="font-bold text-black dark:text-white text-[13.5px]">
                                    {{ ucwords(str_replace('_', ' ', $log->event_type)) }}
                                </div>
                                @if($log->reference_id)
                                    <div class="text-[11px] text-black/50 dark:text-white/50 font-mono mt-0.5">
                                        Ref: {{ $log->reference_id }}
                                    </div>
                                @endif
                            </td>

                            <!-- Col 2: Channel -->
                            <td class="py-4 px-4">
                                @if($log->channel === 'shopee')
                                    <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-bold bg-[#EE4D2D]/15 text-[#EE4D2D]">SHOPEE</span>
                                @elseif($log->channel === 'tiktok_shop')
                                    <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-bold bg-black text-white dark:bg-white dark:text-black">TIKTOK</span>
                                @elseif($log->channel === 'tokopedia')
                                    <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-bold bg-[#00AA5B]/15 text-[#00AA5B]">TOKPED</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-bold bg-black/10">{{ strtoupper($log->channel ?? 'SYSTEM') }}</span>
                                @endif
                            </td>

                            <!-- Col 3: Status & Message -->
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-2">
                                    @if($log->status === 'success')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                            <i data-lucide="check" class="w-3 h-3"></i>
                                            <span>Sukses</span>
                                        </span>
                                    @elseif($log->status === 'failed')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/15 text-[#FF3B30]">
                                            <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                            <span>Gagal</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/15 text-[#FF9500]">
                                            <span>Pending</span>
                                        </span>
                                    @endif
                                    <span class="text-[12px] text-black/70 dark:text-white/70 truncate max-w-[280px]">
                                        {{ $log->error_message ?? ($log->status === 'success' ? 'Operasi berhasil diselesaikan' : 'Sedang diproses') }}
                                    </span>
                                </div>
                            </td>

                            <!-- Col 4: Execution Time -->
                            <td class="py-4 px-4 text-center">
                                <span class="font-mono text-[11.5px] text-black/60 dark:text-white/60">
                                    {{ $log->execution_time_ms !== null ? $log->execution_time_ms . ' ms' : '-' }}
                                </span>
                            </td>

                            <!-- Col 5: Timestamp -->
                            <td class="py-4 px-4 sm:px-6 text-right">
                                <div class="text-[12px] font-medium text-black dark:text-white">
                                    {{ $log->created_at->format('d M Y, H:i:s') }}
                                </div>
                                <div class="text-[10.5px] text-black/45 dark:text-white/45">
                                    {{ $log->created_at->diffForHumans() }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-black/50 dark:text-white/50">
                                <div class="w-12 h-12 rounded-[16px] bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="clipboard-list" class="w-6 h-6 text-black/40 dark:text-white/40"></i>
                                </div>
                                <div class="font-bold text-[14px]">Belum Ada Riwayat Log</div>
                                <p class="text-[12px] mt-0.5">Seluruh aktivitas sinkronisasi dan webhook payload akan tercatat secara terperinci di sini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 4. MODAL DETAIL PAYLOAD & AUDIT TRACE                 -->
    <!-- ===================================================== -->
    <div x-show="detailModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div @click.away="detailModal = false"
            class="w-full max-w-2xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 sm:p-7 space-y-5 shadow-2xl max-h-[85vh] overflow-y-auto">

            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center shrink-0">
                        <i data-lucide="file-text" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white" x-text="selectedLog ? selectedLog.event_type : 'Detail Log'"></h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50" x-text="selectedLog ? (selectedLog.channel + ' • ID #' + selectedLog.id) : ''"></p>
                    </div>
                </div>
                <button type="button" @click="detailModal = false" class="text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <template x-if="selectedLog">
                <div class="space-y-4 text-[13px]">
                    <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] space-y-2">
                        <div class="flex justify-between">
                            <span class="text-black/50 dark:text-white/50">Status:</span>
                            <span class="font-bold uppercase" :class="selectedLog.status === 'success' ? 'text-[#34C759]' : 'text-[#FF3B30]'" x-text="selectedLog.status"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-black/50 dark:text-white/50">Waktu Eksekusi:</span>
                            <span class="font-mono text-black dark:text-white" x-text="selectedLog.execution_time_ms + ' ms'"></span>
                        </div>
                        <template x-if="selectedLog.error_message">
                            <div class="text-[#FF3B30] pt-1 border-t border-black/[0.04]">
                                <strong>Error Message:</strong>
                                <p class="text-[12px] mt-0.5 font-mono" x-text="selectedLog.error_message"></p>
                            </div>
                        </template>
                    </div>

                    <div>
                        <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">Payload / Request Data</label>
                        <pre class="p-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] text-[11px] font-mono overflow-x-auto max-h-48 text-black dark:text-white" x-text="JSON.stringify(selectedLog.request_payload, null, 2)"></pre>
                    </div>

                    <div>
                        <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">Response Data</label>
                        <pre class="p-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] text-[11px] font-mono overflow-x-auto max-h-48 text-black dark:text-white" x-text="JSON.stringify(selectedLog.response_payload, null, 2)"></pre>
                    </div>
                </div>
            </template>

            <div class="flex justify-end pt-2">
                <button type="button" @click="detailModal = false"
                    class="h-10 px-5 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
