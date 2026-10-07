@extends('layouts.app', [
    'title' => __('settings.page_title'),
    'headerTitle' => __('settings.header_title'),
    'headerSubtitle' => __('settings.header_subtitle'),
])

@section('content')
    <!-- Leaflet.js Assets for Interactive Business & Branch Mapping -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        .leaflet-container {
            font-family: inherit;
            z-index: 10 !important;
        }
        .custom-map-marker {
            background: transparent;
            border: none;
        }
    </style>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    @php
        $primaryBranchObj = $branches->firstWhere('is_primary', true) ?? $locations->firstWhere('is_primary', true);
        $defaultBranchLat = ($primaryBranchObj && !empty($primaryBranchObj->latitude)) ? (float) $primaryBranchObj->latitude : -6.2088;
        $defaultBranchLng = ($primaryBranchObj && !empty($primaryBranchObj->longitude)) ? (float) $primaryBranchObj->longitude : 106.8456;
    @endphp

    <div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="settingsPage()">

        <!-- ===================================================== -->
        <!-- 1. TOOLBAR / PAGE HEADER (Apple HIG 3-Baris Standard) -->
        <!-- ===================================================== -->
        <header
            class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 sm:p-6 space-y-4 shadow-sm">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div>
                    <!-- Overline Typographic (Anti-Pill) -->
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45 mb-1">
                        {{ __('settings.breadcrumb_settings') }} &bull; {{ __('settings.breadcrumb_profile') }}
                    </p>
                    <h1 class="text-2xl sm:text-3xl font-bold text-black dark:text-white tracking-tight">
                        {{ __('settings.header_title') }}
                    </h1>
                    <p class="text-[13px] text-black/55 dark:text-white/55 mt-1 leading-relaxed max-w-3xl">
                        {{ __('settings.header_desc') }}
                    </p>
                </div>
            </div>

            <!-- Apple-Style Segmented Controls Switcher (Deep-Linking Active) -->
            <div class="pt-2 border-t border-black/5 dark:border-white/5">
                <div
                    class="inline-flex p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] text-[13px] font-medium self-stretch sm:self-auto overflow-x-auto no-scrollbar max-w-full gap-1">
                    <button type="button" @click="activeTab = 'general'"
                        :class="activeTab === 'general' ?
                            'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' :
                            'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="min-h-[40px] px-4 py-2 rounded-[9px] transition-all flex items-center justify-center gap-2 shrink-0 active:scale-[0.98]">
                        <i data-lucide="building-2" class="w-4 h-4 text-[#007AFF]"></i>
                        <span>{{ __('settings.tab_general') }}</span>
                    </button>

                    <button type="button" @click="activeTab = 'branches'"
                        :class="activeTab === 'branches' ?
                            'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' :
                            'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="min-h-[40px] px-4 py-2 rounded-[9px] transition-all flex items-center justify-center gap-2 shrink-0 active:scale-[0.98]">
                        <i data-lucide="store" class="w-4 h-4 text-[#34C759]"></i>
                        <span>{{ __('settings.tab_branches') }}</span>
                        <span
                            class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] tabular-nums">{{ $branches->count() }}</span>
                    </button>

                    <button type="button" @click="activeTab = 'operations'"
                        :class="activeTab === 'operations' ?
                            'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' :
                            'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="min-h-[40px] px-4 py-2 rounded-[9px] transition-all flex items-center justify-center gap-2 shrink-0 active:scale-[0.98]">
                        <i data-lucide="clock" class="w-4 h-4 text-[#FF9500]"></i>
                        <span>{{ __('settings.tab_operations') }}</span>
                    </button>

                    @if ($business->isModuleEnabled('pos_retail') || $business->isModuleEnabled('pos_dinein'))
                        <button type="button" @click="activeTab = 'wa_receipt'"
                            :class="activeTab === 'wa_receipt' ?
                                'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' :
                                'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                            class="min-h-[40px] px-4 py-2 rounded-[9px] transition-all flex items-center justify-center gap-2 shrink-0 active:scale-[0.98]">
                            <i data-lucide="message-square" class="w-4 h-4 text-[#34C759]"></i>
                            <span>{{ __('settings.tab_wa_receipt') }}</span>
                        </button>
                    @endif

                    <button type="button" @click="activeTab = 'templates'"
                        :class="activeTab === 'templates' ?
                            'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' :
                            'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="min-h-[40px] px-4 py-2 rounded-[9px] transition-all flex items-center justify-center gap-2 shrink-0 active:scale-[0.98]">
                        <i data-lucide="layers" class="w-4 h-4 text-[#AF52DE]"></i>
                        <span>{{ __('settings.tab_templates') }}</span>
                        <span
                            class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-black/10 dark:bg-white/10 tabular-nums">{{ $templates->count() }}</span>
                    </button>

                    <button type="button" @click="activeTab = 'modules'"
                        :class="activeTab === 'modules' ?
                            'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' :
                            'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="min-h-[40px] px-4 py-2 rounded-[9px] transition-all flex items-center justify-center gap-2 shrink-0 active:scale-[0.98]">
                        <i data-lucide="layout-grid" class="w-4 h-4 text-[#007AFF]"></i>
                        <span>{{ __('settings.tab_modules') }}</span>
                        <span
                            class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-[#007AFF]/15 text-[#007AFF] tabular-nums"
                            x-text="activeModulesCount + '/' + totalModulesCount">
                            {{ count($allModules) - count($business->disabled_modules ?? []) }}/{{ count($allModules) }}
                        </span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Governance & Security Fast Links -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
            <a href="{{ route('settings.audit-logs.index') }}"
                class="p-4 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 hover:border-[#FF3B30]/30 transition-all flex items-center justify-between group shadow-sm active:scale-[0.99]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[10px] bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center shrink-0">
                        <i data-lucide="shield-alert" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-[13px] font-semibold text-black dark:text-white group-hover:text-[#FF3B30] transition-colors">{{ __('settings.link_audit_logs_title') }}</p>
                        <p class="text-[11px] text-black/50 dark:text-white/50">{{ __('settings.link_audit_logs_desc') }}</p>
                    </div>
                </div>
                <i data-lucide="chevron-right" class="w-4 h-4 text-black/30 dark:text-white/30 group-hover:translate-x-0.5 transition-transform"></i>
            </a>
            <a href="{{ route('approval-rules.index') }}"
                class="p-4 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 hover:border-[#007AFF]/30 transition-all flex items-center justify-between group shadow-sm active:scale-[0.99]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-[13px] font-semibold text-black dark:text-white group-hover:text-[#007AFF] transition-colors">{{ __('settings.link_approval_rules_title') }}</p>
                        <p class="text-[11px] text-black/50 dark:text-white/50">{{ __('settings.link_approval_rules_desc') }}</p>
                    </div>
                </div>
                <i data-lucide="chevron-right" class="w-4 h-4 text-black/30 dark:text-white/30 group-hover:translate-x-0.5 transition-transform"></i>
            </a>
            <a href="{{ route('roles.index') }}"
                class="p-4 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 hover:border-[#34C759]/30 transition-all flex items-center justify-between group shadow-sm active:scale-[0.99]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                        <i data-lucide="shield" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-[13px] font-semibold text-black dark:text-white group-hover:text-[#34C759] transition-colors">{{ __('settings.link_roles_title') }}</p>
                        <p class="text-[11px] text-black/50 dark:text-white/50">{{ __('settings.link_roles_desc') }}</p>
                    </div>
                </div>
                <i data-lucide="chevron-right" class="w-4 h-4 text-black/30 dark:text-white/30 group-hover:translate-x-0.5 transition-transform"></i>
            </a>
            <a href="{{ route('settings.integrations.mcp.index') }}"
                class="p-4 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 hover:border-[#AF52DE]/30 transition-all flex items-center justify-between group shadow-sm active:scale-[0.99]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[10px] bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center shrink-0">
                        <i data-lucide="bot" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-[13px] font-semibold text-black dark:text-white group-hover:text-[#AF52DE] transition-colors">Integrasi AI (MCP)</p>
                        <p class="text-[11px] text-black/50 dark:text-white/50">Claude, ChatGPT, Gemini, Cursor</p>
                    </div>
                </div>
                <i data-lucide="chevron-right" class="w-4 h-4 text-black/30 dark:text-white/30 group-hover:translate-x-0.5 transition-transform"></i>
            </a>
        </div>

        <!-- ===================================================== -->
        <!-- TAB 1: PROFIL BISNIS, LOGO, BANK & PEMBULATAN        -->
        <!-- ===================================================== -->
        <div x-show="activeTab === 'general'" class="space-y-6">
            <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="_tab" value="general">

                <!-- Section 1: Logo & Identitas Kop Surat -->
                <div
                    class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4 border-b border-black/5 dark:border-white/5 pb-4">
                        <div>
                            <h2 class="text-[17px] font-semibold text-black dark:text-white">{{ __('settings.company_identity') }}</h2>
                            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">{{ __('settings.company_identity_desc') }}</p>
                        </div>
                        <span
                            class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#007AFF]/10 text-[#007AFF]">
                            {{ __('settings.official_letterhead') }}
                        </span>
                    </div>

                    <!-- Logo Section -->
                    <div
                        class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 flex flex-col sm:flex-row items-center gap-5">
                        <div
                            class="w-24 h-24 rounded-[14px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 flex items-center justify-center overflow-hidden shrink-0 shadow-[0_1px_3px_rgba(0,0,0,0.05)]">
                            <template x-if="logoPreview">
                                <img :src="logoPreview" alt="Logo Bisnis" class="w-full h-full object-contain p-2">
                            </template>
                            <template x-if="!logoPreview">
                                <div class="text-center p-2 text-black/30 dark:text-white/30">
                                    <svg class="w-8 h-8 mx-auto mb-1 stroke-current" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                    </svg>
                                    <span class="text-[10px] font-medium block">{{ __('settings.no_logo_yet') }}</span>
                                </div>
                            </template>
                        </div>

                        <div class="flex-1 space-y-2 text-left w-full">
                            <label for="biz_logo_input"
                                class="block text-[13px] font-semibold text-black dark:text-white">{{ __('settings.upload_logo') }}</label>
                            <input type="file" name="logo" id="biz_logo_input" accept="image/*"
                                @change="handleLogoChange($event)"
                                class="block w-full text-[13px] text-black/60 dark:text-white/60 file:mr-3 file:py-2 file:px-3 file:rounded-[8px] file:border-0 file:text-[12px] file:font-semibold file:bg-[#007AFF] file:text-white hover:file:bg-[#0071E3] active:file:scale-[0.97] file:transition file:cursor-pointer cursor-pointer">
                            <p class="text-[12px] text-black/40 dark:text-white/40">
                                {{ __('settings.upload_logo_hint') }}
                            </p>

                            @if ($business->logo_path)
                                <label
                                    class="inline-flex items-center gap-2 mt-1.5 cursor-pointer select-none text-[13px] text-[#FF3B30] font-medium">
                                    <input type="checkbox" name="remove_logo" value="1"
                                        class="rounded-[4px] border-black/20 text-[#FF3B30] focus:ring-[#FF3B30]">
                                    <span>{{ __('settings.remove_current_logo') }}</span>
                                </label>
                            @endif
                        </div>
                    </div>

                    <!-- Input Grid: Nama & NPWP -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="biz_name_input"
                                class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                                {{ __('settings.business_name') }} <span class="text-[#FF3B30]">*</span>
                            </label>
                            <input type="text" id="biz_name_input" name="name"
                                value="{{ old('name', $business->name) }}" required
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[15px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <p class="mt-1 text-[11px] text-black/40 dark:text-white/40">{{ __('settings.business_name_hint') }}</p>
                        </div>
                        <div>
                            <label for="biz_tax_input"
                                class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                                {{ __('settings.tax_id') }}
                            </label>
                            <input type="text" id="biz_tax_input" name="tax_identification_number"
                                value="{{ old('tax_identification_number', $business->tax_identification_number) }}"
                                placeholder="01.234.567.8-901.000"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[15px] text-black dark:text-white tabular-nums placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                    </div>

                    <!-- Input Grid: Phone & Email -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="biz_phone_input"
                                class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                                {{ __('settings.phone') }}
                            </label>
                            <input type="text" id="biz_phone_input" name="phone"
                                value="{{ old('phone', $business->phone) }}" placeholder="0812-3456-7890"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[15px] text-black dark:text-white tabular-nums placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                        <div>
                            <label for="biz_email_input"
                                class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                                {{ __('settings.email') }}
                            </label>
                            <input type="email" id="biz_email_input" name="email"
                                value="{{ old('email', $business->email) }}" placeholder="finance@perusahaan.com"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[15px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                    </div>

                    <!-- Address -->
                    <div>
                        <label for="biz_address_input"
                            class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            {{ __('settings.address') }}
                        </label>
                        <textarea id="biz_address_input" name="address" rows="2"
                            placeholder="Jl. Sudirman No. 45, Gedung Cyber Lt. 5, Jakarta..."
                            class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-3 text-[16px] sm:text-[15px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition resize-none">{{ old('address', $business->address) }}</textarea>
                    </div>
                </div>

                <!-- Section 2: Konfigurasi POS & Pajak (Auto-Hidden if POS Module Disabled) -->
                @if ($business->isModuleEnabled('pos_retail') || $business->isModuleEnabled('pos_dinein'))
                    <div
                        class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4 shadow-sm">
                        <div class="border-b border-black/5 dark:border-white/5 pb-3">
                            <h2 class="text-[17px] font-semibold text-black dark:text-white">{{ __('settings.pos_config') }}</h2>
                            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">{{ __('settings.pos_config_desc') }}</p>
                        </div>

                        <div
                            class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 divide-y divide-black/5 dark:divide-white/5 overflow-hidden">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 gap-3">
                                <div>
                                    <p class="text-[15px] font-medium text-black dark:text-white">{{ __('settings.sales_tax') }}</p>
                                    <p class="text-[13px] text-black/50 dark:text-white/50">{{ __('settings.sales_tax_desc') }}</p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <input type="hidden" name="pos_enable_tax" value="0">
                                    <!-- Apple iOS Toggle Switch -->
                                    <label class="relative inline-flex items-center cursor-pointer select-none">
                                        <input type="checkbox" name="pos_enable_tax" value="1"
                                            {{ $business->pos_enable_tax ? 'checked' : '' }} class="sr-only peer">
                                        <div
                                            class="w-11 h-6 bg-black/15 peer-focus:outline-none rounded-full peer dark:bg-white/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all after:shadow-sm peer-checked:bg-[#34C759]">
                                        </div>
                                    </label>
                                    <div class="flex items-center gap-1.5">
                                        <input type="number" name="pos_tax_percent"
                                            value="{{ old('pos_tax_percent', $business->pos_tax_percent ?? 0) }}"
                                            min="0" max="100" step="0.01"
                                            class="w-20 h-10 text-right bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[8px] px-2.5 text-[15px] sm:text-[13px] font-semibold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                        <span class="text-[13px] font-semibold text-black/50 dark:text-white/50">%</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-4">
                                <div>
                                    <p class="text-[15px] font-medium text-black dark:text-white">{{ __('settings.pos_product_photos') }}</p>
                                    <p class="text-[13px] text-black/50 dark:text-white/50">{{ __('settings.pos_product_photos_desc') }}</p>
                                </div>
                                <input type="hidden" name="pos_show_product_images" value="0">
                                <!-- Apple iOS Toggle Switch -->
                                <label class="relative inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" name="pos_show_product_images" value="1"
                                        {{ $business->pos_show_product_images ? 'checked' : '' }} class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-black/15 peer-focus:outline-none rounded-full peer dark:bg-white/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all after:shadow-sm peer-checked:bg-[#007AFF]">
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Section 3: Rekening Bank Resmi -->
                <div
                    class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4 shadow-sm">
                    <div class="border-b border-black/5 dark:border-white/5 pb-3">
                        <h2 class="text-[17px] font-semibold text-black dark:text-white">{{ __('settings.bank_instructions') }}</h2>
                        <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">{{ __('settings.bank_instructions_desc') }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="bank_name_input"
                                class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('settings.bank_name') }}</label>
                            <input type="text" id="bank_name_input" name="bank_name"
                                value="{{ old('bank_name', $business->bank_name) }}"
                                placeholder="BCA / Mandiri / BNI / BRI"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[15px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                        <div>
                            <label for="bank_acc_num_input"
                                class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('settings.bank_account_number') }}</label>
                            <input type="text" id="bank_acc_num_input" name="bank_account_number"
                                value="{{ old('bank_account_number', $business->bank_account_number) }}"
                                placeholder="123-456-7890"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[15px] text-black dark:text-white tabular-nums placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                        <div>
                            <label for="bank_holder_input"
                                class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('settings.bank_account_holder') }}</label>
                            <input type="text" id="bank_holder_input" name="bank_account_holder"
                                value="{{ old('bank_account_holder', $business->bank_account_holder) }}"
                                placeholder="PT Usaha Bersama"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[15px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                    </div>
                </div>

                <!-- Section 4: Mata Uang & Pembulatan HPP -->
                <div
                    class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4 shadow-sm">
                    <div class="border-b border-black/5 dark:border-white/5 pb-3">
                        <h2 class="text-[17px] font-semibold text-black dark:text-white">{{ __('settings.currency_rounding') }}</h2>
                        <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">{{ __('settings.currency_rounding_desc') }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="currency_code_input"
                                class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('settings.currency_code') }}</label>
                            <input type="text" id="currency_code_input" name="currency_code"
                                value="{{ old('currency_code', $business->currency_code) }}" required
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[15px] text-black dark:text-white tabular-nums uppercase focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                        <div>
                            <label for="currency_symbol_input"
                                class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('settings.currency_symbol') }}</label>
                            <input type="text" id="currency_symbol_input" name="currency_symbol"
                                value="{{ old('currency_symbol', $business->currency_symbol) }}" required
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[15px] text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                    </div>

                    <div class="space-y-2 pt-2">
                        <label for="rounding_strategy_select"
                            class="block text-[13px] font-medium text-black/70 dark:text-white/70">{{ __('settings.rounding_strategy') }}</label>
                        <select id="rounding_strategy_select" name="rounding_strategy" required
                            x-model="roundingStrategy"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition cursor-pointer">
                            <option value="ROUND">ROUND - Pembulatan Standar Terdekat</option>
                            <option value="CEIL">CEIL - Pembulatan Ke Atas (Plafon)</option>
                            <option value="FLOOR">FLOOR - Pembulatan Ke Bawah</option>
                            <option value="ROUND_50">ROUND_50 - Kelipatan 50 Terdekat</option>
                            <option value="ROUND_100">ROUND_100 - Kelipatan 100 Terdekat (Standar Bisnis UMKM)</option>
                            <option value="ROUND_500">ROUND_500 - Kelipatan 500 Terdekat</option>
                            <option value="ROUND_1000">ROUND_1000 - Kelipatan 1.000 Terdekat</option>
                        </select>

                        <div
                            class="rounded-[10px] bg-[#007AFF]/8 border border-[#007AFF]/15 p-3 text-[13px] text-[#007AFF] font-medium flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                            </svg>
                            <span>{{ __('settings.rounding_simulation') }}: <strong class="tabular-nums font-semibold"
                                    x-text="getRoundingExample(roundingStrategy)"></strong></span>
                        </div>
                    </div>
                </div>

                <!-- Section 5: Keamanan Kasir & PIN Otorisasi Supervisor (Auto-Hidden if POS Module Disabled) -->
                @if ($business->isModuleEnabled('pos_retail') || $business->isModuleEnabled('pos_dinein'))
                    <div
                        class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 shadow-sm">
                        <div class="flex items-start justify-between gap-4 border-b border-black/5 dark:border-white/5 pb-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="text-[17px] font-semibold text-black dark:text-white">{{ __('settings.pos_security') }}</h2>
                                    @if (!empty($business->pos_supervisor_pin))
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                            {{ __('settings.pin_status_custom') }}
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/15 text-[#C97700] dark:text-[#FF9F0A]">
                                            {{ __('settings.pin_status_default') }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">{{ __('settings.pos_security_desc') }}</p>
                            </div>
                            <span
                                class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#AF52DE]/10 text-[#AF52DE]">
                                POS Security
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <!-- PIN Input -->
                            <div>
                                <label for="pos_supervisor_pin_input"
                                    class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                                    {{ __('settings.pos_supervisor_pin') }}
                                </label>
                                <input type="password" id="pos_supervisor_pin_input" name="pos_supervisor_pin"
                                    inputmode="numeric" pattern="[0-9]*" maxlength="8"
                                    placeholder="{{ !empty($business->pos_supervisor_pin) ? __('settings.pin_placeholder_masked') : __('settings.pin_placeholder_empty') }}"
                                    class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[15px] font-mono tracking-widest text-black dark:text-white placeholder:font-sans placeholder:tracking-normal placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                <p class="mt-1 text-[11px] text-black/40 dark:text-white/40">{{ __('settings.pos_supervisor_pin_hint') }}</p>
                            </div>

                            <!-- Max Cashier Discount -->
                            <div>
                                <label for="pos_max_discount_input"
                                    class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                                    {{ __('settings.pos_max_cashier_discount') }}
                                </label>
                                <div class="relative">
                                    <input type="number" step="0.5" min="0" max="100"
                                        id="pos_max_discount_input" name="pos_max_cashier_discount_percent"
                                        value="{{ old('pos_max_cashier_discount_percent', $business->pos_max_cashier_discount_percent) }}"
                                        placeholder="10"
                                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-3.5 pr-8 text-[16px] sm:text-[15px] tabular-nums text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    <span
                                        class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[13px] font-semibold text-black/40 dark:text-white/40">%</span>
                                </div>
                                <p class="mt-1 text-[11px] text-black/40 dark:text-white/40">{{ __('settings.pos_max_cashier_discount_hint') }}</p>
                            </div>
                        </div>

                        <!-- Toggles for Void & Refund -->
                        <div class="pt-2 border-t border-black/5 dark:border-white/5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label
                                class="flex items-start gap-3 p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 cursor-pointer hover:bg-black/[0.04] dark:hover:bg-white/[0.05] transition min-h-[48px]">
                                <input type="checkbox" name="pos_require_pin_for_void" value="1"
                                    {{ old('pos_require_pin_for_void', $business->pos_require_pin_for_void) ? 'checked' : '' }}
                                    class="mt-0.5 rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                                <div class="text-[13px]">
                                    <span class="font-medium text-black dark:text-white block">{{ __('settings.pos_require_pin_void') }}</span>
                                    <span class="text-[11px] text-black/50 dark:text-white/50 block mt-0.5">{{ __('settings.pos_require_pin_void_desc') }}</span>
                                </div>
                            </label>

                            <label
                                class="flex items-start gap-3 p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 cursor-pointer hover:bg-black/[0.04] dark:hover:bg-white/[0.05] transition min-h-[48px]">
                                <input type="checkbox" name="pos_require_pin_for_refund" value="1"
                                    {{ old('pos_require_pin_for_refund', $business->pos_require_pin_for_refund) ? 'checked' : '' }}
                                    class="mt-0.5 rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                                <div class="text-[13px]">
                                    <span class="font-medium text-black dark:text-white block">{{ __('settings.pos_require_pin_refund') }}</span>
                                    <span class="text-[11px] text-black/50 dark:text-white/50 block mt-0.5">{{ __('settings.pos_require_pin_refund_desc') }}</span>
                                </div>
                            </label>
                        </div>
                    </div>
                @endif

                @if (\App\Support\Context::hasPermission('settings.edit'))
                    <div class="flex items-center justify-end pt-2">
                        <button type="submit"
                            class="w-full sm:w-auto h-11 px-6 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-2 shadow-[0_1px_2px_rgba(0,122,255,0.25)] min-h-[44px]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            <span>{{ __('settings.save_business_profile') }}</span>
                        </button>
                    </div>
                @endif
            </form>
        </div>

        <!-- ===================================================== -->
        <!-- TAB 2: MANAJEMEN CABANG & TOKO (MULTI-OUTLET HUB)     -->
        <!-- ===================================================== -->
        <div x-show="activeTab === 'branches'" class="space-y-6" style="display: none;">
            {{-- Header Card --}}
            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-start sm:items-center gap-3.5">
                    <div class="w-11 h-11 rounded-[14px] bg-[#34C759]/12 text-[#34C759] flex items-center justify-center shrink-0 border border-[#34C759]/20">
                        <i data-lucide="store" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-[17px] font-semibold text-black dark:text-white flex items-center gap-2">
                            <span>{{ __('settings.branch_management_title') }}</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] tabular-nums">
                                {{ $branches->count() }}
                            </span>
                        </h2>
                        <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5 leading-relaxed">
                            {{ __('settings.branch_management_desc') }}
                        </p>
                    </div>
                </div>

                @if (\App\Support\Context::hasPermission('settings.edit') || \App\Support\Context::isAdminOrOwner())
                    <button type="button" @click="openCreateBranch()"
                            class="w-full sm:w-auto h-11 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#34C759] hover:bg-[#28A745] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-[0_2px_4px_rgba(52,199,89,0.25)] shrink-0 cursor-pointer min-h-[44px]">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>{{ __('settings.add_branch_button') }}</span>
                    </button>
                @endif
            </div>

            {{-- Info Callout: Relasi 1 Cabang Banyak Gudang --}}
            <div class="rounded-[16px] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 border border-[#007AFF]/20 p-4.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div class="flex items-start sm:items-center gap-3">
                    <div class="w-9 h-9 rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center shrink-0">
                        <i data-lucide="info" class="w-4.5 h-4.5"></i>
                    </div>
                    <div class="text-[12px] leading-relaxed text-slate-700 dark:text-slate-300">
                        <strong class="font-semibold text-slate-900 dark:text-white">Arsitektur 1 Cabang Banyak Gudang:</strong>
                        Setiap cabang fisik dapat memiliki beberapa gudang persediaan (contoh: Gudang Depan Toko, Gudang Belakang, atau Gudang Dingin). Manajemen stok dan pergerakan barang dikelola di modul <a href="{{ route('warehouse.index') }}" class="text-[#007AFF] font-bold hover:underline">Gudang & Logistik</a>.
                    </div>
                </div>
                <a href="{{ route('warehouse.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[12px] font-semibold text-[#007AFF] hover:bg-black/[0.03] dark:hover:bg-white/[0.05] transition-all shrink-0">
                    <i data-lucide="warehouse" class="w-3.5 h-3.5"></i>
                    <span>Buka Hub Gudang</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            {{-- Bento Grid Daftar Cabang --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse ($branches as $branch)
                    <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 flex flex-col justify-between space-y-4 shadow-sm hover:shadow-md transition-all">
                        {{-- Top Card: Header & Badges --}}
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-11 h-11 rounded-[14px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0 border border-[#34C759]/20">
                                        <i data-lucide="store" class="w-5 h-5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h3 class="text-[15px] font-bold text-black dark:text-white truncate">
                                                {{ $branch->name }}
                                            </h3>
                                            @if ($branch->is_primary)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500]/15 text-[#D97706] dark:text-[#FBBF24] border border-[#FF9500]/30">
                                                    <i data-lucide="building" class="w-3 h-3"></i>
                                                    <span>{{ __('settings.branch_primary_badge') }}</span>
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2 mt-0.5 text-[11px] text-black/50 dark:text-white/50">
                                            @if($branch->code)
                                                <span class="font-mono font-bold">{{ $branch->code }}</span>
                                                <span>&bull;</span>
                                            @endif
                                            <span>{{ __('settings.branch_type_outlet') }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Status Pill --}}
                                <div>
                                    @if($branch->is_active)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                            <span>{{ __('settings.branch_active_badge') }}</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-black/5 dark:bg-white/10 text-black/50 dark:text-white/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-black/30 dark:bg-white/30"></span>
                                            <span>{{ __('settings.branch_inactive_badge') }}</span>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Alamat & Kontak --}}
                            <div class="mt-3.5 space-y-1.5 text-[12px] text-black/70 dark:text-white/70">
                                <div class="flex items-start gap-2">
                                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-black/40 dark:text-white/40 shrink-0 mt-0.5"></i>
                                    <span class="line-clamp-2">{{ $branch->address ?: 'Alamat belum diisi' }}</span>
                                </div>
                                @if($branch->phone)
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="phone" class="w-3.5 h-3.5 text-black/40 dark:text-white/40 shrink-0"></i>
                                        <span class="font-mono">{{ $branch->phone }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Section Metadata Bento --}}
                            <div class="mt-3.5 pt-3 border-t border-black/5 dark:border-white/5 grid grid-cols-2 gap-2 text-[11px]">
                                <div class="p-2 rounded-[8px] bg-black/[0.02] dark:bg-white/[0.03]">
                                    <span class="text-black/40 dark:text-white/40 block">Geofence Presensi:</span>
                                    <span class="font-semibold text-black dark:text-white tabular-nums">{{ $branch->geofence_radius_meters ?? 100 }} m</span>
                                </div>
                                <div class="p-2 rounded-[8px] bg-black/[0.02] dark:bg-white/[0.03]">
                                    <span class="text-black/40 dark:text-white/40 block">Zona Waktu:</span>
                                    <span class="font-semibold text-black dark:text-white truncate block">{{ $branch->getTimezone() }}</span>
                                </div>
                            </div>

                            {{-- Connected Warehouses Bento Tile --}}
                            <div class="mt-3 p-2.5 rounded-[10px] bg-[#007AFF]/[0.04] dark:bg-[#007AFF]/[0.08] border border-[#007AFF]/15 flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="w-6 h-6 rounded-[6px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center shrink-0">
                                        <i data-lucide="boxes" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="text-[12px] font-bold text-black dark:text-white block tabular-nums">
                                            {{ $branch->children->count() }} Gudang Terhubung
                                        </span>
                                    </div>
                                </div>
                                <a href="{{ route('warehouse.index') }}?parent_id={{ $branch->id }}"
                                   class="text-[11px] font-semibold text-[#007AFF] hover:underline inline-flex items-center gap-0.5 shrink-0">
                                    <span>{{ __('settings.view_branch_warehouses') }}</span>
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        </div>

                        {{-- Footer Actions --}}
                        <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-2">
                            @if (\App\Support\Context::hasPermission('settings.edit') || \App\Support\Context::isAdminOrOwner())
                                <button type="button" @click="openEditBranch('{{ $branch->id }}')"
                                        class="flex-1 h-9 px-3 rounded-[9px] text-[12px] font-semibold text-black/80 dark:text-white/80 bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    <span>{{ __('settings.edit_branch') }}</span>
                                </button>
                                @if(!$branch->is_primary)
                                    <button type="button" @click="openDeleteBranch('{{ $branch->id }}')"
                                            class="w-9 h-9 rounded-[9px] text-[#FF3B30] hover:bg-[#FF3B30]/10 active:scale-[0.98] transition-all flex items-center justify-center shrink-0 cursor-pointer"
                                            title="{{ __('settings.delete_branch') }}">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 text-center space-y-3">
                        <div class="w-12 h-12 rounded-[16px] bg-black/5 dark:bg-white/5 text-black/30 dark:text-white/30 flex items-center justify-center mx-auto">
                            <i data-lucide="store" class="w-6 h-6"></i>
                        </div>
                        <h4 class="text-[15px] font-bold text-black dark:text-white">{{ __('settings.no_branches_found') }}</h4>
                        <p class="text-[12px] text-black/50 dark:text-white/50 max-w-sm mx-auto">{{ __('settings.no_branches_found_desc') }}</p>
                        @if (\App\Support\Context::hasPermission('settings.edit') || \App\Support\Context::isAdminOrOwner())
                            <button type="button" @click="openCreateBranch()"
                                    class="h-10 px-4 rounded-[10px] text-[12px] font-bold text-white bg-[#34C759] hover:bg-[#28A745] active:scale-[0.98] transition-all inline-flex items-center gap-1.5">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                <span>{{ __('settings.add_branch_button') }}</span>
                            </button>
                        @endif
                    </div>
                @endforelse
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- TAB: OPERASIONAL, TIMEZONE & JAM KERJA                -->
        <!-- ===================================================== -->
        <div x-show="activeTab === 'operations'" class="space-y-6" style="display: none;">
            <form action="{{ route('settings.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="_tab" value="operations">
                <input type="hidden" name="operating_hours_json" :value="JSON.stringify(operatingHours)">

                <!-- 1. Header Card & Timezone Configuration -->
                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-6 shadow-sm">
                    <div class="border-b border-black/5 dark:border-white/5 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[10px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center shrink-0">
                                <i data-lucide="clock" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-[16px] font-semibold text-black dark:text-white tracking-tight">{{ __('settings.timezone_title') }}</h3>
                                <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('settings.timezone_desc') }}</p>
                            </div>
                        </div>

                        <!-- Live Digital Clock Badge -->
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] text-[12px] font-medium text-black/70 dark:text-white/70">
                            <span class="w-2 h-2 rounded-full bg-[#34C759] animate-pulse"></span>
                            <span>{{ __('settings.business_time') }}:</span>
                            <span class="font-semibold text-black dark:text-white tabular-nums">{{ \App\Support\TimezoneHelper::formatLocal(now(), $currentTimezone, 'H:i:s') }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                        <div>
                            <label for="business_timezone" class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                                {{ __('settings.base_timezone') }} <span class="text-[#FF3B30]">*</span>
                            </label>
                            <div class="relative">
                                <select id="business_timezone" name="timezone" x-model="selectedTimezone"
                                    class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition cursor-pointer">
                                    @foreach ($timezones as $groupLabel => $zones)
                                        <optgroup label="{{ $groupLabel }}">
                                            @foreach ($zones as $zoneId => $zoneName)
                                                <option value="{{ $zoneId }}" {{ $currentTimezone === $zoneId ? 'selected' : '' }}>
                                                    {{ $zoneName }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <p class="mt-1.5 text-[11px] text-black/45 dark:text-white/45 leading-relaxed">
                                {{ __('settings.base_timezone_hint') }}
                            </p>
                        </div>

                        <div class="p-4 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2">
                            <div class="flex items-center gap-2 text-[12px] font-semibold text-black dark:text-white">
                                <i data-lucide="info" class="w-4 h-4 text-[#007AFF]"></i>
                                <span>{{ __('settings.single_source_truth') }}</span>
                            </div>
                            <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                                {{ __('settings.single_source_truth_desc') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 2. Weekly Operating Hours Bento Card -->
                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-6 shadow-sm">
                    <div class="border-b border-black/5 dark:border-white/5 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h3 class="text-[16px] font-semibold text-black dark:text-white tracking-tight">{{ __('settings.weekly_schedule') }}</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('settings.weekly_schedule_desc') }}</p>
                        </div>
                        <div class="text-[11px] font-medium text-black/40 dark:text-white/40">
                            {{ __('settings.format_24h') }}
                        </div>
                    </div>

                    <div class="space-y-3.5">
                        <template x-for="(dayData, dayKey) in operatingHours" :key="dayKey">
                            <div class="p-4 rounded-[14px] border transition-all"
                                :class="dayData.is_open ? 'bg-white dark:bg-[#242426] border-black/10 dark:border-white/10 shadow-xs' : 'bg-black/[0.02] dark:bg-white/[0.02] border-black/5 dark:border-white/5 opacity-75'">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                                    <div class="flex items-center gap-3">
                                        <label class="relative inline-flex items-center cursor-pointer min-h-[44px]">
                                            <input type="checkbox" x-model="dayData.is_open" class="sr-only peer">
                                            <div class="w-10 h-6 bg-black/20 peer-focus:outline-none rounded-full peer dark:bg-white/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]"></div>
                                        </label>
                                        <span class="text-[14px] font-semibold text-black dark:text-white w-24" x-text="dayData.day_name"></span>
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-medium"
                                            :class="dayData.is_open ? 'bg-[#34C759]/10 text-[#34C759]' : 'bg-black/10 dark:bg-white/10 text-black/50 dark:text-white/50'"
                                            x-text="dayData.is_open ? '{{ __('settings.open') }}' : '{{ __('settings.closed') }}'">
                                        </span>
                                    </div>

                                    <template x-if="dayData.is_open">
                                        <button type="button" @click="addPeriod(dayKey)"
                                            class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-[8px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition active:scale-95 min-h-[36px]">
                                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('settings.add_shift') }}</span>
                                        </button>
                                    </template>
                                </div>

                                <template x-if="!dayData.is_open">
                                    <p class="text-[12px] text-black/40 dark:text-white/40 italic pl-0 sm:pl-12">
                                        {{ __('settings.shop_closed_all_day') }}
                                    </p>
                                </template>

                                <template x-if="dayData.is_open">
                                    <div class="space-y-2.5 pl-0 sm:pl-12">
                                        <template x-for="(period, pIndex) in dayData.periods" :key="pIndex">
                                            <div class="flex flex-wrap items-center gap-2.5 p-2.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04]">
                                                <span class="text-[12px] font-medium text-black/50 dark:text-white/50 w-16" x-text="'Sesi ' + (pIndex + 1) + ':'"></span>
                                                
                                                <div class="flex items-center gap-2">
                                                    <input type="time" x-model="period.start" required
                                                        class="h-10 px-2.5 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[8px] text-[15px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                                                    <span class="text-[12px] text-black/40 dark:text-white/40">{{ __('settings.to') }}</span>
                                                    <input type="time" x-model="period.end" required
                                                        class="h-10 px-2.5 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[8px] text-[15px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                                                </div>

                                                <!-- Overnight Indicator Badge -->
                                                <template x-if="isOvernight(period)">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#AF52DE]/10 text-[#AF52DE]">
                                                        <i data-lucide="moon" class="w-3 h-3"></i>
                                                        <span>{{ __('settings.overnight') }}</span>
                                                    </span>
                                                </template>

                                                <template x-if="dayData.periods.length > 1">
                                                    <button type="button" @click="removePeriod(dayKey, pIndex)"
                                                        class="p-2 rounded-[8px] text-black/40 hover:text-[#FF3B30] hover:bg-[#FF3B30]/10 transition ml-auto min-w-[36px] min-h-[36px] flex items-center justify-center">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- 3. Branch Outlets Timezone & Operating Hours Status -->
                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-black/5 dark:border-white/5 gap-2">
                        <div>
                            <h3 class="text-[16px] font-semibold text-black dark:text-white tracking-tight">{{ __('settings.branch_operations_status') }}</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('settings.branch_operations_desc') }}</p>
                        </div>
                        @if (\App\Support\Context::hasPermission('warehouse.view') || \App\Support\Context::isAdminOrOwner())
                            <a href="{{ route('warehouse.index') }}" class="text-[12px] font-semibold text-[#007AFF] hover:underline inline-flex items-center gap-1 min-h-[36px]">
                                <span>{{ __('settings.branch_management') }}</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </a>
                        @endif
                    </div>

                    <!-- Desktop Table (md and up) -->
                    <div class="hidden md:block overflow-x-auto no-scrollbar">
                        <table class="w-full text-left text-[13px]">
                            <thead>
                                <tr class="text-[11px] font-semibold text-black/40 dark:text-white/40 uppercase tracking-wider border-b border-black/5 dark:border-white/5">
                                    <th class="py-2.5 pr-4">{{ __('settings.branch_name') }}</th>
                                    <th class="py-2.5 px-4">{{ __('settings.branch_type') }}</th>
                                    <th class="py-2.5 px-4">{{ __('settings.branch_timezone') }}</th>
                                    <th class="py-2.5 px-4">{{ __('settings.branch_schedule_mode') }}</th>
                                    <th class="py-2.5 pl-4 text-right">{{ __('settings.action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-black/5 dark:divide-white/5">
                                @forelse ($locations as $loc)
                                    <tr>
                                        <td class="py-3 pr-4 font-semibold text-black dark:text-white">
                                            {{ $loc->name }}
                                            @if ($loc->is_primary)
                                                <span class="ml-1.5 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-[#007AFF]/10 text-[#007AFF]">{{ __('settings.primary_outlet') }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-black/60 dark:text-white/60 capitalize">
                                            {{ $loc->type }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-medium text-black dark:text-white tabular-nums">{{ $loc->getTimezone() }}</span>
                                                @if ($loc->timezone_mode === 'custom' && !empty($loc->timezone))
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-[#FF9500]/10 text-[#FF9500]">{{ __('settings.custom_mode') }}</span>
                                                @else
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-black/5 dark:bg-white/10 text-black/50 dark:text-white/50">{{ __('settings.follow_center') }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-3 px-4">
                                            @if ($loc->operating_hours_mode === 'custom' && !empty($loc->operating_hours))
                                                <span class="inline-flex items-center gap-1.5 text-[12px] font-medium text-[#FF9500]">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span>
                                                    <span>{{ __('settings.custom_branch_schedule') }}</span>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 text-[12px] font-medium text-[#34C759]">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                                    <span>{{ __('settings.follow_center_schedule') }}</span>
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 pl-4 text-right">
                                            @if (\App\Support\Context::hasPermission('warehouse.manage') || \App\Support\Context::isAdminOrOwner())
                                                <a href="{{ route('warehouse.edit', $loc) }}"
                                                    class="inline-flex items-center justify-center px-3 py-1.5 rounded-[8px] text-[12px] font-medium text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 transition min-h-[36px]">
                                                    {{ __('settings.change_schedule') }}
                                                </a>
                                            @elseif (\App\Support\Context::hasPermission('warehouse.view'))
                                                <a href="{{ route('warehouse.show', $loc) }}"
                                                    class="inline-flex items-center justify-center px-3 py-1.5 rounded-[8px] text-[12px] font-medium text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 transition min-h-[36px]">
                                                    {{ __('settings.view_details') }}
                                                </a>
                                            @else
                                                <span class="text-[12px] text-black/40 dark:text-white/40">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 text-center text-black/40 dark:text-white/40 italic">
                                            {{ __('settings.no_branches_yet') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Responsive Cards (md:hidden) -->
                    <div class="md:hidden space-y-3">
                        @forelse ($locations as $loc)
                            <div class="p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <h4 class="text-[14px] font-semibold text-black dark:text-white">{{ $loc->name }}</h4>
                                        <span class="text-[11px] text-black/50 dark:text-white/50 capitalize">{{ $loc->type }}</span>
                                    </div>
                                    @if ($loc->is_primary)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#007AFF]/10 text-[#007AFF]">{{ __('settings.primary_outlet') }}</span>
                                    @endif
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-[12px] pt-1 border-t border-black/5 dark:border-white/5">
                                    <div>
                                        <span class="text-[11px] text-black/40 dark:text-white/40 block">{{ __('settings.branch_timezone') }}</span>
                                        <span class="font-medium text-black dark:text-white tabular-nums">{{ $loc->getTimezone() }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[11px] text-black/40 dark:text-white/40 block">{{ __('settings.branch_schedule_mode') }}</span>
                                        <span class="font-medium {{ $loc->operating_hours_mode === 'custom' ? 'text-[#FF9500]' : 'text-[#34C759]' }}">
                                            {{ $loc->operating_hours_mode === 'custom' ? __('settings.custom_mode') : __('settings.follow_center') }}
                                        </span>
                                    </div>
                                </div>

                                <div class="pt-2 border-t border-black/5 dark:border-white/5">
                                    @if (\App\Support\Context::hasPermission('warehouse.manage') || \App\Support\Context::isAdminOrOwner())
                                        <a href="{{ route('warehouse.edit', $loc) }}"
                                            class="w-full h-11 flex items-center justify-center rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 active:scale-[0.98] transition">
                                            {{ __('settings.change_schedule') }}
                                        </a>
                                    @elseif (\App\Support\Context::hasPermission('warehouse.view'))
                                        <a href="{{ route('warehouse.show', $loc) }}"
                                            class="w-full h-11 flex items-center justify-center rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 active:scale-[0.98] transition">
                                            {{ __('settings.view_details') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="py-6 text-center text-[13px] text-black/40 dark:text-white/40 italic">
                                {{ __('settings.no_branches_yet') }}
                            </div>
                        @endforelse
                    </div>
                </div>

                @if (\App\Support\Context::hasPermission('settings.edit'))
                    <div class="flex items-center justify-end pt-2">
                        <button type="submit"
                            class="w-full sm:w-auto h-11 px-6 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-2 shadow-[0_1px_2px_rgba(0,122,255,0.25)] min-h-[44px]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            <span>{{ __('settings.save_operations') }}</span>
                        </button>
                    </div>
                @endif
            </form>
        </div>

        <!-- ===================================================== -->
        <!-- TAB: CMS TEMPLATE WHATSAPP STRUK                      -->
        <!-- ===================================================== -->
        <div x-show="activeTab === 'wa_receipt'" class="space-y-6" style="display: none;">
            @if ($business->isModuleEnabled('pos_retail') || $business->isModuleEnabled('pos_dinein'))
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    <!-- Form & Controls (Left Column, 7 cols) -->
                    <div class="lg:col-span-7 space-y-6">

                        <!-- WABA Architecture & Delivery Banner (Bento Card) -->
                        <div class="rounded-[16px] bg-[#34C759]/[0.06] dark:bg-[#34C759]/[0.08] border border-[#34C759]/20 p-5 space-y-4">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-[8px] bg-[#34C759] text-white flex items-center justify-center shrink-0 shadow-sm">
                                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-[14px] font-semibold text-black dark:text-white flex items-center gap-2">
                                            <span>{{ __('settings.waba_channel_architecture_title') }}</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                                {{ __('settings.waba_engine_badge') }}
                                            </span>
                                        </h3>
                                        <p class="text-[12px] text-black/60 dark:text-white/60 mt-0.5">
                                            {{ __('settings.waba_channel_architecture_desc') }}
                                        </p>
                                    </div>
                                </div>

                                @if (isset($whatsappAccount) && $whatsappAccount && $whatsappAccount->isActive())
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11.5px] font-medium bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] shrink-0">
                                        <span class="w-2 h-2 rounded-full bg-[#34C759] animate-pulse"></span>
                                        <span>{{ __('settings.waba_active_status') }} ({{ $whatsappAccount->display_phone_number ?? $whatsappAccount->phone_number }})</span>
                                    </div>
                                @else
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11.5px] font-medium bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70 shrink-0">
                                        <span class="w-2 h-2 rounded-full bg-[#007AFF]"></span>
                                        <span>{{ __('settings.waba_platform_status') }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1 text-[12px]">
                                <div class="p-3 rounded-[12px] bg-white/70 dark:bg-black/20 border border-black/5 dark:border-white/5 space-y-1">
                                    <div class="font-semibold text-black/90 dark:text-white/90 flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                        <span>{{ __('settings.waba_mode_auto_title') }}</span>
                                    </div>
                                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                                        {{ __('settings.waba_mode_auto_desc', ['template' => 'cooca_pos_receipt']) }}
                                    </p>
                                </div>
                                <div class="p-3 rounded-[12px] bg-white/70 dark:bg-black/20 border border-black/5 dark:border-white/5 space-y-1">
                                    <div class="font-semibold text-black/90 dark:text-white/90 flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span>
                                        <span>{{ __('settings.waba_mode_manual_title') }}</span>
                                    </div>
                                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                                        {{ __('settings.waba_mode_manual_desc') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Form Card -->
                        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 shadow-sm">
                            <div class="border-b border-black/5 dark:border-white/5 pb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-[10px] bg-[#34C759]/12 text-[#34C759] flex items-center justify-center shrink-0">
                                        <i data-lucide="message-square" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-[17px] font-semibold text-black dark:text-white">{{ __('settings.wa_receipt_title') }}</h2>
                                        <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">{{ __('settings.wa_receipt_desc') }}</p>
                                    </div>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="_tab" value="wa_receipt">

                                <!-- BAGIAN 1: Catatan Kaki Struk Digital (pos_receipt_footer_note) -->
                                <div class="p-4 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2.5">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5">
                                        <div>
                                            <label for="pos_receipt_footer_note" class="block text-[13px] font-semibold text-black dark:text-white">
                                                {{ __('settings.receipt_footer_section_title') }}
                                            </label>
                                            <p class="text-[11.5px] text-black/50 dark:text-white/50">
                                                {{ __('settings.receipt_footer_section_desc') }}
                                            </p>
                                        </div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10.5px] font-medium bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] shrink-0 self-start sm:self-auto">
                                            {{ __('settings.receipt_footer_note_badge') }}
                                        </span>
                                    </div>
                                    <input type="text" id="pos_receipt_footer_note" name="pos_receipt_footer_note"
                                        x-model="receiptFooterNote"
                                        value="{{ old('pos_receipt_footer_note', $business->pos_receipt_footer_note) }}"
                                        placeholder="{{ __('settings.receipt_footer_note_hint') }}"
                                        maxlength="500"
                                        class="w-full h-11 px-3.5 rounded-[10px] bg-white dark:bg-black/20 border border-black/10 dark:border-white/10 text-[15px] sm:text-[14px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#34C759]/50 transition">
                                </div>

                                <!-- BAGIAN 2: Format Teks Struk Manual (pos_receipt_wa_template) -->
                                <div class="space-y-3 pt-2">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5">
                                        <div>
                                            <label for="pos_receipt_wa_template" class="block text-[13px] font-semibold text-black dark:text-white">
                                                {{ __('settings.wa_manual_section_title') }}
                                            </label>
                                            <p class="text-[11.5px] text-black/50 dark:text-white/50">
                                                {{ __('settings.wa_manual_section_desc') }}
                                            </p>
                                        </div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10.5px] font-medium bg-[#007AFF]/10 text-[#007AFF] shrink-0 self-start sm:self-auto">
                                            {{ __('settings.wa_manual_template_badge') }}
                                        </span>
                                    </div>

                                    <!-- Dynamic Variable Chips -->
                                    <div class="space-y-1.5">
                                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70">
                                            {{ __('settings.dynamic_variables') }} <span class="text-[11px] font-normal text-black/40 dark:text-white/40">{{ __('settings.dynamic_variables_hint') }}</span>
                                        </label>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach (['{business_name}', '{customer_name}', '{order_number}', '{date}', '{cashier_name}', '{receipt_link}', '{footer_note}'] as $tag)
                                                <button type="button" @click="insertTag('{{ $tag }}')"
                                                    class="px-2.5 py-1 rounded-full text-[11.5px] font-medium bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/20 active:scale-[0.97] transition flex items-center gap-1 cursor-pointer min-h-[32px]">
                                                    <span>+</span>
                                                    <span class="font-mono">{{ $tag }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Template Textarea -->
                                    <div class="space-y-1.5">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[12px] font-medium text-black/70 dark:text-white/70">
                                                {{ __('settings.wa_template_format') }}
                                            </span>
                                            <span class="text-[11px] text-black/40 dark:text-white/40 font-mono tabular-nums"
                                                x-text="(waTemplateText ? waTemplateText.length : 0) + ' / 1000 karakter'"></span>
                                        </div>
                                        <textarea id="pos_receipt_wa_template" name="pos_receipt_wa_template" x-ref="waTextarea" x-model="waTemplateText"
                                            rows="7" maxlength="1000"
                                            class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] p-3.5 text-[15px] sm:text-[14px] font-mono leading-relaxed text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition resize-y"
                                            placeholder="Tulis format teks WhatsApp manual di sini..."></textarea>
                                        <p class="text-[11px] text-black/45 dark:text-white/45">
                                            {{ __('settings.wa_template_notice') }}
                                        </p>
                                    </div>

                                    <!-- Markdown Cheatsheet Guide -->
                                    <div class="rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3 text-[12px] space-y-1.5">
                                        <div class="font-medium text-black/70 dark:text-white/70 flex items-center gap-1.5">
                                            <i data-lucide="info" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                            <span>{{ __('settings.wa_markdown_guide') }}</span>
                                        </div>
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-black/60 dark:text-white/60 text-[11px]">
                                            <div><code>*teks*</code> &rarr; <strong>Tebal</strong></div>
                                            <div><code>_teks_</code> &rarr; <em>Miring</em></div>
                                            <div><code>~teks~</code> &rarr; <del>Coret</del></div>
                                            <div><code>```teks```</code> &rarr; Monospace</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                @if (\App\Support\Context::hasPermission('settings.edit'))
                                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-3 border-t border-black/5 dark:border-white/5">
                                        <button type="button" @click="resetToDefaultTemplate()"
                                            class="h-11 sm:h-10 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] transition flex items-center justify-center min-h-[44px]">
                                            {{ __('settings.reset_default_template') }}
                                        </button>
                                        <button type="submit"
                                            class="h-11 sm:h-10 px-6 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition flex items-center justify-center gap-2 shadow-[0_1px_2px_rgba(0,122,255,0.25)] min-h-[44px]">
                                            <i data-lucide="check" class="w-4 h-4"></i>
                                            <span>{{ __('settings.save_wa_template') }}</span>
                                        </button>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>

                    <!-- WhatsApp Live Phone Preview (Right Column, 5 cols) -->
                    <div class="lg:col-span-5">
                        <div class="lg:sticky lg:top-6 space-y-3">
                            <div class="flex items-center justify-between px-1">
                                <span class="text-[12px] font-semibold tracking-wider uppercase text-black/40 dark:text-white/40">{{ __('settings.live_preview') }}</span>
                                <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-[#34C759]">
                                    <span class="w-2 h-2 rounded-full bg-[#34C759] animate-pulse"></span>
                                    {{ __('settings.auto_sync') }}
                                </span>
                            </div>

                            <!-- Preview Mode Segmented Switcher -->
                            <div class="p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] flex gap-1 text-[12px] font-medium">
                                <button type="button" @click="previewMode = 'waba'"
                                    :class="previewMode === 'waba' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                                    class="flex-1 py-1.5 px-2 rounded-[9px] transition flex items-center justify-center gap-1.5 min-h-[36px]">
                                    <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                                    <span class="truncate">{{ __('settings.preview_mode_waba') }}</span>
                                </button>
                                <button type="button" @click="previewMode = 'manual'"
                                    :class="previewMode === 'manual' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                                    class="flex-1 py-1.5 px-2 rounded-[9px] transition flex items-center justify-center gap-1.5 min-h-[36px]">
                                    <i data-lucide="share-2" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                    <span class="truncate">{{ __('settings.preview_mode_manual') }}</span>
                                </button>
                            </div>

                            <!-- Apple iOS Hardware Phone Frame -->
                            <div class="w-full max-w-[340px] mx-auto rounded-[38px] p-2.5 bg-neutral-900 shadow-[0_20px_60px_rgba(0,0,0,0.35)] border border-neutral-800">
                                <div class="rounded-[28px] overflow-hidden bg-[#EFEAE2] dark:bg-[#0B141A] flex flex-col min-h-[520px] border border-black/10 dark:border-white/10 select-none relative">

                                    <!-- Dynamic Island Header -->
                                    <div class="bg-[#075E54] dark:bg-[#1F2C34] pt-2 pb-1 flex justify-center">
                                        <div class="w-20 h-4 rounded-full bg-black flex items-center justify-end px-2">
                                            <div class="w-1.5 h-1.5 rounded-full bg-neutral-700"></div>
                                        </div>
                                    </div>

                                    <!-- WhatsApp Top Bar -->
                                    <div class="bg-[#075E54] dark:bg-[#1F2C34] text-white px-3.5 py-2.5 flex items-center gap-2.5 shadow-sm">
                                        <div class="w-8 h-8 rounded-full bg-white/20 text-white font-bold text-[13px] flex items-center justify-center">
                                            {{ strtoupper(substr($business->name ?? 'T', 0, 1)) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-[13px] font-semibold truncate leading-tight text-white flex items-center gap-1">
                                                <span>{{ $business->name ?? 'Toko Saya' }}</span>
                                                <template x-if="previewMode === 'waba'">
                                                    <span class="text-[#34C759] text-[12px]" title="Official Business Account">✓</span>
                                                </template>
                                            </h4>
                                            <p class="text-[10px] text-white/70 leading-tight">
                                                <span x-show="previewMode === 'waba'">{{ __('settings.waba_official_tag') }}</span>
                                                <span x-show="previewMode === 'manual'">online</span>
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-3 text-white/80">
                                            <i data-lucide="phone" class="w-4 h-4"></i>
                                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                                        </div>
                                    </div>

                                    <!-- WhatsApp Chat Body Area -->
                                    <div class="flex-1 p-3 flex flex-col justify-end space-y-2 overflow-y-auto">

                                        <!-- PREVIEW MODE 1: WABA BOT (META OFFICIAL) -->
                                        <div x-show="previewMode === 'waba'"
                                            class="bg-[#D9FDD3] dark:bg-[#005C4B] text-[#111B21] dark:text-[#E9EDEF] rounded-[12px] rounded-tr-none p-2.5 shadow-sm max-w-[96%] ml-auto text-[12px] leading-relaxed space-y-2">

                                            <!-- WABA Header Badge & Status -->
                                            <div class="flex items-center justify-between text-[10px] pb-1 border-b border-black/10 dark:border-white/10 text-black/60 dark:text-white/60">
                                                <span class="font-semibold text-[#1F7A37] dark:text-[#53bdeb] flex items-center gap-1">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                                    <span>{{ __('settings.waba_template_badge') }}</span>
                                                </span>
                                                <span class="font-mono uppercase text-[9px] bg-black/5 dark:bg-white/10 px-1.5 py-0.2 rounded text-black/70 dark:text-white/70">cooca_pos_receipt</span>
                                            </div>

                                            <!-- Official Meta Template Text Header Component -->
                                            <div class="pt-0.5 pb-1 border-b border-black/10 dark:border-white/10 flex items-center justify-between">
                                                <span class="font-bold text-[13px] text-black dark:text-white tracking-tight uppercase">
                                                    {{ __('settings.waba_header_text') }}
                                                </span>
                                                <span class="text-[9px] font-medium text-black/40 dark:text-white/40 font-mono">
                                                    HEADER: TEXT
                                                </span>
                                            </div>

                                            <!-- Official Meta Template Text Body -->
                                            <div class="space-y-1.5 text-[12px] leading-relaxed pt-0.5">
                                                <p>Halo <strong class="font-bold text-black dark:text-white">Agung Mustaqim</strong>! Terima kasih telah berbelanja di <strong class="font-bold text-black dark:text-white">{{ $business->name ?? 'Toko Kami' }}</strong>.</p>
                                                <div class="py-1.5 px-2 font-mono text-[11px] text-black/80 dark:text-white/80 bg-black/[0.03] dark:bg-white/[0.04] p-1.5 rounded space-y-0.5 border border-black/5 dark:border-white/5">
                                                    <div class="text-[9.5px] text-black/50 dark:text-white/50 font-sans font-semibold uppercase tracking-wider mb-0.5">Rincian Transaksi:</div>
                                                    <div>• No. Struk: <strong class="font-bold">POS-20260910-0042</strong></div>
                                                    <div>• Waktu: <strong class="font-bold">{{ now()->format('d/m/Y H:i') }}</strong></div>
                                                    <div>• Total Bayar: <strong class="font-bold">Rp 85.000</strong></div>
                                                </div>
                                                <p class="text-[10.5px] text-black/65 dark:text-white/65">Struk digital transaksi Anda tersimpan aman di sistem kami.</p>
                                            </div>

                                            <!-- Meta Template Footer -->
                                            <div class="text-[10px] text-black/45 dark:text-white/45 italic pt-0.5">
                                                {{ __('settings.waba_footer_official') }}
                                            </div>

                                            <!-- Meta Interactive Button (URL Destination Link) -->
                                            <div class="pt-1.5 border-t border-black/10 dark:border-white/10">
                                                <div class="w-full py-2 rounded-[8px] bg-white/90 dark:bg-white/10 text-[#007AFF] dark:text-[#53bdeb] font-semibold text-[11.5px] flex items-center justify-center gap-1.5 shadow-xs">
                                                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                                    <span>{{ __('settings.waba_open_digital_receipt') }}</span>
                                                </div>
                                                <div class="text-[9px] text-center text-black/40 dark:text-white/40 pt-1 font-mono truncate">
                                                    ↗ https://cooca.id/r/POS-20260910-0042
                                                </div>
                                            </div>

                                            <!-- Time & Read Status -->
                                            <div class="flex items-center justify-end gap-1 text-[10px] text-black/45 dark:text-white/50 select-none pt-0.5">
                                                <span class="tabular-nums">{{ now()->format('H:i') }}</span>
                                                <span class="text-[#53bdeb] font-bold text-[11px]">✓✓</span>
                                            </div>
                                        </div>

                                        <!-- PREVIEW MODE 2: MANUAL CHAT (WA.ME) -->
                                        <div x-show="previewMode === 'manual'" style="display: none;"
                                            class="bg-[#D9FDD3] dark:bg-[#005C4B] text-[#111B21] dark:text-[#E9EDEF] rounded-[12px] rounded-tr-none p-2.5 shadow-sm max-w-[96%] ml-auto text-[12.5px] leading-relaxed space-y-2">

                                            <!-- Rendered WhatsApp Text Caption -->
                                            <div class="whitespace-pre-wrap select-text text-[12px] leading-relaxed break-words pt-1"
                                                x-html="formatWaMarkdown(waRenderedPreview)"></div>

                                            <!-- Time & Read Status -->
                                            <div class="flex items-center justify-end gap-1 text-[10px] text-black/45 dark:text-white/50 select-none">
                                                <span class="tabular-nums">{{ now()->format('H:i') }}</span>
                                                <span class="text-[#53bdeb] font-bold text-[11px]">✓✓</span>
                                            </div>
                                        </div>

                                    </div>

                                    <!-- Fake Message Input Bar -->
                                    <div class="bg-[#F0F2F5] dark:bg-[#1F2C34] p-2 flex items-center gap-2 border-t border-black/5 dark:border-white/5">
                                        <div class="w-6 h-6 rounded-full bg-black/10 dark:bg-white/10 flex items-center justify-center text-black/50 dark:text-white/50 text-[11px] font-bold">+</div>
                                        <div class="flex-1 h-7 rounded-full bg-white dark:bg-[#2A3942] px-3 flex items-center text-[11px] text-black/40 dark:text-white/40">
                                            Ketik pesan
                                        </div>
                                        <div class="w-6 h-6 rounded-full bg-[#00A884] text-white flex items-center justify-center text-[11px]">▶</div>
                                    </div>

                                </div>
                            </div>

                            <!-- Destination Web Page Card: Where receipt image & footer note actually live -->
                            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-4 sm:p-5 space-y-3.5 shadow-sm">
                                <div class="flex items-start justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
                                    <div class="space-y-0.5">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-[#007AFF]"></span>
                                            <h4 class="text-[13px] font-semibold text-black dark:text-white">
                                                {{ __('settings.waba_receipt_paper_title') }}
                                            </h4>
                                        </div>
                                        <p class="text-[11.5px] text-black/55 dark:text-white/55 leading-relaxed">
                                            {{ __('settings.waba_receipt_paper_desc') }}
                                        </p>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-[#007AFF]/10 text-[#007AFF] shrink-0">
                                        {{ __('settings.waba_link_destination_badge') }}
                                    </span>
                                </div>

                                <!-- Browser URL Bar Mockup -->
                                <div class="flex items-center gap-2 px-3 py-1.5 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/5 text-[11px] font-mono text-black/60 dark:text-white/60">
                                    <i data-lucide="lock" class="w-3 h-3 text-[#34C759] shrink-0"></i>
                                    <span class="truncate">https://cooca.id/r/POS-20260910-0042</span>
                                </div>

                                <!-- Rendered Thermal Receipt Paper Simulation -->
                                <div class="rounded-[12px] bg-[#FAF8F5] dark:bg-[#141416] border border-black/10 dark:border-white/10 p-4 font-mono text-[11.5px] text-black/85 dark:text-white/85 space-y-2.5 shadow-inner">
                                    <!-- Store Header -->
                                    <div class="text-center space-y-0.5">
                                        <div class="text-[13px] font-bold tracking-tight text-black dark:text-white uppercase font-sans">
                                            {{ $business->name ?? 'Toko Saya' }}
                                        </div>
                                        <div class="text-[10px] text-black/50 dark:text-white/50">
                                            {{ $business->address ?? 'Pusat Operasional Bisnis' }}
                                        </div>
                                        <div class="text-[9.5px] text-black/40 dark:text-white/40 pt-1">
                                            POS-20260910-0042 • {{ now()->format('d/m/Y H:i') }} • Kasir 1
                                        </div>
                                    </div>

                                    <!-- Divider -->
                                    <div class="border-t border-dashed border-black/20 dark:border-white/20"></div>

                                    <!-- Item Table Breakdown -->
                                    <div class="space-y-1.5 text-[11px]">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <div>2x Kopi Kenangan Manis</div>
                                                <div class="text-[9.5px] text-black/45 dark:text-white/45">@ Rp 18.000</div>
                                            </div>
                                            <span class="tabular-nums">Rp 36.000</span>
                                        </div>
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <div>1x Croissant Butter Bakar</div>
                                                <div class="text-[9.5px] text-black/45 dark:text-white/45">@ Rp 28.000</div>
                                            </div>
                                            <span class="tabular-nums">Rp 28.000</span>
                                        </div>
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <div>1x Teh Botol Sosro Dingin</div>
                                                <div class="text-[9.5px] text-black/45 dark:text-white/45">@ Rp 7.000</div>
                                            </div>
                                            <span class="tabular-nums">Rp 7.000</span>
                                        </div>
                                    </div>

                                    <!-- Divider -->
                                    <div class="border-t border-dashed border-black/20 dark:border-white/20"></div>

                                    <!-- Subtotal & Calculations -->
                                    <div class="space-y-1 text-[11px]">
                                        <div class="flex justify-between text-black/60 dark:text-white/60">
                                            <span>Subtotal</span>
                                            <span class="tabular-nums">Rp 71.000</span>
                                        </div>
                                        <div class="flex justify-between text-black/60 dark:text-white/60">
                                            <span>PB1 / Resto Tax (10%)</span>
                                            <span class="tabular-nums">Rp 7.100</span>
                                        </div>
                                        <div class="flex justify-between text-black/60 dark:text-white/60">
                                            <span>Pembulatan</span>
                                            <span class="tabular-nums">Rp 900</span>
                                        </div>
                                        <div class="flex justify-between font-bold text-[12.5px] text-black dark:text-white pt-1 border-t border-black/10 dark:border-white/10">
                                            <span>TOTAL BAYAR</span>
                                            <span class="tabular-nums text-[#248A3D] dark:text-[#30D158]">Rp 85.000</span>
                                        </div>
                                        <div class="text-[10px] text-right text-black/50 dark:text-white/50">
                                            LUNAS • QRIS Dinamis
                                        </div>
                                    </div>

                                    <!-- Divider -->
                                    <div class="border-t border-dashed border-black/20 dark:border-white/20"></div>

                                    <!-- Verification QR & Barcode Simulation -->
                                    <div class="py-1 text-center space-y-1">
                                        <div class="inline-flex items-center justify-center p-2 rounded bg-white dark:bg-black/40 border border-black/10 dark:border-white/10 shadow-xs">
                                            <div class="text-[9px] font-mono tracking-widest text-black/70 dark:text-white/70">
                                                [ QR VERIFIKASI COOCA ]
                                            </div>
                                        </div>
                                        <div class="text-[9px] text-black/40 dark:text-white/40 uppercase tracking-wider">
                                            Bukti Transaksi Sah &amp; Terverifikasi
                                        </div>
                                    </div>

                                    <!-- LIVE REACTIVE STAMPED FOOTER NOTE -->
                                    <div class="p-2.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.05] border border-dashed border-black/15 dark:border-white/15 text-center space-y-1">
                                        <div class="text-[9.5px] uppercase font-bold tracking-wider text-[#248A3D] dark:text-[#30D158] flex items-center justify-center gap-1 font-sans">
                                            <i data-lucide="feather" class="w-3 h-3"></i>
                                            <span>{{ __('settings.receipt_footer_note') }}</span>
                                        </div>
                                        <div class="text-[11.5px] font-mono italic text-black/85 dark:text-white/85 leading-relaxed break-words"
                                            x-text="receiptFooterNote ? receiptFooterNote : '{{ __('settings.default_footer_note') }}'">
                                        </div>
                                    </div>

                                    <!-- Action Buttons Simulation -->
                                    <div class="pt-1 flex gap-2 font-sans">
                                        <div class="flex-1 py-1.5 px-2 rounded-[8px] bg-black/5 dark:bg-white/10 text-center text-[10.5px] font-semibold text-black/75 dark:text-white/75 flex items-center justify-center gap-1">
                                            <i data-lucide="printer" class="w-3 h-3"></i>
                                            <span>{{ __('settings.print_receipt_btn') }}</span>
                                        </div>
                                        <div class="flex-1 py-1.5 px-2 rounded-[8px] bg-[#007AFF]/10 text-center text-[10.5px] font-semibold text-[#007AFF] flex items-center justify-center gap-1">
                                            <i data-lucide="download" class="w-3 h-3"></i>
                                            <span>{{ __('settings.download_receipt_image_btn') }}</span>
                                        </div>
                                    </div>

                                    <div class="text-center text-[8.5px] text-black/35 dark:text-white/35 font-sans pt-1">
                                        Powered by COOCA POS • cooca.id
                                    </div>
                                </div>
                            </div>

                            <!-- Informational Callout -->
                            <div class="rounded-[12px] bg-[#34C759]/8 border border-[#34C759]/20 p-3.5 text-[12px] text-black/60 dark:text-white/60 space-y-1">
                                <p class="font-medium text-[#248A3D] dark:text-[#30D158] flex items-center gap-1.5">
                                    <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759]"></i>
                                    <span>{{ __('settings.delivery_info') }}</span>
                                </p>
                                <p>{{ __('settings.delivery_info_desc') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Auto-Hiding Placeholder for Non-POS Businesses -->
                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-8 text-center space-y-4 shadow-sm max-w-lg mx-auto my-8">
                    <div class="w-12 h-12 rounded-[14px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center mx-auto">
                        <i data-lucide="shield-alert" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-[17px] font-semibold text-black dark:text-white">{{ __('settings.pos_module_disabled_title') }}</h3>
                        <p class="text-[13px] text-black/55 dark:text-white/55 mt-1 leading-relaxed">
                            {{ __('settings.pos_module_disabled_desc') }}
                        </p>
                    </div>
                    @if (\App\Support\Context::isOwner())
                        <div class="pt-2">
                            <button type="button" @click="activeTab = 'modules'"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] transition active:scale-[0.98] min-h-[44px]">
                                <i data-lucide="layers" class="w-4 h-4"></i>
                                <span>{{ __('settings.open_modules_tab') }}</span>
                            </button>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- ===================================================== -->
        <!-- TAB 4: 20 PRESET TEMPLATE INDUSTRI (§35)              -->
        <!-- ===================================================== -->
        <div x-show="activeTab === 'templates'" class="space-y-6" style="display: none;">
            <!-- Search & Category Filters -->
            <div
                class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 space-y-4 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-[17px] font-semibold text-black dark:text-white flex items-center gap-2">
                            <span>{{ __('settings.templates_title') }}</span>
                        </h2>
                        <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">{{ __('settings.templates_desc') }}</p>
                    </div>

                    <!-- macOS Style Capsule Search -->
                    <div class="relative w-full md:w-72">
                        <i data-lucide="search" class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                        <input type="text" x-model="templateSearch" placeholder="{{ __('settings.search_template') }}"
                            class="w-full h-10 sm:h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-9 pr-3 text-[15px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>

                <!-- Segmented Category Filters -->
                <div class="flex items-center gap-1.5 flex-wrap pt-2 border-t border-black/5 dark:border-white/5">
                    <button type="button" @click="selectedCategory = 'all'"
                        :class="selectedCategory === 'all' ?
                            'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' :
                            'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'"
                        class="h-9 sm:h-8 px-3.5 rounded-[8px] text-[12px] font-medium active:scale-[0.97] transition-all min-h-[36px]">
                        {{ __('settings.all_categories') }}
                    </button>
                    <button type="button" @click="selectedCategory = 'fnb'"
                        :class="selectedCategory === 'fnb' ?
                            'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' :
                            'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'"
                        class="h-9 sm:h-8 px-3.5 rounded-[8px] text-[12px] font-medium active:scale-[0.97] transition-all min-h-[36px]">
                        {{ __('settings.fnb') }}
                    </button>
                    <button type="button" @click="selectedCategory = 'retail'"
                        :class="selectedCategory === 'retail' ?
                            'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' :
                            'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'"
                        class="h-9 sm:h-8 px-3.5 rounded-[8px] text-[12px] font-medium active:scale-[0.97] transition-all min-h-[36px]">
                        {{ __('settings.retail') }}
                    </button>
                    <button type="button" @click="selectedCategory = 'manufacturing'"
                        :class="selectedCategory === 'manufacturing' ?
                            'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' :
                            'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'"
                        class="h-9 sm:h-8 px-3.5 rounded-[8px] text-[12px] font-medium active:scale-[0.97] transition-all min-h-[36px]">
                        {{ __('settings.manufacturing') }}
                    </button>
                    <button type="button" @click="selectedCategory = 'service'"
                        :class="selectedCategory === 'service' ?
                            'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' :
                            'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'"
                        class="h-9 sm:h-8 px-3.5 rounded-[8px] text-[12px] font-medium active:scale-[0.97] transition-all min-h-[36px]">
                        {{ __('settings.service') }}
                    </button>
                    <button type="button" @click="selectedCategory = 'agriculture'"
                        :class="selectedCategory === 'agriculture' ?
                            'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' :
                            'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'"
                        class="h-9 sm:h-8 px-3.5 rounded-[8px] text-[12px] font-medium active:scale-[0.97] transition-all min-h-[36px]">
                        {{ __('settings.agriculture') }}
                    </button>
                </div>
            </div>

            <!-- Templates Grid (Dense & Structured) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($templates as $tmpl)
                    <div x-show="(selectedCategory === 'all' || '{{ strtolower($tmpl->industry_category) }}'.includes(selectedCategory)) && (!templateSearch || '{{ strtolower($tmpl->name . ' ' . $tmpl->description . ' ' . $tmpl->industry_category) }}'.includes(templateSearch.toLowerCase()))"
                        class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 flex flex-col justify-between space-y-4 hover:border-black/15 dark:hover:border-white/15 transition-all duration-200 shadow-sm">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between gap-2">
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                    {{ $tmpl->industry_category }}
                                </span>
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-medium bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 tabular-nums">
                                    {{ $tmpl->recommended_costing_method }}
                                </span>
                            </div>

                            <h3 class="text-[16px] font-semibold text-black dark:text-white">
                                {{ $tmpl->name }}
                            </h3>
                            <p class="text-[13px] text-black/55 dark:text-white/55 line-clamp-2 leading-relaxed">
                                {{ $tmpl->description }}
                            </p>
                        </div>

                        <div
                            class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between gap-2">
                            <span class="text-[12px] text-black/45 dark:text-white/45 tabular-nums">
                                {{ count($tmpl->default_cost_components ?? []) }} {{ __('settings.cost_components') }}
                            </span>

                            @if (\App\Support\Context::hasPermission('settings.edit'))
                                <button type="button"
                                    @click="openApplyTemplate('{{ $tmpl->code }}', '{{ addslashes($tmpl->name) }}', '{{ addslashes($tmpl->industry_category) }}')"
                                    class="h-9 px-3.5 rounded-[8px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] transition-all flex items-center gap-1.5 min-h-[36px]">
                                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                    <span>{{ __('settings.apply') }}</span>
                                </button>

                                <form id="form-apply-template-{{ $tmpl->code }}" method="POST"
                                    action="{{ route('settings.apply-template') }}" class="hidden">
                                    @csrf
                                    <input type="hidden" name="template_code" value="{{ $tmpl->code }}">
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- TAB 5: KELOLA MODUL & PENATAAN MENU BISNIS            -->
        <!-- ===================================================== -->
        <div x-show="activeTab === 'modules'" class="space-y-6" style="display: none;">
            <form method="POST" action="{{ route('settings.modules.update') }}" class="space-y-6" @submit.prevent>
                @csrf
                @method('PUT')

                <!-- Header Card -->
                <div
                    class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4 shadow-sm">
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-black/5 dark:border-white/5 pb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-[17px] font-semibold text-black dark:text-white">{{ __('settings.manage_modules_title') }}</h2>
                                @if ($business->template_code)
                                    <span
                                        class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20">
                                        Preset: {{ strtoupper($business->template_code) }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-[13px] text-black/50 dark:text-white/50 mt-1">
                                {{ __('settings.manage_modules_desc') }}
                            </p>
                        </div>

                        @if (\App\Support\Context::isOwner())
                            <div class="flex items-center gap-2 shrink-0">
                                <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[10px] text-[12px] font-medium transition-all"
                                    :class="{
                                        'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20': moduleSaveStatus === 'saved' || moduleSaveStatus === 'idle',
                                        'bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20': moduleSaveStatus === 'saving',
                                        'bg-[#FF3B30]/10 text-[#FF3B30] border border-[#FF3B30]/20': moduleSaveStatus === 'error'
                                    }">
                                    <template x-if="moduleSaveStatus === 'saving'">
                                        <svg class="animate-spin w-3.5 h-3.5 text-[#007AFF]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </template>
                                    <template x-if="moduleSaveStatus === 'saved' || moduleSaveStatus === 'idle'">
                                        <svg class="w-3.5 h-3.5 text-[#34C759]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </template>
                                    <template x-if="moduleSaveStatus === 'error'">
                                        <svg class="w-3.5 h-3.5 text-[#FF3B30]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10" stroke-width="2"/><line x1="12" y1="8" x2="12" y2="12" stroke-width="2"/><line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2"/>
                                        </svg>
                                    </template>
                                    <span x-text="moduleSaveStatus === 'saving' ? '{{ __('settings.module_autosave_saving') }}' : (moduleSaveStatus === 'error' ? '{{ __('settings.module_autosave_error') }}' : '{{ __('settings.module_autosave_saved') }}')">
                                        {{ __('settings.module_autosave_saved') }}
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Callout Info -->
                    <div
                        class="rounded-[12px] bg-[#007AFF]/8 border border-[#007AFF]/15 p-3.5 flex items-start gap-3 text-[12px] text-black/70 dark:text-white/70">
                        <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"></i>
                        <div class="space-y-0.5">
                            <p class="font-medium text-[#007AFF]">{{ __('settings.owner_control_title') }}</p>
                            <p>{{ __('settings.owner_control_desc') }}</p>
                        </div>
                    </div>
                </div>

                @php
                    $clusterMap = [
                        'kasir' => [
                            'title' => __('settings.cluster_pos_title'),
                            'desc' => __('settings.cluster_pos_desc'),
                            'icon' => 'calculator',
                            'color' => 'text-[#007AFF]',
                            'bg' => 'bg-[#007AFF]/10',
                            'keys' => ['pos_retail', 'pos_dinein'],
                            'impacts' => [
                                'pos_retail' => 'Kasir & POS Resto: Buka Kasir POS, Transaksi Kasir & Shift, Laporan Kasir',
                                'pos_dinein' => 'Kasir & POS Resto: KDS Layar Dapur, Pengaturan Meja QR',
                            ],
                        ],
                        'b2b' => [
                            'title' => __('settings.cluster_b2b_title'),
                            'desc' => __('settings.cluster_b2b_desc'),
                            'icon' => 'file-text',
                            'color' => 'text-[#34C759]',
                            'bg' => 'bg-[#34C759]/10',
                            'keys' => ['b2b_sales'],
                            'impacts' => [
                                'b2b_sales' => 'Penjualan B2B & Faktur: Pesanan Penjualan (SO), Surat Penawaran, Faktur Tagihan (Invoice), Retur Penjualan',
                            ],
                        ],
                        'produksi_gudang' => [
                            'title' => __('settings.cluster_mfg_title'),
                            'desc' => __('settings.cluster_mfg_desc'),
                            'icon' => 'boxes',
                            'color' => 'text-[#AF52DE]',
                            'bg' => 'bg-[#AF52DE]/10',
                            'keys' => ['recipe_bom', 'labor_machines', 'inventory_warehouse', 'procurement'],
                            'impacts' => [
                                'recipe_bom' => 'Produk & Logistik: Bahan Baku & Resep (BOM), Kategori Bahan, Satuan Ukur',
                                'labor_machines' => 'Produksi HPP: Upah Kerja & Mesin, Alokasi Biaya HPP',
                                'inventory_warehouse' => 'Produk & Logistik: Saldo Stok Real-Time, Cabang & Gudang, Transfer Stok, Stock Opname, Mutasi Stok',
                                'procurement' => 'Pembelian & Pengadaan: Pesanan Pembelian (PO), Tagihan Supplier (Bills), Supplier, Retur Pembelian',
                            ],
                        ],
                        'crm_marketing' => [
                            'title' => __('settings.cluster_crm_title'),
                            'desc' => __('settings.cluster_crm_desc'),
                            'icon' => 'award',
                            'color' => 'text-[#FF9500]',
                            'bg' => 'bg-[#FF9500]/10',
                            'keys' => ['crm_loyalty', 'channels_marketing'],
                            'impacts' => [
                                'crm_loyalty' => 'Pelanggan & Loyalitas: Data Pelanggan, Poin Loyalitas Member, Kupon Voucher Diskon',
                                'channels_marketing' => 'Marketing & Pesan: WhatsApp Gateway Struk & Promo, Landing Page CMS',
                            ],
                        ],
                        'toko_online' => [
                            'title' => __('settings.cluster_storefront_title'),
                            'desc' => __('settings.cluster_storefront_desc'),
                            'icon' => 'shopping-bag',
                            'color' => 'text-[#5856D6]',
                            'bg' => 'bg-[#5856D6]/10',
                            'keys' => ['storefront_checkout', 'order_request', 'scheduled_order', 'customer_po', 'reservation', 'merchant_shipping'],
                            'impacts' => [
                                'storefront_checkout' => 'Toko Online: Pengaturan Toko Online, Pesanan Masuk Toko',
                                'order_request' => 'Toko Online: Pengajuan Request Order Khusus',
                                'scheduled_order' => 'Toko Online: Slot Tanggal Pemesanan & Lead Time',
                                'customer_po' => 'B2B Portal: PO Batch Klien & Multi-Drop',
                                'reservation' => 'Kasir & POS: Reservasi Meja & Booking Jadwal',
                                'merchant_shipping' => 'Logistik: Pengaturan Ongkos Kirim & Kurir Toko',
                            ],
                        ],
                        'keuangan_akuntansi' => [
                            'title' => __('settings.cluster_accounting_title'),
                            'desc' => __('settings.cluster_accounting_desc'),
                            'icon' => 'book-open',
                            'color' => 'text-[#007AFF]',
                            'bg' => 'bg-[#007AFF]/10',
                            'keys' => ['accounting_corporate'],
                            'impacts' => [
                                'accounting_corporate' => 'Akuntansi Korporasi: Bagan Akun (COA), Buku Jurnal Keuangan, Buku Besar Akun, Neraca Keuangan SAK EMKM, Neraca Saldo, Rekonsiliasi Bank',
                            ],
                        ],
                    ];
                @endphp

                <!-- 6 Thematic Bento Clusters -->
                @foreach ($clusterMap as $clusterId => $cluster)
                    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4 shadow-sm">
                        <!-- Cluster Header -->
                        <div class="flex items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-[10px] {{ $cluster['bg'] }} {{ $cluster['color'] }} flex items-center justify-center shrink-0">
                                    <i data-lucide="{{ $cluster['icon'] }}" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h3 class="text-[15px] font-semibold text-black dark:text-white">{{ $cluster['title'] }}</h3>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">{{ $cluster['desc'] }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Cards in Cluster -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($cluster['keys'] as $moduleKey)
                                @if (isset($allModules[$moduleKey]))
                                    @php
                                        $mod = $allModules[$moduleKey];
                                        $isEnabled = $business->isModuleEnabled($moduleKey);
                                        $impactText = $cluster['impacts'][$moduleKey] ?? null;
                                    @endphp
                                    <div
                                        class="rounded-[14px] p-4 sm:p-4.5 flex flex-col justify-between gap-3.5 transition-all hover:border-black/15 dark:hover:border-white/15"
                                        :class="modulesState['{{ $moduleKey }}'] ?
                                            'border border-[#34C759]/30 dark:border-[#34C759]/30 bg-[#34C759]/[0.02]' :
                                            'border border-black/5 dark:border-white/5 opacity-75 bg-black/[0.02] dark:bg-white/[0.02]'">
                                        <div class="space-y-2.5">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="flex items-center gap-2.5">
                                                    <div
                                                        class="w-9 h-9 rounded-[9px] flex items-center justify-center shrink-0 transition-colors"
                                                        :class="modulesState['{{ $moduleKey }}'] ?
                                                            'bg-[#007AFF]/10 text-[#007AFF]' :
                                                            'bg-black/5 dark:bg-white/5 text-black/40 dark:text-white/40'">
                                                        <i data-lucide="{{ $mod['icon'] }}" class="w-4.5 h-4.5"></i>
                                                    </div>
                                                    <div>
                                                        <h4 class="text-[14px] font-semibold text-black dark:text-white leading-snug">
                                                            {{ $mod['name'] }}
                                                        </h4>
                                                        <span class="text-[10.5px] text-black/40 dark:text-white/40 uppercase font-semibold">{{ $mod['category'] }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed min-h-[34px]">
                                                {{ $mod['description'] }}
                                            </p>

                                            <!-- Live Impact Badge -->
                                            @if ($impactText)
                                                <div class="rounded-[8px] bg-black/[0.03] dark:bg-white/[0.04] px-2.5 py-1.5 text-[11px] text-black/65 dark:text-white/65 flex items-start gap-1.5 border border-black/5 dark:border-white/5">
                                                    <i data-lucide="layout-panel-left" class="w-3.5 h-3.5 text-black/40 dark:text-white/40 shrink-0 mt-0.5"></i>
                                                    <div class="line-clamp-2 leading-tight">
                                                        <strong class="font-medium text-black dark:text-white">Sidebar:</strong> {{ $impactText }}
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Apple Switch Toggle -->
                                        <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                                            <span class="text-[11.5px] font-medium"
                                                :class="modulesState['{{ $moduleKey }}'] ? 'text-[#248A3D] dark:text-[#30D158]' : 'text-black/40 dark:text-white/40'"
                                                x-text="modulesState['{{ $moduleKey }}'] ? '{{ __('settings.module_active') }}' : '{{ __('settings.module_inactive') }}'">
                                                {{ $isEnabled ? __('settings.module_active') : __('settings.module_inactive') }}
                                            </span>

                                            <label class="relative inline-flex items-center cursor-pointer select-none min-h-[44px]">
                                                <input type="checkbox" name="enabled_modules[]" value="{{ $moduleKey }}"
                                                    x-model="modulesState['{{ $moduleKey }}']"
                                                    @change="toggleModule('{{ $moduleKey }}', $event.target.checked)"
                                                    :disabled="!isOwner || moduleSaving['{{ $moduleKey }}']"
                                                    {{ $isEnabled ? 'checked' : '' }}
                                                    {{ !\App\Support\Context::isOwner() ? 'disabled' : '' }} class="sr-only peer">
                                                <div
                                                    class="w-11 h-6 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759] transition-colors">
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <!-- Bottom Action Footer -->
                @if (\App\Support\Context::isOwner())
                    <div
                        class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-sm">
                        <div class="flex items-center gap-2 text-[12px] text-black/50 dark:text-white/50">
                            <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                            <span>{{ __('settings.module_autosave_notice') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[10px] text-[12px] font-medium transition-all"
                                :class="{
                                    'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20': moduleSaveStatus === 'saved' || moduleSaveStatus === 'idle',
                                    'bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20': moduleSaveStatus === 'saving',
                                    'bg-[#FF3B30]/10 text-[#FF3B30] border border-[#FF3B30]/20': moduleSaveStatus === 'error'
                                }">
                                <template x-if="moduleSaveStatus === 'saving'">
                                    <svg class="animate-spin w-3.5 h-3.5 text-[#007AFF]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>
                                <template x-if="moduleSaveStatus === 'saved' || moduleSaveStatus === 'idle'">
                                    <svg class="w-3.5 h-3.5 text-[#34C759]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </template>
                                <template x-if="moduleSaveStatus === 'error'">
                                    <svg class="w-3.5 h-3.5 text-[#FF3B30]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" stroke-width="2"/><line x1="12" y1="8" x2="12" y2="12" stroke-width="2"/><line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2"/>
                                    </svg>
                                </template>
                                <span x-text="moduleSaveStatus === 'saving' ? '{{ __('settings.module_autosave_saving') }}' : (moduleSaveStatus === 'error' ? '{{ __('settings.module_autosave_error') }}' : '{{ __('settings.module_autosave_saved') }}')">
                                    {{ __('settings.module_autosave_saved') }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif
            </form>
        </div>

        <!-- ===================================================== -->
        <!-- BENTO MODAL SHEET XXL (Terapkan Template Industri)    -->
        <!-- ===================================================== -->
        @if (\App\Support\Context::hasPermission('settings.edit'))
            <template x-teleport="body">
                <div x-show="templateModalOpen" x-cloak
                    class="fixed inset-0 z-[200] overflow-y-auto"
                    role="dialog"
                    aria-modal="true"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                    <!-- Frosted Glass Backdrop -->
                    <div class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-sm transition-opacity"
                         @click="closeApplyTemplate()"
                         aria-hidden="true"></div>

                    <!-- Centering Container -->
                    <div class="min-h-full flex items-center justify-center p-4 pointer-events-none">
                        <div class="relative z-10 pointer-events-auto w-full max-w-lg rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_24px_64px_rgba(0,0,0,0.3)] p-6 space-y-5 text-left"
                            @keydown.escape.window="closeApplyTemplate()"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95">

                            <div class="flex items-start gap-3.5">
                                <div class="w-11 h-11 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                                    <i data-lucide="layers" class="w-5 h-5"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-[17px] font-semibold text-black dark:text-white">{{ __('settings.apply_template_title') }}</h3>
                                    <p class="text-[13px] text-black/55 dark:text-white/55 mt-0.5 leading-snug">
                                        {{ __('settings.apply_template_desc') }}
                                    </p>
                                </div>
                            </div>

                            <!-- Template Details Preview Card -->
                            <div class="p-4 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 space-y-2">
                                <div class="flex items-center justify-between text-[12px]">
                                    <span class="text-black/50 dark:text-white/50">Template Terpilih:</span>
                                    <span class="font-semibold text-black dark:text-white" x-text="selectedTemplate.name"></span>
                                </div>
                                <div class="flex items-center justify-between text-[12px]">
                                    <span class="text-black/50 dark:text-white/50">Kategori Industri:</span>
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-[#34C759]/10 text-[#34C759]" x-text="selectedTemplate.category"></span>
                                </div>
                            </div>

                            <!-- No-Panic Microcopy Notice -->
                            <div class="flex items-start gap-2.5 text-[12px] text-black/60 dark:text-white/60">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0 mt-0.5"></i>
                                <p>Data transaksi, saldo stok, dan invoice Anda yang sudah ada tetap aman dan tidak akan terhapus atau tertimpa.</p>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center justify-end gap-3 pt-2 border-t border-black/5 dark:border-white/5">
                                <button type="button" @click="closeApplyTemplate()"
                                    class="h-11 px-5 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 active:scale-[0.97] transition min-h-[44px]">
                                    {{ __('settings.cancel') }}
                                </button>
                                <button type="button" @click="submitApplyTemplate()"
                                    class="h-11 px-6 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition flex items-center gap-2 shadow-[0_1px_2px_rgba(0,122,255,0.25)] min-h-[44px]">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    <span>{{ __('settings.confirm_apply') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        @endif

        {{-- ===================================================== --}}
        {{-- MODAL 1: TAMBAH CABANG BARU (APPLE BENTO MAP SHEET)   --}}
        {{-- ===================================================== --}}
        <template x-teleport="body">
            <div x-show="showCreateBranchModal"
                 x-cloak
                 class="fixed inset-0 z-[200] overflow-y-auto"
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="create-branch-modal-title"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">

                <!-- Frosted Dark Backdrop (Edge-to-Edge over topbar and layout) -->
                <div class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-sm transition-opacity"
                     @click="showCreateBranchModal = false"
                     aria-hidden="true"></div>

                <!-- Centering Wrapper -->
                <div class="min-h-full flex items-center justify-center p-3 sm:p-6 pointer-events-none">
                    <div class="relative z-10 pointer-events-auto w-full max-w-[95vw] lg:max-w-4xl xl:max-w-5xl max-h-[92vh] rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] shadow-[0_25px_60px_rgba(0,0,0,0.35)] flex flex-col overflow-hidden"
                         @keydown.escape.window="showCreateBranchModal = false">

                    {{-- Header --}}
                    <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between shrink-0 bg-slate-50/50 dark:bg-white/[0.02]">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-[14px] bg-[#34C759]/12 text-[#34C759] flex items-center justify-center shrink-0 border border-[#34C759]/20 shadow-sm">
                                <i data-lucide="store" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                            </div>
                            <div>
                                <p class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 mb-0.5">
                                    Cabang &amp; Outlet &bull; Pengaturan Usaha
                                </p>
                                <h3 class="text-base sm:text-lg font-bold text-black dark:text-white tracking-tight">{{ __('settings.create_branch_title') }}</h3>
                                <p class="text-xs text-black/55 dark:text-white/55 mt-0.5">Atur identitas outlet, titik koordinat peta GPS cabang, dan wilayah kurir pengiriman.</p>
                            </div>
                        </div>
                        <button type="button" @click="showCreateBranchModal = false"
                                class="min-h-[44px] min-w-[44px] rounded-[12px] text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition flex items-center justify-center cursor-pointer"
                                aria-label="Tutup">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    {{-- Scrollable Form --}}
                    <form action="{{ route('settings.branches.store') }}" method="POST" class="flex flex-col flex-1 overflow-hidden" @submit="branchSubmitting = true">
                        @csrf
                        <input type="hidden" name="type" value="outlet">

                        <div class="p-5 sm:p-6 overflow-y-auto space-y-6 flex-1">
                            @if ($errors->any())
                                <div class="p-4 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-xs">
                                    <div class="font-bold mb-1.5 flex items-center gap-2">
                                        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                                        <span>Ada data yang perlu diperbaiki:</span>
                                    </div>
                                    <ul class="list-disc list-inside space-y-1 opacity-90 pl-1">
                                        @foreach ($errors->all() as $err)
                                            <li>{{ $err }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- ========================================================== --}}
                            {{-- BAGIAN 1: IDENTITAS CABANG & KONTAK                        --}}
                            {{-- ========================================================== --}}
                            <div class="space-y-4">
                                <div class="flex items-center justify-between pb-1.5 border-b border-black/5 dark:border-white/5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-[#007AFF]/15 text-[#007AFF] text-xs font-bold flex items-center justify-center">1</span>
                                        <h3 class="text-xs sm:text-sm font-bold text-black dark:text-white uppercase tracking-wider">Identitas Cabang &amp; Kontak</h3>
                                    </div>
                                    <span class="text-[11px] font-medium text-black/45 dark:text-white/45">Data Operasional Toko</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div class="sm:col-span-1">
                                        <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1.5">
                                            {{ __('settings.branch_name') }} <span class="text-[#FF3B30]">*</span>
                                        </label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                                <i data-lucide="store" class="w-4 h-4"></i>
                                            </div>
                                            <input type="text" name="name" x-model="branchForm.name" required placeholder="Contoh: Cabang Senopati"
                                                   class="w-full pl-10 pr-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] sm:rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs sm:text-[13px] outline-none">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1.5">
                                            {{ __('settings.branch_code') }}
                                        </label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                                <i data-lucide="hash" class="w-4 h-4"></i>
                                            </div>
                                            <input type="text" name="code" x-model="branchForm.code" placeholder="Misal: SNP-01"
                                                   class="w-full pl-10 pr-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] sm:rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs sm:text-[13px] font-mono uppercase outline-none">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1.5">
                                            Nomor WhatsApp / HP Cabang
                                        </label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                                <i data-lucide="smartphone" class="w-4 h-4"></i>
                                            </div>
                                            <input type="tel" name="phone" x-model="branchForm.phone" placeholder="081234567890"
                                                   class="w-full pl-10 pr-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] sm:rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs sm:text-[13px] font-mono outline-none">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ========================================================== --}}
                            {{-- BAGIAN 2: TITIK LOKASI & WILAYAH INDONESIA (LEAFLET.JS)   --}}
                            {{-- ========================================================== --}}
                            <div class="space-y-4 pt-2">
                                <div class="flex items-center justify-between pb-1.5 border-b border-black/5 dark:border-white/5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-[#007AFF]/15 text-[#007AFF] text-xs font-bold flex items-center justify-center">2</span>
                                        <h3 class="text-xs sm:text-sm font-bold text-black dark:text-white uppercase tracking-wider">Titik Lokasi Cabang &amp; Wilayah Indonesia</h3>
                                    </div>
                                    <span class="text-[11px] font-medium text-black/45 dark:text-white/45">Tingkat Desa / Kelurahan</span>
                                </div>

                                <!-- Autocomplete Pencarian Cepat Wilayah Indonesia -->
                                <div class="relative">
                                    <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1.5">
                                        Cari Wilayah Cepat (Autocomplete Biteship &amp; Administrasi Indonesia)
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                            <i data-lucide="search" class="w-4 h-4"></i>
                                        </div>
                                        <input type="text"
                                               x-model="branchSearchQuery"
                                               @input.debounce.350ms="searchBranchAreas('create')"
                                               @focus="if(branchSearchResults.length > 0) branchShowDropdown = true"
                                               class="w-full pl-10 pr-10 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] sm:rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs sm:text-[13px] outline-none"
                                               placeholder="Ketik Kode Pos (cth: 12190) atau Nama Kelurahan / Desa (cth: Senayan / Senopati)...">
                                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center">
                                            <template x-if="branchIsSearching">
                                                <i data-lucide="loader-2" class="w-4 h-4 text-[#007AFF] animate-spin"></i>
                                            </template>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-black/50 dark:text-white/50 mt-1">
                                        Ketik minimal 3 karakter untuk mengisi otomatis seluruh kolom wilayah di bawah dan memindahkan pin peta GPS, atau isi langsung secara manual.
                                    </p>

                                    <!-- Dropdown Hasil Pencarian Wilayah -->
                                    <div x-show="branchShowDropdown && branchSearchResults.length > 0" x-cloak
                                         @click.outside="branchShowDropdown = false"
                                         class="absolute z-30 left-0 right-0 mt-1.5 max-h-60 overflow-y-auto bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[16px] shadow-2xl divide-y divide-black/5 dark:divide-white/5">
                                        <template x-for="item in branchSearchResults" :key="item.id || item.label">
                                            <button type="button"
                                                    @click="selectBranchArea('create', item)"
                                                    class="w-full px-4 py-3 text-left hover:bg-[#007AFF]/10 dark:hover:bg-[#0A84FF]/15 transition-colors flex items-start gap-3 cursor-pointer">
                                                <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF] flex items-center justify-center shrink-0 mt-0.5">
                                                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-[13px] font-semibold text-black dark:text-white truncate" x-text="item.village ? item.village + ', ' + item.district : item.label"></p>
                                                    <p class="text-[11.5px] text-black/55 dark:text-white/55" x-text="item.city + ', ' + item.province"></p>
                                                </div>
                                                <span class="px-2 py-0.5 rounded-[6px] text-[11px] font-mono font-bold bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 shrink-0" x-text="item.postal_code || '-'"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <!-- Bento Grid Form Wilayah Administratif (Consistent Apple HIG Inputs) -->
                                <div class="p-4 sm:p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 space-y-3.5">
                                    <div class="flex items-center justify-between pb-1 border-b border-black/5 dark:border-white/5">
                                        <span class="text-[11px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 flex items-center gap-1.5">
                                            <i data-lucide="map-pinned" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                            Data Wilayah Administratif Cabang
                                        </span>
                                        <span class="text-[10.5px] font-medium text-black/45 dark:text-white/45">Bisa diedit manual</span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                        <div>
                                            <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1">
                                                Provinsi <span class="text-[#FF3B30]">*</span>
                                            </label>
                                            <input type="text" name="province" x-model="branchForm.province" required
                                                   class="w-full px-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs outline-none"
                                                   placeholder="Contoh: DKI Jakarta">
                                        </div>

                                        <div>
                                            <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1">
                                                Kota / Kabupaten <span class="text-[#FF3B30]">*</span>
                                            </label>
                                            <input type="text" name="city" x-model="branchForm.city" required
                                                   class="w-full px-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs outline-none"
                                                   placeholder="Contoh: Kota Jakarta Selatan">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                                        <div>
                                            <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1">
                                                Kecamatan <span class="text-[#FF3B30]">*</span>
                                            </label>
                                            <input type="text" name="district" x-model="branchForm.district" required
                                                   class="w-full px-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs outline-none"
                                                   placeholder="Contoh: Kebayoran Baru">
                                        </div>

                                        <div>
                                            <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1">
                                                Kelurahan / Desa <span class="text-[#FF3B30]">*</span>
                                            </label>
                                            <input type="text" name="village" x-model="branchForm.village" required
                                                   class="w-full px-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs outline-none"
                                                   placeholder="Contoh: Senayan">
                                        </div>

                                        <div>
                                            <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1">
                                                Kode Pos <span class="text-[#FF3B30]">*</span>
                                            </label>
                                            <input type="text" name="postal_code" x-model="branchForm.postal_code" required
                                                   class="w-full px-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs outline-none font-mono tabular-nums"
                                                   placeholder="Contoh: 12190">
                                        </div>
                                    </div>
                                </div>

                                <!-- Detail Alamat Jalan / Nomor Bangunan -->
                                <div>
                                    <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1.5">
                                        Detail Alamat Jalan / No. Ruko / Patokan Lokasi <span class="text-[#FF3B30]">*</span>
                                    </label>
                                    <textarea name="address" rows="2" required x-model="branchForm.address"
                                              class="w-full px-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] sm:rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs sm:text-[13px] outline-none resize-none"
                                              placeholder="Contoh: Jl. Senopati No. 45, Ruko Blok B-2 (Sebelah Bank BCA)"></textarea>
                                </div>

                                <!-- Peta Interaktif Leaflet.js -->
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="block font-semibold text-black/80 dark:text-white/85 text-xs">
                                            Titik Koordinat Peta GPS (Geser Pin ke Lokasi Cabang Toko) <span class="text-[#FF3B30]">*</span>
                                        </label>
                                        <button type="button"
                                                @click="useCurrentBranchGps('create')"
                                                :disabled="branchGpsLoading"
                                                class="text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                                            <i data-lucide="crosshair" class="w-3.5 h-3.5" :class="{'animate-spin': branchGpsLoading}"></i>
                                            <span x-text="branchGpsLoading ? 'Mencari GPS...' : 'Gunakan GPS Saya'"></span>
                                        </button>
                                    </div>

                                    <!-- Map Canvas Container (Apple HIG Squircle) -->
                                    <div class="relative rounded-[16px] sm:rounded-[20px] overflow-hidden border border-black/10 dark:border-white/10 shadow-sm">
                                        <div id="branch-create-map" class="w-full h-[260px] sm:h-[300px] bg-black/5 dark:bg-white/5 z-0"></div>
                                        <div class="absolute bottom-2.5 left-2.5 z-10 px-3 py-1.5 rounded-[10px] sm:rounded-[12px] bg-white/95 dark:bg-black/85 backdrop-blur-md border border-black/10 dark:border-white/10 text-[11px] font-mono text-black/70 dark:text-white/70 shadow-md flex items-center gap-2">
                                            <span class="font-bold text-[#007AFF] dark:text-[#0A84FF]">GPS:</span>
                                            <span x-text="branchForm.latitude ? Number(branchForm.latitude).toFixed(5) : '-'"></span>,
                                            <span x-text="branchForm.longitude ? Number(branchForm.longitude).toFixed(5) : '-'"></span>
                                            <span class="text-black/30 dark:text-white/30">&bull;</span>
                                            <span class="text-[#34C759] font-bold">Radius: <span x-text="branchForm.geofence_radius_meters"></span>m</span>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-black/50 dark:text-white/50">
                                        Anda dapat mengklik atau menggeser pin biru di atas peta untuk menyesuaikan titik tepat cabang toko Anda. Lingkaran biru menandakan zona toleransi absensi karyawan.
                                    </p>
                                </div>

                                <!-- Slider Geofence Radius Presensi -->
                                <div class="p-3.5 rounded-[14px] sm:rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 space-y-1.5">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-semibold text-black/80 dark:text-white/80">{{ __('settings.branch_geofence_radius') }}</span>
                                        <span class="font-bold text-[#007AFF] font-mono"><span x-text="branchForm.geofence_radius_meters"></span> Meter</span>
                                    </div>
                                    <input type="range" name="geofence_radius_meters" min="20" max="1000" step="10"
                                           x-model="branchForm.geofence_radius_meters"
                                           @input="updateGeofenceCircle('create')"
                                           class="w-full accent-[#007AFF] cursor-pointer">
                                    <p class="text-[10.5px] text-black/45 dark:text-white/45">Radius toleransi absensi kehadiran karyawan via portal staf menggunakan GPS handphone.</p>
                                </div>

                                <!-- Hidden Form Inputs for Geolocation Metadata -->
                                <input type="hidden" name="latitude" :value="branchForm.latitude">
                                <input type="hidden" name="longitude" :value="branchForm.longitude">
                                <input type="hidden" name="biteship_area_id" :value="branchForm.biteship_area_id">
                            </div>

                            {{-- ========================================================== --}}
                            {{-- BAGIAN 3: PENGATURAN OPERASIONAL & TOKO ONLINE             --}}
                            {{-- ========================================================== --}}
                            <div class="space-y-4 pt-2">
                                <div class="flex items-center justify-between pb-1.5 border-b border-black/5 dark:border-white/5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-[#007AFF]/15 text-[#007AFF] text-xs font-bold flex items-center justify-center">3</span>
                                        <h3 class="text-xs sm:text-sm font-bold text-black dark:text-white uppercase tracking-wider">Pengaturan Operasional &amp; Toko Online</h3>
                                    </div>
                                    <span class="text-[11px] font-medium text-black/45 dark:text-white/45">Fulfillment &amp; Presensi</span>
                                </div>

                                <div class="space-y-3">
                                    <label class="flex items-start gap-3 p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.08] cursor-pointer hover:border-[#FF9500]/40 transition">
                                        <input type="checkbox" name="is_primary" value="1" x-model="branchForm.is_primary" class="mt-0.5 w-4 h-4 rounded text-[#FF9500] focus:ring-[#FF9500]">
                                        <div class="text-xs">
                                            <span class="font-bold text-black dark:text-white block">Jadikan Cabang Utama (Primary Branch)</span>
                                            <span class="text-[11px] text-black/55 dark:text-white/55 block mt-0.5">Digunakan sebagai titik default penjemputan kurir online dan basis lokasi profil usaha.</span>
                                        </div>
                                    </label>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <label class="flex items-start gap-2.5 p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.08] cursor-pointer">
                                            <input type="checkbox" name="is_online_fulfillment" value="1" x-model="branchForm.is_online_fulfillment" class="mt-0.5 w-4 h-4 rounded text-[#007AFF] focus:ring-[#007AFF]">
                                            <div class="text-xs">
                                                <span class="font-semibold text-black/85 dark:text-white/85 block">Titik Kirim Kurir Online</span>
                                                <span class="text-[10.5px] text-black/50 dark:text-white/50 block">Pesanan toko online dapat dikirim dari cabang ini via Biteship.</span>
                                            </div>
                                        </label>
                                        <label class="flex items-start gap-2.5 p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.08] cursor-pointer">
                                            <input type="checkbox" name="allow_storefront_pickup" value="1" x-model="branchForm.allow_storefront_pickup" class="mt-0.5 w-4 h-4 rounded text-[#007AFF] focus:ring-[#007AFF]">
                                            <div class="text-xs">
                                                <span class="font-semibold text-black/85 dark:text-white/85 block">Izinkan Ambil di Toko (Self Pickup)</span>
                                                <span class="text-[10.5px] text-black/50 dark:text-white/50 block">Pelanggan dapat memilih opsi ambil mandiri langsung di cabang ini.</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="px-5 py-3.5 sm:px-6 sm:py-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-end gap-3 shrink-0 bg-slate-50/50 dark:bg-white/[0.02]">
                            <button type="button" @click="showCreateBranchModal = false"
                                    class="min-h-[44px] h-11 px-5 rounded-[12px] text-xs sm:text-[13px] font-medium text-black/70 dark:text-white/70 bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 active:scale-[0.98] transition cursor-pointer">
                                {{ __('settings.cancel') }}
                            </button>
                            <button type="submit" :disabled="branchSubmitting"
                                    class="min-h-[44px] h-11 px-6 rounded-[12px] text-xs sm:text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition shadow-[0_1px_3px_rgba(0,122,255,0.3)] flex items-center gap-2 cursor-pointer disabled:opacity-50">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                                <span x-text="branchSubmitting ? 'Menyimpan...' : 'Simpan Cabang'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

        {{-- ===================================================== --}}
        {{-- MODAL 2: EDIT DATA CABANG (APPLE BENTO MAP SHEET)     --}}
        {{-- ===================================================== --}}
        <template x-teleport="body">
            <div x-show="showEditBranchModal"
                 x-cloak
                 class="fixed inset-0 z-[200] overflow-y-auto"
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="edit-branch-modal-title"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">

                <!-- Frosted Dark Backdrop (Edge-to-Edge over topbar and layout) -->
                <div class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-sm transition-opacity"
                     @click="showEditBranchModal = false"
                     aria-hidden="true"></div>

                <!-- Centering Wrapper -->
                <div class="min-h-full flex items-center justify-center p-3 sm:p-6 pointer-events-none">
                    <div class="relative z-10 pointer-events-auto w-full max-w-[95vw] lg:max-w-4xl xl:max-w-5xl max-h-[92vh] rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] shadow-[0_25px_60px_rgba(0,0,0,0.35)] flex flex-col overflow-hidden"
                         @keydown.escape.window="showEditBranchModal = false">

                    {{-- Header --}}
                    <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between shrink-0 bg-slate-50/50 dark:bg-white/[0.02]">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-[14px] bg-[#007AFF]/12 text-[#007AFF] flex items-center justify-center shrink-0 border border-[#007AFF]/20 shadow-sm">
                                <i data-lucide="pencil" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                            </div>
                            <div>
                                <p class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 mb-0.5">
                                    Cabang &amp; Outlet &bull; Pengaturan Usaha
                                </p>
                                <h3 class="text-base sm:text-lg font-bold text-black dark:text-white tracking-tight">{{ __('settings.edit_branch_title') }}</h3>
                                <p class="text-xs text-black/55 dark:text-white/55 mt-0.5">Perbarui informasi cabang, titik koordinat peta GPS, dan wilayah pengiriman toko.</p>
                            </div>
                        </div>
                        <button type="button" @click="showEditBranchModal = false"
                                class="min-h-[44px] min-w-[44px] rounded-[12px] text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition flex items-center justify-center cursor-pointer"
                                aria-label="Tutup">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    {{-- Scrollable Form --}}
                    <form :action="branchEditUrl" method="POST" class="flex flex-col flex-1 overflow-hidden" @submit="branchSubmitting = true">
                        @csrf
                        @method('PUT')

                        <div class="p-5 sm:p-6 overflow-y-auto space-y-6 flex-1">
                            @if ($errors->any())
                                <div class="p-4 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-xs">
                                    <div class="font-bold mb-1.5 flex items-center gap-2">
                                        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                                        <span>Ada data yang perlu diperbaiki:</span>
                                    </div>
                                    <ul class="list-disc list-inside space-y-1 opacity-90 pl-1">
                                        @foreach ($errors->all() as $err)
                                            <li>{{ $err }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- ========================================================== --}}
                            {{-- BAGIAN 1: IDENTITAS CABANG & KONTAK                        --}}
                            {{-- ========================================================== --}}
                            <div class="space-y-4">
                                <div class="flex items-center justify-between pb-1.5 border-b border-black/5 dark:border-white/5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-[#007AFF]/15 text-[#007AFF] text-xs font-bold flex items-center justify-center">1</span>
                                        <h3 class="text-xs sm:text-sm font-bold text-black dark:text-white uppercase tracking-wider">Identitas Cabang &amp; Kontak</h3>
                                    </div>
                                    <span class="text-[11px] font-medium text-black/45 dark:text-white/45">Data Operasional Toko</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div class="sm:col-span-1">
                                        <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1.5">
                                            {{ __('settings.branch_name') }} <span class="text-[#FF3B30]">*</span>
                                        </label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                                <i data-lucide="store" class="w-4 h-4"></i>
                                            </div>
                                            <input type="text" name="name" x-model="branchEditData.name" required
                                                   class="w-full pl-10 pr-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs sm:text-[13px] outline-none">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1.5">
                                            {{ __('settings.branch_code') }}
                                        </label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                                <i data-lucide="hash" class="w-4 h-4"></i>
                                            </div>
                                            <input type="text" name="code" x-model="branchEditData.code" placeholder="Misal: SNP-01"
                                                   class="w-full pl-10 pr-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs sm:text-[13px] font-mono uppercase outline-none">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1.5">
                                            Nomor WhatsApp / HP Cabang
                                        </label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                                <i data-lucide="smartphone" class="w-4 h-4"></i>
                                            </div>
                                            <input type="tel" name="phone" x-model="branchEditData.phone" placeholder="081234567890"
                                                   class="w-full pl-10 pr-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs sm:text-[13px] font-mono outline-none">
                                        </div>
                                    </div>
                                </div>

                                <!-- Sakelar Status Cabang Aktif -->
                                <div class="pt-1">
                                    <label class="flex items-center gap-3 p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 cursor-pointer">
                                        <input type="checkbox" name="is_active" value="1" x-model="branchEditData.is_active" class="w-4 h-4 rounded text-[#34C759] focus:ring-[#34C759]">
                                        <span class="text-xs font-semibold text-black/85 dark:text-white/85">Status Cabang Aktif (Dapat Melayani Kasir &amp; Presensi Pegawai)</span>
                                    </label>
                                </div>
                            </div>

                            {{-- ========================================================== --}}
                            {{-- BAGIAN 2: TITIK LOKASI & WILAYAH INDONESIA (LEAFLET.JS)   --}}
                            {{-- ========================================================== --}}
                            <div class="space-y-4 pt-2">
                                <div class="flex items-center justify-between pb-1.5 border-b border-black/5 dark:border-white/5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-[#007AFF]/15 text-[#007AFF] text-xs font-bold flex items-center justify-center">2</span>
                                        <h3 class="text-xs sm:text-sm font-bold text-black dark:text-white uppercase tracking-wider">Titik Lokasi Cabang &amp; Wilayah Indonesia</h3>
                                    </div>
                                    <span class="text-[11px] font-medium text-black/45 dark:text-white/45">Tingkat Desa / Kelurahan</span>
                                </div>

                                <!-- Autocomplete Pencarian Cepat Wilayah Indonesia -->
                                <div class="relative">
                                    <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1.5">
                                        Cari Wilayah Cepat (Autocomplete Biteship &amp; Administrasi Indonesia)
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                            <i data-lucide="search" class="w-4 h-4"></i>
                                        </div>
                                        <input type="text"
                                               x-model="branchSearchQuery"
                                               @input.debounce.350ms="searchBranchAreas('edit')"
                                               @focus="if(branchSearchResults.length > 0) branchShowDropdown = true"
                                               class="w-full pl-10 pr-10 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs sm:text-[13px] outline-none"
                                               placeholder="Ketik Kode Pos (cth: 12190) atau Nama Kelurahan / Desa (cth: Senayan / Senopati)...">
                                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center">
                                            <template x-if="branchIsSearching">
                                                <i data-lucide="loader-2" class="w-4 h-4 text-[#007AFF] animate-spin"></i>
                                            </template>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-black/50 dark:text-white/50 mt-1">
                                        Ketik minimal 3 karakter untuk mengisi otomatis seluruh kolom wilayah di bawah dan memindahkan pin peta GPS, atau isi langsung secara manual.
                                    </p>

                                    <!-- Dropdown Hasil Pencarian Wilayah -->
                                    <div x-show="branchShowDropdown && branchSearchResults.length > 0" x-cloak
                                         @click.outside="branchShowDropdown = false"
                                         class="absolute z-30 left-0 right-0 mt-1.5 max-h-60 overflow-y-auto bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[18px] shadow-2xl divide-y divide-black/5 dark:divide-white/5">
                                        <template x-for="item in branchSearchResults" :key="item.id || item.label">
                                            <button type="button"
                                                    @click="selectBranchArea('edit', item)"
                                                    class="w-full px-4 py-3 text-left hover:bg-[#007AFF]/10 dark:hover:bg-[#0A84FF]/15 transition-colors flex items-start gap-3 cursor-pointer">
                                                <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF] flex items-center justify-center shrink-0 mt-0.5">
                                                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-[13px] font-semibold text-black dark:text-white truncate" x-text="item.village ? item.village + ', ' + item.district : item.label"></p>
                                                    <p class="text-[11.5px] text-black/55 dark:text-white/55" x-text="item.city + ', ' + item.province"></p>
                                                </div>
                                                <span class="px-2 py-0.5 rounded-[6px] text-[11px] font-mono font-bold bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 shrink-0" x-text="item.postal_code || '-'"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <!-- Bento Grid Form Wilayah Administratif (Visible & Editable) -->
                                <div class="p-4 sm:p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 space-y-3.5">
                                    <div class="flex items-center justify-between pb-1 border-b border-black/5 dark:border-white/5">
                                        <span class="text-[11px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 flex items-center gap-1.5">
                                            <i data-lucide="map-pinned" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                            Data Wilayah Administratif Cabang
                                        </span>
                                        <span class="text-[10.5px] font-medium text-black/45 dark:text-white/45">Bisa diedit manual</span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                        <div>
                                            <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1">
                                                Provinsi <span class="text-[#FF3B30]">*</span>
                                            </label>
                                            <input type="text" name="province" x-model="branchEditData.province" required
                                                   class="w-full px-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs outline-none"
                                                   placeholder="Contoh: DKI Jakarta">
                                        </div>

                                        <div>
                                            <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1">
                                                Kota / Kabupaten <span class="text-[#FF3B30]">*</span>
                                            </label>
                                            <input type="text" name="city" x-model="branchEditData.city" required
                                                   class="w-full px-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs outline-none"
                                                   placeholder="Contoh: Kota Jakarta Selatan">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                                        <div>
                                            <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1">
                                                Kecamatan <span class="text-[#FF3B30]">*</span>
                                            </label>
                                            <input type="text" name="district" x-model="branchEditData.district" required
                                                   class="w-full px-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs outline-none"
                                                   placeholder="Contoh: Kebayoran Baru">
                                        </div>

                                        <div>
                                            <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1">
                                                Kelurahan / Desa <span class="text-[#FF3B30]">*</span>
                                            </label>
                                            <input type="text" name="village" x-model="branchEditData.village" required
                                                   class="w-full px-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs outline-none"
                                                   placeholder="Contoh: Senayan">
                                        </div>

                                        <div>
                                            <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1">
                                                Kode Pos <span class="text-[#FF3B30]">*</span>
                                            </label>
                                            <input type="text" name="postal_code" x-model="branchEditData.postal_code" required
                                                   class="w-full px-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs outline-none font-mono"
                                                   placeholder="Contoh: 12190">
                                        </div>
                                    </div>
                                </div>

                                <!-- Detail Alamat Jalan / Nomor Bangunan -->
                                <div>
                                    <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1.5">
                                        Detail Alamat Jalan / No. Ruko / Patokan Lokasi <span class="text-[#FF3B30]">*</span>
                                    </label>
                                    <textarea name="address" rows="2" required x-model="branchEditData.address"
                                              class="w-full px-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs sm:text-[13px] outline-none resize-none"
                                              placeholder="Contoh: Jl. Senopati No. 45, Ruko Blok B-2 (Sebelah Bank BCA)"></textarea>
                                </div>

                                <!-- Peta Interaktif Leaflet.js -->
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="block font-semibold text-black/80 dark:text-white/85 text-xs">
                                            Titik Koordinat Peta GPS (Geser Pin ke Lokasi Cabang Toko) <span class="text-[#FF3B30]">*</span>
                                        </label>
                                        <button type="button"
                                                @click="useCurrentBranchGps('edit')"
                                                :disabled="branchGpsLoading"
                                                class="text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                                            <i data-lucide="crosshair" class="w-3.5 h-3.5" :class="{'animate-spin': branchGpsLoading}"></i>
                                            <span x-text="branchGpsLoading ? 'Mencari GPS...' : 'Gunakan GPS Saya'"></span>
                                        </button>
                                    </div>

                                    <!-- Map Canvas Container (Apple HIG Squircle) -->
                                    <div class="relative rounded-[20px] overflow-hidden border border-black/10 dark:border-white/10 shadow-sm">
                                        <div id="branch-edit-map" class="w-full h-[260px] sm:h-[300px] bg-black/5 dark:bg-white/5 z-0"></div>
                                        <div class="absolute bottom-2.5 left-2.5 z-10 px-3 py-1.5 rounded-[12px] bg-white/95 dark:bg-black/85 backdrop-blur-md border border-black/10 dark:border-white/10 text-[11px] font-mono text-black/70 dark:text-white/70 shadow-md flex items-center gap-2">
                                            <span class="font-bold text-[#007AFF] dark:text-[#0A84FF]">GPS:</span>
                                            <span x-text="branchEditData.latitude ? Number(branchEditData.latitude).toFixed(5) : '-'"></span>,
                                            <span x-text="branchEditData.longitude ? Number(branchEditData.longitude).toFixed(5) : '-'"></span>
                                            <span class="text-black/30 dark:text-white/30">&bull;</span>
                                            <span class="text-[#34C759] font-bold">Radius: <span x-text="branchEditData.geofence_radius_meters"></span>m</span>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-black/50 dark:text-white/50">
                                        Anda dapat mengklik atau menggeser pin biru di atas peta untuk menyesuaikan titik tepat cabang toko Anda. Lingkaran biru menandakan zona toleransi absensi karyawan.
                                    </p>
                                </div>

                                <!-- Slider Geofence Radius Presensi -->
                                <div class="p-3.5 rounded-[14px] sm:rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 space-y-1.5">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-semibold text-black/80 dark:text-white/80">{{ __('settings.branch_geofence_radius') }}</span>
                                        <span class="font-bold text-[#007AFF] font-mono"><span x-text="branchEditData.geofence_radius_meters"></span> Meter</span>
                                    </div>
                                    <input type="range" name="geofence_radius_meters" min="20" max="1000" step="10"
                                           x-model="branchEditData.geofence_radius_meters"
                                           @input="updateGeofenceCircle('edit')"
                                           class="w-full accent-[#007AFF] cursor-pointer">
                                    <p class="text-[10.5px] text-black/45 dark:text-white/45">Radius toleransi absensi kehadiran karyawan via portal staf menggunakan GPS handphone.</p>
                                </div>

                                <!-- Hidden Form Inputs for Geolocation Metadata -->
                                <input type="hidden" name="latitude" :value="branchEditData.latitude">
                                <input type="hidden" name="longitude" :value="branchEditData.longitude">
                                <input type="hidden" name="biteship_area_id" :value="branchEditData.biteship_area_id">
                            </div>

                            {{-- ========================================================== --}}
                            {{-- BAGIAN 3: PENGATURAN OPERASIONAL & TOKO ONLINE             --}}
                            {{-- ========================================================== --}}
                            <div class="space-y-4 pt-2">
                                <div class="flex items-center justify-between pb-1.5 border-b border-black/5 dark:border-white/5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-[#007AFF]/15 text-[#007AFF] text-xs font-bold flex items-center justify-center">3</span>
                                        <h3 class="text-xs sm:text-sm font-bold text-black dark:text-white uppercase tracking-wider">Pengaturan Operasional &amp; Toko Online</h3>
                                    </div>
                                    <span class="text-[11px] font-medium text-black/45 dark:text-white/45">Fulfillment &amp; Presensi</span>
                                </div>

                                <div class="space-y-3">
                                    <label class="flex items-start gap-3 p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.08] cursor-pointer hover:border-[#FF9500]/40 transition">
                                        <input type="checkbox" name="is_primary" value="1" x-model="branchEditData.is_primary" class="mt-0.5 w-4 h-4 rounded text-[#FF9500] focus:ring-[#FF9500]">
                                        <div class="text-xs">
                                            <span class="font-bold text-black dark:text-white block">Jadikan Cabang Utama (Primary Branch)</span>
                                            <span class="text-[11px] text-black/55 dark:text-white/55 block mt-0.5">Digunakan sebagai titik default penjemputan kurir online dan basis lokasi profil usaha.</span>
                                        </div>
                                    </label>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <label class="flex items-start gap-2.5 p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.08] cursor-pointer">
                                            <input type="checkbox" name="is_online_fulfillment" value="1" x-model="branchEditData.is_online_fulfillment" class="mt-0.5 w-4 h-4 rounded text-[#007AFF] focus:ring-[#007AFF]">
                                            <div class="text-xs">
                                                <span class="font-semibold text-black/85 dark:text-white/85 block">Titik Kirim Kurir Online</span>
                                                <span class="text-[10.5px] text-black/50 dark:text-white/50 block">Pesanan toko online dapat dikirim dari cabang ini via Biteship.</span>
                                            </div>
                                        </label>
                                        <label class="flex items-start gap-2.5 p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.08] cursor-pointer">
                                            <input type="checkbox" name="allow_storefront_pickup" value="1" x-model="branchEditData.allow_storefront_pickup" class="mt-0.5 w-4 h-4 rounded text-[#007AFF] focus:ring-[#007AFF]">
                                            <div class="text-xs">
                                                <span class="font-semibold text-black/85 dark:text-white/85 block">Izinkan Ambil di Toko (Self Pickup)</span>
                                                <span class="text-[10.5px] text-black/50 dark:text-white/50 block">Pelanggan dapat memilih opsi ambil mandiri langsung di cabang ini.</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="px-5 py-3.5 sm:px-6 sm:py-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-end gap-3 shrink-0 bg-slate-50/50 dark:bg-white/[0.02]">
                            <button type="button" @click="showEditBranchModal = false"
                                    class="min-h-[44px] h-11 px-5 rounded-[12px] text-xs sm:text-[13px] font-medium text-black/70 dark:text-white/70 bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 active:scale-[0.98] transition cursor-pointer">
                                {{ __('settings.cancel') }}
                            </button>
                            <button type="submit" :disabled="branchSubmitting"
                                    class="min-h-[44px] h-11 px-6 rounded-[12px] text-xs sm:text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition shadow-[0_1px_3px_rgba(0,122,255,0.3)] flex items-center gap-2 cursor-pointer disabled:opacity-50">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                <span x-text="branchSubmitting ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

        {{-- ===================================================== --}}
        {{-- MODAL 3: KONFIRMASI HAPUS CABANG (APPLE ALERT)        --}}
        {{-- ===================================================== --}}
        <template x-teleport="body">
            <div x-show="deleteBranchModalOpen"
                 x-cloak
                 class="fixed inset-0 z-[200] overflow-y-auto"
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="delete-branch-modal-title"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">

                <!-- Frosted Dark Backdrop (Edge-to-Edge over topbar and layout) -->
                <div class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-sm transition-opacity"
                     @click="closeDeleteBranch()"
                     aria-hidden="true"></div>

                <!-- Centering Wrapper -->
                <div class="min-h-full flex items-center justify-center p-4 pointer-events-none">
                    <div class="relative z-10 pointer-events-auto w-full max-w-sm rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] shadow-2xl overflow-hidden text-center"
                         @keydown.escape.window="closeDeleteBranch()">
                        <div class="p-6">
                            <div class="w-12 h-12 rounded-full bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center mx-auto mb-3.5">
                                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                            </div>
                            <p class="text-base font-bold text-black dark:text-white">{{ __('settings.delete_branch_confirm_title') }}</p>
                            <p class="text-xs text-black/60 dark:text-white/60 mt-1.5 leading-relaxed">
                                Cabang <span x-text="branchDeleteTarget.name" class="font-bold text-black dark:text-white"></span> akan dihapus dari sistem. Jika memiliki riwayat transaksi/stok, cabang akan dinonaktifkan otomatis.
                            </p>
                        </div>

                        <form x-ref="deleteBranchForm" :action="branchDeleteUrl" method="POST" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>

                        <div class="grid grid-cols-2 border-t border-black/[0.08] dark:border-white/[0.12] text-xs font-semibold">
                            <button type="button" @click="closeDeleteBranch()"
                                    class="min-h-[44px] py-3.5 text-black/70 dark:text-white/70 border-r border-black/[0.08] dark:border-white/[0.12] active:bg-black/5 dark:active:bg-white/5 transition cursor-pointer flex items-center justify-center">
                                {{ __('settings.cancel') }}
                            </button>
                            <button type="button" @click="submitDeleteBranch()"
                                    class="min-h-[44px] py-3.5 text-[#FF3B30] dark:text-[#FF453A] font-bold active:bg-black/5 dark:active:bg-white/5 transition cursor-pointer flex items-center justify-center">
                                {{ __('settings.delete_branch') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <script>
        function settingsPage() {
            return {
                activeTab: @json(request('tab', session('active_tab', 'general'))),
                templateSearch: '',
                selectedCategory: 'all',
                logoPreview: @json($business->logo_url),
                roundingStrategy: @json($business->rounding_strategy ?? 'ROUND_100'),
                templateModalOpen: false,
                selectedTemplate: {
                    code: '',
                    name: '',
                    category: ''
                },

                // Operations & Timezone state
                operatingHours: @json($operatingHours),
                selectedTimezone: @json($currentTimezone),

                // Modules Management Reactive & Auto-Save State
                isOwner: @json(\App\Support\Context::isOwner()),
                totalModulesCount: {{ count($allModules) }},
                activeModulesCount: {{ count($allModules) - count($business->disabled_modules ?? []) }},
                modulesState: {
                    @foreach($allModules as $mKey => $mDef)
                        '{{ $mKey }}': {{ $business->isModuleEnabled($mKey) ? 'true' : 'false' }},
                    @endforeach
                },
                moduleSaving: {},
                moduleSaveStatus: 'idle', // 'idle' | 'saving' | 'saved' | 'error'
                saveStatusTimer: null,

                async toggleModule(moduleKey, isChecked) {
                    if (!this.isOwner) return;

                    this.moduleSaving[moduleKey] = true;
                    this.moduleSaveStatus = 'saving';
                    if (this.saveStatusTimer) clearTimeout(this.saveStatusTimer);

                    // Optimistic update of counter
                    this.activeModulesCount = Object.values(this.modulesState).filter(Boolean).length;

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                            || document.querySelector('input[name="_token"]')?.value;

                        const response = await fetch('{{ route('settings.modules.update') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({
                                _method: 'PUT',
                                module_key: moduleKey,
                                enabled: isChecked,
                            }),
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            this.moduleSaveStatus = 'saved';
                            if (typeof data.enabled_count === 'number') {
                                this.activeModulesCount = data.enabled_count;
                            }
                            if (window.AppAlert && data.message) {
                                window.AppAlert.toast('success', data.message, { duration: 3000 });
                            }
                            this.saveStatusTimer = setTimeout(() => {
                                if (this.moduleSaveStatus === 'saved') {
                                    this.moduleSaveStatus = 'idle';
                                }
                            }, 2500);
                        } else {
                            throw new Error(data.message || '{{ __('settings.module_save_failed_msg') }}');
                        }
                    } catch (err) {
                        console.error('Module auto-save error:', err);
                        // Revert checkbox & counter state
                        this.modulesState[moduleKey] = !isChecked;
                        this.activeModulesCount = Object.values(this.modulesState).filter(Boolean).length;
                        this.moduleSaveStatus = 'error';

                        if (window.AppAlert) {
                            window.AppAlert.toast('error', err.message || '{{ __('settings.module_save_failed_msg') }}');
                        }
                        this.saveStatusTimer = setTimeout(() => {
                            if (this.moduleSaveStatus === 'error') {
                                this.moduleSaveStatus = 'idle';
                            }
                        }, 4000);
                    } finally {
                        this.moduleSaving[moduleKey] = false;
                    }
                },

                init() {
                    // Deep-linking URL synchronization on tab change without page reload
                    this.$watch('activeTab', (val) => {
                        const url = new URL(window.location);
                        url.searchParams.set('tab', val);
                        window.history.replaceState({}, '', url);
                    });

                    if (this.showCreateBranchModal) {
                        this.$nextTick(() => {
                            setTimeout(() => {
                                this.initBranchCreateMap();
                            }, 350);
                        });
                    }
                },

                addPeriod(dayKey) {
                    if (!this.operatingHours[dayKey]) {
                        this.operatingHours[dayKey] = { day_name: dayKey, is_open: true, periods: [] };
                    }
                    if (!Array.isArray(this.operatingHours[dayKey].periods)) {
                        this.operatingHours[dayKey].periods = [];
                    }
                    this.operatingHours[dayKey].periods.push({ start: '17:00', end: '22:00' });
                },

                removePeriod(dayKey, index) {
                    if (this.operatingHours[dayKey] && Array.isArray(this.operatingHours[dayKey].periods)) {
                        this.operatingHours[dayKey].periods.splice(index, 1);
                    }
                },

                isOvernight(period) {
                    if (!period || !period.start || !period.end) return false;
                    return period.end < period.start;
                },

                // WhatsApp Receipt CMS & WABA state
                previewMode: 'waba',
                receiptFooterNote: @json($business->pos_receipt_footer_note ?? ''),
                waTemplateText: @json($business->pos_receipt_wa_template ?: __('settings.default_wa_template')),

                insertTag(tag) {
                    const el = this.$refs.waTextarea;
                    if (!el) {
                        this.waTemplateText += tag;
                        return;
                    }
                    const start = el.selectionStart || 0;
                    const end = el.selectionEnd || 0;
                    const current = this.waTemplateText || '';
                    this.waTemplateText = current.substring(0, start) + tag + current.substring(end);
                    this.$nextTick(() => {
                        el.focus();
                        el.setSelectionRange(start + tag.length, start + tag.length);
                    });
                },

                resetToDefaultTemplate() {
                    this.waTemplateText = @json(__('settings.default_wa_template'));
                },

                get waRenderedPreview() {
                    let text = this.waTemplateText || '';
                    const replacements = {
                        '{business_name}': @json($business->name),
                        '{customer_name}': 'Agung Mustaqim',
                        '{order_number}': 'POS-20260910-0042',
                        '{date}': @json(now()->format('d/m/Y H:i')),
                        '{cashier_name}': @json(auth()->user()?->name ?? __('settings.default_cashier_name')),
                        '{receipt_link}': 'https://cooca.id/receipt/sample',
                        '{footer_note}': this.receiptFooterNote || @json($business->pos_receipt_footer_note ?? __('settings.default_footer_note'))
                    };
                    for (const [key, val] of Object.entries(replacements)) {
                        text = text.replaceAll(key, val || '');
                    }
                    return text;
                },

                formatWaMarkdown(str) {
                    if (!str) return '';
                    let escaped = str
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;');
                    escaped = escaped.replace(/\*([^\*]+)\*/g,
                        '<strong class="font-bold text-black dark:text-white">$1</strong>');
                    escaped = escaped.replace(/_([^_]+)_/g, '<em class="italic">$1</em>');
                    escaped = escaped.replace(/~([^~]+)~/g, '<del class="line-through opacity-60">$1</del>');
                    escaped = escaped.replace(/```([^`]+)```/g,
                        '<code class="bg-black/10 dark:bg-white/10 px-1 py-0.5 rounded font-mono text-[11px]">$1</code>'
                    );
                    return escaped.replace(/\n/g, '<br>');
                },

                openApplyTemplate(code, name, category) {
                    this.selectedTemplate = {
                        code,
                        name,
                        category
                    };
                    this.templateModalOpen = true;
                },

                closeApplyTemplate() {
                    this.templateModalOpen = false;
                    this.selectedTemplate = {
                        code: '',
                        name: '',
                        category: ''
                    };
                },

                submitApplyTemplate() {
                    if (this.selectedTemplate.code) {
                        document.getElementById('form-apply-template-' + this.selectedTemplate.code).submit();
                    }
                },

                handleLogoChange(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (ev) => {
                            this.logoPreview = ev.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                },

                getRoundingExample(strategy) {
                    const samples = {
                        'ROUND': 'Rp 14.234 → Rp 14.234 (Presisi desimal normal)',
                        'CEIL': 'Rp 14.234 → Rp 15.000 (Plafon ke atas)',
                        'FLOOR': 'Rp 14.234 → Rp 14.000 (Pangkas ke bawah)',
                        'ROUND_50': 'Rp 14.234 → Rp 14.250 (Kelipatan 50 terdekat)',
                        'ROUND_100': 'Rp 14.234 → Rp 14.200 (Kelipatan 100 terdekat)',
                        'ROUND_500': 'Rp 14.234 → Rp 14.500 (Kelipatan 500 terdekat)',
                        'ROUND_1000': 'Rp 14.234 → Rp 14.000 (Kelipatan 1.000 terdekat)'
                    };
                    return samples[strategy] || 'Standar pembulatan sistem';
                },

                // =====================================================
                // Branch Management Modals, GPS & Leaflet Map Integration
                // =====================================================
                branchesList: @json($branches ?? []),
                showCreateBranchModal: @json(($errors->any() && old('name')) || (request('tab') === 'branches' && (request('add') === '1' || request('add') === 'branch' || request('add') === 'outlet'))),
                showEditBranchModal: false,
                deleteBranchModalOpen: false,
                branchDeleteTarget: { id: null, name: '' },
                branchDeleteUrl: '',
                branchEditUrl: '',
                branchSubmitting: false,

                branchForm: {
                    name: @json(old('name', '')),
                    code: @json(old('code', '')),
                    phone: @json(old('phone', '')),
                    address: @json(old('address', '')),
                    province: @json(old('province', '')),
                    city: @json(old('city', '')),
                    district: @json(old('district', '')),
                    village: @json(old('village', '')),
                    postal_code: @json(old('postal_code', '')),
                    biteship_area_id: @json(old('biteship_area_id', '')),
                    latitude: @json(old('latitude', $defaultBranchLat)),
                    longitude: @json(old('longitude', $defaultBranchLng)),
                    geofence_radius_meters: @json((int) old('geofence_radius_meters', 100)),
                    is_primary: @json((bool) old('is_primary', false)),
                    is_online_fulfillment: @json((bool) old('is_online_fulfillment', true)),
                    allow_storefront_pickup: @json((bool) old('allow_storefront_pickup', true))
                },

                branchEditData: {
                    id: null,
                    name: '',
                    code: '',
                    phone: '',
                    address: '',
                    province: '',
                    city: '',
                    district: '',
                    village: '',
                    postal_code: '',
                    biteship_area_id: '',
                    latitude: {{ $defaultBranchLat }},
                    longitude: {{ $defaultBranchLng }},
                    geofence_radius_meters: 100,
                    is_primary: false,
                    is_online_fulfillment: true,
                    allow_storefront_pickup: true,
                    is_active: true
                },

                branchSearchQuery: '',
                branchSearchResults: [],
                branchIsSearching: false,
                branchShowDropdown: false,
                branchGpsLoading: false,

                branchCreateMap: null,
                branchCreateMarker: null,
                branchCreateCircle: null,

                branchEditMap: null,
                branchEditMarker: null,
                branchEditCircle: null,

                getMapCustomMarkerIcon() {
                    return L.divIcon({
                        className: 'custom-map-marker',
                        html: `<div style="background-color: #007AFF; width: 32px; height: 32px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(0,122,255,0.45); border: 2.5px solid white;">
                                 <div style="width: 10px; height: 10px; background-color: white; border-radius: 50%; transform: rotate(45deg);"></div>
                               </div>`,
                        iconSize: [32, 32],
                        iconAnchor: [16, 32]
                    });
                },

                openCreateBranch() {
                    this.branchForm = {
                        name: '',
                        code: '',
                        phone: '',
                        address: '',
                        province: '',
                        city: '',
                        district: '',
                        village: '',
                        postal_code: '',
                        biteship_area_id: '',
                        latitude: {{ $defaultBranchLat }},
                        longitude: {{ $defaultBranchLng }},
                        geofence_radius_meters: 100,
                        is_primary: false,
                        is_online_fulfillment: true,
                        allow_storefront_pickup: true
                    };
                    this.branchSearchQuery = '';
                    this.branchSearchResults = [];
                    this.branchShowDropdown = false;
                    this.branchGpsLoading = false;
                    this.branchSubmitting = false;
                    this.showCreateBranchModal = true;

                    this.$nextTick(() => {
                        setTimeout(() => {
                            this.initBranchCreateMap();
                            if (window.lucide && typeof window.lucide.createIcons === 'function') {
                                window.lucide.createIcons();
                            }
                        }, 250);
                    });
                },

                openEditBranch(branchOrId) {
                    let branch = branchOrId;
                    if (typeof branchOrId === 'string' || typeof branchOrId === 'number') {
                        branch = (this.branchesList || []).find(b => String(b.id) === String(branchOrId));
                    }
                    if (!branch) return;

                    this.branchEditUrl = '{{ url('/settings/branches') }}/' + branch.id;
                    const lat = (branch.latitude !== null && branch.latitude !== undefined && branch.latitude !== '')
                        ? parseFloat(branch.latitude)
                        : {{ $defaultBranchLat }};
                    const lng = (branch.longitude !== null && branch.longitude !== undefined && branch.longitude !== '')
                        ? parseFloat(branch.longitude)
                        : {{ $defaultBranchLng }};
                    const radius = (branch.geofence_radius_meters !== null && branch.geofence_radius_meters !== undefined)
                        ? parseInt(branch.geofence_radius_meters)
                        : 100;

                    this.branchEditData = {
                        id: branch.id,
                        name: branch.name || '',
                        code: branch.code || '',
                        phone: branch.phone || '',
                        address: branch.address || '',
                        province: branch.province || '',
                        city: branch.city || '',
                        district: branch.district || '',
                        village: branch.village || '',
                        postal_code: branch.postal_code || '',
                        biteship_area_id: branch.biteship_area_id || '',
                        latitude: lat,
                        longitude: lng,
                        geofence_radius_meters: radius,
                        is_primary: Boolean(branch.is_primary),
                        is_online_fulfillment: Boolean(branch.is_online_fulfillment !== false && branch.is_online_fulfillment !== 0),
                        allow_storefront_pickup: Boolean(branch.allow_storefront_pickup !== false && branch.allow_storefront_pickup !== 0),
                        is_active: Boolean(branch.is_active !== false && branch.is_active !== 0)
                    };
                    this.branchSearchQuery = '';
                    this.branchSearchResults = [];
                    this.branchShowDropdown = false;
                    this.branchGpsLoading = false;
                    this.branchSubmitting = false;
                    this.showEditBranchModal = true;

                    this.$nextTick(() => {
                        setTimeout(() => {
                            this.initBranchEditMap();
                            if (window.lucide && typeof window.lucide.createIcons === 'function') {
                                window.lucide.createIcons();
                            }
                        }, 250);
                    });
                },

                initBranchCreateMap() {
                    if (typeof L === 'undefined') {
                        setTimeout(() => this.initBranchCreateMap(), 200);
                        return;
                    }
                    const container = document.getElementById('branch-create-map');
                    if (!container) return;

                    const lat = parseFloat(this.branchForm.latitude) || {{ $defaultBranchLat }};
                    const lng = parseFloat(this.branchForm.longitude) || {{ $defaultBranchLng }};
                    const radius = parseInt(this.branchForm.geofence_radius_meters) || 100;

                    if (this.branchCreateMap) {
                        try { this.branchCreateMap.remove(); } catch (e) {}
                        this.branchCreateMap = null;
                        this.branchCreateMarker = null;
                        this.branchCreateCircle = null;
                    }

                    this.branchCreateMap = L.map('branch-create-map', {
                        zoomControl: true,
                        scrollWheelZoom: false
                    }).setView([lat, lng], 15);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(this.branchCreateMap);

                    this.branchCreateMarker = L.marker([lat, lng], {
                        draggable: true,
                        icon: this.getMapCustomMarkerIcon()
                    }).addTo(this.branchCreateMap);

                    this.branchCreateCircle = L.circle([lat, lng], {
                        radius: radius,
                        color: '#007AFF',
                        fillColor: '#007AFF',
                        fillOpacity: 0.15,
                        weight: 2
                    }).addTo(this.branchCreateMap);

                    this.branchCreateMarker.on('dragend', (e) => {
                        const pos = e.target.getLatLng();
                        this.branchForm.latitude = parseFloat(pos.lat.toFixed(7));
                        this.branchForm.longitude = parseFloat(pos.lng.toFixed(7));
                        if (this.branchCreateCircle) {
                            this.branchCreateCircle.setLatLng(pos);
                        }
                        this.reverseGeocodeBranch('create', pos.lat, pos.lng, true);
                    });

                    this.branchCreateMap.on('click', (e) => {
                        this.branchCreateMarker.setLatLng(e.latlng);
                        if (this.branchCreateCircle) {
                            this.branchCreateCircle.setLatLng(e.latlng);
                        }
                        this.branchForm.latitude = parseFloat(e.latlng.lat.toFixed(7));
                        this.branchForm.longitude = parseFloat(e.latlng.lng.toFixed(7));
                        this.reverseGeocodeBranch('create', e.latlng.lat, e.latlng.lng, true);
                    });

                    setTimeout(() => {
                        if (this.branchCreateMap) this.branchCreateMap.invalidateSize();
                    }, 350);
                },

                initBranchEditMap() {
                    if (typeof L === 'undefined') {
                        setTimeout(() => this.initBranchEditMap(), 200);
                        return;
                    }
                    const container = document.getElementById('branch-edit-map');
                    if (!container) return;

                    const lat = parseFloat(this.branchEditData.latitude) || {{ $defaultBranchLat }};
                    const lng = parseFloat(this.branchEditData.longitude) || {{ $defaultBranchLng }};
                    const radius = parseInt(this.branchEditData.geofence_radius_meters) || 100;

                    if (this.branchEditMap) {
                        try { this.branchEditMap.remove(); } catch (e) {}
                        this.branchEditMap = null;
                        this.branchEditMarker = null;
                        this.branchEditCircle = null;
                    }

                    this.branchEditMap = L.map('branch-edit-map', {
                        zoomControl: true,
                        scrollWheelZoom: false
                    }).setView([lat, lng], 15);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(this.branchEditMap);

                    this.branchEditMarker = L.marker([lat, lng], {
                        draggable: true,
                        icon: this.getMapCustomMarkerIcon()
                    }).addTo(this.branchEditMap);

                    this.branchEditCircle = L.circle([lat, lng], {
                        radius: radius,
                        color: '#007AFF',
                        fillColor: '#007AFF',
                        fillOpacity: 0.15,
                        weight: 2
                    }).addTo(this.branchEditMap);

                    this.branchEditMarker.on('dragend', (e) => {
                        const pos = e.target.getLatLng();
                        this.branchEditData.latitude = parseFloat(pos.lat.toFixed(7));
                        this.branchEditData.longitude = parseFloat(pos.lng.toFixed(7));
                        if (this.branchEditCircle) {
                            this.branchEditCircle.setLatLng(pos);
                        }
                        this.reverseGeocodeBranch('edit', pos.lat, pos.lng, true);
                    });

                    this.branchEditMap.on('click', (e) => {
                        this.branchEditMarker.setLatLng(e.latlng);
                        if (this.branchEditCircle) {
                            this.branchEditCircle.setLatLng(e.latlng);
                        }
                        this.branchEditData.latitude = parseFloat(e.latlng.lat.toFixed(7));
                        this.branchEditData.longitude = parseFloat(e.latlng.lng.toFixed(7));
                        this.reverseGeocodeBranch('edit', e.latlng.lat, e.latlng.lng, true);
                    });

                    setTimeout(() => {
                        if (this.branchEditMap) this.branchEditMap.invalidateSize();
                    }, 350);
                },

                updateGeofenceCircle(target) {
                    if (target === 'create') {
                        const radius = parseInt(this.branchForm.geofence_radius_meters) || 100;
                        if (this.branchCreateCircle) {
                            this.branchCreateCircle.setRadius(radius);
                        }
                    } else {
                        const radius = parseInt(this.branchEditData.geofence_radius_meters) || 100;
                        if (this.branchEditCircle) {
                            this.branchEditCircle.setRadius(radius);
                        }
                    }
                },

                async searchBranchAreas(target) {
                    const q = (this.branchSearchQuery || '').trim();
                    if (q.length < 2) {
                        this.branchSearchResults = [];
                        this.branchShowDropdown = false;
                        return;
                    }

                    this.branchIsSearching = true;
                    try {
                        const res = await fetch(`/geo/search-areas?query=${encodeURIComponent(q)}`);
                        const data = await res.json();
                        if (data && data.success && Array.isArray(data.areas)) {
                            this.branchSearchResults = data.areas;
                            this.branchShowDropdown = this.branchSearchResults.length > 0;
                        } else {
                            this.branchSearchResults = [];
                            this.branchShowDropdown = false;
                        }
                    } catch (err) {
                        console.error('Error searching areas:', err);
                        this.branchSearchResults = [];
                    } finally {
                        this.branchIsSearching = false;
                    }
                },

                selectBranchArea(target, area) {
                    let formObj = target === 'create' ? this.branchForm : this.branchEditData;
                    let mapObj = target === 'create' ? this.branchCreateMap : this.branchEditMap;
                    let markerObj = target === 'create' ? this.branchCreateMarker : this.branchEditMarker;
                    let circleObj = target === 'create' ? this.branchCreateCircle : this.branchEditCircle;

                    if (formObj) {
                        formObj.province = area.province || '';
                        formObj.city = area.city || '';
                        formObj.district = area.district || '';
                        formObj.village = area.village || '';
                        formObj.postal_code = area.postal_code || '';
                        formObj.biteship_area_id = area.id || '';
                    }

                    this.branchSearchQuery = '';
                    this.branchShowDropdown = false;

                    if (area.latitude && area.longitude && mapObj && markerObj) {
                        const lat = parseFloat(area.latitude);
                        const lng = parseFloat(area.longitude);
                        formObj.latitude = parseFloat(lat.toFixed(7));
                        formObj.longitude = parseFloat(lng.toFixed(7));
                        markerObj.setLatLng([lat, lng]);
                        if (circleObj) circleObj.setLatLng([lat, lng]);
                        mapObj.flyTo([lat, lng], 15, { duration: 1.2 });
                    } else if (mapObj && markerObj) {
                        const labelQuery = area.label || `${area.district || ''}, ${area.city || ''}, Indonesia`;
                        fetch(`https://nominatim.openstreetmap.org/search?format=json&countrycodes=id&limit=1&q=${encodeURIComponent(labelQuery)}`)
                            .then(r => r.json())
                            .then(geo => {
                                if (geo && geo[0]) {
                                    const lat = parseFloat(geo[0].lat);
                                    const lng = parseFloat(geo[0].lon);
                                    formObj.latitude = parseFloat(lat.toFixed(7));
                                    formObj.longitude = parseFloat(lng.toFixed(7));
                                    markerObj.setLatLng([lat, lng]);
                                    if (circleObj) circleObj.setLatLng([lat, lng]);
                                    mapObj.flyTo([lat, lng], 15, { duration: 1.2 });
                                }
                            })
                            .catch(() => {});
                    }
                },

                async reverseGeocodeBranch(target, lat, lng, forceOverwrite = false) {
                    let formObj = target === 'create' ? this.branchForm : this.branchEditData;
                    if (!formObj) return;

                    try {
                        const res = await fetch(`/geo/reverse-geocode?lat=${lat}&lng=${lng}`);
                        const data = await res.json();
                        if (data && data.success) {
                            if (forceOverwrite || !formObj.province) {
                                if (data.province) formObj.province = data.province;
                            }
                            if (forceOverwrite || !formObj.city) {
                                if (data.city) formObj.city = data.city;
                            }
                            if (forceOverwrite || !formObj.district) {
                                if (data.district) formObj.district = data.district;
                            }
                            if (forceOverwrite || !formObj.village) {
                                if (data.village) formObj.village = data.village;
                            }
                            if (forceOverwrite || !formObj.postal_code) {
                                if (data.postal_code) formObj.postal_code = data.postal_code;
                            }
                            if (data.biteship_area_id) {
                                formObj.biteship_area_id = data.biteship_area_id;
                            }
                            if (data.road && (forceOverwrite || !formObj.address)) {
                                formObj.address = data.road;
                            }
                        }
                    } catch (err) {
                        console.warn('Reverse geocode error:', err);
                    }
                },

                useCurrentBranchGps(target) {
                    if (!navigator.geolocation) {
                        alert('Fitur GPS tidak didukung oleh browser Anda.');
                        return;
                    }

                    this.branchGpsLoading = true;
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            const lat = parseFloat(pos.coords.latitude.toFixed(7));
                            const lng = parseFloat(pos.coords.longitude.toFixed(7));
                            let formObj = target === 'create' ? this.branchForm : this.branchEditData;
                            let mapObj = target === 'create' ? this.branchCreateMap : this.branchEditMap;
                            let markerObj = target === 'create' ? this.branchCreateMarker : this.branchEditMarker;
                            let circleObj = target === 'create' ? this.branchCreateCircle : this.branchEditCircle;

                            if (formObj) {
                                formObj.latitude = lat;
                                formObj.longitude = lng;
                            }
                            if (mapObj && markerObj) {
                                markerObj.setLatLng([lat, lng]);
                                if (circleObj) circleObj.setLatLng([lat, lng]);
                                mapObj.flyTo([lat, lng], 16, { duration: 1.2 });
                            }
                            this.reverseGeocodeBranch(target, lat, lng, true);
                            this.branchGpsLoading = false;
                        },
                        (err) => {
                            this.branchGpsLoading = false;
                            alert('Gagal mengambil lokasi GPS: ' + (err.message || 'Izin akses lokasi ditolak.'));
                        },
                        { enableHighAccuracy: true, timeout: 10000 }
                    );
                },

                openDeleteBranch(id, name = null) {
                    if (!name && this.branchesList) {
                        const branch = this.branchesList.find(b => String(b.id) === String(id));
                        name = branch ? branch.name : '';
                    }
                    this.branchDeleteTarget = { id, name: name || '' };
                    this.branchDeleteUrl = '{{ url('/settings/branches') }}/' + id;
                    this.deleteBranchModalOpen = true;
                },

                closeDeleteBranch() {
                    this.deleteBranchModalOpen = false;
                    this.branchDeleteTarget = { id: null, name: '' };
                },

                submitDeleteBranch() {
                    if (this.$refs.deleteBranchForm) {
                        this.$refs.deleteBranchForm.submit();
                    }
                }
            };
        }
    </script>
@endsection
