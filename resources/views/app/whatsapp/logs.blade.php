@extends('layouts.app', [
    'title' => __('whatsapp.logs_title') . ' - ' . $business->name,
    'headerTitle' => __('whatsapp.logs_title'),
    'headerSubtitle' => __('whatsapp.logs_subtitle'),
])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="whatsappLogs()">

        <!-- ========================================== -->
        <!-- 0. BREADCRUMB BAR (APPLE MINIMALIST)       -->
        <!-- ========================================== -->
        <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 py-0.5 whitespace-nowrap print:hidden"
            aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors font-medium">{{ __('whatsapp.breadcrumb_home') }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-black/30 dark:text-white/30"></i>
            <a href="{{ route('whatsapp.index') }}" class="hover:text-[#007AFF] transition-colors font-medium">{{ __('whatsapp.breadcrumb_gateway') }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-black/30 dark:text-white/30"></i>
            <span class="text-black/80 dark:text-white/80 font-medium">{{ __('whatsapp.breadcrumb_logs') }}</span>
        </nav>

        {{-- MODULE HEADER & PERSISTENT COMMUNICATION TABS --}}
        <x-module-header
            module="communication"
            title="{{ __('whatsapp.logs_title') }}"
            subtitle="{{ __('whatsapp.logs_subtitle') }}">
            <x-slot:actions>
                <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
                    <a href="{{ route('whatsapp.logs.export', request()->query()) }}"
                        class="min-h-[44px] px-4 rounded-[12px] text-[13px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.98] transition-all flex items-center justify-center gap-2 w-full sm:w-auto">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        <span>{{ __('whatsapp.export_excel') }}</span>
                    </a>
                    <button type="button" @click="openPruneModal = true"
                        class="min-h-[44px] px-4 rounded-[12px] text-[13px] font-semibold text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all flex items-center justify-center gap-2 w-full sm:w-auto">
                        <i data-lucide="database-zap" class="w-4 h-4 text-[#FF9500]"></i>
                        <span>{{ __('whatsapp.storage_pruning_btn') }}</span>
                    </button>
                </div>
            </x-slot:actions>
        </x-module-header>

        <x-module-tabs module="communication" />

        <!-- Apple-Style Server-Side Segmented Quick Filter -->
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div
                class="inline-flex p-1 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.08] border border-black/5 dark:border-white/5 overflow-x-auto text-[12.5px] font-medium w-full sm:w-auto">
                <a href="{{ route('whatsapp.logs.index') }}"
                    class="min-h-[44px] sm:min-h-[36px] px-4 rounded-[10px] transition-all whitespace-nowrap flex items-center justify-center {{ empty($type) ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                    {{ __('whatsapp.filter_all_logs') }}
                </a>
                <a href="{{ route('whatsapp.logs.index', ['type' => 'receipt']) }}"
                    class="min-h-[44px] sm:min-h-[36px] px-4 rounded-[10px] transition-all whitespace-nowrap flex items-center gap-1.5 {{ $type === 'receipt' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                    <span>{{ __('whatsapp.filter_receipt_logs') }}</span>
                </a>
                <a href="{{ route('whatsapp.logs.index', ['type' => 'broadcast']) }}"
                    class="min-h-[44px] sm:min-h-[36px] px-4 rounded-[10px] transition-all whitespace-nowrap flex items-center gap-1.5 {{ $type === 'broadcast' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="megaphone" class="w-3.5 h-3.5"></i>
                    <span>{{ __('whatsapp.filter_broadcast_logs') }}</span>
                </a>
                <a href="{{ route('whatsapp.logs.index', ['type' => 'test']) }}"
                    class="min-h-[44px] sm:min-h-[36px] px-4 rounded-[10px] transition-all whitespace-nowrap flex items-center gap-1.5 {{ $type === 'test' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="wrench" class="w-3.5 h-3.5"></i>
                    <span>{{ __('whatsapp.filter_test_logs') }}</span>
                </a>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- 2. LOGS DATA TABLE (Apple Dense Table)                -->
        <!-- ===================================================== -->
        <div
            class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] overflow-hidden transition-colors">
            <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <div>
                    <h2 class="text-[15px] font-bold text-black dark:text-white">{{ __('whatsapp.logs_card_title') }}</h2>
                    <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('whatsapp.logs_card_subtitle') }}</p>
                </div>
                <span class="text-[12px] text-black/40 dark:text-white/40 font-semibold tabular-nums">
                    {{ __('whatsapp.page_x_of_y', ['current' => $logs->currentPage(), 'total' => $logs->lastPage()]) }}
                </span>
            </div>

            @if ($logs->isEmpty())
                <div class="text-center py-16 px-4 space-y-3">
                    <div
                        class="w-16 h-16 rounded-[18px] bg-black/[0.04] dark:bg-white/[0.06] text-black/30 dark:text-white/30 flex items-center justify-center mx-auto border border-black/5 dark:border-white/10">
                        <i data-lucide="inbox" class="w-8 h-8"></i>
                    </div>
                    <div>
                        <h3 class="text-black dark:text-white font-bold text-[16px]">{{ __('whatsapp.empty_logs_title') }}</h3>
                        <p class="text-black/50 dark:text-white/50 text-[13px] mt-1 max-w-sm mx-auto leading-relaxed">
                            {{ __('whatsapp.empty_logs_desc') }}
                        </p>
                    </div>
                    <a href="{{ route('whatsapp.index') }}"
                        class="inline-flex items-center gap-1.5 mt-2 text-[13px] font-bold text-[#007AFF] hover:underline min-h-[44px] px-3">
                        <span>{{ __('whatsapp.view_gateway_status') }}</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            @else
                {{-- Desktop Logs Table --}}
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left text-[13.5px]">
                        <thead>
                            <tr
                                class="border-b border-black/5 dark:border-white/10 text-black/40 dark:text-white/40 uppercase tracking-wide text-[11px] font-semibold">
                                <th class="px-5 py-3">{{ __('whatsapp.col_recipient') }}</th>
                                <th class="px-4 py-3">{{ __('whatsapp.col_type') }}</th>
                                <th class="px-4 py-3">{{ __('whatsapp.col_message_preview') }}</th>
                                <th class="px-4 py-3 text-center">{{ __('whatsapp.col_status') }}</th>
                                <th class="px-4 py-3">{{ __('whatsapp.col_created_at') }}</th>
                                <th class="px-5 py-3 text-right">{{ __('whatsapp.action_label') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                            @php
                                $isOwner = \App\Support\Context::isOwner();
                                $formatPhone = function (?string $phone) use ($isOwner): string {
                                    if (! $phone) return '-';
                                    if ($isOwner) return $phone;
                                    $raw = trim($phone);
                                    $len = strlen($raw);
                                    if ($len <= 7) {
                                        return substr($raw, 0, 2) . '••••' . substr($raw, -2);
                                    }
                                    return substr($raw, 0, 4) . '••••' . substr($raw, -4);
                                };
                            @endphp
                            @foreach ($logs as $log)
                                @php
                                    $displayPhone = $formatPhone($log->recipient_phone);
                                    $logJson = [
                                        'id' => $log->id,
                                        'recipient_name' => $log->recipient_name,
                                        'recipient_phone' => $displayPhone,
                                        'raw_phone' => $isOwner ? $log->recipient_phone : null,
                                        'type' => $log->type,
                                        'message' => $log->message,
                                        'status' => $log->status,
                                        'error_message' => $log->error_message,
                                        'sent_at' => $log->created_at->format('d F Y, H:i:s') . ' WIB',
                                    ];
                                @endphp
                                <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors cursor-pointer"
                                    x-show="filterType === 'all' || filterType === '{{ $log->type }}'"
                                    @click="inspectLog(@json($logJson))">
                                    <td class="px-5 py-3.5">
                                        <div class="font-bold text-black dark:text-white">{{ $log->recipient_name }}</div>
                                        <div class="text-black/50 dark:text-white/50 tabular-nums text-[12px] font-mono">{{ $displayPhone }}</div>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        @php
                                            $typeBadge = match ($log->type) {
                                                'receipt' => 'bg-[#30B0C7]/12 text-[#227D8E] dark:text-[#40C8E0]',
                                                'broadcast' => 'bg-[#AF52DE]/12 text-[#7C3AA6] dark:text-[#BF5AF2]',
                                                'test' => 'bg-[#007AFF]/12 text-[#0062CC] dark:text-[#0A84FF]',
                                                default => 'bg-black/6 dark:bg-white/8 text-black/60 dark:text-white/60',
                                            };
                                            $typeLabel = match ($log->type) {
                                                'receipt' => __('whatsapp.type_receipt'),
                                                'broadcast' => __('whatsapp.type_broadcast'),
                                                'test' => __('whatsapp.type_test'),
                                                default => ucfirst($log->type),
                                            };
                                        @endphp
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $typeBadge }}">
                                            {{ $typeLabel }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 max-w-xs">
                                        <div class="text-black/80 dark:text-white/80 truncate text-[12.5px]" title="{{ $log->message }}">
                                            {{ Str::limit($log->message, 65) }}
                                        </div>
                                        @if ($log->error_message)
                                            <div class="text-[#FF3B30] dark:text-[#FF453A] text-[11px] mt-0.5 truncate font-medium flex items-center gap-1">
                                                <i data-lucide="alert-circle" class="w-3 h-3 shrink-0"></i>
                                                <span class="truncate">{{ $log->error_message }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        @if ($log->status === 'sent')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                                <i data-lucide="check" class="w-3 h-3"></i>
                                                <span>{{ __('whatsapp.status_sent') }}</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                                <i data-lucide="x" class="w-3 h-3"></i>
                                                <span>{{ __('whatsapp.status_failed') }}</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-black/50 dark:text-white/50 tabular-nums text-[12px] whitespace-nowrap">
                                        {{ $log->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        <button type="button" @click.stop="inspectLog(@json($logJson))"
                                            class="min-h-[44px] px-3.5 rounded-[12px] text-[12.5px] font-bold text-[#007AFF] hover:bg-[#007AFF]/10 transition-colors inline-flex items-center gap-1.5">
                                            <span>{{ __('whatsapp.view_detail_log') }}</span>
                                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Logs List --}}
                <div class="sm:hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @foreach ($logs as $log)
                        @php
                            $typeBadge = match ($log->type) {
                                'receipt' => 'bg-[#30B0C7]/12 text-[#227D8E] dark:text-[#40C8E0]',
                                'broadcast' => 'bg-[#AF52DE]/12 text-[#7C3AA6] dark:text-[#BF5AF2]',
                                'test' => 'bg-[#007AFF]/12 text-[#0062CC] dark:text-[#0A84FF]',
                                default => 'bg-black/6 dark:bg-white/8 text-black/60 dark:text-white/60',
                            };
                            $typeLabel = match ($log->type) {
                                'receipt' => __('whatsapp.type_receipt'),
                                'broadcast' => __('whatsapp.type_broadcast'),
                                'test' => __('whatsapp.type_test'),
                                default => ucfirst($log->type),
                            };
                            $displayPhoneMobile = $formatPhone($log->recipient_phone);
                            $logJsonMobile = [
                                'id' => $log->id,
                                'recipient_name' => $log->recipient_name,
                                'recipient_phone' => $displayPhoneMobile,
                                'raw_phone' => $isOwner ? $log->recipient_phone : null,
                                'type' => $log->type,
                                'message' => $log->message,
                                'status' => $log->status,
                                'error_message' => $log->error_message,
                                'sent_at' => $log->created_at->format('d F Y, H:i:s') . ' WIB',
                            ];
                        @endphp
                        <div x-show="filterType === 'all' || filterType === '{{ $log->type }}'"
                            @click="inspectLog(@json($logJsonMobile))"
                            class="p-4 space-y-2.5 active:bg-black/[0.02] dark:active:bg-white/[0.03] transition-colors cursor-pointer">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="font-bold text-[14.5px] text-black dark:text-white">{{ $log->recipient_name }}</div>
                                    <div class="text-black/50 dark:text-white/50 font-mono tabular-nums text-[11.5px] mt-0.5">{{ $displayPhoneMobile }}</div>
                                </div>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-semibold {{ $typeBadge }} shrink-0">
                                    {{ $typeLabel }}
                                </span>
                            </div>

                            <div class="text-black/75 dark:text-white/75 text-[12.5px] bg-black/[0.02] dark:bg-white/[0.02] p-3 rounded-[10px] leading-relaxed">
                                {{ $log->message }}
                                @if ($log->error_message)
                                    <div class="text-[#FF3B30] dark:text-[#FF453A] text-[11px] mt-1 font-medium flex items-center gap-1">
                                        <i data-lucide="alert-circle" class="w-3 h-3 shrink-0"></i>
                                        <span class="truncate">{{ $log->error_message }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center justify-between text-[12px] pt-1">
                                <div>
                                    @if ($log->status === 'sent')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                            <i data-lucide="check" class="w-3 h-3"></i>
                                            <span>{{ __('whatsapp.status_sent') }}</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                            <i data-lucide="x" class="w-3 h-3"></i>
                                            <span>{{ __('whatsapp.status_failed') }}</span>
                                        </span>
                                    @endif
                                </div>
                                <span class="text-black/45 dark:text-white/45 text-[11.5px] tabular-nums">
                                    {{ $log->created_at->format('d/m/Y H:i') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($logs->hasPages())
                    <div class="p-4 border-t border-black/5 dark:border-white/10">
                        {{ $logs->links() }}
                    </div>
                @endif
            @endif
        </div>

        <!-- =================================================================== -->
        <!-- 3. LOG INSPECTOR MODAL SHEET (Apple HIG Detail Inspector Sheet)     -->
        <!-- =================================================================== -->
        <template x-teleport="body">
            <div x-show="inspectorOpen" x-cloak
                class="fixed inset-0 z-[200] flex items-center justify-center p-0 sm:p-4 lg:p-6 overflow-hidden"
                @keydown.escape.window="closeInspector()">

            <!-- Backdrop -->
            <div x-show="inspectorOpen" x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" @click="closeInspector()"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <!-- Inspector Dialog Canvas -->
            <div x-show="inspectorOpen" x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4 sm:translate-y-0"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4 sm:translate-y-0"
                role="dialog" aria-modal="true" aria-labelledby="inspectorModalTitle"
                class="relative w-full max-w-[95vw] md:max-w-2xl bg-white dark:bg-[#1C1C1E] sm:rounded-[24px] rounded-t-[28px] max-h-[95vh] sm:max-h-[90vh] flex flex-col overflow-hidden shadow-2xl border border-black/10 dark:border-white/10 z-10">

                <!-- Mobile Grab Bar -->
                <div class="sm:hidden w-10 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto my-2.5 shrink-0"></div>

                <!-- Header -->
                <header class="px-5 sm:px-6 py-4 bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-md border-b border-black/5 dark:border-white/10 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                            <i data-lucide="file-text" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 id="inspectorModalTitle" class="text-[16px] font-bold text-black dark:text-white">{{ __('whatsapp.log_detail_modal_title') }}</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50" x-text="activeLog?.sent_at"></p>
                        </div>
                    </div>
                    <button type="button" @click="closeInspector()"
                        aria-label="{{ __('whatsapp.close_btn') }}"
                        class="min-w-[44px] min-h-[44px] w-10 h-10 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.1] text-black/60 dark:text-white/60 flex items-center justify-center transition">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </header>

                <!-- Body -->
                <div class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-5">
                    <!-- Recipient & Meta Grid -->
                    <div class="grid grid-cols-2 gap-3 p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 text-[12.5px]">
                        <div>
                            <span class="text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase">{{ __('whatsapp.inspector_recipient') }}</span>
                            <p class="font-bold text-black dark:text-white text-[13.5px] mt-0.5" x-text="activeLog?.recipient_name || '-'"></p>
                        </div>
                        <div>
                            <span class="text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase">{{ __('whatsapp.inspector_phone') }}</span>
                            <p class="font-mono font-bold text-[#007AFF] text-[13.5px] mt-0.5 tabular-nums" x-text="activeLog?.recipient_phone || '-'"></p>
                        </div>
                        <div>
                            <span class="text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase">{{ __('whatsapp.inspector_type') }}</span>
                            <p class="font-bold text-black dark:text-white mt-0.5 capitalize" x-text="activeLog?.type || '-'"></p>
                        </div>
                        <div>
                            <span class="text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase">{{ __('whatsapp.inspector_status') }}</span>
                            <p class="font-bold mt-0.5" :class="activeLog?.status === 'sent' ? 'text-[#34C759]' : 'text-[#FF3B30]'" x-text="activeLog?.status === 'sent' ? '{{ __('whatsapp.status_sent_success') }}' : '{{ __('whatsapp.status_failed_sent') }}'"></p>
                        </div>
                    </div>

                    <!-- Full Message Bubble -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-[12px] font-bold text-black/70 dark:text-white/70">{{ __('whatsapp.inspector_full_message') }}</label>
                            <button type="button" @click="copyMessage()"
                                class="min-h-[36px] px-2 text-[11.5px] text-[#007AFF] hover:underline font-semibold flex items-center gap-1">
                                <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                <span x-text="copied ? '{{ __('whatsapp.copied_toast') }}' : '{{ __('whatsapp.copy_message') }}'"></span>
                            </button>
                        </div>
                        <div class="p-4 rounded-[14px] bg-[#ECE5DD]/40 dark:bg-[#0b141a]/60 border border-black/5 dark:border-white/10 font-mono text-[12.5px] text-black/90 dark:text-white/90 whitespace-pre-wrap leading-relaxed max-h-60 overflow-y-auto"
                            x-text="activeLog?.message"></div>
                    </div>

                    <!-- Error Callout if Failed -->
                    <template x-if="activeLog?.error_message">
                        <div class="p-3.5 rounded-[12px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[12px] text-[#C41E17] dark:text-[#FF453A] space-y-1">
                            <span class="font-bold block">{{ __('whatsapp.inspector_system_error') }}</span>
                            <p class="font-mono" x-text="activeLog?.error_message"></p>
                        </div>
                    </template>
                </div>

                <!-- Footer Action -->
                <footer class="px-5 sm:px-6 py-4 bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-md border-t border-black/5 dark:border-white/10 flex items-center justify-between gap-3 shrink-0">
                    <template x-if="activeLog?.raw_phone">
                        <a :href="'https://wa.me/' + activeLog.raw_phone.replace(/[^0-9]/g, '') + '?text=' + encodeURIComponent(activeLog?.message || '')"
                            target="_blank" rel="noopener noreferrer"
                            class="min-h-[44px] px-4 rounded-[12px] bg-[#34C759] hover:bg-[#2FB350] text-white font-bold text-[13px] shadow-sm flex items-center gap-2 transition active:scale-[0.98]">
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                            <span>{{ __('whatsapp.wa_web_fallback_btn') }}</span>
                        </a>
                    </template>
                    <template x-if="!activeLog?.raw_phone">
                        <div class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] text-black/50 dark:text-white/50 text-[12px] font-medium">
                            <i data-lucide="shield" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                            <span>{{ __('whatsapp.pii_masked_notice') }}</span>
                        </div>
                    </template>

                    <button type="button" @click="closeInspector()"
                        class="min-h-[44px] px-4 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] transition active:scale-[0.98]">
                        {{ __('whatsapp.close_btn') }}
                    </button>
                </footer>

            </div>
        </div>
        </template>

        <!-- ===================================================== -->
        <!-- 4. STORAGE PRUNING PREVIEW MODAL                      -->
        <!-- ===================================================== -->
        <template x-teleport="body">
            <div x-show="openPruneModal" x-transition.opacity.duration.200ms
                class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                @click.self="openPruneModal = false" style="display: none;">
            <div role="dialog" aria-modal="true" aria-labelledby="pruneModalTitle"
                class="w-full max-w-[500px] rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-6 sm:p-7 space-y-5 animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[12px] bg-[#FF9500]/12 text-[#FF9500] flex items-center justify-center">
                            <i data-lucide="database-zap" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 id="pruneModalTitle" class="text-[16px] font-bold text-black dark:text-white">{{ __('whatsapp.prune_modal_title') }}</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('whatsapp.prune_older_than', ['days' => 90]) }}</p>
                        </div>
                    </div>
                    <button type="button" @click="openPruneModal = false"
                        aria-label="{{ __('whatsapp.close_btn') }}"
                        class="min-w-[44px] min-h-[44px] w-10 h-10 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:bg-black/10 transition">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-3 text-center">
                    <div class="p-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5">
                        <span class="text-[11px] text-black/50 dark:text-white/50 uppercase font-semibold block">{{ __('whatsapp.prune_estimated_records') }}</span>
                        <strong class="text-[20px] font-bold text-[#FF9500] mt-1 block tabular-nums">0 {{ __('whatsapp.rows_unit') }}</strong>
                    </div>
                    <div class="p-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5">
                        <span class="text-[11px] text-black/50 dark:text-white/50 uppercase font-semibold block">{{ __('whatsapp.prune_estimated_storage') }}</span>
                        <strong class="text-[20px] font-bold text-[#34C759] mt-1 block tabular-nums">0.0 MB</strong>
                    </div>
                </div>

                <div class="p-3.5 rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/20 text-[12px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-2.5">
                    <i data-lucide="shield-check" class="w-4 h-4 shrink-0 text-[#34C759]"></i>
                    <span>{{ __('whatsapp.prune_safe_notice') }}</span>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="openPruneModal = false" class="min-h-[44px] px-5 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] transition">
                        {{ __('whatsapp.cancel_btn') }}
                    </button>
                    <button type="button" @click="handlePruneConfirm()"
                        class="min-h-[44px] px-5 rounded-[12px] bg-[#FF9500] hover:bg-[#E08500] text-white font-bold text-[13px] shadow-sm transition active:scale-[0.98]">
                        {{ __('whatsapp.prune_btn') }}
                    </button>
                </div>
            </div>
        </div>
        </template>

    </div>
@endsection

@push('scripts')
    <script>
        function whatsappLogs() {
            return {
                filterType: 'all',
                inspectorOpen: false,
                openPruneModal: false,
                activeLog: null,
                copied: false,

                init() {
                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });
                },

                inspectLog(log) {
                    this.activeLog = log;
                    this.inspectorOpen = true;
                    this.copied = false;
                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });
                },

                closeInspector() {
                    this.inspectorOpen = false;
                    this.activeLog = null;
                },

                async copyMessage() {
                    if (!this.activeLog?.message) return;
                    try {
                        await navigator.clipboard.writeText(this.activeLog.message);
                        this.copied = true;
                        setTimeout(() => this.copied = false, 2000);
                    } catch (e) {}
                },

                handlePruneConfirm() {
                    this.openPruneModal = false;
                    const msg = {{ Js::from(__('whatsapp.prune_safe_alert')) }};
                    if (window.AppAlert) {
                        AppAlert.info(msg);
                    } else {
                        alert(msg);
                    }
                }
            };
        }
    </script>
@endpush
