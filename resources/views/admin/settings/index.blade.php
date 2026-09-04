@extends('layouts.admin', [
    'title' => 'Sistem & Integrasi — Admin Console',
    'headerTitle' => 'Sistem & Integrasi',
    'headerSubtitle' => 'Kelola integrasi Google OAuth, pengaturan umum aplikasi, dan pintu masuk konfigurasi sistem lainnya'
])

@section('content')
<div class="max-w-4xl space-y-6" x-data="{ showSecret: false }">

    <!-- Instructions Banner -->
    <div class="glass-card p-5 rounded-2xl border-indigo-500/30 bg-gradient-to-r from-indigo-950/40 via-slate-900 to-purple-950/40 space-y-2">
        <div class="flex items-center gap-2 font-bold text-white text-sm">
            <i data-lucide="help-circle" class="w-4 h-4 text-indigo-400"></i>
            <span>Petunjuk Konfigurasi Google Cloud Console:</span>
        </div>
        <p class="text-xs text-slate-300 leading-relaxed">
            1. Buka <a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="text-indigo-400 hover:underline font-semibold">Google Cloud Console > Credentials</a>.<br>
            2. Buat <strong>OAuth 2.0 Client IDs</strong> dengan Application Type: <em>Web application</em>.<br>
            3. Tambahkan <strong>Authorized redirect URIs</strong> dengan URL di bawah:
            <code class="px-2 py-0.5 rounded bg-slate-950 text-emerald-400 font-mono text-[11px]">{{ $googleRedirectUri }}</code><br>
            4. Salin <strong>Client ID</strong> dan <strong>Client Secret</strong> ke formulir di bawah ini.
        </p>
    </div>

    <!-- Shortcuts to related config -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <a href="{{ route('admin.billing-packages.index', 'subscription') }}"
           class="glass-card p-4 rounded-2xl border border-emerald-500/30 bg-emerald-950/20 hover:bg-emerald-950/40 transition flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <i data-lucide="layers-3" class="w-5 h-5 text-emerald-400"></i>
                <div>
                    <div class="text-sm font-bold text-white">Harga & Paket Billing</div>
                    <div class="text-xs text-slate-400 mt-0.5">Harga langganan Core, top-up token, dan storage kini dikelola terpusat di CMS Paket & Harga.</div>
                </div>
            </div>
            <i data-lucide="arrow-right" class="w-4 h-4 text-emerald-300 shrink-0"></i>
        </a>

        <a href="{{ route('admin.smtp.index') }}" class="glass-card p-4 rounded-2xl border border-cyan-500/30 bg-cyan-950/20 hover:bg-cyan-950/40 transition flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <i data-lucide="mail-cog" class="w-5 h-5 text-cyan-400"></i>
                <div>
                    <div class="text-sm font-bold text-white">Pengaturan SMTP Email</div>
                    <div class="text-xs text-slate-400 mt-0.5">Konfigurasi server pengiriman email sistem verifikasi & notifikasi billing.</div>
                </div>
            </div>
            <i data-lucide="arrow-right" class="w-4 h-4 text-cyan-300 shrink-0"></i>
        </a>
    </div>

    <!-- Main Settings Form -->
    <div class="glass-card p-6 sm:p-8 rounded-3xl space-y-6">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6 text-xs">
            @csrf
<!-- Section 1: Google OAuth API -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-800 pb-3">
                    <i data-lucide="chrome" class="w-4 h-4 text-red-400"></i>
                    <h3 class="text-sm font-bold text-white">Integrasi Google OAuth (Single Sign-On)</h3>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5">Google Client ID</label>
                    <input type="text" name="google_client_id" value="{{ old('google_client_id', $googleClientId) }}"
                           placeholder="Contoh: 1234567890-abcdefg.apps.googleusercontent.com"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl text-white font-mono text-xs">
                    <p class="text-[11px] text-slate-500 mt-1">Client ID publik dari Google Cloud Console.</p>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block font-semibold text-slate-300">Google Client Secret</label>
                        <button type="button" @click="showSecret = !showSecret" class="text-[11px] text-indigo-400 hover:text-indigo-300 font-semibold">
                            <span x-text="showSecret ? 'Sembunyikan' : 'Lihat'"></span>
                        </button>
                    </div>
                    <input :type="showSecret ? 'text' : 'password'" name="google_client_secret" value="{{ old('google_client_secret', $googleClientSecret) }}"
                           placeholder="••••••••••••••••••••••••••••••"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl text-white font-mono text-xs">
                    <p class="text-[11px] text-slate-500 mt-1">Kunci rahasia API Google. Disimpan aman pada sistem.</p>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5">Google Authorized Redirect URI</label>
                    <input type="text" name="google_redirect_uri" value="{{ old('google_redirect_uri', $googleRedirectUri) }}"
                           class="w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-300 font-mono text-xs">
                    <p class="text-[11px] text-slate-500 mt-1">URL Callback yang wajib didaftarkan pada Authorized redirect URIs di Google Cloud Console.</p>
                </div>

                <div class="pt-2">
                    <label class="flex items-center gap-3 cursor-pointer p-4 rounded-2xl bg-slate-950 border border-slate-800 hover:border-slate-700 transition-colors">
                        <input type="checkbox" name="allow_google_login" value="1" {{ $allowGoogleLogin == '1' ? 'checked' : '' }}
                               class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500/20">
                        <div>
                            <div class="font-bold text-white">Aktifkan Tombol "Login dengan Google"</div>
                            <div class="text-[11px] text-slate-400">Tampilkan opsi masuk dengan akun Google pada halaman login & registrasi pengguna.</div>
                        </div>
                    </label>
                </div>
            </div>
<!-- Section 2: General Application Settings -->
            <div class="space-y-4 pt-4 border-t border-slate-800">
                <div class="flex items-center gap-2 border-b border-slate-800 pb-3">
                    <i data-lucide="sliders" class="w-4 h-4 text-indigo-400"></i>
                    <h3 class="text-sm font-bold text-white">Pengaturan Umum Aplikasi</h3>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5">Nama Aplikasi (Platform Title)</label>
                    <input type="text" name="app_name" value="{{ old('app_name', $appName) }}" required
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl text-white text-xs">
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 flex justify-end">
                <button type="submit"
                        class="px-6 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs shadow-lg shadow-indigo-500/25 transition-all flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Pengaturan</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection