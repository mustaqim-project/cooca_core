@extends('layouts.admin', [
    'title' => 'Pengaturan Platform & Sistem - Admin Console',
    'headerTitle' => 'Pengaturan Platform & Integrasi',
    'headerSubtitle' => 'Pusat kendali integrasi Google Cloud OAuth, server email SMTP, dan parameter sistem Cooca',
])

@section('content')
<div class="max-w-5xl space-y-6" x-data="{
    activeTab: '{{ $defaultTab ?? request('tab', 'system') }}',
    showSecret: false,
    showPassword: false,
    copiedOwner: false,
    copiedCustomer: false,
    switchTab(tab) {
        this.activeTab = tab;
        const url = new URL(window.location);
        url.searchParams.set('tab', tab);
        window.history.replaceState({}, '', url);
    },
    copyToClipboard(text, type) {
        navigator.clipboard.writeText(text).then(() => {
            if (type === 'owner') {
                this.copiedOwner = true;
                setTimeout(() => this.copiedOwner = false, 2000);
            } else {
                this.copiedCustomer = true;
                setTimeout(() => this.copiedCustomer = false, 2000);
            }
        });
    },
    applyPreset(preset) {
        if (preset === 'gmail') {
            const m = document.querySelector('[name=mail_mailer]'); if (m) m.value = 'smtp';
            const h = document.querySelector('[name=mail_host]'); if (h) h.value = 'smtp.gmail.com';
            const p = document.querySelector('[name=mail_port]'); if (p) p.value = '587';
            const e = document.querySelector('[name=mail_encryption]'); if (e) e.value = 'tls';
        } else if (preset === 'mailtrap') {
            const m = document.querySelector('[name=mail_mailer]'); if (m) m.value = 'smtp';
            const h = document.querySelector('[name=mail_host]'); if (h) h.value = 'sandbox.smtp.mailtrap.io';
            const p = document.querySelector('[name=mail_port]'); if (p) p.value = '2525';
            const e = document.querySelector('[name=mail_encryption]'); if (e) e.value = 'tls';
        } else if (preset === 'cpanel') {
            const m = document.querySelector('[name=mail_mailer]'); if (m) m.value = 'smtp';
            const h = document.querySelector('[name=mail_host]'); if (h) h.value = 'mail.domainanda.com';
            const p = document.querySelector('[name=mail_port]'); if (p) p.value = '465';
            const e = document.querySelector('[name=mail_encryption]'); if (e) e.value = 'ssl';
        } else if (preset === 'log') {
            const m = document.querySelector('[name=mail_mailer]'); if (m) m.value = 'log';
        }
        if (window.AppAlert) {
            AppAlert.info('Preset ' + preset.toUpperCase() + ' berhasil diterapkan ke kolom SMTP.');
        }
    }
}">

    <!-- Apple Segmented Tab Bar (macOS Sonoma / iOS 18 System Settings Style) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-1.5 rounded-[20px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md">
        <div class="flex items-center gap-1.5 overflow-x-auto scrollbar-none w-full sm:w-auto p-0.5">
            <!-- Tab 1: Google OAuth & Sistem -->
            <button type="button" @click="switchTab('system')"
                :class="activeTab === 'system'
                    ? 'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-bold border border-black/[0.06] dark:border-white/[0.08]'
                    : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-semibold hover:bg-black/[0.03] dark:hover:bg-white/[0.04]'"
                class="h-11 sm:h-10 px-4 sm:px-4.5 rounded-[14px] text-[13px] sm:text-[13.5px] transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                <i data-lucide="sliders" class="w-4 h-4 shrink-0" :class="activeTab === 'system' ? 'text-[#007AFF]' : 'text-black/40 dark:text-white/40'"></i>
                <span>Google OAuth &amp; Sistem</span>
                @if(!empty($googleClientId))
                    <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0" title="OAuth Terkonfigurasi"></i>
                @else
                    <i data-lucide="alert-circle" class="w-3.5 h-3.5 text-[#FF9500] shrink-0" title="Belum Dikonfigurasi"></i>
                @endif
            </button>

            <!-- Tab 2: Server SMTP Email -->
            <button type="button" @click="switchTab('smtp')"
                :class="activeTab === 'smtp'
                    ? 'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-bold border border-black/[0.06] dark:border-white/[0.08]'
                    : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-semibold hover:bg-black/[0.03] dark:hover:bg-white/[0.04]'"
                class="h-11 sm:h-10 px-4 sm:px-4.5 rounded-[14px] text-[13px] sm:text-[13.5px] transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                <i data-lucide="mail-cog" class="w-4 h-4 shrink-0" :class="activeTab === 'smtp' ? 'text-[#007AFF]' : 'text-black/40 dark:text-white/40'"></i>
                <span>Pengaturan SMTP Email</span>
                <span class="text-[10px] font-bold px-1.5 py-0.2 rounded-md bg-black/[0.06] dark:bg-white/[0.08] text-black/60 dark:text-white/60 uppercase">
                    {{ $mailMailer ?? 'SMTP' }}
                </span>
            </button>
        </div>

        <!-- Tab 3: Shortcut to Pricing & Billing Catalog -->
        <a href="{{ route('admin.billing-packages.index', 'subscription') }}"
            class="h-10 px-4 rounded-[14px] text-[12.5px] font-bold text-[#34C759] dark:text-[#30D158] hover:bg-[#34C759]/10 active:scale-98 transition-all flex items-center gap-1.5 self-start sm:self-auto shrink-0 cursor-pointer">
            <i data-lucide="layers-3" class="w-4 h-4" stroke-width="1.8"></i>
            <span>Harga &amp; Paket Billing</span>
            <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 text-black/35 dark:text-white/35" stroke-width="2"></i>
        </a>
    </div>

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

                <div>
                    <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                        Nama Aplikasi (Platform Title) <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span>
                    </label>
                    <input type="text" name="app_name" value="{{ old('app_name', $appName) }}" required
                        class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
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

    <!-- ========================================================================= -->
    <!-- TAB 2: SERVER SMTP & EMAIL CONFIGURATION                                  -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'smtp'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">

        <!-- Bento Banner: Status & Quick Presets -->
        <div class="rounded-[22px] p-5 sm:p-6 bg-gradient-to-br from-[#007AFF]/10 via-[#5856D6]/5 to-transparent border border-[#007AFF]/20 backdrop-blur-md shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[14px] bg-[#007AFF]/15 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                        <i data-lucide="mail-check" class="w-5 h-5" stroke-width="1.8"></i>
                    </div>
                    <div>
                        <h2 class="text-[15px] sm:text-[16px] font-bold text-black dark:text-white">Konfigurasi Server SMTP Terpusat</h2>
                        <p class="text-[12px] text-black/55 dark:text-white/55 mt-0.5">Digunakan otomatis untuk verifikasi registrasi, pemulihan akun, dan kuitansi billing</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] self-start sm:self-auto shrink-0 border border-[#34C759]/25">
                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5" stroke-width="2"></i>
                    <span>Driver Aktif: {{ strtoupper($mailMailer ?? 'SMTP') }}</span>
                </span>
            </div>

            <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                Semua email keluar yang dipicu oleh platform dikirim menggunakan parameter SMTP berikut. Pengaturan disimpan langsung di database dan berlaku tanpa perlu merestart server ataupun mengedit file <code>.env</code>.
            </p>

            <!-- 1-Click Quick Presets Buttons -->
            <div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06]">
                <div class="flex items-center gap-2 mb-2">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-[#007AFF]" stroke-width="2"></i>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">Pilih Preset Server Cepat (1-Klik Isi):</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="applyPreset('gmail')"
                        class="h-8 px-3 rounded-[10px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] hover:bg-[#FF3B30]/10 hover:border-[#FF3B30]/30 active:scale-95 text-[12px] font-semibold text-black/80 dark:text-white/80 flex items-center gap-1.5 transition-all cursor-pointer">
                        <i data-lucide="mail" class="w-3.5 h-3.5 text-[#FF3B30]" stroke-width="2"></i>
                        <span>Gmail SMTP (Port 587 TLS)</span>
                    </button>
                    <button type="button" @click="applyPreset('mailtrap')"
                        class="h-8 px-3 rounded-[10px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] hover:bg-[#34C759]/10 hover:border-[#34C759]/30 active:scale-95 text-[12px] font-semibold text-black/80 dark:text-white/80 flex items-center gap-1.5 transition-all cursor-pointer">
                        <i data-lucide="inbox" class="w-3.5 h-3.5 text-[#34C759]" stroke-width="2"></i>
                        <span>Mailtrap Sandbox (Testing)</span>
                    </button>
                    <button type="button" @click="applyPreset('cpanel')"
                        class="h-8 px-3 rounded-[10px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] hover:bg-[#AF52DE]/10 hover:border-[#AF52DE]/30 active:scale-95 text-[12px] font-semibold text-black/80 dark:text-white/80 flex items-center gap-1.5 transition-all cursor-pointer">
                        <i data-lucide="server" class="w-3.5 h-3.5 text-[#AF52DE]" stroke-width="2"></i>
                        <span>cPanel / Webmail (Port 465 SSL)</span>
                    </button>
                    <button type="button" @click="applyPreset('log')"
                        class="h-8 px-3 rounded-[10px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] hover:bg-[#FF9500]/10 hover:border-[#FF9500]/30 active:scale-95 text-[12px] font-semibold text-black/80 dark:text-white/80 flex items-center gap-1.5 transition-all cursor-pointer">
                        <i data-lucide="file-text" class="w-3.5 h-3.5 text-[#FF9500]" stroke-width="2"></i>
                        <span>Driver Log (Simpan File)</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 2-Column Bento Grid: Main Form (2 Cols) & Test / Guardrails Card (1 Col) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Main Settings Form (2 Cols) -->
            <div class="lg:col-span-2 rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 shadow-sm space-y-6">
                <div class="flex items-center justify-between border-b border-black/[0.04] dark:border-white/[0.06] pb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center">
                            <i data-lucide="server" class="w-4.5 h-4.5" stroke-width="1.8"></i>
                        </div>
                        <div>
                            <h3 class="text-[15px] font-bold text-black dark:text-white">Parameter Server SMTP</h3>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Detail host, port, kredensial, dan enkripsi</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.smtp.update') }}" class="space-y-5">
                    @csrf

                    <!-- Driver Mailer -->
                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                            Driver Mailer <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span>
                        </label>
                        <select name="mail_mailer" required
                            class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <option value="smtp" {{ old('mail_mailer', $mailMailer ?? 'smtp') === 'smtp' ? 'selected' : '' }}>SMTP (Disarankan untuk Server Production)</option>
                            <option value="sendmail" {{ old('mail_mailer', $mailMailer ?? 'smtp') === 'sendmail' ? 'selected' : '' }}>Sendmail (Server Linux Lokal)</option>
                            <option value="log" {{ old('mail_mailer', $mailMailer ?? 'smtp') === 'log' ? 'selected' : '' }}>Log (Hanya simpan di storage/logs untuk debugging)</option>
                        </select>
                    </div>

                    <!-- Host & Port -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                                SMTP Host / Server <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span>
                            </label>
                            <input type="text" name="mail_host" value="{{ old('mail_host', $mailHost ?? '') }}" required
                                placeholder="Contoh: smtp.gmail.com atau mail.cooca.id"
                                class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] font-mono text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                        <div>
                            <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                                Port <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span>
                            </label>
                            <input type="number" name="mail_port" value="{{ old('mail_port', $mailPort ?? '587') }}" required
                                min="1" max="65535" placeholder="587"
                                class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] tabular-nums font-mono text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                    </div>

                    <!-- Username & Password -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                                SMTP Username
                            </label>
                            <input type="text" name="mail_username" value="{{ old('mail_username', $mailUsername ?? '') }}"
                                placeholder="Contoh: no-reply@cooca.id atau api"
                                class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75">
                                    SMTP Password
                                </label>
                                <button type="button" @click="showPassword = !showPassword"
                                    class="text-[11px] font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline cursor-pointer">
                                    <span x-text="showPassword ? 'Sembunyikan' : 'Lihat Sandi'"></span>
                                </button>
                            </div>
                            <input :type="showPassword ? 'text' : 'password'" name="mail_password"
                                value="{{ old('mail_password', $mailPassword ?? '') }}" placeholder="••••••••••••••••"
                                class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] font-mono text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                    </div>

                    <!-- Encryption -->
                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                            Tipe Enkripsi Keamanan (Security)
                        </label>
                        <select name="mail_encryption"
                            class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <option value="tls" {{ old('mail_encryption', $mailEncryption ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS (Port 587 - Standar Rekomendasi)</option>
                            <option value="ssl" {{ old('mail_encryption', $mailEncryption ?? 'tls') === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                            <option value="none" {{ old('mail_encryption', $mailEncryption ?? 'tls') === 'none' || empty($mailEncryption) ? 'selected' : '' }}>Tanpa Enkripsi (None / Port 25)</option>
                        </select>
                    </div>

                    <!-- Sender Header Details -->
                    <div class="pt-4 border-t border-black/[0.04] dark:border-white/[0.06] space-y-4">
                        <div class="flex items-center gap-2">
                            <i data-lucide="user-check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]" stroke-width="2"></i>
                            <h4 class="text-[13px] font-bold text-black dark:text-white">Identitas Pengirim Email (Sender Header)</h4>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    Alamat Email Pengirim (From Address) <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span>
                                </label>
                                <input type="email" name="mail_from_address"
                                    value="{{ old('mail_from_address', $mailFromAddress ?? 'no-reply@cooca.id') }}" required
                                    placeholder="no-reply@cooca.id"
                                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    Nama Pengirim (From Name) <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span>
                                </label>
                                <input type="text" name="mail_from_name"
                                    value="{{ old('mail_from_name', $mailFromName ?? 'Cooca Platform') }}" required
                                    placeholder="Cooca Platform"
                                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4 flex justify-end">
                        <button type="submit" aria-label="Simpan Pengaturan SMTP"
                            class="w-full sm:w-auto h-12 sm:h-10 px-6 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-md shadow-[#007AFF]/25 cursor-pointer">
                            <i data-lucide="save" class="w-4 h-4" stroke-width="2"></i>
                            <span>Simpan</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Sidebar Column: Connection Test & Security Guardrails -->
            <div class="space-y-6">

                <!-- Test Mailer Bento Card -->
                <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 space-y-4 shadow-sm">
                    <div class="flex items-center gap-3 border-b border-black/[0.04] dark:border-white/[0.06] pb-3">
                        <div class="w-9 h-9 rounded-[12px] bg-[#34C759]/15 text-[#34C759] dark:text-[#30D158] flex items-center justify-center shrink-0">
                            <i data-lucide="send" class="w-4.5 h-4.5" stroke-width="2"></i>
                        </div>
                        <div>
                            <h4 class="text-[15px] font-bold text-black dark:text-white">Uji Coba Koneksi SMTP</h4>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Kirim email simulasi instan</p>
                        </div>
                    </div>

                    <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                        Pastikan Anda telah menyimpan pengaturan server SMTP terlebih dahulu sebelum menguji koneksi.
                    </p>

                    <form method="POST" action="{{ route('admin.smtp.test') }}" class="space-y-3 pt-1">
                        @csrf
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                Alamat Email Penerima Tes
                            </label>
                            <input type="email" name="test_email" required
                                value="{{ auth('admin')->user()->email ?? '' }}" placeholder="emailanda@gmail.com"
                                class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#34C759]/50 transition">
                        </div>
                        <button type="submit" aria-label="Kirim Email Uji Coba"
                            class="w-full h-12 sm:h-10 rounded-[12px] text-[13px] font-bold text-white bg-[#34C759] hover:bg-[#2FB84C] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-sm shadow-[#34C759]/25 cursor-pointer">
                            <i data-lucide="zap" class="w-4 h-4" stroke-width="2"></i>
                            <span>Kirim</span>
                        </button>
                    </form>
                </div>

                <!-- Security Notice Bento Card -->
                <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-5 text-[12px] text-black/60 dark:text-white/60 space-y-2.5 shadow-sm">
                    <div class="flex items-center gap-2 text-[13px] font-bold text-black dark:text-white">
                        <i data-lucide="shield-alert" class="w-4 h-4 text-[#FF9500] dark:text-[#FF9F0A]" stroke-width="2"></i>
                        <span>Catatan Keamanan Gmail &amp; 2FA:</span>
                    </div>
                    <p class="text-[11px] leading-relaxed text-black/60 dark:text-white/60">
                        Jika menggunakan Gmail dengan 2-Factor Authentication (2FA), gunakan <strong>App Password (Sandi Aplikasi)</strong> 16 karakter yang dibuat di akun Google Anda, bukan kata sandi akun biasa.
                    </p>
                    <div class="pt-1 text-[11px] text-[#007AFF] dark:text-[#0A84FF] flex items-center gap-1">
                        <i data-lucide="check" class="w-3.5 h-3.5" stroke-width="2"></i>
                        <span>Kredensial disimpan aman &amp; terenkripsi</span>
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>
@endsection
