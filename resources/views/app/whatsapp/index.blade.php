@extends('layouts.app', [
    'title' => __('whatsapp.title') . ' - ' . $business->name,
    'headerTitle' => __('whatsapp.header_title'),
    'headerSubtitle' => __('whatsapp.header_subtitle'),
])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="waGateway()" x-init="init()">

        {{-- MODULE HEADER & PERSISTENT COMMUNICATION TABS --}}
        <x-module-header
            module="communication"
            :title="__('whatsapp.title')"
            :subtitle="__('whatsapp.subtitle', ['business' => $business->name])">
            <x-slot:actions>
                <a href="{{ route('whatsapp.logs.index') }}"
                    class="min-h-[44px] px-4 rounded-[12px] text-[13px] font-semibold text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all flex items-center justify-center gap-2 w-full sm:w-auto">
                    <i data-lucide="history" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                    <span>{{ __('whatsapp.tab_logs') }}</span>
                </a>
                @if (\App\Support\Context::hasPermission('pos.terminal'))
                    <a href="{{ route('pos.terminal') }}"
                        class="min-h-[44px] px-4 rounded-[12px] text-[13px] font-semibold text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all flex items-center justify-center gap-2 w-full sm:w-auto">
                        <i data-lucide="calculator" class="w-4 h-4 text-[#007AFF]"></i>
                        <span>{{ __('whatsapp.open_pos') }}</span>
                    </a>
                @endif
            </x-slot:actions>
        </x-module-header>

        <x-module-tabs module="communication" />

        <!-- ===================================================== -->
        <!-- 3. MAIN GATEWAY COCKPIT GRID (2 COLUMNS)              -->
        <!-- ===================================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- ========================================== -->
            <!-- LEFT COLUMN: META WHATSAPP CLOUD API INTEGRATION -->
            <!-- ========================================== -->
            <div class="lg:col-span-7 space-y-6">

                <!-- 1. META EMBEDDED SIGNUP & CLOUD API CARD -->
                <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 sm:p-7 space-y-5 transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-[16px] bg-[#007AFF]/12 text-[#007AFF] border border-[#007AFF]/20 flex items-center justify-center shrink-0">
                                <i data-lucide="shield-check" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h2 class="text-[17px] font-bold text-black dark:text-white tracking-tight">{{ __('whatsapp.meta_official_title') }}</h2>
                                <p class="text-[12.5px] text-black/55 dark:text-white/55 mt-0.5">{{ __('whatsapp.meta_official_desc') }}</p>
                            </div>
                        </div>

                        <div>
                            <span class="px-3 py-1.5 rounded-full text-[11.5px] font-bold inline-flex items-center gap-1.5"
                                :class="metaAccount?.is_active ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25' : 'bg-black/[0.05] dark:bg-white/[0.08] text-black/55 dark:text-white/55 border border-black/10 dark:border-white/10'">
                                <span class="w-2 h-2 rounded-full" :class="metaAccount?.is_active ? 'bg-[#34C759]' : 'bg-[#FF9500]'"></span>
                                <span x-text="metaAccount?.is_active ? '{{ __('whatsapp.connected_active') }}' : '{{ __('whatsapp.disconnected') }}'"></span>
                            </span>
                        </div>
                    </div>

                    <!-- Meta Free Tier Info Callout -->
                    <div class="p-4 rounded-[16px] bg-[#007AFF]/8 border border-[#007AFF]/20 text-[12.5px] text-black/75 dark:text-white/75 space-y-1.5">
                        <div class="flex items-center justify-between font-bold text-[#007AFF]">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="sparkles" class="w-4 h-4 shrink-0"></i>
                                <span>{{ __('whatsapp.free_tier_banner_title') }}</span>
                            </span>
                        </div>
                        <p class="leading-relaxed text-black/65 dark:text-white/65 text-[12px]">
                            {{ __('whatsapp.free_tier_banner_desc') }}
                        </p>
                    </div>

                    <!-- EMBEDDED SIGNUP FLOW (1-CLICK ONBOARDING) -->
                    <div class="p-5 sm:p-6 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="space-y-0.5">
                                <h3 class="text-[15px] font-bold text-black dark:text-white tracking-tight">{{ __('whatsapp.embedded_signup_title') }}</h3>
                                <p class="text-[12px] text-black/55 dark:text-white/55">{{ __('whatsapp.embedded_signup_desc') }}</p>
                            </div>
                        </div>

                        <!-- Active Connected Account Details -->
                        <template x-if="metaAccount?.is_active">
                            <div class="space-y-4 pt-1">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 rounded-[16px] bg-white/80 dark:bg-black/25 border border-black/5 dark:border-white/10 text-[12.5px]">
                                    <div>
                                        <span class="text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase">{{ __('whatsapp.verified_name_label') }}</span>
                                        <p class="font-bold text-black dark:text-white text-[13.5px] mt-0.5" x-text="metaAccount.verified_name || '-'"></p>
                                    </div>
                                    <div>
                                        <span class="text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase">{{ __('whatsapp.phone_number_label') }}</span>
                                        <p class="font-mono font-bold text-[#007AFF] text-[13.5px] mt-0.5 tabular-nums" x-text="metaAccount.display_phone_number || '-'"></p>
                                    </div>
                                    <div>
                                        <span class="text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase">{{ __('whatsapp.quality_rating_label') }}</span>
                                        <p class="font-bold text-[#34C759] mt-0.5 flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                                            <span x-text="metaAccount.quality_rating || 'GREEN'"></span>
                                        </p>
                                    </div>
                                    <div>
                                        <span class="text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase">{{ __('whatsapp.messaging_limit_label') }}</span>
                                        <p class="font-bold text-black dark:text-white mt-0.5" x-text="metaAccount.messaging_limit_tier || 'TIER_50'"></p>
                                    </div>
                                </div>

                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-1">
                                    <span class="text-[11.5px] text-black/50 dark:text-white/50 font-mono truncate max-w-[260px]">
                                        {{ __('whatsapp.waba_id_label') }}: <span x-text="metaAccount.waba_id"></span>
                                    </span>
                                    <button type="button" @click="disconnectMeta()"
                                        class="min-h-[44px] px-4 rounded-[12px] text-[12.5px] font-bold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition-all flex items-center justify-center">
                                        {{ __('whatsapp.disconnect_btn') }}
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- Connect Action (When not connected - 1-Click Method Only) -->
                        <template x-if="!metaAccount?.is_active">
                            <div class="space-y-4">
                                <p class="text-[12.5px] text-black/70 dark:text-white/70 leading-relaxed">
                                    {{ __('whatsapp.embedded_signup_instruction') }}
                                </p>

                                @if (\App\Support\Context::hasPermission('whatsapp.manage'))
                                    <!-- Action Row: Button CTA directly follows instruction, with proportional width & centered icon -->
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 pt-0.5">
                                        <button type="button" @click="launchEmbeddedSignup()" :disabled="embeddedLoading"
                                            class="min-h-[46px] px-6 rounded-[14px] bg-[#1877F2] hover:bg-[#166FE5] text-white font-semibold text-[13.5px] inline-flex items-center justify-center gap-2.5 shadow-sm active:scale-[0.98] transition-all disabled:opacity-50 w-full sm:w-auto">
                                            <i data-lucide="loader-2" x-show="embeddedLoading" class="w-4 h-4 animate-spin shrink-0"></i>
                                            <i data-lucide="shield-check" x-show="!embeddedLoading" class="w-4 h-4 shrink-0"></i>
                                            <span class="whitespace-nowrap" x-text="embeddedLoading ? '{{ __('whatsapp.connecting_meta') }}' : '{{ __('whatsapp.connect_meta_btn') }}'"></span>
                                        </button>
                                    </div>
                                @endif

                                <!-- Quiet Informational Stepper (Apple HIG - NOT Button-like Cards) -->
                                <div class="pt-3 border-t border-black/5 dark:border-white/10">
                                    <div class="flex overflow-x-auto sm:grid sm:grid-cols-3 gap-3 no-scrollbar pb-1">
                                        <div class="flex items-start gap-2.5 shrink-0 w-[190px] sm:w-auto">
                                            <span class="w-5 h-5 rounded-full bg-[#1877F2]/10 text-[#1877F2] text-[11px] font-bold flex items-center justify-center shrink-0 mt-0.5">1</span>
                                            <div class="space-y-0.5 min-w-0">
                                                <p class="font-semibold text-black dark:text-white text-[12px] leading-tight">{{ __('whatsapp.step_1_title') }}</p>
                                                <p class="text-[11px] text-black/55 dark:text-white/55 leading-normal">{{ __('whatsapp.step_1_desc') }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-start gap-2.5 shrink-0 w-[190px] sm:w-auto">
                                            <span class="w-5 h-5 rounded-full bg-[#1877F2]/10 text-[#1877F2] text-[11px] font-bold flex items-center justify-center shrink-0 mt-0.5">2</span>
                                            <div class="space-y-0.5 min-w-0">
                                                <p class="font-semibold text-black dark:text-white text-[12px] leading-tight">{{ __('whatsapp.step_2_title') }}</p>
                                                <p class="text-[11px] text-black/55 dark:text-white/55 leading-normal">{{ __('whatsapp.step_2_desc') }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-start gap-2.5 shrink-0 w-[190px] sm:w-auto">
                                            <span class="w-5 h-5 rounded-full bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] text-[11px] font-bold flex items-center justify-center shrink-0 mt-0.5">3</span>
                                            <div class="space-y-0.5 min-w-0">
                                                <p class="font-semibold text-[#248A3D] dark:text-[#30D158] text-[12px] leading-tight">{{ __('whatsapp.step_3_title') }}</p>
                                                <p class="text-[11px] text-black/55 dark:text-white/55 leading-normal">{{ __('whatsapp.step_3_desc') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Feedback Error -->
                                <div x-show="embeddedError" x-transition class="p-3.5 rounded-[12px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[12px] text-[#C41E17] dark:text-[#FF453A] flex items-center gap-2">
                                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                                    <span x-text="embeddedError"></span>
                                </div>

                                <!-- Feedback Success -->
                                <div x-show="embeddedSuccess" x-transition class="p-3.5 rounded-[12px] bg-[#34C759]/12 border border-[#34C759]/30 text-[12px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                                    <span x-text="embeddedSuccess"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Status Layanan WhatsApp Toko -->
                    <div class="pt-1">
                        <div class="flex items-center justify-between p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                            <div class="pr-2">
                                <p class="text-[13.5px] font-bold text-black dark:text-white">{{ __('whatsapp.service_status_title') }}</p>
                                <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">{{ __('whatsapp.service_status_desc') }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" name="is_active" value="1" @change="toggleServiceStatus($event)" class="sr-only peer"
                                    {{ ($waSession?->is_active ?? true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-black/[0.12] dark:bg-white/[0.15] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]"></div>
                            </label>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ========================================== -->
            <!-- RIGHT COLUMN: SETTINGS & TEST SENDER       -->
            <!-- ========================================== -->
            <div class="lg:col-span-5 space-y-6">

                <!-- 1. AUTO RECEIPT SETTINGS CARD -->
                <div
                    class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 sm:p-6 space-y-4 transition-colors">
                    <div class="flex items-center gap-3 pb-3 border-b border-black/5 dark:border-white/10">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-[#30B0C7]/12 text-[#30B0C7] dark:text-[#40C8E0] flex items-center justify-center">
                            <i data-lucide="receipt" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h2 class="text-[15px] font-bold text-black dark:text-white">{{ __('whatsapp.receipt_settings_title') }}</h2>
                            <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('whatsapp.receipt_settings_desc') }}</p>
                        </div>
                    </div>

                    <form action="{{ route('whatsapp.settings') }}" method="POST" class="space-y-4">
                        @csrf

                        <!-- Toggle Switch Row (Grouped Inset Style) -->
                        <div
                            class="flex items-center justify-between p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                            <div class="pr-2">
                                <p class="text-[13px] font-semibold text-black dark:text-white">{{ __('whatsapp.auto_send_receipt_label') }}</p>
                                <p class="text-[11.5px] text-black/50 dark:text-white/50 mt-0.5">{{ __('whatsapp.auto_send_receipt_desc') }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" name="auto_send_receipt" value="1" class="sr-only peer"
                                    {{ $waSession && $waSession->auto_send_receipt ? 'checked' : '' }}>
                                <div
                                    class="w-11 h-6 bg-black/[0.12] dark:bg-white/[0.15] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]">
                                </div>
                            </label>
                        </div>

                        <!-- Receipt Footer Note -->
                        <div class="space-y-1.5">
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">
                                {{ __('whatsapp.receipt_footer_label') }}
                            </label>
                            <textarea name="receipt_template" rows="2"
                                placeholder="{{ __('whatsapp.receipt_footer_placeholder') }}"
                                class="w-full bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] px-3.5 py-2.5 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 resize-none placeholder:text-black/35 dark:placeholder:text-white/35 transition-colors">{{ $waSession?->receipt_template ?? '' }}</textarea>
                        </div>

                        @if (\App\Support\Context::hasPermission('whatsapp.manage'))
                            <button type="submit"
                                class="w-full min-h-[44px] px-4 rounded-[12px] text-[13px] font-semibold text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all flex items-center justify-center">
                                {{ __('whatsapp.save_receipt_settings_btn') }}
                            </button>
                        @endif
                    </form>
                </div>

                <!-- 2. TEST SEND CONSOLE -->
                <div
                    class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 sm:p-6 space-y-4 transition-colors">
                    <div class="flex items-center gap-3 pb-3 border-b border-black/5 dark:border-white/10">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center">
                            <i data-lucide="send" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h2 class="text-[15px] font-bold text-black dark:text-white">{{ __('whatsapp.test_console_title') }}</h2>
                            <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('whatsapp.test_console_desc') }}</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="space-y-1.5">
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('whatsapp.test_phone_label') }}</label>
                            <input x-model="testPhone" type="tel" placeholder="{{ __('whatsapp.test_phone_placeholder') }}"
                                class="w-full min-h-[44px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[13.5px] font-semibold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 placeholder:text-black/35 dark:placeholder:text-white/35 transition-colors">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('whatsapp.test_message_label') }}</label>
                            <input x-model="testMessage" type="text" placeholder="{{ __('whatsapp.test_message_placeholder') }}"
                                class="w-full min-h-[44px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 placeholder:text-black/35 dark:placeholder:text-white/35 transition-colors">
                        </div>

                        @if (\App\Support\Context::hasPermission('whatsapp.manage'))
                            <button type="button" @click="sendTest()"
                                class="w-full min-h-[44px] px-4 rounded-[12px] text-[13.5px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] shadow-md shadow-[#007AFF]/25 transition-all flex items-center justify-center gap-2"
                                :disabled="testLoading">
                                <i data-lucide="loader-2" x-show="testLoading" class="w-4 h-4 animate-spin"></i>
                                <i data-lucide="send" x-show="!testLoading" class="w-4 h-4"></i>
                                <span x-text="testLoading ? '{{ __('whatsapp.test_sending') }}' : '{{ __('whatsapp.test_send_btn') }}'"></span>
                            </button>
                        @endif

                        <div x-show="testResult" x-text="testResult"
                            class="p-3 rounded-[12px] text-[12.5px] text-center font-semibold transition-all"
                            :class="testOk ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' :
                                'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]'">
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- ===================================================== -->
        <!-- 4. OFFICIAL META MESSAGE TEMPLATES CATALOG (ANTI-BLOKIR) -->
        <!-- ===================================================== -->
        <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 sm:p-7 space-y-6 transition-colors">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-black/5 dark:border-white/10">
                <div class="flex items-start gap-3.5">
                    <div class="w-12 h-12 rounded-[16px] bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center shrink-0 shadow-sm">
                        <i data-lucide="shield-check" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-[17px] font-bold text-black dark:text-white tracking-tight">
                                {{ __('whatsapp.meta_catalog_title') }}
                            </h2>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25">
                                {{ __('whatsapp.meta_compliant_badge') }}
                            </span>
                        </div>
                        <p class="text-[12.5px] text-black/55 dark:text-white/55 mt-1 leading-relaxed">
                            {{ __('whatsapp.meta_catalog_subtitle') }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5 shrink-0">
                    <a href="{{ route('whatsapp.broadcast.index', ['open_composer' => 1]) }}"
                        class="min-h-[44px] px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-bold inline-flex items-center gap-2 shadow-sm transition active:scale-[0.98]">
                        <i data-lucide="megaphone" class="w-4 h-4"></i>
                        <span>{{ __('whatsapp.send_blast_with_template') }}</span>
                    </a>
                </div>
            </div>

            <!-- Bento Grid: 10 Operational System Templates for Store -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @php
                    $storeTemplates = [
                        [
                            'name' => 'cooca_pos_receipt',
                            'title' => __('whatsapp.tpl_pos_receipt_title'),
                            'category' => 'UTILITY',
                            'badge' => __('whatsapp.tpl_pos_receipt_badge'),
                            'desc' => __('whatsapp.tpl_pos_receipt_desc'),
                            'icon' => 'receipt',
                        ],
                        [
                            'name' => 'cooca_reservation_reminder',
                            'title' => __('whatsapp.tpl_reservation_reminder_title'),
                            'category' => 'UTILITY',
                            'badge' => __('whatsapp.tpl_reservation_reminder_badge'),
                            'desc' => __('whatsapp.tpl_reservation_reminder_desc'),
                            'icon' => 'calendar-clock',
                        ],
                        [
                            'name' => 'cooca_marketplace_receipt',
                            'title' => __('whatsapp.tpl_marketplace_receipt_title'),
                            'category' => 'UTILITY',
                            'badge' => __('whatsapp.tpl_marketplace_receipt_badge'),
                            'desc' => __('whatsapp.tpl_marketplace_receipt_desc'),
                            'icon' => 'shopping-bag',
                        ],
                        [
                            'name' => 'cooca_shipping_tracking',
                            'title' => __('whatsapp.tpl_shipping_tracking_title'),
                            'category' => 'UTILITY',
                            'badge' => __('whatsapp.tpl_shipping_tracking_badge'),
                            'desc' => __('whatsapp.tpl_shipping_tracking_desc'),
                            'icon' => 'truck',
                        ],
                        [
                            'name' => 'cooca_cart_reminder',
                            'title' => __('whatsapp.tpl_cart_reminder_title'),
                            'category' => 'MARKETING',
                            'badge' => __('whatsapp.tpl_cart_reminder_badge'),
                            'desc' => __('whatsapp.tpl_cart_reminder_desc'),
                            'icon' => 'shopping-cart',
                        ],
                        [
                            'name' => 'cooca_promo_broadcast',
                            'title' => __('whatsapp.tpl_promo_broadcast_title'),
                            'category' => 'MARKETING',
                            'badge' => __('whatsapp.tpl_promo_broadcast_badge'),
                            'desc' => __('whatsapp.tpl_promo_broadcast_desc'),
                            'icon' => 'megaphone',
                        ],
                        [
                            'name' => 'cooca_order_status_update',
                            'title' => __('whatsapp.tpl_order_status_update_title'),
                            'category' => 'UTILITY',
                            'badge' => __('whatsapp.tpl_order_status_update_badge'),
                            'desc' => __('whatsapp.tpl_order_status_update_desc'),
                            'icon' => 'package-check',
                        ],
                        [
                            'name' => 'cooca_sales_invoice',
                            'title' => __('whatsapp.tpl_sales_invoice_title'),
                            'category' => 'UTILITY',
                            'badge' => __('whatsapp.tpl_sales_invoice_badge'),
                            'desc' => __('whatsapp.tpl_sales_invoice_desc'),
                            'icon' => 'file-text',
                        ],
                        [
                            'name' => 'cooca_customer_welcome',
                            'title' => __('whatsapp.tpl_customer_welcome_title'),
                            'category' => 'MARKETING',
                            'badge' => __('whatsapp.tpl_customer_welcome_badge'),
                            'desc' => __('whatsapp.tpl_customer_welcome_desc'),
                            'icon' => 'sparkles',
                        ],
                        [
                            'name' => 'cooca_payment_reminder',
                            'title' => __('whatsapp.tpl_payment_reminder_title'),
                            'category' => 'UTILITY',
                            'badge' => __('whatsapp.tpl_payment_reminder_badge'),
                            'desc' => __('whatsapp.tpl_payment_reminder_desc'),
                            'icon' => 'bell',
                        ],
                    ];
                @endphp

                @foreach ($storeTemplates as $tpl)
                    <div class="rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 p-4 sm:p-5 flex flex-col justify-between space-y-3.5 hover:border-[#34C759]/30 transition-all">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between gap-2">
                                <span class="px-2.5 py-0.5 rounded-[6px] text-[10.5px] font-bold uppercase tracking-wider
                                    {{ $tpl['category'] === 'UTILITY' ? 'bg-[#007AFF]/15 text-[#007AFF]' : 'bg-[#34C759]/15 text-[#34C759]' }}">
                                    {{ $tpl['category'] }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-[#34C759]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                    <span>{{ __('whatsapp.approved_meta_badge') }}</span>
                                </span>
                            </div>

                            <div class="pt-1">
                                <h3 class="text-[14.5px] font-bold text-black dark:text-white">{{ $tpl['title'] }}</h3>
                                <code class="text-[11.5px] font-mono text-[#007AFF] block mt-0.5">{{ $tpl['name'] }}</code>
                                <p class="text-[12px] text-black/60 dark:text-white/60 mt-1 leading-relaxed">
                                    {{ $tpl['desc'] }}
                                </p>
                            </div>
                        </div>

                        <div class="pt-2.5 border-t border-black/5 dark:border-white/10 flex items-center justify-between text-[11px]">
                            <span class="text-black/45 dark:text-white/45 font-medium">{{ $tpl['badge'] }}</span>
                            <span class="text-[#34C759] font-bold inline-flex items-center gap-1">
                                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                <span>{{ __('whatsapp.safe_24h_badge') }}</span>
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Teleported Supervisor PIN Disconnect Modal -->
        <template x-teleport="body">
            <div x-show="showDisconnectModal" x-cloak
                role="dialog" aria-modal="true" aria-labelledby="disconnect-modal-title"
                @keydown.escape.window="showDisconnectModal = false"
                class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0">
                <div @click.away="showDisconnectModal = false"
                    class="w-full max-w-md rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-6 space-y-4"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95">
                    
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-[#FF3B30]/12 text-[#FF3B30] flex items-center justify-center shrink-0">
                            <i data-lucide="lock" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 id="disconnect-modal-title" class="text-[16px] font-bold text-black dark:text-white">{{ __('whatsapp.disconnect_supervisor_title') }}</h3>
                            <p class="text-[12px] text-black/55 dark:text-white/55">{{ __('whatsapp.disconnect_supervisor_modal_sub') }}</p>
                        </div>
                    </div>

                    <p class="text-[12.5px] text-black/70 dark:text-white/70 leading-relaxed">
                        {{ __('whatsapp.disconnect_supervisor_desc') }}
                    </p>

                    <div class="space-y-1.5">
                        <label class="block text-[11.5px] font-bold text-black/60 dark:text-white/60 uppercase">{{ __('whatsapp.supervisor_pin_label') }}</label>
                        <input type="password" maxlength="6" inputmode="numeric" x-model="disconnectPin"
                            @keydown.enter.prevent="submitDisconnectWithPin()"
                            placeholder="••••••"
                            class="w-full h-12 text-center tracking-[0.5em] text-xl font-mono rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/15 focus:outline-none focus:ring-2 focus:ring-[#FF3B30] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30">
                    </div>

                    <template x-if="disconnectError">
                        <div class="p-3 rounded-[10px] bg-[#FF3B30]/10 text-[#C41E17] dark:text-[#FF453A] text-xs font-semibold flex items-center gap-2">
                            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                            <span x-text="disconnectError"></span>
                        </div>
                    </template>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="showDisconnectModal = false"
                            class="min-h-[44px] px-4 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition">
                            {{ __('whatsapp.cancel_btn') }}
                        </button>
                        <button type="button" @click="submitDisconnectWithPin()" :disabled="disconnectLoading || !disconnectPin.trim()"
                            class="min-h-[44px] px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.98] transition shadow-md shadow-[#FF3B30]/25 disabled:opacity-50 flex items-center gap-2">
                            <i data-lucide="unlink" class="w-4 h-4"></i>
                            <span x-text="disconnectLoading ? '{{ __('whatsapp.verifying') }}' : '{{ __('whatsapp.disconnect_action_btn') }}'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </template>

    </div>
@endsection

@push('scripts')
    <script>
        function waGateway() {
            return {
                provider: 'meta_cloud',
                isActive: {{ ($whatsAppAccount && $whatsAppAccount->isActive()) ? 'true' : 'false' }},
                isMetaConfigured: {{ $whatsAppAccount ? 'true' : 'false' }},
                status: '{{ $whatsAppAccount && $whatsAppAccount->isConnected() ? 'connected' : 'disconnected' }}',
                phone: {{ Js::from($whatsAppAccount?->display_phone_number ?? '') }},
                isLoading: false,
                testPhone: '',
                testMessage: {{ Js::from(__('whatsapp.default_test_message', ['business' => $business->name])) }},
                testLoading: false,
                testResult: '',
                testOk: false,
                embeddedLoading: false,
                embeddedError: null,
                embeddedSuccess: null,
                showDisconnectModal: false,
                disconnectPin: '',
                disconnectLoading: false,
                disconnectError: '',
                metaEmbeddedPhoneId: null,
                metaEmbeddedWabaId: null,
                @php
                    $metaAccountData = $whatsAppAccount ? [
                        'id'                   => $whatsAppAccount->id,
                        'verified_name'        => $whatsAppAccount->verified_name,
                        'display_phone_number' => $whatsAppAccount->display_phone_number,
                        'waba_id'              => $whatsAppAccount->waba_id,
                        'phone_number_id'      => $whatsAppAccount->phone_number_id,
                        'quality_rating'       => $whatsAppAccount->quality_rating,
                        'messaging_limit_tier' => $whatsAppAccount->messaging_limit_tier,
                        'is_active'            => $whatsAppAccount->isActive(),
                    ] : null;
                @endphp
                metaAccount: @json($metaAccountData),

                init() {
                    this.refreshMetaStatus();
                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });

                    // Dengarkan event sessionInfoListener dari Meta Embedded Signup pop-up
                    window.addEventListener('message', (event) => {
                        if (!event.origin.endsWith('facebook.com')) {
                            return;
                        }
                        try {
                            const data = typeof event.data === 'string' ? JSON.parse(event.data) : event.data;
                            if (data && data.type === 'WA_EMBEDDED_SIGNUP') {
                                if (data.event === 'FINISH' && data.data) {
                                    this.metaEmbeddedPhoneId = data.data.phone_number_id || null;
                                    this.metaEmbeddedWabaId = data.data.waba_id || null;
                                }
                            }
                        } catch (e) {}
                    });
                },

                async refreshMetaStatus() {
                    try {
                        const response = await fetch('{{ route('whatsapp.meta.status') }}', {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await response.json();
                        if (!response.ok || !data.success || !data.account) {
                            return;
                        }

                        const account = data.account;
                        this.metaAccount = {
                            ...this.metaAccount,
                            ...account,
                            is_active: Boolean(data.connected && account.is_connected !== false)
                        };
                        this.isActive = this.metaAccount.is_active;
                        this.isMetaConfigured = true;
                        this.status = this.metaAccount.is_active ? 'connected' : 'disconnected';
                        this.phone = account.display_phone_number || this.phone;
                    } catch (e) {}
                },

                async launchEmbeddedSignup() {
                    this.embeddedLoading = true;
                    this.embeddedError = null;
                    this.embeddedSuccess = null;

                    try {
                        // 1. Ambil konfigurasi App ID & Config ID dari server COOCA
                        const configRes = await fetch('{{ route('whatsapp.meta.config') }}', {
                            headers: { 'Accept': 'application/json' }
                        });
                        const configData = await configRes.json();
                        if (!configRes.ok || !configData.success) {
                            this.embeddedError = configData.error || {{ Js::from(__('whatsapp.error_meta_app_id_missing')) }};
                            this.embeddedLoading = false;
                            return;
                        }

                        if (!configData.config_id || String(configData.config_id).trim() === '') {
                            this.embeddedLoading = false;
                            this.embeddedError = {{ Js::from(__('whatsapp.error_meta_config_id_missing')) }};
                            return;
                        }

                        // 2. Pastikan Facebook JavaScript SDK ter-load
                        await this.ensureFbSdkLoaded(configData.app_id, configData.version || 'v26.0');

                        // 3. Launch FB.login dengan WhatsApp Embedded Signup sesuai dokumentasi resmi Meta
                        const loginOptions = {
                            config_id: String(configData.config_id).trim(),
                            response_type: 'code',
                            override_default_response_type: true,
                            extras: {
                                setup: {
                                    business: {
                                        name: {{ Js::from($business->name) }}
                                    }
                                }
                            }
                        };

                        FB.login((response) => {
                            if (response.authResponse && response.authResponse.code) {
                                this.exchangeEmbeddedCode(
                                    response.authResponse.code,
                                    this.metaEmbeddedWabaId,
                                    this.metaEmbeddedPhoneId
                                );
                            } else {
                                this.embeddedLoading = false;
                                this.embeddedError = {{ Js::from(__('whatsapp.error_meta_auth_cancelled')) }};
                            }
                        }, loginOptions);
                    } catch (err) {
                        this.embeddedLoading = false;
                        this.embeddedError = {{ Js::from(__('whatsapp.error_launch_signup_failed')) }} + ': ' + (err.message || err);
                    }
                },

                async exchangeEmbeddedCode(code, wabaId = null, phoneId = null) {
                    this.embeddedLoading = true;
                    try {
                        const res = await fetch('{{ route('whatsapp.meta.exchange-code') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                            },
                            body: JSON.stringify({
                                code: code,
                                waba_id: wabaId,
                                phone_number_id: phoneId
                            })
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.embeddedSuccess = {{ Js::from(__('whatsapp.meta_connected_success')) }};
                            this.metaAccount = data.account;
                            this.isActive = true;
                            this.status = 'connected';
                            this.isMetaConfigured = true;
                            if (window.CoocaBus) {
                                window.CoocaBus.emitDataMutated('whatsapp', data.account);
                            }
                        } else {
                            this.embeddedError = data.error || {{ Js::from(__('whatsapp.error_meta_exchange_failed')) }};
                        }
                    } catch (e) {
                        this.embeddedError = {{ Js::from(__('whatsapp.error_meta_credentials_save')) }};
                    } finally {
                        this.embeddedLoading = false;
                    }
                },

                async toggleServiceStatus(e) {
                    const isChecked = e.target.checked;
                    try {
                        const formData = new FormData();
                        formData.append('is_active', isChecked ? '1' : '0');
                        formData.append('provider', 'meta_cloud');
                        formData.append('_token', document.querySelector('meta[name=csrf-token]')?.content || '');
                        await fetch('{{ route('whatsapp.settings') }}', {
                            method: 'POST',
                            body: formData,
                            headers: { 'Accept': 'application/json' }
                        });
                        if (window.CoocaBus) {
                            window.CoocaBus.emitDataMutated('whatsapp', { is_active: isChecked });
                        }
                    } catch (err) {
                        console.error('Failed to toggle service status', err);
                    }
                },

                ensureFbSdkLoaded(appId, version) {
                    return new Promise((resolve) => {
                        if (window.FB) {
                            resolve();
                            return;
                        }
                        window.fbAsyncInit = function() {
                            FB.init({
                                appId: appId,
                                autoLogAppEvents: true,
                                xfbml: true,
                                version: version
                            });
                            resolve();
                        };
                        const js = document.createElement('script');
                        js.id = 'facebook-jssdk';
                        js.src = 'https://connect.facebook.net/en_US/sdk.js';
                        js.async = true;
                        js.defer = true;
                        document.body.appendChild(js);
                    });
                },

                disconnectMeta() {
                    const hasPin = {{ $business->hasSupervisorPin() ? 'true' : 'false' }};
                    if (hasPin) {
                        this.disconnectPin = '';
                        this.disconnectError = '';
                        this.showDisconnectModal = true;
                    } else {
                        this.confirmDisconnectDirect();
                    }
                },

                async confirmDisconnectDirect() {
                    let confirmed = false;
                    if (window.AppAlert) {
                        confirmed = await AppAlert.confirm({
                            title: {{ Js::from(__('whatsapp.disconnect_confirm_title')) }},
                            message: {{ Js::from(__('whatsapp.disconnect_confirm_desc')) }},
                            type: 'danger',
                            confirmText: {{ Js::from(__('whatsapp.disconnect_btn')) }},
                            cancelText: {{ Js::from(__('whatsapp.cancel_btn')) }}
                        });
                    } else {
                        confirmed = confirm({{ Js::from(__('whatsapp.disconnect_confirm_desc')) }});
                    }
                    if (!confirmed) return;
                    this.executeDisconnect('');
                },

                async submitDisconnectWithPin() {
                    if (this.disconnectLoading) return;
                    if (!this.disconnectPin.trim()) {
                        this.disconnectError = {{ Js::from(__('whatsapp.supervisor_pin_required')) }};
                        return;
                    }
                    this.disconnectLoading = true;
                    this.disconnectError = '';
                    await this.executeDisconnect(this.disconnectPin);
                    this.disconnectLoading = false;
                },

                async executeDisconnect(pin) {
                    try {
                        const res = await fetch('{{ route('whatsapp.meta.disconnect') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                            },
                            body: JSON.stringify({ pin: pin })
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.showDisconnectModal = false;
                            this.metaAccount = null;
                            this.isActive = false;
                            this.status = 'disconnected';
                            this.isMetaConfigured = false;
                            if (window.CoocaBus) {
                                window.CoocaBus.emitDataMutated('whatsapp', { disconnected: true });
                            }
                        } else {
                            const errMsg = data.message || {{ Js::from(__('whatsapp.supervisor_pin_invalid')) }};
                            if (this.showDisconnectModal) {
                                this.disconnectError = errMsg;
                            } else {
                                alert(errMsg);
                            }
                        }
                    } catch (e) {
                        console.error('Failed to disconnect Meta account', e);
                        if (this.showDisconnectModal) {
                            this.disconnectError = {{ Js::from(__('whatsapp.error_network_disconnect')) }};
                        }
                    }
                },

                async sendTest() {
                    if (this.testLoading) return;
                    if (!this.testPhone.trim() || !this.testMessage.trim()) {
                        this.testResult = {{ Js::from(__('whatsapp.test_validation_error')) }};
                        this.testOk = false;
                        return;
                    }
                    this.testLoading = true;
                    this.testResult = '';
                    try {
                        const res = await fetch('{{ route('whatsapp.test') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                            },
                            body: JSON.stringify({
                                phone: this.testPhone,
                                message: this.testMessage
                            })
                        });
                        const data = await res.json();
                        this.testOk = data.success ?? false;
                        this.testResult = this.testOk 
                            ? {{ Js::from(__('whatsapp.test_success')) }} 
                            : ({{ Js::from(__('whatsapp.test_failed_prefix')) }} + ': ' + (data.error || {{ Js::from(__('whatsapp.server_error')) }}));
                    } catch (e) {
                        this.testResult = {{ Js::from(__('whatsapp.test_network_error')) }};
                        this.testOk = false;
                    } finally {
                        this.testLoading = false;
                    }
                }
            };
        }
    </script>
@endpush
