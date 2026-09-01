@extends('layouts.admin', [
    'title' => 'Pengaturan Google API & Sistem — Admin Console',
    'headerTitle' => 'Konfigurasi Google API & Sistem',
    'headerSubtitle' => 'Kelola kredensial OAuth Google untuk otentikasi Single Sign-On (SSO) pengguna'
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
                        <button type="button" @click="showSecret = !showSecret" class="text-[11px] text-indigo-400 hover:text-indigo-300 font-semibold flex items-center gap-1">
                            <span x-text="showSecret ? 'Sembunyikan' : 'Tampilkan Secret'"></span>
                        </button>
                    </div>
                    <input :type="showSecret ? 'text' : 'password'" name="google_client_secret" value="{{ old('google_client_secret', $googleClientSecret) }}" 
                           placeholder="••••••••••••••••••••••••••••••••"
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

            <!-- Section 2: SaaS Subscription Pricing Configuration -->
            <div class="space-y-4 pt-4 border-t border-slate-800">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <i data-lucide="badge-dollar-sign" class="w-4 h-4 text-emerald-400"></i>
                        <h3 class="text-sm font-bold text-white">Pengaturan Harga & Paket Langganan SaaS</h3>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        Cooca Core Tier
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1.5">Harga Paket Bulanan (Rp) <span class="text-rose-400">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500 font-bold font-mono text-xs">Rp</span>
                            <input type="number" name="subscription_price_monthly" value="{{ old('subscription_price_monthly', $subscriptionPriceMonthly) }}" required min="0" step="1000"
                                   placeholder="129000"
                                   class="w-full pl-11 pr-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono font-bold text-xs">
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">Biaya langganan paket Core siklus per bulan.</p>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1.5">Harga Paket Tahunan (Rp) <span class="text-rose-400">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500 font-bold font-mono text-xs">Rp</span>
                            <input type="number" name="subscription_price_annual" value="{{ old('subscription_price_annual', $subscriptionPriceAnnual) }}" required min="0" step="1000"
                                   placeholder="1290000"
                                   class="w-full pl-11 pr-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono font-bold text-xs">
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">Biaya langganan paket Core siklus 1 tahun penuh.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1.5">Kuota Token AI Bulanan (Tokens) <span class="text-rose-400">*</span></label>
                        <div class="relative">
                            <input type="number" name="subscription_ai_tokens_monthly" value="{{ old('subscription_ai_tokens_monthly', $subscriptionAiTokensMonthly) }}" required min="0" step="100000"
                                   placeholder="10000000"
                                   class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono font-bold text-xs">
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">Standar kuota AI Gemini per bulan untuk pelanggan Core (default 10.000.000 token).</p>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1.5">Badge Promo / Label Diskon Tahunan</label>
                        <input type="text" name="subscription_annual_discount_badge" value="{{ old('subscription_annual_discount_badge', $subscriptionAnnualDiscountBadge) }}"
                               placeholder="Contoh: Hemat 2 Bulan"
                               class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white text-xs">
                        <p class="text-[11px] text-slate-500 mt-1">Badge promo yang muncul pada opsi paket tahunan di halaman checkout.</p>
                    </div>
                </div>
            </div>

            <!-- Section 3: General Application Settings -->
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
