    <!-- ========================================================================= -->
    <!-- TAB 4: SERVER SMTP & EMAIL CONFIGURATION                                  -->
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
                            <span>Simpan Pengaturan SMTP</span>
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

