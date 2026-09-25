@extends('layouts.app', [
    'title' => 'Detail Blast: ' . $campaign->title . ' - ' . $business->name,
    'headerTitle' => 'Detail Kampanye Broadcast',
    'headerSubtitle' => 'Audit status pengiriman dan daftar penerima pesan promosi',
])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="broadcastDetail()">

        <!-- ========================================== -->
        <!-- 0. BREADCRUMB BAR (APPLE MINIMALIST)       -->
        <!-- ========================================== -->
        <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 py-0.5 whitespace-nowrap print:hidden"
            aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors font-medium">Dashboard</a>
            <span>›</span>
            <a href="{{ route('whatsapp.index') }}" class="hover:text-[#007AFF] transition-colors font-medium">WhatsApp Gateway</a>
            <span>›</span>
            <a href="{{ route('whatsapp.broadcast.index') }}" class="hover:text-[#007AFF] transition-colors font-medium">Blast Promosi</a>
            <span>›</span>
            <span class="text-black/80 dark:text-white/80 font-medium">Detail Kampanye</span>
        </nav>

        <!-- ===================================================== -->
        <!-- 1. TOOLBAR & ACTION HUB (macOS Sonoma Style)           -->
        <!-- ===================================================== -->
        <header
            class="rounded-[20px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 flex items-center justify-between gap-4 flex-wrap transition-colors shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
            <div class="flex items-center gap-3">
                <a href="{{ route('whatsapp.broadcast.index') }}"
                    class="w-10 h-10 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black/70 dark:text-white/70 flex items-center justify-center transition active:scale-[0.98]"
                    title="Kembali ke Daftar Blast">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-[20px] sm:text-[22px] font-bold text-black dark:text-white tracking-tight">
                            {{ $campaign->title }}
                        </h1>
                        @php
                            $statusBadge = match ($campaign->status) {
                                'completed' => [
                                    'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] border-[#34C759]/20',
                                    'Selesai',
                                    'bg-[#34C759]',
                                ],
                                'processing' => [
                                    'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A] border-[#FF9500]/20',
                                    'Memproses',
                                    'bg-[#FF9500]',
                                ],
                                'failed' => [
                                    'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A] border-[#FF3B30]/20',
                                    'Gagal',
                                    'bg-[#FF3B30]',
                                ],
                                default => [
                                    'bg-black/6 dark:bg-white/8 text-black/60 dark:text-white/60 border-black/10',
                                    'Draft',
                                    'bg-black/40',
                                ],
                            };
                        @endphp
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-[11.5px] font-bold {{ $statusBadge[0] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $statusBadge[2] }}"></span>
                            <span>{{ $statusBadge[1] }}</span>
                        </span>
                    </div>
                    <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">
                        Dibuat pada {{ $campaign->created_at->format('d M Y, H:i') }} WIB &bull; Target: <span
                            class="capitalize font-bold text-black/70 dark:text-white/70">{{ $campaign->target_filter }}</span>
                    </p>
                </div>
            </div>

            @if (\App\Support\Context::hasPermission('whatsapp.manage'))
                <div class="flex items-center gap-2">
                    <a href="{{ route('whatsapp.broadcast.create') }}"
                        class="min-h-[44px] px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-bold text-[13px] shadow-md shadow-[#007AFF]/25 flex items-center gap-2 transition-all active:scale-[0.98]">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Buat Blast Baru</span>
                    </a>
                </div>
            @endif
        </header>

        <!-- Live Processing Alert (When Background Queue Is Running) -->
        <template x-if="status === 'processing'">
            <div class="p-4 rounded-[16px] bg-[#FF9500]/12 border border-[#FF9500]/25 text-[#B25E00] dark:text-[#FF9F0A] text-[13px] flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <i data-lucide="loader-2" class="w-5 h-5 shrink-0 animate-spin text-[#FF9500]"></i>
                    <div>
                        <strong class="font-bold">Pesan Blast Sedang Diproses di Latar Belakang...</strong>
                        <p class="text-[12px] text-black/60 dark:text-white/60 mt-0.5">Halaman ini otomatis memperbarui status pengiriman secara berkala.</p>
                    </div>
                </div>
                <button type="button" @click="pollStatus()" class="min-h-[32px] px-3 rounded-[8px] bg-[#FF9500]/20 hover:bg-[#FF9500]/30 text-xs font-bold transition">
                    Segarkan Sekarang
                </button>
            </div>
        </template>

        <!-- ===================================================== -->
        <!-- 2. STATS KPI GRID (4 METRICS - Apple HIG Style)        -->
        <!-- ===================================================== -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div
                class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[12px] font-semibold text-black/50 dark:text-white/50">Total Target</span>
                    <div
                        class="w-8 h-8 rounded-[10px] bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6] flex items-center justify-center">
                        <i data-lucide="users" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-[22px] sm:text-[28px] font-bold tabular-nums text-black dark:text-white tracking-tight"
                    x-text="totalRecipients.toLocaleString('id-ID')">
                    {{ number_format($campaign->total_recipients, 0, ',', '.') }}
                </div>
                <div class="mt-2 text-[11px] text-black/40 dark:text-white/40">Nomor tujuan terdaftar</div>
            </div>

            <div
                class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[12px] font-semibold text-black/50 dark:text-white/50">Berhasil Terkirim</span>
                    <div
                        class="w-8 h-8 rounded-[10px] bg-[#34C759]/12 text-[#34C759] dark:text-[#30D158] flex items-center justify-center">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                    </div>
                </div>
                <div
                    class="text-[22px] sm:text-[28px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] tracking-tight"
                    x-text="totalSent.toLocaleString('id-ID')">
                    {{ number_format($campaign->total_sent, 0, ',', '.') }}
                </div>
                <div class="mt-2 text-[11px] text-black/40 dark:text-white/40">Sukses terdistribusi</div>
            </div>

            <div
                class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[12px] font-semibold text-black/50 dark:text-white/50">Gagal Terkirim</span>
                    <div
                        class="w-8 h-8 rounded-[10px] bg-[#FF3B30]/12 text-[#FF3B30] dark:text-[#FF453A] flex items-center justify-center">
                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                    </div>
                </div>
                <div
                    class="text-[22px] sm:text-[28px] font-bold tabular-nums text-[#FF3B30] dark:text-[#FF453A] tracking-tight"
                    x-text="totalFailed.toLocaleString('id-ID')">
                    {{ number_format($campaign->total_failed, 0, ',', '.') }}
                </div>
                <div class="mt-2 text-[11px] text-black/40 dark:text-white/40">Nomor tidak aktif / gagal API</div>
            </div>

            <div
                class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[12px] font-semibold text-black/50 dark:text-white/50">Tingkat Sukses</span>
                    <div
                        class="w-8 h-8 rounded-[10px] bg-[#34C759]/12 text-[#34C759] dark:text-[#30D158] flex items-center justify-center">
                        <i data-lucide="percent" class="w-4 h-4"></i>
                    </div>
                </div>
                <div
                    class="text-[22px] sm:text-[28px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] tracking-tight"
                    x-text="successRate + '%'">
                    @php
                        $initialRate = $campaign->total_recipients > 0
                            ? round(($campaign->total_sent / $campaign->total_recipients) * 100, 1)
                            : 0;
                    @endphp
                    {{ $initialRate }}%
                </div>
                <div class="mt-2 text-[11px] text-black/40 dark:text-white/40">Persentase delivery rate</div>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- 3. MESSAGE CONTENT PREVIEW CARD                       -->
        <!-- ===================================================== -->
        <div
            class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] p-5 sm:p-6 space-y-3 transition-colors">
            <div class="flex items-center gap-2.5 pb-3 border-b border-black/5 dark:border-white/10">
                <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="message-square" class="w-4 h-4"></i>
                </div>
                <h2 class="text-[15px] font-bold text-black dark:text-white">Template Pesan Promosi Yang Dikirim</h2>
            </div>

            @if ($campaign->media_url)
                <div
                    class="p-2 bg-black/[0.02] dark:bg-white/[0.03] rounded-[14px] border border-black/5 dark:border-white/10 max-w-sm">
                    <img src="{{ $campaign->media_url }}" alt="Banner Promosi"
                        class="w-full h-auto rounded-[10px] object-cover">
                </div>
            @endif

            <div
                class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 font-mono text-[13px] text-black/90 dark:text-white/90 whitespace-pre-wrap leading-relaxed">
                {{ $campaign->message }}
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- 4. RECIPIENTS DELIVERY AUDIT TABLE (Apple Dense Table)-->
        <!-- ===================================================== -->
        <div
            class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] overflow-hidden transition-colors">
            <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <div>
                    <h2 class="text-[15px] font-bold text-black dark:text-white">Audit Log Penerima Pesan</h2>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Status pengiriman riil per kontak pelanggan</p>
                </div>
                <span class="text-[12px] text-black/40 dark:text-white/40 font-semibold tabular-nums">
                    Halaman {{ $recipients->currentPage() }} dari {{ $recipients->lastPage() }}
                </span>
            </div>

            @if ($recipients->isEmpty())
                <div class="text-center py-12 text-black/40 dark:text-white/40 text-[13px]">
                    Belum ada data penerima yang tercatat dalam kampanye ini.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[13.5px] min-w-[600px]">
                        <thead>
                            <tr
                                class="border-b border-black/5 dark:border-white/10 text-black/40 dark:text-white/40 uppercase tracking-wide text-[11px] font-semibold">
                                <th class="px-5 py-3">Nama Pelanggan</th>
                                <th class="px-4 py-3">Nomor WhatsApp</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3">Waktu Pengiriman</th>
                                <th class="px-5 py-3">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                            @foreach ($recipients as $item)
                                <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                    <td class="px-5 py-3.5 font-bold text-black dark:text-white">
                                        {{ $item->customer_name ?? ($item->customer?->name ?? 'Pelanggan') }}
                                    </td>
                                    <td class="px-4 py-3.5 font-medium tabular-nums text-black/70 dark:text-white/70">
                                        {{ $item->phone_number }}
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        @if ($item->status === 'sent')
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                                <i data-lucide="check" class="w-3 h-3"></i>
                                                <span>Terkirim</span>
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                                <i data-lucide="x" class="w-3 h-3"></i>
                                                <span>Gagal</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-black/50 dark:text-white/50 tabular-nums text-[12px]">
                                        {{ $item->sent_at ? $item->sent_at->format('d/m/Y H:i:s') : '-' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-black/60 dark:text-white/60 text-[12px]">
                                        {{ $item->error_message ?: 'Terkirim sukses via WhatsApp bot' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($recipients->hasPages())
                    <div class="p-4 border-t border-black/5 dark:border-white/10">
                        {{ $recipients->links() }}
                    </div>
                @endif
            @endif
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function broadcastDetail() {
            return {
                status: '{{ $campaign->status }}',
                totalRecipients: {{ $campaign->total_recipients }},
                totalSent: {{ $campaign->total_sent }},
                totalFailed: {{ $campaign->total_failed }},
                successRate: {{ $initialRate }},
                pollTimer: null,

                init() {
                    if (this.status === 'processing') {
                        this.startPolling();
                    }
                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });
                },

                startPolling() {
                    this.pollTimer = setInterval(() => {
                        this.pollStatus();
                    }, 3000);
                },

                async pollStatus() {
                    try {
                        const res = await fetch('{{ route('whatsapp.broadcast.show', $campaign) }}', {
                            headers: { 'Accept': 'application/json' }
                        });
                        if (!res.ok) return;
                        const data = await res.json();
                        this.status = data.status;
                        this.totalRecipients = data.total_recipients;
                        this.totalSent = data.total_sent;
                        this.totalFailed = data.total_failed;
                        if (this.totalRecipients > 0) {
                            this.successRate = Math.round((this.totalSent / this.totalRecipients) * 1000) / 10;
                        }

                        if (this.status === 'completed' || this.status === 'failed') {
                            clearInterval(this.pollTimer);
                            setTimeout(() => window.location.reload(), 1000);
                        }
                    } catch (e) {}
                }
            };
        }
    </script>
@endpush
