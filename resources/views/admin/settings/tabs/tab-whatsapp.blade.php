    <!-- ========================================================================= -->
    <!-- TAB 3: META WHATSAPP CLOUD API (OFFICIAL TECH PROVIDER)                   -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'whatsapp'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">

        <!-- Bento Banner: WhatsApp Hub Info & Bot Status -->
        <div class="rounded-[22px] p-5 sm:p-6 bg-gradient-to-br from-[#25D366]/10 via-[#007AFF]/5 to-transparent border border-[#25D366]/20 backdrop-blur-md shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[14px] bg-[#25D366]/15 text-[#25D366] flex items-center justify-center shrink-0">
                        <i data-lucide="message-square" class="w-5 h-5" stroke-width="1.8"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-[15px] sm:text-[16px] font-bold text-black dark:text-white">Pusat Konfigurasi Meta WhatsApp Cloud API Resmi</h2>
                            @if(!empty($metaWaPhoneNumberId))
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25">
                                    <i data-lucide="check-circle-2" class="w-3 h-3" stroke-width="2"></i>
                                    <span>Cloud API {{ $metaWaGraphVersion ?? 'v25.0' }} Aktif</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A] border border-[#FF9500]/25">
                                    <i data-lucide="alert-circle" class="w-3 h-3" stroke-width="2"></i>
                                    <span>Belum Dikonfigurasi</span>
                                </span>
                            @endif
                        </div>
                        <p class="text-[12px] text-black/55 dark:text-white/55 mt-0.5">
                            Integrasi resmi Meta Tech Provider untuk pengiriman kode OTP akun, kuitansi digital pesanan meja, dan pengingat jatuh tempo tenant.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 self-start sm:self-auto">
                    <a href="https://developers.facebook.com/apps" target="_blank"
                        class="h-10 px-4 rounded-[12px] text-[12px] font-bold text-[#25D366] bg-[#25D366]/10 hover:bg-[#25D366]/20 active:scale-[0.98] transition-all inline-flex items-center gap-1.5 shrink-0 shadow-sm cursor-pointer">
                        <span>Meta Developer Console</span>
                        <i data-lucide="external-link" class="w-3.5 h-3.5" stroke-width="2"></i>
                    </a>
                </div>
            </div>

            <!-- Bot Live Status Pills Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 pt-1">
                <div class="p-3.5 rounded-[14px] bg-white/70 dark:bg-[#1C1C1E]/70 border border-black/[0.05] dark:border-white/[0.06]">
                    <span class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">Nama Akun Bisnis</span>
                    <span class="text-[13px] font-bold text-black dark:text-white truncate block mt-0.5">{{ $waBotStatus['verified_name'] ?? 'COOCA Platform' }}</span>
                </div>
                <div class="p-3.5 rounded-[14px] bg-white/70 dark:bg-[#1C1C1E]/70 border border-black/[0.05] dark:border-white/[0.06]">
                    <span class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">Nomor Bot WhatsApp</span>
                    <span class="text-[13px] font-mono font-bold text-black dark:text-white truncate block mt-0.5">{{ !empty($waBotStatus['phone']) ? '+' . $waBotStatus['phone'] : (!empty($metaWaPhoneNumberId) ? 'ID: ' . $metaWaPhoneNumberId : 'Belum Ada') }}</span>
                </div>
                <div class="p-3.5 rounded-[14px] bg-white/70 dark:bg-[#1C1C1E]/70 border border-black/[0.05] dark:border-white/[0.06]">
                    <span class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">Kualitas Nomor</span>
                    <span class="text-[13px] font-bold text-[#248A3D] dark:text-[#30D158] flex items-center gap-1.5 mt-0.5">
                        <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                        <span>{{ $waBotStatus['quality_rating'] ?? 'GREEN' }}</span>
                    </span>
                </div>
                <div class="p-3.5 rounded-[14px] bg-white/70 dark:bg-[#1C1C1E]/70 border border-black/[0.05] dark:border-white/[0.06]">
                    <span class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">Messaging Tier</span>
                    <span class="text-[13px] font-mono font-bold text-black dark:text-white truncate block mt-0.5">{{ $waBotStatus['messaging_limit_tier'] ?? 'TIER_1K' }}</span>
                </div>
            </div>

            <!-- Readonly Callback Webhook Box with 1-click copy -->
            <div class="p-4 rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xs">
                <div class="space-y-0.5">
                    <div class="flex items-center gap-1.5 text-[12px] font-bold text-[#25D366]">
                        <i data-lucide="webhook" class="w-3.5 h-3.5"></i>
                        <span>Webhook Callback URL (Inbound Messages &amp; Delivery Receipts)</span>
                    </div>
                    <code class="text-[11px] font-mono text-black/80 dark:text-white/80 select-all break-all">{{ $metaWaWebhookUrl ?? url('/api/v1/whatsapp/webhook') }}</code>
                    <p class="text-[11px] text-black/45 dark:text-white/45">Daftarkan URL ini beserta Webhook Verify Token pada WhatsApp &gt; Configuration di Meta Developer Portal.</p>
                </div>
                <button type="button" @click="copyToClipboard('{{ $metaWaWebhookUrl ?? url('/api/v1/whatsapp/webhook') }}', 'wa')"
                    class="h-8 px-3 rounded-[9px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-[11px] font-bold text-black dark:text-white flex items-center gap-1.5 shrink-0 transition-colors cursor-pointer">
                    <i data-lucide="copy" class="w-3.5 h-3.5" x-show="!copiedWaWebhook"></i>
                    <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759]" x-show="copiedWaWebhook"></i>
                    <span x-text="copiedWaWebhook ? 'Tersalin!' : 'Salin URL'"></span>
                </button>
            </div>
        </div>

        <!-- 2-Column Bento Grid: Main Form & Tech Provider Security -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Main Form Column (2 Cols) -->
            <div class="lg:col-span-2 space-y-6">
                <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="active_tab" value="whatsapp">

                    <!-- Bento Card: Kredensial Meta WhatsApp Cloud API -->
                    <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 space-y-5 shadow-sm">
                        <div class="flex items-center justify-between border-b border-black/[0.04] dark:border-white/[0.06] pb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-[12px] bg-[#25D366]/10 text-[#25D366] flex items-center justify-center shrink-0">
                                    <i data-lucide="message-circle" class="w-4.5 h-4.5"></i>
                                </div>
                                <div>
                                    <h3 class="text-[16px] font-bold text-black dark:text-white">Kredensial WhatsApp Business Platform</h3>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">Diperoleh dari Meta App Dashboard &gt; WhatsApp &gt; API Setup &amp; Embedded Signup</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Meta App ID -->
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    Meta App ID <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="text" name="meta_wa_app_id" value="{{ old('meta_wa_app_id', $metaWaAppId ?? '') }}"
                                    placeholder="Contoh: 1454871749894754" required
                                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#25D366]/50 transition">
                            </div>

                            <!-- Meta App Secret -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">
                                        Meta App Secret
                                    </label>
                                    <button type="button" @click="showWaSecret = !showWaSecret" class="text-[11px] font-medium text-[#007AFF] hover:underline cursor-pointer">
                                        <span x-text="showWaSecret ? 'Sembunyikan' : 'Lihat / Ubah'"></span>
                                    </button>
                                </div>
                                <input :type="showWaSecret ? 'text' : 'password'" name="meta_wa_app_secret"
                                    value="{{ old('meta_wa_app_secret', $metaWaAppSecret ?? '') }}"
                                    placeholder="{{ !empty($metaWaAppSecret) ? '••••••••••••••••••••••••••••••••' : 'App Secret' }}"
                                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#25D366]/50 transition">
                            </div>

                            <!-- Phone Number ID -->
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    Phone Number ID <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="text" name="meta_wa_phone_number_id" value="{{ old('meta_wa_phone_number_id', $metaWaPhoneNumberId ?? '') }}"
                                    placeholder="Contoh: 1311095538754578" required
                                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#25D366]/50 transition">
                            </div>

                            <!-- WABA ID -->
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    WhatsApp Business Account ID (WABA ID) <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="text" name="meta_wa_waba_id" value="{{ old('meta_wa_waba_id', $metaWaWabaId ?? '') }}"
                                    placeholder="Contoh: 4663536093891174" required
                                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#25D366]/50 transition">
                            </div>

                            <!-- Embedded Signup Config ID -->
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    Embedded Signup Config ID
                                </label>
                                <input type="text" name="meta_wa_config_id" value="{{ old('meta_wa_config_id', $metaWaConfigId ?? '') }}"
                                    placeholder="Contoh: 893472910482938"
                                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#25D366]/50 transition">
                            </div>

                            <!-- Webhook Verify Token -->
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    Webhook Verify Token <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="text" name="meta_wa_webhook_verify_token" value="{{ old('meta_wa_webhook_verify_token', $metaWaWebhookVerifyToken ?? 'cooca_meta_wa_webhook_secret') }}"
                                    required
                                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#25D366]/50 transition">
                            </div>

                            <!-- Meta Access Token (System User Permanent Token) -->
                            <div class="sm:col-span-2">
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">
                                        System User Access Token (Permanent / Never Expire) <span class="text-[#FF3B30]">*</span>
                                    </label>
                                    <button type="button" @click="showWaToken = !showWaToken" class="text-[11px] font-medium text-[#007AFF] hover:underline cursor-pointer">
                                        <span x-text="showWaToken ? 'Sembunyikan' : 'Lihat / Ubah Token'"></span>
                                    </button>
                                </div>
                                <input :type="showWaToken ? 'text' : 'password'" name="meta_wa_token"
                                    value="{{ old('meta_wa_token', $metaWaToken ?? '') }}"
                                    placeholder="{{ !empty($metaWaToken) ? '••••••••••••••••••••••••••••••••' : 'EAAU...' }}"
                                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#25D366]/50 transition">
                                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">
                                    Token akses System User dari Meta Business Manager. Disimpan terenkripsi AES-256 pada database.
                                </p>
                            </div>

                            <!-- OTP Template Name -->
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    Nama Template OTP Meta
                                </label>
                                <input type="text" name="meta_wa_otp_template" value="{{ old('meta_wa_otp_template', $metaWaOtpTemplate ?? 'cooca_otp') }}"
                                    placeholder="cooca_otp"
                                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#25D366]/50 transition">
                            </div>

                            <!-- Graph API Version -->
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    Graph API Version
                                </label>
                                <input type="text" name="meta_wa_graph_version" value="{{ old('meta_wa_graph_version', $metaWaGraphVersion ?? 'v25.0') }}"
                                    placeholder="v25.0"
                                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#25D366]/50 transition">
                            </div>

                            <!-- Graph API Base URL -->
                            <div class="sm:col-span-2">
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    Graph API Base URL
                                </label>
                                <input type="url" name="meta_wa_graph_url" value="{{ old('meta_wa_graph_url', $metaWaGraphUrl ?? 'https://graph.facebook.com') }}"
                                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#25D366]/50 transition">
                            </div>
                        </div>

                        <!-- Otomasi Saklar Toggles -->
                        <div class="pt-4 border-t border-black/[0.04] dark:border-white/[0.06] grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="flex items-center gap-3 p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.04] cursor-pointer hover:bg-black/[0.04] transition">
                                <input type="checkbox" name="wa_otp_active" value="1" {{ ($waOtpActive ?? true) ? 'checked' : '' }}
                                    class="w-4.5 h-4.5 rounded text-[#25D366] focus:ring-[#25D366] border-black/20">
                                <div>
                                    <span class="text-[12.5px] font-bold text-black dark:text-white block">OTP Otomatis Aktif</span>
                                    <span class="text-[11px] text-black/50 dark:text-white/50 block">Kirim kode OTP keamanan saat login / reset akun</span>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.04] cursor-pointer hover:bg-black/[0.04] transition">
                                <input type="checkbox" name="wa_blast_active" value="1" {{ ($waBlastActive ?? true) ? 'checked' : '' }}
                                    class="w-4.5 h-4.5 rounded text-[#25D366] focus:ring-[#25D366] border-black/20">
                                <div>
                                    <span class="text-[12.5px] font-bold text-black dark:text-white block">Broadcast Blast Aktif</span>
                                    <span class="text-[11px] text-black/50 dark:text-white/50 block">Izinkan pengiriman siaran massal resmi dari platform</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                        <button type="button" @click="testWhatsAppConfig()" :disabled="testingWa"
                            class="w-full sm:w-auto h-11 px-4.5 rounded-[14px] text-[13px] font-bold text-black dark:text-white bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] border border-black/[0.06] dark:border-white/[0.08] transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                            <i data-lucide="loader-2" x-show="testingWa" class="w-4 h-4 animate-spin text-[#25D366]"></i>
                            <i data-lucide="zap" x-show="!testingWa" class="w-4 h-4 text-[#25D366]"></i>
                            <span x-text="testingWa ? 'Menguji API Meta...' : 'Uji Validitas Meta Cloud API'"></span>
                        </button>

                        <button type="submit"
                            class="w-full sm:w-auto h-11 px-6 rounded-[14px] text-[13.5px] font-bold text-white bg-[#25D366] hover:bg-[#20BA5A] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-sm shadow-[#25D366]/25 cursor-pointer">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Simpan Pengaturan WhatsApp</span>
                        </button>
                    </div>

                    <!-- Dynamic Test Result Bento Box -->
                    <div x-show="testWaResult" x-transition class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                        <div class="flex items-center gap-2 text-[13px] font-bold text-black dark:text-white">
                            <i data-lucide="activity" class="w-4 h-4 text-[#25D366]"></i>
                            <span>Hasil Uji Meta WhatsApp Cloud API</span>
                        </div>
                        <div class="p-3.5 rounded-[12px] border text-[12px]"
                            :class="testWaResult?.success ? 'bg-[#34C759]/10 border-[#34C759]/30 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF3B30]/10 border-[#FF3B30]/30 text-[#FF3B30]'">
                            <div class="font-bold flex items-center gap-1.5 mb-1">
                                <i data-lucide="phone-call" class="w-4 h-4"></i>
                                <span x-text="testWaResult?.success ? 'Kredensial Meta Terverifikasi Valid' : 'Verifikasi Gagal'"></span>
                            </div>
                            <p class="text-[12px] leading-relaxed" x-text="testWaResult?.message"></p>
                        </div>
                    </div>
                </form>

                <!-- Live Test Message Sender Card -->
                <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 space-y-4 shadow-sm"
                    x-data="{
                        testPhone: '',
                        testMsg: 'Halo! Ini adalah pesan uji coba dari Meta WhatsApp Cloud API resmi Platform Cooca.',
                        sendingTest: false,
                        testSendResult: null,
                        sendTest() {
                            if (!this.testPhone) {
                                if (window.AppAlert) AppAlert.warning('Masukkan nomor WhatsApp tujuan.');
                                return;
                            }
                            this.sendingTest = true;
                            this.testSendResult = null;
                            fetch('{{ route('admin.whatsapp.test') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    phone: this.testPhone,
                                    message: this.testMsg
                                })
                            })
                            .then(r => r.json())
                            .then(data => {
                                this.sendingTest = false;
                                this.testSendResult = data;
                                if (data.success && window.AppAlert) {
                                    AppAlert.success(data.message || 'Pesan WhatsApp berhasil dikirim!');
                                } else if (!data.success && window.AppAlert) {
                                    AppAlert.error(data.message || 'Gagal mengirim pesan.');
                                }
                            })
                            .catch(err => {
                                this.sendingTest = false;
                                if (window.AppAlert) AppAlert.error('Galat jaringan: ' + (err.message || err));
                            });
                        }
                    }">
                    <div class="flex items-center gap-3 pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                        <div class="w-8 h-8 rounded-[10px] bg-[#25D366]/10 text-[#25D366] flex items-center justify-center shrink-0">
                            <i data-lucide="send" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-[14px] font-bold text-black dark:text-white">Uji Kirim Pesan WhatsApp Langsung</h4>
                            <p class="text-[11.5px] text-black/50 dark:text-white/50">Kirim pesan uji coba nyata ke nomor WhatsApp Anda untuk memverifikasi jalur gateway</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-1">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 mb-1">Nomor WhatsApp Tujuan</label>
                            <input type="text" x-model="testPhone" placeholder="6281234567890"
                                class="w-full h-10 px-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-[10px] text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#25D366]/40">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 mb-1">Pesan Uji Coba</label>
                            <div class="flex items-center gap-2">
                                <input type="text" x-model="testMsg"
                                    class="w-full h-10 px-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#25D366]/40">
                                <button type="button" @click="sendTest()" :disabled="sendingTest"
                                    class="h-10 px-4 rounded-[10px] bg-[#25D366] hover:bg-[#20BA5A] text-white text-[12.5px] font-bold inline-flex items-center gap-1.5 shrink-0 transition cursor-pointer disabled:opacity-50">
                                    <i data-lucide="loader-2" x-show="sendingTest" class="w-3.5 h-3.5 animate-spin"></i>
                                    <i data-lucide="send" x-show="!sendingTest" class="w-3.5 h-3.5"></i>
                                    <span x-text="sendingTest ? 'Mengirim...' : 'Kirim'"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div x-show="testSendResult" x-transition class="p-3 rounded-[10px] text-[12px]"
                        :class="testSendResult?.success ? 'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/30' : 'bg-[#FF3B30]/10 text-[#FF3B30] border border-[#FF3B30]/30'">
                        <span x-text="testSendResult?.message || (testSendResult?.success ? 'Pesan terkirim!' : 'Gagal mengirim')"></span>
                    </div>
                </div>
            </div>

            <!-- Sidebar Column: Official Tech Provider Guidelines & Fast Links -->
            <div class="space-y-6">

                <!-- Tech Provider Bento Card -->
                <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 space-y-3.5 shadow-sm">
                    <div class="flex items-center gap-2.5 text-[#25D366]">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                        <h4 class="text-[14px] font-bold text-black dark:text-white">Official Tech Provider</h4>
                    </div>
                    <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                        Menggunakan Meta WhatsApp Business Platform resmi (Cloud API v25.0) dengan rating kualitas terproteksi. 100% bebas dari risiko banned nomor seperti yang kerap terjadi pada emulator tidak resmi.
                    </p>
                    <div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06] text-[11.5px] text-black/60 dark:text-white/60 space-y-1.5">
                        <div class="flex items-center gap-1.5 text-[#34C759]">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            <span>Anti-Banned Official Infrastructure</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-[#34C759]">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            <span>Template Authentication: cooca_otp</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-[#34C759]">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            <span>Zero .env Dependency</span>
                        </div>
                    </div>
                </div>

                <!-- Fast Links Card -->
                <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-5 text-[12px] text-black/60 dark:text-white/60 space-y-2.5 shadow-sm">
                    <div class="flex items-center gap-2 text-[13px] font-bold text-black dark:text-white">
                        <i data-lucide="navigation" class="w-4 h-4 text-[#007AFF]"></i>
                        <span>Menu Operasional WhatsApp:</span>
                    </div>
                    <div class="space-y-2 pt-1">
                        <a href="{{ route('admin.whatsapp.index', ['tab' => 'merchants']) }}"
                            class="p-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] text-black dark:text-white text-[12px] font-medium flex items-center justify-between transition-colors">
                            <div class="flex items-center gap-2">
                                <i data-lucide="store" class="w-4 h-4 text-[#34C759]"></i>
                                <span>Monitoring Merchant WABA</span>
                            </div>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-black/40 dark:text-white/40"></i>
                        </a>
                        <a href="{{ route('admin.whatsapp.index', ['tab' => 'reminders']) }}"
                            class="p-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] text-black dark:text-white text-[12px] font-medium flex items-center justify-between transition-colors">
                            <div class="flex items-center gap-2">
                                <i data-lucide="bell" class="w-4 h-4 text-[#007AFF]"></i>
                                <span>Pengingat Langganan</span>
                            </div>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-black/40 dark:text-white/40"></i>
                        </a>
                        <a href="{{ route('admin.whatsapp.index', ['tab' => 'blast']) }}"
                            class="p-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] text-black dark:text-white text-[12px] font-medium flex items-center justify-between transition-colors">
                            <div class="flex items-center gap-2">
                                <i data-lucide="send" class="w-4 h-4 text-[#FF9500]"></i>
                                <span>Siaran Platform (Blast)</span>
                            </div>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-black/40 dark:text-white/40"></i>
                        </a>
                    </div>
                </div>

            </div>

        </div>

    </div>
