@extends('layouts.admin')
@section('title', 'Media Sosial Platform Admin Center - COOCA')

@section('content')
    <div class="space-y-6 max-w-7xl w-full min-w-0 mx-auto pb-28 lg:pb-10" x-data="adminSocialCenter()" x-init="init()">

        {{-- 1. BENTO HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 min-w-0">
            <div class="flex items-center gap-3.5 min-w-0 flex-1">
                <div class="w-12 h-12 rounded-[18px] bg-gradient-to-br from-[#1877F2] via-[#E1306C] to-[#000000] flex items-center justify-center shadow-md shadow-[#1877F2]/20 shrink-0 text-white">
                    <i data-lucide="share-2" class="w-6 h-6"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h1 class="text-[20px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight truncate">Media Sosial Platform Admin Center</h1>
                    <p class="text-[12.5px] sm:text-[13px] text-black/55 dark:text-white/55 mt-0.5 truncate">Pusat publikasi konten resmi Cooca, kotak masuk interaksi, integrasi provider, dan pengawasan merchant</p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="px-3.5 py-1.5 rounded-full text-[12px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                    <span>Graph API {{ $platform['graph_version'] }}</span>
                </span>
                <button type="button" @click="activeTab = 'posts'; openCreatePostModal = true"
                    class="min-h-[38px] px-4 rounded-[11px] text-[12.5px] font-bold text-white bg-gradient-to-r from-[#1877F2] to-[#E1306C] hover:opacity-95 active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-sm">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Buat Postingan Cooca</span>
                </button>
                <a href="{{ route('admin.settings.index', ['tab' => 'social']) }}"
                    class="min-h-[38px] px-3.5 rounded-[11px] text-[12.5px] font-semibold text-black dark:text-white bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-sm">
                    <i data-lucide="sliders" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Pengaturan Terpadu</span>
                    <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 opacity-70"></i>
                </a>
            </div>
        </div>

        {{-- 2. STATS KPI OVERVIEW --}}
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 sm:gap-4">
            <div class="p-4 sm:p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm space-y-1">
                <span class="text-[11.5px] font-semibold uppercase text-black/50 dark:text-white/50">Postingan Platform</span>
                <p class="text-[22px] sm:text-[26px] font-bold text-[#007AFF] tabular-nums tracking-tight">{{ number_format($platformPosts->total()) }}</p>
            </div>
            <div class="p-4 sm:p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm space-y-1">
                <span class="text-[11.5px] font-semibold uppercase text-black/50 dark:text-white/50">Komentar Masuk</span>
                <p class="text-[22px] sm:text-[26px] font-bold text-[#AF52DE] tabular-nums tracking-tight">{{ number_format($platformComments->total()) }}</p>
            </div>
            <div class="p-4 sm:p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm space-y-1">
                <span class="text-[11.5px] font-semibold uppercase text-black/50 dark:text-white/50">Merchant Terhubung</span>
                <p class="text-[22px] sm:text-[26px] font-bold text-black dark:text-white tabular-nums tracking-tight">{{ number_format($summary['total_connected_merchants']) }}</p>
            </div>
            <div class="p-4 sm:p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm space-y-1">
                <span class="text-[11.5px] font-semibold uppercase text-black/50 dark:text-white/50">Halaman Facebook</span>
                <p class="text-[22px] sm:text-[26px] font-bold text-[#1877F2] tabular-nums tracking-tight">{{ number_format($summary['facebook_pages_count']) }}</p>
            </div>
            <div class="p-4 sm:p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm space-y-1">
                <span class="text-[11.5px] font-semibold uppercase text-black/50 dark:text-white/50">Instagram Bisnis</span>
                <p class="text-[22px] sm:text-[26px] font-bold text-[#E1306C] tabular-nums tracking-tight">{{ number_format($summary['instagram_accounts_count']) }}</p>
            </div>
        </div>

        {{-- 3. SEGMENTED NAVIGATION TABS --}}
        <div class="p-1.5 bg-black/[0.04] dark:bg-white/[0.06] rounded-[16px] flex items-center gap-1.5 overflow-x-auto shadow-inner">
            <button type="button" @click="activeTab = 'posts'"
                :class="activeTab === 'posts' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                class="min-h-[40px] px-4 rounded-[11px] text-[13px] transition-all flex items-center gap-2 shrink-0">
                <i data-lucide="layout-grid" class="w-4 h-4 text-[#007AFF]"></i>
                <span>Kelola Konten Platform</span>
                @if($platformPosts->total() > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#007AFF]/15 text-[#007AFF]">{{ $platformPosts->total() }}</span>
                @endif
            </button>
            <button type="button" @click="activeTab = 'inbox'"
                :class="activeTab === 'inbox' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                class="min-h-[40px] px-4 rounded-[11px] text-[13px] transition-all flex items-center gap-2 shrink-0">
                <i data-lucide="message-square" class="w-4 h-4 text-[#AF52DE]"></i>
                <span>Kotak Masuk Interaksi</span>
                @if($platformComments->total() > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#AF52DE]/15 text-[#AF52DE]">{{ $platformComments->total() }}</span>
                @endif
            </button>
            <button type="button" @click="activeTab = 'settings'"
                :class="activeTab === 'settings' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                class="min-h-[40px] px-4 rounded-[11px] text-[13px] transition-all flex items-center gap-2 shrink-0">
                <i data-lucide="sliders" class="w-4 h-4 text-[#007AFF]"></i>
                <span>Status Provider &amp; Integrasi</span>
            </button>
            <button type="button" @click="activeTab = 'merchants'"
                :class="activeTab === 'merchants' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                class="min-h-[40px] px-4 rounded-[11px] text-[13px] transition-all flex items-center gap-2 shrink-0">
                <i data-lucide="users" class="w-4 h-4 text-[#34C759]"></i>
                <span>Pengawasan Merchant</span>
            </button>
            <button type="button" @click="activeTab = 'app_review'"
                :class="activeTab === 'app_review' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                class="min-h-[40px] px-4 rounded-[11px] text-[13px] transition-all flex items-center gap-2 shrink-0">
                <i data-lucide="shield-alert" class="w-4 h-4 text-[#FF9500]"></i>
                <span>Panduan Meta App Review</span>
            </button>
        </div>

        {{-- 4. TAB 1: KELOLA KONTEN PLATFORM (COOCA OFFICIAL POSTS) --}}
        <div x-show="activeTab === 'posts'" class="space-y-6">
            {{-- BENTO PROFILE SNAPSHOT & QUICK POST ACTION --}}
            <div class="rounded-[22px] sm:rounded-[24px] bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-md border border-black/[0.08] dark:border-white/[0.08] shadow-sm p-5 sm:p-7 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5 pb-5 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="w-14 h-14 rounded-[20px] bg-gradient-to-tr from-[#F58529] via-[#DD2A7B] to-[#8134AF] p-0.5 shadow-md shrink-0 flex items-center justify-center">
                            @if(!empty($platform['instagram_profile_picture_url']))
                                <img src="{{ $platform['instagram_profile_picture_url'] }}" alt="Instagram Profile" class="w-full h-full object-cover rounded-[18px]">
                            @else
                                <div class="w-full h-full rounded-[18px] bg-white dark:bg-[#1C1C1E] flex items-center justify-center text-[#DD2A7B]">
                                    <i data-lucide="instagram" class="w-7 h-7"></i>
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-[17px] sm:text-[18px] font-bold text-black dark:text-white tracking-tight truncate">
                                    Cooca Official Media Hub
                                </h3>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25">
                                    {{ $platform['instagram_account_type'] ?: 'MEDIA_CREATOR' }}
                                </span>
                            </div>
                            <p class="text-[13px] font-mono text-[#007AFF] mt-0.5 truncate">
                                @<span>{{ $platform['instagram_username'] ?: 'cooca.indonesia' }}</span>
                                <span class="text-black/40 dark:text-white/40 font-sans ml-2 text-[12px]">• {{ $platform['instagram_media_count'] ?: '0' }} Konten Aktif</span>
                            </p>
                        </div>
                    </div>

                    <button type="button" @click="openCreatePostModal = true"
                        class="min-h-[44px] px-5 rounded-[13px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-sm shrink-0">
                        <i data-lucide="feather" class="w-4 h-4"></i>
                        <span>Buat Postingan Baru</span>
                    </button>
                </div>

                {{-- Status Channel Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[12px] bg-[#E1306C]/10 text-[#E1306C] flex items-center justify-center shrink-0">
                            <i data-lucide="instagram" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 block">Instagram</span>
                            <div class="text-[13px] font-bold text-black dark:text-white truncate">
                                @<span>{{ $platform['instagram_username'] ?: 'cooca.indonesia' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[12px] bg-[#1877F2]/10 text-[#1877F2] flex items-center justify-center shrink-0">
                            <i data-lucide="facebook" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 block">Facebook Page</span>
                            <div class="text-[13px] font-bold text-black dark:text-white truncate">
                                {{ !empty($platform['app_id']) ? 'Meta Platform Active' : 'Belum Konfigurasi' }}
                            </div>
                        </div>
                    </div>

                    <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[12px] bg-black/10 dark:bg-white/10 text-black dark:text-white flex items-center justify-center shrink-0">
                            <i data-lucide="video" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 block">TikTok API</span>
                            <div class="text-[13px] font-bold text-black dark:text-white truncate">
                                {{ !empty($platform['tiktok_client_key']) ? 'OAuth 2.0 PKCE Siap' : 'Belum Konfigurasi' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DAFTAR POSTINGAN RESMI PLATFORM --}}
            <div class="rounded-[22px] sm:rounded-[24px] bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-md border border-black/[0.08] dark:border-white/[0.08] shadow-sm p-5 sm:p-7 space-y-5">
                <div class="flex items-center justify-between gap-4 pb-4 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div>
                        <h3 class="text-[16px] sm:text-[17px] font-bold text-black dark:text-white tracking-tight">Riwayat Postingan Resmi Platform</h3>
                        <p class="text-[12px] sm:text-[12.5px] text-black/50 dark:text-white/50 mt-0.5">Daftar konten yang dipublikasikan atau dijadwalkan oleh Administrator untuk akun Cooca</p>
                    </div>
                    <span class="text-[12px] text-black/45 dark:text-white/45 tabular-nums">Total {{ $platformPosts->total() }} Postingan</span>
                </div>

                @if($platformPosts->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-[13px]">
                            <thead>
                                <tr class="border-b border-black/[0.06] dark:border-white/[0.08] text-black/45 dark:text-white/45 text-[11px] uppercase tracking-wider bg-black/[0.01] dark:bg-white/[0.02]">
                                    <th class="px-4 py-3 font-semibold">Media &amp; Konten</th>
                                    <th class="px-4 py-3 font-semibold">Saluran</th>
                                    <th class="px-4 py-3 font-semibold text-center">Status</th>
                                    <th class="px-4 py-3 font-semibold">Dibuat Oleh</th>
                                    <th class="px-4 py-3 font-semibold">Waktu Publikasi</th>
                                    <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                                @foreach($platformPosts as $p)
                                    <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.025] transition-colors">
                                        <td class="px-4 py-3.5 max-w-sm">
                                            <div class="flex items-start gap-3">
                                                @if(!empty($p->media_urls) && is_array($p->media_urls) && count($p->media_urls) > 0)
                                                    <div class="w-12 h-12 rounded-[10px] overflow-hidden bg-black/5 shrink-0 border border-black/10 dark:border-white/10">
                                                        <img src="{{ $p->media_urls[0] }}" alt="Media thumbnail" class="w-full h-full object-cover">
                                                    </div>
                                                @else
                                                    <div class="w-12 h-12 rounded-[10px] bg-black/5 dark:bg-white/5 flex items-center justify-center shrink-0 text-black/40 dark:text-white/40 border border-black/10 dark:border-white/10">
                                                        <i data-lucide="file-text" class="w-5 h-5"></i>
                                                    </div>
                                                @endif
                                                <div class="min-w-0">
                                                    <p class="font-medium text-black dark:text-white line-clamp-2 text-[12.5px]">{{ $p->content }}</p>
                                                    @if($p->platform_post_id)
                                                        <span class="text-[10.5px] font-mono text-black/40 dark:text-white/40 block mt-0.5">ID: {{ Str::limit($p->platform_post_id, 18) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            @php
                                                $targetChannels = $p->targets->pluck('channel')->filter()->unique();
                                            @endphp
                                            @if($targetChannels->isNotEmpty())
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    @foreach($targetChannels as $ch)
                                                        @if($ch === 'instagram')
                                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#E1306C]/10 text-[#E1306C]">
                                                                <i data-lucide="instagram" class="w-3.5 h-3.5"></i>
                                                                <span>Instagram</span>
                                                            </span>
                                                        @elseif($ch === 'facebook')
                                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#1877F2]/10 text-[#1877F2]">
                                                                <i data-lucide="facebook" class="w-3.5 h-3.5"></i>
                                                                <span>Facebook</span>
                                                            </span>
                                                        @elseif($ch === 'threads')
                                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-black/10 dark:bg-white/15 text-black dark:text-white border border-black/10 dark:border-white/10">
                                                                <i data-lucide="at-sign" class="w-3.5 h-3.5"></i>
                                                                <span>Threads</span>
                                                            </span>
                                                        @elseif($ch === 'tiktok')
                                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-black/10 dark:bg-white/10 text-black dark:text-white">
                                                                <i data-lucide="video" class="w-3.5 h-3.5"></i>
                                                                <span>TikTok</span>
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-black/5 text-black dark:text-white">
                                                                <span>{{ ucfirst($ch) }}</span>
                                                            </span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @else
                                                @if($p->platform === 'instagram')
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#E1306C]/10 text-[#E1306C]">
                                                        <i data-lucide="instagram" class="w-3.5 h-3.5"></i>
                                                        <span>Instagram</span>
                                                    </span>
                                                @elseif($p->platform === 'facebook')
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#1877F2]/10 text-[#1877F2]">
                                                        <i data-lucide="facebook" class="w-3.5 h-3.5"></i>
                                                        <span>Facebook</span>
                                                    </span>
                                                @elseif($p->platform === 'threads')
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-black/10 dark:bg-white/15 text-black dark:text-white border border-black/10 dark:border-white/10">
                                                        <i data-lucide="at-sign" class="w-3.5 h-3.5"></i>
                                                        <span>Threads</span>
                                                    </span>
                                                @elseif($p->platform === 'tiktok')
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-black/10 dark:bg-white/10 text-black dark:text-white">
                                                        <i data-lucide="video" class="w-3.5 h-3.5"></i>
                                                        <span>TikTok</span>
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-black/5 text-black dark:text-white">
                                                        <span>{{ ucfirst($p->platform) }}</span>
                                                    </span>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            @if($p->status === 'published')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                                    Tayang
                                                </span>
                                            @elseif($p->status === 'scheduled')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/12 text-[#007AFF]">
                                                    Terjadwal
                                                </span>
                                            @elseif($p->status === 'publishing')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#FF9500]">
                                                    Memproses
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#FF3B30]" title="{{ $p->error_message }}">
                                                    Gagal
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 text-black/60 dark:text-white/60 text-[12px]">
                                            {{ $p->admin?->name ?? 'Super Administrator' }}
                                        </td>
                                        <td class="px-4 py-3.5 text-black/60 dark:text-white/60 text-[12px] tabular-nums">
                                            @if($p->published_at)
                                                {{ $p->published_at->format('d M Y, H:i') }} WIB
                                            @elseif($p->scheduled_at)
                                                {{ $p->scheduled_at->format('d M Y, H:i') }} WIB (Jadwal)
                                            @else
                                                {{ $p->created_at->format('d M Y, H:i') }} WIB
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                @if($p->status === 'failed')
                                                    <form method="POST" action="{{ route('admin.social-media.posts.retry', $p) }}">
                                                        @csrf
                                                        <button type="submit" title="Coba kirim ulang"
                                                            class="p-1.5 rounded-[8px] text-[#007AFF] hover:bg-[#007AFF]/10 transition-colors">
                                                            <i data-lucide="rotate-cw" class="w-4 h-4"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <form method="POST" action="{{ route('admin.social-media.posts.destroy', $p) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus postingan platform ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" title="Hapus postingan"
                                                        class="p-1.5 rounded-[8px] text-[#FF3B30] hover:bg-[#FF3B30]/10 transition-colors">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="pt-2">
                        {{ $platformPosts->appends(['tab' => 'posts'])->links() }}
                    </div>
                @else
                    <div class="p-12 text-center space-y-3 rounded-[18px] bg-black/[0.015] dark:bg-white/[0.02] border border-dashed border-black/10 dark:border-white/10">
                        <div class="w-12 h-12 rounded-[16px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center mx-auto">
                            <i data-lucide="feather" class="w-6 h-6"></i>
                        </div>
                        <div class="space-y-1">
                            <h4 class="font-bold text-[15px] text-black dark:text-white">Belum Ada Postingan Resmi Platform</h4>
                            <p class="text-[12.5px] text-black/50 dark:text-white/50 max-w-md mx-auto">Buat konten promosi atau pengumuman pertama Anda untuk dipublikasikan langsung ke akun Instagram, Facebook Page, atau TikTok resmi Cooca.</p>
                        </div>
                        <button type="button" @click="openCreatePostModal = true"
                            class="min-h-[40px] px-4 rounded-[11px] text-[12.5px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all inline-flex items-center gap-1.5 shadow-sm">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>Buat Postingan Sekarang</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>

        {{-- 5. TAB 2: KOTAK MASUK INTERAKSI (COMMENTS & ENGAGEMENT) --}}
        <div x-show="activeTab === 'inbox'" class="space-y-6">
            <div class="rounded-[22px] sm:rounded-[24px] bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-md border border-black/[0.08] dark:border-white/[0.08] shadow-sm p-5 sm:p-7 space-y-5">
                <div class="flex items-center justify-between gap-4 pb-4 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div>
                        <h3 class="text-[16px] sm:text-[17px] font-bold text-black dark:text-white tracking-tight">Komentar &amp; Interaksi Akun Resmi</h3>
                        <p class="text-[12px] sm:text-[12.5px] text-black/50 dark:text-white/50 mt-0.5">Kelola dan tanggapi komentar pengunjung pada postingan resmi media sosial Cooca</p>
                    </div>
                    <span class="text-[12px] text-black/45 dark:text-white/45 tabular-nums">Total {{ $platformComments->total() }} Komentar</span>
                </div>

                @if($platformComments->count() > 0)
                    <div class="divide-y divide-black/[0.05] dark:divide-white/[0.06]">
                        @foreach($platformComments as $c)
                            <div class="py-4 space-y-2.5">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-9 h-9 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center font-bold text-[13px] text-black/70 dark:text-white/70">
                                            {{ strtoupper(substr($c->from_name ?: 'User', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-[13px] text-black dark:text-white">{{ $c->from_name ?: 'Pengguna Media Sosial' }}</span>
                                                <span class="text-[11px] font-mono text-black/40 dark:text-white/40">({{ $c->platform }})</span>
                                                @if($c->status === 'replied')
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">Dibalas</span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500]/15 text-[#FF9500]">Belum Dibalas</span>
                                                @endif
                                            </div>
                                            <span class="text-[11px] text-black/45 dark:text-white/45 tabular-nums">
                                                {{ $c->created_time ? $c->created_time->format('d M Y, H:i') : $c->created_at->format('d M Y, H:i') }} WIB
                                            </span>
                                        </div>
                                    </div>

                                    <button type="button" @click="openReplyModal('{{ $c->id }}', '{{ addslashes($c->from_name ?: 'User') }}', '{{ addslashes($c->message) }}')"
                                        class="min-h-[32px] px-3 rounded-[9px] text-[12px] font-bold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.98] transition-all inline-flex items-center gap-1.5">
                                        <i data-lucide="reply" class="w-3.5 h-3.5"></i>
                                        <span>Balas</span>
                                    </button>
                                </div>

                                <div class="pl-11">
                                    <p class="text-[13px] text-black/85 dark:text-white/85 bg-black/[0.02] dark:bg-white/[0.03] p-3 rounded-[12px] border border-black/[0.04] dark:border-white/[0.05]">
                                        {{ $c->message }}
                                    </p>
                                    @if($c->post)
                                        <p class="text-[11.5px] text-black/45 dark:text-white/45 mt-1 truncate">
                                            Pada postingan: <span class="italic">"{{ Str::limit($c->post->content, 60) }}"</span>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-2">
                        {{ $platformComments->appends(['tab' => 'inbox'])->links() }}
                    </div>
                @else
                    <div class="p-10 text-center space-y-2.5 rounded-[18px] bg-black/[0.015] dark:bg-white/[0.02] border border-dashed border-black/10 dark:border-white/10">
                        <i data-lucide="message-square" class="w-8 h-8 text-black/30 dark:text-white/30 mx-auto"></i>
                        <p class="text-[13px] text-black/50 dark:text-white/50">Belum ada komentar yang masuk pada postingan resmi platform.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- 6. TAB 3: INTEGRATION HUB & CURRENT PROVIDER STATUS --}}
        <div x-show="activeTab === 'settings'" class="space-y-6">
            <div class="rounded-[22px] sm:rounded-[24px] bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-md border border-black/[0.08] dark:border-white/[0.08] shadow-sm p-5 sm:p-7 space-y-6 w-full min-w-0">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div class="flex items-start gap-3.5 min-w-0">
                        <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-[16px] bg-gradient-to-br from-[#1877F2] via-[#E1306C] to-[#000000] text-white flex items-center justify-center shadow-md shadow-[#1877F2]/20 shrink-0">
                            <i data-lucide="share-2" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-[16px] sm:text-[17px] font-bold text-black dark:text-white tracking-tight">
                                    Pusat Konfigurasi Media Sosial Platform Terpadu
                                </h3>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25">
                                    Terintegrasi
                                </span>
                            </div>
                            <p class="text-[12px] sm:text-[12.5px] text-black/55 dark:text-white/55 mt-0.5 max-w-2xl">
                                Seluruh konfigurasi kredensial Meta App (Facebook &amp; Threads), Instagram Platform (Cooca-IG), dan TikTok Open API kini dikelola secara terpusat di <strong>Pengaturan Platform &amp; Sistem</strong>.
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('admin.settings.index', ['tab' => 'social']) }}"
                        class="min-h-[44px] px-5 rounded-[12px] text-[13px] font-bold bg-[#007AFF] hover:bg-[#0071E3] text-white active:scale-[0.98] transition-all inline-flex items-center justify-center gap-2 shadow-sm shrink-0">
                        <i data-lucide="sliders" class="w-4 h-4"></i>
                        <span>Buka Pengaturan Media Sosial</span>
                        <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 opacity-70"></i>
                    </a>
                </div>

                {{-- Status Badges --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="p-3.5 rounded-[14px] bg-[#1877F2]/8 border border-[#1877F2]/20 flex items-center justify-between">
                        <span class="text-[12px] font-semibold text-[#1877F2]">Meta App ID</span>
                        <span class="text-[12px] font-mono font-bold text-[#1877F2]">{{ $platform['app_id'] ?: 'Belum Diisi' }}</span>
                    </div>
                    <div class="p-3.5 rounded-[14px] bg-[#E1306C]/8 border border-[#E1306C]/20 flex items-center justify-between">
                        <span class="text-[12px] font-semibold text-[#E1306C]">Instagram Resmi</span>
                        <span class="text-[12px] font-mono font-bold text-[#E1306C]">@{{ $platform['instagram_username'] ?: 'cooca.indonesia' }}</span>
                    </div>
                    <div class="p-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 flex items-center justify-between">
                        <span class="text-[12px] font-semibold text-black dark:text-white">TikTok Client Key</span>
                        <span class="text-[12px] font-mono font-bold text-black dark:text-white truncate max-w-[120px]">{{ $platform['tiktok_client_key'] ?: 'Belum Diisi' }}</span>
                    </div>
                </div>

                {{-- Current Settings Snapshot Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                    <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">Meta App Secret</span>
                        <div class="text-[13px] font-mono font-bold text-black dark:text-white truncate">
                            {{ !empty($platform['app_secret']) ? '••••••••••••••••' : 'Belum Dikonfigurasi' }}
                        </div>
                    </div>

                    <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">Instagram App Name</span>
                        <div class="text-[13px] font-mono font-bold text-black dark:text-white truncate">
                            {{ $platform['instagram_app_name'] ?: 'Cooca-IG' }}
                        </div>
                    </div>

                    <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">Status Token Instagram</span>
                        <div class="text-[13px] font-bold text-[#248A3D] dark:text-[#30D158] flex items-center gap-1.5">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                            <span>{{ !empty($platform['instagram_access_token']) ? 'Tersimpan Terenkripsi' : 'Belum Ada Token' }}</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">Webhook Verify Token</span>
                        <div class="text-[13px] font-mono font-bold text-black dark:text-white truncate">
                            {{ $platform['webhook_verify_token'] ?: 'cooca_meta_social_webhook_token' }}
                        </div>
                    </div>

                    <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">TikTok Secret</span>
                        <div class="text-[13px] font-mono font-bold text-black dark:text-white truncate">
                            {{ !empty($platform['tiktok_client_secret']) ? '••••••••••••••••' : 'Belum Dikonfigurasi' }}
                        </div>
                    </div>

                    <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">Versi Graph API</span>
                        <div class="text-[13px] font-mono font-bold text-black dark:text-white truncate">
                            {{ $platform['graph_version'] ?: 'v21.0' }}
                        </div>
                    </div>
                </div>

                {{-- Endpoints & Callback URL --}}
                <div class="space-y-3 pt-2">
                    <h4 class="text-[13px] font-bold text-black dark:text-white tracking-tight">Endpoint Callback &amp; Webhook Resmi</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div class="p-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/[0.06] dark:border-white/[0.08] space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">Meta Webhook URL</span>
                                <button type="button" @click="copyText('{{ $platform['webhook_url'] }}', 'meta_wh')"
                                    class="text-[11px] font-bold text-[#007AFF] hover:underline">
                                    <span x-text="copied === 'meta_wh' ? 'Tersalin' : 'Salin'"></span>
                                </button>
                            </div>
                            <div class="font-mono text-[11px] text-black/80 dark:text-white/80 truncate">{{ $platform['webhook_url'] }}</div>
                        </div>

                        <div class="p-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/[0.06] dark:border-white/[0.08] space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">Instagram Redirect URI</span>
                                <button type="button" @click="copyText('{{ $platform['ig_redirect_uri'] }}', 'ig_uri')"
                                    class="text-[11px] font-bold text-[#007AFF] hover:underline">
                                    <span x-text="copied === 'ig_uri' ? 'Tersalin' : 'Salin'"></span>
                                </button>
                            </div>
                            <div class="font-mono text-[11px] text-black/80 dark:text-white/80 truncate">{{ $platform['ig_redirect_uri'] }}</div>
                        </div>

                        <div class="p-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/[0.06] dark:border-white/[0.08] space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">TikTok Redirect URI</span>
                                <button type="button" @click="copyText('{{ $platform['tiktok_redirect_uri'] }}', 'tt_uri')"
                                    class="text-[11px] font-bold text-[#007AFF] hover:underline">
                                    <span x-text="copied === 'tt_uri' ? 'Tersalin' : 'Salin'"></span>
                                </button>
                            </div>
                            <div class="font-mono text-[11px] text-black/80 dark:text-white/80 truncate">{{ $platform['tiktok_redirect_uri'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 7. TAB 4: MERCHANTS OVERSIGHT --}}
        <div x-show="activeTab === 'merchants'" class="space-y-6">
            <div class="rounded-[22px] sm:rounded-[24px] bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-md border border-black/[0.08] dark:border-white/[0.08] shadow-sm p-5 sm:p-7 space-y-5">
                <div class="flex items-center justify-between gap-4 pb-4 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div>
                        <h3 class="text-[16px] sm:text-[17px] font-bold text-black dark:text-white tracking-tight">Status Koneksi Media Sosial Merchant</h3>
                        <p class="text-[12px] sm:text-[12.5px] text-black/50 dark:text-white/50 mt-0.5">Daftar toko/merchant yang mengaktifkan integrasi akun Facebook, Instagram, Threads, atau TikTok</p>
                    </div>
                    <span class="text-[12px] text-black/45 dark:text-white/45 tabular-nums">Total {{ $merchants->total() }} Merchant</span>
                </div>

                @if ($merchants->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-[13px]">
                            <thead>
                                <tr class="border-b border-black/[0.06] dark:border-white/[0.08] text-black/45 dark:text-white/45 text-[11px] uppercase tracking-wider bg-black/[0.01] dark:bg-white/[0.02]">
                                    <th class="px-4 py-3 font-semibold">Bisnis / Merchant</th>
                                    <th class="px-4 py-3 font-semibold">Kanal Terhubung</th>
                                    <th class="px-4 py-3 font-semibold text-center">Status</th>
                                    <th class="px-4 py-3 font-semibold text-right">Terakhir Diperbarui</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                                @foreach ($merchants as $biz)
                                    <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.025] transition-colors">
                                        <td class="px-4 py-3.5">
                                            <div class="font-bold text-black dark:text-white">{{ $biz->name }}</div>
                                            <div class="text-[11.5px] text-black/50 dark:text-white/50">ID: {{ Str::limit($biz->id, 8) }}</div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                @foreach ($biz->socialMediaAccounts as $acc)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[8px] text-[11.5px] font-medium bg-black/[0.04] dark:bg-white/[0.06] text-black/75 dark:text-white/75">
                                                        @if ($acc->platform === 'facebook')
                                                            <i data-lucide="facebook" class="w-3 h-3 text-[#1877F2]"></i>
                                                        @elseif ($acc->platform === 'instagram')
                                                            <i data-lucide="instagram" class="w-3 h-3 text-[#E1306C]"></i>
                                                        @elseif ($acc->platform === 'threads')
                                                            <i data-lucide="at-sign" class="w-3 h-3 text-black dark:text-white"></i>
                                                        @elseif ($acc->platform === 'tiktok')
                                                            <i data-lucide="video" class="w-3 h-3 text-black dark:text-white"></i>
                                                        @else
                                                            <i data-lucide="globe" class="w-3 h-3 text-black/50"></i>
                                                        @endif
                                                        <span>{{ $acc->account_name }}</span>
                                                    </span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                                Aktif
                                            </span>
                                        </td>
                                        <td class="px-4 py-3.5 text-right text-black/50 dark:text-white/50 text-[12px] tabular-nums">
                                            {{ optional($biz->socialMediaAccounts->first()?->updated_at)->diffForHumans() }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="pt-2">
                        {{ $merchants->appends(['tab' => 'merchants'])->links() }}
                    </div>
                @else
                    <div class="p-10 text-center space-y-2.5 rounded-[18px] bg-black/[0.015] dark:bg-white/[0.02] border border-dashed border-black/10 dark:border-white/10">
                        <i data-lucide="users" class="w-8 h-8 text-black/30 dark:text-white/30 mx-auto"></i>
                        <p class="text-[13px] text-black/50 dark:text-white/50">Belum ada merchant yang mengaktifkan integrasi media sosial.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- 8. TAB 5: APP REVIEW & COMPLIANCE GUIDE --}}
        <div x-show="activeTab === 'app_review'" class="space-y-6">
            <div class="rounded-[22px] sm:rounded-[24px] bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-md border border-black/[0.08] dark:border-white/[0.08] shadow-sm p-5 sm:p-7 space-y-6">
                <div class="pb-4 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <h3 class="text-[16px] sm:text-[17px] font-bold text-black dark:text-white tracking-tight">Panduan Meta App Review &amp; Kepatuhan Kebijakan</h3>
                    <p class="text-[12px] sm:text-[12.5px] text-black/50 dark:text-white/50 mt-0.5">Panduan persyaratan untuk mengajukan izin permissions produksi ke Meta App Review</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-[12.5px]">
                    <div class="p-4.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2.5">
                        <span class="font-bold text-black dark:text-white block text-[13.5px]">1. Permissions yang Diwajibkan</span>
                        <ul class="space-y-1.5 text-black/70 dark:text-white/70 list-disc list-inside">
                            <li><code class="text-[#007AFF] font-mono">pages_manage_posts</code>: Membuat postingan feed di Facebook Page.</li>
                            <li><code class="text-[#007AFF] font-mono">pages_read_engagement</code>: Membaca reaksi, komentar, dan metrik FB.</li>
                            <li><code class="text-[#007AFF] font-mono">instagram_basic</code> &amp; <code class="text-[#007AFF] font-mono">instagram_content_publish</code>: Memposting foto ke akun Instagram Bisnis.</li>
                            <li><code class="text-[#007AFF] font-mono">pages_messaging</code> &amp; <code class="text-[#007AFF] font-mono">instagram_manage_messages</code>: Balas komentar dan pesan.</li>
                            <li><code class="text-[#007AFF] font-mono">threads_content_publish</code> &amp; <code class="text-[#007AFF] font-mono">threads_manage_replies</code>: Publikasi konten Threads.</li>
                        </ul>
                    </div>

                    <div class="p-4.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2.5">
                        <span class="font-bold text-black dark:text-white block text-[13.5px]">2. Skenario Rekaman Video Demo</span>
                        <ol class="space-y-1.5 text-black/70 dark:text-white/70 list-decimal list-inside">
                            <li>Rekam login merchant ke dashboard COOCA.</li>
                            <li>Klik tombol <strong>Hubungkan Media Sosial</strong> hingga muncul popup Meta Login.</li>
                            <li>Pilih Facebook Page &amp; Akun Instagram yang dikelola.</li>
                            <li>Tunjukkan cara membuat dan memposting gambar ke Instagram / Facebook dari halaman COOCA.</li>
                            <li>Tunjukkan bukti postingan berhasil tayang di profil Facebook &amp; Instagram.</li>
                            <li>Tunjukkan cara membalas komentar masuk di Kotak Masuk COOCA.</li>
                        </ol>
                    </div>
                </div>

                <div class="p-4 rounded-[16px] bg-[#007AFF]/8 border border-[#007AFF]/20 text-[12px] text-black/70 dark:text-white/70 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2.5">
                        <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                        <span>Dokumentasi Resmi: Meta App Review &amp; Facebook Login for Business</span>
                    </div>
                    <a href="https://developers.facebook.com/docs/app-review" target="_blank" rel="noopener noreferrer"
                        class="px-3 py-1.5 rounded-[9px] bg-[#007AFF] text-white font-bold text-[11.5px] shrink-0 hover:bg-[#0071E3] transition-colors">
                        Buka Meta Docs
                    </a>
                </div>
            </div>
        </div>

        {{-- 9. MODAL BUAT POSTINGAN RESMI PLATFORM (APPLE HIG MODAL SHEET) --}}
        <div x-show="openCreatePostModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            style="display: none;">

            <div @click.away="openCreatePostModal = false"
                class="bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[24px] shadow-2xl max-w-xl w-full max-h-[90vh] overflow-y-auto p-6 space-y-5">

                <div class="flex items-center justify-between pb-4 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[14px] bg-[#007AFF]/12 text-[#007AFF] flex items-center justify-center">
                            <i data-lucide="feather" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-[17px] font-bold text-black dark:text-white">Buat Postingan Resmi Cooca</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50">Publikasikan konten resmi ke akun media sosial platform</p>
                        </div>
                    </div>
                    <button type="button" @click="openCreatePostModal = false" class="text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.social-media.posts.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    {{-- Pilihan Platform (Multi-Select Bento Tiles) --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">Pilih Saluran Publikasi Resmi Platform</label>
                            <button type="button" @click="toggleAllPlatforms()" class="text-[11.5px] font-bold text-[#007AFF] hover:underline">
                                <span x-text="selectedPlatforms.length === allPlatforms.length ? 'Pilih Instagram Saja' : 'Pilih Semua Saluran'"></span>
                            </button>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                            {{-- Instagram --}}
                            <label class="flex items-center gap-2 p-3 rounded-[14px] border cursor-pointer transition-all"
                                :class="selectedPlatforms.includes('instagram') ? 'bg-[#E1306C]/10 border-[#E1306C] text-[#E1306C] font-bold shadow-xs' : 'bg-black/[0.02] dark:bg-white/[0.02] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                                <input type="checkbox" name="platforms[]" value="instagram" x-model="selectedPlatforms" class="sr-only">
                                <div class="w-7 h-7 rounded-[8px] bg-[#E1306C]/15 text-[#E1306C] flex items-center justify-center shrink-0">
                                    <i data-lucide="instagram" class="w-4 h-4"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[12px] leading-tight">Instagram</div>
                                    <div class="text-[10px] opacity-70 font-mono">@cooca.indonesia</div>
                                </div>
                            </label>

                            {{-- Facebook --}}
                            <label class="flex items-center gap-2 p-3 rounded-[14px] border cursor-pointer transition-all"
                                :class="selectedPlatforms.includes('facebook') ? 'bg-[#1877F2]/10 border-[#1877F2] text-[#1877F2] font-bold shadow-xs' : 'bg-black/[0.02] dark:bg-white/[0.02] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                                <input type="checkbox" name="platforms[]" value="facebook" x-model="selectedPlatforms" class="sr-only">
                                <div class="w-7 h-7 rounded-[8px] bg-[#1877F2]/15 text-[#1877F2] flex items-center justify-center shrink-0">
                                    <i data-lucide="facebook" class="w-4 h-4"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[12px] leading-tight">Facebook</div>
                                    <div class="text-[10px] opacity-70">Page Resmi</div>
                                </div>
                            </label>

                            {{-- Threads --}}
                            <label class="flex items-center gap-2 p-3 rounded-[14px] border cursor-pointer transition-all"
                                :class="selectedPlatforms.includes('threads') ? 'bg-black/10 dark:bg-white/15 border-black dark:border-white text-black dark:text-white font-bold shadow-xs' : 'bg-black/[0.02] dark:bg-white/[0.02] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                                <input type="checkbox" name="platforms[]" value="threads" x-model="selectedPlatforms" class="sr-only">
                                <div class="w-7 h-7 rounded-[8px] bg-black/10 dark:bg-white/10 text-black dark:text-white flex items-center justify-center shrink-0">
                                    <i data-lucide="at-sign" class="w-4 h-4"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[12px] leading-tight">Threads</div>
                                    <div class="text-[10px] opacity-70">Meta Threads</div>
                                </div>
                            </label>

                            {{-- TikTok --}}
                            <label class="flex items-center gap-2 p-3 rounded-[14px] border cursor-pointer transition-all"
                                :class="selectedPlatforms.includes('tiktok') ? 'bg-black/10 dark:bg-white/15 border-black dark:border-white text-black dark:text-white font-bold shadow-xs' : 'bg-black/[0.02] dark:bg-white/[0.02] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                                <input type="checkbox" name="platforms[]" value="tiktok" x-model="selectedPlatforms" class="sr-only">
                                <div class="w-7 h-7 rounded-[8px] bg-black/10 dark:bg-white/10 text-black dark:text-white flex items-center justify-center shrink-0">
                                    <i data-lucide="video" class="w-4 h-4"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[12px] leading-tight">TikTok</div>
                                    <div class="text-[10px] opacity-70">Open API</div>
                                </div>
                            </label>
                        </div>
                        <div x-show="selectedPlatforms.length === 0" class="text-[11.5px] text-[#FF3B30] font-semibold">
                            * Pilih minimal 1 saluran untuk mempublikasikan postingan.
                        </div>
                    </div>

                    {{-- Isi Caption --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">Caption / Isi Postingan</label>
                            <span class="text-[11px] font-mono text-black/45 dark:text-white/45"><span x-text="postCaption.length"></span> / 2.200</span>
                        </div>
                        <textarea name="content" x-model="postCaption" rows="4" required maxlength="2200"
                            placeholder="Tuliskan caption postingan resmi Cooca... Jelaskan promo, pembaruan sistem, atau tips bisnis untuk UMKM."
                            class="w-full bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] p-3 text-[13px] text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all"></textarea>

                        {{-- Chip Tagar --}}
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            <span class="text-[11px] text-black/45 dark:text-white/45 self-center">Tagar cepat:</span>
                            @foreach(['#CoocaERP', '#UMKMIndonesia', '#KasirDigital', '#BisnisSukses', '#SaaSMaju'] as $tag)
                                <button type="button" @click="insertHashtag('{{ $tag }}')"
                                    class="px-2 py-0.5 rounded-[7px] text-[11px] font-mono bg-black/[0.04] dark:bg-white/[0.06] text-[#007AFF] hover:bg-[#007AFF]/10 transition-colors">
                                    {{ $tag }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Unggah Berkas Media atau URL --}}
                    <div class="space-y-3">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">Media Gambar / Video</label>
                        <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.08] space-y-2.5">
                            <div>
                                <span class="text-[11.5px] font-medium text-black/70 dark:text-white/70 block mb-1">Unggah dari Perangkat (Foto/Video)</span>
                                <input type="file" name="media_file" accept="image/jpeg,image/png,video/mp4,video/quicktime"
                                    class="w-full text-[12px] text-black/70 dark:text-white/70 file:mr-3 file:py-1.5 file:px-3 file:rounded-[8px] file:border-0 file:text-[12px] file:font-semibold file:bg-[#007AFF]/10 file:text-[#007AFF] hover:file:bg-[#007AFF]/15 cursor-pointer">
                            </div>
                            <div class="text-center text-[11px] text-black/40 dark:text-white/40 font-bold uppercase">-- ATAU --</div>
                            <div>
                                <span class="text-[11.5px] font-medium text-black/70 dark:text-white/70 block mb-1">Masukkan URL Media Publik</span>
                                <input type="url" name="media_url" placeholder="https://domain.com/storage/post.jpg"
                                    class="w-full min-h-[38px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[10px] px-3 text-[12.5px] text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                            </div>
                        </div>
                    </div>

                    {{-- Opsi Waktu Publikasi (Multi-Mode & Per-Channel Support) --}}
                    <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.08] space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">Waktu Publikasi Saluran</label>
                            <span class="px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-[#007AFF]/10 text-[#007AFF]">Fleksibel</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-[12px]">
                            <label class="p-2.5 rounded-[10px] border cursor-pointer transition-all flex items-center gap-2"
                                :class="postPublishMode === 'now' ? 'bg-[#007AFF]/10 border-[#007AFF] text-black dark:text-white font-bold shadow-xs' : 'bg-white dark:bg-[#2C2C2E] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                                <input type="radio" name="timing_mode" value="now" x-model="postPublishMode" class="sr-only">
                                <i data-lucide="zap" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Semua Sekarang</span>
                            </label>

                            <label class="p-2.5 rounded-[10px] border cursor-pointer transition-all flex items-center gap-2"
                                :class="postPublishMode === 'schedule_all' ? 'bg-[#5856D6]/10 border-[#5856D6] text-black dark:text-white font-bold shadow-xs' : 'bg-white dark:bg-[#2C2C2E] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                                <input type="radio" name="timing_mode" value="schedule_all" x-model="postPublishMode" class="sr-only">
                                <i data-lucide="clock" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                                <span>Jadwal Serentak</span>
                            </label>

                            <label class="p-2.5 rounded-[10px] border cursor-pointer transition-all flex items-center gap-2"
                                :class="postPublishMode === 'per_channel' ? 'bg-[#FF9500]/10 border-[#FF9500] text-black dark:text-white font-bold shadow-xs' : 'bg-white dark:bg-[#2C2C2E] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                                <input type="radio" name="timing_mode" value="per_channel" x-model="postPublishMode" class="sr-only">
                                <i data-lucide="sliders" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                <span>Beda per Saluran</span>
                            </label>
                        </div>

                        {{-- Mode B: Jadwal Serentak --}}
                        <div x-show="postPublishMode === 'schedule_all'" class="pt-2 border-t border-black/5 dark:border-white/5 space-y-1.5" style="display: none;">
                            <label class="block text-[11.5px] font-semibold text-black/70 dark:text-white/70">Pilih Tanggal &amp; Jam Penayangan Serentak</label>
                            <input type="datetime-local" name="scheduled_at"
                                class="w-full min-h-[38px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[10px] px-3 text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6]">
                            <p class="text-[11px] text-black/50 dark:text-white/50">
                                Seluruh saluran yang dipilih akan otomatis dipublikasikan bersamaan oleh cron scheduler saat waktu tiba.
                            </p>
                        </div>

                        {{-- Mode C: Beda Waktu per Saluran --}}
                        <div x-show="postPublishMode === 'per_channel'" class="pt-2 border-t border-black/5 dark:border-white/5 space-y-2.5" style="display: none;">
                            <p class="text-[11.5px] text-black/60 dark:text-white/60">
                                Atur waktu spesifik per media sosial (misal: Instagram langsung sekarang, Facebook jam 2 siang, Threads nanti malam, TikTok besok):
                            </p>
                            <div class="space-y-2">
                                <template x-for="ch in selectedPlatforms" :key="ch">
                                    <div class="p-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 space-y-2">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-[6px] flex items-center justify-center"
                                                    :class="ch === 'instagram' ? 'bg-[#E1306C]/15 text-[#E1306C]' : (ch === 'facebook' ? 'bg-[#1877F2]/15 text-[#1877F2]' : 'bg-black/10 dark:bg-white/15 text-black dark:text-white')">
                                                    <template x-if="ch === 'instagram'"><i data-lucide="instagram" class="w-3.5 h-3.5"></i></template>
                                                    <template x-if="ch === 'facebook'"><i data-lucide="facebook" class="w-3.5 h-3.5"></i></template>
                                                    <template x-if="ch === 'threads'"><i data-lucide="at-sign" class="w-3.5 h-3.5"></i></template>
                                                    <template x-if="ch === 'tiktok'"><i data-lucide="video" class="w-3.5 h-3.5"></i></template>
                                                </div>
                                                <span class="text-[12.5px] font-bold capitalize text-black dark:text-white" x-text="ch"></span>
                                            </div>
                                            <div class="inline-flex p-0.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] text-[11px]">
                                                <button type="button" @click="channelTiming[ch] = 'now'"
                                                    :class="channelTiming[ch] === 'now' ? 'bg-white dark:bg-[#1C1C1E] text-[#007AFF] font-bold shadow-xs' : 'text-black/60 dark:text-white/60'"
                                                    class="px-2.5 py-0.5 rounded-[6px] transition-colors">
                                                    Langsung
                                                </button>
                                                <button type="button" @click="channelTiming[ch] = 'schedule'"
                                                    :class="channelTiming[ch] === 'schedule' ? 'bg-white dark:bg-[#1C1C1E] text-[#FF9500] font-bold shadow-xs' : 'text-black/60 dark:text-white/60'"
                                                    class="px-2.5 py-0.5 rounded-[6px] transition-colors">
                                                    Jadwalkan
                                                </button>
                                            </div>
                                        </div>

                                        <input type="hidden" :name="'platform_timing[' + ch + ']'" :value="channelTiming[ch]">

                                        <div x-show="channelTiming[ch] === 'schedule'" class="pt-1.5 border-t border-black/5 dark:border-white/5">
                                            <input type="datetime-local" :name="'platform_scheduled_at[' + ch + ']'" x-model="channelScheduledAt[ch]"
                                                class="w-full h-8 px-2.5 rounded-[8px] text-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#FF9500]">
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <button type="button" @click="openCreatePostModal = false"
                            class="min-h-[42px] px-4 rounded-[11px] text-[13px] font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="selectedPlatforms.length === 0"
                            class="min-h-[42px] px-5 rounded-[11px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] disabled:opacity-50 disabled:pointer-events-none transition-all inline-flex items-center gap-2 shadow-sm">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            <span x-text="postPublishMode !== 'now' ? 'Simpan Postingan & Jadwal' : 'Publikasikan Sekarang'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- 10. MODAL BALAS KOMENTAR (APPLE HIG MODAL SHEET) --}}
        <div x-show="replyModalOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            style="display: none;">

            <div @click.away="replyModalOpen = false"
                class="bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[24px] shadow-2xl max-w-lg w-full p-6 space-y-4">

                <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-[12px] bg-[#AF52DE]/15 text-[#AF52DE] flex items-center justify-center">
                            <i data-lucide="reply" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-[16px] font-bold text-black dark:text-white">Balas Komentar</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50">Kirim balasan resmi sebagai Cooca Indonesia</p>
                        </div>
                    </div>
                    <button type="button" @click="replyModalOpen = false" class="text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="p-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                    <span class="text-[11px] font-bold text-black/50 dark:text-white/50" x-text="'Dari: ' + activeAuthor"></span>
                    <p class="text-[12.5px] text-black dark:text-white" x-text="activeCommentText"></p>
                </div>

                <form method="POST" :action="replyActionUrl" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">Isi Balasan Anda</label>
                        <textarea name="message" rows="4" required maxlength="1000"
                            placeholder="Tuliskan balasan ramah dan solutif sebagai admin resmi Cooca Indonesia..."
                            class="w-full bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] p-3 text-[13px] text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all"></textarea>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <button type="button" @click="replyModalOpen = false"
                            class="min-h-[40px] px-4 rounded-[11px] text-[12.5px] font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                            class="min-h-[40px] px-5 rounded-[11px] text-[12.5px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all inline-flex items-center gap-1.5 shadow-sm">
                            <i data-lucide="send" class="w-3.5 h-3.5"></i>
                            <span>Kirim Balasan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function adminSocialCenter() {
            return {
                activeTab: '{{ $tab }}',
                openCreatePostModal: false,
                replyModalOpen: false,
                activeCommentId: null,
                activeAuthor: '',
                activeCommentText: '',
                replyActionUrl: '',
                selectedPlatforms: ['instagram'],
                allPlatforms: ['instagram', 'facebook', 'threads', 'tiktok'],
                channelTiming: {
                    instagram: 'now',
                    facebook: 'now',
                    threads: 'now',
                    tiktok: 'now'
                },
                channelScheduledAt: {
                    instagram: '',
                    facebook: '',
                    threads: '',
                    tiktok: ''
                },
                postCaption: '',
                postPublishMode: 'now',
                copied: null,

                toggleAllPlatforms() {
                    if (this.selectedPlatforms.length === this.allPlatforms.length) {
                        this.selectedPlatforms = ['instagram'];
                    } else {
                        this.selectedPlatforms = [...this.allPlatforms];
                    }
                },

                copyText(text, key) {
                    navigator.clipboard.writeText(text);
                    this.copied = key;
                    setTimeout(() => this.copied = null, 2000);
                },

                insertHashtag(tag) {
                    this.postCaption += (this.postCaption.length ? ' ' : '') + tag;
                },

                openReplyModal(commentId, author, text) {
                    this.activeCommentId = commentId;
                    this.activeAuthor = author;
                    this.activeCommentText = text;
                    this.replyActionUrl = "{{ url('admin/social-media/comments') }}/" + commentId + "/reply";
                    this.replyModalOpen = true;
                },

                init() {
                    const urlParams = new URLSearchParams(window.location.search);
                    const tabParam = urlParams.get('tab');
                    if (tabParam) {
                        this.activeTab = tabParam;
                    }
                }
            };
        }
    </script>
@endpush