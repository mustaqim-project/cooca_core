@extends('layouts.admin', [
    'title' => 'Sistem & Integrasi — Admin Console',
    'headerTitle' => 'Sistem & Integrasi',
    'headerSubtitle' => 'Kelola integrasi Google OAuth, pengaturan umum aplikasi, dan pintu masuk konfigurasi sistem lainnya'
])

@section('content')
<div class="max-w-4xl space-y-6" x-data="{ showSecret: false }">

    <!-- Instructions Banner -->
    <div class="rounded-[14px] px-4 py-3.5 bg-[#5856D6]/8 border border-[#5856D6]/20">
        <div class="flex items-center gap-2 text-[13px] font-semibold text-black dark:text-white">
            <i data-lucide="help-circle" class="w-4 h-4 text-[#5856D6] dark:text-[#5E5CE6]" stroke-width="1.5"></i>
            <span>Petunjuk Konfigurasi Google Cloud Console:</span>
        </div>
        <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed mt-2">
            1. Buka <a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="text-[#007AFF] dark:text-[#0A84FF] hover:underline font-medium">Google Cloud Console &gt; Credentials</a>.<br>
            2. Buat <strong>OAuth 2.0 Client IDs</strong> dengan Application Type: <em>Web application</em>.<br>
            3. Tambahkan <strong>Authorized redirect URIs</strong> dengan URL di bawah:
            <code class="px-2 py-0.5 rounded-[6px] bg-black/[0.06] dark:bg-white/[0.08] text-[#34C759] dark:text-[#30D158] text-[11px]">{{ $googleRedirectUri }}</code><br>
            4. Salin <strong>Client ID</strong> dan <strong>Client Secret</strong> ke formulir di bawah ini.
        </p>
    </div>

    <!-- Shortcuts -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <a href="{{ route('admin.billing-packages.index', 'subscription') }}" class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 hover:bg-black/[0.02] dark:hover:bg-white/[0.03] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-between gap-4 group">
            <div class="flex items-center gap-3">
                <i data-lucide="layers-3" class="w-5 h-5 text-[#34C759] dark:text-[#30D158]" stroke-width="1.5"></i>
                <div>
                    <div class="text-[14px] font-semibold text-black dark:text-white">Harga &amp; Paket Billing</div>
                    <div class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Harga langganan Core, top-up token, dan storage kini dikelola terpusat di CMS Paket &amp; Harga.</div>
                </div>
            </div>
            <i data-lucide="arrow-right" class="w-4 h-4 text-[#34C759] dark:text-[#30D158] shrink-0" stroke-width="1.5"></i>
        </a>
        <a href="{{ route('admin.smtp.index') }}" class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 hover:bg-black/[0.02] dark:hover:bg-white/[0.03] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-between gap-4 group">
            <div class="flex items-center gap-3">
                <i data-lucide="mail-cog" class="w-5 h-5 text-[#30B0C7] dark:text-[#40C8E0]" stroke-width="1.5"></i>
                <div>
                    <div class="text-[14px] font-semibold text-black dark:text-white">Pengaturan SMTP Email</div>
                    <div class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Konfigurasi server pengiriman email sistem verifikasi &amp; notifikasi billing.</div>
                </div>
            </div>
            <i data-lucide="arrow-right" class="w-4 h-4 text-[#30B0C7] dark:text-[#40C8E0] shrink-0" stroke-width="1.5"></i>
        </a>
    </div>

    <!-- Main Settings Form -->
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 sm:p-8">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
            @csrf
            <!-- Section 1: Google OAuth API -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 border-b border-black/5 dark:border-white/10 pb-3">
                    <i data-lucide="chrome" class="w-4 h-4 text-[#FF3B30] dark:text-[#FF453A]" stroke-width="1.5"></i>
                    <h3 class="text-[15px] font-semibold text-black dark:text-white">Integrasi Google OAuth (Single Sign-On)</h3>
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Google Client ID</label>
                    <input type="text" name="google_client_id" value="{{ old('google_client_id', $googleClientId) }}" placeholder="Contoh: 1234567890-abcdefg.apps.googleusercontent.com" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Client ID publik dari Google Cloud Console.</p>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70">Google Client Secret</label>
                        <button type="button" @click="showSecret = !showSecret" class="text-[11px] font-medium text-[#007AFF] dark:text-[#0A84FF] hover:underline"><span x-text="showSecret ? 'Sembunyikan' : 'Lihat'"></span></button>
                    </div>
                    <input :type="showSecret ? 'text' : 'password'" name="google_client_secret" value="{{ old('google_client_secret', $googleClientSecret) }}" placeholder="••••••••••••••••••••••••••••••" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Kunci rahasia API Google. Disimpan aman pada sistem.</p>
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Google Authorized Redirect URI</label>
                    <input type="text" name="google_redirect_uri" value="{{ old('google_redirect_uri', $googleRedirectUri) }}" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black/70 dark:text-white/70 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">URL Callback yang wajib didaftarkan pada Authorized redirect URIs di Google Cloud Console.</p>
                </div>

                <div class="pt-2">
                    <label class="flex items-center gap-3 cursor-pointer p-4 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-colors">
                        <input type="checkbox" name="allow_google_login" value="1" {{ $allowGoogleLogin == '1' ? 'checked' : '' }} class="w-4 h-4 rounded-[4px] border-black/20 text-[#007AFF] dark:text-[#0A84FF] focus:ring-[#007AFF]/30">
                        <div>
                            <div class="font-semibold text-black dark:text-white">Aktifkan Tombol "Login dengan Google"</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45">Tampilkan opsi masuk dengan akun Google pada halaman login &amp; registrasi pengguna.</div>
                        </div>
                    </label>
                </div>
            </div>
            <!-- Section 2: General Application Settings -->
            <div class="space-y-4 pt-4 border-t border-black/5 dark:border-white/10">
                <div class="flex items-center gap-2 border-b border-black/5 dark:border-white/10 pb-3">
                    <i data-lucide="sliders" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]" stroke-width="1.5"></i>
                    <h3 class="text-[15px] font-semibold text-black dark:text-white">Pengaturan Umum Aplikasi</h3>
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Nama Aplikasi (Platform Title)</label>
                    <input type="text" name="app_name" value="{{ old('app_name', $appName) }}" required class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-2 shadow-[0_1px_2px_rgba(0,122,255,0.25)]"><i data-lucide="save" class="w-4 h-4" stroke-width="1.5"></i><span>Simpan Pengaturan</span></button>
            </div>
        </form>
    </div>
</div>
@endsection