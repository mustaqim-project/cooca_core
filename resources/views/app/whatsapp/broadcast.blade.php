@extends('layouts.app', [
    'title' => __('whatsapp.broadcast_title') . ' - ' . $business->name,
    'headerTitle' => __('whatsapp.broadcast_header_title'),
    'headerSubtitle' => __('whatsapp.broadcast_header_subtitle'),
])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="broadcastManager()">

        {{-- MODULE HEADER & PERSISTENT COMMUNICATION TABS --}}
        <x-module-header
            module="communication"
            :title="__('whatsapp.broadcast_title')"
            :subtitle="__('whatsapp.broadcast_subtitle')">
            <x-slot:actions>
                <a href="{{ route('whatsapp.logs.index') }}"
                    class="min-h-[44px] px-4 rounded-[12px] text-[13px] font-semibold text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all flex items-center justify-center gap-2 w-full sm:w-auto shrink-0 whitespace-nowrap">
                    <i data-lucide="history" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                    <span>{{ __('whatsapp.tab_logs') }}</span>
                </a>
                @if (\App\Support\Context::hasPermission('whatsapp.manage'))
                    <button type="button" @click="openCreateModal()"
                        class="min-h-[44px] px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-bold text-[13.5px] shadow-md shadow-[#007AFF]/25 flex items-center justify-center gap-2 transition-all active:scale-[0.98] w-full sm:w-auto shrink-0 whitespace-nowrap">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>{{ __('whatsapp.new_broadcast_btn') }}</span>
                    </button>
                @endif
            </x-slot:actions>
        </x-module-header>

        <x-module-tabs module="communication" />

        <!-- ===================================================== -->
        <!-- 2. STATS KPI GRID (4 METRICS - Apple HIG Style)        -->
        <!-- ===================================================== -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <!-- Metric 1: Total Kampanye -->
            <div
                class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[12px] font-semibold text-black/50 dark:text-white/50">{{ __('whatsapp.total_campaigns') }}</span>
                    <div
                        class="w-8 h-8 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.08] flex items-center justify-center text-black/60 dark:text-white/60">
                        <i data-lucide="layers" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-[22px] sm:text-[28px] font-bold tabular-nums text-black dark:text-white tracking-tight">
                    {{ number_format($stats['total_campaigns'], 0, ',', '.') }}
                </div>
                <div class="mt-2 text-[11px] text-black/40 dark:text-white/40">{{ __('whatsapp.kpi_broadcast_history') }}</div>
            </div>

            <!-- Metric 2: Pesan Terkirim (System Green) -->
            <div
                class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[12px] font-semibold text-black/50 dark:text-white/50">{{ __('whatsapp.total_sent') }}</span>
                    <div
                        class="w-8 h-8 rounded-[10px] bg-[#34C759]/12 flex items-center justify-center text-[#34C759] dark:text-[#30D158]">
                        <i data-lucide="send" class="w-4 h-4"></i>
                    </div>
                </div>
                <div
                    class="text-[22px] sm:text-[28px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] tracking-tight">
                    {{ number_format($stats['total_sent'], 0, ',', '.') }}
                </div>
                <div class="mt-2 text-[11px] text-black/40 dark:text-white/40">{{ __('whatsapp.kpi_delivered_to_wa') }}</div>
            </div>

            <!-- Metric 3: Total Target (System Indigo) -->
            <div
                class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[12px] font-semibold text-black/50 dark:text-white/50">{{ __('whatsapp.total_target') }}</span>
                    <div
                        class="w-8 h-8 rounded-[10px] bg-[#5856D6]/10 flex items-center justify-center text-[#5856D6] dark:text-[#5E5CE6]">
                        <i data-lucide="users" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-[22px] sm:text-[28px] font-bold tabular-nums text-black dark:text-white tracking-tight">
                    {{ number_format($stats['total_recipients'], 0, ',', '.') }}
                </div>
                <div class="mt-2 text-[11px] text-black/40 dark:text-white/40">{{ __('whatsapp.kpi_recipients_targeted') }}</div>
            </div>

            <!-- Metric 4: Keberhasilan (System Blue/Green) -->
            <div
                class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[12px] font-semibold text-black/50 dark:text-white/50">{{ __('whatsapp.success_rate') }}</span>
                    <div
                        class="w-8 h-8 rounded-[10px] bg-[#34C759]/12 flex items-center justify-center text-[#34C759] dark:text-[#30D158]">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    </div>
                </div>
                <div
                    class="text-[22px] sm:text-[28px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] tracking-tight">
                    {{ $stats['success_rate'] }}%
                </div>
                <div class="mt-2 text-[11px] text-black/40 dark:text-white/40">{{ __('whatsapp.kpi_delivery_rate_success') }}</div>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- 3. CAMPAIGNS DATA TABLE (Apple Dense Table)           -->
        <!-- ===================================================== -->
        <div
            class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] overflow-hidden transition-colors">
            <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <div>
                    <h2 class="text-[15px] font-bold text-black dark:text-white">{{ __('whatsapp.campaign_history_title') }}</h2>
                    <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('whatsapp.campaign_history_subtitle') }}</p>
                </div>
            </div>

            @if ($campaigns->isEmpty())
                <div class="text-center py-16 px-4 space-y-3">
                    <div
                        class="w-16 h-16 rounded-[18px] bg-black/[0.04] dark:bg-white/[0.06] text-black/30 dark:text-white/30 flex items-center justify-center mx-auto border border-black/5 dark:border-white/10">
                        <i data-lucide="megaphone-off" class="w-8 h-8"></i>
                    </div>
                    <div>
                        <h3 class="text-black dark:text-white font-bold text-[16px]">{{ __('whatsapp.empty_campaigns_title') }}</h3>
                        <p class="text-black/50 dark:text-white/50 text-[13px] mt-1 max-w-sm mx-auto leading-relaxed">
                            {{ __('whatsapp.empty_campaigns_desc') }}
                        </p>
                    </div>
                    @if (\App\Support\Context::hasPermission('whatsapp.manage'))
                        <button type="button" @click="openCreateModal()"
                            class="inline-flex items-center gap-2 mt-3 min-h-[44px] px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-bold text-[13px] shadow-md shadow-[#007AFF]/25 transition-all active:scale-[0.98]">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>{{ __('whatsapp.create_first_broadcast_btn') }}</span>
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
                                <th class="px-5 py-3">{{ __('whatsapp.campaign_name') }}</th>
                                <th class="px-4 py-3">{{ __('whatsapp.target_segment') }}</th>
                                <th class="px-4 py-3 text-center">{{ __('whatsapp.total_recipients') }}</th>
                                <th class="px-4 py-3 text-center">{{ __('whatsapp.col_sent') }}</th>
                                <th class="px-4 py-3 text-center">{{ __('whatsapp.status_label') }}</th>
                                <th class="px-4 py-3">{{ __('whatsapp.sent_date') }}</th>
                                <th class="px-5 py-3 text-right">{{ __('whatsapp.action_label') }}</th>
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
                                            {{ $campaign->target_filter === 'all' ? __('whatsapp.filter_all_members') : ucfirst($campaign->target_filter) }}
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
                                                ({{ $campaign->total_failed }} {{ __('whatsapp.status_failed_lower') }})
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        @php
                                            $statusBadge = match ($campaign->status) {
                                                'completed' => [
                                                    'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]',
                                                    __('whatsapp.status_completed'),
                                                    'bg-[#34C759]',
                                                ],
                                                'processing' => [
                                                    'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]',
                                                    __('whatsapp.status_processing'),
                                                    'bg-[#FF9500]',
                                                ],
                                                'failed' => [
                                                    'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]',
                                                    __('whatsapp.status_failed'),
                                                    'bg-[#FF3B30]',
                                                ],
                                                default => [
                                                    'bg-black/6 dark:bg-white/8 text-black/60 dark:text-white/60',
                                                    __('whatsapp.status_draft'),
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
                                            class="min-h-[36px] sm:min-h-[32px] px-3.5 rounded-[8px] text-[12px] font-semibold text-[#007AFF] hover:bg-[#007AFF]/10 transition-colors inline-flex items-center gap-1">
                                            <span>{{ __('whatsapp.view_detail_action') }}</span>
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
                                    __('whatsapp.status_completed'),
                                    'bg-[#34C759]',
                                ],
                                'processing' => [
                                    'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]',
                                    __('whatsapp.status_processing'),
                                    'bg-[#FF9500]',
                                ],
                                'failed' => [
                                    'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]',
                                    __('whatsapp.status_failed'),
                                    'bg-[#FF3B30]',
                                ],
                                default => [
                                    'bg-black/6 dark:bg-white/8 text-black/60 dark:text-white/60',
                                    __('whatsapp.status_draft'),
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
                                        {{ $campaign->target_filter === 'all' ? __('whatsapp.filter_all_members') : ucfirst($campaign->target_filter) }}
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
                                        class="text-[10px] uppercase font-semibold text-black/40 dark:text-white/40 block">{{ __('whatsapp.col_sent') }}</span>
                                    <span
                                        class="font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">{{ number_format($campaign->total_sent, 0, ',', '.') }}</span>
                                    <span class="text-black/40 dark:text-white/40 text-[11px]">/
                                        {{ number_format($campaign->total_recipients, 0, ',', '.') }}</span>
                                    @if ($campaign->total_failed > 0)
                                        <span
                                            class="text-[#FF3B30] dark:text-[#FF453A] font-medium text-[10.5px]">({{ $campaign->total_failed }}
                                            {{ __('whatsapp.status_failed_lower') }})</span>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <span
                                        class="text-[10px] uppercase font-semibold text-black/40 dark:text-white/40 block">{{ __('whatsapp.sent_date') }}</span>
                                    <span
                                        class="text-black/50 dark:text-white/50 text-[11px] tabular-nums">{{ $campaign->created_at->format('d/m/y H:i') }}</span>
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-end pt-1 border-t border-black/[0.04] dark:border-white/[0.06]">
                                <a href="{{ route('whatsapp.broadcast.show', $campaign) }}"
                                    class="min-h-[44px] px-4 rounded-[10px] text-[13px] font-bold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition-colors inline-flex items-center gap-1">
                                    <span>{{ __('whatsapp.view_detail_report') }}</span>
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
        <template x-teleport="body">
            <div x-show="createModalOpen" x-cloak
                class="fixed inset-0 z-[200] overflow-y-auto"
                @keydown.escape.window="closeCreateModal()">

                <!-- Backdrop Overlay: Solid dark overlay (no backdrop-blur on fullscreen layer to eliminate Chromium child dialog blur defect) -->
                <div x-show="createModalOpen"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="closeCreateModal()"
                    class="fixed inset-0 bg-black/70 dark:bg-black/85 transition-opacity"
                    aria-hidden="true"></div>

                <!-- Centering Wrapper -->
                <div class="min-h-full flex items-end sm:items-center justify-center p-0 sm:p-4 lg:p-6 text-center">
                    <!-- XXL Bento Canvas Container -->
                    <div x-show="createModalOpen"
                        x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95 translate-y-4 sm:translate-y-0"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 translate-y-4 sm:translate-y-0"
                        role="dialog" aria-modal="true" aria-labelledby="broadcastModalTitle"
                        class="relative w-full max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-[1350px] bg-[#F2F2F7] dark:bg-[#121212] sm:rounded-[24px] rounded-t-[28px] max-h-[95vh] sm:max-h-[92vh] flex flex-col overflow-hidden shadow-2xl border border-black/10 dark:border-white/10 z-10 text-left">

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
                            <h2 id="broadcastModalTitle" class="text-[17px] sm:text-[19px] font-bold text-black dark:text-white tracking-tight">
                                {{ __('whatsapp.modal_create_title') }}
                            </h2>
                            <p class="text-[12px] text-black/50 dark:text-white/50">
                                {{ __('whatsapp.modal_create_subtitle') }}
                            </p>
                        </div>
                    </div>

                    <button type="button" @click="closeCreateModal()"
                        aria-label="{{ __('whatsapp.close_btn') }}"
                        class="min-w-[44px] min-h-[44px] w-10 h-10 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.15] text-black/60 dark:text-white/60 flex items-center justify-center transition active:scale-[0.96]">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </header>

                <!-- Scrollable Body (Multi-Column Bento Grid) -->
                <div class="flex-1 overflow-y-auto p-5 sm:p-7 space-y-6">
                    @php
                        $isWaConnected = ($whatsAppAccount && $whatsAppAccount->isConnected()) || ($waSession && $waSession->isConnected());

                        $templateCode = $business->template_code ?? 'retail_general';
                        $isFnb        = str_starts_with($templateCode, 'fnb_');
                        $isWorkshop   = $templateCode === 'service_workshop';
                        $isLaundry    = $templateCode === 'service_laundry';
                        $isMfg        = str_starts_with($templateCode, 'mfg_');
                        $isContractor = $templateCode === 'service_contractor';
                        $isPharmacy   = $templateCode === 'retail_pharmacy';

                        $contextVars = match (true) {
                            $isFnb => ['{nama}', '{poin}', '{meja}', '{bisnis}'],
                            $isWorkshop => ['{nama}', '{nopol}', '{servis_terakhir}', '{bisnis}'],
                            $isLaundry => ['{nama}', '{no_rak}', '{berat_kg}', '{bisnis}'],
                            $isMfg => ['{nama}', '{no_spk}', '{produk}', '{bisnis}'],
                            $isContractor => ['{nama}', '{proyek}', '{termin}', '{bisnis}'],
                            $isPharmacy => ['{nama}', '{no_resep}', '{bisnis}'],
                            default => ['{nama}', '{poin}', '{tier}', '{bisnis}'],
                        };

                        $contextPlaceholder = match (true) {
                            $isFnb => "Halo {nama},\nAda promo jam santai spesial dari {$business->name}!\n\nTunjukkan pesan ini di {meja} untuk mendapatkan bonus dessert pilihan Anda.",
                            $isWorkshop => "Halo {nama},\nKendaraan Anda ({nopol}) sudah saatnya servis berkala di {$business->name}!\n\nServis terakhir: {servis_terakhir}. Booking jadwal sekarang untuk bonus cuci gratis.",
                            $isLaundry => "Halo {nama},\nCucian Anda di {$business->name} (Rak: {no_rak}, Berat: {berat_kg}) sudah selesai disetrika rapi & siap diambil!\n\nTerima kasih.",
                            $isMfg => "Halo {nama},\nPesanan produksi nomor {no_spk} ({produk}) telah selesai diproses di {$business->name} dan siap untuk tahap pengiriman.",
                            $isContractor => "Yth. Bapak/Ibu {nama},\nProgress pekerjaan proyek {proyek} dari {$business->name} telah mencapai tahap {termin}.\n\nTerima kasih atas kerja samanya.",
                            $isPharmacy => "Halo {nama},\nKebutuhan multivitamin & suplemen harian Anda tersedia lengkap di {$business->name}!\n\nNikmati diskon 15% untuk paket kesehatan keluarga pekan ini.",
                            default => "Halo {nama},\nAda promo spesial dari {$business->name} untuk tier {tier}!\n\nDapatkan diskon 20% khusus hari ini. Tunjukkan pesan ini ke kasir.",
                        };
                    @endphp

                    {{-- Quiet Hours Warning (Peringatan Jam Istirahat 21:00 - 08:00 WIB) --}}
                    <template x-if="isQuietHours">
                        <div class="p-4 rounded-[16px] bg-[#5856D6]/10 border border-[#5856D6]/20 text-[#5856D6] dark:text-[#5E5CE6] text-[12.5px] flex items-center gap-3">
                            <i data-lucide="moon" class="w-5 h-5 shrink-0 text-[#5856D6] dark:text-[#5E5CE6]"></i>
                            <div>
                                <strong class="font-bold">{!! __('whatsapp.quiet_hours_warning') !!}:</strong>
                                <p class="text-[12px] text-black/70 dark:text-white/70 mt-0.5 leading-relaxed">
                                    {{ __('whatsapp.quiet_hours_desc') }}
                                </p>
                            </div>
                        </div>
                    </template>

                    {{-- Meta Health Policy Warning for Pharmacy --}}
                    @if ($isPharmacy)
                        <div class="p-4 rounded-[16px] bg-[#FF9500]/12 border border-[#FF9500]/25 text-[#B25E00] dark:text-[#FF9F0A] text-[12.5px] space-y-1">
                            <div class="font-bold flex items-center gap-1.5">
                                <i data-lucide="shield-alert" class="w-4 h-4"></i>
                                <span>{{ __('whatsapp.pharmacy_policy_warning') }}</span>
                            </div>
                            <p class="leading-relaxed">
                                {{ __('whatsapp.pharmacy_policy_desc') }}
                            </p>
                        </div>
                    @endif

                    @if (! $isWaConnected)
                        <div class="p-4 rounded-[16px] bg-[#FF9500]/12 border border-[#FF9500]/20 text-[#B25E00] dark:text-[#FF9F0A] text-[13px] font-medium flex items-center gap-3">
                            <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0 text-[#FF9500]"></i>
                            <div>
                                <strong class="font-bold">{{ __('whatsapp.modal_wa_not_connected_title') }}</strong>
                                <span class="ml-1">{{ __('whatsapp.modal_wa_not_connected_desc') }}</span>
                            </div>
                        </div>
                    @else
                        <div class="p-4 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] text-[12.5px] font-medium flex items-center gap-3">
                            <i data-lucide="shield-check" class="w-4 h-4 shrink-0 text-[#34C759]"></i>
                            <div>
                                <span class="font-bold">{{ __('whatsapp.modal_wa_connected_title') }}</span>
                                <span class="ml-1 text-black/70 dark:text-white/70">{{ __('whatsapp.modal_wa_connected_desc', ['phone' => $whatsAppAccount?->display_phone_number ?? __('whatsapp.store_phone_fallback')]) }}</span>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('whatsapp.broadcast.store') }}" method="POST" id="modalBlastForm"
                        @submit.prevent="submitBlast">
                        @csrf

                        {{-- Mode Selector (Apple HIG Segmented Control) --}}
                        <div class="p-1.5 bg-black/[0.05] dark:bg-white/[0.07] rounded-[16px] flex flex-col sm:flex-row items-center gap-1.5 shadow-inner mb-6">
                            <button type="button" @click="setBroadcastMode('template')"
                                :class="broadcastMode === 'template' ? 'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                                class="w-full sm:flex-1 min-h-[44px] px-4 rounded-[12px] text-[13px] flex items-center justify-center gap-2 transition-all">
                                <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759]"></i>
                                <span>{{ __('whatsapp.mode_template_title') }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">{{ __('whatsapp.recommended_badge') }}</span>
                            </button>
                            <button type="button" @click="setBroadcastMode('free_text')"
                                :class="broadcastMode === 'free_text' ? 'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                                class="w-full sm:flex-1 min-h-[44px] px-4 rounded-[12px] text-[13px] flex items-center justify-center gap-2 transition-all">
                                <i data-lucide="message-square" class="w-4 h-4 text-[#FF9500]"></i>
                                <span>{{ __('whatsapp.mode_freetext_title') }}</span>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                            <!-- LEFT FORM COLUMN (7 cols) -->
                            <div class="lg:col-span-7 space-y-5">

                                <!-- Card: Identitas Kampanye -->
                                <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 space-y-3">
                                    <label class="block text-[12.5px] font-bold text-black/70 dark:text-white/70">{{ __('whatsapp.field_title_label') }} *</label>
                                    <input type="text" name="title" x-model="title" required
                                        placeholder="{{ __('whatsapp.field_title_placeholder') }}"
                                        class="w-full min-h-[44px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[14px] font-medium text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-colors">
                                </div>

                                <!-- Card: Target Audiens (Membership Tiers & Multi-Outlet Targeting) -->
                                <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 space-y-4">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-[12.5px] font-bold text-black/70 dark:text-white/70">{{ __('whatsapp.field_target_label') }}</label>
                                        <span class="text-[12px] font-bold text-[#007AFF] tabular-nums"
                                            x-text="estimatedCount.toLocaleString(localeCode) + ' ' + {{ Js::from(__('whatsapp.contacts_ready')) }}"></span>
                                    </div>

                                    {{-- Group 1: General & Membership Tiers --}}
                                    <div>
                                        <span class="text-[11px] font-semibold text-black/40 dark:text-white/40 uppercase tracking-wide block mb-2">{{ __('whatsapp.target_group_memberships') }}</span>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                            @php
                                                $modalFilters = [
                                                    'all' => ['label' => __('whatsapp.filter_all_members'), 'count' => $customerCount],
                                                    'bronze' => ['label' => __('whatsapp.filter_bronze'), 'count' => $tierCounts['bronze'] ?? 0],
                                                    'silver' => ['label' => __('whatsapp.filter_silver'), 'count' => $tierCounts['silver'] ?? 0],
                                                    'gold' => ['label' => __('whatsapp.filter_gold'), 'count' => $tierCounts['gold'] ?? 0],
                                                    'vip' => ['label' => __('whatsapp.filter_vip'), 'count' => $tierCounts['vip'] ?? 0],
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
                                                            {{ number_format($filter['count'], 0, ',', '.') }} {{ __('whatsapp.contacts_unit') }}
                                                        </div>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Group 2: Multi-Outlet / Multi-Branch Targeting --}}
                                    @if (!empty($locations) && $locations->isNotEmpty())
                                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                                            <span class="text-[11px] font-semibold text-black/40 dark:text-white/40 uppercase tracking-wide block mb-2 flex items-center gap-1.5">
                                                <i data-lucide="store" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                                <span>{{ __('whatsapp.target_group_outlets') }}</span>
                                            </span>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-52 overflow-y-auto pr-1 overscroll-contain">
                                                @foreach ($locations as $loc)
                                                    @php
                                                        $locFilterKey = 'outlet:' . $loc->id;
                                                        $locCount = $outletCounts[$loc->id] ?? 0;
                                                    @endphp
                                                    <label class="cursor-pointer select-none">
                                                        <input type="radio" name="target_filter" value="{{ $locFilterKey }}"
                                                            x-model="targetFilter" class="sr-only">
                                                        <div class="p-3 rounded-[12px] border transition-all flex items-center justify-between gap-2 min-h-[48px]"
                                                            :class="targetFilter === '{{ $locFilterKey }}'
                                                                ? 'border-[#007AFF] bg-[#007AFF]/10 text-[#007AFF] font-bold shadow-sm'
                                                                : 'border-black/5 dark:border-white/5 bg-black/[0.02] dark:bg-white/[0.03] text-black/70 dark:text-white/70 hover:border-black/10'">
                                                            <div class="truncate text-left">
                                                                <div class="text-[13px] font-medium truncate">{{ $loc->name }}</div>
                                                                <div class="text-[11px] opacity-60 capitalize">{{ $loc->type ?? 'Outlet' }}</div>
                                                            </div>
                                                            <div class="text-right shrink-0">
                                                                <span class="text-[11.5px] font-bold tabular-nums">
                                                                    {{ number_format($locCount, 0, ',', '.') }}
                                                                </span>
                                                                <span class="text-[10px] opacity-70 block">{{ __('whatsapp.contacts_unit') }}</span>
                                                            </div>
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- ============================================================== -->
                                <!-- MODE 1: TEMPLATE RESMI META (ANTI-BLOKIR SYSTEM)               -->
                                <!-- ============================================================== -->
                                <div x-show="broadcastMode === 'template'" class="space-y-5">
                                    <input type="hidden" name="template_name" :value="selectedTemplateName">
                                    <input type="hidden" name="template_language" value="id">
                                    <input type="hidden" name="template_params[offer]" :value="templateParams.offer">
                                    <input type="hidden" name="template_params[voucher_code]" :value="templateParams.voucher_code">
                                    <input type="hidden" name="template_params[valid_until]" :value="templateParams.valid_until">
                                    <input type="hidden" name="message" :value="message">

                                    {{-- Card: Pilih Template --}}
                                    <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <label class="block text-[12.5px] font-bold text-black/70 dark:text-white/70">{{ __('whatsapp.field_select_template_label') }} *</label>
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#34C759]">
                                                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                                <span>{{ __('whatsapp.safe_24h_badge') }}</span>
                                            </span>
                                        </div>

                                        <select x-model="selectedTemplateName" @change="onTemplateChange()"
                                            class="w-full min-h-[44px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] px-3.5 text-[14px] font-semibold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                                            @if (isset($approvedTemplates) && $approvedTemplates->isNotEmpty())
                                                @foreach ($approvedTemplates as $tpl)
                                                    <option value="{{ $tpl->name }}">{{ $tpl->name }} ({{ strtoupper($tpl->category) }}) - {{ $tpl->status }}</option>
                                                @endforeach
                                            @else
                                                <option value="cooca_promo_broadcast">cooca_promo_broadcast (MARKETING) - APPROVED</option>
                                                <option value="cooca_reservation_reminder">cooca_reservation_reminder (UTILITY) - APPROVED</option>
                                                <option value="cooca_marketplace_receipt">cooca_marketplace_receipt (UTILITY) - APPROVED</option>
                                                <option value="cooca_shipping_tracking">cooca_shipping_tracking (UTILITY) - APPROVED</option>
                                                <option value="cooca_cart_reminder">cooca_cart_reminder (MARKETING) - APPROVED</option>
                                                <option value="cooca_customer_welcome">cooca_customer_welcome (MARKETING) - APPROVED</option>
                                                <option value="cooca_order_status_update">cooca_order_status_update (UTILITY) - APPROVED</option>
                                            @endif
                                        </select>
                                        <p class="text-[11.5px] text-black/50 dark:text-white/50 leading-relaxed">
                                            {{ __('whatsapp.template_help_text') }}
                                        </p>
                                    </div>

                                    {{-- Card: Parameter Dinamis Template --}}
                                    <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 space-y-4">
                                        <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                                            <label class="block text-[13px] font-bold text-black dark:text-white">{{ __('whatsapp.template_vars_header') }}</label>
                                            <span class="text-[11.5px] font-mono text-black/45 dark:text-white/45">{{ __('whatsapp.template_language_label') }}</span>
                                        </div>

                                        <div class="space-y-3.5">
                                            <div>
                                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                                    {{ __('whatsapp.template_offer_label') }} *
                                                </label>
                                                <input type="text" x-model="templateParams.offer" @input="updateTemplatePreview()"
                                                    placeholder="{{ __('whatsapp.template_offer_placeholder') }}"
                                                    class="w-full min-h-[44px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] px-3.5 text-[14px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-colors">
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                                        {{ __('whatsapp.template_voucher_label') }} *
                                                    </label>
                                                    <input type="text" x-model="templateParams.voucher_code" @input="updateTemplatePreview()"
                                                        placeholder="{{ __('whatsapp.template_voucher_placeholder') }}"
                                                        class="w-full min-h-[44px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] px-3.5 text-[14px] font-mono font-bold uppercase text-[#007AFF] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-colors">
                                                </div>

                                                <div>
                                                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                                        {{ __('whatsapp.template_valid_label') }} *
                                                    </label>
                                                    <input type="text" x-model="templateParams.valid_until" @input="updateTemplatePreview()"
                                                        placeholder="{{ __('whatsapp.template_valid_placeholder') }}"
                                                        class="w-full min-h-[44px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] px-3.5 text-[14px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-colors">
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Anti-Blokir Assurance Badge --}}
                                        <div class="p-3.5 rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/25 text-[#248A3D] dark:text-[#30D158] text-[12px] flex items-start gap-2.5">
                                            <i data-lucide="shield-check" class="w-4 h-4 shrink-0 mt-0.5"></i>
                                            <div class="leading-relaxed">
                                                <strong>{{ __('whatsapp.anti_ban_active_title') }}</strong> {{ __('whatsapp.anti_ban_active_desc') }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Card: Banner URL -->
                                    <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 space-y-2">
                                        <label class="block text-[12.5px] font-bold text-black/70 dark:text-white/70">{{ __('whatsapp.field_media_label') }} <span class="text-black/40 dark:text-white/40 font-normal text-[11.5px]">{{ __('whatsapp.field_media_sublabel') }}</span></label>
                                        <input type="url" name="media_url" x-model="mediaUrl" @input="updateTemplatePreview()"
                                            placeholder="{{ __('whatsapp.field_media_placeholder') }}"
                                            class="w-full min-h-[44px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 placeholder:text-black/35 dark:placeholder:text-white/35 transition-colors">
                                    </div>
                                </div>

                                <!-- ============================================================== -->
                                <!-- MODE 2: TEKS BEBAS (HANYA UNTUK PELANGGAN AKTIF 24 JAM)        -->
                                <!-- ============================================================== -->
                                <div x-show="broadcastMode === 'free_text'" class="space-y-5">
                                    {{-- 24-Hour Policy Alert --}}
                                    <div class="p-4 rounded-[16px] bg-[#FF9500]/12 border border-[#FF9500]/25 text-[#B25E00] dark:text-[#FF9F0A] text-[12.5px] space-y-1">
                                        <div class="font-bold flex items-center gap-1.5">
                                            <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0"></i>
                                            <span>{{ __('whatsapp.window_24h_warning_title') }}</span>
                                        </div>
                                        <p class="leading-relaxed">
                                            {{ __('whatsapp.window_24h_warning_desc') }}
                                        </p>
                                    </div>

                                    <!-- Card: Pesan Bebas -->
                                    <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <label class="block text-[12.5px] font-bold text-black/70 dark:text-white/70">{{ __('whatsapp.field_free_text_label') }}</label>
                                        </div>

                                        <!-- Variable insertion chips (Context-Aware 20 Industri) -->
                                        <div class="flex flex-wrap items-center gap-1.5 pt-1">
                                            <span class="text-[11.5px] text-black/50 dark:text-white/50 font-semibold mr-1">{{ __('whatsapp.personal_tags_header') }}</span>
                                            @foreach ($contextVars as $var)
                                                <button type="button" @click="insertVar('{{ $var }}')"
                                                    class="min-h-[28px] px-2.5 rounded-[8px] bg-black/[0.05] hover:bg-black/[0.08] dark:bg-white/[0.08] dark:hover:bg-white/[0.12] text-black/80 dark:text-white/80 text-[12px] font-mono transition active:scale-[0.97]">
                                                    {{ $var }}
                                                </button>
                                            @endforeach
                                        </div>

                                        <textarea id="modalMsgTextarea" x-model="freeTextMessage" rows="5"
                                            placeholder="{{ $contextPlaceholder }}"
                                            @input="updateFreeTextPreview()"
                                            class="w-full bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] p-3.5 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 resize-none font-sans placeholder:text-black/35 dark:placeholder:text-white/35 leading-relaxed transition-colors"></textarea>

                                        <div class="flex items-center justify-between text-[11px] text-black/40 dark:text-white/40">
                                            <span>{{ __('whatsapp.formatting_hint') }}</span>
                                            <span x-text="freeTextMessage.length + ' / 2000 ' + {{ Js::from(__('whatsapp.chars_unit')) }}" class="tabular-nums font-semibold"></span>
                                        </div>
                                    </div>

                                    <!-- Card: Banner URL -->
                                    <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 space-y-2">
                                        <label class="block text-[12.5px] font-bold text-black/70 dark:text-white/70">{{ __('whatsapp.field_media_label') }} <span class="text-black/40 dark:text-white/40 font-normal text-[11.5px]">{{ __('whatsapp.field_media_sublabel') }}</span></label>
                                        <input type="url" x-model="mediaUrl" @input="updateFreeTextPreview()"
                                            placeholder="{{ __('whatsapp.field_media_placeholder') }}"
                                            class="w-full min-h-[44px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 placeholder:text-black/35 dark:placeholder:text-white/35 transition-colors">
                                    </div>
                                </div>

                            </div>

                            <!-- RIGHT PREVIEW COLUMN (5 cols) -->
                            <div class="lg:col-span-5">
                                <div class="lg:sticky lg:top-2 space-y-3">
                                    <div class="hidden lg:flex items-center justify-between text-[12px] font-bold text-black/50 dark:text-white/50 uppercase tracking-wide px-1">
                                        <span>{{ __('whatsapp.simulator_header') }}</span>
                                        <span class="text-[#34C759] lowercase font-semibold flex items-center gap-1" x-show="broadcastMode === 'template'">
                                            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('whatsapp.meta_verified_badge') }}</span>
                                        </span>
                                    </div>

                                    <!-- Mobile Toggle Simulator (Thumb-zone friendly) -->
                                    <button type="button" @click="showMobilePreview = !showMobilePreview; $nextTick(() => { if (window.lucide) lucide.createIcons(); })"
                                        class="lg:hidden w-full min-h-[44px] px-4 py-2.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 flex items-center justify-between text-[12.5px] font-semibold text-black dark:text-white transition active:scale-[0.98]">
                                        <span class="flex items-center gap-2">
                                            <i data-lucide="smartphone" class="w-4 h-4 text-[#007AFF]"></i>
                                            <span x-text="showMobilePreview ? '{{ __('whatsapp.hide_preview') }}' : '{{ __('whatsapp.show_preview') }}'"></span>
                                        </span>
                                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200" :class="showMobilePreview ? 'rotate-180' : ''"></i>
                                    </button>

                                    <!-- Phone Mockup Frame (Always on Desktop, Collapsible on Mobile) -->
                                    <div :class="showMobilePreview ? 'block' : 'hidden lg:block'" class="space-y-3">
                                        <div class="relative mx-auto max-w-[280px] rounded-[32px] bg-[#1C1C1E] border-[4px] border-black/80 dark:border-white/10 shadow-2xl overflow-hidden"
                                            style="height: 500px;">

                                        <!-- Top Notch -->
                                        <div class="absolute top-2 left-1/2 -translate-x-1/2 w-20 h-3.5 bg-black rounded-full z-20"></div>

                                        <!-- WA Header -->
                                        <div class="bg-[#075E54] px-3.5 pt-7 pb-2.5 flex items-center gap-2 shadow-sm text-white">
                                            <div class="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center text-white shrink-0">
                                                <i data-lucide="bot" class="w-3.5 h-3.5"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="text-[11.5px] font-bold text-white truncate flex items-center gap-1">
                                                    <span class="truncate">{{ $business->name }}</span>
                                                    <i data-lucide="check-circle" class="w-3 h-3 text-[#34C759] shrink-0"></i>
                                                </div>
                                                <div class="text-[9.5px] text-white/75 flex items-center gap-1">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                                    <span>{{ __('whatsapp.official_business_badge') }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Chat Message Area -->
                                        <div class="bg-[#ECE5DD] dark:bg-[#0b141a] h-full overflow-y-auto px-2.5 pt-2.5 pb-24">
                                            <div class="flex justify-end mb-2">
                                                <div class="max-w-[92%] rounded-[14px] rounded-tr-[3px] bg-[#DCF8C6] dark:bg-[#005c4b] p-3 shadow-sm text-black dark:text-white space-y-1.5">
                                                    
                                                    {{-- Meta Standard Badge in Preview --}}
                                                    <div x-show="broadcastMode === 'template'" class="flex items-center gap-1 px-1.5 py-0.5 rounded-[5px] bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] text-[9px] font-bold w-fit">
                                                        <i data-lucide="shield-check" class="w-2.5 h-2.5"></i>
                                                        <span x-text="selectedTemplateName || 'cooca_promo_broadcast'"></span>
                                                    </div>

                                                    <div x-show="mediaUrl" class="rounded-[8px] overflow-hidden bg-black/10">
                                                        <img :src="mediaUrl" alt="banner"
                                                            class="w-full max-h-28 object-cover rounded-[8px]"
                                                            onerror="this.style.display='none'">
                                                    </div>

                                                    <p class="text-[11.5px] whitespace-pre-wrap break-words leading-relaxed text-black/90 dark:text-white/95"
                                                        x-html="formatSimulatorText(previewMessage)"></p>

                                                    {{-- Opt-Out Footer Preview for Marketing Template --}}
                                                    <div x-show="broadcastMode === 'template'"
                                                        class="text-[9.5px] text-black/50 dark:text-white/60 pt-1 border-t border-black/10 dark:border-white/10 italic">
                                                        {{ __('whatsapp.opt_out_footer_text') }}
                                                    </div>

                                                    <div class="text-[9.5px] text-black/45 dark:text-white/60 text-right mt-1 flex items-center justify-end gap-1">
                                                        <span class="tabular-nums">{{ now()->format('H:i') }}</span>
                                                        <i data-lucide="check-check" class="w-3 h-3 text-[#007AFF]"></i>
                                                    </div>

                                                    {{-- Action Button Preview --}}
                                                    <div x-show="broadcastMode === 'template'" class="pt-1">
                                                        <div class="w-full py-1.5 px-3 rounded-[8px] bg-white dark:bg-[#1F2C34] text-center text-[10.5px] font-bold text-[#007AFF] shadow-xs flex items-center justify-center gap-1">
                                                            <i data-lucide="tag" class="w-3 h-3"></i>
                                                            <span>{{ __('whatsapp.claim_offer_btn') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @php
                                        $previewExampleNote = match (true) {
                                            $isFnb => __('whatsapp.preview_note_fnb'),
                                            $isWorkshop => __('whatsapp.preview_note_workshop'),
                                            $isLaundry => __('whatsapp.preview_note_laundry'),
                                            $isMfg => __('whatsapp.preview_note_mfg'),
                                            $isContractor => __('whatsapp.preview_note_contractor'),
                                            $isPharmacy => __('whatsapp.preview_note_pharmacy'),
                                            default => __('whatsapp.preview_note_default'),
                                        };
                                    @endphp
                                    <p class="text-center text-[11px] text-black/50 dark:text-white/50">
                                        {{ $previewExampleNote }}
                                    </p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>

                <!-- Sticky Footer Action Bar -->
                <footer
                    class="px-5 sm:px-7 py-4 bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-md border-t border-black/5 dark:border-white/10 flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0">
                    <div class="text-[12px] text-black/50 dark:text-white/50 hidden sm:block">
                        {{ __('whatsapp.total_recipients_computed') }} <strong class="text-black dark:text-white font-bold" x-text="estimatedCount.toLocaleString(localeCode) + ' ' + {{ Js::from(__('whatsapp.contacts_unit')) }}"></strong>
                    </div>

                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <button type="button" @click="closeCreateModal()"
                            class="min-h-[44px] px-4 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all w-full sm:w-auto">
                            {{ __('whatsapp.cancel_btn') }}
                        </button>

                        @if (\App\Support\Context::hasPermission('whatsapp.manage'))
                            <button type="button" @click="submitBlast()"
                                :disabled="submitting || !message.trim() || !title.trim()"
                                :class="submitting ? 'opacity-70 cursor-not-allowed' : ''"
                                class="min-h-[44px] px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-bold text-[13.5px] shadow-md shadow-[#007AFF]/25 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 transition-all active:scale-[0.98] w-full sm:w-auto">
                                <svg x-show="submitting" class="w-4 h-4 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <i data-lucide="send" x-show="!submitting" class="w-4 h-4"></i>
                                <span x-text="submitting ? {{ Js::from(__('whatsapp.scheduling_btn')) }} : ({{ Js::from(__('whatsapp.send_blast_to_btn_prefix')) }} + ' ' + estimatedCount.toLocaleString(localeCode) + ' ' + {{ Js::from(__('whatsapp.send_blast_to_btn_suffix')) }})"></span>
                            </button>
                        @endif
                    </div>
                </footer>

                    </div>
                </div>
            </div>
        </template>

    </div>
@endsection

@push('scripts')
    <script>
        function broadcastManager() {
            @php
                $mergedTargetCounts = $tierCounts + ['all' => $customerCount];
                if (!empty($locations)) {
                    foreach ($locations as $loc) {
                        $mergedTargetCounts['outlet:' . $loc->id] = $outletCounts[$loc->id] ?? 0;
                    }
                }
            @endphp
            return {
                createModalOpen: {{ (request()->boolean('open_composer') || request()->boolean('create')) ? 'true' : 'false' }},
                mobileModalView: 'editor',
                showMobilePreview: false,
                isQuietHours: false,
                broadcastMode: 'template',
                selectedTemplateName: 'cooca_promo_broadcast',
                localeCode: '{{ app()->getLocale() === 'en' ? 'en-US' : 'id-ID' }}',
                templateParams: {
                    offer: {{ Js::from(__('whatsapp.default_tpl_offer')) }},
                    voucher_code: 'HEMAT25',
                    valid_until: {{ Js::from(__('whatsapp.default_tpl_valid')) }}
                },
                freeTextMessage: '',
                title: {{ Js::from(__('whatsapp.default_campaign_title')) }},
                targetFilter: 'all',
                message: '',
                mediaUrl: '',
                previewMessage: '',
                submitting: false,
                estimatedCount: {{ $customerCount }},
                tierCounts: @json($mergedTargetCounts),

                init() {
                    const currentHour = new Date().getHours();
                    this.isQuietHours = (currentHour >= 21 || currentHour < 8);
                    this.updateTemplatePreview();
                    this.$watch('targetFilter', (val) => {
                        this.estimatedCount = this.tierCounts[val] ?? 0;
                    });
                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });
                },

                setBroadcastMode(mode) {
                    this.broadcastMode = mode;
                    if (mode === 'template') {
                        this.updateTemplatePreview();
                    } else {
                        if (!this.freeTextMessage) {
                            this.freeTextMessage = {{ Js::from(__('whatsapp.default_freetext_template', ['business' => $business->name])) }};
                        }
                        this.updateFreeTextPreview();
                    }
                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });
                },

                onTemplateChange() {
                    if (this.selectedTemplateName === 'cooca_customer_welcome') {
                        this.templateParams.offer = {{ Js::from(__('whatsapp.tpl_welcome_offer')) }};
                        this.templateParams.voucher_code = 'WELCOME';
                        this.templateParams.valid_until = {{ Js::from(__('whatsapp.tpl_welcome_valid')) }};
                    } else if (this.selectedTemplateName === 'cooca_order_status_update') {
                        this.templateParams.offer = {{ Js::from(__('whatsapp.tpl_order_offer')) }};
                        this.templateParams.voucher_code = 'SIAP_AMBIL';
                        this.templateParams.valid_until = 'ORD-2026/10/012';
                    } else if (this.selectedTemplateName === 'cooca_reservation_reminder') {
                        this.templateParams.offer = {{ Js::from(__('whatsapp.tpl_reservation_offer')) }};
                        this.templateParams.voucher_code = 'RSV-8821';
                        this.templateParams.valid_until = {{ Js::from(__('whatsapp.tpl_reservation_valid')) }};
                    } else if (this.selectedTemplateName === 'cooca_marketplace_receipt') {
                        this.templateParams.offer = {{ Js::from(__('whatsapp.tpl_receipt_offer')) }};
                        this.templateParams.voucher_code = 'ORD-2026/10/088';
                        this.templateParams.valid_until = {{ Js::from(__('whatsapp.tpl_receipt_valid')) }};
                    } else if (this.selectedTemplateName === 'cooca_shipping_tracking') {
                        this.templateParams.offer = {{ Js::from(__('whatsapp.tpl_shipping_offer')) }};
                        this.templateParams.voucher_code = 'JT9988221100';
                        this.templateParams.valid_until = 'ORD-2026/10/088';
                    } else if (this.selectedTemplateName === 'cooca_cart_reminder') {
                        this.templateParams.offer = {{ Js::from(__('whatsapp.tpl_cart_offer')) }};
                        this.templateParams.voucher_code = 'ONGKIRFREE';
                        this.templateParams.valid_until = {{ Js::from(__('whatsapp.tpl_cart_valid')) }};
                    } else {
                        this.templateParams.offer = {{ Js::from(__('whatsapp.default_tpl_offer')) }};
                        this.templateParams.voucher_code = 'PROMO25';
                        this.templateParams.valid_until = {{ Js::from(__('whatsapp.default_tpl_valid')) }};
                    }
                    this.updateTemplatePreview();
                },

                updateTemplatePreview() {
                    const bizName = {{ Js::from($business->name) }};
                    const offer = this.templateParams.offer || {{ Js::from(__('whatsapp.default_tpl_offer')) }};
                    const code = (this.templateParams.voucher_code || 'HEMAT').toUpperCase();
                    const valid = this.templateParams.valid_until || {{ Js::from(__('whatsapp.default_tpl_valid')) }};

                    if (this.selectedTemplateName === 'cooca_customer_welcome') {
                        this.previewMessage = {{ Js::from(__('whatsapp.tpl_welcome_preview', ['business' => ':business'])) }}.replace(':business', bizName);
                        this.message = {{ Js::from(__('whatsapp.tpl_welcome_message', ['business' => ':business'])) }}.replace(':business', bizName);
                    } else if (this.selectedTemplateName === 'cooca_order_status_update') {
                        this.previewMessage = {{ Js::from(__('whatsapp.tpl_order_preview', ['business' => ':business', 'offer' => ':offer'])) }}.replace(':business', bizName).replace(':offer', offer);
                        this.message = {{ Js::from(__('whatsapp.tpl_order_message', ['business' => ':business', 'offer' => ':offer'])) }}.replace(':business', bizName).replace(':offer', offer);
                    } else if (this.selectedTemplateName === 'cooca_reservation_reminder') {
                        this.previewMessage = {{ Js::from(__('whatsapp.tpl_reservation_preview', ['business' => ':business', 'code' => ':code', 'valid' => ':valid', 'offer' => ':offer'])) }}.replace(':business', bizName).replace(':code', code).replace(':valid', valid).replace(':offer', offer);
                        this.message = {{ Js::from(__('whatsapp.tpl_reservation_message', ['business' => ':business', 'code' => ':code', 'valid' => ':valid', 'offer' => ':offer'])) }}.replace(':business', bizName).replace(':code', code).replace(':valid', valid).replace(':offer', offer);
                    } else if (this.selectedTemplateName === 'cooca_marketplace_receipt') {
                        this.previewMessage = {{ Js::from(__('whatsapp.tpl_receipt_preview', ['business' => ':business', 'code' => ':code', 'offer' => ':offer', 'valid' => ':valid'])) }}.replace(':business', bizName).replace(':code', code).replace(':offer', offer).replace(':valid', valid);
                        this.message = {{ Js::from(__('whatsapp.tpl_receipt_message', ['business' => ':business', 'code' => ':code', 'offer' => ':offer', 'valid' => ':valid'])) }}.replace(':business', bizName).replace(':code', code).replace(':offer', offer).replace(':valid', valid);
                    } else if (this.selectedTemplateName === 'cooca_shipping_tracking') {
                        this.previewMessage = {{ Js::from(__('whatsapp.tpl_shipping_preview', ['business' => ':business', 'valid' => ':valid', 'offer' => ':offer', 'code' => ':code'])) }}.replace(':business', bizName).replace(':valid', valid).replace(':offer', offer).replace(':code', code);
                        this.message = {{ Js::from(__('whatsapp.tpl_shipping_message', ['business' => ':business', 'valid' => ':valid', 'offer' => ':offer', 'code' => ':code'])) }}.replace(':business', bizName).replace(':valid', valid).replace(':offer', offer).replace(':code', code);
                    } else if (this.selectedTemplateName === 'cooca_cart_reminder') {
                        this.previewMessage = {{ Js::from(__('whatsapp.tpl_cart_preview', ['business' => ':business', 'offer' => ':offer', 'code' => ':code', 'valid' => ':valid'])) }}.replace(':business', bizName).replace(':offer', offer).replace(':code', code).replace(':valid', valid);
                        this.message = {{ Js::from(__('whatsapp.tpl_cart_message', ['business' => ':business', 'offer' => ':offer', 'code' => ':code', 'valid' => ':valid'])) }}.replace(':business', bizName).replace(':offer', offer).replace(':code', code).replace(':valid', valid);
                    } else {
                        // Default cooca_promo_broadcast
                        this.previewMessage = {{ Js::from(__('whatsapp.tpl_promo_preview', ['business' => ':business', 'offer' => ':offer', 'code' => ':code', 'valid' => ':valid'])) }}.replace(':business', bizName).replace(':offer', offer).replace(':code', code).replace(':valid', valid);
                        this.message = {{ Js::from(__('whatsapp.tpl_promo_message', ['business' => ':business', 'offer' => ':offer', 'code' => ':code', 'valid' => ':valid'])) }}.replace(':business', bizName).replace(':offer', offer).replace(':code', code).replace(':valid', valid);
                    }

                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });
                },

                updateFreeTextPreview() {
                    this.message = this.freeTextMessage;
                    this.previewMessage = this.freeTextMessage
                        .replace(/\{nama\}/g, 'Budi Santoso')
                        .replace(/\{poin\}/g, '1.250')
                        .replace(/\{tier\}/g, 'Gold')
                        .replace(/\{bisnis\}/g, {{ Js::from($business->name) }})
                        .replace(/\{meja\}/g, 'Meja 08')
                        .replace(/\{nopol\}/g, 'B 1234 XYZ')
                        .replace(/\{servis_terakhir\}/g, 'Ganti Oli & Filter')
                        .replace(/\{no_rak\}/g, 'RAK-B3')
                        .replace(/\{berat_kg\}/g, '4.5 kg')
                        .replace(/\{no_spk\}/g, 'SPK-2026/09/042')
                        .replace(/\{produk\}/g, 'Kemeja Katun Bordir')
                        .replace(/\{proyek\}/g, 'Renovasi Ruko Blok A')
                        .replace(/\{termin\}/g, 'Termin 2')
                        .replace(/\{no_resep\}/g, 'RSP-8821');

                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });
                },

                formatSimulatorText(text) {
                    if (!text) return '<span class="text-black/35 dark:text-white/35 italic">' + {{ Js::from(__('whatsapp.simulator_empty_placeholder')) }} + '</span>';
                    const escaped = String(text)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;');
                    return escaped
                        .replace(/\*(.*?)\*/g, '<strong class="font-bold text-black dark:text-white">$1</strong>')
                        .replace(/_(.*?)_/g, '<em class="italic opacity-90">$1</em>');
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
                    this.freeTextMessage = this.freeTextMessage.substring(0, start) + v + this.freeTextMessage.substring(end);
                    this.$nextTick(() => {
                        ta.selectionStart = ta.selectionEnd = start + v.length;
                        ta.focus();
                        this.updateFreeTextPreview();
                    });
                },

                async submitBlast() {
                    if (this.submitting || !this.title.trim()) return;

                    if (this.broadcastMode === 'template') {
                        if (!this.templateParams.offer.trim() || !this.templateParams.voucher_code.trim()) {
                            alert({{ Js::from(__('whatsapp.alert_template_fields_required')) }});
                            return;
                        }
                    } else {
                        if (!this.freeTextMessage.trim()) {
                            alert({{ Js::from(__('whatsapp.alert_freetext_field_required')) }});
                            return;
                        }
                    }

                    let confirmed = false;
                    const modeDesc = this.broadcastMode === 'template' 
                        ? {{ Js::from(__('whatsapp.mode_template_title')) }} 
                        : {{ Js::from(__('whatsapp.mode_freetext_title')) }};

                    const msgTemplate = {{ Js::from(__('whatsapp.confirm_send_broadcast_msg')) }};
                    const confirmMsg = msgTemplate.replace(':mode', modeDesc).replace(':count', this.estimatedCount.toLocaleString(this.localeCode));

                    if (window.AppAlert) {
                        confirmed = await AppAlert.confirm({
                            title: {{ Js::from(__('whatsapp.confirm_send_broadcast_title')) }},
                            message: confirmMsg,
                            type: 'info',
                            confirmText: {{ Js::from(__('whatsapp.confirm_send_now')) }},
                            cancelText: {{ Js::from(__('whatsapp.cancel_btn')) }}
                        });
                    } else {
                        confirmed = confirm(confirmMsg);
                    }
                    if (!confirmed) return;
                    this.submitting = true;
                    document.getElementById('modalBlastForm').submit();
                }
            };
        }
    </script>
@endpush
