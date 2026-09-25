    <!-- ========================================================================= -->
    <!-- TAB: MARKETPLACE HUB (SHOPEE, TIKTOK SHOP, TOKOPEDIA OPEN API)            -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'marketplace'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">

        <!-- Bento Banner: Marketplace Hub & Overview -->
        <div class="rounded-[22px] p-5 sm:p-6 bg-gradient-to-br from-[#EE4D2D]/10 via-[#000000]/5 dark:via-[#FFFFFF]/5 to-[#00AA5B]/10 border border-[#EE4D2D]/20 backdrop-blur-md shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[14px] bg-gradient-to-br from-[#EE4D2D] to-[#FF5722] text-white flex items-center justify-center shrink-0 shadow-sm">
                        <i data-lucide="shopping-bag" class="w-5 h-5" stroke-width="1.8"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-[15px] sm:text-[16px] font-bold text-black dark:text-white">Pusat Integrasi Developer Marketplace API</h2>
                            @if(!empty($shopeePartnerId) || !empty($tiktokShopAppKey) || !empty($tokopediaClientId))
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25">
                                    <i data-lucide="check-circle-2" class="w-3 h-3" stroke-width="2"></i>
                                    <span>Aktif / Terkonfigurasi</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/15 text-[#FF9500] border border-[#FF9500]/25">
                                    <i data-lucide="alert-circle" class="w-3 h-3" stroke-width="2"></i>
                                    <span>Memerlukan Konfigurasi</span>
                                </span>
                            @endif
                        </div>
                        <p class="text-[12px] text-black/55 dark:text-white/55 mt-0.5">
                            Kredensial Developer App resmi untuk multi-tenant OAuth, sinkronisasi produk, harga per channel, stok, dan webhook pesanan Shopee, TikTok Shop, &amp; Tokopedia.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 self-start sm:self-auto">
                    <a href="{{ route('marketplace-hub.index') }}" target="_blank"
                        class="h-10 px-4 rounded-[12px] text-[12px] font-bold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 active:scale-[0.98] transition-all inline-flex items-center gap-1.5 shrink-0 shadow-sm cursor-pointer">
                        <span>Lihat Panel Merchant</span>
                        <i data-lucide="external-link" class="w-3.5 h-3.5" stroke-width="2"></i>
                    </a>
                </div>
            </div>

            <!-- Readonly OAuth Callback URLs with 1-click copy -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2">
                <!-- Shopee Callback -->
                <div class="p-3.5 rounded-[16px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-sm flex items-center justify-between gap-3 shadow-xs">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5 text-[11.5px] font-bold text-[#EE4D2D]">
                            <i data-lucide="link" class="w-3.5 h-3.5"></i>
                            <span>Shopee OAuth Callback URL</span>
                        </div>
                        <code class="text-[11px] font-mono text-black/80 dark:text-white/80 block truncate select-all">{{ $shopeeRedirectUri ?? 'https://cooca.id/integrations/shopee/callback' }}</code>
                    </div>
                    <button type="button" @click="copyToClipboard('{{ $shopeeRedirectUri ?? 'https://cooca.id/integrations/shopee/callback' }}', 'shopee_cb')"
                        class="h-7 px-2.5 rounded-[8px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] text-[10.5px] font-bold text-black dark:text-white flex items-center gap-1 shrink-0 cursor-pointer">
                        <i data-lucide="copy" class="w-3 h-3"></i>
                        <span>Salin</span>
                    </button>
                </div>

                <!-- TikTok + Tokopedia Unified Callback -->
                <div class="p-3.5 rounded-[16px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-sm flex items-center justify-between gap-3 shadow-xs">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5 text-[11.5px] font-bold text-[#007AFF]">
                            <i data-lucide="layers-3" class="w-3.5 h-3.5"></i>
                            <span>TikTok Shop + Tokopedia (Unified Callback)</span>
                        </div>
                        <code class="text-[11px] font-mono text-black/80 dark:text-white/80 block truncate select-all">{{ $tiktokTokopediaRedirectUri ?? 'https://cooca.id/integrations/tiktok-tokopedia/callback' }}</code>
                    </div>
                    <button type="button" @click="copyToClipboard('{{ $tiktokTokopediaRedirectUri ?? 'https://cooca.id/integrations/tiktok-tokopedia/callback' }}', 'tt_tokped_cb')"
                        class="h-7 px-2.5 rounded-[8px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] text-[10.5px] font-bold text-black dark:text-white flex items-center gap-1 shrink-0 cursor-pointer">
                        <i data-lucide="copy" class="w-3 h-3"></i>
                        <span>Salin</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Form & Settings -->
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="active_tab" value="marketplace">

            <!-- Section 1: Shopee Open Platform -->
            <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 space-y-5 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/[0.04] dark:border-white/[0.06] pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[12px] bg-[#EE4D2D]/15 text-[#EE4D2D] flex items-center justify-center shrink-0 font-black text-sm">
                            SP
                        </div>
                        <div>
                            <h3 class="text-[16px] font-bold text-black dark:text-white">Shopee Open Platform (V2)</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50">Diperoleh dari Shopee Open Platform Console (partner.shopeemobile.com)</p>
                        </div>
                    </div>
                    <button type="button" @click="testMarketplaceConfig('shopee')" :disabled="testingMarketplace"
                        class="h-9 px-3.5 rounded-[12px] text-[12px] font-bold text-[#EE4D2D] bg-[#EE4D2D]/10 hover:bg-[#EE4D2D]/20 active:scale-98 transition-all flex items-center gap-1.5 self-start sm:self-auto cursor-pointer">
                        <i data-lucide="play" class="w-3.5 h-3.5" x-show="!testingMarketplace"></i>
                        <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin" x-show="testingMarketplace"></i>
                        <span>Uji Shopee API</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                            Partner ID <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="text" name="shopee_partner_id" value="{{ old('shopee_partner_id', $shopeePartnerId ?? '') }}"
                            placeholder="Contoh: 1234567"
                            class="w-full h-11 px-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.1] text-[13px] text-black dark:text-white focus:outline-hidden focus:ring-2 focus:ring-[#EE4D2D]/30">
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                            Partner Key / Secret <span class="text-[#FF3B30]">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showShopeePartnerKey ? 'text' : 'password'" name="shopee_partner_key"
                                placeholder="{{ !empty($shopeePartnerKey) ? '••••••••••••••••••••••••' : 'Masukkan Partner Key' }}"
                                class="w-full h-11 pl-3.5 pr-10 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.1] text-[13px] text-black dark:text-white focus:outline-hidden focus:ring-2 focus:ring-[#EE4D2D]/30 font-mono">
                            <button type="button" @click="showShopeePartnerKey = !showShopeePartnerKey"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white cursor-pointer">
                                <i :data-lucide="showShopeePartnerKey ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                            Shopee Redirect URI
                        </label>
                        <input type="url" name="shopee_redirect_uri" value="{{ old('shopee_redirect_uri', $shopeeRedirectUri ?? 'https://cooca.id/integrations/shopee/callback') }}"
                            class="w-full h-11 px-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.1] text-[13px] text-black dark:text-white focus:outline-hidden focus:ring-2 focus:ring-[#EE4D2D]/30 font-mono text-[12px]">
                    </div>

                    <div class="flex items-center gap-3 pt-6">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="shopee_is_production" value="1" {{ !empty($shopeeIsProduction) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-black/20 peer-focus:outline-none rounded-full peer dark:bg-white/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]"></div>
                        </label>
                        <div>
                            <div class="text-[13px] font-bold text-black dark:text-white">Shopee Production Mode</div>
                            <div class="text-[11px] text-black/50 dark:text-white/50">Aktifkan untuk koneksi toko live resmi shopee</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: TikTok Shop + Tokopedia Unified Platform -->
            <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 space-y-5 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/[0.04] dark:border-white/[0.06] pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[12px] bg-gradient-to-br from-black to-[#00AA5B] text-white flex items-center justify-center shrink-0 font-black text-xs tracking-tight shadow-sm">
                            TT+TP
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-[16px] font-bold text-black dark:text-white">TikTok Shop + Tokopedia (Unified Partner API)</h3>
                                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">1 Terpadu</span>
                            </div>
                            <p class="text-[12px] text-black/50 dark:text-white/50">Satu akun pengembang di TikTok Shop Partner Center (partner.tiktokshop.com) untuk integrasi TikTok Shop &amp; Tokopedia di Indonesia.</p>
                        </div>
                    </div>
                    <button type="button" @click="testMarketplaceConfig('tiktok_shop')" :disabled="testingMarketplace"
                        class="h-9 px-3.5 rounded-[12px] text-[12px] font-bold text-black dark:text-white bg-black/[0.06] dark:bg-white/[0.1] hover:bg-black/[0.1] dark:hover:bg-white/[0.15] active:scale-98 transition-all flex items-center gap-1.5 self-start sm:self-auto cursor-pointer">
                        <i data-lucide="play" class="w-3.5 h-3.5" x-show="!testingMarketplace"></i>
                        <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin" x-show="testingMarketplace"></i>
                        <span>Uji TikTok+Tokopedia API</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                            App Key / Client ID <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="text" name="tiktok_shop_app_key" value="{{ old('tiktok_shop_app_key', $tiktokShopAppKey ?? $tokopediaClientId ?? '') }}"
                            placeholder="Contoh: 6lcrat930i94r"
                            class="w-full h-11 px-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.1] text-[13px] text-black dark:text-white focus:outline-hidden focus:ring-2 focus:ring-[#007AFF]/30">
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                            App ID / Service ID
                        </label>
                        <input type="text" name="tiktok_shop_service_id" value="{{ old('tiktok_shop_service_id', $tiktokShopServiceId ?? '7688937207390750472') }}"
                            placeholder="Contoh: 7688937207390750472"
                            class="w-full h-11 px-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.1] text-[13px] text-black dark:text-white focus:outline-hidden focus:ring-2 focus:ring-[#007AFF]/30 font-mono text-[12px]">
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                            App Secret / Client Secret <span class="text-[#FF3B30]">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showTikTokAppSecret ? 'text' : 'password'" name="tiktok_shop_app_secret"
                                placeholder="{{ !empty($tiktokShopAppSecret) || !empty($tokopediaClientSecret) ? '••••••••••••••••••••••••' : 'Masukkan App Secret' }}"
                                class="w-full h-11 pl-3.5 pr-10 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.1] text-[13px] text-black dark:text-white focus:outline-hidden focus:ring-2 focus:ring-[#007AFF]/30 font-mono">
                            <button type="button" @click="showTikTokAppSecret = !showTikTokAppSecret"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white cursor-pointer">
                                <i :data-lucide="showTikTokAppSecret ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                            Unified OAuth Callback URI
                        </label>
                        <input type="url" name="tiktok_tokopedia_redirect_uri" value="{{ old('tiktok_tokopedia_redirect_uri', $tiktokTokopediaRedirectUri ?? 'https://cooca.id/integrations/tiktok-tokopedia/callback') }}"
                            class="w-full h-11 px-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.1] text-[13px] text-black dark:text-white focus:outline-hidden focus:ring-2 focus:ring-[#007AFF]/30 font-mono text-[12px]">
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                            Inbound Webhook URI
                        </label>
                        <input type="url" readonly value="https://cooca.id/webhooks/marketplace/tiktok_shop"
                            class="w-full h-11 px-3.5 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.05] border border-black/[0.06] dark:border-white/[0.08] text-[13px] text-black/70 dark:text-white/70 font-mono text-[12px] cursor-not-allowed">
                    </div>
                </div>
            </div>

            <!-- Save Action Button -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="submit"
                    class="h-11 px-6 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-98 text-white font-bold text-[13.5px] shadow-sm flex items-center gap-2 transition-all cursor-pointer">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Pengaturan Marketplace</span>
                </button>
            </div>
        </form>
    </div>
