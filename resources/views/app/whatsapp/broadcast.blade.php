@extends('layouts.app', [
    'title' => 'Blast Promosi WhatsApp — ' . $business->name,
    'headerTitle' => 'Blast Promosi WhatsApp',
    'headerSubtitle' => 'Kirim promosi massal & notifikasi spesial ke pelanggan terdaftar'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12">

    <!-- ========================================== -->
    <!-- 0. BREADCRUMB BAR (APPLE MINIMALIST)       -->
    <!-- ========================================== -->
    <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 py-0.5 whitespace-nowrap print:hidden" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors font-medium">Dashboard</a>
        <span>›</span>
        <a href="{{ route('whatsapp.index') }}" class="hover:text-[#007AFF] transition-colors font-medium">WhatsApp Gateway</a>
        <span>›</span>
        <span class="text-black/80 dark:text-white/80 font-medium">Blast Promosi</span>
    </nav>

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR & SUB-TABS (macOS Sonoma Toolbar Style)     -->
    <!-- ===================================================== -->
    <header class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 transition-colors shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
        <div>
            <h1 class="text-[20px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight">Blast Promosi WhatsApp</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Kirim promosi massal &amp; notifikasi spesial ke seluruh pelanggan terdaftar</p>
        </div>

        <a href="{{ route('whatsapp.broadcast.create') }}"
            class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-[13px] shadow-[0_1px_2px_rgba(0,122,255,0.25)] flex items-center justify-center gap-1.5 transition-all active:scale-[0.97] active:opacity-80 w-full sm:w-auto">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Buat Blast Promosi Baru</span>
        </a>
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
                class="h-8 px-4 rounded-[9px] bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] flex items-center gap-2 whitespace-nowrap">
                <i data-lucide="megaphone" class="w-4 h-4 text-[#007AFF]"></i>
                <span>Blast Promosi</span>
            </a>
            <a href="{{ route('whatsapp.logs.index') }}"
                class="h-8 px-4 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                <i data-lucide="history" class="w-4 h-4"></i>
                <span>Log Pesan</span>
            </a>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 2. STATS KPI GRID (4 METRICS - Apple HIG Style)        -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Metric 1: Total Kampanye -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Kampanye</span>
                <div class="w-7 h-7 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.08] flex items-center justify-center text-black/60 dark:text-white/60">
                    <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                </div>
            </div>
            <div class="text-[20px] sm:text-[26px] font-bold tabular-nums text-black dark:text-white tracking-tight">
                {{ number_format($stats['total_campaigns'], 0, ',', '.') }}
            </div>
            <div class="mt-2 text-[11px] text-black/40 dark:text-white/40">Riwayat broadcast</div>
        </div>

        <!-- Metric 2: Pesan Terkirim (System Green) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Pesan Terkirim</span>
                <div class="w-7 h-7 rounded-[8px] bg-[#34C759]/12 flex items-center justify-center text-[#34C759] dark:text-[#30D158]">
                    <i data-lucide="send" class="w-3.5 h-3.5"></i>
                </div>
            </div>
            <div class="text-[20px] sm:text-[26px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] tracking-tight">
                {{ number_format($stats['total_sent'], 0, ',', '.') }}
            </div>
            <div class="mt-2 text-[11px] text-black/40 dark:text-white/40">Berhasil masuk ke WA</div>
        </div>

        <!-- Metric 3: Total Target (System Indigo) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Target</span>
                <div class="w-7 h-7 rounded-[8px] bg-[#5856D6]/10 flex items-center justify-center text-[#5856D6] dark:text-[#5E5CE6]">
                    <i data-lucide="users" class="w-3.5 h-3.5"></i>
                </div>
            </div>
            <div class="text-[20px] sm:text-[26px] font-bold tabular-nums text-black dark:text-white tracking-tight">
                {{ number_format($stats['total_recipients'], 0, ',', '.') }}
            </div>
            <div class="mt-2 text-[11px] text-black/40 dark:text-white/40">Penerima ditargetkan</div>
        </div>

        <!-- Metric 4: Keberhasilan (System Blue/Green) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Tingkat Sukses</span>
                <div class="w-7 h-7 rounded-[8px] bg-[#34C759]/12 flex items-center justify-center text-[#34C759] dark:text-[#30D158]">
                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                </div>
            </div>
            <div class="text-[20px] sm:text-[26px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] tracking-tight">
                {{ $stats['success_rate'] }}%
            </div>
            <div class="mt-2 text-[11px] text-black/40 dark:text-white/40">Delivery rate sukses</div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. CAMPAIGNS DATA TABLE (Apple Dense Table)           -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] overflow-hidden transition-colors">
        <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
            <div>
                <h2 class="text-[14px] font-semibold text-black dark:text-white">Riwayat Kampanye Broadcast</h2>
                <p class="text-[12px] text-black/50 dark:text-white/50">Daftar semua pesan promosi massal yang pernah dikirimkan</p>
            </div>
        </div>

        @if($campaigns->isEmpty())
            <div class="text-center py-16 px-4 space-y-3">
                <div class="w-14 h-14 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] text-black/30 dark:text-white/30 flex items-center justify-center mx-auto border border-black/5 dark:border-white/10">
                    <i data-lucide="megaphone-off" class="w-7 h-7"></i>
                </div>
                <div>
                    <h3 class="text-black dark:text-white font-semibold text-[15px]">Belum Ada Kampanye Blast Promosi</h3>
                    <p class="text-black/50 dark:text-white/50 text-[13px] mt-1 max-w-sm mx-auto leading-relaxed">
                        Buat pesan promosi massal pertama Anda untuk mengabarkan diskon atau info menu terbaru ke pelanggan setia.
                    </p>
                </div>
                <a href="{{ route('whatsapp.broadcast.create') }}"
                    class="inline-flex items-center gap-2 mt-2 h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-[13px] shadow-[0_1px_2px_rgba(0,122,255,0.25)] transition-all active:scale-[0.97] active:opacity-80">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Buat Blast Pertama Sekarang</span>
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px] min-w-[640px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 text-black/40 dark:text-white/40 uppercase tracking-wide text-[11px] font-semibold">
                            <th class="px-5 py-2.5">Judul Kampanye</th>
                            <th class="px-4 py-2.5">Target Audiens</th>
                            <th class="px-4 py-2.5 text-center">Penerima</th>
                            <th class="px-4 py-2.5 text-center">Terkirim</th>
                            <th class="px-4 py-2.5 text-center">Status</th>
                            <th class="px-4 py-2.5">Waktu Kirim</th>
                            <th class="px-5 py-2.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @foreach($campaigns as $campaign)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                <td class="px-5 py-3">
                                    <div class="font-semibold text-black dark:text-white">{{ $campaign->title }}</div>
                                    <div class="text-black/50 dark:text-white/50 truncate max-w-xs mt-0.5 text-[12px]">
                                        {{ Str::limit($campaign->message, 55) }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold
                                        {{ $campaign->target_filter === 'all' 
                                            ? 'bg-black/6 dark:bg-white/8 text-black/60 dark:text-white/60' 
                                            : 'bg-[#007AFF]/10 text-[#007AFF]' }}">
                                        {{ $campaign->target_filter === 'all' ? 'Semua Pelanggan' : ucfirst($campaign->target_filter) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center tabular-nums font-semibold text-black/80 dark:text-white/80">
                                    {{ number_format($campaign->total_recipients, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-center tabular-nums">
                                    <span class="text-[#34C759] dark:text-[#30D158] font-bold">{{ number_format($campaign->total_sent, 0, ',', '.') }}</span>
                                    @if($campaign->total_failed > 0)
                                        <span class="text-[#FF3B30] dark:text-[#FF453A] font-medium text-[11px]"> ({{ $campaign->total_failed }} gagal)</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $statusBadge = match($campaign->status) {
                                            'completed'  => ['bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] border-[#34C759]/20', 'Selesai', 'bg-[#34C759]'],
                                            'processing' => ['bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A] border-[#FF9500]/20', 'Memproses', 'bg-[#FF9500] animate-pulse'],
                                            'failed'     => ['bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A] border-[#FF3B30]/20', 'Gagal', 'bg-[#FF3B30]'],
                                            default      => ['bg-black/6 dark:bg-white/8 text-black/60 dark:text-white/60 border-black/10', 'Draft', 'bg-black/40'],
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $statusBadge[0] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $statusBadge[2] }}"></span>
                                        <span>{{ $statusBadge[1] }}</span>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-black/50 dark:text-white/50 text-[12px] tabular-nums whitespace-nowrap">
                                    {{ $campaign->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('whatsapp.broadcast.show', $campaign) }}"
                                        class="h-7 px-2.5 rounded-[6px] text-[12px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors inline-flex items-center gap-1">
                                        <span>Detail</span>
                                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($campaigns->hasPages())
                <div class="p-4 border-t border-black/5 dark:border-white/10">
                    {{ $campaigns->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
