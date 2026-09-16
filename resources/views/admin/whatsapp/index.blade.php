@extends('layouts.admin')
@section('title', 'WhatsApp Admin Center - Pengingat Langganan & Broadcast Bisnis')

@section('content')
    @php
        $validTabs = ['connection', 'reminders', 'blast', 'templates'];
        $initialTab = in_array($tab, $validTabs, true) ? $tab : 'reminders';
        $initialPhone = $waStatus['phone'] ?? '';
        if (!$initialPhone && isset($waStatus['user']['id'])) {
            $initialPhone = explode(':', (string) $waStatus['user']['id'])[0];
        }
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
    @endphp

    <div class="space-y-5 sm:space-y-6 max-w-7xl w-full min-w-0 mx-auto pb-28 lg:pb-10" x-data="adminWaCenter()" x-init="init()">

        {{-- 1. BENTO HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 sm:gap-4 min-w-0">
            <div class="flex items-center gap-3 sm:gap-3.5 min-w-0 flex-1">
                <div
                    class="w-10 h-10 sm:w-12 sm:h-12 rounded-[14px] sm:rounded-[18px] bg-gradient-to-br from-[#25D366] to-[#128C7E] flex items-center justify-center shadow-md shadow-[#25D366]/20 shrink-0 text-white">
                    <i data-lucide="message-square" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h1 class="text-[18px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight truncate">WhatsApp Admin Center</h1>
                    <p class="text-[12px] sm:text-[13px] text-black/50 dark:text-white/50 mt-0.5 truncate">Pusat layanan WhatsApp untuk pengingat masa aktif langganan dan siaran informasi ke pemilik toko</p>
                </div>
            </div>

            {{-- Live Status Indicator & Quick Connection Button --}}
            <div class="grid grid-cols-1 xs:grid-cols-2 sm:flex items-center gap-2 sm:gap-3 w-full sm:w-auto min-w-0">
                <template x-if="status === 'connected'">
                    <div
                        class="flex items-center justify-center gap-2 px-3 py-2 sm:px-3.5 sm:py-2.5 rounded-[12px] bg-[#34C759]/12 border border-[#34C759]/25 text-[#248A3D] dark:text-[#30D158] text-[12px] font-semibold min-h-[40px] sm:min-h-[44px] min-w-0">
                        <span class="w-2 h-2 rounded-full bg-[#34C759] shrink-0"></span>
                        <span class="truncate">Bot Aktif</span>
                        <span class="text-black/30 dark:text-white/30">|</span>
                        <span class="font-mono truncate" x-text="phone ? '+' + phone : 'Online'"></span>
                    </div>
                </template>
                <template x-if="status === 'scan_qr'">
                    <div
                        class="flex items-center justify-center gap-2 px-3 py-2 sm:px-3.5 sm:py-2.5 rounded-[12px] bg-[#FF9500]/12 border border-[#FF9500]/25 text-[#B25E00] dark:text-[#FF9F0A] text-[12px] font-semibold min-h-[40px] sm:min-h-[44px] min-w-0">
                        <span class="w-2 h-2 rounded-full bg-[#FF9500] shrink-0"></span>
                        <span class="truncate">Pindai Kode QR</span>
                    </div>
                </template>
                <template x-if="status === 'disconnected' || status === ''">
                    <div
                        class="flex items-center justify-center gap-2 px-3 py-2 sm:px-3.5 sm:py-2.5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] border border-black/10 dark:border-white/10 text-black/55 dark:text-white/55 text-[12px] font-semibold min-h-[40px] sm:min-h-[44px] min-w-0">
                        <span class="w-2 h-2 rounded-full bg-black/30 dark:bg-white/30 shrink-0"></span>
                        <span class="truncate">Belum Terhubung</span>
                    </div>
                </template>

                <button type="button" @click="setTab('connection')"
                    class="min-h-[40px] sm:min-h-[44px] px-4 rounded-[12px] text-[12.5px] sm:text-[13px] font-semibold bg-[#25D366] text-white hover:bg-[#1EBE5D] active:scale-[0.98] shadow-sm transition-all inline-flex items-center justify-center gap-2 min-w-0">
                    <i data-lucide="qr-code" class="w-4 h-4 shrink-0"></i>
                    <span class="truncate">Kelola WhatsApp</span>
                </button>
            </div>
        </div>

        {{-- NOTIFICATIONS / ALERTS --}}
        {{--
            NOTE: session('success') banner dihapus dari sini.
            Layout admin.blade.php sudah menangani flash notifications via
            AppAlert toast (session()->pull). Menampilkan banner inline di sini
            DAN toast dari layout mengakibatkan double-notification.
        --}}
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
            {{-- Tile 1: Status Gateway --}}
            <div
                class="rounded-[18px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-3.5 sm:p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between min-w-0">
                <div class="flex items-center justify-between gap-2 min-w-0">
                    <span
                        class="text-[11px] sm:text-[12px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider truncate">Status Gateway</span>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-[9px] sm:rounded-[10px] flex items-center justify-center shrink-0"
                        :class="status === 'connected' ? 'bg-[#34C759]/15 text-[#34C759]' : (status === 'scan_qr' ?
                            'bg-[#FF9500]/15 text-[#FF9500]' :
                            'bg-black/5 dark:bg-white/10 text-black/40 dark:text-white/40')">
                        <i data-lucide="activity" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                    </div>
                </div>
                <div class="mt-2.5 min-w-0">
                    <div
                        class="text-[18px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight flex items-center gap-1.5 truncate">
                        <span
                            x-text="status === 'connected' ? 'Aktif' : (status === 'scan_qr' ? 'Scan' : 'Offline')"></span>
                    </div>
                    <p class="text-[11px] sm:text-[11.5px] text-black/50 dark:text-white/50 mt-0.5 font-mono truncate"
                        x-text="phone ? '+' + phone : 'Belum Konek'"></p>
                </div>
            </div>

            {{-- Tile 2: Pengingat Hari Ini --}}
            <div
                class="rounded-[18px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-3.5 sm:p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between min-w-0">
                <div class="flex items-center justify-between gap-2 min-w-0">
                    <span
                        class="text-[11px] sm:text-[12px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider truncate">Pengingat Hari Ini</span>
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

            {{-- Tile 3: Jangkauan Owner --}}
            <div
                class="rounded-[18px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-3.5 sm:p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between min-w-0">
                <div class="flex items-center justify-between gap-2 min-w-0">
                    <span
                        class="text-[11px] sm:text-[12px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider truncate">Jangkauan Owner</span>
                    <div
                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-[9px] sm:rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center shrink-0">
                        <i data-lucide="users" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                    </div>
                </div>
                <div class="mt-2.5 min-w-0">
                    <div
                        class="text-[18px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight tabular-nums truncate">
                        {{ number_format($totalOwnersCount) }}
                    </div>
                    <p class="text-[11px] sm:text-[11.5px] text-black/50 dark:text-white/50 mt-0.5 truncate">Owner di platform</p>
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
                        {{ number_format($sentRemindersCount + $totalBlastSent) }} pesan sukses</p>
                </div>
            </div>
        </div>

        {{-- 3. PERSISTENT SEGMENTED CONTROL (APPLE HIG STYLE) --}}
        <div class="w-full max-w-full min-w-0 overflow-hidden">
            <div
                class="p-1.5 bg-black/[0.05] dark:bg-white/[0.07] rounded-[16px] flex items-center gap-1.5 overflow-x-auto shadow-inner no-scrollbar">
                <button type="button" @click="setTab('connection')"
                    :class="activeTab === 'connection' ?
                        'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-semibold' :
                        'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                    class="min-h-[42px] sm:min-h-[44px] px-3.5 sm:px-5 rounded-[12px] text-[12.5px] sm:text-[13px] transition-all flex items-center gap-2 sm:gap-2.5 shrink-0 whitespace-nowrap active:scale-[0.98]">
                    <i data-lucide="qr-code" class="w-4 h-4 text-[#25D366]"></i>
                    <span>Status &amp; Sesi QR</span>
                    <template x-if="status === 'connected'">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                    </template>
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
                    <span>Broadcast Bisnis Owner</span>
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
        {{-- TAB 1: STATUS & KONEKSI QR CODE / DUAL GATEWAY CONFIGURATION              --}}
        {{-- ========================================================================= --}}
        <div x-show="activeTab === 'connection'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
            class="space-y-6">

            {{-- 1. HIGH-RISK BAN WARNING BANNER (APPLE HIG VIBRANT CALLOUT) --}}
            <div
                class="rounded-[20px] sm:rounded-[22px] p-4 sm:p-6 bg-gradient-to-br from-[#FF9500]/12 via-[#FF9500]/8 to-[#FF3B30]/10 border border-[#FF9500]/30 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4 w-full min-w-0">
                <div class="flex items-start gap-3 sm:gap-4 min-w-0 flex-1">
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 rounded-[14px] sm:rounded-[16px] bg-[#FF9500]/20 text-[#B25E00] dark:text-[#FF9F0A] flex items-center justify-center shrink-0 mt-0.5 shadow-inner">
                        <i data-lucide="alert-triangle" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                    </div>
                    <div class="space-y-1 min-w-0 flex-1">
                        <h3 class="text-[14px] sm:text-[15px] font-bold text-[#B25E00] dark:text-[#FF9F0A] tracking-tight">
                            Peringatan Risiko Blokir Sangat Besar (Baileys / Scan QR)
                        </h3>
                        <p class="text-[12px] sm:text-[12.5px] text-black/75 dark:text-white/75 leading-relaxed max-w-3xl">
                            Pengiriman kode OTP secara beruntun lewat protokol Scan QR berisiko diblokir oleh WhatsApp.
                            Untuk kelancaran operasional bisnis, disarankan menggunakan <strong>Meta WhatsApp Cloud API
                                resmi</strong> untuk kebutuhan OTP.
                        </p>
                    </div>
                </div>
                <a href="#dual-gateway-config"
                    class="min-h-[42px] sm:min-h-[44px] px-4 sm:px-5 rounded-[12px] sm:rounded-[14px] text-[13px] font-bold bg-[#FF9500] hover:bg-[#E08500] text-white shrink-0 shadow-sm transition-all inline-flex items-center justify-center gap-2 whitespace-nowrap w-full sm:w-auto self-stretch sm:self-start md:self-center active:scale-[0.98]">
                    <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                    <span>Konfigurasi Dual Gateway</span>
                </a>
            </div>

            {{-- 2. DUAL GATEWAY CONFIGURATION BENTO CARD (OTP & BLAST DRIVER MANAGER) --}}
            <div id="dual-gateway-config"
                class="rounded-[22px] sm:rounded-[24px] bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-md border border-black/[0.08] dark:border-white/[0.08] shadow-sm p-4 sm:p-7 space-y-6 w-full min-w-0">
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-black/[0.06] dark:border-white/[0.08] min-w-0">
                    <div class="flex items-center gap-3 sm:gap-3.5 min-w-0 flex-1">
                        <div
                            class="w-10 h-10 sm:w-12 sm:h-12 rounded-[14px] sm:rounded-[16px] bg-gradient-to-br from-[#007AFF] to-[#5856D6] text-white flex items-center justify-center shadow-md shadow-[#007AFF]/20 shrink-0">
                            <i data-lucide="git-fork" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h2 class="text-[16px] sm:text-[17px] font-bold text-black dark:text-white tracking-tight truncate sm:whitespace-normal">Manajemen Dual Gateway WhatsApp</h2>
                            <p class="text-[12px] sm:text-[12.5px] text-black/55 dark:text-white/55 mt-0.5 truncate sm:whitespace-normal">Tentukan gateway independen untuk lalu lintas OTP keamanan vs broadcast promosi guna meminimalkan risiko blokir</p>
                        </div>
                    </div>

                    {{-- Status Pills & Auto-Save Indicator --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <div x-show="gatewaySaving" x-cloak
                            class="px-3 py-1.5 rounded-[12px] text-[11px] font-bold bg-[#007AFF]/12 text-[#007AFF] border border-[#007AFF]/25 flex items-center gap-1.5">
                            <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i>
                            <span>Menyimpan Otomatis...</span>
                        </div>
                        <div x-show="gatewaySavedToast" x-cloak
                            class="px-3 py-1.5 rounded-[12px] text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/30 flex items-center gap-1.5">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            <span>Tersimpan Otomatis</span>
                        </div>
                        <div
                            class="px-3 py-1.5 rounded-[12px] text-[11px] font-semibold flex items-center gap-1.5"
                            :class="isOtpActive && otpDriver !== 'disabled' ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20' : 'bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50 border border-black/10 dark:border-white/10'">
                            <span class="w-2 h-2 rounded-full" :class="isOtpActive && otpDriver !== 'disabled' ? 'bg-[#34C759]' : 'bg-black/30 dark:bg-white/30'"></span>
                            <span>OTP: </span>
                            <span x-text="!isOtpActive || otpDriver === 'disabled' ? 'Nonaktif' : (otpDriver === 'meta_cloud' ? 'Meta Cloud (Resmi)' : 'Baileys (QR)')">
                                {{ $otpDriver === 'meta_cloud' ? 'Meta Cloud (Resmi)' : ($otpDriver === 'baileys' ? 'Baileys (QR)' : 'Nonaktif') }}
                            </span>
                        </div>
                        <div
                            class="px-3 py-1.5 rounded-[12px] text-[11px] font-semibold flex items-center gap-1.5"
                            :class="isBlastActive && blastDriver !== 'disabled' ? 'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A] border border-[#FF9500]/20' : 'bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50 border border-black/10 dark:border-white/10'">
                            <span
                                class="w-2 h-2 rounded-full" :class="isBlastActive && blastDriver !== 'disabled' ? 'bg-[#FF9500]' : 'bg-black/30 dark:bg-white/30'"></span>
                            <span>Blast: </span>
                            <span x-text="!isBlastActive || blastDriver === 'disabled' ? 'Nonaktif' : (blastDriver === 'baileys' ? 'Baileys (QR)' : 'Meta Cloud')">
                                {{ $blastDriver === 'baileys' ? 'Baileys (QR)' : ($blastDriver === 'meta_cloud' ? 'Meta Cloud' : 'Nonaktif') }}
                            </span>
                        </div>
                    </div>
                </div>

                <form id="gatewayConfigForm" method="POST" action="{{ route('admin.whatsapp.config') }}" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Channel 1: OTP Gateway Driver --}}
                        <div
                            class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06] space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div
                                        class="w-8 h-8 rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                                        <i data-lucide="key-round" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-[14px] font-bold text-black dark:text-white">Jalur Kode Masuk (OTP)</h4>
                                        <p class="text-[11.5px] text-black/50 dark:text-white/50">Kanal OTP Keamanan &bull; Untuk verifikasi saat login, daftar akun, dan ganti profil</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="otp_active" value="1" class="sr-only peer"
                                        x-model="isOtpActive" @change="autoSaveGatewayConfig()"
                                        {{ $isOtpActive ? 'checked' : '' }}>
                                    <div
                                        class="w-11 h-6 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]">
                                    </div>
                                </label>
                            </div>

                            <div>
                                <label
                                    class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 mb-1.5">Pilihan Jalur OTP</label>
                                <select name="otp_driver"
                                    x-model="otpDriver" @change="autoSaveGatewayConfig()"
                                    class="w-full min-h-[46px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 font-medium transition-all">
                                    <option value="meta_cloud" {{ $otpDriver === 'meta_cloud' ? 'selected' : '' }}>
                                        Meta WhatsApp Cloud API (Resmi Facebook - Anti Blokir) - Sangat Stabil
                                    </option>
                                    <option value="baileys" {{ $otpDriver === 'baileys' ? 'selected' : '' }}>
                                        Scan QR Baileys (Server Lokal)
                                    </option>
                                    <option value="disabled" {{ $otpDriver === 'disabled' ? 'selected' : '' }}>
                                        Nonaktifkan Pengiriman OTP via WhatsApp
                                    </option>
                                </select>
                            </div>
                            <div class="flex items-start gap-2 text-[11.5px] text-black/50 dark:text-white/50 leading-relaxed">
                                <i data-lucide="info" class="w-3.5 h-3.5 text-[#007AFF] shrink-0 mt-0.5"></i>
                                <span>Rekomendasi: Gunakan <strong>Meta WhatsApp Cloud API</strong> karena Meta menyediakan 1.000 pesan layanan gratis setiap bulan.</span>
                            </div>
                        </div>

                        {{-- Channel 2: Broadcast / Blast Driver --}}
                        <div
                            class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06] space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div
                                        class="w-8 h-8 rounded-[10px] bg-[#FF9500]/15 text-[#FF9500] flex items-center justify-center">
                                        <i data-lucide="megaphone" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-[14px] font-bold text-black dark:text-white">Jalur Pesan Siaran &amp; Pengingat</h4>
                                        <p class="text-[11.5px] text-black/50 dark:text-white/50">Kanal Broadcast &amp; Pengingat &bull; Untuk pengingat jatuh tempo langganan dan pesan pengumuman</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="blast_active" value="1" class="sr-only peer"
                                        x-model="isBlastActive" @change="autoSaveGatewayConfig()"
                                        {{ $isBlastActive ? 'checked' : '' }}>
                                    <div
                                        class="w-11 h-6 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]">
                                    </div>
                                </label>
                            </div>

                            <div>
                                <label
                                    class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 mb-1.5">Pilihan Jalur Siaran</label>
                                <select name="blast_driver"
                                    x-model="blastDriver" @change="autoSaveGatewayConfig()"
                                    class="w-full min-h-[46px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 font-medium transition-all">
                                    <option value="meta_cloud" {{ $blastDriver === 'meta_cloud' ? 'selected' : '' }}>
                                        Meta WhatsApp Cloud API (Resmi &amp; Sangat Stabil)
                                    </option>
                                    <option value="baileys" {{ $blastDriver === 'baileys' ? 'selected' : '' }}>
                                        Scan QR Baileys (Server Lokal - Bebas Biaya Template)
                                    </option>
                                    <option value="disabled" {{ $blastDriver === 'disabled' ? 'selected' : '' }}>
                                        Nonaktifkan Broadcast &amp; Pengingat Otomatis
                                    </option>
                                </select>
                            </div>
                            <div class="flex items-start gap-2 text-[11.5px] text-black/50 dark:text-white/50 leading-relaxed">
                                <i data-lucide="info" class="w-3.5 h-3.5 text-[#FF9500] shrink-0 mt-0.5"></i>
                                <span>Sistem telah menerapkan jeda pengiriman cerdas beberapa detik antar pesan agar pengiriman berjalan alami.</span>
                            </div>
                        </div>
                    </div>

                    {{-- PANDUAN LANGKAH-DEMI-LANGKAH SETUP META WHATSAPP CLOUD API --}}
                    @include('partials.whatsapp-meta-setup-guide', ['mode' => 'admin'])

                    {{-- Kredensial Meta WhatsApp Cloud API --}}
                    <div
                        class="p-5 sm:p-6 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06] space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="w-8 h-8 rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                                    <i data-lucide="cloud" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h4 class="text-[14px] font-bold text-black dark:text-white">Kredensial Meta WhatsApp Cloud API (Facebook Developers)</h4>
                                    <p class="text-[11.5px] text-black/55 dark:text-white/55">Wajib diisi jika memilih driver Meta Cloud API untuk OTP atau Broadcast</p>
                                </div>
                            </div>
                            <a href="https://developers.facebook.com/apps/" target="_blank" rel="noopener noreferrer"
                                class="text-[12px] font-semibold text-[#007AFF] hover:underline inline-flex items-center gap-1 self-start sm:self-center">
                                <span>Buka Meta Developer Portal</span>
                                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <div class="flex items-center justify-between mb-1.5">
                                    <label
                                        class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55">Meta Permanent Access Token (System User Token)</label>
                                    <button type="button" @click="showMetaToken = !showMetaToken"
                                        class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                        <span x-text="showMetaToken ? 'Sembunyikan' : 'Tampilkan Token'"></span>
                                    </button>
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

                            <div>
                                <label
                                    class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 mb-1.5">Phone Number ID</label>
                                <input type="text" name="meta_phone_number_id" x-model="metaPhoneId"
                                    placeholder="Contoh: 104523984712398"
                                    class="w-full min-h-[46px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] px-3.5 text-[16px] sm:text-[13px] font-mono text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all">
                            </div>

                            <div>
                                <label
                                    class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 mb-1.5">WhatsApp Business Account ID (WABA ID)</label>
                                <input type="text" name="meta_waba_id" x-model="metaWabaId"
                                    placeholder="Contoh: 109283746501928"
                                    class="w-full min-h-[46px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] px-3.5 text-[16px] sm:text-[13px] font-mono text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all">
                            </div>

                            <div>
                                <label
                                    class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 mb-1.5">Nama Template OTP Meta (Opsional)</label>
                                <input type="text" name="meta_otp_template"
                                    value="{{ $metaCreds['otp_template'] ?: 'cooca_otp' }}" placeholder="cooca_otp"
                                    class="w-full min-h-[46px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] px-3.5 text-[16px] sm:text-[13px] font-mono text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all">
                            </div>

                            <div class="flex items-center">
                                <div
                                    class="p-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.05] text-[11.5px] text-black/65 dark:text-white/65 leading-relaxed w-full flex items-start gap-2">
                                    <i data-lucide="sparkles" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"></i>
                                    <span><strong>Kuota Gratis:</strong> 1.000 Service Conversation per bulan gratis langsung dari Meta untuk setiap akun WABA.</span>
                                </div>
                            </div>

                            {{-- Action Button: Test & Verify Meta Credentials --}}
                            <div class="md:col-span-2 pt-1">
                                <div
                                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-[14px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 shadow-sm">
                                    <div>
                                        <div class="text-[13px] font-bold text-black dark:text-white">Uji &amp; Verifikasi Kredensial Meta</div>
                                        <div class="text-[11.5px] text-black/55 dark:text-white/55">Hubungkan ke Meta Graph API untuk memeriksa validitas token dan status nomor telepon resmi</div>
                                    </div>
                                    <button type="button" @click="verifyMetaCredentials()" :disabled="metaVerifyLoading"
                                        class="min-h-[40px] px-4 rounded-[10px] text-[12px] font-bold bg-[#007AFF]/12 hover:bg-[#007AFF]/20 text-[#007AFF] active:scale-[0.98] transition-all inline-flex items-center justify-center gap-1.5 shrink-0 disabled:opacity-50">
                                        <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin" x-show="metaVerifyLoading"></i>
                                        <i data-lucide="shield-check" class="w-3.5 h-3.5" x-show="!metaVerifyLoading"></i>
                                        <span x-text="metaVerifyLoading ? 'Memeriksa ke Meta...' : 'Uji Validitas Token Meta'"></span>
                                    </button>
                                </div>

                                {{-- Live Verification Result Card --}}
                                <div x-show="metaVerifyResult" x-transition
                                    class="mt-3 p-4 rounded-[14px] bg-[#34C759]/12 border border-[#34C759]/30 text-[12.5px] text-[#248A3D] dark:text-[#30D158] flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                                    <div class="flex items-center gap-2.5">
                                        <div
                                            class="w-8 h-8 rounded-full bg-[#34C759] text-white flex items-center justify-center shrink-0">
                                            <i data-lucide="check" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <div class="font-bold text-[13px]">Kredensial Meta Valid &amp; Siap Digunakan!</div>
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
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="submit"
                            class="min-h-[48px] px-7 rounded-[14px] text-[14px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] shadow-sm transition-all inline-flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Simpan</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- 3. SAMBUNGKAN WHATSAPP (SCAN QR) & MULTI-DEVICE POOL --}}
            <div class="space-y-5">

                {{-- Section Header --}}
                <div
                    class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-md border border-black/[0.06] dark:border-white/[0.08] shadow-sm p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div
                            class="w-11 h-11 sm:w-12 sm:h-12 rounded-[16px] bg-gradient-to-br from-[#25D366] to-[#128C7E] text-white flex items-center justify-center shadow-md shadow-[#25D366]/20 shrink-0">
                            <i data-lucide="qr-code" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h2 class="text-[17px] font-bold text-black dark:text-white tracking-tight">Sambungkan WhatsApp (Scan QR)</h2>
                            <p class="text-[12.5px] text-black/55 dark:text-white/55 mt-0.5">Hubungkan nomor WhatsApp via scan barcode untuk pesan pengingat dan siaran</p>
                        </div>
                    </div>

                    {{-- Action Button: Tambah Nomor Baru --}}
                    <div class="flex items-center gap-2.5 shrink-0">
                        <button type="button"
                            @click="showNewSessionModal = true; newSessionName = 'Nomor WhatsApp Admin ' + (sessions.length + 1);"
                            class="min-h-[44px] px-5 rounded-[14px] text-[13px] font-bold bg-[#25D366] hover:bg-[#1EBE5D] text-white shadow-sm active:scale-[0.98] transition-all inline-flex items-center gap-2">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>Tambah Nomor</span>
                        </button>
                    </div>
                </div>

                {{-- Banner Edukasi: Rotasi Acak Otomatis (Anti-Blokir) --}}
                <div
                    class="rounded-[20px] p-4 sm:p-5 bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] flex items-start gap-3.5">
                    <div
                        class="w-9 h-9 rounded-[12px] bg-[#007AFF]/12 text-[#007AFF] flex items-center justify-center shrink-0 mt-0.5 shadow-inner">
                        <i data-lucide="shuffle" class="w-4 h-4"></i>
                    </div>
                    <div class="text-[12.5px] leading-relaxed text-black/75 dark:text-white/75 space-y-1">
                        <div class="font-bold text-black dark:text-white">Rotasi Acak Otomatis (Sistem Multi-Device Anti-Blokir)</div>
                        <p>
                            Ketika menggunakan server Baileys lokal, setiap pesan OTP keamanan, broadcast siaran promosi,
                            atau invoice pengingat langganan akan <strong>dikirim secara acak dari nomor-nomor WhatsApp yang
                                sedang aktif terhubung</strong> di bawah. Menghubungkan beberapa nomor WhatsApp sekaligus
                            akan membagi rata volume pengiriman dan melindungi akun bisnis Anda dari pemblokiran.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6">
                    {{-- Kolom Kiri: Daftar Nomor WhatsApp (Multi-Session Bento Cards) (7 Kolom) --}}
                    <div class="lg:col-span-7 space-y-4">

                        {{-- Empty State jika belum ada nomor terhubung sama sekali --}}
                        <div x-show="connectedSessionsCount === 0 && (!sessions.length || (sessions.length === 1 && sessions[0].status === 'disconnected'))"
                            class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-md border border-black/[0.06] dark:border-white/[0.08] shadow-sm p-8 sm:p-10 text-center space-y-5">
                            <div
                                class="w-18 h-18 sm:w-20 sm:h-20 rounded-[24px] bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center mx-auto text-black/30 dark:text-white/30 shadow-inner">
                                <i data-lucide="smartphone" class="w-9 h-9 sm:w-10 sm:h-10"></i>
                            </div>
                            <div class="max-w-md mx-auto">
                                <h3 class="text-black dark:text-white font-bold text-[18px]">WhatsApp Admin Belum Terhubung</h3>
                                <p class="text-black/55 dark:text-white/55 text-[13px] mt-1.5 leading-relaxed">Mulai sesi baru untuk menampilkan kode QR dan menghubungkan nomor WhatsApp resmi Cooca.</p>
                            </div>
                            <button type="button" @click="startDefaultSession()" :disabled="isLoading"
                                class="min-h-[48px] px-8 rounded-[16px] text-[15px] font-bold text-white bg-[#25D366] hover:bg-[#1EBE5D] active:scale-[0.98] shadow-[0_6px_20px_rgba(37,211,102,0.35)] inline-flex items-center gap-2.5 transition-all">
                                <i data-lucide="loader-2" class="w-5 h-5 animate-spin" x-show="isLoading"></i>
                                <i data-lucide="qr-code" class="w-5 h-5" x-show="!isLoading"></i>
                                <span
                                    x-text="isLoading ? 'Menghubungkan ke Gateway WA...' : 'Mulai & Tampilkan Kode QR'">Mulai &amp; Tampilkan Kode QR</span>
                            </button>
                        </div>

                        {{-- Daftar Nomor WhatsApp (Multi-Session Cards) --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <template x-for="(s, idx) in sessions" :key="s.session_id">
                                <div
                                    class="rounded-[20px] bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-md border border-black/[0.07] dark:border-white/[0.08] p-4 sm:p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between space-y-4">
                                    <div>
                                        <div class="flex items-start justify-between gap-2.5">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <div class="w-10 h-10 rounded-[12px] flex items-center justify-center shrink-0 shadow-inner"
                                                    :class="s.status === 'connected' ? 'bg-[#34C759]/15 text-[#34C759]' : (s
                                                        .status === 'scan_qr' ? 'bg-[#FF9500]/15 text-[#FF9500]' :
                                                        'bg-black/5 dark:bg-white/10 text-black/40 dark:text-white/40'
                                                        )">
                                                    <i data-lucide="smartphone" class="w-5 h-5"></i>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="text-[13.5px] font-bold text-black dark:text-white truncate"
                                                        x-text="s.name || ('Nomor Admin ' + (idx + 1))"></div>
                                                    <div class="text-[11px] font-mono text-black/50 dark:text-white/50 truncate"
                                                        x-text="s.session_id"></div>
                                                </div>
                                            </div>

                                            {{-- Status Badge --}}
                                            <div class="shrink-0">
                                                <template x-if="s.status === 'connected'">
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25">
                                                        <span>Terhubung</span>
                                                    </span>
                                                </template>
                                                <template x-if="s.status === 'scan_qr'">
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A] border border-[#FF9500]/25">
                                                        <span>Pindai QR</span>
                                                    </span>
                                                </template>
                                                <template x-if="s.status !== 'connected' && s.status !== 'scan_qr'">
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50 border border-black/10 dark:border-white/10">
                                                        <span>Terputus</span>
                                                    </span>
                                                </template>
                                            </div>
                                        </div>

                                        {{-- Nomor Telepon --}}
                                        <div
                                            class="mt-3.5 p-3 rounded-[13px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.05] flex items-center justify-between gap-2">
                                            <div>
                                                <div
                                                    class="text-[10px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Nomor Ponsel WhatsApp</div>
                                                <div class="text-[14px] font-bold font-mono text-black dark:text-white mt-0.5"
                                                    x-text="s.phone_number ? '+' + s.phone_number : (s.status === 'connected' ? 'Aktif' : 'Belum Tersambung')">
                                                </div>
                                            </div>
                                            <template x-if="s.status === 'connected'">
                                                <i data-lucide="check-circle-2" class="w-5 h-5 text-[#34C759] shrink-0"></i>
                                            </template>
                                        </div>

                                        {{-- Toggle Ikut Pool Acak --}}
                                        <div class="mt-2.5 flex items-center justify-between text-[11.5px] px-1">
                                            <span class="text-black/65 dark:text-white/65 font-medium">Ikut Acak Kirim (Pool)</span>
                                            <button type="button" @click="toggleSessionPool(s.session_id)"
                                                class="text-[11px] font-bold px-2 py-0.5 rounded-[8px] transition-all"
                                                :class="s.is_active ? 'bg-[#007AFF]/12 text-[#007AFF]' :
                                                    'bg-black/5 dark:bg-white/10 text-black/40 dark:text-white/40'">
                                                <span x-text="s.is_active ? 'Aktif di Pool' : 'Dikecualikan'"></span>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Aksi Bawah --}}
                                    <div
                                        class="pt-2 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-2">
                                        <div>
                                            <template x-if="s.status !== 'connected'">
                                                <button type="button" @click="openScanModal(s)"
                                                    class="min-h-[38px] px-3.5 rounded-[10px] text-[12px] font-bold bg-[#25D366] hover:bg-[#1EBE5D] text-white active:scale-[0.98] transition-all inline-flex items-center gap-1.5 shadow-sm">
                                                    <i data-lucide="qr-code" class="w-3.5 h-3.5"></i>
                                                    <span>Pindai QR</span>
                                                </button>
                                            </template>
                                            <template x-if="s.status === 'connected'">
                                                <button type="button" @click="confirmDisconnectTarget(s)"
                                                    class="min-h-[38px] px-3 rounded-[10px] text-[12px] font-semibold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 active:scale-[0.98] transition-all inline-flex items-center gap-1">
                                                    <i data-lucide="unplug" class="w-3.5 h-3.5"></i>
                                                    <span>Putus</span>
                                                </button>
                                            </template>
                                        </div>

                                        <template x-if="sessions.length > 1">
                                            <button type="button" @click="deleteTarget(s)" title="Hapus Nomor Ini"
                                                class="w-9 h-9 rounded-[10px] text-black/40 dark:text-white/40 hover:text-[#FF3B30] hover:bg-[#FF3B30]/10 transition-all flex items-center justify-center">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                    </div>

                    {{-- Kolom Kanan: Panduan 3 Langkah & Form Uji Kirim (5 Kolom) --}}
                    <div class="lg:col-span-5 space-y-5 sm:space-y-6">

                        {{-- Card Panduan 3 Langkah Ramah Boomer --}}
                        <div
                            class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-md border border-black/[0.06] dark:border-white/[0.08] shadow-sm p-5 sm:p-6 space-y-4">
                            <div class="flex items-center gap-2.5 text-black dark:text-white font-bold text-[15px]">
                                <i data-lucide="help-circle" class="w-5 h-5 text-[#007AFF]"></i>
                                <span>Cara Menghubungkan WhatsApp</span>
                            </div>
                            <div class="space-y-3 text-[13px]">
                                <div
                                    class="flex items-start gap-3 p-3 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04]">
                                    <span
                                        class="w-6 h-6 rounded-full bg-[#007AFF] text-white flex items-center justify-center font-bold text-[11px] shrink-0">1</span>
                                    <div class="text-black/75 dark:text-white/75">Buka aplikasi <strong>WhatsApp</strong> di ponsel resmi admin Cooca.</div>
                                </div>
                                <div
                                    class="flex items-start gap-3 p-3 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04]">
                                    <span
                                        class="w-6 h-6 rounded-full bg-[#007AFF] text-white flex items-center justify-center font-bold text-[11px] shrink-0">2</span>
                                    <div class="text-black/75 dark:text-white/75">Ketuk ikon titik tiga (Android) atau <strong>Pengaturan</strong> (iPhone) &gt; pilih <strong>Perangkat Tertaut</strong>.</div>
                                </div>
                                <div
                                    class="flex items-start gap-3 p-3 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04]">
                                    <span
                                        class="w-6 h-6 rounded-full bg-[#007AFF] text-white flex items-center justify-center font-bold text-[11px] shrink-0">3</span>
                                    <div class="text-black/75 dark:text-white/75">Ketuk <strong>Tautkan Perangkat</strong>, lalu arahkan kamera ponsel ke <strong>Kode Barcode QR</strong> yang muncul di layar.</div>
                                </div>
                            </div>
                        </div>

                        {{-- Card Uji Kirim Pesan --}}
                        <div
                            class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-md border border-black/[0.06] dark:border-white/[0.08] shadow-sm p-5 sm:p-6 space-y-4">
                            <div>
                                <h3 class="text-[15px] font-bold text-black dark:text-white flex items-center gap-2">
                                    <i data-lucide="send" class="w-4 h-4 text-[#25D366]"></i>
                                    <span>Uji Kirim Pesan (Live Diagnostic)</span>
                                </h3>
                                <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Kirimkan pesan percobaan untuk memastikan bot merespons dengan lancar</p>
                            </div>

                            <div class="space-y-3">
                                <div>
                                    <label
                                        class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 mb-1.5">Nomor Ponsel Tujuan</label>
                                    <input x-model="testPhone" type="tel"
                                        placeholder="Contoh: 081234567890 atau 6281234567890"
                                        class="w-full min-h-[46px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#25D366]/50 transition-all">
                                </div>

                                <div>
                                    <label
                                        class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 mb-1.5">Isi Pesan Uji Coba</label>
                                    <textarea x-model="testMessage" rows="3" placeholder="Pesan tes..."
                                        class="w-full bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] p-3 text-[16px] sm:text-[13px] text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#25D366]/50 transition-all resize-none"></textarea>
                                </div>

                                <button type="button" @click="sendTest()"
                                    :disabled="testLoading || (status !== 'connected' && otpDriver !== 'meta_cloud' &&
                                        blastDriver !== 'meta_cloud')"
                                    class="w-full min-h-[48px] rounded-[14px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] disabled:opacity-50 disabled:pointer-events-none active:scale-[0.98] transition-all inline-flex items-center justify-center gap-2 shadow-sm">
                                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="testLoading"></i>
                                    <i data-lucide="send" class="w-4 h-4" x-show="!testLoading"></i>
                                    <span x-text="testLoading ? 'Mengirim pesan tes...' : 'Kirim'"></span>
                                </button>

                                <div x-show="testResult" class="p-3.5 rounded-[12px] text-[12px] font-medium"
                                    :class="testOk ?
                                        'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20' :
                                        'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A] border border-[#FF3B30]/20'"
                                    x-text="testResult"></div>
                            </div>
                        </div>

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
                    @if ($blastDriver === 'baileys')
                        <div
                            class="p-3.5 rounded-[14px] bg-[#007AFF]/8 border border-[#007AFF]/20 text-[12px] text-black/75 dark:text-white/75 space-y-1">
                            <div class="flex items-center gap-1.5 font-bold text-[#007AFF]">
                                <i data-lucide="zap" class="w-4 h-4"></i>
                                <span>Gateway Scan QR Baileys Aktif</span>
                            </div>
                            <p class="text-[11.5px] leading-relaxed">
                                Pesan broadcast dikirim secara bertahap dengan jeda cerdas di latar belakang untuk menjaga keandalan pengiriman.
                            </p>
                        </div>
                    @elseif ($blastDriver === 'meta_cloud')
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
                            <strong>Kanal Broadcast Nonaktif:</strong> Aktifkan jalur broadcast di tab Status &amp; Sesi QR terlebih dahulu.
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
        {{-- MODAL SCAN BARCODE QR (MULTI-SESSION REAL-TIME SCANNER - APPLE BOTTOM SHEET) --}}
        {{-- ========================================================================= --}}
        <div x-show="showQrModal" x-cloak
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/65 backdrop-blur-md"
            x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div @click.away="closeQrModal()"
                class="w-full max-w-lg bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[28px] shadow-2xl border border-black/10 dark:border-white/10 p-5 sm:p-7 space-y-4 max-h-[92vh] overflow-y-auto">

                {{-- Mobile Drag Handle Bar --}}
                <div class="w-12 h-1.5 rounded-full bg-black/20 dark:bg-white/20 mx-auto -mt-1 mb-2 sm:hidden"></div>

                {{-- Modal Header --}}
                <div
                    class="flex items-center justify-between pb-3.5 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-[#25D366]/15 text-[#1A7341] dark:text-[#30D158] flex items-center justify-center shrink-0">
                            <i data-lucide="qr-code" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-[16px] font-bold text-black dark:text-white"
                                x-text="modalSession?.name || 'Pindai Kode QR WhatsApp'"></h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50"
                                x-text="modalSession?.session_id ? 'ID Sesi: ' + modalSession.session_id : 'Gateway Baileys Lokal'">
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="closeQrModal()"
                        class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60 hover:bg-black/10 dark:hover:bg-white/15 transition-all flex items-center justify-center">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                {{-- Modal Body: QR Scanner Display --}}
                <div class="text-center space-y-4 py-1">
                    {{-- State 1: Connected --}}
                    <div x-show="modalStatus === 'connected'" class="py-6 space-y-3">
                        <div
                            class="w-16 h-16 rounded-full bg-[#34C759]/20 text-[#34C759] flex items-center justify-center mx-auto shadow-lg shadow-[#34C759]/20">
                            <i data-lucide="check" class="w-8 h-8"></i>
                        </div>
                        <h4 class="text-[18px] font-bold text-black dark:text-white">WhatsApp Berhasil Terhubung!</h4>
                        <p class="text-[13px] text-black/60 dark:text-white/60 max-w-sm mx-auto">
                            Nomor telah aktif dan langsung dimasukkan ke dalam rotasi acak pesan sistem.
                        </p>
                        <button type="button" @click="closeQrModal()"
                            class="mt-2 min-h-[44px] px-6 rounded-[12px] text-[13px] font-bold bg-[#34C759] text-white hover:bg-[#2EB14F] shadow-sm transition-all">
                            Selesai &amp; Tutup
                        </button>
                    </div>

                    {{-- State 2: Scan QR Display --}}
                    <div x-show="modalStatus !== 'connected'">
                        <div class="flex justify-center my-2">
                            <div x-show="modalLoading && !modalQrDataUrl"
                                class="w-64 h-64 rounded-[22px] bg-black/[0.03] dark:bg-white/[0.05] border border-dashed border-black/15 dark:border-white/20 flex flex-col items-center justify-center gap-3">
                                <i data-lucide="loader-2" class="w-8 h-8 text-[#25D366] animate-spin"></i>
                                <span class="text-[12.5px] text-black/50 dark:text-white/50 font-medium">Meminta kode QR dari gateway...</span>
                            </div>
                            <div x-show="modalQrDataUrl" class="space-y-2">
                                <div
                                    class="bg-white p-4 rounded-[24px] shadow-[0_16px_36px_rgba(0,0,0,0.14)] border border-black/10 transition-all inline-block">
                                    <img :src="modalQrDataUrl" alt="WhatsApp QR Code"
                                        class="w-56 h-56 rounded-[14px] block mx-auto">
                                </div>
                                <div>
                                    <button type="button" @click="refreshSessionQr(modalSession?.session_id)"
                                        class="px-3.5 py-1.5 rounded-[10px] text-[11.5px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition-all inline-flex items-center gap-1.5">
                                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                                        <span>Segarkan Kode QR</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Petunjuk Singkat 3 Langkah --}}
                        <div
                            class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] text-left text-[12px] space-y-1.5 border border-black/[0.04] dark:border-white/[0.06]">
                            <div class="font-bold text-black dark:text-white">Petunjuk Hubungkan WhatsApp:</div>
                            <p class="text-black/65 dark:text-white/65">1. Buka <strong>WhatsApp</strong> di HP Anda &gt; Menu Titik Tiga / Pengaturan</p>
                            <p class="text-black/65 dark:text-white/65">2. Pilih <strong>Perangkat Tertaut</strong> &gt; <strong>Tautkan Perangkat</strong></p>
                            <p class="text-black/65 dark:text-white/65">3. Arahkan kamera ke kode barcode di atas. Sistem otomatis mendeteksi koneksi!</p>
                        </div>

                        <div class="flex items-center justify-center gap-2.5 pt-2">
                            <button type="button" @click="fetchSessionQr(modalSession?.session_id)"
                                :disabled="modalLoading"
                                class="min-h-[42px] px-4 rounded-[12px] text-[12px] font-semibold bg-black/[0.06] dark:bg-white/[0.1] text-black/80 dark:text-white/80 hover:bg-black/[0.09] dark:hover:bg-white/[0.15] transition-all inline-flex items-center gap-1.5">
                                <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin" x-show="modalLoading"></i>
                                <i data-lucide="refresh-cw" class="w-3.5 h-3.5" x-show="!modalLoading"></i>
                                <span>Segarkan Barcode QR</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL TAMBAH NOMOR WHATSAPP BARU (APPLE BOTTOM SHEET)                    --}}
        {{-- ========================================================================= --}}
        <div x-show="showNewSessionModal" x-cloak
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/65 backdrop-blur-md"
            x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div @click.away="showNewSessionModal = false"
                class="w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[26px] shadow-2xl border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4">

                {{-- Mobile Drag Handle Bar --}}
                <div class="w-12 h-1.5 rounded-full bg-black/20 dark:bg-white/20 mx-auto -mt-1 mb-2 sm:hidden"></div>

                <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="w-9 h-9 rounded-[12px] bg-[#25D366]/15 text-[#1A7341] dark:text-[#30D158] flex items-center justify-center shrink-0">
                            <i data-lucide="plus" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">Tambah Nomor WhatsApp</h3>
                    </div>
                    <button type="button" @click="showNewSessionModal = false"
                        class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60 hover:bg-black/10 dark:hover:bg-white/15 transition-all flex items-center justify-center">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="space-y-3">
                    <p class="text-[12.5px] text-black/60 dark:text-white/60 leading-relaxed">
                        Beri nama pengenal untuk nomor ini agar mudah dibedakan (misal: <em>WA Admin CS 2</em> atau <em>Nomor Blast Promosi</em>).
                    </p>
                    <div>
                        <label
                            class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 mb-1.5">Nama Perangkat / Label</label>
                        <input type="text" x-model="newSessionName" placeholder="Contoh: Nomor WhatsApp Admin 2"
                            class="w-full min-h-[46px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#25D366]/50 transition-all">
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="button" @click="showNewSessionModal = false"
                        class="w-1/2 min-h-[46px] rounded-[12px] text-[13px] font-semibold bg-black/[0.06] dark:bg-white/[0.08] text-black dark:text-white hover:bg-black/[0.09] transition-all">
                        Batal
                    </button>
                    <button type="button" @click="createSessionSubmit()" :disabled="creatingSession"
                        class="w-1/2 min-h-[46px] rounded-[12px] text-[13px] font-bold bg-[#25D366] hover:bg-[#1EBE5D] text-white active:scale-[0.98] transition-all inline-flex items-center justify-center gap-1.5 disabled:opacity-50 shadow-sm">
                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="creatingSession"></i>
                        <i data-lucide="qr-code" class="w-4 h-4" x-show="!creatingSession"></i>
                        <span x-text="creatingSession ? 'Membuat Sesi...' : 'Lanjut Scan QR'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- MODAL KONFIRMASI PUTUS SESI (APPLE BOTTOM SHEET) --}}
        <div x-show="confirmDisconnect" x-cloak
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/60 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">
            <div @click.away="confirmDisconnect = false"
                class="w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[24px] shadow-2xl border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4">

                {{-- Mobile Drag Handle Bar --}}
                <div class="w-12 h-1.5 rounded-full bg-black/20 dark:bg-white/20 mx-auto -mt-1 mb-2 sm:hidden"></div>

                <div
                    class="w-12 h-12 rounded-[16px] bg-[#FF3B30]/15 text-[#FF3B30] flex items-center justify-center mx-auto">
                    <i data-lucide="unplug" class="w-6 h-6"></i>
                </div>
                <div class="text-center">
                    <h3 class="text-[17px] font-bold text-black dark:text-white">Putus Sesi WhatsApp Admin?</h3>
                    <p class="text-[13px] text-black/60 dark:text-white/60 mt-1.5"
                        x-text="sessionToDisconnect ? 'Anda akan memutus koneksi sesi ' + (sessionToDisconnect.name || sessionToDisconnect.session_id) + '.' : 'Setelah sesi diputus, pengiriman pesan otomatis akan dialihkan ke nomor lain yang aktif.'">
                    </p>
                </div>
                <div
                    class="p-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] text-[12px] text-black/60 dark:text-white/60 flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                    <span>Tenang: Riwayat pesan terkirim dan template Anda tetap aman tersimpan.</span>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="button" @click="confirmDisconnect = false"
                        class="w-1/2 min-h-[46px] rounded-[12px] text-[13px] font-semibold bg-black/[0.06] dark:bg-white/[0.08] text-black dark:text-white hover:bg-black/[0.09] transition-all">
                        Batal
                    </button>
                    <button type="button" @click="confirmDisconnect = false; disconnectTargetSession();"
                        class="w-1/2 min-h-[46px] rounded-[12px] text-[13px] font-bold bg-[#FF3B30] text-white hover:bg-[#E02B20] transition-all shadow-sm">
                        Putus Sesi
                    </button>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
        <script>
            function adminWaCenter() {
                return {
                    activeTab: @json($initialTab),
                    status: @json($liveStatus ?? 'disconnected'),
                    phone: @json($initialPhone),
                    qrDataUrl: @json($qrDataUrl ?? null),
                    isLoading: false,
                    pollTimer: null,
                    confirmDisconnect: false,
                    sessionToDisconnect: null,
                    blastMessage: '',

                    // Multi-Session Pool
                    sessions: @json($adminSessions ?? []),
                    showQrModal: false,
                    modalSession: null,
                    modalQrDataUrl: null,
                    modalStatus: 'disconnected',
                    modalLoading: false,
                    modalError: null,
                    modalPollTimer: null,
                    showNewSessionModal: false,
                    newSessionName: '',
                    creatingSession: false,

                    testPhone: '',
                    testMessage: 'Pesan uji coba resmi dari WhatsApp Admin Gateway Cooca Platform.',
                    testLoading: false,
                    testResult: '',
                    testOk: false,

                    // Dual Gateway Configuration & Auto-Save
                    otpDriver: @json($otpDriver ?? 'baileys'),
                    blastDriver: @json($blastDriver ?? 'baileys'),
                    isOtpActive: @json((bool) $isOtpActive),
                    isBlastActive: @json((bool) $isBlastActive),
                    gatewaySaving: false,
                    gatewaySavedToast: false,

                    metaToken: @json($metaCreds['token'] ?? ''),
                    metaPhoneId: @json($metaCreds['phone_number_id'] ?? ''),
                    metaWabaId: @json($metaCreds['waba_id'] ?? ''),
                    showMetaToken: false,
                    metaVerifyLoading: false,
                    metaVerifyResult: null,
                    metaVerifyError: null,

                    refreshIcons() {
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined' && lucide.createIcons) {
                                lucide.createIcons();
                            }
                        });
                    },

                    async autoSaveGatewayConfig() {
                        this.gatewaySaving = true;
                        try {
                            const form = document.getElementById('gatewayConfigForm');
                            const bodyObj = {
                                otp_driver: this.otpDriver,
                                blast_driver: this.blastDriver,
                                otp_active: this.isOtpActive ? 1 : 0,
                                blast_active: this.isBlastActive ? 1 : 0,
                                meta_token: this.metaToken,
                                meta_phone_number_id: this.metaPhoneId,
                                meta_waba_id: this.metaWabaId,
                                meta_otp_template: form?.querySelector('[name=meta_otp_template]')?.value || 'cooca_otp'
                            };

                            const response = await fetch('{{ route('admin.whatsapp.config') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                                },
                                body: JSON.stringify(bodyObj)
                            });

                            const data = await response.json();
                            if (data.success) {
                                this.gatewaySavedToast = true;
                                setTimeout(() => {
                                    this.gatewaySavedToast = false;
                                }, 3000);
                            }
                        } catch (e) {
                            // Silent fail with fallback to manual save button
                        } finally {
                            this.gatewaySaving = false;
                            this.refreshIcons();
                        }
                    },

                    get connectedSessionsCount() {
                        return (this.sessions || []).filter(s => s.status === 'connected').length;
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
                        this.fetchSessions();
                        this.refreshIcons();
                    },

                    setTab(tab) {
                        this.activeTab = tab;
                        const url = new URL(window.location.href);
                        url.searchParams.set('tab', tab);
                        window.history.replaceState({}, '', url);
                        if (tab === 'connection') {
                            this.checkStatus();
                            this.fetchSessions();
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
                            this.refreshIcons();
                        }
                    },

                    applyStatus(data) {
                        const rawStatus = String(data.status || '').toLowerCase();
                        this.status = rawStatus === 'connected' ? 'connected' : (rawStatus === 'scan_qr' || data.qrDataUrl ?
                            'scan_qr' : 'disconnected');
                        this.phone = data.phone || this.phone;
                        if (data.qrDataUrl) {
                            this.qrDataUrl = data.qrDataUrl;
                        }
                        this.refreshIcons();
                    },

                    async fetchSessions() {
                        try {
                            const response = await fetch('{{ route('admin.whatsapp.sessions.index') }}', {
                                headers: {
                                    'Accept': 'application/json'
                                }
                            });
                            const data = await response.json();
                            if (data.success && Array.isArray(data.sessions)) {
                                this.sessions = data.sessions;
                            }
                        } catch (e) {} finally {
                            this.refreshIcons();
                        }
                    },

                    async startDefaultSession() {
                        let target = this.sessions[0];
                        if (!target) {
                            await this.createSessionSubmit('Nomor Admin Utama');
                            return;
                        }
                        this.openScanModal(target);
                    },

                    async startSession() {
                        // Backward-compatible for index button & tests
                        await this.startDefaultSession();
                    },

                    async createSessionSubmit(customName = null) {
                        const name = customName || this.newSessionName.trim() || ('Nomor WhatsApp Admin ' + (this.sessions
                            .length + 1));
                        this.creatingSession = true;
                        try {
                            const response = await fetch('{{ route('admin.whatsapp.sessions.store') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                                },
                                body: JSON.stringify({
                                    name
                                })
                            });
                            const data = await response.json();
                            if (data.success && data.session) {
                                this.sessions.push(data.session);
                                this.showNewSessionModal = false;
                                this.newSessionName = '';
                                this.openScanModal(data.session);
                            } else {
                                alert(data.error || 'Gagal menambahkan sesi nomor WhatsApp.');
                            }
                        } catch (e) {
                            alert('Kesalahan jaringan saat membuat sesi WhatsApp.');
                        } finally {
                            this.creatingSession = false;
                            this.refreshIcons();
                        }
                    },

                    openScanModal(session) {
                        this.modalSession = session;
                        this.modalQrDataUrl = session.qr_data_url || null;
                        this.modalStatus = session.status || 'scan_qr';
                        this.modalLoading = !this.modalQrDataUrl;
                        this.modalError = null;
                        this.showQrModal = true;
                        this.refreshIcons();

                        // Pastikan session sudah di-start di wa-server
                        fetch('{{ route('admin.whatsapp.start') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                            },
                            body: JSON.stringify({ sessionId: session.session_id })
                        }).catch(() => {});

                        this.fetchSessionQr(session.session_id);
                        this.startSessionPolling(session.session_id);
                    },

                    async fetchSessionQr(sessionId) {
                        if (!sessionId) return;
                        try {
                            const response = await fetch(`/admin/whatsapp/sessions/${sessionId}/qr`, {
                                headers: {
                                    'Accept': 'application/json'
                                }
                            });
                            const data = await response.json();
                            if (data.qrDataUrl) {
                                this.modalQrDataUrl = data.qrDataUrl;
                                this.modalLoading = false;
                            }
                            if (data.status) {
                                const raw = String(data.status).toLowerCase();
                                this.modalStatus = raw === 'connected' ? 'connected' : (raw === 'scan_qr' ? 'scan_qr' :
                                    'disconnected');

                                // Update status di list sessions lokal
                                const idx = this.sessions.findIndex(s => s.session_id === sessionId);
                                if (idx !== -1) {
                                    this.sessions[idx].status = this.modalStatus;
                                    if (data.phone) {
                                        this.sessions[idx].phone_number = data.phone;
                                    }
                                    if (data.qrDataUrl) {
                                        this.sessions[idx].qr_data_url = data.qrDataUrl;
                                    }
                                }
                                if (this.modalStatus === 'connected') {
                                    this.status = 'connected';
                                    this.modalLoading = false;
                                    this.stopSessionPolling();
                                }
                            }
                        } catch (e) {
                            this.modalError = 'Gagal memuat barcode QR.';
                        } finally {
                            this.refreshIcons();
                        }
                    },

                    async refreshSessionQr(sessionId) {
                        if (!sessionId) return;
                        this.modalLoading = true;
                        this.modalQrDataUrl = null;
                        try {
                            await fetch('{{ route('admin.whatsapp.start') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                                },
                                body: JSON.stringify({ sessionId })
                            });
                            await this.fetchSessionQr(sessionId);
                        } catch (e) {
                            this.modalError = 'Gagal menyegarkan kode QR.';
                        } finally {
                            this.refreshIcons();
                        }
                    },

                    startSessionPolling(sessionId) {
                        this.stopSessionPolling();
                        this.modalPollTimer = setInterval(() => {
                            this.fetchSessionQr(sessionId);
                        }, 2500);
                    },

                    stopSessionPolling() {
                        if (this.modalPollTimer) {
                            clearInterval(this.modalPollTimer);
                            this.modalPollTimer = null;
                        }
                    },

                    closeQrModal() {
                        this.showQrModal = false;
                        this.stopSessionPolling();
                        this.fetchSessions();
                        this.checkStatus();
                        this.refreshIcons();
                    },

                    confirmDisconnectTarget(session) {
                        this.sessionToDisconnect = session;
                        this.confirmDisconnect = true;
                        this.refreshIcons();
                    },

                    async disconnectTargetSession() {
                        const target = this.sessionToDisconnect || this.sessions[0];
                        if (!target) return;

                        try {
                            await fetch(`/admin/whatsapp/sessions/${target.session_id}/disconnect`, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                                }
                            });

                            // Update lokal
                            const idx = this.sessions.findIndex(s => s.session_id === target.session_id);
                            if (idx !== -1) {
                                this.sessions[idx].status = 'disconnected';
                                this.sessions[idx].phone_number = null;
                            }
                            this.sessionToDisconnect = null;
                            this.checkStatus();
                        } catch (e) {
                            alert('Gagal memutus sesi WhatsApp.');
                        } finally {
                            this.refreshIcons();
                        }
                    },

                    async disconnectWa() {
                        // Backward-compatible for existing tests & modals
                        await this.disconnectTargetSession();
                    },

                    async deleteTarget(session) {
                        if (!confirm(`Hapus nomor '${session.name || session.session_id}' dari daftar WhatsApp Admin?`)) {
                            return;
                        }

                        try {
                            const response = await fetch(`/admin/whatsapp/sessions/${session.session_id}`, {
                                method: 'DELETE',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                                }
                            });
                            const data = await response.json();
                            if (data.success) {
                                this.sessions = this.sessions.filter(s => s.session_id !== session.session_id);
                                this.checkStatus();
                            } else {
                                alert(data.error || 'Gagal menghapus nomor.');
                            }
                        } catch (e) {
                            alert('Kesalahan jaringan saat menghapus nomor.');
                        } finally {
                            this.refreshIcons();
                        }
                    },

                    async toggleSessionPool(sessionId) {
                        try {
                            const response = await fetch(`/admin/whatsapp/sessions/${sessionId}/toggle-active`, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                                }
                            });
                            const data = await response.json();
                            if (data.success) {
                                const idx = this.sessions.findIndex(s => s.session_id === sessionId);
                                if (idx !== -1) {
                                    this.sessions[idx].is_active = data.is_active;
                                }
                            }
                        } catch (e) {} finally {
                            this.refreshIcons();
                        }
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
                                'Gagal: ' + (data.error || 'Gateway WhatsApp tidak merespons.'));
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
@endsection
