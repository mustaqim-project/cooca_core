@extends('layouts.admin', [
    'title' => 'Pengaturan Platform & Sistem - Admin Console',
    'headerTitle' => 'Pengaturan Platform & Integrasi',
    'headerSubtitle' => 'Pusat kendali integrasi Google Cloud OAuth, Media Sosial (Meta & TikTok), server email SMTP, dan parameter sistem Cooca',
])

@section('content')
<div class="max-w-7xl w-full min-w-0 mx-auto pb-28 lg:pb-10 space-y-6" x-data="{
    activeTab: '{{ $defaultTab ?? request('tab', 'system') }}',
    showSecret: false,
    showPassword: false,
    showMetaSecret: false,
    showTikTokSecret: false,
    showIgSecret: false,
    showIgToken: false,
    showTripayKey: false,
    showTripayPrivateKey: false,
    showWaToken: false,
    showWaSecret: false,
    showBiteshipKey: false,
    copiedOwner: false,
    copiedCustomer: false,
    copiedWebhook: false,
    copiedTikTokRedirect: false,
    copiedTripayCallback: false,
    copiedWaWebhook: false,
    copiedBiteshipWebhook: false,
    switchTab(tab) {
        this.activeTab = tab;
        const url = new URL(window.location);
        url.searchParams.set('tab', tab);
        window.history.replaceState({}, '', url);
    },
    copyToClipboard(text, type) {
        navigator.clipboard.writeText(text).then(() => {
            if (type === 'owner') {
                this.copiedOwner = true;
                setTimeout(() => this.copiedOwner = false, 2000);
            } else if (type === 'customer') {
                this.copiedCustomer = true;
                setTimeout(() => this.copiedCustomer = false, 2000);
            } else if (type === 'webhook') {
                this.copiedWebhook = true;
                setTimeout(() => this.copiedWebhook = false, 2000);
            } else if (type === 'tiktok') {
                this.copiedTikTokRedirect = true;
                setTimeout(() => this.copiedTikTokRedirect = false, 2000);
            } else if (type === 'tripay') {
                this.copiedTripayCallback = true;
                setTimeout(() => this.copiedTripayCallback = false, 2000);
            } else if (type === 'wa') {
                this.copiedWaWebhook = true;
                setTimeout(() => this.copiedWaWebhook = false, 2000);
            } else if (type === 'biteship') {
                this.copiedBiteshipWebhook = true;
                setTimeout(() => this.copiedBiteshipWebhook = false, 2000);
            }
        });
    },
    testingSocial: false,
    testSocialResult: null,
    testSocialMediaConfig() {
        this.testingSocial = true;
        this.testSocialResult = null;
        fetch('{{ route('admin.settings.test-social') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}'
            }
        })
        .then(r => r.json())
        .then(data => {
            this.testingSocial = false;
            if (data.success) {
                this.testSocialResult = data.data;
                if (window.AppAlert) {
                    AppAlert.success('Pengujian kredensial media sosial selesai.');
                }
            }
        })
        .catch(e => {
            this.testingSocial = false;
            if (window.AppAlert) {
                AppAlert.error('Gagal menjalankan pengujian: ' + (e.message || e));
            }
        });
    },
    testingTripay: false,
    testTripayResult: null,
    testTripayConfig() {
        this.testingTripay = true;
        this.testTripayResult = null;
        fetch('{{ route('admin.settings.test-tripay') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}'
            }
        })
        .then(r => r.json())
        .then(data => {
            this.testingTripay = false;
            this.testTripayResult = data;
            if (data.success && window.AppAlert) {
                AppAlert.success(data.message);
            } else if (!data.success && window.AppAlert) {
                AppAlert.error(data.message);
            }
        })
        .catch(e => {
            this.testingTripay = false;
            if (window.AppAlert) {
                AppAlert.error('Gagal menguji koneksi TriPay: ' + (e.message || e));
            }
        });
    },
    testingWa: false,
    testWaResult: null,
    testWhatsAppConfig() {
        this.testingWa = true;
        this.testWaResult = null;
        fetch('{{ route('admin.settings.test-whatsapp') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}'
            }
        })
        .then(r => r.json())
        .then(data => {
            this.testingWa = false;
            this.testWaResult = data;
            if (data.success && window.AppAlert) {
                AppAlert.success(data.message);
            } else if (!data.success && window.AppAlert) {
                AppAlert.error(data.message);
            }
        })
        .catch(e => {
            this.testingWa = false;
            if (window.AppAlert) {
                AppAlert.error('Gagal menguji Meta WhatsApp: ' + (e.message || e));
            }
        });
    },
    testingIg: false,
    testIgResult: null,
    testInstagramConfig() {
        this.testingIg = true;
        this.testIgResult = null;
        fetch('{{ route('admin.settings.test-instagram') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}'
            }
        })
        .then(r => r.json())
        .then(data => {
            this.testingIg = false;
            this.testIgResult = data;
            if (data.success && window.AppAlert) {
                AppAlert.success(data.message);
            } else if (!data.success && window.AppAlert) {
                AppAlert.error(data.message);
            }
        })
        .catch(e => {
            this.testingIg = false;
            if (window.AppAlert) {
                AppAlert.error('Gagal menguji Instagram API: ' + (e.message || e));
            }
        });
    },
    testingBiteship: false,
    testBiteshipResult: null,
    testBiteshipConfig() {
        this.testingBiteship = true;
        this.testBiteshipResult = null;
        fetch('{{ route('admin.settings.test-biteship') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}'
            }
        })
        .then(r => r.json())
        .then(data => {
            this.testingBiteship = false;
            this.testBiteshipResult = data;
            if (data.success && window.AppAlert) {
                AppAlert.success(data.message);
            } else if (!data.success && window.AppAlert) {
                AppAlert.error(data.message);
            }
        })
        .catch(e => {
            this.testingBiteship = false;
            if (window.AppAlert) {
                AppAlert.error('Gagal menguji koneksi Biteship: ' + (e.message || e));
            }
        });
    },
    applyPreset(preset) {
        if (preset === 'gmail') {
            const m = document.querySelector('[name=mail_mailer]'); if (m) m.value = 'smtp';
            const h = document.querySelector('[name=mail_host]'); if (h) h.value = 'smtp.gmail.com';
            const p = document.querySelector('[name=mail_port]'); if (p) p.value = '587';
            const e = document.querySelector('[name=mail_encryption]'); if (e) e.value = 'tls';
        } else if (preset === 'mailtrap') {
            const m = document.querySelector('[name=mail_mailer]'); if (m) m.value = 'smtp';
            const h = document.querySelector('[name=mail_host]'); if (h) h.value = 'sandbox.smtp.mailtrap.io';
            const p = document.querySelector('[name=mail_port]'); if (p) p.value = '2525';
            const e = document.querySelector('[name=mail_encryption]'); if (e) e.value = 'tls';
        } else if (preset === 'cpanel') {
            const m = document.querySelector('[name=mail_mailer]'); if (m) m.value = 'smtp';
            const h = document.querySelector('[name=mail_host]'); if (h) h.value = 'mail.domainanda.com';
            const p = document.querySelector('[name=mail_port]'); if (p) p.value = '465';
            const e = document.querySelector('[name=mail_encryption]'); if (e) e.value = 'ssl';
        } else if (preset === 'log') {
            const m = document.querySelector('[name=mail_mailer]'); if (m) m.value = 'log';
        }
        if (window.AppAlert) {
            AppAlert.info('Preset ' + preset.toUpperCase() + ' berhasil diterapkan ke kolom SMTP.');
        }
    }
}">

    <!-- Apple Segmented Tab Bar (macOS Sonoma / iOS 18 System Settings Style) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-1.5 rounded-[20px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md">
        <div class="flex items-center gap-1.5 overflow-x-auto scrollbar-none w-full sm:w-auto p-0.5">
            <!-- Tab 1: Google OAuth & Sistem -->
            <button type="button" @click="switchTab('system')"
                :class="activeTab === 'system'
                    ? 'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-bold border border-black/[0.06] dark:border-white/[0.08]'
                    : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-semibold hover:bg-black/[0.03] dark:hover:bg-white/[0.04]'"
                class="h-11 sm:h-10 px-4 sm:px-4 rounded-[14px] text-[13px] sm:text-[13.5px] transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                <i data-lucide="sliders" class="w-4 h-4 shrink-0" :class="activeTab === 'system' ? 'text-[#007AFF]' : 'text-black/40 dark:text-white/40'"></i>
                <span>OAuth &amp; Sistem</span>
                @if(!empty($googleClientId))
                    <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0" title="OAuth Terkonfigurasi"></i>
                @else
                    <i data-lucide="alert-circle" class="w-3.5 h-3.5 text-[#FF9500] shrink-0" title="Belum Dikonfigurasi"></i>
                @endif
            </button>

            <!-- Tab 2: Gateway Pembayaran (TriPay) -->
            <button type="button" @click="switchTab('payment')"
                :class="activeTab === 'payment'
                    ? 'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-bold border border-black/[0.06] dark:border-white/[0.08]'
                    : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-semibold hover:bg-black/[0.03] dark:hover:bg-white/[0.04]'"
                class="h-11 sm:h-10 px-4 sm:px-4 rounded-[14px] text-[13px] sm:text-[13.5px] transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                <i data-lucide="credit-card" class="w-4 h-4 shrink-0" :class="activeTab === 'payment' ? 'text-[#007AFF]' : 'text-black/40 dark:text-white/40'"></i>
                <span>Pembayaran (TriPay)</span>
                @if(!empty($tripayApiKey))
                    <span class="text-[10px] font-bold px-1.5 py-0.2 rounded-md {{ $tripayIsProduction ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A]' }}">
                        {{ $tripayIsProduction ? 'PROD' : 'SANDBOX' }}
                    </span>
                    <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0" title="TriPay Terkonfigurasi"></i>
                @else
                    <i data-lucide="alert-circle" class="w-3.5 h-3.5 text-[#FF9500] shrink-0" title="Belum Dikonfigurasi"></i>
                @endif
            </button>

            <!-- Tab 3: Meta WhatsApp Cloud API -->
            <button type="button" @click="switchTab('whatsapp')"
                :class="activeTab === 'whatsapp'
                    ? 'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-bold border border-black/[0.06] dark:border-white/[0.08]'
                    : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-semibold hover:bg-black/[0.03] dark:hover:bg-white/[0.04]'"
                class="h-11 sm:h-10 px-4 sm:px-4 rounded-[14px] text-[13px] sm:text-[13.5px] transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                <i data-lucide="message-square" class="w-4 h-4 shrink-0" :class="activeTab === 'whatsapp' ? 'text-[#25D366]' : 'text-black/40 dark:text-white/40'"></i>
                <span>WhatsApp Cloud API</span>
                @if(!empty($metaWaPhoneNumberId))
                    <span class="text-[10px] font-bold px-1.5 py-0.2 rounded-md bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                        {{ $metaWaGraphVersion ?? 'v25.0' }}
                    </span>
                    <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0" title="WhatsApp Terkonfigurasi"></i>
                @else
                    <i data-lucide="alert-circle" class="w-3.5 h-3.5 text-[#FF9500] shrink-0" title="Belum Dikonfigurasi"></i>
                @endif
            </button>

            <!-- Tab 4: Media Sosial (Meta & TikTok) -->
            <button type="button" @click="switchTab('social')"
                :class="activeTab === 'social'
                    ? 'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-bold border border-black/[0.06] dark:border-white/[0.08]'
                    : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-semibold hover:bg-black/[0.03] dark:hover:bg-white/[0.04]'"
                class="h-11 sm:h-10 px-4 sm:px-4 rounded-[14px] text-[13px] sm:text-[13.5px] transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                <i data-lucide="share-2" class="w-4 h-4 shrink-0" :class="activeTab === 'social' ? 'text-[#007AFF]' : 'text-black/40 dark:text-white/40'"></i>
                <span>Media Sosial</span>
                @if(!empty($metaSocialAppId) && !empty($tiktokClientKey))
                    <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0" title="Meta &amp; TikTok Terkonfigurasi"></i>
                @elseif(!empty($metaSocialAppId) || !empty($tiktokClientKey))
                    <span class="w-2 h-2 rounded-full bg-[#007AFF]" title="Sebagian Terkonfigurasi"></span>
                @else
                    <i data-lucide="alert-circle" class="w-3.5 h-3.5 text-[#FF9500] shrink-0" title="Belum Dikonfigurasi"></i>
                @endif
            </button>

            <!-- Tab 5: Ekspedisi & Logistik (Biteship) -->
            <button type="button" @click="switchTab('shipping')"
                :class="activeTab === 'shipping'
                    ? 'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-bold border border-black/[0.06] dark:border-white/[0.08]'
                    : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-semibold hover:bg-black/[0.03] dark:hover:bg-white/[0.04]'"
                class="h-11 sm:h-10 px-4 sm:px-4 rounded-[14px] text-[13px] sm:text-[13.5px] transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                <i data-lucide="truck" class="w-4 h-4 shrink-0" :class="activeTab === 'shipping' ? 'text-[#FF9500]' : 'text-black/40 dark:text-white/40'"></i>
                <span>Logistik (Biteship)</span>
                @if(!empty($biteshipApiKey))
                    <span class="text-[10px] font-bold px-1.5 py-0.2 rounded-md {{ ($biteshipEnvironment ?? 'production') === 'production' ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A]' }}">
                        {{ strtoupper($biteshipEnvironment ?? 'PROD') }}
                    </span>
                    <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0" title="Biteship Terkonfigurasi"></i>
                @else
                    <i data-lucide="alert-circle" class="w-3.5 h-3.5 text-[#FF9500] shrink-0" title="Belum Dikonfigurasi"></i>
                @endif
            </button>

            <!-- Tab 6: Server SMTP Email -->
            <button type="button" @click="switchTab('smtp')"
                :class="activeTab === 'smtp'
                    ? 'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-bold border border-black/[0.06] dark:border-white/[0.08]'
                    : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-semibold hover:bg-black/[0.03] dark:hover:bg-white/[0.04]'"
                class="h-11 sm:h-10 px-4 sm:px-4 rounded-[14px] text-[13px] sm:text-[13.5px] transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                <i data-lucide="mail-cog" class="w-4 h-4 shrink-0" :class="activeTab === 'smtp' ? 'text-[#007AFF]' : 'text-black/40 dark:text-white/40'"></i>
                <span>Pengaturan SMTP Email</span>
                <span class="text-[10px] font-bold px-1.5 py-0.2 rounded-md bg-black/[0.06] dark:bg-white/[0.08] text-black/60 dark:text-white/60 uppercase">
                    {{ $mailMailer ?? 'SMTP' }}
                </span>
            </button>
        </div>

        <!-- Shortcut to Pricing & Billing Catalog -->
        <a href="{{ route('admin.billing-packages.index', 'subscription') }}"
            class="h-10 px-4 rounded-[14px] text-[12.5px] font-bold text-[#34C759] dark:text-[#30D158] hover:bg-[#34C759]/10 active:scale-98 transition-all flex items-center gap-1.5 self-start sm:self-auto shrink-0 cursor-pointer">
            <i data-lucide="layers-3" class="w-4 h-4" stroke-width="1.8"></i>
            <span>Harga &amp; Paket Billing</span>
            <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 text-black/35 dark:text-white/35" stroke-width="2"></i>
        </a>
    </div>


    <!-- ========================================================================= -->
    <!-- MODULAR SETTINGS TABS (UNIFIED PLATFORM SETTINGS HUB)                     -->
    <!-- ========================================================================= -->
    @include('admin.settings.tabs.tab-system')
    @include('admin.settings.tabs.tab-payment')
    @include('admin.settings.tabs.tab-whatsapp')
    @include('admin.settings.tabs.tab-social')
    @include('admin.settings.tabs.tab-shipping')
    @include('admin.settings.tabs.tab-smtp')

</div>
@endsection
