@extends('layouts.admin', [
    'title' => 'CMS SMTP & Server Email — Admin Console',
    'headerTitle' => 'CMS SMTP & Server Email',
    'headerSubtitle' => 'Kelola konfigurasi server pengiriman email sistem (verifikasi, reset password, notifikasi billing) tanpa perlu mengedit file .env'
])

@section('content')
<div class="max-w-5xl space-y-6" x-data="{ showPassword: false }">

    <!-- Information Banner -->
    <div class="rounded-[14px] px-4 py-3.5 bg-[#5856D6]/8 border border-[#5856D6]/20">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5 text-[13px] font-semibold text-black dark:text-white">
                <div class="w-8 h-8 rounded-[10px] bg-[#30B0C7]/15 text-[#30B0C7] dark:text-[#40C8E0] flex items-center justify-center"><i data-lucide="mail-check" class="w-4 h-4" stroke-width="1.5"></i></div>
                <span>Konfigurasi SMTP Email Terpusat</span>
            </div>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]"><span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>Driver Aktif: {{ strtoupper($mailMailer) }}</span>
        </div>
        <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed mt-2">
            Semua email yang dikirimkan oleh Cooca UMKM (seperti <strong>Verifikasi Email Register</strong>, <strong>Reset Password User/Admin</strong>, dan <strong>Bukti Tagihan Langganan</strong>) akan otomatis dikirim menggunakan konfigurasi server SMTP di bawah ini. Pengaturan langsung tersimpan di database dan berlaku instan.
        </p>
        <div class="pt-2 flex flex-wrap gap-2 text-[11px]">
            <span class="px-3 py-1 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60"><strong class="text-black dark:text-white">Gmail SMTP:</strong> Host <code class="text-[#30B0C7] dark:text-[#40C8E0]">smtp.gmail.com</code> | Port <code class="text-[#30B0C7] dark:text-[#40C8E0]">587 (TLS)</code></span>
            <span class="px-3 py-1 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60"><strong class="text-black dark:text-white">Mailtrap (Testing):</strong> Host <code class="text-[#30B0C7] dark:text-[#40C8E0]">sandbox.smtp.mailtrap.io</code> | Port <code class="text-[#30B0C7] dark:text-[#40C8E0]">2525</code></span>
            <span class="px-3 py-1 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60"><strong class="text-black dark:text-white">cPanel / Webmail:</strong> Host <code class="text-[#30B0C7] dark:text-[#40C8E0]">mail.domainanda.com</code> | Port <code class="text-[#30B0C7] dark:text-[#40C8E0]">465 (SSL)</code></span>
        </div>
    </div>

    <!-- 2-Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Main Settings Form -->
        <div class="lg:col-span-2 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 sm:p-8">
            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-4">
                <div class="flex items-center gap-2">
                    <i data-lucide="server" class="w-5 h-5 text-[#007AFF] dark:text-[#0A84FF]" stroke-width="1.5"></i>
                    <h3 class="text-[15px] font-semibold text-black dark:text-white">Parameter Server SMTP</h3>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.smtp.update') }}" class="space-y-5 mt-4">
                @csrf

                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Driver Mailer <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span></label>
                    <select name="mail_mailer" required class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        <option value="smtp" {{ old('mail_mailer', $mailMailer) === 'smtp' ? 'selected' : '' }}>SMTP (Disarankan untuk Server Production)</option>
                        <option value="sendmail" {{ old('mail_mailer', $mailMailer) === 'sendmail' ? 'selected' : '' }}>Sendmail (Server Linux Lokal)</option>
                        <option value="log" {{ old('mail_mailer', $mailMailer) === 'log' ? 'selected' : '' }}>Log (Hanya simpan di storage/logs/laravel.log untuk debugging)</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">SMTP Host / Server <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span></label>
                        <input type="text" name="mail_host" value="{{ old('mail_host', $mailHost) }}" required placeholder="Contoh: smtp.gmail.com atau mail.cooca.id" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Port <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span></label>
                        <input type="number" name="mail_port" value="{{ old('mail_port', $mailPort) }}" required min="1" max="65535" placeholder="587" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] tabular-nums text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">SMTP Username</label>
                        <input type="text" name="mail_username" value="{{ old('mail_username', $mailUsername) }}" placeholder="Contoh: no-reply@cooca.id atau api" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-[13px] font-medium text-black/70 dark:text-white/70">SMTP Password</label>
                            <button type="button" @click="showPassword = !showPassword" class="text-[11px] font-medium text-[#007AFF] dark:text-[#0A84FF] hover:underline"><span x-text="showPassword ? 'Sembunyikan' : 'Lihat'"></span></button>
                        </div>
                        <input :type="showPassword ? 'text' : 'password'" name="mail_password" value="{{ old('mail_password', $mailPassword) }}" placeholder="••••••••••••••••" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Tipe Enkripsi (Security)</label>
                    <select name="mail_encryption" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        <option value="tls" {{ old('mail_encryption', $mailEncryption) === 'tls' ? 'selected' : '' }}>TLS (Port 587 - Standar)</option>
                        <option value="ssl" {{ old('mail_encryption', $mailEncryption) === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                        <option value="none" {{ old('mail_encryption', $mailEncryption) === 'none' || empty($mailEncryption) ? 'selected' : '' }}>Tanpa Enkripsi (None / Port 25)</option>
                    </select>
                </div>

                <div class="pt-4 border-t border-black/5 dark:border-white/10 space-y-4">
                    <div class="flex items-center gap-2">
                        <i data-lucide="user-check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]" stroke-width="1.5"></i>
                        <h4 class="text-[12px] font-semibold text-black/60 dark:text-white/60">Identitas Pengirim Email (Sender Header)</h4>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Alamat Email Pengirim (From Address) <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span></label>
                            <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $mailFromAddress) }}" required placeholder="no-reply@cooca.id" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                        <div>
                            <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Nama Pengirim (From Name) <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span></label>
                            <input type="text" name="mail_from_name" value="{{ old('mail_from_name', $mailFromName) }}" required placeholder="Cooca UMKM Platform" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-2 shadow-[0_1px_2px_rgba(0,122,255,0.25)]"><i data-lucide="save" class="w-4 h-4" stroke-width="1.5"></i><span>Simpan Pengaturan SMTP</span></button>
                </div>
            </form>
        </div>

        <!-- Test Mailer Card -->
        <div class="space-y-6">
            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 space-y-4">
                <div class="flex items-center gap-2 border-b border-black/5 dark:border-white/10 pb-3">
                    <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/15 text-[#34C759] dark:text-[#30D158] flex items-center justify-center"><i data-lucide="send" class="w-4 h-4" stroke-width="1.5"></i></div>
                    <div>
                        <h4 class="text-[15px] font-semibold text-black dark:text-white">Uji Coba Koneksi SMTP</h4>
                        <p class="text-[11px] text-black/50 dark:text-white/50">Kirim email simulasi ke alamat Anda</p>
                    </div>
                </div>
                <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">Pastikan Anda telah menyimpan pengaturan server SMTP terlebih dahulu sebelum melakukan pengujian koneksi.</p>
                <form method="POST" action="{{ route('admin.smtp.test') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Alamat Email Penerima Tes</label>
                        <input type="email" name="test_email" required value="{{ auth('admin')->user()->email ?? '' }}" placeholder="emailanda@gmail.com" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <button type="submit" class="w-full h-10 rounded-[10px] text-[13px] font-semibold text-white bg-[#34C759] hover:bg-[#2FB84C] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-2"><i data-lucide="zap" class="w-4 h-4" stroke-width="1.5"></i><span>Kirim Email Uji Coba</span></button>
                </form>
            </div>

            <!-- Security Notes -->
            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 text-[12px] text-black/60 dark:text-white/60 space-y-2">
                <div class="flex items-center gap-1.5 text-[13px] font-semibold text-black/80 dark:text-white/80">
                    <i data-lucide="shield" class="w-4 h-4 text-[#FF9500] dark:text-[#FF9F0A]" stroke-width="1.5"></i>
                    <span>Catatan Keamanan:</span>
                </div>
                <p class="text-[11px] leading-relaxed">Jika menggunakan Gmail dengan 2-Factor Authentication (2FA), gunakan <strong>App Password (Sandi Aplikasi)</strong> 16 digit yang dibuat di akun Google Anda, bukan kata sandi akun biasa.</p>
            </div>
        </div>
    </div>
</div>
@endsection