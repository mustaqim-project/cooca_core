@extends('layouts.app', [
    'title' => 'Blast Promosi WhatsApp - ' . $business->name,
    'headerTitle' => 'Blast Promosi WhatsApp',
    'headerSubtitle' => 'Kirim promosi massal & notifikasi spesial ke pelanggan terdaftar',
])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="broadcastManager()">

        <!-- ========================================== -->
        <!-- 0. BREADCRUMB BAR (APPLE MINIMALIST)       -->
        <!-- ========================================== -->
        <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 py-0.5 whitespace-nowrap print:hidden"
            aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors font-medium">Dashboard</a>
            <span>›</span>
            <a href="{{ route('whatsapp.index') }}" class="hover:text-[#007AFF] transition-colors font-medium">WhatsApp Gateway</a>
            <span>›</span>
            <span class="text-black/80 dark:text-white/80 font-medium">Blast Promosi</span>
        </nav>

        <!-- ===================================================== -->
        <!-- 1. TOOLBAR & SUB-TABS (macOS Sonoma Toolbar Style)     -->
        <!-- ===================================================== -->
        <header
            class="rounded-[20px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 transition-colors shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
            <div>
                <h1 class="text-[20px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight">
                    Blast Promosi WhatsApp
                </h1>
                <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">
                    Kirim promosi massal &amp; notifikasi spesial ke seluruh pelanggan terdaftar
                </p>
            </div>

            @if (\App\Support\Context::hasPermission('whatsapp.manage'))
                <button type="button" @click="openCreateModal()"
                    class="min-h-[44px] px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-bold text-[13.5px] shadow-md shadow-[#007AFF]/25 flex items-center justify-center gap-2 transition-all active:scale-[0.98] w-full sm:w-auto">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Buat Blast Promosi Baru</span>
                </button>
            @endif
        </header>

        <!-- Sub-Tabs Segmented Bar -->
        <div
            class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-2 sm:p-2.5 flex items-center justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
            <div
                class="inline-flex p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] border border-black/5 dark:border-white/5 w-full sm:w-auto overflow-x-auto text-[13px] font-medium">
                <a href="{{ route('whatsapp.index') }}"
                    class="h-9 px-4 rounded-[10px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                    <i data-lucide="smartphone" class="w-4 h-4"></i>
                    <span>Koneksi Gateway</span>
                </a>
                <a href="{{ route('whatsapp.broadcast.index') }}"
                    class="h-9 px-4 rounded-[10px] bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] flex items-center gap-2 whitespace-nowrap font-semibold">
                    <i data-lucide="megaphone" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Blast Promosi</span>
                </a>
                <a href="{{ route('whatsapp.logs.index') }}"
                    class="h-9 px-4 rounded-[10px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
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
            <div
                class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[12px] font-semibold text-black/50 dark:text-white/50">Total Kampanye</span>
                    <div
                        class="w-8 h-8 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.08] flex items-center justify-center text-black/60 dark:text-white/60">
                        <i data-lucide="layers" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-[22px] sm:text-[28px] font-bold tabular-nums text-black dark:text-white tracking-tight">
                    {{ number_format($stats['total_campaigns'], 0, ',', '.') }}
                </div>
                <div class="mt-2 text-[11px] text-black/40 dark:text-white/40">Riwayat broadcast</div>
            </div>

            <!-- Metric 2: Pesan Terkirim (System Green) -->
            <div
                class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[12px] font-semibold text-black/50 dark:text-white/50">Pesan Terkirim</span>
                    <div
                        class="w-8 h-8 rounded-[10px] bg-[#34C759]/12 flex items-center justify-center text-[#34C759] dark:text-[#30D158]">
                        <i data-lucide="send" class="w-4 h-4"></i>
                    </div>
                </div>
                <div
                    class="text-[22px] sm:text-[28px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] tracking-tight">
                    {{ number_format($stats['total_sent'], 0, ',', '.') }}
                </div>
                <div class="mt-2 text-[11px] text-black/40 dark:text-white/40">Berhasil masuk ke WhatsApp</div>
            </div>

            <!-- Metric 3: Total Target (System Indigo) -->
            <div
                class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[12px] font-semibold text-black/50 dark:text-white/50">Total Target</span>
                    <div
                        class="w-8 h-8 rounded-[10px] bg-[#5856D6]/10 flex items-center justify-center text-[#5856D6] dark:text-[#5E5CE6]">
                        <i data-lucide="users" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-[22px] sm:text-[28px] font-bold tabular-nums text-black dark:text-white tracking-tight">
                    {{ number_format($stats['total_recipients'], 0, ',', '.') }}
                </div>
                <div class="mt-2 text-[11px] text-black/40 dark:text-white/40">Penerima ditargetkan</div>
            </div>

            <!-- Metric 4: Keberhasilan (System Blue/Green) -->
            <div
                class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[12px] font-semibold text-black/50 dark:text-white/50">Tingkat Sukses</span>
                    <div
                        class="w-8 h-8 rounded-[10px] bg-[#34C759]/12 flex items-center justify-center text-[#34C759] dark:text-[#30D158]">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    </div>
                </div>
                <div
                    class="text-[22px] sm:text-[28px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] tracking-tight">
                    {{ $stats['success_rate'] }}%
                </div>
                <div class="mt-2 text-[11px] text-black/40 dark:text-white/40">Delivery rate sukses</div>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- 3. CAMPAIGNS DATA TABLE (Apple Dense Table)           -->
        <!-- ===================================================== -->
        <div
            class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] overflow-hidden transition-colors">
            <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <div>
                    <h2 class="text-[15px] font-bold text-black dark:text-white">Riwayat Kampanye Broadcast</h2>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Daftar seluruh pesan promosi massal yang telah dijadwalkan</p>
                </div>
            </div>

            @if ($campaigns->isEmpty())
                <div class="text-center py-16 px-4 space-y-3">
                    <div
                        class="w-16 h-16 rounded-[18px] bg-black/[0.04] dark:bg-white/[0.06] text-black/30 dark:text-white/30 flex items-center justify-center mx-auto border border-black/5 dark:border-white/10">
                        <i data-lucide="megaphone-off" class="w-8 h-8"></i>
                    </div>
                    <div>
                        <h3 class="text-black dark:text-white font-bold text-[16px]">Belum Ada Kampanye Blast Promosi</h3>
                        <p class="text-black/50 dark:text-white/50 text-[13px] mt-1 max-w-sm mx-auto leading-relaxed">
                            Buat pesan promosi massal pertama Anda untuk mengabarkan diskon atau info produk terbaru ke pelanggan setia.
                        </p>
                    </div>
                    @if (\App\Support\Context::hasPermission('whatsapp.manage'))
                        <button type="button" @click="openCreateModal()"
                            class="inline-flex items-center gap-2 mt-3 min-h-[44px] px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-bold text-[13px] shadow-md shadow-[#007AFF]/25 transition-all active:scale-[0.98]">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>Buat Blast Pertama Sekarang</span>
                        </button>
                    @endif
                </div>
            @else
                {{-- Desktop Campaigns Table --}}
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left text-[13.5px]">
                        <thead>
                            <tr
                                class="border-b border-black/5 dark:border-white/10 text-black/40 dark:text-white/40 uppercase tracking-wide text-[11px] font-semibold">
                                <th class="px-5 py-3">Judul Kampanye</th>
                                <th class="px-4 py-3">Target Audiens</th>
                                <th class="px-4 py-3 text-center">Penerima</th>
                                <th class="px-4 py-3 text-center">Terkirim</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3">Waktu Kirim</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                            @foreach ($campaigns as $campaign)
                                <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                    <td class="px-5 py-3.5">
                                        <div class="font-bold text-black dark:text-white">{{ $campaign->title }}</div>
                                        <div class="text-black/50 dark:text-white/50 truncate max-w-xs mt-0.5 text-[12px]">
                                            {{ Str::limit($campaign->message, 60) }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold
                                        {{ $campaign->target_filter === 'all'
                                            ? 'bg-black/6 dark:bg-white/8 text-black/60 dark:text-white/60'
                                            : 'bg-[#007AFF]/10 text-[#007AFF]' }}">
                                            {{ $campaign->target_filter === 'all' ? 'Semua Pelanggan' : ucfirst($campaign->target_filter) }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-4 py-3.5 text-center tabular-nums font-semibold text-black/80 dark:text-white/80">
                                        {{ number_format($campaign->total_recipients, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3.5 text-center tabular-nums">
                                        <span
                                            class="text-[#34C759] dark:text-[#30D158] font-bold">{{ number_format($campaign->total_sent, 0, ',', '.') }}</span>
                                        @if ($campaign->total_failed > 0)
                                            <span class="text-[#FF3B30] dark:text-[#FF453A] font-medium text-[11px]">
                                                ({{ $campaign->total_failed }} gagal)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        @php
                                            $statusBadge = match ($campaign->status) {
                                                'completed' => [
                                                    'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]',
                                                    'Selesai',
                                                    'bg-[#34C759]',
                                                ],
                                                'processing' => [
                                                    'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]',
                                                    'Memproses',
                                                    'bg-[#FF9500]',
                                                ],
                                                'failed' => [
                                                    'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]',
                                                    'Gagal',
                                                    'bg-[#FF3B30]',
                                                ],
                                                default => [
                                                    'bg-black/6 dark:bg-white/8 text-black/60 dark:text-white/60',
                                                    'Draft',
                                                    'bg-black/40',
                                                ],
                                            };
                                        @endphp
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $statusBadge[0] }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $statusBadge[2] }}"></span>
                                            <span>{{ $statusBadge[1] }}</span>
                                        </span>
                                    </td>
                                    <td
                                        class="px-4 py-3.5 text-black/50 dark:text-white/50 text-[12px] tabular-nums whitespace-nowrap">
                                        {{ $campaign->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        <a href="{{ route('whatsapp.broadcast.show', $campaign) }}"
                                            class="min-h-[32px] px-3 rounded-[8px] text-[12px] font-semibold text-[#007AFF] hover:bg-[#007AFF]/10 transition-colors inline-flex items-center gap-1">
                                            <span>Rincian</span>
                                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Campaigns List --}}
                <div class="sm:hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @foreach ($campaigns as $campaign)
                        @php
                            $statusBadge = match ($campaign->status) {
                                'completed' => [
                                    'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]',
                                    'Selesai',
                                    'bg-[#34C759]',
                                ],
                                'processing' => [
                                    'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]',
                                    'Memproses',
                                    'bg-[#FF9500]',
                                ],
                                'failed' => [
                                    'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]',
                                    'Gagal',
                                    'bg-[#FF3B30]',
                                ],
                                default => [
                                    'bg-black/6 dark:bg-white/8 text-black/60 dark:text-white/60',
                                    'Draft',
                                    'bg-black/40',
                                ],
                            };
                        @endphp
                        <div class="p-4 space-y-3 active:bg-black/[0.02] dark:active:bg-white/[0.03] transition-colors">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h3 class="font-bold text-[14.5px] text-black dark:text-white leading-snug">
                                        {{ $campaign->title }}
                                    </h3>
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-semibold mt-1
                                {{ $campaign->target_filter === 'all'
                                    ? 'bg-black/6 dark:bg-white/8 text-black/60 dark:text-white/60'
                                    : 'bg-[#007AFF]/10 text-[#007AFF]' }}">
                                        {{ $campaign->target_filter === 'all' ? 'Semua Pelanggan' : ucfirst($campaign->target_filter) }}
                                    </span>
                                </div>
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10.5px] font-semibold {{ $statusBadge[0] }} shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $statusBadge[2] }}"></span>
                                    <span>{{ $statusBadge[1] }}</span>
                                </span>
                            </div>

                            <p
                                class="text-[12.5px] text-black/70 dark:text-white/70 line-clamp-2 bg-black/[0.02] dark:bg-white/[0.02] p-2.5 rounded-[10px] leading-relaxed">
                                {{ $campaign->message }}
                            </p>

                            <div class="flex items-center justify-between text-[12px] pt-1">
                                <div>
                                    <span
                                        class="text-[10px] uppercase font-semibold text-black/40 dark:text-white/40 block">Terkirim</span>
                                    <span
                                        class="font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">{{ number_format($campaign->total_sent, 0, ',', '.') }}</span>
                                    <span class="text-black/40 dark:text-white/40 text-[11px]">/
                                        {{ number_format($campaign->total_recipients, 0, ',', '.') }}</span>
                                    @if ($campaign->total_failed > 0)
                                        <span
                                            class="text-[#FF3B30] dark:text-[#FF453A] font-medium text-[10.5px]">({{ $campaign->total_failed }}
                                            gagal)</span>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <span
                                        class="text-[10px] uppercase font-semibold text-black/40 dark:text-white/40 block">Waktu</span>
                                    <span
                                        class="text-black/50 dark:text-white/50 text-[11px] tabular-nums">{{ $campaign->created_at->format('d/m/y H:i') }}</span>
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-end pt-1 border-t border-black/[0.04] dark:border-white/[0.06]">
                                <a href="{{ route('whatsapp.broadcast.show', $campaign) }}"
                                    class="min-h-[36px] px-3.5 rounded-[8px] text-[12px] font-bold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition-colors inline-flex items-center gap-1">
                                    <span>Lihat Rincian Laporan</span>
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($campaigns->hasPages())
                    <div class="p-4 border-t border-black/5 dark:border-white/10">
                        {{ $campaigns->links() }}
                    </div>
                @endif
            @endif
        </div>

        <!-- =================================================================== -->
        <!-- 4. MODAL-FIRST: FULL-SIZE XXL BENTO COMPOSER SHEET (Desktop & Mobile) -->
        <!-- =================================================================== -->
        <div x-show="createModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-0 sm:p-4 lg:p-6 overflow-hidden"
            @keydown.escape.window="closeCreateModal()">

            <!-- Backdrop Frosted Glass -->
            <div x-show="createModalOpen" x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" @click="closeCreateModal()"
                class="fixed inset-0 bg-black/40 dark:bg-black/60 backdrop-blur-sm"></div>

            <!-- XXL Bento Canvas Container -->
            <div x-show="createModalOpen" x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4 sm:translate-y-0"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4 sm:translate-y-0"
                class="relative w-full max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-[1350px] bg-[#F2F2F7] dark:bg-[#000000] sm:rounded-[24px] rounded-t-[28px] max-h-[95vh] sm:max-h-[92vh] flex flex-col overflow-hidden shadow-2xl border border-black/10 dark:border-white/10 z-10">

                <!-- Mobile Grab Bar -->
                <div class="sm:hidden w-10 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto my-2.5 shrink-0"></div>

                <!-- Sticky Header Modal -->
                <header
                    class="px-5 sm:px-7 py-4 bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-md border-b border-black/5 dark:border-white/10 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                            <i data-lucide="megaphone" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h2 class="text-[17px] sm:text-[19px] font-bold text-black dark:text-white tracking-tight">
                                Buat Kampanye Blast Promosi
                            </h2>
                            <p class="text-[12px] text-black/50 dark:text-white/50">
                                Kirim pesan otomatis ke kontak pelanggan dengan live simulator WhatsApp
                            </p>
                        </div>
                    </div>

                    <button type="button" @click="closeCreateModal()"
                        class="w-9 h-9 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.15] text-black/60 dark:text-white/60 flex items-center justify-center transition active:scale-[0.96]">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </header>

                <!-- Scrollable Body (Multi-Column Bento Grid) -->
                <div class="flex-1 overflow-y-auto p-5 sm:p-7 space-y-6">
                    @php
                        $isWaConnected = ($whatsAppAccount && $whatsAppAccount->isConnected()) || ($waSession && $waSession->isConnected());
                    @endphp

                    @if (! $isWaConnected)
                        <div class="p-4 rounded-[16px] bg-[#FF9500]/12 border border-[#FF9500]/20 text-[#B25E00] dark:text-[#FF9F0A] text-[13px] font-medium flex items-center gap-3">
                            <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0 text-[#FF9500]"></i>
                            <div>
                                <strong class="font-bold">WhatsApp Resmi Belum Terhubung!</strong>
                                <span class="ml-1">Silakan hubungkan akun WhatsApp bisnis Anda di <a href="{{ route('whatsapp.index') }}" class="underline font-bold hover:opacity-80">Integrasi WhatsApp</a> agar blast promosi dapat dikirim.</span>
                            </div>
                        </div>
                    @else
                        <div class="p-4 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] text-[12.5px] font-medium flex items-center gap-3">
                            <i data-lucide="shield-check" class="w-4 h-4 shrink-0 text-[#34C759]"></i>
                            <div>
                                <span class="font-bold">Meta WhatsApp Cloud API Aktif:</span>
                                <span class="ml-1 text-black/70 dark:text-white/70">Pesan blast promosi akan diproses melalui antrean resmi ({{ $whatsAppAccount?->display_phone_number ?? 'Nomor Toko' }}).</span>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('whatsapp.broadcast.store') }}" method="POST" id="modalBlastForm"
                        @submit.prevent="submitBlast">
                        @csrf

                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                            <!-- LEFT FORM COLUMN (7 cols) -->
                            <div class="lg:col-span-7 space-y-5">

                                <!-- Card: Identitas -->
                                <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 space-y-3">
                                    <label class="block text-[12.5px] font-bold text-black/70 dark:text-white/70">Judul Kampanye *</label>
                                    <input type="text" name="title" x-model="title" required
                                        placeholder="Contoh: Promo Gajian Weekend Diskon 20%"
                                        class="w-full min-h-[44px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[14px] font-medium text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-colors">
                                </div>

                                <!-- Card: Target Audiens -->
                                <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-[12.5px] font-bold text-black/70 dark:text-white/70">Pilih Target Audiens Pelanggan</label>
                                        <span class="text-[12px] font-bold text-[#007AFF] tabular-nums"
                                            x-text="estimatedCount.toLocaleString('id-ID') + ' Kontak Siap'"></span>
                                    </div>

                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 pt-1">
                                        @php
                                            $modalFilters = [
                                                'all' => ['label' => 'Semua Pelanggan', 'count' => $customerCount],
                                                'bronze' => ['label' => 'Bronze Tier', 'count' => $tierCounts['bronze'] ?? 0],
                                                'silver' => ['label' => 'Silver Tier', 'count' => $tierCounts['silver'] ?? 0],
                                                'gold' => ['label' => 'Gold Tier', 'count' => $tierCounts['gold'] ?? 0],
                                                'vip' => ['label' => 'VIP Member', 'count' => $tierCounts['vip'] ?? 0],
                                            ];
                                        @endphp
                                        @foreach ($modalFilters as $key => $filter)
                                            <label class="cursor-pointer select-none">
                                                <input type="radio" name="target_filter" value="{{ $key }}"
                                                    x-model="targetFilter" class="sr-only">
                                                <div class="p-3 rounded-[12px] border transition-all text-center"
                                                    :class="targetFilter === '{{ $key }}'
                                                        ? 'border-[#007AFF] bg-[#007AFF]/10 text-[#007AFF] font-bold shadow-sm'
                                                        : 'border-black/5 dark:border-white/5 bg-black/[0.02] dark:bg-white/[0.03] text-black/70 dark:text-white/70 hover:border-black/10'">
                                                    <div class="text-[13px] font-medium">{{ $filter['label'] }}</div>
                                                    <div class="text-[11px] opacity-70 mt-0.5 tabular-nums">
                                                        {{ number_format($filter['count'], 0, ',', '.') }} Kontak
                                                    </div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Card: Pesan -->
                                <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-[12.5px] font-bold text-black/70 dark:text-white/70">Konten Pesan Promosi</label>
                                    </div>

                                    <!-- Variable insertion chips -->
                                    <div class="flex flex-wrap items-center gap-1.5 pt-1">
                                        <span class="text-[11.5px] text-black/50 dark:text-white/50 font-semibold mr-1">Tag Personal:</span>
                                        @foreach (['{nama}', '{poin}', '{tier}', '{bisnis}'] as $var)
                                            <button type="button" @click="insertVar('{{ $var }}')"
                                                class="min-h-[28px] px-2.5 rounded-[8px] bg-black/[0.05] hover:bg-black/[0.08] dark:bg-white/[0.08] dark:hover:bg-white/[0.12] text-black/80 dark:text-white/80 text-[12px] font-mono transition active:scale-[0.97]">
                                                {{ $var }}
                                            </button>
                                        @endforeach
                                    </div>

                                    <textarea name="message" id="modalMsgTextarea" x-model="message" rows="5" required
                                        placeholder="Halo {nama},&#10;Ada promo spesial dari {{ $business->name }} untuk tier {tier}!&#10;&#10;Dapatkan diskon 20% khusus hari ini. Tunjukkan pesan ini ke kasir."
                                        @input="updatePreview()"
                                        class="w-full bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] p-3.5 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 resize-none font-sans placeholder:text-black/35 dark:placeholder:text-white/35 leading-relaxed transition-colors"></textarea>

                                    <div class="flex items-center justify-between text-[11px] text-black/40 dark:text-white/40">
                                        <span>Gunakan *teks* untuk tebal, _teks_ untuk miring</span>
                                        <span x-text="message.length + ' / 2000 Karakter'" class="tabular-nums font-semibold"></span>
                                    </div>
                                </div>

                                <!-- Card: Banner URL -->
                                <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 space-y-2">
                                    <label class="block text-[12.5px] font-bold text-black/70 dark:text-white/70">Banner Gambar Promosi <span class="text-black/40 dark:text-white/40 font-normal text-[11.5px]">(Opsional)</span></label>
                                    <input type="url" name="media_url" x-model="mediaUrl" @input="updatePreview()"
                                        placeholder="https://example.com/banner-promo.jpg"
                                        class="w-full min-h-[44px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 placeholder:text-black/35 dark:placeholder:text-white/35 transition-colors">
                                </div>

                            </div>

                            <!-- RIGHT PREVIEW COLUMN (5 cols) -->
                            <div class="lg:col-span-5">
                                <div class="lg:sticky lg:top-2 space-y-3">
                                    <div class="flex items-center justify-between text-[12px] font-bold text-black/50 dark:text-white/50 uppercase tracking-wide px-1">
                                        <span>Live Smartphone Simulator</span>
                                        <span class="text-[#007AFF] lowercase font-normal">WYSIWYG</span>
                                    </div>

                                    <!-- Phone Mockup Frame -->
                                    <div class="relative mx-auto max-w-[280px] rounded-[32px] bg-[#1C1C1E] border-[4px] border-black/80 dark:border-white/10 shadow-2xl overflow-hidden"
                                        style="height: 480px;">

                                        <!-- Top Notch -->
                                        <div class="absolute top-2 left-1/2 -translate-x-1/2 w-20 h-3.5 bg-black rounded-full z-20"></div>

                                        <!-- WA Header -->
                                        <div class="bg-[#075E54] px-3.5 pt-7 pb-2.5 flex items-center gap-2 shadow-sm text-white">
                                            <div class="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center text-white shrink-0">
                                                <i data-lucide="bot" class="w-3.5 h-3.5"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="text-[11.5px] font-bold text-white truncate">{{ $business->name }}</div>
                                                <div class="text-[9.5px] text-white/75 flex items-center gap-1">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                                    <span>online</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Chat Message Area -->
                                        <div class="bg-[#ECE5DD] dark:bg-[#0b141a] h-full overflow-y-auto px-2.5 pt-2.5 pb-20">
                                            <div class="flex justify-end mb-2">
                                                <div class="max-w-[90%] rounded-[12px] rounded-tr-[3px] bg-[#DCF8C6] dark:bg-[#005c4b] p-3 shadow-sm text-black dark:text-white">
                                                    <div x-show="mediaUrl" class="mb-2 rounded-[6px] overflow-hidden bg-black/10">
                                                        <img :src="mediaUrl" alt="banner"
                                                            class="w-full max-h-28 object-cover rounded-[6px]"
                                                            onerror="this.style.display='none'">
                                                    </div>
                                                    <p class="text-[11.5px] whitespace-pre-wrap break-words leading-relaxed text-black/90 dark:text-white/95"
                                                        x-text="previewMessage || 'Ketik judul dan pesan di formulir untuk melihat live simulasi tampilan...'"></p>
                                                    <div class="text-[9.5px] text-black/45 dark:text-white/60 text-right mt-1 flex items-center justify-end gap-1">
                                                        <span class="tabular-nums">{{ now()->format('H:i') }}</span>
                                                        <i data-lucide="check-check" class="w-3 h-3 text-[#007AFF]"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <p class="text-center text-[11px] text-black/50 dark:text-white/50">
                                        Simulasi data pelanggan contoh: <em>Budi Santoso</em> (Gold Tier).
                                    </p>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>

                <!-- Sticky Footer Action Bar -->
                <footer
                    class="px-5 sm:px-7 py-4 bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-md border-t border-black/5 dark:border-white/10 flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0">
                    <div class="text-[12px] text-black/50 dark:text-white/50 hidden sm:block">
                        Total penerima terhitung: <strong class="text-black dark:text-white font-bold" x-text="estimatedCount.toLocaleString('id-ID') + ' Kontak'"></strong>
                    </div>

                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <button type="button" @click="closeCreateModal()"
                            class="min-h-[44px] px-4 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all w-full sm:w-auto">
                            Batal
                        </button>

                        @if (\App\Support\Context::hasPermission('whatsapp.manage'))
                            <button type="button" @click="submitBlast()"
                                :disabled="submitting || !message.trim() || !title.trim()"
                                class="min-h-[44px] px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-bold text-[13.5px] shadow-md shadow-[#007AFF]/25 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 transition-all active:scale-[0.98] w-full sm:w-auto">
                                <i data-lucide="loader-2" x-show="submitting" class="w-4 h-4 animate-spin"></i>
                                <i data-lucide="send" x-show="!submitting" class="w-4 h-4"></i>
                                <span x-text="submitting ? 'Menjadwalkan...' : 'Kirim Blast ke ' + estimatedCount.toLocaleString('id-ID') + ' Pelanggan'"></span>
                            </button>
                        @endif
                    </div>
                </footer>

            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function broadcastManager() {
            return {
                createModalOpen: false,
                title: '',
                targetFilter: 'all',
                message: '',
                mediaUrl: '',
                previewMessage: '',
                submitting: false,
                estimatedCount: {{ $customerCount }},
                tierCounts: @json($tierCounts + ['all' => $customerCount]),

                init() {
                    this.updatePreview();
                    this.$watch('targetFilter', (val) => {
                        this.estimatedCount = this.tierCounts[val] ?? 0;
                    });
                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });
                },

                openCreateModal() {
                    this.createModalOpen = true;
                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });
                },

                closeCreateModal() {
                    if (this.submitting) return;
                    this.createModalOpen = false;
                },

                insertVar(v) {
                    const ta = document.getElementById('modalMsgTextarea');
                    if (!ta) return;
                    const start = ta.selectionStart;
                    const end = ta.selectionEnd;
                    this.message = this.message.substring(0, start) + v + this.message.substring(end);
                    this.$nextTick(() => {
                        ta.selectionStart = ta.selectionEnd = start + v.length;
                        ta.focus();
                        this.updatePreview();
                    });
                },

                updatePreview() {
                    this.previewMessage = this.message
                        .replace(/\{nama\}/g, 'Budi Santoso')
                        .replace(/\{poin\}/g, '1.250')
                        .replace(/\{tier\}/g, 'Gold')
                        .replace(/\{bisnis\}/g, {{ Js::from($business->name) }});
                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });
                },

                async submitBlast() {
                    if (this.submitting || !this.title.trim() || !this.message.trim()) return;
                    let confirmed = false;
                    if (window.AppAlert) {
                        confirmed = await AppAlert.confirm({
                            title: 'Kirim WhatsApp Broadcast?',
                            message: `Yakin ingin mengirim pesan massal ke ${this.estimatedCount.toLocaleString('id-ID')} pelanggan? Pesan akan didistribusikan di latar belakang secara aman.`,
                            type: 'info',
                            confirmText: 'Ya, Kirim Sekarang',
                            cancelText: 'Batal'
                        });
                    } else {
                        confirmed = confirm(`Yakin ingin mengirim pesan massal ke ${this.estimatedCount} pelanggan?`);
                    }
                    if (!confirmed) return;
                    this.submitting = true;
                    document.getElementById('modalBlastForm').submit();
                }
            };
        }
    </script>
@endpush
