    <!-- ========================================================================= -->
    <!-- TAB: BRANDING & IDENTITAS PLATFORM (LOGO DARK/LIGHT & FAVICON)            -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'branding'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6"
        x-data="{
            lightPreview: '{{ $siteLogoLight }}',
            darkPreview: '{{ $siteLogoDark }}',
            faviconPreview: '{{ $siteFavicon }}',
            resetLight: false,
            resetDark: false,
            resetFavicon: false,
            previewImage(event, target) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        if (target === 'light') { this.lightPreview = e.target.result; this.resetLight = false; }
                        if (target === 'dark') { this.darkPreview = e.target.result; this.resetDark = false; }
                        if (target === 'favicon') { this.faviconPreview = e.target.result; this.resetFavicon = false; }
                    };
                    reader.readAsDataURL(file);
                }
            }
        }">

        <!-- Bento Banner: Branding Overview -->
        <div class="rounded-[22px] p-5 sm:p-6 bg-gradient-to-br from-[#007AFF]/10 via-[#5856D6]/5 to-transparent border border-[#007AFF]/20 backdrop-blur-md shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[14px] bg-[#007AFF]/15 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                        <i data-lucide="palette" class="w-5 h-5" stroke-width="1.8"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-[15px] sm:text-[16px] font-bold text-black dark:text-white">Identitas Visual &amp; Logo Platform</h2>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] border border-[#007AFF]/20">
                                <i data-lucide="sparkles" class="w-3 h-3" stroke-width="2"></i>
                                <span>Dark &amp; Light Mode Native</span>
                            </span>
                        </div>
                        <p class="text-[12px] text-black/55 dark:text-white/55 mt-0.5">Atur logo resmi untuk mode terang, mode gelap, favicon browser, dan identitas brand Cooca.</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-medium text-black/50 dark:text-white/50 bg-black/[0.04] dark:bg-white/[0.06] px-3 py-1 rounded-[10px] border border-black/[0.04] dark:border-white/[0.06]">
                        Format: PNG, SVG, WebP (Maks 2MB)
                    </span>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <input type="hidden" name="active_tab" value="branding">

            <!-- 2-Column Bento Grid: Logo Light & Logo Dark -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Bento Card 1: Logo Light Mode -->
                <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 shadow-sm space-y-5 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between border-b border-black/[0.04] dark:border-white/[0.06] pb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-[10px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center">
                                    <i data-lucide="sun" class="w-4 h-4" stroke-width="2"></i>
                                </div>
                                <div>
                                    <h3 class="text-[15px] font-bold text-black dark:text-white">Logo Light Mode (Terang)</h3>
                                    <p class="text-[11px] text-black/50 dark:text-white/50">Digunakan pada latar putih atau terang</p>
                                </div>
                            </div>
                            @if(!empty($siteLogoLightRaw))
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">Kustom</span>
                            @else
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-black/[0.06] dark:bg-white/[0.08] text-black/50 dark:text-white/50">Default</span>
                            @endif
                        </div>

                        <!-- Live Preview Frame Light -->
                        <div>
                            <span class="block text-[11px] font-semibold text-black/50 dark:text-white/50 mb-1.5 uppercase tracking-wider">Pratinjau Langsung (Latar Terang):</span>
                            <div class="w-full h-32 rounded-[16px] bg-white border border-black/[0.1] shadow-inner p-4 flex items-center justify-center relative overflow-hidden group">
                                <div class="absolute top-2 left-2 text-[10px] font-mono text-black/30 font-bold uppercase tracking-wider">Background #FFFFFF</div>
                                <img :src="lightPreview" alt="Preview Logo Light" class="max-h-20 max-w-full object-contain transition-transform group-hover:scale-105 duration-200">
                            </div>
                        </div>

                        <!-- File Input -->
                        <div>
                            <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                                Unggah File Logo Baru
                            </label>
                            <input type="file" name="site_logo_light_file" accept="image/png,image/jpeg,image/svg+xml,image/webp"
                                @change="previewImage($event, 'light')"
                                class="w-full text-[13px] text-black/70 dark:text-white/70 file:mr-4 file:py-2 file:px-4 file:rounded-[10px] file:border-0 file:text-[12px] file:font-bold file:bg-[#007AFF]/10 file:text-[#007AFF] hover:file:bg-[#007AFF]/20 cursor-pointer transition">
                            <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Disarankan PNG transparan atau SVG dengan teks/ikon berwarna gelap/kontras.</p>
                        </div>
                    </div>

                    <!-- Reset to default option -->
                    @if(!empty($siteLogoLightRaw))
                    <div class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06]">
                        <label class="flex items-center gap-2 cursor-pointer text-[12px] text-[#FF3B30] dark:text-[#FF453A] font-semibold">
                            <input type="checkbox" name="reset_logo_light" value="1" x-model="resetLight"
                                class="w-4 h-4 rounded border-black/20 text-[#FF3B30] focus:ring-[#FF3B30]/30 cursor-pointer">
                            <span>Kembalikan ke Logo Default Sistem</span>
                        </label>
                    </div>
                    @endif
                </div>

                <!-- Bento Card 2: Logo Dark Mode -->
                <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 shadow-sm space-y-5 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between border-b border-black/[0.04] dark:border-white/[0.06] pb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-[10px] bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6] flex items-center justify-center">
                                    <i data-lucide="moon" class="w-4 h-4" stroke-width="2"></i>
                                </div>
                                <div>
                                    <h3 class="text-[15px] font-bold text-black dark:text-white">Logo Dark Mode (Gelap)</h3>
                                    <p class="text-[11px] text-black/50 dark:text-white/50">Digunakan pada sidebar admin &amp; mode gelap</p>
                                </div>
                            </div>
                            @if(!empty($siteLogoDarkRaw))
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">Kustom</span>
                            @else
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-black/[0.06] dark:bg-white/[0.08] text-black/50 dark:text-white/50">Default</span>
                            @endif
                        </div>

                        <!-- Live Preview Frame Dark -->
                        <div>
                            <span class="block text-[11px] font-semibold text-black/50 dark:text-white/50 mb-1.5 uppercase tracking-wider">Pratinjau Langsung (Latar Gelap):</span>
                            <div class="w-full h-32 rounded-[16px] bg-[#0B0F17] border border-white/[0.1] shadow-inner p-4 flex items-center justify-center relative overflow-hidden group">
                                <div class="absolute top-2 left-2 text-[10px] font-mono text-white/30 font-bold uppercase tracking-wider">Background #0B0F17</div>
                                <img :src="darkPreview" alt="Preview Logo Dark" class="max-h-20 max-w-full object-contain transition-transform group-hover:scale-105 duration-200">
                            </div>
                        </div>

                        <!-- File Input -->
                        <div>
                            <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                                Unggah File Logo Dark Baru
                            </label>
                            <input type="file" name="site_logo_dark_file" accept="image/png,image/jpeg,image/svg+xml,image/webp"
                                @change="previewImage($event, 'dark')"
                                class="w-full text-[13px] text-black/70 dark:text-white/70 file:mr-4 file:py-2 file:px-4 file:rounded-[10px] file:border-0 file:text-[12px] file:font-bold file:bg-[#007AFF]/10 file:text-[#007AFF] hover:file:bg-[#007AFF]/20 cursor-pointer transition">
                            <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Disarankan PNG transparan atau SVG berwarna putih/terang dengan kontras tinggi.</p>
                        </div>
                    </div>

                    <!-- Reset to default option -->
                    @if(!empty($siteLogoDarkRaw))
                    <div class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06]">
                        <label class="flex items-center gap-2 cursor-pointer text-[12px] text-[#FF3B30] dark:text-[#FF453A] font-semibold">
                            <input type="checkbox" name="reset_logo_dark" value="1" x-model="resetDark"
                                class="w-4 h-4 rounded border-black/20 text-[#FF3B30] focus:ring-[#FF3B30]/30 cursor-pointer">
                            <span>Kembalikan ke Logo Default Sistem</span>
                        </label>
                    </div>
                    @endif
                </div>

            </div>

            <!-- Bento Card 3: Favicon & Tagline Platform -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Favicon Box (1 Col) -->
                <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 shadow-sm space-y-4 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between border-b border-black/[0.04] dark:border-white/[0.06] pb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                                    <i data-lucide="globe" class="w-4 h-4" stroke-width="2"></i>
                                </div>
                                <div>
                                    <h3 class="text-[15px] font-bold text-black dark:text-white">Favicon Browser</h3>
                                    <p class="text-[11px] text-black/50 dark:text-white/50">Ikon tab browser (1:1)</p>
                                </div>
                            </div>
                            @if(!empty($siteFaviconRaw))
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">Kustom</span>
                            @else
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-black/[0.06] dark:bg-white/[0.08] text-black/50 dark:text-white/50">Default</span>
                            @endif
                        </div>

                        <!-- Browser Tab Mockup -->
                        <div>
                            <span class="block text-[11px] font-semibold text-black/50 dark:text-white/50 mb-1.5 uppercase tracking-wider">Simulasi Tab Browser:</span>
                            <div class="p-3 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.05] border border-black/[0.06] dark:border-white/[0.06]">
                                <div class="flex items-center gap-2 px-3 py-1.5 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.08] shadow-xs max-w-[200px]">
                                    <img :src="faviconPreview" alt="Favicon" class="w-4 h-4 rounded-sm object-contain shrink-0">
                                    <span class="text-[11px] font-medium text-black/80 dark:text-white/80 truncate">{{ $appName ?? 'Cooca UMKM' }}</span>
                                    <i data-lucide="x" class="w-3 h-3 text-black/30 dark:text-white/30 ml-auto shrink-0"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Favicon -->
                        <div>
                            <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                                Unggah File Favicon
                            </label>
                            <input type="file" name="site_favicon_file" accept=".ico,image/png,image/svg+xml"
                                @change="previewImage($event, 'favicon')"
                                class="w-full text-[12px] text-black/70 dark:text-white/70 file:mr-3 file:py-1.5 file:px-3 file:rounded-[8px] file:border-0 file:text-[11px] file:font-bold file:bg-[#34C759]/10 file:text-[#34C759] hover:file:bg-[#34C759]/20 cursor-pointer transition">
                            <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Format: ICO, PNG, atau SVG (Rasio 1:1, 32x32 atau 64x64px, maks 1MB).</p>
                        </div>
                    </div>

                    @if(!empty($siteFaviconRaw))
                    <div class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06]">
                        <label class="flex items-center gap-2 cursor-pointer text-[12px] text-[#FF3B30] dark:text-[#FF453A] font-semibold">
                            <input type="checkbox" name="reset_favicon" value="1" x-model="resetFavicon"
                                class="w-4 h-4 rounded border-black/20 text-[#FF3B30] focus:ring-[#FF3B30]/30 cursor-pointer">
                            <span>Reset ke Favicon Default</span>
                        </label>
                    </div>
                    @endif
                </div>

                <!-- Tagline & Brand Copy (2 Cols) -->
                <div class="lg:col-span-2 rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 shadow-sm space-y-4 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex items-center gap-2.5 border-b border-black/[0.04] dark:border-white/[0.06] pb-3">
                            <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                                <i data-lucide="type" class="w-4 h-4" stroke-width="2"></i>
                            </div>
                            <div>
                                <h3 class="text-[15px] font-bold text-black dark:text-white">Tagline &amp; Identitas Slogan</h3>
                                <p class="text-[11px] text-black/50 dark:text-white/50">Muncul di landing page, footer, meta title, dan dokumen resmi</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                                Tagline Platform Utama
                            </label>
                            <input type="text" name="site_tagline" value="{{ old('site_tagline', $siteTagline) }}"
                                placeholder="Contoh: Business Operating System & Omnichannel ERP"
                                class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Slogan ini disisipkan otomatis di samping nama aplikasi pada header publik dan preview OpenGraph.</p>
                        </div>

                        <div class="p-4 rounded-[16px] bg-[#007AFF]/5 border border-[#007AFF]/15 space-y-2">
                            <div class="flex items-center gap-2 text-[#007AFF] dark:text-[#0A84FF] text-[12px] font-bold">
                                <i data-lucide="info" class="w-4 h-4" stroke-width="2"></i>
                                <span>Petunjuk Implementasi Branding Otomatis</span>
                            </div>
                            <ul class="text-[11.5px] text-black/60 dark:text-white/60 space-y-1 list-disc list-inside leading-relaxed">
                                <li><strong>Mode Terang:</strong> Otomatis diaplikasikan pada landing page marketing, kuitansi PDF, email pemberitahuan, dan dokumen POS.</li>
                                <li><strong>Mode Gelap:</strong> Tampil elegan di sidebar Admin Console, bar navigasi mode malam, dan layar otentikasi login.</li>
                                <li>File yang diunggah otomatis tersimpan aman di server dan di-cache untuk kecepatan akses maksimal.</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Live Sample Header Render -->
                    <div class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.04] flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img :src="lightPreview" alt="Logo Header" class="h-6 object-contain">
                            <div class="h-4 w-px bg-black/15 dark:bg-white/15"></div>
                            <span class="text-[11.5px] font-medium text-black/60 dark:text-white/60" x-text="document.querySelector('[name=site_tagline]')?.value || '{{ $siteTagline }}'"></span>
                        </div>
                        <span class="text-[10px] uppercase font-mono font-bold text-black/30 dark:text-white/30">Header Preview</span>
                    </div>
                </div>

            </div>

            <!-- Bottom Action Bar -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-5 rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md shadow-sm">
                <div class="flex items-center gap-2 text-[12px] text-black/55 dark:text-white/55">
                    <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759] shrink-0" stroke-width="2"></i>
                    <span>Logo dan favicon baru akan langsung tampil di seluruh portal publik dan dashboard admin.</span>
                </div>
                <button type="submit"
                    class="w-full sm:w-auto h-12 sm:h-11 px-6 rounded-[14px] text-[13px] sm:text-[14px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-md shadow-[#007AFF]/25 cursor-pointer">
                    <i data-lucide="save" class="w-4.5 h-4.5" stroke-width="2"></i>
                    <span>Simpan Perubahan Branding</span>
                </button>
            </div>

        </form>
    </div>
