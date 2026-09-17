@extends('layouts.admin')
@section('title', 'WhatsApp Admin Center - Pengingat Langganan & Broadcast Bisnis')

@section('content')
    @php
        $validTabs = ['parent_setup', 'reminders', 'blast', 'templates', 'merchants'];
        $initialTab = in_array($tab, $validTabs, true) ? $tab : 'parent_setup';
        $initialPhone = $waStatus['phone'] ?? '';
        $totalDueCount = $dueData['stats']['total_due'] ?? 0;
        $pendingRemindersCount = $dueData['stats']['total_pending'] ?? 0;
        $sentRemindersCount = $dueData['stats']['total_sent'] ?? 0;
        $totalOwnersCount = $targetCounts['all_owners'] ?? 0;
        $totalBlastSent = $blasts->sum('total_sent');
        $totalBlastRecipients = $blasts->sum('total_recipients');
        $overallSuccessRate =
            $totalBlastRecipients + $totalDueCount > 0
                ? round(
                    (($totalBlastSent + $sentRemindersCount) / max(1, $totalBlastRecipients + $totalDueCount)) * 100,
                )
                : 100;
        $activeMerchantsCount = $merchantSummary['active'] ?? 0;
        $totalMerchantsCount = $merchantSummary['total'] ?? 0;
    @endphp

    <div class="space-y-5 sm:space-y-6 max-w-7xl w-full min-w-0 mx-auto pb-28 lg:pb-10" x-data="adminWaCenter()" x-init="init()">

        {{-- 1. BENTO HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 sm:gap-4 min-w-0">
            <div class="flex items-center gap-3 sm:gap-3.5 min-w-0 flex-1">
                <div
                    class="w-10 h-10 sm:w-12 sm:h-12 rounded-[14px] sm:rounded-[18px] bg-gradient-to-br from-[#1877F2] to-[#007AFF] flex items-center justify-center shadow-md shadow-[#1877F2]/20 shrink-0 text-white">
                    <i data-lucide="shield-check" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h1 class="text-[18px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight truncate">WhatsApp Platform Admin Center</h1>
                    <p class="text-[12px] sm:text-[13px] text-black/50 dark:text-white/50 mt-0.5 truncate">Pusat konfigurasi resmi Meta Tech Provider, gateway OTP keamanan, pengingat langganan, dan pengawasan merchant</p>
                </div>
            </div>

            {{-- Live Status Indicator & Quick Setup Button --}}
            <div class="grid grid-cols-1 xs:grid-cols-2 sm:flex items-center gap-2 sm:gap-3 w-full sm:w-auto min-w-0">
                <template x-if="status === 'connected'">
                    <div
                        class="flex items-center justify-center gap-2 px-3 py-2 sm:px-3.5 sm:py-2.5 rounded-[12px] bg-[#34C759]/12 border border-[#34C759]/25 text-[#248A3D] dark:text-[#30D158] text-[12px] font-semibold min-h-[40px] sm:min-h-[44px] min-w-0">
                        <span class="w-2 h-2 rounded-full bg-[#34C759] shrink-0"></span>
                        <span class="truncate">Meta Resmi Aktif</span>
                        <span class="text-black/30 dark:text-white/30">|</span>
                        <span class="font-mono truncate" x-text="phone ? '+' + phone : 'Online'"></span>
                    </div>
                </template>
                <template x-if="status !== 'connected'">
                    <div
                        class="flex items-center justify-center gap-2 px-3 py-2 sm:px-3.5 sm:py-2.5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] border border-black/10 dark:border-white/10 text-black/55 dark:text-white/55 text-[12px] font-semibold min-h-[40px] sm:min-h-[44px] min-w-0">
                        <span class="w-2 h-2 rounded-full bg-[#FF9500] shrink-0"></span>
                        <span class="truncate">Meta Belum Terkonfigurasi</span>
                    </div>
                </template>

                <button type="button" @click="setTab('parent_setup')"
                    class="min-h-[40px] sm:min-h-[44px] px-4 rounded-[12px] text-[12.5px] sm:text-[13px] font-semibold bg-[#1877F2] text-white hover:bg-[#166FE5] active:scale-[0.98] shadow-sm transition-all inline-flex items-center justify-center gap-2 min-w-0">
                    <i data-lucide="sliders-horizontal" class="w-4 h-4 shrink-0"></i>
                    <span class="truncate">Setup Platform Meta</span>
                </button>
            </div>
        </div>

        {{-- NOTIFICATIONS / ALERTS --}}
        @if ($errors->any())
            <div
                class="rounded-[18px] p-4 bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#C41E17] dark:text-[#FF453A] text-[13px] font-medium shadow-sm">
                <div class="flex items-center gap-2 mb-1.5 font-bold">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span>Periksa kembali isian formulir:</span>
                </div>
                <ul class="list-disc list-inside space-y-1 text-[12px] opacity-90 pl-1">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- 2. BENTO HERO KPI TILES (ADAPTIVE 2-COLUMN MOBILE, 4-COLUMN DESKTOP) --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4 w-full min-w-0">
            {{-- Tile 1: Status Gateway Platform --}}
            <div
                class="rounded-[18px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-3.5 sm:p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between min-w-0">
                <div class="flex items-center justify-between gap-2 min-w-0">
                    <span
                        class="text-[11px] sm:text-[12px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider truncate">Bot Platform</span>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-[9px] sm:rounded-[10px] flex items-center justify-center shrink-0"
                        :class="status === 'connected' ? 'bg-[#34C759]/15 text-[#34C759]' : 'bg-black/5 dark:bg-white/10 text-black/40 dark:text-white/40'">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                    </div>
                </div>
                <div class="mt-2.5 min-w-0">
                    <div
                        class="text-[18px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight flex items-center gap-1.5 truncate">
                        <span x-text="status === 'connected' ? 'Meta Resmi' : 'Offline'"></span>
                    </div>
                    <p class="text-[11px] sm:text-[11.5px] text-black/50 dark:text-white/50 mt-0.5 font-mono truncate"
                        x-text="phone ? '+' + phone : 'Kredensial Belum Lengkap'"></p>
                </div>
            </div>

            {{-- Tile 2: Merchant Terhubung --}}
            <div
                class="rounded-[18px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-3.5 sm:p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between min-w-0">
                <div class="flex items-center justify-between gap-2 min-w-0">
                    <span
                        class="text-[11px] sm:text-[12px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider truncate">Merchant WABA</span>
                    <div
                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-[9px] sm:rounded-[10px] bg-[#1877F2]/15 text-[#1877F2] flex items-center justify-center shrink-0">
                        <i data-lucide="store" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                    </div>
                </div>
                <div class="mt-2.5 min-w-0">
                    <div
                        class="text-[18px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight tabular-nums truncate">
                        {{ $activeMerchantsCount }} <span
                            class="text-[11px] sm:text-[12px] font-medium text-black/45 dark:text-white/45">aktif</span>
                    </div>
                    <p class="text-[11px] sm:text-[11.5px] text-black/50 dark:text-white/50 mt-0.5 truncate tabular-nums">Total {{ $totalMerchantsCount }} toko terdaftar</p>
                </div>
            </div>

            {{-- Tile 3: Pengingat Hari Ini --}}
            <div
                class="rounded-[18px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-3.5 sm:p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between min-w-0">
                <div class="flex items-center justify-between gap-2 min-w-0">
                    <span
                        class="text-[11px] sm:text-[12px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider truncate">Pengingat Tagihan</span>
                    <div
                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-[9px] sm:rounded-[10px] bg-[#FF9500]/15 text-[#FF9500] flex items-center justify-center shrink-0">
                        <i data-lucide="bell" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                    </div>
                </div>
                <div class="mt-2.5 min-w-0">
                    <div
                        class="text-[18px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight tabular-nums truncate">
                        {{ $pendingRemindersCount }} <span
                            class="text-[11px] sm:text-[12px] font-medium text-black/45 dark:text-white/45">pending</span>
                    </div>
                    <p class="text-[11px] sm:text-[11.5px] text-black/50 dark:text-white/50 mt-0.5 truncate tabular-nums">Total
                        {{ $totalDueCount }} langganan</p>
                </div>
            </div>

            {{-- Tile 4: Keberhasilan Kirim --}}
            <div
                class="rounded-[18px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-3.5 sm:p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between min-w-0">
                <div class="flex items-center justify-between gap-2 min-w-0">
                    <span
                        class="text-[11px] sm:text-[12px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider truncate">Keberhasilan Kirim</span>
                    <div
                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-[9px] sm:rounded-[10px] bg-[#34C759]/15 text-[#34C759] flex items-center justify-center shrink-0">
                        <i data-lucide="check-check" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                    </div>
                </div>
                <div class="mt-2.5 min-w-0">
                    <div
                        class="text-[18px] sm:text-[24px] font-bold text-[#248A3D] dark:text-[#30D158] tracking-tight tabular-nums truncate">
                        {{ $overallSuccessRate }}%
                    </div>
                    <p class="text-[11px] sm:text-[11.5px] text-black/50 dark:text-white/50 mt-0.5 truncate tabular-nums">
                        {{ number_format($sentRemindersCount + $totalBlastSent) }} pesan terkirim</p>
                </div>
            </div>
        </div>

        {{-- 3. PERSISTENT SEGMENTED CONTROL (APPLE HIG STYLE) --}}
        <div class="w-full max-w-full min-w-0 overflow-hidden">
            <div
                class="p-1.5 bg-black/[0.05] dark:bg-white/[0.07] rounded-[16px] flex items-center gap-1.5 overflow-x-auto shadow-inner no-scrollbar">
                <button type="button" @click="setTab('parent_setup')"
                    :class="activeTab === 'parent_setup' ?
                        'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-semibold' :
                        'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                    class="min-h-[42px] sm:min-h-[44px] px-3.5 sm:px-5 rounded-[12px] text-[12.5px] sm:text-[13px] transition-all flex items-center gap-2 sm:gap-2.5 shrink-0 whitespace-nowrap active:scale-[0.98]">
                    <i data-lucide="shield-check" class="w-4 h-4 text-[#1877F2]"></i>
                    <span>Pengaturan Platform Meta</span>
                    <template x-if="status === 'connected'">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                    </template>
                </button>

                <button type="button" @click="setTab('merchants')"
                    :class="activeTab === 'merchants' ?
                        'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-semibold' :
                        'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                    class="min-h-[42px] sm:min-h-[44px] px-3.5 sm:px-5 rounded-[12px] text-[12.5px] sm:text-[13px] transition-all flex items-center gap-2 sm:gap-2.5 shrink-0 whitespace-nowrap active:scale-[0.98]">
                    <i data-lucide="store" class="w-4 h-4 text-[#34C759]"></i>
                    <span>Monitoring Merchant</span>
                    @if ($activeMerchantsCount > 0)
                        <span class="px-2 py-0.5 rounded-full bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] text-[11px] font-bold tabular-nums">
                            {{ $activeMerchantsCount }}
                        </span>
                    @endif
                </button>

                <button type="button" @click="setTab('reminders')"
                    :class="activeTab === 'reminders' ?
                        'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-semibold' :
                        'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                    class="min-h-[42px] sm:min-h-[44px] px-3.5 sm:px-5 rounded-[12px] text-[12.5px] sm:text-[13px] transition-all flex items-center gap-2 sm:gap-2.5 shrink-0 whitespace-nowrap active:scale-[0.98]">
                    <i data-lucide="bell" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Pengingat Langganan</span>
                    @if ($pendingRemindersCount > 0)
                        <span class="px-2 py-0.5 rounded-full bg-[#FF3B30] text-white text-[11px] font-bold tabular-nums">
                            {{ $pendingRemindersCount }}
                        </span>
                    @endif
                </button>

                <button type="button" @click="setTab('blast')"
                    :class="activeTab === 'blast' ?
                        'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-semibold' :
                        'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                    class="min-h-[42px] sm:min-h-[44px] px-3.5 sm:px-5 rounded-[12px] text-[12.5px] sm:text-[13px] transition-all flex items-center gap-2 sm:gap-2.5 shrink-0 whitespace-nowrap active:scale-[0.98]">
                    <i data-lucide="send" class="w-4 h-4 text-[#FF9500]"></i>
                    <span>Siaran Platform</span>
                </button>

                <button type="button" @click="setTab('templates')"
                    :class="activeTab === 'templates' ?
                        'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-semibold' :
                        'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                    class="min-h-[42px] sm:min-h-[44px] px-3.5 sm:px-5 rounded-[12px] text-[12.5px] sm:text-[13px] transition-all flex items-center gap-2 sm:gap-2.5 shrink-0 whitespace-nowrap active:scale-[0.98]">
                    <i data-lucide="file-text" class="w-4 h-4 text-[#AF52DE]"></i>
                    <span>Template Notifikasi</span>
                </button>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TAB 1: PENGATURAN PLATFORM META (PARENT SETUP)                            --}}
        {{-- ========================================================================= --}}
        <div x-show="activeTab === 'parent_setup'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
            class="space-y-6">

            {{-- 1. HERO CARD: LIVE STATUS BOT PLATFORM META --}}
            <div
                class="rounded-[22px] sm:rounded-[24px] bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-md border border-black/[0.08] dark:border-white/[0.08] shadow-sm p-5 sm:p-7 space-y-6 w-full min-w-0">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-5 pb-5 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div class="flex items-start gap-4 min-w-0 flex-1">
                        <div
                            class="w-12 h-12 sm:w-14 sm:h-14 rounded-[18px] bg-gradient-to-br from-[#1877F2] to-[#007AFF] text-white flex items-center justify-center shadow-lg shadow-[#1877F2]/25 shrink-0">
                            <i data-lucide="shield-check" class="w-6 h-6 sm:w-7 sm:h-7"></i>
                        </div>
                        <div class="min-w-0 flex-1 space-y-1">
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <h2 class="text-[17px] sm:text-[19px] font-bold text-black dark:text-white tracking-tight">
                                    Bot WhatsApp Platform Cooca
                                </h2>
                                <template x-if="status === 'connected'">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11.5px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                        <span>Aktif Terhubung</span>
                                    </span>
                                </template>
                                <template x-if="status !== 'connected'">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11.5px] font-bold bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A] border border-[#FF9500]/25">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span>
                                        <span>Belum Dikonfigurasi</span>
                                    </span>
                                </template>
                            </div>
                            <p class="text-[12.5px] text-black/60 dark:text-white/60 leading-relaxed max-w-2xl">
                                Kanal resmi Meta WhatsApp Cloud API (Graph API v21.0) tingkat induk (Parent). Digunakan untuk pengiriman OTP otentikasi login, reset PIN, verifikasi akun, dan pengingat tagihan langganan SaaS.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 shrink-0 self-stretch sm:self-auto">
                        <button type="button" @click="checkStatus()" :disabled="isLoading"
                            class="min-h-[44px] px-4 rounded-[12px] text-[12.5px] font-bold bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white active:scale-[0.98] transition-all inline-flex items-center justify-center gap-2 w-full sm:w-auto">
                            <i data-lucide="refresh-cw" class="w-4 h-4" :class="isLoading ? 'animate-spin' : ''"></i>
                            <span>Cek Status Meta</span>
                        </button>
                    </div>
                </div>

                {{-- Status Pills Grid --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                    <div class="p-3.5 sm:p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider block">Nama Akun Bisnis</span>
                        <div class="text-[13.5px] sm:text-[14px] font-bold text-black dark:text-white truncate" x-text="metaVerifyResult?.verified_name || '{{ $waStatus['verified_name'] ?? 'Cooca Platform' }}'">
                            {{ $waStatus['verified_name'] ?? 'Cooca Platform' }}
                        </div>
                    </div>

                    <div class="p-3.5 sm:p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider block">Nomor Telepon Bot</span>
                        <div class="text-[13.5px] sm:text-[14px] font-bold font-mono text-black dark:text-white truncate" x-text="phone ? '+' + phone : '{{ $waStatus['display_phone'] ?? '-' }}'">
                            {{ $waStatus['display_phone'] ?? '-' }}
                        </div>
                    </div>

                    <div class="p-3.5 sm:p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider block">Kualitas Nomor</span>
                        <div class="text-[13.5px] sm:text-[14px] font-bold text-[#248A3D] dark:text-[#30D158] flex items-center gap-1.5 truncate">
                            <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                            <span x-text="metaVerifyResult?.quality_rating || '{{ $waStatus['quality_rating'] ?? 'GREEN' }}'">
                                {{ $waStatus['quality_rating'] ?? 'GREEN' }}
                            </span>
                        </div>
                    </div>

                    <div class="p-3.5 sm:p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider block">Limit Kuota Harian</span>
                        <div class="text-[13.5px] sm:text-[14px] font-bold text-black dark:text-white truncate" x-text="metaVerifyResult?.messaging_tier || '{{ $waStatus['messaging_tier'] ?? 'TIER_1K' }}'">
                            {{ $waStatus['messaging_tier'] ?? 'TIER_1K' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- FORM PENGATURAN PLATFORM & KREDENSIAL META WHATSAPP --}}
            <form method="POST" action="{{ route('admin.whatsapp.config') }}" class="space-y-6">
                @csrf

                {{-- 2. META TECH PROVIDER & EMBEDDED SIGNUP (OFFICIAL INTEGRATION) --}}
                <div
                    class="rounded-[22px] sm:rounded-[24px] bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-md border border-black/[0.08] dark:border-white/[0.08] shadow-sm p-5 sm:p-7 space-y-5 w-full min-w-0">
                    <div class="flex items-center gap-3.5 pb-4 border-b border-black/[0.06] dark:border-white/[0.08]">
                        <div
                            class="w-10 h-10 sm:w-11 sm:h-11 rounded-[14px] bg-[#5856D6]/15 text-[#5856D6] flex items-center justify-center shrink-0">
                            <i data-lucide="webhook" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-[16px] sm:text-[17px] font-bold text-black dark:text-white tracking-tight">
                                Meta Tech Provider &amp; Webhook Terpadu
                            </h3>
                            <p class="text-[12px] sm:text-[12.5px] text-black/55 dark:text-white/55 mt-0.5">
                                Konfigurasi Meta App untuk Embedded Signup merchant dan endpoint penerimaan webhook status pesan
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                        {{-- Meta App ID --}}
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55">
                                    Meta App ID (META_WA_APP_ID)
                                </label>
                                <span class="text-[11px] text-black/40 dark:text-white/40 font-medium">Meta Developers Portal</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="text" name="meta_app_id" x-model="metaAppId"
                                    placeholder="Contoh: 104829384729182"
                                    class="w-full min-h-[46px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] px-3.5 text-[16px] sm:text-[13px] font-mono text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all">
                                <template x-if="metaAppId">
                                    <button type="button" @click="copyToClipboard(metaAppId, 'App ID')"
                                        class="px-3 min-h-[46px] rounded-[12px] bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70 hover:bg-black/10 text-[11.5px] font-medium shrink-0 inline-flex items-center gap-1.5 transition-all">
                                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                        <span class="hidden sm:inline">Salin</span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Meta App Secret --}}
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55">
                                    Meta App Secret (META_WA_APP_SECRET)
                                </label>
                                <span class="text-[11px] text-[#248A3D] dark:text-[#30D158] font-bold">Auto-Encrypted</span>
                            </div>
                            <div class="relative">
                                <input :type="showMetaAppSecret ? 'text' : 'password'" name="meta_app_secret"
                                    x-model="metaAppSecret" placeholder="App Secret dari App Settings > Basic"
                                    class="w-full min-h-[46px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] px-3.5 pr-10 text-[16px] sm:text-[13px] font-mono text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all">
                                <button type="button" @click="showMetaAppSecret = !showMetaAppSecret"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">
                                    <i data-lucide="eye" class="w-4 h-4" x-show="!showMetaAppSecret"></i>
                                    <i data-lucide="eye-off" class="w-4 h-4" x-show="showMetaAppSecret"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Embedded Signup Config ID --}}
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55">
                                    Embedded Signup Config ID (META_WA_CONFIG_ID)
                                </label>
                                <span class="text-[11px] text-black/40 dark:text-white/40 font-medium">WhatsApp Login Config</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="text" name="meta_config_id" x-model="metaConfigId"
                                    placeholder="Contoh: 893472910482938"
                                    class="w-full min-h-[46px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] px-3.5 text-[16px] sm:text-[13px] font-mono text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all">
                                <template x-if="metaConfigId">
                                    <button type="button" @click="copyToClipboard(metaConfigId, 'Config ID')"
                                        class="px-3 min-h-[46px] rounded-[12px] bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70 hover:bg-black/10 text-[11.5px] font-medium shrink-0 inline-flex items-center gap-1.5 transition-all">
                                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                        <span class="hidden sm:inline">Salin</span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Webhook Verify Token --}}
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55">
                                    Webhook Verify Token (META_WA_WEBHOOK_VERIFY_TOKEN)
                                </label>
                                <span class="text-[11px] text-black/40 dark:text-white/40 font-medium">Custom Secret String</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="text" name="meta_webhook_verify_token" x-model="metaWebhookVerifyToken"
                                    placeholder="cooca_meta_wa_webhook_secret"
                                    class="w-full min-h-[46px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] px-3.5 text-[16px] sm:text-[13px] font-mono text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all">
                                <template x-if="metaWebhookVerifyToken">
                                    <button type="button" @click="copyToClipboard(metaWebhookVerifyToken, 'Verify Token')"
                                        class="px-3 min-h-[46px] rounded-[12px] bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70 hover:bg-black/10 text-[11.5px] font-medium shrink-0 inline-flex items-center gap-1.5 transition-all">
                                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                        <span class="hidden sm:inline">Salin</span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Graph API Version --}}
                        <div class="space-y-1.5">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55">
                                Graph API Version (META_WA_GRAPH_VERSION)
                            </label>
                            <input type="text" name="meta_graph_version" x-model="metaGraphVersion"
                                placeholder="v21.0"
                                class="w-full min-h-[46px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] px-3.5 text-[16px] sm:text-[13px] font-mono text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all">
                        </div>

                        {{-- Graph API Base URL --}}
                        <div class="space-y-1.5">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55">
                                Graph API Base URL (META_WA_GRAPH_URL)
                            </label>
                            <input type="text" name="meta_graph_url" x-model="metaGraphUrl"
                                placeholder="https://graph.facebook.com"
                                class="w-full min-h-[46px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] px-3.5 text-[16px] sm:text-[13px] font-mono text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all">
                        </div>

                        {{-- Webhook Callback URL (Readonly Endpoint) --}}
                        <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06] space-y-2 md:col-span-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55">Webhook Callback URL (Meta Webhook Endpoint)</span>
                                <span class="text-[11px] text-[#248A3D] dark:text-[#30D158] font-bold">HMAC-SHA256 Verified</span>
                            </div>
                            <div class="flex items-center justify-between gap-2 bg-white dark:bg-[#2C2C2E] p-2.5 rounded-[12px] border border-black/10 dark:border-white/10">
                                <code class="text-[12px] sm:text-[12.5px] font-mono text-black dark:text-white break-all">{{ $platformApp['webhook_url'] }}</code>
                                <button type="button" @click="copyToClipboard('{{ $platformApp['webhook_url'] }}', 'Webhook URL')"
                                    class="px-3 py-1.5 rounded-[10px] bg-[#007AFF] text-white hover:bg-[#0071E3] text-[12px] font-bold shrink-0 inline-flex items-center gap-1.5 active:scale-[0.98] transition-all">
                                    <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                    <span>Salin URL</span>
                                </button>
                            </div>
                            <p class="text-[11px] text-black/45 dark:text-white/45">
                                Tempelkan URL di atas pada dashboard Meta App di menu <strong>WhatsApp &gt; Configuration &gt; Callback URL</strong>, lalu isi <strong>Verify Token</strong> sesuai nilai di atas.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- 3. KREDENSIAL BOT INDUK PLATFORM META (PARENT CREDENTIALS) --}}
                <div
                    class="rounded-[22px] sm:rounded-[24px] bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-md border border-black/[0.08] dark:border-white/[0.08] shadow-sm p-5 sm:p-7 space-y-6 w-full min-w-0">
                    <div class="flex items-center gap-3.5 pb-4 border-b border-black/[0.06] dark:border-white/[0.08]">
                        <div
                            class="w-10 h-10 sm:w-11 sm:h-11 rounded-[14px] bg-[#1877F2]/15 text-[#1877F2] flex items-center justify-center shrink-0">
                            <i data-lucide="key" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-[16px] sm:text-[17px] font-bold text-black dark:text-white tracking-tight">
                                Kredensial Bot Induk Platform Meta
                            </h3>
                            <p class="text-[12px] sm:text-[12.5px] text-black/55 dark:text-white/55 mt-0.5">
                                Kredensial System User Token resmi untuk eksekusi API otentikasi OTP dan pengingat tagihan langganan SaaS
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                        {{-- Meta Permanent Access Token --}}
                        <div class="space-y-1.5 md:col-span-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55">
                                    Meta System User Permanent Access Token (META_WA_TOKEN)
                                </label>
                                <span class="text-[11px] text-[#248A3D] dark:text-[#30D158] font-bold">Auto-Encrypted</span>
                            </div>
                            <div class="relative">
                                <input :type="showMetaToken ? 'text' : 'password'" name="meta_token"
                                    x-model="metaToken" placeholder="EAAG... (System User Token Meta Graph API)"
                                    class="w-full min-h-[46px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] px-3.5 pr-10 text-[16px] sm:text-[13px] font-mono text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all">
                                <button type="button" @click="showMetaToken = !showMetaToken"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">
                                    <i data-lucide="eye" class="w-4 h-4" x-show="!showMetaToken"></i>
                                    <i data-lucide="eye-off" class="w-4 h-4" x-show="showMetaToken"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Phone Number ID --}}
                        <div class="space-y-1.5">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55">
                                Phone Number ID (META_WA_PHONE_NUMBER_ID)
                            </label>
                            <input type="text" name="meta_phone_number_id" x-model="metaPhoneId"
                                placeholder="Contoh: 104523984712398"
                                class="w-full min-h-[46px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] px-3.5 text-[16px] sm:text-[13px] font-mono text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all">
                        </div>

                        {{-- WABA ID --}}
                        <div class="space-y-1.5">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55">
                                WhatsApp Business Account ID / WABA ID (META_WA_WABA_ID)
                            </label>
                            <input type="text" name="meta_waba_id" x-model="metaWabaId"
                                placeholder="Contoh: 109283746501928"
                                class="w-full min-h-[46px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] px-3.5 text-[16px] sm:text-[13px] font-mono text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all">
                        </div>

                        {{-- Template OTP --}}
                        <div class="space-y-1.5">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55">
                                Nama Template OTP Meta (META_WA_OTP_TEMPLATE)
                            </label>
                            <input type="text" name="meta_otp_template"
                                value="{{ $metaCreds['otp_template'] ?: 'cooca_otp' }}" placeholder="cooca_otp"
                                class="w-full min-h-[46px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] px-3.5 text-[16px] sm:text-[13px] font-mono text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all">
                        </div>

                        {{-- Free Tier Info Box --}}
                        <div class="flex items-center">
                            <div
                                class="p-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.05] text-[11.5px] text-black/65 dark:text-white/65 leading-relaxed w-full flex items-start gap-2.5">
                                <i data-lucide="sparkles" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"></i>
                                <span><strong>Kuota Gratis Meta:</strong> Setiap akun WABA menerima 1.000 Service Conversation gratis per bulan langsung dari Meta.</span>
                            </div>
                        </div>

                        {{-- Verification Action Block --}}
                        <div class="md:col-span-2 pt-1">
                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-[14px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 shadow-sm">
                                <div>
                                    <div class="text-[13px] font-bold text-black dark:text-white">Uji &amp; Verifikasi Token Meta Graph API</div>
                                    <div class="text-[11.5px] text-black/55 dark:text-white/55">Hubungi server Meta secara langsung untuk memvalidasi token dan mengecek status nomor telepon</div>
                                </div>
                                <button type="button" @click="verifyMetaCredentials()" :disabled="metaVerifyLoading"
                                    class="min-h-[40px] px-4 rounded-[10px] text-[12px] font-bold bg-[#007AFF]/12 hover:bg-[#007AFF]/20 text-[#007AFF] active:scale-[0.98] transition-all inline-flex items-center justify-center gap-1.5 shrink-0 disabled:opacity-50">
                                    <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin" x-show="metaVerifyLoading"></i>
                                    <i data-lucide="shield-check" class="w-3.5 h-3.5" x-show="!metaVerifyLoading"></i>
                                    <span x-text="metaVerifyLoading ? 'Memeriksa ke Meta...' : 'Uji Validitas Token Meta'"></span>
                                </button>
                            </div>

                            {{-- Verification Success Card --}}
                            <div x-show="metaVerifyResult" x-transition
                                class="mt-3 p-4 rounded-[14px] bg-[#34C759]/12 border border-[#34C759]/30 text-[12.5px] text-[#248A3D] dark:text-[#30D158] flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                                <div class="flex items-center gap-2.5">
                                    <div
                                        class="w-8 h-8 rounded-full bg-[#34C759] text-white flex items-center justify-center shrink-0">
                                        <i data-lucide="check" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-[13px]">Kredensial Meta Graph API Valid &amp; Siap Digunakan</div>
                                        <div class="text-[11.5px] opacity-85">
                                            Nama Bisnis: <strong x-text="metaVerifyResult?.verified_name || 'Terverifikasi'"></strong> |
                                            No: <span class="font-mono" x-text="metaVerifyResult?.display_phone_number || metaPhoneId"></span>
                                        </div>
                                    </div>
                                </div>
                                <span
                                    class="text-[11px] font-semibold text-[#248A3D] dark:text-[#30D158] self-start sm:self-center uppercase tracking-wider"
                                    x-text="'Kualitas: ' + (metaVerifyResult?.quality_rating || 'GREEN')"></span>
                            </div>

                            {{-- Verification Error Card --}}
                            <div x-show="metaVerifyError" x-transition
                                class="mt-3 p-4 rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[12.5px] text-[#C41E17] dark:text-[#FF453A] flex items-start gap-2.5">
                                <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0 mt-0.5"></i>
                                <div>
                                    <div class="font-bold text-[13px]">Verifikasi Kredensial Meta Gagal:</div>
                                    <div class="text-[12px] opacity-90 mt-0.5" x-text="metaVerifyError"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <button type="submit"
                            class="min-h-[48px] px-8 rounded-[14px] text-[13.5px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] shadow-sm transition-all inline-flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Simpan Konfigurasi &amp; Kredensial Platform</span>
                        </button>
                    </div>
                </div>
            </form>

            {{-- 4. CARD UJI KIRIM PESAN (LIVE DIAGNOSTIC) --}}
            <div
                class="rounded-[22px] sm:rounded-[24px] bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-md border border-black/[0.08] dark:border-white/[0.08] shadow-sm p-5 sm:p-7 space-y-5 w-full min-w-0">
                <div class="flex items-center gap-3.5 pb-4 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div
                        class="w-10 h-10 sm:w-11 sm:h-11 rounded-[14px] bg-[#34C759]/15 text-[#34C759] flex items-center justify-center shrink-0">
                        <i data-lucide="send" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] sm:text-[17px] font-bold text-black dark:text-white tracking-tight">
                            Uji Kirim Pesan Langsung (Live Diagnostic)
                        </h3>
                        <p class="text-[12px] sm:text-[12.5px] text-black/55 dark:text-white/55 mt-0.5">
                            Kirim pesan uji coba langsung melalui Bot Meta WhatsApp Cloud API resmi untuk memvalidasi respon gateway
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                    <div>
                        <label
                            class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 mb-1.5">Nomor Ponsel Tujuan</label>
                        <input x-model="testPhone" type="tel"
                            placeholder="Contoh: 081234567890 atau 6281234567890"
                            class="w-full min-h-[46px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#34C759]/50 transition-all">
                    </div>

                    <div>
                        <label
                            class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 mb-1.5">Isi Pesan Uji Coba</label>
                        <textarea x-model="testMessage" rows="2" placeholder="Pesan tes..."
                            class="w-full bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] p-3 text-[16px] sm:text-[13px] text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#34C759]/50 transition-all resize-none"></textarea>
                    </div>

                    <div class="md:col-span-2 flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
                        <div class="flex-1 min-w-0">
                            <div x-show="testResult" class="p-3.5 rounded-[12px] text-[12px] font-medium"
                                :class="testOk ?
                                    'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20' :
                                    'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A] border border-[#FF3B30]/20'"
                                x-text="testResult"></div>
                        </div>
                        <button type="button" @click="sendTest()"
                            :disabled="testLoading || !testPhone.trim() || !testMessage.trim()"
                            class="min-h-[44px] px-6 rounded-[12px] text-[13px] font-bold text-white bg-[#34C759] hover:bg-[#2DB34F] disabled:opacity-50 disabled:pointer-events-none active:scale-[0.98] transition-all inline-flex items-center justify-center gap-2 shadow-sm shrink-0">
                            <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="testLoading"></i>
                            <i data-lucide="send" class="w-4 h-4" x-show="!testLoading"></i>
                            <span x-text="testLoading ? 'Mengirim pesan tes...' : 'Kirim Pesan Tes'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TAB 2: PENGINGAT LANGGANAN (H-7, H-3, H-1, HARI H)                        --}}
        {{-- ========================================================================= --}}
        <div x-show="activeTab === 'reminders'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
            class="space-y-6 w-full min-w-0">

            {{-- Action Bar: Kirim Semua Pengingat --}}
            <div
                class="p-4 sm:p-6 rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-md border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 w-full min-w-0">
                <div class="min-w-0 flex-1">
                    <h2 class="text-[17px] font-bold text-black dark:text-white">Pengingat Masa Aktif Langganan</h2>
                    <p class="text-[12px] sm:text-[13px] text-black/55 dark:text-white/55 mt-0.5">Sistem mengirimkan invoice perpanjangan secara bertahap pada periode H-7, H-3, H-1, dan Hari H</p>
                </div>
                <div class="flex items-center gap-3">
                    <form method="POST" action="{{ route('admin.whatsapp.reminders.send-all') }}"
                        onsubmit="return confirm('Apakah Anda yakin ingin mengirimkan SEMUA pengingat langganan yang masih pending ke WhatsApp pemilik bisnis hari ini?');">
                        @csrf
                        <button type="submit" :disabled="status !== 'connected'"
                            class="min-h-[48px] px-6 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] disabled:opacity-50 disabled:pointer-events-none text-white text-[13px] font-bold inline-flex items-center gap-2 shadow-sm transition-all active:scale-[0.98]">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            <span>Kirim Semua ({{ $pendingRemindersCount }})</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- 4 Periode Bento Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                @php
                    $periodConfigs = [
                        'h-7' => [
                            'label' => 'Periode H-7 (Peringatan Awal)',
                            'badge' => 'bg-[#007AFF]/15 text-[#007AFF]',
                        ],
                        'h-3' => [
                            'label' => 'Periode H-3 (Tindak Lanjut)',
                            'badge' => 'bg-[#FF9500]/15 text-[#FF9500]',
                        ],
                        'h-1' => [
                            'label' => 'Periode H-1 (Penting / Esok Hari)',
                            'badge' => 'bg-[#AF52DE]/15 text-[#AF52DE]',
                        ],
                        'hari_h' => [
                            'label' => 'Hari H (Jatuh Tempo Hari Ini)',
                            'badge' => 'bg-[#FF3B30]/15 text-[#FF3B30]',
                        ],
                    ];
                @endphp

                @foreach ($periodConfigs as $periodKey => $cfg)
                    <div
                        class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-md border border-black/[0.06] dark:border-white/[0.08] shadow-sm overflow-hidden flex flex-col justify-between">
                        <div>
                            <div
                                class="px-5 py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <span
                                        class="font-mono text-[11px] font-bold tracking-wider text-black/50 dark:text-white/50 uppercase">
                                        {{ strtoupper($periodKey) }}
                                    </span>
                                    <h3 class="text-[14px] font-bold text-black dark:text-white">{{ $cfg['label'] }}</h3>
                                </div>
                                <span class="text-[12px] font-semibold text-black/45 dark:text-white/45 tabular-nums">
                                    {{ count($dueData[$periodKey] ?? []) }} Bisnis
                                </span>
                            </div>

                            @if (!empty($dueData[$periodKey]) && count($dueData[$periodKey]) > 0)
                                <div
                                    class="divide-y divide-black/[0.04] dark:divide-white/[0.06] max-h-72 overflow-y-auto">
                                    @foreach ($dueData[$periodKey] as $item)
                                        <div
                                            class="p-4 flex items-center justify-between gap-3 hover:bg-black/[0.01] dark:hover:bg-white/[0.02] transition-colors">
                                            <div class="min-w-0 flex-1">
                                                <div class="font-bold text-[13px] text-black dark:text-white truncate">
                                                    {{ $item['business']->name ?? 'Bisnis' }}
                                                </div>
                                                <div
                                                    class="text-[11px] text-black/55 dark:text-white/55 flex items-center gap-1.5 mt-0.5 flex-wrap">
                                                    <span>{{ $item['owner']?->name ?? 'Owner' }}</span>
                                                    <span>&bull;</span>
                                                    <span class="font-mono">{{ $item['phone'] ?: 'No Phone' }}</span>
                                                    <span>&bull;</span>
                                                    <span
                                                        class="text-[#007AFF] font-medium">{{ $item['plan_name'] }}</span>
                                                </div>
                                            </div>

                                            <div class="shrink-0">
                                                @if ($item['already_sent'])
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                                        <i data-lucide="check" class="w-3 h-3"></i>
                                                        <span>Terkirim</span>
                                                    </span>
                                                @elseif ($item['phone'])
                                                    <button type="button"
                                                        @click="sendSingleReminder({{ $item['subscription']->id }}, '{{ $periodKey }}')"
                                                        class="min-h-[40px] px-3.5 rounded-[10px] text-[12px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all inline-flex items-center gap-1.5 shadow-sm">
                                                        <i data-lucide="send" class="w-3.5 h-3.5"></i>
                                                        <span>Kirim</span>
                                                    </button>
                                                @else
                                                    <span
                                                        class="text-[11.5px] font-medium text-[#FF3B30] dark:text-[#FF453A] inline-flex items-center gap-1">
                                                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                                        <span>Tanpa No WA</span>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-8 text-center text-[12px] text-black/40 dark:text-white/40">
                                    Tidak ada langganan jatuh tempo pada periode ini.
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Log Riwayat Pengingat Terkini (Table-to-Card Transformation View) --}}
            <div
                class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-md border border-black/[0.06] dark:border-white/[0.08] shadow-sm overflow-hidden">
                <div
                    class="px-5 sm:px-6 py-4.5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                    <div>
                        <h3 class="text-[15px] font-bold text-black dark:text-white">Audit Log Pengingat Terkirim</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50">Daftar rekaman riwayat pesan pengingat yang dieksekusi oleh bot admin</p>
                    </div>
                    <span class="text-[12px] text-black/45 dark:text-white/45 tabular-nums">Halaman {{ $recentReminders->currentPage() }} dari {{ $recentReminders->lastPage() }}</span>
                </div>

                {{-- Mobile Card List View (< md) --}}
                <div class="block md:hidden divide-y divide-black/[0.06] dark:divide-white/[0.06]">
                    @forelse ($recentReminders as $log)
                        <div class="p-4 space-y-2.5">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="font-bold text-[13.5px] text-black dark:text-white truncate">{{ $log->business_name }}</div>
                                    <div class="text-[11.5px] text-black/50 dark:text-white/50">{{ $log->owner_name }}</div>
                                </div>
                                <div class="shrink-0">
                                    @if ($log->status === 'sent')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                            Terkirim
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                            Gagal
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-[11.5px] text-black/60 dark:text-white/60 pt-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-[11px] font-bold uppercase text-black/60 dark:text-white/60 tracking-wider">
                                        {{ strtoupper($log->reminder_type) }}
                                    </span>
                                    <span class="font-mono text-black/70 dark:text-white/70">{{ $log->recipient_phone }}</span>
                                </div>
                                <span class="tabular-nums text-[11px] text-black/45 dark:text-white/45">{{ optional($log->sent_at)->format('d M Y, H:i') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-[13px] text-black/40 dark:text-white/40">
                            Belum ada catatan log pengingat.
                        </div>
                    @endforelse
                </div>

                {{-- Desktop Table View (>= md) --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-[13px]">
                        <thead>
                            <tr
                                class="border-b border-black/[0.06] dark:border-white/[0.08] text-black/45 dark:text-white/45 bg-black/[0.01] dark:bg-white/[0.02]">
                                <th class="text-left px-5 sm:px-6 py-3 font-semibold text-[11px] uppercase tracking-wider">Nama Bisnis &amp; Owner</th>
                                <th class="text-left px-4 py-3 font-semibold text-[11px] uppercase tracking-wider">No. WhatsApp</th>
                                <th class="text-left px-4 py-3 font-semibold text-[11px] uppercase tracking-wider">Periode</th>
                                <th class="text-center px-4 py-3 font-semibold text-[11px] uppercase tracking-wider">Status</th>
                                <th class="text-left px-4 py-3 font-semibold text-[11px] uppercase tracking-wider">Waktu Eksekusi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                            @forelse ($recentReminders as $log)
                                <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.025] transition-colors">
                                    <td class="px-5 sm:px-6 py-3.5 font-medium text-black dark:text-white">
                                        <div>{{ $log->business_name }}</div>
                                        <div class="text-[11px] text-black/50 dark:text-white/50 font-normal">
                                            {{ $log->owner_name }}</div>
                                    </td>
                                    <td class="px-4 py-3.5 font-mono text-black/70 dark:text-white/70 tabular-nums">
                                        {{ $log->recipient_phone }}
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <span
                                            class="font-mono text-[12px] font-semibold uppercase text-black/70 dark:text-white/70">
                                            {{ strtoupper($log->reminder_type) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        @if ($log->status === 'sent')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                                Terkirim
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                                Gagal
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-black/50 dark:text-white/50 tabular-nums">
                                        {{ optional($log->sent_at)->format('d M Y, H:i') }} WIB
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5"
                                        class="p-8 text-center text-[13px] text-black/40 dark:text-white/40">
                                        Belum ada catatan log pengingat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($recentReminders->hasPages())
                    <div class="px-5 sm:px-6 py-3.5 border-t border-black/[0.06] dark:border-white/[0.08]">
                        {{ $recentReminders->appends(['tab' => 'reminders'])->links() }}
                    </div>
                @endif
            </div>

        </div>

        {{-- ========================================================================= --}}
        {{-- TAB 3: BROADCAST BISNIS OWNER                                             --}}
        {{-- ========================================================================= --}}
        <div x-show="activeTab === 'blast'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
            class="space-y-6 w-full min-w-0">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 w-full min-w-0">

                {{-- Kolom Kiri: Form Buat Broadcast Baru (5 Kolom) --}}
                <div
                    class="lg:col-span-5 rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-md border border-black/[0.06] dark:border-white/[0.08] shadow-sm p-4 sm:p-6 space-y-5 w-full min-w-0">
                    <div class="min-w-0">
                        <h2 class="text-[17px] font-bold text-black dark:text-white">Buat Broadcast Pesan Baru</h2>
                        <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Kirim pesan informasi pengumuman, promo, atau rilis fitur baru ke pemilik usaha</p>
                    </div>

                    {{-- Driver Active Indicator & Warning --}}
                    @if ($isBlastActive && ($liveStatus === 'connected' || !empty($metaCreds['token'])))
                        <div
                            class="p-3.5 rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/25 text-[12px] text-[#248A3D] dark:text-[#30D158] space-y-1">
                            <div class="flex items-center gap-1.5 font-bold">
                                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                                <span>Gateway Meta WhatsApp Cloud API Resmi</span>
                            </div>
                            <p class="text-[11.5px] text-black/70 dark:text-white/70 leading-relaxed">
                                Broadcast dikirim melalui server Meta resmi dengan performa maksimal.
                            </p>
                        </div>
                    @else
                        <div
                            class="p-3.5 rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[12px] text-[#C41E17] dark:text-[#FF453A]">
                            <strong>Kanal Broadcast Belum Siap:</strong> Konfigurasi Token dan Phone Number ID Meta di tab Pengaturan Induk terlebih dahulu.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.whatsapp.blasts.store') }}" class="space-y-4"
                        onsubmit="return confirm('Pesan broadcast ini akan dikirimkan ke kontak WhatsApp pemilik bisnis yang terpilih. Lanjutkan pengiriman?');">
                        @csrf

                        <div>
                            <label
                                class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 mb-1.5">Judul Broadcast</label>
                            <input name="title" required maxlength="255"
                                placeholder="Contoh: Promo Perpanjangan Langganan Spesial"
                                class="w-full min-h-[46px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all">
                        </div>

                        <div>
                            <label
                                class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 mb-1.5">Target Segmen Pemilik Usaha</label>
                            <select name="target_filter"
                                class="w-full min-h-[46px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all">
                                <option value="all_owners">Semua Bisnis Owner Terdaftar ({{ number_format($targetCounts['all_owners']) }} kontak)</option>
                                <option value="active_subscribers">Pelanggan Paket Aktif ({{ number_format($targetCounts['active_subscribers']) }} kontak)</option>
                                <option value="expiring_soon">Akan Jatuh Tempo Dalam 7 Hari ({{ number_format($targetCounts['expiring_soon']) }} kontak)</option>
                                <option value="free_tier">Paket Gratis / Expired ({{ number_format($targetCounts['free_tier']) }} kontak)</option>
                            </select>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label
                                    class="text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55">Isi Pesan WhatsApp</label>
                                <span class="text-[11px] text-black/45 dark:text-white/45 font-mono">Maks 3.000 karakter</span>
                            </div>

                            {{-- Chip Variabel Interaktif --}}
                            <div class="flex flex-wrap gap-1.5 mb-2">
                                <span class="text-[11px] text-black/45 dark:text-white/45 self-center mr-1">Klik sisipkan:</span>
                                @foreach (['{owner}', '{bisnis}', '{paket}', '{tanggal_habis}'] as $v)
                                    <button type="button" @click="insertVariableToBlast('{{ $v }}')"
                                        class="px-2 py-0.5 rounded-[8px] text-[11px] font-mono bg-black/[0.05] dark:bg-white/[0.08] text-[#007AFF] hover:bg-[#007AFF]/10 transition-colors">
                                        {{ $v }}
                                    </button>
                                @endforeach
                            </div>

                            <textarea id="blastMessageTextarea" name="message" required maxlength="3000" rows="8" x-model="blastMessage"
                                placeholder="Tuliskan pesan broadcast di sini... Gunakan format tebal (*kata*) dan miring (_kata_) sesuai format WhatsApp."
                                class="w-full bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] p-3 text-[16px] sm:text-[13px] text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all resize-y"></textarea>
                        </div>

                        <div>
                            <label
                                class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 mb-1.5">URL Gambar Banner (Opsional)</label>
                            <input name="media_url" type="url" placeholder="https://domain.com/banner-promo.jpg"
                                class="w-full min-h-[46px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all">
                        </div>

                        {{-- Calming Reassurance Microcopy --}}
                        <div
                            class="p-3.5 rounded-[14px] bg-[#007AFF]/8 border border-[#007AFF]/20 text-[12px] text-[#007AFF] dark:text-[#0A84FF] flex items-start gap-2.5">
                            <i data-lucide="shield-check" class="w-4 h-4 shrink-0 mt-0.5"></i>
                            <span><strong>Pengiriman Aman:</strong> Pesan dikirim secara bertahap di latar belakang agar nomor WhatsApp tetap aman dan nyaman.</span>
                        </div>

                        <button type="submit" :disabled="status !== 'connected'"
                            class="w-full min-h-[48px] sm:min-h-[50px] rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] disabled:opacity-50 disabled:pointer-events-none text-white text-[14px] font-bold inline-flex items-center justify-center gap-2 shadow-sm active:scale-[0.98] transition-all">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            <span>Kirim</span>
                        </button>
                    </form>
                </div>

                {{-- Kolom Kanan: Riwayat Broadcast Blast (7 Kolom) (Table-to-Card Transformation View) --}}
                <div
                    class="lg:col-span-7 rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-md border border-black/[0.06] dark:border-white/[0.08] shadow-sm overflow-hidden flex flex-col justify-between w-full min-w-0">
                    <div>
                        <div
                            class="px-5 sm:px-6 py-4.5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                            <div>
                                <h2 class="text-[16px] font-bold text-black dark:text-white">Riwayat Broadcast WhatsApp</h2>
                                <p class="text-[12px] text-black/50 dark:text-white/50">Daftar riwayat broadcast promosi dan pengumuman yang pernah dikirimkan</p>
                            </div>
                            <span class="text-[12px] text-black/45 dark:text-white/45 tabular-nums">Total {{ $blasts->total() }} Pesan</span>
                        </div>

                        {{-- Mobile Card List View (< md) --}}
                        <div class="block md:hidden divide-y divide-black/[0.06] dark:divide-white/[0.06]">
                            @forelse ($blasts as $b)
                                <div class="p-4 space-y-2.5">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.whatsapp.blasts.show', $b) }}"
                                                class="font-bold text-[13.5px] text-black dark:text-white hover:text-[#007AFF] truncate block">
                                                {{ $b->title }}
                                            </a>
                                            <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">
                                                {{ optional($b->created_at)->format('d M Y, H:i') }} WIB
                                            </div>
                                        </div>
                                        <div class="shrink-0">
                                            @if ($b->status === 'completed')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                                    Selesai
                                                </span>
                                            @elseif ($b->status === 'processing')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                                                    Memproses
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                                    Gagal
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between text-[11.5px] text-black/60 dark:text-white/60 pt-1">
                                        <div class="flex items-center gap-2">
                                            <span>{{ str_replace('_', ' ', ucfirst($b->target_filter)) }}</span>
                                            <span>&bull;</span>
                                            <span class="tabular-nums font-semibold text-black dark:text-white">{{ $b->total_sent }}/{{ $b->total_recipients }}</span>
                                        </div>
                                        <a href="{{ route('admin.whatsapp.blasts.show', $b) }}"
                                            class="min-h-[32px] px-3 rounded-[8px] text-[11.5px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition-all inline-flex items-center gap-1">
                                            <span>Detail</span>
                                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="p-10 text-center text-[13px] text-black/40 dark:text-white/40">
                                    Belum ada siaran broadcast yang dikirimkan.
                                </div>
                            @endforelse
                        </div>

                        {{-- Desktop Table View (>= md) --}}
                        <div class="hidden md:block overflow-x-auto">
                            <table class="w-full text-[13px]">
                                <thead>
                                    <tr
                                        class="border-b border-black/[0.06] dark:border-white/[0.08] text-black/45 dark:text-white/45 bg-black/[0.01] dark:bg-white/[0.02]">
                                        <th class="text-left px-5 sm:px-6 py-3 font-semibold text-[11px] uppercase tracking-wider">Judul Broadcast</th>
                                        <th class="text-left px-4 py-3 font-semibold text-[11px] uppercase tracking-wider">Target</th>
                                        <th class="text-center px-4 py-3 font-semibold text-[11px] uppercase tracking-wider">Keterkiriman</th>
                                        <th class="text-center px-4 py-3 font-semibold text-[11px] uppercase tracking-wider">Status</th>
                                        <th class="text-right px-5 sm:px-6 py-3 font-semibold text-[11px] uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                                    @forelse ($blasts as $b)
                                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.025] transition-colors">
                                            <td class="px-5 sm:px-6 py-4">
                                                <a href="{{ route('admin.whatsapp.blasts.show', $b) }}"
                                                    class="font-bold text-[#007AFF] hover:underline">
                                                    {{ $b->title }}
                                                </a>
                                                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">
                                                    {{ optional($b->created_at)->format('d M Y, H:i') }} WIB
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 text-black/70 dark:text-white/70 text-[12px]">
                                                {{ str_replace('_', ' ', ucfirst($b->target_filter)) }}
                                            </td>
                                            <td class="px-4 py-4 text-center tabular-nums">
                                                <span
                                                    class="font-bold text-[#248A3D] dark:text-[#30D158]">{{ $b->total_sent }}</span>
                                                <span class="text-black/40 dark:text-white/40">/ {{ $b->total_recipients }}</span>
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                @if ($b->status === 'completed')
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                                        Selesai
                                                    </span>
                                                @elseif ($b->status === 'processing')
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                                                        Memproses
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                                        Gagal
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-5 sm:px-6 py-4 text-right">
                                                <a href="{{ route('admin.whatsapp.blasts.show', $b) }}"
                                                    class="min-h-[36px] px-3 rounded-[10px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition-all inline-flex items-center gap-1.5">
                                                    <span>Detail</span>
                                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5"
                                                class="p-10 text-center text-[13px] text-black/40 dark:text-white/40">
                                                Belum ada siaran broadcast yang dikirimkan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if ($blasts->hasPages())
                        <div class="px-5 sm:px-6 py-3.5 border-t border-black/[0.06] dark:border-white/[0.08]">
                            {{ $blasts->appends(['tab' => 'blast'])->links() }}
                        </div>
                    @endif
                </div>

            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TAB 4: TEMPLATE NOTIFIKASI PESAN                                          --}}
        {{-- ========================================================================= --}}
        <div x-show="activeTab === 'templates'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
            class="space-y-6 w-full min-w-0">
            <div
                class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-md border border-black/[0.06] dark:border-white/[0.08] shadow-sm p-4 sm:p-7 space-y-6 w-full min-w-0">
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-black/[0.06] dark:border-white/[0.08] min-w-0">
                    <div class="min-w-0 flex-1">
                        <h2 class="text-[17px] font-bold text-black dark:text-white">Template Pesan Pengingat Otomatis</h2>
                        <p class="text-[12px] sm:text-[13px] text-black/50 dark:text-white/50 mt-0.5">Sesuaikan susunan kalimat dan placeholder variabel dinamis untuk setiap tahapan pengingat</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-[11px] font-semibold text-black/45 dark:text-white/45">Variabel Didukung:</span>
                        @foreach (['{owner}', '{bisnis}', '{paket}', '{tanggal_habis}', '{link_bayar}'] as $tag)
                            <span
                                class="px-2 py-0.5 rounded-[6px] text-[11px] font-mono bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70">
                                {{ $tag }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.whatsapp.reminders.templates') }}" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                        @php
                            $templateConfigs = [
                                'h-7' => [
                                    'name' => 'template_h7',
                                    'label' => 'Pengingat H-7 (Peringatan Awal 1 Minggu)',
                                    'badge' => 'bg-[#007AFF]/15 text-[#007AFF]',
                                ],
                                'h-3' => [
                                    'name' => 'template_h3',
                                    'label' => 'Pengingat H-3 (Tindak Lanjut 3 Hari)',
                                    'badge' => 'bg-[#FF9500]/15 text-[#FF9500]',
                                ],
                                'h-1' => [
                                    'name' => 'template_h1',
                                    'label' => 'Pengingat H-1 (Peringatan Terakhir Besok)',
                                    'badge' => 'bg-[#AF52DE]/15 text-[#AF52DE]',
                                ],
                                'hari_h' => [
                                    'name' => 'template_h0',
                                    'label' => 'Pengingat Hari H (Jatuh Tempo Hari Ini)',
                                    'badge' => 'bg-[#FF3B30]/15 text-[#FF3B30]',
                                ],
                            ];
                        @endphp

                        @foreach ($templateConfigs as $type => $field)
                            <div
                                class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="text-[13px] font-bold text-black dark:text-white">
                                        {{ $field['label'] }}
                                    </label>
                                    <span
                                        class="font-mono text-[11px] font-bold text-black/50 dark:text-white/50 uppercase tracking-wider">
                                        {{ strtoupper($type) }}
                                    </span>
                                </div>
                                <textarea name="{{ $field['name'] }}" required maxlength="2000" rows="8"
                                    class="w-full bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[12px] p-3.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all font-mono leading-relaxed resize-y">{{ $templates[$type] ?? '' }}</textarea>
                            </div>
                        @endforeach
                    </div>

                    <div
                        class="flex items-center justify-end gap-3 pt-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <button type="submit"
                            class="min-h-[48px] px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-bold inline-flex items-center gap-2 shadow-sm active:scale-[0.98] transition-all">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TAB 5: MONITORING MERCHANT WABA (PLATFORM PARENT OVERSIGHT)               --}}
        {{-- ========================================================================= --}}
        <div x-show="activeTab === 'merchants'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
            class="space-y-6 w-full min-w-0">

            {{-- Header Card --}}
            <div
                class="p-5 sm:p-6 rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-md border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 w-full min-w-0">
                <div class="flex items-center gap-3.5">
                    <div
                        class="w-11 h-11 sm:w-12 sm:h-12 rounded-[16px] bg-gradient-to-br from-[#1877F2] to-[#007AFF] text-white flex items-center justify-center shadow-md shadow-[#1877F2]/20 shrink-0">
                        <i data-lucide="store" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h2 class="text-[17px] font-bold text-black dark:text-white tracking-tight">Monitoring Akun WhatsApp Merchant</h2>
                        <p class="text-[12px] sm:text-[12.5px] text-black/55 dark:text-white/55 mt-0.5">
                            Pengawasan sentral seluruh akun WhatsApp Business resmi (WABA) merchant yang terhubung melalui Embedded Signup
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="px-3.5 py-1.5 rounded-[12px] bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] text-[12px] font-bold border border-[#34C759]/20 tabular-nums">
                        {{ $activeMerchantsCount }} Akun Aktif
                    </span>
                </div>
            </div>

            {{-- 4 Metric Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-1">
                    <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider block">Total Merchant</span>
                    <div class="text-[20px] sm:text-[24px] font-bold text-black dark:text-white tabular-nums">
                        {{ $merchantSummary['total'] ?? 0 }}
                    </div>
                </div>
                <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-1">
                    <span class="text-[11px] font-semibold text-[#248A3D] dark:text-[#30D158] uppercase tracking-wider block">Terhubung Aktif</span>
                    <div class="text-[20px] sm:text-[24px] font-bold text-[#248A3D] dark:text-[#30D158] tabular-nums">
                        {{ $merchantSummary['connected'] ?? 0 }}
                    </div>
                </div>
                <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-1">
                    <span class="text-[11px] font-semibold text-[#007AFF] uppercase tracking-wider block">Akun Mode Live</span>
                    <div class="text-[20px] sm:text-[24px] font-bold text-[#007AFF] tabular-nums">
                        {{ $merchantSummary['live_count'] ?? 0 }}
                    </div>
                </div>
                <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-1">
                    <span class="text-[11px] font-semibold text-[#FF9500] uppercase tracking-wider block">Sandbox / Draft</span>
                    <div class="text-[20px] sm:text-[24px] font-bold text-[#FF9500] tabular-nums">
                        {{ $merchantSummary['sandbox_count'] ?? 0 }}
                    </div>
                </div>
            </div>

            {{-- Bento Table: Merchant Accounts --}}
            <div class="rounded-[22px] bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-md border border-black/[0.06] dark:border-white/[0.08] shadow-sm overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                    <h3 class="text-[15px] font-bold text-black dark:text-white">Daftar Akun WhatsApp Merchant</h3>
                    <span class="text-[12px] text-black/50 dark:text-white/50 tabular-nums">Menampilkan {{ count($merchantSummary['accounts'] ?? []) }} akun</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[13px]">
                        <thead>
                            <tr class="bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/[0.06] dark:border-white/[0.08] text-black/50 dark:text-white/50 text-[11px] font-bold uppercase tracking-wider">
                                <th class="py-3 px-4 sm:px-6">Nama Bisnis &amp; Pemilik</th>
                                <th class="py-3 px-4">Nomor WhatsApp</th>
                                <th class="py-3 px-4">WABA ID / Phone ID</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-center">Mode</th>
                                <th class="py-3 px-4 sm:px-6 text-right">Terhubung Sejak</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.06] dark:divide-white/[0.08]">
                            @forelse ($merchantSummary['accounts'] ?? [] as $acc)
                                <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="py-3.5 px-4 sm:px-6">
                                        <div class="font-bold text-black dark:text-white">{{ $acc->business->name ?? 'Bisnis #' . $acc->business_id }}</div>
                                        <div class="text-[11.5px] text-black/50 dark:text-white/50">{{ $acc->business->owner->name ?? '-' }} ({{ $acc->business->owner->email ?? '-' }})</div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-mono font-medium text-black dark:text-white">{{ $acc->display_phone_number ?: ($acc->phone_number ? '+' . $acc->phone_number : '-') }}</div>
                                        <div class="text-[11px] text-black/40 dark:text-white/40">{{ $acc->verified_name ?: 'Nama Belum Terverifikasi' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-[11.5px] text-black/70 dark:text-white/70">
                                        <div>WABA: {{ $acc->waba_id ?: '-' }}</div>
                                        <div>PID: {{ $acc->phone_number_id ?: '-' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @if ($acc->status === 'connected')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                                <span>Aktif</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-black/5 dark:bg-white/10 text-black/50 dark:text-white/50">
                                                <span>{{ ucfirst($acc->status) }}</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @if ($acc->environment === 'live')
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/12 text-[#007AFF]">Live</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">Sandbox</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-6 text-right text-black/50 dark:text-white/50 text-[12px] tabular-nums">
                                        {{ $acc->created_at ? $acc->created_at->isoFormat('D MMM Y, HH:mm') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-black/45 dark:text-white/45">
                                        <div class="w-12 h-12 rounded-[16px] bg-black/5 dark:bg-white/10 text-black/40 dark:text-white/40 flex items-center justify-center mx-auto mb-3">
                                            <i data-lucide="store" class="w-6 h-6"></i>
                                        </div>
                                        <div class="text-[14px] font-bold text-black dark:text-white">Belum Ada Merchant Terhubung</div>
                                        <p class="text-[12px] text-black/50 dark:text-white/50 max-w-sm mx-auto mt-1">
                                            Merchant dapat menghubungkan akun WhatsApp resmi toko mereka secara mandiri melalui menu WhatsApp di dashboard pemilik toko.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Toast Notifikasi Salin Kredensial --}}
        <div x-show="copyToast" x-cloak x-transition
            class="fixed bottom-6 right-6 z-50 p-4 rounded-[16px] bg-black/90 dark:bg-white/95 text-white dark:text-black shadow-2xl flex items-center gap-2.5 text-[13px] font-bold">
            <i data-lucide="check-circle" class="w-4 h-4 text-[#34C759]"></i>
            <span x-text="copyToastMessage"></span>
        </div>

    </div>

    @push('scripts')
        <script>
            function adminWaCenter() {
                return {
                    activeTab: @json($initialTab),
                    status: @json($liveStatus ?? 'disconnected'),
                    phone: @json($initialPhone),
                    isLoading: false,
                    blastMessage: '',

                    testPhone: '',
                    testMessage: 'Pesan uji coba resmi dari WhatsApp Platform Cooca.',
                    testLoading: false,
                    testResult: '',
                    testOk: false,

                    metaToken: @json($metaCreds['token'] ?? ''),
                    metaPhoneId: @json($metaCreds['phone_number_id'] ?? ''),
                    metaWabaId: @json($metaCreds['waba_id'] ?? ''),
                    metaAppId: @json($platformApp['app_id'] ?? ''),
                    metaAppSecret: @json($platformApp['app_secret'] ?? ''),
                    metaConfigId: @json($platformApp['config_id'] ?? ''),
                    metaWebhookVerifyToken: @json($platformApp['webhook_verify_token'] ?? ''),
                    metaGraphVersion: @json($platformApp['graph_version'] ?? 'v21.0'),
                    metaGraphUrl: @json($platformApp['graph_url'] ?? 'https://graph.facebook.com'),
                    showMetaToken: false,
                    showMetaAppSecret: false,
                    metaVerifyLoading: false,
                    metaVerifyResult: null,
                    metaVerifyError: null,

                    copyToast: false,
                    copyToastMessage: '',

                    refreshIcons() {
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined' && lucide.createIcons) {
                                lucide.createIcons();
                            }
                        });
                    },

                    copyToClipboard(text, label) {
                        if (!text) return;
                        navigator.clipboard.writeText(text).then(() => {
                            this.copyToastMessage = label + ' berhasil disalin ke clipboard';
                            this.copyToast = true;
                            setTimeout(() => {
                                this.copyToast = false;
                            }, 3000);
                        }).catch(() => {
                            prompt('Salin manual:', text);
                        });
                    },

                    async verifyMetaCredentials() {
                        if (!this.metaToken.trim() || !this.metaPhoneId.trim()) {
                            this.metaVerifyError =
                                'Silakan isi Meta Permanent Access Token dan Phone Number ID terlebih dahulu.';
                            this.metaVerifyResult = null;
                            return;
                        }
                        this.metaVerifyLoading = true;
                        this.metaVerifyResult = null;
                        this.metaVerifyError = null;
                        try {
                            const response = await fetch('{{ route('admin.whatsapp.verify-meta') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                                },
                                body: JSON.stringify({
                                    token: this.metaToken.trim(),
                                    phone_number_id: this.metaPhoneId.trim()
                                })
                            });
                            const data = await response.json();
                            if (response.ok && data.success) {
                                this.metaVerifyResult = data.data;
                                this.status = 'connected';
                                if (data.data.display_phone_number) {
                                    this.phone = data.data.display_phone_number;
                                }
                            } else {
                                this.metaVerifyError = data.error || 'Gagal memverifikasi akun Meta Graph API.';
                            }
                        } catch (e) {
                            this.metaVerifyError = 'Kesalahan jaringan saat menghubungi server verifikasi.';
                        } finally {
                            this.metaVerifyLoading = false;
                            this.refreshIcons();
                        }
                    },

                    init() {
                        this.checkStatus();
                        this.refreshIcons();
                    },

                    setTab(tab) {
                        this.activeTab = tab;
                        const url = new URL(window.location.href);
                        url.searchParams.set('tab', tab);
                        window.history.replaceState({}, '', url);
                        if (tab === 'parent_setup') {
                            this.checkStatus();
                        }
                        this.refreshIcons();
                    },

                    insertVariableToBlast(variable) {
                        const textarea = document.getElementById('blastMessageTextarea');
                        if (!textarea) return;
                        const start = textarea.selectionStart;
                        const end = textarea.selectionEnd;
                        const text = textarea.value;
                        textarea.value = text.substring(0, start) + variable + text.substring(end);
                        this.blastMessage = textarea.value;
                        textarea.focus();
                        textarea.selectionStart = textarea.selectionEnd = start + variable.length;
                    },

                    async checkStatus() {
                        this.isLoading = true;
                        try {
                            const response = await fetch('{{ route('admin.whatsapp.status') }}', {
                                headers: {
                                    'Accept': 'application/json'
                                }
                            });
                            const data = await response.json();
                            this.applyStatus(data);
                        } catch (error) {
                            this.status = 'disconnected';
                        } finally {
                            this.isLoading = false;
                            this.refreshIcons();
                        }
                    },

                    applyStatus(data) {
                        const rawStatus = String(data.status || '').toLowerCase();
                        this.status = rawStatus === 'connected' ? 'connected' : 'disconnected';
                        this.phone = data.phone || this.phone;
                        this.refreshIcons();
                    },

                    async sendTest() {
                        if (!this.testPhone.trim() || !this.testMessage.trim()) {
                            this.testResult = 'Nomor tujuan dan teks pesan wajib diisi.';
                            this.testOk = false;
                            return;
                        }
                        this.testLoading = true;
                        this.testResult = '';
                        try {
                            const response = await fetch('{{ route('admin.whatsapp.test') }}', {
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
                            const data = await response.json();
                            this.testOk = response.ok && data.success === true;
                            this.testResult = this.testOk ? 'Pesan uji coba berhasil terkirim ke ponsel penerima!' : (
                                'Gagal: ' + (data.error || 'Gateway WhatsApp Meta tidak merespons.'));
                        } catch (error) {
                            this.testOk = false;
                            this.testResult = 'Terjadi kesalahan jaringan saat mengirimkan pesan.';
                        } finally {
                            this.testLoading = false;
                            this.refreshIcons();
                        }
                    },

                    async sendSingleReminder(subscriptionId, type) {
                        if (!confirm(`Kirimkan pengingat langganan (${type}) sekarang ke nomor owner?`)) return;
                        try {
                            const response = await fetch(`/admin/whatsapp/reminders/${subscriptionId}/send`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                                },
                                body: JSON.stringify({
                                    type: type
                                })
                            });
                            const data = await response.json();
                            alert(data.message || 'Proses selesai.');
                            window.location.reload();
                        } catch (error) {
                            alert('Gagal mengirim pengingat: Kesalahan jaringan.');
                        }
                    }
                };
            }
        </script>
    @endpush
@endsection
