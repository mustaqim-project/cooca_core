    <!-- ========================================================================= -->
    <!-- TAB 3: MEDIA SOSIAL TERPADU (META & TIKTOK)                               -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'social'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">

        <!-- Bento Banner: Omnichannel Hub Info -->
        <div class="rounded-[22px] p-5 sm:p-6 bg-gradient-to-br from-[#1877F2]/10 via-[#007AFF]/5 to-black/5 dark:to-white/5 border border-[#1877F2]/20 backdrop-blur-md shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[14px] bg-[#1877F2]/15 text-[#1877F2] flex items-center justify-center shrink-0">
                        <i data-lucide="share-2" class="w-5 h-5" stroke-width="1.8"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-[15px] sm:text-[16px] font-bold text-black dark:text-white">Pusat Konfigurasi Media Sosial Terpadu (Meta &amp; TikTok)</h2>
                            @if(!empty($metaSocialAppId) && !empty($tiktokClientKey))
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25">
                                    <i data-lucide="check-circle-2" class="w-3 h-3" stroke-width="2"></i>
                                    <span>Semua Platform Aktif</span>
                                </span>
                            @elseif(!empty($metaSocialAppId) || !empty($tiktokClientKey))
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/15 text-[#007AFF] border border-[#007AFF]/25">
                                    <i data-lucide="info" class="w-3 h-3" stroke-width="2"></i>
                                    <span>Sebagian Dikonfigurasi</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A] border border-[#FF9500]/25">
                                    <i data-lucide="alert-circle" class="w-3 h-3" stroke-width="2"></i>
                                    <span>Belum Dikonfigurasi</span>
                                </span>
                            @endif
                        </div>
                        <p class="text-[12px] text-black/60 dark:text-white/60 mt-0.5">
                            Kredensial disimpan langsung ke basis data sistem (<code class="px-1 py-0.5 rounded bg-black/5 dark:bg-white/10 font-mono text-[11px]">system_settings</code>) dan dienkripsi aman. Tidak memerlukan konfigurasi berkas <code class="px-1 py-0.5 rounded bg-black/5 dark:bg-white/10 font-mono text-[11px]">.env</code>.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.social-media.index') }}"
                        class="h-9 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black dark:text-white text-[12px] font-semibold flex items-center gap-1.5 transition-colors cursor-pointer">
                        <i data-lucide="activity" class="w-4 h-4 text-[#007AFF]"></i>
                        <span>Cockpit Merchant</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- 2-Column Grid Layout: Forms & Security Guides -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Main Form Column (2 Cols) -->
            <div class="lg:col-span-2 space-y-6">
                <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="active_tab" value="social">

                    <!-- BENTO CARD 1: META GRAPH API (FACEBOOK, INSTAGRAM, THREADS) -->
                    <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 space-y-5 shadow-sm">
                        <div class="flex items-center justify-between border-b border-black/[0.04] dark:border-white/[0.06] pb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-[12px] bg-[#1877F2]/10 text-[#1877F2] flex items-center justify-center shrink-0">
                                    <i data-lucide="facebook" class="w-4.5 h-4.5"></i>
                                </div>
                                <div>
                                    <h3 class="text-[16px] font-bold text-black dark:text-white">Meta Platform (Facebook, Instagram &amp; Threads)</h3>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">Graph API v21.0 - Otorisasi Terpadu 1-Klik</p>
                                </div>
                            </div>
                            @if(!empty($metaSocialAppId))
                                <span class="text-[11px] font-bold text-[#34C759] flex items-center gap-1">
                                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                    <span>Aktif</span>
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Meta App ID -->
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    Meta App ID <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="text" name="social_media_app_id" value="{{ old('social_media_app_id', $metaSocialAppId ?? '') }}"
                                    placeholder="Contoh: 123456789012345"
                                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#1877F2]/50 transition">
                            </div>

                            <!-- Meta App Secret -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">
                                        Meta App Secret <span class="text-[#FF3B30]">*</span>
                                    </label>
                                    <button type="button" @click="showMetaSecret = !showMetaSecret" class="text-[11px] font-medium text-[#007AFF] hover:underline cursor-pointer">
                                        <span x-text="showMetaSecret ? 'Sembunyikan' : 'Tampilkan'"></span>
                                    </button>
                                </div>
                                <input :type="showMetaSecret ? 'text' : 'password'" name="social_media_app_secret" value="{{ old('social_media_app_secret', $metaSocialAppSecret ?? '') }}"
                                    placeholder="••••••••••••••••••••••••"
                                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#1877F2]/50 transition">
                            </div>

                            <!-- Webhook Verify Token -->
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    Webhook Verify Token
                                </label>
                                <input type="text" name="social_media_webhook_verify_token" value="{{ old('social_media_webhook_verify_token', $metaSocialWebhookToken ?? 'cooca_meta_social_webhook_token') }}"
                                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#1877F2]/50 transition">
                            </div>

                            <!-- Graph API Version -->
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    Meta Graph API Version
                                </label>
                                <input type="text" name="social_media_graph_version" value="{{ old('social_media_graph_version', $metaSocialGraphVersion ?? 'v21.0') }}"
                                    placeholder="v21.0"
                                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#1877F2]/50 transition">
                            </div>
                        </div>

                        <!-- Readonly Callback Webhook Box -->
                        <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.06] dark:border-white/[0.06] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2.5">
                            <div class="space-y-0.5">
                                <span class="text-[11px] font-semibold text-black/50 dark:text-white/50">Meta Webhook Callback URL:</span>
                                <div class="font-mono text-[12px] text-[#1877F2] select-all break-all">{{ $metaSocialWebhookUrl ?? url('/api/v1/social-media/meta/webhook') }}</div>
                            </div>
                            <button type="button" @click="copyToClipboard('{{ $metaSocialWebhookUrl ?? url('/api/v1/social-media/meta/webhook') }}', 'webhook')"
                                class="h-8 px-3 rounded-[9px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-[11px] font-bold text-black dark:text-white flex items-center gap-1.5 shrink-0 transition-colors cursor-pointer">
                                <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                <span x-text="copiedWebhook ? 'Tersalin!' : 'Salin URL'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- BENTO CARD 2: INSTAGRAM API (COOCA-IG) - OFFICIAL INSTAGRAM PLATFORM -->
                    <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 space-y-5 shadow-sm">
                        <div class="flex items-center justify-between border-b border-black/[0.04] dark:border-white/[0.06] pb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-[14px] bg-gradient-to-tr from-[#F58529] via-[#DD2A7B] to-[#8134AF] text-white flex items-center justify-center shrink-0 shadow-sm shadow-[#DD2A7B]/20">
                                    <i data-lucide="instagram" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-[16px] font-bold text-black dark:text-white">Instagram API (Cooca-IG)</h3>
                                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#E1306C]/10 text-[#E1306C] border border-[#E1306C]/20">Instagram Platform</span>
                                    </div>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">Posting konten, interaksi pesan &amp; moderasi komentar Instagram</p>
                                </div>
                            </div>
                            @if(!empty($instagramAccessToken) && $instagramStatus === 'active')
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                    <span>Terhubung Aktif</span>
                                </span>
                            @else
                                <span class="text-[11px] font-semibold text-[#FF9500] flex items-center gap-1">
                                    <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                    <span>Belum Terhubung</span>
                                </span>
                            @endif
                        </div>

                        <!-- Connected Account Snapshot Pill -->
                        @if(!empty($instagramUsername))
                            <div class="p-3.5 rounded-[16px] bg-gradient-to-r from-[#DD2A7B]/8 via-[#8134AF]/5 to-transparent border border-[#DD2A7B]/20 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    @if(!empty($instagramProfilePicture))
                                        <img src="{{ $instagramProfilePicture }}" alt="{{ $instagramUsername }}" class="w-11 h-11 rounded-full object-cover border-2 border-[#DD2A7B]/40 shadow-xs shrink-0">
                                    @else
                                        <div class="w-11 h-11 rounded-full bg-gradient-to-tr from-[#F58529] to-[#DD2A7B] text-white flex items-center justify-center font-bold text-[15px] shrink-0">
                                            <span>{{ strtoupper(substr($instagramUsername, 0, 1)) }}</span>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[14px] font-bold text-black dark:text-white tracking-tight">&#64;{{ $instagramUsername }}</span>
                                            <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                                {{ $instagramAccountType ?: 'MEDIA_CREATOR' }}
                                            </span>
                                        </div>
                                        <p class="text-[11.5px] text-black/55 dark:text-white/55 mt-0.5">
                                            <span class="font-bold text-black dark:text-white tabular-nums">{{ $instagramMediaCount }}</span> Postingan Media Aktif &bull; Setup ID: <code class="font-mono text-[10.5px]">{{ $instagramAccountId }}</code>
                                        </p>
                                    </div>
                                </div>
                                <button type="button" @click="testInstagramConfig()" :disabled="testingIg"
                                    class="h-9 px-3.5 rounded-[11px] text-[12px] font-bold text-[#E1306C] bg-[#E1306C]/10 hover:bg-[#E1306C]/20 active:scale-98 transition-all flex items-center gap-1.5 shrink-0 cursor-pointer disabled:opacity-50">
                                    <i data-lucide="loader-2" x-show="testingIg" class="w-3.5 h-3.5 animate-spin"></i>
                                    <i data-lucide="refresh-cw" x-show="!testingIg" class="w-3.5 h-3.5"></i>
                                    <span x-text="testingIg ? 'Memeriksa...' : 'Cek Status Instagram'"></span>
                                </button>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Nama Aplikasi Instagram -->
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    Nama Aplikasi Instagram
                                </label>
                                <input type="text" name="instagram_app_name" value="{{ old('instagram_app_name', $instagramAppName ?? 'Cooca-IG') }}"
                                    placeholder="Cooca-IG"
                                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#E1306C]/50 transition">
                            </div>

                            <!-- ID Aplikasi Instagram -->
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    ID Aplikasi Instagram
                                </label>
                                <input type="text" name="instagram_app_id" value="{{ old('instagram_app_id', $instagramAppId ?? '1813131243044390') }}"
                                    placeholder="1813131243044390"
                                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#E1306C]/50 transition">
                            </div>

                            <!-- Rahasia Aplikasi Instagram -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">
                                        Rahasia Aplikasi Instagram
                                    </label>
                                    <button type="button" @click="showIgSecret = !showIgSecret" class="text-[11px] font-medium text-[#007AFF] hover:underline cursor-pointer">
                                        <span x-text="showIgSecret ? 'Sembunyikan' : 'Tampilkan'"></span>
                                    </button>
                                </div>
                                <input :type="showIgSecret ? 'text' : 'password'" name="instagram_app_secret" value="{{ old('instagram_app_secret', $instagramAppSecret ?? '') }}"
                                    placeholder="{{ !empty($instagramAppSecret) ? '••••••••••••••••••••••••' : 'Masukkan rahasia aplikasi Instagram' }}"
                                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#E1306C]/50 transition">
                            </div>

                            <!-- ID Akun Instagram (Meta Setup) -->
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    ID Akun Instagram (Meta API Setup)
                                </label>
                                <input type="text" name="instagram_account_id" value="{{ old('instagram_account_id', $instagramAccountId ?? '17841439846162016') }}"
                                    placeholder="17841439846162016"
                                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#E1306C]/50 transition">
                            </div>

                            <!-- Username Instagram -->
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    Username Instagram
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 font-mono text-[13px]">&#64;</span>
                                    <input type="text" name="instagram_username" value="{{ old('instagram_username', $instagramUsername ?? 'cooca.indonesia') }}"
                                        placeholder="cooca.indonesia"
                                        class="w-full h-11 pl-8 pr-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#E1306C]/50 transition">
                                </div>
                            </div>

                            <!-- Token Akses Instagram -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">
                                        Token Akses Instagram
                                    </label>
                                    <button type="button" @click="showIgToken = !showIgToken" class="text-[11px] font-medium text-[#007AFF] hover:underline cursor-pointer">
                                        <span x-text="showIgToken ? 'Sembunyikan' : 'Tampilkan'"></span>
                                    </button>
                                </div>
                                <input :type="showIgToken ? 'text' : 'password'" name="instagram_access_token" value="{{ old('instagram_access_token', $instagramAccessToken ?? '') }}"
                                    placeholder="{{ !empty($instagramAccessToken) ? '••••••••••••••••••••••••' : 'IGAA...' }}"
                                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#E1306C]/50 transition">
                            </div>
                        </div>

                        <!-- Live Diagnostic Feedback Alert Box -->
                        <div x-show="testIgResult" x-transition class="p-3.5 rounded-[14px] border"
                            :class="testIgResult?.success ? 'bg-[#34C759]/10 border-[#34C759]/30 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF3B30]/10 border-[#FF3B30]/30 text-[#FF3B30]'">
                            <div class="flex items-start gap-2.5">
                                <i data-lucide="check-circle" x-show="testIgResult?.success" class="w-4 h-4 shrink-0 mt-0.5"></i>
                                <i data-lucide="alert-circle" x-show="!testIgResult?.success" class="w-4 h-4 shrink-0 mt-0.5"></i>
                                <div class="text-[12px] space-y-0.5">
                                    <p class="font-bold" x-text="testIgResult?.message"></p>
                                    <template x-if="testIgResult?.data">
                                        <p class="text-[11px] opacity-80">
                                            ID Akun: <span class="font-mono font-bold" x-text="testIgResult?.data?.id"></span> &bull; 
                                            Tipe: <span class="font-bold" x-text="testIgResult?.data?.account_type"></span> &bull; 
                                            Total Media: <span class="font-bold" x-text="testIgResult?.data?.media_count"></span>
                                        </p>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BENTO CARD 3: TIKTOK DEVELOPER PLATFORM (CONTENT POSTING API) -->
                    <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 space-y-5 shadow-sm">
                        <div class="flex items-center justify-between border-b border-black/[0.04] dark:border-white/[0.06] pb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-[12px] bg-black dark:bg-white text-white dark:text-black flex items-center justify-center shrink-0 text-[14px] font-black">
                                    <span>T</span>
                                </div>
                                <div>
                                    <h3 class="text-[16px] font-bold text-black dark:text-white">TikTok Developer Platform (Content Posting API)</h3>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">Open API v2 - Direct Post Video &amp; Photo Mode</p>
                                </div>
                            </div>
                            @if(!empty($tiktokClientKey))
                                <span class="text-[11px] font-bold text-[#34C759] flex items-center gap-1">
                                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                    <span>Aktif</span>
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- TikTok Client Key -->
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    TikTok Client Key <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="text" name="tiktok_client_key" value="{{ old('tiktok_client_key', $tiktokClientKey ?? '') }}"
                                    placeholder="Contoh: aw12345678abcdef"
                                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-black/40 dark:focus:ring-white/40 transition">
                            </div>

                            <!-- TikTok Client Secret -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">
                                        TikTok Client Secret <span class="text-[#FF3B30]">*</span>
                                    </label>
                                    <button type="button" @click="showTikTokSecret = !showTikTokSecret" class="text-[11px] font-medium text-[#007AFF] hover:underline cursor-pointer">
                                        <span x-text="showTikTokSecret ? 'Sembunyikan' : 'Tampilkan'"></span>
                                    </button>
                                </div>
                                <input :type="showTikTokSecret ? 'text' : 'password'" name="tiktok_client_secret" value="{{ old('tiktok_client_secret', $tiktokClientSecret ?? '') }}"
                                    placeholder="••••••••••••••••••••••••"
                                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-black/40 dark:focus:ring-white/40 transition">
                            </div>
                        </div>

                        <!-- Readonly TikTok OAuth Redirect URI Box -->
                        <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.06] dark:border-white/[0.06] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2.5">
                            <div class="space-y-0.5">
                                <span class="text-[11px] font-semibold text-black/50 dark:text-white/50">TikTok Redirect URI Callback:</span>
                                <div class="font-mono text-[12px] text-black dark:text-white select-all break-all">{{ $tiktokRedirectUri ?? route('social-media.tiktok.callback') }}</div>
                            </div>
                            <button type="button" @click="copyToClipboard('{{ $tiktokRedirectUri ?? route('social-media.tiktok.callback') }}', 'tiktok')"
                                class="h-8 px-3 rounded-[9px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-[11px] font-bold text-black dark:text-white flex items-center gap-1.5 shrink-0 transition-colors cursor-pointer">
                                <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                <span x-text="copiedTikTokRedirect ? 'Tersalin!' : 'Salin URI'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                        <button type="button" @click="testSocialMediaConfig()" :disabled="testingSocial"
                            class="w-full sm:w-auto h-11 px-4.5 rounded-[14px] text-[13px] font-bold text-black dark:text-white bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] border border-black/[0.06] dark:border-white/[0.08] transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                            <i data-lucide="loader-2" x-show="testingSocial" class="w-4 h-4 animate-spin text-[#007AFF]"></i>
                            <i data-lucide="zap" x-show="!testingSocial" class="w-4 h-4 text-[#FF9500]"></i>
                            <span x-text="testingSocial ? 'Menguji Kredensial...' : 'Uji Validitas Kredensial'"></span>
                        </button>

                        <button type="submit"
                            class="w-full sm:w-auto h-11 px-6 rounded-[14px] text-[13.5px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-sm shadow-[#007AFF]/25 cursor-pointer">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Simpan Pengaturan Media Sosial</span>
                        </button>
                    </div>

                    <!-- Dynamic Test Results Bento Box -->
                    <div x-show="testSocialResult" x-transition class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                        <div class="flex items-center gap-2 text-[13px] font-bold text-black dark:text-white">
                            <i data-lucide="activity" class="w-4 h-4 text-[#007AFF]"></i>
                            <span>Hasil Uji Koneksi Kredensial Platform</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-[12px]">
                            <!-- Meta Result -->
                            <div class="p-3 rounded-[12px] border"
                                :class="testSocialResult?.meta?.status === 'valid' ? 'bg-[#34C759]/10 border-[#34C759]/30 text-[#248A3D] dark:text-[#30D158]' : (testSocialResult?.meta?.status === 'unconfigured' ? 'bg-[#FF9500]/10 border-[#FF9500]/30 text-[#B25E00] dark:text-[#FF9F0A]' : 'bg-[#FF3B30]/10 border-[#FF3B30]/30 text-[#FF3B30]')">
                                <div class="font-bold flex items-center gap-1.5 mb-1">
                                    <i data-lucide="facebook" class="w-4 h-4"></i>
                                    <span>Meta Platform (FB/IG/Threads)</span>
                                </div>
                                <p class="text-[11.5px]" x-text="testSocialResult?.meta?.message"></p>
                            </div>

                            <!-- Instagram Result -->
                            <div class="p-3 rounded-[12px] border"
                                :class="testSocialResult?.instagram?.status === 'valid' ? 'bg-[#34C759]/10 border-[#34C759]/30 text-[#248A3D] dark:text-[#30D158]' : (testSocialResult?.instagram?.status === 'unconfigured' ? 'bg-[#FF9500]/10 border-[#FF9500]/30 text-[#B25E00] dark:text-[#FF9F0A]' : 'bg-[#FF3B30]/10 border-[#FF3B30]/30 text-[#FF3B30]')">
                                <div class="font-bold flex items-center gap-1.5 mb-1">
                                    <i data-lucide="instagram" class="w-4 h-4"></i>
                                    <span>Instagram (Cooca-IG)</span>
                                </div>
                                <p class="text-[11.5px]" x-text="testSocialResult?.instagram?.message"></p>
                            </div>

                            <!-- TikTok Result -->
                            <div class="p-3 rounded-[12px] border"
                                :class="testSocialResult?.tiktok?.status === 'valid' ? 'bg-[#34C759]/10 border-[#34C759]/30 text-[#248A3D] dark:text-[#30D158]' : (testSocialResult?.tiktok?.status === 'unconfigured' ? 'bg-[#FF9500]/10 border-[#FF9500]/30 text-[#B25E00] dark:text-[#FF9F0A]' : 'bg-[#FF3B30]/10 border-[#FF3B30]/30 text-[#FF3B30]')">
                                <div class="font-bold flex items-center gap-1.5 mb-1">
                                    <i data-lucide="video" class="w-4 h-4"></i>
                                    <span>TikTok Developer Platform</span>
                                </div>
                                <p class="text-[11.5px]" x-text="testSocialResult?.tiktok?.message"></p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Sidebar Column: Security & Developer Tips -->
            <div class="space-y-6">

                <!-- Security Bento Card -->
                <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 space-y-3.5 shadow-sm">
                    <div class="flex items-center gap-2.5 text-[#34C759]">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                        <h4 class="text-[14px] font-bold text-black dark:text-white">Penyimpanan Terenkripsi</h4>
                    </div>
                    <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                        Seluruh kunci rahasia (<code class="font-mono text-[11px]">app_secret</code> &amp; <code class="font-mono text-[11px]">client_secret</code>) dienkripsi secara simetris AES-256 di basis data COOCA dan ditandai sebagai data rahasia (*is_secret*).
                    </p>
                    <div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06] text-[11.5px] text-black/50 dark:text-white/50 space-y-1">
                        <div class="flex items-center gap-1.5 text-[#34C759]">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            <span>Zero .env Dependency</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-[#34C759]">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            <span>Auto-Refresh TikTok Token Aktif</span>
                        </div>
                    </div>
                </div>

                <!-- Meta Developer Guide Card -->
                <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-5 text-[12px] text-black/60 dark:text-white/60 space-y-2.5 shadow-sm">
                    <div class="flex items-center gap-2 text-[13px] font-bold text-black dark:text-white">
                        <i data-lucide="help-circle" class="w-4 h-4 text-[#1877F2]"></i>
                        <span>Cakupan Izin Meta (FB/IG/Threads):</span>
                    </div>
                    <p class="text-[11.5px] leading-relaxed">
                        Di portal Meta for Developers, pastikan aplikasi Anda telah disetujui untuk izin berikut:
                    </p>
                    <ul class="text-[11px] font-mono list-disc list-inside space-y-1 text-black/70 dark:text-white/70 pl-1">
                        <li>pages_show_list</li>
                        <li>pages_read_engagement</li>
                        <li>pages_manage_posts</li>
                        <li>instagram_basic</li>
                        <li>instagram_content_publish</li>
                        <li>threads_basic</li>
                        <li>threads_content_publish</li>
                    </ul>
                </div>

                <!-- TikTok Developer Guide Card -->
                <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-5 text-[12px] text-black/60 dark:text-white/60 space-y-2.5 shadow-sm">
                    <div class="flex items-center gap-2 text-[13px] font-bold text-black dark:text-white">
                        <i data-lucide="help-circle" class="w-4 h-4 text-[#007AFF]"></i>
                        <span>Cakupan Izin TikTok:</span>
                    </div>
                    <p class="text-[11.5px] leading-relaxed">
                        Di portal TikTok Developer, pastikan aplikasi Anda telah disetujui untuk izin berikut:
                    </p>
                    <ul class="text-[11px] font-mono list-disc list-inside space-y-1 text-black/70 dark:text-white/70 pl-1">
                        <li>user.info.basic</li>
                        <li>user.info.profile</li>
                        <li>user.info.stats</li>
                        <li>video.publish</li>
                        <li>video.upload</li>
                    </ul>
                </div>

                <!-- Fast Links Card -->
                <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-5 text-[12px] text-black/60 dark:text-white/60 space-y-2.5 shadow-sm">
                    <div class="flex items-center gap-2 text-[13px] font-bold text-black dark:text-white">
                        <i data-lucide="navigation" class="w-4 h-4 text-[#007AFF]"></i>
                        <span>Menu Operasional Media Sosial:</span>
                    </div>
                    <div class="space-y-2 pt-1">
                        <a href="{{ route('admin.social-media.index', ['tab' => 'merchants']) }}"
                            class="p-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] text-black dark:text-white text-[12px] font-medium flex items-center justify-between transition-colors">
                            <div class="flex items-center gap-2">
                                <i data-lucide="users" class="w-4 h-4 text-[#34C759]"></i>
                                <span>Pengawasan Merchant</span>
                            </div>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-black/40 dark:text-white/40"></i>
                        </a>
                        <a href="{{ route('admin.social-media.index', ['tab' => 'app_review']) }}"
                            class="p-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] text-black dark:text-white text-[12px] font-medium flex items-center justify-between transition-colors">
                            <div class="flex items-center gap-2">
                                <i data-lucide="shield-alert" class="w-4 h-4 text-[#FF9500]"></i>
                                <span>Panduan Meta App Review</span>
                            </div>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-black/40 dark:text-white/40"></i>
                        </a>
                    </div>
                </div>

            </div>

        </div>

    </div>


