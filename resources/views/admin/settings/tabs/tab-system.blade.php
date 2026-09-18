    <!-- ========================================================================= -->
    <!-- TAB 1: GOOGLE CLOUD OAUTH & SISTEM                                        -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'system'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">

        <!-- Bento Banner: Google Cloud Console Guide -->
        <div class="rounded-[22px] p-5 sm:p-6 bg-gradient-to-br from-[#5856D6]/10 via-[#5856D6]/5 to-transparent border border-[#5856D6]/20 backdrop-blur-md shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[14px] bg-[#5856D6]/15 text-[#5856D6] dark:text-[#5E5CE6] flex items-center justify-center shrink-0">
                        <i data-lucide="shield-alert" class="w-5 h-5" stroke-width="1.8"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-[15px] sm:text-[16px] font-bold text-black dark:text-white">Panduan Konfigurasi Google Cloud Console</h2>
                            @if(!empty($googleClientId))
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25">
                                    <i data-lucide="check-circle-2" class="w-3 h-3" stroke-width="2"></i>
                                    <span>Terkonfigurasi</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A] border border-[#FF9500]/25">
                                    <i data-lucide="alert-circle" class="w-3 h-3" stroke-width="2"></i>
                                    <span>Belum Dikonfigurasi</span>
                                </span>
                            @endif
                        </div>
                        <p class="text-[12px] text-black/55 dark:text-white/55 mt-0.5">Daftarkan kedua URL Callback berikut pada <strong>Authorized redirect URIs</strong> di Google Cloud Console.</p>
                    </div>
                </div>
                <a href="https://console.cloud.google.com/apis/credentials" target="_blank"
                    class="h-10 px-4 rounded-[12px] text-[12px] font-bold text-[#5856D6] dark:text-[#5E5CE6] bg-[#5856D6]/10 hover:bg-[#5856D6]/20 active:scale-[0.98] transition-all inline-flex items-center gap-1.5 self-start sm:self-auto shrink-0 shadow-sm cursor-pointer">
                    <span>Buka Google Console</span>
                    <i data-lucide="external-link" class="w-3.5 h-3.5" stroke-width="2"></i>
                </a>
            </div>

            <!-- 2 Bento URI Tiles with 1-click Copy -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 pt-1">
                <!-- Owner URI Tile -->
                <div class="p-4 rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-sm flex flex-col justify-between gap-3 shadow-xs">
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="inline-flex items-center gap-1.5 text-[12px] font-bold text-[#007AFF] dark:text-[#0A84FF]">
                                <i data-lucide="briefcase" class="w-3.5 h-3.5" stroke-width="1.8"></i>
                                <span>1. Callback Owner Bisnis &amp; Kasir</span>
                            </span>
                            <span class="text-[10px] font-mono font-medium px-2 py-0.5 rounded-[6px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]">guard: web</span>
                        </div>
                        <p class="text-[11px] text-black/50 dark:text-white/50 mt-1">Digunakan untuk login &amp; registrasi pemilik toko dan staf kasir POS.</p>
                    </div>
                    <div class="flex items-center justify-between gap-2 p-2 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.04] dark:border-white/[0.04]">
                        <code class="text-[11px] font-mono text-black/80 dark:text-white/80 truncate">{{ $googleRedirectUri }}</code>
                        <button type="button" @click="copyToClipboard('{{ $googleRedirectUri }}', 'owner')"
                            class="h-7 px-2.5 rounded-[7px] text-[11px] font-bold text-[#007AFF] dark:text-[#0A84FF] hover:bg-[#007AFF]/15 active:scale-95 transition-all inline-flex items-center gap-1 shrink-0 cursor-pointer">
                            <i data-lucide="copy" class="w-3 h-3" stroke-width="2" x-show="!copiedOwner"></i>
                            <i data-lucide="check" class="w-3 h-3 text-[#34C759]" stroke-width="2" x-show="copiedOwner"></i>
                            <span x-text="copiedOwner ? 'Tersalin!' : 'Salin'"></span>
                        </button>
                    </div>
                </div>

                <!-- Customer URI Tile -->
                <div class="p-4 rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-sm flex flex-col justify-between gap-3 shadow-xs">
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="inline-flex items-center gap-1.5 text-[12px] font-bold text-[#34C759] dark:text-[#30D158]">
                                <i data-lucide="shopping-bag" class="w-3.5 h-3.5" stroke-width="1.8"></i>
                                <span>2. Callback Pelanggan Toko &amp; Portal</span>
                            </span>
                            <span class="text-[10px] font-mono font-medium px-2 py-0.5 rounded-[6px] bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]">guard: customer</span>
                        </div>
                        <p class="text-[11px] text-black/50 dark:text-white/50 mt-1">Digunakan untuk checkout online, lacak pesanan, dan login customer.</p>
                    </div>
                    <div class="flex items-center justify-between gap-2 p-2 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.04] dark:border-white/[0.04]">
                        <code class="text-[11px] font-mono text-black/80 dark:text-white/80 truncate">{{ $googleCustomerRedirectUri }}</code>
                        <button type="button" @click="copyToClipboard('{{ $googleCustomerRedirectUri }}', 'customer')"
                            class="h-7 px-2.5 rounded-[7px] text-[11px] font-bold text-[#34C759] dark:text-[#30D158] hover:bg-[#34C759]/15 active:scale-95 transition-all inline-flex items-center gap-1 shrink-0 cursor-pointer">
                            <i data-lucide="copy" class="w-3 h-3" stroke-width="2" x-show="!copiedCustomer"></i>
                            <i data-lucide="check" class="w-3 h-3 text-[#34C759]" stroke-width="2" x-show="copiedCustomer"></i>
                            <span x-text="copiedCustomer ? 'Tersalin!' : 'Salin'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Configuration Form: Bento Cards -->
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
            @csrf

            <!-- Bento Card 1: Kredensial Bersama OAuth Google -->
            <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 space-y-5 shadow-sm">
                <div class="flex items-center gap-3 pb-4 border-b border-black/[0.04] dark:border-white/[0.06]">
                    <div class="w-10 h-10 rounded-[14px] bg-[#FF3B30]/10 flex items-center justify-center text-[#FF3B30] dark:text-[#FF453A] shrink-0">
                        <i data-lucide="chrome" class="w-5 h-5" stroke-width="1.8"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">Kredensial Bersama Google Cloud OAuth</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Kredensial ini digunakan bersama untuk otentikasi Owner Bisnis dan Pelanggan Toko</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                            Google Client ID
                        </label>
                        <input type="text" name="google_client_id" value="{{ old('google_client_id', $googleClientId) }}"
                            placeholder="Contoh: 1234567890-abcdefg.apps.googleusercontent.com"
                            class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] font-mono text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Client ID publik dari project Google Cloud Anda.</p>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75">
                                Google Client Secret
                            </label>
                            <button type="button" @click="showSecret = !showSecret"
                                class="text-[11px] font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center gap-1 cursor-pointer">
                                <i data-lucide="eye" class="w-3.5 h-3.5" x-show="!showSecret"></i>
                                <i data-lucide="eye-off" class="w-3.5 h-3.5" x-show="showSecret"></i>
                                <span x-text="showSecret ? 'Sembunyikan' : 'Tampilkan Secret'"></span>
                            </button>
                        </div>
                        <input :type="showSecret ? 'text' : 'password'" name="google_client_secret"
                            value="{{ old('google_client_secret', $googleClientSecret) }}"
                            placeholder="••••••••••••••••••••••••••••••••••••••••"
                            class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] font-mono text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Kunci rahasia API Google. Disimpan terenkripsi pada sistem.</p>
                    </div>
                </div>
            </div>

            <!-- Bento Card 2: Pengaturan Login Google Owner & Pelanggan (Dual Columns) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Column A: Login Google Owner Bisnis & Kasir -->
                <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 space-y-4 shadow-sm flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3 pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                            <div class="w-9 h-9 rounded-[12px] bg-[#007AFF]/10 flex items-center justify-center text-[#007AFF] dark:text-[#0A84FF] shrink-0">
                                <i data-lucide="user-check" class="w-4.5 h-4.5" stroke-width="1.8"></i>
                            </div>
                            <div>
                                <h4 class="text-[15px] font-bold text-black dark:text-white">Owner Bisnis &amp; Kasir</h4>
                                <span class="text-[11px] text-black/50 dark:text-white/50">Otorisasi Level Workspace &amp; POS</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                Authorized Redirect URI (Owner)
                            </label>
                            <input type="text" name="google_redirect_uri" value="{{ old('google_redirect_uri', $googleRedirectUri) }}"
                                class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[12px] font-mono text-black/80 dark:text-white/80 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Default: <code>/auth/google/callback</code></p>
                        </div>
                    </div>

                    <div class="pt-2">
                        <label class="flex items-start gap-3 cursor-pointer p-4 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-colors border border-black/[0.04] dark:border-white/[0.04]">
                            <input type="checkbox" name="allow_google_login" value="1"
                                {{ $allowGoogleLogin == '1' ? 'checked' : '' }}
                                class="w-5 h-5 rounded-[6px] border-black/20 text-[#007AFF] dark:text-[#0A84FF] focus:ring-[#007AFF]/30 mt-0.5 cursor-pointer">
                            <div>
                                <div class="text-[13px] font-bold text-black dark:text-white">Izinkan Login Google Owner</div>
                                <div class="text-[11px] text-black/50 dark:text-white/50 leading-relaxed mt-0.5">Tampilkan tombol masuk Google di halaman login dan pendaftaran bisnis.</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Column B: Login Google Customer / Pelanggan Toko -->
                <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 space-y-4 shadow-sm flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3 pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                            <div class="w-9 h-9 rounded-[12px] bg-[#34C759]/10 flex items-center justify-center text-[#34C759] dark:text-[#30D158] shrink-0">
                                <i data-lucide="users" class="w-4.5 h-4.5" stroke-width="1.8"></i>
                            </div>
                            <div>
                                <h4 class="text-[15px] font-bold text-black dark:text-white">Pelanggan Toko (Customer)</h4>
                                <span class="text-[11px] text-black/50 dark:text-white/50">Otorisasi Storefront &amp; Portal Order</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                Authorized Redirect URI (Customer)
                            </label>
                            <input type="text" name="google_customer_redirect_uri" value="{{ old('google_customer_redirect_uri', $googleCustomerRedirectUri) }}"
                                class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[12px] font-mono text-black/80 dark:text-white/80 focus:outline-none focus:ring-2 focus:ring-[#34C759]/50 transition">
                            <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Default: <code>/customer/auth/google/callback</code></p>
                        </div>
                    </div>

                    <div class="pt-2">
                        <label class="flex items-start gap-3 cursor-pointer p-4 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-colors border border-black/[0.04] dark:border-white/[0.04]">
                            <input type="checkbox" name="allow_customer_google_login" value="1"
                                {{ $allowCustomerGoogleLogin == '1' ? 'checked' : '' }}
                                class="w-5 h-5 rounded-[6px] border-black/20 text-[#34C759] dark:text-[#30D158] focus:ring-[#34C759]/30 mt-0.5 cursor-pointer">
                            <div>
                                <div class="text-[13px] font-bold text-black dark:text-white">Izinkan Login Google Pelanggan</div>
                                <div class="text-[11px] text-black/50 dark:text-white/50 leading-relaxed mt-0.5">Tampilkan opsi Google SSO saat pembeli memesan di toko online atau melacak pesanan.</div>
                            </div>
                        </label>
                    </div>
                </div>

            </div>

            <!-- Bento Card 3: Konfigurasi Umum Platform -->
            <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 space-y-4 shadow-sm">
                <div class="flex items-center gap-3 pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                    <div class="w-10 h-10 rounded-[14px] bg-[#007AFF]/10 flex items-center justify-center text-[#007AFF] dark:text-[#0A84FF] shrink-0">
                        <i data-lucide="sliders" class="w-5 h-5" stroke-width="1.8"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">Pengaturan Umum Platform</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Nama aplikasi dan identitas sistem yang tampil di portal publik &amp; admin</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                            Nama Aplikasi (Platform Title) <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span>
                        </label>
                        <input type="text" name="app_name" value="{{ old('app_name', $appName) }}" required
                            class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Nama platform bisnis yang tampil pada judul halaman dan portal.</p>
                    </div>

                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                            URL Dasar Platform Produksi (Canonical URL) <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span>
                        </label>
                        <input type="url" name="app_url" value="{{ old('app_url', $appUrl ?? 'https://cooca.id') }}" required
                            placeholder="https://cooca.id"
                            class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Domain utama produksi (https://cooca.id). Seluruh webhook &amp; callback diturunkan dari URL ini.</p>
                    </div>
                </div>
            </div>

            <!-- Bottom Action Bar (Touch-friendly 48-52px height) -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-5 rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md shadow-sm">
                <div class="flex items-center gap-2 text-[12px] text-black/55 dark:text-white/55">
                    <i data-lucide="info" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF] shrink-0" stroke-width="2"></i>
                    <span>Perubahan konfigurasi Google OAuth dan identitas platform akan langsung aktif seketika.</span>
                </div>
                <button type="submit"
                    class="w-full sm:w-auto h-12 sm:h-11 px-6 rounded-[14px] text-[13px] sm:text-[14px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-md shadow-[#007AFF]/25 cursor-pointer">
                    <i data-lucide="save" class="w-4.5 h-4.5" stroke-width="2"></i>
                    <span>Simpan</span>
                </button>
            </div>

        </form>
    </div>

