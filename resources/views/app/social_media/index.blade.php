@extends('layouts.app', [
    'title' => 'Media Sosial - ' . $business->name,
    'headerTitle' => 'Media Sosial & Pemasaran',
    'headerSubtitle' => 'Kelola Facebook Page, Instagram Bisnis, dan Threads untuk toko Anda',
])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="socialMediaGateway()" x-init="init()">

        {{-- 0. BREADCRUMB --}}
        <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 py-0.5 whitespace-nowrap print:hidden">
            <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors font-medium">Dashboard</a>
            <span>›</span>
            <span class="text-black/60 dark:text-white/60 font-medium">Komunikasi &amp; Pemasaran</span>
            <span>›</span>
            <span class="text-black/80 dark:text-white/80 font-medium">Media Sosial</span>
        </nav>

        {{-- 1. PAGE HEADER --}}
        <header class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5 shadow-sm">
            <div class="space-y-1.5 max-w-2xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1.5 bg-[#1877F2]/10 text-[#1877F2]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#1877F2]"></span>
                        <span>Meta Graph API (FB, IG, Threads)</span>
                    </span>
                    <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1.5 bg-black/10 dark:bg-white/10 text-black dark:text-white">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#000000] dark:bg-[#ffffff]"></span>
                        <span>TikTok Official API</span>
                    </span>
                </div>
                <h1 class="text-[20px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight">
                    Pengelolaan Media Sosial &amp; Konten Terpadu
                </h1>
                <p class="text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                    Kelola seluruh media sosial toko <strong class="text-black dark:text-white font-medium">{{ $business->name }}</strong> dari satu dashboard: Facebook, Instagram, Threads, dan TikTok.
                </p>
            </div>

            <div class="flex items-center gap-2.5 w-full lg:w-auto">
                <a href="{{ route('social-media.posts.index') }}"
                    class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 w-full sm:w-auto shadow-sm">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Buat Postingan Baru</span>
                </a>
            </div>
        </header>

        {{-- 2. MODULE NAVIGATION SUB-TABS --}}
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-2 sm:p-2.5 flex items-center justify-between shadow-sm">
            <div class="inline-flex p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] border border-black/5 dark:border-white/5 w-full sm:w-auto overflow-x-auto text-[13px] font-medium">
                <a href="{{ route('social-media.index') }}"
                    class="h-8 px-4 rounded-[9px] bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm flex items-center gap-2 whitespace-nowrap">
                    <i data-lucide="link" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Koneksi Akun</span>
                </a>
                <a href="{{ route('social-media.posts.index') }}"
                    class="h-8 px-4 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                    <i data-lucide="image" class="w-4 h-4"></i>
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
                    @if($summary['unread_comments'] > 0)
                        <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-[#FF3B30] text-white">{{ $summary['unread_comments'] }}</span>
                    @endif
                </a>
                <a href="{{ route('social-media.insights.index') }}"
                    class="h-8 px-4 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                    <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                    <span>Analitik &amp; Performa</span>
                </a>
            </div>
        </div>

        {{-- 3. MAIN COCKPIT GRID --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- LEFT COLUMN: CONNECTED ACCOUNTS & ONBOARDING --}}
            <div class="lg:col-span-7 space-y-6">

                <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 sm:p-7 space-y-5 transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-[16px] bg-gradient-to-br from-[#1877F2] via-[#E1306C] to-[#000000] text-white flex items-center justify-center shrink-0 shadow-md shadow-[#1877F2]/25">
                                <i data-lucide="share-2" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h2 class="text-[17px] font-bold text-black dark:text-white tracking-tight">Akun Media Sosial Toko</h2>
                                <p class="text-[12.5px] text-black/55 dark:text-white/55 mt-0.5">Facebook Page, Instagram Bisnis, Threads &amp; TikTok</p>
                            </div>
                        </div>

                        <div>
                            <span class="px-3 py-1.5 rounded-full text-[11.5px] font-bold inline-flex items-center gap-1.5 {{ $accounts->where('status', 'active')->count() > 0 ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25' : 'bg-black/[0.05] dark:bg-white/[0.08] text-black/55 dark:text-white/55 border border-black/10 dark:border-white/10' }}">
                                <span class="w-2 h-2 rounded-full {{ $accounts->where('status', 'active')->count() > 0 ? 'bg-[#34C759]' : 'bg-[#FF9500]' }}"></span>
                                <span>{{ $accounts->where('status', 'active')->count() > 0 ? $accounts->where('status', 'active')->count() . ' Akun Terhubung' : 'Belum Terhubung' }}</span>
                            </span>
                        </div>
                    </div>

                    {{-- Connected Accounts Cards List --}}
                    @if($accounts->where('status', 'active')->isNotEmpty())
                        <div class="space-y-3">
                            <span class="text-[11.5px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 block">Aset Aktif Terhubung:</span>
                            <div class="grid grid-cols-1 gap-3">
                                @foreach($accounts->where('status', 'active') as $acc)
                                    <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-10 h-10 rounded-[12px] flex items-center justify-center shrink-0 {{ $acc->platform === 'facebook' ? 'bg-[#1877F2]/12 text-[#1877F2]' : ($acc->platform === 'instagram' ? 'bg-[#E1306C]/12 text-[#E1306C]' : ($acc->platform === 'tiktok' ? 'bg-black/10 dark:bg-white/15 text-black dark:text-white' : 'bg-black/10 text-black dark:text-white')) }}">
                                                @if($acc->platform === 'facebook')
                                                    <i data-lucide="facebook" class="w-5 h-5"></i>
                                                @elseif($acc->platform === 'instagram')
                                                    <i data-lucide="instagram" class="w-5 h-5"></i>
                                                @elseif($acc->platform === 'tiktok')
                                                    <i data-lucide="video" class="w-5 h-5"></i>
                                                @else
                                                    <i data-lucide="at-sign" class="w-5 h-5"></i>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-bold text-black dark:text-white text-[13.5px] truncate">{{ $acc->account_name }}</div>
                                                <div class="text-[11.5px] text-black/50 dark:text-white/50 flex items-center gap-2">
                                                    <span class="capitalize font-semibold">{{ $acc->platform }}</span>
                                                    @if($acc->username)
                                                        <span>•</span>
                                                        <span class="font-mono text-[#007AFF]">{{ $acc->username }}</span>
                                                    @endif
                                                    @if($acc->platform === 'tiktok' && $acc->token_expires_at)
                                                        <span>•</span>
                                                        <span class="text-[10.5px] text-[#34C759]">Auto-Refresh Active</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0">
                                            <button type="button" @click="disconnect('{{ $acc->id }}')"
                                                class="min-h-[34px] px-3 rounded-[9px] text-[11.5px] font-semibold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition-all">
                                                Putuskan
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- 1-Click Onboarding Banners: META & TIKTOK --}}
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-[14px] font-bold text-black dark:text-white">
                                Hubungkan Akun Media Sosial (Metode 1-Klik)
                            </h3>
                            <span class="text-[11px] text-black/45 dark:text-white/45">Meta Graph &amp; TikTok API</span>
                        </div>

                        {{-- Meta Connection Card --}}
                        <div class="p-5 sm:p-6 rounded-[18px] bg-gradient-to-br from-[#1877F2]/10 via-white dark:via-[#1C1C1E] to-[#E1306C]/8 border border-[#1877F2]/20 space-y-4">
                            <div class="space-y-0.5">
                                <h3 class="text-[15px] font-bold text-black dark:text-white">
                                    {{ $accounts->whereIn('platform', ['facebook', 'instagram', 'threads'])->where('status', 'active')->isNotEmpty() ? 'Perbarui Akun Meta' : 'Hubungkan Akun Meta (Facebook, Instagram & Threads)' }}
                                </h3>
                                <p class="text-[12px] text-black/55 dark:text-white/55">
                                    Masuk melalui dialog resmi Meta Facebook untuk menghubungkan Halaman FB, Akun Instagram Bisnis, dan Threads.
                                </p>
                            </div>

                            <button type="button" @click="launchMetaLogin()" :disabled="loading"
                                class="w-full min-h-[46px] rounded-[14px] bg-gradient-to-r from-[#1877F2] to-[#007AFF] hover:from-[#166FE5] hover:to-[#0071E3] text-white font-bold text-[13.5px] flex items-center justify-center gap-2 shadow-md shadow-[#1877F2]/25 active:scale-[0.98] transition-all disabled:opacity-50">
                                <i data-lucide="loader-2" x-show="loading" class="w-4 h-4 animate-spin"></i>
                                <i data-lucide="share-2" x-show="!loading" class="w-4 h-4"></i>
                                <span x-text="loading ? 'Menghubungkan ke Meta...' : 'Hubungkan Meta (Facebook & Instagram)'"></span>
                            </button>
                        </div>

                        {{-- TikTok Connection Card --}}
                        <div class="p-5 sm:p-6 rounded-[18px] bg-gradient-to-br from-black/5 via-white dark:via-[#1C1C1E] to-black/10 dark:to-white/5 border border-black/10 dark:border-white/10 space-y-4">
                            <div class="space-y-0.5">
                                <h3 class="text-[15px] font-bold text-black dark:text-white">
                                    {{ $accounts->where('platform', 'tiktok')->where('status', 'active')->isNotEmpty() ? 'Perbarui Akun TikTok' : 'Hubungkan Akun TikTok (Content Posting API)' }}
                                </h3>
                                <p class="text-[12px] text-black/55 dark:text-white/55">
                                    Otorisasi resmi akun TikTok Anda untuk mempublikasikan video dan foto langsung dari dashboard COOCA.
                                </p>
                            </div>

                            <a href="{{ route('social-media.tiktok.connect') }}"
                                class="w-full min-h-[46px] rounded-[14px] bg-black dark:bg-white text-white dark:text-black hover:bg-black/90 dark:hover:bg-white/90 font-bold text-[13.5px] flex items-center justify-center gap-2 shadow-md active:scale-[0.98] transition-all">
                                <i data-lucide="video" class="w-4 h-4"></i>
                                <span>Hubungkan Akun TikTok Resmi</span>
                            </a>
                        </div>

                        {{-- Alert Error --}}
                        <div x-show="errorMessage" x-transition class="p-3.5 rounded-[12px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[12px] text-[#C41E17] dark:text-[#FF453A] flex items-center gap-2">
                            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                            <span x-text="errorMessage"></span>
                        </div>

                        {{-- Alert Success --}}
                        <div x-show="successMessage" x-transition class="p-3.5 rounded-[12px] bg-[#34C759]/12 border border-[#34C759]/30 text-[12px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-2">
                            <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                            <span x-text="successMessage"></span>
                        </div>
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN: RECENT POSTS & QUICK STATS --}}
            <div class="lg:col-span-5 space-y-6">

                {{-- Security & Privacy Bento Card --}}
                <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 space-y-3.5">
                    <div class="flex items-center gap-2.5 font-bold text-[14px] text-black dark:text-white">
                        <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759]"></i>
                        <span>Keamanan &amp; Isolasi Data Bisnis</span>
                    </div>
                    <ul class="space-y-2 text-[12px] text-black/65 dark:text-white/65">
                        <li class="flex items-start gap-2">
                            <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0 mt-0.5"></i>
                            <span>Token Page disimpan dengan enkripsi AES-256 (Laravel APP_KEY).</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0 mt-0.5"></i>
                            <span>Data postingan &amp; komentar terisolasi ketat per toko Anda (Zero Cross-Tenant Leakage).</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0 mt-0.5"></i>
                            <span>Token tidak pernah kedaluwarsa (Long-Lived Page Access Token).</span>
                        </li>
                    </ul>
                </div>

                {{-- Recent Posts List --}}
                <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 space-y-3.5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[14px] font-bold text-black dark:text-white">Postingan Terbaru</h3>
                        <a href="{{ route('social-media.posts.index') }}" class="text-[12px] font-semibold text-[#007AFF] hover:underline">Lihat Semua</a>
                    </div>

                    <div class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($recentPosts as $p)
                            <div class="py-3 first:pt-0 last:pb-0 space-y-1">
                                <div class="flex items-center justify-between text-[11.5px]">
                                    <span class="font-bold uppercase tracking-wider text-[#007AFF]">{{ $p->platform }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold {{ $p->status === 'published' ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/5 text-black/50' }}">
                                        {{ $p->status }}
                                    </span>
                                </div>
                                <p class="text-[12.5px] text-black/80 dark:text-white/80 line-clamp-2">{{ $p->content }}</p>
                                <span class="text-[11px] text-black/45 dark:text-white/45 block">{{ $p->created_at->diffForHumans() }}</span>
                            </div>
                        @empty
                            <p class="py-4 text-center text-[12.5px] text-black/40 dark:text-white/40">Belum ada riwayat postingan.</p>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function socialMediaGateway() {
            return {
                loading: false,
                errorMessage: null,
                successMessage: null,

                init() {
                    // Cek apakah ada parameter 'code' dari OAuth callback redirect
                    const urlParams = new URLSearchParams(window.location.search);
                    const code = urlParams.get('code');
                    if (code) {
                        this.handleOAuthCode(code);
                        // Bersihkan URL tanpa refresh
                        window.history.replaceState({}, document.title, window.location.pathname);
                    }
                },

                async launchMetaLogin() {
                    this.loading = true;
                    this.errorMessage = null;
                    this.successMessage = null;

                    try {
                        const res = await fetch('{{ route('social-media.config') }}');
                        const data = await res.json();

                        if (!data.success || !data.login_url) {
                            this.errorMessage = 'Konfigurasi Meta App belum diisi oleh Superadmin.';
                            this.loading = false;
                            return;
                        }

                        // Buka jendela popup OAuth resmi Meta
                        const width = 600;
                        const height = 700;
                        const left = (window.innerWidth - width) / 2;
                        const top = (window.innerHeight - height) / 2;

                        window.location.href = data.login_url;
                    } catch (e) {
                        this.errorMessage = 'Gagal menghubungi server: ' + (e.message || e);
                        this.loading = false;
                    }
                },

                async handleOAuthCode(code) {
                    this.loading = true;
                    try {
                        const res = await fetch('{{ route('social-media.exchange-token') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                            },
                            body: JSON.stringify({ code: code })
                        });

                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.successMessage = data.message || 'Media sosial berhasil terhubung!';
                            setTimeout(() => window.location.reload(), 1200);
                        } else {
                            this.errorMessage = data.error || 'Gagal menghubungkan akun Meta.';
                        }
                    } catch (e) {
                        this.errorMessage = 'Kesalahan koneksi saat menyimpan otorisasi.';
                    } finally {
                        this.loading = false;
                    }
                },

                async disconnect(accountId) {
                    let confirmed = false;
                    if (window.AppAlert) {
                        confirmed = await AppAlert.confirm({
                            title: 'Putuskan Akun?',
                            message: 'Yakin ingin memutuskan akun media sosial ini dari COOCA?',
                            type: 'danger',
                            confirmText: 'Ya, Putuskan',
                            cancelText: 'Batal'
                        });
                    } else {
                        confirmed = confirm('Yakin ingin memutuskan akun media sosial ini?');
                    }
                    if (!confirmed) return;

                    try {
                        const res = await fetch('{{ route('social-media.disconnect') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                            },
                            body: JSON.stringify({ account_id: accountId })
                        });

                        const data = await res.json();
                        if (data.success) {
                            window.location.reload();
                        }
                    } catch (e) {}
                }
            };
        }
    </script>
@endpush
