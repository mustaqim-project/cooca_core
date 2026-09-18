    <!-- ========================================================================= -->
    <!-- TAB: MEDIA SOSIAL RESMI & KOMUNITAS (PUBLIC CMS)                          -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'social_links'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">

        <!-- Bento Banner: Social CMS Overview -->
        <div class="rounded-[22px] p-5 sm:p-6 bg-gradient-to-br from-[#007AFF]/10 via-[#34C759]/5 to-transparent border border-[#007AFF]/20 backdrop-blur-md shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[14px] bg-[#007AFF]/15 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                        <i data-lucide="share-2" class="w-5 h-5" stroke-width="1.8"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-[15px] sm:text-[16px] font-bold text-black dark:text-white">Kanal Media Sosial &amp; Komunitas Resmi</h2>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25">
                                <i data-lucide="check-circle-2" class="w-3 h-3" stroke-width="2"></i>
                                <span>CMS Publik Dinamis</span>
                            </span>
                        </div>
                        <p class="text-[12px] text-black/55 dark:text-white/55 mt-0.5">Kelola tautan akun media sosial resmi Cooca yang tampil di footer portal, landing page, dan bio profil.</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-semibold text-black/60 dark:text-white/60 bg-black/[0.04] dark:bg-white/[0.06] px-3 py-1.5 rounded-[10px] border border-black/[0.05] dark:border-white/[0.06]">
                        8 Platform Terintegrasi
                    </span>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="active_tab" value="social_links">

            <!-- 8 Bento Cards Grid (2 Cols Desktop, 1 Col Mobile) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <!-- 1. Instagram Card -->
                <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-5 sm:p-6 shadow-sm space-y-4 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-[12px] bg-gradient-to-tr from-[#F58529] via-[#DD2A7B] to-[#8134AF] text-white flex items-center justify-center shadow-xs">
                                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-[15px] font-bold text-black dark:text-white">Instagram</h3>
                                    <p class="text-[11px] text-black/50 dark:text-white/50">Feed &amp; Reels Resmi</p>
                                </div>
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <span class="text-[11px] font-semibold text-black/60 dark:text-white/60">Aktifkan</span>
                                <input type="checkbox" name="social_instagram_active" value="1" {{ $socialInstagramActive ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-black/20 text-[#DD2A7B] focus:ring-[#DD2A7B]/30 cursor-pointer">
                            </label>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Handle / Username</label>
                                <input type="text" name="social_instagram_handle" value="{{ old('social_instagram_handle', $socialInstagramHandle) }}"
                                    placeholder="@cooca.indonesia"
                                    class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#DD2A7B]/50 transition">
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Tautan Lengkap URL</label>
                                <div class="flex items-center gap-2">
                                    <input type="url" name="social_instagram_url" value="{{ old('social_instagram_url', $socialInstagramUrl) }}"
                                        placeholder="https://instagram.com/cooca.indonesia"
                                        class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#DD2A7B]/50 transition">
                                    @if(!empty($socialInstagramUrl))
                                    <a href="{{ $socialInstagramUrl }}" target="_blank" class="h-10 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] flex items-center justify-center text-black/60 dark:text-white/60 shrink-0" title="Buka Profil">
                                        <i data-lucide="external-link" class="w-4 h-4"></i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Facebook Card -->
                <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-5 sm:p-6 shadow-sm space-y-4 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-[12px] bg-[#1877F2] text-white flex items-center justify-center shadow-xs">
                                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-[15px] font-bold text-black dark:text-white">Facebook</h3>
                                    <p class="text-[11px] text-black/50 dark:text-white/50">Halaman Penggemar &amp; Bisnis</p>
                                </div>
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <span class="text-[11px] font-semibold text-black/60 dark:text-white/60">Aktifkan</span>
                                <input type="checkbox" name="social_facebook_active" value="1" {{ $socialFacebookActive ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-black/20 text-[#1877F2] focus:ring-[#1877F2]/30 cursor-pointer">
                            </label>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Nama Halaman</label>
                                <input type="text" name="social_facebook_name" value="{{ old('social_facebook_name', $socialFacebookName) }}"
                                    placeholder="Cooca Indonesia"
                                    class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#1877F2]/50 transition">
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Tautan Halaman URL</label>
                                <div class="flex items-center gap-2">
                                    <input type="url" name="social_facebook_url" value="{{ old('social_facebook_url', $socialFacebookUrl) }}"
                                        placeholder="https://facebook.com/cooca.id"
                                        class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#1877F2]/50 transition">
                                    @if(!empty($socialFacebookUrl))
                                    <a href="{{ $socialFacebookUrl }}" target="_blank" class="h-10 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] flex items-center justify-center text-black/60 dark:text-white/60 shrink-0" title="Buka Halaman">
                                        <i data-lucide="external-link" class="w-4 h-4"></i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. TikTok Card -->
                <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-5 sm:p-6 shadow-sm space-y-4 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-[12px] bg-black text-white flex items-center justify-center shadow-xs">
                                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                        <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-[15px] font-bold text-black dark:text-white">TikTok</h3>
                                    <p class="text-[11px] text-black/50 dark:text-white/50">Konten Video Singkat UMKM</p>
                                </div>
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <span class="text-[11px] font-semibold text-black/60 dark:text-white/60">Aktifkan</span>
                                <input type="checkbox" name="social_tiktok_active" value="1" {{ $socialTiktokActive ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-black/20 text-black dark:text-white focus:ring-black/30 cursor-pointer">
                            </label>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Handle TikTok</label>
                                <input type="text" name="social_tiktok_handle" value="{{ old('social_tiktok_handle', $socialTiktokHandle) }}"
                                    placeholder="@cooca.id"
                                    class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-black/50 transition">
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Tautan Profil URL</label>
                                <div class="flex items-center gap-2">
                                    <input type="url" name="social_tiktok_url" value="{{ old('social_tiktok_url', $socialTiktokUrl) }}"
                                        placeholder="https://tiktok.com/@cooca.id"
                                        class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-black/50 transition">
                                    @if(!empty($socialTiktokUrl))
                                    <a href="{{ $socialTiktokUrl }}" target="_blank" class="h-10 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] flex items-center justify-center text-black/60 dark:text-white/60 shrink-0" title="Buka TikTok">
                                        <i data-lucide="external-link" class="w-4 h-4"></i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. YouTube Card -->
                <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-5 sm:p-6 shadow-sm space-y-4 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-[12px] bg-[#FF0000] text-white flex items-center justify-center shadow-xs">
                                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-[15px] font-bold text-black dark:text-white">YouTube</h3>
                                    <p class="text-[11px] text-black/50 dark:text-white/50">Tutorial, Edukasi &amp; Panduan</p>
                                </div>
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <span class="text-[11px] font-semibold text-black/60 dark:text-white/60">Aktifkan</span>
                                <input type="checkbox" name="social_youtube_active" value="1" {{ $socialYoutubeActive ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-black/20 text-[#FF0000] focus:ring-[#FF0000]/30 cursor-pointer">
                            </label>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Nama Kanal YouTube</label>
                                <input type="text" name="social_youtube_name" value="{{ old('social_youtube_name', $socialYoutubeName) }}"
                                    placeholder="Cooca UMKM Official"
                                    class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF0000]/50 transition">
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Tautan Kanal URL</label>
                                <div class="flex items-center gap-2">
                                    <input type="url" name="social_youtube_url" value="{{ old('social_youtube_url', $socialYoutubeUrl) }}"
                                        placeholder="https://youtube.com/@cooca_id"
                                        class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF0000]/50 transition">
                                    @if(!empty($socialYoutubeUrl))
                                    <a href="{{ $socialYoutubeUrl }}" target="_blank" class="h-10 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] flex items-center justify-center text-black/60 dark:text-white/60 shrink-0" title="Buka YouTube">
                                        <i data-lucide="external-link" class="w-4 h-4"></i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. X / Twitter Card -->
                <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-5 sm:p-6 shadow-sm space-y-4 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-[12px] bg-black text-white flex items-center justify-center shadow-xs">
                                    <svg class="w-4.5 h-4.5 fill-current" viewBox="0 0 24 24">
                                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-[15px] font-bold text-black dark:text-white">X (Twitter)</h3>
                                    <p class="text-[11px] text-black/50 dark:text-white/50">Update &amp; Berita Cepat</p>
                                </div>
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <span class="text-[11px] font-semibold text-black/60 dark:text-white/60">Aktifkan</span>
                                <input type="checkbox" name="social_twitter_active" value="1" {{ $socialTwitterActive ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-black/20 text-black dark:text-white focus:ring-black/30 cursor-pointer">
                            </label>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Handle / Username</label>
                                <input type="text" name="social_twitter_handle" value="{{ old('social_twitter_handle', $socialTwitterHandle) }}"
                                    placeholder="@cooca_id"
                                    class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-black/50 transition">
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Tautan URL</label>
                                <div class="flex items-center gap-2">
                                    <input type="url" name="social_twitter_url" value="{{ old('social_twitter_url', $socialTwitterUrl) }}"
                                        placeholder="https://x.com/cooca_id"
                                        class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-black/50 transition">
                                    @if(!empty($socialTwitterUrl))
                                    <a href="{{ $socialTwitterUrl }}" target="_blank" class="h-10 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] flex items-center justify-center text-black/60 dark:text-white/60 shrink-0" title="Buka X">
                                        <i data-lucide="external-link" class="w-4 h-4"></i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6. LinkedIn Card -->
                <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-5 sm:p-6 shadow-sm space-y-4 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-[12px] bg-[#0A66C2] text-white flex items-center justify-center shadow-xs">
                                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                        <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-[15px] font-bold text-black dark:text-white">LinkedIn</h3>
                                    <p class="text-[11px] text-black/50 dark:text-white/50">Profil Bisnis &amp; Karir Perusahaan</p>
                                </div>
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <span class="text-[11px] font-semibold text-black/60 dark:text-white/60">Aktifkan</span>
                                <input type="checkbox" name="social_linkedin_active" value="1" {{ $socialLinkedinActive ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-black/20 text-[#0A66C2] focus:ring-[#0A66C2]/30 cursor-pointer">
                            </label>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Nama Perusahaan</label>
                                <input type="text" name="social_linkedin_name" value="{{ old('social_linkedin_name', $socialLinkedinName) }}"
                                    placeholder="Cooca Indonesia"
                                    class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#0A66C2]/50 transition">
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Tautan Company Page URL</label>
                                <div class="flex items-center gap-2">
                                    <input type="url" name="social_linkedin_url" value="{{ old('social_linkedin_url', $socialLinkedinUrl) }}"
                                        placeholder="https://linkedin.com/company/cooca"
                                        class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#0A66C2]/50 transition">
                                    @if(!empty($socialLinkedinUrl))
                                    <a href="{{ $socialLinkedinUrl }}" target="_blank" class="h-10 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] flex items-center justify-center text-black/60 dark:text-white/60 shrink-0" title="Buka LinkedIn">
                                        <i data-lucide="external-link" class="w-4 h-4"></i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 7. WhatsApp CS & Channel Card -->
                <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-5 sm:p-6 shadow-sm space-y-4 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-[12px] bg-[#25D366] text-white flex items-center justify-center shadow-xs">
                                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-[15px] font-bold text-black dark:text-white">WhatsApp Kontak / CS</h3>
                                    <p class="text-[11px] text-black/50 dark:text-white/50">Layanan Pelanggan &amp; Helpdesk</p>
                                </div>
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <span class="text-[11px] font-semibold text-black/60 dark:text-white/60">Aktifkan</span>
                                <input type="checkbox" name="social_whatsapp_active" value="1" {{ $socialWhatsappActive ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-black/20 text-[#25D366] focus:ring-[#25D366]/30 cursor-pointer">
                            </label>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Nomor WhatsApp / Label</label>
                                <input type="text" name="social_whatsapp_number" value="{{ old('social_whatsapp_number', $socialWhatsappNumber) }}"
                                    placeholder="0823 3749 9577"
                                    class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#25D366]/50 transition">
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Tautan WhatsApp Direct URL</label>
                                <div class="flex items-center gap-2">
                                    <input type="url" name="social_whatsapp_url" value="{{ old('social_whatsapp_url', $socialWhatsappUrl) }}"
                                        placeholder="https://wa.me/6282337499577"
                                        class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#25D366]/50 transition">
                                    @if(!empty($socialWhatsappUrl))
                                    <a href="{{ $socialWhatsappUrl }}" target="_blank" class="h-10 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] flex items-center justify-center text-black/60 dark:text-white/60 shrink-0" title="Buka Chat">
                                        <i data-lucide="external-link" class="w-4 h-4"></i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 8. Telegram Card -->
                <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-5 sm:p-6 shadow-sm space-y-4 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-[12px] bg-[#229ED9] text-white flex items-center justify-center shadow-xs">
                                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                        <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.295-.6.295-.002 0-.003 0-.005 0l.213-3.054 5.56-5.022c.24-.213-.054-.334-.373-.121l-6.869 4.326-2.96-.924c-.643-.204-.657-.643.136-.953l11.57-4.461c.536-.196 1.006.128.832.942z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-[15px] font-bold text-black dark:text-white">Telegram</h3>
                                    <p class="text-[11px] text-black/50 dark:text-white/50">Komunitas &amp; Info Bot</p>
                                </div>
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <span class="text-[11px] font-semibold text-black/60 dark:text-white/60">Aktifkan</span>
                                <input type="checkbox" name="social_telegram_active" value="1" {{ $socialTelegramActive ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-black/20 text-[#229ED9] focus:ring-[#229ED9]/30 cursor-pointer">
                            </label>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Nama Grup / Channel</label>
                                <input type="text" name="social_telegram_name" value="{{ old('social_telegram_name', $socialTelegramName) }}"
                                    placeholder="Komunitas Cooca UMKM"
                                    class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#229ED9]/50 transition">
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Tautan Telegram URL</label>
                                <div class="flex items-center gap-2">
                                    <input type="url" name="social_telegram_url" value="{{ old('social_telegram_url', $socialTelegramUrl) }}"
                                        placeholder="https://t.me/cooca_id"
                                        class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#229ED9]/50 transition">
                                    @if(!empty($socialTelegramUrl))
                                    <a href="{{ $socialTelegramUrl }}" target="_blank" class="h-10 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] flex items-center justify-center text-black/60 dark:text-white/60 shrink-0" title="Buka Telegram">
                                        <i data-lucide="external-link" class="w-4 h-4"></i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Bottom Action Bar -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-5 rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md shadow-sm">
                <div class="flex items-center gap-2 text-[12px] text-black/55 dark:text-white/55">
                    <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759] shrink-0" stroke-width="2"></i>
                    <span>Kanal media sosial yang diaktifkan otomatis muncul pada footer portal dan landing page publik.</span>
                </div>
                <button type="submit"
                    class="w-full sm:w-auto h-12 sm:h-11 px-6 rounded-[14px] text-[13px] sm:text-[14px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-md shadow-[#007AFF]/25 cursor-pointer">
                    <i data-lucide="save" class="w-4.5 h-4.5" stroke-width="2"></i>
                    <span>Simpan Media Sosial</span>
                </button>
            </div>

        </form>
    </div>
