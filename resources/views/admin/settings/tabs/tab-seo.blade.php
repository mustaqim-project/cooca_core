    <!-- ========================================================================= -->
    <!-- TAB: SEO & METADATA LENGKAP CMS                                           -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'seo'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6"
        x-data="{
            metaTitle: '{{ addslashes($seoMetaTitle) }}',
            metaDesc: '{{ addslashes($seoMetaDescription) }}',
            ogTitle: '{{ addslashes($seoOgTitle) }}',
            ogDesc: '{{ addslashes($seoOgDescription) }}',
            ogImagePreview: '{{ $seoOgImage }}',
            resetOgImage: false,
            serpView: 'desktop',
            socialView: 'facebook',
            previewOgImage(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.ogImagePreview = e.target.result;
                        this.resetOgImage = false;
                    };
                    reader.readAsDataURL(file);
                }
            }
        }">

        <!-- Bento Banner: SEO CMS Overview -->
        <div class="rounded-[22px] p-5 sm:p-6 bg-gradient-to-br from-[#34C759]/10 via-[#007AFF]/5 to-transparent border border-[#34C759]/20 backdrop-blur-md shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[14px] bg-[#34C759]/15 text-[#34C759] dark:text-[#30D158] flex items-center justify-center shrink-0">
                        <i data-lucide="search" class="w-5 h-5" stroke-width="1.8"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-[15px] sm:text-[16px] font-bold text-black dark:text-white">SEO &amp; Pengindeksan Mesin Pencari (Google SERP)</h2>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25">
                                <i data-lucide="check-circle-2" class="w-3 h-3" stroke-width="2"></i>
                                <span>CMS SEO Lengkap</span>
                            </span>
                        </div>
                        <p class="text-[12px] text-black/55 dark:text-white/55 mt-0.5">Optimalkan visibilitas Cooca di Google Search, OpenGraph media sosial, dan pasang tag analitik tanpa sentuh kode.</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-medium text-black/50 dark:text-white/50 bg-black/[0.04] dark:bg-white/[0.06] px-3 py-1 rounded-[10px] border border-black/[0.04] dark:border-white/[0.06]">
                        Google SERP + Social Card Live Preview
                    </span>
                </div>
            </div>
        </div>

        <!-- Interactive Real-Time Preview Hub (Google SERP & Social Share Card) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- 1. Live Google Search Result (SERP) Preview -->
            <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-black/[0.04] dark:border-white/[0.06] pb-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                        </svg>
                        <h3 class="text-[14px] font-bold text-black dark:text-white">Simulasi Hasil Pencarian Google (SERP)</h3>
                    </div>

                    <!-- View Switcher -->
                    <div class="flex items-center gap-1 p-0.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06]">
                        <button type="button" @click="serpView = 'desktop'"
                            :class="serpView === 'desktop' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/50 dark:text-white/50'"
                            class="px-2 py-0.5 rounded-[6px] text-[11px] transition cursor-pointer">Desktop</button>
                        <button type="button" @click="serpView = 'mobile'"
                            :class="serpView === 'mobile' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/50 dark:text-white/50'"
                            class="px-2 py-0.5 rounded-[6px] text-[11px] transition cursor-pointer">Mobile</button>
                    </div>
                </div>

                <!-- Google Snippet Mockup Frame -->
                <div class="p-4 rounded-[16px] bg-[#FFFFFF] dark:bg-[#202124] border border-black/[0.08] dark:border-white/[0.08] shadow-xs space-y-1 font-sans"
                    :class="serpView === 'mobile' ? 'max-w-sm mx-auto' : 'w-full'">
                    <!-- Header: Favicon & Domain -->
                    <div class="flex items-center gap-2.5">
                        <div class="w-6 h-6 rounded-full bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center overflow-hidden shrink-0 border border-black/[0.06] dark:border-white/[0.06]">
                            <img src="{{ $siteFavicon }}" alt="Favicon" class="w-4 h-4 object-contain">
                        </div>
                        <div class="min-w-0">
                            <div class="text-[12px] font-medium text-[#202124] dark:text-[#dadce0] truncate">{{ $appName ?? 'Cooca UMKM' }}</div>
                            <div class="text-[11px] text-[#4d5156] dark:text-[#bdc1c6] truncate leading-tight">{{ $seoCanonicalUrl ?? $appUrl }}</div>
                        </div>
                    </div>

                    <!-- Title -->
                    <div class="pt-1">
                        <span class="text-[17px] sm:text-[18px] text-[#1a0dab] dark:text-[#8ab4f8] hover:underline cursor-pointer font-medium leading-snug line-clamp-2"
                            x-text="metaTitle || 'Cooca UMKM - Business Operating System & Omnichannel ERP'"></span>
                    </div>

                    <!-- Description Snippet -->
                    <p class="text-[13px] text-[#4d5156] dark:text-[#bdc1c6] leading-relaxed line-clamp-3 pt-0.5"
                        x-text="metaDesc || 'Software kasir POS, pembukuan otomatis, kalkulator bisnis, omnichannel media sosial & AI Assistant gratis selamanya untuk UMKM Indonesia.'"></p>
                </div>

                <!-- Character Advisor -->
                <div class="flex items-center justify-between text-[11px] text-black/50 dark:text-white/50 pt-1">
                    <div>
                        Title: <span :class="metaTitle.length > 60 ? 'text-[#FF3B30] font-bold' : 'text-[#34C759] font-bold'" x-text="metaTitle.length"></span>/60 Karakter
                    </div>
                    <div>
                        Deskripsi: <span :class="metaDesc.length > 160 ? 'text-[#FF3B30] font-bold' : 'text-[#34C759] font-bold'" x-text="metaDesc.length"></span>/160 Karakter
                    </div>
                </div>
            </div>

            <!-- 2. Live Social Media Share Card Preview -->
            <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-black/[0.04] dark:border-white/[0.06] pb-3">
                    <div class="flex items-center gap-2">
                        <i data-lucide="share" class="w-4 h-4 text-[#007AFF]"></i>
                        <h3 class="text-[14px] font-bold text-black dark:text-white">Simulasi Social Card (FB / WA / LinkedIn / X)</h3>
                    </div>

                    <div class="flex items-center gap-1 p-0.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06]">
                        <button type="button" @click="socialView = 'facebook'"
                            :class="socialView === 'facebook' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/50 dark:text-white/50'"
                            class="px-2 py-0.5 rounded-[6px] text-[11px] transition cursor-pointer">OpenGraph</button>
                        <button type="button" @click="socialView = 'twitter'"
                            :class="socialView === 'twitter' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/50 dark:text-white/50'"
                            class="px-2 py-0.5 rounded-[6px] text-[11px] transition cursor-pointer">X Card</button>
                    </div>
                </div>

                <!-- Social Card Mockup -->
                <div class="rounded-[16px] overflow-hidden border border-black/[0.1] dark:border-white/[0.1] bg-[#F0F2F5] dark:bg-[#18191A] shadow-xs">
                    <!-- Image Banner -->
                    <div class="w-full h-40 bg-black/5 dark:bg-white/5 relative overflow-hidden flex items-center justify-center">
                        <img :src="ogImagePreview" alt="OG Image Preview" class="w-full h-full object-cover">
                        <span class="absolute bottom-2 right-2 px-2 py-0.5 rounded-md bg-black/60 text-white text-[9px] font-mono">1200 x 630 px</span>
                    </div>

                    <!-- Card Body -->
                    <div class="p-3.5 space-y-1 bg-white dark:bg-[#242526]">
                        <div class="text-[11px] uppercase font-mono text-black/40 dark:text-white/40 font-semibold truncate">{{ parse_url($seoCanonicalUrl ?? $appUrl, PHP_URL_HOST) ?? 'cooca.id' }}</div>
                        <h4 class="text-[14px] font-bold text-black dark:text-white line-clamp-1"
                            x-text="ogTitle || metaTitle || 'Cooca UMKM - Business Operating System'"></h4>
                        <p class="text-[12px] text-black/60 dark:text-white/60 line-clamp-2 leading-relaxed"
                            x-text="ogDesc || metaDesc || 'Software kasir, pembukuan, kalkulator bisnis & AI Assistant gratis selamanya.'"></p>
                    </div>
                </div>

                <div class="text-[11px] text-black/50 dark:text-white/50 flex items-center gap-1.5">
                    <i data-lucide="info" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                    <span>Tampilan saat tautan <code>cooca.id</code> dibagikan ke WhatsApp, Facebook, LinkedIn, Discord &amp; X.</span>
                </div>
            </div>

        </div>

        <!-- Main Configuration Form -->
        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <input type="hidden" name="active_tab" value="seo">

            <!-- Section 1: Standard Meta Tags -->
            <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 shadow-sm space-y-5">
                <div class="flex items-center gap-3 pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                    <div class="w-10 h-10 rounded-[14px] bg-[#007AFF]/10 flex items-center justify-center text-[#007AFF] dark:text-[#0A84FF] shrink-0">
                        <i data-lucide="file-code" class="w-5 h-5" stroke-width="1.8"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">Metadata Dasar &amp; Search Engine Meta Tags</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Judul, deskripsi meta, kata kunci pencarian, dan direktif robot spider</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Meta Title -->
                    <div class="md:col-span-2">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-[13px] font-semibold text-black/75 dark:text-white/75">
                                Meta Title (Judul Halaman Utama) <span class="text-[#FF3B30]">*</span>
                            </label>
                            <span class="text-[11px] font-mono font-medium" :class="metaTitle.length > 60 ? 'text-[#FF3B30]' : 'text-black/40 dark:text-white/40'">
                                <span x-text="metaTitle.length"></span>/60 Karakter Ideal
                            </span>
                        </div>
                        <input type="text" name="seo_meta_title" x-model="metaTitle" required
                            class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Judul yang muncul di tab browser dan judul link biru Google Search.</p>
                    </div>

                    <!-- Meta Description -->
                    <div class="md:col-span-2">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-[13px] font-semibold text-black/75 dark:text-white/75">
                                Meta Description (Deskripsi Ringkas Google) <span class="text-[#FF3B30]">*</span>
                            </label>
                            <span class="text-[11px] font-mono font-medium" :class="metaDesc.length > 160 ? 'text-[#FF3B30]' : 'text-black/40 dark:text-white/40'">
                                <span x-text="metaDesc.length"></span>/160 Karakter Ideal
                            </span>
                        </div>
                        <textarea name="seo_meta_description" x-model="metaDesc" rows="3" required
                            class="w-full p-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition"></textarea>
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Ringkasan persuasif tentang Cooca yang dibaca oleh robot Google dan calon pengguna.</p>
                    </div>

                    <!-- Meta Keywords -->
                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                            Meta Keywords (Kata Kunci)
                        </label>
                        <input type="text" name="seo_meta_keywords" value="{{ old('seo_meta_keywords', $seoMetaKeywords) }}"
                            placeholder="Cooca UMKM, software kasir, aplikasi pembukuan, pos toko"
                            class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Pisahkan kata kunci dengan tanda koma.</p>
                    </div>

                    <!-- Author -->
                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                            Author / Penerbit Platform
                        </label>
                        <input type="text" name="seo_author" value="{{ old('seo_author', $seoAuthor) }}"
                            placeholder="Cooca Indonesia"
                            class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Nama perusahaan atau pemegang hak cipta portal.</p>
                    </div>

                    <!-- Robots Directive -->
                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                            Robots Directive (Indeks Crawler)
                        </label>
                        <select name="seo_robots"
                            class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <option value="index, follow" {{ $seoRobots === 'index, follow' ? 'selected' : '' }}>index, follow (Sangat Direkomendasikan)</option>
                            <option value="noindex, follow" {{ $seoRobots === 'noindex, follow' ? 'selected' : '' }}>noindex, follow (Crawl tanpa tampilkan)</option>
                            <option value="index, nofollow" {{ $seoRobots === 'index, nofollow' ? 'selected' : '' }}>index, nofollow (Index tanpa telusuri link)</option>
                            <option value="noindex, nofollow" {{ $seoRobots === 'noindex, nofollow' ? 'selected' : '' }}>noindex, nofollow (Blokir total crawler)</option>
                        </select>
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Petunjuk untuk Googlebot dan Bingbot mengenai status pengindeksan.</p>
                    </div>

                    <!-- Canonical URL -->
                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                            Canonical URL (Tautan Kanonikal Resmi)
                        </label>
                        <input type="url" name="seo_canonical_url" value="{{ old('seo_canonical_url', $seoCanonicalUrl) }}"
                            placeholder="https://cooca.id"
                            class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Mencegah duplikasi konten antar sub-domain atau HTTP/HTTPS.</p>
                    </div>
                </div>
            </div>

            <!-- Section 2: OpenGraph (OG) & Twitter Cards -->
            <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 shadow-sm space-y-5">
                <div class="flex items-center gap-3 pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                    <div class="w-10 h-10 rounded-[14px] bg-[#5856D6]/10 flex items-center justify-center text-[#5856D6] dark:text-[#5E5CE6] shrink-0">
                        <i data-lucide="image" class="w-5 h-5" stroke-width="1.8"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">OpenGraph (OG) &amp; Social Card Sharing</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Konfigurasi visual saat tautan website dibagikan ke WhatsApp, Telegram, Facebook, dan X</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- OG Title -->
                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                            OpenGraph Title
                        </label>
                        <input type="text" name="seo_og_title" x-model="ogTitle"
                            placeholder="Cooca UMKM - Business Operating System"
                            class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6]/50 transition">
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Kosongkan untuk otomatis menggunakan Meta Title.</p>
                    </div>

                    <!-- Twitter Card Type -->
                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                            Tipe Kartu Twitter / X (Twitter Card)
                        </label>
                        <select name="seo_twitter_card"
                            class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6]/50 transition">
                            <option value="summary_large_image" {{ $seoTwitterCard === 'summary_large_image' ? 'selected' : '' }}>summary_large_image (Gambar Besar Elegan - Direkomendasikan)</option>
                            <option value="summary" {{ $seoTwitterCard === 'summary' ? 'selected' : '' }}>summary (Gambar Ikon Kecil)</option>
                        </select>
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Format tampilan visual di platform X.</p>
                    </div>

                    <!-- OG Description -->
                    <div class="md:col-span-2">
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                            OpenGraph Description (Deskripsi Share Card)
                        </label>
                        <textarea name="seo_og_description" x-model="ogDesc" rows="2"
                            placeholder="Software kasir, pembukuan, kalkulator bisnis & AI Assistant gratis selamanya untuk UMKM Indonesia."
                            class="w-full p-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6]/50 transition"></textarea>
                    </div>

                    <!-- OG Image Upload -->
                    <div class="md:col-span-2 p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.05] dark:border-white/[0.06] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <img :src="ogImagePreview" alt="OG Thumbnail" class="w-20 h-12 object-cover rounded-[10px] border border-black/[0.1] shrink-0">
                            <div>
                                <div class="text-[13px] font-bold text-black dark:text-white">Banner Gambar OpenGraph (1200x630px)</div>
                                <div class="text-[11px] text-black/50 dark:text-white/50">Rasio 1.91:1 ideal untuk WhatsApp &amp; Facebook share link.</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 self-end sm:self-auto">
                            <label class="h-9 px-3.5 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12px] font-bold inline-flex items-center gap-1.5 cursor-pointer shadow-xs active:scale-95 transition">
                                <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                <span>Pilih Gambar Baru</span>
                                <input type="file" name="seo_og_image_file" accept="image/png,image/jpeg,image/webp" class="hidden" @change="previewOgImage($event)">
                            </label>

                            @if(!empty($seoOgImageRaw))
                            <label class="flex items-center gap-1.5 cursor-pointer text-[11px] text-[#FF3B30] font-semibold">
                                <input type="checkbox" name="reset_og_image" value="1" x-model="resetOgImage" class="w-3.5 h-3.5 rounded border-black/20 text-[#FF3B30] cursor-pointer">
                                <span>Reset Default</span>
                            </label>
                            @endif
                        </div>
                    </div>

                    <!-- Twitter Site Handle -->
                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                            Twitter Site Handle (@Akun)
                        </label>
                        <input type="text" name="seo_twitter_site" value="{{ old('seo_twitter_site', $seoTwitterSite) }}"
                            placeholder="@cooca_id"
                            class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6]/50 transition">
                    </div>
                </div>
            </div>

            <!-- Section 3: Webmaster Verification & Tracking Analytics -->
            <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 shadow-sm space-y-5">
                <div class="flex items-center gap-3 pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                    <div class="w-10 h-10 rounded-[14px] bg-[#34C759]/10 flex items-center justify-center text-[#34C759] dark:text-[#30D158] shrink-0">
                        <i data-lucide="shield-alert" class="w-5 h-5" stroke-width="1.8"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">Verifikasi Webmaster &amp; Kode Pelacak Analitik</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Google Search Console, Bing Webmaster, Google Analytics GA4, dan skrip kustom</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Google Search Console -->
                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                            Google Search Console Verification Tag
                        </label>
                        <input type="text" name="seo_google_verification" value="{{ old('seo_google_verification', $seoGoogleVerification) }}"
                            placeholder="Contoh: a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p"
                            class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]/50 transition">
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Masukkan nilai <code>content="..."</code> dari meta tag HTML verifikasi Google Search Console.</p>
                    </div>

                    <!-- Bing Webmaster -->
                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                            Bing Webmaster Verification Code
                        </label>
                        <input type="text" name="seo_bing_verification" value="{{ old('seo_bing_verification', $seoBingVerification) }}"
                            placeholder="Contoh: 1234567890ABCDEF1234567890ABCDEF"
                            class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]/50 transition">
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Kode verifikasi kepemilikan domain di Bing &amp; Yahoo Webmaster.</p>
                    </div>

                    <!-- Google Analytics GA4 / GTM -->
                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                            Google Analytics (GA4 ID / Tag Manager ID)
                        </label>
                        <input type="text" name="seo_google_analytics_id" value="{{ old('seo_google_analytics_id', $seoGoogleAnalyticsId) }}"
                            placeholder="Contoh: G-XXXXXXXXXX atau GTM-XXXXXXX"
                            class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]/50 transition">
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Sistem otomatis menyuntikkan gtag.js jika ID GA4 diisi.</p>
                    </div>

                    <!-- Custom Head Scripts -->
                    <div class="md:col-span-2">
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                            Skrip Tambahan &lt;head&gt; (JSON-LD Structured Data, Pixel, Custom Tag)
                        </label>
                        <textarea name="seo_custom_head_scripts" rows="4"
                            placeholder="<!-- Contoh: Skrip Schema.org JSON-LD atau Meta Pixel -->"
                            class="w-full p-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[12px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]/50 transition">{{ old('seo_custom_head_scripts', $seoCustomHeadScripts) }}</textarea>
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Disuntikkan secara aman ke dalam tag &lt;head&gt; halaman publik landing page.</p>
                    </div>
                </div>
            </div>

            <!-- Bottom Action Bar -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-5 rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md shadow-sm">
                <div class="flex items-center gap-2 text-[12px] text-black/55 dark:text-white/55">
                    <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759] shrink-0" stroke-width="2"></i>
                    <span>Pengaturan SEO dan meta tag akan langsung diterapkan pada seluruh halaman landing page publik.</span>
                </div>
                <button type="submit"
                    class="w-full sm:w-auto h-12 sm:h-11 px-6 rounded-[14px] text-[13px] sm:text-[14px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-md shadow-[#007AFF]/25 cursor-pointer">
                    <i data-lucide="save" class="w-4.5 h-4.5" stroke-width="2"></i>
                    <span>Simpan Pengaturan SEO</span>
                </button>
            </div>

        </form>
    </div>
