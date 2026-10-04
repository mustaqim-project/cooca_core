@extends('layouts.app', [
    'title' => __('whatsapp.detail_title', ['title' => $campaign->title]) . ' - ' . $business->name,
    'headerTitle' => __('whatsapp.detail_header_title'),
    'headerSubtitle' => __('whatsapp.detail_header_subtitle'),
])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="broadcastDetail()">

        <!-- ========================================== -->
        <!-- 0. BREADCRUMB BAR (APPLE MINIMALIST)       -->
        <!-- ========================================== -->
        <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 py-0.5 whitespace-nowrap print:hidden"
            aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors font-medium">{{ __('navigation.dashboard') }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-black/30 dark:text-white/30"></i>
            <a href="{{ route('whatsapp.index') }}" class="hover:text-[#007AFF] transition-colors font-medium">{{ __('whatsapp.title') }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-black/30 dark:text-white/30"></i>
            <a href="{{ route('whatsapp.broadcast.index') }}" class="hover:text-[#007AFF] transition-colors font-medium">{{ __('whatsapp.tab_broadcast') }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-black/30 dark:text-white/30"></i>
            <span class="text-black/80 dark:text-white/80 font-medium">{{ __('whatsapp.breadcrumb_detail') }}</span>
        </nav>

        <!-- ===================================================== -->
        <!-- 1. TOOLBAR & ACTION HUB (macOS Sonoma Style)           -->
        <!-- ===================================================== -->
        <header
            class="rounded-[20px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 flex items-center justify-between gap-4 flex-wrap transition-colors shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
            <div class="flex items-center gap-3">
                <a href="{{ route('whatsapp.broadcast.index') }}"
                    class="min-h-[44px] min-w-[44px] rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black/70 dark:text-white/70 flex items-center justify-center transition active:scale-[0.98]"
                    title="{{ __('whatsapp.back_to_broadcast_list') }}">
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
                                    __('whatsapp.status_completed'),
                                    'bg-[#34C759]',
                                ],
                                'processing' => [
                                    'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A] border-[#FF9500]/20',
                                    __('whatsapp.status_processing'),
                                    'bg-[#FF9500]',
                                ],
                                'failed' => [
                                    'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A] border-[#FF3B30]/20',
                                    __('whatsapp.status_failed'),
                                    'bg-[#FF3B30]',
                                ],
                                default => [
                                    'bg-black/6 dark:bg-white/8 text-black/60 dark:text-white/60 border-black/10',
                                    __('whatsapp.status_draft'),
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
                    @php
                        $targetLabel = match (true) {
                            $campaign->target_filter === 'all' => __('whatsapp.filter_all_members'),
                            str_starts_with((string)$campaign->target_filter, 'outlet:') => (function() use ($campaign, $business) {
                                $locId = substr($campaign->target_filter, 7);
                                $loc = \App\Models\Location::where('business_id', $business->id)->where('id', $locId)->first();
                                return __('whatsapp.target_outlet_badge', ['name' => $loc?->name ?? 'Outlet']);
                            })(),
                            default => ucfirst($campaign->target_filter),
                        };
                    @endphp
                    <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">
                        {{ __('whatsapp.created_on', ['date' => $campaign->created_at->format('d M Y, H:i')]) }} &bull; {{ __('whatsapp.target_label') }}: <span
                            class="font-bold text-black/70 dark:text-white/70">{{ $targetLabel }}</span>
                    </p>
                </div>
            </div>

            @if (\App\Support\Context::hasPermission('whatsapp.manage'))
                <div class="flex items-center gap-2">
                    <a href="{{ route('whatsapp.broadcast.index', ['open_composer' => 1]) }}"
                        class="min-h-[44px] px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-bold text-[13px] shadow-md shadow-[#007AFF]/25 flex items-center gap-2 transition-all active:scale-[0.98]">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>{{ __('whatsapp.create_new_broadcast_btn') }}</span>
                    </a>
                </div>
            @endif
        </header>

        <!-- Live Processing Alert (When Background Queue Is Running) -->
        <template x-if="status === 'processing'">
            <div class="p-4 rounded-[16px] bg-[#FF9500]/12 border border-[#FF9500]/25 text-[#B25E00] dark:text-[#FF9F0A] text-[13px] flex items-center justify-between gap-3 flex-wrap sm:flex-nowrap">
                <div class="flex items-center gap-3">
                    <i data-lucide="loader-2" class="w-5 h-5 shrink-0 animate-spin text-[#FF9500]"></i>
                    <div>
                        <strong class="font-bold">{{ __('whatsapp.processing_alert_title') }}</strong>
                        <p class="text-[12px] text-black/60 dark:text-white/60 mt-0.5">{{ __('whatsapp.processing_alert_desc') }}</p>
                    </div>
                </div>
                <button type="button" @click="pollStatus()" class="min-h-[44px] px-4 rounded-[12px] bg-[#FF9500]/20 hover:bg-[#FF9500]/30 text-xs font-bold transition flex items-center justify-center shrink-0">
                    {{ __('whatsapp.refresh_now_btn') }}
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
                    <span class="text-[12px] font-semibold text-black/50 dark:text-white/50">{{ __('whatsapp.total_target') }}</span>
                    <div
                        class="w-8 h-8 rounded-[10px] bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6] flex items-center justify-center">
                        <i data-lucide="users" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-[22px] sm:text-[28px] font-bold tabular-nums text-black dark:text-white tracking-tight"
                    x-text="totalRecipients.toLocaleString(localeCode)">
                    {{ number_format($campaign->total_recipients, 0, ',', '.') }}
                </div>
                <div class="mt-2 text-[11px] text-black/40 dark:text-white/40">{{ __('whatsapp.kpi_registered_destination') }}</div>
            </div>

            <div
                class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[12px] font-semibold text-black/50 dark:text-white/50">{{ __('whatsapp.total_sent') }}</span>
                    <div
                        class="w-8 h-8 rounded-[10px] bg-[#34C759]/12 text-[#34C759] dark:text-[#30D158] flex items-center justify-center">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                    </div>
                </div>
                <div
                    class="text-[22px] sm:text-[28px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] tracking-tight"
                    x-text="totalSent.toLocaleString(localeCode)">
                    {{ number_format($campaign->total_sent, 0, ',', '.') }}
                </div>
                <div class="mt-2 text-[11px] text-black/40 dark:text-white/40">{{ __('whatsapp.kpi_delivered_success') }}</div>
            </div>

            <div
                class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[12px] font-semibold text-black/50 dark:text-white/50">{{ __('whatsapp.kpi_failed_sent') }}</span>
                    <div
                        class="w-8 h-8 rounded-[10px] bg-[#FF3B30]/12 text-[#FF3B30] dark:text-[#FF453A] flex items-center justify-center">
                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                    </div>
                </div>
                <div
                    class="text-[22px] sm:text-[28px] font-bold tabular-nums text-[#FF3B30] dark:text-[#FF453A] tracking-tight"
                    x-text="totalFailed.toLocaleString(localeCode)">
                    {{ number_format($campaign->total_failed, 0, ',', '.') }}
                </div>
                <div class="mt-2 text-[11px] text-black/40 dark:text-white/40">{{ __('whatsapp.kpi_failed_desc') }}</div>
            </div>

            <div
                class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[12px] font-semibold text-black/50 dark:text-white/50">{{ __('whatsapp.success_rate') }}</span>
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
                <div class="mt-2 text-[11px] text-black/40 dark:text-white/40">{{ __('whatsapp.kpi_delivery_rate_desc') }}</div>
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
                <h2 class="text-[15px] font-bold text-black dark:text-white">{{ __('whatsapp.sent_message_preview_title') }}</h2>
            </div>

            @if ($campaign->media_url)
                <div
                    class="p-2 bg-black/[0.02] dark:bg-white/[0.03] rounded-[14px] border border-black/5 dark:border-white/10 max-w-sm">
                    <img src="{{ $campaign->media_url }}" alt="{{ __('whatsapp.banner_alt') }}"
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
                    <h2 class="text-[15px] font-bold text-black dark:text-white">{{ __('whatsapp.recipients_audit_title') }}</h2>
                    <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('whatsapp.recipients_audit_subtitle') }}</p>
                </div>
                <span class="text-[12px] text-black/40 dark:text-white/40 font-semibold tabular-nums">
                    {{ __('whatsapp.page_x_of_y', ['current' => $recipients->currentPage(), 'total' => $recipients->lastPage()]) }}
                </span>
            </div>

            @if ($recipients->isEmpty())
                <div class="text-center py-12 text-black/40 dark:text-white/40 text-[13px]">
                    {{ __('whatsapp.empty_recipients_desc') }}
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[13.5px] min-w-[600px]">
                        <thead>
                            <tr
                                class="border-b border-black/5 dark:border-white/10 text-black/40 dark:text-white/40 uppercase tracking-wide text-[11px] font-semibold">
                                <th class="px-5 py-3">{{ __('whatsapp.recipient_name') }}</th>
                                <th class="px-4 py-3">{{ __('whatsapp.recipient_phone') }}</th>
                                <th class="px-4 py-3 text-center">{{ __('whatsapp.status_label') }}</th>
                                <th class="px-4 py-3">{{ __('whatsapp.recipient_sent_at') }}</th>
                                <th class="px-5 py-3">{{ __('whatsapp.recipient_error') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                            @php
                                $isOwner = \App\Support\Context::isOwner();
                            @endphp
                            @foreach ($recipients as $item)
                                @php
                                    $rawPhone = (string) $item->phone_number;
                                    $len = strlen($rawPhone);
                                    $maskedPhone = $len <= 7
                                        ? substr($rawPhone, 0, 2) . '••••' . substr($rawPhone, -2)
                                        : substr($rawPhone, 0, 4) . '••••' . substr($rawPhone, -4);
                                    $displayPhone = $isOwner ? $rawPhone : $maskedPhone;
                                @endphp
                                <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                    <td class="px-5 py-3.5 font-bold text-black dark:text-white">
                                        {{ $item->customer_name ?? ($item->customer?->name ?? __('whatsapp.default_customer_name')) }}
                                    </td>
                                    <td class="px-4 py-3.5 font-medium tabular-nums text-black/70 dark:text-white/70">
                                        <span class="font-mono text-[13px]">{{ $displayPhone }}</span>
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        @if ($item->status === 'sent')
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                                <i data-lucide="check" class="w-3 h-3"></i>
                                                <span>{{ __('whatsapp.col_sent') }}</span>
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                                <i data-lucide="x" class="w-3 h-3"></i>
                                                <span>{{ __('whatsapp.status_failed') }}</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-black/50 dark:text-white/50 tabular-nums text-[12px]">
                                        {{ $item->sent_at ? $item->sent_at->format('d/m/Y H:i:s') : '-' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-black/60 dark:text-white/60 text-[12px]">
                                        {{ $item->error_message ?: __('whatsapp.delivery_success_note') }}
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
                localeCode: '{{ app()->getLocale() === 'en' ? 'en-US' : 'id-ID' }}',
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
                            this.pollTimer = null;
                            if (window.CoocaBus) {
                                window.CoocaBus.emitDataMutated('whatsapp-broadcast', { id: '{{ $campaign->id }}', status: this.status });
                            }
                        }
                    } catch (e) {}
                }
            };
        }
    </script>
@endpush
