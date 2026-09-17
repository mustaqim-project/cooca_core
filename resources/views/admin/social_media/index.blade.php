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
                    <p class="text-[12.5px] sm:text-[13px] text-black/55 dark:text-white/55 mt-0.5 truncate">Pusat integrasi resmi Meta App, Webhook terpusat, pengawasan merchant, dan kepatuhan App Review</p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <span class="px-3.5 py-1.5 rounded-full text-[12px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                    <span>Graph API {{ $platform['graph_version'] }}</span>
                </span>
            </div>
        </div>

        {{-- 2. STATS KPI OVERVIEW --}}
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 sm:gap-4">
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
            <div class="p-4 sm:p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm space-y-1">
                <span class="text-[11.5px] font-semibold uppercase text-black/50 dark:text-white/50">Akun TikTok</span>
                <p class="text-[22px] sm:text-[26px] font-bold text-black dark:text-white tabular-nums tracking-tight">{{ number_format($summary['tiktok_accounts_count'] ?? 0) }}</p>
            </div>
            <div class="p-4 sm:p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm space-y-1">
                <span class="text-[11.5px] font-semibold uppercase text-black/50 dark:text-white/50">Total Postingan</span>
                <p class="text-[22px] sm:text-[26px] font-bold text-[#007AFF] tabular-nums tracking-tight">{{ number_format($summary['total_posts']) }}</p>
            </div>
        </div>

        {{-- 3. SEGMENTED NAVIGATION TABS --}}
        <div class="p-1.5 bg-black/[0.04] dark:bg-white/[0.06] rounded-[16px] flex items-center gap-1.5 overflow-x-auto shadow-inner">
            <button type="button" @click="activeTab = 'settings'"
                :class="activeTab === 'settings' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                class="min-h-[40px] px-4 rounded-[11px] text-[13px] transition-all flex items-center gap-2 shrink-0">
                <i data-lucide="sliders" class="w-4 h-4 text-[#007AFF]"></i>
                <span>Konfigurasi Provider (Meta &amp; TikTok)</span>
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

        {{-- 4. TAB 1: PROVIDER CONFIGURATION (META & TIKTOK) --}}
        <div x-show="activeTab === 'settings'" class="space-y-6">
            <form method="POST" action="{{ route('admin.social-media.config') }}" class="space-y-6">
                @csrf
                
                {{-- META APP CARD --}}
                <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 sm:p-7 space-y-5">
                    <div class="flex items-center gap-3 pb-3 border-b border-black/5 dark:border-white/10">
                        <div class="w-9 h-9 rounded-[10px] bg-[#1877F2]/10 text-[#1877F2] flex items-center justify-center">
                            <i data-lucide="key" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h2 class="text-[16px] font-bold text-black dark:text-white">Kredensial Meta App (Facebook Login for Business &amp; Graph API)</h2>
                            <p class="text-[12px] text-black/50 dark:text-white/50">Gunakan App ID dan Secret dari Meta for Developers untuk mengelola Facebook Page, Instagram Bisnis, dan Threads</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 mb-1.5">Meta App ID (Client ID)</label>
                            <input type="text" name="app_id" value="{{ old('app_id', $platform['app_id']) }}" placeholder="Contoh: 123456789012345"
                                class="w-full bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 min-h-[44px] text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55">Meta App Secret</label>
                                <button type="button" @click="showAppSecret = !showAppSecret" class="text-[11px] font-bold text-[#007AFF] hover:underline">
                                    <span x-text="showAppSecret ? 'Sembunyikan' : 'Tampilkan'"></span>
                                </button>
                            </div>
                            <div class="relative">
                                <input :type="showAppSecret ? 'text' : 'password'" name="app_secret" value="{{ old('app_secret', $platform['app_secret']) }}" placeholder="Biarkan kosong jika tidak ingin mengubah"
                                    class="w-full bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 pr-10 min-h-[44px] text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                                <button type="button" @click="showAppSecret = !showAppSecret" class="absolute right-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40">
                                    <i data-lucide="eye" x-show="!showAppSecret" class="w-4 h-4"></i>
                                    <i data-lucide="eye-off" x-show="showAppSecret" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 mb-1.5">Webhook Verify Token</label>
                            <input type="text" name="webhook_verify_token" value="{{ old('webhook_verify_token', $platform['webhook_verify_token']) }}"
                                class="w-full bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 min-h-[44px] text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 mb-1.5">Graph Version</label>
                                <input type="text" name="graph_version" value="{{ old('graph_version', $platform['graph_version']) }}"
                                    class="w-full bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 min-h-[44px] text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 mb-1.5">Graph API URL</label>
                                <input type="url" name="graph_url" value="{{ old('graph_url', $platform['graph_url']) }}"
                                    class="w-full bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 min-h-[44px] text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                            </div>
                        </div>
                    </div>

                    {{-- Webhook URL Readonly Box --}}
                    <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[11.5px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">URL Webhook Terpusat (Pasang di Meta App Dashboard)</span>
                            <span class="text-[10.5px] font-bold px-2 py-0.5 rounded-full bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">HMAC-SHA256 Verified</span>
                        </div>
                        <div class="flex items-center justify-between gap-2 p-2.5 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10">
                            <code class="text-[12px] font-mono text-[#007AFF] break-all">{{ $platform['webhook_url'] }}</code>
                            <button type="button" @click="copyText('{{ $platform['webhook_url'] }}', 'wh')"
                                class="text-[11.5px] font-bold text-[#007AFF] hover:underline shrink-0">
                                <span x-text="copied === 'wh' ? 'Tersalin' : 'Salin URL'"></span>
                            </button>
                        </div>
                        <p class="text-[11.5px] text-black/50 dark:text-white/50">
                            Meta akan mengirimkan notifikasi komentar dan pesan secara real-time ke endpoint ini. Sistem COOCA secara otomatis memetakan Page ID / Instagram ID ke merchant yang tepat.
                        </p>
                    </div>
                </div>

                {{-- TIKTOK DEVELOPER PLATFORM CARD --}}
                <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 sm:p-7 space-y-5">
                    <div class="flex items-center gap-3 pb-3 border-b border-black/5 dark:border-white/10">
                        <div class="w-9 h-9 rounded-[10px] bg-black/10 dark:bg-white/10 text-black dark:text-white flex items-center justify-center">
                            <i data-lucide="video" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h2 class="text-[16px] font-bold text-black dark:text-white">Kredensial TikTok Developer (Content Posting API)</h2>
                            <p class="text-[12px] text-black/50 dark:text-white/50">Diperoleh dari TikTok for Developers App Console untuk mengizinkan merchant menghubungkan akun TikTok &amp; posting video/foto</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 mb-1.5">TikTok Client Key</label>
                            <input type="text" name="tiktok_client_key" value="{{ old('tiktok_client_key', $platform['tiktok_client_key']) }}" placeholder="Contoh: aw0123456789"
                                class="w-full bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 min-h-[44px] text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55">TikTok Client Secret</label>
                                <button type="button" @click="showTikTokSecret = !showTikTokSecret" class="text-[11px] font-bold text-[#007AFF] hover:underline">
                                    <span x-text="showTikTokSecret ? 'Sembunyikan' : 'Tampilkan'"></span>
                                </button>
                            </div>
                            <div class="relative">
                                <input :type="showTikTokSecret ? 'text' : 'password'" name="tiktok_client_secret" value="{{ old('tiktok_client_secret', $platform['tiktok_client_secret']) }}" placeholder="Biarkan kosong jika tidak ingin mengubah"
                                    class="w-full bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 pr-10 min-h-[44px] text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                                <button type="button" @click="showTikTokSecret = !showTikTokSecret" class="absolute right-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40">
                                    <i data-lucide="eye" x-show="!showTikTokSecret" class="w-4 h-4"></i>
                                    <i data-lucide="eye-off" x-show="showTikTokSecret" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- TikTok Redirect URI Box --}}
                    <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[11.5px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">TikTok Redirect URI (Daftarkan pada App Details &gt; Redirect domains)</span>
                            <span class="text-[10.5px] font-bold px-2 py-0.5 rounded-full bg-[#007AFF]/15 text-[#007AFF]">OAuth 2.0 PKCE</span>
                        </div>
                        <div class="flex items-center justify-between gap-2 p-2.5 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10">
                            <code class="text-[12px] font-mono text-[#007AFF] break-all">{{ $platform['tiktok_redirect_uri'] }}</code>
                            <button type="button" @click="copyText('{{ $platform['tiktok_redirect_uri'] }}', 'tt')"
                                class="text-[11.5px] font-bold text-[#007AFF] hover:underline shrink-0">
                                <span x-text="copied === 'tt' ? 'Tersalin' : 'Salin URL'"></span>
                            </button>
                        </div>
                        <p class="text-[11.5px] text-black/50 dark:text-white/50">
                            Pastikan domain ini telah terdaftar di dashboard TikTok Developer pada menu <strong>Login Kit</strong> dan <strong>Content Posting API</strong> agar otorisasi tidak ditolak.
                        </p>
                    </div>

                    <button type="submit"
                        class="min-h-[44px] px-6 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-sm">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Pengaturan Provider (Meta &amp; TikTok)</span>
                    </button>
                </div>
            </form>
        </div>
            </form>
        </div>

        {{-- 5. TAB 2: MERCHANTS OVERSIGHT --}}
        <div x-show="activeTab === 'merchants'" class="space-y-4">
            <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm overflow-hidden">
                <div class="p-5 sm:p-6 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                    <div>
                        <h2 class="text-[16px] font-bold text-black dark:text-white">Status Koneksi Media Sosial Tiap Toko</h2>
                        <p class="text-[12px] text-black/50 dark:text-white/50">Daftar toko yang telah mengintegrasikan Facebook Page atau Instagram Bisnis</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[13px]">
                        <thead class="bg-black/[0.02] dark:bg-white/[0.03] text-black/50 dark:text-white/50 text-[11px] font-bold uppercase tracking-wider">
                            <tr>
                                <th class="py-3 px-4">Nama Bisnis (Merchant)</th>
                                <th class="py-3 px-4">Platform</th>
                                <th class="py-3 px-4">Nama Akun / Page ID</th>
                                <th class="py-3 px-4">Status Token</th>
                                <th class="py-3 px-4">Terhubung Sejak</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/5 dark:divide-white/5">
                            @forelse($merchants as $m)
                                @foreach($m->socialMediaAccounts as $acc)
                                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                        <td class="py-3.5 px-4 font-bold text-black dark:text-white">
                                            {{ $m->name }}
                                        </td>
                                        <td class="py-3.5 px-4">
                                            @if($acc->platform === 'facebook')
                                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#1877F2]/10 text-[#1877F2]">Facebook Page</span>
                                            @elseif($acc->platform === 'instagram')
                                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#E1306C]/10 text-[#E1306C]">Instagram Bisnis</span>
                                            @else
                                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-black/10 dark:bg-white/10 text-black dark:text-white">Threads</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <div class="font-semibold text-black dark:text-white">{{ $acc->account_name }}</div>
                                            <div class="text-[11.5px] text-black/50 dark:text-white/50 font-mono">{{ $acc->username ?: $acc->account_id }}</div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold inline-flex items-center gap-1.5 bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                                <span>Permanent Token</span>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-black/55 dark:text-white/55 text-[12px]">
                                            {{ $acc->created_at->translatedFormat('d M Y H:i') }}
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-black/40 dark:text-white/40 text-[13px]">
                                        Belum ada merchant yang menghubungkan akun media sosial.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($merchants->hasPages())
                    <div class="p-4 border-t border-black/5 dark:border-white/10">
                        {{ $merchants->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- 6. TAB 3: APP REVIEW CHECKLIST & DEMO SCRIPT --}}
        <div x-show="activeTab === 'app_review'" class="space-y-5">
            <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 sm:p-7 space-y-5">
                <div class="flex items-center gap-3 pb-3 border-b border-black/5 dark:border-white/10">
                    <div class="w-9 h-9 rounded-[10px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center">
                        <i data-lucide="file-check-2" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-[16px] font-bold text-black dark:text-white">Panduan Pengajuan Meta App Review</h2>
                        <p class="text-[12px] text-black/50 dark:text-white/50">Ikuti panduan berikut saat mengajukan izin publik ke tim reviewer Meta Facebook</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-[12.5px]">
                    <div class="p-4.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2.5">
                        <span class="font-bold text-black dark:text-white block text-[13.5px]">1. Daftar Izin Resmi yang Diperlukan</span>
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

    </div>
@endsection

@push('scripts')
    <script>
        function adminSocialCenter() {
            return {
                activeTab: '{{ $tab }}',
                showAppSecret: false,
                showTikTokSecret: false,
                copied: null,
                copyText(text, key) {
                    navigator.clipboard.writeText(text);
                    this.copied = key;
                    setTimeout(() => this.copied = null, 2000);
                },
                init() {}
            };
        }
    </script>
@endpush
