    <!-- ========================================================================= -->
    <!-- TAB 2: GATEWAY PEMBAYARAN TRIPAY (MODEL B PLATFORM CENTRALIZED)           -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'payment'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">

        <!-- Bento Banner: Gateway Hub & Mode Indicator -->
        <div class="rounded-[22px] p-5 sm:p-6 bg-gradient-to-br from-[#007AFF]/10 via-[#5856D6]/5 to-transparent border border-[#007AFF]/20 backdrop-blur-md shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[14px] bg-[#007AFF]/15 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                        <i data-lucide="credit-card" class="w-5 h-5" stroke-width="1.8"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-[15px] sm:text-[16px] font-bold text-black dark:text-white">Pusat Konfigurasi TriPay Gateway (Model B Terpusat)</h2>
                            @if(!empty($tripayApiKey))
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $tripayIsProduction ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25' : 'bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A] border border-[#FF9500]/25' }}">
                                    <i data-lucide="{{ $tripayIsProduction ? 'check-circle-2' : 'alert-circle' }}" class="w-3 h-3" stroke-width="2"></i>
                                    <span>Mode {{ $tripayIsProduction ? 'Production (Live)' : 'Sandbox (Uji Coba)' }}</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/15 text-[#FF3B30] border border-[#FF3B30]/25">
                                    <i data-lucide="alert-circle" class="w-3 h-3" stroke-width="2"></i>
                                    <span>Belum Dikonfigurasi</span>
                                </span>
                            @endif
                        </div>
                        <p class="text-[12px] text-black/55 dark:text-white/55 mt-0.5">
                            Gerbang pembayaran terpusat untuk QRIS Dinamis meja kasir, checkout Toko Online, dan tagihan langganan SaaS.
                        </p>
                    </div>
                </div>
                <a href="https://tripay.co.id/member" target="_blank"
                    class="h-10 px-4 rounded-[12px] text-[12px] font-bold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 active:scale-[0.98] transition-all inline-flex items-center gap-1.5 self-start sm:self-auto shrink-0 shadow-sm cursor-pointer">
                    <span>Merchant Dashboard TriPay</span>
                    <i data-lucide="external-link" class="w-3.5 h-3.5" stroke-width="2"></i>
                </a>
            </div>

            <!-- Readonly Callback Box with 1-click copy -->
            <div class="p-4 rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xs">
                <div class="space-y-0.5">
                    <div class="flex items-center gap-1.5 text-[12px] font-bold text-[#007AFF] dark:text-[#0A84FF]">
                        <i data-lucide="webhook" class="w-3.5 h-3.5"></i>
                        <span>Webhook Callback URL (Notifikasi Pembayaran Real-Time)</span>
                    </div>
                    <code class="text-[11px] font-mono text-black/80 dark:text-white/80 select-all break-all">{{ $tripayCallbackUrl ?? 'https://cooca.id/api/v1/payment/tripay/callback' }}</code>
                    <p class="text-[11px] text-black/45 dark:text-white/45">Pasang URL ini pada menu Merchant Dashboard TriPay &gt; Pengaturan &gt; Webhook.</p>
                </div>
                <button type="button" @click="copyToClipboard('{{ $tripayCallbackUrl ?? 'https://cooca.id/api/v1/payment/tripay/callback' }}', 'tripay')"
                    class="h-8 px-3 rounded-[9px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-[11px] font-bold text-black dark:text-white flex items-center gap-1.5 shrink-0 transition-colors cursor-pointer">
                    <i data-lucide="copy" class="w-3.5 h-3.5" x-show="!copiedTripayCallback"></i>
                    <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759]" x-show="copiedTripayCallback"></i>
                    <span x-text="copiedTripayCallback ? 'Tersalin!' : 'Salin URL'"></span>
                </button>
            </div>
        </div>

        <!-- 2-Column Bento Grid: Main Form & Security/Architecture Notes -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Main Form Column (2 Cols) -->
            <div class="lg:col-span-2 space-y-6">
                <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="active_tab" value="payment">

                    <!-- Bento Card: Parameter Kredensial TriPay -->
                    <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 space-y-5 shadow-sm">
                        <div class="flex items-center justify-between border-b border-black/[0.04] dark:border-white/[0.06] pb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                                    <i data-lucide="key-round" class="w-4.5 h-4.5"></i>
                                </div>
                                <div>
                                    <h3 class="text-[16px] font-bold text-black dark:text-white">Kredensial API TriPay Merchant</h3>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">Diperoleh dari menu Pengaturan &gt; Merchant &amp; Integrasi di TriPay</p>
                                </div>
                            </div>
                        </div>

                        <!-- Mode Switcher -->
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-2">
                                Lingkungan Gateway (Environment Mode) <span class="text-[#FF3B30]">*</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="flex items-center gap-3 p-3.5 rounded-[14px] border cursor-pointer transition-all"
                                    :class="{{ $tripayIsProduction ? 'false' : 'true' }} ? 'bg-[#FF9500]/10 border-[#FF9500]/40 text-black dark:text-white' : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/[0.06] dark:border-white/[0.08]'">
                                    <input type="radio" name="tripay_is_production" value="0" {{ !$tripayIsProduction ? 'checked' : '' }}
                                        class="w-4 h-4 text-[#FF9500] focus:ring-[#FF9500]/30 cursor-pointer">
                                    <div>
                                        <div class="text-[13px] font-bold">Sandbox (Testing / Uji Coba)</div>
                                        <div class="text-[11px] text-black/50 dark:text-white/50">Kanal QRIS &amp; VA menggunakan simulator pembayaran gratis</div>
                                    </div>
                                </label>
                                <label class="flex items-center gap-3 p-3.5 rounded-[14px] border cursor-pointer transition-all"
                                    :class="{{ $tripayIsProduction ? 'true' : 'false' }} ? 'bg-[#34C759]/10 border-[#34C759]/40 text-black dark:text-white' : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/[0.06] dark:border-white/[0.08]'">
                                    <input type="radio" name="tripay_is_production" value="1" {{ $tripayIsProduction ? 'checked' : '' }}
                                        class="w-4 h-4 text-[#34C759] focus:ring-[#34C759]/30 cursor-pointer">
                                    <div>
                                        <div class="text-[13px] font-bold">Production (Live Transaksi Riil)</div>
                                        <div class="text-[11px] text-black/50 dark:text-white/50">Menerima uang nyata dari nasabah seluruh bank &amp; e-wallet</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                            <!-- Merchant Code -->
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    Merchant Code <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="text" name="tripay_merchant_code" value="{{ old('tripay_merchant_code', $tripayMerchantCode ?? '') }}"
                                    placeholder="Contoh: T38171" required
                                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Kode unik merchant yang tertera di profil TriPay.</p>
                            </div>

                            <!-- API Key -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">
                                        API Key <span class="text-[#FF3B30]">*</span>
                                    </label>
                                    <button type="button" @click="showTripayKey = !showTripayKey" class="text-[11px] font-medium text-[#007AFF] hover:underline cursor-pointer">
                                        <span x-text="showTripayKey ? 'Sembunyikan' : 'Tampilkan'"></span>
                                    </button>
                                </div>
                                <input :type="showTripayKey ? 'text' : 'password'" name="tripay_api_key" value="{{ old('tripay_api_key', $tripayApiKey ?? '') }}"
                                    placeholder="DEV-..." required
                                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Kunci publik otorisasi API request.</p>
                            </div>

                            <!-- Private Key (Encrypted Secret) -->
                            <div class="sm:col-span-2">
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">
                                        Private Key (Kunci Rahasia Signature HMAC-SHA256) <span class="text-[#FF3B30]">*</span>
                                    </label>
                                    <button type="button" @click="showTripayPrivateKey = !showTripayPrivateKey" class="text-[11px] font-medium text-[#007AFF] hover:underline cursor-pointer">
                                        <span x-text="showTripayPrivateKey ? 'Sembunyikan' : 'Lihat / Ubah Key'"></span>
                                    </button>
                                </div>
                                <input :type="showTripayPrivateKey ? 'text' : 'password'" name="tripay_private_key"
                                    value="{{ old('tripay_private_key', $tripayPrivateKey ?? '') }}"
                                    placeholder="{{ !empty($tripayPrivateKey) ? '••••••••••••••••••••••••••••••••' : 'Masukkan Private Key TriPay' }}"
                                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">
                                    Kunci rahasia untuk memvalidasi callback webhook masuk secara kriptografis. Disimpan terenkripsi AES-256 pada database.
                                </p>
                            </div>

                            <!-- Sandbox Endpoint URL -->
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    URL Sandbox API
                                </label>
                                <input type="url" name="tripay_sandbox_url" value="{{ old('tripay_sandbox_url', $tripaySandboxUrl ?? 'https://tripay.co.id/api-sandbox/') }}"
                                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[12.5px] text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>

                            <!-- Production Endpoint URL -->
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    URL Production API
                                </label>
                                <input type="url" name="tripay_prod_url" value="{{ old('tripay_prod_url', $tripayProdUrl ?? 'https://tripay.co.id/api/') }}"
                                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[12.5px] text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                        <button type="button" @click="testTripayConfig()" :disabled="testingTripay"
                            class="w-full sm:w-auto h-11 px-4.5 rounded-[14px] text-[13px] font-bold text-black dark:text-white bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] border border-black/[0.06] dark:border-white/[0.08] transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                            <i data-lucide="loader-2" x-show="testingTripay" class="w-4 h-4 animate-spin text-[#007AFF]"></i>
                            <i data-lucide="zap" x-show="!testingTripay" class="w-4 h-4 text-[#FF9500]"></i>
                            <span x-text="testingTripay ? 'Menguji Koneksi...' : 'Uji Koneksi Gateway'"></span>
                        </button>

                        <button type="submit"
                            class="w-full sm:w-auto h-11 px-6 rounded-[14px] text-[13.5px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-sm shadow-[#007AFF]/25 cursor-pointer">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Simpan Pengaturan TriPay</span>
                        </button>
                    </div>

                    <!-- Dynamic Test Result Bento Box -->
                    <div x-show="testTripayResult" x-transition class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                        <div class="flex items-center gap-2 text-[13px] font-bold text-black dark:text-white">
                            <i data-lucide="activity" class="w-4 h-4 text-[#007AFF]"></i>
                            <span>Hasil Uji Konektivitas Gateway TriPay</span>
                        </div>
                        <div class="p-3.5 rounded-[12px] border text-[12px]"
                            :class="testTripayResult?.success ? 'bg-[#34C759]/10 border-[#34C759]/30 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF3B30]/10 border-[#FF3B30]/30 text-[#FF3B30]'">
                            <div class="font-bold flex items-center gap-1.5 mb-1">
                                <i data-lucide="credit-card" class="w-4 h-4"></i>
                                <span x-text="testTripayResult?.success ? 'Koneksi Berhasil' : 'Koneksi Gagal'"></span>
                            </div>
                            <p class="text-[12px] leading-relaxed" x-text="testTripayResult?.message"></p>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Sidebar Column: Model B Architecture & Security -->
            <div class="space-y-6">

                <!-- Model B Architecture Bento Card -->
                <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 space-y-3.5 shadow-sm">
                    <div class="flex items-center gap-2.5 text-[#007AFF]">
                        <i data-lucide="cpu" class="w-5 h-5"></i>
                        <h4 class="text-[14px] font-bold text-black dark:text-white">Arsitektur Model B Terpusat</h4>
                    </div>
                    <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                        Platform Cooca mengelola akun gateway utama, memungkinkan tenant UMKM langsung menerima pembayaran QRIS Dinamis dan Virtual Account tanpa perlu mendaftar dan verifikasi KYC badan usaha secara mandiri.
                    </p>
                    <div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06] text-[11.5px] text-black/60 dark:text-white/60 space-y-1.5">
                        <div class="flex items-center gap-1.5 text-[#34C759]">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            <span>Zero Onboarding Friction bagi Tenant</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-[#34C759]">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            <span>Verifikasi Callback HMAC-SHA256</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-[#34C759]">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            <span>Rekonsiliasi Settlement ke Bank Tenant</span>
                        </div>
                    </div>
                </div>

                <!-- MDR Rate Card -->
                <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-5 text-[12px] text-black/60 dark:text-white/60 space-y-2.5 shadow-sm">
                    <div class="flex items-center gap-2 text-[13px] font-bold text-black dark:text-white">
                        <i data-lucide="receipt" class="w-4 h-4 text-[#34C759]"></i>
                        <span>Struktur Biaya MDR Gateway:</span>
                    </div>
                    <p class="text-[11.5px] leading-relaxed">
                        Biaya transaksi resmi yang dipotong TriPay pada saat pembayaran berhasil:
                    </p>
                    <ul class="text-[11px] list-disc list-inside space-y-1 text-black/70 dark:text-white/70 pl-1 font-mono">
                        <li>QRIS Dinamis: Rp 750 + 0.70%</li>
                        <li>BCA Virtual Account: Rp 4.000</li>
                        <li>BRI / BNI / Mandiri / BSI: Rp 3.500</li>
                    </ul>
                </div>

            </div>

        </div>

    </div>

