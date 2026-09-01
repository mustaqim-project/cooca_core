@extends('layouts.admin', [
    'title' => 'CMS SMTP & Server Email — Admin Console',
    'headerTitle' => 'CMS SMTP & Server Email',
    'headerSubtitle' => 'Kelola konfigurasi server pengiriman email sistem (verifikasi, reset password, notifikasi billing) tanpa perlu mengedit file .env'
])

@section('content')
<div class="max-w-5xl space-y-6" x-data="{ showPassword: false, showTestModal: false }">

    <!-- Information & Presets Guide Banner -->
    <div class="glass-card p-6 rounded-3xl border-cyan-500/30 bg-gradient-to-r from-cyan-950/40 via-slate-900 to-blue-950/40 space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5 font-bold text-white text-sm">
                <div class="w-8 h-8 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center border border-cyan-500/30">
                    <i data-lucide="mail-check" class="w-4 h-4"></i>
                </div>
                <span>Konfigurasi SMTP Email Terpusat</span>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                Driver Aktif: {{ strtoupper($mailMailer) }}
            </span>
        </div>
        <p class="text-xs text-slate-300 leading-relaxed">
            Semua email yang dikirimkan oleh Cooca Core (seperti <strong>Verifikasi Email Register</strong>, <strong>Reset Password User/Admin</strong>, dan <strong>Bukti Tagihan Langganan</strong>) akan otomatis dikirim menggunakan konfigurasi server SMTP di bawah ini. Pengaturan langsung tersimpan di database dan berlaku instan.
        </p>

        <!-- Quick Presets Info Chips -->
        <div class="pt-2 flex flex-wrap gap-2 text-[11px]">
            <span class="px-3 py-1 rounded-xl bg-slate-950/80 border border-slate-800 text-slate-300">
                <strong class="text-white">Gmail SMTP:</strong> Host: <code class="text-cyan-400">smtp.gmail.com</code> | Port: <code class="text-cyan-400">587 (TLS)</code> / <code class="text-cyan-400">465 (SSL)</code>
            </span>
            <span class="px-3 py-1 rounded-xl bg-slate-950/80 border border-slate-800 text-slate-300">
                <strong class="text-white">Mailtrap (Testing):</strong> Host: <code class="text-cyan-400">sandbox.smtp.mailtrap.io</code> | Port: <code class="text-cyan-400">2525</code>
            </span>
            <span class="px-3 py-1 rounded-xl bg-slate-950/80 border border-slate-800 text-slate-300">
                <strong class="text-white">cPanel / Webmail:</strong> Host: <code class="text-cyan-400">mail.domainanda.com</code> | Port: <code class="text-cyan-400">465 (SSL)</code>
            </span>
        </div>
    </div>

    <!-- 2-Column Layout: Settings Form & Test Mailer Card -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Column 1 & 2: Main SMTP Settings Form -->
        <div class="lg:col-span-2 glass-card p-6 sm:p-8 rounded-3xl space-y-6">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div class="flex items-center gap-2">
                    <i data-lucide="server" class="w-5 h-5 text-indigo-400"></i>
                    <h3 class="text-sm font-bold text-white">Parameter Server SMTP</h3>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.smtp.update') }}" class="space-y-5 text-xs">
                @csrf

                <!-- Mail Driver -->
                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5">Driver Mailer <span class="text-rose-400">*</span></label>
                    <select name="mail_mailer" required
                            class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl text-white text-xs">
                        <option value="smtp" {{ old('mail_mailer', $mailMailer) === 'smtp' ? 'selected' : '' }}>SMTP (Disarankan untuk Server Production)</option>
                        <option value="sendmail" {{ old('mail_mailer', $mailMailer) === 'sendmail' ? 'selected' : '' }}>Sendmail (Server Linux Lokal)</option>
                        <option value="log" {{ old('mail_mailer', $mailMailer) === 'log' ? 'selected' : '' }}>Log (Hanya simpan di storage/logs/laravel.log untuk debugging)</option>
                    </select>
                </div>

                <!-- Host & Port Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block font-semibold text-slate-300 mb-1.5">SMTP Host / Server <span class="text-rose-400">*</span></label>
                        <input type="text" name="mail_host" value="{{ old('mail_host', $mailHost) }}" required
                               placeholder="Contoh: smtp.gmail.com atau mail.cooca.id"
                               class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl text-white font-mono text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1.5">Port <span class="text-rose-400">*</span></label>
                        <input type="number" name="mail_port" value="{{ old('mail_port', $mailPort) }}" required min="1" max="65535"
                               placeholder="587"
                               class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl text-white font-mono text-xs">
                    </div>
                </div>

                <!-- Username & Password Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1.5">SMTP Username</label>
                        <input type="text" name="mail_username" value="{{ old('mail_username', $mailUsername) }}"
                               placeholder="Contoh: no-reply@cooca.id atau api"
                               class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl text-white font-mono text-xs">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block font-semibold text-slate-300">SMTP Password</label>
                            <button type="button" @click="showPassword = !showPassword" class="text-[11px] text-cyan-400 hover:text-cyan-300 font-semibold">
                                <span x-text="showPassword ? 'Sembunyikan' : 'Lihat'"></span>
                            </button>
                        </div>
                        <input :type="showPassword ? 'text' : 'password'" name="mail_password" value="{{ old('mail_password', $mailPassword) }}"
                               placeholder="••••••••••••••••"
                               class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl text-white font-mono text-xs">
                    </div>
                </div>

                <!-- Encryption Option -->
                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5">Tipe Enkripsi (Security)</label>
                    <select name="mail_encryption"
                            class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl text-white text-xs">
                        <option value="tls" {{ old('mail_encryption', $mailEncryption) === 'tls' ? 'selected' : '' }}>TLS (Port 587 - Standar)</option>
                        <option value="ssl" {{ old('mail_encryption', $mailEncryption) === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                        <option value="none" {{ old('mail_encryption', $mailEncryption) === 'none' || empty($mailEncryption) ? 'selected' : '' }}>Tanpa Enkripsi (None / Port 25)</option>
                    </select>
                </div>

                <div class="pt-4 border-t border-slate-800/80 space-y-4">
                    <div class="flex items-center gap-2">
                        <i data-lucide="user-check" class="w-4 h-4 text-emerald-400"></i>
                        <h4 class="text-xs font-bold text-white uppercase tracking-wider">Identitas Pengirim Email (Sender Header)</h4>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1.5">Alamat Email Pengirim (From Address) <span class="text-rose-400">*</span></label>
                            <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $mailFromAddress) }}" required
                                   placeholder="no-reply@cooca.id"
                                   class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl text-white text-xs">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-300 mb-1.5">Nama Pengirim (From Name) <span class="text-rose-400">*</span></label>
                            <input type="text" name="mail_from_name" value="{{ old('mail_from_name', $mailFromName) }}" required
                                   placeholder="Cooca Core Platform"
                                   class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl text-white text-xs">
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-4 flex justify-end">
                    <button type="submit"
                            class="px-6 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-cyan-600 hover:from-indigo-500 hover:to-cyan-500 text-white font-bold text-xs shadow-lg shadow-indigo-500/25 transition-all flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Simpan Pengaturan SMTP</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Column 3: Test Mailer Card -->
        <div class="space-y-6">
            <div class="glass-card p-6 rounded-3xl border border-slate-800 space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-800 pb-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                        <i data-lucide="send" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-white">Uji Coba Koneksi SMTP</h4>
                        <p class="text-[11px] text-slate-400">Kirim email simulasi ke alamat Anda</p>
                    </div>
                </div>

                <p class="text-xs text-slate-300 leading-relaxed">
                    Pastikan Anda telah menyimpan pengaturan server SMTP terlebih dahulu sebelum melakukan pengujian koneksi.
                </p>

                <form method="POST" action="{{ route('admin.smtp.test') }}" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1.5">Alamat Email Penerima Tes</label>
                        <input type="email" name="test_email" required value="{{ auth('admin')->user()->email ?? '' }}"
                               placeholder="emailanda@gmail.com"
                               class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white text-xs">
                    </div>

                    <button type="submit"
                            class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/20 transition-all">
                        <i data-lucide="zap" class="w-4 h-4"></i>
                        <span>Kirim Email Uji Coba</span>
                    </button>
                </form>
            </div>

            <!-- Security & SSL Notes -->
            <div class="glass-card p-5 rounded-3xl border border-slate-800/80 text-xs text-slate-400 space-y-2">
                <div class="flex items-center gap-1.5 text-slate-300 font-bold">
                    <i data-lucide="shield" class="w-4 h-4 text-amber-400"></i>
                    <span>Catatan Keamanan:</span>
                </div>
                <p class="text-[11px] leading-relaxed">
                    Jika menggunakan Gmail dengan 2-Factor Authentication (2FA), gunakan <strong>App Password (Sandi Aplikasi)</strong> 16 digit yang dibuat di akun Google Anda, bukan kata sandi akun biasa.
                </p>
            </div>
        </div>

    </div>
</div>
@endsection
