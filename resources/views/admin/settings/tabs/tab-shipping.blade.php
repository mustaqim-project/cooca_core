    <!-- ========================================================================= -->
    <!-- TAB 5: LOGISTIK & EKSPEDISI AGREGATOR (BITESHIP MULTI-COURIER API)        -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'shipping'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">

        <!-- Bento Banner: Logistics Hub & Status -->
        <div class="rounded-[22px] p-5 sm:p-6 bg-gradient-to-br from-[#FF9500]/10 via-[#007AFF]/5 to-transparent border border-[#FF9500]/20 backdrop-blur-md shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[14px] bg-[#FF9500]/15 text-[#FF9500] flex items-center justify-center shrink-0">
                        <i data-lucide="truck" class="w-5 h-5" stroke-width="1.8"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-[15px] sm:text-[16px] font-bold text-black dark:text-white">Pusat Konfigurasi Logistik &amp; Ekspedisi (Biteship)</h2>
                            @if(!empty($biteshipApiKey))
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ ($biteshipEnvironment ?? 'production') === 'production' ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25' : 'bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A] border border-[#FF9500]/25' }}">
                                    <i data-lucide="{{ ($biteshipEnvironment ?? 'production') === 'production' ? 'check-circle-2' : 'alert-circle' }}" class="w-3 h-3" stroke-width="2"></i>
                                    <span>Mode {{ ($biteshipEnvironment ?? 'production') === 'production' ? 'Production (Live)' : 'Sandbox (Uji Coba)' }}</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/15 text-[#FF3B30] border border-[#FF3B30]/25">
                                    <i data-lucide="alert-circle" class="w-3 h-3" stroke-width="2"></i>
                                    <span>Belum Dikonfigurasi</span>
                                </span>
                            @endif
                        </div>
                        <p class="text-[12px] text-black/55 dark:text-white/55 mt-0.5">
                            Agregator logistik resmi untuk penjemputan paket otomatis, cetak thermal waybill pesanan Storefront, dan webhook pelacakan kurir real-time.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 self-start sm:self-auto">
                    <a href="https://dashboard.biteship.com" target="_blank"
                        class="h-10 px-4 rounded-[12px] text-[12px] font-bold text-[#FF9500] bg-[#FF9500]/10 hover:bg-[#FF9500]/20 active:scale-[0.98] transition-all inline-flex items-center gap-1.5 shrink-0 shadow-sm cursor-pointer">
                        <span>Dashboard Biteship</span>
                        <i data-lucide="external-link" class="w-3.5 h-3.5" stroke-width="2"></i>
                    </a>
                </div>
            </div>

            <!-- Readonly Webhook Callback Box with 1-click copy -->
            <div class="p-4 rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xs">
                <div class="space-y-0.5">
                    <div class="flex items-center gap-1.5 text-[12px] font-bold text-[#FF9500]">
                        <i data-lucide="webhook" class="w-3.5 h-3.5"></i>
                        <span>Webhook Tracking URL (Pembaruan Status Resi Kurir Otomatis)</span>
                    </div>
                    <code class="text-[11px] font-mono text-black/80 dark:text-white/80 select-all break-all">{{ $biteshipWebhookUrl ?? 'https://cooca.id/api/v1/shipping/biteship/webhook' }}</code>
                    <p class="text-[11px] text-black/45 dark:text-white/45">Pasang URL ini pada menu Dashboard Biteship &gt; Developers &gt; Webhook.</p>
                </div>
                <button type="button" @click="copyToClipboard('{{ $biteshipWebhookUrl ?? 'https://cooca.id/api/v1/shipping/biteship/webhook' }}', 'biteship')"
                    class="h-8 px-3 rounded-[9px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-[11px] font-bold text-black dark:text-white flex items-center gap-1.5 shrink-0 transition-colors cursor-pointer">
                    <i data-lucide="copy" class="w-3.5 h-3.5" x-show="!copiedBiteshipWebhook"></i>
                    <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759]" x-show="copiedBiteshipWebhook"></i>
                    <span x-text="copiedBiteshipWebhook ? 'Tersalin!' : 'Salin URL'"></span>
                </button>
            </div>
        </div>

        <!-- 2-Column Bento Grid: Main Form & Supported Couriers -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Main Form Column (2 Cols) -->
            <div class="lg:col-span-2 space-y-6">
                <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="active_tab" value="shipping">

                    <!-- Bento Card: Parameter Kredensial Biteship API -->
                    <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 space-y-5 shadow-sm">
                        <div class="flex items-center justify-between border-b border-black/[0.04] dark:border-white/[0.06] pb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-[12px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center shrink-0">
                                    <i data-lucide="key-round" class="w-4.5 h-4.5"></i>
                                </div>
                                <div>
                                    <h3 class="text-[16px] font-bold text-black dark:text-white">Kredensial API Biteship</h3>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">Diperoleh dari menu Developers &gt; API Keys di Dashboard Biteship</p>
                                </div>
                            </div>
                        </div>

                        <!-- Mode Switcher -->
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-2">
                                Lingkungan Integrasi (Environment) <span class="text-[#FF3B30]">*</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" x-data="{ currentEnv: '{{ old('biteship_environment', $biteshipEnvironment ?? 'production') }}' }">
                                <label class="flex items-center gap-3 p-3.5 rounded-[14px] border cursor-pointer transition-all"
                                    :class="currentEnv === 'sandbox' ? 'bg-[#FF9500]/10 border-[#FF9500]/40 text-black dark:text-white' : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/[0.06] dark:border-white/[0.08]'">
                                    <input type="radio" name="biteship_environment" value="sandbox" x-model="currentEnv"
                                        class="w-4 h-4 text-[#FF9500] focus:ring-[#FF9500]/30 cursor-pointer">
                                    <div>
                                        <div class="text-[13px] font-bold">Sandbox (Testing / Uji Coba)</div>
                                        <div class="text-[11px] text-black/50 dark:text-white/50">Pesanan pengiriman dummy tanpa penjemputan kurir fisik</div>
                                    </div>
                                </label>
                                <label class="flex items-center gap-3 p-3.5 rounded-[14px] border cursor-pointer transition-all"
                                    :class="currentEnv === 'production' ? 'bg-[#34C759]/10 border-[#34C759]/40 text-black dark:text-white' : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/[0.06] dark:border-white/[0.08]'">
                                    <input type="radio" name="biteship_environment" value="production" x-model="currentEnv"
                                        class="w-4 h-4 text-[#34C759] focus:ring-[#34C759]/30 cursor-pointer">
                                    <div>
                                        <div class="text-[13px] font-bold">Production (Live Operasional)</div>
                                        <div class="text-[11px] text-black/50 dark:text-white/50">Penjemputan paket kurir nyata langsung ke outlet toko merchant</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Biteship API Secret Key -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">
                                    Biteship Secret API Key <span class="text-[#FF3B30]">*</span>
                                </label>
                                @if(!empty($biteshipApiKey))
                                    <span class="text-[11px] font-medium text-[#34C759] flex items-center gap-1">
                                        <i data-lucide="check" class="w-3 h-3"></i> Tersimpan di Database
                                    </span>
                                @endif
                            </div>
                            <div class="relative">
                                <input :type="showBiteshipKey ? 'text' : 'password'" name="biteship_api_key"
                                    value="{{ old('biteship_api_key', $biteshipApiKey) }}"
                                    placeholder="biteship_live.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
                                    required
                                    class="w-full h-11 pl-4 pr-12 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF9500]/50 transition-all">
                                <button type="button" @click="showBiteshipKey = !showBiteshipKey"
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white transition-colors cursor-pointer">
                                    <i data-lucide="eye" class="w-4 h-4" x-show="!showBiteshipKey"></i>
                                    <i data-lucide="eye-off" class="w-4 h-4" x-show="showBiteshipKey"></i>
                                </button>
                            </div>
                            <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">
                                Token otentikasi API Biteship berawalan <code class="font-mono text-[10.5px]">biteship_live.</code> atau <code class="font-mono text-[10.5px]">biteship_test.</code>.
                            </p>
                        </div>

                        <!-- Base URL & Service Fee Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    Biteship Base API URL <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="url" name="biteship_base_url"
                                    value="{{ old('biteship_base_url', $biteshipBaseUrl ?? 'https://api.biteship.com') }}"
                                    placeholder="https://api.biteship.com"
                                    required
                                    class="w-full h-11 px-4 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF9500]/50 transition-all">
                            </div>

                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    Platform Handling Fee (Rp / Pesanan) <span class="text-[#FF3B30]">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[12px] font-bold text-black/40 dark:text-white/40">Rp</span>
                                    <input type="number" step="100" min="0" name="biteship_service_fee"
                                        value="{{ old('biteship_service_fee', $biteshipServiceFee ?? '1000') }}"
                                        placeholder="1000"
                                        required
                                        class="w-full h-11 pl-11 pr-4 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[13px] font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF9500]/50 transition-all">
                                </div>
                                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Biaya penanganan platform per booking pesanan kurir.</p>
                            </div>
                        </div>

                        <!-- Action Bar: Save & Test Connection -->
                        <div class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08] flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                            <button type="button" @click="testBiteshipConfig()" :disabled="testingBiteship"
                                class="h-11 px-4 rounded-[14px] bg-[#FF9500]/10 hover:bg-[#FF9500]/20 active:scale-[0.98] text-[#FF9500] text-[13px] font-bold inline-flex items-center justify-center gap-2 transition-all cursor-pointer disabled:opacity-50">
                                <i data-lucide="refresh-cw" class="w-4 h-4" :class="testingBiteship ? 'animate-spin' : ''"></i>
                                <span x-text="testingBiteship ? 'Memverifikasi API...' : 'Uji Koneksi API Biteship'"></span>
                            </button>

                            <button type="submit"
                                class="h-11 px-6 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-[13px] font-bold inline-flex items-center justify-center gap-2 shadow-[0_2px_8px_rgba(0,122,255,0.25)] transition-all cursor-pointer">
                                <i data-lucide="save" class="w-4 h-4"></i>
                                <span>Simpan Konfigurasi Logistik</span>
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Live Test Result Banner -->
                <div x-show="testBiteshipResult" x-cloak class="rounded-[20px] p-4 border backdrop-blur-sm transition-all"
                    :class="testBiteshipResult?.success ? 'bg-[#34C759]/10 border-[#34C759]/30 text-black dark:text-white' : 'bg-[#FF3B30]/10 border-[#FF3B30]/30 text-black dark:text-white'">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 mt-0.5"
                            :class="testBiteshipResult?.success ? 'bg-[#34C759]/20 text-[#34C759]' : 'bg-[#FF3B30]/20 text-[#FF3B30]'">
                            <i data-lucide="check-circle" class="w-4 h-4" x-show="testBiteshipResult?.success"></i>
                            <i data-lucide="x-circle" class="w-4 h-4" x-show="!testBiteshipResult?.success"></i>
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-[13px] font-bold" x-text="testBiteshipResult?.success ? 'Koneksi Biteship Berhasil' : 'Koneksi Biteship Gagal'"></h4>
                            <p class="text-[12px] text-black/70 dark:text-white/70" x-text="testBiteshipResult?.message"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Info Column (1 Col) -->
            <div class="space-y-6">

                <!-- Supported Couriers Bento Card -->
                <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 space-y-4 shadow-sm">
                    <div class="flex items-center gap-2.5 pb-2 border-b border-black/[0.04] dark:border-white/[0.06]">
                        <div class="w-7 h-7 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                            <i data-lucide="package-check" class="w-4 h-4" stroke-width="1.8"></i>
                        </div>
                        <h4 class="text-[14px] font-bold text-black dark:text-white">Ekspedisi Terintegrasi</h4>
                    </div>

                    <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                        Platform Cooca terhubung otomatis dengan jaringan kurir nasional melalui API Biteship:
                    </p>

                    <div class="space-y-2.5 pt-1">
                        <div class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i data-lucide="zap" class="w-4 h-4 text-[#FF9500]"></i>
                                <span class="text-[12px] font-semibold text-black dark:text-white">Instant &amp; Same Day</span>
                            </div>
                            <span class="text-[11px] font-medium text-black/50 dark:text-white/50">GoSend, GrabExpress</span>
                        </div>

                        <div class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i data-lucide="box" class="w-4 h-4 text-[#007AFF]"></i>
                                <span class="text-[12px] font-semibold text-black dark:text-white">Reguler / Standar</span>
                            </div>
                            <span class="text-[11px] font-medium text-black/50 dark:text-white/50">J&amp;T, SiCepat, JNE</span>
                        </div>

                        <div class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i data-lucide="archive" class="w-4 h-4 text-[#5856D6]"></i>
                                <span class="text-[12px] font-semibold text-black dark:text-white">Hemat &amp; Kargo</span>
                            </div>
                            <span class="text-[11px] font-medium text-black/50 dark:text-white/50">Anteraja, Lion Parcel</span>
                        </div>
                    </div>
                </div>

                <!-- Guidance Note -->
                <div class="rounded-[24px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] p-5 space-y-2">
                    <div class="flex items-center gap-2 text-[#007AFF] text-[13px] font-bold">
                        <i data-lucide="info" class="w-4 h-4"></i>
                        <span>Alamat Asal Toko (Origin Hub)</span>
                    </div>
                    <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                        Alamat asal penjemputan paket dikelola oleh masing-masing pemilik usaha (Merchant) di menu <strong>Toko Online &gt; Pengaturan Ekspedisi</strong>. Setiap outlet menentukan koordinat GPS dan kode pos secara mandiri untuk penjemputan kurir.
                    </p>
                </div>

            </div>

        </div>

    </div>
