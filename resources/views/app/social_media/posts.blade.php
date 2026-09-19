@extends('layouts.app', [
    'title' => 'Posting Konten Media Sosial - ' . $business->name,
    'headerTitle' => 'Posting Konten & Penjadwalan',
    'headerSubtitle' => 'Publikasikan dan jadwalkan konten ke Facebook Page, Instagram, dan Threads',
])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="socialPostsManager()">

        {{-- 0. BREADCRUMB --}}
        <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 py-0.5 whitespace-nowrap print:hidden">
            <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors font-medium">Dashboard</a>
            <span>›</span>
            <a href="{{ route('social-media.index') }}" class="hover:text-[#007AFF] transition-colors font-medium">Media Sosial</a>
            <span>›</span>
            <span class="text-black/80 dark:text-white/80 font-medium">Posting Konten</span>
        </nav>

        {{-- 1. PAGE HEADER --}}
        <header class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5 shadow-sm">
            <div class="space-y-1.5 max-w-2xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1.5 bg-[#007AFF]/10 text-[#007AFF]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span>
                        <span>Omnichannel Publisher</span>
                    </span>
                    <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1.5 bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                        <span>Multi-Akun Aktif</span>
                    </span>
                </div>
                <h1 class="text-[20px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight">
                    Publikasi Konten &amp; Jadwal Otomatis
                </h1>
                <p class="text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                    Tulis materi promosi sekali dan sebarkan langsung ke Facebook Page, feed Instagram, Threads, atau TikTok pelanggan toko Anda. Berkas lokal dan postingan yang terpublikasi akan otomatis dibersihkan dari server setelah 1x24 jam untuk menjaga kapasitas storage server.
                </p>
            </div>

            <div class="flex items-center gap-2.5 w-full lg:w-auto">
                @if(isset($canSchedulePost) && !$canSchedulePost && empty($hasSocialAddon))
                    <button type="button"
                        @click="window.dispatchEvent(new CustomEvent('open-quota-modal', {
                            detail: {
                                title: 'Kuota Posting Media Sosial Habis',
                                desc: 'Anda telah mencapai batas 3 posting gratis bulan ini. Kuota akan otomatis di-reset pada tanggal 1 awal bulan berikutnya atau aktifkan Add-On Social Media Management untuk posting tanpa batas.',
                                used: {{ $postsUsedThisMonth ?? 3 }},
                                limit: {{ $socialPostLimit ?? 3 }},
                                unit: 'posting',
                                upgradeUrl: '{{ route('billing.checkout') }}',
                                upgradeFee: 'Rp 89.000/bln',
                                isAddon: true
                            }
                        }))"
                        class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-slate-700 hover:bg-slate-800 active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 w-full sm:w-auto shadow-sm cursor-pointer">
                        <i data-lucide="lock" class="w-4 h-4 text-rose-300"></i>
                        <span>Tulis Postingan (Batas Tercapai)</span>
                    </button>
                @else
                    <button @click="openComposerModal = true"
                        class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 w-full sm:w-auto shadow-sm">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        <span>Tulis Postingan Baru</span>
                    </button>
                @endif
            </div>
        </header>

        {{-- QUOTA LIMIT BANNER IF REACHED --}}
        @if(isset($canSchedulePost) && !$canSchedulePost && empty($hasSocialAddon))
            <div class="rounded-[16px] p-4 sm:p-5 bg-rose-50/80 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-800/40 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 backdrop-blur-md">
                <div class="flex items-start sm:items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                        <i data-lucide="lock" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="text-[14px] font-bold text-slate-900 dark:text-white">
                            Kuota Posting Gratis Bulan Ini Habis ({{ $postsUsedThisMonth ?? 3 }}/{{ $socialPostLimit ?? 3 }} Posting)
                        </div>
                        <div class="text-[12px] text-slate-600 dark:text-slate-400 mt-0.5">
                            Seluruh riwayat postingan dan analitik tetap aman dapat diakses. Kuota akan otomatis di-reset pada tanggal 1 awal bulan berikutnya.
                        </div>
                    </div>
                </div>
                <button type="button"
                    @click="window.dispatchEvent(new CustomEvent('open-quota-modal', {
                        detail: {
                            title: 'Kuota Posting Media Sosial Terpakai',
                            desc: 'Anda telah mencapai batas 3 posting gratis bulan ini. Aktifkan Add-On Social Media Management untuk posting & jadwal konten tanpa batas.',
                            used: {{ $postsUsedThisMonth ?? 3 }},
                            limit: {{ $socialPostLimit ?? 3 }},
                            unit: 'posting',
                            upgradeUrl: '{{ route('billing.checkout') }}',
                            upgradeFee: 'Rp 89.000/bln',
                            isAddon: true
                        }
                    }))"
                    class="h-9 px-4 rounded-[10px] text-[12px] font-bold text-white bg-rose-600 hover:bg-rose-500 shadow-sm transition whitespace-nowrap cursor-pointer shrink-0">
                    Aktifkan Unlimited
                </button>
            </div>
        @endif

        {{-- 2. MODULE NAVIGATION SUB-TABS --}}
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-2 sm:p-2.5 flex items-center justify-between shadow-sm">
            <div class="inline-flex p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] border border-black/5 dark:border-white/5 w-full sm:w-auto overflow-x-auto text-[13px] font-medium">
                <a href="{{ route('social-media.index') }}"
                    class="h-8 px-4 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                    <i data-lucide="link" class="w-4 h-4"></i>
                    <span>Koneksi Akun</span>
                </a>
                <a href="{{ route('social-media.posts.index') }}"
                    class="h-8 px-4 rounded-[9px] bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm flex items-center gap-2 whitespace-nowrap">
                    <i data-lucide="image" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Posting Konten</span>
                </a>
                <a href="{{ route('social-media.calendar') }}"
                    class="h-8 px-4 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                    <span>Kalender Konten</span>
                </a>
                <a href="{{ route('social-media.inbox.index') }}"
                    class="h-8 px-4 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                    <i data-lucide="message-square" class="w-4 h-4"></i>
                    <span>Kotak Masuk &amp; Komentar</span>
                </a>
                <a href="{{ route('social-media.insights.index') }}"
                    class="h-8 px-4 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                    <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                    <span>Analitik &amp; Performa</span>
                </a>
            </div>
        </div>

        {{-- FLASH ALERTS --}}
        @if(session('success'))
            <div class="rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/25 p-4 flex items-center gap-3 text-[#248A3D] dark:text-[#30D158]">
                <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0"></i>
                <div class="text-[13px] font-semibold">{{ session('success') }}</div>
            </div>
        @endif
        @if(session('error'))
            <div class="rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 p-4 flex items-center gap-3 text-[#FF3B30]">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                <div class="text-[13px] font-semibold">{{ session('error') }}</div>
            </div>
        @endif

        {{-- 3. FILTERS & SEARCH BAR --}}
        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[12px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider mr-1">Status:</span>
                <a href="{{ route('social-media.posts.index', ['status' => 'all', 'platform' => $platform]) }}"
                    class="h-7 px-3 rounded-full text-[12px] font-medium transition-colors {{ $status === 'all' ? 'bg-[#007AFF] text-white shadow-sm' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 hover:bg-black/[0.08]' }}">
                    Semua ({{ $posts->total() }})
                </a>
                <a href="{{ route('social-media.posts.index', ['status' => 'published', 'platform' => $platform]) }}"
                    class="h-7 px-3 rounded-full text-[12px] font-medium transition-colors {{ $status === 'published' ? 'bg-[#34C759] text-white shadow-sm' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 hover:bg-black/[0.08]' }}">
                    Terpublikasi
                </a>
                <a href="{{ route('social-media.posts.index', ['status' => 'scheduled', 'platform' => $platform]) }}"
                    class="h-7 px-3 rounded-full text-[12px] font-medium transition-colors {{ $status === 'scheduled' ? 'bg-[#5856D6] text-white shadow-sm' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 hover:bg-black/[0.08]' }}">
                    Terjadwal
                </a>
                <a href="{{ route('social-media.posts.index', ['status' => 'failed', 'platform' => $platform]) }}"
                    class="h-7 px-3 rounded-full text-[12px] font-medium transition-colors {{ $status === 'failed' ? 'bg-[#FF3B30] text-white shadow-sm' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 hover:bg-black/[0.08]' }}">
                    Gagal
                </a>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-[12px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Platform:</span>
                <select onchange="window.location.href=this.value"
                    class="h-8 px-3 rounded-[10px] text-[12.5px] font-medium bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                    <option value="{{ route('social-media.posts.index', ['status' => $status, 'platform' => 'all']) }}" {{ $platform === 'all' ? 'selected' : '' }}>Semua Platform</option>
                    <option value="{{ route('social-media.posts.index', ['status' => $status, 'platform' => 'facebook']) }}" {{ $platform === 'facebook' ? 'selected' : '' }}>Facebook</option>
                    <option value="{{ route('social-media.posts.index', ['status' => $status, 'platform' => 'instagram']) }}" {{ $platform === 'instagram' ? 'selected' : '' }}>Instagram</option>
                    <option value="{{ route('social-media.posts.index', ['status' => $status, 'platform' => 'threads']) }}" {{ $platform === 'threads' ? 'selected' : '' }}>Threads</option>
                    <option value="{{ route('social-media.posts.index', ['status' => $status, 'platform' => 'tiktok']) }}" {{ $platform === 'tiktok' ? 'selected' : '' }}>TikTok</option>
                </select>
            </div>
        </div>

        {{-- 4. POSTS LIST / FEED CARDS --}}
        @if($posts->isEmpty())
            <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-12 text-center shadow-sm space-y-4">
                <div class="w-16 h-16 rounded-[20px] bg-black/[0.04] dark:bg-white/[0.06] text-black/40 dark:text-white/40 flex items-center justify-center mx-auto">
                    <i data-lucide="inbox" class="w-8 h-8"></i>
                </div>
                <div class="max-w-md mx-auto space-y-1">
                    <h3 class="text-[16px] font-bold text-black dark:text-white">Belum Ada Postingan</h3>
                    <p class="text-[13px] text-black/60 dark:text-white/60">
                        Mulai bagikan produk baru, promo, atau cerita toko Anda ke pengikut media sosial hari ini.
                    </p>
                </div>
                <div>
                    <button @click="openComposerModal = true"
                        class="h-9 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all inline-flex items-center gap-2">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Tulis Postingan Sekarang</span>
                    </button>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($posts as $post)
                    <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between space-y-4">
                        <div class="space-y-3">
                            {{-- Header Card --}}
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-[12px] font-bold shadow-sm
                                        {{ $post->platform === 'facebook' ? 'bg-[#1877F2]' : ($post->platform === 'instagram' ? 'bg-gradient-to-tr from-[#F58529] via-[#DD2A7B] to-[#8134AF]' : ($post->platform === 'tiktok' ? 'bg-black dark:bg-white dark:text-black' : 'bg-black/10 text-black dark:text-white')) }}">
                                        @if($post->platform === 'facebook')
                                            <i data-lucide="facebook" class="w-4 h-4"></i>
                                        @elseif($post->platform === 'instagram')
                                            <i data-lucide="instagram" class="w-4 h-4"></i>
                                        @elseif($post->platform === 'tiktok')
                                            <i data-lucide="video" class="w-4 h-4"></i>
                                        @else
                                            <i data-lucide="at-sign" class="w-4 h-4"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-[13px] font-bold text-black dark:text-white truncate">
                                            {{ $post->account ? $post->account->account_name : ucfirst($post->platform) }}
                                        </div>
                                        <div class="text-[11px] text-black/50 dark:text-white/50">
                                            {{ $post->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>

                                {{-- Format & Status Badges --}}
                                <div class="flex items-center gap-1.5 flex-wrap justify-end">
                                    @if($post->media_type === 'carousel')
                                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#FF9500]/15 text-[#FF9500] inline-flex items-center gap-1">
                                            <i data-lucide="layers" class="w-3 h-3"></i>
                                            <span>Carousel</span>
                                        </span>
                                    @elseif($post->media_type === 'reels')
                                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#AF52DE]/15 text-[#AF52DE] inline-flex items-center gap-1">
                                            <i data-lucide="film" class="w-3 h-3"></i>
                                            <span>Reels</span>
                                        </span>
                                    @elseif($post->media_type === 'video')
                                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#007AFF]/15 text-[#007AFF] inline-flex items-center gap-1">
                                            <i data-lucide="video" class="w-3 h-3"></i>
                                            <span>Video</span>
                                        </span>
                                    @elseif($post->media_type === 'image')
                                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] inline-flex items-center gap-1">
                                            <i data-lucide="image" class="w-3 h-3"></i>
                                            <span>Foto</span>
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60 inline-flex items-center gap-1">
                                            <i data-lucide="align-left" class="w-3 h-3"></i>
                                            <span>Teks</span>
                                        </span>
                                    @endif

                                    @if($post->status === 'published')
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] inline-flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                            <span>Terpublikasi</span>
                                        </span>
                                    @elseif($post->status === 'scheduled')
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#5856D6]/15 text-[#5856D6] inline-flex items-center gap-1">
                                            <i data-lucide="clock" class="w-3 h-3"></i>
                                            <span>Jadwal: {{ $post->scheduled_at ? $post->scheduled_at->format('d M H:i') : '-' }}</span>
                                        </span>
                                    @elseif($post->status === 'failed')
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/15 text-[#FF3B30] inline-flex items-center gap-1">
                                            <i data-lucide="alert-triangle" class="w-3 h-3"></i>
                                            <span>Gagal</span>
                                        </span>
                                    @elseif($post->status === 'partially_failed')
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500] inline-flex items-center gap-1">
                                            <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                            <span>Sebagian Gagal</span>
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-black/10 dark:bg-white/10 text-black/60 dark:text-white/60">
                                            {{ ucfirst($post->status) }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Multi-Channel Target Breakdown with Retry --}}
                            @if($post->targets->isNotEmpty())
                                <div class="p-2.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1.5">
                                    <div class="text-[10.5px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">Target Saluran:</div>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($post->targets as $target)
                                            <div class="px-2 py-0.5 rounded-[6px] text-[11px] font-semibold flex items-center gap-1.5 border
                                                {{ $target->status === 'published' ? 'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border-[#34C759]/20' : ($target->status === 'failed' ? 'bg-[#FF3B30]/10 text-[#FF3B30] border-[#FF3B30]/20' : ($target->status === 'processing' ? 'bg-[#007AFF]/10 text-[#007AFF] border-[#007AFF]/20' : 'bg-black/5 dark:bg-white/5 text-black/60 dark:text-white/60 border-black/10')) }}">
                                                <span class="capitalize font-bold">{{ $target->channel }}</span>
                                                <span class="text-[10px] opacity-75">({{ $target->content_type }})</span>
                                                @if($target->status === 'published')
                                                    <i data-lucide="check" class="w-3 h-3 text-[#34C759]"></i>
                                                @elseif($target->status === 'failed')
                                                    <form method="POST" action="{{ route('social-media.targets.retry', $target) }}" class="inline ml-1">
                                                        @csrf
                                                        <button type="submit" class="underline text-[#FF3B30] hover:text-black dark:hover:text-white font-bold inline-flex items-center gap-0.5" title="Retry Target Ini">
                                                            <i data-lucide="refresh-cw" class="w-2.5 h-2.5"></i>
                                                            <span>Retry</span>
                                                        </button>
                                                    </form>
                                                @elseif($target->status === 'processing')
                                                    <i data-lucide="loader-2" class="w-3 h-3 animate-spin"></i>
                                                @else
                                                    <span class="text-[10px] opacity-75">Antre</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($post->isPartiallyFailed())
                                <div class="p-2 rounded-[8px] bg-[#FF9500]/10 text-[#FF9500] text-[11.5px] font-semibold flex items-center gap-1.5">
                                    <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                                    <span>Sebagian target publikasi gagal. Klik Retry untuk mencoba ulang target tersebut.</span>
                                </div>
                            @endif

                            {{-- Media Preview if available --}}
                            @if(!empty($post->media_urls) && count($post->media_urls) > 0)
                                <div class="rounded-[14px] overflow-hidden border border-black/5 dark:border-white/10 bg-black aspect-video max-h-48 relative group flex items-center justify-center">
                                    @if(in_array($post->media_type, ['video', 'reels']))
                                        <video src="{{ $post->media_urls[0] }}" controls preload="metadata" class="w-full h-full object-cover"></video>
                                    @else
                                        <img src="{{ $post->media_urls[0] }}" alt="Lampiran Konten" class="w-full h-full object-cover"
                                             onerror="this.onerror=null; this.src='https://placehold.co/600x400/1C1C1E/FFF?text=Media+Preview';">
                                    @endif
                                </div>
                            @elseif(in_array($post->media_type, ['video', 'reels', 'image']) && $post->status === 'published')
                                <div class="rounded-[12px] p-3 bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 flex items-center justify-between text-[11.5px] text-black/60 dark:text-white/60">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="{{ $post->media_type === 'reels' ? 'film' : ($post->media_type === 'video' ? 'video' : 'image') }}" class="w-4 h-4 text-[#007AFF]"></i>
                                        <span class="font-medium">Media Terpublikasi di Meta</span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] inline-flex items-center gap-1">
                                        <i data-lucide="shield-check" class="w-3 h-3"></i>
                                        <span>Server Bersih</span>
                                    </span>
                                </div>
                            @endif

                            {{-- Content text --}}
                            <p class="text-[13px] text-black/80 dark:text-white/80 leading-relaxed line-clamp-4 whitespace-pre-wrap">
                                {{ $post->content }}
                            </p>

                            @if($post->status === 'failed' && $post->error_message)
                                <div class="p-2.5 rounded-[10px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[11.5px] text-[#FF3B30]">
                                    <strong>Penyebab:</strong> {{ $post->error_message }}
                                </div>
                            @endif
                        </div>

                        {{-- Footer Card with Metrics --}}
                        <div class="pt-3 border-t border-black/5 dark:border-white/10 flex items-center justify-between text-[12px] text-black/50 dark:text-white/50">
                            <div class="flex items-center gap-3 font-semibold tabular-nums">
                                <span class="inline-flex items-center gap-1" title="Tayangan (Impressions)">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    <span>{{ number_format($post->getMetric('impressions')) }}</span>
                                </span>
                                <span class="inline-flex items-center gap-1" title="Suka (Likes)">
                                    <i data-lucide="heart" class="w-3.5 h-3.5"></i>
                                    <span>{{ number_format($post->getMetric('likes')) }}</span>
                                </span>
                                <span class="inline-flex items-center gap-1" title="Komentar">
                                    <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                    <span>{{ number_format($post->getMetric('comments')) }}</span>
                                </span>
                            </div>

                            @if($post->status === 'published' && $post->published_at)
                                <span class="text-[11px]">
                                    {{ $post->published_at->format('d/m/Y') }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-4">
                {{ $posts->links() }}
            </div>
        @endif

        {{-- 5. UNIFIED COMPOSER MODAL SHEET (Apple HIG Bento Design) --}}
        <div x-show="openComposerModal" style="display: none;"
            class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="relative w-full max-w-3xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-6 sm:p-7 space-y-5 my-8 max-h-[90vh] overflow-y-auto"
                @click.away="openComposerModal = false">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold">
                            <i data-lucide="feather" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-[17px] font-bold text-black dark:text-white">Unified Social Media Composer</h3>
                            <p class="text-[12.5px] text-black/55 dark:text-white/55">Buat satu materi dan sebarkan ke Facebook, Instagram, Threads, dan TikTok</p>
                        </div>
                    </div>
                    <button type="button" @click="openComposerModal = false" class="text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white p-1 rounded-full hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                {{-- Form Content --}}
                <form action="{{ route('social-media.posts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4" @submit="isSubmitting = true">
                    @csrf
                    <input type="hidden" name="media_format" :value="mediaFormat">
                    <input type="hidden" name="social_media_account_id" :value="selectedAccounts[0] || ''">

                    {{-- 1. Target Accounts Multi-Selector (Bento Tiles) --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-[12.5px] font-bold text-black/80 dark:text-white/80">
                                Pilih Saluran Publikasi <span class="text-[#FF3B30]">*</span>
                            </label>
                            <button type="button" @click="toggleSelectAll()" class="text-[11.5px] font-bold text-[#007AFF] hover:underline">
                                <span x-text="selectedAccounts.length === allAccountIds.length ? 'Batalkan Semua' : 'Pilih Semua Saluran'"></span>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            @foreach($accounts as $acc)
                                <label class="p-3 rounded-[14px] border cursor-pointer transition-all flex items-center justify-between gap-2.5"
                                    :class="selectedAccounts.includes('{{ $acc->id }}') ? 'bg-[#007AFF]/10 border-[#007AFF] text-black dark:text-white shadow-xs' : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70 hover:bg-black/[0.04]'">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <input type="checkbox" name="target_accounts[]" value="{{ $acc->id }}"
                                            x-model="selectedAccounts" class="rounded text-[#007AFF] focus:ring-[#007AFF]">
                                        <div class="min-w-0">
                                            <div class="text-[13px] font-bold truncate">{{ $acc->account_name }}</div>
                                            <div class="text-[11px] opacity-75 capitalize flex items-center gap-1.5">
                                                <span>{{ $acc->platform }}</span>
                                                @if($acc->username)
                                                    <span>•</span>
                                                    <span class="font-mono text-[#007AFF]">{{ $acc->username }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="w-7 h-7 rounded-[9px] flex items-center justify-center shrink-0 {{ $acc->platform === 'facebook' ? 'bg-[#1877F2]/15 text-[#1877F2]' : ($acc->platform === 'instagram' ? 'bg-[#E1306C]/15 text-[#E1306C]' : ($acc->platform === 'tiktok' ? 'bg-black/10 dark:bg-white/15 text-black dark:text-white' : 'bg-black/10 text-black dark:text-white')) }}">
                                        @if($acc->platform === 'facebook')
                                            <i data-lucide="facebook" class="w-4 h-4"></i>
                                        @elseif($acc->platform === 'instagram')
                                            <i data-lucide="instagram" class="w-4 h-4"></i>
                                        @elseif($acc->platform === 'tiktok')
                                            <i data-lucide="video" class="w-4 h-4"></i>
                                        @else
                                            <i data-lucide="at-sign" class="w-4 h-4"></i>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        @if($accounts->isEmpty())
                            <div class="p-3 rounded-[12px] bg-[#FF9500]/10 border border-[#FF9500]/25 text-[12px] text-[#FF9500] flex items-center gap-2">
                                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                                <span>Belum ada akun media sosial aktif. <a href="{{ route('social-media.index') }}" class="underline font-bold">Hubungkan akun Meta atau TikTok terlebih dahulu</a>.</span>
                            </div>
                        @endif
                    </div>

                    {{-- 2. Format Selector (Bento Segmented Buttons) --}}
                    <div class="space-y-2">
                        <label class="text-[12.5px] font-bold text-black/80 dark:text-white/80">
                            Format Postingan <span class="text-[#FF3B30]">*</span>
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                            {{-- Foto Tunggal --}}
                            <button type="button" @click="setMediaFormat('photo')"
                                :class="mediaFormat === 'photo' ? 'bg-[#007AFF] text-white shadow-sm ring-2 ring-[#007AFF]/30' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black/70 dark:text-white/70 hover:bg-black/[0.06]'"
                                class="p-2.5 rounded-[14px] border border-black/5 dark:border-white/10 text-left transition-all flex flex-col gap-1">
                                <div class="flex items-center justify-between">
                                    <i data-lucide="image" class="w-4 h-4"></i>
                                    <span class="w-1.5 h-1.5 rounded-full" :class="mediaFormat === 'photo' ? 'bg-white' : 'bg-transparent'"></span>
                                </div>
                                <div class="text-[12px] font-bold leading-tight">Foto</div>
                                <div class="text-[10px] opacity-75 truncate">Feed / Album</div>
                            </button>

                            {{-- Carousel (Instagram & Multi) --}}
                            <button type="button" @click="setMediaFormat('carousel')"
                                :class="mediaFormat === 'carousel' ? 'bg-[#FF9500] text-white shadow-sm ring-2 ring-[#FF9500]/30' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black/70 dark:text-white/70 hover:bg-black/[0.06]'"
                                class="p-2.5 rounded-[14px] border border-black/5 dark:border-white/10 text-left transition-all flex flex-col gap-1">
                                <div class="flex items-center justify-between">
                                    <i data-lucide="layers" class="w-4 h-4"></i>
                                    <span class="w-1.5 h-1.5 rounded-full" :class="mediaFormat === 'carousel' ? 'bg-white' : 'bg-transparent'"></span>
                                </div>
                                <div class="text-[12px] font-bold leading-tight">Carousel</div>
                                <div class="text-[10px] opacity-75 truncate">2-10 Media</div>
                            </button>

                            {{-- Video --}}
                            <button type="button" @click="setMediaFormat('video')"
                                :class="mediaFormat === 'video' ? 'bg-[#007AFF] text-white shadow-sm ring-2 ring-[#007AFF]/30' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black/70 dark:text-white/70 hover:bg-black/[0.06]'"
                                class="p-2.5 rounded-[14px] border border-black/5 dark:border-white/10 text-left transition-all flex flex-col gap-1">
                                <div class="flex items-center justify-between">
                                    <i data-lucide="video" class="w-4 h-4"></i>
                                    <span class="w-1.5 h-1.5 rounded-full" :class="mediaFormat === 'video' ? 'bg-white' : 'bg-transparent'"></span>
                                </div>
                                <div class="text-[12px] font-bold leading-tight">Video</div>
                                <div class="text-[10px] opacity-75 truncate">MP4 / TikTok</div>
                            </button>

                            {{-- Reels --}}
                            <button type="button" @click="setMediaFormat('reels')"
                                :class="mediaFormat === 'reels' ? 'bg-[#AF52DE] text-white shadow-sm ring-2 ring-[#AF52DE]/30' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black/70 dark:text-white/70 hover:bg-black/[0.06]'"
                                class="p-2.5 rounded-[14px] border border-black/5 dark:border-white/10 text-left transition-all flex flex-col gap-1">
                                <div class="flex items-center justify-between">
                                    <i data-lucide="film" class="w-4 h-4"></i>
                                    <span class="w-1.5 h-1.5 rounded-full" :class="mediaFormat === 'reels' ? 'bg-white' : 'bg-transparent'"></span>
                                </div>
                                <div class="text-[12px] font-bold leading-tight">Reels</div>
                                <div class="text-[10px] opacity-75 truncate">9:16 Vertikal</div>
                            </button>

                            {{-- Teks --}}
                            <button type="button" @click="setMediaFormat('text')"
                                :class="mediaFormat === 'text' ? 'bg-[#34C759] text-white shadow-sm ring-2 ring-[#34C759]/30' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black/70 dark:text-white/70 hover:bg-black/[0.06]'"
                                class="p-2.5 rounded-[14px] border border-black/5 dark:border-white/10 text-left transition-all flex flex-col gap-1">
                                <div class="flex items-center justify-between">
                                    <i data-lucide="align-left" class="w-4 h-4"></i>
                                    <span class="w-1.5 h-1.5 rounded-full" :class="mediaFormat === 'text' ? 'bg-white' : 'bg-transparent'"></span>
                                </div>
                                <div class="text-[12px] font-bold leading-tight">Teks</div>
                                <div class="text-[10px] opacity-75 truncate">FB / Threads</div>
                            </button>
                        </div>
                    </div>

                    {{-- 3. Media Upload Area (Single or Carousel) --}}
                    <div x-show="mediaFormat !== 'text'" class="space-y-3 pt-1">
                        <div class="flex items-center justify-between">
                            <label class="text-[12.5px] font-bold text-black/80 dark:text-white/80">
                                Berkas Media <span class="text-[#FF3B30]">*</span>
                            </label>
                            <div class="inline-flex p-0.5 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] text-[11.5px] font-medium">
                                <button type="button" @click="mediaSourceTab = 'upload'"
                                    :class="mediaSourceTab === 'upload' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs' : 'text-black/60 dark:text-white/60'"
                                    class="px-2.5 py-1 rounded-[7px] transition-colors">
                                    Upload Berkas
                                </button>
                                <button type="button" @click="mediaSourceTab = 'url'"
                                    :class="mediaSourceTab === 'url' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs' : 'text-black/60 dark:text-white/60'"
                                    class="px-2.5 py-1 rounded-[7px] transition-colors">
                                    Tautan URL
                                </button>
                            </div>
                        </div>

                        {{-- Mode A: Carousel Multi-Upload (2 - 10 Media) --}}
                        <div x-show="mediaFormat === 'carousel' && mediaSourceTab === 'upload'" class="space-y-3">
                            <input type="file" name="media_files[]" id="carousel_files_input" x-ref="carouselInput" multiple
                                @change="handleCarouselFilesSelect($event)" accept="image/jpeg,image/png,image/webp,video/mp4" class="hidden">

                            <div class="flex items-center justify-between text-[12px]">
                                <span class="font-bold text-black/70 dark:text-white/70">
                                    Urutan Media Carousel: <span class="text-[#FF9500]" x-text="carouselItems.length + ' / 10 media'"></span>
                                </span>
                                <button type="button" @click="$refs.carouselInput.click()"
                                    class="px-3 py-1 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/20 font-bold text-[11.5px] transition-colors flex items-center gap-1">
                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                    <span>Tambah Media</span>
                                </button>
                            </div>

                            {{-- Carousel Empty State --}}
                            <div x-show="carouselItems.length === 0" @click="$refs.carouselInput.click()"
                                class="rounded-[16px] border-2 border-dashed border-black/15 dark:border-white/15 p-6 text-center cursor-pointer hover:border-[#FF9500] hover:bg-[#FF9500]/5 transition-all group">
                                <div class="w-12 h-12 rounded-[14px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center mx-auto mb-2">
                                    <i data-lucide="layers" class="w-6 h-6"></i>
                                </div>
                                <div class="text-[13px] font-bold text-black dark:text-white group-hover:text-[#FF9500]">
                                    Pilih 2 hingga 10 Foto / Video untuk Carousel
                                </div>
                                <p class="text-[11.5px] text-black/50 dark:text-white/50 mt-0.5">
                                    Instagram Carousel mendukung campuran foto dan video berurutan.
                                </p>
                            </div>

                            {{-- Carousel Items Tray with Reordering & Badges --}}
                            <div x-show="carouselItems.length > 0" class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                <template x-for="(item, idx) in carouselItems" :key="idx">
                                    <div class="relative p-2 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 flex flex-col gap-2 group">
                                        {{-- Sort Order Badge --}}
                                        <div class="absolute top-3 left-3 w-5 h-5 rounded-full bg-black/80 text-white text-[10px] font-bold flex items-center justify-center z-10" x-text="idx + 1"></div>

                                        {{-- Thumbnail --}}
                                        <div class="w-full aspect-square rounded-[10px] overflow-hidden bg-black/5 flex items-center justify-center">
                                            <template x-if="item.mime.startsWith('image/')">
                                                <img :src="item.previewUrl" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="item.mime.startsWith('video/')">
                                                <video :src="item.previewUrl" class="w-full h-full object-cover"></video>
                                            </template>
                                        </div>

                                        {{-- Controls: Move Left, Move Right, Delete --}}
                                        <div class="flex items-center justify-between text-[11px] pt-1">
                                            <div class="flex items-center gap-1">
                                                <button type="button" @click="moveCarouselItem(idx, -1)" :disabled="idx === 0"
                                                    class="w-6 h-6 rounded-[6px] bg-black/5 dark:bg-white/10 hover:bg-black/10 flex items-center justify-center disabled:opacity-30">
                                                    ‹
                                                </button>
                                                <button type="button" @click="moveCarouselItem(idx, 1)" :disabled="idx === carouselItems.length - 1"
                                                    class="w-6 h-6 rounded-[6px] bg-black/5 dark:bg-white/10 hover:bg-black/10 flex items-center justify-center disabled:opacity-30">
                                                    ›
                                                </button>
                                            </div>
                                            <button type="button" @click="removeCarouselItem(idx)"
                                                class="w-6 h-6 rounded-[6px] text-[#FF3B30] hover:bg-[#FF3B30]/10 flex items-center justify-center">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Mode B: Single File Upload (Photo, Video, Reels) --}}
                        <div x-show="mediaFormat !== 'carousel' && mediaSourceTab === 'upload'" class="space-y-2.5">
                            <input type="file" name="media_file" id="media_file_input" x-ref="fileInput"
                                @change="handleFileSelect($event)"
                                :accept="mediaFormat === 'photo' ? 'image/jpeg,image/png,image/webp,image/gif' : 'video/mp4,video/quicktime'"
                                class="hidden">

                            {{-- Drop Zone when empty --}}
                            <div x-show="!filePreviewUrl"
                                @click="$refs.fileInput.click()"
                                class="rounded-[16px] border-2 border-dashed border-black/15 dark:border-white/15 p-6 text-center cursor-pointer hover:border-[#007AFF] hover:bg-[#007AFF]/5 transition-all group">
                                <div class="w-12 h-12 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] text-black/50 dark:text-white/50 group-hover:text-[#007AFF] group-hover:scale-105 transition-all flex items-center justify-center mx-auto mb-2.5">
                                    <i data-lucide="upload-cloud" class="w-6 h-6"></i>
                                </div>
                                <div class="text-[13px] font-bold text-black dark:text-white group-hover:text-[#007AFF] transition-colors">
                                    Pilih Berkas Media
                                </div>
                                <p class="text-[11.5px] text-black/50 dark:text-white/50 mt-0.5"
                                   x-text="mediaFormat === 'photo' ? 'Mendukung JPG, PNG, WEBP hingga 100 MB' : 'Mendukung MP4 atau MOV video hingga 100 MB'">
                                </p>
                            </div>

                            {{-- File Preview Card when selected --}}
                            <div x-show="filePreviewUrl" style="display: none;"
                                class="rounded-[16px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 p-3.5 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <template x-if="fileMime && fileMime.startsWith('image/')">
                                        <img :src="filePreviewUrl" alt="Pratinjau Foto" class="w-16 h-16 rounded-[10px] object-cover border border-black/10 dark:border-white/10 shrink-0">
                                    </template>
                                    <template x-if="fileMime && fileMime.startsWith('video/')">
                                        <video :src="filePreviewUrl" controls class="w-24 h-16 rounded-[10px] object-cover bg-black shrink-0"></video>
                                    </template>
                                    <div class="min-w-0">
                                        <div class="text-[13px] font-bold text-black dark:text-white truncate" x-text="fileName"></div>
                                        <div class="text-[11.5px] text-black/50 dark:text-white/50 flex items-center gap-2 mt-0.5">
                                            <span x-text="fileSize"></span>
                                            <span>•</span>
                                            <span class="uppercase font-semibold text-[#007AFF]" x-text="mediaFormat"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button type="button" @click="$refs.fileInput.click()"
                                        class="h-8 px-2.5 rounded-[8px] text-[11.5px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
                                        Ganti
                                    </button>
                                    <button type="button" @click="clearFile()"
                                        class="h-8 w-8 rounded-[8px] text-[#FF3B30] hover:bg-[#FF3B30]/10 transition-colors flex items-center justify-center">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Mode C: Direct URL --}}
                        <div x-show="mediaSourceTab === 'url'" style="display: none;" class="space-y-1.5">
                            <div class="relative">
                                <i data-lucide="link" class="w-4 h-4 absolute left-3 top-3 text-black/40 dark:text-white/40"></i>
                                <input type="url" name="media_url" x-model="mediaUrl"
                                    placeholder="https://domain-anda.com/media/promo.mp4"
                                    class="w-full h-10 pl-9 pr-3 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                            <p class="text-[11px] text-black/50 dark:text-white/50">
                                Pastikan tautan langsung mengarah ke berkas media publik (HTTPS) yang dapat diunduh oleh server Meta atau TikTok.
                            </p>
                        </div>
                    </div>

                    {{-- 4. Text Content & STRICT COOCA 5-HASHTAG COUNTER --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-[12.5px] font-bold text-black/80 dark:text-white/80">
                            <label>Caption Postingan Utama <span class="text-[#FF3B30]">*</span></label>
                            <div class="flex items-center gap-2">
                                {{-- COOCA 5 Hashtag Badge --}}
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold inline-flex items-center gap-1 border transition-colors"
                                    :class="hashtagCount <= 5 ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border-[#34C759]/30' : 'bg-[#FF3B30]/15 text-[#FF3B30] border-[#FF3B30]/30'">
                                    <i data-lucide="hash" class="w-3 h-3"></i>
                                    <span x-text="'Tagar: ' + hashtagCount + ' / 5'"></span>
                                </span>
                                <span class="text-[11.5px] text-black/40 dark:text-white/40 tabular-nums font-normal" x-text="captionText.length + ' / 5000'"></span>
                            </div>
                        </div>

                        <textarea name="content" rows="4" required x-model="captionText"
                            :placeholder="mediaFormat === 'reels' ? 'Tulis caption menarik dan tagar untuk Reels Anda...' : (mediaFormat === 'video' ? 'Jelaskan materi video promo toko Anda...' : 'Tulis pesan promosi, pengumuman promo, diskon, atau informasi produk...')"
                            class="w-full p-3 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] resize-none"></textarea>

                        {{-- Alert Exceeding 5 Hashtags --}}
                        <div x-show="hashtagCount > 5" class="p-3 rounded-[12px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[12px] text-[#FF3B30] flex items-start gap-2">
                            <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0 mt-0.5"></i>
                            <div>
                                <strong>Aturan COOCA:</strong> Maksimal 5 hashtag unik per postingan! Saat ini terdeteksi <strong x-text="hashtagCount"></strong> hashtag. Kurangi <span x-text="hashtagCount - 5"></span> hashtag agar dapat dipublikasikan.
                            </div>
                        </div>
                    </div>

                    {{-- 5. Collapsible Channel Caption Overrides (Optional) --}}
                    <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3">
                        <button type="button" @click="showOverrides = !showOverrides"
                            class="w-full flex items-center justify-between text-left text-[12.5px] font-bold text-black/80 dark:text-white/80">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="sliders-horizontal" class="w-4 h-4 text-[#007AFF]"></i>
                                <span>Kustomisasi Caption per Saluran (Opsional)</span>
                            </span>
                            <span class="text-[11px] text-[#007AFF]" x-text="showOverrides ? 'Tutup' : 'Buka Kustomisasi'"></span>
                        </button>

                        <div x-show="showOverrides" style="display: none;" class="space-y-3 pt-2 border-t border-black/5 dark:border-white/5">
                            <p class="text-[11.5px] text-black/50 dark:text-white/50">
                                Bila diisi, saluran di bawah ini akan menggunakan teks khusus sebagai pengganti caption utama.
                            </p>
                            @foreach($accounts as $acc)
                                <div class="space-y-1">
                                    <label class="text-[11.5px] font-semibold text-black/70 dark:text-white/70 capitalize flex items-center gap-1.5">
                                        <span>Khusus {{ $acc->platform }} ({{ $acc->account_name }}):</span>
                                    </label>
                                    <textarea name="custom_captions[{{ $acc->id }}]" rows="2" placeholder="Biarkan kosong untuk menggunakan caption utama..."
                                        class="w-full p-2.5 rounded-[10px] text-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF] resize-none"></textarea>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- 6. Waktu & Penjadwalan Publikasi (Multi-Mode & Per-Channel Support) --}}
                    <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-[13px] font-bold text-black dark:text-white">Waktu Publikasi Saluran</span>
                                <p class="text-[11.5px] text-black/50 dark:text-white/50">Tentukan kapan konten ini ditayangkan ke masing-masing media sosial</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-[#007AFF]/10 text-[#007AFF]">
                                Fleksibel
                            </span>
                        </div>

                        {{-- Mode Selector --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-[12px]">
                            <label class="p-2.5 rounded-[10px] border cursor-pointer transition-all flex items-center gap-2"
                                :class="scheduleMode === 'all_now' ? 'bg-[#007AFF]/10 border-[#007AFF] text-black dark:text-white font-bold shadow-xs' : 'bg-black/[0.02] dark:bg-white/[0.04] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                                <input type="radio" name="schedule_mode" value="all_now" x-model="scheduleMode" class="sr-only">
                                <i data-lucide="zap" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Semua Sekarang</span>
                            </label>

                            <label class="p-2.5 rounded-[10px] border cursor-pointer transition-all flex items-center gap-2"
                                :class="scheduleMode === 'all_same' ? 'bg-[#5856D6]/10 border-[#5856D6] text-black dark:text-white font-bold shadow-xs' : 'bg-black/[0.02] dark:bg-white/[0.04] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                                <input type="radio" name="schedule_mode" value="all_same" x-model="scheduleMode" class="sr-only">
                                <i data-lucide="clock" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                                <span>Jadwal Serentak</span>
                            </label>

                            <label class="p-2.5 rounded-[10px] border cursor-pointer transition-all flex items-center gap-2"
                                :class="scheduleMode === 'per_channel' ? 'bg-[#FF9500]/10 border-[#FF9500] text-black dark:text-white font-bold shadow-xs' : 'bg-black/[0.02] dark:bg-white/[0.04] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                                <input type="radio" name="schedule_mode" value="per_channel" x-model="scheduleMode" class="sr-only">
                                <i data-lucide="sliders" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                <span>Beda per Saluran</span>
                            </label>
                        </div>

                        {{-- Mode B: Jadwal Serentak (Satu waktu untuk semua saluran) --}}
                        <div x-show="scheduleMode === 'all_same'" style="display: none;" class="pt-2 border-t border-black/5 dark:border-white/5 space-y-1.5">
                            <label class="block text-[11.5px] font-semibold text-black/70 dark:text-white/70">Pilih Tanggal &amp; Jam Penayangan Serentak</label>
                            <input type="datetime-local" name="scheduled_at" x-model="globalScheduleTime"
                                class="w-full h-9 px-3 rounded-[10px] text-[12.5px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6]">
                            <p class="text-[11px] text-black/50 dark:text-white/50">
                                Seluruh saluran terpilih akan otomatis dipublikasikan bersamaan oleh cron scheduler saat waktu tiba.
                            </p>
                        </div>

                        {{-- Mode C: Beda Waktu per Saluran (Bento Card per Akun Terpilih) --}}
                        <div x-show="scheduleMode === 'per_channel'" style="display: none;" class="pt-2 border-t border-black/5 dark:border-white/5 space-y-2.5">
                            <p class="text-[11.5px] text-black/60 dark:text-white/60">
                                Tentukan waktu khusus untuk masing-masing saluran (misal: Instagram langsung, Facebook jam 2 siang, TikTok besok):
                            </p>
                            <div class="space-y-2">
                                @foreach($accounts as $acc)
                                    <div x-show="selectedAccounts.includes('{{ $acc->id }}')"
                                        class="p-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 space-y-2">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <div class="w-6 h-6 rounded-[7px] flex items-center justify-center shrink-0 {{ $acc->platform === 'facebook' ? 'bg-[#1877F2]/15 text-[#1877F2]' : ($acc->platform === 'instagram' ? 'bg-[#E1306C]/15 text-[#E1306C]' : ($acc->platform === 'tiktok' ? 'bg-black/10 dark:bg-white/15 text-black dark:text-white' : 'bg-black/10 text-black dark:text-white')) }}">
                                                    @if($acc->platform === 'facebook')
                                                        <i data-lucide="facebook" class="w-3.5 h-3.5"></i>
                                                    @elseif($acc->platform === 'instagram')
                                                        <i data-lucide="instagram" class="w-3.5 h-3.5"></i>
                                                    @elseif($acc->platform === 'tiktok')
                                                        <i data-lucide="video" class="w-3.5 h-3.5"></i>
                                                    @else
                                                        <i data-lucide="at-sign" class="w-3.5 h-3.5"></i>
                                                    @endif
                                                </div>
                                                <span class="text-[12px] font-bold text-black dark:text-white truncate">{{ $acc->account_name }}</span>
                                                <span class="text-[10.5px] text-black/50 dark:text-white/50 uppercase font-mono">({{ $acc->platform }})</span>
                                            </div>
                                            <div class="inline-flex p-0.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] text-[11px]">
                                                <button type="button" @click="channelTiming['{{ $acc->id }}'] = 'now'"
                                                    :class="channelTiming['{{ $acc->id }}'] === 'now' ? 'bg-white dark:bg-[#1C1C1E] text-[#007AFF] font-bold shadow-xs' : 'text-black/60 dark:text-white/60'"
                                                    class="px-2 py-0.5 rounded-[6px] transition-colors">
                                                    Langsung
                                                </button>
                                                <button type="button" @click="channelTiming['{{ $acc->id }}'] = 'schedule'"
                                                    :class="channelTiming['{{ $acc->id }}'] === 'schedule' ? 'bg-white dark:bg-[#1C1C1E] text-[#FF9500] font-bold shadow-xs' : 'text-black/60 dark:text-white/60'"
                                                    class="px-2 py-0.5 rounded-[6px] transition-colors">
                                                    Jadwalkan
                                                </button>
                                            </div>
                                        </div>

                                        <input type="hidden" :name="'channel_schedule_modes[{{ $acc->id }}]'" :value="channelTiming['{{ $acc->id }}']">

                                        <div x-show="channelTiming['{{ $acc->id }}'] === 'schedule'" class="pt-1.5 border-t border-black/5 dark:border-white/5">
                                            <input type="datetime-local" :name="'channel_scheduled_at[{{ $acc->id }}]'" x-model="channelScheduledAts['{{ $acc->id }}']"
                                                class="w-full h-8 px-2.5 rounded-[8px] text-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#FF9500]">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="pt-3 flex items-center justify-end gap-2 border-t border-black/5 dark:border-white/10">
                        <button type="button" @click="openComposerModal = false"
                            class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="!captionText.trim() || hashtagCount > 5 || selectedAccounts.length === 0 || isSubmitting"
                            class="h-9 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] disabled:opacity-50 disabled:pointer-events-none transition-all flex items-center gap-1.5 shadow-sm">
                            <template x-if="!isSubmitting">
                                <i data-lucide="send" class="w-4 h-4"></i>
                            </template>
                            <template x-if="isSubmitting">
                                <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                            </template>
                            <span x-text="isSubmitting ? 'Mengunggah & Memproses...' : (scheduleMode !== 'all_now' ? 'Simpan Jadwal' : 'Publikasikan Sekarang')"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        function socialPostsManager() {
            return {
                openComposerModal: false,
                allAccountIds: [
                    @foreach($accounts as $acc)
                        '{{ $acc->id }}',
                    @endforeach
                ],
                selectedAccounts: [
                    @foreach($accounts as $acc)
                        '{{ $acc->id }}',
                    @endforeach
                ],
                mediaFormat: 'photo',
                mediaSourceTab: 'upload',
                captionText: '',
                mediaUrl: '',
                scheduleMode: 'all_now',
                globalScheduleTime: '',
                channelTiming: {
                    @foreach($accounts as $acc)
                        '{{ $acc->id }}': 'now',
                    @endforeach
                },
                channelScheduledAts: {
                    @foreach($accounts as $acc)
                        '{{ $acc->id }}': '',
                    @endforeach
                },
                showOverrides: false,
                filePreviewUrl: null,
                fileName: '',
                fileSize: '',
                fileMime: '',
                carouselItems: [],
                isSubmitting: false,

                get uniqueHashtags() {
                    if (!this.captionText) return [];
                    const matches = this.captionText.match(/#[a-zA-Z0-9_\u0080-\uFFFF]+/gu) || [];
                    const unique = new Set(matches.map(t => t.toLowerCase()));
                    return Array.from(unique);
                },

                get hashtagCount() {
                    return this.uniqueHashtags.length;
                },

                toggleSelectAll() {
                    if (this.selectedAccounts.length === this.allAccountIds.length) {
                        this.selectedAccounts = [];
                    } else {
                        this.selectedAccounts = [...this.allAccountIds];
                    }
                },

                setMediaFormat(fmt) {
                    this.mediaFormat = fmt;
                    if (fmt === 'text') {
                        this.clearFile();
                        this.carouselItems = [];
                        this.mediaUrl = '';
                    }
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                },

                handleFileSelect(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    this.fileName = file.name;
                    this.fileMime = file.type;
                    const sizeInMB = (file.size / (1024 * 1024)).toFixed(2);
                    this.fileSize = sizeInMB >= 1 ? sizeInMB + ' MB' : Math.round(file.size / 1024) + ' KB';

                    if (this.filePreviewUrl) {
                        URL.revokeObjectURL(this.filePreviewUrl);
                    }
                    this.filePreviewUrl = URL.createObjectURL(file);

                    // Auto-sync format if user chose video while in photo mode
                    if (file.type.startsWith('video/') && this.mediaFormat === 'photo') {
                        this.mediaFormat = 'video';
                    } else if (file.type.startsWith('image/') && (this.mediaFormat === 'video' || this.mediaFormat === 'reels')) {
                        this.mediaFormat = 'photo';
                    }

                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                },

                handleCarouselFilesSelect(event) {
                    const files = Array.from(event.target.files);
                    if (!files.length) return;

                    for (const file of files) {
                        if (this.carouselItems.length >= 10) break;
                        this.carouselItems.push({
                            file: file,
                            name: file.name,
                            mime: file.type,
                            previewUrl: URL.createObjectURL(file)
                        });
                    }

                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                },

                moveCarouselItem(index, direction) {
                    const targetIndex = index + direction;
                    if (targetIndex < 0 || targetIndex >= this.carouselItems.length) return;
                    const temp = this.carouselItems[index];
                    this.carouselItems[index] = this.carouselItems[targetIndex];
                    this.carouselItems[targetIndex] = temp;
                },

                removeCarouselItem(index) {
                    const item = this.carouselItems[index];
                    if (item && item.previewUrl) {
                        URL.revokeObjectURL(item.previewUrl);
                    }
                    this.carouselItems.splice(index, 1);
                },

                clearFile() {
                    if (this.filePreviewUrl) {
                        URL.revokeObjectURL(this.filePreviewUrl);
                    }
                    this.filePreviewUrl = null;
                    this.fileName = '';
                    this.fileSize = '';
                    this.fileMime = '';
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.value = '';
                    }
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                }
            };
        }
    </script>
@endsection
